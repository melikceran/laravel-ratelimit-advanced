<?php

namespace Melikceran\RateLimit\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;


class RateLimit
{

    private $userAgent;
    private $clientIP;
    private $clientMaskIP;
    private $allowedHostAddress;
    private $allowedUserAgents;
    private $cache;

    public function __construct(Request $request)
    {
        $this->userAgent = $request->server('HTTP_USER_AGENT');
        $this->clientIP = $this->getClientIP();
        $this->clientMaskIP = $this->maskLastSegment();
        if (!$this->clientMaskIP) $this->clientMaskIP = "defaultIP";
        $this->allowedHostAddress = config("ratelimit.allowedHostAddress");
        $this->allowedUserAgents = config("ratelimit.allowedUserAgents");
        // $this->userAgent = "Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)";
        // $this->clientIP = "66.249.64.10";
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next, $BOT_RATELIMIT_PER_MINUTE, $cache=true)
    {
        // Cache active/deactive
        if (!is_bool($cache)) $cache = ($cache == "false") ? false : true;
        $this->cache = $cache;

        // Bot Detect
        if ($this->botValidate()) {
            return $next($request);
        }

        // User Rate Limit
        if (!RateLimiter::attempt($this->clientMaskIP, $BOT_RATELIMIT_PER_MINUTE, function(){})) {
            if ($request->wantsJson()) {
                return response()->json(['status'=>429, 'message' => 'Too Many Request!'], 429);
             }
            return response('Too Many Request!', 429);
        }

        return $next($request);
    }


    private function botValidate() {
        // Bot UserAgents Check
        if (str_ireplace($this->allowedUserAgents, "", $this->userAgent) != $this->userAgent) {
            if ($this->cache) {
                return Cache::rememberForever('bot_validate_'.md5($this->clientIP), function() {
                    return $this->validate();
                });
            }
            return $this->validate();
        }
        return false;
    }
    
    private function validate() {
        // Get IP to host name
        $getHostName = gethostbyaddr($this->clientIP);
        if (!$this->hostValidate($getHostName)) {
            return false;
        }
        // Check Real IP
        $get_dns_record = (object)current(dns_get_record($getHostName));
        $checkedIP = isset($get_dns_record->ip) ? $get_dns_record->ip : (isset($get_dns_record->ipv6) ? $get_dns_record->ipv6 : null);
        if ($checkedIP != $this->clientIP) {
            return false;
        }
        // verified bot passed
        return true;
    }

    private function hostValidate($hostname) {
        if (blank($hostname)) return false;
        $hostname = rtrim($hostname, ".");
        $filtered = array_filter($this->allowedHostAddress, function($item) use ($hostname) {
            return $item === substr($hostname, -strlen($item));
        });
        if (!blank($filtered)) return true;
        return false;
    }

    private function maskLastSegment()    {
        $ip = $this->getClientIP();
        if (strpos($ip, ".") !== false) {
            $ipExplode =  explode(".", $ip);
            array_pop($ipExplode);
            return implode(".", $ipExplode);
        } else if (strpos($ip, ":") !== false) {
            $ipExplode =  explode(":", $ip);
            array_pop($ipExplode);
            return implode(":", $ipExplode);
        }
        return null;
    }

    private function getClientIP()
    {
        $clientIP = getenv('HTTP_CLIENT_IP') ?:
            getenv('HTTP_X_FORWARDED_FOR') ?:
            getenv('HTTP_X_FORWARDED') ?:
            getenv('HTTP_FORWARDED_FOR') ?:
            getenv('HTTP_FORWARDED') ?:
            getenv('REMOTE_ADDR');
        return $clientIP;
    }

}

# Laravel RateLimit for Bot

It is the extended version of the Laravel Rate Limiting class.
Removes limitations on search engine bots.

100% Detects fake bots.
Detects real bots with IP and Reverse DNS queries.
It supports IPv4 and IPv6.

Default supported Search Engine Bots;

**Google, Bing, Msn, Yandex, Yahoo, Baidu, Petalbot**

## How does it work?

 1. Checks whether the User-Agent information belongs to the search engine. 
 2. Performs DNS Lookup check from IP address. 
 3. It confirms that it is a real search engine bot by doing a reverse DNS query. 
 4. If it is a allowed bot, it will whitelist the IPv4/Ipv6 address. 
 5. It also limits the last block of the requesting visitor's IP address (1.1.1.x). It blocks requests and attacks from the same block.
 6. Other visitors can enter the site within the limits you specify. 

Available for API or Web.
In API usage, it returns over-limit information in json format.

    {'status':429, 'message':'Too Many Requests!'}
    

## Requirements;

It is an extended version of the Laravel Rate Limiter class. There is no extra requirement.

> Caching: 							Laravel Cache 
> 
> Rate Limit: 						Laravel RateLimiter
> 
> Ip Lookup: 						gethostbyaddr() 
> 
> Reverse DNS Lookup: 	dns_get_record()



## Use of;
Specify how many requests per minute visitors can make.

    Route::get('/', function () {
        return view('welcome');
    })->middleware('ratelimit:30');

For real-time querying without using cache;

    Route::get('/', function () {
        return view('welcome');
    })->middleware('ratelimit:30,false');
    
    
## Configuration;
  To publish the configuration file; (config/ratelimit.php)

    php artisan vendor:publish --tag=ratelimit
    
    
    
    
    
    

Simple GeoBlocking-Bundle
=========================

Symfony2 Bundle that allows you to configure geoblocking access to certain pages of your application.

It adds an kernel event listener that listens for "kernel.request" events and uses the php geoip module to identify the country of origin of the current request and depending on the configuration displays an error-page.

## Requirements
There are no explicit requirements. BUT the default setup makes two assumptions:

##### 1. the php geoip-module is enabled on your server or you installed and configured the Maxmind/GeoIP Bundle
   
"DefaultLookupAdapter" uses the [php function geoip_country_code_by_name($address)](http://www.php.net/manual/en/function.geoip-country-code3-by-name.php) 
to find the country of the given address.

To use the default implementation, this function (provided by the php geoip module => http://www.php.net/manual/en/book.geoip.php) must be available.

Alternatively you can use the MaxmindLookupAdapter (from the Maxmind/GeoIP-Bundle => "maxmind/geoip": "dev-master"), which requires that the MaxmindGeoIPBundle 
is installed and configured.

Or you can implement and use your own GeoLookupAdapter that uses an other way to find the country for the given ip (see below).

##### 2. you use fosuserbundle for authentication/usermanagment

Most often you would like that registered users can access your site from wherever they are. So there should be a option to login and for logged 
in users no pages should be blocked. As a lot of people (including me) use the fosuserbundle for user managment, the default configuration is set 
to work nicely with the default configuration of the fosuserbundle.

You can change this of course in the config.yml.


## Installation
To install AzineGeoBlockingBundle with Composer just add the following to your composer.json file:

```
// composer.json
{
    // ...
    require: {
        // ...
        "azine/geoblocking-bundle": "dev-master"
    }
}
```

Then, you can install the new dependencies by running Composer’s update command from the directory where your composer.json file is located:

```
php composer.phar update
```

Now, Composer will automatically download all required files, and install them for you. All that is left to do is to update your AppKernel.php file, and register the new bundle:

```
<?php

// in AppKernel::registerBundles()
$bundles = array(
    // ...
   	new Azine\GeoBlockingBundle\AzineGeoBlockingBundle(),
    // ...
);
```


## Configuration options
For the bundle to work with the default-settings, no config-options are required. 
The default blocks all anonymouse users unless they are in the same 
private subnet (=> both server & client are inside the same home/company network) or on localhost (=> web-server and client are the same computer, e.g. when debugging locally).

This is the complete list of configuration options with their defaults.
```
// app/config/config.yml
azine_geo_blocking:
    enabled:              			true 										# true|false : turn the whole bundle on/off
    access_denied_view:  AzineGeoBlockingBundle::accessDenied.html.twig 		# the view to be rendered as "blocked" page
    block_anonymouse_users_only:	true		 								# block all users or only users that are not logged in yet
    login_route:          			fos_user_security_login 					# route name to the login-form (only relevant if block_anonymouse_users_only is set to true)
    lookup_adapter:       			azine_geo_blocking.default.lookup.adapter	# id of the lookup-adapter you would like to use (e.g. azine_geo_blocking.maxmind.lookup.adapter)
    allow_private_ips:    			true										# true | false : also applie the rules to private IPs e.g. 127.0.0.1 or 192.168.xxx.yyy etc.
	
	# you can white-list ips certain networks can access you site     
	# default is empty, but you can specify an arry of ip addresses or regex-pattern
    ip_whitelist:       			[]										    # List of IPs you would like to allow. E.g. Search engine crawlers
    logBlockedRequests:   			false									    # true | false : Log a message for blocked request.

	# you can also allow search-bots by looking up their domain
	# also see https://support.google.com/webmasters/answer/80553 on how to check googleBots
	allow_search_bots: 				false										# true | false : allow the domains listed in "search_bot_domains"
    # array of domains of allowed search-engine-bots e.g. .googlebot.com or .search.msn.com (make sure you add the dot at the start of the domain, so "evilcopyofgooglebot.com" will not be allowed but "some.host.name.googlebot.com" will be.
    search_bot_domains:
        # Defaults:
        - .google.com
        - .googlebot.com
        - .search.msn.com

	# routes to applie the blocking rules to
    # only either whitelist or blacklist can contain values, if you configure both, the blacklist will be ignored.
    routes:
        whitelist:
        	- route_to_allways_allow
            # the following three routes work nice with the default routes of the fosuserbundle
            - fos_user_security_login
            - fos_user_security_login_check
            - fos_user_security_logout
        blacklist:            
        	- route_to_allways_block
        	- other_route_to_allways_block

	# countries to applie the blocking rules for
    # only either whitelist or blacklist can contain values, if you configure both, the blacklist will be ignored.
    countries:
        whitelist:  # e.g. "CH","FR","DE" etc. => access is allowed to visitors from these countries
        	- CH
        	- FR
        	- DE
        blacklist:  # e.g. "US","CN" etc. => access is denied to visitors from these countries
        	- US
        	- CN
        	
    # You can enable/disable the feature to check for the "geoblocking_allow_cookie" to either allow or block the user. 
    allow_by_cookie: false 
    
    # You can change the name of the cookie that should be checked. 
    # If the value of the cookie evaluates to true in php, the user is allowed to see the pages. see http://www.php.net/manual/en/language.types.boolean.php
    # Cookie-Value => User allowed
    # true|1|2|-1  :   yes
    # false|0|null :   no
    # 12.3.2014    :   yes
    # 'no-way'     :   yes 
    allow_by_cookie_name: "geoblocking_allow_cookie"
      
```

## Allow user by cookie
There are special cases where you want to allow visitors full access to your site even though they are not (yet) registered. For example allow an invited user to see all the pages, before signing up.

To allow this, you can set a coockie (named: geoblocking_allow_cookie, value true) that disables the geoblocking for a while.

To allow "invited" users to check out the site before registering, add this code to the action handling the first page view of an invited user to set the cookie:

```
// src/Acme/YourBundle/Controller/InvitationController.php
...
    public function handleClickOnInvitationLinkAction(Request $request){
        ...
        // do your magic here 
        ...
        
        // render the view welcoming the invited user
        $response = $this->container->get('templating')->renderResponse('AcmeYourBundle:Invitation:welcomeInvitedUser.html.twig.');
        
        // set the geoblocking_allow_cookie, so the invited user can take a look arround before registering.
        $response->headers->setCookie(new Cookie("geoblocking_allow_cookie", true, new \DateTime("2 days")));
        return $response;
    }
```

Update your config.yml to enable the "allow_by_cookie"-feature and to allow the route that sets the cookie
```
// app/config/config.yml
azine_geo_blocking:
    ...
    routes:
        whitelist:
            ...
            - public_handle_click_on_invitation_link
            
    allow_by_cookie: true  
```

## Alternative GeoIpLookupAdapter
You can create your own implementation of [Adapter\GeoIpLookupAdapterInterface.php](Adapter/GeoIpLookupAdapterInterface.php), define it as service in your service.yml or service.xml and set the service-id as lookup_adapter in the config.yml:
```
// app/config/config.yml
azine_geo_blocking:
    enabled:              true 										# true|false : turn the whole bundle on/off
    lookup_adapter:       your.own.implementation.of.lookup.adapter	# id of the lookup-adapter you would like to use
``` 



## Build-Status ec.
[![Build Status](https://travis-ci.org/azine/geoblocking-bundle.png)](https://travis-ci.org/azine/geoblocking-bundle)
[![Total Downloads](https://poser.pugx.org/azine/geoblocking-bundle/downloads.png)](https://packagist.org/packages/azine/geoblocking-bundle)
[![Latest Stable Version](https://poser.pugx.org/azine/geoblocking-bundle/v/stable.png)](https://packagist.org/packages/azine/geoblocking-bundle)
[![Scrutinizer Quality Score](https://scrutinizer-ci.com/g/azine/geoblocking-bundle/badges/quality-score.png?s=c6d9068893471309c3de0cadd2cf9f8f51804c91)](https://scrutinizer-ci.com/g/azine/geoblocking-bundle/)
[![Code Coverage](https://scrutinizer-ci.com/g/azine/geoblocking-bundle/badges/coverage.png?s=bb74d9f20c0797f3a49b57aad0ae3258666513cb)](https://scrutinizer-ci.com/g/azine/geoblocking-bundle/)

<?php
namespace Azine\GeoBlockingBundle\Adapter;

class DefaultLookupAdapter implements GeoIpLookupAdapterInterface {

    public function getCountry($visitorAddress) {
    	if(!function_exists("geoip_country_code_by_name")){
    		throw new \InvalidArgumentException("It seems, the geo-ip extension is not installed for php.");
    	}
    	$counrtyCode = @\geoip_country_code_by_name($visitorAddress);

    	return $counrtyCode;
    }
}
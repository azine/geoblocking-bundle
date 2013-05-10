<?php
namespace Azine\GeoBlockingBundle\Adapter;

class DefaultLookupAdapter implements GeoIpLookupAdapterInterface
{
    public function getCountry($visitorAddress)
    {
    	$counrtyCode = @\geoip_country_code_by_name($visitorAddress);

    	return $counrtyCode;
    }
}
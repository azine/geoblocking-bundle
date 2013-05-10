<?php
namespace Azine\GeoBlockingBundle\Adapter;

interface GeoIpLookupAdapterInterface
{
	/**
	 * Return the 2-character country-code for the given ip address
	 * @param unknown_type $visitorAddress
	 */
    public function getCountry($visitorAddress);
}
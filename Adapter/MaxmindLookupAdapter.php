<?php
namespace Azine\GeoBlockingBundle\Adapter;

use Maxmind\lib\GeoIp;

use Symfony\Component\DependencyInjection\ContainerInterface;

class MaxmindLookupAdapter implements GeoIpLookupAdapterInterface{

	protected $geoip = null;

	public function __construct(ContainerInterface $container){
		if(!$container->hasParameter('maxmind_geoip_data_file_path')){
			throw new \InvalidArgumentException("It seems, the MaxmindGeoIP-Bundle is not installed. The parameter 'maxmind_geoip_data_file_path' is not defined.");
		}
    	$filePath = $container->getParameter('maxmind_geoip_data_file_path');
        $this->geoip = new GeoIp($filePath);
	}

    public function getCountry($visitorAddress){
        $record = $this->geoip->geoip_record_by_addr($visitorAddress);
        return $record->country_code;
    }
}
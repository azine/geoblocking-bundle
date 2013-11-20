<?php
namespace Azine\GeoBlockingBundle\Test\Adapter;

use Azine\GeoBlockingBundle\Adapter\DefaultLookupAdapter;

class DefaultLookupAdapterTest extends \PHPUnit_Framework_TestCase{

    public function testGetCountry(){
    	if(function_exists("geoip_country_code_by_name")){
			$adapter = new DefaultLookupAdapter();

	    	$chIp = "194.150.248.201";
	    	$this->assertEquals("CH",$adapter->getCountry($chIp), "$chIp should be in Switzerland => CH");

	    	$usIp = "8.8.8.8";
	    	$this->assertEquals("US",$adapter->getCountry($usIp), "$usIp should be in the USA => US");
    	} else {
    		$this->markTestSkipped("php geoip-module seems not to be installed.");
    	}
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testGetCountryWithoutGeoIpModule(){
    	if(!function_exists("geoip_country_code_by_name")){
	    	$adapter = new DefaultLookupAdapter();
	    	$chIp = "194.150.248.201";
	    	$this->assertEquals("CH",$adapter->getCountry($chIp), "$chIp should be in Switzerland => CH");
    	} else {
    		throw new \InvalidArgumentException("It seems, the geo-ip extension is installed, so this test doesn't make sense.");
    	}

    }
}
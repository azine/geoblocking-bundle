<?php
namespace Azine\GeoBlockingBundle\Test\Adapter;

use Azine\GeoBlockingBundle\Adapter\DefaultLookupAdapter;

class DefaultLookupAdapterTest extends \PHPUnit_Framework_TestCase{

    public function testGetCountry(){
		$adapter = new DefaultLookupAdapter();


    	$chIp = "194.150.248.201";
    	$this->assertEquals("CH",$adapter->getCountry($chIp), "$chIp should be in Switzerland => CH");

    	$usIp = "8.8.8.8";
    	$this->assertEquals("US",$adapter->getCountry($usIp), "$usIp should be in the USA => US");


    }
}
<?php
namespace Azine\GeoBlockingBundle\Test\EventListener;

use Symfony\Component\HttpKernel\HttpKernelInterface;

use Symfony\Component\HttpFoundation\Response;

use Azine\GeoBlockingBundle\Adapter\DefaultLookupAdapter;

use Azine\GeoBlockingBundle\EventListener\GeoBlockingKernelRequestListener;

class GeoBlockingKernelRequestListenerTest extends \PHPUnit_Framework_TestCase{

	private $usIP = "17.149.160.49";
	private $localIP = "192.168.0.42";
	private $chIP = "194.150.248.201";
	private $googleBotIP = "66.249.78.150";
	private $msnBotIP = "157.56.93.153";

    public function testOnKernelRequestGeoBlocking_Disabled(){
		$parameters = $this->getDefaultParams();
    	$parameters['enabled'] = false;
		$eventAllowMock = $this->getMockBuilder("Symfony\Component\HttpKernel\Event\GetResponseEvent")->disableOriginalConstructor()->getMock();
		$loggerMock = $this->getMockBuilder("Psr\Log\LoggerInterface")->disableOriginalConstructor()->getMock();

		$eventAllowMock->expects($this->never())->method("getRequest");
		$eventAllowMock->expects($this->never())->method("setResponse");
		$eventAllowMock->expects($this->never())->method("stopPropagation");

		$geoBlockingListener = new GeoBlockingKernelRequestListener($this->getTemplatingMock(false), new DefaultLookupAdapter(), $loggerMock, $parameters);
		$geoBlockingListener->onKernelRequest($eventAllowMock);

    }

    public function testOnKernelRequestGeoBlocking_BlockAccess(){
    	$parameters = $this->getDefaultParams();
    	$eventBlockMock = $this->getMockBuilder("Symfony\Component\HttpKernel\Event\GetResponseEvent")->disableOriginalConstructor()->getMock();
		$requestMock = $this->getMockBuilder("Symfony\Component\HttpFoundation\Request")->disableOriginalConstructor()->getMock();
		$loggerMock = $this->getMockBuilder("Psr\Log\LoggerInterface")->disableOriginalConstructor()->getMock();

		$eventBlockMock->expects($this->once())->method("getRequestType")->will($this->returnValue(HttpKernelInterface::MASTER_REQUEST));
		$eventBlockMock->expects($this->once())->method("setResponse");
		$eventBlockMock->expects($this->once())->method("stopPropagation");
		$requestMock->expects($this->once())->method("getUser")->will($this->returnValue(null));
		$requestMock->expects($this->once())->method("getClientIp")->will($this->returnValue($this->usIP));
		$eventBlockMock->expects($this->once())->method("getRequest")->will($this->returnValue($requestMock));

		$geoBlockingListener = new GeoBlockingKernelRequestListener($this->getTemplatingMock(true), new DefaultLookupAdapter(), $loggerMock, $parameters);
		$geoBlockingListener->onKernelRequest($eventBlockMock);
    }

    public function testOnKernelRequestGeoBlocking_SubRequest(){
    	$parameters = $this->getDefaultParams();
    	$eventBlockMock = $this->getMockBuilder("Symfony\Component\HttpKernel\Event\GetResponseEvent")->disableOriginalConstructor()->getMock();
    	$loggerMock = $this->getMockBuilder("Psr\Log\LoggerInterface")->disableOriginalConstructor()->getMock();

		$eventBlockMock->expects($this->once())->method("getRequestType")->will($this->returnValue(HttpKernelInterface::SUB_REQUEST));
		$eventBlockMock->expects($this->never())->method("setResponse");
		$eventBlockMock->expects($this->never())->method("stopPropagation");
		$eventBlockMock->expects($this->never())->method("getRequest");

		$geoBlockingListener = new GeoBlockingKernelRequestListener($this->getTemplatingMock(false), new DefaultLookupAdapter(), $loggerMock, $parameters);
		$geoBlockingListener->onKernelRequest($eventBlockMock);
     }

    public function testOnKernelRequestGeoBlocking_AnonOnlyBlockAll(){
    	$parameters = $this->getDefaultParams();
    	$parameters['blockAnonOnly'] = false;
    	$requestMock = $this->getMockBuilder("Symfony\Component\HttpFoundation\Request")->disableOriginalConstructor()->getMock();
    	$userMock = $this->getMockBuilder("FOS\UserBundle\Model\UserInterface")->disableOriginalConstructor()->getMock();
    	$loggerMock = $this->getMockBuilder("Psr\Log\LoggerInterface")->disableOriginalConstructor()->getMock();

    	$eventBlockMock = $this->getMockBuilder("Symfony\Component\HttpKernel\Event\GetResponseEvent")->disableOriginalConstructor()->getMock();
    	$eventBlockMock->expects($this->once())->method("getRequestType")->will($this->returnValue(HttpKernelInterface::MASTER_REQUEST));
    	$eventBlockMock->expects($this->once())->method("setResponse");
    	$eventBlockMock->expects($this->once())->method("stopPropagation");
    	$requestMock->expects($this->once())->method("getUser")->will($this->returnValue($userMock));
    	$requestMock->expects($this->once())->method("getClientIp")->will($this->returnValue($this->usIP));
    	$eventBlockMock->expects($this->once())->method("getRequest")->will($this->returnValue($requestMock));

    	$geoBlockingListener = new GeoBlockingKernelRequestListener($this->getTemplatingMock(true), new DefaultLookupAdapter(), $loggerMock, $parameters);
    	$geoBlockingListener->onKernelRequest($eventBlockMock);
    }

    public function testOnKernelRequestGeoBlocking_AnonOnlyNotLoggedIn(){
    	$parameters = $this->getDefaultParams();
    	$eventBlockMock = $this->getMockBuilder("Symfony\Component\HttpKernel\Event\GetResponseEvent")->disableOriginalConstructor()->getMock();
    	$requestMock = $this->getMockBuilder("Symfony\Component\HttpFoundation\Request")->disableOriginalConstructor()->getMock();
    	$loggerMock = $this->getMockBuilder("Psr\Log\LoggerInterface")->disableOriginalConstructor()->getMock();

    	$eventBlockMock->expects($this->once())->method("getRequestType")->will($this->returnValue(HttpKernelInterface::MASTER_REQUEST));
    	$eventBlockMock->expects($this->once())->method("setResponse");
    	$requestMock->expects($this->once())->method("getUser")->will($this->returnValue(null));
    	$requestMock->expects($this->once())->method("getClientIp")->will($this->returnValue($this->usIP));
		$requestMock->expects($this->once())->method("get");
    	$eventBlockMock->expects($this->once())->method("stopPropagation");
    	$eventBlockMock->expects($this->once())->method("getRequest")->will($this->returnValue($requestMock));

    	$geoBlockingListener = new GeoBlockingKernelRequestListener($this->getTemplatingMock(true), new DefaultLookupAdapter(), $loggerMock, $parameters);
    	$geoBlockingListener->onKernelRequest($eventBlockMock);

    }

    public function testOnKernelRequestGeoBlocking_AnonOnlyLoggedIn(){
    	$parameters = $this->getDefaultParams();
    	$eventBlockMock = $this->getMockBuilder("Symfony\Component\HttpKernel\Event\GetResponseEvent")->disableOriginalConstructor()->getMock();
    	$requestMock = $this->getMockBuilder("Symfony\Component\HttpFoundation\Request")->disableOriginalConstructor()->getMock();
    	$userMock = $this->getMockBuilder("FOS\UserBundle\Model\UserInterface")->disableOriginalConstructor()->getMock();
    	$loggerMock = $this->getMockBuilder("Psr\Log\LoggerInterface")->disableOriginalConstructor()->getMock();

    	$eventBlockMock->expects($this->once())->method("getRequestType")->will($this->returnValue(HttpKernelInterface::MASTER_REQUEST));
    	$eventBlockMock->expects($this->once())->method("getRequest")->will($this->returnValue($requestMock));
    	$requestMock->expects($this->once())->method("getUser")->will($this->returnValue($userMock));
       	$requestMock->expects($this->never())->method("getClientIp")->will($this->returnValue($this->usIP));
		$requestMock->expects($this->never())->method("get");
    	$eventBlockMock->expects($this->never())->method("setResponse");
    	$eventBlockMock->expects($this->never())->method("stopPropagation");

    	$geoBlockingListener = new GeoBlockingKernelRequestListener($this->getTemplatingMock(false), new DefaultLookupAdapter(), $loggerMock, $parameters);
    	$geoBlockingListener->onKernelRequest($eventBlockMock);

    }

    public function testOnKernelRequestGeoBlocking_AllowPrivateIPs(){
    	$parameters = $this->getDefaultParams();
    	$eventBlockMock = $this->getMockBuilder("Symfony\Component\HttpKernel\Event\GetResponseEvent")->disableOriginalConstructor()->getMock();
    	$requestMock = $this->getMockBuilder("Symfony\Component\HttpFoundation\Request")->disableOriginalConstructor()->getMock();
    	$userMock = $this->getMockBuilder("FOS\UserBundle\Model\UserInterface")->disableOriginalConstructor()->getMock();
    	$lookUpMock = $this->getMockBuilder("Azine\GeoBlockingBundle\Adapter\DefaultLookupAdapter")->getMock();
    	$loggerMock = $this->getMockBuilder("Psr\Log\LoggerInterface")->disableOriginalConstructor()->getMock();

    	$eventBlockMock->expects($this->once())->method("getRequestType")->will($this->returnValue(HttpKernelInterface::MASTER_REQUEST));
    	$eventBlockMock->expects($this->once())->method("getRequest")->will($this->returnValue($requestMock));
    	$requestMock->expects($this->once())->method("getUser")->will($this->returnValue(null));
    	$requestMock->expects($this->once())->method("getClientIp")->will($this->returnValue($this->localIP));
    	$requestMock->expects($this->never())->method("get");
    	$lookUpMock->expects($this->never())->method("getCountry");
    	$eventBlockMock->expects($this->never())->method("setResponse");
    	$eventBlockMock->expects($this->never())->method("stopPropagation");

    	$geoBlockingListener = new GeoBlockingKernelRequestListener($this->getTemplatingMock(false), $lookUpMock, $loggerMock, $parameters);
    	$geoBlockingListener->onKernelRequest($eventBlockMock);

    }

    public function testOnKernelRequestGeoBlocking_RouteBlocking_BlockWithWhiteList(){
    	$parameters = $this->getDefaultParams();
    	$eventBlockMock = $this->getMockBuilder("Symfony\Component\HttpKernel\Event\GetResponseEvent")->disableOriginalConstructor()->getMock();
    	$requestMock = $this->getMockBuilder("Symfony\Component\HttpFoundation\Request")->disableOriginalConstructor()->getMock();
    	$userMock = $this->getMockBuilder("FOS\UserBundle\Model\UserInterface")->disableOriginalConstructor()->getMock();
    	$lookUpMock = $this->getMockBuilder("Azine\GeoBlockingBundle\Adapter\DefaultLookupAdapter")->getMock();
    	$loggerMock = $this->getMockBuilder("Psr\Log\LoggerInterface")->disableOriginalConstructor()->getMock();

    	$eventBlockMock->expects($this->once())->method("getRequestType")->will($this->returnValue(HttpKernelInterface::MASTER_REQUEST));
    	$eventBlockMock->expects($this->once())->method("getRequest")->will($this->returnValue($requestMock));
    	$requestMock->expects($this->once())->method("getUser")->will($this->returnValue(null));
    	$requestMock->expects($this->once())->method("getClientIp")->will($this->returnValue($this->chIP));
    	$requestMock->expects($this->once())->method("get")->with("_route", null, false)->will($this->returnValue("notAllowedRoute"));
    	$lookUpMock->expects($this->once())->method("getCountry");
    	$eventBlockMock->expects($this->once())->method("setResponse");
    	$eventBlockMock->expects($this->once())->method("stopPropagation");

    	$geoBlockingListener = new GeoBlockingKernelRequestListener($this->getTemplatingMock(true), $lookUpMock, $loggerMock, $parameters);
    	$geoBlockingListener->onKernelRequest($eventBlockMock);

    }

    public function testOnKernelRequestGeoBlocking_RouteBlocking_AllowWithWhiteList(){
    	$parameters = $this->getDefaultParams();
    	$eventBlockMock = $this->getMockBuilder("Symfony\Component\HttpKernel\Event\GetResponseEvent")->disableOriginalConstructor()->getMock();
    	$requestMock = $this->getMockBuilder("Symfony\Component\HttpFoundation\Request")->disableOriginalConstructor()->getMock();
    	$userMock = $this->getMockBuilder("FOS\UserBundle\Model\UserInterface")->disableOriginalConstructor()->getMock();
    	$lookUpMock = $this->getMockBuilder("Azine\GeoBlockingBundle\Adapter\DefaultLookupAdapter")->getMock();
    	$loggerMock = $this->getMockBuilder("Psr\Log\LoggerInterface")->disableOriginalConstructor()->getMock();

    	$eventBlockMock->expects($this->once())->method("getRequestType")->will($this->returnValue(HttpKernelInterface::MASTER_REQUEST));
    	$eventBlockMock->expects($this->once())->method("getRequest")->will($this->returnValue($requestMock));
    	$requestMock->expects($this->once())->method("getUser")->will($this->returnValue(null));
    	$requestMock->expects($this->once())->method("getClientIp")->will($this->returnValue($this->chIP));
    	$requestMock->expects($this->once())->method("get")->with("_route", null, false)->will($this->returnValue("fos_user_security_login"));
    	$lookUpMock->expects($this->never())->method("getCountry");
    	$eventBlockMock->expects($this->never())->method("setResponse");
    	$eventBlockMock->expects($this->never())->method("stopPropagation");

    	$geoBlockingListener = new GeoBlockingKernelRequestListener($this->getTemplatingMock(false), $lookUpMock, $loggerMock, $parameters);
    	$geoBlockingListener->onKernelRequest($eventBlockMock);

    }

    public function testOnKernelRequestGeoBlocking_RouteBlocking_BlockWithBlackList(){
    	$parameters = $this->getDefaultParams();
 		$parameters["countryWhitelist"] = array();
    	$parameters["routeWhitelist"] = array();
    	$parameters["routeBlacklist"] = array("notAllowedRoute");

    	$eventBlockMock = $this->getMockBuilder("Symfony\Component\HttpKernel\Event\GetResponseEvent")->disableOriginalConstructor()->getMock();
    	$requestMock = $this->getMockBuilder("Symfony\Component\HttpFoundation\Request")->disableOriginalConstructor()->getMock();
    	$userMock = $this->getMockBuilder("FOS\UserBundle\Model\UserInterface")->disableOriginalConstructor()->getMock();
    	$lookUpMock = $this->getMockBuilder("Azine\GeoBlockingBundle\Adapter\DefaultLookupAdapter")->getMock();
    	$loggerMock = $this->getMockBuilder("Psr\Log\LoggerInterface")->disableOriginalConstructor()->getMock();

    	$eventBlockMock->expects($this->once())->method("getRequestType")->will($this->returnValue(HttpKernelInterface::MASTER_REQUEST));
    	$eventBlockMock->expects($this->once())->method("getRequest")->will($this->returnValue($requestMock));
    	$requestMock->expects($this->once())->method("getUser")->will($this->returnValue(null));
    	$requestMock->expects($this->once())->method("getClientIp")->will($this->returnValue($this->chIP));
    	$requestMock->expects($this->once())->method("get")->with("_route", null, false)->will($this->returnValue("notAllowedRoute"));
    	$lookUpMock->expects($this->once())->method("getCountry");
    	$eventBlockMock->expects($this->once())->method("setResponse");
    	$eventBlockMock->expects($this->once())->method("stopPropagation");

    	$geoBlockingListener = new GeoBlockingKernelRequestListener($this->getTemplatingMock(true), $lookUpMock, $loggerMock, $parameters);
    	$geoBlockingListener->onKernelRequest($eventBlockMock);

    }

    public function testOnKernelRequestGeoBlocking_RouteBlocking_AllowWithBlackList(){
    	$parameters = $this->getDefaultParams();
    	$parameters["routeWhitelist"] = array();
    	$parameters["routeBlacklist"] = array("notAllowedRoute");

    	$eventBlockMock = $this->getMockBuilder("Symfony\Component\HttpKernel\Event\GetResponseEvent")->disableOriginalConstructor()->getMock();
    	$requestMock = $this->getMockBuilder("Symfony\Component\HttpFoundation\Request")->disableOriginalConstructor()->getMock();
    	$userMock = $this->getMockBuilder("FOS\UserBundle\Model\UserInterface")->disableOriginalConstructor()->getMock();
    	$lookUpMock = $this->getMockBuilder("Azine\GeoBlockingBundle\Adapter\DefaultLookupAdapter")->getMock();
    	$loggerMock = $this->getMockBuilder("Psr\Log\LoggerInterface")->disableOriginalConstructor()->getMock();

    	$eventBlockMock->expects($this->once())->method("getRequestType")->will($this->returnValue(HttpKernelInterface::MASTER_REQUEST));
    	$eventBlockMock->expects($this->once())->method("getRequest")->will($this->returnValue($requestMock));
    	$requestMock->expects($this->once())->method("getUser")->will($this->returnValue(null));
    	$requestMock->expects($this->once())->method("getClientIp")->will($this->returnValue($this->chIP));
    	$requestMock->expects($this->once())->method("get")->with("_route", null, false)->will($this->returnValue("someOtherRoute"));
    	$lookUpMock->expects($this->once())->method("getCountry")->with($this->chIP)->will($this->returnValue("CH"));
    	$eventBlockMock->expects($this->never())->method("setResponse");
    	$eventBlockMock->expects($this->never())->method("stopPropagation");

    	$geoBlockingListener = new GeoBlockingKernelRequestListener($this->getTemplatingMock(false), $lookUpMock, $loggerMock, $parameters);
    	$geoBlockingListener->onKernelRequest($eventBlockMock);
   }

	public function testOnKernelRequestGeoBlocking_CountryBlocking_AllowWithWhiteList(){
		$parameters = $this->getDefaultParams();
    	$parameters["routeWhitelist"] = array();

		$eventBlockMock = $this->getMockBuilder("Symfony\Component\HttpKernel\Event\GetResponseEvent")->disableOriginalConstructor()->getMock();
		$requestMock = $this->getMockBuilder("Symfony\Component\HttpFoundation\Request")->disableOriginalConstructor()->getMock();
		$userMock = $this->getMockBuilder("FOS\UserBundle\Model\UserInterface")->disableOriginalConstructor()->getMock();
		$lookUpMock = $this->getMockBuilder("Azine\GeoBlockingBundle\Adapter\DefaultLookupAdapter")->getMock();
		$loggerMock = $this->getMockBuilder("Psr\Log\LoggerInterface")->disableOriginalConstructor()->getMock();

		$eventBlockMock->expects($this->once())->method("getRequestType")->will($this->returnValue(HttpKernelInterface::MASTER_REQUEST));
		$eventBlockMock->expects($this->once())->method("getRequest")->will($this->returnValue($requestMock));
		$requestMock->expects($this->once())->method("getUser")->will($this->returnValue(null));
		$requestMock->expects($this->once())->method("getClientIp")->will($this->returnValue($this->chIP));
		$requestMock->expects($this->once())->method("get")->with("_route", null, false)->will($this->returnValue("noWhiteListRoute"));
		$lookUpMock->expects($this->once())->method("getCountry")->with($this->chIP)->will($this->returnValue("CH"));
		$eventBlockMock->expects($this->never())->method("setResponse");
		$eventBlockMock->expects($this->never())->method("stopPropagation");

		$geoBlockingListener = new GeoBlockingKernelRequestListener($this->getTemplatingMock(false), $lookUpMock, $loggerMock, $parameters);
		$geoBlockingListener->onKernelRequest($eventBlockMock);
	}

	public function testOnKernelRequestGeoBlocking_CountryBlocking_DenyWithWhiteList(){
		$parameters = $this->getDefaultParams();
		$parameters["routeWhitelist"] = array();

		$eventBlockMock = $this->getMockBuilder("Symfony\Component\HttpKernel\Event\GetResponseEvent")->disableOriginalConstructor()->getMock();
		$requestMock = $this->getMockBuilder("Symfony\Component\HttpFoundation\Request")->disableOriginalConstructor()->getMock();
		$userMock = $this->getMockBuilder("FOS\UserBundle\Model\UserInterface")->disableOriginalConstructor()->getMock();
		$lookUpMock = $this->getMockBuilder("Azine\GeoBlockingBundle\Adapter\DefaultLookupAdapter")->getMock();
		$loggerMock = $this->getMockBuilder("Psr\Log\LoggerInterface")->disableOriginalConstructor()->getMock();

		$eventBlockMock->expects($this->once())->method("getRequestType")->will($this->returnValue(HttpKernelInterface::MASTER_REQUEST));
		$eventBlockMock->expects($this->once())->method("getRequest")->will($this->returnValue($requestMock));
		$requestMock->expects($this->once())->method("getUser")->will($this->returnValue(null));
		$requestMock->expects($this->once())->method("getClientIp")->will($this->returnValue($this->usIP));
		$requestMock->expects($this->once())->method("get")->with("_route", null, false)->will($this->returnValue("noWhiteListRoute"));
		$lookUpMock->expects($this->once())->method("getCountry")->with($this->usIP)->will($this->returnValue("US"));
		$eventBlockMock->expects($this->once())->method("setResponse");
		$eventBlockMock->expects($this->once())->method("stopPropagation");

		$geoBlockingListener = new GeoBlockingKernelRequestListener($this->getTemplatingMock(true), $lookUpMock, $loggerMock, $parameters);
		$geoBlockingListener->onKernelRequest($eventBlockMock);
	}

	public function testOnKernelRequestGeoBlocking_CountryBlocking_DenyWithBlackList(){
		$parameters = $this->getDefaultParams();
		$parameters["routeWhitelist"] = array();
		$parameters["countryWhitelist"] = array();
		$parameters["countryBlacklist"] = array("US");

		$eventBlockMock = $this->getMockBuilder("Symfony\Component\HttpKernel\Event\GetResponseEvent")->disableOriginalConstructor()->getMock();
		$requestMock = $this->getMockBuilder("Symfony\Component\HttpFoundation\Request")->disableOriginalConstructor()->getMock();
		$userMock = $this->getMockBuilder("FOS\UserBundle\Model\UserInterface")->disableOriginalConstructor()->getMock();
		$lookUpMock = $this->getMockBuilder("Azine\GeoBlockingBundle\Adapter\DefaultLookupAdapter")->getMock();
		$loggerMock = $this->getMockBuilder("Psr\Log\LoggerInterface")->disableOriginalConstructor()->getMock();

		$eventBlockMock->expects($this->once())->method("getRequestType")->will($this->returnValue(HttpKernelInterface::MASTER_REQUEST));
		$eventBlockMock->expects($this->once())->method("getRequest")->will($this->returnValue($requestMock));
		$requestMock->expects($this->once())->method("getUser")->will($this->returnValue(null));
		$requestMock->expects($this->once())->method("getClientIp")->will($this->returnValue($this->usIP));
		$requestMock->expects($this->once())->method("get")->with("_route", null, false)->will($this->returnValue("random_route"));
		$lookUpMock->expects($this->once())->method("getCountry")->with($this->usIP)->will($this->returnValue("US"));
		$eventBlockMock->expects($this->once())->method("setResponse");
		$eventBlockMock->expects($this->once())->method("stopPropagation");

		$geoBlockingListener = new GeoBlockingKernelRequestListener($this->getTemplatingMock(true), $lookUpMock, $loggerMock, $parameters);
		$geoBlockingListener->onKernelRequest($eventBlockMock);
	}

    public function testOnKernelRequestGeoBlocking_CountryBlocking_AllowWithBlackList(){
		$parameters = $this->getDefaultParams();
		$parameters["routeWhitelist"] = array();
		$parameters["routeBlacklist"] = array('someNotAllowedRoute');
		$parameters["countryWhitelist"] = array();
		$parameters["countryBlacklist"] = array("US");

		$eventBlockMock = $this->getMockBuilder("Symfony\Component\HttpKernel\Event\GetResponseEvent")->disableOriginalConstructor()->getMock();
		$requestMock = $this->getMockBuilder("Symfony\Component\HttpFoundation\Request")->disableOriginalConstructor()->getMock();
		$userMock = $this->getMockBuilder("FOS\UserBundle\Model\UserInterface")->disableOriginalConstructor()->getMock();
		$lookUpMock = $this->getMockBuilder("Azine\GeoBlockingBundle\Adapter\DefaultLookupAdapter")->getMock();
		$loggerMock = $this->getMockBuilder("Psr\Log\LoggerInterface")->disableOriginalConstructor()->getMock();

		$eventBlockMock->expects($this->once())->method("getRequestType")->will($this->returnValue(HttpKernelInterface::MASTER_REQUEST));
		$eventBlockMock->expects($this->once())->method("getRequest")->will($this->returnValue($requestMock));
		$requestMock->expects($this->once())->method("getUser")->will($this->returnValue(null));
		$requestMock->expects($this->once())->method("getClientIp")->will($this->returnValue($this->chIP));
		$requestMock->expects($this->once())->method("get")->with("_route", null, false)->will($this->returnValue("noWhiteListRoute"));
		$lookUpMock->expects($this->once())->method("getCountry")->with($this->chIP)->will($this->returnValue("CH"));
		$eventBlockMock->expects($this->never())->method("setResponse");
		$eventBlockMock->expects($this->never())->method("stopPropagation");

		$geoBlockingListener = new GeoBlockingKernelRequestListener($this->getTemplatingMock(false), $lookUpMock, $loggerMock, $parameters);
		$geoBlockingListener->onKernelRequest($eventBlockMock);
    }

    public function testOnKernelRequestGeoBlocking_IP_WhiteList_allow_regexp(){
    	$parameters = $this->getDefaultParams();
    	$parameters['ip_whitelist'] = array("/66\.249\.78\.\d{1,3}/");
    	$eventBlockMock = $this->getMockBuilder("Symfony\Component\HttpKernel\Event\GetResponseEvent")->disableOriginalConstructor()->getMock();
    	$requestMock = $this->getMockBuilder("Symfony\Component\HttpFoundation\Request")->disableOriginalConstructor()->getMock();
    	$userMock = $this->getMockBuilder("FOS\UserBundle\Model\UserInterface")->disableOriginalConstructor()->getMock();
    	$lookUpMock = $this->getMockBuilder("Azine\GeoBlockingBundle\Adapter\DefaultLookupAdapter")->getMock();
    	$loggerMock = $this->getMockBuilder("Psr\Log\LoggerInterface")->disableOriginalConstructor()->getMock();

    	$eventBlockMock->expects($this->once())->method("getRequestType")->will($this->returnValue(HttpKernelInterface::MASTER_REQUEST));
    	$eventBlockMock->expects($this->once())->method("getRequest")->will($this->returnValue($requestMock));
    	$requestMock->expects($this->once())->method("getUser")->will($this->returnValue(null));
    	$requestMock->expects($this->once())->method("getClientIp")->will($this->returnValue($this->googleBotIP));
		$requestMock->expects($this->once())->method("get")->with("_route", null, false)->will($this->returnValue("random_route"));
		$lookUpMock->expects($this->once())->method("getCountry")->with($this->googleBotIP)->will($this->returnValue("US"));
    	$eventBlockMock->expects($this->never())->method("setResponse");
    	$eventBlockMock->expects($this->never())->method("stopPropagation");

    	$geoBlockingListener = new GeoBlockingKernelRequestListener($this->getTemplatingMock(false), $lookUpMock, $loggerMock, $parameters);
    	$geoBlockingListener->onKernelRequest($eventBlockMock);

    }

    public function testOnKernelRequestGeoBlocking_IP_WhiteList_allow_ip(){
    	$parameters = $this->getDefaultParams();
    	$parameters['ip_whitelist'] = array($this->googleBotIP);
    	$eventBlockMock = $this->getMockBuilder("Symfony\Component\HttpKernel\Event\GetResponseEvent")->disableOriginalConstructor()->getMock();
    	$requestMock = $this->getMockBuilder("Symfony\Component\HttpFoundation\Request")->disableOriginalConstructor()->getMock();
    	$userMock = $this->getMockBuilder("FOS\UserBundle\Model\UserInterface")->disableOriginalConstructor()->getMock();
    	$lookUpMock = $this->getMockBuilder("Azine\GeoBlockingBundle\Adapter\DefaultLookupAdapter")->getMock();
    	$loggerMock = $this->getMockBuilder("Psr\Log\LoggerInterface")->disableOriginalConstructor()->getMock();

    	$eventBlockMock->expects($this->once())->method("getRequestType")->will($this->returnValue(HttpKernelInterface::MASTER_REQUEST));
    	$eventBlockMock->expects($this->once())->method("getRequest")->will($this->returnValue($requestMock));
    	$requestMock->expects($this->once())->method("getUser")->will($this->returnValue(null));
    	$requestMock->expects($this->once())->method("getClientIp")->will($this->returnValue($this->googleBotIP));
		$requestMock->expects($this->once())->method("get")->with("_route", null, false)->will($this->returnValue("random_route"));
		$lookUpMock->expects($this->once())->method("getCountry")->with($this->googleBotIP)->will($this->returnValue("US"));
    	$eventBlockMock->expects($this->never())->method("setResponse");
    	$eventBlockMock->expects($this->never())->method("stopPropagation");

    	$loggerMock->expects($this->never())->method("warning");


    	$geoBlockingListener = new GeoBlockingKernelRequestListener($this->getTemplatingMock(false), $lookUpMock, $loggerMock, $parameters);
    	$geoBlockingListener->onKernelRequest($eventBlockMock);

    }

    public function testOnKernelRequestGeoBlocking_Log_Blocked_Requests(){
    	$parameters = $this->getDefaultParams();
    	$parameters["routeWhitelist"] = array();
    	$parameters["countryWhitelist"] = array();
    	$parameters["countryBlacklist"] = array("US");
    	$parameters["logBlockedRequests"] = true;

    	$_SERVER['HTTP_USER_AGENT'] = "some blocked visitors UA.";

    	$eventBlockMock = $this->getMockBuilder("Symfony\Component\HttpKernel\Event\GetResponseEvent")->disableOriginalConstructor()->getMock();
    	$requestMock = $this->getMockBuilder("Symfony\Component\HttpFoundation\Request")->disableOriginalConstructor()->getMock();
    	$userMock = $this->getMockBuilder("FOS\UserBundle\Model\UserInterface")->disableOriginalConstructor()->getMock();
    	$lookUpMock = $this->getMockBuilder("Azine\GeoBlockingBundle\Adapter\DefaultLookupAdapter")->getMock();
    	$loggerMock = $this->getMockBuilder("Psr\Log\LoggerInterface")->disableOriginalConstructor()->getMock();

    	$eventBlockMock->expects($this->once())->method("getRequestType")->will($this->returnValue(HttpKernelInterface::MASTER_REQUEST));
    	$eventBlockMock->expects($this->exactly(2))->method("getRequest")->will($this->returnValue($requestMock));
    	$requestMock->expects($this->once())->method("getUser")->will($this->returnValue(null));
    	$requestMock->expects($this->exactly(2))->method("getClientIp")->will($this->returnValue($this->usIP));
    	$requestMock->expects($this->exactly(2))->method("get")->with("_route", null, false)->will($this->returnValue("random_route"));
    	$lookUpMock->expects($this->once())->method("getCountry")->with($this->usIP)->will($this->returnValue("US"));
    	$eventBlockMock->expects($this->once())->method("setResponse");
    	$eventBlockMock->expects($this->once())->method("stopPropagation");

		$loggerMock->expects($this->exactly(2))->method("warning");

    	$geoBlockingListener = new GeoBlockingKernelRequestListener($this->getTemplatingMock(true), $lookUpMock, $loggerMock, $parameters);
    	$geoBlockingListener->onKernelRequest($eventBlockMock);

    }

    public function testOnKernelRequestGeoBlocking_Search_Engine_Bot_Allow_Google(){
    	$parameters = $this->getDefaultParams();
    	$parameters['allow_search_bots'] = true;
    	$eventBlockMock = $this->getMockBuilder("Symfony\Component\HttpKernel\Event\GetResponseEvent")->disableOriginalConstructor()->getMock();
    	$requestMock = $this->getMockBuilder("Symfony\Component\HttpFoundation\Request")->disableOriginalConstructor()->getMock();
    	$userMock = $this->getMockBuilder("FOS\UserBundle\Model\UserInterface")->disableOriginalConstructor()->getMock();
    	$lookUpMock = $this->getMockBuilder("Azine\GeoBlockingBundle\Adapter\DefaultLookupAdapter")->getMock();
    	$loggerMock = $this->getMockBuilder("Psr\Log\LoggerInterface")->disableOriginalConstructor()->getMock();

    	$eventBlockMock->expects($this->once())->method("getRequestType")->will($this->returnValue(HttpKernelInterface::MASTER_REQUEST));
    	$eventBlockMock->expects($this->once())->method("getRequest")->will($this->returnValue($requestMock));
    	$requestMock->expects($this->once())->method("getUser")->will($this->returnValue(null));
    	$requestMock->expects($this->once())->method("getClientIp")->will($this->returnValue($this->googleBotIP));
    	$requestMock->expects($this->once())->method("get")->with("_route", null, false)->will($this->returnValue("random_route"));
    	$lookUpMock->expects($this->once())->method("getCountry")->with($this->googleBotIP)->will($this->returnValue("US"));
    	$eventBlockMock->expects($this->never())->method("setResponse");
    	$eventBlockMock->expects($this->never())->method("stopPropagation");

    	$loggerMock->expects($this->never())->method("warning");


    	$geoBlockingListener = new GeoBlockingKernelRequestListener($this->getTemplatingMock(false), $lookUpMock, $loggerMock, $parameters);
    	$geoBlockingListener->onKernelRequest($eventBlockMock);
    }

    public function testOnKernelRequestGeoBlocking_Search_Engine_Bot_Allow_MSN(){
    	$parameters = $this->getDefaultParams();
    	$parameters['allow_search_bots'] = true;
    	$eventBlockMock = $this->getMockBuilder("Symfony\Component\HttpKernel\Event\GetResponseEvent")->disableOriginalConstructor()->getMock();
    	$requestMock = $this->getMockBuilder("Symfony\Component\HttpFoundation\Request")->disableOriginalConstructor()->getMock();
    	$userMock = $this->getMockBuilder("FOS\UserBundle\Model\UserInterface")->disableOriginalConstructor()->getMock();
    	$lookUpMock = $this->getMockBuilder("Azine\GeoBlockingBundle\Adapter\DefaultLookupAdapter")->getMock();
    	$loggerMock = $this->getMockBuilder("Psr\Log\LoggerInterface")->disableOriginalConstructor()->getMock();

    	$eventBlockMock->expects($this->once())->method("getRequestType")->will($this->returnValue(HttpKernelInterface::MASTER_REQUEST));
    	$eventBlockMock->expects($this->once())->method("getRequest")->will($this->returnValue($requestMock));
    	$requestMock->expects($this->once())->method("getUser")->will($this->returnValue(null));
    	$requestMock->expects($this->once())->method("getClientIp")->will($this->returnValue($this->msnBotIP));
    	$requestMock->expects($this->once())->method("get")->with("_route", null, false)->will($this->returnValue("random_route"));
    	$lookUpMock->expects($this->once())->method("getCountry")->with($this->msnBotIP)->will($this->returnValue("US"));
    	$eventBlockMock->expects($this->never())->method("setResponse");
    	$eventBlockMock->expects($this->never())->method("stopPropagation");

    	$loggerMock->expects($this->never())->method("warning");


    	$geoBlockingListener = new GeoBlockingKernelRequestListener($this->getTemplatingMock(false), $lookUpMock, $loggerMock, $parameters);
    	$geoBlockingListener->onKernelRequest($eventBlockMock);
    }

    public function testOnKernelRequestGeoBlocking_Search_Engine_Bot_Deny_usIP(){
    	$parameters = $this->getDefaultParams();
    	$parameters['allow_search_bots'] = true;

    	$eventBlockMock = $this->getMockBuilder("Symfony\Component\HttpKernel\Event\GetResponseEvent")->disableOriginalConstructor()->getMock();
    	$requestMock = $this->getMockBuilder("Symfony\Component\HttpFoundation\Request")->disableOriginalConstructor()->getMock();
    	$userMock = $this->getMockBuilder("FOS\UserBundle\Model\UserInterface")->disableOriginalConstructor()->getMock();
    	$lookUpMock = $this->getMockBuilder("Azine\GeoBlockingBundle\Adapter\DefaultLookupAdapter")->getMock();
    	$loggerMock = $this->getMockBuilder("Psr\Log\LoggerInterface")->disableOriginalConstructor()->getMock();

    	$eventBlockMock->expects($this->once())->method("getRequestType")->will($this->returnValue(HttpKernelInterface::MASTER_REQUEST));
    	$eventBlockMock->expects($this->once())->method("getRequest")->will($this->returnValue($requestMock));
    	$requestMock->expects($this->once())->method("getUser")->will($this->returnValue(null));
    	$requestMock->expects($this->once())->method("getClientIp")->will($this->returnValue($this->usIP));
    	$requestMock->expects($this->once())->method("get")->with("_route", null, false)->will($this->returnValue("random_route"));
    	$lookUpMock->expects($this->once())->method("getCountry")->with($this->usIP)->will($this->returnValue("US"));

    	$eventBlockMock->expects($this->once())->method("setResponse");
    	$eventBlockMock->expects($this->once())->method("stopPropagation");

    	$geoBlockingListener = new GeoBlockingKernelRequestListener($this->getTemplatingMock(true), $lookUpMock, $loggerMock, $parameters);
    	$geoBlockingListener->onKernelRequest($eventBlockMock);
    }



    private function getTemplatingMock($showBlockingPage){
		$templatingMock = $this->getMockBuilder("Symfony\Bundle\FrameworkBundle\Templating\EngineInterface")->disableOriginalConstructor()->getMock();
		if($showBlockingPage){
			$templatingMock->expects($this->once())->method("renderResponse")->with()->will($this->returnValue(new Response()));
		} else {
			$templatingMock->expects($this->never())->method("renderResponse");
		}
		return $templatingMock;
    }

    private function getDefaultParams(){
    	$parameters = array();
    	$parameters['enabled']			= true;
    	$parameters['blockAnonOnly']	= true;
    	$parameters['allowPrivateIPs']	= true;
    	$parameters['loginRoute']		= 'fos_user_security_login';
    	$parameters['blockedPageView']	= 'AzineGeoBlockingBundle::accessDenied.html.twig';
    	$parameters['routeWhitelist']	= array('fos_user_security_login', 'fos_user_security_login_check', 'fos_user_security_logout');
    	$parameters['routeBlacklist']	= array();
    	$parameters['countryWhitelist']	= array("CH","DE","FR");
    	$parameters['countryBlacklist']	= array();
    	$parameters['ip_whitelist']	= array();
    	$parameters['logBlockedRequests'] = false;
    	$parameters['allow_search_bots'] = false;
    	$parameters['search_bot_domains'] = array(".google.com", ".googlebot.com", ".search.msn.com");
    	return $parameters;
    }
}
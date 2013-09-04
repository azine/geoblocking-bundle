<?php
namespace Azine\GeoBlockingBundle\Test\EventListener;

use Symfony\Component\HttpKernel\HttpKernelInterface;

use Symfony\Component\HttpFoundation\Response;

use Azine\GeoBlockingBundle\Adapter\DefaultLookupAdapter;

use Azine\GeoBlockingBundle\EventListener\GeoBlockingKernelRequestListener;

class GeoBlockingKernelRequestListenerTest extends \PHPUnit_Framework_TestCase{

	private $usIP = "8.8.8.8";
	private $localIP = "192.168.0.42";
	private $chIP = "194.150.248.201";

    public function testOnKernelRequestGeoBlocking_Disabled(){
		$parameters = $this->getDefaultParams();
    	$parameters['enabled'] = false;
		$eventAllowMock = $this->getMockBuilder("Symfony\Component\HttpKernel\Event\GetResponseEvent")->disableOriginalConstructor()->getMock();

		$eventAllowMock->expects($this->never())->method("getRequest");
		$eventAllowMock->expects($this->never())->method("setResponse");
		$eventAllowMock->expects($this->never())->method("stopPropagation");

		$geoBlockingListener = new GeoBlockingKernelRequestListener($this->getTemplatingMock(false), new DefaultLookupAdapter(), $parameters);
		$geoBlockingListener->onKernelRequest($eventAllowMock);

    }

    public function testOnKernelRequestGeoBlocking_BlockAccess(){
    	$parameters = $this->getDefaultParams();
    	$eventBlockMock = $this->getMockBuilder("Symfony\Component\HttpKernel\Event\GetResponseEvent")->disableOriginalConstructor()->getMock();
		$requestMock = $this->getMockBuilder("Symfony\Component\HttpFoundation\Request")->disableOriginalConstructor()->getMock();

		$eventBlockMock->expects($this->once())->method("getRequestType")->will($this->returnValue(HttpKernelInterface::MASTER_REQUEST));
		$eventBlockMock->expects($this->once())->method("setResponse");
		$eventBlockMock->expects($this->once())->method("stopPropagation");
		$requestMock->expects($this->once())->method("getUser")->will($this->returnValue(null));
		$requestMock->expects($this->once())->method("getClientIp")->will($this->returnValue($this->usIP));
		$eventBlockMock->expects($this->once())->method("getRequest")->will($this->returnValue($requestMock));

		$geoBlockingListener = new GeoBlockingKernelRequestListener($this->getTemplatingMock(true), new DefaultLookupAdapter(), $parameters);
		$geoBlockingListener->onKernelRequest($eventBlockMock);
    }

    public function testOnKernelRequestGeoBlocking_SubRequest(){
    	$parameters = $this->getDefaultParams();
    	$eventBlockMock = $this->getMockBuilder("Symfony\Component\HttpKernel\Event\GetResponseEvent")->disableOriginalConstructor()->getMock();

		$eventBlockMock->expects($this->once())->method("getRequestType")->will($this->returnValue(HttpKernelInterface::SUB_REQUEST));
		$eventBlockMock->expects($this->never())->method("setResponse");
		$eventBlockMock->expects($this->never())->method("stopPropagation");
		$eventBlockMock->expects($this->never())->method("getRequest");

		$geoBlockingListener = new GeoBlockingKernelRequestListener($this->getTemplatingMock(false), new DefaultLookupAdapter(), $parameters);
		$geoBlockingListener->onKernelRequest($eventBlockMock);
     }

    public function testOnKernelRequestGeoBlocking_AnonOnlyBlockAll(){
    	$parameters = $this->getDefaultParams();
    	$parameters['blockAnonOnly'] = false;
    	$requestMock = $this->getMockBuilder("Symfony\Component\HttpFoundation\Request")->disableOriginalConstructor()->getMock();
    	$userMock = $this->getMockBuilder("FOS\UserBundle\Model\UserInterface")->disableOriginalConstructor()->getMock();

    	$eventBlockMock = $this->getMockBuilder("Symfony\Component\HttpKernel\Event\GetResponseEvent")->disableOriginalConstructor()->getMock();
    	$eventBlockMock->expects($this->once())->method("getRequestType")->will($this->returnValue(HttpKernelInterface::MASTER_REQUEST));
    	$eventBlockMock->expects($this->once())->method("setResponse");
    	$eventBlockMock->expects($this->once())->method("stopPropagation");
    	$requestMock->expects($this->once())->method("getUser")->will($this->returnValue($userMock));
    	$requestMock->expects($this->once())->method("getClientIp")->will($this->returnValue($this->usIP));
    	$eventBlockMock->expects($this->once())->method("getRequest")->will($this->returnValue($requestMock));

    	$geoBlockingListener = new GeoBlockingKernelRequestListener($this->getTemplatingMock(true), new DefaultLookupAdapter(), $parameters);
    	$geoBlockingListener->onKernelRequest($eventBlockMock);
    }

    public function testOnKernelRequestGeoBlocking_AnonOnlyNotLoggedIn(){
    	$parameters = $this->getDefaultParams();
    	$eventBlockMock = $this->getMockBuilder("Symfony\Component\HttpKernel\Event\GetResponseEvent")->disableOriginalConstructor()->getMock();
    	$requestMock = $this->getMockBuilder("Symfony\Component\HttpFoundation\Request")->disableOriginalConstructor()->getMock();

    	$eventBlockMock->expects($this->once())->method("getRequestType")->will($this->returnValue(HttpKernelInterface::MASTER_REQUEST));
    	$eventBlockMock->expects($this->once())->method("setResponse");
    	$requestMock->expects($this->once())->method("getUser")->will($this->returnValue(null));
    	$requestMock->expects($this->once())->method("getClientIp")->will($this->returnValue($this->usIP));
		$requestMock->expects($this->once())->method("get");
    	$eventBlockMock->expects($this->once())->method("stopPropagation");
    	$eventBlockMock->expects($this->once())->method("getRequest")->will($this->returnValue($requestMock));

    	$geoBlockingListener = new GeoBlockingKernelRequestListener($this->getTemplatingMock(true), new DefaultLookupAdapter(), $parameters);
    	$geoBlockingListener->onKernelRequest($eventBlockMock);

    }

    public function testOnKernelRequestGeoBlocking_AnonOnlyLoggedIn(){
    	$parameters = $this->getDefaultParams();
    	$eventBlockMock = $this->getMockBuilder("Symfony\Component\HttpKernel\Event\GetResponseEvent")->disableOriginalConstructor()->getMock();
    	$requestMock = $this->getMockBuilder("Symfony\Component\HttpFoundation\Request")->disableOriginalConstructor()->getMock();
    	$userMock = $this->getMockBuilder("FOS\UserBundle\Model\UserInterface")->disableOriginalConstructor()->getMock();

    	$eventBlockMock->expects($this->once())->method("getRequestType")->will($this->returnValue(HttpKernelInterface::MASTER_REQUEST));
    	$eventBlockMock->expects($this->once())->method("getRequest")->will($this->returnValue($requestMock));
    	$requestMock->expects($this->once())->method("getUser")->will($this->returnValue($userMock));
       	$requestMock->expects($this->never())->method("getClientIp")->will($this->returnValue($this->usIP));
		$requestMock->expects($this->never())->method("get");
    	$eventBlockMock->expects($this->never())->method("setResponse");
    	$eventBlockMock->expects($this->never())->method("stopPropagation");

    	$geoBlockingListener = new GeoBlockingKernelRequestListener($this->getTemplatingMock(false), new DefaultLookupAdapter(), $parameters);
    	$geoBlockingListener->onKernelRequest($eventBlockMock);

    }

    public function testOnKernelRequestGeoBlocking_AllowPrivateIPs(){
    	$parameters = $this->getDefaultParams();
    	$eventBlockMock = $this->getMockBuilder("Symfony\Component\HttpKernel\Event\GetResponseEvent")->disableOriginalConstructor()->getMock();
    	$requestMock = $this->getMockBuilder("Symfony\Component\HttpFoundation\Request")->disableOriginalConstructor()->getMock();
    	$userMock = $this->getMockBuilder("FOS\UserBundle\Model\UserInterface")->disableOriginalConstructor()->getMock();
    	$lookUpMock = $this->getMockBuilder("Azine\GeoBlockingBundle\Adapter\DefaultLookupAdapter")->getMock();

    	$eventBlockMock->expects($this->once())->method("getRequestType")->will($this->returnValue(HttpKernelInterface::MASTER_REQUEST));
    	$eventBlockMock->expects($this->once())->method("getRequest")->will($this->returnValue($requestMock));
    	$requestMock->expects($this->once())->method("getUser")->will($this->returnValue(null));
    	$requestMock->expects($this->once())->method("getClientIp")->will($this->returnValue($this->localIP));
    	$requestMock->expects($this->never())->method("get");
    	$lookUpMock->expects($this->never())->method("getCountry");
    	$eventBlockMock->expects($this->never())->method("setResponse");
    	$eventBlockMock->expects($this->never())->method("stopPropagation");

    	$geoBlockingListener = new GeoBlockingKernelRequestListener($this->getTemplatingMock(false), $lookUpMock, $parameters);
    	$geoBlockingListener->onKernelRequest($eventBlockMock);

    }

    public function testOnKernelRequestGeoBlocking_RouteBlocking_BlockWithWhiteList(){
    	$parameters = $this->getDefaultParams();
    	$eventBlockMock = $this->getMockBuilder("Symfony\Component\HttpKernel\Event\GetResponseEvent")->disableOriginalConstructor()->getMock();
    	$requestMock = $this->getMockBuilder("Symfony\Component\HttpFoundation\Request")->disableOriginalConstructor()->getMock();
    	$userMock = $this->getMockBuilder("FOS\UserBundle\Model\UserInterface")->disableOriginalConstructor()->getMock();
    	$lookUpMock = $this->getMockBuilder("Azine\GeoBlockingBundle\Adapter\DefaultLookupAdapter")->getMock();

    	$eventBlockMock->expects($this->once())->method("getRequestType")->will($this->returnValue(HttpKernelInterface::MASTER_REQUEST));
    	$eventBlockMock->expects($this->once())->method("getRequest")->will($this->returnValue($requestMock));
    	$requestMock->expects($this->once())->method("getUser")->will($this->returnValue(null));
    	$requestMock->expects($this->once())->method("getClientIp")->will($this->returnValue($this->chIP));
    	$requestMock->expects($this->once())->method("get")->with("_route", null, false)->will($this->returnValue("notAllowedRoute"));
    	$lookUpMock->expects($this->once())->method("getCountry");
    	$eventBlockMock->expects($this->once())->method("setResponse");
    	$eventBlockMock->expects($this->once())->method("stopPropagation");

    	$geoBlockingListener = new GeoBlockingKernelRequestListener($this->getTemplatingMock(true), $lookUpMock, $parameters);
    	$geoBlockingListener->onKernelRequest($eventBlockMock);

    }

    public function testOnKernelRequestGeoBlocking_RouteBlocking_AllowWithWhiteList(){
    	$parameters = $this->getDefaultParams();
    	$eventBlockMock = $this->getMockBuilder("Symfony\Component\HttpKernel\Event\GetResponseEvent")->disableOriginalConstructor()->getMock();
    	$requestMock = $this->getMockBuilder("Symfony\Component\HttpFoundation\Request")->disableOriginalConstructor()->getMock();
    	$userMock = $this->getMockBuilder("FOS\UserBundle\Model\UserInterface")->disableOriginalConstructor()->getMock();
    	$lookUpMock = $this->getMockBuilder("Azine\GeoBlockingBundle\Adapter\DefaultLookupAdapter")->getMock();

    	$eventBlockMock->expects($this->once())->method("getRequestType")->will($this->returnValue(HttpKernelInterface::MASTER_REQUEST));
    	$eventBlockMock->expects($this->once())->method("getRequest")->will($this->returnValue($requestMock));
    	$requestMock->expects($this->once())->method("getUser")->will($this->returnValue(null));
    	$requestMock->expects($this->once())->method("getClientIp")->will($this->returnValue($this->chIP));
    	$requestMock->expects($this->once())->method("get")->with("_route", null, false)->will($this->returnValue("fos_user_security_login"));
    	$lookUpMock->expects($this->never())->method("getCountry");
    	$eventBlockMock->expects($this->never())->method("setResponse");
    	$eventBlockMock->expects($this->never())->method("stopPropagation");

    	$geoBlockingListener = new GeoBlockingKernelRequestListener($this->getTemplatingMock(false), $lookUpMock, $parameters);
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

    	$eventBlockMock->expects($this->once())->method("getRequestType")->will($this->returnValue(HttpKernelInterface::MASTER_REQUEST));
    	$eventBlockMock->expects($this->once())->method("getRequest")->will($this->returnValue($requestMock));
    	$requestMock->expects($this->once())->method("getUser")->will($this->returnValue(null));
    	$requestMock->expects($this->once())->method("getClientIp")->will($this->returnValue($this->chIP));
    	$requestMock->expects($this->once())->method("get")->with("_route", null, false)->will($this->returnValue("notAllowedRoute"));
    	$lookUpMock->expects($this->once())->method("getCountry");
    	$eventBlockMock->expects($this->once())->method("setResponse");
    	$eventBlockMock->expects($this->once())->method("stopPropagation");

    	$geoBlockingListener = new GeoBlockingKernelRequestListener($this->getTemplatingMock(true), $lookUpMock, $parameters);
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

    	$eventBlockMock->expects($this->once())->method("getRequestType")->will($this->returnValue(HttpKernelInterface::MASTER_REQUEST));
    	$eventBlockMock->expects($this->once())->method("getRequest")->will($this->returnValue($requestMock));
    	$requestMock->expects($this->once())->method("getUser")->will($this->returnValue(null));
    	$requestMock->expects($this->once())->method("getClientIp")->will($this->returnValue($this->chIP));
    	$requestMock->expects($this->once())->method("get")->with("_route", null, false)->will($this->returnValue("someOtherRoute"));
    	$lookUpMock->expects($this->once())->method("getCountry")->with($this->chIP)->will($this->returnValue("CH"));
    	$eventBlockMock->expects($this->never())->method("setResponse");
    	$eventBlockMock->expects($this->never())->method("stopPropagation");

    	$geoBlockingListener = new GeoBlockingKernelRequestListener($this->getTemplatingMock(false), $lookUpMock, $parameters);
    	$geoBlockingListener->onKernelRequest($eventBlockMock);
   }

	public function testOnKernelRequestGeoBlocking_CountryBlocking_AllowWithWhiteList(){
		$parameters = $this->getDefaultParams();
    	$parameters["routeWhitelist"] = array();

		$eventBlockMock = $this->getMockBuilder("Symfony\Component\HttpKernel\Event\GetResponseEvent")->disableOriginalConstructor()->getMock();
		$requestMock = $this->getMockBuilder("Symfony\Component\HttpFoundation\Request")->disableOriginalConstructor()->getMock();
		$userMock = $this->getMockBuilder("FOS\UserBundle\Model\UserInterface")->disableOriginalConstructor()->getMock();
		$lookUpMock = $this->getMockBuilder("Azine\GeoBlockingBundle\Adapter\DefaultLookupAdapter")->getMock();

		$eventBlockMock->expects($this->once())->method("getRequestType")->will($this->returnValue(HttpKernelInterface::MASTER_REQUEST));
		$eventBlockMock->expects($this->once())->method("getRequest")->will($this->returnValue($requestMock));
		$requestMock->expects($this->once())->method("getUser")->will($this->returnValue(null));
		$requestMock->expects($this->once())->method("getClientIp")->will($this->returnValue($this->chIP));
		$requestMock->expects($this->once())->method("get")->with("_route", null, false)->will($this->returnValue("noWhiteListRoute"));
		$lookUpMock->expects($this->once())->method("getCountry")->with($this->chIP)->will($this->returnValue("CH"));
		$eventBlockMock->expects($this->never())->method("setResponse");
		$eventBlockMock->expects($this->never())->method("stopPropagation");

		$geoBlockingListener = new GeoBlockingKernelRequestListener($this->getTemplatingMock(false), $lookUpMock, $parameters);
		$geoBlockingListener->onKernelRequest($eventBlockMock);
	}

	public function testOnKernelRequestGeoBlocking_CountryBlocking_DenyWithWhiteList(){
		$parameters = $this->getDefaultParams();
		$parameters["routeWhitelist"] = array();

		$eventBlockMock = $this->getMockBuilder("Symfony\Component\HttpKernel\Event\GetResponseEvent")->disableOriginalConstructor()->getMock();
		$requestMock = $this->getMockBuilder("Symfony\Component\HttpFoundation\Request")->disableOriginalConstructor()->getMock();
		$userMock = $this->getMockBuilder("FOS\UserBundle\Model\UserInterface")->disableOriginalConstructor()->getMock();
		$lookUpMock = $this->getMockBuilder("Azine\GeoBlockingBundle\Adapter\DefaultLookupAdapter")->getMock();

		$eventBlockMock->expects($this->once())->method("getRequestType")->will($this->returnValue(HttpKernelInterface::MASTER_REQUEST));
		$eventBlockMock->expects($this->once())->method("getRequest")->will($this->returnValue($requestMock));
		$requestMock->expects($this->once())->method("getUser")->will($this->returnValue(null));
		$requestMock->expects($this->once())->method("getClientIp")->will($this->returnValue($this->usIP));
		$requestMock->expects($this->once())->method("get")->with("_route", null, false)->will($this->returnValue("noWhiteListRoute"));
		$lookUpMock->expects($this->once())->method("getCountry")->with($this->usIP)->will($this->returnValue("US"));
		$eventBlockMock->expects($this->once())->method("setResponse");
		$eventBlockMock->expects($this->once())->method("stopPropagation");

		$geoBlockingListener = new GeoBlockingKernelRequestListener($this->getTemplatingMock(true), $lookUpMock, $parameters);
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

		$eventBlockMock->expects($this->once())->method("getRequestType")->will($this->returnValue(HttpKernelInterface::MASTER_REQUEST));
		$eventBlockMock->expects($this->once())->method("getRequest")->will($this->returnValue($requestMock));
		$requestMock->expects($this->once())->method("getUser")->will($this->returnValue(null));
		$requestMock->expects($this->once())->method("getClientIp")->will($this->returnValue($this->usIP));
		$requestMock->expects($this->once())->method("get")->with("_route", null, false)->will($this->returnValue("fos_"));
		$lookUpMock->expects($this->once())->method("getCountry")->with($this->usIP)->will($this->returnValue("US"));
		$eventBlockMock->expects($this->once())->method("setResponse");
		$eventBlockMock->expects($this->once())->method("stopPropagation");

		$geoBlockingListener = new GeoBlockingKernelRequestListener($this->getTemplatingMock(true), $lookUpMock, $parameters);
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

		$eventBlockMock->expects($this->once())->method("getRequestType")->will($this->returnValue(HttpKernelInterface::MASTER_REQUEST));
		$eventBlockMock->expects($this->once())->method("getRequest")->will($this->returnValue($requestMock));
		$requestMock->expects($this->once())->method("getUser")->will($this->returnValue(null));
		$requestMock->expects($this->once())->method("getClientIp")->will($this->returnValue($this->chIP));
		$requestMock->expects($this->once())->method("get")->with("_route", null, false)->will($this->returnValue("noWhiteListRoute"));
		$lookUpMock->expects($this->once())->method("getCountry")->with($this->chIP)->will($this->returnValue("CH"));
		$eventBlockMock->expects($this->never())->method("setResponse");
		$eventBlockMock->expects($this->never())->method("stopPropagation");

		$geoBlockingListener = new GeoBlockingKernelRequestListener($this->getTemplatingMock(false), $lookUpMock, $parameters);
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
    	return $parameters;
    }
}
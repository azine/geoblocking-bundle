<?php
namespace Azine\GeoBlockingBundle\EventListener;

use FOS\UserBundle\Model\UserInterface;

use Symfony\Component\HttpKernel\HttpKernelInterface;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;

use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;

use Azine\GeoBlockingBundle\Adapter\GeoIpLookupAdapterInterface;

class GeoBlockingKernelRequestListener{
	private $configParams;
	private $lookUpAdapter;
	private $templating;


	public function __construct(EngineInterface $templating, GeoIpLookupAdapterInterface $lookupAdapter,  array $parameters){
		$this->configParams = $parameters;
		$this->lookUpAdapter 	= $lookupAdapter;
		$this->templating = $templating;
	}

	public function onKernelRequest(GetResponseEvent $event)
	{
		// ignore sub-requests
		if($event->getRequestType() == HttpKernelInterface::SUB_REQUEST){
			return;
		}

		// check if the blocking is enabled at all
		if(!$this->configParams['enabled']){
			return;
		}

		$request = $event->getRequest();
		// check if blocking authenticated users is enabled
		$authenticated = $request->getUser() instanceof UserInterface;
		if($this->configParams['blockAnonOnly'] && $authenticated){
			return;
		}

		$visitorAddress = $request->getClientIp();

		// check if the visitors IP is a private IP => the request comes from the same subnet as the server or the server it self.
		if($this->configParams['allowPrivateIPs']){
			$patternForPrivateIPs = "#(^127\.0\.0\.1)|(^10\.)|(^172\.1[6-9]\.)|(^172\.2[0-9]\.)|(^172\.3[0-1]\.)|(^192\.168\.)#";
			if(preg_match($patternForPrivateIPs, $visitorAddress) == 1){
				return;
			}
		}

	   	// check if the route is allowed from the current visitors country via whitelists
		$routeName = $request->get('_route');
		$allowedByRouteWhiteList = array_search($routeName, $this->configParams['routeWhitelist'], true) === false;
		if(!$allowedByRouteWhiteList){
			return;
		}

		$country = $this->lookUpAdapter->getCountry($visitorAddress);
		$allowedByCountryWhiteList = array_search($country, $this->configParams['countryWhitelist'], true) === false;
		if(!$allowedByCountryWhiteList){
			return;
		}

		$useRouteBL = !empty($this->configParams['routeBlacklist']);
		$useCountryBL = !empty($this->configParams['countryBlacklist']);

		if(!$useRouteBL && !$useCountryBL){
			$this->blockAccess($event);
			return;
		}

		// check if one of the blacklists denies access
		if($useRouteBL){
			if(array_search($routeName, $this->configParams['routeBlacklist'], true) !== false){
				$this->blockAccess($event);
			}
		}

		if($useCountryBL){
			if(array_search($country, $this->configParams['countryBlacklist'], true) !== false){
				$this->blockAccess($event);
			}
		}

		return;
	}

	private function blockAccess(GetResponseEvent $event){
		// render the "sorry you are not allowed here"-page
		$parameters = array('loginRoute' => $this->configParams['loginRoute']);
		$event->setResponse($this->templating->renderResponse($this->configParams['blockedPageView'], $parameters));
		$event->stopPropagation();
	}
}
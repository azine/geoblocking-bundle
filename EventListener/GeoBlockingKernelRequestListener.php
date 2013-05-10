<?php
namespace Azine\GeoBlockingBundle\EventListener;

use Symfony\Component\HttpKernel\HttpKernelInterface;

use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\HttpFoundation\RedirectResponse;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;

use Symfony\Bundle\FrameworkBundle\DependencyInjection\Configuration;

use Symfony\Component\HttpKernel\KernelEvents;

use Symfony\Component\HttpFoundation\Response;

use Azine\GeoBlockingBundle\Adapter\GeoIpLookupAdapterInterface;

class GeoBlockingKernelRequestListener
{
	private $router;
	private $container;
	private $enabled;
	private $blockAnonOnly;
	private $countryWhitelist;
	private $countryBlacklist;
	private $routeWhitelist;
	private $routeBlacklist;
	private $lookUpAdapter;
	private $allowPrivateIPs;
	private $blockedPageView;

	public function __construct($router, $container)
	{
		$this->router = $router;
		$this->container = $container;

		// initialize the configuration parameters
		$this->enabled 			= $this->container->getParameter('azine_geo_blocking_enabled');
		$this->blockAnonOnly 	= $this->container->getParameter('azine_geo_blocking_block_anonymouse_users_only');

		$this->countryWhitelist = $this->container->getParameter('azine_geo_blocking_countries_whitelist');
		$this->countryBlacklist = $this->container->getParameter('azine_geo_blocking_countries_blacklist');

		$this->routeWhitelist 	= $this->container->getParameter('azine_geo_blocking_routes_whitelist');
		$this->routeBlacklist 	= $this->container->getParameter('azine_geo_blocking_routes_blacklist');

		$this->lookUpAdapter 	= $this->container->getParameter('azine_geo_blocking_lookup_adapter');
		$this->allowPrivateIPs 	= $this->container->getParameter('allow_private_ips');
		$this->blockedPageView 	= $this->container->getParameter('azine_geo_blocking_access_denied_view', 'AzineGeoBlockingBundle::accessDenied.html.twig');
		$this->loginRoute	 	= $this->container->getParameter('azine_geo_blocking_login_route');

	}

	public function onKernelRequest(GetResponseEvent $event)
	{
		// ignore sub-requests
		if($event->getRequestType() == HttpKernelInterface::SUB_REQUEST){
			return;
		}

		// check if the blocking is enabled at all
		if(!$this->enabled){
			return;
		}

		// check if blocking authenticated users is enabled
		$securityContext = $this->container->get('security.context');
		$authenticated = $securityContext->getToken() && $securityContext->isGranted('IS_AUTHENTICATED_REMEMBERED');
		if($this->blockAnonOnly && $authenticated){
			return;
		}

		$visitorAddress = $this->container->get('request')->getClientIp();

		// check if the visitors IP is a private IP => the request comes from the same subnet as the server or the server it self.
		if($this->allowPrivateIPs){
			$patternForPrivateIPs = "#(^127\.0\.0\.1)|(^10\.)|(^172\.1[6-9]\.)|(^172\.2[0-9]\.)|(^172\.3[0-1]\.)|(^192\.168\.)#";
			if(preg_match($patternForPrivateIPs, $visitorAddress) == 1){
				return;
			}
		}

	   	// check if the route is allowed from the current visitors country
		$country = $this->getLookupAdapter()->getCountry($visitorAddress);
		$routeName = $event->getRequest()->get('_route');

		$allowedByRouteWhiteList = array_search($routeName, $this->routeWhitelist, true);
		if(!($allowedByRouteWhiteList === false)){
			return;
		}

		$allowedByCountryWhiteList = array_search($country, $this->countryWhitelist, true);
		if(!($allowedByCountryWhiteList === false)){
			return;
		}

		$blockedByRouteBlacklist   = empty($this->routeBlacklist)   || array_search($routeName, $this->routeBlacklist, true);
		$blockedByCountryBlacklist = empty($this->countryBlacklist) || array_search($country, $this->countryBlacklist, true);

		if(!$blockedByRouteBlacklist && !$blockedByCountryBlacklist){
			return;
		}

		// render the "sorry you are not allowed here"-page
		$parameters = array('loginRoute' => $this->loginRoute);
		$event->setResponse($this->container->get('templating')->renderResponse($this->blockedPageView, $parameters));
		$event->stopPropagation();
	}

	/**
	 * @return GeoIpLookupAdapterInterface
	 */
	private function getLookupAdapter(){
		return $this->container->get($this->lookUpAdapter);
	}
}
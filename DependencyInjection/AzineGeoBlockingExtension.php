<?php

namespace Azine\GeoBlockingBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class AzineGeoBlockingExtension extends Extension
{
	const COUNTRIES = "countries";
	const ROUTES 	= "routes";
	const WHITELIST = "whitelist";
	const BLACKLIST = "blacklist";
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        if(array_key_exists(self::COUNTRIES, $config)){
        	$config[self::COUNTRIES] = $this->checkExclusiveness($config[self::COUNTRIES]);
        }

        if(array_key_exists(self::ROUTES, $config)){
        	$config[self::ROUTES] = $this->checkExclusiveness($config[self::ROUTES]);
        }

       	$container->setParameter('azine_geo_blocking.params', $config);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');

    }

    /**
     * Make sure that only either whitelist or blacklist is present. If both are present (and both contain values), the blacklist is cleared.
     * @param unknown_type $config
     * @return array
     */
    private function checkExclusiveness(array $config){
    	$whiteListIsSet = array_key_exists(self::WHITELIST, $config) && is_array($config[self::WHITELIST]) && !empty($config[self::WHITELIST]);
    	$blackListIsSet = array_key_exists(self::BLACKLIST, $config) && is_array($config[self::BLACKLIST]) && !empty($config[self::BLACKLIST]);
    	if($whiteListIsSet && $blackListIsSet){
    		$config[self::BLACKLIST] = array();
    	}
		return $config;
    }
}

<?php

namespace Azine\GeoBlockingBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('azine_geo_blocking');

        $rootNode
        	->children()
	        	->booleanNode("enabled")						->defaultTrue()->end()
	        	->scalarNode('access_denied_view')				->defaultValue('AzineGeoBlockingBundle::accessDenied.html.twig')->end()
	        	->booleanNode('block_anonymouse_users_only')	->defaultTrue()->end()
	        	->scalarNode('login_route')						->defaultValue('fos_user_security_login')->end()
	        	->scalarNode('lookup_adapter')					->defaultValue('azine_geo_blocking.lookup.adapter')->end()
	        	->booleanNode('allow_private_ips')				->defaultTrue()->end()
	        	->arrayNode('countries')
		        	->children()
		        		->variableNode('whitelist')->defaultValue(array('fos_user_security_login', 'fos_user_security_login_check', 'fos_user_security_logout'))->end()
		        		->variableNode('blacklist')->defaultValue(array())->end()
	        		->end()
	        	->end()// end countries
	        	->arrayNode('routes')
		        	->children()
		        		->variableNode('whitelist')->defaultValue(array())->end()
		        		->variableNode('blacklist')->defaultValue(array())->end()
	        		->end()
	        	->end()// end routes
	        ->end();

        return $treeBuilder;
    }
}

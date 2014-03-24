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
                ->booleanNode	("enabled")						->defaultTrue()->info("true|false : turn the whole bundle on/off")->end()
                ->scalarNode	('access_denied_view')			->defaultValue('AzineGeoBlockingBundle::accessDenied.html.twig')->info("the view to be rendered as 'blocked' page")->end()
                ->booleanNode	('block_anonymouse_users_only')	->defaultTrue()->info("block all users or only users that are not logged in yet")->end()
                ->scalarNode	('login_route')					->defaultValue('fos_user_security_login')->info("route name to the login-form (only relevant if block_anonymouse_users_only is set to true)")->end()
                ->scalarNode	('lookup_adapter')				->defaultValue('azine_geo_blocking.default.lookup.adapter')->info("id of the lookup-adapter you would like to use")->end()
                ->booleanNode	('allow_private_ips')			->defaultTrue()->info("true | false : also applie the rules to private IPs e.g. 127.0.0.1 or 192.168.xxx.yyy etc.")->end()
                ->variableNode	('ip_whitelist')				->defaultValue(array())->info("List of IPs (or regexp for IPs) you would like to allow. E.g. Search engine crawlers")->end()
                ->scalarNode	('logBlockedRequests')			->defaultFalse()->info("true | false : Log a message for blocked request.")->end()
                ->scalarNode	('allow_search_bots')			->defaultFalse()->info("true | false : Allow Bing and Google crawlers.")->end()
                ->variableNode	('search_bot_domains')			->defaultValue(array(".google.com", ".googlebot.com", ".search.msn.com"))->info("array of domains of allowed search-engine-bots e.g. .googlebot.com or .search.msn.com")->end()
                ->arrayNode		('countries')					->info("only whitelist or blacklist can contain values.")->addDefaultsIfNotSet()
                    ->children()
                        ->variableNode('blacklist')->defaultValue(array())->info("e.g. 'US','CN' etc. => access is denied to visitors from these countries")->end()
                        ->variableNode('whitelist')->defaultValue(array())->info("e.g. 'CH','FR','DE' etc. => access is allowed to visitors from these countries")->end()
                    ->end()
                ->end()// end countries
                ->arrayNode		('routes')->info("only whitelist or blacklist can contain values.")->addDefaultsIfNotSet()
                    ->children()
                        ->variableNode('blacklist')->defaultValue(array())->info("list of routes, that always should be blocked for access from unliked locations.")->end()
                        ->variableNode('whitelist')->defaultValue(array('fos_user_security_login', 'fos_user_security_login_check', 'fos_user_security_logout'))->info("list of routes, that never should be blocked for access from unliked locations (e.g. the login-routes).")->end()
                    ->end()
                ->end()// end routes
                ->booleanNode	("allow_by_cookie")				->defaultFalse()->info("true|false : turn the 'allow by cookie' feature on/off")->end()
                ->scalarNode	("allow_by_cookie_name")		->defaultValue("geoblocking_allow_cookie")->info("name of the 'allow_by_cookie'-cookie")->end()

            ->end();

        return $treeBuilder;
    }
}

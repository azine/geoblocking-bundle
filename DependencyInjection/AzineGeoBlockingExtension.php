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

        if (array_key_exists(self::COUNTRIES, $config)) {
            $config[self::COUNTRIES] = $this->checkExclusiveness($config[self::COUNTRIES]);
        }

        if (array_key_exists(self::ROUTES, $config)) {
            $config[self::ROUTES] = $this->checkExclusiveness($config[self::ROUTES]);
        }

        $prefix = 'azine_geo_blocking_';
        $container->setParameter($prefix."enabled", $config["enabled"]);
        $container->setParameter($prefix."block_anonymouse_users_only", $config["block_anonymouse_users_only"]);
        $container->setParameter($prefix."countries_whitelist", $config["countries"]["whitelist"]);
        $container->setParameter($prefix."countries_blacklist", $config["countries"]["blacklist"]);
        $container->setParameter($prefix."routes_whitelist", $config["routes"]["whitelist"]);
        $container->setParameter($prefix."routes_blacklist", $config["routes"]["blacklist"]);
        $container->setAlias($prefix."lookup_adapter", $config["lookup_adapter"]);
        $container->setParameter($prefix."allow_private_ips", $config["allow_private_ips"]);
        $container->setParameter($prefix."access_denied_view", $config["access_denied_view"]);
        $container->setParameter($prefix."login_route", $config["login_route"]);
        $container->setParameter($prefix."ip_whitelist", $config['ip_whitelist']);
        $container->setParameter($prefix."logBlockedRequests", $config['logBlockedRequests']);
        $container->setParameter($prefix."allow_search_bots", $config['allow_search_bots']);
        $container->setParameter($prefix."search_bot_domains", $config['search_bot_domains']);
        $container->setParameter($prefix."allow_by_cookie", $config['allow_by_cookie']);
        $container->setParameter($prefix."allow_by_cookie_name", $config['allow_by_cookie_name']);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');

    }

    /**
     * Make sure that only either whitelist or blacklist is present. If both are present (and both contain values), the blacklist is cleared.
     * @param  unknown_type $config
     * @return array
     */
    private function checkExclusiveness(array $config)
    {
        $whiteListIsSet = array_key_exists(self::WHITELIST, $config) && is_array($config[self::WHITELIST]) && !empty($config[self::WHITELIST]);
        $blackListIsSet = array_key_exists(self::BLACKLIST, $config) && is_array($config[self::BLACKLIST]) && !empty($config[self::BLACKLIST]);
        if ($whiteListIsSet && $blackListIsSet) {
            $config[self::BLACKLIST] = array();
        }

        return $config;
    }
}

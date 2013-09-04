<?php
namespace Azine\EmailBundle\Tests\DependencyInjection;

use Azine\GeoBlockingBundle\DependencyInjection\AzineGeoBlockingExtension;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Yaml\Parser;

class AzineGeoBlockingBundleTest extends \PHPUnit_Framework_TestCase{
	/** @var ContainerBuilder */
	protected $configuration;

	/**
	 * This should not throw an exception
	 */
	public function testMinimalConfig(){
		$loader = new AzineGeoBlockingExtension();
		$config = $this->getMinimalConfig();
		$loader->load(array($config), new ContainerBuilder());
	}

	/**
	 * This should not throw an exception
	 */
	public function testFullConfig(){
		$loader = new AzineGeoBlockingExtension();
		$config = $this->getFullConfig();
		$loader->load(array($config), new ContainerBuilder());
	}

	public function testCustomConfigurationWithWhiteList(){
		$this->configuration = new ContainerBuilder();
		$loader = new AzineGeoBlockingExtension();
		$config = $this->getFullConfig();
		$config['countries']['whitelist'][]		= 'UK';
		$config['countries']['whitelist'][]		= 'CH';
		$config['countries']['whitelist'][]		= 'DE';
		$config['countries']['blacklist'][]		= 'RU';
		$config['countries']['blacklist'][]		= 'CN';
		$config['routes']['whitelist'][]			= 'some_allowed_route';
		$config['routes']['blacklist'][]			= 'some_blocked_route';
		$config['access_denied_view']			= 'AcmeFooBundle:Geoblocking:accessDenied.html.twig';
		$config['block_anonymouse_users_only'] 	= false;
		$config['login_route']					= 'the_login_route';
		$config['lookup_adapter'] 				= 'service.name.of.lookup.adapter';
		$config['allow_private_ips'] 			= false;


		$loader->load(array($config), $this->configuration);

		$countryWL = $this->configuration->getParameter('azine_geo_blocking_countries_whitelist');
		$countryBL = $this->configuration->getParameter('azine_geo_blocking_countries_blacklist');

		$this->assertContains("UK", $countryWL);
		$this->assertContains("DE", $countryWL);
		$this->assertContains("CH", $countryWL);
		$this->assertNotContains("RU", $countryWL);
		$this->assertNotContains("CN", $countryWL);

		// if a whiteList is present, the blacklist is cleared and ignored
		$this->assertEmpty($countryBL);

		$routeWL = $this->configuration->getParameter('azine_geo_blocking_routes_whitelist');
		$routeBL = $this->configuration->getParameter('azine_geo_blocking_routes_blacklist');

		$this->assertContains("some_allowed_route", $routeWL);
		$this->assertNotContains("some_blocked_route", $routeWL);
		// if a whiteList is present, the blacklist is cleared and ignored
		$this->assertEmpty($routeBL);

		$this->assertParameter('AcmeFooBundle:Geoblocking:accessDenied.html.twig',	'azine_geo_blocking_access_denied_view');
		$this->assertParameter(false,	'azine_geo_blocking_block_anonymouse_users_only');
		$this->assertParameter('the_login_route',	'azine_geo_blocking_login_route');
		$this->assertAlias('service.name.of.lookup.adapter',	'azine_geo_blocking_lookup_adapter');
		$this->assertParameter(false,	'azine_geo_blocking_allow_private_ips');
	}

	public function testCustomConfigurationWithBlackList(){
		$this->configuration = new ContainerBuilder();
		$loader = new AzineGeoBlockingExtension();
		$config = $this->getFullConfig();
		$config['countries']['blacklist'][]		= 'RU';
		$config['countries']['blacklist'][]		= 'CN';
		$config['routes']['blacklist']			= array('some_blocked_route');
		$config['routes']['whitelist']			= array();

		$loader->load(array($config), $this->configuration);

		$countryWL = $this->configuration->getParameter('azine_geo_blocking_countries_whitelist');
		$countryBL = $this->configuration->getParameter('azine_geo_blocking_countries_blacklist');

		$this->assertContains("RU", $countryBL);
		$this->assertContains("CN", $countryBL);
		// if the whiteList is empty, the blacklist is not cleared
		$this->assertEmpty($countryWL);

		$routeWL = $this->configuration->getParameter('azine_geo_blocking_routes_whitelist');
		$routeBL = $this->configuration->getParameter('azine_geo_blocking_routes_blacklist');

		$this->assertNotContains("some_allowed_route", $routeBL);
		$this->assertContains("some_blocked_route", $routeBL);
		// if a whiteList is present, the blacklist is cleared and ignored
		$this->assertEmpty($routeWL);

	}



	protected function createFullConfiguration(){
		$this->configuration = new ContainerBuilder();
		$loader = new AzineGeoBlockingExtension();
		$config = $this->getFullConfig();
		$loader->load(array($config), $this->configuration);
		$this->assertTrue($this->configuration instanceof ContainerBuilder);
	}

	/**
	 * Get the minimal config
	 * @return array
	 */
	protected function getMinimalConfig(){
		$yaml = <<<EOF
# true|false : turn the whole bundle on/off
enabled:              true
EOF;
		$parser = new Parser();

		return $parser->parse($yaml);
	}


	/**
	 * Get a full config for this bundle
	 */
	protected function getFullConfig(){
		$yaml = <<<EOF
# true|false : turn the whole bundle on/off
enabled:              true

# the view to be rendered as 'blocked' page
access_denied_view:   AzineGeoBlockingBundle::accessDenied.html.twig

# block all users or only users that are not logged in yet
block_anonymouse_users_only:  true

# route name to the login-form (only relevant if block_anonymouse_users_only is set to true)
login_route:          fos_user_security_login

# id of the lookup-adapter you would like to use
lookup_adapter:       azine_geo_blocking.lookup.adapter

# true | false : also applie the rules to private IPs e.g. 127.0.0.1 or 192.168.xxx.yyy etc.
allow_private_ips:    true

# only whitelist or blacklist can contain values.
countries:

    # e.g. 'CH','FR','DE' etc. => access is allowed to visitors from these countries
    whitelist: []

    # e.g. 'US','CN' etc. => access is denied to visitors from these countries
    blacklist: []

# only whitelist or blacklist can contain values.
routes:

    # list of routes, that never should be blocked for access from unliked locations (e.g. the login-routes).
    whitelist:

        # Defaults:
        - fos_user_security_login
        - fos_user_security_login_check
        - fos_user_security_logout

    # list of routes, that always should be blocked for access from unliked locations.
    blacklist: []
EOF;
		$parser = new Parser();

		return $parser->parse($yaml);
	}

	/**
	 * @param string $value
	 * @param string $key
	 */
	private function assertAlias($value, $key){
		$this->assertEquals($value, (string) $this->configuration->getAlias($key), sprintf('%s alias is correct', $key));
	}

	/**
	 * @param mixed  $value
	 * @param string $key
	 */
	private function assertParameter($value, $key){
		$this->assertEquals($value, $this->configuration->getParameter($key), sprintf('%s parameter is correct', $key));
	}

	/**
	 * @param string $id
	 */
	private function assertHasDefinition($id){
		$this->assertTrue(($this->configuration->hasDefinition($id) ?: $this->configuration->hasAlias($id)));
	}

	/**
	 * @param string $id
	 */
	private function assertNotHasDefinition($id){
		$this->assertFalse(($this->configuration->hasDefinition($id) ?: $this->configuration->hasAlias($id)));
	}

	protected function tearDown(){
		unset($this->configuration);
	}
}

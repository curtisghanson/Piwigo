<?php
namespace Piwigo\Provider;

use Silex\Application;
use Silex\ServiceProviderInterface;
use Symfony\Component\Config\FileLocator;
use Piwigo\DependencyInjection\Configuration;

class ConfigurationServiceProvider implements  ServiceProviderInterface
{
    public function register(Application $app)
    {
        $app['piwigo.conf'] = $app->share(function ($name) use ($app) {
            $dir = array(__DIR__ . '/../../../app/config');

            $locator = new FileLocator($dir);
            $conf    = new Configuration($locator);

            $file = $locator->locate('config.yml', null, false);

            return $conf->load($file);
        });
    }

    public function boot(Application $app)
    {
    }
}

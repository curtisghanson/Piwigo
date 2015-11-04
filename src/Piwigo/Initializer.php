<?php
namespace Piwigo;

use Silex\Application;

class Initializer
{
    public static function initialize(Application &$app)
    {
        $app['debug'] = true;

        // determine the initial instant to indicate the generation time of this page
        $app['t2'] = function() {
            return microtime(true);
        };

        // Register Piwigo Configurations before any other service
        $app->register(new \Piwigo\Provider\ConfigurationServiceProvider());

        // Register Built-in Twig Service Provider
        $app->register(new \Silex\Provider\TwigServiceProvider(), array(
            'twig.path' => __DIR__ . '/Resources/views',
        ));

        // Register Built-in Doctrine Service Provider
        $app->register(new \Silex\Provider\DoctrineServiceProvider(), array(
            'db.options' => array(
                'driver'   => 'pdo_mysql',
                'dbname'   => 'piwigo',
                'charset'  => 'utf8',
                'host'     => 'localhost',
                'port'     => '',
                'user'     => 'root',
                'password' => '12qw!@QW'
            )
        ));

        // Register Built-in Security Service Provider
        $app->register(new \Silex\Provider\SecurityServiceProvider(), array(
            'security.firewalls' => array(
                'default' => array(
                    'anonymous' => true,
            )),
        ));

        // Register Built-in Remember Me Service Provider
        $app->register(new \Silex\Provider\RememberMeServiceProvider());

        // Register Built-in Session Service Provider
        $app->register(new \Silex\Provider\SessionServiceProvider(), array(
            'session.storage.save_path' => __DIR__ . '/../../app/sessions',
        ));

        // Register Built-in Controller Service Provider
        $app->register(new \Silex\Provider\ServiceControllerServiceProvider());

        // Register Built-in Url Generator Service Provider
        $app->register(new \Silex\Provider\UrlGeneratorServiceProvider());

        // Register Built-in Form Service Provider
        $app->register(new \Silex\Provider\FormServiceProvider());

        // Register Built-in Translator Service Provider
        $app->register(new \Silex\Provider\TranslationServiceProvider(), array(
            'translator.messages' => array(),
        ));

        $app['page'] = function() {
            return array(
                'info'    => array(),
                'error'   => array(),
                'warning' => array(),
            );
        };

        $app['user'] = function() {
            return array();
        };

        $app['lang'] = function() {
            return array();
        };

        $app['header_msgs'] = function() {
            return array();
        };

        $app['header_notes'] = function() {
            return array();
        };

        $app['filter'] = function() {
            return array();
        };

    }
}
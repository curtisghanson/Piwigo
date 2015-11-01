<?php
namespace Piwigo\Controller;

use \Twig_Autoloader;
use \Twig_Loader_Filesystem;
use \Twig_Environment;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class Controller
{
    const TWIG_FILESYSTEM = '/../../../src/Piwigo/Resources/views';
    const TWIG_CACHE      = '/../../../app/cache/twig';

    private $twigFilesystem;
    private $twigCache;
    private $loader;
    private $twig;
    private $request;
    private $response;

    public function __construct()
    {
        $this->twigFilesystem = __DIR__ . self::TWIG_FILESYSTEM;
        $this->twigCache      = __DIR__ . self::TWIG_CACHE;

        Twig_Autoloader::register();

        $this->loader = new Twig_Loader_Filesystem($this->twigFilesystem);
        $this->twig   = new Twig_Environment($this->loader, array(
            'cache' => $this->twigCache,
        ));

        /* Build the request and response objects using HttpFoundation */
        $this->request  = Request::createFromGlobals();
        $this->response = new Response();
    }
}

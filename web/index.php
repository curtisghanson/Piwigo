<?php
// web/index.php
require_once __DIR__.'/../vendor/autoload.php';

// Create a new Silex application
$app = new Silex\Application();

// Initializer application
Piwigo\Initializer::initialize($app);

//var_dump($app);exit;

// Boot the application
$app->boot();

// Load routing
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;
use Imagine\Imagick\Imagine;
use Imagine\Image\Box;
use Imagine\Image\ImageInterface;
$app->get('/', function () use ($app) {

    $qb = $app['db']->createQueryBuilder();
    $q = $qb
        ->select('i.file, i.path, i.filesize')
        ->from('piwigo_images', 'i')
        ->execute()
    ;

    $images = $q->fetchAll();

    foreach ($images as &$image)
    {
        $fs       = new Filesystem();
        $file     = new File($image['path']);
        $fileExt  = $file->getExtension();
        $fileSum  = md5($image['file']);
        $lvl1 = 'cache/';
        $lvl2 = $lvl1 . substr($fileSum, 0, 2) . '/';
        $lvl3 = $lvl2 . substr($fileSum, 2, 2) . '/';
        $lvl4 = $lvl3 . $fileSum . '.' . $fileExt;

        if (!$fs->exists($lvl4))
        {
            if (!$fs->exists($lvl2))
            {
                $fs->mkdir($lvl2, 0777);
            }

            if (!$fs->exists($lvl3))
            {
                $fs->mkdir($lvl3, 0777);
            }

            $imagine = new Imagine();
            $size    = new Box(60, 60);
            $mode    = ImageInterface::THUMBNAIL_OUTBOUND;

            $imagine
                ->open($image['path'])
                ->thumbnail($size, $mode)
                ->save($lvl4)
            ;
        }

        $image['path'] = $lvl4;
    }

    return $app['twig']->render('index.html.twig', array(
        'conf'   => $app['piwigo.conf'],
        'images' => $images,
    ));
});

// Run the motherfuckin' app
$app->run();

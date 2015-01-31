<?php

use Silex\Application;
use Silex\Provider\TwigServiceProvider;
use Silex\Provider\UrlGeneratorServiceProvider;
use Symfony\Component\Finder\Finder;
use MotionDetectionViewer\ImageThumbnailService;

$app = new Application();
$app->register(new UrlGeneratorServiceProvider());
$app->register(new TwigServiceProvider(), array(
  'twig.path'    => array(__DIR__.'/../templates'),
  'twig.options' => array('cache' => __DIR__.'/../var/cache/twig'),
));
$app['twig'] = $app->share($app->extend('twig', function($twig, $app) {
  return $twig;
}));
$app['thumbnail_service'] = function($app) {
  return new ImageThumbnailService($app['config']['thumbnailWidth'], $app['config']['thumbnailHeight'], new Finder);
};

$app['config'] = array(
  'imagesPath'        => __DIR__ . '/../web/images/',
  'imagesArchivePath' => __DIR__ . '/../web/images/archive/',
  'thumbnailsPath'    => __DIR__ . '/../web/thumbnails/',
  'thumbnailWidth'    => 150,
  'thumbnailHeight'   => 200,
  'lazyImageLoading'  => true,
);

return $app;

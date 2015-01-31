# Motion Detection Viewer

This is a web based motion detection image viewer. Written in PHP using the Silex micro-framework (based on Symfony2 components).

It can be used to view uploaded images by surveillance network cameras.

Simple function to archive examined images. Mobile phone friendly layout (provided by the Bootstrap framework).

Requires installed ImageMagick (imagick) PHP extension for thumbnail creation.

## Setup

1. Git clone this repository.
2. Install Composer: ```curl -sS https://getcomposer.org/installer | php```
3. Download dependencies: ```php composer.phar install```
4. Make sure the webserver has write permissions to ```var/cache/```, ```web/images/```, ```web/images/archive/``` and ```web/thumbnails```.
5. Make sure the rewrite rule in ```web/.htaccess``` will be executed by your webserver.
6. Configure your network cameras to upload motion detected images to ```web/images/```.
7. Setup a cronjob to execute *http://your-host/cleanup/* once a day to delete archived images that are older than three days.

## Configuration

Edit ```src/app.php```:

```php
$app['config'] = array(
  'imagesPath'        => __DIR__ . '/../web/images/',
  'imagesArchivePath' => __DIR__ . '/../web/images/archive/',
  'thumbnailsPath'    => __DIR__ . '/../web/thumbnails/',
  'thumbnailWidth'    => 150,
  'thumbnailHeight'   => 200,
  'lazyImageLoading'  => false,
);
```

If ```lazyImageLoading``` is set to ```true```, [Unveil.js](https://github.com/luis-almeida/unveil) will be used to lazy load images.

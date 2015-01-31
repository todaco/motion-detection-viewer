<?php

namespace MotionDetectionViewer;

/**
 * Image thumbnail service.
 *
 * Collection of thumbnail related tasks:
 * Create thumbnails, cleanup old thumbnails, move thumbnails.
 */
class ImageThumbnailService
{
  protected $width;
  protected $height;
  protected $finder;
  protected $fileNames = array();
  protected $images = array();

  /**
   * Constructor.
   *
   * @param int    $width   thumbnail width
   * @param int    $height  thumbnail height
   * @param Finder finder   image finder (Symfony\Component\Finder\Finder)
   */
  public function __construct($width, $height, $finder)
  {
    $this->width = $width;
    $this->height = $height;
    $this->finder = $finder;
  }

  /**
   * Return thumbnail width.
   *
   * @return int
   */
  protected function getWidth()
  {
    return $this->width;
  }
  
  /**
   * Return thumbnail height.
   *
   * @return int
   */
  protected function getHeight()
  {
    return $this->height;
  }

  /**
   * Return Finder instance.
   *
   * @return Symfony\Component\Finder\Finder
   */
  protected function getFinder()
  {
    return $this->finder;
  }

  /**
   * Retrieve images for given path.
   *
   * @param string $imagesPath  images directory
   */
  public function retrieve($imagesPath)
  {
    $sortByTime = function(\SplFileInfo $a, \SplFileInfo $b)
    {
      return ($b->getMTime() - $a->getMTime());
    };

    $finder = $this->getFinder();
    $finder->files()
      ->in($imagesPath) 
      ->depth(0)
      ->name('/\.jpg/')
      ->sort($sortByTime);

    $fileNames = array();
    $images = array();
    foreach($finder as $file)
    {
      $images[] = array(
        'thumbnailWidth'  => $this->getWidth(),
        'thumbnailHeight' => $this->getHeight(),
        'url'             => $file->getRelativePathname(),
        'timestamp'       => date('Y-m-d H:i:s', $file->getMTime()),
      );
      $fileNames[] = $file->getRelativePathname();
    }

    $this->fileNames = $fileNames;
    $this->images = $images;
  }

  /**
   * Return images array data.
   *
   * @return array
   */
  public function getImages()
  {
    return $this->images;
  }

  /**
   * Get number of images.
   *
   * @return int
   */
  public function getImagesCount()
  {
    return count($this->images);
  }

  /**
   * Return string of image file names.
   *
   * @return string
   */
  public function getFileNames()
  {
    $files = implode(',', $this->fileNames);

    return $files;
  }

  /**
   * Return timestamp of latest image.
   *
   * @return string
   */
  public function getLatestTimestamp()
  {
    $latestTimestamp = false;
    $images = $this->getImages();

    if (array_key_exists('0', $images))
    {
      $latestTimestamp = $images[0]['timestamp'];
    }

    return $latestTimestamp;
  }

  /**
   * Create thumbnail and return image path.
   *
   * @param string $file              image file name
   * @param string $imagesPath        image source directory
   * @param string $imagesArchivePath image archive directory
   * @param string $thumbnailsPath    thumbnail destination directory
   *
   * @return string image path
   */
  public function getThumbnail($file, $imagesPath, $imagesArchivePath, $thumbnailsPath)
  {
    $thumbnailsPath = realpath($thumbnailsPath);
    $fullImagePath = realpath($imagesPath . $file);

    // maybe $file is already archived but we have no thumbnail yet? try archive directory.
    if ($fullImagePath == false)
    {
      $fullImagePath = realpath($imagesArchivePath . $file);
    }

    if ($fullImagePath == false)
    {
      throw new \Exception("File not found.");
    }

    // security check
    if (strpos($fullImagePath, realpath($imagesPath)) !== 0)
    {
      throw new \Exception("Access denied.");
    }

    $image = new \Imagick($fullImagePath); // @FIXME: use DI for Imagick
    // $image->rotateImage(new ImagickPixel(), 270); // rotate image
    $image->thumbnailImage($this->getWidth(), $this->getHeight());

    $outputImage = $thumbnailsPath . DIRECTORY_SEPARATOR . $file;
    $image->writeImage($outputImage);

    return $outputImage;
  }

  /**
   * Move images (to archive directory).
   *
   * @param array  $filenames       array of image file names
   * @param string $sourceDir       source directory
   * @param string $destinationDir  destination directory
   */
  public function moveToArchive($filenames, $sourceDir, $destinationDir)
  {
    $realSourcePath = realpath($sourceDir);

    foreach ($filenames as $filename)
    {
      $fullSourceImagePath = realpath($sourceDir . $filename);
      if (strpos($fullSourceImagePath, $realSourcePath) !== false)
      {
        $fullDestinationImagePath = $destinationDir . $filename;
        rename($fullSourceImagePath, $fullDestinationImagePath);
      }
    }
  }

  /**
   * Delete archived images that are older than 3 days.
   *
   * @param string $imagesPath      images directory
   * @param string $thumbnailsPath  thumbnails directory
   *
   * @return int number of removed images
   */
  public function cleanup($imagesPath, $thumbnailsPath)
  {
    $finder = $this->getFinder();
    $finder->files()
      ->in($imagesPath)
      ->depth(0)
      ->name('/\.jpg/')
      ->date('until -3 days');

    $count = count($finder);
      
    foreach($finder as $file)
    {
      $fullThumbnailPath = realpath($thumbnailsPath . $file->getRelativePathname());
      unlink($file->getRealpath());
      if ($fullThumbnailPath !== false)
      {
        unlink($fullThumbnailPath);
      }
    }

    return $count;
  }

}
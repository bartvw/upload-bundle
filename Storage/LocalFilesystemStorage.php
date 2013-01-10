<?php
/**
 * Created by JetBrains PhpStorm.
 * User: bart
 * Date: 22-12-12
 * Time: 18:19
 * To change this template use File | Settings | File Templates.
 */

namespace BVW\UploadBundle\Storage;

use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Filesystem\Filesystem;
use Imagine\Image\Box;

class LocalFilesystemStorage extends AbstractStorage {

  private $baseUrl;
  private $basePath;

  public function __construct($baseUrl, $basePath, $secret)
  {
    $this->baseUrl = $baseUrl;
    $this->basePath = $basePath;
    $this->setSecret($secret);
  }

  protected function storeFile(File $file, $name) {
    $file = $file->move($this->basePath, $name);
  }

  protected function storeImage(array $paths, $name)
  {
    foreach($paths as $format => $path) {
      $path_suffix = '';
      if ($format != 'default') {
        $path_suffix = '/' . $format;
      }
      $file = new File($path);
      $file->move($this->basePath . $path_suffix, $name);
    }
  }

  protected function generateUniqueName($originalName)
  {
    return uniqid() . '_' . $originalName;
  }

  public function getPublicUrl($name, $subformat = null) {
    if (null !== $subformat && 'default' !== $subformat) {
      return $this->baseUrl . DIRECTORY_SEPARATOR . $subformat . DIRECTORY_SEPARATOR . $name;
    }
    return $this->baseUrl . DIRECTORY_SEPARATOR . $name;
  }


}
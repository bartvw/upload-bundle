<?php

namespace BVW\UploadBundle\Twig;

use Twig_Extension;
use BVW\UploadBundle\Storage\Storage;

class BVWUploadTwigExtension extends Twig_Extension
{

  private $storage;

  public function __construct(Storage $storage)
  {
    $this->storage = $storage;
  }

  public function getFunctions()
  {
    return array(
      new \Twig_SimpleFunction('image_public_url', array($this, 'getPublicUrl'))
    );
  }


  public function getPublicUrl($name, $format = null)
  {
    return $this->storage->getPublicUrl($name, $format);
  }

  /**
   * Returns the name of the extension.
   *
   * @return string The extension name
   */
  public function getName()
  {
    return 'bvw_upload_extension';
  }


}
<?php
/**
 * Created by JetBrains PhpStorm.
 * User: bart
 * Date: 24-12-12
 * Time: 20:36
 * To change this template use File | Settings | File Templates.
 */
namespace BVW\UploadBundle\Storage;


use Symfony\Component\HttpFoundation\File\File;
use Aws\S3\S3Client;

class S3Storage extends AbstractStorage {

  private $s3;
  private $bucket;
  private $region;

  public function __construct($key, $secret, $region, $bucket, $thumbnailFormats, $imageGenerator)
  {

    $this->s3 = $s3 = S3Client::factory(array(
      'key'    => $key,
      'secret' => $secret,
      'region' => $region,
    ));
    $this->bucket = $bucket;
    $this->region = $region;
    $this->thumbnailFormats = $thumbnailFormats;
    $this->imageGenerator = $imageGenerator;
  }

  public function getPublicUrl($name, $subformat = null)
  {
    if ($subformat && ('default' != $subformat))
    {
      return 'https://s3-' . $this->region . '.amazonaws.com/'
              . $this->bucket . '/' .$subformat  . '/' .  $name;
    }
    return 'https://s3-' . $this->region . '.amazonaws.com/'
        . $this->bucket . '/' . $name;
  }


  protected function storeFile(File $file, $name)
  {
    $fp = fopen($file->getPathname(), "r");
    $result = $this->s3->putObject(array(
      'Bucket' => $this->bucket,
      'Key'    => $name,
      'ContentType' => $file->getMimeType(),
      'Body'   => $fp,
      'ACL'     => 'public-read',
      'ContentMD5' => false
    ));


  }

  protected function storeImage(array $paths, $name)
  {
    foreach($paths as $format => $path) {
      $fp = fopen($path, 'r');
      $result = $this->s3->putObject(array(
        'Bucket' => $this->bucket,
        'Key'    => $format . '/' . $name,
        'ContentType' => 'image/jpeg',
        'Body'   => $fp,
        'ACL'     => 'public-read',
        'ContentMD5' => false
      ));
      fclose($fp);
    }
  }


  protected function generateUniqueName($name)
  {
    return uniqid() . '_' . $name;
  }


}
<?php
/**
 * Created by JetBrains PhpStorm.
 * User: bart
 * Date: 22-12-12
 * Time: 18:13
 * To change this template use File | Settings | File Templates.
 */

namespace BVW\UploadBundle\Storage;

use Symfony\Component\HttpFoundation\File\File;

abstract class AbstractStorage implements Storage
{


  private $secret;

  abstract protected function storeFile(File $file, $name);
  abstract protected function storeImage(array $paths, $name);
  abstract protected function generateUniqueName($name);

  public function createObjectFromFile($file, $originalName = null) {
    $storageObject = new StorageObject();
    $storageObject->setStorage($this);
    $name = $this->generateUniqueName($originalName);
    $storageObject->setName($name);
    $this->storeFile($file, $name);
    return $storageObject;
  }

  public function createImageObjectFromArray($pathArray, $originalName)
  {
    $storageObject = new StorageObject();
    $storageObject->setStorage($this);
    $name = $this->generateUniqueName($originalName);
    $storageObject->setName($name);
    $this->storeImage($pathArray, $name);
    return $storageObject;
  }



	public function getObjectByName($name) {
		$object = new StorageObject();
		$object->setName($name);
		$object->setStorage($this);
    return $object;
  }


  protected function setSecret($secret)
  {
    $this->secret = $secret;
  }

  public function getSignature($name)
  {
    //TODO: find a signature that is safe against replay attacks
    //i.e. include session id?
    return sha1($name . $this->secret);
  }




}
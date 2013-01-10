<?php
/**
 * Created by JetBrains PhpStorm.
 * User: bart
 * Date: 22-12-12
 * Time: 18:35
 * To change this template use File | Settings | File Templates.
 */

namespace BVW\UploadBundle\Storage;

class StorageObject
{

  private $name;


  /**
   * @var Storage
   */
  private $storage;

  public function setName($name)
  {
    $this->name = $name;
  }

  public function getName()
  {
    return $this->name;
  }

  /**
   * @param \Storage $storage
   */
  public function setStorage($storage)
  {
    $this->storage = $storage;
  }

  /**
   * @return \Storage
   */
  public function getStorage()
  {
    return $this->storage;
  }


}
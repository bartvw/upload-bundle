<?php
/**
 * Created by JetBrains PhpStorm.
 * User: bart
 * Date: 22-12-12
 * Time: 18:36
 * To change this template use File | Settings | File Templates.
 */

namespace BVW\UploadBundle\Storage;

interface Storage {

  public function getPublicUrl($name, $subformat = null);
  public function createObjectFromFile($file, $originalName = null);
  public function createImageObjectFromArray($pathArray, $originalName);
  public function getObjectByName($name);


}
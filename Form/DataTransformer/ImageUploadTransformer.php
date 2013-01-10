<?php
/**
 * Created by JetBrains PhpStorm.
 * User: bart
 * Date: 22-12-12
 * Time: 18:39
 * To change this template use File | Settings | File Templates.
 */

namespace BVW\UploadBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use BVW\UploadBundle\Storage\Storage;
use Imagine\Image\Point;
use Symfony\Component\Form\Exception\TransformationFailedException;

class ImageUploadTransformer implements DataTransformerInterface
{

  private $storage;

  public function __construct(Storage $storage) {
    $this->storage = $storage;

  }


  /**
   * Transforms a value from the original representation to a transformed representation.
   *
   * @param mixed $value The value in the original representation
   *
   * @return mixed The value in the transformed representation
   *
   * @throws UnexpectedTypeException   when the argument is not a string
   * @throws TransformationFailedException  when the transformation fails
   */
  public function transform($value)
  {
    if (null === $value) {
      return null;
    }
    if ($object = $this->storage->getObjectByName($value)) {
      return array(
        'signature' => $this->storage->getSignature($value),
        'name' => $value
      );
    }
    return null;
  }

  /**
   * Transforms a value from the transformed representation to its original
   * representation.
   *
   * @param mixed $value The value in the transformed representation
   *
   * @return mixed The value in the original representation
   *
   * @throws UnexpectedTypeException   when the argument is not of the expected type
   * @throws TransformationFailedException  when the transformation fails
   */
  public function reverseTransform($value)
  {
    if (!is_array($value)) {
      return null;
    }

    if (!isset($value['name'])) {
      return null;
    }
    if (!isset($value['signature'])) {
      throw new TransformationFailedException('Missing signature');
    }
    if ($value['signature'] != $this->storage->getSignature($value['name'])) {
      throw new TransformationFailedException('Invalid signature');
    }
    return $value['name'];

  }
}
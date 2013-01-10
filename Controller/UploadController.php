<?php

namespace BVW\UploadBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\JsonResponse;
use Imagine\Image\Point;
use Symfony\Component\HttpFoundation\Response;
use BVW\UploadBundle\Form\Type\ImageUploadType;
use Imagine\Image\ManipulatorInterface;
use Imagine\Image\Box;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * @Route(service="bvw_upload.controller")
 */
class UploadController
{
  private $imageGenerator,
      $storage,
      $uploadBasePath,
      $uploadBaseUrl,
      $formFactory;

  public function __construct($imageGenerator,
                              $storage,
                              $uploadBasePath,
                              $uploadBaseUrl,
                              $formFactory)
  {
    $this->imageGenerator = $imageGenerator;
    $this->storage = $storage;
    $this->uploadBasePath = $uploadBasePath;
    $this->uploadBaseUrl = $uploadBaseUrl;
    $this->formFactory = $formFactory;
  }

  /**
   * @Route("/bvw_upload/upload_temp", name="bvw_uploadbundle_upload_temp")
   * @Template()
   */
  public function uploadTempAction(Request $request)
  {
    $file = $request->files->get('file');
    if (null === $file) {
      return new JSonResponse(array('error' => 'upload error'));
    }

    try {
      $imageSize = $this->imageGenerator->getImageSize($file);
      $token = uniqid() . '.' . $file->guessExtension();
      $file->move($this->uploadBasePath, $token);

      // attempt to load the image, will throw an exception if it's not a valid image
      return new JsonResponse(array(
        'file' => array(
          'token' => $token,
          'filename' => $file->getClientOriginalName(),
          'url' => $this->uploadBaseUrl . '/' . $token,
          'width' => $imageSize['width'],
          'height' => $imageSize['height']
        )));
    } catch (\Imagine\Exception\InvalidArgumentException $e) {
      return new JSonResponse(array('error' => 'The selected file is not a valid image'));
    } catch (Exception $e) {
      return new JSonResponse(array('error' => 'unkown error')); // TODO: more helpful message
    }
  }

  /**
   * @Route("/bvw-image-upload/store", name="bvw_upload_image_store")
   * @param $token
   * @param $format
   * @return \Symfony\Component\HttpFoundation\Response
   */
  public function storeAction(Request $request)
  {
    $form = $this->formFactory->createBuilder('form')
        ->add('x')->add('y')->add('w')->add('h')
        ->add('token')
        ->add('format')
        ->add('return_thumbnail')
        ->add('filename')
        ->getForm();
    $form->bind($request);
    $data = $form->getData();

    $originalName = str_replace('\\', '/', $data['filename']);
    $pos = strrpos($originalName, '/');
    $originalName = false === $pos ? $originalName : substr($originalName, $pos + 1);

    $file = new File($this->uploadBasePath . DIRECTORY_SEPARATOR . $data['token']);
    if ($file->getPath() != $this->uploadBasePath) {
      throw new Exception('file not in upload base path');
    }

    $renderedImages = $this->imageGenerator->generateFromFile($file, $data['format'],
      array('x' => $data['x'], 'y' => $data['y'], 'w' => $data['w'], 'h' => $data['h']));
    $storageObject = $this->storage->createImageObjectFromArray($renderedImages, $originalName);
    return new JsonResponse(array(
        'thumbnail_url' =>
        $this->storage->getPublicUrl($storageObject->getName(), $data['return_thumbnail']),
        'name' => $storageObject->getName(),
        'signature' => $this->storage->getSignature($storageObject->getName())
      )
    );




  }


}

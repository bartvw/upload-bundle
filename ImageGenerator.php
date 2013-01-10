<?php

namespace BVW\UploadBundle;

use Imagine\Image\Point;
use Imagine\Image\Color;
use Imagine\Image\Box;

class ImageGenerator {

  private $imagine;

  public function __construct($imagine, $settings)
  {
    $this->imagine = $imagine;
    $this->temp_dir = $settings['temp_dir'];
    $this->formats = $settings['formats'];
  }

  public function getImageSize($file)
  {
    $image = $this->imagine->open($file->getPathname());
    return array(
      'width' => $image->getSize()->getWidth(),
      'height' => $image->getSize()->getHeight()
    );
  }

  public function generateFromFile($path, $format, $crop = false)
  {
    $image = $this->imagine->open($path);
    if (is_array($crop) && $crop['w'] > 0 && $crop['h'] > 0) {
      $image->crop(new Point($crop['x'], $crop['y']), new Box($crop['w'], $crop['h']));
    }

    $width  = $image->getSize()->getWidth();
    $height = $image->getSize()->getHeight();

    $result = array();
    $formatSpec = $this->formats[$format];
    foreach($formatSpec as $name => $settings)
    {
      $ratios = array(
          $settings['width'] / $width,
          $settings['height'] / $height
      );
      $targetSize = new Box($settings['width'], $settings['height']);

      switch($settings['mode']) {
        case 'fit':
        case 'scale':
        case 'limit':
          if ($settings['mode'] == 'limit' && $targetSize->contains($image->getSize())) {
            $thumb = $image;
            break;
          }
          $img = $image->copy();
          $ratio = min($ratios);
          $scaledSize = $image->getSize()->scale($ratio);
          $img->resize($scaledSize);
          if ($settings['mode'] != 'fit') {
            $thumb = $img;
            break;
          }
          $color = new Color($settings['background_color'], 100);
          $thumb = $this->imagine->create($targetSize, $color);
          $thumb->paste($img, new Point(
              max(0, ($targetSize->getWidth() - $scaledSize->getWidth()) / 2.0),
              max(0, ($targetSize->getHeight() - $scaledSize->getHeight()) / 2.0)));
          break;
        case 'inflate':
        case 'deflate':
          $thumb = $image->copy();
          $thumb->resize($targetSize);
          break;
        case 'center':
          $thumb = $image->copy();
          $ratio = max($ratios);
          $scaledSize = $image->getSize()->scale($ratio);
          $thumb->resize($scaledSize);
          $thumb->crop(new Point(
                          max(0, round(($scaledSize->getWidth() - $targetSize->getWidth()) / 2)),
                          max(0, round(($scaledSize->getHeight() - $targetSize->getHeight()) / 2))
                      ), $targetSize);
          break;
      }
      $outputPath = tempnam($this->temp_dir, 'upload');
      $thumb->save($outputPath, array('format' => 'jpg'));
      $result[$name] = $outputPath;
    }
    return $result;
  }

  public function getFormat($format)
  {
    return $this->formats[$format];
  }



}
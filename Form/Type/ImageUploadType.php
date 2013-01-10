<?php

namespace BVW\UploadBundle\Form\Type;

use BVW\UploadBundle\Form\DataTransformer\ImageUploadTransformer;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\AbstractType;

class ImageUploadType extends AbstractType {

  private $storage;
  private $imageGenerator;

  public function __construct($storage, $imageGenerator)
  {
    $this->storage = $storage;
    $this->imageGenerator = $imageGenerator;
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(FormBuilderInterface $builder, array $options)
  {
    $builder
        ->addViewTransformer(new ImageUploadTransformer(
            $this->storage))
        ->add('name', 'hidden')
        ->add('signature', 'hidden');
  }

  /**
   * {@inheritdoc}
   */
  public function buildView(FormView $view, FormInterface $form, array $options)
  {
    $data = $form->getViewData();

    if ($options['format'] && $options['use_aspect_ratio'])
    {
      $format = $this->imageGenerator->getFormat($options['format']);
      $aspectRatio = (double) $format['default']['width'] / $format['default']['height'];
    } elseif ($options['use_aspect_ratio']) {
      throw new InvalidOptionsException('use_aspect_ratio requires target_format to be set');
    } else {
      $aspectRatio = false;
    }

    $view->vars = array_replace($view->vars, array(
      'preview_url'   => $this->storage->getPublicUrl($data['name'], $options['preview']),
      'aspect_ratio' => $aspectRatio ?: 'false',
      'thumbnail_preview_subformat' => $options['preview'],
      'target_format' => $options['format'],
      'crop_area_width' => $options['crop_area_width'],
      'crop_area_height' => $options['crop_area_height'],
      'unique_id' => uniqid("upload-widget")
    ));
  }

  /**
   * {@inheritdoc}
   */
  public function setDefaultOptions(OptionsResolverInterface $resolver)
  {
    $resolver->setDefaults(array(
      'preview'      => 'default',
      'crop_area_width' => 530,       // width of the cropping area
      'crop_area_height' => 330,      // height of the cropping area
      'use_aspect_ratio' => false,     // force aspect ratio for cropping tool to target format
                                      // (requires target format to be set)
    ));
    $resolver->setRequired(array('format'));
  }


  /**
   * {@inheritdoc}
   */
  public function getName()
  {
    return 'image_upload';
  }

}
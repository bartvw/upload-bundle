<?php

namespace BVW\UploadBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class BVWUploadExtension extends Extension
{
  /**
   * {@inheritDoc}
   */
  public function load(array $configs, ContainerBuilder $container)
  {
    $configuration = new Configuration();
    $config = $this->processConfiguration($configuration, $configs);

    $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
    $loader->load('services.yml');

    $container->setParameter('bvw_upload.upload_base_path', $config['upload']['base_path']);
    $container->setParameter('bvw_upload.upload_base_url', $config['upload']['base_url']);
    $container->setParameter('bvw_upload.storage_base_path', $config['localstorage']['base_path']);
    $container->setParameter('bvw_upload.storage_base_url', $config['localstorage']['base_url']);
    $container->setParameter('bvw_upload.formats', $config['formats']);
    $container->setParameter('bvw_upload.secret', $config['secret']);

    if (array_key_exists('s3storage', $config)) {
      $container->setParameter('bvw_upload.s3.key', $config['s3storage']['key']);
      $container->setParameter('bvw_upload.s3.secret', $config['s3storage']['secret']);
      $container->setParameter('bvw_upload.s3.bucket', $config['s3storage']['bucket']);
      $container->setParameter('bvw_upload.s3.region', $config['s3storage']['region']);
    } else {
      $container->setParameter('bvw_upload.s3.key', '');
      $container->setParameter('bvw_upload.s3.secret', '');
      $container->setParameter('bvw_upload.s3.bucket', '');
      $container->setParameter('bvw_upload.s3.region', '');
    }

    switch($config['storage']) {
      case 's3':
        $container->setAlias('bvw_upload.storage', 'bvw_uploadbundle_s3_file_storage');
        break;
      case 'local':
        $container->setAlias('bvw_upload.storage', 'bvw_uploadbundle_local_file_storage');
    }

    $container->setParameter('twig.form.resources', array_merge(
      $container->getParameter('twig.form.resources'),
      array('BVWUploadBundle:Form:imageUpload.html.twig')
    ));
  }
}

<?php

namespace BisonLab\ContextBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration
 *
 */
class BisonLabContextExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $config = $this->processConfiguration($this->getConfiguration($configs, $container), $configs);

        $loader = new Loader\YamlFileLoader($container,
            new FileLocator(array(
                __DIR__.'/../../config',
                $container->getParameter('kernel.project_dir').'/config/packages'
                )
            ));
        $loader->load('services.yaml');
        $loader->load('contexts.yaml');
    }
}

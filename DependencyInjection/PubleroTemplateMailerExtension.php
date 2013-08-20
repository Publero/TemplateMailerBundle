<?php

/*
 * This file is part of the PubleroTemplateMailerBundle package.
 *
 * (c) Tomas Pecserke <tomas@pecserke.eu>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Publero\TemplateMailerBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class PubleroTemplateMailerExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $processor = new Processor();
        $configuration = new Configuration();

        $config = $processor->processConfiguration($configuration, $configs);

        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');

        $gearmanClientDefinition = $container->getDefinition('publero_template_mailer.gearman_client');
        $gearmanClientDefinition->addMethodCall('addServers', array($config['gearman_servers']));

        if (null !== $config['template_storage']['type']) {
            $loader->load('template_storage.yml');

            switch ($config['template_storage']['type']) {
                case 'doctrine':
                    $backend = $config['template_storage']['backend'];
                    $container->setParameter('publero_template_mailer.backend.' . $backend, true);
                    $definition = $container->getDefinition('publero_template_mailer.template.storage.doctrine');
                    switch ($backend) {
                        case 'orm':
                            $objectManagerId = 'doctrine.orm.entity_manager';
                            break;
                        case 'mongodb':
                            $objectManagerId = 'doctrine_mongodb.odm.document_manager';
                            break;
                    }
                    $definition->replaceArgument(2, new Reference($objectManagerId));
                    break;
                case 'service':
                    $container->setAlias('publero_template_mailer.template.storage', $config['template_storage']['id']);
                    break;

            }
        }
    }
}

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

        $definition = $container->getDefinition('publero_template_mailer.gearman_client');
        $definition->addMethodCall('addServers', array($config['gearman_servers']));

        if ('gearman' === $config['client']['plain_mailer']['type']) {
            $definition = $container->getDefinition('publero_template_mailer.client.message.gearman');
            $definition->replaceArgument(1, $config['client']['plain_mailer']['function_name']);
        }
        if ('gearman' === $config['client']['template_mailer']['type']) {
            $definition = $container->getDefinition('publero_template_mailer.client.template.gearman');
            $definition->replaceArgument(1, $config['client']['template_mailer']['function_name']);
        }
        if ('gearman' === $config['client']['remote_storage']['type']) {
            $definition = $container->getDefinition('publero_template_mailer.client.remote_storage.gearman');
            $definition->replaceArgument(1, $config['client']['remote_storage']['upload_function_name']);
            $definition->replaceArgument(2, $config['client']['remote_storage']['remove_function_name']);
        }

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
                    if (!empty($objectManagerId)) {
                        $definition->replaceArgument(1, new Reference($objectManagerId));
                    }
                    break;
                case 'service':
                    $container->setAlias('publero_template_mailer.template.storage', $config['template_storage']['id']);
                    break;
            }

            if (!empty($config['template_storage']['template_processor'])) {
                $definition = $container->getDefinition('publero_template_mailer.template.storage.doctrine');
                $definition->replaceArgument(2, new Reference($config['template_storage']['template_processor']));
            }
        }
    }
}

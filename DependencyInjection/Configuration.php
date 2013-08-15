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

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('publero_template_mailer');

        $rootNode
            ->children()
                ->arrayNode('gearman_servers')
                    ->beforeNormalization()
                        ->ifString()
                        ->then(function($v) { return array($v); })
                    ->end()
                    ->prototype('array')
                        ->beforeNormalization()
                            ->ifString()
                            ->then(function($v) { return array('host' => $v); })
                        ->end()
                        ->children()
                            ->scalarNode('host')->isRequired()->end()
                            ->integerNode('port')->defaultNull()->end()
                        ->end()
                    ->end()
                    ->isRequired()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}

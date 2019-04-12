<?php

namespace Aequasi\Bundle\MemcachedBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * EnableSessionSupport is a compiler pass to set the session handler.
 * Based on Emagister\MemcachedBundle by Christian Soronellas
 */
class EnableSessionSupport implements CompilerPassInterface
{
    /**
     * {@inheritDoc}
     */
    public function process(ContainerBuilder $container)
    {
        // If there is no active session support, return
        if ( ! $container->hasAlias('session.storage')) {
            return;
        }
        // If the memcache.session_handler service is loaded set the alias
        if ($container->hasDefinition('memcached.session_handler')) {
            $container->setAlias('session.handler', 'memcached.session_handler');
        }
    }
}

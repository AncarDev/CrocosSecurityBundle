<?php

namespace Crocos\SecurityBundle\DependencyInjection\Compiler;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

/**
 * FacebookAuthPass.
 *
 * @author Katsuhiro Ogawa <ogawa@crocos.co.jp>
 */
class FacebookAuthPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->has('facebook.api')) {
            return;
        }

        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../../Resources/config'));
        $loader->load('facebook.yml');

        $facebookAuth = $container->getDefinition('crocos_security.auth_logic.facebook');

        // tags:
        //   - { name: crocos_security.facebook_role_loader, alias: group }
        foreach ($container->findTaggedServiceIds('crocos_security.facebook_role_loader') as $id => $attributes) {
            $facebookAuth->addMethodCall('registerRoleLoader', array($attributes[0]['alias'], new Reference($id)));
        }
    }
}

<?php

namespace Crocos\SecurityBundle\Tests\DependencyInjection;

use Crocos\SecurityBundle\DependencyInjection\CrocosSecurityExtension;
use Crocos\SecurityBundle\DependencyInjection\Compiler;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class CrocosSecurityExtensionTest extends \PHPUnit_Framework_TestCase
{
    public function testLoadContainer()
    {
        $container = $this->getContainer();
    }

    protected function getContainer(array $config = [], $debug = false)
    {
        $container = new ContainerBuilder();
        $container->setParameter('kernel.debug', $debug);
        $container->getCompilerPassConfig()->setOptimizationPasses([]);
        $container->getCompilerPassConfig()->setRemovingPasses([]);

        $container->addCompilerPass(new Compiler\AuthLogicPass());
        $container->addCompilerPass(new Compiler\HttpAuthFactoryPass());
        $container->addCompilerPass(new Compiler\RoleManagerPass());

        $loader = new CrocosSecurityExtension();
        $loader->load($config, $container);
        $container->compile();

        return $container;
    }
}
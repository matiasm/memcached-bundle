<?php
/**
 * @author    Aaron Scherer <aequasi@gmail.com>
 * @date 2013
 * @license   http://www.apache.org/licenses/LICENSE-2.0.html Apache License, Version 2.0
 */

namespace Aequasi\Bundle\MemcachedBundle\Tests;

use Doctrine\Common\Annotations\AnnotationReader;

use Aequasi\Bundle\MemcachedBundle\DependencyInjection\AequasiMemcachedExtension;

use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\DependencyInjection\Compiler\ResolveDefinitionTemplatesPass;

class MemcachedBundle extends TestCase
{

	/**
	 * @return ContainerBuilder
	 */
	public function createYamlBundleTestContainer()
	{
		$container = new ContainerBuilder( new ParameterBag( array(
			'kernel.debug'       => false,
			'kernel.bundles'     => array( 'YamlBundle' => 'Fixtures\Bundles\YamlBundle\YamlBundle' ),
			'kernel.cache_dir'   => sys_get_temp_dir(),
			'kernel.environment' => 'test',
			'kernel.root_dir'    => __DIR__ . '/../../../../' // src dir
		) ) );
		$container->set( 'annotation_reader', new AnnotationReader() );
		$loader = new AequasiMemcachedExtension();
		$container->registerExtension( $loader );
		$loader->load(
			array(
				array(
					'clusters' => array(
						'default' => array(
							'hosts' => array(
								array(
									'host' => 'localhost',
									'port' => 11211,
								)
							),
							'keyMap' => array(
								'enabled' => false,
							)
						)
					)
				)
			),
			$container
		);

		$container->getCompilerPassConfig()->setOptimizationPasses( array( new ResolveDefinitionTemplatesPass() ) );
		$container->getCompilerPassConfig()->setRemovingPasses( array() );
		$container->compile();

		return $container;
	}
}

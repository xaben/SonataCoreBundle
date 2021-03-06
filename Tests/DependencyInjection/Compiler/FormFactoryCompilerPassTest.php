<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\CoreBundle\Tests\DependencyInjection\Compiler;

use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractCompilerPassTestCase;
use Sonata\CoreBundle\DependencyInjection\Compiler\FormFactoryCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

/**
 * @author Ahmet Akbana <ahmetakbana@gmail.com>
 */
final class FormFactoryCompilerPassTest extends AbstractCompilerPassTestCase
{
    /**
     * {@inheritdoc}
     */
    public function registerCompilerPass(ContainerBuilder $container)
    {
        $container->addCompilerPass(new FormFactoryCompilerPass());
    }

    public function testProcessWithContainerHasNoFormExtensionDefinition()
    {
        $formType = new Definition();
        $formType->addTag('form.type');
        $this->setDefinition('foo', $formType);
        $this->setDefinition('bar', $formType);

        $formTypeExtension = new Definition();
        $formTypeExtension->addTag('form.type_extension');
        $this->setDefinition('baz', $formTypeExtension);
        $this->setDefinition('caz', $formTypeExtension);

        $this->compile();

        $taggedFormTypes = $this->container->getParameter('sonata.core.form.types');
        $this->assertSame($taggedFormTypes, array('foo', 'bar'));

        $taggedFormTypes = $this->container->getParameter('sonata.core.form.type_extensions');
        $this->assertSame($taggedFormTypes, array('baz', 'caz'));
    }

    public function testProcessWithContainerHasFormExtensionDefinition()
    {
        $formExtension = new Definition(null, array(null, null, null, null));
        $this->setDefinition('form.extension', $formExtension);

        $formType = new Definition();
        $formType->addTag('form.type');
        $this->setDefinition('foo', $formType);

        $sonataFormExtension = new Definition(null, array(null, null, null, null));
        $this->setDefinition('sonata.core.form.extension.dependency', $sonataFormExtension);

        $this->compile();

        $this->assertSame($sonataFormExtension, $this->container->getDefinition('form.extension'));

        $this->assertContains('foo', $sonataFormExtension->getArgument(1));
        $this->assertSame(array(), $sonataFormExtension->getArgument(2));
        $this->assertSame(array(), $sonataFormExtension->getArgument(3));
    }
}

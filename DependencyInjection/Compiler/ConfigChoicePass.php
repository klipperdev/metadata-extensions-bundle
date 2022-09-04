<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Bundle\MetadataExtensionsBundle\DependencyInjection\Compiler;

use Klipper\Component\Choice\ChoiceInterface;
use Klipper\Component\Choice\NameableChoiceInterface;
use Klipper\Component\Choice\PlaceholderableChoiceInterface;
use Klipper\Component\Metadata\ChoiceBuilder;
use Klipper\Component\Metadata\Util\MetadataUtil;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\PriorityTaggedServiceTrait;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class ConfigChoicePass implements CompilerPassInterface
{
    use PriorityTaggedServiceTrait;

    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasDefinition('klipper_metadata.registry')) {
            return;
        }

        $registryDef = $container->getDefinition('klipper_metadata.registry');
        $choices = $container->getParameter('klipper_metadata_extensions.config.choices');

        foreach ($choices as $choice) {
            if (class_exists($choice)
                && interface_exists(ChoiceInterface::class)
                && is_a($choice, ChoiceInterface::class, true)
            ) {
                $name = MetadataUtil::getObjectName($choice);
                $placeholder = null;

                $identifiersCallback = [$choice, 'listIdentifiers'];
                $valuesCallback = [$choice, 'getValues'];
                $translationDomainCallback = [$choice, 'getTranslationDomain'];

                if (is_a($choice, NameableChoiceInterface::class, true)) {
                    $nameCallback = [$choice, 'getName'];
                    $name = $nameCallback();
                }

                if (is_a($choice, PlaceholderableChoiceInterface::class, true)) {
                    $placeholderCallback = [$choice, 'getPlaceholder'];
                    $placeholder = $placeholderCallback();
                }

                $identifiers = $identifiersCallback();
                $values = $valuesCallback();
                $translationDomain = $translationDomainCallback();

                if (!empty($identifiers)) {
                    $choiceDef = new Definition(ChoiceBuilder::class, [
                        $name,
                        $translationDomain,
                        $identifiers,
                        $values,
                        $placeholder,
                    ]);

                    $id = 'klipper_metadata_extensions.config_choice.'.$name;
                    $container->setDefinition($id, $choiceDef);

                    $registryDef->addMethodCall('addChoice', [new Reference($id)]);
                }
            }
        }

        $container->getParameterBag()->remove('klipper_metadata_extensions.config.choices');
    }
}

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

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\PriorityTaggedServiceTrait;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class GuessSymfonyConstraintPass implements CompilerPassInterface
{
    use PriorityTaggedServiceTrait;

    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasDefinition('klipper_metadata_extensions.guess.symfony_constraint_config')) {
            return;
        }

        $def = $container->getDefinition('klipper_metadata_extensions.guess.symfony_constraint_config');
        $guessers = $this->findAndSortTaggedServices('klipper_metadata_extensions.guess.symfony_constraint', $container);

        $def->setArgument(1, $guessers);
    }
}

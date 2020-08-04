<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Bundle\MetadataExtensionsBundle\DependencyInjection;

use Doctrine\ORM\EntityManager;
use Gedmo\Translatable\TranslatableListener;
use JMS\SerializerBundle\JMSSerializerBundle;
use Klipper\Component\MetadataExtensions\Guess\GuessConstraint\UserPasswordGuessConstraint;
use Klipper\Component\Security\Permission\PermissionManager;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;
use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;
use Symfony\Component\Translation\Translator;
use Symfony\Component\Validator\Validation;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class KlipperMetadataExtensionsExtension extends Extension
{
    /**
     * @throws \Exception
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));

        if (class_exists(EntityManager::class)) {
            $loader->load('guess_doctrine.xml');

            if (class_exists(TranslatableListener::class)) {
                $loader->load('guess_doctrine_translatable.xml');
            }

            $this->configGuessDoctrine($container, $config['guessers']['doctrine']);
        }

        if (class_exists(JMSSerializerBundle::class)) {
            $loader->load('guess_jms_serializer.xml');
        }

        if (class_exists(Validation::class)) {
            $loader->load('guess_symfony_constraint.xml');
            $this->configGuessConstraint($container);
        }

        if (class_exists(Translator::class)) {
            $loader->load('guess_translator.xml');
        }

        if (class_exists(Form::class)) {
            if (class_exists(PermissionManager::class)) {
                $loader->load('form.xml');
            }

            $loader->load('guess_form.xml');
            $loader->load('guess_input.xml');
            $this->configGuessForm($container, $config['guessers']['form']);
        }

        if (class_exists(PermissionManager::class)
                && class_exists(Translator::class)
                && class_exists(AuthorizationChecker::class)) {
            $loader->load('permission_metadata.xml');
        }
    }

    /**
     * Configure the doctrine guessers.
     *
     * @param ContainerBuilder $container The container
     * @param array            $config    The cache config
     */
    private function configGuessDoctrine(ContainerBuilder $container, array $config): void
    {
        $container->getDefinition('klipper_metadata_extensions.guess.doctrine_config')
            ->replaceArgument(1, $config['mapping_field_types'])
            ->replaceArgument(2, $config['mapping_association_types'])
            ->replaceArgument(3, $config['mapping_field_type_inputs'])
        ;
    }

    /**
     * Configure the symfony constraint guessers.
     *
     * @param ContainerBuilder $container The container
     */
    private function configGuessConstraint(ContainerBuilder $container): void
    {
        if (class_exists(UserPassword::class)) {
            $def = new Definition(UserPasswordGuessConstraint::class);
            $def->addTag('klipper_metadata_extensions.guess.symfony_constraint');

            $container->setDefinition(UserPasswordGuessConstraint::class, $def);
        }
    }

    /**
     * Configure the symfony form guessers.
     *
     * @param ContainerBuilder $container The container
     * @param array            $config    The cache config
     */
    private function configGuessForm(ContainerBuilder $container, array $config): void
    {
        $container->getDefinition('klipper_metadata_extensions.guess.form_type')
            ->replaceArgument(0, $config['mapping_input_types'])
            ->replaceArgument(1, $config['form_options'])
        ;
    }
}

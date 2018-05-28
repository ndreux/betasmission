<?php

namespace BetasMissionBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class BetasMissionExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config        = $this->processConfiguration($configuration, $configs);

        $container->setParameter('betas_mission.betaseries.login', $config['betaseries']['login']);
        $container->setParameter('betas_mission.betaseries.password_hash', $config['betaseries']['password_hash']);
        $container->setParameter('betas_mission.betaseries.api_base_path', $config['betaseries']['api_base_path']);
        $container->setParameter('betas_mission.betaseries.api_key', $config['betaseries']['api_key']);

        $container->setParameter('betas_mission.trakt_tv.api_base_path', $config['trakt_tv']['api_base_path']);
        $container->setParameter('betas_mission.trakt_tv.client_id', $config['trakt_tv']['client_id']);
        $container->setParameter('betas_mission.trakt_tv.client_secret', $config['trakt_tv']['client_secret']);
        $container->setParameter('betas_mission.trakt_tv.application_pin', $config['trakt_tv']['application_pin']);
        $container->setParameter('betas_mission.trakt_tv.access_token', $config['trakt_tv']['access_token']);
        $container->setParameter('betas_mission.trakt_tv.refresh_token', $config['trakt_tv']['refresh_token']);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('apiWrappers.yml');
        $loader->load('business.yml');
        $loader->load('commandHelpers.yml');
    }
}

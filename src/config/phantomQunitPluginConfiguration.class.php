<?php

/**
 * phantomQunitPlugin configuration.
 * 
 * @package     phantomQunitPlugin
 * @subpackage  config
 * @author      Joshua Spankin Nankin
 * @version     SVN: $Id: PluginConfiguration.class.php 17207 2009-04-10 15:36:26Z Kris.Wallsmith $
 */
class phantomQunitPluginConfiguration extends sfPluginConfiguration {
    const VERSION = '1.0.0-DEV';

    /**
     * @see sfPluginConfiguration
     */
    public function initialize() {
        $configFiles = $this->configuration->getConfigPaths('config/phantomQunit.yml');
        $config = sfDefineEnvironmentConfigHandler::getConfiguration($configFiles);

        foreach ($config as $name => $value) {
            sfConfig::set("phantomqunit_{$name}", $value);
        }

        $this->configuration->getEventDispatcher()->connect('plugin.post_install', array($this, 'postInstall'));
    }

    public function postInstall(sfEvent $event) {
        $initTask = new phantomQunitRunTask($this->configuration->getEventDispatcher(), new sfAnsiColorFormatter());
        $initTask->run();
    }

}

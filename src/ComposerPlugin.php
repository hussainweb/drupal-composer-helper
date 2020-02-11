<?php

namespace Hussainweb\DrupalComposerHelper;

use Composer\Composer;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;
use Composer\Script\Event;
use Composer\Script\ScriptEvents;

class ComposerPlugin implements PluginInterface, EventSubscriberInterface
{

    /**
     * @var Composer
     */
    private $composer;

    /**
     * @var IOInterface
     */
    private $io;

    /**
     * @var Options
     */
    private $options;

    /**
     * {@inheritdoc}
     */
    public function activate(Composer $composer, IOInterface $io)
    {
        $this->composer = $composer;
        $this->io = $io;
        $this->options = new Options($composer);

        // Set sane defaults for Drupal installer paths.
        $composer_installers_helper = new ComposerInstallersHelper($composer, $this->options);
        $composer_installers_helper->setInstallerPaths();
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        // Set the priorities so that cleanup actions are called at the end.
        return [
            ScriptEvents::POST_INSTALL_CMD => [
                ['createDrupalFiles', -1],
            ],
            ScriptEvents::POST_UPDATE_CMD => [
                ['createDrupalFiles', -1],
            ],
        ];
    }

    public function createDrupalFiles(Event $event)
    {
        $drupalFiles = new DrupalFiles($this->io, $this->options);
        $drupalFiles->createRequiredFiles();
    }
}

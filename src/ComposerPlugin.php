<?php

namespace Hussainweb\DrupalComposerHelper;

use Composer\Composer;
use Composer\DependencyResolver\Operation\InstallOperation;
use Composer\DependencyResolver\Operation\UpdateOperation;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\Installer\PackageEvent;
use Composer\Installer\PackageEvents;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;
use Composer\Script\Event;
use Composer\Script\ScriptEvents;
use DrupalComposer\DrupalScaffold\Handler as DrupalScaffoldHandler;

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
            PackageEvents::POST_PACKAGE_INSTALL => ['cleanupVendorFiles', -100],
            PackageEvents::PRE_PACKAGE_INSTALL => ['cleanupVendorFiles', -100],
            ScriptEvents::POST_INSTALL_CMD => [
                ['createDrupalFiles', -1],
                ['cleanupAdditionalFiles', -100],
            ],
            ScriptEvents::POST_UPDATE_CMD => [
                ['createDrupalFiles', -1],
                ['cleanupAdditionalFiles', -100],
            ],
            DrupalScaffoldHandler::POST_DRUPAL_SCAFFOLD_CMD => 'createDrupalFiles',
        ];
    }

    public function cleanupVendorFiles(PackageEvent $event)
    {
        $op = $event->getOperation();
        if ($op instanceof UpdateOperation) {
            $package = $op->getTargetPackage();
        } elseif ($op instanceof InstallOperation) {
            $package = $op->getPackage();
        } else {
            // We shouldn't really reach here, but just in case.
            return;
        }

        $vendor_dir = $this->composer->getConfig()->get('vendor-dir');
        $drupalVendorCleanup = new DrupalVendorCleanup($vendor_dir, $this->io);
        $drupalVendorCleanup->vendorTestCodeCleanup($package);
    }

    public function cleanupAdditionalFiles(Event $event)
    {
        $additional_files = $this->options->get('additional-cleanup');
        if ($additional_files) {
            $vendor_dir = $this->composer->getConfig()->get('vendor-dir');
            $drupalVendorCleanup = new DrupalVendorCleanup($vendor_dir, $this->io);
            $drupalVendorCleanup->additionalCleanup($additional_files);
        }
    }

    public function createDrupalFiles(Event $event)
    {
        $drupalFiles = new DrupalFiles($this->io, $this->options);
        $drupalFiles->createRequiredFiles();
    }
}

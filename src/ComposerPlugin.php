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
     * @var DrupalVendorCleanup
     */
    private $drupalVendorCleanup;

    private $options;

    /**
     * {@inheritdoc}
     */
    public function activate(Composer $composer, IOInterface $io)
    {
        $this->composer = $composer;
        $this->io = $io;
        $this->options = new Options($composer);

        $vendor_dir = $composer->getConfig()->get('vendor-dir');
        $this->drupalVendorCleanup = new DrupalVendorCleanup($vendor_dir, $io);

        // Set sane defaults for Drupal installer paths.
        $composer_installers_helper = new ComposerInstallersHelper($composer, $this->options);
        $composer_installers_helper->setInstallerPaths();
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            PackageEvents::POST_PACKAGE_INSTALL => 'cleanupVendorFiles',
            PackageEvents::PRE_PACKAGE_INSTALL => 'cleanupVendorFiles',
            ScriptEvents::POST_INSTALL_CMD => 'createDrupalFiles',
            ScriptEvents::POST_UPDATE_CMD => 'createDrupalFiles',
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

        $this->drupalVendorCleanup->vendorTestCodeCleanup($package);
    }

    public function createDrupalFiles(Event $event)
    {
        DrupalFiles::createRequiredFiles($event->getIO());
    }
}

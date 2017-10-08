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
    protected $drupalVendorCleanup;

    /**
     * {@inheritdoc}
     */
    public function activate(Composer $composer, IOInterface $io)
    {
        $this->composer = $composer;
        $this->io = $io;

        $vendor_dir = $composer->getConfig()->get('vendor-dir');
        $this->drupalVendorCleanup = new DrupalVendorCleanup($vendor_dir, $io);
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            PackageEvents::POST_PACKAGE_INSTALL => 'cleanupVendorFiles',
            PackageEvents::PRE_PACKAGE_INSTALL => 'cleanupVendorFiles',
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
}

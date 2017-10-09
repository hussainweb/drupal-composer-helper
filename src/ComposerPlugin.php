<?php

namespace Hussainweb\DrupalComposerHelper;

use Composer\Composer;
use Composer\DependencyResolver\Operation\InstallOperation;
use Composer\DependencyResolver\Operation\UpdateOperation;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\Installer\PackageEvent;
use Composer\Installer\PackageEvents;
use Composer\IO\IOInterface;
use Composer\IO\NullIO;
use Composer\Plugin\PluginInterface;
use Composer\Repository\ComposerRepository;
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

        // Add drupal.org packagist to repositories if not present.
        $this->addDrupalRepository();
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

        $vendor_dir = $this->composer->getConfig()->get('vendor-dir');
        $drupalVendorCleanup = new DrupalVendorCleanup($vendor_dir, $this->io);
        $drupalVendorCleanup->vendorTestCodeCleanup($package);
    }

    public function createDrupalFiles(Event $event)
    {
        $drupalFiles = new DrupalFiles($this->io, $this->options);
        $drupalFiles->createRequiredFiles();
    }

    public function addDrupalRepository()
    {
        $repositories = $this->composer->getPackage()->getRepositories();
        $already_present = array_reduce($repositories, function ($carry, $item) {
            return $carry
                || ($item['type'] == 'composer' && $item['url'] == 'https://packages.drupal.org/8');
        }, false);

        if (!$already_present) {
            $repo = new ComposerRepository([
                'type' => 'composer',
                'url' => 'https://packages.drupal.org/8',
            ], new NullIO(), $this->composer->getConfig());
            $this->composer->getRepositoryManager()->addRepository($repo);
        }
    }
}

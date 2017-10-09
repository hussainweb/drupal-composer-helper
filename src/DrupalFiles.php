<?php

namespace Hussainweb\DrupalComposerHelper;

use Composer\IO\IOInterface;
use Symfony\Component\Filesystem\Filesystem;
use Webmozart\PathUtil\Path;

class DrupalFiles
{

    private $io;

    private $options;

    public function __construct(IOInterface $io, Options $options)
    {
        $this->io = $io;
        $this->options = $options;
    }

    public function createRequiredFiles()
    {
        $fs = new Filesystem();
        $composer_root = getcwd();
        $drupal_root = $composer_root . '/' . $this->options->get('web-prefix');

        // Create the basic structure.
        foreach (['modules', 'profiles', 'themes'] as $dir) {
            if (!$fs->exists($drupal_root . '/'. $dir)) {
                $fs->mkdir($drupal_root . '/'. $dir);
                $fs->touch($drupal_root . '/'. $dir . '/.gitkeep');
            }
        }

        // Prepare the settings file for installation
        if (!$fs->exists($drupal_root . '/sites/default/settings.php')
            && $fs->exists($drupal_root . '/sites/default/default.settings.php')) {
            $fs->copy($drupal_root . '/sites/default/default.settings.php', $drupal_root . '/sites/default/settings.php');
            require_once $drupal_root . '/core/includes/bootstrap.inc';
            require_once $drupal_root . '/core/includes/install.inc';
            $settings['config_directories'] = [
                CONFIG_SYNC_DIRECTORY => (object) [
                    'value' => Path::makeRelative($composer_root . '/config/sync', $drupal_root),
                    'required' => true,
                ],
            ];
            drupal_rewrite_settings($settings, $drupal_root . '/sites/default/settings.php');
            $fs->chmod($drupal_root . '/sites/default/settings.php', 0666);
            $this->io->write("Create a sites/default/settings.php file with chmod 0666");
        }

        // Create the files directory with chmod 0777
        if (!$fs->exists($drupal_root . '/sites/default/files')) {
            $oldmask = umask(0);
            $fs->mkdir($drupal_root . '/sites/default/files', 0777);
            umask($oldmask);
            $this->io->write("Create a sites/default/files directory with chmod 0777");
        }
    }
}

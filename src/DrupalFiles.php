<?php

namespace Hussainweb\DrupalComposerHelper;

use Composer\IO\IOInterface;
use DrupalFinder\DrupalFinder;
use Symfony\Component\Filesystem\Filesystem;
use Webmozart\PathUtil\Path;

class DrupalFiles
{

    public static function createRequiredFiles(IOInterface $io)
    {
        $fs = new Filesystem();
        $drupalFinder = new DrupalFinder();
        $drupalFinder->locateRoot(getcwd());
        $drupalRoot = $drupalFinder->getDrupalRoot();

        $dirs = [
            'modules',
            'profiles',
            'themes',
        ];

        // Required for unit testing
        foreach ($dirs as $dir) {
            if (!$fs->exists($drupalRoot . '/'. $dir)) {
                $fs->mkdir($drupalRoot . '/'. $dir);
                $fs->touch($drupalRoot . '/'. $dir . '/.gitkeep');
            }
        }

        // Prepare the settings file for installation
        if (!$fs->exists($drupalRoot . '/sites/default/settings.php')
            && $fs->exists($drupalRoot . '/sites/default/default.settings.php')) {
            $fs->copy($drupalRoot . '/sites/default/default.settings.php', $drupalRoot . '/sites/default/settings.php');
            require_once $drupalRoot . '/core/includes/bootstrap.inc';
            require_once $drupalRoot . '/core/includes/install.inc';
            $settings['config_directories'] = [
                CONFIG_SYNC_DIRECTORY => (object) [
                    'value' => Path::makeRelative($drupalFinder->getComposerRoot() . '/config/sync', $drupalRoot),
                    'required' => true,
                ],
            ];
            drupal_rewrite_settings($settings, $drupalRoot . '/sites/default/settings.php');
            $fs->chmod($drupalRoot . '/sites/default/settings.php', 0666);
            $io->write("Create a sites/default/settings.php file with chmod 0666");
        }

        // Create the files directory with chmod 0777
        if (!$fs->exists($drupalRoot . '/sites/default/files')) {
            $oldmask = umask(0);
            $fs->mkdir($drupalRoot . '/sites/default/files', 0777);
            umask($oldmask);
            $io->write("Create a sites/default/files directory with chmod 0777");
        }
    }
}

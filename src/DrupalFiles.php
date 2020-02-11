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
        $composerRoot = getcwd();
        $drupalRoot = $composerRoot . '/' . $this->options->getWebPrefix();

        // Create the basic structure.
        foreach (['modules', 'profiles', 'themes'] as $dir) {
            if (!$fs->exists($drupalRoot . '/' . $dir)) {
                $fs->mkdir($drupalRoot . '/' . $dir);
                $fs->touch($drupalRoot . '/' . $dir . '/.gitkeep');
            }
        }

        // Prepare the settings file for installation
        $settingsFilename = $drupalRoot . '/sites/default/settings.php';
        $defaultSettingsFilename = $drupalRoot . '/sites/default/default.settings.php';
        if (!$fs->exists($settingsFilename) && $fs->exists($defaultSettingsFilename)) {
            $fs->copy($defaultSettingsFilename, $settingsFilename);
            require_once $drupalRoot . '/core/includes/bootstrap.inc';
            require_once $drupalRoot . '/core/includes/install.inc';
            $settings['settings']['config_sync_directory'] = (object)[
              'value' => Path::makeRelative($composerRoot . '/config/sync', $drupalRoot),
              'required' => true,
            ];
            drupal_rewrite_settings($settings, $settingsFilename);
            $fs->chmod($settingsFilename, 0666);
            $this->io->write("Created a sites/default/settings.php file with chmod 0666");
        }

        // Create the files directory with chmod 0777
        if (!$fs->exists($drupalRoot . '/sites/default/files')) {
            $oldmask = umask(0);
            $fs->mkdir($drupalRoot . '/sites/default/files', 0777);
            umask($oldmask);
            $this->io->write("Created a sites/default/files directory with chmod 0777");
        }
    }
}

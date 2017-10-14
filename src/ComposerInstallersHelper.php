<?php

namespace Hussainweb\DrupalComposerHelper;

use Composer\Composer;

class ComposerInstallersHelper
{

    private $composer;

    private $options;

    /**
     * The installer paths optimal for a composer based Drupal setup.
     *
     * @var array
     */
    private $installerPaths = [
        'd7' => [
            'core' => '{$prefix}',
            'module' => '{$prefix}sites/all/modules/contrib/{$name}/',
            'theme' => '{$prefix}sites/all/themes/contrib/{$name}/',
            'library' => '{$prefix}sites/all/libraries/{$name}/',
            'profile' => '{$prefix}sites/all/profiles/contrib/{$name}/',
            'drush' => 'drush/{$name}/',
            'custom-theme' => '{$prefix}sites/all/themes/custom/{$name}/',
            'custom-module' => '{$prefix}sites/all/modules/custom/{$name}/',
        ],
        'd8' => [
            'core' => '{$prefix}core/',
            'module' => '{$prefix}modules/contrib/{$name}/',
            'theme' => '{$prefix}themes/contrib/{$name}/',
            'library' => '{$prefix}libraries/{$name}/',
            'profile' => '{$prefix}profiles/contrib/{$name}/',
            'drush' => 'drush/{$name}/',
            'custom-theme' => '{$prefix}themes/custom/{$name}/',
            'custom-module' => '{$prefix}modules/custom/{$name}/',
        ],
    ];

    public function __construct(Composer $composer, Options $options)
    {
        $this->composer = $composer;
        $this->options = $options;
    }

    public function setInstallerPaths()
    {
        $extra = $this->composer->getPackage()->getExtra() + ['installer-paths' => []];

        // Get the configured prefix.
        $prefix = $this->options->get('web-prefix');

        // Check if we have to set Drupal 7 paths.
        $d7_paths = $this->options->get('set-d7-paths');

        // Get the existing Drupal specific installer paths.
        $installer_paths = $this->getDrupalInstallerPaths();

        // Set the installer paths we need for Drupal.
        foreach ($this->installerPaths[$d7_paths ? 'd7' : 'd8'] as $type => $path) {
            $type_key = 'type:drupal-' . $type;
            if (empty($installer_paths[$type_key])) {
                $path = str_replace('{$prefix}', $prefix . '/', $path);
                $extra['installer-paths'][$path] = [$type_key];
            }
        }
        $this->composer->getPackage()->setExtra($extra);
    }

    private function getDrupalInstallerPaths()
    {
        $extra = $this->composer->getPackage()->getExtra();
        if (!isset($extra['installer-paths'])) {
            return false;
        }

        $output = [];
        foreach ($extra['installer-paths'] as $path => $filters) {
            foreach ($filters as $filter) {
                if (substr($filter, 0, 12) == 'type:drupal-') {
                    $output[$filter] = $path;
                }
            }
        }

        return $output;
    }
}

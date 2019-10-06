<?php

namespace Hussainweb\DrupalComposerHelper;

use Composer\Composer;

class Options
{
    const DEFAULT_WEB_PREFIX = 'web';

    private $composer;

    public function __construct(Composer $composer)
    {
        $this->composer = $composer;
    }

    public function get($key = '')
    {
        $extra = $this->composer->getPackage()->getExtra() + ['drupal-composer-helper' => []];

        $extra['drupal-composer-helper'] += [
            'web-prefix' => static::DEFAULT_WEB_PREFIX,
            'additional-cleanup' => [],
            'set-d7-paths' => false,
        ];

        return $key ? $extra['drupal-composer-helper'][$key] : $extra['drupal-composer-helper'];
    }

    public function getWebPrefix()
    {
        $extra = $this->composer->getPackage()->getExtra();
        if (!empty($extra['drupal-web-prefix'])) {
            return $extra['drupal-web-prefix'];
        }
        if ($this->get('web-prefix')) {
            return $this->get('web-prefix');
        }
        return static::DEFAULT_WEB_PREFIX;
    }
}

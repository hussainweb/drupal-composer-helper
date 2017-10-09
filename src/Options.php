<?php

namespace Hussainweb\DrupalComposerHelper;

use Composer\Composer;

class Options
{

    private $composer;

    public function __construct(Composer $composer)
    {
        $this->composer = $composer;
    }

    public function get($key = '')
    {
        $extra = $this->composer->getPackage()->getExtra() + [
                'drupal-composer-helper' => [
                    'web-prefix' => 'web',
                ],
            ];

        return $key ? $extra['drupal-composer-helper'][$key] : $extra['drupal-composer-helper'];
    }
}

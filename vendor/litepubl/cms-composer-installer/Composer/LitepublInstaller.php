<?php
namespace litepubl\Composer;

use Composer\Installers\BaseInstaller;

class LitepublInstaller extends BaseInstaller
{
    protected $locations = array(
        'core'    => '.',
        'plugin'    => 'plugins/{$name}/',
        'theme'     => 'themes/{$name}/',
        'shop'     => 'shop/{$name}/',
    );
}

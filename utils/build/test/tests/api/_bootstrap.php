<?php
/**
 * LitePubl CMS
 *
 * @copyright 2010 - 2017 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.08
  */

// Here you can initialize variables that will be available to your tests

use Codeception\Util\Autoload;
use test\config;

config::init();
Autoload::addNamespace('litepubl', config::$home . '/lib');

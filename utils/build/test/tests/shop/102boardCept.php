<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.02
  */

use shop\Board;

$i = new AcceptanceTester($scenario);
$i->wantTo('Test admin hop dashboard');
$board = new Board($i, '09home');
$board->open();

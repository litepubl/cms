<?php
/**
* Lite Publisher CMS
* @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
* @link https://github.com/litepubl\cms
* @version 6.15
**/

namespace litepubl\view;

interface ViewInterface
 {
    public function request($arg);
    public function getTitle();
    public function getKeywords();
    public function getDescription();
    public function getHead();
    public function getCont();
    public function getIdSchema();
    public function setIdSchema($id);
}
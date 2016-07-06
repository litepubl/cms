<?php
/**
 * Lite Publisher CMS
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.00
  */

namespace Page;

class Cats extends Base
{
    public $url = '/admin/posts/addcat/';
    public $title = '#text-title';
    public $content = '#editor-raw';
    public $parent = 'select[name=parent]';


    public $titleFixture = 'New category';
    public $contentFixture = 'Some category content';
}

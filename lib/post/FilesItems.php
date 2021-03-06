<?php
/**
 * LitePubl CMS
 *
 * @copyright 2010 - 2017 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.08
  */

namespace litepubl\post;

class FilesItems extends \litepubl\core\ItemsPosts
{
    protected function create()
    {
        $this->dbversion = true;
        parent::create();
        $this->basename = 'fileitems';
        $this->table = 'filesitemsposts';
    }
}

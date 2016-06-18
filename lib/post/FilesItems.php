<?php
/**
 * Lite Publisher CMS
 * @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link https://github.com/litepubl\cms
 * @version 6.15
 *
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

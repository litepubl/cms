<?php
/**
 * Lite Publisher CMS
 * @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link https://github.com/litepubl\cms
 * @version 6.15
 *
 */

namespace litepubl\view;

class AdminParser extends BaseParser
{

    protected function create()
    {
        parent::create();
        $this->basename = 'admimparser';
        $this->tagfiles[] = 'themes/admin/admintags.ini';
    }

    public function loadpaths()
    {
        if (!count($this->tagfiles)) {
            $this->tagfiles[] = 'themes/admin/admintags.ini';
        }

        return parent::loadpaths();
    }

}


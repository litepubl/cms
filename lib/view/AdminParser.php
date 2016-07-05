<?php
/**
 * Lite Publisher CMS
 * @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link https://github.com/litepubl\cms
 * @version 7.00
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

    public function loadPaths(): array
    {
        if (!count($this->tagfiles)) {
            $this->tagfiles[] = 'themes/admin/admintags.ini';
        }

        return parent::loadPaths();
    }

    public function getFileList(string $name): array
    {
        if ($name == 'admin') {
                $result = parent::getFileList($name);
        } else {
                $about = $this->getAbout($name);
                $result = [$this->getApp()->paths->themes . $name . '/' . $about['file']];
        }

        return $result;
    }
}

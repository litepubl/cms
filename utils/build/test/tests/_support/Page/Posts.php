<?php
/**
 * Lite Publisher CMS
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.00
  */

namespace Page;

class Posts extends Base
{
    public $url = '/admin/posts/';
    public $deleteButton = '#submitbutton-delete';
    public $screenShotName = '';

    public function delete()
    {
        $list = func_get_args();
        if (count($list)) {
            $this->open();
            $i = $this->tester;
            $i->wantTo('Delete posts');
            $i->screenShot($this->screenShotName . '1table');
            foreach ($list as $id) {
                $i->checkOption("input[name=checkbox-$id]");
            }

            $i->screenShot($this->screenShotName . '2checked');
            $i->click($this->deleteButton);
            $i->checkError();
            $i->screenShot($this->screenShotName . '3deleted');
        }
    }
}

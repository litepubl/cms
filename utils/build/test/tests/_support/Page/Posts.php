<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.08
  */

namespace Page;

trait Posts
{
    private $postsUrl = '/admin/posts/';
    private $deleteButton = '#submitbutton-delete';

    private function deletePosts()
    {
        $list = func_get_args();
        if (count($list)) {
            $this->open($this->postsUrl);
            $i = $this->tester;
            $i->wantTo('Delete posts');
            $this->screenShot('table');
            foreach ($list as $id) {
                $i->checkOption("input[name=checkbox-$id]");
            }

            $this->screenShot('selected');
            $i->click($this->deleteButton);
            $i->checkError();
            $this->screenShot('deleted');
        }
    }
}

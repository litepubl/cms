<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.03
  */

namespace litepubl\plugins\downloaditem;

class Factory extends \litepubl\post\Factory
{

    public function getPosts()
    {
        return Plugin::i();
    }

    public function getView()
    {
        return View::i();
    }
}

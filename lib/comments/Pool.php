<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.03
  */

namespace litepubl\comments;

use litepubl\view\Lang;

class Pool extends \litepubl\core\Pool
{

    protected function create()
    {
        parent::create();
        $this->basename = 'commentspool';
        $this->perpool = 50;
    }

    public function getItem($id)
    {
        return $this->getdb('posts')->getvalue($id, 'commentscount');
    }

    public function getLangcount($count)
    {
        $l = Lang::i()->ini['comment'];
        switch ($count) {
        case 0:
            return $l[0];

        case 1:
            return $l[1];

        default:
            return sprintf($l[2], count);
        }
    }

    public function getLink($idpost, $tml)
    {
        return sprintf($tml, $this->getlangcount($this->get($idpost)));
    }
}

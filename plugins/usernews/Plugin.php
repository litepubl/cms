<?php
/**
 * Lite Publisher CMS
 * @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link https://github.com/litepubl\cms
 * @version 6.15
 *
 */

namespace litepubl\plugins\usernews;

use litepubl\view\Filter;
use litepubl\view\Lang;

class Plugin extends \litepubl\core\Plugin
{

    public function create()
    {
        parent::create();
        $this->data['_changeposts'] = false;
        $this->data['_canupload'] = true;
        $this->data['_candeletefile'] = true;
        $this->data['insertsource'] = true;
        $this->data['sourcetml'] = '<h4><a href="%1$s">%1$s</a></h4>';
        $this->data['checkspam'] = false;
    }

    public function getNoRights()
    {
        $lang = Lang::admin('usernews');
        return sprintf('<h4>%s</h4>', $lang->norights);
    }

    public function changePosts($action)
    {
        if (!$this->_changeposts) {
            return $this->noRights;
        }
    }

    public function canUpload()
    {
        if (!$this->_canupload) {
            return $this->noRights;
        }
    }

    public function canDeleteFile()
    {
        if (!$this->_candeletefile) {
            return $this->noRights;
        }
    }

    public function getHead()
    {
        return '';
    }
}

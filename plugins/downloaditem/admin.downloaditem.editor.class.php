<?php
/**
 * Lite Publisher CMS
 * @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link https://github.com/litepubl\cms
 * @version 6.15
 *
 */

namespace litepubl;

use litepubl\view\Lang;

class tdownloaditemeditor extends tposteditor
{

    public static function i($id = 0)
    {
        return parent::iteminstance(__class__, $id);
    }

    public function getTitle()
    {
        $lang = Lang::admin('downloaditems');
        $lang->addsearch('downloaditems', 'downloaditem', 'editor');

        if ($this->idpost == 0) {
            return parent::gettitle();
        } else {
            return Lang::admin('downloaditems')->editor;
        }
    }

    public function getTabstemplate()
    {
        $admintheme = $this->admintheme;
        return strtr($admintheme->templates['tabs'], array(
            '$id' => 'tabs',
            '$tab' => '[tab=downloaditem]' . $admintheme->templates['posteditor.tabs.tabs'],
            '$panel' => '[tabpanel=downloaditem{
[combo=type]
[text=downloadurl]
[text=authorurl]
[text=authorname]
[text=version]
}]' . $admintheme->templates['posteditor.tabs.panels'],
        ));
    }

    public function getArgstab(tpost $post, Args $args)
    {
        parent::getargstab($post, $args);

        $args->downloadurl = $post->downloadurl;
        $args->authorname = $post->authorname;
        $args->authorurl = $post->authorurl;
        $args->version = $post->version;

        $types = array(
            'theme' => Lang::get('downloaditem', 'theme') ,
            'plugin' => Lang::get('downloaditem', 'plugin')
        );

        $args->type = $this->theme->comboItems($types, $post->type);
    }

    public function newpost()
    {
        return new tdownloaditem();
    }

    public function processtab(tpost $post)
    {
        parent::processtab($post);

        extract($_POST, EXTR_SKIP);
        $post->version = $version;
        $post->type = $type;
        $post->downloadurl = $downloadurl;
        $post->authorname = $authorname;
        $post->authorurl = $authorurl;
    }

}


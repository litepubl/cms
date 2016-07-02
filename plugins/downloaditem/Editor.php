<?php
/**
 * Lite Publisher CMS
 * @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link https://github.com/litepubl\cms
 * @version 7.00
 *
 */


namespace litepubl\plugins\downloaditem;

use litepubl\post\Post;
use litepubl\view\Args;
use litepubl\view\Lang;

class Editor extends \litepubl\admin\posts\Editor
{

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
        return strtr(
            $admintheme->templates['tabs'], array(
            '$id' => 'tabs',
            '$tab' => '[tab=downloaditem]' . $admintheme->templates['posteditor.tabs.tabs'],
            '$panel' => '[tabpanel=downloaditem{
[combo=type]
[text=downloadurl]
[text=authorurl]
[text=authorname]
[text=version]
}]' . $admintheme->templates['posteditor.tabs.panels'],
            )
        );
    }

    public function getArgstab(Post $post, Args $args)
    {
        parent::getArgsTab($post, $args);

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

    public function newPost()
    {
        return new Download();
    }

    public function processTab(Post $post)
    {
        parent::processTab($post);

        extract($_POST, EXTR_SKIP);
        $post->version = $version;
        $post->type = $type;
        $post->downloadurl = $downloadurl;
        $post->authorname = $authorname;
        $post->authorurl = $authorurl;
    }
}

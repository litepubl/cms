<?php
/**
 * Lite Publisher CMS
 * @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link https://github.com/litepubl\cms
 * @version 7.00
 *
 */


namespace litepubl\admin\options;

use litepubl\view\Lang;
use litepubl\view\MainView;

class Options extends \litepubl\admin\Menu
{

    public function getContent(): string
    {
        $template = MainView::i();
        $args = $this->newArgs();
        $lang = Lang::admin('options');
        $admin = $this->admintheme;

        $site = $this->getApp()->site;
        $args->fixedurl = $site->fixedurl;
        $args->redirdom = $this->getApp()->router->redirdom;
        $args->url = $site->url;
        $args->name = $site->name;
        $args->description = $site->description;
        $args->keywords = $site->keywords;
        $args->author = $site->author;
        $args->footer = $template->footer;

        $args->formtitle = $lang->options;
        return $admin->form(
            '
      [checkbox=fixedurl]
      [checkbox=redirdom]
      [text=url]
      [text=name]
      [text=description]
      [text=keywords]
      [text=author]
      [editor=footer]
      ', $args
        );
    }

    public function processForm()
    {
        extract($_POST, EXTR_SKIP);
        $this->getApp()->router->redirdom = isset($redirdom);
        $site = $this->getApp()->site;
        $site->fixedurl = isset($fixedurl);
        $site->url = $url;
        $site->name = $name;
        $site->description = $description;
        $site->keywords = $keywords;
        $site->author = $author;
        $this->getdb('users')->setvalue(1, 'name', $author);
        MainView::i()->footer = $footer;
        return '';
    }
}

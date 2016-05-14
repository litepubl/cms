<?php
/**
 * Lite Publisher CMS
 * @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link https://github.com/litepubl\cms
 * @version 6.15
 *
 */

namespace litepubl\admin\options;

use litepubl\core\Str;
use litepubl\view\AdminParser;
use litepubl\view\Args;
use litepubl\view\Base;
use litepubl\view\Lang;
use litepubl\view\Parser;

class ThemeParser extends \litepubl\admin\Menu
{

    public function getContent()
    {
        $lang = Lang::admin('options');
        $args = new Args();
        $tabs = $this->newTabs();

        $themeparser = Parser::i();
        $args->tagfiles = implode("\n", $themeparser->tagfiles);
        $args->themefiles = implode("\n", $themeparser->themefiles);
        $tabs->add($lang->theme, '[editor=tagfiles] [editor=themefiles]');

        $admin = AdminParser::i();
        $args->admintagfiles = implode("\n", $admin->tagfiles);
        $args->adminthemefiles = implode("\n", $admin->themefiles);
        $tabs->add($lang->admin, '[editor=admintagfiles] [editor=adminthemefiles]');

        $args->formtitle = $lang->options;
        return $this->admintheme->form($tabs->get() , $args);
    }

    public function processForm()
    {
        $themeparser = Parser::i();
        $themeparser->tagfiles = Str::toArray($_POST['tagfiles']);
        $themeparser->themefiles = Str::toArray($_POST['themefiles']);
        $themeparser->save();

        $admin = AdminParser::i();
        $admin->tagfiles = Str::toArray($_POST['admintagfiles']);
        $admin->themefiles = Str::toArray($_POST['adminthemefiles']);
        $admin->save();

        Base::clearcache();
    }

}


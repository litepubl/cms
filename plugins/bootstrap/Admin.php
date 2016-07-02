<?php
/**
* 
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.00
 *
 */


namespace litepubl\plugins\bootstrap;

use litepubl\view\Args;
use litepubl\view\Base;
use litepubl\view\Lang;
use litepubl\view\Schemes;

class Admin extends \litepubl\admin\Menu
{

    public function getContent(): string
    {
        $result = '';
        $theme = $this->theme;
        $admintheme = $this->admintheme;

        $lang = Lang::admin('adminbootstraptheme');
        $args = new Args();

        $mainsidebars = array(
            'left' => $lang->left,
            'right' => $lang->right,
        );

        $schemes = Schemes::i();
        foreach ($schemes->items as $id => $item) {
            if (!isset($item['custom']['mainsidebar'])) {
                continue;
            }

            $result.= $admintheme->h($item['name']);
            $result.= $theme->getinput('combo', "mainsidebar-$id", $this->theme->comboItems($mainsidebars, $item['custom']['mainsidebar']), $lang->mainsidebar);
            $result.= $theme->getinput('combo', "cssfile-$id", $this->theme->comboItems($lang->ini['subthemes'], $item['custom']['cssfile']), $lang->cssfile);
            $result.= '<hr>';
        }

        $args->formtitle = $lang->customizeview;
        return $admintheme->form($result, $args);
    }

    public function processForm()
    {
        $lang = Lang::admin('adminbootstraptheme');
        $schemes = Schemes::i();
        foreach ($schemes->items as $id => $item) {
            if (!isset($item['custom']['mainsidebar'])) {
                continue;
            }

            $sidebar = $_POST["mainsidebar-$id"];
            if (!in_array(
                $sidebar, array(
                'left',
                'right'
                )
            )) {
                $sidebar = 'left';
            }
            $schemes->items[$id]['custom']['mainsidebar'] = $sidebar;

            $cssfile = $_POST["cssfile-$id"];
            if (!isset($lang->ini['subthemes'][$cssfile])) {
                $cssfile = 'default';
            }
            $schemes->items[$id]['custom']['cssfile'] = $cssfile;
        }

        $schemes->save();
        Base::clearcache();
    }
}
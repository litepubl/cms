<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.05
  */

namespace litepubl\plugins\catbread;

use litepubl\view\Base;

class Admincatbread extends \litepubl\admin\Panel
{
    use \litepubl\core\Singleton;

    public function getContent(): string
    {
        $plugin = Catbread::i();
        $lang = $this->getLangAbout();
        $args = $this->args;
        $args->showhome = $plugin->showhome;
        $args->showchilds = $plugin->showchilds;
        $args->showsimilar = $plugin->showsimilar;

        $lang->addsearch('sortnametags');
        $sort = [
            'title' => $lang->title,
            'itemscount' => $lang->count,
            'customorder' => $lang->customorder,
        ];

        $args->sort = $this->theme->comboItems($sort, $plugin->childsortname);

        $pos = [
            'replace' => $lang->replace,
            'top' => $lang->top,
            'before' => $lang->before,
            'after' => $lang->after,
            'nothing' => $lang->nothing,
        ];

        $args->breadpos = $this->theme->comboItems($pos, $plugin->breadpos);
        $args->similarpos = $this->theme->comboItems($pos, $plugin->similarpos);

        $args->formtitle = $lang->formtitle;
        return $this->admin->form(
            '
    [checkbox=showhome]
    [combo=breadpos]
    [checkbox=showchilds]
    [combo=sort]
    [checkbox=showsimilar]
    [combo=similarpos]
    ', $args
        );
    }

    public function processForm()
    {
        extract($_POST, EXTR_SKIP);
        $plugin = Catbread::i();
        $plugin->showhome = isset($showchilds);
        $plugin->showchilds = isset($showchilds);
        $plugin->showsimilar = isset($showsimilar);
        $plugin->childsortname = $sort;
        $plugin->breadpos = $breadpos;
        $plugin->similarpos = $similarpos;
        $plugin->save();
        Base::clearcache();
        return '';
    }
}

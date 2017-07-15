<?php
/**
 * LitePubl CMS
 *
 * @copyright 2010 - 2017 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.08
  */

namespace litepubl\plugins\extrasidebars;

use litepubl\admin\UList;
use litepubl\utils\Filer;
use litepubl\view\Base;

class Admin extends \litepubl\admin\Panel
{

    public function getContent(): string
    {
        $plugin = ExtraSidebars::i();
        $ul = new UList($this->admin);
        $themes = '';
        $tml = str_replace(
            '$value',
            $this->admin->templates['checkbox.label'],
            $this->admin->templates['list.value']
        );

        $dirnames = Filer::getDir($this->getApp()->paths->themes);
        foreach ($dirnames as $name) {
                $themes .= strtr(
                    $tml,
                    [
                    '$name' => 'theme',
                    '$id' => $name,
                    '$checked' => in_array($name, $plugin->themes) ? 'checked="checked"' : '',
                    '$title' => $name,
                    ]
                );
        }

        $args = $this->args;
        $lang = $this->getLangAbout();
        $args->formtitle = $lang->name;
        $args->beforepost = $plugin->beforepost;
        $args->afterpost = $plugin->afterpost;

        return $this->admin->form(
            '
[checkbox=beforepost]
 [checkbox=afterpost]
'
            . $this->admin->getSection($lang->themes, $ul->ul($themes)),
            $args
        );
    }

    public function processForm()
    {
        $plugin = ExtraSidebars::i();
        $plugin->beforepost = isset($_POST['beforepost']);
        $plugin->afterpost = isset($_POST['afterpost']);
        $plugin->themes = $this->admin->check2array('theme-');
        $plugin->save();
        Base::clearCache();
    }
}

<?php
/**
 * Lite Publisher CMS
 * @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link https://github.com/litepubl\cms
 * @version 6.15
 *
 */

namespace litepubl\plugins\extrasidebars;

use litepubl\admin\AdminInterface;
use litepubl\view\Base;

class Admin implements \litepubl\admin\AdminInterface
{
use \litepubl\admin\PanelTrait;

    public function getContent()
    {
        $plugin = ExtraSidebars::i();
        $themes = tadminthemes::getlist('<li><input name="theme-$name" id="checkbox-theme-$name" type="checkbox" value="$name" $checked />
    <label for="checkbox-theme-$name"><img src="$site.files/themes/$name/$screenshot" alt="$name" /></label>
    $lang.version:$version $lang.author: <a href="$url">$author</a> $lang.description:  $description</li>', $plugin->themes);

        $args = $this->args;
        $lang = $this->getLangAbout();
        $args->formtitle = $lang->name;
        $args->beforepost = $plugin->beforepost;
        $args->afterpost = $plugin->afterpost;

        return $this->admin->form('
[checkbox=beforepost]
 [checkbox=afterpost]
'
 . "<h4>$lang->themes</h4><ul>$themes</ul>"
, $args);
    }

    public function processForm()
    {
        $plugin = ExtraSidebars::i();
        $plugin->beforepost = isset($_POST['beforepost']);
        $plugin->afterpost = isset($_POST['afterpost']);
        $plugin->themes = tadminhtml::check2array('theme-');
        $plugin->save();
        Base::clearCache();
    }

}


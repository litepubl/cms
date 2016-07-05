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

use litepubl\pages\Appcache;
use litepubl\pages\RobotsTxt;
use litepubl\view\Lang;

class Robots extends \litepubl\admin\Menu
{

    public function getContent(): string
    {
        $admin = $this->admintheme;
        $lang = Lang::admin('options');
        $args = $this->newArgs();

        $args->formtitle = 'robots.txt';
        $args->robots = RobotsTxt::i()->text;
        $args->appcache = Appcache::i()->text;
        $tabs = $this->newTabs($this->admintheme);
        $tabs->add('robots.txt', '[editor=robots]');
        $tabs->add('manifest.appcache', '[editor=appcache]');
        return $admin->form($tabs->get(), $args);
    }

    public function processForm()
    {
        $robo = RobotsTxt::i();
        $robo->text = $_POST['robots'];
        $robo->save();

        $appcache = Appcache::i();
        $appcache->text = $_POST['appcache'];
        $appcache->save();
    }
}

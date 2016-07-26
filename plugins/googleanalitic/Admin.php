<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.02
  */

namespace litepubl\plugins\googleanalitic;

use litepubl\view\Js;

class Admin extends \litepubl\admin\Panel
{

    public function getContent(): string
    {
        $plugin = Plugin::i();
        $lang = $this->getLangAbout();
        $args = $this->args;
        $args->formtitle = $lang->formtitle;
        $args->user = $plugin->user;
        $args->se = $plugin->se;
        return $this->admin->form(
            '
[text=user]
    [editor=se]
', $args
        );
    }

    public function processForm()
    {
        $plugin = Plugin::i();
        $plugin->user = trim($_POST['user']);
        $plugin->se = $_POST['se'];
        $plugin->save();

        $js = Js::i();
        if (!$plugin->user) {
            $js->deleteFile('default', $plugin->jsfile);
        } else {
            $s = file_get_contents(__DIR__ . '/googleanalitic.js');
            $s = sprintf($s, $plugin->user, $plugin->se);
            $filename = $this->getApp()->paths->home . $plugin->jsfile;
            file_plufile_put_contents($filename, $s);
            @chmod($filename, 0666);

            $js->lock();
            $js->add('default', $plugin->jsfile);
            $js->unlock();
        }

    }
}

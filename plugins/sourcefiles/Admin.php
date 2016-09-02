<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.07
  */

namespace litepubl\plugins\sourcefiles;

use litepubl\utils\Http;

class Admin extends \litepubl\admin\Panel
{

    public function getContent(): string
    {
        $plugin = Plugin::i();
        $lang = $this->getLangAbout();
        $args = $this->args;
        $args->zipurl = $plugin->zipurl;
        $args->formtitle = $lang->title;
        return $this->admin->form('[text=zipurl]', $args);
    }

    public function processForm()
    {
        $plugin = Plugin::i();
        $m = microtime(true);
        $url = trim($_POST['zipurl']);
        if ($url && ($s = Http::get($url))) {
            $plugin->data['zipurl'] = $url;
            $plugin->save();
            set_time_limit(120);
            $filename = $this->getApp()->paths->data . 'sourcefile.temp.zip';
            file_put_contents($filename, $s);
            @chmod($filename, 0666);
            $plugin->clear();
            $plugin->readZip($filename);
            unlink($filename);
            return sprintf('<h4>Processed  by %f seconds</h4>', round(microtime(true) - $m, 2));
        }
    }
}

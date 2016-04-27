<?php
/**
* Lite Publisher CMS
* @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
* @link https://github.com/litepubl\cms
* @version 6.15
**/

namespace litepubl;
use litepubl\view\Args;
use litepubl\core\Plugins;

class tadminsourcefiles {

    public static function i() {
        return static::iGet(__class__);
    }

    public function getContent() {
        $plugin = tsourcefiles::i();
        $lang = Plugins::getnamelang(basename(dirname(__file__)));
        $html = tadminhtml::i();
        $args = new Args();
        $args->zipurl = $plugin->zipurl;
        $args->formtitle = $lang->title;
        return $html->adminform('[text=zipurl]', $args);
    }

    public function processForm() {
        $plugin = tsourcefiles::i();
        $m = microtime(true);
        $url = trim($_POST['zipurl']);
        if ($url && ($s = http::get($url))) {
            $plugin->data['zipurl'] = $url;
            $plugin->save();
            set_time_limit(120);
            $filename =  $this->getApp()->paths->data . 'sourcefile.temp.zip';
            file_put_contents($filename, $s);
            @chmod($filename, 0666);
            $plugin->clear();
            $plugin->readzip($filename);
            unlink($filename);
            return sprintf('<h4>Processed  by %f seconds</h4>', round(microtime(true) - $m, 2));
        }
    }

}
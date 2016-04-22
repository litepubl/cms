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
use litepubl\view\Js;
use litepubl\core\Plugins;

class tgoogleanalitic extends \litepubl\core\Plugin
 {

    public static function i() {
        return getinstance(__class__);
    }

    protected function create() {
        parent::create();
        $this->data['user'] = '';
        $this->data['se'] = '';
    }

    public function getContent() {
        $tml = '[text:user]
    [editor:se]';
        $html = tadminhtml::i();
        $args = new Args();
        $about = Plugins::getabout(Plugins::getname(__file__));
        $args->formtitle = $about['formtitle'];
        $args->data['$lang.user'] = $about['user'];
        $args->data['$lang.se'] = $about['se'];
        $args->user = $this->user;
        $args->se = $this->se;
        return $html->adminform($tml, $args);
    }

    public function processForm() {
        $this->user = $_POST['user'];
        $this->se = $_POST['se'];
        $this->save();

        $jsmerger = Js::i();
        if ($this->user == '') {
            $jsmerger->deletetext('default', 'googleanalitic');
        } else {
            $s = file_get_contents(dirname(__file__) . DIRECTORY_SEPARATOR . 'googleanalitic.js');
            $s = sprintf($s, $this->user, $this->se);
            $jsmerger->addtext('default', 'googleanalitic', $s);
        }
    }

    public function install() {
        $this->se = file_get_contents(dirname(__file__) . DIRECTORY_SEPARATOR .  $this->getApp()->options->language . 'se.js');
        $this->save();
    }

    public function uninstall() {
        $jsmerger = Js::i();
        $jsmerger->deletetext('default', 'googleanalitic');
    }

} 
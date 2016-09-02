<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.07
  */

namespace litepubl\plugins\googleanalitic;

use litepubl\view\Js;

class Plugin extends \litepubl\core\Plugin
{
    public $jsfile = 'files/js/googleanalitic.js';

    protected function create()
    {
        parent::create();
        $this->data['user'] = '';
        $this->data['se'] = '';
    }

    public function install()
    {
        $this->se = file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . $this->getApp()->options->language . 'se.js');
        $this->save();
    }

    public function uninstall()
    {
        $js = Js::i();
        $js->deleteFile('default', $this->jsfile);
    }
}

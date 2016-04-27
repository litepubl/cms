<?php
/**
* Lite Publisher CMS
* @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
* @link https://github.com/litepubl\cms
* @version 6.15
**/

namespace litepubl;

class tmarkdownplugin extends \litepubl\core\Plugin
 {
    public $parser;

    public static function i() {
        return getinstance(__class__);
    }

    protected function create() {
        parent::create();
        $this->data['deletep'] = false;
         $this->getApp()->classes->include_file( $this->getApp()->paths->plugins . 'markdown' . DIRECTORY_SEPARATOR . 'MarkdownInterface.php');
         $this->getApp()->classes->include_file( $this->getApp()->paths->plugins . 'markdown' . DIRECTORY_SEPARATOR . 'Markdown.php');
        $this->parser = new Michelf\Markdown();
    }

    public function filter(&$content) {
        if ($this->deletep) $content = str_replace('_', '&#95;', $content);
        $content = $this->parser->transform($content);
        if ($this->deletep) $content = strtr($content, array(
            '<p>' => '',
            '</p>' => '',
            '&#95;' => '_'
        ));
    }

    public function install() {
        $filter = tcontentfilter::i();
        $filter->lock();
        $filter->onsimplefilter = $this->filter;
        $filter->oncomment = $this->filter;
        $filter->unlock();
    }

    public function uninstall() {
        $filter = tcontentfilter::i();
        $filter->unbind($this);
    }

}
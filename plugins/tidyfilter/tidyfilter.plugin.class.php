<?php
/**
* Lite Publisher CMS
* @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
* @link https://github.com/litepubl\cms
* @version 6.15
**/

namespace litepubl;

class ttidyfilter extends \litepubl\core\Plugin
 {

    public static function i() {
        return getinstance(__class__);
    }

    public function getHtml($s) {
        return sprintf('<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">' . '<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
    <meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
    <title>title</title>
    </head>
    <body><div>%s</div></body></html>', $s);
    }

    public function getBody($s) {
        $tag = '<div>';
        $i = strpos($s, $tag) + strlen($tag);
        $j = strrpos($s, '</div>');
        return substr($s, $i, $j - $i);
    }

    public function filter(&$content) {
        $config = array(
            'clean' => true,
            'enclose-block-text' => true,
            'enclose-text' => true,
            'preserve-entities' => true,
            //'input-xml' => true,
            'logical-emphasis' => true,
            'char-encoding' => 'utf8',
            //'input-encoding' => 'utf8',
            //'output-encoding' => 'utf8',
            'indent' => 'auto', //true,
            'output-xhtml' => true,
            'wrap' => 200
        );

        $tidy = new \tidy;
        $tidy->parseString($this->gethtml($content) , $config, 'utf8');
        $tidy->cleanRepair();
        $content = $this->getbody((string)$tidy);
    }

} 
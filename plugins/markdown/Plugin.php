<?php
/**
 * Lite Publisher CMS
 * @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link https://github.com/litepubl\cms
 * @version 7.00
 *
 */


namespace litepubl\plugins\markdown;

class Plugin extends \litepubl\core\Plugin
{
    public $parser;

    protected function create()
    {
        parent::create();
        $this->data['deletep'] = false;
        include_once __DIR__ . '/MarkdownInterface.php';
        include_once __DIR__ . '/Markdown.php';
        $this->parser = new \Michelf\Markdown();
    }

    public function filter(&$content)
    {
        if ($this->deletep) {
            $content = str_replace('_', '&#95;', $content);
        }
        $content = $this->parser->transform($content);
        if ($this->deletep) {
            $content = strtr(
                $content, array(
                '<p>' => '',
                '</p>' => '',
                '&#95;' => '_'
                )
            );
        }
    }
}

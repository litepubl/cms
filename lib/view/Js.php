<?php
/**
 * Lite Publisher CMS
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.00
  */

namespace litepubl\view;

class Js extends Merger
{

    protected function create()
    {
        parent::create();
        $this->basename = 'jsmerger';
    }

    public function addLang($section, $key, array $lang)
    {
        return $this->addText($section, $key, 'window.lang = window.lang || {};' . sprintf('lang.%s = %s;', $section, json_encode($lang)));
    }
}

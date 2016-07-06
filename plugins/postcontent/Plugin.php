<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.00
  */

namespace litepubl\plugins\postcontent;

class Plugin extends \litepubl\core\Plugin
{

    protected function create()
    {
        parent::create();
        $this->data['before'] = '';
        $this->data['after'] = '';
    }

    public function beforeContent($post, &$content)
    {
        $content = $this->before . $content;
    }

    public function aftercontent($post, &$content)
    {
        $content.= $this->after;
    }
}

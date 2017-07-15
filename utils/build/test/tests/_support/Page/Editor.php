<?php
/**
 * LitePubl CMS
 *
 * @copyright 2010 - 2017 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.08
  */

namespace Page;

class Editor extends Base
{
    protected $url = '/admin/posts/editor/';
    protected $content = '#editor-raw';
    protected $category = 'input[name=category-1]';
    protected $calendar = '#calendar-posted';
    protected $postbookmark = '.post-bookmark';
    protected $idpost = 'input[name=id]';
    protected $datePicker = '.ui-datepicker';

    protected function fillTitleContent(string $title, string $content)
    {
        $i = $this->tester;
        $i->fillField($this->title, $title);
        $i->fillField($this->content, $content);
    }

    protected function upload(string $filename)
    {
        parent::upload($filename);
        $r = $this->js('upload.js');
        if ($r) {
            codecept_debug(var_export($r, true));
        }
    }

    protected function submit(int $timeout = 1)
    {
        $i = $this->tester;
        $i->executeJs('$("form:last").submit();');
        sleep(4);
        $i->checkError();
    }

    protected function getPostLink()
    {
        return $this->tester->grabAttributeFrom($this->postbookmark, 'href');
    }

    protected function getPostId()
    {
        return $this->tester->grabAttributeFrom($this->idpost, 'value');
    }
}

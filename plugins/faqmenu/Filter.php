<?php
/**
 * Lite Publisher CMS
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.00
  */

namespace litepubl\plugins\faqmenu;

use litepubl\core\Str;
use litepubl\view\Filter as ViewFilter;

class Filter
{
    public $templateItems = '<ul>$items</ul>';
    public $template = '<li><a href="#answer-$id" id="question-$id" class="faq-question dashed" data-toggle="collapse" aria-expanded="false" aria-controls="answer-$id">$title</a>
<div id="answer-$id" class="collapse faq-answer" aria-labelledby="question-$id">$content</div></li>';

    private $id = 0;

    public function convert($content)
    {
        $result = '';
        $content = str_replace(
            array(
            "\r\n",
            "\r"
            ), "\n", trim($content)
        );

        $lines = explode("\n", $content);
        $q = array();
        $a = array();

        $filter = ViewFilter::i();
        foreach ($lines as $s) {
            $s = trim($s);
            if (Str::begin($s, 'q:') || Str::begin($s, 'Q:')) {
                $q[] = trim(substr($s, 2));
            } elseif (Str::begin($s, 'a:') || Str::begin($s, 'A:')) {
                $a[] = trim(substr($s, 2));
            } elseif ($s) {
                $result.= $this->createlist($q, $a);
                $result.= $filter->simplefilter($s);
                $q = array();
                $a = array();
            }
        }

        $result.= $this->createlist($q, $a);
        return $result;
    }

    private function createlist(array $questions, array $answers)
    {
        if (!count($questions)) {
            return '';
        }

        $result = '';
        foreach ($questions as $i => $q) {
            $result.= strtr($this->template, ['$id' => $this->id++, '$title' => $q, '$content' => $answers[$i], ]);
        }

        return str_replace('$items', $result, $this->templateItems);
    }
}

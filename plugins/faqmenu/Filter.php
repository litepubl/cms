<?php

namespace litepubl\plugins\faqmenu;
use litepubl\view\Filter;

class Filter
{

    public function convert($content) {
        $result = '';
        $content = str_replace(array(
            "\r\n",
            "\r"
        ) , "\n", trim($content));

        $lines = explode("\n", $content);
        $q = array();
        $a = array();

        $filter = Filter::i();
        foreach ($lines as $s) {
            $s = trim($s);
            if (Str::begin($s, 'q:') || Str::begin($s, 'Q:')) {
                $q[] = trim(substr($s, 2));
            } elseif (Str::begin($s, 'a:') || Str::begin($s, 'A:')) {
                $a[] = trim(substr($s, 2));
            } elseif ($s != '') {
                $result.= $this->createlist($q, $a);
                $q = array();
                $a = array();
                $result.= $filter->simplefilter($s);
            }
        }

        $result.= $this->createlist($q, $a);
        return $result;
    }

    private function createlist(array $questions, array $answers) {
        if (count($questions) == 0) {
 return '';
}

        $result = '';
        foreach ($questions as $i => $q) {
            $result.= sprintf('<li><a href="#" rel="faqitem">%s</a><p>%s</p></li>', $q, $answers[$i]);
        }
        return sprintf('<ul class="faqlist">%s</ul>', $result);
    }

}

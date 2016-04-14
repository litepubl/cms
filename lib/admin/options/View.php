<?php

namespace litepubl\admin\options;
use litepubl\view\Lang;
use litepubl\view\Args;
use litepubl\view\Filter;
use litepubl\view\Parser;

class View extends \litepubl\admin\Menu
{
public function getcontent() {
$options = litepubl::$options;
$args = new Args();
                $args->perpage = $options->perpage;

                $filter = Filter::i();
                $args->usefilter = $filter->usefilter;
                $args->automore = $filter->automore;
                $args->automorelength = $filter->automorelength;
                $args->autolinks = $filter->autolinks;
                $args->commentautolinks = $filter->commentautolinks;
                //$args->icondisabled = $options->icondisabled;
                $args->hidefilesonpage = $options->hidefilesonpage;

                $themeparser = Parser::i();
                $args->replacelang = $themeparser->replacelang;

$lang = Lang::admin('options');
                $args->formtitle = $lang->viewoptions;
                return $admintheme->form('
      [text=perpage]
      [checkbox=usefilter]
      [checkbox=automore]
      [text=automorelength]
      [checkbox=autolinks]
      [checkbox=commentautolinks]
      [checkbox=hidefilesonpage]
      [checkbox=replacelang]
      ', $args);
}

public function processform() {
        extract($_POST, EXTR_SKIP);
$options = litepubl::$options;
                //$options->icondisabled = isset($icondisabled);
                $options->hidefilesonpage = isset($hidefilesonpage);
                if (!empty($perpage)) {
$options->perpage = (int)$perpage;
}

                $filter = Filter::i();
                $filter->usefilter = isset($usefilter);
                $filter->automore = isset($automore);
                $filter->automorelength = (int)$automorelength;
                $filter->autolinks = isset($autolinks);
                $filter->commentautolinks = isset($commentautolinks);
                $filter->save();

                $themeparser = Parser::i();
                $themeparser->replacelang = isset($replacelang);
                $themeparser->save();
}

}
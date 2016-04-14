<?php

namespace litepubl\admin\options;
use litepubl\view\Lang;
use litepubl\view\Args;
use litepubl\post\Rss as PostRss;

class Rss extends \litepubl\admin\Menu
{
public function getcontent() {
$rss = PostRss::i();
$args = new Args();
                $args->feedburner = $rss->feedburner;
$args->feedburnercomments = $rss->feedburnercomments;
$args->template = $rss->template;

$lang = Lang::admin('options');
$args->formtitle = $lang->rssoptions;
return $this->admintheme->form('
[text=feedburner]
[text=feedburnercomments]
[editor=template]
', $args);
}

public function processform() {
$rss = PostRss::i();
$rss->feedburner = trim($_POST['feedburner']);
$rss->feedburnercomments = trim($_POST['feedburnercomments']);
$rss->template = trim($_POST['template']);
$rss->save();
}

}
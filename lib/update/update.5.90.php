<?php
function update590() {
$js = tjsmerger::i();
$js->lock();
if ($i = array_search('/js/jquery/ui/tabs.min.js', $js->items['admin']['files'])) {
array_insert($js->items['admin']['files'], '/js/jquery/ui/effect.min.js', $i);
} else {
  $js->add('admin', '/js/jquery/ui/effect.min.js');
}

$js->unlock();

  tcssmerger::i()->add('admin', '/js/litepublisher/css/form.inline.min.css');
}
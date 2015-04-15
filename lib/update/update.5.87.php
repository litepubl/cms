<?php
function update587() {
  litepublisher::$site->jqueryui_version = '1.11.0';
  litepublisher::$site->save();

$js = tjsmerger::i();
foreach ($js->items as $section => $items) {
foreach ($items['files'] as $i => $filename) {
$js->items[$section]['files'][$i] = str_replace('-$site.jqueryui_version/jquery.ui.', '/', $filename);
}
}

$js->save();

$css = tcssmerger::i();
foreach ($css->items as $section => $items) {
foreach ($items['files'] as $i => $filename) {
$filename = str_replace('-$site.jqueryui_version/', '/', $filename);
$css->items[$section]['files'][$i] = str_replace('jquery-ui-$site.jqueryui_version.custom.min.css', 'jquery-ui.min.css', $filename);
}
}

$css->save();
}
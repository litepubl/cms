<?php
function update608() {
  litepublisher::$site->jquery_version = '1.12.2';

$css = tcssmerger::i();
$css->replacefile('default',
'/js/litepublisher/css/form-inline.min.css',
'/js/litepubl/common/css/form.inline.min.css'
);

}
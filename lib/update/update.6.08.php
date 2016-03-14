<?php
function update608() {
$css = tcssmerger::i();
$css->replacefile('default',
'/js/litepublisher/css/form-inline.min.css',
'/js/litepubl/common/css/form.inline.min.css'
);

}
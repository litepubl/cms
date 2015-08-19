<?php
function update599() {
$js = tjsmerger::i();
$js->lock();
$js->items['comments']['files'] = array(
'/js/litepubl/comments/comments.template.min.js',
'/js/litepubl/comments/comments.quote.min.js',
'/js/litepubl/comments/confirmcomment.min.js',
'/js/litepubl/comments/moderate.min.js',
'/lib/languages/' . litepublisher::$options->language . '/comments.min.js',
  );
$js->unlock();
}
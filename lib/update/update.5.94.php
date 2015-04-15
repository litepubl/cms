<?php
function update594() {
if (litepublisher::$classes->exists('ulogin')) {
  tjsonserver::i()->addevent('check_logged', 'ulogin', 'check_logged');
}
}
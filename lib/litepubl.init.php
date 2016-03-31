<?php

namespace litepubl {
if (\version_compare(\PHP_VERSION, '5.4', '<')) {
  die('Lite Publisher requires PHP 5.4 or later. You are using PHP ' . \PHP_VERSION);
}

if (isset(config::$classes['root']) && class_exists(config::$classes['root'])) {
\call_user_func_array(config::$classes['root'], 'run', []);
} else {
litepubl::run();
}
}
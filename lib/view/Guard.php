<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

namespace litepubl\view;
use litepubl\core\litepubl;
use litepubl\core\Router;

class Guard {

    public static function is_xxx() {
        if (isset($_GET['ref'])) {
            $ref = $_GET['ref'];
            $url = $_SERVER['REQUEST_URI'];
            $url = substr($url, 0, strpos($url, '&ref='));
            if ($ref == md5(litepubl::$secret . litepubl::$site->url . $url . litepubl::$options->solt)) return false;
        }

        $host = '';
        if (!empty($_SERVER['HTTP_REFERER'])) {
            $p = parse_url($_SERVER['HTTP_REFERER']);
            $host = $p['host'];
        }
        return $host != $_SERVER['HTTP_HOST'];
    }

    public static function checkattack() {
        if (litepubl::$options->xxxcheck && static ::is_xxx()) {
            Router::nocache();
            Lang::usefile('admin');
            if ($_POST) {
                die(Lang::get('login', 'xxxattack'));
            }
            if ($_GET) {
                die(Lang::get('login', 'confirmxxxattack') . sprintf(' <a href="%1$s">%1$s</a>', $_SERVER['REQUEST_URI']));
            }
        }
        return false;
    }

} //class
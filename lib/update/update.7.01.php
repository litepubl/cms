<?php
/**
 * Lite Publisher CMS
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.00
  */

namespace litepubl\update;


use litepubl\pages\Sitemap;
use litepubl\core\litepubl;
use litepubl\view\Css;

function update701()
{
    $map = include __DIR__ . '/update7/classmap.php';

    $sitemap = Sitemap::i();
    foreach ($sitemap->classes as $i => $old) {
        if (isset($map[$old])) {
            $sitemap->classes[$i] = $map[$old];
        }
    }
    $sitemap->save();

    litepubl::$app->router->updateFilter();

    $css = Css::i();
    $css->replaceFile(
        'admin',
        'js/litepublisher/css/form.inline.min.css ',
        'js/litepubl/common/css/form.inline.min.css'
    );
}

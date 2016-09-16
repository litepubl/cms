<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.08
  */

namespace litepubl\update;

use litepubl\tag\Cats;

function update707()
{
    $cats = Cats::i();
    $db = $cats->getDb('posts');
    $idPosts = $db->res2id($db->query("select id from $db->posts where categories like '% %'"));
    //$cats->itemsposts->updatePosts($items, 'categories');
    foreach ($idPosts as $idPost) {
        $items = $cats->itemsposts->getItems($idPost);
        $db->table = 'posts';
        $db->setValue($idPost, 'categories', implode(',', $items));
    }

}

var_dump($items);

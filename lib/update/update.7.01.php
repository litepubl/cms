<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.07
  */

namespace litepubl\update;

use litepubl\core\litepubl;
use litepubl\pages\Sitemap;
use litepubl\post\Posts;
use litepubl\post\View;
use litepubl\updater\StorageIterator;
use litepubl\updater\Updater;
use litepubl\view\Css;
use litepubl\view\Parser;
use litepubl\view\Schemes;

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

    litepubl::$app->router->delete('/manifest.appcache');
    litepubl::$app->router->updateFilter();

    $parser = Parser::i();
    $i = array_search('lib/install/ini/themeparser.ini', $parser->tagfiles);
    if ($i !== false) {
        $parser->tagfiles[$i] = 'themes/default/tags.ini';
        $parser->save();
    }

    $css = Css::i();
    $css->replaceFile(
        'admin',
        'js/litepublisher/css/form.inline.min.css ',
        'js/litepubl/common/css/form.inline.min.css'
    );

    $schemes = Schemes::i();
    foreach ($schemes->items as $id => $item) {
        if (isset($item['postanounce'])) {
            $item['postannounce'] = $item['postanounce'];
            unset($item['postanounce']);
            if (!in_array($item['postannounce'], ['excerpt', 'card', 'lite'])) {
                $item['postannounce'] = 'excerpt';
            }
            $schemes->items[$id] = $item;
        }
    }

    $schemes->save();

    $posts = Posts::i();
    $view = View::i();
    foreach (['beforecontent', 'aftercontent', 'beforeexcerpt', 'afterexcerpt', 'onhead', 'onanhead'] as $name) {
        if (isset($posts->data['events'][$name])) {
            $view->data['events'][$name] = $posts->data['events'][$name];
            unset($posts->data['events'][$name]);
        }
    }

    $posts->save();
    $view->save();

    $updater = Updater::i();
    $updater->lock();
    $updater->unbind('litepubl\view\Js');
    $updater->unbind('litepubl\view\Css');
    $updater->unlock();

    $map = include __DIR__ . '/update7/eventmap.php';
    $func= function (\StdClass $std) use ($map) {

        $result = false;
        if (isset($std->data['events']) && count($std->data['events'])) {
            foreach ($std->data['events'] as $name => $events) {
                foreach ($events as $i => $event) {
                    if (isset($map[$event[0]])
                        && isset($map[$event[0]][$event[1]])
                    ) {
                                        $std->data['events'][$name][$i][1] = $map[$event[0]][$event[1]];
                                $result = true;
                    }
                }
            }
        }

        if (isset($std->data['coclasses'])) {
            unset($std->data['coclasses']);
            $result = true;
        }

        return $result;
    };

        $iterator = new StorageIterator(litepubl::$app->storage, $func);
        $iterator->dir(litepubl::$app->paths->data);


    foreach (litepubl::$app->poolStorage->data as $name => $data) {
        $std = new \StdClass();
        $std->data = $data;
        $func($std);
        litepubl::$app->poolStorage->data[$name] = $std->data;
    }

    litepubl::$app->poolStorage->save(litepubl::$app->options);
    litepubl::$app->poolStorage->commit();
}

<?php
/**
 * Lite Publisher CMS
 * @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link https://github.com/litepubl\cms
 * @version 7.00
 *
 */


use Page\Plugin;
use Page\Editor;
use page\Posts;

$i = new AcceptanceTester($scenario);
$i->wantTo('Test wikiwords plugin');
$plugin = new Plugin($i, '20wikiwords');
$plugin->install('wikiwords');

$editor = new Editor($i, '20wikiwords');
$editor->open();
$i->wantTo('Create post with declared wiki word');
$i->checkOption($editor->category);
$editor->fillTitleContent(
    'Declare wiki word',
    'Some text with [wiki:wikilink] must be here'
);

$editor->submit();

$i->wantTo('Get id post #1');
$id1 = $editor->getPostId();
$holderlink = $editor->getPostLink();
$editor->screenShot('declare');

$i->openPage($editor->url);
$i->wantTo('Create post with use wiki word');
$i->checkOption($editor->category);
$editor->fillTitleContent(
    'Use wiki word',
    'Some text where used [[wikilink]]. Link must be present'
);

$editor->submit();
$editor->screenShot('use');

$i->wantTo('Get id post #2');
$id2 = $editor->getPostId();

$i->wantTo('Check used word');
$i->amOnUrl($editor->getPostLink());
$i->checkError();
$editor->screenShot('used');
$i->click('.wiki-link');
$i->checkError();
$i->assertEquals($holderlink, $i->getAbsoluteUrl(), 'Wiki word linked');
$editor->screenShot('declared');

$i->wantTo('Delete new posts');
$posts = new Posts($i);
$posts->screenShotName = '20.wikiwords.05';
$posts->delete($id1, $id2);

$plugin->uninstall('wikiwords');

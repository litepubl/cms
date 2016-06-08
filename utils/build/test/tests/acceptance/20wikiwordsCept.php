<?php 

use Page\Plugin;
use Page\Editor;

$i = new AcceptanceTester($scenario);
$i->wantTo('Test wikiwords plugin');
$plugin = new Plugin($i);
$plugin->install('wikiwords');

$editor = new Editor($i);
$editor->open();
$i->wantTo('Create post with declared wiki word');
$i->checkOption($editor->category);
$editor->fillTitleContent(
'Declare wiki word',
'Some text with [wiki:wikilink] must be here'
);

$editor->submit();
$holderlink = $editor->getPostLink();
$i->screenShot('20.wikiwords.01declare');

$i->openPage($editor->url);
$i->wantTo('Create post with use wiki word');
$i->checkOption($editor->category);
$editor->fillTitleContent(
'Use wiki word',
'Some text where used [[wikilink]]. Link must be present'
);

$editor->submit();
$i->screenShot('20.wikiwords.02use');
$i->wantTo('Check used word');
$i->amOnUrl($editor->getPostLink());
$i->checkError();
$i->screenShot('20.wikiwords.03used');
$i->click('.wiki-link');
$i->checkError();

$i->screenShot('20.wikiwords.04declared');
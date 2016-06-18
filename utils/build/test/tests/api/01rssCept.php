<?php

use Page\Rss;
use Codeception\Util\Xml as XmlUtils;

$i = new apiTester($scenario);
$i->wantTo('Test RSS feeds');
$rss = new Rss($i);
$i->sendGET($rss->posts);
$i->seeResponseIsXml();
//$i->seeXmlResponseMatchesXpath('//user/login');

$i->sendGET($rss->comments);
$i->seeResponseIsXml();

$i->sendGET($rss->post);
$i->seeResponseIsXml();

$i->sendGET($rss->cats);
$i->seeResponseIsXml();

$i->sendGET($rss->tags);
$i->seeResponseIsXml();

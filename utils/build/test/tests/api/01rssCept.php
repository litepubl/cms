<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.03
  */

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

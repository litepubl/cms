<?php
/**
* Lite Publisher CMS
* @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
* @link https://github.com/litepubl\cms
* @version 6.15
**/

namespace litepubl\post;

class DomRss extends \domDocument {
    public $items;
    public $rss;
    public $channel;

    public function __construct() {
        parent::__construct();
        $this->items = array();
    }

    public function CreateRoot($url, $title) {
        $this->encoding = 'utf-8';
        $this->appendChild($this->createComment('generator="Lite Publisher/' .  $this->getApp()->options->version . ' version"'));
        $this->rss = $this->createElement('rss');
        $this->appendChild($this->rss);

        Node::attr($this->rss, 'version', '2.0');
        Node::attr($this->rss, 'xmlns:content', 'http://purl.org/rss/1.0/modules/content/');
        Node::attr($this->rss, 'xmlns:wfw', 'http://wellformedweb.org/CommentAPI/');
        Node::attr($this->rss, 'xmlns:dc', 'http://purl.org/dc/elements/1.1/');
        Node::attr($this->rss, 'xmlns:atom', 'http://www.w3.org/2005/Atom');

        $this->channel = Node::add($this->rss, 'channel');

        $link = Node::add($this->channel, 'atom:link');
        Node::attr($link, 'href', $url);
        Node::attr($link, 'rel', 'self');
        Node::attr($link, 'type', 'application/rss+xml');

        Node::addvalue($this->channel, 'title', $title);
        Node::addvalue($this->channel, 'link', $url);
        Node::addvalue($this->channel, 'description',  $this->getApp()->site->description);
        Node::addvalue($this->channel, 'pubDate', date('r'));
        Node::addvalue($this->channel, 'generator', 'http://litepublisher.com/generator.htm?version=' .  $this->getApp()->options->version);
        Node::addvalue($this->channel, 'language', 'en');
    }

    public function CreateRootMultimedia($url, $title) {
        $this->encoding = 'utf-8';
        $this->appendChild($this->createComment('generator="Lite Publisher/' .  $this->getApp()->options->version . ' version"'));
        $this->rss = $this->createElement('rss');
        $this->appendChild($this->rss);

        Node::attr($this->rss, 'version', '2.0');
        Node::attr($this->rss, 'xmlns:media', 'http://video.search.yahoo.com/mrss');
        Node::attr($this->rss, 'xmlns:atom', 'http://www.w3.org/2005/Atom');

        $this->channel = Node::add($this->rss, 'channel');

        $link = Node::add($this->channel, 'atom:link');
        Node::attr($link, 'href', $url);
        Node::attr($link, 'rel', 'self');
        Node::attr($link, 'type', 'application/rss+xml');

        Node::addvalue($this->channel, 'title', $title);
        Node::addvalue($this->channel, 'link', $url);
        Node::addvalue($this->channel, 'description',  $this->getApp()->site->description);
        Node::addvalue($this->channel, 'pubDate', date('r'));
        Node::addvalue($this->channel, 'generator', 'http://litepublisher.com/generator.htm?version=' .  $this->getApp()->options->version);
        Node::addvalue($this->channel, 'language', 'en');
    }

    public function AddItem() {
        $result = Node::add($this->channel, 'item');
        $this->items[] = $result;
        return $result;
    }

    public function GetStripedXML() {
        $s = $this->saveXML();
        return substr($s, strpos($s, '?>') + 2);
    }

} 
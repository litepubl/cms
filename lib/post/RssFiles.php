<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.03
  */

namespace litepubl\post;

use litepubl\core\Context;
use litepubl\core\Event;

/**
 * RSS files
 *
 * @property       string $feedburner
 * @property-write callable $onRoot
 * @property-write callable $onItem
 * @method         array onRoot(array $params)
 * @method         array onItem(array $params)
 */

class RssFiles extends \litepubl\core\Events implements \litepubl\core\ResponsiveInterface
{
    public $domrss;

    protected function create()
    {
        parent::create();
        $this->basename = 'rssmultimedia';
        $this->addEvents('onroot', 'onitem');
        $this->data['feedburner'] = '';
    }

    public function filesChanged(Event $event)
    {
        $app = $this->getApp();
        $list = $app->router->getUrlsOfClass(get_class($this));
        foreach ($list as $url) {
            $app->cache->delete($app->controller->url2cacheFile($url));
        }
    }

    public function request(Context $context)
    {
        $response = $context->response;

        if (($context->itemRoute['arg'] == null) && $this->feedburner) {
            $response->body.= "<?php
      if (!preg_match('/feedburner|feedvalidator/i', \$_SERVER['HTTP_USER_AGENT'])) {
        \\litepubl\\core\\litepubl::\$app->redirExit('$this->feedburner');
      }
      ?>";
        }

        $response->setXml();
        $this->domrss = new DomRss();
        $this->domrss->CreateRootMultimedia($this->getApp()->site->url . $this->getApp()->router->url, 'media');
        $this->onRoot(['dom' => $this->domrss]);

        $list = $this->getrecent($arg, $this->getApp()->options->perpage);
        foreach ($list as $id) {
            $this->addfile($id);
        }

        $response->body.= $this->domrss->GetStripedXML();
    }

    private function getRecent($type, $count)
    {
        $files = Files::i();
        $sql = $type == '' ? '' : "media = '$type' and ";
        return $files->select($sql . 'parent = 0 and idperm = 0', " order by posted desc limit $count");
    }

    public function addfile($id)
    {
        $files = Files::i();
        $file = $files->getitem($id);
        $posts = $files->itemsposts->getposts($id);

        if (count($posts) == 0) {
            $postlink = $this->getApp()->site->url . '/';
        } else {
            $post = Post::i($posts[0]);
            $postlink = $post->link;
        }

        $item = $this->domrss->AddItem();
        Node::addvalue($item, 'title', $file['title']);
        Node::addvalue($item, 'link', $postlink);
        Node::addvalue($item, 'pubDate', $file['posted']);

        $media = Node::add($item, 'media:content');
        Node::attr($media, 'url', $files->geturl($id));
        Node::attr($media, 'fileSize', $file['size']);
        Node::attr($media, 'type', $file['mime']);
        Node::attr($media, 'medium', $file['media']);
        Node::attr($media, 'expression', 'full');

        if ($file['width'] > 0 && $file['height'] > 0) {
            Node::attr($media, 'height', $file['height']);
            Node::attr($media, 'width', $file['width']);
        }
        /*
        if (!empty($file['bitrate'])) Node::attr($media, 'bitrate', $file['bitrate']);
        if (!empty($file['framerate'])) Node::attr($media, 'framerate', $file['framerate']);
        if (!empty($file['samplingrate'])) Node::attr($media, 'samplingrate', $file['samplingrate']);
        if (!empty($file['channels'])) Node::attr($media, 'channels', $file['channels']);
        if (!empty($file['duration'])) Node::attr($media, 'duration', $file['duration']);
        */
        $hash = Node::addvalue($item, 'media:hash', static ::hashtomd5($file['hash']));
        Node::attr($hash, 'algo', "md5");

        if (!empty($file['keywords'])) {
            Node::addvalue($item, 'media:keywords', $file['keywords']);
        }

        if (!empty($file['description'])) {
            $description = Node::addvalue($item, 'description', $file['description']);
            Node::attr($description, 'type', 'html');
        }

        if ($file['preview'] > 0) {
            $idpreview = $file['preview'];
            $preview = $files->getitem($idpreview);
            $thumbnail = Node::add($item, 'media:thumbnail');
            Node::attr($thumbnail, 'url', $files->geturl($idpreview));
            if ($preview['width'] > 0 && $preview['height'] > 0) {
                Node::attr($thumbnail, 'height', $preview['height']);
                Node::attr($thumbnail, 'width', $preview['width']);
            }
        }
        $this->onItem(['item' => $item, 'file' => $file]);
    }

    public static function hashToMD5(string $hash): string
    {
        $r = '';
        $a = base64_decode($hash);
        for ($i = 0; $i < 16; $i++) {
            $r.= dechex(ord($a[$i]));
        }

        return $r;
    }

    public function setFeedburner($url)
    {
        if (($this->feedburner != $url)) {
            $this->data['feedburner'] = $url;
            $this->save();
            $this->getApp()->cache->clear();
        }
    }
}

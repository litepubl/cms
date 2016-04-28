<?php
/**
* Lite Publisher CMS
* @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
* @link https://github.com/litepubl\cms
* @version 6.15
**/

namespace litepubl\post;
use litepubl\core\Context;
use litepubl\tag\Categories;
use litepubl\tag\Tags;
use litepubl\comments\Comments;
use litepubl\coments\Manager as CommentManager;
use litepubl\widget\Comments as CommentWidget;
use litepubl\perm\Perm;
use litepubl\view\Lang;
use litepubl\view\Theme;

class Rss extends \litepubl\core\Events implements \litepubl\core\ResponsiveInterface
 {
    public $domrss;
public $url;
public $commentsUrl;
public $postCommentsUrl;

    protected function create() {
        parent::create();
        $this->basename = 'rss';
        $this->addevents('beforepost', 'afterpost', 'onpostitem');
        $this->data['feedburner'] = '';
        $this->data['feedburnercomments'] = '';
        $this->data['template'] = '';
        $this->data['idpostcomments'] = 0;

$this->url = '/rss.xml';
$this->commentsUrl = '/comments.xml';
$this->postCommentsUrl = '/comments/';
    }

    public function commentschanged() {
         $cache = $this->getApp()->cache;
$cache->clearUrl($this->commentsUrl);
$cache->clearUrl($this->postCommentsUrl);
    }

    public function request(Context $context)
    {
    $response = $context->response;
        $this->domrss = new DomRss();
$arg = $context->itemRoute['arg'];
        switch ($arg) {
            case 'posts':
                $this->getrecentposts();
if ($this->feedburner) {
$response->body .= "<?php
      if (!preg_match('/feedburner|feedvalidator/i', \$_SERVER['HTTP_USER_AGENT'])) {
header('HTTP/1.1 307 Temporary Redirect', true, 307);
header('Location: $this->feedburner');
return;
      }
      ?>";
}
                break;


            case 'comments':
                $this->GetRecentComments();
if ($this->feedburnercomments) {
$response->body .= "<?php
      if (!preg_match('/feedburner|feedvalidator/i', \$_SERVER['HTTP_USER_AGENT'])) {
header('HTTP/1.1 307 Temporary Redirect', true, 307);
header('Location: $this->feedburnercomments');
      }
      ?>";
        }
                break;


            case 'categories':
            case 'tags':
                if (!preg_match('/\/(\d*?)\.xml$/',  $this->getApp()->router->url, $match)) {
$response->status = 404;
                    return;
                }

                $id = (int)$match[1];
                $tags = $arg == 'categories' ? Categories::i() : Tags::i();
                if (!$tags->itemexists($id)) {
$response->status = 404;
                    return;
                }

                $tags->id = $id;
                if (isset($tags->idperm) && ($idperm = $tags->idperm)) {
                    $perm = Perm::i($idperm);
$perm->setResponse($response, $tags);
                }

                $this->gettagrss($tags, $id);
                break;


            default:
                if (!preg_match('/\/(\d*?)\.xml$/',  $this->getApp()->router->url, $match)) {
$response->status = 404;
                    return;
                }

                $idpost = (int)$match[1];
                $posts = Posts::i();
                if (!$posts->itemexists($idpost)) {
$response->status = 404;
                    return;
                }

                $post = Post::i($idpost);
                if ($post->status != 'published') {
$response->status = 404;
                    return;
                }

                if (isset($post->idperm) && ($post->idperm > 0)) {
                    $perm = Perm::i($post->idperm);
$perm->setResponse($response, $post);
                }

                $this->GetRSSPostComments($idpost);
        }

$response->setXml();
if ($before) {
$response->body = $before . $response->body;
}

        $response->body .= $this->domrss->GetStripedXML();
    }

    public function getRecentposts() {
        $this->domrss->CreateRoot( $this->getApp()->site->url . '/rss.xml',  $this->getApp()->site->name);
        $posts = Posts::i();
        $this->getrssposts($posts->getpage(0, 1,  $this->getApp()->options->perpage, false));
    }

    public function getRssposts(array $list) {
        foreach ($list as $id) {
            $this->addpost(Post::i($id));
        }
    }

    public function getTagrss(tcommontags $tags, $id) {
        $this->domrss->CreateRoot( $this->getApp()->site->url .  $this->getApp()->router->url, $tags->getvalue($id, 'title'));

        $items = $tags->getidposts($id);
        $this->getrssposts(array_slice($items, 0,  $this->getApp()->options->perpage));
    }

    public function GetRecentComments() {
        $this->domrss->CreateRoot( $this->getApp()->site->url . '/comments.xml', Lang::get('comment', 'onrecent') . ' ' .  $this->getApp()->site->name);

        $title = Lang::get('comment', 'onpost') . ' ';
        $comment = new \ArrayObject([], ArrayObject::ARRAY_AS_PROPS);
        $recent = CommentWidget::i()->getrecent( $this->getApp()->options->perpage);
        foreach ($recent as $item) {
            $comment->exchangeArray($item);
            $this->AddRSSComment($comment, $title . $comment->title);
        }
    }

    public function getHoldcomments($url, $count) {
        $result = '<?php litepubl\\litepubl\core\Router::sendxml(); ?>';
        $this->dogetholdcomments($url, $count);
        $result.= $this->domrss->GetStripedXML();
        return $result;
    }

    private function dogetholdcomments($url, $count) {
        $this->domrss->CreateRoot( $this->getApp()->site->url . $url, Lang::get('comment', 'onrecent') . ' ' .  $this->getApp()->site->name);
        $manager = CommentManager::i();
        $recent = $manager->getrecent($count, 'hold');
        $title = Lang::get('comment', 'onpost') . ' ';
        $comment = new \ArrayObject([], ArrayObject::ARRAY_AS_PROPS);
        foreach ($recent as $item) {
            $comment->exchangeArray($item);
            $this->AddRSSComment($comment, $title . $comment->title);
        }
    }

    public function GetRSSPostComments($idpost) {
        $post = Post::i($idpost);
        $lang = Lang::i('comment');
        $title = $lang->from . ' ';
        $this->domrss->CreateRoot($post->rsscomments, "$lang->onpost $post->title");
        $comments = Comments::i($idpost);
        $comtable = $comments->thistable;
        $comment = new \ArrayObject([], ArrayObject::ARRAY_AS_PROPS);

        $recent = $comments->select("$comtable.post = $idpost and $comtable.status = 'approved'", "order by $comtable.posted desc limit " .  $this->getApp()->options->perpage);

        foreach ($recent as $id) {
            $comment->exchangeArray ($comments->getitem($id));
            $comment->posturl = $post->url;
            $comment->title = $post->title;
            $this->AddRSSComment($comment, $title . $comment->name);
        }
    }

    public function addpost(tpost $post) {
        $item = $this->domrss->AddItem();
        Node::addvalue($item, 'title', $post->title);
        Node::addvalue($item, 'link', $post->link);
        Node::addvalue($item, 'comments', $post->link . '#comments');
        Node::addvalue($item, 'pubDate', $post->pubdate);

        $guid = Node::addvalue($item, 'guid', $post->link);
        Node::attr($guid, 'isPermaLink', 'true');

        if (class_exists('tprofile')) {
            $profile = tprofile::i();
            Node::addvalue($item, 'dc:creator', $profile->nick);
        } else {
            Node::addvalue($item, 'dc:creator', 'admin');
        }

        $categories = Categories::i();
        $names = $categories->getnames($post->categories);
        foreach ($names as $name) {
            if (empty($name)) {
 continue;
}


            Node::addcdata($item, 'category', $name);
        }

        $tags = Tags::i();
        $names = $tags->getnames($post->tags);
        foreach ($names as $name) {
            if (empty($name)) {
 continue;
}


            Node::addcdata($item, 'category', $name);
        }

        $content = '';
        $this->callevent('beforepost', array(
            $post->id, &$content
        ));
        if ($this->template == '') {
            $content.= $post->replacemore($post->rss, true);
        } else {
            $content.= Theme::parsevar('post', $post, $this->template);
        }
        $this->callevent('afterpost', array(
            $post->id, &$content
        ));
        Node::addcdata($item, 'content:encoded', $content);
        Node::addcdata($item, 'description', strip_tags($content));
        Node::addvalue($item, 'wfw:commentRss', $post->rsscomments);

        if (count($post->files) > 0) {
            $files = Files::i();
            $files->loaditems($post->files);
            foreach ($post->files as $idfile) {
                $file = $files->getitem($idfile);
                $enclosure = Node::add($item, 'enclosure');
                Node::attr($enclosure, 'url',  $this->getApp()->site->files . '/files/' . $file['filename']);
                Node::attr($enclosure, 'length', $file['size']);
                Node::attr($enclosure, 'type', $file['mime']);
            }
        }
        $post->onrssitem($item);
        $this->onpostitem($item, $post);
        return $item;
    }

    public function AddRSSComment($comment, $title) {
        $link =  $this->getApp()->site->url . $comment->posturl . '#comment-' . $comment->id;
        $date = is_int($comment->posted) ? $comment->posted : strtotime($comment->posted);
        $item = $this->domrss->AddItem();
        Node::addvalue($item, 'title', $title);
        Node::addvalue($item, 'link', $link);
        Node::addvalue($item, 'dc:creator', $comment->name);
        Node::addvalue($item, 'pubDate', date('r', $date));
        Node::addvalue($item, 'guid', $link);
        Node::addcdata($item, 'description', strip_tags($comment->content));
        Node::addcdata($item, 'content:encoded', $comment->content);
    }

    public function SetFeedburnerLinks($rss, $comments) {
        if (($this->feedburner != $rss) || ($this->feedburnercomments != $comments)) {
            $this->feedburner = $rss;
            $this->feedburnercomments = $comments;
            $this->save();
             $this->getApp()->cache->clear();
        }
    }

}
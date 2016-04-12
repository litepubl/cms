<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

namespace litepubl\post;
use litepubl\tag\Categories;
use litepubl\tag\Tags;
use litepubl\comments\Comments;
use litepubl\coments\Manager as CommentManager;
use litepubl\widget\Comments as CommentWidget;
use litepubl\perm\Perm;

class Rss extends \litepubl\core\Events
 {
    public $domrss;

    protected function create() {
        parent::create();
        $this->basename = 'rss';
        $this->addevents('beforepost', 'afterpost', 'onpostitem');
        $this->data['feedburner'] = '';
        $this->data['feedburnercomments'] = '';
        $this->data['template'] = '';
        $this->data['idcomments'] = 0;
        $this->data['idpostcomments'] = 0;
    }

    public function commentschanged() {
        litepubl::$router->setexpired($this->idcomments);
        litepubl::$router->setexpired($this->idpostcomments);
    }

    public function request($arg) {
        $result = '';
        if (($arg == 'posts') && ($this->feedburner != '')) {
            $result.= "<?php
      if (!preg_match('/feedburner|feedvalidator/i', \$_SERVER['HTTP_USER_AGENT'])) {
        return litepubl::\$urlmap->redir('$this->feedburner', 307);
      }
      ?>";
        } elseif (($arg == 'comments') && ($this->feedburnercomments != '')) {
            $result.= "<?php
      if (!preg_match('/feedburner|feedvalidator/i', \$_SERVER['HTTP_USER_AGENT'])) {
        return litepubl::\$urlmap->redir('$this->feedburnercomments', 307);
      }
      ?>";
        }

        $result.= '<?php litepubl\turlmap::sendxml(); ?>';
        $this->domrss = new DomRss();

        switch ($arg) {
            case 'posts':
                $this->getrecentposts();
                break;


            case 'comments':
                $this->GetRecentComments();
                break;


            case 'categories':
            case 'tags':
                if (!preg_match('/\/(\d*?)\.xml$/', litepubl::$urlmap->url, $match)) {
                    return 404;
                }

                $id = (int)$match[1];
                $tags = $arg == 'categories' ? Categories::i() : Tags::i();
                if (!$tags->itemexists($id)) {
                    return 404;
                }

                $tags->id = $id;
                if (isset($tags->idperm) && ($idperm = $tags->idperm)) {
                    $perm = Perm::i($idperm);
                    if ($header = $perm->getheader($tags)) {
                        $result = $header . $result;
                    }
                }

                $this->gettagrss($tags, $id);
                break;


            default:
                if (!preg_match('/\/(\d*?)\.xml$/', litepubl::$urlmap->url, $match)) {
                    return 404;
                }

                $idpost = (int)$match[1];
                $posts = Posts::i();
                if (!$posts->itemexists($idpost)) {
                    return 404;
                }

                $post = Post::i($idpost);
                if ($post->status != 'published') {
                    return 404;
                }

                if (isset($post->idperm) && ($post->idperm > 0)) {
                    $perm = Perm::i($post->idperm);
                    if ($header = $perm->getheader($post)) {
                        $result = $header . $result;
                    }
                }

                $this->GetRSSPostComments($idpost);
        }

        $result.= $this->domrss->GetStripedXML();
        return $result;
    }

    public function getrecentposts() {
        $this->domrss->CreateRoot(litepubl::$site->url . '/rss.xml', litepubl::$site->name);
        $posts = Posts::i();
        $this->getrssposts($posts->getpage(0, 1, litepubl::$options->perpage, false));
    }

    public function getrssposts(array $list) {
        foreach ($list as $id) {
            $this->addpost(Post::i($id));
        }
    }

    public function gettagrss(tcommontags $tags, $id) {
        $this->domrss->CreateRoot(litepubl::$site->url . litepubl::$urlmap->url, $tags->getvalue($id, 'title'));

        $items = $tags->getidposts($id);
        $this->getrssposts(array_slice($items, 0, litepubl::$options->perpage));
    }

    public function GetRecentComments() {
        $this->domrss->CreateRoot(litepubl::$site->url . '/comments.xml', tlocal::get('comment', 'onrecent') . ' ' . litepubl::$site->name);

        $title = tlocal::get('comment', 'onpost') . ' ';
        $comment = new tarray2prop();
        $recent = CommentWidget::i()->getrecent(litepubl::$options->perpage);
        foreach ($recent as $item) {
            $comment->array = $item;
            $this->AddRSSComment($comment, $title . $comment->title);
        }
    }

    public function getholdcomments($url, $count) {
        $result = '<?php litepubl\turlmap::sendxml(); ?>';
        $this->dogetholdcomments($url, $count);
        $result.= $this->domrss->GetStripedXML();
        return $result;
    }

    private function dogetholdcomments($url, $count) {
        $this->domrss->CreateRoot(litepubl::$site->url . $url, tlocal::get('comment', 'onrecent') . ' ' . litepubl::$site->name);
        $manager = CommentManager::i();
        $recent = $manager->getrecent($count, 'hold');
        $title = tlocal::get('comment', 'onpost') . ' ';
        $comment = new tarray2prop();
        foreach ($recent as $item) {
            $comment->array = $item;
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
        $comment = new tarray2prop();

        $recent = $comments->select("$comtable.post = $idpost and $comtable.status = 'approved'", "order by $comtable.posted desc limit " . litepubl::$options->perpage);

        foreach ($recent as $id) {
            $comment->array = $comments->getitem($id);
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
            if (empty($name)) continue;
            Node::addcdata($item, 'category', $name);
        }

        $tags = Tags::i();
        $names = $tags->getnames($post->tags);
        foreach ($names as $name) {
            if (empty($name)) continue;
            Node::addcdata($item, 'category', $name);
        }

        $content = '';
        $this->callevent('beforepost', array(
            $post->id, &$content
        ));
        if ($this->template == '') {
            $content.= $post->replacemore($post->rss, true);
        } else {
            $content.= ttheme::parsevar('post', $post, $this->template);
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
                Node::attr($enclosure, 'url', litepubl::$site->files . '/files/' . $file['filename']);
                Node::attr($enclosure, 'length', $file['size']);
                Node::attr($enclosure, 'type', $file['mime']);
            }
        }
        $post->onrssitem($item);
        $this->onpostitem($item, $post);
        return $item;
    }

    public function AddRSSComment($comment, $title) {
        $link = litepubl::$site->url . $comment->posturl . '#comment-' . $comment->id;
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
            litepubl::$urlmap->clearcache();
        }
    }

} //class
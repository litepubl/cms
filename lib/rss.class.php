<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class trss extends tevents {
  public $domrss;
  
  public static function i() {
    return getinstance(__class__);
  }
  
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
    litepublisher::$urlmap->setexpired($this->idcomments);
    litepublisher::$urlmap->setexpired($this->idpostcomments);
  }
  
  public function request($arg) {
    $result = '';
    if (($arg == 'posts') && ($this->feedburner  != '')) {
      $result .= "<?php
      if (!preg_match('/feedburner|feedvalidator/i', \$_SERVER['HTTP_USER_AGENT'])) {
        return litepublisher::\$urlmap->redir('$this->feedburner', 307);
      }
      ?>";
    }elseif (($arg == 'comments') && ($this->feedburnercomments  != '')) {
      $result .= "<?php
      if (!preg_match('/feedburner|feedvalidator/i', \$_SERVER['HTTP_USER_AGENT'])) {
        return litepublisher::\$urlmap->redir('$this->feedburnercomments', 307);
      }
      ?>";
    }
    
    $result .= '<?php turlmap::sendxml(); ?>';
    $this->domrss = new tdomrss;
    switch ($arg) {
      case 'posts':
      $this->getrecentposts();
      break;
      
      case 'comments':
      $this->GetRecentComments();
      break;
      
      case 'categories':
      case 'tags':
      if (!preg_match('/\/(\d*?)\.xml$/', litepublisher::$urlmap->url, $match)) return 404;
      $id = (int) $match[1];
      $tags = $arg == 'categories' ? tcategories::i() : ttags::i();
      if (!$tags->itemexists($id)) return 404;
      $tags->id =$id;
      if (isset($tags->idperm) && ($idperm = $tags->idperm)) {
        $perm =tperm::i($idperm);
        if ($header = $perm->getheader($tags)) {
          $result = $header . $result;
        }
      }
      $this->gettagrss($tags, $id);
      break;
      
      default:
      if (!preg_match('/\/(\d*?)\.xml$/', litepublisher::$urlmap->url, $match)) return 404;
      $idpost = (int) $match[1];
      $posts = tposts::i();
      if (!$posts->itemexists($idpost)) return 404;
      $post = tpost::i($idpost);
      if ($post->status != 'published') return 404;
      if (isset($post->idperm) && ($post->idperm > 0)) {
        $perm =tperm::i($post->idperm);
        if ($header = $perm->getheader($post)) {
          $result = $header . $result;
        }
      }
      
      $this->GetRSSPostComments($idpost);
    }
    
    $result .= $this->domrss->GetStripedXML();
    return $result;
  }
  
  public function getrecentposts() {
    $this->domrss->CreateRoot(litepublisher::$site->url. '/rss.xml', litepublisher::$site->name);
    $posts = tposts::i();
    $this->getrssposts($posts->getpage(0, 1, litepublisher::$options->perpage, false));
  }
  
  public function getrssposts(array $list) {
    foreach ($list as $id ) {
      $this->addpost(tpost::i($id));
    }
  }
  
  public function gettagrss(tcommontags $tags, $id) {
    $this->domrss->CreateRoot(litepublisher::$site->url. litepublisher::$urlmap->url, $tags->getvalue($id, 'title'));
    
    $items = $tags->getidposts($id);
    $this->getrssposts(array_slice($items, 0, litepublisher::$options->perpage));
  }
  
  public function GetRecentComments() {
    $this->domrss->CreateRoot(litepublisher::$site->url . '/comments.xml', tlocal::get('comment', 'onrecent') . ' '. litepublisher::$site->name);
    
    $title = tlocal::get('comment', 'onpost') . ' ';
    $comment = new tarray2prop();
    $recent = tcommentswidget::i()->getrecent(litepublisher::$options->perpage);
    foreach ($recent  as $item) {
      $comment->array = $item;
      $this->AddRSSComment($comment, $title . $comment->title);
    }
  }
  
  public function getholdcomments($url, $count) {
    $result = '<?php turlmap::sendxml(); ?>';
    $this->dogetholdcomments($url, $count);
    $result .= $this->domrss->GetStripedXML();
    return $result;
  }
  
  private function dogetholdcomments($url, $count) {
    $this->domrss->CreateRoot(litepublisher::$site->url . $url, tlocal::get('comment', 'onrecent') . ' '. litepublisher::$site->name);
    $manager = tcommentmanager::i();
    $recent = $manager->getrecent($count, 'hold');
    $title = tlocal::get('comment', 'onpost') . ' ';
    $comment = new tarray2prop();
    foreach ($recent  as $item) {
      $comment->array = $item;
      $this->AddRSSComment($comment, $title . $comment->title);
    }
  }
  
  public function GetRSSPostComments($idpost) {
    $post = tpost::i($idpost);
    $lang = tlocal::i('comment');
    $title = $lang->from . ' ';
    $this->domrss->CreateRoot($post->rsscomments, "$lang->onpost $post->title");
    $comments = tcomments::i($idpost);
    $comtable = $comments->thistable;
    $comment = new tarray2prop();
    
    $recent = $comments->select("$comtable.post = $idpost and $comtable.status = 'approved'",
    "order by $comtable.posted desc limit ". litepublisher::$options->perpage);
    
    foreach ($recent  as $id) {
      $comment->array = $comments->getitem($id);
      $comment->posturl = $post->url;
      $comment->title = $post->title;
      $this->AddRSSComment($comment, $title . $comment->name);
    }
  }
  
  public function addpost(tpost $post) {
    $item = $this->domrss->AddItem();
    tnode::addvalue($item, 'title', $post->title);
    tnode::addvalue($item, 'link', $post->link);
    tnode::addvalue($item, 'comments', $post->link . '#comments');
    tnode::addvalue($item, 'pubDate', $post->pubdate);
    
    $guid  = tnode::addvalue($item, 'guid', $post->link);
    tnode::attr($guid, 'isPermaLink', 'true');
    
    if (class_exists   ('tprofile')) {
      $profile = tprofile::i();
      tnode::addvalue($item, 'dc:creator', $profile->nick);
    } else {
      tnode::addvalue($item, 'dc:creator', 'admin');
    }
    
    $categories = tcategories::i();
    $names = $categories->getnames($post->categories);
    foreach ($names as $name) {
      if (empty($name)) continue;
      tnode::addcdata($item, 'category', $name);
    }
    
    $tags = ttags::i();
    $names = $tags->getnames($post->tags);
    foreach ($names as $name) {
      if (empty($name)) continue;
      tnode::addcdata($item, 'category', $name);
    }
    
    $content = '';
    $this->callevent('beforepost', array($post->id, &$content));
    if ($this->template == '') {
      $content .= $post->replacemore($post->rss, true);
    } else {
      $content .= ttheme::parsevar('post', $post, $this->template);
    }
    $this->callevent('afterpost', array($post->id, &$content));
    tnode::addcdata($item, 'content:encoded', $content);
    tnode::addcdata($item, 'description', strip_tags($content));
    tnode::addvalue($item, 'wfw:commentRss', $post->rsscomments);
    
    if (count($post->files) > 0) {
      $files = tfiles::i();
      $files->loaditems($post->files);
      foreach ($post->files as $idfile) {
        $file = $files->getitem($idfile);
        $enclosure = tnode::add($item, 'enclosure');
        tnode::attr($enclosure , 'url', litepublisher::$site->files . '/files/' . $file['filename']);
        tnode::attr($enclosure , 'length', $file['size']);
        tnode::attr($enclosure , 'type', $file['mime']);
      }
    }
    $post->onrssitem($item);
    $this->onpostitem($item, $post);
    return $item;
  }
  
  public function AddRSSComment($comment, $title) {
    $link = litepublisher::$site->url . $comment->posturl . '#comment-' . $comment->id;
    $date = is_int($comment->posted) ? $comment->posted : strtotime($comment->posted);
    $item = $this->domrss->AddItem();
    tnode::addvalue($item, 'title', $title);
    tnode::addvalue($item, 'link', $link);
    tnode::addvalue($item, 'dc:creator', $comment->name);
    tnode::addvalue($item, 'pubDate', date('r', $date));
    tnode::addvalue($item, 'guid', $link);
    tnode::addcdata($item, 'description', strip_tags($comment->content));
    tnode::addcdata($item, 'content:encoded', $comment->content);
  }
  
  public function SetFeedburnerLinks($rss, $comments) {
    if (($this->feedburner != $rss) || ($this->feedburnercomments != $comments)) {
      $this->feedburner= $rss;
      $this->feedburnercomments = $comments;
      $this->save();
      litepublisher::$urlmap->clearcache();
    }
  }
  
}//class
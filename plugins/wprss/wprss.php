<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2012 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class twprssimporter extends timporter {
  private $tagsmap;
  
  public static function i() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->data['script'] = '';
    $this->data['ignorelink'] = false;
    $this->tagsmap = array(
    'title' => 'title',
    'pubDate' => 'pubdate',
    'content:encoded' => 'content',
    //'wp:post_id' => 'id',
    'wp:post_parent' => 'parent'
    );
    
  }
  
  public function getcontent() {
    $result = parent::getcontent();
    $args = targs::i();
    $args->script = $this->script;
    $args->ignorelink = $this->ignorelink;
    $about = tplugins::getabout(tplugins::getname(__file__));
    $args->scriptlabel = $about['scriptlabel'];
    $args->ignorelinklabel = $about['ignorelink'];
    $tml = file_get_contents(dirname(__file__) . DIRECTORY_SEPARATOR . 'form.tml');
    $html = tadminhtml::i();
    $result .= $html->parsearg($tml, $args);
    return $result;
  }
  
  public function processform() {
    if ($_POST['form'] != 'options')  return parent::ProcessForm();
    $this->data['script'] = $_POST['script'];
    $this->data['ignorelink'] = isset($_POST['ignorelink']);
    $this->save();
  }
  
  public function import($s) {
    require_once(litepublisher::$paths->lib . 'domrss.class.php');
    $a = xml2array($s);
    
    $urlmap = turlmap::i();
    $urlmap->lock();
    $cats = tcategories::i();
    $cats->lock();
    $tags = ttags::i();
    $tags->lock();
    $posts = tposts::i();
    $posts->lock();
    foreach ($a['rss']['channel'][0]['item'] as $item) {
      if ($post = $this->add($item)) {
        $posts->add($post);
        if (isset($item['wp:comment']) && is_array($item['wp:comment'])) {
          $this->importcomments($item['wp:comment'], $post->id);
        }
        if (!tfilestorage::$disabled) $post->free();
      }
    }
    $posts->unlock();
    $tags->unlock();
    $cats->unlock();
    $urlmap->unlock();
  }
  
  public function add(array $item) {
    if (isset($item['wp:post_type']) && ($item['wp:post_type'] != 'post')) return false;
    $post = tpost::i();
    foreach ($this->tagsmap as $key => $val) {
      if (isset($item[$key])) {
      $post->{$val} = $item[$key];
      }
    }
    
    if (!$this->ignorelink && isset($item['link'])) $post->link = $item['link'];
    if (isset($item['wp:status'])) {
      $post->status = $item['wp:status'] == 'publish' ? 'published' : 'draft';
    }
    
    if (isset($item['wp:comment_status'])) {
      $post->commentsenabled = $item['wp:comment_status'] == 'open';
    }
    
    if (isset($item['wp:ping_status'])) {
      $post->pingenabled = $item['wp:ping_status'] == 'open';
    }
    
    if (isset($item['category'])) {
      $post->categories = $this->getcategories($item['category'], 'category');
      $post->tags = $this->getcategories($item['category'], 'tag');
    }
    
    if ($this->script != '') eval($this->script);
    return $post;
  }
  
  private function getcategories($values, $type) {
    $result = array();
    $tags = $type == 'tag' ? ttags::i() : tcategories::i();
    if (!is_array($values)) {
      if ($type == 'tag') return $result;
      $result[] = $tags->add(0, $values);
      return $result;
    }
    
    foreach ($values as $item) {
      if (is_array($item)) {
        if (!isset($item['attributes']['domain']) || ($item['attributes']['domain'] != $type)) continue;
        $id = $tags->add(0, $item[0]);
      } else {
        if ($type == 'tag') continue;
        $id = $tags->add(0, $item);
      }
      if (!in_array($id, $result)) $result[] = $id;
    }
    
    return $result;
  }
  
  private function importcomments(array $items, $idpost) {
    $comments = tcomments::i($idpost);
    $comments->lock();
    $comusers = tcomusers::i($idpost);
    $comusers->lock();
    foreach ($items as $item) {
      $status = $item['wp:comment_approved'] == '1' ? 'approved' : 'hold';
      $posted = strtotime($item['wp:comment_date']);
      if ($item['wp:comment_type'] == 'pingback') {
        $pingbacks = tpingbacks::i($idpost);
        $pingbacks->import( $item['wp:comment_author_url'], $item['wp:comment_author'], $posted, $item['wp:comment_author_IP'], $status);
        continue;
      }
      
      $idauthor = $comusers->add($item['wp:comment_author'], $item['wp:comment_author_email'], $item['wp:comment_author_url']);
      $comments->add($idpost, $idauthor,  $item['wp:comment_content'], $item['wp:comment_author_IP'], $status, $posted);
    }
    $comusers->unlock();
    $comments->unlock();
  }
  
}//class
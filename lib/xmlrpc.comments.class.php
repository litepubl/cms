<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class TXMLRPCComments extends TXMLRPCAbstract {
  
  public static function i() {
    return getinstance(__class__);
  }
  
  public function delete($login, $password, $id, $idpost) {
    $this->auth($login, $password, 'moderator');
    $manager = tcommentmanager::i();
    if (!$manager->delete((int) $id, (int) $idpost)) return $this->xerror(404, "Comment not deleted");
    return true;
  }
  
  public function setstatus($login, $password, $id, $idpost, $status) {
    $this->auth($login, $password, 'moderator');
    $manager = tcommentmanager::i();
    if (!$manager->setstatus((int) $id, (int) $idpost, $status)) return $this->xerror(404, "Comment status not changed");
    return true;
  }
  
  public function add($login, $password, $idpost, $name, $email, $url, $content) {
    $this->auth($login, $password, 'moderator');
    $manager = tcommentmanager::i();
    return $manager->add((int) $idpost, $name, $email, $url, $content);
  }
  
  public function edit($login, $password, $id, $idpost, $comment) {
    $this->auth($login, $password, 'moderator');
    $manager = tcommentmanager::i();
    if (!$manager->edit((int) $id, (int) $idpost, $comment['name'], $comment['email'], $comment['url'], $comment['content'])) {
      return $this->xerror(404, 'Comment not edited');
    }
    return true;
  }
  
  public function reply($login, $password, $id, $idpost, $content) {
    $this->auth($login, $password, 'moderator');
    $manager = tcommentmanager::i();
    return $manager->reply((int) $id, (int) $idpost, $content);
  }
  
  public function getcomment($login, $password, $id, $idpost) {
    $this->auth($login, $password, 'moderator');
    $comments = tcomments::i((int) $idpost);
    $comment = $comments->getcomment((int) $id);
    $result = array(
    'id' => (int) $comment->id,
    'author' => (int)$comment->author,
    'name' => $comment->name,
    'email' => $comment->email,
    'url' => $comment->website,
    'content' => $comment->content,
    'rawcontent' => $comment->rawcontent
    );
    return $result;
  }
  
  public function getrecent($login, $password, $count) {
    $this->auth($login, $password, 'moderator');
    $manager = tcommentmanager::i();
    return $manager->getrecent($count);
  }
  
  public function moderate($login, $password, $idpost, $list, $action) {
    $this->auth($login, $password, 'moderator');
    $idpost = (int) $idpost;
    $comments = tcomments::i($idpost);
    $comments->lock();
    $manager = tcommentmanager::i();
    $delete = $action == 'delete';
    foreach ($list as $id) {
      $id = (int) $id;
      if ($delete) {
        $manager->delete($id, $idpost);
      } else {
        $manager->setstatus($id, $idpost, $action);
      }
    }
    $comments->unlock();
    return true;
  }
  
  //wordpress api
  /* only db version */
  public function wpgetCommentCount($blog_id, $login, $password, $idpost) {
    $this->auth($login, $password, 'moderator');
    $idpost = (int) $idpost;
    $comments = tcomments::i($idpost);
    if (dbversion) {
      $approved = $comments->getcount("post = $idpost and status = 'approved'");
      $hold = $comments->getcount("post = $idpost and status = 'hold'");
      $spam= $comments->getcount("post = $idpost and status = 'spam'");
      $total = $comments->getcount("post = $idpost");
    } else {
      $approved = $comments->count;
      $hold = $comments->hold->count;
      $spam= 0;
      $total = $approved + $spam;
    }
    
    return array(
    "approved" => $approved,
    "awaiting_moderation" => $hold,
    "spam" => $spam,
    "total_comments" => $total
    );
  }
  
  public function wpgetComment($blog_id, $login, $password, $id) {
    $this->auth($login, $password, 'moderator');
    $id = (int) $id;
    $comments = tcomments::i();
    if ($comments->itemexists($id)) return $this->xerror(404, 'Invalid comment ID.');
    $comment = $comments->getcomment($id);
    return $this->_wpgetcomment($comment);
  }
  
  private function _wpgetcomment(tcomment $comment) {
    $data = $comment->data;
    
    return array(
    "date_created_gmt"		=> new IXR_Date($comment->posted - litepublisher::$options->gmt),
    "user_id"				=> $data['author'],
    "comment_id"			=> $id,
    "parent"				=> $data['parent'],
    "status"				=> $data['status'] == 'approved' ? 'approve' : $data['status'],
    "content"				=> $data['content'],
    "link"					=> $comment->link,
    "post_id"				=> $data['post'],
    "post_title"			=> $comment->posttitle,
    "author"				=> $data['name'],
    "author_url"			=> $data['url'],
    "author_email"			=> $data['email'],
    "author_ip"				=> $data['ip'],
    "type"					=> ''
    );
  }
  
  public function wpgetComments($blog_id, $login, $password, $struct) {
    $this->auth($login, $password, 'moderator');
    $where = '';
    $where .= isset($struct['status']) ? ' status = '. dbquote($struct['status']) : '';
    $where .= isset($struct['post_id']) ? ' post = ' . (int) $struct['post_id'] : '';
    $offset = isset($struct['offset']) ? (int) $struct['offset'] : 0;
    $count= isset($struct['number']) ? (int) $struct['number'] : 10;
    $limit = " order by posted limit $offset, $count";
    
    $comments = tcomments::i();
    $items = $comments->select($where, $limit);
    $result = array();
    $comment = new tcomment();
    foreach ($items as $id) {
      $comment->id = $id;
      $result[] = $this->_getcomment($comment);
    }
    return $result;
  }
  
  public function wpdeleteComment($blog_id, $login, $password, $id) {
    $this->auth($login, $password, 'moderator');
    $id = (int) $id;
    $comments = tcomments::i();
    if (!$comments->itemexists($id)) return $this->xerror(404, 'Invalid comment ID.');
    $manager = tcommentmanager::i();
    return $manager->delete($id);
  }
  
  public function wpeditComment($blog_id, $login, $password, $id, $struct) {
    $this->auth($login, $password, 'moderator');
    $id = (int) $id;
    $comments = tcomments::i();
    if (!$comments->itemexists($id)) return $this->xerror(404, 'Invalid comment ID.');
    $comment = $comment->getcomment($id);
    
    if ( isset($struct['status'])) {
      if (!preg_match('/^hold|approve|spam$/', $struct['status'])) return $this->xerror(401, 'Invalid comment status.');
      $comment->status = $struct['status'] == 'approve' ? 'approved' : $struct['status'];
    }
    
    $comusers = tcomusers::i();
    $comment->author = $comusers->add(
    isset($struct['author']) ? $struct['author'] : $comment->name,
    isset($struct['author_email']) ? $struct['author_email'] : $comment->email,
    isset($struct['author_url']) ? $struct['author_url'] : $comment->url
    );
    
    if ( !empty( $struct['date_created_gmt'] ) ) {
      $comment->posted = $struct['date_created_gmt']->getTimestamp();
    }
    
    if ( isset($struct['content']) ) {
      $comment->rawcontent = $struct['content'];
    }
    
    $comment->save();
    return true;
  }
  
  
  public function wpnewComment($blog_id, $login, $password, $idpost, $struct) {
    $this->auth($login, $password, 'moderator');
    
    if ( is_numeric($idpost) ) {
      $idpost = absint($idpost);
    } else {
      $urlmap = turlmap::i();
      if (!($item = $urlmap->finditem($url))) {
        return $this->xerror(404, 'Invalid post ID.');
      }
      
      if ($item['class'] != litepublisher::$classes->classes['post'])  {
        return $this->xerror(404, 'Invalid post ID.');
      }
      $idpost = $item['arg'];
    }
    
    $post = tpost::i($idpost);
    if (!$post->commentenabled || ($post->status != 'published')) {
      return $this->xerror(403, 'The specified post cannot be used to commenting');
    }
    
    $manager = tcommentmanager::i();
    return $manager->add($idpost,
    isset($struct['author']) ? $struct['author'] : '',
    isset($struct['author_email']) ? $struct['author_email'] : '',
    isset($struct['author_url']) ? $struct['author_url'] : '',
    $struct['content']
    );
  }
  
  
  public function wpgetCommentStatusList($blog_id, $login, $password) {
    $this->auth($login, $password, 'moderator');
    return array(
    'hold'		=> 'Unapproved',
    'approve'	=> 'Approved',
    'spam'		=> 'Spam',
    );
  }
  
}//class
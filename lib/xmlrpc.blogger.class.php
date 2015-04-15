<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class TXMLRPCBlogger  extends TXMLRPCAbstract {
  
  public static function i() {
    return getinstance(__class__);
  }
  
  /*
  appkey (string): Unique identifier/passcode of the application sending the post. (See access info.)
  username (string): Login for the Blogger user who's blogs will be retrieved.
  password (string): Password for said username.
  */
  public function getUsersBlogs($appkey, $login, $password) {
    $this->auth($login, $password, 'author');
    
    $result = array(
    //'isAdmin'  => true,
    'url'      => litepublisher::$site->url . '/',
    'blogid'   => '1',
    'blogName' => litepublisher::$site->name
    );
    return array($result);
  }
  
  public function getUserInfo($appkey, $login, $password) {
    $this->auth($login, $password, 'author');
    
    $result= array(
    'nickname'  => $login,
    'userid'    => litepublisher::$options->user,
    'url'       => litepublisher::$site->url .'/',
    'lastname'  => '',
    'firstname' => ''		);
    return $result;
  }
  
  public function getPost($appkey, $id, $login, $password) {
    $id    = (int) $id;
    $this->canedit($login, $password, $id);
    $posts= tposts::i();
    if (!$posts->itemexists($id)) return $this->xerror(404, "Sorry, no such post.");
    
    $Post = tpost::i($id);
    $categories = implode(',', $Post->categories);
    
    $content  = '<title>'.$Post->title .'</title>';
    $content .= '<category>'.$categories.'</category>';
    $content .= $Post->content;
    
    $result= array(
    'userid'    => $Post->user,
    'dateCreated' => new IXR_Date($Post->posted),
    'content'     => $content,
    'postid'  => $id
    );
    
    return $result;
  }
  
  public function getRecentPosts($appkey, $blogid, $login, $password, $count) {
    $this->auth($login, $password, 'author');
    
    $posts = tposts::i();
    $Items = $posts->finditems("status = 'published'", " order by posted desc limit 0, " . ((int) $count));
    
    foreach ($Items as $id) {
      $Post = tpost::i($id);
      $categories = implode(',', $Post->categories);
      $content  = '<title>'.$Post->title . '</title>';
      $content .= '<category>'.$categories.'</category>';
      $content .= $Post->content;
      
      $result[] = array(
      'userid' => litepublisher::$options->user,
      'dateCreated' => new IXR_Date($Post->date),
      'content' => $content,
      'postid' => $Post->id,
      );
    }
    
    return $result;
  }
  
  private function getposttitle($content) {
    if ( preg_match('/<title>(.+?)<\/title>/is', $content, $matchtitle) ) {
      $result = $matchtitle[0];
      $result = preg_replace('/<title>/si', '', $result);
      $result = preg_replace('/<\/title>/si', '', $result);
    } else {
      $result = 'no title';
    }
    return $result;
  }
  
  private function getpostcategory($content) {
    if ( preg_match('/<category>(.+?)<\/category>/is', $content, $matchcat) ) {
      $result = trim($matchcat[1], ',');
      $result = explode(',', $result);
    } else {
      $result = array(1);
    }
    return $result;
  }
  
  private function removepostdata($content) {
    $content = preg_replace('/<title>(.+?)<\/title>/si', '', $content);
    $content = preg_replace('/<category>(.+?)<\/category>/si', '', $content);
    $content = trim($content);
    return $content;
  }
  
  /*
  appkey (string): Unique identifier/passcode of the application sending the post. (See access info.)
  blogid (string): Unique identifier of the blog the post will be added to.
  username (string): Login for a Blogger user who has permission to post to the blog.
  password (string): Password for said username.
  content (string): Contents of the post.
  publish (boolean): If true, the blog will be published immediately after the post is made.
  */
  public function newPost($appkey, $blogid, $login, $password, $content, $publish) {
    $this->auth($login, $password, 'author');
    
    $posts = tposts::i();
    $post = tpost::i(0);
    $post->status = $publish ? 'published' : 'draft';
    $post->title = $this->getposttitle($content);
    $post->content = $this->removepostdata($content);
    $post->categories = $this->getpostcategory($content);
    
    $id = $posts->add($post);
    return (string) $id;
  }
  
  public function editPost($appkey, $id, $login, $password, $content, $publish) {
    $id = (int) $id;
    $this->canedit($login, $password, $id);
    $posts = tposts::i();
    if (!$posts->itemexists($id)) return $this->xerror(404, 'Sorry, no such post.');
    $post = tpost::i($id);
    $post->status = $publish ? 'published' : 'draft';
    $post->title = $this->getposttitle($content);
    $post->content = $this->removepostdata($content);
    $post->categories = $this->getpostcategory($content);
    
    $posts->edit($post);
    return true;
  }
  
  public function deletePost($appkey, $id, $login, $password) {
    $id = (int) $id;
    $this->canedit($login, $password, $id);
    $posts = tposts::i();
    if (!$posts->itemexists($id)) return $this->xerror(404, 'Sorry, no such post.');
    $posts->delete($id);
    return true;
  }
  
  public function getTemplate($appkey, $blogid, $login, $password, $templateType) {
    return '';
  }
  
  public function setTemplate($appkey, $blogid, $login, $password, $template , $templateType) {
    return true;
  }
}
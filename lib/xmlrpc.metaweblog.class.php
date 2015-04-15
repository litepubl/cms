<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class TXMLRPCMetaWeblog extends TXMLRPCAbstract {
  
  public static function i() {
    return getinstance(__class__);
  }
  
  protected function MWSetPingCommentStatus(array &$Struct, tpost $post) {
    if(isset($struct["mt_allow_comments"])) {
      if(!is_numeric($struct["mt_allow_comments"])) {
        switch($struct["mt_allow_comments"]) {
          case "closed":
          $post->comstatus = 'closed';
          break;
          case "open":
          $post->comstatus = 'guest';
          break;
          default:
          $post->comstatus = litepublisher::$options->comstatus;
          break;
        }
      }
      else {
        switch((int) $struct["mt_allow_comments"]) {
          case 0:
          $post->comstatus = 'closed';
          break;
          
          case 1:
          $post->comstatus = 'guest';
          break;
          
          default:
          $post->comstatus = litepublisher::$options->comstatus;
          break;
        }
      }
    }
    else {
      $post->comstatus = litepublisher::$options->comstatus;
    }
    
    if(isset($struct["mt_allow_pings"])) {
      if(!is_numeric($struct["mt_allow_pings"])) {
        switch($struct['mt_allow_pings']) {
          case "closed":
          $post->pingenabled = false;
          break;
          case "open":
          $post->pingenabled = true;
          break;
          default:
          $post->pingenabled = litepublisher::$options->pingenabled;
          break;
        }
      }
      else {
        switch((int) $struct["mt_allow_pings"]) {
          case 0:
          $post->pingenabled = false;
          break;
          case 1:
          $post->pingenabled = true;
          break;
          default:
          $post->pingenabled = litepublisher::$options->pingenabled;
          break;
        }
      }
    }
    else {
      $post->pingenabled = litepublisher::$options->pingenabled;
    }
  }
  
  protected function MWSetDate(array &$struct, $post) {
    foreach (array('dateCreated', 'pubDate') as $name) {
      if (!empty($struct[$name])) {
        if (is_object($struct[$name])) {
          $post->posted = $struct[$name]->getTimestamp();
        } else {
          $post->pubdate  = $struct[$name];
        }
        return;
      }
    }
    $post->posted = time();
  }
  
  //forward implementation
  public function wp_newPage($blogid, $username, $password, $struct, $publish) {
    $this->auth($username, $password, 'editor');
    $menus = tmenus::i();
    $menu = tmenu::i(0);
    $menu->status = $publish ? 'published' : 'draft';
    $this->WPAssignPage($struct, $menu);
    return "menu_" . $menus->add($menu);
  }
  
  protected function  WPAssignPage(array &$struct, tmenu $menu) {
    $menu->title = $struct['title'];
    if (empty($struct['mt_text_more'])) {
      $menu->content = $struct['description'];
    } else {
      $menu->content = $struct['description'] . $struct['mt_text_more'];
    }
    
    if(!empty($struct["wp_slug"])) {
      $linkgen = tlinkgenerator::i();
      $menu->url = $linkgen->AddSlashes($struct['wp_slug']);
    }
    
    if(isset($struct["wp_password"])) {
      $menu->password = $struct["wp_password"];
    }
    
    if(isset($struct["wp_page_parent_id"])) {
      $parent = $struct["wp_page_parent_id"];
      if (strbegin($parent, 'menu_')) $parent = substr($parent, strlen('menu_'));
      $menu->parent = (int) $parent;
    }
    
    if(isset($struct["wp_page_order"])) {
      $menu->order = (int) $struct["wp_page_order"];
    }
    
    /* custom_fields is not supported */
  }
  
  /* <item> in RSS 2.0, providing a rich variety of item-level metadata, with well-understood applications.
  The three basic elements are title, link and description.  */
  public function  setpost(array &$struct, tpost $post) {
    $post->title = $struct['title'];
    $more = isset($struct['mt_text_more']) ? trim($struct['mt_text_more']) : '';
    if ($more == '') {
      $post->content = $struct['description'];
    } else {
      $morelink = sprintf("\n<!--more %s-->\n", tlocal::get('post', 'more'));
      $post->content = $struct['description']. $morelink . $more;
    }
    
    $excerpt =isset($struct['mt_excerpt']) ? trim($struct['mt_excerpt']) : '';
    if ($excerpt != '') $post->excerpt = $excerpt;
    
    if (isset($struct['categories']) && is_array($struct['categories'])) {
      $post->catnames = $struct['categories'];
    }
    
    if(!empty($struct["wp_slug"])) {
      $linkgen = tlinkgenerator::i();
      $post->url = $linkgen->AddSlashes($struct["wp_slug"] . '/');
    } elseif (!empty($struct['link'])) {
      $post->link = $struct['link'];
    } elseif (!empty($struct['guid'])) {
      $post->link = $struct['guid'];
    } elseif (!empty($struct['permaLink'])) {
      $post->link = $struct['permaLink'];
    }
    
    if(isset($struct['wp_password'])) {
      $post->password = $struct['wp_password'];
    }
    
    if (!empty($struct['mt_keywords'])) {
      $post->tagnames = $struct['mt_keywords'];
    }
    
    $this->MWSetDate($struct, $post);
    $this->MWSetPingCommentStatus($struct, $post);
    
    /* not supported yet
    if (isset($struct['flNotOnHomePage']) && $struct['flNotOnHomePage']) {
      //exclude post from homepage
    }
    
    if (!empty($struct['enclosure'])) {
      //enclosure Describes a media object that is attached to the item.
      <enclosure> is an optional sub-element of <item>.
      
      It has three required attributes. url says where the enclosure is located, length says how big it is in bytes, and type says what its type is, a standard MIME type.
      
      The url must be an http url.
      
      <enclosure url="http://www.scripting.com/mp3s/weatherReportSuite.mp3" length="12216320" type="audio/mpeg" />
      
      A use-case narrative for this element is here.
    }
    
    */
  }
  
  public function wp_editPage($blogid, $id, $username, $password, $struct, $publish) {
    $this->auth($username, $password, 'editor');
    if (strbegin($id, 'menu_')) $id = substr($id, strlen('menu_'));
    $id = (int) $id;
    $menus = tmenus::i();
    if (!$menus->itemexists($id))  return $this->xerror(404, "Sorry, no such page.");
    $menu = tmenu::i($id);
    $menu->status = $publish ? 'published' : 'draft';
    $this->WPAssignPage($struct, $menu);
    $menus->edit($menu);
    return true;
  }
  
  /* returns struct.
  The struct returned contains one struct for each category, containing the following elements: description, htmlUrl and rssUrl. */
  
  public function getCategories($blogid, $username, $password) {
    $this->auth($username, $password, 'author');
    
    $categories = tcategories::i();
    $categories->loadall();
    $result = array();
    foreach ( $categories->items as $id => $item) {
      $result[] = array(
      'categoryId' =>   $id,
      'parentId' => $item['parent'],
      'description' => $categories->contents->getdescription($item['id']),
      'categoryName' => $item['title'],
      'title' => $item['title'],
      'htmlUrl' => litepublisher::$site->url . $item['url'],
      'rssUrl' =>  litepublisher::$site->url . "/rss/categories/$id.xml"
      );
    }
    
    return $result;
  }
  
  //returns string
  public function newPost($blogid, $username, $password, $struct, $publish) {
    if(isset($struct["post_type"]) && ($struct["post_type"] == "page")) {
      return  $this->wp_newPage($blogid, $username, $password, $struct, $publish);
    }
    
    $this->auth($username, $password, 'author');
    $posts = tposts::i();
    $post = tpost::i(0);
    
    switch ($publish) {
      case 1:
      case 'true':
      case 'publish':
      $post->status = 'published';
      break;
      
      default:
      $post->status =  'draft';
    }
    
    $this->setpost($struct, $post);
    $id = $posts->add($post);
    return (string) $id;
  }
  
  // returns true
  public function editPost($postid, $username, $password, $struct, $publish) {
    if(!empty($struct["post_type"]) && ($struct["post_type"] == "page")) {
      return  $this->wp_editPage(0, $postid, $username, $password, $struct, $publish);
    }
    
    $postid = (int)$postid;
    $this->canedit($username, $password, $postid);
    $posts = tposts::i();
    if (!$posts->itemexists($postid))  return $this->xerror(404, "Invalid post id.");
    
    $post = tpost::i($postid);
    switch ($publish) {
      case 1:
      case 'true':
      case 'publish':
      $post->status = 'published';
      break;
      
      default:
      $post->status =  'draft';
    }
    
    $this->setpost($struct, $post);
    
    $posts->edit($post);
    return true;
  }
  
  // returns struct
  public function getPost($id, $username, $password) {
    $id=(int) $id;
    $this->canedit($username, $password, $id);
    $posts = tposts::i();
    if (!$posts->itemexists($id))  return $this->xerror(404, "Invalid post id.");
    
    $post = tpost::i($id);
    return $this->GetStruct($post);
  }
  
  private function GetStruct(tpost $post) {
    $categories = tcategories::i();
    return array(
    'dateCreated' => new IXR_Date($post->posted),
    'userid' => (string) $post->author,
    'postid' =>  (string) $post->id,
    'description' => $post->rawcontent,
    'title' => $post->title,
    'link' => $post->link,
    'permaLink' => $post->link,
    'categories' => $categories->getnames($post->categories),
    'mt_excerpt' => $post->excerpt,
    'mt_text_more' => '',
    'mt_allow_comments' => $post->comstatus != 'closed' ? 1 : 0,
    'mt_allow_pings' => $post->pingenabled ? 1 : 0,
    'mt_keywords' => $post->tagnames,
    'wp_slug' => $post->url,
    'wp_password' => $post->password,
    'wp_author_id' => $post->author,
    'wp_author_display_name'	=> 'admin',
    'date_created_gmt' => new IXR_Date($post->posted- litepublisher::$options->gmt),
    'publish' => $post->status == 'published' ? 1 : 0
    );
  }
  
  // returns array of structs
  public function getRecentPosts($blogid, $username, $password, $numberOfPosts) {
    $this->auth($username, $password, 'author');
    $count = (int) $numberOfPosts;
    $posts = tposts::i();
    $list = $posts->getrecent(litepublisher::$options->user, $count);
    $result = array();
    foreach ($list as $id) {
      $post = tpost::i($id);
      $result[] = $this->GetStruct($post);
    }
    
    return $result;
  }
  
  // returns struct
  public function newMediaObject($blogid, $username, $password, $struct) {
    $this->auth($username, $password, 'author');
    
    //The struct must contain at least three elements, name, type and bits.
    $filename = $struct['name'] ;
    //$mimetype =$struct['type'];
    $overwrite = isset($struct["overwrite"]) && $struct["overwrite"];
    
    if (empty($filename)) return $this->xerror(500, "Empty filename");
    
    $parser = tmediaparser::i();
    $id = $parser->upload($filename, $struct['bits'], '', '', '', $overwrite );
    
    if (!$id)  return $this->xerror(500, "Could not write file $name");
    $files = tfiles::i();
    $item = $files->getitem($id);
    
    return array(
    'file' => $item['filename'],
    'url' => $files->geturl($id),
    'type' => $item['mime']
    );
  }
  
}//class
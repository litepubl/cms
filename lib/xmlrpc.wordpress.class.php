<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class TXMLRPCWordpress extends TXMLRPCMetaWeblog {
  public static function i() {
    return getinstance(__class__);
  }
  
  private function menutostruct($id) {
    if (strbegin($id, 'menu_')) $id = substr($id, strlen('menu_'));
    $id	= (int) $id;
    $menus = tmenus::i();
    if (!$menus->itemexists($id))  return xerror(404, "Sorry, no such page.");
    $menu = tmenu::i($id);
    
    if ($menu->parent > 0) {
      $parent= tmenu::i($menu->parent);
      $ParentTitle = $parent->title;
    } else {
      $ParentTitle = "";
    }
    
    $Result = array(
    "dateCreated"			=> new IXR_Date(time()),
    "userid"				=> $menu->author,
    "page_id"				=> "menu_" . $menu->id,
    "page_status"			=> $menu->status == 'published' ? 'publish' : 'draft',
    "description"			=> $menu->content,
    "title"					=> $menu->title,
    "link"					=> $menu->url,
    "permaLink"				=> $menu->url,
    "categories"			=> array(),
    "excerpt"				=> '',
    "text_more"				=> '',
    "mt_allow_comments"		=> 0,
    //"mt_allow_pings"		=> $menu->pingenabled ? 1 : 0,
    "mt_allow_pings"		=> 0,
    
    "wp_slug"				=> $menu->url,
    "wp_password"			=> $menu->password,
    "wp_author"				=> 'ADMIN',
    "wp_page_parent_id"		=> "menu_" . $menu->parent,
    "wp_page_ParentTitle"	=> $ParentTitle,
    "wp_page_order"			=> $menu->order,
    "wp_author_id"			=> $menu->author,
    "wp_author_display_name"	=> 'ADMIN',
    "date_created_gmt"		=> new IXR_Date(time() - litepublisher::$options->gmt)
    );
    
    return$Result;
  }
  
  // return struct
  public function wp_getPage($blogid, $id, $username, $password) {
    $this->auth($username, $password, 'editor');
    return $this->menutostruct($id);
  }
  
  public function wp_getPages($blogid, $username, $password) {
    $this->auth($username, $password, 'editor');
    $result = array();
    $menus = tmenus::i();
    foreach ($menus->items as $id => $item) {
      $result[] = $this->menutostruct($id);
    }
    return $result;
  }
  
  public function wp_getPageList($blogid, $username, $password) {
    $this->auth($username, $password, 'editor');
    $result = array();
    $menus = tmenus::i();
    foreach ($menus->items as $id => $item) {
      $result[] = array(
      'page_id' => "menu_" . $id,
      'page_title' => $item['title'],
      'page_parent_id' => "menu_" . $item['parent'],
      'dateCreated' => new IXR_Date(time()),
      );
    }
    
    return $result;
  }
  
  public function wp_deletePage($blogid, $username, $password, $id) {
    $this->auth($username, $password, 'editor');
    if (strbegin($id, 'menu_')) $id = substr($id, strlen('menu_'));
    $id = (int) $id;
    $menus = tmenus::i();
    if (!$menus->itemexists($id))  return xerror(404, "Sorry, no such page.");
    $menus->delete($id);
    return true;
  }
  
  public function wp_newCategory($blogid, $username, $password, $struct) {
    $this->auth($username, $password, 'editor');
    $categories = tcategories::i();
    return(int) $categories->add($struct["name"], $category["slug"]);
  }
  
  public function deleteCategory ($blogid, $username, $password, $id) {
    $this->auth($username, $password, 'editor');
    $id = (int) $id;
    $categories = tcategories::i();
    if (!$categories->itemexists($id))  return xerror(404, "Sorry, no such page.");
    $categories->delete($id);
    return true;
  }
  
  public function getTags($blogid, $username, $password) {
    $this->auth($username, $password, 'editor');
    $tags = ttags::i();
    $result = array();
    $tags->loadall();
    foreach ($tags->items as $id => $item) {
      $result[] = array(
      'tag_id'		 => (string) $id,
      'name' => $item['title'],
      'count' => $item['itemscount'],
      'slug' => '',
      'html_url' => litepublisher::$site->url . $item['url'],
      'rss_url' => litepublisher::$site->url . $item['url']
      );
    }
    return $result;
  }
  
}//class

?>
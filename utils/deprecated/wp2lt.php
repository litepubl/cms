<?php
set_time_limit(100);
@ini_set('memory_limit', '48M'); 

define('litepublisher_mode', 'debug');
require('index.php');
//require_once('F:\web5\home\dest\www\index.debug.php');

if (file_exists('wp-load.php')) {
require('wp-load.php');
} else {
 require('wp-config.php');
}

      @Header( 'Cache-Control: no-cache, must-revalidate', true);
      @Header( 'Pragma: no-cache', true);

    error_reporting(E_ALL | E_NOTICE | E_STRICT | E_WARNING );
    ini_set('display_errors', 1);

echo "<pre>\n";

function ExportOptions() {
$options = litepublisher::$options;
$options->lock();
litepublisher::$site->name = get_option('blogname');
litepublisher::$site->description = get_option('blogdescription');
$options->email = get_option('admin_email');
$options->unlock();

 $robots = trobotstxt ::i();
 $robots->AddDisallow('/feed/');

$redir = tredirector::i();
$redir->items['/feed/'] = '/rss.xml';
$redir->items['/feed'] = '/rss.xml';
$redir->save();
}

function ExportPages() {
		global $wpdb;
$menus = tmenus::i();
$menus->lock();
  litepublisher::$urlmap->lock();
$r = $wpdb->get_results("SELECT max(ID) as autoid FROM $wpdb->posts WHERE post_type = 'page'");
$autoid = (int) $r[0]->autoid;
$menus->autoid = $autoid;
      $filter = tcontentfilter::i();
$list = $wpdb->get_results("SELECT * FROM $wpdb->posts WHERE post_type = 'page'");
foreach ($list as  $item) {
$id = (int) $item->ID;
if ($id == 1) $id = ++$menus->autoid;
if ($id == $menus->idhome) $id = ++$menus->autoid;

$menu = new tmenu();
$menu->data['id'] = 0;
  $menu->data['title'] = $item->post_title;
  $menu->data['rawcontent'] = $item->post_content;
      $menu->data['content'] = $filter->filter($item->post_content);
$menu->data['status'] = 'published';
$menu->data['order'] = (int) $item->menu_order;
$menu->data['parent'] = (int) $item->post_parent;

  $url =@get_permalink($item->ID);
$UrlArray = parse_url($url);
$url = $UrlArray['path'];
if (!empty($UrlArray['query'])) $url .= '?' . $UrlArray['query'];
$menu->data['url'] = $url;
    //$menu->data['idurl'] = litepublisher::$urlmap->add($url, get_class($menu), $id);

    $menus->items[$id] = array(
    'id' => $id,
    'class' => get_class($menu)
    );
    //move props
    foreach (tmenu::$ownerprops as $prop) {
      $menus->items[$id][$prop] = $menu->$prop;
      if (array_key_exists($prop, $menu->data)) unset($menu->data[$prop]);
    }
$menu->id = $id;
    $menu->idurl = litepublisher::$urlmap->add($url, get_class($menu), $id);
$menu->save();
echo "menu $menu->id\n";	
flush();
}

$menus->sort();
$menus->unlock();

litepublisher::$urlmap->unlock();
}

 function AddTag($tags, $id, $parent, $title, $url) {
$id = (int) $id;
$parent = (int) $parent;
if (isset($tags->items[$id])) return;
  $UrlArray = parse_url($url);
  $url = $UrlArray['path'];
  if (!empty($UrlArray['query'])) $url .= '?' . $UrlArray['query'];
       $idurl =         litepublisher::$urlmap->add($url, get_class($tags),  $id);

    if ($tags->dbversion)  {
$a = $tags->db->fetchassoc($tags->db->query("SHOW TABLE STATUS like '$tags->thistable'"));
$autoid = (int) $a['Auto_increment'];
    $tags->db->exec(sprintf('ALTER TABLE %s AUTO_INCREMENT = %d',$tags->thistable,max($id, $autoid)));
//echo "after alter auto id\n";
$tags->db->insert_a(array(
'id' => $id,
      'parent' => $parent,
'idurl' => $idurl,
      'title' => $title,
      'idview' => 1
      ));
    } else {
$tags->autoid = max($tags->autoid, $id);
}
    
    $tags->items[$id] = array(
    'id' => $id,
    'parent' => $parent,
    'idurl' =>         $idurl,
    'url' =>$url,
    'title' => $title,
    'icon' => 0,
    'idview' => 1,
    'itemscount' => 0
    );
}
    
function ExportCategories() {
$categories = tcategories::i();
$categories->lock();
		if ( $cats = get_categories('get=all') ) {
			foreach ( $cats as $cat ) {
AddTag($categories, 
$cat->term_id, 
$cat->parent, 
$cat->name, 
get_category_link($cat->term_id));
}
}
$categories->unlock();
}

function  ExportPosts() {
		global $wpdb, $from;

  $urlmap = turlmap::i();
  $urlmap->lock();

$posts = tposts::i();
$posts->lock();
if (dbversion) {
$r = $wpdb->get_results("SELECT max(ID) as autoid FROM $wpdb->posts ");
$autoid = (int) $r[0]->autoid;
echo "$autoid = auto id posts\n";
    $posts->db->exec(sprintf('ALTER TABLE %s AUTO_INCREMENT = %d',$posts->thistable,$autoid ));
}

$categories = tcategories::i();
$categories->loadall();
$categories->lock();
$tags = ttags::i();
$tags->loadall();
$tags->lock();

if ($from == 0) {
echo "import categories\n";
ExportCategories();
echo "import pages\n";
ExportPages();
}

$cron = tcron::i();
$cron->disableadd = true;
//$list = $wpdb->get_results("SELECT * FROM $wpdb->posts WHERE post_type = 'post'");
$list = $wpdb->get_results("SELECT ID FROM $wpdb->posts 
WHERE post_type = 'post'
and ID > $from
limit 500
");

echo count($list), " = countposts\n";
foreach ($list as $index => $idresult) {
//$itemres= $wpdb->get_results("SELECT * FROM $wpdb->posts WHERE ID = $idresult");
$itemres= $wpdb->get_results("SELECT * FROM $wpdb->posts WHERE ID = $idresult->ID");
$item = &$itemres[0];
  $post = new tpost();
$post->id = (int) $item->ID;
echo $post->id, " = id post\n";
$post->posted =strtotime(mysql2date('Ymd\TH:i:s', $item->post_date));
  $post->title = $item->post_title;
  $post->categories = wp_get_post_categories($item->ID);
$taglist = array();
$wptags = wp_get_post_tags( $item->ID);
foreach ($wptags as 	$wptag) {
AddTag($tags, 
(int) $wptag->term_id, 
0,
$wptag->name, 
get_tag_link($wptag->term_id ));
$taglist[] = (int) $wptag->term_id ;
}

  $post->tags = $taglist;

$UrlArray = parse_url(@get_permalink($item->ID));
$url = $UrlArray['path'];
if (!empty($UrlArray['query'])) $url .= '?' . $UrlArray['query'];
$post->url = $url;
$post->idurl = litepublisher::$urlmap->add($post->url, get_class($post), $post->id);
 $post->content = $item->post_content;
$post->commentsenabled =  'open' == $item->comment_status;
$post->pingenabled = 'open' == $item->ping_status;
$post->password = $item->post_password;
$post->status = $item->post_status == 'publish' ? 'published' : 'draft';
savepost($post);
  $categories->itemsposts->setitems($post->id, $post->categories);
  $tags->itemsposts->setitems($post->id, $post->tags);
ExportComments($post);
$post->free();
}
$cron->unlock();
//$CommentManager->SubscribtionEnabled = true;
//$CommentManager->NotifyModerator = true;
$tags->unlock();
$categories->unlock();

$posts->UpdateArchives();
$posts->addrevision();
$posts->unlock();
  $urlmap->clearcache();
$arch = tarchives::i();
$arch->postschanged();
  $urlmap->unlock();

if (count($list) < 500) return false;
return $item->ID;
}

function savepost($post) {
    if ($post->posted == 0) $post->posted = time();
$post->modified = time();
 
$posts =tposts::i();
if (dbversion) {
    $self = tposttransform::i($post);
    $values = array('id' => $post->id);
    foreach (tposttransform::$props as $name) {
     $values[$name] = $self->__get($name);
    }

    $db = litepublisher::$db;
    $db->table = 'posts';
$db->insert_a($values);
    $post->rawdb->insert_a(array(
    'id' => $post->id,
    'created' => sqldate(),
    'modified' => sqldate(),
    'rawcontent' => $post->data['rawcontent']
    ));

    $db->table = 'pages';
    foreach ($post->data['pages'] as $i => $content) {
      $db->insert_a(array(
'post' => $POST->id,
 'page' => $i,         
'content' => $content
));
    }
} else {
  $posts->autoid = max($posts->autoid, $post->id);
      $dir =litepublisher::$paths->data . 'posts' . DIRECTORY_SEPARATOR  . $post->id;
      if (!is_dir($dir)) mkdir($dir, 0777);
      chmod($dir, 0777);
$post->save();

      $posts->items[$post->id] = array(
      'posted' => $post->posted
      );
      if   ($post->status != 'published') $posts->items[$post->id]['status'] = $post->status;
      if   ($post->author > 1) $posts->items[$post->id]['author'] = $post->author;
}

flush();
 }

function ExportComments(tpost $post) {
  global $wpdb;
$comments = tcomments::i($post->id);
$comments->lock();
$comusers = tcomusers::i($post->id);
$comusers->lock();

  $items = $wpdb->get_results("SELECT  * FROM $wpdb->comments 
  WHERE comment_post_ID   = $post->id");
foreach ($items as $item) {
$date =strtotime(mysql2date('Ymd\TH:i:s', $item->comment_date));
$status = $item->comment_approved ==  '1' ? 'approved' : 'hold';

if ($item->comment_type != '') {
addpingback($post->id,
$item->comment_author, 
$item->comment_author_url,
$item->comment_author_IP,
$date, $status
);
continue;
}

$author = $comusers->add($item->comment_author, 
$item->comment_author_email,
$item->comment_author_url,
$item->comment_author_IP 
);

$cid = $comments->add($author, 
$item->comment_content,
$status,
$item->comment_author_IP);

if (dbversion) {
$comments->db->setvalue($cid, 'posted', sqldate(min(time(), $date)));
} else {
$comments->items[$cid]['posted'] = $date;
}
}

$comusers->unlock();
$comments->unlock();

if (dbversion) {
      $count = $comments->db->getcount("post = $post->id and status = 'approved'");
      $comments->getdb('posts')->setvalue($post->id, 'commentscount', $count);
    $count= $comments->getdb('pingbacks')->getcount("post = $post->id and status = 'approved'");
$comments->getdb('posts')->setvalue($post->id, 'pingbackscount', $count);
}

}

function addpingback($idpost, $title, $url, $ip, $date, $status) {
$pingbacks = tpingbacks::i($idpost);
if (dbversion) {
    $item = array(
    'url' => $url,
    'title' => $title,
    'post' => $idpost,
    'posted' =>sqldate($date),
    'status' => $status,
    'ip' => $ip
    );
    $id =     $pingbacks->db->add($item);
} else {
    $pingbacks->items[++$pingbacks->autoid] = array(
    'url' => $url,
    'title' => $title,
    'post' => $idpost,
    'posted' =>$date,
    'approved' => $status == 'approved',
    'ip' => $ip
    );
    $pingbacks->save();
}
}

function cleartags($tags) {
$tags->lock();
$tags->loadall();
foreach ($tags->items as $id => $item) {
$tags->delete($id);
}
$tags->unlock();
}

function clearposts() {
$posts = tposts::i();
$posts->lock();
if (dbversion) {
$items = $posts->select(litepublisher::$db->prefix . 'posts.id > 0', '');
foreach ($items as $id) {
$posts->delete($id);
}

} else {
foreach ($posts->items as $id => $item) {
$posts->delete($id);
}
}
$posts->unlock();
}

function clearmenu() {
$menus = tmenus::i();
$menus->lock();
foreach ($menus->items as $id => $item) {
$menus->delete($id);
}
$menus->unlock();
}

function clearall() {
clearposts();
cleartags(tcategories::i());
cleartags(ttags::i());
clearmenu();
if (dbversion) {
$do = tdboptimizer::i();
$do->optimize();
}
}

try {

clearall();
echo "started\n";
$from = isset($_REQUEST['from']) ? $_REQUEST['from'] : 0;
if ($from == 0) ExportOptions();
if ($from = ExportPosts()) {
echo "</pre>
<form name='form' action='' type='get'>
<input type=hidden name='from' value='$from' />
 <p><input type='submit' name='Submit' value='Continue'/></p>
</form>
<br>";
} else {
echo "import finished<br>\n";
}
echo "final\n";
} catch (Exception $e) {
echo $e->GetMessage();
echo "\n";
echo $e->getTraceAsString();
//litepublisher::$options->showerror = true;
//  litepublisher::$options->handexception($e);
}
tstorage::savemodified();
echo round(memory_get_usage()/1024/1024, 2), 'MB <br>'; 

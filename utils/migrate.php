<?php
set_time_limit(120);
define('litepublisher_mode', 'xmlrpc');
include('index.php');

function md5bin($a) {
if (strlen($a) < 32) return $a;
$result ='';
for($i=0; $i<32; $i+=2){
$result .= chr(hexdec($a[$i] . $a[$i+1]));
}
return $result;
}

function newmd5($old) {
if (strlen($old) != 32) return $old;
return trim(base64_encode(md5bin($old)), '=');
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
$posts = tposts::instance();
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
$menus = tmenus::instance();
$menus->lock();
foreach ($menus->items as $id => $item) {
$menus->delete($id);
}
$menus->unlock();
}

class tmigratedata extends tdata {
public static $dir;

public function loadfile($name) {
$this->data = array();
$filename = self::$dir . $name . '.php';
    if (file_exists($filename)) {
      return $this->loadfromstring(tfilestorage::loadfile($filename));
    }
}

}//class

function migrateposts() {
global $data, $man;
$data->loadfile('posts' . DIRECTORY_SEPARATOR . 'index');
$posts = tposts::instance();
$posts->lock();
if (dbversion) {
$man = tdbmanager::instance();
$man->setautoincrement('posts', $data->lastid);
} else {
$posts->autoid = $data->lastid;
}

$tags = ttags::instance();
$tags->lock();
$tags->itemsposts->lock();
$cats = tcategories::instance();
$cats->lock();
$cats->itemsposts->lock();
$items = $data->data['items'];
foreach ($items as $id => $item) {
$post = migratepost($id);
savepost($post);
  $cats->itemsposts->setitems($post->id, $post->categories);
  $tags->itemsposts->setitems($post->id, $post->tags);
migratecomments($id);
if (!dbversion) {
      $posts->items[$post->id] = array(
      'posted' => $post->posted
      );
      if   ($post->status != 'published') $posts->items[$post->id]['status'] = $post->status;
      if   ($post->author > 1) $posts->items[$post->id]['author'] = $post->author;
}
}
$posts->UpdateArchives();
$posts->addrevision();
$posts->unlock();
$tags->itemsposts->unlock();
$tags->unlock();
$cats->itemsposts->unlock();
$cats->unlock();

$arch = tarchives::instance();
$arch->postschanged();

//update trust values
if (dbversion) {
      $db = litepublisher::$db;
$trusts = $db->res2assoc($db->query("SELECT author as 'author', count(author) as 'count' FROM  $db->comments 
where status = 'approved' GROUP BY  author"));

$db->table = 'comusers';
foreach ($trusts as $r) {
        $db->setvalue($r['author'], 'trust', $r['count']);
}
unset($trust);
}
}

function migratepost($id) {
global $data;
$data->loadfile('posts' . DIRECTORY_SEPARATOR  . $id . DIRECTORY_SEPARATOR . 'index');
$post = tpost::instance();
foreach ($data->data as $name =>  $value) {
if (isset($post->data[$name])) $post->data[$name] = $value;
}

    $post->posted = $data->date;
$post->idurl = litepublisher::$urlmap->add($post->url, get_class($post), (int) $post->id);

return $post;
}

function savepost(tpost $post) {
    if ($post->posted == 0) $post->posted = time();
$post->modified = time();
 
$posts =tposts::instance();
if (dbversion) {
//tfiler::log($post->id);
    $self = tposttransform::instance($post);
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

echo "$post->id\n";	
flush();
 }

function migratecomments($idpost) {
global $data, $users;
if (!$data->loadfile('posts' . DIRECTORY_SEPARATOR  . $idpost . DIRECTORY_SEPARATOR . 'comments')) return;
if (!isset($data->data['items'])) {
var_dump($idpost, $data->data);
exit();
}
if (!isset($users)) {
$users = new tmigratedata();
$users->loadfile('commentusers');
}

$comments = tcomments::instance($idpost);
$comments->lock();
$comusers = tcomusers::instance($idpost);
$comusers->lock();
foreach ($data->data['items'] as $id => $item) {
$user = $users->data['items'][$item['uid']];
if ($item['type'] == '') {
$author = $comusers->add($user['name'], $user['email'], $user['url'], '');
$cid = $comments->add($author, $item['rawcontent'], $item['status'], '');
if (dbversion) {
$comments->db->setvalue($cid, 'posted', sqldate(min(time(), $item['date'])));
$comusers->db->setvalue($author, 'cookie', newmd5($user['cookie']));
} else {
$comments->items[$cid]['posted'] = $item['date'];
$comusers->items[$author]['cookie'] = newmd5($user['cookie']);
}
} else {
addpingback($idpost, $item, $user);
}
}
$comusers->unlock();
$comments->unlock();

if (dbversion) {      
      $count = $comments->db->getcount("post = $idpost and status = 'approved'");
      $comments->getdb('posts')->setvalue($idpost, 'commentscount', $count);
    $count= $comments->getdb('pingbacks')->getcount("post = $idpost and status = 'approved'");
$comments->getdb('posts')->setvalue($idpost, 'pingbackscount', $count);
}

}

function addpingback($idpost, $ping, $user) {
$pingbacks = tpingbacks::instance($idpost);
if (dbversion) {
    $item = array(
    'url' => $user['url'],
    'title' => $user['name'],
    'post' => $idpost,
    'posted' =>sqldate($ping['date']),
    'status' => $ping['status'],
    'ip' => ''
    );
    $id =     $pingbacks->db->add($item);
} else {
    $pingbacks->items[++$pingbacks->autoid] = array(
    'url' => $user['url'],
    'title' => $user['name'],
    'posted' => $ping['date'],
    'ip' => '',
    'approved' => $ping['status'] == 'approved'
    );
    $pingbacks->save();
}
}

function migratetags(tcommontags $tags) {
global $data, $man;
$data->loadfile($tags->basename);
if (dbversion) {
$man->setautoincrement($tags->table, $data->lastid);
} else {
$tags->autoid = $data->lastid;
}
//var_dump($data->data['items']);
foreach ($data->data['items'] as $id => $item) {
if ($item['count'] == 0) continue;
       $idurl =         litepublisher::$urlmap->add($item['url'], get_class($tags),  $id);
if (dbversion) {
$tags->db->insert_a(array(
'id' => $id,
'idurl' => $idurl,
      'parent' => 0,
'title' => $item['name'],
      'idview' => 1,
'icon' => 0,
'itemscount' => count($item['items'])
));
} else {
$tags->items[$id]  = array(
    'id' => $id,
    'parent' => 0,
    'idurl' =>         $idurl,
    'url' =>$item['url'],
    'title' => $item['name'],
    'icon' => 0,
    'idview' => 1,
    'itemscount' => 0
    );
}
}
if (!dbversion) $tags->save();
}

function migratemenus() {
$data = new tmigratedata();
$data->loadfile('menus' . DIRECTORY_SEPARATOR . 'index');
$menus = tmenus::instance();
$menus->lock();
$menus->autoid = $data->lastid;

foreach ($data->data['items'] as $id => $item) {
$menu = migratemenu($id, $item['class']);
if ($id == 1) $id = ++$menus->autoid;
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
$menu->idurl = litepublisher::$urlmap->add($menu->url, get_class($menu), (int) $id);
$menu->save();
}
$menus->sort();
$menus->unlock();
}

function migratemenu($id, $class) {
global $data;
$data->loadfile('menus' . DIRECTORY_SEPARATOR . $id . DIRECTORY_SEPARATOR  . 'index');
$classes = array(
'TMenuItem' => 'tmenu',
'TContactForm' => 'tcontactform'
);
$class = $classes[$class];
$menu = new $class();
foreach ($data->data as $name => $value) {
if (isset($menu->data[$name])) $menu->data[$name] = $value;
}
$menu->data['id'] = 0;
if ($class =='tcontactform') {
$menu->data['content'] = str_replace(
array('FormValue', '_Value'),
array('contactvalue', '_contactform'),
$menu->data['content']);
}
return $menu;
}  

function addurl($url, $obj, $id) {
return litepublisher::$urlmap->add($url, get_class($obj), $id, 'normal');
}

function migrateoptions() {
global $data;
$data->loadfile('options');
$options = litepublisher::$options;
$site = litepublisher::$site;
$site->name = $data->name;
$site->description = $data->description;
$site->keywords = $data->keywords;
$options->email = $data->email;
    $options->timezone = $data->timezone;
$options->cache = $data->CacheEnabled;
$options->expiredcache = $data->CacheExpired;
$options->perpage = $data->postsperpage;
  $options->DefaultCommentStatus = $data->DefaultCommentStatus;
  $options->commentsdisabled = $data->commentsdisabled;
  $options->commentsenabled = $data->commentsenabled;
  $options->pingenabled = $data->pingenabled;
  $options->commentpages = $data->commentpages;
  $options->commentsperpage = $data->commentsperpage;
  $options->echoexception = $data->echoexception;
$options->savemodified();
    }

function migratewidgets() {
global $data;
//links
if ($data->loadfile('linkswidget')) {
$widget = tlinkswidget::instance();
$widget->redir = $data->redir;
$widget->items = array();
foreach ($data->data['items'] as $id => $item) {
    $widget->items[++$widget->autoid] = array(
    'url' => $item['url'],
    'title' => $item['title'],
    'anchor' => $item['text']
    );
}
$widget->save();
}

if ($data->loadfile('customwidget')) {
    $widgets = twidgets::instance();
    $widgets->lock();
$widget = tcustomwidget::instance();
$widget->lock();
foreach ($widget->items as $id => $item) {
$widget->delete($id);
}

foreach ($data->data['items'] as $id => $item) {
$widget->add($item['title'], $item['content'], 'widget');
}
$widget->unlock();
$widgets->unlock();
}
}


echo "<pre>\n";

tmigratedata::$dir = dirname(__file__) . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . litepublisher::$domain . DIRECTORY_SEPARATOR;
$data = new tmigratedata();

$linkgen = tlinkgenerator::instance();
$linkgen->archive = '/[year]/[month]/';
$linkgen->save();

clearposts();
cleartags(tcategories::instance());
cleartags(ttags::instance());
clearmenu();

if (dbversion && (litepublisher::$options->version == 4.65)) {
$man = tdbmanager::instance();
$man->alter('comusers', "modify `name` text NOT NULL");
$man->alter('pingbacks', "modify `title` text NOT NULL");
litepublisher::$options->version = 4.66;
}
$do = tdboptimizer::instance();
$do->optimize();
migrateoptions();
migrateposts();
migratetags(tcategories::instance());
migratetags(ttags::instance());
migratemenus();
//migratewidgets();
litepublisher::$urlmap->unlock();
litepublisher::$options->savemodified();
litepublisher::$urlmap->clearcache();
//echo  $man->performance();
echo "\nmigrated\n";
?>
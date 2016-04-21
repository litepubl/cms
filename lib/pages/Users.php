<?php
/**
* Lite Publisher CMS
* @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
* @link https://github.com/litepubl\cms
* @version 6.15
**/

namespace litepubl\pages;
use litepubl\core\Users as CoreUsers;
use litepubl\view\Filter;
use litepubl\core\Str;
use litepubl\view\Theme;

class Users extends \litepubl\core\Items implements \litepubl\view\ViewInterface
{
    public static $userprops = array(
        'email',
        'name',
        'website'
    );

    public static $pageprops = array(
        'url',
        'content',
        'rawcontent'
    );
    public $id;
    protected $useritem;

    protected function create() {
        $this->dbversion = dbversion;
        parent::create();
        $this->basename = 'userpage';
        $this->table = 'userpage';
        $this->data['createpage'] = true;
    }

    public function __get($name) {
        if (in_array($name, static ::$userprops)) {
            return CoreUsers::i()->getvalue($this->id, $name);
        }

        if (in_array($name, static ::$pageprops)) {
            return $this->getvalue($this->id, $name);
        }

        return parent::__get($name);
    }

    public function getMd5email() {
        if ($email = CoreUsers::i()->getvalue($this->id, 'email')) {
            return md5($email);
        } else {
            return '';
        }
    }

    public function getGravatar() {
        if ($md5 = $this->md5email) {
            return sprintf('<img class="avatar photo" src="http://www.gravatar.com/avatar/%s?s=120&amp;r=g&amp;d=wavatar" title="%2$s" alt="%2$s"/>', $md5, $this->name);
        } else {
            return '';
        }
    }

    public function getWebsitelink() {
        if ($website = $this->website) {
            return sprintf('<a href="%1$s">%1$s</a>', $website);
        }
        return '';
    }

    public function select($where, $limit) {
        if (!$this->dbversion) $this->error('Select method must be called ffrom database version');
        if ($where) $where.= ' and ';
        $db =  $this->getApp()->db;
        $table = $this->thistable;
        $res = $db->query("select $table.*, $db->urlmap.url as url from $table, $db->urlmap
    where $where $db->urlmap.id  = $table.idurl $limit");
        return $this->res2items($res);
    }

    public function getItem($id) {
        $item = parent::getitem($id);
        if (!isset($item['url'])) {
            $item['url'] = $item['idurl'] == 0 ? '' :  $this->getApp()->router->getidurl($item['idurl']);
            $this->items[$id]['url'] = $item['url'];
        }
        return $item;
    }

    public function request($id) {
        if ($id == 'url') {
            $id = isset($_GET['id']) ? (int)$_GET['id'] : 1;
            $users = CoreUsers::i();
            if (!$users->itemexists($id)) {
 return 404;
}


            $item = $users->getitem($id);
            $website = $item['website'];
            if (!strpos($website, '.')) $website =  $this->getApp()->site->url .  $this->getApp()->site->home;
            if (!Str::begin($website, 'http://')) $website = 'http://' . $website;
            return "<?php litepubl::\$router->redir('$website');";
        }

        $this->id = (int)$id;
        if (!$this->itemexists($id)) {
 return 404;
}


        $item = $this->getitem($id);

        $schema = Schema::getview($this);
        $perpage = $schema->perpage ? $schema->perpage :  $this->getApp()->options->perpage;
        $pages = (int)ceil( $this->getApp()->classes->posts->archivescount / $perpage);
        if (( $this->getApp()->router->page > 1) && ( $this->getApp()->router->page > $pages)) {
            $url =  $this->getApp()->router->getvalue($item['idurl'], 'url');
            return "<?php litepubl::\$router->redir('$url'); ?>";
        }

    }

    public function getTitle() {
        return $this->name;
    }

    public function getKeywords() {
        return $this->getvalue($this->id, 'keywords');
    }

    public function getDescription() {
        return $this->getvalue($this->id, 'description');
    }

    public function getIdSchema() {
        return $this->getvalue($this->id, 'idschema');
    }

    public function setIdSchema($id) {
        $this->setvalue($this->id, 'idveiw');
    }

    public function getHead() {
        return $this->getvalue($this->id, 'head');
    }

    public function getCont() {
        $item = $this->getitem($this->id);
        Theme::$vars['author'] = $this;

        $schema = Schema::getview($this);
        $theme = $schema->theme;
        $result = $theme->parse($theme->templates['content.author']);

        $perpage = $schema->perpage ? $schema->perpage :  $this->getApp()->options->perpage;
        $posts =  $this->getApp()->classes->posts;
        $from = ( $this->getApp()->router->page - 1) * $perpage;

        $poststable = $posts->thistable;
        $count = $posts->db->getcount("$poststable.status = 'published' and $poststable.author = $this->id");
        $order = $schema->invertorder ? 'asc' : 'desc';
        $items = $posts->select("$poststable.status = 'published' and $poststable.author = $this->id", "order by $poststable.posted $order limit $from, $perpage");

        $result.= $theme->getposts($items, $schema->postanounce);
        $result.= $theme->getpages($item['url'],  $this->getApp()->router->page, ceil($count / $perpage));
        return $result;
    }

    public function addpage($id) {
        $item = $this->getitem($id);
        if ($item['idurl'] > 0) {
 return $item['idurl'];
}


        $item = $this->addurl($item);
        $this->items = $item;
        unset($item['url']);
        $item['id'] = $id;
        $this->db->updateassoc($item);
    }

    private function addurl(array $item) {
        if ($item['id'] == 1) {
 return $item;
}


        $item['url'] = '';
        $linkitem = CoreUsers::i()->getitem($item['id']) + $item;
        $linkgen = tlinkgenerator::i();
        $item['url'] = $linkgen->addurl(new tarray2prop($linkitem) , 'user');
        $item['idurl'] =  $this->getApp()->router->add($item['url'], get_class($this) , $item['id']);
        return $item;
    }

    public function add($id) {
        $item = array(
            'id' => $id,
            'idurl' => 0,
            'idschema' => 1,
            'registered' => Str::sqlDate() ,
            'ip' => '',
            'avatar' => 0,
            'content' => '',
            'rawcontent' => '',
            'keywords' => '',
            'description' => '',
            'head' => ''
        );

        if ($this->createpage) {
            $users = CoreUsers::i();
            if ('approved' == $users->getvalue($id, 'status')) $item = $this->addurl($item);
        }
        $this->items[$id] = $item;
        unset($item['url']);
        $this->db->insert($item);
    }

    public function delete($id) {
        if ($id <= 1) {
 return false;
}


        if (!$this->itemexists($id)) {
 return false;
}


        $idurl = $this->getvalue($id, 'idurl');
        if ($idurl > 0)  $this->getApp()->router->deleteitem($idurl);
        return parent::delete($id);
    }

    public function edit($id, array $values) {
        if (!$this->itemexists($id)) {
 return false;
}


        $item = $this->getitem($id);
        $url = isset($values['url']) ? $values['url'] : '';
        unset($values['url'], $values['idurl'], $values['id']);
        foreach ($item as $k => $v) {
            if (isset($values[$k])) $item[$k] = $values[$k];
        }
        $item['id'] = $id;
        $item['content'] = Filter::i()->filter($item['rawcontent']);
        if ($url && ($url != $item['url'])) {
            if ($item['idurl'] == 0) {
                $item['idurl'] =  $this->getApp()->router->add($url, get_class($this) , $id);
            } else {
                 $this->getApp()->router->addredir($item['url'], $url);
                 $this->getApp()->router->setidurl($item['idurl'], $url);
            }
            $item['url'] = $url;
        }

        $this->items[$id] = $item;
        unset($item['url']);
        $this->db->updateassoc($item);
    }

} //class
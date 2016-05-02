<?php

namespace litepubl\tag;
use litepubl\core\Context;
use litepubl\view\Theme;
use litepubl\view\Args;
use litepubl\view\Schemes;
use litepubl\view\Schema;
use litepubl\view\Vars;
use litepubl\view\Lang;
use litepubl\post\Announce;
use litepubl\core\Str;

class View extends \litepubl\core\Events implements \litepubl\view\ViewInterface
{
    public $id;
private $tags;
    private $cachedIdPosts;
private $context;

    protected function create() {
        parent::create();
        $this->addEvents('onbeforecontent', 'oncontent');
        $this->cachedIdPosts = array();
}

public function setTags(Common $tags)
{
$this->tags = $tags;
}

    public function getSorted(array $tml, $parent, $sortname, $count, $showcount) {
        $sorted = $this->tags->getSorted($parent, $sortname, $count);
        if (!count($sorted)) {
 return '';
}

        $result = '';
        $theme = Theme::i();
$tags = $this->tags;
        $args = new Args();
        $args->rel = $tags->PermalinkIndex;
        $args->parent = $parent;

        foreach ($sorted as $id) {
            $item = $tags->getItem($id);
            $args->add($item);
            $args->icon = '';
            $args->subcount = $showcount ? $theme->parsearg($tml['subcount'], $args) : '';
            $args->subitems = $tml['subitems'] ? $this->getSorted($tml, $id, $sortname, $count, $showcount) : '';

            $result.= $theme->parsearg($tml['item'], $args);
        }

        if (!$parent) {
 return $result;
}

        $args->parent = $parent;
        $args->item = $result;
        return $theme->parsearg($tml['subitems'], $args);
    }

public function getValue($name)
{
return $this->tags->getValue($this->id, $name);
}

public function getPostPropName()
{
return $this->tags->postpropname;
}

    public function request(Context $context) {
        if ($this->id = (int) $context->itemRoute['arg']) {
            try {
                $item = $this->tags->getItem($this->id);
            }
            catch(\Exception $e) {
$context->response->status = 404;
                return;
            }

            $schema = Schema::getSchema($this);
            $perpage = $schema->perpage ? $schema->perpage :  $this->getApp()->options->perpage;
            $pages = (int)ceil($item['itemscount'] / $perpage);
            if (( $context->request->page > 1) && ( $context->request->page > $pages)) {
$context->response->redir($item['url']);
return;
            }
        }

$this->context = $context;
    }

    public function getTitle() {
        if ($this->id) {
            return $this->getValue('title');
        }

        return Lang::i()->categories;
    }

    public function getHead() {
        if ($this->id) {
            $result = $this->tags->contents->getValue($this->id, 'head');

            $theme = Schema::getSchema($this)->theme;
            $result.= $theme->templates['head.tags'];

            $list = $this->getIdPosts($this->id);
$announce = new Announce($theme);
            $result.= $announce->getAnHead($list);

            return $theme->parse($result);
        }
    }

    public function getKeywords() {
        if ($this->id) {
            $result = $this->tags->contents->getValue($this->id, 'keywords');
            if (!$result) {
$result = $this->title;
}

            return $result;
        }
    }

    public function getDescription() {
        if ($this->id) {
            $result = $this->tags->contents->getvalue($this->id, 'description');
            if (!$result) {
$result = $this->title;
}

            return $result;
        }
    }

    public function getIdschema() {
        if ($this->id) {
            return $this->getValue('idschema');
        }

        return 1;
    }

    public function setIdSchema($id) {
        if ($id != $this->idschema) {
            $this->tags->setValue($this->id, 'idschema', $id);
        }
    }

    public function getIdPerm() {
        if ($this->id) {
            $item = $this->tags->getItem($this->id);
            return isset($item['idperm']) ? (int)$item['idperm'] : 0;
        }

        return 0;
    }

    public function getIndex_tml() {
        $theme = Theme::i();
        if (!empty($theme->templates['index.tag'])) {
 return $theme->templates['index.tag'];
}

        return false;
    }

    public function getContent() {
        if ($s = $this->tags->contents->getcontent($this->id)) {
            $pages = explode('<!--nextpage-->', $s);
            $page =  $this->context->request->page - 1;
            if (isset($pages[$page])) {
 return $pages[$page];
}
        }

        return '';
    }

    public function getCont() {
$result = new Str('');
        $this->onbeforecontent($result);

        if (!$this->id) {
            $result->value .= $this->getcont_all();
        } else {
            $schema = Schema::getSchema($this);
$theme = $schema->theme;

            if ($this->getContent()) {
$vars = new Vars();
$vars->menu = $this;
                $result->value .= $theme->parse($theme->templates['content.menu']);
            }

            $list = $this->getIdPosts($this->id);
            $item = $this->tags->getItem($this->id);
            $announce = new Announce($theme);
            $result->value .= $announce->getPostsNavi($list, $item['url'], $item['itemscount'], $schema->postanounce, $schema->perpage);
        }

        $this->oncontent($result);
        return $result->value;
    }

    public function getCont_all() {
        return sprintf('<ul>%s</ul>', $this->getSorted(array(
            'item' => '<li><a href="$link" title="$title">$icon$title</a>$subcount</li>',
            'subcount' => '<strong>($itemscount)</strong>',
            'subitems' => '<ul>$item</ul>'
        ) , 0, 'count', 0, 0, false));
    }

    public function get_sorted_posts($id, $count, $invert) {
        $ti = $this->itemsposts->thistable;
        $posts = $this->tags->factory->posts;
        $p = $posts->thistable;
        $order = $invert ? 'asc' : 'desc';
        $result = $this->db->res2id($this->db->query("select $p.id as id, $ti.post as post from $p, $ti
    where    $ti.item = $id and $p.id = $ti.post and $p.status = 'published'
    order by $p.posted $order limit 0, $count"));

        $posts->loadItems($result);
        return $result;
    }

    public function getIdPosts($id) {
        if (isset($this->cachedIdPosts[$id])) {
            return $this->cachedIdPosts[$id];
        }

        $schema = Schema::i($this->tags->getValue($id, 'idschema'));
        $perpage = $schema->perpage ? $schema->perpage :  $this->getApp()->options->perpage;
        $from = ( $this->context->request->page - 1) * $perpage;

$result = $this->tags->getIdPosts($id, $from, $perpage, $schema->invertorder);
        $this->cachedIdPosts[$id] = $result;
return $result;
}

}
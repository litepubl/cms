<?php

namespace litepubl\tag;

class View extends \litepubl\core\Events implements \litepubl\view\ViewInterface
{
    public $id;
    private $_idposts;
    protected function create() {
        parent::create();
        $this->_idposts = array();
}

    public function getSortedcontent(array $tml, $parent, $sortname, $count, $showcount) {
        $sorted = $this->getsorted($parent, $sortname, $count);
        if (count($sorted) == 0) {
 return '';
}


        $result = '';
        $theme = Theme::i();
        $args = new Args();
        $args->rel = $this->PermalinkIndex;
        $args->parent = $parent;
        foreach ($sorted as $id) {
            $item = $this->getitem($id);
            $args->add($item);
            $args->icon = '';
            $args->subcount = $showcount ? $theme->parsearg($tml['subcount'], $args) : '';
            $args->subitems = $tml['subitems'] ? $this->getsortedcontent($tml, $id, $sortname, $count, $showcount) : '';
            $result.= $theme->parsearg($tml['item'], $args);
        }
        if ($parent == 0) {
 return $result;
}


        $args->parent = $parent;
        $args->item = $result;
        return $theme->parsearg($tml['subitems'], $args);
    }

    public function request(Context $context) {
        if ($this->id = $context->id) {
            try {
                $item = $this->getitem((int)$id);
            }
            catch(\Exception $e) {
$context->response->status = 404;
                return;
            }

            $schema = Schema::getview($this);
            $perpage = $schema->perpage ? $schema->perpage :  $this->getApp()->options->perpage;
            $pages = (int)ceil($item['itemscount'] / $perpage);
            if (( $context->request->page > 1) && ( $context->request->page > $pages)) {
$context->response->redir($item['url']);
return;
            }
        }
    }

    public function getName($id) {
        $item = $this->getitem($id);
        return $item['title'];
    }

    public function getTitle() {
        if ($this->id) {
            return $this->getvalue($this->id, 'title');
        }

        return Lang::i()->categories;
    }

    public function getHead() {
        if ($this->id) {
            $result = $this->contents->getvalue($this->id, 'head');
            $theme = Schema::getview($this)->theme;
            $result.= $theme->templates['head.tags'];

            $list = $this->getidposts($this->id);
            $result.= $this->factory->posts->getanhead($list);

            return $theme->parse($result);
        }
    }

    public function getKeywords() {
        if ($this->id) {
            $result = $this->contents->getvalue($this->id, 'keywords');
            if ($result == '') $result = $this->title;
            return $result;
        }
    }

    public function getDescription() {
        if ($this->id) {
            $result = $this->contents->getvalue($this->id, 'description');
            if ($result == '') $result = $this->title;
            return $result;
        }
    }

    public function getIdschema() {
        if ($this->id) {
            return $this->getvalue($this->id, 'idschema');
        }

        return 1;
    }

    public function setIdschema($id) {
        if ($id != $this->idschema) {
            $this->setvalue($this->id, 'idschema', $id);
        }
    }

    public function getIdperm() {
        if ($this->id) {
            $item = $this->getitem($this->id);
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
        if ($s = $this->contents->getcontent($this->id)) {
            $pages = explode('<!--nextpage-->', $s);
            $page =  $this->getApp()->router->page - 1;
            if (isset($pages[$page])) {
 return $pages[$page];
}


        }

        return '';
    }

    public function getCont() {
        $result = '';
        $this->callevent('onbeforecontent', array(&$result
        ));

        if (!$this->id) {
            $result.= $this->getcont_all();
        } else {
            $schema = Schema::getview($this);

            if ($this->getcontent()) {
                Theme::$vars['menu'] = $this;
                $result.= $schema->theme->parse($schema->theme->templates['content.menu']);
            }

            $list = $this->getidposts($this->id);
            $item = $this->getitem($this->id);
            $result.= $schema->theme->getpostsnavi($list, $item['url'], $item['itemscount'], $schema->postanounce, $schema->perpage);
        }

        $this->callevent('oncontent', array(&$result
        ));
        return $result;
    }

    public function getCont_all() {
        return sprintf('<ul>%s</ul>', $this->getsortedcontent(array(
            'item' => '<li><a href="$link" title="$title">$icon$title</a>$subcount</li>',
            'subcount' => '<strong>($itemscount)</strong>',
            'subitems' => '<ul>$item</ul>'
        ) , 0, 'count', 0, 0, false));
    }

    public function get_sorted_posts($id, $count, $invert) {
        $ti = $this->itemsposts->thistable;
        $posts = $this->factory->posts;
        $p = $posts->thistable;
        $order = $invert ? 'asc' : 'desc';
        $result = $this->db->res2id($this->db->query("select $p.id as id, $ti.post as post from $p, $ti
    where    $ti.item = $id and $p.id = $ti.post and $p.status = 'published'
    order by $p.posted $order limit 0, $count"));

        $posts->loaditems($result);
        return $result;
    }

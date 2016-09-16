<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.07
  */

namespace litepubl\tag;

use litepubl\core\Context;
use litepubl\post\Announce;
use litepubl\view\Args;
use litepubl\view\Lang;
use litepubl\view\Schema;
use litepubl\view\Theme;
use litepubl\view\Vars;

/**
 * View of categories and tags
 *
 * @property-write callable $onContent
 * @property-write callable $onBeforeContent
 * @method         array onContent(array $params)
 * @method         array onBeforeContent(array $params) triggered when item has been deleted
 */

class View extends \litepubl\core\Events implements \litepubl\view\ViewInterface
{
    public $id;
    private $tags;
    private $cachedIdPosts;
    private $page;

    protected function create()
    {
        parent::create();
        $this->addEvents('onbeforecontent', 'oncontent');
        $this->cachedIdPosts = [];
        $this->page = 0;
    }

    public function setTags(Common $tags)
    {
        $this->tags = $tags;
    }

    public function getSorted(array $tml, $parent, $sortname, $count, $showcount)
    {
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
            $args->subcount = $showcount ? $theme->parseArg($tml['subcount'], $args) : '';
            $args->subitems = $tml['subitems'] ? $this->getSorted($tml, $id, $sortname, $count, $showcount) : '';

            $result.= $theme->parseArg($tml['item'], $args);
        }

        if (!$parent) {
            return $result;
        }

        $args->parent = $parent;
        $args->item = $result;
        return $theme->parseArg($tml['subitems'], $args);
    }

    public function getValue($name)
    {
        return $this->tags->getValue($this->id, $name);
    }

    public function getPostPropName()
    {
        return $this->tags->postpropname;
    }

    public function request(Context $context)
    {
        if ($this->id = (int)$context->itemRoute['arg']) {
            try {
                $item = $this->tags->getItem($this->id);
            } catch (\Exception $e) {
                $context->response->status = 404;
                return;
            }

            $schema = Schema::getSchema($this);
            $perpage = $schema->perpage ? $schema->perpage : $this->getApp()->options->perpage;
            $pages = (int)ceil($item['itemscount'] / $perpage);
            if (($context->request->page > 1) && ($context->request->page > $pages)) {
                $context->response->redir($item['url']);
                return;
            }
        }

            $this->page = $context->request->page - 1;
    }

    public function getTitle(): string
    {
        if ($this->id) {
            return $this->getValue('title');
        }

        return Lang::i()->categories;
    }

    public function getHead(): string
    {
        if ($this->id) {
            $result = $this->tags->contents->getValue($this->id, 'head');

            $theme = Schema::getSchema($this)->theme;
            $result.= $theme->templates['head.tags'];

            $list = $this->getIdPosts($this->id);
            $announce = Announce::i($theme);
            $result.= $announce->getHead($list);

            return $theme->parse($result);
        }

        return '';
    }

    public function getKeywords(): string
    {
        if ($this->id) {
            $result = $this->tags->contents->getValue($this->id, 'keywords');
            if (!$result) {
                $result = $this->title;
            }

            return $result;
        }

        return '';
    }

    public function getDescription(): string
    {
        if ($this->id) {
            $result = $this->tags->contents->getvalue($this->id, 'description');
            if (!$result) {
                $result = $this->title;
            }

            return $result;
        }

        return '';
    }

    public function getIdschema(): int
    {
        if ($this->id) {
            return $this->getValue('idschema');
        }

        return 1;
    }

    public function setIdSchema(int $id)
    {
        if ($id != $this->idschema) {
            $this->tags->setValue($this->id, 'idschema', $id);
        }
    }

    public function getIdPerm(): int
    {
        if ($this->id) {
            $item = $this->tags->getItem($this->id);
            return isset($item['idperm']) ? (int)$item['idperm'] : 0;
        }

        return 0;
    }

    public function getIndex_tml()
    {
        $theme = Theme::i();
        if (!empty($theme->templates['index.tag'])) {
            return $theme->templates['index.tag'];
        }

        return false;
    }

    public function getContent()
    {
        if ($s = $this->tags->contents->getcontent($this->id)) {
            $pages = explode('<!--nextpage-->', $s);
            if (isset($pages[$this->page])) {
                return $pages[$this->page];
            }
        }

        return '';
    }

    public function getCont(): string
    {
        $result = $this->onbeforecontent(['content' => '']);

        if (!$this->id) {
            $result['content'] .= $this->getcont_all();
        } else {
            $schema = Schema::getSchema($this);
            $theme = $schema->theme;

            if ($this->getContent()) {
                $vars = new Vars();
                $vars->menu = $this;
                $result['content'] .= $theme->parse($theme->templates['content.menu']);
            }

            $list = $this->getIdPosts($this->id);
            $item = $this->tags->getItem($this->id);
            $announce = Announce::i();
            $result['content'] .= $announce->getNavi($list, $schema, $item['url'], $item['itemscount']);
        }

        $result = $this->oncontent($result);
        return $result['content'];
    }

    public function getCont_all()
    {
        return sprintf(
            '<ul>%s</ul>', $this->getSorted(
                [
                'item' => '<li><a href="$link" title="$title">$title</a>$subcount</li>',
                'subcount' => '<strong>($itemscount)</strong>',
                'subitems' => '<ul>$item</ul>'
                ], 0, 'count', 0, 0, false
            )
        );
    }

    public function getIdPosts($id)
    {
        if (isset($this->cachedIdPosts[$id])) {
            return $this->cachedIdPosts[$id];
        }

        $schema = Schema::i($this->tags->getValue($id, 'idschema'));
        $perpage = $schema->perpage ? $schema->perpage : $this->getApp()->options->perpage;
        $from = $this->page * $perpage;

        $result = $this->tags->getIdPosts($id, $from, $perpage, $schema->invertorder);
        $this->cachedIdPosts[$id] = $result;
        return $result;
    }
}

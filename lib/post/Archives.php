<?php
/**
 * Lite Publisher CMS
 * @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link https://github.com/litepubl\cms
 * @version 7.00
 *
 */


namespace litepubl\post;

use litepubl\core\Context;
use litepubl\utils\LinkGenerator;
use litepubl\view\Lang;
use litepubl\view\Schema;

class Archives extends \litepubl\core\Items implements \litepubl\view\ViewInterface
{
    use \litepubl\view\ViewTrait;

    public $date;
    private $_idposts;

    protected function create()
    {
        parent::create();
        $this->basename = 'archives';
        $this->table = 'posts';
    }

    public function getHeadLinks()
    {
        $result = '';
        foreach ($this->items as $date => $item) {
            $result.= "<link rel=\"archives\" title=\"{$item['title']}\" href=\" $this->getApp()->site->url{$item['url']}\" />\n";
        }

        return $this->schema->theme->parse($result);
    }

    public function postschanged()
    {
        $posts = Posts::i();
        $this->lock();
        $this->items = array();
        //sort archive by months
        $linkgen = LinkGenerator::i();
        $db = $this->db;
        $res = $db->query(
            "SELECT YEAR(posted) AS 'year', MONTH(posted) AS 'month', count(id) as 'count' FROM  $db->posts
    where status = 'published' GROUP BY YEAR(posted), MONTH(posted) ORDER BY posted DESC "
        );

        while ($r = $db->fetchassoc($res)) {
            $this->date = mktime(0, 0, 0, $r['month'], 1, $r['year']);
            $this->items[$this->date] = array(
                'idurl' => 0,
                'url' => $linkgen->Createlink($this, 'archive', false) ,
                'title' => Lang::date($this->date, 'F Y') ,
                'year' => $r['year'],
                'month' => $r['month'],
                'count' => $r['count']
            );
        }

        $this->CreatePageLinks();
        $this->unlock();
    }

    public function CreatePageLinks()
    {
        $this->lock();
        //Compare links
        $old = $this->getApp()->router->getUrlsOfClass(get_class($this));
        foreach ($this->items as $date => $item) {
            $j = array_search($item['url'], $old);
            if (is_int($j)) {
                array_splice($old, $j, 1);
            } else {
                $this->items[$date]['idurl'] = $this->getApp()->router->Add($item['url'], get_class($this), $date);
            }
        }
        foreach ($old as $url) {
            $this->getApp()->router->delete($url);
        }

        $this->unlock();
    }

    //ITemplate
    public function request(Context $context)
    {
        $date = $context->id;
        if (!isset($this->items[$date])) {
            $context->response->status = 404;
            return;
        }

        $this->date = $date;
        $item = $this->items[$date];
        $schema = Schema::getSchema($this);
        $perpage = $schema->perpage ? $schema->perpage : $this->getApp()->options->perpage;
        $pages = (int)ceil($item['count'] / $perpage);
        if (($context->request->page > 1) && ($context->request->page > $pages)) {
            $context->response->redir($item['url']);
            return;
        }
    }

    public function getHead(): string
    {
        $announce = new Announce($this->schema->theme);
        $result = $announce->getAnHead($this->getIdPosts());
        return $result;
    }

    public function getTitle(): string
    {
        return $this->items[$this->date]['title'];
    }

    public function getCont(): string
    {
        $items = $this->getidposts();
        if (count($items) == 0) {
            return '';
        }

        $schema = Schema::getSchema($this);
        $perpage = $schema->perpage ? $schema->perpage : $this->getApp()->options->perpage;
        $list = array_slice($items, ($this->getApp()->context->request->page - 1) * $perpage, $perpage);
        $announce = new Announce($schema->theme);
        $result = $announce->getposts($list, $schema->postanounce);
        $result.= $schema->theme->getpages($this->items[$this->date]['url'], $this->getApp()->context->request->page, ceil(count($items) / $perpage));
        return $result;
    }

    public function getIdposts()
    {
        if (isset($this->_idposts)) {
            return $this->_idposts;
        }

        $item = $this->items[$this->date];
        $order = Schema::getSchema($this)->invertorder ? 'asc' : 'desc';
        return $this->_idposts = $this->getdb('posts')->idselect(
            "status = 'published' and
year(posted) = '{$item['year']}' and month(posted) = '{$item['month']}'
    ORDER BY posted $order"
        );
    }

    public function getSitemap($from, $count)
    {
        return $this->externalfunc(
            get_class($this), 'GetSitemap', array(
            $from,
            $count
            )
        );
    }
}

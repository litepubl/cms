<?php
/**
* Lite Publisher CMS
* @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
* @link https://github.com/litepubl\cms
* @version 6.15
**/

namespace litepubl\comments;
use litepubl\post\Post;
use litepubl\view\Filter;
use litepubl\view\Args;
use litepubl\view\Lang;
use litepubl\view\Vars;
use litepubl\core\Str;

class Comments extends \litepubl\core\Items
 {
    public $rawtable;
    private $pid;

    public static function i($pid = 0) {
        $result = static::iGet(get_called_class());
        if ($pid) {
$result->pid = $pid;
}
        return $result;
    }

    protected function create() {
        $this->dbversion = true;
        parent::create();
        $this->table = 'comments';
        $this->rawtable = 'rawcomments';
        $this->basename = 'comments';
        $this->addevents('edited', 'onstatus', 'changed', 'onapproved');
        $this->pid = 0;
    }

    public function add($idpost, $idauthor, $content, $status, $ip) {
        if ($idauthor == 0) $this->error('Author id = 0');
        $filter = Filter::i();
        $filtered = $filter->filtercomment($content);

        $item = array(
            'post' => $idpost,
            'parent' => 0,
            'author' => (int)$idauthor,
            'posted' => Str::sqlDate() ,
            'content' => $filtered,
            'status' => $status
        );

        $id = (int)$this->db->add($item);
        $item['id'] = $id;
        $item['rawcontent'] = $content;
        $this->items[$id] = $item;

        $this->getdb($this->rawtable)->add(array(
            'id' => $id,
            'created' => Str::sqlDate() ,
            'modified' => Str::sqlDate() ,
            'ip' => $ip,
            'rawcontent' => $content,
            'hash' => Str::baseMd5($content)
        ));

        $this->added($id);
        $this->changed($id);
        return $id;
    }

    public function edit($id, $content) {
        if (!$this->itemexists($id)) {
 return false;
}


        $filtered = Filter::i()->filtercomment($content);
        $this->db->setvalue($id, 'content', $filtered);
        $this->getdb($this->rawtable)->updateassoc(array(
            'id' => $id,
            'modified' => Str::sqlDate() ,
            'rawcontent' => $content,
            'hash' => Str::baseMd5($content)
        ));

        if (isset($this->items[$id])) {
            $this->items[$id]['content'] = $filtered;
            $this->items[$id]['rawcontent'] = $content;
        }

        $this->edited($id);
        $this->changed($id);
        return true;
    }

    public function delete($id) {
        if (!$this->itemexists($id)) {
 return false;
}


        $this->db->setvalue($id, 'status', 'deleted');
        $this->deleted($id);
        $this->changed($id);
        return true;
    }

    public function setStatus($id, $status) {
        if (!in_array($status, array(
            'approved',
            'hold',
            'spam'
        ))) return false;
        if (!$this->itemexists($id)) {
 return false;
}


        $old = $this->getvalue($id, 'status');
        if ($old != $status) {
            $this->setvalue($id, 'status', $status);
            $this->onstatus($id, $old, $status);
            $this->changed($id);
            if (($old == 'hold') && ($status == 'approved')) $this->onapproved($id);
            return true;
        }
        return false;
    }

    public function postdeleted($idpost) {
        $this->db->update("status = 'deleted'", "post = $idpost");
    }

    public function getComment($id) {
        return new tcomment($id);
    }

    public function getCount($where = '') {
        return $this->db->getcount($where);
    }

    public function select($where, $limit) {
        if ($where != '') $where.= ' and ';
        $table = $this->thistable;
        $authors =  $this->getApp()->db->users;
        $res =  $this->getApp()->db->query("select $table.*, $authors.name, $authors.email, $authors.website, $authors.trust from $table, $authors
    where $where $authors.id = $table.author $limit");

        return $this->res2items($res);
    }

    public function getRaw() {
        return $this->getdb($this->rawtable);
    }

    public function getApprovedcount() {
        return $this->db->getcount("post = $this->pid and status = 'approved'");
    }

    //uses in import functions
    public function insert($idauthor, $content, $ip, $posted, $status) {
        $filtered = Filter::i()->filtercomment($content);
        $item = array(
            'post' => $this->pid,
            'parent' => 0,
            'author' => $idauthor,
            'posted' => Str::sqlDate($posted) ,
            'content' => $filtered,
            'status' => $status
        );

        $id = $this->db->add($item);
        $item['rawcontent'] = $content;
        $this->items[$id] = $item;

        $this->getdb($this->rawtable)->add(array(
            'id' => $id,
            'created' => Str::sqlDate($posted) ,
            'modified' => Str::sqlDate() ,
            'ip' => $ip,
            'rawcontent' => $content,
            'hash' => Str::baseMd5($content)
        ));

        return $id;
    }

    public function getContent() {
        return $this->getcontentwhere('approved', '');
    }

    public function getHoldcontent($idauthor) {
        return $this->getcontentwhere('hold', "and $this->thistable.author = $idauthor");
    }

    public function getContentwhere($status, $where) {
        $result = '';
        $post = Post::i($this->pid);
        $theme = $post->theme;
        if ($status == 'approved') {
            if ( $this->getApp()->options->commentpages) {
                $page =  $this->getApp()->router->page;
                if ( $this->getApp()->options->comments_invert_order) $page = max(0, $post->commentpages - $page) + 1;
                $count =  $this->getApp()->options->commentsperpage;
                $from = ($page - 1) * $count;
            } else {
                $from = 0;
                $count = $post->commentscount;
            }
        } else {
            $from = 0;
            $count =  $this->getApp()->options->commentsperpage;
        }

        $table = $this->thistable;
        $items = $this->select("$table.post = $this->pid $where and $table.status = '$status'", "order by $table.posted asc limit $from, $count");

        $args = new Args();
        $args->from = $from;
        $comment = new Comment(0);
$vars = new Vars();
        $vars->comment = $comment;
        $lang = Lang::i('comment');

        $tml = strtr($theme->templates['content.post.templatecomments.comments.comment'], array(
            '$quotebuttons' => $post->comstatus != 'closed' ? $theme->templates['content.post.templatecomments.comments.comment.quotebuttons'] : ''
        ));

        $index = $from;
        $class1 = $theme->templates['content.post.templatecomments.comments.comment.class1'];
        $class2 = $theme->templates['content.post.templatecomments.comments.comment.class2'];
        foreach ($items as $id) {
            $comment->id = $id;
            $args->index = ++$index;
            $args->indexplus = $index + 1;
            $args->class = ($index % 2) == 0 ? $class1 : $class2;
            $result.= $theme->parsearg($tml, $args);
        }

        if (!$result){
 return '';
}

        if ($status == 'hold') {
            $tml = $theme->templates['content.post.templatecomments.holdcomments'];
        } else {
            $tml = $theme->templates['content.post.templatecomments.comments'];
        }

        $args->from = $from + 1;
        $args->comment = $result;
        return $theme->parsearg($tml, $args);
    }

} 
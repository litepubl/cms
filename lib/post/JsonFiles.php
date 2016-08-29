<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.06
  */

namespace litepubl\post;

use litepubl\admin\AuthorRights;
use litepubl\admin\GetPerm;
use litepubl\view\Filter;
use litepubl\view\Parser;
use litepubl\view\Theme;

/**
 * JSON-RPC methods to upload files
 *
 * @property-write callable $uploaded
 * @property-write callable $onProps
 * @method         array uploaded(array $params)
 * @method         array onProps(array $params)
 */

class JsonFiles extends \litepubl\core\Events
{

    protected function create()
    {
        parent::create();
        $this->addEvents('uploaded', 'onprops');
    }

    public function auth($idpost)
    {
        if (!$this->getApp()->options->user) {
            return false;
        }

        if ($this->getApp()->options->ingroup('editor')) {
            return true;
        }

        if ($idpost == 0) {
            return true;
        }

        if ($idauthor = $this->getdb('posts')->getvalue($idpost, 'author')) {
            return $this->getApp()->options->user == (int)$idauthor;
        }
        return false;
    }

    public function forbidden()
    {
        $this->error('Forbidden', 403);
    }

    public function files_getpost(array $args)
    {
        $idpost = (int)$args['idpost'];
        if (!$this->auth($idpost)) {
            return $this->forbidden();
        }

        $where = $this->getApp()->options->ingroup('editor') ? '' : ' and author = ' . $this->getApp()->options->user;

        $files = Files::i();
        $result = [
            'count' => (int)$files->db->getcount(" parent = 0 $where") ,
            'files' => []
        ];

        if ($idpost) {
            $list = $files->itemsposts->getitems($idpost);
            if (count($list)) {
                $items = implode(',', $list);
                $result['files'] = $files->db->res2items($files->db->query("select * from $files->thistable where id in ($items) or parent in ($items)"));
            }
        }

        if ($this->getApp()->options->show_file_perm) {
            Theme::getTheme('default');
            $result['fileperm'] = GetPerm::combo(0, 'idperm_upload');
        }

        return $result;
    }

    public function files_getpage(array $args)
    {
        if (!$this->getApp()->options->hasgroup('author')) {
            return $this->forbidden();
        }

        $page = (int)$args['page'];
        $perpage = isset($args['perpage']) ? (int)$args['perpage'] : 10;

        $from = $page * $perpage;
        $where = $this->getApp()->options->ingroup('editor') ? '' : ' and author = ' . $this->getApp()->options->user;

        $files = Files::i();
        $db = $files->db;

        $result = $db->res2items($db->query("select * from $files->thistable where parent = 0 $where order by id desc limit $from, $perpage"));

        if (count($result)) {
            $idlist = implode(',', array_keys($result));
            $thumbs = $db->res2items($db->query("select * from $files->thistable where parent in ($idlist)"));
            $result = array_merge($result, $thumbs);
        }

        return [
            'files' => $result
        ];
    }

    public function files_setprops(array $args)
    {
        if (!$this->getApp()->options->hasgroup('author')) {
            return $this->forbidden();
        }

        $id = (int)$args['idfile'];
        $files = Files::i();
        if (!$files->itemExists($id)) {
            return $this->forbidden();
        }

        $item = $files->getitem($id);
        $item['title'] = Filter::escape(Filter::unescape($args['title']));
        $item['description'] = Filter::escape(Filter::unescape($args['description']));
        $item['keywords'] = Filter::escape(Filter::unescape($args['keywords']));

        $r = $this->onProps(['item' => $item]);
        $item = $files->escape($r['item']);
        $files->db->updateassoc($item);
        return [
            'item' => $item
        ];
    }

    public function canUpload(): bool
    {
        $options = $this->getApp()->options;

        return $options->inGroup('editor')
        || ($options->hasGroup('author')
            && AuthorRights::canUpload());
    }

    public function files_upload(array $args)
    {
        if ('POST' != $_SERVER['REQUEST_METHOD']) {
            return $this->forbidden();
        }

        if (!isset($_FILES['Filedata']) || !is_uploaded_file($_FILES['Filedata']['tmp_name']) || $_FILES['Filedata']['error'] != 0) {
            return $this->forbidden();
        }

        //psevdo logout
        $this->getApp()->options->resetUser();

        if (!$this->canUpload()) {
            return $this->forbidden();
        }

        $parser = MediaParser::i();
        $id = $parser->uploadFile($_FILES['Filedata']['name'], $_FILES['Filedata']['tmp_name'], '', '', '', false);
        if (isset($_POST['idperm'])) {
            $idperm = (int)$_POST['idperm'];
            if ($idperm > 0) {
                PrivateFiles::i()->setPerm($id, (int)$_POST['idperm']);
            }
        }

        $this->uploaded(['id' => $id]);

        $files = Files::i();
        $item = $files->db->getitem($id);
        $files->items[$id] = $item;

        $result = [
            'id' => $id,
            'item' => $item
        ];

        if ((int)$item['preview']) {
            $result['preview'] = $files->db->getitem($item['preview']);
        }

        if ((int)$item['midle']) {
            $result['midle'] = $files->db->getitem($item['midle']);
        }

        return $result;
    }
}

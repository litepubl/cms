<?php
/**
* Lite Publisher CMS
* @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
* @link https://github.com/litepubl\cms
* @version 6.15
**/

namespace litepubl;
use litepubl\Config;
use litepubl\core\Str;

class polls extends titems {
    const votes = 'pollvotes';

    public static function i() {
        return getinstance(__class__);
    }

    protected function create() {
        $this->dbversion = true;
        parent::create();
        $this->addevents('edited');
        $this->basename = 'polls';
        $this->table = 'polls';
    }

    public function add($template, $idobject, $typeobject) {
        $best = $template == 'stars' ? 5 : 2;
        return $this->additem(array(
            'idobject' => (int)$idobject,
            'typeobject' => $typeobject,
            'votes' => 0,
            'rate' => 0.0,
            'best' => $best,
            'created' => Str::sqlDate() ,
            'status' => 'opened',
            'template' => $template,
        ));
    }

    public function setStatus($id, $status) {
        $this->setvalue($id, 'status', $status);
        if ($status == 'closed') {
            $this->getdb(static ::votes)->delete("idpoll = $id");
        }
    }

    public function close($id) {
        $this->setstatus($id, 'closed');
    }

    public function delete($id) {
        $this->db->iddelete($id);
        return parent::delete($id);
    }

    public function getObjectpoll($idobject, $typeobject) {
        return $this->getpoll($this->finditem("idobject = $idobject and typeobject = '$typeobject'"));
    }

    public function getPoll($id) {
        if (!$id) {
            return '';
        }

        if (Config::$debug) $this->getdb(static ::votes)->delete('iduser = ' .  $this->getApp()->options->user);
        $item = $this->getitem($id);

        $lang = Lang::i('poll');
        $args = new Args();
        $args->add($item);

        $theme = ttheme::context();
        if ($item['status'] == 'closed') {
            $tml = $theme->templates['polls.closed'];
        } else {
            $k = 'polls.' . $item['template'];
            $tml = $theme->templates[$k];

            if (isset($theme->templates[$k . '.item'])) {
                $tml_item = $theme->templates[$k . '.item'];
                $items = '';
                for ($vote = 1; $vote <= $item['best']; $vote++) {
                    $args->vote = $vote;
                    $items.= $theme->parsearg($tml_item, $args);
                }
                $args->item = $items;
            }
        }

        return $theme->parsearg($tml, $args);
    }

    public function err($mesg) {
        $lang = Lang::i('poll');

        return array(
            'error' => array(
                'code' => 403,
                'message' => $lang->$mesg
            )
        );
    }

    public function polls_sendvote(array $args) {
        extract($args, EXTR_SKIP);
        if (!isset($idpoll) || !isset($vote)) {
            $this->error('Invalid data', 403);
        }

        $idpoll = (int)$idpoll;
        if (!$idpoll) {
            $this->error('Invalid data', 403);
        }

        $iduser =  $this->getApp()->options->user;
        if (!$iduser) {
            $result = $this->err('notauth');
        } else if (!$this->itemexists($idpoll)) {
            $result = $this->err('notfound');
        } else if ('closed' == $this->getvalue($idpoll, 'status')) {
            $result = $this->err('closed');
        } else if ($this->hasvote($idpoll, $iduser)) {
            $result = $this->err('voted');
        } else {
            $vote = min(max(1, (int)$vote) , $this->getvalue($idpoll, 'best'));
            $this->addvote($idpoll, $iduser, (int)$vote);
            $item = $this->getitem($idpoll);

            $result = array(
                'code' => 'success',
                'item' => $item,
                'html' => $this->getpoll($idpoll) ,
            );
        }

        return $result;
    }

    public function hasvote($idpoll, $iduser) {
        return $this->getdb(static ::votes)->findprop('idpoll', "idpoll = $idpoll and iduser = $iduser");
    }

    public function addvote($id, $iduser, $vote) {
        $db = $this->getdb(static ::votes);
        $db->insert(array(
            'idpoll' => (int)$id,
            'iduser' => (int)$iduser,
            'vote' => (int)$vote,
        ));

        $t = $db->prefix . static ::votes;
        $statitems = $db->res2assoc($db->query("select count(idpoll) as count, vote from $t
where idpoll = $id group by vote order by vote asc"));

        //it impossible but maybe strange
        if (!count($statitems)) {
            return false;
        }

        $votes = 0;
        $rate = 0;
        foreach ($statitems as $statitem) {
            $votes+= $statitem['count'];
            $rate+= $statitem['vote'] * $statitem['count'];
        }

        $item = $this->getitem($id);
        $item['votes'] = $votes;
        $item['rate'] = $votes == 0 ? 0 : round($rate / $votes, 1);
        $item['bestvotes'] = $statitems[count($statitems) - 1]['count'];
        $item['worstvotes'] = count($statitems) == 1 ? 0 : $statitems[0]['count'];

        $this->db->updateassoc($item);
        $this->items[$id] = $item;
    }

    public function addfakevote($id) {
        $item = $this->getitem($id);
        $best = (int)$item['best'];
        $this->getdb(static ::votes)->insert(array(
            'idpoll' => (int)$id,
            'iduser' => 1,
            'vote' => $best,
        ));

        $item['votes'] = mt_rand(7, 167);
        $item['rate'] = mt_rand(($best - 1) * 10, $best * 10) / 10;
        $item['bestvotes'] = mt_rand(2, 120);
        $item['worstvotes'] = 1;

        $this->db->updateassoc($item);
        $this->items[$id] = $item;
    }

    public function optimize() {
        $date = Str::sqlDate(strtotime('-1 month'));
        $list = $this->db->idselect("created <= '$date' and status = 'opened'");
        if (count($list)) {
            $ids = implode(',', $list);
            $this->db->update("status = 'closed'", "id in ($ids)");
            $this->getdb(static ::votes)->delete("idpoll in ($ids)");
        }
    }

    public function objectdeleted($idobject, $typeobject) {
        if ($id = $this->db->findid("idobject = $idobject and typeobject = '$typeobject'")) {
            $this->delete($id);
        }
    }

    public function postdeleted($idpost) {
        $this->objectdeleted($idpost, 'post');
    }

    public function filter(&$content) {
        if (preg_match_all('/\[poll\=(\d*?)\]/', $content, $m, PREG_SET_ORDER)) {
            $polls = tpolls::i();
            foreach ($m as $item) {
                $id = (int)$item[1];
                if ($polls->itemexists($id)) {
                    $html = $polls->gethtml($id);
                    $html = '[html]' . $html . '[/html]';
                } else {
                    $html = '';
                }

                $content = str_replace($item[0], $html, $content);
            }
        }
    }

} //class
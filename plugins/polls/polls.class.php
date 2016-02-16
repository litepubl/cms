<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

class tpolls extends titems {
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
  'idobject' => (int) $idobject,
'typeobject' => $typeobject,
  'votes' => 0,
  'rate' => 0.0,
  'best' => $best,
  'created' => sqldate(),
  'status' => 'opened',
'template' => $template,
));
  }

  public function setstatus($id, $status) {
    $this->setvalue($id, 'status', $status);
if ($status == 'closed') {
$this->getdb(self::votes)->delete("idpoll = $id");
}
  }

  public function close($id) {
    $this->setstatus($id, 'closed');
  }

  public function delete($id) {
    $this->db->iddelete($id);
return parent::delete($id);
  }

  public function getobjectpoll($idobject, $typeobject) {
return $this->getpoll($this->finditem("idobject = $idobject and typeobject = '$typeobject'"));
}

  public function getpoll($id) {
if (!$id) {return '';
}

    $item = $this->getitem($id);

    $args = new targs();
    $args->add($item);

   $theme = ttheme::i();
    $tml = $theme->templates[$item['status'] == 'opened' ? 'polls.' . $item['template'] : 'polls.closed'];
return $theme->parsearg($tml, $args);
  }

  public function err($mesg) {
    $lang = tlocal::i('poll');

    return array(
      'code' => 'error',
      'message' => $lang->$mesg
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

    $iduser = litepublisher::$options->user;
    if (!$iduser) {
$result = $this->err('notauth');
} else     if (!$this->itemexists($idpoll)) {
$result = $this->err('notfound');
} else     if ('closed' == $this->getvalue($idpoll, 'status')) {
$result = $this->err('closed');
} else if ($this->hasvote($idpoll, $iduser)) {
$result = $this->err('voted');
} else {
    $vote = min(max(1, (int)$vote), $this->getvalue($id, 'best')));
$this->addvote($idpoll, $iduser, (int)$vote);
$item = $this->getitem($id);

    $result = array(
      'code' => 'success',
'item' => $item,
'html' => $this->getplll($id),
    );
}

return $result;
  }

  public function hasvote($idpoll, $iduser) {
    return $this->getdb(self::votes)->findprop('idpoll', "idpoll = $idpoll and iduser = $iduser");
  }

  public function addvote($id, $iduser, $vote) {
$db = $this->getdb(self::votes);
$db->insert(array(
'idpoll' => (int) $idpoll,
'iduser' => (int) $iduser,
'vote' => (int) $vote,
));

$t = $db->prefix . self::$votes;
$statitems = $db->res2assoc($db->query(
"select count(idpoll as count, vote from $t
where idpoll = $id group by vote order by vote asc"
));

$votes = 0;
$rate = 0;
    foreach ($statitems as $item) {
$votes += $item['count'];
      $rate += $item['vote'] * $item['count'];
    }

$item = $this->getitem($id);
$item['votes'] = $votes;
    $item['rate'] = $votes == 0 ? 0 : round($rate / $votes, 1);
$item['worstvotes'] = count($statitems) ? $statitems[0]['count'] : 0;
$item['bestvotes'] = count($statitems) ? $statitems[count($statitems) - 1]['count'] : 0;

    $this->db->updateassoc($item);
$this->items[$id] = $item;
  }

 public function optimize() {
$date =sqldate(strtotime('-1 month'));
$list = $this->db->idselect("created <= '$date' and status = 'opened');
if (count($list)) {
$ids = implode(',', $list);
$this->db->update("status = 'closed'", "id in($ids)");
$this->getdb(self::votes)->delete("idpoll in ($ids)");
}
}

public function objectdeleted($idobject, $typeobject) {
if ($id = $this->db->findid("idobject = $idpost and typeobject = 'post'")) {
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
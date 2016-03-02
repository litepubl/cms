<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */
class tadminfoaf extends tadminmenu {
  private $user;

  public static function i($id = 0) {
    return parent::iteminstance(__class__, $id);
  }

  private function getlist() {
    $foaf = tfoaf::i();
    $perpage = 20;
    $total = $foaf->getcount();
    $from = $this->getfrom($perpage, $total);

      $items = $foaf->select('', " order by status asc, added desc limit $from, $perpage");
      if (!$items) $items = array();

$tb = new tablebuilder();
$tb->admintheme = $this->admintheme;
$tb->setowner($foaf);
$tb->setstruct(array(
$tb->checkbox('checkbox'),
array(
$lang->nick,
'$nick'
),

array(
$lang->url,
'$url',
),

array(
$lang->status,
function(tablebuilder $b) {
return tlocal::i()->__get($b->item['status']);
}),

array(
$lang->edit,),
"<a href=\"$this->adminurl=\$id&action=edit\">$lang->edit</a>",
),
));

$form = new adminform();
$fform->body = $tb->build($items);
    $form->body .= $form->centergroup($this->html->getsubmit('approve', 'hold', 'delete'));
$form->body .= $form->hidden('foaftable', 1);
$form->submit = false;
$result = $form->get();

    $result.= $this->theme->getpages('/admin/foaf/', litepublisher::$urlmap->page, ceil($total / $perpage));
    return $result;
  }

  public function getcontent() {
    $result = '';
    $foaf = tfoaf::i();
$admintheme = $this->admintheme;
    $lang = tlocal::i('foaf');
    $html = $this->html;
$args = new targs();

    switch ($this->name) {
      case 'foaf':
        switch ($this->action) {
          case false:
$args->url = '';
$form = new adminform($args);
$form->title = $lang->addform;
$form->body = '[text=url]';
$form->submit = 'add';
            $result = $form->get();
            break;


          case 'edit':
            $id = $this->idget();
            if (!$foaf->itemexists($id)) {
              return $this->notfound;
            }

            $item = $foaf->getitem($id);
            $args->add($item);
    $statuses = array(
      'approved' => $lang->approved,
      'hold' => $lang->hold,
      'invated' => $lang->invated,
      'rejected' => $lang->rejected,
      'spam' => $lang->spam,
      'error' => $lang->error,
    );

            $args->status = tadminhtml::array2combo($statuses, $item['status']);
            $result.= $admintheme->form('
[text=nick]
[text=url]
[text=foafurl]
[combo=status]
', $args);
            break;


          case 'delete':
            $id = $this->idget();
            if (!$foaf->itemexists($id)) {
              return $this->notfound;
            }

            if ($this->confirmed) {
              $foaf->delete($id);
              $result.= $admintheme->h($lang->deleted);
            } else {
              $item = $foaf->getitem($id);
$mesg = "$lang->confirmdelete {$item['nick']} ({$item['url']})?";
              $result.= $this->html->confirmdelete($id, $this->adminurl, $mesg);
            }
            break;
        }
        $result.= $this->getlist();
        break;


      case 'profile':
        $profile = tprofile::i();
$vars = new themevars();
$vars->profile  = $profile;
        $args = targs::i();
        $form = '';
        foreach (array(
          'nick',
          'img',
          'dateOfBirth',
          'googleprofile',
          'skype',
          'icqChatID',
          'aimChatID',
          'jabberID',
          'msnChatID',
          'yahooChatID',
          'mbox',
          'country',
          'region',
          'city',
          'geourl',
          'interests',
          'interesturl'
        ) as $name) {
          $args->$name = $profile->$name;
          $form.= is_bool($profile->$name) ? "[checkbox=$name]" : "[text=$name]";
          if (!isset($lang->$name)) $args->data["\$lang.$name"] = $name;
        }
        $args->gender = $profile->gender != 'female';
        $args->data['$lang.gender'] = $lang->ismale;
        $args->bio = $profile->bio;
        $args->formtitle = $lang->profileform;
        $result.= $html->adminform($form . '[checkbox=gender]
      [editor=bio]
      ', $args);
        break;


      case 'profiletemplate':
        $profile = tprofile::i();
        $args->template = $profile->template;
        $result.= $admintheme->form('[editor=template]', $args);
        break;
      }

      return $result;
  }

  public function processform() {
    $foaf = tfoaf::i();
    switch ($this->name) {
      case 'foaf':
        if (!isset($_POST['foaftable'])) {
          extract($_POST, EXTR_SKIP);
          if ($this->action == 'edit') {
            $id = $this->idget();
            if (!$foaf->itemexists($id)) {
              return '';
            }

            $foaf->edit($id, $nick, $url, $foafurl, $status);
            return $this->admintheme->success($lang->successedit);
          } else {
            if (empty($url)) {
              return '';
            }

            if ($foaf->hasfriend($url)) {
              return $this->admintheme->h($lang->erroradd);
            }

            $foaf->addurl($url);
            return $this->admintheme->h($lang->successadd);
          }
        } else {
          $status = isset($_POST['approve']) ? 'approved' : (isset($_POST['hold']) ? 'hold' : 'delete');
          $foaf->lock();
          foreach ($_POST as $key => $id) {
            if (!is_numeric($id)) continue;
            $id = (int)$id;
            if ($status == 'delete') {
              $foaf->delete($id);
            } else {
              $foaf->changestatus($id, $status);
            }
          }
          $foaf->unlock();
          return $this->admintheme->h($lang->successmoderate);
        }

      case 'profile':
        $profile = tprofile::i();
        foreach ($_POST as $key => $value) {
          if (isset($profile->data[$key])) $profile->data[$key] = $value;
        }
        $profile->gender = isset($_POST['gender']) ? 'male' : 'female';
        $profile->save();
        return $html->h2->successprofile;

      case 'profiletemplate':
        $profile = tprofile::i();
        $profile->template = $_POST['template'];
        $profile->save();
        return $html->h2->successprofile;
      }

      return '';
    }

} //class
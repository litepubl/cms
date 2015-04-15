<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tadmincommoncomments extends tadminmenu {
  protected $user;
  protected $showcolumns;
  
  public function gethead() {
    $result = parent::gethead();
    $result .= ttemplate::i()->getjavascript('/js/litepublisher/tablecolumns.min.js');
    return $result;
  }
  
  protected function getmanager() {
    return litepublisher::$classes->commentmanager;
  }
  
  protected function create() {
    parent::create();
    $this->showcolumns = array();
    //tfilestorage::loadvar(litepublisher::$paths->data . 'commentscolumns.php', $this->showcolumns);
    if (isset($_COOKIE['tablecolumns'])) $this->showcolumns = unserialize($_COOKIE['tablecolumns']);
    if (!in_array(true, $this->showcolumns)) $this->showcolumns = array();
  }
  
  protected function saveshowcolumns() {
    //tfilestorage::savevar(litepublisher::$paths->data .'commentscolumns', $this->showcolumns);
    setcookie('tablecolumns', serialize($this->showcolumns), time() + 30000000, '/admin/comments', false);
  }
  
  protected function showcolumn($index, $default) {
    return isset($this->showcolumns[$index])? $this->showcolumns[$index] : $default;
  }
  
  public function createtable() {
    $lang = tlocal::admin('comments');
    $table = new ttablecolumns();
    $table->index = 1;
    $table->checkboxes[]  = $this->html->tablecolumns();
    $table->add(
    '$id',
    'ID',
    'right',
    $this->showcolumn($table->index + 1, true));
    
    $table->add(
    '$comment.date',
    $lang->date,
    'left',
    $this->showcolumn($table->index + 1, false));
    
    $table->add(
    '$comment.localstatus',
    $lang->status,
    'left',
    $this->showcolumn($table->index + 1, false));
    
    $table->add(
  '<a href="$site.url/admin/users/{$site.q}id=$comment.author&action=edit">$comment.name</a>',
    $lang->author,
    'left',
    $this->showcolumn($table->index + 1, true));
    
    $table->add(
    '$email',
    'E-Mail',
    'left',
    $this->showcolumn($table->index + 1, true));
    
    $table->add(
    '$website',
    $lang->website,
    'left',
    $this->showcolumn($table->index + 1, false));
    
    $table->checkboxes[] = "<br />$lang->content: ";
    $table->add(
    '<a href="$comment.url">$comment.posttitle</a>',
    $lang->post,
    'left',
    $this->showcolumn($table->index + 1, false));
    
    $table->add(
    '$excerpt',
    $lang->content,
    'left',
    $this->showcolumn($table->index + 1, true));
    
    $table->add(
    '$comment.ip',
    'IP',
    'left',
    $this->showcolumn($table->index + 1, false));
    
    $table->checkboxes[]  = "<br />$lang->moderate: ";
    $table->add(
    '<a href="$adminurl=$comment.id&action=reply">$lang.reply</a>',
    $lang->reply,
    'left',
    $this->showcolumn($table->index + 1, false));
    
    $table->add(
    '<a href="$adminurl=$comment.id&action=approve">$lang.approve</a>',
    $lang->approve,
    'left',
    $this->showcolumn($table->index + 1, false));
    
    $table->add(
    '<a href="$adminurl=$comment.id&action=hold">$lang.hold</a>',
    $lang->hold,
    'left',
    $this->showcolumn($table->index + 1, false));
    
    $table->add(
    '<a class="confirm-delete-link" href="$adminurl=$comment.id&action=delete">$lang.delete</a>',
    $lang->delete,
    'left',
    $this->showcolumn($table->index + 1, false));
    
    $table->add(
    '<a href="$adminurl=$comment.id&action=edit">$lang.edit</a>',
    $lang->edit,
    'left',
    $this->showcolumn($table->index + 1, false));
    
    $table->body ='<tr>
    <td align ="center"><input type="checkbox" name="checkbox-item-$id" id="checkbox-item-$id" value="$id" $onhold /></td>' .
    $table->body . '</tr>';
    
    $table->checkboxes[]  = '</p>-->';
    return $table;
  }
  
  public function processform() {
    if (isset($_POST['changed_hidden'])) {
      //$l = $table->index;
      // 1 based index because jquery selector nth-child same indexed
      $l = 15;
      for ($i = 1; $i<= $l; $i++) {
        $this->showcolumns[$i] = isset($_POST["checkbox-showcolumn-$i"]);
      }
      $this->saveshowcolumns();
    }
  }
  
}//class
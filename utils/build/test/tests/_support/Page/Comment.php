<?php
namespace Page;

class Comment extends Base
{
public  $comment = '#comment';
      public  $email = 'input[name=email]';
      public  $submit = '#submit-button';
public $postlink= '.post-bookmark';

public function send($comment)
{
$i = $this->tester;
//$i->fillField($this->email, $email);
$i->fillField($this->comment, $comment);
$i->click($this->submit);
$i->checkError();
return $this;
}

}
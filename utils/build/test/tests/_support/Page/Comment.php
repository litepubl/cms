<?php
namespace Page;

class Comment extends Base
{
public  $comment = '#comment';
      public  $email = 'input[name=email]';
      public  $submit = '#submit-button';
public $postlink= '.post-bookmark';

public function send(string $comment)
{
$i = $this->tester;
//$i->fillField($this->email, $email);
$i->fillField($this->comment, $comment);
$this->screenshot('send');
$i->click($this->submit);
$i->checkError();
}

}
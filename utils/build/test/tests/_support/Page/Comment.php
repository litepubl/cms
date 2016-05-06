<?php
namespace Page;

class Comment
{
use TesterTrait;

public static $comment = '#comment';
      public static $email = 'input[name=email]';

      public static $submit = '#submit-button';

public function send($comment)
{
$i = $this->tester;
//$i->fillField(static::$email, $email);
$i->fillField(static::$comment, $comment);
$i->click(static::$submit);
$i->checkError();
return $this;
}

}
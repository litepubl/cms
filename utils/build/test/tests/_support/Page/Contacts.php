<?php
namespace Page;

class Contacts extends Base
{
      public  $email = 'input[name=email]';
public  $message = '#editor-content';
      public  $submit = '#submitbutton-update';

public function sendForm($email, $message)
{
$i = $this->tester;
$i->fillField($this->email, $email);
$i->fillField($this->message, $message);
$i->click($this->submit);
$i->checkError();
return $this;
}

}
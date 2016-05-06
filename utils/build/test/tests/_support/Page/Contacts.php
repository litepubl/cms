<?php
namespace Page;

class Contacts 
{
use TesterTrait;

    // include url of current page
      public static $email = 'input[name=email]';
public static $message = '#editor-content';
      public static $submit = '#submitbutton-update';

public function sendForm($email, $message)
{
$i = $this->tester;
$i->fillField(static::$email, $email);
$i->fillField(static::$message, $message);
$i->click(static::$submit);
$i->checkError();
return $this;
}

}
<?php
namespace Page;

class Cats extends Base
{
    public  $url = '/admin/posts/addcats/';

public $enabled = 'input[name=usersenabled]';
public $reguser = 'input[name=reguser]';

      public  $email = '[name=email]';
public  $name = '[name=name]';
      public  $submit = '#submitbutton-signup';

}
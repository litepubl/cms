<?php
namespace Page;
use test\config;

class RegUser extends Base
{
    public  $url = '/admin/reguser/';
    public  $optionsUrl = '/admin/options/secure/';
public $enabled = 'input[name=usersenabled]';
public $reguser = 'input[name=reguser]';

      public  $email = '[name=email]';
public  $name = '[name=name]';
      public  $submit = '#submitbutton-signup';

}
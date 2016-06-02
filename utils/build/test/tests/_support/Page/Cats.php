<?php
namespace Page;

class Cats extends Base
{
    public  $url = '/admin/posts/addcats/';
public $title = '#text-title';
public $content = '#editor-raw';
public $parent = 'select[name=parent]';


public $titleFixture = 'New category';
public $contentFixture = 'Some category content';
}
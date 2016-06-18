<?php
namespace Page;

class Cats extends Base
{
    public $url = '/admin/posts/addcat/';
    public $title = '#text-title';
    public $content = '#editor-raw';
    public $parent = 'select[name=parent]';


    public $titleFixture = 'New category';
    public $contentFixture = 'Some category content';
}

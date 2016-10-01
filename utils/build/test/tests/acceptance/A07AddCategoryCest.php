<?php
namespace litepubl\tests\acceptance;

class A07AddCategoryCest extends \Page\Base
{
    protected $url = '/admin/posts/addcat/';
    protected $title = '#text-title';
    protected $content = '#editor-raw';
    protected $parent = 'select[name=parent]';

    protected $titleFixture = 'New category';
    protected $contentFixture = 'Some category content';

    protected function test(\AcceptanceTester $i)
    {
        $i->wantTo('Test category editor');
        $this->open();
        $this->screenShot('addcat');

        $i->fillField($this->title, $this->titleFixture);
        //$i->selectOption($this->parent, 
        $this->screenShot('title');

        //final submit
        $i->executeJs('$("form:last").submit();');
        $i->checkError();
        $this->screenShot('saved');

    }
}

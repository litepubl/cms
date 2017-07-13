<?php

namespace litepubl\tests\acceptance;

class A20TicketsCest extends \Page\Editor
{
    use \page\Posts;

    protected $url = '/admin/tickets/editor/';
    protected $optionsUrl = '/admin/tickets/options/';
    protected $codeTab = '#tab-1';
    protected $codeEditor = '[name="code"]';

    protected function add(): int
    {
        $this->open();
        $i = $this->tester;
        $i->wantTo('Create ticket');
        $this->fillTitleContent(
            'Some ticket title',
            'Test withproblem description'
        );

        $this->screenShot('editor');
        $i->click($this->codeTab);
        $i->waitForElement($this->codeEditor, 1);
        $i->fillField($this->codeEditor, '<?php echo  \'Hello world\';');
        $this->screenShot('code');
        $this->submit();
        $id = $this->getPostId();
        $i->openPage($this->getPostLink());
        $this->screenshot('ticket');
        return $id;
    }

    protected function test(\AcceptanceTester $i)
    {
        $this->postsUrl = '/admin/tickets/';
        $i->wantTo('Test tickets plugin');
        $this->installPlugin('tickets');

        $i->wantTo('Test tickets options');
        $i->openPage($this->optionsUrl);
        $i->checkOption($this->category);
        $this->screenshot('options');
        $i->click($this->updateButton);
        $i->checkError();

        $id = $this->add();
        $i->wantTo('Delete new ticket');
        $this->deletePosts($id);
        $this->logout();

        $i->wantTo('Test user ticket');
        $ulogin = $this->getUlogin();
        $ulogin->_click();
        $i->waitForUrlChanged(15);
        $id = $this->add();
        $this->logout();
        $i->wantTo('Delete new ticket');
        $this->deletePosts($id);
        $this->uninstallPlugin('tickets');
    }
}

<?php

namespace litepubl\tests\acceptance;

class A20TicketsCest extends \Page\Editor
{
    use \page\Posts;

    protected $url = '/admin/tickets/';
    protected $url = '/admin/tickets/editor/';
    protected $optionsUrl = '/admin/tickets/options/';
    private $postsUrl = '/admin/tickets/';

    protected function test(\AcceptanceTester $i)
    {
        $i->wantTo('Test tickets plugin');
        $this->installPlugin('tickets');

$i->wantTo('Test tickets options');
$i->openPage($this->optionsUrl);
        $i->checkOption($this->category);
$this->screenshot('options');
$this->submit();

        $this->open();
        $i->wantTo('Create admin ticket');
        $i->checkOption($this->category);
        $this->fillTitleContent(
            'Declare wiki word',
            'Some text with [wiki:wikilink] must be here'
        );

        $this->submit();

        $i->wantTo('Get id post #1');
        $id1 = $this->getPostId();
        $holderlink = $this->getPostLink();
        $this->screenShot('declare');

        $i->openPage($this->url);
        $i->wantTo('Create post with use wiki word');
        $i->checkOption($this->category);
        $this->fillTitleContent(
            'Use wiki word',
            'Some text where used [[wikilink]]. Link must be present'
        );

        $this->submit();
        $this->screenShot('use');

        $i->wantTo('Get id post #2');
        $id2 = $this->getPostId();

        $i->wantTo('Check used word');
        $i->amOnUrl($this->getPostLink());
        $i->checkError();
        $this->screenShot('used');
        $i->click($this->wikiLink);
        $i->checkError();
        $i->assertEquals($holderlink, $i->getAbsoluteUrl(), 'Wiki word linked');
        $this->screenShot('declared');

        $i->wantTo('Delete new posts');
        $this->deletePosts($id1, $id2);

        $this->uninstallPlugin('tickets');

    }
}

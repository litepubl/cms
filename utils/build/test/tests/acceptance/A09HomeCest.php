<?php
/**
 * LitePubl CMS
 *
 * @copyright 2010 - 2017 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.08
  */

namespace litepubl\tests\acceptance;

class A09HomeCest extends \Page\Base
{
    protected $url = '/admin/options/home/';
    protected $imageTab = '#tab-1';
    protected $image = '#text-image';
    protected $smallimage = '#text-smallimage';

    protected function uploadImage(string $filename)
    {
        $this->upload($filename);
        $r = $this->js('home.upload.js');
        if ($r) {
            codecept_debug(var_export($r, true));
        }

        $this->tester->waitForJs($this->getJs('home.wait.js'), 12);
    }

    protected function test(\AcceptanceTester $i)
    {
        $i->wantTo('Test home image');
        $this->open();
        $i->wantTo('Remove current image');
        $this->clickTab($this->imageTab);
        $i->fillField($this->image, '');
        $i->fillField($this->smallimage, '');
        $this->submit();

        $i->wantTo('See empty home page');
        $i->openPage('/');
        $this->screenshot('noimage');
        $i->wantTo('Upload image');
        $this->open();
        $this->clickTab($this->imageTab);
        $this->uploadImage('img1.jpg');

        $this->submit();
        $this->screenshot('uploaded');
        $i->openPage('/');
        $this->screenshot('image');
    }
}

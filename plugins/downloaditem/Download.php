<?php
/**
 * Lite Publisher CMS
 * @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link https://github.com/litepubl\cms
 * @version 7.00
 *
 */


namespace litepubl\plugins\downloaditem;

use litepubl\view\Filter;

class Download extends \litepubl\post\Post
{

    public static function getChildTable(): string
    {
        return 'downloaditems';
    }

    protected function create()
    {
        parent::create();
        $this->childData = array(
            'type' => 'plugin',
            'downloads' => 0,
            'downloadurl' => '',
            'authorurl' => '',
            'authorname' => '',
            'version' => '1.00',
        );
    }

    public function getFactory()
    {
        return Factory::i();
    }

    public function getParenttag()
    {
        return $this->type == 'theme' ? $this->getApp()->options->downloaditem_themetag : $this->getApp()->options->downloaditem_plugintag;
    }

    public function setTagNames(string $names)
    {
        $names = trim($names);
        if ($names == '') {
            $this->tags = array();
            return;
        }

        $parent = $this->getParentTag();
        $tags = $this->factory->tags;
        $items = array();
        $list = explode(',', $names);
        foreach ($list as $title) {
            $title = Filter::escape($title);
            if ($title == '') {
                continue;
            }

            $items[] = $tags->add($parent, $title);
        }

        $this->tags = $items;
    }
}

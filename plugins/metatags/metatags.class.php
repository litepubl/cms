<?php
/**
* Lite Publisher CMS
* @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
* @link https://github.com/litepubl\cms
* @version 6.15
**/

namespace litepubl;
use litepubl\view\MainView;

class tmetatags extends \litepubl\core\Plugin
 {

    public static function i() {
        return getinstance(__class__);
    }

    public function themeparsed(ttheme $theme) {
        $theme->templates['index'] = strtr($theme->templates['index'], array(
            '$template.keywords' => '$metatags.keywords',
            '$template.description' => '$metatags.description',
        ));
    }

    public function getList() {
        $context = MainView::i()->context;
        if ($context instanceof tcommontags) {
            $list = $context->getidposts($context->id);
        } elseif (isset($context) && isset($context->idposts)) {
            $list = $context->idposts;
        } else {
            return false;
        }

        if (count($list) > 0) {
            tposts::i()->loaditems($list);
            return array_slice($list, 0, 3);
        }

        return false;
    }

    public function getKeywords() {
        if ($list = $this->getlist()) {
            $result = '';
            foreach ($list as $id) {
                $post = tpost::i($id);
                $result.= $post->keywords . ', ';
            }
            return trim($result, ', ');
        }
        return MainView::i()->getkeywords();
    }

    public function getDescription() {
        if ($list = $this->getlist()) {
            $result = '';
            foreach ($list as $id) {
                $post = tpost::i($id);
                $result.= $post->title . ' ';
                if (strlen($result) > 250) break;
            }
            //return tcontentfilter::getexcerpt($result, 300);
            return $result;
        }
        return MainView::i()->getdescription();
    }

}
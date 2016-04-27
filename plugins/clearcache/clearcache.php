<?php
/**
* Lite Publisher CMS
* @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
* @link https://github.com/litepubl\cms
* @version 6.15
**/

namespace litepubl;

class tclearcache extends \litepubl\core\Plugin
 {

    public static function i() {
        return static::iGet(__class__);
    }

    public function clearcache() {
        tfiler::delete( $this->getApp()->paths->data . 'themes', false, false);
         $this->getApp()->cache->clear();
    }

    public function themeparsed(ttheme $theme) {
        $name = $theme->name;
        $schemes = Schemes::i();
        foreach ($schemes->items as & $itemview) {
            if ($name == $itemview['themename']) {
                $itemview['custom'] = $theme->templates['custom'];
            }
        }
        $schemes->save();
    }

}
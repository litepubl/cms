<?php
/**
 * Lite Publisher CMS
 * @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link https://github.com/litepubl\cms
 * @version 6.15
 *
 */

namespace litepubl\admin\options;

use litepubl\tag\Cats;
use litepubl\tag\Tags;
use litepubl\view\Lang;

class CatsTags extends \litepubl\admin\Menu
{
    public function getContent(): string
    {
        $lang = Lang::admin('options');
        $admin = $this->admintheme;
        $args = $this->newArgs();

        $cats = Cats::i();
        $args->parentcats = $cats->includeparents;
        $args->childcats = $cats->includechilds;

        $tags = Tags::i();
        $args->parenttags = $tags->includeparents;
        $args->childtags = $tags->includechilds;
        $args->formtitle = $lang->catstags;
        return $admin->form('
      [checkbox=parentcats]
 [checkbox=childcats]
      [checkbox=parenttags]
 [checkbox=childtags]
', $args);
    }

    public function processForm()
    {
        extract($_POST, EXTR_SKIP);
        $cats = Cats::i();
        $cats->includeparents = isset($parentcats);
        $cats->includechilds = isset($childcats);
        $cats->save();

        $tags = Tags::i();
        $tags->includeparents = isset($parenttags);
        $tags->includechilds = isset($childtags);
        $tags->save();
    }

}


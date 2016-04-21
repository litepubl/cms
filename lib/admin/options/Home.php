<?php
/**
* Lite Publisher CMS
* @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
* @link https://github.com/litepubl\cms
* @version 6.15
**/

namespace litepubl\admin\options;
use litepubl\view\Lang;
use litepubl\view\Args;
use litepubl\pages\Home as HomePage;
use litepubl\pages\Menus;
use litepubl\post\MediaParser;
use litepubl\core\Str;
use litepubl\view\Parser;

class Home extends \litepubl\admin\Menu
{

    public function getHead() {
        $result = parent::gethead();

        $result.= '<script type="text/javascript" src="$site.files/js/plugins/filereader.min.js"></script>';
        $result.= '<script type="text/javascript" src="$site.files/js/litepubl/admin/homeuploader.min.js"></script>';

        return $result;
    }

    public function getContent() {
        $args = new Args();
        $lang = Lang::admin('options');
        $home = HomePage::i();
        $tabs = $this->newTabs();
        $args->image = $home->image;
        $args->smallimage = $home->smallimage;
        $args->parsetags = $home->parsetags;
        $args->showmidle = $home->showmidle;
        $args->midlecat = tposteditor::getcombocategories(array() , $home->midlecat);
        $args->showposts = $home->showposts;
        $args->invertorder = $home->getSchema()->invertorder;
        $args->showpagenator = $home->showpagenator;

        $args->idhome = $home->id;
        $menus = Menus::i();
        $args->homemenu = $menus->home;

        $tabs->add($lang->options, '
    [checkbox=homemenu]
    [checkbox=showmidle]
    [combo=midlecat]
    [checkbox=showposts]
    [checkbox=invertorder]
    [checkbox=showpagenator]
    [checkbox=parsetags]
    ');

        $lang->addsearch('editor');
        $tabs->add($lang->images, '
    [text=image]
    [text=smallimage]
    [upload=imgupload]
    <div id="dropzone" class="help-block">$lang.dragfiles</div>
    
    <h5 id="helpstatus" class="help-block">
    <span id="img-help" class="text-info">$lang.imagehelp</span>
    <span id="img-success" class="text-success hide">$lang.imgsuccess</span>
    <span id="img-fail" class="text-danger hide">$lang.imgfail</span>
    <span id="img-percent" class=text-info hide"></span>
    </h5>
    ');

$admin = $this->admintheme;
        $tabs->add($lang->includecats, $admin->h($lang->includehome) . $admin->getcats($home->includecats));
        $tabs->add($lang->excludecats, $admin->h($lang->excludehome) . 
str_replace('category-', 'exclude_category-', $admin->getcats($home->excludecats)));

        $args->formtitle = $lang->homeform;
        return $admin->form('<h4><a href="$site.url/admin/menu/edit/{$site.q}id=$idhome">$lang.hometext</a></h4>' . $tabs->get() , $args);
    }

    public function processForm() {
        extract($_POST, EXTR_SKIP);
        $home = HomePage::i();
        $home->lock();
        $home->image = $image;
        $home->smallimage = $smallimage;
        $home->parsetags = isset($parsetags);
        $home->showmidle = isset($showmidle);
        $home->midlecat = (int)$midlecat;
        $home->showposts = isset($showposts);
        $home->getSchema()->invertorder = isset($invertorder);
$home->getSchema()->save();

        $home->includecats = $this->admintheme->check2array('category-');
        $home->excludecats = $this->admintheme->check2array('exclude_category-');
        $home->showpagenator = isset($showpagenator);
        $home->postschanged();
        $home->unlock();

        $menus = Menus::i();
        $menus->home = isset($homemenu);
        $menus->save();
    }

    public function request($a) {
        if ($response = parent::request($a)) {
            return $response;
        }

        $name = 'image';
        if (!isset($_FILES[$name])) {
 return;
}



        $result = array(
            'result' => 'error'
        );

        if (is_uploaded_file($_FILES[$name]['tmp_name']) &&
 !$_FILES[$name]['error'] &&
 Str::begin($_FILES[$name]['type'], 'image/') &&
 ($data = file_get_contents($_FILES[$name]['tmp_name']))) {
            $home = HomePage::i();
            $index = 1;
            if (preg_match('/^\/files\/home(\d*+)\.jpg$/', $home->image, $m)) {
                $index = (int)$m[1];
                $filename =  $this->getApp()->paths->files . "home$index.jpg";
                if (file_exists($filename)) {
                    @unlink($filename);
                }

                $filename =  $this->getApp()->paths->files . "home$index.small.jpg";
                if (file_exists($filename)) {
                    @unlink($filename);
                }

                $index++;
            }

            $home->image = "/files/home$index.jpg";
            $home->smallimage = "/files/home$index.small.jpg";

            $filename =  $this->getApp()->paths->files . "home$index.jpg";
            if (file_exists($filename)) {
                @unlink($filename);
            }

            if (move_uploaded_file($_FILES[$name]['tmp_name'], $filename)) {
                @chmod($filename, 0666);

                if ($image = tmediaparser::readimage($filename)) {
                    $maxwidth = 1900;
                    $maxheight = $maxwidth / 4 * 3;
                    if (imagesx($image) > $maxwidth) {
                        @unlink($filename);
                        MediaParser::createthumb($image, $filename, $maxwidth, $maxheight, 80, 'max');
                    } else if (filesize($filename) > 1024 * 1024 * 800) {
                        //no resize just save in low quality
                        @unlink($filename);
                        imagejpeg($image, $filename, 80);
                        @chmod($filename, 0666);
                    }

                    //create small image
                    $smallfile =  $this->getApp()->paths->files . "home$index.small.jpg";
                    if (file_exists($smallfile)) {
                        @unlink($smallfile);
                    }

                    MediaParser::createthumb($image, $smallfile, 760, 760 / 4 * 3, 80, 'max');
                    imagedestroy($image);

                    $home->save();

                    $result = array(
                        'result' => array(
                            'image' => $home->image,
                            'smallimage' => $home->smallimage
                        )
                    );
                }
            }
        }

        $js = Str::toJson($result);
        return "<?php
    header('Connection: close');
    header('Content-Length: " . strlen($js) . "');
    header('Content-Type: application/json; charset=utf-8');
    header('Date: " . date('r') . "');
    Header( 'Cache-Control: no-cache, must-revalidate');
    Header( 'Pragma: no-cache');
    ?>" . $js;
    }

}
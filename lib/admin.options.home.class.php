<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* Licensed under the MIT (LICENSE.txt) license.
**/

namespace litepubl;

class adminhomeoptions extends tadminmenu {

    public static function i($id = 0) {
        return parent::iteminstance(__class__, $id);
    }

    public function gethead() {
        $result = parent::gethead();

        $result.= '<script type="text/javascript" src="$site.files/js/plugins/filereader.min.js"></script>';
        $result.= '<script type="text/javascript" src="$site.files/js/litepubl/admin/homeuploader.min.js"></script>';

        return $result;
    }

    public function getcontent() {
        $args = new targs();
        $lang = tlocal::admin('options');
        $html = $this->html;
        $home = thomepage::i();
        $tabs = new tabs($this->admintheme);
        $args->image = $home->image;
        $args->smallimage = $home->smallimage;
        $args->parsetags = $home->parsetags;
        $args->showmidle = $home->showmidle;
        $args->midlecat = tposteditor::getcombocategories(array() , $home->midlecat);
        $args->showposts = $home->showposts;
        $args->invertorder = tview::getview($home)->invertorder;
        $args->showpagenator = $home->showpagenator;

        $args->idhome = $home->id;
        $menus = tmenus::i();
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

        $tabs->add($lang->includecats, $html->h4->includehome . $this->admintheme->getcats($home->includecats));

        $tabs->add($lang->excludecats, $html->h4->excludehome . str_replace('category-', 'exclude_category-', $this->admintheme->getcats($home->excludecats)));

        $args->formtitle = $lang->homeform;
        return $html->adminform('<h4><a href="$site.url/admin/menu/edit/{$site.q}id=$idhome">$lang.hometext</a></h4>' . $tabs->get() , $args);
    }

    public function processform() {
        extract($_POST, EXTR_SKIP);
        $home = thomepage::i();
        $home->lock();
        $home->image = $image;
        $home->smallimage = $smallimage;
        $home->parsetags = isset($parsetags);
        $home->showmidle = isset($showmidle);
        $home->midlecat = (int)$midlecat;
        $home->showposts = isset($showposts);
        tview::getview($home)->invertorder = isset($invertorder);
        tview::getview($home)->save();
        $home->includecats = tadminhtml::check2array('category-');
        $home->excludecats = tadminhtml::check2array('exclude_category-');
        $home->showpagenator = isset($showpagenator);
        $home->postschanged();
        $home->unlock();

        $menus = tmenus::i();
        $menus->home = isset($homemenu);
        $menus->save();
    }

    public function request($a) {
        if ($response = parent::request($a)) {
            return $response;
        }

        $name = 'image';
        if (!isset($_FILES[$name])) return;

        $result = array(
            'result' => 'error'
        );

        if (is_uploaded_file($_FILES[$name]['tmp_name']) && !$_FILES[$name]['error'] && strbegin($_FILES[$name]['type'], 'image/') && ($data = file_get_contents($_FILES[$name]['tmp_name']))) {
            $home = thomepage::i();
            $index = 1;
            if (preg_match('/^\/files\/home(\d*+)\.jpg$/', $home->image, $m)) {
                $index = (int)$m[1];
                $filename = litepubl::$paths->files . "home$index.jpg";
                if (file_exists($filename)) {
                    @unlink($filename);
                }

                $filename = litepubl::$paths->files . "home$index.small.jpg";
                if (file_exists($filename)) {
                    @unlink($filename);
                }

                $index++;
            }

            $home->image = "/files/home$index.jpg";
            $home->smallimage = "/files/home$index.small.jpg";

            $filename = litepubl::$paths->files . "home$index.jpg";
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
                        tmediaparser::createthumb($image, $filename, $maxwidth, $maxheight, 80, 'max');
                    } else if (filesize($filename) > 1024 * 1024 * 800) {
                        //no resize just save in low quality
                        @unlink($filename);
                        imagejpeg($image, $filename, 80);
                        @chmod($filename, 0666);
                    }

                    //create small image
                    $smallfile = litepubl::$paths->files . "home$index.small.jpg";
                    if (file_exists($smallfile)) {
                        @unlink($smallfile);
                    }

                    tmediaparser::createthumb($image, $smallfile, 760, 760 / 4 * 3, 80, 'max');
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

        $js = tojson($result);
        return "<?php
    header('Connection: close');
    header('Content-Length: " . strlen($js) . "');
    header('Content-Type: text/javascript; charset=utf-8');
    header('Date: " . date('r') . "');
    Header( 'Cache-Control: no-cache, must-revalidate');
    Header( 'Pragma: no-cache');
    ?>" . $js;
    }

} //class
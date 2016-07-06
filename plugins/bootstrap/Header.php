<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.00
  */

namespace litepubl\plugins\bootstrap;

use litepubl\core\Context;
use litepubl\core\Str;
use litepubl\view\Args;
use litepubl\view\Css;
use litepubl\view\Lang;

class Header extends \litepubl\admin\Menu
{

    public function getHead(): string
    {
        $result = parent::gethead();

        foreach (array(
            'header',
            'logo'
        ) as $name) {
            $css = file_get_contents(__DIR__ . "/resource/css.$name.tml");
            $css = strtr(
                $css, array(
                "\n" => '',
                "\r" => '',
                "'" => '"'
                )
            );

            $result.= "<script type=\"text/javascript\">litepubl.tml.$name = '$css';</script>";
        }

        $result.= '<script type="text/javascript" src="$site.files/js/plugins/filereader.min.js"></script>';
        $result.= '<script type="text/javascript" src="$site.files/plugins/bootstrap/resource/header.min.js"></script>';

        return $result;
    }

    public function getContent(): string
    {
        $tml = file_get_contents(dirname(__file__) . '/resource/content.tml');
        $admin = $this->admintheme;
        $theme = $this->theme;
        $lang = Lang::admin('adminbootstraptheme');
        $lang->addsearch('themeheader', 'editor');
        $args = new Args();
        $args->radio = $theme->getradio('radioplace', 'header', $lang->header, true) . $theme->getradio('radioplace', 'logo', $lang->logo, false);

        return $admin->parseArg($tml, $args);
    }

    public function request(Context $context)
    {
        parent::request($context);
        $response = $context->response;
        if ($response->status != 200) {
            return;
        }

        if (isset($_FILES['header'])) {
            $name = 'header';
        } elseif (isset($_FILES['logo'])) {
            $name = 'logo';
        } else {
            return;
        }

        if (is_uploaded_file($_FILES[$name]['tmp_name']) && !$_FILES[$name]['error'] && Str::begin($_FILES[$name]['type'], 'image/') && ($data = file_get_contents($_FILES[$name]['tmp_name']))) {
            $css = file_get_contents(dirname(__file__) . "/resource/css.$name.tml");
            if ($name == 'logo') {
                $info = @getimagesize($_FILES[$name]['tmp_name']);
                $css = str_replace('%%width%%', $info[0], $css);
            }

            $css = str_replace('%%file%%', 'data:%s;base64,%s', $css);
            $css = sprintf($css, $_FILES[$name]['type'], base64_encode($data));

            $filename = $this->getApp()->paths->files . "js/$name.css";
            file_put_contents($filename, $css);
            @chmod($filename, 0666);

            $merger = Css::i();
            $merger->lock();
            if ($name == 'logo') {
                $merger->deleteFile('default', '/themes/default/css/logo.min.css');
            }

            $merger->add('default', "/files/js/$name.css");
            $merger->unlock();

            //file_put_contents($filename . '.tmp', $data);
            $result = array(
                'result' => 'ok'
            );
        } else {
            $result = array(
                'result' => 'error'
            );
        }

        $response->setJson(Str::toJson($result));
    }
}

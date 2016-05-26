<?php
/**
 * Lite Publisher CMS
 * @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link https://github.com/litepubl\cms
 * @version 6.15
 *
 */

namespace litepubl\plugins\downloaditem;

use litepubl\core\Str;
use litepubl\utils\Http;
use litepubl\updater\Backuper;
use litepubl\post\MediaParser;

class AboutParser
{

    public static function parse(string $url)
    {
        if ($s = Http::get($url)) {
            $backuper = Backuper::i();
            $archtype = $backuper->getArchType($url);
            if ($files = $backuper->unpack($s, $archtype)) {
                list($filename, $content) = each($files);
                if ($about = static ::getAbout($files)) {
                    $item = new Download();
                    $item->type = Str::begin($filename, 'plugins/') ? 'plugin' : 'theme';
                    $item->title = $about['name'];
                    $item->downloadurl = $url;
                    $item->authorurl = $about['url'];
                    $item->authorname = $about['author'];
                    $item->rawcontent = $about['description'];
                    $item->version = $about['version'];
                    $item->tagnames = empty($about['tags']) ? '' : trim($about['tags']);
                    if ($screenshot = static ::getfile($files, 'screenshot.png')) {
                        $media = MediaParser::i();
                        $idscreenshot = $media->uploadThumbnail($about['name'] . '.png', $screenshot);
                        $item->files = array(
                            $idscreenshot
                        );
                    }

                    return $item;
                }
            }
        }
        return false;
    }

    public static function getFile(array & $files, $name)
    {
        foreach ($files as $filename => & $content) {
            if ($name == basename($filename)) {
                return $content;
            }
        }

        return false;
    }

    public static function getAbout(array & $files)
    {
        if ($about_ini = static ::getfile($files, 'about.ini')) {
            $about_ini = trim($about_ini);
            //trim unicode sign
            $about_ini = substr($about_ini, strpos($about_ini, '['));
            $about = parse_ini_string($about_ini, true);
            if (isset($about[$this->getApp()->options->language])) {
                $about['about'] = $about[$this->getApp()->options->language] + $about['about'];
            }
            return $about['about'];
        }
        return false;
    }

}


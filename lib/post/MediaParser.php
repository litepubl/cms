<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.01
  */

namespace litepubl\post;

use litepubl\core\Str;
use litepubl\utils\LinkGenerator;

/**
 * Manage uploaded images
 *
 * @property       string $previewmode
 * @property       int $previewwidth
 * @property       int $previewheight
 * @property       int $maxwidth
 * @property       int $maxheight
 * @property       bool $enablemidle
 * @property       int $midlewidth
 * @property       int $midleheight
 * @property       int $quality_snapshot
 * @property       int $quality_original
 * @property       bool $alwaysresize
 * @property       string $audioext
 * @property       string $videoext
 * @property-write callable $added
 * @property-write callable $onBefore
 * @property-write callable $onResize
 * @property-write callable $noResize
 * @property-write callable $onImage
 * @method         array added(array $params)
 * @method         array onBefore(array $params)
 * @method         array onResize(array $params)
 * @method         array noResize(array $params)
 * @method         array onImage(array $params)
 */

class MediaParser extends \litepubl\core\Events
{

    protected function create()
    {
        parent::create();
        $this->basename = 'mediaparser';
        $this->addEvents('added', 'onbefore', 'onresize', 'noresize', 'onimage');
        $this->data['previewmode'] = 'fixed';
        $this->data['previewwidth'] = 120;
        $this->data['previewheight'] = 120;
        $this->data['maxwidth'] = 1200;
        $this->data['maxheight'] = 900;
        $this->data['enablemidle'] = true;
        $this->data['midlewidth'] = 760;
        $this->data['midleheight'] = 570;

        $this->data['quality_snapshot'] = 85;
        $this->data['quality_original'] = 85;
        $this->data['alwaysresize'] = false;

        $this->data['audioext'] = 'mp3|wav |flac';
        $this->data['videoext'] = 'mp4|ogv|webm';
    }

    public static function fixfilename($filename)
    {
        if (preg_match('/\.(htm|html|js|php|phtml|php\d|htaccess)$/i', $filename)) {
            return $filename . '.txt';
        }

        return $filename;
    }

    public static function linkgen($filename)
    {
        $filename = LinkGenerator::i()->filterfilename($filename);
        return static ::fixfilename($filename);
    }

    public function addlocal($filename)
    {
        return $this->upload(basename($filename), file_get_contents($filename), '', '', '', false);
    }

    public function upload($filename, $content, $title, $description, $keywords, $overwrite)
    {
        if ($title == '') {
            $title = $filename;
        }
        $filename = static ::linkgen($filename);
        $tempfilename = $this->doupload($filename, $content);
        return $this->addfile($filename, $tempfilename, $title, $description, $keywords, $overwrite);
    }

    public function getTempname($parts)
    {
        return 'tmp.' . Str::md5Rand() . '.' . $parts['filename'] . (empty($parts['extension']) ? '' : '.' . $parts['extension']);
    }

    public function uploadfile($filename, $tempfilename, $title, $description, $keywords, $overwrite)
    {
        if ($title == '') {
            $title = $filename;
        }
        if ($description == '') {
            $description = $title;
        }
        $filename = static ::linkgen($filename);
        $parts = pathinfo($filename);
        $newtemp = $this->gettempname($parts);
        if (!move_uploaded_file($tempfilename, $this->getApp()->paths->files . $newtemp)) {
            return $this->error('Error access to uploaded file');
        }

        return $this->add(
            array(
            'filename' => $filename,
            'tempfilename' => $newtemp,
            'title' => $title,
            'description' => $description,
            'keywords' => $keywords,
            'overwrite' => $overwrite
            )
        );
    }

    public static function move_uploaded($filename, $tempfilename, $subdir)
    {
        $filename = static ::linkgen($filename);
        $filename = static ::create_filename($filename, $subdir, false);
        $sep = $subdir == '' ? '' : $subdir . DIRECTORY_SEPARATOR;
        if (!move_uploaded_file($tempfilename, static::getAppInstance()->paths->files . $sep . $filename)) {
            return false;
        }

        return $subdir == '' ? $filename : "$subdir/$filename";
    }

    public static function prepare_filename($filename, $subdir)
    {
        $filename = static ::linkgen($filename);
        $filename = static ::create_filename($filename, $subdir, false);
        return $subdir == '' ? $filename : "$subdir/$filename";
    }

    private function doUpload($filename, &$content)
    {
        $filename = static ::fixfilename($filename);
        $parts = pathinfo($filename);
        $filename = $this->gettempname($parts);
        if (@file_put_contents($this->getApp()->paths->files . $filename, $content)) {
            @chmod($this->getApp()->paths->files . $filename, 0666);
            return $filename;
        }
        return false;
    }

    public static function replace_ext($filename, $ext)
    {
        $parts = pathinfo($filename);
        $result = $parts['filename'] . $ext;
        if (!empty($parts['dirname']) && ($parts['dirname'] != '.')) {
            $result = $parts['dirname'] . DIRECTORY_SEPARATOR . $result;
        }
        return $result;
    }

    public static function makeunique($filename)
    {
        $filename = str_replace('/', DIRECTORY_SEPARATOR, $filename);
        $i = strrpos($filename, DIRECTORY_SEPARATOR);
        $dir = substr($filename, 0, $i + 1);
        return $dir . static ::getunique($dir, substr($filename, $i + 1));
    }

    public static function getUnique($dir, $filename)
    {
        $files = Files::i();
        $subdir = basename(rtrim($dir, '/' . DIRECTORY_SEPARATOR)) . '/';
        if (!$files->exists($subdir . $filename) && !@file_exists($dir . $filename)) {
            return $filename;
        }

        $parts = pathinfo($filename);
        $base = $parts['filename'];
        $ext = empty($parts['extension']) ? '' : ".$parts[extension]";
        for ($i = 2; $i < 10000; $i++) {
            $filename = "$base$i$ext";
            if (!$files->exists($subdir . $filename) && !file_exists($dir . $filename)) {
                return $filename;
            }
        }
        return $filename;
    }

    public static function create_filename($filename, $subdir, $overwrite)
    {
        $dir = static ::getAppInstance()->paths->files . $subdir;
        if (!is_dir($dir)) {
            mkdir($dir, 0777);
            @chmod($dir, 0777);
        }

        if ($subdir) {
            $dir.= DIRECTORY_SEPARATOR;
        }

        if ($overwrite) {
            if (file_exists($dir . $filename)) {
                unlink($dir . $filename);
            }
        } else {
            $filename = static ::getunique($dir, $filename);
        }

        return $filename;
    }

    public function getMediafolder($media)
    {
        if (isset($this->data[$media])) {
            if ($result = $this->data[$media]) {
                return $result;
            }
        }
        return $media;
    }

    public function movetofolder($filename, $tempfilename, $subdir, $overwrite)
    {
        $filename = static ::create_filename($filename, $subdir, $overwrite);
        $sep = $subdir == '' ? '' : $subdir . DIRECTORY_SEPARATOR;
        if (!rename($this->getApp()->paths->files . $tempfilename, $this->getApp()->paths->files . $sep . $filename)) {
            return $this->error(sprintf('Error rename file %s to %s', $tempfilename, $filename));
        }

        return $subdir == '' ? $filename : "$subdir/$filename";
    }

    public function addfile($filename, $tempfilename, $title, $description, $keywords, $overwrite)
    {
        return $this->add(
            array(
            'filename' => $filename,
            'tempfilename' => $tempfilename,
            'title' => $title,
            'description' => $description,
            'keywords' => $keywords,
            'overwrite' => $overwrite
            )
        );
    }

    public function add(array $file)
    {
        if (!isset($file['filename']) || !isset($file['tempfilename'])) {
            $this->error('No file name');
        }

        $files = Files::i();
        $hash = $files->gethash($this->getApp()->paths->files . $file['tempfilename']);
        if (($id = $files->indexof('hash', $hash)) || ($id = $files->getdb('imghashes')->findid('hash = ' . Str::quote($hash)))) {
            @unlink($this->getApp()->paths->files . $file['tempfilename']);
            return $id;
        }

        $item = $this->getInfo($file['tempfilename']);
        $item = array_merge(
            $item, array(
            'filename' => $this->movetofolder($file['filename'], $file['tempfilename'], $this->getmediafolder($item['media']), isset($file['overwrite']) ? $file['overwrite'] : false) ,
            'title' => isset($file['title']) ? $file['title'] : $file['filename'],
            'description' => isset($file['description']) ? $file['description'] : '',
            'keywords' => isset($file['keywords']) ? $file['keywords'] : ''
            )
        );

        $preview = false;
        $midle = false;
        if ($item['media'] == 'image') {
            $srcfilename = $this->getApp()->paths->files . str_replace('/', DIRECTORY_SEPARATOR, $item['filename']);
            $r = $this->onBefore(['item' => $item, 'filename' => $srcfilename]);
            $item = $r['item'];

            $maxwidth = isset($file['maxwidth']) ? $file['maxwidth'] : $this->maxwidth;
            $maxheight = isset($file['maxheight']) ? $file['maxheight'] : $this->maxheight;

            $resize = $this->alwaysresize && ($maxwidth > 0) && ($maxheight > 0);
            if (!$resize) {
                $resize = ($item['width'] > $maxwidth) || ($item['height'] > $maxheight);
            }
            $enablepreview = isset($file['enablepreview']) ? $file['enablepreview'] : (isset($file['ispreview']) ? $file['ispreview'] : $this->previewmode != 'none');
            $enablemidle = isset($file['enablemidle']) ? $file['enablemidle'] : $this->enablemidle;

            if (($resize || $enablepreview || $enablemidle) && ($image = static ::readimage($srcfilename))) {
                $this->onImage(['image' => $image]);

                if ($enablepreview && ($preview = $this->getsnapshot($srcfilename, $image))) {
                    $preview['title'] = $file['title'];

                    if (isset($file['ispreview']) && $file['ispreview']) {
                        $item['filename'] = $preview['filename'];
                        $item['width'] = $preview['width'];
                        $item['height'] = $preview['height'];
                        $item['mime'] = $preview['mime'];
                        @unlink($srcfilename);
                        $resize = false;
                        $preview = false;
                        $enablemidle = false;
                    }
                }

                if ($enablemidle) {
                    $midle = $this->createmidle($srcfilename, $image);
                }

                if ($resize && ($item['width'] > $maxwidth || $item['height'] > $maxheight) && ($sizes = $this->resize($srcfilename, $image, $maxwidth, $maxheight))) {
                    $item['width'] = $sizes['width'];
                    $item['height'] = $sizes['height'];

                    // after resize only jpg format
                    if (!Str::end($srcfilename, '.jpg')) {
                        $fixfilename = static ::replace_ext($srcfilename, '.jpg');
                        $fixfilename = static ::makeunique($fixfilename);
                        $item['filename'] = str_replace(DIRECTORY_SEPARATOR, '/', substr($fixfilename, strlen($this->getApp()->paths->files)));

                        rename($srcfilename, $fixfilename);
                        @chmod($fixfilename, 0666);
                    }
                } else {
                    $this->noResize(['image' => $image, 'filename' => $srcfilename]);
                }

                imagedestroy($image);
            }
        }

        $id = $files->additem($item);
        if ($hash != $files->getvalue($id, 'hash')) {
            $files->getdb('imghashes')->insert(
                array(
                'id' => $id,
                'hash' => $hash
                )
            );
        }

        if ($preview) {
            $preview['parent'] = $id;
            $idpreview = $files->additem($preview);
            $files->setvalue($id, 'preview', $idpreview);
        }

        if ($midle) {
            $midle['parent'] = $id;
            if ($preview) {
                $midle['preview'] = $idpreview;
            }
            $idmidle = $files->additem($midle);
            $files->setvalue($id, 'midle', $idmidle);
        }

        $this->added(['id' => $id]);
        return $id;
    }

    public function uploadthumbnail($filename, $content)
    {
        if (!preg_match('/\.(jpg|jpeg|gif|png|bmp)$/i', $filename)) {
            return false;
        }

        $linkgen = LinkGenerator::i();
        $filename = $linkgen->filterfilename($filename);
        $tempfilename = $this->doupload($filename, $content);

        return $this->add(
            array(
            'filename' => $filename,
            'tempfilename' => $tempfilename,
            'enabledpreview' => false
            )
        );
    }

    //$filename must be specefied before such as  thumb/img004893.jpg
    public function uploadthumb($filename, &$content)
    {
        $hash = trim(base64_encode(md5($content, true)), '=');
        $files = Files::i();
        if (($id = $files->indexof('hash', $hash)) || ($id = $files->getdb('imghashes')->findid('hash = ' . Str::quote($hash)))) {
            return $id;
        }

        if ($image = imagecreatefromstring($content)) {
            if (!Str::begin($filename, $this->getApp()->paths->files)) {
                $filename = $this->getApp()->paths->files . ltrim($filename, '\/');
            }
            $destfilename = static ::replace_ext($filename, '.jpg');
            $destfilename = static ::makeunique($destfilename);
            if ($size = static ::createthumb($image, $destfilename, $this->previewwidth, $this->previewheight, $this->quality_snapshot, $this->previewmode)) {
                $item = $this->getdefaultvalues(str_replace(DIRECTORY_SEPARATOR, '/', substr($destfilename, strlen($this->getApp()->paths->files))));
                $item['media'] = 'image';
                //jpeg always for thumbnails
                $item['mime'] = 'image/jpeg';
                $item['width'] = $size['width'];
                $item['height'] = $size['height'];

                $id = $files->additem($item);
                if ($hash != $files->getvalue($id, 'hash')) {
                    $files->getdb('imghashes')->insert(
                        array(
                        'id' => $id,
                        'hash' => $hash
                        )
                    );
                }

                $this->added(['id' => $id]);
                return $id;
            }
        }
        return false;
    }

    public function getDefaultvalues($filename)
    {
        return array(
            'parent' => 0,
            'midle' => 0,
            'preview' => 0,
            'media' => 'bin',
            'mime' => 'application/octet-stream',
            'filename' => $filename,
            'size' => 0,
            'idperm' => 0,
            'height' => 0,
            'width' => 0,
            'preview' => 0,
            'title' => '',
            'description' => '',
            'keywords' => ''
        );
    }

    public function getInfo($filename)
    {
        $realfile = $this->getApp()->paths->files . str_replace('/', DIRECTORY_SEPARATOR, $filename);
        $result = $this->getdefaultvalues($filename);
        if (preg_match("/\\.($this->videoext)\$/", $filename, $m)) {
            $ext = $m[1];
            $mime = array(
                'mp4' => 'video/mp4',
                'mpe' => 'video/mpeg',
                'mpeg' => 'video/mpeg',
                'mpg' => 'video/mpeg',
                'avi' => 'video/x-msvideo',
                'mov' => 'video/quicktime',
                'ogv' => 'video/ogg',
                'webm' => 'video/webm',
                'flv' => 'video/x-flv',
                'f4v' => 'video/mp4',
                'f4p' => 'video/mp4',
            );

            if (isset($mime[$ext])) {
                $result['mime'] = $mime[$ext];
            }
            $result['media'] = 'video';
            return $result;
        }

        if ($info = @getimagesize($realfile)) {
            $result['mime'] = $info['mime'];
            $result['media'] = 'application/x-shockwave-flash' == $info['mime'] ? 'flash' : 'image';
            $result['width'] = $info[0];
            $result['height'] = $info[1];
            return $result;
        }

        if (preg_match("/\\.($this->audioext)\$/", $filename)) {
            $mime = array(
                'mp3' => 'audio/mpeg',
                'wav' => 'audio/x-wav',
                'flac' => 'audio/ogg',
                'f4a' => 'audio/mp4',
                'f4b' => 'audio/mp4',
            );

            $result['mime'] = $mime[strtolower(substr($filename, -3)) ];
            $result['media'] = 'audio';
            return $result;
        }

        if (Str::end($filename, '.txt')) {
            $result['mime'] = 'text/plain';
            $result['media'] = 'text';
            return $result;
        }

        if (Str::end($filename, '.swf')) {
            $result['media'] = 'flash';
            $result['mime'] = 'application/x-shockwave-flash';

            include_once $this->getApp()->paths->libinclude . 'getid3.php';
            include_once $this->getApp()->paths->libinclude . 'getid3.lib.php';
            include_once $this->getApp()->paths->libinclude . 'module.audio-video.swf.php';

            $getID3 = new \getID3;
            $getID3->option_md5_data = true;
            $getID3->option_md5_data_source = true;
            $getID3->encoding = 'UTF-8';
            $getID3->openfile($realfile);
            $swf = new \getid3_swf($getID3);
            $swf->analyze();
            fclose($getID3->fp);
            $info = $getID3->info;

            if (!isset($info['error'])) {
                $result['width'] = (int)round($info['swf']['header']['frame_width'] / 20);
                $result['height'] = (int)round($info['swf']['header']['frame_height'] / 20);
                return $result;
            }
        }

        return $result;

    }

    public static function readimage($srcfilename)
    {
        if (!file_exists($srcfilename)) {
            return false;
        }

        if (!($info = @getimagesize($srcfilename))) {
            return false;
        }

        if (!$info[0] || !$info[1]) {
            return false;
        }

        switch ($info[2]) {
        case 1:
            return @imagecreatefromgif($srcfilename);

        case 2:
            return @imagecreatefromjpeg($srcfilename);

        case 3:
            return @imagecreatefrompng($srcfilename);
                /*
                4 IMAGETYPE_SWF
                5 IMAGETYPE_PSD
                6 IMAGETYPE_BMP
                7 IMAGETYPE_TIFF_II (intel byte order)
                8 IMAGETYPE_TIFF_MM (motorola byte order)
                9 IMAGETYPE_JPC
                10 IMAGETYPE_JP2
                11 IMAGETYPE_JPX
                12  IMAGETYPE_JB2
                13 IMAGETYPE_SWC
                14 IMAGETYPE_IFF
                */
        case 15:
            return @imagecreatefromwbmp($srcfilename);

        case 16:
            return @imagecreatefromxbm($srcfilename);
        }
        return false;
    }

    public static function createsnapshot($srcfilename, $destfilename, $x, $y, $mode)
    {
        if (!($source = static ::readimage($srcfilename))) {
            return false;
        }

        $r = static ::createthumb($source, $destfilename, $x, $y, 85, $mode);
        imagedestroy($source);
        return $r;
    }

    public static function createthumb($source, $destfilename, $x, $y, $quality, $mode)
    {
        if ($result = static ::scale($source, $x, $y, $mode)) {
            imagejpeg($result['image'], $destfilename, $quality);
            imagedestroy($result['image']);
            @chmod($destfilename, 0666);
            unset($result['image']);
            return $result;
        }

        return false;
    }

    public static function scale($source, $x, $y, $mode)
    {
        if (!$source) {
            return false;
        }

        $sourcex = imagesx($source);
        $sourcey = imagesy($source);
        if (!$x) {
            $x = $y;
        }
        if (!$y) {
            $y = $x;
        }
        if (($x >= $sourcex) && ($y >= $sourcey)) {
            return false;
        }

        switch ($mode) {
        case 'fixed':
            $ratio = $x / $y;
            //clip source size
            if ($sourcex / $sourcey > $ratio) {
                $sourcex = (int)round($sourcey * $ratio);
            } else {
                $sourcey = (int)round($sourcex / $ratio);
            }
            break;


        case 'max':
        case 'min':
            $ratio = $sourcex / $sourcey;
            if ($mode == 'max' ? $x / $y > $ratio : $x / $y <= $ratio) {
                $x = (int)round($y * $ratio);
            } else {
                $y = (int)round($x / $ratio);
            }
            break;


        default:
            throw new \Exception("Unknow thumbnail options $mode");
        }

        $dest = imagecreatetruecolor($x, $y);
        imagecopyresampled($dest, $source, 0, 0, 0, 0, $x, $y, $sourcex, $sourcey);

        return array(
            'width' => $x,
            'height' => $y,
            'image' => $dest,
        );
    }

    public function getSnapshot($srcfilename, $image)
    {
        $destfilename = static ::replace_ext($srcfilename, '.preview.jpg');
        $destfilename = static ::makeunique($destfilename);
        if ($size = static ::createthumb($image, $destfilename, $this->previewwidth, $this->previewheight, $this->quality_snapshot, $this->previewmode)) {
            $result = $this->getdefaultvalues(str_replace(DIRECTORY_SEPARATOR, '/', substr($destfilename, strlen($this->getApp()->paths->files))));
            $result['media'] = 'image';
            $result['mime'] = 'image/jpeg';
            $result['width'] = $size['width'];
            $result['height'] = $size['height'];
            return $result;
        }

        return false;
    }

    public function createmidle($srcfilename, $image)
    {
        if (imagesx($image) <= $this->midlewidth && imagesy($image) <= $this->midleheight) {
            return false;
        }

        $destfilename = static ::replace_ext($srcfilename, '.midle.jpg');
        $destfilename = static ::makeunique($destfilename);

        if ($sizes = $this->resize($destfilename, $image, $this->midlewidth, $this->midleheight)) {
            $result = $this->getdefaultvalues(str_replace(DIRECTORY_SEPARATOR, '/', substr($destfilename, strlen($this->getApp()->paths->files))));
            $result['media'] = 'image';
            $result['mime'] = 'image/jpeg';
            $result['width'] = $sizes['width'];
            $result['height'] = $sizes['height'];
            return $result;
        }

        return false;
    }

    public function resize($filename, $image, $x, $y)
    {
        $sourcex = imagesx($image);
        $sourcey = imagesy($image);
        if (!$x || !$sourcex || !$sourcey) {
            return false;
        }

        $ratio = $sourcex / $sourcey;
        if (!$y) {
            $y = $x / $ratio;
        } elseif ($x / $y > $ratio) {
            $x = $y * $ratio;
        } else {
            $y = $x / $ratio;
        }

        $x = (int)round($x);
        $y = (int)round($y);

        $dest = imagecreatetruecolor($x, $y);
        imagecopyresampled($dest, $image, 0, 0, 0, 0, $x, $y, $sourcex, $sourcey);
        $this->onResize(['image' => $dest]);

        imagejpeg($dest, $filename, $this->quality_original);
        imagedestroy($dest);
        @chmod($filename, 0666);

        return array(
            'width' => $x,
            'height' => $y,
        );
    }

    private function getAudioinfo($filename)
    {
        return false;
        /*
        if (!class_exists('getID3')) {
        return false;
        }
        
        
        $realfile =  $this->getApp()->paths->files . str_replace('/', DIRECTORY_SEPARATOR, $filename);
        
        // Initialize getID3 engine
        $getID3 = new \getID3;
        $getID3->option_md5_data        = true;
        $getID3->option_md5_data_source = true;
        $getID3->encoding               = 'UTF-8';
        
        $info = $getID3->analyze($realfile);
        if (isset($info['error'])) {
        return false;
        }
        
        
        
        $result = array (
        'bitrate'  => @$info['audio']['bitrate'],
        'samplingrate'  => @$info['audio']['sample_rate'],
        'channels'  => @$info['audio']['channels'],
        'duration'  => @$info['playtime_seconds'],
        );
        //$result['comments']        = @$info['comments'];
        return $result;
        */
    }

    public function getVideopreview($filename)
    {
        return 0;
    }
}

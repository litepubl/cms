<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.03
  */

namespace litepubl\updater;

use litepubl\core\Plugins;
use litepubl\core\Str;
use litepubl\utils\Filer;
use litepubl\view\Lang;

/**
 * Integrated class for archives and filers
 *
 * @property       string $ftproot
 * @property       string $filertype
 * @property-write callable $onUploaded
 * @method         array onUploaded(array $params)
 */

class Backuper extends \litepubl\core\Events
{
    public $archtype;
    public $result;
    public $tar;
    public $zip;
    private $__filer;
    private $existingfolders;
    private $lastdir;
    private $hasdata;

    protected function create()
    {
        parent::create();
        $this->basename = 'backuper';
        $this->addEvents('onuploaded');
        $this->data['ftproot'] = '';
        $this->data['filertype'] = 'ftp';
        $this->__filer = false;
        $this->tar = false;
        $this->zip = false;
        $this->archtype = 'zip';
        $this->lastdir = '';
    }

    public function __destruct()
    {
        unset($this->__filer, $this->tar, $this->zip);
        parent::__destruct();
    }

    public function newTar()
    {
        include_once $this->getApp()->paths->lib . 'include/tar.class.php';
        return new \tar;
    }

    public function unknown_archive()
    {
        $this->error('Unknown archive type ' . $this->archtype);
    }

    public function load()
    {
        $result = parent::load();
        if ($this->filertype == 'auto') {
            $this->filertype = static ::getprefered();
        }

        return $result;
    }

    public static function getPrefered()
    {
        $datafile = static::getAppInstance()->paths->data . 'storage' . static::getAppInstance()->storage->ext;
        if (file_exists($datafile)) {
            $dataowner = fileowner($datafile);
            $libowner = fileowner(__DIR__);

            if (($libowner !== false) && ($libowner === $dataowner)) {
                return 'file';
            }
        }
        //if (extension_loaded('ssh2') && function_exists('stream_get_contents') ) return 'ssh2';
        if (extension_loaded('ftp')) {
            return 'ftp';
        }

        if (extension_loaded('sockets') || function_exists('fsockopen')) {
            return 'socket';
        }

        return false;
    }

    public function getFiler()
    {
        if ($this->__filer) {
            return $this->__filer;
        }

        switch ($this->filertype) {
        case 'ftp':
            $result = new Ftp();
            break;


        case 'ssh2':
            $result = new Ssh2();
            break;


        case 'socket':
            $result = new FtpSocket();
            break;


        case 'file':
            $result = Local::i();
            break;


        default:
            $this->filertype = 'file';
            $result = Local::i();
            $result->chmod_file = 0666;
            $result->chmod_dir = 0777;
            break;
        }

        $this->__filer = $result;
        return $result;
    }

    public function connect($host, $login, $password)
    {
        if ($this->filer->connected) {
            return true;
        }

        if ($this->filer->connect($host, $login, $password)) {
            if (($this->filertype == 'ftp') || ($this->filertype == 'socket')) {
                if (($root = $this->filer->getroot($this->ftproot)) && ($root != $this->ftproot)) {
                    $this->ftproot = $root;
                    $this->save();
                }
            }

            return true;
        }

        return false;
    }

    public function createArchive(): bool
    {
        if (!$this->filer->connected) {
            $this->error('Filer not connected');
        }

        switch ($this->archtype) {
        case 'tar':
            $this->tar = $this->newTar();
            break;


        case 'zip':
        case 'unzip':
            $this->zip = new \ZipArchive();
            $filename = $this->getApp()->paths->backup . Str::md5Rand() . '.zip';
            if ($this->zip->open($filename, \ZipArchive::CREATE) === false) {
                        $this->eror("Error create zip archive $filename");
            }
            break;


        default:
            $this->unknown_archive();
            return false;
        }

        return true;
    }

    public function saveArchive(): string
    {
        switch ($this->archtype) {
        case 'tar':
            $result = $this->tar->savetostring(true);
            $this->tar = false;
            return $result;

        case 'zip':
            $filename = $this->zip->filename;
            $this->zip->close();
            $this->zip = false;
            $result = file_get_contents($filename);
            @unlink($filename);
            return $result;

        default:
            $this->unknown_archive();
        }

        return '';
    }

    private function addfile($filename, $content, $perm)
    {
        switch ($this->archtype) {
        case 'tar':
            return $this->tar->addstring($content, $filename, $perm);

        case 'zip':
            return $this->zip->addFromString($filename, $content);

        default:
            $this->unknown_archive();
        }
    }

    private function adddir($dir, $perm)
    {
        switch ($this->archtype) {
        case 'tar':
            return $this->tar->adddir($dir, $perm);

        case 'zip':
            return $this->zip->addEmptyDir($dir);

        default:
            $this->unknown_archive();
        }
    }

    private function readdir($path)
    {
        $path = rtrim($path, '/');
        $filer = $this->getfiler();
        if ($list = $filer->getdir($path)) {
            $this->adddir($path, $filer->getchmod($path));
            $path.= '/';
            $hasindex = false;
            foreach ($list as $name => $item) {
                $filename = $path . $name;
                if ($item['isdir']) {
                    $this->readdir($filename);
                } else {
                    if (preg_match('/(\.bak\.php$)|(\.lok$)/', $name)) {
                        continue;
                    }

                    $this->addfile($filename, $filer->getfile($filename), $item['mode']);
                    if (!$hasindex) {
                        $hasindex = ($name == 'index.php') || ($name == 'index.htm');
                    }
                }
            }
            if (!$hasindex) {
                $this->addfile($path . 'index.htm', '', $filer->chmod_file);
            }
        }
    }

    private function readdata($path)
    {
        $path = rtrim($path, DIRECTORY_SEPARATOR);
        $filer = Local::i();
        if ($list = $filer->getdir($path)) {
            $dir = 'storage/data/' . str_replace(DIRECTORY_SEPARATOR, '/', substr($path, strlen($this->getApp()->paths->data)));
            $this->adddir($dir, $filer->getchmod($path));
            $dir = rtrim($dir, '/') . '/';
            $hasindex = false;
            $path.= DIRECTORY_SEPARATOR;
            $ignoredir = [
                'languages',
                'logs',
                'themes'
            ];
            foreach ($list as $name => $item) {
                $filename = $path . $name;
                if (is_dir($filename)) {
                    if (($dir == 'storage/data/') && in_array($name, $ignoredir)) {
                        $this->adddir($dir . $name . '/', 0777);
                        $this->addfile($dir . $name . '/index.htm', '', 0666);
                    } else {
                        $this->readdata($filename);
                    }
                } else {
                    if (preg_match('/(\.bak\.php$)|(\.lok$)|(\.log$)/', $name)) {
                        continue;
                    }

                    $this->addfile($dir . $name, file_get_contents($filename), $item['mode']);
                    if (!$hasindex) {
                        $hasindex = ($name == 'index.php') || ($name == 'index.htm');
                    }
                }
            }
            if (!$hasindex) {
                $this->addfile($dir . 'index.htm', '', $filer->chmod_file);
            }
        }
    }

    private function readhome()
    {
        $filer = $this->filer;
        $this->chdir(rtrim($this->getApp()->paths->home, DIRECTORY_SEPARATOR));
        if ($list = $filer->getdir('.')) {
            foreach ($list as $name => $item) {
                if ($item['isdir']) {
                    continue;
                }

                $this->addfile($name, $filer->getfile($name), $item['mode']);
            }
        }
    }

    public function chDir($dir)
    {
        if ($dir === $this->lastdir) {
            return;
        }

        $this->lastdir = $dir;

        if (!($this->__filer instanceof Local)) {
            $dir = str_replace('\\', '/', $dir);
            if ('/' != DIRECTORY_SEPARATOR) {
                $dir = str_replace(DIRECTORY_SEPARATOR, '/', $dir);
            }
            $dir = rtrim($dir, '/');
            $root = rtrim($this->ftproot, '/');
            if (Str::begin($dir, $root)) {
                $dir = substr($dir, strlen($root));
            }

            $this->filer->chdir($dir);
        } else {
            $this->filer->chdir($dir);
        }
    }

    public function setDir($dir)
    {
        $dir = trim($dir, '/');
        if ($i = strpos($dir, '/')) {
            $dir = substr($dir, 0, $i);
        }

        $paths = $this->getApp()->paths;
        if (isset($paths->$dir)) {
            $this->chDir(dirname(rtrim($paths->$dir, DIRECTORY_SEPARATOR)));
        } else {
                $this->chDir('/');
                $this->chDir(rtrim($paths->home, DIRECTORY_SEPARATOR));
        }
    }

    public function getPartial(bool $plugins, bool $theme, bool $lib): string
    {
        set_time_limit(300);

        $this->createArchive();
        $this->addfile('dump.sql', $this->getdump(), $this->filer->chmod_file);

        $this->setdir('storage');
        $this->readdir('storage/data');

        if ($lib) {
            $this->setdir('lib');
            $this->readdir('lib');
            $this->setdir('js');
            $this->readdir('js');

            $this->readhome();
        }

        if ($theme) {
            $this->setdir('themes');
            $schemes = Schemes::i();
            $names = [];
            foreach ($schemes->items as $id => $item) {
                if (in_array($item['themename'], $names)) {
                    continue;
                }

                $names[] = $item['themename'];
                $this->readdir('themes/' . $item['themename']);
            }
        }

        if ($plugins) {
            $this->setdir('plugins');
            $plugins = Plugins::i();
            foreach ($plugins->items as $name => $item) {
                if (@is_dir($this->getApp()->paths->plugins . $name)) {
                    $this->readdir('plugins/' . $name);
                }
            }
        }

        return $this->saveArchive();
    }

    public function getFull()
    {
        set_time_limit(300);
        $this->createArchive();
        $this->addfile('dump.sql', $this->getdump(), $this->filer->chmod_file);

        //$this->readdata( $this->getApp()->paths->data);
        $this->setdir('storage');
        $this->readdir('storage/data');

        $this->setdir('lib');
        $this->readdir('lib');
        $this->setdir('js');
        $this->readdir('js');
        $this->readhome();

        $this->setdir('plugins');
        $this->readdir('plugins');

        $this->setdir('themes');
        $this->readdir('themes');

        return $this->saveArchive();
    }

    public function getDump()
    {
        return $this->getApp()->db->man->export();
    }

    public function setDump(&$dump)
    {
        return $this->getApp()->db->man->import($dump);
    }

    public function getTempName()
    {
        return $this->getApp()->paths->backup . Str::md5Rand() . '.zip';
    }

    public function uploaddump($s, $filename)
    {
        if (Str::end($filename, '.zip')) {
            $tempfile = $this->getTempName();
            file_put_contents($tempfile, $s);
            @chmod($tempfile, 0666);

            $zip = new \ZipArchive();
            if ($zip->open($tempfile) === true) {
                for ($i = 0; $i < $zip->numFiles; $i++) {
                    $filename = $zip->getNameIndex($i);
                    if (Str::end($filename, '.sql')) {
                        $s = $zip->getFromIndex($i);
                        break;
                    }
                }

                $zip->close();
                unset($zip);
            }

            unlink($tempfile);
        } elseif (Str::end($filename, '.tar.gz') || Str::end($filename, '.tar')) {
            $tar = $this->newTar();
            $tar->loadfromstring($s);
            foreach ($tar->files as $item) {
                if (!Str::end($item['name'], '.sql')) {
                    $s = $item['file'];
                    break;
                }
            }
            unset($tar);
        } else {
            if ($s[0] == chr(31) && $s[1] == chr(139) && $s[2] == chr(8)) {
                $s = gzinflate(substr($s, 10, -4));
            }
        }

        return $this->setdump($s);
    }

    private function writeData($filename, $content, $mode)
    {
        if (Str::end($filename, '/.htaccess')) {
            return true;
        }

        if (Str::end($filename, '/index.htm')) {
            return true;
        }

        $this->hasdata = true;
        $filename = substr($filename, strlen('storage/data/'));
        $filename = str_replace('/', DIRECTORY_SEPARATOR, $filename);
        $filename = $this->getApp()->paths->storage . 'newdata' . DIRECTORY_SEPARATOR . $filename;
        Filer::forcedir(dirname($filename));
        if (file_put_contents($filename, $content) === false) {
            return false;
        }

        @chmod($filename, $mode);
        return true;
    }

    public function uploadfile($filename, $content, $mode)
    {
        $filename = ltrim($filename, '/');
        if ($filename == 'dump.sql') {
            $this->setdump($content);
            return true;
        }

        $mode = $this->filer->getmode($mode);

        //ignore home files
        if (!strpos($filename, '/')) {
            return true;
        }

        //spec rule for storage folder
        if (Str::begin($filename, 'storage/')) {
            if (Str::begin($filename, 'storage/data/')) {
                return $this->writeData($filename, $content, $mode);
            }

            return true;
        }

        $dir = rtrim(dirname($filename), '/');
        $this->setDir($dir);
        if (!isset($this->existingfolders[$dir])) {
            $this->filer->forceDir($dir);
            $this->existingfolders[$dir] = true;
        }

        if ($this->filer->putContent($filename, $content) === false) {
            return false;
        }

        $this->filer->chmod($filename, $mode);
        return true;
    }

    public function uploadarch($filename, $archtype)
    {
        switch ($archtype) {
        case 'tar':
            return $this->uploadtar($filename);

        case 'zip':
        case 'unzip':
            return $this->uploadzip($filename);
        }
    }

    public function upload($content, $archtype)
    {
        set_time_limit(300);
        $this->archtype = $archtype;
        $this->hasdata = false;
        $this->existingfolders = [];
        $this->createArchive();

        switch ($archtype) {
        case 'tar':
            $this->tar->loadFromString(is_object($content) ? $content->file : $content);
            if (!is_array($this->tar->files)) {
                $this->tar = false;
                return $this->errorarch();
            }

            if (is_object($content)) {
                        $content->file = null;
            }

            $content = null;
            $path_checked = false;
            $path_root = false;

            foreach ($this->tar->files as $item) {
                if (!$path_checked) {
                    $path_checked = true;
                    $path_root = $this->get_path_root($item['name']);
                }

                $name = $path_root ? ltrim(substr(ltrim($item['name'], '/'), strlen($path_root)), '/') : $item['name'];
                if (!$this->uploadFile($name, $item['file'], $item['mode'])) {
                    return $this->errorwrite($name);
                }
            }

            $this->onuploaded([]);
            $this->tar = false;
            break;


        case 'unzip':
        case 'zip':
            $mode = $this->filer->chmod_file;
            $tempfile = $this->getTempName();
            file_put_contents($tempfile, is_object($content) ? $content->file : $content);
            @chmod($tempfile, 0666);

            if ($this->zip->open($tempfile) !== true) {
                unlink($tempfile);
                $this->zip = false;
                return $this->errorarch();
            }

            if (is_object($content)) {
                        $content->file = null;
            }

            $content = null;

            $path_checked = false;
            $path_root = false;

            for ($i = 0; $i < $this->zip->numFiles; $i++) {
                if ($s = $this->zip->getFromIndex($i)) {
                    $filename = $this->zip->getNameIndex($i);

                    if (!$path_checked) {
                        $path_checked = true;
                        $path_root = $this->get_path_root($filename);
                    }

                    $filename = $path_root ? ltrim(substr(ltrim($filename, '/'), strlen($path_root)), '/') : $filename;
                    if (!$this->uploadfile($filename, $s, $mode)) {
                        $this->zip->close();
                        unlink($tempfile);
                        return $this->errorwrite($item->Path . $item->Name);
                    }
                }
            }

            $this->onuploaded([]);
            $this->zip->close();
            $this->zip = false;
            unlink($tempfile);
            break;


        default:
            $this->unknown_archive();
        }

        $this->existingfolders = false;
        if ($this->hasdata) {
            $this->renamedata();
        }

        return true;
    }

    //define if first dir is versioned
    public function get_path_root($path)
    {
        $list = explode('/', trim($path, '/'));
        if (preg_match('/\d*+\.\d*+$/', $list[0])) {
            return $list[0];
        }

        return false;
    }

    public function uploadtar($filename)
    {
        if (file_exists($filename)) {
            return $this->upload(file_get_contents($filename), 'tar');
        }

        return false;
    }

    public function uploadzip($filename)
    {
        if (!file_exists($filename)) {
            return false;
        }

        set_time_limit(300);
        $this->archtype = 'unzip';
        $this->hasdata = false;
        $this->existingfolders = [];

        $mode = $this->filer->chmod_file;
        $path_checked = false;
        $path_root = false;

        $zip = new \ZipArchive();
        if ($zip->open($filename) !== true) {
            return $this->errorarch();
        }

        for ($i = 0; $i < $zip->numFiles; $i++) {
            if ($s = $zip->getFromIndex($i)) {
                $filename = $zip->getNameIndex($i);

                if (!$path_checked) {
                    $path_checked = true;
                    $path_root = $this->get_path_root($filename);
                }

                $filename = $path_root ? ltrim(substr(ltrim($filename, '/'), strlen($path_root)), '/') : $filename;
                if (!$this->uploadfile($filename, $s, $mode)) {
                    $zip->close();
                    return $this->errorwrite($filename);
                }
            }
        }

        $zip->close();
        $this->onuploaded([]);
        $this->existingfolders = false;
        if ($this->hasdata) {
            $this->renamedata();
        }

        return true;
    }

    private function renamedata()
    {
        if (!is_dir($this->getApp()->paths->backup)) {
            mkdir($this->getApp()->paths->backup, 0777);
            @chmod($this->getApp()->paths->backup, 0777);
        }
        $backup = $this->getApp()->paths->backup . 'data-' . time();
        $data = rtrim($this->getApp()->paths->data, DIRECTORY_SEPARATOR);
        rename($data, $backup);
        rename($this->getApp()->paths->storage . 'newdata', $data);
        Filer::delete($backup, true, true);
    }

    private function errorwrite($filename)
    {
        $lang = Lang::admin('service');
        $this->result = sprintf($lang->errorwritefile, $filename);
        return false;
    }

    private function errorarch()
    {
        $lang = Lang::admin('service');
        $this->result = $lang->errorarchive;
        return false;
    }

    public function unpack($content, $archtype)
    {
        $result = [];
        switch ($archtype) {
        case 'tar':
            $tar = $this->newTar();
            $tar->loadfromstring($content);
            if (!is_array($tar->files)) {
                unset($tar);
                return $this->errorarch();
            }

            foreach ($tar->files as $item) {
                $result[$item['name']] = $item['file'];
            }
            unset($tar);
            break;


        case 'unzip':
        case 'zip':
            $filename = $this->getApp()->paths->backup . Str::md5Rand() . '.zip';
            file_put_contents($filename, $content);
            @chmod($filename, 0666);
            $content = '';

            $zip = new \ZipArchive();
            if ($zip->open($filename) === true) {
                for ($i = 0; $i < $zip->numFiles; $i++) {
                    if ($s = $zip->getFromIndex($i)) {
                        $result[$zip->getNameIndex($i) ] = $s;
                    }
                }

                $zip->close();
            }

            @unlink($filename);
            break;


        default:
            $this->unknown_archive();
        }

        return $result;
    }

    public function createfullbackup()
    {
        return $this->_savebackup($this->getpartial(true, true, true));
    }

    public function createbackup()
    {
        /*
        $filer = $this->__filer;
        if (!$filer || ! ($filer instanceof Local)) {
        $this->__filer = Local::i();
        }
        */
        $result = $this->_savebackup($this->getpartial(false, false, false));
        //$this->__filer = $filer;
        return $result;
    }

    public function getFilename($ext)
    {
        $filename = $this->getApp()->paths->backup . $this->getApp()->site->domain . date('-Y-m-d');
        $result = $filename . $ext;
        $i = 2;
        while (file_exists($result) && ($i < 100)) {
            $result = $filename . '_' . $i++ . $ext;
        }
        return $result;
    }

    private function _savebackup($s)
    {
        $filename = $this->getfilename($this->archtype == 'zip' ? '.zip' : '.tar.gz');
        file_put_contents($filename, $s);
        @chmod($filename, 0666);
        return $filename;
    }

    public function getShellfilename()
    {
        $filename = $this->getfilename('.tar.gz');
        return substr(substr($filename, 0, strlen($filename) - strlen('.tar.gz')), strrpos($filename, DIRECTORY_SEPARATOR) + 1);
    }

    public function createshellbackup()
    {
        $dbconfig = $this->getApp()->options->dbconfig;
        $cmd = [];
        $cmd[] = 'cd ' . $this->getApp()->paths->backup;
        $cmd[] = sprintf('mysqldump -u%s -p%s %s>dump.sql', $dbconfig['login'], str_rot13(base64_decode($dbconfig['password'])), $dbconfig['dbname']);
        $filename = $this->getshellfilename();
        $cmd[] = sprintf('tar --exclude="*.bak.php" --exclude="*.lok" --exclude="*.log" -cf %s.tar ../../storage/data/* dump.sql', $filename);
        $cmd[] = 'rm dump.sql';
        $cmd[] = "gzip $filename.tar";
        $cmd[] = "rm $filename.tar";
        $cmd[] = "chmod 0666 $filename.tar.gz";
        exec(implode("\n", $cmd), $r);
        //echo implode("\n", $r);
        return $this->getApp()->paths->backup . $filename . '.tar.gz';
    }

    public function createshellfullbackup()
    {
        $dbconfig = $this->getApp()->options->dbconfig;
        $cmd = [];
        $cmd[] = 'cd ' . $this->getApp()->paths->backup;
        $cmd[] = sprintf('mysqldump -u%s -p%s %s>dump.sql', $dbconfig['login'], str_rot13(base64_decode($dbconfig['password'])), $dbconfig['dbname']);
        $filename = $this->getshellfilename();
        $cmd[] = sprintf('tar --exclude="*.bak.php" --exclude="*.lok" --exclude="*.log" -cf %s.tar ../../storage/data/* dump.sql ../../lib/* ../../plugins/* ../../themes/* ../../js/* ../../index.php "../../.htaccess"', $filename);
        $cmd[] = 'rm dump.sql';
        $cmd[] = "gzip $filename.tar";
        $cmd[] = "rm $filename.tar";
        $cmd[] = "chmod 0666 $filename.tar.gz";
        exec(implode("\n", $cmd), $r);
        //echo implode("\n", $r);
        return $this->getApp()->paths->backup . $filename . '.tar.gz';
    }

    public function createshellfilesbackup()
    {
        $cmd = [];
        $cmd[] = 'cd ' . $this->getApp()->paths->backup;
        $filename = 'files_' . $this->getApp()->site->domain . date('-Y-m-d');
        $cmd[] = sprintf('tar --exclude="*.bak.php" --exclude="*.lok" --exclude="*.log" -cf %s.tar ../../files/*', $filename);
        $cmd[] = "gzip $filename.tar";
        $cmd[] = "rm $filename.tar";
        $cmd[] = "chmod 0666 $filename.tar.gz";
        exec(implode("\n", $cmd), $r);
        //echo implode("\n", $r);
        return $this->getApp()->paths->backup . $filename . '.tar.gz';
    }

    public function test()
    {
        if (!@file_put_contents($this->getApp()->paths->data . 'index.htm', ' ')) {
            return false;
        }

        if (!$this->filer->connected) {
            return false;
        }

        $this->setdir('lib');
        return $this->uploadfile('lib/index.htm', ' ', $this->filer->chmod_file);
    }

    public function getFiletype()
    {
        if ($this->archtype == 'zip') {
            return '.zip';
        }

        if ($this->archtype == 'tar') {
            return '.tar.gz';
        }

        return false;
    }

    public function getArchtype($filename)
    {
        if (Str::end($filename, '.zip')) {
            return 'zip';
        }

        if (Str::end($filename, '.tar.gz') || Str::end($filename, '.tar')) {
            return 'tar';
        }

        return false;
    }
}

<?php
/**
* Lite Publisher CMS
* @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
* @link https://github.com/litepubl\cms
* @version 6.15
**/

namespace litepubl\updater;
use litepubl\utils\Filer;
use litepubl\widget\Sidebars;
use litepubl\utils\http;
use litepubl\core\Str;
use litepubl\view\Lang;

class Updater extends \litepubl\core\Events
 {
    private $releases;
    public $versions;
    public $result;
    public $log;

    protected function create() {
        parent::create();
        $this->basename = 'updater';
        $this->addevents('onupdated');
        $this->data['useshell'] = false;
        $this->versions = static ::getversions();
        $this->log = false;
    }

    public static function getVersions() {
        return Str::toArray(file_get_contents( $this->getApp()->paths->lib . 'install' . DIRECTORY_SEPARATOR . 'versions.txt'));
    }

    public function getVersion() {
        return $this->versions[0];
    }

    public function getNextversion() {
        return $this->getnext($this->versions);
    }

    public function getNext(array $versions) {
        $cur =  $this->getApp()->options->version;
        for ($i = count($versions) - 1; $i >= 0; $i--) {
            if (version_compare($cur, $versions[$i]) < 0) {
 return $versions[$i];
}


        }
        return $versions[0];
    }

    public function run($ver) {
        $ver = (string)$ver;
        if (strlen($ver) == 3) $ver.= '0';
        if (strlen($ver) == 1) $ver.= '.00';
        $filename =  $this->getApp()->paths->lib . "update/update.$ver.php";

        if (file_exists($filename)) {
            require_once ($filename);
            if ($this->log) Filer::log("$filename is required file", 'update');
            $func = 'update' . str_replace('.', '', $ver);

            if (function_exists($func)) {
                $func();
                if ($this->log) {
Filer::log("$func is called", 'update');
}
                 $this->getApp()->options->savemodified();
            } else if (function_exists('litepubl\\' . $func)) {
                call_user_func_array('litepubl\\' . $func, array());
                if ($this->log) {
Filer::log("$func is called", 'update');
}

                 $this->getApp()->options->savemodified();
            }
        }
    }

    public function update() {
        $log = $this->log;
        false;
        if ($log) {
Filer::log("begin update", 'update');
}
        Lang::clearcache();
        $this->versions = static ::getversions();
        $nextver = $this->nextversion;
        if ($log) {
Filer::log("update started from  $this->getApp()->options->version to $this->version", 'update');
}

        $v =  $this->getApp()->options->version + 0.01;
        while (version_compare($v, $nextver) <= 0) {
            $ver = (string)$v;
            if (strlen($ver) == 3) $ver.= '0';
            if (strlen($ver) == 1) $ver.= '.00';
            if ($log) {
Filer::log("$v selected to update", 'update');
}

            $this->run($v);
             $this->getApp()->options->version = $ver;
             $this->getApp()->options->savemodified();
            $v = $v + 0.01;
        }

        Filer::delete( $this->getApp()->paths->data . 'themes', false, false);
         $this->getApp()->cache->clear();
        Lang::clearcache();
        Sidebars::fix();

        if (function_exists('apc_clear_cache')) {
            apc_clear_cache();
        }

        if ($log) {
            Filer::log("update finished", 'update');
        }
    }

    public function autoupdate($protecttimeout = true) {
        if ($protecttimeout) {
            if (ob_get_level()) @ob_end_clean();
            Header('Cache-Control: no-cache, must-revalidate');
            Header('Pragma: no-cache');
            echo "\n";
            flush();
        }

        $lang = Lang::i('service');
        $backuper = Backuper::i();
        if ($this->useshell) {
            $backuper->createshellbackup();
        } else {
            $backuper->createbackup();
        }

        $releases = $this->downloadreleases();
        $latest = $this->getnext($releases);
        if ($this->download($latest)) {
            $this->result = $lang->successdownload;
            $this->update();
            $this->result.= $lang->successupdated;
            return true;
        }
        return false;
    }

    public function auto2($ver) {
        $lang = Lang::i('service');
        $latest = $this->latest;
        if ($latest ==  $this->getApp()->options->version) {
 return 'Already updated';
}


        if (($ver == 0) || ($ver > $latest)) $ver = $latest;
        if ($this->download($ver)) {
            $this->result = $lang->successdownload;
            $this->update();
            $result.= $lang->successupdated;
            return true;
        }
        return false;
    }

    public function islatest() {
        if ($latest = $this->getlatest()) {
            return version_compare($latest,  $this->getApp()->options->version);
        }
        return false;
    }

    public function getLatest() {
        if ($releases = $this->downloadreleases()) {
            return $releases[0];
        }

        return false;
    }

    public function downloadreleases() {
        if (isset($this->releases)) {
            return $this->releases;
        }

        if (($s = http::get('http://litepublisher.ru/service/versions.php' . '?php=' . PHP_VERSION . '&mysql=' .  $this->getApp()->db->mysqli->server_info . '&litepubl=' .  $this->getApp()->options->version)) ||

        ($s = http::get('https://github.com/litepubl/cms/raw/master/lib/install/versions.txt'))) {
            $this->releases = Str::toArray($s);
            return $this->releases;
        }

        return false;
    }

    public function download($version) {
        //if ($this->useshell) return $this->downloadshell($version);
        $lang = Lang::i('service');
        $backuper = Backuper::i();
        if (!$backuper->test()) {
            $this->result = $lang->errorwrite;
            return false;
        }

        if (!(($s = http::get("https://codeload.github.com/litepubl/cms/tar.gz/v$version")) || ($s = http::get("https://github.com/litepubl/cms/archive/v$version.tar.gz")) || ($s = http::get("http://litepublisher.com/download/litepublisher.$version.tar.gz")))) {
            $this->result = $lang->errordownload;
            return false;
        }

        if (!$backuper->upload($s, 'tar')) {
            $this->result = $backuper->result;
            return false;
        }

        $this->onupdated();
        return true;
    }

    public function downloadshell($version) {
        $filename = "litepublisher.$version.tar.gz";
        $cmd = array();
        $cmd[] = 'cd ' .  $this->getApp()->paths->backup;
        $cmd[] = 'wget http://litepublisher.googlecode.com/files/' . $filename;
        $cmd[] = 'cd ' .  $this->getApp()->paths->home;
        $cmd[] = sprintf('tar -xf %s%s -p --overwrite',  $this->getApp()->paths->backup, $filename);
        $cmd[] = 'rm ' .  $this->getApp()->paths->backup . $filename;
        //Str::dump(implode("\n", $cmd));
        exec(implode("\n", $cmd) , $r);
        if ($s = implode("\n", $r)) {
 return $s;
}


        $this->onupdated();
        return true;
    }

}
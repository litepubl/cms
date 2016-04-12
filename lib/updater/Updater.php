<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

namespace litepubl;

class tupdater extends tevents {
    private $releases;
    public $versions;
    public $result;
    public $log;

    public static function i() {
        return getinstance(__class__);
    }

    protected function create() {
        parent::create();
        $this->basename = 'updater';
        $this->addevents('onupdated');
        $this->data['useshell'] = false;
        $this->versions = static ::getversions();
        $this->log = false;
    }

    public static function getversions() {
        return strtoarray(file_get_contents(litepubl::$paths->lib . 'install' . DIRECTORY_SEPARATOR . 'versions.txt'));
    }

    public function getversion() {
        return $this->versions[0];
    }

    public function getnextversion() {
        return $this->getnext($this->versions);
    }

    public function getnext(array $versions) {
        $cur = litepubl::$options->version;
        for ($i = count($versions) - 1; $i >= 0; $i--) {
            if (version_compare($cur, $versions[$i]) < 0) return $versions[$i];
        }
        return $versions[0];
    }

    public function run($ver) {
        $ver = (string)$ver;
        if (strlen($ver) == 3) $ver.= '0';
        if (strlen($ver) == 1) $ver.= '.00';
        $filename = litepubl::$paths->lib . "update/update.$ver.php";

        if (file_exists($filename)) {
            require_once ($filename);
            if ($this->log) tfiler::log("$filename is required file", 'update');
            $func = 'update' . str_replace('.', '', $ver);

            if (function_exists($func)) {
                $func();
                if ($this->log) tfiler::log("$func is called", 'update');
                litepubl::$options->savemodified();
            } else if (function_exists('litepubl\\' . $func)) {
                call_user_func_array('litepubl\\' . $func, array());
                if ($this->log) tfiler::log("$func is called", 'update');
                litepubl::$options->savemodified();
            }
        }
    }

    public function update() {
        $log = $this->log;
        false;
        if ($log) tfiler::log("begin update", 'update');
        tlocal::clearcache();
        $this->versions = static ::getversions();
        $nextver = $this->nextversion;
        if ($log) tfiler::log("update started from litepubl::$options->version to $this->version", 'update');
        $v = litepubl::$options->version + 0.01;
        while (version_compare($v, $nextver) <= 0) {
            $ver = (string)$v;
            if (strlen($ver) == 3) $ver.= '0';
            if (strlen($ver) == 1) $ver.= '.00';
            if ($log) tfiler::log("$v selected to update", 'update');
            $this->run($v);
            litepubl::$options->version = $ver;
            litepubl::$options->savemodified();
            $v = $v + 0.01;
        }

        //ttheme::clearcache();
        tfiler::delete(litepubl::$paths->data . 'themes', false, false);
        litepubl::$urlmap->clearcache();
        tlocal::clearcache();
        tsidebars::fix();
        if (function_exists('apc_clear_cache')) {
            apc_clear_cache();
        }

        if ($log) {
            tfiler::log("update finished", 'update');
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

        $lang = tlocal::i('service');
        $backuper = tbackuper::i();
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
        $lang = tlocal::i('service');
        $latest = $this->latest;
        if ($latest == litepubl::$options->version) return 'Already updated';
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
            return version_compare($latest, litepubl::$options->version);
        }
        return false;
    }

    public function getlatest() {
        if ($releases = $this->downloadreleases()) {
            return $releases[0];
        }

        return false;
    }

    public function downloadreleases() {
        if (isset($this->releases)) {
            return $this->releases;
        }

        if (($s = http::get('http://litepublisher.ru/service/versions.php' . '?php=' . PHP_VERSION . '&mysql=' . litepubl::$db->mysqli->server_info . '&litepubl=' . litepubl::$options->version)) ||

        ($s = http::get('https://github.com/litepubl/cms/raw/master/lib/install/versions.txt'))) {
            $this->releases = strtoarray($s);
            return $this->releases;
        }

        return false;
    }

    public function download($version) {
        //if ($this->useshell) return $this->downloadshell($version);
        $lang = tlocal::i('service');
        $backuper = tbackuper::i();
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
        $cmd[] = 'cd ' . litepubl::$paths->backup;
        $cmd[] = 'wget http://litepublisher.googlecode.com/files/' . $filename;
        $cmd[] = 'cd ' . litepubl::$paths->home;
        $cmd[] = sprintf('tar -xf %s%s -p --overwrite', litepubl::$paths->backup, $filename);
        $cmd[] = 'rm ' . litepubl::$paths->backup . $filename;
        //dumpstr(implode("\n", $cmd));
        exec(implode("\n", $cmd) , $r);
        if ($s = implode("\n", $r)) return $s;
        $this->onupdated();
        return true;
    }

} //class
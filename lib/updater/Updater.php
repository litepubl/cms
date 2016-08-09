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

use litepubl\core\Str;
use litepubl\utils\Filer;
use litepubl\utils\Http;
use litepubl\view\Css;
use litepubl\view\Js;
use litepubl\view\Lang;
use litepubl\widget\Sidebars;

/**
 * Download and update latest version
 *
 * @property       bool $useshell
 * @property-write callable $onUpdated
 * @method         array onUpdated(array $params)
 */

class Updater extends \litepubl\core\Events
{
    protected $releases;
    public $versions;
    public $result;
    public $log;

    protected function create()
    {
        parent::create();
        $this->basename = 'updater';
        $this->addevents('onupdated');
        $this->data['useshell'] = false;
        $this->versions = $this->getVersions();
        $this->log = false;
    }

    public function getVersions(): array
    {
        return Str::toArray(file_get_contents($this->getApp()->paths->lib . 'install' . DIRECTORY_SEPARATOR . 'versions.txt'));
    }

    public function getVersion(): string
    {
        return $this->versions[0];
    }

    public function getNextversion(): string
    {
        return $this->getnext($this->versions);
    }

    public function getCurrentVersion(): string
    {
        return $this->getApp()->options->version;
    }

    public function setCurrentVersion(string $ver)
    {
        $this->getApp()->options->version = $ver;
    }

    public function getNext(array $versions): string
    {
        $cur = $this->getCurrentVersion();
        for ($i = count($versions) - 1; $i >= 0; $i--) {
            if (version_compare($cur, $versions[$i]) < 0) {
                return $versions[$i];
            }
        }

        return $versions[0];
    }

    public function log($mesg)
    {
        if ($this->log) {
            $this->getApp()->getLogger()->debug($mesg);
        }
    }

    public function getScriptFileName(string $ver): string
    {
        return $this->getApp()->paths->lib . "update/update.$ver.php";
    }

    public function getScriptFuncName(string $ver): string
    {
        return 'litepubl\update\update' . str_replace('.', '', $ver);
    }

    public function run($ver)
    {
        $ver = (string)$ver;
        if (strlen($ver) == 3) {
            $ver.= '0';
        }

        if (strlen($ver) == 1) {
            $ver.= '.00';
        }

        $app = $this->getApp();
        $filename = $this->getScriptFileName($ver);
        if (file_exists($filename)) {
            include_once $filename;
            $this->log("$filename is required file", 'update');
            $func = $this->getScriptFuncName($ver);

            if (function_exists($func)) {
                call_user_func_array($func, []);
                $app->poolStorage->commit();
                $this->log("$func is called", 'update');
            } else {
                $this->log("$func not exists", 'update');
            }
        } else {
                $this->log("Update file $filename not exists");
        }
    }

    public function update()
    {
        $this->log("begin update", 'update');
        Lang::clearCache();

        $js = Js::i();
        $js->lock();

        $css = Css::i();
        $css->lock();

        $this->versions = $this->getVersions();
        $nextver = (float) $this->nextversion;
        $app = $this->getApp();
        $v = $this->getCurrentVersion();
        $v += 0.01;

        while (version_compare($v, $nextver) <= 0) {
            $ver = (string)$v;
            if (strlen($ver) == 3) {
                $ver.= '0';
            }
            if (strlen($ver) == 1) {
                $ver.= '.00';
            }

            $this->log("$v selected to update", 'update');
            $this->run($v);
            $this->setCurrentVersion($ver);
            $app->poolStorage->commit();
            $v = $v + 0.01;
        }

        $js->unlock();
        $css->unlock();
        Filer::delete($app->paths->data . 'themes', false, false);
        $app->cache->clear();
        Lang::clearCache();
        Sidebars::fix();

        if (function_exists('apc_clear_cache')) {
            apc_clear_cache();
        }

        $this->log("update finished", 'update');
        $this->onupdated([]);
    }

    public function autoUpdate($protecttimeout = true)
    {
        if ($protecttimeout) {
            if (ob_get_level()) {
                @ob_end_clean();
            }

            Header('Cache-Control: no-cache, must-revalidate');
            Header('Pragma: no-cache');
            echo "\n";
            flush();
        }

        $lang = Lang::i('service');
        $backuper = Backuper::i();
        if ($this->useshell) {
            $backuper->createShellBackup();
        } else {
            $backuper->createBackup();
        }

        $releases = $this->downloadReleases();
        $latest = $this->getNext($releases);
        if ($this->download($latest)) {
            $this->result = $lang->successdownload;
            $this->update();
            $this->result.= $lang->successupdated;
            return true;
        }
        return false;
    }

    public function auto2($ver)
    {
        $lang = Lang::i('service');
        $latest = $this->latest;
        if ($latest == $this->getCurrentVersion()) {
            return 'Already updated';
        }

        if (($ver == 0) || ($ver > $latest)) {
            $ver = $latest;
        }

        if ($this->download($ver)) {
            $this->result = $lang->successdownload;
            $this->update();
            $this->result.= $lang->successupdated;
            return true;
        }
        return false;
    }

    public function isLatest()
    {
        if ($latest = $this->getlatest()) {
            return version_compare($latest, $this->getCurrentVersion());
        }
        return false;
    }

    public function getLatest()
    {
        if ($releases = $this->downloadreleases()) {
            return $releases[0];
        }

        return false;
    }

    public function getVersionUrls(): array
    {
        return [
        'http://litepublisher.ru/service/versions.php' . '?php=' . PHP_VERSION . '&mysql=' . $this->getApp()->db->mysqli->server_info . '&litepubl=' . $this->getApp()->options->version,
        'https://github.com/litepubl/cms/raw/master/lib/install/versions.txt'
        ];
    }

    public function downloadReleases()
    {
        if (isset($this->releases)) {
            return $this->releases;
        }

        $urls = $this->getVersionUrls();
        foreach ($urls as $url) {
            if ($s = Http::get($url)) {
                    $this->releases = Str::toArray($s);
                    return $this->releases;
            }
        }

        return false;
    }

    public function getDownloadUrls(string $version): array
    {
        return [
        "https://codeload.github.com/litepubl/cms/tar.gz/v$version",
        "https://github.com/litepubl/cms/archive/v$version.tar.gz",
        "http://litepublisher.com/download/litepublisher.$version.tar.gz",
        ];
    }

    public function downloadFile(array $urls)
    {
        $result = new \StdClass();
        foreach ($urls as $url) {
            if ($result->file = Http::get($url)) {
                return $result;
            }
        }

        return false;
    }

    public function download(string $version)
    {
        $lang = Lang::i('service');
        $backuper = Backuper::i();
        if (!$backuper->test()) {
            $this->result = $lang->errorwrite;
            return false;
        }
        $urls = $this->getDownloadUrls($version);
        $s = $this->downloadFile($urls);
        if (!$s) {
            $this->result = $lang->errordownload;
            return false;
        }

        if (!$backuper->upload($s, $this->isZip($s) ? 'zip' : 'tar')) {
            $this->result = $backuper->result;
            return false;
        }

        return true;
    }

    public function isZip($content): bool
    {
        $sign = "\x50\x4b\x03\x04";
        if (is_string($content)) {
            return Str::begin($content, $sign);
        } elseif (is_object($content)) {
            for ($i = strlen($sign) - 1; $i >= 0; $i--) {
                if ($sign[$i] != $content->file[$i]) {
                    return false;
                }
            }

            return true;
        }

        return false;
    }


    public function downloadShell(string $version)
    {
        $urls = $this->getDownloadUrls($version);

        $cmd = [];
        $cmd[] = 'cd ' . $this->getApp()->paths->backup;
        $cmd[] = 'wget ' . $urls[0];
        $cmd[] = 'cd ' . $this->getApp()->paths->home;
        $cmd[] = sprintf('tar -xf %s%s -p --overwrite', $this->getApp()->paths->backup, $filename);
        $cmd[] = 'rm ' . $this->getApp()->paths->backup . $filename;
        //Str::dump(implode("\n", $cmd));
        exec(implode("\n", $cmd), $r);
        if ($s = implode("\n", $r)) {
            return $s;
        }

        return true;
    }

}

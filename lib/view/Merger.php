<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.05
  */

namespace litepubl\view;

use litepubl\core\Arr;
use litepubl\core\Plugins;
use litepubl\core\Str;

/**
 * Common class for join files
 *
 * @property-write callable $onSave
 * @method         array onsave(array $params)
 */

class Merger extends \litepubl\core\Items
{

    protected function create()
    {
        $this->dbversion = false;
        parent::create();
        $this->basename = 'merger';
        $this->data['revision'] = 0;
        $this->addEvents('onsave');
    }

    public function save()
    {
        if ($this->lockcount > 0) {
            return;
        }

        $this->data['revision']++;
        parent::save();
        $this->merge();
        $this->onsave([]);
    }

    public function normFilename($filename)
    {
        $filename = trim($filename);
        if (Str::begin($filename, $this->getApp()->paths->home)) {
            $filename = substr($filename, strlen($this->getApp()->paths->home));
        }

        if (empty($filename)) {
            return false;
        }

        $filename = str_replace(DIRECTORY_SEPARATOR, '/', $filename);
        return '/' . ltrim($filename, '/');
    }

    public function add($section, $filename)
    {
        if (!($filename = $this->normfilename($filename))) {
            return false;
        }

        if (!isset($this->items[$section])) {
            $this->items[$section] = [
                'files' => [
                    $filename
                ] ,
                'texts' => []
            ];
        } else {
            if (in_array($filename, $this->items[$section]['files'])) {
                return false;
            }

            $this->items[$section]['files'][] = $filename;
        }
        $this->save();
        return count($this->items[$section]['files']) - 1;
    }

    public function delete($id)
    {
        $a = func_get_args();
        return $this->deleteFile($id, $a[1]);
    }

    public function deleteFile($section, $filename)
    {
        if (!isset($this->items[$section])) {
            return false;
        }

        if (!($filename = $this->normfilename($filename))) {
            return false;
        }

        if (false === ($i = array_search($filename, $this->items[$section]['files']))) {
            return false;
        }

        Arr::delete($this->items[$section]['files'], $i);
        $this->save();
    }

    public function replaceFile(string $section, string $src, string $dst): bool
    {
        if (isset($this->items[$section])
            && ($src = $this->normFilename($src))
            && ($dst = $this->normFilename($dst))
            && (false !== ($i = array_search($src, $this->items[$section]['files'])))
        ) {
            $this->items[$section]['files'][$i] = $dst;
            $this->save();
            return true;
        }

        return false;
    }

    public function after($section, $src, $dst)
    {
        if (!isset($this->items[$section])) {
            return false;
        }

        if (!($src = $this->normfilename($src))) {
            return false;
        }

        if (in_array($dst, $this->items[$section]['files'])) {
            return false;
        }

        if (!($dst = $this->normfilename($dst))) {
            return false;
        }

        if (false === ($i = array_search($src, $this->items[$section]['files']))) {
            //simple add
            $this->items[$section]['files'][] = $dst;
        } else {
            //insert after
            array_splice(
                $this->items[$section]['files'], $i + 1, 0, [
                $dst
                ]
            );
        }
        $this->save();
    }

    public function setFiles($section, $s)
    {
        $this->lock();
        if (isset($this->items[$section])) {
            $this->items[$section]['files'] = [];
        } else {
            $this->items[$section] = [
                'files' => [] ,
                'texts' => []
            ];
        }

        $a = explode("\n", trim($s));
        foreach ($a as $filename) {
            $this->add($section, trim($filename));
        }
        $this->unlock();
    }

    public function addText($section, $key, $s)
    {
        $s = trim($s);
        if (empty($s)) {
            return false;
        }

        if (!isset($this->items[$section])) {
            $this->items[$section] = [
                'files' => [] ,
                'texts' => [
                    $key => $s
                ]
            ];
        } else {
            if (in_array($s, $this->items[$section]['texts'])) {
                return false;
            }

            $this->items[$section]['texts'][$key] = $s;
        }
        $this->save();
        return count($this->items[$section]['texts']) - 1;
    }

    public function deleteText($section, $key)
    {
        if (!isset($this->items[$section]['texts'][$key])) {
            return;
        }

        unset($this->items[$section]['texts'][$key]);
        $this->save();
        return true;
    }

    public function getFileName($section, $revision)
    {
        return sprintf('/files/js/%s.%s.js', $section, $revision);
    }

    public function readFile($filename)
    {
        $result = file_get_contents($filename);
        if ($result === false) {
            $this->error(sprintf('Error read %s file', $filename));
        }
        return $result;
    }

    public function deleteSection(string $section)
    {
        $home = rtrim($this->getApp()->paths->home, DIRECTORY_SEPARATOR);
        @unlink($home . str_replace('/', DIRECTORY_SEPARATOR, $this->getfilename($section, $this->revision)));

        $template = MainView::i();
        unset($template->data[$this->basename . '_' . $section]);
        $template->save();

        unset($this->items[$section]);
        $this->save();
        $this->deleted(['section' => $section]);
    }

    public function merge()
    {
        $home = rtrim($this->getApp()->paths->home, DIRECTORY_SEPARATOR);
        $theme = Theme::i();
        $vars = new Vars();
        $vars->plugins = Plugins::i();
        $template = MainView::i();
        $template->data[$this->basename] = $this->revision;


        foreach ($this->items as $section => $items) {
            $s = '';
            foreach ($items['files'] as $filename) {
                $filename = $theme->parse($filename);
                $filename = $home . str_replace('/', DIRECTORY_SEPARATOR, $filename);
                if (file_exists($filename)) {
                    $s.= $this->readfile($filename);
                    //prevent comments
                    $s.= "\n";
                } else {
                    trigger_error(sprintf('The file "%s" not exists', $filename), E_USER_WARNING);
                }
            }

            $s.= implode("\n", $items['texts']);
            $savefile = $this->getfilename($section, $this->revision);
            $realfile = $home . str_replace('/', DIRECTORY_SEPARATOR, $savefile);
            file_put_contents($realfile, $s);
            @chmod($realfile, 0666);
            $template->data[$this->basename . '_' . $section] = $savefile;
        }
        $template->save();
        $this->getApp()->cache->clear();
        foreach (array_keys($this->items) as $section) {
            $old = $home . str_replace('/', DIRECTORY_SEPARATOR, $this->getfilename($section, $this->revision - 1));
            @unlink($old);
        }
    }
}

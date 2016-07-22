<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.01
  */

namespace litepubl\view;

use litepubl\Config;
use litepubl\core\Arr;
use litepubl\core\Str;
use litepubl\core\Plugins;

/**
 * Common class for theme parsing
 *
 * @property-write callable $onGetPaths
 * @property-write callable $beforeParse
 * @property-write callable $parsed
 * @property-write callable $onFix
 * @method         array onGetPaths(array $params) triggered when tags required before parse
 * @method         array beforeParse(array $params) triggered before parse text
 * @method         array parsed(array $params) triggered after parse
 * @method         array onFix(array $params) triggered after parse
 */

class BaseParser extends \litepubl\core\Events
{
    public $theme;
    public $themefiles;
    public $tagfiles;
    public $paths;
    public $extrapaths;
    protected $abouts;
    protected $pathmap;
    protected $parsedtags;

    protected function create()
    {
        parent::create();
        $this->basename = 'baseparser';
        $this->addEvents('ongetpaths', 'beforeparse', 'parsed', 'onfix');
        $this->addmap('tagfiles', array());
        $this->addmap('themefiles', array());
        $this->addmap('extrapaths', array());
        $this->data['replacelang'] = false;
        $this->data['removephp'] = true;
        $this->data['removespaces'] = true;

        $this->pathmap = array();
    }

    public function checkAbout(string $name): bool
    {
        return true;
    }

    public function getParentName(string $name): string
    {
        $about = $this->getAbout($name);
        return $about['parent'] ?? '';
    }

    public function getFileList(string $name): array
    {
        $result = array();
        $paths = $this->getApp()->paths;
        $about = $this->getAbout($name);
        $result[] = $paths->themes . $name . '/' . $about['file'];

        $base = new Base();
        $vars = new Vars();
        $vars->plugins = Plugins::i();
        foreach ($this->themefiles as $filename) {
            $filename = ltrim($filename, '/');
            if (!$filename) {
                continue;
            }

            $filename = $base->parse($filename);
                $result[] = $paths->home . $filename;
        }

        return $result;
    }

    public function parse(Base $theme)
    {
        $this->checkParent($theme->name);
        if (!$this->checkAbout($theme->name)) {
            return false;
        }

        $theme->lock();
        if ($parentname = $this->getParentName($theme->name)) {
            $parent_theme = Base::getByName(get_class($theme), $parentname);
            $theme->templates = $parent_theme->templates;
            $theme->parent = $parent_theme->name;
        }

        $this->parsedtags = array();
        $about = $this->getAbout($theme->name);

        $filelist = $this->getFileList($theme->name);
        foreach ($filelist as $filename) {
            if ($s = $this->getfile($filename)) {
                $s = $this->replace_about($s, $about);
                $this->parsetags($theme, $s);
            }
        }

        $this->afterparse($theme);
        if ($this->replacelang) {
            $this->doreplacelang($theme);
        }

        $this->parsed(['theme' => $theme]);
        $theme->unlock();
        return true;
    }

    public function doreplacelang(Base $theme)
    {
        $lang = Lang::i('comment');
        foreach ($theme->templates as $name => $value) {
            if (is_string($value)) {
                $theme->templates[$name] = $theme->replacelang($value, $lang);
            }
        }
    }

    public function callback_replace_php(array $m)
    {
        return strtr(
            $m[0], array(
            '$' => '&#36;',
            '?' => '&#63;',
            '(' => '&#40;',
            ')' => '&#41;',
            '[' => '&#91;',
            ']' => '&#93;',
            '{' => '&#123;',
            '}' => '&#125;'
            )
        );
    }

    public function callback_restore_php($m)
    {
        return strtr(
            $m[0], array(
            '&#36;' => '$',
            '&#63;' => '?',
            '&#40;' => '(',
            '&#41;' => ')',
            '&#91;' => '[',
            '&#93;' => ']',
            '&#123;' => '{',
            '&#125;' => '}'
            )
        );
    }

    public function getFile($filename)
    {
        if (!file_exists($filename)) {
            return $this->error(sprintf('The required file "%s" file not exists', $filename));
        }

        $s = file_get_contents($filename);
        if ($s === false) {
            return $this->error(sprintf('Error read "%s" file', $filename));
        }

        $s = Str::trimUtf($s);
        $s = str_replace(
            array(
            "\r\n",
            "\r",
            "\n\n"
            ), "\n", $s
        );

        //strip coments
        $s = preg_replace('/\s*\/\*.*?\*\/\s*/sm', "\n", $s);
        $s = preg_replace('/^\s*\/\/.*?$/m', '', $s);

        //normalize tags
        $s = preg_replace('/%%([a-zA-Z0-9]*+)_(\w\w*+)%%/', '\$$1.$2', $s);
        return trim($s);
    }

    //replace $about.*
    public function replace_about($s, $about)
    {
        if (preg_match_all('/\$about\.(\w\w*+)/', $s, $m, PREG_SET_ORDER)) {
            $a = array();
            foreach ($m as $item) {
                $name = $item[1];
                if (isset($about[$name])) {
                    $a[$item[0]] = $about[$name];
                }
            }
            $s = strtr($s, $a);
        }

        return $s;
    }

    public function getAbout(string $name): array
    {
        if (!isset($this->abouts)) {
            $this->abouts = array();
        }
        if (!isset($this->abouts[$name])) {
            $filename = $this->getApp()->paths->themes . $name . DIRECTORY_SEPARATOR . 'about.ini';
            if (file_exists($filename) && ($about = parse_ini_file($filename, true))) {
                if (empty($about['about']['type'])) {
                    $about['about']['type'] = 'litepublisher3';
                }
                //join languages
                if (isset($about[$this->getApp()->options->language])) {
                    $about['about'] = $about[$this->getApp()->options->language] + $about['about'];
                }

                $this->abouts[$name] = $about['about'];
            } else {
                $this->abouts[$name] = false;
            }
        }
        return $this->abouts[$name];
    }

    public function checkParent(string $name): bool
    {
        if ($name == 'default') {
            return true;
        }

        $about = $this->getAbout($name);
        $parents = [$name];
        while (!empty($about['parent'])) {
            $name = $about['parent'];
            if (in_array($name, $parents)) {
                $this->error(sprintf('Theme circle "%s"', implode(', ', $parents)));
            }

            $parents[] = $name;
            $about = $this->getAbout($name);
        }

        return true;
    }

    public function reparse()
    {
        $theme = Theme::i();
        $theme->lock();
        $this->parse($theme);
        Base::clearCache();
        $theme->unlock();
    }

    //4 ver
    public static function find_close($s, $a)
    {
        $brackets = array(
            '[' => ']',
            '{' => '}',
            '(' => ')'
        );

        $b = $brackets[$a];
        $i = strpos($s, $b);
        $sub = substr($s, 0, $i);
        if (substr_count($sub, $a) == 0) {
            return $i;
        }

        while (substr_count($sub, $a) > substr_count($sub, $b)) {
            $i = strpos($s, $b, $i + 1);
            if ($i === false) {
                die(" The '$b' not found in\n$s");
            }
            $sub = substr($s, 0, $i);
        }

        return $i;
    }

    public function parseTags(Base $theme, $s)
    {
        $this->theme = $theme;
        if (!$this->paths || !count($this->paths)) {
            $this->paths = $this->loadpaths();
        }

        $s = trim($s);
        $info = $this->beforeparse(
            [
            'theme' => $theme,
            'text' => $s,
            ]
        );

        $s = $info['text'];
        if ($this->removephp) {
            $s = preg_replace('/\<\?.*?\?\>/ims', '', $s);
        } else {
            $s = preg_replace_callback(
                '/\<\?(.*?)\?\>/ims', array(
                $this,
                'callback_replace_php'
                ), $s
            );
        }

        while ($s) {
            if (preg_match('/^(((\$template|\$custom)?\.?)?\w*+(\.\w\w*+)*)\s*=\s*(\[|\{|\()?/i', $s, $m)) {
                $tag = $m[1];
                $s = ltrim(substr($s, strlen($m[0])));
                if (isset($m[5])) {
                    $i = static ::find_close($s, $m[5]);
                } else {
                    $i = strpos($s, "\n");
                }

                $value = trim(substr($s, 0, $i));
                $s = ltrim(substr($s, $i));

                $this->settag($tag, $value);
            } else {
                if ($i = strpos($s, "\n")) {
                    $s = ltrim(substr($s, $i));
                } else {
                    $s = '';
                }
            }
        }

    }

    public function setTag($parent, $s)
    {
        if (isset($this->pathmap[$parent])) {
            $parent = $this->pathmap[$parent];
        }

        if (preg_match('/file\s*=\s*(\w[\w\._\-]*?\.\w\w*+\s*)/i', $s, $m) || preg_match('/\@import\s*\(\s*(\w[\w\._\-]*?\.\w\w*+\s*)\)/i', $s, $m)) {
            $filename = $this->getApp()->paths->themes . $this->theme->name . DIRECTORY_SEPARATOR . $m[1];
            if (!file_exists($filename)) {
                $this->error("File '$filename' not found");
            }

            if ($s = $this->getfile($filename)) {
                $s = $this->replace_about($s, $this->getabout($this->theme->name));
            }
        }

        $parent = $this->preparetag($parent);

        if ($this->removephp) {
            $s = preg_replace('/\<\?.*?\?\>/ims', '', $s);
        } else {
            $s = preg_replace_callback(
                '/\<\?(.*?)\?\>/ims', array(
                $this,
                'callback_replace_php'
                ), $s
            );
        }

        while ($s && preg_match('/(\$\w*+(\.\w\w*+)?)\s*=\s*(\[|\{|\()/i', $s, $m)) {
            if (!isset($m[3])) {
                $this->error('The bracket not found in ' . $s);
            }

            $tag = $m[1];
            $j = strpos($s, $m[0]);
            $pre = rtrim(substr($s, 0, $j));
            $s = ltrim(substr($s, $j + strlen($m[0])));
            $i = static ::find_close($s, $m[3]);
            $value = trim(substr($s, 0, $i));
            $s = ltrim(substr($s, $i + 1));

            $checkpath = $parent . '.' . substr($tag, 1);
            if (isset($this->pathmap[$checkpath])) {
                $newpath = $this->pathmap[$checkpath];
                $info = $this->paths[$newpath];
                $info['path'] = $newpath;
            } else {
                $info = $this->getinfo($parent, $tag);
            }

            $this->settag($info['path'], $value);
            $s = $pre . $info['replace'] . $s;
        }

        $s = trim($s);
        if (!$this->removephp) {
            $s = preg_replace_callback(
                '/\<\&\#63;.*?\&\#63;\>/ims', array(
                $this,
                'callback_restore_php'
                ), $s
            );
        }

        $this->setvalue($parent, $s);
    }

    protected function preparetag($name)
    {
        if (Str::begin($name, '$template.')) {
            $name = substr($name, strlen('$template.'));
        }
        return $name;
    }

    protected function setValue($name, $value)
    {
        if (isset($this->paths[$name])) {
            $this->theme->templates[$name] = $value;
        } else {
            $this->error("The '$name' tag not found. Content \n$value");
        }
    }

    public function getInfo($name, $child)
    {
        $path = $name . '.' . substr($child, 1);
        if (isset($this->paths[$path])) {
            $info = $this->paths[$path];
            $info['path'] = $path;
            return $info;
        } else {
            /*
            foreach ($this->paths as $path => $info) {
            if (Str::begin($path, $name) && ($child == $info['tag'])) {
              $info['path'] = $path;
              return $info;
            }
            }
            */
        }

        $this->error("The '$child' not found in path '$name'");
    }

    public function afterparse($theme)
    {
        $this->onFix(['theme' => $theme]);
        $this->reuse($this->theme->templates);

        if (!Config::$debug && $this->removespaces) {
            foreach ($theme->templates as $k => $v) {
                if (is_string($v)) {
                    $theme->templates[$k] = $this->remove_spaces($v);
                } elseif (is_array($v)) {
                    foreach ($v as $vk => $vv) {
                        if (is_string($vv)) {
                            $v[$vk] = $this->remove_spaces($vv);
                        }
                    }
                    $theme->templates[$k] = $v;
                }
            }
        }
    }

    public function reuse(&$templates)
    {
        foreach ($templates as $k => $v) {
            if (is_string($v) && !Str::begin($v, '<') && isset($templates[$v]) && is_string($templates[$v])) {
                $templates[$k] = $templates[$v];
            }
        }
    }

    public function remove_spaces($s)
    {
        $s = str_replace(
            array(
            "\n",
            "\t",
            "\x00"
            ), ' ', $s
        );
        $s = preg_replace('/[ ]{2,}/ms', ' ', $s);
        return str_replace('> <', '><', $s);
    }

    public function loadPaths(): array
    {
        $result = array();
        $paths = $this->getApp()->paths;
        foreach ($this->tagfiles as $filename) {
            $filename = $paths->home . trim($filename, '/');
            if ($filename && file_exists($filename) && ($a = parse_ini_file($filename, true))) {
                if (isset($a['remap'])) {
                    $this->pathmap = $this->pathmap + $a['remap'];
                    unset($a['remap']);
                }
                $result = $result + $a;
            }
        }

        $result = $result + $this->extrapaths;
        $info = $this->ongetpaths(['paths' => $result]);
        return $info['paths'];
    }

    public function addtags($filetheme, $filetags)
    {
        if ($filetheme && !in_array($filetheme, $this->themefiles)) {
            $this->themefiles[] = $filetheme;
        }

        if ($filetags && !in_array($filetags, $this->tagfiles)) {
            $this->tagfiles[] = $filetags;
        }

        Arr::clean($this->themefiles);
        Arr::clean($this->tagfiles);
        $this->save();
        Base::clearcache();
    }

    public function removetags($filetheme, $filetags)
    {
        Arr::deleteValue($this->themefiles, $filetheme);
        Arr::deleteValue($this->tagfiles, $filetags);
        Arr::clean($this->themefiles);
        Arr::clean($this->tagfiles);
        $this->save();
        Base::clearcache();
    }
}

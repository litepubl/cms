<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.05
  */

namespace litepubl\post;

use litepubl\core\Str;
use litepubl\view\Args;
use litepubl\view\Theme;
use litepubl\view\Vars;

/**
 * View file list
 *
 * @property-write callable $onGetFilelist
 * @property-write callable $onlist
 * @method         array onGetFilelist(array $params)
 * @method         array onlist(array $params)
 */

class FileView extends \litepubl\core\Events
{
    protected $templates;

    protected function create()
    {
        parent::create();
        $this->basename = 'fileview';
        $this->addEvents('ongetfilelist', 'onlist');
        $this->templates = [];
    }

    public function getFiles(): Files
    {
        return Files::i();
    }

    public function getFileList(array $list, bool $excerpt, Theme $theme): string
    {
        $r = $this->onGetFilelist(['list' => $list, 'excerpt' => $excerpt, 'result' => false]);
        if ($r['result']) {
            return $r['result'];
        }

        if (!count($list)) {
            return '';
        }

        $tml = $excerpt ? $this->getTml($theme, 'content.excerpts.excerpt.filelist') : $this->getTml($theme, 'content.post.filelist');
        return $this->getList($list,  $tml);
    }

    public function getTml(Theme $theme, string $basekey): array
    {
        if (isset($this->templates[$theme->name][$basekey])) {
            return $this->templates[$theme->name][$basekey];
        }

        $result = [
            'container' => $theme->templates[$basekey],
        ];

        $key = $basekey . '.';
        foreach ($theme->templates as $k => $v) {
            if (Str::begin($k, $key)) {
                $result[substr($k, strlen($key)) ] = $v;
            }
        }

        if (!isset($this->templates[$theme->name])) {
                $this->templates[$theme->name] = [];
        }

        $this->templates[$theme->name][$basekey] = $result;
        return $result;
    }

    public function getList(array $list, array $tml): string
    {
        if (!count($list)) {
            return '';
        }

        $this->onList(['list' => $list]);
        $result = '';
        $files = $this->getFiles();
        $files->preLoad($list);

        //sort by media type
        $items = [];
        foreach ($list as $id) {
            if (!isset($files->items[$id])) {
                continue;
            }

            $item = $files->items[$id];
            $type = $item['media'];
            if (isset($tml[$type])) {
                $items[$type][] = $id;
            } else {
                $items['file'][] = $id;
            }
        }

        $theme = Theme::i();
        $args = new Args();
        $args->count = count($list);

        $url = $this->getApp()->site->files . '/files/';

        $preview = new \ArrayObject([], \ArrayObject::ARRAY_AS_PROPS);
        $vars = new Vars();
        $vars->preview = $preview;
        $midle = new \ArrayObject([], \ArrayObject::ARRAY_AS_PROPS);
        $vars->midle = $midle;

        $index = 0;
        foreach ($items as $type => $subitems) {
            $args->subcount = count($subitems);
            $sublist = '';
            foreach ($subitems as $typeindex => $id) {
                $item = $files->items[$id];
                $args->add($item);
                $args->link = $url . $item['filename'];
                $args->id = $id;
                $args->typeindex = $typeindex;
                $args->index = $index++;
                $args->preview = '';
                $preview->exchangeArray([]);

                if ($idmidle = (int)$item['midle']) {
                    $midle->exchangeArray($files->getItem($idmidle));
                    $midle->link = $url . $midle->filename;
                    $midle->json = $this->getJson($idmidle);
                } else {
                    $midle->exchangeArray([]);
                    $midle->link = '';
                    $midle->json = '';
                }

                if ((int)$item['preview']) {
                    $preview->exchangeArray($files->getItem($item['preview']));
                } elseif ($type == 'image') {
                    $preview->exchangeArray($item);
                    $preview->id = $id;
                } elseif ($type == 'video') {
                    $args->preview = $theme->parseArg($tml['videos.fallback'], $args);
                    $preview->exchangeArray([]);
                }

                if ($preview->count()) {
                    $preview->link = $url . $preview->filename;
                    $args->preview = $theme->parseArg($tml['preview'], $args);
                }

                $args->json = $this->getJson($id);
                $sublist.= $theme->parseArg($tml[$type], $args);
            }

            $args->__set($type, $sublist);
            $result.= $theme->parseArg($tml[$type . 's'], $args);
        }

        $args->files = $result;
        return $theme->parseArg($tml['container'], $args);
    }

    public function getFirstImage(array $items): string
    {
        $files = $this->getFiles();
        foreach ($items as $id) {
            $item = $files->getItem($id);
            if (('image' == $item['media']) && ($idpreview = (int)$item['preview'])) {
                $baseurl = $this->getApp()->site->files . '/files/';
                $args = new Args();
                $args->add($item);
                $args->link = $baseurl . $item['filename'];
                $args->json = $this->getJson($id);

                $preview = new \ArrayObject($files->getItem($idpreview), \ArrayObject::ARRAY_AS_PROPS);
                $preview->link = $baseurl . $preview->filename;

                $midle = new \ArrayObject([], \ArrayObject::ARRAY_AS_PROPS);
                if ($idmidle = (int)$item['midle']) {
                    $midle->exchangeArray($files->getItem($idmidle));
                    $midle->link = $baseurl . $midle->filename;
                    $midle->json = $this->getJson($idmidle);
                } else {
                    $midle->json = '';
                }

                $vars = new Vars();
                $vars->preview = $preview;
                $vars->midle = $midle;
                $theme = Theme::i();
                return $theme->parseArg($theme->templates['content.excerpts.excerpt.firstimage'], $args);
            }
        }

        return '';
    }

    public function getJson(int $id): string
    {
        $item = $this->getFiles()->getItem($id);
        return Str::jsonAttr(
            [
            'id' => $id,
            'link' => $this->getApp()->site->files . '/files/' . $item['filename'],
            'width' => $item['width'],
            'height' => $item['height'],
            'size' => $item['size'],
            'midle' => $item['midle'],
            'preview' => $item['preview'],
            ]
        );
    }
}

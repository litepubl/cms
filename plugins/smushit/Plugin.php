<?php
/**
 * LitePubl CMS
 *
 * @copyright 2010 - 2017 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.08
  */

namespace litepubl\plugins\smushit;

use litepubl\core\Event;
use litepubl\core\Str;
use litepubl\post\Files;
use litepubl\post\MediaParser;
use litepubl\utils\Http;

class Plugin extends \litepubl\core\Plugin
{

    public function install()
    {
        $parser = MediaParser::i();
        $parser->added = $this->fileAdded;
    }

    public function uninstall()
    {
        $parser = MediaParser::i();
        $parser->unbind($this);
    }

    public function fileAdded(Event $event)
    {
        $files = Files::i();
        $item = $files->getItem($event->id);
        if ('image' != $item['media']) {
            return;
        }

        $fileurl = $files->getUrl($id);
        if ($s = Http::get('http://www.smushit.com/ysmush.it/ws.php?img=' . urlencode($fileurl))) {
            $json = json_decode($s);
            if (isset($json->error) || (-1 === (int)$json->dest_size) || !$json->dest) {
                return;
            }

            $div = $item['size'] - (int)$json->dest_size;
            if (($div / ($item['size'] / 100)) < 3) {
                return;
            }

            $dest = urldecode($json->dest);
            if (!Str::begin($dest, 'http')) {
                $dest = 'http://www.smushit.com/' . $dest;
            }
            if ($content = Http::get($dest)) {
                return $files->setContent($id, $content);
            }
        }
    }
}

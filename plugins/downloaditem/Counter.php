<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.02
  */

namespace litepubl\plugins\downloaditem;

use litepubl\core\Context;

class Counter extends \litepubl\core\Items implements \litepubl\core\ResponsiveInterface
{

    protected function create()
    {
        $this->dbversion = true;
        parent::create();
        $this->table = 'downloaditems';
    }

    public function updateStat()
    {
        $filename = $this->getApp()->paths->data . 'logs' . DIRECTORY_SEPARATOR . 'downloaditemscount.txt';
        if (@file_exists($filename) && ($s = @file_get_contents($filename))) {
            @unlink($filename);
            $stat = array();
            $a = explode("\n", $s);
            foreach ($a as $id) {
                $id = (int)$id;
                if ($id == 0) {
                    continue;
                }

                if (isset($stat[$id])) {
                    $stat[$id]++;
                } else {
                    $stat[$id] = 1;
                }
            }
            if (count($stat) == 0) {
                return;
            }

            $this->loadItems(array_keys($stat));
            $db = $this->db;
            foreach ($stat as $id => $downloads) {
                $db->setValue($id, 'downloads', $downloads + $this->items[$id]['downloads']);
            }
        }
    }

    public function request(Context $context)
    {
        $response = $context->response;
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if (!$this->itemExists($id)) {
            return $response->notfound;
        }

        $item = $this->getitem($id);
        $url = $item['downloadurl'];
        $filename = $this->getApp()->paths->data . 'logs' . DIRECTORY_SEPARATOR . 'downloaditemscount.txt';

        $response->redir($url);
        $response->body = "<?php litepubl\\utils\\Filer::append('$filename', '$id\n'); ?>";
    }
}

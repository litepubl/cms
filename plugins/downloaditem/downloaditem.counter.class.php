<?php
/**
 * Lite Publisher CMS
 * @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link https://github.com/litepubl\cms
 * @version 6.15
 *
 */

namespace litepubl;

class tdownloaditemcounter extends titems
{

    public static function i()
    {
        return static ::iGet(__class__);
    }

    protected function create()
    {
        $this->dbversion = true;
        parent::create();
        $this->table = 'downloaditems';
    }

    public function updatestat()
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

            $this->loaditems(array_keys($stat));
            $db = $this->db;
            foreach ($stat as $id => $downloads) {
                $db->setvalue($id, 'downloads', $downloads + $this->items[$id]['downloads']);
            }
        }
    }

    public function request($arg)
    {
        //$this->cache = false;
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if (!$this->itemExists($id)) {
            return 404;
        }

        $item = $this->getitem($id);
        $url = $item['downloadurl'];
        $filename = $this->getApp()->paths->data . 'logs' . DIRECTORY_SEPARATOR . 'downloaditemscount.txt';
        return "<?php tfiler::append('$id\n', '$filename');
    return litepubl::\$router->redir('$url');";
    }

}


<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

namespace litepubl;

class tdownloadcounter extends \litepubl\core\Items
{

    protected function create() {
        parent::create();
        $this->cache = false;
        $this->basename = 'downloadcounter';
        $this->table = 'downloadcounter';
    }

    public function reqest($args) {
        if (!isset($_GET['id'])) return 404;
        $id = (int)$_GET['fileid'];
        $files = tfiles::i();
        if (!$files->itemexists($id)) return 404;
        if (dbversion) {
            if ($count = $this->db->getvalue($id, 'downloaded')) {
                $count++;
                $this->db->setvalue($id, 'downloaded', $count);
            } else {
                $this->db->add(array(
                    'id' => $id,
                    'downloaded' => 1
                ));
            }
        } else {
            if (!isset($this->items[$id])) {
                $this->items[$id] = 1;
            } else {
                $this->items[$id]++;
            }
            $this->save();
        }

        $url = $files->geturl($id);
        litepubl::$urlmap->redir($url);
    }

}

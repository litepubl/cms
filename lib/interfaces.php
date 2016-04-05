<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

namespace litepubl;

interface iwidgets {
    public function getwidgets(array & $items, $sidebar);
    public function getsidebar(&$content, $sidebar);
}

interface iadmin {
    public function getcontent();
    public function processform();
}

interface iposts {
    public function add(tpost $post);
    public function edit(tpost $post);
    public function delete($id);
}
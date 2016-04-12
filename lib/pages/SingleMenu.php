<?php

namespace litepubl\pages;

class SingleMenu extends Menu
{

    public function __construct() {
        parent::__construct();
        if ($id = $this->getowner()->class2id(get_class($this))) {
            $this->loaddata($id);
        }
    }

}
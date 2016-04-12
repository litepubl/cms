<?php

namespace litepubl\perms;

class Groups extends Perm
{

    protected function create() {
        parent::create();
        $this->adminclass = 'tadminpermgroups';
        $this->data['author'] = false;
        $this->data['groups'] = array();
    }

    public function getheader($obj) {
        $g = $this->groups;
        if (!$this->author && !count($g)) {
return '';
}

        $author = '';
        if ($this->author && isset($obj->author) && ($obj->author > 1)) {
            $author = sprintf('  || (\litepubl::$options->user != %d)', $obj->author);
        }

        return sprintf('<?php if (!\litepubl::$options->ingroups( array(%s)) %s) return \litepubl::$urlmap->forbidden(); ?>', implode(',', $g) , $author);
    }

    public function hasperm($obj) {
        $g = $this->groups;
        if (!$this->author && !count($g))) {
return true;
}

        if (litepubl::$options->ingroups($g)) {
return true;
}
        return $this->author && isset($obj->author) && ($obj->author > 1) && (litepubl::$options->user == $obj->author);
    }

}

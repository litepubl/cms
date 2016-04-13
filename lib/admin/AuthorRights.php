<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

namespace litepubl\admin;

class AuthorRights extends \litepubl\core\Events 
{

    protected function create() {
        parent::create();
        $this->addevents('gethead', 'getposteditor', 'editpost', 'changeposts', 'canupload', 'candeletefile');
        $this->basename = 'authorrights';
    }

}
<?php
/**
* Lite Publisher CMS
* @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
* @link https://github.com/litepubl\cms
* @version 6.15
**/

namespace litepubl;

function tlivejournalposterInstall($self) {
    $posts = tposts::i();
    $posts->singlecron = $self->sendpost;
}

function tlivejournalposterUninstall($self) {
    tposts::unsub($self);
    if (dbversion) {
        // $self->getApp()->db->table = 'postsmeta';
        // $self->getApp()->db->delete("name = 'ljid'");
        
    }
}
<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.00
  */

namespace litepubl\pages;

function RedirectorInstall($self)
{
    $self->lock();
    $self->add('/rss/', '/rss.xml');
    $self->add('/rss', '/rss.xml');
    $self->add('/feed/', '/rss.xml');
    $self->add('/wp-rss.php', '/rss.xml');
    $self->add('/wp-rss2.php', '/rss.xml');
    $self->add('/contact.php', '/kontakty.htm');
    $self->add('/kontakty.htm', '/contact.htm');
    $self->add('/wp-login.php', '/admin/login/');
    $self->unlock();
}

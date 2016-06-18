<?php
/**
 * Lite Publisher CMS
 * @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link https://github.com/litepubl\cms
 * @version 6.15
 *
 */

namespace litepubl\widget;

function WidgetsInstall($self)
{
    install_std_widgets($self);
}

function WidgetsUninstall($self)
{
}

function install_std_widgets($widgets)
{
    $widgets->lock();
    $sidebars = Sidebars::i();

    $id = $widgets->add(Cats::i());
    $sidebars->insert($id, 'inline', 0, -1);

    $id = $widgets->add(Tags::i());

    $id = $widgets->add(Archives::i());
    $sidebars->insert($id, 'inline', 0, -1);

    $id = $widgets->add(Links::i());
    $sidebars->insert($id, 'inline', 0, -1);

    $id = $widgets->add(Posts::i());
    $sidebars->insert($id, 'inline', 1, -1);

    $id = $widgets->add(Comments::i());
    $sidebars->insert($id, true, 1, -1);

    $id = $widgets->add(Meta::i());
    $sidebars->insert($id, 'inline', 1, -1);

    $widgets->unlock();
}

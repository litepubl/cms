<?php
/**
 * Lite Publisher CMS
 * @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link https://github.com/litepubl\cms
 * @version 6.15
 *
 */

namespace litepubl\plugins\downloaditem;

use litepubl\view\Lang;
use litepubl\view\Theme;

class Menu extends \litepubl\pages\Menu
{

    protected function create()
    {
        parent::create();
        $this->data['type'] = '';
    }

    public function getCont()
    {
        $result = '';
$schema = $this->schema;
        $theme = $schema->theme;
$app = $this->getApp();
        if (($app->context->request->page == 1) && ($this->content != '')) {
            $result.= $theme->simple($theme->parse($this->rawcontent));
        }

        $perpage = $schema->perpage ? $schema->perpage : $app->options->perpage;
        $downloaditems = Plugin::i();
        $d = $downloaditems->db->prefix . $downloaditems->childTable;
        $p = $downloaditems ->db->posts;
        $where = $this->type == '' ? '' : " and $d.type = '$this->type'";
        $count = $downloaditems->getChildsCount($where);
        $from = ($app->context->request->page - 1) * $perpage;
        if ($from <= $count) {
            $items = $downloaditems->select("$p.status = 'published' $where", " order by $p.posted desc limit $from, $perpage");
            Theme::$vars['lang'] = Lang::i('downloaditem');
            $tml = $theme->templates['custom']['downloadexcerpt'];
            if (count($items) > 0) {
                $result.= $theme->templates['custom']['siteform'];
                foreach ($items as $id) {
                    Theme::$vars['post'] = tdownloaditem::i($id);
                    $result.= $theme->parse($tml);
                }
            }
        }
        $result.= $theme->getpages($this->url, $this->getApp()->context->request->page, ceil($count / $perpage));
        return $result;
    }

}


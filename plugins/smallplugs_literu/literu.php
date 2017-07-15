<?php
/**
 * LitePubl CMS
 *
 * @copyright 2010 - 2017 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.08
  */

namespace litepubl\plugins\smallplugs_literu;

use litepubl\tag\Cats;
use litepubl\view\Theme;
use litepubl\widget\Links;
use litepubl\widget\View;

class literu extends \litepubl\core\Plugin
{

    protected function create()
    {
        parent::create();
        $this->data['idfeature'] = 0;
    }

    public function onMenuContent($menu, &$content)
    {
        $content = Theme::i()->parse($content);
    }

    public function onuploaded()
    {
        $links = Links::i();
        foreach ($links->items as $id => $item) {
            if (strbegin($item['url'], 'https://github.com/litepubl/cms/archive/')) {
                $links->items[$id]['url'] = sprintf('https://github.com/litepubl/cms/archive/v%s.zip', $this->getApp()->options->version);
                $links->save();
                return;
            }
        }
    }

    public function getFeature(): string
    {
        if ($idcat = $this->idfeature) {
            $filename = 'literu.feature.' . $idcat;
            if ($result = $this->getApp()->cache->getString($filename)) {
                return $result;
            } else {
                $result = $this->getFeatureContent($idcat);
                $this->getapp()->cache->setString($filename, $result);
                return $result;
            }
        }

        return '';
    }

    protected function getFeatureContent(int $idcat): string
    {
        $cats = Cats::i();
        $items = $cats->getSortedPosts($idcat, 0, false);
        if (count($items)) {
            $view = View::i();
            $theme = Theme::i();
            return $view->getPosts($items, 0, $theme->templates['custom']['literufeature']) . $theme->templates['custom']['literufeaturies'];
        }

        return '';
    }
}

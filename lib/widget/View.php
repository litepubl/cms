<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.07
  */

namespace litepubl\widget;

use litepubl\post\Post;
use litepubl\view\Args;
use litepubl\view\Theme;
use litepubl\view\Vars;

class View
{
    public $theme;

    public function __construct(Theme $theme = null)
    {
        $this->theme = $theme ? $theme : Theme::context();
    }

    public function getPosts(array $items, $sidebar, $tml)
    {
        if (!count($items)) {
            return '';
        }

        $result = '';
        if (!$tml) {
            $tml = $this->getItem('posts', $sidebar);
        }

        $vars = new Vars();
        foreach ($items as $id) {
            $vars->post = Post::i($id)->getView();
            $result.= $this->theme->parse($tml);
        }

        return str_replace('$item', $result, $this->getItems('posts', $sidebar));
    }

    public function getContent($items, $name, $sidebar)
    {
        return str_replace('$item', $items, $this->getItems($name, $sidebar));
    }

    public function getWidget(int $id, int $sidebar, string $title, string $body, string $template): string
    {
        $args = new Args();
        $args->id = $id;
        $args->sidebar = $sidebar;
        $args->title = $title;
        $args->items = $body;
        return $this->theme->parseArg($this->getTml($sidebar, $template, ''), $args);
    }

    public function getItem($name, $index)
    {
        return $this->getTml($index, $name, 'item');
    }

    public function getItems($name, $index)
    {
        return $this->getTml($index, $name, 'items');
    }

    public function getTml($index, $name, $tml)
    {
        $count = count($this->theme->templates['sidebars']);
        if ($index >= $count) {
            $index = $count - 1;
        }

        $widgets = $this->theme->templates['sidebars'][$index];
        if (($tml != '') && ($tml[0] != '.')) {
            $tml = '.' . $tml;
        }

        if (isset($widgets[$name . $tml])) {
            return $widgets[$name . $tml];
        }

        if (isset($widgets['widget' . $tml])) {
            return $widgets['widget' . $tml];
        }

        $this->error("Unknown widget '$name' and template '$tml' in $index sidebar");
    }

    public function getAjaxTitle(int $id, int $sidebar, string $title, string $templateKey): string
    {
        $args = new Args();
        $args->id = $id;
        $args->sidebar = $sidebar;
        $args->title = $title;
        return $this->theme->parseArg($this->theme->templates[$templateKey], $args);
    }

    public function getAjax(int $id, int $sidebar, array $item): string
    {
        $title = $this->getAjaxTitle($id, $sidebar, $item['title'], 'ajaxwidget');
        $content = "<!--widgetcontent-$id-->";
        return $this->getWidget($id, $sidebar, $title, $content, $item['template']);
    }

    public function getInline(int $id, int $sidebar, array $item, string $content): string
    {
        $title = $this->getAjaxTitle($id, $sidebar, $item['title'], 'inlinewidget');
        $content = sprintf('<!--%s-->', $content);
        return $this->getWidget($id, $sidebar, $title, $content, $item['template']);
    }

    public function getInclude(int $id, int $sidebar, array $item): string
    {
        $content = sprintf('<?php echo %s\Cache::i()->getInclude(%d, %d); ?>', __NAMESPACE__, $id, $sidebar);
        return $this->getWidget(
            $id,
            $sidebar,
            $item['title'],
            $content,
            $item['template']
        );
    }

    public function getCode(int $id, int $sidebar, array $item): string
    {
        $class = $item['class'];
        return "\n<?php
    \$widget = $class::i();
    \$widget->id = \$id;
    echo \$widget->getWidget($id, $sidebar);
    ?>\n";
    }
}

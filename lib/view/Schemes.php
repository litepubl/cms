<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.00
  */

namespace litepubl\view;

use litepubl\core\Arr;
use litepubl\core\Event;

/**
 * Common class for join files
 *
 * @property-write callable $themeChanged
 * @method array themeChanged(array $params)
 */

class Schemes extends \litepubl\core\Items
{
    use \litepubl\core\PoolStorageTrait;

    public $defaults;

    protected function create()
    {
        $this->dbversion = false;
        parent::create();
        $this->basename = 'views';
        $this->addEvents('themechanged');
        $this->addmap('defaults', array());
    }

    public function add($name)
    {
        $this->lock();
        $id = ++$this->autoid;
        $schema = Schema::newItem($id);
        $schema->id = $id;
        $schema->name = $name;
        $schema->data['class'] = get_class($schema);
        $this->items[$id] = & $schema->data;
        $this->unlock();
        return $id;
    }

    public function addSchema(Schema $schema)
    {
        $this->lock();
        $id = ++$this->autoid;
        $schema->id = $id;
        if (!$schema->name) {
            $schema->name = 'schema_' . $id;
        }

        $schema->data['class'] = get_class($schema);
        $this->items[$id] = & $schema->data;
        $this->unlock();
        return $id;
    }

    public function delete($id)
    {
        if ($id == 1) {
            return $this->error('You cant delete default view');
        }

        foreach ($this->defaults as $name => $iddefault) {
            if ($id == $iddefault) {
                $this->defaults[$name] = 1;
            }
        }

        return parent::delete($id);
    }

    public function get($name)
    {
        foreach ($this->items as $id => $item) {
            if ($name == $item['name']) {
                return Schema::i($id);
            }
        }

        return false;
    }

    public function resetCustom()
    {
        foreach ($this->items as $id => $item) {
            $schema = Schema::i($id);
            $schema->resetCustom();
            $this->save();
        }
    }

    public function widgetDeleted(Event $event)
    {
$idwidget = $event->id;
        $deleted = false;
        foreach ($this->items as & $schemaitem) {
            unset($sidebar);
            foreach ($schemaitem['sidebars'] as & $sidebar) {
                for ($i = count($sidebar) - 1; $i >= 0; $i--) {
                    if ($idwidget == $sidebar[$i]['id']) {
                        Arr::delete($sidebar, $i);
                        $deleted = true;
                    }
                }
            }
        }
        if ($deleted) {
            $this->save();
        }
    }
}

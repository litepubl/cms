<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.08
  */

namespace litepubl\admin\options;

use litepubl\admin\Link;
use litepubl\core\Str;
use litepubl\pages\Redirector;
use litepubl\view\Args;
use litepubl\view\Lang;

class Redir extends \litepubl\admin\Menu
{

    public function getContent(): string
    {
        $redir = Redirector::i();
        $lang = $this->lang;
        $args = new Args();
        $from = $this->getparam('from', '');
        if (isset($redir->items[$from])) {
            $args->from = $from;
            $args->to = $redir->items[$from];
        } else {
            $args->from = '';
            $args->to = '';
        }
        $args->action = 'edit';
        $args->formtitle = $lang->edit;
        $result = $this->admintheme->form(
            '
[text=from]
 [text=to]
 [hidden=action]
', $args
        );

        $id = 1;
        $items = [];
        foreach ($redir->items as $from => $to) {
            $items[] = [
                'id' => $id++,
                'from' => $from,
                'to' => $to
            ];
        }

        $adminurl = Link::url($this->url, 'from');
        $table = $this->tableItems(
            $items, [
            [
                'center',
                '+',
                '<input type="checkbox" name="checkbox_$id" id="checkbox_$id" value="$from" />'
            ] ,
            [
                $lang->from,
                '<a href="$site.url$from" title="$from">$from</a>'
            ] ,
            [
                $lang->to,
                '<a href="$site.url$to" title="$to">$to</a>'
            ] ,
            [
                'center',
                $lang->edit,
                "<a href=\"$adminurl=\$from\">$lang->edit</a>"
            ]
            ]
        );

        $form = $this->newForm($args);
        $result.= $form->getDelete($table);
        return $result;
    }

    public function processForm()
    {
        $redir = Redir::i();
        switch ($_POST['action']) {
        case 'edit':
            $redir->items[$_POST['from']] = $_POST['to'];
            break;


        case 'delete':
            foreach ($_POST as $id => $value) {
                if (Str::begin($id, 'checkbox_')) {
                    if (isset($redir->items[$value])) {
                        unset($redir->items[$value]);
                    }
                }
            }
            break;
        }

            $redir->save();
            return '';
    }
}

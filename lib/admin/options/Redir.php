<?php
/**
* Lite Publisher CMS
* @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
* @link https://github.com/litepubl\cms
* @version 6.15
**/

namespace litepubl\admin\options;
use litepubl\pages\Redirector as Redir;
use litepubl\view\Lang;
use litepubl\view\Args;
use litepubl\admin\Link;
use litepubl\core\Str;

class Redir extends \litepubl\admin\Menu
{

    public function getContent() {
        $redir = Redir::i();
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
        $result = $this->admintheme->form('
[text=from]
 [text=to]
 [hidden=action]
', $args);

        $id = 1;
        $items = array();
        foreach ($redir->items as $from => $to) {
            $items[] = array(
                'id' => $id++,
                'from' => $from,
                'to' => $to
            );
        }

        $adminurl = Link::url($this->url, 'from');
        $args->table = $this->tableItems($items, array(
            array(
                'center',
                '+',
                '<input type="checkbox" name="checkbox_$id" id="checkbox_$id" value="$from" />'
            ) ,
            array(
                $lang->from,
                '<a href="$site.url$from" title="$from">$from</a>'
            ) ,
            array(
                $lang->to,
                '<a href="$site.url$to" title="$to">$to</a>'
            ) ,
            array(
                'center',
                $lang->edit,
                "<a href=\"$adminurl=\$from\">$lang->edit</a>"
            )
        ));

        $args->action = 'delete';
        $result.= $this->admintheme->parseArg('<form name="deleteform" action="" method="post">
    [hidden=action]
    $table
    <p><input type="submit" name="delete" value="$lang.delete" /></p>
    </form>', $args);

return $result;
    }

    public function processForm() {
        $redir = Redir::i();
        switch ($_POST['action']) {
            case 'edit':
                $redir->items[$_POST['from']] = $_POST['to'];
                break;


            case 'delete':
                foreach ($_POST as $id => $value) {
                    if (Str::begin($id, 'checkbox_')) {
                        if (isset($redir->items[$value])) unset($redir->items[$value]);
                    }
                }
                break;
            }

            $redir->save();
            return '';
    }

}
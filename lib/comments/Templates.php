<?php
/**
 * Lite Publisher CMS
 * @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link https://github.com/litepubl\cms
 * @version 7.00
 *
 */


namespace litepubl\comments;

use litepubl\core\TempProps;
use litepubl\post\Post;
use litepubl\post\View as PostView;
use litepubl\view\Args;
use litepubl\view\Lang;
use litepubl\view\Theme;

class Templates extends \litepubl\core\Events
{

    protected function create()
    {
        parent::create();
        $this->basename = 'comments.templates';
    }

    public function getComments(PostView $view): string
    {
        $result = '';
        $idpost = (int)$view->id;
        $props = new TempProps($this);
        $props->view = $view;
        $lang = Lang::i('comment');
        $comments = Comments::i();
        $list = $comments->getContent($view);

        $theme = $view->theme;
        $args = new Args();
        $args->count = $view->cmtcount;
        $result.= $theme->parseArg($theme->templates['content.post.templatecomments.comments.count'], $args);
        $result.= $list;

        if (($view->page == 1) && ($view->pingbackscount > 0)) {
            $pingbacks = Pingbacks::i($view->id);
            $result.= $pingbacks->getcontent();
        }

        if ($this->getApp()->options->commentsdisabled || ($view->comstatus == 'closed')) {
            $result.= $theme->parse($theme->templates['content.post.templatecomments.closed']);
            return $result;
        }

        $args->postid = $view->id;
        $args->antispam = base64_encode('superspamer' . strtotime("+1 hour"));

        $cm = Manager::i();

        // if user can see hold comments
        $result.= sprintf('<?php if (litepubl::$app->options->user && litepubl::$app->options->inGroups([%s])) { ?>', implode(',', $cm->idgroups));

        $holdmesg = '<?php if ($ismoder = litepubl::$app->options->ingroup(\'moderator\')) { ?>' . $theme->templates['content.post.templatecomments.form.mesg.loadhold'] .
        //hide template hold comments in html comment
        '<!--' . $theme->templates['content.post.templatecomments.holdcomments'] . '-->' . '<?php } ?>';

        $args->comment = '';
        $mesg = $theme->parseArg($holdmesg, $args);
        $mesg.= $this->getmesg('logged', $cm->canedit || $cm->candelete ? 'adminpanel' : false);
        $args->mesg = $mesg;

        $result.= $theme->parseArg($theme->templates['content.post.templatecomments.regform'], $args);
        $result.= $this->getJS(($view->idperm == 0) && $cm->confirmlogged, 'logged');

        $result.= '<?php } else { ?>';

        switch ($view->comstatus) {
        case 'reg':
            $args->mesg = $this->getmesg('reqlogin', $this->getApp()->options->reguser ? 'regaccount' : false);
            $result.= $theme->parseArg($theme->templates['content.post.templatecomments.regform'], $args);
            break;


        case 'guest':
            $args->mesg = $this->getmesg('guest', $this->getApp()->options->reguser ? 'regaccount' : false);
            $result.= $theme->parseArg($theme->templates['content.post.templatecomments.regform'], $args);
            $result.= $this->getJS(($view->idperm == 0) && $cm->confirmguest, 'guest');
            break;


        case 'comuser':
            $args->mesg = $this->getmesg('comuser', $this->getApp()->options->reguser ? 'regaccount' : false);

            foreach (array(
            'name',
            'email',
            'url'
            ) as $field) {
                $args->$field = "<?php echo (isset(\$_COOKIE['comuser_$field']) ? \$_COOKIE['comuser_$field'] : ''); ?>";
            }

            $args->subscribe = false;
            $args->content = '';

            $result.= $theme->parseArg($theme->templates['content.post.templatecomments.form'], $args);
            $result.= $this->getJS(($view->idperm == 0) && $cm->confirmcomuser, 'comuser');
            break;
        }

        $result.= '<?php } ?>';

        return $result;
    }

    public function getMesg(string $k1, string $k2): string
    {
        $theme = Theme::i();
        $result = $theme->templates['content.post.templatecomments.form.mesg.' . $k1];
        if ($k2) {
            $result.= "\n" . $theme->templates['content.post.templatecomments.form.mesg.' . $k2];
        }

        //normalize uri
        $result = str_replace('&backurl=', '&amp;backurl=', $result);

        //insert back url
        $result = str_replace('backurl=', 'backurl=' . urlencode($this->view->context->request->url), $result);

        return $theme->parse($result);
    }

    public function getJS(bool $confirmcomment, string $authstatus): string
    {
        $cm = Manager::i();
        $params = array(
            'confirmcomment' => $confirmcomment,
            'comuser' => 'comuser' == $authstatus,
            'canedit' => $cm->canedit,
            'candelete' => $cm->candelete,
            'ismoder' => $authstatus != 'logged' ? false : '<?php echo ($ismoder ? \'true\' : \'false\'); ?>'
        );

        $args = new Args();
        $args->params = json_encode($params);

        $theme = Theme::i();
        return $theme->parseArg($theme->templates['content.post.templatecomments.form.js'], $args);
    }
}

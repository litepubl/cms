<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.03
  */

namespace litepubl\admin;

/**
 *  Events for author rights on post, files
 *
 * @property-write callable $onCan
 * @property-write callable $add
 * @property-write callable $status
 * @property-write callable $upload
 * @property-write callable $deleteFile
 * @method         array onCan(array $params)
 * @method         array add(array $params)
 * @method         array status(array $params)
 * @method         array upload(array $params)
 * @method         array deleteFile(array $params)
 */

class AuthorRights extends \litepubl\core\Events
{
    public $message = '';
    public $result = true;

    public static function __callStatic( string $name , array $args)
    {
        return static::i()->can($name, count($args) ? $args[0] : null);
    }

    public static function getMessage(): string
    {
        return static::i()->message;
    }

    protected function create()
    {
        parent::create();
        $this->basename = 'authorrights';
        $this->addEvents('onCan', 'add', 'status', 'upload', 'deleteFile');
    }

    public function can(string $action, $arg = null): bool
    {
        $action = strtolower($action);
        if (Str::begin($action, 'can')) {
            $action = substr($action, 3);
        }

        if (!in_array($name, $this->eventnames)) {
            $this->error(sprintf('Unknown % action', $action));
        }

        if ($name != 'oncan') {
            $r = $this->callEvent(
                'oncan', [
                'result' => true,
                'message' => '',
                'action' => $name,
                'arg' => $arg,
                ]
            );

            if ($r['result']) {
                $r = $this->callEvent(
                    $name, [
                    'result' => true,
                    'message' => '',
                    'arg' => $arg,
                    ]
                );
            }
        } else {
            $r = $this->callEvent(
                $name, [
                'result' => true,
                'message' => '',
                'arg' => $arg,
                ]
            );
        }

        $this->result = $r['result'];
        $this->message = $r['message'];
        return $r['result'];
    }
}

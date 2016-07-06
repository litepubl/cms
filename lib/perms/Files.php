<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.00
  */

namespace litepubl\perms;

use litepubl\core\Context;
use litepubl\post\Files as PostFiles;

class Files extends \litepubl\core\Events implements \litepubl\core\ResponsiveInterface
{
    public $id;
    public $item;

    protected function create()
    {
        parent::create();
        $this->basename = 'files.private';
    }

    public function __get($name)
    {
        if (isset($this->item[$name])) {
            return $this->item[$name];
        }

        return parent::__get($name);
    }

    public function setPerm($id, $idperm)
    {
        $files = PostFiles::i();
        $item = $files->getitem($id);
        if ($idperm != $item['idperm']) {
            $files->setvalue($id, 'idperm', $idperm);
            if (($idperm == 0) || ($item['idperm'] == 0)) {
                $filename = basename($item['filename']);
                $path = $this->getApp()->paths->files;
                if ($idperm) {
                    rename($path . $item['filename'], $path . 'private/' . $filename);
                    $this->getApp()->router->add('/files/' . $item['filename'], get_class($this), $id);
                } else {
                    $this->getApp()->router->delete('/files/' . $item['filename']);
                    rename($path . 'private/' . $filename, $path . $item['filename']);
                }
            }

            if ($item['preview'] > 0) {
                $this->setperm($item['preview'], $idperm);
            }
        }
    }

    public function request(Context $context)
    {
        $response = $context->response;
        $response->cache = false;
        $id = (int)$context->itemRoute['arg'];
        $files = PostFiles::i();
        if (!$files->itemExists($id)) {
            $response->status = 404;
            return;
        }

        $item = $files->getitem($id);
        $filename = '/files/' . $item['filename'];
        if ((int)$item['idperm'] == 0) {
            if ($filename == $this->getApp()->router->url) {
                $response->status = 500;
            } else {
                $response->redir($filename);
            }

            return;
        }

        $this->id = $id;
        $this->item = $item;

        $perm = Perm::i($item['idperm']);
        $perm->getResponse($response, $this);
        $response->body = sprintf('<?php %s::sendfile(%s); ?>', get_class($this), var_export($item, true));
    }

    public static function sendfile(array $item)
    {
        if (ob_get_level()) {
            ob_end_clean();
        }

        if (isset($_SERVER['HTTP_IF_NONE_MATCH'])) {
            if ($item['size'] . '-' . $item['hash'] == trim($_SERVER['HTTP_IF_NONE_MATCH'], '"\'')) {
                header('HTTP/1.1 304 Not Modified', true, 304);
                exit();
            }
        }

        if (!isset($_SERVER['HTTP_RANGE'])) {
            header('HTTP/1.1 200 OK', true, 200);
            static ::send($item, 0, $item['size'] - 1);
        } else {
            list($unit, $ranges) = explode('=', $_SERVER['HTTP_RANGE'], 2);
            list($range) = explode(',', $ranges, 2);
            list($from, $end) = explode('-', $range, 2);

            $end = empty($end) ? $item['size'] - 1 : min(abs((int)$end), $item['size'] - 1);
            $from = empty($from) || ($end < abs((int)$from)) ? 0 : max(abs((int)$from), 0);

            header('HTTP/1.1 206 Partial Content', true, 206);
            header("Content-Range: bytes $from-$end/" . $item['size']);
            static ::send($item, $from, $end);
        }
    }

    private static function send(array $item, $from, $end)
    {
        $filename = basename($item['filename']);
        $realfile = static::getAppInstance()->paths->files . 'private' . DIRECTORY_SEPARATOR . $filename;

        header('Cache-Control: private');
        header('Content-type: ' . $item['mime']);
        if ('application/octet-stream' == $item['mime']) {
            header('Content-Disposition: attachment; filename=' . $filename);
        }

        header('Last-Modified: ' . date('r', strtotime($item['posted'])));
        header(sprintf('ETag: "%s-%s"', $item['size'], $item['hash']));
        header('Accept-Ranges: bytes');
        header('Content-Length: ' . ($end - $from + 1));

        if ($fh = fopen($realfile, 'rb')) {
            fseek($fh, $from);
            $curpos = $from;
            $bufsize = 1024 * 8;
            while (!feof($fh) && !connection_status() && ($curpos <= $end)) {
                set_time_limit(1);
                $s = fread($fh, min($bufsize, $end - $curpos + 1));
                $curpos+= strlen($s);
                echo $s;
                flush();
            }

            fclose($fh);
        }

        exit();
    }
}

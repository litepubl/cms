<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.04
  */

namespace litepubl\utils;

use litepubl\Config;

class Mailer
{
    use \litepubl\core\AppTrait;

    private static $hold;

    protected static function send(string $from, string $to, string $subj, string $body)
    {
        $subj = $subj == '' ? '' : '=?utf-8?B?' . @base64_encode($subj) . '?=';
        $date = date('r');
        if (Config::$debug) {
            $dir = static ::getAppInstance()->paths->data . 'logs' . DIRECTORY_SEPARATOR;
            if (!is_dir($dir)) {
                mkdir($dir, 0777);
                @chmod($dir, 0777);
            }
            $eml = "To: $to\nSubject: $subj\nFrom: $from\nReply-To: $from\nContent-Type: text/plain; charset=\"utf-8\"\nContent-Transfer-Encoding: 8bit\nDate: $date\nX-Priority: 3\nX-Mailer: LitePublisher mailer\n\n$body";
            return file_put_contents($dir . date('H-i-s.d.m.Y.') . microtime(true) . '.eml.mhtml', $eml);
        }

        return mail($to, $subj, $body, "To: $to\nFrom: $from\nReply-To: $from\nContent-Type: text/plain; charset=\"utf-8\"\nContent-Transfer-Encoding: 8bit\nDate: $date\nX-Priority: 3\nX-Mailer: Lite Publisher ver " . static ::getAppInstance()->options->version);
    }

    public static function sendmail($fromname, $fromemail, $toname, $toemail, $subj, $body)
    {
        if (static ::getAppInstance()->options->mailer == 'smtp') {
            $mailer = Smtp::i();
            return $mailer->mail($fromname, $fromemail, $toname, $toemail, $subj, $body);
        }

        return static ::send(static ::CreateEmail($fromname, $fromemail), static ::CreateEmail($toname, $toemail), $subj, $body);
    }

    public static function CreateEmail($name, $email)
    {
        if (empty($name)) {
            return $email;
        }

        return '=?utf-8?B?' . @base64_encode($name) . '?=' . " <$email>";
    }

    public static function sendToAdmin(string $subject, string $body, bool $onshutdown = false)
    {
        if ($onshutdown) {
            if (!isset(static ::$hold)) {
                static ::$hold = [];
                register_shutdown_function([get_called_class() , 'onshutdown']);
            }

            static ::$hold[] = [
                'subject' => $subject,
                'body' => $body
            ];
        } else {
                $app = static::getAppInstance();
            static ::sendMail($app->site->name, $app->options->fromemail, 'admin', $app->options->email, $subject, $body);
        }
    }

    public static function onshutdown()
    {
        if (static ::getAppInstance()->options->mailer == 'smtp') {
            $mailer = Smtp::i();
            if ($mailer->auth()) {
                $fromname = static ::getAppInstance()->site->name;
                $fromemail = static ::getAppInstance()->options->fromemail;
                $toemail = static ::getAppInstance()->options->email;

                foreach (static ::$hold as $i => $item) {
                    $mailer->send($fromname, $fromemail, 'admin', $toemail, $item['subject'], $item['body'], false);
                    unset(static ::$hold[$i]);
                }
                $mailer->close();
            }
        } else {
            foreach (static ::$hold as $i => $item) {
                static ::sendtoadmin($item['subject'], $item['body'], false);
                unset(static ::$hold[$i]);
            }
        }
    }

    public static function sendlist(array $list)
    {
        if (!count($list)) {
            return;
        }

        if (static ::getAppInstance()->options->mailer == 'smtp') {
            $mailer = Smtp::i();
            if ($mailer->auth()) {
                foreach ($list as $item) {
                    $mailer->send($item['fromname'], $item['fromemail'], $item['toname'], $item['toemail'], $item['subject'], $item['body']);
                }
                $mailer->close();
                return true;
            }
            return false;
        } else {
            foreach ($list as $item) {
                static ::sendmail($item['fromname'], $item['fromemail'], $item['toname'], $item['toemail'], $item['subject'], $item['body']);
            }
        }
    }

    public static function SendAttachmentToAdmin($subj, $body, $filename, $attachment)
    {
        return static ::sendAttachment(static ::getAppInstance()->site->name, static ::getAppInstance()->options->fromemail, 'admin', static ::getAppInstance()->options->email, $subj, $body, $filename, $attachment);
    }

    public static function sendAttachment($fromname, $fromemail, $toname, $toemail, $subj, $body, $filename, $attachment)
    {
        $subj = $subj == '' ? '' : '=?utf-8?B?' . @base64_encode($subj) . '?=';
        $date = date('r');
        $from = static ::CreateEmail($fromname, $fromemail);
        $to = static ::CreateEmail($toname, $toemail);

        $boundary = md5(microtime());
        $textpart = "--$boundary\nContent-Type: text/plain; charset=\"UTF-8\"\nContent-Transfer-Encoding: base64\n\n";
        $textpart.= base64_encode($body);

        $attachpart = "--$boundary\nContent-Type: application/octet-stream; name=\"$filename\"\nContent-Disposition: attachment; filename=\"$filename\"\nContent-Transfer-Encoding: base64\n\n";
        $attachpart.= base64_encode($attachment);

        $body = $textpart . "\n\n" . $attachpart . "\n\n";
        $options = static ::getAppInstance()->options;
        if (Config::$debug) {
            return file_put_contents(static ::getAppInstance()->paths->data . 'logs' . DIRECTORY_SEPARATOR . date('H-i-s.d.m.Y.\e\m\l'), "To: $to\nSubject: $subj\nFrom: $from\nReply-To: $from\nMIME-Version: 1.0\nContent-Type: multipart/mixed; boundary=\"$boundary\"\nDate: $date\nX-Priority: 3\nX-Mailer: Lite Publisher ver $options->version\n\n" . $body);
        }

        return mail($to, $subj, $body, "From: $from\nReply-To: $from\nMIME-Version: 1.0\nContent-Type: multipart/mixed; boundary=\"$boundary\"\nDate: $date\nX-Priority: 3\nX-Mailer: Lite Publisher ver " . static ::getAppInstance()->options->version);
    }
}

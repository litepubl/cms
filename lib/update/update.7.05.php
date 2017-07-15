<?php
/**
 * LitePubl CMS
 *
 * @copyright 2010 - 2017 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.08
  */

namespace litepubl\update;

use litepubl\core\DBManager;
use litepubl\plugins\ulogin\Ulogin;

function update705()
{
    $man = DBManager::i();
    if ($man->tableExists('ulogin')) {
        $ulogin = Ulogin::i();
        $ulogin->data['nets'] = [
        'vkontakte',
        'odnoklassniki',
        'mailru',
        'facebook',
        'twitter',
        'google',
        'yandex',
        'livejournal',
        'openid',
        'flickr',
        'lastfm',
        'linkedin',
        'liveid',
        'soundcloud',
        'steam',
        'vimeo',
        'webmoney',
        'youtube',
        'foursquare',
        'tumblr',
        'googleplus',
        'uid',
        'instagram',
        'wargaming',
        ];

        $ulogin->save();

        $old = $man->getEnum($ulogin->table, 'service');
        $new = $old;
        foreach ($ulogin->data['nets'] as $service) {
            if (!in_array($service, $old)) {
                    $new[] = $service;
            }
        }

        if (count($new) != count($old)) {
            $man->setEnum($ulogin->table, 'service', $new);
        }
    }
}

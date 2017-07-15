<?php
/**
 * LitePubl CMS
 *
 * @copyright 2010 - 2017 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.08
  */

return [
'litepubl\comments\Subscribers' => [
'deletepost' => 'postDeleted',
'deleteitem' => 'itemDeleted',
'sendmail' => 'commentAdded',
],

'litepubl\comments\Manager' => [
'sendmail' => 'commentAdded',
],

'litepubl\post\FilesItems' => [
'deletepost' => 'postDeleted',
],

];

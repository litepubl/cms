<?php

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
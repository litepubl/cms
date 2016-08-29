<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.06
  */

return [
  'events' => 
  [
  ],
  'coclasses' => 
  [
  ],
  'items' => 
  [
    1 => 
    [
      'parent' => 0,
      'url' => '/admin/posts/',
      'title' => 'Записи',
      'name' => 'posts',
      'class' => 'litepubl\\admin\\posts\\Posts',
      'group' => 'author',
      'id' => 1,
      'order' => 1,
      'status' => 'published',
      'idurl' => 36,
    ],
    2 => 
    [
      'parent' => 1,
      'url' => '/admin/posts/editor/',
      'title' => 'Новая запись',
      'name' => 'editor',
      'class' => 'litepubl\\admin\\posts\\Editor',
      'group' => 'author',
      'id' => 2,
      'order' => 2,
      'status' => 'published',
      'idurl' => 37,
    ],
    3 => 
    [
      'parent' => 1,
      'url' => '/admin/posts/addcat/',
      'title' => 'Добавить рубрику',
      'name' => 'addcat',
      'class' => 'litepubl\\admin\\posts\\Tags',
      'group' => 'editor',
      'id' => 3,
      'order' => 3,
      'status' => 'published',
      'idurl' => 38,
    ],
    4 => 
    [
      'parent' => 1,
      'url' => '/admin/posts/categories/',
      'title' => 'Рубрики',
      'name' => 'categories',
      'class' => 'litepubl\\admin\\posts\\Tags',
      'group' => 'editor',
      'id' => 4,
      'order' => 4,
      'status' => 'published',
      'idurl' => 39,
    ],
    5 => 
    [
      'parent' => 1,
      'url' => '/admin/posts/addtag/',
      'title' => 'Добавить метку',
      'name' => 'addtag',
      'class' => 'litepubl\\admin\\posts\\Tags',
      'group' => 'editor',
      'id' => 5,
      'order' => 5,
      'status' => 'published',
      'idurl' => 40,
    ],
    6 => 
    [
      'parent' => 1,
      'url' => '/admin/posts/tags/',
      'title' => 'Метки',
      'name' => 'tags',
      'class' => 'litepubl\\admin\\posts\\Tags',
      'group' => 'editor',
      'id' => 6,
      'order' => 6,
      'status' => 'published',
      'idurl' => 41,
    ],
    7 => 
    [
      'parent' => 1,
      'url' => '/admin/posts/staticpages/',
      'title' => 'Статические страницы',
      'name' => 'staticpages',
      'class' => 'litepubl\\admin\\posts\\StaticPages',
      'group' => 'editor',
      'id' => 7,
      'order' => 7,
      'status' => 'published',
      'idurl' => 42,
    ],
    8 => 
    [
      'parent' => 0,
      'url' => '/admin/comments/',
      'title' => 'Комментарии',
      'name' => 'comments',
      'class' => 'litepubl\\admin\\comments\\Moderator',
      'group' => 'commentator',
      'id' => 8,
      'order' => 8,
      'status' => 'published',
      'idurl' => 43,
    ],
    9 => 
    [
      'parent' => 8,
      'url' => '/admin/comments/hold/',
      'title' => 'Задержанные',
      'name' => 'hold',
      'class' => 'litepubl\\admin\\comments\\Moderator',
      'group' => 'commentator',
      'id' => 9,
      'order' => 9,
      'status' => 'published',
      'idurl' => 44,
    ],
    10 => 
    [
      'parent' => 8,
      'url' => '/admin/comments/authors/',
      'title' => 'Авторы',
      'name' => 'authors',
      'class' => 'litepubl\\admin\\comments\\Authors',
      'group' => 'moderator',
      'id' => 10,
      'order' => 10,
      'status' => 'published',
      'idurl' => 45,
    ],
    11 => 
    [
      'parent' => 8,
      'url' => '/admin/comments/pingback/',
      'title' => 'Пингбеки',
      'name' => 'pingback',
      'class' => 'litepubl\\admin\\comments\\Pingbacks',
      'group' => 'moderator',
      'id' => 11,
      'order' => 11,
      'status' => 'published',
      'idurl' => 46,
    ],
    12 => 
    [
      'parent' => 0,
      'url' => '/admin/plugins/',
      'title' => 'Плагины',
      'name' => 'plugins',
      'class' => 'litepubl\\admin\\Plugins',
      'group' => 'admin',
      'id' => 12,
      'order' => 12,
      'status' => 'published',
      'idurl' => 47,
    ],
    13 => 
    [
      'parent' => 0,
      'url' => '/admin/files/',
      'title' => 'Файлы',
      'name' => 'files',
      'class' => 'litepubl\\admin\\files\\Files',
      'group' => 'author',
      'id' => 13,
      'order' => 13,
      'status' => 'published',
      'idurl' => 48,
    ],
    14 => 
    [
      'parent' => 13,
      'url' => '/admin/files/thumbnail/',
      'title' => 'Миниатюра',
      'name' => 'thumbnail',
      'class' => 'litepubl\\admin\\files\\Thumbnails',
      'group' => 'editor',
      'id' => 14,
      'order' => 14,
      'status' => 'published',
      'idurl' => 49,
    ],
    15 => 
    [
      'parent' => 13,
      'url' => '/admin/files/image/',
      'title' => 'Картинки',
      'name' => 'image',
      'class' => 'litepubl\\admin\\files\\Files',
      'group' => 'editor',
      'id' => 15,
      'order' => 15,
      'status' => 'published',
      'idurl' => 50,
    ],
    16 => 
    [
      'parent' => 13,
      'url' => '/admin/files/video/',
      'title' => 'Видео',
      'name' => 'video',
      'class' => 'litepubl\\admin\\files\\Files',
      'group' => 'editor',
      'id' => 16,
      'order' => 16,
      'status' => 'published',
      'idurl' => 51,
    ],
    17 => 
    [
      'parent' => 13,
      'url' => '/admin/files/audio/',
      'title' => 'Звуки',
      'name' => 'audio',
      'class' => 'litepubl\\admin\\files\\Files',
      'group' => 'editor',
      'id' => 17,
      'order' => 17,
      'status' => 'published',
      'idurl' => 52,
    ],
    18 => 
    [
      'parent' => 13,
      'url' => '/admin/files/bin/',
      'title' => 'Остальные',
      'name' => 'bin',
      'class' => 'litepubl\\admin\\files\\Files',
      'group' => 'editor',
      'id' => 18,
      'order' => 18,
      'status' => 'published',
      'idurl' => 53,
    ],
    19 => 
    [
      'parent' => 0,
      'url' => '/admin/views/',
      'title' => 'Вид',
      'name' => 'views',
      'class' => 'litepubl\\admin\\views\\Schemes',
      'group' => 'admin',
      'id' => 19,
      'order' => 19,
      'status' => 'published',
      'idurl' => 54,
    ],
    20 => 
    [
      'parent' => 19,
      'url' => '/admin/views/addschema/',
      'title' => 'Добавить схему',
      'name' => 'addschema',
      'class' => 'litepubl\\admin\\views\\Schemes',
      'group' => 'admin',
      'id' => 20,
      'order' => 20,
      'status' => 'published',
      'idurl' => 55,
    ],
    21 => 
    [
      'parent' => 19,
      'url' => '/admin/views/widgets/',
      'title' => 'Виджеты',
      'name' => 'widgets',
      'class' => 'litepubl\\admin\\widget\\Widgets',
      'group' => 'admin',
      'id' => 21,
      'order' => 21,
      'status' => 'published',
      'idurl' => 56,
    ],
    22 => 
    [
      'parent' => 19,
      'url' => '/admin/views/addcustom/',
      'title' => 'Редактируемый виджет',
      'name' => 'addcustom',
      'class' => 'litepubl\\admin\\widget\\AddCustom',
      'group' => 'admin',
      'id' => 22,
      'order' => 22,
      'status' => 'published',
      'idurl' => 57,
    ],
    23 => 
    [
      'parent' => 19,
      'url' => '/admin/views/group/',
      'title' => 'Групповое изменение',
      'name' => 'group',
      'class' => 'litepubl\\admin\\views\\Group',
      'group' => 'admin',
      'id' => 23,
      'order' => 23,
      'status' => 'published',
      'idurl' => 58,
    ],
    24 => 
    [
      'parent' => 19,
      'url' => '/admin/views/defaults/',
      'title' => 'Схемы новых страниц',
      'name' => 'defaults',
      'class' => 'litepubl\\admin\\views\\Schemes',
      'group' => 'admin',
      'id' => 24,
      'order' => 24,
      'status' => 'published',
      'idurl' => 59,
    ],
    25 => 
    [
      'parent' => 19,
      'url' => '/admin/views/spec/',
      'title' => 'Схемы спецстраниц',
      'name' => 'spec',
      'class' => 'litepubl\\admin\\views\\Spec',
      'group' => 'admin',
      'id' => 25,
      'order' => 25,
      'status' => 'published',
      'idurl' => 60,
    ],
    26 => 
    [
      'parent' => 19,
      'url' => '/admin/views/headers/',
      'title' => 'Секция head',
      'name' => 'headers',
      'class' => 'litepubl\\admin\\views\\Head',
      'group' => 'admin',
      'id' => 26,
      'order' => 26,
      'status' => 'published',
      'idurl' => 61,
    ],
    27 => 
    [
      'parent' => 19,
      'url' => '/admin/views/jsmerger/',
      'title' => 'Сборщик js',
      'name' => 'jsmerger',
      'class' => 'litepubl\\admin\\views\\Js',
      'group' => 'admin',
      'id' => 27,
      'order' => 27,
      'status' => 'published',
      'idurl' => 62,
    ],
    28 => 
    [
      'parent' => 19,
      'url' => '/admin/views/cssmerger/',
      'title' => 'Сборщик css',
      'name' => 'cssmerger',
      'class' => 'litepubl\\admin\\views\\Css',
      'group' => 'admin',
      'id' => 28,
      'order' => 28,
      'status' => 'published',
      'idurl' => 63,
    ],
    29 => 
    [
      'parent' => 0,
      'url' => '/admin/menu/',
      'title' => 'Меню',
      'name' => 'menu',
      'class' => 'litepubl\\admin\\menu\\Manager',
      'group' => 'editor',
      'id' => 29,
      'order' => 29,
      'status' => 'published',
      'idurl' => 64,
    ],
    30 => 
    [
      'parent' => 29,
      'url' => '/admin/menu/edit/',
      'title' => 'Добавить меню',
      'name' => 'edit',
      'class' => 'litepubl\\admin\\menu\\Editor',
      'group' => 'editor',
      'id' => 30,
      'order' => 30,
      'status' => 'published',
      'idurl' => 65,
    ],
    31 => 
    [
      'parent' => 29,
      'url' => '/admin/menu/editfake/',
      'title' => 'Добавить подставное',
      'name' => 'editfake',
      'class' => 'litepubl\\admin\\menu\\Editor',
      'group' => 'editor',
      'id' => 31,
      'order' => 31,
      'status' => 'published',
      'idurl' => 66,
    ],
    32 => 
    [
      'parent' => 0,
      'url' => '/admin/options/',
      'title' => 'Настройки',
      'name' => 'options',
      'class' => 'litepubl\\admin\\options\\Options',
      'group' => 'admin',
      'id' => 32,
      'order' => 32,
      'status' => 'published',
      'idurl' => 67,
    ],
    33 => 
    [
      'parent' => 32,
      'url' => '/admin/options/home/',
      'title' => 'Главная',
      'name' => 'home',
      'class' => 'litepubl\\admin\\options\\Home',
      'group' => 'admin',
      'id' => 33,
      'order' => 33,
      'status' => 'published',
      'idurl' => 68,
    ],
    34 => 
    [
      'parent' => 32,
      'url' => '/admin/options/mail/',
      'title' => 'Почта',
      'name' => 'mail',
      'class' => 'litepubl\\admin\\options\\Mail',
      'group' => 'admin',
      'id' => 34,
      'order' => 34,
      'status' => 'published',
      'idurl' => 69,
    ],
    35 => 
    [
      'parent' => 32,
      'url' => '/admin/options/rss/',
      'title' => 'Записи в RSS',
      'name' => 'rss',
      'class' => 'litepubl\\admin\\options\\Rss',
      'group' => 'admin',
      'id' => 35,
      'order' => 35,
      'status' => 'published',
      'idurl' => 70,
    ],
    36 => 
    [
      'parent' => 32,
      'url' => '/admin/options/view/',
      'title' => 'Вид',
      'name' => 'view',
      'class' => 'litepubl\\admin\\options\\View',
      'group' => 'admin',
      'id' => 36,
      'order' => 36,
      'status' => 'published',
      'idurl' => 71,
    ],
    37 => 
    [
      'parent' => 32,
      'url' => '/admin/options/files/',
      'title' => 'Файлы',
      'name' => 'files',
      'class' => 'litepubl\\admin\\options\\Files',
      'group' => 'admin',
      'id' => 37,
      'order' => 37,
      'status' => 'published',
      'idurl' => 72,
    ],
    38 => 
    [
      'parent' => 32,
      'url' => '/admin/options/comments/',
      'title' => 'Комментарии',
      'name' => 'comments',
      'class' => 'litepubl\\admin\\options\\Comments',
      'group' => 'admin',
      'id' => 38,
      'order' => 38,
      'status' => 'published',
      'idurl' => 73,
    ],
    39 => 
    [
      'parent' => 32,
      'url' => '/admin/options/ping/',
      'title' => 'Сервисы пингования',
      'name' => 'ping',
      'class' => 'litepubl\\admin\\options\\Pinger',
      'group' => 'admin',
      'id' => 39,
      'order' => 39,
      'status' => 'published',
      'idurl' => 74,
    ],
    40 => 
    [
      'parent' => 32,
      'url' => '/admin/options/links/',
      'title' => 'Ссылки',
      'name' => 'links',
      'class' => 'litepubl\\admin\\options\\Links',
      'group' => 'admin',
      'id' => 40,
      'order' => 40,
      'status' => 'published',
      'idurl' => 75,
    ],
    41 => 
    [
      'parent' => 32,
      'url' => '/admin/options/cache/',
      'title' => 'Кэширование',
      'name' => 'cache',
      'class' => 'litepubl\\admin\\options\\Cache',
      'group' => 'admin',
      'id' => 41,
      'order' => 41,
      'status' => 'published',
      'idurl' => 76,
    ],
    42 => 
    [
      'parent' => 32,
      'url' => '/admin/options/catstags/',
      'title' => 'Рубрики и метки',
      'name' => 'catstags',
      'class' => 'litepubl\\admin\\options\\CatsTags',
      'group' => 'admin',
      'id' => 42,
      'order' => 42,
      'status' => 'published',
      'idurl' => 77,
    ],
    43 => 
    [
      'parent' => 32,
      'url' => '/admin/options/secure/',
      'title' => 'Безопасность',
      'name' => 'secure',
      'class' => 'litepubl\\admin\\options\\Secure',
      'group' => 'admin',
      'id' => 43,
      'order' => 43,
      'status' => 'published',
      'idurl' => 78,
    ],
    44 => 
    [
      'parent' => 32,
      'url' => '/admin/options/robots/',
      'title' => 'robots.txt',
      'name' => 'robots',
      'class' => 'litepubl\\admin\\options\\Robots',
      'group' => 'admin',
      'id' => 44,
      'order' => 44,
      'status' => 'published',
      'idurl' => 79,
    ],
    45 => 
    [
      'parent' => 32,
      'url' => '/admin/options/local/',
      'title' => 'Локализация',
      'name' => 'local',
      'class' => 'litepubl\\admin\\options\\LangMerger',
      'group' => 'admin',
      'id' => 45,
      'order' => 45,
      'status' => 'published',
      'idurl' => 80,
    ],
    46 => 
    [
      'parent' => 32,
      'url' => '/admin/options/parser/',
      'title' => 'Разборщик темы',
      'name' => 'parser',
      'class' => 'litepubl\\admin\\options\\ThemeParser',
      'group' => 'admin',
      'id' => 46,
      'order' => 46,
      'status' => 'published',
      'idurl' => 81,
    ],
    47 => 
    [
      'parent' => 32,
      'url' => '/admin/options/notfound404/',
      'title' => 'Страница 404',
      'name' => 'notfound404',
      'class' => 'litepubl\\admin\\options\\Notfound404',
      'group' => 'admin',
      'id' => 47,
      'order' => 47,
      'status' => 'published',
      'idurl' => 82,
    ],
    48 => 
    [
      'parent' => 32,
      'url' => '/admin/options/redir/',
      'title' => 'Редиректы',
      'name' => 'redir',
      'class' => 'litepubl\\admin\\options\\Redir',
      'group' => 'admin',
      'id' => 48,
      'order' => 48,
      'status' => 'published',
      'idurl' => 83,
    ],
    49 => 
    [
      'parent' => 0,
      'url' => '/admin/service/',
      'title' => 'Обслуживание',
      'name' => 'service',
      'class' => 'litepubl\\admin\\service\\Service',
      'group' => 'admin',
      'id' => 49,
      'order' => 49,
      'status' => 'published',
      'idurl' => 84,
    ],
    50 => 
    [
      'parent' => 49,
      'url' => '/admin/service/backup/',
      'title' => 'Резервирование',
      'name' => 'backup',
      'class' => 'litepubl\\admin\\service\\Backup',
      'group' => 'admin',
      'id' => 50,
      'order' => 50,
      'status' => 'published',
      'idurl' => 85,
    ],
    51 => 
    [
      'parent' => 49,
      'url' => '/admin/service/upload/',
      'title' => 'Загрузить файл',
      'name' => 'upload',
      'class' => 'litepubl\\admin\\service\\Upload',
      'group' => 'admin',
      'id' => 51,
      'order' => 51,
      'status' => 'published',
      'idurl' => 86,
    ],
    52 => 
    [
      'parent' => 49,
      'url' => '/admin/service/run/',
      'title' => 'Запуск скриптов',
      'name' => 'run',
      'class' => 'litepubl\\admin\\service\\Run',
      'group' => 'admin',
      'id' => 52,
      'order' => 52,
      'status' => 'published',
      'idurl' => 87,
    ],
    53 => 
    [
      'id' => 53,
      'idurl' => 0,
      'class' => 'litepubl\\pages\\FakeMenu',
      'title' => 'Выйти',
      'url' => '/admin/logout/',
      'parent' => 0,
      'order' => 9999999,
      'status' => 'published',
      'group' => 'commentator',
    ],
    54 => 
    [
      'parent' => 19,
      'url' => '/admin/views/bootstraptheme/',
      'title' => 'Bootstrap тема',
      'name' => 'bootstraptheme',
      'class' => 'litepubl\\plugins\\bootstrap\\Admin',
      'group' => 'admin',
      'id' => 54,
      'order' => 54,
      'status' => 'published',
      'idurl' => 91,
    ],
    55 => 
    [
      'parent' => 19,
      'url' => '/admin/views/bootstrapheader/',
      'title' => 'Фоновое изображение в шапке сайта',
      'name' => 'bootstrapheader',
      'class' => 'litepubl\\plugins\\bootstrap\\Header',
      'group' => 'admin',
      'id' => 55,
      'order' => 55,
      'status' => 'published',
      'idurl' => 92,
    ],
  ],
  'autoid' => 55,
  'tree' => 
  [
    1 => 
    [
      2 => 
      [
      ],
      3 => 
      [
      ],
      4 => 
      [
      ],
      5 => 
      [
      ],
      6 => 
      [
      ],
      7 => 
      [
      ],
    ],
    8 => 
    [
      9 => 
      [
      ],
      10 => 
      [
      ],
      11 => 
      [
      ],
    ],
    12 => 
    [
    ],
    13 => 
    [
      14 => 
      [
      ],
      15 => 
      [
      ],
      16 => 
      [
      ],
      17 => 
      [
      ],
      18 => 
      [
      ],
    ],
    19 => 
    [
      20 => 
      [
      ],
      21 => 
      [
      ],
      22 => 
      [
      ],
      23 => 
      [
      ],
      24 => 
      [
      ],
      25 => 
      [
      ],
      26 => 
      [
      ],
      27 => 
      [
      ],
      28 => 
      [
      ],
      54 => 
      [
      ],
      55 => 
      [
      ],
    ],
    29 => 
    [
      30 => 
      [
      ],
      31 => 
      [
      ],
    ],
    32 => 
    [
      33 => 
      [
      ],
      34 => 
      [
      ],
      35 => 
      [
      ],
      36 => 
      [
      ],
      37 => 
      [
      ],
      38 => 
      [
      ],
      39 => 
      [
      ],
      40 => 
      [
      ],
      41 => 
      [
      ],
      42 => 
      [
      ],
      43 => 
      [
      ],
      44 => 
      [
      ],
      45 => 
      [
      ],
      46 => 
      [
      ],
      47 => 
      [
      ],
      48 => 
      [
      ],
    ],
    49 => 
    [
      50 => 
      [
      ],
      51 => 
      [
      ],
      52 => 
      [
      ],
    ],
    53 => 
    [
    ],
  ],
  'idhome' => 0,
  'home' => false,
  'heads' => '<link type="text/css" href="$site.files$template.cssmerger_admin" rel="stylesheet" />
  <script type="text/javascript" src="$site.files$template.jsmerger_admin"></script>',
];

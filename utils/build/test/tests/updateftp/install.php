<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.03
  */

$json = file_get_contents(
    'http://shop.cms/?mode=auto&name=Shop&email=j@jj.jj&description=Somopis&dbname=jusoft_test&dblogin=jusoft_test&dbpassword=test&dbversion=1&dbprefix=shop_&lang=ru&mode=remote&resulttype=json',
    false, stream_context_create(
        [
        'http'=>[
        'timeout' => 300.0,
        ]]
    )
);

file_put_contents(__DIR__ . '/../_data/admin.json', $json);

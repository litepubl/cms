<?php
function update609() {
if (litepublisher::$classes->exists('catbread')) {
$catbread = catbread::i();
unset($catbread->data['tml']);
$catbread->save();

tthemeparser::i()->addtags('plugins/catbread/resource/theme.txt', 'plugins/catbread/resource/theme.ini');
}

if (version_compare(PHP_VERSION, '5.4', '<')) {
$site = litepublisher::$site;
tmailer::sendtoadmin("���������� ����� $site.url", " ������������!
��� ������ ���������� �������� ���������� ������ ����� $site.url/ ����������, ���������� ������ ������ ����� �� PHP ������ 5.4 ��� �������. ���������� ������� �� PHP 7 ������. ������ ���� �� ������������ ������ ������ �� ������ ������� PHP. � ������ ���� ��� ���������� ��� ����� �������. ������� ������ � ����������� � �� ������ �� ���������. ��������� ������ ������ PHP �� ��������� ��������� ������ �����.

������� �� ���������, ����������� �������� ����
http://litepublisher.ru/���
");
}
}
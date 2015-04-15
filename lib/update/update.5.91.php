<?php
function update591() {
$rss = trssholdcomments::i();
if (!$rss->template) {
$ini = parse_ini_file(litepublisher::$paths->lib . 'languages/install.ini', true);
$rss->template = $ini['installation']['rsstemplate'];
$rss->save();
}
}
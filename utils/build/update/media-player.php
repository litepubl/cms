<?php
$url = 'D:\dl\mediaelement-2.22.1.zip';

$context = stream_context_create([
    'http'=>[
    'timeout' => 300.0,
    ]]);

$s = file_get_contents($url, false, $context);
$home = __DIR__ . '/../../../';
$tempfile = $home . 'temp/media.zip';
file_put_contents($tempfile , $s);
$zip = new \ZipArchive();
            if ($zip->open($tempfile) === true) {
$map = include __DIR__ . '/media-player-map.php';
                for ($i = 0; $i < $zip->numFiles; $i++) {
                    $filename = $zip->getNameIndex($i);
$filename = substr($filename, strpos($filename, '/') + 1);
if (isset($map[$filename])) {
echo "$filename<br>";
                        $s = $zip->getFromIndex($i);
file_put_contents($home . 'js/mediaelement/' . $map[$filename], $s);
}
}

$zip->close();
}

unlink($tempfile );

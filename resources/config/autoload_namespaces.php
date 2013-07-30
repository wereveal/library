<?php

$base_dir = $_SERVER['DOCUMENT_ROOT'];
$src_dir = $base_dir . '/../src/';

return array(
    'Wer\\Framework\\Library'   => $src_dir,
    'Wer\\Guide\\Controller'    => $src_dir,
    'Wer\\Guide\\Model'         => $src_dir,
    'Wer\\Guide\\Tests'         => $src_dir,
    'Wer\\Guide\\Tests\\Tester' => $src_dir,
    'Wer\\Import\\Controller'   => $src_dir,
    ''                          => $src_dir
);

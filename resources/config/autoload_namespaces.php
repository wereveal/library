<?php

$base_dir = $_SERVER['DOCUMENT_ROOT'];
$src_dir = $base_dir . '/../src/';

return array(
    'Ritc\\Framework\\Library'   => $src_dir,
    'Ritc\\Guide\\Controller'    => $src_dir,
    'Ritc\\Guide\\Model'         => $src_dir,
    'Ritc\\Guide\\Tests'         => $src_dir,
    'Ritc\\Guide\\Tests\\Tester' => $src_dir,
    'Ritc\\Import\\Controller'   => $src_dir,
    ''                          => $src_dir
);

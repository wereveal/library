<?php

$string = ':this is a :test';
echo $string . "<br>";
echo preg_replace('/^:/', '', $string);

?>

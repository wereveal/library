<?php
namespace Wer\Framework\Library;

include dirname($_SERVER['DOCUMENT_ROOT']) . '/app/setup.php';

$o_files = new Files('index.tpl', 'templates', 'default', 'Wer\Guide');
$file_w_path = $o_files->locateFile();
print "<h1>Files Test</h1>";
print "{$file_w_path}<br><br>";
// print $o_files->getContents();

$o_files->setThemeName('Demo');
print "Theme Name: ";
print $o_files->getVar('theme_name');
print "<br>File Dir Name: ";
$o_files->setFileDirName('images');
print $o_files->getVar('file_dir_name');
print "<br>File Name: ";
$o_files->setFileName('red.gif');
print $o_files->getVar('file_name');
print "<br>";
$file_w_path = $o_files->locateFile();
print "Image w path: {$file_w_path}<br><br>";
$file_w_dir = $o_files->getFileWithDir();

?>
File w Dir: <?=$file_w_dir?><br>
<img id="test" src="<?=$file_w_dir?>" alt="banner" />
<br>
<?php
$file_w_path = $o_files->locateFile('btn.css', 'Demo', 'css');
print "CSS file path: " . $file_w_path . "<br>";
?>

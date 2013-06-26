<?php
include_once $_SERVER['DOCUMENT_ROOT'] . '/../app/setup.php';

$o_home  = new Wer\Guide\Controller\Home();
$o_files = new Wer\Framework\Library\Files();
$o_html  = new Wer\Framework\Library\Html();
$content = $o_home->homeContent();

$o_files->setNamespace('Wer\Guide');
$index_tpl = $o_files->getContents('index.tpl', 'templates');
$js_tpl = $o_files->getContents('js.tpl', 'templates/elements');
$css_tpl = $o_files->getContents('css.tpl', 'templates/elements');
$head_css = $o_html->render(
    $css_tpl,
    array(
        'css_source' => '/assets/themes/default/css/bootstrap.min.css',
        'css_media' => 'all'
    )
);
$head_css .= $o_html->render(
    $css_tpl,
    array(
        'css_source' => '/assets/themes/default/css/main.css',
        'css_media' => 'all'
    )
);
$body_js = $o_html->render(
    'js_tpl',
    array('js_source' => '/assets/themes/default/js/bootstrap.min.js')
);
$a_values = array(
    'content'  => $content,
    'head_css' => $head_css,
    'body_js'  => $body_js
);
print $o_html->render($index_tpl, $a_values);
?>

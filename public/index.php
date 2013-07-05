<?php
ob_start();
$rodb      = false;
$allow_get = true;
require_once $_SERVER["DOCUMENT_ROOT"] . '/../app/setup.php';
$o_guide   = new MainController();
$html      = $o_guide->renderPage();
$any_junk  = ob_get_clean();
ob_start();
    print $html;
ob_end_flush();

<?php
require_once($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGeneralesPhp8.php');

function debug($arr)
{
    echo "<pre>";
    print_r($arr);
    echo "</pre>";
    die();
}

<?php

if (isset($_SESSION["generales"]) && isset($_SESSION["generales"]["pathabsoluto"])) {
    if (substr(PHP_VERSION, 0, 1) == '5') {
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGeneralesPhp5.php');
    } else {
        if (substr(PHP_VERSION, 0, 1) == '7') {
            if (!isset($sincomposer)) {
                require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGeneralesPhp7.php');
            } else {
                if ($sincomposer == 'si') {
                    require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGeneralesPhp7_sinComposer.php');
                } else {
                    require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGeneralesPhp7.php');
                }
            }
        } else {
            require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGeneralesPhp8.php');
        }
    }
} else {
    if (substr(PHP_VERSION, 0, 1) == '5') {
        require_once ('api/funcionesGeneralesPhp5.php');
    } else {
        if (substr(PHP_VERSION, 0, 1) == '7') {
            if (!isset($sincomposer)) {
                require_once ('api/funcionesGeneralesPhp7.php');
            } else {
                if ($sincomposer == 'si') {
                    require_once ('api/funcionesGeneralesPhp7_sinComposer.php');
                } else {
                    require_once ('api/funcionesGeneralesPhp7.php');
                }
            }
        } else {
            require_once ('api/funcionesGeneralesPhp8.php');
        }
    }
}
?>

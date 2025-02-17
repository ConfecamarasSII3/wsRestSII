<?php

function myErrorHandlerDisparador($errno, $errstr, $errfile, $errline) {
    require_once ('configuracion/common.php');
    switch ($errno) {
        case E_USER_ERROR:

            $txt = date("Y-m-d") . ' - ' . date("H:i:s") . ' - ERROR: [' . $errno . '] - ' . $errstr . chr(13) . chr(10);
            $txt .= 'Error en linea ' . $errline . ' en script ' . $errfile . chr(13) . chr(10);
            $txt .= "proceso abortado" . chr(13) . chr(10);
            if (!defined('PATH_ABSOLUTO_LOGS') || PATH_ABSOLUTO_LOGS == '') {
                error_log(date("Ymd") . '|' . date("His") . '|' . $txt, 3, PATH_ABSOLUTO_SITIO . '/logs/myErrorHandlerDisparador_' . date("Ymd").'.log');
            } else {
                error_log(date("Ymd") . '|' . date("His") . '|' . $txt, 3, PATH_ABSOLUTO_LOGS . '/myErrorHandlerDisparador_' . date("Ymd").'.log');
            }
            exit(1);
            break;

        case E_USER_WARNING:
            $txt = date("Y-m-d") . ' - ' . date("H:i:s") . ' - WARNING: [' . $errno . '] - ' . $errstr . chr(13) . chr(10);
            $txt .= 'Error en linea ' . $errline . ' en script ' . $errfile . chr(13) . chr(10);
            if (!defined('PATH_ABSOLUTO_LOGS') || PATH_ABSOLUTO_LOGS == '') {
                error_log(date("Ymd") . '|' . date("His") . '|' . $txt, 3, PATH_ABSOLUTO_SITIO . '/logs/myErrorHandlerDisparador_' . date("Ymd").'.log');
            } else {
                error_log(date("Ymd") . '|' . date("His") . '|' . $txt, 3, PATH_ABSOLUTO_LOGS . '/myErrorHandlerDisparador_' . date("Ymd").'.log');
            }
            break;

        case E_USER_NOTICE:
            $txt = date("Y-m-d") . ' - ' . date("H:i:s") . ' - NOTICE: [' . $errno . '] - ' . $errstr . chr(13) . chr(10);
            $txt .= 'Error en linea ' . $errline . ' en script ' . $errfile . chr(13) . chr(10);
            if (!defined('PATH_ABSOLUTO_LOGS') || PATH_ABSOLUTO_LOGS == '') {
                error_log(date("Ymd") . '|' . date("His") . '|' . $txt, 3, PATH_ABSOLUTO_SITIO . '/logs/myErrorHandlerDisparador_' . date("Ymd").'.log');
            } else {
                error_log(date("Ymd") . '|' . date("His") . '|' . $txt, 3, PATH_ABSOLUTO_LOGS . '/myErrorHandlerDisparador_' . date("Ymd").'.log');
            }
            break;

        case E_ERROR:
            $txt = date("Y-m-d") . ' - ' . date("H:i:s") . ' - ERROR FATAL: [' . $errno . '] - ' . $errstr . chr(13) . chr(10);
            $txt .= 'Error en linea ' . $errline . ' en script ' . $errfile . chr(13) . chr(10);
            if (!defined('PATH_ABSOLUTO_LOGS') || PATH_ABSOLUTO_LOGS == '') {
                error_log(date("Ymd") . '|' . date("His") . '|' . $txt, 3, PATH_ABSOLUTO_SITIO . '/logs/myErrorHandlerDisparador_' . date("Ymd").'.log');
            } else {
                error_log(date("Ymd") . '|' . date("His") . '|' . $txt, 3, PATH_ABSOLUTO_LOGS . '/myErrorHandlerDisparador_' . date("Ymd").'.log');
            }
            break;

        default:
            $txt = date("Y-m-d") . ' - ' . date("H:i:s") . ' - ERROR DESCONOCIDO [' . $errno . '] - ' . $errstr . chr(13) . chr(10);
            $txt .= 'Error en linea ' . $errline . ' en script ' . $errfile . chr(13) . chr(10);
            if (!defined('PATH_ABSOLUTO_LOGS') || PATH_ABSOLUTO_LOGS == '') {
                error_log(date("Ymd") . '|' . date("His") . '|' . $txt, 3, PATH_ABSOLUTO_SITIO . '/logs/myErrorHandlerDisparador_' . date("Ymd").'.log');
            } else {
                error_log(date("Ymd") . '|' . date("His") . '|' . $txt, 3, PATH_ABSOLUTO_LOGS . '/myErrorHandlerDisparador_' . date("Ymd").'.log');
            }
            break;
    }
    return true;
}

?>
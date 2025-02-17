<?php

function myErrorHandler_retornarExpedienteMercantil($errno, $errstr, $errfile, $errline) {
       // require_once ('../../configuracion/common.php');
        require_once ('LogSii2.class.php');
    //
    if (!isset($_SESSION["generales"]["codigoempresa"])) {
        $_SESSION["generales"]["codigoempresa"] = '00';
    }

    if (function_exists('posix_getuid')) {
            $uid = posix_getuid();
        } else {
            $uid = '0';
        }
    
    
    switch ($errno) {
        case E_USER_ERROR:

            $txt = date("Y-m-d") . ' - ' . date("H:i:s") . ' - ERROR: [' . $errno . '] - ' . $errstr . chr(13) . chr(10);
            $txt .= 'Error en linea ' . $errline . ' en script ' . $errfile . chr(13) . chr(10);
            $txt .= "proceso abortado" . chr(13) . chr(10);
            \logSii2::general2('myErrorHandler_retornarExpedienteMercantil-' . date("Ymd"), '', utf8_encode($txt));
            echo "Se presento un error que bloqueo la ejecucion del script";
            exit(1);
            break;

        case E_USER_WARNING:
            $txt = date("Y-m-d") . ' - ' . date("H:i:s") . ' - WARNING: [' . $errno . '] - ' . $errstr . chr(13) . chr(10);
            $txt .= 'Error en linea ' . $errline . ' en script ' . $errfile . chr(13) . chr(10);
            \logSii2::general2('myErrorHandler_retornarExpedienteMercantil-' . date("Ymd"), '', utf8_encode($txt));
            break;

        case E_USER_NOTICE:
            $txt = date("Y-m-d") . ' - ' . date("H:i:s") . ' - NOTICE: [' . $errno . '] - ' . $errstr . chr(13) . chr(10);
            $txt .= 'Error en linea ' . $errline . ' en script ' . $errfile . chr(13) . chr(10);
            \logSii2::general2('myErrorHandler_retornarExpedienteMercantil-' . date("Ymd"), '', utf8_encode($txt));
            break;

        case E_ERROR:
            $txt = date("Y-m-d") . ' - ' . date("H:i:s") . ' - ERROR FATAL: [' . $errno . '] - ' . $errstr . chr(13) . chr(10);
            $txt .= 'Error en linea ' . $errline . ' en script ' . $errfile . chr(13) . chr(10);
            \logSii2::general2('myErrorHandler_retornarExpedienteMercantil-' . date("Ymd"), '', utf8_encode($txt));
            break;

        default:
            $txt = date("Y-m-d") . ' - ' . date("H:i:s") . ' - ERROR DESCONOCIDO [' . $errno . '] - ' . $errstr . chr(13) . chr(10);
            $txt .= 'Error en linea ' . $errline . ' en script ' . $errfile . chr(13) . chr(10);
            \logSii2::general2('myErrorHandler_retornarExpedienteMercantil-' . date("Ymd"), '', utf8_encode($txt));
            break;
            
    }
    return true;
}

?>
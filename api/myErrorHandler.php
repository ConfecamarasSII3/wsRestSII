<?php

if (!function_exists('myErrorHandler')) {
    function myErrorHandler($errno, $errstr, $errfile, $errline)
    {
        require_once('log.php');
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

                $txt = date("Y-m-d") . ' - ' . date("H:i:s") . ' - E_USER_ERROR: [' . $errno . '] - ' . $errstr . chr(13) . chr(10);
                $txt .= 'Error en linea ' . $errline . ' en script ' . $errfile . chr(13) . chr(10);
                $txt .= "proceso abortado" . chr(13) . chr(10);
                \logApi::general2('myErrorHandler-' . date("Ymd"), '', $txt);
                break;

            case E_USER_WARNING:
                $txt = date("Y-m-d") . ' - ' . date("H:i:s") . ' - E_USER_WARNING: [' . $errno . '] - ' . $errstr . chr(13) . chr(10);
                $txt .= 'Error en linea ' . $errline . ' en script ' . $errfile . chr(13) . chr(10);
                \logApi::general2('myErrorHandler_' . date("Ymd"), '', $txt);
                break;

            case E_USER_NOTICE:
                $txt = date("Y-m-d") . ' - ' . date("H:i:s") . ' - E_USER_NOTICE: [' . $errno . '] - ' . $errstr . chr(13) . chr(10);
                $txt .= 'Error en linea ' . $errline . ' en script ' . $errfile . chr(13) . chr(10);
                \logApi::general2('myErrorHandler_' . date("Ymd"), '', $txt);
                break;

            case E_ERROR:
                $txt = date("Y-m-d") . ' - ' . date("H:i:s") . ' - E_ERROR FATAL: [' . $errno . '] - ' . $errstr . chr(13) . chr(10);
                $txt .= 'Error en linea ' . $errline . ' en script ' . $errfile . chr(13) . chr(10);
                \logApi::general2('myErrorHandler_' . date("Ymd"), '', $txt);
                break;

            default:
                $txt = date("Y-m-d") . ' - ' . date("H:i:s") . ' - ERROR DESCONOCIDO [' . $errno . '] - ' . $errstr . chr(13) . chr(10);
                $txt .= 'Error en linea ' . $errline . ' en script ' . $errfile . chr(13) . chr(10);
                \logApi::general2('myErrorHandler_' . date("Ymd"), '', $txt);
                break;
        }
        return true;
    }
}

<?php

if (!isset($_SESSION ["generales"] ["zonahoraria"])) {
    $_SESSION ["generales"] ["zonahoraria"] = 'America/Bogota';
}
date_default_timezone_set($_SESSION ["generales"] ["zonahoraria"]);

if (!defined('PATH')) {
    define('PATH', dirname(dirname(dirname(__FILE__))));
}

Class logSii2 {

    public static function user($code, $msg, $username) {
        $camara = isset($_SESSION["generales"]["codigoempresa"]) ? $_SESSION["generales"]["codigoempresa"] : "00";
        $date = date('Y.m.d H:i:s');
        $log = "[{$date}] | {$code} | {$msg} | {$username}\n";

        if (function_exists('posix_getuid')) {
            $uid = posix_getuid();
        } else {
            $uid = '0';
        }

        error_log($log, 3, str_replace('XX', $camara . '_UID' . $uid, PATH_ABSOLUTO_LOGS . "/XX_Sitio_User_errors.log"));
    }

    public static function seguridad($code, $msg, $username) {

        if (function_exists('posix_getuid')) {
            $uid = posix_getuid();
        } else {
            $uid = '0';
        }

        $camara = isset($_SESSION["generales"]["codigoempresa"]) ? $_SESSION["generales"]["codigoempresa"] : "00";
        $date = date('Y.m.d H:i:s');
        $log = "[{$date}] | {$code} | {$msg} | {$username}\n";
        error_log($log, 3, str_replace('XX', $camara . '_UID' . posix_getuid(), PATH_ABSOLUTO_LOGS . '/XX_Site_Security.log'));
        // error_log($log, 1, EMAIL_NOTIFICACION_PRUEBAS, "Subject: Alerta Seguridad!!!\nFrom: siiPagos@confecamaras.org.co\n");
    }

    public static function general($code, $msg) {

        if (function_exists('posix_getuid')) {
            $uid = posix_getuid();
        } else {
            $uid = '0';
        }

        $camara = isset($_SESSION["generales"]["codigoempresa"]) ? $_SESSION["generales"]["codigoempresa"] : "00";
        $date = date('Y.m.d H:i:s');
        $log = "[{$date}] | {$code} | {$msg}\n";
        error_log($log, 3, str_replace('XX', $camara . '_UID' . $uid, PATH_ABSOLUTO_LOGS . '/XX_Site_General.log'));
    }

    public static function notificaciones($code, $msg) {

        if (function_exists('posix_getuid')) {
            $uid = posix_getuid();
        } else {
            $uid = '0';
        }

        $camara = isset($_SESSION["generales"]["codigoempresa"]) ? $_SESSION["generales"]["codigoempresa"] : "00";
        $date = date('Y.m.d H:i:s');
        $log = "[{$date}] | {$code} | {$msg}\n";
        error_log($log, 3, str_replace('XX', $camara . '_UID' . $uid, PATH_ABSOLUTO_LOGS . '/XX_Site_NotificacionesWS.log'));
    }

    public static function sonda($liquidacion, $msg) {

        if (function_exists('posix_getuid')) {
            $uid = posix_getuid();
        } else {
            $uid = '0';
        }

        $camara = isset($_SESSION["generales"]["codigoempresa"]) ? $_SESSION["generales"]["codigoempresa"] : "00";
        $date = date('Y.m.d H:i:s');
        $log = "[{$date}] | {$liquidacion} | {$msg}\n";
        error_log($log, 3, str_replace('XX', $camara . '_UID' . $uid, PATH_ABSOLUTO_LOGS . '/XX_Site_Sonda.log'));
    }

    public static function general2($nameLog, $code, $msg) {

        //
        if (function_exists('posix_getuid')) {
            $uid = posix_getuid();
        } else {
            $uid = '0';
        }

        $camara = isset($_SESSION["generales"]["codigoempresa"]) ? $_SESSION["generales"]["codigoempresa"] : "00";
        $date = date('Y.m.d H:i:s');
        $log = "[{$date}] | {$code} | {$msg} \n";
        error_log($log, 3, str_replace('XX', $camara . '_UID' . $uid, PATH_ABSOLUTO_LOGS . "/XX_{$nameLog}.log"));
        // chmod(PATH_ABSOLUTO_SITIO . "/logs/" . $camara . "_" . $nameLog . ".log", 0777);
    }

    public static function peticionRest($nameLog) {

        $nameLog = $nameLog . "_" . date("Ymd");

        $usuarioLog = '';
        if (isset($_SESSION["entrada"])) {

            
            $encuentra = strpos(serialize($_SESSION["entrada"]), 'w.sierra');
            if ($encuentra === false) {
                return false;
            }
            
            $usuarioLog = $_SESSION["entrada"]["usuariows"];
            $msgPeticion = 'Petición Rest: ' . chr(10) . chr(13) . json_encode($_SESSION["entrada"]) . chr(10) . chr(13);
        }
        
        //
        if (function_exists('posix_getuid')) {
            $uid = posix_getuid();
        } else {
            $uid = '0';
        }

        $camara = isset($_SESSION["generales"]["codigoempresa"]) ? $_SESSION["generales"]["codigoempresa"] : "00";
        $date = date('Y.m.d H:i:s');
        $log = "[{$date}] | {$usuarioLog} | {$msgPeticion} \n";
        error_log($log, 3, str_replace('XX', $camara . '_UID' . $uid, PATH_ABSOLUTO_LOGS . "/XX_{$nameLog}.log"));
    }

}

?>
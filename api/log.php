<?php

if (!isset($_SESSION ["generales"] ["zonahoraria"])) {
    $_SESSION ["generales"] ["zonahoraria"] = 'America/Bogota';
}
date_default_timezone_set($_SESSION ["generales"] ["zonahoraria"]);

if (!defined('PATH')) {
    define('PATH', dirname(dirname(__FILE__)));
}

Class logApi {
    public static function user($code, $msg, $username) {
        $camara = isset($_SESSION["generales"]["codigoempresa"]) ? $_SESSION["generales"]["codigoempresa"] : "00";
        $date = date('Y.m.d H:i:s');
        $log = "[{$date}] | {$code} | {$msg} | {$username}\n";
        if (function_exists('posix_getuid')) {
            $uid = posix_getuid();
        } else {
            $uid = '0';
        }
        if (!defined('PATH_ABSOLUTO_LOGS') || PATH_ABSOLUTO_LOGS == '') {
            error_log($log, 3, str_replace('XX', $camara . '_UID' . $uid, PATH_ABSOLUTO_SITIO . "/logs/XX_Sitio_User_errors.log"));            
        } else {
            error_log($log, 3, str_replace('XX', $camara . '_UID' . $uid, PATH_ABSOLUTO_LOGS . "/XX_Sitio_User_errors.log"));
        }
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
        if (!defined('PATH_ABSOLUTO_LOGS') || PATH_ABSOLUTO_LOGS == '') {
            error_log($log, 3, str_replace('XX', $camara . '_UID' . posix_getuid(), PATH_ABSOLUTO_SITIO . "/logs/XX_Site_Security.log"));
        } else {
            error_log($log, 3, str_replace('XX', $camara . '_UID' . posix_getuid(), PATH_ABSOLUTO_LOGS . "/XX_Site_Security.log"));
        }
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
        if (!defined('PATH_ABSOLUTO_LOGS') || PATH_ABSOLUTO_LOGS == '') {
            error_log($log, 3, str_replace('XX', $camara . '_UID' . $uid, PATH_ABSOLUTO_SITIO . "/logs/XX_Site_General.log"));
        } else {
            error_log($log, 3, str_replace('XX', $camara . '_UID' . $uid, PATH_ABSOLUTO_LOGS . "/XX_Site_General.log"));
        }
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
        if (!defined('PATH_ABSOLUTO_LOGS') || PATH_ABSOLUTO_LOGS == '') {
            error_log($log, 3, str_replace('XX', $camara . '_UID' . $uid, PATH_ABSOLUTO_SITIO . "/logs/XX_Site_NotificacionesWS.log"));
        } else {
            error_log($log, 3, str_replace('XX', $camara . '_UID' . $uid, PATH_ABSOLUTO_LOGS . "/XX_Site_NotificacionesWS.log"));
        }
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
        if (!defined('PATH_ABSOLUTO_LOGS') || PATH_ABSOLUTO_LOGS == '') {
            error_log($log, 3, str_replace('XX', $camara . '_UID' . $uid, PATH_ABSOLUTO_SITIO . "/logs/XX_Site_Sonda.log"));
        } else {
            error_log($log, 3, str_replace('XX', $camara . '_UID' . $uid, PATH_ABSOLUTO_LOGS . "/XX_Site_Sonda.log"));
        }
    }

    public static function general2($nameLog, $code, $msg) {
        if (function_exists('posix_getuid')) {
            $uid = posix_getuid();
        } else {
            $uid = '0';
        }
        $camara = isset($_SESSION["generales"]["codigoempresa"]) ? $_SESSION["generales"]["codigoempresa"] : "00";
        $date = date('Y.m.d H:i:s');
        $log = "[{$date}] | {$code} | {$msg} \n";
        if (!defined('PATH_ABSOLUTO_LOGS') || PATH_ABSOLUTO_LOGS == '') {
            $nlog = PATH_ABSOLUTO_SITIO . '/logs/' . $camara . '_UID' . $uid . '_' . $nameLog . '.log';
        } else {
            $nlog = PATH_ABSOLUTO_LOGS . '/' . $camara . '_UID' . $uid . '_' . $nameLog . '.log';
        }
        $f = fopen ($nlog,"a");
        fwrite ($f, $log);
        fclose ($f);
        /*
        if (!defined('PATH_ABSOLUTO_LOGS') || PATH_ABSOLUTO_LOGS == '') {
            error_log($log, 3, str_replace('XX', $camara . '_UID' . $uid, PATH_ABSOLUTO_SITIO . "/logs/XX_{$nameLog}.log"));
        } else {
            error_log($log, 3, str_replace('XX', $camara . '_UID' . $uid, PATH_ABSOLUTO_LOGS . "/XX_{$nameLog}.log"));
        }
        */
    }

    public static function generalSinCodigoEmpresa($nameLog, $code, $msg) {
        if (function_exists('posix_getuid')) {
            $uid = posix_getuid();
        } else {
            $uid = '0';
        }
        $date = date('Y.m.d H:i:s');
        $log = "[{$date}] | {$code} | {$msg} \n";
        if (!defined('PATH_ABSOLUTO_LOGS') || PATH_ABSOLUTO_LOGS == '') {
            error_log($log, 3, PATH_ABSOLUTO_SITIO . "/logs/{$nameLog}.log");
        } else {
            error_log($log, 3, PATH_ABSOLUTO_LOGS . "/{$nameLog}.log");
        }
    }
    
    public static function peticionRest($nameLog) {
        $nameLog = $nameLog . "_" . date("Ymd");
        $usuarioLog = '';
        if (isset($_SESSION["entrada"])) {            
            $usuarioLog = $_SESSION["entrada"]["usuariows"];
            $msgPeticion = 'Petición Rest: ' . chr(10) . chr(13) . json_encode($_SESSION["entrada"]) . chr(10) . chr(13);
        }      
        if (function_exists('posix_getuid')) {
            $uid = posix_getuid();
        } else {
            $uid = '0';
        }
        $camara = isset($_SESSION["generales"]["codigoempresa"]) ? $_SESSION["generales"]["codigoempresa"] : "00";
        $date = date('Y.m.d H:i:s');
        $log = "[{$date}] | {$usuarioLog} | {$msgPeticion} \n";
        if (!defined('PATH_ABSOLUTO_LOGS') || PATH_ABSOLUTO_LOGS == '') {
            error_log($log, 3, str_replace('XX', $camara . '_UID' . $uid, PATH_ABSOLUTO_SITIO . "/logs/XX_{$nameLog}.log"));
        } else {
            error_log($log, 3, str_replace('XX', $camara . '_UID' . $uid, PATH_ABSOLUTO_LOGS . "/XX_{$nameLog}.log"));
        }
    }
}

?>
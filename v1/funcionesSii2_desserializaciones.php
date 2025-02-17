<?php

class funcionesSii2_desserializaciones {

    public static function desserializarExpedienteProponente($dbx, $xml, $codigoempresa = '', $controlprimeravez = 'no', $proceso = 'llamado directo a desserializarExpedienteProponente', $tipotramite = '') {
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales.php');
        $retorno = \funcionesGenerales::desserializarExpedienteProponente($dbx, $xml, $controlprimeravez, $proceso, $tipotramite);
        return $retorno;
    }

    public static function desserializarExpedienteMatricula($dbx, $xml) {
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales.php');
        $retorno = \funcionesGenerales::desserializarExpedienteMatricula($dbx, $xml);
        return $retorno;
    }

    public static function serializarExpedienteMatricula($numrec = '', $datos = array(), $reemplazar = 'si', $extendido = 'si') {
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales.php');
        $xml = \funcionesRegistrales::serializarExpedienteMatricula(null, $numrec, $datos, $reemplazar, $extendido);
        return $xml;
    }

    public static function isJson($string) {
        return ((is_string($string) &&
                (is_object(json_decode($string)) ||
                is_array(json_decode($string))))) ? true : false;
    }
}

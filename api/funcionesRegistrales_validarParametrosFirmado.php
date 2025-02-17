<?php

class funcionesRegistrales_validarParametrosFirmado {

    public static function validarParametrosFirmado() {
        if (!defined('FUNCION_FIRMADO_ELECTRONICO_SOBRES') || FUNCION_FIRMADO_ELECTRONICO_SOBRES != 'TCPDF') {
            require_once($_SESSION["generales"]["pathabsoluto"] . '/api/PDFA.class.php');
            $pdfa = new PDFA();
            $x = $pdfa->verificarParametrosFirmado('FIRMA_' . $_SESSION["generales"]["codigoempresa"] . '_PJ', date("Ymd"));
            if (!$x) {
                if ($_SESSION["generales"]["escajero"] != 'SI') {
                    return false;
                }
            }
        }
        return true;
    }
}

?>

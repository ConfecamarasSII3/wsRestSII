<?php

class funcionesRegistrales_validarMunicipioJurisdiccion {

    public static function validarMunicipioJurisdiccion($mysqli, $mun) {
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        if (contarRegistrosMysqliApi($mysqli, 'mreg_municipiosjurisdiccion', "idcodigo='" . $mun . "'") > 0) {
            return true;
        } else {
            return false;
        }
    }
}

?>

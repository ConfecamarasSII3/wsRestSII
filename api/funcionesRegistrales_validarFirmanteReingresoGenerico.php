<?php

class funcionesRegistrales_validarFirmanteReingresoGenerico {

    public static function validarFirmanteReingresoGenerico($mysqli) {
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        $respuesta = array();
        $respuesta["codigoError"] = '0000';
        $respuesta["msgError"] = '';
        $respuesta["tiptra"] = '';
        $respuesta["firmante"] = '';
        $respuesta["factorfirmado"] = '';
        $respuesta["exigeverificado"] = '';

//
        $respuesta["firmante"] = '99';
        $respuesta["factorfirmado"] = 'CLAVE';
        $respuesta["exigeverificado"] = 'si';

//
        $tt = retornarRegistroMysqliApi($mysqli, 'bas_tipotramites', "id='reingresogenerico'");
        if ($tt && !empty($tt)) {
            $respuesta["firmante"] = $tt["firmante"];
            $respuesta["factorfirmado"] = $tt["factorfirmado"];
            $respuesta["exigeverificado"] = $tt["exigeverificado"];
        }

//
        if ($respuesta["exigeverificado"] == 'no') {
            return $respuesta;
        }

//
        if ($respuesta["exigeverificado"] == 'si') {
            $usu = false;
            $usus = retornarRegistrosMysqliApi($mysqli, 'usuarios_verificados', "email='" . $_SESSION["generales"]["emailusuariocontrol"] . "' and identificacion='" . $_SESSION["generales"]["identificacionusuariocontrol"] . "'", "id");
            if ($usus && !empty($usus)) {
                foreach ($usus as $usx) {
                    if ($usx["estado"] != 'EL') {
                        $usu = $usx;
                    }
                }
            }
            unset($usus);
            if ($usu === false || empty($usu) || $usu["estado"] == 'EL') {
                $respuesta["codigoError"] = '0002'; // usuario no existe  o está eliminado
            }
            if ($usu["estado"] == 'PE') {
                $respuesta["codigoError"] = '0003'; // usuario pendiente
            }
            if ($usu["estado"] == 'RZ') {
                $respuesta["codigoError"] = '0004'; // usuario rechazado
            }
            if ($usu["estado"] == 'SF') {
                $respuesta["codigoError"] = '0005'; // usuario sin informacion financiera
            }
            if ($usu["estado"] == 'VE' && $usu["claveconfirmacion"] == '') {
                $respuesta["codigoError"] = '0006'; // usuario no activado
            }
        }

//
        return $respuesta;
    }
}

?>

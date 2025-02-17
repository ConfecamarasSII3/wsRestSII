<?php

class funcionesRegistrales_validarFirmanteMatriculaPnatEst {

    public static function validarFirmanteMatriculaPnatEst($mysqli, $exp) {
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        $respuesta = array();
        $respuesta["codigoError"] = '0000';
        $respuesta["msgError"] = '';
        $respuesta["tiptra"] = '';
        $respuesta["firmante"] = '';
        $respuesta["factorfirmado"] = '';
        $respuesta["exigeverificado"] = '';

//
        $respuesta["firmante"] = '01,11';
        $respuesta["factorfirmado"] = 'CLAVE';
        $respuesta["exigeverificado"] = 'si';

        $continuar = 'no';

//
        if ($exp["organizacion"] == '01') {
            if ($_SESSION["generales"]["identificacionusuariocontrol"] === $exp["identificacion"]) {
                $continuar = 'si';
            }
        }

//
        if ($exp["organizacion"] === '02') {

            foreach ($exp["propietarios"] as $px) {
                if ($px["identificacionpropietario"] == $_SESSION["generales"]["identificacionusuariocontrol"]) {
                    $continuar = 'si';
                }
            }
        }

        if ($exp["organizacion"] > '02' && $exp["categoria"] == '2') {
            foreach ($exp["vinculos"] as $vx) {
                if (
                        $vx["tipovinculo"] == 'ADMP' ||
                        $vx["tipovinculo"] == 'RLP' ||
                        $vx["tipovinculo"] == 'RLS' ||
                        $vx["tipovinculo"] == 'RLS1' ||
                        $vx["tipovinculo"] == 'RLS2'
                ) {
                    if ($vx["identificacionotros"] == $_SESSION["generales"]["identificacionusuariocontrol"]) {
                        $continuar = 'si';
                    }
                }
            }
        }

        if ($exp["organizacion"] > '02' && $exp["categoria"] == '3') {
            foreach ($exp["vinculos"] as $vx) {
                if (
                        $vx["tipovinculo"] == 'ADMP' ||
                        $vx["tipovinculo"] == 'RLP' ||
                        $vx["tipovinculo"] == 'RLS' ||
                        $vx["tipovinculo"] == 'RLS1' ||
                        $vx["tipovinculo"] == 'RLS2'
                ) {
                    if ($vx["identificacionotros"] == $_SESSION["generales"]["identificacionusuariocontrol"]) {
                        $continuar = 'si';
                    }
                }
            }
        }

//
        if ($continuar == 'no') {
            $respuesta["codigoError"] = '0001';
            $respuesta["msgError"] = '';
            return $respuesta;
        }

//
        return $respuesta;
    }
}

?>

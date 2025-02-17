<?php

class funcionesRegistrales_generarSecuenciaOperacion {

    public static function generarSecuenciaOperacion($dbx, $usuario, $fecha, $cajero = '', $sedex = '') {
        $sec = 0;
        $sec1 = 0;
        $sede = $sedex;
        if ($cajero == 'INTERNET' || $cajero == 'USUPUBXX') {
            $sede = '99';
        }
        if ($cajero == 'RUE') {
            $sede = '90';
        }
        $contar = contarRegistrosMysqliApi($dbx, 'mreg_controlusuarios', "usuario='" . $cajero . "' and fecha='" . $fecha . "'");
        if ($contar === false) {
            return false;
        }
        if ($contar == 0) {
            $arrCampos = array(
                'usuario',
                'fecha',
                'secuencia'
            );
            $arrValores = array(
                "'" . $usuario . "'",
                "'" . $fecha . "'",
                1
            );
            insertarRegistrosMysqliApi($dbx, 'mreg_controlusuarios', $arrCampos, $arrValores);
            $sec1 = 1;
        } else {
            $arrTem = retornarRegistroMysqliApi($dbx, 'mreg_controlusuarios', "usuario='" . $cajero . "' and fecha='" . $fecha . "'");
            $sec = $arrTem["secuencia"] + 1;
            $sec1 = $sec;
            $arrCampos = array(
                'secuencia'
            );
            $arrValores = array(
                $sec
            );
            regrabarRegistrosMysqliApi($dbx, 'mreg_controlusuarios', $arrCampos, $arrValores, "usuario='" . $cajero . "' and fecha='" . $fecha . "'");
        }
        return $sede . '-' . trim($cajero) . '-' . $fecha . '-' . sprintf("%04s", $sec1);
    }

}

?>

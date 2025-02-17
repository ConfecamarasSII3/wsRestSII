<?php

class funcionesRegistrales_actualizarMregLiquidacionDatosOriginal {

    public static function actualizarMregLiquidacionDatosOriginal($dbx, $liq, $sec, $gru, $xml) {
        $condicion = "idliquidacion=" . $_SESSION["formulario"]["liquidacion"] . " and secuencia='000' and grupodatos='completo'";
        $contar = contarRegistrosMysqliApi($dbx, 'mreg_liquidaciondatosoriginal', $condicion);
        if ($contar == 0) {
            $arrCampos = array(
                'idliquidacion',
                'secuencia',
                'grupodatos',
                'xml'
            );
            $arrValues = array(
                $liq,
                "'" . $sec . "'",
                "'" . $gru . "'",
                "'" . addslashes(\funcionesGenerales::restaurarEspeciales($xml)) . "'"
            );
            $result = insertarRegistrosMysqliApi($dbx, 'mreg_liquidaciondatosoriginal', $arrCampos, $arrValues);
            if ($result === false) {
                return false;
            } else {
                return true;
            }
        }
    }


}

?>

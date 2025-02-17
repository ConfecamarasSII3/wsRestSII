<?php

class funcionesRegistrales_actualizarMregLiquidacionDatos {

    public static function actualizarMregLiquidacionDatos($dbx, $liq, $sec, $exp, $ide, $gru, $xml, $est) {

        //
        $arrCampos = array(
            'idliquidacion',
            'secuencia',
            'expediente',
            'numrue',
            'grupodatos',
            'xml',
            'idestado'
        );
        $arrValues = array(
            $liq,
            "'" . $sec . "'",
            "'" . $exp . "'",
            "'" . $ide . "'",
            "'" . $gru . "'",
            "'" . addslashes(restaurarEspeciales($xml)) . "'",
            "'" . $est . "'"
        );

        // Graba el log de liquidaciones
        \funcionesRegistrales::grabarMregLiquidacionDatosLog($dbx, $liq, $exp, $xml);

        // 
        $query = "idliquidacion=" . $_SESSION["formulario"]["liquidacion"] . " and secuencia='000' and expediente='" . $_SESSION["formulario"]["proponente"] . "' and numrue='" . $_SESSION["formulario"]["numrue"] . "' and grupodatos='completo'";
        $result = borrarRegistrosMysqliApi($dbx, 'mreg_liquidaciondatos', $query);
        if ($result === false) {
            return false;
        } else {
            $result = insertarRegistrosMysqliApi($dbx, 'mreg_liquidaciondatos', $arrCampos, $arrValues);
            if ($result === false) {
                return false;
            } else {
                return true;
            }
        }
    }

}

?>

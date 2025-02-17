<?php

class funcionesRegistrales_grabarMregLiquidacionDatosLog {

    public static function grabarMregLiquidacionDatosLog($dbx, $idliquidacion, $expediente, $xml1) {
        $arrCampos = array(
            'fecha',
            'hora',
            'idusuario',
            'ip',
            'idliquidacion',
            'expediente',
            'xml'
        );
        $arrValues = array(
            "'" . date("Ymd") . "'",
            "'" . date("His") . "'",
            "'" . $_SESSION["generales"]["codigousuario"] . "'",
            "'" . \funcionesGenerales::localizarIP() . "'",
            $idliquidacion,
            "'" . $expediente . "'",
            "'" . addslashes($xml1) . "'"
        );
        insertarRegistrosMysqliApi($dbx, 'mreg_liquidaciondatos_log_' . date("Y"), $arrCampos, $arrValues);
    }


}

?>

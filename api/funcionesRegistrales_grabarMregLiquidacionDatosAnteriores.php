<?php

class funcionesRegistrales_grabarMregLiquidacionDatosAnteriores {

    public static function grabarMregLiquidacionDatosAnteriores($dbx, $idliquidacion, $expediente, $xml1) {
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        $arrCampos = array(
            'idliquidacion',
            'expediente',
            'xml'
        );
        $arrValues = array(
            $idliquidacion,
            "'" . $expediente . "'",
            "'" . addslashes($xml1) . "'"
        );
        insertarRegistrosMysqliApi($dbx, 'mreg_liquidaciondatos_anteriores', $arrCampos, $arrValues);
    }
    
}

?>

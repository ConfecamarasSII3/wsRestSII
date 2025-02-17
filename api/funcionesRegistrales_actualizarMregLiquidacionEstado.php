<?php

class funcionesRegistrales_actualizarMregLiquidacionEstado {

    public static function actualizarMregLiquidacionEstado($dbx, $liqui, $est) {
        $arrCampos = array('idestado');
        $arrValores = array("'" . $est . "'");
        $result = regrabarRegistrosMysqliApi($dbx, 'mreg_liquidacion', $arrCampos, $arrValores, "idliquidacion=" . $liqui);
        if ($result === false) {
            return false;
        } else {
            return true;
        }
    }

}

?>

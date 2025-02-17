<?php

class funcionesRegistrales_actualizarMregLiquidacionDatosControl {

    public static function actualizarMregLiquidacionDatosControl($dbx, $liq, $sec, $exp, $ide, $dat, $est) {
        $retornar = "0";
        $result = retornarRegistroMysqliApi($dbx, 'mreg_liquidaciondatoscontrol', "idliquidacion=" . $liq . " and secuencia='" . $sec . "' and expediente='" . $exp . "' and numrue='" . $ide . "' and grupodatos='" . $dat . "'", "*", "U");
        if ($result === false) {
            return false;
        } else {
            if (empty($result)) {
                $retornar = "0";
            } else {
                $retornar = $result["controlgrabacion"];
            }
            if ($retornar < $est) {
                if ($retornar == "0") {
                    $arrCampos = array(
                        'idliquidacion',
                        'secuencia',
                        'expediente',
                        'numrue',
                        'grupodatos',
                        'controlgrabacion'
                    );
                    $arrValues = array(
                        $liq,
                        "'" . $sec . "'",
                        "'" . $exp . "'",
                        "'" . $ide . "'",
                        "'" . $dat . "'",
                        "'" . $est . "'"
                    );
                    $result = insertarRegistrosMysqliApi($dbx, 'mreg_liquidaciondatoscontrol', $arrCampos, $arrValues);
                } else {
                    $arrCampos = array(
                        'idliquidacion',
                        'secuencia',
                        'expediente',
                        'numrue',
                        'grupodatos',
                        'controlgrabacion'
                    );
                    $arrValues = array(
                        $liq,
                        "'" . $sec . "'",
                        "'" . $exp . "'",
                        "'" . $ide . "'",
                        "'" . $dat . "'",
                        "'" . $est . "'"
                    );
                    $query = "idliquidacion=" . $liq . " and secuencia='" . $sec . "' and expediente='" . $exp . "' and numrue='" . $ide . "' and grupodatos='" . $dat . "'";
                    $result = regrabarRegistrosMysqliApi($dbx, 'mreg_liquidaciondatoscontrol', $arrCampos, $arrValues, $query);
                }
            }
        }
        return $result;
    }

}

?>

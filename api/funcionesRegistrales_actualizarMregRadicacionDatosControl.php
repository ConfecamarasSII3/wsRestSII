<?php

class funcionesRegistrales_actualizarMregRadicacionDatosControl {

    public static function actualizarMregRadicacionDatosControl($dbx, $rad, $sec, $exp, $ide, $dat, $est) {
        $retornar = "0";
        $query = "select * from mreg_radicacionesdatoscontrol where idradicacion='" . ltrim($rad, "0") . "' and secuencia=" . $sec . " and expediente='" . $exp . "' and numrue='" . $ide . "' and grupodatos='" . $dat . "'";
        $result = retornarRegistroMysqliApi($dbx, 'mreg_radicacionesdatoscontrol', "idradicacion='" . ltrim($rad, "0") . "' and secuencia=" . $sec . " and expediente='" . $exp . "' and numrue='" . $ide . "' and grupodatos='" . $dat . "'", "*", "U");
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
                        'idradicacion',
                        'secuencia',
                        'expediente',
                        'numrue',
                        'grupodatos',
                        'controlgrabacion'
                    );
                    $arrValues = array(
                        "'" . ltrim($rad, "0") . "'",
                        "'" . $sec . "'",
                        "'" . $exp . "'",
                        "'" . $ide . "'",
                        "'" . $dat . "'",
                        "'" . $est . "'"
                    );
                    $result = insertarRegistrosMysqliApi($dbx, 'mreg_radicacionesdatoscontrol', $arrCampos, $arrValues);
                } else {
                    $arrCampos = array(
                        'idradicacion',
                        'secuencia',
                        'expediente',
                        'numrue',
                        'grupodatos',
                        'controlgrabacion'
                    );
                    $arrValues = array(
                        "'" . ltrim($rad, "0") . "'",
                        "'" . $sec . "'",
                        "'" . $exp . "'",
                        "'" . $ide . "'",
                        "'" . $dat . "'",
                        "'" . $est . "'"
                    );
                    $query = "idradicacion='" . ltrim($rad, "0") . "' and secuencia=" . $sec . " and expediente='" . $exp . "' and numrue='" . $ide . "' and grupodatos='" . $dat . "'";
                    $result = regrabarRegistrosMysqliApi($dbx, 'mreg_radicacionesdatoscontrol', $arrCampos, $arrValues, $query);
                }
            }
        }
        return $result;
    }

}

?>

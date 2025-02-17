<?php

class funcionesRegistrales_actualizarMregRadicacionesDatos {

    public static function actualizarMregRadicacionesDatos($dbx, $numrad, $sec, $tipotra, $exp, $est, $xml) {
        $arrCampos = array(
            'idradicacion',
            'secuencia',
            'tipotramite',
            'expediente',
            'idestado',
            'xml'
        );
        $arrValues = array(
            "'" . ltrim($numrad, "0") . "'",
            "'" . $sec . "'",
            "'" . $tipotra . "'",
            "'" . $exp . "'",
            "'" . $est . "'",
            "'" . addslashes($xml) . "'"
        );
        $query = "idradicacion='" . ltrim($numrad, "0") . "' and secuencia='" . $sec . "'";
        $result = borrarRegistrosMysqliApi($dbx, 'mreg_radicacionesdatos', $query);
        if ($result === false) {
            return false;
        } else {
            $result = insertarRegistrosMysqliApi($dbx, 'mreg_radicacionesdatos', $arrCampos, $arrValues);
            if ($result === false) {
                return false;
            } else {
                return true;
            }
        }
    }

}

?>

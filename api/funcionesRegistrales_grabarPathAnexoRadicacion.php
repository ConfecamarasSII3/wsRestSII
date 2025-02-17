<?php

class funcionesRegistrales_grabarPathAnexoRadicacion {

    public static function grabarPathAnexoRadicacion($dbx = null, $idanexo = '', $path = '') {
        $arrCampos = array(
            'path'
        );

        $arrValores = array(
            "'" . $path . "'"
        );

        $condicion = "idanexo = " . $idanexo;

        regrabarRegistrosMysqliApi($dbx, 'mreg_radicacionesanexos', $arrCampos, $arrValores, $condicion);
    }
    
}

?>

<?php

class funcionesRegistrales_actualizarMregSecuencia {

    public static function actualizarMregSecuencia($dbx, $clave, $contenido) {

        $arrTem = retornarRegistroMysqliApi($dbx, 'mreg_secuencias', "id='" . $clave . "'");

//
        if ($arrTem === false || empty($arrTem)) {

            $arrCampos = array(
                'id',
                'secuencia'
            );
            $arrValores = array(
                "'" . $clave . "'",
                $contenido
            );
            insertarRegistrosMysqliApi($dbx, 'mreg_secuencias', $arrCampos, $arrValores);
        } else {

            $arrCampos = array(
                'id',
                'secuencia'
            );
            $arrValores = array(
                "'" . $clave . "'",
                $contenido
            );
            regrabarRegistrosMysqliApi($dbx, 'mreg_secuencias', $arrCampos, $arrValores, "id='" . $clave . "'");
        }
    }
}

?>

<?php

class funcionesRegistrales_generarSecuenciaProponente {

    public static function generarSecuenciaProponente($dbx) {
        $mat = 0;
        $mat = \funcionesRegistrales::retornarMregSecuencia($dbx, 'PROPONENTE');

        if ($mat == 0) {
            $_SESSION["generales"]["mensajeerror"] = 'No fue posible localizar el ultimo proponente asignado';
            return false;
        }
        $seguir = "si";
        while ($seguir == 'si') {
            $mat++;
            if (contarRegistrosMysqliApi($dbx, 'mreg_est_proponentes', "proponente='" . ltrim($mat, "0") . "'") == 0) {
                $seguir = 'no';
            }
        }
        \funcionesRegistrales::actualizarMregSecuencia($dbx, 'PROPONENTE', $mat);
        $arrCampos = array(
            'proponente',
        );
        $arrValores = array(
            "'" . $mat . "'",
        );
        $res = insertarRegistrosMysqliApi($dbx, 'mreg_est_proponentes', $arrCampos, $arrValores);
        if ($res === false) {
            return false;
        }
        return $mat;
    }

}

?>

<?php

class funcionesRegistrales_generarSecuenciaLibrosProponentes {

    public static function generarSecuenciaLibrosProponentes($dbx) {
        $nameLog = 'generarSecuenciaLibrosProponentes_' . date("Ymd") . '.log';
        $ins = 0;
        $ins = \funcionesRegistrales::retornarMregSecuencia($dbx, 'LIBRO-' . 'RP01');
        if ($ins == 0) {
            $_SESSION["generales"]["mensajeerror"] = 'No fue posible localizar la ultima inscripcion para el libro RP01';
            \logApi::general2($nameLog, '', 'Error recuperando secuencia del libro RP01');
            return false;
        }
        $seguir = "si";
        while ($seguir == 'si') {
            $ins++;
            if (contarRegistrosMysqliApi($dbx, 'mreg_est_inscripciones_proponentes', "libro='RP01' and registro='" . $ins . "'") == 0) {
                $seguir = 'no';
            }
        }
        \funcionesRegistrales::actualizarMregSecuencia($dbx, 'LIBRO-' . 'RP01', $ins);
        $arrCampos = array(
            'libro',
            'registro',
            'dupli'
        );
        $arrValores = array(
            "'RP01'",
            "'" . $ins . "'",
            "'1'"
        );
        $res = insertarRegistrosMysqliApi($dbx, 'mreg_est_inscripciones_proponentes', $arrCampos, $arrValores);
        if ($res === false) {
            \logApi::general2($nameLog, '', 'Error grabando mreg_est_inscripciones_proponentes RP01-' . $ins . ' : ' . $_SESSION["generales"]["mensajeerror"]);
            return false;
        }
        return $ins;
    }

}

?>

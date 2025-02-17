<?php

class funcionesRegistrales_generarSecuenciaMatricula {

    /** 
     * 
     * @param type $dbx
     * @param type $tipomat
     * @param type $org
     * @param type $cat
     * @param type $nom
     * @param type $fmat
     * @param type $fren
     * @param type $aren
     * @param string $est
     * @param type $codbar
     * @param type $proceso
     * @return bool|string
     */
    public static function generarSecuenciaMatricula($dbx, $tipomat = '', $org = '', $cat = '', $nom = '', $fmat = '', $fren = '', $aren = '', $est = '', $codbar = '', $proceso = '') {
        $nameLog = 'generarSecuenciaMatricula_' . date("Ymd");
        $mat = 0;
        $mat = \funcionesRegistrales::retornarMregSecuencia($dbx, $tipomat);
        if ($mat == 0) {
            $_SESSION["generales"]["mensajeerror"] = 'No fue posible localizar la ultima matricula asignada';
            return false;
        }
        $seguir = "si";
        while ($seguir == 'si') {
            $mat++;
            if ($tipomat == 'MATREGMER') {
                $xMat = ltrim($mat, "0");
            }
            if ($tipomat == 'MATESADL') {
                $xMat = "S" . sprintf("%07s", $mat);
            }
            if ($tipomat == 'MATCIVIL') {
                $xMat = "N" . sprintf("%07s", $mat);
            }

            if (contarRegistrosMysqliApi($dbx, 'mreg_est_inscritos', "matricula='" . $xMat . "'") == 0) {
                $seguir = 'no';
            }
        }
        if ($est == '') {
            $est = 'AS';
        }
        \funcionesRegistrales::actualizarMregSecuencia($dbx, $tipomat, $mat);
        $arrCampos = array(
            'matricula',
            'razonsocial',
            'organizacion',
            'categoria',
            'fecmatricula',
            'fecrenovacion',
            'ultanoren',
            'ctrestmatricula',
            'ctrestdatos',
            'origendocconst',
            'fecactualizacion',
            'fecsincronizacion',
            'horsincronizacion'
        );
        $arrValores = array(
            "'" . $xMat . "'",
            "'" . addslashes($nom) . "'",
            "'" . $org . "'",
            "'" . $cat . "'",
            "'" . $fmat . "'",
            "'" . $fren . "'",
            "'" . $aren . "'",
            "'" . $est . "'",
            "'3'",
            "''",
            "''",
            "''",
            "''"
        );
        unset($_SESSION["expedienteactual"]);
        $res = insertarRegistrosMysqliApi($dbx, 'mreg_est_inscritos', $arrCampos, $arrValores);
        if ($res === false) {
            \logApi::general2($nameLog, '', 'Error asignando numero de matricula : ' . $_SESSION["generales"]["mensajeerror"]);
            return false;
        } else {
            
            $detalle = 'Se asigno numero de matricula ' . $xMat;
            if ($codbar != '') {
                $detalle .= ', codigo de barras ' . $codbar;
            }
            if ($proceso != '') {
                $detalle .= ', proceso ' . $proceso;
            }
            actualizarLogMysqliApi($dbx, '002', $_SESSION["generales"]["codigousuario"], '', '', '', '', $detalle, $xMat, '');
        }

        return $xMat;
    }

}

?>

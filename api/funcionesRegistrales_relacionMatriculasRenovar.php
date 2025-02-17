<?php

class funcionesRegistrales_relacionMatriculasRenovar {

    public static function relacionMatriculasRenovar($mysqli, $txt) {
        require_once($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common' . $_SESSION["generales"]["codigoempresa"] . '.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');

        if ($txt != '') {
            $lista = explode(',', $txt);
            $canceladas = 0;
            $aldia = 0;
            $in = '';
            $inx = '';
            foreach ($lista as $l) {
                $lx = explode('-', $l);
                if (isset($lx[1])) {
                    $ly = ltrim($lx[1], "0");
                } else {
                    $ly = ltrim($l, "0");
                }
                if ($in != '') {
                    $in .= ',';
                }
                $in .= "'" . $ly . "'";
                $inx = $ly . ' ';
            }
            $arrTem = retornarRegistrosMysqliApi($mysqli, 'mreg_est_inscritos', "matricula IN (" . $in . ")", "matricula");
            foreach ($arrTem as $t) {
                if ($t["ctrestmatricula"] != 'MA' && $t["ctrestmatricula"] != 'IA') {
                    $canceladas++;
                }
                if ($t["ultanoren"] == date("Y")) {
                    $aldia++;
                }
            }
            unset($arrTem);

            if ($canceladas == 0 && $aldia == 0) {
                $resultado["codigoError"] = '0000';
                $resultado["msgError"] = '';
            }
            if ($canceladas > 0) {
                $resultado["codigoError"] = '0002';
                $resultado["msgError"] = 'Alguna(s) de la(s) matr&iacute;cula(s) a renovar fue(ron) cancelada(s) en fecha posterior a la elaboraci&oacute;n de la liquidaci&oacute;n (' . $txt . ')';
            }
            if ($aldia > 0) {
                $resultado["codigoError"] = '0003';
                $resultado["msgError"] = 'Alguna(s) de la(s) matr&iacute;cula(s) a renovar fue(ron) renovadas en fecha posterior a la elaboraci&oacute;n de la liquidaci&oacute;n (' . $txt . ')';
            }
        } else {
            $resultado["codigoError"] = '0000';
            $resultado["msgError"] = '';
        }

        return $resultado;
    }
}

?>

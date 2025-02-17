<?php

class funcionesRegistrales_almacenarDatosImportantesRenovacion {

    public static function almacenarDatosImportantesRenovacion($mysqli, $liq, $dat, $mom) {
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common' . $_SESSION["generales"]["codigoempresa"] . '.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        if ($mom == 'F') {
            borrarRegistrosMysqliApi($mysqli, 'mreg_renovacion_datos_control', "idliquidacion=" . $liq . " and matricula='" . ltrim($dat["matricula"], "0") . "' and momento='F'");
        }
        $arrCampos = array(
            'idliquidacion',
            'matricula',
            'dato',
            'contenido',
            'momento'
        );
        $arrValores = array();
        foreach ($dat as $key => $valor) {
            if (!is_array($valor)) {
                if ($valor == '.') {
                    $valor = '';
                }
                $arrValores[] = array($liq, "'" . ltrim($dat["matricula"], "0") . "'", "'" . $key . "'", "'" . addslashes($valor) . "'", "'" . $mom . "'");
            }
        }
        $arrValores[] = array($liq, "'" . ltrim($dat["matricula"], "0") . "'", "'ciiu1'", "'" . $dat["ciius"][1] . "'", "'" . $mom . "'"); // Ciiu1
        $arrValores[] = array($liq, "'" . ltrim($dat["matricula"], "0") . "'", "'ciiu2'", "'" . $dat["ciius"][2] . "'", "'" . $mom . "'"); // Ciiu2
        $arrValores[] = array($liq, "'" . ltrim($dat["matricula"], "0") . "'", "'ciiu3'", "'" . $dat["ciius"][3] . "'", "'" . $mom . "'"); // Ciiu3
        $arrValores[] = array($liq, "'" . ltrim($dat["matricula"], "0") . "'", "'ciiu4'", "'" . $dat["ciius"][4] . "'", "'" . $mom . "'"); // Ciiu4
        insertarRegistrosBloqueMysqliApi($mysqli, 'mreg_renovacion_datos_control', $arrCampos, $arrValores);
        return true;
    }

}

?>

<?php

class funcionesRegistrales_actualizarMregNotificacionesParaEnviarEmail {

    public static function actualizarMregNotificacionesParaEnviarEmail(
            $mysqli, $tnot = '', $rad = '', $dev = '', $ope = '', $rec = '', $lib = '', $reg = '', $dup = '', $idc = '', $ide = '', $mat = '', $pro = '', $nom = '', $ema = '', $det = '', $fpro = '', $hpro = '', $fnot = '', $hnot = '', $est = '', $obs = '', $bandeja = ''
    ) {
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/genPdfImagenes635.php');
        ini_set('memory_limit', '1024M');
        $arrCampos = array(
            'tiponotificacion',
            'radicacion',
            'devolucion',
            'operacion',
            'recibo',
            'libro',
            'registro',
            'dupli',
            'idclase',
            'numid',
            'matricula',
            'proponente',
            'nombre',
            'email',
            'detallenotificacion',
            'fechaprogramacion',
            'horaprogramacion',
            'fechanotificacion',
            'horanotificacion',
            'idestadonotificacion',
            'observaciones'
        );
        $arrValores = array(
            "'" . $tnot . "'",
            "'" . $rad . "'",
            "'" . $dev . "'",
            "'" . $ope . "'",
            "'" . $rec . "'",
            "'" . $lib . "'",
            "'" . $reg . "'",
            "'" . $dup . "'",
            "'" . $idc . "'",
            "'" . $ide . "'",
            "'" . $mat . "'",
            "'" . $pro . "'",
            "'" . addslashes($nom) . "'",
            "'" . addslashes($ema) . "'",
            "'" . addslashes($det) . "'",
            "'" . $fpro . "'",
            "'" . $hpro . "'",
            "'" . $fnot . "'",
            "'" . $hnot . "'",
            "'" . $est . "'",
            "'" . addslashes($obs) . "'"
        );
        insertarRegistrosMysqliApi($mysqli, 'mreg_notificaciones_para_enviar_email', $arrCampos, $arrValores);
        $_SESSION["generales"]["idanexogenerado"] = 0;
        if ($tnot < '20') {
            $_SESSION["generales"]["idanexogenerado"] = generarPdfNotificacionEmailApi635($mysqli, $tnot, $rad, $dev, $ope, $rec, $lib, $reg, $dup, $idc, $ide, $mat, $pro, $nom, $ema, $det, $fpro, $hpro, $fnot, $hnot, $est, $obs, $bandeja);
        }
        $retornar = true;
        if ($_SESSION["generales"]["idanexogenerado"] > 0) {
            $anx = retornarRegistroMysqliApi($mysqli, 'mreg_radicacionesanexos', "idanexo=" . $_SESSION["generales"]["idanexogenerado"]);
            if ($anx === false || empty($anx)) {
                $retornar = false;
            } else {
                if (!file_exists($_SESSION["generales"]["pathabsoluto"] . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/' . $anx["path"])) {
                    $retornar = false;
                }
            }
        }
        return $retornar;
    }
 
}

?>

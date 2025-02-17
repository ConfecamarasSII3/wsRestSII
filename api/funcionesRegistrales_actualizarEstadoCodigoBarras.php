<?php

class funcionesRegistrales_actualizarEstadoCodigoBarras {

    public static function actualizarEstadoCodigoBarras($mysqli = null, $codbar = '', $estado = '', $ope = '', $sede = '', $imagenes = '', $fecha = '', $hora = '') {
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRues.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesPowerFile.php');
        $cerrarmysql = 'no';
        if ($mysqli == null) {
            $mysqli = conexionMysqliApi();
            $cerrarmysql = 'si';
        }
        if ($fecha == '') {
            $fecha = date("Ymd");
            $hora = date("His");
        }
        if ($hora == '') {
            $hora = date("His");
        }

        if (trim($imagenes) != '') {
            $arrCampos = array(
                'estadofinal',
                'operadorfinal',
                'fechaestadofinal',
                'horaestadofinal',
                'sucursalfinal',
                'escaneocompleto'
            );
            $arrValores = array(
                "'" . $estado . "'",
                "'" . $ope . "'",
                "'" . $fecha . "'",
                "'" . $hora . "'",
                "'" . $sede . "'",
                "'" . $imagenes . "'"
            );
        } else {
            $arrCampos = array(
                'estadofinal',
                'operadorfinal',
                'fechaestadofinal',
                'horaestadofinal',
                'sucursalfinal'
            );
            $arrValores = array(
                "'" . $estado . "'",
                "'" . $ope . "'",
                "'" . date("Ymd") . "'",
                "'" . date("His") . "'",
                "'" . $sede . "'"
            );
        }
        
        //Generar nueva alfa
        $consultaPrevia = retornarRegistroMysqliApi($mysqli, 'mreg_est_codigosbarras', "codigobarras='" . ltrim($codbar, "0") . "'");
        if (!$consultaPrevia) {
            $res = false;
        } else {
            $res = regrabarRegistrosMysqliApi($mysqli, 'mreg_est_codigosbarras', $arrCampos, $arrValores, "codigobarras='" . ltrim($codbar, "0") . "'");
            $detalle = 'Cambio estado del codigo de barras No. ' . ltrim($codbar, "0") . ', estado final: ' . $estado . ', operador final: ' . $ope;
            actualizarLogMysqliApi($mysqli, '069', $_SESSION["generales"]["codigousuario"], 'mregMostrarBandejas.php', '', '', '', $detalle, '', '');            
        }
       
        
        if ($res === false) {
            if ($cerrarmysql == 'si') {
                $mysqli->close();
            }
            return false;
        }

        // ************************************************************************************************* //
        // Actualiza el log de estados del codigo de barras en SII
        // ************************************************************************************************* //
        $arrCampos = array(
            'codigobarras',
            'fecha',
            'hora',
            'estado',
            'operador',
            'sucursal'
        );
        $arrValores = array(
            "'" . ltrim($codbar, "0") . "'",
            "'" . $fecha . "'",
            "'" . $hora . "'",
            "'" . $estado . "'",
            "'" . $ope . "'",
            "'" . $sede . "'"
        );
        $res = insertarRegistrosMysqliApi($mysqli, 'mreg_est_codigosbarras_documentos', $arrCampos, $arrValores);
        if ($res === false) {
            if ($cerrarmysql == 'si') {
                $mysqli->close();
            }
            return false;
        }

        // JINT: 2020-11-27 - Actualiza estado en VUE
        $numliq = '';
        $liq = retornarRegistroMysqliApi($mysqli, 'mreg_liquidacion', "numeroradicacion='" . ltrim($codbar, "0") . "'");
        if ($liq && !empty($liq)) {
            $numliq = $liq["idliquidacion"];
            if ($estado == '39') {
                $arrCampos = array (
                    'idestado'
                );
                $arrValores = array (
                    "'18'"
                );
                regrabarRegistrosMysqliApi($mysqli, 'mreg_liquidacion', $arrCampos, $arrValores, "idliquidacion=" . $numliq);
            }
        }
        
        //
        \funcionesRues::reportarEstadoTramiteVUE($mysqli, ltrim($codbar, "0"), $numliq, $estado, $fecha, $hora);

        // 2017-08-17 - JINT: Llamado a docXflow para reportar cambio de estado
        if (SISTEMA_IMAGENES_REGISTRO == 'DOCXFLOW') {
            if (defined('DOCXFLOW_SERVER') && trim(DOCXFLOW_SERVER) != '') {
                \funcionesRegistrales::docXflowNotificarCambioEstado($mysqli, ltrim($codbar, "0"), $estado);
            }
        }

        // Si se trata de reingreso (09) y el gestor documental es PowerFile
        if (SISTEMA_IMAGENES_REGISTRO == 'POWERFILE') {
            if ($estado == '09') {
                \funcionesPowerFile::reportarReingresoPowerFile($mysqli, ltrim($codbar, "0"), '', $_SESSION["generales"]["codigousuario"], $fecha, substr($hora, 0, 4), $_SESSION["generales"]["sedeusuario"]);
            }
        }

        if ($cerrarmysql == 'si') {
            $mysqli->close();
        }

        return true;
    }

}

?>

<?php

class funcionesRegistrales_actualizarMregCertificasSii {

    public static function actualizarMregCertificasSii($mysqli, $data, $codbarras = '', $tt = '', $rec = '') {
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
        $nameLog = 'actualizarMregCertificasSii_' . date("Ymd");

        //
        $cerrarmysql = 'no';
        if ($mysqli == null) {
            $cerrarmysql = 'si';
            $mysqli = conexionMysqliApi();
        }

        $data["CodigoSII"] = (isset($data["CodigoSII"])) ? $data["CodigoSII"] : '';
        $data["Texto"] = (isset($data["Texto"])) ? $data["Texto"] : '';
        $data["TipoRegistro"] = (isset($data["TipoRegistro"])) ? $data["TipoRegistro"] : '';
        $data["matricula"] = (isset($data["matricula"])) ? $data["matricula"] : '';
        $data["proponente"] = (isset($data["proponente"])) ? $data["proponente"] : '';

        //
        if ($tt == '') {
            $tt = 'docCTVCE';
        }
        
        //
        if ($data["TipoRegistro"] == '') {
            $data["TipoRegistro"] = 'REGMER';
        }

        //
        if (ltrim((string)$data["matricula"], "0") == '' && ltrim((string)$data["proponente"], "0") == '') {
            if ($cerrarmysql == 'si') {
                $mysqli->close();
            }
            return true;
        }
        
        $expediente = '';
        if ($data["TipoRegistro"] == 'REGMER') {
            $expediente = $data["matricula"];
        }
        if ($data["TipoRegistro"] == 'REGPRO') {
            $expediente = $data["proponente"];
        }
        
        //
        $arrCampos = array(
            'registro',
            'expediente',
            'idcertifica',
            'contenido',
            'fechaultimamodificacion',
            'horaultimamodificacion',
            'idusuario'
        );

        $arrValores = array(
            "'" . $data["TipoRegistro"] . "'",
            "'" . ltrim($expediente, "0") . "'",
            "'" . trim($data["CodigoSII"]) . "'",
            "'" . addslashes(trim($data["Texto"])) . "'",
            "'" . date("Ymd") . "'",
            "'" . date("His") . "'",
            "'" . $_SESSION["generales"]["codigousuario"] . "'"
        );

        //
        $condicion = "registro='" . $data["TipoRegistro"] . "' and expediente='" . ltrim($expediente, "0") . "' and idcertifica='" . trim($data["CodigoSII"]) . "'";
        
        $idreg = retornarRegistroMysqliApi($mysqli, 'mreg_certificas_sii', $condicion);
        if ($idreg === false || empty($idreg)) {
            $control = 'insertar';
            $res = insertarRegistrosMysqliApi($mysqli, 'mreg_certificas_sii', $arrCampos, $arrValores);
            if ($res === false) {
                \logApi::general2($nameLog, ltrim($expediente, "0"), 'Error creando registro en mreg_certificas_sii : ' . $_SESSION["generales"]["mensajeerror"]);
                if ($cerrarmysql == 'si') {
                    $mysqli->close();
                }
                return false;
            } else {
                $arrCampos1 = array(
                    'matricula',
                    'campo',
                    'fecha',
                    'hora',
                    'codigobarras',
                    'datoanterior',
                    'datonuevo',
                    'usuario',
                    'ip',
                    'tipotramite',
                    'recibo'
                );
                $arrValores1 = array(
                    "'" . ltrim($expediente, "0") . "'",
                    "'" . 'certifica-creado' . "'",
                    "'" . date("Ymd") . "'",
                    "'" . date("His") . "'",
                    "'" . $codbarras . "'", // Codigo de barras
                    "''", // Datos originales
                    "'" . addslashes($data["CodigoSII"] . ':' . $data["Texto"]) . "'",
                    "'" . $_SESSION["generales"]["codigousuario"] . "'",
                    "'" . \funcionesGenerales::localizarIP() . "'",
                    "'" . $tt . "'",
                    "''" // recibo
                );
                insertarRegistrosMysqliApi($mysqli, 'mreg_campos_historicos_' . date("Y"), $arrCampos1, $arrValores1);
            }
        } else {
            borrarRegistrosMysqliApi($mysqli, 'mreg_certificas_sii', $condicion);
            unset($_SESSION["expedienteactual"]);
            $control = 'regrabar';
            $res = insertarRegistrosMysqliApi($mysqli, 'mreg_certificas_sii', $arrCampos, $arrValores);
            if ($res === false) {
                \logApi::general2($nameLog, ltrim($expediente, "0"), 'Error modificando matrícula en mreg_certificas_sii: ' . $_SESSION["generales"]["mensajeerror"]);
                if ($cerrarmysql == 'si') {
                    $mysqli->close();
                }
                return false;
            } else {
                $arrCampos1 = array(
                    'matricula',
                    'campo',
                    'fecha',
                    'hora',
                    'codigobarras',
                    'datoanterior',
                    'datonuevo',
                    'usuario',
                    'ip',
                    'tipotramite',
                    'recibo'
                );
                $arrValores1 = array(
                    "'" . ltrim($expediente, "0") . "'",
                    "'" . 'certifica-modificado' . "'",
                    "'" . date("Ymd") . "'",
                    "'" . date("His") . "'",
                    "'" . $codbarras . "'", // Codigo de barras
                    "'" . addslashes($idreg["idcertifica"] . ':' . $idreg["contenido"]) . "'", // Datos originales
                    "'" . addslashes($data["CodigoSII"] . ':' . $data["Texto"]) . "'",
                    "'" . $_SESSION["generales"]["codigousuario"] . "'",
                    "'" . \funcionesGenerales::localizarIP() . "'",
                    "'" .$tt . "'",
                    "''" // recibo
                );
                insertarRegistrosMysqliApi($mysqli, 'mreg_campos_historicos_' . date("Y"), $arrCampos1, $arrValores1);
            }
        }
        
        //
        $detalle = 'Creacion/Modificacion Certifica ' . $data["CodigoSII"] . ' : ' . $data["Texto"];
        actualizarLogMysqliApi($mysqli, '003', $_SESSION["generales"]["codigousuario"], $tt, '', '', '', $detalle, $data["matricula"], $data["proponente"]);
        
        // ********************************************************************************************* //
        // Cierra conexión con BD
        // ********************************************************************************************* //

        if ($cerrarmysql == 'si') {
            $mysqli->close();
        }

        //
        return true;
    }
}

?>

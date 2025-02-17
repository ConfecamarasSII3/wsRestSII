<?php

class funcionesRegistrales_armarDesistimiento {

    public static function armarDesistimiento($mysqli, $d, $arrUsu) {
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/configuracion/commonPredefiniciones.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common' . $_SESSION["generales"]["codigoempresa"] . '.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistralesCalculos.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/genWord.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/myErrorHandler.php');
        set_error_handler('myErrorHandler');

        $arrMen = array();

        $_SESSION["fdoc"] = array();
        $_SESSION["fdoc"]["doctip"] = $d["idtipodocdesistimiento"];
        $_SESSION["fdoc"]["docnro"] = ltrim((string)$d["numdocdesistimiento"], "0");
        $_SESSION["fdoc"]["docfecn"] = $d["fechadocdesistimiento"];
        $_SESSION["fdoc"]["razonsocial"] = \funcionesGenerales::utf8_decode($d["nombre"]);
        $_SESSION["fdoc"]["ident"] = ltrim((string)$d["identificacion"], "0");
        $_SESSION["fdoc"]["codbarras"] = ltrim((string)$d["codigobarras"], "0");
        $_SESSION["fdoc"]["fecradica"] = \funcionesGenerales::mostrarFechaLetras1($d["fecharadicacion"]);
        $_SESSION["fdoc"]["fecradl1"] = \funcionesGenerales::mostrarFechaLetras1($d["fecharadicacion"]);
        $_SESSION["fdoc"]["fecdevolucion"] = \funcionesGenerales::mostrarFechaLetras1($d["fechadevolucionentrega"]);        
        $_SESSION["fdoc"]["fecdevl1"] = \funcionesGenerales::mostrarFechaLetras1($d["fechadevolucionentrega"]);
        $_SESSION["fdoc"]["tipotramite"] = retornarRegistroMysqliApi($mysqli, 'mreg_servicios', "idservicio='" . $d["servicio"] . "'", "nombre");
        $_SESSION["fdoc"]["nomabo"] = $arrUsu["nombreusuario"];
        $_SESSION["fdoc"]["tipdoc"] = retornarRegistroMysqliApi($mysqli, 'mreg_tipos_documentales_registro', "id='" . $d["idtipodoc"] . "'", "descripcion");
        $_SESSION["fdoc"]["numdoc"] = ltrim((string)$d["numdoc"]);
        $_SESSION["fdoc"]["actoreparto"] = retornarRegistroMysqliApi($mysqli, 'mreg_est_codigosbarras', "codigobarras='" . ltrim($d["codigobarras"], "0") . "'", "actoreparto");

        $_SESSION["fdoc"]["valor"] = '';
        $_SESSION["fdoc"]["email"] = '';        
        $liq = retornarRegistroMysqliApi($mysqli,'mreg_liquidacion',"numeroradicacion='" . ltrim((string)$d["codigobarras"], "0") . "'");
        if ($liq && !empty ($liq)) {
            $_SESSION["fdoc"]["valor"] = number_format($liq["valortotal"]);
            $_SESSION["fdoc"]["email"] = $liq["email"];
        }
        
        if (ltrim($_SESSION["fdoc"]["numdoc"], "0") == '') {
            $_SESSION["fdoc"]["numdoc"] = 'N/A';
        }
        $_SESSION["fdoc"]["oridoc"] = ltrim((string)$d["origendoc"]);
        if (trim($_SESSION["fdoc"]["oridoc"]) == '') {
            $_SESSION["fdoc"]["oridoc"] = 'EL COMERCIANTE, ENTIDAD SIN ANIMO DE LUCRO O PROPONENTE';
        }
        $_SESSION["fdoc"]["numrec"] = ltrim($d["recibo"]);

//
        if ($_SESSION["fdoc"]["actoreparto"] == '09' || $_SESSION["fdoc"]["actoreparto"] == '53') {
            $file = $_SESSION["generales"]["pathabsoluto"] . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/formatos/' . $_SESSION["fdoc"]["doctip"] . '-998.docx';
        } else {
            $file = $_SESSION["generales"]["pathabsoluto"] . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/formatos/' . $_SESSION["fdoc"]["doctip"] . '-999.docx';
        }

//
        if (file_exists($file)) {
            $formato = $file;
        } else {
            $formato = $_SESSION["generales"]["pathabsoluto"] . '/librerias/formatos/DeclaratoriaDesistimientos-RegistrosPublicos-v1.docx';
        }

        if (!file_exists($formato)) {
            $_SESSION["generales"]["mensajeerror"] = 'No es posible generar la impresi&oacute;n de la nota de devolucion dado que no existe plantilla documental tipo docx';
            return false;
        }

        // Localiza el logo de la empresa
        if (!file_exists($_SESSION["generales"]["pathabsoluto"] . '/images/logocamara' . $_SESSION["generales"]["codigoempresa"] . '.gif')) {
            if (!file_exists($_SESSION["generales"]["pathabsoluto"] . '/images/logocamara' . $_SESSION["generales"]["codigoempresa"] . '.jpg')) {
                $_SESSION["generales"]["mensajeerror"] = 'No es posible generar la impresi&oacute;n dado que el logo de la empresa en formato gif no ha sido cargado';
                return false;
            }
        }

        $usu = retornarRegistroMysqliApi($mysqli, 'usuariosfirmas', "idusuario='" . $arrUsu["idusuario"] . "'");
        if ($usu["firmagif"] == '') {
            $raiz = 'firmaDec-' . $_SESSION["generales"]["codigoempresa"] . '-' . $arrUsu["idusuario"] . '.jpg';
            $f = fopen($_SESSION["generales"]["pathabsoluto"] . '/tmp/' . $raiz, "wb");
            fwrite($f, $usu["firma"]);
            fclose($f);
        } else {
            $raiz = 'firmaDec-' . $_SESSION["generales"]["codigoempresa"] . '-' . $arrUsu["idusuario"] . '.gif';
            $f = fopen($_SESSION["generales"]["pathabsoluto"] . '/tmp/' . $raiz, "wb");
            fwrite($f, $usu["firmagif"]);
            fclose($f);
        }
        $_SESSION["fdoc"]["img1"] = $_SESSION["generales"]["pathabsoluto"] . '/tmp/' . $raiz;

        // if 
        $name = armarWord($mysqli, $_SESSION["fdoc"], $formato);
        unset($_SESSION["fdoc"]);
        if (!$name) {
            $_SESSION["generales"]["mensajeerror"] = 'No fue posible generar el archivo word';
            return false;
        }

        $mensaje = '';
//

        if (!defined('CONVERTIR_WORD_PDF')) {
            define('CONVERTIR_WORD_PDF', 'SI');
        }
        if (CONVERTIR_WORD_PDF == 'NO') {
            $name = $_SESSION["generales"]["pathabsoluto"] . '/tmp/' . $name;
            $tipo = 'docx';
            $mensaje = 'No est&aacute;n habilitadas las rutinas para pasar de Doc a Pdf';
        } else {
            if (file_exists('/usr/bin/libreoffice')) {
                $name1 = $_SESSION["generales"]["pathabsoluto"] . '/tmp/' . $name;
                if (\funcionesGenerales::docToPdf($name1, $_SESSION["generales"]["pathabsoluto"] . '/tmp/' . session_id() . '-' . $d["id"] . '.pdf')) {
                    $name = $_SESSION["generales"]["pathabsoluto"] . '/tmp/' . session_id() . '-' . $d["id"] . '.pdf';
                    $tipo = 'pdf';
                } else {
                    $name = $_SESSION["generales"]["pathabsoluto"] . '/tmp/' . $name;
                    $tipo = 'docx';
                    $mensaje = $_SESSION["generales"]["mensajeerror"];
                }
            } else {
                $name = $_SESSION["generales"]["pathabsoluto"] . '/tmp/' . $name;
                $tipo = 'docx';
                $mensaje = 'No est&aacute;n habilitadas las rutinas para pasar de Doc a Pdf';
            }
        }

        return array(
            'name' => $name,
            'tipo' => $tipo,
            'mensaje' => $mensaje
        );
    }
}

?>

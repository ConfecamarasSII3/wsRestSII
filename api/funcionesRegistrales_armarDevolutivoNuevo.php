<?php

class funcionesRegistrales_armarDevolutivoNuevo {

    public static function armarDevolutivoNuevo($mysqli, $codbarras, $iddevolucion, $firma = '') {
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/configuracion/commonPredefiniciones.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common' . $_SESSION["generales"]["codigoempresa"] . '.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistralesCalculos.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/genWord.php');

// Valida el c&oacute;digo de barras
        $arrCod = \funcionesRegistrales::retornarCodigoBarras($mysqli, $codbarras);
        if (($arrCod === false) || (empty($arrCod))) {
            \funcionesGenerales::armarMensaje('El c&oacute;digo de barras no pudo ser recuperado del sistema de registro');
            return false;
        }

        $_SESSION["devolucion"] = array();
        $_SESSION["devolucion"]["datos"] = array();
        $_SESSION["devolucion"]["motivos"] = array();
        $_SESSION["devolucion"]["datos"] = retornarRegistroMysqliApi($mysqli, 'mreg_devoluciones_nueva', "iddevolucion=" . $iddevolucion);
        if (empty($_SESSION["devolucion"]["datos"])) {
            \funcionesGenerales::armarMensaje('El c&oacute;digo de barras no tiene devolutivos en desarrollo que puedan visualizarse');
            return false;
        } else {
            $_SESSION["devolucion"]["datos"]["observaciones"] = \funcionesGenerales::restaurarEspeciales($_SESSION["devolucion"]["datos"]["observaciones"]);
            $_SESSION["devolucion"]["datos"]["recibo"] = $arrCod["recibo"];
            $_SESSION["devolucion"]["datos"]["operacion"] = $arrCod["operacion"];
            $_SESSION["devolucion"]["datos"]["fecharadicacion"] = $arrCod["fecharad"];
            $_SESSION["devolucion"]["datos"]["matricula"] = $arrCod["matricula"];
            $_SESSION["devolucion"]["datos"]["proponente"] = $arrCod["proponente"];
            $_SESSION["devolucion"]["datos"]["nin"] = $arrCod["nin"];
            $_SESSION["devolucion"]["datos"]["nuc"] = $arrCod["nuc"];
            $arrMots1 = retornarRegistrosMysqliApi($mysqli, 'mreg_devoluciones_motivos', "iddevolucion=" . $_SESSION["devolucion"]["datos"]["iddevolucion"], "id");
            $arrMots = array();
            foreach ($arrMots1 as $mot1) {
                $_SESSION["devolucion"]["motivos"][$mot1["idmotivo"]] = 'SI';
            }
            unset($arrMots1);
            unset($mot1);
        }


        $tArrayMotivos = array();
        $txtMotivos = '';
        $i = 0;
        foreach ($_SESSION["devolucion"]["motivos"] as $key => $valor) {
            $i++;
            $mot = retornarRegistroMysqliApi($mysqli, 'mreg_motivosdevolucion_nuevo', "idmotivo=" . $key);
            $txtMotivos .= $i . '.) ' . stripslashes(\funcionesGenerales::utf8_decode($mot["descripcion"]));
            $txtMotivos .= chr(10) . chr(10);
            $tArrayMotivos[]["ds"] = $i . '.) ' . trim(stripslashes(\funcionesGenerales::utf8_decode($mot["descripcion"])));
        }

        $tArrayObservaciones = array();
        $tx = explode("\n", \funcionesGenerales::utf8_decode($_SESSION["devolucion"]["datos"]["observaciones"]));
        $i = 0;
        foreach ($tx as $tx1) {
            $i++;
            if (trim($tx1) != '') {
                $tArrayObservaciones[]["ds"] = $tx1;
            }
        }

        $arrTra = retornarRegistroMysqliApi($mysqli, 'mreg_tipotramite', "idtramite='" . $_SESSION["devolucion"]["datos"]["tipotramite"] . "'");

        $txtReingreso = '';
        if ($_SESSION["devolucion"]["datos"]["tipodevolucion"] == 'D') {
            if ($_SESSION["devolucion"]["datos"]["devolucionparcial"] == 'V') {
                $file = $_SESSION["generales"]["pathabsoluto"] . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/formatos/' . $_SESSION["devolucion"]["datos"]["idtipodoc"] . '-996.docx';
            } else {
                $file = $_SESSION["generales"]["pathabsoluto"] . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/formatos/' . $_SESSION["devolucion"]["datos"]["idtipodoc"] . '-998.docx';
            }
            $txtReingreso = 'El documento se rechaza / devuelve de plano, no procede su reingreso. Debe presentar una comunicaci&oacute;n por parte ';
            $txtReingreso .= 'del interesado, solicitando la devoluci&oacute;n del dinero cancelado, adjuntando los recibos originales. ';
        } else {
            if ($_SESSION["devolucion"]["datos"]["devolucionparcial"] == 'V') {
                $file = $_SESSION["generales"]["pathabsoluto"] . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/formatos/' . $_SESSION["devolucion"]["datos"]["idtipodoc"] . '-997.docx';
            } else {
                $file = $_SESSION["generales"]["pathabsoluto"] . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/formatos/' . $_SESSION["devolucion"]["datos"]["idtipodoc"] . '-999.docx';
            }
            $txtReingreso = 'En caso de desistir del registro de dicho trámite, debe presentar una comunicación por parte ';
            $txtReingreso .= 'del interesado, solicitando la devolución del dinero cancelado, adjuntando los recibos originales. ';
            $txtReingreso .= chr(13) . chr(13);
            $txtReingreso .= 'Tenga presente que a partir del día siguiente a la presente comunicación, usted cuenta con treinta ';
            $txtReingreso .= '(30) días para reingresar su trámite debidamente corregido, de lo contrario se decretará el ';
            $txtReingreso .= 'desistimiento y archivo del mismo, para lo cual se expedirá el acto administrativo solo ';
            $txtReingreso .= 'suceptible del Recurso de Reposición. (Artículo 17 Código de Procedimiento Administrativo ';
            $txtReingreso .= 'y de lo Contencioso Administrativo).';
        }
        $_SESSION["fdoc"] = array();
        $_SESSION["fdoc"]["expediente"] = ltrim($_SESSION["devolucion"]["datos"]["matricula"], "0") . ltrim($_SESSION["devolucion"]["datos"]["proponente"], "0");
        $_SESSION["fdoc"]["doctip"] = $_SESSION["devolucion"]["datos"]["idtipodoc"];
        $_SESSION["fdoc"]["docnro"] = ltrim($_SESSION["devolucion"]["datos"]["numdoc"], "0");
        $_SESSION["fdoc"]["numdoc"] = ltrim($_SESSION["devolucion"]["datos"]["numdoc"], "0");
        $_SESSION["fdoc"]["docfecn"] = $_SESSION["devolucion"]["datos"]["fechadevolucion"];
        $_SESSION["fdoc"]["fecdevolucion"] = $_SESSION["devolucion"]["datos"]["fechadevolucion"];
        if (trim($_SESSION["devolucion"]["datos"]["nombredevolucion"]) == '') {
            $_SESSION["devolucion"]["datos"]["nombredevolucion"] = $_SESSION["devolucion"]["datos"]["razonsocial"];
        }
        $_SESSION["fdoc"]["razonsocial"] = ($_SESSION["devolucion"]["datos"]["nombredevolucion"]);
        $_SESSION["fdoc"]["nombreafectado"] = ($_SESSION["devolucion"]["datos"]["razonsocial"]);
        $_SESSION["fdoc"]["ident"] = ltrim($_SESSION["devolucion"]["datos"]["identificacion"], "0");
        $_SESSION["fdoc"]["codbarras"] = ltrim($_SESSION["devolucion"]["datos"]["idradicacion"], "0");
        $_SESSION["fdoc"]["fecradica"] = $arrCod["fecharad"];
        $_SESSION["fdoc"]["tipotramite"] = \funcionesGenerales::utf8_decode($arrTra["descripcion"]);
        $_SESSION["fdoc"]["motivos"] = $txtMotivos;
        $_SESSION["fdoc"]["docobs"] = $_SESSION["devolucion"]["datos"]["observaciones"];
        $_SESSION["fdoc"]["dev1"] = $tArrayMotivos;
        $_SESSION["fdoc"]["dev2"] = $tArrayObservaciones;
        $_SESSION["fdoc"]["reingreso"] = \funcionesGenerales::utf8_decode($txtReingreso);
        $_SESSION["fdoc"]["nomabo"] = retornarNombreUsuarioMysqliApi($mysqli, $_SESSION["devolucion"]["datos"]["idusuario"]);
        $_SESSION["fdoc"]["cargoabogado"] = retornarCargoUsuarioMysqliApi($mysqli, $_SESSION["devolucion"]["datos"]["idusuario"]);
        $_SESSION["fdoc"]["nin"] = $arrCod["nin"];
        $_SESSION["fdoc"]["nuc"] = $arrCod["nuc"];
        if ($_SESSION["devolucion"]["datos"]["tipodevolucion"] == 'D') {
            $_SESSION["fdoc"]["tipodevolucion"] = 'DEVOLUCION DE PLANO';
        }
        if ($_SESSION["devolucion"]["datos"]["tipodevolucion"] == 'R') {
            $_SESSION["fdoc"]["tipodevolucion"] = 'DEVOLUCION CONDICIONAL';
            if ($_SESSION["devolucion"]["datos"]["devolucionparcial"] == 'A') {
                $_SESSION["fdoc"]["tipodevolucion"] = 'SOLICITUD AUTORIZACION INSCRIPCION PARCIAL';
            }
            if ($_SESSION["devolucion"]["datos"]["devolucionparcial"] == 'V') {
                $_SESSION["fdoc"]["tipodevolucion"] = 'DESISTIMIENTO VOLUNTARIO Y TEMPORAL DEL TRÁMITE';
            }
        }

//
        if (file_exists($file)) {
            $formato = $file;
        } else {
            if ($_SESSION["devolucion"]["datos"]["devolucionparcial"] == 'V') {
                if ($_SESSION["devolucion"]["datos"]["tipodevolucion"] == 'D') {
                    $formato = $_SESSION["generales"]["pathabsoluto"] . '/librerias/formatos/Devolucion-RegistrosPublicos-v1-Voluntario-SinReingreso.docx';
                } else {
                    $formato = $_SESSION["generales"]["pathabsoluto"] . '/librerias/formatos/Devolucion-RegistrosPublicos-v1-Voluntario-ConReingreso.docx';
                }
            } else {
                if ($_SESSION["devolucion"]["datos"]["tipodevolucion"] == 'D') {
                    $formato = $_SESSION["generales"]["pathabsoluto"] . '/librerias/formatos/Devolucion-RegistrosPublicos-v1-SinReingreso.docx';
                } else {
                    $formato = $_SESSION["generales"]["pathabsoluto"] . '/librerias/formatos/Devolucion-RegistrosPublicos-v1-ConReingreso.docx';
                }
            }
        }

        if (!file_exists($formato)) {
            \funcionesGenerales::armarMensaje('No es posible generar la impresi&oacute;n de la nota de devoluci&oacute;n dado que no existe plantilla documental tipo docx');
            return false;
        }

// Localiza el logo de la empresa
        if (!file_exists($_SESSION["generales"]["pathabsoluto"] . '/images/logocamara' . $_SESSION["generales"]["codigoempresa"] . '.gif')) {
            if (!file_exists($_SESSION["generales"]["pathabsoluto"] . '/images/logocamara' . $_SESSION["generales"]["codigoempresa"] . '.jpg')) {
                \funcionesGenerales::armarMensaje('No es posible generar la impresi&oacute;n dado que el logo de la empresa en formato gif no ha sido cargado');
                return false;
            }
        }

//
        if ($firma != '') {
            $usu = retornarRegistroMysqliApi($mysqli, 'usuariosfirmas', "idusuario='" . $firma . "'");
        } else {
            $usu = retornarRegistroMysqliApi($mysqli, 'usuariosfirmas', "idusuario='" . $_SESSION["generales"]["codigousuario"] . "'");
        }
        if ((!$usu) || (empty($usu))) {
            \funcionesGenerales::armarMensaje('El usuario que firma el devolutivo no tiene una firma digitaliza en el sistema de informaci&oacute;n');
            return false;
        }
        if (trim($usu["firmagif"]) != '') {
            $f = fopen($_SESSION["generales"]["pathabsoluto"] . '/tmp/' . $_SESSION["generales"]["codigoempresa"] . '-' . $_SESSION["generales"]["codigousuario"] . '.gif', "wb");
            fwrite($f, $usu["firmagif"]);
            fclose($f);
            $_SESSION["fdoc"]["img1"] = $_SESSION["generales"]["pathabsoluto"] . '/tmp/' . $_SESSION["generales"]["codigoempresa"] . '-' . $_SESSION["generales"]["codigousuario"] . '.gif';
        } else {
            $f = fopen($_SESSION["generales"]["pathabsoluto"] . '/tmp/' . $_SESSION["generales"]["codigoempresa"] . '-' . $_SESSION["generales"]["codigousuario"] . '.jpg', "wb");
            fwrite($f, $usu["firma"]);
            fclose($f);
            $_SESSION["fdoc"]["img1"] = $_SESSION["generales"]["pathabsoluto"] . '/tmp/' . $_SESSION["generales"]["codigoempresa"] . '-' . $_SESSION["generales"]["codigousuario"] . '.jpg';
        }

// Arcma archivo word
        $namex = armarWord($mysqli, $_SESSION["fdoc"], $formato);
        $namey = $_SESSION["generales"]["pathabsoluto"] . '/tmp/' . $namex;

//
        unset($_SESSION["fdoc"]);
        return $namex;
    }
}

?>

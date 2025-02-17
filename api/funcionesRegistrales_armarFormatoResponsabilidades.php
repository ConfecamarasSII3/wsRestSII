<?php

class funcionesRegistrales_armarFormatoResponsabilidades {

    public static function armarFormatoResponsabilidades($mysqli) {
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/configuracion/commonPredefiniciones.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common' . $_SESSION["generales"]["codigoempresa"] . '.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistralesCalculos.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/genWord.php');

        //
        if ($_SESSION["formulario"]["datos"]["organizacion"] == '01') {
            $formato = $_SESSION["generales"]["pathabsoluto"] . '/librerias/formatos/Carta-ResponsabilidadesTributarias-PN.docx';
            $_SESSION["fdoc"] = array();
            $_SESSION["fdoc"]["docfecn"] = date("Ymd");
            $_SESSION["fdoc"]["razonsocial"] = '';
            $_SESSION["fdoc"]["nombre"] = $_SESSION["formulario"]["datos"]["nom1"];
            if (trim($_SESSION["formulario"]["datos"]["nom2"]) != '') {
                $_SESSION["fdoc"]["nombre"] .= ' ' . $_SESSION["formulario"]["datos"]["nom2"];
            }
            if (trim($_SESSION["formulario"]["datos"]["ape1"]) != '') {
                $_SESSION["fdoc"]["nombre"] .= ' ' . $_SESSION["formulario"]["datos"]["ape1"];
            }
            if (trim($_SESSION["formulario"]["datos"]["ape2"]) != '') {
                $_SESSION["fdoc"]["nombre"] .= ' ' . $_SESSION["formulario"]["datos"]["ape2"];
            }            
            $_SESSION["fdoc"]["nombre"] = \funcionesGenerales::utf8_decode($_SESSION["fdoc"]["nombre"]);
            $_SESSION["fdoc"]["tipoid"] = retornarRegistroMysqliApi($mysqli, 'mreg_tipoidentificacion', "id='" . $_SESSION["formulario"]["datos"]["tipoidentificacion"] . "'", "resumen");
            $_SESSION["fdoc"]["identificacion"] = $_SESSION["formulario"]["datos"]["identificacion"];
            $_SESSION["fdoc"]["ciuexp"] = retornarRegistroMysqliApi($mysqli, 'bas_municipios', "codigomunicipio='" . $_SESSION["formulario"]["datos"]["idmunidoc"] . "'", "ciudad");
            $_SESSION["fdoc"]["dptoexp"] = retornarRegistroMysqliApi($mysqli, 'bas_municipios', "codigomunicipio='" . $_SESSION["formulario"]["datos"]["idmunidoc"] . "'", "departamento");
            $_SESSION["fdoc"]["paiexp"] = retornarRegistroMysqliApi($mysqli, 'bas_paises', "codnumpais='" . $_SESSION["formulario"]["datos"]["paisexpdoc"] . "'", "nombrepais");
            $_SESSION["fdoc"]["textofirma"] = \funcionesGenerales::utf8_decode($_SESSION["formulario"]["textofirma"]);
            if ($_SESSION["formulario"]["textofirma"] != '') {
                $_SESSION["fdoc"]["firmamensaje"] = 'FIRMADO ELECTRONICAMENTE';
            }
            $tresp = '';
            if (!empty($_SESSION["formulario"]["datos"]["codrespotri"])) {
                foreach ($_SESSION["formulario"]["datos"]["codrespotri"] as $tx) {
                    $tresp .= $tx . ' - ' . \funcionesGenerales::utf8_decode(retornarRegistroMysqliApi($mysqli, 'tablas', "tabla='responsabilidadestributarias' and idcodigo='" . $tx . "'", "descripcion")) . "\r\n";
                }
            }
            $_SESSION["fdoc"]["responsabilidades"] = $tresp;
        } else {
            $formato = $_SESSION["generales"]["pathabsoluto"] . '/librerias/formatos/Carta-ResponsabilidadesTributarias-PJ.docx';
            $_SESSION["fdoc"] = array();
            $_SESSION["fdoc"]["docfecn"] = date("Ymd");
            $_SESSION["fdoc"]["razonsocial"] = \funcionesGenerales::utf8_decode($_SESSION["formulario"]["datos"]["nombre"]);
            $_SESSION["fdoc"]["nombre"] = $_SESSION["tramite"]["nombre1repleg"];
            if (trim($_SESSION["tramite"]["nombre2repleg"]) != '') {
                $_SESSION["fdoc"]["nombre"] .= ' ' . $_SESSION["tramite"]["nombre2repleg"];
            }
            if (trim($_SESSION["tramite"]["apellido1repleg"]) != '') {
                $_SESSION["fdoc"]["nombre"] .= ' ' . $_SESSION["tramite"]["apellido1repleg"];
            }
            if (trim($_SESSION["tramite"]["apellido2repleg"]) != '') {
                $_SESSION["fdoc"]["nombre"] .= ' ' . $_SESSION["tramite"]["apellido2repleg"];
            }
            $_SESSION["fdoc"]["nombre"] = \funcionesGenerales::utf8_decode($_SESSION["fdoc"]["nombre"]);
            $_SESSION["fdoc"]["tipoid"] = retornarRegistroMysqliApi($mysqli, 'mreg_tipoidentificacion', "id='" . $_SESSION["formulario"]["datos"]["tipoiderepleg"] . "'", "resumen");
            $_SESSION["fdoc"]["identificacion"] = $_SESSION["tramite"]["iderepleg"];
            $_SESSION["fdoc"]["ciuexp"] = '';
            $_SESSION["fdoc"]["dptoexp"] = '';
            $_SESSION["fdoc"]["paiexp"] = '';
            $_SESSION["fdoc"]["textofirma"] = \funcionesGenerales::utf8_decode($_SESSION["formulario"]["textofirma"]);
            if ($_SESSION["formulario"]["textofirma"] != '') {
                $_SESSION["fdoc"]["firmamensaje"] = 'FIRMADO ELECTRONICAMENTE';
            } 
            $tresp = '';
            if (!empty($_SESSION["formulario"]["datos"]["codrespotri"])) {
                foreach ($_SESSION["formulario"]["datos"]["codrespotri"] as $tx) {
                    $tresp .= $tx . ' - ' . \funcionesGenerales::utf8_decode(retornarRegistroMysqliApi($mysqli, 'tablas', "tabla='responsabilidadestributarias' and idcodigo='" . $tx . "'", "descripcion")) . "\r\n";
                }
            }
            $_SESSION["fdoc"]["responsabilidades"] = $tresp;
        }
        $namex = armarWord($mysqli, $_SESSION["fdoc"], $formato);
        unset($_SESSION["fdoc"]);
        $namepdf1 = $_SESSION["generales"]["codigoempresa"] . '-' . session_id() . '-' . date("Ymd") . '-' . date("His") . '-ar.pdf';
        $namepdf = $_SESSION["generales"]["pathabsoluto"] . '/tmp/' . $namepdf1;
        $res = \funcionesGenerales::docToPdf($_SESSION["generales"]["pathabsoluto"] . '/tmp/'. $namex, $namepdf);
        return $namepdf1;
    }

}

?>

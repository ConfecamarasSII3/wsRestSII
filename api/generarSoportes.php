<?php

function generarReciboCajaRepositorio($mysqli, $mat = '', $pro = '', $arreglo = array(), $tiporegistro = 'RegMer', $genrec = '') {
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common.php');
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/configuracion/commonPredefiniciones.php');
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common' . $_SESSION["generales"]["codigoempresa"] . '.php');
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales.php');
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistralesCalculos.php');
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/genPdfsGenerales.php');

    
    if (!isset($_SESSION["tramite"]["claveprepago"])) {
        $_SESSION["tramite"]["claveprepago"] = '';
    }
    if (!isset($_SESSION["tramite"]["saldoprepago"])) {
        $_SESSION["tramite"]["saldoprepago"] = 0;
    }

    // echo "entra a armar pdf del recibo<br>";
    $name = armarPdfRecibo($mysqli, $_SESSION["tramite"]["idliquidacion"], 'T', 'SI', $arreglo, $_SESSION["tramite"]["claveprepago"], $_SESSION["tramite"]["saldoprepago"], $genrec);
    // echo "armo pdf del recibo<br>";

    $tipodoc = '';
    $bandeja = '';
    switch ($tiporegistro) {
        case "RegMer": {
                $tipodoc = TIPO_DOC_REC_CAJA_MERCANTIL;
                $bandeja = '4.-REGMER';
                break;
            }
        case "LibCom":
            $tipodoc = TIPO_DOC_REC_CAJA_MERCANTIL;
            $bandeja = '4.-REGMER';
            break;
        case "CerEle":
            $tipodoc = TIPO_DOC_REC_CAJA_MERCANTIL;
            $bandeja = '4.-REGMER';
            break;
        case "CerVirt":
            $tipodoc = TIPO_DOC_REC_CAJA_MERCANTIL;
            $bandeja = '4.-REGMER';
            break;
        case "InscDoc":
            $tipodoc = TIPO_DOC_REC_CAJA_MERCANTIL;
            $bandeja = '4.-REGMER';
            break;
        case "InscDocRegMer":
            $tipodoc = TIPO_DOC_REC_CAJA_MERCANTIL;
            $bandeja = '4.-REGMER';
            break;
        case "InscDocEsadl":
            $tipodoc = TIPO_DOC_REC_CAJA_ESADL;
            $bandeja = '5.-REGESADL';
            break;
        case "crm":
            $tipodoc = TIPO_DOC_REC_CAJA_MERCANTIL;
            $bandeja = '4.-REGMER';
            break;
        case "RegEsadl":
            $tipodoc = TIPO_DOC_REC_CAJA_ESADL;
            $bandeja = '5.-REGESADL';
            break;
        case "RegPro":
            $tipodoc = TIPO_DOC_REC_CAJA_PROPONENTES;
            $bandeja = '6.-REGPRO';
            break;
        case "PrePag":
            $tipodoc = TIPO_DOC_REC_CAJA_MERCANTIL;
            $bandeja = '4.-REGMER';
            break;
        default :
            $tipodoc = TIPO_DOC_REC_CAJA_MERCANTIL;
            $bandeja = '4.-REGMER';
            break;
    }

    $id = \funcionesRegistrales::grabarAnexoRadicacion(
                    $mysqli, // DB
                    ltrim($_SESSION["tramite"]["numeroradicacion"], "0"),
                    trim($_SESSION["tramite"]["numerorecibo"]),
                    trim($_SESSION["tramite"]["numerooperacion"]),
                    ltrim($_SESSION["tramite"]["identificacioncliente"], "0"),
                    trim($_SESSION["tramite"]["nombrecliente"]),
                    '', // Acreedor
                    '', // Nombre acreedor
                    ltrim($mat, "0"),
                    ltrim($pro, "0"),
                    $tipodoc, // Tipo de documento
                    '', // N&uacute;mero del documento
                    $_SESSION["tramite"]["fecharecibo"],
                    '', // C&oacute;digo de origen
                    'CAJA DE LA CAMARA DE COMERCIO', // Txtorigen
                    '', // Clasificaci&oacute;n
                    '', // N&uacute;mero del contrato
                    '', // Idfuente
                    1, // versi&oacute;n
                    '', // Path
                    '1', // Estado
                    date("Ymd"), // fecha de escaneo o generaci&oacute;n
                    $_SESSION["generales"]["codigousuario"],
                    '', // Caja
                    '', // Libro
                    'RECIBO DE CAJA No. ' . $_SESSION["tramite"]["numerorecibo"],
                    '', // Libro de comercio
                    '', // Numero de registros en libro
                    '', // Dupli
                    $bandeja, // Bandeja de registro
                    'S', // Soporte del recibo de caja
                    '', // Identificador
                    '509' // Tipo anexo	
    );

    // Traslada la imagen tif del sello al repositorio
    // $tam = filesize($name);

    $dirx = date("Ymd");
    $path = PATH_ABSOLUTO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/mreg/' . $dirx;
    if (!is_dir($path) || !is_readable($path)) {
        mkdir($path, 0777);
        \funcionesGenerales::crearIndex($path);
    }

    $pathsalida = 'mreg/' . $dirx . '/' . $id . '.pdf';
    copy($_SESSION["generales"]["pathabsoluto"] . '/' . $name, $_SESSION["generales"]["pathabsoluto"] . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/' . $pathsalida);
    unlink($_SESSION["generales"]["pathabsoluto"] . '/' . $name);
    \funcionesRegistrales::grabarPathAnexoRadicacion($mysqli, $id, $pathsalida);
}

function generarFormularioProponentesRepositorio($mysqli, $pro) {
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common.php');
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/configuracion/commonPredefiniciones.php');
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common' . $_SESSION["generales"]["codigoempresa"] . '.php');
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales.php');
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistralesCalculos.php');
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/genPdfsGenerales.php');
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/genIm7Generales.php');
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/genIm7Certificados.php');
    require_once ($_SESSION["generales"]["pathabsoluto"] . "/api/genPdfsProponentes_formulario2020.php");
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/genPdfsMercantil.php');
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/unirPdfs.php');

    $arrLiqDet = retornarRegistroMysqliApi($mysqli, 'mreg_liquidaciondatos', "idliquidacion=" . $_SESSION["tramite"]["numeroliquidacion"]);
    if (!empty($arrLiqDet)) {
        $xml = stripslashes($arrLiqDet["xml"]);
        $arrDatos = \funcionesGenerales::desserializarExpedienteProponente($mysqli, $xml, $_SESSION["generales"]["codigoempresa"]);

        // 2023-03-30: JINT: Si el trámite se encuentra firmado electrónicamente, el fgormulario lo genera con el hash de firmado
        $fir = false;
        $firms = retornarRegistrosMysqliApi($mysqli, 'mreg_firmadoelectronico_log',"idliquidacion=" . $_SESSION["tramite"]["numeroliquidacion"], "fecha,hora");
        if ($firms && !empty($firms)) {
            foreach ($firms as $fx) {
                if (trim((string)$fx["respuesta"]) != '') {
                    if (base64_encode(base64_decode($fx["respuesta"], true)) === $fx["respuesta"]) {
                        $fir = $fx;
                    }
                }
            }
        }
        if ($fir === false) {
            $name = armarPdfFormularioProponentes2020Api($mysqli, $arrDatos, $_SESSION["tramite"]["tipotramite"], $_SESSION["tramite"]["numeroliquidacion"], $_SESSION["tramite"]["numerorecuperacion"], '', '', '', 'final');
        } else {
            $nfirma= $fir["nombre1firmante"];
            if (trim((string)$fir["nombre2firmante"]) != '') {
                $nfirma .= ' ' . $fir["nombre2firmante"];
            }
            if (trim((string)$fir["apellido1firmante"]) != '') {
                $nfirma .= ' ' . $fir["apellido1firmante"];
            }
            if (trim((string)$fir["apellido2firmante"]) != '') {
                $nfirma .= ' ' . $fir["apellido2firmante"];
            }
            $name = armarPdfFormularioProponentes2020Api($mysqli, $arrDatos, $_SESSION["tramite"]["tipotramite"], $_SESSION["tramite"]["numeroliquidacion"], $_SESSION["tramite"]["numerorecuperacion"], $nfirma, $fir["identificacionfirmante"], $fir["tipoidefirmante"], 'final', base64_decode($fir["respuesta"]), \funcionesGenerales::mostrarFecha($fir["fecha"]), \funcionesGenerales::mostrarHora($fir["hora"]), '*** Regenerado ***');
        }

        $id = \funcionesRegistrales::grabarAnexoRadicacion(
                        $mysqli,
                        ltrim($_SESSION["tramite"]["numeroradicacion"], "0"), trim($_SESSION["tramite"]["numerorecibo"]), trim($_SESSION["tramite"]["numerooperacion"]), ltrim($_SESSION["tramite"]["identificacioncliente"], "0"), trim($_SESSION["tramite"]["nombrecliente"]), '', // Acreedor
                        '', // Nombre acreedor
                        '', // N&uacute;mero de matr&iacute;cula
                        ltrim($pro, "0"), TIPO_DOC_FOR_PROPONENTES, // Tipo de documento
                        '', // N&uacute;mero del documento
                        $_SESSION["tramite"]["fecharecibo"], '', // C&oacute;digo de origen
                        'EL PROPONENTE', '', // Clasificaci&oacute;n
                        '', // N&uacute;mero del contrato
                        '', // Idfuente
                        1, // versi&oacute;n
                        '', // Path
                        '1', // Estado
                        date("Ymd"), // fecha de escaneo o generaci&oacute;n
                        $_SESSION["generales"]["codigousuario"], '', // Caja
                        '', // Libro
                        'FORMULARIO DEL PROPONENTE',
                        '', // Numero del libro
                        '', // Numero del registro
                        '', // Dupli
                        '6.-REGPRO', // Bandeja de registro
                        'S', // Soporte del recibo de caja
                        '', // Identificador
                        '503' // Tipo de anexo
        );
        $dirx = date("Ymd");
        $path = PATH_ABSOLUTO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/mreg/' . $dirx;
        if (!is_dir($path) || !is_readable($path)) {
            mkdir($path, 0777);
            \funcionesGenerales::crearIndex($path);
        }

        $pathsalida = 'mreg/' . $dirx . '/' . $id . '.pdf';
        copy($_SESSION["generales"]["pathabsoluto"] . '/tmp/' . $name, $_SESSION["generales"]["pathabsoluto"] . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/' . $pathsalida);
        unlink($_SESSION["generales"]["pathabsoluto"] . '/tmp/' . $name);
        \funcionesRegistrales::grabarPathAnexoRadicacion($mysqli, $id, $pathsalida);

        unset($arrDatos);
        unset($arrIncon);
    }
    unset($arrLiqDet);
}

function generarFormularioRenovacionRepositorio($mysqli, $mat = '') {
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common.php');
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/configuracion/commonPredefiniciones.php');
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common' . $_SESSION["generales"]["codigoempresa"] . '.php');
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales.php');
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistralesCalculos.php');
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/genPdfsGenerales.php');
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/genIm7Generales.php');
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/genIm7Certificados.php');
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/genPdfsMercantil.php');
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/unirPdfs.php');

    //
    $iForm = 0;
    $iEstab = 0;
    $iPpal = 0;

    //
    $_SESSION["formulario"]["datos"] = array();
    $_SESSION["formulario"]["datoshijos"] = array();
    $_SESSION["formulario"]["matriculashijos"] = array();
    $_SESSION["formulario"]["tipotramite"] = $_SESSION["tramite"]["tipotramite"];

    //
    $datPrincipal = array();
    $textoFirmadoDatPrincipal = '';
    $tramasha1DatPrincipal = '';

    // $arrForms = retornarLiquidacionDatosExpedienteArregloXml($_SESSION["tramite"]["numeroliquidacion"]);
    $arrForms = retornarRegistrosMysqliApi($mysqli, 'mreg_liquidaciondatos', "idliquidacion=" . $_SESSION["tramite"]["numeroliquidacion"], "secuencia");
    if (!empty($arrForms)) {
        foreach ($arrForms as $form) {

            $x = \funcionesGenerales::desserializarExpedienteMatricula($mysqli, $form["xml"]);

            // 2018-10-17: JINT: Para generar formato de renovación de afiliacion
            if (defined('TIPO_DOC_FOR_AFILIACION') && TIPO_DOC_FOR_AFILIACION != '') {
                if ($_SESSION["tramite"]["tipotramite"] == 'renovacionmatricula') {
                    if ($x["organizacion"] == '01' || $x["organizacion"] > '02') {
                        if ($x["afiliado"] == '1') {
                            $datPrincipal = $x;
                        }
                    }
                }
            }

            if (
                    $x["organizacion"] == '01' ||
                    ($x["organizacion"] > '02' && $x["categoria"] == '1')
            ) {
                $_SESSION["formulario"]["datos"] = $x;
                $_SESSION["formulario"]["datos"]["fechaultimamodificacion"] = $_SESSION["tramite"]["fechaultimamodificacion"];
                $iPpal++;
            } else {
                $iEstab++;
                $_SESSION["formulario"]["datoshijos"][$iEstab] = $x;
                $_SESSION["formulario"]["datoshijos"][$iEstab]["fechaultimamodificacion"] = $_SESSION["tramite"]["fechaultimamodificacion"];
            }
            $iForm++;
        }


        if ($iForm > 0) {

            if ($iPpal > 0) {

                $det = '';

                if ($_SESSION["tramite"]["tipotramite"] == 'renovacionmatricula' ||
                        $_SESSION["tramite"]["tipotramite"] == 'renovacionesadl'
                ) {
                    $det = 'FORMULARIO DE RENOVACION - MATRICULA NO. ' . ltrim($_SESSION["formulario"]["datos"]["matricula"], "0");
                }

                if ($_SESSION["tramite"]["tipotramite"] == 'matriculapnat' ||
                        $_SESSION["tramite"]["tipotramite"] == 'matriculaest' ||
                        $_SESSION["tramite"]["tipotramite"] == 'matriculapjur'
                ) {
                    $det = 'FORMULARIO DE MATRICULA - MATRICULA NO. ' . ltrim($_SESSION["formulario"]["datos"]["matricula"], "0");
                }

                if ($_SESSION["tramite"]["tipotramite"] == 'matriculaesadl'
                ) {
                    $det = 'FORMULARIO DE INSCRIPCION AL REG-ESADL - NO. ' . ltrim($_SESSION["formulario"]["datos"]["matricula"], "0");
                }

                //
                if (!defined('FECHA_INICIAL_FORMULARIO_2020') || FECHA_INICIAL_FORMULARIO_2020 == '') {
                    $fecfor2020 = '20200803';
                } else {
                    $fecfor2020 = FECHA_INICIAL_FORMULARIO_2020;
                }

                //
                if ($_SESSION["tramite"]["fecha"] < $fecfor2020) {
                    $name = armarPdfPrincipalNuevo1082Api($mysqli, $_SESSION["tramite"]["numerorecuperacion"], $_SESSION["tramite"]["numeroliquidacion"], '');
                } else {
                    $name = armarPdfPrincipalNuevo2020Api($mysqli, $_SESSION["tramite"]["numerorecuperacion"], $_SESSION["tramite"]["numeroliquidacion"], '');
                }

                if (count($_SESSION["formulario"]["datos"]["f"]) > 1) {
                    $name1 = armarPdfPrincipalNuevoAnosAnteriores1082Api($mysqli, $_SESSION["tramite"]["numerorecuperacion"], $_SESSION["tramite"]["numeroliquidacion"], '');
                    unirPdfsApi(
                            array(
                                $_SESSION["generales"]["pathabsoluto"] . '/tmp/' . $name,
                                $_SESSION["generales"]["pathabsoluto"] . '/tmp/' . $name1
                            ),
                            $_SESSION["generales"]["pathabsoluto"] . '/tmp/' . $name
                    );
                }

                //
                $bandeja = '';
                if (substr(ltrim($_SESSION["formulario"]["datos"]["matricula"], "0"), 0, 1) == 'S') {
                    $bandeja = '5.-REGESADL';
                } else {
                    $bandeja = '4.-REGMER';
                }

                //
                $id = \funcionesRegistrales::grabarAnexoRadicacion(
                                $mysqli,
                                ltrim($_SESSION["tramite"]["numeroradicacion"], "0"), // Codigo barras
                                trim($_SESSION["tramite"]["numerorecibo"]), // Recibo
                                trim($_SESSION["tramite"]["numerooperacion"]), // Operacion
                                ltrim($_SESSION["tramite"]["identificacioncliente"], "0"), // Identificacion
                                trim($_SESSION["tramite"]["nombrecliente"]), // Nombre
                                '', // Acreedor
                                '', // Nombre acreedor
                                ltrim($_SESSION["formulario"]["datos"]["matricula"], "0"), // N&uacute;mero de matr&iacute;cula
                                '', // N&uacute;mero de proponente
                                TIPO_DOC_FOR_MERCANTIL, // Tipo de documento
                                '', // N&uacute;mero del documento
                                $_SESSION["tramite"]["fecharecibo"], '', // C&oacute;digo de origen
                                'EL COMERCIANTE', '', // Clasificaci&oacute;n
                                '', // N&uacute;mero del contrato
                                '', // Idfuente
                                1, // versi&oacute;n
                                '', // Path
                                '1', // Estado
                                date("Ymd"), // fecha de escaneo o generaci&oacute;n
                                $_SESSION["generales"]["codigousuario"],
                                '', // Caja
                                '', // Libro
                                $det,
                                '', // Libro del registro
                                '', // Numero del registro
                                $bandeja, // Bandeja de registro
                                '', // Dupli
                                'S', // Soporte del recibo
                                '', // identificador
                                '503' // Tipo anexo
                );

                $dirx = date("Ymd");
                $path = PATH_ABSOLUTO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/mreg/' . $dirx;
                if (!is_dir($path) || !is_readable($path)) {
                    mkdir($path, 0777);
                    \funcionesGenerales::crearIndex($path);
                }

                $pathsalida = 'mreg/' . $dirx . '/' . $id . '.pdf';
                copy($_SESSION["generales"]["pathabsoluto"] . '/tmp/' . $name, $_SESSION["generales"]["pathabsoluto"] . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/' . $pathsalida);
                unlink($_SESSION["generales"]["pathabsoluto"] . '/tmp/' . $name);
                \funcionesRegistrales::grabarPathAnexoRadicacion($mysqli, $id, $pathsalida);
            }

            if ($iEstab > 0) {

                foreach ($_SESSION["formulario"]["datoshijos"] as $hijo) {

                    $_SESSION["formulario"]["datos"] = $hijo;
                    $det = '';

                    if ($_SESSION["tramite"]["tipotramite"] == 'renovacionmatricula' ||
                            $_SESSION["tramite"]["tipotramite"] == 'renovacionesadl'
                    ) {
                        $det = 'FORMULARIO DE RENOVACION - MATRICULA NO. ' . ltrim($_SESSION["formulario"]["datos"]["matricula"], "0");
                    }

                    if ($_SESSION["tramite"]["tipotramite"] == 'matriculapnat' ||
                            $_SESSION["tramite"]["tipotramite"] == 'matriculaest' ||
                            $_SESSION["tramite"]["tipotramite"] == 'matriculapjur' ||
                            $_SESSION["tramite"]["tipotramite"] == 'matriculaesadl'
                    ) {
                        $det = 'FORMULARIO DE MATRICULA - MATRICULA NO. ' . ltrim($_SESSION["formulario"]["datos"]["matricula"], "0");
                    }

                    $name = armarPdfEstablecimientoNuevo1082Api($mysqli, $_SESSION["tramite"]["numerorecuperacion"], $_SESSION["tramite"]["numeroliquidacion"], '');

                    if (count($_SESSION["formulario"]["datos"]["f"]) > 1) {
                        $name1 = armarPdfEstablecimientoNuevoAnosAnteriores1082Api($mysqli, $_SESSION["tramite"]["numerorecuperacion"], $_SESSION["tramite"]["numeroliquidacion"], '');
                        unirPdfsApi(
                                array(
                                    $_SESSION["generales"]["pathabsoluto"] . '/tmp/' . $name, 
                                    $_SESSION["generales"]["pathabsoluto"] . '/tmp/' . $name1
                                ), 
                                $_SESSION["generales"]["pathabsoluto"] . '/tmp/' . $name
                        );
                    }


                    //
                    $bandeja = '';
                    if (substr(ltrim($_SESSION["formulario"]["datos"]["matricula"], "0"), 0, 1) == 'S') {
                        $bandeja = '5.-REGESADL';
                    } else {
                        $bandeja = '4.-REGMER';
                    }

                    $id = \funcionesRegistrales::grabarAnexoRadicacion(
                            $mysqli, // DB
                            ltrim($_SESSION["tramite"]["numeroradicacion"], "0"), trim($_SESSION["tramite"]["numerorecibo"]), trim($_SESSION["tramite"]["numerooperacion"]), ltrim($_SESSION["tramite"]["identificacioncliente"], "0"), trim($_SESSION["tramite"]["nombrecliente"]), '', // Acreedor
                            '', // Nombre acreedor
                            ltrim($_SESSION["formulario"]["datos"]["matricula"], "0"), // N&uacute;mero de matr&iacute;cula
                            '', // N&uacute;mero de proponente
                            TIPO_DOC_FOR_MERCANTIL, // Tipo de documento
                            '', // N&uacute;mero del documento
                            $_SESSION["tramite"]["fecharecibo"], '', // C&oacute;digo de origen
                            'EL COMERCIANTE', '', // Clasificaci&oacute;n
                            '', // N&uacute;mero del contrato
                            '', // Idfuente
                            1, // versi&oacute;n
                            '', // Path
                            '1', // Estado
                            date("Ymd"), // fecha de escaneo o generaci&oacute;n
                            $_SESSION["generales"]["codigousuario"],
                            '', // Caja
                            '', // Libro
                            $det,
                            '', // Libro del registro
                            '', // Numero del registro
                            '', // Dupli
                            $bandeja, // Bandeja de registro
                            'S', // Soporte del recibo
                            '', // Identificador
                            '503' // Tipo de anexo
                    );

                    $dirx = date("Ymd");
                    $path = PATH_ABSOLUTO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/mreg/' . $dirx;
                    if (!is_dir($path) || !is_readable($path)) {
                        mkdir($path, 0777);
                        \funcionesGenerales::crearIndex($path);
                    }

                    $pathsalida = 'mreg/' . $dirx . '/' . $id . '.pdf';
                    copy($_SESSION["generales"]["pathabsoluto"] . '/tmp/' . $name, $_SESSION["generales"]["pathabsoluto"]. '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/' . $pathsalida);
                    unlink($_SESSION["generales"]["pathabsoluto"] . '/tmp/' . $name);
                    \funcionesRegistrales::grabarPathAnexoRadicacion($mysqli,$id, $pathsalida);
                }
            }
        }
        unset($arrForms);
        unset($form);

        //
        if (!empty($datPrincipal)) {
            $bandeja = '4.-REGMER';
            $_SESSION["formulario"]["datos"] = $datPrincipal;
            $_SESSION["formulario"]["numrec"] = $_SESSION["tramite"]["numerorecuperacion"];
            $name = armarPdfFormatoAfiliacion($mysqli,$_SESSION["tramite"]["numerorecuperacion"], $_SESSION["tramite"]["numeroliquidacion"], '', '');
            $det = 'Formato de renovación de afiliación matrícula No. ' . $datPrincipal["matricula"];
            $id = \funcionesRegistrales::grabarAnexoRadicacion(
                    $mysqli, // DB
                    ltrim($_SESSION["tramite"]["numeroradicacion"], "0"), // COdigo barras
                    trim($_SESSION["tramite"]["numerorecibo"]), // Recibo
                    trim($_SESSION["tramite"]["numerooperacion"]), // Operacion
                    ltrim($_SESSION["tramite"]["identificacioncliente"], "0"), // identificacion
                    trim($_SESSION["tramite"]["nombrecliente"]), // Nombre
                    '', // Acreedor
                    '', // Nombre acreedor
                    ltrim($_SESSION["formulario"]["datos"]["matricula"], "0"), // Numero de matricula
                    '', // N&uacute;mero de proponente
                    TIPO_DOC_FOR_AFILIACION, // Tipo de documento
                    '', // Numero del documento
                    $_SESSION["tramite"]["fecharecibo"], // fecha del recibo
                    '', // Codigo de origen
                    'EL COMERCIANTE', '', // Clasificacion
                    '', // Numero del contrato
                    '', // Idfuente
                    1, // version
                    '', // Path
                    '1', // Estado
                    date("Ymd"), // fecha de escaneo o generacion
                    $_SESSION["generales"]["codigousuario"], // Usuario que genera
                    '', // Caja
                    '', // Libro
                    $det, // Detalle
                    '', // Libro del registro
                    '', // Numero del registro
                    '', // Dupli
                    $bandeja, // Bandeja de registro
                    'S', // Soporte del recibo
                    '', // Identificador
                    '503' // Tipo de anexo
            );

            $dirx = date("Ymd");
            $path = PATH_ABSOLUTO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/mreg/' . $dirx;
            if (!is_dir($path) || !is_readable($path)) {
                mkdir($path, 0777);
                \funcionesGenerales::crearIndex($path);
            }

            $pathsalida = 'mreg/' . $dirx . '/' . $id . '.pdf';
            copy($_SESSION["generales"]["pathabsoluto"] . '/tmp/' . $name, $_SESSION["generales"]["pathabsoluto"] . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/' . $pathsalida);
            unlink($_SESSION["generales"]["pathabsoluto"] . '/tmp/' . $name);
            \funcionesRegistrales::grabarPathAnexoRadicacion($mysqli,$id, $pathsalida);
        }
    }
}

?>
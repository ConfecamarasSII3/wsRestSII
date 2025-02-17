<?php

class funcionesFirmadoElectronico {

    /**
     * 
     * @param type $mysqli
     * @param type $fdate
     * @param type $fhora
     * @param type $fip
     * @param type $idefirmante
     * @param type $nomfirmante
     * @param string $nameLog
     * @param string $ambienteimagenes (to - Local y produccion)
     * @return type
     */
    public static function armarListaSoportes($mysqli, $fdate = '', $fhora = '', $fip = '', $idefirmante = '', $nomfirmante = '', $nameLog = '', $ambienteimagenes = 'to') {
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/genPdfsMercantil.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/genPdfsProponentes.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/genPdfsMutacionGeneral.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/genPdfsSolicitudCancelacion.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistralesCalculos.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesApiSii.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/unirPdfs.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
        if ($nameLog == '') {
            $nameLog = 'funcionesFirmadoElectronico_armarListaSoportes_' . date("Ymd");
        }

        \logApi::general2($nameLog, '', 'Entro a armar soportes del tramite');
        $listado = array();
        $iLista = 0;

        // Arma el nombre del firmante.
        if ($_SESSION["tramite"]["identificacionfirmante"] == '') {
            $xIdentificacion = $idefirmante;
            $xNombre = $nomfirmante;
        } else {
            $xIdentificacion = $_SESSION["tramite"]["identificacionfirmante"];
            $xNombre = $_SESSION["tramite"]["apellido1firmante"];
            if (trim($_SESSION["tramite"]["apellido2firmante"]) != '') {
                $xNombre .= ' ' . $_SESSION["tramite"]["apellido2firmante"];
            }
            if (trim($_SESSION["tramite"]["nombre1firmante"]) != '') {
                $xNombre .= ' ' . $_SESSION["tramite"]["nombre1firmante"];
            }
            if (trim($_SESSION["tramite"]["nombre2firmante"]) != '') {
                $xNombre .= ' ' . $_SESSION["tramite"]["nombre2firmante"];
            }
            if (trim($xNombre) == '') {
                $xNombre = $_SESSION["tramite"]["nombrefirmante"];
            }
            if ($xNombre == '') {
                if ($_SESSION["tramite"]["identificacionfirmante"] == $idefirmante) {
                    $xNombre = $nomfirmante;
                }
            }
        }

        // *************************************************************************** //
        // En caso de matrículas y renovaciones
        // *************************************************************************** //
        if ($_SESSION["tramite"]["tipotramite"] == 'matriculapnat' ||
                $_SESSION["tramite"]["tipotramite"] == 'matriculapjur' ||
                $_SESSION["tramite"]["tipotramite"] == 'matriculaest' ||
                $_SESSION["tramite"]["tipotramite"] == 'matriculasuc' ||
                $_SESSION["tramite"]["tipotramite"] == 'matriculaage' ||
                $_SESSION["tramite"]["tipotramite"] == 'matriculaesadl' ||
                $_SESSION["tramite"]["tipotramite"] == 'renovacionmatricula' ||
                $_SESSION["tramite"]["tipotramite"] == 'inscripciondocumentos' ||
                $_SESSION["tramite"]["tipotramite"] == 'renovacionesadl') {

            //
            $datPrincipal = array();
            $textoFirmadoDatPrincipal = '';
            $tramasha1DatPrincipal = '';

            //
            $arrForms = retornarRegistrosMysqliApi($mysqli, "mreg_liquidaciondatos", "idliquidacion=" . $_SESSION ["tramite"] ["numeroliquidacion"], "idliquidacion");
            if ($arrForms && !empty($arrForms)) {
                foreach ($arrForms as $form) {
                    $dat = \funcionesGenerales::desserializarExpedienteMatricula($mysqli, $form["xml"]);
                    $textofirmado = '';
                    $tramasha1 = '';
                    // ******************************************************************** //
                    // Arma la trama de firmado electrónico
                    // Para formularios del Registro mercantil
                    // armar un string con:
                    // - fecha
                    // - hora
                    // - pin
                    // - ip
                    // - tipotramite
                    // - nombrefirmante
                    // - identificacionfirmante
                    // - nombre
                    // - identificacion
                    // - muncom
                    // - dircom
                    // - munnot
                    // - dirnot
                    // - ciius[1]
                    // - sumatoria de la informacion financiera
                    // ---- acttot
                    // ---- pastot
                    // ---- pattot
                    // ---- utiope
                    // ---- utinet
                    // ---- actvin
                    // ---- personal
                    // ****************************************************************** //

                    $sumatoria = $dat["acttot"] + $dat["pastot"] + $dat["pattot"] +
                            $dat["utiope"] + $dat["utinet"] + $dat["actvin"] +
                            $dat["personal"];

                    $trama = $fdate . $fhora . $fip .
                            $_SESSION["tramite"]["tipotramite"] .
                            $xNombre .
                            $xIdentificacion .
                            $dat["nombre"] .
                            $dat["identificacion"] . $dat["muncom"] .
                            $dat["dircom"] . $dat["munnot"] . $dat["dirnot"] .
                            $dat["ciius"][1] . $sumatoria;

                    $tramasha1 = sha1($trama);

                    if ($_SESSION["tramite"]["tipotramite"] == 'renovacionmatricula' || $_SESSION["tramite"]["tipotramite"] == 'renovacionesadl') {
                        $textofirmado = 'SE FIRMÓ ELECTRÓNICAMENTE ' .
                                'EL FORMULARIO EL ' . \funcionesGenerales::mostrarFecha($fdate) . ' A LAS ' . $fhora . '. ' .
                                'HASH DE FIRMADO ' . $tramasha1;
                    } else {
                        $textofirmado = 'EL SEÑOR(A) ' . $xNombre . ' IDENTIFICADO(A) CON EL NÚMERO ' .
                                $xIdentificacion . ' FIRMÓ ELECTRÓNICAMENTE ' .
                                'EL FORMULARIO EL ' . \funcionesGenerales::mostrarFecha($fdate) . ' A LAS ' . $fhora . '. ' .
                                'HASH DE FIRMADO ' . $tramasha1;
                    }


                    //        
                    $_SESSION ["formulario"] ["datos"] = $dat;
                    $_SESSION ["formulario"] ["datos"] ["identificacionfirmante"] = $_SESSION["tramite"]["identificacionfirmante"];
                    $_SESSION ["formulario"] ["datos"] ["numidfirmante"] = $_SESSION["tramite"]["identificacionfirmante"];
                    $_SESSION ["formulario"] ["datos"] ["idclasefirmante"] = '';
                    $_SESSION ["formulario"] ["datos"] ["emailfirmante"] = $_SESSION["tramite"]["emailfirmante"];
                    $_SESSION ["formulario"] ["datos"] ["celularfirmante"] = $_SESSION["tramite"]["celularfirmante"];
                    $_SESSION ["formulario"] ["datos"] ["nombrefirmante"] = $_SESSION["tramite"]["nombrefirmante"];
                    $_SESSION ["formulario"] ["datos"] ["nombre1firmante"] = '';
                    $_SESSION ["formulario"] ["datos"] ["nombre2firmante"] = '';
                    $_SESSION ["formulario"] ["datos"] ["apellido1firmante"] = '';
                    $_SESSION ["formulario"] ["datos"] ["apellido2firmante"] = '';

                    $_SESSION ["formulario"] ["datos"] ["identificacionfirmante"] = $xIdentificacion;
                    $_SESSION ["formulario"] ["datos"] ["numidfirmante"] = $xIdentificacion;
                    $_SESSION ["formulario"] ["datos"] ["nombrefirmante"] = $xNombre;

                    //
                    $_SESSION ["formulario"] ["tipotramite"] = $_SESSION["tramite"]["tipotramite"];

                    //
                    if (($_SESSION ["formulario"] ["datos"] ["organizacion"] == '02') || ($_SESSION ["formulario"] ["datos"] ["categoria"] == '2') || ($_SESSION ["formulario"] ["datos"] ["categoria"] == '3')) {
                        $name = armarPdfEstablecimientoNuevo1082Api($mysqli, $_SESSION ["tramite"] ["numerorecuperacion"], $_SESSION ["tramite"] ["numeroliquidacion"], '', '', $textofirmado);
                        if (count($_SESSION ["formulario"] ["datos"] ["f"]) > 1) {
                            $name1 = armarPdfEstablecimientoNuevoAnosAnteriores1082Api($mysqli, $_SESSION ["tramite"] ["numerorecuperacion"], $_SESSION ["tramite"] ["numeroliquidacion"], '', $textofirmado);
                            unirPdfsApiV2(array(
                                $_SESSION["generales"]["pathabsoluto"] . '/tmp/' . $name,
                                $_SESSION["generales"]["pathabsoluto"] . '/tmp/' . $name1,
                                    ), $_SESSION["generales"]["pathabsoluto"] . '/tmp/' . $name
                            );
                        }
                        //
                        if (defined('TIPO_DOC_FOR_AFILIACION') && TIPO_DOC_FOR_AFILIACION != '' && ($_SESSION ["formulario"] ["datos"]["afiliado"] == '1' || $_SESSION ["formulario"] ["datos"]["afiliado"] == 'S')) {
                            $name3 = armarPdfFormatoAfiliacion($mysqli, $_SESSION["tramite"]["numerorecuperacion"], $_SESSION["tramite"]["numeroliquidacion"], $textofirmado, '');
                            unirPdfsApiV2(array($_SESSION["generales"]["pathabsoluto"] . '/tmp/' . $name, $_SESSION["generales"]["pathabsoluto"] . '/tmp/' . $name3), $_SESSION["generales"]["pathabsoluto"] . '/tmp/' . $name);
                        }
                    } else {
                        if (defined('ACTIVAR_IMPRESION_FORMULARIO_2023') && ACTIVAR_IMPRESION_FORMULARIO_2023 == 'SI') {
                            $name = armarPdfPrincipalNuevo2023Api($mysqli, $_SESSION ["tramite"] ["numerorecuperacion"], $_SESSION ["tramite"] ["numeroliquidacion"], '', 'no', $textofirmado);
                        } else {
                            $name = armarPdfPrincipalNuevo2020Api($mysqli, $_SESSION ["tramite"] ["numerorecuperacion"], $_SESSION ["tramite"] ["numeroliquidacion"], '', 'no', $textofirmado);
                        }

                        if (count($_SESSION["formulario"]["datos"]["f"]) > 1) {
                            $name1 = armarPdfPrincipalNuevoAnosAnteriores1082Api($mysqli, $_SESSION ["tramite"] ["numerorecuperacion"], $_SESSION ["tramite"] ["numeroliquidacion"], '', $textofirmado);
                            unirPdfsApiV2(array($_SESSION["generales"]["pathabsoluto"] . '/tmp/' . $name, $_SESSION["generales"]["pathabsoluto"] . '/tmp/' . $name1), $_SESSION["generales"]["pathabsoluto"] . '/tmp/' . $name);
                        }
                        if (!defined('ACTIVAR_IMPRESION_FORMULARIO_2023') || ACTIVAR_IMPRESION_FORMULARIO_2023 != 'SI') {
                            if ($_SESSION ["formulario"] ["datos"]["emprendimientosocial"] != '') {
                                $name2 = armarPdfFormatoEmpSoc($mysqli, $_SESSION["tramite"]["numerorecuperacion"], $_SESSION["tramite"]["numeroliquidacion"]);
                                unirPdfsApiV2(array($_SESSION["generales"]["pathabsoluto"] . '/tmp/' . $name, $_SESSION["generales"]["pathabsoluto"] . '/tmp/' . $name2), $_SESSION["generales"]["pathabsoluto"] . '/tmp/' . $name);
                            }
                        }
                        //
                        if (defined('TIPO_DOC_FOR_AFILIACION') && TIPO_DOC_FOR_AFILIACION != '' && ($_SESSION ["formulario"] ["datos"]["afiliado"] == '1' || $_SESSION ["formulario"] ["datos"]["afiliado"] == 'S')) {
                            $name3 = armarPdfFormatoAfiliacion($mysqli, $_SESSION["tramite"]["numerorecuperacion"], $_SESSION["tramite"]["numeroliquidacion"], $textofirmado, '');
                            unirPdfsApiV2(array($_SESSION["generales"]["pathabsoluto"] . '/tmp/' . $name, $_SESSION["generales"]["pathabsoluto"] . '/tmp/' . $name3), $_SESSION["generales"]["pathabsoluto"] . '/tmp/' . $name);
                        }
                    }
                    $iLista++;
                    if ($_SESSION["tramite"]["tipotramite"] == 'matriculapnat' ||
                            $_SESSION["tramite"]["tipotramite"] == 'matriculapjur' ||
                            $_SESSION["tramite"]["tipotramite"] == 'matriculaest' ||
                            $_SESSION["tramite"]["tipotramite"] == 'matriculasuc' ||
                            $_SESSION["tramite"]["tipotramite"] == 'matriculaage') {
                        $listado[$iLista]["observaciones"] = 'FORMULARIO DE MATRICULA';
                        $listado[$iLista]["idtipodoc"] = TIPO_DOC_FOR_MERCANTIL;
                        $listado[$iLista]["origendoc"] = 'EL COMERCIANTE';
                    }
                    if ($_SESSION["tramite"]["tipotramite"] == 'matriculaesadl') {
                        $listado[$iLista]["observaciones"] = 'FORMULARIO DE INSCRIPCION DE ESADL';
                        $listado[$iLista]["idtipodoc"] = TIPO_DOC_FOR_ESADL;
                        $listado[$iLista]["origendoc"] = 'LA ENTIDAD SIN ANIMO DE LUCRO';
                    }
                    if ($_SESSION["tramite"]["tipotramite"] == 'renovacionmatricula') {
                        if ($_SESSION ["formulario"] ["datos"]["organizacion"] == '01') {
                            $listado[$iLista]["observaciones"] = 'FORMULARIO DE RENOVACION DE PERSONA NATURAL';
                        }
                        if ($_SESSION ["formulario"] ["datos"]["organizacion"] == '02') {
                            $listado[$iLista]["observaciones"] = 'FORMULARIO DE RENOVACION DE ESTABLECIMIENTO DE COMERCIO';
                        }
                        if ($_SESSION ["formulario"] ["datos"]["organizacion"] > '02' && $_SESSION ["formulario"] ["datos"]["categoria"] == '1') {
                            $listado[$iLista]["observaciones"] = 'FORMULARIO DE RENOVACION DE PERSONA JURIDICA';
                        }
                        if ($_SESSION ["formulario"] ["datos"]["organizacion"] > '02' && $_SESSION ["formulario"] ["datos"]["categoria"] == '2') {
                            $listado[$iLista]["observaciones"] = 'FORMULARIO DE RENOVACION DE SUCURSAL';
                        }
                        if ($_SESSION ["formulario"] ["datos"]["organizacion"] > '02' && $_SESSION ["formulario"] ["datos"]["categoria"] == '3') {
                            $listado[$iLista]["observaciones"] = 'FORMULARIO DE RENOVACION DE AGENCIA';
                        }
                        $listado[$iLista]["idtipodoc"] = TIPO_DOC_FOR_MERCANTIL;
                        $listado[$iLista]["origendoc"] = 'EL COMERCIANTE';
                    }
                    if ($_SESSION["tramite"]["tipotramite"] == 'renovacionesadl') {
                        $listado[$iLista]["observaciones"] = 'FORMULARIO DE RENOVACION ESADL';
                        $listado[$iLista]["idtipodoc"] = TIPO_DOC_FOR_ESADL;
                        $listado[$iLista]["origendoc"] = 'LA ENTIDAD SIN ANIMO DE LUCRO';
                    }

                    //WSI - 20170814

                    if ($_SESSION["tramite"]["tipotramite"] == 'inscripciondocumentos') {
                        if ($_SESSION ["formulario"] ["datos"]["organizacion"] == '01') {
                            $listado[$iLista]["idtipodoc"] = TIPO_DOC_FOR_MERCANTIL;
                        }
                        if ($_SESSION ["formulario"] ["datos"]["organizacion"] == '02') {
                            $listado[$iLista]["idtipodoc"] = TIPO_DOC_FOR_MERCANTIL;
                        }
                        if ($_SESSION ["formulario"] ["datos"]["organizacion"] > '02' && $_SESSION ["formulario"] ["datos"]["categoria"] == '1') {
                            $listado[$iLista]["idtipodoc"] = TIPO_DOC_FOR_MERCANTIL;
                        }
                        if ($_SESSION ["formulario"] ["datos"]["organizacion"] > '02' && $_SESSION ["formulario"] ["datos"]["categoria"] == '2') {
                            $listado[$iLista]["idtipodoc"] = TIPO_DOC_FOR_MERCANTIL;
                        }
                        if ($_SESSION ["formulario"] ["datos"]["organizacion"] > '02' && $_SESSION ["formulario"] ["datos"]["categoria"] == '3') {
                            $listado[$iLista]["idtipodoc"] = TIPO_DOC_FOR_MERCANTIL;
                        }
                        if ($_SESSION ["formulario"] ["datos"]["organizacion"] == '12' || $_SESSION ["formulario"] ["datos"]["organizacion"] == '14') {
                            $listado[$iLista]["idtipodoc"] = TIPO_DOC_FOR_ESADL;
                        }

                        $listado[$iLista]["origendoc"] = 'EL COMERCIANTE';
                    }

                    if ($_SESSION["tramite"]["tipotramite"] == 'inscripciondocumentos') {
                        $listado[$iLista]["observaciones"] = 'FORMULARIO DE ACTOS Y DOCUMENTOS';
                        $listado[$iLista]["origendoc"] = 'EL COMERCIANTE';
                    }

                    $listado[$iLista]["numdoc"] = 'N/A';
                    $listado[$iLista]["fechadoc"] = date("Ymd");
                    $listado[$iLista]["tipoanexo"] = '503'; // formularios y solicitudes
                    $listado[$iLista]["matricula"] = $_SESSION ["formulario"] ["datos"]["matricula"];
                    $listado[$iLista]["proponente"] = '';
                    $listado[$iLista]["tipoarchivo"] = 'pdf';
                    $listado[$iLista]["hashfirmado"] = $tramasha1;

                    $mi_pdf = str_replace("/", "_", $name);
                    $pathx = $_SESSION["generales"]["pathabsoluto"] . '/tmp/' . $mi_pdf;

                    $listado[$iLista]["identificacion"] = $dat["identificacion"];
                    $listado[$iLista]["nombre"] = $dat["nombre"];
                    $listado[$iLista]["identificador"] = '';
                    $listado[$iLista]["pesoarchivo"] = filesize($pathx);
                    $listado[$iLista]["pathabsoluto"] = $pathx;
                    $listado[$iLista]["path"] = $mi_pdf;
                    $listado[$iLista]["ubicacion"] = 'tmp';

                    //
                    $dirx = date("Ymd");
                    $path = $_SESSION["generales"]["pathabsoluto"] . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/liquidacionmreg/' . $dirx;
                    if (!is_dir($path) || !is_readable($path)) {
                        mkdir($path, 0777);
                        \funcionesGenerales::crearIndex($path);
                    }

                    // ********************************************************************************************* //
                    // Se crea el anexo a la liquidación (liquidacionmreg) en la tabla mreg_anexos_liquidaciones
                    // Se obtiene el id del anexo
                    // ******************************************************************************************** //
                    $arrCampos = array(
                        'idliquidacion',
                        'identificador',
                        'expediente',
                        'tipoanexo',
                        'idradicacion',
                        'numerorecibo',
                        'numerooperacion',
                        'identificacion',
                        'nombre',
                        'idtipodoc',
                        'numdoc',
                        'fechadoc',
                        'txtorigendoc',
                        'path',
                        'tipoarchivo',
                        'observaciones',
                        'bandeja',
                        'eliminado'
                    );
                    $arrValores = array(
                        $_SESSION["tramite"]["idliquidacion"],
                        "'" . $listado[$iLista]["identificador"] . "'",
                        "'" . $listado[$iLista]["matricula"] . "'",
                        "'" . $listado[$iLista]["tipoanexo"] . "'",
                        0,
                        "''",
                        "''",
                        "'" . $listado[$iLista]["identificacion"] . "'",
                        "'" . $listado[$iLista]["nombre"] . "'",
                        "'" . $listado[$iLista]["idtipodoc"] . "'",
                        "'" . $listado[$iLista]["idtipodoc"] . "'",
                        "'" . $listado[$iLista]["fechadoc"] . "'",
                        "'" . $listado[$iLista]["origendoc"] . "'",
                        "'liquidacionmreg/" . $dirx . "/'",
                        "'" . $listado[$iLista]["tipoarchivo"] . "'",
                        "'" . $listado[$iLista]["observaciones"] . "'",
                        "'" . '4.-REGMER' . "'",
                        "'NO'"
                    );
                    insertarRegistrosMysqliApi($mysqli, 'mreg_anexos_liquidaciones', $arrCampos, $arrValores, 'si');
                    $idanexo = $_SESSION["generales"]["lastId"];

                    // *********************************************************************** //
                    // Se traslada el pdf al directorio donde debe quedar almacenado
                    // *********************************************************************** //        
                    copy($pathx, $_SESSION["generales"]["pathabsoluto"] . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/liquidacionmreg/' . $dirx . '/' . $idanexo . '.' . $listado[$iLista]["tipoarchivo"]);
                    $listado[$iLista]["path"] = 'liquidacionmreg/' . $dirx . '/' . $idanexo . '.' . $listado[$iLista]["tipoarchivo"];
                    $listado[$iLista]["pathabsoluto"] = $_SESSION["generales"]["pathabsoluto"] . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/liquidacionmreg/' . $dirx . '/' . $idanexo . '.' . $listado[$iLista]["tipoarchivo"];
                    $listado[$iLista]["ubicacion"] = 'repolocal';

                    // *********************************************************************** //
                    // Se borra el temporal
                    // *********************************************************************** //
                    unlink($pathx);
                    unset($arrForms);
                    unset($form);
                }
            }

            // ********************************************************************************** //
            // 2018-10-17: JINT: Adición formato de afiliación
            // Solicitud de la CC de Ibagué
            // ********************************************************************************** //
            if (!empty($datPrincipal)) {
                $_SESSION["formulario"]["datos"] = $datPrincipal;
                $_SESSION["formulario"]["numrec"] = $_SESSION ["tramite"] ["numerorecuperacion"];
                $name = armarPdfFormatoAfiliacion($mysqli, $_SESSION ["tramite"] ["numerorecuperacion"], $_SESSION ["tramite"] ["numeroliquidacion"], $textoFirmadoDatPrincipal, '');
                $iLista++;
                if ($_SESSION["tramite"]["tipotramite"] == 'renovacionmatricula') {
                    if ($_SESSION ["formulario"] ["datos"]["organizacion"] == '01') {
                        $listado[$iLista]["observaciones"] = 'FORMATO RENOVACION AFILIACION DE PERSONA NATURAL';
                    }
                    if ($_SESSION ["formulario"] ["datos"]["organizacion"] > '02' && $_SESSION ["formulario"] ["datos"]["categoria"] == '1') {
                        $listado[$iLista]["observaciones"] = 'FORMATO RENOVACION AFILIACION DE PERSONA JURIDICA';
                    }
                    if ($_SESSION ["formulario"] ["datos"]["organizacion"] > '02' && $_SESSION ["formulario"] ["datos"]["categoria"] == '2') {
                        $listado[$iLista]["observaciones"] = 'FORMATO RENOVACION AFILIACION DE SUCURSAL';
                    }
                    $listado[$iLista]["idtipodoc"] = TIPO_DOC_FOR_AFILIACION;
                    $listado[$iLista]["origendoc"] = 'EL COMERCIANTE';
                    $listado[$iLista]["numdoc"] = 'N/A';
                    $listado[$iLista]["fechadoc"] = date("Ymd");
                    $listado[$iLista]["tipoanexo"] = '503'; // formularios y solicitudes
                    $listado[$iLista]["matricula"] = $_SESSION ["formulario"] ["datos"]["matricula"];
                    $listado[$iLista]["proponente"] = '';
                    $listado[$iLista]["tipoarchivo"] = 'pdf';
                    $listado[$iLista]["hashfirmado"] = $tramasha1DatPrincipal;

                    $mi_pdf = str_replace("/", "_", $name);
                    $pathx = $_SESSION["generales"]["pathabsoluto"] . '/tmp/' . $mi_pdf;

                    $listado[$iLista]["identificacion"] = $_SESSION["formulario"]["datos"]["identificacion"];
                    $listado[$iLista]["nombre"] = $_SESSION["formulario"]["datos"]["nombre"];
                    $listado[$iLista]["identificador"] = '';
                    $listado[$iLista]["pesoarchivo"] = filesize($pathx);
                    $listado[$iLista]["path"] = $pathx;

                    //
                    $dirx = date("Ymd");
                    $path = $_SESSION["generales"]["pathabsoluto"] . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/liquidacionmreg/' . $dirx;
                    if (!is_dir($path) || !is_readable($path)) {
                        mkdir($path, 0777);
                        \funcionesGenerales::crearIndex($path);
                    }

                    // *********************************************************************** //
                    // Se crea el anexo a la liquidación (liquidacionmreg) en la tabla mreg_anexos_liquidaciones
                    // Se obtiene el id del anexo
                    // *********************************************************************** //
                    $arrCampos = array(
                        'idliquidacion',
                        'identificador',
                        'expediente',
                        'tipoanexo',
                        'idradicacion',
                        'numerorecibo',
                        'numerooperacion',
                        'identificacion',
                        'nombre',
                        'idtipodoc',
                        'numdoc',
                        'fechadoc',
                        'txtorigendoc',
                        'path',
                        'tipoarchivo',
                        'observaciones',
                        'bandeja',
                        'eliminado'
                    );
                    $arrValores = array(
                        $_SESSION["tramite"]["idliquidacion"],
                        "'" . $listado[$iLista]["identificador"] . "'",
                        "'" . $listado[$iLista]["matricula"] . "'",
                        "'" . $listado[$iLista]["tipoanexo"] . "'",
                        0,
                        "''",
                        "''",
                        "'" . $listado[$iLista]["identificacion"] . "'",
                        "'" . $listado[$iLista]["nombre"] . "'",
                        "'" . $listado[$iLista]["idtipodoc"] . "'",
                        "'" . $listado[$iLista]["idtipodoc"] . "'",
                        "'" . $listado[$iLista]["fechadoc"] . "'",
                        "'" . $listado[$iLista]["origendoc"] . "'",
                        "'liquidacionmreg/" . $dirx . "/'",
                        "'" . $listado[$iLista]["tipoarchivo"] . "'",
                        "'" . $listado[$iLista]["observaciones"] . "'",
                        "'" . '4.-REGMER' . "'",
                        "'NO'"
                    );
                    insertarRegistrosMysqliApi($mysqli, 'mreg_anexos_liquidaciones', $arrCampos, $arrValores, 'si');
                    $idanexo = $_SESSION["generales"]["lastId"];

                    // *********************************************************************** //
                    // Se traslada el pdf al directorio donde debe quedar almacenado
                    // *********************************************************************** //        
                    copy($pathx, $_SESSION["generales"]["pathabsoluto"] . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/liquidacionmreg/' . $dirx . '/' . $idanexo . '.' . $listado[$iLista]["tipoarchivo"]);
                    $listado[$iLista]["path"] = 'liquidacionmreg/' . $dirx . '/' . $idanexo . '.' . $listado[$iLista]["tipoarchivo"];
                    $listado[$iLista]["pathabsoluto"] = $_SESSION["generales"]["pathabsoluto"] . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/liquidacionmreg/' . $dirx . '/' . $idanexo . '.' . $listado[$iLista]["tipoarchivo"];
                    $listado[$iLista]["ubicacion"] = 'repolocal';
                }
            }
        }

        // *************************************************************************** //
        // En caso de tramites de proponentes
        // *************************************************************************** //
        if ($_SESSION["tramite"]["tipotramite"] == 'inscripcionproponente' ||
                $_SESSION["tramite"]["tipotramite"] == 'renovacionproponente' ||
                $_SESSION["tramite"]["tipotramite"] == 'actualizacionproponente' ||
                $_SESSION["tramite"]["tipotramite"] == 'cancelacionproponente') {

            $_SESSION["formulario"] = array();
            $_SESSION["formulario"]["version"] = '1510';
            $_SESSION["formulario"]["grabandodesde"] = 'ModuloPublico';
            $_SESSION["formulario"]["liquidacion"] = $_SESSION["tramite"]["numeroliquidacion"];
            $_SESSION["formulario"]["fecha"] = $_SESSION["tramite"]["fecha"];
            $_SESSION["formulario"]["hora"] = $_SESSION["tramite"]["hora"];
            $_SESSION["formulario"]["tipotramite"] = $_SESSION["tramite"]["tipotramite"];
            $_SESSION["formulario"]["funcion"] = '';
            $_SESSION["formulario"]["matricula"] = $_SESSION["tramite"]["expedientes"][1]["matricula"];
            $_SESSION["formulario"]["proponente"] = $_SESSION["tramite"]["expedientes"][1]["proponente"];
            $_SESSION["formulario"]["numrue"] = $_SESSION["tramite"]["expedientes"][1]["numrue"];
            $_SESSION["formulario"]["idestadotramite"] = $_SESSION["tramite"]["idestado"];
            $_SESSION["formulario"]["numerorecuperacion"] = $_SESSION["tramite"]["numerorecuperacion"];
            $_SESSION["formulario"]["numeroradicacion"] = $_SESSION["tramite"]["numeroradicacion"];

            //
            if ($_SESSION["tramite"]["expedientes"][1]["organizacion"] == '01') {
                $_SESSION["formulario"]["idtipoidentificacion"] = $_SESSION["tramite"]["expedientes"][1]["idtipoidentificacion"];
                $_SESSION["formulario"]["identificacion"] = $_SESSION["tramite"]["expedientes"][1]["identificacion"];
            } else {
                $_SESSION["formulario"]["idtipoidentificacion"] = '2';
                $_SESSION["formulario"]["identificacion"] = $_SESSION["tramite"]["expedientes"][1]["numrue"];
            }
            $_SESSION["formulario"]["razonsocial"] = $_SESSION["tramite"]["expedientes"][1]["razonsocial"];
            $_SESSION["formulario"]["ape1"] = $_SESSION["tramite"]["expedientes"][1]["ape1"];
            $_SESSION["formulario"]["ape2"] = $_SESSION["tramite"]["expedientes"][1]["ape2"];
            $_SESSION["formulario"]["nom1"] = $_SESSION["tramite"]["expedientes"][1]["nom1"];
            $_SESSION["formulario"]["nom2"] = $_SESSION["tramite"]["expedientes"][1]["nom2"];
            $_SESSION["formulario"]["organizacion"] = $_SESSION["tramite"]["expedientes"][1]["organizacion"];
            $_SESSION["formulario"]["datos"] = array();

            //
            $ret = retornarRegistroMysqliApi($mysqli, "mreg_liquidaciondatos", "idliquidacion=" . $_SESSION ["tramite"] ["numeroliquidacion"]);
            if ($ret && !empty($ret)) {
                \logApi::general2($nameLog, '', 'Entro a desserializar formulario de proponentes');
                $_SESSION["formulario"]["datos"] = \funcionesGenerales::desserializarExpedienteProponente($mysqli, $ret["xml"]);
                \logApi::general2($nameLog, '', 'Desserializo formulario de proponentes');
                // \logApi::general2($nameLog, '', print_r($_SESSION["formulario"]["datos"], true));
                if (($_SESSION["formulario"]["datos"]["organizacion"] != '01') && (trim($_SESSION["formulario"]["datos"]["matricula"]) != '')) {
                    if ($_SESSION["formulario"]["tipotramite"] == 'inscripcionproponente' ||
                            $_SESSION["formulario"]["tipotramite"] == 'cambiodomicilioproponente') {
                        $ret1 = \funcionesRegistrales::retornarExpedienteProponente($mysqli, $_SESSION["formulario"]["proponente"], $_SESSION["formulario"]["matricula"]);
                    } else {
                        $ret1 = \funcionesRegistrales::retornarExpedienteProponente($mysqli, $_SESSION["formulario"]["proponente"]);
                    }
                }
            }

            if (($_SESSION["formulario"]["datos"]["organizacion"] != '01') && (trim($_SESSION["formulario"]["datos"]["matricula"]) != '')) {
                if (!empty($ret1)) {
                    if (trim($ret1["facultades"]) != '') {
                        $_SESSION["formulario"]["datos"]["facultades"] = $ret1["facultades"];
                    }
                    if (!empty($ret1["representantes"])) {
                        $_SESSION["formulario"]["datos"]["representantes"] = $ret1["representantes"];
                    }
                }
            }
            unset($ret);
            unset($ret1);

            $textofirmado = '';
            $tramasha1 = '';
            $cantcla = 0;
            $cantcont = 0;
            $sumcla = 0;
            $sumcont = 0;
            $sumfin = 0;

            //
            foreach ($_SESSION["formulario"]["datos"]["clasi1510"] as $c) {
                $cantcla++;
                $sumcla = $sumcla + intval($c);
            }

            //
            foreach ($_SESSION["formulario"]["datos"]["exp1510"] as $c) {
                $cantcont++;
                $sumcont = $sumcont + intval($c["valor"]);
            }

            $trama = $fdate . $fhora . $fip .
                    $_SESSION["tramite"]["tipotramite"] .
                    $xNombre .
                    $xIdentificacion .
                    $_SESSION["formulario"]["datos"]["nombre"] .
                    $_SESSION["formulario"]["datos"]["identificacion"] .
                    $_SESSION["formulario"]["datos"]["muncom"] .
                    $_SESSION["formulario"]["datos"]["dircom"] .
                    $_SESSION["formulario"]["datos"]["munnot"] .
                    $_SESSION["formulario"]["datos"]["dirnot"] .
                    $cantcla .
                    $cantcont .
                    $sumcla .
                    $sumcont .
                    $sumfin;

            $tramasha1 = sha1($trama);

            // *********************************************************************** //
            // Se arma el texto que se envía a la rutina de generación de PDFs
            // para incluir el firmado electrónico
            // *********************************************************************** //
            $textofirmado = 'EL SEÑOR(A) ' . $xNombre . ' IDENTIFICADO(A) CON EL NÚMERO ' .
                    $xIdentificacion . ' FIRMO ELECTRÓNICAMENTE ' .
                    'EL FORMULARIO EL ' . \funcionesGenerales::mostrarFecha($fdate) . ' A LAS ' . $fhora . ' ' .
                    'HASH DE FIRMADO ' . $tramasha1;

            // *********************************************************************** //
            // Se genera el pdf con el formulario en el directorio tmp
            // *********************************************************************** //
            if (!defined('FECHA_INICIAL_FORMULARIO_2020') || FECHA_INICIAL_FORMULARIO_2020 == '') {
                $fecfor2020 = '20200803';
            } else {
                $fecfor2020 = FECHA_INICIAL_FORMULARIO_2020;
            }
            if (date("Ymd") >= $fecfor2020) {
                $name = armarPdfFormularioProponentes2020Api($mysqli, $_SESSION["formulario"]["datos"], $_SESSION["formulario"]["tipotramite"], $_SESSION["formulario"]["liquidacion"], $_SESSION["formulario"]["numerorecuperacion"], '', '', '', 'final', $textofirmado);
            } else {
                $name = armarPdfFormularioProponentes1082Api($mysqli, $_SESSION["formulario"]["datos"], $_SESSION["formulario"]["tipotramite"], $_SESSION["formulario"]["liquidacion"], $_SESSION["formulario"]["numerorecuperacion"], '', '', '', 'final', $textofirmado);
            }

            // *********************************************************************** //
            // Se adiciona el anexo a la lista de anexos
            // *********************************************************************** //        
            $iLista++;
            $listado[$iLista]["observaciones"] = 'FORMULARIO DE PROPONENTE';
            $listado[$iLista]["identificador"] = '';
            $listado[$iLista]["idtipodoc"] = TIPO_DOC_FOR_PROPONENTES;
            $listado[$iLista]["origendoc"] = 'EL PROPONENTE';
            $listado[$iLista]["numdoc"] = 'N/A';
            $listado[$iLista]["fechadoc"] = date("Ymd");
            $listado[$iLista]["tipoanexo"] = '503'; // formularios y solicitudes
            $listado[$iLista]["matricula"] = '';
            $listado[$iLista]["proponente"] = $_SESSION["formulario"]["proponente"];
            $listado[$iLista]["tipoarchivo"] = 'pdf';
            $listado[$iLista]["hashfirmado"] = $tramasha1;
            $listado[$iLista]["identificacion"] = $_SESSION["formulario"]["datos"]["identificacion"];
            $listado[$iLista]["nombre"] = $_SESSION["formulario"]["datos"]["nombre"];
            $listado[$iLista]["identificador"] = '';
            $listado[$iLista]["pesoarchivo"] = filesize($_SESSION["generales"]["pathabsoluto"] . '/tmp/' . $name);

            //
            $dirx = date("Ymd");
            $path = $_SESSION["generales"]["pathabsoluto"] . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/liquidacionmreg/' . $dirx;
            if (!is_dir($path) || !is_readable($path)) {
                mkdir($path, 0777);
                \funcionesGenerales::crearIndex($path);
            }


            // *********************************************************************** //
            // Se crea el anexo a la liquidación (liquidacionmreg) en la tabla mreg_anexos_liquidaciones
            // Se obtiene el id del anexo
            // *********************************************************************** //
            $arrCampos = array(
                'idliquidacion',
                'identificador',
                'expediente',
                'tipoanexo',
                'idradicacion',
                'numerorecibo',
                'numerooperacion',
                'identificacion',
                'nombre',
                'idtipodoc',
                'numdoc',
                'fechadoc',
                'txtorigendoc',
                'path',
                'tipoarchivo',
                'observaciones',
                'bandeja',
                'eliminado'
            );
            $arrValores = array(
                $_SESSION["tramite"]["idliquidacion"],
                "'" . $listado[$iLista]["identificador"] . "'",
                "'" . $listado[$iLista]["proponente"] . "'",
                "'" . $listado[$iLista]["tipoanexo"] . "'",
                0,
                "''",
                "''",
                "'" . $listado[$iLista]["identificacion"] . "'",
                "'" . $listado[$iLista]["nombre"] . "'",
                "'" . $listado[$iLista]["idtipodoc"] . "'",
                "'" . $listado[$iLista]["idtipodoc"] . "'",
                "'" . $listado[$iLista]["fechadoc"] . "'",
                "'" . $listado[$iLista]["origendoc"] . "'",
                "'liquidacionmreg/" . $dirx . "/'",
                "'" . $listado[$iLista]["tipoarchivo"] . "'",
                "'" . $listado[$iLista]["observaciones"] . "'",
                "'" . '6.-REGPRO' . "'",
                "'NO'"
            );
            insertarRegistrosMysqliApi($mysqli, 'mreg_anexos_liquidaciones', $arrCampos, $arrValores, 'si');
            $idanexo = $_SESSION["generales"]["lastId"];

            // *********************************************************************** //
            // Se traslada el pdf al directorio donde debe quedar almacenado
            // *********************************************************************** //        
            copy($_SESSION["generales"]["pathabsoluto"] . '/tmp/' . $name, $_SESSION["generales"]["pathabsoluto"] . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/liquidacionmreg/' . $dirx . '/' . $idanexo . '.' . $listado[$iLista]["tipoarchivo"]);
            $listado[$iLista]["path"] = 'liquidacionmreg/' . $dirx . '/' . $idanexo . '.' . $listado[$iLista]["tipoarchivo"];
            $listado[$iLista]["pathabsoluto"] = $_SESSION["generales"]["pathabsoluto"] . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/liquidacionmreg/' . $dirx . '/' . $idanexo . '.' . $listado[$iLista]["tipoarchivo"];
            $listado[$iLista]["ubicacion"] = 'repolocal';

            // *********************************************************************** //
            // Se borra el temporal
            // *********************************************************************** //
            unlink($_SESSION["generales"]["pathabsoluto"] . '/tmp/' . $name);
            // }
            // *********************************************************************** //
            // Se destruye en memoria el formulario
            // *********************************************************************** //
            unset($_SESSION["formulario"]);
        }

        if ($_SESSION["tramite"]["tipotramite"] == 'cambiodomicilioproponente') {
            $_SESSION["formulario"] = array();
            $_SESSION["formulario"]["version"] = '1510';
            $_SESSION["formulario"]["grabandodesde"] = 'ModuloPublico';
            $_SESSION["formulario"]["liquidacion"] = $_SESSION["tramite"]["numeroliquidacion"];
            $_SESSION["formulario"]["fecha"] = $_SESSION["tramite"]["fecha"];
            $_SESSION["formulario"]["hora"] = $_SESSION["tramite"]["hora"];
            $_SESSION["formulario"]["tipotramite"] = $_SESSION["tramite"]["tipotramite"];
            $_SESSION["formulario"]["funcion"] = '';
            $_SESSION["formulario"]["matricula"] = $_SESSION["tramite"]["expedientes"][1]["matricula"];
            $_SESSION["formulario"]["proponente"] = $_SESSION["tramite"]["expedientes"][1]["proponente"];
            $_SESSION["formulario"]["numrue"] = $_SESSION["tramite"]["expedientes"][1]["numrue"];
            $_SESSION["formulario"]["idestadotramite"] = $_SESSION["tramite"]["idestado"];
            $_SESSION["formulario"]["numerorecuperacion"] = $_SESSION["tramite"]["numerorecuperacion"];
            $_SESSION["formulario"]["numeroradicacion"] = $_SESSION["tramite"]["numeroradicacion"];

            //
            if ($_SESSION["tramite"]["expedientes"][1]["organizacion"] == '01') {
                $_SESSION["formulario"]["idtipoidentificacion"] = $_SESSION["tramite"]["expedientes"][1]["idtipoidentificacion"];
                $_SESSION["formulario"]["identificacion"] = $_SESSION["tramite"]["expedientes"][1]["identificacion"];
            } else {
                $_SESSION["formulario"]["idtipoidentificacion"] = '2';
                $_SESSION["formulario"]["identificacion"] = $_SESSION["tramite"]["expedientes"][1]["numrue"];
            }
            $_SESSION["formulario"]["razonsocial"] = $_SESSION["tramite"]["expedientes"][1]["razonsocial"];
            $_SESSION["formulario"]["ape1"] = $_SESSION["tramite"]["expedientes"][1]["ape1"];
            $_SESSION["formulario"]["ape2"] = $_SESSION["tramite"]["expedientes"][1]["ape2"];
            $_SESSION["formulario"]["nom1"] = $_SESSION["tramite"]["expedientes"][1]["nom1"];
            $_SESSION["formulario"]["nom2"] = $_SESSION["tramite"]["expedientes"][1]["nom2"];
            $_SESSION["formulario"]["organizacion"] = $_SESSION["tramite"]["expedientes"][1]["organizacion"];

            //
            $_SESSION["formulario"]["datos"] = \funcionesGenerales::desserializarExpedienteProponente($mysqli, '');

            $_SESSION["formulario"]["datos"]["tipotramite"] = 'cambiodomicilioproponente';
            $_SESSION["formulario"]["datos"]["numerorecuperacion"] = $_SESSION["tramite"]["numerorecuperacion"];
            $_SESSION["formulario"]["datos"]["numeroliquidacion"] = $_SESSION["tramite"]["idliquidacion"];

            // Datos de Identificaci&oacute;n del expediente
            $_SESSION["formulario"]["datos"]["proponente"] = $_SESSION["tramite"]["idproponentebase"];
            $_SESSION["formulario"]["datos"]["matricula"] = $_SESSION["tramite"]["idmatriculabase"];
            $_SESSION["formulario"]["datos"]["nombre"] = $_SESSION["tramite"]["expedientes"][1]["razonsocial"];
            $_SESSION["formulario"]["datos"]["ape1"] = $_SESSION["tramite"]["expedientes"][1]["ape1"];
            $_SESSION["formulario"]["datos"]["ape2"] = $_SESSION["tramite"]["expedientes"][1]["ape2"];
            $_SESSION["formulario"]["datos"]["nom1"] = $_SESSION["tramite"]["expedientes"][1]["nom1"];
            $_SESSION["formulario"]["datos"]["nom2"] = $_SESSION["tramite"]["expedientes"][1]["nom2"];
            $_SESSION["formulario"]["datos"]["tipoidentificacion"] = $_SESSION["tramite"]["expedientes"][1]["idtipoidentificacion"];
            $_SESSION["formulario"]["datos"]["identificacion"] = $_SESSION["tramite"]["expedientes"][1]["identificacion"];
            $_SESSION["formulario"]["datos"]["idpaisidentificacion"] = '';
            $_SESSION["formulario"]["datos"]["nit"] = $_SESSION["tramite"]["expedientes"][1]["numrue"];
            $_SESSION["formulario"]["datos"]["propcamaraorigen"] = $_SESSION["tramite"]["propcamaraorigen"];
            $_SESSION["formulario"]["datos"]["propcamaraorigennombre"] = retornarRegistroMysqliApi($mysqli, 'bas_camaras', "id='" . $_SESSION["tramite"]["propcamaraorigen"] . "'", "descripcion");
            $_SESSION["formulario"]["datos"]["cambidom_idmunicipioorigen"] = '';
            $_SESSION["formulario"]["datos"]["cambidom_idmunicipiodestino"] = '';
            $_SESSION["formulario"]["datos"]["cambidom_fechaultimainscripcion"] = $_SESSION["tramite"]["propfechaultimainscripcion"];
            $_SESSION["formulario"]["datos"]["cambidom_fechaultimarenovacion"] = $_SESSION["tramite"]["propfechaultimarenovacion"];
            $_SESSION["formulario"]["datos"]["dircom"] = $_SESSION["tramite"]["propdircom"];
            $_SESSION["formulario"]["datos"]["muncom"] = $_SESSION["tramite"]["propmuncom"];
            $_SESSION["formulario"]["datos"]["telcom1"] = $_SESSION["tramite"]["proptelcom1"];
            $_SESSION["formulario"]["datos"]["telcom2"] = $_SESSION["tramite"]["proptelcom2"];
            $_SESSION["formulario"]["datos"]["celcom"] = $_SESSION["tramite"]["proptelcom3"];
            $_SESSION["formulario"]["datos"]["emailcom"] = $_SESSION["tramite"]["propemailcom"];

            // informaci&oacute;n de ubicaci&oacute;n de notificacion
            $_SESSION["formulario"]["datos"]["dirnot"] = $_SESSION["tramite"]["propdirnot"];
            $_SESSION["formulario"]["datos"]["munnot"] = $_SESSION["tramite"]["propmunnot"];
            $_SESSION["formulario"]["datos"]["telnot"] = $_SESSION["tramite"]["proptelnot1"];
            $_SESSION["formulario"]["datos"]["telnot2"] = $_SESSION["tramite"]["proptelnot2"];
            $_SESSION["formulario"]["datos"]["celnot"] = $_SESSION["tramite"]["proptelnot3"];
            $_SESSION["formulario"]["datos"]["emailnot"] = $_SESSION["tramite"]["propemailnot"];

            $textofirmado = '';
            $tramasha1 = '';
            $cantcla = 0;
            $cantcont = 0;
            $sumcla = 0;
            $sumcont = 0;
            $sumfin = 0;

            //
            foreach ($_SESSION["formulario"]["datos"]["clasi1510"] as $c) {
                $cantcla++;
                $sumcla = $sumcla + intval($c);
            }

            //
            foreach ($_SESSION["formulario"]["datos"]["exp1510"] as $c) {
                $cantcont++;
                $sumcont = $sumcont + intval($c["valor"]);
            }

            $trama = $fdate . $fhora . $fip .
                    $_SESSION["tramite"]["tipotramite"] .
                    $xNombre .
                    $xIdentificacion .
                    $_SESSION["formulario"]["datos"]["nombre"] .
                    $_SESSION["formulario"]["datos"]["identificacion"] .
                    $_SESSION["formulario"]["datos"]["muncom"] .
                    $_SESSION["formulario"]["datos"]["dircom"] .
                    $_SESSION["formulario"]["datos"]["munnot"] .
                    $_SESSION["formulario"]["datos"]["dirnot"] .
                    $cantcla .
                    $cantcont .
                    $sumcla .
                    $sumcont .
                    $sumfin;

            $tramasha1 = sha1($trama);

            // *********************************************************************** //
            // Se arma el texto que se envía a la rutina de generación de PDFs
            // para incluir el firmado electrónico
            // *********************************************************************** //
            $textofirmado = 'EL SEÑOR(A) ' . $xNombre . ' IDENTIFICADO(A) CON EL NÚMERO ' .
                    $xIdentificacion . ' FIRMO ELECTRÓNICAMENTE ' .
                    'EL FORMULARIO EL ' . \funcionesGenerales::mostrarFecha($fdate) . ' A LAS ' . $fhora . '. ' .
                    'HASH DE FIRMADO ' . $tramasha1;

            // *********************************************************************** //
            // Se genera el pdf con el formulario en el directorio tmp
            // *********************************************************************** //
            $name = armarPdfFormularioProponentes1082Api($mysqli, $_SESSION["formulario"]["datos"], $_SESSION["formulario"]["tipotramite"], $_SESSION["formulario"]["liquidacion"], $_SESSION["formulario"]["numerorecuperacion"], '', '', '', 'final', $textofirmado);

            // *********************************************************************** //
            // Se adiciona el anexo a la lista de anexos
            // *********************************************************************** //        
            $iLista++;
            $listado[$iLista]["observaciones"] = 'FORMULARIO DE PROPONENTE';
            $listado[$iLista]["identificador"] = '';
            $listado[$iLista]["idtipodoc"] = TIPO_DOC_FOR_PROPONENTES;
            $listado[$iLista]["origendoc"] = 'EL PROPONENTE';
            $listado[$iLista]["numdoc"] = 'N/A';
            $listado[$iLista]["fechadoc"] = date("Ymd");
            $listado[$iLista]["tipoanexo"] = '503'; // formularios y solicitudes
            $listado[$iLista]["matricula"] = '';
            $listado[$iLista]["proponente"] = $_SESSION["formulario"]["proponente"];
            $listado[$iLista]["tipoarchivo"] = 'pdf';
            $listado[$iLista]["hashfirmado"] = $tramasha1;
            $listado[$iLista]["identificacion"] = $_SESSION["formulario"]["datos"]["identificacion"];
            $listado[$iLista]["nombre"] = $_SESSION["formulario"]["datos"]["nombre"];
            $listado[$iLista]["identificador"] = '';
            $listado[$iLista]["pesoarchivo"] = filesize($_SESSION["generales"]["pathabsoluto"] . '/tmp/' . $name);

            //
            $dirx = date("Ymd");
            $path = $_SESSION["generales"]["pathabsoluto"] . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/liquidacionmreg/' . $dirx;
            if (!is_dir($path) || !is_readable($path)) {
                mkdir($path, 0777);
                \funcionesGenerales::crearIndex($path);
            }


            // *********************************************************************** //
            // Se crea el anexo a la liquidación (liquidacionmreg) en la tabla mreg_anexos_liquidaciones
            // Se obtiene el id del anexo
            // *********************************************************************** //
            $arrCampos = array(
                'idliquidacion',
                'identificador',
                'expediente',
                'tipoanexo',
                'idradicacion',
                'numerorecibo',
                'numerooperacion',
                'identificacion',
                'nombre',
                'idtipodoc',
                'numdoc',
                'fechadoc',
                'txtorigendoc',
                'path',
                'tipoarchivo',
                'observaciones',
                'bandeja',
                'eliminado'
            );
            $arrValores = array(
                $_SESSION["tramite"]["idliquidacion"],
                "'" . $listado[$iLista]["identificador"] . "'",
                "'" . $listado[$iLista]["proponente"] . "'",
                "'" . $listado[$iLista]["tipoanexo"] . "'",
                0,
                "''",
                "''",
                "'" . $listado[$iLista]["identificacion"] . "'",
                "'" . $listado[$iLista]["nombre"] . "'",
                "'" . $listado[$iLista]["idtipodoc"] . "'",
                "'" . $listado[$iLista]["idtipodoc"] . "'",
                "'" . $listado[$iLista]["fechadoc"] . "'",
                "'" . $listado[$iLista]["origendoc"] . "'",
                "'liquidacionmreg/" . $dirx . "/'",
                "'" . $listado[$iLista]["tipoarchivo"] . "'",
                "'" . $listado[$iLista]["observaciones"] . "'",
                "'" . '6.-REGPRO' . "'",
                "'NO'"
            );
            insertarRegistrosMysqliApi($mysqli, 'mreg_anexos_liquidaciones', $arrCampos, $arrValores, 'si');
            $idanexo = $_SESSION["generales"]["lastId"];

            // *********************************************************************** //
            // Se traslada el pdf al directorio donde debe quedar almacenado
            // *********************************************************************** //        
            copy($_SESSION["generales"]["pathabsoluto"] . '/tmp/' . $name, $_SESSION["generales"]["pathabsoluto"] . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/liquidacionmreg/' . $dirx . '/' . $idanexo . '.' . $listado[$iLista]["tipoarchivo"]);
            $listado[$iLista]["path"] = 'liquidacionmreg/' . $dirx . '/' . $idanexo . '.' . $listado[$iLista]["tipoarchivo"];
            $listado[$iLista]["pathabsoluto"] = $_SESSION["generales"]["pathabsoluto"] . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/liquidacionmreg/' . $dirx . '/' . $idanexo . '.' . $listado[$iLista]["tipoarchivo"];
            $listado[$iLista]["ubicacion"] = 'repolocal';

            // *********************************************************************** //
            // Se borra el temporal
            // *********************************************************************** //
            unlink($_SESSION["generales"]["pathabsoluto"] . '/tmp/' . $name);
            // }
            // *********************************************************************** //
            // Se destruye en memoria el formulario
            // *********************************************************************** //
            unset($_SESSION["formulario"]);
        }


        // *************************************************************************** //
        // En caso de mutaciones
        // *************************************************************************** //
        if ($_SESSION["tramite"]["tipotramite"] == 'mutacionregmer' || $_SESSION["tramite"]["tipotramite"] == 'mutacionesadl') {

            $_SESSION["formulario"]["datos"] = array();
            $arrForms = retornarRegistrosMysqliApi($mysqli, "mreg_liquidaciondatos", "idliquidacion=" . $_SESSION ["tramite"] ["numeroliquidacion"], "idliquidacion");
            foreach ($arrForms as $form) {
                $_SESSION["formulario"]["datos"] = \funcionesGenerales::desserializarExpedienteMatricula($mysqli, $form["xml"]);
                $_SESSION["formulario"]["datosanteriores"] = \funcionesRegistrales::retornarExpedienteMercantil($mysqli, $_SESSION["formulario"]["datos"]["matricula"], '', '', '', 'si', 'N');
                if (substr($_SESSION["formulario"]["datos"]["matricula"], 0, 1) == 'S') {
                    $clavevalor = TIPO_DOC_MUTACION_ESADL;
                } else {
                    $clavevalor = TIPO_DOC_MUTACION_MERCANTIL;
                }

                //
                $_SESSION["formulario"]["numrec"] = $_SESSION["tramite"]["numerorecuperacion"];

                $textofirmado = '';
                $tramasha1 = '';
                $trama = $fdate . $fhora . $fip .
                        $_SESSION["tramite"]["tipotramite"] .
                        $xNombre .
                        $xIdentificacion .
                        $_SESSION["formulario"]["datos"]["nombre"] .
                        $_SESSION["formulario"]["datos"]["identificacion"] .
                        $_SESSION["formulario"]["datos"]["matricula"] .
                        $_SESSION["formulario"]["datos"]["muncom"] .
                        $_SESSION["formulario"]["datos"]["dircom"] .
                        $_SESSION["formulario"]["datos"]["telcom1"] .
                        $_SESSION["formulario"]["datos"]["telcom2"] .
                        $_SESSION["formulario"]["datos"]["celcom"] .
                        $_SESSION["formulario"]["datos"]["emailcom"] .
                        $_SESSION["formulario"]["datos"]["munnot"] .
                        $_SESSION["formulario"]["datos"]["dirnot"] .
                        $_SESSION["formulario"]["datos"]["telnot"] .
                        $_SESSION["formulario"]["datos"]["telnot2"] .
                        $_SESSION["formulario"]["datos"]["celnot"] .
                        $_SESSION["formulario"]["datos"]["emailnot"];

                $tramasha1 = sha1($trama);

                $textofirmado = 'EL SEÑOR(A) ' . $xNombre . ' IDENTIFICADO(A) CON EL NÚMERO ' .
                        $xIdentificacion . ' FIRMO ELECTRÓNICAMENTE ' .
                        'LA SOLICITUD DE MUTACION EL ' . \funcionesGenerales::mostrarFecha($fdate) . ' A LAS ' . $fhora . ' ' .
                        'DANDO FE DEL CONTENIDO DE LA MISMA. HASH DE FIRMADO ' . $tramasha1;

                $name1 = armarPdfMutacionGeneralTcpdf($mysqli, $_SESSION["tramite"]["numerorecuperacion"], $_SESSION["tramite"]["numeroliquidacion"], $textofirmado);
                $name = $_SESSION["generales"]["pathabsoluto"] . '/tmp/' . $name1;
                $mi_pdf = $name;
                $pathx = $mi_pdf;

                if (!file_exists($pathx)) {
                    \logApi::general2($nameLog, $_SESSION["tramite"]["numerorecuperacion"], 'No fue posible crear el archivo pdf de la mutacion ' . $pathx);
                } else {
                    $iLista++;
                    $listado[$iLista]["observaciones"] = 'SOLICITUD MUTACION (ACTUALIZACION DE DATOS)';
                    $listado[$iLista]["idtipodoc"] = $clavevalor;
                    $listado[$iLista]["origendoc"] = 'EL COMERCIANTE';

                    $listado[$iLista]["numdoc"] = 'N/A';
                    $listado[$iLista]["fechadoc"] = date("Ymd");
                    $listado[$iLista]["tipoanexo"] = '503'; // formularios y solicitudes
                    $listado[$iLista]["matricula"] = $_SESSION ["formulario"] ["datos"]["matricula"];
                    $listado[$iLista]["proponente"] = '';
                    $listado[$iLista]["tipoarchivo"] = 'pdf';
                    $listado[$iLista]["hashfirmado"] = $tramasha1;

                    $listado[$iLista]["identificacion"] = $_SESSION["formulario"]["datos"]["identificacion"];
                    $listado[$iLista]["nombre"] = $_SESSION["formulario"]["datos"]["nombre"];
                    $listado[$iLista]["identificador"] = '';
                    $listado[$iLista]["pesoarchivo"] = filesize($pathx);
                    $listado[$iLista]["path"] = $pathx;
                    $listado[$iLista]["pathabsoluto"] = $_SESSION["generales"]["pathabsoluto"] . '/tmp/' . $name1;
                    $listado[$iLista]["path"] = $name1;
                    $listado[$iLista]["ubicacion"] = 'tmp';

                    //
                    $dirx = date("Ymd");
                    $path = $_SESSION["generales"]["pathabsoluto"] . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/liquidacionmreg/' . $dirx;
                    if (!is_dir($path) || !is_readable($path)) {
                        mkdir($path, 0777);
                        \funcionesGenerales::crearIndex($path);
                    }


                    // *********************************************************************** //
                    // Se crea el anexo a la liquidación (liquidacionmreg) en la tabla mreg_anexos_liquidaciones
                    // Se obtiene el id del anexo
                    // *********************************************************************** //
                    $arrCampos = array(
                        'idliquidacion',
                        'identificador',
                        'expediente',
                        'tipoanexo',
                        'idradicacion',
                        'numerorecibo',
                        'numerooperacion',
                        'identificacion',
                        'nombre',
                        'idtipodoc',
                        'numdoc',
                        'fechadoc',
                        'txtorigendoc',
                        'path',
                        'tipoarchivo',
                        'observaciones',
                        'bandeja',
                        'eliminado'
                    );
                    $arrValores = array(
                        $_SESSION["tramite"]["idliquidacion"],
                        "'" . $listado[$iLista]["identificador"] . "'",
                        "'" . $listado[$iLista]["matricula"] . "'",
                        "'" . $listado[$iLista]["tipoanexo"] . "'",
                        0,
                        "''",
                        "''",
                        "'" . $listado[$iLista]["identificacion"] . "'",
                        "'" . $listado[$iLista]["nombre"] . "'",
                        "'" . $listado[$iLista]["idtipodoc"] . "'",
                        "'" . $listado[$iLista]["numdoc"] . "'",
                        "'" . $listado[$iLista]["fechadoc"] . "'",
                        "'" . $listado[$iLista]["origendoc"] . "'",
                        "'liquidacionmreg/" . $dirx . "/'",
                        "'" . $listado[$iLista]["tipoarchivo"] . "'",
                        "'" . $listado[$iLista]["observaciones"] . "'",
                        "'" . '4.-REGMER' . "'",
                        "'NO'"
                    );
                    insertarRegistrosMysqliApi($mysqli, 'mreg_anexos_liquidaciones', $arrCampos, $arrValores, 'si');
                    $idanexo = $_SESSION["generales"]["lastId"];

                    // *********************************************************************** //
                    // Se traslada el pdf al directorio donde debe quedar almacenado
                    // *********************************************************************** //   
                    $pathsalida = $_SESSION["generales"]["pathabsoluto"] . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/liquidacionmreg/' . $dirx . '/' . $idanexo . '.' . $listado[$iLista]["tipoarchivo"];
                    if (copy($pathx, $pathsalida)) {
                        $listado[$iLista]["path"] = 'liquidacionmreg/' . $dirx . '/' . $idanexo . '.' . $listado[$iLista]["tipoarchivo"];
                        $listado[$iLista]["pathabsoluto"] = $_SESSION["generales"]["pathabsoluto"] . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/liquidacionmreg/' . $dirx . '/' . $idanexo . '.' . $listado[$iLista]["tipoarchivo"];
                        $listado[$iLista]["ubicacion"] = 'repolocal';
                    } else {
                        \logApi::general2($nameLog, $_SESSION["tramite"]["numerorecuperacion"], 'Error pasando soporte al repositorio, origen: ' . $pathx . ', salida: ' . $pathsalida);
                        borrarRegistrosMysqliApi($mysqli, 'mreg_anexos_liquidaciones', "idanexo=" . $idanexo);
                    }

                    // *********************************************************************** //
                    // Se borra el temporal
                    // *********************************************************************** //
                    unlink($pathx);
                }

                unset($arrForms);
                unset($form);
                // }
            }
        }

        // *************************************************************************** //
        // En caso de solicitudes de cancelacion
        // *************************************************************************** //
        if ($_SESSION["tramite"]["tipotramite"] == 'solicitudcancelacionpnat' || $_SESSION["tramite"]["tipotramite"] == 'solicitudcancelacionest') {
            $textofirmado = '';
            $tramasha1 = '';
            $trama = $fdate . $fhora . $fip .
                    $_SESSION["tramite"]["tipotramite"] .
                    $xNombre .
                    $xIdentificacion .
                    $_SESSION["tramite"]["idmotivocancelacion"] .
                    $_SESSION["tramite"]["motivocancelacion"];

            $tramasha1 = sha1($trama);

            $textofirmado = 'EL SEÑOR(A) ' . $xNombre . ' IDENTIFICADO(A) CON EL NÚMERO ' .
                    $xIdentificacion . ' FIRMO ELECTRÓNICAMENTE ' .
                    'LA SOLICITUD DE CANCELACIÓN EL ' . \funcionesGenerales::mostrarFecha($fdate) . ' A LAS ' . \funcionesGenerales::mostrarHora($fhora) . ' ' .
                    'DANDO FE DEL CONTENIDO DE LA MISMA. HASH DE FIRMADO ' . $tramasha1;
            \logApi::general2($nameLog, $_SESSION["tramite"]["idliquidacion"], $textofirmado);
            $name = armarPdfSolicitudCancelacion($mysqli, $textofirmado, $xIdentificacion, $xNombre);

            $iLista++;
            $listado[$iLista]["observaciones"] = 'SOLICITUD DE CANCELACION';
            $listado[$iLista]["idtipodoc"] = $clavevalor;
            $listado[$iLista]["origendoc"] = 'EL COMERCIANTE';

            $listado[$iLista]["numdoc"] = 'N/A';
            $listado[$iLista]["fechadoc"] = date("Ymd");
            $listado[$iLista]["tipoanexo"] = '503'; // formularios y solicitudes
            $listado[$iLista]["matricula"] = $_SESSION ["tramite"] ["idmatriculabase"];
            $listado[$iLista]["proponente"] = '';
            $listado[$iLista]["tipoarchivo"] = 'pdf';
            $listado[$iLista]["hashfirmado"] = $tramasha1;

            $mi_pdf = $_SESSION["generales"]["pathabsoluto"] . '/tmp/' . $name;
            $pathx = $mi_pdf;

            $listado[$iLista]["identificacion"] = $xIdentificacion;
            $listado[$iLista]["nombre"] = $xNombre;
            $listado[$iLista]["identificador"] = '';
            $listado[$iLista]["pesoarchivo"] = filesize($pathx);
            $listado[$iLista]["pathabsoluto"] = $_SESSION["generales"]["pathabsoluto"] . '/tmp/' . $name;
            $listado[$iLista]["path"] = $name;
            $listado[$iLista]["ubicacion"] = 'tmp';

            //
            $dirx = date("Ymd");
            $path = $_SESSION["generales"]["pathabsoluto"] . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/liquidacionmreg/' . $dirx;
            if (!is_dir($path) || !is_readable($path)) {
                mkdir($path, 0777);
                \funcionesGenerales::crearIndex($path);
            }


            // *********************************************************************** //
            // Se crea el anexo a la liquidación (liquidacionmreg) en la tabla mreg_anexos_liquidaciones
            // Se obtiene el id del anexo
            // *********************************************************************** //
            $arrCampos = array(
                'idliquidacion',
                'identificador',
                'expediente',
                'tipoanexo',
                'idradicacion',
                'numerorecibo',
                'numerooperacion',
                'identificacion',
                'nombre',
                'idtipodoc',
                'numdoc',
                'fechadoc',
                'txtorigendoc',
                'path',
                'tipoarchivo',
                'observaciones',
                'bandeja',
                'eliminado'
            );
            $arrValores = array(
                $_SESSION["tramite"]["idliquidacion"],
                "'" . $listado[$iLista]["identificador"] . "'",
                "'" . $listado[$iLista]["matricula"] . "'",
                "'" . $listado[$iLista]["tipoanexo"] . "'",
                0,
                "''",
                "''",
                "'" . $listado[$iLista]["identificacion"] . "'",
                "'" . $listado[$iLista]["nombre"] . "'",
                "'" . $listado[$iLista]["idtipodoc"] . "'",
                "'" . $listado[$iLista]["idtipodoc"] . "'",
                "'" . $listado[$iLista]["fechadoc"] . "'",
                "'" . $listado[$iLista]["origendoc"] . "'",
                "'liquidacionmreg/" . $dirx . "/'",
                "'" . $listado[$iLista]["tipoarchivo"] . "'",
                "'" . $listado[$iLista]["observaciones"] . "'",
                "'" . '4.-REGMER' . "'",
                "'NO'"
            );
            insertarRegistrosMysqliApi($mysqli, 'mreg_anexos_liquidaciones', $arrCampos, $arrValores, 'si');
            $idanexo = $_SESSION["generales"]["lastId"];

            // *********************************************************************** //
            // Se traslada el pdf al directorio donde debe quedar almacenado
            // *********************************************************************** //        
            copy($pathx, $_SESSION["generales"]["pathabsoluto"] . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/liquidacionmreg/' . $dirx . '/' . $idanexo . '.' . $listado[$iLista]["tipoarchivo"]);
            $listado[$iLista]["path"] = 'liquidacionmreg/' . $dirx . '/' . $idanexo . '.' . $listado[$iLista]["tipoarchivo"];
            $listado[$iLista]["pathabsoluto"] = $_SESSION["generales"]["pathabsoluto"] . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/liquidacionmreg/' . $dirx . '/' . $idanexo . '.' . $listado[$iLista]["tipoarchivo"];
            $listado[$iLista]["ubicacion"] = 'repolocal';

            // *********************************************************************** //
            // Se borra el temporal
            // *********************************************************************** //
            unlink($pathx);

            unset($arrForms);
            unset($form);
            // }
        }


        // *************************************************************************** //
        // En caso de radicadión de libros electrónicos
        // - Apertura de libro electrónico
        // - Anotacion al libro electrónico
        // *************************************************************************** //
        // *************************************************************************** //
        // Lee los soportes anexados al tramite
        // *************************************************************************** //
        $arrTem = retornarRegistrosMysqliApi($mysqli, 'mreg_anexos_liquidaciones', "idliquidacion=" . $_SESSION["tramite"]["idliquidacion"] . " and tipoanexo <> '503' and eliminado <> 'SI'", "idanexo");
        foreach ($arrTem as $t) {
            $iLista++;
            $listado[$iLista]["observaciones"] = $t["observaciones"];
            $listado[$iLista]["identificador"] = $t["identificador"];
            $listado[$iLista]["idtipodoc"] = $t["idtipodoc"];
            $listado[$iLista]["origendoc"] = $t["txtorigendoc"];
            $listado[$iLista]["numdoc"] = $t["numdoc"];
            $listado[$iLista]["fechadoc"] = $t["fechadoc"];
            $listado[$iLista]["tipoanexo"] = $t["tipoanexo"];
            if ($_SESSION["tramite"]["tipotramite"] == 'inscripcionproponente' ||
                    $_SESSION["tramite"]["tipotramite"] == 'renovacionproponente' ||
                    $_SESSION["tramite"]["tipotramite"] == 'actualizacionproponente' ||
                    $_SESSION["tramite"]["tipotramite"] == 'cancelacionproponente' ||
                    $_SESSION["tramite"]["tipotramite"] == 'cambiodomicilioproponente') {
                $listado[$iLista]["matricula"] = '';
                $listado[$iLista]["proponente"] = $t["expediente"];
            } else {
                $listado[$iLista]["matricula"] = $t["expediente"];
                $listado[$iLista]["proponente"] = '';
            }
            $listado[$iLista]["tipoarchivo"] = $t["tipoarchivo"];
            $listado[$iLista]["identificacion"] = $t["identificacion"];
            $listado[$iLista]["nombre"] = $t["nombre"];
            $listado[$iLista]["pesoarchivo"] = 0;
            $listado[$iLista]["pathabsoluto"] = '';
            $listado[$iLista]["pathrepositorio"] = '';
            $listado[$iLista]["link"] = '';
            $listado[$iLista]["ubicacion"] = '';
            $listado[$iLista]["error"] = '';
            $listado[$iLista]["encontro"] = '';
            if ($ambienteimagenes = 'to') {
                if (file_exists($_SESSION["generales"]["pathabsoluto"] . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/' . $t["path"] . '/' . $t["idanexo"] . '.' . $t["tipoarchivo"])) {
                    $listado[$iLista]["pesoarchivo"] = filesize($_SESSION["generales"]["pathabsoluto"] . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/' . $t["path"] . '/' . $t["idanexo"] . '.' . $t["tipoarchivo"]);
                    $listado[$iLista]["pathabsoluto"] = $_SESSION["generales"]["pathabsoluto"] . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/' . $t["path"] . '/' . $t["idanexo"] . '.' . $t["tipoarchivo"];
                    $listado[$iLista]["pathrepositorio"] = $t["path"] . '/' . $t["idanexo"] . '.' . $t["tipoarchivo"];
                    $listado[$iLista]["path"] = $t["path"] . '/' . $t["idanexo"] . '.' . $t["tipoarchivo"];
                    $listado[$iLista]["ubicacion"] = 'repolocal';
                    $listado[$iLista]["encontro"] = 'si';
                }
            }
            if ($listado[$iLista]["encontro"] != 'si') {
                $url = \funcionesGenerales::retornarUrlSiiCoreProduccion($_SESSION["generales"]["codigoempresa"]) . '/librerias/wsRestSII/v1/';
                $resanexo = \funcionesApiSii::apiRecuperarAnexoLiquidacion($t["idanexo"], $_SESSION["tramite"]["emailfirmante"], $_SESSION["tramite"]["identificacionfirmante"], $_SESSION["tramite"]["celularfirmante"], $url, $nameLog);
                if ($resanexo === false) {
                    $listado[$iLista]["error"] = 'No fue posible recuperar el anexo No. ' . $t["idanexo"] . ' - respuesta erronea del metodo apiRecuperarAnexoLiquidacion';
                } else {
                    if ($resanexo["codigoerror"] == '0000') {
                        if ($resanexo["link"] != '') {
                            $listado[$iLista]["link"] = $resanexo["link"];
                            $listado[$iLista]["ubicacion"] = 'efs/s3produccion';
                            $aleatanexo = \funcionesGenerales::generarAleatorioAlfanumerico10();
                            $contenido = file_get_contents($resanexo["link"]);
                            $f = fopen(PATH_ABSOLUTO_SITIO . '/tmp/' . $aleatanexo . '.pdf', "w");
                            fwrite($f, $contenido);
                            fclose($f);
                            $listado[$iLista]["pesoarchivo"] = filesize(PATH_ABSOLUTO_SITIO . '/tmp/' . $aleatanexo . '.pdf');
                            $listado[$iLista]["pathabsoluto"] = PATH_ABSOLUTO_SITIO . '/tmp/' . $aleatanexo . '.pdf';
                            $listado[$iLista]["pathrepositorio"] = $t["path"] . '/' . $t["idanexo"] . '.' . $t["tipoarchivo"];
                            $listado[$iLista]["path"] = $aleatanexo . '.pdf';
                        } else {
                            $listado[$iLista]["error"] = 'No fue posible recuperar el anexo No. ' . $t["idanexo"] . ' - No se obtuvo link';
                        }
                    } else {
                        $listado[$iLista]["error"] = $resanexo["mensajeerror"] . ' - Anexo No. ' . $t["idanexo"];
                    }
                }
            }

            $textofirmado = '';
            $tramasha1 = '';
            if ($listado[$iLista]["pathabsoluto"] != '') {
                $textoarchivo = base64_encode(file_get_contents($listado[$iLista]["pathabsoluto"]));
            } else {
                $textoarchivo = '';
            }
            $trama = $fdate . $fhora . $fip .
                    $_SESSION["tramite"]["tipotramite"] .
                    $xNombre .
                    $xIdentificacion .
                    $textoarchivo;

            $tramasha1 = sha1($trama);

            $listado[$iLista]["hashfirmado"] = $tramasha1;
        }

        $tarreglosoportes = print_r($listado, true);
        \logApi::general2($nameLog, '', $tarreglosoportes);
        return $listado;
    }

    public static function getNumPagesInPDF($filePath) {
        if (!file_exists($filePath)) {
            return 0;
        }
        if (!$fp = @fopen($filePath, "r")) {
            return 0;
        }
        $i = 0;
        $type = "/Contents";
        while (!feof($fp)) {
            $line = fgets($fp, 255);
            $x = explode($type, $line);
            if (count($x) > 1) {
                $i++;
            }
        }
        fclose($fp);
        return (int) $i;
    }

}

?>
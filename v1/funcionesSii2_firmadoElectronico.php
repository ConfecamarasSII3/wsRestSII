<?php

class funcionesSii2_firmadoElectronico {

    /**
     * 
     * @param type $mysqli
     * @param type $fdate
     * @param type $fhora
     * @param type $fip
     * @return type
     */
    public static function armarListaSoportes($mysqli, $fdate = '', $fhora = '', $fip = '') {
        require_once ('genPdfsMercantil.php');
        require_once ('genPdfsMutacionGeneral.php');
        require_once ('genPdfsSolicitudCancelacion.php');
        require_once ('funcionesSii2.php');
        require_once ('funcionesSii2_desserializaciones.php');

        // require_once ('genPdfsMutacionGeneral.php');
        // require_once ('genPdfsFormatoAfiliacion.php');
        // require_once ('genPdfsSolicitudCancelacion.php');

        $listado = array();
        $iLista = 0;

        // Arma el nombre del firmante.
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
            $arrForms = retornarRegistrosMysqli2($mysqli, "mreg_liquidaciondatos", "idliquidacion=" . $_SESSION ["tramite"] ["numeroliquidacion"], "idliquidacion");
            if ($arrForms && !empty($arrForms)) {
                foreach ($arrForms as $form) {
                    $dat = \funcionesSii2::desserializarExpedienteMatricula($mysqli, $form["xml"]);
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
                            $_SESSION["tramite"]["identificacionfirmante"] .
                            $dat["nombre"] .
                            $dat["identificacion"] . $dat["muncom"] .
                            $dat["dircom"] . $dat["munnot"] . $dat["dirnot"] .
                            $dat["ciius"][1] . $sumatoria;

                    $tramasha1 = sha1($trama);

                    if ($_SESSION["tramite"]["tipotramite"] == 'renovacionmatricula' || $_SESSION["tramite"]["tipotramite"] == 'renovacionesadl') {
                        $textofirmado = 'SE FIRMÓ ELECTRÓNICAMENTE ' .
                                'EL FORMULARIO EL ' . \funcionesSii2::mostrarFecha($fdate) . ' A LAS ' . $fhora . '. ' .
                                'HASH DE FIRMADO ' . $tramasha1;
                    } else {
                        $textofirmado = 'EL SEÑOR(A) ' . $xNombre . ' IDENTIFICADO(A) CON EL NÚMERO ' .
                                $_SESSION["tramite"]["identificacionfirmante"] . ' FIRMÓ ELECTRÓNICAMENTE ' .
                                'EL FORMULARIO EL ' . \funcionesSii2::mostrarFecha($fdate) . ' A LAS ' . $fhora . '. ' .
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
                    
                    //
                    $_SESSION ["formulario"] ["tipotramite"] = $_SESSION["tramite"]["tipotramite"];
                    
                    //
                    if (($_SESSION ["formulario"] ["datos"] ["organizacion"] == '02') || ($_SESSION ["formulario"] ["datos"] ["categoria"] == '2') || ($_SESSION ["formulario"] ["datos"] ["categoria"] == '3')) {
                        $name = armarPdfEstablecimientoNuevo1082Sii($mysqli, $_SESSION ["tramite"] ["numerorecuperacion"], $_SESSION ["tramite"] ["numeroliquidacion"], '', '', $textofirmado);
                        if (count($_SESSION ["formulario"] ["datos"] ["f"]) > 1) {
                            $name1 = armarPdfEstablecimientoNuevoAnosAnteriores1082Sii($mysqli, $_SESSION ["tramite"] ["numerorecuperacion"], $_SESSION ["tramite"] ["numeroliquidacion"], '', $textofirmado);
                            unirPdfs(array(
                                '../../../tmp/' . $name,
                                '../../../tmp/' . $name1
                                    ), '../../../tmp/' . $name
                            );
                        }
                    } else {
                        $name = armarPdfPrincipalNuevo1082Sii($mysqli, $_SESSION ["tramite"] ["numerorecuperacion"], $_SESSION ["tramite"] ["numeroliquidacion"], '', 'no', $textofirmado);
                        if (count($_SESSION ["formulario"] ["datos"] ["f"]) > 1) {
                            $name1 = armarPdfPrincipalNuevoAnosAnteriores1082Sii($mysqli, $_SESSION ["tramite"] ["numerorecuperacion"], $_SESSION ["tramite"] ["numeroliquidacion"], '', $textofirmado);
                            unirPdfs(array(
                                '../../../tmp/' . $name,
                                '../../../tmp/' . $name1
                                    ), '../../../tmp/' . $name
                            );
                        }
                        // ************************************************************************************ //
                        // 2018-10-17: JINT: SE adiciona la generación del formato 
                        // Por solicitud de la CC de Ibagué
                        // ************************************************************************************ //
                        if ($_SESSION ["formulario"] ["datos"]["afiliado"] == '1') {
                            if (defined('TIPO_DOC_FOR_AFILIACION') && TIPO_DOC_FOR_AFILIACION != '') {
                                if ($_SESSION["tramite"]["tipotramite"] == 'renovacionmatricula') {
                                    $datPrincipal = $_SESSION ["formulario"] ["datos"];
                                    $textoFirmadoDatPrincipal = $textofirmado;
                                    $tramasha1DatPrincipal = $tramasha1;
                                }
                            }
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
                    $pathx = '../../../tmp/' . $mi_pdf;

                    $listado[$iLista]["identificacion"] = $dat["identificacion"];
                    $listado[$iLista]["nombre"] = $dat["nombre"];
                    $listado[$iLista]["identificador"] = '';
                    $listado[$iLista]["pesoarchivo"] = filesize($pathx);
                    $listado[$iLista]["path"] = $pathx;

                    //
                    $dirx = date("Ymd");
                    $path = PATH_ABSOLUTO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/liquidacionmreg/' . $dirx;
                    if (!is_dir($path) || !is_readable($path)) {
                        mkdir($path, 0777);
                        \funcionesSii2::crearIndex($path);
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
                    insertarRegistrosMysqli2($mysqli, 'mreg_anexos_liquidaciones', $arrCampos, $arrValores, 'si');
                    $idanexo = $_SESSION["generales"]["lastId"];

                    // *********************************************************************** //
                    // Se traslada el pdf al directorio donde debe quedar almacenado
                    // *********************************************************************** //        
                    copy($pathx, '../../../' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/liquidacionmreg/' . $dirx . '/' . $idanexo . '.' . $listado[$iLista]["tipoarchivo"]);
                    $listado[$iLista]["path"] = 'liquidacionmreg/' . $dirx . '/' . $idanexo . '.' . $listado[$iLista]["tipoarchivo"];

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
                $name = armarPdfFormatoAfiliacionSii($mysqli, $_SESSION ["tramite"] ["numerorecuperacion"], $_SESSION ["tramite"] ["numeroliquidacion"], $textoFirmadoDatPrincipal, '');
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
                    $pathx = '../../../tmp/' . $mi_pdf;

                    $listado[$iLista]["identificacion"] = $_SESSION["formulario"]["datos"]["identificacion"];
                    $listado[$iLista]["nombre"] = $_SESSION["formulario"]["datos"]["nombre"];
                    $listado[$iLista]["identificador"] = '';
                    $listado[$iLista]["pesoarchivo"] = filesize($pathx);
                    $listado[$iLista]["path"] = $pathx;

                    //
                    $dirx = date("Ymd");
                    $path = PATH_ABSOLUTO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/liquidacionmreg/' . $dirx;
                    if (!is_dir($path) || !is_readable($path)) {
                        mkdir($path, 0777);
                        \funcionesSii2::crearIndex($path);
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
                    insertarRegistrosMysqli2($mysqli, 'mreg_anexos_liquidaciones', $arrCampos, $arrValores, 'si');
                    $idanexo = $_SESSION["generales"]["lastId"];

                    // *********************************************************************** //
                    // Se traslada el pdf al directorio donde debe quedar almacenado
                    // *********************************************************************** //        
                    copy($pathx, '../../../' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/liquidacionmreg/' . $dirx . '/' . $idanexo . '.' . $listado[$iLista]["tipoarchivo"]);
                    $listado[$iLista]["path"] = 'liquidacionmreg/' . $dirx . '/' . $idanexo . '.' . $listado[$iLista]["tipoarchivo"];
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

            //
            $ret = retornarRegistroMysqli2($mysqli, "mreg_liquidaciondatos", "idliquidacion=" . $_SESSION ["tramite"] ["numeroliquidacion"]);
            $_SESSION["formulario"]["datos"] = \funcionesSii2_desserializaciones::desserializarExpedienteProponente($mysqli, $ret["xml"]);
            if (($ret["organizacion"] != '01') && (trim($ret["matricula"]) != '')) {
                if ($_SESSION["formulario"]["tipotramite"] == 'inscripcionproponente' ||
                        $_SESSION["formulario"]["tipotramite"] == 'cambiodomicilioproponente') {
                    $ret1 = \funcionesSii2::retornarExpedienteProponenteSii($mysqli, $_SESSION["formulario"]["proponente"], $_SESSION["formulario"]["matricula"]);
                } else {
                    $ret1 = \funcionesSii2::retornarExpedienteProponenteSii($mysqli, $_SESSION["formulario"]["proponente"]);
                }
            }
            $_SESSION["formulario"]["datos"] = $ret;

            if (($ret["organizacion"] != '01') && (trim($ret["matricula"]) != '')) {
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
                    $_SESSION["tramite"]["identificacionfirmante"] .
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
                    $_SESSION["tramite"]["identificacionfirmante"] . ' FIRMO ELECTRÓNICAMENTE ' .
                    'EL FORMULARIO EL ' . mostrarFecha($fdate) . ' A LAS ' . $fhora . ' ' .
                    'HASH DE FIRMADO ' . $tramasha1;

            // *********************************************************************** //
            // Se genera el pdf con el formulario en el directorio tmp
            // *********************************************************************** //
            $name = armarPdfFormularioProponentes1082Sii($mysqli, $_SESSION["formulario"]["datos"], $_SESSION["formulario"]["tipotramite"], $_SESSION["formulario"]["liquidacion"], $_SESSION["formulario"]["numerorecuperacion"], '', '', '', 'final', $textofirmado);

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
            $listado[$iLista]["pesoarchivo"] = filesize('../../../tmp/' . $name);
            $listado[$iLista]["path"] = '../../../tmp/' . $name;

            //
            $dirx = date("Ymd");
            $path = PATH_ABSOLUTO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/liquidacionmreg/' . $dirx;
            if (!is_dir($path) || !is_readable($path)) {
                mkdir($path, 0777);
                \funcionesSii2::crearIndex($path);
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
            insertarRegistrosMysqli2($mysqli, 'mreg_anexos_liquidaciones', $arrCampos, $arrValores, 'si');
            $idanexo = $_SESSION["generales"]["lastId"];

            // *********************************************************************** //
            // Se traslada el pdf al directorio donde debe quedar almacenado
            // *********************************************************************** //        
            copy('../../../tmp/' . $name, '../../../' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/liquidacionmreg/' . $dirx . '/' . $idanexo . '.' . $listado[$iLista]["tipoarchivo"]);
            $listado[$iLista]["path"] = 'liquidacionmreg/' . $dirx . '/' . $idanexo . '.' . $listado[$iLista]["tipoarchivo"];

            // *********************************************************************** //
            // Se borra el temporal
            // *********************************************************************** //
            unlink('../../../tmp/' . $name);
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
            $_SESSION["formulario"]["datos"] = \funcionesSii2_desserializaciones::desserializarExpedienteProponente($mysqli, '');

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
            $_SESSION["formulario"]["datos"]["propcamaraorigennombre"] = retornarRegistroMysqli2($mysqli, 'bas_camaras', "id='" . $_SESSION["tramite"]["propcamaraorigen"] . "'", "descripcion");
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

            // informaci&oacute;n de ubicaci&oacute;n de notificaci&oacute;n
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
                    $_SESSION["tramite"]["identificacionfirmante"] .
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
                    $_SESSION["tramite"]["identificacionfirmante"] . ' FIRMO ELECTRÓNICAMENTE ' .
                    'EL FORMULARIO EL ' . mostrarFecha($fdate) . ' A LAS ' . $fhora . '. ' .
                    'HASH DE FIRMADO ' . $tramasha1;

            // *********************************************************************** //
            // Se genera el pdf con el formulario en el directorio tmp
            // *********************************************************************** //
            $name = armarPdfFormularioProponentes1082Sii($mysqli, $_SESSION["formulario"]["datos"], $_SESSION["formulario"]["tipotramite"], $_SESSION["formulario"]["liquidacion"], $_SESSION["formulario"]["numerorecuperacion"], '', '', '', 'final', $textofirmado);

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
            $listado[$iLista]["pesoarchivo"] = filesize('../../../tmp/' . $name);
            $listado[$iLista]["path"] = '../../../tmp/' . $name;

            //
            $dirx = date("Ymd");
            $path = PATH_ABSOLUTO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/liquidacionmreg/' . $dirx;
            if (!is_dir($path) || !is_readable($path)) {
                mkdir($path, 0777);
                \funcionesSii2::crearIndex($path);
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
            insertarRegistros('mreg_anexos_liquidaciones', $arrCampos, $arrValores, 'si');
            $idanexo = $_SESSION["generales"]["lastId"];

            // *********************************************************************** //
            // Se traslada el pdf al directorio donde debe quedar almacenado
            // *********************************************************************** //        
            copy('../../../tmp/' . $name, '../../../' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/liquidacionmreg/' . $dirx . '/' . $idanexo . '.' . $listado[$iLista]["tipoarchivo"]);
            $listado[$iLista]["path"] = 'liquidacionmreg/' . $dirx . '/' . $idanexo . '.' . $listado[$iLista]["tipoarchivo"];

            // *********************************************************************** //
            // Se borra el temporal
            // *********************************************************************** //
            unlink('../../../tmp/' . $name);
            // }
            // *********************************************************************** //
            // Se destruye en memoria el formulario
            // *********************************************************************** //
            unset($_SESSION["formulario"]);
        }


        // *************************************************************************** //
        // En caso de mutaciones de direccion
        // *************************************************************************** //
        if ($_SESSION["tramite"]["tipotramite"] == 'mutacionregmer' || $_SESSION["tramite"]["tipotramite"] == 'mutacionesadl') {

            $_SESSION["formulario"]["datos"] = array();
            $arrForms = retornarRegistrosMysqli2($mysqli, "mreg_liquidaciondatos", "idliquidacion=" . $_SESSION ["tramite"] ["numeroliquidacion"], "idliquidacion");
            // $arrForms = retornarLiquidacionDatosExpedienteArregloXml($_SESSION["tramite"]["numeroliquidacion"]);
            foreach ($arrForms as $form) {
                $_SESSION["formulario"]["datos"] = \funcionesSii2::desserializarExpedienteMatricula($mysqli,$form["xml"]);
                $_SESSION["formulario"]["datosanteriores"] = \funcionesSii2::retornarExpedienteMercantilSii($mysqli,$_SESSION["formulario"]["datos"]["matricula"], '', '', '', 'si', 'N');
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
                        $_SESSION["tramite"]["identificacionfirmante"] .
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
                        $_SESSION["tramite"]["identificacionfirmante"] . ' FIRMO ELECTRÓNICAMENTE ' .
                        'LA SOLICITUD DE MUTACION EL ' . mostrarFecha($fdate) . ' A LAS ' . $fhora . ' ' .
                        'DANDO FE DEL CONTENIDO DE LA MISMA. HASH DE FIRMADO ' . $tramasha1;


                $name = armarPdfMutacionGeneralTcpdf($mysqli,$_SESSION["tramite"]["numerorecuperacion"], $_SESSION["tramite"]["numeroliquidacion"], $textofirmado);

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

                $mi_pdf = $name;
                $pathx = $mi_pdf;

                $listado[$iLista]["identificacion"] = $_SESSION["formulario"]["datos"]["identificacion"];
                $listado[$iLista]["nombre"] = $_SESSION["formulario"]["datos"]["nombre"];
                $listado[$iLista]["identificador"] = '';
                $listado[$iLista]["pesoarchivo"] = filesize($pathx);
                $listado[$iLista]["path"] = $pathx;

                //
                $dirx = date("Ymd");
                $path = PATH_ABSOLUTO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/liquidacionmreg/' . $dirx;
                if (!is_dir($path) || !is_readable($path)) {
                    mkdir($path, 0777);
                    \funcionesSii2::crearIndex($path);
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
                insertarRegistros('mreg_anexos_liquidaciones', $arrCampos, $arrValores, 'si');
                $idanexo = $_SESSION["generales"]["lastId"];

                // *********************************************************************** //
                // Se traslada el pdf al directorio donde debe quedar almacenado
                // *********************************************************************** //        
                copy($pathx, PATH_ABSOLUTO_SITIO . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/liquidacionmreg/' . $dirx . '/' . $idanexo . '.' . $listado[$iLista]["tipoarchivo"]);
                $listado[$iLista]["path"] = 'liquidacionmreg/' . $dirx . '/' . $idanexo . '.' . $listado[$iLista]["tipoarchivo"];

                // *********************************************************************** //
                // Se borra el temporal
                // *********************************************************************** //
                unlink($pathx);

                unset($arrForms);
                unset($form);
                // }
            }
        }

        // *************************************************************************** //
        // En caso de solicitudes de cancelacion
        // *************************************************************************** //
        if ($_SESSION["tramite"]["tipotramite"] == 'solicitudcancelacionpnat' ||
                $_SESSION["tramite"]["tipotramite"] == 'solicitudcancelacionest') {
            $textofirmado = '';
            $tramasha1 = '';
            $trama = $fdate . $fhora . $fip .
                    $_SESSION["tramite"]["tipotramite"] .
                    $xNombre .
                    $_SESSION["tramite"]["identificacionfirmante"] .
                    $_SESSION["tramite"]["idmotivocancelacion"] .
                    $_SESSION["tramite"]["motivocancelacion"];

            $tramasha1 = sha1($trama);

            $textofirmado = 'EL SEÑOR(A) ' . $xNombre . ' IDENTIFICADO(A) CON EL NÚMERO ' .
                    $_SESSION["tramite"]["identificacionfirmante"] . ' FIRMO ELECTRÓNICAMENTE ' .
                    'LA SOLICITUD DE CANCELACIÓN EL ' . mostrarFecha($fdate) . ' A LAS ' . $fhora . ' ' .
                    'DANDO FE DEL CONTENIDO DE LA MISMA. HASH DE FIRMADO ' . $tramasha1;


            $name = armarPdfSolicitudCancelacion($mysqli, $textofirmado);

            $iLista++;
            $listado[$iLista]["observaciones"] = 'SOLICITUD DE CANCELACION';
            $listado[$iLista]["idtipodoc"] = $clavevalor;
            $listado[$iLista]["origendoc"] = 'EL COMERCIANTE';

            $listado[$iLista]["numdoc"] = 'N/A';
            $listado[$iLista]["fechadoc"] = date("Ymd");
            $listado[$iLista]["tipoanexo"] = '503'; // formularios y solicitudes
            $listado[$iLista]["matricula"] = $_SESSION ["formulario"] ["datos"]["matricula"];
            $listado[$iLista]["proponente"] = '';
            $listado[$iLista]["tipoarchivo"] = 'pdf';
            $listado[$iLista]["hashfirmado"] = $tramasha1;

            $mi_pdf = $name;
            $pathx = $mi_pdf;

            $listado[$iLista]["identificacion"] = $_SESSION["tramite"]["identificacion"];
            $listado[$iLista]["nombre"] = $_SESSION["tramite"]["nombre"];
            $listado[$iLista]["identificador"] = '';
            $listado[$iLista]["pesoarchivo"] = filesize($pathx);
            $listado[$iLista]["path"] = $pathx;

            //
            $dirx = date("Ymd");
            $path = PATH_ABSOLUTO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/liquidacionmreg/' . $dirx;
            if (!is_dir($path) || !is_readable($path)) {
                mkdir($path, 0777);
                \funcionesSii2::crearIndex($path);
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
            insertarRegistros('mreg_anexos_liquidaciones', $arrCampos, $arrValores, 'si');
            $idanexo = $_SESSION["generales"]["lastId"];

            // *********************************************************************** //
            // Se traslada el pdf al directorio donde debe quedar almacenado
            // *********************************************************************** //        
            copy($pathx, PATH_ABSOLUTO_SITIO . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/liquidacionmreg/' . $dirx . '/' . $idanexo . '.' . $listado[$iLista]["tipoarchivo"]);
            $listado[$iLista]["path"] = 'liquidacionmreg/' . $dirx . '/' . $idanexo . '.' . $listado[$iLista]["tipoarchivo"];

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
        $arrTem = retornarRegistros('mreg_anexos_liquidaciones', "idliquidacion=" . $_SESSION["tramite"]["idliquidacion"] . " and tipoanexo <> '503' and eliminado <> 'SI'", "idanexo");
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
            // $listado[$iLista]["identificador"] = '';
            $listado[$iLista]["pesoarchivo"] = filesize('../../../' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/' . $t["path"] . '/' . $t["idanexo"] . '.' . $t["tipoarchivo"]);
            $listado[$iLista]["path"] = '../../../' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/' . $t["path"] . '/' . $t["idanexo"] . '.' . $t["tipoarchivo"];

            $textofirmado = '';
            $tramasha1 = '';
            $textoarchivo = base64_encode(file_get_contents($listado[$iLista]["path"]));

            $trama = $fdate . $fhora . $fip .
                    $_SESSION["tramite"]["tipotramite"] .
                    $xNombre .
                    $_SESSION["tramite"]["identificacionfirmante"] .
                    $textoarchivo;

            $tramasha1 = sha1($trama);

            $listado[$iLista]["path"] = $t["path"] . '/' . $t["idanexo"] . '.' . $t["tipoarchivo"];
            // }

            $listado[$iLista]["hashfirmado"] = $tramasha1;
        }

        return $listado;
    }

    function getNumPagesInPDF($filePath) {
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
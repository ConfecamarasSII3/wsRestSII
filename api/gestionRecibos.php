<?php

class gestionRecibos {

    /**
     * 
     * @param mysqli $mysqli
     * @param type $idSolicitudPago
     * @param type $tipogasto
     * @param type $estadofinalliquidacion
     * @param type $fechareciboagenerar
     * @param type $fecharenovacionagenerar
     * @param type $cajero
     * @param type $requieresoportesasentamiento (si o no)
     * @return string
     */
    public static function asentarRecibos($mysqli, $idSolicitudPago, $tipogasto = '0', $estadofinalliquidacion = '09', $fechareciboagenerar = '', $fecharenovacionagenerar = '', $cajero = '', $requieresoportesasentamiento = '') {

        ini_set('memory_limit', '6144M');

        //
        if (!isset($_SESSION["generales"]["codigousuario"])) {
            $_SESSION["generales"]["codigousuario"] = '';
        }
        $_SESSION["generales"]["codigousuariooriginal"] = $_SESSION["generales"]["codigousuario"];
        $_SESSION["generales"]["pathabsoluto"] = PATH_ABSOLUTO_SITIO;

        //
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/genPdfsGenerales.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/genPdfsMercantil.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/genPdfsFormatoAfiliacion.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/genPdfsCertificadosProponentes.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/genPdfSellos.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistralesCalculos.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRues.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesCFE.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/presentacion.class.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/myErrorHandler.php');
        set_error_handler('myErrorHandler');

        $fecharenaplicable = $fecharenovacionagenerar;

        // **************************************************************************************** //
        // Arreglo de salida de la funcion
        // **************************************************************************************** //
        $resultado = array(
            'codigoError' => '0000',
            'msgError' => '',
            'numeroRecibo' => '',
            'numeroOperacion' => '',
            'fechaRecibo' => '',
            'horaRecibo' => '',
            'numeroReciboGob' => '',
            'numeroOperacionGob' => '',
            'fechaReciboGob' => '',
            'horaReciboGob' => '',
            'codigoBarras' => '',
            'saldoPrepago' => 0,
            'clavePrepago' => ''
        );

        // ***************************************************************************************** //
        // Log a generar
        // ***************************************************************************************** //
        $nameLog = 'asentarRecibosApiNuevo_' . date("Ymd");

        // ***************************************************************************************** //
        // Titulo a campos de mutaciones
        // ***************************************************************************************** //
        $tituloDatos = array();
        $tituloDatos["nombre"] = 'Nombre o razón social';
        $tituloDatos["dircom"] = 'Dirección comercial';
        $tituloDatos["muncom"] = 'Municipio comercial';
        $tituloDatos["paicom"] = 'Pais comercial';
        $tituloDatos["barriocom"] = 'Barrio comercial';
        $tituloDatos["codigozonacom"] = 'Zona urbana o rural comercial';
        $tituloDatos["codigopostalcom"] = 'Código postal comercial';
        $tituloDatos["telcom1"] = 'Primer teléfono comercial';
        $tituloDatos["telcom2"] = 'Segundo teléfono comercial';
        $tituloDatos["telcom3"] = 'Tercer teléfono comercial';
        $tituloDatos["emailcom"] = 'Primer email comercial';
        $tituloDatos["emailcom2"] = 'Segundo email comercial';
        $tituloDatos["emailcom3"] = 'Tercer email comercial';
        $tituloDatos["ctrubi"] = 'Ubicación';
        $tituloDatos["numpredial"] = 'Número predial';
        $tituloDatos["urlcom"] = 'Página web comercial';

        $tituloDatos["dirnot"] = 'Dirección de notificación';
        $tituloDatos["munnot"] = 'Municipio de notificación';
        $tituloDatos["painot"] = 'País de notificación';
        $tituloDatos["barrionot"] = 'Barrio de notificación';
        $tituloDatos["codigozonanot"] = 'Zona urbana o rural de notificación';
        $tituloDatos["codigopostalnot"] = 'Código postal de notificación';
        $tituloDatos["telnot"] = 'Primer teléfono de notificación';
        $tituloDatos["telnot2"] = 'Segundo teléfono de notificación';
        $tituloDatos["telnot3"] = 'Tercer teléfono de notificación';
        $tituloDatos["emailnot"] = 'Primer email de notificación';
        $tituloDatos["tiposedeadm"] = 'Tipo de sede administrativa';
        $tituloDatos["urlnot"] = 'Página web';

        $tituloDatos["ciiu1"] = 'Ciiu principal';
        $tituloDatos["ciiu2"] = 'Ciiu secundario';
        $tituloDatos["ciiu3"] = 'Tercer ciiu';
        $tituloDatos["ciiu4"] = 'Cuarto ciiu';
        $tituloDatos["desactiv"] = 'Descripción de la actividad';
        $tituloDatos["cantidadmujeres"] = 'Cantidad de mujeres';
        $tituloDatos["cantidadmujerescargosdirectivos"] = 'Cantidad de mujeres en cargos directivos';
        $tituloDatos["personal"] = 'Personal';
        $tituloDatos["ciiutamanoempresarial"] = 'Ciiu de mayores ingresos';

        $tituloDatos["feciniact1"] = 'Fecha de inicio de la actividad principal';
        $tituloDatos["feciniact2"] = 'Fecha de inicio de la actividad secundaria';

        //
        $archivable = 'si';

        // ************************************************************************************ //
        // Conexion a la BD
        // ************************************************************************************ //
        $cerrarMysqli = 'no';
        if ($mysqli == null) {
            $cerrarMysqli = 'si';
            $mysqli = conexionMysqliApi();
        }

        // ************************************************************************************ //
        // Inicia el log de la transaccion
        // ************************************************************************************ //
        \logApi::general2($nameLog, $idSolicitudPago, '############# INICIA EL REGISTRO DE UNA NUEVA LIQUIDACION  : ' . $idSolicitudPago . ' #########');
        \logApi::general2($nameLog, $idSolicitudPago, 'Usuario  logueado : ' . $_SESSION["generales"]["codigousuariooriginal"] . ' - Usuario  cajero : ' . $cajero);

        // ***************************************************************************************** //    
        // 2018-10-11: JINT
        // Determina si el recibo debe ser generado a nombre del usuario logueado o a nombre de
        // un cajero en particular pasado por parámetro
        // ***************************************************************************************** //
        $_SESSION["generales"]["codigousuario"] = $cajero;
        $_SESSION["generales"]["cajero"] = $cajero;

        if ($cajero == '') {
            $resultado["codigoError"] = '9999';
            $resultado["msgError"] = 'No fue posible localizar el cajero al cual se debe cargar el recibo a generar';
            if ($cerrarMysqli == 'si') {
                $mysqli->close();
            }
            $_SESSION["generales"]["codigousuario"] = $_SESSION["generales"]["codigousuariooriginal"];
            return $resultado;
        }

        // ***************************************************************************************** //    
        // 2018-10-11: JINT
        // Busca el cajero en la tabla de usuarios
        // ***************************************************************************************** //
        // if ($_SESSION["generales"]["codigousuario"] != 'USUPUBXX' && $_SESSION["generales"]["codigousuario"] != 'RUE') {
        if ($_SESSION["generales"]["codigousuario"] != 'USUPUBXX') {
            $usuCajero = retornarRegistroMysqliApi($mysqli, 'usuarios', "idusuario='" . $_SESSION["generales"]["cajero"] . "'");
            if ($usuCajero === false || empty($usuCajero)) {
                $resultado["codigoError"] = '9999';
                $resultado["msgError"] = 'Usuario / cajero no encontrado en el sistema';
                if ($cerrarMysqli == 'si') {
                    $mysqli->close();
                }
                $_SESSION["generales"]["codigousuario"] = $_SESSION["generales"]["codigousuariooriginal"];
                return $resultado;
            }
        } else {
            $usuCajero = false;
        }

        // ************************************************************************************ //
        // Inicializa variables del proceso
        // ************************************************************************************ //
        $idope = $_SESSION["generales"]["cajero"];

        //
        $fecharenovacion = $fecharenovacionagenerar;
        if ($fecharenovacion == '') {
            $fecharenovacion = date("Ymd");
        }

        // 2019-07-17: JINT: para actualizar la tabla nombresanteriores
        $arrServs = array();

        $reciboSII = '';
        $operacionSII = '';
        $fechaSII = '';
        $horaSII = '';
        $estadoSII = '';
        $codbarrasSII = '';

        $reciboSIIgob = '';
        $operacionSIIgob = '';
        $fechaSIIgob = '';
        $horaSIIgob = '';

        $totalcamara = 0;
        $totalcamara1 = 0;
        $totalgobernacion = 0;
        $totalgobernacion1 = 0;

        $nomant = '';
        $librocamnom = '';
        $inscripcioncamnom = '';
        $duplicamnom = '';
        $matcamnom = '';

        //
        $valorbruto = 0;
        $valorbaseiva = 0;
        $valoriva = 0;
        $valortotal = 0;
        $matricularecibo = '';
        $proponenterecibo = '';
        $usuario = '';
        $identificacionPrepago = '';
        $claveprepago = '';
        $saldoprepago = 0;
        $saldoafiliadoX = 0;
        $incluyePagoAfiliacion = '';
        $valorPagoAfiliacion = 0;
        $cupoAfiliado = 0;
        $xInsc = 0;
        $xSiNat22 = '';
        $xSiEst22 = '';
        $opeSecJur = retornarClaveValorMysqliApi($mysqli, '90.01.01');
        $tipoPagoAfiliacion = '';
        $matAfiliacion = '';
        $anoAfiliacion = '';
        $matPnat = '';
        $matPjur = '';
        $matEst = '';
        $matSuc = '';
        $matAge = '';
        $matEsadl = '';
        $pedirPnat = 'N';
        $pedirEst = 'N';
        $pedirSuc = 'N';
        $pedirAge = 'N';
        $pedirPjur = 'N';
        $pedirEsadl = 'N';
        $iForms = 0;
        $tieneformularios = 'no';
        $arrSubTipoTramite = false;
        $arregloForms = array();
        $xInscripciones = array();
        $soportes = array();
        $cbSII = '';
        $tramitevue = '';
        $vuebenley1780 = '';

        // ************************************************************************
        // Encuentra total servicios y total gobernacion
        // valida si está habilitada la separación de recibos
        // ************************************************************************
        foreach ($_SESSION["tramite"]["liquidacion"] as $l) {
            if (!isset($arrServs[$l["idservicio"]])) {
                $arrServs[$l["idservicio"]] = retornarRegistroMysqliApi($mysqli, 'mreg_servicios', "idservicio='" . $l["idservicio"] . "'");
            }
            if (ltrim((string) $arrServs[$l["idservicio"]]["conceptodepartamental"], "0") != '') {
                $totalgobernacion1 = $totalgobernacion1 + $l["valorservicio"];
            } else {
                $totalcamara1 = $totalcamara1 + $l["valorservicio"];
            }
            if ($l["idservicio"] == '01090110' || $l["idservicio"] == '01090111') {
                $vuebenley1780 = 'si';
            }
        }

        //
        if (!defined('SEPARAR_RECIBOS') || SEPARAR_RECIBOS == '' || SEPARAR_RECIBOS == 'NO') {
            $totalcamara = $_SESSION["tramite"]["valortotal"];
        } else {
            foreach ($_SESSION["tramite"]["liquidacion"] as $l) {
                if (isset($arrServs[$l["idservicio"]])) {
                    $arrServs[$l["idservicio"]] = retornarRegistroMysqliApi($mysqli, 'mreg_servicios', "idservicio='" . $l["idservicio"] . "'");
                }
                if (substr($_SESSION["tramite"]["tipotramite"], 6) != 'responsable') {
                    if (ltrim((string) $arrServs[$l["idservicio"]]["conceptodepartamental"], "0") != '') {
                        $totalgobernacion = $totalgobernacion + $l["valorservicio"];
                    } else {
                        $totalcamara = $totalcamara + $l["valorservicio"];
                    }
                } else {
                    $totalcamara = $totalcamara + $l["valorservicio"];
                }
            }
        }

        // Control automático o no de asiento de matrículas
        $asientoautomaticomatriculas = 'si';
        $asientoautomaticomutaciones = 'si';
        $asientoautomaticorenovaciones = 'si';

        //
        if ($_SESSION["generales"]["codigousuario"] == 'USUPUBXX' || $usuCajero["esbanco"] == 'SI') {
            if (defined('MATRICULAS_ASIENTO_AUTOMATICO_USUPUBXX') && MATRICULAS_ASIENTO_AUTOMATICO_USUPUBXX == 'N') {
                $asientoautomaticomatriculas = 'no';
            }
        }

        //
        if ($_SESSION["generales"]["codigousuario"] == 'USUPUBXX' || $usuCajero["esbanco"] == 'SI') {
            if (defined('MUTACIONES_ASIENTO_AUTOMATICO_USUPUBXX') && MUTACIONES_ASIENTO_AUTOMATICO_USUPUBXX == 'N') {
                $asientoautomaticomutaciones = 'no';
            }
        }

        //
        if ($_SESSION["generales"]["codigousuario"] == 'USUPUBXX' || $usuCajero["esbanco"] == 'SI') {
            if (defined('RENOVACIONES_ASIENTO_AUTOMATICO_USUPUBXX') && RENOVACIONES_ASIENTO_AUTOMATICO_USUPUBXX == 'N') {
                $asientoautomaticorenovaciones = 'no';
            }
        }

        //
        if (trim($opeSecJur) == '') {
            $resultado["codigoError"] = '9999';
            $resultado["msgError"] = 'No fue posible localizar el usurio jefe juridico';
            if ($cerrarMysqli == 'si') {
                $mysqli->close();
            }
            $_SESSION["generales"]["codigousuario"] = $_SESSION["generales"]["codigousuariooriginal"];
            return $resultado;
        }


        // ********************************************************************************** //
        // Recupera la liquidación
        // ********************************************************************************** //
        $_SESSION["tramite"] = \funcionesRegistrales::retornarMregLiquidacion($mysqli, $idSolicitudPago, 'L');
        if ($_SESSION["tramite"] === false || empty($_SESSION["tramite"])) {
            $resultado["codigoError"] = '9999';
            $resultado["msgError"] = 'Liquidación no encontrada en el sistema de registro';
            if ($cerrarMysqli == 'si') {
                $mysqli->close();
            }
            $_SESSION["generales"]["codigousuario"] = $_SESSION["generales"]["codigousuariooriginal"];
            return $resultado;
        }

        if ($_SESSION["tramite"]["subtipotramite"] != 'matriculapnatcae' &&  $_SESSION["tramite"]["subtipotramite"] != 'matriculapnatvue') {
            $vuebenley1780 = '';
        }
        
        //
        $_SESSION["tramite"]["tipogasto"] = $tipogasto;
        $_SESSION["tramite"]["cajero"] = $_SESSION["generales"]["cajero"];
        $_SESSION["tramite"]["totalcamara"] = $totalcamara;
        $_SESSION["tramite"]["totalcamara1"] = $totalcamara1;
        $_SESSION["tramite"]["totalgobernacion"] = $totalgobernacion;
        $_SESSION["tramite"]["totalgobernacion1"] = $totalgobernacion1;
        $_SESSION["tramite"]["fecharenaplicable"] = $fecharenaplicable;
        $_SESSION["tramite"]["fechareciboagenerar"] = $fechareciboagenerar;
        $_SESSION["tramite"]["fecharenovacion"] = $fecharenovacion;

        // ************************************************************************************************************** //
        // Verifica que previamente no se hubiere generado un recibo para dicha liquidacion
        // ************************************************************************************************************** //
        $arrX = retornarRegistrosMysqliApi($mysqli, 'mreg_recibosgenerados', "idliquidacion=" . $_SESSION["tramite"]["idliquidacion"]);
        if ($arrX === false || empty($arrX)) {
            $generarSII = 'si';
            // \logApi::general2($nameLog, $idSolicitudPago, \funcionesGenerales::utf8_encode('Recibo de caja NO generado previamente en SII'));
        } else {
            $generarSII = 'no';
            foreach ($arrX as $ax) {
                if (trim((string) $ax["tiporecibo"]) == '' || $ax["tiporecibo"] == 'S') {
                    $reciboSII = $ax["recibo"];
                    $operacionSII = $ax["operacion"];
                    $fechaSII = $ax["fecha"];
                    $horaSII = $ax["hora"];
                    $estadoSII = $ax["estado"];
                    $codbarrasSII = $_SESSION["tramite"]["numeroradicacion"];
                }
                if ($ax["tiporecibo"] == 'G') {
                    $reciboSIIgob = $ax["recibo"];
                    $operacionSIIgob = $ax["operacion"];
                    $fechaSIIgob = $ax["fecha"];
                    $horaSIIgob = $ax["hora"];
                    $codbarrasSII = '';
                }
            }
            // $codbarrasSII = $_SESSION["tramite"]["numeroradicacion"];

            \logApi::general2($nameLog, $idSolicitudPago, \funcionesGenerales::utf8_encode('Recibo de caja generado previamente en SII : Recibo : ' . $reciboSII . ', Operacion : ' . $operacionSII . ', Fecha y hora : ' . $fechaSII . ' ' . $horaSII . ', Codigo barras: ' . $codbarrasSII));
            if ($reciboSIIgob != '') {
                \logApi::general2($nameLog, $idSolicitudPago, \funcionesGenerales::utf8_encode('Recibo de caja (gobernacion) generado previamente en SII : Recibo : ' . $reciboSIIgob . ', Operacion : ' . $operacionSIIgob . ', Fecha y hora : ' . $fechaSIIgob . ' ' . $horaSIIgob . ', Codigo barras: ' . $codbarrasSII));
            }
            $resultado["codigoError"] = '9999';
            $resultado["msgError"] = 'Recibo de caja generado previamente en SII : Recibo : ' . $reciboSII . ', Operacion : ' . $operacionSII . ', Fecha y hora : ' . $fechaSII . ' ' . $horaSII . ', Codigo barras: ' . $codbarrasSII;
            if ($cerrarMysqli == 'si') {
                $mysqli->close();
            }
            $_SESSION["generales"]["codigousuario"] = $_SESSION["generales"]["codigousuariooriginal"];
            return $resultado;
        }

        //
        if (!isset($_SESSION["generales"]["emailusuariocontrol"]) || $_SESSION["generales"]["emailusuariocontrol"] == '') {
            $_SESSION["generales"]["emailusuariocontrol"] = $_SESSION["tramite"]["emailcontrol"];
        }

        // ********************************************************************************** //
        // Si subtipotramite = matriculapnatcae ||  matriculapjurcae
        // Valida el parámetro MATRICULAS_ASIENTO_AUTOMATICO_VUE para determinar
        //  si las matrículas VUE se asientan o no en forma automática
        // ********************************************************************************** //
        if ($_SESSION["tramite"]["subtipotramite"] == 'matriculapnatcae' ||
                $_SESSION["tramite"]["subtipotramite"] == 'matriculapnatvue' ||
                $_SESSION["tramite"]["subtipotramite"] == 'matriculaestcae' ||
                $_SESSION["tramite"]["subtipotramite"] == 'matriculaestvue') {
            $asientoautomaticomatriculas = 'no';
            if (defined('CTVCE_IMPORTACION_AUTOMATICA') && CTVCE_IMPORTACION_AUTOMATICA == 'S') {
                $asientoautomaticomatriculas = 'si';
            }
            $tramitevue = 'si';
        }

        if ($_SESSION["tramite"]["subtipotramite"] == 'matriculapjurcae' ||
                $_SESSION["tramite"]["subtipotramite"] == 'matriculapjurvue' ||
                $_SESSION["tramite"]["subtipotramite"] == 'matriculasexvue' ||
                $_SESSION["tramite"]["subtipotramite"] == 'matriculaagevue' ||
                $_SESSION["tramite"]["subtipotramite"] == 'matriculasucvue'
        ) {
            $asientoautomaticomatriculas = 'no';
            $tramitevue = 'si';
        }


        // ********************************************************************************** //
        // Valida el estado de la liquidacion
        // ********************************************************************************** //
        if ($_SESSION["generales"]["codigousuario"] == 'USUPUBXX' || $usuCajero["esbanco"] == 'SI') {
            if ($_SESSION["tramite"]["idestado"] > '06' && $_SESSION["tramite"]["idestado"] != '08' && $_SESSION["tramite"]["idestado"] != '19' && $_SESSION["tramite"]["idestado"] != '20' && $_SESSION["tramite"]["idestado"] != '21' && $_SESSION["tramite"]["idestado"] != '44') {
                $resultado["codigoError"] = '9999';
                $resultado["msgError"] = 'Liquidacion no disponible para pago (1A)';
                if ($cerrarMysqli == 'si') {
                    $mysqli->close();
                }
                $_SESSION["generales"]["codigousuario"] = $_SESSION["generales"]["codigousuariooriginal"];
                \logApi::general2($nameLog, $idSolicitudPago, 'Liquidacion no disponible para pago (1A)');
                return $resultado;
            }
        }

        if ($_SESSION["generales"]["codigousuario"] != 'USUPUBXX' && $usuCajero["esbanco"] != 'SI') {
            if ($_SESSION["tramite"]["idestado"] > '06' && $_SESSION["tramite"]["idestado"] != '19' && $_SESSION["tramite"]["idestado"] != '44' && $_SESSION["tramite"]["idestado"] != '77') {
                if ($_SESSION["tramite"]["idestado"] != '20' || ($_SESSION["tramite"]["idestado"] == '20' && $estadofinalliquidacion != '20')) {
                    if ($_SESSION["tramite"]["idestado"] != '21' || ($_SESSION["tramite"]["idestado"] == '21' && $estadofinalliquidacion != '21')) {
                        $resultado["codigoError"] = '9999';
                        $resultado["msgError"] = 'Liquidacion no disponible para pago (2A)';
                        if ($cerrarMysqli == 'si') {
                            $mysqli->close();
                        }
                        \logApi::general2($nameLog, $idSolicitudPago, 'Liquidacion no disponible para pago (2A)');
                        $_SESSION["generales"]["codigousuario"] = $_SESSION["generales"]["codigousuariooriginal"];
                        return $resultado;
                    }
                }
            }
        }

        // ************************************************************************ //
        // Localiza el usuario SII que realiza la transaccion
        // ************************************************************************ //
        if ($_SESSION["generales"]["cajero"] != 'USUPUBXX' && $_SESSION["generales"]["cajero"] != 'RUE') {
            $usuCajero = retornarRegistroMysqliApi($mysqli, 'usuarios', "idusuario='" . $_SESSION["generales"]["cajero"] . "'");
            if ($usuCajero === false || empty($usuCajero)) {
                $resultado["codigoError"] = '9999';
                $resultado["msgError"] = 'Usuario / cajero no encontrado en el sistema';
                if ($cerrarMysqli == 'si') {
                    $mysqli->close();
                }
                $_SESSION["generales"]["codigousuario"] = $_SESSION["generales"]["codigousuariooriginal"];
                return $resultado;
            }
            if ($usuCajero["fechainactivacion"] != '' && $usuCajero["fechainactivacion"] != '00000000') {
                $resultado["codigoError"] = '9999';
                $resultado["msgError"] = 'Usuario-cajero esta inactivo';
                if ($cerrarMysqli == 'si') {
                    $mysqli->close();
                }
                \logApi::general2($nameLog, $idSolicitudPago, $resultado["msgError"]);
                $_SESSION["generales"]["codigousuario"] = $_SESSION["generales"]["codigousuariooriginal"];
                return $resultado;
            }

            // ************************************************************************************************ //
            // Si el usuario no ha sido activado
            // ************************************************************************************************ //
            if ($usuCajero["fechaactivacion"] == '' || $usuCajero["fechaactivacion"] == '00000000') {
                $resultado["codigoError"] = '9999';
                $resultado["msgError"] = 'Usuario-cajero no ha sido activado';
                if ($cerrarMysqli == 'si') {
                    $mysqli->close();
                }
                \logApi::general2($nameLog, $idSolicitudPago, $resultado["msgError"]);
                $_SESSION["generales"]["codigousuario"] = $_SESSION["generales"]["codigousuariooriginal"];
                return $resultado;
            }

            // ************************************************************************************************ //
            // Si el usuario no ha sido activado
            // ************************************************************************************************ //
            if ($usuCajero["escajero"] != 'SI' && $usuCajero["esbanco"] != 'SI') {
                $resultado["codigoError"] = '9999';
                $resultado["msgError"] = 'Usuario-cajero no es tipo cajero/banco';
                if ($cerrarMysqli == 'si') {
                    $mysqli->close();
                }
                \logApi::general2($nameLog, $idSolicitudPago, $resultado["msgError"]);
                $_SESSION["generales"]["codigousuario"] = $_SESSION["generales"]["codigousuariooriginal"];
                return $resultado;
            }

            //
            if ($usuCajero["esbanco"] == 'SI') {
                $_SESSION["generales"]["escajero"] = 'NO';
                $_SESSION["generales"]["origen"] = 'bancos';
                $usuCajero["idsedeoperacion"] = $usuCajero["idsede"];
                $usuCajero["idsede"] = '97';
            } else {
                $usuCajero["idsedeoperacion"] = $usuCajero["idsede"];
                $_SESSION["generales"]["escajero"] = 'SI';
                $_SESSION["generales"]["origen"] = 'presencial';
            }
            if ($usuCajero["idsede"] == '') {
                $usuCajero["idsede"] = '01';
            }
            if ($usuCajero["idsedeoperacion"] == '') {
                $usuCajero["idsedeoperacion"] = '01';
            }
        } else {
            $_SESSION["generales"]["escajero"] = 'NO';
            $_SESSION["generales"]["origen"] = 'electronico';
            $usuCajero["escajero"] = 'NO';
            $usuCajero["esbanco"] = 'NO';
            $usuCajero["idsedeoperacion"] = '99';
            $usuCajero["idsede"] = '99';
            if ($_SESSION["generales"]["cajero"] == 'RUE') {
                $usuCajero["idsede"] = '90';
                $usuCajero["idsedeoperacion"] = '90';
                $_SESSION["generales"]["origen"] = 'presencial';
            }
        }

        // ************************************************************************************ //
        // Actualiza la tabla indicando que inició el asentamiento del trámite
        // ************************************************************************************ //    
        $arrCampos = array('iniciaasentamiento', 'finalizaasentamiento');
        $arrValores = array("'" . date("Y-m-d") . ' ' . date("H:i:s") . "'", "''");
        regrabarRegistrosMysqliApi($mysqli, 'mreg_liquidacion', $arrCampos, $arrValores, "idliquidacion='" . $idSolicitudPago . "'");

        // ************************************************************************************ //
        // Actualiza codposcom y codposnot
        // ************************************************************************************ //    
        $codpos = 'no';
        if ($_SESSION["tramite"]["codposcom"] == '' && $_SESSION["tramite"]["idmunicipio"] != '') {
            $zonas = retornarRegistrosMysqliApi($mysqli, 'bas_codigos_postales', "municipio='" . $_SESSION["tramite"]["idmunicipio"] . "'", "id");
            if ($zonas && !empty($zonas)) {
                foreach ($zonas as $z) {
                    if ($z["tipo"] == 'Urbano') {
                        if ($_SESSION["tramite"]["codposcom"] == '') {
                            $_SESSION["tramite"]["codposcom"] = $z["codigopostal"];
                            $codpos = 'si';
                        }
                    }
                }
            }
        }
        if ($_SESSION["tramite"]["codposnot"] == '' && $_SESSION["tramite"]["idmunicipionot"] != '') {
            $zonas = retornarRegistrosMysqliApi($mysqli, 'bas_codigos_postales', "municipio='" . $_SESSION["tramite"]["idmunicipionot"] . "'", "id");
            if ($zonas && !empty($zonas)) {
                foreach ($zonas as $z) {
                    if ($z["tipo"] == 'Urbano') {
                        if ($_SESSION["tramite"]["codposnot"] == '') {
                            $_SESSION["tramite"]["codposnot"] = $z["codigopostal"];
                            $codpos = 'si';
                        }
                    }
                }
            }
        }

        // ************************************************************************************ //
        // Localiza el tipo generico de Transaccion
        // ************************************************************************************ //
        $arrTipoTramite = retornarRegistroMysqliApi($mysqli, 'bas_tipotramites', "id='" . $_SESSION["tramite"]["tipotramite"] . "'");
        if ($arrTipoTramite === false || empty($arrTipoTramite) || $arrTipoTramite["gruposervicios"] == '') {
            $resultado["codigoError"] = '9999';
            $resultado["msgError"] = 'No fue posible localizar el tipo de tramite';
            if ($cerrarMysqli == 'si') {
                $mysqli->close();
            }
            \logApi::general2($nameLog, $idSolicitudPago, $resultado["msgError"]);
            $_SESSION["generales"]["codigousuario"] = $_SESSION["generales"]["codigousuariooriginal"];
            return $resultado;
        }

        // Identifica el grupo de servicios
        $grupoServicios = $arrTipoTramite["gruposervicios"];

        // Identifica tipo de registro
        $tipoRegistro = $arrTipoTramite["tiporegistro"];

        // Identifica Bandeja
        $bandejaDigitalizacion = $arrTipoTramite["bandeja"];

        // Identifica recibe matrícula
        $recibeMatricula = $arrTipoTramite["recibematricula"];

        // Identifica recibe proponente
        $recibeProponente = $arrTipoTramite["recibeproponente"];

        // Identifica es renovación
        $esRenovacion = $arrTipoTramite["esrenovacion"];

        // Identifica es mutacion
        $esMutacion = $arrTipoTramite["esmutacion"];

        // Identifica es cancelacion
        $esCancelacion = $arrTipoTramite["escancelacion"];

        // Identifica es reforma
        $esReforma = $arrTipoTramite["esreforma"];

        // Identifica es cambio de domicilio
        $esCambioDomicilio = $arrTipoTramite["escambiodomicilio"];

        // Identifica asigna matricula
        $asignaMatricula = $arrTipoTramite["asignamatricula"];

        // Estado final Datos
        $estadoFinalDatos = $arrTipoTramite["estadofinaldatos"];

        // Estado matricula
        $estadoMatricula = $arrTipoTramite["estadomatricula"];

        // Cambio de nombre
        $cambioNombre = $arrTipoTramite["cambionombre"];

        // Cambio de nombre
        $cambioActividad = $arrTipoTramite["cambioactividad"];

        // Cambio de dirección
        $cambioDireccion = $arrTipoTramite["cambiodireccion"];

        // Crea ruta
        $creaRuta = $arrTipoTramite["crearuta"];

        // Estados de la ruta predefinidos
        $estadosRutaPredefinidos = $arrTipoTramite["estadosrutapredefinidos"];

        // Revisa y en caso de rues04responsable busca el servicio y con el asigna la ruta 
        // Genera registro en forma inmediata?
        $registroInmediato = $arrTipoTramite["registroinmediato"];

        //
        if ($registroInmediato == '') {
            $registroInmediato = 'no';
            if ($_SESSION["tramite"]["tipotramite"] == 'matriculapnat' || $_SESSION["tramite"]["tipotramite"] == 'matriculaest') {
                $registroInmediato = 'si';
            }
            if ($_SESSION["tramite"]["tipotramite"] == 'mutacionregmer' || $_SESSION["tramite"]["tipotramite"] == 'mutacionesadl') {
                $registroInmediato = 'si';
            }
            if ($_SESSION["tramite"]["tipotramite"] == 'renovacionmatricula' || $_SESSION["tramite"]["tipotramite"] == 'renovacionesadl') {
                $registroInmediato = 'si';
            }
        }
        //

        if ($_SESSION["tramite"]["tipotramite"] == 'matriculapnat' ||
                $_SESSION["tramite"]["tipotramite"] == 'matriculaest' ||
                $_SESSION["tramite"]["tipotramite"] == 'matriculasuc' ||
                $_SESSION["tramite"]["tipotramite"] == 'matriculaaje' ||
                $_SESSION["tramite"]["tipotramite"] == 'matriculaesadl' ||
                $_SESSION["tramite"]["tipotramite"] == 'matriculapjur') {
            if ($registroInmediato == 'si' && ($_SESSION["generales"]["codigousuario"] == 'USUPUBXX' || $usuCajero["esbanco"] == 'SI') && $asientoautomaticomatriculas == 'no') {
                $registroInmediato = 'no';
            }
        }

        /*
         * En caso de matriculapnat si tiene registro automatico, se verifica el parámetro MATRICULAS_ASIENTO_AUTOMATICO_CAJA_LEY1780
         * para saber si se debe o no asentar en forma automática
         */
        if ($_SESSION["tramite"]["tipotramite"] == 'matriculapnat') {
            if ($registroInmediato == 'si' && $_SESSION["tramite"]["benley1780"] == 'S') {
                if (!defined('MATRICULAS_ASIENTO_AUTOMATICO_CAJA_LEY1780') || MATRICULAS_ASIENTO_AUTOMATICO_CAJA_LEY1780 == 'S') {
                    $registroInmediato = 'si';
                } else {
                    $registroInmediato = 'no';
                }
            }
        }

        if ($_SESSION["tramite"]["tipotramite"] == 'matriculapnatcae' ||
                $_SESSION["tramite"]["tipotramite"] == 'matriculapnatvue' ||
                $_SESSION["tramite"]["tipotramite"] == 'matriculaestvue' ||
                $_SESSION["tramite"]["tipotramite"] == 'matriculaestcae') {
            if (defined('CTVCE_IMPORTACION_AUTOMATICA') && CTVCE_IMPORTACION_AUTOMATICA == 'S') {                
                if ($vuebenley1780 == 'si') {
                    if (defined('CTVCE_IMPORTACION_AUTOMATICA_BENLEY1780') && CTVCE_IMPORTACION_AUTOMATICA_BENLEY1780 == 'S') {
                        $registroInmediato = 'si';
                    } else {
                        $registroInmediato = 'no';
                    }
                } else {
                    $registroInmediato = 'si';
                }
            } else {
                if ($registroInmediato == 'si' && ($_SESSION["generales"]["codigousuario"] == 'USUPUBXX' || $usuCajero["esbanco"] == 'SI') && $asientoautomaticomatriculas == 'no') {
                    $registroInmediato = 'no';
                }
            }
        }

        if ($_SESSION["tramite"]["tipotramite"] == 'matriculapjurcae' ||
                $_SESSION["tramite"]["tipotramite"] == 'matriculapjurvue') {
            if ($registroInmediato == 'si' && ($_SESSION["generales"]["codigousuario"] == 'USUPUBXX' || $usuCajero["esbanco"] == 'SI') && $asientoautomaticomatriculas == 'no') {
                $registroInmediato = 'no';
            }
        }

        // ********************************************************************************************************** //
        // jint: 2024-06-06 - Evaluación del parámetor para asentar o no en forma automática las reliquidaciones
        // ********************************************************************************************************** //
        if ($_SESSION["generales"]["codigousuario"] == 'USUPUBXX' || $usuCajero["esbanco"] == 'SI') {
            if (defined('RENOVACIONES_ASIENTO_AUTOMATICO_RELIQUIDACIONES_USUPUBXX') && RENOVACIONES_ASIENTO_AUTOMATICO_RELIQUIDACIONES_USUPUBXX == 'N') {
                if ($_SESSION["tramite"]["reliquidacion"] == 'si') {
                    foreach ($_SESSION["tramite"]["expedientes"] as $dte) {
                        if ($dte["cc"] == '' || $dte["cc"] == CODIGO_EMPRESA) {
                            if ($dte["organizacion"] == '01' || ($dte["organizacion"] > '02' && $dte["categoria"] == '1')) {
                                $asientoautomaticorenovaciones = 'no';
                            }
                        }
                    }
                }
            }
        } else {
            if (defined('RENOVACIONES_ASIENTO_AUTOMATICO_RELIQUIDACIONES_CAJERO') && RENOVACIONES_ASIENTO_AUTOMATICO_RELIQUIDACIONES_CAJERO == 'N') {
                if ($_SESSION["tramite"]["reliquidacion"] == 'si') {
                    foreach ($_SESSION["tramite"]["expedientes"] as $dte) {
                        if ($dte["cc"] == '' || $dte["cc"] == CODIGO_EMPRESA) {
                            if ($dte["organizacion"] == '01' || ($dte["organizacion"] > '02' && $dte["categoria"] == '1')) {
                                $asientoautomaticorenovaciones = 'no';
                            }
                        }
                    }
                }
            }
        }

        //
        if ($esCancelacion == 'si' && ($_SESSION["generales"]["codigousuario"] == 'USUPUBXX' || $usuCajero["esbanco"] == 'SI')) {
            $registroInmediato = 'no';
        }

        //
        if ($esMutacion == 'si' && ($_SESSION["generales"]["codigousuario"] == 'USUPUBXX' || $usuCajero["esbanco"] == 'SI')) {
            if ($registroInmediato == 'si' && $asientoautomaticomutaciones == 'no') {
                $registroInmediato = 'no';
            }
        }

        //
        if ($registroInmediato == 'si') {
            if ($_SESSION["tramite"]["tramiteautomatico"] == 'no') {
                $registroInmediato = 'no';
            }
        }

        //
        if ($registroInmediato == 'si') {
            if ($_SESSION["tramite"]["tipotramite"] == 'renovacionmatricula' || $_SESSION["tramite"]["tipotramite"] == 'renovacionesadl') {
                if ($_SESSION["generales"]["codigousuario"] == 'USUPUBXX' || $usuCajero["esbanco"] == 'SI') {
                    $sopBalances = retornarRegistrosMysqliApi($mysqli, 'mreg_anexos_liquidaciones', "idliquidacion=" . $_SESSION["tramite"]["idliquidacion"], "idanexo");
                    if ($sopBalances && !empty($sopBalances)) {
                        foreach ($sopBalances as $s) {
                            if ($s["identificador"] == 'regmer-balgen') {
                                $registroInmediato = 'no';
                            }
                        }
                    }
                }
            }
        }

        //
        if ($esRenovacion == 'si' && ($_SESSION["generales"]["codigousuario"] == 'USUPUBXX' || $usuCajero["esbanco"] == 'SI')) {
            if ($registroInmediato == 'si' && $asientoautomaticorenovaciones == 'no') {
                $registroInmediato = 'no';
            }
        }

        if ($esRenovacion == 'si' && $_SESSION["generales"]["codigousuario"] != 'USUPUBXX' && $usuCajero["esbanco"] != 'SI') {
            if ($registroInmediato == 'si' && $asientoautomaticorenovaciones == 'no') {
                $registroInmediato = 'no';
            }
        }


        // Estados de la ruta predefinidos - si el trámite se hace a través de inscripción de actos y documentos
        $estadosRutaPredefinidosInscripcionDocumentos = $arrTipoTramite["estadosrutapredefinidosinscripciondocumentos"];
        if (trim((string) $estadosRutaPredefinidosInscripcionDocumentos) == '') {
            $estadosRutaPredefinidosInscripcionDocumentos = $estadosRutaPredefinidos;
        }

        // Si es cancelación relaizada como usuario público siempre quedará en estado 01 para reparto.
        if ($esCancelacion == 'si' && ($_SESSION["generales"]["codigousuario"] == 'USUPUBXX' || $usuCajero["esbanco"] == 'SI')) {
            $estadosRutaPredefinidosInscripcionDocumentos = '01';
        }


        // ************************************************************************************ //
        // Lee el usuario jefe juridico en la tabla de usuarios
        // ************************************************************************************ //
        $arrSec = retornarRegistroMysqliApi($mysqli, 'usuarios', "idusuario='" . $opeSecJur . "'");
        if ($arrSec === false || empty($arrSec)) {
            $resultado["codigoError"] = '9999';
            $resultado["msgError"] = 'No fue posible localizar el usuario jefe juridico en la tabla de usuarios';
            if ($cerrarMysqli == 'si') {
                $mysqli->close();
            }
            \logApi::general2($nameLog, $idSolicitudPago, $resultado["msgError"]);
            $_SESSION["generales"]["codigousuario"] = $_SESSION["generales"]["codigousuariooriginal"];
            return $resultado;
        }

        // 2016-04-08: Asigna forma en que se realizó el tramite
        if ($_SESSION["tramite"]["tramitepresencial"] == '1') {
            if ($_SESSION["tramite"]["origen"] == 'bancos') {
                $_SESSION["tramite"]["tramitepresencial"] = '2';
            }
            if ($_SESSION["tramite"]["origen"] == 'electronico') {
                $_SESSION["tramite"]["tramitepresencial"] = '3';
            }
        }
        if ($_SESSION["tramite"]["tramitepresencial"] == '4') {
            if ($_SESSION["tramite"]["origen"] == 'bancos') {
                $_SESSION["tramite"]["tramitepresencial"] = '5';
            }
            if ($_SESSION["tramite"]["origen"] == 'electronico') {
                $_SESSION["tramite"]["tramitepresencial"] = '6';
            }
        }

        // ************************************************************************************ //
        // 2019-03-29: JINT: Solo evaluar datos de afiliación si el trámite es diferente de rues receptor
        // Caso presentado entre mmedio recibiéndole a honda
        // ************************************************************************************ //

        if (substr($_SESSION["tramite"]["tipotramite"], 6) != 'receptora') {
            foreach ($_SESSION["tramite"]["liquidacion"] as $l) {
                if ($l["idservicio"] == '06010001' || $l["idservicio"] == '06010002') {
                    $incluyePagoAfiliacion = 'si';
                    $valorPagoAfiliacion = $l["valorservicio"];
                    if ($l["idservicio"] == '06010001') {
                        $tipoPagoAfiliacion = '01';
                    }
                    if ($l["idservicio"] == '06010002') {
                        $tipoPagoAfiliacion = '02';
                    }
                    $matAfiliacion = $l["expediente"];
                    $anoAfiliacion = $l["ano"];
                    if (ltrim($anoAfiliacion, "0") == '') {
                        $anoAfiliacion = date("Y");
                    }
                } else {
                    if (ltrim($l["expediente"], "0") != '') {
                        $matAfiliacion = $l["expediente"];
                    }
                }
            }
        }

        if (trim($_SESSION["tramite"]["subtipotramite"]) != '') {
            \logApi::general2($nameLog, $idSolicitudPago, \funcionesGenerales::utf8_encode('Sub tipo de Tramite : ' . $_SESSION["tramite"]["subtipotramite"]));
        }

        // Localiza subtipo de trámite
        if ($_SESSION["tramite"]["subtipotramite"] != '' && $_SESSION["tramite"]["tipotramite"] == 'inscripciondocumentos') {
            $arrSubTipoTramite = retornarRegistroMysqliApi($mysqli, 'bas_tipotramites', "id='" . $_SESSION["tramite"]["subtipotramite"] . "'");

            // Identifica el grupo de servicios
            $grupoServicios = $arrSubTipoTramite["gruposervicios"];

            // Identifica tipo de registro
            $tipoRegistro = $arrSubTipoTramite["tiporegistro"];

            // Identifica Bandeja
            $bandejaDigitalizacion = $arrSubTipoTramite["bandeja"];

            // Identifica recibe matrícula
            $recibeMatricula = $arrSubTipoTramite["recibematricula"];

            // Identifica recibe proponente
            $recibeProponente = $arrSubTipoTramite["recibeproponente"];

            // Identifica es renovación
            $esRenovacion = $arrSubTipoTramite["esrenovacion"];

            // Identifica es mutacion
            $esMutacion = $arrSubTipoTramite["esmutacion"];

            // Identifica es cancelacion
            $esCancelacion = $arrSubTipoTramite["escancelacion"];

            // Identifica es reforma
            $esReforma = $arrSubTipoTramite["esreforma"];

            // Identifica es cambio de domicilio
            $esCambioDomicilio = $arrSubTipoTramite["escambiodomicilio"];

            // Identifica asigna matricula
            $asignaMatricula = $arrSubTipoTramite["asignamatricula"];

            // Estado final Datos
            $estadoFinalDatos = $arrSubTipoTramite["estadofinaldatos"];

            // Estado matricula
            $estadoMatricula = $arrSubTipoTramite["estadomatricula"];

            // Cambio de nombre
            $cambioNombre = $arrSubTipoTramite["cambionombre"];

            // Cambio de nombre
            $cambioActividad = $arrSubTipoTramite["cambioactividad"];

            // Cambio de dirección
            $cambioDireccion = $arrSubTipoTramite["cambiodireccion"];

            // Crea ruta
            $creaRuta = $arrSubTipoTramite["crearuta"];

            // Estados de la ruta predefinidos
            $estadosRutaPredefinidos = $arrSubTipoTramite["estadosrutapredefinidos"];

            // Estados de la ruta predefinidos - si el trámite se hace a través de inscripción de actos y documentos
            $estadosRutaPredefinidosInscripcionDocumentos = $arrSubTipoTramite["estadosrutapredefinidosinscripciondocumentos"];
            if (trim($estadosRutaPredefinidosInscripcionDocumentos) == '') {
                $estadosRutaPredefinidosInscripcionDocumentos = $estadosRutaPredefinidos;
            }

            // Genera registro en forma inmediata?
            $registroInmediato = $arrSubTipoTramite["registroinmediato"];

            //
            if (
                    $_SESSION["tramite"]["subtipotramite"] == 'matriculapnat' ||
                    $_SESSION["tramite"]["subtipotramite"] == 'matriculaest' ||
                    $_SESSION["tramite"]["subtipotramite"] == 'matriculasuc' ||
                    $_SESSION["tramite"]["subtipotramite"] == 'matriculaaje' ||
                    $_SESSION["tramite"]["subtipotramite"] == 'matriculaesadl' ||
                    $_SESSION["tramite"]["subtipotramite"] == 'matriculapjur'
            ) {
                if ($registroInmediato == 'si' && $asientoautomaticomatriculas == 'no') {
                    $registroInmediato = 'no';
                }
                $registroInmediato = 'no';
                $asientoautomaticomatriculas = 'no';
            }

            if ($_SESSION["tramite"]["subtipotramite"] == 'matriculapnatcae' ||
                    $_SESSION["tramite"]["subtipotramite"] == 'matriculapnatvue' ||
                    $_SESSION["tramite"]["subtipotramite"] == 'matriculaestvue' ||
                    $_SESSION["tramite"]["subtipotramite"] == 'matriculaestcae') {
                if (defined('CTVCE_IMPORTACION_AUTOMATICA') && CTVCE_IMPORTACION_AUTOMATICA == 'S') {
                    if ($vuebenley1780 == 'si') {
                        if (defined('CTVCE_IMPORTACION_AUTOMATICA_BENLEY1780') && CTVCE_IMPORTACION_AUTOMATICA_BENLEY1780 == 'S') {
                            $registroInmediato = 'si';
                            $asientoautomaticomatriculas = 'si';
                        } else {
                            $registroInmediato = 'no';
                            $asientoautomaticomatriculas = 'no';                            
                        }
                    } else {
                        $registroInmediato = 'si';
                        $asientoautomaticomatriculas = 'si';                        
                    }
                } else {
                    if ($registroInmediato == 'si' && ($_SESSION["generales"]["codigousuario"] == 'USUPUBXX' || $usuCajero["esbanco"] == 'SI') && $asientoautomaticomatriculas == 'no') {
                        $registroInmediato = 'no';
                    }
                }
            }

            if ($_SESSION["tramite"]["subtipotramite"] == 'matriculapjurcae' || $_SESSION["tramite"]["subtipotramite"] == 'matriculapjurvue') {
                if ($registroInmediato == 'si' && ($_SESSION["generales"]["codigousuario"] == 'USUPUBXX' || $usuCajero["esbanco"] == 'SI') && $asientoautomaticomatriculas == 'no') {
                    $registroInmediato = 'no';
                }
            }

            //
            if ($esMutacion == 'si' && ($_SESSION["generales"]["codigousuario"] == 'USUPUBXX' || $usuCajero["esbanco"] == 'SI')) {
                if ($registroInmediato == 'si' && $asientoautomaticomutaciones == 'no') {
                    $registroInmediato = 'no';
                }
            }

            if ($esRenovacion == 'si' && ($_SESSION["generales"]["codigousuario"] == 'USUPUBXX' || $usuCajero["esbanco"] == 'SI')) {
                if ($registroInmediato == 'si' && $asientoautomaticorenovaciones == 'no') {
                    $registroInmediato = 'no';
                }
            }

            if ($esCancelacion == 'si' && ($_SESSION["generales"]["codigousuario"] == 'USUPUBXX' || $usuCajero["esbanco"] == 'SI')) {
                $registroInmediato = 'no';
            }
        }

        //
        $txtx = 'Tipo tramite : ' . $_SESSION["tramite"]["tipotramite"] .
                ', Asiento automático matriculas USUPUXX : ' . $asientoautomaticomatriculas .
                ', Asiento automático mutaciones USUPUXX : ' . $asientoautomaticomutaciones .
                ', Asiento automático renovaciones USUPUXX : ' . $asientoautomaticorenovaciones .
                ', Registro inmediato : ' . $registroInmediato;

        \logApi::general2($nameLog, $idSolicitudPago, $txtx);

        // ************************************************************************************************************** //
        // Asigna variables al trámite en memoria
        // ************************************************************************************************************** //
        $valorbruto = $_SESSION["tramite"]["valorbruto"];
        $valorbaseiva = $_SESSION["tramite"]["valorbaseiva"];
        $valoriva = $_SESSION["tramite"]["valoriva"];
        $valortotal = $_SESSION["tramite"]["valortotal"];
        $usuario = $_SESSION["tramite"]["idusuario"];
        $identificacionPrepago = $_SESSION["tramite"]["identificacioncliente"];

        // ************************************************************************************************************** //
        // En caso de tramites RUES como RECEPTORA, dispara al RUES antes de registrar la transaccion
        // En el sistema LOCAL
        // ************************************************************************************************************** //
        $respRues = 'no';
        if ($tipoRegistro == 'RueRec') {
            switch ($_SESSION["tramite"]["tipotramite"]) {
                case "rues01receptora":
                    $respRues = \funcionesRues::consumirRR04N($_SESSION["generales"]["cajero"], $_SESSION["tramite"]);
                    break;
                case "rues02receptora":
                    $respRues = \funcionesRues::consumirMR02N($_SESSION["generales"]["cajero"], $_SESSION["tramite"]);
                    break;
                case "rues03receptora":
                    $respRues = \funcionesRues::consumirMR02N($_SESSION["generales"]["cajero"], $_SESSION["tramite"]);
                    break;
                case "rues04receptora":
                    $respRues = \funcionesRues::consumirMR02N($_SESSION["generales"]["cajero"], $_SESSION["tramite"]);
                    break;
                case "rues05receptora":
                    $respRues = \funcionesRues::consumirMR02N($_SESSION["generales"]["cajero"], $_SESSION["tramite"]);
                    break;
                case "rues06receptora":
                    $respRues = \funcionesRues::consumirMR02N($_SESSION["generales"]["cajero"], $_SESSION["tramite"]);
                    break;
                default: break;
            }
            if ($respRues === 'no') {
                $resultado["codigoError"] = '9999';
                $resultado["msgError"] = 'Tramite RUES en el SII no parametrizado';
                if ($cerrarMysqli == 'si') {
                    $mysqli->close();
                }
                $_SESSION["generales"]["codigousuario"] = $_SESSION["generales"]["codigousuariooriginal"];
                return $resultado;
            }

            if ($respRues === false) {
                $resultado["codigoError"] = '9999';
                $resultado["msgError"] = 'Error en respuesta del tramite RUES: ' . $_SESSION["generales"]["mensajeerror"];
                if ($cerrarMysqli == 'si') {
                    $mysqli->close();
                }
                $_SESSION["generales"]["codigousuario"] = $_SESSION["generales"]["codigousuariooriginal"];
                return $resultado;
            }

            if ($respRues["codigo_error"] !== "0000") {
                $resultado["codigoError"] = $respRues["codigo_error"];
                $resultado["msgError"] = 'Error en respuesta del tramite RUES: ' . $respRues["mensaje_error"];
                if ($cerrarMysqli == 'si') {
                    $mysqli->close();
                }
                $_SESSION["generales"]["codigousuario"] = $_SESSION["generales"]["codigousuariooriginal"];
                return $resultado;
            }

            $_SESSION["tramite"]["rues_numerofactura"] = $respRues["numero_factura"];
            $_SESSION["tramite"]["rues_referenciaoperacion"] = $respRues["referencia_operacion"];
            if ($respRues["total_pagado"] != 0) {
                $_SESSION["tramite"]["rues_totalpagado"] = $respRues["total_pagado"];
            }

            if (!isset($respRues["numero_unico_consulta"])) {
                $respRues["numero_unico_consulta"] = '';
            }
            if (!isset($respRues["firma_digital"])) {
                $respRues["firma_digital"] = '';
            }

            if (!isset($respRues["fecha_respuesta"])) {
                $respRues["fecha_respuesta"] = '';
            }
            if (!isset($respRues["hora_respuesta"])) {
                $respRues["hora_respuesta"] = '';
            }

            if (!isset($respRues["caracteres_por_linea"])) {
                $respRues["caracteres_por_linea"] = '';
            }


            $_SESSION["tramite"]["rues_numerounico"] = $respRues["numero_unico_consulta"];
            $_SESSION["tramite"]["rues_fecharespuesta"] = $respRues["fecha_respuesta"];
            $_SESSION["tramite"]["rues_horarespuesta"] = $respRues["hora_respuesta"];
            $_SESSION["tramite"]["rues_firmadigital"] = $respRues["firma_digital"];
            $_SESSION["tramite"]["rues_caracteres_por_linea"] = $respRues["caracteres_por_linea"];

            //2017-08-23 - WSI ACTUALIZAR EN LA LIQUIDACION EL NUMERO INTERNO
            $_SESSION["tramite"]["rues_numerointerno"] = $respRues["numero_interno"];

            $_SESSION["tramite"]["rues_textos"] = array();
            \funcionesRegistrales::grabarLiquidacionMreg($mysqli);
        }


        $matricularecibo = $_SESSION["tramite"]["liquidacion"][1]["expediente"];
        if ($recibeProponente == 'si') {
            $proponenterecibo = $_SESSION["tramite"]["liquidacion"][1]["expediente"];
        }

        // ************************************************************************************************************** //
        // Pone el estado de los datos en 2, 6 o 7 dependiendo del caso
        // ************************************************************************************************************** //
        $i = 0;
        $sinat = 'no';
        $siest = 'no';
        $sisuc = 'no';
        $siage = 'no';
        $sijur = 'no';
        $siesa = 'no';
        $nomnat = '';
        $nomest = '';
        $nomsuc = '';
        $nomage = '';
        $nomjur = '';
        $nomesa = '';

        $tieneformulariosgrabados = 'no';
        foreach ($_SESSION["tramite"]["liquidacion"] as $r) {
            $i++;
            if (trim($estadoFinalDatos) != '') {
                $_SESSION["tramite"]["liquidacion"][$i]["estadodatos"] = $estadoFinalDatos;
            }
            if ($r["expediente"] == 'NUEVANAT') {
                if ($_SESSION["tramite"]["tipotramite"] == "matriculapnat" ||
                        $_SESSION["tramite"]["subtipotramite"] == "matriculapnatcae" ||
                        $_SESSION["tramite"]["subtipotramite"] == "matriculapnatvue") {
                    $sinat = 'si';
                    $nomnat = $r["nombre"];
                }
            }
            if ($r["expediente"] == 'NUEVAEST') {
                if ($_SESSION["tramite"]["tipotramite"] == "matriculapnat" ||
                        $_SESSION["tramite"]["tipotramite"] == "matriculaest" ||
                        $_SESSION["tramite"]["subtipotramite"] == "matriculapnatcae" ||
                        $_SESSION["tramite"]["subtipotramite"] == "matriculapnatvue" ||
                        $_SESSION["tramite"]["subtipotramite"] == "matriculaestcae" ||
                        $_SESSION["tramite"]["subtipotramite"] == "matriculaestvue") {
                    $siest = 'si';
                    $nomest = $r["nombre"];
                }
            }
            if ($r["expediente"] == 'NUEVAJUR') {
                $sijur = 'si';
                $nomjur = $r["nombre"];
            }
            if ($r["expediente"] == 'NUEVASUC') {
                $sisuc = 'si';
                $nomsuc = $r["nombre"];
            }
            if ($r["expediente"] == 'NUEVAAGE') {
                $siage = 'si';
                $nomage = $r["nombre"];
            }
            if ($r["expediente"] == 'NUEVAESA') {
                $siesa = 'si';
                $nomesa = $r["nombre"];
            }
        }

        // Si el tipo de trámite general es actos 
        // Asigna matrícula solo si el tipo de trámite indica que se registra en forma inmediata
        if ($asignaMatricula == 'si' && $registroInmediato == 'si') {
            // 2017-12-16: JINT
            // Se adiciona control para ley 1780, código de policia alto impacto y codigo de policía multas
            if (
                    $_SESSION["tramite"]["multadoponal"] != 'S' &&
                    $_SESSION["tramite"]["multadoponal"] != 'L' &&
                    $_SESSION["tramite"]["controlactividadaltoimpacto"] != 'S'
            ) {


                $xmlDatos = retornarRegistrosMysqliApi($mysqli, 'mreg_liquidaciondatos', "idliquidacion=" . $_SESSION["tramite"]["idliquidacion"], "expediente desc");
                foreach ($xmlDatos as $xml) {

                    $datos = \funcionesGenerales::desserializarExpedienteMatricula($mysqli, $xml["xml"]);
                    if ($datos["matricula"] == 'NUEVANAT') {
                        $matPnat = 'NUEVANAT';
                        $tieneformulariosgrabados = 'si';
                        $nomnat = $datos["nombre"];
                    }
                    if ($datos["matricula"] == 'NUEVAEST') {
                        $matEst = 'NUEVAEST';
                        $tieneformulariosgrabados = 'si';
                        $nomest = $datos["nombre"];
                    }
                    if ($datos["matricula"] == 'NUEVASUC') {
                        $matSuc = 'NUEVASUC';
                        $tieneformulariosgrabados = 'si';
                        $nomsuc = $datos["nombre"];
                    }
                    if ($datos["matricula"] == 'NUEVAAGE') {
                        $matAge = 'NUEVAAGE';
                        $tieneformulariosgrabados = 'si';
                        $nomage = $datos["nombre"];
                    }
                    if ($datos["matricula"] == 'NUEVAESA') {
                        $matEsadl = 'NUEVAESA';
                        $tieneformulariosgrabados = 'si';
                        $nomesa = $datos["nombre"];
                    }
                    if ($datos["matricula"] == 'NUEVAJUR') {
                        $matPjur = 'NUEVAJUR';
                        if ($datos["organizacion"] == '10') {
                            $matPjur = 'NUEVACIV';
                        }
                        $tieneformulariosgrabados = 'si';
                        $nomjur = $datos["nombre"];
                    }
                }

                if ($tieneformulariosgrabados == 'si') {
                    if ($matPnat == 'NUEVANAT') {
                        $pedirPnat = 'S';
                    }
                    if ($matEst == 'NUEVAEST') {
                        $pedirEst = 'S';
                    }
                    if ($matSuc == 'NUEVASUC') {
                        $pedirSuc = 'S';
                    }
                    if ($matAge == 'NUEVAAGE') {
                        $pedirAge = 'S';
                    }
                    if ($matPjur == 'NUEVAJUR') {
                        $pedirPjur = 'S';
                    }
                    if ($matPjur == 'NUEVACIV') {
                        $pedirPjur = 'C';
                    }
                    if ($matEsadl == 'NUEVAESA') {
                        $pedirEsadl = 'S';
                    }

                    if (
                            $pedirPnat == 'N' &&
                            $pedirEst == 'N' &&
                            $pedirSuc == 'N' &&
                            $pedirAge == 'N' &&
                            $pedirPjur == 'N' &&
                            $pedirEsadl == 'N'
                    ) {
                        foreach ($_SESSION["tramite"]["liquidacion"] as $l) {
                            if ($l["expediente"] == 'NUEVANAT') {
                                $pedirPnat = 'S';
                                $nomnat = $l["nombre"];
                            }
                            if ($l["expediente"] == 'NUEVAEST') {
                                $pedirEst = 'S';
                                $nomest = $l["nombre"];
                            }
                            if ($l["expediente"] == 'NUEVASUC') {
                                $pedirSuc = 'S';
                                $nomsuc = $l["nombre"];
                            }
                            if ($l["expediente"] == 'NUEVAAGE') {
                                $pedirAge = 'S';
                            }
                            if ($l["expediente"] == 'NUEVAJUR') {
                                $pedirPjur = 'S';
                                $nomjur = $l["nombre"];
                            }
                            if ($l["expediente"] == 'NUEVACIV') {
                                $pedirPjur = 'C';
                                $nomjur = $l["nombre"];
                            }
                            if ($l["expediente"] == 'NUEVAESA') {
                                $pedirEsadl = 'S';
                                $nomesa = $l["nombre"];
                            }
                        }
                    }

                    // Busca los numeros de matricula en SII            
                    // \logApi::general2($nameLog, $idSolicitudPago, \funcionesGenerales::utf8_encode('Entra a procesar numeros de matricula en SII [' . $pedirPnat . '][' . $pedirEst . '][' . $pedirPjur . '][' . $pedirEsadl . '][' . $pedirSuc . '][' . $pedirAge . ']'));
                    if ($pedirPnat == 'S') {
                        $matPnat = \funcionesRegistrales::generarSecuenciaMatricula($mysqli, 'MATREGMER', '01', '0', $nomnat, date("Ymd"), date("Ymd"), date("Y"), 'MA');
                    }
                    if ($pedirEst == 'S') {
                        $matEst = \funcionesRegistrales::generarSecuenciaMatricula($mysqli, 'MATREGMER', '02', '0', $nomest, date("Ymd"), date("Ymd"), date("Y"), 'MA');
                    }
                    if ($pedirSuc == 'S') {
                        $matSuc = \funcionesRegistrales::generarSecuenciaMatricula($mysqli, 'MATREGMER', '99', '2', $nomsuc, date("Ymd"), date("Ymd"), date("Y"), 'MA');
                    }
                    if ($pedirAge == 'S') {
                        $matAge = \funcionesRegistrales::generarSecuenciaMatricula($mysqli, 'MATREGMER', '99', '3', $nomage, date("Ymd"), date("Ymd"), date("Y"), 'MA');
                    }
                    if ($pedirPjur == 'S') {
                        $matPjur = \funcionesRegistrales::generarSecuenciaMatricula($mysqli, 'MATREGMER', '99', '1', $nomjur, date("Ymd"), date("Ymd"), date("Y"), 'MA');
                    }
                    if ($pedirPjur == 'C') {
                        $matPjur = \funcionesRegistrales::generarSecuenciaMatricula($mysqli, 'MATCIVIL', '99', '1', $nomjur, date("Ymd"), date("Ymd"), date("Y"), 'MA');
                    }
                    if ($pedirEsadl == 'S') {
                        $matEsadl = \funcionesRegistrales::generarSecuenciaMatricula($mysqli, 'MATESADL', '12', '1', $nomesa, date("Ymd"), date("Ymd"), date("Y"), 'IA');
                    }
                    $txtx = 'Genero mat PNAT : ' . $matPnat .
                            ', EST : ' . $matEst .
                            ', SUC : ' . $matSuc .
                            ', AGE : ' . $matAge .
                            ', PJUR : ' . $matPjur .
                            ', ESADL : ' . $matEsadl;
                    \logApi::general2($nameLog, $idSolicitudPago, $txtx);
                } else {
                    if ($sinat == 'si') {
                        $pedirPnat = 'S';
                        $matPnat = 'NUEVANAT';
                    }
                    if ($siest == 'si') {
                        $pedirEst = 'S';
                        $matEst = 'NUEVAEST';
                    }
                    if ($sisuc == 'si') {
                        $pedirSuc = 'S';
                        $matSuc = 'NUEVASUC';
                    }
                    if ($siage == 'si') {
                        $pedirAge = 'S';
                        $matAge = 'NUEVAAGE';
                    }
                    if ($sijur == 'si') {
                        $pedirPjur = 'S';
                        $matPjur = 'NUEVAJUR';
                    }
                    if ($siesa == 'si') {
                        $pedirEsadl = 'S';
                        $matEsadl = 'NUEVAESA';
                    }

                    if ($pedirPnat == 'S') {
                        $matPnat = \funcionesRegistrales::generarSecuenciaMatricula($mysqli, 'MATREGMER', '01', '0', $nomnat, date("Ymd"), date("Ymd"), date("Y"), 'MA');
                    }
                    if ($pedirEst == 'S') {
                        $matEst = \funcionesRegistrales::generarSecuenciaMatricula($mysqli, 'MATREGMER', '02', '0', $nomest, date("Ymd"), date("Ymd"), date("Y"), 'MA');
                    }
                    if ($pedirSuc == 'S') {
                        $matSuc = \funcionesRegistrales::generarSecuenciaMatricula($mysqli, 'MATREGMER', '99', '2', $nomsuc, date("Ymd"), date("Ymd"), date("Y"), 'MA');
                    }
                    if ($pedirAge == 'S') {
                        $matAge = \funcionesRegistrales::generarSecuenciaMatricula($mysqli, 'MATREGMER', '99', '3', $nomage, date("Ymd"), date("Ymd"), date("Y"), 'MA');
                    }
                    if ($pedirPjur == 'S') {
                        $matPjur = \funcionesRegistrales::generarSecuenciaMatricula($mysqli, 'MATREGMER', '99', '1', $nomjur, date("Ymd"), date("Ymd"), date("Y"), 'MA');
                    }
                    if ($pedirPjur == 'C') {
                        $matPjur = \funcionesRegistrales::generarSecuenciaMatricula($mysqli, 'MATCIVIL', '99', '1', $nomjur, date("Ymd"), date("Ymd"), date("Y"), 'MA');
                    }
                    if ($pedirEsadl == 'S') {
                        $matEsadl = \funcionesRegistrales::generarSecuenciaMatricula($mysqli, 'MATESADL', '12', '1', $nomesa, date("Ymd"), date("Ymd"), date("Y"), 'IA');
                    }
                    $txtx = 'Genero mat PNAT : ' . $matPnat .
                            ', EST : ' . $matEst .
                            ', SUC : ' . $matSuc .
                            ', AGE : ' . $matAge .
                            ', PJUR : ' . $matPjur .
                            ', ESADL : ' . $matEsadl;
                    \logApi::general2($nameLog, $idSolicitudPago, $txtx);
                }
            }
        }

        // ************************************************************************************************************** //
        // Inicializa las variables para los procesos posteriores de inscripcion en libros y ruta
        // ************************************************************************************************************** //
        $tideExpediente = '';
        $ideExpediente = '';
        $camExpediente = '';
        $matExpediente = '';
        $proExpediente = '';
        $orgExpediente = '';
        $catExpediente = '';
        $razExpediente = '';
        $clasGenExpediente = '';
        $clasEspExpediente = '';
        $clasEconExpediente = '';

        $tidePropietario = '';
        $idePropietario = '';
        $camPropietario = '';
        $matPropietario = '';
        $orgPropietario = '';
        $catPropietario = '';
        $razPropietario = '';
        $clasGenPropietario = '';
        $clasEspPropietario = '';
        $clasEconPropietario = '';

        $tideEstablecimiento = '';
        $ideEstablecimiento = '';
        $camEstablecimiento = '';
        $matEstablecimiento = '';
        $orgEstablecimiento = '';
        $catEstablecimiento = '';
        $razEstablecimiento = '';

        $xSiNat22 = '';
        $xSiEst22 = '';

        // ************************************************************************************************************** //
        // Asigna los datos del expediente afectado como datos basicos para el codigo de barras
        // ************************************************************************************************************** //
        if ($tipoRegistro == 'RegMer' || $tipoRegistro == 'RegEsadl') {
            if (ltrim($_SESSION["tramite"]["idexpedientebase"], "0") != '') {
                $matExpediente = $_SESSION["tramite"]["idexpedientebase"];
            } else {
                $matExpediente = $_SESSION["tramite"]["idmatriculabase"];
            }
            $proExpediente = '';
        }
        if ($tipoRegistro == 'RegPro') {
            if (ltrim($_SESSION["tramite"]["idexpedientebase"], "0") != '') {
                $proExpediente = $_SESSION["tramite"]["idexpedientebase"];
            } else {
                $proExpediente = $_SESSION["tramite"]["idproponentebase"];
            }
            $matExpediente = '';
        }
        $razExpediente = $_SESSION["tramite"]["nombrebase"];
        $tideExpediente = $_SESSION["tramite"]["tipoidentificacionbase"];
        $ideExpediente = $_SESSION["tramite"]["identificacionbase"];

        // ************************************************************************************************************** //
        // Actualiza en los XML los numeros de matricula recuperados del SII y otros datos como version CIIU
        // Asigna las variables generales para inscripciones y ruta
        // ************************************************************************************************************** //
        // \logApi::general2($nameLog, $idSolicitudPago, \funcionesGenerales::utf8_encode('Grupo de Servicios : ' . $grupoServicios));

        $arregloForms = array();
        $iForms = 0;
        $tieneformularios = 'no';
        if ($grupoServicios == 'RegPro') {
            $tideExpediente = $_SESSION["tramite"]["idtipoidentificacioncliente"];
            $ideExpediente = $_SESSION["tramite"]["identificacioncliente"];
            $camExpediente = $_SESSION["generales"]["codigoempresa"];
            $proExpediente = $_SESSION["tramite"]["liquidacion"][1]["expediente"];
            $razExpediente = trim($_SESSION["tramite"]["nombrecliente"] . ' ' . $_SESSION["tramite"]["apellidocliente"]);
        }

        // ******************************************************************************************** //
        // 2017-12-16: JINT
        // Se adiciona control para ley 1780, código de policia alto impacto y codigo de policía multas
        // Matrículas, mutaciones, cancelaciones, cambios de nombre
        // ******************************************************************************************** //
        if ($_SESSION["tramite"]["tipotramite"] != 'renovacionmatricula' && $_SESSION["tramite"]["tipotramite"] != 'renovacionesadl') {
            if (
                    $_SESSION["tramite"]["multadoponal"] != 'S' &&
                    $_SESSION["tramite"]["multadoponal"] != 'L' &&
                    $_SESSION["tramite"]["controlactividadaltoimpacto"] != 'S' && ($_SESSION["tramite"]["cumplorequisitosbenley1780"] != 'S' ||
                    $_SESSION["tramite"]["mantengorequisitosbenley1780"] != 'S' ||
                    $_SESSION["tramite"]["renunciobeneficiosley1780"] != 'N')
            ) {

                if ($grupoServicios == 'RegMer' || $grupoServicios == 'RegEsadl') {
                    $txterrores = '';
                    $xmlDatos = retornarRegistrosMysqliApi($mysqli, 'mreg_liquidaciondatos', "idliquidacion=" . $_SESSION["tramite"]["idliquidacion"], "expediente");
                    if ($xmlDatos && !empty($xmlDatos)) {
                        foreach ($xmlDatos as $xml) {

                            // Si el tipo de trámite general es actos y documentos, no crea la matrícula automáticamente
                            if ($asignaMatricula == 'si' && $registroInmediato == 'si') {
                                $xml["xml"] = str_replace("NUEVANAT", $matPnat, $xml["xml"]);
                                $xml["xml"] = str_replace("NUEVAEST", $matEst, $xml["xml"]);
                                $xml["xml"] = str_replace("NUEVASUC", $matSuc, $xml["xml"]);
                                $xml["xml"] = str_replace("NUEVAAGE", $matAge, $xml["xml"]);
                                $xml["xml"] = str_replace("NUEVAJUR", $matPjur, $xml["xml"]);
                                $xml["xml"] = str_replace("NUEVAESA", $matEsadl, $xml["xml"]);
                            }

                            $datos = \funcionesGenerales::desserializarExpedienteMatricula($mysqli, $xml["xml"]);

                            if ($cambioNombre == 'si') {
                                $_SESSION["tramite"]["nombreanterior"] = $datos["nombre"];
                                $_SESSION["tramite"]["nombrenuevo"] = $datos["nuevonombre"];
                                $_SESSION["tramite"]["nuevonombre"] = $datos["nuevonombre"];
                            }

                            if ($asignaMatricula == 'si') {
                                if (($datos["organizacion"] == '01' || $datos["organizacion"] > '02') && ($datos["categoria"] == '0' || $datos["categoria"] == '1')) {
                                    $tidePropietario = $datos["tipoidentificacion"];
                                    $idePropietario = $datos["identificacion"];
                                    $camPropietario = $_SESSION["generales"]["codigoempresa"];
                                    $matPropietario = $datos["matricula"];
                                    $orgPropietario = $datos["organizacion"];
                                    $catPropietario = $datos["categoria"];
                                    $razPropietario = $datos["nombre"];
                                    $clasGenPropietario = $datos["clasegenesadl"];
                                    $clasEspPropietario = $datos["claseespesadl"];
                                    $clasEconPropietario = $datos["claseeconsoli"];
                                    $tideExpediente = $datos["tipoidentificacion"];
                                    $ideExpediente = $datos["identificacion"];
                                    $camExpediente = $_SESSION["generales"]["codigoempresa"];
                                    $matExpediente = $datos["matricula"];
                                    $orgExpediente = $datos["organizacion"];
                                    $catExpediente = $datos["categoria"];
                                    $razExpediente = $datos["nombre"];
                                    $clasGenExpediente = $datos["clasegenesadl"];
                                    $clasEspExpediente = $datos["claseespesadl"];
                                    $clasEconExpediente = $datos["claseeconsoli"];
                                } else {
                                    if ($datos["organizacion"] == '02') {
                                        $tideEstablecimiento = $datos["propietarios"][1]["idtipoidentificacionpropietario"];
                                        $ideEstablecimiento = $datos["propietarios"][1]["identificacionpropietario"];
                                        $camEstablecimiento = $_SESSION["generales"]["codigoempresa"];
                                        $matEstablecimiento = $datos["matricula"];
                                        $orgEstablecimiento = $datos["organizacion"];
                                        $catEstablecimiento = $datos["categoria"];
                                        $razEstablecimiento = $datos["nombre"];
                                        if ($matPnat == '' && $matPjur == '') {
                                            $tidePropietario = $datos["propietarios"][1]["idtipoidentificacionpropietario"];
                                            $idePropietario = $datos["propietarios"][1]["identificacionpropietario"];
                                            $camPropietario = $datos["propietarios"][1]["camarapropietario"];
                                            $matPropietario = $datos["propietarios"][1]["matriculapropietario"];
                                            $orgPropietario = $datos["propietarios"][1]["organizacionpropietario"];
                                            $catPropietario = '0';
                                            $razPropietario = $datos["propietarios"][1]["nombrepropietario"];
                                            $tideExpediente = $datos["propietarios"][1]["idtipoidentificacionpropietario"];
                                            $ideExpediente = $datos["propietarios"][1]["identificacionpropietario"];
                                            $camExpediente = $datos["propietarios"][1]["camarapropietario"];
                                            $matExpediente = $datos["propietarios"][1]["matriculapropietario"];
                                            $orgExpediente = $datos["propietarios"][1]["organizacionpropietario"];
                                            $catExpediente = '0';
                                            $razExpediente = $datos["propietarios"][1]["nombrepropietario"];
                                        }
                                    }

                                    if ($datos["organizacion"] > '02') {
                                        $tideEstablecimiento = '2';
                                        $ideEstablecimiento = $datos["cpnumnit"];
                                        $camEstablecimiento = $_SESSION["generales"]["codigoempresa"];
                                        $matEstablecimiento = $datos["matricula"];
                                        $orgEstablecimiento = $datos["organizacion"];
                                        $catEstablecimiento = $datos["categoria"];
                                        $razEstablecimiento = $datos["nombre"];
                                        if ($matPnat == '' && $matPjur == '') {
                                            $tidePropietario = '2';
                                            $idePropietario = $datos["cpnumnit"];
                                            $camPropietario = $datos["cpcodcam"];
                                            $matPropietario = $datos["cpnummat"];
                                            $orgPropietario = $datos["organizacion"];
                                            $catPropietario = '1';
                                            $razPropietario = $datos["cprazsoc"];
                                            $tideExpediente = '2';
                                            $ideExpediente = $datos["cpnumnit"];
                                            $camExpediente = $datos["cpcodcam"];
                                            $matExpediente = $datos["cpnummat"];
                                            $orgExpediente = $datos["organizacion"];
                                            $catExpediente = '1';
                                            $razExpediente = $datos["cprazsoc"];
                                        }
                                    }
                                }
                            }


                            // ********************************************************************************************************** //
                            // En caso de que el tramite sea mutacion
                            // ********************************************************************************************************** //
                            if ($esMutacion == 'si') {
                                $tideExpediente = $datos["tipoidentificacion"];
                                $ideExpediente = $datos["identificacion"];
                                $camExpediente = $_SESSION["generales"]["codigoempresa"];
                                $matExpediente = $datos["matricula"];
                                $orgExpediente = $datos["organizacion"];
                                $catExpediente = $datos["categoria"];
                                $razExpediente = $datos["nombre"];
                                $clasGenExpediente = $datos["clasegenesadl"];
                                $clasEspExpediente = $datos["claseespesadl"];
                                $clasEconExpediente = $datos["claseeconsoli"];
                            }

                            // ********************************************************************************************************** //
                            // En caso de que el tramite sea solicitud de cancelacion
                            // ********************************************************************************************************** //
                            if ($esCancelacion == 'si') {
                                $tideExpediente = $datos["tipoidentificacion"];
                                $ideExpediente = $datos["identificacion"];
                                $camExpediente = $_SESSION["generales"]["codigoempresa"];
                                $matExpediente = $datos["matricula"];
                                $orgExpediente = $datos["organizacion"];
                                $catExpediente = $datos["categoria"];
                                $razExpediente = $datos["nombre"];
                                $clasGenExpediente = $datos["clasegenesadl"];
                                $clasEspExpediente = $datos["claseespesadl"];
                                $clasEconExpediente = $datos["claseeconsoli"];
                            }

                            // ********************************************************************************************************** //
                            // Ordena los fomularios, primero la persona natural o juridica y luego los establecimientos de comercio
                            // ********************************************************************************************************** //
                            if (($datos["organizacion"] == '01' || $datos["organizacion"] > '02') && ($datos["categoria"] == '' || $datos["categoria"] == '0' || $datos["categoria"] == '1')) {
                                $arregloForms[0]["datos"] = $datos;
                                $arregloForms[0]["xml"] = $xml["xml"];
                                $tieneformularios = 'si';
                            } else {
                                $iForms++;
                                $arregloForms[$iForms]["datos"] = $datos;
                                $arregloForms[$iForms]["xml"] = $xml["xml"];
                                $tieneformularios = 'si';
                            }

                            // ************************************************************************************************* //
                            // Determina si el formulario tienes CIIUS que afecten al libro XXII por ser apuestas y juegos de azar
                            // De acuerdo con el CIIU
                            // ************************************************************************************************* //
                            if ($datos["ciius"][1] == 'R9200' || $datos["ciius"][2] == 'R9200' ||
                                    $datos["ciius"][3] == 'R9200' || $datos["ciius"][4] == 'R9200') {
                                if (
                                        $datos["organizacion"] == '01' || ($datos["organizacion"] > '02' && $datos["categoria"] == '1')
                                ) {
                                    $xSiNat22 = 'S';
                                }
                            }
                        }
                    }

                    // ************************************************************************************************* //
                    // Actualiza mreg_liquidacion
                    // Con los datos de la matrícula
                    // Siempre y cuando no sea inscripciondocumentos
                    // Si es actos y documentos el tramite debe estar marcado como de registro inmediato
                    // ************************************************************************************************* //
                    if ($asignaMatricula == 'si' && $registroInmediato == 'si') {
                        $arrCampos = array(
                            'idexpedientebase',
                            'tipoidentificacionbase',
                            'identificacionbase'
                        );
                        if ($matPnat != '') {
                            $arrValores = array(
                                "'" . ltrim($matPnat, "0") . "'",
                                "'" . $tidePropietario . "'",
                                "'" . $idePropietario . "'"
                            );
                        } else {
                            if ($matPjur != '') {
                                $arrValores = array(
                                    "'" . ltrim($matPjur, "0") . "'",
                                    "'" . $tidePropietario . "'",
                                    "'" . $idePropietario . "'"
                                );
                            } else {
                                if ($matEsadl != '') {
                                    $arrValores = array(
                                        "'" . ltrim($matEsadl, "0") . "'",
                                        "'" . $tidePropietario . "'",
                                        "'" . $idePropietario . "'"
                                    );
                                } else {
                                    if ($matEst != '') {
                                        $arrValores = array(
                                            "'" . ltrim($matEst, "0") . "'",
                                            "'" . $tidePropietario . "'",
                                            "'" . $idePropietario . "'"
                                        );
                                    } else {
                                        if ($matAge != '') {
                                            $arrValores = array(
                                                "'" . ltrim($matAge, "0") . "'",
                                                "'" . $tidePropietario . "'",
                                                "'" . $idePropietario . "'"
                                            );
                                        } else {
                                            if ($matSuc != '') {
                                                $arrValores = array(
                                                    "'" . ltrim($matSuc, "0") . "'",
                                                    "'" . $tidePropietario . "'",
                                                    "'" . $idePropietario . "'"
                                                );
                                            }
                                        }
                                    }
                                }
                            }
                        }
                        regrabarRegistrosMysqliApi($mysqli, 'mreg_liquidacion', $arrCampos, $arrValores, "idliquidacion='" . $_SESSION["tramite"]["idliquidacion"] . "'");
                    }

                    // ************************************************************************************************* //
                    // Actualiza mreg_liquidaciondetalle
                    // Cuando se trata de tramites de matricula y reemplaza NUEVANAT Y NUEVAEST
                    // ************************************************************************************************* //
                    if ($asignaMatricula == 'si' && $registroInmediato == 'si') {
                        $arrCampos = array(
                            "expediente"
                        );
                        $arrTem = retornarRegistrosMysqliApi($mysqli, 'mreg_liquidaciondetalle', "idliquidacion=" . $_SESSION["tramite"]["idliquidacion"], "secuencia");
                        foreach ($arrTem as $t) {
                            if ($t["expediente"] == 'NUEVANAT') {
                                $arrValores = array(
                                    "'" . ltrim($matPnat, "0") . "'"
                                );
                                regrabarRegistrosMysqliApi($mysqli, 'mreg_liquidaciondetalle', $arrCampos, $arrValores, "idliquidacion=" . $t["idliquidacion"] . " and secuencia=" . $t["secuencia"]);
                            }
                            if ($t["expediente"] == 'NUEVAJUR') {
                                $arrValores = array(
                                    "'" . ltrim($matPjur, "0") . "'"
                                );
                                regrabarRegistrosMysqliApi($mysqli, 'mreg_liquidaciondetalle', $arrCampos, $arrValores, "idliquidacion=" . $t["idliquidacion"] . " and secuencia=" . $t["secuencia"]);
                            }
                            if ($t["expediente"] == 'NUEVAEST') {
                                $arrValores = array(
                                    "'" . ltrim($matEst, "0") . "'"
                                );
                                regrabarRegistrosMysqliApi($mysqli, 'mreg_liquidaciondetalle', $arrCampos, $arrValores, "idliquidacion=" . $t["idliquidacion"] . " and secuencia=" . $t["secuencia"]);
                            }
                            if ($t["expediente"] == 'NUEVASUC') {
                                $arrValores = array(
                                    "'" . ltrim($matSuc, "0") . "'"
                                );
                                regrabarRegistrosMysqliApi($mysqli, 'mreg_liquidaciondetalle', $arrCampos, $arrValores, "idliquidacion=" . $t["idliquidacion"] . " and secuencia=" . $t["secuencia"]);
                            }
                            if ($t["expediente"] == 'NUEVAAGE') {
                                $arrValores = array(
                                    "'" . ltrim($matAge, "0") . "'"
                                );
                                regrabarRegistrosMysqliApi($mysqli, 'mreg_liquidaciondetalle', $arrCampos, $arrValores, "idliquidacion=" . $t["idliquidacion"] . " and secuencia=" . $t["secuencia"]);
                            }
                            if ($t["expediente"] == 'NUEVAESA') {
                                $arrValores = array(
                                    "'" . ltrim($matEsadl, "0") . "'"
                                );
                                regrabarRegistrosMysqliApi($mysqli, 'mreg_liquidaciondetalle', $arrCampos, $arrValores, "idliquidacion=" . $t["idliquidacion"] . " and secuencia=" . $t["secuencia"]);
                            }
                        }
                    }

                    // ************************************************************************************************* //
                    // Actualiza mreg_liquidacionexpedientes
                    // Cuando se trata de tramites de matricula y reemplaza NUEVANAT Y NUEVAEST
                    // ************************************************************************************************* //
                    if ($asignaMatricula == 'si' && $registroInmediato == 'si') {
                        $arrCampos = array(
                            "matricula"
                        );
                        $arrTem = retornarRegistrosMysqliApi($mysqli, 'mreg_liquidacionexpedientes', "idliquidacion=" . $_SESSION["tramite"]["idliquidacion"], "secuencia");
                        foreach ($arrTem as $t) {
                            if ($t["matricula"] == 'NUEVANAT') {
                                $arrValores = array(
                                    "'" . ltrim($matPnat, "0") . "'"
                                );
                                regrabarRegistrosMysqliApi($mysqli, 'mreg_liquidacionexpedientes', $arrCampos, $arrValores, "idliquidacion=" . $t["idliquidacion"] . " and secuencia='" . $t["secuencia"] . "'");
                            }
                            if ($t["matricula"] == 'NUEVAJUR') {
                                $arrValores = array(
                                    "'" . ltrim($matPjur, "0") . "'"
                                );
                                regrabarRegistrosMysqliApi($mysqli, 'mreg_liquidacionexpedientes', $arrCampos, $arrValores, "idliquidacion=" . $t["idliquidacion"] . " and secuencia='" . $t["secuencia"] . "'");
                            }
                            if ($t["matricula"] == 'NUEVAEST') {
                                $arrValores = array(
                                    "'" . ltrim($matEst, "0") . "'"
                                );
                                regrabarRegistrosMysqliApi($mysqli, 'mreg_liquidacionexpedientes', $arrCampos, $arrValores, "idliquidacion=" . $t["idliquidacion"] . " and secuencia='" . $t["secuencia"] . "'");
                            }
                            if ($t["matricula"] == 'NUEVASUC') {
                                $arrValores = array(
                                    "'" . ltrim($matSuc, "0") . "'"
                                );
                                regrabarRegistrosMysqliApi($mysqli, 'mreg_liquidacionexpedientes', $arrCampos, $arrValores, "idliquidacion=" . $t["idliquidacion"] . " and secuencia='" . $t["secuencia"] . "'");
                            }
                            if ($t["matricula"] == 'NUEVAAGE') {
                                $arrValores = array(
                                    "'" . ltrim($matAge, "0") . "'"
                                );
                                regrabarRegistrosMysqliApi($mysqli, 'mreg_liquidacionexpedientes', $arrCampos, $arrValores, "idliquidacion=" . $t["idliquidacion"] . " and secuencia='" . $t["secuencia"] . "'");
                            }
                            if ($t["matricula"] == 'NUEVAESA') {
                                $arrValores = array(
                                    "'" . ltrim($matEsadl, "0") . "'"
                                );
                                regrabarRegistrosMysqliApi($mysqli, 'mreg_liquidacionexpedientes', $arrCampos, $arrValores, "idliquidacion=" . $t["idliquidacion"] . " and secuencia='" . $t["secuencia"] . "'");
                            }
                        }
                    }

                    // ************************************************************************************************* //
                    // Actualiza mreg_liquidaciondatos
                    // Cuando se trata de tramites de matricula y reemplaza NUEVANAT Y NUEVAEST
                    // ************************************************************************************************* //
                    if ($asignaMatricula == 'si' && $registroInmediato == 'si') {
                        $arrCampos = array(
                            "xml"
                        );
                        $arrTem = retornarRegistrosMysqliApi($mysqli, 'mreg_liquidaciondatos', "idliquidacion=" . $_SESSION["tramite"]["idliquidacion"], "secuencia");
                        foreach ($arrTem as $t) {
                            $t["xml"] = str_replace("NUEVANAT", ltrim($matPnat, "0"), $t["xml"]);
                            $t["xml"] = str_replace("NUEVAJUR", ltrim($matPjur, "0"), $t["xml"]);
                            $t["xml"] = str_replace("NUEVAEST", ltrim($matEst, "0"), $t["xml"]);
                            $t["xml"] = str_replace("NUEVAESA", ltrim($matEsadl, "0"), $t["xml"]);
                            $t["xml"] = str_replace("NUEVASUC", ltrim($matSuc, "0"), $t["xml"]);
                            $t["xml"] = str_replace("NUEVAAGE", ltrim($matAge, "0"), $t["xml"]);
                            $arrValores = array(
                                "'" . $t["xml"] . "'"
                            );
                            regrabarRegistrosMysqliApi($mysqli, 'mreg_liquidaciondatos', $arrCampos, $arrValores, "idliquidacion=" . $t["idliquidacion"] . " and expediente='" . $t["expediente"] . "'");
                        }
                    }

                    // ************************************************************************************************* //
                    // Actualiza mreg_anexos_liquidaciones
                    // Cuando se trata de tramites de matricula y reemplaza NUEVANAT Y NUEVAEST
                    // ************************************************************************************************* //
                    if ($asignaMatricula == 'si' && $registroInmediato == 'si') {
                        $arrTem = retornarRegistrosMysqliApi($mysqli, 'mreg_anexos_liquidaciones', "idliquidacion=" . $_SESSION["tramite"]["idliquidacion"], "idanexo");
                        foreach ($arrTem as $t) {
                            $expediente = $t["expediente"];
                            $obs = $t["observaciones"];
                            if ($t["expediente"] == 'NUEVANAT') {
                                $expediente = $matPnat;
                            }
                            if ($t["expediente"] == 'NUEVAJUR') {
                                $expediente = $matPjur;
                            }
                            if ($t["expediente"] == 'NUEVAEST') {
                                $expediente = $matEst;
                            }
                            if ($t["expediente"] == 'NUEVASUC') {
                                $expediente = $matSuc;
                            }
                            if ($t["expediente"] == 'NUEVAAGE') {
                                $expediente = $matAge;
                            }
                            if ($t["expediente"] == 'NUEVAESA') {
                                $expediente = $matEsadl;
                            }

                            $obs = str_replace("NUEVANAT", $matPnat, $obs);
                            $obs = str_replace("NUEVAJUR", $matPjur, $obs);
                            $obs = str_replace("NUEVAEST", $matEst, $obs);
                            $obs = str_replace("NUEVASUC", $matSuc, $obs);
                            $obs = str_replace("NUEVAAGE", $matAge, $obs);
                            $obs = str_replace("NUEVAESA", $matEsadl, $obs);
                            $arrCampos = array(
                                'expediente',
                                'observaciones'
                            );
                            $arrValores = array(
                                "'" . $expediente . "'",
                                "'" . $obs . "'"
                            );
                            regrabarRegistrosMysqliApi($mysqli, 'mreg_anexos_liquidaciones', $arrCampos, $arrValores, "idanexo=" . $t["idanexo"]);
                        }
                    }

                    // ************************************************************************************************************* //
                    // Modifica la liquidacion (EN MEMORIA) $_SESSION["tramite"] y asigna los numeros de matricula que SII retorna
                    // Cuando se trata de tramites de matricula y reemplaza NUEVANAT Y NUEVAEST
                    // ************************************************************************************************************* //
                    if ($asignaMatricula == 'si' && $registroInmediato == 'si') {
                        if ($matPnat != '') {
                            $_SESSION["tramite"]["matriculabase"] = ltrim($matPnat, "0");
                        } else {
                            if ($matPjur != '') {
                                $_SESSION["tramite"]["matriculabase"] = ltrim($matPjur, "0");
                            } else {
                                if ($matEsadl != '') {
                                    $_SESSION["tramite"]["matriculabase"] = ltrim($matEsadl, "0");
                                } else {
                                    if ($matSuc != '') {
                                        $_SESSION["tramite"]["matriculabase"] = ltrim($matSuc, "0");
                                    } else {
                                        if ($matAge != '') {
                                            $_SESSION["tramite"]["matriculabase"] = ltrim($matAge, "0");
                                        }
                                    }
                                }
                            }
                        }

                        //
                        $ix = 0;
                        foreach ($_SESSION["tramite"]["liquidacion"] as $ind => $datx) {
                            $ix++;
                            if ($datx["expediente"] == 'NUEVANAT') {
                                $_SESSION["tramite"]["liquidacion"][$ix]["expediente"] = $matPnat;
                            }
                            if ($datx["expediente"] == 'NUEVAJUR') {
                                $_SESSION["tramite"]["liquidacion"][$ix]["expediente"] = $matPjur;
                            }
                            if ($datx["expediente"] == 'NUEVAEST') {
                                $_SESSION["tramite"]["liquidacion"][$ix]["expediente"] = $matEst;
                            }
                            if ($datx["expediente"] == 'NUEVASUC') {
                                $_SESSION["tramite"]["liquidacion"][$ix]["expediente"] = $matSuc;
                            }
                            if ($datx["expediente"] == 'NUEVAAGE') {
                                $_SESSION["tramite"]["liquidacion"][$ix]["expediente"] = $matAge;
                            }
                            if ($datx["expediente"] == 'NUEVAESA') {
                                $_SESSION["tramite"]["liquidacion"][$ix]["expediente"] = $matEsadl;
                            }
                        }
                        $ix = 0;
                        foreach ($_SESSION["tramite"]["expedientes"] as $ind => $datx) {
                            $ix++;
                            if ($datx["matricula"] == 'NUEVANAT') {
                                $_SESSION["tramite"]["expedientes"][$ix]["matricula"] = $matPnat;
                            }
                            if ($datx["matricula"] == 'NUEVAJUR') {
                                $_SESSION["tramite"]["expedientes"][$ix]["matricula"] = $matPjur;
                            }
                            if ($datx["matricula"] == 'NUEVAEST') {
                                $_SESSION["tramite"]["expedientes"][$ix]["matricula"] = $matEst;
                            }
                            if ($datx["matricula"] == 'NUEVASUC') {
                                $_SESSION["tramite"]["expedientes"][$ix]["matricula"] = $matSuc;
                            }
                            if ($datx["matricula"] == 'NUEVAAGE') {
                                $_SESSION["tramite"]["expedientes"][$ix]["matricula"] = $matAge;
                            }
                            if ($datx["matricula"] == 'NUEVAESA') {
                                $_SESSION["tramite"]["expedientes"][$ix]["matricula"] = $matEsadl;
                            }
                        }
                    }
                }
            }
        }

        // ******************************************************************************************** //
        // 2017-12-16: JINT
        // Se adiciona control para ley 1780, código de policia alto impacto y codigo de policía multas
        // Se excluye el control si es reliquidacion
        // Renovaciones
        // ******************************************************************************************** //
        if ($_SESSION["tramite"]["tipotramite"] == 'renovacionmatricula' || $_SESSION["tramite"]["tipotramite"] == 'renovacionesadl') {
            $procesarRen = 'no';
            $tiene1780 = 'no';
            //foreach ($_SESSION["tramite"]["liquidacion"] as $lq) {
            //}
            if (
                    $_SESSION["tramite"]["multadoponal"] != 'S' &&
                    $_SESSION["tramite"]["multadoponal"] != 'L' &&
                    ($_SESSION["tramite"]["cumplorequisitosbenley1780"] != 'S' ||
                    $_SESSION["tramite"]["mantengorequisitosbenley1780"] != 'S' ||
                    $_SESSION["tramite"]["renunciobeneficiosley1780"] != 'N')
            ) {
                $procesarRen = 'si';
            } else {
                if ($_SESSION["tramite"]["reliquidacion"] == 'si') {
                    $procesarRen = 'si';
                }
            }
            if ($procesarRen == 'si') {
                $txterrores = '';
                $xmlDatos = retornarRegistrosMysqliApi($mysqli, 'mreg_liquidaciondatos', "idliquidacion=" . $_SESSION["tramite"]["idliquidacion"], "secuencia");
                if ($xmlDatos && !empty($xmlDatos)) {
                    foreach ($xmlDatos as $xml) {
                        $datos = \funcionesGenerales::desserializarExpedienteMatricula($mysqli, $xml["xml"]);
                        if (($datos["versionciiu"] == '') || ($datos["versionciiu"] == '0') || ($datos["versionciiu"] == '2')) {
                            $datos["versionciiu"] = '3';
                            $xml["xml"] = \funcionesRegistrales::serializarExpedienteMatricula($mysqli, $datos["numerorecuperacion"], $datos);
                        }
                        if (($datos["organizacion"] == '01' || $datos["organizacion"] > '02') && ($datos["categoria"] == '0' || $datos["categoria"] == '1')) {
                            $tidePropietario = $datos["tipoidentificacion"];
                            $idePropietario = $datos["identificacion"];
                            $camPropietario = $_SESSION["generales"]["codigoempresa"];
                            $matPropietario = $datos["matricula"];
                            $orgPropietario = $datos["organizacion"];
                            $catPropietario = $datos["categoria"];
                            $razPropietario = $datos["nombre"];
                            $clasGenPropietario = $datos["clasegenesadl"];
                            $clasEspPropietario = $datos["claseespesadl"];
                            $clasEconPropietario = $datos["claseeconsoli"];
                        } else {
                            if ($tidePropietario == '') {
                                if ($datos["organizacion"] == '02') {
                                    $tidePropietario = $datos["propietarios"][1]["idtipoidentificacionpropietario"];
                                    $idePropietario = $datos["propietarios"][1]["identificacionpropietario"];
                                    $camPropietario = $datos["propietarios"][1]["camarapropietario"];
                                    $matPropietario = $datos["propietarios"][1]["matriculapropietario"];
                                    $orgPropietario = $datos["propietarios"][1]["organizacionpropietario"];
                                    $catPropietario = '0';
                                    $razPropietario = $datos["propietarios"][1]["nombrepropietario"];
                                }

                                if ($datos["organizacion"] > '02') {
                                    $tidePropietario = '2';
                                    $idePropietario = $datos["cpnumnit"];
                                    $camPropietario = $datos["cpcodcam"];
                                    $matPropietario = $datos["cpnummat"];
                                    $orgPropietario = $datos["organizacion"];
                                    $catPropietario = '1';
                                    $razPropietario = $datos["cprazsoc"];
                                }
                            }
                        }

                        // ********************************************************************************************************** //
                        // Ordena los fomularios, primero la persona natural o juridica y luego los establecimientos de comercio
                        // ********************************************************************************************************** //
                        if (($datos["organizacion"] == '01' || $datos["organizacion"] > '02') && ($datos["categoria"] == '' || $datos["categoria"] == '0' || $datos["categoria"] == '1')) {
                            $arregloForms[0]["datos"] = $datos;
                            $arregloForms[0]["xml"] = $xml["xml"];
                            $tieneformularios = 'si';
                        } else {
                            $iForms++;
                            $arregloForms[$iForms]["datos"] = $datos;
                            $arregloForms[$iForms]["xml"] = $xml["xml"];
                            $tieneformularios = 'si';
                        }

                        // ************************************************************************************************* //
                        // Determina si el formulario tienes CIIUS que afecten al libro XXII por ser apuestas y juegos de azar
                        // De acuerdo con el CIIU
                        // ************************************************************************************************* //
                        if (
                                $datos["ciius"][1] == 'R9200' || $datos["ciius"][2] == 'R9200' ||
                                $datos["ciius"][3] == 'R9200' || $datos["ciius"][4] == 'R9200'
                        ) {
                            if (
                                    $datos["organizacion"] == '01' || ($datos["organizacion"] > '02' && $datos["categoria"] == '1')
                            ) {
                                $xSiNat22 = 'S';
                            }
                        }
                    }
                }
            }
        }

        //
        if ($incluyePagoAfiliacion == 'si') {
            if ($_SESSION["tramite"]["incrementocupocertificados"] != 0) {
                $cupoAfiliado = $_SESSION["tramite"]["incrementocupocertificados"];
                // \logApi::general2($nameLog, $idSolicitudPago, \funcionesGenerales::utf8_encode('asigno/incrmento el cupo de certificados por pago de afiliacion en ' . $cupoAfiliado));
            }
        }

        // ************************************************************************************************************** //
        // Genera el recibo de caja en SII
        // Ya actualiza el recibo de caja en SII con los numeros de matricula retornados por el sistema
        // ************************************************************************************************************** //
        // ************************************************************************************************************** //
        // Genera el numero de recibo de caja y de operacion si quien genera los recibos es SII
        // Si el SISTEMA REGISTRO ES SII entonces no importa el parámetro $controlareciboscaja
        // ************************************************************************************************************** //

        if ($generarSII == 'si') {
            if ($fechareciboagenerar == '') {
                $fechaSII = date("Ymd");
                $fechaSIIgob = date("Ymd");
            } else {
                $fechaSII = $fechareciboagenerar;
                $fechaSIIgob = $fechareciboagenerar;
            }
            $horaSII = date("His");
            $horaSIIgob = date("His");

            $operacionSII = \funcionesRegistrales::generarSecuenciaOperacion($mysqli, $_SESSION["generales"]["codigousuario"], $fechaSII, $_SESSION["generales"]["cajero"], $usuCajero["idsedeoperacion"]);
            if ($totalgobernacion != 0) {
                $operacionSIIgob = \funcionesRegistrales::generarSecuenciaOperacion($mysqli, $_SESSION["generales"]["codigousuario"], $fechaSII, $_SESSION["generales"]["cajero"], $usuCajero["idsedeoperacion"]);
            }

            if ($operacionSII === false || $operacionSIIgob === false) {
                $resultado["codigoError"] = '9999';
                $resultado["msgError"] = 'No fue posible generar la operación / recibo de caja en el sistema : ' . $_SESSION["generales"]["mensajeerror"];
                if ($cerrarMysqli == 'si') {
                    $mysqli->close();
                }
                \logApi::general2($nameLog, $idSolicitudPago, \funcionesGenerales::utf8_encode('No fue posible generar el recibo de caja en el sistema : ' . $_SESSION["generales"]["mensajeerror"]));
                $_SESSION["generales"]["codigousuario"] = $_SESSION["generales"]["codigousuariooriginal"];
                return $resultado;
            }
            \logApi::general2($nameLog, $idSolicitudPago, \funcionesGenerales::utf8_encode('Genero numero de operacion: ' . $operacionSII));
            if ($totalgobernacion != 0) {
                \logApi::general2($nameLog, $idSolicitudPago, \funcionesGenerales::utf8_encode('Genero numero de operacion (gobernacion): ' . $operacionSIIgob));
            }

            // ************************************************************************************************************** //
            // Localiza la foma de pago
            // ************************************************************************************************************** //
            $arrFp = array();
            $arrFp["cheque"] = '';
            $arrFp["chequebanco"] = '';
            $arrFp["consignacion"] = '';
            $arrFp["consignacionbanco"] = '';
            $arrFp["ach"] = '';
            $arrFp["achbanco"] = '';
            $arrFp["visa"] = '';
            $arrFp["visabanco"] = '';
            $arrFp["mastercard"] = '';
            $arrFp["mastercardbanco"] = '';
            $arrFp["credencial"] = '';
            $arrFp["credencialbanco"] = '';
            $arrFp["american"] = '';
            $arrFp["americanbanco"] = '';
            $arrFp["diners"] = '';
            $arrFp["dinersbanco"] = '';
            $arrFp["tdebito"] = '';
            $arrFp["tdebitobanco"] = '';
            if ($tipogasto != '1' && $tipogasto != '2' && $tipogasto != '3' && $tipogasto != '5' && $tipogasto != '9' && $tipogasto != 'A') {
                if ($_SESSION["tramite"]["pagocheque"] != 0) {
                    $arrFp["cheque"] = $_SESSION["tramite"]["numerocheque"];
                    $arrFp["chequebanco"] = $_SESSION["tramite"]["idcodban"];
                }
                if ($_SESSION["tramite"]["pagovisa"] != 0) {
                    $arrFp["visa"] = $_SESSION["tramite"]["numeroautorizacion"];
                    $arrFp["visabanco"] = '';
                }
                if ($_SESSION["tramite"]["pagomastercard"] != 0) {
                    $arrFp["mastercard"] = $_SESSION["tramite"]["numeroautorizacion"];
                    $arrFp["mastercardbanco"] = '';
                }
                if ($_SESSION["tramite"]["pagoamerican"] != 0) {
                    $arrFp["american"] = $_SESSION["tramite"]["numeroautorizacion"];
                    $arrFp["americanbanco"] = '';
                }
                if ($_SESSION["tramite"]["pagocredencial"] != 0) {
                    $arrFp["credencial"] = $_SESSION["tramite"]["numeroautorizacion"];
                    $arrFp["credencialbanco"] = '';
                }
                if ($_SESSION["tramite"]["pagodiners"] != 0) {
                    $arrFp["diners"] = $_SESSION["tramite"]["numeroautorizacion"];
                    $arrFp["dinersbanco"] = '';
                }
                if ($_SESSION["tramite"]["pagoconsignacion"] != 0) {
                    $arrFp["consignacion"] = $_SESSION["tramite"]["numerocheque"];
                    $arrFp["consignacionbanco"] = $_SESSION["tramite"]["idcodban"];
                }
                if ($_SESSION["tramite"]["pagoqr"] != 0) {
                    $arrFp["consignacion"] = $_SESSION["tramite"]["numerocheque"];
                    $arrFp["consignacionbanco"] = $_SESSION["tramite"]["idcodban"];
                }
                if ($_SESSION["tramite"]["pagoach"] != 0) {
                    $arrFp["ach"] = $_SESSION["tramite"]["numeroautorizacion"];
                    $arrFp["achbanco"] = '';
                }
            }

            //
            $xnombre = '';
            if ($tipogasto == '1' || $tipogasto == '2' || $tipogasto == '3') {
                $xnombre = $_SESSION["tramite"]["nombrecliente"];
            } else {
                if ($_SESSION["tramite"]["idtipoidentificacioncliente"] == '2') {
                    $xnombre = $_SESSION["tramite"]["nombrecliente"];
                } else {
                    $xnombre = $_SESSION["tramite"]["apellido1cliente"];
                    if (trim($_SESSION["tramite"]["apellido2cliente"]) != '') {
                        $xnombre .= ' ' . $_SESSION["tramite"]["apellido2cliente"];
                    }
                    if (trim($_SESSION["tramite"]["nombre1cliente"]) != '') {
                        $xnombre .= ' ' . $_SESSION["tramite"]["nombre1cliente"];
                    }
                    if (trim($_SESSION["tramite"]["nombre2cliente"]) != '') {
                        $xnombre .= ' ' . $_SESSION["tramite"]["nombre2cliente"];
                    }
                }
            }

            //
            if ($tipogasto == '1' || $tipogasto == '2' || $tipogasto == '3') {
                $reciboSII = \funcionesRegistrales::generarSecuenciaRecibo($mysqli, 'H', $arrFp, $operacionSII, $fechaSII, $horaSII, $codbarrasSII, $tipoRegistro, $_SESSION["tramite"]["identificacioncliente"], $xnombre, '', '', '', '', '', '', '', '', 'S', $arrServs);
            } else {
                if ($tipogasto == '5') {
                    if (CONTABILIZAR_PREPAGO_COMO == 'CXP') {
                        $reciboSII = \funcionesRegistrales::generarSecuenciaRecibo($mysqli, 'S', $arrFp, $operacionSII, $fechaSII, $horaSII, $codbarrasSII, $tipoRegistro, $_SESSION["tramite"]["identificacioncliente"], $xnombre, '', '', '', '', '', '', '', $fecharenaplicable, 'S', $arrServs);
                        if ($totalgobernacion != 0) {
                            $reciboSIIgob = \funcionesRegistrales::generarSecuenciaRecibo($mysqli, 'S', $arrFp, $operacionSIIgob, $fechaSIIgob, $horaSIIgob, $codbarrasSII, $tipoRegistro, $_SESSION["tramite"]["identificacioncliente"], $xnombre, '', '', '', '', '', '', '', '', 'G', $arrServs);
                        }
                    } else {
                        $reciboSII = \funcionesRegistrales::generarSecuenciaRecibo($mysqli, 'H', $arrFp, $operacionSII, $fechaSII, $horaSII, $codbarrasSII, $tipoRegistro, $_SESSION["tramite"]["identificacioncliente"], $xnombre, '', '', '', '', '', '', '', '', 'S', $arrServs);
                    }
                } else {
                    if ($tipogasto == '9') {
                        $reciboSII = \funcionesRegistrales::generarSecuenciaRecibo($mysqli, 'D', $arrFp, $operacionSII, $fechaSII, $horaSII, $codbarrasSII, $tipoRegistro, $_SESSION["tramite"]["identificacioncliente"], $xnombre, '', '', '', '', '', '', '', 'S', $arrServs);
                    } else {
                        $reciboSII = \funcionesRegistrales::generarSecuenciaRecibo($mysqli, 'S', $arrFp, $operacionSII, $fechaSII, $horaSII, $codbarrasSII, $tipoRegistro, $_SESSION["tramite"]["identificacioncliente"], $xnombre, '', '', '', '', '', '', '', $fecharenaplicable, 'S', $arrServs);
                        if ($totalgobernacion != 0) {
                            $reciboSIIgob = \funcionesRegistrales::generarSecuenciaRecibo($mysqli, 'S', $arrFp, $operacionSIIgob, $fechaSIIgob, $horaSIIgob, $codbarrasSII, $tipoRegistro, $_SESSION["tramite"]["identificacioncliente"], $xnombre, '', '', '', '', '', '', '', '', 'G', $arrServs);
                        }
                    }
                }
            }

            if ($reciboSII === false || $reciboSIIgob === false) {
                $resultado["codigoError"] = '9999';
                $resultado["msgError"] = 'No fue posible generar el recibo de caja en el sistema : ' . $_SESSION["generales"]["mensajeerror"];
                if ($cerrarMysqli == 'si') {
                    $mysqli->close();
                }
                \logApi::general2($nameLog, $idSolicitudPago, \funcionesGenerales::utf8_encode('No fue posible generar el recibo de caja en el sistema (3) : ' . $_SESSION["generales"]["mensajeerror"]));
                $_SESSION["generales"]["codigousuario"] = $_SESSION["generales"]["codigousuariooriginal"];
                return $resultado;
            } else {
                \logApi::general2($nameLog, $idSolicitudPago, \funcionesGenerales::utf8_encode('Recibo generado : ' . $reciboSII));
            }

            $txtx = 'Es reliquidacion: ' . $_SESSION["tramite"]["reliquidacion"] .
                    ', Incremento en el cupo de certificados: ' . $_SESSION["tramite"]["incrementocupocertificados"] .
                    ', Entra a generar recibo de caja: ' . $reciboSII;
            \logApi::general2($nameLog, $idSolicitudPago, $txtx);

            $estadoSII = '01';
            $result = array();
            $result["codigoError"] = '0000';
            $result["msgError"] = '';
            $result["numerorecibo"] = $reciboSII;
            $result["numerooperacion"] = $operacionSII;
            $result["fecharecibo"] = $fechaSII;
            $result["horarecibo"] = $horaSII;
            $result["numeroradicacion"] = '';

            $result["numerorecibogob"] = $reciboSIIgob;
            $result["numerooperaciongob"] = $operacionSIIgob;
            $result["fecharecibogob"] = $fechaSIIgob;
            $result["horarecibogob"] = $horaSIIgob;
        } else {
            $result = array();
            $result["codigoError"] = '0000';
            $result["msgError"] = '';
            $result["numerorecibo"] = $reciboSII;
            $result["numerooperacion"] = $operacionSII;
            $result["fecharecibo"] = $fechaSII;
            $result["horarecibo"] = $horaSII;
            $result["numeroradicacion"] = '';

            $result["numerorecibogob"] = $reciboSIIgob;
            $result["numerooperaciongob"] = $operacionSIIgob;
            $result["fecharecibogob"] = $fechaSIIgob;
            $result["horarecibogob"] = $horaSIIgob;
        }


        // ************************************************************************************************************** //
        // Si la respuesta es falsa por error en la generacion del recibo de caja
        // ************************************************************************************************************** //
        if ($result === false) {
            $resultado["codigoError"] = '9999';
            $resultado["msgError"] = 'No fue posible registrar el recibo en el sistema de Registro : ' . $_SESSION["generales"]["mensajeerror"];
            if ($cerrarMysqli == 'si') {
                $mysqli->close();
            }
            $txtx = 'No fue posible registrar el recibo en el sistema (3) : ' . $_SESSION["generales"]["mensajeerror"] .
                    ', Error : ' . $_SESSION["generales"]["mensajeerror"];
            \logApi::general2($nameLog, $idSolicitudPago, $txtx);
            \logApi::general2($nameLog, $idSolicitudPago, \funcionesGenerales::utf8_encode('######################  REGISTRO DE PAGO TERMINADO CON ERROR #####################'));
            $_SESSION["generales"]["codigousuario"] = $_SESSION["generales"]["codigousuariooriginal"];
            return $resultado;
        }

        // ************************************************************************************************************** //
        // le indica al Log que genero el recibo de caja
        // ************************************************************************************************************** //
        \funcionesRegistrales::actualizarMregLiquidacionFlujo($mysqli, $_SESSION["tramite"]["tipotramite"], $_SESSION["tramite"]["idliquidacion"], $idSolicitudPago, '2', $result["numerorecibo"], $result["numerooperacion"], '', $result["fecharecibo"], $result["horarecibo"]);
        if ($totalgobernacion != 0) {
            \funcionesRegistrales::actualizarMregLiquidacionFlujo($mysqli, $_SESSION["tramite"]["tipotramite"], $_SESSION["tramite"]["idliquidacion"], $idSolicitudPago, '2', $result["numerorecibogob"], $result["numerooperaciongob"], '', $result["fecharecibogob"], $result["horarecibogob"]);
        }
        \logApi::general2($nameLog, $idSolicitudPago, 'Actualizó flujo de la liquidación');

        // ************************************************************************************************************** //
        // Actualiza el estado del recibo en el SII 
        // Siempre y cuando los consecutivos los controle SII
        // Esto previene que en caso de reliquidacion por errores se genere un nuevo recibo
        // ************************************************************************************************************** //
        $arrCampos = array('estado');
        $arrValores = array("'02'");
        regrabarRegistrosMysqliApi($mysqli, 'mreg_recibosgenerados', $arrCampos, $arrValores, "recibo='" . $reciboSII . "'");
        if ($totalgobernacion != 0) {
            regrabarRegistrosMysqliApi($mysqli, 'mreg_recibosgenerados', $arrCampos, $arrValores, "recibo='" . $reciboSIIgob . "'");
        }
        \logApi::general2($nameLog, $idSolicitudPago, 'Puso en 02 el estado del recibo');

        if ($grupoServicios == 'CerEle') {
            if ($cerrarMysqli == 'si') {
                $mysqli->close();
            }
            \logApi::general2($nameLog, $idSolicitudPago, \funcionesGenerales::utf8_encode('###################### PROCESO CER-ELE TERMINADO #####################'));
            $_SESSION["generales"]["codigousuario"] = $_SESSION["generales"]["codigousuariooriginal"];
            return $resultado;
        }

        // ************************************************************************************************************** //
        // Asigna las variables de respuesta del SII con los datos del recibo de caja
        // ************************************************************************************************************** //
        if (
                $_SESSION["tramite"]["idestado"] == '05' ||
                $_SESSION["tramite"]["idestado"] == '06' ||
                $_SESSION["tramite"]["idestado"] == '66' ||
                $_SESSION["tramite"]["idestado"] == '11' ||
                $_SESSION["tramite"]["idestado"] == '19' ||
                $_SESSION["tramite"]["idestado"] == '44'
        ) {
            if ($estadofinalliquidacion != '') {
                $_SESSION["tramite"]["idestado"] = $estadofinalliquidacion;
            }
        }

        //
        $_SESSION["tramite"]["numerorecibo"] = $result["numerorecibo"];
        $_SESSION["tramite"]["numerooperacion"] = $result["numerooperacion"];
        $_SESSION["tramite"]["fecharecibo"] = $result["fecharecibo"];
        $_SESSION["tramite"]["horarecibo"] = $result["horarecibo"];
        if (isset($result["numeroradicacion"])) {
            $_SESSION["tramite"]["numeroradicacion"] = $result["numeroradicacion"];
        }
        // } else {
        //    $_SESSION["tramite"]["numeroradicacion"] = '';
        // }
        $_SESSION["tramite"]["totalrecibo"] = $totalcamara;
        $_SESSION["tramite"]["numerorecibogob"] = $result["numerorecibogob"];
        $_SESSION["tramite"]["numerooperaciongob"] = $result["numerooperaciongob"];
        $_SESSION["tramite"]["fecharecibogob"] = $result["fecharecibogob"];
        $_SESSION["tramite"]["horarecibogob"] = $result["horarecibogob"];
        $_SESSION["tramite"]["totalrecibogob"] = $totalgobernacion;

        //
        $certificadoConsultaRues = 'no';
        if (
                $_SESSION["tramite"]["idestado"] != '20' &&
                $_SESSION["tramite"]["idestado"] != '21' &&
                $_SESSION["tramite"]["idestado"] != '22' &&
                $_SESSION["tramite"]["idestado"] != '23' &&
                $_SESSION["tramite"]["idestado"] != '24'
        ) {

            if ($_SESSION["generales"]["escajero"] == 'SI') {
                $_SESSION["tramite"]["idestado"] = '09'; // Pagada en caja
            } else {
                if ($_SESSION["tramite"]["idformapago"] == '91') {
                    $_SESSION["tramite"]["idestado"] = '25'; // Con cargo al prepago
                } else {
                    $_SESSION["tramite"]["idestado"] = '07'; // Pago electronico
                }
            }
            if (
                    substr($_SESSION["tramite"]["tipotramite"], 0, 4) == 'rues' &&
                    substr($_SESSION["tramite"]["tipotramite"], 6) == 'responsable'
            ) {
                $_SESSION["tramite"]["idestado"] = '09'; // Pagada en caja
                if (ltrim($_SESSION["tramite"]["rues_camarareceptora"], "0") == '') {
                    $certificadoConsultaRues = 'si';
                }
            }
        }

        //
        $_SESSION["tramite"]["sede"] = $usuCajero["idsede"];
        $resultado1 = \funcionesRegistrales::grabarLiquidacionMreg($mysqli);
        if ($resultado1 === false) {
            \logApi::general2($nameLog, $idSolicitudPago, \funcionesGenerales::utf8_encode('Error regrabando en mreg_liquidacion los datos del recibo de caja generado : ' . $_SESSION["generales"]["mensajeerror"]));
        }
        \logApi::general2($nameLog, $idSolicitudPago, 'Actualizó liquidacion con los datos del recibo generado');

        // ************************************************************************************************************** //
        // 2018-09-03: JINT
        // En caso de rues04responsable, busca la ruta asociada al servicio (el primer servicio)
        // ************************************************************************************************************** //
        $creaRutax = '';
        if ($_SESSION["tramite"]["tipotramite"] == 'rues04responsable') {
            $ilx = 0;
            foreach ($_SESSION["tramite"]["liquidacion"] as $lx) {
                $ilx++;
                if ($ilx == 1) {
                    $creaRutax = retornarRegistroMysqliApi($mysqli, 'mreg_servicios', "idservicio='" . $lx["idservicio"] . "'", "rutareparto");
                }
            }
            if (ltrim(trim($creaRutax), "0") == '') {
                $creaRutax = $creaRuta;
            }
            $creaRuta = $creaRutax;
        }

        // ************************************************************************************************************** //
        // Crea la ruta si es un tramite que requiera actualizacion de rutas
        // ************************************************************************************************************** //
        $xRuta = '';
        $xTipoDoc = '';
        $xEstados = array();
        $txtOrigen = '';
        $xNumDoc = '';
        $xFecDoc = '';
        $xMunDoc = '';
        if (trim($creaRuta) != '') {
            $xRuta = $creaRuta;
            $xNumDoc = "";
            $xTipoDoc = "";
            $xMunDoc = "";
            $txtOrigen = "";
            $xFecDoc = "";

            switch ($_SESSION["tramite"]["tipotramite"]) {

                case "mutacionactividad":
                case "mutaciondireccion":
                case "mutacionnombre":
                case "mutacionregmer":
                case "mutacionesadl":
                    $xNumDoc = 'N/A';
                    $xTipoDoc = '06';
                    $xMunDoc = $_SESSION["tramite"]["municipiopagador"];
                    $xFecDoc = date("Ymd");
                    $txtOrigen = 'EL COMERCIANTE';
                    if (substr($_SESSION["tramite"]["idexpedientebase"], 0, 1) == 'S') {
                        $txtOrigen = 'LA PERSONA JURIDICA';
                    }
                    break;

                case "solicitudcancelacionpnat":
                case "solicitudcancelacionest":
                case "mutacionnombre":
                case "mutacionregmer":
                case "mutacionesadl":
                    $xNumDoc = 'N/A';
                    $xTipoDoc = '06';
                    $xMunDoc = $_SESSION["tramite"]["municipiopagador"];
                    $xFecDoc = date("Ymd");
                    $txtOrigen = 'EL COMERCIANTE';
                    if (substr($_SESSION["tramite"]["idexpedientebase"], 0, 1) == 'S') {
                        $txtOrigen = 'LA PERSONA JURIDICA';
                    }
                    break;

                case "renovacionmatricula":
                case "renovacionesadl":
                    $xNumDoc = 'N/A';
                    $xTipoDoc = '08';
                    $xMunDoc = $_SESSION["tramite"]["municipiopagador"];
                    $xFecDoc = date("Ymd");
                    $txtOrigen = 'EL COMERCIANTE';
                    if (substr($_SESSION["tramite"]["idexpedientebase"], 0, 1) == 'S') {
                        $txtOrigen = 'LA PERSONA JURIDICA';
                    }
                    break;

                case "inscripcionproponente":
                case "renovacionproponente":
                case "actualizacionproponente":
                case "actualizacionproponente399":
                case "cancelacionproponente":
                case "cambiodomicilioproponente":
                    $xNumDoc = 'N/A';
                    $xTipoDoc = '21';
                    $xFecDoc = date("Ymd");
                    $xMunDoc = $_SESSION["tramite"]["municipiopagador"];
                    $txtOrigen = 'EL PROPONENTE';
                    break;

                case "matriculapnat":
                case "matriculaest":
                    $xNumDoc = 'N/A';
                    $xTipoDoc = '08';
                    $xFecDoc = date("Ymd");
                    $xMunDoc = $_SESSION["tramite"]["municipiopagador"];
                    $txtOrigen = 'EL COMERCIANTE';
                    if (substr($_SESSION["tramite"]["idexpedientebase"], 0, 1) == 'S') {
                        $txtOrigen = 'LA PERSONA JURIDICA';
                    }
                    break;

                case "inscripciondocumentos":
                    if ($_SESSION["tramite"]["subtipotramite"] == 'matriculapnatcae' ||
                            $_SESSION["tramite"]["subtipotramite"] == 'matriculapnatvue' ||
                            $_SESSION["tramite"]["subtipotramite"] == 'matriculaestcae' ||
                            $_SESSION["tramite"]["subtipotramite"] == 'matriculaestvue') {
                        $xNumDoc = 'N/A';
                        $xTipoDoc = '08';
                        $xFecDoc = date("Ymd");
                        $xMunDoc = $_SESSION["tramite"]["municipiopagador"];
                        $txtOrigen = 'EL COMERCIANTE';
                        if (substr($_SESSION["tramite"]["idexpedientebase"], 0, 1) == 'S') {
                            $txtOrigen = 'LA PERSONA JURIDICA';
                        }
                    } else {
                        $xNumDoc = $_SESSION["tramite"]["numdoc"];
                        $xTipoDoc = sprintf("%02s", $_SESSION["tramite"]["tipodoc"]);
                        $xMunDoc = $_SESSION["tramite"]["mundoc"];
                        $txtOrigen = $_SESSION["tramite"]["origendoc"];
                        $xFecDoc = $_SESSION["tramite"]["fechadoc"];
                    }
                    break;

                default:
                    break;
            }

            if (trim($xNumDoc) == '') {
                $xNumDoc = $_SESSION["tramite"]["numdoc"];
                $xTipoDoc = sprintf("%02s", $_SESSION["tramite"]["tipodoc"]);
                $xMunDoc = $_SESSION["tramite"]["mundoc"];
                $txtOrigen = $_SESSION["tramite"]["origendoc"];
                $xFecDoc = $_SESSION["tramite"]["fechadoc"];
            }



            //
            if ($_SESSION["tramite"]["subtipotramite"] == 'matriculapnatcae' ||
                    $_SESSION["tramite"]["subtipotramite"] == 'matriculapnatvue' ||
                    $_SESSION["tramite"]["subtipotramite"] == 'matriculaestcae' ||
                    $_SESSION["tramite"]["subtipotramite"] == 'matriculaestvue') {
                $estadosRutaPredefinidosInscripcionDocumentos = $estadosRutaPredefinidos;
            }

            //
            $txt = 'Ruta asignada : ' . $xRuta . chr(13) . chr(10);
            $txt .= 'Tipo documento : ' . $xTipoDoc . chr(13) . chr(10);
            $txt .= 'Numero del documento: ' . $xNumDoc . chr(13) . chr(10);
            $txt .= 'Municipio del documento: ' . $xMunDoc . chr(13) . chr(10);
            $txt .= 'Origen del documento: ' . $txtOrigen . chr(13) . chr(10);
            $txt .= 'Fecha del documento: ' . $xFecDoc . chr(13) . chr(10);
            $txt .= 'Pasos ruta predefinidos (para tramite directo): ' . $estadosRutaPredefinidos . chr(13) . chr(10);
            $txt .= 'Pasos ruta predefinidos (para inscripcion de documentos): ' . $estadosRutaPredefinidosInscripcionDocumentos . chr(13) . chr(10);
            \logApi::general2($nameLog, $idSolicitudPago, $txt);

            $xEstados = array();
            // ****************************************************************************************************** //
            // Asigna rutas por defecto a los tramites pagados en forma electronica o recibidos en caja
            // ****************************************************************************************************** // 
            if (
                    $_SESSION["tramite"]["tipotramite"] == 'inscripciondocumentos' ||
                    $_SESSION["tramite"]["tipotramite"] == 'inscripcionesregmer' ||
                    $_SESSION["tramite"]["tipotramite"] == 'inscripcionesesadl'
            ) {
                if (
                        $_SESSION["tramite"]["multadoponal"] == 'S' ||
                        $_SESSION["tramite"]["multadoponal"] == 'L' ||
                        $_SESSION["tramite"]["controlactividadaltoimpacto"] == 'S' ||
                        ($_SESSION["tramite"]["cumplorequisitosbenley1780"] == 'S' &&
                        $_SESSION["tramite"]["mantengorequisitosbenley1780"] == 'S' &&
                        $_SESSION["tramite"]["renunciobeneficiosley1780"] == 'N')
                ) {
                    $estadosRutaPredefinidosInscripcionDocumentos = '';
                }
                if ($estadosRutaPredefinidos != '' && $asientoautomaticomatriculas == 'no' && $asignaMatricula = 'si') {
                    $estadosRutaPredefinidos = '';
                }

                if (trim($estadosRutaPredefinidosInscripcionDocumentos) == '') {
                    \logApi::general2($nameLog, $idSolicitudPago, \funcionesGenerales::utf8_encode('No localizo pasos de la ruta predefinidos'));
                    $xEstados[1]["estado"] = '01'; // Radicado
                    $xEstados[1]["ope"] = $idope; // Radicado
                } else {
                    $estadosRutaPredefinidos = str_replace(" ", "", trim($estadosRutaPredefinidosInscripcionDocumentos));
                    $il = 0;
                    $lis = explode(",", $estadosRutaPredefinidos);
                    foreach ($lis as $l) {
                        $il++;
                        $xEstados[$il]["estado"] = $l;
                        $xEstados[$il]["ope"] = $idope;
                    }
                }
            } else {
                // 2017-12-16: JINT
                // Se adiciona control para ley 1780, código de policia alto impacto y codigo de policía multas
                if (
                        $_SESSION["tramite"]["multadoponal"] == 'S' ||
                        $_SESSION["tramite"]["multadoponal"] == 'L' ||
                        $_SESSION["tramite"]["controlactividadaltoimpacto"] == 'S' ||
                        ($_SESSION["tramite"]["cumplorequisitosbenley1780"] == 'S' &&
                        $_SESSION["tramite"]["mantengorequisitosbenley1780"] == 'S' &&
                        $_SESSION["tramite"]["renunciobeneficiosley1780"] == 'N') ||
                        ($asignaMatricula == 'si' && $registroInmediato == 'no') ||
                        ($esMutacion == 'si' && $registroInmediato == 'no') ||
                        ($esRenovacion == 'si' && $registroInmediato == 'no') ||
                        ($esCancelacion == 'si' && $registroInmediato == 'no')
                ) {
                    // \logApi::general2($nameLog, $idSolicitudPago, 'Inicializa estados a asignar al código de barras $estadosRutaPredefinidos (1)');
                    $estadosRutaPredefinidos = '';
                }
                if ($estadosRutaPredefinidos != '' && $asientoautomaticomatriculas == 'no' && $asignaMatricula == 'si') {
                    // \logApi::general2($nameLog, $idSolicitudPago, 'Inicializa estados a asignar al código de barras $estadosRutaPredefinidos (2)');
                    $estadosRutaPredefinidos = '';
                } else {
                    if ($estadosRutaPredefinidos != '' && $asientoautomaticomutaciones == 'no' && $esMutacion == 'si') {
                        // \logApi::general2($nameLog, $idSolicitudPago, 'Inicializa estados a asignar al código de barras $estadosRutaPredefinidos (3)');
                        $estadosRutaPredefinidos = '';
                    }
                }

                if (trim($estadosRutaPredefinidos) == '') {
                    $estadosRutaPredefinidos = '01';
                }
                if (trim($estadosRutaPredefinidos) == '') {
                    $xEstados[1]["estado"] = '01'; // Radicado
                    $xEstados[1]["ope"] = $idope; // Radicado
                } else {
                    $estadosRutaPredefinidos = str_replace(" ", "", trim($estadosRutaPredefinidos));
                    $il = 0;
                    $lis = explode(",", $estadosRutaPredefinidos);
                    foreach ($lis as $l) {
                        $il++;
                        $xEstados[$il]["estado"] = $l;
                        $xEstados[$il]["ope"] = $idope;
                    }

                    // En caso de no haber formulartios deja el trámite en radicado
                    if (
                            $_SESSION["tramite"]["tipotramite"] == 'renovacionmatricula' ||
                            $_SESSION["tramite"]["tipotramite"] == 'renovacionesadl' ||
                            $_SESSION["tramite"]["tipotramite"] == 'mutaciondireccion' ||
                            $_SESSION["tramite"]["tipotramite"] == 'mutacionactividad' ||
                            $_SESSION["tramite"]["tipotramite"] == 'mutacionnombre'
                    ) {
                        if (empty($arregloForms)) {
                            $xEstados = array();
                            $xEstados[1]["estado"] = '01'; // Radicado
                            $xEstados[1]["ope"] = $idope; // Radicado
                        }
                    }
                }
            }

            \logApi::general2($nameLog, $idSolicitudPago, 'Estados a asignar al código de barras: ' . $estadosRutaPredefinidos);

            // Asigna el tipo de matricula que se asocia con el expediente
            if ($asignaMatricula == 'si' && $registroInmediato == 'si') {
                // 2017-12-16: JINT
                // Se adiciona control para ley 1780, código de policia alto impacto y codigo de policía multas
                if (
                        $_SESSION["tramite"]["multadoponal"] != 'S' &&
                        $_SESSION["tramite"]["multadoponal"] != 'L' &&
                        $_SESSION["tramite"]["controlactividadaltoimpacto"] != 'S' && ($_SESSION["tramite"]["cumplorequisitosbenley1780"] != 'S' ||
                        $_SESSION["tramite"]["mantengorequisitosbenley1780"] != 'S' ||
                        $_SESSION["tramite"]["renunciobeneficiosley1780"] != 'N')
                ) {

                    switch ($_SESSION["tramite"]["tipotramite"]) {

                        case "matriculapnat":
                            $matExpediente = $matPnat;
                            break;
                        case "matriculaest":
                            $matExpediente = $matEst;
                            break;
                        case "matriculacambidom":
                            $matExpediente = $matPjur;
                            break;
                        case "matriculasuc":
                            $matExpediente = $matSuc;
                            break;
                        case "matriculaage":
                            $matExpediente = $matAge;
                            break;
                        case "matriculapjur":
                            $matExpediente = $matPjur;
                            break;
                        case "matriculaesadl":
                            $matExpediente = $matEsadl;
                            break;
                        case "inscripciondocumentos":
                            switch ($_SESSION["tramite"]["subtipotramite"]) {
                                case "matriculapnatcae":
                                case "matriculapnatvue":
                                    $matExpediente = $matPnat;
                                    break;
                                case "matriculaetcae":
                                case "matriculaestvue":
                                    $matExpediente = $matEst;
                                    break;
                            }
                            break;
                        default:
                            break;
                    }
                }
            }

            //
            $xDetalle = '';
            $idclaserr = '';
            $numidrr = '';
            $nomrr = '';
            $librr = '';
            $regrr = '';
            $duprr = '';
            $sarr = ''; // Incluye apelacion
            $saarr = ''; // solo apelacion
            if (
                    $_SESSION["tramite"]["tipotramite"] == 'recursosreposicionregmer' ||
                    $_SESSION["tramite"]["tipotramite"] == 'recursosreposicionesadl' ||
                    $_SESSION["tramite"]["tipotramite"] == 'recursosreposicionregpro'
            ) {
                $idclaserr = $_SESSION["tramite"]["tipoidentificacionrr"];
                $numidrr = $_SESSION["tramite"]["identificacionrr"];
                $nomrr = $_SESSION["tramite"]["nombre1rr"] . ' ' . $_SESSION["tramite"]["nombre2rr"] . ' ' . $_SESSION["tramite"]["apellido1rr"] . ' ' . $_SESSION["tramite"]["apellido2rr"];
                if (trim($_SESSION["tramite"]["inscripcionrr"]) != '') {
                    list($librr, $regrr, $duprr) = explode("-", $_SESSION["tramite"]["inscripcionrr"]);
                }
                $sarr = $_SESSION["tramite"]["subsidioapelacionrr"];
                $saarr = $_SESSION["tramite"]["soloapelacionrr"];
            }

            //
            if (trim($_SESSION["tramite"]["descripcionpqr"]) != '') {
                $xDetalle = $_SESSION["tramite"]["descripcionpqr"];
            }
            if (trim($_SESSION["tramite"]["descripcionrr"]) != '') {
                $xDetalle = $_SESSION["tramite"]["descripcionrr"];
            }
            if (isset($_SESSION["tramite"]["descripcioncor"])) {
                if (trim($_SESSION["tramite"]["descripcioncor"]) != '') {
                    $xDetalle = $_SESSION["tramite"]["descripcioncor"];
                }
            }
            if (trim($_SESSION["tramite"]["descripcionembargo"]) != '') {
                $xDetalle = $_SESSION["tramite"]["descripcionembargo"];
            }
            if (trim($_SESSION["tramite"]["descripciondesembargo"]) != '') {
                $xDetalle = $_SESSION["tramite"]["descripciondesembargo"];
            }
            if (trim($_SESSION["tramite"]["motivocorreccion"]) != '') {
                $xDetalle = $_SESSION["tramite"]["motivocorreccion"];
            }
            if (trim($xDetalle) == '') {
                $xDetalle = $_SESSION["tramite"]["textolibre"];
            }

            // ************************************************************************* //
            // 2016-07-30 : JINT
            // Si el sistema de registro es SII, el código de barras lo crea SII
            // ************************************************************************* //
            $buscar = 'si';
            $icant = 0;
            if (!defined('CONTROLA_CODIGOSBARRAS') || CONTROLA_CODIGOSBARRAS == '' || CONTROLA_CODIGOSBARRAS == 'SII' || CONTROLA_CODIGOSBARRAS == 'SIREP') {
                while ($buscar == 'si') {
                    $cbSII = \funcionesRegistrales::generarSecuenciaCodigoBarras($mysqli);
                    if ($cbSII == 0 || $cbSII === false) {
                        $icant++;
                        if ($icant > 20) {
                            $buscar = 'error';
                        }
                    } else {
                        $buscar = 'no';
                    }
                }
                if ($buscar == 'error') {
                    \logApi::general2($nameLog, $idSolicitudPago, \funcionesGenerales::utf8_encode('No fue posible crear el codigo de barras de la transacción'));
                }
            }

            //
            if (defined('CONTROLA_CODIGOSBARRAS') && CONTROLA_CODIGOSBARRAS == 'POWERFILE') {
                $cbSII = \funcionesRegistrales::generarSecuenciaCodigoBarrasPowerFile($mysqli);
                if ($cbSII === false || ltrim(trim($cbSII), "0") == '') {
                    \logApi::general2($nameLog, $idSolicitudPago, \funcionesGenerales::utf8_encode('Error recuperando codigo de barras desde PowerFile'));
                } else {
                    \logApi::general2($nameLog, $idSolicitudPago, \funcionesGenerales::utf8_encode('Codigo de barras asignado por PowerFile : ' . $cbSII));
                }
            }

            // 2021-02-01: JINT: Se ajusta para que en caso que las renovaciones estén marcadas como no registro inmediato, el campo
            // verificacionsoportes quede en SI para obligar a pasr por la bandeja de revisión de trámites no automáticos.
            $verificacionsoportes = 'SI';
            if (
                    $_SESSION["tramite"]["multadoponal"] != 'S' &&
                    $_SESSION["tramite"]["multadoponal"] != 'L' &&
                    $_SESSION["tramite"]["controlactividadaltoimpacto"] != 'S' && ($_SESSION["tramite"]["cumplorequisitosbenley1780"] != 'S' ||
                    $_SESSION["tramite"]["mantengorequisitosbenley1780"] != 'S' ||
                    $_SESSION["tramite"]["renunciobeneficiosley1780"] != 'N')
            ) {
                if ($_SESSION["tramite"]["tipotramite"] == 'renovacionmatricula' || $_SESSION["tramite"]["tipotramite"] == 'renovacionesadl') {
                    if ($registroInmediato == 'si') {
                        if (isset($_SESSION["generales"]["tiporenovacion"]) && $_SESSION["generales"]["tiporenovacion"] == 'prediligenciados') {
                            $verificacionsoportes = 'SI';
                        } else {
                            $verificacionsoportes = 'NO';
                        }
                    } else {
                        if (substr($_SESSION["tramite"]["numerooperacion"], 0, 2) != '97' && substr($_SESSION["tramite"]["numerooperacion"], 0, 2) != '99') {
                            $verificacionsoportes = 'NO';
                        }
                    }
                } else {
                    $verificacionsoportes = 'NO';
                }
            }

            if ($verificacionsoportes == 'NO') {
                if (
                        $_SESSION["tramite"]["tipotramite"] == 'rues02responsable' ||
                        $_SESSION["tramite"]["tipotramite"] == 'rues03responsable' ||
                        $_SESSION["tramite"]["tipotramite"] == 'rues04responsable' ||
                        $_SESSION["tramite"]["tipotramite"] == 'rues05responsable' ||
                        $_SESSION["tramite"]["tipotramite"] == 'rues06responsable'
                ) {
                    $verificacionsoportes = 'SI';
                }
            }

            //
            if ($verificacionsoportes == 'NO') {
                if ($asignaMatricula == 'si' && ($asientoautomaticomatriculas == 'no' || $registroInmediato == 'no')) {
                    $verificacionsoportes = 'SI';
                }
                if ($esMutacion == 'si' && ($asientoautomaticomutaciones == 'no' || $registroInmediato == 'no')) {
                    $verificacionsoportes = 'SI';
                }
                if ($esRenovacion == 'si' && ($asientoautomaticorenovaciones == 'no' || $registroInmediato == 'no' || $tieneformularios != 'si')) {
                    $verificacionsoportes = 'SI';
                }
                if ($esCancelacion == 'si' && $registroInmediato == 'no') {
                    $verificacionsoportes = 'SI';
                }
            }

            //
            if ($requieresoportesasentamiento == 'si') {
                $verificacionsoportes = 'SI';
            }

            //
            if ($cbSII && (string) $cbSII != '' && (string) $cbSII != '0') {
                // Crea la tabla mreg_est_codigosbarras
                $arrCampos = array(
                    'operacion',
                    'recibo',
                    'fecharadicacion',
                    'matricula',
                    'proponente',
                    'idclase',
                    'numid',
                    'numdocextenso',
                    'nombre',
                    'estadofinal',
                    'operadorfinal',
                    'fechaestadofinal',
                    'horaestadofinal',
                    'sucursalfinal',
                    'activos',
                    'liquidacion',
                    'reliquidacion',
                    'actoreparto',
                    'tipdoc',
                    'numdoc',
                    'oridoc',
                    'mundoc',
                    'fecdoc',
                    'sucursalradicacion',
                    'detalle',
                    'verificacionsoportes',
                    'idclaserecurrente',
                    'numidrecurrente',
                    'nombrerecurrente',
                    'libroafectado',
                    'registroafectado',
                    'dupliafectado',
                    'subsidioapelacion'
                );
                $arrValores = array(
                    "'" . $_SESSION["tramite"]["numerooperacion"] . "'",
                    "'" . $_SESSION["tramite"]["numerorecibo"] . "'",
                    "'" . $fechaSII . "'",
                    "'" . $matExpediente . "'",
                    "'" . $proExpediente . "'",
                    "'" . $tideExpediente . "'",
                    "'" . $ideExpediente . "'",
                    "''", // NumdocExtenso
                    "'" . addslashes($razExpediente) . "'",
                    "'" . $xEstados[1]["estado"] . "'",
                    "'" . $xEstados[1]["ope"] . "'",
                    "'" . date("Ymd") . "'",
                    "'" . date("His") . "'",
                    "''", // Sucursal final
                    0,
                    0,
                    "'" . strtoupper(substr($_SESSION["tramite"]["reliquidacion"], 0, 1)) . "'", // reliquidacion
                    "'" . $xRuta . "'",
                    "'" . $xTipoDoc . "'",
                    "'" . $xNumDoc . "'",
                    "'" . $txtOrigen . "'",
                    "'" . $xMunDoc . "'",
                    "'" . $xFecDoc . "'", // fecha del documento
                    "''", // Sucursal radicación
                    "'" . addslashes($xDetalle) . "'",
                    "'" . $verificacionsoportes . "'",
                    "'" . $idclaserr . "'",
                    "'" . $numidrr . "'",
                    "'" . addslashes($nomrr) . "'",
                    "'" . $librr . "'",
                    "'" . $regrr . "'",
                    "'" . $duprr . "'",
                    "'" . $sarr . "'"
                );
                $res = regrabarRegistrosMysqliApi($mysqli, 'mreg_est_codigosbarras', $arrCampos, $arrValores, "codigobarras='" . $cbSII . "'");
                if ($res === false) {
                    \logApi::general2($nameLog, $idSolicitudPago, \funcionesGenerales::utf8_encode('No fue posible actualizar la informacion en el codigo de barras ' . $_SESSION["generales"]["mensajeerror"]));
                }

                //
                if ($saarr !== '') {
                    $arrCampos = array(
                        'codigobarras',
                        'campo',
                        'contenido'
                    );
                    $arrValores = array(
                        "'" . $cbSII . "'",
                        "'soloapelacion'",
                        "'" . $saarr . "'"
                    );
                    insertarRegistrosMysqliApi($mysqli, 'mreg_est_codigosbarras_campos', $arrCampos, $arrValores);
                }

                //
                $arrCampos = array(
                    'codigobarras',
                    'campo',
                    'contenido'
                );
                $arrValores = array(
                    "'" . $cbSII . "'",
                    "'nombrebase64'",
                    "'" . base64_encode($razExpediente) . "'"
                );
                insertarRegistrosMysqliApi($mysqli, 'mreg_est_codigosbarras_campos', $arrCampos, $arrValores);

                
                // Matrículas asociadas
                
                
                
                //
                $detalle = 'Cambio estado del codigo de barras No. ' . $cbSII . ', estado final: ' . $xEstados[1]["estado"] . ', operador final: ' . $xEstados[1]["ope"];
                actualizarLogMysqliApi($mysqli, '069', $_SESSION["generales"]["codigousuario"], 'gestionRecibos', '', '', '', $detalle, '', '');

                // Adiciona el primer estado de la ruta al código de barras
                $arrCampos = array(
                    'codigobarras',
                    'fecha',
                    'hora',
                    'estado',
                    'operador',
                    'sucursal'
                );
                $arrValores = array(
                    "'" . $cbSII . "'",
                    "'" . $fechaSII . "'",
                    "'" . date("His") . "'",
                    "'" . $xEstados[1]["estado"] . "'",
                    "'" . $xEstados[1]["ope"] . "'",
                    "''"
                );
                insertarRegistrosMysqliApi($mysqli, 'mreg_est_codigosbarras_documentos', $arrCampos, $arrValores);

                $respuestaRuta = array(
                    'codigoError' => '0000',
                    'codbarras' => $cbSII
                );
            } else {
                $respuestaRuta = array(
                    'codigoError' => '9999',
                    'codbarras' => 0,
                    'mensajeError' => 'No fue posible recuperar el número del radicado'
                );
            }

            if ($respuestaRuta["codigoError"] != '0000') {
                \logApi::general2($nameLog, $idSolicitudPago, \funcionesGenerales::utf8_encode('Error creando ruta : ' . $respuestaRuta["msgError"]));
            } else {
                \logApi::general2($nameLog, $idSolicitudPago, \funcionesGenerales::utf8_encode('Creo ruta CodBarras : ' . $respuestaRuta["codbarras"]));
            }


            // ************************************************************************************************************** //
            // Asigna el codigo de barras retornado a la liquidacion
            // ************************************************************************************************************** //		
            $_SESSION["tramite"]["numeroradicacion"] = $cbSII;
            $result["numeroradicacion"] = $cbSII;
            $arrCampos = array('numeroradicacion');
            $arrValues = array("'" . $_SESSION["tramite"]["numeroradicacion"] . "'");
            $condicion = "idliquidacion=" . $_SESSION["tramite"]["idliquidacion"];
            $resx = regrabarRegistrosMysqliApi($mysqli, 'mreg_liquidacion', $arrCampos, $arrValues, $condicion);
            if ($resx === false) {
                \logApi::general2($nameLog, $idSolicitudPago, \funcionesGenerales::utf8_encode('Error en la asignacion del codigo de barras (' . $_SESSION["tramite"]["numeroradicacion"] . ') a la liquidacion (' . $_SESSION["tramite"]["idliquidacion"] . ')'));
            } else {
                \logApi::general2($nameLog, $idSolicitudPago, \funcionesGenerales::utf8_encode('asigno del codigo de barras (' . $_SESSION["tramite"]["numeroradicacion"] . ') a la liquidacion (' . $_SESSION["tramite"]["idliquidacion"] . ')'));
            }

            // ************************************************************************************* //
            // Asigna los pasos de la ruta que se deben generar
            // ************************************************************************************* //
            $archivable = 'no';
            $iEst = 0;
            $ultEst = '';
            $opeUlt = '';
            foreach ($xEstados as $est) {
                $ultEst = $est["estado"];
                $opeUlt = $est["ope"];
                $iEst++;
                if ($iEst > 1) {
                    // Adiciona pasos adicionales a la ruta
                    $arrCampos = array(
                        'codigobarras',
                        'fecha',
                        'hora',
                        'estado',
                        'operador',
                        'sucursal'
                    );
                    $arrValores = array(
                        "'" . $cbSII . "'",
                        "'" . date("Ymd") . "'",
                        "'" . date("His") . "'",
                        "'" . $est["estado"] . "'",
                        "'" . $est["ope"] . "'",
                        "''"
                    );
                    insertarRegistrosMysqliApi($mysqli, 'mreg_est_codigosbarras_documentos', $arrCampos, $arrValores);
                }
            }

            // Asigna el último estado de la ruta al código de barras.
            if ($iEst > 1) {
                $arrCampos = array(
                    'estadofinal',
                    'operadorfinal',
                    'fechaestadofinal',
                    'horaestadofinal',
                    'sucursalfinal',
                );
                $arrValores = array(
                    "'" . $ultEst . "'",
                    "'" . $opeUlt . "'",
                    "'" . date("Ymd") . "'",
                    "'" . date("His") . "'",
                    "''"
                );
                regrabarRegistrosMysqliApi($mysqli, 'mreg_est_codigosbarras', $arrCampos, $arrValores, "codigobarras='" . $cbSII . "'");
                $detalle = 'Cambio estado del codigo de barras No. ' . $cbSII . ', estado final: ' . $ultEst . ', operador final: ' . $opeUlt;
                actualizarLogMysqliApi($mysqli, '069', $_SESSION["generales"]["codigousuario"], 'gestionRecibos', '', '', '', $detalle, '', '');
                if ($est == '15') {
                    $archivable = 'si';
                }
            }
        }

        // ************************************************************************************************************** //
        // asigna el código de barras al recibo generado
        // ************************************************************************************************************** //    
        $_SESSION["generales"]["codigobarras1"] = '';
        if ($_SESSION["tramite"]["numeroradicacion"] != '') {
            $_SESSION["generales"]["codigobarras1"] = $_SESSION["tramite"]["numeroradicacion"];
            $arrCampos = array('codigobarras');
            $arrValores = array("'" . $_SESSION["tramite"]["numeroradicacion"] . "'");
            $resx = regrabarRegistrosMysqliApi($mysqli, 'mreg_recibosgenerados', $arrCampos, $arrValores, "recibo='" . $_SESSION["tramite"]["numerorecibo"] . "'");
            if ($totalgobernacion != 0) {
                $resx = regrabarRegistrosMysqliApi($mysqli, 'mreg_recibosgenerados', $arrCampos, $arrValores, "recibo='" . $_SESSION["tramite"]["numerorecibogob"] . "'");
            }
            \logApi::general2($nameLog, $idSolicitudPago, 'Actualizó datos del códigop de barras en el recibo generado');
        }

        // ************************************************************************************************************** //
        // Si es matricula o renovacion encuentra si es beneficiario o no para actualizar el formulario en SII
        // Valida Ley 1429
        // valida Ley 1780
        // ************************************************************************************************************** //    
        // 2017-12-16: JINT
        // Se adiciona control para ley 1780, código de policia alto impacto y codigo de policía multas        
        $benart7 = '';
        $benley1780 = '';
        if (
                $_SESSION["tramite"]["multadoponal"] != 'S' &&
                $_SESSION["tramite"]["multadoponal"] != 'L' &&
                $_SESSION["tramite"]["controlactividadaltoimpacto"] != 'S' && ($_SESSION["tramite"]["cumplorequisitosbenley1780"] != 'S' ||
                $_SESSION["tramite"]["mantengorequisitosbenley1780"] != 'S' ||
                $_SESSION["tramite"]["renunciobeneficiosley1780"] != 'N')
        ) {
            if ($grupoServicios == 'RegMer') {
                if ($asignaMatricula == 'si' || $esRenovacion == 'si') {
                    $arrTem = retornarRegistrosMysqliApi($mysqli, 'mreg_liquidacionexpedientes', "idliquidacion=" . $_SESSION["tramite"]["idliquidacion"], "secuencia");
                    foreach ($arrTem as $t) {
                        if ($t["registrobase"] == 'S') {
                            if ($t["cc"] == '' || $t["cc"] == CODIGO_EMPRESA) {
                                if (
                                        $t["organizacion"] == '01' || ($t["organizacion"] > '02' && $t["categoria"] == '1')
                                ) {
                                    if ($asignaMatricula != 'si') {
                                        if ($t["benart7"] == 'S') {
                                            $benart7 = 'S';
                                        }
                                        if ($t["benart7"] == 'N') {
                                            $benart7 = 'N';
                                        }
                                        if ($t["benart7"] == 'P') {
                                            $benart7 = 'P';
                                        }
                                        if ($t["benart7"] == 'R') {
                                            $benart7 = 'R';
                                        }
                                    } else {
                                        $benart7 = 'N';
                                    }
                                    if ($t["benley1780"] == 'S') {
                                        $benley1780 = 'S';
                                    }
                                    if ($t["benley1780"] == 'N') {
                                        $benley1780 = 'N';
                                    }
                                    if ($t["benley1780"] == 'P') {
                                        $benley1780 = 'P';
                                    }
                                    if ($t["benley1780"] == 'R') {
                                        $benley1780 = 'R';
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        \logApi::general2($nameLog, $idSolicitudPago, \funcionesGenerales::utf8_encode('asigno el tipo de beneficiario si es matricula o renovacion'));

        // }
        // ************************************************************************************************************** //
        // Localiza el último año renovado por matrícula
        // Localiza que expedientes se deben marcar en estado 4
        // ************************************************************************************************************** //
        if ($esRenovacion == 'si') {
            $arrMatsAct = array();
            $arrMatsRens = array();
            $arrMatsActivos = array();
            $arrMatsActVin = array();
            $arrMatsPersonal = array();
            $ix = 0;
            foreach ($_SESSION["tramite"]["liquidacion"] as $d) {
                if (isset($d["idservicio"]) && $d["idservicio"] != '') {
                    if (substr($d["idservicio"], 0, 6) == '010202') {
                        if ($d["cc"] == '' || $d["cc"] == CODIGO_EMPRESA) {
                            $ix++;
                            if (trim($d["expediente"]) != '') {
                                if (!isset($arrMatsAct[$d["expediente"]])) {
                                    $arrMatsAct[$d["expediente"]] = $d["expediente"];
                                }
                                if (ltrim($d["ano"], "0") != '') {
                                    if (!isset($arrMatsRens[$d["expediente"]])) {
                                        $arrMatsRens[$d["expediente"]] = '';
                                    }
                                    if ($arrMatsRens[$d["expediente"]] < $d["ano"]) {
                                        $arrMatsRens[$d["expediente"]] = $d["ano"];
                                        if ($d["idservicio"] == '01020201' || $d["idservicio"] == '01020208') {
                                            $arrMatsPersonal[$d["expediente"]] = 0;
                                            $arrMatsActVin[$d["expediente"]] = 0;
                                            if (isset($_SESSION["tramite"]["tiporenovacion"]) && $_SESSION["tramite"]["tiporenovacion"] == 'prediligenciados') {
                                                $arrMatsActivos[$d["expediente"]] = $_SESSION["tramite"]["activosbase"];
                                                $arrMatsPersonal[$d["expediente"]] = $_SESSION["tramite"]["personalbase"];
                                            } else {
                                                $arrMatsActivos[$d["expediente"]] = $d["valorbase"];
                                            }
                                        } else {
                                            $arrMatsPersonal[$d["expediente"]] = 0;
                                            $arrMatsActivos[$d["expediente"]] = 0;
                                            if (isset($_SESSION["tramite"]["tiporenovacion"]) && $_SESSION["tramite"]["tiporenovacion"] == 'prediligenciados') {
                                                $arrMatsActVin[$d["expediente"]] = $_SESSION["tramite"]["activosbase"];
                                                $arrMatsPersonal[$d["expediente"]] = $_SESSION["tramite"]["personalbase"];
                                            } else {
                                                $arrMatsActVin[$d["expediente"]] = $d["valorbase"];
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        \logApi::general2($nameLog, $idSolicitudPago, \funcionesGenerales::utf8_encode('localizo el ultimo ano renovado si es renovacion'));

        // ************************************************************************************************************** //
        // Actualiza SII siempre y cuando sea renovación 
        // y no sea un tramite RUES 
        // y no hayan formularios
        // ************************************************************************************************************** //
        $fecharenovacion = '';
        // 2017-12-16: JINT
        // Se adiciona control para ley 1780, código de policia alto impacto y codigo de policía multas        
        if (
                $_SESSION["tramite"]["multadoponal"] != 'S' &&
                $_SESSION["tramite"]["multadoponal"] != 'L' &&
                $_SESSION["tramite"]["controlactividadaltoimpacto"] != 'S' && ($_SESSION["tramite"]["cumplorequisitosbenley1780"] != 'S' ||
                $_SESSION["tramite"]["mantengorequisitosbenley1780"] != 'S' ||
                $_SESSION["tramite"]["renunciobeneficiosley1780"] != 'N')
        ) {

            if ($esRenovacion == 'si' && $registroInmediato == 'si') {
                if ($fecharenovacionagenerar != '') {
                    if (substr(strtoupper($_SESSION["tramite"]["reliquidacion"]), 0, 1) != 'S') {
                        $fecharenovacion = $fecharenovacionagenerar;
                    }
                } else {
                    if (substr(strtoupper($_SESSION["tramite"]["reliquidacion"]), 0, 1) != 'S') {
                        $fecharenovacion = date("Ymd");
                    }
                }
            }
        }
        \logApi::general2($nameLog, $idSolicitudPago, \funcionesGenerales::utf8_encode('localiza la fecha de renovacion en caos de estos tipos de tramite'));

        // ************************************************************************************************************** //
        // Actualiza mreg_est_inscritos siempre y cuando sea renovación 
        // y no sea un tramite RUES y 
        // no hayan formularios
        // ************************************************************************************************************** //
        // 2017-12-16: JINT
        // Se adiciona control para ley 1780, código de policia alto impacto y codigo de policía multas
        // ************************************************************************************************************** //
        // 2020-03-23: JINT
        // Se elimina el control de alto impacto.
        // ************************************************************************************************************** //        
        if (
                $_SESSION["tramite"]["multadoponal"] != 'S' &&
                $_SESSION["tramite"]["multadoponal"] != 'L' &&
                // $_SESSION["tramite"]["controlactividadaltoimpacto"] != 'S' && 
                ($_SESSION["tramite"]["cumplorequisitosbenley1780"] != 'S' ||
                $_SESSION["tramite"]["mantengorequisitosbenley1780"] != 'S' ||
                $_SESSION["tramite"]["renunciobeneficiosley1780"] != 'N')
        ) {

            if ($esRenovacion == 'si') {
                if ($tieneformularios == 'no' || $registroInmediato == 'no') {
                    foreach ($arrMatsAct as $m) {
                        $res = \funcionesRegistrales::actualizarMregEstInscritosCampo($mysqli, ltrim($m, "0"), 'ctrestdatos', '4', 'varchar', $_SESSION["generales"]["codigobarras1"], $_SESSION["tramite"]["tipotramite"], $_SESSION["tramite"]["numerorecibo"]);
                        if ($res === false) {
                            \logApi::general2($nameLog, $idSolicitudPago, \funcionesGenerales::utf8_encode('Error poniendo estado en 4 en mreg_est_inscritos para la matricula  ' . ltrim($m, "0")));
                        }
                    }
                }
                if ($tieneformularios == 'no' && $grupoServicios != 'RUE' && $registroInmediato == 'si') {
                    foreach ($arrMatsRens as $m => $a) {
                        if (trim($a) != '') {
                            if (substr(strtoupper($_SESSION["tramite"]["reliquidacion"]), 0, 1) != 'S') {
                                $res = \funcionesRegistrales::actualizarMregEstInscritosCampo($mysqli, ltrim($m, "0"), 'fecrenovacion', $fecharenovacion, 'varchar', $_SESSION["generales"]["codigobarras1"], $_SESSION["tramite"]["tipotramite"], $_SESSION["tramite"]["numerorecibo"]);
                                if ($res === false) {
                                    \logApi::general2($nameLog, $idSolicitudPago, \funcionesGenerales::utf8_encode('Error actualizando fecrenovacion en mreg_est_inscritos para la matricula  ' . ltrim($m, "0")));
                                }
                            }
                            $res = \funcionesRegistrales::actualizarMregEstInscritosCampo($mysqli, ltrim($m, "0"), 'anodatos', $a, 'varchar', $_SESSION["generales"]["codigobarras1"], $_SESSION["tramite"]["tipotramite"], $_SESSION["tramite"]["numerorecibo"]);
                            if ($res === false) {
                                \logApi::general2($nameLog, $idSolicitudPago, \funcionesGenerales::utf8_encode('Error actualizando anodatos en mreg_est_inscritos para la matricula  ' . ltrim($m, "0")));
                            }
                            $res = \funcionesRegistrales::actualizarMregEstInscritosCampo($mysqli, ltrim($m, "0"), 'fecdatos', date("Ymd"), 'varchar', $_SESSION["generales"]["codigobarras1"], $_SESSION["tramite"]["tipotramite"], $_SESSION["tramite"]["numerorecibo"]);
                            if ($res === false) {
                                \logApi::general2($nameLog, $idSolicitudPago, \funcionesGenerales::utf8_encode('Error actualizando fecdatos en mreg_est_inscritos para la matricula  ' . ltrim($m, "0")));
                            }
                            if ($arrMatsActivos[$m] != 0) {
                                $res = \funcionesRegistrales::actualizarMregEstInscritosCampo($mysqli, ltrim($m, "0"), 'acttot', $arrMatsActivos[$m], 'double', $_SESSION["generales"]["codigobarras1"], $_SESSION["tramite"]["tipotramite"], $_SESSION["tramite"]["numerorecibo"]);
                                if ($res === false) {
                                    \logApi::general2($nameLog, $idSolicitudPago, \funcionesGenerales::utf8_encode('Error actualizando acttot en mreg_est_inscritos para la matricula  ' . ltrim($m, "0")));
                                }
                                $res = \funcionesRegistrales::actualizarMregEstInscritosCampo($mysqli, ltrim($m, "0"), 'pattot', $arrMatsActivos[$m], 'double', $_SESSION["generales"]["codigobarras1"], $_SESSION["tramite"]["tipotramite"], $_SESSION["tramite"]["numerorecibo"]);
                                if ($res === false) {
                                    \logApi::general2($nameLog, $idSolicitudPago, \funcionesGenerales::utf8_encode('Error actualizando paspat en mreg_est_inscritos para la matricula  ' . ltrim($m, "0")));
                                }
                                $res = \funcionesRegistrales::actualizarMregEstInscritosCampo($mysqli, ltrim($m, "0"), 'paspat', $arrMatsActivos[$m], 'double', $_SESSION["generales"]["codigobarras1"], $_SESSION["tramite"]["tipotramite"], $_SESSION["tramite"]["numerorecibo"]);
                                if ($res === false) {
                                    \logApi::general2($nameLog, $idSolicitudPago, \funcionesGenerales::utf8_encode('Error actualizando paspat en mreg_est_inscritos para la matricula  ' . ltrim($m, "0")));
                                }
                            }
                            if ($arrMatsActVin[$m] != 0) {
                                $res = \funcionesRegistrales::actualizarMregEstInscritosCampo($mysqli, ltrim($m, "0"), 'actvin', $arrMatsActVin[$m], 'double', $_SESSION["generales"]["codigobarras1"], $_SESSION["tramite"]["tipotramite"], $_SESSION["tramite"]["numerorecibo"]);
                                if ($res === false) {
                                    \logApi::general2($nameLog, $idSolicitudPago, \funcionesGenerales::utf8_encode('Error actualizando actvin en mreg_est_inscritos para la matricula  ' . ltrim($m, "0")));
                                }
                            }
                            $res = \funcionesRegistrales::actualizarMregEstInscritosCampo($mysqli, ltrim($m, "0"), 'personal', $arrMatsPersonal[$m], 'double', $_SESSION["generales"]["codigobarras1"], $_SESSION["tramite"]["tipotramite"], $_SESSION["tramite"]["numerorecibo"]);
                            if ($res === false) {
                                \logApi::general2($nameLog, $idSolicitudPago, \funcionesGenerales::utf8_encode('Error actualizando personal en mreg_est_inscritos para la matricula  ' . ltrim($m, "0")));
                            }

                            $arrCampos = array(
                                'personaltemp',
                                'actcte',
                                'actnocte',
                                'actfij',
                                'fijnet',
                                'actval',
                                'actotr',
                                'actsinaju',
                                'invent',
                                'pascte',
                                'paslar',
                                'pastot',
                                'balsoc',
                                'ingope',
                                'ingnoope',
                                'gasope',
                                'gasnoope',
                                'gasint',
                                'gasimp',
                                'gtoven',
                                'gtoadm',
                                'utiope',
                                'utinet',
                                'cosven',
                                'depamo'
                            );
                            $arrValores = array(
                                0,
                                0,
                                0,
                                0,
                                0,
                                0,
                                0,
                                0,
                                0,
                                0,
                                0,
                                0,
                                0,
                                0,
                                0,
                                0,
                                0,
                                0,
                                0,
                                0,
                                0,
                                0,
                                0,
                                0,
                                0
                            );
                            unset($_SESSION["expedienteactual"]);
                            regrabarRegistrosMysqliApi($mysqli, 'mreg_est_inscritos', $arrCampos, $arrValores, "matricula='" . $m . "'");
                        }
                    }
                }
            }
        }
        \logApi::general2($nameLog, $idSolicitudPago, \funcionesGenerales::utf8_encode('actualizo datos de las matriculas en caso de renovacion siempre y cuando no hayan formularios'));

        // ************************************************************************************************************** //
        // Actualiza mreg_est_financiera con los datos de la renovación, siempre y cuando
        // y no sea un tramite RUES y 
        // no hayan formularios
        // ************************************************************************************************************** //
        // 2017-12-16: JINT
        // Se adiciona control para ley 1780, código de policia alto impacto y codigo de policía multas
        // ************************************************************************************************************** //
        // 2020-03-23: JINT
        // Se elimina el control de alto impacto.     
        // ************************************************************************************************************** //        
        if (
                $_SESSION["tramite"]["multadoponal"] != 'S' &&
                $_SESSION["tramite"]["multadoponal"] != 'L' &&
                // $_SESSION["tramite"]["controlactividadaltoimpacto"] != 'S' && 
                ($_SESSION["tramite"]["cumplorequisitosbenley1780"] != 'S' ||
                $_SESSION["tramite"]["mantengorequisitosbenley1780"] != 'S' ||
                $_SESSION["tramite"]["renunciobeneficiosley1780"] != 'N')
        ) {

            if ($esRenovacion == 'si') {
                if ($tieneformularios == 'no' && $grupoServicios != 'RUE' && $registroInmediato == 'si') {
                    foreach ($_SESSION["tramite"]["liquidacion"] as $d) {
                        if (isset($d["idservicio"]) && $d["idservicio"] != '') {
                            if (substr($d["idservicio"], 0, 6) == '010202') {
                                if ($d["cc"] == '' || $d["cc"] == CODIGO_EMPRESA) {
                                    $activos = 0;
                                    $personal = 0;
                                    $valest = 0;
                                    if ($d["idservicio"] == '01020201' || $d["idservicio"] == '01020208') {
                                        $activos = $d["valorbase"];
                                    } else {
                                        $valest = $d["valorbase"];
                                    }
                                    if (isset($_SESSION["tramite"]["tiporenovacion"]) && $_SESSION["tramite"]["tiporenovacion"] == 'prediligenciados') {
                                        $personal = $_SESSION["tramite"]["personalbase"];
                                    }
                                    $arrCampos = array(
                                        'matricula',
                                        'anodatos',
                                        'fechadatos',
                                        'personal',
                                        'acttot',
                                        'patnet',
                                        'paspat',
                                        'actvin'
                                    );
                                    $arrValores = array(
                                        "'" . $d["expediente"] . "'",
                                        "'" . $d["ano"] . "'",
                                        "'" . date("Ymd") . "'",
                                        intval($personal),
                                        doubleval($activos),
                                        doubleval($activos),
                                        doubleval($activos),
                                        doubleval($valest)
                                    );
                                    $resx = insertarRegistrosMysqliApi($mysqli, 'mreg_est_financiera', $arrCampos, $arrValores);
                                    if ($resx === false) {
                                        \logApi::general2($nameLog, $idSolicitudPago, \funcionesGenerales::utf8_encode('Error creando informacion financiera para ' . $d["expediente"] . ' - ' . $d["ano"] . ' - ' . date("Ymd") . ' - ' . $d["valorbase"]));
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        \logApi::general2($nameLog, $idSolicitudPago, \funcionesGenerales::utf8_encode('actualizo informacion financiera en caso de renovacion siempre y cuando no hayan formularios'));

        // ************************************************************************************************************** //
        // ACTUALIZA EL FORMULARIO
        // - Con la existencia de formularios
        // - Matriculas
        // ************************************************************************************************************** //
        // 2017-12-16: JINT
        // Se adiciona control para ley 1780, código de policia alto impacto y codigo de policía multas
        // ************************************************************************************************************** //
        // ************************************************************************************************************** //
        // 2020-03-23: JINT
        // Se separa en caso de matrículas de la renovación
        // Por el manejo que se le debe dar a las renovaciones con alto impacto
        // ************************************************************************************************************** //
        $formulariosactualizados = 0;
        if (
                $_SESSION["tramite"]["multadoponal"] != 'S' &&
                $_SESSION["tramite"]["multadoponal"] != 'L' &&
                $_SESSION["tramite"]["controlactividadaltoimpacto"] != 'S' &&
                ($_SESSION["tramite"]["cumplorequisitosbenley1780"] != 'S' ||
                $_SESSION["tramite"]["mantengorequisitosbenley1780"] != 'S' ||
                $_SESSION["tramite"]["renunciobeneficiosley1780"] != 'N')
        ) {
            if ($asignaMatricula == 'si' && $registroInmediato == 'si' && $grupoServicios != 'RUE') {
                $arregloForms1 = array();
                foreach ($arregloForms as $x) {
                    if ($x["datos"]["organizacion"] == '01' || ($x["datos"]["organizacion"] > '02' && $x["datos"]["categoria"] == '1')) {
                        $arregloForms1[] = $x;
                    }
                }
                foreach ($arregloForms as $x) {
                    if ($x["datos"]["organizacion"] == '02' || ($x["datos"]["organizacion"] > '02' && ($x["datos"]["categoria"] == '2' || $x["datos"]["categoria"] == '3'))) {
                        if ($x["datos"]["cc"] == '' || $x["datos"]["cc"] == CODIGO_EMPRESA) {
                            $arregloForms1[] = $x;
                        }
                    }
                }

                //
                $actualizoFormularios = 'no';
                foreach ($arregloForms1 as $x) {
                    if ($asignaMatricula == 'si') {
                        $x["datos"]["estadodatosmatricula"] = '2';
                        $x["xml"] = str_replace("<estadodatosmatricula></estadodatosmatricula>", "<estadodatosmatricula>2</estadodatosmatricula>", $x["xml"]);
                        $x["xml"] = str_replace("<estadodatosmatricula>6</estadodatosmatricula>", "<estadodatosmatricula>2</estadodatosmatricula>", $x["xml"]);
                        if (
                                $x["datos"]["organizacion"] == '01' || ($x["datos"]["organizacion"] > '02' && $x["datos"]["categoria"] == '1')
                        ) {
                            $x["datos"]["art4"] = $benart7;
                            $x["datos"]["art7"] = $benart7;
                            if ($_SESSION["tramite"]["cumplorequisitosbenley1780"] != '') {
                                $x["datos"]["cumplerequisitos1780"] = $_SESSION["tramite"]["cumplorequisitosbenley1780"];
                            }
                            if ($_SESSION["tramite"]["mantengorequisitosbenley1780"] != '') {
                                $x["datos"]["cumplerequisitos1780primren"] = $_SESSION["tramite"]["mantengorequisitosbenley1780"];
                            }
                            if ($_SESSION["tramite"]["renunciobeneficiosley1780"] != '') {
                                $x["datos"]["renunciabeneficios1780"] = $_SESSION["tramite"]["renunciobeneficiosley1780"];
                            }
                            $x["datos"]["benley1780"] = $benley1780;
                            if (trim($x["datos"]["versionciiu"]) == '') {
                                $x["datos"]["versionciiu"] = '0';
                            }
                            if ($fecharenovacion == '') {
                                $fecharenovacion = date("Ymd");
                            }
                            $x["datos"]["fecharenovacion"] = $fecharenovacion;
                            $x["datos"]["fechamatricula"] = $fecharenovacion;
                            $x["datos"]["ultanoren"] = substr($fecharenovacion, 0, 4);
                            $x["datos"]["ingresostamanoempresarial"] = $x["datos"]["ingope"];
                            $x["datos"]["anodatostamanoempresarial"] = $x["datos"]["ultanoren"];
                            $x["datos"]["fechadatostamanoempresarial"] = $x["datos"]["fecharenovacion"];
                            $x["xml"] = \funcionesRegistrales::serializarExpedienteMatricula($mysqli, $_SESSION["tramite"]["numerorecuperacion"], $x["datos"]);
                        } else {
                            // 2020-01-21 JINT: Se incluye para prevenir que deje en blanco la fecha de matrícula y la fecha de renovación
                            if ($fecharenovacion == '') {
                                $fecharenovacion = date("Ymd");
                            }
                            $x["datos"]["fecharenovacion"] = $fecharenovacion;
                            $x["datos"]["fechamatricula"] = $fecharenovacion;
                            $x["datos"]["ultanoren"] = substr($fecharenovacion, 0, 4);
                            $x["datos"]["ingresostamanoempresarial"] = $x["datos"]["ingope"];
                            $x["datos"]["anodatostamanoempresarial"] = $x["datos"]["ultanoren"];
                            $x["datos"]["fechadatostamanoempresarial"] = $x["datos"]["fecharenovacion"];
                            $x["xml"] = \funcionesRegistrales::serializarExpedienteMatricula($mysqli, $_SESSION["tramite"]["numerorecuperacion"], $x["datos"]);
                        }
                    }

                    // Asigna datos del cambio de domicilio                
                    if ($arrTipoTramite["escambiodomicilio"] == 'si' || $_SESSION["tramite"]["tipomatricula"] == 'cambidom') {
                        $x["datos"]["fecharenovacion"] = $_SESSION["tramite"]["fecrencambidom"];
                        $x["datos"]["ultanoren"] = $_SESSION["tramite"]["ultanoren"];
                        $x["datos"]["ingresostamanoempresarial"] = $x["datos"]["ingope"];
                        $x["datos"]["anodatostamanoempresarial"] = $x["datos"]["ultanoren"];
                        $x["datos"]["fechadatostamanoempresarial"] = $x["datos"]["fecharenovacion"];
                        $x["datos"]["estadomatricula"] = 'MA';
                        $x["datos"]["estadodatosmatricula"] = '6';
                        $x["datos"]["muncom"] = $_SESSION["tramite"]["munpnat"];
                        $x["datos"]["fecmatant"] = $_SESSION["tramite"]["fecmatcambidom"];
                        $x["datos"]["fecrenant"] = $_SESSION["tramite"]["fecrencambidom"];
                        $x["datos"]["ultanorenant"] = $_SESSION["tramite"]["ultanoren"];
                        $x["datos"]["camant"] = $_SESSION["tramite"]["camaracambidom"];
                        $x["datos"]["matant"] = $_SESSION["tramite"]["matriculacambidom"];
                        $x["datos"]["munant"] = $_SESSION["tramite"]["municipiocambidom"];
                        if (isset($_SESSION["tramite"]["benart7cambidom"])) {
                            $x["datos"]["benart7ant"] = $_SESSION["tramite"]["benart7cambidom"];
                        }
                        if (isset($_SESSION["tramite"]["benart1780cambidom"])) {
                            $x["datos"]["benley1780ant"] = $_SESSION["tramite"]["benart1780cambidom"];
                        }
                        if (trim($x["datos"]["versionciiu"]) == '') {
                            $x["datos"]["versionciiu"] = '0';
                        }

                        $x["xml"] = \funcionesRegistrales::serializarExpedienteMatricula($mysqli, $_SESSION["tramite"]["numerorecuperacion"], $x["datos"]);
                    }

                    //
                    if ($_SESSION["generales"]["cajero"] == 'USUPUBXX') {
                        $_SESSION["generales"]["idcodigosirepcaja"] = 'WWW';
                    }
                    // \logApi::general2($nameLog, $_SESSION["tramite"]["idliquidacion"], \funcionesGenerales::utf8_encode('Re-Actualizara formulario de la matricula ' . $x["datos"]["matricula"] . ': ' . $x["xml"]));
                    if (!isset($_SESSION["tramite"]["tipomatricula"])) {
                        $_SESSION["tramite"]["tipomatricula"] = '';
                    }

                    //  
                    $data = $x["datos"];
                    $formulariosactualizados++;

                    // Actualiza mreg_est_inscritos
                    $respuestaSii = \funcionesRegistrales::actualizarMregEstInscritos($mysqli, $data, $_SESSION["generales"]["codigobarras1"], $_SESSION["tramite"]["tipotramite"], $_SESSION["tramite"]["numerorecibo"]);
                    if ($respuestaSii == false) {
                        \logApi::general2($nameLog, $_SESSION["tramite"]["idliquidacion"], 'Error actualizando formulario Matricula en SII (mreg_est_inscritos) ' . $x["datos"]["matricula"] . ' : ' . str_replace("'", "", $_SESSION["generales"]["mensajeerror"]));
                        actualizarLogMysqliApi($mysqli, '502', $_SESSION["generales"]["codigousuario"], 'asentarReciboRegistro', '', '', '', str_replace("'", "", $respuestaSii["msgError"]), $x["datos"]["matricula"], '', '', $_SESSION["tramite"]["idliquidacion"]);
                        $txterrores .= 'Matricula ' . $x["datos"]["matricula"] . ', ' . $respuestaSii["msgError"] . '\n\n';
                        $okProcesoTerminado = 'no';
                    }
                    if ($data["organizacion"] == '02') {
                        if ($arrTipoTramite["asignamatricula"] == 'si') {
                            $arrCampos = array(
                                'matricula',
                                'codigocamara',
                                'matriculapropietario',
                                'tipopropiedad',
                                'tipoidentificacion',
                                'identificacion',
                                'nit',
                                'razonsocial',
                                'apellido1',
                                'apellido2',
                                'nombre1',
                                'nombre2',
                                'dircom',
                                'muncom',
                                'telcom1',
                                'telcom2',
                                'telcom3',
                                'emailcom',
                                'dirnot',
                                'munnot',
                                'telnot1',
                                'telnot2',
                                'telnot3',
                                'emailnot',
                                'tipoidentificacionreplegal',
                                'identificacionreplegal',
                                'estado'
                            );
                            $creoprop = 'no';
                            if (isset($data["propietarios"]) && !empty($data["propietarios"])) {
                                if ($creoprop == 'no') {
                                    $arrValores = array(
                                        "'" . $data["matricula"] . "'",
                                        "'" . $data["propietarios"][1]["camarapropietario"] . "'",
                                        "'" . $data["propietarios"][1]["matriculapropietario"] . "'",
                                        "'0'", // Unico
                                        "'" . $data["propietarios"][1]["idtipoidentificacionpropietario"] . "'",
                                        "'" . $data["propietarios"][1]["identificacionpropietario"] . "'",
                                        "'" . $data["propietarios"][1]["nitpropietario"] . "'",
                                        "'" . addslashes($data["propietarios"][1]["nombrepropietario"]) . "'",
                                        "'" . addslashes($data["propietarios"][1]["ape1propietario"]) . "'",
                                        "'" . addslashes($data["propietarios"][1]["ape2propietario"]) . "'",
                                        "'" . addslashes($data["propietarios"][1]["nom1propietario"]) . "'",
                                        "'" . addslashes($data["propietarios"][1]["nom2propietario"]) . "'",
                                        "'" . addslashes($data["propietarios"][1]["direccionpropietario"]) . "'",
                                        "'" . $data["propietarios"][1]["municipiopropietario"] . "'",
                                        "'" . $data["propietarios"][1]["telefonopropietario"] . "'",
                                        "'" . $data["propietarios"][1]["telefono2propietario"] . "'",
                                        "'" . $data["propietarios"][1]["celularpropietario"] . "'",
                                        "''",
                                        "'" . addslashes($data["propietarios"][1]["direccionnotpropietario"]) . "'",
                                        "'" . addslashes($data["propietarios"][1]["municipionotpropietario"]) . "'",
                                        "''",
                                        "''",
                                        "''",
                                        "''",
                                        "''",
                                        "''",
                                        "'V'"
                                    );
                                    insertarRegistrosMysqliApi($mysqli, 'mreg_est_propietarios', $arrCampos, $arrValores);
                                    $creoprop = 'si';
                                }
                            }
                            if ($creoprop == 'no') {
                                $arrValores = array(
                                    "'" . $data["matricula"] . "'",
                                    "'" . $camPropietario . "'",
                                    "'" . $matPropietario . "'",
                                    "'0'", // Unico
                                    "'" . $tidePropietario . "'",
                                    "'" . $idePropietario . "'",
                                    "''",
                                    "'" . addslashes($razPropietario) . "'",
                                    "''",
                                    "''",
                                    "''",
                                    "''",
                                    "''",
                                    "''",
                                    "''",
                                    "''",
                                    "''",
                                    "''",
                                    "''",
                                    "''",
                                    "''",
                                    "''",
                                    "''",
                                    "''",
                                    "''",
                                    "''",
                                    "'V'"
                                );
                                insertarRegistrosMysqliApi($mysqli, 'mreg_est_propietarios', $arrCampos, $arrValores);
                            }
                        }
                    }
                    $actualizoFormularios = 'si';
                }
            }
        }
        \logApi::general2($nameLog, $idSolicitudPago, \funcionesGenerales::utf8_encode('actualizo datos cuando hay formularios en caso matriculas'));

        // ************************************************************************************************************** //
        // ACTUALIZA EL FORMULARIO
        // - Con la existencia de formularios
        // - Renovaciones
        // ************************************************************************************************************** //
        // 2017-12-16: JINT
        // Se adiciona control para ley 1780, código de policia alto impacto y codigo de policía multas
        // ************************************************************************************************************** //
        // ************************************************************************************************************** //
        // 2020-03-23: JINT
        // Se elimina el control de alto impacto.  
        // En caso de alto impacto, no actualiza ni dirección, ni nactividades económicas
        // ************************************************************************************************************** //
        // ************************************************************************************************************** //
        // 2021-02-01: JINT
        // Se incluye validación del campo registroinmediato en el proceso de renovación
        // ************************************************************************************************************** //
        $formulariosactualizados = 0;
        if (
                $_SESSION["tramite"]["multadoponal"] != 'S' &&
                $_SESSION["tramite"]["multadoponal"] != 'L' &&
                // $_SESSION["tramite"]["controlactividadaltoimpacto"] != 'S' && 
                ($_SESSION["tramite"]["cumplorequisitosbenley1780"] != 'S' ||
                $_SESSION["tramite"]["mantengorequisitosbenley1780"] != 'S' ||
                $_SESSION["tramite"]["renunciobeneficiosley1780"] != 'N')
        ) {

            if ($esRenovacion == 'si' && $registroInmediato == 'si' && $grupoServicios != 'RUE') {
                $arregloForms1 = array();
                foreach ($arregloForms as $x) {
                    if ($x["datos"]["organizacion"] == '01' || ($x["datos"]["organizacion"] > '02' && $x["datos"]["categoria"] == '1')) {
                        $arregloForms1[] = $x;
                    }
                }
                foreach ($arregloForms as $x) {
                    if ($x["datos"]["organizacion"] == '02' || ($x["datos"]["organizacion"] > '02' && ($x["datos"]["categoria"] == '2' || $x["datos"]["categoria"] == '3'))) {
                        if ($x["datos"]["cc"] == '' || $x["datos"]["cc"] == CODIGO_EMPRESA) {
                            $arregloForms1[] = $x;
                        }
                    }
                }

                //
                $actualizoFormularios = 'no';
                foreach ($arregloForms1 as $x) {
                    $x["datos"]["estadodatosmatricula"] = '6';
                    // if (substr(strtoupper($_SESSION["tramite"]["reliquidacion"]), 0, 1) != 'S') {
                    if ($fecharenovacion == '') {
                        $fecharenovacion = date("Ymd");
                    }
                    $x["datos"]["fecharenovacion"] = $fecharenovacion;
                    if ($x["datos"]["organizacion"] == '01' || ($x["datos"]["organizacion"] > '02' && $x["datos"]["categoria"] == '1')) {
                        $x["datos"]["ingresostamanoempresarial"] = $x["datos"]["ingope"];
                        $x["datos"]["anodatostamanoempresarial"] = $x["datos"]["ultanoren"];
                        $x["datos"]["fechadatostamanoempresarial"] = $x["datos"]["fecharenovacion"];
                    }
                    // }
                    if ($x["datos"]["organizacion"] == '01' || ($x["datos"]["organizacion"] > '02' && $x["datos"]["categoria"] == '1')) {
                        $x["datos"]["art4"] = $benart7;
                        $x["datos"]["art7"] = $benart7;
                        $x["datos"]["benley1780"] = $benley1780;
                        if ($_SESSION["tramite"]["cumplorequisitosbenley1780"] != '') {
                            $x["datos"]["cumplerequisitos1780"] = $_SESSION["tramite"]["cumplorequisitosbenley1780"];
                        }
                        if ($_SESSION["tramite"]["mantengorequisitosbenley1780"] != '') {
                            $x["datos"]["cumplerequisitos1780primren"] = $_SESSION["tramite"]["mantengorequisitosbenley1780"];
                        }
                        if ($_SESSION["tramite"]["renunciobeneficiosley1780"] != '') {
                            $x["datos"]["renunciabeneficios1780"] = $_SESSION["tramite"]["renunciobeneficiosley1780"];
                        }
                    }
                    if (trim($x["datos"]["versionciiu"]) == '') {
                        $x["datos"]["versionciiu"] = '0';
                    }
                    $x["xml"] = \funcionesRegistrales::serializarExpedienteMatricula($mysqli, $_SESSION["tramite"]["numerorecuperacion"], $x["datos"]);

                    if (substr(strtoupper($_SESSION["tramite"]["reliquidacion"]), 0, 1) != 'S') {
                        if (substr($x["datos"]["fecharenovacion"], 0, 4) != date("Y")) {
                            if (trim($fecharenovacion) != '') {
                                $x["datos"]["fecharenovacion"] = $fecharenovacion;
                            } else {
                                $x["datos"]["fecharenovacion"] = date("Ymd");
                            }
                        }
                    }
                    foreach ($arrMatsRens as $m => $a) {
                        if (trim($a) != '') {
                            if ($m == $x["datos"]["matricula"]) {
                                $x["datos"]["ultanoren"] = $a;
                                if ($x["datos"]["organizacion"] == '01' || ($x["datos"]["organizacion"] > '02' && $x["datos"]["categoria"] == '1')) {
                                    $x["datos"]["anodatostamanoempresarial"] = $a;
                                }
                            }
                        }
                    }
                    if (substr($x["datos"]["matricula"], 0, 1) == 'S') {
                        $x["datos"]["estadomatricula"] = 'IA';
                    } else {
                        $x["datos"]["estadomatricula"] = 'MA';
                    }
                    $x["datos"]["estadodatosmatricula"] = '6';
                    if ($_SESSION["tramite"]["controlactividadaltoimpacto"] == 'S') {
                        $x["datos"]["estadodatosmatricula"] = '4';
                    }
                    $x["datos"]["art4"] = $benart7;
                    $x["datos"]["art7"] = $benart7;
                    $x["datos"]["benley1780"] = $benley1780;
                    if ($_SESSION["tramite"]["cumplorequisitosbenley1780"] != '') {
                        $x["datos"]["cumplerequisitos1780"] = $_SESSION["tramite"]["cumplorequisitosbenley1780"];
                    }
                    if ($_SESSION["tramite"]["mantengorequisitosbenley1780"] != '') {
                        $x["datos"]["cumplerequisitos1780primren"] = $_SESSION["tramite"]["mantengorequisitosbenley1780"];
                    }
                    if ($_SESSION["tramite"]["renunciobeneficiosley1780"] != '') {
                        $x["datos"]["renunciabeneficios1780"] = $_SESSION["tramite"]["renunciobeneficiosley1780"];
                    }
                    if (trim($x["datos"]["versionciiu"]) == '') {
                        $x["datos"]["versionciiu"] = '0';
                    }

                    $x["xml"] = \funcionesRegistrales::serializarExpedienteMatricula($mysqli, $_SESSION["tramite"]["numerorecuperacion"], $x["datos"]);

                    if ($_SESSION["generales"]["cajero"] == 'USUPUBXX') {
                        $_SESSION["generales"]["idcodigosirepcaja"] = 'WWW';
                    }
                    \logApi::general2($nameLog, $_SESSION["tramite"]["idliquidacion"], \funcionesGenerales::utf8_encode('Re-Actualizara formulario de la renovacion ' . $x["datos"]["matricula"] . ': ' . $x["xml"]));
                    if (!isset($_SESSION["tramite"]["tipomatricula"])) {
                        $_SESSION["tramite"]["tipomatricula"] = '';
                    }

                    //  
                    $data = $x["datos"];
                    $formulariosactualizados++;

                    // Actualiza mreg_est_inscritos
                    if ($_SESSION["tramite"]["controlactividadaltoimpacto"] != 'S') {
                        $respuestaSii = \funcionesRegistrales::actualizarMregEstInscritos($mysqli, $data, $_SESSION["generales"]["codigobarras1"], $_SESSION["tramite"]["tipotramite"], $_SESSION["tramite"]["numerorecibo"]);
                    }
                    if ($_SESSION["tramite"]["controlactividadaltoimpacto"] == 'S') {
                        $respuestaSii = \funcionesRegistrales::actualizarMregEstInscritos($mysqli, $data, $_SESSION["generales"]["codigobarras1"], $_SESSION["tramite"]["tipotramite"], $_SESSION["tramite"]["numerorecibo"], 'si');
                    }

                    //
                    if ($respuestaSii == false) {
                        \logApi::general2($nameLog, $_SESSION["tramite"]["idliquidacion"], 'Error actualizando formulario Matricula en SII (mreg_est_inscritos) ' . $x["datos"]["matricula"] . ' : ' . str_replace("'", "", $_SESSION["generales"]["mensajeerror"]));
                        actualizarLogMysqliApi($mysqli, '502', $_SESSION["generales"]["codigousuario"], 'asentarReciboRegistro', '', '', '', str_replace("'", "", $respuestaSii["msgError"]), $x["datos"]["matricula"], '', '', $_SESSION["tramite"]["idliquidacion"]);
                        $txterrores .= 'Matricula ' . $x["datos"]["matricula"] . ', ' . $respuestaSii["msgError"] . '\n\n';
                        $okProcesoTerminado = 'no';
                    }

                    //
                    if ($data["organizacion"] == '02') {
                        if ($arrTipoTramite["asignamatricula"] == 'si') {

                            $arrCampos = array(
                                'matricula',
                                'codigocamara',
                                'matriculapropietario',
                                'tipopropiedad',
                                'tipoidentificacion',
                                'identificacion',
                                'nit',
                                'razonsocial',
                                'apellido1',
                                'apellido2',
                                'nombre1',
                                'nombre2',
                                'dircom',
                                'muncom',
                                'telcom1',
                                'telcom2',
                                'telcom3',
                                'emailcom',
                                'dirnot',
                                'munnot',
                                'telnot1',
                                'telnot2',
                                'telnot3',
                                'emailnot',
                                'estado'
                            );
                            $arrValores = array(
                                "'" . $data["matricula"] . "'",
                                "'" . $camPropietario . "'",
                                "'" . $matPropietario . "'",
                                "'0'", // Unico
                                "'" . $tidePropietario . "'",
                                "'" . $idePropietario . "'",
                                "''",
                                "'" . addslashes($razPropietario) . "'",
                                "''",
                                "''",
                                "''",
                                "''",
                                "''",
                                "''",
                                "''",
                                "''",
                                "''",
                                "''",
                                "''",
                                "''",
                                "''",
                                "''",
                                "''",
                                "''",
                                "'V'"
                            );
                            insertarRegistrosMysqliApi($mysqli, 'mreg_est_propietarios', $arrCampos, $arrValores);
                        }
                    }
                    $actualizoFormularios = 'si';
                }
            }
        }
        \logApi::general2($nameLog, $idSolicitudPago, \funcionesGenerales::utf8_encode('actualizo datos cuando hay formularios en caso de renovacion y/o matriculas'));

        // ************************************************************************************************************** //
        // ACTUALIZA EL FORMULARIO
        // - Mutacion Direccion
        // - Mutacion Actividad
        // - Cambio de Nombre
        // - Mutación general
        // ************************************************************************************************************** //
        // 2017-12-16: JINT
        // Se adiciona control para ley 1780, código de policia alto impacto y codigo de policía multas
        // ************************************************************************************************************** //
        if (
                $_SESSION["tramite"]["multadoponal"] != 'S' &&
                $_SESSION["tramite"]["multadoponal"] != 'L' &&
                $_SESSION["tramite"]["controlactividadaltoimpacto"] != 'S' && ($_SESSION["tramite"]["cumplorequisitosbenley1780"] != 'S' ||
                $_SESSION["tramite"]["mantengorequisitosbenley1780"] != 'S' ||
                $_SESSION["tramite"]["renunciobeneficiosley1780"] != 'N')
        ) {

            if ($esMutacion == 'si' && $grupoServicios != 'RUE' && $asientoautomaticomutaciones == 'si') {
                $actualizoFormularios = 'no';
                foreach ($arregloForms as $x) {
                    $datx = \funcionesRegistrales::retornarExpedienteMercantil($mysqli, $x["datos"]["matricula"], '', '', '', 'si', 'N');

                    //
                    $txtSello = array();
                    //
                    $actpersonal = 'no';
                    if ($_SESSION["tramite"]["tipotramite"] == 'mutacionregmer' || $_SESSION["tramite"]["tipotramite"] == 'mutacionesadl') {
                        if ($datx["nombre"] != $x["datos"]["nombre"]) {
                            $nomant = $datx["nombre"];
                            $datx["nuevonombre"] = $x["datos"]["nombre"];
                        }

                        if ($datx["dircom"] != $x["datos"]["dircom"]) {
                            $datx["dircom"] = $x["datos"]["dircom"];
                        }

                        if ($datx["muncom"] != $x["datos"]["muncom"]) {
                            $datx["muncom"] = $x["datos"]["muncom"];
                        }

                        if ($datx["telcom1"] != $x["datos"]["telcom1"]) {
                            $datx["telcom1"] = $x["datos"]["telcom1"];
                        }

                        if ($datx["telcom2"] != $x["datos"]["telcom2"]) {
                            $datx["telcom2"] = $x["datos"]["telcom2"];
                        }

                        if ($datx["celcom"] != $x["datos"]["celcom"]) {
                            $datx["celcom"] = $x["datos"]["celcom"];
                        }

                        if ($datx["emailcom"] != $x["datos"]["emailcom"]) {
                            $datx["emailcom"] = $x["datos"]["emailcom"];
                        }

                        if ($datx["numpredial"] != $x["datos"]["numpredial"]) {
                            $datx["numpredial"] = $x["datos"]["numpredial"];
                        }

                        if ($datx["ctrubi"] != $x["datos"]["ctrubi"]) {
                            $datx["ctrubi"] = $x["datos"]["ctrubi"];
                        }

                        if ($datx["barriocom"] != $x["datos"]["barriocom"]) {
                            $datx["barriocom"] = $x["datos"]["barriocom"];
                        }

                        if ($datx["codigozonacom"] != $x["datos"]["codigozonacom"]) {
                            $datx["codigozonacom"] = $x["datos"]["codigozonacom"];
                        }

                        if ($datx["codigopostalcom"] != $x["datos"]["codigopostalcom"]) {
                            $datx["codigopostalcom"] = $x["datos"]["codigopostalcom"];
                        }

                        if ($datx["paicom"] != $x["datos"]["paicom"]) {
                            $datx["paicom"] = $x["datos"]["paicom"];
                        }

                        if ($datx["urlcom"] != $x["datos"]["urlcom"]) {
                            $datx["urlcom"] = $x["datos"]["urlcom"];
                        }

                        if ($datx["dirnot"] != $x["datos"]["dirnot"]) {
                            $datx["dirnot"] = $x["datos"]["dirnot"];
                        }

                        if ($datx["munnot"] != $x["datos"]["munnot"]) {
                            $datx["munnot"] = $x["datos"]["munnot"];
                        }

                        if ($datx["telnot"] != $x["datos"]["telnot"]) {
                            $datx["telnot"] = $x["datos"]["telnot"];
                        }

                        if ($datx["telnot2"] != $x["datos"]["telnot2"]) {
                            $datx["telnot2"] = $x["datos"]["telnot2"];
                        }

                        if ($datx["celnot"] != $x["datos"]["celnot"]) {
                            $datx["celnot"] = $x["datos"]["celnot"];
                        }

                        if ($datx["emailnot"] != $x["datos"]["emailnot"]) {
                            $datx["emailnot"] = $x["datos"]["emailnot"];
                        }

                        if ($datx["barrionot"] != $x["datos"]["emailnot"]) {
                            $datx["barrionot"] = $x["datos"]["barrionot"];
                        }

                        if ($datx["codigozonanot"] != $x["datos"]["codigozonanot"]) {
                            $datx["codigozonanot"] = $x["datos"]["codigozonanot"];
                        }

                        if ($datx["codigopostalnot"] != $x["datos"]["codigopostalnot"]) {
                            $datx["codigopostalnot"] = $x["datos"]["codigopostalnot"];
                        }

                        if ($datx["painot"] != $x["datos"]["painot"]) {
                            $datx["painot"] = $x["datos"]["painot"];
                        }

                        if ($datx["urlnot"] != $x["datos"]["urlnot"]) {
                            $datx["urlnot"] = $x["datos"]["urlnot"];
                        }

                        if ($datx["tiposedeadm"] != $x["datos"]["tiposedeadm"]) {
                            $datx["tiposedeadm"] = $x["datos"]["tiposedeadm"];
                        }

                        if ($datx["ctrmennot"] != $x["datos"]["ctrmennot"]) {
                            $datx["ctrmennot"] = $x["datos"]["ctrmennot"];
                        }

                        if ($datx["ciius"][1] != $x["datos"]["ciius"][1]) {
                            $datx["ciius"][1] = $x["datos"]["ciius"][1];
                        }

                        if ($datx["ciius"][2] != $x["datos"]["ciius"][2]) {
                            $datx["ciius"][2] = $x["datos"]["ciius"][2];
                        }

                        if ($datx["ciius"][3] != $x["datos"]["ciius"][3]) {
                            $datx["ciius"][3] = $x["datos"]["ciius"][3];
                        }

                        if ($datx["ciius"][4] != $x["datos"]["ciius"][4]) {
                            $datx["ciius"][4] = $x["datos"]["ciius"][4];
                        }

                        if ($datx["desactiv"] != $x["datos"]["desactiv"]) {
                            $datx["desactiv"] = $x["datos"]["desactiv"];
                        }

                        if ($datx["organizacion"] == '01' || ($datx["organizacion"] > '02' && $datx["categoria"] == '1')) {
                            if ($datx["feciniact1"] != $x["datos"]["feciniact1"]) {
                                $datx["feciniact1"] = $x["datos"]["feciniact1"];
                            }
                            if ($datx["feciniact2"] != $x["datos"]["feciniact2"]) {
                                $datx["feciniact2"] = $x["datos"]["feciniact2"];
                            }
                        }

                        if ($datx["personal"] != $x["datos"]["personal"]) {
                            $datx["personal"] = $x["datos"]["personal"];
                            $actpersonal = 'si';
                        }

                        if ($datx["cantidadmujeres"] != $x["datos"]["cantidadmujeres"]) {
                            $datx["cantidadmujeres"] = $x["datos"]["cantidadmujeres"];
                        }

                        if ($datx["cantidadmujerescargosdirectivos"] != $x["datos"]["cantidadmujerescargosdirectivos"]) {
                            $datx["cantidadmujerescargosdirectivos"] = $x["datos"]["cantidadmujerescargosdirectivos"];
                        }

                        if ($datx["ciiutamanoempresarial"] != $x["datos"]["ciiutamanoempresarial"]) {
                            $datx["ciiutamanoempresarial"] = $x["datos"]["ciiutamanoempresarial"];
                        }
                    }

                    //
                    if ($_SESSION["tramite"]["tipotramite"] == 'mutaciondireccion') {
                        if ($_SESSION["tramite"]["modcom"] == 'S') {
                            $datx["dircom"] = $x["datos"]["dircom"];
                            $datx["telcom1"] = $x["datos"]["telcom1"];
                            $datx["telcom2"] = $x["datos"]["telcom2"];
                            $datx["celcom"] = $x["datos"]["celcom"];
                            $datx["faxcom"] = $x["datos"]["faxcom"];
                            $datx["barriocom"] = $x["datos"]["barriocom"];
                            $datx["muncom"] = $x["datos"]["muncom"];
                            $datx["emailcom"] = $x["datos"]["emailcom"];
                            // $datx["emailcom2"] = $x["datos"]["emailcom2"];
                            // $datx["emailcom3"] = $x["datos"]["emailcom3"];
                            $datx["numpredial"] = $x["datos"]["numpredial"];
                        }

                        //
                        if ($_SESSION["tramite"]["modnot"] == 'S') {
                            $datx["dirnot"] = $x["datos"]["dirnot"];
                            $datx["telnot"] = $x["datos"]["telnot"];
                            $datx["telnot2"] = $x["datos"]["telnot2"];
                            $datx["celnot"] = $x["datos"]["celnot"];
                            $datx["faxnot"] = $x["datos"]["faxnot"];
                            $datx["barrionot"] = $x["datos"]["barrionot"];
                            $datx["munnot"] = $x["datos"]["munnot"];
                            $datx["emailnot"] = $x["datos"]["emailnot"];
                        }
                    }

                    //
                    if ($_SESSION["tramite"]["tipotramite"] == 'mutacionactividad') {
                        if ($_SESSION["tramite"]["modciiu"] == 'S') {
                            $datx["ciius"][1] = $x["datos"]["ciius"][1];
                            $datx["ciius"][2] = $x["datos"]["ciius"][2];
                            $datx["ciius"][3] = $x["datos"]["ciius"][3];
                            $datx["ciius"][4] = $x["datos"]["ciius"][4];
                            $datx["feciniact1"] = $x["datos"]["feciniact1"];
                            $datx["feciniact2"] = $x["datos"]["feciniact2"];
                            $datx["desactiv"] = $x["datos"]["desactiv"];
                            if (trim($x["datos"]["versionciiu"]) == '') {
                                $x["datos"]["versionciiu"] = '0';
                            }
                            $datx["versionciiu"] = $x["datos"]["versionciiu"];
                        }
                    }

                    //
                    if ($_SESSION["tramite"]["tipotramite"] == 'mutacionnombre') {
                        if ($_SESSION["tramite"]["modnombre"] == 'S') {
                            $datx["nuevonombre"] = $x["datos"]["nuevonombre"];
                        }
                    }

                    //
                    if ($_SESSION["generales"]["cajero"] == 'USUPUBXX') {
                        $_SESSION["generales"]["idcodigosirepcaja"] = 'WWW';
                    }

                    //
                    if (!isset($_SESSION["tramite"]["tipomatricula"])) {
                        $_SESSION["tramite"]["tipomatricula"] = '';
                    }

                    //
                    $respuestaSii = \funcionesRegistrales::actualizarMregEstInscritos($mysqli, $datx, $_SESSION["generales"]["codigobarras1"], $_SESSION["tramite"]["tipotramite"], $_SESSION["tramite"]["numerorecibo"]);
                    if ($respuestaSii === false) {
                        \logApi::general2($nameLog, $_SESSION["tramite"]["idliquidacion"], 'Error actualizando formulario Matricula en mreg_est_inscritos del SII' . $datx["matricula"] . ' : ' . str_replace("'", "", $_SESSION["generales"]["mensajeerror"]));
                        actualizarLogMysqliApi($mysqli, '502', $_SESSION["generales"]["codigousuario"], 'asentarReciboRegistro', '', '', '', str_replace("'", "", $respuestaSii["msgError"]), $datx["matricula"], '', '', $_SESSION["tramite"]["idliquidacion"]);
                        $txterrores .= 'Matricula ' . $datx["matricula"] . ', ' . $respuestaSii["msgError"] . '\n\n';
                        $okProcesoTerminado = 'no';
                    }
                    if ($actpersonal == 'si') {
                        if ($datx["organizacion"] == '01' || ($datx["organizacion"] > '02' && $datx["categoria"] == '1')) {
                            $fin = retornarRegistroMysqliApi($mysqli, 'mreg_est_financiera', "matricula='" . $datx["matricula"] . "'", '*', 'U');
                            if ($fin && !empty($fin)) {
                                $fin["personal"] = $datx["personal"];
                                $fin["fechadatos"] = date("Ymd");
                                \funcionesRegistrales::actualizarMregEstInformacionFinanciera($mysqli, $fin, $_SESSION["generales"]["codigobarras1"], $_SESSION["tramite"]["tipotramite"], $_SESSION["tramite"]["numerorecibo"]);
                            }
                        } else {
                            $fin = retornarRegistroMysqliApi($mysqli, 'mreg_est_financiera', "matricula='" . $datx["matricula"] . "'", '*', 'U');
                            if ($fin && !empty($fin)) {
                                $fin["personal"] = $datx["personal"];
                                $fin["fechadatos"] = date("Ymd");
                                \funcionesRegistrales::actualizarMregEstInformacionFinancieraEstablecimientos($mysqli, $fin, $_SESSION["generales"]["codigobarras1"], $_SESSION["tramite"]["tipotramite"], $_SESSION["tramite"]["numerorecibo"]);
                            }
                        }
                    }
                }
            }
        }
        \logApi::general2($nameLog, $idSolicitudPago, \funcionesGenerales::utf8_encode('actualizo datos cuando hay formularios en caso de mutacion'));

        // ************************************************************************************************************** //
        // En caso de matrículas
        // Sin formularios
        // ************************************************************************************************************** //
        // 2017-12-16: JINT
        // Se adiciona control para ley 1780, código de policia alto impacto y codigo de policía multas
        if (
                $_SESSION["tramite"]["multadoponal"] != 'S' &&
                $_SESSION["tramite"]["multadoponal"] != 'L' &&
                $_SESSION["tramite"]["controlactividadaltoimpacto"] != 'S' && ($_SESSION["tramite"]["cumplorequisitosbenley1780"] != 'S' ||
                $_SESSION["tramite"]["mantengorequisitosbenley1780"] != 'S' ||
                $_SESSION["tramite"]["renunciobeneficiosley1780"] != 'N')
        ) {

            if ($asignaMatricula == 'si' && $registroInmediato == 'si' && $grupoServicios != 'RUE') {
                if ($actualizoFormularios == 'no') {
                    foreach ($_SESSION["tramite"]["liquidacion"] as $x) {
                        $sigrabar = 'no';
                        if (substr($x["idservicio"], 0, 6) == '010201') {
                            $datos = \funcionesGenerales::desserializarExpedienteMatricula($mysqli, '');
                            $datos["estadomatricula"] = 'MA';
                            $datos["estadodatosmatricula"] = '4';
                            $datos["fechamatricula"] = date("Ymd");
                            $datos["fecharenovacion"] = date("Ymd");
                            $datos["ultanoren"] = date("Y");
                            if ($x["idservicio"] == '01020101') {
                                if (ltrim($matPnat, "0") != '') {
                                    $datos["matricula"] = ltrim($matPnat, "0");
                                    $datos["organizacion"] = '01';
                                    $datos["categoria"] = '';
                                    $datos["nombre"] = $nomnat;
                                    $sigrabar = 'si';
                                }
                            }
                            if ($x["idservicio"] == '01020102' || $x["idservicio"] == '01020103') {
                                if (ltrim($matEst, "0") != '') {
                                    $datos["matricula"] = ltrim($matEst, "0");
                                    $datos["organizacion"] = '02';
                                    $datos["categoria"] = '';
                                    $datos["nombre"] = $nomest;
                                    $sigrabar = 'si';
                                }
                            }
                            if ($x["idservicio"] == '01020104' || $x["idservicio"] == '01020105') {
                                if (ltrim($matAge, "0") != '') {
                                    $datos["matricula"] = ltrim($matAge, "0");
                                    $datos["organizacion"] = $_SESSION["tramite"]["orgpjur"];
                                    $datos["categoria"] = '3';
                                    $datos["nombre"] = $nomage;
                                    $sigrabar = 'si';
                                }
                                if (ltrim($matSuc, "0") != '') {
                                    $datos["matricula"] = ltrim($matSuc, "0");
                                    $datos["organizacion"] = $_SESSION["tramite"]["orgpjur"];
                                    $datos["categoria"] = '2';
                                    $datos["nombre"] = $nomsuc;
                                    $sigrabar = 'si';
                                }
                            }
                            if ($x["idservicio"] == '01020108') {
                                if (ltrim($matPjur, "0") != '') {
                                    $datos["matricula"] = ltrim($matPjur, "0");
                                    $datos["organizacion"] = $_SESSION["tramite"]["orgpjur"];
                                    $datos["categoria"] = '1';
                                    $datos["nombre"] = $nomjur;
                                    $sigrabar = 'si';
                                }
                                if ($sigrabar == 'si') {
                                    if ($arrTipoTramite["escambiodomicilio"] == 'si' || $_SESSION["tramite"]["tipomatricula"] == 'cambidom') {
                                        $datos["fecharenovacion"] = $_SESSION["tramite"]["fecrencambidom"];
                                        $datos["ultanoren"] = $_SESSION["tramite"]["ultanoren"];
                                        $datos["muncom"] = $_SESSION["tramite"]["munpnat"];
                                        $datos["fecmatant"] = $_SESSION["tramite"]["fecmatcambidom"];
                                        $datos["fecrenant"] = $_SESSION["tramite"]["fecrencambidom"];
                                        $datos["ultanorenant"] = $_SESSION["tramite"]["ultanoren"];
                                        $datos["camant"] = $_SESSION["tramite"]["camaracambidom"];
                                        $datos["matant"] = $_SESSION["tramite"]["matriculacambidom"];
                                        $datos["munant"] = $_SESSION["tramite"]["municipiocambidom"];
                                        if (isset($_SESSION["tramite"]["benart7cambidom"])) {
                                            $datos["benart7ant"] = $_SESSION["tramite"]["benart7cambidom"];
                                        }
                                        if (isset($_SESSION["tramite"]["benart1780cambidom"])) {
                                            $datos["benley1780ant"] = $_SESSION["tramite"]["benart1780cambidom"];
                                        }
                                    }
                                }

                                // Actualizar mreg_est_inscritos
                                if ($sigrabar == 'si') {
                                    $respuestaSii = \funcionesRegistrales::actualizarMregEstInscritos($mysqli, $datos, $_SESSION["generales"]["codigobarras1"], $_SESSION["tramite"]["tipotramite"], $_SESSION["tramite"]["numerorecibo"]);
                                    if ($respuestaSii === false) {
                                        \logApi::general2($nameLog, $_SESSION["tramite"]["idliquidacion"], 'Error actualizando formulario Matricula en mreg_est_inscritos del SII' . $datos["matricula"] . ' : ' . str_replace("'", "", $_SESSION["generales"]["mensajeerror"]));
                                        actualizarLogMysqliApi($mysqli, '502', $_SESSION["generales"]["codigousuario"], 'asentarReciboRegistro', '', '', '', str_replace("'", "", $respuestaSii["msgError"]), $datos["matricula"], '', '', $_SESSION["tramite"]["idliquidacion"]);
                                        $txterrores .= 'Matricula ' . $datx["matricula"] . ', ' . $respuestaSii["msgError"] . '\n\n';
                                        $okProcesoTerminado = 'no';
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        \logApi::general2($nameLog, $idSolicitudPago, \funcionesGenerales::utf8_encode('actualizo datos cuando no hay formularios en caso de matriculas'));

        // ************************************************************************************** //
        // 2017-12-16: JINT
        // Se adiciona control para ley 1780, código de policia alto impacto y codigo de policía multas
        // ************************************************************************************** //
        if (
                $_SESSION["tramite"]["multadoponal"] != 'S' &&
                $_SESSION["tramite"]["multadoponal"] != 'L' &&
                $_SESSION["tramite"]["controlactividadaltoimpacto"] != 'S' && ($_SESSION["tramite"]["cumplorequisitosbenley1780"] != 'S' ||
                $_SESSION["tramite"]["mantengorequisitosbenley1780"] != 'S' ||
                $_SESSION["tramite"]["renunciobeneficiosley1780"] != 'N')
        ) {

            if ($asignaMatricula == 'si' && $registroInmediato == 'si' && $grupoServicios != 'RUE') {
                if (ltrim($matPnat, "0") != '') {
                    \funcionesRegistrales::actualizarMregEstInscritosCampo($mysqli, ltrim($matPnat, "0"), 'numrecibo', $_SESSION["tramite"]["numerorecibo"], 'varchar', $_SESSION["generales"]["codigobarras1"], $_SESSION["tramite"]["tipotramite"], $_SESSION["tramite"]["numerorecibo"]);
                    \funcionesRegistrales::actualizarMregEstInscritosCampo($mysqli, ltrim($matPnat, "0"), 'codigobarras', $_SESSION["generales"]["codigobarras1"], 'varchar', $_SESSION["generales"]["codigobarras1"], $_SESSION["tramite"]["tipotramite"], $_SESSION["tramite"]["numerorecibo"]);
                }
                if (ltrim($matEst, "0") != '') {
                    \funcionesRegistrales::actualizarMregEstInscritosCampo($mysqli, ltrim($matEst, "0"), 'numrecibo', $_SESSION["tramite"]["numerorecibo"], 'varchar', $_SESSION["generales"]["codigobarras1"], $_SESSION["tramite"]["tipotramite"], $_SESSION["tramite"]["numerorecibo"]);
                    \funcionesRegistrales::actualizarMregEstInscritosCampo($mysqli, ltrim($matEst, "0"), 'codigobarras', $_SESSION["generales"]["codigobarras1"], 'varchar', $_SESSION["generales"]["codigobarras1"], $_SESSION["tramite"]["tipotramite"], $_SESSION["tramite"]["numerorecibo"]);
                }
                if (ltrim($matAge, "0") != '') {
                    \funcionesRegistrales::actualizarMregEstInscritosCampo($mysqli, ltrim($matAge, "0"), 'numrecibo', $_SESSION["tramite"]["numerorecibo"], 'varchar', $_SESSION["generales"]["codigobarras1"], $_SESSION["tramite"]["tipotramite"], $_SESSION["tramite"]["numerorecibo"]);
                    \funcionesRegistrales::actualizarMregEstInscritosCampo($mysqli, ltrim($matAge, "0"), 'codigobarras', $_SESSION["generales"]["codigobarras1"], 'varchar', $_SESSION["generales"]["codigobarras1"], $_SESSION["tramite"]["tipotramite"], $_SESSION["tramite"]["numerorecibo"]);
                }
                if (ltrim($matSuc, "0") != '') {
                    \funcionesRegistrales::actualizarMregEstInscritosCampo($mysqli, ltrim($matSuc, "0"), 'numrecibo', $_SESSION["tramite"]["numerorecibo"], 'varchar', $_SESSION["generales"]["codigobarras1"], $_SESSION["tramite"]["tipotramite"], $_SESSION["tramite"]["numerorecibo"]);
                    \funcionesRegistrales::actualizarMregEstInscritosCampo($mysqli, ltrim($matSuc, "0"), 'codigobarras', $_SESSION["generales"]["codigobarras1"], 'varchar', $_SESSION["generales"]["codigobarras1"], $_SESSION["tramite"]["tipotramite"], $_SESSION["tramite"]["numerorecibo"]);
                }
                if (ltrim($matPjur, "0") != '') {
                    \funcionesRegistrales::actualizarMregEstInscritosCampo($mysqli, ltrim($matPjur, "0"), 'numrecibo', $_SESSION["tramite"]["numerorecibo"], 'varchar', $_SESSION["generales"]["codigobarras1"], $_SESSION["tramite"]["tipotramite"], $_SESSION["tramite"]["numerorecibo"]);
                    \funcionesRegistrales::actualizarMregEstInscritosCampo($mysqli, ltrim($matPjur, "0"), 'codigobarras', $_SESSION["generales"]["codigobarras1"], 'varchar', $_SESSION["generales"]["codigobarras1"], $_SESSION["tramite"]["tipotramite"], $_SESSION["tramite"]["numerorecibo"]);
                }
                if (ltrim($matEsadl, "0") != '') {
                    \funcionesRegistrales::actualizarMregEstInscritosCampo($mysqli, ltrim($matEsadl, "0"), 'numrecibo', $_SESSION["tramite"]["numerorecibo"], 'varchar', $_SESSION["generales"]["codigobarras1"], $_SESSION["tramite"]["tipotramite"], $_SESSION["tramite"]["numerorecibo"]);
                    \funcionesRegistrales::actualizarMregEstInscritosCampo($mysqli, ltrim($matEsadl, "0"), 'codigobarras', $_SESSION["generales"]["codigobarras1"], 'varchar', $_SESSION["generales"]["codigobarras1"], $_SESSION["tramite"]["tipotramite"], $_SESSION["tramite"]["numerorecibo"]);
                }
            }
        }
        \logApi::general2($nameLog, $idSolicitudPago, \funcionesGenerales::utf8_encode('actualizo numero del recibo y codigo de barras al expediente  en caso de matriculas'));

        // ************************************************************************************************************** //
        // Si el estado final de los datos es diferente de vacio
        // Y el tipo de trámite no es renovación
        // ************************************************************************************************************** //
        if ($estadoFinalDatos != '' && $esRenovacion != 'si') {
            $arrMatsAct = array();
            $ix = 0;
            foreach ($_SESSION["tramite"]["liquidacion"] as $d) {
                $ix++;
                if (
                        trim($d["expediente"]) != '' &&
                        $d["expediente"] != 'NUEVANAT' &&
                        $d["expediente"] != 'NUEVAEST' &&
                        $d["expediente"] != 'NUEVAJUR' &&
                        $d["expediente"] != 'NUEVASUC' &&
                        $d["expediente"] != 'NUEVAAGE' &&
                        $d["expediente"] != 'NUEVAESA' &&
                        $d["expediente"] != 'NUEVACIV'
                ) {
                    if (!isset($arrMatsAct[$d["expediente"]])) {
                        $arrMatsAct[$d["expediente"]] = ltrim(trim($d["expediente"]), "0");
                    }
                }
            }

            // Actualiza el estado de los datos en SII si este está activado
            foreach ($arrMatsAct as $m) {
                // Actualiza mreg_est_inscritos
                \funcionesRegistrales::actualizarMregEstInscritosCampo($mysqli, $m, 'ctrestdatos', $estadoFinalDatos, 'varchar', $_SESSION["generales"]["codigobarras1"], $_SESSION["tramite"]["tipotramite"], $_SESSION["tramite"]["numerorecibo"]);
            }
        }
        \logApi::general2($nameLog, $idSolicitudPago, \funcionesGenerales::utf8_encode('actualizo estado de los datos en caso de matricula'));

        // 
        if (
                $_SESSION["tramite"]["idestado"] != '20' &&
                $_SESSION["tramite"]["idestado"] != '21' &&
                $_SESSION["tramite"]["idestado"] != '22' &&
                $_SESSION["tramite"]["idestado"] != '23' &&
                $_SESSION["tramite"]["idestado"] != '24'
        ) {
            \funcionesRegistrales::actualizarMregLiquidacionPagoElectronico($mysqli, $idSolicitudPago, $_SESSION["tramite"]["idestado"], trim($_SESSION["tramite"]["nombrecliente"] . ' ' . $_SESSION["tramite"]["apellidocliente"]), $_SESSION["tramite"]["idtipoidentificacioncliente"], $_SESSION["tramite"]["direccion"], $_SESSION["tramite"]["telefono"], $_SESSION["tramite"]["idmunicipio"], $_SESSION["tramite"]["email"], $_SESSION["tramite"]["identificacioncliente"], $_SESSION["tramite"]["pagoefectivo"], $_SESSION["tramite"]["pagocheque"], $_SESSION["tramite"]["pagovisa"], $_SESSION["tramite"]["pagoach"], $_SESSION["tramite"]["pagomastercard"], $_SESSION["tramite"]["pagoamerican"], $_SESSION["tramite"]["pagocredencial"], $_SESSION["tramite"]["pagodiners"], $_SESSION["tramite"]["pagotdebito"], $_SESSION["tramite"]["idcodban"], $_SESSION["tramite"]["numerocheque"], $_SESSION["tramite"]["numeroautorizacion"], $_SESSION["generales"]["cajero"], $result["numerooperacion"], $result["numerorecibo"], $result["fecharecibo"], $result["horarecibo"], $result["numerooperaciongob"], $result["numerorecibogob"], $result["fecharecibogob"], $result["horarecibogob"], $_SESSION["generales"]["codigobarras1"], $_SESSION["tramite"]["idfranquicia"], '', $_SESSION["tramite"]["idformapago"]);
        }
        \logApi::general2($nameLog, $idSolicitudPago, \funcionesGenerales::utf8_encode('actualizo datos de la forma de pago cuando sea pago electronico'));

        // ************************************************************************************************************** //
        // Arma el arreglo de las inscripciones que se deben hacer en los libros
        // En caso de matriculas
        // Por fuera del módulo de inscripción de actos y documentos
        // ************************************************************************************************************** //
        if ($asignaMatricula == 'si' && $registroInmediato == 'si') {
            // 2017-12-16: JINT
            // Se adiciona control para ley 1780, código de policia alto impacto y codigo de policía multas
            if (
                    $_SESSION["tramite"]["multadoponal"] != 'S' &&
                    $_SESSION["tramite"]["multadoponal"] != 'L' &&
                    $_SESSION["tramite"]["controlactividadaltoimpacto"] != 'S' && ($_SESSION["tramite"]["cumplorequisitosbenley1780"] != 'S' ||
                    $_SESSION["tramite"]["mantengorequisitosbenley1780"] != 'S' ||
                    $_SESSION["tramite"]["renunciobeneficiosley1780"] != 'N')
            ) {

                if ($matPnat != '') {
                    $xInsc++;
                    $xInscripciones[$xInsc]["tiporegistro"] = $arrTipoTramite["tiporegistro"];
                    $xInscripciones[$xInsc]["tiposello"] = '90.20.31';
                    $xInscripciones[$xInsc]["libro"] = 'RM15';
                    $xInscripciones[$xInsc]["numreg"] = '';
                    $xInscripciones[$xInsc]["organizacion"] = '01';
                    $xInscripciones[$xInsc]["categoria"] = '';
                    $xInscripciones[$xInsc]["filesello"] = '';

                    if ($_SESSION["tramite"]["tipomatricula"] == 'cambidom') {
                        $xInscripciones[$xInsc]["noticia"] = 'MATRICULA DE PERSONA NATURAL POR CAMBIO DE DOMICILIO';
                        $xInscripciones[$xInsc]["acto"] = '9990';
                    } else {
                        $xInscripciones[$xInsc]["noticia"] = 'MATRICULA DE PERSONA NATURAL';
                        $xInscripciones[$xInsc]["acto"] = '9991';
                    }
                    $xInscripciones[$xInsc]["matricula"] = $matPnat;
                    $xInscripciones[$xInsc]["proponente"] = '';
                    $xInscripciones[$xInsc]["tipodoc"] = '06';
                    $xInscripciones[$xInsc]["numdoc"] = 'N/A';
                    $xInscripciones[$xInsc]["origendoc"] = $txtOrigen;
                    $xInscripciones[$xInsc]["municipio"] = $_SESSION["tramite"]["municipiopagador"];
                    if ($_SESSION["tramite"]["tipomatricula"] == 'cambidom') {
                        $xInscripciones[$xInsc]["municipio"] = $_SESSION["tramite"]["municipiocambidom"];
                    }
                    $xInscripciones[$xInsc]["fecha"] = date("Ymd");
                    $xInscripciones[$xInsc]["hora"] = date("His");
                    if ($_SESSION["tramite"]["tipomatricula"] == 'cambidom') {
                        $xInscripciones[$xInsc]["fecha"] = $_SESSION["tramite"]["fecmatcambidom"];
                    }
                    $xInscripciones[$xInsc]["fechadoc"] = date("Ymd");
                    $xInscripciones[$xInsc]["nombre"] = $nomnat;
                    $xInscripciones[$xInsc]["tipoidentificacion"] = $tidePropietario;
                    $xInscripciones[$xInsc]["identificacion"] = $idePropietario;
                    $xInscripciones[$xInsc]["ope"] = $opeSecJur;
                    $xInscripciones[$xInsc]["bandeja"] = $arrTipoTramite["bandeja"];
                }

                if ($matPjur != '') {
                    // Acto al libro XV (9992)
                    $xInsc++;
                    $xInscripciones[$xInsc]["tiporegistro"] = $arrTipoTramite["tiporegistro"];
                    $xInscripciones[$xInsc]["tiposello"] = '90.20.31';
                    $xInscripciones[$xInsc]["libro"] = 'RM15';
                    $xInscripciones[$xInsc]["numreg"] = '';
                    $xInscripciones[$xInsc]["organizacion"] = $_SESSION["tramite"]["organizacion"];
                    $xInscripciones[$xInsc]["categoria"] = '1';
                    $xInscripciones[$xInsc]["filesello"] = '';
                    if ($_SESSION["tramite"]["tipomatricula"] == 'cambidom') {
                        $xInscripciones[$xInsc]["noticia"] = 'MATRICULA DE PERSONA PERSONA JURIDICA POR CAMBIO DE DOMICILIO';
                    } else {
                        $xInscripciones[$xInsc]["noticia"] = 'MATRICULA DE PERSONA PERSONA JURIDICA';
                    }
                    $xInscripciones[$xInsc]["matricula"] = $matPjur;
                    $xInscripciones[$xInsc]["proponente"] = '';
                    $xInscripciones[$xInsc]["tipodoc"] = $_SESSION["tramite"]["tipodoc"];
                    $xInscripciones[$xInsc]["numdoc"] = $_SESSION["tramite"]["numdoc"];
                    $xInscripciones[$xInsc]["origendoc"] = $_SESSION["tramite"]["origendoc"];
                    $xInscripciones[$xInsc]["municipio"] = $_SESSION["tramite"]["mundoc"];
                    if ($_SESSION["tramite"]["tipomatricula"] == 'cambidom') {
                        $xInscripciones[$xInsc]["municipio"] = $_SESSION["tramite"]["municipiocambidom"];
                    }
                    $xInscripciones[$xInsc]["acto"] = '9992';
                    $xInscripciones[$xInsc]["fecha"] = date("Ymd");
                    $xInscripciones[$xInsc]["hora"] = date("His");
                    if ($_SESSION["tramite"]["tipomatricula"] == 'cambidom') {
                        $xInscripciones[$xInsc]["fecha"] = $_SESSION["tramite"]["fecmatcambidom"];
                    }
                    $xInscripciones[$xInsc]["fechadoc"] = $_SESSION["tramite"]["fechadoc"];
                    $xInscripciones[$xInsc]["nombre"] = $nomjur;
                    $xInscripciones[$xInsc]["tipoidentificacion"] = $_SESSION["tramite"]["tipoiderepleg"];
                    $xInscripciones[$xInsc]["identificacion"] = $_SESSION["tramite"]["iderepleg"];

                    $xInscripciones[$xInsc]["ope"] = $opeSecJur;
                    $xInscripciones[$xInsc]["bandeja"] = $arrTipoTramite["bandeja"];

                    // En caso de constitucion - Acto de constitucion (0040)
                    $xInsc++;
                    $xInscripciones[$xInsc]["tiporegistro"] = $arrTipoTramite["tiporegistro"];
                    $xInscripciones[$xInsc]["tiposello"] = '90.20.31';
                    $xInscripciones[$xInsc]["libro"] = 'RM09';
                    switch ($_SESSION["tramite"]["organizacion"]) {
                        case "08":
                            $xInscripciones[$xInsc]["libro"] = 'RM06';
                            break;
                        case "09":
                            $xInscripciones[$xInsc]["libro"] = 'RM14';
                            break;
                        case "10":
                            $xInscripciones[$xInsc]["libro"] = 'RM13';
                            break;
                        default:
                            break;
                    }
                    /*
                      if ($xSiNat22 == 'S') {
                      $xInscripciones[$xInsc]["libro"] = 'RM22';
                      }
                     */
                    $xInscripciones[$xInsc]["numreg"] = '';
                    $xInscripciones[$xInsc]["organizacion"] = $_SESSION["tramite"]["organizacion"];
                    $xInscripciones[$xInsc]["categoria"] = '1';
                    $xInscripciones[$xInsc]["filesello"] = '';

                    if ($_SESSION["tramite"]["organizacion"] == '08') {
                        $xInscripciones[$xInsc]["noticia"] = 'APERTURA DE SUCURSAL DE SOCIEDAD EXTRANJERA EN COLOMBIA, DENOMINADA ' . $_SESSION["tramite"]["nombrepnat"];
                    } else {
                        $xInscripciones[$xInsc]["noticia"] = 'CONSTITUCION DE PERSONA JURIDICA DENOMINADA ' . $_SESSION["tramite"]["nombrepnat"];
                    }
                    $xInscripciones[$xInsc]["matricula"] = $matPjur;
                    $xInscripciones[$xInsc]["proponente"] = '';
                    $xInscripciones[$xInsc]["tipodoc"] = $_SESSION["tramite"]["tipodoc"];
                    $xInscripciones[$xInsc]["numdoc"] = $_SESSION["tramite"]["numdoc"];
                    $xInscripciones[$xInsc]["origendoc"] = $_SESSION["tramite"]["origendoc"];
                    $xInscripciones[$xInsc]["municipio"] = $_SESSION["tramite"]["mundoc"];
                    if ($_SESSION["tramite"]["tipomatricula"] == 'cambidom') {
                        $xInscripciones[$xInsc]["municipio"] = $_SESSION["tramite"]["municipiocambidom"];
                    }
                    $xInscripciones[$xInsc]["acto"] = '0040';
                    $xInscripciones[$xInsc]["fecha"] = date("Ymd");
                    $xInscripciones[$xInsc]["hora"] = date("His");
                    if ($_SESSION["tramite"]["tipomatricula"] == 'cambidom') {
                        $xInscripciones[$xInsc]["fecha"] = $_SESSION["tramite"]["fecmatcambidom"];
                    }
                    $xInscripciones[$xInsc]["fechadoc"] = $_SESSION["tramite"]["fechadoc"];
                    $xInscripciones[$xInsc]["nombre"] = $_SESSION["tramite"]["nombrepnat"];
                    $xInscripciones[$xInsc]["tipoidentificacion"] = $_SESSION["tramite"]["tipoiderepleg"];
                    $xInscripciones[$xInsc]["identificacion"] = $_SESSION["tramite"]["iderepleg"];

                    $xInscripciones[$xInsc]["ope"] = $opeSecJur;
                    $xInscripciones[$xInsc]["bandeja"] = $arrTipoTramite["bandeja"];

                    // En caso de constitucion - Acto de cambio de domicilio (0042)
                    if ($_SESSION["tramite"]["tipomatricula"] == 'cambidom') {
                        $xInsc++;
                        $xInscripciones[$xInsc]["tiporegistro"] = $arrTipoTramite["tiporegistro"];
                        $xInscripciones[$xInsc]["tiposello"] = '90.20.31';
                        $xInscripciones[$xInsc]["libro"] = 'RM09';
                        switch ($_SESSION["tramite"]["organizacion"]) {
                            case "08":
                                $xInscripciones[$xInsc]["libro"] = 'RM06';
                                break;
                            case "09":
                                $xInscripciones[$xInsc]["libro"] = 'RM14';
                                break;
                            case "10":
                                $xInscripciones[$xInsc]["libro"] = 'RM13';
                                break;
                            default: break;
                        }
                        /*
                          if ($xSiNat22 == 'S') {
                          $xInscripciones[$xInsc]["libro"] = 'RM22';
                          }
                         */
                        $xInscripciones[$xInsc]["numreg"] = '';
                        $xInscripciones[$xInsc]["organizacion"] = $_SESSION["tramite"]["organizacion"];
                        $xInscripciones[$xInsc]["categoria"] = '1';
                        $xInscripciones[$xInsc]["filesello"] = '';
                        $xInscripciones[$xInsc]["noticia"] = 'CONSTITUCION DE PERSONA JURIDICA POR CAMBIO DE DOMICILIO DENOMINADA' . $_SESSION["tramite"]["nombrepnat"];
                        $xInscripciones[$xInsc]["matricula"] = $matPjur;
                        $xInscripciones[$xInsc]["proponente"] = '';
                        $xInscripciones[$xInsc]["tipodoc"] = $_SESSION["tramite"]["tipodoc"];
                        $xInscripciones[$xInsc]["numdoc"] = $_SESSION["tramite"]["numdoc"];
                        $xInscripciones[$xInsc]["origendoc"] = $_SESSION["tramite"]["origendoc"];
                        $xInscripciones[$xInsc]["municipio"] = $_SESSION["tramite"]["mundoc"];
                        $xInscripciones[$xInsc]["municipio"] = $_SESSION["tramite"]["municipiocambidom"];
                        $xInscripciones[$xInsc]["acto"] = '0042';
                        $xInscripciones[$xInsc]["fecha"] = date("Ymd");
                        $xInscripciones[$xInsc]["hora"] = date("His");
                        $xInscripciones[$xInsc]["fechadoc"] = $_SESSION["tramite"]["fechadoc"];
                        $xInscripciones[$xInsc]["nombre"] = $_SESSION["tramite"]["nombrepnat"];
                        $xInscripciones[$xInsc]["tipoidentificacion"] = $_SESSION["tramite"]["tipoiderepleg"];
                        $xInscripciones[$xInsc]["identificacion"] = $_SESSION["tramite"]["iderepleg"];
                        $xInscripciones[$xInsc]["ope"] = $opeSecJur;
                        $xInscripciones[$xInsc]["bandeja"] = $arrTipoTramite["bandeja"];
                    }
                }

                if ($matEst != '') {
                    $xInsc++;
                    $xInscripciones[$xInsc]["tiporegistro"] = $arrTipoTramite["tiporegistro"];
                    $xInscripciones[$xInsc]["tiposello"] = '90.20.31';
                    $xInscripciones[$xInsc]["libro"] = 'RM15';
                    $xInscripciones[$xInsc]["numreg"] = '';
                    $xInscripciones[$xInsc]["organizacion"] = '02';
                    $xInscripciones[$xInsc]["categoria"] = '';
                    $xInscripciones[$xInsc]["filesello"] = '';
                    $xInscripciones[$xInsc]["noticia"] = 'MATRICULA DE ESTABLECIMIENTO DE COMERCIO DENOMINADO ' . $razEstablecimiento;
                    $xInscripciones[$xInsc]["matricula"] = $matEst;
                    $xInscripciones[$xInsc]["proponente"] = '';
                    $xInscripciones[$xInsc]["tipodoc"] = '06';
                    $xInscripciones[$xInsc]["numdoc"] = 'N/A';
                    $xInscripciones[$xInsc]["origendoc"] = 'EL COMERCIANTE';
                    $xInscripciones[$xInsc]["municipio"] = '';
                    $xInscripciones[$xInsc]["acto"] = '9993';
                    $xInscripciones[$xInsc]["fecha"] = date("Ymd");
                    $xInscripciones[$xInsc]["hora"] = date("His");
                    $xInscripciones[$xInsc]["fechadoc"] = date("Ymd");
                    $xInscripciones[$xInsc]["nombre"] = $nomest;
                    $xInscripciones[$xInsc]["tipoidentificacion"] = $tidePropietario;
                    $xInscripciones[$xInsc]["identificacion"] = $idePropietario;
                    $xInscripciones[$xInsc]["ope"] = $opeSecJur;
                    $xInscripciones[$xInsc]["bandeja"] = $arrTipoTramite["bandeja"];
                }

                if ($matSuc != '' || $matAge != '') {
                    // Acto de matricula en el libro XV
                    $xInsc++;
                    $xInscripciones[$xInsc]["tiporegistro"] = $arrTipoTramite["tiporegistro"];
                    $xInscripciones[$xInsc]["tiposello"] = '90.20.31';
                    $xInscripciones[$xInsc]["libro"] = 'RM15';
                    $xInscripciones[$xInsc]["numreg"] = '';
                    $xInscripciones[$xInsc]["organizacion"] = $_SESSION["tramite"]["organizacion"];
                    $xInscripciones[$xInsc]["categoria"] = $_SESSION["tramite"]["categoria"];
                    $xInscripciones[$xInsc]["filesello"] = '';
                    $xInscripciones[$xInsc]["noticia"] = 'MATRICULA DE SUCURSAL O AGENCIA DENOMINADA ' . trim($_SESSION["tramite"]["nombresuc"]) . trim($_SESSION["tramite"]["nombreage"]);
                    $xInscripciones[$xInsc]["matricula"] = ltrim($matSuc, 0) . ltrim($matAge, 0);
                    $xInscripciones[$xInsc]["proponente"] = '';
                    $xInscripciones[$xInsc]["tipodoc"] = $_SESSION["tramite"]["tipodoc"];
                    $xInscripciones[$xInsc]["numdoc"] = $_SESSION["tramite"]["numdoc"];
                    $xInscripciones[$xInsc]["origendoc"] = $_SESSION["tramite"]["origendoc"];
                    $xInscripciones[$xInsc]["municipio"] = $_SESSION["tramite"]["mundoc"];
                    $xInscripciones[$xInsc]["acto"] = '9994';
                    $xInscripciones[$xInsc]["fecha"] = date("Ymd");
                    $xInscripciones[$xInsc]["hora"] = date("His");
                    $xInscripciones[$xInsc]["fechadoc"] = $_SESSION["tramite"]["fechadoc"];
                    $xInscripciones[$xInsc]["nombre"] = trim($nomage . $nomsuc);
                    $xInscripciones[$xInsc]["tipoidentificacion"] = $tidePropietario;
                    $xInscripciones[$xInsc]["identificacion"] = $idePropietario;
                    $xInscripciones[$xInsc]["ope"] = $opeSecJur;
                    $xInscripciones[$xInsc]["bandeja"] = $arrTipoTramite["bandeja"];

                    // Acto de inscripcion en el libro VI por apertura de sucursal
                    $xInsc++;
                    $xInscripciones[$xInsc]["tiporegistro"] = $arrTipoTramite["tiporegistro"];
                    $xInscripciones[$xInsc]["tiposello"] = '90.20.31';
                    $xInscripciones[$xInsc]["libro"] = 'RM06';
                    $xInscripciones[$xInsc]["numreg"] = '';
                    $xInscripciones[$xInsc]["organizacion"] = $_SESSION["tramite"]["organizacion"];
                    $xInscripciones[$xInsc]["categoria"] = $_SESSION["tramite"]["categoria"];
                    $xInscripciones[$xInsc]["filesello"] = '';
                    $xInscripciones[$xInsc]["noticia"] = 'APERTURA DE SUCURSAL O AGENCIA DENOMINADA ' . trim($_SESSION["tramite"]["nombresuc"]) . trim($_SESSION["tramite"]["nombreage"]);
                    $xInscripciones[$xInsc]["matricula"] = ltrim($matSuc, 0) . ltrim($matAge, 0);
                    $xInscripciones[$xInsc]["proponente"] = '';
                    $xInscripciones[$xInsc]["tipodoc"] = $_SESSION["tramite"]["tipodoc"];
                    $xInscripciones[$xInsc]["numdoc"] = $_SESSION["tramite"]["numdoc"];
                    $xInscripciones[$xInsc]["origendoc"] = $_SESSION["tramite"]["origendoc"];
                    $xInscripciones[$xInsc]["municipio"] = $_SESSION["tramite"]["mundoc"];
                    $xInscripciones[$xInsc]["acto"] = '0081';
                    $xInscripciones[$xInsc]["fecha"] = date("Ymd");
                    $xInscripciones[$xInsc]["hora"] = date("His");
                    $xInscripciones[$xInsc]["fechadoc"] = $_SESSION["tramite"]["fechadoc"];
                    $xInscripciones[$xInsc]["nombre"] = trim($nomage . $nomsuc);
                    $xInscripciones[$xInsc]["tipoidentificacion"] = $tidePropietario;
                    $xInscripciones[$xInsc]["identificacion"] = $idePropietario;
                    $xInscripciones[$xInsc]["ope"] = $opeSecJur;
                    $xInscripciones[$xInsc]["bandeja"] = $arrTipoTramite["bandeja"];
                }

                if ($matEsadl != '') {
                    $xInsc++;
                    $xInscripciones[$xInsc]["tiporegistro"] = $arrTipoTramite["tiporegistro"];
                    $xInscripciones[$xInsc]["tiposello"] = '90.20.33';
                    $xInscripciones[$xInsc]["libro"] = 'RM51';
                    if ($_SESSION["tramite"]["organizacion"] == '12') {
                        $xInscripciones[$xInsc]["libro"] = 'RM51';
                    }
                    if ($_SESSION["tramite"]["organizacion"] == '14') {
                        $xInscripciones[$xInsc]["libro"] = 'RM53';
                    }
                    $xInscripciones[$xInsc]["numreg"] = '';
                    $xInscripciones[$xInsc]["organizacion"] = $_SESSION["tramite"]["organizacion"];
                    $xInscripciones[$xInsc]["categoria"] = '1';
                    $xInscripciones[$xInsc]["filesello"] = '';
                    $xInscripciones[$xInsc]["noticia"] = 'INSCRIPCION ENTIDAD SIN ANIMO DE LUCRO ' . $_SESSION["tramite"]["nombrepnat"];
                    $xInscripciones[$xInsc]["matricula"] = $matSuc;
                    $xInscripciones[$xInsc]["proponente"] = '';
                    $xInscripciones[$xInsc]["tipodoc"] = $_SESSION["tramite"]["tipodoc"];
                    $xInscripciones[$xInsc]["numdoc"] = $_SESSION["tramite"]["numdoc"];
                    $xInscripciones[$xInsc]["origendoc"] = $_SESSION["tramite"]["origendoc"];
                    $xInscripciones[$xInsc]["municipio"] = $_SESSION["tramite"]["mundoc"];
                    $xInscripciones[$xInsc]["acto"] = '0040';
                    $xInscripciones[$xInsc]["fecha"] = date("Ymd");
                    $xInscripciones[$xInsc]["hora"] = date("His");
                    $xInscripciones[$xInsc]["fechadoc"] = $_SESSION["tramite"]["fechadoc"];
                    $xInscripciones[$xInsc]["nombre"] = $nomesa;
                    $xInscripciones[$xInsc]["tipoidentificacion"] = $tidePropietario;
                    $xInscripciones[$xInsc]["identificacion"] = $idePropietario;
                    $xInscripciones[$xInsc]["ope"] = $opeSecJur;
                    $xInscripciones[$xInsc]["bandeja"] = $arrTipoTramite["bandeja"];
                }

                // ********************************************************************************************** //
                // Genera libro XXII Solo en caso de matriculas en actividad de apuestas y juegos de azar
                // lo hace para cada una de las matriculas
                // ********************************************************************************************** //	
                if ($matPnat != '' && $xSiNat22 == 'S') {
                    $xInsc++;
                    $xInscripciones[$xInsc]["tiporegistro"] = $arrTipoTramite["tiporegistro"];
                    $xInscripciones[$xInsc]["tiposello"] = '90.20.31';
                    $xInscripciones[$xInsc]["libro"] = 'RM22';
                    $xInscripciones[$xInsc]["numreg"] = '';
                    $xInscripciones[$xInsc]["organizacion"] = '01';
                    $xInscripciones[$xInsc]["categoria"] = '';
                    $xInscripciones[$xInsc]["filesello"] = '';
                    $xInscripciones[$xInsc]["noticia"] = 'INSCRIPCION DE LA ACTIVIDAD DE APUESTAS Y JUEGOS DE AZAR PARA LA PERSONA NATURAL';
                    $xInscripciones[$xInsc]["matricula"] = $matPnat;
                    $xInscripciones[$xInsc]["proponente"] = '';
                    $xInscripciones[$xInsc]["tipodoc"] = '06';
                    $xInscripciones[$xInsc]["numdoc"] = 'N/A';
                    $xInscripciones[$xInsc]["origendoc"] = 'EL COMERCIANTE';
                    $xInscripciones[$xInsc]["municipio"] = '';
                    $xInscripciones[$xInsc]["acto"] = '0743';
                    $xInscripciones[$xInsc]["fecha"] = date("Ymd");
                    $xInscripciones[$xInsc]["hora"] = date("His");
                    $xInscripciones[$xInsc]["fechadoc"] = date("Ymd");
                    $xInscripciones[$xInsc]["nombre"] = $nomnat;
                    $xInscripciones[$xInsc]["tipoidentificacion"] = $tidePropietario;
                    $xInscripciones[$xInsc]["identificacion"] = $idePropietario;
                    $xInscripciones[$xInsc]["ope"] = $opeSecJur;
                    $xInscripciones[$xInsc]["bandeja"] = $arrTipoTramite["bandeja"];
                }

                /*
                  if ($matEst != '' && $xSiEst22 == 'S') {
                  $xInsc++;
                  $xInscripciones[$xInsc]["tiporegistro"] = $arrTipoTramite["tiporegistro"];
                  $xInscripciones[$xInsc]["tiposello"] = '90.20.31';
                  $xInscripciones[$xInsc]["libro"] = 'RM22';
                  $xInscripciones[$xInsc]["numreg"] = '';
                  $xInscripciones[$xInsc]["organizacion"] = '02';
                  $xInscripciones[$xInsc]["filesello"] = '';
                  $xInscripciones[$xInsc]["noticia"] = 'INSCRIPCION DE LA ACTIVIDAD DE APUESTAS Y JUEGOS DE AZAR PARA EL ESTABLECIMIENTO DE COMERCIO DENOMINADO ' . $razEstablecimiento;
                  $xInscripciones[$xInsc]["matricula"] = $matEst;
                  $xInscripciones[$xInsc]["proponente"] = '';
                  $xInscripciones[$xInsc]["tipodoc"] = '06';
                  $xInscripciones[$xInsc]["numdoc"] = 'N/A';
                  $xInscripciones[$xInsc]["origendoc"] = 'EL COMERCIANTE';
                  $xInscripciones[$xInsc]["municipio"] = '';
                  $xInscripciones[$xInsc]["acto"] = '0743';
                  $xInscripciones[$xInsc]["fecha"] = date("Ymd");
                  $xInscripciones[$xInsc]["hora"] = date("His");
                  $xInscripciones[$xInsc]["fechadoc"] = date("Ymd");
                  $xInscripciones[$xInsc]["nombre"] = $nomest;
                  $xInscripciones[$xInsc]["tipoidentificacion"] = $tidePropietario;
                  $xInscripciones[$xInsc]["identificacion"] = $idePropietario;
                  $xInscripciones[$xInsc]["ope"] = $opeSecJur;
                  $xInscripciones[$xInsc]["bandeja"] = $arrTipoTramite["bandeja"];
                  }
                 */

                /*
                  if (($matSuc != '' || $matAge != '') && $xSiEst22 == 'S') {
                  $xInsc++;
                  $xInscripciones[$xInsc]["tiporegistro"] = $arrTipoTramite["tiporegistro"];
                  $xInscripciones[$xInsc]["tiposello"] = '90.20.31';
                  $xInscripciones[$xInsc]["libro"] = 'RM22';
                  $xInscripciones[$xInsc]["numreg"] = '';
                  $xInscripciones[$xInsc]["organizacion"] = $_SESSION["tramite"]["organizacion"];
                  $xInscripciones[$xInsc]["categoria"] = $_SESSION["tramite"]["categoria"];
                  $xInscripciones[$xInsc]["filesello"] = '';
                  $xInscripciones[$xInsc]["noticia"] = 'INSCRIPCION DE LA ACTIVIDAD DE APUESTAS Y JUEGOS DE AZAR PARA LA SUCURSAL O AGENCIA DENOMINADA ' . trim($_SESSION["tramite"]["nombresuc"]) . trim($_SESSION["tramite"]["nombreage"]);
                  $xInscripciones[$xInsc]["matricula"] = ltrim($matSuc, 0) . ltrim($matAge, 0);
                  $xInscripciones[$xInsc]["proponente"] = '';
                  $xInscripciones[$xInsc]["tipodoc"] = $_SESSION["tramite"]["tipodoc"];
                  $xInscripciones[$xInsc]["numdoc"] = $_SESSION["tramite"]["numdoc"];
                  $xInscripciones[$xInsc]["origendoc"] = $_SESSION["tramite"]["origendoc"];
                  $xInscripciones[$xInsc]["municipio"] = $_SESSION["tramite"]["mundoc"];
                  $xInscripciones[$xInsc]["acto"] = '0743';
                  $xInscripciones[$xInsc]["fecha"] = date("Ymd");
                  $xInscripciones[$xInsc]["hora"] = date("His");
                  $xInscripciones[$xInsc]["fechadoc"] = $_SESSION["tramite"]["fechadoc"];
                  $xInscripciones[$xInsc]["nombre"] = trim($nomage . $nomsuc);
                  $xInscripciones[$xInsc]["tipoidentificacion"] = $tidePropietario;
                  $xInscripciones[$xInsc]["identificacion"] = $idePropietario;
                  $xInscripciones[$xInsc]["ope"] = $opeSecJur;
                  $xInscripciones[$xInsc]["bandeja"] = $arrTipoTramite["bandeja"];
                  }
                 */
            }
        }
        \logApi::general2($nameLog, $idSolicitudPago, \funcionesGenerales::utf8_encode('Arma lista de inscripciones (matriculas)'));

        // ************************************************************************************************************** //
        // Arma el arreglo con las inscripciones que deben hacerse en los libros en caso de mutaciones
        // ************************************************************************************************************** //
        if ($_SESSION["tramite"]["tipotramite"] != 'inscripciondocumentos') {
            if ($esMutacion == 'si' && $asientoautomaticomutaciones == 'si') {
                // 2017-12-16: JINT
                // Se adiciona control para ley 1780, código de policia alto impacto y codigo de policía multas
                if (
                        $_SESSION["tramite"]["multadoponal"] != 'S' &&
                        $_SESSION["tramite"]["multadoponal"] != 'L' &&
                        $_SESSION["tramite"]["controlactividadaltoimpacto"] != 'S' && ($_SESSION["tramite"]["cumplorequisitosbenley1780"] != 'S' ||
                        $_SESSION["tramite"]["mantengorequisitosbenley1780"] != 'S' ||
                        $_SESSION["tramite"]["renunciobeneficiosley1780"] != 'N')
                ) {


                    // ************************************************************************************************* //
                    // Recupera xmloriginal y xmlfinal
                    // Arma variables temporales de manejo
                    // ************************************************************************************************* //
                    $_SESSION["tramite"]["xmloriginal"] = '';
                    $_SESSION["tramite"]["xmlfinal"] = '';

                    $_SESSION["tramite"]["original"]["nombre"] = '';
                    $_SESSION["tramite"]["original"]["dircom"] = '';
                    $_SESSION["tramite"]["original"]["telcom1"] = '';
                    $_SESSION["tramite"]["original"]["telcom2"] = '';
                    $_SESSION["tramite"]["original"]["telcom3"] = '';
                    $_SESSION["tramite"]["original"]["muncom"] = '';
                    $_SESSION["tramite"]["original"]["barriocom"] = '';
                    $_SESSION["tramite"]["original"]["paicom"] = '';
                    $_SESSION["tramite"]["original"]["faxcom"] = '';
                    $_SESSION["tramite"]["original"]["emailcom"] = '';
                    $_SESSION["tramite"]["original"]["numpredial"] = '';
                    $_SESSION["tramite"]["original"]["codigozonacom"] = '';
                    $_SESSION["tramite"]["original"]["codigopostalcom"] = '';
                    $_SESSION["tramite"]["original"]["ctrubi"] = '';
                    $_SESSION["tramite"]["original"]["urlcom"] = '';

                    //
                    $_SESSION["tramite"]["original"]["dirnot"] = '';
                    $_SESSION["tramite"]["original"]["telnot1"] = '';
                    $_SESSION["tramite"]["original"]["telnot2"] = '';
                    $_SESSION["tramite"]["original"]["telnot3"] = '';
                    $_SESSION["tramite"]["original"]["munnot"] = '';
                    $_SESSION["tramite"]["original"]["painot"] = '';
                    $_SESSION["tramite"]["original"]["faxnot"] = '';
                    $_SESSION["tramite"]["original"]["emailnot"] = '';
                    $_SESSION["tramite"]["original"]["codigozonanot"] = '';
                    $_SESSION["tramite"]["original"]["codigopostalnot"] = '';
                    $_SESSION["tramite"]["original"]["urlnot"] = '';
                    $_SESSION["tramite"]["original"]["tiposedeadm"] = '';
                    $_SESSION["tramite"]["original"]["ctrmennot"] = '';

                    $_SESSION["tramite"]["original"]["ciiu1"] = '';
                    $_SESSION["tramite"]["original"]["ciiu2"] = '';
                    $_SESSION["tramite"]["original"]["ciiu3"] = '';
                    $_SESSION["tramite"]["original"]["ciiu4"] = '';
                    $_SESSION["tramite"]["original"]["ciiu5"] = '';
                    $_SESSION["tramite"]["original"]["desactiv"] = '';
                    $_SESSION["tramite"]["original"]["feciniact1"] = '';
                    $_SESSION["tramite"]["original"]["feciniact2"] = '';
                    $_SESSION["tramite"]["original"]["ciiutamanoempresarial"] = '';
                    $_SESSION["tramite"]["original"]["cantidadmujeres"] = '';
                    $_SESSION["tramite"]["original"]["cantidadmujerescargosdirectivos"] = '';

                    $_SESSION["tramite"]["final"]["nombre"] = '';
                    $_SESSION["tramite"]["final"]["dircom"] = '';
                    $_SESSION["tramite"]["final"]["telcom1"] = '';
                    $_SESSION["tramite"]["final"]["telcom2"] = '';
                    $_SESSION["tramite"]["final"]["telcom3"] = '';
                    $_SESSION["tramite"]["final"]["muncom"] = '';
                    $_SESSION["tramite"]["final"]["paicom"] = '';
                    $_SESSION["tramite"]["final"]["barriocom"] = '';
                    $_SESSION["tramite"]["final"]["faxcom"] = '';
                    $_SESSION["tramite"]["final"]["emailcom"] = '';
                    $_SESSION["tramite"]["final"]["numpredial"] = '';
                    $_SESSION["tramite"]["final"]["codigozonacom"] = '';
                    $_SESSION["tramite"]["final"]["codigopostalcom"] = '';
                    $_SESSION["tramite"]["final"]["ctrubi"] = '';
                    $_SESSION["tramite"]["final"]["urlcom"] = '';

                    $_SESSION["tramite"]["final"]["nombre"] = '';
                    $_SESSION["tramite"]["final"]["dirnot"] = '';
                    $_SESSION["tramite"]["final"]["telnot1"] = '';
                    $_SESSION["tramite"]["final"]["telnot2"] = '';
                    $_SESSION["tramite"]["final"]["telnot3"] = '';
                    $_SESSION["tramite"]["final"]["munnot"] = '';
                    $_SESSION["tramite"]["final"]["painot"] = '';
                    $_SESSION["tramite"]["final"]["faxnot"] = '';
                    $_SESSION["tramite"]["final"]["emailnot"] = '';
                    $_SESSION["tramite"]["final"]["codigozonanot"] = '';
                    $_SESSION["tramite"]["final"]["codigopostalnot"] = '';
                    $_SESSION["tramite"]["final"]["urlnot"] = '';
                    $_SESSION["tramite"]["final"]["tiposedeadm"] = '';
                    $_SESSION["tramite"]["final"]["ctrmennot"] = '';

                    $_SESSION["tramite"]["final"]["ciiu1"] = '';
                    $_SESSION["tramite"]["final"]["ciiu2"] = '';
                    $_SESSION["tramite"]["final"]["ciiu3"] = '';
                    $_SESSION["tramite"]["final"]["ciiu4"] = '';
                    $_SESSION["tramite"]["final"]["ciiu5"] = '';
                    $_SESSION["tramite"]["final"]["desactiv"] = '';
                    $_SESSION["tramite"]["final"]["feciniact1"] = '';
                    $_SESSION["tramite"]["final"]["feciniact2"] = '';
                    $_SESSION["tramite"]["final"]["ciiutamanoempresarial"] = '';
                    $_SESSION["tramite"]["final"]["cantidadmujeres"] = '';
                    $_SESSION["tramite"]["final"]["cantidadmujerescargosdirectivos"] = '';

                    //
                    if ($_SESSION["tramite"]["tipotramite"] == 'mutacionregmer' || $_SESSION["tramite"]["tipotramite"] == 'mutacionesadl') {

                        $arrMat = retornarRegistroMysqliApi($mysqli, 'mreg_est_inscritos', "matricula='" . $_SESSION["tramite"]["idexpedientebase"] . "'");
                        $txtNoticia = '';
                        $txtSello = array();
                        $expo = array();
                        $expf = array();

                        //
                        $temx = retornarRegistrosMysqliApi($mysqli, 'mreg_renovacion_datos_control', "idliquidacion=" . $_SESSION["tramite"]["idliquidacion"], "dato");
                        foreach ($temx as $tx1) {
                            if ($tx1["momento"] == 'I') {
                                $expo[$tx1["dato"]] = $tx1["contenido"];
                            }
                            if ($tx1["momento"] == 'F') {
                                $expf[$tx1["dato"]] = $tx1["contenido"];
                            }
                            // \logApi::general2($nameLog, $_SESSION["tramite"]["idliquidacion"], 'mreg_renovacion_datos_control : ' . $tx1["momento"] . ' ' . $tx1["dato"] . ' : ' . $tx1["contenido"]);
                        }
                        unset($temx);

                        //
                        $_SESSION["tramite"]["modnombre"] = '';
                        $_SESSION["tramite"]["modcom"] = '';
                        $_SESSION["tramite"]["modnot"] = '';
                        $_SESSION["tramite"]["modciiu"] = '';

                        //
                        if ($expo["nombre"] != $expf["nombre"]) {
                            $_SESSION["tramite"]["modnombre"] = 'S';
                            $txtSello[] = "nombre";
                            if ($txtNoticia != '') {
                                $txtNoticia .= ', ';
                            }
                            $txtNoticia .= 'Nombre: ' . $expf["nombre"];
                        }
                        if ($expo["dircom"] != $expf["dircom"]) {
                            $_SESSION["tramite"]["modcom"] = 'S';
                            $txtSello[] = "dircom";
                            if ($txtNoticia != '') {
                                $txtNoticia .= ', ';
                            }
                            $txtNoticia .= 'Dirección comercial: ' . $expf["dircom"];
                        }
                        if ($expo["telcom1"] != $expf["telcom1"]) {
                            $_SESSION["tramite"]["modcom"] = 'S';
                            $txtSello[] = "telcom1";
                            if ($txtNoticia != '') {
                                $txtNoticia .= ', ';
                            }
                            $txtNoticia .= 'Teléfono comercial 1: ' . $expf["telcom1"];
                        }
                        if ($expo["telcom2"] != $expf["telcom2"]) {
                            $_SESSION["tramite"]["modcom"] = 'S';
                            $txtSello[] = "telcom2";
                            if ($txtNoticia != '') {
                                $txtNoticia .= ', ';
                            }
                            $txtNoticia .= 'Teléfono comercial 2: ' . $expf["telcom2"];
                        }
                        if ($expo["celcom"] != $expf["celcom"]) {
                            $_SESSION["tramite"]["modcom"] = 'S';
                            $txtSello[] = "celcom";
                            if ($txtNoticia != '') {
                                $txtNoticia .= ', ';
                            }
                            $txtNoticia .= 'Teléfono comercial 3: ' . $expf["celcom"];
                        }
                        if ($expo["muncom"] != $expf["muncom"]) {
                            $_SESSION["tramite"]["modcom"] = 'S';
                            $txtSello[] = "muncom";
                            if ($txtNoticia != '') {
                                $txtNoticia .= ', ';
                            }
                            $txtNoticia .= 'Municipio comercial: ' . $expf["muncom"];
                        }
                        if ($expo["barriocom"] != $expf["barriocom"]) {
                            $_SESSION["tramite"]["modcom"] = 'S';
                            $txtSello[] = "barriocom";
                            if ($txtNoticia != '') {
                                $txtNoticia .= ', ';
                            }
                            $txtNoticia .= 'Barrio comercial: ' . $expf["barriocom"];
                        }

                        if ($expo["emailcom"] != $expf["emailcom"]) {
                            $_SESSION["tramite"]["modcom"] = 'S';
                            $txtSello[] = "emailcom";
                            if ($txtNoticia != '') {
                                $txtNoticia .= ', ';
                            }
                            $txtNoticia .= 'Email comercial 1: ' . $expf["emailcom"];
                        }


                        if ($expo["numpredial"] != $expf["numpredial"]) {
                            $_SESSION["tramite"]["modcom"] = 'S';
                            $txtSello[] = "numpredial";
                            if ($txtNoticia != '') {
                                $txtNoticia .= ', ';
                            }
                            $txtNoticia .= 'Predial: ' . $expf["numpredial"];
                        }

                        /*
                          if ($expo["codigozonacom"] != $expf["codigozonacom"]) {
                          $_SESSION["tramite"]["modcom"] = 'S';
                          $txtSello[] = "codigozonacom";
                          if ($txtNoticia != '') {
                          $txtNoticia .= ', ';
                          }
                          $txtNoticia .= 'Zona comercial: ' . $expf["codigozonacom"];
                          }
                         */

                        /*
                          if ($expo["codigopostalcom"] != $expf["codigopostalcom"]) {
                          $_SESSION["tramite"]["modcom"] = 'S';
                          $txtSello[] = "codigopostalcom";
                          if ($txtNoticia != '') {
                          $txtNoticia .= ', ';
                          }
                          $txtNoticia .= 'Código postal comercial: ' . $expf["codigopostalcom"];
                          }
                         */

                        /*
                          if ($expo["ctrubi"] != $expf["ctrubi"]) {
                          $_SESSION["tramite"]["modcom"] = 'S';
                          $txtSello[] = "ctrubi";
                          if ($txtNoticia != '') {
                          $txtNoticia .= ', ';
                          }
                          $txtNoticia .= 'Ubicación comercial: ' . $expf["ctrubi"];
                          }
                         */

                        if ($expo["urlcom"] != $expf["urlcom"]) {
                            $_SESSION["tramite"]["modcom"] = 'S';
                            $txtSello[] = "urlcom";
                            if ($txtNoticia != '') {
                                $txtNoticia .= ', ';
                            }
                            $txtNoticia .= 'Url comercial: ' . $expf["urlcom"];
                        }
                        if ($expo["dirnot"] != $expf["dirnot"]) {
                            $_SESSION["tramite"]["modnot"] = 'S';
                            $txtSello[] = "dirnot";
                            if ($txtNoticia != '') {
                                $txtNoticia .= ', ';
                            }
                            $txtNoticia .= 'Dirección de notificación: ' . $expf["dirnot"];
                        }

                        /*
                          if ($expo["telnot"] != $expf["telnot"]) {
                          $_SESSION["tramite"]["modnot"] = 'S';
                          $txtSello[] = "telnot";
                          if ($txtNoticia != '') {
                          $txtNoticia .= ', ';
                          }
                          $txtNoticia .= 'Teléfono de notificación 1: ' . $expf["telnot"];
                          }
                         */

                        /*
                          if ($expo["telnot2"] != $expf["telnot2"]) {
                          $_SESSION["tramite"]["modnot"] = 'S';
                          $txtSello[] = "telnot2";
                          if ($txtNoticia != '') {
                          $txtNoticia .= ', ';
                          }
                          $txtNoticia .= 'Teléfono de notificación 2: ' . $expf["telnot2"];
                          }
                         */

                        /*
                          if ($expo["celnot"] != $expf["celnot"]) {
                          $_SESSION["tramite"]["modnot"] = 'S';
                          $txtSello[] = "celnot";
                          if ($txtNoticia != '') {
                          $txtNoticia .= ', ';
                          }
                          $txtNoticia .= 'Teléfono de notificación 3: ' . $expf["celnot"];
                          }
                         */

                        if ($expo["munnot"] != $expf["munnot"]) {
                            $_SESSION["tramite"]["modnot"] = 'S';
                            $txtSello[] = "munnot";
                            if ($txtNoticia != '') {
                                $txtNoticia .= ', ';
                            }
                            $txtNoticia .= 'Municipio de notificación: ' . $expf["munnot"];
                        }
                        if ($expo["barrionot"] != $expf["barrionot"]) {
                            $_SESSION["tramite"]["modnot"] = 'S';
                            $txtSello[] = "barrionot";
                            if ($txtNoticia != '') {
                                $txtNoticia .= ', ';
                            }
                            $txtNoticia .= 'Barrio de notificación: ' . $expf["barrionot"];
                        }

                        /*
                          if ($expo["painot"] == '' || $expo["painot"] == '169') {
                          $expo["painot"] = 'CO';
                          }
                          if ($expo["painot"] != $expf["painot"]) {
                          $_SESSION["tramite"]["modnot"] = 'S';
                          $txtSello[] = "painot";
                          if ($txtNoticia != '') {
                          $txtNoticia .= ', ';
                          }
                          $txtNoticia .= 'País de notificación: ' . $expf["painot"];
                          }
                         */

                        if ($expo["emailnot"] != $expf["emailnot"]) {
                            $_SESSION["tramite"]["modnot"] = 'S';
                            $txtSello[] = "emailnot";
                            if ($txtNoticia != '') {
                                $txtNoticia .= ', ';
                            }
                            $txtNoticia .= 'Email de notificación: ' . $expf["emailnot"];
                        }

                        /*
                          if ($expo["codigozonanot"] != $expf["codigozonanot"]) {
                          $_SESSION["tramite"]["modnot"] = 'S';
                          $txtSello[] = "codigozonanot";
                          if ($txtNoticia != '') {
                          $txtNoticia .= ', ';
                          }
                          $txtNoticia .= 'Zona de ubicación para notificación: ' . $expf["codigozonanot"];
                          }
                         */

                        /*
                          if ($expo["codigopostalnot"] != $expf["codigopostalnot"]) {
                          $_SESSION["tramite"]["modnot"] = 'S';
                          $txtSello[] = "codigopostalnot";
                          if ($txtNoticia != '') {
                          $txtNoticia .= ', ';
                          }
                          $txtNoticia .= 'Código postal para notificación: ' . $expf["codigopostalnot"];
                          }
                         */

                        /*
                          if ($expo["tiposedeadm"] != $expf["tiposedeadm"]) {
                          $_SESSION["tramite"]["modnot"] = 'S';
                          $txtSello[] = "tiposedeadm";
                          if ($txtNoticia != '') {
                          $txtNoticia .= ', ';
                          }
                          $txtNoticia .= 'Tipo de sede administrativa: ' . $expf["tiposedeadm"];
                          }
                         */

                        if ($expo["ciiu1"] != $expf["ciiu1"]) {
                            $_SESSION["tramite"]["modciiu"] = 'S';
                            $txtSello[] = "ciiu1";
                            if ($txtNoticia != '') {
                                $txtNoticia .= ', ';
                            }
                            $txtNoticia .= 'Código de actividad principal (1): ' . $expf["ciiu1"];
                        }
                        if ($expo["ciiu2"] != $expf["ciiu2"]) {
                            $_SESSION["tramite"]["modciiu"] = 'S';
                            $txtSello[] = "ciiu2";
                            if ($txtNoticia != '') {
                                $txtNoticia .= ', ';
                            }
                            $txtNoticia .= 'Código de actividad secundaria (2): ' . $expf["ciiu2"];
                        }
                        if ($expo["ciiu3"] != $expf["ciiu3"]) {
                            $_SESSION["tramite"]["modciiu"] = 'S';
                            $txtSello[] = "ciiu3";
                            if ($txtNoticia != '') {
                                $txtNoticia .= ', ';
                            }
                            $txtNoticia .= 'Código de actividad adicional (3): ' . $expf["ciiu3"];
                        }
                        if ($expo["ciiu4"] != $expf["ciiu4"]) {
                            $_SESSION["tramite"]["modciiu"] = 'S';
                            $txtSello[] = "ciiu4";
                            if ($txtNoticia != '') {
                                $txtNoticia .= ', ';
                            }
                            $txtNoticia .= 'Código de actividad adicional (4): ' . $expf["ciiu4"];
                        }

                        //
                        if ($expo["desactiv"] != $expf["desactiv"]) {
                            $_SESSION["tramite"]["modciiu"] = 'S';
                            $txtSello[] = "desactiv";
                            if ($txtNoticia != '') {
                                $txtNoticia .= ', ';
                            }
                            $txtNoticia .= 'Descripción de la actividad: ' . $expf["desactiv"];
                        }

                        //
                        if ($expo["ciiutamanoempresarial"] != $expf["ciiutamanoempresarial"]) {
                            $_SESSION["tramite"]["modciiu"] = 'S';
                            $txtSello[] = "ciiutamanoempresarial";
                            if ($txtNoticia != '') {
                                $txtNoticia .= ', ';
                            }
                            $txtNoticia .= 'Ciiu de mayores ingresos : ' . $expf["ciiutamanoempresarial"];
                        }


                        //
                        if ($arrMat["organizacion"] == '01' || ($arrMat["organizacion"] > '02' && $arrMat["categoria"] == '1')) {
                            if ($expo["feciniact1"] != $expf["feciniact1"]) {
                                $_SESSION["tramite"]["modciiu"] = 'S';
                                $txtSello[] = "feciniact1";
                                if ($txtNoticia != '') {
                                    $txtNoticia .= ', ';
                                }
                                $txtNoticia .= 'Fecha de inicio de actividad principal: ' . $expf["feciniact1"];
                            }
                            if ($expo["feciniact2"] != $expf["feciniact2"]) {
                                $_SESSION["tramite"]["modciiu"] = 'S';
                                $txtSello[] = "feciniact2";
                                if ($txtNoticia != '') {
                                    $txtNoticia .= ', ';
                                }
                                $txtNoticia .= 'Fecha de inicio de actividad secundaria: ' . $expf["feciniact2"];
                            }
                        }

                        //
                        if ($expo["cantidadmujeres"] != $expf["cantidadmujeres"]) {
                            $_SESSION["tramite"]["modciiu"] = 'S';
                            $txtSello[] = "cantidadmujeres";
                            if ($txtNoticia != '') {
                                $txtNoticia .= ', ';
                            }
                            $txtNoticia .= 'Cantidad mujeres : ' . $expf["cantidadmujeres"];
                        }

                        // 
                        if ($expo["cantidadmujerescargosdirectivos"] != $expf["cantidadmujerescargosdirectivos"]) {
                            $_SESSION["tramite"]["modciiu"] = 'S';
                            $txtSello[] = "cantidadmujerescargosdirectivos";
                            if ($txtNoticia != '') {
                                $txtNoticia .= ', ';
                            }
                            $txtNoticia .= 'Cantidad mujeres en cargos directivos : ' . $expf["cantidadmujerescargosdirectivos"];
                        }

                        //
                        if ($expo["personal"] != $expf["personal"]) {
                            $_SESSION["tramite"]["modciiu"] = 'S';
                            $txtSello[] = "personal";
                            if ($txtNoticia != '') {
                                $txtNoticia .= ', ';
                            }
                            $txtNoticia .= 'Personal : ' . $expf["personal"];
                        }

                        //
                        if (!isset($expo["paicom"])) {
                            $expo["paicom"] = '';
                        }
                        if (!isset($expo["painot"])) {
                            $expo["painot"] = '';
                        }
                        if (!isset($expf["paicom"])) {
                            $expf["paicom"] = '';
                        }
                        if (!isset($expf["painot"])) {
                            $expf["painot"] = '';
                        }

                        //
                        $_SESSION["tramite"]["original"]["nombre"] = $expo["nombre"];
                        $_SESSION["tramite"]["original"]["dircom"] = $expo["dircom"];
                        $_SESSION["tramite"]["original"]["telcom1"] = $expo["telcom1"];
                        $_SESSION["tramite"]["original"]["telcom2"] = $expo["telcom2"];
                        $_SESSION["tramite"]["original"]["telcom3"] = $expo["celcom"];
                        $_SESSION["tramite"]["original"]["muncom"] = $expo["muncom"];
                        $_SESSION["tramite"]["original"]["barriocom"] = $expo["barriocom"];
                        $_SESSION["tramite"]["original"]["paicom"] = $expo["paicom"];
                        $_SESSION["tramite"]["original"]["faxcom"] = '';
                        $_SESSION["tramite"]["original"]["emailcom"] = $expo["emailcom"];
                        $_SESSION["tramite"]["original"]["numpredial"] = $expo["numpredial"];
                        $_SESSION["tramite"]["original"]["codigozonacom"] = $expo["codigozonacom"];
                        $_SESSION["tramite"]["original"]["codigopostalcom"] = $expo["codigopostalcom"];
                        $_SESSION["tramite"]["original"]["ctrubi"] = $expo["ctrubi"];
                        $_SESSION["tramite"]["original"]["urlcom"] = $expo["urlcom"];

                        //
                        $_SESSION["tramite"]["original"]["dirnot"] = $expo["dirnot"];
                        $_SESSION["tramite"]["original"]["telnot1"] = $expo["telnot"];
                        $_SESSION["tramite"]["original"]["telnot2"] = $expo["telnot2"];
                        $_SESSION["tramite"]["original"]["telnot3"] = $expo["celnot"];
                        $_SESSION["tramite"]["original"]["munnot"] = $expo["munnot"];
                        $_SESSION["tramite"]["original"]["painot"] = $expo["painot"];
                        $_SESSION["tramite"]["original"]["faxnot"] = '';
                        $_SESSION["tramite"]["original"]["emailnot"] = $expo["emailnot"];
                        $_SESSION["tramite"]["original"]["codigozonanot"] = $expo["codigozonanot"];
                        $_SESSION["tramite"]["original"]["codigopostalnot"] = $expo["codigopostalnot"];
                        $_SESSION["tramite"]["original"]["urlnot"] = $expo["urlnot"];
                        $_SESSION["tramite"]["original"]["tiposedeadm"] = $expo["tiposedeadm"];
                        $_SESSION["tramite"]["original"]["tiposedeadm"] = $expo["tiposedeadm"];

                        $_SESSION["tramite"]["original"]["ciiu1"] = $expo["ciiu1"];
                        $_SESSION["tramite"]["original"]["ciiu2"] = $expo["ciiu2"];
                        $_SESSION["tramite"]["original"]["ciiu3"] = $expo["ciiu3"];
                        $_SESSION["tramite"]["original"]["ciiu4"] = $expo["ciiu4"];
                        $_SESSION["tramite"]["original"]["ciiu5"] = '';
                        $_SESSION["tramite"]["original"]["desactiv"] = $expo["desactiv"];
                        if ($arrMat["organizacion"] == '02' || $arrMat["categoria"] == '2' || $arrMat["categoria"] == '3') {
                            $_SESSION["tramite"]["original"]["feciniact1"] = '';
                            $_SESSION["tramite"]["original"]["feciniact2"] = '';
                        } else {
                            $_SESSION["tramite"]["original"]["feciniact1"] = $expo["feciniact1"];
                            $_SESSION["tramite"]["original"]["feciniact2"] = $expo["feciniact2"];
                        }
                        $_SESSION["tramite"]["original"]["ciiutamanoempresarial"] = $expo["ciiutamanoempresarial"];
                        $_SESSION["tramite"]["original"]["cantidadmujeres"] = $expo["cantidadmujeres"];
                        $_SESSION["tramite"]["original"]["cantidadmujerescargosdirectivos"] = $expo["cantidadmujerescargosdirectivos"];
                        $_SESSION["tramite"]["original"]["personal"] = $expo["personal"];

                        $_SESSION["tramite"]["final"]["nombre"] = $expf["nombre"];
                        $_SESSION["tramite"]["final"]["dircom"] = $expf["dircom"];
                        $_SESSION["tramite"]["final"]["telcom1"] = $expf["telcom1"];
                        $_SESSION["tramite"]["final"]["telcom2"] = $expf["telcom2"];
                        $_SESSION["tramite"]["final"]["telcom3"] = $expf["celcom"];
                        $_SESSION["tramite"]["final"]["muncom"] = $expf["muncom"];
                        $_SESSION["tramite"]["final"]["barriocom"] = $expf["barriocom"];
                        $_SESSION["tramite"]["final"]["paicom"] = $expf["paicom"];
                        $_SESSION["tramite"]["final"]["faxcom"] = '';
                        $_SESSION["tramite"]["final"]["emailcom"] = $expf["emailcom"];
                        $_SESSION["tramite"]["final"]["numpredial"] = $expf["numpredial"];
                        $_SESSION["tramite"]["final"]["codigozonacom"] = $expf["codigozonacom"];
                        $_SESSION["tramite"]["final"]["codigopostalcom"] = $expf["codigopostalcom"];
                        $_SESSION["tramite"]["final"]["ctrubi"] = $expf["ctrubi"];
                        $_SESSION["tramite"]["final"]["urlcom"] = $expf["urlcom"];

                        //
                        $_SESSION["tramite"]["final"]["dirnot"] = $expf["dirnot"];
                        $_SESSION["tramite"]["final"]["telnot1"] = $expf["telnot"];
                        $_SESSION["tramite"]["final"]["telnot2"] = $expf["telnot2"];
                        $_SESSION["tramite"]["final"]["telnot3"] = $expf["celnot"];
                        $_SESSION["tramite"]["final"]["munnot"] = $expf["munnot"];
                        $_SESSION["tramite"]["final"]["painot"] = $expf["painot"];
                        $_SESSION["tramite"]["final"]["faxnot"] = '';
                        $_SESSION["tramite"]["final"]["emailnot"] = $expf["emailnot"];
                        $_SESSION["tramite"]["final"]["codigozonanot"] = $expf["codigozonanot"];
                        $_SESSION["tramite"]["final"]["codigopostalnot"] = $expf["codigopostalnot"];
                        $_SESSION["tramite"]["final"]["urlnot"] = $expf["urlnot"];
                        $_SESSION["tramite"]["final"]["tiposedeadm"] = $expf["tiposedeadm"];
                        $_SESSION["tramite"]["final"]["tiposedeadm"] = $expf["tiposedeadm"];

                        $_SESSION["tramite"]["final"]["ciiu1"] = $expf["ciiu1"];
                        $_SESSION["tramite"]["final"]["ciiu2"] = $expf["ciiu2"];
                        $_SESSION["tramite"]["final"]["ciiu3"] = $expf["ciiu3"];
                        $_SESSION["tramite"]["final"]["ciiu4"] = $expf["ciiu4"];
                        $_SESSION["tramite"]["final"]["ciiu5"] = '';
                        $_SESSION["tramite"]["final"]["desactiv"] = $expf["desactiv"];
                        $_SESSION["tramite"]["final"]["ciiutamanoempresarial"] = $expf["ciiutamanoempresarial"];
                        $_SESSION["tramite"]["final"]["cantidadmujeres"] = $expf["cantidadmujeres"];
                        $_SESSION["tramite"]["final"]["cantidadmujerescargosdirectivos"] = $expf["cantidadmujerescargosdirectivos"];
                        $_SESSION["tramite"]["final"]["personal"] = $expf["personal"];

                        //
                        if ($arrMat["organizacion"] == '02' || $arrMat["categoria"] == '2' || $arrMat["categoria"] == '3') {
                            $_SESSION["tramite"]["final"]["feciniact1"] = '';
                            $_SESSION["tramite"]["final"]["feciniact2"] = '';
                        } else {
                            $_SESSION["tramite"]["final"]["feciniact1"] = $expf["feciniact1"];
                            $_SESSION["tramite"]["final"]["feciniact2"] = $expf["feciniact2"];
                        }
                    }

                    //
                    if (
                            $_SESSION["tramite"]["tipotramite"] == 'mutaciondireccion' ||
                            $_SESSION["tramite"]["tipotramite"] == 'mutacionactividad' ||
                            $_SESSION["tramite"]["tipotramite"] == 'mutacionnombre'
                    ) {
                        $arrTem = retornarRegistrosMysqliApi($mysqli, 'mreg_liquidaciondatos_complementarios', "idliquidacion=" . $_SESSION["tramite"]["idliquidacion"], "expediente");
                        foreach ($arrTem as $t) {
                            $_SESSION["tramite"]["xmloriginal"] = $t["xmloriginal"];
                            $_SESSION["tramite"]["xmlfinal"] = $t["xmlfinal"];
                        }
                        $dom = new DomDocument('1.0', 'utf-8');
                        $result1 = $dom->loadXML($_SESSION["tramite"]["xmloriginal"]);
                        if ($result1) {
                            $reg1 = $dom->getElementsByTagName("datosanteriores");
                            foreach ($reg1 as $reg) {
                                if ($_SESSION["tramite"]["modcom"] == 'S') {
                                    $_SESSION["tramite"]["original"]["dircom"] = $reg->getElementsByTagName("dircom")->item(0)->textContent;
                                    $_SESSION["tramite"]["original"]["telcom1"] = $reg->getElementsByTagName("telcom1")->item(0)->textContent;
                                    $_SESSION["tramite"]["original"]["telcom2"] = $reg->getElementsByTagName("telcom2")->item(0)->textContent;
                                    $_SESSION["tramite"]["original"]["telcom3"] = $reg->getElementsByTagName("celcom")->item(0)->textContent;
                                    $_SESSION["tramite"]["original"]["faxcom"] = $reg->getElementsByTagName("faxcom")->item(0)->textContent;
                                    $_SESSION["tramite"]["original"]["barriocom"] = $reg->getElementsByTagName("barriocom")->item(0)->textContent;
                                    $_SESSION["tramite"]["original"]["muncom"] = $reg->getElementsByTagName("muncom")->item(0)->textContent;
                                    $_SESSION["tramite"]["original"]["emailcom"] = $reg->getElementsByTagName("emailcom")->item(0)->textContent;
                                    $_SESSION["tramite"]["original"]["numpredial"] = $reg->getElementsByTagName("numpredial")->item(0)->textContent;
                                }
                                if ($_SESSION["tramite"]["modnot"] == 'S') {
                                    $_SESSION["tramite"]["original"]["dirnot"] = $reg->getElementsByTagName("dirnot")->item(0)->textContent;
                                    $_SESSION["tramite"]["original"]["telnot1"] = $reg->getElementsByTagName("telnot")->item(0)->textContent;
                                    $_SESSION["tramite"]["original"]["telnot2"] = $reg->getElementsByTagName("telnot2")->item(0)->textContent;
                                    $_SESSION["tramite"]["original"]["telnot3"] = $reg->getElementsByTagName("celnot")->item(0)->textContent;
                                    $_SESSION["tramite"]["original"]["faxnot"] = $reg->getElementsByTagName("faxnot")->item(0)->textContent;
                                    $_SESSION["tramite"]["original"]["munnot"] = $reg->getElementsByTagName("munnot")->item(0)->textContent;
                                    $_SESSION["tramite"]["original"]["emailnot"] = $reg->getElementsByTagName("emailnot")->item(0)->textContent;
                                }
                                if ($_SESSION["tramite"]["modnombre"] == 'S') {
                                    $_SESSION["tramite"]["original"]["nombre"] = $reg->getElementsByTagName("nombre")->item(0)->textContent;
                                }
                                if ($_SESSION["tramite"]["modciiu"] == 'S') {
                                    $_SESSION["tramite"]["original"]["ciiu1"] = $reg->getElementsByTagName("ciiu1")->item(0)->textContent;
                                    $_SESSION["tramite"]["original"]["ciiu2"] = $reg->getElementsByTagName("ciiu2")->item(0)->textContent;
                                    $_SESSION["tramite"]["original"]["ciiu3"] = $reg->getElementsByTagName("ciiu3")->item(0)->textContent;
                                    $_SESSION["tramite"]["original"]["ciiu4"] = $reg->getElementsByTagName("ciiu4")->item(0)->textContent;
                                    $_SESSION["tramite"]["original"]["feciniact1"] = $reg->getElementsByTagName("feciniact1")->item(0)->textContent;
                                    $_SESSION["tramite"]["original"]["feciniact2"] = $reg->getElementsByTagName("feciniact2")->item(0)->textContent;
                                    $_SESSION["tramite"]["original"]["desactiv"] = $reg->getElementsByTagName("desactiv")->item(0)->textContent;
                                    $_SESSION["tramite"]["original"]["ciiutamanoempresarial"] = $reg->getElementsByTagName("ciiutamanoempresarial")->item(0)->textContent;
                                    $_SESSION["tramite"]["original"]["cantidadmujeres"] = $reg->getElementsByTagName("cantidadmujeres")->item(0)->textContent;
                                    $_SESSION["tramite"]["original"]["cantidadmujerescargosdirectivos"] = $reg->getElementsByTagName("cantidadmujerescargosdirectivos")->item(0)->textContent;
                                    $_SESSION["tramite"]["original"]["personal"] = $reg->getElementsByTagName("personal")->item(0)->textContent;
                                }
                            }
                        }
                        unset($reg);
                        unset($reg1);
                        unset($dom);

                        $dom = new DomDocument('1.0', 'utf-8');
                        $result2 = $dom->loadXML($_SESSION["tramite"]["xmlfinal"]);
                        if ($result2) {
                            $reg1 = $dom->getElementsByTagName("datosnuevos");
                            foreach ($reg1 as $reg) {
                                if ($_SESSION["tramite"]["modcom"] == 'S') {
                                    $_SESSION["tramite"]["final"]["dircom"] = $reg->getElementsByTagName("dircom")->item(0)->textContent;
                                    $_SESSION["tramite"]["final"]["telcom1"] = $reg->getElementsByTagName("telcom1")->item(0)->textContent;
                                    $_SESSION["tramite"]["final"]["telcom2"] = $reg->getElementsByTagName("telcom2")->item(0)->textContent;
                                    $_SESSION["tramite"]["final"]["telcom3"] = $reg->getElementsByTagName("celcom")->item(0)->textContent;
                                    $_SESSION["tramite"]["final"]["faxcom"] = $reg->getElementsByTagName("faxcom")->item(0)->textContent;
                                    $_SESSION["tramite"]["final"]["barriocom"] = $reg->getElementsByTagName("barriocom")->item(0)->textContent;
                                    $_SESSION["tramite"]["final"]["muncom"] = $reg->getElementsByTagName("muncom")->item(0)->textContent;
                                    $_SESSION["tramite"]["final"]["emailcom"] = $reg->getElementsByTagName("emailcom")->item(0)->textContent;
                                    $_SESSION["tramite"]["final"]["numpredial"] = $reg->getElementsByTagName("numpredial")->item(0)->textContent;
                                }
                                if ($_SESSION["tramite"]["modnot"] == 'S') {
                                    $_SESSION["tramite"]["final"]["dirnot"] = $reg->getElementsByTagName("dirnot")->item(0)->textContent;
                                    $_SESSION["tramite"]["final"]["telnot1"] = $reg->getElementsByTagName("telnot")->item(0)->textContent;
                                    $_SESSION["tramite"]["final"]["telnot2"] = $reg->getElementsByTagName("telnot2")->item(0)->textContent;
                                    $_SESSION["tramite"]["final"]["telnot3"] = $reg->getElementsByTagName("celnot")->item(0)->textContent;
                                    $_SESSION["tramite"]["final"]["faxnot"] = $reg->getElementsByTagName("faxnot")->item(0)->textContent;
                                    $_SESSION["tramite"]["final"]["munnot"] = $reg->getElementsByTagName("munnot")->item(0)->textContent;
                                    $_SESSION["tramite"]["final"]["emailnot"] = $reg->getElementsByTagName("emailnot")->item(0)->textContent;
                                }
                                if ($_SESSION["tramite"]["modnombre"] == 'S') {
                                    $_SESSION["tramite"]["final"]["nombre"] = $reg->getElementsByTagName("nombre")->item(0)->textContent;
                                }
                                if ($_SESSION["tramite"]["modciiu"] == 'S') {
                                    $_SESSION["tramite"]["final"]["ciiu1"] = $reg->getElementsByTagName("ciiu1")->item(0)->textContent;
                                    $_SESSION["tramite"]["final"]["ciiu2"] = $reg->getElementsByTagName("ciiu2")->item(0)->textContent;
                                    $_SESSION["tramite"]["final"]["ciiu3"] = $reg->getElementsByTagName("ciiu3")->item(0)->textContent;
                                    $_SESSION["tramite"]["final"]["ciiu4"] = $reg->getElementsByTagName("ciiu4")->item(0)->textContent;
                                    $_SESSION["tramite"]["final"]["feciniact1"] = $reg->getElementsByTagName("feciniact1")->item(0)->textContent;
                                    $_SESSION["tramite"]["final"]["feciniact2"] = $reg->getElementsByTagName("feciniact2")->item(0)->textContent;
                                    $_SESSION["tramite"]["final"]["desactiv"] = $reg->getElementsByTagName("desactiv")->item(0)->textContent;
                                    $_SESSION["tramite"]["final"]["ciiutamanoempresarial"] = $reg->getElementsByTagName("ciiutamanoempresarial")->item(0)->textContent;
                                    $_SESSION["tramite"]["final"]["cantidadmujeres"] = $reg->getElementsByTagName("cantidadmujeres")->item(0)->textContent;
                                    $_SESSION["tramite"]["final"]["cantidadmujerescargosdirectivos"] = $reg->getElementsByTagName("cantidadmujerescargosdirectivos")->item(0)->textContent;
                                    $_SESSION["tramite"]["final"]["personal"] = $reg->getElementsByTagName("personal")->item(0)->textContent;
                                }
                            }
                        }
                        unset($reg);
                        unset($reg1);
                        unset($dom);
                    }

                    // ******************************************************************************************************************* //
                    // Construye el libro, y el acto dependiendo de la organizacion juridica, de la categoria y de la clase especial
                    // ******************************************************************************************************************* //
                    $mtiporegistro = 'RegMer';
                    $mtiposello = '90.20.31';
                    $mtipobandeja = $arrTipoTramite["bandeja"];
                    $mlibro = 'RM15';
                    $macto = '';
                    $mnoticia = '';
                    // $mnoticia1 = '';               
                    //
                    if ($orgExpediente == '12' && $clasEspExpediente == '60') { // Veedurias
                        if ($catExpediente == '1') {
                            $mtiporegistro = 'RegEsadl';
                            $mlibro = 'RE54';
                            $mtiposello = '90.20.33';
                            $mtipobandeja = '5.-REGESADL';
                        }
                    }
                    if ($orgExpediente == '12' && $clasEspExpediente == '61') { // ONG Extranjeras
                        if ($catExpediente == '1') {
                            $mtiporegistro = 'RegEsadl';
                            $mlibro = 'RE55';
                            $mtiposello = '90.20.33';
                            $mtipobandeja = '5.-REGESADL';
                        }
                    }
                    if ($orgExpediente == '12' && $clasEspExpediente != '60' && $clasEspExpediente != '61') { // Otras
                        if ($catExpediente == '1') {
                            $mtiporegistro = 'RegEsadl';
                            $mlibro = 'RE51';
                            $mtiposello = '90.20.33';
                            $mtipobandeja = '5.-REGESADL';
                        }
                    }
                    if ($orgExpediente == '10') { // Otras
                        $mtiporegistro = 'RegMer';
                        $mlibro = 'RM13';
                        $mtiposello = '90.20.31';
                        $mtipobandeja = '4.-REGMER';
                    }
                    if ($orgExpediente == '14') { // Otras
                        if ($catExpediente == '1') {
                            $mtiporegistro = 'RegEsadl';
                            $mlibro = 'RE53';
                            $mtiposello = '90.20.33';
                            $mtipobandeja = '5.-REGESADL';
                        }
                    }

                    // Asigna el libro 22 en caso que sea una mutacion de actividad y que se asigne apuestas y juegos de azar o
                    // siendo una mutacion diferente, sea el codigo CIIU R9200		
                    if ($cambioActividad == 'S' || $_SESSION["tramite"]["modciiu"] == 'S') {
                        if (($_SESSION["tramite"]["final"]["ciiu1"] == 'R9200') || ($_SESSION["tramite"]["final"]["ciiu2"] == 'R9200') || ($_SESSION["tramite"]["final"]["ciiu3"] == 'R9200') || ($_SESSION["tramite"]["final"]["ciiu4"] == 'R9200')
                        ) {
                            if ($orgExpediente == '01' || ($orgExpediente > '02' && $catExpediente == '1')) {
                                $mlibro = 'RM22'; // Registro Mercantil - Juegos de apuestas y azar
                            }
                        }
                    } else {
                        if (
                                $datx["ciius"][1] == 'R9200' ||
                                $datx["ciius"][2] == 'R9200' ||
                                $datx["ciius"][3] == 'R9200' ||
                                $datx["ciius"][4] == 'R9200'
                        ) {
                            if ($orgExpediente == '01' || ($orgExpediente > '02' && $catExpediente == '1')) {
                                $mlibro = 'RM22'; // Registro Mercantil - Juegos de apuestas y azar
                            }
                        }
                    }

                    if ($_SESSION["tramite"]["tipotramite"] == 'mutacionregmer' || $_SESSION["tramite"]["tipotramite"] == 'mutacionesadl') {
                        $macto = '0802';
                        if ($mlibro == 'RM15') {
                            $acto = '0802';
                        }
                        if ($mlibro == 'RM22') {
                            $acto = '0802';
                        }
                        if ($mlibro == 'RE51') {
                            $acto = '0802';
                        }
                        if ($mlibro == 'RE53') {
                            $acto = '0802';
                        }
                    } else {
                        if ($_SESSION["tramite"]["modcom"] == 'S' && $_SESSION["tramite"]["modnot"] == 'S') {
                            $macto = '0747';
                        }
                        if ($_SESSION["tramite"]["modcom"] == 'S' && $_SESSION["tramite"]["modnot"] == 'N') {
                            $macto = '0747';
                        }
                        if ($_SESSION["tramite"]["modcom"] == 'N' && $_SESSION["tramite"]["modnot"] == 'S') {
                            $macto = '0748';
                        }
                        if ($_SESSION["tramite"]["modnombre"] == 'S') {
                            $macto = '0749';
                        }
                        if ($_SESSION["tramite"]["modciiu"] == 'S') {
                            $macto = '0745';
                        }
                    }


                    if ($_SESSION["tramite"]["tipotramite"] == 'mutacionregmer' || $_SESSION["tramite"]["tipotramite"] == 'mutacionesadl') {
                        $mnoticia = 'ACTUALIZACIÓN DE DATOS (MUTACIÓN) POR SOLICITUD DEL COMERCIANTE / INSCRITO. ' . $txtNoticia;
                    } else {
                        if ($_SESSION["tramite"]["modciiu"] == 'S') {
                            $mnoticia = 'MUTACIÓN DE ACTIVIDAD ECONÓMICA POR SOLICITUD DEL COMERCIANTE / INSCRITO. ';
                            if ($_SESSION["tramite"]["original"]["ciiu1"] != $_SESSION["tramite"]["final"]["ciiu1"]) {
                                $mnoticia .= 'CIIU PRINCIPAL CAMBIÓ DE ' . $_SESSION["tramite"]["original"]["ciiu1"] . ' POR ' . $_SESSION["tramite"]["final"]["ciiu1"] . '.';
                            }
                            if ($_SESSION["tramite"]["original"]["ciiu2"] != $_SESSION["tramite"]["final"]["ciiu2"]) {
                                if ($_SESSION["tramite"]["original"]["ciiu2"] != '') {
                                    $mnoticia .= 'CIIU SECUNDARIO CAMBIÓ DE ' . $_SESSION["tramite"]["original"]["ciiu2"] . ' POR ' . $_SESSION["tramite"]["final"]["ciiu2"] . '.';
                                } else {
                                    $mnoticia .= 'ADICIONÓ CIIU SECUNDARIO ' . $_SESSION["tramite"]["final"]["ciiu2"] . '.';
                                }
                            }
                            if ($_SESSION["tramite"]["original"]["ciiu3"] != $_SESSION["tramite"]["final"]["ciiu3"]) {
                                if ($_SESSION["tramite"]["original"]["ciiu3"] != '') {
                                    $mnoticia .= 'TERCER CIIU CAMBIÓ DE ' . $_SESSION["tramite"]["original"]["ciiu3"] . ' POR ' . $_SESSION["tramite"]["final"]["ciiu3"] . '.';
                                } else {
                                    $mnoticia .= 'ADICIONÓ TERCER CIIU ' . $_SESSION["tramite"]["final"]["ciiu3"] . '.';
                                }
                            }
                            if ($_SESSION["tramite"]["original"]["ciiu4"] != $_SESSION["tramite"]["final"]["ciiu4"]) {
                                if ($_SESSION["tramite"]["original"]["ciiu4"] != '') {
                                    $mnoticia .= 'CUARTO CIIU CAMBIÓ DE ' . $_SESSION["tramite"]["original"]["ciiu4"] . ' POR ' . $_SESSION["tramite"]["final"]["ciiu4"] . '.';
                                } else {
                                    $mnoticia .= 'ADICIONÓ CUARTO CIIU ' . $_SESSION["tramite"]["final"]["ciiu4"] . '.';
                                }
                            }
                            if ($_SESSION["tramite"]["original"]["ciiutamanoemperesarial"] != $_SESSION["tramite"]["final"]["ciiutamanoemperesarial"]) {
                                if ($_SESSION["tramite"]["original"]["ciiutamanoemperesarial"] != '') {
                                    $mnoticia .= 'ACTIVIDAD DE MAYORES INGRESOS CAMBIÓ DE ' . $_SESSION["tramite"]["original"]["ciiutamanoemperesarial"] . ' POR ' . $_SESSION["tramite"]["final"]["ciiutamanoemperesarial"] . '.';
                                } else {
                                    $mnoticia .= 'ACTIVIDAD DE MAYORES INGRESOS ' . $_SESSION["tramite"]["final"]["ciiutamanoemperesarial"] . '.';
                                }
                            }
                            if ($_SESSION["tramite"]["original"]["cantidadmujeres"] != $_SESSION["tramite"]["final"]["cantidadmujeres"]) {
                                if ($_SESSION["tramite"]["original"]["cantidadmujeres"] != '') {
                                    $mnoticia .= 'CANTIDAD DE MUJERES CAMBIÓ DE ' . $_SESSION["tramite"]["original"]["cantidadmujeres"] . ' POR ' . $_SESSION["tramite"]["final"]["cantidadmujeres"] . '.';
                                } else {
                                    $mnoticia .= 'CANTIDAD DE MUJERES ' . $_SESSION["tramite"]["final"]["cantidadmujeres"] . '.';
                                }
                            }
                            if ($_SESSION["tramite"]["original"]["cantidadmujerescargosdirectivos"] != $_SESSION["tramite"]["final"]["cantidadmujerescargosdirectivos"]) {
                                if ($_SESSION["tramite"]["original"]["cantidadmujeres"] != '') {
                                    $mnoticia .= 'CANTIDAD DE MUJERES EN CARGOS DIRECTIVOS CAMBIÓ DE ' . $_SESSION["tramite"]["original"]["cantidadmujerescargosdirectivos"] . ' POR ' . $_SESSION["tramite"]["final"]["cantidadmujerescargosdirectivos"] . '.';
                                } else {
                                    $mnoticia .= 'CANTIDAD DE MUJERES EN CARGOS DIRECTIVOS ' . $_SESSION["tramite"]["final"]["cantidadmujerescargosdirectivos"] . '.';
                                }
                            }
                            if ($_SESSION["tramite"]["original"]["personal"] != $_SESSION["tramite"]["final"]["personal"]) {
                                if ($_SESSION["tramite"]["original"]["personal"] != '') {
                                    $mnoticia .= 'PERSONAL OCUPADO CAMBIÓ DE ' . $_SESSION["tramite"]["original"]["personal"] . ' POR ' . $_SESSION["tramite"]["final"]["personal"] . '.';
                                } else {
                                    $mnoticia .= 'PERSONAL OCUPADO ' . $_SESSION["tramite"]["final"]["personal"] . '.';
                                }
                            }
                        }

                        //
                        if ($_SESSION["tramite"]["modcom"] == 'S' || $_SESSION["tramite"]["modnot"] == 'S') {
                            $mnoticia = '';
                            if (
                                    $_SESSION["tramite"]["original"]["dircom"] != $_SESSION["tramite"]["final"]["dircom"] ||
                                    $_SESSION["tramite"]["original"]["telcom1"] != $_SESSION["tramite"]["final"]["telcom1"] ||
                                    $_SESSION["tramite"]["original"]["telcom2"] != $_SESSION["tramite"]["final"]["telcom2"] ||
                                    $_SESSION["tramite"]["original"]["telcom3"] != $_SESSION["tramite"]["final"]["telcom3"] ||
                                    $_SESSION["tramite"]["original"]["muncom"] != $_SESSION["tramite"]["final"]["muncom"] ||
                                    $_SESSION["tramite"]["original"]["emailcom"] != $_SESSION["tramite"]["final"]["emailcom"]
                            ) {
                                $mnoticia .= '*** CAMBIÓ DATOS DE UBICACION COMERCIAL : ';
                                if ($_SESSION["tramite"]["original"]["dircom"] != $_SESSION["tramite"]["final"]["dircom"]) {
                                    $mnoticia .= 'DIRECCIÓN : ' . $_SESSION["tramite"]["original"]["dircom"] . ' POR ' . $_SESSION["tramite"]["final"]["dircom"] . '.';
                                }
                                if ($_SESSION["tramite"]["original"]["telcom1"] != $_SESSION["tramite"]["final"]["telcom1"]) {
                                    if (trim($mnoticia) != '') {
                                        $mnoticia .= ' ';
                                    }
                                    $mnoticia .= 'TELÉFONO 1 : ' . $_SESSION["tramite"]["original"]["telcom1"] . ' POR ' . $_SESSION["tramite"]["final"]["telcom1"] . '.';
                                }
                                if ($_SESSION["tramite"]["original"]["telcom2"] != $_SESSION["tramite"]["final"]["telcom2"]) {
                                    if (trim($mnoticia) != '') {
                                        $mnoticia .= ' ';
                                    }
                                    $mnoticia .= 'TELËFONO 2 : ' . $_SESSION["tramite"]["original"]["telcom2"] . ' POR ' . $_SESSION["tramite"]["final"]["telcom2"] . '.';
                                }
                                if ($_SESSION["tramite"]["original"]["telcom3"] != $_SESSION["tramite"]["final"]["telcom3"]) {
                                    if (trim($mnoticia) != '') {
                                        $mnoticia .= ' ';
                                    }
                                    $mnoticia .= 'TELËFONO 3 : ' . $_SESSION["tramite"]["original"]["telcom3"] . ' POR ' . $_SESSION["tramite"]["final"]["telcom3"] . '.';
                                }
                                if ($_SESSION["tramite"]["original"]["emailcom"] != $_SESSION["tramite"]["final"]["emailcom"]) {
                                    if (trim($mnoticia) != '') {
                                        $mnoticia .= ' ';
                                    }
                                    $mnoticia .= 'CORREO ELECTRÓNICO : ' . $_SESSION["tramite"]["original"]["emailcom"] . ' POR ' . $_SESSION["tramite"]["final"]["emailcom"] . '.';
                                }
                                if ($_SESSION["tramite"]["original"]["muncom"] != $_SESSION["tramite"]["final"]["muncom"]) {
                                    if (trim($mnoticia) != '') {
                                        $mnoticia .= ' ';
                                    }
                                    $mnoticia .= 'MUNICIPIO : ' . $_SESSION["tramite"]["original"]["muncom"] . ' - ' . retornarNombreMunicipioMysqliApi($mysqli, $_SESSION["tramite"]["original"]["muncom"]) . ' POR ' . $_SESSION["tramite"]["final"]["muncom"] . ' - ' . retornarNombreMunicipio($_SESSION["tramite"]["final"]["muncom"]) . '.';
                                }
                            }
                            if (
                                    $_SESSION["tramite"]["original"]["dirnot"] != $_SESSION["tramite"]["final"]["dirnot"] ||
                                    $_SESSION["tramite"]["original"]["munnot"] != $_SESSION["tramite"]["final"]["munnot"] ||
                                    $_SESSION["tramite"]["original"]["emailnot"] != $_SESSION["tramite"]["final"]["emailnot"]
                            ) {
                                if (trim($mnoticia) != '') {
                                    $mnoticia .= ' ';
                                }
                                $mnoticia .= '*** CAMBIÓ DATOS DE UBICACION PARA NOTIFICACION : ';
                                if ($_SESSION["tramite"]["original"]["dirnot"] != $_SESSION["tramite"]["final"]["dirnot"]) {
                                    $mnoticia .= 'DIRECCIÓN : ' . $_SESSION["tramite"]["original"]["dirnot"] . ' POR ' . $_SESSION["tramite"]["final"]["dirnot"] . '.';
                                }

                                /*
                                  if ($_SESSION["tramite"]["original"]["telnot1"] != $_SESSION["tramite"]["final"]["telnot1"]) {
                                  if (trim($mnoticia) != '') {
                                  $mnoticia .= ' ';
                                  }
                                  $mnoticia .= 'TELÉFONO 1 : ' . $_SESSION["tramite"]["original"]["telnot1"] . ' POR ' . $_SESSION["tramite"]["final"]["telnot1"] . '.';
                                  }
                                  if ($_SESSION["tramite"]["original"]["telnot2"] != $_SESSION["tramite"]["final"]["telnot2"]) {
                                  if (trim($mnoticia) != '') {
                                  $mnoticia .= ' ';
                                  }
                                  $mnoticia .= 'TELÉFONO 2 : ' . $_SESSION["tramite"]["original"]["telnot2"] . ' POR ' . $_SESSION["tramite"]["final"]["telnot2"] . '.';
                                  }
                                  if ($_SESSION["tramite"]["original"]["telnot3"] != $_SESSION["tramite"]["final"]["telnot3"]) {
                                  if (trim($mnoticia) != '') {
                                  $mnoticia .= ' ';
                                  }
                                  $mnoticia .= 'TELÉFONO 3 : ' . $_SESSION["tramite"]["original"]["telnot3"] . ' POR ' . $_SESSION["tramite"]["final"]["telnot3"] . '.';
                                  }
                                 */

                                if ($_SESSION["tramite"]["original"]["emailnot"] != $_SESSION["tramite"]["final"]["emailnot"]) {
                                    if (trim($mnoticia) != '') {
                                        $mnoticia .= ' ';
                                    }
                                    $mnoticia .= 'CORREO ELECTRÓNICO : ' . $_SESSION["tramite"]["original"]["emailnot"] . ' POR ' . $_SESSION["tramite"]["final"]["emailnot"] . '.';
                                }
                                if ($_SESSION["tramite"]["original"]["munnot"] != $_SESSION["tramite"]["final"]["munnot"]) {
                                    if (trim($mnoticia) != '') {
                                        $mnoticia .= ' ';
                                    }
                                    $mnoticia .= 'MUNICIPIO : ' . $_SESSION["tramite"]["original"]["munnot"] . ' - ' . retornarNombreMunicipioMysqliApi($mysqli, $_SESSION["tramite"]["original"]["munnot"]) . ' POR ' . $_SESSION["tramite"]["final"]["munnot"] . ' - ' . retornarNombreMunicipio($_SESSION["tramite"]["final"]["munnot"]) . '.';
                                }
                            }
                        }

                        //
                        if ($cambioNombre == 'si') {
                            $mnoticia = 'CAMBIÓ EL NOMBRE DEL ESTABLECIMIENTO DE : ' . $_SESSION["tramite"]["original"]["nombre"] . ' POR ' . $_SESSION["tramite"]["final"]["nombre"];
                        }
                    }

                    $xInsc++;
                    $xInscripciones[$xInsc]["tiporegistro"] = $mtiporegistro;
                    $xInscripciones[$xInsc]["tiposello"] = $mtiposello;
                    $xInscripciones[$xInsc]["libro"] = $mlibro;
                    $xInscripciones[$xInsc]["numreg"] = '';
                    $xInscripciones[$xInsc]["organizacion"] = $orgExpediente;
                    $xInscripciones[$xInsc]["filesello"] = '';
                    $xInscripciones[$xInsc]["noticia"] = $mnoticia;
                    $xInscripciones[$xInsc]["matricula"] = $matExpediente;
                    $xInscripciones[$xInsc]["proponente"] = '';
                    $xInscripciones[$xInsc]["tipodoc"] = '06';
                    $xInscripciones[$xInsc]["numdoc"] = 'N/A';
                    $xInscripciones[$xInsc]["origendoc"] = 'EL COMERCIANTE O INSCRITO';
                    $xInscripciones[$xInsc]["municipio"] = $_SESSION["tramite"]["municipiopagador"];
                    $xInscripciones[$xInsc]["acto"] = $macto;
                    $xInscripciones[$xInsc]["fecha"] = date("Ymd");
                    $xInscripciones[$xInsc]["hora"] = date("His");
                    $xInscripciones[$xInsc]["fechadoc"] = date("Ymd");
                    $xInscripciones[$xInsc]["nombre"] = $razExpediente;
                    $xInscripciones[$xInsc]["tipoidentificacion"] = $tideExpediente;
                    $xInscripciones[$xInsc]["identificacion"] = $ideExpediente;
                    $xInscripciones[$xInsc]["ope"] = $opeSecJur;
                    $xInscripciones[$xInsc]["bandeja"] = $mtipobandeja;
                }
            }
        }
        \logApi::general2($nameLog, $idSolicitudPago, \funcionesGenerales::utf8_encode('Armo lista de inscripciones (mutaciones)'));

        // ************************************************************************************************************** //
        // 2017-11-28: JINT: Evalua si se trata de una reliquidacion que a su vez
        // genera cobro por mutación, de ser así genera la inscripción en el libro
        // ************************************************************************************************************** //  
        if (($_SESSION["tramite"]["tipotramite"] == 'renovacionmatricula' || $_SESSION["tramite"]["tipotramite"] == 'renovacionesadl') && $_SESSION["tramite"]["reliquidacion"] == 'si') {
            foreach ($_SESSION["tramite"]["liquidacion"] as $l) {
                if ($l["expediente"] != '') {
                    if ($l["cc"] == '' || $l["cc"] == CODIGO_EMPRESA) {
                        $srv = retornarRegistroMysqliApi($mysqli, 'mreg_servicios', "idservicio='" . $l["idservicio"] . "'");
                        if ($srv["tipoingreso"] == '07' || $srv["tipoingreso"] == '17') {
                            $exp = retornarRegistroMysqliApi($mysqli, 'mreg_est_inscritos', "matricula='" . $l["expediente"] . "'");
                            // $exp = retornarRegistro('mreg_est_inscritos', "matricula='" . $l["expediente"] . "'");
                            $mtiporegistro = 'RegMer';
                            $mtiposello = TIPO_DOC_SELLO_MERCANTIL;
                            $mtipobandeja = '4.-REGMER';
                            $mlibro = 'RM15';
                            $macto = '0744';
                            $mnoticia = 'MUTACION DE INFORMACION FINANCIERA (RELIQUIDACION)';

                            if (
                                    $exp["ciiu1"] == 'R9200' ||
                                    $exp["ciiu2"] == 'R9200' ||
                                    $exp["ciiu3"] == 'R9200' ||
                                    $exp["ciiu4"] == 'R9200'
                            ) {
                                if ($exp["organizacion"] == '01' || ($exp["organizacion"] > '02' && $exp["categoria"] == '1')) {
                                    $mlibro = 'RM22';
                                    $macto = '0744';
                                }
                            }

                            if ($exp["organizacion"] == '12' && $exp["categoria"] == '1') {
                                $mlibro = 'RE51';
                                $mtipobandeja = '5.-REGESADL';
                            }
                            if ($exp["organizacion"] == '14' && $exp["categoria"] == '1') {
                                $mlibro = 'RE53';
                                $mtipobandeja = '5.-REGESADL';
                            }

                            $xInsc++;
                            $xInscripciones[$xInsc]["tiporegistro"] = $mtiporegistro;
                            $xInscripciones[$xInsc]["tiposello"] = $mtiposello;
                            $xInscripciones[$xInsc]["libro"] = $mlibro;
                            $xInscripciones[$xInsc]["numreg"] = '';
                            $xInscripciones[$xInsc]["organizacion"] = $orgExpediente;
                            $xInscripciones[$xInsc]["filesello"] = '';
                            $xInscripciones[$xInsc]["noticia"] = $mnoticia;
                            $xInscripciones[$xInsc]["matricula"] = $l["expediente"];
                            $xInscripciones[$xInsc]["proponente"] = '';
                            $xInscripciones[$xInsc]["tipodoc"] = '06';
                            $xInscripciones[$xInsc]["numdoc"] = 'N/A';
                            $xInscripciones[$xInsc]["origendoc"] = 'EL COMERCIANTE O INSCRITO';
                            $xInscripciones[$xInsc]["municipio"] = $_SESSION["tramite"]["municipiopagador"];
                            $xInscripciones[$xInsc]["acto"] = $macto;
                            $xInscripciones[$xInsc]["fecha"] = date("Ymd");
                            $xInscripciones[$xInsc]["hora"] = date("His");
                            $xInscripciones[$xInsc]["fechadoc"] = date("Ymd");
                            $xInscripciones[$xInsc]["nombre"] = $exp["razonsocial"];
                            $xInscripciones[$xInsc]["tipoidentificacion"] = $exp["idclase"];
                            $xInscripciones[$xInsc]["identificacion"] = $exp["numid"];
                            $xInscripciones[$xInsc]["ope"] = $opeSecJur;
                            $xInscripciones[$xInsc]["bandeja"] = $mtipobandeja;
                            $xInscripciones[$xInsc]["noticia"] = $mnoticia;
                        }
                    }
                }
            }
        }
        \logApi::general2($nameLog, $idSolicitudPago, \funcionesGenerales::utf8_encode('Armo lista de inscripciones (reliquidacion de activos)'));

        //  ************************************************************************************************ //
        // Si el tramite es de proponente como inscripcion, renovacion, actualizacion o cambio de domicilio
        // Actualiza el xml del formulario en la tabla mreg_radicacionesdatos
        //  ************************************************************************************************ //
        if ($grupoServicios == 'RegPro') {
            $xmlDatos = retornarRegistroMysqliApi($mysqli, 'mreg_liquidaciondatos', "idliquidacion=" . $_SESSION["tramite"]["idliquidacion"], "xml");
            if ($xmlDatos === false) {
                $xmlDatos = '';
            }
            if ($xmlDatos != '0' && $xmlDatos != '') {
                $resx = \funcionesRegistrales::actualizarMregRadicacionesDatos($mysqli, ltrim($_SESSION["generales"]["codigobarras1"], '0'), '000', $_SESSION["tramite"]["tipotramite"], '', '05', $xmlDatos);
                if ($resx === false) {
                    \logApi::general2($nameLog, $idSolicitudPago, \funcionesGenerales::utf8_encode('Error en traslado XML desde mreg_liquidaciondatos a mreg_radicionesdatos, codigo de barras ' . $_SESSION["generales"]["codigobarras1"] . ' : ' . $xmlDatos));
                }
            } else {
                \logApi::general2($nameLog, $idSolicitudPago, \funcionesGenerales::utf8_encode('No encontro XML del tramite de proponentes para trasladar desde mreg_liquidaciondatos a mreg_radicacionesdatos, codigo de barras ' . $_SESSION["generales"]["codigobarras1"]));
            }
        }
        \logApi::general2($nameLog, $idSolicitudPago, \funcionesGenerales::utf8_encode('Actualizo mreg_radicacionesdatos desde mreg_liquidaciondatos cuando sea un tramite de proponentes'));

        //
        if ($grupoServicios == 'RegMer' || $grupoServicios == 'RegEsadl') {
            $xsec = 0;
            borrarRegistrosMysqliApi($mysqli, 'mreg_radicacionesdatos', "idradicacion='" . $_SESSION["generales"]["codigobarras1"] . "'");
            $xmlDatos = retornarRegistrosMysqliApi($mysqli, 'mreg_liquidaciondatos', "idliquidacion=" . $_SESSION["tramite"]["idliquidacion"], "secuencia");
            if ($xmlDatos && !empty($xmlDatos)) {
                foreach ($xmlDatos as $xmld) {
                    $xsec++;
                    $arrCampos = array(
                        'idradicacion',
                        'secuencia',
                        'tipotramite',
                        'expediente',
                        'idestado',
                        'xml'
                    );
                    $arrValues = array(
                        "'" . $_SESSION["generales"]["codigobarras1"] . "'",
                        "'" . sprintf("%03s", $xsec) . "'",
                        "'" . $_SESSION["tramite"]["tipotramite"] . "'",
                        "'" . $xmld["expediente"] . "'",
                        "'05'",
                        "'" . addslashes($xmld["xml"]) . "'"
                    );
                    $result1x = insertarRegistrosMysqliApi($mysqli, 'mreg_radicacionesdatos', $arrCampos, $arrValues);
                    if ($result1x === false) {
                        \logApi::general2($nameLog, $idSolicitudPago, 'Error creando mreg_radicacionesdatos: ' . $_SESSION["generales"]["mensajeerror"]);
                    }
                }
            } else {
                \logApi::general2($nameLog, $idSolicitudPago, 'No encontraron XMLs para trasladar desde mreg_liquidaciondatos a mreg_radicacionesdatos, codigo de barras ' . $_SESSION["generales"]["codigobarras1"]);
            }
        }
        \logApi::general2($nameLog, $idSolicitudPago, 'Traslado datos del formulario de liquidaciondatos a radicacionanexos en caso de tramites con formulario');

        //  ************************************************************************************* //
        // Si el tramite es cancelacion de matricula
        // Crea la cancelacion en el sistema 
        // Siempre y cuando el usuario que hace el trámite no sea un usuario público
        //  ************************************************************************************* //
        if ($_SESSION["tramite"]["tipotramite"] != 'inscripciondocumentos' && $esCancelacion == 'si' && $registroInmediato == 'si') {
            if ($_SESSION["generales"]["tipousuario"] != '00' && $_SESSION["generales"]["tipousuario"] != '06') {
                $xInsc++;
                $xInscripciones[$xInsc] = \funcionesRegistrales::adicionarInscripcionCancelacionIndividual($mysqli, $_SESSION["tramite"]["idexpedientebase"], sprintf("%02s", $_SESSION["tramite"]["idmotivocancelacion"], $_SESSION["tramite"]["motivocancelacion"]));
            }
            \funcionesRegistrales::actualizarMregEstInscritosCampo($mysqli, $xInscripciones[$xInsc]["matricula"], 'ctrestmatricula', 'MC', 'varchar', $_SESSION["generales"]["codigobarras1"], $_SESSION["tramite"]["tipotramite"], $_SESSION["tramite"]["numerorecibo"]);
            \logApi::general2($nameLog, $idSolicitudPago, \funcionesGenerales::utf8_encode('Cancelo matricula : ' . $xInscripciones[$xInsc]["matricula"]));

            // 2016-08-26 : JINT : Actualiza fecha de cancelacion en mreg_est_inscritos
            \funcionesRegistrales::actualizarMregEstInscritosCampo($mysqli, $xInscripciones[$xInsc]["matricula"], 'feccancelacion', date("Ymd"), 'varchar', $_SESSION["generales"]["codigobarras1"], $_SESSION["tramite"]["tipotramite"], $_SESSION["tramite"]["numerorecibo"]);
            \logApi::general2($nameLog, $idSolicitudPago, \funcionesGenerales::utf8_encode('Actualizado fecha de cancelacion en mreg_est_inscritos : ' . $xInscripciones[$xInsc]["matricula"]));

            // 2024-03-25 : JINT : Actualiza el motivo de la cancelación en  mreg_est_inscritos
            \funcionesRegistrales::actualizarMregEstInscritosCampo($mysqli, $xInscripciones[$xInsc]["matricula"], 'motivocancelacion', $_SESSION["tramite"]["idmotivocancelacion"], 'varchar', $_SESSION["generales"]["codigobarras1"], $_SESSION["tramite"]["tipotramite"], $_SESSION["tramite"]["numerorecibo"]);
            \logApi::general2($nameLog, $idSolicitudPago, \funcionesGenerales::utf8_encode('Actualizado motivo de cancelacion en mreg_est_inscritos : ' . $xInscripciones[$xInsc]["matricula"]));

            // 2024-03-25 : JINT : Actualiza la descripción del motivo de la cancelación en  mreg_est_inscritos_campos
            \funcionesRegistrales::actualizarMregEstInscritosCampoCampos($mysqli, $xInscripciones[$xInsc]["matricula"], 'descripcionmotivocancelacion', $_SESSION["tramite"]["motivocancelacion"], $_SESSION["generales"]["codigobarras1"], $_SESSION["tramite"]["tipotramite"], $_SESSION["tramite"]["numerorecibo"]);
            \logApi::general2($nameLog, $idSolicitudPago, \funcionesGenerales::utf8_encode('Actualizado la descripcion del motivo de cancelacion en mreg_est_inscritos_campos : ' . $xInscripciones[$xInsc]["matricula"]));

            // 2016-11-30 : JINT : En caso de un afiliado, lo marca como ex-afiliado
            //if (retornarRegistro('mreg_est_inscritos', "matricula='" . $xInscripciones[$xInsc]["matricula"] . "'", "ctrafiliacion") == '1') {
            if (retornarRegistroMysqliApi($mysqli, "mreg_est_inscritos", "matricula='" . $xInscripciones[$xInsc]["matricula"] . "'", "ctrafiliacion") == '1') {
                \funcionesRegistrales::actualizarMregEstInscritosCampo($mysqli, $xInscripciones[$xInsc]["matricula"], 'ctrafiliacion', 2, 'varchar', $_SESSION["generales"]["codigobarras1"], $_SESSION["tramite"]["tipotramite"], $_SESSION["tramite"]["numerorecibo"]);
                \funcionesRegistrales::actualizarMregEstInscritosCampo($mysqli, $xInscripciones[$xInsc]["matricula"], 'motivodesafiliacion', 2, 'varchar', $_SESSION["generales"]["codigobarras1"], $_SESSION["tramite"]["tipotramite"], $_SESSION["tramite"]["numerorecibo"]);
                \funcionesRegistrales::actualizarMregEstInscritosCampo($mysqli, $xInscripciones[$xInsc]["matricula"], 'txtmotivodesafiliacion', 'CANCELACION DE LA MATRICULA MERCANTIL', 'varchar', $_SESSION["generales"]["codigobarras1"], $_SESSION["tramite"]["tipotramite"], $_SESSION["tramite"]["numerorecibo"]);
                \logApi::general2($nameLog, $idSolicitudPago, \funcionesGenerales::utf8_encode('Actualizado estado de ctrafiliacion a 2 en mreg_est_inscritos : ' . $xInscripciones[$xInsc]["matricula"]));
            }
        }

        //
        if ($_SESSION["tramite"]["tipotramite"] == 'inscripciondocumentos') {
            foreach ($_SESSION["tramite"]["transacciones"] as $tradat) {
                $tramae = retornarRegistroMysqliApi($mysqli, 'mreg_transacciones', "idcampo='" . $tradat["idtransaccion"] . "'");
                $tiptra = retornarRegistroMysqliApi($mysqli, 'bas_tipotramites', "id='" . $tramae["tipotramite"] . "'");
                if ($tiptra["escancelacion"] == 'si' && $tiptra["registroinmediato"] == 'si') {
                    if ($_SESSION["generales"]["tipousuario"] != '00' && $_SESSION["generales"]["tipousuario"] != '06') {
                        $cancambidom = 'no';
                        if ($tramae["idacto1"] != '') {
                            $xInsc++;
                            $xInscripciones[$xInsc] = \funcionesRegistrales::adicionarInscripcionCancelacion($mysqli, $tramae, $tradat, $tiptra, 1);
                            if (substr($tramae["idacto1"], 5, 4) == '0532') {
                                $cancambidom = 'si';
                            }
                        }

                        if ($tramae["idacto2"] != '') {
                            $xInsc++;
                            $xInscripciones[$xInsc] = \funcionesRegistrales::adicionarInscripcionCancelacion($mysqli, $tramae, $tradat, $tiptra, 2);
                            if (substr($tramae["idacto2"], 5, 4) == '0532') {
                                $cancambidom = 'si';
                            }
                        }

                        if ($tramae["idacto3"] != '') {
                            $xInsc++;
                            $xInscripciones[$xInsc] = \funcionesRegistrales::adicionarInscripcionCancelacion($mysqli, $tramae, $tradat, $tiptra, 3);
                            if (substr($tramae["idacto3"], 5, 4) == '0532') {
                                $cancambidom = 'si';
                            }
                        }

                        if ($tramae["idacto4"] != '') {
                            $xInsc++;
                            $xInscripciones[$xInsc] = \funcionesRegistrales::adicionarInscripcionCancelacion($mysqli, $tramae, $tradat, $tiptra, 4);
                            if (substr($tramae["idacto4"], 5, 4) == '0532') {
                                $cancambidom = 'si';
                            }
                        }

                        if ($tramae["idacto5"] != '') {
                            $xInsc++;
                            $xInscripciones[$xInsc] = \funcionesRegistrales::adicionarInscripcionCancelacion($mysqli, $tramae, $tradat, $tiptra, 5);
                            if (substr($tramae["idacto5"], 5, 4) == '0532') {
                                $cancambidom = 'si';
                            }
                        }

                        // 2016-08-26 : JINT : Actualiza el estado en mreg_est_inscritos
                        if (substr($xInscripciones[$xInsc]["matricula"], 0, 1) == 'S') {
                            \funcionesRegistrales::actualizarMregEstInscritosCampo($mysqli, $xInscripciones[$xInsc]["matricula"], 'ctrestmatricula', 'IC', 'varchar', $_SESSION["generales"]["codigobarras1"], $_SESSION["tramite"]["tipotramite"], $_SESSION["tramite"]["numerorecibo"]);
                        } else {
                            if ($cancambidom == 'si') {
                                \funcionesRegistrales::actualizarMregEstInscritosCampo($mysqli, $xInscripciones[$xInsc]["matricula"], 'ctrestmatricula', 'MF', 'varchar', $_SESSION["generales"]["codigobarras1"], $_SESSION["tramite"]["tipotramite"], $_SESSION["tramite"]["numerorecibo"]);
                            } else {
                                \funcionesRegistrales::actualizarMregEstInscritosCampo($mysqli, $xInscripciones[$xInsc]["matricula"], 'ctrestmatricula', 'MC', 'varchar', $_SESSION["generales"]["codigobarras1"], $_SESSION["tramite"]["tipotramite"], $_SESSION["tramite"]["numerorecibo"]);
                            }
                        }
                        \logApi::general2($nameLog, $idSolicitudPago, \funcionesGenerales::utf8_encode('Actualizado estado de la matricula a MC/IC/MF : ' . $xInscripciones[$xInsc]["matricula"]));

                        // 2016-08-26 : JINT : Actualiza fecha de cancelacion en mreg_est_inscritos
                        \funcionesRegistrales::actualizarMregEstInscritosCampo($mysqli, $xInscripciones[$xInsc]["matricula"], 'feccancelacion', date("Ymd"), 'varchar', $_SESSION["generales"]["codigobarras1"], $_SESSION["tramite"]["tipotramite"], $_SESSION["tramite"]["numerorecibo"]);
                        \logApi::general2($nameLog, $idSolicitudPago, \funcionesGenerales::utf8_encode('Actualizado fecha de cancelacion en mreg_est_inscritos : ' . $xInscripciones[$xInsc]["matricula"]));

                        // 2016-11-30 : JINT : En caso de un afiliado, lo marca como ex-afiliado
                        //if (retornarRegistro('mreg_est_inscritos', "matricula='" . $xInscripciones[$xInsc]["matricula"] . "'", "ctrafiliacion") == '1') {
                        if (retornarRegistroMysqliApi($mysqli, "mreg_est_inscritos", "matricula='" . $xInscripciones[$xInsc]["matricula"] . "'", "ctrafiliacion") == '1') {
                            \funcionesRegistrales::actualizarMregEstInscritosCampo($mysqli, $xInscripciones[$xInsc]["matricula"], 'ctrafiliacion', 2, 'varchar', $_SESSION["generales"]["codigobarras1"], $_SESSION["tramite"]["tipotramite"], $_SESSION["tramite"]["numerorecibo"]);
                            \logApi::general2($nameLog, $idSolicitudPago, \funcionesGenerales::utf8_encode('Actualizado estado de ctrafiliacion a 2 en mreg_est_inscritos : ' . $xInscripciones[$xInsc]["matricula"]));
                        }
                    }
                }
            }
        }

        // ******************************************************************************************************** //
        // Genera inscripciones en libros
        // Cuando la transacción es automática
        // ******************************************************************************************************** //
        if ($xInsc != 0) {
            $xInsc = 0;
            foreach ($xInscripciones as $x) {
                if ($x["fecha"] == '') {
                    $x["fecha"] = date("Ymd");
                }
                if ($x["fechadoc"] == '') {
                    $x["fechadoc"] = date("Ymd");
                }

                // **************************************************************************** //
                // Si la controla SII
                // **************************************************************************** //            
                $numInsc = \funcionesRegistrales::generarSecuenciaLibros($mysqli, $x["libro"]);
                if (ltrim($numInsc, "0") == '' || $numInsc === false) {
                    \logApi::general2($nameLog, $idSolicitudPago, \funcionesGenerales::utf8_encode('Error recuperando secuencia en mreg_secuencias para el libro : ' . $x["libro"]) . ' ' . $_SESSION["generales"]["mensajeerror"]);
                }

                //
                if ($numInsc && $numInsc > 0) {
                    $respuestaLibro = array();
                    $respuestaLibro["codigoError"] = '0000';
                    $respuestaLibro["msgError"] = '';
                    $respuestaLibro["renglon"] = array();
                    $respuestaLibro["renglon"][1] = array(
                        'numreg' => $numInsc,
                        'dupli' => '01',
                        'fecha' => date("Ymd"),
                        'hora' => date("His")
                    );

                    // **************************************************************************** //
                    // Crea el libro en mreg_est_inscripciones
                    // **************************************************************************** //
                    $arrCampos = array(
                        'libro',
                        'registro',
                        'dupli',
                        'fecharegistro',
                        'horaregistro',
                        'matricula',
                        'organizacion',
                        'categoria',
                        'naturaleza',
                        'proponente',
                        'tipoidentificacion',
                        'identificacion',
                        'nombre',
                        'fechadocumento',
                        'origendocumento',
                        'municipiodocumento',
                        'paisdocumento',
                        'numerodocumento',
                        'numdocextenso',
                        'tipodocumento',
                        'tipolibro',
                        'idcodlibro',
                        'codigolibro',
                        'descripcionlibro',
                        'paginainicial',
                        'numeropaginas',
                        'acto',
                        'noticia',
                        'operador',
                        'recibo',
                        'numerooperacion',
                        'idradicacion',
                        'fecharadicacion',
                        'usuarioinscribe',
                        'usuariofirma',
                        'firma',
                        'clavefirmado',
                        'ctrrotulo',
                        'ctrrecurso',
                        'ctrnotificacion',
                        'timestampnotificacionemail',
                        'timestampnotificacionsms',
                        'ctrrevoca',
                        'registrorevocacion',
                        'ctrsello',
                        'idanexosello',
                        'idnotificacionemail',
                        'idnotificacionsms',
                        'estado'
                    );

                    if (!isset($x["categoria"])) {
                        $x["categoria"] = '';
                    }
                    if (!isset($x["naturaleza"])) {
                        $x["naturaleza"] = '';
                    }

                    $arrValores = array(
                        "'" . $x["libro"] . "'",
                        "'" . $respuestaLibro["renglon"][1]["numreg"] . "'",
                        "'" . sprintf("%02s", $respuestaLibro["renglon"][1]["dupli"]) . "'",
                        "'" . $respuestaLibro["renglon"][1]["fecha"] . "'",
                        "'" . $respuestaLibro["renglon"][1]["hora"] . "'",
                        "'" . ltrim($x["matricula"], "0") . "'",
                        "'" . $x["organizacion"] . "'",
                        "'" . $x["categoria"] . "'",
                        "'" . $x["naturaleza"] . "'",
                        "'" . ltrim($x["proponente"], "0") . "'",
                        "'" . $x["tipoidentificacion"] . "'",
                        "'" . $x["identificacion"] . "'",
                        "'" . addslashes($x["nombre"]) . "'",
                        "'" . $x["fechadoc"] . "'",
                        "'" . addslashes($x["origendoc"]) . "'",
                        "'" . $x["municipio"] . "'",
                        "'0169'",
                        "'" . $x["numdoc"] . "'",
                        "''",
                        "'" . $x["tipodoc"] . "'",
                        "''", // Tipo libro
                        "''", // idcodlibro
                        "''", // codigolibro
                        "''", // descripcionlibro
                        0, // paginainicial
                        0, // numeropaginas
                        "'" . $x["acto"] . "'",
                        "'" . addslashes($x["noticia"]) . "'",
                        "'" . $x["ope"] . "'",
                        "'" . $_SESSION["tramite"]["numerorecibo"] . "'",
                        "'" . $_SESSION["tramite"]["numerooperacion"] . "'",
                        "'" . ltrim($_SESSION["generales"]["codigobarras1"], "0") . "'",
                        "'" . $_SESSION["tramite"]["fecharecibo"] . "'",
                        "'" . $_SESSION["generales"]["codigousuario"] . "'", // usuarioinscribe
                        "''", // usuariofirma
                        "''", // firma
                        "''", // clavefirmado
                        "'0'", // ctrrotulo
                        "'0'", // ctrrecurso
                        "'0'", // ctrnotificacion
                        "''", // timestampnotificacionemail
                        "''", // timestampnotificacionsms
                        "'0'", // ctrrevoca
                        "''", // registrorevocacion
                        "'0'", // ctrsello
                        0, // idanexosello
                        0, // idnotificacionemail
                        0, // idnotificacionsms
                        "'V'"
                    );

                    $librocamnom = $x["libro"];
                    $inscripcioncamnom = $respuestaLibro["renglon"][1]["numreg"];
                    $duplicamnom = $respuestaLibro["renglon"][1]["dupli"];
                    $matcamnom = ltrim($x["matricula"], "0");

                    //
                    if (contarRegistrosMysqliApi($mysqli, 'mreg_est_inscripciones', "libro='" . $x["libro"] . "' and registro='" . $respuestaLibro["renglon"][1]["numreg"] . "' and dupli='" . $respuestaLibro["renglon"][1]["dupli"] . "'") == 0) {
                        $res = insertarRegistrosMysqliApi($mysqli, 'mreg_est_inscripciones', $arrCampos, $arrValores);
                    } else {
                        $res = regrabarRegistrosMysqliApi($mysqli, 'mreg_est_inscripciones', $arrCampos, $arrValores, "libro='" . $x["libro"] . "' and registro='" . $respuestaLibro["renglon"][1]["numreg"] . "' and dupli='" . $respuestaLibro["renglon"][1]["dupli"] . "'");
                    }

                    // **************************************************************************** //
                    // 2019-06-07: JINT: Si la inscripción es una constitución, una reforma, 
                    // un nombramiento, una disolución, una liquidación, una fusión, etc.,
                    // marca la matrícula como pendiente de ajusta al nuevo formato
                    // Siempre y cuando la fehca sea igual o superior al parámetro del commonXX
                    // **************************************************************************** //
                    \funcionesRegistrales::actualizarMregInscritosPendienteNuevoCertificado($mysqli, $x["libro"], $x["acto"], ltrim($x["matricula"], "0"));

                    // **************************************************************************** //
                    // Crea el libro en mreg_inscripciones
                    // **************************************************************************** //
                    $arrCampos = array(
                        'libro',
                        'numreg',
                        'dupli',
                        'fecha',
                        'hora',
                        'tipodoc',
                        'numdoc',
                        'fechadoc',
                        'txtorigendoc',
                        'municipiodoc',
                        'paisdoc',
                        'acto',
                        'matricula',
                        'proponente',
                        'tipoidentificacion',
                        'identificacion',
                        'nombre',
                        'idlibrocomercio',
                        'codlibrocomercio',
                        'numhojas',
                        'noticia',
                        'textoanterior',
                        'textonuevo',
                        'idliquidacion',
                        'numeroradicacion',
                        'numerorecibo',
                        'operador',
                        'numerooperacion',
                        'fecharadicacion',
                        'usuario',
                        'ctrrevocacion',
                        'numregrevocacion',
                        'ctrrecurso',
                        'numrecurso',
                        'firma',
                        'clavefirmado',
                        'numeropublicacionrue',
                        'fechapublicacionrue',
                        'horapublicacionrue',
                        'fechafirmeza',
                        'idestado',
                        'ctrnotificacion',
                        'ctrsello'
                    );

                    $arrValores = array(
                        "'" . $x["libro"] . "'",
                        "'" . $respuestaLibro["renglon"][1]["numreg"] . "'",
                        "'" . $respuestaLibro["renglon"][1]["dupli"] . "'",
                        "'" . $respuestaLibro["renglon"][1]["fecha"] . "'",
                        "'" . $respuestaLibro["renglon"][1]["hora"] . "'",
                        "'" . $x["tipodoc"] . "'",
                        "'" . $x["numdoc"] . "'",
                        "'" . $x["fechadoc"] . "'",
                        "'" . addslashes($x["origendoc"]) . "'",
                        "'" . $x["municipio"] . "'",
                        "'0169'",
                        "'" . $x["acto"] . "'",
                        "'" . ltrim($x["matricula"], "0") . "'",
                        "''",
                        "'" . $x["tipoidentificacion"] . "'",
                        "'" . $x["identificacion"] . "'",
                        "'" . addslashes($x["nombre"]) . "'",
                        "''", // idcodlibro
                        "''", // codigolibro
                        0, // numeropaginas
                        "'" . addslashes($x["noticia"]) . "'",
                        "''",
                        "''",
                        0,
                        "'" . ltrim($_SESSION["generales"]["codigobarras1"], "0") . "'",
                        "'" . $_SESSION["tramite"]["numerorecibo"] . "'",
                        "'" . $x["ope"] . "'", // Operador
                        "'" . $_SESSION["tramite"]["numerooperacion"] . "'",
                        "'" . $_SESSION["tramite"]["fecharecibo"] . "'",
                        "'" . $_SESSION["generales"]["codigousuario"] . "'", // Usuario inscribe
                        "'0'", // ctrrevoca
                        "''", // numero registro que soporta la revocación
                        "'0'", // ctrrecurso
                        0, // numero del recurso
                        "''", // firma
                        "''", // clavefirmado
                        "''", // numeropublicacionrue
                        "''", // fechapublicacionrue
                        "''", // hora publicacion rue
                        "''", // fechafirmeza
                        "'00'", // Estado registrado
                        "'0'", // ctrnotificacion
                        "'0'" // ctrsello
                    );

                    if (contarRegistrosMysqliApi($mysqli, 'mreg_inscripciones', "libro='" . $x["libro"] . "' and numreg='" . $respuestaLibro["renglon"][1]["numreg"] . "' and dupli='" . $respuestaLibro["renglon"][1]["dupli"] . "'") == 0) {
                        insertarRegistrosMysqliApi($mysqli, 'mreg_inscripciones', $arrCampos, $arrValores);
                    } else {
                        regrabarRegistrosMysqliApi($mysqli, 'mreg_inscripciones', $arrCampos, $arrValores, "libro='" . $x["libro"] . "' and numreg='" . $respuestaLibro["renglon"][1]["numreg"] . "' and dupli='" . $respuestaLibro["renglon"][1]["dupli"] . "'");
                    }

                    //
                    $txt = '';
                    foreach ($x as $key => $valor) {
                        if ($key == 'numreg') {
                            $valor = $respuestaLibro["renglon"][1]["numreg"];
                        }
                        $txt .= $key . ' = ' . $valor . "\r\n";
                    }

                    //
                    if ($res === false) {
                        \logApi::general2($nameLog, $idSolicitudPago, \funcionesGenerales::utf8_encode('Error generando inscripciones en libros (mreg_est_inscripciones) : ' . $txt . "\r\n" . $_SESSION["generales"]["mensajeerror"]));
                    }
                    $xInsc++;
                    if ($respuestaLibro["codigoError"] != '0000') {
                        $txt = '';
                        foreach ($x as $key => $valor) {
                            if ($key == 'numreg') {
                                $valor = $respuestaLibro["renglon"][1]["numreg"];
                            }
                            $txt .= $key . ' = ' . $valor . "\r\n";
                        }
                        \logApi::general2($nameLog, $idSolicitudPago, \funcionesGenerales::utf8_encode('Error generando inscripciones en libros : ' . $txt . "\r\n" . $respuestaLibro["msgError"]));
                    } else {
                        $xInscripciones[$xInsc]["numreg"] = $respuestaLibro["renglon"][1]["numreg"];
                        $xInscripciones[$xInsc]["dupli"] = $respuestaLibro["renglon"][1]["dupli"];
                        $xInscripciones[$xInsc]["fechareg"] = $respuestaLibro["renglon"][1]["fecha"];
                        $xInscripciones[$xInsc]["horareg"] = $respuestaLibro["renglon"][1]["hora"];
                        $txt = '';
                        foreach ($xInscripciones[$xInsc] as $key => $valor) {
                            $txt .= $key . ' = ' . $valor . "\r\n";
                        }
                        \logApi::general2($nameLog, $idSolicitudPago, \funcionesGenerales::utf8_encode('Genero inscripciones en libros : ' . $txt . "\r\n"));

                        // *********************************************************************************** //
                        // Adiciona acto a la tabla mreg_estudio_actos_registro
                        // Con los actos generados en forma automática
                        // Esto se hace con el objeto que al llegar al módulo de digitación, si es del caso
                        // Aparezcan relacionados la totalidad de los actos para digitar o revisar.
                        // *********************************************************************************** //
                        if ($_SESSION["generales"]["codigousuario"] == 'USUPUBXX') {
                            $usuXX = $opeSecJur;
                        } else {
                            $usuXX = $_SESSION["generales"]["codigousuario"];
                        }

                        $arrCampos = array(
                            'idradicacion',
                            'idliquidacion',
                            'idsecuenciatransaccion',
                            'idtransaccion',
                            'orden',
                            'matricula',
                            'proponente',
                            'idclase',
                            'numid',
                            'nombre',
                            'idclaseacreedor',
                            'numidacreedor',
                            'nombreacreedor',
                            'idclasedemandante1',
                            'numiddemandante1',
                            'nombredemandante1',
                            'idclasedemandante2',
                            'numiddemandante2',
                            'nombredemandante2',
                            'idclasedemandante3',
                            'numiddemandante3',
                            'nombredemandante3',
                            'tipodoc',
                            'numdoc',
                            'fechadoc',
                            'fechavenci',
                            'mundoc',
                            'txtorigendoc',
                            'dirubi',
                            'munubi',
                            'valor',
                            'descripcion',
                            'libro',
                            'acto',
                            'noticia',
                            'librocruce',
                            'inscripcioncruce',
                            'nuevaorganizacion',
                            'nuevacategoria',
                            'nuevonombre',
                            'nuevafechaduracion',
                            'camaraanterior',
                            'matriculaanterior',
                            'fechamatriculaanterior',
                            'fecharenovacionanterior',
                            'ultimoanorenovadoanterior',
                            'municipioanterior',
                            'benart7anterior',
                            'personal',
                            'acttot',
                            'pastot',
                            'pattot',
                            'actvin',
                            'clase_libro',
                            'tipo_libro',
                            'codigo_libro',
                            'nombre_libro',
                            'email_libro',
                            'emailconfirmacion_libro',
                            'paginainicial_libro',
                            'paginafinal_libro',
                            'actanro_libro',
                            'fechaacta_libro',
                            'motivocancelacion',
                            'numlibro',
                            'numreg',
                            'dupli',
                            'fechareg',
                            'horareg',
                            'usuario'
                        );

                        $arrValores = array(
                            "'" . ltrim($_SESSION["generales"]["codigobarras1"], "0") . "'", // Codigo de barras
                            $_SESSION["tramite"]["idliquidacion"], // Numero liquidacion
                            0, // idsecuenciatransaccion
                            "''", // idtransaccion
                            "'" . sprintf("%02s", $xInsc) . "'", // orden
                            "'" . ltrim($x["matricula"], "0") . "'", // Matricula
                            "'" . ltrim($x["proponente"], "0") . "'", // Proponente
                            "'" . trim($x["tipoidentificacion"]) . "'", // idclase
                            "'" . ltrim($x["identificacion"], "0") . "'", // numid
                            "'" . addslashes(trim($x["nombre"])) . "'", // nombre
                            "''", // idclaseacreedor
                            "''", // numidacreddor
                            "''", // nombreacreedor
                            "''", // idclasedemandante1
                            "''", // numiddemandante1
                            "''", // nombredemandante1
                            "''", // idclasedemandante2
                            "''", // numiddemandante2
                            "''", // nombredemandante2
                            "''", // idclasedemandante3
                            "''", // numiddemandante3
                            "''", // nombredemandante3
                            "'" . $x["tipodoc"] . "'", // tipodoc
                            "'" . $x["numdoc"] . "'", // numdoc
                            "'" . $x["fechadoc"] . "'", // fechadoc
                            "''", // fechavenci
                            "'" . $x["municipio"] . "'", // mundoc
                            "'" . $x["origendoc"] . "'", // txtorigendoc
                            "''", // dirubi,
                            "''", // munubi
                            0, // valor
                            "'ACTO REGISTRADO AUTOMATICAMENTE DESDE CAJA'", // descripcion
                            "'" . $x["libro"] . "'", // libro
                            "'" . $x["libro"] . '-' . $x["acto"] . "'", // acto
                            "'" . addslashes($x["noticia"]) . "'", // Noticia
                            "''", // Libro cruce
                            "''", // Inscripción cruce
                            "''", // nueva organizacion
                            "''", // nuevacategoria
                            "''", // nuevonombre
                            "''", // nueva fecha de duración
                            "''", // camaraanterior
                            "''", // matriculaanterior
                            "''", // fechamatriculaanterior
                            "''", // fecharenovacionanterior
                            "''", // ultimoanorenovadoanterior
                            "''", // municipioanterior
                            "''", // benart7anterior
                            0, // personal
                            0, // acttot
                            0, // pastot
                            0, // pattot
                            0, // actvin
                            "''", // Clase libro
                            "''", // Tipo libro
                            "''", // Codigo libro
                            "''", // Nombre libro
                            "''", // email libro
                            "''", // emailconfirmacion libro
                            0, // pagina inicial libro
                            0, // Pagina final libro,
                            "''", // Acta numero libro
                            "''", // Fecha acta libro
                            "''", // motivo cancelacion
                            "'" . $x["libro"] . "'", // libro
                            "'" . ltrim($respuestaLibro["renglon"][1]["numreg"], "0") . "'", // numreg
                            "'" . $respuestaLibro["renglon"][1]["dupli"] . "'", // dupli
                            "'" . $respuestaLibro["renglon"][1]["fecha"] . "'", // fechareg
                            "'" . $respuestaLibro["renglon"][1]["hora"] . "'", // horareg
                            "'" . $usuXX . "'" // Usuario que registra                    
                        );
                        $resI = insertarRegistrosMysqliApi($mysqli, 'mreg_estudio_actos_registro', $arrCampos, $arrValores);
                        if ($resI === false) {
                            \logApi::general2($nameLog, $idSolicitudPago, \funcionesGenerales::utf8_encode('Error insertando acto en mreg_estudio_actos_registro : ' . $_SESSION["generales"]["mensajeerror"] . "\r\n"));
                        }
                    }
                }
            }
        }
        \logApi::general2($nameLog, $idSolicitudPago, \funcionesGenerales::utf8_encode('Genero inscripciones en libros'));

        // ******************************************************************************************************************** //
        // 2019-7-17: JINT: En caso de cambio de nombre
        // ******************************************************************************************************************** // 
        if ($nomant != '' && $librocamnom != '') {
            if (contarRegistrosMysqliApi($mysqli, 'mreg_est_inscritos', "matricula='" . ltrim($matx, "0") . "' and (organizacion = '02' or categoria = '2' or categoria = '3')") == 1) {
                $sec = contarRegistrosMysqliApi($mysqli, 'mreg_est_nombresanteriores', "matricula='" . ltrim($matx, "0") . "'");
                $sec++;
                $arrCampos = array(
                    'matricula',
                    'secuencia',
                    'fechareg',
                    'nombre',
                    'libro',
                    'registro',
                    'dupli',
                    'operador',
                    'fechacreacion'
                );
                $arrValores = array(
                    "'" . ltrim($matcamnom, "0") . "'",
                    "'" . sprintf("%03s", $sec) . "'",
                    "'" . date("Ymd") . "'",
                    "'" . addslashes($nomant) . "'",
                    "'" . $librocamnom . "'",
                    "'" . $inscripcioncamnom . "'",
                    "'" . $duplicamnom . "'",
                    "'" . $_SESSION["generales"]["codigousuario"] . "'",
                    "'" . date("Ymd") . "'"
                );
                insertarRegistrosMysqliApi($mysqli, 'mreg_est_nombresanteriores', $arrCampos, $arrValores);
            }
        }
        \logApi::general2($nameLog, $idSolicitudPago, \funcionesGenerales::utf8_encode('GActualizo nom bres anrteriores en caso de cambios de nombre'));

        // ******************************************************************************************************************** //
        // 2019-7-17: JINT: Activar afiliados (pasarlos de aceptados a activos
        // ******************************************************************************************************************** // 
        if ($_SESSION["tramite"]["tipotramite"] == 'serviciosempresariales') {
            foreach ($_SESSION["tramite"]["liquidacion"] as $liqdet) {
                $serv = retornarRegistroMysqliApi($mysqli, 'mreg_servicios', "idservicio='" . $liqdet["idservicio"] . "'");
                if ($serv && !empty($serv)) {
                    if ($serv["grupoventas"] == '02') {
                        if ($liqdet["expediente"] != '') {
                            $exp = retornarRegistroMysqliApi($mysqli, 'mreg_est_inscritos', "matricula='" . $liqdet["expediente"] . "'");
                            if ($exp && $exp["ctrafiliacion"] == '3') {
                                \funcionesRegistrales::actualizarMregEstInscritosCampo($mysqli, $liqdet["expediente"], 'ctrafiliacion', "1", "varchar", "", $_SESSION["tramite"]["tipotramite"], $_SESSION["tramite"]["numerorecibo"]);
                            }
                        }
                    }
                }
            }
        }

        // ******************************************************************************************************** //
        // Genera sellos en el repositorio
        // El sello se genera con la firma del jefe juridico - persona parametrizada en claves valor 90.01.01
        // Siempre y cuando el sistema esté activado para generar los sellos en el SII 
        // ******************************************************************************************************** //
        if (!defined('GENERAR_PDF_SELLOS_EN_SII')) {
            define('GENERAR_PDF_SELLOS_EN_SII', 'S');
        }
        if (
                GENERAR_PDF_SELLOS_EN_SII == '' ||
                GENERAR_PDF_SELLOS_EN_SII == 'S' ||
                GENERAR_PDF_SELLOS_EN_SII == 'S-AL-INSCRIBIR' ||
                GENERAR_PDF_SELLOS_EN_SII == 'S-AL-FIRMAR'
        ) {
            if ($xInsc != 0) {
                $xInsc = 0;
                foreach ($xInscripciones as $x) {

                    $xInsc++;
                    if (ltrim($x["numreg"], "0") != '') {
                        $fx = generarSelloRegistros(
                                $mysqli,
                                $x["tiporegistro"],
                                $x["libro"],
                                sprintf("%08s", $x["numreg"]),
                                $x["fecha"],
                                date("His"),
                                $x["acto"] . ' ' . retornarNombreActosRegistroMysqliApi($mysqli, $x["libro"], $x["acto"]),
                                $x["noticia"],
                                $x["matricula"],
                                $x["nombre"],
                                $x["identificacion"]
                        );
                        if ($fx) {
                            $xInscripciones[$xInsc]["filesello"] = $fx;
                        } else {
                            \logApi::general2($nameLog, $idSolicitudPago, \funcionesGenerales::utf8_encode('Error generando sello para : ' . $x["tiporegistro"] . ' - ' . substr($x["libro"], 2, 2) . ' - ' . sprintf("%08s", $x["numreg"]) . "\r\n"));
                        }
                        $texto = '';
                        if (substr($x["libro"], 0, 2) == 'RE') {
                            switch (substr($x["libro"], 2, 2)) {
                                case "51":
                                    $texto = 'INSCRIPCION EN EL REGISTRO DE ENTIDADES SIN ANIMO DE LUCRO : ';
                                    break;
                                case "53":
                                    $texto = 'INSCRIPCION EN EL REGISTRO DE ENTIDADES DE LA ECONOMIA SOLIDARIA : ';
                                    break;
                                case "54":
                                    $texto = 'INSCRIPCION EN EL REGISTRO DE ENTIDADES DE VEEDURIAS : ';
                                    break;
                                case "55":
                                    $texto = 'INSCRIPCION EN EL REGISTRO DE ONGS EXTRANJERAS : ';
                                    break;
                                default: break;
                            }
                        } else {
                            $texto = 'INSCRIPCION EN EL REGISTRO MERCANTIL : ';
                        }
                        $id = \funcionesRegistrales::grabarAnexoRadicacion(
                                        $mysqli, // Conexion BD
                                        $_SESSION["generales"]["codigobarras1"], // Código de barras
                                        $_SESSION["tramite"]["numerorecibo"], // Número del recibo
                                        $_SESSION["tramite"]["numerooperacion"], // Operacion
                                        ltrim($x["identificacion"], "0"), // Identificacion
                                        trim($x["nombre"]), // Nombre
                                        '', // Acreedor
                                        '', // Nombre acreedor
                                        ltrim($x["matricula"], "0"), // matrícula
                                        '', // proponente
                                        retornarClaveValorMysqliApi($mysqli, $x["tiposello"]), // Tipo de documento para el sello de mercantil
                                        $x["numdoc"], // Numero del documento
                                        $x["fecha"],
                                        '', // Codigo de origen
                                        'EL SECRETARIO DE LA CAMARA O SU DELEGADO', // origen del documento
                                        '', // Clasificacion
                                        '', // Numero del contrato
                                        '', // Idfuente
                                        1, // version
                                        '', // Path
                                        '1', // Estado
                                        date("Ymd"), // fecha de escaneo o generacion
                                        $_SESSION["generales"]["codigousuario"], // Usuario que genera el registro
                                        '', // Caja de archivo
                                        '', // Libro de archivo
                                        $texto . ', LIBRO: ' . ltrim(substr($x["libro"], 2, 2), "0") . ' - REGISTRO ' . ltrim($x["numreg"], "0") . ' - ACTO: ' . $x["noticia"], // Observaciones
                                        ltrim(substr($x["libro"], 2, 2), "0"), // Libro
                                        ltrim($x["numreg"], "0"), // Numero del registro en libros
                                        $x["dupli"], // Dupli
                                        $x["bandeja"], // Bandeja de registro
                                        'N', // Soporte recibo
                                        '', // Identificador
                                        '505', // Sello	
                                        '' // Proceso especial
                        );

                        //
                        $dirx = date("Ymd");
                        $path = PATH_ABSOLUTO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/mreg/' . $dirx;
                        if (!is_dir($path) || !is_readable($path)) {
                            mkdir($path, 0777);
                            \funcionesGenerales::crearIndex($path);
                        }

                        $pathsalida = 'mreg/' . $dirx . '/' . $id . '.pdf';
                        copy($_SESSION["generales"]["pathabsoluto"] . '/tmp/' . $fx, $_SESSION["generales"]["pathabsoluto"] . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/' . $pathsalida);
                        \funcionesRegistrales::grabarPathAnexoRadicacion($mysqli, $id, $pathsalida);

                        //
                        $arrCampos = array('ctrsello', 'idanexosello');
                        $arrValores = array("'SI'", $id);
                        regrabarRegistrosMysqliApi($mysqli, 'mreg_est_inscripciones', $arrCampos, $arrValores, "libro='" . $x["libro"] . "' and registro='" . ltrim($x["numreg"], "0") . "' and dupli='" . $x["dupli"] . "'");
                        // \logApi::general2($nameLog, $idSolicitudPago, \funcionesGenerales::utf8_encode('Genero Sello : Libro: ' . ltrim(substr($x["libro"], 2, 2), "0") . ' - Numreg: ' . ltrim($x["numreg"], "0") . ' - Dupli: ' . $x["dupli"] . ' - IdAnexo: ' . $id . ' - Path: ' . $pathsalida));
                        //
                        $arrCampos = array('idsellogenerado');
                        $arrValores = array($id);
                        regrabarRegistrosMysqliApi($mysqli, 'mreg_estudio_actos_registro', $arrCampos, $arrValores, "numlibro='" . $x["libro"] . "' and numreg='" . ltrim($x["numreg"], "0") . "' and dupli='" . $x["dupli"] . "'");
                    }
                }
            }
        }
        \logApi::general2($nameLog, $idSolicitudPago, \funcionesGenerales::utf8_encode('Genero sellos soporte de las inscripciones en libros'));

        // ******************************************************************************************************** //
        // Actualiza el periodico de inscripciones en el codigo de barras
        // ******************************************************************************************************** //
        if ($xInsc > 0) {
            $texto = '';
            foreach ($xInscripciones as $x) {
                $texto .= $x["libro"] . sprintf("%08s", $x["numreg"]) . $x["acto"];
                // ************************************************************************************************** //
                // Actualiza en SII las inscripciones en libros
                // ************************************************************************************************** //
                $arrCampos = array(
                    'codigobarras',
                    'libro',
                    'acto',
                    'registro'
                );
                $arrValores = array(
                    "'" . $_SESSION["generales"]["codigobarras1"] . "'",
                    "'" . $x["libro"] . "'",
                    "'" . $x["acto"] . "'",
                    "'" . $x["numreg"] . "'",
                );
                insertarRegistrosMysqliApi($mysqli, 'mreg_est_codigosbarras_libros', $arrCampos, $arrValores);
            }
        }
        \logApi::general2($nameLog, $idSolicitudPago, \funcionesGenerales::utf8_encode('Actualizo el periodico de inscripciones en el codigo de barras'));

        // ************************************************************************************************* //
        // Actualiza tabla de alertas marcando como 'AP' aplicada la alerta en cuestion
        // ************************************************************************************************* //
        if ($_SESSION["tramite"]["alertaid"] != 0) {
            $ale = retornarRegistroMysqliApi($mysqli, 'mreg_alertas', "id=" . $_SESSION["tramite"]["alertaid"]);
            if ($ale && !empty($ale)) {
                $valalerta = $ale["valoralerta"] - $ale["valoraplicado"];
                if ($valalerta <= $_SESSION["tramite"]["alertavalor"]) {
                    $valaplicado = $ale["valoraplicado"] + $_SESSION["tramite"]["alertavalor"];
                    $arrCampos = array('idestado', 'numerorecibo', 'valoraplicado');
                    $arrValues = array("'AP'", "'" . $_SESSION["tramite"]["numerorecibo"] . "'", $valaplicado);
                    $condicion = "id=" . $_SESSION["tramite"]["alertaid"];
                    regrabarRegistrosMysqliApi($mysqli, 'mreg_alertas', $arrCampos, $arrValues, $condicion);
                }
                if ($valalerta > $_SESSION["tramite"]["alertavalor"]) {
                    $valaplicado = $ale["valoraplicado"] + $_SESSION["tramite"]["alertavalor"];
                    $arrCampos = array('idestado', 'numerorecibo', 'valoraplicado');
                    $arrValues = array("'VI'", "'" . $_SESSION["tramite"]["numerorecibo"] . "'", $valaplicado);
                    $condicion = "id=" . $_SESSION["tramite"]["alertaid"];
                    regrabarRegistrosMysqliApi($mysqli, 'mreg_alertas', $arrCampos, $arrValues, $condicion);
                }
            }
        }

        // ************************************************************************************************* //
        // Alertas administrativas incluidas en la liquidacion (tipos 1 y 4)
        // ************************************************************************************************* //
        foreach ($_SESSION["tramite"]["liquidacion"] as $t) {
            if (isset($d["idalerta"]) && ltrim($t["idalerta"], "0") != '') {
                $arrAle = retornarRegistromysqliApi($mysqli, 'mreg_alertas', "id=" . $t["idalerta"]);
                if ($arrAle && !empty($arrAle)) {
                    if ($arrAle["tipoalerta"] == '1') {
                        $val = $arrAle["valoralerta"] + $t["valorservicio"];
                        if ($val == 0) {
                            $arrCampos = array('idestado', 'numerorecibo');
                            $arrValues = array("'AP'", "'" . $_SESSION["tramite"]["numerorecibo"] . "'");
                            $condicion = "id=" . $t["idalerta"];
                            regrabarRegistrosMysqliApi($mysqli, 'mreg_alertas', $arrCampos, $arrValues, $condicion);
                        }
                        if ($val > 0) {
                            $arrCampos = array('valoralerta');
                            $arrValues = array($val);
                            $condicion = "id=" . $t["idalerta"];
                            regrabarRegistrosMysqliApi($mysqli, 'mreg_alertas', $arrCampos, $arrValues, $condicion);
                        }
                        if ($val < 0) {
                            $arrCampos = array('valoralerta', 'idestado', 'numerorecibo');
                            $arrValues = array($val, "'AP'", "'" . $_SESSION["tramite"]["numerorecibo"] . "'");
                            $condicion = "id=" . $t["idalerta"];
                            regrabarRegistrosMysqliApi($mysqli, 'mreg_alertas', $arrCampos, $arrValues, $condicion);
                        }
                    }
                    if ($arrAle["tipoalerta"] == '4') {
                        $arrCampos = array('idestado', 'numerorecibo');
                        $arrValues = array("'AP'", "'" . $_SESSION["tramite"]["numerorecibo"] . "'");
                        $condicion = "id=" . $t["idalerta"];
                        regrabarRegistrosMysqliApi($mysqli, 'mreg_alertas', $arrCampos, $arrValues, $condicion);
                    }
                }
            }
        }
        \logApi::general2($nameLog, $idSolicitudPago, \funcionesGenerales::utf8_encode('Actualizo tabla de alertas marcando como AP aplicada la alerta en cuestion'));

        // ************************************************************************************************* //
        // En caso de cancelacion de prepago
        // Actualiza (C) tabla de prepagos del SII
        // genera la clave de activacion del prepago
        // ************************************************************************************************* //
        $claveprepago = '';
        $saldoprepago = 0;
        if ($grupoServicios == 'PrePag') {
            $claveprepago = \funcionesGenerales::generarAleatorioNumerico($mysqli);
            $respuesta = \funcionesRegistrales::actualizarPrepago($mysqli, 'C', $_SESSION["tramite"]["identificacioncliente"], $claveprepago, $_SESSION["tramite"]["valortotal"], $_SESSION["tramite"]["numerorecibo"], $_SESSION["tramite"]["numerooperacion"], $_SESSION["tramite"]["liquidacion"][1]["idservicio"], '0', 'Recarga del prepago', \funcionesGenerales::localizarIP(), $_SESSION["generales"]["codigousuario"], '', $_SESSION["tramite"]["email"], trim($_SESSION["tramite"]["apellidocliente"] . ' ' . $_SESSION["tramite"]["nombrecliente"]), $_SESSION["tramite"]["movil"], $_SESSION["tramite"]["direccion"], $_SESSION["tramite"]["idmunicipio"], '', $_SESSION["tramite"]["telefono"], $_SESSION["tramite"]["nombre1cliente"], $_SESSION["tramite"]["nombre2cliente"], $_SESSION["tramite"]["apellido1cliente"], $_SESSION["tramite"]["apellido2cliente"]);
            if ($respuesta["codigoError"] == '0000') {
                $saldoprepago = $respuesta["saldoprepago"];
            }
        }
        \logApi::general2($nameLog, $idSolicitudPago, \funcionesGenerales::utf8_encode('Actualizo prepagos, si aplica.'));

        // ************************************************************************************************************** //
        // 2016-10-19 : JINT
        // Si el pago es con cargo al prepago
        // Adiciona el registro a la tabla de movimientos de prepago
        // ************************************************************************************************************** //
        if ($_SESSION["tramite"]["pagoprepago"] != 0) {
            $arrCampos = array(
                'identificacion',
                'fecha',
                'hora',
                'ip',
                'operador',
                'usuario',
                'tipomov',
                'servicio',
                'concepto',
                'expediente',
                'recibo',
                'cantidad',
                'valor'
            );
            $arrValores = array();
            $iPrep = 0;
            foreach ($_SESSION["tramite"]["liquidacion"] as $l) {
                $iPrep++;
                $arrValores[$iPrep] = array(
                    "'" . ltrim($_SESSION["tramite"]["identificacioncliente"], "0") . "'",
                    "'" . date("Ymd") . "'",
                    "'" . date("His") . "'",
                    "'" . \funcionesGenerales::localizarIP() . "'",
                    "'" . $_SESSION["generales"]["cajero"] . "'",
                    "'" . $_SESSION["generales"]["codigousuario"] . "'",
                    "'D'", // Descuenta del prepago
                    "'" . $l["idservicio"] . "'", // Servicio
                    "'" . retornarRegistroMysqliApi($mysqli, "mreg_servicios", "idservicio='" . $l["idservicio"] . "'", "nombre") . "'", // Nombre del servicio
                    "'" . $l["expediente"] . "'", // Expediente
                    "'" . $_SESSION["tramite"]["numerorecibo"] . "'",
                    $l["cantidad"], // Cantidad
                    $l["valorservicio"]
                );
            }
            $resx = insertarRegistrosBloqueMysqliApi($mysqli, 'mreg_prepagos_uso', $arrCampos, $arrValores);
            if ($resx === false) {
                \logApi::general2($nameLog, $idSolicitudPago, \funcionesGenerales::utf8_encode('Error Actualizando consumo de prepago en mreg_prepagos_uso, valor : ' . $_SESSION["tramite"]["pagoprepago"]));
            }

            $saldoprepago = 0;
            $arrTemP = retornarRegistrosMysqliApi($mysqli, 'mreg_prepagos_uso', "identificacion='" . ltrim($_SESSION["tramite"]["identificacioncliente"], "0") . "'", "fecha,hora");
            foreach ($arrTemP as $tp) {
                if ($tp["tipomov"] == 'C') {
                    $saldoprepago = $saldoprepago + $tp["valor"];
                } else {
                    $saldoprepago = $saldoprepago - $tp["valor"];
                }
            }
        }
        $_SESSION["tramite"]["claveprepago"] = $claveprepago;
        $_SESSION["tramite"]["saldoprepago"] = $saldoprepago;
        \logApi::general2($nameLog, $idSolicitudPago, \funcionesGenerales::utf8_encode('Actualizo prepagos movimientos cuando sea con cargo al mismo'));

        // ******************************************************************************************************** //
        // En caso de mutacion de nombre crea el historico del nombre en SII   	
        // ******************************************************************************************************** //
        // 2017-12-16: JINT
        // Se adiciona control para ley 1780, código de policia alto impacto y codigo de policía multas
        if (
                $_SESSION["tramite"]["multadoponal"] != 'S' &&
                $_SESSION["tramite"]["multadoponal"] != 'L' &&
                $_SESSION["tramite"]["controlactividadaltoimpacto"] != 'S' && ($_SESSION["tramite"]["cumplorequisitosbenley1780"] != 'S' ||
                $_SESSION["tramite"]["mantengorequisitosbenley1780"] != 'S' ||
                $_SESSION["tramite"]["renunciobeneficiosley1780"] != 'N')
        ) {

            if (($cambioNombre == 'si' && $_SESSION["tramite"]["tipotramite"] != 'inscripciondocumentos') || ($cambioNombre == 'si' && $_SESSION["tramite"]["tipotramite"] == 'inscripciondocumentos' && $registroInmediato == 'si')
            ) {

                foreach ($xInscripciones as $x) {
                    $secNom = '';
                    $tmNom = retornarRegistrosMysqliApi($mysqli, 'mreg_est_nombresanteriores', "matricula='" . ltrim($x["matricula"]) . "'", "matricula,secuencia");
                    if ($tmNom && !empty($tmNom)) {
                        foreach ($tmNom as $xrnom) {
                            if ($xrnom["secuencia"] > $secNom) {
                                $secNom = $xrnom["secuencia"];
                            }
                        }
                    }
                    unset($xrnom);
                    unset($tmNom);
                    $secNom = intval($secNom) + 1;

                    // Crea el cambio de nombre en mreg_est_nombresanteriores
                    $arrCampos = array(
                        'matricula',
                        'secuencia',
                        'fechareg',
                        'nombre',
                        'libro',
                        'registro',
                        'operador',
                        'fechacreacion'
                    );
                    $arrValores = array(
                        "'" . ltrim($x["matricula"]) . "'",
                        "'" . sprintf("%03s", $secNom) . "'",
                        "'" . date("Ymd") . "'",
                        "'" . addslashes($_SESSION["tramite"]["nombreanterior"]) . "'",
                        "'" . $x["libro"] . "'",
                        "'" . ltrim($x["numreg"]) . "'",
                        "'" . $idope . "'",
                        "'" . date("Ymd") . "'"
                    );
                    $resx = insertarRegistrosMysqliApi($mysqli, 'mreg_est_nombresanteriores', $arrCampos, $arrValores);
                    if ($resx === false) {
                        \logApi::general2($nameLog, $idSolicitudPago, \funcionesGenerales::utf8_encode('Error actualizando la tabla mreg_est_nombresanteriores'));
                    }
                }
            }
        }
        \logApi::general2($nameLog, $idSolicitudPago, \funcionesGenerales::utf8_encode('En caso de mutacion de nombre crea el historico del nombre en SII  '));

        // ******************************************************************************************************** //
        // Recupera la liquidacion
        // ******************************************************************************************************** //
        $_SESSION["tramite"] = \funcionesRegistrales::retornarMregLiquidacion($mysqli, $_SESSION["tramite"]["idliquidacion"]);
        $matricularecibo = '';
        $proponenterecibo = '';
        $tiporeg = $tipoRegistro;
        if ($recibeMatricula == 'si') {
            $matricularecibo = $_SESSION["tramite"]["liquidacion"][1]["expediente"];
        }
        if ($recibeProponente == 'si') {
            $proponenterecibo = $_SESSION["tramite"]["liquidacion"][1]["expediente"];
        }
        $_SESSION["tramite"]["claveprepago"] = $claveprepago;
        $_SESSION["tramite"]["saldoprepago"] = $saldoprepago;

        // ******************************************************************************************************** //
        // En caso de tramites RUES como receptora
        // Crea la tabla rue_radicacion en SII
        // Crea la tabla de certificados virtuales   	
        // ******************************************************************************************************** //
        if ($tipoRegistro == 'RueRec' ||
                (substr($_SESSION["tramite"]["tipotramite"], 0, 4) == 'rues' &&
                substr($_SESSION["tramite"]["tipotramite"], 6) == 'receptora')
        ) {
            //
            if (!isset($respRues["numero_unico_consulta"])) {
                $respRues["numero_unico_consulta"] = '';
            }
            if (!isset($respRues["ctrestadomensajerue"])) {
                $respRues["ctrestadomensajerue"] = '';
            }
            if (!isset($respRues["ctrtramite"])) {
                $respRues["ctrtramite"] = '';
            }
            if (!isset($respRues["txtobservacion"])) {
                $respRues["txtobservacion"] = '';
            }
            if (!isset($respRues["total_pagado"])) {
                $respRues["total_pagado"] = 0;
            }
            if ($respRues["total_pagado"] == 0) {
                $respRues["total_pagado"] = $_SESSION["tramite"]["rues_totalpagado"];
            }
            if (!isset($respRues["estado_transaccion"])) {
                $respRues["estado_transaccion"] = '11';
            }

            // mreg_rue_radicacion 
            $arrCampos = array(
                'tipooperacion',
                'codigobarras',
                'numerooperacion',
                'fechaoperacion',
                'horaoperacion',
                'recibolocal',
                'numerointernorue',
                'idliquidacion',
                'usuario',
                'idserviciorue',
                'camaraorigen',
                'camaradestino',
                'matricularue',
                'proponenterue',
                'nombreregistrado',
                'idclaserue',
                'numuidrue',
                'dv',
                'nombrepagador',
                'ctrdocnal',
                'fechadocumento',
                'estadotransaccion',
                'fechapago',
                'numfactura',
                'operacionreferencia',
                'totalpagado',
                'formapagorue',
                'numerounicoconsulta',
                'ctrestadomensajerue',
                'fecharespuesta',
                'horarespuesta',
                'ctrtramite',
                'txtobservacion'
            );
            $arrValores = array(
                "'" . '2' . "'", // Tramite RUES como receptor
                "''", // Codigo de barras
                "'" . $_SESSION["tramite"]["numerooperacion"] . "'",
                "'" . $_SESSION["tramite"]["fecharecibo"] . "'",
                "'" . $_SESSION["tramite"]["horarecibo"] . "'",
                "'" . $_SESSION["tramite"]["numerorecibo"] . "'",
                "'" . $respRues["numero_interno"] . "'",
                $_SESSION["tramite"]["idliquidacion"],
                "'" . $_SESSION["tramite"]["idusuario"] . "'",
                "'" . substr($respRues["numero_interno"], 21, 8) . "'",
                "'" . $_SESSION["tramite"]["rues_camarareceptora"] . "'",
                "'" . $_SESSION["tramite"]["rues_camararesponsable"] . "'",
                "'" . ltrim($_SESSION["tramite"]["rues_matricula"], "0") . "'",
                "'" . ltrim($_SESSION["tramite"]["rues_proponente"], "0") . "'",
                "'" . $_SESSION["tramite"]["rues_nombreregistrado"] . "'",
                "'" . $respRues["clase_identificacion"] . "'",
                "'" . ltrim($respRues["numero_identificacion"], "0") . "'",
                "'" . $respRues["digito_verificacion"] . "'",
                "'" . addslashes(trim($_SESSION["tramite"]["apellidopagador"] . " " . $_SESSION["tramite"]["nombrepagador"])) . "'",
                "'" . $_SESSION["tramite"]["rues_origendocumento"] . "'",
                "'" . $_SESSION["tramite"]["rues_fechadocumento"] . "'",
                "'" . $respRues["estado_transaccion"] . "'",
                "'" . $respRues["fecha_respuesta"] . "'",
                "'" . $respRues["numero_factura"] . "'",
                "'" . $respRues["referencia_operacion"] . "'",
                $respRues["total_pagado"],
                "'" . $respRues["forma_pago"] . "'",
                "'" . $respRues["numero_unico_consulta"] . "'",
                "'" . $respRues["ctrestadomensajerue"] . "'",
                "'" . $respRues["fecha_respuesta"] . "'",
                "'" . $respRues["fecha_respuesta"] . "'",
                "'" . $respRues["ctrtramite"] . "'",
                "'" . addslashes($respRues["txtobservacion"]) . "'"
            );
            $resx = insertarRegistrosMysqliApi($mysqli, 'mreg_rue_radicacion', $arrCampos, $arrValores);
            if ($resx === false) {
                \logApi::general2($nameLog, $idSolicitudPago, \funcionesGenerales::utf8_encode('Error grabando mreg_rue_radicacion (receptora): ' . $_SESSION["generales"]["mensajeerror"]));
            }

            // mreg_rue_radicacion_estados
            $arrCampos = array(
                'numerointernorue',
                'fecha',
                'hora',
                'usuario',
                'estado'
            );
            $arrValores = array(
                "'" . $respRues["numero_interno"] . "'",
                "'" . date("Ymd") . "'",
                "'" . date("His") . "'",
                "'" . $_SESSION["tramite"]["idusuario"] . "'",
                "'" . $respRues["estado_transaccion"] . "'"
            );
            $resx = insertarRegistrosMysqliApi($mysqli, 'mreg_rue_radicacion_estados', $arrCampos, $arrValores);
            if ($resx === false) {
                \logApi::general2($nameLog, $idSolicitudPago, \funcionesGenerales::utf8_encode('Error grabando mreg_rue_radicacion_estados (receptora): ' . $_SESSION["generales"]["mensajeerror"]));
            }

            // mreg_rue_radicacion_servicios
            $arrCampos = array(
                'numerointernorue',
                'secuencia',
                'codigoservicio',
                'nombreservicio',
                'nombrebase',
                'valorbase',
                'valorliquidacion',
                'cantidad',
                'ctrbase',
                'ctrren',
                'matricula',
                'anorenovacion'
            );
            $arrValores = array();
            $i = 0;
            foreach ($_SESSION["tramite"]["rues_servicios"] as $s) {
                $i++;
                $arrValores[$i] = array(
                    "'" . $respRues["numero_interno"] . "'",
                    $i,
                    "'" . $s["codigo_servicio"] . "'",
                    "'" . $s["descripcion_servicio"] . "'",
                    "'" . addslashes($s["nombre_base"]) . "'",
                    $s["valor_base"],
                    $s["valor_liquidacion"],
                    $s["cantidad_servicio"],
                    "'" . $s["indicador_base"] . "'",
                    "'" . $s["indicador_renovacion"] . "'",
                    "'" . ltrim($s["matricula_servicio"], "0") . "'",
                    "'" . ltrim($s["ano_renovacion"], "0") . "'"
                );
            }
            $resx = insertarRegistrosBloqueMysqliApi($mysqli, 'mreg_rue_radicacion_servicios', $arrCampos, $arrValores);
            if ($resx === false) {
                \logApi::general2($nameLog, $idSolicitudPago, \funcionesGenerales::utf8_encode('Error grabando mreg_rue_radicacion_servicios (receptora) : ' . $_SESSION["generales"]["mensajeerror"]));
            }

            //rues01receptora rues01responsable
            // ***************************************************************************************** //
            // Si se trata de certificados crea la tabla mreg_certificados_virtuales
            // ***************************************************************************************** //

            if (substr($_SESSION["tramite"]["tipotramite"], 0, 6) == 'rues01') {

                if (ltrim($respRues["camara_receptora"], "0") != '') {

                    // cuando el certyificado se retorna en el arreglo $respRues["texto_certificado"]["texto"]
                    if (isset($respRues["texto_certificado"]["texto"]) && !empty($respRues["texto_certificado"]["texto"])) {
                        $sec = 0;
                        $arrTx = array();
                        if (!is_array($respRues["texto_certificado"]["texto"])) {
                            $sec++;
                            $arrTx[$sec] = $respRues["texto_certificado"]["texto"];
                        } else {
                            foreach ($respRues["texto_certificado"]["texto"] as $tx) {
                                $sec++;
                                $arrTx[$sec] = $tx;
                            }
                        }

                        $sec = 0;
                        foreach ($arrTx as $tx) {
                            $sec++;
                            $aleatorio = \funcionesGenerales::generarAleatorioAlfanumerico(6);
                            $nomcer = "RUES-" . date("Ymd") . '-' . date("His") . '-' . sprintf("%03s", $sec) . '-' . $aleatorio;
                            $pathx = 'mreg/certificados/' . date("Y") . '/' . date("m") . '/' .
                                    $nomcer .
                                    '.pdf';
                            if (!is_dir(PATH_ABSOLUTO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"])) {
                                mkdir(PATH_ABSOLUTO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"], 0777);
                            }
                            if (!is_dir(PATH_ABSOLUTO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/mreg')) {
                                mkdir(PATH_ABSOLUTO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/mreg', 0777);
                            }
                            if (!is_dir(PATH_ABSOLUTO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/mreg/certificados')) {
                                mkdir(PATH_ABSOLUTO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/mreg/certificados', 0777);
                            }
                            if (!is_dir(PATH_ABSOLUTO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/mreg/certificados/' . date("Y"))) {
                                mkdir(PATH_ABSOLUTO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/mreg/certificados/' . date("Y"), 0777);
                            }
                            if (!is_dir(PATH_ABSOLUTO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/mreg/certificados/' . date("Y") . '/' . date("m"))) {
                                mkdir(PATH_ABSOLUTO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/mreg/certificados/' . date("Y") . '/' . date("m"), 0777);
                            }

                            $f = fopen(PATH_ABSOLUTO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/' . $pathx, "w");
                            fwrite($f, base64_decode($tx));
                            fclose($f);

                            //
                            if (!file_exists(PATH_ABSOLUTO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/' . $pathx)) {
                                \logApi::general2($nameLog, $idSolicitudPago, \funcionesGenerales::utf8_encode('Error Almacenando el archivo : ' . PATH_ABSOLUTO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/' . $pathx));
                            }

                            $arrCampos = array(
                                'id',
                                'recibo',
                                'operacion',
                                'fecha',
                                'hora',
                                'servicio',
                                'cantidad',
                                'valor',
                                'expediente',
                                'razonsocial',
                                'identificacioncliente',
                                'nombrecliente',
                                'estado',
                                'cantidadconsultas',
                                'tipocertificado',
                                'contenido',
                                'path'
                            );
                            $arrValores = array(
                                "'" . $nomcer . "'",
                                "'" . $_SESSION["tramite"]["numerorecibo"] . "'",
                                "'" . $_SESSION["tramite"]["numerooperacion"] . "'",
                                "'" . $_SESSION["tramite"]["fecharecibo"] . "'",
                                "'" . $_SESSION["tramite"]["horarecibo"] . "'",
                                "''",
                                1,
                                0,
                                "''",
                                "''",
                                "'" . ltrim($_SESSION["tramite"]["identificacionpagador"], "0") . "'",
                                "'" . addslashes(trim($_SESSION["tramite"]["apellidopagador"] . " " . $_SESSION["tramite"]["nombrepagador"])) . "'",
                                "'1'",
                                0,
                                "''",
                                "''",
                                "'" . $pathx . "'"
                            );
                            insertarRegistrosMysqliApi($mysqli, 'mreg_certificados_virtuales', $arrCampos, $arrValores);
                        }
                    }

                    // cuando el certyificado se retorna en el arreglo $respRues["texto_certificado"]
                    if (isset($respRues["texto_certificado"]) && !empty($respRues["texto_certificado"] && !isset($respRues["texto_certificado"]["texto"]))) {
                        $sec = 0;
                        $arrTx = array();
                        if (!is_array($respRues["texto_certificado"])) {
                            $sec++;
                            $arrTx[$sec] = $respRues["texto_certificado"];
                        } else {
                            foreach ($respRues["texto_certificado"] as $tx) {
                                $sec++;
                                $arrTx[$sec] = $tx;
                            }
                        }

                        $sec = 0;
                        foreach ($arrTx as $tx) {
                            $sec++;
                            $aleatorio = \funcionesGenerales::generarAleatorioAlfanumerico(6);
                            $nomcer = "RUES-" . date("Ymd") . '-' . date("His") . '-' . sprintf("%03s", $sec) . '-' . $aleatorio;
                            $pathx = 'mreg/certificados/' . date("Y") . '/' . date("m") . '/' .
                                    $nomcer .
                                    '.pdf';
                            if (!is_dir(PATH_ABSOLUTO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"])) {
                                mkdir(PATH_ABSOLUTO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"], 0777);
                            }
                            if (!is_dir(PATH_ABSOLUTO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/mreg')) {
                                mkdir(PATH_ABSOLUTO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/mreg', 0777);
                            }
                            if (!is_dir(PATH_ABSOLUTO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/mreg/certificados')) {
                                mkdir(PATH_ABSOLUTO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/mreg/certificados', 0777);
                            }
                            if (!is_dir(PATH_ABSOLUTO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/mreg/certificados/' . date("Y"))) {
                                mkdir(PATH_ABSOLUTO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/mreg/certificados/' . date("Y"), 0777);
                            }
                            if (!is_dir(PATH_ABSOLUTO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/mreg/certificados/' . date("Y") . '/' . date("m"))) {
                                mkdir(PATH_ABSOLUTO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/mreg/certificados/' . date("Y") . '/' . date("m"), 0777);
                            }

                            $f = fopen(PATH_ABSOLUTO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/' . $pathx, "w");
                            fwrite($f, base64_decode($tx));
                            fclose($f);

                            //
                            if (!file_exists(PATH_ABSOLUTO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/' . $pathx)) {
                                \logApi::general2($nameLog, $idSolicitudPago, \funcionesGenerales::utf8_encode('Error Almacenando el archivo : ' . PATH_ABSOLUTO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/' . $pathx));
                            }

                            $arrCampos = array(
                                'id',
                                'recibo',
                                'operacion',
                                'fecha',
                                'hora',
                                'servicio',
                                'cantidad',
                                'valor',
                                'expediente',
                                'razonsocial',
                                'identificacioncliente',
                                'nombrecliente',
                                'estado',
                                'cantidadconsultas',
                                'tipocertificado',
                                'contenido',
                                'path'
                            );
                            $arrValores = array(
                                "'" . $nomcer . "'",
                                "'" . $_SESSION["tramite"]["numerorecibo"] . "'",
                                "'" . $_SESSION["tramite"]["numerooperacion"] . "'",
                                "'" . $_SESSION["tramite"]["fecharecibo"] . "'",
                                "'" . $_SESSION["tramite"]["horarecibo"] . "'",
                                "''",
                                1,
                                0,
                                "''",
                                "''",
                                "'" . ltrim($_SESSION["tramite"]["identificacionpagador"], "0") . "'",
                                "'" . addslashes(trim($_SESSION["tramite"]["apellidopagador"] . " " . $_SESSION["tramite"]["nombrepagador"])) . "'",
                                "'1'",
                                0,
                                "''",
                                "''",
                                "'" . $pathx . "'"
                            );
                            insertarRegistrosMysqliApi($mysqli, 'mreg_certificados_virtuales', $arrCampos, $arrValores);
                        }
                    }

                    // cuando el certyificado se retorna en el arreglo $respRues["link_certificado"]
                    if (isset($respRues["link_certificado"]) && !empty($respRues["link_certificado"])) {
                        $sec = 0;
                        $arrTx = array();
                        foreach ($respRues["link_certificado"] as $lk) {
                            $sec++;
                            $arrTx[$sec] = $lk;
                        }

                        $sec = 0;
                        foreach ($arrTx as $lk) {
                            $certi = file_get_contents($lk);
                            $sec++;
                            $aleatorio = \funcionesGenerales::generarAleatorioAlfanumerico(6);
                            $nomcer = "RUES-" . date("Ymd") . '-' . date("His") . '-' . sprintf("%03s", $sec) . '-' . $aleatorio;
                            $pathx = 'mreg/certificados/' . date("Y") . '/' . date("m") . '/' .
                                    $nomcer .
                                    '.pdf';
                            if (!is_dir(PATH_ABSOLUTO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"])) {
                                mkdir(PATH_ABSOLUTO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"], 0777);
                            }
                            if (!is_dir(PATH_ABSOLUTO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/mreg')) {
                                mkdir(PATH_ABSOLUTO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/mreg', 0777);
                            }
                            if (!is_dir(PATH_ABSOLUTO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/mreg/certificados')) {
                                mkdir(PATH_ABSOLUTO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/mreg/certificados', 0777);
                            }
                            if (!is_dir(PATH_ABSOLUTO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/mreg/certificados/' . date("Y"))) {
                                mkdir(PATH_ABSOLUTO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/mreg/certificados/' . date("Y"), 0777);
                            }
                            if (!is_dir(PATH_ABSOLUTO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/mreg/certificados/' . date("Y") . '/' . date("m"))) {
                                mkdir(PATH_ABSOLUTO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/mreg/certificados/' . date("Y") . '/' . date("m"), 0777);
                            }

                            $f = fopen(PATH_ABSOLUTO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/' . $pathx, "w");
                            fwrite($f, $certi);
                            fclose($f);

                            //
                            if (!file_exists(PATH_ABSOLUTO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/' . $pathx)) {
                                \logApi::general2($nameLog, $idSolicitudPago, \funcionesGenerales::utf8_encode('Error Almacenando el archivo : ' . PATH_ABSOLUTO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/' . $pathx));
                            }

                            $arrCampos = array(
                                'id',
                                'recibo',
                                'operacion',
                                'fecha',
                                'hora',
                                'servicio',
                                'cantidad',
                                'valor',
                                'expediente',
                                'razonsocial',
                                'identificacioncliente',
                                'nombrecliente',
                                'estado',
                                'cantidadconsultas',
                                'tipocertificado',
                                'contenido',
                                'path'
                            );
                            $arrValores = array(
                                "'" . $nomcer . "'",
                                "'" . $_SESSION["tramite"]["numerorecibo"] . "'",
                                "'" . $_SESSION["tramite"]["numerooperacion"] . "'",
                                "'" . $_SESSION["tramite"]["fecharecibo"] . "'",
                                "'" . $_SESSION["tramite"]["horarecibo"] . "'",
                                "''",
                                1,
                                0,
                                "''",
                                "''",
                                "'" . ltrim($_SESSION["tramite"]["identificacionpagador"], "0") . "'",
                                "'" . addslashes(trim($_SESSION["tramite"]["apellidopagador"] . " " . $_SESSION["tramite"]["nombrepagador"])) . "'",
                                "'1'",
                                0,
                                "''",
                                "''",
                                "'" . $pathx . "'"
                            );
                            insertarRegistrosMysqliApi($mysqli, 'mreg_certificados_virtuales', $arrCampos, $arrValores);
                        }
                    }
                }
            }
        }
        \logApi::general2($nameLog, $idSolicitudPago, \funcionesGenerales::utf8_encode('En caso de tramites RUES como receptora, Creo la tabla rue_radicacion en SII, Creo la tabla de certificados virtuales'));

        // ******************************************************************************************************** //
        // En caso de tramites RUES como responsable
        // Crea la tabla rue_radicacion en SII
        // Crea la tabla de certificados virtuales
        // ******************************************************************************************************** //
        if (
                substr($_SESSION["tramite"]["tipotramite"], 0, 4) == 'rues' &&
                substr($_SESSION["tramite"]["tipotramite"], 6) == 'responsable'
        ) {

            if (substr($_SESSION["tramite"]["tipotramite"], 4, 2) == '01') {
                $estadorues = '11';
            } else {
                $estadorues = '02';
            }

            // mreg_rue_radicacion
            $arrCampos = array(
                'tipooperacion',
                'codigobarras',
                'numerooperacion',
                'fechaoperacion',
                'horaoperacion',
                'recibolocal',
                'numerointernorue',
                'idliquidacion',
                'usuario',
                'idserviciorue',
                'camaraorigen',
                'camaradestino',
                'matricularue',
                'proponenterue',
                'nombreregistrado',
                'idclaserue',
                'numuidrue',
                'dv',
                'nombrepagador',
                'ctrdocnal',
                'fechadocumento',
                'estadotransaccion',
                'fechapago',
                'numfactura',
                'operacionreferencia',
                'totalpagado',
                'formapagorue',
                'numerounicoconsulta',
                'ctrestadomensajerue',
                'fecharespuesta',
                'horarespuesta',
                'ctrtramite',
                'txtobservacion'
            );

            //
            if (!isset($_SESSION["tramite"]["rues_codigoservicioradicar"])) {
                $_SESSION["tramite"]["rues_codigoservicioradicar"] = '';
            }
            if ($_SESSION["tramite"]["rues_codigoservicioradicar"] == '') {
                $_SESSION["tramite"]["rues_codigoservicioradicar"] = substr($_SESSION["tramite"]["rues_numerointerno"], 21, 8);
            }

            //
            $arrValores = array(
                "'" . '1' . "'", // Tramite RUES como responsable
                "'" . $_SESSION["generales"]["codigobarras1"] . "'", // Codigo de barras
                "'" . $_SESSION["tramite"]["numerooperacion"] . "'",
                "'" . $_SESSION["tramite"]["fecharecibo"] . "'",
                "'" . $_SESSION["tramite"]["horarecibo"] . "'",
                "'" . $_SESSION["tramite"]["numerorecibo"] . "'",
                "'" . $_SESSION["tramite"]["rues_numerointerno"] . "'",
                $_SESSION["tramite"]["idliquidacion"],
                "'" . $_SESSION["tramite"]["idusuario"] . "'",
                "'" . $_SESSION["tramite"]["rues_codigoservicioradicar"] . "'",
                "'" . $_SESSION["tramite"]["rues_camarareceptora"] . "'",
                "'" . $_SESSION["tramite"]["rues_camararesponsable"] . "'",
                "'" . ltrim($_SESSION["tramite"]["rues_matricula"], "0") . "'",
                "'" . ltrim($_SESSION["tramite"]["rues_proponente"], "0") . "'",
                "'" . addslashes($_SESSION["tramite"]["rues_nombreregistrado"]) . "'",
                "'" . $_SESSION["tramite"]["rues_claseidentificacion"] . "'",
                "'" . ltrim($_SESSION["tramite"]["rues_numeroidentificacion"], "0") . "'",
                "'" . $_SESSION["tramite"]["rues_dv"] . "'",
                "'" . addslashes(trim($_SESSION["tramite"]["apellidopagador"] . " " . $_SESSION["tramite"]["nombrepagador"])) . "'",
                "'" . $_SESSION["tramite"]["rues_origendocumento"] . "'",
                "'" . $_SESSION["tramite"]["rues_fechadocumento"] . "'",
                "'" . $estadorues . "'",
                "'" . date("Ymd") . "'",
                "''", // Número de factura
                "''", // Numero de operacion
                $_SESSION["tramite"]["valortotal"],
                "'01'",
                "'" . $_SESSION["tramite"]["rues_numerounico"] . "'",
                "''",
                "'" . date("Ymd") . "'",
                "'" . date("His") . "'",
                "''",
                "''"
            );
            $res1 = insertarRegistrosMysqliApi($mysqli, 'mreg_rue_radicacion', $arrCampos, $arrValores);
            if ($res1 == false) {
                \logApi::general2($nameLog, $idSolicitudPago, \funcionesGenerales::utf8_encode('Error creando mreg_rue_radicacion (responsable) : ' . $_SESSION["generales"]["mensajeerror"]));
            }

            // mreg_rue_radicacion_estados
            $arrCampos = array(
                'numerointernorue',
                'fecha',
                'hora',
                'usuario',
                'estado'
            );
            $arrValores = array(
                "'" . $_SESSION["tramite"]["rues_numerointerno"] . "'",
                "'" . date("Ymd") . "'",
                "'" . date("His") . "'",
                "'" . $_SESSION["tramite"]["idusuario"] . "'",
                "'" . $estadorues . "'"
            );
            $res1 = insertarRegistrosMysqliApi($mysqli, 'mreg_rue_radicacion_estados', $arrCampos, $arrValores);
            if ($res1 == false) {
                \logApi::general2($nameLog, $idSolicitudPago, \funcionesGenerales::utf8_encode('Error creando mreg_rue_radicacion_estados (responsable) : ' . $_SESSION["generales"]["mensajeerror"]));
            }

            // mreg_rue_radicacion_servicios
            $arrCampos = array(
                'numerointernorue',
                'secuencia',
                'codigoservicio',
                'nombreservicio',
                'nombrebase',
                'valorbase',
                'valorliquidacion',
                'cantidad',
                'ctrbase',
                'ctrren',
                'matricula',
                'anorenovacion'
            );
            $arrValores = array();
            $i = 0;
            foreach ($_SESSION["tramite"]["rues_servicios"] as $s) {
                $i++;
                $arrValores[$i] = array(
                    "'" . $_SESSION["tramite"]["rues_numerointerno"] . "'",
                    $i,
                    "'" . $s["codigo_servicio"] . "'",
                    "'" . $s["descripcion_servicio"] . "'",
                    "'" . addslashes($s["nombre_base"]) . "'",
                    $s["valor_base"],
                    $s["valor_liquidacion"],
                    $s["cantidad_servicio"],
                    "'" . $s["indicador_base"] . "'",
                    "'" . $s["indicador_renovacion"] . "'",
                    "'" . ltrim($s["matricula_servicio"], "0") . "'",
                    "'" . $s["ano_renovacion"] . "'"
                );
            }
            $res1 = insertarRegistrosBloqueMysqliApi($mysqli, 'mreg_rue_radicacion_servicios', $arrCampos, $arrValores);
            if ($res1 == false) {
                \logApi::general2($nameLog, $idSolicitudPago, \funcionesGenerales::utf8_encode('Error creando mreg_rue_radicacion_servicios (responsable) : ' . $_SESSION["generales"]["mensajeerror"]));
            }
        }
        \logApi::general2($nameLog, $idSolicitudPago, \funcionesGenerales::utf8_encode('En caso de tramites RUES como responsable, Creo la tabla rue_radicacion en SII, Creo la tabla de certificados virtuales'));

        // ******************************************************************************************************** //
        // Genera soporte de caja en el repositorio
        // ******************************************************************************************************** //
        // \logApi::general2($nameLog, $idSolicitudPago, \funcionesGenerales::utf8_encode('Genera Soportes de Caja en el Repositorio'));        
        \funcionesRegistrales::generarReciboCajaRepositorio($mysqli, $matricularecibo, $proponenterecibo, array(), $tiporeg, 'SII', 'S');
        \logApi::general2($nameLog, $idSolicitudPago, \funcionesGenerales::utf8_encode('Genero recibo de caja en repositorio (Servicios)'));

        if (trim((string) $_SESSION["tramite"]["numerorecibogob"]) != '') {
            \funcionesRegistrales::generarReciboCajaRepositorio($mysqli, $matricularecibo, $proponenterecibo, array(), $tiporeg, 'SII', 'G');
            \logApi::general2($nameLog, $idSolicitudPago, \funcionesGenerales::utf8_encode('Genero recibo de caja en repositorio (Gobernacion)'));
        }


        // ************************************************************************************************************** //
        // Si existen transacciones asociadas a la liquidacion entonces crea los actos para adelantar trabajo del abogado
        // En la tabla mreg_estudio_actos_registro 
        // ************************************************************************************************************** //
        $_SESSION["generales"]["icontrol"] = 0;
        $iActos = 0;
        $arrTrans = retornarRegistrosMysqliApi($mysqli, 'mreg_liquidacion_transacciones', "idliquidacion=" . $_SESSION["tramite"]["idliquidacion"], "idsecuencia,idtransaccion");
        if ($arrTrans && !empty($arrTrans)) {
            foreach ($arrTrans as $tra) {
                $tra["razonsocialbase64"] = retornarRegistroMysqliApi($mysqli, 'mreg_liquidacion_transacciones_campos', "idliquidacion=" . $tra["idliquidacion"] . " and idsecuencia=" . $tra["idsecuencia"] . " and campo='razonsocialbase64'", "contenido");
                if ($tra["razonsocialbase64"] != '') {
                    $tra["razonsocial"] = base64_decode($tra["razonsocialbase64"]);
                }
                $arrTemX = retornarRegistroMysqliApi($mysqli, 'mreg_transacciones', "idcampo='" . $tra["idtransaccion"] . "'");
                if ($arrTemX && !empty($arrTemX)) {
                    if ($arrTemX["idacto1"] != '') {
                        $controlacto = 'si';
                        if ($arrTemX["condicionacto1"] != '') {
                            eval($arrTemX["condicionacto1"]);
                        }
                        if ($controlacto == 'si') {
                            $iActos++;
                            $xres = \funcionesRegistrales::adicionarActoEstudioAsentarRecibo($mysqli, 1, $tra, $arrTemX, $_SESSION["generales"]["codigobarras1"]);
                            if ($xres === false) {
                                \logApi::general2($nameLog, $idSolicitudPago, \funcionesGenerales::utf8_encode('Error dicionando a mreg_estudio_actos_registro (1)'));
                            }
                        }
                    }
                    if ($arrTemX["idacto2"] != '') {
                        $controlacto = 'si';
                        if ($arrTemX["condicionacto2"] != '') {
                            eval($arrTemX["condicionacto2"]);
                        }
                        if ($controlacto == 'si') {
                            $iActos++;
                            $xres = \funcionesRegistrales::adicionarActoEstudioAsentarRecibo($mysqli, 2, $tra, $arrTemX, $_SESSION["generales"]["codigobarras1"]);
                            if ($xres === false) {
                                \logApi::general2($nameLog, $idSolicitudPago, \funcionesGenerales::utf8_encode('Error dicionando a mreg_estudio_actos_registro (2)'));
                            }
                        }
                    }
                    if ($arrTemX["idacto3"] != '') {
                        $controlacto = 'si';
                        if ($arrTemX["condicionacto3"] != '') {
                            eval($arrTemX["condicionacto3"]);
                        }
                        if ($controlacto == 'si') {
                            $iActos++;
                            $xres = \funcionesRegistrales::adicionarActoEstudioAsentarRecibo($mysqli, 3, $tra, $arrTemX, $_SESSION["generales"]["codigobarras1"]);
                            if ($xres === false) {
                                \logApi::general2($nameLog, $idSolicitudPago, \funcionesGenerales::utf8_encode('Error dicionando a mreg_estudio_actos_registro (3)'));
                            }
                        }
                    }
                    if ($arrTemX["idacto4"] != '') {
                        $controlacto = 'si';
                        if ($arrTemX["condicionacto4"] != '') {
                            eval($arrTemX["condicionacto4"]);
                        }
                        if ($controlacto == 'si') {
                            $iActos++;
                            $xres = \funcionesRegistrales::adicionarActoEstudioAsentarRecibo($mysqli, 4, $tra, $arrTemX, $_SESSION["generales"]["codigobarras1"]);
                            if ($xres === false) {
                                \logApi::general2($nameLog, $idSolicitudPago, \funcionesGenerales::utf8_encode('Error dicionando a mreg_estudio_actos_registro (4)'));
                            }
                        }
                    }
                    if ($arrTemX["idacto5"] != '') {
                        $controlacto = 'si';
                        if ($arrTemX["condicionacto5"] != '') {
                            eval($arrTemX["condicionacto5"]);
                        }
                        if ($controlacto == 'si') {
                            $iActos++;
                            $xres = \funcionesRegistrales::adicionarActoEstudioAsentarRecibo($mysqli, 5, $tra, $arrTemX, $_SESSION["generales"]["codigobarras1"]);
                            if ($xres === false) {
                                \logApi::general2($nameLog, $idSolicitudPago, \funcionesGenerales::utf8_encode('Error dicionando a mreg_estudio_actos_registro (5)'));
                            }
                        }
                    }
                }
            }
        }
        \logApi::general2($nameLog, $idSolicitudPago, \funcionesGenerales::utf8_encode('Adiciono actos a mreg_estudio_actos_registro, cuando sea el caso - ' . $iActos));

        // ************************************************************************************************************** //
        // 2016-10-19 : JINT
        // Si el pago es con cargo al cupo de afiliados, descuenta del cupo
        // ************************************************************************************************************** //
        if (strtoupper($_SESSION["tramite"]["cargoafiliacion"]) == 'SI' || $_SESSION["tramite"]["pagoafiliado"] != 0) {
            $saldoaflia = 0;
            $ultanorenafi = '';
            $formacalculocupoafiliados = retornarClaveValorMysqliApi($mysqli, '90.01.60');
            if ($formacalculocupoafiliados == 'CANTI_CERTIFICADOS') {
                $resx1 = \funcionesRegistrales::consultarSaldoAfiliadoCantidadMysqliApi($mysqli, $matAfiliacion);
                if ($resx1 && !empty($resx1)) {
                    foreach ($resx1 as $x1) {
                        if ($x1["tipo"] == 'PagoAfiliación' && $ultanorenafi == '') {
                            $saldoaflia = $x1["cupo"];
                            $ultanorenafi = substr($x1["fecha"], 0, 4);
                        }
                        if ($x1["tipo"] == 'Consumo') {
                            $saldoaflia = $x1["cupo"];
                        }
                    }
                }
            } else {
                $resx1 = \funcionesRegistrales::consultarSaldoAfiliadoMysqliApi($mysqli, $matAfiliacion);
                if ($resx1 && !empty($resx1)) {
                    foreach ($resx1 as $x1) {
                        if ($x1["tipo"] == 'PagoAfiliación' && $ultanorenafi == '') {
                            $saldoaflia = $x1["cupo"];
                            $ultanorenafi = substr($x1["fecha"], 0, 4);
                        }
                        if ($x1["tipo"] == 'Consumo') {
                            $saldoaflia = $x1["cupo"];
                        }
                    }
                }
            }
            unset($resx1);
            $_SESSION["tramite"]["saldoafiliado"] = $saldoaflia;
            $saldoafiliadoX = $saldoaflia;
        }
        \logApi::general2($nameLog, $idSolicitudPago, \funcionesGenerales::utf8_encode('Si el pago es con cargo al cupo de afiliados, descuento del cupo'));

        // ************************************************************************************************************** //
        // CONSTRUYE SOPORTES 
        //  2015-11-17 - JINT - No genera formularios si el trámite no fue firmado electronicamente
        //  2016-01-15 - JINT - Valida clave valor 90.01.61 para saber si debe o no generar formularios no firmados
        // ************************************************************************************************************** //
        // ************************************************************************************************************** //
        // SI se trata de renovación
        // ************************************************************************************************************** //
        if ($arrTipoTramite["gruposervicios"] == 'RegMer' || $arrTipoTramite["gruposervicios"] == 'RegEsadl') {
            if ($arrTipoTramite["esrenovacion"] == 'si') {
                if (!isset($_SESSION["tramite"]["tiporenovacion"]) || $_SESSION["tramite"]["tiporenovacion"] != 'prediligenciados') {
                    if (!isset($_SESSION["tramite"]["firmadoelectronicamente"])) {
                        $_SESSION["tramite"]["firmadoelectronicamente"] = '';
                    }
                    if (!isset($_SESSION["tramite"]["firmadomanuscrita"])) {
                        $_SESSION["tramite"]["firmadomanuscrita"] = '';
                    }
                    if (trim($_SESSION["tramite"]["firmadoelectronicamente"]) != 'si' && trim($_SESSION["tramite"]["firmadomanuscrita"]) != 'si') {
                        if (retornarClaveValorMysqliApi($mysqli, '90.01.61') != 'SI') {
                            // \logApi::general2($nameLog, $idSolicitudPago, \funcionesGenerales::utf8_encode('Entro a generar formularios NO FIRMADOS en el repositorio'));
                            \funcionesRegistrales::generarFormularioRenovacionRepositorio($mysqli, '', $nameLog);
                            \logApi::general2($nameLog, $idSolicitudPago, \funcionesGenerales::utf8_encode('Genero formularios NO FIRMADOS en el repositorio'));
                        } else {
                            \logApi::general2($nameLog, $idSolicitudPago, \funcionesGenerales::utf8_encode('No Genero formularios en el repositorio pues la clave valor 90.01.61 no esta activada'));
                        }
                    } else {
                        \logApi::general2($nameLog, $idSolicitudPago, \funcionesGenerales::utf8_encode('No Genero formularios en el repositorio pues es un tramite firmado electronicamente/manuscrita'));
                    }
                }
            }
        }
        \logApi::general2($nameLog, $idSolicitudPago, \funcionesGenerales::utf8_encode('genero soportes de formuliarios de renovacion cuando haya lugar (no firmados)'));

        // ************************************************************************************************************** //
        // SI se trata de matricula
        // ************************************************************************************************************** //
        if ($arrTipoTramite["gruposervicios"] == 'RegMer' || $arrTipoTramite["gruposervicios"] == 'RegEsadl') {
            if ($arrTipoTramite["asignamatricula"] == 'si') {
                if (!isset($_SESSION["tramite"]["firmadoelectronicamente"])) {
                    $_SESSION["tramite"]["firmadoelectronicamente"] = '';
                }
                if (!isset($_SESSION["tramite"]["firmadomanuscrita"])) {
                    $_SESSION["tramite"]["firmadomanuscrita"] = '';
                }
                if (trim($_SESSION["tramite"]["firmadoelectronicamente"]) != 'si' && trim($_SESSION["tramite"]["firmadomanuscrita"]) != 'si') {
                    if (retornarClaveValorMysqliApi($mysqli, '90.01.61') != 'SI') {
                        \funcionesRegistrales::generarFormularioRenovacionRepositorio($mysqli);
                        \logApi::general2($nameLog, $idSolicitudPago, \funcionesGenerales::utf8_encode('Genero formularios NO FIRMADOS en el repositorio'));
                    } else {
                        \logApi::general2($nameLog, $idSolicitudPago, \funcionesGenerales::utf8_encode('No Genero formularios en el repositorio pues la clave valor 90.01.61 no esta activada'));
                    }
                } else {
                    \logApi::general2($nameLog, $idSolicitudPago, \funcionesGenerales::utf8_encode('No Genero formularios en el repositorio pues es un tramite firmado electronicamente'));
                }
            }
        }
        \logApi::general2($nameLog, $idSolicitudPago, \funcionesGenerales::utf8_encode('genero soportes de formuliarios de matricula cuando haya lugar (no firmados)'));

        // ************************************************************************************************************** //
        // SI se trata de cambio de direccion
        // ************************************************************************************************************** //

        if ($arrTipoTramite["cambiodireccion"] == 'si') {
            if (trim($_SESSION["tramite"]["firmadoelectronicamente"]) != 'si' && trim($_SESSION["tramite"]["firmadomanuscrita"]) != 'si') {
                if (retornarClaveValorMysqliApi($mysqli, '90.01.61') != 'SI') {
                    \funcionesRegistrales::generarMutacionDireccionRepositorio($mysqli);
                    \logApi::general2($nameLog, $idSolicitudPago, \funcionesGenerales::utf8_encode('Genero formato de la mutacion en el repositorio'));
                } else {
                    \logApi::general2($nameLog, $idSolicitudPago, \funcionesGenerales::utf8_encode('No Genero formato de mutacion en el repositorio pues la clave valor 90.01.61 no esta activada'));
                }
            } else {
                \logApi::general2($nameLog, $idSolicitudPago, \funcionesGenerales::utf8_encode('No Genero formularios en el repositorio pues es un tramite firmado electronicamente'));
            }
        }
        \logApi::general2($nameLog, $idSolicitudPago, \funcionesGenerales::utf8_encode('genero soportes de mutacion de direccion cuando haya lugar (no firmados)'));

        // ************************************************************************************************************** //
        // SI se trata de cambio de actividad
        // ************************************************************************************************************** //

        if ($arrTipoTramite["cambioactividad"] == 'si') {
            if (trim($_SESSION["tramite"]["firmadoelectronicamente"]) != 'si' && trim($_SESSION["tramite"]["firmadomanuscrita"]) != 'si') {
                if (retornarClaveValorMysqliApi($mysqli, '90.01.61') != 'SI') {
                    \funcionesRegistrales::generarMutacionRepositorio($mysqli);
                    \logApi::general2($nameLog, $idSolicitudPago, \funcionesGenerales::utf8_encode('Genero formato de la mutacion en el repositorio'));
                } else {
                    \logApi::general2($nameLog, $idSolicitudPago, \funcionesGenerales::utf8_encode('No Genero formato de mutacion en el repositorio pues la clave valor 90.01.61 no esta activada'));
                }
            } else {
                \logApi::general2($nameLog, $idSolicitudPago, \funcionesGenerales::utf8_encode('No Genero formularios en el repositorio pues es un tramite firmado electronicamente'));
            }
        }
        \logApi::general2($nameLog, $idSolicitudPago, \funcionesGenerales::utf8_encode('genero soportes de mutacion de actividad cuando haya lugar (no firmados)'));

        // ************************************************************************************************************** //
        // SI se trata de cambio de nombre
        // ************************************************************************************************************** //
        if ($arrTipoTramite["cambionombre"] == 'si') {
            if (trim($_SESSION["tramite"]["firmadoelectronicamente"]) != 'si' && trim($_SESSION["tramite"]["firmadomanuscrita"]) != 'si') {
                if (retornarClaveValorMysqliApi($mysqli, '90.01.61') != 'SI') {
                    \funcionesRegistrales::generarMutacionNombreRepositorio($mysqli);
                    \logApi::general2($nameLog, $idSolicitudPago, \funcionesGenerales::utf8_encode('Genero  formato de la mutacion en el repositorio'));
                } else {
                    \logApi::general2($nameLog, $idSolicitudPago, \funcionesGenerales::utf8_encode('No Genero formato de mutacion en el repositorio pues la clave valor 90.01.61 no esta activada'));
                }
            } else {
                \logApi::general2($nameLog, $idSolicitudPago, \funcionesGenerales::utf8_encode('No Genero formularios en el repositorio pues es un tramite firmado electronicamente'));
            }
        }
        \logApi::general2($nameLog, $idSolicitudPago, \funcionesGenerales::utf8_encode('genero soportes de mutacion de nombre cuando haya lugar (no firmados)'));

        // ******************************************************************************************************** //
        // Traslada al repositorio los formatos y documentos anexos al tramite (liquidacion)
        // Documentos anexos al tramite, como son Copia de documentos de identidad, certificaciones, etc
        // Anexos tipo 501 de la tabla documentosanexos
        // ******************************************************************************************************** //
        // \logApi::general2($nameLog, $idSolicitudPago, \funcionesGenerales::utf8_encode('Traslada al repositorio los formatos y documentos anexos al tramite (liquidacion)'));
        $arrTem = retornarRegistrosMysqliApi($mysqli, 'mreg_anexos_liquidaciones', "idliquidacion= " . $_SESSION["tramite"]["idliquidacion"] . " and tipoanexo='501' and eliminado <> 'SI'", "idanexo");
        if ($arrTem && !empty($arrTem)) {
            foreach ($arrTem as $t) {
                $namefx = PATH_ABSOLUTO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/' . $t["path"] . $t["idanexo"] . '.' . $t["tipoarchivo"];
                if (file_exists($namefx)) {
                    $tem = explode("-", $t["idtipodoc"]);
                    $tipoDocumento = trim($tem[0]);
                    $pptx = '';
                    $matx = '';
                    if ($arrTipoTramite["recibeproponente"] == 'si') {
                        $pptx = ltrim($t["expediente"], "0");
                        $matx = '';
                    } else {
                        $pptx = '';
                        $matx = ltrim($t["expediente"], "0");
                    }
                    if ($t["fechadoc"] == '') {
                        $t["fechadoc"] = date("Ymd");
                    }
                    if ($t["txtorigendoc"] == '') {
                        $t["txtorigendoc"] = 'EL COMERCIANTE';
                    }

                    $id = \funcionesRegistrales::grabarAnexoRadicacion(
                                    $mysqli, // Conexion BD
                                    $_SESSION["generales"]["codigobarras1"], // Cödigo de barras
                                    $_SESSION["tramite"]["numerorecibo"], // Número del recibo
                                    $_SESSION["tramite"]["numerooperacion"], // Operación
                                    ltrim($t["identificacion"], "0"), // identificación
                                    trim($t["nombre"]), // Nombre
                                    '', // Acreedor
                                    '', // Nombre acreedor
                                    $matx, // Matricula
                                    $pptx, // Proponente
                                    $tipoDocumento, // Tipo de documento 
                                    $t["numdoc"], // Numero del documento
                                    $t["fechadoc"],
                                    '', // Codigo de origen
                                    $t["txtorigendoc"], // txt origen del documento
                                    '', // Clasificacion
                                    '', // Numero del contrato
                                    '', // Idfuente
                                    1, // version
                                    '', // Path
                                    '1', // Estado
                                    date("Ymd"), // fecha de escaneo o generacion
                                    $_SESSION["generales"]["codigousuario"], // Código usuario
                                    '', // Caja
                                    '', // Libro
                                    $t["observaciones"], // Observaciones
                                    '', // Libro
                                    '', // Numero de registro en libros
                                    '', // Dupli
                                    $t["bandeja"], // Bandeja
                                    'N', // Soporte del recibo de caja
                                    $t["identificador"], // Identificador del tipo de soporte
                                    $t["tipoanexo"], // Tipo de anexo	
                                    '' // proceso especial
                    );

                    //
                    $dirx = date("Ymd");
                    $path = PATH_ABSOLUTO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/mreg/' . $dirx;
                    if (!is_dir($path) || !is_readable($path)) {
                        mkdir($path, 0777);
                        \funcionesGenerales::crearIndex($path);
                    }

                    $pathsalida = 'mreg/' . $dirx . '/' . $id . '.' . $t["tipoarchivo"];
                    copy($namefx, PATH_ABSOLUTO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/' . $pathsalida);
                    \funcionesRegistrales::grabarPathAnexoRadicacion($mysqli, $id, $pathsalida);
                    $txt = '';
                    foreach ($t as $key => $valor) {
                        if (!is_numeric($key)) {
                            $txt .= $key . ' = > ' . $valor . "\r\n";
                        }
                    }
                    $txt .= 'Anexos (tipo de anexo 501) : Archivo de entrada (mreg_anexo_liquidaciones) : ' . $namefx . "\r\n";
                    $txt .= 'Anexos (tipo de anexo 501) : Archivo de salida (mreg_radicacionesanexos) : ' . PATH_ABSOLUTO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/' . $pathsalida . "\r\n";
                    \logApi::general2($nameLog, $idSolicitudPago, \funcionesGenerales::utf8_encode('Traslado soporte desde mreg_anexos_liquidaciones a mreg_radicacionesanexos : ' . $txt));
                }
            }
        }
        \logApi::general2($nameLog, $idSolicitudPago, \funcionesGenerales::utf8_encode('Traslado anexos del tramite de liquidacionanexos a radicacionanexos'));

        // ******************************************************************************************************** //
        // Traslada al repositorio los formularios y solicitudes firmadas electronicamente como son
        // - Formularios
        // - Solicitudes de mutacion
        // - Solicitudes de cancelacion
        // - Solicitudes de cambio de nombre
        // - Anexos tipo 503 de la tabla documentosanexos
        // ******************************************************************************************************** //
        $arrTem = retornarRegistrosMysqliApi($mysqli, 'mreg_anexos_liquidaciones', "idliquidacion= " . $_SESSION["tramite"]["idliquidacion"] . " and tipoanexo='503' and eliminado <> 'SI'", "idanexo");
        if ($arrTem && !empty($arrTem)) {
            foreach ($arrTem as $t) {
                $namefx = PATH_ABSOLUTO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/' . $t["path"] . '/' . $t["idanexo"] . '.' . $t["tipoarchivo"];
                if (file_exists($namefx)) {
                    $tem = explode("-", $t["idtipodoc"]);
                    $tipoDocumento = $tem[0];
                    $pptx = '';
                    $matx = '';
                    if ($arrTipoTramite["recibeproponente"] == 'si') {
                        $pptx = ltrim($t["expediente"], "0");
                        $matx = '';
                    } else {
                        $pptx = '';
                        $matx = ltrim($t["expediente"], "0");
                    }
                    $id = \funcionesRegistrales::grabarAnexoRadicacion(
                                    $mysqli, // Conexion a BD
                                    $_SESSION["generales"]["codigobarras1"], // Código de barras
                                    $_SESSION["tramite"]["numerorecibo"], // Número de recibo
                                    $_SESSION["tramite"]["numerooperacion"], // Número de operación
                                    ltrim($t["identificacion"], "0"), // Número de identificación del afectado
                                    trim($t["nombre"]), // Nombre del afectado
                                    '', // Acreedor
                                    '', // Nombre acreedor
                                    $matx, // Matrícula afectada
                                    $pptx, // proponente afectado
                                    $tipoDocumento, // Tipo de documento
                                    $t["numdoc"], // Numero del documento
                                    $t["fechadoc"],
                                    '', // Codigo de origen
                                    $t["txtorigendoc"],
                                    '', // Clasificacion
                                    '', // Numero del contrato
                                    '', // Idfuente
                                    1, // version
                                    '', // Path
                                    '1', // Estado
                                    date("Ymd"), // fecha de escaneo o generacion
                                    $_SESSION["generales"]["codigousuario"], // Código del usuario
                                    '', // Caja
                                    '', // Libro
                                    $t["observaciones"], // Observaciones
                                    '', // Libro
                                    '', // Numero del registro
                                    '', // Dupli
                                    $t["bandeja"], // Bandeja de registro
                                    'N', // Soporte del recibo de caja?
                                    $t["identificador"], // identificador del tipo de soporte
                                    $t["tipoanexo"] // Tipo de anexo
                    );
                    // $namefx = PATH_ABSOLUTO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/' . $t["path"] . '/' . $t["idanexo"] . '.' . $t["tipoarchivo"];
                    //
                    $dirx = date("Ymd");
                    $path = PATH_ABSOLUTO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/mreg/' . $dirx;
                    if (!is_dir($path) || !is_readable($path)) {
                        mkdir($path, 0777);
                        \funcionesGenerales::crearIndex($path);
                    }

                    $pathsalida = 'mreg/' . $dirx . '/' . $id . '.' . $t["tipoarchivo"];
                    copy($namefx, PATH_ABSOLUTO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/' . $pathsalida);
                    \funcionesRegistrales::grabarPathAnexoRadicacion($mysqli, $id, $pathsalida);
                    $txt = '';
                    foreach ($t as $key => $valor) {
                        if (!is_numeric($key)) {
                            $txt .= $key . ' = > ' . $valor . "\r\n";
                        }
                    }
                    $txt .= 'Formatos y solicitudes firmadas electronicamente (tipo de anexo 503) : Archivo de entrada (mreg_anexo_liquidaciones) : ' . $namefx . "\r\n";
                    $txt .= 'Formatos y solicitudes firmadas electronicamente (tipo de anexo 503) : Archivo de salida (mreg_radicacionesanexos) : ' . PATH_ABSOLUTO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/' . $pathsalida . "\r\n";
                    \logApi::general2($nameLog, $idSolicitudPago, \funcionesGenerales::utf8_encode('Traslado soporte desde mreg_anexos_liquidaciones a mreg_radicacionesanexos : ' . $txt));
                }
            }
        }
        \logApi::general2($nameLog, $idSolicitudPago, \funcionesGenerales::utf8_encode('Traslado al repositorio los formularios y solicitudes firmadas electronicamente'));

        // ******************************************************************************************************** //    
        // 2017-11-07: JINT Incluye en el repositorio de imágenes el sobre digital si este existe
        // Traslada al repositorio el sobre digital si este existe
        // ******************************************************************************************************** //
        $arrTem = retornarRegistroMysqliApi($mysqli, 'mreg_liquidacion_sobre', "idliquidacion= " . $_SESSION["tramite"]["idliquidacion"], "*", "U");
        if ($arrTem && !empty($arrTem)) {
            $nombre = $arrTem["apellido1firmante"];
            if (trim($arrTem["apellido2firmante"])) {
                $nombre .= ' ' . $arrTem["apellido2firmante"];
            }
            if (trim($arrTem["nombre1firmante"])) {
                $nombre .= ' ' . $arrTem["nombre1firmante"];
            }
            if (trim($arrTem["nombre2firmante"])) {
                $nombre .= ' ' . $arrTem["nombre2firmante"];
            }
            if (!defined('TIPO_DOC_SOBRE_MERCANTIL')) {
                define('TIPO_DOC_SOBRE_MERCANTIL', '');
            }
            if (!defined('TIPO_DOC_SOBRE_PROPONENTES')) {
                define('TIPO_DOC_SOBRE_PROPONENTES', '');
            }
            if (!defined('TIPO_DOC_SOBRE_ESADL')) {
                define('TIPO_DOC_SOBRE_ESADL', '');
            }
            if ($bandejaDigitalizacion == '4.-REGMER') {
                $tipoDocumento = TIPO_DOC_SOBRE_MERCANTIL;
            }
            if ($bandejaDigitalizacion == '5.-REGESADL') {
                $tipoDocumento = TIPO_DOC_SOBRE_ESADL;
            }
            if ($bandejaDigitalizacion == '6.-REGPRO') {
                $tipoDocumento = TIPO_DOC_SOBRE_PROPONENTES;
            }
            $id = \funcionesRegistrales::grabarAnexoRadicacion(
                            $mysqli, // Conexion BD
                            $_SESSION["generales"]["codigobarras1"], // Codigo de barras
                            $_SESSION["tramite"]["numerorecibo"], // Numero del recibo
                            $_SESSION["tramite"]["numerooperacion"], // Numero de operacion
                            ltrim($arrTem["identificacionfirmante"], "0"), // Identificacion
                            trim($nombre), // Nombre
                            '', // Acreedor
                            '', // Nombre acreedor
                            '', // Matricula
                            '', // Proponente
                            $tipoDocumento, // Tipo de documento 
                            '', // Numero del documento
                            $arrTem["fecha"],
                            '', // Codigo de origen
                            'EL CLIENTE', // txt origen del documento
                            '', // Clasificacion
                            '', // Numero del contrato
                            '', // Idfuente
                            1, // version
                            '', // Path
                            '1', // Estado
                            date("Ymd"), // fecha de escaneo o generacion
                            $_SESSION["generales"]["codigousuario"], // COdigo del usuario
                            '', // Caja
                            '', // Libro
                            'SOBRE DIGITAL', // Descripcion
                            '', // Libro
                            '', // Numero de registro en libros
                            '', // Dupli
                            $bandejaDigitalizacion, // Bandeja
                            'N', // Soporte del recibo de caja
                            '', // Identificador del tipo de soporte
                            '601' // Tipo de anexo	
            );
            $tipoarchivo = \funcionesGenerales::encontrarExtension($arrTem["path"]);
            $namefx = PATH_ABSOLUTO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/' . $arrTem["path"];

            //
            $dirx = date("Ymd");
            $path = PATH_ABSOLUTO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/mreg/' . $dirx;
            if (!is_dir($path) || !is_readable($path)) {
                mkdir($path, 0777);
                \funcionesGenerales::crearIndex($path);
            }

            $pathsalida = 'mreg/' . $dirx . '/' . $id . '.' . $tipoarchivo;
            copy($namefx, PATH_ABSOLUTO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/' . $pathsalida);
            \funcionesRegistrales::grabarPathAnexoRadicacion($mysqli, $id, $pathsalida);
            \logApi::general2($nameLog, $idSolicitudPago, \funcionesGenerales::utf8_encode('Traslado el sobre digital de la liquidación al repositorio : ' . $arrTem["path"]));
        }
        \logApi::general2($nameLog, $idSolicitudPago, \funcionesGenerales::utf8_encode('Incluyo en el repositorio de imágenes el sobre digital si este existe'));

        // ******************************************************************************************************** //
        // 2020-03-13: JINT
        // Actualiza tabla de datos_empresas
        // ******************************************************************************************************** //
        if (trim($_SESSION["tramite"]["identificacioncliente"]) != '222222222222') {
            $arrCampos = array(
                'tipoidentificacion',
                'identificacion',
                'tipopersona',
                'razonsocial',
                'nombreregistrado',
                'primernombre',
                'segundonombre',
                'primerapellido',
                'segundoapellido',
                'particula',
                'email',
                'responsabilidadtributaria',
                'codigoregimen',
                'codigoimpuesto',
                'nombreimpuesto',
                'responsabilidadfiscal',
                'telefono1',
                'telefono2',
                'zonapostal',
                'pais',
                'lenguaje',
                'dircom',
                'muncom',
                'codposcom',
                'dirnot',
                'munnot',
                'codposnot'
            );
            if (trim($_SESSION["tramite"]["apellido1cliente"]) == '') {
                $tipopersona = '1';
            } else {
                $tipopersona = '2';
            }
            if (!isset($_SESSION["tramite"]["responsabilidadtributaria"])) {
                $_SESSION["tramite"]["responsabilidadtributaria"] = '';
            }
            if (!isset($_SESSION["tramite"]["responsabilidadfiscal"])) {
                $_SESSION["tramite"]["responsabilidadfiscal"] = '';
            }
            if (!isset($_SESSION["tramite"]["codigoregimen"])) {
                $_SESSION["tramite"]["codigoregimen"] = '';
            }
            if (!isset($_SESSION["tramite"]["codigoimpuesto"])) {
                $_SESSION["tramite"]["codigoimpuesto"] = '';
            }
            if (!isset($_SESSION["tramite"]["nombreimpuesto"])) {
                $_SESSION["tramite"]["nombreimpuesto"] = '';
            }
            if (!isset($_SESSION["tramite"]["zonapostal"])) {
                $_SESSION["tramite"]["zonapostal"] = '';
            }
            if (!isset($_SESSION["tramite"]["pais"])) {
                $_SESSION["tramite"]["pais"] = '';
            }
            if (!isset($_SESSION["tramite"]["lenguaje"])) {
                $_SESSION["tramite"]["lenguaje"] = '';
            }
            if (!isset($_SESSION["tramite"]["direccionnot"])) {
                $_SESSION["tramite"]["direccionnot"] = '';
            }
            if (!isset($_SESSION["tramite"]["idmunicipionot"])) {
                $_SESSION["tramite"]["idmunicipionot"] = '';
            }
            if (!isset($_SESSION["tramite"]["codposnot"])) {
                $_SESSION["tramite"]["codposnot"] = '';
            }
            if ($_SESSION["tramite"]["idtipoidentificacioncliente"] != '2') {
                $xname1 = $_SESSION["tramite"]["apellido1cliente"];
                if (trim($_SESSION["tramite"]["apellido2cliente"]) != '') {
                    $xname1 .= ' ' . $_SESSION["tramite"]["apellido2cliente"];
                }
                if (trim($_SESSION["tramite"]["nombre1cliente"]) != '') {
                    $xname1 .= ' ' . $_SESSION["tramite"]["nombre1cliente"];
                }
                if (trim($_SESSION["tramite"]["nombre2cliente"]) != '') {
                    $xname1 .= ' ' . $_SESSION["tramite"]["nombre2cliente"];
                }
                if (strlen($xname1) > strlen(trim($_SESSION["tramite"]["razonsocialcliente"]))) {
                    $_SESSION["tramite"]["razonsocialcliente"] = $xname1;
                }
            }
            $arrValores = array(
                "'" . $_SESSION["tramite"]["idtipoidentificacioncliente"] . "'",
                "'" . $_SESSION["tramite"]["identificacioncliente"] . "'",
                "'" . $tipopersona . "'",
                "'" . addslashes(trim($_SESSION["tramite"]["razonsocialcliente"])) . "'",
                "'" . addslashes(trim($_SESSION["tramite"]["razonsocialcliente"])) . "'",
                "'" . addslashes($_SESSION["tramite"]["nombre1cliente"]) . "'",
                "'" . addslashes($_SESSION["tramite"]["nombre2cliente"]) . "'",
                "'" . addslashes($_SESSION["tramite"]["apellido1cliente"]) . "'",
                "'" . addslashes($_SESSION["tramite"]["apellido2cliente"]) . "'",
                "'" . addslashes($_SESSION["tramite"]["particulacliente"]) . "'",
                "'" . addslashes($_SESSION["tramite"]["email"]) . "'",
                "'" . addslashes($_SESSION["tramite"]["responsabilidadtributaria"]) . "'",
                "'" . addslashes($_SESSION["tramite"]["codigoregimen"]) . "'",
                "'" . addslashes($_SESSION["tramite"]["codigoimpuesto"]) . "'",
                "'" . addslashes($_SESSION["tramite"]["nombreimpuesto"]) . "'",
                "'" . addslashes($_SESSION["tramite"]["responsabilidadfiscal"]) . "'",
                "'" . addslashes($_SESSION["tramite"]["telefono"]) . "'",
                "'" . addslashes($_SESSION["tramite"]["movil"]) . "'",
                "'" . addslashes($_SESSION["tramite"]["zonapostal"]) . "'",
                "'" . addslashes($_SESSION["tramite"]["pais"]) . "'",
                "'" . addslashes($_SESSION["tramite"]["lenguaje"]) . "'",
                "'" . addslashes($_SESSION["tramite"]["direccion"]) . "'",
                "'" . addslashes($_SESSION["tramite"]["idmunicipio"]) . "'",
                "'" . addslashes($_SESSION["tramite"]["codposcom"]) . "'",
                "'" . addslashes($_SESSION["tramite"]["direccionnot"]) . "'",
                "'" . addslashes($_SESSION["tramite"]["idmunicipionot"]) . "'",
                "'" . addslashes($_SESSION["tramite"]["codposnot"]) . "'"
            );
            $datemp = retornarRegistroMysqliApi($mysqli, 'datos_empresas', "tipoidentificacion='" . $_SESSION["tramite"]["idtipoidentificacioncliente"] . "' and identificacion='" . $_SESSION["tramite"]["identificacioncliente"] . "'");
            if ($datemp === false || empty($datemp)) {
                insertarRegistrosMysqliApi($mysqli, 'datos_empresas', $arrCampos, $arrValores);
            } else {
                if (
                        $datemp["tipoidentificacion"] != $_SESSION["tramite"]["idtipoidentificacioncliente"] ||
                        $datemp["identificacion"] != $_SESSION["tramite"]["identificacioncliente"] ||
                        $datemp["tipopersona"] != $tipopersona ||
                        $datemp["razonsocial"] != $_SESSION["tramite"]["razonsocialcliente"] ||
                        $datemp["nombreregistrado"] != $_SESSION["tramite"]["razonsocialcliente"] ||
                        $datemp["primernombre"] != $_SESSION["tramite"]["nombre1cliente"] ||
                        $datemp["segundonombre"] != $_SESSION["tramite"]["nombre2cliente"] ||
                        $datemp["primerapellido"] != $_SESSION["tramite"]["apellido1cliente"] ||
                        $datemp["segundoapellido"] != $_SESSION["tramite"]["apellido2cliente"] ||
                        $datemp["particula"] != $_SESSION["tramite"]["particulacliente"] ||
                        $datemp["email"] != $_SESSION["tramite"]["email"] ||
                        $datemp["responsabilidadtributaria"] != $_SESSION["tramite"]["responsabilidadtributaria"] ||
                        $datemp["codigoregimen"] != $_SESSION["tramite"]["codigoregimen"] ||
                        $datemp["codigoimpuesto"] != $_SESSION["tramite"]["codigoimpuesto"] ||
                        $datemp["nombreimpuesto"] != $_SESSION["tramite"]["nombreimpuesto"] ||
                        $datemp["responsabilidadfiscal"] != $_SESSION["tramite"]["responsabilidadfiscal"] ||
                        $datemp["telefono1"] != $_SESSION["tramite"]["telefono"] ||
                        $datemp["telefono2"] != $_SESSION["tramite"]["movil"] ||
                        $datemp["zonapostal"] != $_SESSION["tramite"]["zonapostal"] ||
                        $datemp["pais"] != $_SESSION["tramite"]["pais"] ||
                        $datemp["lenguaje"] != $_SESSION["tramite"]["lenguaje"] ||
                        $datemp["dircom"] != $_SESSION["tramite"]["direccion"] ||
                        $datemp["muncom"] != $_SESSION["tramite"]["idmunicipio"] ||
                        $datemp["codposcom"] != $_SESSION["tramite"]["codposcom"] ||
                        $datemp["dirnot"] != $_SESSION["tramite"]["direccionnot"] ||
                        $datemp["munnot"] != $_SESSION["tramite"]["idmunicipionot"] ||
                        $datemp["codposnot"] != $_SESSION["tramite"]["codposnot"]) {
                    regrabarRegistrosMysqliApi($mysqli, 'datos_empresas', $arrCampos, $arrValores, "tipoidentificacion='" . $_SESSION["tramite"]["idtipoidentificacioncliente"] . "' and identificacion='" . $_SESSION["tramite"]["identificacioncliente"] . "'");
                }
            }
        }
        \logApi::general2($nameLog, $idSolicitudPago, \funcionesGenerales::utf8_encode('Actualizo la tabla datos_empresas con datos del cliente'));

        // ************************************************************************************************* //
        // 2016-09-15 : JINT
        // Crea archivo en SII para Workflow
        // Solo si el sistema SII esta activado
        // ************************************************************************************************* //
        if (!defined('GENERAR_WORKFLOW_SOPORTES')) {
            define('GENERAR_WORKFLOW_SOPORTES', 'S');
        }
        if (GENERAR_WORKFLOW_SOPORTES == 'S' || GENERAR_WORKFLOW_SOPORTES == '') {

            //
            // \logApi::general2($nameLog, $idSolicitudPago, \funcionesGenerales::utf8_encode('Entro a generar archivo del recibo para el workflow - operacion No. ' . $_SESSION["tramite"]["numerooperacion"] . ', recibo No. ' . $_SESSION["tramite"]["numerorecibo"]));
            if (!is_dir(PATH_ABSOLUTO_SITIO . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/workflow')) {
                mkdir(PATH_ABSOLUTO_SITIO . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/workflow');
                \funcionesGenerales::crearIndex(PATH_ABSOLUTO_SITIO . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/workflow');
            }
            if (!is_dir(PATH_ABSOLUTO_SITIO . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/workflow/recibos')) {
                mkdir(PATH_ABSOLUTO_SITIO . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/workflow/recibos');
                \funcionesGenerales::crearIndex(PATH_ABSOLUTO_SITIO . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/workflow/recibos');
            }
            if (!is_dir(PATH_ABSOLUTO_SITIO . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/workflow/imgrecibos')) {
                mkdir(PATH_ABSOLUTO_SITIO . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/workflow/imgrecibos');
                \funcionesGenerales::crearIndex(PATH_ABSOLUTO_SITIO . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/workflow/imgrecibos');
            }
            if (!is_dir(PATH_ABSOLUTO_SITIO . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/workflow/sellos')) {
                mkdir(PATH_ABSOLUTO_SITIO . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/workflow/sellos');
                \funcionesGenerales::crearIndex(PATH_ABSOLUTO_SITIO . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/workflow/sellos');
            }

            //
            $sedey = $usuCajero["idsede"];
            if ($_SESSION["generales"]["cajero"] == 'USUPUBXX') {
                $usuy = 'WWW';
            } else {
                if ($_SESSION["generales"]["cajero"] == 'RUE') {
                    $usuy = 'RUE';
                } else {
                    $usux = retornarRegistroMysqliApi($mysqli, 'usuarios', "idusuario='" . $_SESSION["generales"]["cajero"] . "'");
                    if ($usux && !empty($usux)) {
                        $usuy = $usux["idcodigosirepcaja"];
                    }
                    if ($usuy == '') {
                        $usuy = substr($_SESSION["generales"]["cajero"], 0, 3);
                    }
                    if (strlen($usuy) > 3) {
                        $usuy1 = substr($usuy, 0, 3);
                        $usuy = $usuy1;
                    }
                }
            }

            // ****************************************************************************** //
            // Genera recibo con servicios de cámara
            // Genera archivo para SGD
            // ****************************************************************************** //
            if ($_SESSION["generales"]["codigoempresa"] == '11') {
                $ex = explode("-", $_SESSION["tramite"]["numerooperacion"]);
                $sedey = $ex[0];
                $sec = substr($ex[3], 1);
                $ope = $sedey . $usuy . substr($_SESSION["tramite"]["fecharecibo"], 4, 4) . $sec;
            } else {
                $ope = $_SESSION["tramite"]["numerooperacion"];
            }

            //
            $expbas = '';
            $nombas = '';

            $nfr = PATH_ABSOLUTO_SITIO . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/workflow/recibos/' . $_SESSION["tramite"]["numerorecibo"] . '.txt';
            $fr = fopen($nfr, 'wt');
            fwrite($fr, \funcionesGenerales::tamano80SentarPago('<DATOS-RECIBO=' . $_SESSION["tramite"]["numerorecibo"] . '>') . chr(10));
            fwrite($fr, \funcionesGenerales::tamano80SentarPago('FECHA=' . $_SESSION["tramite"]["fecharecibo"]) . chr(10));
            fwrite($fr, \funcionesGenerales::tamano80SentarPago('HORA=' . substr($_SESSION["tramite"]["horarecibo"], 0, 4)) . chr(10));
            fwrite($fr, \funcionesGenerales::tamano80SentarPago('OPERADOR=' . $usuy) . chr(10));
            fwrite($fr, \funcionesGenerales::tamano80SentarPago('USUARIO=' . $_SESSION["generales"]["cajero"]) . chr(10));
            fwrite($fr, \funcionesGenerales::tamano80SentarPago('NUMERO DE OPERACION=' . $ope) . chr(10));
            fwrite($fr, \funcionesGenerales::tamano80SentarPago('VALOR RECIBO=' . sprintf("%010s", $totalcamara) . '.00') . chr(10));
            fwrite($fr, \funcionesGenerales::tamano80SentarPago('NOMBRE RECIBO=' . trim($_SESSION["tramite"]["nombrecliente"] . ' ' . $_SESSION["tramite"]["apellidocliente"])) . chr(10));
            fwrite($fr, \funcionesGenerales::tamano80SentarPago('IDENTIFICACION RECIBO=' . $_SESSION["tramite"]["idtipoidentificacioncliente"] . '-' . $_SESSION["tramite"]["identificacioncliente"]) . chr(10));
            fwrite($fr, \funcionesGenerales::tamano80SentarPago('SEDE=' . $sedey) . chr(10));
            fwrite($fr, \funcionesGenerales::tamano80SentarPago('NUC=' . $_SESSION["tramite"]["rues_numerounico"]) . chr(10));
            fwrite($fr, \funcionesGenerales::tamano80SentarPago('') . chr(10));
            $iSer = 0;
            foreach ($_SESSION["tramite"]["liquidacion"] as $k) {
                $incluir = '';
                if (!defined('SEPARAR_RECIBOS') || SEPARAR_RECIBOS == 'NO') {
                    $incluir = 'si';
                } else {
                    if (ltrim((string) $arrServs[$k["idservicio"]]["conceptodepartamental"], "0") == '') {
                        $incluir = 'si';
                    }
                }
                if ($incluir == 'si') {
                    $iSer++;
                    if ($iSer == 1) {
                        $expbas = $k["expediente"];
                        $nombas = $k["nombre"];
                    }
                    fwrite($fr, \funcionesGenerales::tamano80SentarPago('<SECUENCIA DE SERVICIO=' . sprintf("%03s", $iSer) . '>') . chr(10));
                    fwrite($fr, \funcionesGenerales::tamano80SentarPago('MATRICULA=' . sprintf("%08s", $k["expediente"])) . chr(10));
                    fwrite($fr, \funcionesGenerales::tamano80SentarPago('RAZON SOCIAL=' . $k["nombre"]) . chr(10));
                    fwrite($fr, \funcionesGenerales::tamano80SentarPago('CODIGO SERVICIO=' . $k["idservicio"]) . chr(10));
                    fwrite($fr, \funcionesGenerales::tamano80SentarPago('DESCRIPCION SERVICIO=' . retornarRegistroMysqliApi($mysqli, 'mreg_servicios', "idservicio='" . $k["idservicio"] . "'", "nombre")) . chr(10));
                    fwrite($fr, \funcionesGenerales::tamano80SentarPago('VALOR SERVICIO=' . sprintf("%010s", $k["valorservicio"]) . '.00') . chr(10));
                    if ($k["valorbase"] != 0) {
                        fwrite($fr, \funcionesGenerales::tamano80SentarPago('VALOR BASE=' . sprintf("%010s", $k["valorbase"]) . '.00') . chr(10));
                    }
                    fwrite($fr, \funcionesGenerales::tamano80SentarPago('ARCHIVADOR=' . \funcionesRegistrales::evaluarArchivadorAsentarRecibo($k["idservicio"])) . chr(10));
                    fwrite($fr, \funcionesGenerales::tamano80SentarPago('') . chr(10));
                }
            }
            $iCb = 0;
            $arrCb = retornarRegistrosMysqliApi($mysqli, 'mreg_est_codigosbarras', "codigobarras='" . ltrim($_SESSION["generales"]["codigobarras1"], "0") . "'");
            if ($arrCb && !empty($arrCb)) {
                if (
                        $_SESSION["tramite"]["tipotramite"] != 'certificadosvirtuales' &&
                        $_SESSION["tramite"]["tipotramite"] != 'certificadoselectronicos' &&
                        $_SESSION["tramite"]["tipotramite"] != 'certificadosespeciales' &&
                        $_SESSION["tramite"]["tipotramite"] != 'serviciosempresariales' &&
                        $_SESSION["tramite"]["tipotramite"] != 'rues01receptora' &&
                        $_SESSION["tramite"]["tipotramite"] != 'rues01responsable'
                ) {
                    foreach ($arrCb as $k) {
                        $iCb++;
                        fwrite($fr, \funcionesGenerales::tamano80SentarPago('<SECUENCIA DE DOCUMENTO=' . sprintf("%03s", $iCb) . '>') . chr(10));
                        fwrite($fr, \funcionesGenerales::tamano80SentarPago('MATRICULA=' . sprintf("%08s", $k["matricula"])) . chr(10));
                        fwrite($fr, \funcionesGenerales::tamano80SentarPago('INSCRIPCION=' . sprintf("%08s", $k["proponente"])) . chr(10));
                        fwrite($fr, \funcionesGenerales::tamano80SentarPago('RAZON SOCIAL=' . $k["nombre"]) . chr(10));
                        fwrite($fr, \funcionesGenerales::tamano80SentarPago('TIPO DOCUMENTO=' . $k["tipdoc"]) . chr(10));
                        fwrite($fr, \funcionesGenerales::tamano80SentarPago('NUMERO DOCUMENTO=' . $k["numdoc"]) . chr(10));
                        fwrite($fr, \funcionesGenerales::tamano80SentarPago('CODIGO DE BARRAS=' . sprintf("%015s", $k["codigobarras"])) . chr(10));
                        fwrite($fr, \funcionesGenerales::tamano80SentarPago('CODIGO BARRAS=' . sprintf("%015s", $k["codigobarras"])) . chr(10));
                        fwrite($fr, \funcionesGenerales::tamano80SentarPago('FECHA DOCUMENTO=' . $k["fecdoc"]) . chr(10));
                        fwrite($fr, \funcionesGenerales::tamano80SentarPago('ORIGEN DOCUMENTO=' . $k["oridoc"]) . chr(10));
                        fwrite($fr, \funcionesGenerales::tamano80SentarPago('CODIGO MUNICIPIO ORIGEN DOCUMENTO=' . $k["mundoc"]) . chr(10));
                        fwrite($fr, \funcionesGenerales::tamano80SentarPago('DESCRIPCION MUNICIPIO ORIGEN DOCUMENTO=' . retornarNombreMunicipioMysqliApi($mysqli, $k["mundoc"])) . chr(10));
                        fwrite($fr, \funcionesGenerales::tamano80SentarPago('') . chr(10));
                    }
                }
            }
            fwrite($fr, \funcionesGenerales::tamano80SentarPago('<FIN DATOS RECIBO>') . chr(10));
            fclose($fr);

            // ****************************************************************************** //
            // Genera recibo con srvicios de gobernacion
            // Genera archivo para SGD
            // ****************************************************************************** //
            if ($totalgobernacion != 0) {
                if ($_SESSION["generales"]["codigoempresa"] == '11') {
                    $ex = explode("-", $_SESSION["tramite"]["numerooperaciongob"]);
                    $sedey = $ex[0];
                    $sec = substr($ex[3], 1);
                    $ope = $sedey . $usuy . substr($_SESSION["tramite"]["fecharecibogob"], 4, 4) . $sec;
                } else {
                    $ope = $_SESSION["tramite"]["numerooperaciongob"];
                }

                //
                $expbas = '';
                $nombas = '';

                // Genera archivo para SGD
                $nfr = PATH_ABSOLUTO_SITIO . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/workflow/recibos/' . $_SESSION["tramite"]["numerorecibogob"] . '.txt';
                $fr = fopen($nfr, 'wt');
                fwrite($fr, \funcionesGenerales::tamano80SentarPago('<DATOS-RECIBO=' . $_SESSION["tramite"]["numerorecibogob"] . '>') . chr(10));
                fwrite($fr, \funcionesGenerales::tamano80SentarPago('FECHA=' . $_SESSION["tramite"]["fecharecibogob"]) . chr(10));
                fwrite($fr, \funcionesGenerales::tamano80SentarPago('HORA=' . substr($_SESSION["tramite"]["horarecibogob"], 0, 4)) . chr(10));
                fwrite($fr, \funcionesGenerales::tamano80SentarPago('OPERADOR=' . $usuy) . chr(10));
                fwrite($fr, \funcionesGenerales::tamano80SentarPago('USUARIO=' . $_SESSION["generales"]["cajero"]) . chr(10));
                fwrite($fr, \funcionesGenerales::tamano80SentarPago('NUMERO DE OPERACION=' . $ope) . chr(10));
                fwrite($fr, \funcionesGenerales::tamano80SentarPago('VALOR RECIBO=' . sprintf("%010s", $totalgobernacion) . '.00') . chr(10));
                fwrite($fr, \funcionesGenerales::tamano80SentarPago('NOMBRE RECIBO=' . trim($_SESSION["tramite"]["nombrecliente"] . ' ' . $_SESSION["tramite"]["apellidocliente"])) . chr(10));
                fwrite($fr, \funcionesGenerales::tamano80SentarPago('IDENTIFICACION RECIBO=' . $_SESSION["tramite"]["idtipoidentificacioncliente"] . '-' . $_SESSION["tramite"]["identificacioncliente"]) . chr(10));
                fwrite($fr, \funcionesGenerales::tamano80SentarPago('SEDE=' . $sedey) . chr(10));
                fwrite($fr, \funcionesGenerales::tamano80SentarPago('NUC=' . $_SESSION["tramite"]["rues_numerounico"]) . chr(10));
                fwrite($fr, \funcionesGenerales::tamano80SentarPago('') . chr(10));
                $iSer = 0;
                foreach ($_SESSION["tramite"]["liquidacion"] as $k) {
                    if (ltrim((string) $arrServs[$k["idservicio"]]["conceptodepartamental"], "0") != '') {
                        $iSer++;
                        if ($iSer == 1) {
                            $expbas = $k["expediente"];
                            $nombas = $k["nombre"];
                        }
                        fwrite($fr, \funcionesGenerales::tamano80SentarPago('<SECUENCIA DE SERVICIO=' . sprintf("%03s", $iSer) . '>') . chr(10));
                        fwrite($fr, \funcionesGenerales::tamano80SentarPago('MATRICULA=' . sprintf("%08s", $k["expediente"])) . chr(10));
                        fwrite($fr, \funcionesGenerales::tamano80SentarPago('RAZON SOCIAL=' . $k["nombre"]) . chr(10));
                        fwrite($fr, \funcionesGenerales::tamano80SentarPago('CODIGO SERVICIO=' . $k["idservicio"]) . chr(10));
                        fwrite($fr, \funcionesGenerales::tamano80SentarPago('DESCRIPCION SERVICIO=' . retornarRegistroMysqliApi($mysqli, 'mreg_servicios', "idservicio='" . $k["idservicio"] . "'", "nombre")) . chr(10));
                        fwrite($fr, \funcionesGenerales::tamano80SentarPago('VALOR SERVICIO=' . sprintf("%010s", $k["valorservicio"]) . '.00') . chr(10));
                        if ($k["valorbase"] != 0) {
                            fwrite($fr, \funcionesGenerales::tamano80SentarPago('VALOR BASE=' . sprintf("%010s", $k["valorbase"]) . '.00') . chr(10));
                        }
                        fwrite($fr, \funcionesGenerales::tamano80SentarPago('ARCHIVADOR=' . \funcionesRegistrales::evaluarArchivadorAsentarRecibo($k["idservicio"])) . chr(10));
                        fwrite($fr, \funcionesGenerales::tamano80SentarPago('') . chr(10));
                    }
                }
                fwrite($fr, \funcionesGenerales::tamano80SentarPago('<FIN DATOS RECIBO>') . chr(10));
                fclose($fr);
            }

            // ****************************************************************************** //
            // Genera archivo de información del recibo con servicios camara
            // ****************************************************************************** //
            $nfr = PATH_ABSOLUTO_SITIO . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/workflow/imgrecibos/' . $_SESSION["tramite"]["numerorecibo"] . '.inf';
            $fr = fopen($nfr, 'w');
            fwrite($fr, 'RECIBO=' . $_SESSION["tramite"]["numerorecibo"] . "\r\n");
            fwrite($fr, 'MATRICULA=' . $_SESSION["tramite"]["idmatriculabase"] . "\r\n");
            fwrite($fr, 'NIT=' . $_SESSION["tramite"]["identificacionbase"] . "\r\n");
            fwrite($fr, 'RAZON SOCIAL=' . trim($_SESSION["tramite"]["nombrebase"]) . "\r\n");

            fwrite($fr, 'NOMBRE RECIBO=' . trim($_SESSION["tramite"]["nombrecliente"] . $_SESSION["tramite"]["apellidocliente"]) . "\r\n");
            fwrite($fr, 'OPERACION=' . $_SESSION["tramite"]["numerooperacion"] . "\r\n");
            fwrite($fr, 'VALOR RECIBO=' . $totalcamara . "\r\n");
            fwrite($fr, 'FECHA=' . $_SESSION["tramite"]["fecharecibo"] . "\r\n");
            fwrite($fr, 'NUC=' . $_SESSION["tramite"]["rues_numerointerno"] . "\r\n");
            fwrite($fr, 'CODIGO DE BARRAS=' . $_SESSION["generales"]["codigobarras1"] . "\r\n");
            fclose($fr);

            // ****************************************************************************** //
            // Genera archivo de información del recibo con servicios gobernacion
            // ****************************************************************************** //
            if ($totalgobernacion != 0) {
                $nfr = PATH_ABSOLUTO_SITIO . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/workflow/imgrecibos/' . $_SESSION["tramite"]["numerorecibogob"] . '.inf';
                $fr = fopen($nfr, 'w');
                fwrite($fr, 'RECIBO=' . $_SESSION["tramite"]["numerorecibogob"] . "\r\n");
                fwrite($fr, 'MATRICULA=' . $_SESSION["tramite"]["idmatriculabase"] . "\r\n");
                fwrite($fr, 'NIT=' . $_SESSION["tramite"]["identificacionbase"] . "\r\n");
                fwrite($fr, 'RAZON SOCIAL=' . trim($_SESSION["tramite"]["nombrebase"]) . "\r\n");

                fwrite($fr, 'NOMBRE RECIBO=' . trim($_SESSION["tramite"]["nombrecliente"] . $_SESSION["tramite"]["apellidocliente"]) . "\r\n");
                fwrite($fr, 'OPERACION=' . $_SESSION["tramite"]["numerooperaciongob"] . "\r\n");
                fwrite($fr, 'VALOR RECIBO=' . $totalgobernacion . "\r\n");
                fwrite($fr, 'FECHA=' . $_SESSION["tramite"]["fecharecibogob"] . "\r\n");
                fwrite($fr, 'NUC=' . $_SESSION["tramite"]["rues_numerointerno"] . "\r\n");
                fwrite($fr, 'CODIGO DE BARRAS=' . $_SESSION["generales"]["codigobarras1"] . "\r\n");
                fclose($fr);
            }

            // ****************************************************************************** //
            // Genera archivo de información para impresion (.rec) - servicios camara
            // ****************************************************************************** //
            $nfr = PATH_ABSOLUTO_SITIO . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/workflow/imgrecibos/' . $_SESSION["tramite"]["numerorecibo"] . '.rec';
            $fr = fopen($nfr, 'w');
            fwrite($fr, RAZONSOCIAL . chr(13) . chr(10));
            fwrite($fr, 'NIT. ' . NIT . chr(13) . chr(10));
            fwrite($fr, DIRECCION1 . chr(13) . chr(10));
            fwrite($fr, 'TELEFONO. ' . TELEFONO_ATENCION_USUARIOS . chr(13) . chr(10));
            fwrite($fr, 'FACTURA DE VENTA - RECIBO DE CAJA' . chr(13) . chr(10));
            fwrite($fr, chr(13) . chr(10));

            $numunicorue = '';
            $rrue = retornarRegistroMysqliApi($mysqli, 'mreg_rue_radicacion', "recibolocal='" . $_SESSION["tramite"]["numerorecibo"] . "'");
            if ($rrue && !empty($rrue)) {
                $numunicorue = trim($rrue["numerounicoconsulta"]);
            }
            $fpago = '';
            if ($_SESSION["tramite"]["pagoefectivo"] != 0) {
                $fpago = 'Efectivo';
            }
            if ($_SESSION["tramite"]["pagocheque"] != 0) {
                $fpago = 'En cheque';
            }
            if ($_SESSION["tramite"]["pagoconsignacion"] != 0) {
                $fpago = 'En consignación';
            }
            if ($_SESSION["tramite"]["pagoqr"] != 0) {
                $fpago = 'En QR';
            }
            if ($_SESSION["tramite"]["pagotdebito"] != 0) {
                $fpago = 'Tarj. Débito';
            }
            if ($_SESSION["tramite"]["pagoach"] != 0) {
                $fpago = 'Sistema ACH';
            }
            if ($_SESSION["tramite"]["pagovisa"] != 0) {
                $fpago = 'Tarj. Crédito';
            }
            if ($_SESSION["tramite"]["pagomastercard"] != 0) {
                $fpago = 'Tarj. Crédito';
            }
            if ($_SESSION["tramite"]["pagocredencial"] != 0) {
                $fpago = 'Tarj. Crédito';
            }
            if ($_SESSION["tramite"]["pagodiners"] != 0) {
                $fpago = 'Tarj. Crédito';
            }
            if ($_SESSION["tramite"]["pagoamerican"] != 0) {
                $fpago = 'Tarj. Crédito';
            }
            if ($_SESSION["tramite"]["pagoprepago"] != 0) {
                $fpago = 'Cargo a prepago';
            }

            fwrite($fr, 'FECHA: ' . \funcionesGenerales::mostrarFecha($_SESSION["tramite"]["fecharecibo"]) . chr(13) . chr(10));
            fwrite($fr, 'OPERAC.: ' . $_SESSION["tramite"]["numerooperacion"] . chr(13) . chr(10));
            fwrite($fr, 'NUM.REC: ' . sprintf("%-6s", $_SESSION["tramite"]["numerorecuperacion"]) . '    RECIBO NO. ' . $_SESSION["tramite"]["numerorecibo"] . chr(13) . chr(10));
            //
            fwrite($fr, 'NUM.RAD: ' . sprintf("%-6s", $_SESSION["tramite"]["numeroradicacion"]) . chr(13) . chr(10));
            //
            fwrite($fr, 'HORA: ' . \funcionesGenerales::mostrarHora($_SESSION["tramite"]["horarecibo"]) . '      PAGINA 1 DE 1' . chr(13) . chr(10));
            if ($numunicorue != '') {
                fwrite($fr, ('NUM. UNICO RUES: ' . $numunicorue) . chr(13) . chr(10));
            }
            fwrite($fr, ('USUARIO: ' . sprintf("%-8s", $_SESSION["tramite"]["idusuario"])) . chr(13) . chr(10));
            fwrite($fr, ('-------------------------------------') . chr(13) . chr(10));
            fwrite($fr, ('MAT/INSC: (' . $expbas . ')') . chr(13) . chr(10));
            fwrite($fr, ('-------------------------------------') . chr(13) . chr(10));
            fwrite($fr, (sprintf("%-33s", substr(\funcionesGenerales::utf8_decode($_SESSION["tramite"]["nombrecliente"] . ' ' . $_SESSION["tramite"]["apellidocliente"]), 0, 33))) . chr(13) . chr(10));
            fwrite($fr, ('NIT/CC: ' . $_SESSION["tramite"]["identificacioncliente"] . '  RUE: ') . chr(13) . chr(10));
            fwrite($fr, ('FORMA DE PAGO: ' . $fpago) . chr(13) . chr(10));
            fwrite($fr, ('DESCRIPCION       DET.          VALOR') . chr(13) . chr(10));
            fwrite($fr, ('----------------- ---- --------------') . chr(13) . chr(10));
            foreach ($_SESSION["tramite"]["liquidacion"] as $dt) {
                if (isset($dt["idservicio"]) && $dt["idservicio"] != '') {
                    if (ltrim((string) $arrServs[$dt["idservicio"]]["conceptodepartamental"], "0") == '') {
                        if (trim($arrServs[$dt["idservicio"]]["descripcioncorta"]) != '') {
                            $servtxt = sprintf("%-17s", substr($arrServs[$dt["idservicio"]]["descripcioncorta"], 0, 17));
                        } else {
                            $servtxt = sprintf("%-17s", substr($arrServs[$dt["idservicio"]]["nombre"], 0, 17));
                        }
                        if (trim($dt["ano"]) != '') {
                            $canttxt = $dt["ano"];
                        } else {
                            $canttxt = sprintf("%4s", $dt["cantidad"]);
                        }
                        $valtxt = sprintf("%14s", number_format($dt["valorservicio"], 0));
                        fwrite($fr, ($servtxt . ' ' . $canttxt . ' ' . $valtxt) . chr(13) . chr(10));
                        if (doubleval($dt["valorbase"]) != 0) {
                            fwrite($fr, ('Valor base:' . number_format($dt["valorbase"])) . chr(13) . chr(10));
                        }
                    }
                }
            }

            if (($_SESSION["tramite"]["cargogastoadministrativo"] == 'SI') || ($_SESSION["tramite"]["cargoentidadoficial"] == 'SI')) {
                $servtxt = sprintf("%-17s", '*** TOTAL PAGADO');
                $canttxt = sprintf("%4s", ' ');
                $valtxt = sprintf("%14s", '0');
                fwrite($fr, ($servtxt . ' ' . $canttxt . ' ' . $valtxt) . chr(13) . chr(10));
                if ($_SESSION["tramite"]["cargogastoadministrativo"] == 'SI') {
                    fwrite($fr, ('*** SIN COSTO PARA EL CLIENTE ***') . chr(13) . chr(10));
                }
                if ($_SESSION["tramite"]["cargoentidadoficial"] == 'SI') {
                    fwrite($fr, ('*** SIN COSTO PARA LA ENTIDAD ***') . chr(13) . chr(10));
                }
            } else {
                $servtxt = sprintf("%-17s", '*** TOTAL PAGADO');
                $canttxt = sprintf("%4s", ' ');
                if ($_SESSION["tramite"]["cargoafiliacion"] == 'SI') {
                    $valtxt = sprintf("%14s", '0');
                } else {
                    $valtxt = sprintf("%14s", number_format($totalcamara, 0));
                }
                fwrite($fr, ($servtxt . ' ' . $canttxt . ' ' . $valtxt) . chr(13) . chr(10));
                if ($_SESSION["tramite"]["cargoafiliacion"] == 'SI') {
                    fwrite($fr, ('*** CON CARGO A CUPO AFILIADOS ***') . chr(13) . chr(10));
                }
                if ($_SESSION["tramite"]["pagoprepago"] != 0) {
                    fwrite($fr, ('*** CON CARGO AL CUPO DE PREPAGO ***') . chr(13) . chr(10));
                }
            }

            if (trim($claveprepago) != '') {
                fwrite($fr, ('----------------- ---- --------------') . chr(13) . chr(10));
                fwrite($fr, ('Clave prepago: ' . $claveprepago) . chr(13) . chr(10));
            }
            if (doubleval($saldoprepago) != 0) {
                fwrite($fr, ('----------------- ---- --------------') . chr(13) . chr(10));
                fwrite($fr, ('Saldoprepago: ' . $claveprepago) . chr(13) . chr(10));
            }

            //
            if (trim($_SESSION["tramite"]["numeroradicacion"]) != '') {


                if ($_SESSION["generales"]["codigoempresa"] == 20) {
                    fwrite($fr, ('-------------------------------------') . chr(13) . chr(10));
                    fwrite($fr, ('Para informacion sobre este(os) docu-') . chr(13) . chr(10));
                    fwrite($fr, ('mento(s)  comuniquese  al  8962121  o') . chr(13) . chr(10));
                    fwrite($fr, ('consulte  en  www.ccmpc.org.co,  link') . chr(13) . chr(10));
                    fwrite($fr, ('servicios, servicios en linea, consul') . chr(13) . chr(10));
                    fwrite($fr, ('ta estado de tramites, alli digite el') . chr(13) . chr(10));
                    fwrite($fr, ('siguiente numero:') . chr(13) . chr(10));
                    fwrite($fr, ($_SESSION["tramite"]["numeroradicacion"]) . chr(13) . chr(10));
                } else {
                    fwrite($fr, ('-------------------------------------') . chr(13) . chr(10));
                    fwrite($fr, ('Codigo de barras: ' . $_SESSION["tramite"]["numeroradicacion"]) . chr(13) . chr(10));
                    fwrite($fr, ('-------------------------------------') . chr(13) . chr(10));
                    fwrite($fr, ('Para conocer el estado de su  tramite') . chr(13) . chr(10));
                    fwrite($fr, ('ir a: ' . TIPO_HTTP . HTTP_HOST) . chr(13) . chr(10));
                }
            }

            fclose($fr);

            // ****************************************************************************** //
            // Genera archivo de información para impresion (.rec) - servicios gobernacion
            // ****************************************************************************** //
            if ($totalgobernacion != 0) {
                $nfr = PATH_ABSOLUTO_SITIO . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/workflow/imgrecibos/' . $_SESSION["tramite"]["numerorecibogob"] . '.rec';
                $fr = fopen($nfr, 'w');
                fwrite($fr, RAZONSOCIAL . chr(13) . chr(10));
                fwrite($fr, 'NIT. ' . NIT . chr(13) . chr(10));
                fwrite($fr, DIRECCION1 . chr(13) . chr(10));
                fwrite($fr, 'TELEFONO. ' . TELEFONO_ATENCION_USUARIOS . chr(13) . chr(10));
                fwrite($fr, 'FACTURA DE VENTA - RECIBO DE CAJA' . chr(13) . chr(10));
                fwrite($fr, chr(13) . chr(10));

                $numunicorue = '';
                $rrue = retornarRegistroMysqliApi($mysqli, 'mreg_rue_radicacion', "recibolocal='" . $_SESSION["tramite"]["numerorecibogob"] . "'");
                if ($rrue && !empty($rrue)) {
                    $numunicorue = trim($rrue["numerounicoconsulta"]);
                }
                $fpago = '';
                if ($_SESSION["tramite"]["pagoefectivo"] != 0) {
                    $fpago = 'Efectivo';
                }
                if ($_SESSION["tramite"]["pagocheque"] != 0) {
                    $fpago = 'En cheque';
                }
                if ($_SESSION["tramite"]["pagoconsignacion"] != 0) {
                    $fpago = 'En consignación';
                }
                if ($_SESSION["tramite"]["pagoqr"] != 0) {
                    $fpago = 'En QR';
                }
                if ($_SESSION["tramite"]["pagotdebito"] != 0) {
                    $fpago = 'Tarj. Débito';
                }
                if ($_SESSION["tramite"]["pagoach"] != 0) {
                    $fpago = 'Sistema ACH';
                }
                if ($_SESSION["tramite"]["pagovisa"] != 0) {
                    $fpago = 'Tarj. Crédito';
                }
                if ($_SESSION["tramite"]["pagomastercard"] != 0) {
                    $fpago = 'Tarj. Crédito';
                }
                if ($_SESSION["tramite"]["pagocredencial"] != 0) {
                    $fpago = 'Tarj. Crédito';
                }
                if ($_SESSION["tramite"]["pagodiners"] != 0) {
                    $fpago = 'Tarj. Crédito';
                }
                if ($_SESSION["tramite"]["pagoamerican"] != 0) {
                    $fpago = 'Tarj. Crédito';
                }
                if ($_SESSION["tramite"]["pagoprepago"] != 0) {
                    $fpago = 'Cargo a prepago';
                }

                fwrite($fr, 'FECHA: ' . \funcionesGenerales::mostrarFecha($_SESSION["tramite"]["fecharecibogob"]) . '  OPERAC.: ' . $_SESSION["tramite"]["numerooperaciongob"] . chr(13) . chr(10));
                fwrite($fr, 'NUM.REC: ' . sprintf("%-6s", $_SESSION["tramite"]["numerorecuperacion"]) . '    RECIBO NO. ' . $_SESSION["tramite"]["numerorecibogob"] . chr(13) . chr(10));
                //
                fwrite($fr, 'NUM.RAD: ' . sprintf("%-6s", $_SESSION["tramite"]["numeroradicacion"]) . chr(13) . chr(10));
                //
                fwrite($fr, 'HORA: ' . \funcionesGenerales::mostrarHora($_SESSION["tramite"]["horarecibogob"]) . '      PAGINA 1 DE 1' . chr(13) . chr(10));
                if ($numunicorue != '') {
                    fwrite($fr, ('NUM. UNICO RUES: ' . $numunicorue) . chr(13) . chr(10));
                }
                fwrite($fr, ('USUARIO: ' . sprintf("%-8s", $_SESSION["tramite"]["idusuario"])) . chr(13) . chr(10));
                fwrite($fr, ('-------------------------------------') . chr(13) . chr(10));
                fwrite($fr, ('MAT/INSC: (' . $expbas . ')') . chr(13) . chr(10));
                fwrite($fr, ('-------------------------------------') . chr(13) . chr(10));
                fwrite($fr, (sprintf("%-33s", substr(\funcionesGenerales::utf8_decode($_SESSION["tramite"]["nombrecliente"] . ' ' . $_SESSION["tramite"]["apellidocliente"]), 0, 33))) . chr(13) . chr(10));
                fwrite($fr, ('NIT/CC: ' . $_SESSION["tramite"]["identificacioncliente"] . '  RUE: ') . chr(13) . chr(10));
                fwrite($fr, ('FORMA DE PAGO: ' . $fpago) . chr(13) . chr(10));
                fwrite($fr, ('DESCRIPCION       DET.          VALOR') . chr(13) . chr(10));
                fwrite($fr, ('----------------- ---- --------------') . chr(13) . chr(10));
                foreach ($_SESSION["tramite"]["liquidacion"] as $dt) {
                    if (isset($dt["idservicio"]) && $dt["idservicio"] != '') {
                        if (ltrim((string) $arrServs[$dt["idservicio"]]["conceptodepartamental"], "0") != '') {
                            if (trim($arrServs[$dt["idservicio"]]["descripcioncorta"]) != '') {
                                $servtxt = sprintf("%-17s", substr($arrServs[$dt["idservicio"]]["descripcioncorta"], 0, 17));
                            } else {
                                $servtxt = sprintf("%-17s", substr($arrServs[$dt["idservicio"]]["nombre"], 0, 17));
                            }
                            if (trim($dt["ano"]) != '') {
                                $canttxt = $dt["ano"];
                            } else {
                                $canttxt = sprintf("%4s", $dt["cantidad"]);
                            }
                            $valtxt = sprintf("%14s", number_format($dt["valorservicio"], 0));
                            fwrite($fr, ($servtxt . ' ' . $canttxt . ' ' . $valtxt) . chr(13) . chr(10));
                            if (doubleval($dt["valorbase"]) != 0) {
                                fwrite($fr, ('Valor base:' . number_format($dt["valorbase"])) . chr(13) . chr(10));
                            }
                        }
                    }
                }

                if (($_SESSION["tramite"]["cargogastoadministrativo"] == 'SI') || ($_SESSION["tramite"]["cargoentidadoficial"] == 'SI')) {
                    $servtxt = sprintf("%-17s", '*** TOTAL PAGADO');
                    $canttxt = sprintf("%4s", ' ');
                    $valtxt = sprintf("%14s", '0');
                    fwrite($fr, ($servtxt . ' ' . $canttxt . ' ' . $valtxt) . chr(13) . chr(10));
                    if ($_SESSION["tramite"]["cargogastoadministrativo"] == 'SI') {
                        fwrite($fr, ('*** SIN COSTO PARA EL CLIENTE ***') . chr(13) . chr(10));
                    }
                    if ($_SESSION["tramite"]["cargoentidadoficial"] == 'SI') {
                        fwrite($fr, ('*** SIN COSTO PARA LA ENTIDAD ***') . chr(13) . chr(10));
                    }
                } else {
                    $servtxt = sprintf("%-17s", '*** TOTAL PAGADO');
                    $canttxt = sprintf("%4s", ' ');
                    if ($_SESSION["tramite"]["cargoafiliacion"] == 'SI') {
                        $valtxt = sprintf("%14s", '0');
                    } else {
                        $valtxt = sprintf("%14s", number_format($totalgobernacion, 0));
                    }
                    fwrite($fr, ($servtxt . ' ' . $canttxt . ' ' . $valtxt) . chr(13) . chr(10));
                    if ($_SESSION["tramite"]["cargoafiliacion"] == 'SI') {
                        fwrite($fr, ('*** CON CARGO A CUPO AFILIADOS ***') . chr(13) . chr(10));
                    }
                    if ($_SESSION["tramite"]["pagoprepago"] != 0) {
                        fwrite($fr, ('*** CON CARGO AL CUPO DE PREPAGO ***') . chr(13) . chr(10));
                    }
                }

                //
                if (trim($_SESSION["tramite"]["numeroradicacion"]) != '') {

                    if ($_SESSION["generales"]["codigoempresa"] == 20) {
                        fwrite($fr, ('-------------------------------------') . chr(13) . chr(10));
                        fwrite($fr, ('Para informacion sobre este(os) docu-') . chr(13) . chr(10));
                        fwrite($fr, ('mento(s)  comuniquese  al  8962121  o') . chr(13) . chr(10));
                        fwrite($fr, ('consulte  en  www.ccmpc.org.co,  link') . chr(13) . chr(10));
                        fwrite($fr, ('servicios, servicios en linea, consul') . chr(13) . chr(10));
                        fwrite($fr, ('ta estado de tramites, alli digite el') . chr(13) . chr(10));
                        fwrite($fr, ('siguiente numero:') . chr(13) . chr(10));
                        fwrite($fr, ($_SESSION["tramite"]["numeroradicacion"]) . chr(13) . chr(10));
                    } else {
                        fwrite($fr, ('-------------------------------------') . chr(13) . chr(10));
                        fwrite($fr, ('Codigo de barras: ' . $_SESSION["tramite"]["numeroradicacion"]) . chr(13) . chr(10));
                        fwrite($fr, ('-------------------------------------') . chr(13) . chr(10));
                        fwrite($fr, ('Para conocer el estado de su  tramite') . chr(13) . chr(10));
                        fwrite($fr, ('ir a: ' . TIPO_HTTP . HTTP_HOST) . chr(13) . chr(10));
                    }
                }

                fclose($fr);
            }

            // *************************************************************************************************
            // Genera archivos de inscripciones si las hay
            // *************************************************************************************************
            if (isset($xInscripciones) && is_array($xInscripciones) && !empty($xInscripciones)) {
                foreach ($xInscripciones as $xi) {
                    // \logApi::general2($nameLog, $idSolicitudPago, \funcionesGenerales::utf8_encode('Entro a generar archivo el sello para el workflow - Inscripcion No. ' . $xi["libro"] . sprintf("%08s", $xi["numreg"])));
                    if (CODIGO_EMPRESA == 40) {
                        $nins = PATH_ABSOLUTO_SITIO . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/workflow/sellos/S' . substr($xi["libro"], 2, 2) . '-' . sprintf("%08s", $xi["numreg"]) . '.txt';
                    } else {
                        $nins = PATH_ABSOLUTO_SITIO . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/workflow/sellos/S' . substr($xi["libro"], 2, 2) . sprintf("%08s", $xi["numreg"]) . '.txt';
                    }
                    $fr = fopen($nins, 'w');
                    fwrite($fr, '<DATOS-LIBRO=' . substr($xi["libro"], 2, 2) . ',' . sprintf("%08s", $xi["numreg"]) . '>' . chr(13) . chr(10));
                    fwrite($fr, 'FECHA-REGISTRO=' . $xi["fecha"] . chr(13) . chr(10));
                    fwrite($fr, 'HORA-REGISTRO=' . $xi["hora"] . chr(13) . chr(10));
                    fwrite($fr, 'MATRICULA=' . sprintf("%08s", $xi["matricula"]) . chr(13) . chr(10));
                    fwrite($fr, 'IDENTIFICACION=' . sprintf("%011s", $xi["identificacion"]) . chr(13) . chr(10));
                    fwrite($fr, 'NOMBRE=' . $xi["nombre"] . chr(13) . chr(10));
                    fwrite($fr, 'CODIGO-ACTO=' . $xi["acto"] . chr(13) . chr(10));
                    fwrite($fr, 'DESCRIPCION-ACTO=' . retornarRegistroMysqliApi($mysqli, "mreg_actos", "idlibro='" . $xi["libro"] . "' and idacto='" . $xi["acto"] . "'", "nombre") . chr(13) . chr(10));
                    fwrite($fr, 'FECHA-DOCUMENTO=' . $xi["fechadoc"] . chr(13) . chr(10));
                    fwrite($fr, 'NUMERO-DOCUMENTO=' . $xi["numdoc"] . chr(13) . chr(10));
                    fwrite($fr, 'NOTICIA1=' . substr($xi["noticia"], 0, 65) . chr(13) . chr(10));
                    if (trim(substr($xi["noticia"], 65, 65)) != '') {
                        fwrite($fr, 'NOTICIA2=' . substr($xi["noticia"], 65, 65) . chr(13) . chr(10));
                    }
                    if (trim(substr($xi["noticia"], 130, 65)) != '') {
                        fwrite($fr, 'NOTICIA3=' . substr($xi["noticia"], 130, 65) . chr(13) . chr(10));
                    }
                    if (trim(substr($xi["noticia"], 195, 65)) != '') {
                        fwrite($fr, 'NOTICIA4=' . substr($xi["noticia"], 195, 65) . chr(13) . chr(10));
                    }
                    if (trim(substr($xi["noticia"], 260, 65)) != '') {
                        fwrite($fr, 'NOTICIA5=' . substr($xi["noticia"], 260, 65) . chr(13) . chr(10));
                    }
                    if (trim(substr($xi["noticia"], 325, 65)) != '') {
                        fwrite($fr, 'NOTICIA6=' . substr($xi["noticia"], 325, 65) . chr(13) . chr(10));
                    }
                    if (trim(substr($xi["noticia"], 390, 65)) != '') {
                        fwrite($fr, 'NOTICIA7=' . substr($xi["noticia"], 390, 65) . chr(13) . chr(10));
                    }
                    if (trim(substr($xi["noticia"], 455, 65)) != '') {
                        fwrite($fr, 'NOTICIA8=' . substr($xi["noticia"], 455, 65) . chr(13) . chr(10));
                    }
                    if (trim(substr($xi["noticia"], 520, 65)) != '') {
                        fwrite($fr, 'NOTICIA9=' . substr($xi["noticia"], 520, 65) . chr(13) . chr(10));
                    }
                    if (trim(substr($xi["noticia"], 585, 65)) != '') {
                        fwrite($fr, 'NOTICIA10=' . substr($xi["noticia"], 585, 65) . chr(13) . chr(10));
                    }

                    fwrite($fr, 'OPERACION=' . $_SESSION["tramite"]["numerooperacion"] . chr(13) . chr(10));
                    fwrite($fr, 'RECIBO=' . $_SESSION["tramite"]["numerorecibo"] . chr(13) . chr(10));
                    fwrite($fr, 'USUARIO=' . $xi["ope"] . chr(13) . chr(10));
                    fwrite($fr, '<FIN-DATOS-SELLOS>' . chr(13) . chr(10));
                    fclose($fr);
                }
            }
        }
        \logApi::general2($nameLog, $idSolicitudPago, \funcionesGenerales::utf8_encode('Creo archivo en SII para Workflow'));

        // ******************************************************************************************************** //
        // En caso de certificados especiales actualiza la tabla mreg_certificados_especiales
        // ******************************************************************************************************** //
        if ($_SESSION["tramite"]["tipotramite"] == 'certificadosespeciales') {
            foreach ($_SESSION["tramite"]["liquidacion"] as $l) {
                if ($l["idservicio"] <= '01090151' || $l["idservicio"] >= '01090160') {
                    $arrCampos = array(
                        'idliquidacion',
                        'idusuario',
                        'recibo',
                        'fechasolicitud',
                        'horasolicitud',
                        'matricula',
                        'proponente',
                        'identificacion',
                        'nombre',
                        'tipocertificado',
                        'explicacion',
                        'cantidad',
                        'valor',
                        'idestado'
                    );

                    $arrValores = array(
                        $idSolicitudPago,
                        "'" . $_SESSION["tramite"]["idusuario"] . "'",
                        "'" . $_SESSION["tramite"]["numerorecibo"] . "'",
                        "'" . date("Ymd") . "'",
                        "'" . date("His") . "'",
                        "'" . ltrim($_SESSION["tramite"]["idmatriculabase"], "0") . "'",
                        "'" . ltrim($_SESSION["tramite"]["idproponentebase"], "0") . "'",
                        "'" . ltrim($_SESSION["tramite"]["identificacionbase"], "0") . "'",
                        "'" . addslashes($_SESSION["tramite"]["nombrebase"]) . "'",
                        "'" . $_SESSION["tramite"]["tipocertificado"] . "'",
                        "'" . addslashes($_SESSION["tramite"]["explicacion"]) . "'",
                        $_SESSION["tramite"]["liquidacion"][1]["cantidad"],
                        $_SESSION["tramite"]["liquidacion"][1]["valorservicio"],
                        "'1'"
                    );
                    $resx = insertarRegistrosMysqliApi($mysqli, 'mreg_certificados_especiales', $arrCampos, $arrValores);
                    if ($resx === false) {
                        \logApi::general2($nameLog, $idSolicitudPago, \funcionesGenerales::utf8_encode('Error almacenando certificados especiales'));
                    } else {
                        \logApi::general2($nameLog, $idSolicitudPago, \funcionesGenerales::utf8_encode('Almaceno correctamente certificados especiales'));
                    }
                }
            }
        }
        \logApi::general2($nameLog, $idSolicitudPago, \funcionesGenerales::utf8_encode('En caso de certificados especiales actualiza la tabla mreg_certificados_especiales'));

        // ******************************************************************************************************** //
        // 2015-08-03 : Si el recibo de caja generado corresponde a certificados 
        // ******************************************************************************************************** //
        $certificadosencolados = 'no';
        $iCertificados = 0;
        $arrCertificados = array();
        $gencert = 'no';
        if (
                $_SESSION["tramite"]["tipotramite"] == 'certificadosvirtuales' ||
                $_SESSION["tramite"]["tipotramite"] == 'renovacionmatricula' ||
                $_SESSION["tramite"]["tipotramite"] == 'renovacionesadl' ||
                $grupoServicios == 'CerVirt' ||
                ($_SESSION["tramite"]["tipotramite"] == 'inscripciondocumentos')
        ) {
            if ($_SESSION["tramite"]["subtipotramite"] != 'matriculapjurcae' &&
                    $_SESSION["tramite"]["subtipotramite"] != 'matriculapjurvue' &&
                    $_SESSION["tramite"]["subtipotramite"] != 'matriculapnatcae' &&
                    $_SESSION["tramite"]["subtipotramite"] != 'matriculapnatvue') {
                if ($_SESSION["tramite"]["tipotramite"] == 'inscripciondocumentos') {
                    $gencert = 'no';
                } else {
                    if ($_SESSION["tramite"]["tipotramite"] == 'renovacionmatricula' || $_SESSION["tramite"]["tipotramite"] == 'renovacionesadl') {
                        if (
                                $_SESSION["tramite"]["multadoponal"] != 'S' &&
                                $_SESSION["tramite"]["multadoponal"] != 'L' &&
                                $_SESSION["tramite"]["controlactividadaltoimpacto"] != 'S' && ($_SESSION["tramite"]["cumplorequisitosbenley1780"] != 'S' ||
                                $_SESSION["tramite"]["mantengorequisitosbenley1780"] != 'S' ||
                                $_SESSION["tramite"]["renunciobeneficiosley1780"] != 'N')
                        ) {
                            $gencert = 'si';
                        }
                        if ($gencert == 'si') {
                            if ($formulariosactualizados == 0) {
                                $gencert = 'no';
                            }
                        }
                    } else {
                        $gencert = 'si';
                    }
                }
            }

            if ($gencert == 'no') {
                foreach ($_SESSION["tramite"]["liquidacion"] as $a) {
                    $tipo = '';
                    $serv = retornarRegistroMysqliApi($mysqli, "mreg_servicios", "idservicio='" . $a["idservicio"] . "'");

                    // $serv = retornarRegistro('mreg_servicios', "idservicio='" . $a["idservicio"] . "'");
                    if ($serv && !empty($serv)) {
                        if (trim($serv["tipocertificado"]) != '') {
                            $tipo = $serv["tipocertificado"];
                        }
                    }
                    if ($tipo != '') {
                        // \logApi::general2($nameLog, $idSolicitudPago, \funcionesGenerales::utf8_encode('Almaceno certificado para expedir posteriormente'));
                        $arrCampos = array(
                            'recibo',
                            'idliquidacion',
                            'fecha',
                            'hora',
                            'matricula',
                            'proponente',
                            'servicio',
                            'cantidad',
                            'valor',
                            'estado',
                            'fechageneracion',
                            'horageneracion'
                        );
                        $arrValores = array(
                            "'" . $_SESSION["tramite"]["numerorecibo"] . "'",
                            $_SESSION["tramite"]["idliquidacion"],
                            "'" . date("Ymd") . "'",
                            "'" . date("His") . "'",
                            "'" . $a["expediente"] . "'",
                            "''",
                            "'" . $a["idservicio"] . "'",
                            $a["cantidad"],
                            $a["valorservicio"],
                            "'PE'",
                            "''",
                            "''"
                        );
                        insertarRegistrosMysqliApi($mysqli, 'mreg_certificados_pendientes', $arrCampos, $arrValores);
                    }
                }
            }


            if ($gencert == 'si') {
                $certificadosencolados = 'no';
                foreach ($_SESSION["tramite"]["liquidacion"] as $a) {
                    $tipo = '';
                    $serv = retornarRegistroMysqliApi($mysqli, 'mreg_servicios', "idservicio='" . $a["idservicio"] . "'");
                    if ($serv && !empty($serv)) {
                        if (trim($serv["tipocertificado"]) != '') {
                            $tipo = $serv["tipocertificado"];
                        }
                    }
                    if ($tipo == '') {
                        \logApi::general2($nameLog, $idSolicitudPago, \funcionesGenerales::utf8_encode('No localizo el tipo de certificado a generar asociado con el servicio ' . $a["idservicio"]));
                    }
                    $generarcert = 'si';
                    $controlarcert = 'no';
                    if ($tipo == 'CerExi' && defined('FECHA_INICIO_NUEVO_CERTIFICADO_CEREXI') && FECHA_INICIO_NUEVO_CERTIFICADO_CEREXI != '' && date("Ymd") >= FECHA_INICIO_NUEVO_CERTIFICADO_CEREXI) {
                        $controlarcert = 'si';
                    }
                    if ($tipo == 'CerMat' && defined('FECHA_INICIO_NUEVO_CERTIFICADO_CERMAT') && FECHA_INICIO_NUEVO_CERTIFICADO_CERMAT != '' && date("Ymd") >= FECHA_INICIO_NUEVO_CERTIFICADO_CERMAT) {
                        $controlarcert = 'si';
                    }
                    if ($tipo == 'CerEsadl' && defined('FECHA_INICIO_NUEVO_CERTIFICADO_CERESADL') && FECHA_INICIO_NUEVO_CERTIFICADO_CERESADL != '' && date("Ymd") >= FECHA_INICIO_NUEVO_CERTIFICADO_CERESADL) {
                        $controlarcert = 'si';
                    }
                    if ($tipo == 'CerLibRegMer' && defined('FECHA_INICIO_NUEVO_CERTIFICADO_LIBROS') && FECHA_INICIO_NUEVO_CERTIFICADO_LIBROS != '' && date("Ymd") >= FECHA_INICIO_NUEVO_CERTIFICADO_LIBROS) {
                        $controlarcert = 'si';
                    }
                    if ($tipo == 'CerLibEsadl' && defined('FECHA_INICIO_NUEVO_CERTIFICADO_LIBROS') && FECHA_INICIO_NUEVO_CERTIFICADO_LIBROS != '' && date("Ymd") >= FECHA_INICIO_NUEVO_CERTIFICADO_LIBROS) {
                        $controlarcert = 'si';
                    }

                    //
                    if ($generarcert == 'si') {
                        if ($tipo != '') {
                            $tipogastoadm = 'NOR';
                            $tipocertificadoadm = 'Normal';
                            if ($_SESSION["tramite"]["cargogastoadministrativo"] == 'SI') {
                                $tipogastoadm = 'ADM';
                                $tipocertificadoadm = 'GasAdm';
                            }
                            if ($_SESSION["tramite"]["cargoafiliacion"] == 'SI') {
                                $tipogastoadm = 'AFI';
                                $tipocertificadoadm = 'GasAfi';
                            }
                            if ($_SESSION["tramite"]["cargoentidadoficial"] == 'SI') {
                                $tipogastoadm = 'OFI';
                                $tipocertificadoadm = 'GasOfi';
                            }
                            if ($_SESSION["tramite"]["cargoconsulta"] == 'SI') {
                                $tipogastoadm = 'CON';
                                $tipocertificadoadm = 'Consulta';
                            }

                            //
                            \logApi::general2($nameLog, $idSolicitudPago, \funcionesGenerales::utf8_encode('Tipo de certificado a generar : ' . $tipo . ' Expediente ' . $a["expediente"]));

                            //
                            $firmar = '';
                            if ($_SESSION["tramite"]["enviara"] == 'C') {
                                $firmar = 'si';
                            }

                            if ($_SESSION["generales"]["cajero"] == 'USUPUBXXx' || $usuCajero["esbanco"] == 'SIx' || $_SESSION["tramite"]["enviara"] == 'Cx') {
                                $certificadosencolados = 'si';
                                $arrCampos = array(
                                    'recibo',
                                    'idliquidacion',
                                    'fecha',
                                    'hora',
                                    'tipocertificado',
                                    'expediente',
                                    'tipogasto',
                                    'cantidad',
                                    'valor',
                                    'estado'
                                );
                                $arrValores = array(
                                    "'" . $_SESSION["tramite"]["numerorecibo"] . "'",
                                    $_SESSION["tramite"]["idliquidacion"],
                                    "'" . date("Ymd") . "'",
                                    "'" . date("His") . "'",
                                    "'" . $tipo . "'",
                                    "'" . $a["expediente"] . "'",
                                    "'" . $tipogastoadm . "'",
                                    "'" . $a["cantidad"] . "'",
                                    "'" . $a["valor"] . "'",
                                    "'PE'"
                                );
                                $resx = insertarRegistrosMysqliApi($mysqli, 'pila_certificados', $arrCampos, $arrValores);
                                if ($resx === false) {
                                    \logApi::general2($nameLog, $idSolicitudPago, \funcionesGenerales::utf8_encode('Error encolando certificados en pila_certificados para el recibo : ' . $_SESSION["tramite"]["numerorecibo"]));
                                }
                            }

                            if ($certificadosencolados == 'no') {
                                $expecertificar = '';
                                if ($tipo == 'CerPro') {
                                    $exps = \funcionesRegistrales::retornarExpedienteProponente($mysqli, ltrim($a["expediente"], '0'));
                                    $expecertificar = $a["expediente"];
                                } else {
                                    $exps = \funcionesRegistrales::retornarExpedienteMercantil($mysqli, $a["expediente"], '', '', '', 'si', 'N');
                                    $expecertificar = $a["expediente"];
                                }
                                for ($cantCer = 1; $cantCer <= $a["cantidad"]; $cantCer++) {
                                    $aleatorio = \funcionesGenerales::generarAleatorioAlfanumerico10($mysqli, 'mreg_certificados_virtuales', 'si');
                                    // \logApi::general2($nameLog, $idSolicitudPago, \funcionesGenerales::utf8_encode('genero numero aleatorio : ' . $aleatorio));
                                    if ($tipo == 'CerMat') {
                                        if (defined('FECHA_INICIO_NUEVO_CERTIFICADO_CERMAT') && FECHA_INICIO_NUEVO_CERTIFICADO_CERMAT != '' && date("Ymd") >= FECHA_INICIO_NUEVO_CERTIFICADO_CERMAT && ($exps["fechamatricula"] >= FECHA_INICIO_NUEVO_CERTIFICADO_CERMAT || $exps["fecharenovacion"] >= FECHA_INICIO_NUEVO_CERTIFICADO_CERMAT)) {
                                            require_once($_SESSION["generales"]["pathabsoluto"] . '/api/genPdfsCertificadosFormato2019.php');
                                            $namex = generarCertificadosPdfMatriculaFormato2019($mysqli, $exps, $tipocertificadoadm, $a["valorservicio"] / $a["cantidad"], $_SESSION["tramite"]["numerooperacion"], $_SESSION["tramite"]["numerorecibo"], $aleatorio, $certificadoConsultaRues, $usuCajero["escajero"], $usuCajero["esbanco"], $firmar);
                                            \logApi::general2($nameLog, $idSolicitudPago, \funcionesGenerales::utf8_encode('Genero certificado de matricula formato 2019 en disco ' . $namex));
                                        } else {
                                            if ($exps["pendiente_ajuste_nuevo_formato"] == 'R' && defined('FECHA_INICIO_NUEVO_CERTIFICADO_CERMAT') && FECHA_INICIO_NUEVO_CERTIFICADO_CERMAT != '') {
                                                require_once($_SESSION["generales"]["pathabsoluto"] . '/api/genPdfsCertificadosFormato2019.php');
                                                $namex = generarCertificadosPdfMatriculaFormato2019($mysqli, $exps, $tipocertificadoadm, $a["valorservicio"] / $a["cantidad"], $_SESSION["tramite"]["numerooperacion"], $_SESSION["tramite"]["numerorecibo"], $aleatorio, $certificadoConsultaRues, $usuCajero["escajero"], $usuCajero["esbanco"], $firmar);
                                                \logApi::general2($nameLog, $idSolicitudPago, \funcionesGenerales::utf8_encode('Genero certificado de matricula formato 2019 en disco ' . $namex));
                                            } else {
                                                require_once($_SESSION["generales"]["pathabsoluto"] . '/api/genPdfsCertificadosNuevos.php');
                                                $namex = generarCertificadosPdfMatricula($mysqli, $exps, $tipocertificadoadm, $a["valorservicio"] / $a["cantidad"], $_SESSION["tramite"]["numerooperacion"], $_SESSION["tramite"]["numerorecibo"], $aleatorio, $certificadoConsultaRues, $usuCajero["escajero"], $usuCajero["esbanco"], $firmar);
                                                \logApi::general2($nameLog, $idSolicitudPago, \funcionesGenerales::utf8_encode('Genero certificado de matricula formato antiguo en disco ' . $namex));
                                            }
                                        }
                                    }
                                    if ($tipo == 'CerExi') {
                                        if (defined('FECHA_INICIO_NUEVO_CERTIFICADO_CEREXI') && FECHA_INICIO_NUEVO_CERTIFICADO_CEREXI != '' && date("Ymd") >= FECHA_INICIO_NUEVO_CERTIFICADO_CEREXI && $exps["fechamatricula"] >= FECHA_INICIO_NUEVO_CERTIFICADO_CEREXI) {
                                            if ($exps["estadomatricula"] == 'MC') {
                                                require_once($_SESSION["generales"]["pathabsoluto"] . '/api/genPdfsCertificadosNuevos.php');
                                                $namex = generarCertificadosPdfExistencia($mysqli, $exps, $tipocertificadoadm, $a["valorservicio"] / $a["cantidad"], $_SESSION["tramite"]["numerooperacion"], $_SESSION["tramite"]["numerorecibo"], $aleatorio, $certificadoConsultaRues, $usuCajero["escajero"], $usuCajero["esbanco"], $firmar);
                                                \logApi::general2($nameLog, $idSolicitudPago, \funcionesGenerales::utf8_encode('Genero certificado de existencia formato 2019 en disco ' . $namex));
                                            } else {
                                                require_once($_SESSION["generales"]["pathabsoluto"] . '/api/genPdfsCertificadosFormato2019.php');
                                                $namex = generarCertificadosPdfExistenciaFormato2019($mysqli, $exps, $tipocertificadoadm, $a["valorservicio"] / $a["cantidad"], $_SESSION["tramite"]["numerooperacion"], $_SESSION["tramite"]["numerorecibo"], $aleatorio, $certificadoConsultaRues, $usuCajero["escajero"], $usuCajero["esbanco"], $firmar);
                                                \logApi::general2($nameLog, $idSolicitudPago, \funcionesGenerales::utf8_encode('Genero certificado de existencia formato 2019 en disco ' . $namex));
                                            }
                                        } else {
                                            if ($exps["estadomatricula"] == 'MC') {
                                                require_once($_SESSION["generales"]["pathabsoluto"] . '/api/genPdfsCertificadosNuevos.php');
                                                $namex = generarCertificadosPdfExistencia($mysqli, $exps, $tipocertificadoadm, $a["valorservicio"] / $a["cantidad"], $_SESSION["tramite"]["numerooperacion"], $_SESSION["tramite"]["numerorecibo"], $aleatorio, $certificadoConsultaRues, $usuCajero["escajero"], $usuCajero["esbanco"], $firmar);
                                                \logApi::general2($nameLog, $idSolicitudPago, \funcionesGenerales::utf8_encode('Genero certificado de existencia formato antiguo en disco ' . $namex));
                                            } else {
                                                if ($exps["pendiente_ajuste_nuevo_formato"] == 'R' && defined('FECHA_INICIO_NUEVO_CERTIFICADO_CEREXI') && FECHA_INICIO_NUEVO_CERTIFICADO_CEREXI != '') {
                                                    require_once($_SESSION["generales"]["pathabsoluto"] . '/api/genPdfsCertificadosFormato2019.php');
                                                    $namex = generarCertificadosPdfExistenciaFormato2019($mysqli, $exps, $tipocertificadoadm, $a["valorservicio"] / $a["cantidad"], $_SESSION["tramite"]["numerooperacion"], $_SESSION["tramite"]["numerorecibo"], $aleatorio, $certificadoConsultaRues, $usuCajero["escajero"], $usuCajero["esbanco"], $firmar);
                                                    \logApi::general2($nameLog, $idSolicitudPago, \funcionesGenerales::utf8_encode('Genero certificado de existencia formato 2019 en disco ' . $namex));
                                                } else {
                                                    require_once($_SESSION["generales"]["pathabsoluto"] . '/api/genPdfsCertificadosNuevos.php');
                                                    $namex = generarCertificadosPdfExistencia($mysqli, $exps, $tipocertificadoadm, $a["valorservicio"] / $a["cantidad"], $_SESSION["tramite"]["numerooperacion"], $_SESSION["tramite"]["numerorecibo"], $aleatorio, $certificadoConsultaRues, $usuCajero["escajero"], $usuCajero["esbanco"], $firmar);
                                                    \logApi::general2($nameLog, $idSolicitudPago, \funcionesGenerales::utf8_encode('Genero certificado de existencia formato antiguo en disco ' . $namex));
                                                }
                                            }
                                        }
                                    }
                                    if ($tipo == 'CerEsadl') {
                                        if (defined('FECHA_INICIO_NUEVO_CERTIFICADO_CERESADL') && FECHA_INICIO_NUEVO_CERTIFICADO_CERESADL != '' && date("Ymd") >= FECHA_INICIO_NUEVO_CERTIFICADO_CERESADL && $exps["fechamatricula"] >= FECHA_INICIO_NUEVO_CERTIFICADO_CERESADL) {
                                            if ($exps["estadomatricula"] == 'IC') {
                                                require_once($_SESSION["generales"]["pathabsoluto"] . '/api/genPdfsCertificadosNuevos.php');
                                                $namex = generarCertificadosPdfEsadl($mysqli, $exps, $tipocertificadoadm, $a["valorservicio"] / $a["cantidad"], $_SESSION["tramite"]["numerooperacion"], $_SESSION["tramite"]["numerorecibo"], $aleatorio, $certificadoConsultaRues, $usuCajero["escajero"], $usuCajero["esbanco"], $firmar);
                                                \logApi::general2($nameLog, $idSolicitudPago, \funcionesGenerales::utf8_encode('Genero certificado de ESADL formato antiguo en disco ' . $namex));
                                            } else {
                                                require_once($_SESSION["generales"]["pathabsoluto"] . '/api/genPdfsCertificadosFormato2019.php');
                                                $namex = generarCertificadosPdfEsadlFormato2019($mysqli, $exps, $tipocertificadoadm, $a["valorservicio"] / $a["cantidad"], $_SESSION["tramite"]["numerooperacion"], $_SESSION["tramite"]["numerorecibo"], $aleatorio, $certificadoConsultaRues, $usuCajero["escajero"], $usuCajero["esbanco"], $firmar);
                                                \logApi::general2($nameLog, $idSolicitudPago, \funcionesGenerales::utf8_encode('Genero certificado de ESADL formato 2019 en disco ' . $namex));
                                            }
                                        } else {
                                            if ($exps["estadomatricula"] == 'IC') {
                                                require_once($_SESSION["generales"]["pathabsoluto"] . '/api/genPdfsCertificadosNuevos.php');
                                                $namex = generarCertificadosPdfEsadl($mysqli, $exps, $tipocertificadoadm, $a["valorservicio"] / $a["cantidad"], $_SESSION["tramite"]["numerooperacion"], $_SESSION["tramite"]["numerorecibo"], $aleatorio, $certificadoConsultaRues, $usuCajero["escajero"], $usuCajero["esbanco"], $firmar);
                                                \logApi::general2($nameLog, $idSolicitudPago, \funcionesGenerales::utf8_encode('Genero certificado de ESADL formato antiguo en disco ' . $namex));
                                            } else {
                                                if ($exps["pendiente_ajuste_nuevo_formato"] == 'R' && defined('FECHA_INICIO_NUEVO_CERTIFICADO_CERESADL') && FECHA_INICIO_NUEVO_CERTIFICADO_CERESADL != '') {
                                                    require_once($_SESSION["generales"]["pathabsoluto"] . '/api/genPdfsCertificadosFormato2019.php');
                                                    $namex = generarCertificadosPdfEsadlFormato2019($mysqli, $exps, $tipocertificadoadm, $a["valorservicio"] / $a["cantidad"], $_SESSION["tramite"]["numerooperacion"], $_SESSION["tramite"]["numerorecibo"], $aleatorio, $certificadoConsultaRues, $usuCajero["escajero"], $usuCajero["esbanco"], $firmar);
                                                    \logApi::general2($nameLog, $idSolicitudPago, \funcionesGenerales::utf8_encode('Genero certificado de ESADL formato 2019 en disco ' . $namex));
                                                } else {
                                                    require_once($_SESSION["generales"]["pathabsoluto"] . '/api/genPdfsCertificadosNuevos.php');
                                                    $namex = generarCertificadosPdfEsadl($mysqli, $exps, $tipocertificadoadm, $a["valorservicio"] / $a["cantidad"], $_SESSION["tramite"]["numerooperacion"], $_SESSION["tramite"]["numerorecibo"], $aleatorio, $certificadoConsultaRues, $usuCajero["escajero"], $usuCajero["esbanco"], $firmar);
                                                    \logApi::general2($nameLog, $idSolicitudPago, \funcionesGenerales::utf8_encode('Genero certificado de ESADL formato antiguo en disco ' . $namex));
                                                }
                                            }
                                        }
                                    }
                                    if ($tipo == 'CerLibRegMer') {
                                        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/genPdfsCertificadosFormato2019.php');
                                        // require_once($_SESSION["generales"]["pathabsoluto"] . '/api/genPdfsCertificadosNuevos.php');
                                        // $namex = generarCertificadosPdfLibros($mysqli, $exps, $tipocertificadoadm, $a["valorservicio"] / $a["cantidad"], $_SESSION["tramite"]["numerooperacion"], $_SESSION["tramite"]["numerorecibo"], $aleatorio, '', $usuCajero["escajero"], $usuCajero["esbanco"], $firmar);
                                        $namex = generarCertificadosPdfLibrosFormato2019($mysqli, $exps, $tipocertificadoadm, $a["valorservicio"] / $a["cantidad"], $_SESSION["tramite"]["numerooperacion"], $_SESSION["tramite"]["numerorecibo"], $aleatorio, '', $usuCajero["escajero"], $usuCajero["esbanco"], $firmar);
                                        \logApi::general2($nameLog, $idSolicitudPago, \funcionesGenerales::utf8_encode('Genero certificado de libros formato 2019 en disco ' . $namex));
                                    }
                                    if ($tipo == 'CerLibRegEsdadl') {
                                        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/genPdfsCertificadosFormato2019.php');
                                        // require_once($_SESSION["generales"]["pathabsoluto"] . '/api/genPdfsCertificadosNuevos.php');
                                        // $namex = generarCertificadosPdfLibros($mysqli, $exps, $tipocertificadoadm, $a["valorservicio"] / $a["cantidad"], $_SESSION["tramite"]["numerooperacion"], $_SESSION["tramite"]["numerorecibo"], $aleatorio, '', $usuCajero["escajero"], $usuCajero["esbanco"], $firmar);
                                        $namex = generarCertificadosPdfLibrosFormato2019($mysqli, $exps, $tipocertificadoadm, $a["valorservicio"] / $a["cantidad"], $_SESSION["tramite"]["numerooperacion"], $_SESSION["tramite"]["numerorecibo"], $aleatorio, '', $usuCajero["escajero"], $usuCajero["esbanco"], $firmar);
                                        \logApi::general2($nameLog, $idSolicitudPago, \funcionesGenerales::utf8_encode('Genero certificado de libros formato 2019 en disco ' . $namex));
                                    }
                                    if ($tipo == 'CerPro') {
                                        $namex = generarCertificadosPdfProponentes($mysqli, $exps, $tipocertificadoadm, $a["valorservicio"] / $a["cantidad"], $_SESSION["tramite"]["numerooperacion"], $_SESSION["tramite"]["numerorecibo"], $aleatorio, $certificadoConsultaRues, $usuCajero["escajero"], $usuCajero["esbanco"], $firmar);
                                        if (\funcionesGenerales::tamanoArchivo($_SESSION["generales"]["pathabsoluto"] . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/' . $namex) == 0) {
                                            sleep(5);
                                            $namex = generarCertificadosPdfProponentes($mysqli, $exps, $tipocertificadoadm, $a["valorservicio"] / $a["cantidad"], $_SESSION["tramite"]["numerooperacion"], $_SESSION["tramite"]["numerorecibo"], $aleatorio, $certificadoConsultaRues, $usuCajero["escajero"], $usuCajero["esbanco"], $firmar);
                                            if (\funcionesGenerales::tamanoArchivo($_SESSION["generales"]["pathabsoluto"] . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/' . $namex) != 0) {
                                                \logApi::general2($nameLog, $idSolicitudPago, \funcionesGenerales::utf8_encode('Genero certificado de proponentes en disco en segundo intento ' . $namex));
                                            } else {
                                                \logApi::general2($nameLog, $idSolicitudPago, \funcionesGenerales::utf8_encode('No fue posible generar el certificado de proponentes en disco en segundo intento, tamano cero ' . $namex));
                                                if ($_SESSION["generales"]["codigousuario"] != 'USUPUBXX') {
                                                    $namex = false;
                                                } else {
                                                    $emx1 = EMAIL_ATENCION_USUARIOS;
                                                    $mensaje = 'Se detectó que en el pago electronico soportado con el recibo No. ' . $_SESSION["tramite"]["numerorecibo"] . ', el certificado enviado está vacio, se sugiere generar un gasto administrativo y enviárselo al cliente';
                                                    \funcionesGenerales::enviarEmail(SERVER_SMTP, SMTP_PORT, REQUIERE_SMTP_AUTENTICACION, SMTP_TIPO_ENCRIPCION, CUENTA_ADMIN_PORTAL, CLAVE_ADMIN_PORTAL, EMAIL_ADMIN_PORTAL, NOMBRE_ADMIN_PORTAL, $emx1, 'Certificado erroneo Recibo No. ' . $_SESSION["tramite"]["numerorecibo"], $mensaje);
                                                }
                                            }
                                        } else {
                                            \logApi::general2($nameLog, $idSolicitudPago, \funcionesGenerales::utf8_encode('Genero certificado de proponentes en disco en primer intento ' . $namex));
                                        }
                                    }

                                    if ($namex) {
                                        $iCertificados++;
                                        $arrCertificados[$iCertificados]["idservicio"] = $a["idservicio"];
                                        $arrCertificados[$iCertificados]["cantidad"] = 1;
                                        $arrCertificados[$iCertificados]["valorservicio"] = $a["valorservicio"];
                                        $arrCertificados[$iCertificados]["tipo"] = $tipo;
                                        $arrCertificados[$iCertificados]["tipocertificadoadm"] = $tipocertificadoadm;
                                        $arrCertificados[$iCertificados]["tipogastoadm"] = $tipogastoadm;
                                        $arrCertificados[$iCertificados]["texto"] = '';
                                        $arrCertificados[$iCertificados]["name"] = $namex;
                                        $arrCertificados[$iCertificados]["aleatorio"] = $aleatorio;
                                        $arrCertificados[$iCertificados]["estadodatos"] = $exps["estadodatosmatricula"];
                                        $arrCertificados[$iCertificados]["nombre"] = $exps["nombre"];
                                        $arrCertificados[$iCertificados]["expediente"] = $expecertificar;
                                        $arrCertificados[$iCertificados]["nit"] = $exps["nit"];
                                        if ($tipo != 'CerPro') {
                                            $arrCertificados[$iCertificados]["matricula"] = $expecertificar;
                                            $arrCertificados[$iCertificados]["proponente"] = '';
                                        } else {
                                            $arrCertificados[$iCertificados]["matricula"] = '';
                                            $arrCertificados[$iCertificados]["proponente"] = $expecertificar;
                                        }
                                    } else {
                                        if ($_SESSION["generales"]["codigousuario"] != 'USUPUBXX') {
                                            \logApi::general2($nameLog, $idSolicitudPago, \funcionesGenerales::utf8_encode('Error generando certificado en SII ' . $a["idservicio"]));
                                            $resultado["codigoError"] = '9990';
                                            $resultado["msgError"] = 'Error generando certificado en SII, se sugiere anular el recibo generado (' . $_SESSION["tramite"]["numerorecibo"] . ') y reintentar la operación.';

                                            //
                                            $arrCampos = array('ctranulacion');
                                            $arrValores = array('1');
                                            regrabarRegistrosMysqliApi($mysqli, 'mreg_est_recibos', $arrCampos, $arrValores, "numerorecibo='" . $_SESSION["tramite"]["numerorecibo"] . "'");

                                            //
                                            $arrCampos = array('estado');
                                            $arrValores = array('99');
                                            regrabarRegistrosMysqliApi($mysqli, 'mreg_recibosgenerados', $arrCampos, $arrValores, "recibo='" . $_SESSION["tramite"]["numerorecibo"] . "'");

                                            //
                                            $detalle = 'Anulación automática del recibo No. ' . $_SESSION["tramite"]["numerorecibo"] . ' por error en la generación del certificado';
                                            actualizarLogMysqliApi($mysqli, '006', $_SESSION["generales"]["codigousuario"], 'gestionRecibos.php', '', '', '', $detalle, '', '');

                                            //
                                            $arrCampos = array(
                                                'fecha',
                                                'hora',
                                                'idusuario',
                                                'tipogeneral',
                                                'recibo',
                                                'operacion',
                                                'expediente',
                                                'notareversion',
                                                'idmotivo',
                                                'motivo'
                                            );
                                            $arrValores = array(
                                                "'" . date("Ymd") . "'",
                                                "'" . date("His") . "'",
                                                "'" . $_SESSION["generales"]["codigousuario"] . "'",
                                                "'RECIBOS'",
                                                "'" . $_SESSION["generales"]["numerorecibo"] . "'",
                                                "'" . $_SESSION["generales"]["numerooperacion"] . "'",
                                                "'" . $expecertificar . "'",
                                                "''",
                                                "'09'",
                                                "'Anulación automática por error en la generación del certificado en caja'"
                                            );
                                            insertarRegistrosMysqliApi($mysqli, 'mreg_anulaciones', $arrCampos, $arrValores);

                                            //
                                            if ($cerrarMysqli == 'si') {
                                                $mysqli->close();
                                            }
                                        }
                                        $_SESSION["generales"]["codigousuario"] = $_SESSION["generales"]["codigousuariooriginal"];
                                        return $resultado;
                                    }
                                }
                            }
                        }
                    }
                }
                if ($certificadosencolados == 'si') {
                    exec("php " . $_SESSION["generales"]["pathabsoluto"] . "/scripts/backEndCertificados.php " . $_SESSION["generales"]["codigoempresa"] . " " . $_SESSION["generales"]["codigoempresa"] . " " . $_SESSION["tramite"]["numerorecibo"] . " > " . $_SESSION["generales"]["pathabsoluto"] . "/tmp/" . $_SESSION["generales"]["codigoempresa"] . "-backEndCertificados-" . date("Ymd") . "-" . date("His") . ".log &", $output);
                }
            }
        }


        // ******************************************************************************************************** //
        // En caso de tramites locales
        // En caso de certificados virtuales, genera el certificado solicitado en pdf y genera el control
        // para verificacion. 
        // ******************************************************************************************************** //
        $arrCertificadosSalida = array();
        if ($iCertificados != 0) {
            $iCertificadosSalida = 0;
            $arrCertificadosSalida = array();

            //
            foreach ($arrCertificados as $cert) {
                if (!isset($cert["name"])) {
                    $cert["name"] = '';
                }
                $arrCampos = array(
                    'id',
                    'recibo',
                    'operacion',
                    'fecha',
                    'hora',
                    'servicio',
                    'cantidad',
                    'valor',
                    'expediente',
                    'razonsocial',
                    'identificacioncliente',
                    'nombrecliente',
                    'estado',
                    'cantidadconsultas',
                    'tipocertificado',
                    'contenido',
                    'path'
                );
                $arrValores = array(
                    "'" . $cert["aleatorio"] . "'",
                    "'" . $_SESSION["tramite"]["numerorecibo"] . "'",
                    "'" . $_SESSION["tramite"]["numerooperacion"] . "'",
                    "'" . $_SESSION["tramite"]["fecharecibo"] . "'",
                    "'" . $_SESSION["tramite"]["horarecibo"] . "'",
                    "'" . $cert["idservicio"] . "'",
                    1,
                    $cert["valorservicio"],
                    "'" . ltrim($cert["expediente"], "0") . "'",
                    "'" . addslashes($cert["nombre"]) . "'",
                    "'" . $_SESSION["tramite"]["identificacionpagador"] . "'",
                    "'" . addslashes(iconv("UTF-8", "UTF-8//IGNORE", trim($_SESSION["tramite"]["apellidopagador"] . " " . $_SESSION["tramite"]["nombrepagador"]))) . "'",
                    "'1'",
                    0,
                    "'" . $cert["tipo"] . "'",
                    "''", // Contenido
                    "'" . $cert["name"] . "'"
                );
                $cfx = retornarRegistroMysqliApi($mysqli, 'mreg_certificados_virtuales', "id='" . $cert["aleatorio"] . "'");
                if ($cfx && empty($cfx)) {
                    $res = insertarRegistrosMysqliApi($mysqli, 'mreg_certificados_virtuales', $arrCampos, $arrValores);
                    if ($res === false) {
                        \logApi::general2($nameLog, $idSolicitudPago, \funcionesGenerales::utf8_encode('Error almacenando  mreg_certificados_virtuales : ' . $_SESSION["generales"]["mensajeerror"]));
                    }
                } else {
                    if (trim((string) $cfx["path"]) == '') {
                        $res = regrabarRegistrosMysqliApi($mysqli, 'mreg_certificados_virtuales', $arrCampos, $arrValores, "id='" . $cert["aleatorio"] . "'");
                        if ($res === false) {
                            \logApi::general2($nameLog, $idSolicitudPago, \funcionesGenerales::utf8_encode('Error almacenando  mreg_certificados_virtuales : ' . $_SESSION["generales"]["mensajeerror"]));
                        }
                    } else {
                        \logApi::general2($nameLog, $idSolicitudPago, \funcionesGenerales::utf8_encode('Error almacenando  mreg_certificados_virtuales, reescribira un id de certificado expedido previamente. Será necesario anular el recibo y regenarar.'));
                        $resultado["codigoError"] = '9999';
                        $resultado["msgError"] = 'Error generando certificado en SII , se reescribira un codigo9 de un certificado previamente generado. Por favor anule el recibo y reintente.';
                        if ($cerrarMysqli == 'si') {
                            $mysqli->close();
                        }
                        $_SESSION["generales"]["codigousuario"] = $_SESSION["generales"]["codigousuariooriginal"];
                        return $resultado;
                    }
                }

                $iCertificadosSalida++;
                $arrCertificadosSalida[$iCertificadosSalida]["file"] = PATH_ABSOLUTO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/' . $cert["name"];
                $arrCertificadosSalida[$iCertificadosSalida]["path"] = $cert["name"];
                $arrCertificadosSalida[$iCertificadosSalida]["aleatorio"] = $cert["aleatorio"];
                \logApi::general2($nameLog, $idSolicitudPago, \funcionesGenerales::utf8_encode('Almaceno certificado generado  ' . $cert["idservicio"] . ' - ' . $cert["aleatorio"]));
            }
        }


        // ******************************************************************************************************** //
        // Envia emails de confirmacion tanto al usuario como al encargado de evaluar las
        // transacciones electronicas o bancarias
        // Siempre y cuando el pago se hubiere hecho por Internet, en bancos o en corresponsales bancarios
        // 2018-06-05: Si es certificado y "enviara" es "C"(Correo) tambien envía
        // ******************************************************************************************************** //
        if (!isset($usuCajero)) {
            $usuCajero = array(
                'esbanco' => 'NO'
            );
        }
        \logApi::general2($nameLog, $idSolicitudPago, 'Idusuario: ' . $_SESSION["generales"]["cajero"] . ' EsBanco: ' . $usuCajero["esbanco"]);

        // if ($_SESSION["generales"]["cajero"] == 'USUPUBXX' || $usuCajero["esbanco"] == 'SI' || $_SESSION["tramite"]["enviara"] == 'C') {
        // ******************************************************************************************************** //
        // Actualiza el flujo del tramite virtual
        // ******************************************************************************************************** //   		   

        \funcionesRegistrales::actualizarMregLiquidacionFlujo($mysqli, $_SESSION["tramite"]["tipotramite"], $idSolicitudPago, $idSolicitudPago, '4', $result["numerorecibo"], $result["numerooperacion"], $result["numeroradicacion"], $result["fecharecibo"], $result["horarecibo"]);
        if ($totalgobernacion != 0) {
            \funcionesRegistrales::actualizarMregLiquidacionFlujo($mysqli, $_SESSION["tramite"]["tipotramite"], $idSolicitudPago, $idSolicitudPago, '4', $result["numerorecibogob"], $result["numerooperaciongob"], $result["numeroradicacion"], $result["fecharecibogob"], $result["horarecibogob"]);
        }


        // ******************************************************************************************************** //
        // Arma el arreglo de los soportes
        // ******************************************************************************************************** //
        if (ltrim($_SESSION["generales"]["codigobarras1"], "0") != '') {
            $arrSop = retornarRegistrosMysqliApi($mysqli, 'mreg_radicacionesanexos', "numerorecibo='" . $_SESSION["tramite"]["numerorecibo"] . "' or idradicacion='" . ltrim($_SESSION["generales"]["codigobarras1"], "0") . "'", "idanexo");
        } else {
            $arrSop = retornarRegistrosMysqliApi($mysqli, 'mreg_radicacionesanexos', "numerorecibo='" . $_SESSION["tramite"]["numerorecibo"] . "'", "idanexo");
        }

        $i = 0;
        foreach ($arrSop as $sop) {
            if ($sop["soporterecibo"] != 'N') {
                $i++;
                $soportes[$i]["nombre"] = $sop["observaciones"];
                $parametros = base64_encode($_SESSION["generales"]["codigoempresa"] . '|' . $sop["path"]);
                $soportes[$i]["enlace"] = TIPO_HTTP . HTTP_HOST . '/verAnexos.php?parametros=' . $parametros;
            }
        }

        //
        $arrSD = retornarRegistrosMysqliApi($mysqli, 'mreg_liquidacion_sobre', "idliquidacion=" . $_SESSION["tramite"]["idliquidacion"], "fecha,hora");
        if (!empty($arrSD)) {
            foreach ($arrSD as $d) {
                $i++;
                $soportes[$i]["nombre"] = 'Sobre digital del tramite (soporte de documentos presentados)';
                $parametros = base64_encode($_SESSION["generales"]["codigoempresa"] . '|' . $d["path"]);
                $soportes[$i]["enlace"] = TIPO_HTTP . HTTP_HOST . '/verAnexos.php?parametros=' . $parametros;
            }
        }

        //
        if ($i > 0) {
            \logApi::general2($nameLog, $idSolicitudPago, \funcionesGenerales::utf8_encode('Armo arreglo de soportes del recibo de caja (' . $i . ')'));
        }

        $txtCliente = 'CLIENTES VARIOS';
        if (trim($_SESSION["tramite"]["nombrepagador"]) != '') {
            $txtCliente = iconv("UTF-8", "UTF-8//IGNORE", $_SESSION["tramite"]["nombrepagador"] . ' ' . $_SESSION["tramite"]["apellidopagador"]);
        } else {
            $txtCliente = iconv("UTF-8", "UTF-8//IGNORE", $_SESSION["tramite"]["nombrecliente"] . ' ' . $_SESSION["tramite"]["apellidocliente"]);
        }

        $txtIdentificacion = '22222222222';
        if ($_SESSION["tramite"]["identificacionpagador"] != '') {
            $txtIdentificacion = $_SESSION["tramite"]["identificacionpagador"];
        } else {
            $txtIdentificacion = $_SESSION["tramite"]["identificacioncliente"];
        }

        $txtDireccion = '';
        if ($_SESSION["tramite"]["direccionpagador"] != '') {
            $txtDireccion = iconv("UTF-8", "UTF-8//IGNORE", $_SESSION["tramite"]["direccionpagador"]);
        } else {
            $txtDireccion = iconv("UTF-8", "UTF-8//IGNORE", $_SESSION["tramite"]["direccion"]);
        }

        $txtCiudad = '';
        if ($_SESSION["tramite"]["municipiopagador"] != '') {
            $txtCiudad = $_SESSION["tramite"]["municipiopagador"];
        } else {
            $txtCiudad = $_SESSION["tramite"]["idmunicipio"];
        }

        $txtEmail = '';
        if ($_SESSION["tramite"]["emailenviocertificados"] != '') {
            $txtEmail = $_SESSION["tramite"]["emailenviocertificados"];
        } else {
            $txtEmail = $_SESSION["tramite"]["email"];
        }

        $mensajeX = '';
        if ($_SESSION["tramite"]["cargoafiliacion"] == 'SI') {
            $mensajeX = '*** CON CARGO AL CUPO DEL AFILIADO ***<br>';
            $mensajeX .= '*** NO IMPLICA DESEMBOLSO ***<br>';
            $mensajeX .= '*** SALDO PARA CERTIFICADOS : ' . number_format($saldoafiliadoX, 0) . ' ***';
        }
        if ($_SESSION["tramite"]["pagoprepago"] != 0) {
            $mensajeX = '*** CON CARGO AL CUPO DEL PREPAGO ***<br>';
            $mensajeX .= '*** NO IMPLICA DESEMBOLSO ***<br>';
        }


        $pres = new presentacionBootstrap();
        $string = $pres->abrirPanelGeneral(800);
        $string .= '<br>';

        //
        $forzarVUE = 'no';
        $string .= $pres->abrirPanel();
        if ($_SESSION["tramite"]["subtipotramite"] == 'matriculapnatcae' || $_SESSION["tramite"]["subtipotramite"] == 'matriculapjurcae' || $forzarVUE == 'si') {
            $string .= '<table><tr>
                        <td width="50%"><img src="' . TIPO_HTTP . HTTP_HOST . '/images/logocamara' . $_SESSION["generales"]["codigoempresa"] . '.jpg' . '" width="150px"/></td>
                        <td width="50%"><img src="' . TIPO_HTTP . HTTP_HOST . '/images/logo-vue' . '.png' . '"  width="150px""/></td>
                        </tr></table>';
        } else {
            $string .= '<table><tr>
                        <td width="50%"><img src="' . TIPO_HTTP . HTTP_HOST . '/images/logocamara' . $_SESSION["generales"]["codigoempresa"] . '.jpg' . '" width="150px"/></td>
                        <td width="50%">&nbsp;</td>
                        </tr></table>';
        }
        $string .= $pres->cerrarPanel();

        //
        $string .= $pres->abrirPanel();
        $string .= $pres->armarLineaTextoInformativa('Soportes de pago de transacción', 'center', 'h2');
        $string .= $pres->armarLineaTextoInformativa(RAZONSOCIAL, 'center', 'h2');
        $string .= $pres->cerrarPanel();

        //
        $string .= $pres->abrirPanel();
        $txt = 'Apreciado usuario, a través de este correo confirmamos la transacción por usted realizada en el portal de servicios virtuales de ';
        $txt .= RAZONSOCIAL . ', la información de la transacción es la siguiente:';
        $string .= $pres->armarLineaTextoInformativa($txt, 'center');
        $txt = '<strong>Cliente: </strong>' . str_replace(array("á", "é", "í", "ó", "ú", "Á", "É", "Í", "Ó", "Ú", "ñ", "Ñ"), array("&aacute;", "&eacute;", "&iacute;", "&oacute;", "&uacute;", "&Aacute;", "&Eacute;", "&Iacute;", "&Oacute;", "&Uacute;", "&ntilde;", "&Ntilde;"), $txtCliente) . '<br>';
        $txt .= '<strong>Identificación: </strong>' . $txtIdentificacion . '<br>';
        $txt .= '<strong>Trámite realizado: </strong>' . $_SESSION["tramite"]["tipotramite"] . '<br>';
        $txt .= '<strong>Valor pagado: </strong>' . $valortotal . '<br>';
        $string .= $pres->armarLineaTextoInformativa($txt, 'left');
        $string .= $pres->cerrarPanel();

        //
        if ($mensajeX != '') {
            $string .= $pres->abrirPanel();
            $string .= $pres->armarLineaTextoInformativa($mensajeX, 'center');
            $string .= $pres->cerrarPanel();
        }

        //
        if ($totalgobernacion1 != 0) {
            if (CODIGO_EMPRESA == '55') {
                $servtot = $totalgobernacion1 + $totalcamara1;
                $mensajeX = 'Le informamos que con este recibo usted está pagando:<br>';
                $mensajeX .= '- IMPUESTO DE REGISTRO  a favor de la Gobernación de Antioquia: $' . number_format($totalgobernacion1, 0) . '<br>';
                $mensajeX .= '-- TRÁMITE REGISTRAL  ante Cámara de Comercio Aburrá Sur: $' . number_format($totalcamara1, 0, 0);
                $string .= $pres->abrirPanel();
                $string .= $pres->armarLineaTextoInformativa($mensajeX, 'center');
                $string .= $pres->cerrarPanel();
                /*
                  $mensajeX = 'Queremos informarle que en esta transacción por un total de $' . number_format($servtot, 0) . ' usted no solo está cancelando ';
                  $mensajeX .= 'lo correspondiente a su trámite registral ante la Cámara de Comercio,';
                  $mensajeX .= 'equivalente a $' . number_format($totalcamara1, 0) . ', ';
                  $mensajeX .= 'sino también el Impuesto de  Registro que recaudamos a favor de la ';
                  $mensajeX .= 'Gobernación, por valor de $' . number_format($totalgobernacion1, 0);
                  $string .= $pres->abrirPanel();
                  $string .= $pres->armarLineaTextoInformativa($mensajeX, 'center');
                  $string .= $pres->cerrarPanel();
                 */
            } else {
                $servtot = $totalgobernacion1 + $totalcamara1;
                $mensajeX = 'Queremos informarle que en esta transacción por un total de $' . number_format($servtot, 0) . ' usted no solo está cancelando ';
                $mensajeX .= 'lo correspondiente a su trámite registral ante la Cámara de Comercio,';
                $mensajeX .= 'equivalente a $' . number_format($totalcamara1, 0) . ', ';
                $mensajeX .= 'sino también el Impuesto de  Registro que recaudamos a favor de la ';
                $mensajeX .= 'Gobernación, por valor de $' . number_format($totalgobernacion1, 0);
                $string .= $pres->abrirPanel();
                $string .= $pres->armarLineaTextoInformativa($mensajeX, 'center');
                $string .= $pres->cerrarPanel();
            }
        }

        //
        if (!empty($soportes)) {
            $string .= $pres->abrirPanel();
            $string .= $pres->armarLineaTextoInformativa('<center>Soportes del pago</center>', 'center', 'h3');
            $string .= $pres->armarLineaTextoInformativa('A continuación se muestran los enlaces a los soportes del trámite realizado', 'center');
            $txSop = '';
            foreach ($soportes as $sop) {
                $txSop .= $pres->armarBotonDinamico('href', $sop["nombre"], $sop["enlace"]) . '<br>';
            }
            $string .= $pres->armarLineaTextoInformativa($txSop, 'center');
            $string .= $pres->cerrarPanel();
        }

        //
        if (!empty($arrCertificadosSalida)) {
            $string .= $pres->abrirPanel();
            $string .= $pres->armarLineaTextoInformativa('<center>Certificados adquiridos</center>', 'center', 'h2');
            $string .= $pres->armarLineaTextoInformativa('Como se adquirieron certificados en el trámite realizado, a continuación se muestran los enlaces para que pueda descargarlos', 'center');
            $txSop = '';
            foreach ($arrCertificadosSalida as $c) {
                $txSop .= $pres->armarBotonDinamico('href', 'Descargar certificado con código ' . $c["aleatorio"], TIPO_HTTP . HTTP_HOST . '/verAnexos.php?parametros=' . base64_encode($_SESSION["generales"]["codigoempresa"] . '|' . $c["path"])) . '<br>';
            }
            $string .= $pres->armarLineaTextoInformativa($txSop, 'center');
            $string .= $pres->cerrarPanel();

            $string .= $pres->abrirPanel();
            $string .= $pres->armarLineaTextoInformativa('<center>Información para apostilla</center>', 'center', 'h2');
            $txt1 = 'Si requiere información para apostillar certificados públicos expedidos por ésta cámara de comercio, haga clic en el ';
            $txt1 .= 'siguiente enlace: <a href="https://superwas.supersociedades.gov.co/SistemaPQRSWeb/ProcesarPQRS?codigoTramite=90054">Ir a apostillar</a>';
            $string .= $pres->armarLineaTextoInformativa($txt1, 'justify');
            $string .= $pres->cerrarPanel();
        } else {
            if ($certificadosencolados == 'si') {
                $string .= $pres->abrirPanel();
                $string .= $pres->armarLineaTextoInformativa('<center>Certificados adquiridos</center>', 'center', 'h2');
                $string .= $pres->armarLineaTextoInformativa('Los certificados adquiridos en esta transacción serán enviados a su correo electrónico en un lapso no mayor a 15 minutos tan pronto su generación sea realizada.', 'center');
                $string .= $pres->cerrarPanel();
            }
        }

        // 
        if ($_SESSION["tramite"]["tipotramite"] == 'renovacionmatricula' || $_SESSION["tramite"]["tipotramite"] == 'renovacionesadl') {
            if ($_SESSION["generales"]["codigoempresa"] == '39') {
                $string .= $pres->abrirPanel();
                $string .= $pres->armarLineaTextoInformativa('<center>Información de Interés</center>', 'center', 'h3');
                $txt = 'A través del enlace <a href="' . TIPO_HTTP . HTTP_HOST . '/ic.php?rec=' . $_SESSION["generales"]["codigoempresa"] . $result["numerorecibo"] . '">Descargar cartulinas</a> ';
                $txt .= 'podrá descargar las cartulinas (certificados) de los establecimientos renovados para que los exponga en forma visible en su local';
                $string .= $pres->armarLineaTextoInformativa($txt, 'center');
                $string .= $pres->cerrarPanel();
            }
        }

        //
        if (defined('RENOVACION_JURIDICAS_ALERTA_DEPOSITO') && trim(RENOVACION_JURIDICAS_ALERTA_DEPOSITO) == 'S') {

            $tienejuridicas = 'no';
            foreach ($_SESSION["tramite"]["liquidacion"] as $xli) {
                if ($xli["expediente"] != '') {
                    if (substr((string) $xli["expediente"], 0, 1) != 'S') {
                        $expx1 = retornarRegistroMysqliApi($mysqli, 'mreg_est_inscritos', "matricula='" . $xli["expediente"] . "'", "organizacion,categoria");
                        if ($expx1 && !empty($expx1)) {
                            if ($expx1["organizacion"] != '12' && $expx1["organizacion"] != '14') {
                                $tienejuridicas = 'si';
                            }
                        }
                    }
                }
            }
            if ($tienejuridicas == 'si') {
                $string .= $pres->abrirPanel();
                $string .= $pres->armarLineaTextoInformativa('<center>Información de Interés</center>', 'center', 'h3');
                $txt = 'Le recordamos que en cumplimiento de lo establecido el artículo 41 de la Ley 222 de 1995 ';
                $txt .= 'es responsabilidad del comerciante, cuando se trate de una persona jurídica, hacer el depósito de los estados financieros de propósito ';
                $txt .= 'general en la Cámara de Comercio, dentro del mes siguiente a la fecha en la que han sido aprobados';
                $string .= $pres->armarLineaTextoInformativa($txt, 'center');
                $string .= $pres->cerrarPanel();
            }
        }

        //
        if (defined('ENLACE_ENCUESTAS_PAGO_ELECTRONICO') && trim(ENLACE_ENCUESTAS_PAGO_ELECTRONICO) != '') {
            $string .= $pres->abrirPanel();
            $txt = 'Lo invitamos a diligenciar la siguiente encuesta, que nos ayudará a mejorar nuestros servicios<br>';
            $txt .= '<a href="' . ENLACE_ENCUESTAS_PAGO_ELECTRONICO . '">Encuesta</a>';
            $string .= $pres->armarLineaTextoInformativa($txt, 'center');
            $string .= $pres->cerrarPanel();
        }

        // ******************************************************************************************************** //
        // mensaje matrículas CAE
        // ******************************************************************************************************** //
        if ($_SESSION["tramite"]["subtipotramite"] === 'matriculapnatcae' || $_SESSION["tramite"]["subtipotramite"] === 'matriculapjurcae') {
            $string .= $pres->abrirPanel();
            $txt = '<center>Información de Interés</center>';
            $string .= $pres->armarLineaTextoInformativa($txt, 'center', 'h3');
            $txt = 'Una vez matriculado, ingrese a <strong>www.vue.gov.co</strong> para registrarse como empleador y acceder a la información sobre cómo ';
            $txt .= 'terminar su proceso ante las entidades de seguridad social y otros servicios.';
            $string .= $pres->armarLineaTextoInformativa($txt, 'center');
            $string .= $pres->cerrarPanel();
        }

        //
        if ($_SESSION["tramite"]["facturableelectronicamente"] == 'si') {
            if (defined('CFE_TELEFONO_ATENCION_USUARIOS') && CFE_TELEFONO_ATENCION_USUARIOS != '') {
                $xftel = CFE_TELEFONO_ATENCION_USUARIOS;
            } else {
                $xftel = TELEFONO_ATENCION_USUARIOS;
            }
            if (defined('CFE_EMAIL_ATENCION_USUARIOS') && CFE_EMAIL_ATENCION_USUARIOS != '') {
                $xfemail = CFE_EMAIL_ATENCION_USUARIOS;
            } else {
                $xfemail = EMAIL_ATENCION_USUARIOS;
            }
            $string .= $pres->abrirPanel();
            $txt = '<center>Información de Interés</center>';
            $string .= $pres->armarLineaTextoInformativa($txt, 'center', 'h3');
            $txt = 'La(s) factura(s) electrónica(s) correspondiente(s) con el pago realizado le será(n) enviada(s) a su correo electrónico en un lapso no mayor a 24 horas, ';
            $txt .= 'en caso de no recibirla(s) le solicitamos comunicarse al Nro ' . $xftel . ' o enviar un correo electrónico al buzón ' . $xfemail . ' e informar que no ';
            $txt .= 'ha recibido la(s) misma(s). De esta forma nuestro grupo de apoyo revisará que ha pasado con el proceso para realizar el envío pertinente.';
            $string .= $pres->armarLineaTextoInformativa($txt, 'center');
            $string .= $pres->cerrarPanel();
        }

        //
        $string .= $pres->cerrarPanelGeneral();

        //
        $image = '';
        $txtSalida = \funcionesGenerales::mostrarCuerpoBootstrap('', '', '', '', '', $string, $image, 'plantillaVaciaHttpEmail.html', 'string');

        // ******************************************************************************************************** //
        // Email al dueño de la transaccion 
        // ******************************************************************************************************** //
        if ($txtEmail != '') {
            if (
                    substr($txtEmail, 0, 9) != 'SINCORREO' &&
                    substr($txtEmail, 0, 8) != 'incorreo' &&
                    substr($txtEmail, 0, 9) != 'sincorreo' &&
                    substr($txtEmail, 0, 7) != 'NOTIENE' &&
                    substr($txtEmail, 0, 7) != 'notiene'
            ) {
                if (!empty($arrCertificadosSalida)) {
                    $asunto = 'Certificados y soporte de compra No. ' . $result["numerorecibo"];
                } else {
                    $asunto = 'Soporte de compra No. ' . $result["numerorecibo"];
                }
                if (TIPO_AMBIENTE === 'PRODUCCION' && $_SESSION["generales"]["emailusuariocontrol"] !== 'prueba@prueba.prueba') {
                    $salidaemail = \funcionesGenerales::enviarEmail(SERVER_SMTP, SMTP_PORT, REQUIERE_SMTP_AUTENTICACION, SMTP_TIPO_ENCRIPCION, CUENTA_ADMIN_PORTAL, CLAVE_ADMIN_PORTAL, EMAIL_ADMIN_PORTAL, NOMBRE_ADMIN_PORTAL, $txtEmail, $asunto, $txtSalida, array());
                    if ($salidaemail === false) {
                        \logApi::general2($nameLog, $idSolicitudPago, \funcionesGenerales::utf8_encode('Error en envio email al pagador ' . $txtEmail . ', se reintentara'));
                        sleep(3);
                        $salidaemail = \funcionesGenerales::enviarEmail(SERVER_SMTP, SMTP_PORT, REQUIERE_SMTP_AUTENTICACION, SMTP_TIPO_ENCRIPCION, CUENTA_ADMIN_PORTAL, CLAVE_ADMIN_PORTAL, EMAIL_ADMIN_PORTAL, NOMBRE_ADMIN_PORTAL, $txtEmail, $asunto, $txtSalida, array());
                        if ($salidaemail === false) {
                            \logApi::general2($nameLog, $idSolicitudPago, \funcionesGenerales::utf8_encode('No fue posible enviar email al pagador ' . $txtEmail));
                        } else {
                            \logApi::general2($nameLog, $idSolicitudPago, \funcionesGenerales::utf8_encode('Enviado email al pagador ' . $txtEmail . ', segundo intento'));
                            \funcionesRegistrales::actualizarMregNotificacionesParaEnviarEmail($mysqli, '30', $_SESSION["tramite"]["numeroradicacion"], '', '', $_SESSION["tramite"]["numerorecibo"], '', '', '', '', $txtIdentificacion, '', '', $txtCliente, $txtEmail, $txtSalida, date("Ymd"), date("His"), date("Ymd"), date("His"), '3', '** OK **', '');
                        }
                    } else {
                        \logApi::general2($nameLog, $idSolicitudPago, \funcionesGenerales::utf8_encode('Enviado email al pagador ' . $txtEmail));
                        \funcionesRegistrales::actualizarMregNotificacionesParaEnviarEmail($mysqli, '30', $_SESSION["tramite"]["numeroradicacion"], '', '', $_SESSION["tramite"]["numerorecibo"], '', '', '', '', $txtIdentificacion, '', '', $txtCliente, $txtEmail, $txtSalida, date("Ymd"), date("His"), date("Ymd"), date("His"), '3', '** OK **', '');
                    }
                } else {
                    if (!defined('EMAIL_NOTIFICACION_PRUEBAS') || EMAIL_NOTIFICACION_PRUEBAS == '') {
                        $emx = 'jnieto@confecamaras.org.co';
                    } else {
                        $emx = EMAIL_NOTIFICACION_PRUEBAS;
                    }
                    $salidaemail = \funcionesGenerales::enviarEmail(SERVER_SMTP, SMTP_PORT, REQUIERE_SMTP_AUTENTICACION, SMTP_TIPO_ENCRIPCION, CUENTA_ADMIN_PORTAL, CLAVE_ADMIN_PORTAL, EMAIL_ADMIN_PORTAL, NOMBRE_ADMIN_PORTAL, $emx, $asunto, $txtSalida, array());
                    if ($salidaemail === false) {
                        \logApi::general2($nameLog, $idSolicitudPago, \funcionesGenerales::utf8_encode('Error en envio email al pagador ' . $emx . ' / ' . $txtEmail . ', se reintentara'));
                        sleep(3);
                        $salidaemail = \funcionesGenerales::enviarEmail(SERVER_SMTP, SMTP_PORT, REQUIERE_SMTP_AUTENTICACION, SMTP_TIPO_ENCRIPCION, CUENTA_ADMIN_PORTAL, CLAVE_ADMIN_PORTAL, EMAIL_ADMIN_PORTAL, NOMBRE_ADMIN_PORTAL, $emx, $asunto, $txtSalida, array());
                        if ($salidaemail === false) {
                            \logApi::general2($nameLog, $idSolicitudPago, \funcionesGenerales::utf8_encode('No fue posible enviar email al pagador ' . $emx . ' (' . $txtEmail . ')'));
                        } else {
                            \logApi::general2($nameLog, $idSolicitudPago, \funcionesGenerales::utf8_encode('Enviado email al pagador ' . $emx . ' (' . $txtEmail . '), segundo intento'));
                            \funcionesRegistrales::actualizarMregNotificacionesParaEnviarEmail($mysqli, '30', $_SESSION["tramite"]["numeroradicacion"], '', '', $_SESSION["tramite"]["numerorecibo"], '', '', '', '', $txtIdentificacion, '', '', $txtCliente, $txtEmail, $txtSalida, date("Ymd"), date("His"), date("Ymd"), date("His"), '3', '** OK **', '');
                        }
                    } else {
                        \logApi::general2($nameLog, $idSolicitudPago, \funcionesGenerales::utf8_encode('Enviado email al pagador ' . $emx . ' (' . $txtEmail . ')'));
                        \funcionesRegistrales::actualizarMregNotificacionesParaEnviarEmail($mysqli, '30', $_SESSION["tramite"]["numeroradicacion"], '', '', $_SESSION["tramite"]["numerorecibo"], '', '', '', '', $txtIdentificacion, '', '', $txtCliente, $txtEmail, $txtSalida, date("Ymd"), date("His"), date("Ymd"), date("His"), '3', '** OK **', '');
                    }
                }
            }
        }

        // sleep(2);
        // ******************************************************************************************************** //
        // Email al encargado en la C.C.
        // ******************************************************************************************************** //
        if (!defined('EMAIL_NOTIFICACION_TRANSACCIONES')) {
            define('EMAIL_NOTIFICACION_TRANSACCIONES', '');
        }
        if (!defined('EMAIL_NOTIFICACION_TRANSACCIONES1')) {
            define('EMAIL_NOTIFICACION_TRANSACCIONES1', '');
        }
        if (!defined('EMAIL_NOTIFICACION_TRANSACCIONES2')) {
            define('EMAIL_NOTIFICACION_TRANSACCIONES2', '');
        }
        if (!defined('EMAIL_NOTIFICACION_TRANSACCIONES_AFILIADOS')) {
            define('EMAIL_NOTIFICACION_TRANSACCIONES_AFILIADOS', '');
        }
        $enviadotransacciones = 'no';
        $attach = array();

        // 2022-07-29: Se ajusta para que no envíe email soporte a transacciones si se trata de trámites locales
        // Solamente lo hace en caso de trámites pagados en bancos o virtuales
        if (substr($_SESSION["tramite"]["numerooperacion"], 0, 2) == '97' || substr($_SESSION["tramite"]["numerooperacion"], 0, 2) == '99') {

            $emailnot = '';
            if (trim(EMAIL_NOTIFICACION_TRANSACCIONES) != '') {
                $emailnot = EMAIL_NOTIFICACION_TRANSACCIONES;
            }
            if (trim($emailnot) == '') {
                $emailnot = EMAIL_ADMIN_PORTAL;
            }

            if (trim($emailnot) != '') {
                if (TIPO_AMBIENTE === 'PRODUCCION' && $_SESSION["generales"]["emailusuariocontrol"] !== 'prueba@prueba.prueba') {
                    \funcionesGenerales::enviarEmail(SERVER_SMTP, SMTP_PORT, REQUIERE_SMTP_AUTENTICACION, SMTP_TIPO_ENCRIPCION, CUENTA_ADMIN_PORTAL, CLAVE_ADMIN_PORTAL, EMAIL_ADMIN_PORTAL, NOMBRE_ADMIN_PORTAL, $emailnot, 'Notificacion - Soportes transaccion realizada, Recibo No. : ' . $result["numerorecibo"], $txtSalida, $attach);
                    $enviadotransacciones = 'si';
                }
            }

            $emailnot = '';
            if (trim(EMAIL_NOTIFICACION_TRANSACCIONES1) != '') {
                $emailnot = EMAIL_NOTIFICACION_TRANSACCIONES1;
            }
            if (trim($emailnot) != '') {
                if (TIPO_AMBIENTE === 'PRODUCCION' && $_SESSION["generales"]["emailusuariocontrol"] !== 'prueba@prueba.prueba') {
                    \funcionesGenerales::enviarEmail(SERVER_SMTP, SMTP_PORT, REQUIERE_SMTP_AUTENTICACION, SMTP_TIPO_ENCRIPCION, CUENTA_ADMIN_PORTAL, CLAVE_ADMIN_PORTAL, EMAIL_ADMIN_PORTAL, NOMBRE_ADMIN_PORTAL, $emailnot, 'Notificacion - Soportes transaccion realizada, Recibo No. : ' . $result["numerorecibo"], $txtSalida, $attach);
                    $enviadotransacciones = 'si';
                }
            }

            $emailnot = '';
            if (trim(EMAIL_NOTIFICACION_TRANSACCIONES2) != '') {
                $emailnot = EMAIL_NOTIFICACION_TRANSACCIONES2;
            }
            if (trim($emailnot) != '') {
                if (TIPO_AMBIENTE === 'PRODUCCION' && $_SESSION["generales"]["emailusuariocontrol"] !== 'prueba@prueba.prueba') {
                    \funcionesGenerales::enviarEmail(SERVER_SMTP, SMTP_PORT, REQUIERE_SMTP_AUTENTICACION, SMTP_TIPO_ENCRIPCION, CUENTA_ADMIN_PORTAL, CLAVE_ADMIN_PORTAL, EMAIL_ADMIN_PORTAL, NOMBRE_ADMIN_PORTAL, $emailnot, 'Notificacion - Soportes transaccion realizada, Recibo No. : ' . $result["numerorecibo"], $txtSalida, $attach);
                    $enviadotransacciones = 'si';
                }
            }
        }

        // ******************************************************************************************************** //
        // 2016-01-14 : JINT : Se adiciona por solicitud de la CC de Pereira
        // Solo para trámites de certificados de afiliados        
        // Solo en caso que dicho email sea diferente a los demás (de notificación de transacciones)
        // ******************************************************************************************************** //
        if ($_SESSION["tramite"]["cargoafiliacion"] == 'SI') {
            $emailnot = '';
            if (trim(EMAIL_NOTIFICACION_TRANSACCIONES_AFILIADOS) !== '') {
                if (
                        EMAIL_NOTIFICACION_TRANSACCIONES_AFILIADOS !== EMAIL_NOTIFICACION_TRANSACCIONES &&
                        EMAIL_NOTIFICACION_TRANSACCIONES_AFILIADOS !== EMAIL_NOTIFICACION_TRANSACCIONES1 &&
                        EMAIL_NOTIFICACION_TRANSACCIONES_AFILIADOS !== EMAIL_NOTIFICACION_TRANSACCIONES2
                ) {
                    $emailnot = EMAIL_NOTIFICACION_TRANSACCIONES_AFILIADOS;
                } else {
                    if ($enviadotransacciones = 'no') {
                        $emailnot = EMAIL_NOTIFICACION_TRANSACCIONES_AFILIADOS;
                    }
                }
            }
            if (trim($emailnot) !== '') {
                if (TIPO_AMBIENTE === 'PRODUCCION' && $_SESSION["generales"]["emailusuariocontrol"] !== 'prueba@prueba.prueba') {
                    \funcionesGenerales::enviarEmail(SERVER_SMTP, SMTP_PORT, REQUIERE_SMTP_AUTENTICACION, SMTP_TIPO_ENCRIPCION, CUENTA_ADMIN_PORTAL, CLAVE_ADMIN_PORTAL, EMAIL_ADMIN_PORTAL, NOMBRE_ADMIN_PORTAL, $emailnot, 'Notificacion - Soportes transaccion de afiliados realizada, Recibo No. : ' . $result["numerorecibo"], $txtSalida, $attach);
                }
            }
        } else {
            // 2022-11-22. JINT: Se adiciona por solicitud de la CC de Cúcuta
            // Se debe notificar a EMAIL_NOTIFICACION_TRANSACCIONES_AFILIADOS siempre y cuando el cliente afectado sea un afiliado    
            $notiafil = 'no';
            if (trim(EMAIL_NOTIFICACION_TRANSACCIONES_AFILIADOS) !== '') {
                foreach ($_SESSION["tramite"]["liquidacion"] as $l1) {
                    if (trim((string) $l1["expediente"]) != '' &&
                            $l1["expediente"] != 'NUEVANAT' &&
                            $l1["expediente"] != 'NUEVAEST' &&
                            $l1["expediente"] != 'NUEVAJUR' &&
                            $l1["expediente"] != 'NUEVAESA' &&
                            $l1["expediente"] != 'NUEVASUC' &&
                            $l1["expediente"] != 'NUEVAAGE' &&
                            $l1["expediente"] != 'NUEVACIV' &&
                            substr($l1["expediente"], 0, 1) != 'S' &&
                            $_SESSION["tramite"]["tipotramite"] != 'inscripcionproponente' &&
                            $_SESSION["tramite"]["tipotramite"] != 'actualizacionproponente' &&
                            $_SESSION["tramite"]["tipotramite"] != 'renovacionproponente' &&
                            $_SESSION["tramite"]["tipotramite"] != 'cancelacionproponente' &&
                            $_SESSION["tramite"]["tipotramite"] != 'cambiodomicilioproponente' &&
                            $_SESSION["tramite"]["tipotramite"] != 'actualizacionproponente399'
                    ) {
                        $esafil = retornarRegistroMysqliApi($mysqli, 'mreg_est_inscritos', "matricula='" . $l1["expediente"] . "'", "ctrafiliacion");
                        if ($esafil == '1' || $esafil == '3' || $esafil == '5') {
                            $notiafil = 'si';
                        }
                    }
                }
                if ($notiafil == 'si') {
                    $enafil = EMAIL_NOTIFICACION_TRANSACCIONES_AFILIADOS;
                    if (TIPO_AMBIENTE === 'PRUEBAS') {
                        $enafil = EMAIL_NOTIFICACION_PRUEBAS;
                    }
                    if ($enafil == '') {
                        $enafil = 'jnieto@confecamaras.org.co';
                    }
                    \funcionesGenerales::enviarEmail(SERVER_SMTP, SMTP_PORT, REQUIERE_SMTP_AUTENTICACION, SMTP_TIPO_ENCRIPCION, CUENTA_ADMIN_PORTAL, CLAVE_ADMIN_PORTAL, EMAIL_ADMIN_PORTAL, NOMBRE_ADMIN_PORTAL, $enafil, 'Notificacion - Soportes transaccion de afiliados realizada, Recibo No. : ' . $result["numerorecibo"], $txtSalida, $attach);
                }
            }
        }

        // ******************************************************************************************************** //
        // Actualiza el flujo de la transaccion
        // ******************************************************************************************************** //
        \funcionesRegistrales::actualizarMregLiquidacionFlujo($mysqli, $_SESSION["tramite"]["tipotramite"], $idSolicitudPago, $idSolicitudPago, '5', $result["numerorecibo"], $result["numerooperacion"], $result["numeroradicacion"], $result["fecharecibo"], $result["horarecibo"]);
        if ($totalgobernacion != 0) {
            \funcionesRegistrales::actualizarMregLiquidacionFlujo($mysqli, $_SESSION["tramite"]["tipotramite"], $idSolicitudPago, $idSolicitudPago, '5', $result["numerorecibogob"], $result["numerooperaciongob"], $result["numeroradicacion"], $result["fecharecibogob"], $result["horarecibogob"]);
        }

        // ******************************************************************************************************** //
        // Actualiza el flujo de la transaccion
        // ******************************************************************************************************** //
        \funcionesRegistrales::actualizarMregLiquidacionFlujo($mysqli, $_SESSION["tramite"]["tipotramite"], $idSolicitudPago, $idSolicitudPago, '6', $result["numerorecibo"], $result["numerooperacion"], $result["numeroradicacion"], $result["fecharecibo"], $result["horarecibo"]);
        if ($totalgobernacion != 0) {
            \funcionesRegistrales::actualizarMregLiquidacionFlujo($mysqli, $_SESSION["tramite"]["tipotramite"], $idSolicitudPago, $idSolicitudPago, '6', $result["numerorecibogob"], $result["numerooperaciongob"], $result["numeroradicacion"], $result["fecharecibogob"], $result["horarecibogob"]);
        }

        // \logApi::general2($nameLog, $idSolicitudPago, \funcionesGenerales::utf8_encode('Actualiza mreg_liquidacionflujo a 6'));
        // }
        // ****************************************************************************** //
        // 2018-02-27 : JINT : En caso de renovaciones nacionales
        // marca en mreg_establecimientos_nacionales aquellos expedientes que
        // fueron radicados con el trámite, para el postrior consumo de MR02N
        // ****************************************************************************** //
        // \logApi::general2($nameLog, $idSolicitudPago, \funcionesGenerales::utf8_encode('En caso de renovaciones nacionales, marca en mreg_establecimientos_nacionales aquellos expedientes que fueron radicados con el trámite, para el postrior consumo de MR02N'));
        if ($_SESSION["tramite"]["tipotramite"] === 'renovacionmatricula' || $_SESSION["tramite"]["tipotramite"] === 'renovacionesadl') {
            foreach ($_SESSION["tramite"]["expedientes"] as $e) {
                if ($e["renovaresteano"] === 'si') {
                    if ($e["cc"] !== '' && $e["cc"] !== CODIGO_EMPRESA) {
                        $arrCampos = array('numerorecibo', 'fecharecibo');
                        $arrValores = array("'" . $_SESSION["tramite"]["numerorecibo"] . "'", "'" . $_SESSION["tramite"]["fecharecibo"] . "'");
                        regrabarRegistrosMysqliApi($mysqli, 'mreg_establecimientos_nacionales', $arrCampos, $arrValores, "idliquidacion=" . $_SESSION["tramite"]["idliquidacion"] . " and cc='" . $e["cc"] . "' and matricula='" . $e["matricula"] . "'");
                    }
                }
            }
        }

        // *********************************************************************************************** //
        // 2017-10-16: JINT: Notificación de radicaciones
        // *********************************************************************************************** //
        $notificar = 'no';
        $ttx1 = $_SESSION["tramite"]["subtipotramite"];
        if (trim($ttx1) == '') {
            $ttx1 = $_SESSION["tramite"]["tipotramite"];
        }
        $ttx = retornarRegistroMysqliApi($mysqli, 'bas_tipotramites', "id='" . $ttx1 . "'");
        if ($ttx && !empty($ttx)) {
            if ($ttx["crearuta"] == '') {
                $notificar = 'no';
            } else {
                $notificar = 'si';
                if ($ttx["id"] === 'certificadosespeciales') {
                    $notificar = 'no';
                } else {
                    if ($ttx["crearuta"] === '07' || $ttx["crearuta"] === '29') { //Excluye los embargos
                        $notificar = 'no';
                    }
                }
                if ($notificar === 'si') {
                    $ls = 0;
                    $lsno = 0;
                    $recsx = retornarRegistrosMysqliApi($mysqli, 'mreg_est_recibos', "numerorecibo='" . $_SESSION["tramite"]["numerorecibo"] . "'", "id");
                    foreach ($recsx as $xc) {
                        $ls++;
                        $servx = retornarRegistroMysqliApi($mysqli, "mreg_servicios", "idservicio='" . $xc["servicio"] . "'");
                        if ($servx && !empty($servx)) {
                            if (
                                    $servx["rutareparto"] == '07' || // Embargos regmer
                                    $servx["rutareparto"] == '29' || // Embargos esadl

                                    $servx["rutareparto"] == '27' || // Recursos - mercantil
                                    $servx["rutareparto"] == '28' || // Recursos - esadl

                                    $servx["rutareparto"] == '31' || // Correcciones - mercantil
                                    $servx["rutareparto"] == '32' || // Correcciones - esadl
                                    $servx["rutareparto"] == '33' || // Correcciones - proponentes
                                    $servx["rutareparto"] == '34' || // Deposito de estados financieros
                                    $servx["rutareparto"] == '41' || // PQRs
                                    $servx["rutareparto"] == '42' || // Cartas de aceptación regmer
                                    $servx["rutareparto"] == '43' || // Cartas de aceptación esadl
                                    $servx["rutareparto"] == '54' || // Recursos de resposición RUES

                                    $servx["rutareparto"] == '61' || // Conciliaciones

                                    $servx["rutareparto"] == '81' || // Oficios

                                    $servx["rutareparto"] == '90' || // Certificados regmer
                                    $servx["rutareparto"] == '91' || // Certificados esadl
                                    $servx["rutareparto"] == '92' // Certificados proponentes
                            ) {
                                $lsno++;
                            }
                        }
                    }
                    unset($recsx);
                    unset($servx);
                    if ($ls == 0) {
                        $notificar = 'no';
                    } else {
                        if ($ls == $lsno) {
                            $notificar = 'no';
                        }
                    }
                }
            }
        }

        // 
        if ($notificar == 'si') {
            $res = \funcionesRegistrales::rutinaNotificarRadicacion($mysqli, $_SESSION["tramite"]["numerorecibo"], '', array(), array(), $nameLog);
            \logApi::general2($nameLog, $idSolicitudPago, 'Salio de la generacion de notificaciones de radicacion');
            if ($registroInmediato == 'si' && $verificacionsoportes != 'SI') {
                $res = \funcionesRegistrales::rutinaNotificarAsentamiento($mysqli, $_SESSION["tramite"]["numerorecibo"], '', array(), array(), $nameLog);
                \logApi::general2($nameLog, $idSolicitudPago, 'Salio de la generacion de notificaciones de asentamiento');
            } else {
                \logApi::general2($nameLog, $idSolicitudPago, 'No notifico asentamiento');
            }
        } else {
            \logApi::general2($nameLog, $idSolicitudPago, 'Transaccion no notificable en la radicacion');
        }

        // ****************************************************************************** //
        // 2019-10-29 : JINT : Llamado a la rutina de generación SIPREF de inscripciones
        // ****************************************************************************** //
        $res = \funcionesRegistrales::rutinaNotificarInscripciones($mysqli, $_SESSION["tramite"]["numeroradicacion"], $_SESSION["tramite"]["idliquidacion"], $nameLog);

        // ****************************************************************************************** //
        // 2018-12-01 : JINT : En caso de deposito de estado financieros
        // Asigna los datos básicos del recibo generado a la tabla mreg_publicacion_balances
        // ***************************************************************************************** //        
        if ($_SESSION["tramite"]["tipotramite"] == 'depestfinregmer' || $_SESSION["tramite"]["tipotramite"] == 'depestfinesadl') {
            $arrCampos = array(
                'codigobarras',
                'recibo',
                'fecharadicacion',
                'horaradicacion'
            );
            $arrValores = array(
                "'" . $result["numeroradicacion"] . "'",
                "'" . $result["numerorecibo"] . "'",
                "'" . $result["fecharecibo"] . "'",
                "'" . $result["horarecibo"] . "'"
            );
            regrabarRegistrosMysqliApi($mysqli, 'mreg_publicacion_balances', $arrCampos, $arrValores, "idliquidacion=" . $_SESSION["tramite"]["idliquidacion"]);
            \logApi::general2($nameLog, $idSolicitudPago, \funcionesGenerales::utf8_encode('asocio datos del recibo a la tabla mreg_publicacion_balances'));
        }

        // ******************************************************************************************************** //
        // Dispara la fcturación electrónica
        // ******************************************************************************************************** //
        if (defined('CFE_FECHA_INICIAL') && CFE_FECHA_INICIAL != '' && CFE_FECHA_INICIAL <= date("Ymd")) {
            $resCfe = \funcionesCFE::seleccionRecibosCFE($mysqli, '00000000', '000000', '000000', $result["numerorecibo"]);
            \logApi::general2($nameLog, $idSolicitudPago, \funcionesGenerales::utf8_encode('ejecuto envio de factura electronica a CFE - Codigo : ' . $resCfe["codigoError"] . ' - Mensaje : ' . $resCfe["mensajeError"]));
            if ($totalgobernacion != 0) {
                $resCfe1 = \funcionesCFE::seleccionRecibosCFE($mysqli, '00000000', '000000', '000000', $result["numerorecibogob"]);
                \logApi::general2($nameLog, $idSolicitudPago, \funcionesGenerales::utf8_encode('ejecuto envio de factura electronica a CFE - Codigo : ' . $resCfe1["codigoError"] . ' - Mensaje : ' . $resCfe1["mensajeError"]));
            }
        }

        // ******************************************************************************************************** //
        // Actualiza el RUES en caso de tratarse de un tramite de :
        // - Matricula de persona natural o establecimiento
        // - Renovacion
        // - Mutacion de actividad
        // - Mutacion de nombre
        // - Mutacion de actividad
        // - Cancelacion automática
        // 
        // Actualiza igualmente compite360
        // ******************************************************************************************************** //
        // 2017-12-16: JINT
        // Se adiciona control para ley 1780, código de policia alto impacto y codigo de policía multas             
        $actrues = 'no';
        $txt1 = "Valida actualizacion RUES en linea:\r\n";
        $txt1 .= "tipotramite: " . $_SESSION["tramite"]["tipotramite"] . "\r\n";
        $txt1 .= "subtipotramite: " . $_SESSION["tramite"]["subtipotramite"] . "\r\n";
        $txt1 .= "grupoServicios: " . $grupoServicios . "\r\n";
        $txt1 .= "multadoponal: " . $_SESSION["tramite"]["multadoponal"] . "\r\n";
        $txt1 .= "controlactividadaltoimpacto: " . $_SESSION["tramite"]["controlactividadaltoimpacto"] . "\r\n";
        $txt1 .= "cumplorequisitosbenley1780: " . $_SESSION["tramite"]["cumplorequisitosbenley1780"] . "\r\n";
        $txt1 .= "mantengorequisitosbenley1780: " . $_SESSION["tramite"]["mantengorequisitosbenley1780"] . "\r\n";
        $txt1 .= "renunciobeneficiosley1780: " . $_SESSION["tramite"]["renunciobeneficiosley1780"] . "\r\n";
        $txt1 .= "registroInmediato: " . $registroInmediato . "\r\n";
        $txt1 .= "asignaMatricula: " . $asignaMatricula . "\r\n";
        $txt1 .= "esRenovacion: " . $esRenovacion . "\r\n";
        $txt1 .= "esMutacion: " . $esMutacion . "\r\n";
        $txt1 .= "esCancelacion: " . $esCancelacion . "\r\n";
        $txt1 .= "ACTUALIZAR_RUES_AL_PAGAR: " . ACTUALIZAR_RUES_AL_PAGAR . "\r\n";
        \logApi::general2($nameLog, $idSolicitudPago, $txt1);

        //
        if (
                $_SESSION["tramite"]["multadoponal"] != 'S' &&
                $_SESSION["tramite"]["multadoponal"] != 'L' &&
                $_SESSION["tramite"]["controlactividadaltoimpacto"] != 'S' &&
                ($_SESSION["tramite"]["cumplorequisitosbenley1780"] != 'S' ||
                $_SESSION["tramite"]["mantengorequisitosbenley1780"] != 'S' ||
                $_SESSION["tramite"]["renunciobeneficiosley1780"] != 'N') &&
                $registroInmediato == 'si'
        ) {
            $actrues = 'si';
        } else {
            if ($_SESSION["tramite"]["tipotramite"] != 'renovacionmatricula' && $_SESSION["tramite"]["tipotramite"] != 'renovacionesadl') {
                $actrues = 'si';
            } else {
                \logApi::general2($nameLog, $idSolicitudPago, \funcionesGenerales::utf8_encode('No actualiza el RUES'));
            }
        }
        if ($actrues == 'si' && ($grupoServicios == 'RegMer' || $grupoServicios == 'RegEsadl')) {

            if ($_SESSION["tramite"]["tipotramite"] != 'inscripciondocumentos' || ($_SESSION["tramite"]["tipotramite"] == 'inscripciondocumentos' && $registroInmediato == 'si')) {
                if (!defined('ACTUALIZAR_RUES_AL_PAGAR')) {
                    define('ACTUALIZAR_RUES_AL_PAGAR', 'S');
                }
                if (ACTUALIZAR_RUES_AL_PAGAR == 'S') {
                    if (
                            $asignaMatricula == 'si' ||
                            $esRenovacion == 'si' ||
                            $esMutacion == 'si' ||
                            $esCancelacion == 'si'
                    ) {
                        $mats = array();
                        $arrTem = retornarRegistrosMysqliApi($mysqli, 'mreg_liquidaciondetalle', "idliquidacion=" . $_SESSION["tramite"]["idliquidacion"], "expediente");
                        foreach ($arrTem as $t) {
                            if ($t["cc"] == '' || $t["cc"] == CODIGO_EMPRESA) {
                                if (ltrim(trim($t["expediente"]), "0") != '') {
                                    if (!isset($mats[$t["expediente"]])) {
                                        $mats[$t["expediente"]] = $t["expediente"];
                                    }
                                }
                            }
                        }
                        if (!empty($mats)) {
                            foreach ($mats as $m) {
                                if (!defined('ACTUALIZAR_RUES_BACKGROUND') || ACTUALIZAR_RUES_BACKGROUND == '' || ACTUALIZAR_RUES_BACKGROUND == 'SI') {
                                    if ($m != '' && substr($m, 0, 5) != 'NUEVA') {
                                        exec("php " . $_SESSION["generales"]["pathabsoluto"] . "/scripts/backEndSincronizarRuesMatriculados.php " . $_SESSION["generales"]["pathabsoluto"] . " " . $_SESSION["generales"]["codigoempresa"] . " " . $m . " " . $_SESSION["generales"]["codigousuario"] . " > " . $_SESSION["generales"]["pathabsoluto"] . "/tmp/" . $_SESSION["generales"]["codigoempresa"] . "-backEndSincronizarRuesMatriculados-" . date("Ymd") . "-" . date("His") . ".log &", $output);
                                    }
                                } else {
                                    if ($m != '' && substr($m, 0, 5) != 'NUEVA') {
                                        $_SESSION['formulario'] = \funcionesRegistrales::retornarExpedienteMercantil($mysqli, $m, '', '', '', 'si', 'N');
                                        $r = \funcionesRues::actualizarMercantilRues($_SESSION['formulario'], '2');
                                        if ($r["codigoError"] == '0000') {
                                            $arrCampos = array(
                                                'rues'
                                            );
                                            $arrValores = array(
                                                "'SI'"
                                            );
                                            unset($_SESSION["expedienteactual"]);
                                            regrabarRegistrosMysqliApi($mysqli, 'mreg_est_inscritos', $arrCampos, $arrValores, "matricula='" . $m . "'");
                                            \logApi::general2($nameLog, $idSolicitudPago, \funcionesGenerales::utf8_encode('RUES actualizado satisfactoriamente (' . $m . '), version 2'));
                                        } else {
                                            \logApi::general2($nameLog, $idSolicitudPago, \funcionesGenerales::utf8_encode('Error actualizando el RUES (' . $m . '), version 2, (' . $r["msgError"] . ')'));
                                            $r = \funcionesRues::actualizarMercantilRues($_SESSION['formulario'], '1');
                                            if ($r["codigoError"] == '0000') {
                                                $arrCampos = array(
                                                    'rues'
                                                );
                                                $arrValores = array(
                                                    "'SI'"
                                                );
                                                unset($_SESSION["expedienteactual"]);
                                                regrabarRegistrosMysqliApi($mysqli, 'mreg_est_inscritos', $arrCampos, $arrValores, "matricula='" . $m . "'");
                                                \logApi::general2($nameLog, $idSolicitudPago, \funcionesGenerales::utf8_encode('RUES actualizado satisfactoriamente (' . $m . '), version 1'));
                                            } else {
                                                \logApi::general2($nameLog, $idSolicitudPago, \funcionesGenerales::utf8_encode('Error actualizando el RUES (' . $m . '), version 1, (' . $r["msgError"] . ')'));
                                            }
                                        }
                                        $_SESSION["expediente"] = $_SESSION["formulario"];
                                        unset($_SESSION["expediente"]);
                                    }
                                }
                            }
                        }
                    } else {
                        \logApi::general2($nameLog, $idSolicitudPago, \funcionesGenerales::utf8_encode('No actualiza el RUES'));
                    }
                } else {
                    \logApi::general2($nameLog, $idSolicitudPago, \funcionesGenerales::utf8_encode('No esta configurada la actualizacion del RUES al pagar en el commonXX'));
                }
            } else {
                \logApi::general2($nameLog, $idSolicitudPago, \funcionesGenerales::utf8_encode('No actualiza el RUES'));
            }
        }

        // ************************************************************************************ //
        // JINT: 2020-11-27 - Actualiza estado en VUE
        // Verifica si es un trámite de matrícula o constituciónm virtual
        // Envía al VUE los estados        
        // ************************************************************************************ //
        if ($_SESSION["tramite"]["tramitepresencial"] == '3') {
            if (
                    $_SESSION["tramite"]["subtipotramite"] == 'matriculapnatcae' ||
                    $_SESSION["tramite"]["subtipotramite"] == 'matriculapjurcae' ||
                    $_SESSION["tramite"]["subtipotramite"] == 'constitucionpjur' ||
                    $_SESSION["tramite"]["subtipotramite"] == 'matriculapnat' ||
                    $_SESSION["tramite"]["subtipotramite"] == 'matriculaest' ||
                    $_SESSION["tramite"]["subtipotramite"] == 'matriculapnat' ||
                    $_SESSION["tramite"]["tipotramite"] == 'matriculapnatcae' ||
                    $_SESSION["tramite"]["tipotramite"] == 'matriculapjurcae' ||
                    $_SESSION["tramite"]["tipotramite"] == 'constitucionpjur' ||
                    $_SESSION["tramite"]["tipotramite"] == 'matriculapnat' ||
                    $_SESSION["tramite"]["tipotramite"] == 'matriculaest' ||
                    $_SESSION["tramite"]["tipotramite"] == 'matriculapnat'
            ) {
                $ests = retornarRegistrosMysqliApi($mysqli, 'mreg_est_codigosbarras_documentos', "codigobarras='" . $_SESSION["tramite"]["numeroradicacion"] . "'", "fecha,hora");
                if ($ests && !empty($ests)) {
                    foreach ($ests as $est) {
                        \funcionesRues::reportarEstadoTramiteVUE($mysqli, $_SESSION["tramite"]["numeroradicacion"], $_SESSION["tramite"]["idliquidacion"], $est["estado"], $est["fecha"], $est["hora"]);
                    }
                }
            }
        }

        // ************************************************************************************ //
        // Actualiza la tabla indicando que se terminó el asentamiento del trámite
        // Ajusta el estado de la liquidación según el cajero
        // ************************************************************************************ //    
        $txEstado = '07'; // Pagado electrónicamente
        if ($cajero != 'USUPUBXX') {
            if ($usuCajero["escajero"] == 'SI') {
                $txEstado = '09'; // Pagado en caja
            }
            if ($usuCajero["esbanco"] == 'SI') {
                $txEstado = '20'; // Pagado en bancos
            }
        }
        $arrCampos = array('idestado', 'finalizaasentamiento');
        $arrValores = array("'" . $txEstado . "'", "'" . date("Y-m-d") . ' ' . date("H:i:s") . "'");
        regrabarRegistrosMysqliApi($mysqli, 'mreg_liquidacion', $arrCampos, $arrValores, "idliquidacion='" . $idSolicitudPago . "'");

        // ************************************************************************************ //
        // Crea el registro con la factura abonada / pagada en la tabla mreg_jsp7_cxc_facturas_abonadas
        // Cuando el usurio se USUPUBXX y sea cancelacionfcturas
        // ************************************************************************************ //
        if ($_SESSION["tramite"]["idusuario"] == 'USUPUBXX' && $_SESSION["tramite"]["tipotramite"] == 'cancelacionfacturas') {
            $arrCampos = array(
                'identificacion',
                'numerofactura',
                'codigocontable',
                'tipocxc',
                'fuenteoriginal',
                'numerofuenteoriginal',
                'cuentacliente',
                'periodoorigen',
                'ccos',
                'estado',
                'fondo',
                'valor',
                'numerorecibo',
                'fecharecibo',
                'horarecibo',
                'operacion',
                'operador',
                'usuario'
            );

            $arrValores = array(
                "'" . $_SESSION["tramite"]["cf_identificacionfactura"] . "'",
                "'" . $_SESSION["tramite"]["cf_numerofactura"] . "'",
                "'" . $_SESSION["tramite"]["cf_cuentafactura"] . "'",
                "'" . $_SESSION["tramite"]["cf_tipofactura"] . "'",
                "'" . $_SESSION["tramite"]["cf_fuenteoriginalfactura"] . "'",
                "'" . $_SESSION["tramite"]["cf_numerofuenteoriginalfactura"] . "'",
                "'" . $_SESSION["tramite"]["cf_cuentaclientefactura"] . "'",
                "'" . $_SESSION["tramite"]["cf_periodofactura"] . "'",
                "'" . $_SESSION["tramite"]["cf_ccosfactura"] . "'",
                "'PE'",
                "'" . $_SESSION["tramite"]["cf_fondofactura"] . "'",
                $_SESSION["tramite"]["valortotal"],
                "'" . $_SESSION["tramite"]["numerorecibo"] . "'",
                "'" . $_SESSION["tramite"]["fecharecibo"] . "'",
                "'" . $_SESSION["tramite"]["horarecibo"] . "'",
                "'" . $_SESSION["tramite"]["numerooperacion"] . "'",
                "'" . $_SESSION["tramite"]["idusuario"] . "'",
                "'" . $_SESSION["tramite"]["idusuario"] . "'"
            );
            insertarRegistrosMysqliApi($mysqli, 'mreg_jsp7_facturas_abonadas', $arrCampos, $arrValores);
            \logApi::general2($nameLog, $idSolicitudPago, \funcionesGenerales::utf8_encode('Actualoizo facturas abonadas en mreg_jsp7_facturas_abonadas'));
        }

        // ************************************************************************************ //
        // 2021-05-18 - JINT
        // Verifica si pago afiliación y de ser así procede a actualizar la fecha de la misma
        // ************************************************************************************ //    
        $mats = array();
        $recs = retornarRegistrosMysqliApi($mysqli, 'mreg_est_recibos', "numerorecibo='" . $result["numerorecibo"] . "'", "id");
        if ($recs && !empty($recs)) {
            foreach ($recs as $r) {
                if ($r["tipogasto"] == '0' || $r["tipogasto"] == '8') {
                    if (substr($r["servicio"], 0, 4) != '0101' && substr($r["servicio"], 0, 4) != '0103') {
                        $serv = retornarRegistroMysqliApi($mysqli, 'mreg_servicios', "idservicio='" . $r["servicio"] . "'");
                        if ($serv && !empty($serv)) {
                            if ($serv["grupoventas"] == '02') {
                                if ($r["matricula"] != '') {
                                    $mats[$r["matricula"]] = $r["matricula"];
                                }
                            }
                        }
                    }
                }
            }
        }
        if (!empty($mats)) {
            foreach ($mats as $m) {
                $histo = encontrarHistoricoPagosAfiliacionMysqliApi($mysqli, $m);
                if ($histo["anorenaflia"] != '') {
                    \funcionesRegistrales::actualizarMregEstInscritosCampo($mysqli, $m, 'anorenaflia', $histo["anorenaflia"], 'varchar', $result["numeroradicacion"], $_SESSION["tramite"]["tipotramite"], $result["numerorecibo"]);
                    \funcionesRegistrales::actualizarMregEstInscritosCampo($mysqli, $m, 'fecrenaflia', $histo["fecrenaflia"], 'varchar', $result["numeroradicacion"], $_SESSION["tramite"]["tipotramite"], $result["numerorecibo"]);
                    // \logApi::general2($nameLog, $idSolicitudPago, 'Actualizo datos de afiliacion para la matricula No. ' . $m);
                }
            }
        }

        // 2023-04-11: JINT. Prevención de error de no asociar el código de barras a la liquidacion.
        if (ltrim((string) $cbSII, "0") != '') {
            $_SESSION["tramite"]["numeroradicacion"] = (string) $cbSII;
            $result["numeroradicacion"] = (string) $cbSII;
            $arrCampos = array('numeroradicacion');
            $arrValues = array("'" . $_SESSION["tramite"]["numeroradicacion"] . "'");
            $condicion = "idliquidacion=" . $_SESSION["tramite"]["idliquidacion"];
            $resx = regrabarRegistrosMysqliApi($mysqli, 'mreg_liquidacion', $arrCampos, $arrValues, $condicion);
            if ($resx === false) {
                \logApi::general2($nameLog, $idSolicitudPago, \funcionesGenerales::utf8_encode('Error final en la asignacion del codigo de barras (' . $_SESSION["tramite"]["numeroradicacion"] . ') a la liquidacion (' . $_SESSION["tramite"]["idliquidacion"] . ')'));
            } else {
                \logApi::general2($nameLog, $idSolicitudPago, \funcionesGenerales::utf8_encode('asignacion final del codigo de barras (' . $_SESSION["tramite"]["numeroradicacion"] . ') a la liquidacion (' . $_SESSION["tramite"]["idliquidacion"] . ')'));
            }
        }

        //
        if (substr($result["numerooperacion"], 0, 2) == '97' || substr($result["numerooperacion"], 0, 2) == '99') {
            $arrCampos = array(
                'horafinalpagnopres'
            );
            $arrValores = array(
                "'" . date("Ymd") . ' ' . date("His") . "'"
            );
            regrabarRegistrosMysqliApi($mysqli, 'mreg_liquidacion', $arrCampos, $arrValores, "idliquidacion=" . $_SESSION["tramite"]["idliquidacion"]);
        }

        //
        if ($archivable == 'si') {
            if ($result["numeroradicacion"] != '') {
                if (defined('INFOMAR_ARCHIVO') && substr((string) INFOMAR_ARCHIVO, 0, 2) == 'SI') {
                    \funcionesRegistrales::rutinaInformarArchivoTramite($mysqli, $result["numeroradicacion"]);
                }
            }
        }


        //
        if ($cerrarMysqli == 'si') {
            $mysqli->close();
        }

        //    
        $resultado["codigoError"] = '0000';
        $resultado["msgError"] = '0000';
        $resultado["numeroRecibo"] = $result["numerorecibo"];
        $resultado["numeroOperacion"] = $result["numerooperacion"];
        $resultado["fechaRecibo"] = $result["fecharecibo"];
        $resultado["horaRecibo"] = $result["horarecibo"];

        $resultado["numeroReciboGob"] = $result["numerorecibogob"];
        $resultado["numeroOperacionGob"] = $result["numerooperaciongob"];
        $resultado["fechaReciboGob"] = $result["fecharecibogob"];
        $resultado["horaReciboGob"] = $result["horarecibogob"];

        $resultado["numeroRadicacion"] = $result["numeroradicacion"];
        $resultado["codigoBarras"] = $result["numeroradicacion"];

        // Envio respuesta a webhook
        // pagos confirmados        
        if (trim((string) $_SESSION["tramite"]["webhook"]) != '') {
            \logApi::general2($nameLog, $idSolicitudPago, 'Envio webhook a app movil : ' . $_SESSION["tramite"]["webhook"]);
            $pwebhook = array();
            $pwebhook["codigocamara"] = CODIGO_EMPRESA;
            $pwebhook["idliquidacion"] = $_SESSION["tramite"]["idliquidacion"];
            $pwebhook["numerorecuperacion"] = $_SESSION["tramite"]["numerorecuperacion"];
            $pwebhook["recibo"] = $resultado["numeroRecibo"];
            $pwebhook["fecharecibo"] = $resultado["fechaRecibo"];
            $pwebhook["horarecibo"] = $resultado["horaRecibo"];
            $pwebhook["estado"] = 'Pagado';
            $jwebhook = json_encode($pwebhook);
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, $_SESSION["tramite"]["webhook"]);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $jwebhook);
            curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-type: application/json'));
            curl_exec($curl);
            curl_close($curl);
            \logApi::general2($nameLog, $idSolicitudPago, 'Envio json a webhook : ' . $_SESSION["tramite"]["webhook"] . ' - ' . $jwebhook);
        }

        //
        \logApi::general2($nameLog, $idSolicitudPago, 'Evalua integracion VUE - asentamiento automático');
        $sp = \funcionesGenerales::armarVariablesPantalla();
        $matpcer = '';
        $matpnat1 = '';
        $matest1 = '';
        if (defined('CTVCE_IMPORTACION_AUTOMATICA') && CTVCE_IMPORTACION_AUTOMATICA == 'S') {
            if ($_SESSION["tramite"]["subtipotramite"] == 'matriculapnatcae' ||
                    $_SESSION["tramite"]["subtipotramite"] == 'matriculapnatvue' ||
                    $_SESSION["tramite"]["subtipotramite"] == 'matriculaestcae' ||
                    $_SESSION["tramite"]["subtipotramite"] == 'matriculaestvue') {
                if (!empty($xInscripciones)) {
                    \logApi::general2($nameLog, $idSolicitudPago, 'Ingresa a importar automaticamente matriculas desde VUE / CTCE');
                    foreach ($xInscripciones as $xi) {
                        if ($xi["acto"] == '9990' || $xi["acto"] == '9991' || $xi["acto"] == '9993') {
                            if ($xi["matricula"] != '') {
                                \logApi::general2($nameLog, $idSolicitudPago, 'Importa matricua No. ' . $xi["matricula"]);
                                $parametros = array(
                                    'codigoempresa' => CODIGO_EMPRESA,
                                    'codigousuario' => $_SESSION["generales"]["codigousuario"],
                                    'emailusuariocontrol' => $_SESSION["generales"]["emailusuariocontrol"],
                                    'nombreusuariocontrol' => $_SESSION["generales"]["nombreusuariocontrol"],
                                    'identificacionusuariocontrol' => $_SESSION["generales"]["identificacionusuariocontrol"],
                                    'idliquidacion' => $_SESSION["tramite"]["idliquidacion"],
                                    'numerorecuperacion' => $_SESSION["tramite"]["numerorecuperacion"],
                                    'matricula' => $xi["matricula"],
                                    'organizacion' => $xi["organizacion"],
                                    'categoria' => $xi["categoria"],
                                    'codigobarras' => $resultado["numeroRadicacion"],
                                    'recibo' => $resultado["numeroRecibo"],
                                    'codigopaso' => '01.001',
                                    'libro' => $xi["libro"],
                                    'registro' => $xi["numreg"],
                                    'dupli' => $xi["dupli"],
                                    'matriculapropietario' => $matpcer,
                                    'log' => $nameLog
                                );
                                \funcionesRues::importarFormularioCTVCEAutomatico($parametros);
                                if ($xi["acto"] == '9991') {
                                    $matpcer = $xi["matricula"];
                                    $matpnat1 = $xi["matricula"];
                                }
                                if ($xi["acto"] == '9993') {
                                    $matest1 = $xi["matricula"];
                                    if ($matpcer == '') {
                                        $matpcer = $xi["matricula"];
                                    }
                                }
                            }
                        }
                    }
                    if ($matpcer != '') {
                        actualizarCampoMysqliApi($mysqli,'mreg_certificados_pendientes','matricula',"'" . $matpcer . "'", "recibo='" . $_SESSION["tramite"]["numerorecibo"] . "'");
                    }
                    if ($matpnat1 != '') {
                        actualizarCampoMysqliApi($mysqli,'mreg_liquidacion','idexpedientebase',"'" . $matpnat1 . "'", "idliquidacion=" . $_SESSION["tramite"]["idliquidacion"]);
                        actualizarCampoMysqliApi($mysqli,'mreg_liquidacion','idmatriculabase',"'" . $matpnat1 . "'", "idliquidacion=" . $_SESSION["tramite"]["idliquidacion"]);
                        actualizarCampoMysqliApi($mysqli,'mreg_liquidaciondetalle','expediente',"'" . $matpnat1 . "'", "idliquidacion=" . $_SESSION["tramite"]["idliquidacion"] . " and expediente='NUEVANAT'");
                        actualizarCampoMysqliApi($mysqli,'mreg_liquidaciondetalle', 'expediente', "'" . $matpnat1 . "'", "idliquidacion=" . $_SESSION["tramite"]["idliquidacion"] . " and idservicio='01090110'");
                        actualizarCampoMysqliApi($mysqli,'mreg_est_recibos','matricula', "'" . $matpnat1 . "'", "numerorecibo='" . $_SESSION["tramite"]["numerorecibo"] . "' and matricula='NUEVANAT'");
                        actualizarCampoMysqliApi($mysqli,'mreg_est_recibos','matricula', "'" . $matpnat1 . "'", "numerorecibo='" . $_SESSION["tramite"]["numerorecibo"] . "' and servicio='01090110'");
                        actualizarCampoMysqliApi($mysqli,'mreg_recibosgenerados_detalle','matricula',"'" . $matpnat1 . "'", "recibo='" . $_SESSION["tramite"]["numerorecibo"] . "' and matricula='NUEVANAT'");
                        actualizarCampoMysqliApi($mysqli,'mreg_recibosgenerados_detalle','matricula',"'" . $matpnat1 . "'", "recibo='" . $_SESSION["tramite"]["numerorecibo"] . "' and idservicio='01090110'");
                    }
                    if ($matest1 != '') {
                        if ($matpnat1 == '') {
                            actualizarCampoMysqliApi($mysqli,'mreg_liquidacion','idexpedientebase',"'" . $matest1 . "'", "idliquidacion=" . $_SESSION["tramite"]["idliquidacion"]);
                            actualizarCampoMysqliApi($mysqli,'mreg_liquidacion','idmatriculabase',"'" . $matest1 . "'", "idliquidacion=" . $_SESSION["tramite"]["idliquidacion"]);
                        }
                        actualizarCampoMysqliApi($mysqli,'mreg_liquidaciondetalle','expediente',"'" . $matest1 . "'", "idliquidacion=" . $_SESSION["tramite"]["idliquidacion"] . " and expediente='NUEVAEST'");
                        actualizarCampoMysqliApi($mysqli,'mreg_est_recibos','matricula',"'" . $matest1 . "'", "numerorecibo='" . $_SESSION["tramite"]["numerorecibo"] . "' and matricula='NUEVAEST'");
                        actualizarCampoMysqliApi($mysqli,'mreg_recibosgenerados_detalle','matricula',"'" . $matest1 . "'", "recibo='" . $_SESSION["tramite"]["numerorecibo"] . "' and matricula='NUEVAEST'");
                    }
                    $res = \funcionesRegistrales::rutinaNotificarInscripciones($mysqli, $_SESSION["tramite"]["numeroradicacion"], $_SESSION["tramite"]["idliquidacion"], $nameLog, '', '', 'todos', 'si');
                }
            }
        }

        //
        \logApi::general2($nameLog, $idSolicitudPago, '########## FINALIZO REGISTRO DE PAGO ########');

        $_SESSION["generales"]["codigousuario"] = $_SESSION["generales"]["codigousuariooriginal"];
        return $resultado;
    }

}

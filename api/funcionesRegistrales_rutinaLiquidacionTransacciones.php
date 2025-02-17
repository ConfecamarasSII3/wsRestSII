<?php

class funcionesRegistrales_rutinaLiquidacionTransacciones {

    public static function rutinaLiquidacionTransacciones($mysqli, $transaccion = '', $tipoliquidacion = '', $expediente = false) {
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common' . $_SESSION ["generales"] ["codigoempresa"] . '.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/myErrorHandler.php');
        set_error_handler('myErrorHandler');

        //
        $_SESSION["codigos"] = array();
        $_SESSION["codigos"]["matriculascomerciantes"] = array(
            '01010000',
            '01010010',
            '01020100',
            '01020200',
            '01020300',
            '01020400',
            '01020500',
            '01020600',
            '01050000',
            '01060000'
        );
        $_SESSION["codigos"]["matriculasestablecimientos"] = array(
            '01030100',
            '01030200',
            '01040100',
            '01040200',
            '01040300',
            '01040400'
        );
        $_SESSION["codigos"]["constituciones"] = array(
            '09060500',
            '09090100',
            '09090101',
            '09090102',
            '09090111',
            '09090112',
            '09090121',
            '09090122',
            '09090123',
            '09090124',
            '09090125',
            '09130100',
            '09140100',
            '09510100',
            '09530100',
            '09540100',
            '09550100'
        );
        $_SESSION["codigos"]["aperturas"] = array(
            '01030100',
            '01030200',
            '09060100',
            '09060101',
            '09060102',
            '09060103'
        );

        //
        $fcorte = retornarRegistroMysqliApi($mysqli, 'mreg_cortes_renovacion', "ano='" . date("Y") . "'", "corte");

        //
        $nameLog = 'rutinaLiquidacionTransacciones_' . date("Ymd");

        \logApi::general2($nameLog, $_SESSION["tramite"]["idliquidacion"], '******************************************** ');
        \logApi::general2($nameLog, $_SESSION["tramite"]["idliquidacion"], 'Inicia liquidacion de tramite');
        \logApi::general2($nameLog, $_SESSION["tramite"]["idliquidacion"], '******************************************** ');

        //
        $txtFinal = '';

        // echo "Transaccion : " . $transaccion . '<br>';
        // *********************************************************************** //
        // Arma Tabla de servicios
        // *********************************************************************** //
        $txtServAfiliacion = '';
        $arrServs = array();
        $arrTem = retornarRegistrosMysqliApi($mysqli, 'mreg_servicios', "1=1", "idservicio");
        foreach ($arrTem as $t) {
            $arrServs[$t["idservicio"]] = $t;
            if ($t["grupoventas"] == '02') {
                if ($txtServAfiliacion != '') {
                    $txtServAfiliacion .= ",";
                }
                $txtServAfiliacion .= "'" . $t["idservicio"] . "'";
            }
        }

        // *********************************************************************** //
        // Arma tabla de transacciones asociadas a la liquidacion
        // *********************************************************************** //
        $_SESSION["tramite"]["txtemergente"] = '';
        $arrTrans = array();
        if ($transaccion != '') {
            $arrTem = retornarRegistroMysqliApi($mysqli, 'mreg_transacciones', "idcampo='" . $transaccion . "'");
            if ($arrTem === false || empty($arrTem)) {
                $_SESSION["generales"]["txtemergente"] = 'Transacci&oacute;n no localizada (' . $transaccion . ')';
                return false;
            } else {
                $arrTrans[1]["maetrans"] = $arrTem;
                $arrTrans[1]["dattrans"] = array();
                $arrTrans[1]["dattrans"]["idsecuencia"] = 1;
                $arrTrans[1]["dattrans"]["idtransaccion"] = $transaccion;
                $arrTrans[1]["dattrans"]["matriculaafectada"] = $_SESSION["tramite"]["idmatriculabase"];
                if (trim($arrTrans[1]["dattrans"]["matriculaafectada"]) == '') {
                    $arrTrans[1]["dattrans"]["matriculaafectada"] = $_SESSION["tramite"]["idexpedientebase"];
                }
                $arrTrans[1]["dattrans"]["tipodoc"] = '06'; // Documento privado
                $arrTrans[1]["dattrans"]["numdoc"] = 'N/A';
                $arrTrans[1]["dattrans"]["origendoc"] = 'EL COMERCIANTE';
                $arrTrans[1]["dattrans"]["mundoc"] = '';
                $arrTrans[1]["dattrans"]["camaravendedor"] = '';
                $arrTrans[1]["dattrans"]["matriculavendedor"] = '';
                $arrTrans[1]["dattrans"]["tipoidentificacionvendedor"] = '';
                $arrTrans[1]["dattrans"]["identificacionvendedor"] = '';
                $arrTrans[1]["dattrans"]["nombrevendedor"] = '';
                $arrTrans[1]["dattrans"]["nombre1vendedor"] = '';
                $arrTrans[1]["dattrans"]["nombre2vendedor"] = '';
                $arrTrans[1]["dattrans"]["apellido1vendedor"] = '';
                $arrTrans[1]["dattrans"]["apellido2vendedor"] = '';
                $arrTrans[1]["dattrans"]["emailvendedor"] = '';
                $arrTrans[1]["dattrans"]["celularvendedor"] = '';
                $arrTrans[1]["dattrans"]["cancelarvendedor"] = '';
                $arrTrans[1]["dattrans"]["camaracomprador"] = '';
                $arrTrans[1]["dattrans"]["matriculacomprador"] = '';
                $arrTrans[1]["dattrans"]["organizacioncomprador"] = '';
                $arrTrans[1]["dattrans"]["municipiocomprador"] = '';
                $arrTrans[1]["dattrans"]["tipoidentificacioncomprador"] = '';
                $arrTrans[1]["dattrans"]["identificacioncomprador"] = '';
                $arrTrans[1]["dattrans"]["nombrecomprador"] = '';
                $arrTrans[1]["dattrans"]["nombre1comprador"] = '';
                $arrTrans[1]["dattrans"]["nombre2comprador"] = '';
                $arrTrans[1]["dattrans"]["apellido1comprador"] = '';
                $arrTrans[1]["dattrans"]["apellido2comprador"] = '';
                $arrTrans[1]["dattrans"]["emailcomprador"] = '';
                $arrTrans[1]["dattrans"]["celularcomprador"] = '';
                $arrTrans[1]["dattrans"]["activoscomprador"] = 0;
                $arrTrans[1]["dattrans"]["personalcomprador"] = 0;
                $arrTrans[1]["dattrans"]["municipioanterior"] = '';
                $arrTrans[1]["dattrans"]["matriculaanterior"] = '';
                $arrTrans[1]["dattrans"]["camaraanterior"] = '';
                $arrTrans[1]["dattrans"]["fechamatriculaanterior"] = '';
                $arrTrans[1]["dattrans"]["fecharenovacionanterior"] = '';
                $arrTrans[1]["dattrans"]["ultimoanorenovadoanterior"] = '';
                $arrTrans[1]["dattrans"]["benart7anterior"] = '';
                $arrTrans[1]["dattrans"]["camaradestino"] = '';
                $arrTrans[1]["dattrans"]["organizacion"] = $_SESSION["tramite"]["organizacionbase"];
                $arrTrans[1]["dattrans"]["categoria"] = $_SESSION["tramite"]["categoriabase"];
                $arrTrans[1]["dattrans"]["razonsocial"] = str_replace("'", "´", $_SESSION["tramite"]["nombrebase"]);
                $arrTrans[1]["dattrans"]["sigla"] = '';
                $arrTrans[1]["dattrans"]["tipoidentificacion"] = '';
                $arrTrans[1]["dattrans"]["identificacion"] = '';
                $arrTrans[1]["dattrans"]["nit"] = '';
                $arrTrans[1]["dattrans"]["prerut"] = '';
                $arrTrans[1]["dattrans"]["ape1"] = '';
                $arrTrans[1]["dattrans"]["ape2"] = '';
                $arrTrans[1]["dattrans"]["nom1"] = '';
                $arrTrans[1]["dattrans"]["nom2"] = '';
                $arrTrans[1]["dattrans"]["cargo"] = '';
                $arrTrans[1]["dattrans"]["idvinculo"] = '';
                $arrTrans[1]["dattrans"]["idrenglon"] = '';
                $arrTrans[1]["dattrans"]["aceptacion"] = '';
                $arrTrans[1]["dattrans"]["identificacionrepresentada"] = '';
                $arrTrans[1]["dattrans"]["razonsocialrepresentada"] = '';
                $arrTrans[1]["dattrans"]["pornaltot"] = 0;
                $arrTrans[1]["dattrans"]["pornalpub"] = 0;
                $arrTrans[1]["dattrans"]["pornalpri"] = 0;
                $arrTrans[1]["dattrans"]["porexttot"] = 0;
                $arrTrans[1]["dattrans"]["porextpub"] = 0;
                $arrTrans[1]["dattrans"]["porextpri"] = 0;
                $arrTrans[1]["dattrans"]["personal"] = 0;
                $arrTrans[1]["dattrans"]["activos"] = 0;
                $arrTrans[1]["dattrans"]["ingresos"] = 0;
                $arrTrans[1]["dattrans"]["ciiu"] = '';
                $arrTrans[1]["dattrans"]["costotransaccion"] = 0;
                $arrTrans[1]["dattrans"]["patrimonio"] = 0;
                $arrTrans[1]["dattrans"]["benart7"] = '';
                $arrTrans[1]["dattrans"]["benley1780"] = '';
                $arrTrans[1]["dattrans"]["fechanacimientopnat"] = '';
                $arrTrans[1]["dattrans"]["capitalsocial"] = 0;
                $arrTrans[1]["dattrans"]["capitalautorizado"] = 0;
                $arrTrans[1]["dattrans"]["capitalsuscrito"] = 0;
                $arrTrans[1]["dattrans"]["capitalpagado"] = 0;
                $arrTrans[1]["dattrans"]["aporteactivos"] = 0;
                $arrTrans[1]["dattrans"]["aportedinero"] = 0;
                $arrTrans[1]["dattrans"]["aportelaboral"] = 0;
                $arrTrans[1]["dattrans"]["aportelaboraladicional"] = 0;
                $arrTrans[1]["dattrans"]["capitalasignado"] = 0;
                $arrTrans[1]["dattrans"]["acreditapagoir"] = 'N';
                $arrTrans[1]["dattrans"]["nroreciboacreditapagoir"] = '';
                $arrTrans[1]["dattrans"]["fechareciboacreditapagoir"] = '';
                $arrTrans[1]["dattrans"]["gobernacionacreditapagoir"] = '';
                $arrTrans[1]["dattrans"]["dircom"] = '';
                $arrTrans[1]["dattrans"]["municipio"] = '';
                $arrTrans[1]["dattrans"]["fechaduracion"] = '';
                $arrTrans[1]["dattrans"]["tipodisolucion"] = '';
                $arrTrans[1]["dattrans"]["tipoliquidacion"] = '';
                $arrTrans[1]["dattrans"]["motivodisolucion"] = '';
                $arrTrans[1]["dattrans"]["motivoliquidacion"] = '';
                $arrTrans[1]["dattrans"]["ciiu1"] = '';
                $arrTrans[1]["dattrans"]["ciiu2"] = '';
                $arrTrans[1]["dattrans"]["ciiu3"] = '';
                $arrTrans[1]["dattrans"]["ciiu4"] = '';
                $arrTrans[1]["dattrans"]["entidadvigilancia"] = '';
                $arrTrans[1]["dattrans"]["objetosocial"] = '';
                $arrTrans[1]["dattrans"]["facultades"] = '';
                $arrTrans[1]["dattrans"]["limitaciones"] = '';
                $arrTrans[1]["dattrans"]["poderespecial"] = '';
                $arrTrans[1]["dattrans"]["texto"] = '';
                $arrTrans[1]["dattrans"]["cantidad"] = 1;
                $arrTrans[1]["dattrans"]["cantidadadicional"] = 0;
                $arrTrans[1]["dattrans"]["tipocontrolante"] = '';
                $arrTrans[1]["dattrans"]["tipocontrolantemotivo"] = '';
                $arrTrans[1]["dattrans"]["tipoidentificacioncontrolante"] = '';
                $arrTrans[1]["dattrans"]["identificacioncontrolante"] = '';
                $arrTrans[1]["dattrans"]["nombrecontrolante"] = '';
                $arrTrans[1]["dattrans"]["tipoidentificacionsocio"] = '';
                $arrTrans[1]["dattrans"]["identificacionsocio"] = '';
                $arrTrans[1]["dattrans"]["nombresocio"] = '';
                $arrTrans[1]["dattrans"]["direccionnotificacionsocio"] = '';
                $arrTrans[1]["dattrans"]["domiciliosocio"] = '';
                $arrTrans[1]["dattrans"]["nacionalidadsocio"] = '';
                $arrTrans[1]["dattrans"]["actividadsocio"] = '';
                $arrTrans[1]["dattrans"]["cantidadcermat"] = 0;
                $arrTrans[1]["dattrans"]["cantidadcerexi"] = 0;
                $arrTrans[1]["dattrans"]["cantidadcerlib"] = 0;
                $arrTrans[1]["dattrans"]["anadirrazonsocial"] = '';
                $arrTrans[1]["dattrans"]["codgenesadl"] = '';
                $arrTrans[1]["dattrans"]["codespeesadl"] = '';
                $arrTrans[1]["dattrans"]["condiespecialley2219"] = '';

                \logApi::general2($nameLog, $_SESSION["tramite"]["idliquidacion"], 'Cargo datos de transaccion');
            }
        } else {
            $i = 0;
            foreach ($_SESSION["tramite"]["transacciones"] as $t) {
                $i++;
                $arrTem = retornarRegistroMysqliApi($mysqli, 'mreg_transacciones', "idcampo='" . $t["idtransaccion"] . "'");
                if ($arrTem === false || empty($arrTem)) {
                    $_SESSION["generales"]["txtemergente"] .= 'Transacci&oacute;n ' . $t["idtransaccion"] . ' no encontrada en el maestro de transacciones\r\n';
                } else {
                    $arrTrans[$i]["maetrans"] = $arrTem;
                    $arrTrans[$i]["dattrans"] = $t;
                }
            }
        }
        if (trim($_SESSION["generales"]["txtemergente"]) != '') {
            return false;
        }

        // ********************************************************************************** //
        // Verifica si la camara liquida o no impuesto de registro
        // Encuentra servicios, porcentajes y valores
        // ********************************************************************************** //	
        $liquidarIR = retornarClaveValorMysqliApi($mysqli, '90.01.35');
        \logApi::general2($nameLog, $_SESSION["tramite"]["idliquidacion"], 'Liquidar impuesto de registro segun claves control ' . $liquidarIR);

        //
        if ($liquidarIR == 'SI') {
            if (isset($_SESSION["tramite"]["liquidarir"]) && $_SESSION["tramite"]["liquidarir"] == 'NO') {
                $liquidarIR = 'NO';
                \logApi::general2($nameLog, $_SESSION["tramite"]["idliquidacion"], 'No se liquidara impuesto de registro por reporte de presentacion de pago');
            }
        }

        // Servicios para el calculo del impuesto de registro
        $sir_regmer_cuantia = retornarClaveValorMysqliApi($mysqli, '90.27.53');
        $sir_regmer_cuantia_mipyme = retornarClaveValorMysqliApi($mysqli, '90.27.54');
        if ($sir_regmer_cuantia_mipyme == '') {
            $sir_regmer_cuantia_mipyme = $sir_regmer_cuantia;
        }
        $sir_regmer_sincuantia = retornarClaveValorMysqliApi($mysqli, '90.27.51');
        $sir_regesadl_cuantia = retornarClaveValorMysqliApi($mysqli, '90.27.63');
        $sir_regesadl_cuantia_mipyme = retornarClaveValorMysqliApi($mysqli, '90.27.64');
        if ($sir_regesadl_cuantia_mipyme == '') {
            $sir_regesadl_cuantia_mipyme = $sir_regesadl_cuantia;
        }
        $sir_regesadl_sincuantia = retornarClaveValorMysqliApi($mysqli, '90.27.61');

        // Servicios para el calculo de la mora del impuesto de registro
        $smir_regmer_cuantia = retornarClaveValorMysqliApi($mysqli, '90.27.57');
        $smir_regmer_sincuantia = retornarClaveValorMysqliApi($mysqli, '90.27.55');
        $smir_regesadl_cuantia = retornarClaveValorMysqliApi($mysqli, '90.27.67');
        $smir_regesadl_sincuantia = retornarClaveValorMysqliApi($mysqli, '90.27.65');

        // *********************************************************************** //
        // Inicializa datos de la liquidación
        // *********************************************************************** //
        $_SESSION["tramite"]["liquidacion"] = array();
        $_SESSION["tramite"]["valorbruto"] = 0;
        $_SESSION["tramite"]["valoriva"] = 0;
        $_SESSION["tramite"]["valortotal"] = 0;
        $_SESSION["tramite"]["valorbaseiva"] = 0;
        $_SESSION["tramite"]["valorimpregistro"] = 0;

        // ************************************************************ //
        // Cuando el trámite sea renovación de matrícula
        // ************************************************************ //
        $ley1780 = array();
        $iley1780 = 0;
        $liquidarLey1780 = 'NO';
        $disminucionactivos = 'NO';

        if ($_SESSION["tramite"]["tipotramite"] == 'renovacionmatricula' || $_SESSION["tramite"]["tipotramite"] == 'renovacionesadl') {

            //
            $_SESSION["tramite"]["liqtideprop"] = '';
            $_SESSION["tramite"]["liqideprop"] = '';
            $_SESSION["tramite"]["liqmatprop"] = '';
            $_SESSION["tramite"]["liqcamprop"] = '';
            $_SESSION["tramite"]["liqactprop"] = 0;
            $_SESSION["tramite"]["liqcantesttotnal"] = 0;
            $cantesttot = 0;

            // Encuentra datos del propietario en la renovación
            foreach ($_SESSION["tramite"]["expedientes"] as $matri) {
                if ($matri["organizacion"] == '01' || ($matri["organizacion"] > '02' && $matri["categoria"] == '1')) {
                    if ($matri["renovaresteano"] == 'si') {
                        $_SESSION["tramite"]["liqtideprop"] = $matri["idtipoidentificacion"];
                        $_SESSION["tramite"]["liqideprop"] = $matri["identificacion"];
                        $_SESSION["tramite"]["liqactprop"] = $matri["nuevosactivos"];
                    }
                }
            }


            // Encuentra casntidad de establecimientos a renovar
            $unamatest = '';
            $unaorgest = '';
            $unacatest = '';
            foreach ($_SESSION["tramite"]["expedientes"] as $matri) {
                if ($matri["organizacion"] == '02' || ($matri["organizacion"] > '02' && ($matri["categoria"] == '2' || $matri["categoria"] == '3'))) {
                    if ($matri["renovaresteano"] == 'si') {
                        $unamatest = $matri["matricula"];
                        $unaorgest = $matri["organizacion"];
                        $unacatest = $matri["categoria"];
                        $cantesttot++;
                    }
                }
            }

            // Si el propietario no renueva en el trámite, lo busca.
            if ($_SESSION["tramite"]["liqtideprop"] == '') {
                if ($unamatest != '') {
                    if ($unaorgest == '02') {
                        $prop = retornarRegistrosMysqliApi($mysqli, 'mreg_est_propietarios', "matricula='" . $unamatest . "'", "id");
                        if ($prop && !empty($prop)) {
                            foreach ($prop as $p) {
                                if ($p["estado"] == 'V') {
                                    if ($p["codigocamara"] == CODIGO_EMPRESA) {
                                        $prop1 = retornarRegistroMysqliApi($mysqli, 'mreg_est_inscritos', "matricula='" . $p["matriculapropietario"] . "'");
                                        if ($prop1 && !empty($prop1)) {
                                            $_SESSION["tramite"]["liqtideprop"] = $prop1["idclase"];
                                            $_SESSION["tramite"]["liqideprop"] = $prop1["numid"];
                                            $_SESSION["tramite"]["liqactprop"] = $prop1["acttot"];
                                        }
                                    } else {
                                        $_SESSION["tramite"]["liqtideprop"] = $p["tipoidentificacion"];
                                        $_SESSION["tramite"]["liqideprop"] = $prop1["identificacion"];
                                        $_SESSION["tramite"]["liqactprop"] = 0;
                                    }
                                }
                            }
                        }
                    } else {
                        if ($unacatest == '2' || $unacatest == '3') {
                            $est1 = retornarRegistroMysqliApi($mysqli, 'mreg_est_inscritos', "matricula='" . $unamatest . "'");
                            if ($est1 && !empty($est1)) {
                                $_SESSION["tramite"]["liqtideprop"] = '2';
                                $_SESSION["tramite"]["liqideprop"] = $est1["cpnumnit"];
                            }
                        }
                    }
                }
            }

            // Encuentra el valor de los activos del propietario a nivel nacional y
            // La cantidad de establecimientos a nivel nacional
            if ($cantesttot > 0) {
                if ($_SESSION["tramite"]["liqtideprop"] != '') {
                    $prop = \funcionesRues::consultarRegMerIdentificacionActivos($_SESSION["generales"]["codigousuario"], $_SESSION["tramite"]["liqtideprop"], $_SESSION["tramite"]["liqideprop"]);
                    if ($prop) {
                        if ($_SESSION["tramite"]["liqactprop"] == 0) {
                            $_SESSION["tramite"]["liqactprop"] = $prop["activos_totales"];
                        }
                        $_SESSION["tramite"]["liqcantesttotnal"] = $prop["establecimientos_locales"] + $prop["establecimientos_foraneos"];
                    }
                }
            }

            $msg = "(Renovacion) Datos del propietario a nivel nacional \r\n";
            $msg .= "Identificación el propietario : " . $_SESSION["tramite"]["liqtideprop"] . "-" . $_SESSION["tramite"]["liqideprop"] . "\r\n";
            $msg .= "Cantidad de establecimientos a nivel nacional : " . $_SESSION["tramite"]["liqcantesttotnal"] . "\r\n";
            $msg .= "Establecimientos/años a renovar : " . $cantesttot . "\r\n";
            $msg .= "Activos del propietario : " . $_SESSION["tramite"]["liqactprop"] . "\r\n";
            \logApi::general2($nameLog, $_SESSION["tramite"]["idliquidacion"], $msg);

            //
            if (!defined('TIPO_AFILIACION')) {
                define('TIPO_AFILIACION', 'TARIFAGENERAL');
            }

            //
            $actprin = 0;
            $actest = 0;
            if (!defined('RENOVACION_ACTIVO_MINIMO')) {
                $actmin = 0;
            } else {
                if (trim(RENOVACION_ACTIVO_MINIMO) == '') {
                    $actmin = 0;
                } else {
                    $actmin = RENOVACION_ACTIVO_MINIMO;
                }
            }

            if (!defined('RENOVACION_ACTIVO_MINIMO_ESADL')) {
                $actminesadl = 0;
            } else {
                if (trim(RENOVACION_ACTIVO_MINIMO_ESADL) == '') {
                    $actminesadl = 0;
                } else {
                    $actminesadl = RENOVACION_ACTIVO_MINIMO_ESADL;
                }
            }

            if (!defined('RENOVACION_PORCENTAJE_DISMINUCION')) {
                $porcentajedisminucion = 0;
            } else {
                if (trim(RENOVACION_PORCENTAJE_DISMINUCION) == '') {
                    $porcentajedisminucion = 0;
                } else {
                    $porcentajedisminucion = intval(RENOVACION_PORCENTAJE_DISMINUCION);
                }
            }

            $_SESSION ["generales"] ["txtemergente"] = '';
            $cantAnos = 0;
            $cantRenovar = 0;
            $arrForms = array();
            $iRenglon = 0;
            foreach ($_SESSION ["tramite"] ["expedientes"] as $matri) {
                $iRenglon++;
                $cantAnos++;

//
                if (!isset($arrForms [$matri ["matricula"]])) {
                    $arrForms [$matri ["matricula"]] = 0;
                }

//
                if ($matri ["renovaresteano"] != 'si') {
                    if ($matri ["renovaresteano"] == 'no') {
                        $finactivacion = localizarFechaActoMysqliApi($mysqli, $matri ["matricula"], '0580', 'R');
                        $freactivacion = localizarFechaActoMysqliApi($mysqli, $matri ["matricula"], '0581', 'R');
                        if ($finactivacion == '') {
                            $arrForms [$matri ["matricula"]] = 1;
                        } else {
                            if ($freactivacion != '') {
                                if ($freactivacion <= $matri ["ultimoanorenovado"] . '1231') {
                                    $arrForms [$matri ["matricula"]] = 1;
                                }
                            }
                        }
                    }
                    if ($matri ["nuevosactivos"] != 0) {
                        if ($tipoliquidacion != 'consulta') {
                            $_SESSION ["generales"] ["txtemergente"] .= 'Se indic&oacute; que el a&ntilde;o ' . $matri ["ultimoanorenovado"] . ' para la matr&iacute;cula ' . $matri ["matricula"] . ' no ser&aacute; renovado, los activos deber&aacute;n ser ceros\n';
                        } else {
                            $txtFinal .= 'Se indic&oacute; que el a&ntilde;o ' . $matri ["ultimoanorenovado"] . ' para la matr&iacute;cula ' . $matri ["matricula"] . ' no ser&aacute; renovado, los activos deber&aacute;n ser ceros<br>';
                        }
                    }
                }

                if ($tipoliquidacion != 'consulta') {
                    if ($txtFinal != '') {
                        return false;
                    }
                }

//
                if ($matri ["renovaresteano"] == 'si') {
                    if ($arrForms [$matri ["matricula"]] == 1) {
                        if ($tipoliquidacion != 'consulta') {
                            $_SESSION ["generales"] ["txtemergente"] .= 'Los a&ntilde;os a renovar de la matr&iacute;cula ' . $matri ["matricula"] . ' deben ser consecutivos (No dejar huecos - a&ntilde;os sin renovar)\n';
                        } else {
                            $txtFinal .= 'Los a&ntilde;os a renovar de la matr&iacute;cula ' . $matri ["matricula"] . ' deben ser consecutivos (No dejar huecos - a&ntilde;os sin renovar)<br>';
                        }
                    }

                    $cantRenovar++;
                    if ($matri ["ultimoanorenovado"] == date("Y")) {
                        if (($matri ["organizacion"] == '01') || (($matri ["organizacion"] > '02') && (($matri ["categoria"] == '0') || ($matri ["categoria"] == '1')))) {
                            $actprin = $actprin + $matri ["nuevosactivos"];
                        } else {
                            $actest = $actest + $matri ["nuevosactivos"];
                        }
                    }
                    if (!is_numeric($matri ["nuevosactivos"])) {
                        if ($tipoliquidacion != 'consulta') {
                            $_SESSION ["generales"] ["txtemergente"] .= 'Activos err&oacute;neos para la matr&iacute;cula ' . $matri ["matricula"] . ' en el a&ntilde;o ' . $matri ["ultimoanorenovado"] . '\n';
                        } else {
                            $txtFinal .= 'Los activos para la matr&iacute;cula ' . $matri ["matricula"] . ' en el a&ntilde;o ' . $matri ["ultimoanorenovado"] . ' no est&aacute;n correctamente digitados<br>';
                        }
                    }

//
                    $aceptavalor = RENOVACION_ACEPTAR_VALOR;
                    if ($_SESSION["generales"]["codigousuario"] == 'USUPUBXX') {
                        if (defined('RENOVACION_ACEPTAR_VALOR_USUPUBXX') && RENOVACION_ACEPTAR_VALOR_USUPUBXX != '') {
                            $aceptavalor = RENOVACION_ACEPTAR_VALOR_USUPUBXX;
                        }
                    }

//
                    if ($matri ["nuevosactivos"] < $matri ["ultimosactivos"]) {
                        $disminucionactivos = 'SI';
                    }

//
                    if ($aceptavalor == "igual") {
                        if ($matri ["nuevosactivos"] < $matri ["ultimosactivos"]) {
                            if ($_SESSION ["generales"] ["escajero"] != 'SI') {
                                if ($tipoliquidacion != 'consulta') {
                                    $_SESSION ["generales"] ["txtemergente"] .= 'Activos nuevos deben ser IGUALES O SUPERIORES a los &uacute;ltimos reportados para la matr&iacute;cula ' . $matri ["matricula"] . ' en el a&ntilde;o ' . $matri ["ultimoanorenovado"] . '\n';
                                } else {
                                    $txtFinal .= 'Los activos con los que se va a renovar la matr&iacute;cula ' . $matri ["matricula"] . ' en el a&ntilde;o ' . $matri ["ultimoanorenovado"] . ' son menores a los de la &uacute;ltima renovaci&oacute;n, por favor tenga esto en cuenta pues ';
                                    $txtFinal .= 'puede tener inconvenientes al momento de presentar su tr&aacute;mite. La C&aacute;mara podr&aacute; solicitarle la documentaci&oacute;n necesaria para justificar la disminuci&oacute;n.<br>';
                                }
                            }
                        }
                    }
                    if ($aceptavalor == "mayor") {
                        if ($matri ["nuevosactivos"] <= $matri ["ultimosactivos"]) {
                            if ($_SESSION ["generales"] ["escajero"] != 'SI') {
                                if ($tipoliquidacion != 'consulta') {
                                    $_SESSION ["generales"] ["txtemergente"] .= 'Activos nuevos deben ser SUPERIORES a los &uacute;ltimos reportados para la matr&iacute;cula ' . $matri ["matricula"] . ' en el a&ntilde;o ' . $matri ["ultimoanorenovado"] . '\n';
                                } else {
                                    $txtFinal .= 'Los activos con los que se va a renovar la matr&iacute;cula ' . $matri ["matricula"] . ' en el a&ntilde;o ' . $matri ["ultimoanorenovado"] . ' son menores a los de la &uacute;ltima renovaci&oacute;n, por favor tenga esto en cuenta pues ';
                                    $txtFinal .= 'puede tener inconvenientes al momento de presentar su tr&aacute;mite o la C&aacute;mara puede solicitarle la documentaci&oacute;n necesaria para justificar la disminuci&oacute;n.<br>';
                                }
                            }
                        }
                    }

//
                    if (trim($_SESSION ["generales"] ["txtemergente"]) == '') {
                        if ($matri ["ultimoanorenovado"] == date("Y")) {
                            if (($matri ["organizacion"] != '12') && ($matri ["organizacion"] != '14')) {
                                if ($matri ["nuevosactivos"] < $actmin) {
                                    if ($_SESSION ["generales"] ["escajero"] != 'SI') {
                                        if ($tipoliquidacion != 'consulta') {
                                            $_SESSION ["generales"] ["txtemergente"] .= 'Matr&iacute;cula No. ' . $matri ["matricula"] . ', a&ntilde;o ' . $matri ["ultimoanorenovado"] . '. Se&ntilde;or Usuario: Teniendo en cuenta el valor del activo ingresado para la renovaci&oacute;n, le sugerimos presentarse  a cualquiera de nuestras sedes para recibir informaci&oacute;n   y proceder a  efectuar  el tr&aacute;mite  respectivo.\n';
                                        } else {
                                            $txtFinal .= 'Los activos con los que se est&aacute; liquindado la renovaci&oacute;n de la matr&iacute;cula No. ' . $matri ["matricula"] . ', ';
                                            $txtFinal .= 'a&ntilde;o ' . $matri ["ultimoanorenovado"] . ', son cero o inferiores a los reportados en la &uacute;ltima renovaci&oacute;n, ';
                                            $txtFinal .= 'esto puede traerle inconvenientes al momento de realizar su tr&aacute;mite. Es posible que la C&aacute;mara ';
                                            $txtFinal .= 'le solicite informaci&oacute;n adicional para justificar este hecho.<br>';
                                        }
                                    }
                                }
                            } else {
                                if (($matri ["categoria"] == '2') || ($matri ["categoria"] == '3')) {
                                    if ($matri ["nuevosactivos"] < $actmin) {
                                        if ($_SESSION ["generales"] ["escajero"] != 'SI') {
                                            if ($tipoliquidacion != 'consulta') {
                                                $_SESSION ["generales"] ["txtemergente"] .= 'Matr&iacute;cula No. ' . $matri ["matricula"] . ', a&ntilde;o ' . $matri ["ultimoanorenovado"] . '. Se&ntilde;or Usuario: Teniendo en cuenta el valor del activo ingresado para la renovaci&oacute;n, le sugerimos presentarse  a cualquiera de nuestras sedes para recibir informaci&oacute;n   y proceder a  efectuar  el tr&aacute;mite  respectivo.\n';
                                            } else {
                                                $txtFinal .= 'Matr&iacute;cula No. ' . $matri ["matricula"] . ', a&ntilde;o ' . $matri ["ultimoanorenovado"] . '. Se&ntilde;or Usuario: Teniendo en cuenta el valor del activo ingresado para la renovaci&oacute;n, le sugerimos presentarse  a cualquiera de nuestras sedes para recibir informaci&oacute;n   y proceder a  efectuar  el tr&aacute;mite  respectivo.<br>';
                                            }
                                        }
                                    }
                                } else {
                                    if ($matri ["nuevosactivos"] < $actminesadl) {
                                        if ($_SESSION ["generales"] ["escajero"] != 'SI') {
                                            if ($tipoliquidacion != 'consulta') {
                                                $_SESSION ["generales"] ["txtemergente"] .= 'Matr&iacute;cula No. ' . $matri ["matricula"] . ', a&ntilde;o ' . $matri ["ultimoanorenovado"] . '. Se&ntilde;or Usuario: Teniendo en cuenta el valor del activo ingresado para la renovaci&oacute;n, le sugerimos presentarse  a cualquiera de nuestras sedes para recibir informaci&oacute;n   y proceder a  efectuar  el tr&aacute;mite  respectivo.\n';
                                            } else {
                                                $txtFinal .= 'Matr&iacute;cula No. ' . $matri ["matricula"] . ', a&ntilde;o ' . $matri ["ultimoanorenovado"] . '. Se&ntilde;or Usuario: Teniendo en cuenta el valor del activo ingresado para la renovaci&oacute;n, le sugerimos presentarse  a cualquiera de nuestras sedes para recibir informaci&oacute;n   y proceder a  efectuar  el tr&aacute;mite  respectivo.<br>';
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }

            if ($tipoliquidacion != 'consulta') {
                if ($_SESSION ["generales"] ["txtemergente"] != '') {
                    return false;
                }
            }

            if ($actprin > 0) {
                if ($actprin < $actest) {
                    $_SESSION ["generales"] ["txtemergente"] = 'El activo de la persona natural o sociedad principal NO DEBE ser inferior a la sumatoria de los activos de sus establecimientos de comercio\n';
                    return false;
                }
            }

            $_SESSION ["generales"] ["txtemergente"] = '';

            // Liquida el costo de la renovacion por cada matricula
            $i = 0;
            $arregloDesc = array();
            $idesc = 0;
            $tarianteriorafiliacion = 0;
            $baseanteriorafiliacion = 0;

            foreach ($_SESSION ["tramite"] ["expedientes"] as $matri) {

                if ($matri ["renovaresteano"] == 'si') {

                    $i++;
                    $_SESSION ["tramite"] ["liquidacion"] [$i] ["idsec"] = '000';
                    $_SESSION ["tramite"] ["liquidacion"] [$i] ["cc"] = $matri ["cc"];
                    $_SESSION ["tramite"] ["liquidacion"] [$i] ["expediente"] = $matri ["matricula"];
                    $_SESSION ["tramite"] ["liquidacion"] [$i] ["nombre"] = $matri ["razonsocial"];
                    $_SESSION ["tramite"] ["liquidacion"] [$i] ["organizacion"] = $matri ["organizacion"];
                    $_SESSION ["tramite"] ["liquidacion"] [$i] ["categoria"] = $matri ["categoria"];
                    $_SESSION ["tramite"] ["liquidacion"] [$i] ["afiliado"] = $matri ["afiliado"];
                    $_SESSION ["tramite"] ["liquidacion"] [$i] ["ultimoanoafiliado"] = $matri ["ultimoanoafiliado"];

                    if ($matri ["organizacion"] == '01') {
                        $_SESSION ["tramite"] ["liquidacion"] [$i] ["idservicio"] = RENOVACION_SERV_PNAT;
                        $servtarifa = $_SESSION ["tramite"] ["liquidacion"] [$i] ["idservicio"];
                        if ($_SESSION["tramite"]["reliquidacion"] == 'si' && substr($matri["fechamatricula"], 0, 4) == $matri ["ultimoanorenovado"]) {
                            $servtarifa = '01020101';
                        }
                    }
                    if (($matri ["organizacion"] == '02') && ($matri ["propietariojurisdiccion"] == 'N')) {
                        $_SESSION ["tramite"] ["liquidacion"] [$i] ["idservicio"] = RENOVACION_SERV_EST_NOJUR;
                        $servtarifa = $_SESSION ["tramite"] ["liquidacion"] [$i] ["idservicio"];
                        if ($_SESSION["tramite"]["reliquidacion"] == 'si' && substr($matri["fechamatricula"], 0, 4) == $matri ["ultimoanorenovado"]) {
                            $servtarifa = '01020103';
                        }
                    }
                    if (($matri ["organizacion"] == '02') && ($matri ["propietariojurisdiccion"] == 'S' || $matri ["propietariojurisdiccion"] == '')) {
                        $_SESSION ["tramite"] ["liquidacion"] [$i] ["idservicio"] = RENOVACION_SERV_EST_JUR;
                        $servtarifa = $_SESSION ["tramite"] ["liquidacion"] [$i] ["idservicio"];
                        if ($_SESSION["tramite"]["reliquidacion"] == 'si' && substr($matri["fechamatricula"], 0, 4) == $matri ["ultimoanorenovado"]) {
                            $servtarifa = '01020102';
                        }
                    }
                    if (($matri ["organizacion"] > '02') && ($matri ["categoria"] == '0')) {
                        $_SESSION ["tramite"] ["liquidacion"] [$i] ["idservicio"] = RENOVACION_SERV_PJUR;
                        $servtarifa = $_SESSION ["tramite"] ["liquidacion"] [$i] ["idservicio"];
                        if ($_SESSION["tramite"]["reliquidacion"] == 'si' && substr($matri["fechamatricula"], 0, 4) == $matri ["ultimoanorenovado"]) {
                            $servtarifa = '01020108';
                        }
                    }
                    if (($matri ["organizacion"] > '02') && ($matri ["categoria"] == '1')) {
                        $_SESSION ["tramite"] ["liquidacion"] [$i] ["idservicio"] = RENOVACION_SERV_PJUR;
                        $servtarifa = $_SESSION ["tramite"] ["liquidacion"] [$i] ["idservicio"];
                        if ($_SESSION["tramite"]["reliquidacion"] == 'si' && substr($matri["fechamatricula"], 0, 4) == $matri ["ultimoanorenovado"]) {
                            $servtarifa = '01020108';
                        }
                    }
                    if (($matri ["organizacion"] > '02') && ($matri ["categoria"] == '2') && ($matri ["propietariojurisdiccion"] == 'S')) {
                        $_SESSION ["tramite"] ["liquidacion"] [$i] ["idservicio"] = RENOVACION_SERV_SUC_JUR;
                        $servtarifa = $_SESSION ["tramite"] ["liquidacion"] [$i] ["idservicio"];
                        if ($_SESSION["tramite"]["reliquidacion"] == 'si' && substr($matri["fechamatricula"], 0, 4) == $matri ["ultimoanorenovado"]) {
                            $servtarifa = '01020104';
                        }
                    }
                    if (($matri ["organizacion"] > '02') && ($matri ["categoria"] == '2') && ($matri ["propietariojurisdiccion"] == 'N')) {
                        $_SESSION ["tramite"] ["liquidacion"] [$i] ["idservicio"] = RENOVACION_SERV_SUC_NOJUR;
                        $servtarifa = $_SESSION ["tramite"] ["liquidacion"] [$i] ["idservicio"];
                        if ($_SESSION["tramite"]["reliquidacion"] == 'si' && substr($matri["fechamatricula"], 0, 4) == $matri ["ultimoanorenovado"]) {
                            $servtarifa = '01020105';
                        }
                    }
                    if (($matri ["organizacion"] > '02') && ($matri ["categoria"] == '3') && ($matri ["propietariojurisdiccion"] == 'S')) {
                        $_SESSION ["tramite"] ["liquidacion"] [$i] ["idservicio"] = RENOVACION_SERV_AGE_JUR;
                        $servtarifa = $_SESSION ["tramite"] ["liquidacion"] [$i] ["idservicio"];
                        if ($_SESSION["tramite"]["reliquidacion"] == 'si' && substr($matri["fechamatricula"], 0, 4) == $matri ["ultimoanorenovado"]) {
                            $servtarifa = '01020104';
                        }
                    }
                    if (($matri ["organizacion"] > '02') && ($matri ["categoria"] == '3') && ($matri ["propietariojurisdiccion"] == 'N')) {
                        $_SESSION ["tramite"] ["liquidacion"] [$i] ["idservicio"] = RENOVACION_SERV_AGE_NOJUR;
                        $servtarifa = $_SESSION ["tramite"] ["liquidacion"] [$i] ["idservicio"];
                        if ($_SESSION["tramite"]["reliquidacion"] == 'si' && substr($matri["fechamatricula"], 0, 4) == $matri ["ultimoanorenovado"]) {
                            $servtarifa = '01020105';
                        }
                    }

                    // En caso de entidades sin Animo de Lucro
                    if (($matri ["organizacion"] == '12') || ($matri ["organizacion"] == '14')) {
                        if (($matri ["categoria"] == '0') || ($matri ["categoria"] == '1')) {
                            $_SESSION ["tramite"] ["liquidacion"] [$i] ["idservicio"] = retornarClaveValorMysqliApi($mysqli, '90.25.15');
                            $servtarifa = $_SESSION ["tramite"] ["liquidacion"] [$i] ["idservicio"];
                        }
                    }
                    $_SESSION ["tramite"] ["liquidacion"] [$i] ["ano"] = $matri ["ultimoanorenovado"];
                    $_SESSION ["tramite"] ["liquidacion"] [$i] ["cantidad"] = 1;
                    $_SESSION ["tramite"] ["liquidacion"] [$i] ["valorbase"] = $matri ["nuevosactivos"];
                    $_SESSION ["tramite"] ["liquidacion"] [$i] ["porcentaje"] = 0;
                    $_SESSION ["tramite"] ["liquidacion"] [$i] ["valorservicio"] = 0;
                    $_SESSION ["tramite"] ["liquidacion"] [$i] ["reliquidacion"] = $matri ["reliquidacion"];

                    // base para el calculo de afiliaciones anteriormente liquidadas (en reliquidaciones)
                    if ($matri ["reliquidacion"] == 'si') {
                        if ($matri ["organizacion"] == '01' || $matri ["organizacion"] > '02') {
                            if ($matri ["categoria"] == '' || $matri ["categoria"] == '0' || $matri ["categoria"] == '1' || $matri ["categoria"] == '2') {
                                $baseanteriorafiliacion = $matri ["ultimosactivos"];
                            }
                        }
                    }

                    $tari = \funcionesRegistrales::buscaTarifa($mysqli, $servtarifa, $matri ["ultimoanorenovado"], 1, $matri ["nuevosactivos"], 'tarifa', $_SESSION["tramite"]["liqactprop"], $_SESSION["tramite"]["liqcantesttotnal"]);
                    $tarianterior = \funcionesRegistrales::buscaTarifa($mysqli, $servtarifa, $matri ["ultimoanorenovado"], 1, $matri ["ultimosactivos"], 'tarifa', $_SESSION["tramite"]["liqactprop"], $_SESSION["tramite"]["liqcantesttotnal"]);
                    $okbeneficio = $matri ["benart7"];
                    $okbeneficio1780 = $matri ["benley1780"];
                    if ($_SESSION["tramite"]["reliquidacion"] != 'si') {
                        if ($okbeneficio1780 == 'S') {
                            if ($_SESSION["tramite"]["cumplorequisitosbenley1780"] == 'N' ||
                                    $_SESSION["tramite"]["mantengorequisitosbenley1780"] == 'N') {
                                $okbeneficio1780 = 'P';
                            } else {
                                if ($_SESSION["tramite"]["renunciobeneficiosley1780"] == 'S') {
                                    $okbeneficio1780 = 'R';
                                }
                            }
                        }
                    }

                    // En caso de esadl que sean veedurias personas naturales y esadl extranjeras
                    // tarifa de renovacion en cero
                    if (($matri ["organizacion"] == '12')) {
                        if (($matri ["categoria"] == '0') || ($matri ["categoria"] == '1')) {
                            // if (($matri ["claseespesadl"] == '61') || ($matri ["claseespesadl"] == '62')) {
                            if ($matri ["claseespesadl"] == '61') {
                                $tari = 0;
                                $tarianterior = 0;
                            }
                        }
                    }

                    // En caso de economia solidaria que sean cooperativas y precooperativas de trabajo asociado (49) o
                    // Ongs (extranjeras (61), cooperativas de educacion (63)
                    // tarifa de renovacion en cero
                    if (($matri ["organizacion"] == '14')) {
                        if (($matri ["categoria"] == '0') || ($matri ["categoria"] == '1')) {
                            if ($matri ["claseespesadl"] == '49' || $matri ["claseespesadl"] == '61'
                            // || $matri ["claseespesadl"] == '63'
                            ) {
                                $tari = 0;
                                $tarianterior = 0;
                            }
                        }
                    }
                    $fecmatcontrol = $matri ["fechamatricula"];
                    if (ltrim($matri ["fecmatant"], "0") != '') {
                        $fecmatcontrol = $matri ["fecmatant"];
                    }

                    // **************************************************************************** //
                    // ******** AJUSTES LEY 1780 APLICABLES PARA EL AÑO 2019 EN ADELANTE ********** //
                    // **************************************************************************** //
                    // 2018-12-21   
                    $aplico1780 = 'no';
                    $tanoren = date("Y");
                    $tanorenant = $tanoren - 1;
                    if (($_SESSION["tramite"]["cumplorequisitosbenley1780"] == 'S' && $_SESSION["tramite"]["mantengorequisitosbenley1780"] == 'S' && $_SESSION["tramite"]["renunciobeneficiosley1780"] == 'N') ||
                            $_SESSION["tramite"]["reliquidacion"] == 'si'
                    ) {
                        if ($fecmatcontrol >= $tanorenant . '0101') {
                            if ($matri ["benley1780"] == 'S') {
                                if ($_SESSION ["tramite"] ["numeroempleados"] <= 50 && $matri ["nuevosactivos"] <= $_SESSION ["generales"] ["smmlv"] * 5000) {
                                    $liquidarLey1780 = 'SI';
                                    $tari1 = $tari;
                                    $tarianterior1 = $tarianterior;
                                    if ($matri ["reliquidacion"] == 'si') {
                                        $tari1 = $tari1 - $tarianterior1;
                                    }
                                    $iley1780++;
                                    $ley1780 [$iley1780] = array();
                                    $ley1780 [$iley1780]["idsec"] = '000';
                                    $ley1780 [$iley1780]["porcentaje"] = 100;
                                    $ley1780 [$iley1780]["activos"] = $matri ["nuevosactivos"];
                                    $ley1780 [$iley1780]["cc"] = $matri ["cc"];
                                    $ley1780 [$iley1780]["expediente"] = $matri ["matricula"];
                                    $ley1780 [$iley1780]["nombre"] = $matri ["razonsocial"];
                                    $ley1780 [$iley1780]["organizacion"] = $matri ["organizacion"];
                                    $ley1780 [$iley1780]["categoria"] = $matri ["categoria"];
                                    $ley1780 [$iley1780]["valorservicio"] = $tari1;
                                    $ley1780 [$iley1780]["servicio"] = retornarClaveValorMysqliApi($mysqli, '90.27.41');
                                    $ley1780 [$iley1780]["afiliado"] = $matri ["afiliado"];
                                    $ley1780 [$iley1780]["ultimoanoafiliado"] = $matri ["ultimoanoafiliado"];
                                    $ley1780 [$iley1780]["ano"] = $matri ["ultimoanorenovado"];
                                    $ley1780 [$iley1780]["cantidad"] = 1;
                                    $ley1780 [$iley1780]["valorbase"] = $matri ["nuevosactivos"];
                                    $ley1780 [$iley1780]["reliquidacion"] = $matri ["reliquidacion"];
                                    if (!defined('RENOVACION_DEVOLUCION_DINEROS_RELIQUIDACION_CAJA') || RENOVACION_DEVOLUCION_DINEROS_RELIQUIDACION_CAJA != 'S' || $_SESSION["generales"]["codigousuario"] == 'USUPUBXX') {
                                        if ($ley1780 [$iley1780]["valorservicio"] < 0) {
                                            $ley1780 [$iley1780]["valorservicio"] = 0;
                                        }
                                    }
                                    $aplico1780 = 'si';
                                    \logApi::general2($nameLog, $_SESSION["tramite"]["idliquidacion"], 'Aplica descuento Ley 1780 - ' . $ley1780 [$iley1780]["servicio"] . ' - ' . $ley1780 [$iley1780]["valorbase"]);
                                } else {
                                    $okbeneficio1780 = 'P';
                                }
                            }
                        }
                    } else {
                        if ($_SESSION["tramite"]["expedientes"][1]["benley1780"] == 'S') {
                            if ($_SESSION["tramite"]["cumplorequisitosbenley1780"] == 'N' ||
                                    $_SESSION["tramite"]["mantengorequisitosbenley1780"] == 'N') {
                                $okbeneficio1780 = 'P';
                            } else {
                                if ($_SESSION["tramite"]["renunciobeneficiosley1780"] == 'S') {
                                    $okbeneficio1780 = 'R';
                                }
                            }
                        } else {
                            $okbeneficio1780 = 'N';
                        }
                    }

                    // **************************************************************** //
                    // 2017-12-21: JINT
                    // AJUSTE DECRETO 658 - MOCOA - 2018 
                    // **************************************************************** //
                    $aplico658 = 'no';
                    if ($aplico1780 == 'no') {
                        if (date("Y") == '2018') {
                            if ($matri["fechamatricula"] >= '20170421') {
                                $tmx = retornarRegistroMysqliApi($mysqli, 'mreg_est_inscritos', "matricula='" . $matri["matricula"] . "'");
                                if ($tmx["muncom"] == '86001' && $tmx["ctrben658"] != 'N') {
                                    $aplico658 = 'si';

                                    if (($tmx["organizacion"] == '12' && $tmx["categoria"] == '1') ||
                                            ($tmx["organizacion"] == '14' && $tmx["categoria"] == '1')) {
                                        $aplico658 = 'no';
                                    }

                                    if ($aplico658 == 'si') {
                                        $tari1 = $tari;
                                        $idesc++;
                                        $arregloDesc [$idesc] ["idsec"] = '000';
                                        $arregloDesc [$idesc] ["cc"] = $matri ["cc"];
                                        $arregloDesc [$idesc] ["expediente"] = $matri ["matricula"];
                                        $arregloDesc [$idesc] ["nombre"] = $matri ["razonsocial"];
                                        $arregloDesc [$idesc] ["organizacion"] = $matri ["organizacion"];
                                        $arregloDesc [$idesc] ["categoria"] = $matri ["categoria"];
                                        $arregloDesc [$idesc] ["afiliado"] = $matri ["afiliado"];
                                        $arregloDesc [$idesc] ["ultimoanoafiliado"] = $matri ["ultimoanoafiliado"];
                                        $arregloDesc [$idesc] ["idservicio"] = '01090151';
                                        $arregloDesc [$idesc] ["ano"] = $matri ["ultimoanorenovado"];
                                        $arregloDesc [$idesc] ["cantidad"] = 1;
                                        $arregloDesc [$idesc] ["valorbase"] = $matri ["nuevosactivos"];
                                        $arregloDesc [$idesc] ["porcentaje"] = 0;
                                        $arregloDesc [$idesc] ["valorservicio"] = $tari1 * - 1;
                                        $arregloDesc [$idesc] ["reliquidacion"] = $matri ["reliquidacion"];
                                        if ($arregloDesc [$idesc] ["valorservicio"] > 0) {
                                            $arregloDesc [$idesc] ["valorservicio"] = 0;
                                        }
                                    }
                                }
                            }
                        }
                    }

                    // 
                    if ($matri ["reliquidacion"] == 'si') {
                        $_SESSION ["tramite"] ["liquidacion"] [$i] ["valorservicio"] = $tari - $tarianterior;
                    } else {
                        $_SESSION ["tramite"] ["liquidacion"] [$i] ["valorservicio"] = $tari;
                    }
                    $_SESSION ["tramite"] ["valorbruto"] = $_SESSION ["tramite"] ["valorbruto"] + $_SESSION ["tramite"] ["liquidacion"] [$i] ["valorservicio"];
                    $_SESSION ["tramite"] ["liquidacion"] [$i] ["serviciobase"] = 'S';
                    $_SESSION ["tramite"] ["liquidacion"] [$i] ["benart7"] = $okbeneficio;
                    $_SESSION ["tramite"] ["liquidacion"] [$i] ["benaley1780"] = $okbeneficio1780;

                    // 2019-06-19: JINT: Se ajusta para evaluar el parametro RENOVACION_DEVOLUCION_DINEROS_RELIQUIDACION_CAJA
                    if (defined('RENOVACION_DEVOLUCION_DINEROS_RELIQUIDACION_CAJA') && RENOVACION_DEVOLUCION_DINEROS_RELIQUIDACION_CAJA == 'S' && $_SESSION["generales"]["codigousuario"] != 'USUPUBXX') {
                        \logApi::general2($nameLog, $_SESSION["tramite"]["idliquidacion"], 'Valor renovacion: ' . $_SESSION ["tramite"] ["liquidacion"] [$i] ["valorservicio"]);
                    } else {
                        \logApi::general2($nameLog, $_SESSION["tramite"]["idliquidacion"], 'Valor renovacion: ' . $_SESSION ["tramite"] ["liquidacion"] [$i] ["valorservicio"]);
                        if ($_SESSION ["tramite"] ["liquidacion"] [$i] ["valorservicio"] < 0) {
                            $_SESSION ["tramite"] ["liquidacion"] [$i] ["valorservicio"] = 0;
                            \logApi::general2($nameLog, $_SESSION["tramite"]["idliquidacion"], 'Redondea renovacion a cero');
                        }
                    }
                }
            }

            // *********************************************************************** //
            // Aplica descuentos de Ley 1429 solamente si se van a renovar todos los años 
            // (al dia)
            // No aplica para renovaciones parciales
            // ***********************************************************************//
            if ($idesc > 0) {
                if ($cantAnos == $cantRenovar) {
                    foreach ($arregloDesc as $dsc) {
                        $i++;
                        $_SESSION ["tramite"] ["liquidacion"] [$i] = $dsc;
                        $_SESSION ["tramite"] ["valorbruto"] = $_SESSION ["tramite"] ["valorbruto"] + $_SESSION ["tramite"] ["liquidacion"] [$i] ["valorservicio"];
                        $_SESSION ["tramite"] ["liquidacion"] [$i] ["serviciobase"] = 'N';
                        $_SESSION ["tramite"] ["liquidacion"] [$i] ["idsec"] = '000';
                    }
                }
            }
        }


        $liqtideprop = '';
        $liqideprop = '';
        $liqactprop = 0;
        $liqtotestnal = 0;
        $totest = 0;
        $simatcom = 'no';
        $simatest = 'no';

        //
        foreach ($arrTrans as $t) {
            for ($ixx = 1; $ixx <= 8; $ixx++) {
                if (trim((string) $t["maetrans"]["idservicio" . $ixx]) != '') {
                    if (in_array(trim($t["maetrans"]["idservicio" . $ixx]), $_SESSION["codigos"]["matriculascomerciantes"])) {
                        $liqactprop = $t["dattrans"]["activos"];
                        $simatcom = 'si';
                    }
                    if (in_array(trim($t["maetrans"]["idservicio" . $ixx]), $_SESSION["codigos"]["constituciones"])) {
                        $liqactprop = $t["dattrans"]["activos"];
                        $simatcom = 'si';
                    }
                    if (in_array(trim($t["maetrans"]["idservicio" . $ixx]), $_SESSION["codigos"]["aperturas"])) {
                        $totest++;
                        $simatest = 'si';
                    }
                    if (in_array(trim($t["maetrans"]["idservicio" . $ixx]), $_SESSION["codigos"]["matriculasestablecimientos"])) {
                        $totest++;
                        $simatest = 'si';
                    }
                }
            }
        }

        $liqtotestnal = $totest;

        if ($simatest == 'si') {
            if ($simatcom == 'no') {
                if ($t["dattrans"]["tipoidentificacion"] == "" || $t["dattrans"]["identificacion"] == "") {
                    $_SESSION["generales"]["txtemergente"] = 'No se reportó el tipo y número de identificación del propietario';
                    return false;
                } else {
                    $liqtideprop = $t["dattrans"]["tipoidentificacion"];
                    $liqideprop = $t["dattrans"]["identificacion"];
                    $rues = \funcionesRues::consultarRegMerIdentificacionActivos('LIQRUES', $liqtideprop, $liqideprop);
                    if ($rues) {
                        $liqactprop = $rues["activos_totales"];
                        $liqtotestnal = $rues["establecimientos_locales"] + $rues["establecimientos_foraneos"] + $liqtotestnal;
                    }
                }
            }
        }


        $msg = "(Liquidar transacciones) Datos del propietario a nivel nacional \r\n";
        $msg .= "Identificación el propietario : " . $liqtideprop . "-" . $liqideprop . "\r\n";
        $msg .= "Cantidad de establecimientos a nivel nacional : " . $liqtotestnal . "\r\n";
        $msg .= "Establecimientos/años a renovar : " . $totest . "\r\n";
        $msg .= "Activos del propietario : " . $liqactprop . "\r\n";
        \logApi::general2($nameLog, '', $msg);

        // ***********************************************************************//
        // Evalua transacciones del trámite
        // ***********************************************************************//
        $i = count($_SESSION["tramite"]["liquidacion"]);
        $_SESSION["generales"]["txtemergente"] = '';
        $txtErrores = '';

        for ($ixt = 1; $ixt <= count($arrTrans); $ixt++) {
            \logApi::general2($nameLog, $_SESSION["tramite"]["idliquidacion"], 'Entro a liquidar transaccion ' . $arrTrans[$ixt]["dattrans"]["idtransaccion"]);
            // ****************************************************************** //
            // En caso de constitución con socio único controlante
            // Que no tenga el servicio de situacion de control
            // Se adiciona a la transacción
            // ****************************************************************** //
            $incluyesitcontrol = 'NO';
            if ($arrTrans[$ixt]["dattrans"]["tipocontrolante"] == '1') {
                \logApi::general2($nameLog, $_SESSION["tramite"]["idliquidacion"], 'Encontro socio controlante unico');
                $incluyesitcontrol = 'SI-INCLUIR';
                if (trim($arrTrans[$ixt]["maetrans"]["idservicio1"]) == '01030914' ||
                        trim($arrTrans[$ixt]["maetrans"]["idservicio2"]) == '01030914' ||
                        trim($arrTrans[$ixt]["maetrans"]["idservicio3"]) == '01030914' ||
                        trim($arrTrans[$ixt]["maetrans"]["idservicio4"]) == '01030914' ||
                        trim($arrTrans[$ixt]["maetrans"]["idservicio5"]) == '01030914' ||
                        trim($arrTrans[$ixt]["maetrans"]["idservicio6"]) == '01030914' ||
                        trim($arrTrans[$ixt]["maetrans"]["idservicio7"]) == '01030914' ||
                        trim($arrTrans[$ixt]["maetrans"]["idservicio8"]) == '01030914') {
                    $incluyesitcontrol = 'SI-NOINCLUIR';
                }
                if ($incluyesitcontrol == 'SI-INCLUIR') {
                    if (trim($arrTrans[$ixt]["maetrans"]["idservicio1"]) == '') {
                        $arrTrans[$ixt]["maetrans"]["idservicio1"] = '01030914';
                        $arrTrans[$ixt]["maetrans"]["cantidad1"] = 1;
                        $arrTrans[$ixt]["maetrans"]["valorbase1"] = 'sinbase';
                        $arrTrans[$ixt]["maetrans"]["ir1"] = 'S';
                    } else {
                        if (trim($arrTrans[$ixt]["maetrans"]["idservicio2"]) == '') {
                            $arrTrans[$ixt]["maetrans"]["idservicio2"] = '01030914';
                            $arrTrans[$ixt]["maetrans"]["cantidad2"] = 1;
                            $arrTrans[$ixt]["maetrans"]["valorbase2"] = 'sinbase';
                            $arrTrans[$ixt]["maetrans"]["ir2"] = 'S';
                        } else {
                            if (trim($arrTrans[$ixt]["maetrans"]["idservicio3"]) == '') {
                                $arrTrans[$ixt]["maetrans"]["idservicio3"] = '01030914';
                                $arrTrans[$ixt]["maetrans"]["cantidad3"] = 1;
                                $arrTrans[$ixt]["maetrans"]["valorbase3"] = 'sinbase';
                                $arrTrans[$ixt]["maetrans"]["ir3"] = 'S';
                            } else {
                                if (trim($arrTrans[$ixt]["maetrans"]["idservicio4"]) == '') {
                                    $arrTrans[$ixt]["maetrans"]["idservicio4"] = '01030914';
                                    $arrTrans[$ixt]["maetrans"]["cantidad4"] = 1;
                                    $arrTrans[$ixt]["maetrans"]["valorbase4"] = 'sinbase';
                                    $arrTrans[$ixt]["maetrans"]["ir4"] = 'S';
                                } else {
                                    if (trim($t["maetrans"]["idservicio5"]) == '') {
                                        $t["maetrans"]["idservicio5"] = '01030914';
                                        $t["maetrans"]["cantidad5"] = 1;
                                        $t["maetrans"]["valorbase5"] = 'sinbase';
                                        $t["maetrans"]["ir5"] = 'S';
                                    } else {
                                        if (trim($arrTrans[$ixt]["maetrans"]["idservicio6"]) == '') {
                                            $arrTrans[$ixt]["maetrans"]["idservicio6"] = '01030914';
                                            $arrTrans[$ixt]["maetrans"]["cantidad6"] = 1;
                                            $arrTrans[$ixt]["maetrans"]["valorbase6"] = 'sinbase';
                                            $arrTrans[$ixt]["maetrans"]["ir6"] = 'S';
                                        } else {
                                            if (trim($arrTrans[$ixt]["maetrans"]["idservicio7"]) == '') {
                                                $arrTrans[$ixt]["maetrans"]["idservicio7"] = '01030914';
                                                $arrTrans[$ixt]["maetrans"]["cantidad7"] = 1;
                                                $arrTrans[$ixt]["maetrans"]["valorbase7"] = 'sinbase';
                                                $arrTrans[$ixt]["maetrans"]["ir7"] = 'S';
                                            } else {
                                                if (trim($arrTrans[$ixt]["maetrans"]["idservicio8"]) == '') {
                                                    $arrTrans[$ixt]["maetrans"]["idservicio8"] = '01030914';
                                                    $arrTrans[$ixt]["maetrans"]["cantidad8"] = 1;
                                                    $arrTrans[$ixt]["maetrans"]["valorbase8"] = 'sinbase';
                                                    $arrTrans[$ixt]["maetrans"]["ir8"] = 'S';
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
        }

        //
        foreach ($arrTrans as $t) {

            // ********************************************************************************** //
            // Valida que la transaccion tenga correctamente parametrizados los servicios
            // ********************************************************************************** //
            if (trim($t["maetrans"]["idservicio1"]) == '' &&
                    trim($t["maetrans"]["idservicio2"]) == '' &&
                    trim($t["maetrans"]["idservicio3"]) == '' &&
                    trim($t["maetrans"]["idservicio4"]) == '' &&
                    trim($t["maetrans"]["idservicio5"]) == '' &&
                    trim($t["maetrans"]["idservicio6"]) == '' &&
                    trim($t["maetrans"]["idservicio7"]) == '' &&
                    trim($t["maetrans"]["idservicio8"])) {
                $txtErrores .= "Error en la transacci&oacute;n No. " . $t["idtransaccion"] . ", no tiene parametrizados los servicios asociados.\r\n";
            }

            //
            if (trim($txtErrores) != '') {
                $_SESSION["generales"]["txtemergente"] = $txtErrores . "\r\n" . "\r\n";
                $_SESSION["generales"]["txtemergente"] .= "Por favor informe esta situaci&oacute;n al administrador del sistema.";
                return false;
            }
        }

        // ***********************************************************************//
        // Inicializa variables relacionadas con Decreto Ley 1820
        // ***********************************************************************//

        $liquidarDecreto1820 = 'NO';
        $liquidarDecreto658 = 'NO';
        $decreto1820 = array();
        $idecreto1820 = 0;
        $decreto658 = array();
        $idecreto658 = 0;

        // ******************************************************************************************   //
        // Liquida transaccion por transaccion, los servicios base, el impuesto de registro y la mora   //
        // Al igual que los beneficios de Ley 1429, Decreto 1820 y Ley 1780                             //
        // ******************************************************************************************   //
        foreach ($arrTrans as $t) {

            // 2017-05-31: Se carga el expedienmte afectado desde mreg_est_inscritos para tener disponibles en la variable $expMat el mismo en caso de
            // alguna necesidad.
            $expMat = false;
            if ($t["dattrans"]["matriculaafectada"] != 'NUEVANAT' &&
                    $t["dattrans"]["matriculaafectada"] != 'NUEVAEST' &&
                    $t["dattrans"]["matriculaafectada"] != 'NUEVAJUR' &&
                    $t["dattrans"]["matriculaafectada"] != 'NUEVAESA' &&
                    $t["dattrans"]["matriculaafectada"] != 'NUEVASUC' &&
                    $t["dattrans"]["matriculaafectada"] != 'NUEVAAGE' &&
                    $t["dattrans"]["matriculaafectada"] != 'NUEVACIV' &&
                    $t["dattrans"]["matriculaafectada"] != 'PNAT' &&
                    $t["dattrans"]["matriculaafectada"] != 'PNAT-MICRO' &&
                    $t["dattrans"]["matriculaafectada"] != 'PNAT-PEQUENA' &&
                    $t["dattrans"]["matriculaafectada"] != 'PNAT-MEDIANA' &&
                    $t["dattrans"]["matriculaafectada"] != 'PNAT-GRAN' &&
                    $t["dattrans"]["matriculaafectada"] != 'PJUR' &&
                    $t["dattrans"]["matriculaafectada"] != 'PJUR-MICRO' &&
                    $t["dattrans"]["matriculaafectada"] != 'PJUR-PEQUENA' &&
                    $t["dattrans"]["matriculaafectada"] != 'PJUR-MEDIANA' &&
                    $t["dattrans"]["matriculaafectada"] != 'PJUR-GRAN' &&
                    $t["dattrans"]["matriculaafectada"] != 'ESADL' &&
                    $t["dattrans"]["matriculaafectada"] != 'ESADL-MICRO' &&
                    $t["dattrans"]["matriculaafectada"] != 'ESADL-PEQUENA' &&
                    $t["dattrans"]["matriculaafectada"] != 'ESADL-MEDIANA' &&
                    $t["dattrans"]["matriculaafectada"] != 'ESADL-GRAN' &&
                    $t["dattrans"]["matriculaafectada"] != ''
            ) {
                $expMat = \funcionesRegistrales::retornarExpedienteMercantil($mysqli, $t["dattrans"]["matriculaafectada"]);
                if ($expMat["tamanoempresarial957codigo"] == '') {
                    $expMat["tamanoempresarial957codigo"] = \funcionesRegistrales::determinarTamanoEmpresarialCodigo($mysqli, $expMat["ciius"][1], $expMat["ingope"], $expMat["anodatos"], $expMat["fechadatos"], $expMat["fechamatricula"]);
                    \logApi::general2($nameLog, $_SESSION["tramite"]["idliquidacion"], 'Tamano empresarial calculado [' . $expMat["tamanoempresarial957codigo"] . '] Ciiu: ' . $t["dattrans"]["ciiu"] . ' - Ingresos : ' . $t["dattrans"]["ingresos"]);
                } else {
                    \logApi::general2($nameLog, $_SESSION["tramite"]["idliquidacion"], 'Tamano empresarial recuperado [' . $expMat["tamanoempresarial957codigo"] . ']');
                }
            } else {
                if ($expediente !== false) {
                    $expMat = $expediente;
                    \logApi::general2($nameLog, $_SESSION["tramite"]["idliquidacion"], 'Tamano empresarial recuperado [' . $expMat["tamanoempresarial957codigo"] . ']');
                } else {
                    $expMat = array();
                    $expMat["tamanoempresarial957codigo"] = \funcionesRegistrales::determinarTamanoEmpresarialCodigo($mysqli, $t["dattrans"]["ciiu"], $t["dattrans"]["ingresos"], date("Y"), date("Ymd"), date("Y"));
                    \logApi::general2($nameLog, $_SESSION["tramite"]["idliquidacion"], 'Tamano empresarial calculado [' . $expMat["tamanoempresarial957codigo"] . '] Ciiu: ' . $t["dattrans"]["ciiu"] . ' - Ingresos : ' . $t["dattrans"]["ingresos"]);
                }
            }
            if ($t["maetrans"]["solicitar_datostamanoempresarial"] != '' && $t["maetrans"]["solicitar_datostamanoempresarial"] != '0') {
                if ($t["maetrans"]["solicitar_datostamanoempresarial"] != $expMat["tamanoempresarial957codigo"]) {
                    $_SESSION["generales"]["txtemergente"] = "La transacción seleccionada no puede ser aplicable al expediente en cuestión, por diferencias en el tamaño empresarial<br>";
                    $_SESSION["generales"]["txtemergente"] .= "Por favor seleccione otra transacción o informe esta situaci&oacute;n al administrador del sistema.";
                    return false;
                }
            }


            $liquidarDecreto1820 = 'NO';
            $liquidarLey1780 = 'NO';
            $liquidarDecreto658 = 'NO';
            $liquidarLey2259 = 'NO';
            $valorDescuentosLey2259 = 0;

            // Determina si el expediente tiene o no beneficio de la Ley 2259 
            // asociaciones campesinas y agropecuarias
            // Que renueven a tiempo
            if ($expMat && isset($expMat["claseespesadl"]) && ($expMat["claseespesadl"] == '29' || $expMat["claseespesadl"] == '73' || $expMat["claseespesadl"] == '74' || $expMat["claseespesadl"] == '75')) {
                if ($expMat["ultanoren"] == date("Y")) {
                    if (substr($expMat["fechamatricula"], 0, 4) < date("Y")) {
                        if ($expMat["fecharenovacion"] <= $fcorte) {
                            $liquidarLey2259 = 'SI';
                        }
                    }
                }
            }


            //
            $_SESSION["tramite"]["valorimpregistro"] = 0;

            // 2016-05-14 : JINT
            // Revisa si alguna de las transacciones implica beneficio de la Ley 1780 de 2016
            // Sobre la base del concepto de matrícula mercantil
            $okbeneficio1780 = '';
            if ($t["maetrans"]["solicitar_datos1780"] == 'S') {
                if ($t["dattrans"]["benley1780"] == 'S') {
                    $okbeneficio1780 = 'S';
                    $liquidarLey1780 = 'SI';
                    $iley1780++;
                    $ley1780[$iley1780] = array();
                    $ley1780[$iley1780]["tipotramite"] = $t["maetrans"]["tipotramite"];
                    $ley1780[$iley1780]["idsec"] = $t["dattrans"]["idsecuencia"];
                    $ley1780[$iley1780]["porcentaje"] = 100;
                    if (isset($t["dattrans"]["activoscomprador"]) && $t["dattrans"]["activoscomprador"] != 0) {
                        $ley1780[$iley1780]["activos"] = $t["dattrans"]["activoscomprador"];
                    } else {
                        $ley1780[$iley1780]["activos"] = $t["dattrans"]["activos"];
                    }
                    $ley1780[$iley1780]["nombre"] = $t["dattrans"]["razonsocial"];
                    $ley1780[$iley1780]["organizacion"] = $t["dattrans"]["organizacion"];
                    $ley1780[$iley1780]["categoria"] = $t["dattrans"]["categoria"];
                    $ley1780[$iley1780]["servicio"] = '';
                    $ley1780[$iley1780]["valorservicio"] = 0;
                    if ($_SESSION["tramite"]["tipotramite"] == 'renovacionmatricula') {
                        $ley1780[$iley1780]["servicio"] = retornarClaveValorMysqliApi($mysqli, '90.27.41');
                        $servX = '01020101';
                    } else {
                        $ley1780[$iley1780]["servicio"] = retornarClaveValorMysqliApi($mysqli, '90.27.40');
                        $servX = '01020101';
                    }
                    $ley1780[$iley1780]["valorservicio"] = \funcionesRegistrales::buscaTarifa($mysqli, $servX, date("Y"), 1, $ley1780[$iley1780]["activos"], 'tarifa', $liqactprop, $liqtotestnal);
                }
            } else {
                if ($t["maetrans"]["nombrecorto"] == 'CONTRACV') {
                    if ($t["dattrans"]["benley1780"] == 'S') {
                        $okbeneficio1780 = 'S';
                        $liquidarLey1780 = 'SI';
                        $iley1780++;
                        $ley1780[$iley1780] = array();
                        $ley1780[$iley1780]["tipotramite"] = $t["maetrans"]["tipotramite"];
                        $ley1780[$iley1780]["idsec"] = $t["dattrans"]["idsecuencia"];
                        $ley1780[$iley1780]["porcentaje"] = 100;
                        $ley1780[$iley1780]["activos"] = $t["dattrans"]["activoscomprador"];
                        $ley1780[$iley1780]["nombre"] = $t["dattrans"]["razonsocial"];
                        $ley1780[$iley1780]["organizacion"] = $t["dattrans"]["organizacion"];
                        $ley1780[$iley1780]["categoria"] = $t["dattrans"]["categoria"];
                        $ley1780[$iley1780]["servicio"] = '';
                        $ley1780[$iley1780]["valorservicio"] = 0;
                        if ($_SESSION["tramite"]["tipotramite"] == 'renovacionmatricula') {
                            $ley1780[$iley1780]["servicio"] = retornarClaveValorMysqliApi($mysqli, '90.27.41');
                            $servX = '01020101';
                        } else {
                            $ley1780[$iley1780]["servicio"] = retornarClaveValorMysqliApi($mysqli, '90.27.40');
                            $servX = '01020101';
                        }
                        $ley1780[$iley1780]["valorservicio"] = \funcionesRegistrales::buscaTarifa($mysqli, $servX, date("Y"), 1, $ley1780[$iley1780]["activos"], 'tarifa', $liqactprop, $liqtotestnal);
                    }
                }
            }

            // 2015-12-17 : JINT
            // Revisa si alguna de las transacciones implica beneficio del decreto 1820 de 2015
            // Sobre la base del concepto de matrícula mercantil
            if ($liquidarLey1780 == 'NO') {
                if ($t["maetrans"]["solicitar_datos1820"] == 'S') {
                    $xMun = '';
                    if ($t["dattrans"]["municipio"] != '') {
                        $xMun = $t["dattrans"]["municipio"];
                    } else {
                        if ($t["dattrans"]["mundoc"] != '') {
                            $xMun = $t["dattrans"]["mundoc"];
                        }
                    }
                    if ($xMun != '') {
                        $arrTemY = retornarRegistroMysqliApi($mysqli, 'mreg_municipiosjurisdiccion', "idcodigo='" . $xMun . "'");
                        if ($arrTemY && $arrTemY["excencionmatricula"] != 0) {
                            $liquidarDecreto1820 = 'SI';
                            $idecreto1820++;
                            $decreto1820[$idecreto1820] = array();
                            $decreto1820[$idecreto1820]["idsec"] = $t["dattrans"]["idsecuencia"];
                            $decreto1820[$idecreto1820]["porcentaje"] = $arrTemY["excencionmatricula"];
                            $decreto1820[$idecreto1820]["activos"] = $t["dattrans"]["activos"];
                            $decreto1820[$idecreto1820]["nombre"] = $t["dattrans"]["razonsocial"];
                            $decreto1820[$idecreto1820]["organizacion"] = $t["dattrans"]["organizacion"];
                            $decreto1820[$idecreto1820]["categoria"] = $t["dattrans"]["categoria"];
                            $decreto1820[$idecreto1820]["servicio"] = '';
                            $decreto1820[$idecreto1820]["valorservicio"] = 0;
                            if ($t["dattrans"]["organizacion"] == '01') {
                                $decreto1820[$idecreto1820]["servicio"] = retornarClaveValorMysqliApi($mysqli, '90.27.30');
                                $servX = '01020101';
                            }
                            if ($t["dattrans"]["organizacion"] == '02') {
                                $decreto1820[$idecreto1820]["servicio"] = retornarClaveValorMysqliApi($mysqli, '90.27.32');
                                $servX = '01020102';
                            }
                            if ($t["dattrans"]["organizacion"] > '02' && ($t["dattrans"]["categoria"] == '2' || $t["dattrans"]["categoria"] == '3')) {
                                $decreto1820[$idecreto1820]["servicio"] = retornarClaveValorMysqliApi($mysqli, '90.27.32');
                                $regx = retornarRegistroMysqliApi($mysqli, 'mreg_municipiosjurisdiccion', "idcodigo='" . $t["dattrans"]["mundoc"] . "'");
                                if ($regx && !empty($regx)) {
                                    $servX = '01020102';
                                } else {
                                    $servX = '01020105';
                                }
                            }

                            if ($t["dattrans"]["organizacion"] > '02' && ($t["dattrans"]["categoria"] == '0' || $t["dattrans"]["categoria"] == '1')) {
                                $decreto1820[$idecreto1820]["servicio"] = retornarClaveValorMysqliApi($mysqli, '90.27.31');
                                $servX = '01020101';
                            }
                            $decreto1820[$idecreto1820]["valorservicio"] = \funcionesRegistrales::buscaTarifa($mysqli, $servX, date("Y"), 1, $decreto1820[$idecreto1820]["activos"], 'tarifa', $liqactprop, $liqtotestnal);
                        }
                    }
                }
            }

            // 2017-12-23 : JINT 
            // Aplica decreto 658 - emergencia - mocoa
            // Sobre la base del concepto de matrícula mercantil
            // Solo para constituciones y matrículas
            if ($liquidarLey1780 == 'NO') {
                if (date("Ymd") >= '20170421' && date("Ymd") <= '20181231') {
                    if ($t["maetrans"]["tipotramite"] == 'constitucionpjur' ||
                            $t["maetrans"]["tipotramite"] == 'matriculapnat' ||
                            $t["maetrans"]["tipotramite"] == 'matriculaest' ||
                            $t["maetrans"]["tipotramite"] == 'matriculasuc' ||
                            $t["maetrans"]["tipotramite"] == 'matriculaage') {
                        if ($t["dattrans"]["municipio"] == '86001') {
                            $liquidarDecreto658 = 'SI';
                            $idecreto658++;
                            $decreto658[$idecreto658] = array();
                            $decreto658[$idecreto658]["idsec"] = $t["dattrans"]["idsecuencia"];
                            $decreto658[$idecreto658]["porcentaje"] = $arrTemY["excencionmatricula"];
                            $decreto658[$idecreto658]["activos"] = $t["dattrans"]["activos"];
                            $decreto658[$idecreto658]["nombre"] = $t["dattrans"]["razonsocial"];
                            $decreto658[$idecreto658]["organizacion"] = $t["dattrans"]["organizacion"];
                            $decreto658[$idecreto658]["categoria"] = $t["dattrans"]["categoria"];
                            $decreto658[$idecreto658]["servicio"] = '';
                            $decreto658[$idecreto658]["valorservicio"] = 0;
                            if ($t["dattrans"]["organizacion"] == '01') {
                                $decreto658[$idecreto658]["servicio"] = '01090150';
                                $servX = '01020101';
                            }
                            if ($t["dattrans"]["organizacion"] == '02' || $t["dattrans"]["categoria"] == '2' || $t["dattrans"]["categoria"] == '3') {
                                $decreto658[$idecreto658]["servicio"] = '01090150';
                                $regx = retornarRegistroMysqliApi($mysqli, 'mreg_municipiosjurisdiccion', "idcodigo='" . $t["dattrans"]["mundoc"] . "'");
                                if ($regx && !empty($regx)) {
                                    $servX = '01020102';
                                } else {
                                    $servX = '01020105';
                                }
                            }
                            if ($t["dattrans"]["organizacion"] > '02' && $t["dattrans"]["categoria"] == '1') {
                                $decreto658[$idecreto658]["servicio"] = '01090150';
                                $servX = '01020101';
                            }

                            $decreto658[$idecreto658]["valorservicio"] = \funcionesRegistrales::buscaTarifa($mysqli, $servX, date("Y"), 1, $decreto658[$idecreto658]["activos"], 'tarifa', $liqactprop, $liqtotestnal);
                        }
                    }
                }
            }

            // ********************************************************************************************** //
            // Encuentra valores y porcentajes del impuesto de registro
            // ********************************************************************************************** //
            $v_regmer_sincuantia = doubleval(retornarClaveValorMysqliApi($mysqli, '90.27.81')); // Valor registro IR - Regmer - Sin cuantia
            if ($t["maetrans"]["solicitar_datosconstitucion"] == 'S') { // En caso de constituciones
                $v_regmer_cuantia = doubleval(retornarClaveValorMysqliApi($mysqli, '90.27.79')); // % registro IR - Regmer - Con cuantia - Constituciones   
                if ($expMat["tamanoempresarial957codigo"] == '1') {
                    $v_regmer_cuantia = doubleval(retornarClaveValorMysqliApi($mysqli, '90.27.90')); // % registro IR - Regmer - Con cuantia - Constituciones - mipymes
                    if ($v_regmer_cuantia == 0) {
                        $v_regmer_cuantia = doubleval(retornarClaveValorMysqliApi($mysqli, '90.27.79')); // % registro IR - Regmer - Con cuantia - Constituciones   
                    }
                }
                if ($v_regmer_cuantia == 0) {
                    $v_regmer_cuantia = doubleval(retornarClaveValorMysqliApi($mysqli, '90.27.80')); // % registro IR - Regmer - Con cuantia - Otros
                    if ($expMat["tamanoempresarial957codigo"] == '1') {
                        $v_regmer_cuantia = doubleval(retornarClaveValorMysqliApi($mysqli, '90.27.91')); // % registro IR - Regmer - Con cuantia - otros - mipymes
                        if ($v_regmer_cuantia == 0) {
                            $v_regmer_cuantia = doubleval(retornarClaveValorMysqliApi($mysqli, '90.27.80')); // % registro IR - Regmer - Con cuantia - Otros
                        }
                    }
                }
            } else {
                if ($t["maetrans"]["idtipotransaccion"] == '029') { // En caso de prima en colocacion de acciones
                    $v_regmer_cuantia = doubleval(retornarClaveValorMysqliApi($mysqli, '90.27.78')); // % registro IR - Regmer - Con cuantia - Constituciones   
                    if ($expMat["tamanoempresarial957codigo"] == '1') {
                        $v_regmer_cuantia = doubleval(retornarClaveValorMysqliApi($mysqli, '90.27.94')); // % registro IR - Regmer - Con cuantia - Constituciones - mipymes
                        if ($v_regmer_cuantia == 0) {
                            $v_regmer_cuantia = doubleval(retornarClaveValorMysqliApi($mysqli, '90.27.78')); // % registro IR - Regmer - Con cuantia - Constituciones   
                        }
                    }
                    if ($v_regmer_cuantia == 0) {
                        $v_regmer_cuantia = doubleval(retornarClaveValorMysqliApi($mysqli, '90.27.80')); // % registro IR - Regmer - Con cuantia - Otros
                        if ($expMat["tamanoempresarial957codigo"] == '1') {
                            $v_regmer_cuantia = doubleval(retornarClaveValorMysqliApi($mysqli, '90.27.91')); // % registro IR - Regmer - Con cuantia - otros - mipymes
                            if ($v_regmer_cuantia == 0) {
                                $v_regmer_cuantia = doubleval(retornarClaveValorMysqliApi($mysqli, '90.27.80')); // % registro IR - Regmer - Con cuantia - Otros
                            }
                        }
                    }
                } else { // Otros
                    $v_regmer_cuantia = doubleval(retornarClaveValorMysqliApi($mysqli, '90.27.80')); // % registro IR - Regmer - Con cuantia - Otros
                    if ($expMat["tamanoempresarial957codigo"] == '1') {
                        $v_regmer_cuantia = doubleval(retornarClaveValorMysqliApi($mysqli, '90.27.91')); // % registro IR - Regmer - Con cuantia - otros - mipymes
                        if ($v_regmer_cuantia == 0) {
                            $v_regmer_cuantia = doubleval(retornarClaveValorMysqliApi($mysqli, '90.27.80')); // % registro IR - Regmer - Con cuantia - Otros
                        }
                    }
                }
            }

            //
            $v_regmer_cuantiaD = doubleval(retornarClaveValorMysqliApi($mysqli, '90.27.78')); // % registro IR - Regmer - Con cuantia - Tarifa especial similar a constituciones  
            //
            $v_regesadl_sincuantia = doubleval(retornarClaveValorMysqliApi($mysqli, '90.27.84')); // Valor registro IR - regEsadl - Sin cuant&iacute;a
            if ($t["maetrans"]["solicitar_datosconstitucion"] == 'S' || $t["maetrans"]["idtipotransaccion"] == '029') {
                $v_regesadl_cuantia = doubleval(retornarClaveValorMysqliApi($mysqli, '90.27.82')); // % registro IR - RegEsadl - Con cuantia - Constituciones    
                if ($expMat["tamanoempresarial957codigo"] == '1') {
                    $v_regesadl_cuantia = doubleval(retornarClaveValorMysqliApi($mysqli, '90.27.92')); // % registro IR - Regmer - Con cuantia - Constituciones - mipymes
                    if ($v_regesadl_cuantia == 0) {
                        $v_regesadl_cuantia = doubleval(retornarClaveValorMysqliApi($mysqli, '90.27.82')); // % registro IR - RegEsadl - Con cuantia - Constituciones    
                    }
                }
                if ($v_regesadl_cuantia == 0) {
                    $v_regesadl_cuantia = doubleval(retornarClaveValorMysqliApi($mysqli, '90.27.83')); // % registro IR - RegEsadl - Con cuantia
                    if ($expMat["tamanoempresarial957codigo"] == '1') {
                        $v_regesadl_cuantia = doubleval(retornarClaveValorMysqliApi($mysqli, '90.27.93')); // % registro IR - Regmer - Con cuantia - otros - mipymes
                        if ($v_regesadl_cuantia == 0) {
                            $v_regesadl_cuantia = doubleval(retornarClaveValorMysqliApi($mysqli, '90.27.83')); // % registro IR - RegEsadl - Con cuantia
                        }
                    }
                }
            } else {
                $v_regesadl_cuantia = doubleval(retornarClaveValorMysqliApi($mysqli, '90.27.83')); // % registro IR - RegEsadl - Con cuantia
                if ($expMat["tamanoempresarial957codigo"] == '1') {
                    $v_regesadl_cuantia = doubleval(retornarClaveValorMysqliApi($mysqli, '90.27.93')); // % registro IR - Regmer - Con cuantia - otros - mipymes
                    if ($v_regesadl_cuantia == 0) {
                        $v_regesadl_cuantia = doubleval(retornarClaveValorMysqliApi($mysqli, '90.27.83')); // % registro IR - RegEsadl - Con cuantia
                    }
                }
            }

            //
            $v_regesadl_cuantiaD = doubleval(retornarClaveValorMysqliApi($mysqli, '90.27.85')); // % registro IR - RegEsadl - Con cuantia - Tarifa especial similar a constituciones 
            $vm_regmer_sincuantia = doubleval(retornarClaveValorMysqliApi($mysqli, '90.27.87')); // % mora IR - RegMer - sin cuantia
            \logApi::general2($nameLog, $_SESSION["tramite"]["idliquidacion"], 'vm_regmer_sincuantia ' . $vm_regmer_sincuantia);
            $vm_regmer_cuantia = doubleval(retornarClaveValorMysqliApi($mysqli, '90.27.86')); // % mora IR - RegMer - con cuantia
            \logApi::general2($nameLog, $_SESSION["tramite"]["idliquidacion"], 'vm_regmer_cuantia ' . $vm_regmer_cuantia);
            $vm_regesadl_sincuantia = doubleval(retornarClaveValorMysqliApi($mysqli, '90.27.89')); // % mora IR - RegEsadl - sin cuantia
            \logApi::general2($nameLog, $_SESSION["tramite"]["idliquidacion"], 'vm_regesadl_sincuantia ' . $vm_regesadl_sincuantia);
            $vm_regesadl_cuantia = doubleval(retornarClaveValorMysqliApi($mysqli, '90.27.88')); // % mora IR - RegEsadl - con cuantia
            \logApi::general2($nameLog, $_SESSION["tramite"]["idliquidacion"], 'vm_regesadl_cuantia ' . $vm_regesadl_cuantia);

            //
            $arrIR = retornarRegistroMysqliApi($mysqli, 'mreg_impregistro', "codigomunicipio='" . $t["dattrans"]["mundoc"] . "'");
            if ($arrIR && !empty($arrIR)) {
                if ($liquidarIR == 'SI' && $arrIR["aplica"] == 'SI') {
                    $liquidarIR = 'NO';
                }
                $v_regmer_sincuantia = $arrIR["valorimpregsincuarmer"]; // Valor registro IR - Regmer - Sin cuantia
                //
                if ($t["maetrans"]["solicitar_datosconstitucion"] == 'S') {
                    $v_regmer_cuantia = $arrIR["porcimpregconcuarmer"]; // Concstitucion
                    if ($expMat["tamanoempresarial957codigo"] == '1') {
                        $v_regmer_cuantia = $arrIR["porcimpregconcuarmermipymes"]; // % registro IR - Regmer - Con cuantia - Constituciones - mipymes
                    }
                    if ($v_regmer_cuantia == 0) {
                        $v_regmer_cuantia = $arrIR["porcimpregconcuarmer"]; // % registro IR - Regmer - Con cuantia - Tartifa constitucion
                    }
                } else {
                    if ($t["maetrans"]["idtipotransaccion"] == '029') { // prima colocacion de acciones
                        $v_regmer_cuantia = $arrIR["porcimpregconcuarmerdiferencial"]; // Concstitucion
                        if ($expMat["tamanoempresarial957codigo"] == '1') {
                            $v_regmer_cuantia = $arrIR["porcimpregconcuarmerdiferencialmipymes"];
                        }
                        if ($v_regmer_cuantia == 0) {
                            $v_regmer_cuantia = $arrIR["porcimpregconcuarmer"]; // % registro IR - Regmer - Con cuantia - Tartifa constitucion
                        }
                    } else {
                        $v_regmer_cuantia = $arrIR["porcimpregconcuarmerotros"]; // % registro IR - Regmer - Con cuantia  - Tarifa estandard
                        if ($expMat["tamanoempresarial957codigo"] == '1') {
                            $v_regmer_cuantia = $arrIR["porcimpregconcuarmerotrosmipymes"]; // % registro IR - Regmer - Con cuantia - Constituciones - mipymes
                        }
                        if ($v_regmer_cuantia == 0) {
                            $v_regmer_cuantia = $arrIR["porcimpregconcuarmerotros"]; // % registro IR - Regmer - Con cuantia  - Tarifa estandard
                        }
                    }
                }
                $v_regmer_cuantiaD = $arrIR["porcimpregconcuarmerdiferencial"]; // % registro IR - Regmer - Con cuantia - Especial
                //
                $v_regesadl_sincuantia = $arrIR["valorimpregsincuaesadl"]; // Valor registro IR - regEsadl - Sin cuantia
                if ($t["maetrans"]["solicitar_datosconstitucion"] == 'S' || $t["maetrans"]["idtipotransaccion"] == '029') {
                    $v_regesadl_cuantia = $arrIR["porcimpregconcuaesadl"]; // % registro IR - RegEsadl - Con cuantia - Tarifa constitucion
                    if ($expMat["tamanoempresarial957codigo"] == '1') {
                        $v_regesadl_cuantia = $arrIR["porcimpregconcuaesadlmipymes"]; // % registro IR - Regmer - Con cuantia - Constituciones - mipymes
                        if ($v_regesadl_cuantia == 0) {
                            $v_regesadl_cuantia = $arrIR["porcimpregconcuaesadl"]; // % registro IR - RegEsadl - Con cuantia - Tarifa constitucion
                        }
                    }
                } else {
                    $v_regesadl_cuantia = $arrIR["porcimpregconcuaesadlotros"]; // % registro IR - RegEsadl - Con cuantia - Tarifa estandard
                    if ($expMat["tamanoempresarial957codigo"] == '1') {
                        $v_regesadl_cuantia = $arrIR["porcimpregconcuaesadlotrosmipymes"]; // % registro IR - Regmer - Con cuantia - Constituciones - mipymes
                        if ($v_regesadl_cuantia == 0) {
                            $v_regesadl_cuantia = $arrIR["porcimpregconcuaesadlotros"]; // % registro IR - RegEsadl - Con cuantia - Tarifa estandard
                        }
                    }
                }
                $v_regesadl_cuantiaD = $arrIR["porcimpregconcuaesadldiferencial"]; // % registro IR - RegEsadl - Con cuantia - Especial
                $vm_regmer_sincuantia = $arrIR["porcmoraimpregrmer"]; // % mora IR - RegMer - sin cuantia
                $vm_regmer_cuantia = $arrIR["porcmoraimpregrmer"]; // % mora IR - RegMer - con cuantia
                $vm_regesadl_sincuantia = $arrIR["porcmoraimpregesadl"]; // % mora IR - RegEsadl - sin cuantia
                $vm_regesadl_cuantia = $arrIR["porcmoraimpregesadl"]; // % mora IR - RegEsadl - con cuantia
            }



            for ($ixSer = 1; $ixSer <= 8; $ixSer++) {
                $controlliquidacion = 'si';
                $contServ = $t["maetrans"]["idservicio" . $ixSer];
                $evalServ = $t["maetrans"]["condicionservicio" . $ixSer];
                $irServ = $t["maetrans"]["ir" . $ixSer];
                if ($contServ == '') {
                    $controlliquidacion = 'no';
                }
                if ($controlliquidacion == 'si') {
                    if ($evalServ != '') {
                        eval($evalServ);
                    }
                }

                //
                if ($controlliquidacion == 'si') {
                    if ($contServ != '') {
                        $serv = $contServ;
                        $temServ = retornarRegistroMysqliApi($mysqli, 'mreg_servicios', "idservicio='" . $serv . "'");
                        if ($t["dattrans"]["acreditapagoir"] == 'N' || $t["dattrans"]["acreditapagoir"] == '' || ($t["dattrans"]["acreditapagoir"] == 'S' && ltrim($temServ["conceptodepartamental"], "0") == '')) {
                            $i++;
                            $j = $i;
                            $arrValor = \funcionesRegistrales::sumarServicio($mysqli, $t["dattrans"]["idsecuencia"], $serv, $t, $arrServs, $temServ, $ixSer, '', $fcorte, $liqactprop, $liqtotestnal);
                            $valor = \funcionesRegistrales::redondearServicio($mysqli, $serv, $arrValor["valorservicio"]);
                            $arrValor["valorservicio"] = $valor;
                            $_SESSION["tramite"]["liquidacion"][$i] = $arrValor;
                            \logApi::general2($nameLog, $_SESSION["tramite"]["idliquidacion"], 'Liquido servicio ' . $serv);

                            if ($liquidarLey2259 == 'SI') {
                                if ($temServ["tipoingreso"] == '14' || $temServ["tipoingreso"] == '15' || $temServ["tipoingreso"] == '17' || $temServ["tipoingreso"] == '20') {
                                    $valorDescuentosLey2259 = $valorDescuentosLey2259 + $valor;
                                }
                            }
                        }


                        // 2018-08-22: JINT: En caso de constitución y siempre y cuando sea el primer servicio y
                        // se deba liquidar impuesto de registro, y el parámetro CONSTITUCION_1780_IMPUESTO_REGISTRO esté en N
                        $primerimpuesto = 'si';
                        if ($ixSer == 1) {
                            if (defined('CONSTITUCION_1780_IMPUESTO_REGISTRO') && CONSTITUCION_1780_IMPUESTO_REGISTRO == 'N') {
                                if ($liquidarLey1780 == 'SI') {
                                    $primerimpuesto = 'no';
                                }
                            } else {
                                if (isset($arrIR) && !empty($arrIR)) {
                                    if (isset($arrIR["aplica1780"]) && $arrIR["aplica1780"] == 'SI') {
                                        if ($liquidarLey1780 == 'SI') {
                                            $primerimpuesto = 'no';
                                        }
                                    }
                                }
                            }
                        }
                        if ($primerimpuesto == 'no') {
                            \logApi::general2($nameLog, $_SESSION["tramite"]["idliquidacion"], 'No aplicara primer impuesto por condicion de Ley 1780');
                        }
                        if ($primerimpuesto == 'si') {
                            if ($t["dattrans"]["acreditapagoir"] == 'N' || $t["dattrans"]["acreditapagoir"] == '') {
                                if ($liquidarIR == 'SI' && ($irServ == 'S' || $irServ == 'C' || $irServ == 'D' || is_numeric($irServ))) {
                                    \logApi::general2($nameLog, $_SESSION["tramite"]["idliquidacion"], 'Liquidara impuesto de registro');
                                    //
                                    $servicioorigenmora = '';
                                    if ($irServ == 'S') {
                                        $porccapub = 0;
                                        if ($expMat && $expMat["cap_porcnalpub"] != 0) {
                                            $porccapub = $expMat["cap_porcnalpub"];
                                        }
                                        $limitecapitalpublicocobroimpuestosincuantia = doubleval(retornarClaveValorMysqliApi($mysqli, '90.01.36')); // % Limite minimo capital publico para cobro I.R sin cuantía
                                        if ($porccapub == 0 || $limitecapitalpublicocobroimpuestosincuantia >= $porccapub) {
                                        // if ($porccapub == 0 || ($porccapub > 0 && $porccapub < 100)) {
                                            $i++;
                                            $j1 = $i;
                                            if ($t["dattrans"]["organizacion"] != '12' && $t["dattrans"]["organizacion"] != '14') {
                                                $valor = round($_SESSION["tramite"]["liquidacion"][$j]["cantidad"] * $v_regmer_sincuantia, -2);
                                                $_SESSION["tramite"]["valorimpregistro"] = $_SESSION["tramite"]["valorimpregistro"] + $valor;
                                                $_SESSION["tramite"]["liquidacion"][$i] = \funcionesRegistrales::sumarServicioGeneral($mysqli, $t["dattrans"]["idsecuencia"], $sir_regmer_sincuantia, $_SESSION["tramite"]["liquidacion"][$j]["expediente"], $_SESSION["tramite"]["liquidacion"][$j]["nombre"], date("Y"), $_SESSION["tramite"]["liquidacion"][$j]["cantidad"], $_SESSION["tramite"]["liquidacion"][$j]["valorbase"], 0, $valor, 'N', 'N', 'N', 'N', 'S', 'N', $liqactprop, $liqtotestnal, $serv, 0);
                                                $servicioorigenmora = $sir_regmer_sincuantia;
                                            } else {
                                                $valor = round($_SESSION["tramite"]["liquidacion"][$j]["cantidad"] * $v_regesadl_sincuantia, -2);
                                                $_SESSION["tramite"]["valorimpregistro"] = $_SESSION["tramite"]["valorimpregistro"] + $valor;
                                                $_SESSION["tramite"]["liquidacion"][$i] = \funcionesRegistrales::sumarServicioGeneral($mysqli, $t["dattrans"]["idsecuencia"], $sir_regesadl_sincuantia, $_SESSION["tramite"]["liquidacion"][$j]["expediente"], $_SESSION["tramite"]["liquidacion"][$j]["nombre"], date("Y"), $_SESSION["tramite"]["liquidacion"][$j]["cantidad"], $_SESSION["tramite"]["liquidacion"][$j]["valorbase"], 0, $valor, 'N', 'N', 'N', 'N', 'S', 'N', $liqactprop, $liqtotestnal, $serv, 0);
                                                $servicioorigenmora = $sir_regesadl_sincuantia;
                                            }
                                            \logApi::general2($nameLog, $_SESSION["tramite"]["idliquidacion"], 'Liquido I.R sin cuantia para el servicio ' . $serv);
                                        }
                                    }

                                    //
                                    if ($irServ == 'C' || $irServ == 'D') {                                        
                                        if ($t["dattrans"]["pornaltot"] + $t["dattrans"]["porexttot"] == 0) {
                                            $t["dattrans"]["pornalpri"] = 100;
                                        }
                                        if ($irServ == 'C') {
                                            $vimp = $v_regmer_cuantia;
                                            $vimpEsadl = $v_regesadl_cuantia;
                                        }
                                        if ($irServ == 'D') {
                                            $vimp = $v_regmer_cuantiaD;
                                            $vimpEsadl = $v_regesadl_cuantiaD;
                                        }
                                        if ($expMat["tamanoempresarial957codigo"] == '1') {
                                            $sir_regmer_cuantia = $sir_regmer_cuantia_mipyme;
                                            $sir_regesadl_cuantia = $sir_regesadl_cuantia_mipyme;
                                        }
                                        $i++;
                                        $j1 = $i;
                                        if ($t["dattrans"]["organizacion"] != '12' && $t["dattrans"]["organizacion"] != '14') {
                                            $valor = \funcionesRegistrales::redondearServicio($mysqli, $sir_regmer_cuantia, $_SESSION["tramite"]["liquidacion"][$j]["valorbase"] * ($t["dattrans"]["pornalpri"] + $t["dattrans"]["porextpri"]) / 100 * $vimp / 100);
                                            $_SESSION["tramite"]["valorimpregistro"] = $_SESSION["tramite"]["valorimpregistro"] + $valor;
                                            $_SESSION["tramite"]["liquidacion"][$i] = \funcionesRegistrales::sumarServicioGeneral($mysqli, $t["dattrans"]["idsecuencia"], $sir_regmer_cuantia, $_SESSION["tramite"]["liquidacion"][$j]["expediente"], $_SESSION["tramite"]["liquidacion"][$j]["nombre"], date("Y"), $_SESSION["tramite"]["liquidacion"][$j]["cantidad"], $_SESSION["tramite"]["liquidacion"][$j]["valorbase"], $vimp, $valor, 'N', 'N', 'N', 'N', 'S', 'N', $liqactprop, $liqtotestnal, $serv, 0);
                                            $servicioorigenmora = $sir_regmer_cuantia;
                                        } else {
                                            $valor = \funcionesRegistrales::redondearServicio($mysqli, $sir_regesadl_cuantia, $_SESSION["tramite"]["liquidacion"][$j]["valorbase"] * ($t["dattrans"]["pornalpri"] + $t["dattrans"]["porextpri"]) / 100 * $vimpEsadl / 100);
                                            $_SESSION["tramite"]["valorimpregistro"] = $_SESSION["tramite"]["valorimpregistro"] + $valor;
                                            $_SESSION["tramite"]["liquidacion"][$i] = \funcionesRegistrales::sumarServicioGeneral($mysqli, $t["dattrans"]["idsecuencia"], $sir_regesadl_cuantia, $_SESSION["tramite"]["liquidacion"][$j]["expediente"], $_SESSION["tramite"]["liquidacion"][$j]["nombre"], date("Y"), $_SESSION["tramite"]["liquidacion"][$j]["cantidad"], $_SESSION["tramite"]["liquidacion"][$j]["valorbase"], $v_regesadl_cuantia, $valor, 'N', 'N', 'N', 'N', 'S', 'N', $liqactprop, $liqtotestnal, $serv, 0);
                                            $servicioorigenmora = $sir_regesadl_cuantia;
                                        }
                                        \logApi::general2($nameLog, $_SESSION["tramite"]["idliquidacion"], 'Liquido I.R con cuantia para el servicio ' . $serv);
                                    }

                                    //
                                    if (is_numeric($irServ)) {
                                        if ($t["dattrans"]["pornaltot"] + $t["dattrans"]["porexttot"] == 0) {
                                            $t["dattrans"]["pornalpri"] = 100;
                                        }
                                        $vimp = doubleval($irServ);
                                        if ($expMat["tamanoempresarial957codigo"] == '1') {
                                            $sir_regmer_cuantia = $sir_regmer_cuantia_mipyme;
                                            $sir_regesadl_cuantia = $sir_regesadl_cuantia_mipyme;
                                        }
                                        $i++;
                                        $j1 = $i;
                                        if ($t["dattrans"]["organizacion"] != '12' && $t["dattrans"]["organizacion"] != '14') {
                                            $valor = \funcionesRegistrales::redondearServicio($mysqli, $sir_regmer_cuantia, $_SESSION["tramite"]["liquidacion"][$j]["valorbase"] * ($t["dattrans"]["pornalpri"] + $t["dattrans"]["porextpri"]) / 100 * $vimp / 100);
                                            $_SESSION["tramite"]["valorimpregistro"] = $_SESSION["tramite"]["valorimpregistro"] + $valor;
                                            $_SESSION["tramite"]["liquidacion"][$i] = \funcionesRegistrales::sumarServicioGeneral($mysqli, $t["dattrans"]["idsecuencia"], $sir_regmer_cuantia, $_SESSION["tramite"]["liquidacion"][$j]["expediente"], $_SESSION["tramite"]["liquidacion"][$j]["nombre"], date("Y"), $_SESSION["tramite"]["liquidacion"][$j]["cantidad"], $_SESSION["tramite"]["liquidacion"][$j]["valorbase"], $vimp, $valor, 'N', 'N', 'N', 'N', 'S', 'N', $liqactprop, $liqtotestnal, $serv, 0);
                                            $servicioorigenmora = $sir_regmer_cuantia;
                                        } else {
                                            $valor = \funcionesRegistrales::redondearServicio($mysqli, $sir_regesadl_cuantia, $_SESSION["tramite"]["liquidacion"][$j]["valorbase"] * ($t["dattrans"]["pornalpri"] + $t["dattrans"]["porextpri"]) / 100 * $vimp / 100);
                                            $_SESSION["tramite"]["valorimpregistro"] = $_SESSION["tramite"]["valorimpregistro"] + $valor;
                                            $_SESSION["tramite"]["liquidacion"][$i] = \funcionesRegistrales::sumarServicioGeneral($mysqli, $t["dattrans"]["idsecuencia"], $sir_regesadl_cuantia, $_SESSION["tramite"]["liquidacion"][$j]["expediente"], $_SESSION["tramite"]["liquidacion"][$j]["nombre"], date("Y"), $_SESSION["tramite"]["liquidacion"][$j]["cantidad"], $_SESSION["tramite"]["liquidacion"][$j]["valorbase"], $v_regesadl_cuantia, $valor, 'N', 'N', 'N', 'N', 'S', 'N', $liqactprop, $liqtotestnal, $serv, 0);
                                            $servicioorigenmora = $sir_regesadl_cuantia;
                                        }
                                        \logApi::general2($nameLog, $_SESSION["tramite"]["idliquidacion"], 'Liquido I.R con cuantia para el servicio ' . $serv);
                                    }

                                    //
                                    $dias = \funcionesGenerales::diferenciaEntreFechasCalendario(date("Ymd"), $t["dattrans"]["fechadoc"]);

                                    //
                                    if (
                                            ($dias > 30 && ($temServ["meses_mora"] == '1')) ||
                                            ($dias > 60 && ($temServ["meses_mora"] == '' || $temServ["meses_mora"] == '0' || $temServ["meses_mora"] == '1' || $temServ["meses_mora"] == '2'))
                                    ) {
                                        // 2021-04-26: JINT: Se ajusta el liquidador para que reste un día pues segun reporta CC Honda esta calculando un dia de mas
                                        if (defined('IMPUESTO_REGISTRO_MORA_RESTAR_1') && IMPUESTO_REGISTRO_MORA_RESTAR_1 == 'SI') {
                                            if ($temServ["meses_mora"] == '' || $temServ["meses_mora"] == '0' || $temServ["meses_mora"] == '2') {
                                                $diascalcular = $dias - 60 - 1;
                                            } else {
                                                $diascalcular = $dias - 30 - 1;
                                            }
                                        } else {
                                            if ($temServ["meses_mora"] == '' || $temServ["meses_mora"] == '0' || $temServ["meses_mora"] == '2') {
                                                $diascalcular = $dias - 60;
                                            } else {
                                                $diascalcular = $dias - 30;
                                            }
                                        }
                                        if ($diascalcular > 0) {
                                            if ($irServ == 'S') {
                                                $i++;
                                                if ($t["dattrans"]["organizacion"] != '12' && $t["dattrans"]["organizacion"] != '14') {
                                                    $valor = \funcionesRegistrales::redondearServicio($mysqli, $smir_regmer_sincuantia, $_SESSION["tramite"]["liquidacion"][$j1]["valorservicio"] * $vm_regmer_sincuantia * 12 / 365 / 100 * $diascalcular);
                                                    $_SESSION["tramite"]["liquidacion"][$i] = \funcionesRegistrales::sumarServicioGeneral($mysqli, $t["dattrans"]["idsecuencia"], $smir_regmer_sincuantia, $_SESSION["tramite"]["liquidacion"][$j]["expediente"], $_SESSION["tramite"]["liquidacion"][$j]["nombre"], date("Y"), $_SESSION["tramite"]["liquidacion"][$j]["cantidad"], $_SESSION["tramite"]["liquidacion"][$j1]["valorservicio"], $vm_regmer_sincuantia, $valor, 'N', 'N', 'N', 'N', 'S', 'N', $liqactprop, $liqtotestnal, $servicioorigenmora, $diascalcular);
                                                } else {
                                                    $valor = \funcionesRegistrales::redondearServicio($mysqli, $smir_regesadl_sincuantia, $_SESSION["tramite"]["liquidacion"][$j1]["valorservicio"] * $vm_regesadl_sincuantia * 12 / 365 / 100 * $diascalcular);
                                                    $_SESSION["tramite"]["liquidacion"][$i] = \funcionesRegistrales::sumarServicioGeneral($mysqli, $t["dattrans"]["idsecuencia"], $smir_regesadl_sincuantia, $_SESSION["tramite"]["liquidacion"][$j]["expediente"], $_SESSION["tramite"]["liquidacion"][$j]["nombre"], date("Y"), $_SESSION["tramite"]["liquidacion"][$j]["cantidad"], $_SESSION["tramite"]["liquidacion"][$j1]["valorservicio"], $vm_regesadl_sincuantia, $valor, 'N', 'N', 'N', 'N', 'S', 'N', $liqactprop, $liqtotestnal, $servicioorigenmora, $diascalcular);
                                                }
                                                \logApi::general2($nameLog, $_SESSION["tramite"]["idliquidacion"], 'Liquido M.I.R sin cuantia para el servicio ' . $serv);
                                            }
                                            if ($irServ == 'C' || $irServ == 'D' || is_numeric($irServ)) {
                                                $i++;
                                                if ($t["dattrans"]["organizacion"] != '12' && $t["dattrans"]["organizacion"] != '14') {
                                                    $valor = \funcionesRegistrales::redondearServicio($mysqli, $smir_regmer_cuantia, $_SESSION["tramite"]["liquidacion"][$j1]["valorservicio"] * $vm_regmer_cuantia * 12 / 365 / 100 * $diascalcular);
                                                    $_SESSION["tramite"]["liquidacion"][$i] = \funcionesRegistrales::sumarServicioGeneral($mysqli, $t["dattrans"]["idsecuencia"], $smir_regmer_cuantia, $_SESSION["tramite"]["liquidacion"][$j]["expediente"], $_SESSION["tramite"]["liquidacion"][$j]["nombre"], date("Y"), $_SESSION["tramite"]["liquidacion"][$j]["cantidad"], $_SESSION["tramite"]["liquidacion"][$j1]["valorservicio"], $vm_regmer_cuantia, $valor, 'N', 'N', 'N', 'N', 'S', 'N', $liqactprop, $liqtotestnal, $servicioorigenmora, $diascalcular);
                                                } else {
                                                    $valor = \funcionesRegistrales::redondearServicio($mysqli, $smir_regesadl_cuantia, $_SESSION["tramite"]["liquidacion"][$j1]["valorservicio"] * $vm_regesadl_cuantia * 12 / 365 / 100 * $diascalcular);
                                                    $_SESSION["tramite"]["liquidacion"][$i] = \funcionesRegistrales::sumarServicioGeneral($mysqli, $t["dattrans"]["idsecuencia"], $smir_regesadl_cuantia, $_SESSION["tramite"]["liquidacion"][$j]["expediente"], $_SESSION["tramite"]["liquidacion"][$j]["nombre"], date("Y"), $_SESSION["tramite"]["liquidacion"][$j]["cantidad"], $_SESSION["tramite"]["liquidacion"][$j1]["valorservicio"], $vm_regesadl_cuantia, $valor, 'N', 'N', 'N', 'N', 'S', 'N', $liqactprop, $liqtotestnal, $servicioorigenmora, $diascalcular);
                                                }
                                                \logApi::general2($nameLog, $_SESSION["tramite"]["idliquidacion"], 'Liquido M.I.R con cuantia para el servicio ' . $serv);
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }

            // Aplicación descuento ley 2259
            if (isset($valorDescuentosLey2259) && $valorDescuentosLey2259 != 0) {
                $i++;
                $_SESSION["tramite"]["liquidacion"][$i]["idsec"] = $t["dattrans"]["idsecuencia"];
                $_SESSION["tramite"]["liquidacion"][$i]["idservicio"] = '01090170';
                $_SESSION["tramite"]["liquidacion"][$i]["expediente"] = $_SESSION["tramite"]["matriculabase"];
                $_SESSION["tramite"]["liquidacion"][$i]["nombre"] = $_SESSION["tramite"]["nombrebase"];
                $_SESSION["tramite"]["liquidacion"][$i]["ano"] = '';
                $_SESSION["tramite"]["liquidacion"][$i]["cantidad"] = -1;
                $_SESSION["tramite"]["liquidacion"][$i]["valorbase"] = 0;
                $_SESSION["tramite"]["liquidacion"][$i]["porcentaje"] = 0;
                $_SESSION["tramite"]["liquidacion"][$i]["valorservicio"] = $valorDescuentosLey2259 * -1;
                $_SESSION["tramite"]["liquidacion"][$i]["benart7"] = '';
                $_SESSION["tramite"]["liquidacion"][$i]["reliquidacion"] = 'N';
                $_SESSION["tramite"]["liquidacion"][$i]["serviciobase"] = 'N';
                $_SESSION["tramite"]["liquidacion"][$i]["pagoafiliacion"] = 'N';
                $_SESSION["tramite"]["liquidacion"][$i]["ir"] = 'N';
                $_SESSION["tramite"]["liquidacion"][$i]["iva"] = 'N';
                \logApi::general2($nameLog, $_SESSION["tramite"]["idliquidacion"], 'Liquido descuento ley 2259 ');
            }

            // Liquida hojas
            if (isset($t["dattrans"]["incluir_costo_hojas"]) && $t["dattrans"]["incluir_costo_hojas"] == 'S') {
                $srv = retornarRegistroMysqliApi($mysqli, 'mreg_servicios', "idservicio='06055001'");
                if ($srv && !empty($srv)) {
                    $i++;
                    $_SESSION["tramite"]["liquidacion"][$i]["idsec"] = $t["dattrans"]["idsecuencia"];
                    $_SESSION["tramite"]["liquidacion"][$i]["idservicio"] = '06055001';
                    $_SESSION["tramite"]["liquidacion"][$i]["expediente"] = $_SESSION["tramite"]["matriculabase"];
                    $_SESSION["tramite"]["liquidacion"][$i]["nombre"] = $_SESSION["tramite"]["nombrebase"];
                    $_SESSION["tramite"]["liquidacion"][$i]["ano"] = '';
                    $_SESSION["tramite"]["liquidacion"][$i]["cantidad"] = $t["dattrans"]["paginafinal_libro"] - $t["dattrans"]["paginainicial_libro"] + 1;
                    $_SESSION["tramite"]["liquidacion"][$i]["valorbase"] = 0;
                    $_SESSION["tramite"]["liquidacion"][$i]["porcentaje"] = 0;
                    $_SESSION["tramite"]["liquidacion"][$i]["valorservicio"] = \funcionesRegistrales::buscaTarifa($mysqli, $_SESSION["tramite"]["liquidacion"][$i]["idservicio"], date("Y"), $_SESSION["tramite"]["liquidacion"][$i]["cantidad"], $_SESSION["tramite"]["liquidacion"][$i]["valorbase"], 'tarifa', $liqactprop, $liqtotestnal);
                    $_SESSION["tramite"]["liquidacion"][$i]["benart7"] = '';
                    $_SESSION["tramite"]["liquidacion"][$i]["reliquidacion"] = 'N';
                    $_SESSION["tramite"]["liquidacion"][$i]["serviciobase"] = 'N';
                    $_SESSION["tramite"]["liquidacion"][$i]["pagoafiliacion"] = 'N';
                    $_SESSION["tramite"]["liquidacion"][$i]["ir"] = 'N';
                    $_SESSION["tramite"]["liquidacion"][$i]["iva"] = 'N';
                    \logApi::general2($nameLog, $_SESSION["tramite"]["idliquidacion"], 'Liquido costo hojas ');
                }
            }

            // Liquida envio hojas
            if (isset($t["dattrans"]["incluir_costo_envio"]) && $t["dattrans"]["incluir_costo_envio"] == 'S') {
                $srv = retornarRegistroMysqliApi($mysqli, 'mreg_servicios', "idservicio='06055002'");
                if ($srv && !empty($srv)) {
                    $i++;
                    $_SESSION["tramite"]["liquidacion"][$i]["idsec"] = $t["dattrans"]["idsecuencia"];
                    $_SESSION["tramite"]["liquidacion"][$i]["idservicio"] = '06055002';
                    $_SESSION["tramite"]["liquidacion"][$i]["expediente"] = $_SESSION["tramite"]["matriculabase"];
                    $_SESSION["tramite"]["liquidacion"][$i]["nombre"] = $_SESSION["tramite"]["nombrebase"];
                    $_SESSION["tramite"]["liquidacion"][$i]["ano"] = '';
                    $_SESSION["tramite"]["liquidacion"][$i]["cantidad"] = 1;
                    $_SESSION["tramite"]["liquidacion"][$i]["valorbase"] = 0;
                    $_SESSION["tramite"]["liquidacion"][$i]["porcentaje"] = 0;
                    $_SESSION["tramite"]["liquidacion"][$i]["valorservicio"] = \funcionesRegistrales::buscaTarifa($mysqli, $_SESSION["tramite"]["liquidacion"][$i]["idservicio"], date("Y"), 1, $_SESSION["tramite"]["liquidacion"][$i]["valorbase"], 'tarifa', $liqactprop, $liqtotestnal) * $_SESSION["tramite"]["liquidacion"][$i]["cantidad"];
                    $_SESSION["tramite"]["liquidacion"][$i]["benart7"] = '';
                    $_SESSION["tramite"]["liquidacion"][$i]["reliquidacion"] = 'N';
                    $_SESSION["tramite"]["liquidacion"][$i]["serviciobase"] = 'N';
                    $_SESSION["tramite"]["liquidacion"][$i]["pagoafiliacion"] = 'N';
                    $_SESSION["tramite"]["liquidacion"][$i]["ir"] = 'N';
                    $_SESSION["tramite"]["liquidacion"][$i]["iva"] = 'N';
                    \logApi::general2($nameLog, $_SESSION["tramite"]["idliquidacion"], 'Liquido costo envio');
                }
            }

            // Liquida certificados - matricula
            if ($t["dattrans"]["cantidadcermat"] > 0) {
                $srv = retornarRegistroMysqliApi($mysqli, 'mreg_servicios', "idservicio='01010101'");
                if ($srv && !empty($srv)) {
                    $i++;
                    $_SESSION["tramite"]["liquidacion"][$i]["idsec"] = $t["dattrans"]["idsecuencia"];
                    $_SESSION["tramite"]["liquidacion"][$i]["idservicio"] = '01010101';
                    $_SESSION["tramite"]["liquidacion"][$i]["expediente"] = $t["dattrans"]["matriculaafectada"];
                    $_SESSION["tramite"]["liquidacion"][$i]["expediente"] = $t["dattrans"]["matriculaafectada"];
                    if ($t["dattrans"]["matriculaafectada"] == '') {
                        if (($t["dattrans"]["organizacion"] == '12' || $t["dattrans"]["organizacion"] == '14') && $t["dattrans"]["categoria"] == '1') {
                            $_SESSION["tramite"]["liquidacion"][$i]["expediente"] = 'NUEVAESA';
                        } else {
                            if ($t["dattrans"]["organizacion"] == '01') {
                                $_SESSION["tramite"]["liquidacion"][$i]["expediente"] = 'NUEVANAT';
                            }
                            if ($t["dattrans"]["organizacion"] == '02') {
                                $_SESSION["tramite"]["liquidacion"][$i]["expediente"] = 'NUEVAEST';
                            }
                            if ($t["dattrans"]["organizacion"] > '02') {
                                $_SESSION["tramite"]["liquidacion"][$i]["expediente"] = 'NUEVAJUR';
                            }
                            if ($t["dattrans"]["categoria"] == '2') {
                                $_SESSION["tramite"]["liquidacion"][$i]["expediente"] = 'NUEVASUC';
                            }
                            if ($t["dattrans"]["categoria"] == '3') {
                                $_SESSION["tramite"]["liquidacion"][$i]["expediente"] = 'NUEVAAGE';
                            }
                        }
                    }
                    $_SESSION["tramite"]["liquidacion"][$i]["nombre"] = $_SESSION["tramite"]["nombrebase"];
                    $_SESSION["tramite"]["liquidacion"][$i]["ano"] = '';
                    $_SESSION["tramite"]["liquidacion"][$i]["cantidad"] = $t["dattrans"]["cantidadcermat"];
                    $_SESSION["tramite"]["liquidacion"][$i]["valorbase"] = 0;
                    $_SESSION["tramite"]["liquidacion"][$i]["porcentaje"] = 0;
                    $_SESSION["tramite"]["liquidacion"][$i]["valorservicio"] = \funcionesRegistrales::buscaTarifa($mysqli, $_SESSION["tramite"]["liquidacion"][$i]["idservicio"], date("Y"), 1, $_SESSION["tramite"]["liquidacion"][$i]["valorbase"], 'tarifa', $liqactprop, $liqtotestnal) * $_SESSION["tramite"]["liquidacion"][$i]["cantidad"];
                    $_SESSION["tramite"]["liquidacion"][$i]["benart7"] = '';
                    $_SESSION["tramite"]["liquidacion"][$i]["reliquidacion"] = 'N';
                    $_SESSION["tramite"]["liquidacion"][$i]["serviciobase"] = 'N';
                    $_SESSION["tramite"]["liquidacion"][$i]["pagoafiliacion"] = 'N';
                    $_SESSION["tramite"]["liquidacion"][$i]["ir"] = 'N';
                    $_SESSION["tramite"]["liquidacion"][$i]["iva"] = 'N';
                    \logApi::general2($nameLog, $_SESSION["tramite"]["idliquidacion"], 'Liquido certificado de matricula ');
                }
            }

            // Liquida certificados - existencia
            if ($t["dattrans"]["cantidadcerexi"] > 0) {
                if ($t["dattrans"]["matriculaafectada"] == '') {
                    if (($t["dattrans"]["organizacion"] == '12' || $t["dattrans"]["organizacion"] == '14') && $t["dattrans"]["categoria"] == '1') {
                        $srv = retornarRegistroMysqliApi($mysqli, 'mreg_servicios', "idservicio='01010301'");
                    } else {
                        $srv = retornarRegistroMysqliApi($mysqli, 'mreg_servicios', "idservicio='01010102'");
                    }
                } else {
                    if (substr($t["dattrans"]["matriculaafectada"], 0, 1) == 'S') {
                        $srv = retornarRegistroMysqliApi($mysqli, 'mreg_servicios', "idservicio='01010301'");
                    } else {
                        $srv = retornarRegistroMysqliApi($mysqli, 'mreg_servicios', "idservicio='01010102'");
                    }
                }
                if ($srv && !empty($srv)) {
                    $i++;
                    $_SESSION["tramite"]["liquidacion"][$i]["idsec"] = $t["dattrans"]["idsecuencia"];
                    if ($t["dattrans"]["matriculaafectada"] == '') {
                        if (($t["dattrans"]["organizacion"] == '12' || $t["dattrans"]["organizacion"] == '14') && $t["dattrans"]["categoria"] == '1') {
                            $_SESSION["tramite"]["liquidacion"][$i]["idservicio"] = '01010301';
                        } else {
                            $_SESSION["tramite"]["liquidacion"][$i]["idservicio"] = '01010102';
                        }
                    } else {
                        if (substr($t["dattrans"]["matriculaafectada"], 0, 1) == 'S') {
                            $_SESSION["tramite"]["liquidacion"][$i]["idservicio"] = '01010301';
                        } else {
                            $_SESSION["tramite"]["liquidacion"][$i]["idservicio"] = '01010102';
                        }
                    }
                    if ($t["dattrans"]["matriculaafectada"] == '') {
                        if (($t["dattrans"]["organizacion"] == '12' || $t["dattrans"]["organizacion"] == '14') && $t["dattrans"]["categoria"] == '1') {
                            $_SESSION["tramite"]["liquidacion"][$i]["expediente"] = 'NUEVAESA';
                        } else {
                            if ($t["dattrans"]["organizacion"] == '01') {
                                $_SESSION["tramite"]["liquidacion"][$i]["expediente"] = 'NUEVANAT';
                            }
                            if ($t["dattrans"]["organizacion"] == '02') {
                                $_SESSION["tramite"]["liquidacion"][$i]["expediente"] = 'NUEVAEST';
                            }
                            if ($t["dattrans"]["organizacion"] > '02') {
                                $_SESSION["tramite"]["liquidacion"][$i]["expediente"] = 'NUEVAJUR';
                            }
                            if ($t["dattrans"]["categoria"] == '2') {
                                $_SESSION["tramite"]["liquidacion"][$i]["expediente"] = 'NUEVASUC';
                            }
                            if ($t["dattrans"]["categoria"] == '3') {
                                $_SESSION["tramite"]["liquidacion"][$i]["expediente"] = 'NUEVAAGE';
                            }
                        }
                    }
                    $_SESSION["tramite"]["liquidacion"][$i]["nombre"] = $_SESSION["tramite"]["nombrebase"];
                    $_SESSION["tramite"]["liquidacion"][$i]["ano"] = '';
                    $_SESSION["tramite"]["liquidacion"][$i]["cantidad"] = $t["dattrans"]["cantidadcerexi"];
                    $_SESSION["tramite"]["liquidacion"][$i]["valorbase"] = 0;
                    $_SESSION["tramite"]["liquidacion"][$i]["porcentaje"] = 0;
                    $_SESSION["tramite"]["liquidacion"][$i]["valorservicio"] = \funcionesRegistrales::buscaTarifa($mysqli, $_SESSION["tramite"]["liquidacion"][$i]["idservicio"], date("Y"), 1, $_SESSION["tramite"]["liquidacion"][$i]["valorbase"], 'tarifa', $liqactprop, $liqtotestnal) * $_SESSION["tramite"]["liquidacion"][$i]["cantidad"];
                    $_SESSION["tramite"]["liquidacion"][$i]["benart7"] = '';
                    $_SESSION["tramite"]["liquidacion"][$i]["reliquidacion"] = 'N';
                    $_SESSION["tramite"]["liquidacion"][$i]["serviciobase"] = 'N';
                    $_SESSION["tramite"]["liquidacion"][$i]["pagoafiliacion"] = 'N';
                    $_SESSION["tramite"]["liquidacion"][$i]["ir"] = 'N';
                    $_SESSION["tramite"]["liquidacion"][$i]["iva"] = 'N';
                    \logApi::general2($nameLog, $_SESSION["tramite"]["idliquidacion"], 'Liquido certificado de existencia ');
                }
            }

            // Liquida certificados - libros
            if ($t["dattrans"]["cantidadcerlib"] > 0) {
                $srv = retornarRegistroMysqliApi($mysqli, 'mreg_servicios', "idservicio='01010104'");
                if ($srv && !empty($srv)) {
                    $i++;
                    $_SESSION["tramite"]["liquidacion"][$i]["idsec"] = $t["dattrans"]["idsecuencia"];
                    $_SESSION["tramite"]["liquidacion"][$i]["idservicio"] = '01010104';
                    if ($t["dattrans"]["matriculaafectada"] == '') {
                        if (($t["dattrans"]["organizacion"] == '12' || $t["dattrans"]["organizacion"] == '14') && $t["dattrans"]["categoria"] == '1') {
                            $_SESSION["tramite"]["liquidacion"][$i]["expediente"] = 'NUEVAESA';
                        } else {
                            if ($t["dattrans"]["organizacion"] == '01') {
                                $_SESSION["tramite"]["liquidacion"][$i]["expediente"] = 'NUEVANAT';
                            }
                            if ($t["dattrans"]["organizacion"] == '02') {
                                $_SESSION["tramite"]["liquidacion"][$i]["expediente"] = 'NUEVAEST';
                            }
                            if ($t["dattrans"]["organizacion"] > '02') {
                                $_SESSION["tramite"]["liquidacion"][$i]["expediente"] = 'NUEVAJUR';
                            }
                            if ($t["dattrans"]["categoria"] == '2') {
                                $_SESSION["tramite"]["liquidacion"][$i]["expediente"] = 'NUEVASUC';
                            }
                            if ($t["dattrans"]["categoria"] == '3') {
                                $_SESSION["tramite"]["liquidacion"][$i]["expediente"] = 'NUEVAAGE';
                            }
                        }
                    }
                    $_SESSION["tramite"]["liquidacion"][$i]["nombre"] = $_SESSION["tramite"]["nombrebase"];
                    $_SESSION["tramite"]["liquidacion"][$i]["ano"] = '';
                    $_SESSION["tramite"]["liquidacion"][$i]["cantidad"] = $t["dattrans"]["cantidadcerlib"];
                    $_SESSION["tramite"]["liquidacion"][$i]["valorbase"] = 0;
                    $_SESSION["tramite"]["liquidacion"][$i]["porcentaje"] = 0;
                    $_SESSION["tramite"]["liquidacion"][$i]["valorservicio"] = \funcionesRegistrales::buscaTarifa($mysqli, $_SESSION["tramite"]["liquidacion"][$i]["idservicio"], date("Y"), 1, $_SESSION["tramite"]["liquidacion"][$i]["valorbase"], 'tarifa', $liqactprop, $liqtotestnal) * $_SESSION["tramite"]["liquidacion"][$i]["cantidad"];
                    $_SESSION["tramite"]["liquidacion"][$i]["benart7"] = '';
                    $_SESSION["tramite"]["liquidacion"][$i]["reliquidacion"] = 'N';
                    $_SESSION["tramite"]["liquidacion"][$i]["serviciobase"] = 'N';
                    $_SESSION["tramite"]["liquidacion"][$i]["pagoafiliacion"] = 'N';
                    $_SESSION["tramite"]["liquidacion"][$i]["ir"] = 'N';
                    $_SESSION["tramite"]["liquidacion"][$i]["iva"] = 'N';
                    \logApi::general2($nameLog, $_SESSION["tramite"]["idliquidacion"], 'Liquido certificado de libros ');
                }
            }
        }


        // ********************************************************************************** //
        // Verifica Aplicacion de Ley 1429 100% (S1 - al 100%)
        // Se debe modificar si se extiende el plazo de la Ley 1429
        // No aplica en caso de renovaciones
        // ********************************************************************************** //
        $i = count($_SESSION["tramite"]["liquidacion"]);
        if ($_SESSION["tramite"]["tipotramite"] != 'renovacionmatricula' && $_SESSION["tramite"]["tipotramite"] != 'renovacionesadl') {
            if (date("Y") <= '2014') {
                $arrLiq = $_SESSION["tramite"]["liquidacion"];
                $i = count($_SESSION["tramite"]["liquidacion"]);
                foreach ($arrLiq as $l) {
                    if ($l["serviciobase"] == 'S') {
                        if ($arrServs[$l["idservicio"]]["aplica1429"] == 'S1') {
                            foreach ($_SESSION["tramite"]["transacciones"] as $t) {
                                if (sprintf("%03s", $t["idsecuencia"]) == sprintf("%03s", $l["idsec"])) {
                                    if ($t["benart7"] == 'S') {
                                        $i++;
                                        $_SESSION["tramite"]["liquidacion"][$i]["idsec"] = $l["idsec"];
                                        $_SESSION["tramite"]["liquidacion"][$i]["idservicio"] = retornarClaveValorMysqliApi($mysqli, '90.27.01');
                                        $_SESSION["tramite"]["liquidacion"][$i]["expediente"] = $l["expediente"];
                                        $_SESSION["tramite"]["liquidacion"][$i]["nombre"] = $l["nombre"];
                                        $_SESSION["tramite"]["liquidacion"][$i]["ano"] = date("Y");
                                        $_SESSION["tramite"]["liquidacion"][$i]["cantidad"] = 1;
                                        $_SESSION["tramite"]["liquidacion"][$i]["valorbase"] = $l["valorbase"];
                                        $_SESSION["tramite"]["liquidacion"][$i]["porcentaje"] = 100;
                                        $_SESSION["tramite"]["liquidacion"][$i]["valorservicio"] = $l["valorservicio"] * -1;
                                        $_SESSION["tramite"]["liquidacion"][$i]["benart7"] = 'S';
                                        $_SESSION["tramite"]["liquidacion"][$i]["reliquidacion"] = 'N';
                                        $_SESSION["tramite"]["liquidacion"][$i]["serviciobase"] = 'N';
                                        $_SESSION["tramite"]["liquidacion"][$i]["pagoafiliacion"] = 'N';
                                        $_SESSION["tramite"]["liquidacion"][$i]["ir"] = 'N';
                                        $_SESSION["tramite"]["liquidacion"][$i]["iva"] = 'N';
                                        \logApi::general2($nameLog, $_SESSION["tramite"]["idliquidacion"], 'Verifico 1429');
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }


        // ********************************************************************************** //
        // Verifica Aplicacion del decreto 658
        // ********************************************************************************** //
        if ($liquidarDecreto658 == 'SI') {
            foreach ($decreto658 as $d) {
                if ($d["porcentaje"] != 0) {
                    if ($d["valorservicio"] != 0) {
                        $arrLiq = $_SESSION["tramite"]["liquidacion"];
                        $i = count($_SESSION["tramite"]["liquidacion"]);
                        $i++;
                        $_SESSION["tramite"]["liquidacion"][$i]["idsec"] = sprintf("%03s", $d["idsec"]);
                        $_SESSION["tramite"]["liquidacion"][$i]["idservicio"] = $d["servicio"];
                        $_SESSION["tramite"]["liquidacion"][$i]["expediente"] = '';
                        $_SESSION["tramite"]["liquidacion"][$i]["nombre"] = $d["nombre"];
                        $_SESSION["tramite"]["liquidacion"][$i]["ano"] = date("Y");
                        $_SESSION["tramite"]["liquidacion"][$i]["cantidad"] = -1;
                        $_SESSION["tramite"]["liquidacion"][$i]["valorbase"] = $d["activos"];
                        $_SESSION["tramite"]["liquidacion"][$i]["porcentaje"] = $d["porcentaje"];
                        $_SESSION["tramite"]["liquidacion"][$i]["valorservicio"] = $d["valorservicio"] * -1;
                        $_SESSION["tramite"]["liquidacion"][$i]["benart7"] = 'N';
                        $_SESSION["tramite"]["liquidacion"][$i]["reliquidacion"] = 'N';
                        $_SESSION["tramite"]["liquidacion"][$i]["serviciobase"] = 'N';
                        $_SESSION["tramite"]["liquidacion"][$i]["pagoafiliacion"] = 'N';
                        $_SESSION["tramite"]["liquidacion"][$i]["ir"] = 'N';
                        $_SESSION["tramite"]["liquidacion"][$i]["iva"] = 'N';
                        \logApi::general2($nameLog, $_SESSION["tramite"]["idliquidacion"], 'Liquido aplicabilidad 658');
                    }
                }
            }
        }


        // ********************************************************************************** //
        // Verifica Aplicacion Ley 1780
        // ********************************************************************************** //
        foreach ($ley1780 as $d) {
            if ($d["valorservicio"] != 0) {
                $arrLiq = $_SESSION["tramite"]["liquidacion"];
                $i = count($_SESSION["tramite"]["liquidacion"]);
                $i++;
                $_SESSION["tramite"]["liquidacion"][$i]["idsec"] = sprintf("%03s", $d["idsec"]);
                $_SESSION["tramite"]["liquidacion"][$i]["idservicio"] = $d["servicio"];
                if (isset($d["cc"])) {
                    $_SESSION["tramite"]["liquidacion"][$i]["cc"] = $d["cc"];
                } else {
                    $_SESSION["tramite"]["liquidacion"][$i]["cc"] = CODIGO_EMPRESA;
                }
                if (!isset($d["expediente"])) {
                    $d["expediente"] = '';
                }
                $_SESSION["tramite"]["liquidacion"][$i]["expediente"] = $d["expediente"];
                if ($d["expediente"] == '') {
                    if ($d["tipotramite"] == 'constitucionpjur') {
                        $_SESSION["tramite"]["liquidacion"][$i]["expediente"] = 'NUEVAJUR';
                    }
                    if ($d["tipotramite"] == 'constitucionesadl') {
                        $_SESSION["tramite"]["liquidacion"][$i]["expediente"] = 'NUEVAESA';
                    }
                    if ($d["tipotramite"] == 'matriculapnat') {
                        $_SESSION["tramite"]["liquidacion"][$i]["expediente"] = 'NUEVANAT';
                    }
                    if ($d["tipotramite"] == 'matriculaest') {
                        $_SESSION["tramite"]["liquidacion"][$i]["expediente"] = 'NUEVAEST';
                    }
                    if ($d["tipotramite"] == 'matriculasuc') {
                        $_SESSION["tramite"]["liquidacion"][$i]["expediente"] = 'NUEVASUC';
                    }
                    if ($d["tipotramite"] == 'matriculaage') {
                        $_SESSION["tramite"]["liquidacion"][$i]["expediente"] = 'NUEVAAGE';
                    }
                }
                $_SESSION["tramite"]["liquidacion"][$i]["nombre"] = $d["nombre"];
                $_SESSION["tramite"]["liquidacion"][$i]["ano"] = date("Y");
                $_SESSION["tramite"]["liquidacion"][$i]["cantidad"] = -1;
                $_SESSION["tramite"]["liquidacion"][$i]["valorbase"] = $d["activos"];
                $_SESSION["tramite"]["liquidacion"][$i]["porcentaje"] = $d["porcentaje"];
                $_SESSION["tramite"]["liquidacion"][$i]["valorservicio"] = $d["valorservicio"] * -1;
                $_SESSION["tramite"]["liquidacion"][$i]["benart7"] = 'N';
                $_SESSION["tramite"]["liquidacion"][$i]["benley1780"] = 'S';
                $_SESSION["tramite"]["liquidacion"][$i]["reliquidacion"] = 'N';
                $_SESSION["tramite"]["liquidacion"][$i]["serviciobase"] = 'N';
                $_SESSION["tramite"]["liquidacion"][$i]["pagoafiliacion"] = 'N';
                $_SESSION["tramite"]["liquidacion"][$i]["ir"] = 'N';
                $_SESSION["tramite"]["liquidacion"][$i]["iva"] = 'N';
                \logApi::general2($nameLog, $_SESSION["tramite"]["idliquidacion"], 'Liquido aplicabilidad 1780');
            }
        }


        // ************************************************************ //
        // Liquida los servicios dependientes (relacionados)
        // ************************************************************ //
        if ($_SESSION["tramite"]["tipotramite"] != 'inscripciondocumentos') {
            $i = count($_SESSION ["tramite"] ["liquidacion"]);
            $_SESSION ["generales"] ["txtemergente"] = '';
            $siAfiliado = 'NO';
            $tarifaanteriorafiliacion = 0;
            foreach ($_SESSION ["tramite"] ["liquidacion"] as $serv) {
                if (trim($serv["idservicio"]) != '') {
                    if ($serv ["serviciobase"] == 'S') {

                        $arrServicio = $arrServs[$serv ["idservicio"]];
                        if (!isset($arrServs[$serv ["idservicio"]])) {
                            $_SESSION ["generales"] ["txtemergente"] .= 'El servicio [' . $serv ["idservicio"] . '] no esta definido en la tabla de servicios del SII\n';
                        } else {
                            for ($x = 1; $x <= 7; $x++) {
                                switch ($x) {
                                    case 1 :
                                        $tempserv = $arrServs[$serv ["idservicio"]] ["iddependiente1"];
                                        break;
                                    case 2 :
                                        $tempserv = $arrServs[$serv ["idservicio"]] ["iddependiente2"];
                                        break;
                                    case 3 :
                                        $tempserv = $arrServs[$serv ["idservicio"]] ["iddependiente3"];
                                        break;
                                    case 4 :
                                        $tempserv = $arrServs[$serv ["idservicio"]] ["iddependiente4"];
                                        break;
                                    case 5 :
                                        $tempserv = $arrServs[$serv ["idservicio"]] ["iddependiente5"];
                                        break;
                                    case 6 :
                                        $tempserv = $arrServs[$serv ["idservicio"]] ["iddependiente6"];
                                        break;
                                    case 7 :
                                        $tempserv = $arrServs[$serv ["idservicio"]] ["iddependiente7"];
                                        break;
                                }
                                if (trim($tempserv) != '') {
                                    $sumar = 'si';
                                    if ($tempserv == RENOVACION_SERV_AFILIACION) {
                                        if ($disminucionactivos == 'SI') {
                                            $sumar = 'no';
                                        } else {
                                            if ($serv ["organizacion"] != '02') {
                                                if ($serv ["categoria"] != '3') {
                                                    if ($_SESSION ["tramite"] ["incluirafiliacion"] == 'S') {
                                                        if ($serv ["afiliado"] != 'S') {
                                                            $sumar = 'no';
                                                        } else {
                                                            if (RENOVACION_LIQUIDAR_AFILIACION_RELACIONADO != 'S') {
                                                                $sumar = 'no';
                                                            } else {
                                                                if ($serv ["reliquidacion"] != 'si') {
                                                                    if ($serv ["ultimoanoafiliado"] == intval(date("Y"))) {
                                                                        $sumar = 'no';
                                                                    }
                                                                }
                                                                if ($serv ["reliquidacion"] == 'si') {
                                                                    if ($serv ["ultimoanoafiliado"] != intval(date("Y"))) {
                                                                        $sumar = 'no';
                                                                    } else {
                                                                        $tarifaanteriorafiliacion = \funcionesRegistrales::buscaTarifa($mysqli, RENOVACION_SERV_AFILIACION, $serv ["ultimoanoafiliado"], 1, $baseanteriorafiliacion, 'tarifa', $liqactprop, $liqtotestnal);
                                                                        if (TIPO_AFILIACION == 'TARIFAPORTIPO') {
                                                                            if ($serv ["organizacion"] == '01') {
                                                                                $tarifaanteriorafiliacion = \funcionesRegistrales::buscaTarifa($mysqli, RENOVACION_SERV_AFILIACION, $serv ["ultimoanoafiliado"], 1, $baseanteriorafiliacion, 'tarifapnat', $liqactprop, $liqtotestnal);
                                                                            }
                                                                            if ($serv ["organizacion"] != '01') {
                                                                                $tarifaanteriorafiliacion = \funcionesRegistrales::buscaTarifa($mysqli, RENOVACION_SERV_AFILIACION, $serv ["ultimoanoafiliado"], 1, $baseanteriorafiliacion, 'tarifapjur', $liqactprop, $liqtotestnal);
                                                                            }
                                                                        }
                                                                    }
                                                                }
                                                            }
                                                        }
                                                    } else {
                                                        $sumar = 'no';
                                                    }
                                                } else {
                                                    $sumar = 'no';
                                                }
                                            } else {
                                                $sumar = 'no';
                                            }
                                        }
                                    }

//
                                    if ($sumar == 'si') {
                                        if ($serv ["ano"] == date("Y")) {
                                            $sumar = 'si';
                                        } else {
                                            $sumar = \funcionesRegistrales::validarReglasEspecialesRenovacion($mysqli, $_SESSION ["generales"] ["codigoempresa"], $tempserv, $serv ["ano"]);
                                        }
                                    }

                                    if ($sumar == 'si') {
                                        $i++;
                                        $_SESSION ["tramite"] ["liquidacion"] [$i] ["idsec"] = $serv ["idsec"];
                                        $_SESSION ["tramite"] ["liquidacion"] [$i] ["expediente"] = $serv ["expediente"];
                                        $_SESSION ["tramite"] ["liquidacion"] [$i] ["nombre"] = $serv ["nombre"];
                                        $_SESSION ["tramite"] ["liquidacion"] [$i] ["organizacion"] = $serv ["organizacion"];
                                        $_SESSION ["tramite"] ["liquidacion"] [$i] ["categoria"] = $serv ["categoria"];
                                        $_SESSION ["tramite"] ["liquidacion"] [$i] ["idservicio"] = $tempserv;
                                        $_SESSION ["tramite"] ["liquidacion"] [$i] ["ano"] = $serv ["ano"];
                                        $_SESSION ["tramite"] ["liquidacion"] [$i] ["cantidad"] = 1;
                                        $_SESSION ["tramite"] ["liquidacion"] [$i] ["valorbase"] = $serv ["valorbase"];
                                        $_SESSION ["tramite"] ["liquidacion"] [$i] ["porcentaje"] = 0;
                                        if ($_SESSION ["tramite"] ["liquidacion"] [$i] ["idservicio"] == RENOVACION_SERV_AFILIACION) {
                                            $_SESSION ["tramite"] ["liquidacion"] [$i] ["valorservicio"] = \funcionesRegistrales::buscaTarifa($mysqli, $_SESSION ["tramite"] ["liquidacion"] [$i] ["idservicio"], $_SESSION ["tramite"] ["liquidacion"] [$i] ["ano"], 1, $serv ["valorbase"], 'tarifa', $liqactprop, $liqtotestnal);
                                            if (TIPO_AFILIACION == 'TARIFAPORTIPO') {
                                                if ($serv ["organizacion"] == '01') {
                                                    $_SESSION ["tramite"] ["liquidacion"] [$i] ["valorservicio"] = \funcionesRegistrales::buscaTarifa($mysqli, $_SESSION ["tramite"] ["liquidacion"] [$i] ["idservicio"], $_SESSION ["tramite"] ["liquidacion"] [$i] ["ano"], 1, $serv ["valorbase"], 'tarifapnat', $liqactprop, $liqtotestnal);
                                                }
                                                if ($serv ["organizacion"] != '01') {
                                                    $_SESSION ["tramite"] ["liquidacion"] [$i] ["valorservicio"] = \funcionesRegistrales::buscaTarifa($mysqli, $_SESSION ["tramite"] ["liquidacion"] [$i] ["idservicio"], $_SESSION ["tramite"] ["liquidacion"] [$i] ["ano"], 1, $serv ["valorbase"], 'tarifapjur', $liqactprop, $liqtotestnal);
                                                }
                                            }
                                        } else {
                                            $_SESSION ["tramite"] ["liquidacion"] [$i] ["valorservicio"] = \funcionesRegistrales::buscaTarifa($mysqli, $_SESSION ["tramite"] ["liquidacion"] [$i] ["idservicio"], $_SESSION ["tramite"] ["liquidacion"] [$i] ["ano"], 1, $serv ["valorbase"], 'tarifa', $liqactprop, $liqtotestnal);
                                        }
                                        if ($_SESSION ["tramite"] ["liquidacion"] [$i] ["idservicio"] == RENOVACION_SERV_AFILIACION) {
                                            $_SESSION ["tramite"] ["liquidacion"] [$i] ["valorservicio"] = $_SESSION ["tramite"] ["liquidacion"] [$i] ["valorservicio"] - $tarifaanteriorafiliacion;
                                        }
                                        $_SESSION ["tramite"] ["valorbruto"] = $_SESSION ["tramite"] ["valorbruto"] + $_SESSION ["tramite"] ["liquidacion"] [$i] ["valorservicio"];
                                        $_SESSION ["tramite"] ["liquidacion"] [$i] ["serviciobase"] = 'N';
                                        $_SESSION ["tramite"] ["liquidacion"] [$i] ["reliquidacion"] = $serv ["reliquidacion"];
                                        if ($tempserv == RENOVACION_SERV_AFILIACION) {
                                            $siAfiliado = 'SI';
                                        }
                                        \logApi::general2($nameLog, $_SESSION["tramite"]["idliquidacion"], 'Liquido dependientes');
                                    }
                                }
                            }
                        }
                    }
                }
            }


            if ($_SESSION ["generales"] ["txtemergente"] != '') {
                $_SESSION ["generales"] ["txtemergente"] = "Hay errores en la parametrizaci&oacute;n de los servicios , por favor informe al administrador del portal los errores que a continuaci&oacute;n se describen: " . $_SESSION ["generales"] ["txtemergente"];
                return false;
            }
        }


        // ************************************************************ //
        // Liquida Certificados
        // Solo en caso de renovaciones
        // Solamente si se renuevan todos los años
        // ************************************************************ //
        $i = count($_SESSION ["tramite"] ["liquidacion"]);
        if ($_SESSION["tramite"]["tipotramite"] == 'renovacionmatricula' || $_SESSION["tramite"]["tipotramite"] == 'renovacionesadl') {
            $siCertificados = 'NO';
            if (RENOVACION_LIQUIDAR_CERTIFICADOS == 'S') {
                if ($_SESSION ["tramite"] ["incluircertificados"] == 'S') {
                    $serv = $_SESSION ["tramite"] ["liquidacion"] [1];
                    if ($serv ["organizacion"] == '01') {
                        $tempserv = RENOVACION_SERV_CERTIMAT;
                    }
                    if ($serv ["organizacion"] == '02') {
                        $tempserv = RENOVACION_SERV_CERTIMAT;
                    }
                    if (($serv ["organizacion"] > '02') && ($serv ["categoria"] == '1')) {
                        $tempserv = RENOVACION_SERV_CERTIEXI;
                    }
                    if (($serv ["organizacion"] > '02') && ($serv ["categoria"] == '2')) {
                        $tempserv = RENOVACION_SERV_CERTIEXI;
                    }
                    if (($serv ["organizacion"] > '02') && ($serv ["categoria"] == '3')) {
                        $tempserv = RENOVACION_SERV_CERTIMAT;
                    }
                    $i++;

                    $_SESSION ["tramite"] ["liquidacion"] [$i] ["idsec"] = '000';
                    $_SESSION ["tramite"] ["liquidacion"] [$i] ["expediente"] = $serv ["expediente"];
                    $_SESSION ["tramite"] ["liquidacion"] [$i] ["nombre"] = $serv ["nombre"];
                    $_SESSION ["tramite"] ["liquidacion"] [$i] ["organizacion"] = $serv ["organizacion"];
                    $_SESSION ["tramite"] ["liquidacion"] [$i] ["categoria"] = $serv ["categoria"];
                    $_SESSION ["tramite"] ["liquidacion"] [$i] ["idservicio"] = $tempserv;
                    $_SESSION ["tramite"] ["liquidacion"] [$i] ["ano"] = '';
                    $_SESSION ["tramite"] ["liquidacion"] [$i] ["cantidad"] = RENOVACION_CANT_CERTIFICADOS;
                    $_SESSION ["tramite"] ["liquidacion"] [$i] ["valorbase"] = 0;
                    $_SESSION ["tramite"] ["liquidacion"] [$i] ["porcentaje"] = 0;
                    if ($siAfiliado == 'SI') {
                        if (!defined('RENOVACION_COBRAR_CERTIFICADOS_AFILIADOS')) {
                            define('RENOVACION_COBRAR_CERTIFICADOS_AFILIADOS', 'S');
                        }
                        if (RENOVACION_COBRAR_CERTIFICADOS_AFILIADOS == 'S') {
                            $_SESSION ["tramite"] ["liquidacion"] [$i] ["valorservicio"] = \funcionesRegistrales::buscaTarifa($mysqli, $_SESSION ["tramite"] ["liquidacion"] [$i] ["idservicio"], 0, RENOVACION_CANT_CERTIFICADOS, 0, 'tarifa', $liqactprop, $liqtotestnal);
                        } else {
                            $_SESSION ["tramite"] ["liquidacion"] [$i] ["valorservicio"] = 0;
                        }
                    } else {
                        $_SESSION ["tramite"] ["liquidacion"] [$i] ["valorservicio"] = \funcionesRegistrales::buscaTarifa($mysqli, $_SESSION ["tramite"] ["liquidacion"] [$i] ["idservicio"], 0, RENOVACION_CANT_CERTIFICADOS, 0, 'tarifa', $liqactprop, $liqtotestnal);
                    }
                    $_SESSION ["tramite"] ["valorbruto"] = $_SESSION ["tramite"] ["valorbruto"] + $_SESSION ["tramite"] ["liquidacion"] [$i] ["valorservicio"];
                    $_SESSION ["tramite"] ["liquidacion"] [$i] ["serviciobase"] = 'N';
                    $siCertificados = 'SI';
                    \logApi::general2($nameLog, $_SESSION["tramite"]["idliquidacion"], 'Liquido certificados');
                }
            }
        }


        // ************************************************************ //
        // Liquida Diplomas (para las personas naturales y sociedades principales)
        // Solo en caso de renovaciones
        // Solamente si se renuevan todos los años
        // ************************************************************ //
        $i = count($_SESSION ["tramite"] ["liquidacion"]);
        if ($_SESSION["tramite"]["tipotramite"] == 'renovacionmatricula' || $_SESSION["tramite"]["tipotramite"] == 'renovacionesadl') {
            if (RENOVACION_LIQUIDAR_DIPLOMAS == 'S') {
                if ($_SESSION ["tramite"] ["incluirdiploma"] == 'S') {
                    if ($cantAnos == $cantRenovar) {
                        foreach ($_SESSION ["tramite"] ["liquidacion"] as $serv) {
                            if ($serv ["serviciobase"] == 'S') {
                                if (($serv ["organizacion"] == '01') || (($serv ["organizacion"] > '02') && ($serv ["categoria"] == '1'))) {
                                    if ($serv ["ano"] == date("Y")) {
                                        $i++;
                                        $_SESSION ["tramite"] ["liquidacion"] [$i] ["idsec"] = $serv ["000"];
                                        $_SESSION ["tramite"] ["liquidacion"] [$i] ["expediente"] = $serv ["expediente"];
                                        $_SESSION ["tramite"] ["liquidacion"] [$i] ["nombre"] = $serv ["nombre"];
                                        $_SESSION ["tramite"] ["liquidacion"] [$i] ["organizacion"] = $serv ["organizacion"];
                                        $_SESSION ["tramite"] ["liquidacion"] [$i] ["categoria"] = $serv ["categoria"];
                                        $_SESSION ["tramite"] ["liquidacion"] [$i] ["idservicio"] = RENOVACION_SERV_DIPLOMA;
                                        $_SESSION ["tramite"] ["liquidacion"] [$i] ["ano"] = '';
                                        $_SESSION ["tramite"] ["liquidacion"] [$i] ["cantidad"] = 1;
                                        $_SESSION ["tramite"] ["liquidacion"] [$i] ["valorbase"] = 0;
                                        $_SESSION ["tramite"] ["liquidacion"] [$i] ["porcentaje"] = 0;
                                        $_SESSION ["tramite"] ["liquidacion"] [$i] ["valorservicio"] = \funcionesRegistrales::buscaTarifa($mysqli, $_SESSION ["tramite"] ["liquidacion"] [$i] ["idservicio"], 0, 1, $serv ["valorservicio"], 'tarifa', $liqactprop, $liqtotestnal);
                                        $_SESSION ["tramite"] ["valorbruto"] = $_SESSION ["tramite"] ["valorbruto"] + $_SESSION ["tramite"] ["liquidacion"] [$i] ["valorservicio"];
                                        $_SESSION ["tramite"] ["liquidacion"] [$i] ["serviciobase"] = 'N';
                                        \logApi::general2($nameLog, $_SESSION["tramite"]["idliquidacion"], 'Liquido diplomas');
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        // ************************************************************ //
        // Liquida Cartulinas (para los establecimientos de comercio)
        // Solo en caso de renovaciones
        // Solamente si se renuevan todos los años
        // ************************************************************ //
        $i = count($_SESSION ["tramite"] ["liquidacion"]);
        if ($_SESSION["tramite"]["tipotramite"] == 'renovacionmatricula' ||
                $_SESSION["tramite"]["tipotramite"] == 'renovacionesadl') {

            if (RENOVACION_LIQUIDAR_CARTULINAS == 'S') {
                if ($_SESSION ["tramite"] ["incluircartulina"] == 'S') {
                    if ($cantAnos == $cantRenovar) {
                        foreach ($_SESSION ["tramite"] ["liquidacion"] as $serv) {
                            if ($serv ["serviciobase"] == 'S') {
                                if (($serv ["organizacion"] == '02') || ($serv ["categoria"] == '2') || ($serv ["categoria"] == '3')) {
                                    if ($serv ["ano"] == date("Y")) {
                                        $i++;
                                        $_SESSION ["tramite"] ["liquidacion"] [$i] ["idsec"] = '000';
                                        $_SESSION ["tramite"] ["liquidacion"] [$i] ["expediente"] = $serv ["expediente"];
                                        $_SESSION ["tramite"] ["liquidacion"] [$i] ["nombre"] = $serv ["nombre"];
                                        $_SESSION ["tramite"] ["liquidacion"] [$i] ["organizacion"] = $serv ["organizacion"];
                                        $_SESSION ["tramite"] ["liquidacion"] [$i] ["categoria"] = $serv ["categoria"];
                                        $_SESSION ["tramite"] ["liquidacion"] [$i] ["idservicio"] = RENOVACION_SERV_CARTULINAS;
                                        $_SESSION ["tramite"] ["liquidacion"] [$i] ["ano"] = '';
                                        $_SESSION ["tramite"] ["liquidacion"] [$i] ["cantidad"] = 1;
                                        $_SESSION ["tramite"] ["liquidacion"] [$i] ["valorbase"] = 0;
                                        $_SESSION ["tramite"] ["liquidacion"] [$i] ["porcentaje"] = 0;
                                        $_SESSION ["tramite"] ["liquidacion"] [$i] ["valorservicio"] = \funcionesRegistrales::buscaTarifa($mysqli, $_SESSION ["tramite"] ["liquidacion"] [$i] ["idservicio"], 0, 1, $serv ["valorservicio"], 'tarifa', $liqactprop, $liqtotestnal);
                                        $_SESSION ["tramite"] ["valorbruto"] = $_SESSION ["tramite"] ["valorbruto"] + $_SESSION ["tramite"] ["liquidacion"] [$i] ["valorservicio"];
                                        $_SESSION ["tramite"] ["liquidacion"] [$i] ["serviciobase"] = 'N';
                                        \logApi::general2($nameLog, $_SESSION["tramite"]["idliquidacion"], 'Liquido cartulinas');
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        // ************************************************************ //
        // Liquida afiliacion
        // Solo en caso de renovaciones
        // Debe activarse cuando la afiliacion no se maneje como un servicio 
        // relacionado
        // RENOVACION_PUBLICOS_COBROS_ADICIONALES
        // ************************************************************ //
        $i = count($_SESSION ["tramite"] ["liquidacion"]);
        if ($_SESSION["tramite"]["tipotramite"] == 'renovacionmatricula' || $_SESSION["tramite"]["tipotramite"] == 'renovacionesadl') {
            if ($_SESSION ["tramite"] ["incluirafiliacion"] == 'S') {
                if (RENOVACION_LIQUIDAR_AFILIACION_APARTE == 'S' && $siAfiliado == 'NO') {
                    $siAfiliado = 'NO';
                    $tarifaanteriorafiliacion = 0;
                    foreach ($_SESSION ["tramite"] ["liquidacion"] as $serv) {
                        if ($serv ["serviciobase"] == 'S') {
                            if ($serv ["afiliado"] == 'S' || $serv ["afiliado"] == '1') {
                                if ($serv ["ultimoanoafiliado"] < intval(date("Y")) || $serv ["reliquidacion"] == 'si') {
                                    if ($serv ["ano"] == date("Y")) {
                                        if ($serv ["organizacion"] != '02') {
                                            if ($serv ["categoria"] != '3') {
                                                // 2020-03-04: JINT - Busca los pagos de afiliación primera vez o renovación que haya tenido
                                                $tarifaanteriorafiliacion = 0;
                                                $pagosAfil = retornarRegistrosMysqliApi($mysqli, 'mreg_est_recibos', "(matricula='" . $serv ["expediente"] . "') and (fecoperacion > '" . date("Y") . "0100') and (servicio in (" . $txtServAfiliacion . ")) and (tipogasto in ('0','8')) ", "numerorecibo");
                                                if ($pagosAfil && !empty($pagosAfil)) {
                                                    foreach ($pagosAfil as $afil) {
                                                        $tarifaanteriorafiliacion = $tarifaanteriorafiliacion + $afil["valor"];
                                                    }
                                                }
                                                $nuevatarifaafiliacion = 0;
                                                $deltaafiliacion = 0;
                                                if (TIPO_AFILIACION == 'TARIFAPORTIPO') {
                                                    if ($serv ["organizacion"] == '01') {
                                                        $nuevatarifaafiliacion = \funcionesRegistrales::buscaTarifa($mysqli, RENOVACION_SERV_AFILIACION, date("Y"), 1, $serv ["valorbase"], 'tarifapnat', $liqactprop, $liqtotestnal);
                                                        $deltaafiliacion = $nuevatarifaafiliacion - $tarifaanteriorafiliacion;
                                                    }
                                                    if ($serv ["organizacion"] != '01') {
                                                        $nuevatarifaafiliacion = \funcionesRegistrales::buscaTarifa($mysqli, RENOVACION_SERV_AFILIACION, date("Y"), 1, $serv ["valorbase"], 'tarifapjur', $liqactprop, $liqtotestnal);
                                                        $deltaafiliacion = $nuevatarifaafiliacion - $tarifaanteriorafiliacion;
                                                    }
                                                } else {
                                                    $nuevatarifaafiliacion = \funcionesRegistrales::buscaTarifa($mysqli, RENOVACION_SERV_AFILIACION, date("Y"), 1, $serv ["valorbase"], 'tarifa', $liqactprop, $liqtotestnal);
                                                    $deltaafiliacion = $nuevatarifaafiliacion - $tarifaanteriorafiliacion;
                                                }


                                                if ($deltaafiliacion > 0) {
                                                    $i++;
                                                    $_SESSION ["tramite"] ["liquidacion"] [$i] ["idsec"] = '000';
                                                    $_SESSION ["tramite"] ["liquidacion"] [$i] ["expediente"] = $serv ["expediente"];
                                                    $_SESSION ["tramite"] ["liquidacion"] [$i] ["nombre"] = $serv ["nombre"];
                                                    $_SESSION ["tramite"] ["liquidacion"] [$i] ["organizacion"] = $serv ["organizacion"];
                                                    $_SESSION ["tramite"] ["liquidacion"] [$i] ["categoria"] = $serv ["categoria"];
                                                    $_SESSION ["tramite"] ["liquidacion"] [$i] ["idservicio"] = RENOVACION_SERV_AFILIACION;
                                                    $_SESSION ["tramite"] ["liquidacion"] [$i] ["ano"] = date("Y");
                                                    $_SESSION ["tramite"] ["liquidacion"] [$i] ["cantidad"] = 1;
                                                    $_SESSION ["tramite"] ["liquidacion"] [$i] ["valorbase"] = $serv ["valorbase"];
                                                    $_SESSION ["tramite"] ["liquidacion"] [$i] ["valorservicio"] = $deltaafiliacion;
                                                    $_SESSION ["tramite"] ["liquidacion"] [$i] ["porcentaje"] = 0;
                                                    $_SESSION ["tramite"] ["valorbruto"] = $_SESSION ["tramite"] ["valorbruto"] + $_SESSION ["tramite"] ["liquidacion"] [$i] ["valorservicio"];
                                                    $_SESSION ["tramite"] ["liquidacion"] [$i] ["serviciobase"] = 'N';
                                                    $siAfiliado = 'SI';

                                                    $cupoanteriorcertificados = 0;
                                                    $cuponuevocertificados = 0;
                                                    $incrementocupocertificados = 0;
                                                    $formaCalculoAfiliacion = retornarClaveValorMysqliApi($mysqli, '90.01.60');
                                                    if ($formaCalculoAfiliacion != '') {
                                                        if ($formaCalculoAfiliacion == 'RANGO_VAL_AFI') {
                                                            $arrRan = retornarRegistrosMysqliApi($mysqli, 'mreg_rangos_cupo_afiliacion', "ano='" . date("Y") . "'", "orden");
                                                            foreach ($arrRan as $rx) {
                                                                if ($rx["minimo"] <= $tarifaanteriorafiliacion && $rx["maximo"] >= $tarifaanteriorafiliacion) {
                                                                    $cupoanteriorcertificados = $rx["cupo"];
                                                                }
                                                                if ($rx["minimo"] <= $nuevatarifaafiliacion && $rx["maximo"] >= $nuevatarifaafiliacion) {
                                                                    $cuponuevocertificados = $rx["cupo"];
                                                                }
                                                            }
                                                            unset($arrRan);
                                                            unset($rx);
                                                        } else {
                                                            $cupoanteriorcertificados = round(doubleval($formaCalculoAfiliacion) * $tarifaanteriorafiliacion, 0);
                                                            $cuponuevocertificados = round(doubleval($formaCalculoAfiliacion) * $nuevatarifaafiliacion, 0);
                                                        }
                                                    }
                                                    $incrementocupocertificados = $cuponuevocertificados - $cupoanteriorcertificados;
                                                    $_SESSION ["tramite"] ["incrementocupocertificados"] = $incrementocupocertificados;

                                                    if ($_SESSION ["tramite"] ["liquidacion"] [$i] ["valorservicio"] == 0) {
                                                        unset($_SESSION["tramite"]["liquidacion"][$i]);
                                                        $i = $i - 1;
                                                    }

                                                    \logApi::general2($nameLog, $_SESSION["tramite"]["idliquidacion"], 'Liquido afiliacion');
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
        }


        // ************************************************************ //
        // Liquida formularios - Uno por cada matricula
        // Solo en caso de renovación
        // ************************************************************ //

        if (!defined('RENOVACION_LIQUIDAR_FORMULARIOS_USUPUBXX')) {
            define('RENOVACION_LIQUIDAR_FORMULARIOS_USUPUBXX', 'S');
        }
        $i = count($_SESSION ["tramite"] ["liquidacion"]);
        if ($_SESSION["tramite"]["tipotramite"] == 'renovacionmatricula' ||
                $_SESSION["tramite"]["tipotramite"] == 'renovacionesadl') {

            $arrForms = array();
            $cant = 0;
            $expeini = '';
            if (
                    (RENOVACION_LIQUIDAR_FORMULARIOS == 'S' && $_SESSION["generales"]["escajero"] == 'SI') ||
                    ((RENOVACION_LIQUIDAR_FORMULARIOS_USUPUBXX == 'S' || RENOVACION_LIQUIDAR_FORMULARIOS_USUPUBXX == '') && $_SESSION["generales"]["escajero"] == 'NO')
            ) {
                if (substr(strtoupper($_SESSION ["tramite"] ["incluirformularios"]), 0, 1) == 'S') {
                    $cant = 0;
                    foreach ($_SESSION ["tramite"] ["liquidacion"] as $serv) {
                        if ($serv ["serviciobase"] == 'S') {
                            if (!isset($arrForms [$serv ["expediente"]])) {
                                $arrForms [$serv ["expediente"]] = 0;
                            }
                            $arrForms [$serv ["expediente"]]++;
                            if ($expeini == '') {
                                $expeini = $serv ["expediente"];
                            }
                        }
                    }

                    // Resta un formulario al total, salvo cuando el conteo de uno (1)
                    // Por efectos del primer establecimiento de comercio
                    foreach ($arrForms as $f) {
                        $cant++;
                    }
                    if ($cant > 1) {
                        $cant = $cant - 1;
                    }

                    if ($cant > 0) {
                        $i++;
                        $_SESSION ["tramite"] ["liquidacion"] [$i] ["idsec"] = '000';
                        $_SESSION ["tramite"] ["liquidacion"] [$i] ["expediente"] = $expeini;
                        $_SESSION ["tramite"] ["liquidacion"] [$i] ["nombre"] = '';
                        $_SESSION ["tramite"] ["liquidacion"] [$i] ["organizacion"] = '';
                        $_SESSION ["tramite"] ["liquidacion"] [$i] ["categoria"] = '';
                        if ($_SESSION["tramite"]["tipotramite"] == 'renovacionesadl') {
                            if (defined('RENOVACION_SERV_FORMULARIOS_ESADL') && RENOVACION_SERV_FORMULARIOS_ESADL != '') {
                                $_SESSION ["tramite"] ["liquidacion"] [$i] ["idservicio"] = RENOVACION_SERV_FORMULARIOS_ESADL;
                            } else {
                                $_SESSION ["tramite"] ["liquidacion"] [$i] ["idservicio"] = RENOVACION_SERV_FORMULARIOS;
                            }
                        } else {
                            $_SESSION ["tramite"] ["liquidacion"] [$i] ["idservicio"] = RENOVACION_SERV_FORMULARIOS;
                        }
                        $_SESSION ["tramite"] ["liquidacion"] [$i] ["ano"] = '';
                        $_SESSION ["tramite"] ["liquidacion"] [$i] ["cantidad"] = $cant;
                        $_SESSION ["tramite"] ["liquidacion"] [$i] ["valorbase"] = 0;
                        $_SESSION ["tramite"] ["liquidacion"] [$i] ["porcentaje"] = 0;
                        if ($siAfiliado == 'SI' || $siAfiliado == 'S' || $siAfiliado == '1') {
                            if (!defined('RENOVACION_COBRAR_FORMULARIOS_AFILIADOS')) {
                                define('RENOVACION_COBRAR_FORMULARIOS_AFILIADOS', 'S');
                            }
                            if (RENOVACION_COBRAR_FORMULARIOS_AFILIADOS == 'S') {
                                $_SESSION ["tramite"] ["liquidacion"] [$i] ["valorservicio"] = \funcionesRegistrales::buscaTarifa($mysqli, $_SESSION ["tramite"] ["liquidacion"] [$i] ["idservicio"], 0, $cant, $serv ["valorservicio"], 'tarifa', $liqactprop, $liqtotestnal);
                            } else {
                                $_SESSION ["tramite"] ["liquidacion"] [$i] ["valorservicio"] = 0;
                            }
                        } else {
                            $_SESSION ["tramite"] ["liquidacion"] [$i] ["valorservicio"] = \funcionesRegistrales::buscaTarifa($mysqli, $_SESSION ["tramite"] ["liquidacion"] [$i] ["idservicio"], 0, $cant, $serv ["valorservicio"], 'tarifa', $liqactprop, $liqtotestnal);
                        }
                        $_SESSION ["tramite"] ["valorbruto"] = $_SESSION ["tramite"] ["valorbruto"] + $_SESSION ["tramite"] ["liquidacion"] [$i] ["valorservicio"];
                        $_SESSION ["tramite"] ["liquidacion"] [$i] ["serviciobase"] = 'N';
                        \logApi::general2($nameLog, $_SESSION["tramite"]["idliquidacion"], 'Liquido formularios');
                    }
                }
            }
        }


        // ***********************************************************************//
        // Liquida Fletes
        // Solo en caso de renovaciones
        // Obviamente si se han incluido certificados
        // Solamente si se renuevan todos los años
        // ***********************************************************************//
        $i = count($_SESSION ["tramite"] ["liquidacion"]);
        if ($_SESSION["tramite"]["tipotramite"] == 'renovacionmatricula' || $_SESSION["tramite"]["tipotramite"] == 'renovacionesadl') {

            if (!defined('RENOVACION_VALOR_FLETE')) {
                define('RENOVACION_VALOR_FLETE', '0');
            }
            if (!defined('RENOVACION_SERV_FLETE')) {
                define('RENOVACION_SERV_FLETE', '');
            }

            if (RENOVACION_SERV_FLETE != '') {
                if ($_SESSION ["tramite"] ["incluirfletes"] == 'S') {
                    if ($cantAnos == $cantRenovar) {
                        $i++;
                        $_SESSION ["tramite"] ["liquidacion"] [$i] ["idsec"] = '000';
                        $_SESSION ["tramite"] ["liquidacion"] [$i] ["expediente"] = $_SESSION ["tramite"] ["expedientes"] [1] ["matricula"];
                        $_SESSION ["tramite"] ["liquidacion"] [$i] ["nombre"] = $_SESSION ["tramite"] ["expedientes"] [1] ["razonsocial"];
                        $_SESSION ["tramite"] ["liquidacion"] [$i] ["organizacion"] = $_SESSION ["tramite"] ["expedientes"] [1] ["organizacion"];
                        $_SESSION ["tramite"] ["liquidacion"] [$i] ["categoria"] = $_SESSION ["tramite"] ["expedientes"] [1] ["categoria"];
                        $_SESSION ["tramite"] ["liquidacion"] [$i] ["idservicio"] = RENOVACION_SERV_FLETE;
                        $_SESSION ["tramite"] ["liquidacion"] [$i] ["ano"] = '';
                        $_SESSION ["tramite"] ["liquidacion"] [$i] ["cantidad"] = 1;
                        $_SESSION ["tramite"] ["liquidacion"] [$i] ["valorbase"] = 0;
                        $_SESSION ["tramite"] ["liquidacion"] [$i] ["porcentaje"] = 0;
                        $_SESSION ["tramite"] ["liquidacion"] [$i] ["valorservicio"] = RENOVACION_VALOR_FLETE;
                        $_SESSION ["tramite"] ["valorbruto"] = $_SESSION ["tramite"] ["valorbruto"] + $_SESSION ["tramite"] ["liquidacion"] [$i] ["valorservicio"];
                        $_SESSION ["tramite"] ["liquidacion"] [$i] ["serviciobase"] = 'N';
                        \logApi::general2($nameLog, $_SESSION["tramite"]["idliquidacion"], 'Liquido fletes');
                    }
                }
            }
        }

        // ************************************************************ //
        // Liquida servicios Gravados
        // Servicios que están asociados con otros servicios
        // ************************************************************ //
        $_SESSION ["generales"] ["txtemergente"] = '';
        $i = count($_SESSION ["tramite"] ["liquidacion"]);
        foreach ($_SESSION ["tramite"] ["liquidacion"] as $ind => $serv) {
            if (trim($serv ["idservicio"]) != '') {
                $arrServicio = $arrServs[$serv ["idservicio"]];
                if (!isset($arrServs[$serv ["idservicio"]])) {
                    $_SESSION ["generales"] ["txtemergente"] .= 'El servicio [[' . $serv ["idservicio"] . ']] no est&aacute; definido en la tabla de servicios del SII.\n';
                } else {
                    for ($x = 1; $x <= 7; $x++) {
                        switch ($x) {
                            case 1 :
                                $tempserv = $arrServs[$serv ["idservicio"]] ["idgravado1"];
                                break;
                            case 2 :
                                $tempserv = $arrServs[$serv ["idservicio"]] ["idgravado2"];
                                break;
                            case 3 :
                                $tempserv = $arrServs[$serv ["idservicio"]] ["idgravado3"];
                                break;
                            case 4 :
                                $tempserv = $arrServs[$serv ["idservicio"]] ["idgravado4"];
                                break;
                            case 5 :
                                $tempserv = $arrServs[$serv ["idservicio"]] ["idgravado5"];
                                break;
                            case 6 :
                                $tempserv = $arrServs[$serv ["idservicio"]] ["idgravado6"];
                                break;
                            case 7 :
                                $tempserv = $arrServs[$serv ["idservicio"]] ["idgravado7"];
                                break;
                        }
                        if (trim($tempserv) != '') {
                            $arrServicio = $arrServs[$tempserv];
                            $ok1 = 'si';
                            if (trim($serv ["ano"]) != '') {
                                if (trim($arrServs[$tempserv] ["fechainicial"]) == '') {
                                    $ok1 = 'si';
                                } else {
                                    if (substr($arrServs[$tempserv] ["fechainicial"], 0, 4) <= $serv ["ano"]) {
                                        $ok1 = 'si';
                                    } else {
                                        $ok1 = 'no';
                                    }
                                }
                            } else {
                                $ok1 = 'si';
                            }
                            if ($ok1 == 'si') {
                                $i++;
                                $_SESSION ["tramite"] ["liquidacion"] [$i] ["idsec"] = $serv ["idsec"];
                                $_SESSION ["tramite"] ["liquidacion"] [$i] ["expediente"] = $serv ["expediente"];
                                $_SESSION ["tramite"] ["liquidacion"] [$i] ["nombre"] = $serv ["nombre"];
                                $_SESSION ["tramite"] ["liquidacion"] [$i] ["organizacion"] = '';
                                $_SESSION ["tramite"] ["liquidacion"] [$i] ["categoria"] = '';
                                $_SESSION ["tramite"] ["liquidacion"] [$i] ["idservicio"] = $tempserv;
                                $_SESSION ["tramite"] ["liquidacion"] [$i] ["ano"] = $serv ["ano"];
                                $_SESSION ["tramite"] ["liquidacion"] [$i] ["cantidad"] = 0;
                                $_SESSION ["tramite"] ["liquidacion"] [$i] ["valorbase"] = $serv ["valorservicio"];

                                if ($arrServicio ["idesiva"] == 'S') {
                                    $_SESSION ["tramite"] ["liquidacion"] [$i] ["porcentaje"] = $arrServicio ["valorservicio"];
                                } else {
                                    if (($arrServicio ["idtipovalor"] == '4') || ($arrServicio ["idtipovalor"] == '5')) {
                                        $_SESSION ["tramite"] ["liquidacion"] [$i] ["porcentaje"] = 0;
                                    }
                                }


                                $_SESSION ["tramite"] ["liquidacion"] [$i] ["valorservicio"] = \funcionesRegistrales::buscaTarifa($mysqli, $_SESSION ["tramite"] ["liquidacion"] [$i] ["idservicio"], 0, 1, $serv ["valorservicio"], 'tarifa', $liqactprop, $liqtotestnal);
                                $_SESSION ["tramite"] ["liquidacion"] [$i] ["serviciobase"] = 'N';
                                if ($arrServicio ["idesiva"] == 'S') {
                                    $_SESSION ["tramite"] ["valorbaseiva"] = $_SESSION ["tramite"] ["valorbaseiva"] + $_SESSION ["tramite"] ["liquidacion"] [$i] ["valorbase"];
                                    $_SESSION ["tramite"] ["valoriva"] = $_SESSION ["tramite"] ["valoriva"] + $_SESSION ["tramite"] ["liquidacion"] [$i] ["valorservicio"];
                                } else {
                                    $_SESSION ["tramite"] ["valorbruto"] = $_SESSION ["tramite"] ["valorbruto"] + $_SESSION ["tramite"] ["liquidacion"] [$i] ["valorservicio"];
                                }

                                // 2020-06-08: JINT
                                $_SESSION ["tramite"] ["liquidacion"] [$ind] ["porcentajeiva"] = $arrServicio ["valorservicio"];
                                $_SESSION ["tramite"] ["liquidacion"] [$ind] ["valoriva"] = $_SESSION ["tramite"] ["liquidacion"][$i]["valorservicio"];
                                $_SESSION ["tramite"] ["liquidacion"] [$ind] ["servicioiva"] = $tempserv;
                                \logApi::general2($nameLog, $_SESSION["tramite"]["idliquidacion"], 'Liquido servicios gravados');
                            }
                        }
                    }
                }
            } else {
                $_SESSION ["generales"] ["txtemergente"] .= 'El servicio se encuentra vacio, cuidado, no puede continuar.\n';
            }
        }


//
        if ($_SESSION ["generales"] ["txtemergente"] != '') {
            $_SESSION ["generales"] ["txtemergente"] = 'Hay errores en la parametrizaci&oacute;n de los servicios de renovaci&oacute;n, por favor informe al administrador del portal los errores que a continuaci&oacute;n se describen:\n\n' . $_SESSION ["generales"] ["txtemergente"];
            return false;
        }

        if (RENOVACION_RELIQUIDACION_COMO_MUTACION == 'S') {
            foreach ($_SESSION["tramite"]["expedientes"] as $e) {
                if ($e["reliquidacion"] == 'si' && $e["renovaresteano"] == 'si') {

                    $servx = RENOVACION_SERV_RELIQUIDACION_COMO_MUTACION_15;
                    $ex = retornarRegistroMysqliApi($mysqli, 'mreg_est_inscritos', "matricula='" . $e["matricula"] . "'");

                    if ($ex["ciiu1"] == 'R9200' ||
                            $ex["ciiu2"] == 'R9200' ||
                            $ex["ciiu3"] == 'R9200' ||
                            $ex["ciiu4"] == 'R9200') {
                        $servx = RENOVACION_SERV_RELIQUIDACION_COMO_MUTACION_22;
                    }
                    if ($ex["organizacion"] == '12' && $ex["categoria"] == '1') {
                        $servx = RENOVACION_SERV_RELIQUIDACION_COMO_MUTACION_51;
                    }
                    if ($ex["organizacion"] == '14' && $ex["categoria"] == '1') {
                        $servx = RENOVACION_SERV_RELIQUIDACION_COMO_MUTACION_53;
                    }

                    $i++;
                    $_SESSION ["tramite"] ["liquidacion"] [$i] ["idsec"] = '000';
                    $_SESSION ["tramite"] ["liquidacion"] [$i] ["expediente"] = $e["matricula"];
                    $_SESSION ["tramite"] ["liquidacion"] [$i] ["nombre"] = $e["razonsocial"];
                    $_SESSION ["tramite"] ["liquidacion"] [$i] ["organizacion"] = $e["organizacion"];
                    $_SESSION ["tramite"] ["liquidacion"] [$i] ["categoria"] = $e["categoria"];
                    $_SESSION ["tramite"] ["liquidacion"] [$i] ["idservicio"] = $servx;
                    $_SESSION ["tramite"] ["liquidacion"] [$i] ["ano"] = '';
                    $_SESSION ["tramite"] ["liquidacion"] [$i] ["cantidad"] = 1;
                    $_SESSION ["tramite"] ["liquidacion"] [$i] ["valorbase"] = 0;
                    $_SESSION ["tramite"] ["liquidacion"] [$i] ["porcentaje"] = 0;
                    $_SESSION ["tramite"] ["liquidacion"] [$i] ["valorservicio"] = \funcionesRegistrales::buscaTarifa($mysqli, $servx, 0, 1, 0, 'tarifa', $liqactprop, $liqtotestnal);
                    $_SESSION ["tramite"] ["valorbruto"] = $_SESSION ["tramite"] ["valorbruto"] + $_SESSION ["tramite"] ["liquidacion"] [$i] ["valorservicio"];
                    $_SESSION ["tramite"] ["liquidacion"] [$i] ["serviciobase"] = 'N';
                    \logApi::general2($nameLog, $_SESSION["tramite"]["idliquidacion"], 'Valido renovacion como mutacion');
                }
            }
        }


        // ************************************************************ //
        // Aplica Alertas que estén asociados con la matrícula o con
        // la identificación
        // ************************************************************ //
        $aleTem = array();
        if (ltrim($_SESSION ["tramite"] ["matriculabase"], "0") != '') {
            $aleTem = retornarRegistrosMysqliApi($mysqli, 'mreg_alertas', "matricula='" . trim($_SESSION ["tramite"] ["matriculabase"]) . "' and (idestado<>'AP' and idestado<>'IN') and (tipoalerta='1' or tipoalerta='4')", "fecha");
            if (empty($aleTem)) {
                if (trim($_SESSION ["tramite"] ["identificacionbase"]) != '') {
                    $aleTem = retornarRegistrosMysqliApi($mysqli, 'mreg_alertas', "identificacion='" . $_SESSION ["tramite"] ["identificacionbase"] . "' and (idestado<>'AP' and idestado<>'IN') and (tipoalerta='1' or tipoalerta='4')", "fecha");
                }
            }
        }


        //
        if ($aleTem && !empty($aleTem)) {
            $i = count($_SESSION ["tramite"] ["liquidacion"]);
            foreach ($aleTem as $t) {
                $valalerta = 0;
                $i++;
                $_SESSION ["tramite"] ["liquidacion"] [$i] ["idsec"] = '000';
                $_SESSION ["tramite"] ["liquidacion"] [$i] ["expediente"] = $_SESSION ["tramite"] ["liquidacion"] [1] ["expediente"];
                $_SESSION ["tramite"] ["liquidacion"] [$i] ["nombre"] = $_SESSION ["tramite"] ["liquidacion"] [1] ["nombre"];
                $_SESSION ["tramite"] ["liquidacion"] [$i] ["organizacion"] = $_SESSION ["tramite"] ["liquidacion"] [1] ["organizacion"];
                $_SESSION ["tramite"] ["liquidacion"] [$i] ["categoria"] = $_SESSION ["tramite"] ["liquidacion"] [1] ["categoria"];
                $_SESSION ["tramite"] ["liquidacion"] [$i] ["idservicio"] = $t ["idservicio"];
                $_SESSION ["tramite"] ["liquidacion"] [$i] ["ano"] = '';
                $_SESSION ["tramite"] ["liquidacion"] [$i] ["cantidad"] = 1;
                $_SESSION ["tramite"] ["liquidacion"] [$i] ["valorbase"] = 0;
                $_SESSION ["tramite"] ["liquidacion"] [$i] ["porcentaje"] = 0;
                if ($t ["tipoalerta"] == '1') {
                    $valalerta = $t ["valoralerta"];
                    $_SESSION ["tramite"] ["valorbruto"] = $_SESSION ["tramite"] ["valorbruto"] - $valalerta;
                    $_SESSION ["tramite"] ["liquidacion"] [$i] ["valorservicio"] = $valalerta * - 1;
                }
                if ($t ["tipoalerta"] == '4') {
                    $valalerta = $t ["valoralerta"];
                    $_SESSION ["tramite"] ["liquidacion"] [$i] ["valorservicio"] = $valalerta;
                    $_SESSION ["tramite"] ["valorbruto"] = $_SESSION ["tramite"] ["valorbruto"] + $valalerta;
                }
                $_SESSION ["tramite"] ["liquidacion"] [$i] ["serviciobase"] = 'N';
                $_SESSION ["tramite"] ["liquidacion"] [$i] ["idalerta"] = $t ["id"];
                \logApi::general2($nameLog, $_SESSION["tramite"]["idliquidacion"], 'Aplico alertas');
            }
        }


        // ************************************************************ //
        // Recalcula los valores generales de la liquidacion
        // para prevenir errores
        // ************************************************************ //
        $_SESSION ["tramite"] ["valortotal"] = 0;
        $_SESSION ["tramite"] ["valoriva"] = 0;
        $_SESSION ["tramite"] ["valorbruto"] = 0;
        $_SESSION ["tramite"] ["valorbaseiva"] = 0;
        foreach ($_SESSION ["tramite"] ["liquidacion"] as $serv) {
            $_SESSION ["tramite"] ["valortotal"] = $_SESSION ["tramite"] ["valortotal"] + $serv ["valorservicio"];
            $tmpServ = $arrServs[$serv ["idservicio"]];
            if ($tmpServ ["idesiva"] == 'S') {
                $_SESSION ["tramite"] ["valorbaseiva"] = $_SESSION ["tramite"] ["valorbaseiva"] + $serv ["valorbase"];
                $_SESSION ["tramite"] ["valoriva"] = $_SESSION ["tramite"] ["valoriva"] + $serv ["valorservicio"];
            } else {
                $_SESSION ["tramite"] ["valorbruto"] = $_SESSION ["tramite"] ["valorbruto"] + $serv ["valorservicio"];
            }
        }
        \logApi::general2($nameLog, $_SESSION["tramite"]["idliquidacion"], 'Recalculo liquidacion');
        \logApi::general2($nameLog, $_SESSION["tramite"]["idliquidacion"], '');

        // ************************************************************ //      
        if ($txtFinal != '') {
            $_SESSION["generales"]["txtemergente"] = $txtFinal;
        } else {
            $_SESSION["generales"]["txtemergente"] = '';
        }
        if (!isset($_SESSION["tramite"]["idliquidacion"]) || $_SESSION["tramite"]["idliquidacion"] == '' || $_SESSION["tramite"]["idliquidacion"] == 0) {
            return false;
        }

//
        return true;
    }

}

?>

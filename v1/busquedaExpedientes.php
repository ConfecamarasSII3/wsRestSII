<?php

namespace libreriaswsRestSII;

use libreriaswsRestSII\API;

trait busquedaExpedientes {

    public function busquedaExpedientes(API $api) {
        ini_set('memory_limit', '4096M');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');

        //cantidad de registros
        $limit = 50;

        // array de respuesta
        $_SESSION["jsonsalida"] = array();
        $_SESSION["jsonsalida"]["codigoerror"] = '0000';
        $_SESSION["jsonsalida"]["mensajeerror"] = '';
        $expedientes = array();
        $_SESSION["jsonsalida"]["total"] = '';
        $_SESSION["jsonsalida"]["expedientes"] = $expedientes;
        // Verifica que  método de recepcion de parámetros sea POST
        if ($api->get_request_method() != "POST") {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'La petición debe ser POST';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }
        $api->validarParametro("usuariows", true);
        $api->validarParametro("token", true);
        $api->validarParametro("matriculainicial", false);
        $api->validarParametro("proponenteinicial", false);
        $api->validarParametro("nombreinicial", false);
        $api->validarParametro("palabras", false);
        $api->validarParametro("identificacion", false);
        $api->validarParametro("semilla", true, false);
        $api->validarParametro("ambiente", false, false);

        //
        $_SESSION["entrada"]["semilla"] = intval($_SESSION["entrada"]["semilla"]);

        if (!is_numeric($_SESSION["entrada"]["semilla"])) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Semilla no es un número entero';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        if (trim($_SESSION["entrada"]["matriculainicial"]) == '' &&
                trim($_SESSION["entrada"]["proponenteinicial"]) == '' &&
                trim($_SESSION["entrada"]["nombreinicial"]) == '' &&
                trim($_SESSION["entrada"]["palabras"]) == '' &&
                trim($_SESSION["entrada"]["identificacion"]) == '') {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'No se indicó un parámetro de búsqueda';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        // ********************************************************************** //
        // Valida que el usuario reportado tenga acceso a la BD y al metodo
        // ********************************************************************** // 
        if (!$api->validarToken('busquedaExpedientes', $_SESSION["entrada"]["token"], $_SESSION["entrada"]["usuariows"])) {
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        if (!isset($_SESSION["entrada"]["ambiente"]) || $_SESSION["entrada"]["ambiente"] == '') {
            $_SESSION["entrada"]["ambiente"] = '';
        }

        if ($_SESSION["entrada"]["ambiente"] == '') {
            $mysqli = conexionMysqliApi();
        }
        if ($_SESSION["entrada"]["ambiente"] == 'D') {
            $mysqli = conexionMysqliApi('D-' . $_SESSION["generales"]["codigoempresa"]);
        }
        if ($_SESSION["entrada"]["ambiente"] == 'R') {
            $mysqli = conexionMysqliApi('R-' . $_SESSION["generales"]["codigoempresa"]);
        }
        if ($_SESSION["entrada"]["ambiente"] == 'P') {
            $mysqli = conexionMysqliApi('P-' . $_SESSION["generales"]["codigoempresa"]);
        }
        if ($_SESSION["entrada"]["ambiente"] == 'PP') {
            $mysqli = conexionMysqliApi();
        }

        //
        if ($mysqli === false) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Error de conexion a la BD';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        $tipoOrganizacion = array();
        $resQueryOrg = retornarRegistrosMysqliApi($mysqli, 'bas_organizacionjuridica', "1=1", "id");
        if ($resQueryOrg && !empty($resQueryOrg)) {
            foreach ($resQueryOrg as $orgTemp) {
                $tipoOrganizacion[$orgTemp['id']] = mb_strtoupper($orgTemp['descripcion'], 'utf-8');
            }
        }
        // $resQueryOrg->free();


        $tipoAfiliado = array();
        $tipoAfiliado[0] = "NO AFILIADO";
        $tipoAfiliado[1] = "AFILIACIÓN ACTIVA";
        $tipoAfiliado[2] = "DES-AFILIADO";
        $tipoAfiliado[3] = "ACEPTADO";
        $tipoAfiliado[5] = "DESAFILIACIÓN TEMPORAL";

        $tipoCategoria = array();
        $tipoCategoria[1] = "PRINCIPAL";
        $tipoCategoria[2] = "SUCURSAL";
        $tipoCategoria[3] = "AGENCIA";

        $estadoProponente = array();
        $estadoProponente["00"] = "ACTIVO";
        $estadoProponente["01"] = "CANCELADO";
        $estadoProponente["02"] = "EN ACTUALIZACIÓN";
        $estadoProponente["03"] = "NO RENOVADO";
        $estadoProponente["04"] = "NO ASIGNADO";

        $estadoDatos = array();
        $estadoDatos["0"] = "SIN DATOS";
        $estadoDatos["1"] = "NORMAL";
        $estadoDatos["2"] = "EN DIGITACIÓN";
        $estadoDatos["3"] = "POR VERIFICAR";
        $estadoDatos["4"] = "EN RENOVACIÓN";
        $estadoDatos["5"] = "EN CORRECCIÓN";
        $estadoDatos["6"] = "REVISADO";
        $estadoDatos["7"] = "DOC. TRÁMITE";
        $estadoDatos["8"] = "EN REVISIÓN";

        // ********************************************************************** //
        // Buscar expedientes
        // ********************************************************************** // 
        $arrTem = array();
        $arrExpedientes = array();

        if (trim($_SESSION["entrada"]["matriculainicial"]) != '') {
            $criterio = 1;
        } else if (trim($_SESSION["entrada"]["proponenteinicial"]) != '') {
            $criterio = 2;
        } else if (trim($_SESSION["entrada"]["nombreinicial"]) != '') {
            $criterio = 3;
        } else if (trim($_SESSION["entrada"]["palabras"]) != '') {
            $criterio = 4;
        } else if (trim($_SESSION["entrada"]["identificacion"]) != '') {
            $criterio = 5;
        }

        //$mostrarnomatriculados = 'N';
        $soloestablecimientos = 'N';
        $mostrarcancelados = 'S';
        $relacionestablecimientos = 'N';

        $matbase = trim($_SESSION["entrada"]["matriculainicial"]);
        $propbase = trim($_SESSION["entrada"]["proponenteinicial"]);
        $nombase = trim($_SESSION["entrada"]["nombreinicial"]);
        $palbase = trim($_SESSION["entrada"]["palabras"]);
        $idebase = trim($_SESSION["entrada"]["identificacion"]);

        if ($criterio != '') {
            $consulta["matricula"] = ltrim($matbase, "0");
            $consulta["proponente"] = ltrim($propbase, "0");
            $consulta["nombre"] = trim($nombase);
            $consulta["palabras"] = trim($palbase);
            $consulta["identificacion"] = ltrim(trim($idebase), "0");
            $consulta["retornar"] = $limit;
            $consulta["offset"] = 0;
        }

        $filtroEstablecimiento = '';
        $filtroEstados = '';

        if ($soloestablecimientos == 'S') {
            $filtroEstablecimiento = " and organizacion='02'";
        }
        if ($mostrarcancelados != 'S') {
            $filtroEstados = " and (ctrestmatricula IN ('MA','MI','IA','II',''))";
        }

        if ($_SESSION["entrada"]["semilla"] != '0') {
            $consulta["offset"] = $_SESSION["entrada"]["semilla"] * $limit;
        }

        switch ($criterio) {
            case "1":

                $continuarBusqueda = 'si';

                $matriculaBuscarSII = ltrim($consulta["matricula"], "0");

                if (!is_numeric($matriculaBuscarSII)) {
                    if (strlen(ltrim($consulta["matricula"], "0")) == 8) {
                        if ((substr($matriculaBuscarSII, 0, 1) == 'S') ||
                                (substr($matriculaBuscarSII, 0, 1) == 'N')) {

                            if (!is_numeric(substr($matriculaBuscarSII, 1, 8))) {
                                $continuarBusqueda = 'no';
                            }
                        } else {
                            $continuarBusqueda = 'no';
                        }
                    } else {
                        $continuarBusqueda = 'no';
                    }
                    if ($continuarBusqueda == 'no') {
                        $mysqli->close();
                        $_SESSION["jsonsalida"]["codigoerror"] = "0000";
                        $_SESSION["jsonsalida"]["mensajeerror"] = 'No encontraron registros que cumplan con el criterio indicado';
                        $api->response($api->json($_SESSION["jsonsalida"]), 200);
                    }
                }

                $reg = retornarRegistrosMysqliApi($mysqli, 'mreg_est_inscritos', "matricula>='" . $matriculaBuscarSII . "' " . $filtroEstablecimiento . $filtroEstados, "matricula", "*", $consulta["offset"], $consulta["retornar"]);
                break;
            case "2":
                $proponenteBuscarSII = ltrim($consulta["proponente"], "0");
                if (!is_numeric($proponenteBuscarSII)) {
                    $mysqli->close();
                    $_SESSION["jsonsalida"]["codigoerror"] = "0000";
                    $_SESSION["jsonsalida"]["mensajeerror"] = 'No encontraron registros que cumplan con el criterio indicado';
                    $api->response($api->json($_SESSION["jsonsalida"]), 200);
                }

                $reg = retornarRegistrosMysqliApi($mysqli, 'mreg_est_inscritos', "proponente>='" . $proponenteBuscarSII . "'", "proponente", "*", $consulta["offset"], $consulta["retornar"]);
                break;
            case "3":
                $filtroEstados = " and (trim(ctrestmatricula)!='') and ((trim(ctrestproponente)!='01') OR (ctrestproponente is null))";
                $reg = retornarRegistrosMysqliApi($mysqli, 'mreg_est_inscritos', "razonsocial like '" . $consulta["nombre"] . "%' " . $filtroEstablecimiento . $filtroEstados, "razonsocial", "*", $consulta["offset"], $consulta["retornar"]);
                break;

            case "4":
                $busqueda = "";
                $cantidad_palabras = 0;
                $palabras_busqueda = explode(" ", $consulta["palabras"]);
                foreach ($palabras_busqueda as $palabra) {
                    $cantidad_palabras++;
                    if ($cantidad_palabras == 1) {
                        $busqueda .= "(razonsocial like '%" . $palabra . "%' or sigla like '%" . $palabra . "%')";
                    } else {
                        $busqueda .= " and (razonsocial like '%" . $palabra . "%'  or sigla like '%" . $palabra . "%')";
                    }
                }
                $reg = retornarRegistrosMysqliApi($mysqli, 'mreg_est_inscritos', $busqueda . $filtroEstablecimiento . $filtroEstados, "razonsocial", "*", $consulta["offset"], $consulta["retornar"]);
                break;
            case "5":
                // $filtroEstados = " and (trim(ctrestmatricula)!='') and ((trim(ctrestproponente)!='01') OR (ctrestproponente is null))";
                $filtroEstados = "";
                $reg = retornarRegistrosMysqliApi($mysqli, 'mreg_est_inscritos', "(numid like '" . $consulta["identificacion"] . "%' or nit like '" . $consulta["identificacion"] . "%')" . $filtroEstablecimiento . $filtroEstados, "numid", "*", $consulta["offset"], $consulta["retornar"]);
                break;
            default:
                break;
        }

        $arrTem = array();

        if ($reg === false || empty($reg)) {
            $mysqli->close();
            $_SESSION["jsonsalida"]["codigoerror"] = "0000";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'No encontraron registros que cumplan con el criterio indicado';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
            return array();
        } else {
            $i = -1;
            foreach ($reg as $rg) {

                //
                $rg["saldoaflia"] = 0;
                $rg["ultanorenaflia"] = '';
                $busafi = 'no';
                if ($rg["ctrafiliacion"] == '1') {
                    if ($rg["matricula"] != '') {
                        if ($rg["ctrestmatricula"] == 'MA') {
                            $busafi = 'si';
                        }
                    }
                }

                if ($busafi == 'si') {
                    $formacalculocupoafiliados = retornarClaveValorMysqliApi($mysqli, '90.01.60');
                    if ($formacalculocupoafiliados == 'CANTI_CERTIFICADOS') {
                        $resx1 = \funcionesRegistrales::consultarSaldoAfiliadoCantidadMysqliApi($mysqli, $rg["matricula"]);
                        if ($resx1 && !empty($resx1)) {
                            foreach ($resx1 as $x1) {
                                if ($x1["tipo"] == 'PagoAfiliación' && $rg["ultanorenaflia"] == '') {
                                    $rg["saldoaflia"] = $x1["cupo"];
                                    $rg["ultanorenaflia"] = substr($x1["fecha"], 0, 4);
                                }
                                if ($x1["tipo"] == 'Consumo') {
                                    $rg["saldoaflia"] = $x1["cupo"];
                                }
                            }
                        }
                    } else {
                        $resx1 = \funcionesRegistrales::consultarSaldoAfiliadoMysqliApi($mysqli, $rg["matricula"]);
                        if ($resx1 && !empty($resx1)) {
                            foreach ($resx1 as $x) {
                                if ($x1["tipo"] == 'PagoAfiliación' && $rg["ultanorenaflia"] == '') {
                                    $rg["saldoaflia"] = $x1["cupo"];
                                    //$ultanorenafi = substr($x1["fecha"], 0, 4);
                                }
                                if ($x1["tipo"] == 'Consumo') {
                                    $rg["saldoaflia"] = $x1["cupo"];
                                }
                            }
                        }
                    }
                    unset($resx1);
                }


                $i++;
                $arrTem[$i]["matricula"] = $rg["matricula"];
                $arrTem[$i]["proponente"] = ltrim(trim($rg["proponente"]), "0");
                $arrTem[$i]["tipoidentificacion"] = $rg["idclase"];
                $arrTem[$i]["identificacion"] = $rg["numid"];
                $arrTem[$i]["nombre"] = $rg["razonsocial"];
                $arrTem[$i]["razonsocial"] = $rg["razonsocial"];
                $arrTem[$i]["ape1"] = $rg["apellido1"];
                $arrTem[$i]["ape2"] = $rg["apellido2"];
                $arrTem[$i]["nom1"] = $rg["nombre1"];
                $arrTem[$i]["nom2"] = $rg["nombre2"];
                $arrTem[$i]["organizacion"] = $rg["organizacion"]; //
                $arrTem[$i]["categoria"] = $rg["categoria"]; //
                $arrTem[$i]["fecmat"] = '';
                $arrTem[$i]["fecren"] = '';
                $arrTem[$i]["ultanoren"] = '';
                $arrTem[$i]["afiliacion"] = '';
                if ($rg["matricula"] != '') {
                    $arrTem[$i]["fecmat"] = $rg["fecmatricula"];
                    $arrTem[$i]["fecren"] = $rg["fecrenovacion"];
                    $arrTem[$i]["ultanoren"] = $rg["ultanoren"];
                }

                // 2019-07-09: JINT: Calculo de fechas de renovación y afiliación
                if ($rg["matricula"] != '') {
                    $histopagos = encontrarHistoricoPagosMysqliApi($mysqli, $rg["matricula"], array(), array());
                    if ($histopagos["fecultren"] != '') {
                        $arrTem[$i]["fecren"] = $histopagos["fecultren"];
                        $arrTem[$i]["ultanoren"] = $histopagos["ultanoren"];
                    }
                }

                //
                $arrTem[$i]["afiliacion"] = $rg["ctrafiliacion"];
                $arrTem[$i]["estadomatricula"] = $rg["ctrestmatricula"];
                $arrTem[$i]["estadoproponente"] = $rg["ctrestproponente"];
                $arrTem[$i]["estabs"] = array();
                $arrTem[$i]["embargos"] = '';
                $arrTem[$i]["estadodatosmatricula"] = $rg["ctrestdatos"];
                $arrTem[$i]["fecinsprop"] = '';
                $arrTem[$i]["fecrenprop"] = '';
                $arrTem[$i]["feccanprop"] = '';
                $arrTem[$i]["saldoafiliado"] = 0;
                $arrTem[$i]["dircom"] = $rg["dircom"];
                $arrTem[$i]["muncom"] = $rg["muncom"];
                $arrTem[$i]["telcom1"] = $rg["telcom1"];
                $arrTem[$i]["telcom2"] = $rg["telcom2"];
                $arrTem[$i]["telcom3"] = $rg["telcom3"];
                $arrTem[$i]["emailcom"] = $rg["emailcom"];
                $arrTem[$i]["sigla"] = $rg["sigla"];
                $arrTem[$i]["nit"] = $rg["nit"];
                $arrTem[$i]["saldoafiliado"] = 0;

                //
                if ($busafi == 'si') {
                    $afils = \funcionesRegistrales::consultarSaldoAfiliadoMysqliApi($mysqli, $rg["matricula"]);
                    if ($afils && !empty($afils)) {
                        foreach ($afils as $af) {
                            $arrTem[$i]["saldoafiliado"] = $af["cupo"];
                        }
                    }
                }

                //
                if (ltrim(trim($rg["proponente"]), "0") != '') {
                    $arr1 = retornarRegistroMysqliApi($mysqli, 'mreg_est_proponentes', "proponente='" . ltrim($rg["proponente"], "0") . "'");
                    if ($arr1 && !empty($arr1)) {
                        $arrTem[$i]["fecinsprop"] = $arr1["fechaultimainscripcion"];
                        $arrTem[$i]["fecrenprop"] = $arr1["fechaultimarenovacion"];
                        $arrTem[$i]["feccanprop"] = $arr1["fechacancelacion"];
                    }
                }

                if ($relacionestablecimientos == 'S') {
                    if ($rg["categoria"] != '2' && $rg["categoria"] != '3' && $rg["organizacion"] != '02') {
                        $arr1 = retornarRegistrosMysqliApi($mysqli, 'mreg_est_propietarios', "matriculapropietario='" . $rg["matricula"] . "'", "matricula");
                        if ($arr1 && !empty($arr1)) {
                            $j = 0;
                            foreach ($arr1 as $ar) {
                                $arr2 = retornarRegistroMysqliApi($mysqli, 'mreg_est_inscritos', "matricula='" . $ar["matricula"] . "'");
                                $arrTem[$i]["estabs"][$j]["mat"] = $ar["matricula"];
                                $arrTem[$i]["estabs"][$j]["nom"] = $arr2["razonsocial"];
                                $arrTem[$i]["estabs"][$j]["est"] = $arr2["ctrestmatricula"];
                            }
                        }
                    }
                }

                // Relación de códigos de barras pendientes
                // log::general2($nameLog, $retorno["matricula"], 'Entro a codbarras pendientes : ' . date("His"));
                $retorno["lcodigosbarras"] = array();
                if ($rg["matricula"] != '') {
                    $arrX = retornarRegistrosMysqliApi($mysqli, 'mreg_est_codigosbarras', "matricula='" . $rg["matricula"] . "'", "codigobarras");
                    $ixtra = 0;
                    $ixemb = 0;
                    $ixrec = 0;
                    if ($arrX && !empty($arrX)) {
                        foreach ($arrX as $x) {
                            if ($x["estadofinal"] == '01' || // Radicado
                                    $x["estadofinal"] == '04' || // En estudio
                                    $x["estadofinal"] == '09' || // Reingresado
                                    $x["estadofinal"] == '10' || // Devuelto a reparto
                                    $x["estadofinal"] == '11' || // Inscrito
                                    $x["estadofinal"] == '13' || // Asignado a estudio
                                    $x["estadofinal"] == '22' || // Registrado - proponentes
                                    $x["estadofinal"] == '23' || // En digitacion
                                    $x["estadofinal"] == '34' || // Para firma
                                    $x["estadofinal"] == '35' || // Firmado
                                    $x["estadofinal"] == '38' // Control de calidad
                            ) {
                                $ixtra++;
                                if ($x["actoreparto"] == '07') {
                                    $ixemb++;
                                }
                                if ($x["actoreparto"] == '19' || $x["actoreparto"] == '23' || $x["actoreparto"] == '54') {
                                    $ixrec++;
                                }
                            }
                        }
                    }
                }
                if ($rg["proponente"] != '') {
                    $arrX = retornarRegistrosMysqliApi($mysqli, 'mreg_est_codigosbarras', "proponente='" . $rg["proponente"] . "'", "codigobarras");
                    $ixtra = 0;
                    $ixemb = 0;
                    $ixrec = 0;
                    if ($arrX && !empty($arrX)) {
                        foreach ($arrX as $x) {
                            if ($x["estadofinal"] == '01' || // Radicado
                                    $x["estadofinal"] == '04' || // En estudio
                                    $x["estadofinal"] == '09' || // Reingresado
                                    $x["estadofinal"] == '10' || // Devuelto a reparto
                                    $x["estadofinal"] == '11' || // Inscrito
                                    $x["estadofinal"] == '13' || // Asignado a estudio
                                    $x["estadofinal"] == '22' || // Registrado - proponentes
                                    $x["estadofinal"] == '23' || // En digitacion
                                    $x["estadofinal"] == '34' || // Para firma
                                    $x["estadofinal"] == '35' || // Firmado
                                    $x["estadofinal"] == '38' // Control de calidad
                            ) {
                                $ixtra++;
                                if ($x["actoreparto"] == '07') {
                                    $ixemb++;
                                }
                                if ($x["actoreparto"] == '19' || $x["actoreparto"] == '23' || $x["actoreparto"] == '54') {
                                    $ixrec++;
                                }
                            }
                        }
                    }
                }
                $arrTem[$i]["procesosentramite"] = 'N';
                $arrTem[$i]["embargosentramite"] = 'N';
                $arrTem[$i]["recursosentramite"] = 'N';
                if ($ixtra > 0) {
                    $arrTem[$i]["procesosentramite"] = 'S';
                }
                if ($ixemb > 0) {
                    $arrTem[$i]["embargosentramite"] = 'S';
                }
                if ($ixrec > 0) {
                    $arrTem[$i]["recursosentramite"] = 'S';
                }

                if ($rg["matricula"] != '') {
                    $arrTem[$i]["embargos"] = contarRegistrosMysqliApi($mysqli, 'mreg_est_embargos', "matricula='" . ltrim($rg["matricula"], "0") . "' and acto IN ('0900','0940','1000','1040') and ctrestadoembargo = '1'");
                }

                // 2018-08-27: JINT: Se adiciona que tipo de certificados se pueden expedir, el costo de cada uno y el servicio
                $certificados = array();

                // En caso de matriculados
                if (trim($arrTem[$i]["matricula"]) != '') {

                    // En caso de personas naturales
                    if ($arrTem[$i]["organizacion"] == '01') {
                        $certif = array();
                        $certif["tipocertificado"] = "CerMat";
                        $certif["descripcioncertificado"] = "Certificado de matrícula mercantil";
                        $certif["servicio"] = "01010101";
                        $certif["valor"] = \funcionesRegistrales::buscaTarifa($mysqli, '01010101', date ("Y"), 1);
                        $certificados[] = $certif;
                        $certif = array();
                        $certif["tipocertificado"] = "CerLibRegMer";
                        $certif["descripcioncertificado"] = "Certificado de libros de comercio";
                        $certif["servicio"] = "01010104";
                        $certif["valor"] = \funcionesRegistrales::buscaTarifa($mysqli, '01010104', date ("Y"), 1);
                        $certificados[] = $certif;
                    }

                    // En caso de establecimientos
                    if ($arrTem[$i]["organizacion"] == '02') {
                        $certif = array();
                        $certif["tipocertificado"] = "CerMat";
                        $certif["descripcioncertificado"] = "Certificado de matrícula mercantil";
                        $certif["servicio"] = "01010101";
                        $certif["valor"] = \funcionesRegistrales::buscaTarifa($mysqli, '01010101', date ("Y"), 1);
                        $certificados[] = $certif;
                    }

                    // En caso de personas jurídicas principales
                    if ($arrTem[$i]["organizacion"] > '02' && $arrTem[$i]["organizacion"] != '12' && $arrTem[$i]["organizacion"] != '14') {
                        if ($arrTem[$i]["categoria"] == '1') {
                            $certif = array();
                            $certif["tipocertificado"] = "CerExi";
                            $certif["descripcioncertificado"] = "Certificado de Existencia";
                            $certif["servicio"] = "01010102";
                            $certif["valor"] = \funcionesRegistrales::buscaTarifa($mysqli, '01010102', date ("Y"), 1);
                            $certificados[] = $certif;
                            $certif = array();
                            $certif["tipocertificado"] = "CerMat";
                            $certif["descripcioncertificado"] = "Certificado de matrícula mercantil";
                            $certif["servicio"] = "01010101";
                            $certif["valor"] = \funcionesRegistrales::buscaTarifa($mysqli, '01010101', date ("Y"), 1);
                            $certificados[] = $certif;
                            $certif = array();
                            $certif["tipocertificado"] = "CerLibRegMer";
                            $certif["descripcioncertificado"] = "Certificado de libros de comercio";
                            $certif["servicio"] = "01010104";
                            $certif["valor"] = \funcionesRegistrales::buscaTarifa($mysqli, '01010104', date ("Y"), 1);
                            $certificados[] = $certif;
                        }
                    }

                    // En caso de agencias
                    if ($arrTem[$i]["categoria"] == '3') {
                        $certif = array();
                        $certif["tipocertificado"] = "CerMat";
                        $certif["descripcioncertificado"] = "Certificado de matrícula mercantil";
                        $certif["servicio"] = "01010101";
                        $certif["valor"] = \funcionesRegistrales::buscaTarifa($mysqli, '01010101', date ("Y"), 1);
                        $certificados[] = $certif;
                    }



                    // En caso de sucursales
                    if ($arrTem[$i]["categoria"] == '2') {
                        $certif = array();
                        $certif["tipocertificado"] = "CerMat";
                        $certif["descripcioncertificado"] = "Certificado de matrícula mercantil";
                        $certif["servicio"] = "01010101";
                        $certif["valor"] = \funcionesRegistrales::buscaTarifa($mysqli, '01010101', date ("Y"), 1);
                        $certificados[] = $certif;
                    }

                    // En caso de ESADL principales
                    if ($arrTem[$i]["organizacion"] == '12' || $arrTem[$i]["organizacion"] == '14') {
                        if ($arrTem[$i]["categoria"] == '1') {
                            $certif = array();
                            $certif["tipocertificado"] = "CerEsadl";
                            $certif["descripcioncertificado"] = "Certificado de Existencia";
                            $certif["servicio"] = "01010301";
                            $certif["valor"] = \funcionesRegistrales::buscaTarifa($mysqli, '01010301', date ("Y"), 1);
                            $certificados[] = $certif;
                            $certif = array();
                            $certif["tipocertificado"] = "CerLibRegEsadl";
                            $certif["descripcioncertificado"] = "Certificado de libros de comercio";
                            $certif["servicio"] = "01010104";
                            $certif["valor"] = \funcionesRegistrales::buscaTarifa($mysqli, '01010104', date ("Y"), 1);
                            $certificados[] = $certif;
                        }
                    }
                }

                // Certificado de proponentes
                if (trim($arrTem[$i]["proponente"]) != '') {
                    if ($arrTem[$i]["estadoproponente"] == '00' || $arrTem[$i]["estadoproponente"] == '02') {
                        $certif = array();
                        $certif["tipocertificado"] = "CerPro";
                        $certif["descripcioncertificado"] = "Certificado de proponente";
                        $certif["servicio"] = "01010201";
                        $certif["valor"] = \funcionesRegistrales::buscaTarifa($mysqli, '01010201', date ("Y"), 1);
                        $certificados[] = $certif;
                    }
                }

                //
                $arrTem[$i]["certificados"] = $certificados;
            }
        }

        // **************************************************************************** //
        // Construye salida API
        // **************************************************************************** //


        foreach ($arrTem as $expedienteInfo) {


            $arrayExpresp = array();
            $arrayExpresp["matricula"] = trim($expedienteInfo["matricula"]);
            $arrayExpresp["proponente"] = trim($expedienteInfo["proponente"]);
            $arrayExpresp["nombre"] = trim($expedienteInfo["razonsocial"]);
            $arrayExpresp["sigla"] = trim($expedienteInfo["sigla"]);
            $arrayExpresp["idclase"] = trim($expedienteInfo["tipoidentificacion"]);
            $arrayExpresp["identificacion"] = trim($expedienteInfo["identificacion"]);
            $arrayExpresp["nit"] = trim($expedienteInfo["nit"]);
            $arrayExpresp["organizacion"] = trim($expedienteInfo["organizacion"]);
            $arrayExpresp["organizaciontextual"] = isset($tipoOrganizacion[$arrayExpresp["organizacion"]]) ? $tipoOrganizacion[$arrayExpresp["organizacion"]] : "";
            $arrayExpresp["categoria"] = trim($expedienteInfo["categoria"]);
            $arrayExpresp["categoriatextual"] = isset($tipoCategoria[$arrayExpresp["categoria"]]) ? $tipoCategoria[$arrayExpresp["categoria"]] : "";
            $arrayExpresp["estadodatosmatricula"] = $expedienteInfo["estadodatosmatricula"];
            $arrayExpresp["estadodatosmatriculatextual"] = isset($estadoDatos[$arrayExpresp["estadodatosmatricula"]]) ? $estadoDatos[$arrayExpresp["estadodatosmatricula"]] : "";
            $arrayExpresp["estadomatricula"] = trim($expedienteInfo["estadomatricula"]);
            $arrayExpresp["estadoproponente"] = trim($expedienteInfo["estadoproponente"]);
            $arrayExpresp["estadoproponentetextual"] = isset($estadoProponente[$arrayExpresp["estadoproponente"]]) ? $estadoProponente[$arrayExpresp["estadoproponente"]] : "";
            $arrayExpresp["fechamatricula"] = trim($expedienteInfo["fecmat"]);
            $arrayExpresp["fecharenovacion"] = trim($expedienteInfo["fecren"]);
            $arrayExpresp["ultanorenovado"] = trim($expedienteInfo["ultanoren"]);
            $arrayExpresp["afiliado"] = trim($expedienteInfo["afiliacion"]);
            $arrayExpresp["afiliadotextual"] = isset($tipoAfiliado[$arrayExpresp["afiliado"]]) ? $tipoAfiliado[$arrayExpresp["afiliado"]] : "";
            $arrayExpresp["saldoafiliado"] = trim($expedienteInfo["saldoafiliado"]);
            $arrayExpresp["embargos"] = trim($expedienteInfo["embargos"]);
            $arrayExpresp["direccion"] = trim($expedienteInfo["dircom"]);
            $arrayExpresp["municipio"] = trim($expedienteInfo["muncom"]); //
            $arrayExpresp["municipiotextual"] = retornarNombreMunicipioMysqliApi($mysqli, trim($expedienteInfo["muncom"]));
            $arrayExpresp["telcom1"] = $expedienteInfo["telcom1"];
            $arrayExpresp["telcom2"] = $expedienteInfo["telcom2"];
            $arrayExpresp["telcom3"] = $expedienteInfo["telcom3"];
            $arrayExpresp["emailcom"] = $expedienteInfo["emailcom"];
            $arrayExpresp["procesosentramite"] = $expedienteInfo["procesosentramite"];
            $arrayExpresp["embargosentramite"] = $expedienteInfo["embargosentramite"];
            $arrayExpresp["recursosentramite"] = $expedienteInfo["recursosentramite"];
            $arrayExpresp["establecimientos"] = $expedienteInfo["estabs"];
            $arrayExpresp["certificados"] = $expedienteInfo["certificados"];

            $arrExpedientes[] = $arrayExpresp;
        }
        $_SESSION["jsonsalida"]["total"] = count($arrExpedientes);
        $_SESSION["jsonsalida"]["expedientes"] = $arrExpedientes;

        $mysqli->close();

        // **************************************************************************** //
        // Resultado
        // **************************************************************************** //
        \logApi::peticionRest('api_' . __FUNCTION__);
        $json = $api->json($_SESSION["jsonsalida"]);
        $api->response(str_replace("\\/", "/", $json), 200);
    }

}

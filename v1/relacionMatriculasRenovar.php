<?php

/*
 * Se recibe json con la siguiente información
 * 
 */

namespace libreriaswsRestSII;

use libreriaswsRestSII\API;

trait relacionMatriculasRenovar {

    public function relacionMatriculasRenovar(API $api) {
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/myErrorHandler.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
        $resError = set_error_handler('myErrorHandler');

        //
        $mysqli = conexionMysqliApi();

        // ********************************************************************** //
        // Fecha de corte de renovaciones
        // ********************************************************************** //
        $fcorte = retornarRegistroMysqliApi($mysqli, 'mreg_cortes_renovacion', "ano='" . date("Y") . "'", "corte");


        // ********************************************************************** //
        // array de respuesta
        // ********************************************************************** //
        $_SESSION["jsonsalida"] = array();
        $_SESSION["jsonsalida"]["codigoerror"] = '0000';
        $_SESSION["jsonsalida"]["mensajeerror"] = '';
        $_SESSION["jsonsalida"]["cantidad"] = 0;
        $_SESSION["jsonsalida"]["fecha_servidor"] = '';
        $_SESSION["jsonsalida"]["hora_servidor"] = '';
        $_SESSION["jsonsalida"]["tramite"] = array();
        $_SESSION["jsonsalida"]["alertasAdministrativas"] = array();
        $_SESSION["jsonsalida"]["alertasRegistrales"] = array();
        $_SESSION["jsonsalida"]["controles"] = array();


        // ********************************************************************** //
        // Verifica que  método de recepcion de parámetros sea POST
        // ********************************************************************** //
        if ($api->get_request_method() != "POST" && $api->get_request_method() != "GET") {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'La petición debe ser POST/GET';
            $mysqli->close();
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }
        $api->validarParametro("usuariows", true);
        $api->validarParametro("token", true);
        $api->validarParametro("idusuario", true);
        $api->validarParametro("tipousuario", true, false);
        $api->validarParametro("emailcontrol", true, true);
        $api->validarParametro("identificacioncontrol", true, true);
        $api->validarParametro("celularcontrol", true, true);
        $api->validarParametro("ip", true, true);
        $api->validarParametro("sistemaorigen", true, true);

        //
        $api->validarParametro("idliquidacion", false, false);
        $api->validarParametro("procesartodas", true);
        $api->validarParametro("cancelarmatricula", true);
        $api->validarParametro("benley1780", true);

        $api->validarParametro("matricula", false, false);
        $api->validarParametro("identificacion", false, false);


        if (ltrim($_SESSION["entrada"]["idliquidacion"], "0") != '') {
            if (trim($_SESSION["entrada"]["matricula"]) == '' && trim($_SESSION["entrada"]["identificacion"]) == '') {
                $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                $_SESSION["jsonsalida"]["mensajeerror"] = 'No se recibió número de matrícula ni número de identificación';
                $mysqli->close();
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
            }
        }

        // ********************************************************************** //
        // Valida procesar todas
        // ********************************************************************** //
        if (trim($_SESSION["entrada"]["procesartodas"]) == '') {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'No indicó si se procesa una matrícula o todas las matrículas asociadas';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }
        
        //
        if (trim($_SESSION["entrada"]["procesartodas"]) != 'N' && trim($_SESSION["entrada"]["procesartodas"]) != 'L' && trim($_SESSION["entrada"]["procesartodas"]) != 'S' && trim($_SESSION["entrada"]["procesartodas"]) != 'SP') {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Indicador "procesar todas" erróneo (S, N, L)';
            $mysqli->close();
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        // ********************************************************************** //
        // Valida control de cancelación
        // ********************************************************************** //        
        if (trim($_SESSION["entrada"]["cancelarmatricula"]) != 'SI' && trim($_SESSION["entrada"]["cancelarmatricula"]) != 'NO') {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Indicó en forma errónea si renovará para cancelar o no (' . $_SESSION["entrada"]["cancelarmatricula"] . ')';
            $mysqli->close();
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        // ********************************************************************** //
        // Valida control de beneficio Ley 1780
        // ********************************************************************** //                
        if (trim($_SESSION["entrada"]["benley1780"]) != 'S' && trim($_SESSION["entrada"]["benley1780"]) != 'N' && trim($_SESSION["entrada"]["benley1780"]) != 'P' && trim($_SESSION["entrada"]["benley1780"]) != 'R') {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Control de beneficio de Ley 1780 erróneo';
            $mysqli->close();
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        // ********************************************************************** //
        // Valida versión del SII
        // ********************************************************************** //                
        if (trim($_SESSION["entrada"]["sistemaorigen"]) != 'SII1' && trim($_SESSION["entrada"]["sistemaorigen"]) != 'SII2') {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Control de versionado del sii erróneo';
            $mysqli->close();
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }


        // ********************************************************************** //
        // Valida que se pueda consumir el método
        // ********************************************************************** //
        if (!$api->validarToken('relacionMatriculasRenovar', $_SESSION["entrada"]["token"], $_SESSION["entrada"]["usuariows"])) {
            $mysqli->close();
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        // ************************************************************************ //
        // Arma variables de session
        // ************************************************************************ //        
        $res = \funcionesGenerales::asignarVariablesSession($mysqli, $_SESSION["entrada"]);
        if ($res === false) {
            $mysqli->close();
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Error de autenticacion, usuario no puede consumir el API - problemas de sesion';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        // ************************************************************************ //
        // En caos de no tener número de recuperacion, la crea
        // ************************************************************************ //        
        if (ltrim($_SESSION["entrada"]["idliquidacion"], "0") == '') {
            $_SESSION ["tramite"] = \funcionesRegistrales::retornarMregLiquidacion($mysqli, 0, 'VC');
            $_SESSION ["tramite"]["codigoError"] = '0000';
            $_SESSION ["tramite"]["mensajeError"] = '0000';
            $_SESSION ["tramite"]["matriculabase"] = $_SESSION["entrada"]["matricula"];
            $_SESSION ["tramite"]["idexpedientebase"] = $_SESSION["entrada"]["matricula"];
            $_SESSION ["tramite"]["identificacionbase"] = $_SESSION["entrada"]["identificacion"];
            $_SESSION ["tramite"]["ctrcancelacion"] = $_SESSION["entrada"]["cancelarmatricula"];
            $_SESSION ["tramite"]["procesartodas"] = $_SESSION["entrada"]["procesartodas"];
            $_SESSION ["tramite"]["benley1780"] = $_SESSION["entrada"]["procesartodas"];
            $_SESSION ["tramite"]["reliquidacion"] = 'no';
            $_SESSION ["tramite"]["idestado"] = '01';
            $_SESSION ["tramite"]["idliquidacion"] = \funcionesGenerales::retornarSecuencia($mysqli, 'LIQUIDACION-REGISTROS');
            $_SESSION ["tramite"]["numeroliquidacion"] = $_SESSION ["tramite"]["idliquidacion"];
            $_SESSION ["tramite"]["numerorecuperacion"] = \funcionesGenerales::asignarNumeroRecuperacion($mysqli, 'mreg');
        } else {
            $temx = retornarRegistroMysqliApi($mysqli, 'mreg_liquidacion', "idliquidacion=" . $_SESSION["entrada"]["idliquidacion"]);
            $_SESSION ["tramite"]["idliquidacion"] = $temx["idliquidacion"];
            $_SESSION ["tramite"]["numeroliquidacion"] = $temx["idliquidacion"];
            $_SESSION ["tramite"]["numerorecuperacion"] = $temx["numerorecuperacion"];

            //AQUI WSI 2018-02-19 Para reutilizar las selecciones al modificar liquidación 
            $_SESSION ["tramite"]["numeroempleados"] = $temx["numeroempleados"];
            $_SESSION ["tramite"]["cumplorequisitosbenley1780"] = $temx["cumplorequisitosbenley1780"];
            $_SESSION ["tramite"]["mantengorequisitosbenley1780"] = $temx["mantengorequisitosbenley1780"];
            $_SESSION ["tramite"]["renunciobeneficiosley1780"] = $temx["renunciobeneficiosley1780"];
            $_SESSION ["tramite"]["ctrcancelacion"] = $temx["ctrcancelacion"];
        }

        //
        $retorno = array();
        $retorno["codigoalerta"] = '0000';
        $retorno["mensajealerta"] = '';
        $retorno["idexpedientebase"] = '';
        $retorno["idmatriculabase"] = '';
        $retorno["nombrebase"] = '';
        $retorno["nom1base"] = '';
        $retorno["nom2base"] = '';
        $retorno["ape1base"] = '';
        $retorno["ape2base"] = '';
        $retorno["tipoidentificacionbase"] = '';
        $retorno["identificacionbase"] = '';
        $retorno["organizacionbase"] = '';
        $retorno["categoriabase"] = '';
        $retorno["afiliadobase"] = '';
        $retorno["email"] = '';
        $retorno["direccion"] = '';
        $retorno["telefono"] = '';
        $retorno["movil"] = '';
        $retorno["idmunicipio"] = '';
        $retorno["benley1780"] = '';
        $retorno["cumplorequisitosbenley1780"] = '';
        $retorno["mantengorequisitosbenley1780"] = '';
        $retorno["renunciobeneficiosley1780"] = '';
        $retorno["multadoponal"] = '';
        $retorno["matriculas"] = array();

        //
        $propJurisdiccion = '';
        if ($_SESSION["entrada"]["matricula"] != '') {

            switch (trim($_SESSION["entrada"]["procesartodas"])) {
                case 'S':
                    $tipoData = 'E';
                    break;
                case 'N':
                    $tipoData = 'N';
                    break;
                case 'L':
                    $tipoData = 'N';
                    break;
                default:
                    break;
            }

            $arrTem = \funcionesRegistrales::retornarExpedienteMercantil($mysqli, $_SESSION["entrada"]["matricula"], '', '', '', $tipoData);


            if ($arrTem === false || empty($arrTem)) {
                $mysqli->close();
                $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                $_SESSION["jsonsalida"]["mensajeerror"] = 'Expediente no localizado en el SII';
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
            }
            if ($arrTem["estadomatricula"] != 'MA' &&
                    $arrTem["estadomatricula"] != 'MI' &&
                    $arrTem["estadomatricula"] != 'IA' &&
                    $arrTem["estadomatricula"] != 'II') {
                $mysqli->close();
                $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                $_SESSION["jsonsalida"]["mensajeerror"] = 'El expediente seleccionado no se encuentra activo (registra el estado ' . $arrTem["estadomatricula"] . ')';
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
            }
            if ($arrTem["organizacion"] == '01' || ($arrTem["organizacion"] > '02' && $arrTem["categoria"] == '1')) {
                $retorno["idexpedientebase"] = $_SESSION["entrada"]["matricula"];
                $retorno["idmatriculabase"] = $_SESSION["entrada"]["matricula"];
                $retorno["nombrebase"] = $arrTem["nombre"];
                $retorno["nom1base"] = $arrTem["nom1"];
                $retorno["nom2base"] = $arrTem["nom2"];
                $retorno["ape1base"] = $arrTem["ape1"];
                $retorno["ape2base"] = $arrTem["ape2"];
                $retorno["tipoidentificacionbase"] = $arrTem["tipoidentificacion"];
                $retorno["identificacionbase"] = $arrTem["identificacion"];
                $retorno["organizacionbase"] = $arrTem["organizacion"];
                $retorno["categoriabase"] = $arrTem["categoria"];
                $retorno["afiliadobase"] = $arrTem["afiliado"];
                $retorno["email"] = $arrTem["emailcom"];
                $retorno["direccion"] = $arrTem["dircom"];
                $telcom = '';
                $celcom = '';
                if (strlen($arrTem["telcom1"]) == 7) {
                    $telcom = $arrTem["telcom1"];
                } else {
                    if (strlen($arrTem["telcom1"]) == 10) {
                        $celcom = $arrTem["telcom1"];
                    }
                }
                if (strlen($arrTem["telcom2"]) == 7) {
                    if (trim($telcom) == '') {
                        $telcom = $arrTem["telcom2"];
                    }
                } else {
                    if (strlen($arrTem["telcom2"]) == 10) {
                        if (trim($celcom) == '') {
                            $celcom = $arrTem["telcom2"];
                        }
                    }
                }
                if (strlen($arrTem["celcom"]) == 7) {
                    if (trim($telcom) == '') {
                        $telcom = $arrTem["celcom"];
                    }
                } else {
                    if (strlen($arrTem["celcom"]) == 10) {
                        if (trim($celcom) == '') {
                            $celcom = $arrTem["celcom"];
                        }
                    }
                }
                $retorno["telefono"] = $telcom;
                $retorno["movil"] = $celcom;
                $retorno["idmunicipio"] = $arrTem["muncom"];
                $retorno["benley1780"] = $arrTem["benley1780"];
                $propJurisdiccion = 'S';
            }
        }

        //
        if ($_SESSION["entrada"]["matricula"] == '' && $_SESSION["entrada"]["identificacion"] != '') {
            $arrTemX = retornarRegistrosMysqliApi($mysqli, 'mreg_est_inscritos', "numid like '" . $_SESSION["entrada"]["identificacion"] . "%' or nit like '" . $_SESSION["entrada"]["identificacion"] . "%'", "numid");
            if ($arrTemX === false || empty($arrTemX)) {
                $mysqli->close();
                $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                $_SESSION["jsonsalida"]["mensajeerror"] = 'Identificacion no localizado en el SII (*)';
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
            }
            $arrTem = array();
            foreach ($arrTemX as $t) {
                if (ltrim(trim($t["matricula"]), "0") != '') {
                    if ($t["ctrestmatricula"] == 'MA' || $t["ctrestmatricula"] == 'MI' || $t["ctrestmatricula"] == 'IA' || $t["ctrestmatricula"] == 'II') {
                        if (empty($arrTem)) {
                            $arrTem = \funcionesRegistrales::retornarExpedienteMercantil($mysqli, $t["matricula"], '', '', '', 'N');
                            $propJurisdiccion = 'S';
                            $retorno["idexpedientebase"] = $t["matricula"];
                            $retorno["idmatriculabase"] = $t["matricula"];
                            $retorno["nombrebase"] = $arrTem["nombre"];
                            $retorno["nom1base"] = $arrTem["nom1"];
                            $retorno["nom2base"] = $arrTem["nom2"];
                            $retorno["ape1base"] = $arrTem["ape1"];
                            $retorno["ape2base"] = $arrTem["ape2"];
                            $retorno["tipoidentificacionbase"] = $arrTem["tipoidentificacion"];
                            $retorno["identificacionbase"] = $arrTem["identificacion"];
                            $retorno["organizacionbase"] = $arrTem["organizacion"];
                            $retorno["categoriabase"] = $arrTem["categoria"];
                            $retorno["afiliadobase"] = $arrTem["afiliado"];
                            $retorno["email"] = $arrTem["emailcom"];
                            $retorno["direccion"] = $arrTem["dircom"];
                            $telcom = '';
                            $celcom = '';
                            if (strlen($arrTem["telcom1"]) == 7) {
                                $telcom = $arrTem["telcom1"];
                            } else {
                                if (strlen($arrTem["telcom1"]) == 10) {
                                    $celcom = $arrTem["telcom1"];
                                }
                            }
                            if (strlen($arrTem["telcom2"]) == 7) {
                                if (trim($telcom) == '') {
                                    $telcom = $arrTem["telcom2"];
                                }
                            } else {
                                if (strlen($arrTem["telcom2"]) == 10) {
                                    if (trim($celcom) == '') {
                                        $celcom = $arrTem["telcom2"];
                                    }
                                }
                            }
                            if (strlen($arrTem["celcom"]) == 7) {
                                if (trim($telcom) == '') {
                                    $telcom = $arrTem["celcom"];
                                }
                            } else {
                                if (strlen($arrTem["celcom"]) == 10) {
                                    if (trim($celcom) == '') {
                                        $celcom = $arrTem["celcom"];
                                    }
                                }
                            }
                            $retorno["telefono"] = $telcom;
                            $retorno["movil"] = $celcom;
                            $retorno["idmunicipio"] = $arrTem["muncom"];
                            $retorno["benley1780"] = $arrTem["benley1780"];
                        }
                    }
                }
            }
            if (empty($arrTem)) {
                $mysqli->close();
                $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                $_SESSION["jsonsalida"]["mensajeerror"] = 'Identificacion no localizado en el SII (**)';
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
            }
        }

        // 
        if ($arrTem["organizacion"] == '02') {
            if (count($arrTem["propietarios"]) == 1) {
                if ($arrTem["propietarios"][1]["camarapropietario"] == CODIGO_EMPRESA ||
                        ltrim($arrTem["propietarios"][1]["camarapropietario"], "0") == '') {
                    if ($arrTem["propietarios"][1]["matriculapropietario"] != '') {
                        $arrTem1 = \funcionesRegistrales::retornarExpedienteMercantil($mysqli, ltrim(trim($arrTem["propietarios"][1]["matriculapropietario"]), "0"), '', '', '', 'N');
                        if ($arrTem1 && !empty($arrTem1) && $arrTem1 != 0) {
                            if ($arrTem1["estadomatricula"] == 'MA' ||
                                    $arrTem1["estadomatricula"] == 'MI' ||
                                    $arrTem1["estadomatricula"] == 'IA' ||
                                    $arrTem1["estadomatricula"] == 'II' ||
                                    $arrTem1["estadomatricula"] == 'MC') {
                                $propJurisdiccion = 'S';
                                if ($_SESSION["entrada"]["procesartodas"] == 'S' || $_SESSION["entrada"]["procesartodas"] == 'SP') {
                                    if ($arrTem1["estadomatricula"] != 'MC') {
                                        $arrTem = $arrTem1;
                                        $retorno["idexpedientebase"] = $arrTem1["matricula"];
                                        $retorno["idmatriculabase"] = $arrTem1["matricula"];
                                        $retorno["nombrebase"] = $arrTem1["nombre"];
                                        $retorno["nom1base"] = $arrTem1["nom1"];
                                        $retorno["nom2base"] = $arrTem1["nom2"];
                                        $retorno["ape1base"] = $arrTem1["ape1"];
                                        $retorno["ape2base"] = $arrTem1["ape2"];
                                        $retorno["tipoidentificacionbase"] = $arrTem1["tipoidentificacion"];
                                        $retorno["identificacionbase"] = $arrTem1["identificacion"];
                                        $retorno["organizacionbase"] = $arrTem1["organizacion"];
                                        $retorno["categoriabase"] = $arrTem1["categoria"];
                                        $retorno["afiliadobase"] = $arrTem1["afiliado"];
                                        $retorno["email"] = $arrTem1["emailcom"];
                                        $retorno["direccion"] = $arrTem1["dircom"];
                                        $telcom = '';
                                        $celcom = '';
                                        if (strlen($arrTem1["telcom1"]) == 7) {
                                            $telcom = $arrTem1["telcom1"];
                                        } else {
                                            if (strlen($arrTem1["telcom1"]) == 10) {
                                                $celcom = $arrTem1["telcom1"];
                                            }
                                        }
                                        if (strlen($arrTem1["telcom2"]) == 7) {
                                            if (trim($telcom) == '') {
                                                $telcom = $arrTem1["telcom2"];
                                            }
                                        } else {
                                            if (strlen($arrTem1["telcom2"]) == 10) {
                                                if (trim($celcom) == '') {
                                                    $celcom = $arrTem1["telcom2"];
                                                }
                                            }
                                        }
                                        if (strlen($arrTem1["celcom"]) == 7) {
                                            if (trim($telcom) == '') {
                                                $telcom = $arrTem1["celcom"];
                                            }
                                        } else {
                                            if (strlen($arrTem1["celcom"]) == 10) {
                                                if (trim($celcom) == '') {
                                                    $celcom = $arrTem1["celcom"];
                                                }
                                            }
                                        }
                                        $retorno["telefono"] = $telcom;
                                        $retorno["movil"] = $celcom;
                                        $retorno["idmunicipio"] = $arrTem1["muncom"];
                                        $retorno["benley1780"] = $arrTem1["benley1780"];
                                    }
                                }
                            } else {
                                $propJurisdiccion = 'N';
                            }
                        } else {
                            $propJurisdiccion = 'N';
                        }
                        unset($arrTem1);
                    } else {
                        $propJurisdiccion = 'N';
                    }
                } else {
                    $propJurisdiccion = 'N';
                }
            } else {
                $propJurisdiccion = 'N';
                if (count($arrTem["propietarios"]) > 1) {
                    if ($arrTem["propietarios"][1]["camarapropietario"] == CODIGO_EMPRESA ||
                            ltrim($arrTem["propietarios"][1]["camarapropietario"], "0") == '') {
                        $propJurisdiccion = 'S';
                    }
                }
            }
        }

        // 2017-07-24: JINT: Para determinar si el propietario está dentro o fuera de la jurisdiccion
        // cuanto se trate de sucursales y agencias
        if ($arrTem["organizacion"] > '02' && ($arrTem["categoria"] == '2' || $arrTem["categoria"] == '3')) {
            $propJurisdiccion = 'S';
            if ($arrTem["cpcodcam"] != '00' && $arrTem["cpcodcam"] != CODIGO_EMPRESA) {
                $propJurisdiccion = 'N';
            }
        }


        //
        $i = -1;
        if ($_SESSION["entrada"]["procesartodas"] != 'SP') {
            $i++;
            $retorno["matriculas"][$i]["idtipoidentificacion"] = $arrTem["tipoidentificacion"];
            $retorno["matriculas"][$i]["identificacion"] = $arrTem["identificacion"];
            $retorno["matriculas"][$i]["cc"] = CODIGO_EMPRESA;
            $retorno["matriculas"][$i]["matricula"] = $arrTem["matricula"];
            $retorno["matriculas"][$i]["nombre"] = mb_strtoupper($arrTem["nombre"], 'utf-8');
            $retorno["matriculas"][$i]["ape1"] = $arrTem["ape1"];
            $retorno["matriculas"][$i]["ape2"] = $arrTem["ape2"];
            $retorno["matriculas"][$i]["nom1"] = $arrTem["nom1"];
            $retorno["matriculas"][$i]["nom2"] = $arrTem["nom2"];
            $retorno["matriculas"][$i]["organizacion"] = $arrTem["organizacion"];
            $retorno["matriculas"][$i]["categoria"] = $arrTem["categoria"];
            $retorno["matriculas"][$i]["txtorganizacion"] = retornarRegistroMysqliApi($mysqli, "bas_organizacionjuridica", "id='" . $arrTem["organizacion"] . "'", "descripcion");
            $retorno["matriculas"][$i]["txtcategoria"] = retornarRegistroMysqliApi($mysqli, "bas_categorias", "id='" . $arrTem["categoria"] . "'", "descripcion");
            $retorno["matriculas"][$i]["identificacionpropietario"] = '';
            $retorno["matriculas"][$i]["identificacionrepresentantelegal"] = '';
            if ($arrTem["organizacion"] == '02') {
                $retorno["matriculas"][$i]["identificacionpropietario"] = $arrTem["propietarios"][1]["idtipoidentificacionpropietario"];
            }
            if ($arrTem["organizacion"] > '02') {
                $retorno["matriculas"][$i]["identificacionrepresentantelegal"] = $arrTem["replegal"][1]["identificacionreplegal"];
            }
            $retorno["matriculas"][$i]["ultimoanorenovado"] = $arrTem["ultanoren"];
            if ($arrTem["organizacion"] == '01' || ($arrTem["organizacion"] > '02' && $arrTem["categoria"] == '1')) {
                $retorno["matriculas"][$i]["ultimosactivos"] = $arrTem["acttot"];
            } else {
                $retorno["matriculas"][$i]["ultimosactivos"] = $arrTem["actvin"];
            }
            $retorno["matriculas"][$i]["afiliado"] = $arrTem["afiliado"];
            $retorno["matriculas"][$i]["ultimoanoafiliado"] = $arrTem["ultanorenafi"];
            $retorno["matriculas"][$i]["propietariojurisdiccion"] = $propJurisdiccion;
            $retorno["matriculas"][$i]["disolucion"] = '';
            if ($arrTem["disueltaporvencimiento"] == 'si' || $arrTem["disueltaporacto510"] == 'si') {
                $retorno["matriculas"][$i]["disolucion"] = 'S';
            }
            $retorno["matriculas"][$i]["fechadisolucion"] = $arrTem["fechadisolucion"];
            $retorno["matriculas"][$i]["fechanacimiento"] = $arrTem["fechanacimiento"];
            $retorno["matriculas"][$i]["fechamatricula"] = $arrTem["fechamatricula"];
            $retorno["matriculas"][$i]["fecmatant"] = $arrTem["fecmatant"];
            $retorno["matriculas"][$i]["fecharenovacion"] = $arrTem["fecharenovacion"];
            $retorno["matriculas"][$i]["benart7"] = $arrTem["art7"];
            $retorno["matriculas"][$i]["benley1780"] = $arrTem["benley1780"];
            $retorno["matriculas"][$i]["circular19"] = '';
            $retorno["matriculas"][$i]["municipio"] = $arrTem["muncom"];
            $retorno["matriculas"][$i]["clasegenesadl"] = $arrTem["clasegenesadl"];
            $retorno["matriculas"][$i]["claseespesadl"] = $arrTem["claseespesadl"];
            $retorno["matriculas"][$i]["econsoli"] = $arrTem["claseeconsoli"];
            $retorno["matriculas"][$i]["expedienteinactivo"] = '';
            if ($arrTem["estadomatricula"] == 'MI' || $arrTem["estadomatricula"] == 'II') {
                $retorno["matriculas"][$i]["expedienteinactivo"] = 'S';
            }
            $retorno["matriculas"][$i]["dircom"] = $arrTem["dircom"];
            $retorno["matriculas"][$i]["emailcom"] = $arrTem["emailcom"];
            $retorno["matriculas"][$i]["telcom1"] = $arrTem["telcom1"];
            $retorno["matriculas"][$i]["telcom2"] = $arrTem["telcom2"];
            $retorno["matriculas"][$i]["telcom3"] = $arrTem["celcom"];
            $retorno["matriculas"][$i]["multadoponal"] = '';
        }

        if ($_SESSION["entrada"]["procesartodas"] == 'L' ||
                $_SESSION["entrada"]["procesartodas"] == 'S' ||
                $_SESSION["entrada"]["procesartodas"] == 'SP') {
            if ($arrTem["organizacion"] == '01' || ($arrTem["organizacion"] > '02' && $arrTem["categoria"] == '1')) {
                foreach ($arrTem["establecimientos"] as $est) {
                    $i++;
                    $retorno["matriculas"][$i]["idtipoidentificacion"] = '';
                    $retorno["matriculas"][$i]["identificacion"] = '';
                    $retorno["matriculas"][$i]["cc"] = CODIGO_EMPRESA;
                    $retorno["matriculas"][$i]["matricula"] = $est["matriculaestablecimiento"];
                    $retorno["matriculas"][$i]["nombre"] = $est["nombreestablecimiento"];
                    $retorno["matriculas"][$i]["ape1"] = '';
                    $retorno["matriculas"][$i]["ape2"] = '';
                    $retorno["matriculas"][$i]["nom1"] = '';
                    $retorno["matriculas"][$i]["nom2"] = '';
                    $retorno["matriculas"][$i]["organizacion"] = '02';
                    $retorno["matriculas"][$i]["categoria"] = '';
                    $retorno["matriculas"][$i]["txtorganizacion"] = 'Establecimiento de comercio';
                    $retorno["matriculas"][$i]["txtcategoria"] = '';
                    $retorno["matriculas"][$i]["identificacionpropietario"] = $arrTem["identificacion"];
                    $retorno["matriculas"][$i]["identificacionrepresentantelegal"] = '';
                    $retorno["matriculas"][$i]["ultimoanorenovado"] = $est["ultanoren"];
                    $retorno["matriculas"][$i]["ultimosactivos"] = $est["actvin"];
                    $retorno["matriculas"][$i]["afiliado"] = '';
                    $retorno["matriculas"][$i]["ultimoanoafiliado"] = '';
                    $retorno["matriculas"][$i]["propietariojurisdiccion"] = $propJurisdiccion;
                    $retorno["matriculas"][$i]["disolucion"] = '';
                    $retorno["matriculas"][$i]["fechadisolucion"] = '';
                    $retorno["matriculas"][$i]["fechamatricula"] = $est["fechamatricula"];
                    $retorno["matriculas"][$i]["fecmatant"] = '';
                    $retorno["matriculas"][$i]["fecharenovacion"] = $est["fecharenovacion"];
                    $retorno["matriculas"][$i]["benart7"] = '';
                    $retorno["matriculas"][$i]["benley1780"] = '';
                    $retorno["matriculas"][$i]["circular19"] = '';
                    $retorno["matriculas"][$i]["municipio"] = $est["muncom"];
                    $retorno["matriculas"][$i]["clasegenesadl"] = '';
                    $retorno["matriculas"][$i]["claseespesadl"] = '';
                    $retorno["matriculas"][$i]["econsoli"] = '';
                    $retorno["matriculas"][$i]["expedienteinactivo"] = '';
                    if ($est["estadodatosestablecimiento"] == 'MI') {
                        $retorno["matriculas"][$i]["expedienteinactivo"] = 'S';
                    }
                    $retorno["matriculas"][$i]["dircom"] = $est["dircom"];
                    $retorno["matriculas"][$i]["emailcom"] = $est["emailcom"];
                    $retorno["matriculas"][$i]["telcom1"] = $est["telcom1"];
                    $retorno["matriculas"][$i]["telcom2"] = $est["telcom2"];
                    $retorno["matriculas"][$i]["telcom3"] = $est["telcom3"];
                    $retorno["matriculas"][$i]["multadoponal"] = '';
                }

                foreach ($arrTem["sucursalesagencias"] as $est) {
                    if ($est["estado"] == 'MA' || $est["estado"] == 'MI') {
                        $i++;
                        $retorno["matriculas"][$i]["idtipoidentificacion"] = '';
                        $retorno["matriculas"][$i]["identificacion"] = '';
                        $retorno["matriculas"][$i]["cc"] = CODIGO_EMPRESA;
                        $retorno["matriculas"][$i]["matricula"] = $est["matriculasucage"];
                        $retorno["matriculas"][$i]["nombre"] = $est["nombresucage"];
                        $retorno["matriculas"][$i]["ape1"] = '';
                        $retorno["matriculas"][$i]["ape2"] = '';
                        $retorno["matriculas"][$i]["nom1"] = '';
                        $retorno["matriculas"][$i]["nom2"] = '';
                        $retorno["matriculas"][$i]["organizacion"] = $arrTem["organizacion"];
                        $retorno["matriculas"][$i]["categoria"] = $est["categoria"];
                        $retorno["matriculas"][$i]["txtorganizacion"] = retornarRegistroMysqliApi($mysqli, "bas_organizacionjuridica", "id='" . $arrTem["organizacion"] . "'", "descripcion");
                        $retorno["matriculas"][$i]["txtcategoria"] = retornarRegistroMysqliApi($mysqli, "bas_categorias", "id='" . $est["categoria"] . "'", "descripcion");
                        $retorno["matriculas"][$i]["identificacionpropietario"] = $arrTem["identificacion"];
                        $retorno["matriculas"][$i]["identificacionrepresentantelegal"] = '';
                        $retorno["matriculas"][$i]["ultimoanorenovado"] = $est["ultanoren"];
                        $retorno["matriculas"][$i]["ultimosactivos"] = $est["actvin"];
                        $retorno["matriculas"][$i]["afiliado"] = '';
                        $retorno["matriculas"][$i]["ultimoanoafiliado"] = '';
                        $retorno["matriculas"][$i]["propietariojurisdiccion"] = 'S';
                        $retorno["matriculas"][$i]["disolucion"] = '';
                        $retorno["matriculas"][$i]["fechadisolucion"] = '';
                        $retorno["matriculas"][$i]["fechamatricula"] = $est["fechamatricula"];
                        $retorno["matriculas"][$i]["fecmatant"] = '';
                        $retorno["matriculas"][$i]["fecharenovacion"] = $est["fecharenovacion"];
                        $retorno["matriculas"][$i]["benart7"] = '';
                        $retorno["matriculas"][$i]["benley1780"] = '';
                        $retorno["matriculas"][$i]["circular19"] = '';
                        $retorno["matriculas"][$i]["municipio"] = $est["muncom"];
                        $retorno["matriculas"][$i]["clasegenesadl"] = '';
                        $retorno["matriculas"][$i]["claseespesadl"] = '';
                        $retorno["matriculas"][$i]["econsoli"] = '';
                        $retorno["matriculas"][$i]["expedienteinactivo"] = '';
                        if ($est["estado"] == 'MI') {
                            $retorno["matriculas"][$i]["expedienteinactivo"] = 'S';
                        }
                        $retorno["matriculas"][$i]["dircom"] = $est["dircom"];
                        $retorno["matriculas"][$i]["emailcom"] = $est["emailcom"];
                        $retorno["matriculas"][$i]["telcom1"] = $est["telcom1"];
                        $retorno["matriculas"][$i]["telcom2"] = $est["telcom2"];
                        $retorno["matriculas"][$i]["telcom3"] = $est["telcom3"];
                        $retorno["matriculas"][$i]["multadoponal"] = '';
                    }
                }

                if (!defined('RENOVACION_ACTIVAR_NACIONALES')) {
                    define('RENOVACION_ACTIVAR_NACIONALES', 'N');
                }
                if ($_SESSION["entrada"]["procesartodas"] == 'S' && substr(RENOVACION_ACTIVAR_NACIONALES, 0, 1) == 'S') {
                    $inat = 0;
                    foreach ($arrTem["establecimientosnacionales"] as $est) {
                        $i++;
                        $inat++;
                        $retorno["matriculas"][$i]["idtipoidentificacion"] = '';
                        $retorno["matriculas"][$i]["identificacion"] = '';
                        $retorno["matriculas"][$i]["cc"] = $est["cc"];
                        $retorno["matriculas"][$i]["matricula"] = $est["matriculaestablecimiento"];
                        $retorno["matriculas"][$i]["nombre"] = $est["nombreestablecimiento"];
                        $retorno["matriculas"][$i]["ape1"] = '';
                        $retorno["matriculas"][$i]["ape2"] = '';
                        $retorno["matriculas"][$i]["nom1"] = '';
                        $retorno["matriculas"][$i]["nom2"] = '';
                        $retorno["matriculas"][$i]["organizacion"] = $est["organizacion"];
                        $retorno["matriculas"][$i]["categoria"] = $est["categoria"];
                        $retorno["matriculas"][$i]["txtorganizacion"] = retornarRegistroMysqliApi($mysqli, "bas_organizacionjuridica", "id='" . $est["organizacion"] . "'", "descripcion");
                        $retorno["matriculas"][$i]["txtcategoria"] = retornarRegistroMysqliApi($mysqli, "bas_categorias", "id='" . $est["categoria"] . "'", "descripcion");
                        $retorno["matriculas"][$i]["identificacionpropietario"] = $arrTem["identificacion"];
                        $retorno["matriculas"][$i]["identificacionrepresentantelegal"] = '';
                        $retorno["matriculas"][$i]["ultimoanorenovado"] = $est["ultanoren"];
                        $retorno["matriculas"][$i]["ultimosactivos"] = $est["actvin"];
                        $retorno["matriculas"][$i]["afiliado"] = '';
                        $retorno["matriculas"][$i]["ultimoanoafiliado"] = '';
                        $retorno["matriculas"][$i]["propietariojurisdiccion"] = 'N';
                        $retorno["matriculas"][$i]["disolucion"] = '';
                        $retorno["matriculas"][$i]["fechadisolucion"] = '';
                        $retorno["matriculas"][$i]["fechamatricula"] = $est["fechamatricula"];
                        $retorno["matriculas"][$i]["fecmatant"] = '';
                        $retorno["matriculas"][$i]["fecharenovacion"] = $est["fecharenovacion"];
                        $retorno["matriculas"][$i]["benart7"] = '';
                        $retorno["matriculas"][$i]["benley1780"] = '';
                        $retorno["matriculas"][$i]["circular19"] = '';
                        $retorno["matriculas"][$i]["municipio"] = $est["muncom"];
                        $retorno["matriculas"][$i]["clasegenesadl"] = '';
                        $retorno["matriculas"][$i]["claseespesadl"] = '';
                        $retorno["matriculas"][$i]["econsoli"] = '';
                        $retorno["matriculas"][$i]["expedienteinactivo"] = '';
                        if ($est["estadodatosestablecimiento"] == 'MI') {
                            $retorno["matriculas"][$i]["expedienteinactivo"] = 'S';
                        }
                        $retorno["matriculas"][$i]["dircom"] = $est["dircom"];
                        $retorno["matriculas"][$i]["emailcom"] = $est["emailcom"];
                        $retorno["matriculas"][$i]["telcom1"] = $est["telcom1"];
                        $retorno["matriculas"][$i]["telcom2"] = $est["telcom2"];
                        $retorno["matriculas"][$i]["telcom3"] = $est["telcom3"];
                        $retorno["matriculas"][$i]["multadoponal"] = '';

                        //WSI 2018-02-26 Gestión de datos de establecimientos nacionales asociado a liquidación.
                        if (ltrim($_SESSION["entrada"]["idliquidacion"], "0") != 0) {
                            if ($inat == 1) {
                                borrarRegistrosMysqliApi($mysqli, 'mreg_establecimientos_nacionales', "idliquidacion=" . ltrim($_SESSION["entrada"]["idliquidacion"], "0"));
                            }
                            $arrCampos = array(
                                'idliquidacion',
                                'cc',
                                'matricula',
                                'razonsocial',
                                'organizacion',
                                'categoria',
                                'estado',
                                'fechamatricula',
                                'fecharenovacion',
                                'ultanoren',
                                'dircom',
                                'barriocom',
                                'telcom1',
                                'telcom2',
                                'telcom3',
                                'muncom',
                                'emailcom',
                                'ctrubi',
                                'zonapostalcom',
                                'dirnot',
                                'barrionot',
                                'telnot1',
                                'munnot',
                                'emailnot',
                                'zonapostalnot',
                                'tipolocal',
                                'tipopropietario',
                                'afiliado',
                                'desactiv',
                                'ciiu1',
                                'shd1',
                                'ciiu2',
                                'shd2',
                                'ciiu3',
                                'shd3',
                                'ciiu4',
                                'shd4',
                                'personal',
                                'actvin'
                            );
                            $arrValores = array(
                                "'" . ltrim($_SESSION["entrada"]["idliquidacion"], "0") . "'",
                                "'" . $est["cc"] . "'",
                                "'" . ltrim($est["matriculaestablecimiento"], "0") . "'",
                                "'" . addslashes($est["nombreestablecimiento"]) . "'",
                                "'" . ($est["organizacion"]) . "'",
                                "'" . ($est["categoria"]) . "'",
                                "'" . ($est["estadomatricula"]) . "'",
                                "'" . ($est["fechamatricula"]) . "'",
                                "'" . ($est["fecharenovacion"]) . "'",
                                "'" . ($est["ultanoren"]) . "'",
                                "'" . addslashes($est["dircom"]) . "'",
                                "'" . addslashes($est["nbarriocom"]) . "'",
                                "'" . ($est["telcom1"]) . "'",
                                "'" . ($est["telcom2"]) . "'",
                                "'" . ($est["telcom3"]) . "'",
                                "'" . ($est["muncom"]) . "'",
                                "'" . addslashes($est["emailcom"]) . "'",
                                "'" . ($est["ctrubi"]) . "'",
                                "'" . ($est["codpostalcom"]) . "'",
                                "'" . addslashes($est["dirnot"]) . "'",
                                "'" . addslashes($est["nbarrionot"]) . "'",
                                "''",
                                "'" . ($est["munnot"]) . "'",
                                "'" . addslashes($est["emailnot"]) . "'",
                                "'" . ($est["codpostalnot"]) . "'",
                                "'" . ($est["tipolocal"]) . "'",
                                "'" . ($est["tipopropietario"]) . "'",
                                "'" . ($est["afiliado"]) . "'",
                                "'" . addslashes($est["desactiv"]) . "'",
                                "'" . ($est["ciiu1"]) . "'",
                                "'" . ($est["shd1"]) . "'",
                                "'" . ($est["ciiu2"]) . "'",
                                "'" . ($est["shd2"]) . "'",
                                "'" . ($est["ciiu3"]) . "'",
                                "'" . ($est["shd3"]) . "'",
                                "'" . ($est["ciiu4"]) . "'",
                                "'" . ($est["shd4"]) . "'",
                                intval($est["personal"]),
                                doubleval($est["actvin"])
                            );
                            $res = insertarRegistrosmysqliApi($mysqli, 'mreg_establecimientos_nacionales', $arrCampos, $arrValores);
                            if ($res == false) {
                                $mysqli->close();
                                $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                                $_SESSION["jsonsalida"]["mensajeerror"] = 'Error grabado establecimientos nacionales' . $_SESSION["generales"]["mensajeerror"];
                                $api->response($api->json($_SESSION["jsonsalida"]), 200);
                            }
                        }
                        //FIN WSI
                    }
                }
            }
        }

        // Confirma si tiene o no beneficio de la Ley 1780
        // Aplica para el 2017
        if (date("Y") == '2017') {
            if ($retorno["matriculas"][0]["organizacion"] == '01' ||
                    ($retorno["matriculas"][0]["organizacion"] > '02' &&
                    $retorno["matriculas"][0]["categoria"] == '1')) {
                if ($retorno["matriculas"][0]["fechamatricula"] >= '20160502') {
                    if ($retorno["matriculas"][0]["benley1780"] == '') {
                        if (contarRegistrosMysqliApi($mysqli, 'mreg_est_recibos', "matricula='" . $retorno["matriculas"][0]["matricula"] . "' and servicio='01090110' and (fecoperacion between '20160101' and '20161231')") > 0) {
                            $retorno["matriculas"][0]["benley1780"] = 'S';
                            $retorno["matriculas"]["benley1780"] = 'S';
                        }
                    }
                }
            }
        }

        // Aplica para el 2018
        if (date("Y") == '2018') {
            if ($retorno["matriculas"][0]["organizacion"] == '01' || ($retorno["matriculas"][0]["organizacion"] > '02' && $retorno["matriculas"][0]["categoria"] == '1')) {
                if ($retorno["matriculas"][0]["fechamatricula"] >= '20170101') {
                    if ($retorno["matriculas"][0]["benley1780"] == '') {
                        if (contarRegistrosMysqliApi($mysqli, 'mreg_est_recibos', "matricula='" . $retorno["matriculas"][0]["matricula"] . "' and servicio='01090110' and (fecoperacion between '20170101' and '20171231')") > 0) {
                            $retorno["matriculas"][0]["benley1780"] = 'S';
                            $retorno["matriculas"]["benley1780"] = 'S';
                        }
                    }
                }
            }
        }

        // Aplica para el 2019
        if (date("Y") == '2019') {
            if ($retorno["matriculas"][0]["organizacion"] == '01' || ($retorno["matriculas"][0]["organizacion"] > '02' && $retorno["matriculas"][0]["categoria"] == '1')) {
                if ($retorno["matriculas"][0]["fechamatricula"] >= '20180101') {
                    if ($retorno["matriculas"][0]["benley1780"] == '') {
                        if (contarRegistrosMysqliApi($mysqli, 'mreg_est_recibos', "matricula='" . $retorno["matriculas"][0]["matricula"] . "' and servicio='01090110' and (fecoperacion between '20180101' and '20181231')") > 0) {
                            $retorno["matriculas"][0]["benley1780"] = 'S';
                            $retorno["matriculas"]["benley1780"] = 'S';
                        }
                    }
                }
            }
        }

        //
        // Verifica codigo de policia
        if (!defined('ACTIVAR_CONTROL_MULTAS_PONAL')) {
            define('ACTIVAR_CONTROL_MULTAS_PONAL', 'NO');
        }

        //
        if (ACTIVAR_CONTROL_MULTAS_PONAL == 'SI-NOBLOQUEAR') {
            if ($retorno["matriculas"][0]["organizacion"] == '01' && $_SESSION["entrada"]["procesartodas"] != 'SP') {
                $resx = \funcionesGenerales::consultarMultasPolicia($mysqli, $retorno["matriculas"][0]["idtipoidentificacion"], $retorno["matriculas"][0]["identificacion"], $_SESSION ["tramite"]["idliquidacion"]);
                if ($resx == 'SI') {
                    $retorno["matriculas"][0]["multadoponal"] = 'S';
                    $retorno["multadoponal"] = 'S';
                } else {
                    $retorno["matriculas"][0]["multadoponal"] = 'N';
                    $retorno["multadoponal"] = 'N';
                }
            }
        }

        //
        if (ACTIVAR_CONTROL_MULTAS_PONAL == 'SI-BLOQUEAR') {
            if ($retorno["matriculas"][0]["organizacion"] == '01' && $_SESSION["entrada"]["procesartodas"] != 'SP') {
                $resx = \funcionesGenerales::consultarMultasPolicia($mysqli, $retorno["matriculas"][0]["idtipoidentificacion"], $retorno["matriculas"][0]["identificacion"], $_SESSION ["tramite"]["idliquidacion"]);
                if ($resx == 'SI') {
                    if (isset($retorno["matriculas"][1])) {
                        $_SESSION["jsonsalida"]["codigoerror"] = '5000';
                        $_SESSION["jsonsalida"]["mensajeerror"] = 'Se ha encontrado que la identificación ' . $retorno["matriculas"][0]["identificacion"] . ' ';
                        $_SESSION["jsonsalida"]["mensajeerror"] .= 'tiene multas reportadas al Registro Nacional de Medidas Correctivas (RNMC) administrado ';
                        $_SESSION["jsonsalida"]["mensajeerror"] .= 'por la Policía Nacional y alguna(s) de ella(s) se encuentra(n) vencida(s) - ';
                        $_SESSION["jsonsalida"]["mensajeerror"] .= 'No ha(n) sido pagada(s).<br><br>';
                        $_SESSION["jsonsalida"]["mensajeerror"] .= 'Para realizar la renovación de la matrícula asociada con la identificación ' . $retorno["matriculas"][0]["identificacion"] . ' ';
                        $_SESSION["jsonsalida"]["mensajeerror"] .= 'se deberá(n) pagar la(s) misma(s) y esperar a que sea(n) registrada(s) en el ';
                        $_SESSION["jsonsalida"]["mensajeerror"] .= 'Sistema Nacional de Medidas Correctiva.<br><br>';
                        $_SESSION["jsonsalida"]["mensajeerror"] .= '<a href="' . TIPO_HTTP . HTTP_HOST . '/librerias/proceso/verMultas.php?idliquidacion=' . $_SESSION ["tramite"]["idliquidacion"] . '" target="_blank">Ver Las multas</a><br><br>';
                        $_SESSION["jsonsalida"]["mensajeerror"] .= 'Si desea continuar con el proceso de renovación de las matrículas asociadas ';
                        $_SESSION["jsonsalida"]["mensajeerror"] .= 'a la identificación ' . $retorno["matriculas"][0]["identificacion"] . ' oprima el ';
                        $_SESSION["jsonsalida"]["mensajeerror"] .= 'siguiente enlace<br><br>';
                        $_SESSION["jsonsalida"]["mensajeerror"] .= '<a href="' . TIPO_HTTP . HTTP_HOST . '/librerias/proceso/mregRenovacionMatriculaSii1.php?accion=validarseleccionnuevasinpropietario&procesartodas=SP&matricula=' . $retorno["matriculas"][0]["matricula"] . '&identificacion=' . $retorno["matriculas"][0]["identificacion"] . '&cancelarmatricula=' . $_SESSION["entrada"]["cancelarmatricula"] . '&benley1780=' . $_SESSION["entrada"]["benley1780"] . '">Renovar establecimientos sucursales y agencias</a><br><br>';
                        $_SESSION["jsonsalida"]["matriculas"] = array();
                    } else {
                        $_SESSION["jsonsalida"]["codigoerror"] = '5000';
                        $_SESSION["jsonsalida"]["mensajeerror"] = 'Se ha encontrado que la identificación ' . $retorno["matriculas"][0]["identificacion"] . ' ';
                        $_SESSION["jsonsalida"]["mensajeerror"] .= 'tiene multas reportadas al Registro Nacional de Medidas Correctivas (RNMC) administrado ';
                        $_SESSION["jsonsalida"]["mensajeerror"] .= 'por la Policía Nacional y alguna(s) de ella(s) se encuentra(n) vencida(s) - ';
                        $_SESSION["jsonsalida"]["mensajeerror"] .= 'No ha(n) sido pagada(s).<br><br>';
                        $_SESSION["jsonsalida"]["mensajeerror"] .= 'Para realizar la renovación de la matrícula asociada con la identificación ' . $retorno["matriculas"][0]["identificacion"] . ' ';
                        $_SESSION["jsonsalida"]["mensajeerror"] .= 'se deberá(n) pagar la(s) misma(s) y esperar a que sea(n) registrada(s) en el ';
                        $_SESSION["jsonsalida"]["mensajeerror"] .= 'Sistema Nacional de Medidas Correctiva.<br><br>';
                        $_SESSION["jsonsalida"]["mensajeerror"] .= '<a href="' . TIPO_HTTP . HTTP_HOST . '/librerias/proceso/verMultas.php?idliquidacion=' . $_SESSION ["tramite"]["idliquidacion"] . '" target="_blank">Ver Las multas</a><br><br>';
                        $_SESSION["jsonsalida"]["mensajeerror"] .= 'No es posible continuar con el proceso de renovación. ';
                        $_SESSION["jsonsalida"]["mensajeerror"] .= 'Para volver al módulo de renovación y seleccionar otro expediente ';
                        $_SESSION["jsonsalida"]["mensajeerror"] .= 'oprima el siguiente enlace.<br><br>';
                        $_SESSION["jsonsalida"]["mensajeerror"] .= '<a href="' . TIPO_HTTP . HTTP_HOST . '/librerias/proceso/mregRenovacionMatriculaSii1.php?accion=pantallaseleccion">Abandonar renovación</a><br><br>';
                        $_SESSION["jsonsalida"]["matriculas"] = array();
                    }
                } else {
                    $retorno["matriculas"][0]["multadoponal"] = 'N';
                    $retorno["multadoponal"] = 'N';
                }
            }
        }

        //
        // Datos básicos del trámite
        $_SESSION["tramite"]["emailcontrol"] = $_SESSION["generales"]["emailusuariocontrol"];
        $_SESSION["tramite"]["idexpedientebase"] = $retorno["idexpedientebase"];
        $_SESSION["tramite"]["idmatriculabase"] = $retorno["idmatriculabase"];
        $_SESSION["tramite"]["nombrebase"] = $retorno["nombrebase"];
        $_SESSION["tramite"]["nom1base"] = $retorno["nom1base"];
        $_SESSION["tramite"]["nom2base"] = $retorno["nom2base"];
        $_SESSION["tramite"]["ape1base"] = $retorno["ape1base"];
        $_SESSION["tramite"]["ape2base"] = $retorno["ape2base"];
        $_SESSION["tramite"]["tipoidentificacionbase"] = $retorno["tipoidentificacionbase"];
        $_SESSION["tramite"]["identificacionbase"] = $retorno ["identificacionbase"];
        $_SESSION["tramite"]["organizacionbase"] = $retorno["organizacionbase"];
        $_SESSION["tramite"]["categoriabase"] = $retorno["categoriabase"];
        $_SESSION["tramite"]["afiliadobase"] = $retorno["afiliadobase"];

        // Datos del cliente
        $_SESSION["tramite"]["idtipoidentificacioncliente"] = $_SESSION["tramite"]["tipoidentificacionbase"];
        $_SESSION["tramite"]["identificacioncliente"] = $_SESSION["tramite"]["identificacionbase"];
        $_SESSION["tramite"]["nombrecliente"] = $_SESSION["tramite"]["nombrebase"];
        $_SESSION["tramite"]["nombre1cliente"] = $_SESSION["tramite"]["nom1base"];
        $_SESSION["tramite"]["nombre2cliente"] = $_SESSION["tramite"]["nom2base"];
        $_SESSION["tramite"]["apellido1cliente"] = $_SESSION["tramite"]["ape1base"];
        $_SESSION["tramite"]["apellido2cliente"] = $_SESSION["tramite"]["ape2base"];

        $_SESSION["tramite"]["email"] = $retorno ["email"];
        $_SESSION["tramite"]["direccion"] = $retorno["direccion"];
        $_SESSION["tramite"]["idmunicipio"] = $retorno["idmunicipio"];

        if ($_SESSION["tramite"]["benley1780"] == 'S') {
            $_SESSION["tramite"]["benley1780"] = $retorno["benley1780"];
        }

        // 2017-12-16: JINT: Multado ponal
        if ($retorno["multadoponal"] == 'S') {
            $_SESSION["tramite"]["multadoponal"] = 'S';
        } else {
            $_SESSION["tramite"]["multadoponal"] = 'N';
        }

        $_SESSION ["tramite"]["telefono"] = $retorno["telefono"];
        $_SESSION ["tramite"]["movil"] = $retorno["movil"];

        //
        if ($_SESSION["tramite"]["idtipoidentificacioncliente"] == '2') {
            $_SESSION["tramite"]["tipocliente"] = 'PJ';
        } else {
            $_SESSION["tramite"]["tipocliente"] = 'PN';
        }

        $_SESSION["tramite"]["razonsocialcliente"] = $_SESSION["tramite"]["nombrecliente"];

        // Datos del pagador
        if ($_SESSION["tramite"]["idtipoidentificacioncliente"] == '2') {
            $_SESSION["tramite"]["tipopagador"] = 'PJ';
        } else {
            $_SESSION["tramite"]["tipopagador"] = 'PN';
        }
        $_SESSION["tramite"]["tipoidentificacionpagador"] = $_SESSION["tramite"]["idtipoidentificacioncliente"];
        $_SESSION["tramite"]["identificacionpagador"] = $_SESSION["tramite"]["identificacioncliente"];

        $_SESSION["tramite"]["razonsocialpagador"] = $_SESSION["tramite"]["razonsocialcliente"];
        $_SESSION["tramite"]["nombre1pagador"] = $_SESSION["tramite"]["nombre1cliente"];
        $_SESSION["tramite"]["nombre2pagador"] = $_SESSION["tramite"]["nombre2cliente"];
        $_SESSION["tramite"]["apellido1pagador"] = $_SESSION["tramite"]["apellido1cliente"];
        $_SESSION["tramite"]["apellido2pagador"] = $_SESSION["tramite"]["apellido2cliente"];
        $_SESSION["tramite"]["telefonopagador"] = $_SESSION ["tramite"]["telefono"];
        $_SESSION["tramite"]["movilpagador"] = $_SESSION ["tramite"]["movil"];
        $_SESSION["tramite"]["emailpagador"] = $_SESSION ["tramite"]["email"];

        /**
         * Verifica que no existan procesos previos de liquidación para la matrícula que estén en proceso de pago electrónico
         * Valida la tabla mreg_liquidacion:
         * tipo de trámite "renovacionmatricula" o "renovacionesadl"
         * Número de identificacion = número de identificación base
         * Estado = '06'
         * idexpediente = Numero de matrícula seleccionada
         */
        if (trim($_SESSION ["tramite"]["idexpedientebase"], '0') != '') {
            $condicion = "idexpedientebase='" . ltrim(trim($_SESSION["tramite"]["idexpedientebase"]), '0') . "' and ";
            $condicion .= "(tipotramite='renovacionmatricula' or tipotramite='renovacionesadl') and ";
            $condicion .= "idestado='06'";

            //
            $cantidad = contarRegistrosMysqliApi($mysqli, 'mreg_liquidacion', $condicion);
            if ($cantidad > 0) {
                $regliq = retornarRegistroMysqliApi($mysqli, 'mreg_liquidacion', $condicion);
                if (($regliq === false) || (empty($regliq))) {
                    $num1rec = '';
                } else {
                    $num1rec = trim($regliq ["numerorecuperacion"]);
                }
                unset($regliq);
                $mysqli->close();
                $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                $_SESSION ["jsonsalida"]["mensajeerror"] = 'No se puede iniciar la operación debido a que el número de referencia o número de factura utilizado se ';
                $_SESSION ["jsonsalida"]["mensajeerror"] .= 'encuentra actualmente asociado a otro proceso de pago iniciado previamente, por  favor espere unos minutos e ';
                $_SESSION ["jsonsalida"]["mensajeerror"] .= 'intente nuevamente hasta que el sistema obtenga el resultado final de la transacción.';
                if ($num1rec != '') {
                    $_SESSION ["jsonsalida"]["mensajeerror"] .= ' Número de recuperación: ' . $num1rec;
                }
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
            }
        }

        // Verifica que las matriculas no esten inactivas
        $caninactivas = 0;
        if (!defined('FECHA_CONTROL_INACTIVAS')) {
            define('FECHA_CONTROL_INACTIVAS', '20140601');
        }
        if (date("Ymd") >= FECHA_CONTROL_INACTIVAS) {
            $matinactiva = '';
            $nominactiva = '';
            $caninactivas = 0;
            foreach ($retorno["matriculas"] as $m) {
                if ($m ["expedienteinactivo"] == '1' || $m ["expedienteinactivo"] == 'S') {
                    $caninactivas ++;
                    if ($caninactivas == 1) {
                        $matinactiva = $m ["matricula"];
                        $nominactiva = $m ["nombre"];
                    }
                }
            }
        }



        // Inicializa arreglo del tramite
        $_SESSION ["tramite"]["caninactivas"] = $caninactivas;
        $_SESSION ["tramite"]["fecha"] = date("Ymd");
        $_SESSION ["tramite"]["hora"] = date("H:i:s");
        $_SESSION ["tramite"]["idusuario"] = $_SESSION ["entrada"]["idusuario"];
        $_SESSION ["tramite"]["tipotramite"] = "renovacionmatricula";
        if (($retorno["matriculas"][0]["organizacion"] == '12') || ($retorno["matriculas"][0]["organizacion"] == '14')) {
            if ($retorno["matriculas"][0]["categoria"] == '1') {
                $_SESSION ["tramite"]["tipotramite"] = "renovacionesadl";
            }
        }
        $_SESSION ["tramite"]["iptramite"] = $_SESSION["entrada"]["ip"];
        $_SESSION ["tramite"]["idestado"] = '01';
        $_SESSION ["tramite"]["idexpedientebase"] = $_SESSION ["tramite"]["matriculabase"];
        if (substr($_SESSION ["generales"]["tipousuario"], 0, 2) == '06') {
            $_SESSION ["tramite"]["idtipoidentificacioncliente"] = ltrim($_SESSION ["generales"]["idtipoidentificacionusuario"], '0');
            $_SESSION ["tramite"]["identificacioncliente"] = ltrim($_SESSION ["generales"]["identificacionusuario"], '0');
            $_SESSION ["tramite"]["nombrecliente"] = $_SESSION ["generales"]["nombreusuario"];
            $_SESSION ["tramite"]["email"] = $_SESSION ["generales"]["emailusuario"];
            $_SESSION ["tramite"]["direccion"] = $_SESSION ["generales"]["direccionusuario"];
            $_SESSION ["tramite"]["idmunicipio"] = $_SESSION ["generales"]["idmunicipiousuario"];
            $_SESSION ["tramite"]["telefono"] = $_SESSION ["generales"]["telefonousuario"];
            $_SESSION ["tramite"]["movil"] = $_SESSION ["generales"]["movilusuario"];
        }

        //
        $i = 0;

        //
        $candidatoreafiliacion = "no";
        $porrenovar = 0;
        $disueltos = 0;
        $circular19 = 0;
        $_SESSION["tramite"]["bloquear5anios"] = 'no';
        $inactivas = '';

        $ix1 = 0;
        foreach ($retorno["matriculas"] as $matricula) {

            if ($matricula["expedienteinactivo"] == 'S') {
                $inactivas .= $matricula["matricula"] . ' ';
            }

            // 2017-06-07 : JINT : AlertaTemprana
            $ix1++;
            if ($ix1 == 1) {
                \funcionesGenerales::programarAlertaTemprana($mysqli, 'RegMer', $_SESSION["tramite"]["idliquidacion"], $matricula["matricula"], '', 'renovacion');
            }

            //
            $ultimo = $matricula ["ultimoanorenovado"];

            // 2016-03-31 : JINT: Control para bloqueo de los últimos 5 años
            if ((date("Y") - $ultimo) >= 5) {
                $_SESSION["tramite"]["bloquear5anios"] = 'si';
            }

            //
            if (($matricula ["organizacion"] != '02') && ($matricula ["categoria"] != '2') && ($matricula ["categoria"] != '3')) {
                if ($_SESSION["tramite"]["ctrcancelacion"] == 'SI') {
                    if (date("Ymd") > $fcorte) {
                        $actual = intval(date("Y"));
                    } else {
                        if ($ultimo < date("Y") - 1) {
                            $actual = intval(date("Y")) - 1;
                        } else {
                            $actual = $ultimo;
                        }
                    }
                } else {
                    if ($matricula ["disolucion"] == 'S') {
                        $actual = date("Y");
                        $disueltos = 1;
                    } else {
                        $actual = date("Y");
                    }
                }
            } else {
                if ($_SESSION ["tramite"]["ctrcancelacion"] == 'SI') {
                    if (date("Ymd") > $fcorte) {
                        $actual = intval(date("Y"));
                    } else {
                        if ($ultimo < date("Y") - 1) {
                            $actual = intval(date("Y")) - 1;
                        } else {
                            $actual = $ultimo;
                        }
                    }
                } else {
                    $actual = date("Y");
                }
            }

            //
            if ($ultimo == $actual) {
                $_SESSION["tramite"]["reliquidacion"] = 'si';
            }

            if (($matricula ["organizacion"] != '02') && ($matricula ["categoria"] != '2') && ($matricula ["categoria"] != '3')) {
                $anosdebe = 0;
                // $beneficiario = 'N';
                $beneficiario = $matricula ["benart7"];
                for ($j = $ultimo + 1; $j <= $actual; $j ++) {
                    $anosdebe ++;
                    if ($j == $actual) {
                        $beneficiario = $matricula ["benart7"];
                    }
                }
                if ($beneficiario == "S") {
                    if ($anosdebe > 1) {
                        $beneficiario = 'P';
                    } else {
                        if ((date("Ymd") <= $fcorte || $_SESSION ["tramite"]["reliquidacion"] == 'si')) {
                            $ben1780okx = '';
                        } else {
                            $beneficiario = 'P';
                        }
                    }
                }
            } else {
                $beneficiario = 'N';
            }

            if (($matricula ["organizacion"] != '02') && ($matricula ["categoria"] != '2') && ($matricula ["categoria"] != '3')) {
                $anosdebe = 0;
                $beneficiario1780 = $matricula ["benley1780"];
                for ($j = $ultimo + 1; $j <= $actual; $j ++) {
                    $anosdebe ++;
                    if ($j == $actual) {
                        $beneficiario1780 = $matricula ["benley1780"];
                    }
                }
                if ($beneficiario1780 == 'S') {
                    if ($anosdebe > 1) {
                        $beneficiario1780 = 'P';
                    } else {
                        // 2017-11-26: JINT: Se incluye este control para permitir hacer pruebas de Ley 1780
                        // en los ambientes de pruebas y en fechas posteriores al 2017-03-31
                        if (TIPO_AMBIENTE == 'PRUEBAS' || TIPO_AMBIENTE == 'QA') {
                            $ben1780okx = '';
                        } else {
                            if ((date("Ymd") <= $fcorte || $_SESSION ["tramite"]["reliquidacion"] == 'si')) {
                                $ben1780okx = '';
                            } else {
                                $beneficiario1780 = 'P';
                            }
                        }
                    }
                }
                // }
            } else {
                $beneficiario1780 = 'N';
            }


            //
            // Control incluido en mayo 29 de 2013
            // Por solicitud de la CC Neiva
            if ($matricula ["afiliado"] == 'E' || $matricula ["afiliado"] == 'D') {
                if (intval($matricula ["ultimoanoafiliado"]) == ($actual - 1)) {
                    $candidatoreafiliacion = "si";
                }
            }

            if ($ultimo < $actual) {
                for ($j = $ultimo + 1; $j <= $actual; $j ++) {
                    $incluir = 'si';
                    if (($matricula["organizacion"] == '12' ||
                            $matricula["organizacion"] == '14') && $matricula["categoria"] == '1') {
                        if ($j < '2013') {
                            $incluir = 'no';
                        }
                    }
                    if ($incluir == 'si') {
                        $temp = array();
                        $porrenovar ++;
                        $i ++;
                        if ($j == $actual) {
                            $temp ["registrobase"] = 'S';
                        } else {
                            $temp ["registrobase"] = 'N';
                        }
                        $temp ["cc"] = $matricula ["cc"];
                        $temp ["textocc"] = retornarRegistroMysqliApi($mysqli, "bas_camaras", "id='" . $matricula ["cc"] . "'", "nombre");
                        $temp ["matricula"] = $matricula ["matricula"];
                        $temp ["proponente"] = '';
                        $temp ["numrue"] = '';
                        $temp ["idtipoidentificacion"] = $matricula ["idtipoidentificacion"];
                        $temp ["identificacion"] = ltrim($matricula ["identificacion"], '0');
                        $temp ["razonsocial"] = mb_strtoupper($matricula["nombre"], 'utf-8');
                        $temp ["ape1"] = $matricula ["ape1"];
                        $temp ["ape2"] = $matricula ["ape2"];
                        $temp ["nom1"] = $matricula ["nom1"];
                        $temp ["nom2"] = $matricula ["nom2"];
                        $temp ["organizacion"] = $matricula ["organizacion"];
                        $temp ["categoria"] = $matricula ["categoria"];
                        $temp ["txtorganizacion"] = retornarRegistroMysqliApi($mysqli, "bas_organizacionjuridica", "id='" . $matricula ["organizacion"] . "'", "descripcion");
                        $temp ["txtcategoria"] = '';
                        switch ($temp["categoria"]) {
                            case "1" :$temp ["txtcategoria"] = 'Principal';
                                break;
                            case "2" :$temp ["txtcategoria"] = 'Sucursal';
                                break;
                            case "3" :$temp ["txtcategoria"] = 'Agencia';
                                break;
                        }

                        $temp ["identificacionpropietario"] = $matricula ["identificacionpropietario"];
                        $temp ["identificacionrepresentantelegal"] = $matricula ["identificacionrepresentantelegal"];
                        $temp ["afiliado"] = $matricula ["afiliado"];
                        if ($j == $actual) {
                            $temp ["ultimoanoafiliado"] = $matricula ["ultimoanoafiliado"];
                            if ($matricula ["ultimoanoafiliado"] == $actual) {
                                $temp ["afiliado"] = 'N';
                            }
                        } else {
                            $temp ["ultimoanoafiliado"] = '0000';
                        }
                        $temp ["ultimoanorenovado"] = $j;
                        if ($temp ["registrobase"] == 'S') {
                            $temp ["primeranorenovado"] = $ultimo + 1;
                        } else {
                            $temp ["primeranorenovado"] = '';
                        }
                        $temp ["propietariojurisdiccion"] = $matricula ["propietariojurisdiccion"];
                        $temp ["ultimosactivos"] = $matricula ["ultimosactivos"];
                        // Arma arreglo de expedientes

                        $filtro = "idliquidacion=" . $_SESSION["entrada"]["idliquidacion"] . " and matricula=" . $temp["matricula"] . " and ultimoanorenovado=" . $temp["ultimoanorenovado"];

                        $arrExpLiq = retornarRegistroMysqliApi($mysqli, "mreg_liquidacionexpedientes", $filtro, '*');

                        if (!$arrExpLiq || empty($arrExpLiq)) {
                            $temp ["nuevosactivos"] = 0;
                            $temp ["fechanacimiento"] = '';
                            $temp ["reliquidacion"] = '';
                            $temp ["renovaresteano"] = '';
                        } else {
                            $temp ["nuevosactivos"] = $arrExpLiq["nuevosactivos"];
                            $temp ["fechanacimiento"] = $arrExpLiq["fechanacimiento"];
                            $temp ["reliquidacion"] = $arrExpLiq["reliquidacion"];
                            $temp ["renovaresteano"] = $arrExpLiq["renovaresteano"];
                        }
                        unset($arrExpLiq);

                        $temp ["actividad"] = '';
                        $temp ["benart7"] = $beneficiario;
                        $temp ["benley1780"] = $beneficiario1780;
                        $temp ["disolucion"] = $matricula ["disolucion"];
                        $temp ["fechadisolucion"] = $matricula ["fechadisolucion"];
                        $temp ["fechamatricula"] = $matricula ["fechamatricula"];
                        $temp ["fecmatant"] = $matricula ["fecmatant"];
                        $temp ["fecharenovacion"] = $matricula ["fecharenovacion"];
                        $temp ["clasegenesadl"] = $matricula ["clasegenesadl"];
                        $temp ["claseespesadl"] = $matricula ["claseespesadl"];
                        $temp ["econsoli"] = $matricula ["econsoli"];

                        $selsi = '';
                        $selno = '';
                        $selin = '';
                        if ($matricula ["disolucion"] == 'S') {
                            $fcorte1 = retornarRegistroMysqliApi($mysqli, 'mreg_cortes_renovacion', "ano='" . $j . "'", "corte");
                            if ($matricula ["fechadisolucion"] <= $fcorte1) {
                                $temp ["renovaresteano"] = 'no';
                                $selno = 'S';
                            } else {
                                $selsi = 'S';
                            }
                        } else {
                            //WSI 2018-02-26
                            if (trim($temp ["renovaresteano"]) == 'no') {
                                $selno = 'S';
                            } else {
                                $selsi = 'S';
                            }
                        }

                        //
                        $temp["renovaresteanosii2"] = array();
                        $temp["renovaresteanosii2"][] = array(
                            'label' => 'SI',
                            'val' => 'si',
                            'selected' => $selsi,
                            'name' => 'renovaresteano'
                        );
                        $temp["renovaresteanosii2"][] = array(
                            'label' => 'NO',
                            'val' => 'no',
                            'selected' => $selno,
                            'name' => 'renovaresteano'
                        );
                        if ($_SESSION["generales"]["escajero"] == 'SI') {
                            $temp["renovaresteanosii2"][] = array(
                                'label' => 'INACT',
                                'val' => 'in',
                                'selected' => $selin,
                                'name' => 'renovaresteano'
                            );
                        }
                        //
                        $_SESSION ["tramite"]["expedientes"][] = $temp;
                    }
                }
            } else {

                if ($_SESSION ["tramite"]["ctrcancelacion"] != 'SI') {
                    $_SESSION ["tramite"]["reliquidacion"] = 'si';
                    $i ++;
                    $temp = array();
                    $temp ["registrobase"] = 'S';
                    $temp ["cc"] = $matricula ["cc"];
                    $temp ["textocc"] = retornarRegistroMysqliApi($mysqli, "bas_camaras", "id='" . $matricula ["cc"] . "'", "nombre");
                    $temp ["matricula"] = $matricula ["matricula"];
                    $temp ["proponente"] = '';
                    $temp ["numrue"] = '';
                    $temp ["idtipoidentificacion"] = $matricula ["idtipoidentificacion"];
                    $temp ["identificacion"] = ltrim($matricula ["identificacion"], '0');
                    $temp ["razonsocial"] = mb_strtoupper($matricula["nombre"], 'utf-8');
                    $temp ["ape1"] = $matricula ["ape1"];
                    $temp ["ape2"] = $matricula ["ape2"];
                    $temp ["nom1"] = $matricula ["nom1"];
                    $temp ["nom2"] = $matricula ["nom2"];
                    $temp ["organizacion"] = $matricula ["organizacion"];
                    $temp ["categoria"] = $matricula ["categoria"];
                    $temp ["identificacionpropietario"] = $matricula ["identificacionpropietario"];
                    $temp ["identificacionrepresentantelegal"] = $matricula ["identificacionrepresentantelegal"];
                    $temp ["afiliado"] = $matricula ["afiliado"];
                    $temp ["ultimoanoafiliado"] = $matricula ["ultimoanoafiliado"];
                    $temp ["ultimoanorenovado"] = $actual;
                    $temp ["primeranorenovado"] = $actual;
                    $temp ["propietariojurisdiccion"] = $matricula ["propietariojurisdiccion"];
                    $temp ["ultimosactivos"] = $matricula ["ultimosactivos"];
                    $temp ["nuevosactivos"] = 0;
                    $temp ["actividad"] = '';
                    $temp ["benart7"] = $beneficiario;
                    $temp ["benley1780"] = $beneficiario1780;
                    $temp ["disolucion"] = $matricula ["disolucion"];
                    $temp ["fechadisolucion"] = $matricula ["fechadisolucion"];
                    $temp ["fechamatricula"] = $matricula ["fechamatricula"];
                    $temp ["fecmatant"] = $matricula ["fecmatant"];
                    $temp ["fecharenovacion"] = $matricula ["fecharenovacion"];
                    $temp ["clasegenesadl"] = $matricula ["clasegenesadl"];
                    $temp ["claseespesadl"] = $matricula ["claseespesadl"];
                    $temp ["econsoli"] = $matricula ["econsoli"];
                    $temp ["reliquidacion"] = 'si';
                    $temp ["renovaresteano"] = 'si';
                    $selsi = '';
                    $selno = '';
                    $selin = '';
                    if ($matricula ["disolucion"] == 'S') {
                        $fcorte1 = retornarRegistroMysqliApi($mysqli, 'mreg_cortes_renovacion', "ano='" . $j . "'", "corte");
                        if ($matricula ["fechadisolucion"] <= $fcorte1) {
                            $temp ["renovaresteano"] = 'no';
                            $selno = 'S';
                        } else {
                            $selsi = 'S';
                        }
                    } else {
                        //WSI 2018-02-26
                        if (trim($temp ["renovaresteano"]) == 'no') {
                            $selno = 'S';
                        } else {
                            $selsi = 'S';
                        }
                    }

                    //
                    $temp["renovaresteanosii2"] = array();
                    $temp["renovaresteanosii2"][] = array(
                        'label' => 'SI',
                        'val' => 'si',
                        'selected' => $selsi,
                        //'selected' => '',
                        'name' => 'renovaresteano'
                    );
                    $temp["renovaresteanosii2"][] = array(
                        'label' => 'NO',
                        'val' => 'no',
                        'selected' => $selno,
                        'name' => 'renovaresteano'
                    );
                    if ($_SESSION["generales"]["escajero"] == 'SI') {
                        $temp["renovaresteanosii2"][] = array(
                            'label' => 'INACT',
                            'val' => 'in',
                            'selected' => $selin,
                            'name' => 'renovaresteano'
                        );
                    }
                    $_SESSION ["tramite"]["expedientes"][] = $temp;
                }
            }
            if ($ix1 == 1) {
                $tempBase = $temp;
            }
        }

        // ********************************************************************* //
        // Salva la liquidacion, toma como base $_SESSION["tramite"]
        // ********************************************************************* //
        \funcionesRegistrales::grabarLiquidacionMreg($mysqli);

        //        
        if ($_SESSION["jsonsalida"]["codigoerror"] == '0000') {
            if ($inactivas != '') {
                $_SESSION["jsonsalida"]["codigoerror"] = '5000';
                $_SESSION["jsonsalida"]["mensajeerror"] = '!!! IMPORTANTE !!!<br><br>';
                $_SESSION["jsonsalida"]["mensajeerror"] .= 'Se han encontrado matrículas inactivas (' . trim($inactivas) . '), estsa deben ser reactivadas ';
                $_SESSION["jsonsalida"]["mensajeerror"] .= 'antes de continuar con el proceso de renovación.';
                $mysqli->close();
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
            }
            if ($porrenovar == 0) {
                if ($_SESSION ["tramite"]["reliquidacion"] == 'si') {
                    $_SESSION["jsonsalida"]["codigoerror"] = '4000';
                    $_SESSION["jsonsalida"]["mensajeerror"] = '!!! IMPORTANTE !!!<br><br>';
                    $_SESSION["jsonsalida"]["mensajeerror"] .= 'Los expedientes se encuentran al día (renovados), por lo tanto si continua con el proceso<br>';
                    $_SESSION["jsonsalida"]["mensajeerror"] .= 'realizará una RELIQUIDACION DE ACTIVOS<br>';
                } else {
                    if ($_SESSION ["tramite"]["ctrcancelacion"] == 'SI') {
                        if (date("Ymd") <= $fcorte) {
                            $_SESSION["jsonsalida"]["codigoerror"] = '4000';
                            $_SESSION["jsonsalida"]["mensajeerror"] = '!!! IMPORTANTE !!!<br><br>';
                            $_SESSION["jsonsalida"]["mensajeerror"] .= 'El usuario  ha  marcado que  renovará para cancelar pero<br>';
                            $_SESSION["jsonsalida"]["mensajeerror"] .= 'el expediente  se   encuentra  al  día  en  su  renovación,<br>';
                            $_SESSION["jsonsalida"]["mensajeerror"] .= 'Dado que aún no ha pasado el ' . \funcionesGenerales::mostrarFechaLetras1($fcorte) . ', no es obligatorio ';
                            $_SESSION["jsonsalida"]["mensajeerror"] .= 'que renueve.<br>';
                        } else {
                            $_SESSION["jsonsalida"]["codigoerror"] = '4000';
                            $_SESSION["jsonsalida"]["mensajeerror"] = '!!! IMPORTANTE !!!<br><br>';
                            $_SESSION["jsonsalida"]["mensajeerror"] .= 'Aparentemente no se encontraron a&ntilde;os para renovar,<br>';
                            $_SESSION["jsonsalida"]["mensajeerror"] .= 'por favor  informe  este hecho al administrador de<br>';
                            $_SESSION["jsonsalida"]["mensajeerror"] .= 'la Cámara de Comercio si considera que es un error.<br>';
                        }
                    } else {
                        $_SESSION["jsonsalida"]["codigoerror"] = '4000';
                        $_SESSION["jsonsalida"]["mensajeerror"] = '!!! IMPORTANTE !!!<br><br>';
                        $_SESSION["jsonsalida"]["mensajeerror"] .= 'Aparentemente no se encontraron a&ntilde;os para renovar,<br>';
                        $_SESSION["jsonsalida"]["mensajeerror"] .= 'por favor  informe  este hecho al administrador de<br>';
                        $_SESSION["jsonsalida"]["mensajeerror"] .= 'la Cámara de Comercio si considera que es un error.<br>';
                        $_SESSION["jsonsalida"]["mensajeerror"] .= 'Recuerde que en caso de personas jurídicas disueltas<br>';
                        $_SESSION["jsonsalida"]["mensajeerror"] .= 'y en liquidación, no deben renovar.<br>';
                    }
                }
            } else {
                if ($disueltos == 1) {
                    $_SESSION["jsonsalida"]["codigoerror"] = '4000';
                    $_SESSION["jsonsalida"]["mensajeerror"] = '!!! IMPORTANTE !!!<br><br>';
                    $_SESSION["jsonsalida"]["mensajeerror"] .= 'En nuestros registros aparece que la matrícula de la persona jurídica<br>';
                    $_SESSION["jsonsalida"]["mensajeerror"] .= 'se encuentra disuelta, por lo tanto y de acuerdo como lo estipula la Ley,<br>';
                    $_SESSION["jsonsalida"]["mensajeerror"] .= 'no tiene obligación de renovar los a&ntilde;os durante los cuales se encuentre en<br>';
                    $_SESSION["jsonsalida"]["mensajeerror"] .= 'dicho estado. Sin embargo mientras el establecimiento de comercio esté<br>';
                    $_SESSION["jsonsalida"]["mensajeerror"] .= 'abierto al público, por el mismo se deberá pagar la renovación.<br>';
                }
            }

            //
            if ($_SESSION["tramite"]["bloquear5anios"] == 'si') {
                if ($_SESSION["generales"]["tipousuario"] == '00' || $_SESSION["generales"]["tipousuario"] == '06') {
                    $_SESSION["jsonsalida"]["codigoerror"] = '5000';
                    $_SESSION["jsonsalida"]["mensajeerror"] = '!!! IMPORTANTE !!!<br><br>';
                    $_SESSION["jsonsalida"]["mensajeerror"] .= 'No es posible continuar con la renovacion, el expediente tiene mas de 5 años sin haber renovado.<br><br>';
                } else {
                    $_SESSION["jsonsalida"]["codigoerror"] = '6000';
                    $_SESSION["jsonsalida"]["mensajeerror"] = 'Se ha encontrado que existen matrículas asociadas al trámite que tienen más de 5 años sin haber renovado, ';
                    $_SESSION["jsonsalida"]["mensajeerror"] .= 'Si está seguro de continuar, por favor oprima el siguiente enlace<br><br>';
                    $_SESSION["jsonsalida"]["mensajeerror"] .= '<a href="' . TIPO_HTTP . HTTP_HOST . '/librerias/proceso/mregRenovacionMatriculaSii1.php?accion=continuarrenovacion5anios">Continuar con la renovación</a>';
                }
            }
        }

        // ***************************************************************************************
        // Arma el json de salida
        // ***************************************************************************************
        $_SESSION["jsonsalida"]["cantidad"] = 0;
        $_SESSION["jsonsalida"]["fecha_servidor"] = date("Ymd");
        $_SESSION["jsonsalida"]["hora_servidor"] = date("His");
        $_SESSION["jsonsalida"]["tramite"] = $_SESSION["tramite"];

        $i = -1;
        foreach ($retorno["matriculas"] as $m) {
            $i++;
            $_SESSION["jsonsalida"]["matriculas"][$i]["idtipoidentificacion"] = $m["idtipoidentificacion"];
            $_SESSION["jsonsalida"]["matriculas"][$i]["identificacion"] = $m["identificacion"];
            $_SESSION["jsonsalida"]["matriculas"][$i]["cc"] = $m["cc"];
            $_SESSION["jsonsalida"]["matriculas"][$i]["textocc"] = retornarRegistroMysqliApi($mysqli, "bas_camaras", "id='" . $m["cc"] . "'", "nombre");
            $_SESSION["jsonsalida"]["matriculas"][$i]["matricula"] = $m["matricula"];
            $_SESSION["jsonsalida"]["matriculas"][$i]["nombre"] = $m["nombre"];
            $_SESSION["jsonsalida"]["matriculas"][$i]["ape1"] = $m["ape1"];
            $_SESSION["jsonsalida"]["matriculas"][$i]["ape2"] = $m["ape2"];
            $_SESSION["jsonsalida"]["matriculas"][$i]["nom1"] = $m["nom1"];
            $_SESSION["jsonsalida"]["matriculas"][$i]["nom2"] = $m["nom2"];
            $_SESSION["jsonsalida"]["matriculas"][$i]["organizacion"] = $m["organizacion"];
            $_SESSION["jsonsalida"]["matriculas"][$i]["txtorganizacion"] = retornarRegistroMysqliApi($mysqli, "bas_organizacionjuridica", "id='" . $m["organizacion"] . "'", "descripcion");
            $_SESSION["jsonsalida"]["matriculas"][$i]["categoria"] = $m["categoria"];
            $_SESSION["jsonsalida"]["matriculas"][$i]["txtcategoria"] = '';
            switch ($m["categoria"]) {
                case "1": $_SESSION["jsonsalida"]["matriculas"][$i]["txtcategoria"] = 'Persona jurídica principal';
                    break;
                case "2": $_SESSION["jsonsalida"]["matriculas"][$i]["txtcategoria"] = 'Sucursal';
                    break;
                case "3": $_SESSION["jsonsalida"]["matriculas"][$i]["txtcategoria"] = 'Agencia';
                    break;
            }
            $_SESSION["jsonsalida"]["matriculas"][$i]["identificacionpropietario"] = $m["identificacionpropietario"];
            $_SESSION["jsonsalida"]["matriculas"][$i]["identificacionrepresentantelegal"] = $m["identificacionrepresentantelegal"];
            $_SESSION["jsonsalida"]["matriculas"][$i]["ultimoanorenovado"] = $m["ultimoanorenovado"];
            $_SESSION["jsonsalida"]["matriculas"][$i]["ultimosactivos"] = $m["ultimosactivos"];
            $_SESSION["jsonsalida"]["matriculas"][$i]["afiliado"] = $m["afiliado"];
            $_SESSION["jsonsalida"]["matriculas"][$i]["ultimoanoafiliado"] = $m["ultimoanoafiliado"];
            $_SESSION["jsonsalida"]["matriculas"][$i]["propietariojurisdiccion"] = $m["propietariojurisdiccion"];
            $_SESSION["jsonsalida"]["matriculas"][$i]["disolucion"] = $m["disolucion"];
            $_SESSION["jsonsalida"]["matriculas"][$i]["fechadisolucion"] = $m["fechadisolucion"];
            $_SESSION["jsonsalida"]["matriculas"][$i]["fechanacimiento"] = isset($m["fechanacimiento"]) ? $m["fechanacimiento"] : '';
            $_SESSION["jsonsalida"]["matriculas"][$i]["fechamatricula"] = $m["fechamatricula"];
            $_SESSION["jsonsalida"]["matriculas"][$i]["fecmatant"] = $m["fecmatant"];
            $_SESSION["jsonsalida"]["matriculas"][$i]["fecharenovacion"] = $m["fecharenovacion"];
            $_SESSION["jsonsalida"]["matriculas"][$i]["benart7"] = $m["benart7"];
            $_SESSION["jsonsalida"]["matriculas"][$i]["benley1780"] = $m["benley1780"];
            $_SESSION["jsonsalida"]["matriculas"][$i]["circular19"] = $m["circular19"];
            $_SESSION["jsonsalida"]["matriculas"][$i]["municipio"] = $m["municipio"];
            $_SESSION["jsonsalida"]["matriculas"][$i]["clasegenesadl"] = $m["clasegenesadl"];
            $_SESSION["jsonsalida"]["matriculas"][$i]["claseespesadl"] = $m["claseespesadl"];
            $_SESSION["jsonsalida"]["matriculas"][$i]["econsoli"] = $m["econsoli"];
            $_SESSION["jsonsalida"]["matriculas"][$i]["expedienteinactivo"] = $m["expedienteinactivo"];
            $_SESSION["jsonsalida"]["matriculas"][$i]["dircom"] = $m["dircom"];
            $_SESSION["jsonsalida"]["matriculas"][$i]["emailcom"] = $m["emailcom"];
            $_SESSION["jsonsalida"]["matriculas"][$i]["telcom1"] = $m["telcom1"];
            $_SESSION["jsonsalida"]["matriculas"][$i]["telcom2"] = $m["telcom2"];
            $_SESSION["jsonsalida"]["matriculas"][$i]["telcom3"] = $m["telcom3"];
            $_SESSION["jsonsalida"]["matriculas"][$i]["multadoponal"] = $m["multadoponal"];
        }

        // **************************************************************************** //
        // Alertas Administrativas
        // **************************************************************************** //
        $query = '(';
        if (trim($_SESSION ["entrada"]["matricula"]) != '') {
            $query = "matricula='" . trim($_SESSION ["entrada"]["matricula"]) . "'";
        }
        if (trim($_SESSION ["entrada"]["identificacion"]) != '') {
            if ($query != '') {
                $query .= ' or ';
            }
            $query = "identificacion='" . trim($_SESSION ["entrada"]["identificacion"]) . "'";
        }
        $query .= ") and idestado='VI' and eliminad<>'SI'";

        //
        $aleAdm = retornarRegistrosMysqliApi($mysqli, 'mreg_alertas', $query, "id");
        $i = 0;
        if ($aleAdm && !empty($aleAdm)) {
            $_SESSION["jsonsalida"]["alertasAdministrativas"]["codigoerror"] = '5000';
            $_SESSION["jsonsalida"]["alertasAdministrativas"]["mensajeerror"] = 'El expediente seleccionado tiene registradas alertas administrativas.';
            $_SESSION["jsonsalida"]["alertasAdministrativas"]["alertas"] = array();
            foreach ($aleAdm as $a) {
                $i++;
                $_SESSION["jsonsalida"]["alertasAdministrativas"]["alertas"][$i] = $a;
                $_SESSION["jsonsalida"]["alertasAdministrativas"]["alertas"][$i]["txttipoalerta"] = '';
                switch ($a["tipoalerta"]) {
                    case "1" : $_SESSION["jsonsalida"]["alertasAdministrativas"]["alertas"][$i]["txttipoalerta"] = 'Valor a favor del cliente';
                        break;
                    case "2" : $_SESSION["jsonsalida"]["alertasAdministrativas"]["alertas"][$i]["txttipoalerta"] = 'Informativa';
                        break;
                    case "3" : $_SESSION["jsonsalida"]["alertasAdministrativas"]["alertas"][$i]["txttipoalerta"] = 'Restrictiva';
                        break;
                    case "4" : $_SESSION["jsonsalida"]["alertasAdministrativas"]["alertas"][$i]["txttipoalerta"] = 'Valor a favor de la Cámara';
                        break;
                }
            }
        }

        // **************************************************************************** //
        // Alertas Registrales
        // **************************************************************************** //
        $query = '(';
        if (trim($_SESSION ["entrada"]["matricula"]) != '') {
            $query = "matricula='" . trim($_SESSION ["entrada"]["matricula"]) . "'";
        }
        if (trim($_SESSION ["entrada"]["identificacion"]) != '') {
            if ($query != '') {
                $query .= ' or ';
            }
            $query = "identificacion='" . trim($_SESSION ["entrada"]["identificacion"]) . "'";
        }
        $query .= ") and idestado<>'AP' and idestado<>'IN'";

        //
        $aleReg = retornarRegistrosMysqliApi($mysqli, 'mreg_alertas_registro', $query, "id");
        $i = 0;
        if ($aleReg && !empty($aleReg)) {
            $_SESSION["jsonsalida"]["alertasRegistrales"]["codigoerror"] = '5000';
            $_SESSION["jsonsalida"]["alertasRegistrales"]["mensajeerror"] = 'El expediente seleccionado tiene registradas alertas del registro.';
            $_SESSION["jsonsalida"]["alertasRegistrales"]["alertas"] = array();

            foreach ($aleReg as $a) {
                $i++;
                $_SESSION["jsonsalida"]["alertasRegistrales"]["alertas"][$i] = $a;
                $_SESSION["jsonsalida"]["alertasRegistrales"]["alertas"][$i]["txttipoalerta"] = '';
                switch ($a["tipoalerta"]) {
                    case "1" : $_SESSION["jsonsalida"]["alertasRegistrales"]["alertas"][$i]["txttipoalerta"] = 'Informativa';
                        break;
                }
            }
        }

        // **************************************************************************** //
        // Localiza el estado en texto
        // **************************************************************************** //
        $_SESSION["tramite"]["txtestado"] = retornarRegistroMysqliApi($mysqli, "mreg_liquidacionestados", "id='" . $_SESSION["tramite"]["idestado"] . "'", "descripcion");

        // **************************************************************************** //
        // Activación de controles
        // **************************************************************************** //

        $_SESSION["jsonsalida"]["controles"] = array();

        // ******************************************************************** //
        // Panel Número de empleados
        // ******************************************************************** //
        $tempPanel = array();
        $tempPanel["tipo"] = 'data';
        $tempPanel["titulopanel"] = 'Información de empleados';
        $tempPanel["inputs"] = array();

        // Campo número de empleados
        $temp = array();
        $temp["tipo"] = 'input';
        $temp["mostrar"] = 'SI';
        $temp["encabezado"] = '';
        $temp["label"] = 'Número de empleados a nivel nacional';
        $temp["id"] = 'numeroempleados';
        $temp["name"] = 'numeroempleados';
        $temp["type"] = 'text';
        $temp["size"] = '6';
        $temp["value"] = $_SESSION["tramite"]["numeroempleados"];
        $temp["placeholder"] = '';
        $tempPanel["inputs"][] = $temp;
        $_SESSION["jsonsalida"]["controles"][] = $tempPanel;

        // ******************************************************************** //
        // Panel ley 1780
        // ******************************************************************** //
        // *************************************************************************************** //
        // Lógica para beneficios Ley 1780
        // *************************************************************************************** //
        $mostrarcumple = 'no';
        $mostrarmantiene = 'no';

        if (!isset($_SESSION ["tramite"]["cumplorequisitosbenley1780"])) {
            $_SESSION ["tramite"]["cumplorequisitosbenley1780"] = '';
        }
        if (!isset($_SESSION ["tramite"]["mantengorequisitosbenley1780"])) {
            $_SESSION ["tramite"]["mantengorequisitosbenley1780"] = '';
        }
        if (!isset($_SESSION ["tramite"]["renunciobeneficiosley1780"])) {
            $_SESSION ["tramite"]["renunciobeneficiosley1780"] = '';
        }
        if ($tempBase["benley1780"] == 'S') {
            if ($tempBase["organizacion"] != '02' &&
                    ($tempBase["categoria"] == '' || $tempBase["categoria"] == '0' || $tempBase["categoria"] == '1')) {
                if ($tempBase["fechamatricula"] >= '20160502') {
                    $anoactual = date("Y");
                    $anoanterior = $anoactual - 1;
                    if (substr($tempBase["fechamatricula"], 0, 4) == $anoanterior) {
                        $mostrarcumple = 'si';
                        $mostrarmantiene = 'si';
                    }
                }
            }
        }

        $tempPanel = array();
        $tempPanel["tipo"] = 'data';
        $tempPanel["titulopanel"] = 'Beneficios Ley 1780 de 2016';

        //

        $tempPanel["avisopie"] = array();

        $tempPanel["avisopie"]["texto"] = 'Para continuar con los beneficios de la Ley 1780 y de acuerdo con lo ' .
                'indicado en el decreto 639 de 2017, deberá anexar los siguientes soportes:<br><br>' .
                '1.- Relación de trabajadores vinculados directamente con la empresa, si los tuviere, indicando el nombre e identificación de los mismos<br>' .
                '2.- Certiticar que la empresa ha realizado los aportes al Sistema de Seguridad Social Integral y demás contribuciones de nómina, en caso de estar obligada a ello, y ha cumplido con sus obligaciones oportunamente en materia tributaria<br>' .
                '3.- Presentar copia de los estados financieros debidamente firmados por el contador o revisor fiscal, según el caso, con corte al 31 de diciembre del año inmediatamente anterior<br>' .
                '4.- Certificar que la titularidad de la mitad más uno de las cuotas, acciones o participaciones en que se divide el capital de la sociedad o empresa, pertenezcan a socios con edades entre 18 y 35 años.<br>';

        $tempPanel["avisopie"]["color"] = 'warning';
        //

        $tempPanel["inputs"] = array();

        // Campo fecha de nacimiento
        $temp = array();
        $temp["tipo"] = 'input';
        if ($tempBase["organizacion"] == '01' &&
                $mostrarcumple == 'si' &&
                $mostrarmantiene == 'si') {
            $temp["mostrar"] = 'SI';
            $temp["obligatorio"] = 'SI';
        } else {
            $temp["mostrar"] = 'NO';
            $temp["obligatorio"] = 'NO';
        }
        $temp["encabezado"] = '';
        $temp["label"] = 'Fecha de nacimiento';
        $temp["id"] = 'fechanacimiento';
        $temp["name"] = 'fechanacimiento';
        $temp["type"] = 'date';
        $temp["size"] = '10';
        $temp["value"] = $_SESSION ["tramite"]["expedientes"][0]["fechanacimiento"];
        $temp["placeholder"] = '';
        $tempPanel["inputs"][] = $temp;

        // Campo cumplorequisitosbenley1780

        $temp = array();
        $temp["tipo"] = 'select';
        $selsi = '';
        $selno = '';

        if ($mostrarcumple == 'si') {
            $temp["mostrar"] = 'SI';
            $temp["obligatorio"] = 'SI';
            $selsi = 'S';
        } else {
            $temp["mostrar"] = 'NO';
            $temp["obligatorio"] = 'NO';
            $selno = 'S';
        }
        $temp["encabezado"] = '';
        $temp["label"] = 'Cumplo con los requisitos establecidos para acceder al beneficio de la Ley 1780 de 2016';
        $temp["id"] = 'cumplorequisitosbenley1780';
        $temp["name"] = 'cumplorequisitosbenley1780';
        $temp["type"] = 'text';
        $temp["size"] = '2';
        $temp["opc"] = array();


        $temp["opc"][] = array(
            'label' => 'SI',
            'val' => 'S',
            'selected' => $selsi
        );
        $temp["opc"][] = array(
            'label' => 'NO',
            'val' => 'N',
            'selected' => $selno
        );
        $temp["value"] = $_SESSION ["tramite"]["cumplorequisitosbenley1780"];
        $temp["placeholder"] = '';
        $tempPanel["inputs"][] = $temp;


        // Campo mantengorequisitosbenley1780
        $temp = array();
        $temp["tipo"] = 'select';
        $temp["mostrar"] = 'SI';
        $selsi = '';
        $selno = '';
        if ($mostrarmantiene == 'si') {
            $temp["mostrar"] = 'SI';
            $temp["obligatorio"] = 'SI';
            $selsi = 'S';
        } else {
            $temp["mostrar"] = 'NO';
            $temp["obligatorio"] = 'NO';
            $selno = 'S';
        }

        $temp["encabezado"] = '';
        $temp["label"] = 'Mantengo los requisitos establecidos para acceder al beneficio de la Ley 1780 de 2016';
        $temp["id"] = 'mantengorequisitosbenley1780';
        $temp["name"] = 'mantengorequisitosbenley1780';
        $temp["type"] = 'text';
        $temp["size"] = '2';
        $temp["opc"] = array();
        $temp["opc"][] = array(
            'label' => 'SI',
            'val' => 'S',
            'selected' => $selsi
        );
        $temp["opc"][] = array(
            'label' => 'NO',
            'val' => 'N',
            'selected' => $selno
        );
        $temp["value"] = $_SESSION ["tramite"]["mantengorequisitosbenley1780"];
        $temp["placeholder"] = '';
        $tempPanel["inputs"][] = $temp;

        // Campo renunciobeneficiosley1780
        $temp = array();
        $temp["tipo"] = 'select';
        if ($mostrarcumple == 'si' && $mostrarmantiene == 'si') {
            $temp["mostrar"] = 'SI';
            $temp["obligatorio"] = 'SI';
        } else {
            $temp["mostrar"] = 'NO';
            $temp["obligatorio"] = 'NO';
        }

        $temp["encabezado"] = '';
        $temp["label"] = 'Renuncio voluntariamente a los beneficios de la ley 1780 de 2016';
        $temp["id"] = 'renunciobeneficiosley1780';
        $temp["name"] = 'renunciobeneficiosley1780';
        $temp["type"] = 'text';
        $temp["size"] = '2';
        $temp["opc"] = array();
        $temp["opc"][] = array(
            'label' => 'SI',
            'val' => 'S',
            'selected' => ''
        );
        $temp["opc"][] = array(
            'label' => 'NO',
            'val' => 'N',
            'selected' => ''
        );
        $temp["value"] = $_SESSION ["tramite"]["renunciobeneficiosley1780"];
        $temp["placeholder"] = '';

        $tempPanel["inputs"][] = $temp;

        //
        $_SESSION["jsonsalida"]["controles"][] = $tempPanel;


        // ******************************************************************** //
        // Controles adicionales de liquidación
        // ******************************************************************** //
        $tempPanel = array();
        $tempPanel["tipo"] = 'data';
        $tempPanel["titulopanel"] = 'Controles adicionales a la liquidación';
        $tempPanel["inputs"] = array();

        // Campo incluirafiliacion si es usuario publico
        if ($_SESSION["generales"]["codigousuario"] == 'USUPUBXX') {
            $temp = array();
            $temp["tipo"] = 'select';
            $temp["mostrar"] = 'NO';
            $temp["obligatorio"] = 'SI';
            $temp["encabezado"] = '';
            $temp["label"] = 'Liquidar cuota de afiliación (S o N)';
            $temp["id"] = 'incluirafiliacion';
            $temp["name"] = 'incluirafiliacion';
            $temp["type"] = 'text';
            $temp["size"] = '2';
            $temp["opc"] = array();
            $temp["opc"][] = array(
                'label' => 'SI',
                'val' => 'S',
                'selected' => 'si'
            );
            $temp["opc"][] = array(
                'label' => 'NO',
                'val' => 'N',
                'selected' => ''
            );
            $temp["value"] = 'S';
            $temp["placeholder"] = '';
            $tempPanel["inputs"][] = $temp;
        }

        // Campo incluirafiliacion si es usuario no publico
        if ($_SESSION["generales"]["codigousuario"] != 'USUPUBXX') {
            $temp = array();
            $temp["tipo"] = 'select';
            $temp["mostrar"] = 'SI';
            $temp["obligatorio"] = 'SI';
            $temp["encabezado"] = '';

            $temp["label"] = 'Liquidar cuota de afiliación (S o N)';
            $temp["id"] = 'incluirafiliacion';
            $temp["name"] = 'incluirafiliacion';
            $temp["type"] = 'text';
            $temp["size"] = '2';
            $temp["opc"] = array();
            $temp["opc"][] = array(
                'label' => 'SI',
                'val' => 'S',
                'selected' => ''
            );
            $temp["opc"][] = array(
                'label' => 'NO',
                'val' => 'N',
                'selected' => ''
            );
            $temp["value"] = 'S';
            $temp["placeholder"] = '';
            $tempPanel["inputs"][] = $temp;
        }

        // Campo incluirformularios si es usuario público
        if ($_SESSION["generales"]["codigousuario"] == 'USUPUBXX') {
            $temp = array();
            $temp["tipo"] = 'select';
            $temp["mostrar"] = 'NO';
            $temp["obligatorio"] = 'SI';
            $temp["encabezado"] = '';
            $temp["label"] = 'Liquidar formularios (S o N)';
            $temp["id"] = 'incluirformularios';
            $temp["name"] = 'incluirformularios';
            $temp["type"] = 'text';
            $temp["size"] = '2';
            $temp["opc"] = array();
            $temp["opc"][] = array(
                'label' => 'SI',
                'val' => 'S',
                'selected' => 'si'
            );
            $temp["opc"][] = array(
                'label' => 'NO',
                'val' => 'N',
                'selected' => ''
            );
            $temp["value"] = 'S';
            $temp["placeholder"] = '';
            $tempPanel["inputs"][] = $temp;
        }

        // Campo incluirformularios si es usuario interno
        if ($_SESSION["generales"]["codigousuario"] != 'USUPUBXX') {
            $temp = array();
            $temp["tipo"] = 'select';
            $temp["mostrar"] = 'SI';
            $temp["obligatorio"] = 'SI';
            $temp["encabezado"] = '';
            $temp["label"] = 'Liquidar formularios (S o N)';
            $temp["id"] = 'incluirformularios';
            $temp["name"] = 'incluirformularios';
            $temp["type"] = 'text';
            $temp["size"] = '2';
            $temp["opc"] = array();
            $temp["opc"][] = array(
                'label' => 'SI',
                'val' => 'S',
                'selected' => ''
            );
            $temp["opc"][] = array(
                'label' => 'NO',
                'val' => 'N',
                'selected' => ''
            );
            $temp["value"] = 'S';
            $temp["placeholder"] = '';
            $tempPanel["inputs"][] = $temp;
        }

        // Campo incluirdiploma
        /*
          $temp = array();
          $temp["tipo"] = 'select';
          $temp["mostrar"] = 'SI';
          $temp["encabezado"] = '';
          $temp["label"] = 'Liquidar diploma (S o N)';
          $temp["id"] = 'incluirdiploma';
          $temp["name"] = 'incluirdiploma';
          $temp["type"] = 'text';
          $temp["size"] = '2';
          $temp["opc"] = array();
          $temp["opc"][] = array(
          'label' => 'SI',
          'val' => 'S',
          'selected' => ''
          );
          $temp["opc"][] = array(
          'label' => 'NO',
          'val' => 'N',
          'selected' => ''
          );
          $temp["value"] = 'N';
          $tempPanel["inputs"][] = $temp;
         */

        // Campo incluircartulina
        /*
          $temp = array();
          $temp["tipo"] = 'select';
          $temp["mostrar"] = 'SI';
          $temp["encabezado"] = '';
          $temp["label"] = 'Liquidar cartulina (S o N)';
          $temp["id"] = 'incluircartulina';
          $temp["name"] = 'incluircartulina';
          $temp["type"] = 'text';
          $temp["opc"] = array();
          $temp["opc"][] = array(
          'label' => 'SI',
          'val' => 'S',
          'selected' => ''
          );
          $temp["opc"][] = array(
          'label' => 'NO',
          'val' => 'N',
          'selected' => ''
          );
          $temp["size"] = '2';
          $temp["value"] = 'N';
          $temp["placeholder"] = '';
          $tempPanel["inputs"][] = $temp;
         */

        // Campo incluirfletes
        /*
          $temp = array();
          $temp["tipo"] = 'select';
          $temp["mostrar"] = 'NO';
          $temp["encabezado"] = '';
          $temp["label"] = 'Liquidar fletes (S o N)';
          $temp["id"] = 'incluirfletes';
          $temp["name"] = 'incluirfletes';
          $temp["type"] = 'text';
          $temp["size"] = '2';
          $temp["opc"] = array();
          $temp["opc"][] = array(
          'label' => 'SI',
          'val' => 'S',
          'selected' => ''
          );
          $temp["opc"][] = array(
          'label' => 'NO',
          'val' => 'N',
          'selected' => ''
          );
          $temp["value"] = 'N';
          $temp["placeholder"] = '';
          $tempPanel["inputs"][] = $temp;
         */

        // Campo incluircertificados - usuario público
        if ($_SESSION["generales"]["codigousuario"] == 'USUPUBXX') {
            $temp = array();
            $temp["tipo"] = 'input';
            $temp["mostrar"] = 'NO';
            $temp["obligatorio"] = 'NO';
            $temp["encabezado"] = '';
            $temp["label"] = 'Cantidad de certificados a incluir';
            $temp["id"] = 'incluircertificados';
            $temp["name"] = 'incluircertificados';
            $temp["type"] = 'number';
            $temp["size"] = '2';
            $temp["value"] = '0';
            $temp["placeholder"] = '';
            $tempPanel["inputs"][] = $temp;
        }

        // Campo incluircertificados - usuario público
        if ($_SESSION["generales"]["codigousuario"] != 'USUPUBXX') {
            $temp = array();
            $temp["tipo"] = 'input';
            $temp["mostrar"] = 'SI';
            $temp["obligatorio"] = 'NO';
            $temp["encabezado"] = '';
            $temp["label"] = 'Cantidad de certificados a incluir';
            $temp["id"] = 'incluircertificados';
            $temp["name"] = 'incluircertificados';
            $temp["type"] = 'number';
            $temp["size"] = '2';
            $temp["value"] = '1';
            $temp["placeholder"] = '';
            $tempPanel["inputs"][] = $temp;
        }


        $_SESSION["jsonsalida"]["controles"][] = $tempPanel;

        // ******************************************************************** //
        // Panel de botones
        // ******************************************************************** //
        $tempPanel = array();
        $tempPanel["tipo"] = 'boton';
        $tempPanel["titulopanel"] = '';
        $tempPanel["inputs"] = array();

        // Botón Liquidar
        $temp = array();
        $temp["tipo"] = 'botton';
        $temp["mostrar"] = 'SI';
        $temp["label"] = 'LIQUIDAR';
        $temp["color"] = 'btn-primary';
        $temp["x"] = 'TramitesController';
        $temp["y"] = 'liquidacion';
        $temp["z"] = 'liquidacion';
        $temp["href"] = '';
        $tempPanel["inputs"][] = $temp;

        // Boton Abandonar
        $temp = array();
        $temp["tipo"] = 'botton';
        $temp["mostrar"] = 'SI';
        $temp["label"] = 'ABANDONAR';
        $temp["color"] = 'btn-secondary';
        $temp["x"] = 'TramitesController';
        $temp["y"] = 'renovarCancelarMatricula';
        $temp["z"] = 'renovarMatricula';
        $temp["href"] = '';
        $tempPanel["inputs"][] = $temp;

        $_SESSION["jsonsalida"]["controles"][] = $tempPanel;

        //
        $mysqli->close();

        // **************************************************************************** //
        // Resultado
        // **************************************************************************** //
        \logApi::peticionRest('api_' . __FUNCTION__);
        $json = $api->json($_SESSION["jsonsalida"]);
        $api->response(str_replace("\\/", "/", $json), 200);
    }

}

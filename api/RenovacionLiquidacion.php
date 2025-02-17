<?php

/*
 * Se recibe json con la siguiente información
 *
 */

namespace libreriaswsRestSII;

use libreriaswsRestSII\API;

trait RenovacionLiquidacion
{
    public function controlesRenovacion(API $api)
    {
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/myErrorHandler.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
        $resError = set_error_handler('myErrorHandler');

        if (!defined('CLAVE_ENCRIPTACION') || CLAVE_ENCRIPTACION == '') {
            $claveEncriptacion = 'c0nf3c@m@r@s2017';
        }

        // array de respuesta
        $_SESSION["jsonsalida"] = array();
        $_SESSION["jsonsalida"]["codigoerror"] = '0000';
        $_SESSION["jsonsalida"]["mensajeerror"] = '';
        $_SESSION['jsonsalida']['tramite'] = array();
        $_SESSION['jsonsalida']['controles'] = array();

        // Verifica método de recepcion de parámetros
        if ($api->get_request_method() != "POST" && $api->get_request_method() != "GET") {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'La petición debe ser POST/GET';
        }

        //
        $api->validarParametro("codigoempresa", true);
        $api->validarParametro("usuariows", true);
        $api->validarParametro("token", true);
        $api->validarParametro("idusuario", true);
        $api->validarParametro("tipousuario", true, true);
        $api->validarParametro("emailcontrol", false);
        $api->validarParametro("identificacioncontrol", false);
        $api->validarParametro("celularcontrol", false);
        $api->validarParametro("ip", false);
        $api->validarParametro("sistemaorigen", false);

        //
        $api->validarParametro("tipoconsulta", true);
        $api->validarParametro("idliquidacion", false);
        $api->validarParametro("numerorecuperacion", false);

        //
        if (!$api->validarToken('controlesRenovacion', $_SESSION["entrada"]["token"], $_SESSION["entrada"]["usuariows"])) {
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        // ********************************************************************** //
        // Crea la conexión con la BD
        // ********************************************************************** //
        $mysqli = conexionMysqliApi();
        if ($mysqli === false) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Error de conexion a la BD';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        // ********************************************************************** //
        // Recupera liquidacion
        // ********************************************************************** //
        //Areglo de Data Basico para reutilizar en los metodos del API
        $dataBasica = array(
            "codigoempresa" => $_SESSION["entrada"]["codigoempresa"],
            "usuariows" => $_SESSION["entrada"]["usuariows"],
            "token" => $_SESSION["entrada"]["token"],
            "idusuario" => $_SESSION["entrada"]["idusuario"],
            "tipousuario" => $_SESSION["entrada"]["tipousuario"],
            "emailcontrol" => $_SESSION["entrada"]["emailcontrol"],
            "identificacioncontrol" => $_SESSION["entrada"]["identificacioncontrol"],
            "celularcontrol" => $_SESSION["entrada"]["celularcontrol"],
            "ip" => $_SESSION["entrada"]["ip"],
            "sistemaorigen" => $_SESSION["entrada"]["sistemaorigen"]
        );
        $da = array('tipoconsulta' => $_SESSION["entrada"]["tipoconsulta"], "idliquidacion" => $_SESSION["entrada"]["idliquidacion"], "numerorecuperacion" => $_SESSION["entrada"]["numerorecuperacion"]);
        //Consumo del metodo consultarLiquidacion del API para guardar toda la Data del tramite en un array
        $arrayTramite = $this->cUrl(array_merge($dataBasica, $da), "consultarLiquidacion");
        //Consumo del metodo retornarControlesUsuario del API para tener la Data del usuario logeado si es cajero o no y guardarla en un array
        $validacionCajero = $this->cUrl($dataBasica, "retornarControlesUsuario");
        //Consumo del metodo local valorConstante que recibe el nombre de la constante y retorna la data de esta, y se guarda en una variable
        $RENOVACION_BLOQUEAR_ACTIVOS_MENORES = $this->valorConstante($dataBasica, 'RENOVACION_BLOQUEAR_ACTIVOS_MENORES');
        $RENOVACION_SERV_FORMULARIOS = $this->valorConstante($dataBasica, 'RENOVACION_SERV_FORMULARIOS');
        $RENOVACION_SERV_FORMULARIOS_ESADL = $this->valorConstante($dataBasica, 'RENOVACION_SERV_FORMULARIOS_ESADL');
        $ACTIVAR_CONTROL_POT_PONAL = $this->valorConstante($dataBasica, 'ACTIVAR_CONTROL_POT_PONAL');
        $RENOVACION_COBRAR_FORMULARIOS_AFILIADOS = $this->valorConstante($dataBasica, 'RENOVACION_COBRAR_FORMULARIOS_AFILIADOS');
        $TIPO_HTTP = $this->valorConstante($dataBasica, 'TIPO_HTTP');
        $HTTP_HOST = $this->valorConstante($dataBasica, 'HTTP_HOST');
        //Validacion si la constante llega vacia y asignacion de un valor
        if (!defined($RENOVACION_COBRAR_FORMULARIOS_AFILIADOS)) {
            $RENOVACION_COBRAR_FORMULARIOS_AFILIADOS = 'N';
        }
        //
        $arrForms = array();
        //Inicializacion variables de beneficios y cobro Formularios
        $okBeneficio = '';
        $okBeneficio1780 = '';
        $matBeneficio1780 = '';
        $cobrarformularios = 'no';
        //Retorno del array de tramite si el codigo de error es 9999
        if ($arrayTramite['codigoerror'] == '9999') {
            $mysqli->close();
            $_SESSION["jsonsalida"]["codigoerror"] = $arrayTramite['codigoerror'];
            $_SESSION["jsonsalida"]["mensajeerror"] = $arrayTramite['mensajeerror'];
            \logApi::peticionRest('api_' . __FUNCTION__);
            $json = $api->json($_SESSION["jsonsalida"]);
            $api->response(str_replace("\\/", "/", $json), 200);
        }
        //
        foreach ($arrayTramite["tramite"] ["liquidacion"] as $s) {            
            if ($s["idservicio"] == $RENOVACION_SERV_FORMULARIOS || $s["idservicio"] == $RENOVACION_SERV_FORMULARIOS_ESADL) {
                //Validacion de usuario cajero
                if ($validacionCajero["escajero"] == 'SI') {
                    if ($arrayTramite["tramite"]["incluirformularios"] == 'S') {
                        $cobrarformularios = 'si';
                    }
                } else {
                    $cobrarformularios = 'si';
                }
            }
            if ($s ["benart7"] == 'S' || $s ["benart7"] == 'P') {
                $okBeneficio = $s ["benart7"];
            }
        }
        //
        $cantmin = 0;
        $cantminprincipales = 0;
        $faltanformularios = 'no';
        $matPonal = '';
        //
        if (isset($arrayTramite ["tramite"] ["expedientes"])) {
            if (!empty($arrayTramite ["tramite"] ["expedientes"])) {
                foreach ($arrayTramite["tramite"] ["expedientes"] as $exp) {
                    if ($matPonal == '') {
                        $matPonal = $exp["matricula"];
                    }
                    if (!isset($exp ["benley1780"])) {
                        $exp ["benley1780"] = '';
                    }
                    if ($arrayTramite["tramite"]["renunciobeneficiosley1780"] == 'N') {
                        if ($exp ["benley1780"] == 'S' || $exp ["benley1780"] == 'P') {
                            $okBeneficio1780 = $exp ["benley1780"];
                            $matBeneficio1780 = $exp["matricula"];
                            $orgBeneficio1780 = $exp["organizacion"];
                        }
                    } else {
                        if ($exp ["benley1780"] == 'S') {
                            $okBeneficio1780 = 'R';
                            $matBeneficio1780 = $exp["matricula"];
                            $orgBeneficio1780 = $exp["organizacion"];
                        } else {
                            $okBeneficio1780 = 'R';
                        }
                    }
                    //
                    if (!isset($arrForms [$exp ["matricula"]])) {
                        $arrForms [$exp ["matricula"]] ["inicial"] = '9999';
                        $arrForms [$exp ["matricula"]] ["final"] = '0000';
                    }
                    //
                    if ($exp ["renovaresteano"] == 'si') {
                        //
                        if ($arrForms [$exp ["matricula"]] ["inicial"] > $exp ["ultimoanorenovado"]) {
                            $arrForms [$exp ["matricula"]] ["inicial"] = $exp ["ultimoanorenovado"];
                        }
                        if ($arrForms [$exp ["matricula"]] ["final"] < $exp ["ultimoanorenovado"]) {
                            $arrForms [$exp ["matricula"]] ["final"] = $exp ["ultimoanorenovado"];
                        }
                        //
                        if ($exp ["nuevosactivos"] < $exp ["ultimosactivos"]) {
                            $cantmin ++;
                            if ($exp ["organizacion"] == '01' || ($exp ["organizacion"] > '02' && $exp ["categoria"] == '1')) {
                                $cantminprincipales ++;
                            }
                        }
                        //
                        if (($arrayTramite ["tramite"] ["tipotramite"] == 'renovacionmatricula') || ($arrayTramite ["tramite"] ["tipotramite"] == 'renovacionesadl')) {
                            //Consumo del metodo validacionFormularioGrabado del API de confecamaras
                            $valForm = $this->cUrl(array_merge($dataBasica, array("idliquidacion" => $arrayTramite ["tramite"] ["numeroliquidacion"], "expediente" => $exp ["matricula"])), 'validacionFormularioGrabado');
                            //Validacion de la variable retornada por el metodo validacionFormularioGrabado para darle un valor booleano
                            $resValForm = ($valForm['codigoerror'] === '0000') ? true : false;
                            //Array de Data requerida para consumir el metodo contarRegistrosde del API de Confecamaras
                            $arrC = array('tabla' => 'mreg_liquidaciondatos', 'query' => "idliquidacion=" . $arrayTramite ["tramite"] ["numeroliquidacion"] . " and expediente='" . $exp ["matricula"] . "'");
                            //Validar respuesta del metodo contarRegistros
                            if ($this->cUrl(array_merge($dataBasica, $arrC), 'contarRegistros') == 0 || !$resValForm) {
                                $faltanformularios = 'si';
                            }
                        }
                    }
                }
            }
            //Variable de incremento para los titulos de los paneles
            $e = 0;
            //
            $formulariosCompletos = 0;
            //Controlador del Panel Datos Generales del Tramite
            $controles2['idpanel'] = '1';
            $controles2['descripcion'] = '';
            $controles2['titulo'] = ++$e . '. Datos generales del trámite y liquidación';
            $controles2['avisopie'] = array();
            $controles2['detallefactura'] = array();

            //
            if ($arrayTramite ["tramite"] ["reliquidacion"] == 'no') {
                //
                if ($cobrarformularios == 'no') {
                    //
                    $arraypie['titulo'] = '!!! ATENCION !!!';
                    $arraypie['descripcion'] = 'En el momento de acercarse a la caja a realizar el pago de la renovación, se le cobrará el valor de los formularios correspondientes. Si el pago se realiza en forma electrónica, no se hará cobro de los mismos.';
                    $arraypie['pie'] = '';
                    //
                    if ($RENOVACION_COBRAR_FORMULARIOS_AFILIADOS == 'N') {
                        //
                        $arraypie['pie'] = 'Si usted es afiliado, los formularios no le serán cobrados.';
                        //
                    }
                    $controles2['avisopie'] = $arraypie;
                    //
                }
            }
            //Controlador del Panel Datos Formularios
            $controles = array();
            $controles['idpanel'] = '2';
            $controles['descripcion'] = '';
            $controles['formulario'] = array();
            $formulariosCompletos = 0;
            $imprimirFormulariosAll = true;
            //
            $i = - 1;
            //
            if (isset($arrayTramite ["tramite"] ["expedientes"])) {
                if (!empty($arrayTramite ["tramite"] ["expedientes"])) {
                    foreach ($arrayTramite ["tramite"] ["expedientes"] as $matri) {
                        if ($matri ["registrobase"] == 'S') {
                            if ($arrForms [$matri ["matricula"]] ["final"] != '0000' && $arrForms [$matri ["matricula"]] ["inicial"] != '9999') {
                                $imprimir = '';
                                $i ++;
                                //
                                if (trim($matri ["matricula"]) != '') {
                                    //
                                    $controles['formulario'][$i]['matricula'] = $matri ["matricula"];
                                    //
                                } else {
                                    //
                                    $controles['formulario'][$i]['matricula'] = '';
                                    //
                                }
                                //
                                $controles['formulario'][$i]['nombre'] = $matri ["razonsocial"];
                                $controles['formulario'][$i]['anofinal'] = $arrForms [$matri ["matricula"]] ["final"];
                                $controles['formulario'][$i]['anoinicial'] = $arrForms [$matri ["matricula"]] ["inicial"];
                                //Array con la Data para consumir el metodo contarRegistros del API de confecamaras
                                $arrCon = array('tabla' => 'mreg_liquidaciondatos', 'query' => "idliquidacion=" . $arrayTramite ["tramite"] ["numeroliquidacion"] . " and expediente='" . $matri ["matricula"] . "' and idestado='2'");
                                //Consumo del metodo contarRegistros del Api de confecamaras
                                $cant = array();
                                $cant = $this->cUrl(array_merge($dataBasica, $arrCon), "contarRegistros");
                                //Validacion para ver si los formularios ya estan llenados
                                if ($cant["conteo"] == 0) {
                                    $controles['formulario'][$i]['estado'] = 'Pendiente';
                                    $formulariosCompletos = 1;
                                    $imprimirFormulariosAll = false;
                                    $imprimir = 'no';
                                } else {
                                    //Array con la Dato necesaria para el consumo del metodo del validacionFormularioGrabado API
                                    $arrCon2 = array('idliquidacion' => $arrayTramite ["tramite"] ["numeroliquidacion"], "expediente" => $matri ["matricula"]);
                                    //Consumo del metodo del API con una respuesta de error 8000
                                    $validacionFormul = $this->cUrl(array_merge($dataBasica, $arrCon2), "validacionFormularioGrabado");
                                    //Validacion envio de informacion para consumir metodo validacionFormularioGrabado y asignacion de un valor booleano
                                    $codigError = ($validacionFormul["codigoerror"] == "0000") ? true : false;
                                    //Validacion del formulario
                                    if ($codigError === false) {
                                        $controles['formulario'][$i]['estado'] = 'En grabación';
                                        $formulariosCompletos = 1;
                                        $imprimir = 'no';
                                    } else {
                                        $controles['formulario'][$i]['estado'] = 'Grabado';
                                        $imprimir = 'si';
                                    }
                                }
                                //
                                $controles["formulario"][$i]['capturar'] = array();
                                $controles['formulario'][$i]['capturar']['mostrar'] = "no";
                                $controles["formulario"][$i]['imprimir'] = '';
                                if (isset($arrayTramite ["tramite"] ["idestado"])) {
                                    $controles['formulario'][$i]['capturar']['idliquidacion'] = $arrayTramite ["tramite"] ["numeroliquidacion"];
                                    $controles['formulario'][$i]['capturar']['matricula'] = $matri ["matricula"];
                                }
                                if ($arrayTramite ["tramite"] ["idestado"] < '05') {
                                    //llenado del array de formulario para el control
                                    $controles['formulario'][$i]['capturar']['idliquidacion'] = $arrayTramite ["tramite"] ["numeroliquidacion"];
                                    $controles['formulario'][$i]['capturar']['cc'] = $matri["cc"];
                                    $controles['formulario'][$i]['capturar']['matricula'] = $matri ["matricula"];
                                    $controles['formulario'][$i]['capturar']['anoinicial'] = $arrForms [$matri ["matricula"]] ["inicial"];
                                    $controles['formulario'][$i]['capturar']['anofinal'] = $arrForms [$matri ["matricula"]] ["final"];
                                    $controles['formulario'][$i]['capturar']['mostrar'] = "si";
                                    $controles['formulario'][$i]["imprimir"] = '';
                                    //
                                    if ($imprimir == 'si') {
                                        if ($imprimirFormulariosAll) {
                                            $controles['formulario'][$i]["imprimir"] = 'si';
                                        } else {
                                            $controles['formulario'][$i]["imprimir"] = 'no';
                                        }
                                    } else {
                                        $controles['formulario'][$i]["imprimir"] = 'no';
                                    }
                                    if ($cantmin > 0) {
                                        if ($RENOVACION_BLOQUEAR_ACTIVOS_MENORES == 'S' && $validacionCajero["escajero"] != 'SI') {
                                            $controles["formulario"][$i]['capturar'] = array();
                                            $controles['formulario'][$i]["imprimir"] = '';
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
            if (isset($controles['formulario'])) {
                foreach ($controles['formulario'] as &$formulario) {
                    //Lanzador para imprimir formularios
                    if ($imprimirFormulariosAll) {
                        $formulario["imprimir"] = "si";
                        if ($arrayTramite ["tramite"] ["idestado"] < '05') {
                            $formulario["capturar"]["mostrar"] = "si";
                        } else {
                            $formulario["capturar"]["mostrar"] = "no";
                        }
                    } else {
                        $formulario["imprimir"] = "";
                    }
                }
            }
            //Validacion del texto de descripcion segun si es cajero o usuario
            if ($validacionCajero["escajero"] == 'SI') {
                //texto cajero
                $controles2['descripcion'] = '<br>Señor Cajero:</br> Verifique detenidamente la liquidación realizada, si está de acuerdo con ella proceda a diligenciar como se indica en la parte inferior de esta pantalla, los formularios para cada una de las matrículas. Cuando haya terminado la digitación de los formularios, seleccione la forma de pago que desee utilizar, ya sea PAGAR EN CAJA o PAGO EN LINEA o ABANDONE la transacción si lo prefiere.';
            } else {
                //texto usuario
                $controles2['descripcion'] = 'Verifique detenidamente la liquidación realizada, si está de acuerdo con ella proceda a diligenciar como se indica en la parte inferior de esta pantalla, los formularios para cada una de las matrículas. Cuando haya terminado la digitación de los formularios, seleccione la forma de pago que desee utilizar, ya sea PAGAR EN CAJA o PAGO EN LINEA o ABANDONE la transacción si lo prefiere.';
            }
            //Data del Panel de Datos Generales del Tramite
            $controles2['numerorecuperacion'] = $arrayTramite["tramite"] ["numerorecuperacion"];
            $controles2['idliquidacion'] = $arrayTramite["tramite"] ["numeroliquidacion"];
            $controles2['estado'] = $arrayTramite["tramite"] ["txtestado"];
            $controles2['renovaraparacancelar'] = $arrayTramite["tramite"] ["ctrcancelacion"];
            $controles2['mostrar'] = 'SI';
            //Data de la tabla de formularios
            foreach ($arrayTramite ["tramite"] ["liquidacion"] as $s) {
                $factura["idservicio"] = $s["idservicio"];
                $factura["txtservicio"] = $s["nombre"];
                $factura["nombre"] = $s["txtservicio"];
                $factura["expediente"] = $s["expediente"];
                $factura["ano"] = $s["ano"];
                $factura["cantidad"] = $s["cantidad"];
                $factura["valorbase"] = $s["valorbase"];
                $factura["valorservicio"] = $s["valorservicio"];
                $controles2['detallefactura'][] = $factura;
            }
            $controles2['valorbruto'] = $arrayTramite["tramite"] ["valorbruto"];
            $controles2['valoriva'] = $arrayTramite["tramite"] ["valoriva"];
            $controles2['valortotal'] = $arrayTramite["tramite"] ["valortotal"];
            //Validacion de beneficios del Tramite

            if ($okBeneficio == 'P' || $okBeneficio == 'S') {
                if ($okBeneficio == 'P') {
                    //
                                    //  $controles2['pie'] = 'El comerciante ha perdido los beneficios de la Ley 1429 de 2010';
                }
                if ($okBeneficio == 'S') {
                    //
                                    //$controles2['pie'] = 'El comerciante conserva los beneficios de la Ley 1429 de 2010';
                }
                if ($okBeneficio == '') {
                    //
                                    //$controles2['pie'] = 'Comerciante sin beneficios de la Ley 1429 de 2010';
                }
                //
            }
            //Validacion de Beneficios del Tramite
            if ($okBeneficio1780 == 'P' || $okBeneficio1780 == 'S' || $okBeneficio1780 == 'R') {
                $txtBen1780 = '';
                if ($okBeneficio1780 == 'P') {
                    //
                                    //$controles2['pie'] = 'El comerciante ha perdido los beneficios de la Ley 1780 de 2016';
                }
                if ($okBeneficio1780 == 'S') {
                    //
                                    //$controles2['pie'] = 'El comerciante conserva los beneficios de la Ley 1780 de 2016';
                }
                if ($okBeneficio1780 == 'R') {
                    //
                                    //$controles2['pie'] = 'El comerciante renunció a los beneficios de la Ley 1780 de 2016';
                }
                if ($okBeneficio1780 == '') {
                    //
                                    //$controles2['pie'] = 'Comerciante sin beneficios de la Ley 1780 de 2016';
                }
                $controles2['pie'] = '';
            }
            //Controlador del Panel de Soportes Ley 1780
            $controles3 = array();
            $controles3['idpanel'] = '3';
            $controles3['idliquidacion'] = array();
            $controles3['titulo'] = 'Ley 1780';
            $controles3['descripcion'] = '';
            $controles3['avisopie'] = array();
            $controles3['mostrar'] = 'NO';
            $cantsoportes = 0;
            //inicializacion array para verificar soportes completos
            $soportesCompletos = 'NO';
            //Validacion si requiere soportes
            $soportesRequeridos = 'NO';
            if ($okBeneficio1780 == 'S') {
                $soportesRequeridos = 'SI';
                //Data del Controlador
                $avisopie['titulo'] = '!!! IMPORTANTE !!!';
                $avisopie['descripcion'] = 'Los soportes que se anexen (imágenes) deben estar debidamente diligenciados y firmados por quien corresponda, ser claros y exactos a las autoridades competentes.';
                $controles3['avisopie'] = $avisopie;
                $controles3['titulo'] = ++$e . '. Soportes para acceder al beneficio de la Ley 1780';
                $controles3['descripcion'] = 'De acuerdo con lo establecido en el Decreto 659 de 2017, para acceder a los beneficios de la Ley 1780 deberá anexar los soportes que se indican a continuación, serán de vital importancia para confirmar que usted puede acceder a los beneficios.';
                $controles3['mostrar'] = 'SI';
                $controles3['idliquidacion'] = $arrayTramite["tramite"] ["numeroliquidacion"];
                $cantupload = 0;
                //Array con la Data para consumir el metodo retornarListaSoportesLey1780 del API de confecamaras
                $arrSoportes = array('idliquidacion' => $arrayTramite['tramite']['idliquidacion'], 'tiposoporte' => 'ley1780');
                $soportes = $this->cUrl(array_merge($dataBasica, $arrSoportes), 'retornarSoportesLiquidacion');
                $soportesCompletos = 'SI';
                if (!empty($soportes)) {
                    $i = 0;
                    foreach ($soportes['expedientes'] as $ex) {
                        foreach ($ex['soportes'] as $sp) {
                            if (count($sp['documentos']) == 0) {
                                $soportesCompletos = 'NO';
                            }
                            //Incremento segun los soportes generados
                            $cantsoportes++;
                            //Incremento de los soportes generados
                            $i++;
                            $tablasoporte['consecutivo'] = $i . '.)';
                            $tablasoporte['identificador'] = $sp["identificador"];
                            $tablasoporte['descripcion'] = $sp["descripcion"];
                            $tablasoporte['observaciones'] = $sp["observaciones"];
                            $tablasoporte['idtipodoc'] = $sp["idtipodoc"];
                            $tablasoporte['documentos'] = array();
                            if (is_array($sp['documentos']) and count($sp['documentos']) > 0) {
                                foreach ($sp['documentos'] as $dc) {
                                    $documentos['idanexo'] = $dc['idanexo'];
                                    $documentos['observaciones'] = $dc['observaciones'];
                                    $documentos['idtipodoc'] = $dc['idtipodoc'];
                                    $tablasoporte['documentos'][] = $documentos;
                                }
                            }
                            if (is_array($sp['documentos']) and $sp['documentos'] == array() or count($sp['documentos']) <> 1) {
                                $tablasoporte['mostrar'] = 'SI';
                            } else {
                                $tablasoporte['mostrar'] = 'NO';
                            }
                            //Asignacion del array de los soportes generados al controlador en la posicion soportes
                            $controles3['soportes'][] = $tablasoporte;
                        }
                    }
                }
            }
            //Data del Controlador Panel Soportes Ley 1780
            $controles['mostrar'] = 'SI';
            if ($arrayTramite ["tramite"] ["idestado"] < '05') {
                if ($validacionCajero["escajero"] == 'SI') {
                    //
                    $controles['descripcion'] = 'Señor cajero, confirme al usuario el valor total de la renovación y proceda, digitar la información de los formularios. ';
                } else {
                    //
                    $controles['descripcion'] = 'Apreciado usuario, si aceptó la liquidación y decide continuar con el proceso, deberá grabar los formularios para cada una de las matrículas o inscripciones involucradas, para hacerlo siga el enlace <b>FORMULARIO</b> que aparece al frente de cada una de ellas.';
                }
            }
            //Inicio control soportes ponal-multas
            $cantsoportesponal = 0;
            $cantuploadponal = 0;
            //Controlador del Panel de Multas Codigo Policia
            $controlesPonalMultas = array();
            $controlesPonalMultas['idpanel'] = "4";
            $controlesPonalMultas['liquidacion'] = $arrayTramite['tramite']['idliquidacion'];
            $controlesPonalMultas['titulo'] = 'ponal-multas';
            $controlesPonalMultas['mostrar'] = 'NO';
            $controlesPonalMultas['descripcion'] = '';
            $controlesPonalMultas['avisopie'] = array();
            //
            if ($arrayTramite["tramite"]["multadoponal"] == 'S') {
                $soportesRequeridos = 'SI';
                $controlesPonalMultas['mostrar'] = 'SI';
                $avisopie['titulo'] = '!!! IMPORTANTE !!!';
                $avisopie['descripcion'] = 'Los soportes que se anexen (imágenes) deben estar debidamente diligenciados y firmados por quien corresponda, ser claros y exactos. Se entenderán válidos y reemplazan las copias físicas que se entregan ante la Cámara de Comercio. En caso de detectarse falsedad esta será remitida a las autoridades competentes.';
                $controlesPonalMultas['avisopie'] = $avisopie;
                $controlesPonalMultas['titulo'] = ++$e . '. Aparece multado según Código de Policía';
                if ($validacionCajero["escajero"] == 'SI') {
                    $txt = 'Señor cajero debe anexar el soporte (soportes) del pago de ' .
                                            'la multa (multas) para poder continuar con el proceso ' .
                                            'de renovación. Haga click <a href="' . $TIPO_HTTP . $HTTP_HOST . '/librerias/proceso/verMultas.php?accion=ver&idliquidacion=' . $_SESSION["tramite"]["numeroliquidacion"] . '" target="_blank">aquí</a> para conocer las multas del cliente.';
                } else {
                    $txt = 'Para continuar con el proceso de renovación debe anexar el(los) soporte(s) de pago de la(s) multa(s) que indica el Sistema de Multas y sanciones de la Policía. Para ver las multas encontradas, haga click '
                                            . '<a href="' . $TIPO_HTTP . $HTTP_HOST . '/librerias/proceso/verMultas.php?accion=ver&idliquidacion=' . $arrayTramite["tramite"]["numeroliquidacion"] . '" target="_blank">aquí</a>';
                }
                $controlesPonalMultas['descripcion'] = $txt;
                $controlesPonalMultas['mostrar'] = 'SI';
                $tablasoporte = array();
                //array con la data para consumir el metodo ponal-multas del API de confecamaras
                $arrSoportes = array('idliquidacion' => $arrayTramite['tramite']['idliquidacion'], 'tiposoporte' => 'ponal-multas');
                $soportes = $this->cUrl(array_merge($dataBasica, $arrSoportes), 'retornarSoportesLiquidacion');
                $soportesCompletos = 'SI';
                //
                if ($soportes['codigoerror'] == '0000' && !empty($soportes['expedientes'])) {
                    $i = 0;
                    foreach ($soportes['expedientes'] as $ex) {
                        $cantsoportesponal++;
                        foreach ($ex['soportes'] as $sp) {
                            if (count($sp['documentos']) == 0) {
                                $soportesCompletos = 'NO';
                            }
                            $i++;
                            $tablasoporte['consecutivo'] = $i . '.)';
                            $tablasoporte['identificador'] = $sp["identificador"];
                            $tablasoporte['descripcion'] = $ex["expediente"] . " - " . $sp["descripcion"];
                            $tablasoporte['idtipodoc'] = $sp["idtipodoc"];
                            $tablasoporte['expediente'] = $ex["expediente"];
                            $tablasoporte['observaciones'] = $sp["observaciones"];
                            $tablasoporte['mostrar'] = "SI";
                            $tablasoporte['documentos'] = array();
                            if ($arrayTramite["tramite"]["idestado"] < '04' || $arrayTramite["tramite"]["idestado"] == '10') {
                                if (isset($sp["tipoformulario"]) && (trim($sp["tipoformulario"]) == '' || $sp["tipoformulario"] == 'NO')) {
                                    $tablasoporte['anexarSoportePonal'] = 'SI';
                                }
                            }
                            if (is_array($sp['documentos']) and count($sp['documentos']) > 0) {
                                foreach ($sp['documentos'] as $dc) {
                                    $cantuploadponal++;
                                    $documentos['idanexo'] = $dc['idanexo'];
                                    $documentos['idtipodoc'] = $dc['idtipodoc'];
                                    $documentos['observaciones'] = $dc['observaciones'];
                                    $documentos['inputs'] = array(array("type" => "botton", 'mostrar' => "SI", 'label' => 'Ver anexo'), array("type" => "botton", 'mostrar' => "SI", 'label' => 'Eliminar anexo'));
                                    $tablasoporte['documentos'][] = $documentos;
                                    $tablasoporte['mostrar'] = "NO";
                                }
                            }
                            $controlesPonalMultas['soportes'][] = $tablasoporte;
                            $tablasoporte['documentos'] = array();
                        }
                    }
                }
            }
            //Fin control soportes ponal-multas
            $controles['titulo'] = ++$e . '. Diligenciamiento e impresión de formularios';

            // INICIO Control Ponal POT - relacionAltoImpacto
            if (!isset($ACTIVAR_CONTROL_POT_PONAL)) {
                $ACTIVAR_CONTROL_POT_PONAL = 'NO';
            }
            //Controlador Panel Ponal-Pot
            $relacionAltoImpacto = array();
            $conrestriccionnuevo = 'NO';
            $conrestriccioninicial = 'NO';
            $panelAltoImpacto = array();
            $panelAltoImpacto['titulo'] = 'ponal-pot';
            $panelAltoImpacto['descripcion'] = '';
            $panelAltoImpacto['avisopie'] = array();
            $panelAltoImpacto['mostrar'] = 'NO';
            $panelAltoImpacto['idpanel'] = "5";
            $panelAltoImpacto['liquidacion'] = $arrayTramite['tramite']['idliquidacion'];
            //            print_r($ACTIVAR_CONTROL_POT_PONAL);die();
            if ($ACTIVAR_CONTROL_POT_PONAL == 'SI') {
                //
                foreach ($arrayTramite ["tramite"] ["expedientes"] as $matri) {
                    if ($matri ["registrobase"] == 'S') {
                        $query = "idliquidacion=" . $arrayTramite ["tramite"] ["numeroliquidacion"] . " and expediente='" . $matri ["matricula"] . "' and idestado='2'";
                        $dataPeticion = array('tabla' => "mreg_liquidaciondatos", 'query' => $query);
                        $dataRespuesta = $this->cUrl(array_merge($dataBasica, $dataPeticion), "contarRegistros");
                        if ($dataRespuesta['codigoerror'] == '0000' && $dataRespuesta['conteo'] > 0) {
                            $dataPeticion = array("tabla" => 'mreg_liquidaciondatos',
                                                "query" => "idliquidacion=" . $arrayTramite ["tramite"] ["numeroliquidacion"] . " and expediente='" . $matri ["matricula"] . "'",
                                                "campos" => '*',
                                                "orden" => '',
                                                "offset" => '0',
                                                "limit" => '1000'
                                            );
                            $serx = $this->cUrl(array_merge($dataBasica, $dataPeticion), "retornarRegistros");
                            $rst = array();
                            if (!empty($serx['registros'][1]['xml'])) {
                                $xml = simplexml_load_string(str_replace('&', '&amp;', $serx['registros'][1]['xml']), "SimpleXMLElement", LIBXML_NOCDATA);
                                $json = json_encode($xml);
                                $rst = json_decode($json, true);
                                if (isset($rst['expediente']['ciius']['ciiu'])) {
                                    $rsCiiu = $rst['expediente']['ciius']['ciiu'];
                                    $ciius = array("I5630", "S9609");
                                    if (!empty($rsCiiu) && is_array($rsCiiu) && (in_array($ciius[0], $rsCiiu) || in_array($ciius[1], $rsCiiu))) {
                                        $conrestriccionnuevo = 'SI';
                                    } elseif (in_array($rsCiiu, $ciius)) {
                                        $conrestriccionnuevo = 'SI';
                                    }
                                }
                            }
                            $dataPeticion["query"] = "idliquidacion=" . $arrayTramite ["tramite"] ["numeroliquidacion"] . " and matricula='" . $matri ["matricula"] . "' and momento='I'";
                            $dataPeticion["tabla"] = "mreg_renovacion_datos_control";
                            $dataPeticion["campos"] = "*";
                            $regsx = $this->cUrl(array_merge($dataBasica, $dataPeticion), "retornarRegistros");
                            $indat = array();
                            foreach ($regsx["registros"] as $rg) {
                                $indat[$rg["dato"]] = $rg["contenido"];
                                if ($rg["contenido"] == 'I5630' || $rg["contenido"] == 'S9609') {
                                    $conrestriccioninicial = 'SI';
                                }
                            }
                            if ($conrestriccionnuevo == 'SI' && $conrestriccioninicial == 'NO' && isset($rst["expediente"]["identificacion"])) {
                                $relacionAltoImpacto[$matri ["matricula"]] = array(
                                                    'identificacion' => $rst["expediente"]["identificacion"],
                                                    'nombre' => $rst["expediente"]["nombre"]
                                                );
                            } elseif ($conrestriccionnuevo == 'SI' && isset($rst["expediente"]["nombre"])) {
                                if ((isset($indat["nombre"]) && $indat["nombre"] != $rst["expediente"]["nombre"]) || (isset($indat["dircom"]) && $indat["dircom"] != $rst["expediente"]["dircom"]) || (isset($indat["muncom"]) && $indat["muncom"] != $rst["expediente"]["muncom"])) {
                                    $relacionAltoImpacto[$matri ["matricula"]] = array(
                                                        'identificacion' => $rst["expediente"]["identificacion"],
                                                        'nombre' => $rst["expediente"]["nombre"]
                                                    );
                                }
                            }
                            unset($indat);
                            unset($regsx);
                        }
                    }
                }
            }
            /*             * *********************************************************************
             * Actualiza la condición de alto impacto de la liquidación
             * ********************************************************************* */
            //            $arrayTramite["tramite"]["controlaactividadaltoimpacto"] = 'N';
            if (!empty($relacionAltoImpacto)) {
                $arrayTramite["tramite"]["controlaactividadaltoimpacto"] = 'S';
                $panelAltoImpacto['mostrar'] = 'SI';
            }
            /*             * *********************************************************************
             * Muestra soportes de alto impacto
             * ********************************************************************* */

            $cantsoportespot = 0;
            $cantuploadpot = 0;
            //
            if (!empty($relacionAltoImpacto)) {
                $soportesRequeridos = 'SI';
                $panelAltoImpacto['titulo'] = ++$e . '. Realiza actividades de alto impacto que requieren autorización de uso de suelos (POT)';
                $panelAltoImpacto['avisopie']['titulo'] = '!!! IMPORTANTE !!!';
                $panelAltoImpacto['avisopie']['descripcion'] = 'Los soportes que se anexen (im&aacute;genes) deben estar debidamente diligenciados y firmados por quien corresponda, ser claros y exactos. '
                           . 'Se entender&aacute;n v&aacute;lidos y reemplazan las copias f&iacute;sicas que se entregan ante la C&aacute;mara de Comercio. En caso de detectarse falsedad esta ser&aacute; remitida '
                           . 'a las autoridades competentes.';
                $panelAltoImpacto['descripcion'] = 'Para cada una de las matrículas que se indican a continuación deberá anexar el certificado de uso de suelos dado que se realiza una actividad de alto impacto';
                $panelAltoImpacto['soportes'] = array();
                $i = 0;
                $tablasoporte = array();
                $arrSoportes = array('idliquidacion' => $arrayTramite ["tramite"] ["numeroliquidacion"], 'tiposoporte' => 'ponal-pot');
                $anx = $this->cUrl(array_merge($dataBasica, $arrSoportes), 'retornarSoportesLiquidacion');
                if ($anx ["codigoerror"] == '0000' && !empty($anx["expedientes"])) {
                    foreach ($anx["expedientes"] as $expedientes) {
                        $soportesCompletos = 'SI';
                        if (!empty($expedientes["soportes"])) {
                            foreach ($expedientes["soportes"] as $soportes) {
                                if (count($soportes["documentos"]) == 0) {
                                    $soportesCompletos = 'NO';
                                }
                                $i++;
                                $tablasoporte['consecutivo'] = $i . '.) ';
                                $tablasoporte["nombre"] = $expedientes["expediente"] . " - " . $expedientes["nombre"];
                                $tablasoporte["expediente"] = $expedientes["expediente"];
                                $tablasoporte["identificador"] = $soportes["identificador"];
                                $tablasoporte["idtipodoc"] = $soportes["idtipodoc"];
                                $tablasoporte["descripcion"] = $soportes["descripcion"];
                                $tablasoporte["mostrar"] = "SI";
                                $tablasoporte['documentos'] = array();
                                foreach ($soportes["documentos"] as $documento) {
                                    $documentos = array();
                                    $documentos["observaciones"] = $documento["observaciones"];
                                    $documentos["idanexo"] = $documento["idanexo"];
                                    $documentos["idtipodoc"] = $documento["idtipodoc"];
                                    $tablasoporte['documentos'][] = $documentos;
                                    $tablasoporte["mostrar"] = "NO";
                                }
                                $panelAltoImpacto['soportes'][] = $tablasoporte;
                            }
                        }
                    }
                }
            }
            //FIN Control Ponal POT - relacionAltoImpacto
            //Controlador del Panel de Activos Menores
            $controlactivosmenores = array();
            $controlactivosmenores['mostrar'] = 'NO';
            $controlactivosmenores['idpanel'] = "6";
            $controlactivosmenores['titulo'] = 'activos-menores';
            $controlactivosmenores['avisopie'] = array();
            $controlactivosmenores['descripcion'] = '';
            $controlactivosmenores['mostrarFile'] = "SI";
            $controlactivosmenores['anexo'] = array();
            $controlactivosmenores['anexo']["mostrar"] = "NO";
            $e = ($RENOVACION_BLOQUEAR_ACTIVOS_MENORES == 'S') ? $e++ : $e;
            if ($validacionCajero["escajero"] != 'SI') {
                if ($cantmin > 0 && $RENOVACION_BLOQUEAR_ACTIVOS_MENORES == 'S') {
                    $controlactivosmenores['mostrar'] = 'SI';
                    $controlactivosmenores['titulo'] = $e . '. Soportes';
                    $controlactivosmenores['descripcion'] = '!!! IMPORTANTE !!! <br> '
                               . 'Apreciado usuario, hemos detectado un problema con su renovaci&oacute;n, le recomendamos que se dirija a nuestra oficina m&aacute;s '
                               . 'cercana para ser atendido por uno de nuestros asesores. '
                               . 'Por favor disculpe los inconvenientes.<BR><BR>'
                               . 'De acuerdo con lo establecido en el art&iacute;culo '
                               . '38 del C&oacute;digo del Comercio, la falsedad en los datos que se '
                               . 'suministren al Registro Mercantil y de Entidades sin Animo de Lucro '
                               . 'sera sancionada conforme al C&oacute;digo Penal.<br><br>'
                               . 'De acuerdo al art&iacute;culo 36 del C&oacute;digo del Comercio '
                               . 'la C&aacute;mara de Comercio podr&aacute; constatar los datos '
                               . 'suministrados por el Empresario.';
                    $controlactivosmenores['mostrarFile'] = "NO";
                }
            }
            // Control de balances para renovaci&oacute;n con activos inferiores
            if ($cantminprincipales > 0 && $RENOVACION_BLOQUEAR_ACTIVOS_MENORES != 'S') {
                $controlactivosmenores['mostrar'] = 'SI';
                $controlactivosmenores['titulo'] = $e . '. Soportes';
                if ($validacionCajero["escajero"] == 'SI') {
                    $controlactivosmenores['descripcion'] = 'Se&ntilde;or cajero, por favor recu&eacute;rdele al usuario que es necesario anexar los balances con corte al &uacute;ltimo a&ntilde;o '
                               . 'renovado puesto que se est&aacute;n disminuyendo los activos en relaci&oacute;n con el a&ntilde;o anterior.<br><br>';
                } else {
                    $controlactivosmenores['descripcion'] = 'Se&ntilde;or usuario, dado que est&aacute; renovando con activos inferiores a los del a&ntilde;o inmediatamente anterior '
                               . 'es necesario que adicione, en formato PDF (solo en este formato), los balances con corte a 31 de diciembre del a&ntilde;o inmediatamente anterior. Esta informaci&oacute;n '
                               . 'es de suma importancia para que legalmente quede justificada su disminuci&oacute;n de activos. No reportar esta informaci&oacute;n o hacerlo en forma indebida '
                               . 'puede traer como consecuencia que el tr&aacute;mite de renovaci&oacute;n no quede registrado.';
                }
                $dataPeticion = array("idliquidacion" => $arrayTramite ["tramite"] ["numeroliquidacion"]);
                $anexoBalance = $this->cUrl(array_merge($dataBasica, $dataPeticion), "recuperarAnexoBalanceLiquidacion");
                $controlactivosmenores['anexo'] = array();
                if ($anexoBalance['codigoerror'] == '0000') {
                    $controlactivosmenores['anexo']["link"] = $anexoBalance["link"];
                    $controlactivosmenores['anexo']["mostrar"] = "SI";
                }
            }
            //FIN Activos Menores
            //
            $mostrartextopagoelectronico = 'no';
            $mostrartextofirmadoelectronico = 'no';
            $mostrarResto = 'si';
            if ($cantmin > 0 && $RENOVACION_BLOQUEAR_ACTIVOS_MENORES == 'S' && $validacionCajero["escajero"] != 'SI') {
                $mostrarResto = 'no';
            }
            //validacion formularios grabados
            $validacionformularioscompletos = 'NO';
            if ($imprimirFormulariosAll) {
                $validacionformularioscompletos = 'SI';
            }
            //data peticion para consumir el metodo consultarBotonesLiquidacion
            $validacionesBotones = array('idliquidacion' => $arrayTramite ["tramite"] ["numeroliquidacion"], 'requieresoportes' => $soportesRequeridos, 'soportescompletos' => $soportesCompletos, 'formularioscompletos' => $validacionformularioscompletos);
            //Consumo metodo Api consultarBotonesLiquidacion para renderizar el panel de los botones
            $dataPeticion = $this->cUrl(array_merge($dataBasica, $validacionesBotones), 'consultarBotonesLiquidacion');
            //Controlador del panel de numero de
            //die(print_r($dataPeticion));
            $controladornumeror = array();
            $controladornumeror['idpanel'] = '7';
            $controladornumeror['mostrar'] = 'NO';
            $controladornumeror['titulo'] = '';
            $controladornumeror['descripcion'] = '';
            $controladornumeror['numeroRecuperacion'] = '';
            if (isset($dataPeticion ['controles']['inputs'])) {
                foreach ($dataPeticion ['controles']['inputs'] as $key) {
                    if ($key["label"] == 'Firma Electrónica' and $key["mostrar"] == 'SI') {
                        $mostrartextofirmadoelectronico = 'si';
                    }
                    if ($arrayTramite ["tramite"] ["idestado"] == '19') {
                        $mostrartextopagoelectronico = 'si';
                    }
                }
            }

            //print_r($mostrartextofirmadoelectronico);
            //die();
            if ($mostrarResto == 'si') {
                if ($mostrartextofirmadoelectronico == 'si') {
                    $controladornumeror['titulo'] = ++$e . '. Número de Recuperación';
                    $controladornumeror['descripcion'] = 'Usted puede pagar en forma electrónica (Sistema PSE o mediante tarjetas de crédito) o en las oficinas de bancos y Corresponsales Bancarios, sin tener que desplazarse a nuestras instalaciones para realizar el pago y la radicación del trámite. O puede, si lo desea, acercarse a una de nuestras sedes y citar el código.';
                    $controladornumeror['numeroRecuperacion'] = $arrayTramite["tramite"]["numerorecuperacion"];
                    $controladornumeror['mostrar'] = 'SI';
                }
                if ($mostrartextopagoelectronico == 'si') {
                    $controladornumeror['titulo'] = ++$e . '. Pago de la Renovación';
                    $controladornumeror['descripcion'] = 'El trámite se encuentra Firmado Electrónicamente. Usted puede proceder a continuación a realizar el pago a través de alguno de los mecanismos que la Cámara de Comercio ha dispuesto para el efecto. Dado que el pago está firmado electrónicamente, no es necesario que imprima los formularios ni que los presente f&iacute;sicamente en nuestras oficinas. Lo invitamos a hacer uso de las formas de pago NO PRESENCIALES que hemos dispuesto para usted oprimiendo el botón "Pago Electrónico".';
                    $controladornumeror['mostrar'] = 'SI';
                }
            }

            //Control Panel Botones
            $controlBotones2 = array();
            $controlBotones2 ['idpanel'] = '8';
            $controlBotones2['titulo'] = ++$e . '. ' . $dataPeticion ['controles']['titulopanel'];
            $controlBotones2['mostrar'] = 'SI';
            $controlBotones2['botones'] = array();
            $controlBotones2['botones'] = $dataPeticion ['controles']['inputs'];
            //asignacion de controles a los paneles
            $arrayTramite['controles'][] = $controles2;
            $arrayTramite['controles'][] = $controles3;
            $arrayTramite['controles'][] = $controlesPonalMultas;
            $arrayTramite['controles'][] = $controles;
            $arrayTramite['controles'][] = $panelAltoImpacto;
            $arrayTramite['controles'][] = $controlactivosmenores;
            $arrayTramite['controles'][] = $controladornumeror;
            $arrayTramite['controles'][] = $controlBotones2;
            //
            $_SESSION['jsonsalida']['tramite'] = $arrayTramite["tramite"];
            $_SESSION['jsonsalida']['controles'] = $arrayTramite['controles'];

            // **************************************************************************** //
            // Resultado
            // **************************************************************************** //
            //
            \logApi::peticionRest('api_' . __FUNCTION__);
            $json = $api->json($_SESSION["jsonsalida"]);
            $api->response(str_replace("\\/", "/", $json), 200);
        }
    }

    public function cUrl($data, $metodo)
    {
        $url = "http://localhost:80";
        if ($url) {
            $json_data = json_encode($data);
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url . "/librerias/wsRestSII/v1/" . $metodo);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
            $result = curl_exec($ch);
            curl_close($ch);
            if ($result != null) {
                return json_decode($result, true);
            } else {
                return json_encode(array("codigoerror" => "E9999", "mensajeerror" => "Ocurrió un error de comunicación con el API, por favor intente más tarde."));
            }
        } else {
            return json_encode(array("codigoerror" => "E9999", "mensajeerror" => "No se han seteado los datos para realizar el consumo del API"));
        }
    }

    public function valorConstante($dataBasica, $constante)
    {
        $constanteRecibida = array('constante' => $constante);
        $dataRespuesta = $this->cUrl(array_merge($dataBasica, $constanteRecibida), "retornarConstante");
        return ($dataRespuesta['constante']);
    }
}

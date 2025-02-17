<?php

namespace libreriaswsRestSII;

use libreriaswsRestSII\API;

trait consultarRecibo {

    public function consultarRecibo(API $api) {
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/myErrorHandler.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/s3_v4_api.php');
        $resError = set_error_handler('myErrorHandler');


        // array de respuesta
        $_SESSION["jsonsalida"] = array();
        $_SESSION["jsonsalida"]["codigoerror"] = '0000';
        $_SESSION["jsonsalida"]["mensajeerror"] = '';
        $_SESSION['jsonsalida']['recibo'] = '';
        $_SESSION['jsonsalida']['fecha'] = '';
        $_SESSION['jsonsalida']['hora'] = '';
        $_SESSION['jsonsalida']['estado'] = '';
        $_SESSION['jsonsalida']['operacion'] = '';
        $_SESSION['jsonsalida']['factura'] = '';
        $_SESSION['jsonsalida']['radicado'] = '';
        $_SESSION['jsonsalida']['estadoradicado'] = '';
        $_SESSION['jsonsalida']['rutasii'] = '';
        $_SESSION['jsonsalida']['usuario'] = '';
        $_SESSION['jsonsalida']['tipogasto'] = '';
        $_SESSION['jsonsalida']['idclase'] = '';
        $_SESSION['jsonsalida']['identificacion'] = '';
        $_SESSION['jsonsalida']['nombre'] = '';
        $_SESSION['jsonsalida']['direccion'] = '';
        $_SESSION['jsonsalida']['municipio'] = '';
        $_SESSION['jsonsalida']['telefono'] = '';
        $_SESSION['jsonsalida']['email'] = '';
        $_SESSION['jsonsalida']['tipotramite'] = '';
        $_SESSION['jsonsalida']['subtipotramite'] = '';
        $_SESSION['jsonsalida']['tiporegistro'] = '';
        $_SESSION['jsonsalida']['valorneto'] = '';
        $_SESSION['jsonsalida']['idformapago'] = '';
        $_SESSION['jsonsalida']['numeroautorizacion'] = '';
        $_SESSION['jsonsalida']['tipodoc'] = '';
        $_SESSION['jsonsalida']['numerodoc'] = '';
        $_SESSION['jsonsalida']['origendoc'] = '';
        $_SESSION['jsonsalida']['fechadoc'] = '';
        $_SESSION['jsonsalida']['municipiodoc'] = '';
        $_SESSION['jsonsalida']['numerointernorue'] = '';
        $_SESSION['jsonsalida']['numerounicorue'] = '';
        $_SESSION['jsonsalida']['cumplorequisitosbenley1780'] = '';
        $_SESSION['jsonsalida']['mantengorequisitosbenley1780'] = '';
        $_SESSION['jsonsalida']['renunciobeneficiosley1780'] = '';
        $_SESSION['jsonsalida']['multadoponal'] = '';
        $_SESSION['jsonsalida']['controlactividadaltoimpacto'] = '';
        $_SESSION['jsonsalida']['verificacionsoportes'] = '';        
        $_SESSION['jsonsalida']['asentado'] = '';
        $_SESSION['jsonsalida']['idliquidacion'] = 0;
        $_SESSION['jsonsalida']['firmadoelectronico'] = '';
        $_SESSION['jsonsalida']['firmadomanuscrita'] = '';
        $_SESSION['jsonsalida']['servicios'] = array();
        $_SESSION['jsonsalida']['imagenes'] = array();
        $_SESSION['jsonsalida']['certificados'] = array();
        $servicios = array();
        $imagenes = array();
        $certificados = array();


        // Verifica que  método de recepcion de parámetros sea POST
        if ($api->get_request_method() != "POST") {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'La petición debe ser POST';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        $api->validarParametro("usuariows", true);
        $api->validarParametro("token", true);
        $api->validarParametro("recibo", true);

        // ********************************************************************** //
        // Valida que el usuario reportado tenga acceso a la BD y al metodo
        // ********************************************************************** // 
        if (!$api->validarToken('consultarRecibo', $_SESSION["entrada"]["token"], $_SESSION["entrada"]["usuariows"])) {
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        $mysqli = conexionMysqliApi();
        
        if ($mysqli === false) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Error de conexión a BD';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        // ********************************************************************** //
        // Construye arreglo de tipos documentales
        // ********************************************************************** // 
        $trd = array();
        $query = "SELECT * from bas_tipodoc where 1=1 order by idtipodoc";
        $mysqli->set_charset("utf8");
        $resQueryTD = $mysqli->query($query);
        if (!empty($resQueryTD)) {
            while ($trdtemp = $resQueryTD->fetch_array(MYSQLI_ASSOC)) {
                $trd[$trdtemp['idtipodoc']] = array(
                    'tiposirep' => $trdtemp['homologasirep'],
                    'tipodigitalizacion' => $trdtemp['homologadigitalizacion']
                );
            }
        }
        $resQueryTD->free();

        // ********************************************************************** //
        // Consulta el recibo
        // ********************************************************************** // 
        $origen = '';
        $res = retornarRegistroMysqliApi($mysqli, 'mreg_recibosgenerados', "recibo='" . $_SESSION["entrada"]["recibo"] . "'");
        if ($res === false || empty($res)) {
            $res1 = retornarRegistrosMysqliApi($mysqli, 'mreg_est_recibos', "numerorecibo='" . $_SESSION["entrada"]["recibo"] . "'", "id");
            if ($res1 === false || empty($res1)) {
                $mysqli->close();
                $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                $_SESSION["jsonsalida"]["mensajeerror"] = 'Recibo no localizado en la BD.';
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
            } else {
                $origen = 'recibo';
                $icon = 0;
                $valortotal = 0;
                foreach ($res1 as $res) {
                    $icon++;
                    if ($icon == 1) {
                        $_SESSION['jsonsalida']['recibo'] = trim((string)$res['numerorecibo']);
                        $_SESSION['jsonsalida']['fecha'] = trim((string)$res['fecoperacion']);
                        $_SESSION['jsonsalida']['hora'] = trim((string)$res['horaoperacion']);
                        $_SESSION['jsonsalida']['estado'] = trim((string)$res['ctranulacion']);
                        $_SESSION['jsonsalida']['operacion'] = trim((string)$res['numerooperacion']);
                        $_SESSION['jsonsalida']['factura'] = trim((string)$res['numfactura']);
                        $_SESSION['jsonsalida']['usuario'] = trim((string)$res['operador']);
                        $_SESSION['jsonsalida']['tipogasto'] = trim((string)$res['tipogasto']);
                        $_SESSION['jsonsalida']['idclase'] = trim((string)$res['idclase']);
                        $_SESSION['jsonsalida']['identificacion'] = trim((string)$res['identificacion']);
                        $_SESSION['jsonsalida']['nombre'] = trim((string)$res['nombre']);
                        $_SESSION['jsonsalida']['direccion'] = trim((string)$res['direccion']);
                        $_SESSION['jsonsalida']['municipio'] = trim((string)$res['municipio']);
                        $_SESSION['jsonsalida']['telefono'] = trim((string)$res['telefono']);
                        $_SESSION['jsonsalida']['email'] = '';
                    }
                    $valortotal = $valortotal + $res["valor"];
                }
                $_SESSION['jsonsalida']['valorneto'] = $valortotal;
                $_SESSION['jsonsalida']['tipodoc'] = "";
                $_SESSION['jsonsalida']['numerodoc'] = "";
                $_SESSION['jsonsalida']['origendoc'] = "";
                $_SESSION['jsonsalida']['fechadoc'] = "";
                $_SESSION['jsonsalida']['municipiodoc'] = "";
                $_SESSION['jsonsalida']['numerointernorue'] = trim((string)$res['numinterno']);
                $_SESSION['jsonsalida']['numerounicorue'] = trim((string)$res['numunico']);
                $cba = retornarRegistroMysqliApi($mysqli, 'mreg_est_codigosbarras', "recibo='" . $res['numerorecibo'] . "'");
                if ($cba && !empty($cba)) {
                    $_SESSION['jsonsalida']['radicado'] = $cba['codigobarras'];
                    $_SESSION['jsonsalida']['estadoradicado'] = $cba['estadofinal'];
                    $_SESSION['jsonsalida']['rutasii'] = trim((string)$cba['actoreparto']);
                    $_SESSION['jsonsalida']['tipodoc'] = trim((string)$cba['tipdoc']);
                    $_SESSION['jsonsalida']['numerodoc'] = trim((string)$cba['numdoc']);
                    $_SESSION['jsonsalida']['origendoc'] = trim((string)$cba['oridoc']);
                    $_SESSION['jsonsalida']['fechadoc'] = trim((string)$cba['fecdoc']);
                    $_SESSION['jsonsalida']['municipiodoc'] = trim((string)$cba['mundoc']);
                    if (strtoupper($cba['verificacionsoportes']) == 'SI' || strtoupper($cba['verificacionsoportes']) == 'S') {
                        $_SESSION['jsonsalida']['verificacionsoportes'] = trim((string)$cba['verificacionsoportes']);
                    } else {
                        $_SESSION['jsonsalida']['verificacionsoportes'] = 'N';
                    }
                } else {
                    if ($_SESSION['jsonsalida']['operacion'] != '') {
                        $cbas = retornarRegistrosMysqliApi($mysqli, 'mreg_est_codigosbarras', "operacion='" . $_SESSION['jsonsalida']['operacion'] . "'", "codigobarras");
                        if ($cbas && !empty($cbas)) {
                            foreach ($cbas as $cba) {
                                if (substr($cba["fecharadicacion"], 0, 4) == substr($res["fecoperacion"], 0, 4)) {
                                    $_SESSION['jsonsalida']['radicado'] = $cba['codigobarras'];
                                    $_SESSION['jsonsalida']['rutasii'] = trim((string)$cba['actoreparto']);
                                    $_SESSION['jsonsalida']['tipodoc'] = trim((string)$cba['tipdoc']);
                                    $_SESSION['jsonsalida']['numerodoc'] = trim((string)$cba['numdoc']);
                                    $_SESSION['jsonsalida']['origendoc'] = trim((string)$cba['oridoc']);
                                    $_SESSION['jsonsalida']['fechadoc'] = trim((string)$cba['fecdoc']);
                                    $_SESSION['jsonsalida']['municipiodoc'] = trim((string)$cba['mundoc']);
                                }
                            }
                        }
                    }
                }
            }
        } else {
            $origen = 'recibosgenerados';
            $_SESSION['jsonsalida']['recibo'] = trim((string)$res['recibo']);
            $_SESSION['jsonsalida']['fecha'] = trim((string)$res['fecha']);
            $_SESSION['jsonsalida']['hora'] = trim((string)$res['hora']);
            $_SESSION['jsonsalida']['estado'] = '';
            if ($res["estado"] == '01' || $res["estado"] == '02') {
                $_SESSION['jsonsalida']['estado'] = '0';
            }
            if ($res["estado"] == '03') {
                $_SESSION['jsonsalida']['estado'] = '2';
            }
            if ($res["estado"] == '99') {
                $_SESSION['jsonsalida']['estado'] = '1';
            }
            $_SESSION['jsonsalida']['operacion'] = trim((string)$res['operacion']);
            $_SESSION['jsonsalida']['factura'] = trim((string)$res['factura']);
            $_SESSION['jsonsalida']['radicado'] = trim((string)$res['codigobarras']);
            $_SESSION['jsonsalida']['usuario'] = trim((string)$res['usuario']);
            $_SESSION['jsonsalida']['tipogasto'] = trim((string)$res['tipogasto']);
            $_SESSION['jsonsalida']['idclase'] = trim((string)$res['tipoidentificacion']);
            $_SESSION['jsonsalida']['identificacion'] = trim((string)$res['identificacion']);
            $_SESSION['jsonsalida']['nombre'] = trim((string)$res['razonsocial']);
            $_SESSION['jsonsalida']['direccion'] = trim((string)$res['direccion']);
            $_SESSION['jsonsalida']['municipio'] = trim((string)$res['municipio']);
            $_SESSION['jsonsalida']['telefono'] = isset($res['telefono']) ? trim((string)$res['telefono']): '';
            $_SESSION['jsonsalida']['email'] = trim((string)$res['email']);
            $_SESSION['jsonsalida']['tipotramite'] = trim((string)$res['tipotramite']);
            $_SESSION['jsonsalida']['subtipotramite'] = retornarRegistroMysqliApi($mysqli, 'mreg_liquidacion_campos', "idliquidacion=" . $res["idliquidacion"] . " and campo='subtipotramite'","contenido");
            $_SESSION['jsonsalida']['valorneto'] = doubleval($res['valorneto']);
            $_SESSION['jsonsalida']['tipodoc'] = "";
            $_SESSION['jsonsalida']['numerodoc'] = "";
            $_SESSION['jsonsalida']['origendoc'] = "";
            $_SESSION['jsonsalida']['fechadoc'] = "";
            $_SESSION['jsonsalida']['municipiodoc'] = "";
            $_SESSION['jsonsalida']['numerointernorue'] = trim((string)$res['numerointernorue']);
            $_SESSION['jsonsalida']['numerounicorue'] = trim((string)$res['numerounicorue']);
            $_SESSION['jsonsalida']['idliquidacion'] = $res["idliquidacion"];

            //
            if (trim((string)$res['codigobarras'] != "")) {
                $cba = retornarRegistroMysqliApi($mysqli, 'mreg_est_codigosbarras', "codigobarras='" . $res['codigobarras'] . "'");
                if ($cba && !empty($cba)) {
                    $_SESSION['jsonsalida']['estadoradicado'] = $cba['estadofinal'];
                    $_SESSION['jsonsalida']['rutasii'] = trim((string)$cba['actoreparto']);
                    $_SESSION['jsonsalida']['tipodoc'] = trim((string)$cba['tipdoc']);
                    $_SESSION['jsonsalida']['numerodoc'] = trim((string)$cba['numdoc']);
                    $_SESSION['jsonsalida']['origendoc'] = trim((string)$cba['oridoc']);
                    $_SESSION['jsonsalida']['fechadoc'] = trim((string)$cba['fecdoc']);
                    $_SESSION['jsonsalida']['municipiodoc'] = trim((string)$cba['mundoc']);
                    if (strtoupper($cba['verificacionsoportes']) == 'SI' || strtoupper($cba['verificacionsoportes']) == 'S') {
                        $_SESSION['jsonsalida']['verificacionsoportes'] = trim((string)$cba['verificacionsoportes']);
                    } else {
                        $_SESSION['jsonsalida']['verificacionsoportes'] = 'N';
                    }
                }
            }
        }

        $resy = retornarRegistroMysqliApi($mysqli, 'mreg_liquidacion', "numerorecibo='" . $_SESSION["entrada"]["recibo"] . "'");
        if ($resy && !empty($resy)) {
            $_SESSION['jsonsalida']['idformapago'] = $resy["idformapago"];
            $_SESSION['jsonsalida']['numeroautorizacion'] = $resy["numeroautorizacion"];
            $_SESSION['jsonsalida']['cumplorequisitosbenley1780'] = $resy["cumplorequisitosbenley1780"];
            $_SESSION['jsonsalida']['mantengorequisitosbenley1780'] = $resy["mantengorequisitosbenley1780"];
            $_SESSION['jsonsalida']['renunciobeneficiosley1780'] = $resy["renunciobeneficiosley1780"];
            $_SESSION['jsonsalida']['multadoponal'] = $resy["multadoponal"];
            $_SESSION['jsonsalida']['controlactividadaltoimpacto'] = $resy["controlactividadaltoimpacto"];
            $_SESSION['jsonsalida']['idliquidacion'] = $resy["idliquidacion"];
            $_SESSION['jsonsalida']['firmadoelectronicamente'] = $resy["firmadoelectronicamente"];
            $_SESSION['jsonsalida']['firmadomanuscrita'] = $resy["firmadomanuscrita"];
            if ($resy["firmadomanuscrita"] == '' && ($resy["tipotramite"] == 'renovacionmatricula' || $resy["tipotramite"] == 'renovacionesadl')) {
                $temxx = retornarRegistrosMysqliApi($mysqli, 'mreg_firmadoelectronico_log', "idliquidacion=" . $resy["idliquidacion"], "fecha,hora");
                if ($temxx && !empty($temxx)) {
                    foreach ($temxx as $tx2) {
                        if (substr($tx2["respuesta"], 0, 25) == 'Firmo el tramite en forma') {
                            $_SESSION['jsonsalida']['firmadomanuscrita'] = 'si';
                        }
                    }
                }
            }
        }

        // Localiza si el recibo de caja se encuentra o no asentado
        // Toma como base el código de barras, su estado y los controles verificación soportes
        // Solo para trámites de renovacion
        // actoreparto IN ('17','18','22')) and (substring(operacion,1,2) NOT IN ('80','90')) and (verificacionsoportes = 'SI' and estadofinal NOT IN ('00','05','06','07','15','16','17','18','39','40','41','42','99'))                
        $arrayRutas = array ('17','18','22'); // Matrículas, Mutaciones, Renovaciones
        $arrayEstados = array ('00','05','06','07','15','16','17','18','39','40','41','42','99');        
        if (in_array($_SESSION['jsonsalida']["rutasii"],$arrayRutas)) {
            $_SESSION['jsonsalida']["asentado"] = 'S';            
            if (trim((string)$_SESSION['jsonsalida']["operacion"]) != '80' && trim((string)$_SESSION['jsonsalida']["operacion"]) != '90') {
                if ($_SESSION['jsonsalida']["verificacionsoportes"] == 'S') {
                    if (!in_array($_SESSION['jsonsalida']["estadoradicado"],$arrayEstados)) {
                        $_SESSION['jsonsalida']["asentado"] = 'N';
                    }
                }
            }
        }
        
        //
        $servbase = '';
        $res1 = retornarRegistrosMysqliApi($mysqli, 'mreg_est_recibos', "numerorecibo='" . $_SESSION["entrada"]["recibo"] . "'", "id");
        if ($res1 && !empty($res1)) {
            foreach ($res1 as $s1) {
                $mat = '';
                $pro = '';
                $serv = retornarRegistroMysqliApi($mysqli, 'mreg_servicios', "idservicio='" . trim((string)$s1['servicio']) . "'");
                if ($serv["tipoingreso"] > '20' && $serv["tipoingreso"] <= '30') {
                    $pro = $s1["matricula"];
                } else {
                    $mat = $s1["matricula"];
                }
                $servicio = array();
                if ($servbase == '') {
                    $servbase = trim((string)$s1['servicio']);
                }
                $servicio['servicio'] = trim((string)$s1['servicio']);
                $servicio['nservicio'] = $serv["nombre"];
                $servicio['matricula'] = $mat;
                $servicio['proponente'] = $pro;
                $servicio['identificacion'] = $s1["identificacion"];
                $servicio['nombre'] = $s1["nombre"];
                $servicio['cantidad'] = doubleval($s1['cantidad']);
                $servicio['valorbase'] = doubleval($s1['base']);
                $servicio['valorservicio'] = doubleval($s1['valor']);
                $servicio['ano'] = trim((string)$s1['anorenovacion']);
                $servicios[] = $servicio;
            }
            $_SESSION['jsonsalida']['servicios'] = $servicios;
        }

        //
        $_SESSION['jsonsalida']['tiporegistro'] = 'Otros';
        $lserv = retornarRegistroMysqliApi($mysqli, 'mreg_servicios', "idservicio='" . $servbase . "'");
        if ($lserv && !empty($lserv)) {
            if ($lserv["tipoingreso"] >= '01' && $lserv["tipoingreso"] <= '20') {
                $_SESSION['jsonsalida']['tiporegistro'] = 'RegMer';
            }
            if ($lserv["tipoingreso"] >= '11' && $lserv["tipoingreso"] <= '20') {
                $_SESSION['jsonsalida']['tiporegistro'] = 'RegEsadl';
            }
            if ($lserv["tipoingreso"] >= '21' && $lserv["tipoingreso"] <= '30') {
                $_SESSION['jsonsalida']['tiporegistro'] = 'RegPro';
            }
        }
        // *********************************************************************************************** //
        // Retornar imágenes formularios (503), recibo (509) y notificación sipref (518-519) del recibo
        // *********************************************************************************************** // 
        $anxs = array();
        if ($_SESSION['jsonsalida']['recibo'] != '' && $_SESSION['jsonsalida']['radicado'] == '') {
            $anxs = retornarRegistrosMysqliApi($mysqli, 'mreg_radicacionesanexos', "numerorecibo='" . $_SESSION['jsonsalida']['recibo'] . "' and tipoanexo IN('503','509','518','519')and eliminado<>'SI'", "idanexo");
        }
        if ($_SESSION['jsonsalida']['recibo'] != '' && $_SESSION['jsonsalida']['radicado'] != '') {
            $anxs = retornarRegistrosMysqliApi($mysqli, 'mreg_radicacionesanexos', "(numerorecibo='" . $_SESSION['jsonsalida']['recibo'] . "' or idradicacion='" . $_SESSION['jsonsalida']['radicado'] . "') and tipoanexo IN('503','509','518','519')and eliminado<>'SI'", "idanexo");
        }
        if ($_SESSION['jsonsalida']['recibo'] == '' && $_SESSION['jsonsalida']['radicado'] != '') {
            $anxs = retornarRegistrosMysqliApi($mysqli, 'mreg_radicacionesanexos', "idradicacion='" . $_SESSION['jsonsalida']['radicado'] . "' and tipoanexo IN('503','509','518','519')and eliminado<>'SI'", "idanexo");
        }
        if ($anxs && !empty($anxs)) {
            foreach ($anxs as $imagent) {
                $tiposirep = '';
                $tipodigitalizacion = '';
                if (isset($trd[$imagent["idtipodoc"]])) {
                    $tiposirep = $trd[$imagent["idtipodoc"]]["tiposirep"];
                    $tipodigitalizacion = $trd[$imagent["idtipodoc"]]["tipodigitalizacion"];
                }
                $imagen = array();
                if (TIPO_REPOSITORIO_NUEVAS_IMAGENES == 'EFS_S3') {
                    $imagen['url'] = obtenerUrlRepositorioS3Api($imagent['path']);
                } else {
                    $imagen['url'] = TIPO_HTTP . HTTP_HOST . "/" . PATH_RELATIVO_IMAGES . "/" . $_SESSION["entrada"]["codigoempresa"] . "/" . $imagent['path'];
                }

                $imagen['idanexo'] = ($imagent['idanexo']);
                $imagen['tipo'] = trim((string)$imagent['idtipodoc']);

                //WSIERRA : 2018-11-22  - Incluye campo tipoanexo
                $imagen['tipoanexo'] = trim((string)$imagent['tipoanexo']);

                $imagen['tiposirep'] = $tiposirep;
                $imagen['tipodigitalizacion'] = $tipodigitalizacion;
                $imagen['identificador'] = trim((string)$imagent['identificador']);
                $strings = explode(".", $imagent['path']);
                $imagen['formato'] = $strings[count($strings) - 1];
                $imagen['identificacion'] = trim((string)$imagent['identificacion']);
                $imagen['nombre'] = trim((string)$imagent['nombre']);
                $imagen['matricula'] = trim((string)$imagent['matricula']);
                $imagen['proponente'] = trim((string)$imagent['proponente']);
                $imagen['fechadocumento'] = trim((string)$imagent['fechadoc']);
                $imagen['origen'] = trim((string)$imagent['txtorigendoc']);
                
                $imagen['idusuarioescaneo'] = $imagent["idusuarioescaneo"];
                $imagen['fechaescaneo'] = $imagent["fechaescaneo"];
                
                
                $imagen['observaciones'] = trim((string)$imagent['observaciones']);
                $imagenes[] = $imagen;
            }
            $_SESSION['jsonsalida']['imagenes'] = $imagenes;
        }

        // **************************************************************************** //
        // 2018-08-27: JINT: Se adiciona búsqueda de certificados relacionados.
        // **************************************************************************** //
        $certs = retornarRegistrosMysqliApi($mysqli, 'mreg_certificados_virtuales', "recibo='" . $_SESSION["jsonsalida"]["recibo"] . "'", "id");
        if ($certs && !empty($certs)) {
            foreach ($certs as $cx) {
                $cert1 = array();
                $cert1["codigoverificacion"] = $cx["id"];
                $cert1["tipocertificado"] = $cx["tipocertificado"];
                $cert1["path"] = TIPO_HTTP . HTTP_HOST . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/' . $cx["path"];
                $_SESSION["jsonsalida"]["certificados"][] = $cert1;
            }
        }
        unset($certs);

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

<?php

namespace libreriaswsRestSII;

use libreriaswsRestSII\API;

trait solicitarCertificado {

    public function solicitarCertificado(API $api) {

        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistralesCalculos.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');

        $nameLog = 'solicitarCertificadosApi_' . date("Ymd");

        // array de respuesta
        $_SESSION["jsonsalida"] = array();
        $_SESSION["jsonsalida"]["codigoerror"] = '0000';
        $_SESSION["jsonsalida"]["mensajeerror"] = '';
        $_SESSION['jsonsalida']['expediente'] = '';
        $_SESSION['jsonsalida']['organizacion'] = '';

        // Verifica que  método de recepcion de parámetros sea POST
        if ($api->get_request_method() != "POST") {
            $_SESSION["jsonsalida"]["codigoerror"] = "9900";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'La petición debe ser POST';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        $api->validarParametro("usuariows", true);
        $api->validarParametro("token", true);
        $api->validarParametro("expediente", true);
        $api->validarParametro("tipocertificado", true);
        $api->validarParametro("tipogasto", true);
        $api->validarParametro("tiposalida", true);
        $api->validarParametro("modelocertificado", false);
        $api->validarParametro("ambiente", false);

        $_SESSION["generales"]["codigousuario"] = 'API';

        if (trim($_SESSION["entrada"]["expediente"]) == '') {
            $_SESSION["jsonsalida"]["codigoerror"] = "9901";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'No se indico el expediente a certificar';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        // ********************************************************************** //
        // Valida que el usuario reportado tenga acceso a la BD y al metodo
        // ********************************************************************** // 
        if (!$api->validarToken('solicitarCertificado', $_SESSION["entrada"]["token"], $_SESSION["entrada"]["usuariows"])) {
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        // valida tipo de gasto
        if (trim($_SESSION["entrada"]["tipogasto"]) != '9') {
            $_SESSION["jsonsalida"]["codigoerror"] = "9902";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Debe indicar 9 en el tipo de gasto';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        // valida tipo de certificado
        if (trim($_SESSION["entrada"]["tipocertificado"]) == '') {
            $_SESSION["jsonsalida"]["codigoerror"] = "9903";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'No se indico el tipo de certificado a certificar';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        // valida tipo de certificado
        if (!isset($_SESSION["entrada"]["modelocertificado"]) || $_SESSION["entrada"]["modelocertificado"] == '') {
            $_SESSION["entrada"]["modelocertificado"] = 'anterior';
        }
        if (trim($_SESSION["entrada"]["modelocertificado"]) != 'anterior' && trim($_SESSION["entrada"]["modelocertificado"]) != 'nuevo') {
            $_SESSION["jsonsalida"]["codigoerror"] = "9903";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'No se indico el modelo del certificado a certificar';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        //
        $mysqli = false;
        if (!isset($_SESSION["entrada"]["ambiente"]) || $_SESSION["entrada"]["ambiente"] == '' || $_SESSION["entrada"]["ambiente"] == 'A') {
            $mysqli = conexionMysqliApi();
        }
        if (isset($_SESSION["entrada"]["ambiente"]) && $_SESSION["entrada"]["ambiente"] == 'D') {
            $mysqli = conexionMysqliApi('D-' . $_SESSION["generales"]["codigoempresa"]);
        }
        if (isset($_SESSION["entrada"]["ambiente"]) && $_SESSION["entrada"]["ambiente"] == 'P') {
            $mysqli = conexionMysqliApi('P-' . $_SESSION["generales"]["codigoempresa"]);
        }
        if ($mysqli === false) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9904";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Error de conexion a la BD';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }
        

        if (trim($_SESSION["entrada"]["tipocertificado"]) == 'CerPro') {
            $arrExp = \funcionesRegistrales::retornarExpedienteProponente($mysqli, $_SESSION["entrada"]["expediente"], '', '', '', '', 'no', 'si');
            if ($arrExp === false || empty($arrExp)) {
                $mysqli->close();
                $_SESSION["jsonsalida"]["codigoerror"] = "9905";
                $_SESSION["jsonsalida"]["mensajeerror"] = 'Proponente no encontrado en el sistema de registro';
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
            }
            if ($arrExp["idestadoproponente"] != '00' && $arrExp["idestadoproponente"] != '02') {
                if ($_SESSION["entrada"]["tipogasto"] != '9') {
                    $mysqli->close();
                    $_SESSION["jsonsalida"]["codigoerror"] = "9906";
                    $_SESSION["jsonsalida"]["mensajeerror"] = 'Proponente no se encuentra activo, no es certificable';
                    $api->response($api->json($_SESSION["jsonsalida"]), 200);
                }
            }
            require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/genPdfsCertificadosProponentes.php');
            $namex = generarCertificadosPdfProponentes($mysqli, $arrExp, 'Api-Consulta');
            if (trim($namex) == '') {
                $mysqli->close();
                $_SESSION["jsonsalida"]["codigoerror"] = "9907";
                $_SESSION["jsonsalida"]["mensajeerror"] = 'Error en la generación del tipo de certificado solicitado';
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
            } else {
                if (trim($_SESSION["entrada"]["tiposalida"]) == '1') {
                    $_SESSION['jsonsalida']['certificadobase64'] = base64_encode(file_get_contents(TIPO_HTTP . HTTP_HOST . "/" . $namex));
                    \logApi::general2($nameLog, '', 'genero certificado : ' . TIPO_HTTP . HTTP_HOST . "/" . $namex);
                }
                if (trim($_SESSION["entrada"]["tiposalida"]) == '2') {
                    $_SESSION['jsonsalida']['certificadolink'] = TIPO_HTTP . HTTP_HOST . "/" . $namex;
                    \logApi::general2($nameLog, '', 'genero certificado : ' . $_SESSION['jsonsalida']['certificadolink']);
                }
                $_SESSION['jsonsalida']['expediente'] = $arrExp['proponente'];
                $_SESSION['jsonsalida']['organizacion'] = $arrExp['organizacion'];
            }
        }

        //

        if (trim($_SESSION["entrada"]["tipocertificado"]) != 'CerPro') {
            $arrExp = \funcionesRegistrales::retornarExpedienteMercantil($mysqli, $_SESSION["entrada"]["expediente"], '', '', '', 'N');

            //
            if ($arrExp === false || $arrExp == 0) {
                $mysqli->close();
                $_SESSION["jsonsalida"]["codigoerror"] = "9908";
                $_SESSION["jsonsalida"]["mensajeerror"] = 'Matricula no encontrada en el sistema de regisro';
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
            }

            //
            if (trim($_SESSION["entrada"]["tipocertificado"]) == 'CerMat') {
                if ($arrExp["organizacion"] == '12' || $arrExp["organizacion"] == '14') {
                    if ($arrExp["categoria"] == '1') {
                        $mysqli->close();
                        $_SESSION["jsonsalida"]["codigoerror"] = "9909";
                        $_SESSION["jsonsalida"]["mensajeerror"] = 'Para este expediente (' . $_SESSION["entrada"]["expediente"] . ') no se debe generar certificado de matricula mercantil';
                        $api->response($api->json($_SESSION["jsonsalida"]), 200);
                    }
                }
            }

        
            //            
            if (trim($_SESSION["entrada"]["tipocertificado"]) == 'CerExi' && $arrExp["categoria"] == '3') {
                $mysqli->close();
                $_SESSION["jsonsalida"]["codigoerror"] = "9909";
                $_SESSION["jsonsalida"]["mensajeerror"] = 'Para este expediente (' . $_SESSION["entrada"]["expediente"] . ') no se debe generar certificado de existencia';
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
            }

            //
            if (trim($_SESSION["entrada"]["tipocertificado"]) == 'CerExi') {
                if ($arrExp["organizacion"] == '01' || $arrExp["organizacion"] == '02') {
                    $mysqli->close();
                    $_SESSION["jsonsalida"]["codigoerror"] = "9911";
                    $_SESSION["jsonsalida"]["mensajeerror"] = 'Para este expediente (' . $_SESSION["entrada"]["expediente"] . ') no se debe generar certificado de existencia';
                    $api->response($api->json($_SESSION["jsonsalida"]), 200);
                }
            }

            //
            if (trim($_SESSION["entrada"]["tipocertificado"]) == 'CerExi') {
                if ($arrExp["organizacion"] > '02') {
                    if ($arrExp["organizacion"] == '12' || $arrExp["organizacion"] == '14') {
                        if ($arrExp["categoria"] == '1') {
                            $mysqli->close();
                            $_SESSION["jsonsalida"]["codigoerror"] = "9911";
                            $_SESSION["jsonsalida"]["mensajeerror"] = 'Para este expediente (' . $_SESSION["entrada"]["expediente"] . ') no se debe generar certificado de existencia';
                            $api->response($api->json($_SESSION["jsonsalida"]), 200);
                        }
                        if ($arrExp["categoria"] == '3') {
                            $mysqli->close();
                            $_SESSION["jsonsalida"]["codigoerror"] = "9911";
                            $_SESSION["jsonsalida"]["mensajeerror"] = 'Para este expediente (' . $_SESSION["entrada"]["expediente"] . ') no se debe generar certificado de existencia';
                            $api->response($api->json($_SESSION["jsonsalida"]), 200);
                        }
                    }
                }
            }

            //
            if (trim($_SESSION["entrada"]["tipocertificado"]) == 'CerEsadl') {
                if ($arrExp["organizacion"] != '12' && $arrExp["organizacion"] != '14') {
                    $mysqli->close();
                    $_SESSION["jsonsalida"]["codigoerror"] = "9913";
                    $_SESSION["jsonsalida"]["mensajeerror"] = 'Para este expediente (' . $_SESSION["entrada"]["expediente"] . ') no se debe generar certificado de ESADL';
                    $api->response($api->json($_SESSION["jsonsalida"]), 200);
                } else {
                    if ($arrExp["categoria"] != '1') {
                        $mysqli->close();
                        $_SESSION["jsonsalida"]["codigoerror"] = "9914";
                        $_SESSION["jsonsalida"]["mensajeerror"] = 'Para este expediente (' . $_SESSION["entrada"]["expediente"] . ') no se debe generar certificado de ESADL';
                        $api->response($api->json($_SESSION["jsonsalida"]), 200);
                    }
                }
            }

            //
            if (trim($_SESSION["entrada"]["tipocertificado"]) == 'CerMat') {
                if ($_SESSION["entrada"]["modelocertificado"] == 'nuevo') {
                    require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/genPdfsCertificadosFormato2019.php');
                    $namex = generarCertificadosPdfMatriculaFormato2019($mysqli, $arrExp, 'Api-Consulta');
                    \logApi::general2($nameLog, '', 'genero certificado formato nuevo : ' . $namex);
                } else {
                    require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/genPdfsCertificadosNuevos.php');
                    $namex = generarCertificadosPdfMatricula($mysqli, $arrExp, 'Api-Consulta');
                    \logApi::general2($nameLog, '', 'genero certificado formato antiguo : ' . $namex);
                }
            }

            //
            if (trim($_SESSION["entrada"]["tipocertificado"]) == 'CerExi') {
                if ($_SESSION["entrada"]["modelocertificado"] == 'nuevo') {
                    if ($arrExp["estadomatricula"] == 'MC') {
                        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/genPdfsCertificadosNuevos.php');
                        $namex = generarCertificadosPdfExistencia($mysqli, $arrExp, 'Api-Consulta');
                        \logApi::general2($nameLog, '', 'genero certificado formato antiguo : ' . $namex);
                    } else {
                        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/genPdfsCertificadosFormato2019.php');
                        $namex = generarCertificadosPdfExistenciaFormato2019($mysqli, $arrExp, 'Api-Consulta');
                        \logApi::general2($nameLog, '', 'genero certificado formato nuevo : ' . $namex);
                    }
                } else {
                    require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/genPdfsCertificadosNuevos.php');
                    $namex = generarCertificadosPdfExistencia($mysqli, $arrExp, 'Api-Consulta');
                    \logApi::general2($nameLog, '', 'genero certificado formato antiguo : ' . $namex);
                }
            }

            //
            if (trim($_SESSION["entrada"]["tipocertificado"]) == 'CerEsadl') {
                if ($_SESSION["entrada"]["modelocertificado"] == 'nuevo') {
                    if ($arrExp["estadomatricula"] == 'IC') {
                        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/genPdfsCertificadosNuevos.php');
                        $namex = generarCertificadosPdfEsadl($mysqli, $arrExp, 'Api-Consulta');
                        \logApi::general2($nameLog, '', 'genero certificado formato antiguo : ' . $namex);
                    } else {
                        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/genPdfsCertificadosFormato2019.php');
                        $namex = generarCertificadosPdfEsadlFormato2019($mysqli, $arrExp, 'Api-Consulta');
                        \logApi::general2($nameLog, '', 'genero certificado formato nuevo : ' . $namex);
                    }
                } else {
                    require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/genPdfsCertificadosNuevos.php');
                    $namex = generarCertificadosPdfEsadl($mysqli, $arrExp, 'Api-Consulta');
                    \logApi::general2($nameLog, '', 'genero certificado formato antiguo : ' . $namex);
                }
            }

            //
            if (trim($_SESSION["entrada"]["tipocertificado"]) == 'CerLibEsadl') {
                if ($arrExp["organizacion"] != '12' && $arrExp["organizacion"] != '14') {
                    $_SESSION["entrada"]["tipocertificado"] = 'CerLibRegMer';
                }
            }

            //
            if (trim($_SESSION["entrada"]["tipocertificado"]) == 'CerLibRegMer') {
                require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/genPdfsCertificadosFormato2019.php');
                $namex = generarCertificadosPdfLibrosFormato2019($mysqli, $arrExp, 'Api-Consulta');
                \logApi::general2($nameLog, '', 'genero certificado  : ' . $namex);
            }

            if (trim($_SESSION["entrada"]["tipocertificado"]) == 'CerLibEsadl') {
                require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/genPdfsCertificadosFormato2019.php');
                $namex = generarCertificadosPdfLibrosFormato2019($mysqli, $arrExp, 'Api-Consulta');
                \logApi::general2($nameLog, '', 'genero certificado  : ' . $namex);
            }


            if (trim($namex) == '') {
                $mysqli->close();
                $_SESSION["jsonsalida"]["codigoerror"] = "9915";
                $_SESSION["jsonsalida"]["mensajeerror"] = 'Error en la generación del tipo de certificado solicitado';
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
            } else {
                if (trim($_SESSION["entrada"]["tiposalida"]) == '1') {
                    $_SESSION['jsonsalida']['certificadobase64'] = base64_encode(file_get_contents(TIPO_HTTP . HTTP_HOST . '/' . $namex));
                }
                if (trim($_SESSION["entrada"]["tiposalida"]) == '2') {
                    $_SESSION['jsonsalida']['certificadolink'] = TIPO_HTTP . HTTP_HOST . "/" . $namex;
                }

                $_SESSION['jsonsalida']['expediente'] = $arrExp['proponente'];
                $_SESSION['jsonsalida']['organizacion'] = $arrExp['organizacion'];
            }

            //
        }

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

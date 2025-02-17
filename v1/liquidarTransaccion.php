<?php

namespace libreriaswsRestSII;

use libreriaswsRestSII\API;

trait liquidarTransaccion {

    public function liquidarTransaccion(API $api) {
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/myErrorHandler.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistralesEspeciales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistralesCalculos.php');
        $resError = set_error_handler('myErrorHandler');

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
        
        if (!defined('CLAVE_ENCRIPTACION') || CLAVE_ENCRIPTACION == '') {
            $claveEncriptacion = 'c0nf3c@m@r@s2017';
        }

        // array de respuesta
        $_SESSION["jsonsalida"] = array();
        $_SESSION["jsonsalida"]["codigoerror"] = '0000';
        $_SESSION["jsonsalida"]["mensajeerror"] = '';

        // Verifica método de recepcion de parámetros
        if ($api->get_request_method() != "POST" && $api->get_request_method() != "GET") {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'La petición debe ser POST/GET';
        }

        //
        $api->validarParametro("usuariows", true);
        $api->validarParametro("token", true);
        $api->validarParametro("idusuario", true);
        $api->validarParametro("emailcontrol", true);
        $api->validarParametro("identificacioncontrol", true);
        $api->validarParametro("nombrecontrol", true);
        $api->validarParametro("celularcontrol", true);
        $api->validarParametro("idtransaccion", false);

        //
        if (!$api->validarToken('liquidarTransaccion', $_SESSION["entrada"]["token"], $_SESSION["entrada"]["usuariows"])) {
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        // ********************************************************************** //
        // Crea la conexión con la BD
        // ********************************************************************** //
        $mysqli = conexionMysqliApi();
        if ($mysqli === false) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Error de conexión a BD';
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
        // Valida exitencia de la transacción reportada
        // ************************************************************************ //   
        $_SESSION["entrada"]["idtransaccion"] = sprintf("%06s", $_SESSION["entrada"]["idtransaccion"]);
        $tra = retornarRegistroMysqliApi($mysqli, 'mreg_transacciones', "idcampo='" . $_SESSION["entrada"]["idtransaccion"] . "'");
        if ($tra === false || empty($tra)) {
            $mysqli->close();
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Transacicón ' . $_SESSION["entrada"]["idtransaccion"] . ' no localizada en el sistema de información';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        // ************************************************************************ //
        // valida obligatoriedad de la matrícula
        // ************************************************************************ // 
        if (!isset($_SESSION["entrada"]["matricula"]) || $_SESSION["entrada"]["matricula"] == '') {
            if ($tra["exige_matriculaafectada"] == 'S') {
                $mysqli->close();
                $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                $_SESSION["jsonsalida"]["mensajeerror"] = 'Para la transacción ' . $_SESSION["entrada"]["idtransaccion"] . ' debe indicarse la matrícula afectada.';
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
            }
        }

        // ************************************************************************ //
        // valida obligatoriedad de la matrícula
        // ************************************************************************ // 
        if (isset($_SESSION["entrada"]["matricula"]) && $_SESSION["entrada"]["matricula"] == 'GENERICA') {
            if ($tra["exige_matriculaafectada"] != 'S') {
                $mysqli->close();
                $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                $_SESSION["jsonsalida"]["mensajeerror"] = 'Para la transacción ' . $_SESSION["entrada"]["idtransaccion"] . ' no debe indicarse la matrícula afectada.';
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
            }
            if (!isset($_SESSION["entrada"]["organizacion"]) || $_SESSION["entrada"]["organizacion"] == '') {
                $mysqli->close();
                $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                $_SESSION["jsonsalida"]["mensajeerror"] = 'Para la transacción ' . $_SESSION["entrada"]["idtransaccion"] . ' con matrícula ' . $_SESSION["entrada"]["matricula"] . ', debe indicarse la organización jurídica.';
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
            }
            if (!isset($_SESSION["entrada"]["categoria"]) || $_SESSION["entrada"]["categoria"] == '') {
                $mysqli->close();
                $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                $_SESSION["jsonsalida"]["mensajeerror"] = 'Para la transacción ' . $_SESSION["entrada"]["idtransaccion"] . ' con matrícula ' . $_SESSION["entrada"]["matricula"] . ', debe indicarse la categoría.';
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
            }
        }

        // ************************************************************************ //
        // valida obligatoriedad de los activos
        // ************************************************************************ // 
        if (!isset($_SESSION["entrada"]["activos"]) || $_SESSION["entrada"]["activos"] == '') {
            if ($tra["solicitar_activos"] == 'S') {
                $mysqli->close();
                $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                $_SESSION["jsonsalida"]["mensajeerror"] = 'Para la transacción ' . $_SESSION["entrada"]["idtransaccion"] . ' debe indicarse el valor de los activos. Se acepta 0.';
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
            }
        }

        // ************************************************************************ //
        // valida obligatoriedad de ingresos
        // ************************************************************************ // 
        if (!isset($_SESSION["entrada"]["ingresos"]) || $_SESSION["entrada"]["ingresos"] == '') {
            if ($tra["solicitar_ingresos"] == 'S') {
                $mysqli->close();
                $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                $_SESSION["jsonsalida"]["mensajeerror"] = 'Para la transacción ' . $_SESSION["entrada"]["idtransaccion"] . ' debe indicarse el valor de los ingresos. Se acepta 0.';
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
            }
        }

        // ************************************************************************ //
        // valida obligatoriedad de ciiu
        // ************************************************************************ // 
        if (!isset($_SESSION["entrada"]["ciiu"]) || $_SESSION["entrada"]["ciiu"] == '') {
            if ($tra["solicitar_ciiu"] == 'S') {
                $mysqli->close();
                $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                $_SESSION["jsonsalida"]["mensajeerror"] = 'Para la transacción ' . $_SESSION["entrada"]["idtransaccion"] . ' debe indicarse el ciiu.';
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
            }
        }

        // ************************************************************************ //
        // valida obligatoriedad de costo transaccion
        // ************************************************************************ // 
        if (!isset($_SESSION["entrada"]["costotransaccion"]) || $_SESSION["entrada"]["costotransaccion"] == '') {
            if ($tra["solicitar_costotransaccion"] == 'S') {
                $mysqli->close();
                $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                $_SESSION["jsonsalida"]["mensajeerror"] = 'Para la transacción ' . $_SESSION["entrada"]["idtransaccion"] . ' debe indicarse el valor del costo de la transacción. Se acepta 0.';
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
            }
        }

        // ************************************************************************ //
        // valida obligatoriedad de patrimonio
        // ************************************************************************ // 
        if (!isset($_SESSION["entrada"]["patrimonio"]) || $_SESSION["entrada"]["patrimonio"] == '') {
            if ($tra["solicitar_patrimonio"] == 'S') {
                $mysqli->close();
                $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                $_SESSION["jsonsalida"]["mensajeerror"] = 'Para la transacción ' . $_SESSION["entrada"]["idtransaccion"] . ' debe indicarse el valor del patrimonio. Se acepta 0.';
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
            }
        }

        // ************************************************************************ //
        // valida obligatoriedad de capital social
        // ************************************************************************ // 
        if (!isset($_SESSION["entrada"]["capitalsocial"]) || $_SESSION["entrada"]["capitalsocial"] == '') {
            if ($tra["solicitar_datosreformacapitalsocial"] == 'S') {
                $mysqli->close();
                $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                $_SESSION["jsonsalida"]["mensajeerror"] = 'Para la transacción ' . $_SESSION["entrada"]["idtransaccion"] . ' debe indicarse el valor del capital social. Se acepta 0.';
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
            }
        }

        // ************************************************************************ //
        // valida obligatoriedad de capital autorizado
        // ************************************************************************ // 
        if (!isset($_SESSION["entrada"]["capitalautorizado"]) || $_SESSION["entrada"]["capitalautorizado"] == '') {
            if ($tra["solicitar_datosreformacapitalautorizado"] == 'S') {
                $mysqli->close();
                $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                $_SESSION["jsonsalida"]["mensajeerror"] = 'Para la transacción ' . $_SESSION["entrada"]["idtransaccion"] . ' debe indicarse el valor del capital autorizado. Se acepta 0.';
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
            }
        }

        // ************************************************************************ //
        // valida obligatoriedad de capital suscrito
        // ************************************************************************ // 
        if (!isset($_SESSION["entrada"]["capitalsuscrito"]) || $_SESSION["entrada"]["capitalsuscrito"] == '') {
            if ($tra["solicitar_datosreformacapitalsuscrito"] == 'S') {
                $mysqli->close();
                $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                $_SESSION["jsonsalida"]["mensajeerror"] = 'Para la transacción ' . $_SESSION["entrada"]["idtransaccion"] . ' debe indicarse el valor del capital suscrito. Se acepta 0.';
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
            }
        }

        // ************************************************************************ //
        // valida obligatoriedad de capital pagado
        // ************************************************************************ // 
        if (!isset($_SESSION["entrada"]["capitalpagado"]) || $_SESSION["entrada"]["capitalpagado"] == '') {
            if ($tra["solicitar_datosreformacapitalpagado"] == 'S') {
                $mysqli->close();
                $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                $_SESSION["jsonsalida"]["mensajeerror"] = 'Para la transacción ' . $_SESSION["entrada"]["idtransaccion"] . ' debe indicarse el valor del capital pagado. Se acepta 0.';
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
            }
        }

        // ************************************************************************ //
        // valida obligatoriedad de capital asociativas
        // ************************************************************************ // 
        if (!isset($_SESSION["entrada"]["aporteactivos"]) || $_SESSION["entrada"]["aporteactivos"] == '' ||
                !isset($_SESSION["entrada"]["aportedinero"]) || $_SESSION["entrada"]["aportedinero"] == '' ||
                !isset($_SESSION["entrada"]["aportelaboral"]) || $_SESSION["entrada"]["aportelaboral"] == '' ||
                !isset($_SESSION["entrada"]["aportelaboraladicional"]) || $_SESSION["entrada"]["aportelaboraladicional"] == '') {
            if ($tra["solicitar_datosreformacapitalasociativas"] == 'S') {
                $mysqli->close();
                $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                $_SESSION["jsonsalida"]["mensajeerror"] = 'Para la transacción ' . $_SESSION["entrada"]["idtransaccion"] . ' deben indicarse los valores del capital para asociativas. Se acepta 0.';
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
            }
        }

        // ************************************************************************ //
        // valida obligatoriedad de capital asignado
        // ************************************************************************ // 
        if (!isset($_SESSION["entrada"]["capitalasignado"]) || $_SESSION["entrada"]["capitalasignado"] == '') {
            if ($tra["solicitar_datosreformacapitalasignado"] == 'S') {
                $mysqli->close();
                $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                $_SESSION["jsonsalida"]["mensajeerror"] = 'Para la transacción ' . $_SESSION["entrada"]["idtransaccion"] . ' debe indicarse el valor del capital asignado. Se acepta 0.';
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
            }
        }

        // ************************************************************************ //
        // valida obligatoriedad de datos de 1780
        // ************************************************************************ // 
        if (!isset($_SESSION["entrada"]["beneficiarioley1780"]) || $_SESSION["entrada"]["beneficiarioley1780"] == '') {
            if ($tra["solicitar_datos1780"] == 'S') {
                $mysqli->close();
                $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                $_SESSION["jsonsalida"]["mensajeerror"] = 'Para la transacción ' . $_SESSION["entrada"]["idtransaccion"] . ' debe indicarse si se accede o no al beneficio de ley 1780.';
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
            }
        }

        // ************************************************************************ //
        // valida obligatoriedad fecha de documento
        // ************************************************************************ // 
        if (!isset($_SESSION["entrada"]["fechadocumento"]) || $_SESSION["entrada"]["fechadocumento"] == '') {
                $mysqli->close();
                $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                $_SESSION["jsonsalida"]["mensajeerror"] = 'la fecha de documento es obligatoria y en formato AAAAMMDD';
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }
        
        // ************************************************************************ //
        // valida obligatoriedad del municipio del documento
        // ************************************************************************ // 
        if (!isset($_SESSION["entrada"]["municipiodocumento"]) || $_SESSION["entrada"]["municipiodocumento"] == '') {
                $mysqli->close();
                $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                $_SESSION["jsonsalida"]["mensajeerror"] = 'El código del municipio del documento es obligatorio';
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }
        
        // ************************************************************************ //
        // valida obligatoriedad tipo controlante
        // ************************************************************************ // 
        if (!isset($_SESSION["entrada"]["tipocontrolante"]) || $_SESSION["entrada"]["tipocontrolante"] == '') {
            if ($tra["solicitar_datosconstitucion"] == 'S') {
                $mysqli->close();
                $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                $_SESSION["jsonsalida"]["mensajeerror"] = 'Para la transacción ' . $_SESSION["entrada"]["idtransaccion"] . ' debe indicarse el tipo de controlante';
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
            }
        }
        
        // ********************************************************************** //
        // Inicializa variables
        // ********************************************************************** //       
        if (!isset($_SESSION["entrada"]["matricula"])) {
            $_SESSION["entrada"]["matricula"] = '';
        }
        if (!isset($_SESSION["entrada"]["tipodocumento"])) {
            $_SESSION["entrada"]["tipodocumento"] = '';
        }
        if (!isset($_SESSION["entrada"]["numerodocumento"])) {
            $_SESSION["entrada"]["numerodocumento"] = '';
        }
        if (!isset($_SESSION["entrada"]["fechadocumento"])) {
            $_SESSION["entrada"]["fechadocumento"] = '';
        }
        if (!isset($_SESSION["entrada"]["origendocumento"])) {
            $_SESSION["entrada"]["origendocumento"] = '';
        }
        if (!isset($_SESSION["entrada"]["municipiodocumento"])) {
            $_SESSION["entrada"]["municipiodocumento"] = '';
        }
        if (!isset($_SESSION["entrada"]["organizacion"])) {
            $_SESSION["entrada"]["organizacion"] = '';
        }
        if (!isset($_SESSION["entrada"]["categoria"])) {
            $_SESSION["entrada"]["categoria"] = '';
        }
        if (!isset($_SESSION["entrada"]["razonsocial"])) {
            $_SESSION["entrada"]["razonsocial"] = '';
        }
        if (!isset($_SESSION["entrada"]["tipoidentificacion"])) {
            $_SESSION["entrada"]["tipoidentificacion"] = '';
        }
        if (!isset($_SESSION["entrada"]["identificacion"])) {
            $_SESSION["entrada"]["identificacion"] = '';
        }
        if (!isset($_SESSION["entrada"]["nit"])) {
            $_SESSION["entrada"]["nit"] = '';
        }
        if (!isset($_SESSION["entrada"]["apelliodo1"])) {
            $_SESSION["entrada"]["apelliodo1"] = '';
        }
        if (!isset($_SESSION["entrada"]["apelliodo2"])) {
            $_SESSION["entrada"]["apelliodo2"] = '';
        }
        if (!isset($_SESSION["entrada"]["nombre1"])) {
            $_SESSION["entrada"]["nombre1"] = '';
        }
        if (!isset($_SESSION["entrada"]["nombre2"])) {
            $_SESSION["entrada"]["nombre2"] = '';
        }
        if (!isset($_SESSION["entrada"]["pornaltot"]) || $_SESSION["entrada"]["pornaltot"] == '') {
            $_SESSION["entrada"]["pornaltot"] = 0;
        }
        if (!isset($_SESSION["entrada"]["pornalpub"]) || $_SESSION["entrada"]["pornalpub"] == '') {
            $_SESSION["entrada"]["pornalpub"] = 0;
        }
        if (!isset($_SESSION["entrada"]["pornalpri"]) || $_SESSION["entrada"]["pornalpri"] == '') {
            $_SESSION["entrada"]["pornalpri"] = 0;
        }
        if (!isset($_SESSION["entrada"]["porexttot"]) || $_SESSION["entrada"]["porexttot"] == '') {
            $_SESSION["entrada"]["porexttot"] = 0;
        }
        if (!isset($_SESSION["entrada"]["porextpub"]) || $_SESSION["entrada"]["porextpub"] == '') {
            $_SESSION["entrada"]["porextpub"] = 0;
        }
        if (!isset($_SESSION["entrada"]["porextpri"]) || $_SESSION["entrada"]["porextpri"] == '') {
            $_SESSION["entrada"]["porextpri"] = 0;
        }
        if (!isset($_SESSION["entrada"]["activos"]) || $_SESSION["entrada"]["activos"] == '') {
            $_SESSION["entrada"]["activos"] = 0;
        }
        if (!isset($_SESSION["entrada"]["costotransaccion"]) || $_SESSION["entrada"]["costotransaccion"] == '') {
            $_SESSION["entrada"]["costotransaccion"] = 0;
        }
        if (!isset($_SESSION["entrada"]["patrimonio"]) || $_SESSION["entrada"]["patrimonio"] == '') {
            $_SESSION["entrada"]["patrimonio"] = 0;
        }
        if (!isset($_SESSION["entrada"]["beneficiarioley1780"])) {
            $_SESSION["entrada"]["beneficiarioley1780"] = '';
        }
        if (!isset($_SESSION["entrada"]["fehanacimientopnat"])) {
            $_SESSION["entrada"]["fehanacimientopnat"] = '';
        }
        if (!isset($_SESSION["entrada"]["tipocontrolante"])) {
            $_SESSION["entrada"]["tipocontrolante"] = '';
        }        
        if (!isset($_SESSION["entrada"]["capitalsocial"]) || $_SESSION["entrada"]["capitalsocial"] == '') {
            $_SESSION["entrada"]["capitalsocial"] = 0;
        }
        if (!isset($_SESSION["entrada"]["capitalautorizado"]) || $_SESSION["entrada"]["capitalautorizado"] == '') {
            $_SESSION["entrada"]["capitalautorizado"] = 0;
        }
        if (!isset($_SESSION["entrada"]["capitalsuscrito"]) || $_SESSION["entrada"]["capitalsuscrito"] == '') {
            $_SESSION["entrada"]["capitalsuscrito"] = 0;
        }
        if (!isset($_SESSION["entrada"]["capitalpagado"]) || $_SESSION["entrada"]["capitalpagado"] == '') {
            $_SESSION["entrada"]["capitalpagado"] = 0;
        }
        if (!isset($_SESSION["entrada"]["aporteactivos"]) || $_SESSION["entrada"]["aporteactivos"] == '') {
            $_SESSION["entrada"]["aporteactivos"] = 0;
        }
        if (!isset($_SESSION["entrada"]["aportedinero"]) || $_SESSION["entrada"]["aportedinero"] == '') {
            $_SESSION["entrada"]["aportedinero"] = 0;
        }
        if (!isset($_SESSION["entrada"]["aportelaboral"]) || $_SESSION["entrada"]["aportelaboral"] == '') {
            $_SESSION["entrada"]["aportelaboral"] = 0;
        }
        if (!isset($_SESSION["entrada"]["aportelaboraladicional"]) || $_SESSION["entrada"]["aportelaboraladicional"] == '') {
            $_SESSION["entrada"]["aportelaboraladicional"] = 0;
        }
        if (!isset($_SESSION["entrada"]["capitalasignado"]) || $_SESSION["entrada"]["capitalasignado"] == '') {
            $_SESSION["entrada"]["capitalasignado"] = 0;
        }
        if (!isset($_SESSION["entrada"]["acreditadopagoir"])) {
            $_SESSION["entrada"]["acreditadopagoir"] = '';
        }
        if (!isset($_SESSION["entrada"]["ingresos"]) || $_SESSION["entrada"]["ingresos"] == '') {
            $_SESSION["entrada"]["ingresos"] = 0;
        }
        if (!isset($_SESSION["entrada"]["ciiu"])) {
            $_SESSION["entrada"]["ciiu"] = '';
        }

        // ************************************************************************ //
        // valida obligatoriedad de la matrícula
        // ************************************************************************ // 
        if ($_SESSION["entrada"]["matricula"] != '') {
            if ($tra["exige_matriculaafectada"] == 'N') {
                $mysqli->close();
                $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                $_SESSION["jsonsalida"]["mensajeerror"] = 'Para la transacción ' . $_SESSION["entrada"]["idtransaccion"] . ' no debe indicarse número de matrícula';
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
            }
        } else {
            if ($tra["exige_matriculaafectada"] == 'S') {
                $mysqli->close();
                $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                $_SESSION["jsonsalida"]["mensajeerror"] = 'Para la transacción ' . $_SESSION["entrada"]["idtransaccion"] . ' debe indicarse un número de matrícula';
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
            }
        }


        // ************************************************************************ //
        // Valida que la matrícula exista y esté activa
        // ************************************************************************ // 
        if ($_SESSION["entrada"]["matricula"] != '') {
            if (substr($_SESSION["entrada"]["matricula"],0,4) == 'PNAT') {
                $expe = array();
                $expe["matricula"] = $_SESSION["entrada"]["matricula"];
                $expe["nombre"] = 'PERSONA NATURAL DE PRUEBA';
                $expe["organizacion"] = '01';
                $expe["categoria"] = '0';
                $expe["estadomatricula"] = 'MA';
                $expe["tamanoempresarial957codigo"] = '1';
                if (substr($_SESSION["entrada"]["matricula"],5) == 'PEQUENA') {
                    $expe["tamanoempresarial957codigo"] = '2';
                }
                if (substr($_SESSION["entrada"]["matricula"],5) == 'MEDIANA') {
                    $expe["tamanoempresarial957codigo"] = '3';
                }
                if (substr($_SESSION["entrada"]["matricula"],5) == 'GRAN') {
                    $expe["tamanoempresarial957codigo"] = '4';
                }                
            }
            if (substr($_SESSION["entrada"]["matricula"],0,4) == 'PJUR') {
                $expe = array();
                $expe["matricula"] = $_SESSION["entrada"]["matricula"];
                $expe["nombre"] = 'PERSONA JURIDICA DE PRUEBA';
                $expe["organizacion"] = $_SESSION["entrada"]["organizacion"];
                $expe["categoria"] = $_SESSION["entrada"]["categoria"];
                $expe["estadomatricula"] = 'MA';
                $expe["tamanoempresarial957codigo"] = '1';
                if (substr($_SESSION["entrada"]["matricula"],5) == 'PEQUENA') {
                    $expe["tamanoempresarial957codigo"] = '2';
                }
                if (substr($_SESSION["entrada"]["matricula"],5) == 'MEDIANA') {
                    $expe["tamanoempresarial957codigo"] = '3';
                }
                if (substr($_SESSION["entrada"]["matricula"],5) == 'GRAN') {
                    $expe["tamanoempresarial957codigo"] = '4';
                }                                
            }
            if (substr($_SESSION["entrada"]["matricula"],0,5) == 'ESADL') {
                $expe = array();
                $expe["matricula"] = $_SESSION["entrada"]["matricula"];
                $expe["nombre"] = 'ENTIDAD DE PRUEBA';
                $expe["organizacion"] = $_SESSION["entrada"]["organizacion"];
                $expe["categoria"] = $_SESSION["entrada"]["categoria"];
                $expe["estadomatricula"] = 'IA';
                $expe["tamanoempresarial957codigo"] = '1';
                if (substr($_SESSION["entrada"]["matricula"],6) == 'PEQUENA') {
                    $expe["tamanoempresarial957codigo"] = '2';
                }
                if (substr($_SESSION["entrada"]["matricula"],6) == 'MEDIANA') {
                    $expe["tamanoempresarial957codigo"] = '3';
                }
                if (substr($_SESSION["entrada"]["matricula"],6) == 'GRAN') {
                    $expe["tamanoempresarial957codigo"] = '4';
                }                 
            }
            if (substr($_SESSION["entrada"]["matricula"],0,4) != 'PNAT' && substr($_SESSION["entrada"]["matricula"],0,4) != 'PJUR' && substr($_SESSION["entrada"]["matricula"],0,5) != 'ESADL') {
                $expe = \funcionesRegistrales::retornarExpedienteMercantil($mysqli, $_SESSION["entrada"]["matricula"]);
                if ($expe === false || empty($expe)) {
                    $mysqli->close();
                    $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                    $_SESSION["jsonsalida"]["mensajeerror"] = 'La matrícula ' . $_SESSION["entrada"]["matricula"] . ' no localizada en el sistema de información';
                    $api->response($api->json($_SESSION["jsonsalida"]), 200);
                } else {
                    if ($expe["estadomatricula"] != 'MA' &&
                            $expe["estadomatricula"] != 'IA' &&
                            $expe["estadomatricula"] != 'MI' &&
                            $expe["estadomatricula"] != 'II') {
                        $mysqli->close();
                        $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                        $_SESSION["jsonsalida"]["mensajeerror"] = 'La matrícula ' . $_SESSION["entrada"]["matricula"] . ' no se encuentra en un estado que permita la liquidación de trámites';
                        $api->response($api->json($_SESSION["jsonsalida"]), 200);
                    }
                }
            }

            if ($tra["aplica" . $expe["organizacion"]] != 'S') {
                $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                $_SESSION["jsonsalida"]["mensajeerror"] = 'La transacción reportada (' . $_SESSION["entrada"]["idtransaccion"] . ') no puede ser utilizada para la matrícula ' . $_SESSION["entrada"]["matricula"] . ' con organización ' . $expe["organizacion"];
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
            }

            if ($tra["aplicac" . $expe["categoria"]] != 'S') {
                $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                $_SESSION["jsonsalida"]["mensajeerror"] = 'La transacción reportada (' . $_SESSION["entrada"]["idtransaccion"] . ') no puede ser utilizada para la matrícula ' . $_SESSION["entrada"]["matricula"] . ' con categoria ' . $expe["categoria"];
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
            }
        } else {
            $expe = false;
        }




        // ********************************************************************** //
        // Inicializa la liquidación
        // ********************************************************************** //        
        $_SESSION["tramite"] = \funcionesRegistrales::retornarMregLiquidacion($mysqli, 0, 'VC');

        //
        $_SESSION["tramite"]["idliquidacion"] = \funcionesGenerales::retornarSecuencia($mysqli, 'LIQUIDACION-REGISTROS');
        $_SESSION["tramite"]["numeroliquidacion"] = $_SESSION["tramite"]["idliquidacion"];
        $_SESSION["tramite"]["numerorecuperacion"] = \funcionesGenerales::asignarNumeroRecuperacion($mysqli, 'mreg');
        $_SESSION["tramite"]["fecha"] = date("Ymd");
        $_SESSION["tramite"]["hora"] = date("His");
        $_SESSION["tramite"]["idusuario"] = 'USUPUBXX';
        $_SESSION["tramite"]["sede"] = '99';
        $_SESSION["tramite"]["tramitepresencial"] = '1';

        $_SESSION["tramite"]["tipotramite"] = 'inscripciondocumentos';
        $_SESSION["tramite"]["subtipotramite"] = $tra["tipotramite"];
        $_SESSION["tramite"]["tipogasto"] = '0';
        $_SESSION["tramite"]["origen"] = '';
        $_SESSION["tramite"]["iptramite"] = \funcionesGenerales::localizarIP();
        $_SESSION["tramite"]["idestado"] = '01';
        $_SESSION["tramite"]["txtestado"] = 'PENDIENTE';
        $_SESSION["tramite"]["idexpedientebase"] = $_SESSION["entrada"]["matricula"];
        $_SESSION["tramite"]["idmatriculabase"] = $_SESSION["entrada"]["matricula"];

        if ($expe === false) {
            $_SESSION["tramite"]["tipoidentificacionbase"] = $_SESSION["entrada"]["tipoidentificacion"];
            $_SESSION["tramite"]["identificacionbase"] = $_SESSION["entrada"]["identificacion"];
            $_SESSION["tramite"]["nombrebase"] = strtoupper($_SESSION["entrada"]["razonsocial"]);
            $_SESSION["tramite"]["nom1base"] = strtoupper($_SESSION["entrada"]["apelliodo1"]);
            $_SESSION["tramite"]["nom2base"] = strtoupper($_SESSION["entrada"]["apelliodo2"]);
            $_SESSION["tramite"]["ape1base"] = strtoupper($_SESSION["entrada"]["nombre1"]);
            $_SESSION["tramite"]["ape2base"] = strtoupper($_SESSION["entrada"]["nombre2"]);
            $_SESSION["tramite"]["organizacionbase"] = $_SESSION["entrada"]["organizacion"];
            $_SESSION["tramite"]["categoriabase"] = $_SESSION["entrada"]["categoria"];
        } else {
            $_SESSION["tramite"]["tipoidentificacionbase"] = $expe["tipoidentificacion"];
            $_SESSION["tramite"]["identificacionbase"] = $expe["identificacion"];
            $_SESSION["tramite"]["nombrebase"] = $expe["nombre"];
            $_SESSION["tramite"]["nom1base"] = $expe["nom1"];
            $_SESSION["tramite"]["nom2base"] = $expe["nom2"];
            $_SESSION["tramite"]["ape1base"] = $expe["ape1"];
            $_SESSION["tramite"]["ape2base"] = $expe["ape2"];
            $_SESSION["tramite"]["organizacionbase"] = $expe["organizacion"];
            $_SESSION["tramite"]["categoriabase"] = $expe["categoria"];
        }
        $_SESSION["tramite"]["afiliadobase"] = '';
        $_SESSION["tramite"]["matriculabase"] = $_SESSION["entrada"]["matricula"];

        $_SESSION["tramite"]["numeromatriculapnat"] = '';
        $_SESSION["tramite"]["camarapnat"] = '';
        $_SESSION["tramite"]["orgpnat"] = '';
        $_SESSION["tramite"]["tipoidepnat"] = '';
        $_SESSION["tramite"]["idepnat"] = '';
        $_SESSION["tramite"]["nombrepnat"] = '';

//
        $_SESSION["tramite"]["nombreest"] = '';
        $_SESSION["tramite"]["nombrepjur"] = '';
        $_SESSION["tramite"]["nombresuc"] = '';
        $_SESSION["tramite"]["nombreage"] = '';

        $_SESSION["tramite"]["orgpjur"] = '';
        $_SESSION["tramite"]["orgsuc"] = '';
        $_SESSION["tramite"]["orgage"] = '';

        $_SESSION["tramite"]["actpnat"] = '';
        $_SESSION["tramite"]["actpjur"] = '';
        $_SESSION["tramite"]["actest"] = '';
        $_SESSION["tramite"]["actsuc"] = '';
        $_SESSION["tramite"]["actage"] = '';

        $_SESSION["tramite"]["perpnat"] = '';
        $_SESSION["tramite"]["perpjur"] = '';

        $_SESSION["tramite"]["munpnat"] = '';
        $_SESSION["tramite"]["munest"] = '';
        $_SESSION["tramite"]["munpjur"] = '';
        $_SESSION["tramite"]["munsuc"] = '';
        $_SESSION["tramite"]["munage"] = '';

        $_SESSION["tramite"]["ultanoren"] = '';
        $_SESSION["tramite"]["domicilioorigen"] = '';
        $_SESSION["tramite"]["domiciliodestino"] = '';

        $_SESSION["tramite"]["incluirformularios"] = '';
        $_SESSION["tramite"]["incluircertificados"] = '';
        $_SESSION["tramite"]["incluirdiploma"] = '';
        $_SESSION["tramite"]["incluircartulina"] = '';
        $_SESSION["tramite"]["matricularpnat"] = '';
        $_SESSION["tramite"]["matricularest"] = '';
        $_SESSION["tramite"]["regimentributario"] = '';
        $_SESSION["tramite"]["tipomatricula"] = '';
        $_SESSION["tramite"]["camaracambidom"] = '';
        $_SESSION["tramite"]["matriculacambidom"] = '';
        $_SESSION["tramite"]["municipiocambidom"] = '';
        $_SESSION["tramite"]["fecmatcambidom"] = '';
        $_SESSION["tramite"]["fecrencambidom"] = '';
        $_SESSION["tramite"]["benart7"] = 'N';
        $_SESSION["tramite"]["benley1780"] = 'N';
        $_SESSION["tramite"]["controlfirma"] = 'N';
        $_SESSION["tramite"]["cumplorequisitosbenley1780"] = '';
        $_SESSION["tramite"]["mantengorequisitosbenley1780"] = '';
        $_SESSION["tramite"]["renunciobeneficiosley1780"] = '';
        $_SESSION["tramite"]["multadoponal"] = '';
        $_SESSION["tramite"]["controlaactividadaltoimpacto"] = '';
        $_SESSION["tramite"]["sistemacreacion"] = 'API - liquidarTransaccion - ' . $_SESSION["entrada"]["usuariows"];

        $_SESSION["tramite"]["capital"] = 0;
        $_SESSION["tramite"]["tipodoc"] = '';
        $_SESSION["tramite"]["numdoc"] = '';
        $_SESSION["tramite"]["fechadoc"] = '';
        $_SESSION["tramite"]["origendoc"] = '';
        $_SESSION["tramite"]["mundoc"] = '';
        $_SESSION["tramite"]["organizacion"] = '';
        $_SESSION["tramite"]["categoria"] = '';

        $_SESSION["tramite"]["tipolibro"] = ''; //
        $_SESSION["tramite"]["codigolibro"] = ''; //
        $_SESSION["tramite"]["primeravez"] = ''; //
        $_SESSION["tramite"]["confirmadigital"] = ''; //
        //
        $_SESSION["tramite"]["transacciones"] = array();
        $ttra = array();
        $ttra["idliquidacion"] = 0;
        $ttra["idsecuencia"] = 1;
        $ttra["idtransaccion"] = $_SESSION["entrada"]["idtransaccion"];
        $ttra["matriculaafectada"] = $_SESSION["entrada"]["matricula"];
        $ttra["tipodoc"] = $_SESSION["entrada"]["tipodocumento"];
        $ttra["numdoc"] = $_SESSION["entrada"]["numerodocumento"];
        $ttra["fechadoc"] = $_SESSION["entrada"]["fechadocumento"];
        $ttra["origendoc"] = strtoupper($_SESSION["entrada"]["origendocumento"]);
        $ttra["mundoc"] = $_SESSION["entrada"]["municipiodocumento"];
        if ($expe === false) {
            $ttra["organizacion"] = $_SESSION["entrada"]["organizacion"];
            $ttra["categoria"] = $_SESSION["entrada"]["categoria"];
            $ttra["razonsocial"] = strtoupper($_SESSION["entrada"]["razonsocial"]);
            $ttra["sigla"] = '';
            $ttra["tipoidentificacion"] = $_SESSION["entrada"]["tipoidentificacion"];
            $ttra["identificacion"] = $_SESSION["entrada"]["identificacion"];
            $ttra["nit"] = $_SESSION["entrada"]["nit"];
            $ttra["prerut"] = '';
            $ttra["ape1"] = strtoupper($_SESSION["entrada"]["apelliodo1"]);
            $ttra["ape2"] = strtoupper($_SESSION["entrada"]["apelliodo2"]);
            $ttra["nom1"] = strtoupper($_SESSION["entrada"]["nombre1"]);
            $ttra["nom2"] = strtoupper($_SESSION["entrada"]["nombre2"]);
            $ttra["pornaltot"] = doubleval($_SESSION["entrada"]["pornaltot"]);
            $ttra["pornalpub"] = doubleval($_SESSION["entrada"]["pornalpub"]);
            $ttra["pornalpri"] = doubleval($_SESSION["entrada"]["pornalpri"]);
            $ttra["porexttot"] = doubleval($_SESSION["entrada"]["porexttot"]);
            $ttra["porextpub"] = doubleval($_SESSION["entrada"]["porextpub"]);
            $ttra["porextpri"] = doubleval($_SESSION["entrada"]["porextpri"]);
        } else {
            $ttra["organizacion"] = $expe["organizacion"];
            $ttra["categoia"] = $expe["categoia"];
            $ttra["razonsocial"] = $expe["nombre"];
            $ttra["sigla"] = $expe["sigla"];
            $ttra["tipoidentificacion"] = $expe["tipoidentificacion"];
            $ttra["identificacion"] = $expe["identificacion"];
            $ttra["nit"] = $expe["nit"];
            $ttra["prerut"] = '';
            $ttra["ape1"] = $expe["ape1"];
            $ttra["ape2"] = $expe["ape2"];
            $ttra["nom1"] = $expe["nom1"];
            $ttra["nom2"] = $expe["nom2"];
            $ttra["pornaltot"] = $expe["cap_porcnaltot"];
            $ttra["pornalpub"] = $expe["cap_porcnalpub"];
            $ttra["pornalpri"] = $expe["cap_porcnalpri"];
            $ttra["porexttot"] = $expe["cap_porcexttot"];
            $ttra["porextpub"] = $expe["cap_porcextpub"];
            $ttra["porextpri"] = $expe["cap_porcextpri"];
        }
        $ttra["activos"] = doubleval($_SESSION["entrada"]["activos"]);
        $ttra["costotransaccion"] = doubleval($_SESSION["entrada"]["costotransaccion"]);
        $ttra["patrimonio"] = doubleval($_SESSION["entrada"]["patrimonio"]);
        $ttra["benley1780"] = strtoupper($_SESSION["entrada"]["beneficiarioley1780"]);
        $ttra["fehanacimientopnat"] = $_SESSION["entrada"]["fehanacimientopnat"];
        $ttra["capitalsocial"] = doubleval($_SESSION["entrada"]["capitalsocial"]);
        $ttra["capitalautorizado"] = doubleval($_SESSION["entrada"]["capitalautorizado"]);
        $ttra["capitalsuscrito"] = doubleval($_SESSION["entrada"]["capitalsuscrito"]);
        $ttra["capitalpagado"] = doubleval($_SESSION["entrada"]["capitalpagado"]);
        $ttra["aporteactivos"] = doubleval($_SESSION["entrada"]["aporteactivos"]);
        $ttra["aportedinero"] = doubleval($_SESSION["entrada"]["aportedinero"]);
        $ttra["aportelaboral"] = doubleval($_SESSION["entrada"]["aportelaboral"]);
        $ttra["aportelaboraladicional"] = doubleval($_SESSION["entrada"]["aportelaboraladicional"]);
        $ttra["capitalasignado"] = doubleval($_SESSION["entrada"]["capitalasignado"]);
        $ttra["acreditapagoir"] = strtoupper($_SESSION["entrada"]["acreditapagoir"]);
        $ttra["tipocontrolante"] = strtoupper($_SESSION["entrada"]["tipocontrolante"]);
        $ttra["nroreciboacreditapagoir"] = '';
        $ttra["fechareciboacreditapagoir"] = '';
        $ttra["gobernacionacreditapagoir"] = '';
        $ttra["dircom"] = '';
        $ttra["municipio"] = '';
        $ttra["fechaduracion"] = '';
        $ttra["tipodisolucion"] = '';
        $ttra["motivodisolucion"] = '';
        $ttra["tipoliquidacion"] = '';
        $ttra["motivoliquidacion"] = '';
        $ttra["ciiu1"] = '';
        $ttra["ciiu2"] = '';
        $ttra["ciiu3"] = '';
        $ttra["ciiu4"] = '';
        $ttra["clase_libro"] = '';
        $ttra["tipo_libro"] = '';
        $ttra["codigo_libro"] = '';
        $ttra["nombre_libro"] = '';
        $ttra["email_libro"] = '';
        $ttra["emailconfirmacion_libro"] = '';
        $ttra["paginainicial_libro"] = 0;
        $ttra["paginafinal_libro"] = 0;
        $ttra["incluirrotulado_libro"] = '';
        $ttra["incluir_costo_hojas"] = '';
        $ttra["incluir_costo_envio"] = '';

        $ttra["libeleanot_libro"] = '';
        $ttra["libeleanot_registro"] = '';
        $ttra["libeleanot_dupli"] = '';
        $ttra["libeleanot_nroacta"] = '';
        $ttra["libeleanot_fechaacta"] = '';
        $ttra["libeleanot_nropaginas"] = 0;
        $ttra["libeleanot_fechainiinscripciones"] = '';
        $ttra["libeleanot_fechafininscripciones"] = '';
        $ttra["libeleanot_nroregistros"] = 0;

        $ttra["actanro_libro"] = '';
        $ttra["fechaacta_libro"] = '';
        $ttra["horaacta_libro"] = '';
        $ttra["fechaini_libroelectronico"] = '';
        $ttra["fechafin_libroelectronico"] = '';
        $ttra["fechainiinscripciones_libroelectronico"] = '';
        $ttra["fechafininscripciones_libroelectronico"] = '';
        $ttra["embargo"] = '';
        $ttra["tipoideembargante"] = '';
        $ttra["ideembargante"] = '';
        $ttra["nom1embargante"] = '';
        $ttra["nom2embargante"] = '';
        $ttra["ape1embargante"] = '';
        $ttra["ape2embargante"] = '';
        $ttra["desembargo"] = '';
        $ttra["librocruce"] = '';
        $ttra["inscripcioncruce"] = '';
        $ttra["entidadvigilancia"] = '';
        $ttra["objetosocial"] = '';
        $ttra["facultades"] = '';
        $ttra["limitaciones"] = '';
        $ttra["poderespecial"] = '';
        $ttra["texto"] = '';
        $ttra["cantidad"] = 0;

        $ttra["tipocontrolante"] = '';
        $ttra["tipocontrolantemotivo"] = '';
        $ttra["tipoidentificacioncontrolante"] = '';
        $ttra["identificacioncontrolante"] = '';
        $ttra["nombrecontrolante"] = '';
        $ttra["tipoidentificacionsocio"] = '';
        $ttra["identificacionsocio"] = '';
        $ttra["nombresocio"] = '';
        $ttra["direccionnotificacionsocio"] = '';
        $ttra["domiciliosocio"] = '';
        $ttra["nacionalidadsocio"] = '';
        $ttra["actividadsocio"] = '';

        $ttra["ingresos"] = doubleval($_SESSION["entrada"]["ingresos"]);
        $ttra["ciiu"] = strtoupper($_SESSION["entrada"]["ciiu"]);

        $ttra["cantidadcermat"] = 0;
        $ttra["cantidadcerexi"] = 0;
        $ttra["cantidadcerlib"] = 0;

        //
        $_SESSION["tramite"]["transacciones"][] = $ttra;

        //
        \funcionesRegistrales::rutinaLiquidacionTransacciones($mysqli,'','',$expe);
        \funcionesRegistrales::grabarLiquidacionMreg($mysqli);
        \funcionesRegistralesEspeciales::calcularTarifaEspecial2021($mysqli, $_SESSION["tramite"]["idliquidacion"]);
        $_SESSION["tramite"] = \funcionesRegistrales::retornarMregLiquidacion($mysqli, $_SESSION["tramite"]["idliquidacion"]);

        //
        $_SESSION["jsonsalida"]["codigoerror"] = "0000";
        $_SESSION["jsonsalida"]["mensajeerror"] = '';
        $_SESSION["jsonsalida"]["idusuario"] = $_SESSION["entrada"]["idusuario"];
        $_SESSION["jsonsalida"]["identificacioncontrol"] = $_SESSION["entrada"]["identificacioncontrol"];
        $_SESSION["jsonsalida"]["nombrecontrol"] = $_SESSION["entrada"]["nombrecontrol"];
        $_SESSION["jsonsalida"]["emailcontrol"] = $_SESSION["entrada"]["emailcontrol"];
        $_SESSION["jsonsalida"]["celularcontrol"] = $_SESSION["entrada"]["celularcontrol"];
        $_SESSION["jsonsalida"]["idliquidacion"] = $_SESSION["tramite"]["idliquidacion"];
        $_SESSION["jsonsalida"]["numerorecuperacion"] = $_SESSION["tramite"]["numerorecuperacion"];
        $_SESSION["jsonsalida"]["valorbruto"] = $_SESSION["tramite"]["valorbruto"];
        $_SESSION["jsonsalida"]["valorbaseiva"] = $_SESSION["tramite"]["valorbaseiva"];
        $_SESSION["jsonsalida"]["valoriva"] = $_SESSION["tramite"]["valoriva"];
        $_SESSION["jsonsalida"]["valortotal"] = $_SESSION["tramite"]["valortotal"];
        $_SESSION["jsonsalida"]["liquidacion"] = array();
        foreach ($_SESSION["tramite"]["liquidacion"] as $xliq) {
            $dliq = array();
            $dliq["servicio"] = $xliq["idservicio"];
            $dliq["nservicio"] = retornarRegistroMysqliApi($mysqli, 'mreg_servicios', "idservicio='" . $xliq["idservicio"] . "'", "nombre");
            $dliq["matricula"] = $_SESSION["entrada"]["matricula"];
            $dliq["nombre"] = $_SESSION["tramite"]["nombrebase"];
            $dliq["cantidad"] = $xliq["cantidad"];
            $dliq["baseliquidacion"] = $xliq["valorbase"];
            $dliq["porcentaje"] = $xliq["porcentaje"];
            $dliq["valor"] = $xliq["valorservicio"];
            $_SESSION["jsonsalida"]["liquidacion"][] = $dliq;
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

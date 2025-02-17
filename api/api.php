<?php

namespace api;

session_start();
set_time_limit(0);
// ini_set('memory_limit','1024M');
error_reporting(E_ALL);
ini_set('display_errors', '1');
$_SESSION["generales"]["zonahoraria"] = "America/Bogota";
$_SESSION["generales"]["idioma"] = "es";
date_default_timezone_set($_SESSION["generales"]["zonahoraria"]);
$_SESSION["generales"]["pathabsoluto"] = getcwd();
$_SESSION["generales"]["pathabsoluto"] = str_replace("\\", "/", $_SESSION["generales"]["pathabsoluto"]);
$_SESSION["generales"]["pathabsoluto"] = str_replace(array("/scripts/", "/scripts", "/api/", "/api"), "", $_SESSION["generales"]["pathabsoluto"]);
require_once($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common.php');
$_SESSION["generales"]["pathabsolutositio"] = PATH_ABSOLUTO_SITIO;
$_SESSION["generales"]["pathabsoluto"] = PATH_ABSOLUTO_SITIO;
//

/*
 * Archivos del CORE del API 
 */
require("Rest.inc.php");
require("funciones.php");
include("apiValidaciones.php");
include("devolverTramite.php");
include("grabaFormularioMercantil.php");
include("mantenimientoServicios.php");
include("mregActosDocumentos.php");
include("mregActualizaciones.php");
include("mregMantenimientos.php");
include("mregPagoElectronico.php");
include("mregRadicarDocumentos.php");
include("mregRecibirPagos.php");
include("mregReingresarGenerico.php");
include("mregSolicitudDeposito.php");
include("mregTramitesRues.php");
include("mregValidaciones.php");
include("mregVotaciones.php");

use api\API;
use api\REST;

class API extends REST
{

    use apiValidaciones;
    use devolverTramite;
    use grabaFormularioMercantil;
    use mantenimientoServicios;
    use mregActosDocumentos;
    use mregActualizaciones;
    use mregMantenimientos;
    use mregPagoElectronico;
    use mregRadicarDocumentos;
    use mregRecibirPagos;
    use mregReingresarGenerico;
    use mregSolicitudDeposito;
    use mregTramitesRues;
    use mregValidaciones;
    use mregVotaciones;

    public $data = "";

    public function __construct()
    {
        // Init parent contructor
        parent::__construct();
    }

    /*
     * Public method for access api.
     * This method dynmically call the method based on the query string
     *
     */

    public function processApi()
    {

        // ********************************************************************** //
        // Captura de parametros
        // ********************************************************************** //
        $_SESSION["entrada"] = array();
        $_SESSION["entrada1"] = array();
        $_SESSION["jsonsalida"] = array();


        $dataTexto = file_get_contents("php://input");

        //WSI 2017-04-14 Ajuste para que reciba peticiones GET sin cadenas JSON
        if ($dataTexto == '') {
            if (isset($_GET) && !empty($_GET)) {
                $dataTexto = json_encode($_GET);
            } else {
                if (isset($_POST) && !empty($_POST)) {
                    $dataTexto = json_encode($_POST);
                }
            }
        } else {
            if (!$this->isJsonApi($dataTexto)) {
                parse_str($dataTexto, $dataTexto1);
                $dataTexto = json_encode($dataTexto1);
            }
        }

        //
        $data = json_decode($dataTexto, true);

        // 2017-12-28: JINT: Se adiciona para controlar la recepción de data compleja
        $_SESSION["entrada1"] = $data;

        //Convertir cualquier entrada para que sea manipulada como JSON
        if (count($data) > 0) {
            foreach ($data as $key => $valor) {
                $_SESSION["entrada"][$key] = $valor;
            }
        } else {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'no hay par&aacute;metros';
            $this->response($this->json($_SESSION["jsonsalida"]), 200);
        }

        // ********************************************************************** //
        // Desencapsula usuariows y token
        // ENCRYPTION_SECRET_KEY
        // ENCRYPTION_SECRET_IV
        // ********************************************************************** //
        $secretkey = '';
        $secretiv = '';

        //
        if (!defined('ENCRYPTION_SECRET_KEY') || ENCRYPTION_SECRET_KEY == '') {
            $secretkey = '';
        } else {
            $secretkey = ENCRYPTION_SECRET_KEY;
        }

        //
        if (!defined('ENCRYPTION_SECRET_IV') || ENCRYPTION_SECRET_IV == '') {
            $secretiv = '';
        } else {
            $secretiv = ENCRYPTION_SECRET_IV;
        }

        //
        if (isset($_SESSION["entrada"]["acceso"])) {
            if ($_SESSION["entrada"]["acceso"] != '') {
                $temp = $this->encrypt_decrypt('decrypt', ENCRYPTION_SECRET_KEY, ENCRYPTION_SECRET_IV, base64_decode($_SESSION["entrada"]["acceso"]));
                if ($temp == '') {
                    $temp = $this->encrypt_decrypt('decrypt', ENCRYPTION_SECRET_KEY, ENCRYPTION_SECRET_IV, $_SESSION["entrada"]["acceso"]);
                }

                // echo 'temp: '. $temp . '<br>';
                if (substr_count($temp, '|') == 1) {
                    list($us, $tk) = explode('|', $temp);
                    $em = '';
                }
                if (substr_count($temp, '|') == 2) {
                    list($us, $tk, $em) = explode('|', $temp);
                }
                $_SESSION["entrada"]["usuariows"] = $us;
                $_SESSION["entrada"]["token"] = $tk;
                if ($em != '') {
                    $_SESSION["entrada"]["codigoempresa"] = $em;
                }
                $_SESSION["entrada1"]["usuariows"] = $us;
                $_SESSION["entrada1"]["token"] = $tk;
                if ($em != '') {
                    $_SESSION["entrada1"]["codigoempresa"] = $em;
                }
            }
        }

        // ********************************************************************** //
        // Validaciones sobre la variable codigoempresa
        // ********************************************************************** //
        if (isset($_SESSION["entrada"]["codigoempresa"])) {
            $_SESSION["generales"]["codigoempresa"] = $_SESSION["entrada"]["codigoempresa"];
            if (ltrim($_SESSION["generales"]["codigoempresa"], "0") == '') {
                $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                $_SESSION["jsonsalida"]["mensajeerror"] = 'codigoempresa mal reportado';
                $this->response($this->json($_SESSION["jsonsalida"]), 200);
            }
            if (!file_exists($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common' . $_SESSION["generales"]["codigoempresa"] . '.php')) {
                $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                $_SESSION["jsonsalida"]["mensajeerror"] = 'No se encuentra archivo de configuraci&oacute;n para la empresa solicitada.';
                $this->response($this->json($_SESSION["jsonsalida"]), 200);
            }
        } else {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'codigoempresa es obligatorio';
            $this->response($this->json($_SESSION["jsonsalida"]), 200);
        }

        // ********************************************************************** //
        // incluye las librerias que serán utilizadas
        // ********************************************************************** //
        if (isset($_SESSION["jsonsalida"]["codigoerror"]) && $_SESSION["jsonsalida"]["codigoerror"] == "9999") {
            $this->response($this->json($_SESSION["jsonsalida"]), 200);
        } else {
            require_once($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common' . $_SESSION["generales"]["codigoempresa"] . '.php');
            require_once('mysqli.php');
        }
        if (isset($_REQUEST['rquest'])) {
            $func = (trim(str_replace("/", "", $_REQUEST['rquest'])));
        } else {
            $func = "";
        }

        //2017-12-08 WSIERRA: Se utiliza unicamente el llamdo a la función y se descarta el control anterior
        try {
            if (is_callable([$this, $func])) {
                call_user_func(array($this, $func), $this);
            } else {
                $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                $_SESSION["jsonsalida"]["mensajeerror"] = 'Funcion o recurso no ejecutable ' . $func;
                $this->response($this->json($_SESSION["jsonsalida"]), 403);
            }
        } catch (Exception $e) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = '(exception) Funcion o recurso no ejecutable ' . $func;
            $this->response($this->json($_SESSION["jsonsalida"]), 403);
        }
    }

    public function json($data)
    {
        if (is_array($data)) {
            return json_encode($data);
        }
    }

    public function validarToken($metodo, $token_send, $usuariows = '')
    {
        $validaTokenUnico = true;
        if ((defined('TOKEN_API_DEFECTO') && TOKEN_API_DEFECTO != '') && (defined('USUARIO_API_DEFECTO') && USUARIO_API_DEFECTO != '')) {
            if ($token_send == md5(sha1(TOKEN_API_DEFECTO)) && $usuariows == md5(sha1(USUARIO_API_DEFECTO))) {
                return true;
            } else {
                $validaTokenUnico = false;
            }
        } else {
            $validaTokenUnico = false;
        }

        //WSI - 20170418 - Interpretación de JWT

        $classObj = new funciones();
        $resp = $classObj->interpretarJWT($token_send);
        //$resp = funcionesAPI::interpretarJWT($token_send);

        if ($resp["codigoerror"] == '0000') {

            $mysqli = new \mysqli(DB_HOST, DB_USUARIO, DB_PASSWORD, DB_NAME, DB_PORT);
            if ($mysqli->connect_error) {
                $_SESSION["jsonsalida"]["codigoerror"] = "9997";
                $_SESSION["jsonsalida"]["mensajeerror"] = $mysqli->connect_error;
                return false;
            }

            $metodoWs = retornarRegistroMysqliApi($mysqli, 'mreg_api_sii_metodos', "metodo='" . $metodo . "'");

            if (!$metodoWs || empty($metodoWs)) {
                $mysqli->close();
                $_SESSION["jsonsalida"]["codigoerror"] = "9998";
                $_SESSION["jsonsalida"]["mensajeerror"] = 'El m&eacute;todo no concuerda con la informaci&oacute;n almacenada en la BD';
                return false;
            }

            if ($metodoWs['estado'] != "A") {
                $mysqli->close();
                $_SESSION["jsonsalida"]["codigoerror"] = "9998";
                $_SESSION["jsonsalida"]["mensajeerror"] = 'El m&eacute;todo no est&aacute; activo';
                return false;
            }

            $metodoUsuarioWs = retornarRegistroMysqliApi($mysqli, 'mreg_api_sii_usuarios_metodos', "metodo='" . $metodoWs["id"] . "' and usuariows='" . $resp["propiedades"]->data->sub . "'");

            if (!$metodoUsuarioWs || empty($metodoUsuarioWs)) {
                $mysqli->close();
                $_SESSION["jsonsalida"]["codigoerror"] = "9998";
                $_SESSION["jsonsalida"]["mensajeerror"] = 'El campo <<usuariows>> no est&aacute; asociado al m&eacute;todo';
                return false;
            }

            if (!empty($resp["propiedades"])) {
                $mysqli->close();
                $_SESSION["entrada"]["codigoempresa"] = $resp["propiedades"]->data->id;
                $_SESSION["entrada"]["usuariows"] = $resp["propiedades"]->data->sub;
                return true;
            } else {
                $mysqli->close();
                $_SESSION["jsonsalida"]["codigoerror"] = "9998";
                $_SESSION["jsonsalida"]["mensajeerror"] = 'El token no contiene la informaci&oacute;n requerida';
                return false;
            }
        } else {
            $_SESSION["jsonsalida"]["codigoerror"] = "9998";
            $_SESSION["jsonsalida"]["mensajeerror"] = $resp["mensajeerror"];
            return false;
        }

        if ($validaTokenUnico == false) {
            $mysqli->close();
            $_SESSION["jsonsalida"]["codigoerror"] = "9998";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Error en definici&oacute;n del Token Defecto';
            return false;
        }
    }

    public function response($data, $status)
    {
        parent::response($data, $status);
    }

    public function validarParametro($parametro, $obligatorio, $valecero = false)
    {
        if (!isset($_SESSION["entrada"][$parametro])) {
            if ($obligatorio) {
                $_SESSION["jsonsalida"]["codigoerror"] = '9999';
                $_SESSION["jsonsalida"]["mensajeerror"] = 'Parámetros incompletos - ' . $parametro;
                $this->response($this->json($_SESSION["jsonsalida"]), 200);
            } else {
                $_SESSION["entrada"][$parametro] = '';
            }
        } elseif (isset($_SESSION["entrada"][$parametro]) && trim($_SESSION["entrada"][$parametro]) == "") {
            if ($obligatorio) {
                if (($_SESSION["entrada"][$parametro] == '0') && $valecero) {
                    $_SESSION["entrada"][$parametro] = "0";
                } else {
                    $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                    $_SESSION["jsonsalida"]["mensajeerror"] = 'Se recibió el parámetro ' . $parametro . ' sin contenido';
                    $this->response($this->json($_SESSION["jsonsalida"]), 200);
                }
            } else {
                if ($parametro != 'usuario' && $parametro != 'tipousuario') {
                    $_SESSION["entrada"][$parametro] = ltrim($_SESSION["entrada"][$parametro], "0");
                }
            }
        } else {
            //$_SESSION["entrada"]["tipousuario"] = sprintf("%02s", $_SESSION["entrada"]["tipousuario"]);
            if ($parametro != 'usuario' && $parametro != 'tipousuario') {
                $_SESSION["entrada"][$parametro] = ltrim($_SESSION["entrada"][$parametro], "0");
            }
        }
    }

    public function encrypt_decrypt($action, $secret_key, $secret_iv, $string)
    {
        $output = false;
        $encrypt_method = "AES-256-CBC";
        $key = hash('sha256', $secret_key);

        // iv - encrypt method AES-256-CBC expects 16 bytes - else you will get a warning
        $iv = substr(hash('sha256', $secret_iv), 0, 16);
        if ($action == 'encrypt') {
            $output = openssl_encrypt($string, $encrypt_method, $key, 0, $iv);
            $output = base64_encode($output);
        } else if ($action == 'decrypt') {
            $output = openssl_decrypt(base64_decode($string), $encrypt_method, $key, 0, $iv);
        }
        return $output;
    }

    public function isJsonApi($string)
    {
        return ((is_string($string) &&
            (is_object(json_decode($string)) ||
                is_array(json_decode($string))))) ? true : false;
    }
}

// Initiate Library

$api = new API();
$api->processApi();

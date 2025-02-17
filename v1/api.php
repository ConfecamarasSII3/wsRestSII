<?php

namespace libreriaswsRestSII;

session_start();
set_time_limit(0);
ini_set('memory_limit', '1024M');
error_reporting(E_ALL);
ini_set('display_errors', '1');
$_SESSION["generales"]["zonahoraria"] = "America/Bogota";
$_SESSION["generales"]["idioma"] = "es";
date_default_timezone_set($_SESSION["generales"]["zonahoraria"]);
require_once('../config/config.php');

if (file_exists(PATH_ABSOLUTO_SITIO)) {
    $_SESSION["generales"]["pathabsolutositio"] = PATH_ABSOLUTO_SITIO;
    $_SESSION["generales"]["pathabsoluto"] = PATH_ABSOLUTO_SITIO;
} else {
    echo "No existe la ruta del PATH_ABSOLUTO_SITIO" . PATH_ABSOLUTO_SITIO;
    die();
}
if (file_exists(PATH_ABSOLUTO_LOGS)) {
    $_SESSION["generales"]["pathabsolutologs"] = PATH_ABSOLUTO_LOGS;
} else {
    echo "No existe la ruta del PATH_ABSOLUTO_LOGS " . PATH_ABSOLUTO_LOGS;
    die();
}

//2019-07-05 : Weymer : Inicializar clavevalor para evitar reutilización de sesiones entre CC
$_SESSION["generales"]["clavevalor"] = array();
//

/*
 * Archivos del CORE del API
 */
require("Rest.inc.php");
require("funcionesAPI.php");
include("solicitarToken.php");
include("ConsultarBD.php");

/*
 * Archivos que realizan Consultas
 */
include("actualizarClienteLiquidacion.php");
include("actualizarControlesLiquidacion.php");
include("actualizarEstadoLiquidacion.php");
include('adicionarDocumentosExpediente.php');
include("administrarAnexos.php");
include("administrarFotosExpediente.php");
include("almacenarFormularioRenovacion.php");
include("aplicar1756Liquidacion.php");
include("autenticarUsuarioVerificado.php");
include("autenticarUsuarioRegistrado.php");
include("busquedaExpedientes.php");
include("calcularTamanoEmpresarial.php");
include("confirmarMatriculasSistemasExternos.php");
include("consultarANI.php");
include("consultarBarrios.php");
include("consultarCertificadoCapas.php");
include("consultarCiius.php");
include("consultarCodigosEmprendimientoSocial.php");
include("consultarCodigosEsadl.php");
include("consultarDevolucion.php");
include("consultarDirectorioAfiliados.php");
include("consultarEstablecimientosNacionales.php");
include("consultarEstadoRadicado.php");
include("consultarExpedienteMercantil.php");
include("consultarExpedienteMercantilClientesExternos.php");
include("consultarExpedienteProponente.php");
include("consultarGeolocalizacionCenso.php");
include("consultarImagenesExpedienteMercantil.php");
include("consultarImagenesExpedienteProponentes.php");
include("consultarInformacionSello.php");
include("consultarInformacionSelloUnitario.php");
include("consultarKardexExpediente.php");
include("consultarLiquidacion.php");
include("consultarLiquidacionReferencia.php");
include("consultarListaResponsabilidadesTributarias.php");
include("consultarMotivosCancelacion.php");
include("consultarMultasPonal.php");
include("consultarMunicipiosJurisdiccion.php");
include("consultarRelacionRecibos.php");
include("consultarRelacionRecibosParaSap.php");
include("consultarRelacionTramites.php");
include("consultarRecibo.php");
include("consultarRadicado.php");
include("consultarRadicadoSaia.php");
include("consultarRadicados.php");
include("consultarRues.php");
include("consultarServicios.php");
include("consultarTransaccion.php");
include("consultarTransaccionesCliente.php");
include("consultarUsuarioVerificado.php");
include("consultarVinculosIdentificacion.php");
include("construirRelacionInscritos.php");
include("construirSobreDigital.php");
include("directorioAfiliados.php");
include("enviarPinSms.php");
include("exportarMatriculados.php");
include("firmarDigitalmenteDocumento.php");
include("firmarElectronicamenteTramite.php");
include("generarAlertasTempranas.php");
include("inactivarUsuarioRegistradoVerificado.php");
include("interoperabilidadRues.php");
include("liquidarRenovacionMetodos.php");
include("liquidarServicios.php");
include("liquidarSolicitudCancelacion.php");
include("liquidarTransaccion.php");
include("ProcesosRegistrosPublicos.php");
include("recibirCambioEstadoRadicado.php");
include("recordarContrasenaAfiliado.php");
include("recordarContrasenaVerificado.php");
include("recuperarAnexoLiquidacion.php");
include("recuperarFormularioMercantil.php");
include("recuperarFormularioRenovacion.php");
include("reingresarTramite.php");
include("relacionActosInscritos.php");
include("relacionExpedientesModificados.php");
include("relacionMatriculasRenovar.php");
include("relacionMovimientos.php");
include("relacionPotencialesAfiliados.php");
include("reportarAccionLog.php");
include("reportarMatriculasSistemasExternos.php");
include("reportarNovedadesGeoreferenciacion.php");
include("reportarTransaccion.php");
include("reportarPago.php");
include("restaurarClaveRegistro.php");
include("retornarDatosFormularioMercantil.php");
include("retornarExpedienteMercantil.php");
include("retornarMenuUsuario.php");
include("retornarResumenMatriculadosCanceladosAno.php");
include("retornarResumenServicios.php");
include("retornarSoportesLiquidacion.php");
include("solicitarCertificado.php");
include("solicitarRegistro.php");
include("uploadSoporteLiquidacion.php");
include("verificarLimiteConsultas.php");
include("verificarAfiliado.php");
include("verificarParametrosFirmado.php");
include("verificarRegistro.php");

use libreriaswsRestSII\API;
use libreriaswsRestSII\REST;

class API extends REST
{

    use actualizarClienteLiquidacion;
    use actualizarControlesLiquidacion;
    use actualizarEstadoLiquidacion;
    use adicionarDocumentosExpediente;
    use administrarAnexos;
    use administrarFotosExpediente;
    use almacenarFormularioRenovacion;
    use aplicar1756Liquidacion;
    use autenticarUsuarioVerificado;
    use autenticarUsuarioRegistrado;
    use busquedaExpedientes;
    use calcularTamanoEmpresarial;
    use confirmarMatriculasSistemasExternos;
    use consultarANI;
    use ConsultarBD;
    use consultarBarrios;
    use consultarCertificadoCapas;
    use consultarCiius;
    use consultarCodigosEmprendimientoSocial;
    use consultarCodigosEsadl;
    use consultarDevolucion;
    use consultarDirectorioAfiliados;
    use consultarEstablecimientosNacionales;
    use consultarEstadoRadicado;
    use consultarExpedienteMercantil;
    use consultarExpedienteMercantilClientesExternos;
    use consultarExpedienteProponente;
    use consultarGeolocalizacionCenso;
    use consultarImagenesExpedienteMercantil;
    use consultarImagenesExpedienteProponentes;
    use consultarInformacionSello;
    use consultarInformacionSelloUnitario;
    use consultarKardexExpediente;
    use consultarLiquidacion;
    use consultarLiquidacionReferencia;
    use consultarListaResponsabilidadesTributarias;
    use consultarMotivosCancelacion;
    use consultarMultasPonal;
    use consultarMunicipiosJurisdiccion;
    use consultarRadicado;
    use consultarRadicadoSaia;
    use consultarRadicados;
    use consultarRecibo;
    use consultarRelacionRecibos;
    use consultarRelacionRecibosParaSap;
    use consultarRelacionTramites;
    use consultarRues;
    use consultarServicios;
    use consultarTransaccion;
    use consultarTransaccionesCliente;
    use consultarUsuarioVerificado;
    use consultarVinculosIdentificacion;
    use construirRelacionInscritos;
    use construirSobreDigital;
    use directorioAfiliados;
    use enviarPinSms;
    use exportarMatriculados;
    use firmarDigitalmenteDocumento;
    use firmarElectronicamenteTramite;
    use generarAlertasTempranas;
    use inactivarUsuarioRegistradoVerificado;
    use interoperabilidadRues;
    use liquidarRenovacionMetodos;
    use liquidarServicios;
    use liquidarSolicitudCancelacion;
    use liquidarTransaccion;
    use ProcesosRegistrosPublicos;
    use recibirCambioEstadoRadicado;
    use recordarContrasenaAfiliado;
    use recordarContrasenaVerificado;
    use recuperarAnexoLiquidacion;
    use recuperarFormularioMercantil;
    use recuperarFormularioRenovacion;
    use reingresarTramite;
    use relacionActosInscritos;
    use relacionExpedientesModificados;
    use relacionMatriculasRenovar;
    use relacionMovimientos;
    use relacionPotencialesAfiliados;
    use reportarAccionLog;
    use reportarMatriculasSistemasExternos;
    use reportarNovedadesGeoreferenciacion;
    use reportarPago;
    use reportarTransaccion;
    use restaurarClaveRegistro;
    use retornarDatosFormularioMercantil;
    use retornarExpedienteMercantil;
    use retornarMenuUsuario;
    use retornarResumenMatriculadosCanceladosAno;
    use retornarResumenServicios;
    use retornarSoportesLiquidacion;
    use solicitarCertificado;
    use solicitarRegistro;
    use solicitarToken;
    use uploadSoporteLiquidacion;
    use verificarLimiteConsultas;
    use verificarAfiliado;
    use verificarParametrosFirmado;
    use verificarRegistro;

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
        $_SESSION["dataTexto"] = '';

        $dataTexto = file_get_contents("php://input");
        $_SESSION["dataTexto"] = $dataTexto;

        $f = fopen($_SESSION["generales"]["pathabsolutologs"] . '/api_request_' . date("Ymd") . '.log', "wa");
        fwrite($f, date("Ymd") . '-' . date("H:i:s") . ' - ' . $dataTexto . "\r\n\r\n");
        fclose($f);

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

        $dataTexto = str_replace('"{', '{', $dataTexto);
        $dataTexto = str_replace('}"', '}', $dataTexto);
        $dataTexto = str_replace('\"', '&quot;', $dataTexto);

        $data = json_decode($dataTexto, true);

        // 2017-12-28: JINT: Se adiciona para controlar la recepción de data compleja
        $_SESSION["entrada1"] = $data;

        //Convertir cualquier entrada para que sea manipulada como JSON
        if (!empty($data)) {
            foreach ($data as $key => $valor) {
                $_SESSION["entrada"][$key] = $valor;
            }
        } else {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'no hay parámetros';
            $this->response($this->json($_SESSION["jsonsalida"]), 200);
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
                $_SESSION["jsonsalida"]["mensajeerror"] = 'No se encuentra archivo de configuración para la empresa solicitada.';
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
        //Debe ajustarse y desacoplarlo del SII. WSIERRA 2018-02-20
        // require_once($_SESSION["generales"]["pathabsoluto"] . '/librerias/funciones/persistencia.php');
        // require_once($_SESSION["generales"]["pathabsoluto"] . '/librerias/funciones/persistenciamreg.php');
        //Requerido en modo desacoplado
        require_once($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common' . $_SESSION["generales"]["codigoempresa"] . '.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');

        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/myErrorHandler.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/librerias/wsRestSII/v1/funcionesAPI.php');
        $resError = set_error_handler('myErrorHandler');

        //
        if (isset($_REQUEST['rquest'])) {
            $func = (trim(str_replace("/", "", $_REQUEST['rquest'])));
        } else {
            $func = "";
        }

        \logApi::general2('logApiGeneral_' . date("Ymd"), '', $func . ' ' . $dataTexto);

        //
        if (isset($_SESSION["entrada"]["ip"])) {
            $ip = $_SESSION["entrada"]["ip"];
        } else {
            $ip = \funcionesGenerales::localizarIp();
        }

        //
        if (!isset($_SESSION["entrada"]["ambiente"]) || $_SESSION["entrada"]["ambiente"] == '' || $_SESSION["entrada"]["ambiente"] == 'A') {
            $mysqli = conexionMysqliApi();
        }

        //
        if (isset($_SESSION["entrada"]["ambiente"]) && $_SESSION["entrada"]["ambiente"] == 'D') {
            $mysqli = conexionMysqliApi('D-' . $_SESSION["generales"]["codigoempresa"]);
        }

        //
        if (isset($_SESSION["entrada"]["ambiente"]) && $_SESSION["entrada"]["ambiente"] == 'P') {
            $mysqli = conexionMysqliApi('P-' . $_SESSION["generales"]["codigoempresa"]);
        }

        //
        if (isset($_SESSION["entrada"]["ambiente"]) && $_SESSION["entrada"]["ambiente"] == 'Q') {
            $mysqli = conexionMysqliApi('Q-' . $_SESSION["generales"]["codigoempresa"]);
        }

        // $mysqli = conexionMysqliApi();
        if ($mysqli === false) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'No fue posible la conexion a la BD (***)';
            $this->response($this->json($_SESSION["jsonsalida"]), 200);
        }

        $nlogapi = 'log_api_' . date("Ym");

        $arrCampos = array(
            'fecha',
            'hora',
            'usuarioapi',
            'ip',
            'metodo',
            'request'
        );
        $arrValores = array(
            "'" . date("Ymd") . "'",
            "'" . date("H:i:s") . "'",
            "'" . $_SESSION["entrada"]["usuariows"] . "'",
            "'" . $ip . "'",
            "'" . $func . "'",
            "'" . addslashes($dataTexto) . "'"
        );
        insertarRegistrosMysqliApi($mysqli, $nlogapi, $arrCampos, $arrValores);

        $mysqli->close();

        //2017-12-08 WSIERRA: Se utiliza unicamente el llamdo a la función y se descarta el control anterior
        call_user_func(array($this, $func), $this);
    }

    public function json($data)
    {
        if (is_array($data)) {
            return json_encode($data);
        }
    }

    public function validarToken($metodo, $token_send, $usuariows = '')
    {
        /*
         * Método que realiza la validación de tokens interpretando el string JWT
         */

        // 2017-11-16: JINT: En caso que el usuario y token recibidos correspondan
        // Con el usuario y token por defecto entonces permite el acceso
        // pendiente de controlar que el permiso se otorgue si y solo si la petición llega de localhost
        //
        // 2017-12-07: WSIERRA: Se corrige la validación del usuario y token por defecto.

        $validaTokenUnico = true;
        if ((defined('TOKEN_API_DEFECTO') && TOKEN_API_DEFECTO != '') && (defined('USUARIO_API_DEFECTO') && USUARIO_API_DEFECTO != '')) {
            if ($token_send == md5(TOKEN_API_DEFECTO) && $usuariows == md5(USUARIO_API_DEFECTO)) {
                return true;
            } else {
                $validaTokenUnico = false;
            }
        } else {
            $validaTokenUnico = false;
        }

        //WSI - 20170418 - Interpretación de JWT

        $classObj = new funcionesAPI();
        $resp = $classObj->interpretarJWT($token_send);

        if ($resp["codigoerror"] == '0000') {

            $mysqli = conexionMysqliApi();
            if ($mysqli === false) {
                $_SESSION["jsonsalida"]["codigoerror"] = "9997";
                $_SESSION["jsonsalida"]["mensajeerror"] = 'Error de conexión a la BD';
                return false;
            }

            $metodoWs = retornarRegistroMysqliApi($mysqli, 'mreg_api_sii_metodos', "metodo='" . $metodo . "'");

            if (!$metodoWs || empty($metodoWs)) {
                $mysqli->close();
                $_SESSION["jsonsalida"]["codigoerror"] = "9998";
                $_SESSION["jsonsalida"]["mensajeerror"] = 'El método no concuerda con la información almacenada en la BD';
                return false;
            }

            if ($metodoWs['estado'] != "A") {
                $mysqli->close();
                $_SESSION["jsonsalida"]["codigoerror"] = "9998";
                $_SESSION["jsonsalida"]["mensajeerror"] = 'El método no está activo';
                return false;
            }

            $metodoUsuarioWs = retornarRegistroMysqliApi($mysqli, 'mreg_api_sii_usuarios_metodos', "metodo='" . $metodoWs["id"] . "' and usuariows='" . $resp["propiedades"]->data->sub . "'");

            if (!$metodoUsuarioWs || empty($metodoUsuarioWs)) {
                $mysqli->close();
                $_SESSION["jsonsalida"]["codigoerror"] = "9998";
                $_SESSION["jsonsalida"]["mensajeerror"] = 'El campo <<usuariows>> no está asociado al método';
                return false;
            }

            if (!empty($resp["propiedades"])) {

                // 2024-09-03: Se adiciona a la tabla la fecha del ultimo acceso y el ultimo metodo de api consumido
                $arrCampos = array(
                    'fechaultimoacceso',
                    'ultimometodoconsumido'
                );
                $arrValores = array(
                    "'" . date("Ymd") . ' ' . date("His") . "'",
                    "'" . $metodo . "'"
                );
                regrabarRegistrosMysqliApi($mysqli, 'mreg_api_sii_usuarios', $arrCampos, $arrValores, "usuariows='" . $resp["propiedades"]->data->sub . "'");

                $mysqli->close();
                $_SESSION["entrada"]["codigoempresa"] = $resp["propiedades"]->data->id;
                $_SESSION["entrada"]["usuariows"] = $resp["propiedades"]->data->sub;
                return true;
            } else {
                $mysqli->close();
                $_SESSION["jsonsalida"]["codigoerror"] = "9998";
                $_SESSION["jsonsalida"]["mensajeerror"] = 'El token no contiene la información requerida';
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
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Error en definición del Token Defecto';
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
                $_SESSION["entrada"][$parametro] = $_SESSION["entrada"][$parametro];
            }
        } else {
            $_SESSION["entrada"][$parametro] = $_SESSION["entrada"][$parametro];
        }
    }

    public function isJsonApi($string)
    {
        return ((is_string($string) && (is_object(json_decode($string)) ||
            is_array(json_decode($string))))) ? true : false;
    }
}

// Initiate Library

$api = new API();
$api->processApi();

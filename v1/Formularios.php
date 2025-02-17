<?php

/*
 * Se recibe json con la siguiente información
 *
 */

namespace libreriaswsRestSII;

use libreriaswsRestSII\API;

trait Formularios
{
    public function recuperarFormularioMercantil(API $api)
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
        $_SESSION['jsonsalida']['titulo'] = '';
        $_SESSION['jsonsalida']['texto'] = '';
        $_SESSION['jsonsalida']['numeroliquidacion'] = '';
        $_SESSION['jsonsalida']['numerorecuperacion'] = '';
        $_SESSION['jsonsalida']['tipotramitegeneral'] = '';
        $_SESSION['jsonsalida']['tipotramitetransaccion'] = '';
        $_SESSION['jsonsalida']['matricula'] = '';
        $_SESSION['jsonsalida']['numeroliquidacion'] = '';
        $_SESSION['jsonsalida']['tipotramitegeneral'] = '';
        $_SESSION['jsonsalida']['tipotramitetransaccion'] = '';
        $_SESSION['jsonsalida']['formulario'] = array();

        // Verifica método de recepcion de parámetros
        if ($api->get_request_method() != "POST" && $api->get_request_method() != "GET") {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'La petición debe ser POST/GET';
        }

        //
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
        $api->validarParametro("idliquidacion", true);
        $api->validarParametro("sectransaccion", false);
        $api->validarParametro("cc", false);
        $api->validarParametro("matricula", true);
        $api->validarParametro("anorenovar", true);
        $api->validarParametro("anorenovarpri", true);
        $api->validarParametro("organizacion", false);
        $api->validarParametro("categoria", false);

        //
        if (!$api->validarToken('recuperarFormularioMercantil', $_SESSION["entrada"]["token"], $_SESSION["entrada"]["usuariows"])) {
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
        $_SESSION["tramite"] = \funcionesRegistrales::retornarMregLiquidacion($mysqli, $_SESSION["entrada"]["idliquidacion"]);
        if ($_SESSION["tramite"] === false || empty($_SESSION["tramite"])) {
            $mysqli->close();
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Liquidación no localizada';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        // ********************************************************************** //
        // Arma data del formulario
        // ********************************************************************** //
        $_SESSION["formulario"]["tipomatricula"] = '';
        $_SESSION["formulario"]["tipotramite"] = $_SESSION["tramite"]["tipotramite"];
        $_SESSION["formulario"]["reliquidacion"] = $_SESSION["tramite"]["reliquidacion"];
        $_SESSION["formulario"]["liquidacion"] = $_SESSION["entrada"]["idliquidacion"];
        $_SESSION["formulario"]["matricula"] = $_SESSION["entrada"]["matricula"];
        $_SESSION["formulario"]["anorenovar"] = $_SESSION["entrada"]["anorenovar"];
        $_SESSION["formulario"]["anorenovarpri"] = $_SESSION["entrada"]["anorenovarpri"];
        $_SESSION["formulario"]["sectransaccion"] = $_SESSION["entrada"]["sectransaccion"];
        $_SESSION["formulario"]["organizacion"] = '';
        $_SESSION["formulario"]["categoria"] = '';


        // ********************************************************************** //
        // Recupera datos del expediente
        // ********************************************************************** //
        $exps = retornarRegistroMysqliApi($mysqli, "mreg_liquidaciondatos", "idliquidacion=" . $_SESSION["entrada"]["idliquidacion"] . " and expediente='" . $_SESSION["entrada"]["matricula"] . "'");
        if ($exps === false || empty($exps)) {
            if ($_SESSION["entrada"]["matricula"] == '' ||
                substr($_SESSION["entrada"]["matricula"], 0, 5) == 'NUEVA') {
                $_SESSION["formulario"]["datos"] = \funcionesGenerales::desserializarExpedienteMatricula($mysqli, '');
            } else {
                $_SESSION["formulario"]["datos"] = \funcionesRegistrales::retornarExpedienteMercantil($mysqli, $_SESSION["entrada"]["matricula"]);
                $_SESSION["formulario"]["organizacion"] = $_SESSION["formulario"]["datos"]["organizacion"];
                $_SESSION["formulario"]["categoria"] = $_SESSION["formulario"]["datos"]["categoria"];
                \funcionesRegistrales::almacenarDatosImportantesRenovacion($mysqli, $_SESSION["entrada"]["idliquidacion"], $_SESSION["formulario"]["datos"], 'I');
            }
        } else {
            $_SESSION["formulario"]["datos"] = \funcionesGenerales::desserializarExpedienteMatricula($mysqli, $exps["xml"]);
            $_SESSION["formulario"]["organizacion"] = $_SESSION["formulario"]["datos"]["organizacion"];
            $_SESSION["formulario"]["categoria"] = $_SESSION["formulario"]["datos"]["categoria"];
        }
        unset($exps);

        // ********************************************************************** //
        // Arma el formulario que va a retornar
        // ********************************************************************** //
        // ********************************************************************** //
        // Encabezados
        // ********************************************************************** //
        $tipo = '';
        if ($_SESSION["formulario"]["datos"]["organizacion"] == '01') {
            $_SESSION['jsonsalida']['titulo'] = 'FORMULARIO DE PERSONA NATURAL';
            $_SESSION['jsonsalida']['texto'] = 'A continuación se muestra el formulario de captura de renovación para ';
            $_SESSION['jsonsalida']['texto'] .= 'Personas Naturales, por favor diligenciar en su totalidad, ';
            $_SESSION['jsonsalida']['texto'] .= 'tenga en cuenta que los campos sombreados no son diligenciables y que los ';
            $_SESSION['jsonsalida']['texto'] .= 'campos marcados con (*) son obligatorios. Al terminar, oprima el botón grabar, ';
            $_SESSION['jsonsalida']['texto'] .= 'el sistema le indicará que inconsistencias encuentra para proceder a su ';
            $_SESSION['jsonsalida']['texto'] .= 'correspondiente corrección.';
            $tipo = 'pnat';
        }
        if ($_SESSION["formulario"]["datos"]["organizacion"] == '02') {
            $_SESSION['jsonsalida']['titulo'] = 'FORMULARIO DE ESTABLECIMIENTOS DE COMERCIO';
            $_SESSION['jsonsalida']['texto'] = 'A continuación se muestra el formulario de captura de renovación para ';
            $_SESSION['jsonsalida']['texto'] .= 'Establecimientos de Comercio, Por favor diligenciar en su totalidad, ';
            $_SESSION['jsonsalida']['texto'] .= 'tenga en cuenta que los campos sombreados no son diligenciables y que los ';
            $_SESSION['jsonsalida']['texto'] .= 'campos marcados con (*) son obligatorios. Al terminar, oprima el botón grabar, ';
            $_SESSION['jsonsalida']['texto'] .= 'el sistema le indicará que inconsistencias encuentra para proceder a su ';
            $_SESSION['jsonsalida']['texto'] .= 'correspondiente corrección.';
            $tipo = 'est';
        }
        if ($_SESSION["formulario"]["datos"]["organizacion"] > '02' && $_SESSION["formulario"]["datos"]["categoria"] == '1') {
            if ($_SESSION["formulario"]["datos"]["organizacion"] == '12' || $_SESSION["formulario"]["datos"]["organizacion"] == '14') {
                $_SESSION['jsonsalida']['titulo'] = 'FORMULARIO DE ENTIDAD SIN ANIMO DE LUCRO O DE LA ECONOMIA SOLIDARIA';
                $_SESSION['jsonsalida']['texto'] = 'A continuación se muestra el formulario de captura de renovación para ';
                $_SESSION['jsonsalida']['texto'] .= 'Entidades sin ánimo de lucro o de la economía solidaria, Por favor diligenciar en su totalidad, ';
                $_SESSION['jsonsalida']['texto'] .= 'tenga en cuenta que los campos sombreados no son diligenciables y que los ';
                $_SESSION['jsonsalida']['texto'] .= 'campos marcados con (*) son obligatorios. Al terminar, oprima el botón grabar, ';
                $_SESSION['jsonsalida']['texto'] .= 'el sistema le indicará que inconsistencias encuentra para proceder a su ';
                $_SESSION['jsonsalida']['texto'] .= 'correspondiente corrección.';
                $tipo = 'esadl';
            } else {
                $_SESSION['jsonsalida']['titulo'] = 'FORMULARIO DE PERSONA JURIDICA';
                $_SESSION['jsonsalida']['texto'] = 'A continuación se muestra el formulario de captura de renovación para ';
                $_SESSION['jsonsalida']['texto'] .= 'Personas Jurídicas, Por favor diligenciar en su totalidad, ';
                $_SESSION['jsonsalida']['texto'] .= 'tenga en cuenta que los campos sombreados no son diligenciables y que los ';
                $_SESSION['jsonsalida']['texto'] .= 'campos marcados con (*) son obligatorios. Al terminar, oprima el botón grabar, ';
                $_SESSION['jsonsalida']['texto'] .= 'el sistema le indicará que inconsistencias encuentra para proceder a su ';
                $_SESSION['jsonsalida']['texto'] .= 'correspondiente corrección.';
                $tipo = 'pjur';
            }
        }
        if ($_SESSION["formulario"]["datos"]["organizacion"] > '02' && $_SESSION["formulario"]["datos"]["categoria"] == '2') {
            $_SESSION['jsonsalida']['titulo'] = 'FORMULARIO DE SUCURSAL';
            $_SESSION['jsonsalida']['texto'] = 'A continuación se muestra el formulario de captura de renovación para ';
            $_SESSION['jsonsalida']['texto'] .= 'Sucursales, Por favor diligenciar en su totalidad, ';
            $_SESSION['jsonsalida']['texto'] .= 'tenga en cuenta que los campos sombreados no son diligenciables y que los ';
            $_SESSION['jsonsalida']['texto'] .= 'campos marcados con (*) son obligatorios. Al terminar, oprima el botón grabar, ';
            $_SESSION['jsonsalida']['texto'] .= 'el sistema le indicará que inconsistencias encuentra para proceder a su ';
            $_SESSION['jsonsalida']['texto'] .= 'correspondiente corrección.';
            $tipo = 'suc';
        }
        if ($_SESSION["formulario"]["datos"]["organizacion"] > '02' && $_SESSION["formulario"]["datos"]["categoria"] == '3') {
            $_SESSION['jsonsalida']['titulo'] = 'FORMULARIO DE AGENCIA';
            $_SESSION['jsonsalida']['texto'] = 'A continuación se muestra el formulario de captura de renovación para ';
            $_SESSION['jsonsalida']['texto'] .= 'Agencias, Por favor diligenciar en su totalidad, ';
            $_SESSION['jsonsalida']['texto'] .= 'tenga en cuenta que los campos sombreados no son diligenciables y que los ';
            $_SESSION['jsonsalida']['texto'] .= 'campos marcados con (*) son obligatorios. Al terminar, oprima el botón grabar, ';
            $_SESSION['jsonsalida']['texto'] .= 'el sistema le indicará que inconsistencias encuentra para proceder a su ';
            $_SESSION['jsonsalida']['texto'] .= 'correspondiente corrección.';
            $tipo = 'age';
        }

        //
        $_SESSION['jsonsalida']['matricula'] = $_SESSION["entrada"]["matricula"];
        $_SESSION['jsonsalida']['numeroliquidacion'] = $_SESSION["entrada"]["idliquidacion"];
        $_SESSION['jsonsalida']['numerorecuperacion'] = $_SESSION["tramite"]["numerorecuperacion"];
        $_SESSION['jsonsalida']['tipotramitegeneral'] = $_SESSION["tramite"]["tipotramite"];
        $_SESSION['jsonsalida']['anorenovar'] = $_SESSION["entrada"]["anorenovar"];
        //die("<pre>".print_r($_SESSION["tramite"], true)."</pre>");
        if ($_SESSION['jsonsalida']['tipotramitegeneral'] == 'renovacionmatricula') {
            $_SESSION['jsonsalida']['tipotramitetransaccion'] = $_SESSION['jsonsalida']['tipotramitegeneral'];
        } else {
            $_SESSION['jsonsalida']['tipotramitetransaccion'] = $_SESSION["tramite"]["subtipotramite"];
        }



        // ********************************************************************** //
        // Grupo datos básicos
        // ********************************************************************** //
        $panel = array();
        $panel["col"] = '';
        $panel["offset"] = '';
        $panel["titulo"] = 'DATOS DE IDENTIFICACION';
        $panel["texto"] = '';
        $panel["inputs"] = array();

        // Matrícula
        $temp = array();
        $temp["tipo"] = 'input';
        $temp["mostrar"] = 'SI';
        $temp["protegido"] = 'SI';
        $temp["obligatorio"] = 'SI';
        $temp["encabezado"] = '';
        $temp["label"] = 'Número de matrícula o inscripción';
        $temp["id"] = 'matricula';
        $temp["name"] = 'matricula';
        $temp["type"] = 'text';
        $temp["size"] = '8';
        $temp["maxlength"] = '30';
        $temp["minlength"] = '0';
        $temp["value"] = $_SESSION["formulario"]["datos"]["matricula"];
        $temp["placeholder"] = 'Número de matrícula o inscripción';
        $panel["inputs"][] = $temp;

        // Fecha de matrícula
        $temp = array();
        $temp["tipo"] = 'input';
        $temp["mostrar"] = 'SI';
        $temp["protegido"] = 'SI';
        $temp["obligatorio"] = 'SI';
        $temp["encabezado"] = '';
        $temp["label"] = 'Fecha de matrícula o inscripción';
        $temp["id"] = 'fechamatricula';
        $temp["name"] = 'fechamatricula';
        $temp["type"] = 'date';
        $temp["size"] = '10';
        $temp["value"] = \funcionesGenerales::mostrarFecha($_SESSION["formulario"]["datos"]["fechamatricula"]);
        $temp["placeholder"] = 'Fecha de matrícula o inscripción';
        $panel["inputs"][] = $temp;

        // Fecha de renovación
        $temp = array();
        $temp["tipo"] = 'input';
        $temp["mostrar"] = 'SI';
        $temp["protegido"] = 'SI';
        $temp["obligatorio"] = 'SI';
        $temp["encabezado"] = '';
        $temp["label"] = 'Fecha de última renovación';
        $temp["id"] = 'fecharenovacion';
        $temp["name"] = 'fecharenovacion';
        $temp["type"] = 'date';
        $temp["size"] = '10';
        $temp["value"] = \funcionesGenerales::mostrarFecha($_SESSION["formulario"]["datos"]["fecharenovacion"]);
        $temp["placeholder"] = 'Fecha de última renovación';
        $panel["inputs"][] = $temp;

        // Ultimo año renovado
        $temp = array();
        $temp["tipo"] = 'input';
        $temp["mostrar"] = 'SI';
        $temp["protegido"] = 'SI';
        $temp["obligatorio"] = 'SI';
        $temp["encabezado"] = '';
        $temp["label"] = 'Último año renovado';
        $temp["id"] = 'ultanoren';
        $temp["name"] = 'ultanoren';
        $temp["type"] = 'text';
        $temp["size"] = '4';
        $temp["maxlength"] = '30';
        $temp["minlength"] = '0';
        $temp["value"] = $_SESSION["formulario"]["datos"]["ultanoren"];
        $temp["placeholder"] = 'Último año renovado';
        $panel["inputs"][] = $temp;

        // organizacion jurídica
        $temp = array();
        $temp["tipo"] = 'select';
        $temp["mostrar"] = 'SI';
        $temp["protegido"] = 'SI';
        $temp["obligatorio"] = 'SI';
        $temp["encabezado"] = '';
        $temp["label"] = 'Organización jurídica';
        $temp["id"] = 'organizacion';
        $temp["name"] = 'organizacion';
        $temp["type"] = 'text';
        $temp["size"] = '2';
        $temp["value"] = $_SESSION["formulario"]["datos"]["organizacion"];
        $temp["opc"] = array();
        $temp["opc"][] = array(
                'label' => 'Seleccione ...',
                'val' => '',
                'selected' => ''
            );
        $temx = retornarRegistrosMysqliApi($mysqli, "bas_organizacionjuridica", "1=1", "id");
        foreach ($temx as $tx) {
            $tsel = '';
            if ($tx["id"] == $_SESSION["formulario"]["datos"]["organizacion"]) {
                $tsel = 'SI';
            }
            $temp["opc"][] = array(
            'label' => $tx["id"] . ' - ' . strtoupper($tx["descripcion"]),
            'val' => $tx["id"],
            'selected' => $tsel
        );
        }
        $temp["placeholder"] = 'Organización jurídica';
        $panel["inputs"][] = $temp;

        // categoria
        if ($tipo == 'pjur' || $tipo == 'suc' || $tipo == 'age' || $tipo == 'est') {
            $temp = array();
            $temp["tipo"] = 'select';
            $temp["mostrar"] = 'SI';
            $temp["protegido"] = 'SI';
            $temp["obligatorio"] = 'NO';
            $temp["encabezado"] = '';
            $temp["label"] = 'Categoría';
            $temp["id"] = 'categoria';
            $temp["name"] = 'categoria';
            $temp["type"] = 'text';
            $temp["size"] = '1';
            $temp["value"] = $_SESSION["formulario"]["datos"]["categoria"];
            $temp["opc"] = array();
            $temp["opc"][] = array(
                'label' => '',
                'val' => '',
                'selected' => ''
            );
            $temx = retornarRegistrosMysqliApi($mysqli, "bas_categorias", "1=1", "id");
            foreach ($temx as $tx) {
                $tsel = '';
                if ($tx["id"] == $_SESSION["formulario"]["datos"]["categoria"]) {
                    $tsel = 'SI';
                }
                $temp["opc"][] = array(
                'label' => $tx["id"] . ' - ' . strtoupper($tx["descripcion"]),
                'val' => $tx["id"],
                'selected' => $tsel
            );
            }
            $temp["placeholder"] = 'Categoría';
            $panel["inputs"][] = $temp;
        }

        // naturaleza
        if ($tipo == 'pjur' || $tipo == 'esadl') {
            $temp = array();
            $temp["tipo"] = 'select';
            $temp["mostrar"] = 'SI';
            $temp["protegido"] = 'SI';
            $temp["obligatorio"] = 'SI';
            $temp["encabezado"] = '';
            $temp["label"] = 'Naturaleza';
            $temp["id"] = 'naturaleza';
            $temp["name"] = 'naturaleza';
            $temp["type"] = 'text';
            $temp["size"] = '1';
            $temp["value"] = $_SESSION["formulario"]["datos"]["naturaleza"];
            $temp["opc"] = array();
            $temp["opc"][] = array(
                'label' => 'Seleccione ...',
                'val' => '',
                'selected' => ''
            );
            $temx = retornarRegistrosMysqliApi($mysqli, "bas_naturalezas", "1=1", "id");
            foreach ($temx as $tx) {
                $tsel = '';
                if ($tx["id"] == $_SESSION["formulario"]["datos"]["naturaleza"]) {
                    $tsel = 'SI';
                }
                $temp["opc"][] = array(
                'label' => $tx["id"] . ' - ' . $tx["descripcion"],
                'val' => $tx["id"],
                'selected' => $tsel
            );
            }
            $temp["placeholder"] = 'Naturaleza';
            $panel["inputs"][] = $temp;
        }

        // Tipo de identificación
        if ($tipo == 'esadl') {
            //  die("<pre>".print_r($_SESSION["formulario"]["datos"], true)."</pre>");
            $temp = array();
            $temp["tipo"] = 'select';
            $temp["mostrar"] = 'SI';
            $temp["protegido"] = "SI";
            $temp["obligatorio"] = "NO";
            $temp["encabezado"] = '';
            $temp["label"] = 'Tipo de identificación';
            $temp["id"] = 'tipoidentificacion';
            $temp["name"] = 'tipoidentificacion';
            $temp["type"] = 'text';
            $temp["size"] = '1';
            $temp["value"] = $_SESSION["formulario"]["datos"]["tipoidentificacion"];
            $temp["opc"] = array();
            $temp["opc"][] = array(
          'label' => 'Seleccione ...',
          'val' => '',
          'selected' => ''
      );

            $temx = retornarRegistrosMysqliApi($mysqli, "mreg_tipoidentificacion", "1=1", "id");
            foreach ($temx as $tx) {
                $tsel = '';
                if ($tx["id"] == $_SESSION["formulario"]["datos"]["tipoidentificacion"]) {
                    $tsel = 'SI';
                }
                $temp["opc"][] = array(
          'label' => $tx["descripcion"],
          'val' => $tx["id"],
          'selected' => $tsel
      );
            }
            $temp["placeholder"] = 'Matrícula del propietario';
            $panel["inputs"][] = $temp;

            // Número de identificación
            $temp = array();
            $temp["tipo"] = 'input';
            $temp["mostrar"] = 'SI';
            $temp["protegido"] = 'SI';
            $temp["obligatorio"] = 'NO';
            $temp["encabezado"] = '';
            $temp["label"] = 'Número de identificación';
            $temp["id"] = 'identificacion';
            $temp["name"] = 'identificacion';
            $temp["type"] = 'text';
            $temp["size"] = '11';
            $temp["maxlength"] = '11';
            $temp["minlength"] = '1';
            $temp["value"] = $_SESSION["formulario"]["datos"]["identificacion"];
            $temp["placeholder"] = 'Número de identificación';
            $temp["cssValidator"] = 'numeros';
            $panel["inputs"][] = $temp;
        }

        // tipo identificación
        if ($tipo == 'pnat') {
            $temp = array();
            $temp["tipo"] = 'select';
            $temp["mostrar"] = 'SI';
            $temp["protegido"] = 'SI';
            $temp["obligatorio"] = 'SI';
            $temp["encabezado"] = '';
            $temp["label"] = 'Tipo identificación';
            $temp["id"] = 'tipoidentificacion';
            $temp["name"] = 'tipoidentificacion';
            $temp["type"] = 'text';
            $temp["size"] = '1';
            $temp["value"] = $_SESSION["formulario"]["datos"]["tipoidentificacion"];
            $temp["opc"] = array();
            $temp["opc"][] = array(
                'label' => 'Seleccione ...',
                'val' => '',
                'selected' => ''
            );

            $temx = retornarRegistrosMysqliApi($mysqli, "mreg_tipoidentificacion", "1=1", "id");
            foreach ($temx as $tx) {
                $tsel = '';
                if ($tx["id"] == $_SESSION["formulario"]["datos"]["tipoidentificacion"]) {
                    $tsel = 'SI';
                }
                $temp["opc"][] = array(
                'label' => $tx["id"] . ' - ' . $tx["descripcion"],
                'val' => $tx["id"],
                'selected' => $tsel
            );
            }
            $temp["placeholder"] = '';
            $panel["inputs"][] = $temp;
        }

        // Identificación
        if ($tipo == 'pnat') {
            $temp = array();
            $temp["tipo"] = 'input';
            $temp["mostrar"] = 'SI';
            $temp["protegido"] = 'SI';
            $temp["obligatorio"] = 'SI';
            $temp["encabezado"] = '';
            $temp["label"] = 'Número de identificación';
            $temp["id"] = 'identificacion';
            $temp["name"] = 'identificacion';
            $temp["type"] = 'text';
            $temp["size"] = '20';
            $temp["maxlength"] = '30';
            $temp["minlength"] = '0';
            $temp["value"] = $_SESSION["formulario"]["datos"]["identificacion"];
            $temp["placeholder"] = 'Número de identificación';
            $temp["cssValidator"] = 'numeros';
            $panel["inputs"][] = $temp;
        }

        // Fecha de nacimiento
        if ($tipo == 'pnat') {
            $temp = array();
            $temp["tipo"] = 'input';
            $temp["mostrar"] = 'SI';
            $temp["protegido"] = 'NO';
            $temp["obligatorio"] = 'SI';
            $temp["encabezado"] = '';
            $temp["label"] = 'Fecha de nacimiento';
            $temp["id"] = 'fechanacimiento';
            $temp["name"] = 'fechanacimiento';
            $temp["type"] = 'date';
            $temp["size"] = '10';
            $temp["value"] = (isset($_SESSION["formulario"]["datos"]["fechanacimiento"]) ? $_SESSION["formulario"]["datos"]["fechanacimiento"] : $_SESSION["tramite"]["expedientes"][0]["fechanacimiento"]);
            $temp["value"] = \funcionesGenerales::mostrarFecha($temp["value"]);
            $temp["placeholder"] = 'Fecha de nacimiento';
            $panel["inputs"][] = $temp;
        }

        // Fecha de expedición del documento de identidad
        if ($tipo == 'pnat') {
            $temp = array();
            $temp["tipo"] = 'input';
            $temp["mostrar"] = 'SI';
            $temp["protegido"] = 'NO';
            $temp["obligatorio"] = 'SI';
            $temp["encabezado"] = '';
            $temp["label"] = 'Fecha de exp. del documento de identidad';
            $temp["id"] = 'fecexpdoc';
            $temp["name"] = 'fecexpdoc';
            $temp["type"] = 'date';
            $temp["size"] = '10';
            $temp["value"] = \funcionesGenerales::mostrarFecha($_SESSION["formulario"]["datos"]["fecexpdoc"]);
            $temp["placeholder"] = 'Fecha de expedición del documento de identidad';
            $panel["inputs"][] = $temp;
        }

        // Municipio de expedición
        if ($tipo == 'pnat') {
            $temp = array();
            $temp["tipo"] = 'select';
            $temp["mostrar"] = 'SI';
            $temp["protegido"] = 'NO';
            $temp["obligatorio"] = 'NO';
            $temp["encabezado"] = '';
            $temp["label"] = 'Municipio de exp. del documento de identidad';
            $temp["id"] = 'idmunidoc';
            $temp["name"] = 'idmunidoc';
            $temp["type"] = 'text';
            $temp["size"] = '5';
            $temp["value"] = $_SESSION["formulario"]["datos"]["idmunidoc"];
            $temp["opc"] = array();
            $temp["opc"][] = array(
                'label' => 'Seleccione ...',
                'val' => '',
                'selected' => ''
            );
            $temx = retornarRegistrosMysqliApi($mysqli, "bas_municipios", "1=1", "ciudad");
            foreach ($temx as $tx) {
                $tsel = '';
                if ($tx["codigomunicipio"] == $_SESSION["formulario"]["datos"]["idmunidoc"]) {
                    $tsel = 'SI';
                }
                $temp["opc"][] = array(
                'label' => $tx["ciudad"] . ' (' . substr($tx["departamento"], 0, 3) . ')',
                'val' => $tx["codigomunicipio"],
                'selected' => $tsel
            );
            }
            $temp["placeholder"] = 'Municipio de expedición del documento de identidad';
            $temp["z"] = 'buscarBarrio';
            $panel["inputs"][] = $temp;
        }

        // Pais de expedición
        if ($tipo == 'pnat') {
            if ($_SESSION["formulario"]["datos"]["paisexpdoc"] == '') {
                $_SESSION["formulario"]["datos"]["paisexpdoc"] = '169';
            }
            $temp = array();
            $temp["tipo"] = 'select';
            $temp["mostrar"] = 'SI';
            $temp["protegido"] = 'NO';
            $temp["obligatorio"] = 'NO';
            $temp["encabezado"] = '';
            $temp["label"] = 'País de exp. del documento de identidad';
            $temp["id"] = 'paisexpdoc';
            $temp["name"] = 'paisexpdoc';
            $temp["type"] = 'text';
            $temp["size"] = '5';
            $temp["value"] = $_SESSION["formulario"]["datos"]["paisexpdoc"];
            $temp["opc"] = array();
            $temp["opc"][] = array(
            'label' => 'Seleccione ...',
            'val' => '',
            'selected' => ''
            );
            $temx = retornarRegistrosMysqliApi($mysqli, "bas_paises", "1=1", "nombrepais");
            $tsel = '';
            if ($_SESSION["formulario"]["datos"]["tipoidentificacion"] == '1' ||
                $_SESSION["formulario"]["datos"]["tipoidentificacion"] == '3' ||
                $_SESSION["formulario"]["datos"]["tipoidentificacion"] == '4'
        ) {
                $tsel = 'SI';
            }


            $temp["opc"][] = array(
            'label' => 'COLOMBIA' . ' (169)',
            'val' => '169',
            'selected' => $tsel
        );
            foreach ($temx as $tx) {
                if ($tx["codnumpais"] != '' && $tx["codnumpais"] != '169') {
                    $tsel = '';
                    if ($tx["codnumpais"] == $_SESSION["formulario"]["datos"]["paisexpdoc"]) {
                        $tsel = 'SI';
                    }
                    
                    
                    
                    $temp["opc"][] = array(
                    'label' => mb_strtoupper($tx["nombrepais"],'utf-8') . ' (' . $tx["codnumpais"] . ')',
                    'val' => $tx["codnumpais"],
                    'selected' => $tsel
                );
                }
            }
            $temp["placeholder"] = 'País de expedición del documento de identidad';
            $panel["inputs"][] = $temp;
        }

        // Nacionalidad
        if ($tipo == 'pnat') {
            if ($_SESSION["formulario"]["datos"]["nacionalidad"] == '') {
                $_SESSION["formulario"]["datos"]["nacionalidad"] = 'COLOMBIANO/A';
            }
            $temp = array();
            $temp["tipo"] = 'input';
            $temp["mostrar"] = 'SI';
            $temp["protegido"] = 'NO';
            $temp["obligatorio"] = 'SI';
            $temp["encabezado"] = '';
            $temp["label"] = 'Nacionalidad';
            $temp["id"] = 'nacionalidad';
            $temp["name"] = 'nacionalidad';
            $temp["type"] = 'text';
            $temp["size"] = '30';
            $temp["maxlength"] = '30';
            $temp["minlength"] = '0';
            $temp["value"] = $_SESSION["formulario"]["datos"]["nacionalidad"];
            $temp["placeholder"] = 'Nacionalidad';
            $temp["cssValidator"] = 'letrasunapalabra';
            $panel["inputs"][] = $temp;
        }

        // Nit
        if ($tipo == 'pnat' || $tipo == 'pjur' || $tipo == 'esadl') {
            $temp = array();
            $temp["tipo"] = 'input';
            $temp["mostrar"] = 'SI';
            $temp["protegido"] = 'NO';
            $temp["obligatorio"] = 'SI';
            $temp["label"] = 'N.I.T';
            $temp["id"] = 'nit';
            $temp["name"] = 'nit';
            $temp["type"] = 'text';
            $temp["size"] = '15';
            $temp["maxlength"] = '15';
            $temp["minlength"] = '0';
            $temp["value"] = $_SESSION["formulario"]["datos"]["nit"];
            $temp["placeholder"] = 'N.I.T';
            $temp["cssValidator"] = 'numeros';
            $panel["inputs"][] = $temp;
        }

        // Administración dian
        if ($tipo == 'pnat' || $tipo == 'pjur' || $tipo == 'esadl') {
            $temp = array();
            $temp["tipo"] = 'select';
            $temp["mostrar"] = 'SI';
            $temp["protegido"] = 'NO';
            $temp["obligatorio"] = 'NO';
            $temp["label"] = 'Administración DIAN';
            $temp["id"] = 'admondian';
            $temp["name"] = 'admondian';
            $temp["type"] = 'text';
            $temp["size"] = '20';
            $temp["value"] = $_SESSION["formulario"]["datos"]["admondian"];
            $temx = retornarRegistrosMysqliApi($mysqli, "bas_admindian", "1=1", "id");

            $temp["opc"][] = array(
                'label' => 'Seleccione ...',
                'val' => '',
                'selected' => ''
            );

            foreach ($temx as $tx) {
                $tsel = '';
                if ($tx["id"] == $_SESSION["formulario"]["datos"]["admondian"]) {
                    $tsel = 'SI';
                }
                $temp["opc"][] = array(
                'label' => $tx["id"] . ' - ' . $tx["descripcion"],
                'val' => $tx["id"],
                'selected' => $tsel
            );
            }
            $temp["placeholder"] = 'Administración DIAN';
            $panel["inputs"][] = $temp;
        }

        // Pre rut
        if ($tipo == 'pnat' || $tipo == 'pjur' || $tipo == 'esadl') {
            $temp = array();
            $temp["tipo"] = 'input';
            $temp["mostrar"] = 'SI';
            $temp["protegido"] = 'NO';
            $temp["obligatorio"] = 'NO';
            $temp["label"] = 'Pre RUT';
            $temp["id"] = 'prerut';
            $temp["name"] = 'prerut';
            $temp["type"] = 'text';
            $temp["size"] = '20';
            $temp["maxlength"] = '30';
            $temp["minlength"] = '0';
            $temp["value"] = $_SESSION["formulario"]["datos"]["prerut"];
            $temp["placeholder"] = 'Pre RUT';
            $temp["cssValidator"] = 'numeros';
            $panel["inputs"][] = $temp;
        }

        //
        $_SESSION["jsonsalida"]["formulario"][] = $panel;

        // ********************************************************************** //
        // Nombre y razón social
        // ********************************************************************** //
        $panel = array();
        $panel["col"] = '';
        $panel["offset"] = '';
        $panel["titulo"] = 'NOMBRES, RAZONES SOCIALES Y SIGLAS';
        $panel["texto"] = '';
        $panel["inputs"] = array();

        // Razon social
        if ($tipo == 'pjur' || $tipo == 'esadl') {
            $temp = array();
            $temp["tipo"] = 'textarea';
            $temp["mostrar"] = 'SI';
            $temp["protegido"] = 'NO';
            $temp["obligatorio"] = 'SI';
            $temp["encabezado"] = '';
            $temp["label"] = 'Razón social';
            $temp["id"] = 'nombre';
            $temp["name"] = 'nombre';
            $temp["type"] = 'text';
            $temp["size"] = '200';
            $temp["maxlength"] = '200';
            $temp["minlength"] = '0';
            $temp["value"] = $_SESSION["formulario"]["datos"]["nombre"];
            $temp["placeholder"] = 'Razón social';
            $temp["cssValidator"] = 'validarReferencia';
            $panel["inputs"][] = $temp;
        }

        // sigla
        if ($tipo == 'pjur' || $tipo == 'esadl') {
            $temp = array();
            $temp["tipo"] = 'textarea';
            $temp["mostrar"] = 'SI';
            $temp["protegido"] = 'NO';
            $temp["obligatorio"] = 'NO';
            $temp["encabezado"] = '';
            $temp["label"] = 'Sigla';
            $temp["id"] = 'sigla';
            $temp["name"] = 'sigla';
            $temp["type"] = 'text';
            $temp["size"] = '8';
            $temp["maxlength"] = '200';
            $temp["minlength"] = '0';
            $temp["value"] = $_SESSION["formulario"]["datos"]["sigla"];
            $temp["placeholder"] = 'Sigla';
            $panel["inputs"][] = $temp;
        }

        // Nombre comercial
        if ($tipo == 'esadl') {
            $temp = array();
            $temp["tipo"] = 'textarea';
            $temp["mostrar"] = 'SI';
            $temp["protegido"] = 'NO';
            $temp["obligatorio"] = 'NO';
            $temp["encabezado"] = '';
            $temp["label"] = 'Nombre comercial';
            $temp["id"] = 'nombrecomercial';
            $temp["name"] = 'nombrecomercial';
            $temp["type"] = 'text';
            $temp["size"] = '8';
            $temp["maxlength"] = '200';
            $temp["minlength"] = '0';
            $temp["value"] = $_SESSION["formulario"]["datos"]["nombrecomercial"];
            $temp["placeholder"] = 'Nombre comercial';
            $temp["cssValidator"] = 'validarReferencia';
            $panel["inputs"][] = $temp;
        }

        // Nombre - SUC / AGE
        if ($tipo == 'est' || $tipo == 'suc' || $tipo == 'age') {
            $temp = array();
            $temp["tipo"] = 'textarea';
            $temp["mostrar"] = 'SI';
            $temp["protegido"] = 'NO';
            $temp["obligatorio"] = 'SI';
            $temp["encabezado"] = '';
            $temp["label"] = 'Nombre';
            $temp["id"] = 'nombre';
            $temp["name"] = 'nombre';
            $temp["type"] = 'text';
            $temp["size"] = '200';
            $temp["maxlength"] = '200';
            $temp["minlength"] = '0';
            $temp["value"] = $_SESSION["formulario"]["datos"]["nombre"];
            $temp["placeholder"] = 'Nombre';
            $temp["cssValidator"] = 'validarReferencia';
            $panel["inputs"][] = $temp;
        }

        // Nombre - PNAT
        if ($tipo == 'pnat') {
            $temp = array();
            $temp["tipo"] = 'textarea';
            $temp["mostrar"] = 'SI';
            $temp["protegido"] = 'NO';
            $temp["obligatorio"] = 'SI';
            $temp["encabezado"] = '';
            $temp["label"] = 'Nombre';
            $temp["id"] = 'nombre';
            $temp["name"] = 'nombre';
            $temp["type"] = 'text';
            $temp["size"] = '200';
            $temp["maxlength"] = '200';
            $temp["minlength"] = '0';
            $temp["value"] = $_SESSION["formulario"]["datos"]["nombre"];
            $temp["placeholder"] = 'Nombre';
            $temp["cssValidator"] = 'letras';
            $panel["inputs"][] = $temp;
        }

        // Apellido 1
        if ($tipo == 'pnat') {
            $temp = array();
            $temp["tipo"] = 'input';
            $temp["mostrar"] = 'SI';
            $temp["protegido"] = 'NO';
            $temp["obligatorio"] = 'SI';
            $temp["encabezado"] = '';
            $temp["label"] = 'Primer apellido';
            $temp["id"] = 'ape1';
            $temp["name"] = 'ape1';
            $temp["type"] = 'text';
            $temp["size"] = '50';
            $temp["maxlength"] = '50';
            $temp["minlength"] = '0';
            $temp["value"] = $_SESSION["formulario"]["datos"]["ape1"];
            $temp["placeholder"] = 'Primer apellido';
            $temp["cssValidator"] = 'letras';
            $panel["inputs"][] = $temp;
        }

        // Apellido 2
        if ($tipo == 'pnat') {
            $temp = array();
            $temp["tipo"] = 'input';
            $temp["mostrar"] = 'SI';
            $temp["protegido"] = 'NO';
            $temp["obligatorio"] = 'NO';
            $temp["encabezado"] = '';
            $temp["label"] = 'Segundo apellido';
            $temp["id"] = 'ape2';
            $temp["name"] = 'ape2';
            $temp["type"] = 'text';
            $temp["size"] = '50';
            $temp["maxlength"] = '50';
            $temp["minlength"] = '0';
            $temp["value"] = $_SESSION["formulario"]["datos"]["ape2"];
            $temp["placeholder"] = 'Segundo apellido';
            $temp["cssValidator"] = 'letras';
            $panel["inputs"][] = $temp;
        }

        // Nombre 1
        if ($tipo == 'pnat') {
            $temp = array();
            $temp["tipo"] = 'input';
            $temp["mostrar"] = 'SI';
            $temp["protegido"] = 'NO';
            $temp["obligatorio"] = 'SI';
            $temp["encabezado"] = '';
            $temp["label"] = 'Primer nombre';
            $temp["id"] = 'nom1';
            $temp["name"] = 'nom1';
            $temp["type"] = 'text';
            $temp["size"] = '50';
            $temp["maxlength"] = '50';
            $temp["minlength"] = '0';
            $temp["value"] = $_SESSION["formulario"]["datos"]["nom1"];
            $temp["placeholder"] = 'Primer nombre';
            $temp["cssValidator"] = 'letras';
            $panel["inputs"][] = $temp;
        }

        // Nombre 2
        if ($tipo == 'pnat') {
            $temp = array();
            $temp["tipo"] = 'input';
            $temp["mostrar"] = 'SI';
            $temp["protegido"] = 'NO';
            $temp["obligatorio"] = 'NO';
            $temp["encabezado"] = '';
            $temp["label"] = 'Otros nombres';
            $temp["id"] = 'nom2';
            $temp["name"] = 'nom2';
            $temp["type"] = 'text';
            $temp["size"] = '50';
            $temp["maxlength"] = '50';
            $temp["minlength"] = '0';
            $temp["value"] = $_SESSION["formulario"]["datos"]["nom2"];
            $temp["placeholder"] = 'Otros nombres';
            $temp["cssValidator"] = 'letras';
            $panel["inputs"][] = $temp;
        }

        //
        $_SESSION["jsonsalida"]["formulario"][] = $panel;

        // ************************************************************************************************************ //
        // Identificación del extranjero
        // $_SESSION["formulario"]["datos"]["idetripaiori"]
        // $_SESSION["formulario"]["datos"]["paiori"]
        // $_SESSION["formulario"]["datos"]["idetriextep"]
        // *********************************************************************************************************** //
        if ($tipo == 'pnat') {
            if ($_SESSION["formulario"]["datos"]["tipoidentificacion"] == '3' ||
                $_SESSION["formulario"]["datos"]["tipoidentificacion"] == '5' ||
                $_SESSION["formulario"]["datos"]["tipoidentificacion"] == 'E') {
                $panel = array();
                $panel["col"] = '';
                $panel["offset"] = '';
                $panel["titulo"] = 'IDENTIFICACION EN EL EXTRANJERO';
                $panel["texto"] = '';
                $panel["inputs"] = array();

                $temp = array();
                $temp["tipo"] = 'input';
                $temp["mostrar"] = 'SI';
                $temp["protegido"] = 'NO';
                $temp["obligatorio"] = 'NO';
                $temp["encabezado"] = '';
                $temp["label"] = 'Identificación tributaria en el país de origen';
                $temp["id"] = 'idetripaiori';
                $temp["name"] = 'idetripaiori';
                $temp["type"] = 'text';
                $temp["size"] = '30';
                $temp["maxlength"] = '30';
                $temp["minlength"] = '0';
                $temp["value"] = $_SESSION["formulario"]["datos"]["idetripaiori"];
                $temp["placeholder"] = 'Identificación tributaria en el país de origen';
                $panel["inputs"][] = $temp;

                $temp = array();
                $temp["tipo"] = 'input';
                $temp["mostrar"] = 'SI';
                $temp["protegido"] = 'NO';
                $temp["obligatorio"] = 'NO';
                $temp["encabezado"] = '';
                $temp["label"] = 'Identificación tributaria en el extranjero';
                $temp["id"] = 'idetriextep';
                $temp["name"] = 'idetriextep';
                $temp["type"] = 'text';
                $temp["size"] = '30';
                $temp["maxlength"] = '30';
                $temp["minlength"] = '0';
                $temp["value"] = $_SESSION["formulario"]["datos"]["idetriextep"];
                $temp["placeholder"] = 'Identificación tributaria en el extranjero';
                $panel["inputs"][] = $temp;

                $temp = array();
                $temp["tipo"] = 'select';
                $temp["mostrar"] = 'SI';
                $temp["protegido"] = 'NO';
                $temp["obligatorio"] = 'NO';
                $temp["encabezado"] = '';
                $temp["label"] = 'País de origen';
                $temp["id"] = 'paiori';
                $temp["name"] = 'paiori';
                $temp["type"] = 'text';
                $temp["size"] = '5';
                $temp["value"] = $_SESSION["formulario"]["datos"]["paiori"];
                $temp["opc"] = array();

                $temp["opc"][] = array(
                'label' => 'Seleccione ...',
                'val' => '',
                'selected' => ''
            );

                $temx = retornarRegistrosMysqliApi($mysqli, "bas_paises", "1=1", "nombrepais");
                foreach ($temx as $tx) {
                    if ($tx["codnumpais"] != '') {
                        $tsel = '';
                        if ($tx["codnumpais"] == $_SESSION["formulario"]["datos"]["paiori"]) {
                            $tsel = 'SI';
                        }
                        $temp["opc"][] = array(
                              'label' => mb_strtoupper($tx["nombrepais"],'utf-8'). ' (' . $tx["codnumpais"] . ')',
                        'val' => '0' . $tx["codnumpais"],
                        'selected' => $tsel
                    );
                    }
                }
                $temp["placeholder"] = 'País de origen';
                $panel["inputs"][] = $temp;

                $_SESSION["jsonsalida"]["formulario"][] = $panel;
            }
        }

        // ************************************************************************************************************ //
        // Estado actual de la persona jurídica
        // $_SESSION["formulario"]["datos"]["estadocapturado"]
        // $_SESSION["formulario"]["datos"]["estadocapturadootros"]
        // *********************************************************************************************************** //
        if ($tipo == 'pjur' || $tipo == 'esadl') {
            $panel = array();
            $panel["col"] = '';
            $panel["offset"] = '';
            $panel["titulo"] = 'ESTADO DE LA PERSONA JURIDICA';
            $panel["texto"] = '';
            $panel["inputs"] = array();

            //
            $temp = array();
            $temp["tipo"] = 'select';
            $temp["mostrar"] = 'SI';
            $temp["protegido"] = 'NO';
            $temp["obligatorio"] = 'NO';
            $temp["encabezado"] = '';
            $temp["label"] = 'Estado de la persona jurídica';
            $temp["id"] = 'estadocapturado';
            $temp["name"] = 'estadocapturado';
            $temp["type"] = 'text';
            $temp["size"] = '2';
            $temp["value"] = $_SESSION["formulario"]["datos"]["estadocapturado"];
            $temp["opc"] = array();
            $arr = array();
            $arr['01'] = 'ACTIVA';
            $arr['02'] = 'PREOPERATIVA';
            $arr['03'] = 'EN CONCORDATO';
            $arr['04'] = 'INTERVENIDA';
            $arr['05'] = 'EN LIQUIDACION';
            $arr['06'] = 'ACUERD. REESTRUCTURACION';
            $arr['07'] = 'OTROS';

            $temp["opc"][] = array(
                'label' => 'Seleccione ...',
                'val' => '',
                'selected' => ''
            );

            foreach ($arr as $k => $t) {
                $tsel = '';
                if ($k == $_SESSION["formulario"]["datos"]["estadocapturado"]) {
                    $tsel = 'SI';
                }
                $temp["opc"][] = array(
                'label' => $t,
                'val' => $k,
                'selected' => $tsel
            );
            }
            unset($arr);
            $temp["placeholder"] = 'Estado de la persona jurídica';
            $panel["inputs"][] = $temp;

            // estado captura
            $temp = array();
            $temp["tipo"] = 'input';
            $temp["mostrar"] = 'SI';
            $temp["protegido"] = 'NO';
            $temp["obligatorio"] = 'NO';
            $temp["encabezado"] = '';
            $temp["label"] = 'Cual estado?';
            $temp["id"] = 'estadocapturadootros';
            $temp["name"] = 'estadocapturadootros';
            $temp["type"] = 'text';
            $temp["size"] = '30';
            $temp["maxlength"] = '30';
            $temp["minlength"] = '0';
            $temp["value"] = $_SESSION["formulario"]["datos"]["estadocapturadootros"];
            $temp["placeholder"] = 'Cual estado?';
            $panel["inputs"][] = $temp;

            //
            $_SESSION["jsonsalida"]["formulario"][] = $panel;
        }

        // ************************************************************************************************************ //
        // Datos de ubicación comercial
        // Siempre y cuando no sea reliquidación
        // *********************************************************************************************************** //
        if ($_SESSION["formulario"]["reliquidacion"] != 'si') {
            $panel = array();
            $panel["col"] = '';
            $panel["offset"] = '';
            $panel["titulo"] = 'UBICACION COMERCIAL';
            $panel["texto"] = '';

            $panel["avisotop"] = array();
            $panel["avisotop"]["texto"] = ''
                . '!!! IMPORTANTE ¡¡¡ Señor usuario recuerde que si al momento de realizar su renovación modifica '
                . 'la dirección comercial debe tener en cuenta lo prohibido, restringido y permitido por el respectivo '
                . 'municipio en cuanto al Uso de Suelo donde funcionará su empresa o negocio. Para mayor información al '
                . 'respecto comuniquese con la Cámara de Comercio y/o con la Alcaldía Municipal - Secretaria de Planeación, '
                . 'para que le informen adecuadamente sobre este requerimiento.';

            $panel["avisotop"]["color"] = 'warning';

            $panel["inputs"] = array();

            // Dirección
            $temp = array();
            $temp["tipo"] = 'textarea';
            $temp["mostrar"] = 'SI';
            $temp["protegido"] = 'NO';
            $temp["obligatorio"] = 'SI';
            $temp["encabezado"] = '';
            $temp["label"] = 'Dirección comercial';
            $temp["id"] = 'dircom';
            $temp["name"] = 'dircom';
            $temp["type"] = 'text';
            $temp["size"] = '200';
            $temp["maxlength"] = '200';
            $temp["minlength"] = '0';
            $temp["value"] = $_SESSION["formulario"]["datos"]["dircom"];
            $temp["placeholder"] = 'Dirección comercial';
            $temp["cssValidator"] = 'validarDireccion';
            $panel["inputs"][] = $temp;

            // Pais comercial
            if (trim($_SESSION["formulario"]["datos"]["paicom"]) == '') {
                $_SESSION["formulario"]["datos"]["paicom"] = '169';
            }

            //
            if (trim($_SESSION["formulario"]["datos"]["paicom"]) == 'CO') {
                $_SESSION["formulario"]["datos"]["paicom"] = '169';
            }
            if (strlen($_SESSION["formulario"]["datos"]["paicom"]) == 4) {
                $paicom = substr($_SESSION["formulario"]["datos"]["paicom"], 1);
            } else {
                $paicom = $_SESSION["formulario"]["datos"]["paicom"];
            }
            //
            $temp = array();
            $temp["tipo"] = 'select';
            $temp["mostrar"] = 'SI';
            $temp["protegido"] = 'NO';
            $temp["obligatorio"] = 'SI';
            $temp["encabezado"] = '';
            $temp["label"] = 'País';
            $temp["id"] = 'paicom';
            $temp["name"] = 'paicom';
            $temp["type"] = 'text';
            $temp["size"] = '5';
            $temp["value"] = $paicom;
            $temp["opc"] = array();
            $temx = retornarRegistrosMysqliApi($mysqli, "bas_paises", "1=1", "nombrepais");
            $temp["opc"][] = array(
            'label' => 'Seleccione ...',
            'val' => '',
            'selected' => ''
        );
            foreach ($temx as $tx) {
                if ($tx["codnumpais"] != '') {
                    $tsel = '';
                    if ($tx["codnumpais"] == $paicom) {
                        $tsel = 'SI';
                    }
                    $temp["opc"][] = array(
                          'label' => mb_strtoupper($tx["nombrepais"],'utf-8') . ' (' . $tx["codnumpais"] . ')',
                                'val' => $tx["codnumpais"],
                                'selected' => $tsel
                            );
                }
            }
            $temp["z"] = 'buscarMunicipio';
            $temp["modifica"] = 'muncom';
            $temp["placeholder"] = 'País';
            $panel["inputs"][] = $temp;

            // Municipio comercial
            $temp = array();
            $temp["tipo"] = 'select';
            $temp["mostrar"] = 'SI';
            $temp["protegido"] = 'NO';
            $temp["obligatorio"] = 'NO';
            $temp["encabezado"] = '';
            $temp["label"] = 'Municipio';
            $temp["id"] = 'muncom';
            $temp["name"] = 'muncom';
            $temp["type"] = 'text';
            $temp["size"] = '5';
            $temp["value"] = $_SESSION["formulario"]["datos"]["muncom"];
            $temp["opc"] = array();
            $temp["opc"][] = array(
            'label' => 'Seleccione ...',
            'val' => '',
            'selected' => ''
        );
            $temx = retornarRegistrosMysqliApi($mysqli, "mreg_municipiosjurisdiccion", "1=1", "idcodigo");
            foreach ($temx as $tx) {
                $tsel = '';
                if ($tx["idcodigo"] == $_SESSION["formulario"]["datos"]["muncom"]) {
                    $tsel = 'SI';
                }
                $mun = retornarRegistroMysqliApi($mysqli, "bas_municipios", "codigomunicipio='" . $tx["idcodigo"] . "'");
                $temp["opc"][] = array(
                            'label' => $mun["codigomunicipio"] . ' - ' . $mun["ciudad"] . ' (' . $mun["departamento"] . ')',
                            'val' => $tx["idcodigo"],
                            'selected' => $tsel
                        );
            }
            $temp["placeholder"] = 'Municipio';
            $temp["z"] = 'buscarBarrio';
            $temp["modifica"] = 'barriocom';
            $panel["inputs"][] = $temp;

            // Barrio comercial
            $temp = array();
            $temp["tipo"] = 'select';
            $temp["mostrar"] = 'SI';
            $temp["protegido"] = 'NO';
            $temp["obligatorio"] = 'NO';
            $temp["encabezado"] = '';
            $temp["label"] = 'Barrio';
            $temp["id"] = 'barriocom';
            $temp["name"] = 'barriocom';
            $temp["type"] = 'text';
            $temp["size"] = '5';
            $temp["value"] = $_SESSION["formulario"]["datos"]["barriocom"];
            $temp["opc"] = array();
            $temp["opc"][] = array(
            'label' => 'Seleccione ...',
            'val' => '',
            'selected' => ''
        );
            $temx = retornarRegistrosMysqliApi($mysqli, "mreg_barriosmuni", "idmunicipio='".$_SESSION["formulario"]["datos"]["muncom"]."'", "nombre");
            foreach ($temx as $tx) {
                $tsel = '';
                if ($tx["idmunicipio"] == $_SESSION["formulario"]["datos"]["muncom"] &&
                                $tx["idbarrio"] == $_SESSION["formulario"]["datos"]["barriocom"]) {
                    $tsel = 'SI';
                }
                $temp["opc"][] = array(
                            'label' => $tx["nombre"],
                            'val' => $tx["idbarrio"],
                            'selected' => $tsel
                        );
            }
            $temp["placeholder"] = 'Barrio';
            $panel["inputs"][] = $temp;

            // Teléfono No. 1
            $temp = array();
            $temp["tipo"] = 'input';
            $temp["mostrar"] = 'SI';
            $temp["protegido"] = 'NO';
            $temp["obligatorio"] = 'SI';
            $temp["encabezado"] = '';
            $temp["label"] = 'Teléfono No. 1';
            $temp["id"] = 'telcom1';
            $temp["name"] = 'telcom1';
            $temp["type"] = 'text';
            $temp["size"] = '10';
            $temp["maxlength"] = '10';
            $temp["minlength"] = '7';
            $temp["value"] = $_SESSION["formulario"]["datos"]["telcom1"];
            $temp["placeholder"] = 'Teléfono No. 1';
            $temp["cssValidator"] = 'numeros';
            $panel["inputs"][] = $temp;

            // Teléfono No. 2
            $temp = array();
            $temp["tipo"] = 'input';
            $temp["mostrar"] = 'SI';
            $temp["protegido"] = 'NO';
            $temp["obligatorio"] = 'NO';
            $temp["encabezado"] = '';
            $temp["label"] = 'Teléfono No. 2';
            $temp["id"] = 'telcom2';
            $temp["name"] = 'telcom2';
            $temp["type"] = 'text';
            $temp["size"] = '10';
            $temp["maxlength"] = '10';
            $temp["minlength"] = '7';
            $temp["value"] = $_SESSION["formulario"]["datos"]["telcom2"];
            $temp["placeholder"] = 'Teléfono No. 2';
            $temp["cssValidator"] = 'numeros';
            $panel["inputs"][] = $temp;

            // Teléfono No. 3
            $temp = array();
            $temp["tipo"] = 'input';
            $temp["mostrar"] = 'SI';
            $temp["protegido"] = 'NO';
            $temp["obligatorio"] = 'NO';
            $temp["encabezado"] = '';
            $temp["label"] = 'Teléfono No. 3';
            $temp["id"] = 'celcom';
            $temp["name"] = 'celcom';
            $temp["type"] = 'text';
            $temp["size"] = '10';
            $temp["maxlength"] = '10';
            $temp["minlength"] = '7';
            $temp["value"] = $_SESSION["formulario"]["datos"]["celcom"];
            $temp["placeholder"] = 'Teléfono No. 3';
            $temp["cssValidator"] = 'numeros';
            $panel["inputs"][] = $temp;

            // Fax
            $temp = array();
            $temp["tipo"] = 'input';
            $temp["mostrar"] = 'SI';
            $temp["protegido"] = 'NO';
            $temp["obligatorio"] = 'NO';
            $temp["encabezado"] = '';
            $temp["label"] = 'Número de fax';
            $temp["id"] = 'faxcom';
            $temp["name"] = 'faxcom';
            $temp["type"] = 'text';
            $temp["size"] = '15';
            $temp["maxlength"] = '15';
            $temp["minlength"] = '5';
            $temp["value"] = $_SESSION["formulario"]["datos"]["faxcom"];
            $temp["placeholder"] = 'Número de fax';
            $temp["cssValidator"] = 'numeros';
            $panel["inputs"][] = $temp;

            // Zona de ubicación
            $temp = array();
            $temp["tipo"] = 'select';
            $temp["mostrar"] = 'SI';
            $temp["protegido"] = 'NO';
            $temp["obligatorio"] = 'SI';
            $temp["encabezado"] = '';
            $temp["label"] = 'Zona';
            $temp["id"] = 'codigozonacom';
            $temp["name"] = 'codigozonacom';
            $temp["type"] = 'text';
            $temp["size"] = '1';
            $temp["value"] = $_SESSION["formulario"]["datos"]["codigozonacom"];
            $temp["opc"] = '';
            $arr = array();
            $arr["U"] = "URBANA";
            $arr["R"] = "RURAL";

            $temp["opc"][] = array(
                'label' => 'Seleccione ...',
                'val' => '',
                'selected' => ''
            );

            foreach ($arr as $k => $v) {
                $tsel = '';
                if ($k == $_SESSION["formulario"]["datos"]["codigozonacom"]) {
                    $tsel = 'SI';
                }
                $temp["opc"][] = array(
                'label' => $v,
                'val' => $k,
                'selected' => $tsel
            );
            }
            unset($arr);
            $temp["placeholder"] = 'Zona de ubicación';
            $panel["inputs"][] = $temp;

            // Código postal
            $temp = array();
            $temp["tipo"] = 'input';
            $temp["mostrar"] = 'SI';
            $temp["protegido"] = 'NO';
            $temp["obligatorio"] = 'NO';
            $temp["encabezado"] = '';
            $temp["label"] = 'Código Postal';
            $temp["id"] = 'codigopostalcom';
            $temp["name"] = 'codigopostalcom';
            $temp["type"] = 'text';
            $temp["size"] = '6';
            $temp["maxlength"] = '15';
            $temp["minlength"] = '0';
            $temp["value"] = $_SESSION["formulario"]["datos"]["codigopostalcom"];
            $temp["placeholder"] = 'Código Postal';
            $temp["cssValidator"] = 'numeros';
            $panel["inputs"][] = $temp;

            // Número predial
            $temp = array();
            $temp["tipo"] = 'input';
            $temp["mostrar"] = 'SI';
            $temp["protegido"] = 'NO';
            $temp["obligatorio"] = 'NO';
            $temp["encabezado"] = '';
            $temp["label"] = 'Número predial';
            $temp["id"] = 'numpredial';
            $temp["name"] = 'numpredial';
            $temp["type"] = 'text';
            $temp["size"] = '25';
            $temp["maxlength"] = '30';
            $temp["minlength"] = '0';
            $temp["value"] = $_SESSION["formulario"]["datos"]["numpredial"];
            $temp["placeholder"] = 'Número predial';
            $temp["cssValidator"] = 'numeros';
            $panel["inputs"][] = $temp;

            // Email comercial
            $temp = array();
            $temp["tipo"] = 'input';
            $temp["mostrar"] = 'SI';
            $temp["protegido"] = 'NO';
            $temp["obligatorio"] = 'SI';
            $temp["encabezado"] = '';
            $temp["label"] = 'Correo electrónico';
            $temp["id"] = 'emailcom';
            $temp["name"] = 'emailcom';
            $temp["type"] = 'email';
            $temp["size"] = '50';
            $temp["maxlength"] = '50';
            $temp["minlength"] = '0';
            $temp["value"] = $_SESSION["formulario"]["datos"]["emailcom"];
            $temp["placeholder"] = 'Correo electrónico';
            $temp["cssValidator"] = 'validarEmail';
            $panel["inputs"][] = $temp;

            // Ubicación de la empresa
            $temp = array();
            $temp["tipo"] = 'select';
            $temp["mostrar"] = 'SI';
            $temp["protegido"] = 'NO';
            $temp["obligatorio"] = 'SI';
            $temp["encabezado"] = '';
            $temp["label"] = 'Ubicación';
            $temp["id"] = 'ctrubi';
            $temp["name"] = 'ctrubi';
            $temp["type"] = 'text';
            $temp["size"] = '1';
            $temp["value"] = $_SESSION["formulario"]["datos"]["ctrubi"];
            $temp["opc"]=array();

            $temp["opc"][] = array(
                'label' => 'Seleccione ...',
                'val' => '',
                'selected' => ''
            );

            $temx = retornarRegistrosMysqliApi($mysqli, "mreg_ubicacion", "1=1");
            foreach ($temx as $tx) {
                $tsel = '';
                if ($tx["id"] == $_SESSION["formulario"]["datos"]["ctrubi"]) {
                    $tsel = 'SI';
                }
                $temp["opc"][] = array(
                    'label' => mb_strtoupper($tx["descripcion"], 'utf-8'),
                    'val' => $tx["id"],
                    'selected' => $tsel
                );
            }
            unset($temx);
            $temp["placeholder"] = '';
            $panel["inputs"][] = $temp;


            //
            $_SESSION["jsonsalida"]["formulario"][] = $panel;
        }

        // ************************************************************************************************************ //
        // Datos de notificación
        // Siempre y cuando no sea reliquidacion
        // *********************************************************************************************************** //
        if ($_SESSION["formulario"]["reliquidacion"] != 'si') {
            if ($tipo != 'est' && $tipo != 'age') {
                $panel = array();
                $panel["col"] = '';
                $panel["offset"] = '';
                $panel["titulo"] = 'NOTIFICACION JUDICIAL';
                $panel["texto"] = '';
                $panel["inputs"] = array();

                // Dirección
                $temp = array();
                $temp["tipo"] = 'textarea';
                $temp["mostrar"] = 'SI';
                $temp["protegido"] = 'NO';
                $temp["obligatorio"] = 'SI';
                $temp["encabezado"] = '';
                $temp["label"] = 'Dirección de notificación';
                $temp["id"] = 'dirnot';
                $temp["name"] = 'dirnot';
                $temp["type"] = 'text';
                $temp["size"] = '256';
                $temp["maxlength"] = '300';
                $temp["minlength"] = '0';
                $temp["value"] = $_SESSION["formulario"]["datos"]["dirnot"];
                $temp["placeholder"] = 'Dirección de notificación';
                $temp["cssValidator"] = 'validarDireccion';
                $panel["inputs"][] = $temp;

                // Pais de notificacion
                if (trim($_SESSION["formulario"]["datos"]["painot"]) == '') {
                    $_SESSION["formulario"]["datos"]["painot"] = '169';
                }
                if (trim($_SESSION["formulario"]["datos"]["painot"]) == 'CO') {
                    $_SESSION["formulario"]["datos"]["painot"] = '169';
                }
                if (strlen($_SESSION["formulario"]["datos"]["painot"]) == 4) {
                    $painot = substr($_SESSION["formulario"]["datos"]["painot"], 1);
                } else {
                    $painot = $_SESSION["formulario"]["datos"]["painot"];
                }

                $temp = array();
                $temp["tipo"] = 'select';
                $temp["mostrar"] = 'SI';
                $temp["protegido"] = 'NO';
                $temp["obligatorio"] = 'SI';
                $temp["encabezado"] = '';
                $temp["label"] = 'País';
                $temp["id"] = 'painot';
                $temp["name"] = 'painot';
                $temp["type"] = 'text';
                $temp["size"] = '5';
                $temp["value"] = $painot;
                $temp["opc"] = array();
                $temx = retornarRegistrosMysqliApi($mysqli, "bas_paises", "1=1", "nombrepais");
                $temp["opc"][] = array(
                'label' => 'Seleccione ...',
                'val' => '',
                'selected' => ''
            );
                foreach ($temx as $tx) {
                    if ($tx["codnumpais"] != '') {
                        $tsel = '';
                        if ($tx["codnumpais"] == $painot) {
                            $tsel = 'SI';
                        }
                        $temp["opc"][] = array(
                                        'label' => mb_strtoupper($tx["nombrepais"],'utf-8') . ' (' . $tx["codnumpais"] . ')',
                                        'val' => $tx["codnumpais"],
                                        'selected' => $tsel
                                    );
                    }
                }
                $temp["z"] = 'buscarMunicipio';
                $temp["modifica"] = 'munnot';
                $temp["placeholder"] = 'País';
                $panel["inputs"][] = $temp;

                // Municipio de notificacion
                $temp = [];
                $temp["tipo"] = 'select';
                $temp["mostrar"] = 'SI';
                $temp["protegido"] = 'NO';
                $temp["obligatorio"] = 'NO';
                $temp["encabezado"] = '';
                $temp["label"] = 'Municipio';
                $temp["id"] = 'munnot';
                $temp["name"] = 'munnot';
                $temp["type"] = 'text';
                $temp["size"] = '5';
                $temp["value"] = $_SESSION["formulario"]["datos"]["muncom"];
                $temp["opc"] = array();
                $temp["opc"][] = array(
                'label' => 'Seleccione ...',
                'val' => '',
                'selected' => ''
            );
                $temx = retornarRegistrosMysqliApi($mysqli, "bas_municipios", "1=1", "ciudad");
                foreach ($temx as $tx) {
                    $tsel = '';
                    if ($tx["codigomunicipio"] == $_SESSION["formulario"]["datos"]["munnot"]) {
                        $tsel = 'SI';
                    }
                    $temp["opc"][] = array(
                                    'label' => $tx["codigomunicipio"] . ' - ' . $tx["ciudad"] . ' (' . $tx["departamento"] . ')',
                                    'val' => $tx["codigomunicipio"],
                                    'selected' => $tsel
                                );
                }
                $temp["placeholder"] = 'Municipio';
                $temp["z"] = 'buscarBarrio';
                $temp["modifica"] = 'barrionot';
                $panel["inputs"][] = $temp;

                // Barrio de notificacion
                $temp = array();
                $temp["tipo"] = 'select';
                $temp["mostrar"] = 'SI';
                $temp["protegido"] = 'NO';
                $temp["obligatorio"] = 'NO';
                $temp["encabezado"] = '';
                $temp["label"] = 'Barrio';
                $temp["id"] = 'barrionot';
                $temp["name"] = 'barrionot';
                $temp["type"] = 'text';
                $temp["size"] = '5';
                $temp["value"] = $_SESSION["formulario"]["datos"]["barrionot"];
                $temp["opc"] = array();
                $temp["opc"][] = array(
                'label' => 'Seleccione ...',
                'val' => '',
                'selected' => ''
            );
                $temx = retornarRegistrosMysqliApi($mysqli, "mreg_barriosmuni", "idmunicipio='".$_SESSION["formulario"]["datos"]["muncom"]."'", "nombre");
                foreach ($temx as $tx) {
                    $tsel = '';
                    if ($tx["idmunicipio"] == $_SESSION["formulario"]["datos"]["munnot"] &&
                              $tx["idbarrio"] == $_SESSION["formulario"]["datos"]["barrionot"]) {
                        $tsel = 'SI';
                    }
                    $temp["opc"][] = array(
                                'label' => $tx["nombre"],
                                'val' => $tx["idbarrio"],
                                'selected' => $tsel
                              );
                }
                $temp["placeholder"] = 'Barrio';
                $panel["inputs"][] = $temp;

                // Teléfono No. 1
                $temp = array();
                $temp["tipo"] = 'input';
                $temp["mostrar"] = 'SI';
                $temp["protegido"] = 'NO';
                $temp["obligatorio"] = 'SI';
                $temp["encabezado"] = '';
                $temp["label"] = 'Teléfono No. 1';
                $temp["id"] = 'telnot';
                $temp["name"] = 'telnot';
                $temp["type"] = 'text';
                $temp["size"] = '10';
                $temp["maxlength"] = '10';
                $temp["minlength"] = '7';
                $temp["value"] = $_SESSION["formulario"]["datos"]["telnot"];
                $temp["placeholder"] = 'Teléfono No. 1';
                $temp["cssValidator"] = 'numeros';
                $panel["inputs"][] = $temp;

                // Teléfono No. 2
                $temp = array();
                $temp["tipo"] = 'input';
                $temp["mostrar"] = 'SI';
                $temp["protegido"] = 'NO';
                $temp["obligatorio"] = 'NO';
                $temp["encabezado"] = '';
                $temp["label"] = 'Teléfono No. 2';
                $temp["id"] = 'telnot2';
                $temp["name"] = 'telnot2';
                $temp["type"] = 'text';
                $temp["size"] = '10';
                $temp["maxlength"] = '10';
                $temp["minlength"] = '7';
                $temp["value"] = $_SESSION["formulario"]["datos"]["telnot2"];
                $temp["placeholder"] = 'Teléfono No. 2';
                $temp["cssValidator"] = 'numeros';
                $panel["inputs"][] = $temp;

                // Teléfono No. 3
                $temp = array();
                $temp["tipo"] = 'input';
                $temp["mostrar"] = 'SI';
                $temp["protegido"] = 'NO';
                $temp["obligatorio"] = 'NO';
                $temp["encabezado"] = '';
                $temp["label"] = 'Teléfono No. 3';
                $temp["id"] = 'celnot';
                $temp["name"] = 'celnot';
                $temp["type"] = 'text';
                $temp["size"] = '10';
                $temp["maxlength"] = '10';
                $temp["minlength"] = '7';
                $temp["value"] = $_SESSION["formulario"]["datos"]["celnot"];
                $temp["placeholder"] = 'Teléfono No. 3';
                $temp["cssValidator"] = 'numeros';
                $panel["inputs"][] = $temp;

                // Fax
                $temp = array();
                $temp["tipo"] = 'input';
                $temp["mostrar"] = 'SI';
                $temp["protegido"] = 'NO';
                $temp["obligatorio"] = 'NO';
                $temp["encabezado"] = '';
                $temp["label"] = 'Número de fax';
                $temp["id"] = 'faxnot';
                $temp["name"] = 'faxnot';
                $temp["type"] = 'text';
                $temp["size"] = '15';
                $temp["maxlength"] = '15';
                $temp["minlength"] = '7';
                $temp["value"] = $_SESSION["formulario"]["datos"]["faxnot"];
                $temp["placeholder"] = 'Número de fax';
                $temp["cssValidator"] = 'numeros';
                $panel["inputs"][] = $temp;

                // Zona de ubicación
                $temp = array();
                $temp["tipo"] = 'select';
                $temp["mostrar"] = 'SI';
                $temp["protegido"] = 'NO';
                $temp["obligatorio"] = 'SI';
                $temp["encabezado"] = '';
                $temp["label"] = 'Zona de ubicación';
                $temp["id"] = 'codigozonanot';
                $temp["name"] = 'codigozonanot';
                $temp["type"] = 'text';
                $temp["size"] = '1';
                $temp["value"] = $_SESSION["formulario"]["datos"]["codigozonanot"];
                $temp["opc"] = array();

                $arr = array();
                $arr["U"] = "URBANA";
                $arr["R"] = "RURAL";


                $temp["opc"][] = array(
                'label' => 'Seleccione ...',
                'val' => '',
                'selected' => ''
            );

                foreach ($arr as $k => $v) {
                    $tsel = '';
                    if ($k == $_SESSION["formulario"]["datos"]["codigozonanot"]) {
                        $tsel = 'SI';
                    }
                    $temp["opc"][] = array(
                    'label' => $v,
                    'val' => $k,
                    'selected' => $tsel
                );
                }
                unset($arr);
                $temp["placeholder"] = 'Zona de ubicación';
                $panel["inputs"][] = $temp;

                // Código postal
                $temp = array();
                $temp["tipo"] = 'input';
                $temp["mostrar"] = 'SI';
                $temp["protegido"] = 'NO';
                $temp["obligatorio"] = 'NO';
                $temp["encabezado"] = '';
                $temp["label"] = 'Código Postal';
                $temp["id"] = 'codigopostalnot';
                $temp["name"] = 'codigopostalnot';
                $temp["type"] = 'text';
                $temp["size"] = '6';
                $temp["maxlength"] = '30';
                $temp["minlength"] = '0';
                $temp["value"] = $_SESSION["formulario"]["datos"]["codigopostalnot"];
                $temp["placeholder"] = 'Código Postal';
                $temp["cssValidator"] = 'numeros';
                $panel["inputs"][] = $temp;

                // Email de notificación
                $temp = array();
                $temp["tipo"] = 'input';
                $temp["mostrar"] = 'SI';
                $temp["protegido"] = 'NO';
                $temp["obligatorio"] = 'SI';
                $temp["encabezado"] = '';
                $temp["label"] = 'Correo electrónico';
                $temp["id"] = 'emailnot';
                $temp["name"] = 'emailnot';
                $temp["type"] = 'email';
                $temp["size"] = 'Correo electrónico';
                $temp["maxlength"] = '50';
                $temp["minlength"] = '0';
                $temp["value"] = $_SESSION["formulario"]["datos"]["emailnot"];
                $temp["placeholder"] = 'Correo electrónico';
                $temp["cssValidator"] = 'validarEmail';
                $panel["inputs"][] = $temp;

                //
                $_SESSION["jsonsalida"]["formulario"][] = $panel;
            }
        }

        // ************************************************************************************************************ //
        // Tipo de sede y autorizaciones
        // *********************************************************************************************************** //
        if ($_SESSION["formulario"]["reliquidacion"] != 'si') {
            if ($tipo != 'est' && $tipo != 'age') {
                $panel = array();
                $panel["titulo"] = 'TIPO DE SEDE Y AUTORIZACIONES';
                $panel["texto"] = '';
                $panel["col"] = '6';
                $panel["offset"] = '';
                $panel["inputs"] = array();

                // Tipo de sede
                $temp = array();
                $temp["tipo"] = 'select';
                $temp["mostrar"] = 'SI';
                $temp["protegido"] = 'NO';
                $temp["obligatorio"] = 'SI';
                $temp["encabezado"] = '';
                $temp["label"] = 'Sede administrativa';
                $temp["id"] = 'tiposedeadm';
                $temp["name"] = 'tiposedeadm';
                $temp["type"] = 'text';
                $temp["size"] = '5';
                $temp["value"] = $_SESSION["formulario"]["datos"]["tiposedeadm"];
                $temp¨["opc"] = array();


                $temp["opc"][] = array(
                'label' => 'Seleccione ...',
                'val' => '',
                'selected' => ''
            );


                $arr = array(
                '0' => 'NO REPORTO',
                '1' => 'PROPIA',
                '2' => 'ARRIENDO',
                '3' => 'COMODATO',
                '4' => 'PRÉSTAMO'
            );
                foreach ($arr as $k => $v) {
                    $tsel = '';
                    if ($k == $_SESSION["formulario"]["datos"]["tiposedeadm"]) {
                        $tsel = 'SI';
                    }
                    $temp["opc"][] = array(
                    'label' => $v,
                    'val' => $k,
                    'selected' => $tsel
                );
                }
                $temp["placeholder"] = 'Sede administrativa';
                $panel["inputs"][] = $temp;

                // Autorizacion email
                $temp = array();
                $temp["tipo"] = 'select';
                $temp["mostrar"] = 'SI';
                $temp["protegido"] = 'NO';
                $temp["obligatorio"] = 'SI';
                $temp["encabezado"] = 'De conformidad con lo establecido en el artículo 67 del Código de Procedimiento Administrativo y de lo Contencioso Administrativo, autorizo para que '
                    . 'me comuniquen y notifiquen personalmente a través del correo electrónico aquí especificado (Correo electrónico para notificaciones judiciales).';
                $temp["label"] = 'Autoriza envío de mensajes al correo electrónico';
                $temp["id"] = 'ctrmennot';
                $temp["name"] = 'ctrmennot';
                $temp["type"] = 'text';
                $temp["size"] = '5';
                $temp["value"] = $_SESSION["formulario"]["datos"]["ctrmennot"];
                $temp¨["opc"] = array();


                $temp["opc"][] = array(
                'label' => 'Seleccione ...',
                'val' => '',
                'selected' => ''
            );

                $arr = array(
                'SI' => 'SI',
                'NO' => 'NO'
            );
                foreach ($arr as $k => $v) {
                    $tsel = '';
                    if ($k == $_SESSION["formulario"]["datos"]["ctrmennot"]) {
                        $tsel = 'SI';
                    }
                    $temp["opc"][] = array(
                    'label' => $v,
                    'val' => $k,
                    'selected' => $tsel
                );
                }
                $temp["placeholder"] = 'Autoriza envío de mensajes al correo electrónico';
                $panel["inputs"][] = $temp;

                $_SESSION["jsonsalida"]["formulario"][] = $panel;
            }
        }


        // ************************************************************************************************************ //
        // Actividad económica
        // *********************************************************************************************************** //
        if ($_SESSION["formulario"]["reliquidacion"] != 'si') {
            $panel = array();
            $panel["titulo"] = 'ACTIVIDAD ECONÓMICA';
            $panel["texto"] = '';
            $panel["col"] = '6';
            $panel["offset"] = '';
            $panel["inputs"] = array();

            if ($tipo == 'pnat') {
                // Descripción de la actividad económica
                $temp = array();
                $temp["tipo"] = 'textarea';
                $temp["mostrar"] = 'SI';
                $temp["protegido"] = 'NO';
                $temp["obligatorio"] = 'SI';
                $temp["encabezado"] = 'Por favor describa en forma resumida la actividad que usted realiza, indicando que tipo de productos fabrica o '
                    . 'comercializa o que tipo de servicios presta. Utilice máximo 1000 caracteres';
                $temp["label"] = 'Descripción de la actividad económica';
                $temp["id"] = 'desactiv';
                $temp["name"] = 'desactiv';
                $temp["type"] = 'text';
                $temp["size"] = '1000';
                $temp["maxlength"] = '1000';
                $temp["minlength"] = '0';
                $temp["value"] = $_SESSION["formulario"]["datos"]["desactiv"];
                $temp["placeholder"] = 'Descripción de la actividad económica';
                $temp["cssValidator"] = 'validarCaracteres';
                $panel["inputs"][] = $temp;
            }

            if ($tipo == 'est') {
                // Descripción de la actividad económica
                $temp = array();
                $temp["tipo"] = 'textarea';
                $temp["mostrar"] = 'SI';
                $temp["protegido"] = 'NO';
                $temp["obligatorio"] = 'SI';
                $temp["encabezado"] = 'Por favor describa en forma resumida la actividad que se realiza en el establecimiento, sucursal o agencia. Utilice máximo 500 caracteres';
                $temp["label"] = 'Descripción de la actividad económica';
                $temp["id"] = 'desactiv';
                $temp["name"] = 'desactiv';
                $temp["type"] = 'text';
                $temp["size"] = '500';
                $temp["maxlength"] = '1000';
                $temp["minlength"] = '0';
                $temp["value"] = $_SESSION["formulario"]["datos"]["desactiv"];
                $temp["placeholder"] = 'Descripción de la actividad económica';
                $temp["cssValidator"] = 'validarCaracteres';
                $panel["inputs"][] = $temp;
            }

            $arrCiiu = array();
            $temx = retornarRegistrosMysqliApi($mysqli, "bas_ciius", "1=1", "idciiu");
            foreach ($temx as $tx) {
                $arrCiiu[$tx["idciiu"]] = $tx["descripcion"];
            }
            unset($temx);


            // Código CIIU principal
            $temp = array();
            $temp["tipo"] = 'input';
            $temp["mostrar"] = 'SI';
            $temp["protegido"] = 'SI';
            $temp["obligatorio"] = 'SI';
            $temp["encabezado"] = '';
            $temp["label"] = 'Código de actividad económica principal';
            $temp["id"] = 'ciiu1';
            $temp["name"] = 'ciiu1';
            $temp["type"] = 'text';
            $temp["size"] = '5';
            $temp["maxlength"] = '30';
            $temp["minlength"] = '0';
            $temp["value"] = $_SESSION["formulario"]["datos"]["ciius"][1];
            if (isset($arrCiiu[$temp["value"]])) {
                $temp["txtvalue"] = mb_strtoupper($arrCiiu[$temp["value"]], 'utf-8');
            } else {
                $temp["txtvalue"] = '';
            }
            $temp["placeholder"] = 'Código de actividad económica principal';
            $temp["cssValidator"] = 'validarCaracteres';
            $panel["inputs"][] = $temp;

            if ($tipo == 'pnat' || $tipo == 'pjur' || $tipo == 'esadl') {
                // Fecha de inicio de la actividad principal
                $temp = array();
                $temp["tipo"] = 'input';
                $temp["mostrar"] = 'SI';
                $temp["protegido"] = 'NO';
                $temp["obligatorio"] = 'SI';
                $temp["encabezado"] = '';
                $temp["label"] = 'Fecha de inicio de actividad principal';
                $temp["id"] = 'feciniact1';
                $temp["name"] = 'feciniact1';
                $temp["type"] = 'date';
                $temp["size"] = '10';
                $temp["value"] = \funcionesGenerales::mostrarFecha($_SESSION["formulario"]["datos"]["feciniact1"]);
                $temp["placeholder"] = 'Fecha de inicio de actividad principal';
                $panel["inputs"][] = $temp;
            }


            // Código CIIU secundario
            $temp = array();
            $temp["tipo"] = 'input';
            $temp["mostrar"] = 'SI';
            $temp["protegido"] = 'SI';
            $temp["obligatorio"] = 'NO';
            $temp["encabezado"] = '';
            $temp["label"] = 'Código de actividad económica secundaria';
            $temp["id"] = 'ciiu2';
            $temp["name"] = 'ciiu2';
            $temp["type"] = 'text';
            $temp["size"] = '5';
            $temp["maxlength"] = '30';
            $temp["minlength"] = '0';
            $temp["value"] = $_SESSION["formulario"]["datos"]["ciius"][2];
            if (isset($arrCiiu[$temp["value"]])) {
                $temp["txtvalue"] = mb_strtoupper($arrCiiu[$temp["value"]], 'utf-8');
            } else {
                $temp["txtvalue"] = '';
            }
            $temp["placeholder"] = 'Código de actividad económica secundaria';
            $temp["cssValidator"] = 'validarCaracteres';
            $panel["inputs"][] = $temp;

            if ($tipo == 'pnat' || $tipo == 'pjur' || $tipo == 'esadl') {
                // Fecha de inicio de la actividad secundaria
                $temp = array();
                $temp["tipo"] = 'input';
                $temp["mostrar"] = 'SI';
                $temp["protegido"] = 'NO';
                $temp["obligatorio"] = 'NO';
                $temp["encabezado"] = '';
                $temp["label"] = 'Fecha de inicio de actividad secundaria';
                $temp["id"] = 'feciniact2';
                $temp["name"] = 'feciniact2';
                $temp["type"] = 'date';
                $temp["size"] = '10';
                $temp["value"] = \funcionesGenerales::mostrarFecha($_SESSION["formulario"]["datos"]["feciniact2"]);
                $temp["placeholder"] = 'Fecha de inicio de actividad secundaria';
                $panel["inputs"][] = $temp;
            }

            // Tercer ciiu
            $temp = array();
            $temp["tipo"] = 'input';
            $temp["mostrar"] = 'SI';
            $temp["protegido"] = 'SI';
            $temp["obligatorio"] = 'NO';
            $temp["encabezado"] = '';
            $temp["label"] = 'Otras actividades';
            $temp["id"] = 'ciiu3';
            $temp["name"] = 'ciiu3';
            $temp["type"] = 'text';
            $temp["size"] = '5';
            $temp["maxlength"] = '30';
            $temp["minlength"] = '0';
            $temp["value"] = $_SESSION["formulario"]["datos"]["ciius"][3];
            if (isset($arrCiiu[$temp["value"]])) {
                $temp["txtvalue"] = mb_strtoupper($arrCiiu[$temp["value"]], 'utf-8');
            } else {
                $temp["txtvalue"] = '';
            }
            $temp["placeholder"] = 'Otras actividades';
            $temp["cssValidator"] = 'validarCaracteres';
            $panel["inputs"][] = $temp;

            // Cuarto ciiu
            $temp = array();
            $temp["tipo"] = 'input';
            $temp["mostrar"] = 'SI';
            $temp["protegido"] = 'SI';
            $temp["obligatorio"] = 'NO';
            $temp["encabezado"] = '';
            $temp["label"] = 'Otras actividades';
            $temp["id"] = 'ciiu4';
            $temp["name"] = 'ciiu4';
            $temp["type"] = 'text';
            $temp["size"] = '5';
            $temp["maxlength"] = '30';
            $temp["minlength"] = '0';
            $temp["value"] = $_SESSION["formulario"]["datos"]["ciius"][4];
            if (isset($arrCiiu[$temp["value"]])) {
                $temp["txtvalue"] = mb_strtoupper($arrCiiu[$temp["value"]], 'utf-8');
            } else {
                $temp["txtvalue"] = '';
            }
            $temp["placeholder"] = 'Otras actividades';
            $temp["cssValidator"] = 'validarCaracteres';
            $panel["inputs"][] = $temp;

            $_SESSION["jsonsalida"]["formulario"][] = $panel;
        }

        // ************************************************************************************************************ //
        // Indicadores adicionales
        // *********************************************************************************************************** //
        if ($_SESSION["formulario"]["reliquidacion"] != 'si') {
            if ($tipo == 'pnat' || $tipo == 'pjur' || $tipo == 'esadl') {
                $panel = array();
                $panel["col"] = '';
                $panel["offset"] = '';
                $panel["titulo"] = 'INDICADORES ADICIONALES';
                $panel["texto"] = '';
                $panel["inputs"] = array();

                // Importador o exportador
                $temp = array();
                $temp["tipo"] = 'select';
                $temp["mostrar"] = 'SI';
                $temp["protegido"] = 'NO';
                $temp["obligatorio"] = 'NO';
                $temp["encabezado"] = '';
                $temp["label"] = 'Importador / exportador';
                $temp["id"] = 'impexp';
                $temp["name"] = 'impexp';
                $temp["type"] = 'text';
                $temp["size"] = '1';
                $temp["value"] = $_SESSION["formulario"]["datos"]["impexp"];
                $temp¨["opc"] = array();

                $temp["opc"][] = array(
                'label' => 'Seleccione ...',
                'val' => '',
                'selected' => ''
            );

                $arr = array(
                '0' => 'NO',
                '1' => 'IMPORTA',
                '2' => 'EXPORTA',
                '3' => 'IMPORTA / EXPORTA'
            );
                foreach ($arr as $k => $v) {
                    $tsel = '';
                    if ($k == $_SESSION["formulario"]["datos"]["impexp"]) {
                        $tsel = 'SI';
                    }
                    $temp["opc"][] = array(
                    'label' => $v,
                    'val' => $k,
                    'selected' => $tsel
                );
                }
                $temp["placeholder"] = 'Importador / exportador';
                $panel["inputs"][] = $temp;

                // Usuario aduanero
                $temp = array();
                $temp["tipo"] = 'select';
                $temp["mostrar"] = 'SI';
                $temp["protegido"] = 'NO';
                $temp["obligatorio"] = 'NO';
                $temp["encabezado"] = '';
                $temp["label"] = 'Usuario aduanero';
                $temp["id"] = 'codaduaneros';
                $temp["name"] = 'codaduaneros';
                $temp["type"] = 'text';
                $temp["size"] = '1';
                $temp["value"] = $_SESSION["formulario"]["datos"]["codaduaneros"];
                $temp¨["opc"] = array();

                $temp["opc"][] = array(
                    'label' => 'Seleccione ...',
                    'val' => '',
                    'selected' => ''
                );
                $arr = array(
                '0' => 'N.- No es usuario aduanero',
                '1' => 'S.- Es usuario aduanero'
            );
                foreach ($arr as $k => $v) {
                    $tsel = '';
                    if ($k == $_SESSION["formulario"]["datos"]["codaduaneros"]) {
                        $tsel = 'SI';
                    }
                    $temp["opc"][] = array(
                    'label' => $v,
                    'val' => $k,
                    'selected' => $tsel
                );
                }
                $temp["placeholder"] = 'Usuario aduanero';
                $panel["inputs"][] = $temp;

                // Procesos de innovacion
                $temp = array();
                $temp["tipo"] = 'select';
                $temp["mostrar"] = 'SI';
                $temp["protegido"] = 'NO';
                $temp["obligatorio"] = 'NO';
                $temp["encabezado"] = '';
                $temp["label"] = 'Tiene procesos de innovación';
                $temp["id"] = 'procesosinnovacion';
                $temp["name"] = 'procesosinnovacion';
                $temp["type"] = 'text';
                $temp["size"] = '1';
                $temp["value"] = $_SESSION["formulario"]["datos"]["procesosinnovacion"];
                $temp¨["opc"] = array();


                $temp["opc"][] = array(
                    'label' => 'Seleccione ...',
                    'val' => '',
                    'selected' => ''
                );

                $arr = array(
                '0' => 'N.- No tiene procesos de innovación',
                '1' => 'S.- Tiene procesos de innovación'
            );
                foreach ($arr as $k => $v) {
                    $tsel = '';
                    if ($k == $_SESSION["formulario"]["datos"]["procesosinnovacion"]) {
                        $tsel = 'SI';
                    }
                    $temp["opc"][] = array(
                    'label' => $v,
                    'val' => $k,
                    'selected' => $tsel
                );
                }
                $temp["placeholder"] = 'Tiene procesos de innovación';
                $panel["inputs"][] = $temp;

                // Empresa familiar
                $temp = array();
                $temp["tipo"] = 'select';
                $temp["mostrar"] = 'SI';
                $temp["protegido"] = 'NO';
                $temp["obligatorio"] = 'NO';
                $temp["encabezado"] = '';
                $temp["label"] = 'Empresa familiar';
                $temp["id"] = 'empresafamiliar';
                $temp["name"] = 'empresafamiliar';
                $temp["type"] = 'text';
                $temp["size"] = '1';
                $temp["value"] = $_SESSION["formulario"]["datos"]["empresafamiliar"];
                $temp¨["opc"] = array();

                $temp["opc"][] = array(
                    'label' => 'Seleccione ...',
                    'val' => '',
                    'selected' => ''
                );

                $arr = array(
                '0' => 'N.- No es familiar',
                '1' => 'S.- Empresa familiar'
            );
                foreach ($arr as $k => $v) {
                    $tsel = '';
                    if ($k == $_SESSION["formulario"]["datos"]["empresafamiliar"]) {
                        $tsel = 'SI';
                    }
                    $temp["opc"][] = array(
                    'label' => $v,
                    'val' => $k,
                    'selected' => $tsel
                );
                }
                $temp["placeholder"] = 'Empresa familiar';
                $panel["inputs"][] = $temp;

                // Cantidad de establecimientos
                $temp = array();
                $temp["tipo"] = 'input';
                $temp["mostrar"] = 'SI';
                $temp["protegido"] = 'NO';
                $temp["obligatorio"] = 'NO';
                $temp["encabezado"] = '';
                $temp["label"] = 'Cantidad de establecimientos a nivel nacional';
                $temp["id"] = 'cantest';
                $temp["name"] = 'cantest';
                $temp["type"] = 'text';
                $temp["size"] = '4';
                $temp["maxlength"] = '7';
                $temp["minlength"] = '0';
                $temp["value"] = $_SESSION["formulario"]["datos"]["cantest"];
                $temp["placeholder"] = 'Cantidad de establecimientos a nivel nacional';
                $temp["cssValidator"] = 'numeros';
                $panel["inputs"][] = $temp;

                //
                $_SESSION["jsonsalida"]["formulario"][] = $panel;
            }
        }

        // ************************************************************************************************************ //
        // Referencias crediticias
        // *********************************************************************************************************** //
        if ($_SESSION["formulario"]["reliquidacion"] != 'si') {
            if ($tipo == 'pnat' || $tipo == 'pjur' || $tipo == 'esadl') {
                $panel = array();
                $panel["titulo"] = 'REFERENCIAS DE CRÉDITO';
                $panel["texto"] = '';
                $panel["referencias"] = array();

                // Primera referencia
                $subpanel = array();
                $subpanel["titulo"] = 'Primera referencia';
                $subpanel["col"] = '6';
                $subpanel["offset"] = '';
                $subpanel["inputs"] = array();

                $temp = array();
                $temp["tipo"] = 'input';
                $temp["mostrar"] = 'SI';
                $temp["protegido"] = 'NO';
                $temp["obligatorio"] = 'NO';
                $temp["encabezado"] = '';
                $temp["label"] = 'Nombre entidad';
                $temp["id"] = 'refcrenom1';
                $temp["name"] = 'refcrenom1';
                $temp["type"] = 'text';
                $temp["size"] = '128';
                $temp["maxlength"] = '150';
                $temp["minlength"] = '0';
                $temp["value"] = $_SESSION["formulario"]["datos"]["refcrenom1"];
                $temp["placeholder"] = 'Nombre entidad';
                $temp["cssValidator"] = 'validarReferencia';
                $subpanel["inputs"][] = $temp;

                $temp = array();
                $temp["tipo"] = 'input';
                $temp["mostrar"] = 'SI';
                $temp["protegido"] = 'NO';
                $temp["obligatorio"] = 'NO';
                $temp["encabezado"] = '';
                $temp["label"] = 'Teléfono';
                $temp["id"] = 'refcretel1';
                $temp["name"] = 'refcretel1';
                $temp["type"] = 'text';
                $temp["size"] = '10';
                $temp["maxlength"] = '10';
                $temp["minlength"] = '7';
                $temp["value"] = $_SESSION["formulario"]["datos"]["refcretel1"];
                $temp["placeholder"] = 'Teléfono';
                $temp["cssValidator"] = 'numeros';
                $subpanel["inputs"][] = $temp;

                $panel["referencias"][] = $subpanel;

                // Segunda referencia
                $subpanel = array();
                $subpanel["titulo"] = 'Segunda referencia';
                $subpanel["col"] = '6';
                $subpanel["offset"] = '';
                $subpanel["inputs"] = array();

                $temp = array();
                $temp["tipo"] = 'input';
                $temp["mostrar"] = 'SI';
                $temp["protegido"] = 'NO';
                $temp["obligatorio"] = 'NO';
                $temp["encabezado"] = '';
                $temp["label"] = 'Nombre entidad';
                $temp["id"] = 'refcrenom2';
                $temp["name"] = 'refcrenom2';
                $temp["type"] = 'text';
                $temp["size"] = '128';
                $temp["maxlength"] = '150';
                $temp["minlength"] = '0';
                $temp["value"] = $_SESSION["formulario"]["datos"]["refcrenom2"];
                $temp["placeholder"] = 'Nombre entidad';
                $temp["cssValidator"] = 'validarReferencia';
                $subpanel["inputs"][] = $temp;

                $temp = array();
                $temp["tipo"] = 'input';
                $temp["mostrar"] = 'SI';
                $temp["protegido"] = 'NO';
                $temp["obligatorio"] = 'NO';
                $temp["encabezado"] = '';
                $temp["label"] = 'Teléfono';
                $temp["id"] = 'refcretel2';
                $temp["name"] = 'refcretel2';
                $temp["type"] = 'text';
                $temp["size"] = '10';
                $temp["maxlength"] = '10';
                $temp["minlength"] = '7';
                $temp["value"] = $_SESSION["formulario"]["datos"]["refcretel2"];
                $temp["placeholder"] = 'Teléfono';
                $temp["cssValidator"] = 'numeros';
                $subpanel["inputs"][] = $temp;

                $panel["referencias"][] = $subpanel;

                //
                $_SESSION["jsonsalida"]["formulario"][] = $panel;
            }
        }

        // ************************************************************************************************************ //
        // Referencias comerciales
        // *********************************************************************************************************** //
        if ($_SESSION["formulario"]["reliquidacion"] != 'si') {
            if ($tipo == 'pnat' || $tipo == 'pjur' || $tipo == 'esadl') {
                $panel = array();
                $panel["titulo"] = 'REFERENCIAS COMERCIALES';
                $panel["texto"] = '';
                $panel["referencias"] = array();

                // Primera referencia
                $subpanel = array();
                $subpanel["titulo"] = 'Primera referencia';
                $subpanel["col"] = '6';
                $subpanel["offset"] = '';
                $subpanel["inputs"] = array();

                $temp = array();
                $temp["tipo"] = 'input';
                $temp["mostrar"] = 'SI';
                $temp["protegido"] = 'NO';
                $temp["obligatorio"] = 'NO';
                $temp["encabezado"] = '';
                $temp["label"] = 'Nombre entidad';
                $temp["id"] = 'refcomnom1';
                $temp["name"] = 'refcomnom1';
                $temp["type"] = 'text';
                $temp["size"] = '128';
                $temp["maxlength"] = '150';
                $temp["minlength"] = '0';
                $temp["value"] = $_SESSION["formulario"]["datos"]["refcomnom1"];
                $temp["placeholder"] = 'Nombre entidad';
                $temp["cssValidator"] = 'validarReferencia';
                $subpanel["inputs"][] = $temp;

                $temp = array();
                $temp["tipo"] = 'input';
                $temp["mostrar"] = 'SI';
                $temp["protegido"] = 'NO';
                $temp["obligatorio"] = 'NO';
                $temp["encabezado"] = '';
                $temp["label"] = 'Teléfono';
                $temp["id"] = 'refcomtel1';
                $temp["name"] = 'refcomtel1';
                $temp["type"] = 'text';
                $temp["size"] = '10';
                $temp["maxlength"] = '10';
                $temp["minlength"] = '7';
                $temp["value"] = $_SESSION["formulario"]["datos"]["refcomtel1"];
                $temp["placeholder"] = 'Teléfono';
                $temp["cssValidator"] = 'numeros';
                $subpanel["inputs"][] = $temp;

                $panel["referencias"][] = $subpanel;

                // Segunda referencia
                $subpanel = array();
                $subpanel["titulo"] = 'Segunda referencia';
                $subpanel["col"] = '6';
                $subpanel["offset"] = '';
                $subpanel["inputs"] = array();

                $temp = array();
                $temp["tipo"] = 'input';
                $temp["mostrar"] = 'SI';
                $temp["protegido"] = 'NO';
                $temp["obligatorio"] = 'NO';
                $temp["encabezado"] = '';
                $temp["label"] = 'Nombre entidad';
                $temp["id"] = 'refcomnom2';
                $temp["name"] = 'refcomnom2';
                $temp["type"] = 'text';
                $temp["size"] = '128';
                $temp["maxlength"] = '150';
                $temp["minlength"] = '0';
                $temp["value"] = $_SESSION["formulario"]["datos"]["refcomnom2"];
                $temp["placeholder"] = 'Nombre entidad';
                $temp["cssValidator"] = 'validarReferencia';
                $subpanel["inputs"][] = $temp;

                $temp = array();
                $temp["tipo"] = 'input';
                $temp["mostrar"] = 'SI';
                $temp["protegido"] = 'NO';
                $temp["obligatorio"] = 'NO';
                $temp["encabezado"] = '';
                $temp["label"] = 'Teléfono';
                $temp["id"] = 'refcomtel2';
                $temp["name"] = 'refcomtel2';
                $temp["type"] = 'text';
                $temp["size"] = '10';
                $temp["maxlength"] = '10';
                $temp["minlength"] = '7';
                $temp["value"] = $_SESSION["formulario"]["datos"]["refcomtel2"];
                $temp["placeholder"] = 'Teléfono';
                $temp["cssValidator"] = 'numeros';
                $subpanel["inputs"][] = $temp;

                $panel["referencias"][] = $subpanel;

                //
                $_SESSION["jsonsalida"]["formulario"][] = $panel;
            }
        }


        // ************************************************************************************************************ //
        // Información Financiera - personas naturales y juridicas
        // *********************************************************************************************************** //
        if ($tipo == 'pnat' || $tipo == 'pjur' || $tipo == 'esadl') {
            krsort($_SESSION["formulario"]["datos"]["f"]);
            // die("<pre>".print_r($_SESSION["formulario"]["datos"]["f"], true)."</pre>");
            for ($ano =  $_SESSION["formulario"]["anorenovar"]; $ano >= $_SESSION["formulario"]["anorenovarpri"]; $ano--) {
                $panel = array();
                $panel["titulo"] = 'INFORMACION FINANCIERA AÑO ' . $ano;
                $panel["texto"] = '';
                $panel["avisotop"] = array();
                $panel["avisotop"]["texto"] = ''
                    . 'Por favor indique a continuación la información de su balance comercial con corte a diciembre 31 de '
                    . '' . ($ano - 1) . ', los valores deberan estar expresados en pesos colombianos y sin decimales. Igualmente '
                    . 'digite el número de personas que tenía vinculadas laboralmente a dicho corte asi como el porcentaje de '
                    . 'personas con contrato temporal';

                $panel["avisotop"]["color"] = 'info';


                $panel["grupos"] = array();

                // PANEL DE ACTIVOS
                $subpanel = array();
                $subpanel["titulo"] = 'ACTIVOS';
                $subpanel["col"] = '8';
                $subpanel["offset"] = '2';
                $subpanel["inputs"] = array();

                // Año de los datos
                $temp = array();
                $temp["tipo"] = 'input';
                $temp["mostrar"] = 'NO';
                $temp["protegido"] = 'SI';
                $temp["obligatorio"] = 'SI';
                $temp["encabezado"] = '';
                $temp["label"] = 'Año de los datos';
                $temp["id"] = 'anodatos_' . $ano;
                $temp["name"] = 'anodatos_' . $ano;
                $temp["type"] = 'text';
                $temp["size"] = '4';
                $temp["maxlength"] = '4';
                $temp["minlength"] = '0';
                $temp["value"] = ($ano == ".00" ? "0" : $ano);
                $temp["placeholder"] = 'Año de los datos';
                $subpanel["inputs"][] = $temp;

                // Fecha datos
                $temp = array();
                $temp["tipo"] = 'input';
                $temp["mostrar"] = 'NO';
                $temp["protegido"] = 'SI';
                $temp["obligatorio"] = 'SI';
                $temp["encabezado"] = '';
                $temp["label"] = 'Fecha de los datos';
                $temp["id"] = 'fechadatos_' . $ano;
                $temp["name"] = 'fechadatos_' . $ano;
                $temp["type"] = 'text';
                $temp["size"] = '4';
                $temp["maxlength"] = '30';
                $temp["minlength"] = '0';
                $temp["value"] = $ano . '1231';
                $temp["placeholder"] = 'Fecha de los datos';
                $subpanel["inputs"][] = $temp;


                // Activo corriente
                $temp = array();
                $temp["tipo"] = 'input';
                $temp["mostrar"] = 'SI';
                $temp["protegido"] = 'NO';
                $temp["obligatorio"] = 'SI';
                $temp["encabezado"] = '';
                $temp["label"] = 'Activo corriente';
                $temp["id"] = 'actcte_' . $ano;
                $temp["name"] = 'actcte_' . $ano;
                $temp["type"] = 'text';
                $temp["size"] = '20';
                $temp["maxlength"] = '30';
                $temp["minlength"] = '0';
                if (!isset($_SESSION["formulario"]["datos"]["f"][$ano]["actcte"])) {
                    $_SESSION["formulario"]["datos"]["f"][$ano]["actcte"] = 0;
                }
                $temp["value"] = ($_SESSION["formulario"]["datos"]["f"][$ano]["actcte"] == ".00" ? "0" : $_SESSION["formulario"]["datos"]["f"][$ano]["actcte"]);
                $temp["placeholder"] = 'Activo corriente';
                $temp["cssValidator"] = 'numeros formatoMoneda';
                $subpanel["inputs"][] = $temp;

                // Activo no corriente
                $temp = array();
                $temp["tipo"] = 'input';
                $temp["mostrar"] = 'SI';
                $temp["protegido"] = 'NO';
                $temp["obligatorio"] = 'SI';
                $temp["encabezado"] = '';
                $temp["label"] = 'Activo no corriente';
                $temp["id"] = 'actnocte_' . $ano;
                $temp["name"] = 'actnocte_' . $ano;
                $temp["type"] = 'text';
                $temp["size"] = '20';
                $temp["maxlength"] = '30';
                $temp["minlength"] = '0';
                if (!isset($_SESSION["formulario"]["datos"]["f"][$ano]["actnocte"])) {
                    $_SESSION["formulario"]["datos"]["f"][$ano]["actnocte"] = 0;
                }
                $temp["value"] = ($_SESSION["formulario"]["datos"]["f"][$ano]["actnocte"] == ".00" ? "0" : $_SESSION["formulario"]["datos"]["f"][$ano]["actnocte"]);
                $temp["placeholder"] = 'Activo no corriente';
                $temp["cssValidator"] = 'numeros formatoMoneda';
                $subpanel["inputs"][] = $temp;

                // Activo total
                $temp = array();
                $temp["tipo"] = 'input';
                $temp["mostrar"] = 'SI';
                $temp["protegido"] = 'SI';
                $temp["obligatorio"] = 'SI';
                $temp["encabezado"] = '';
                $temp["label"] = 'Activo total';
                $temp["id"] = 'acttot_' . $ano;
                $temp["name"] = 'acttot_' . $ano;
                $temp["type"] = 'text';
                $temp["size"] = '20';
                $temp["maxlength"] = '30';
                $temp["minlength"] = '0';
                if (!isset($_SESSION["formulario"]["datos"]["f"][$ano]["acttot"])) {
                    foreach ($_SESSION["tramite"]["expedientes"] as $key => $l) {
                        if (($l["ultimoanorenovado"] == $ano) && ($l["matricula"] == $_SESSION["formulario"]["matricula"])) {
                            $_SESSION["formulario"]["datos"]["f"][$ano]["acttot"] = $l["nuevosactivos"];
                        }
                    }
                }
                $temp["value"] = ($_SESSION["formulario"]["datos"]["f"][$ano]["acttot"] == ".00" ? "0" : $_SESSION["formulario"]["datos"]["f"][$ano]["acttot"]);
                $temp["placeholder"] = 'Activo total';
                $temp["cssValidator"] = 'numeros formatoMoneda';
                $subpanel["inputs"][] = $temp;

                $panel["grupos"][] = $subpanel;

                // PANEL DE PASIVOS
                $subpanel = array();
                $subpanel["titulo"] = 'PASIVOS Y PATRIMONIO';
                $subpanel["inputs"] = array();
                $subpanel["col"] = '8';
                $subpanel["offset"] = '2';

                // Pasivo corriente
                $temp = array();
                $temp["tipo"] = 'input';
                $temp["mostrar"] = 'SI';
                $temp["protegido"] = 'NO';
                $temp["obligatorio"] = 'SI';
                $temp["encabezado"] = '';
                $temp["label"] = 'Pasivo corriente';
                $temp["id"] = 'pascte_' . $ano;
                $temp["name"] = 'pascte_' . $ano;
                $temp["type"] = 'text';
                $temp["size"] = '20';
                $temp["maxlength"] = '30';
                $temp["minlength"] = '0';
                if (!isset($_SESSION["formulario"]["datos"]["f"][$ano]["pascte"])) {
                    $_SESSION["formulario"]["datos"]["f"][$ano]["pascte"] = 0;
                }
                $temp["value"] = ($_SESSION["formulario"]["datos"]["f"][$ano]["pascte"] == ".00" ? "0" : $_SESSION["formulario"]["datos"]["f"][$ano]["pascte"]);
                $temp["placeholder"] = 'Pasivo corriente';
                $temp["cssValidator"] = 'numeros formatoMoneda pasivos';
                $subpanel["inputs"][] = $temp;

                // Pasivo no corriente
                $temp = array();
                $temp["tipo"] = 'input';
                $temp["mostrar"] = 'SI';
                $temp["protegido"] = 'NO';
                $temp["obligatorio"] = 'SI';
                $temp["encabezado"] = '';
                $temp["label"] = 'Pasivo no corriente';
                $temp["id"] = 'paslar_' . $ano;
                $temp["name"] = 'paslar_' . $ano;
                $temp["type"] = 'text';
                $temp["size"] = '20';
                $temp["maxlength"] = '30';
                $temp["minlength"] = '0';
                if (!isset($_SESSION["formulario"]["datos"]["f"][$ano]["paslar"])) {
                    $_SESSION["formulario"]["datos"]["f"][$ano]["paslar"] = 0;
                }
                $temp["value"] = ($_SESSION["formulario"]["datos"]["f"][$ano]["paslar"] == ".00" ? "0" : $_SESSION["formulario"]["datos"]["f"][$ano]["paslar"]);
                $temp["placeholder"] = 'Pasivo no corriente';
                $temp["cssValidator"] = 'numeros formatoMoneda pasivos';
                $subpanel["inputs"][] = $temp;

                // Pasivo total
                $temp = array();
                $temp["tipo"] = 'input';
                $temp["mostrar"] = 'SI';
                $temp["protegido"] = 'SI';
                $temp["obligatorio"] = 'SI';
                $temp["encabezado"] = '';
                $temp["label"] = 'Pasivo total';
                $temp["id"] = 'pastot_' . $ano;
                $temp["name"] = 'pastot_' . $ano;
                $temp["type"] = 'text';
                $temp["size"] = '20';
                $temp["maxlength"] = '30';
                $temp["minlength"] = '0';
                if (!isset($_SESSION["formulario"]["datos"]["f"][$ano]["pastot"])) {
                    $_SESSION["formulario"]["datos"]["f"][$ano]["pastot"] = 0;
                }
                $temp["value"] = ($_SESSION["formulario"]["datos"]["f"][$ano]["pastot"] == ".00" ? "0" : $_SESSION["formulario"]["datos"]["f"][$ano]["pastot"]);
                $temp["placeholder"] = 'Pasivo total';
                $temp["cssValidator"] = 'numeros formatoMoneda pasivos';
                $subpanel["inputs"][] = $temp;

                // Patrimonio
                $temp = array();
                $temp["tipo"] = 'input';
                $temp["mostrar"] = 'SI';
                $temp["protegido"] = 'NO';
                $temp["obligatorio"] = 'SI';
                $temp["encabezado"] = '';
                $temp["label"] = 'Patrimonio neto';
                $temp["id"] = 'pattot_' . $ano;
                $temp["name"] = 'pattot_' . $ano;
                $temp["type"] = 'text';
                $temp["size"] = '20';
                $temp["maxlength"] = '30';
                $temp["minlength"] = '0';
                if (!isset($_SESSION["formulario"]["datos"]["f"][$ano]["pattot"])) {
                    $_SESSION["formulario"]["datos"]["f"][$ano]["pattot"] = 0;
                }
                $temp["value"] = ($_SESSION["formulario"]["datos"]["f"][$ano]["pattot"] == ".00" ? "0" : $_SESSION["formulario"]["datos"]["f"][$ano]["pattot"]);
                $temp["placeholder"] = 'Patrimonio neto';
                $temp["cssValidator"] = 'numeros formatoMoneda pasivos';
                $subpanel["inputs"][] = $temp;

                // Pasivo + Patrimonio
                $temp = array();
                $temp["tipo"] = 'input';
                $temp["mostrar"] = 'SI';
                $temp["protegido"] = 'SI';
                $temp["obligatorio"] = 'SI';
                $temp["encabezado"] = '';
                $temp["label"] = 'Pasivo + Patrimonio';
                $temp["id"] = 'paspat_' . $ano;
                $temp["name"] = 'paspat_' . $ano;
                $temp["type"] = 'text';
                $temp["size"] = '20';
                $temp["maxlength"] = '30';
                $temp["minlength"] = '0';
                if (!isset($_SESSION["formulario"]["datos"]["f"][$ano]["paspat"])) {
                    $_SESSION["formulario"]["datos"]["f"][$ano]["paspat"] = 0;
                }
                $temp["value"] = ($_SESSION["formulario"]["datos"]["f"][$ano]["paspat"] == ".00" ? "0" : $_SESSION["formulario"]["datos"]["f"][$ano]["paspat"]);
                $temp["placeholder"] = 'Pasivo + Patrimonio';
                $temp["cssValidator"] = 'numeros formatoMoneda pasivos';
                $subpanel["inputs"][] = $temp;

                // Balance social
                $temp = array();
                $temp["tipo"] = 'input';
                if ($tipo == 'esadl') {
                    $temp["mostrar"] = 'SI';
                } else {
                    $temp["mostrar"] = 'NO';
                }
                $temp["protegido"] = 'NO';
                $temp["obligatorio"] = 'SI';
                $temp["encabezado"] = '';
                $temp["label"] = 'Balance social';
                $temp["id"] = 'balsoc_' . $ano;
                $temp["name"] = 'balsoc_' . $ano;
                $temp["type"] = 'text';
                $temp["size"] = '20';
                $temp["maxlength"] = '30';
                $temp["minlength"] = '0';
                if (!isset($_SESSION["formulario"]["datos"]["f"][$ano]["balsoc"])) {
                    $_SESSION["formulario"]["datos"]["f"][$ano]["balsoc"] = 0;
                }
                if ($tipo != 'esadl') {
                    $_SESSION["formulario"]["datos"]["f"][$ano]["balsoc"] = 0;
                }
                $temp["value"] = ($_SESSION["formulario"]["datos"]["f"][$ano]["balsoc"] == ".00" ? "0" : $_SESSION["formulario"]["datos"]["f"][$ano]["balsoc"]);
                $temp["placeholder"] = 'Balance social';
                $temp["cssValidator"] = 'numeros formatoMoneda';
                $subpanel["inputs"][] = $temp;

                $panel["grupos"][] = $subpanel;


                // PANEL DE ESTADO DE RESULTADOS
                $subpanel = array();
                $subpanel["titulo"] = 'ESTADO DE RESULTADOS';
                $subpanel["inputs"] = array();
                $subpanel["col"] = '8';
                $subpanel["offset"] = '2';

                // Ingresos operacionales
                $temp = array();
                $temp["tipo"] = 'input';
                $temp["mostrar"] = 'SI';
                $temp["protegido"] = 'NO';
                $temp["obligatorio"] = 'SI';
                $temp["encabezado"] = '';
                $temp["label"] = 'Ingresos de la actividad ordinaria';
                $temp["id"] = 'ingope_' . $ano;
                $temp["name"] = 'ingope_' . $ano;
                $temp["type"] = 'text';
                $temp["size"] = '20';
                $temp["maxlength"] = '30';
                $temp["minlength"] = '0';
                if (!isset($_SESSION["formulario"]["datos"]["f"][$ano]["ingope"])) {
                    $_SESSION["formulario"]["datos"]["f"][$ano]["ingope"] = 0;
                }
                $temp["value"] = ($_SESSION["formulario"]["datos"]["f"][$ano]["ingope"] == ".00" ? "0" : $_SESSION["formulario"]["datos"]["f"][$ano]["ingope"]);
                $temp["placeholder"] = 'Ingresos de la actividad ordinaria';
                $temp["cssValidator"] = 'numeros';
                $subpanel["inputs"][] = $temp;

                // Ingresos no operacionales
                $temp = array();
                $temp["tipo"] = 'input';
                $temp["mostrar"] = 'SI';
                $temp["protegido"] = 'NO';
                $temp["obligatorio"] = 'SI';
                $temp["encabezado"] = '';
                $temp["label"] = 'Otros ingresos';
                $temp["id"] = 'ingnoope_' . $ano;
                $temp["name"] = 'ingnoope_' . $ano;
                $temp["type"] = 'text';
                $temp["size"] = '20';
                $temp["maxlength"] = '30';
                $temp["minlength"] = '0';
                if (!isset($_SESSION["formulario"]["datos"]["f"][$ano]["ingnoope"])) {
                    $_SESSION["formulario"]["datos"]["f"][$ano]["ingnoope"] = 0;
                }
                $temp["value"] = ($_SESSION["formulario"]["datos"]["f"][$ano]["ingnoope"] == ".00" ? "0" : $_SESSION["formulario"]["datos"]["f"][$ano]["ingnoope"]);
                $temp["placeholder"] = 'Otros ingresos';
                $temp["cssValidator"] = 'numeros formatoMoneda';
                $subpanel["inputs"][] = $temp;

                // Costo de ventas
                $temp = array();
                $temp["tipo"] = 'input';
                $temp["mostrar"] = 'SI';
                $temp["protegido"] = 'NO';
                $temp["obligatorio"] = 'SI';
                $temp["encabezado"] = '';
                $temp["label"] = 'Costo de ventas';
                $temp["id"] = 'cosven_' . $ano;
                $temp["name"] = 'cosven_' . $ano;
                $temp["type"] = 'text';
                $temp["size"] = '20';
                $temp["maxlength"] = '30';
                $temp["minlength"] = '0';
                if (!isset($_SESSION["formulario"]["datos"]["f"][$ano]["cosven"])) {
                    $_SESSION["formulario"]["datos"]["f"][$ano]["cosven"] = 0;
                }
                $temp["value"] = ($_SESSION["formulario"]["datos"]["f"][$ano]["cosven"] == ".00" ? "0" : $_SESSION["formulario"]["datos"]["f"][$ano]["cosven"]);
                $temp["placeholder"] = 'Costo de ventas';
                $temp["cssValidator"] = 'numeros formatoMoneda';
                $subpanel["inputs"][] = $temp;

                // Gastos operacionales
                $temp = array();
                $temp["tipo"] = 'input';
                $temp["mostrar"] = 'SI';
                $temp["protegido"] = 'NO';
                $temp["obligatorio"] = 'SI';
                $temp["encabezado"] = '';
                $temp["label"] = 'Gastos operacionales';
                $temp["id"] = 'gtoven_' . $ano;
                $temp["name"] = 'gtoven_' . $ano;
                $temp["type"] = 'text';
                $temp["size"] = '20';
                $temp["maxlength"] = '30';
                $temp["minlength"] = '0';
                if (!isset($_SESSION["formulario"]["datos"]["f"][$ano]["gtoven"])) {
                    $_SESSION["formulario"]["datos"]["f"][$ano]["gtoven"] = 0;
                }
                $temp["value"] = ($_SESSION["formulario"]["datos"]["f"][$ano]["gtoven"] == ".00" ? "0" : $_SESSION["formulario"]["datos"]["f"][$ano]["gtoven"]);
                $temp["placeholder"] = 'Gastos operacionales';
                $temp["cssValidator"] = 'numeros formatoMoneda';
                $subpanel["inputs"][] = $temp;

                // Otros gastos
                $temp = array();
                $temp["tipo"] = 'input';
                $temp["mostrar"] = 'SI';
                $temp["protegido"] = 'NO';
                $temp["obligatorio"] = 'SI';
                $temp["encabezado"] = '';
                $temp["label"] = 'Otros gastos';
                $temp["id"] = 'gtoadm_' . $ano;
                $temp["name"] = 'gtoadm_' . $ano;
                $temp["type"] = 'text';
                $temp["size"] = '20';
                $temp["maxlength"] = '30';
                $temp["minlength"] = '0';
                if (!isset($_SESSION["formulario"]["datos"]["f"][$ano]["gtoadm"])) {
                    $_SESSION["formulario"]["datos"]["f"][$ano]["gtoadm"] = 0;
                }
                $temp["value"] = ($_SESSION["formulario"]["datos"]["f"][$ano]["gtoadm"] == ".00" ? "0" : $_SESSION["formulario"]["datos"]["f"][$ano]["gtoadm"]);
                $temp["placeholder"] = 'Otros gastos';
                $temp["cssValidator"] = 'numeros formatoMoneda';
                $subpanel["inputs"][] = $temp;

                // Gastos por impuestos
                $temp = array();
                $temp["tipo"] = 'input';
                $temp["mostrar"] = 'SI';
                $temp["protegido"] = 'NO';
                $temp["obligatorio"] = 'SI';
                $temp["encabezado"] = '';
                $temp["label"] = 'Gastos por impuestos';
                $temp["id"] = 'gasimp_' . $ano;
                $temp["name"] = 'gasimp_' . $ano;
                $temp["type"] = 'text';
                $temp["size"] = '20';
                $temp["maxlength"] = '30';
                $temp["minlength"] = '0';
                if (!isset($_SESSION["formulario"]["datos"]["f"][$ano]["gasimp"])) {
                    $_SESSION["formulario"]["datos"]["f"][$ano]["gasimp"] = 0;
                }
                $temp["value"] = ($_SESSION["formulario"]["datos"]["f"][$ano]["gasimp"] == ".00" ? "0" : $_SESSION["formulario"]["datos"]["f"][$ano]["gasimp"]);
                $temp["placeholder"] = 'Gastos por impuestos';
                $temp["cssValidator"] = 'numeros formatoMoneda';
                $subpanel["inputs"][] = $temp;

                // Utilidad o pérdida operacional
                $temp = array();
                $temp["tipo"] = 'input';
                $temp["mostrar"] = 'SI';
                $temp["protegido"] = 'NO';
                $temp["obligatorio"] = 'SI';
                $temp["encabezado"] = '';
                $temp["label"] = 'Utilidad o pérdida operacional';
                $temp["id"] = 'utiope_' . $ano;
                $temp["name"] = 'utiope_' . $ano;
                $temp["type"] = 'text';
                $temp["size"] = '20';
                $temp["maxlength"] = '30';
                $temp["minlength"] = '0';
                if (!isset($_SESSION["formulario"]["datos"]["f"][$ano]["utiope"])) {
                    $_SESSION["formulario"]["datos"]["f"][$ano]["utiope"] = 0;
                }
                $temp["value"] = ($_SESSION["formulario"]["datos"]["f"][$ano]["utiope"] == ".00" ? "0" : $_SESSION["formulario"]["datos"]["f"][$ano]["utiope"]);
                $temp["placeholder"] = 'Utilidad o pérdida operacional';
                $temp["cssValidator"] = 'numeros formatoMoneda';
                $subpanel["inputs"][] = $temp;

                // Resultado del ejercicio
                $temp = array();
                $temp["tipo"] = 'input';
                $temp["mostrar"] = 'SI';
                $temp["protegido"] = 'NO';
                $temp["obligatorio"] = 'SI';
                $temp["encabezado"] = '';
                $temp["label"] = 'Resultado del ejercicio';
                $temp["id"] = 'utinet_' . $ano;
                $temp["name"] = 'utinet_' . $ano;
                $temp["type"] = 'text';
                $temp["size"] = '20';
                $temp["maxlength"] = '30';
                $temp["minlength"] = '0';
                if (!isset($_SESSION["formulario"]["datos"]["f"][$ano]["utinet"])) {
                    $_SESSION["formulario"]["datos"]["f"][$ano]["utinet"] = 0;
                }
                $temp["value"] = ($_SESSION["formulario"]["datos"]["f"][$ano]["utinet"] == ".00" ? "0" : $_SESSION["formulario"]["datos"]["f"][$ano]["utinet"]);
                $temp["placeholder"] = 'Resultado del ejercicio';
                $temp["cssValidator"] = 'numeros formatoMoneda';
                $subpanel["inputs"][] = $temp;

                $panel["grupos"][] = $subpanel;


                // PANEL DE ESTADO DE RESULTADOS
                $subpanel = array();
                $subpanel["titulo"] = 'PERSONAL';
                $subpanel["col"] = '';
                $subpanel["offset"] = '';
                $subpanel["inputs"] = array();

                // Personal vinculado
                $temp = array();
                $temp["tipo"] = 'input';
                $temp["mostrar"] = 'SI';
                $temp["protegido"] = 'NO';
                $temp["obligatorio"] = 'SI';
                $temp["encabezado"] = '';
                $temp["label"] = 'Personal ocupado';
                $temp["id"] = 'personal_' . $ano;
                $temp["name"] = 'personal_' . $ano;
                $temp["type"] = 'text';
                $temp["size"] = '6';
                $temp["maxlength"] = '30';
                $temp["minlength"] = '0';
                if (!isset($_SESSION["formulario"]["datos"]["f"][$ano]["personal"])) {
                    $_SESSION["formulario"]["datos"]["f"][$ano]["personal"] = $_SESSION ["tramite"]["numeroempleados"];
                }
                $temp["value"] = ($_SESSION["formulario"]["datos"]["f"][$ano]["personal"] == ".00" ? "0" : $_SESSION["formulario"]["datos"]["f"][$ano]["personal"]);
                $temp["placeholder"] = 'Personal ocupado';
                $temp["cssValidator"] = 'numeros';
                $subpanel["inputs"][] = $temp;

                // % Personal temporal
                $temp = array();
                $temp["tipo"] = 'input';
                $temp["mostrar"] = 'SI';
                $temp["protegido"] = 'NO';
                $temp["obligatorio"] = 'SI';
                $temp["encabezado"] = '';
                $temp["label"] = 'Porcentaje de personal temporal (%)';
                $temp["id"] = 'personaltemp_' . $ano;
                $temp["name"] = 'personaltemp_' . $ano;
                $temp["type"] = 'text';
                $temp["size"] = '6';
                $temp["maxlength"] = '30';
                $temp["minlength"] = '0';
                if (!isset($_SESSION["formulario"]["datos"]["f"][$ano]["personaltemp"])) {
                    $_SESSION["formulario"]["datos"]["f"][$ano]["personaltemp"] = 0;
                }
                $temp["value"] = ($_SESSION["formulario"]["datos"]["f"][$ano]["personaltemp"] == ".00" ? "0" : $_SESSION["formulario"]["datos"]["f"][$ano]["personaltemp"]);
                $temp["placeholder"] = 'Porcentaje de personal temporal (%)';
                $temp["cssValidator"] = 'numeros';
                $subpanel["inputs"][] = $temp;

                $panel["grupos"][] = $subpanel;

                $_SESSION["jsonsalida"]["formulario"][] = $panel;
            }
//
            $panel = array();
            $panel["col"] = '';
            $panel["offset"] = '';
            $panel["titulo"] = 'MARCO NORMATIVO';
            $panel["texto"] = '';
            $panel["inputs"] = array();

            // Grupo NIIF
            $temp = array();
            $temp["tipo"] = 'select';
            $temp["mostrar"] = 'SI';
            $temp["protegido"] = 'NO';
            $temp["obligatorio"] = 'SI';
            $temp["encabezado"] = '';
            $temp["label"] = 'Grupo NIIF';
            $temp["id"] = 'gruponiif';
            $temp["name"] = 'gruponiif';
            $temp["type"] = 'text';
            $temp["size"] = '1';
            if (!isset($_SESSION["formulario"]["datos"]["gruponiif"])) {
                $_SESSION["formulario"]["datos"]["gruponiif"] = '';
            }
            $temp["value"] = $_SESSION["formulario"]["datos"]["gruponiif"];
            $temp["opc"] = array();


            $temp["opc"][] = array(
                'label' => 'Seleccione ...',
                'val' => '',
                'selected' => ''
            );

            $temx = retornarRegistrosMysqliApi($mysqli, 'bas_gruponiif', "1=1", "descripcion");
            foreach ($temx as $tx) {
                $tsel = '';
                if ($tx["id"] == $_SESSION["formulario"]["datos"]["gruponiif"]) {
                    $tsel = 'SI';
                }
                $temp["opc"][] = array(
                'label' => $tx["descripcion"],
                'val' => $tx["id"],
                'selected' => $tsel
            );
            }
            $temp["placeholder"] = '';
            $panel["inputs"][] = $temp;

            $_SESSION["jsonsalida"]["formulario"][] = $panel;
        }

        // ************************************************************************************************************ //
        // Información de bienes que posee
        // *********************************************************************************************************** //
        if ($_SESSION["formulario"]["reliquidacion"] != 'si') {
            if ($tipo == 'pnat' || $tipo == 'pjur' || $tipo == 'esadl') {
                $panel = array();
                $panel["col"] = '';
                $panel["offset"] = '';
                $panel["titulo"] = 'DETALLE DE LOS BIENES RAICES QUE POSEA';
                $panel["texto"] = '';
                $panel["avisotop"] = array();
                $panel["avisotop"]["texto"] = ''
                    . '(En cumplimiento del artículo 32 del Código de Comercio)';

                $panel["avisotop"]["color"] = 'info';
                $panel["referencias"] = array();

                for ($ind = 1; $ind < 3; $ind++) {
                    $subpanel = array();
                    $subpanel["col"] = '';
                    $subpanel["offset"] = '';
                    $subpanel["titulo"] = 'Bien # ' . $ind;
                    $subpanel["inputs"] = array();

                    $temp = array();
                    $temp["tipo"] = 'input';
                    $temp["mostrar"] = 'SI';
                    $temp["protegido"] = 'NO';
                    $temp["obligatorio"] = 'NO';
                    $temp["encabezado"] = '';
                    $temp["label"] = 'Matrícula Inmobiliaria';
                    $temp["id"] = '_rmerGen_bienes_mi_' . $ind;
                    $temp["name"] = '_rmerGen_bienes_mi_' . $ind;
                    $temp["type"] = 'text';
                    $temp["size"] = '128';
                    $temp["maxlength"] = '150';
                    $temp["minlength"] = '0';
                    $temp["value"] = $_SESSION["formulario"]["datos"]["bienes"][$ind]["matinmo"];
                    $temp["placeholder"] = 'Matrícula Inmobiliaria';
                    $temp["cssValidator"] = 'numeros';
                    $subpanel["inputs"][] = $temp;

                    $temp = array();
                    $temp["tipo"] = 'input';
                    $temp["mostrar"] = 'SI';
                    $temp["protegido"] = 'NO';
                    $temp["obligatorio"] = 'NO';
                    $temp["encabezado"] = '';
                    $temp["label"] = 'País';
                    $temp["id"] = '_rmerGen_bienes_pa_' . $ind;
                    $temp["name"] = '_rmerGen_bienes_pa_' . $ind;
                    $temp["type"] = 'text';
                    $temp["size"] = '30';
                    $temp["maxlength"] = '50';
                    $temp["minlength"] = '0';
                    $temp["value"] = $_SESSION["formulario"]["datos"]["bienes"][$ind]["pais"];
                    $temp["placeholder"] = 'País';
                    $temp["cssValidator"] = 'letras';
                    $subpanel["inputs"][] = $temp;

                    $temp = array();
                    $temp["tipo"] = 'input';
                    $temp["mostrar"] = 'SI';
                    $temp["protegido"] = 'NO';
                    $temp["obligatorio"] = 'NO';
                    $temp["encabezado"] = '';
                    $temp["label"] = 'Departamento';
                    $temp["id"] = '_rmerGen_bienes_dp_' . $ind;
                    $temp["name"] = '_rmerGen_bienes_dp_' . $ind;
                    $temp["type"] = 'text';
                    $temp["size"] = '30';
                    $temp["maxlength"] = '50';
                    $temp["minlength"] = '0';
                    $temp["value"] = $_SESSION["formulario"]["datos"]["bienes"][$ind]["dpto"];
                    $temp["placeholder"] = 'Departamento';
                    $temp["cssValidator"] = 'letras';
                    $subpanel["inputs"][] = $temp;

                    $temp = array();
                    $temp["tipo"] = 'input';
                    $temp["mostrar"] = 'SI';
                    $temp["protegido"] = 'NO';
                    $temp["obligatorio"] = 'NO';
                    $temp["encabezado"] = '';
                    $temp["label"] = 'Municipio';
                    $temp["id"] = '_rmerGen_bienes_mu_' . $ind;
                    $temp["name"] = '_rmerGen_bienes_mu_' . $ind;
                    $temp["type"] = 'text';
                    $temp["size"] = '128';
                    $temp["maxlength"] = '150';
                    $temp["minlength"] = '0';
                    $temp["value"] = $_SESSION["formulario"]["datos"]["bienes"][$ind]["muni"];
                    $temp["placeholder"] = 'Municipio';
                    $temp["cssValidator"] = 'letras';
                    $subpanel["inputs"][] = $temp;

                    $temp = array();
                    $temp["tipo"] = 'input';
                    $temp["mostrar"] = 'SI';
                    $temp["protegido"] = 'NO';
                    $temp["obligatorio"] = 'NO';
                    $temp["encabezado"] = '';
                    $temp["label"] = 'Barrio';
                    $temp["id"] = '_rmerGen_bienes_ba_' . $ind;
                    $temp["name"] = '_rmerGen_bienes_ba_' . $ind;
                    $temp["type"] = 'text';
                    $temp["size"] = '128';
                    $temp["maxlength"] = '150';
                    $temp["minlength"] = '0';
                    $temp["value"] = $_SESSION["formulario"]["datos"]["bienes"][$ind]["barrio"];
                    $temp["placeholder"] = 'Barrio';
                    $temp["cssValidator"] = 'validarDireccion';
                    $subpanel["inputs"][] = $temp;

                    $temp = array();
                    $temp["tipo"] = 'input';
                    $temp["mostrar"] = 'SI';
                    $temp["protegido"] = 'NO';
                    $temp["obligatorio"] = 'NO';
                    $temp["encabezado"] = '';
                    $temp["label"] = 'Dirección';
                    $temp["id"] = '_rmerGen_bienes_di_' . $ind;
                    $temp["name"] = '_rmerGen_bienes_di_' . $ind;
                    $temp["type"] = 'text';
                    $temp["size"] = '128';
                    $temp["maxlength"] = '150';
                    $temp["minlength"] = '0';
                    $temp["value"] = $_SESSION["formulario"]["datos"]["bienes"][$ind]["dir"];
                    $temp["placeholder"] = 'Dirección';
                    $temp["cssValidator"] = 'validarDireccion';
                    $subpanel["inputs"][] = $temp;

                    $panel["referencias"][] = $subpanel;
                }


                //
                $_SESSION["jsonsalida"]["formulario"][] = $panel;
            }
        }


        // ************************************************************************************************************ //
        // Información Financiera - establecimientos, sucursales y agencias
        // *********************************************************************************************************** //

        if ($tipo == 'est' || $tipo == 'suc' || $tipo == 'age') {
            krsort($_SESSION["formulario"]["datos"]["f"]);
            //die("<pre>".print_r($_SESSION["formulario"]["datos"]["f"], true)."</pre>");
            for ($ano =  $_SESSION["formulario"]["anorenovar"]; $ano >= $_SESSION["formulario"]["anorenovarpri"]; $ano--) {
                $panel = array();
                $panel["col"] = '';
                $panel["offset"] = '';
                $panel["titulo"] = 'INFORMACION FINANCIERA AÑO ' . $ano;
                $panel["texto"] = '';
                $panel["inputs"] = array();

                // Valor comercial
                $temp = array();
                $temp["tipo"] = 'input';
                $temp["mostrar"] = 'SI';
                $temp["protegido"] = 'SI';
                $temp["obligatorio"] = 'SI';
                $temp["encabezado"] = '';
                $temp["label"] = 'Valor comercial o activos vinculados';
                $temp["id"] = 'actvin_' . $ano;
                $temp["name"] = 'actvin_' . $ano;
                $temp["type"] = 'text';
                $temp["size"] = '20';
                $temp["maxlength"] = '30';
                $temp["minlength"] = '0';
                if (!isset($_SESSION["formulario"]["datos"]["f"][$ano]["actvin"])) {
                    foreach ($_SESSION["tramite"]["expedientes"] as $key => $l) {
                        if (($l["ultimoanorenovado"] == $ano) && ($l["matricula"] == $_SESSION["formulario"]["matricula"])) {
                            $_SESSION["formulario"]["datos"]["f"][$ano]["actvin"] = $l["nuevosactivos"];
                        }
                    }
                }
                $temp["value"] = $_SESSION["formulario"]["datos"]["f"][$ano]["actvin"];
                $temp["placeholder"] = 'Valor comercial o activos vinculados';
                $temp["cssValidator"] = 'numeros formatoMoneda';
                $panel["inputs"][] = $temp;

                // Personal ocupado
                $temp = array();
                $temp["tipo"] = 'input';
                $temp["mostrar"] = 'SI';
                $temp["protegido"] = 'NO';
                $temp["obligatorio"] = 'SI';
                $temp["encabezado"] = '';
                $temp["label"] = 'Personal ocupado';
                $temp["id"] = 'personal_' . $ano;
                $temp["name"] = 'personal_' . $ano;
                $temp["type"] = 'text';
                $temp["size"] = '6';
                $temp["maxlength"] = '30';
                $temp["minlength"] = '0';
                if (!isset($_SESSION["formulario"]["datos"]["f"][$ano]["personal"])) {
                    $_SESSION["formulario"]["datos"]["f"][$ano]["personal"] = 0;
                }
                $temp["value"] = $_SESSION["formulario"]["datos"]["f"][$ano]["personal"];
                $temp["placeholder"] = 'Personal ocupado';
                $temp["cssValidator"] = 'numeros';
                $panel["inputs"][] = $temp;

                $_SESSION["jsonsalida"]["formulario"][] = $panel;
            }

            // Tipo de local
            $panel = array();
            $panel["col"] = '';
            $panel["offset"] = '';
            $panel["titulo"] = 'INFORMACION DEL LOCAL';
            $panel["texto"] = '';
            $panel["inputs"] = array();

            $temp = array();
            $temp["tipo"] = 'select';
            $temp["mostrar"] = 'SI';
            $temp["protegido"] = 'NO';
            $temp["obligatorio"] = 'SI';
            $temp["encabezado"] = '';
            $temp["label"] = 'Tipo de local';
            $temp["id"] = 'tipolocal';
            $temp["name"] = 'tipolocal';
            $temp["type"] = 'text';
            $temp["size"] = '1';
            if (!isset($_SESSION["formulario"]["datos"]["tipolocal"])) {
                $_SESSION["formulario"]["datos"]["tipolocal"] = '';
            }
            $temp["value"] = $_SESSION["formulario"]["datos"]["tipolocal"];
            $temp["opc"] = array();

            $temp["opc"][] = array(
                'label' => 'Seleccione ...',
                'val' => '',
                'selected' => ''
            );


            $arr = array();
            $arr["0"] = 'NO PROPIO - ARRENDADO';
            $arr["1"] = 'PROPIO';


            foreach ($arr as $k => $v) {
                $tsel = '';
                if ($k == $_SESSION["formulario"]["datos"]["tipolocal"]) {
                    $tsel = 'SI';
                }
                $temp["opc"][] = array(
                'label' => $v,
                'val' => $k,
                'selected' => $tsel
            );
            }
            $temp["placeholder"] = 'Tipo de local';
            $panel["inputs"][] = $temp;

            $_SESSION["jsonsalida"]["formulario"][] = $panel;
        }

        // ************************************************************************************************************ //
        // Composición del CAPITAL
        // *********************************************************************************************************** //
        if ($tipo == 'pjur' || $tipo == 'esadl') {
            $panel = array();
            $panel["col"] = '';
            $panel["offset"] = '';
            $panel["titulo"] = 'COMPOSICION DEL CAPITAL';
            $panel["texto"] = '';
            $panel["inputs"] = array();

            // % Participacion nacional
            $temp = array();
            $temp["tipo"] = 'input';
            $temp["mostrar"] = 'SI';
            $temp["protegido"] = 'NO';
            $temp["obligatorio"] = 'SI';
            $temp["encabezado"] = '';
            $temp["label"] = '% de participación nacional';
            $temp["id"] = 'cap_porcnaltot';
            $temp["name"] = 'cap_porcnaltot';
            $temp["type"] = 'text';
            $temp["size"] = '20';
            $temp["maxlength"] = '30';
            $temp["minlength"] = '0';
            if (!isset($_SESSION["formulario"]["datos"]["cap_porcnaltot"])) {
                $_SESSION["formulario"]["datos"]["cap_porcnaltot"] = 0;
            }
            $temp["value"] = $_SESSION["formulario"]["datos"]["cap_porcnaltot"];
            $temp["placeholder"] = '% de participación nacional';
            $panel["inputs"][] = $temp;

            // % Participacion nacional - privado
            $temp = array();
            $temp["tipo"] = 'input';
            $temp["mostrar"] = 'SI';
            $temp["protegido"] = 'NO';
            $temp["obligatorio"] = 'SI';
            $temp["encabezado"] = '';
            $temp["label"] = '% de participación nacional (privado)';
            $temp["id"] = 'cap_porcnalpri';
            $temp["name"] = 'cap_porcnalpri';
            $temp["type"] = 'text';
            $temp["size"] = '20';
            $temp["maxlength"] = '30';
            $temp["minlength"] = '0';
            if (!isset($_SESSION["formulario"]["datos"]["cap_porcnalpri"])) {
                $_SESSION["formulario"]["datos"]["cap_porcnalpri"] = 0;
            }
            $temp["value"] = $_SESSION["formulario"]["datos"]["cap_porcnalpri"];
            $temp["placeholder"] = '% de participación nacional (privado)';
            $panel["inputs"][] = $temp;

            // % Participacion nacional - publico
            $temp = array();
            $temp["tipo"] = 'input';
            $temp["mostrar"] = 'SI';
            $temp["protegido"] = 'NO';
            $temp["obligatorio"] = 'SI';
            $temp["encabezado"] = '';
            $temp["label"] = '% de participación nacional (público)';
            $temp["id"] = 'cap_porcnalpub';
            $temp["name"] = 'cap_porcnalpub';
            $temp["type"] = 'text';
            $temp["size"] = '20';
            $temp["maxlength"] = '30';
            $temp["minlength"] = '0';
            if (!isset($_SESSION["formulario"]["datos"]["cap_porcnalpub"])) {
                $_SESSION["formulario"]["datos"]["cap_porcnalpub"] = 0;
            }
            $temp["value"] = $_SESSION["formulario"]["datos"]["cap_porcnalpub"];
            $temp["placeholder"] = '% de participación nacional (público)';
            $panel["inputs"][] = $temp;

            // % Participacion extranjero
            $temp = array();
            $temp["tipo"] = 'input';
            $temp["mostrar"] = 'SI';
            $temp["protegido"] = 'NO';
            $temp["obligatorio"] = 'SI';
            $temp["encabezado"] = '';
            $temp["label"] = '% de participación extranjero';
            $temp["id"] = 'cap_porcexttot';
            $temp["name"] = 'cap_porcexttot';
            $temp["type"] = 'text';
            $temp["size"] = '20';
            $temp["maxlength"] = '30';
            $temp["minlength"] = '0';
            if (!isset($_SESSION["formulario"]["datos"]["cap_porcexttot"])) {
                $_SESSION["formulario"]["datos"]["cap_porcexttot"] = 0;
            }
            $temp["value"] = $_SESSION["formulario"]["datos"]["cap_porcexttot"];
            $temp["placeholder"] = '% de participación extranjero';
            $panel["inputs"][] = $temp;

            // % Participacion extranjero - privado
            $temp = array();
            $temp["tipo"] = 'input';
            $temp["mostrar"] = 'SI';
            $temp["protegido"] = 'NO';
            $temp["obligatorio"] = 'SI';
            $temp["encabezado"] = '';
            $temp["label"] = '% de participación extranjero (privado)';
            $temp["id"] = 'cap_porcextpri';
            $temp["name"] = 'cap_porcextpri';
            $temp["type"] = 'text';
            $temp["size"] = '20';
            $temp["maxlength"] = '30';
            $temp["minlength"] = '0';
            if (!isset($_SESSION["formulario"]["datos"]["cap_porcextpri"])) {
                $_SESSION["formulario"]["datos"]["cap_porcextpri"] = 0;
            }
            $temp["value"] = $_SESSION["formulario"]["datos"]["cap_porcextpri"];
            $temp["placeholder"] = '% de participación extranjero (privado)';
            $panel["inputs"][] = $temp;

            // % Participacion extranjero - publico
            $temp = array();
            $temp["tipo"] = 'input';
            $temp["mostrar"] = 'SI';
            $temp["protegido"] = 'NO';
            $temp["obligatorio"] = 'SI';
            $temp["encabezado"] = '';
            $temp["label"] = '% de participación extranjero (público)';
            $temp["id"] = 'cap_porcextpub';
            $temp["name"] = 'cap_porcextpub';
            $temp["type"] = 'text';
            $temp["size"] = '20';
            $temp["maxlength"] = '30';
            $temp["minlength"] = '0';
            if (!isset($_SESSION["formulario"]["datos"]["cap_porcextpub"])) {
                $_SESSION["formulario"]["datos"]["cap_porcextpub"] = 0;
            }
            $temp["value"] = $_SESSION["formulario"]["datos"]["cap_porcextpub"];
            $temp["placeholder"] = '% de participación extranjero (público)';
            $panel["inputs"][] = $temp;

            $_SESSION["jsonsalida"]["formulario"][] = $panel;
        }


        // ************************************************************************************************************ //
        // Información de ESADL
        // *********************************************************************************************************** //
        if ($tipo == 'esadl') {
            $panel = array();
            $panel["col"] = '';
            $panel["offset"] = '';
            $panel["titulo"] = 'INFORMACION GENERAL DE ENTIDADES SIN ANIMO DE LUCRO';
            $panel["texto"] = '';
            $panel["inputs"] = array();

            // Número de asociados
            $temp = array();
            $temp["tipo"] = 'input';
            $temp["mostrar"] = 'SI';
            $temp["protegido"] = 'NO';
            $temp["obligatorio"] = 'SI';
            $temp["encabezado"] = '';
            $temp["label"] = 'Número total de asociados';
            $temp["id"] = 'ctresacntasociados';
            $temp["name"] = 'ctresacntasociados';
            $temp["type"] = 'text';
            $temp["size"] = '4';
            $temp["maxlength"] = '4';
            $temp["minlength"] = '1';
            if (!isset($_SESSION["formulario"]["datos"]["ctresacntasociados"])) {
                $_SESSION["formulario"]["datos"]["ctresacntasociados"] = 0;
            }
            $temp["value"] = $_SESSION["formulario"]["datos"]["ctresacntasociados"];
            $temp["placeholder"] = 'Número total de asociados';
            $temp["cssValidator"] = 'numeros';
            $panel["inputs"][] = $temp;

            // Número de asociados - mujeres
            $temp = array();
            $temp["tipo"] = 'input';
            $temp["mostrar"] = 'SI';
            $temp["protegido"] = 'NO';
            $temp["obligatorio"] = 'SI';
            $temp["encabezado"] = '';
            $temp["label"] = 'Número total de asociados (mujeres)';
            $temp["id"] = 'ctresacntmujeres';
            $temp["name"] = 'ctresacntmujeres';
            $temp["type"] = 'text';
            $temp["size"] = '4';
            $temp["maxlength"] = '4';
            $temp["minlength"] = '1';
            if (!isset($_SESSION["formulario"]["datos"]["ctresacntmujeres"])) {
                $_SESSION["formulario"]["datos"]["ctresacntmujeres"] = 0;
            }
            $temp["value"] = $_SESSION["formulario"]["datos"]["ctresacntmujeres"];
            $temp["placeholder"] = 'Número total de asociados (mujeres)';
            $temp["cssValidator"] = 'numeros';
            $panel["inputs"][] = $temp;

            // Número de asociados - hombres
            $temp = array();
            $temp["tipo"] = 'input';
            $temp["mostrar"] = 'SI';
            $temp["protegido"] = 'NO';
            $temp["obligatorio"] = 'SI';
            $temp["encabezado"] = '';
            $temp["label"] = 'Número total de asociados (hombres)';
            $temp["id"] = 'ctresacnthombres';
            $temp["name"] = 'ctresacnthombres';
            $temp["type"] = 'text';
            $temp["size"] = '4';
            $temp["maxlength"] = '4';
            $temp["minlength"] = '1';
            if (!isset($_SESSION["formulario"]["datos"]["ctresacnthombres"])) {
                $_SESSION["formulario"]["datos"]["ctresacnthombres"] = 0;
            }
            $temp["value"] = $_SESSION["formulario"]["datos"]["ctresacnthombres"];
            $temp["placeholder"] = 'Número total de asociados (hombres)';
            $temp["cssValidator"] = 'numeros';
            $panel["inputs"][] = $temp;

            // Pertenencia a un gremio
            $temp = array();
            $temp["tipo"] = 'select';
            $temp["mostrar"] = 'SI';
            $temp["protegido"] = 'NO';
            $temp["obligatorio"] = 'SI';
            $temp["encabezado"] = '';
            $temp["label"] = 'Pertenece a algún gremio';
            $temp["id"] = 'ctresapertgremio';
            $temp["name"] = 'ctresapertgremio';
            $temp["type"] = 'text';
            $temp["size"] = '6';
            if (!isset($_SESSION["formulario"]["datos"]["ctresapertgremio"])) {
                $_SESSION["formulario"]["datos"]["ctresapertgremio"] = '';
            }
            $temp["value"] = $_SESSION["formulario"]["datos"]["ctresapertgremio"];
            $arr = array();
            $arr[""] = "No reporta";
            $arr["S"] = "Si pertenece";
            $arr["N"] = "No pertenece";

            $temp["opc"] = array();

            $temp["opc"][] = array(
                'label' => 'Seleccione ...',
                'val' => '',
                'selected' => ''
            );


            foreach ($arr as $k => $v) {
                $tsel = '';
                if (trim($k) == trim((string)$_SESSION["formulario"]["datos"]["ctresapertgremio"])) {
                    $tsel = 'SI';
                }
                $temp["opc"][] = array(
                'label' => $v,
                'val' => $k,
                'selected' => $tsel
            );
            }
            $temp["placeholder"] = 'Pertenece a algún gremio';
            $panel["inputs"][] = $temp;

            // Cual gremio
            $temp = array();
            $temp["tipo"] = 'input';
            $temp["mostrar"] = 'SI';
            $temp["protegido"] = 'NO';
            $temp["obligatorio"] = 'NO';
            $temp["encabezado"] = 'En caso e haber indicado que pertenece a algún gremio, por favor indique a cual';
            $temp["label"] = 'Cual gremio';
            $temp["id"] = 'ctresagremio';
            $temp["name"] = 'ctresagremio';
            $temp["type"] = 'text';
            $temp["size"] = '30';
            $temp["maxlength"] = '30';
            $temp["minlength"] = '0';
            if (!isset($_SESSION["formulario"]["datos"]["ctresagremio"])) {
                $_SESSION["formulario"]["datos"]["ctresagremio"] = '';
            }
            $temp["value"] = $_SESSION["formulario"]["datos"]["ctresagremio"];
            $temp["placeholder"] = 'Cual gremio';
            $temp["cssValidator"] = 'validarCaracteres';
            $panel["inputs"][] = $temp;

            // Entidad acreditada que impartió el curso básico de economía solidaria
            $temp = array();
            $temp["tipo"] = 'input';
            $temp["mostrar"] = 'SI';
            $temp["protegido"] = 'NO';
            $temp["obligatorio"] = 'NO';
            $temp["encabezado"] = '';
            $temp["label"] = 'Entidad acreditada que impartió el curso básico de economía solidaria - cuando sea del caso';
            $temp["id"] = 'ctresaacredita';
            $temp["name"] = 'ctresaacredita';
            $temp["type"] = 'text';
            $temp["size"] = '128';
            $temp["maxlength"] = '150';
            $temp["minlength"] = '0';
            if (!isset($_SESSION["formulario"]["datos"]["ctresaacredita"])) {
                $_SESSION["formulario"]["datos"]["ctresaacredita"] = '';
            }
            $temp["value"] = $_SESSION["formulario"]["datos"]["ctresaacredita"];
            $temp["placeholder"] = 'Entidad acreditada que impartió el curso básico de economía solidaria - cuando sea del caso';
            $temp["cssValidator"] = 'validarCaracteres';
            $panel["inputs"][] = $temp;

            // Entidad que ejerce inspección, vigilancia y control - cuando sea del caso
            $temp = array();
            $temp["tipo"] = 'input';
            $temp["mostrar"] = 'SI';
            $temp["protegido"] = 'NO';
            $temp["obligatorio"] = 'NO';
            $temp["encabezado"] = '';
            $temp["label"] = 'Entidad que ejerce inspección, vigilancia y control - cuando sea del caso';
            $temp["id"] = 'ctresaivc';
            $temp["name"] = 'ctresaivc';
            $temp["type"] = 'text';
            $temp["size"] = '128';
            $temp["maxlength"] = '150';
            $temp["minlength"] = '0';
            if (!isset($_SESSION["formulario"]["datos"]["ctresaivc"])) {
                $_SESSION["formulario"]["datos"]["ctresaivc"] = '';
            }
            $temp["value"] = $_SESSION["formulario"]["datos"]["ctresaivc"];
            $temp["placeholder"] = 'Entidad que ejerce inspección, vigilancia y control - cuando sea del caso';
            $temp["cssValidator"] = 'validarCaracteres';
            $panel["inputs"][] = $temp;

            // Ha remitido información a entidades del IVC
            $temp = array();
            $temp["tipo"] = 'select';
            $temp["mostrar"] = 'SI';
            $temp["protegido"] = 'NO';
            $temp["obligatorio"] = 'SI';
            $temp["encabezado"] = '';
            $temp["label"] = 'Ha remitido documentación al ente de inspección, vigilancia y control';
            $temp["id"] = 'ctresainfoivc';
            $temp["name"] = 'ctresainfoivc';
            $temp["type"] = 'text';
            $temp["size"] = '6';
            if (!isset($_SESSION["formulario"]["datos"]["ctresainfoivc"])) {
                $_SESSION["formulario"]["datos"]["ctresainfoivc"] = '';
            }
            $temp["value"] = $_SESSION["formulario"]["datos"]["ctresainfoivc"];
            $arr = array();
            $arr[""] = "No reporta";
            $arr["S"] = "Si ha remitido";
            $arr["N"] = "No ha remitido";
            $temp["opc"] = array();

            $temp["opc"][] = array(
                'label' => 'Seleccione ...',
                'val' => '',
                'selected' => ''
            );


            foreach ($arr as $k => $v) {
                $tsel = '';
                if (trim($k) == trim((string)_SESSION["formulario"]["datos"]["ctresainfoivc"])) {
                    $tsel = 'SI';
                }
                $temp["opc"][] = array(
                'label' => $v,
                'val' => $k,
                'selected' => $tsel
            );
            }
            $temp["placeholder"] = 'Ha remitido documentación al ente de inspección, vigilancia y control';
            $panel["inputs"][] = $temp;

            // Requiere autorización de registro
            $temp = array();
            $temp["tipo"] = 'select';
            $temp["mostrar"] = 'SI';
            $temp["protegido"] = 'NO';
            $temp["obligatorio"] = 'SI';
            $temp["encabezado"] = '';
            $temp["label"] = 'Requiere autorización de registro';
            $temp["id"] = 'ctresaautregistro';
            $temp["name"] = 'ctresaautregistro';
            $temp["type"] = 'text';
            $temp["size"] = '6';
            if (!isset($_SESSION["formulario"]["datos"]["ctresaautregistro"])) {
                $_SESSION["formulario"]["datos"]["ctresaautregistro"] = '';
            }
            $temp["value"] = $_SESSION["formulario"]["datos"]["ctresaautregistro"];
            $arr = array();
            $arr[""] = "No aplica";
            $arr["S"] = "Si requiere";
            $arr["N"] = "No requiere";

            $temp["opc"] = array();

            $temp["opc"][] = array(
                'label' => 'Seleccione ...',
                'val' => '',
                'selected' => ''
            );

            foreach ($arr as $k => $v) {
                $tsel = '';
                if (trim($k) == trim((string)$_SESSION["formulario"]["datos"]["ctresaautregistro"])) {
                    $tsel = 'SI';
                }
                $temp["opc"][] = array(
                'label' => $v,
                'val' => $k,
                'selected' => $tsel
            );
            }
            $temp["placeholder"] = 'Requiere autorización de registro';
            $panel["inputs"][] = $temp;

            // Entidad de registro
            $temp = array();
            $temp["tipo"] = 'input';
            $temp["mostrar"] = 'SI';
            $temp["protegido"] = 'NO';
            $temp["obligatorio"] = 'NO';
            $temp["encabezado"] = '';
            $temp["label"] = 'Entidad que autoriza el registro - cuando sea del caso';
            $temp["id"] = 'ctresaentautoriza';
            $temp["name"] = 'ctresaentautoriza';
            $temp["type"] = 'text';
            $temp["size"] = '128';
            $temp["maxlength"] = '150';
            $temp["minlength"] = '0';
            if (!isset($_SESSION["formulario"]["datos"]["ctresaentautoriza"])) {
                $_SESSION["formulario"]["datos"]["ctresaentautoriza"] = '';
            }
            $temp["value"] = $_SESSION["formulario"]["datos"]["ctresaentautoriza"];
            $temp["placeholder"] = 'Entidad que autoriza el registro - cuando sea del caso';
            $temp["cssValidator"] = 'validarCaracteres';
            $panel["inputs"][] = $temp;

            $_SESSION["jsonsalida"]["formulario"][] = $panel;
        }

        // ************************************************************************************************************ //
        // Clase de entidad sin ánimo de lucro
        // *********************************************************************************************************** //
        if ($tipo == 'esadl') {
            $panel = array();
            $panel["col"] = '';
            $panel["offset"] = '';
            $panel["titulo"] = 'CLASIFICACIÓN DE LA ENTIDAD SIN ANIMO DE LUCRO';
            $panel["texto"] = '';
            $panel["inputs"] = array();
            // Naturaleza
            $temp = array();
            $temp["tipo"] = 'select';
            $temp["mostrar"] = 'SI';
            $temp["protegido"] = 'NO';
            $temp["obligatorio"] = 'SI';
            $temp["encabezado"] = '';
            $temp["label"] = 'Clasificación (Naturaleza)';
            $temp["id"] = 'ctresacodnat';
            $temp["name"] = 'ctresacodnat';
            $temp["type"] = 'text';
            $temp["size"] = '6';
            if (!isset($_SESSION["formulario"]["datos"]["ctresacodnat"])) {
                $_SESSION["formulario"]["datos"]["ctresacodnat"] = '';
            }
            $temp["value"] = $_SESSION["formulario"]["datos"]["ctresacodnat"];
            if ($_SESSION["formulario"]["datos"]["organizacion"] == '12') {
                $arr = array(
                '1' => '1 - FUNDACION',
                '2' => '2 - ASOCIACION',
                '3' => '3 - CORPORACION'
            );
            } else {
                $arr = array(
                '4' => '4 - ECONOMIA SOLIDARIA'
            );
            }
            $temp["opc"] = array();

            $temp["opc"][] = array(
                'label' => 'Seleccione ...',
                'val' => '',
                'selected' => ''
            );

            foreach ($arr as $k => $v) {
                $tsel = '';
                if (trim($k) == trim((string)$_SESSION["formulario"]["datos"]["ctresacodnat"])) {
                    $tsel = 'SI';
                }
                $temp["opc"][] = array(
                'label' => $v,
                'val' => $k,
                'selected' => $tsel
            );
            }
            $temp["placeholder"] = 'Clasificación (Naturaleza)';
            $panel["inputs"][] = $temp;

            // Clase especifica
            $temp = array();
            $temp["tipo"] = 'select';
            $temp["mostrar"] = 'SI';
            $temp["protegido"] = 'NO';
            $temp["obligatorio"] = 'SI';
            $temp["encabezado"] = '';
            $temp["label"] = 'Tipo de ESADL?';
            $temp["id"] = 'claseespesadl';
            $temp["name"] = 'claseespesadl';
            $temp["type"] = 'text';
            $temp["size"] = '6';
            if (!isset($_SESSION["formulario"]["datos"]["claseespesadl"])) {
                $_SESSION["formulario"]["datos"]["claseespesadl"] = '';
            }
            $temp["value"] = $_SESSION["formulario"]["datos"]["claseespesadl"];
            $temx = retornarRegistrosMysqliApi($mysqli, 'mreg_clase_esadl', "1=1", "descripcion");
            //die("<pre>".print_r($temx, true)."</pre>");
            $temp["opc"] = array();

            $temp["opc"][] = array(
                'label' => 'Seleccione ...',
                'val' => '',
                'selected' => ''
            );

            foreach ($temx as $tx) {
                $tsel = '';
                if (trim($tx["id"]) == trim((string)$_SESSION["formulario"]["datos"]["claseespesadl"])) {
                    $tsel = 'SI';
                }
                $temp["opc"][] = array(
                'label' => $tx["codigorues"].' - '.$tx["descripcion"],
                'val' => $tx["id"],
                'selected' => $tsel
            );
            }
            $temp["placeholder"] = 'Clase de Entidad sin ánimo de lucro';
            //die("<pre>".print_r($_SESSION["formulario"]["datos"], true)."</pre>");
            $panel["inputs"][] = $temp;

            // Clase especifica de econsoli
            $temp = array();
            $temp["tipo"] = 'select';
            if ($_SESSION["formulario"]["datos"]["organizacion"] == '12') {
                $temp["mostrar"] = 'NO';
                $temp["protegido"] = 'NO';
                $temp["obligatorio"] = 'NO';
            } else {
                $temp["mostrar"] = 'SI';
                $temp["protegido"] = 'NO';
                $temp["obligatorio"] = 'SI';
            }
            $temp["encabezado"] = '';
            $temp["label"] = 'Clase de entidad de economía solidaria';
            $temp["id"] = 'claseeconsoli';
            $temp["name"] = 'claseeconsoli';
            $temp["type"] = 'text';
            $temp["size"] = '6';
            if (!isset($_SESSION["formulario"]["datos"]["claseeconsoli"])) {
                $_SESSION["formulario"]["datos"]["claseeconsoli"] = '';
            }
            $temp["value"] = $_SESSION["formulario"]["datos"]["claseeconsoli"];
            $temx = retornarRegistrosMysqliApi($mysqli, 'mreg_clase_econsoli', "1=1", "descripcion");
            $temp["opc"] = array();

            $temp["opc"][] = array(
                'label' => 'Seleccione ...',
                'val' => '',
                'selected' => ''
            );

            foreach ($temx as $tx) {
                $tsel = '';
                if (trim($tx["id"]) == trim((string)$_SESSION["formulario"]["datos"]["claseeconsoli"])) {
                    $tsel = 'SI';
                }
                $temp["opc"][] = array(
                'label' => $tx["descripcion"],
                'val' => $tx["id"],
                'selected' => $tsel
            );
            }
            $temp["placeholder"] = 'Clase de entidad de economía solidaria';
            $panel["inputs"][] = $temp;

            $_SESSION["jsonsalida"]["formulario"][] = $panel;
        }

        // ************************************************************************************************************ //
        // Datos adicionales de esadl
        // *********************************************************************************************************** //
        if ($tipo == 'esadl') {
            $panel = array();
            $panel["col"] = '';
            $panel["offset"] = '';
            $panel["titulo"] = 'INFORMACIÓN ADICIONAL DE LAS ENTIDADES SIN ANIMO DE LUCRO';
            $panel["texto"] = '';
            $panel["inputs"] = array();


            // personal con discapacidad
            $temp = array();
            $temp["tipo"] = 'select';
            $temp["mostrar"] = 'SI';
            $temp["protegido"] = 'NO';
            $temp["obligatorio"] = 'SI';
            $temp["encabezado"] = '';
            $temp["label"] = 'Tiene personal contratado que tenga alguna discapacidad?';
            $temp["id"] = 'ctresadiscap';
            $temp["name"] = 'ctresadiscap';
            $temp["type"] = 'text';
            $temp["size"] = '6';
            if (!isset($_SESSION["formulario"]["datos"]["ctresadiscap"])) {
                $_SESSION["formulario"]["datos"]["ctresadiscap"] = '';
            }
            $temp["value"] = $_SESSION["formulario"]["datos"]["ctresadiscap"];
            $arr = array(
            '' => 'No aplica',
            'S' => 'Si tiene personal con discapacidad',
            'N' => 'No tiene'
        );
            $temp["opc"] = array();

            $temp["opc"][] = array(
                'label' => 'Seleccione ...',
                'val' => '',
                'selected' => ''
            );

            foreach ($arr as $k => $v) {
                $tsel = '';
                if (trim($k) == trim((string)$_SESSION["formulario"]["datos"]["ctresadiscap"])) {
                    $tsel = 'SI';
                }
                $temp["opc"][] = array(
                'label' => $v,
                'val' => $k,
                'selected' => $tsel
            );
            }
            $temp["placeholder"] = 'Tiene personal contratado que tenga alguna discapacidad?';
            $panel["inputs"][] = $temp;

            // personal de etnias
            $temp = array();
            $temp["tipo"] = 'select';
            $temp["mostrar"] = 'SI';
            $temp["protegido"] = 'NO';
            $temp["obligatorio"] = 'SI';
            $temp["encabezado"] = '';
            $temp["label"] = 'Tiene personal contratado que pertenezca a etnias';
            $temp["id"] = 'ctresaetnia';
            $temp["name"] = 'ctresaetnia';
            $temp["type"] = 'text';
            $temp["size"] = '6';
            if (!isset($_SESSION["formulario"]["datos"]["ctresaetnia"])) {
                $_SESSION["formulario"]["datos"]["ctresaetnia"] = '';
            }
            $temp["value"] = $_SESSION["formulario"]["datos"]["ctresaetnia"];
            $arr = array(
            '' => 'No aplica',
            'S' => 'Si tiene personal perteneciente a etnias',
            'N' => 'No tiene'
        );
            $temp["opc"] = array();

            $temp["opc"][] = array(
                'label' => 'Seleccione ...',
                'val' => '',
                'selected' => ''
            );

            foreach ($arr as $k => $v) {
                $tsel = '';
                if (trim($k) == trim((string)$_SESSION["formulario"]["datos"]["ctresaetnia"])) {
                    $tsel = 'SI';
                }
                $temp["opc"][] = array(
                'label' => $v,
                'val' => $k,
                'selected' => $tsel
            );
            }
            $temp["placeholder"] = 'Tiene personal contratado que pertenezca a etnias';
            $panel["inputs"][] = $temp;

            // Cual etnia
            $temp = array();
            $temp["tipo"] = 'input';
            $temp["mostrar"] = 'SI';
            $temp["protegido"] = 'NO';
            $temp["obligatorio"] = 'NO';
            $temp["encabezado"] = 'En caso de haber indicado que tiene personal vinculado perteneciente a alguna etnia ...';
            $temp["label"] = 'Cual etnia?';
            $temp["id"] = 'ctresaetnia';
            $temp["name"] = 'ctresaetnia';
            $temp["type"] = 'text';
            $temp["size"] = '30';
            $temp["maxlength"] = '30';
            $temp["minlength"] = '0';
            if (!isset($_SESSION["formulario"]["datos"]["ctresaetnia"])) {
                $_SESSION["formulario"]["datos"]["ctresaetnia"] = '';
            }
            $temp["value"] = $_SESSION["formulario"]["datos"]["ctresaetnia"];
            $temp["placeholder"] = 'Cual etnia?';
            $temp["cssValidator"] = 'validarCaracteres';
            $panel["inputs"][] = $temp;

            // personal lgbti
            $temp = array();
            $temp["tipo"] = 'select';
            $temp["mostrar"] = 'SI';
            $temp["protegido"] = 'NO';
            $temp["obligatorio"] = 'SI';
            $temp["encabezado"] = '';
            $temp["label"] = 'Tiene personal contratado que pertenezca a la comunidad LGBTI';
            $temp["id"] = 'ctresalgbti';
            $temp["name"] = 'ctresalgbti';
            $temp["type"] = 'text';
            $temp["size"] = '6';
            if (!isset($_SESSION["formulario"]["datos"]["ctresalgbti"])) {
                $_SESSION["formulario"]["datos"]["ctresalgbti"] = '';
            }
            $temp["value"] = $_SESSION["formulario"]["datos"]["ctresalgbti"];
            $arr = array(
            '' => 'No aplica',
            'S' => 'Si tiene personal lgbti',
            'N' => 'No tiene'
        );
            $temp["opc"] = array();

            $temp["opc"][] = array(
                'label' => 'Seleccione ...',
                'val' => '',
                'selected' => ''
            );

            foreach ($arr as $k => $v) {
                $tsel = '';
                if (trim($k) == trim((string)$_SESSION["formulario"]["datos"]["ctresalgbti"])) {
                    $tsel = 'SI';
                }
                $temp["opc"][] = array(
                'label' => $v,
                'val' => $k,
                'selected' => $tsel
            );
            }
            $temp["placeholder"] = 'Tiene personal contratado que pertenezca a la comunidad LGBTI';
            $panel["inputs"][] = $temp;

            // personal en situacion de desplazados victimas o reintsertados
            $temp = array();
            $temp["tipo"] = 'select';
            $temp["mostrar"] = 'SI';
            $temp["protegido"] = 'NO';
            $temp["obligatorio"] = 'SI';
            $temp["encabezado"] = '';
            $temp["label"] = 'Tiene personal vinculado en condición de desplazado, víctima o reinsertado';
            $temp["id"] = 'ctresadespvictreins';
            $temp["name"] = 'ctresadespvictreins';
            $temp["type"] = 'text';
            $temp["size"] = '6';
            if (!isset($_SESSION["formulario"]["datos"]["ctresadespvictreins"])) {
                $_SESSION["formulario"]["datos"]["ctresadespvictreins"] = '';
            }
            $temp["value"] = $_SESSION["formulario"]["datos"]["ctresadespvictreins"];
            $arr = array(
            '' => 'No aplica',
            'S' => 'Si tiene personal en situación de vulnerabilidad',
            'N' => 'No tiene'
        );
            $temp["opc"] = array();

            $temp["opc"][] = array(
                'label' => 'Seleccione ...',
                'val' => '',
                'selected' => ''
            );

            foreach ($arr as $k => $v) {
                $tsel = '';
                if (trim($k) == trim((string)$_SESSION["formulario"]["datos"]["ctresadespvictreins"])) {
                    $tsel = 'SI';
                }
                $temp["opc"][] = array(
                'label' => $v,
                'val' => $k,
                'selected' => $tsel
            );
            }
            $temp["placeholder"] = 'Tiene personal vinculado en condición de desplazado, víctima o reinsertado';
            $panel["inputs"][] = $temp;

            // que situación de vulnerabilidad
            $temp = array();
            $temp["tipo"] = 'input';
            $temp["mostrar"] = 'SI';
            $temp["protegido"] = 'NO';
            $temp["obligatorio"] = 'NO';
            $temp["encabezado"] = 'En caso de haber indicado que tiene personal vinculado en condición de vulnerabilidad ...';
            $temp["label"] = 'Cual condición?';
            $temp["id"] = 'ctresacualdespvictreins';
            $temp["name"] = 'ctresacualdespvictreins';
            $temp["type"] = 'text';
            $temp["size"] = '30';
            $temp["maxlength"] = '30';
            $temp["minlength"] = '0';
            if (!isset($_SESSION["formulario"]["datos"]["ctresacualdespvictreins"])) {
                $_SESSION["formulario"]["datos"]["ctresacualdespvictreins"] = '';
            }
            $temp["value"] = $_SESSION["formulario"]["datos"]["ctresacualdespvictreins"];
            $temp["placeholder"] = 'Cual condición?';
            $temp["cssValidator"] = 'validarCaracteres';
            $panel["inputs"][] = $temp;

            // Indicadores de gestión
            $temp = array();
            $temp["tipo"] = 'select';
            $temp["mostrar"] = 'SI';
            $temp["protegido"] = 'NO';
            $temp["obligatorio"] = 'SI';
            $temp["encabezado"] = '';
            $temp["label"] = 'Cuenta con indicadores de gestión';
            $temp["id"] = 'ctresaindgest';
            $temp["name"] = 'ctresaindgest';
            $temp["type"] = 'text';
            $temp["size"] = '6';
            if (!isset($_SESSION["formulario"]["datos"]["ctresaindgest"])) {
                $_SESSION["formulario"]["datos"]["ctresaindgest"] = '';
            }
            $temp["value"] = $_SESSION["formulario"]["datos"]["ctresaindgest"];
            $arr = array(
            '' => 'No aplica',
            'S' => 'Si tiene',
            'N' => 'No tiene'
        );
            $temp["opc"] = array();

            $temp["opc"][] = array(
                'label' => 'Seleccione ...',
                'val' => '',
                'selected' => ''
            );

            foreach ($arr as $k => $v) {
                $tsel = '';
                if (trim($k) == trim((string)$_SESSION["formulario"]["datos"]["ctresaindgest"])) {
                    $tsel = 'SI';
                }
                $temp["opc"][] = array(
                'label' => $v,
                'val' => $k,
                'selected' => $tsel
            );
            }
            $temp["placeholder"] = 'Cuenta con indicadores de gestión';
            $panel["inputs"][] = $temp;


            //
            $_SESSION["jsonsalida"]["formulario"][] = $panel;
        }

        // ************************************************************************************************************ //
        // Datos de Ley 1780
        // *********************************************************************************************************** //
        if ($tipo == 'pnat' || $tipo == 'pjur' || $tipo == 'esadl') {
            $panel = array();
            $panel["titulo"] = 'LEY 1780 de 2016';
            $panel["col"] = '6';
            $panel["offset"] = '';
            $panel["texto"] = '';
            $panel["inputs"] = array();

            // Cumple con los requisitos
            $temp = array();
            $temp["tipo"] = 'select';
            $temp["mostrar"] = 'SI';
            $temp["protegido"] = 'SI';
            $temp["obligatorio"] = 'NO';
            $temp["encabezado"] = 'Declaro bajo la gravedad de juramento que cumplo con los requisitos establecidos en la Ley 1780 de 2016 para acceder a los beneficios del artículo 3';
            $temp["label"] = 'Cumplo?';
            $temp["id"] = 'cumplerequisitos1780';
            $temp["name"] = 'cumplerequisitos1780';
            $temp["type"] = 'text';
            $temp["size"] = '6';
            if (!isset($_SESSION["formulario"]["datos"]["cumplerequisitos1780"])) {
                $_SESSION["formulario"]["datos"]["cumplerequisitos1780"] =  $_SESSION ["tramite"]["cumplorequisitosbenley1780"];
            }
            $temp["value"] = $_SESSION["formulario"]["datos"]["cumplerequisitos1780"];
            $arr = array(
            '' => 'No reporta',
            'S' => 'Si cumplo',
            'N' => 'No cumplo'
        );
            $temp["opc"] = array();

            foreach ($arr as $k => $v) {
                $tsel = '';
                if (trim($k) == trim((string)$_SESSION["formulario"]["datos"]["cumplerequisitos1780"])) {
                    $tsel = 'SI';
                }
                $temp["opc"][] = array(
                'label' => $v,
                'val' => $k,
                'selected' => $tsel
            );
            }
            $temp["placeholder"] = 'Cumplo?';
            $panel["inputs"][] = $temp;

            // Mantengo los requisitos
            $temp = array();
            $temp["tipo"] = 'select';
            $temp["mostrar"] = 'SI';
            $temp["protegido"] = 'SI';
            $temp["obligatorio"] = 'NO';
            $temp["encabezado"] = 'Manifiesto bajo la gravedad de juramento que mantengo el cumplimiento de los requisitos establecidos en el numeral 2.2.2.41.5.2 del decreto reglamentario de la Ley 1780 de 2016 para acceder a los beneficios del artículo 3';
            $temp["label"] = 'Mantengo?';
            $temp["id"] = 'cumplerequisitos1780primren';
            $temp["name"] = 'cumplerequisitos1780primren';
            $temp["type"] = 'text';
            $temp["size"] = '6';
            if (!isset($_SESSION["formulario"]["datos"]["cumplerequisitos1780primren"])) {
                $_SESSION["formulario"]["datos"]["cumplerequisitos1780primren"] = $_SESSION ["tramite"] ["mantengorequisitosbenley1780"];
            }
            $temp["value"] = $_SESSION["formulario"]["datos"]["cumplerequisitos1780primren"];
            $arr = array(
            '' => 'No reporta',
            'S' => 'Si mantengo',
            'N' => 'No mantengo'
        );
            $temp["opc"] = array();

            foreach ($arr as $k => $v) {
                $tsel = '';
                if (trim($k) == trim((string)$_SESSION["formulario"]["datos"]["cumplerequisitos1780primren"])) {
                    $tsel = 'SI';
                }
                $temp["opc"][] = array(
                'label' => $v,
                'val' => $k,
                'selected' => $tsel
            );
            }
            $temp["placeholder"] = 'Mantengo?';
            $panel["inputs"][] = $temp;

            //
            $_SESSION["jsonsalida"]["formulario"][] = $panel;
        }

        // ************************************************************************************************************ //
        // Seguridad social
        // *********************************************************************************************************** //
        if ($tipo == 'pnat' || $tipo == 'pjur' || $tipo == 'esadl') {
            $panel = array();
            $panel["titulo"] = 'PROTECCIÓN SOCIAL';
            $panel["texto"] = '';
            $panel["col"] = '6';
            $panel["offset"] = '';
            $panel["inputs"] = array();

            // aportante
            $temp = array();
            $temp["tipo"] = 'select';
            $temp["mostrar"] = 'SI';
            $temp["protegido"] = 'NO';
            $temp["obligatorio"] = 'NO';
            $temp["encabezado"] = '';
            $temp["label"] = 'Es aportante?';
            $temp["id"] = 'aportantesegsocial';
            $temp["name"] = 'aportantesegsocial';
            $temp["type"] = 'text';
            $temp["size"] = '6';
            if (!isset($_SESSION["formulario"]["datos"]["aportantesegsocial"])) {
                $_SESSION["formulario"]["datos"]["aportantesegsocial"] = '';
            }
            $temp["value"] = $_SESSION["formulario"]["datos"]["aportantesegsocial"];
            $arr = array(
            '' => 'No reporta',
            'S' => 'Si aporta',
            'N' => 'No aporta'
        );
            $temp["opc"] = array();

            foreach ($arr as $k => $v) {
                $tsel = '';
                if (trim($k) == trim((string)$_SESSION["formulario"]["datos"]["aportantesegsocial"])) {
                    $tsel = 'SI';
                }
                $temp["opc"][] = array(
                'label' => $v,
                'val' => $k,
                'selected' => $tsel
            );
            }
            $temp["placeholder"] = 'Es aportante?';
            $panel["inputs"][] = $temp;

            // aportante
            $temp = array();
            $temp["tipo"] = 'select';
            $temp["mostrar"] = 'SI';
            $temp["protegido"] = 'NO';
            $temp["obligatorio"] = 'SI';
            $temp["encabezado"] = '';
            $temp["label"] = 'Tipo de aportante';
            $temp["id"] = 'tipoaportantesegsocial';
            $temp["name"] = 'tipoaportantesegsocial';
            $temp["type"] = 'text';
            $temp["size"] = '6';
            if (!isset($_SESSION["formulario"]["datos"]["tipoaportantesegsocial"])) {
                $_SESSION["formulario"]["datos"]["tipoaportantesegsocial"] = '';
            }
            $temp["value"] = $_SESSION["formulario"]["datos"]["tipoaportantesegsocial"];
            $arr = array(
             // '' => 'Seleccione ...',
              '0' => 'No reporta',
              '1' => '200 o más cotizantes',
              '2' => 'Menos de 200 cotizantes',
              '3' => 'Beneficiario art. 5 Ley 1429/2010',
              '4' => 'Aportante independiente'
            );
            $temp["opc"] = array();

            $temp["opc"][] = array(
                'label' => 'Seleccione ...',
                'val' => '',
                'selected' => ''
            );

            foreach ($arr as $k => $v) {
                $tsel = '';
                if (trim($k) == trim((string)$_SESSION["formulario"]["datos"]["tipoaportantesegsocial"])) {
                    $tsel = 'SI';
                }
                $temp["opc"][] = array(
                'label' => $v,
                'val' => $k,
                'selected' => $tsel
            );
            }
            $temp["placeholder"] = 'Tipo de aportante';
            $panel["inputs"][] = $temp;

            //
            $_SESSION["jsonsalida"]["formulario"][] = $panel;
        }

        // ************************************************************************************************************ //
        // INFORMACIÓN DEL PROPIETARIO
        // *********************************************************************************************************** //
        if (count($_SESSION["formulario"]["datos"]["propietarios"])>0) {
            if ($_SESSION["tramite"]["tipotramite"] === 'renovacionmatricula' || $_SESSION["tramite"]["tipotramite"] === 'renovacionesadl') {
                for ($i=1; $i < (count($_SESSION["formulario"]["datos"]["propietarios"])+1); $i++) {
                    $protegido = "SI";
                    $obligatorio = "NO";
                    if ($_SESSION["entrada"]["codigoempresa"] !== $_SESSION["formulario"]["datos"]["propietarios"][$i]["camarapropietario"]) {
                        $protegido = "NO";
                        $obligatorio = "SI";
                    }
                    // Panel principal
                    $panel = array();
                    $panel["titulo"] = 'INFORMACIÓN DEL PROPIETARIO Nro. '. $i;
                    $panel["avisotop"] = array();
                    $panel["avisotop"]["texto"] = 'Por favor complemente a continuación la información del propietario.';
                    $panel["avisotop"]["color"] = 'warning';
                    $panel["grupos"] = array();

                    // Sub panel
                    // Datos basicos
                    $subpanel = array();
                    $subpanel["col"] = '6';
                    $subpanel["offset"] = '';
                    $subpanel["titulo"] = 'Datos básicos';
                    $subpanel["inputs"] = array();

                    // Organización jurídica del propietario
                    $temp = array();
                    $temp["tipo"] = 'select';
                    $temp["mostrar"] = 'SI';
                    $temp["protegido"] = $protegido;
                    $temp["obligatorio"] = $obligatorio;
                    $temp["encabezado"] = '';
                    $temp["label"] = 'Organización jurídica del propietario';
                    $temp["id"] = 'prop_'.$i.'_organizacionpropietario';
                    $temp["name"] = 'prop_'.$i.'_organizacionpropietario';
                    $temp["type"] = 'text';
                    $temp["size"] = '2';
                    $temp["value"] = $_SESSION["formulario"]["datos"]["propietarios"][$i]["organizacionpropietario"];
                    $temp["opc"] = array();
                    $temp["opc"][] = array(
                        'label' => 'Seleccione ...',
                        'val' => '',
                        'selected' => ''
                    );
                    $temx = retornarRegistrosMysqli2($mysqli, "bas_organizacionjuridica", "1=1", "id");
                    foreach ($temx as $tx) {
                        $tsel = '';
                        if ($tx["id"] == $_SESSION["formulario"]["datos"]["propietarios"][$i]["organizacionpropietario"]) {
                            $tsel = 'SI';
                        }
                        $temp["opc"][] = array(
                    'label' => $tx["id"] . ' - ' . strtoupper($tx["descripcion"]),
                    'val' => $tx["id"],
                    'selected' => $tsel
                );
                    }
                    $temp["placeholder"] = 'Organización jurídica del propietario';
                    $subpanel["inputs"][] = $temp;

                    // Cámara de Comercio del propietario
                    $temp = array();
                    $temp["tipo"] = 'select';
                    $temp["mostrar"] = 'SI';
                    $temp["protegido"] = $protegido;
                    $temp["obligatorio"] = $obligatorio;
                    $temp["encabezado"] = '';
                    $temp["label"] = 'Cámara de Comercio del propietario';
                    $temp["id"] = 'prop_'.$i.'_camarapropietario';
                    $temp["name"] = 'prop_'.$i.'_camarapropietario';
                    $temp["type"] = 'text';
                    $temp["size"] = '6';
                    $temp["value"] = $_SESSION["formulario"]["datos"]["propietarios"][$i]["camarapropietario"];
                    $temp["opc"] = array();
                    $temx = retornarRegistrosMysqliApi($mysqli, "bas_camaras", "1=1", "id,nombre");

                    foreach ($temx as $tx) {
                        $tsel  = '';
                        if ($tx["id"] == $_SESSION["formulario"]["datos"]["propietarios"][$i]["camarapropietario"]) {
                            $tsel = 'SI';
                        }
                        $temp["opc"][] = array(
                                'label' => $tx["nombre"] ,
                                'val' => $tx["id"],
                                'selected' => $tsel
                            );
                    }
                    $temp["placeholder"] = 'Cámara de Comercio del propietario';
                    $subpanel["inputs"][] = $temp;
                    //die("<pre>".print_r($panel["inputs"], true)."</pre>");

                    // Matrícula del propietario
                    $temp = array();
                    $temp["tipo"] = 'input';
                    $temp["mostrar"] = 'SI';
                    $temp["protegido"] = $protegido;
                    $temp["obligatorio"] = $obligatorio;
                    $temp["encabezado"] = '';
                    $temp["label"] = 'Matrícula del propietario';
                    $temp["id"] = 'prop_'.$i.'_matriculapropietario';
                    $temp["name"] = 'prop_'.$i.'_matriculapropietario';
                    $temp["type"] = 'text';
                    $temp["size"] = '10';
                    $temp["value"] = $_SESSION["formulario"]["datos"]["propietarios"][$i]["matriculapropietario"];
                    $temp["placeholder"] = '';
                    $subpanel["inputs"][] = $temp;

                    // Tipo de identificación del propietario
                    $temp = array();
                    $temp["tipo"] = 'select';
                    $temp["mostrar"] = 'SI';
                    $temp["protegido"] = $protegido;
                    $temp["obligatorio"] = $obligatorio;
                    $temp["encabezado"] = '';
                    $temp["label"] = 'Tipo de identificación del propietario';
                    $temp["id"] = 'prop_'.$i.'_idtipoidentificacionpropietario';
                    $temp["name"] = 'prop_'.$i.'_idtipoidentificacionpropietario';
                    $temp["type"] = 'text';
                    $temp["size"] = '1';
                    $temp["value"] = $_SESSION["formulario"]["datos"]["propietarios"][$i]["idtipoidentificacionpropietario"];
                    $temp["opc"] = array();
                    $temp["opc"][] = array(
                    'label' => 'Seleccione ...',
                    'val' => '',
                    'selected' => ''
                );

                    $temx = retornarRegistrosMysqliApi($mysqli, "mreg_tipoidentificacion", "1=1", "id");
                    foreach ($temx as $tx) {
                        $tsel = '';
                        if ($tx["id"] == $_SESSION["formulario"]["datos"]["propietarios"][$i]["idtipoidentificacionpropietario"]) {
                            $tsel = 'SI';
                        }
                        $temp["opc"][] = array(
                    'label' => $tx["descripcion"],
                    'val' => $tx["id"],
                    'selected' => $tsel
                );
                    }
                    $temp["placeholder"] = 'Matrícula del propietario';
                    $subpanel["inputs"][] = $temp;

                    // Número de identificación del propietario
                    $temp = array();
                    $temp["tipo"] = 'input';
                    $temp["mostrar"] = 'SI';
                    $temp["protegido"] = $protegido;
                    $temp["obligatorio"] = $obligatorio;
                    $temp["encabezado"] = '';
                    $temp["label"] = 'Número de identificación del propietario';
                    $temp["id"] = 'prop_'.$i.'_identificacionpropietario';
                    $temp["name"] = 'prop_'.$i.'_identificacionpropietario';
                    $temp["type"] = 'text';
                    $temp["size"] = '15';
                    $temp["maxlength"] = '30';
                    $temp["minlength"] = '0';
                    $temp["value"] = $_SESSION["formulario"]["datos"]["propietarios"][$i]["identificacionpropietario"];
                    $temp["placeholder"] = 'Número de identificación del propietario';
                    $subpanel["inputs"][] = $temp;

                    // Nit del propietario
                    $temp = array();
                    $temp["tipo"] = 'input';
                    $temp["mostrar"] = 'SI';
                    $temp["protegido"] = $protegido;
                    $temp["obligatorio"] = $obligatorio;
                    $temp["encabezado"] = '';
                    $temp["label"] = 'Nit del propietario';
                    $temp["id"] = 'prop_'.$i.'_nitpropietario';
                    $temp["name"] = 'prop_'.$i.'_nitpropietario';
                    $temp["type"] = 'text';
                    $temp["size"] = '15';
                    $temp["maxlength"] = '15';
                    $temp["minlength"] = '0';
                    $temp["value"] = $_SESSION["formulario"]["datos"]["propietarios"][$i]["nitpropietario"];
                    $temp["placeholder"] = 'Nit del propietario';
                    $temp["cssValidator"] = 'numeros';
                    $subpanel["inputs"][] = $temp;

                    // Nombre del propietario
                    $temp = array();
                    $temp["tipo"] = 'input';
                    $temp["mostrar"] = 'SI';
                    $temp["protegido"] = $protegido;
                    $temp["obligatorio"] = $obligatorio;
                    $temp["encabezado"] = '';
                    $temp["label"] = 'Nombre del propietario';
                    $temp["id"] = 'prop_'.$i.'_nombrepropietario';
                    $temp["name"] = 'prop_'.$i.'_nombrepropietario';
                    $temp["type"] = 'text';
                    $temp["size"] = '30';
                    $temp["maxlength"] = '30';
                    $temp["minlength"] = '0';
                    $temp["value"] = $_SESSION["formulario"]["datos"]["propietarios"][$i]["nombrepropietario"];
                    $temp["placeholder"] = 'Nombre del propietario';
                    $temp["cssValidator"] = 'letras';
                    $subpanel["inputs"][] = $temp;
                    $panel["grupos"][] = $subpanel;

                    // Informacion de notificacion y ubicacion
                    $subpanel = array();
                    $subpanel["titulo"] = 'Información de notificación y ubicación';
                    $subpanel["col"] = '6';
                    $subpanel["offset"] = '';
                    $subpanel["inputs"] = array();

                    // Dirección comercial del propietario
                    $temp = array();
                    $temp["tipo"] = 'textarea';
                    $temp["mostrar"] = 'SI';
                    $temp["protegido"] = $protegido;
                    $temp["obligatorio"] = $obligatorio;
                    $temp["encabezado"] = '';
                    $temp["label"] = 'Dirección comercial del propietario';
                    $temp["id"] = 'prop_'.$i.'_direccionpropietario';
                    $temp["name"] = 'prop_'.$i.'_direccionpropietario';
                    $temp["type"] = 'text';
                    $temp["size"] = '200';
                    $temp["maxlength"] = '200';
                    $temp["minlength"] = '0';
                    $temp["value"] = $_SESSION["formulario"]["datos"]["propietarios"][$i]["direccionpropietario"];
                    $temp["placeholder"] = 'Dirección comercial del propietario';
                    $temp["expresionRegular"] = '/[^a-zA-Z0-9|#|\-|.|, ÑñÁÉÍÓÚáéíóú]/g';
                    $subpanel["inputs"][] = $temp;

                    // Municipio comercial del propietario
                    $temp = array();
                    $temp["tipo"] = 'select';
                    $temp["mostrar"] = 'SI';
                    $temp["protegido"] = $protegido;
                    $temp["obligatorio"] = "NO";
                    $temp["encabezado"] = '';
                    $temp["label"] = 'Municipio comercial del propietario';
                    $temp["id"] = 'prop_'.$i.'_municipiopropietario';
                    $temp["name"] = 'prop_'.$i.'_municipiopropietario';
                    $temp["type"] = 'text';
                    $temp["size"] = '5';
                    $temp["value"] = $_SESSION["formulario"]["datos"]["propietarios"][$i]["municipiopropietario"];
                    $temp["opc"] = array();
                    $temp["opc"][] = array(
                    'label' => 'Seleccione ...',
                    'val' => '',
                    'selected' => ''
                );
                    $temx = retornarRegistrosMysqliApi($mysqli, "mreg_municipiosjurisdiccion", "1=1", "idcodigo");
                    foreach ($temx as $tx) {
                        $tsel = '';
                        if ($tx["idcodigo"] == $_SESSION["formulario"]["datos"]["propietarios"][$i]["municipiopropietario"]) {
                            $tsel = 'SI';
                        }
                        $mun = retornarRegistroMysqliApi($mysqli, "bas_municipios", "codigomunicipio='" . $tx["idcodigo"] . "'");
                        $temp["opc"][] = array(
                                'label' => $mun["codigomunicipio"] . ' - ' . $mun["ciudad"] . ' (' . $mun["departamento"] . ')',
                                'val' => $tx["idcodigo"],
                                'selected' => $tsel
                            );
                    }
                    $temp["placeholder"] = 'Municipio comercial del propietario';
                    $subpanel["inputs"][] = $temp;

                    // Dirección de notificación del propietario
                    $temp = array();
                    $temp["tipo"] = 'textarea';
                    $temp["mostrar"] = 'SI';
                    $temp["protegido"] = $protegido;
                    $temp["obligatorio"] = $obligatorio;
                    $temp["encabezado"] = '';
                    $temp["label"] = 'Dirección de notificación del propietario';
                    $temp["id"] = 'prop_'.$i.'_direccionnotpropietario';
                    $temp["name"] = 'prop_'.$i.'_direccionnotpropietario';
                    $temp["type"] = 'text';
                    $temp["size"] = '30';
                    $temp["maxlength"] = '200';
                    $temp["minlength"] = '0';
                    $temp["value"] = $_SESSION["formulario"]["datos"]["propietarios"][$i]["direccionnotpropietario"];
                    $temp["placeholder"] = 'Dirección de notificación del propietario';
                    $temp["expresionRegular"] = '/[^a-zA-Z0-9|#|\-|.|, ÑñÁÉÍÓÚáéíóú]/g';
                    $subpanel["inputs"][] = $temp;

                    // Municipio de notificación del propietario
                    $temp = array();
                    $temp["tipo"] = 'select';
                    $temp["mostrar"] = 'SI';
                    $temp["protegido"] = $protegido;
                    $temp["obligatorio"] = "NO";
                    $temp["encabezado"] = '';
                    $temp["label"] = 'Municipio de notificación del propietario';
                    $temp["id"] = 'prop_'.$i.'_municipionotpropietario';
                    $temp["name"] = 'prop_'.$i.'_municipionotpropietario';
                    $temp["type"] = 'text';
                    $temp["size"] = '5';
                    $temp["value"] = $_SESSION["formulario"]["datos"]["propietarios"][$i]["municipionotpropietario"];
                    $temp["opc"] = array();
                    $temp["opc"][] = array(
                    'label' => 'Seleccione ...',
                    'val' => '',
                    'selected' => ''
                );
                    $temx = retornarRegistrosMysqliApi($mysqli, "mreg_municipiosjurisdiccion", "1=1", "idcodigo");
                    foreach ($temx as $tx) {
                        $tsel = '';
                        if ($tx["idcodigo"] == $_SESSION["formulario"]["datos"]["propietarios"][$i]["municipionotpropietario"]) {
                            $tsel = 'SI';
                        }
                        $mun = retornarRegistroMysqliApi($mysqli, "bas_municipios", "codigomunicipio='" . $tx["idcodigo"] . "'");
                        $temp["opc"][] = array(
                                'label' => $mun["codigomunicipio"] . ' - ' . $mun["ciudad"] . ' (' . $mun["departamento"] . ')',
                                'val' => $tx["idcodigo"],
                                'selected' => $tsel
                            );
                    }
                    $temp["placeholder"] = 'Municipio de notificación del propietario';
                    $subpanel["inputs"][] = $temp;

                    // Teléfono No. 1 del propietario
                    $temp = array();
                    $temp["tipo"] = 'input';
                    $temp["mostrar"] = 'SI';
                    $temp["protegido"] = $protegido;
                    $temp["obligatorio"] = $obligatorio;
                    $temp["encabezado"] = '';
                    $temp["label"] = 'Teléfono No. 1 del propietario';
                    $temp["id"] = 'prop_'.$i.'_telefonopropietario';
                    $temp["name"] = 'prop_'.$i.'_telefonopropietario';
                    $temp["type"] = 'text';
                    $temp["size"] = '10';
                    $temp["maxlength"] = '10';
                    $temp["minlength"] = '5';
                    $temp["value"] = $_SESSION["formulario"]["datos"]["propietarios"][$i]["telefonopropietario"];
                    $temp["placeholder"] = 'Teléfono No. 1 del propietario';
                    $temp["cssValidator"] = 'numeros';
                    $subpanel["inputs"][] = $temp;

                    // Teléfono No. 2 del propietario
                    $temp = array();
                    $temp["tipo"] = 'input';
                    $temp["mostrar"] = 'SI';
                    $temp["protegido"] = $protegido;
                    $temp["obligatorio"] = $obligatorio;
                    $temp["encabezado"] = '';
                    $temp["label"] = 'Teléfono No. 2 del propietario';
                    $temp["id"] = 'prop_'.$i.'_telefono2propietario';
                    $temp["name"] = 'prop_'.$i.'_telefono2propietario';
                    $temp["type"] = 'text';
                    $temp["size"] = '10';
                    $temp["maxlength"] = '10';
                    $temp["minlength"] = '5';
                    $temp["value"] = $_SESSION["formulario"]["datos"]["propietarios"][$i]["telefono2propietario"];
                    $temp["placeholder"] = 'Teléfono No. 2 del propietario';
                    $temp["cssValidator"] = 'numeros';
                    $subpanel["inputs"][] = $temp;

                    // Teléfono No. 3 del propietario
                    $temp = array();
                    $temp["tipo"] = 'input';
                    $temp["mostrar"] = 'SI';
                    $temp["protegido"] = $protegido;
                    $temp["obligatorio"] = $obligatorio;
                    $temp["encabezado"] = '';
                    $temp["label"] = 'Teléfono No. 3 del propietario';
                    $temp["id"] = 'prop_'.$i.'_celularpropietario';
                    $temp["name"] = 'prop_'.$i.'_celularpropietario';
                    $temp["type"] = 'text';
                    $temp["size"] = '10';
                    $temp["maxlength"] = '10';
                    $temp["minlength"] = '5';
                    $temp["value"] = $_SESSION["formulario"]["datos"]["propietarios"][$i]["celularpropietario"];
                    $temp["placeholder"] = 'Teléfono No. 3 del propietario';
                    $temp["cssValidator"] = 'numeros';
                    $subpanel["inputs"][] = $temp;
                    $panel["grupos"][] = $subpanel;

                    // Representacion legal
                    $subpanel = array();
                    $subpanel["titulo"] = 'Representación legal';
                    $subpanel["col"] = '6';
                    $subpanel["offset"] = '';
                    $subpanel["inputs"] = array();

                    // Nombre del Representante Legal
                    $temp = array();
                    $temp["tipo"] = 'input';
                    $temp["mostrar"] = 'SI';
                    $temp["protegido"] = $protegido;
                    $temp["obligatorio"] = $obligatorio;
                    $temp["encabezado"] = '';
                    $temp["label"] = 'Nombre del Representante Legal';
                    $temp["id"] = 'prop_'.$i.'_nomreplegpropietario';
                    $temp["name"] = 'prop_'.$i.'_nomreplegpropietario';
                    $temp["type"] = 'text';
                    $temp["size"] = '40';
                    $temp["maxlength"] = '50';
                    $temp["minlength"] = '0';
                    $temp["value"] = $_SESSION["formulario"]["datos"]["propietarios"][$i]["nomreplegpropietario"];
                    $temp["placeholder"] = 'Nombre del Representante Legal';
                    $temp["cssValidator"] = 'letras';
                    $subpanel["inputs"][] = $temp;

                    // Tipo de identificación del Representante Legal
                    $temp = array();
                    $temp["tipo"] = 'select';
                    $temp["mostrar"] = 'SI';
                    $temp["protegido"] = $protegido;
                    $temp["obligatorio"] = $obligatorio;
                    $temp["encabezado"] = '';
                    $temp["label"] = 'Tipo de identificación del Representante Legal';
                    $temp["id"] = 'prop_'.$i.'_tipoidreplegpropietario';
                    $temp["name"] = 'prop_'.$i.'_tipoidreplegpropietario';
                    $temp["type"] = 'text';
                    $temp["size"] = '1';
                    if ($_SESSION["formulario"]["datos"]["propietarios"][$i]["tipoidreplegpropietario"] == "" || $_SESSION["formulario"]["datos"]["propietarios"][$i]["tipoidreplegpropietario"] == 0) {
                        $temp["value"] = 0;
                    } else {
                        $temp["value"] = $_SESSION["formulario"]["datos"]["propietarios"][$i]["tipoidreplegpropietario"];
                    }
                    $temp["opc"] = array();
                    $temp["opc"][] = array(
                    'label' => 'Por verificar',
                    'val' => '0',
                    'selected' => ''
                );

                    $temx = retornarRegistrosMysqliApi($mysqli, "mreg_tipoidentificacion", "1=1", "id");
                    foreach ($temx as $tx) {
                        $tsel = '';
                        if ($tx["id"] == $_SESSION["formulario"]["datos"]["propietarios"][$i]["tipoidreplegpropietario"]) {
                            $tsel = 'SI';
                        }
                        $temp["opc"][] = array(
                    'label' => $tx["descripcion"],
                    'val' => $tx["id"],
                    'selected' => $tsel
                );
                    }
                    $temp["placeholder"] = 'Tipo de identificación del Representante Legal';
                    $subpanel["inputs"][] = $temp;

                    // Número de identificación del Representante Legal
                    $temp = array();
                    $temp["tipo"] = 'input';
                    $temp["mostrar"] = 'SI';
                    $temp["protegido"] = $protegido;
                    $temp["obligatorio"] = $obligatorio;
                    $temp["encabezado"] = '';
                    $temp["label"] = 'Número de identificación del Representante Legal';
                    $temp["id"] = 'prop_'.$i.'_numidreplegpropietario';
                    $temp["name"] = 'prop_'.$i.'_numidreplegpropietario';
                    $temp["type"] = 'text';
                    $temp["size"] = '20';
                    $temp["maxlength"] = '30';
                    $temp["minlength"] = '0';
                    $temp["value"] = $_SESSION["formulario"]["datos"]["propietarios"][$i]["numidreplegpropietario"];
                    $temp["placeholder"] = 'Número de identificación del Representante Legal';
                    $subpanel["inputs"][] = $temp;

                    // Ultimo año renovado del propietario
                    $temp = array();
                    $temp["tipo"] = 'input';
                    $temp["mostrar"] = 'SI';
                    $temp["protegido"] = $protegido;
                    $temp["obligatorio"] = $obligatorio;
                    $temp["encabezado"] = '';
                    $temp["label"] = 'Ultimo año renovado del propietario';
                    $temp["id"] = 'prop_'.$i.'_ultanorenpropietario';
                    $temp["name"] = 'prop_'.$i.'_ultanorenpropietario';
                    $temp["type"] = 'text';
                    $temp["size"] = '4';
                    $temp["maxlength"] = '4';
                    $temp["minlength"] = '0';
                    $temp["value"] = $_SESSION["formulario"]["datos"]["propietarios"][$i]["ultanorenpropietario"];
                    $temp["placeholder"] = 'Ultimo año renovado del propietario';
                    $subpanel["inputs"][] = $temp;

                    // add subpanel to panel
                    $panel["grupos"][] = $subpanel;
                }
                $_SESSION["jsonsalida"]["formulario"][] = $panel;
            }
        }
        // close db
        $mysqli->close();

        // **************************************************************************** //
        // Resultado
        // **************************************************************************** //
        \logApi::peticionRest('api_' . __FUNCTION__);
        $json = $api->json($_SESSION["jsonsalida"]);
        $api->response(str_replace("\\/", "/", $json), 200);
    }

    /**
     * Método que retorna un expediente mercantil
     *
     * Recibe
     * - codigoempresa
     * - usuariows
     * - token
     * - idusuario
     * - tipopusuario
     * - emailcontrol
     * - identificacioncontrol
     * - celularcontrol
     * - consulta
     *
     * Retorna
     * - json con el array de ciiu
     *
     * @param API $api
     */
    public function consultarCiiu(API $api)
    {
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/myErrorHandler.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
        $resError = set_error_handler('myErrorHandler');

        // array de respuesta
        $_SESSION["jsonsalida"] = array();
        $_SESSION["jsonsalida"]["codigoerror"] = '0000';
        $_SESSION["jsonsalida"]["mensajeerror"] = '';
        $_SESSION["jsonsalida"]["registros"] = array();

        // Verifica que  método de recepcion de parámetros sea POST
        if ($api->get_request_method() != "POST" && $api->get_request_method() != "GET") {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'La petición debe ser POST/GET';
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

        $api->validarParametro("consulta", true);

        // ********************************************************************** //
        // Valida versión del SII
        // ********************************************************************** //
        if (trim($_SESSION["entrada"]["sistemaorigen"]) != 'SII1' && trim($_SESSION["entrada"]["sistemaorigen"]) != 'SII2') {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Control de versionado del sii erróneo';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        if (!$api->validarToken('consultarCiiu', $_SESSION["entrada"]["token"], $_SESSION["entrada"]["usuariows"])) {
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
        // Retorna registros ciius
        // ********************************************************************** //

        $query = '';

        if (is_numeric($_SESSION["entrada"]["consulta"])) {
            $query = "idciiu like '%" . $_SESSION["entrada"]["consulta"] . "%'";
        } else {
            $cantidad_palabras = 0;
            $palabras_busqueda = explode(" ", $_SESSION["entrada"]["consulta"]);

            foreach ($palabras_busqueda as $palabra) {
                $cantidad_palabras++;
                if ($cantidad_palabras == 1) {
                    //$query = "MATCH (idciiu, descripcion, detalle, incluye) AGAINST ('*" . $_SESSION["entrada"]["consulta"] . "*' in boolean mode)";
                    $query .= "(idciiu like '%" . $palabra . "%' and descripcion like '%" . $palabra . "%' or detalle like '%" . $palabra . "%' or incluye like '%" . $palabra . "%')";
                } else {
                    $query .= " and (idciiu like '%" . $palabra . "%' and descripcion like '%" . $palabra . "%'  or detalle like '%" . $palabra . "%' or incluye like '%" . $palabra . "%')";
                }
            }
        }


        $res = retornarRegistrosMysqliApi($mysqli, 'bas_ciius', $query, "", "idciiu, descripcion, incluye, excluye", "", "");
        $mysqli->close();
        if ($res === false) {
            $_SESSION["jsonsalida"]["codigoerror"] = '9999';
            $_SESSION["jsonsalida"]["mensajeerror"] = $_SESSION["generales"]["mensajeerror"];
        }

        if ($res && !empty($res)) {
            foreach ($res as $r) {
                $_SESSION["jsonsalida"]["registros"][] = $r;
            }
        }
        // **************************************************************************** //
        // Resultado
        // **************************************************************************** //
        \logApi::peticionRest('api_' . __FUNCTION__);
        $json = $api->json($_SESSION["jsonsalida"]);
        $api->response(str_replace("\\/", "/", $json), 200);
    }

    public function buscarBarrio(API $api)
    {
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/myErrorHandler.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
        $resError = set_error_handler('myErrorHandler');

        // array de respuesta
        $_SESSION["jsonsalida"] = array();
        $_SESSION["jsonsalida"]["codigoerror"] = '0000';
        $_SESSION["jsonsalida"]["mensajeerror"] = '';
        $_SESSION["jsonsalida"]["registros"] = array();

        // Verifica que  método de recepcion de parámetros sea POST
        if ($api->get_request_method() != "POST" && $api->get_request_method() != "GET") {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'La petición debe ser POST/GET';
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

        $api->validarParametro("idmunicipio", true);

        // ********************************************************************** //
        // Valida versión del SII
        // ********************************************************************** //
        if (trim($_SESSION["entrada"]["sistemaorigen"]) != 'SII1' && trim($_SESSION["entrada"]["sistemaorigen"]) != 'SII2') {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Control de versionado del sii erróneo';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        if (!$api->validarToken('consultarCiiu', $_SESSION["entrada"]["token"], $_SESSION["entrada"]["usuariows"])) {
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        // ********************************************************************** //
        // Valida idmunicipio es numerico
        // ********************************************************************** //
        if (!is_numeric($_SESSION["entrada"]["idmunicipio"])) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'idmunicipio debe ser numerico';
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
        // Retorna registros ciius
        // ********************************************************************** //
        $res = retornarRegistrosMysqliApi($mysqli, "mreg_barriosmuni", "idmunicipio='" . $_SESSION["entrada"]["idmunicipio"] . "'", "", "idbarrio, nombre");
        $mysqli->close();


        if ($res === false) {
            $_SESSION["jsonsalida"]["codigoerror"] = '9999';
            $_SESSION["jsonsalida"]["mensajeerror"] = $_SESSION["generales"]["mensajeerror"];
        }

        if ($res && !empty($res)) {
            foreach ($res as $r) {
                $_SESSION["jsonsalida"]["registros"][] = $r;
            }
        }
        // **************************************************************************** //
        // Resultado
        // **************************************************************************** //
        $json = $api->json($_SESSION["jsonsalida"]);
        $api->response(str_replace("\\/", "/", $json), 200);
    }

    public function construirFormularioPdf(API $api)
    {
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/myErrorHandler.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/genPdfsMercantil_armarPdfEstablecimientoslNuevo1082.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/genPdfsMercantil_armarPdfEstablecimientoslNuevoAnosAnteriores1082.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/genPdfsMercantil_armarPdfPrincipalNuevo2023.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/genPdfsMercantil_armarPdfPrincipalNuevoAnosAnteriores1082.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/uniPdfs.php');
        $resError = set_error_handler('myErrorHandler');

        if (!defined('CLAVE_ENCRIPTACION') || CLAVE_ENCRIPTACION == '') {
            $claveEncriptacion = 'c0nf3c@m@r@s2017';
        }

        // array de respuesta
        $_SESSION["jsonsalida"] = array();
        $_SESSION["jsonsalida"]["codigoerror"] = '0000';
        $_SESSION["jsonsalida"]["mensajeerror"] = '';
        $_SESSION['jsonsalida']['matricula'] = '';
        $_SESSION['jsonsalida']['idliquidacion'] = '';
        $_SESSION['jsonsalida']['link'] = '';

        // Verifica método de recepcion de parámetros
        if ($api->get_request_method() != "POST" && $api->get_request_method() != "GET") {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'La petición debe ser POST/GET';
        }

        //
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
        $api->validarParametro("matricula", true);
        $api->validarParametro("idliquidacion", true);


        //
        if (!$api->validarToken('construirFormularioPdf', $_SESSION["entrada"]["token"], $_SESSION["entrada"]["usuariows"])) {
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
        $_SESSION["tramite"] = \funcionesRegistrales::retornarMregLiquidacion($mysqli, $_SESSION["entrada"]["idliquidacion"]);
        if ($_SESSION["tramite"] === false || empty($_SESSION["tramite"])) {
            $mysqli->close();
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Liquidación no localizada (1)';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        // ********************************************************************** //
        // Arma data del formulario
        // ********************************************************************** //
        $_SESSION["formulario"]["tipomatricula"] = '';
        $_SESSION["formulario"]["tipotramite"] = $_SESSION["tramite"]["tipotramite"];
        $_SESSION["formulario"]["reliquidacion"] = $_SESSION["tramite"]["reliquidacion"];
        $_SESSION["formulario"]["liquidacion"] = $_SESSION["entrada"]["idliquidacion"];
        $_SESSION["formulario"]["matricula"] = $_SESSION["entrada"]["matricula"];
        $_SESSION["formulario"]["organizacion"] = '';
        $_SESSION["formulario"]["categoria"] = '';


        // ********************************************************************** //
        // Recupera datos del expediente
        // ********************************************************************** //
        $arrForms = retornarRegistroMysqliApi($mysqli, "mreg_liquidaciondatos", "idliquidacion=" . $_SESSION["entrada"]["idliquidacion"] . " and expediente='" . $_SESSION["entrada"]["matricula"] . "'");
        if ($arrForms === false || empty($arrForms)) {
            if ($_SESSION["entrada"]["matricula"] == '' ||
                    substr($_SESSION["entrada"]["matricula"], 0, 5) == 'NUEVA') {
                $_SESSION["formulario"]["datos"] = \funcionesGenerales::desserializarExpedienteMatricula($mysqli, '');
            } else {
                $_SESSION["formulario"]["datos"] = \funcionesRegistrales::retornarExpedienteMercantil($mysqli, $_SESSION["entrada"]["matricula"]);
                $_SESSION["formulario"]["organizacion"] = $_SESSION["formulario"]["datos"]["organizacion"];
                $_SESSION["formulario"]["categoria"] = $_SESSION["formulario"]["datos"]["categoria"];
                \funcionesRegistrales::almacenarDatosImportantesRenovacion($mysqli, $_SESSION["entrada"]["idliquidacion"], $_SESSION["formulario"]["datos"], 'I');
            }
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Liquidación no localizada (2)';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        } else {

            /*
              $_SESSION["formulario"]["datos"] = \funcionesSii2_desserializaciones::desserializarExpedienteMatricula($mysqli, $arrForms["xml"]);
              $_SESSION["formulario"]["organizacion"] = $_SESSION["formulario"]["datos"]["organizacion"];
              $_SESSION["formulario"]["categoria"] = $_SESSION["formulario"]["datos"]["categoria"];
             */
            foreach ($arrForms as $form) {
                $dat = \funcionesGenerales::desserializarExpedienteMatricula($mysqli, $form);
                if ($dat["matricula"] == $_SESSION["formulario"]["matricula"]) {
                    $_SESSION["formulario"]["datos"] = $dat;
                    if (($_SESSION["formulario"]["datos"]["organizacion"] == '02') ||
                            ($_SESSION["formulario"]["datos"]["categoria"] == '2') ||
                            ($_SESSION["formulario"]["datos"]["categoria"] == '3')) {
                        $name = armarPdfEstablecimientoNuevo1082Api($mysqli, $_SESSION["tramite"]["numerorecuperacion"], $_SESSION["tramite"]["numeroliquidacion"], '');

                        if (count($_SESSION["formulario"]["datos"]["f"]) > 1) {
                            $name1 = armarPdfEstablecimientoNuevoAnosAnteriores1082Api($mysqli, $_SESSION["tramite"]["numerorecuperacion"], $_SESSION["tramite"]["numeroliquidacion"], '');

                            $pathUnionPdf = PATH_ABSOLUTO_SITIO . '/tmp/';

                            unirPdfsApiV2(array(
                                $pathUnionPdf . $name,
                                $pathUnionPdf . $name1
                                    ), $pathUnionPdf . $name);
                        }
                        $ok = 'si';
                    } else {
                        $name = armarPdfPrincipalNuevo2023Api($mysqli, $_SESSION["tramite"]["numerorecuperacion"], $_SESSION["tramite"]["numeroliquidacion"], '');

                        if (count($_SESSION["formulario"]["datos"]["f"]) > 1) {
                            $name1 = armarPdfPrincipalNuevoAnosAnteriores1082Api($mysqli, $_SESSION["tramite"]["numerorecuperacion"], $_SESSION["tramite"]["numeroliquidacion"], '');

                            $pathUnionPdf = PATH_ABSOLUTO_SITIO . '/tmp/';

                            unirPdfsApiV2(array(
                                $pathUnionPdf . $name,
                                $pathUnionPdf . $name1
                                    ), $pathUnionPdf . $name);
                        }
                        $ok = 'si';
                    }
                }
            }
        }

        unset($arrForms);

        //
        $_SESSION['jsonsalida']['matricula'] = $_SESSION["entrada"]["idliquidacion"];
        $_SESSION['jsonsalida']['idliquidacion'] = $_SESSION["entrada"]["matricula"];
        $_SESSION["jsonsalida"]["link"] = TIPO_HTTP . HTTP_HOST . "/tmp/" . $name;
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

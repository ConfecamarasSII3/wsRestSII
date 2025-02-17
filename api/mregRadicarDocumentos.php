<?php

namespace api;

use api\API;

trait mregRadicarDocumentos {

    public function mregRadicarDocumentosSeleccionarCiiu(API $api) {
        require_once ('mysqli.php');
        require_once ('funcionesGenerales.php');
        require_once ('presentacion.class.php');

        // array de respuesta
        $_SESSION ["jsonsalida"] = array();
        $_SESSION ["jsonsalida"] ["codigoerror"] = '0000';
        $_SESSION ["jsonsalida"] ["mensajeerror"] = '';
        $_SESSION ["jsonsalida"] ["html"] = '';

        //
        if ($api->get_request_method() != "POST") {
            $_SESSION ["jsonsalida"] ["codigoerror"] = "9999";
            $_SESSION ["jsonsalida"] ["mensajeerror"] = 'La petición debe ser POST';
            $api->response($api->json($_SESSION ["jsonsalida"]), 200);
        }

        //
        $api->validarParametro("numciiu", true);
        $api->validarParametro("session_parameters", false);

        // ********************************************************************** //
        // Valida session_parameters
        // ********************************************************************** //
        if (isset($_SESSION ["entrada"] ["session_parameters"]) && $_SESSION ["entrada"] ["session_parameters"] != '') {
            \funcionesGenerales::desarmarVariablesPantalla($_SESSION ["entrada"] ["session_parameters"]);
        }

        $pres = new \presentacionBootstrap ();
        // $string = $pres->abrirPanel();
        $string = '<center>';
        $string .= $pres->armarCampoTextoMd('Palabras o códigos a buscar', 'no', 'textobuscar', 8, '', '');
        $string .= '</center>';
        $string .= '<br>';
        $arrBtnTipo = array();
        $arrBtnImagen = array();
        $arrBtnEnlace = array();
        $arrBtnTipo [] = 'javascript';
        $arrBtnImagen [] = 'Consultar';
        $arrBtnEnlace [] = 'seleccionarCiiuContinuar();';
        $string .= $pres->armarBotonesDinamicos($arrBtnTipo, $arrBtnImagen, $arrBtnEnlace);
        // $string .= $pres->cerrarPanel();
        unset($pres);

        //
        // $mysqli->close();
        //
        $_SESSION ["jsonsalida"] ["html"] = base64_encode($string);

        // **************************************************************************** //
        // Resultado
        // **************************************************************************** //
        // \logApi::peticionRest('api_' . __FUNCTION__);
        $json = $api->json($_SESSION ["jsonsalida"]);
        $api->response(str_replace("\\/", "/", $json), 200);
    }

    public function mregRadicarDocumentosSeleccionarCiiuContinuar(API $api) {
        require_once ('mysqli.php');
        require_once ('funcionesGenerales.php');
        require_once ('presentacion.class.php');

        // array de respuesta
        $_SESSION ["jsonsalida"] = array();
        $_SESSION ["jsonsalida"] ["codigoerror"] = '0000';
        $_SESSION ["jsonsalida"] ["mensajeerror"] = '';
        $_SESSION ["jsonsalida"] ["html"] = '';

        //
        if ($api->get_request_method() != "POST") {
            $_SESSION ["jsonsalida"] ["codigoerror"] = "9999";
            $_SESSION ["jsonsalida"] ["mensajeerror"] = 'La petición debe ser POST';
            $api->response($api->json($_SESSION ["jsonsalida"]), 200);
        }

        //
        $api->validarParametro("numciiu", true);
        $api->validarParametro("textobuscar", true);
        $api->validarParametro("session_parameters", false);

        // ********************************************************************** //
        // Valida session_parameters
        // ********************************************************************** //
        if (isset($_SESSION ["entrada"] ["session_parameters"]) && $_SESSION ["entrada"] ["session_parameters"] != '') {
            \funcionesGenerales::desarmarVariablesPantalla($_SESSION ["entrada"] ["session_parameters"]);
        }

        $_SESSION["entrada"]["textobuscar"] = base64_decode($_SESSION["entrada"]["textobuscar"]);
        $busqueda = "";
        $cantidad_palabras = 0;
        $palabras_busqueda = explode(" ", $_SESSION["entrada"]["textobuscar"]);

        if (is_numeric($_SESSION["entrada"]["textobuscar"])) {
            $busqueda = "idciiu like '%" . $_SESSION["entrada"]["textobuscar"] . "'";
        } else {
            foreach ($palabras_busqueda as $palabra) {
                $cantidad_palabras++;
                if ($cantidad_palabras == 1) {
                    // $busqueda .= "(descripcion like '%" . $palabra . "%' or detalle like '%" . $palabra . "%' or incluye like '%" . $palabra . "%')";
                    $busqueda .= "(descripcion like '%" . $palabra . "%' or incluye like '%" . $palabra . "%')";
                } else {
                    // $busqueda .= " and (descripcion like '%" . $palabra . "%'  or detalle like '%" . $palabra . "%' or incluye like '%" . $palabra . "%')";
                    $busqueda .= " and (descripcion like '%" . $palabra . "%' or incluye like '%" . $palabra . "%')";
                }
            }
        }

        $mysqli = conexionMysqliApi();
        $res = retornarRegistrosMysqliApi($mysqli, 'bas_ciius', $busqueda, "idciiu");
        if ($res === false || empty($res)) {
            $pres = new \presentacionBootstrap ();
            $string = '<center>';
            $string .= $pres->armarCampoTextoMd('Palabras o códigos a buscar', 'no', 'textobuscar', 8, '', '');
            $string .= '</center>';
            $string .= '<br>';
            $string .= $pres->armarLineaTextoInformativa('No se encontraron códigos CIIUs para el texto indicado', 'center', '', 'text-danger');
            $string .= '<br>';
            $arrBtnTipo = array();
            $arrBtnImagen = array();
            $arrBtnEnlace = array();
            $arrBtnTipo [] = 'javascript';
            $arrBtnImagen [] = 'Consultar';
            $arrBtnEnlace [] = 'seleccionarCiiuContinuar(\'' . $_SESSION["entrada"]["numciiu"] . '\');';
            $string .= $pres->armarBotonesDinamicos($arrBtnTipo, $arrBtnImagen, $arrBtnEnlace);
            unset($pres);
        } else {
            $pres = new \presentacionBootstrap ();
            // $string = $pres->abrirPanel();
            $string = '<center>';
            $string .= $pres->armarCampoTextoMd('Palabras o códigos a buscar', 'no', 'textobuscar', 8, '', '');
            $string .= '</center>';
            $string .= '<br>';
            $arrBtnTipo = array();
            $arrBtnImagen = array();
            $arrBtnEnlace = array();
            $arrBtnTipo [] = 'javascript';
            $arrBtnImagen [] = 'Consultar';
            $arrBtnEnlace [] = 'seleccionarCiiuContinuar(\'' . $_SESSION["entrada"]["numciiu"] . '\');';
            $string .= $pres->armarBotonesDinamicos($arrBtnTipo, $arrBtnImagen, $arrBtnEnlace);
            $string .= $pres->armarLineaTextoInformativa('A continuacuión se presenta el resultado de la búsqueda', 'center');
            $actnocom = '';
            foreach ($res as $x) {
                if ($x["movimientomatricula"] != 'N' || $x["movimientorenovacion"] != 'N' || $x["movimientomutacion"] != 'N') {
                    if ($x["actividadcomercial"] == 'NO') {
                        $actnocom = 'si';
                        $txt = '<a href="javascript:seleccionarCiiuContinuarFin(\'' . $x["idciiu"] . '\',\'' . base64_encode($x["descripcion"]) . '\')"><strong>' . $x["idciiu"] . '</strong> (actividad catalogada como no comercial para personas naturales y establecimientos)</a><br>';
                    } else {
                        $txt = '<a href="javascript:seleccionarCiiuContinuarFin(\'' . $x["idciiu"] . '\',\'' . base64_encode($x["descripcion"]) . '\')"><strong>' . $x["idciiu"] . '</strong></a><br>';
                    }
                    $txt .= $x["descripcion"];
                    $txt .= '<hr>';
                    if ($actnocom == 'si') {
                        $txt .= '<strong>Importante !!!</strong> Cuando se trate de personas naturales o establecimientos de comercio de estas, le recomendamos no seleccionar códigos de actividad catalogados como no comerciales.';
                        $txt .= '<hr>';
                    }
                    $string .= $pres->armarLineaTextoInformativa($txt);
                }
            }
            $string .= '<br>';
            // $string .= $pres->cerrarPanel();
            unset($pres);
        }
        $mysqli->close();
        $_SESSION ["jsonsalida"] ["html"] = base64_encode($string);

        // **************************************************************************** //
        // Resultado
        // **************************************************************************** //
        // \logApi::peticionRest('api_' . __FUNCTION__);
        $json = $api->json($_SESSION ["jsonsalida"]);
        $api->response(str_replace("\\/", "/", $json), 200);
    }
    
    public function mregRadicarDocumentosSalvarDatosCliente(API $api) {
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistralesCalculos.php');

        // array de respuesta
        $_SESSION["jsonsalida"] = array();
        $_SESSION["jsonsalida"]["codigoerror"] = '0000';
        $_SESSION["jsonsalida"]["mensajeerror"] = '';

        //
        if ($api->get_request_method() != "POST") {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'La petición debe ser POST';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        //
        $mysqli = conexionMysqliApi();

        //
        $api->validarParametro("usuariows", true);
        $api->validarParametro("token", true);

        // ********************************************************************** //
        // Valida que el usuario reportado tenga acceso a la BD y al metodo
        // ********************************************************************** // 
        if (!$api->validarToken('mregRadicarDocumentosSalvarDatosCliente', $_SESSION["entrada"]["token"], $_SESSION["entrada"]["usuariows"])) {
            $mysqli->close();
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Sin permisos para ejecutar el método mregRadicarDocumentosSalvarDatosCliente ';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        // ********************************************************************** //
        // Recupera las variables del arreglo $_SESSION["entrada1"]
        // ********************************************************************** //
        $vars = array();
        foreach ($_SESSION["entrada1"] as $key => $valor) {
            $vars[$key] = base64_decode($valor);
        }

        // ********************************************************************** //
        // Recupera la liquidación
        // ********************************************************************** //
        $_SESSION["tramite"] = \funcionesRegistrales::retornarMregLiquidacion($mysqli, $vars["idliquidacion"]);
        if ($_SESSION["tramite"] === false || empty($_SESSION["tramite"])) {
            $mysqli->close();
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'No se localizó la liquidación (' . $vars["idliquidacion"] . ')';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        // ********************************************************************** //
        // Valida el estado de la liquidación
        // ********************************************************************** //
        if ($_SESSION["tramite"]["idestado"] > '05' && $_SESSION["tramite"]["idestado"] != '10' && $_SESSION["tramite"]["idestado"] != '50') {
            $mysqli->close();
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'La liquidación se encuentra en un estado que no permite ser modificada';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        // ********************************************************************** //
        // Mueve las variables a la liquidación
        // ********************************************************************** //
        //
        $_SESSION["tramite"]["idtipoidentificacioncliente"] = ($vars["tidc"]);
        $_SESSION["tramite"]["identificacioncliente"] = ($vars["idc"]);
        if ($_SESSION["tramite"]["idtipoidentificacioncliente"] == '2') {
            $_SESSION["tramite"]["nombrecliente"] = ($vars["nc"]);
            $_SESSION["tramite"]["apellidocliente"] = '';
            $_SESSION["tramite"]["nombre1cliente"] = '';
            $_SESSION["tramite"]["nombre2cliente"] = '';
            $_SESSION["tramite"]["apellido1cliente"] = '';
            $_SESSION["tramite"]["apellido2cliente"] = '';
        } else {
            $_SESSION["tramite"]["nombrecliente"] = trim(($vars["n1c"]));
            if (trim(($vars["n2c"])) != '') {
                $_SESSION["tramite"]["nombrecliente"] .= ' ' . trim(($vars["n2c"]));
            }
            $_SESSION["tramite"]["apellidocliente"] = trim(($vars["a1c"]));
            if (trim(($vars["a2c"])) != '') {
                $_SESSION["tramite"]["apellidocliente"] .= ' ' . trim(($vars["a2c"]));
            }
            $_SESSION["tramite"]["apellido1cliente"] = trim(($vars["a1c"]));
            $_SESSION["tramite"]["apellido2cliente"] = trim(($vars["a2c"]));
            $_SESSION["tramite"]["nombre1cliente"] = trim(($vars["n1c"]));
            $_SESSION["tramite"]["nombre2cliente"] = trim(($vars["n2c"]));
        }
        $_SESSION["tramite"]["direccion"] = trim(($vars["d"]));
        $_SESSION["tramite"]["idmunicipio"] = trim(($vars["im"]));
        $_SESSION["tramite"]["codposcom"] = trim(($vars["cpc"]));

        $_SESSION["tramite"]["direccionnot"] = trim(($vars["dn"]));
        $_SESSION["tramite"]["idmunicipionot"] = trim(($vars["imn"]));
        $_SESSION["tramite"]["codposnot"] = trim(($vars["cpn"]));

        $_SESSION["tramite"]["lenguaje"] = trim(($vars["l"]));
        $_SESSION["tramite"]["pais"] = trim(($vars["p"]));
        $_SESSION["tramite"]["telefono"] = trim(($vars["t"]));
        $_SESSION["tramite"]["movil"] = trim(($vars["m"]));
        $_SESSION["tramite"]["email"] = trim(($vars["e"]));
        $_SESSION["tramite"]["zonapostal"] = trim(($vars["zp"]));

        $_SESSION["tramite"]["codigoregimen"] = trim(($vars["cr"]));
        $_SESSION["tramite"]["responsabilidadfiscal"] = trim(($vars["rf"]));
        $_SESSION["tramite"]["codigoimpuesto"] = trim(($vars["ci"]));
        $_SESSION["tramite"]["nombreimpuesto"] = trim(($vars["ni"]));
        $_SESSION["tramite"]["responsabilidadtributaria"] = trim(($vars["rt"]));

        //
        if (trim($_SESSION["tramite"]["apellido1cliente"]) == '') {
            $tipopersona = '1';
        } else {
            $tipopersona = '2';
        }
        if (!isset($_SESSION["tramite"]["responsabilidadtributaria"])) {
            $_SESSION["tramite"]["responsabilidadtributaria"] = '';
        }
        if (!isset($_SESSION["tramite"]["responsabilidadfiscal"])) {
            $_SESSION["tramite"]["responsabilidadfiscal"] = '';
        }
        if (!isset($_SESSION["tramite"]["codigoregimen"])) {
            $_SESSION["tramite"]["codigoregimen"] = '';
        }
        if (!isset($_SESSION["tramite"]["codigoimpuesto"])) {
            $_SESSION["tramite"]["codigoimpuesto"] = '';
        }
        if (!isset($_SESSION["tramite"]["nombreimpuesto"])) {
            $_SESSION["tramite"]["nombreimpuesto"] = '';
        }
        if (!isset($_SESSION["tramite"]["zonapostal"])) {
            $_SESSION["tramite"]["zonapostal"] = '';
        }
        if (!isset($_SESSION["tramite"]["pais"])) {
            $_SESSION["tramite"]["pais"] = '';
        }
        if (!isset($_SESSION["tramite"]["lenguaje"])) {
            $_SESSION["tramite"]["lenguaje"] = '';
        }
        if (!isset($_SESSION["tramite"]["direccionnot"])) {
            $_SESSION["tramite"]["direccionnot"] = '';
        }
        if (!isset($_SESSION["tramite"]["idmunicipionot"])) {
            $_SESSION["tramite"]["idmunicipionot"] = '';
        }
        if (!isset($_SESSION["tramite"]["codposnot"])) {
            $_SESSION["tramite"]["codposnot"] = '';
        }

        //
        if ($_SESSION["tramite"]["codposcom"] == '' && $_SESSION["tramite"]["idmunicipio"] != '') {
            $zonas = retornarRegistrosMysqliApi($mysqli, 'bas_codigos_postales', "municipio='" . $_SESSION["tramite"]["idmunicipio"] . "'", "id");
            if ($zonas && !empty($zonas)) {
                foreach ($zonas as $z) {
                    if ($z["tipo"] == 'Urbano') {
                        if ($_SESSION["tramite"]["codposcom"] == '') {
                            $_SESSION["tramite"]["codposcom"] = $z["codigopostal"];
                        }
                    }
                }
            }
        }

        //
        if ($_SESSION["tramite"]["codposnot"] == '' && $_SESSION["tramite"]["idmunicipionot"] != '') {
            $zonas = retornarRegistrosMysqliApi($mysqli, 'bas_codigos_postales', "municipio='" . $_SESSION["tramite"]["idmunicipionot"] . "'", "id");
            if ($zonas && !empty($zonas)) {
                foreach ($zonas as $z) {
                    if ($z["tipo"] == 'Urbano') {
                        if ($_SESSION["tramite"]["codposnot"] == '') {
                            $_SESSION["tramite"]["codposnot"] = $z["codigopostal"];
                        }
                    }
                }
            }
        }

        //
        $_SESSION["tramite"]["datosfijados"] = 'si';

        //
        \funcionesRegistrales::grabarLiquidacionMreg($mysqli);

        // Almacen ala tabla datos_empresas
        $arrCampos = array(
            'tipoidentificacion',
            'identificacion',
            'tipopersona',
            'razonsocial',
            'nombreregistrado',
            'primernombre',
            'segundonombre',
            'primerapellido',
            'segundoapellido',
            'particula',
            'email',
            'responsabilidadtributaria',
            'codigoregimen',
            'codigoimpuesto',
            'nombreimpuesto',
            'responsabilidadfiscal',
            'telefono1',
            'telefono2',
            'zonapostal',
            'pais',
            'lenguaje',
            'dircom',
            'muncom',
            'codposcom',
            'dirnot',
            'munnot',
            'codposnot'
        );

        $arrValores = array(
            "'" . $_SESSION["tramite"]["idtipoidentificacioncliente"] . "'",
            "'" . $_SESSION["tramite"]["identificacioncliente"] . "'",
            "'" . $tipopersona . "'",
            "'" . addslashes(trim($_SESSION["tramite"]["nombrecliente"] . ' ' . $_SESSION["tramite"]["apellidocliente"])) . "'",
            "'" . addslashes(trim($_SESSION["tramite"]["nombrecliente"] . ' ' . $_SESSION["tramite"]["apellidocliente"])) . "'",
            "'" . addslashes($_SESSION["tramite"]["nombre1cliente"]) . "'",
            "'" . addslashes($_SESSION["tramite"]["nombre2cliente"]) . "'",
            "'" . addslashes($_SESSION["tramite"]["apellido1cliente"]) . "'",
            "'" . addslashes($_SESSION["tramite"]["apellido2cliente"]) . "'",
            "'" . addslashes($_SESSION["tramite"]["particulacliente"]) . "'",
            "'" . addslashes($_SESSION["tramite"]["email"]) . "'",
            "'" . addslashes($_SESSION["tramite"]["responsabilidadtributaria"]) . "'",
            "'" . addslashes($_SESSION["tramite"]["codigoregimen"]) . "'",
            "'" . addslashes($_SESSION["tramite"]["codigoimpuesto"]) . "'",
            "'" . addslashes($_SESSION["tramite"]["nombreimpuesto"]) . "'",
            "'" . addslashes($_SESSION["tramite"]["responsabilidadfiscal"]) . "'",
            "'" . addslashes($_SESSION["tramite"]["telefono"]) . "'",
            "'" . addslashes($_SESSION["tramite"]["movil"]) . "'",
            "'" . addslashes($_SESSION["tramite"]["zonapostal"]) . "'",
            "'" . addslashes($_SESSION["tramite"]["pais"]) . "'",
            "'" . addslashes($_SESSION["tramite"]["lenguaje"]) . "'",
            "'" . addslashes($_SESSION["tramite"]["direccion"]) . "'",
            "'" . addslashes($_SESSION["tramite"]["idmunicipio"]) . "'",
            "'" . addslashes($_SESSION["tramite"]["codposcom"]) . "'",
            "'" . addslashes($_SESSION["tramite"]["direccionnot"]) . "'",
            "'" . addslashes($_SESSION["tramite"]["idmunicipionot"]) . "'",
            "'" . addslashes($_SESSION["tramite"]["codposnot"]) . "'"
        );
        if (contarRegistrosMysqliApi($mysqli, 'datos_empresas', "tipoidentificacion='" . $_SESSION["tramite"]["idtipoidentificacioncliente"] . "' and identificacion='" . $_SESSION["tramite"]["identificacioncliente"] . "'") == 0) {
            insertarRegistrosMysqliApi($mysqli, 'datos_empresas', $arrCampos, $arrValores);
        } else {
            regrabarRegistrosMysqliApi($mysqli, 'datos_empresas', $arrCampos, $arrValores, "tipoidentificacion='" . $_SESSION["tramite"]["idtipoidentificacioncliente"] . "' and identificacion='" . $_SESSION["tramite"]["identificacioncliente"] . "'");
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

    public function mregRadicarDocumentosBuscarIdentificacion(API $api) {
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistralesCalculos.php');

        // array de respuesta
        $_SESSION["jsonsalida"] = array();
        $_SESSION["jsonsalida"]["codigoerror"] = '9999';
        $_SESSION["jsonsalida"]["mensajeerror"] = 'No localizo la identificación';

        //
        if ($api->get_request_method() != "POST") {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'La petición debe ser POST';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        //
        $mysqli = conexionMysqliApi();
        if ($mysqli == false) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Error de conexion a la BD';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        //
        $api->validarParametro("usuariows", true);
        $api->validarParametro("token", true);
        $api->validarParametro("idtipoidentificacioncliente", true);
        $api->validarParametro("identificacioncliente", true);

        // ********************************************************************** //
        // Valida que el usuario reportado tenga acceso a la BD y al metodo
        // ********************************************************************** // 
        if (!$api->validarToken('mregRadicarDocumentosBuscarIdentificacion', $_SESSION["entrada"]["token"], $_SESSION["entrada"]["usuariows"])) {
            $mysqli->close();
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Sin permisos para ejecutar el método mregRadicarDocumentosBuscarIdentificacion ';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        // ********************************************************************** //
        // Recupera de datos cliente
        // ********************************************************************** //
        $de = retornarRegistroMysqliApi($mysqli, 'datos_empresas', "tipoidentificacion='" . $_SESSION["entrada"]["idtipoidentificacioncliente"] . "' and identificacion='" . $_SESSION["entrada"]["identificacioncliente"] . "'");
        if ($de && !empty($de)) {
            if ($_SESSION["entrada"]["idtipoidentificacioncliente"] == '2') {
                $_SESSION["jsonsalida"]["nombrecliente"] = $de["razonsocial"];
                $_SESSION["jsonsalida"]["apellido1cliente"] = '';
                $_SESSION["jsonsalida"]["apellido2cliente"] = '';
                $_SESSION["jsonsalida"]["nombre1cliente"] = '';
                $_SESSION["jsonsalida"]["nombre2cliente"] = '';
            } else {
                $_SESSION["jsonsalida"]["nombrecliente"] = $de["primerapellido"];
                if (trim($de["segundoapellido"]) != '') {
                    $_SESSION["jsonsalida"]["nombrecliente"] .= ' ' . $de["segundoapellido"];
                }
                if (trim($de["primernombre"]) != '') {
                    $_SESSION["jsonsalida"]["nombrecliente"] .= ' ' . $de["primernombre"];
                }
                if (trim($de["segundonombre"]) != '') {
                    $_SESSION["jsonsalida"]["nombrecliente"] .= ' ' . $de["segundonombre"];
                }
                $_SESSION["jsonsalida"]["apellido1cliente"] = $de["primerapellido"];
                $_SESSION["jsonsalida"]["apellido2cliente"] = $de["segundoapellido"];
                $_SESSION["jsonsalida"]["nombre1cliente"] = $de["primernombre"];
                $_SESSION["jsonsalida"]["nombre2cliente"] = $de["segundonombre"];
            }
            $_SESSION["jsonsalida"]["direccion"] = strtoupper($de["dircom"]);
            $_SESSION["jsonsalida"]["direccionnot"] = strtoupper($de["dirnot"]);
            $_SESSION["jsonsalida"]["idmunicipio"] = $de["muncom"];
            $_SESSION["jsonsalida"]["idmunicipionot"] = $de["munnot"];
            $_SESSION["jsonsalida"]["zonapostal"] = $de["zonapostal"];
            $_SESSION["jsonsalida"]["codposcom"] = $de["codposcom"];
            $_SESSION["jsonsalida"]["codposnot"] = $de["codposnot"];
            $_SESSION["jsonsalida"]["telefono"] = $de["telefono1"];
            $_SESSION["jsonsalida"]["movil"] = $de["telefono2"];
            $_SESSION["jsonsalida"]["pais"] = $de["pais"];
            $_SESSION["jsonsalida"]["lenguaje"] = strtolower($de["lenguaje"]);
            $_SESSION["jsonsalida"]["codigoregimen"] = $de["codigoregimen"];
            $_SESSION["jsonsalida"]["responsabilidadfiscal"] = $de["responsabilidadfiscal"];
            $_SESSION["jsonsalida"]["codigoimpuesto"] = $de["codigoimpuesto"];
            $_SESSION["jsonsalida"]["nombreimpuesto"] = strtoupper($de["nombreimpuesto"]);
            $_SESSION["jsonsalida"]["responsabilidadtributaria"] = strtoupper($de["responsabilidadtributaria"]);
            $_SESSION["jsonsalida"]["email"] = strtolower($de["email"]);
            $mysqli->close();
            $_SESSION["jsonsalida"]["codigoerror"] = "0000";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Desde datos_empresas';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
            exit();
        }

        // ********************************************************************** //
        // Recupera de la bd
        // ********************************************************************** //
        $ins = retornarRegistroMysqliApi($mysqli, 'mreg_est_inscritos', "idclase='" . $_SESSION["entrada"]["idtipoidentificacioncliente"] . "' and numid='" . $_SESSION["entrada"]["identificacioncliente"] . "'");
        if ($ins && !empty($ins)) {
            $_SESSION["jsonsalida"]["nombrecliente"] = $ins["razonsocial"];
            $_SESSION["jsonsalida"]["apellido1cliente"] = $ins["apellido1"];
            $_SESSION["jsonsalida"]["apellido2cliente"] = $ins["apellido2"];
            $_SESSION["jsonsalida"]["nombre1cliente"] = $ins["nombre1"];
            $_SESSION["jsonsalida"]["nombre2cliente"] = $ins["nombre2"];
            $_SESSION["jsonsalida"]["direccion"] = $ins["dircom"];
            $_SESSION["jsonsalida"]["direccionnot"] = $ins["dirnot"];
            $_SESSION["jsonsalida"]["idmunicipio"] = $ins["muncom"];
            $_SESSION["jsonsalida"]["idmunicipionot"] = $ins["munnot"];
            $_SESSION["jsonsalida"]["zonapostal"] = $ins["codigopostalcom"];
            $_SESSION["jsonsalida"]["codposcom"] = $ins["codigopostalcom"];
            $_SESSION["jsonsalida"]["codposnot"] = $ins["codigopostalnot"];
            $_SESSION["jsonsalida"]["telefono"] = $ins["telcom1"];
            $_SESSION["jsonsalida"]["movil"] = $ins["telcom2"];
            $_SESSION["jsonsalida"]["pais"] = 'CO';
            $_SESSION["jsonsalida"]["lenguaje"] = 'es';
            $_SESSION["jsonsalida"]["email"] = $ins["emailcom"];
            if ($ins["idclase"] == '2') {
                $_SESSION["jsonsalida"]["codigoregimen"] = '48';
                $_SESSION["jsonsalida"]["responsabilidadfiscal"] = 'R-99-PJ';
                $_SESSION["jsonsalida"]["codigoimpuesto"] = '01';
                $_SESSION["jsonsalida"]["nombreimpuesto"] = 'IVA';
            } else {
                $_SESSION["jsonsalida"]["codigoregimen"] = '49';
                $_SESSION["jsonsalida"]["responsabilidadfiscal"] = 'R-99-PN';
                $_SESSION["jsonsalida"]["codigoimpuesto"] = '';
                $_SESSION["jsonsalida"]["nombreimpuesto"] = '';
            }
            $_SESSION["jsonsalida"]["responsabilidadtributaria"] = '';
            $mysqli->close();
            $_SESSION["jsonsalida"]["codigoerror"] = "0000";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Desde mreg_est_inscritos';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
            exit();
        }

        //
        $_SESSION["jsonsalida"]["nombrecliente"] = '';
        $_SESSION["jsonsalida"]["apellido1cliente"] = '';
        $_SESSION["jsonsalida"]["apellido2cliente"] = '';
        $_SESSION["jsonsalida"]["nombre1cliente"] = '';
        $_SESSION["jsonsalida"]["nombre2cliente"] = '';
        $_SESSION["jsonsalida"]["direccion"] = '';
        $_SESSION["jsonsalida"]["direccionnot"] = '';
        $_SESSION["jsonsalida"]["idmunicipio"] = '';
        $_SESSION["jsonsalida"]["idmunicipionot"] = '';
        $_SESSION["jsonsalida"]["zonapostal"] = '';
        $_SESSION["jsonsalida"]["codposcom"] = '';
        $_SESSION["jsonsalida"]["codposnot"] = '';
        $_SESSION["jsonsalida"]["telefono"] = '';
        $_SESSION["jsonsalida"]["movil"] = '';
        $_SESSION["jsonsalida"]["pais"] = 'CO';
        $_SESSION["jsonsalida"]["lenguaje"] = 'es';
        $_SESSION["jsonsalida"]["email"] = '';
        $_SESSION["jsonsalida"]["codigoregimen"] = '49';
        $_SESSION["jsonsalida"]["responsabilidadfiscal"] = 'R-99-PN';
        $_SESSION["jsonsalida"]["codigoimpuesto"] = '';
        $_SESSION["jsonsalida"]["nombreimpuesto"] = '';
        $_SESSION["jsonsalida"]["responsabilidadtributaria"] = '';
        $mysqli->close();

        //
        $_SESSION["jsonsalida"]["codigoerror"] = "0000";
        $_SESSION["jsonsalida"]["mensajeerror"] = 'No cruzo, no existe (' . $_SESSION["generales"]["codigoempresa"] . ')';
        $api->response($api->json($_SESSION["jsonsalida"]), 200);
    }

    public function mregRadicarDocumentosBuscarIdentificacionExpedienteDatosCliente(API $api) {
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistralesCalculos.php');

        // array de respuesta
        $_SESSION["jsonsalida"] = array();
        $_SESSION["jsonsalida"]["codigoerror"] = '9999';
        $_SESSION["jsonsalida"]["mensajeerror"] = 'No localizo la identificación';
        $_SESSION["jsonsalida"]["saldoprepago"] = 0;
        $_SESSION["jsonsalida"]["claveprepago"] = '';

        //
        if ($api->get_request_method() != "POST") {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'La petición debe ser POST';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        //
        $mysqli = conexionMysqliApi();
        if ($mysqli == false) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Error de conexion a la BD';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        //
        $api->validarParametro("usuariows", true);
        $api->validarParametro("token", true);
        $api->validarParametro("idtipoidentificacioncliente", true);
        $api->validarParametro("identificacioncliente", true);
        $api->validarParametro("idliquidacion", true);

        // ********************************************************************** //
        // Valida que el usuario reportado tenga acceso a la BD y al metodo
        // ********************************************************************** // 
        if (!$api->validarToken('mregRadicarDocumentosBuscarIdentificacion', $_SESSION["entrada"]["token"], $_SESSION["entrada"]["usuariows"])) {
            $mysqli->close();
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Sin permisos para ejecutar el método mregRadicarDocumentosBuscarIdentificacion ';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        // ****************************************************************************************************** //
        // Encuentra datos de prepago
        // 1.- Busca prepago para la identificacion dada, si no lo encuentra o no tiene saldo
        // 2.- Busca el expediente que corresponda a dicha identificación (matrícula) en mreg_est_inscritos
        // 3.- Busca en los vínculos cual de ellos tenga un prepago con saldo
        // ******************************************************************************************************* //
        $saldop = 0;
        $clavep = '';
        if (ltrim(trim($_SESSION["entrada"]["identificacioncliente"]), "0") != '' && ltrim(trim($_SESSION["entrada"]["identificacioncliente"]), "0") != '222222222222') {
            $prep = \funcionesRegistrales::actualizarPrepago($mysqli, 'S', $_SESSION["entrada"]["identificacioncliente"]);
            if ($prep && !empty($prep) && $prep["saldoprepago"] > 0) {
                $saldop = $prep["saldoprepago"];
                $clavep = $prep["clave"];
            } else {
                $matbus = '';
                $expes = retornarRegistrosMysqliApi($mysqli, 'mreg_est_inscritos', "numid='" . $_SESSION["entrada"]["identificacioncliente"] . "' or nit = '" . $_SESSION["entrada"]["identificacioncliente"] . "'", "id");
                if ($expes && !empty($expes)) {
                    foreach ($expes as $expe) {
                        if (trim($expe["matricula"]) != '' && ($expe["ctrestmatricula"] == 'MA' || $expe["ctrestmatricula"] == 'MI' || $expe["ctrestmatricula"] == 'MR' || $expe["ctrestmatricula"] == 'IA' || $expe["ctrestmatricula"] == 'II' || $expe["ctrestmatricula"] == 'IR')) {
                            $matbus = $expe["matricula"];
                        }
                    }
                }
                if ($matbus != '') {
                    $vins = retornarRegistrosMysqliApi($mysqli, 'mreg_est_vinculos', "matricula='" . $matbus . "' and estado = 'V'", "id");
                    if ($vins && !empty($vins)) {
                        foreach ($vins as $v) {
                            if ($saldop == 0) {
                                $prep = \funcionesRegistrales::actualizarPrepago($mysqli, 'S', $v["numid"]);
                                if ($prep && !empty($prep) && $prep["saldoprepago"] > 0) {
                                    $saldop = $prep["saldoprepago"];
                                    $clavep = $prep["clave"];
                                }
                            }
                        }
                    }
                }
            }
        }

        // ********************************************************************** //
        // Valida si previamente se han grabado los datos del cliente
        // ********************************************************************** //
        $asigno = retornarRegistroMysqliApi($mysqli, 'mreg_liquidacion_campos', "idliquidacion=" . $_SESSION["entrada"]["idliquidacion"] . " and campo='asignodatosfacturacion'", 'contenido');
        if ($asigno == 'si') {
            $liq = \funcionesRegistrales::retornarMregLiquidacion($mysqli, $_SESSION["entrada"]["idliquidacion"]);
            if ($liq["idtipoidentificacioncliente"] == $_SESSION["entrada"]["idtipoidentificacioncliente"] && $liq["identificacioncliente"] == $_SESSION["entrada"]["identificacioncliente"]) {
                if ($_SESSION["entrada"]["idtipoidentificacioncliente"] != '2') {
                    if (trim($liq["apellido1cliente"]) != '') {
                        $liq["nombrecliente"] = $liq["apellido1cliente"];
                        if (trim($liq["apellido2cliente"]) != '') {
                            $liq["nombrecliente"] .= ' ' . $liq["apellido2cliente"];
                        }
                        if (trim($liq["nombre1cliente"]) != '') {
                            $liq["nombrecliente"] .= ' ' . $liq["nombre1cliente"];
                        }
                        if (trim($liq["nombre2cliente"]) != '') {
                            $liq["nombrecliente"] .= ' ' . $liq["nombre2cliente"];
                        }
                    }
                }
                $_SESSION["jsonsalida"]["nombrecliente"] = $liq["nombrecliente"];
                $_SESSION["jsonsalida"]["apellido1cliente"] = $liq["apellido1cliente"];
                $_SESSION["jsonsalida"]["apellido2cliente"] = $liq["apellido2cliente"];
                $_SESSION["jsonsalida"]["nombre1cliente"] = $liq["nombre1cliente"];
                $_SESSION["jsonsalida"]["nombre2cliente"] = $liq["nombre2cliente"];
                $_SESSION["jsonsalida"]["direccion"] = $liq["direccion"];
                $_SESSION["jsonsalida"]["direccionnot"] = $liq["direccionnot"];
                $_SESSION["jsonsalida"]["idmunicipio"] = $liq["idmunicipio"];
                $_SESSION["jsonsalida"]["idmunicipionot"] = $liq["idmunicipionot"];
                $_SESSION["jsonsalida"]["zonapostal"] = $liq["zonapostal"];
                $_SESSION["jsonsalida"]["codposcom"] = $liq["codposcom"];
                $_SESSION["jsonsalida"]["codposnot"] = $liq["codposnot"];
                $_SESSION["jsonsalida"]["telefono"] = $liq["telefono"];
                $_SESSION["jsonsalida"]["movil"] = $liq["movil"];
                $_SESSION["jsonsalida"]["pais"] = $liq["pais"];
                $_SESSION["jsonsalida"]["lenguaje"] = $liq["lenguaje"];
                $_SESSION["jsonsalida"]["email"] = $liq["email"];
                $_SESSION["jsonsalida"]["codigoregimen"] = $liq["codigoregimen"];
                $_SESSION["jsonsalida"]["responsabilidadfiscal"] = $liq["responsabilidadfiscal"];
                $_SESSION["jsonsalida"]["codigoimpuesto"] = $liq["codigoimpuesto"];
                $_SESSION["jsonsalida"]["nombreimpuesto"] = $liq["nombreimpuesto"];
                $_SESSION["jsonsalida"]["responsabilidadtributaria"] = $liq["responsabilidadtributaria"];
                $_SESSION["jsonsalida"]["saldoprepago"] = $saldop;
                $_SESSION["jsonsalida"]["claveprepago"] = $clavep;
                $mysqli->close();
                $_SESSION["jsonsalida"]["codigoerror"] = "0000";
                $_SESSION["jsonsalida"]["mensajeerror"] = 'Desde mreg_liquidacion';
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
                exit();
            }
        }

        // ********************************************************************** //
        // Recupera de la bd
        // ********************************************************************** //
        $ins = retornarRegistroMysqliApi($mysqli, 'mreg_est_inscritos', "idclase='" . $_SESSION["entrada"]["idtipoidentificacioncliente"] . "' and numid='" . $_SESSION["entrada"]["identificacioncliente"] . "' and matricula > '' and ctrestmatricula IN ('MA','MI','MR','IA','II','IR')");
        if ($ins && !empty($ins)) {
            $_SESSION["jsonsalida"]["nombrecliente"] = $ins["razonsocial"];
            $_SESSION["jsonsalida"]["apellido1cliente"] = $ins["apellido1"];
            $_SESSION["jsonsalida"]["apellido2cliente"] = $ins["apellido2"];
            $_SESSION["jsonsalida"]["nombre1cliente"] = $ins["nombre1"];
            $_SESSION["jsonsalida"]["nombre2cliente"] = $ins["nombre2"];
            $_SESSION["jsonsalida"]["direccion"] = $ins["dircom"];
            $_SESSION["jsonsalida"]["direccionnot"] = $ins["dirnot"];
            $_SESSION["jsonsalida"]["idmunicipio"] = $ins["muncom"];
            $_SESSION["jsonsalida"]["idmunicipionot"] = $ins["munnot"];
            $_SESSION["jsonsalida"]["zonapostal"] = $ins["codigopostalcom"];
            $_SESSION["jsonsalida"]["codposcom"] = $ins["codigopostalcom"];
            $_SESSION["jsonsalida"]["codposnot"] = $ins["codigopostalnot"];
            $_SESSION["jsonsalida"]["telefono"] = $ins["telcom1"];
            $_SESSION["jsonsalida"]["movil"] = $ins["telcom2"];
            $_SESSION["jsonsalida"]["pais"] = 'CO';
            $_SESSION["jsonsalida"]["lenguaje"] = 'es';
            $_SESSION["jsonsalida"]["email"] = $ins["emailcom"];
            if ($ins["idclase"] == '2') {
                $_SESSION["jsonsalida"]["codigoregimen"] = '48';
                $_SESSION["jsonsalida"]["responsabilidadfiscal"] = 'R-99-PJ';
                $_SESSION["jsonsalida"]["codigoimpuesto"] = '01';
                $_SESSION["jsonsalida"]["nombreimpuesto"] = 'IVA';
            } else {
                $_SESSION["jsonsalida"]["codigoregimen"] = '49';
                $_SESSION["jsonsalida"]["responsabilidadfiscal"] = 'R-99-PN';
                $_SESSION["jsonsalida"]["codigoimpuesto"] = '';
                $_SESSION["jsonsalida"]["nombreimpuesto"] = '';
            }
            $_SESSION["jsonsalida"]["responsabilidadtributaria"] = '';

            //
            $de = retornarRegistroMysqliApi($mysqli, 'datos_empresas', "tipoidentificacion='" . $_SESSION["entrada"]["idtipoidentificacioncliente"] . "' and identificacion='" . $_SESSION["entrada"]["identificacioncliente"] . "'");
            if ($de && !empty($de)) {
                $_SESSION["jsonsalida"]["pais"] = $de["pais"];
                $_SESSION["jsonsalida"]["lenguaje"] = strtolower($de["lenguaje"]);
                $_SESSION["jsonsalida"]["codigoregimen"] = $de["codigoregimen"];
                $_SESSION["jsonsalida"]["responsabilidadfiscal"] = $de["responsabilidadfiscal"];
                $_SESSION["jsonsalida"]["codigoimpuesto"] = $de["codigoimpuesto"];
                $_SESSION["jsonsalida"]["nombreimpuesto"] = strtoupper($de["nombreimpuesto"]);
                $_SESSION["jsonsalida"]["responsabilidadtributaria"] = strtoupper($de["responsabilidadtributaria"]);
            }

            //
            $_SESSION["jsonsalida"]["saldoprepago"] = $saldop;
            $_SESSION["jsonsalida"]["claveprepago"] = $clavep;

            //
            $mysqli->close();
            $_SESSION["jsonsalida"]["codigoerror"] = "0000";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Desde mreg_est_inscritos';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
            exit();
        }

        // ********************************************************************** //
        // Recupera de datos cliente
        // ********************************************************************** //
        $de = retornarRegistroMysqliApi($mysqli, 'datos_empresas', "tipoidentificacion='" . $_SESSION["entrada"]["idtipoidentificacioncliente"] . "' and identificacion='" . $_SESSION["entrada"]["identificacioncliente"] . "'");
        if ($de && !empty($de)) {
            if ($_SESSION["entrada"]["idtipoidentificacioncliente"] == '2') {
                $_SESSION["jsonsalida"]["nombrecliente"] = $de["razonsocial"];
                $_SESSION["jsonsalida"]["apellido1cliente"] = '';
                $_SESSION["jsonsalida"]["apellido2cliente"] = '';
                $_SESSION["jsonsalida"]["nombre1cliente"] = '';
                $_SESSION["jsonsalida"]["nombre2cliente"] = '';
            } else {
                $_SESSION["jsonsalida"]["nombrecliente"] = $de["primerapellido"];
                if (trim($de["segundoapellido"]) != '') {
                    $_SESSION["jsonsalida"]["nombrecliente"] .= ' ' . $de["segundoapellido"];
                }
                if (trim($de["primernombre"]) != '') {
                    $_SESSION["jsonsalida"]["nombrecliente"] .= ' ' . $de["primernombre"];
                }
                if (trim($de["segundonombre"]) != '') {
                    $_SESSION["jsonsalida"]["nombrecliente"] .= ' ' . $de["segundonombre"];
                }
                $_SESSION["jsonsalida"]["apellido1cliente"] = $de["primerapellido"];
                $_SESSION["jsonsalida"]["apellido2cliente"] = $de["segundoapellido"];
                $_SESSION["jsonsalida"]["nombre1cliente"] = $de["primernombre"];
                $_SESSION["jsonsalida"]["nombre2cliente"] = $de["segundonombre"];
            }
            $_SESSION["jsonsalida"]["direccion"] = strtoupper($de["dircom"]);
            $_SESSION["jsonsalida"]["direccionnot"] = strtoupper($de["dirnot"]);
            $_SESSION["jsonsalida"]["idmunicipio"] = $de["muncom"];
            $_SESSION["jsonsalida"]["idmunicipionot"] = $de["munnot"];
            $_SESSION["jsonsalida"]["zonapostal"] = $de["zonapostal"];
            $_SESSION["jsonsalida"]["codposcom"] = $de["codposcom"];
            $_SESSION["jsonsalida"]["codposnot"] = $de["codposnot"];
            $_SESSION["jsonsalida"]["telefono"] = $de["telefono1"];
            $_SESSION["jsonsalida"]["movil"] = $de["telefono2"];
            $_SESSION["jsonsalida"]["pais"] = $de["pais"];
            $_SESSION["jsonsalida"]["lenguaje"] = strtolower($de["lenguaje"]);
            $_SESSION["jsonsalida"]["codigoregimen"] = $de["codigoregimen"];
            $_SESSION["jsonsalida"]["responsabilidadfiscal"] = $de["responsabilidadfiscal"];
            $_SESSION["jsonsalida"]["codigoimpuesto"] = $de["codigoimpuesto"];
            $_SESSION["jsonsalida"]["nombreimpuesto"] = strtoupper($de["nombreimpuesto"]);
            $_SESSION["jsonsalida"]["responsabilidadtributaria"] = strtoupper($de["responsabilidadtributaria"]);
            $_SESSION["jsonsalida"]["email"] = strtolower($de["email"]);
            //
            $_SESSION["jsonsalida"]["saldoprepago"] = $saldop;
            $_SESSION["jsonsalida"]["claveprepago"] = $clavep;

            //
            $mysqli->close();
            $_SESSION["jsonsalida"]["codigoerror"] = "0000";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Desde datos_empresas';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
            exit();
        }

        // ********************************************************************** //
        // Recupera de datos desde prepago
        // ********************************************************************** //
        if ($prep["nombre"] != '') {
            if ($_SESSION["entrada"]["idtipoidentificacioncliente"] == '2') {
                $_SESSION["jsonsalida"]["nombrecliente"] = $prep["razonsocial"];
                $_SESSION["jsonsalida"]["apellido1cliente"] = '';
                $_SESSION["jsonsalida"]["apellido2cliente"] = '';
                $_SESSION["jsonsalida"]["nombre1cliente"] = '';
                $_SESSION["jsonsalida"]["nombre2cliente"] = '';
            } else {
                $_SESSION["jsonsalida"]["nombrecliente"] = $prep["apellido1"];
                if (trim($prep["apellido2"]) != '') {
                    $_SESSION["jsonsalida"]["nombrecliente"] .= ' ' . $prep["apellido2"];
                }
                if (trim($prep["nombre1"]) != '') {
                    $_SESSION["jsonsalida"]["nombrecliente"] .= ' ' . $prep["nombre1"];
                }
                if (trim($prep["nombre2"]) != '') {
                    $_SESSION["jsonsalida"]["nombrecliente"] .= ' ' . $prep["nombre2"];
                }
                $_SESSION["jsonsalida"]["apellido1cliente"] = $prep["apellido1"];
                $_SESSION["jsonsalida"]["apellido2cliente"] = $prep["apellido2"];
                $_SESSION["jsonsalida"]["nombre1cliente"] = $prep["nombre1"];
                $_SESSION["jsonsalida"]["nombre2cliente"] = $prep["nombre2"];
            }
            $_SESSION["jsonsalida"]["direccion"] = strtoupper($prep["direccion"]);
            $_SESSION["jsonsalida"]["direccionnot"] = strtoupper($prep["direccion"]);
            $_SESSION["jsonsalida"]["idmunicipio"] = $prep["municipio"];
            $_SESSION["jsonsalida"]["idmunicipionot"] = $prep["municipio"];
            $_SESSION["jsonsalida"]["zonapostal"] = '';
            $_SESSION["jsonsalida"]["codposcom"] = '';
            $_SESSION["jsonsalida"]["codposnot"] = '';
            $_SESSION["jsonsalida"]["telefono"] = $prep["telefono"];
            $_SESSION["jsonsalida"]["movil"] = $prep["celular"];
            $_SESSION["jsonsalida"]["pais"] = '';
            $_SESSION["jsonsalida"]["lenguaje"] = 'es';
            $_SESSION["jsonsalida"]["codigoregimen"] = '';
            $_SESSION["jsonsalida"]["responsabilidadfiscal"] = '';
            $_SESSION["jsonsalida"]["codigoimpuesto"] = '';
            $_SESSION["jsonsalida"]["nombreimpuesto"] = '';
            $_SESSION["jsonsalida"]["responsabilidadtributaria"] = '';
            $_SESSION["jsonsalida"]["email"] = strtolower($prep["email"]);
            $_SESSION["jsonsalida"]["saldoprepago"] = $saldop;
            $_SESSION["jsonsalida"]["claveprepago"] = $clavep;
            $mysqli->close();
            $_SESSION["jsonsalida"]["codigoerror"] = "0000";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Desde datos_empresas';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
            exit();
        }

        //
        $_SESSION["jsonsalida"]["nombrecliente"] = '';
        $_SESSION["jsonsalida"]["apellido1cliente"] = '';
        $_SESSION["jsonsalida"]["apellido2cliente"] = '';
        $_SESSION["jsonsalida"]["nombre1cliente"] = '';
        $_SESSION["jsonsalida"]["nombre2cliente"] = '';
        $_SESSION["jsonsalida"]["direccion"] = '';
        $_SESSION["jsonsalida"]["direccionnot"] = '';
        $_SESSION["jsonsalida"]["idmunicipio"] = '';
        $_SESSION["jsonsalida"]["idmunicipionot"] = '';
        $_SESSION["jsonsalida"]["zonapostal"] = '';
        $_SESSION["jsonsalida"]["codposcom"] = '';
        $_SESSION["jsonsalida"]["codposnot"] = '';
        $_SESSION["jsonsalida"]["telefono"] = '';
        $_SESSION["jsonsalida"]["movil"] = '';
        $_SESSION["jsonsalida"]["pais"] = 'CO';
        $_SESSION["jsonsalida"]["lenguaje"] = 'es';
        $_SESSION["jsonsalida"]["email"] = '';
        $_SESSION["jsonsalida"]["codigoregimen"] = '49';
        $_SESSION["jsonsalida"]["responsabilidadfiscal"] = 'R-99-PN';
        $_SESSION["jsonsalida"]["codigoimpuesto"] = '';
        $_SESSION["jsonsalida"]["nombreimpuesto"] = '';
        $_SESSION["jsonsalida"]["responsabilidadtributaria"] = '';
        $_SESSION["jsonsalida"]["saldoprepago"] = $saldop;
        $_SESSION["jsonsalida"]["claveprepago"] = $clavep;
        $mysqli->close();
        $_SESSION["jsonsalida"]["codigoerror"] = "0000";
        $_SESSION["jsonsalida"]["mensajeerror"] = 'No cruzo, no existe (' . $_SESSION["generales"]["codigoempresa"] . ')';
        $api->response($api->json($_SESSION["jsonsalida"]), 200);
    }

    public function mregRadicarDocumentosBuscarExpedientePorIdentificacion(API $api) {
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistralesCalculos.php');

        // array de respuesta
        $_SESSION["jsonsalida"] = array();
        $_SESSION["jsonsalida"]["codigoerror"] = '9999';
        $_SESSION["jsonsalida"]["mensajeerror"] = 'No localizo la identificación';
        $_SESSION["jsonsalida"]["idclase"] = '';
        $_SESSION["jsonsalida"]["numid"] = '';
        $_SESSION["jsonsalida"]["nombrecliente"] = '';
        $_SESSION["jsonsalida"]["organizacion"] = '';
        $_SESSION["jsonsalida"]["categoria"] = '';
        $_SESSION["jsonsalida"]["matricula"] = '';
        $_SESSION["jsonsalida"]["saldoprepago"] = 0;
        $_SESSION["jsonsalida"]["claveprepago"] = '';

        //
        if ($api->get_request_method() != "POST") {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'La petición debe ser POST';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        //
        $mysqli = conexionMysqliApi();
        if ($mysqli == false) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Error de conexion a la BD';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        //
        $api->validarParametro("usuariows", true);
        $api->validarParametro("token", true);
        $api->validarParametro("idtipoidentificacion", true);
        $api->validarParametro("identificacion", true);
        $_SESSION["entrada"]["identificacion"] = str_replace(array(".",",","-"," "),"",$_SESSION["entrada"]["identificacion"]);
        
        // ********************************************************************** //
        // Valida que el usuario reportado tenga acceso a la BD y al metodo
        // ********************************************************************** // 
        if (!$api->validarToken('mregRadicarDocumentosBuscarExpedientePorIdentificacion', $_SESSION["entrada"]["token"], $_SESSION["entrada"]["usuariows"])) {
            $mysqli->close();
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Sin permisos para ejecutar el método mregRadicarDocumentosBuscarIdentificacion ';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }


        $_SESSION["jsonsalida"]["idclase"] = $_SESSION["entrada"]["idtipoidentificacion"];
        $_SESSION["jsonsalida"]["numid"] = $_SESSION["entrada"]["identificacion"];
        
        // ********************************************************************** //
        // Recupera de la bd
        // ********************************************************************** //
        $ins = retornarRegistroMysqliApi($mysqli, 'mreg_est_inscritos', "idclase='" . $_SESSION["entrada"]["idtipoidentificacion"] . "' and numid='" . $_SESSION["entrada"]["identificacion"] . "' and matricula > '' and ctrestmatricula IN ('MA','MI','MR','IA','II','IR')");
        if ($ins && !empty($ins)) {
            $_SESSION["jsonsalida"]["idclase"] = $ins["idclase"];
            $_SESSION["jsonsalida"]["numid"] = $ins["numid"];
            $_SESSION["jsonsalida"]["nombrecliente"] = $ins["razonsocial"];
            $_SESSION["jsonsalida"]["apellido1cliente"] = $ins["apellido1"];
            $_SESSION["jsonsalida"]["apellido2cliente"] = $ins["apellido2"];
            $_SESSION["jsonsalida"]["nombre1cliente"] = $ins["nombre1"];
            $_SESSION["jsonsalida"]["nombre2cliente"] = $ins["nombre2"];
            $_SESSION["jsonsalida"]["direccion"] = $ins["dircom"];
            $_SESSION["jsonsalida"]["direccionnot"] = $ins["dirnot"];
            $_SESSION["jsonsalida"]["idmunicipio"] = $ins["muncom"];
            $_SESSION["jsonsalida"]["idmunicipionot"] = $ins["munnot"];
            $_SESSION["jsonsalida"]["zonapostal"] = $ins["codigopostalcom"];
            $_SESSION["jsonsalida"]["codposcom"] = $ins["codigopostalcom"];
            $_SESSION["jsonsalida"]["codposnot"] = $ins["codigopostalnot"];
            $_SESSION["jsonsalida"]["telefono"] = $ins["telcom1"];
            $_SESSION["jsonsalida"]["movil"] = $ins["telcom2"];
            $_SESSION["jsonsalida"]["pais"] = 'CO';
            $_SESSION["jsonsalida"]["lenguaje"] = 'es';
            $_SESSION["jsonsalida"]["email"] = $ins["emailcom"];
            $_SESSION["jsonsalida"]["organizacion"] = $ins["organizacion"];
            $_SESSION["jsonsalida"]["categoria"] = $ins["categoria"];
            $_SESSION["jsonsalida"]["matricula"] = $ins["matricula"];

            //
            $mysqli->close();
            $_SESSION["jsonsalida"]["codigoerror"] = "0000";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Desde mreg_est_inscritos';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
            exit();
        }

        // ********************************************************************** //
        // Recupera de datos cliente
        // ********************************************************************** //
        $de = retornarRegistroMysqliApi($mysqli, 'datos_empresas', "tipoidentificacion='" . $_SESSION["entrada"]["idtipoidentificacion"] . "' and identificacion='" . $_SESSION["entrada"]["identificacion"] . "'");
        if ($de && !empty($de)) {
            if ($_SESSION["entrada"]["idtipoidentificacion"] == '2') {
                $_SESSION["jsonsalida"]["idclase"] = '2';
                $_SESSION["jsonsalida"]["numid"] = $_SESSION["entrada"]["identificacion"];
                $_SESSION["jsonsalida"]["nombrecliente"] = $de["razonsocial"];
                $_SESSION["jsonsalida"]["apellido1cliente"] = '';
                $_SESSION["jsonsalida"]["apellido2cliente"] = '';
                $_SESSION["jsonsalida"]["nombre1cliente"] = '';
                $_SESSION["jsonsalida"]["nombre2cliente"] = '';
            } else {
                $_SESSION["jsonsalida"]["nombrecliente"] = $de["primerapellido"];
                if (trim($de["segundoapellido"]) != '') {
                    $_SESSION["jsonsalida"]["nombrecliente"] .= ' ' . $de["segundoapellido"];
                }
                if (trim($de["primernombre"]) != '') {
                    $_SESSION["jsonsalida"]["nombrecliente"] .= ' ' . $de["primernombre"];
                }
                if (trim($de["segundonombre"]) != '') {
                    $_SESSION["jsonsalida"]["nombrecliente"] .= ' ' . $de["segundonombre"];
                }
                $_SESSION["jsonsalida"]["apellido1cliente"] = $de["primerapellido"];
                $_SESSION["jsonsalida"]["apellido2cliente"] = $de["segundoapellido"];
                $_SESSION["jsonsalida"]["nombre1cliente"] = $de["primernombre"];
                $_SESSION["jsonsalida"]["nombre2cliente"] = $de["segundonombre"];
            }
            $_SESSION["jsonsalida"]["direccion"] = strtoupper($de["dircom"]);
            $_SESSION["jsonsalida"]["direccionnot"] = strtoupper($de["dirnot"]);
            $_SESSION["jsonsalida"]["idmunicipio"] = $de["muncom"];
            $_SESSION["jsonsalida"]["idmunicipionot"] = $de["munnot"];
            $_SESSION["jsonsalida"]["zonapostal"] = $de["zonapostal"];
            $_SESSION["jsonsalida"]["codposcom"] = $de["codposcom"];
            $_SESSION["jsonsalida"]["codposnot"] = $de["codposnot"];
            $_SESSION["jsonsalida"]["telefono"] = $de["telefono1"];
            $_SESSION["jsonsalida"]["movil"] = $de["telefono2"];
            $_SESSION["jsonsalida"]["pais"] = $de["pais"];
            $_SESSION["jsonsalida"]["lenguaje"] = strtolower($de["lenguaje"]);
            $_SESSION["jsonsalida"]["codigoregimen"] = $de["codigoregimen"];
            $_SESSION["jsonsalida"]["responsabilidadfiscal"] = $de["responsabilidadfiscal"];
            $_SESSION["jsonsalida"]["codigoimpuesto"] = $de["codigoimpuesto"];
            $_SESSION["jsonsalida"]["nombreimpuesto"] = strtoupper($de["nombreimpuesto"]);
            $_SESSION["jsonsalida"]["responsabilidadtributaria"] = strtoupper($de["responsabilidadtributaria"]);
            $_SESSION["jsonsalida"]["email"] = strtolower($de["email"]);
            //
            $_SESSION["jsonsalida"]["saldoprepago"] = 0;
            $_SESSION["jsonsalida"]["claveprepago"] = '';

            //
            $mysqli->close();
            $_SESSION["jsonsalida"]["codigoerror"] = "0000";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Desde datos_empresas';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
            exit();
        }

        // ********************************************************************** //
        // Recupera de datos desde prepago
        // ********************************************************************** //
        /*
        if ($prep["nombre"] != '') {
            if ($_SESSION["entrada"]["idtipoidentificacioncliente"] == '2') {
                $_SESSION["jsonsalida"]["nombrecliente"] = $prep["razonsocial"];
                $_SESSION["jsonsalida"]["apellido1cliente"] = '';
                $_SESSION["jsonsalida"]["apellido2cliente"] = '';
                $_SESSION["jsonsalida"]["nombre1cliente"] = '';
                $_SESSION["jsonsalida"]["nombre2cliente"] = '';
            } else {
                $_SESSION["jsonsalida"]["nombrecliente"] = $prep["apellido1"];
                if (trim($prep["apellido2"]) != '') {
                    $_SESSION["jsonsalida"]["nombrecliente"] .= ' ' . $prep["apellido2"];
                }
                if (trim($prep["nombre1"]) != '') {
                    $_SESSION["jsonsalida"]["nombrecliente"] .= ' ' . $prep["nombre1"];
                }
                if (trim($prep["nombre2"]) != '') {
                    $_SESSION["jsonsalida"]["nombrecliente"] .= ' ' . $prep["nombre2"];
                }
                $_SESSION["jsonsalida"]["apellido1cliente"] = $prep["apellido1"];
                $_SESSION["jsonsalida"]["apellido2cliente"] = $prep["apellido2"];
                $_SESSION["jsonsalida"]["nombre1cliente"] = $prep["nombre1"];
                $_SESSION["jsonsalida"]["nombre2cliente"] = $prep["nombre2"];
            }
            $_SESSION["jsonsalida"]["direccion"] = strtoupper($prep["direccion"]);
            $_SESSION["jsonsalida"]["direccionnot"] = strtoupper($prep["direccion"]);
            $_SESSION["jsonsalida"]["idmunicipio"] = $prep["municipio"];
            $_SESSION["jsonsalida"]["idmunicipionot"] = $prep["municipio"];
            $_SESSION["jsonsalida"]["zonapostal"] = '';
            $_SESSION["jsonsalida"]["codposcom"] = '';
            $_SESSION["jsonsalida"]["codposnot"] = '';
            $_SESSION["jsonsalida"]["telefono"] = $prep["telefono"];
            $_SESSION["jsonsalida"]["movil"] = $prep["celular"];
            $_SESSION["jsonsalida"]["pais"] = '';
            $_SESSION["jsonsalida"]["lenguaje"] = 'es';
            $_SESSION["jsonsalida"]["codigoregimen"] = '';
            $_SESSION["jsonsalida"]["responsabilidadfiscal"] = '';
            $_SESSION["jsonsalida"]["codigoimpuesto"] = '';
            $_SESSION["jsonsalida"]["nombreimpuesto"] = '';
            $_SESSION["jsonsalida"]["responsabilidadtributaria"] = '';
            $_SESSION["jsonsalida"]["email"] = strtolower($prep["email"]);
            $_SESSION["jsonsalida"]["saldoprepago"] = $saldop;
            $_SESSION["jsonsalida"]["claveprepago"] = $clavep;
            $mysqli->close();
            $_SESSION["jsonsalida"]["codigoerror"] = "0000";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Desde datos_empresas';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
            exit();
        }
        */
        $saldop = 0;
        $clavep = '';
        
        //
        $_SESSION["jsonsalida"]["nombrecliente"] = '';
        $_SESSION["jsonsalida"]["apellido1cliente"] = '';
        $_SESSION["jsonsalida"]["apellido2cliente"] = '';
        $_SESSION["jsonsalida"]["nombre1cliente"] = '';
        $_SESSION["jsonsalida"]["nombre2cliente"] = '';
        $_SESSION["jsonsalida"]["direccion"] = '';
        $_SESSION["jsonsalida"]["direccionnot"] = '';
        $_SESSION["jsonsalida"]["idmunicipio"] = '';
        $_SESSION["jsonsalida"]["idmunicipionot"] = '';
        $_SESSION["jsonsalida"]["zonapostal"] = '';
        $_SESSION["jsonsalida"]["codposcom"] = '';
        $_SESSION["jsonsalida"]["codposnot"] = '';
        $_SESSION["jsonsalida"]["telefono"] = '';
        $_SESSION["jsonsalida"]["movil"] = '';
        $_SESSION["jsonsalida"]["pais"] = 'CO';
        $_SESSION["jsonsalida"]["lenguaje"] = 'es';
        $_SESSION["jsonsalida"]["email"] = '';
        $_SESSION["jsonsalida"]["codigoregimen"] = '49';
        $_SESSION["jsonsalida"]["responsabilidadfiscal"] = 'R-99-PN';
        $_SESSION["jsonsalida"]["codigoimpuesto"] = '';
        $_SESSION["jsonsalida"]["nombreimpuesto"] = '';
        $_SESSION["jsonsalida"]["responsabilidadtributaria"] = '';
        $_SESSION["jsonsalida"]["saldoprepago"] = $saldop;
        $_SESSION["jsonsalida"]["claveprepago"] = $clavep;
        $mysqli->close();
        $_SESSION["jsonsalida"]["codigoerror"] = "0000";
        $_SESSION["jsonsalida"]["mensajeerror"] = 'No cruzo, no existe (' . $_SESSION["generales"]["codigoempresa"] . ')';
        $api->response($api->json($_SESSION["jsonsalida"]), 200);
    }
    
    public function mregRadicarDocumentosValidarAni(API $api) {
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistralesCalculos.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRues.php');

        // array de respuesta
        $_SESSION["jsonsalida"] = array();
        $_SESSION["jsonsalida"]["codigoerror"] = '0000';
        $_SESSION["jsonsalida"]["mensajeerror"] = '';

        //
        if ($api->get_request_method() != "POST") {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'La petición debe ser POST';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        //
        $mysqli = conexionMysqliApi();

        //
        $api->validarParametro("usuariows", true);
        $api->validarParametro("token", true);
        $api->validarParametro("tipoidentificacion", true);
        $api->validarParametro("identificacion", true);

        // ********************************************************************** //
        // Valida que el usuario reportado tenga acceso a la BD y al metodo
        // ********************************************************************** // 
        if (!$api->validarToken('mregRadicarDocumentosValidarAni', $_SESSION["entrada"]["token"], $_SESSION["entrada"]["usuariows"])) {
            $mysqli->close();
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Sin permisos para ejecutar el método mregRadicarDocumentosValidarAni ';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        $_SESSION["jsonsalida"] = \funcionesRues::consumirANI2($mysqli, $_SESSION["entrada"]["tipoidentificacion"], $_SESSION["entrada"]["identificacion"]);

        //
        $mysqli->close();

        // **************************************************************************** //
        // Resultado
        // **************************************************************************** //
        \logApi::peticionRest('api_' . __FUNCTION__);
        $json = $api->json($_SESSION["jsonsalida"]);
        $api->response(str_replace("\\/", "/", $json), 200);
    }

    public function mregRadicarDocumentosDetalleTransaccion(API $api) {
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistralesCalculos.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRues.php');

        // array de respuesta
        $_SESSION["jsonsalida"] = array();
        $_SESSION["jsonsalida"]["codigoerror"] = '0000';
        $_SESSION["jsonsalida"]["mensajeerror"] = '';
        $_SESSION["jsonsalida"]["html"] = '';

        //
        if ($api->get_request_method() != "POST") {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'La petición debe ser POST';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        //
        $mysqli = conexionMysqliApi();

        $det = retornarRegistrosMysqliApi($mysqli, 'mreg_liquidaciondetalle', "idliquidacion=" . $_SESSION["entrada"]["idliquidacion"] . " and idsec='" . $_SESSION["entrada"]["idsecuencia"] . "'", "secuencia");
        $html = '<div class="table-responsive-sm">
            <table class="table">
            <thead>
                <tr>
                    <th scope="col">Servicio</th>
                    <th scope="col">Matrícula</th>
                    <th scope="col">Cant</th>
                    <th scope="col">Base</th>
                    <th scope="col">Valor</th>
                </tr>
            </thead>
            <tbody>';
    
        foreach ($det as $d) {
            $html .= '<tr>
            <td><small>' . $d["idservicio"] . ' ' . retornarRegistroMysqliApi($mysqli, 'mreg_servicios', "idservicio='" . $d["idservicio"] . "'", "nombre") . '</small></td>            
            <td><small>' . $d["expediente"] . ' ' . $d["nombre"] . '</small></td>
            <td><small>' . $d["cantidad"] . '</small></td>
            <td><small>$' . number_format($d["valorbase"],2) . '</small></td>
            <td><small>$' . number_format($d["valorservicio"],2) . '</small></td>    
            </tr>';
        }
        
        $html .= '</tbody>
            </table>
            </div>';

        //
        $_SESSION["jsonsalida"]["html"] = base64_encode($html);

        //
        $mysqli->close();

        // **************************************************************************** //
        // Resultado
        // **************************************************************************** //
        $json = $api->json($_SESSION["jsonsalida"]);
        $api->response(str_replace("\\/", "/", $json), 200);
    }
    
    public function mregRadicarDocumentosDetalleLiquidacion(API $api) {
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistralesCalculos.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRues.php');

        // array de respuesta
        $_SESSION["jsonsalida"] = array();
        $_SESSION["jsonsalida"]["codigoerror"] = '0000';
        $_SESSION["jsonsalida"]["mensajeerror"] = '';
        $_SESSION["jsonsalida"]["html"] = '';

        //
        if ($api->get_request_method() != "POST") {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'La petición debe ser POST';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        //
        $mysqli = conexionMysqliApi();

        $det = retornarRegistrosMysqliApi($mysqli, 'mreg_liquidaciondetalle', "idliquidacion=" . $_SESSION["entrada"]["idliquidacion"], "secuencia");
        $html = '<div class="table-responsive-sm">
            <table class="table">
            <thead>
                <tr>
                    <th scope="col">Secuencia</th>
                    <th scope="col">Servicio</th>
                    <th scope="col">Matrícula</th>
                    <th scope="col">Cant</th>
                    <th scope="col">Base</th>
                    <th scope="col">Valor</th>
                </tr>
            </thead>
            <tbody>';
    
        foreach ($det as $d) {
            $html .= '<tr>
            <td><small>' . $d["idsec"] . '</small></td>            
            <td><small>' . $d["idservicio"] . ' ' . retornarRegistroMysqliApi($mysqli, 'mreg_servicios', "idservicio='" . $d["idservicio"] . "'", "nombre") . '</small></td>            
            <td><small>' . $d["expediente"] . ' ' . $d["nombre"] . '</small></td>
            <td><small>' . $d["cantidad"] . '</small></td>
            <td><small>$' . number_format($d["valorbase"],2) . '</small></td>
            <td><small>$' . number_format($d["valorservicio"],2) . '</small></td>    
            </tr>';
        }
        
        $html .= '</tbody>
            </table>
            </div>';

        //
        $_SESSION["jsonsalida"]["html"] = base64_encode($html);

        //
        $mysqli->close();

        // **************************************************************************** //
        // Resultado
        // **************************************************************************** //
        $json = $api->json($_SESSION["jsonsalida"]);
        $api->response(str_replace("\\/", "/", $json), 200);
    }

    public function mregRadicarDocumentosBorrarAnexoLiquidacion(API $api) {
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistralesCalculos.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRues.php');

        // array de respuesta
        $_SESSION["jsonsalida"] = array();
        $_SESSION["jsonsalida"]["codigoerror"] = '0000';
        $_SESSION["jsonsalida"]["mensajeerror"] = '';

        //
        if ($api->get_request_method() != "POST") {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'La petición debe ser POST';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        //
        $mysqli = conexionMysqliApi();

        //
        $api->validarParametro("usuariows", true);
        $api->validarParametro("token", true);
        $api->validarParametro("idanexo", true);

        // ********************************************************************** //
        // Valida que el usuario reportado tenga acceso a la BD y al metodo
        // ********************************************************************** // 
        if (!$api->validarToken('mregRadicarDocumentosBorrarAnexoLiquidacion', $_SESSION["entrada"]["token"], $_SESSION["entrada"]["usuariows"])) {
            $mysqli->close();
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Sin permisos para ejecutar el método mregRadicarDocumentosBorrarAnexoLiquidacion ';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        //
        $anx = retornarRegistroMysqliApi($mysqli, 'mreg_anexos_liquidaciones', "idanexo=" . $_SESSION["entrada"]["idanexo"]);
        if ($anx && !empty($anx)) {
            if (trim($anx["path"]) != '') {
                if (file_exists($_SESSION["generales"]["pathabsoluto"] . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/' . $anx["path"] . $anx["idanexo"] . '.' . $anx["tipoarchivo"])) {
                    unlink($_SESSION["generales"]["pathabsoluto"] . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/' . $anx["path"] . $anx["idanexo"] . '.' . $anx["tipoarchivo"]);
                }
            }
            borrarRegistrosMysqliApi($mysqli, 'mreg_anexos_liquidaciones', "idanexo=" . $_SESSION["entrada"]["idanexo"]);
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

    public function mregRadicarDocumentosBuscarExpediente(API $api) {
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistralesCalculos.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/presentacion.class.php');

        $string = '';
        $pres = new \presentacionBootstrap();
        $string .= $pres->abrirPanelGeneral(800);
        $string .= '<br>';
        $string .= $pres->abrirPanel();
        $string .= $pres->armarCampoTextoOculto('_bloque1', $_SESSION["entrada"]["bloque"]);
        $txt = '';
        $txt .= 'A través de esta opción usted puede realizar la búsqueda de los expedientes ';
        $txt .= 'que se encuentran inscritos en los registros que administra nuestra entidad. ';
        $txt .= 'Por favor indique el contenido a buscar en el criterio que desee y ';
        $txt .= 'oprima el botón continuar. ';
        $string .= $pres->armarLineaTextoInformativa($txt, 'center');
        $string .= '<br>';

        $string .= $pres->abrirRow();
        $string .= $pres->armarCampoTextoMd('Identificación', 'no', '_identificacion1', 2, '');
        $string .= $pres->armarCampoTextoMd('Matrícula', 'no', '_matricula1', 2, '');
        $string .= $pres->armarCampoTextoMd('Nombre o razón social', 'no', '_nombre1', 3, '');
        $string .= $pres->armarCampoTextoMd('Palabras', 'no', '_palabras1', 3, '');
        $string .= $pres->cerrarRow();
        $string .= '<br>';
        $string .= $pres->armarBotonDinamico('javascript', 'Continuar', 'seleccionBuscarExpedienteContinuar();');
        $string .= '<br>';
        $string .= $pres->cerrarPanel();
        $string .= '<br>';
        $string .= $pres->cerrarPanelGeneral();
        unset($pres);

        //
        $_SESSION["jsonsalida"]["codigoerror"] = "0000";
        $_SESSION["jsonsalida"]["mensajeerror"] = '';
        $_SESSION["jsonsalida"]["html"] = base64_encode($string);
        $api->response($api->json($_SESSION["jsonsalida"]), 200);
    }

    public function mregRadicarDocumentosBuscarExpedienteContinuar(API $api) {
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistralesCalculos.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/presentacion.class.php');

        //
        if (trim(base64_decode($_SESSION["entrada"]["matricula"])) == '' &&
                trim(base64_decode($_SESSION["entrada"]["identificacion"])) == '' &&
                trim(base64_decode($_SESSION["entrada"]["nombre"])) == '' &&
                trim(base64_decode($_SESSION["entrada"]["palabras"])) == '') {
            $_SESSION["entrada"]["nombre"] = 'A';
        }

        //
        $mysqli = conexionMysqliApi();

        //
        $res1 = \funcionesGenerales::buscarExpedientes($mysqli, base64_decode($_SESSION["entrada"]["identificacion"]), base64_decode($_SESSION["entrada"]["matricula"]), '', base64_decode($_SESSION["entrada"]["nombre"]), base64_decode($_SESSION["entrada"]["palabras"]), 200);

        //
        $res = array();
        foreach ($res1 as $r) {
            if ($r["ctrestmatricula"] == 'MA' || $r["ctrestmatricula"] == 'IA' || $r["ctrestmatricula"] == 'MR' || $r["ctrestmatricula"] == 'IR') {
                if ($r["fecrenovacion"] != '') {
                    if ($r["ultanoren"] < date("Y")) {
                        if (\funcionesRegistrales::inactivarSiprefMatriculas($mysqli, $r["matricula"], $r["fecmatricula"], $r["fecrenovacion"])) {
                            $r["ctrestmatricula"] = 'MI';
                            if (substr($r["matricula"], 0, 1) == 'S') {
                                $r["ctrestmatricula"] = 'II';
                            }
                        }
                    }
                }
                $r["tipopropiedad"] = '';
                if ($r["organizacion"] == '02') {
                    $cp = 0;
                    $r["tipopropiedad"] = 'Propietario único';
                    $prs = retornarRegistrosMysqliApi($mysqli, 'mreg_est_propietarios', "matricula='" . $r["matricula"] . "'", "id");
                    if ($prs && !empty($prs)) {
                        foreach ($prs as $ps) {
                            if ($ps["estado"] == 'V') {
                                $cp++;
                            }
                        }
                        if ($cp > 1) {
                            $r["tipopropiedad"] = 'Multiples propietarios';
                        }
                    }
                }
            }
            $res[] = $r;
        }
        unset($res1);

        //
        if (empty($res)) {
            $mysqli->close();
            $string = 'No se encontraron registros que cumplan con el criterio indicado, cierre esta ventana y busque de nuevo';
            $_SESSION["jsonsalida"]["codigoerror"] = "0000";
            $_SESSION["jsonsalida"]["mensajeerror"] = '';
            $_SESSION["jsonsalida"]["html"] = base64_encode($string);
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        $txt = '<table>';
        $txt .= '<tr>';
        $txt .= '<th style="width:20%">Matrícula</th>';
        $txt .= '<th style="width:70%">Nombre o razón social</th>';
        $txt .= '<th style="width:10%">Estado</th>';
        $txt .= '</tr>';
        foreach ($res as $tx) {
            $dat = array(
                'bloque' => base64_decode($_SESSION["entrada"]["bloque"]),
                'camara' => $_SESSION["generales"]["codigoempresa"],
                'matricula' => $tx["matricula"],
                'razonsocial' => $tx["razonsocial"],
                'sigla' => $tx["sigla"],
                'apellido1' => $tx["apellido1"],
                'apellido2' => $tx["apellido2"],
                'nombre1' => $tx["nombre1"],
                'nombre2' => $tx["nombre2"],
                'idclase' => $tx["idclase"],
                'numid' => $tx["numid"],
                'nit' => $tx["nit"],
                'organizacion' => $tx["organizacion"],
                'categoria' => $tx["categoria"],
                'muncom' => $tx["muncom"],
                'emailcom' => $tx["emailcom"],
                'telcom1' => $tx["telcom1"],
                'acttot' => $tx["acttot"],
                'personal' => $tx["personal"]
            );
            $parametros = base64_encode(json_encode($dat));
            $txt .= '<tr>';
            $txt .= '<td>';
            $txt .= '<a href="javascript:seleccionBuscarExpedienteFijar(\'' . $parametros . '\');">' . $tx["matricula"] . '</a><br>';
            $txt .= '</td>';
            $txt .= '<td>';
            $txt .= \funcionesGenerales::utf8_decode($tx["razonsocial"]);
            $txt .= '</td>';
            $txt .= '<td>';
            $txt .= $tx["ctrestmatricula"];
            $txt .= '</td>';
            $txt .= '</tr>';
        }
        $txt .= '</table>';
        $mysqli->close();
        $_SESSION["jsonsalida"]["codigoerror"] = "0000";
        $_SESSION["jsonsalida"]["mensajeerror"] = '';
        $_SESSION["jsonsalida"]["html"] = base64_encode($txt);
        $api->response($api->json($_SESSION["jsonsalida"]), 200);
    }

}

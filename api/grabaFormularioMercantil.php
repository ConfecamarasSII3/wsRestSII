<?php

namespace api;

use api\API;

trait grabaFormularioMercantil {

    public function grabaFormularioMercantilSeleccionarCiiu(API $api) {
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
        $arrBtnEnlace [] = 'seleccionarCiiuContinuar(\'' . $_SESSION["entrada"]["numciiu"] . '\');';
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

    public function grabaFormularioMercantilSeleccionarCiiuContinuar(API $api) {
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
                        $txt = '<a href="javascript:seleccionarCiiuContinuarFin(\'' . $_SESSION["entrada"]["numciiu"] . '\',\'' . $x["idciiu"] . '\',\'' . base64_encode($x["descripcion"]) . '\')"><strong>' . $x["idciiu"] . '</strong> (actividad catalogada como no comercial para personas naturales y establecimientos)</a><br>';
                    } else {
                        $txt = '<a href="javascript:seleccionarCiiuContinuarFin(\'' . $_SESSION["entrada"]["numciiu"] . '\',\'' . $x["idciiu"] . '\',\'' . base64_encode($x["descripcion"]) . '\')"><strong>' . $x["idciiu"] . '</strong></a><br>';
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

    public function grabaFormularioMercantilGeneradorDirecciones(API $api) {
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
        $api->validarParametro("tipodir", true);
        $api->validarParametro("dir", false);
        $api->validarParametro("session_parameters", false);

        // ********************************************************************** //
        // Valida session_parameters
        // ********************************************************************** //
        if (isset($_SESSION ["entrada"] ["session_parameters"]) && $_SESSION ["entrada"] ["session_parameters"] != '') {
            \funcionesGenerales::desarmarVariablesPantalla($_SESSION ["entrada"] ["session_parameters"]);
        }

        //
        $mysqli = conexionMysqliApi();
        $tipovias = \funcionesGenerales::retornarArregloTipoVias($mysqli);
        $letras = \funcionesGenerales::retornarArregloLetras($mysqli);
        $apendices = \funcionesGenerales::retornarArregloApendices($mysqli);
        $orientaciones = \funcionesGenerales::retornarArregloOrientaciones($mysqli);
        $mysqli->close();

        //
        $pres = new \presentacionBootstrap ();
        // $string = $pres->abrirPanel();
        $string = $pres->armarCampoMultiTextoProtegidoMd('Dirección', 'direccion', 12, 3, 70, $_SESSION["entrada"]["dir"]);
        $arrBtnTipo = array('javascript', 'javascript', 'javascript', 'javascript');
        $arrBtnEnlace = array('limpiarDireccion()', 'borrarUltimaDireccion()', 'fijarDireccion(\'' . $_SESSION["entrada"]["tipodir"] . '\')', 'abandonarGeneradorDirecciones()');
        $arrBtnImagen = array('Limpiar dirección', 'Borrar última palabra', 'Trasladar dirección al formulario', 'Abandonar');
        $string .= $pres->armarBotonesDinamicos($arrBtnTipo, $arrBtnImagen, $arrBtnEnlace);
        $string .= '<hr>';
        $string .= $pres->armarLineaTextoInformativa('Vía principal', 'left', 'h3');
        $string .= $pres->abrirRow();
        $string .= $pres->armarCampoSelectOnChangeMd('Nomenclaturas', 'no', 'viappaltipo', 3, '', $tipovias, 'armarDireccion()');
        $string .= $pres->armarCampoTextoMd('Nombre de vías', 'no', 'viappalnombre', 3, '', '', '', '', '', 'armarDireccion()');
        $string .= $pres->armarCampoTextoMd('Nro', 'no', 'viappalnro', 3, '', '', '', '', '', 'armarDireccion()');
        $string .= $pres->armarCampoSelectOnChangeMd('Letras', 'no', 'viappalletras', 3, '', $letras, 'armarDireccion()');
        $string .= $pres->cerrarRow();

        unset($pres);
        $_SESSION ["jsonsalida"] ["html"] = base64_encode($string);

        // **************************************************************************** //
        // Resultado
        // **************************************************************************** //
        // \logApi::peticionRest('api_' . __FUNCTION__);
        $json = $api->json($_SESSION ["jsonsalida"]);
        $api->response(str_replace("\\/", "/", $json), 200);
    }

    public function grabaFormularioMercantilSeleccionarResponsabilidades(API $api) {
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
        $api->validarParametro("num", true);
        $api->validarParametro("organizacion", false);
        $api->validarParametro("session_parameters", false);
        if (!isset($_SESSION ["entrada"] ["organizacion"])) {
            $_SESSION ["entrada"] ["organizacion"] = '';
        }

        // ********************************************************************** //
        // Valida session_parameters
        // ********************************************************************** //
        if (isset($_SESSION ["entrada"] ["session_parameters"]) && $_SESSION ["entrada"] ["session_parameters"] != '') {
            \funcionesGenerales::desarmarVariablesPantalla($_SESSION ["entrada"] ["session_parameters"]);
        }

        //
        $txt = '<table>';
        $txt .= '<tbody>';
        $txt .= '<tr><td>';
        $txt .= '<a href="javascript:seleccionarResponsabilidadesContinuar(\'' . $_SESSION["entrada"]["num"] . '\',\'\');"><small>' . 'Limpiar campo' . '</small></a></br>';
        $iLin = 0;
        $mysqli = conexionMysqliApi();
        if ($_SESSION ["entrada"] ["organizacion"] == '') {
            $tems = retornarRegistrosMysqliApi($mysqli, 'tablas', "tabla='responsabilidadestributarias' and campo1 <> 'NO'", "idcodigo");
        } else {
            if ($_SESSION ["entrada"] ["organizacion"] == '01') {
                $tems = retornarRegistrosMysqliApi($mysqli, 'tablas', "tabla='responsabilidadestributarias' and campo1 <> 'NO' and campo2 = 'SI'", "idcodigo");
            } else {
                if ($_SESSION ["entrada"] ["organizacion"] == '12' || $_SESSION ["entrada"] ["organizacion"] == '14') {
                    $tems = retornarRegistrosMysqliApi($mysqli, 'tablas', "tabla='responsabilidadestributarias' and campo1 <> 'NO' and campo4 = 'SI'", "idcodigo");
                } else {
                    $tems = retornarRegistrosMysqliApi($mysqli, 'tablas', "tabla='responsabilidadestributarias' and campo1 <> 'NO' and campo3 = 'SI'", "idcodigo");
                }
            }
        }
        $cant = count($tems) + 1;
        $cant = \funcionesGenerales::parteEntera($cant / 2);
        foreach ($tems as $t) {
            $iLin++;
            if ($iLin > $cant) {
                $iLin = 0;
                $txt .= '</td>';
                $txt .= '<td>';
            }
            $txt .= '<a href="javascript:seleccionarResponsabilidadesContinuar(\'' . $_SESSION["entrada"]["num"] . '\',\'' . $t["idcodigo"] . '\');"><small>' . $t["idcodigo"] . ' - ' . $t["descripcion"] . '</small></a></br>';
        }
        $txt .= '</td></tr>';
        $txt .= '</tbody>';
        $txt .= '</table>';
        $mysqli->close();

        //
        $pres = new \presentacionBootstrap ();
        $string = $pres->abrirPanel();
        $string .= $pres->armarLineaTextoInformativa($txt, 'left');
        $string .= $pres->cerrarPanel();
        unset($pres);

        //
        $_SESSION ["jsonsalida"] ["html"] = base64_encode($string);

        // **************************************************************************** //
        // Resultado
        // **************************************************************************** //
        // \logApi::peticionRest('api_' . __FUNCTION__);
        $json = $api->json($_SESSION ["jsonsalida"]);
        $api->response(str_replace("\\/", "/", $json), 200);
    }

    public function grabaFormularioMercantilValidarFormulario(API $api) {
        require_once ('mysqli.php');
        require_once ('funcionesGenerales.php');
        require_once ('funcionesRegistrales.php');
        require_once ('log.php');
        require_once ('myErrorHandler.php');
        set_error_handler('myErrorHandler');

        $nameLog = 'api_grabaFormularioMercantilValidarFormulario_' . date("Ymd");
        if (base64_decode($_SESSION["entrada"]["idliquidacion"]) == 0) {
            \logApi::general2($nameLog, base64_decode($_SESSION["entrada"]["matricula"]), 'Ingreso a grabar datos del formulario');
        } else {
            \logApi::general2($nameLog, base64_decode($_SESSION["entrada"]["idliquidacion"]), 'Ingreso a grabar datos del formulario');
        }

        // array de respuesta
        $_SESSION ["jsonsalida"] = array();
        $_SESSION ["jsonsalida"] ["codigoerror"] = '0000';
        $_SESSION ["jsonsalida"] ["mensajeerror"] = '';
        $_SESSION ["jsonsalida"] ["html"] = '';
        $_SESSION ["jsonsalida"] ["focus"] = '';

        //
        if ($api->get_request_method() != "POST") {
            $_SESSION ["jsonsalida"] ["codigoerror"] = "9999";
            $_SESSION ["jsonsalida"] ["mensajeerror"] = 'La petición debe ser POST';
            $api->response($api->json($_SESSION ["jsonsalida"]), 200);
        }

        //
        $ok = 'no';
        if (!isset($_SESSION["entrada"]["formulariotipotramite"])) {
            $_SESSION["entrada"]["formulariotipotramite"] = '';
        }
        if (trim(base64_decode($_SESSION["entrada"]["formulariotipotramite"])) == 'actualizacionmatriculados') {
            $api->validarParametro("session_parameters", false);
            $ok = 'si';
        }
        if (trim(base64_decode($_SESSION["entrada"]["formulariotipotramite"])) == 'actualizacionafiliados') {
            $api->validarParametro("session_parameters", false);
            $ok = 'si';
        }
        if (trim(base64_decode($_SESSION["entrada"]["formulariotipotramite"])) == 'digitacionmatriculados') {
            $api->validarParametro("session_parameters", false);
            $ok = 'si';
        }

        if ($ok == 'no') {
            $api->validarParametro("idliquidacion", true);
            $api->validarParametro("tipotramite", true);
            $api->validarParametro("formulariotipotramite", true);
            $api->validarParametro("session_parameters", false);
        }

        // ********************************************************************** //
        // Valida session_parameters
        // ********************************************************************** //
        if (isset($_SESSION ["entrada"] ["session_parameters"]) && $_SESSION ["entrada"] ["session_parameters"] != '') {
            \funcionesGenerales::desarmarVariablesPantalla($_SESSION ["entrada"] ["session_parameters"]);
        }

        //
        $res = $this->salvarFormularioMercantil();

        //
        if ($res === false) {
            if ($ok == 'no') {
                if (base64_decode($_SESSION["entrada"]["idliquidacion"]) == 0) {
                    \logApi::general2($nameLog, base64_decode($_SESSION["entrada"]["matricula"]), 'Se presento error salvando el formulario (' . $_SESSION["generales"]["mensajeerror"] . ')');
                } else {
                    \logApi::general2($nameLog, base64_decode($_SESSION["entrada"]["idliquidacion"]), 'Se presento error salvando el formulario (' . $_SESSION["generales"]["mensajeerror"] . ')');
                }
            }
            $_SESSION ["jsonsalida"] ["codigoerror"] = '0001';
            $_SESSION ["jsonsalida"] ["mensajeerror"] = 'Se presento error salvando el formulario (' . $_SESSION["generales"]["mensajeerror"] . ')';
            $json = $api->json($_SESSION ["jsonsalida"]);
            $api->response(str_replace("\\/", "/", $json), 200);
        }

        //
        if ($ok == 'si') {
            if ($_SESSION["formulario"]["datos"]["emprendimientosocial"] == '') {
                if ($_SESSION["formulario"]["datos"]["organizacion"] == '01' || ($_SESSION["formulario"]["datos"]["organizacion"] > '02' && $_SESSION["formulario"]["datos"]["categoria"] == '1')) {
                    if ($_SESSION["formulario"]["datos"]["fechamatricula"] > '20230100' || $_SESSION["formulario"]["datos"]["ultanoren"] >= '2023') {
                        $_SESSION ["jsonsalida"] ["codigoerror"] = '0001';
                        $_SESSION ["jsonsalida"] ["mensajeerror"] = 'Debe indicar si es o no emprendimiento social (Ley 2234 de 2022)';
                        $_SESSION ["jsonsalida"] ["focus"] = 'sexo';
                        $json = $api->json($_SESSION ["jsonsalida"]);
                        $api->response(str_replace("\\/", "/", $json), 200);
                    }
                }
            }
            /*
              $_SESSION ["jsonsalida"] ["codigoerror"] = '0000';
              $_SESSION ["jsonsalida"] ["mensajeerror"] = '';
              $_SESSION ["jsonsalida"] ["html"] = '';
              $json = $api->json($_SESSION ["jsonsalida"]);
              $api->response(str_replace("\\/", "/", $json), 200);
              exit();
             */
        }

        //
        if ($_SESSION["formulario"]["datos"]["claseespesadl"] == '73' || $_SESSION["formulario"]["datos"]["claseespesadl"] == '74' || $_SESSION["formulario"]["datos"]["claseespesadl"] == '75') {
            if ($_SESSION["formulario"]["datos"]["condiespe2219"] == '') {
                $_SESSION ["jsonsalida"] ["codigoerror"] = '0001';
                $_SESSION ["jsonsalida"] ["mensajeerror"] = 'Al seleccionar que la clase de entidad es una Asociación Campesina o Agropecuaria, se debe indicar si esta tiene alguna condición especial de acuerdo con la Ley 2219 de 2022.';
                $_SESSION ["jsonsalida"] ["focus"] = 'condiespe2219';
                $json = $api->json($_SESSION ["jsonsalida"]);
                $api->response(str_replace("\\/", "/", $json), 200);
                exit();
            }
        }


        if ($_SESSION["formulario"]["formulariotipotramite"] == 'actualizacionmatriculados' || $_SESSION["formulario"]["formulariotipotramite"] == 'digitacionmatriculados' || $_SESSION["formulario"]["formulariotipotramite"] == 'digitacion') {
            if ($_SESSION["formulario"]["datos"]["organizacion"] == '01' || ($_SESSION["formulario"]["datos"]["organizacion"] > '02' && $_SESSION["formulario"]["datos"]["categoria"] == '1')) {
                if ($_SESSION["formulario"]["datos"]["ultanoren"] >= '2020') {
                    if ($_SESSION["formulario"]["datos"]["ciiutamanoempresarial"] == '' ||
                            $_SESSION["formulario"]["datos"]["anodatostamanoempresarial"] == '' ||
                            $_SESSION["formulario"]["datos"]["fechadatostamanoempresarial"] == '') {
                        // $mysqli->close();
                        $_SESSION ["jsonsalida"] ["codigoerror"] = '0001';
                        $_SESSION ["jsonsalida"] ["mensajeerror"] = 'Debe complementarse la información del tamaño empresarial';
                        $_SESSION ["jsonsalida"] ["focus"] = 'ciiutamanoempresarial';
                        $json = $api->json($_SESSION ["jsonsalida"]);
                        $api->response(str_replace("\\/", "/", $json), 200);
                    }
                }
            }
        }

        //
        $mysqli = conexionMysqliApi();

        //
        $xTipoTramite = $_SESSION["formulario"]["formulariotipotramite"];
        $txtemergente = '';

        //
        if (ltrim($_SESSION["formulario"]["datos"]["nit"], "0") != '') {
            if (\funcionesGenerales::validarDv(ltrim($_SESSION["formulario"]["datos"]["nit"], "0")) === false) {
                $mysqli->close();
                $_SESSION ["jsonsalida"] ["codigoerror"] = '0001';
                $_SESSION ["jsonsalida"] ["mensajeerror"] = 'El nit parece mal digitado o no se incluyó el dígito de verificación.';
                $_SESSION ["jsonsalida"] ["focus"] = 'nit';
                $json = $api->json($_SESSION ["jsonsalida"]);
                $api->response(str_replace("\\/", "/", $json), 200);
            }
        }

        if ((trim($_SESSION["formulario"]["datos"]["ape1"]) == '') && ($_SESSION["formulario"]["datos"]["organizacion"] == '01')) {
            $mysqli->close();
            $_SESSION ["jsonsalida"] ["codigoerror"] = '0001';
            $_SESSION ["jsonsalida"] ["mensajeerror"] = 'Primer apellido no debe ser blancos';
            $_SESSION ["jsonsalida"] ["focus"] = 'ape1';
            $json = $api->json($_SESSION ["jsonsalida"]);
            $api->response(str_replace("\\/", "/", $json), 200);
        }

        if ((trim($_SESSION["formulario"]["datos"]["nom1"]) == '') && ($_SESSION["formulario"]["datos"]["organizacion"] == '01')) {
            $mysqli->close();
            $_SESSION ["jsonsalida"] ["codigoerror"] = '0001';
            $_SESSION ["jsonsalida"] ["mensajeerror"] = 'Primer nombre no debe ser blancos';
            $_SESSION ["jsonsalida"] ["focus"] = 'nom1';
            $json = $api->json($_SESSION ["jsonsalida"]);
            $api->response(str_replace("\\/", "/", $json), 200);
        }

        if (trim($_SESSION["formulario"]["datos"]["tipoidentificacion"]) == '') {
            if ($_SESSION["formulario"]["datos"]["organizacion"] == '01') {
                $mysqli->close();
                $_SESSION ["jsonsalida"] ["codigoerror"] = '0001';
                $_SESSION ["jsonsalida"] ["mensajeerror"] = 'Tipo de identificación incorrecto';
                $_SESSION ["jsonsalida"] ["focus"] = 'tipoidentificacion';
                $json = $api->json($_SESSION ["jsonsalida"]);
                $api->response(str_replace("\\/", "/", $json), 200);
            }
        }

        if (trim($_SESSION["formulario"]["datos"]["identificacion"]) == '') {
            if ($_SESSION["formulario"]["datos"]["organizacion"] == '01') {
                $mysqli->close();
                $_SESSION ["jsonsalida"] ["codigoerror"] = '0001';
                $_SESSION ["jsonsalida"] ["mensajeerror"] = 'Número de identificación incorrecto';
                $_SESSION ["jsonsalida"] ["focus"] = 'identificacion';
                $json = $api->json($_SESSION ["jsonsalida"]);
                $api->response(str_replace("\\/", "/", $json), 200);
            }
        }

        if ($_SESSION["formulario"]["datos"]["sexo"] == '') {
            if ($_SESSION["formulario"]["datos"]["organizacion"] == '01') {
                $mysqli->close();
                $_SESSION ["jsonsalida"] ["codigoerror"] = '0001';
                $_SESSION ["jsonsalida"] ["mensajeerror"] = 'Debe indicar el género de la persona';
                $_SESSION ["jsonsalida"] ["focus"] = 'sexo';
                $json = $api->json($_SESSION ["jsonsalida"]);
                $api->response(str_replace("\\/", "/", $json), 200);
            }
        }

        if ($_SESSION["formulario"]["datos"]["emprendimientosocial"] == '') {
            if ($_SESSION["formulario"]["datos"]["organizacion"] == '01' || ($_SESSION["formulario"]["datos"]["organizacion"] > '02' && $_SESSION["formulario"]["datos"]["categoria"] == '1')) {
                $mysqli->close();
                $_SESSION ["jsonsalida"] ["codigoerror"] = '0001';
                $_SESSION ["jsonsalida"] ["mensajeerror"] = 'Debe indicar si es o no emprendimiento social (Ley 2234 de 2022)';
                $_SESSION ["jsonsalida"] ["focus"] = 'sexo';
                $json = $api->json($_SESSION ["jsonsalida"]);
                $api->response(str_replace("\\/", "/", $json), 200);
            }
        }

        if ($_SESSION["formulario"]["datos"]["organizacion"] == '01' || ($_SESSION["formulario"]["datos"]["organizacion"] > '02' && $_SESSION["formulario"]["datos"]["categoria"] == '1')) {
            if ($_SESSION["formulario"]["datos"]["emprendimientosocial"] == '' || $_SESSION["formulario"]["datos"]["emprendimientosocial"] == 'N') {
                foreach ($_SESSION["formulario"]["datos"] as $key => $valores) {
                    if (substr($key, 0, 10) == 'empsoccat_' || substr($key, 0, 10) == 'empsocben_') {
                        $_SESSION["formulario"]["datos"][$key] = '';
                    }
                }
            } else {
                foreach ($_SESSION["formulario"]["datos"] as $key => $valores) {
                    if (substr($key, 0, 10) == 'empsoccat_' && $valores == 'N') {
                        $_SESSION["formulario"]["datos"][$key] = '';
                    }
                    if (substr($key, 0, 10) == 'empsocben_' && $valores == 'N') {
                        $_SESSION["formulario"]["datos"][$key] = '';
                    }
                }
            }
        } else {
            foreach ($_SESSION["formulario"]["datos"] as $key => $valores) {
                if (substr($key, 0, 10) == 'empsoccat_' || substr($key, 0, 10) == 'empsocben_') {
                    unset($_SESSION["formulario"]["datos"][$key]);
                }
            }
        }


        if ($_SESSION["formulario"]["datos"]["organizacion"] == '01' || ($_SESSION["formulario"]["datos"]["organizacion"] > '02' && $_SESSION["formulario"]["datos"]["categoria"] == '1')) {
            // if ($_SESSION["formulario"]["datos"]["organizacion"] != '12' && $_SESSION["formulario"]["datos"]["organizacion"] != '14') {
            if ($_SESSION["formulario"]["datos"]["ciiutamanoempresarial"] == '') {
                $mysqli->close();
                $_SESSION ["jsonsalida"] ["codigoerror"] = '0001';
                $_SESSION ["jsonsalida"] ["mensajeerror"] = 'Debe indicar una S en el código ciiu del cual provengan sus ingresos o la mayor parte de ellos';
                $_SESSION ["jsonsalida"] ["focus"] = 'maying1';
                $json = $api->json($_SESSION ["jsonsalida"]);
                $api->response(str_replace("\\/", "/", $json), 200);
            }

            $ingmay = '';
            if ($_SESSION["formulario"]["datos"]["maying1"] == 'S') {
                $ingmay .= 'S';
            }
            if ($_SESSION["formulario"]["datos"]["maying2"] == 'S') {
                $ingmay .= 'S';
            }
            if ($_SESSION["formulario"]["datos"]["maying3"] == 'S') {
                $ingmay .= 'S';
            }
            if ($_SESSION["formulario"]["datos"]["maying4"] == 'S') {
                $ingmay .= 'S';
            }
            if ($ingmay != 'S') {
                if ($_SESSION["formulario"]["formulariotipotramite"] !== 'actualizacionmatriculados' && $_SESSION["formulario"]["formulariotipotramite"] !== 'digitacionmatriculados') {
                    $mysqli->close();
                    $_SESSION ["jsonsalida"] ["codigoerror"] = '0001';
                    $_SESSION ["jsonsalida"] ["mensajeerror"] = 'Debe indicar solamente un código ciiu como actividad que genera los mayores ingresos';
                    $_SESSION ["jsonsalida"] ["focus"] = 'maying1';
                    $json = $api->json($_SESSION ["jsonsalida"]);
                    $api->response(str_replace("\\/", "/", $json), 200);
                }
            }
            //}
        }

        //
        if ($_SESSION["formulario"]["datos"]["organizacion"] > '02' && $_SESSION["formulario"]["datos"]["categoria"] == '1') {
            if ($_SESSION["formulario"]["datos"]["organizacion"] != '12' && $_SESSION["formulario"]["datos"]["organizacion"] != '14') {
                if ($_SESSION["formulario"]["tipotramite"] == 'matriculapjur' ||
                        ($_SESSION["formulario"]["tipotramite"] == 'inscripciondocumentos' && $_SESSION["formulario"]["formulariotipotramite"] != 'constitucionpjur')
                ) {
                    if (trim($_SESSION["formulario"]["datos"]["ctrbic"]) != 'S' && trim($_SESSION["formulario"]["datos"]["ctrbic"]) != 'N') {
                        $mysqli->close();
                        $_SESSION ["jsonsalida"] ["codigoerror"] = '0001';
                        $_SESSION ["jsonsalida"] ["mensajeerror"] = 'Debe indicar si la empresa es o no certificada BIC';
                        $_SESSION ["jsonsalida"] ["focus"] = 'ctrbic';
                        $json = $api->json($_SESSION ["jsonsalida"]);
                        $api->response(str_replace("\\/", "/", $json), 200);
                    }
                }
            }
        }



        if (
                $xTipoTramite != 'matriculapnat' && $xTipoTramite != 'matriculapjur' && $xTipoTramite != 'matriculaesadl' && $xTipoTramite != 'inscripciondocumentos' && $xTipoTramite != 'constitucionpjur' && $xTipoTramite != 'constitucionesadl'
        ) {
            if (trim($_SESSION["formulario"]["datos"]["nit"]) == '') {
                if (($_SESSION["formulario"]["datos"]["organizacion"] > '02') && (($_SESSION["formulario"]["datos"]["categoria"] == '0') || ($_SESSION["formulario"]["datos"]["categoria"] == '1'))
                ) {
                    if ($_SESSION["formulario"]["datos"]["ultanoren"] >= '2015') {
                        $mysqli->close();
                        $_SESSION ["jsonsalida"] ["codigoerror"] = '0001';
                        $_SESSION ["jsonsalida"] ["mensajeerror"] = 'Número de nit no debe ser ceros';
                        $_SESSION ["jsonsalida"] ["focus"] = 'nit';
                        $json = $api->json($_SESSION ["jsonsalida"]);
                        $api->response(str_replace("\\/", "/", $json), 200);
                    }
                }
                if ($_SESSION["formulario"]["datos"]["organizacion"] == '01') {
                    if ($_SESSION["formulario"]["datos"]["ultanoren"] >= '2015') {
                        $mysqli->close();
                        $_SESSION ["jsonsalida"] ["codigoerror"] = '0001';
                        $_SESSION ["jsonsalida"] ["mensajeerror"] = 'Número de nit no debe ser ceros';
                        $_SESSION ["jsonsalida"] ["focus"] = 'nit';
                        $json = $api->json($_SESSION ["jsonsalida"]);
                        $api->response(str_replace("\\/", "/", $json), 200);
                    }
                }
            }
        }

        if ($_SESSION["formulario"]["datos"]["organizacion"] == '01') {
            if ($_SESSION["formulario"]["tipotramite"] == 'renovacionmatricula' || $_SESSION["formulario"]["tipotramite"] == 'renovacioesadl') {
                if (defined('RENOVACION_EXIGIR_FECHA_EXPEDICION') && RENOVACION_EXIGIR_FECHA_EXPEDICION == 'S') {
                    if (ltrim(trim($_SESSION["formulario"]["datos"]["fecexpdoc"]), "0") == '') {
                        $mysqli->close();
                        $_SESSION ["jsonsalida"] ["codigoerror"] = '0001';
                        $_SESSION ["jsonsalida"] ["mensajeerror"] = 'La fecha de expedición de documento de identidad es obligatoria';
                        $_SESSION ["jsonsalida"] ["focus"] = 'fecexpdoc';
                        $json = $api->json($_SESSION ["jsonsalida"]);
                        $api->response(str_replace("\\/", "/", $json), 200);
                    }
                    if ($_SESSION["formulario"]["datos"]["organizacion"] == '01') {
                        if ($_SESSION["formulario"]["datos"]["idmunidoc"] != '') {
                            if (retornarRegistroMysqliApi($mysqli, 'bas_municipios', "codigomunicipio='" . $_SESSION["formulario"]["datos"]["idmunidoc"] . "'", "ciudad") === '') {
                                $mysqli->close();
                                $_SESSION ["jsonsalida"] ["codigoerror"] = '0001';
                                $_SESSION ["jsonsalida"] ["mensajeerror"] = 'El municipio de expedición del documento de identidad fue digitado erroneamente';
                                $_SESSION ["jsonsalida"] ["focus"] = 'idmunidoc';
                                $json = $api->json($_SESSION ["jsonsalida"]);
                                $api->response(str_replace("\\/", "/", $json), 200);
                            }
                        }
                    }
                    if ($_SESSION["formulario"]["datos"]["organizacion"] == '01') {
                        if (retornarRegistroMysqliApi($mysqli, 'bas_paises', "codnumpais='" . $_SESSION["formulario"]["datos"]["paisexpdoc"] . "'", "nombrepais") === '') {
                            $mysqli->close();
                            $_SESSION ["jsonsalida"] ["codigoerror"] = '0001';
                            $_SESSION ["jsonsalida"] ["mensajeerror"] = 'El país de expedición del documento de identidad fue digitado erroneamente';
                            $_SESSION ["jsonsalida"] ["focus"] = 'paisexpdoc';
                            $json = $api->json($_SESSION ["jsonsalida"]);
                            $api->response(str_replace("\\/", "/", $json), 200);
                        }
                    }
                }
            } else {
                if (defined('MATRICULAS_EXIGIR_FECHA_EXPEDICION') && MATRICULAS_EXIGIR_FECHA_EXPEDICION == 'S') {
                    if (ltrim(trim($_SESSION["formulario"]["datos"]["fecexpdoc"]), "0") == '') {
                        $mysqli->close();
                        $_SESSION ["jsonsalida"] ["codigoerror"] = '0001';
                        $_SESSION ["jsonsalida"] ["mensajeerror"] = 'La fecha de expedición de documento de identidad es obligatoria';
                        $_SESSION ["jsonsalida"] ["focus"] = 'fecexpdoc';
                        $json = $api->json($_SESSION ["jsonsalida"]);
                        $api->response(str_replace("\\/", "/", $json), 200);
                    }
                    if ($_SESSION["formulario"]["datos"]["organizacion"] == '01') {
                        if ($_SESSION["formulario"]["datos"]["idmunidoc"] != '') {
                            if (retornarRegistroMysqliApi($mysqli, 'bas_municipios', "codigomunicipio='" . $_SESSION["formulario"]["datos"]["idmunidoc"] . "'", "ciudad") === '') {
                                $mysqli->close();
                                $_SESSION ["jsonsalida"] ["codigoerror"] = '0001';
                                $_SESSION ["jsonsalida"] ["mensajeerror"] = 'El municipio de expedición del documento de identidad fue digitado erroneamente';
                                $_SESSION ["jsonsalida"] ["focus"] = 'idmunidoc';
                                $json = $api->json($_SESSION ["jsonsalida"]);
                                $api->response(str_replace("\\/", "/", $json), 200);
                            }
                        }
                    }


                    if ($_SESSION["formulario"]["datos"]["organizacion"] == '01') {
                        if (retornarRegistroMysqliApi($mysqli, 'bas_paises', "codnumpais='" . $_SESSION["formulario"]["datos"]["paisexpdoc"] . "'", "nombrepais") === '') {
                            $mysqli->close();
                            $_SESSION ["jsonsalida"] ["codigoerror"] = '0001';
                            $_SESSION ["jsonsalida"] ["mensajeerror"] = 'El país de expedición del documento de identidad fue digitado erroneamente';
                            $_SESSION ["jsonsalida"] ["focus"] = 'paisexpdoc';
                            $json = $api->json($_SESSION ["jsonsalida"]);
                            $api->response(str_replace("\\/", "/", $json), 200);
                        }
                    }
                }
            }

            if (trim($_SESSION["formulario"]["datos"]["fecexpdoc"]) != '') {
                if (\funcionesGenerales::validarFecha($_SESSION["formulario"]["datos"]["fecexpdoc"]) === false) {
                    $mysqli->close();
                    $_SESSION ["jsonsalida"] ["codigoerror"] = '0001';
                    $_SESSION ["jsonsalida"] ["mensajeerror"] = 'La fecha de expedición de documento de identidad fue digitada erroneamente';
                    $_SESSION ["jsonsalida"] ["focus"] = 'fecexpdoc';
                    $json = $api->json($_SESSION ["jsonsalida"]);
                    $api->response(str_replace("\\/", "/", $json), 200);
                }
            }
        }

        if ($_SESSION["formulario"]["datos"]["organizacion"] == '01') {
            if (trim($_SESSION["formulario"]["datos"]["fecexpdoc"]) != '') {
                if (\funcionesGenerales::validarFecha($_SESSION["formulario"]["datos"]["fecexpdoc"]) === false) {
                    $mysqli->close();
                    $_SESSION ["jsonsalida"] ["codigoerror"] = '0001';
                    $_SESSION ["jsonsalida"] ["mensajeerror"] = 'La fecha de expedición de documento de identidad fue digitada erroneamente';
                    $_SESSION ["jsonsalida"] ["focus"] = 'fecexpdoc';
                    $json = $api->json($_SESSION ["jsonsalida"]);
                    $api->response(str_replace("\\/", "/", $json), 200);
                }
            }
        }

        if ($_SESSION["formulario"]["datos"]["organizacion"] == '01') {
            if ($_SESSION["formulario"]["tipotramite"] == 'renovacionmatricula' || $_SESSION["formulario"]["tipotramite"] == 'renovacioesadl') {
                if (defined('RENOVACION_EXIGIR_FECHA_NACIMIENTO') && RENOVACION_EXIGIR_FECHA_NACIMIENTO == 'S') {
                    if (ltrim(trim($_SESSION["formulario"]["datos"]["fechanacimiento"]), "0") == '') {
                        $mysqli->close();
                        $_SESSION ["jsonsalida"] ["codigoerror"] = '0001';
                        $_SESSION ["jsonsalida"] ["mensajeerror"] = 'La fecha de nacimiento  es obligatoria';
                        $_SESSION ["jsonsalida"] ["focus"] = 'fechanacimiento';
                        $json = $api->json($_SESSION ["jsonsalida"]);
                        $api->response(str_replace("\\/", "/", $json), 200);
                    }
                }
            } else {
                if (defined('MATRICULAS_EXIGIR_FECHA_NACIMIENTO') && MATRICULAS_EXIGIR_FECHA_NACIMIENTO == 'S') {
                    if (ltrim(trim($_SESSION["formulario"]["datos"]["fechanacimiento"]), "0") == '') {
                        $mysqli->close();
                        $_SESSION ["jsonsalida"] ["codigoerror"] = '0001';
                        $_SESSION ["jsonsalida"] ["mensajeerror"] = 'La fecha de nacimiento  es obligatoria';
                        $_SESSION ["jsonsalida"] ["focus"] = 'fechanacimiento';
                        $json = $api->json($_SESSION ["jsonsalida"]);
                        $api->response(str_replace("\\/", "/", $json), 200);
                    }
                }
            }
            if ($_SESSION["formulario"]["datos"]["fechanacimiento"] != '') {
                if (\funcionesGenerales::validarFecha($_SESSION["formulario"]["datos"]["fechanacimiento"]) === false) {
                    $mysqli->close();
                    $_SESSION ["jsonsalida"] ["codigoerror"] = '0001';
                    $_SESSION ["jsonsalida"] ["mensajeerror"] = 'La fecha de nacimiento fue digitada erroneamente';
                    $_SESSION ["jsonsalida"] ["focus"] = 'fechanacimiento';
                    $json = $api->json($_SESSION ["jsonsalida"]);
                    $api->response(str_replace("\\/", "/", $json), 200);
                }
            }
        }

        if ($_SESSION["formulario"]["datos"]["organizacion"] == '01') {
            if (trim($_SESSION["formulario"]["datos"]["nacionalidad"]) == '') {
                $mysqli->close();
                $_SESSION ["jsonsalida"] ["codigoerror"] = '0001';
                $_SESSION ["jsonsalida"] ["mensajeerror"] = 'Debe seleccionar una nacionalidad';
                $_SESSION ["jsonsalida"] ["focus"] = 'nacionalidad';
                $json = $api->json($_SESSION ["jsonsalida"]);
                $api->response(str_replace("\\/", "/", $json), 200);
            }
        }

        if (trim($_SESSION["formulario"]["datos"]["dircom"]) == '') {
            $mysqli->close();
            $_SESSION ["jsonsalida"] ["codigoerror"] = '0001';
            $_SESSION ["jsonsalida"] ["mensajeerror"] = 'Dirección comercial no debe estar en blancos';
            $_SESSION ["jsonsalida"] ["focus"] = 'dircom';
            $json = $api->json($_SESSION ["jsonsalida"]);
            $api->response(str_replace("\\/", "/", $json), 200);
        }

        if (strlen(trim($_SESSION["formulario"]["datos"]["dircom"])) < 8) {
            $mysqli->close();
            $_SESSION ["jsonsalida"] ["codigoerror"] = '0001';
            $_SESSION ["jsonsalida"] ["mensajeerror"] = 'Dirección comercial debe tener mínimo 8 caracteres';
            $_SESSION ["jsonsalida"] ["focus"] = 'dircom';
            $json = $api->json($_SESSION ["jsonsalida"]);
            $api->response(str_replace("\\/", "/", $json), 200);
        }

        if (trim($_SESSION["formulario"]["datos"]["muncom"]) == '') {
            $mysqli->close();
            $_SESSION ["jsonsalida"] ["codigoerror"] = '0001';
            $_SESSION ["jsonsalida"] ["mensajeerror"] = 'Código del domicilio comercial no debe estar en blancos';
            $_SESSION ["jsonsalida"] ["focus"] = 'dircom';
            $json = $api->json($_SESSION ["jsonsalida"]);
            $api->response(str_replace("\\/", "/", $json), 200);
        }

        //
        if (!defined('RENOVACION_OBLIGATORIO_BARRIOCOM')) {
            define('RENOVACION_OBLIGATORIO_BARRIOCOM', 'S');
        }
        if (!defined('RENOVACION_OBLIGATORIO_NUMEROPREDIAL')) {
            define('RENOVACION_OBLIGATORIO_NUMEROPREDIAL', 'S');
        }
        if (!defined('RENOVACION_OBLIGATORIO_EMAILCOM')) {
            define('RENOVACION_OBLIGATORIO_EMAILCOM', 'S');
        }
        if (!defined('RENOVACION_OBLIGATORIO_EMAILNOT')) {
            define('RENOVACION_OBLIGATORIO_EMAILNOT', 'S');
        }

        //
        if ($_SESSION["formulario"]["datos"]["cc"] == CODIGO_EMPRESA) {
            if (RENOVACION_OBLIGATORIO_BARRIOCOM == 'S') {
                if (trim($_SESSION["formulario"]["datos"]["barriocom"]) == '') {
                    if ($_SESSION["formulario"]["datos"]["ultanoren"] >= '2015') {
                        $mysqli->close();
                        $_SESSION ["jsonsalida"] ["codigoerror"] = '0001';
                        $_SESSION ["jsonsalida"] ["mensajeerror"] = 'El campo barrio comercial es obligatorio';
                        $_SESSION ["jsonsalida"] ["focus"] = 'barriocom';
                        $json = $api->json($_SESSION ["jsonsalida"]);
                        $api->response(str_replace("\\/", "/", $json), 200);
                    }
                }
            }

            if (RENOVACION_OBLIGATORIO_NUMEROPREDIAL == 'S') {
                if (trim($_SESSION["formulario"]["datos"]["numpredial"]) == '') {
                    if ($_SESSION["formulario"]["datos"]["ultanoren"] >= '2015') {
                        $mysqli->close();
                        $_SESSION ["jsonsalida"] ["codigoerror"] = '0001';
                        $_SESSION ["jsonsalida"] ["mensajeerror"] = 'El campo número del predial es obligatorio';
                        $_SESSION ["jsonsalida"] ["focus"] = 'numpredial';
                        $json = $api->json($_SESSION ["jsonsalida"]);
                        $api->response(str_replace("\\/", "/", $json), 200);
                    }
                }
            }
        }

        if (RENOVACION_OBLIGATORIO_EMAILCOM == 'S') {
            if (trim($_SESSION["formulario"]["datos"]["emailcom"]) == '') {
                if ($_SESSION["formulario"]["datos"]["ultanoren"] >= '2015') {
                    $mysqli->close();
                    $_SESSION ["jsonsalida"] ["codigoerror"] = '0001';
                    $_SESSION ["jsonsalida"] ["mensajeerror"] = 'El campo correo electrónico comercial es obligatorio';
                    $_SESSION ["jsonsalida"] ["focus"] = 'emailcom';
                    $json = $api->json($_SESSION ["jsonsalida"]);
                    $api->response(str_replace("\\/", "/", $json), 200);
                }
            }
        }

        if (trim($_SESSION["formulario"]["datos"]["emailcom"]) != '') {
            if (\funcionesGenerales::validarEmail($_SESSION["formulario"]["datos"]["emailcom"]) === false) {
                if ($_SESSION["formulario"]["datos"]["ultanoren"] >= '2015') {
                    $mysqli->close();
                    $_SESSION ["jsonsalida"] ["codigoerror"] = '0001';
                    $_SESSION ["jsonsalida"] ["mensajeerror"] = 'El campo correo electrónico comercial parece estar incorrectamente diligenciado';
                    $_SESSION ["jsonsalida"] ["focus"] = 'emailcom';
                    $json = $api->json($_SESSION ["jsonsalida"]);
                    $api->response(str_replace("\\/", "/", $json), 200);
                }
            }
        }

        if ($_SESSION["formulario"]["datos"]["organizacion"] == '02') {
            if ($_SESSION["formulario"]["datos"]["ultanoren"] >= '2015') {
                if ($_SESSION["formulario"]["datos"]["ctrubi"] == '') {
                    $mysqli->close();
                    $_SESSION ["jsonsalida"] ["codigoerror"] = '0001';
                    $_SESSION ["jsonsalida"] ["mensajeerror"] = 'Debe seleccionar una opción para la ubicación del establecimiento de comercio.';
                    $_SESSION ["jsonsalida"] ["focus"] = 'ctrubi';
                    $json = $api->json($_SESSION ["jsonsalida"]);
                    $api->response(str_replace("\\/", "/", $json), 200);
                }
            }
        }

        if ($_SESSION["formulario"]["datos"]["organizacion"] != '02' && $_SESSION["formulario"]["datos"]["categoria"] != '3') {
            if (RENOVACION_OBLIGATORIO_EMAILNOT == 'S') {
                if (trim($_SESSION["formulario"]["datos"]["emailnot"]) == '') {
                    if ($_SESSION["formulario"]["datos"]["ultanoren"] >= '2015') {
                        $mysqli->close();
                        $_SESSION ["jsonsalida"] ["codigoerror"] = '0001';
                        $_SESSION ["jsonsalida"] ["mensajeerror"] = 'El campo correo electrónico de notificaci&oacute;n es obligatorio';
                        $_SESSION ["jsonsalida"] ["focus"] = 'emailnot';
                        $json = $api->json($_SESSION ["jsonsalida"]);
                        $api->response(str_replace("\\/", "/", $json), 200);
                    }
                }
            }

            if (trim($_SESSION["formulario"]["datos"]["emailnot"]) != '') {
                if (\funcionesGenerales::validarEmail($_SESSION["formulario"]["datos"]["emailnot"]) === false) {
                    $mysqli->close();
                    $_SESSION ["jsonsalida"] ["codigoerror"] = '0001';
                    $_SESSION ["jsonsalida"] ["mensajeerror"] = 'El campo correo electrónico de notificación parece estar incorrectamente diligenciado';
                    $_SESSION ["jsonsalida"] ["focus"] = 'emailnot';
                    $json = $api->json($_SESSION ["jsonsalida"]);
                    $api->response(str_replace("\\/", "/", $json), 200);
                }
            }

            if (trim($_SESSION["formulario"]["datos"]["dirnot"]) == '') {
                if ($_SESSION["formulario"]["datos"]["ultanoren"] >= '2015') {
                    $mysqli->close();
                    $_SESSION ["jsonsalida"] ["codigoerror"] = '0001';
                    $_SESSION ["jsonsalida"] ["mensajeerror"] = 'Dirección de notificación no debe estar en blancos';
                    $_SESSION ["jsonsalida"] ["focus"] = 'dirnot';
                    $json = $api->json($_SESSION ["jsonsalida"]);
                    $api->response(str_replace("\\/", "/", $json), 200);
                }
            }

            if (strlen(trim($_SESSION["formulario"]["datos"]["dirnot"])) < 8) {
                $mysqli->close();
                $_SESSION ["jsonsalida"] ["codigoerror"] = '0001';
                $_SESSION ["jsonsalida"] ["mensajeerror"] = 'Dirección de notificación  debe tener mínimo 8 caracteres';
                $_SESSION ["jsonsalida"] ["focus"] = 'dirnot';
                $json = $api->json($_SESSION ["jsonsalida"]);
                $api->response(str_replace("\\/", "/", $json), 200);
            }

            if (trim($_SESSION["formulario"]["datos"]["munnot"]) == '') {
                if ($_SESSION["formulario"]["datos"]["ultanoren"] >= '2015') {
                    $mysqli->close();
                    $_SESSION ["jsonsalida"] ["codigoerror"] = '0001';
                    $_SESSION ["jsonsalida"] ["mensajeerror"] = 'Código del domicilio de notificación no debe estar en blancos';
                    $_SESSION ["jsonsalida"] ["focus"] = 'munnot';
                    $json = $api->json($_SESSION ["jsonsalida"]);
                    $api->response(str_replace("\\/", "/", $json), 200);
                }
            }

            if (trim($_SESSION["formulario"]["datos"]["munnot"]) == '') {
                if ($_SESSION["formulario"]["datos"]["ultanoren"] >= '2015') {
                    $mysqli->close();
                    $_SESSION ["jsonsalida"] ["codigoerror"] = '0001';
                    $_SESSION ["jsonsalida"] ["mensajeerror"] = 'Código del domicilio de notificación no debe estar en blancos';
                    $_SESSION ["jsonsalida"] ["focus"] = 'munnot';
                    $json = $api->json($_SESSION ["jsonsalida"]);
                    $api->response(str_replace("\\/", "/", $json), 200);
                }
            }
        }

        if (empty($_SESSION["formulario"]["datos"]["ciius"])) {
            if ($_SESSION["formulario"]["datos"]["ultanoren"] >= '2015') {
                $mysqli->close();
                $_SESSION ["jsonsalida"] ["codigoerror"] = '0001';
                $_SESSION ["jsonsalida"] ["mensajeerror"] = 'Debe indicar al menos un código ciiu';
                $_SESSION ["jsonsalida"] ["focus"] = 'ciiu1';
                $json = $api->json($_SESSION ["jsonsalida"]);
                $api->response(str_replace("\\/", "/", $json), 200);
            }
        }

        $errorCiius = 'si';
        foreach ($_SESSION["formulario"]["datos"]["ciius"] as $cx) {
            if (ltrim(trim($cx), "0") != '') {
                $errorCiius = 'no';
            }
        }
        if ($errorCiius == 'si') {
            if ($_SESSION["formulario"]["datos"]["ultanoren"] >= '2015') {
                $mysqli->close();
                $_SESSION ["jsonsalida"] ["codigoerror"] = '0001';
                $_SESSION ["jsonsalida"] ["mensajeerror"] = 'Debe indicar al menos un código ciiu';
                $_SESSION ["jsonsalida"] ["focus"] = 'ciiu1';
                $json = $api->json($_SESSION ["jsonsalida"]);
                $api->response(str_replace("\\/", "/", $json), 200);
            }
        }

        //
        if (count($_SESSION["formulario"]["datos"]["ciius"]) > 0) {
            $iCiiu = 0;
            foreach ($_SESSION["formulario"]["datos"]["ciius"] as $ciiu) {
                $iCiiu++;
                if (trim($ciiu) != '') {
                    $arrCiiu = retornarRegistroMysqliApi($mysqli, 'bas_ciius', "idciiu='" . $ciiu . "'");
                    if (($arrCiiu === false) || ($arrCiiu == 0) || (count($arrCiiu) == 0)) {
                        $mysqli->close();
                        $_SESSION ["jsonsalida"] ["codigoerror"] = '0001';
                        $_SESSION ["jsonsalida"] ["mensajeerror"] = 'El código CIIU ' . $ciiu . ' no fue encontrado en la BD, no utilizable';
                        $_SESSION ["jsonsalida"] ["focus"] = 'ciiu' . $iCiiu;
                        $json = $api->json($_SESSION ["jsonsalida"]);
                        $api->response(str_replace("\\/", "/", $json), 200);
                    } else {
                        if (isset($arrCiiu["movimientorenovacion"])) {
                            if ($arrCiiu["movimientomatricula"] == 'N' || $arrCiiu["movimientorenovacion"] == 'N') {
                                $arrAnt = retornarRegistroMysqliApi($mysqli, 'mreg_liquidaciondatos_anteriores', "idliquidacion=" . $_SESSION["formulario"]["liquidacion"] . " and expediente='" . $_SESSION["formulario"]["matricula"] . "'");
                                if (empty($arrAnt)) {
                                    $mysqli->close();
                                    $_SESSION ["jsonsalida"] ["codigoerror"] = '0001';
                                    $_SESSION ["jsonsalida"] ["mensajeerror"] = 'El código CIIU ' . $ciiu . ' no es un código CIIU utilizable en el proceso de matrícula o renovación. Le sugerimos que lo modifique.';
                                    $_SESSION ["jsonsalida"] ["focus"] = 'ciiu' . $iCiiu;
                                    $json = $api->json($_SESSION ["jsonsalida"]);
                                    $api->response(str_replace("\\/", "/", $json), 200);
                                } else {
                                    $datAnt = \funcionesGenerales::desserializarExpedienteMatricula($mysqli, $arrAnt["xml"]);
                                    $ciiusiguales = 0;
                                    foreach ($datAnt["ciius"] as $ciiuant) {
                                        if ($ciiu == $ciiuant) {
                                            $ciiusiguales++;
                                        }
                                    }
                                    if ($ciiusiguales == 0) {
                                        $mysqli->close();
                                        $_SESSION ["jsonsalida"] ["codigoerror"] = '0001';
                                        $_SESSION ["jsonsalida"] ["mensajeerror"] = 'El código CIIU ' . $ciiu . ' ha sido adicionado pero este tiene restricción para ser incluido en el proceso de matrícula o renovación. Le sugerimos que lo modifique.';
                                        $_SESSION ["jsonsalida"] ["focus"] = 'ciiu' . $iCiiu;
                                        $json = $api->json($_SESSION ["jsonsalida"]);
                                        $api->response(str_replace("\\/", "/", $json), 200);
                                    }
                                }
                                unset($arrAnt);
                                unset($datAnt);
                            }
                        }
                        if ($arrCiiu["actividadcomercial"] == 'NO') {
                            if ($_SESSION["formulario"]["datos"]["organizacion"] == '01' || $_SESSION["formulario"]["datos"]["organizacion"] == '02') {
                                $ciiusnocom++;
                            }
                        }
                    }
                }
            }
        }

        //
        if ($_SESSION["formulario"]["datos"]["organizacion"] == '01' || ($_SESSION["formulario"]["datos"]["organizacion"] > '02' && $_SESSION["formulario"]["datos"]["categoria"] == '1')) {
            if (count($_SESSION["formulario"]["datos"]["ciius"]) > 0) {
                if ($_SESSION["formulario"]["datos"]["ciius"][1] != '') {
                    if (ltrim(trim($_SESSION["formulario"]["datos"]["feciniact1"]), "0") == '' || !\funcionesGenerales::validarFecha($_SESSION["formulario"]["datos"]["feciniact1"])) {
                        $mysqli->close();
                        $_SESSION ["jsonsalida"] ["codigoerror"] = '0001';
                        $_SESSION ["jsonsalida"] ["mensajeerror"] = 'Fecha de inicio de la actividad principal errónea o sin digitar.';
                        $_SESSION ["jsonsalida"] ["focus"] = 'feciniact1';
                        $json = $api->json($_SESSION ["jsonsalida"]);
                        $api->response(str_replace("\\/", "/", $json), 200);
                    } else {
                        if ($_SESSION["formulario"]["datos"]["feciniact1"] < '19000101' || $_SESSION["formulario"]["datos"]["feciniact1"] > date("Ymd")) {
                            $mysqli->close();
                            $_SESSION ["jsonsalida"] ["codigoerror"] = '0001';
                            $_SESSION ["jsonsalida"] ["mensajeerror"] = 'Fecha de inicio de la actividad principal parece ser incorrecta. Debe ser superior al 1900-01-01 e inferior o igual a la fecha actual.';
                            $_SESSION ["jsonsalida"] ["focus"] = 'feciniact1';
                            $json = $api->json($_SESSION ["jsonsalida"]);
                            $api->response(str_replace("\\/", "/", $json), 200);
                        }
                    }
                }
                if ($_SESSION["formulario"]["datos"]["ciius"][2] != '') {
                    if (ltrim(trim($_SESSION["formulario"]["datos"]["feciniact2"]), "0") == '' || !\funcionesGenerales::validarFecha($_SESSION["formulario"]["datos"]["feciniact2"])) {
                        $mysqli->close();
                        $_SESSION ["jsonsalida"] ["codigoerror"] = '0001';
                        $_SESSION ["jsonsalida"] ["mensajeerror"] = 'Fecha de inicio de la actividad secundaria errónea o sin digitar';
                        $_SESSION ["jsonsalida"] ["focus"] = 'feciniact2';
                        $json = $api->json($_SESSION ["jsonsalida"]);
                        $api->response(str_replace("\\/", "/", $json), 200);
                    } else {
                        if ($_SESSION["formulario"]["datos"]["feciniact2"] < '19000101' || $_SESSION["formulario"]["datos"]["feciniact2"] > date("Ymd")) {
                            $mysqli->close();
                            $_SESSION ["jsonsalida"] ["codigoerror"] = '0001';
                            $_SESSION ["jsonsalida"] ["mensajeerror"] = 'Fecha de inicio de la actividad secundaria parece ser incorrecta. Debe ser superior al 1900-01-01 e inferior o igual a la fecha actual.';
                            $_SESSION ["jsonsalida"] ["focus"] = 'feciniact2';
                            $json = $api->json($_SESSION ["jsonsalida"]);
                            $api->response(str_replace("\\/", "/", $json), 200);
                        }
                    }
                }
            }
        }

        //
        if (($_SESSION["formulario"]["datos"]["versionciiu"] == '0') || ($_SESSION["formulario"]["datos"]["versionciiu"] == '')) {
            $_SESSION["formulario"]["datos"]["versionciiu"] = '1';
        }

        if (trim($_SESSION["formulario"]["datos"]["telcom1"]) == '') {
            $mysqli->close();
            $_SESSION ["jsonsalida"] ["codigoerror"] = '0001';
            $_SESSION ["jsonsalida"] ["mensajeerror"] = 'Debe indicar al menos un número telefónico de ubicación comercial';
            $_SESSION ["jsonsalida"] ["focus"] = 'telcom1';
            $json = $api->json($_SESSION ["jsonsalida"]);
            $api->response(str_replace("\\/", "/", $json), 200);
        }

        if (trim($_SESSION["formulario"]["datos"]["telcom1"]) != '') {
            if (strlen($_SESSION["formulario"]["datos"]["telcom1"]) != 7 && strlen($_SESSION["formulario"]["datos"]["telcom1"]) != 10) {
                $mysqli->close();
                $_SESSION ["jsonsalida"] ["codigoerror"] = '0001';
                $_SESSION ["jsonsalida"] ["mensajeerror"] = 'El número telef&oacute;nico comercial (1) debe ser de 7 o 10 dígitos.';
                $_SESSION ["jsonsalida"] ["focus"] = 'telcom1';
                $json = $api->json($_SESSION ["jsonsalida"]);
                $api->response(str_replace("\\/", "/", $json), 200);
            }
        }

        if (trim($_SESSION["formulario"]["datos"]["telcom2"]) != '') {
            if (strlen($_SESSION["formulario"]["datos"]["telcom2"]) != 7 && strlen($_SESSION["formulario"]["datos"]["telcom2"]) != 10) {
                $mysqli->close();
                $_SESSION ["jsonsalida"] ["codigoerror"] = '0001';
                $_SESSION ["jsonsalida"] ["mensajeerror"] = 'El número telefónico comercial (2) debe ser de 7 o 10 dígitos.';
                $_SESSION ["jsonsalida"] ["focus"] = 'telcom2';
                $json = $api->json($_SESSION ["jsonsalida"]);
                $api->response(str_replace("\\/", "/", $json), 200);
            }
        }

        if (trim($_SESSION["formulario"]["datos"]["celcom"]) != '') {
            if (strlen($_SESSION["formulario"]["datos"]["celcom"]) != 7 && strlen($_SESSION["formulario"]["datos"]["celcom"]) != 10) {
                $mysqli->close();
                $_SESSION ["jsonsalida"] ["codigoerror"] = '0001';
                $_SESSION ["jsonsalida"] ["mensajeerror"] = 'El número telefónico comercial (3) debe ser de 7 o 10 dígitos.';
                $_SESSION ["jsonsalida"] ["focus"] = 'celcom';
                $json = $api->json($_SESSION ["jsonsalida"]);
                $api->response(str_replace("\\/", "/", $json), 200);
            }
        }

        /* JINT : 2024-11-14: Se elimina el control
        if ($_SESSION["formulario"]["datos"]["organizacion"] != '02' &&
                $_SESSION["formulario"]["datos"]["categoria"] != '2' &&
                $_SESSION["formulario"]["datos"]["categoria"] != '3') {
            if (trim($_SESSION["formulario"]["datos"]["codigozonacom"]) == '' || $_SESSION["formulario"]["datos"]["codigozonacom"] == 'N') {
                if ($_SESSION["formulario"]["datos"]["ultanoren"] >= '2015') {
                    $mysqli->close();
                    $_SESSION ["jsonsalida"] ["codigoerror"] = '0001';
                    $_SESSION ["jsonsalida"] ["mensajeerror"] = 'Zona de ubicación comercial incorrectamente seleccionada';
                    $_SESSION ["jsonsalida"] ["focus"] = 'codigozonacom';
                    $json = $api->json($_SESSION ["jsonsalida"]);
                    $api->response(str_replace("\\/", "/", $json), 200);
                }
            }
            if (trim($_SESSION["formulario"]["datos"]["codigozonanot"]) == '' || $_SESSION["formulario"]["datos"]["codigozonanot"] == 'N') {
                if ($_SESSION["formulario"]["datos"]["ultanoren"] >= '2015') {
                    $mysqli->close();
                    $_SESSION ["jsonsalida"] ["codigoerror"] = '0001';
                    $_SESSION ["jsonsalida"] ["mensajeerror"] = 'Zona de ubicación de notificación incorrectamente seleccionada';
                    $_SESSION ["jsonsalida"] ["focus"] = 'codigozonanot';
                    $json = $api->json($_SESSION ["jsonsalida"]);
                    $api->response(str_replace("\\/", "/", $json), 200);
                }
            }
        }
        */
        
        /* JINT : 2024-11-14: Se elimina el control
        if ($_SESSION["formulario"]["datos"]["organizacion"] != '02' && $_SESSION["formulario"]["datos"]["categoria"] != '3') {
            if (trim($_SESSION["formulario"]["datos"]["telnot"]) != '') {
                if (strlen($_SESSION["formulario"]["datos"]["telnot"]) != 7 && strlen($_SESSION["formulario"]["datos"]["telnot"]) != 10) {
                    if ($_SESSION["formulario"]["datos"]["ultanoren"] >= '2015') {
                        $mysqli->close();
                        $_SESSION ["jsonsalida"] ["codigoerror"] = '0001';
                        $_SESSION ["jsonsalida"] ["mensajeerror"] = 'El número telefónico de notificación (1) debe ser de 7 o 10 dígitos';
                        $_SESSION ["jsonsalida"] ["focus"] = 'telnot';
                        $json = $api->json($_SESSION ["jsonsalida"]);
                        $api->response(str_replace("\\/", "/", $json), 200);
                    }
                }
            }

            if (trim($_SESSION["formulario"]["datos"]["telnot2"]) != '') {
                if (strlen($_SESSION["formulario"]["datos"]["telnot2"]) != 7 && strlen($_SESSION["formulario"]["datos"]["telnot2"]) != 10) {
                    if ($_SESSION["formulario"]["datos"]["ultanoren"] >= '2015') {
                        $mysqli->close();
                        $_SESSION ["jsonsalida"] ["codigoerror"] = '0001';
                        $_SESSION ["jsonsalida"] ["mensajeerror"] = 'El número telefónico de notificación (2) debe ser de 7 o 10 dígitos';
                        $_SESSION ["jsonsalida"] ["focus"] = 'telnot2';
                        $json = $api->json($_SESSION ["jsonsalida"]);
                        $api->response(str_replace("\\/", "/", $json), 200);
                    }
                }
            }

            if (trim($_SESSION["formulario"]["datos"]["celnot"]) != '') {
                if (strlen($_SESSION["formulario"]["datos"]["celnot"]) != 7 && strlen($_SESSION["formulario"]["datos"]["celnot"]) != 10) {
                    if ($_SESSION["formulario"]["datos"]["ultanoren"] >= '2015') {
                        $mysqli->close();
                        $_SESSION ["jsonsalida"] ["codigoerror"] = '0001';
                        $_SESSION ["jsonsalida"] ["mensajeerror"] = 'El número telefónico de notificación (3) debe ser de 7 o 10 dígitos';
                        $_SESSION ["jsonsalida"] ["focus"] = 'celnot';
                        $json = $api->json($_SESSION ["jsonsalida"]);
                        $api->response(str_replace("\\/", "/", $json), 200);
                    }
                }
            }
        }
        */
        
        if ($xTipoTramite == 'renovacionmatricula' || $xTipoTramite == 'renovacionesadl') {
            if (!is_numeric($_SESSION["formulario"]["datos"]["personal"]) || intval($_SESSION["formulario"]["datos"]["personal"]) == '0') {
                if (!defined('RENOVACION_OBLIGATORIO_PERSONAL') || RENOVACION_OBLIGATORIO_PERSONAL == 'S') {
                    if ($_SESSION["formulario"]["datos"]["ultanoren"] >= '2015') {
                        $mysqli->close();
                        $_SESSION ["jsonsalida"] ["codigoerror"] = '0001';
                        $_SESSION ["jsonsalida"] ["mensajeerror"] = 'Personal ocupado digitado erroneamente, no debe ser 0 ni vacio';
                        $_SESSION ["jsonsalida"] ["focus"] = 'personal';
                        $json = $api->json($_SESSION ["jsonsalida"]);
                        $api->response(str_replace("\\/", "/", $json), 200);
                    }
                }
            }
        }

        if (
                $xTipoTramite == 'matriculapnat' ||
                $xTipoTramite == 'matriculaest' ||
                $xTipoTramite == 'matriculapjur' ||
                $xTipoTramite == 'matriculaesadl' ||
                $xTipoTramite == 'inscripciondocumentos' ||
                $xTipoTramite == 'inscripcionesregmer' ||
                $xTipoTramite == 'constitucionpjur' ||
                $xTipoTramite == 'constitucionesadl'
        ) {
            if (!is_numeric($_SESSION["formulario"]["datos"]["personal"]) || intval($_SESSION["formulario"]["datos"]["personal"]) == '0') {
                if (!defined('MATRICULAS_OBLIGATORIO_PERSONAL') || MATRICULAS_OBLIGATORIO_PERSONAL == 'S') {
                    if ($_SESSION["formulario"]["datos"]["ultanoren"] >= '2015') {
                        $mysqli->close();
                        $_SESSION ["jsonsalida"] ["codigoerror"] = '0001';
                        $_SESSION ["jsonsalida"] ["mensajeerror"] = 'Personal ocupado digitado erroneamente, no debe ser 0 ni vacio';
                        $_SESSION ["jsonsalida"] ["focus"] = 'personal';
                        $json = $api->json($_SESSION ["jsonsalida"]);
                        $api->response(str_replace("\\/", "/", $json), 200);
                    }
                }
            }
        }

        //
        if ($_SESSION["formulario"]["datos"]["organizacion"] == '01' || ($_SESSION["formulario"]["datos"]["organizacion"] > '02') && $_SESSION["formulario"]["datos"]["categoria"] == '1') {
            if (!is_numeric($_SESSION["formulario"]["datos"]["cantidadmujeres"])) {
                if ($_SESSION["formulario"]["datos"]["ultanoren"] >= '2015') {
                    $mysqli->close();
                    $_SESSION ["jsonsalida"] ["codigoerror"] = '0001';
                    $_SESSION ["jsonsalida"] ["mensajeerror"] = 'Cantidad de mujeres vinculadas laboralmente debe ser cero o mayor a cero';
                    $_SESSION ["jsonsalida"] ["focus"] = 'cantidadmujeres';
                    $json = $api->json($_SESSION ["jsonsalida"]);
                    $api->response(str_replace("\\/", "/", $json), 200);
                }
            } else {
                if (intval($_SESSION["formulario"]["datos"]["cantidadmujeres"]) > intval($_SESSION["formulario"]["datos"]["personal"])) {
                    if ($_SESSION["formulario"]["datos"]["ultanoren"] >= '2015') {
                        $mysqli->close();
                        $_SESSION ["jsonsalida"] ["codigoerror"] = '0001';
                        $_SESSION ["jsonsalida"] ["mensajeerror"] = 'Cantidad de mujeres vinculadas laboralmente no debe ser mayor al personal reportado';
                        $_SESSION ["jsonsalida"] ["focus"] = 'cantidadmujeres';
                        $json = $api->json($_SESSION ["jsonsalida"]);
                        $api->response(str_replace("\\/", "/", $json), 200);
                    }
                }
            }
        }

        //
        if ($_SESSION["formulario"]["datos"]["organizacion"] == '01' || ($_SESSION["formulario"]["datos"]["organizacion"] > '02') && $_SESSION["formulario"]["datos"]["categoria"] == '1') {
            if (!is_numeric($_SESSION["formulario"]["datos"]["participacionmujeres"])) {
                if ($_SESSION["formulario"]["datos"]["ultanoren"] >= '2015') {
                    $mysqli->close();
                    $_SESSION ["jsonsalida"] ["codigoerror"] = '0001';
                    $_SESSION ["jsonsalida"] ["mensajeerror"] = 'Porcentaje de participación de mujeres en el capital debe ser cero o mayor a cero';
                    $_SESSION ["jsonsalida"] ["focus"] = 'participacionmujeres';
                    $json = $api->json($_SESSION ["jsonsalida"]);
                    $api->response(str_replace("\\/", "/", $json), 200);
                }
            } else {
                if (doubleval($_SESSION["formulario"]["datos"]["participacionmujeres"]) > 100) {
                    $mysqli->close();
                    $_SESSION ["jsonsalida"] ["codigoerror"] = '0001';
                    $_SESSION ["jsonsalida"] ["mensajeerror"] = 'Porcentaje de participación de mujeres eno debe ser superior al 100%';
                    $_SESSION ["jsonsalida"] ["focus"] = 'participacionmujeres';
                    $json = $api->json($_SESSION ["jsonsalida"]);
                    $api->response(str_replace("\\/", "/", $json), 200);
                }
            }
        }

        /* JINT : 2024-11-14: Se elimina el control
        if (($_SESSION["formulario"]["datos"]["organizacion"] != '02') && ($_SESSION["formulario"]["datos"]["categoria"] != '2') && ($_SESSION["formulario"]["datos"]["categoria"] != '3')) {
            if (ltrim(trim($_SESSION["formulario"]["datos"]["tiposedeadm"]), "0") == '') {
                if ($_SESSION["formulario"]["datos"]["ultanoren"] >= '2015') {
                    $mysqli->close();
                    $_SESSION ["jsonsalida"] ["codigoerror"] = '0001';
                    $_SESSION ["jsonsalida"] ["mensajeerror"] = 'Sede administrativa erroneamente seleccionada. Debe seleccionar una opción válida.';
                    $_SESSION ["jsonsalida"] ["focus"] = 'tiposedeadm';
                    $json = $api->json($_SESSION ["jsonsalida"]);
                    $api->response(str_replace("\\/", "/", $json), 200);
                }
            }
        }
        */
        
        //
        if ($_SESSION["formulario"]["datos"]["organizacion"] == '01' || $_SESSION["formulario"]["datos"]["organizacion"] == '02') {
            if (trim($_SESSION["formulario"]["datos"]["desactiv"]) == '') {
                if ($_SESSION["formulario"]["datos"]["ultanoren"] >= '2015') {
                    $mysqli->close();
                    $_SESSION ["jsonsalida"] ["codigoerror"] = '0001';
                    $_SESSION ["jsonsalida"] ["mensajeerror"] = 'Debe indicar la descripción resumida de la actividad económica.';
                    $_SESSION ["jsonsalida"] ["focus"] = 'desactiv';
                    $json = $api->json($_SESSION ["jsonsalida"]);
                    $api->response(str_replace("\\/", "/", $json), 200);
                }
            } else {
                if ($_SESSION["formulario"]["datos"]["organizacion"] == '01') {
                    if (strlen(trim($_SESSION["formulario"]["datos"]["desactiv"])) > 1000) {
                        $mysqli->close();
                        $_SESSION ["jsonsalida"] ["codigoerror"] = '0001';
                        $_SESSION ["jsonsalida"] ["mensajeerror"] = 'La descripción de la actividad económica es demasiado extensa, por favor resuma (máximo 1000).';
                        $_SESSION ["jsonsalida"] ["focus"] = 'desactiv';
                        $json = $api->json($_SESSION ["jsonsalida"]);
                        $api->response(str_replace("\\/", "/", $json), 200);
                    }
                } else {
                    if (strlen(trim($_SESSION["formulario"]["datos"]["desactiv"])) > 500) {
                        $mysqli->close();
                        $_SESSION ["jsonsalida"] ["codigoerror"] = '0001';
                        $_SESSION ["jsonsalida"] ["mensajeerror"] = 'La descripción de la actividad econ&oacute;mica es demasiado extensa, por favor resuma. (máximo 500)';
                        $_SESSION ["jsonsalida"] ["focus"] = 'desactiv';
                        $json = $api->json($_SESSION ["jsonsalida"]);
                        $api->response(str_replace("\\/", "/", $json), 200);
                    }
                }
            }
        }

        //
        if (!defined('ACTIVADO_CAE')) {
            define('ACTIVADO_CAE', 'N');
        }

        //
        if (!defined('MATRICULAS_SOLICITAR_INGESPERADOS')) {
            define('MATRICULAS_SOLICITAR_INGESPERADOS', 'N');
        }

        //
        if (defined('ACTIVADO_CAE') && ACTIVADO_CAE == 'S') {
            if (
                    $xTipoTramite == 'matriculapnat' ||
                    $xTipoTramite == 'matriculaest' ||
                    $xTipoTramite == 'matriculapjur' ||
                    $xTipoTramite == 'matriculaesadl' ||
                    $xTipoTramite == 'inscripciondocumentos' ||
                    $xTipoTramite == 'inscripcionesregmer' ||
                    $xTipoTramite == 'constitucionpjur' ||
                    $xTipoTramite == 'constitucionesadl'
            ) {
                if (MATRICULAS_SOLICITAR_INGESPERADOS == 'SI-OBLIGATORIO') {
                    if (doubleval($_SESSION["formulario"]["datos"]["ingesperados"]) == 0) {
                        if ($_SESSION["formulario"]["datos"]["ultanoren"] >= '2015') {
                            $mysqli->close();
                            $_SESSION ["jsonsalida"] ["codigoerror"] = '0001';
                            $_SESSION ["jsonsalida"] ["mensajeerror"] = 'Los ingresos esperados para el año en que se matricula deben ser mayores a cero.';
                            $_SESSION ["jsonsalida"] ["focus"] = 'ingesperados';
                            $json = $api->json($_SESSION ["jsonsalida"]);
                            $api->response(str_replace("\\/", "/", $json), 200);
                        }
                    }
                }
            }
        }


        //
        if (($_SESSION["formulario"]["datos"]["organizacion"] != '02') && ($_SESSION["formulario"]["datos"]["categoria"] != '2') && ($_SESSION["formulario"]["datos"]["categoria"] != '3')) {
            $_SESSION["formulario"]["datos"]["actvin"] = 0;
            foreach ($_SESSION["formulario"]["datos"]["f"] as $anox => $fin) {
                $finAno = $fin["anodatos"];
                $txtemergente2 = '';
                if (isset($fin["actcte"])) {
                    if (!is_numeric($fin["actcte"])) {
                        $mysqli->close();
                        $_SESSION ["jsonsalida"] ["codigoerror"] = '0001';
                        $_SESSION ["jsonsalida"] ["mensajeerror"] = 'Activo corriente para el año ' . $finAno . ' digitado erroneamente';
                        $_SESSION ["jsonsalida"] ["focus"] = 'actcte_' . $finAno;
                        $json = $api->json($_SESSION ["jsonsalida"]);
                        $api->response(str_replace("\\/", "/", $json), 200);
                    }
                }
                if (isset($fin["actnocte"])) {
                    if (!is_numeric($fin["actnocte"])) {
                        $mysqli->close();
                        $_SESSION ["jsonsalida"] ["codigoerror"] = '0001';
                        $_SESSION ["jsonsalida"] ["mensajeerror"] = 'Activo no corriente para el año ' . $finAno . ' digitado erroneamente';
                        $_SESSION ["jsonsalida"] ["focus"] = 'actnocte_' . $finAno;
                        $json = $api->json($_SESSION ["jsonsalida"]);
                        $api->response(str_replace("\\/", "/", $json), 200);
                    }
                }

                if (isset($fin["acttot"])) {
                    if (!is_numeric($fin["acttot"])) {
                        $mysqli->close();
                        $_SESSION ["jsonsalida"] ["codigoerror"] = '0001';
                        $_SESSION ["jsonsalida"] ["mensajeerror"] = 'Activo total para el año ' . $finAno . ' digitado erroneamente';
                        $_SESSION ["jsonsalida"] ["focus"] = 'acttot_' . $finAno;
                        $json = $api->json($_SESSION ["jsonsalida"]);
                        $api->response(str_replace("\\/", "/", $json), 200);
                    }
                }

                if (isset($fin["pascte"])) {
                    if (!is_numeric($fin["pascte"])) {
                        $mysqli->close();
                        $_SESSION ["jsonsalida"] ["codigoerror"] = '0001';
                        $_SESSION ["jsonsalida"] ["mensajeerror"] = 'Pasivo corriente para el año ' . $finAno . ' digitado erroneamente';
                        $_SESSION ["jsonsalida"] ["focus"] = 'pascte_' . $finAno;
                        $json = $api->json($_SESSION ["jsonsalida"]);
                        $api->response(str_replace("\\/", "/", $json), 200);
                    }
                }

                if (isset($fin["paslar"])) {
                    if (!is_numeric($fin["paslar"])) {
                        $mysqli->close();
                        $_SESSION ["jsonsalida"] ["codigoerror"] = '0001';
                        $_SESSION ["jsonsalida"] ["mensajeerror"] = 'Pasivo a largo plazo para el año ' . $finAno . ' digitado erroneamente';
                        $_SESSION ["jsonsalida"] ["focus"] = 'paslar_' . $finAno;
                        $json = $api->json($_SESSION ["jsonsalida"]);
                        $api->response(str_replace("\\/", "/", $json), 200);
                    }
                }

                if (isset($fin["pastot"])) {
                    if (!is_numeric($fin["pastot"])) {
                        $mysqli->close();
                        $_SESSION ["jsonsalida"] ["codigoerror"] = '0001';
                        $_SESSION ["jsonsalida"] ["mensajeerror"] = 'Pasivo total para el año ' . $finAno . ' digitado erroneamente';
                        $_SESSION ["jsonsalida"] ["focus"] = 'pastot_' . $finAno;
                        $json = $api->json($_SESSION ["jsonsalida"]);
                        $api->response(str_replace("\\/", "/", $json), 200);
                    }
                }

                if (isset($fin["pattot"])) {
                    if (!is_numeric($fin["pattot"])) {
                        $mysqli->close();
                        $_SESSION ["jsonsalida"] ["codigoerror"] = '0001';
                        $_SESSION ["jsonsalida"] ["mensajeerror"] = 'Patrimonio para el año ' . $finAno . ' digitado erroneamente';
                        $_SESSION ["jsonsalida"] ["focus"] = 'pattot_' . $finAno;
                        $json = $api->json($_SESSION ["jsonsalida"]);
                        $api->response(str_replace("\\/", "/", $json), 200);
                    }
                }

                if (isset($fin["paspat"])) {
                    if (!is_numeric($fin["paspat"])) {
                        $mysqli->close();
                        $_SESSION ["jsonsalida"] ["codigoerror"] = '0001';
                        $_SESSION ["jsonsalida"] ["mensajeerror"] = 'Pasivo + patrimonio para el año ' . $finAno . ' digitado erroneamente';
                        $_SESSION ["jsonsalida"] ["focus"] = 'paspat_' . $finAno;
                        $json = $api->json($_SESSION ["jsonsalida"]);
                        $api->response(str_replace("\\/", "/", $json), 200);
                    }
                }

                if (isset($fin["balsoc"])) {
                    if (!is_numeric($fin["balsoc"])) {
                        $mysqli->close();
                        $_SESSION ["jsonsalida"] ["codigoerror"] = '0001';
                        $_SESSION ["jsonsalida"] ["mensajeerror"] = 'Balance social para el año ' . $finAno . ' digitado erroneamente';
                        $_SESSION ["jsonsalida"] ["focus"] = 'balsoc_' . $finAno;
                        $json = $api->json($_SESSION ["jsonsalida"]);
                        $api->response(str_replace("\\/", "/", $json), 200);
                    }
                }

                if (isset($fin["ingope"])) {
                    if (!is_numeric($fin["ingope"])) {
                        $mysqli->close();
                        $_SESSION ["jsonsalida"] ["codigoerror"] = '0001';
                        $_SESSION ["jsonsalida"] ["mensajeerror"] = 'Ingresos de la actividad principal para el año ' . $finAno . ' digitado erroneamente';
                        $_SESSION ["jsonsalida"] ["focus"] = 'ingope_' . $finAno;
                        $json = $api->json($_SESSION ["jsonsalida"]);
                        $api->response(str_replace("\\/", "/", $json), 200);
                    }
                    if ($xTipoTramite == 'renovacionmatricula' || $xTipoTramite == 'renovacionesadl') {
                        if (doubleval($fin["ingope"]) == 0) {
                            if (!defined('RENOVACION_OBLIGATORIO_INGOPE') || RENOVACION_OBLIGATORIO_INGOPE == 'S') {
                                $mysqli->close();
                                $_SESSION ["jsonsalida"] ["codigoerror"] = '0001';
                                $_SESSION ["jsonsalida"] ["mensajeerror"] = 'Ingresos de la actividad principal para el año ' . $finAno . ' no deben ser cero o vacío';
                                $_SESSION ["jsonsalida"] ["focus"] = 'ingope_' . $finAno;
                                $json = $api->json($_SESSION ["jsonsalida"]);
                                $api->response(str_replace("\\/", "/", $json), 200);
                            }
                        }
                    }
                    if ($xTipoTramite == 'matriculapnat' || $xTipoTramite == 'matriculaest' || $xTipoTramite == 'matriculapjur' || $xTipoTramite == 'matriculaesadl' || $xTipoTramite == 'inscripciondocumentos' || $xTipoTramite == 'constitucionpjur' || $xTipoTramite == 'constitucionesadl') {
                        if (doubleval($fin["ingope"]) == 0) {
                            if (!defined('MATRICULAS_OBLIGATORIO_INGOPE') || MATRICULAS_OBLIGATORIO_INGOPE == 'S') {
                                $mysqli->close();
                                $_SESSION ["jsonsalida"] ["codigoerror"] = '0001';
                                $_SESSION ["jsonsalida"] ["mensajeerror"] = 'Ingresos de la actividad principal para el a&ntilde;o ' . $finAno . ' no deben ser cero o vacío';
                                $_SESSION ["jsonsalida"] ["focus"] = 'ingope_' . $finAno;
                                $json = $api->json($_SESSION ["jsonsalida"]);
                                $api->response(str_replace("\\/", "/", $json), 200);
                            }
                        }
                    }
                }

                if (isset($fin["ingnoope"])) {
                    if (!is_numeric($fin["ingnoope"])) {
                        $mysqli->close();
                        $_SESSION ["jsonsalida"] ["codigoerror"] = '0001';
                        $_SESSION ["jsonsalida"] ["mensajeerror"] = 'Otros ingresos para el a&ntilde;o ' . $finAno . ' no deben ser cero o vacío';
                        $_SESSION ["jsonsalida"] ["focus"] = 'ingnoope_' . $finAno;
                        $json = $api->json($_SESSION ["jsonsalida"]);
                        $api->response(str_replace("\\/", "/", $json), 200);
                    }
                }

                if (isset($fin["gtoven"])) {
                    if (!is_numeric($fin["gtoven"])) {
                        $mysqli->close();
                        $_SESSION ["jsonsalida"] ["codigoerror"] = '0001';
                        $_SESSION ["jsonsalida"] ["mensajeerror"] = 'Gastos operacionales para el a&ntilde;o ' . $finAno . ' no deben ser cero o vacío';
                        $_SESSION ["jsonsalida"] ["focus"] = 'gtoven_' . $finAno;
                        $json = $api->json($_SESSION ["jsonsalida"]);
                        $api->response(str_replace("\\/", "/", $json), 200);
                    }
                }

                if (isset($fin["gtoadm"])) {
                    if (!is_numeric($fin["gtoadm"])) {
                        $mysqli->close();
                        $_SESSION ["jsonsalida"] ["codigoerror"] = '0001';
                        $_SESSION ["jsonsalida"] ["mensajeerror"] = 'Gastos no operacionales para el a&ntilde;o ' . $finAno . ' no deben ser cero o vacío';
                        $_SESSION ["jsonsalida"] ["focus"] = 'gtoadm_' . $finAno;
                        $json = $api->json($_SESSION ["jsonsalida"]);
                        $api->response(str_replace("\\/", "/", $json), 200);
                    }
                }

                if (isset($fin["cosven"])) {
                    if (!is_numeric($fin["cosven"])) {
                        $mysqli->close();
                        $_SESSION ["jsonsalida"] ["codigoerror"] = '0001';
                        $_SESSION ["jsonsalida"] ["mensajeerror"] = 'Costo de ventas para el año ' . $finAno . ' no deben ser cero o vacío';
                        $_SESSION ["jsonsalida"] ["focus"] = 'cosven_' . $finAno;
                        $json = $api->json($_SESSION ["jsonsalida"]);
                        $api->response(str_replace("\\/", "/", $json), 200);
                    }
                }

                if (isset($fin["gasimp"])) {
                    if (!is_numeric($fin["gasimp"])) {
                        $mysqli->close();
                        $_SESSION ["jsonsalida"] ["codigoerror"] = '0001';
                        $_SESSION ["jsonsalida"] ["mensajeerror"] = 'Gastos por impuestos para el año ' . $finAno . ' no deben ser cero o vacío';
                        $_SESSION ["jsonsalida"] ["focus"] = 'gasimp_' . $finAno;
                        $json = $api->json($_SESSION ["jsonsalida"]);
                        $api->response(str_replace("\\/", "/", $json), 200);
                    }
                }

                if (isset($fin["gasint"])) {
                    if (!is_numeric($fin["gasint"])) {
                        $mysqli->close();
                        $_SESSION ["jsonsalida"] ["codigoerror"] = '0001';
                        $_SESSION ["jsonsalida"] ["mensajeerror"] = 'Gastos por intereses/financieros para el año ' . $finAno . ' no deben ser cero o vacío';
                        $_SESSION ["jsonsalida"] ["focus"] = 'gasint_' . $finAno;
                        $json = $api->json($_SESSION ["jsonsalida"]);
                        $api->response(str_replace("\\/", "/", $json), 200);
                    }
                }


                if (isset($fin["utiope"])) {
                    if (!is_numeric($fin["utiope"])) {
                        $mysqli->close();
                        $_SESSION ["jsonsalida"] ["codigoerror"] = '0001';
                        $_SESSION ["jsonsalida"] ["mensajeerror"] = 'Utilidad / pérdida para el año ' . $finAno . ' no deben ser cero o vacío';
                        $_SESSION ["jsonsalida"] ["focus"] = 'utiope_' . $finAno;
                        $json = $api->json($_SESSION ["jsonsalida"]);
                        $api->response(str_replace("\\/", "/", $json), 200);
                    }
                }

                if (isset($fin["utinet"])) {
                    if (!is_numeric($fin["utinet"])) {
                        $mysqli->close();
                        $_SESSION ["jsonsalida"] ["codigoerror"] = '0001';
                        $_SESSION ["jsonsalida"] ["mensajeerror"] = 'Resultado del ejercicio para el año ' . $finAno . ' no deben ser cero o vacío';
                        $_SESSION ["jsonsalida"] ["focus"] = 'utinet_' . $finAno;
                        $json = $api->json($_SESSION ["jsonsalida"]);
                        $api->response(str_replace("\\/", "/", $json), 200);
                    }
                }

                if ($finAno == date("Y")) {
                    $temx = floatval($fin["actcte"]) + floatval($fin["actnocte"]);
                    $temx = abs(floatval($fin["acttot"]) - $temx);
                    if ($temx > 0.005) {
                        $mysqli->close();
                        $_SESSION ["jsonsalida"] ["codigoerror"] = '0001';
                        $_SESSION ["jsonsalida"] ["mensajeerror"] = 'La sumatoria de los componentes del activo no es igual al activo total (para el año ' . $finAno . ') (' . $temx . ') (' . $fin["acttot"] . ')';
                        $_SESSION ["jsonsalida"] ["focus"] = 'actcte_' . $finAno;
                        $json = $api->json($_SESSION ["jsonsalida"]);
                        $api->response(str_replace("\\/", "/", $json), 200);
                    }

                    if (floatval($fin["acttot"]) != floatval($fin["paspat"])) {
                        $mysqli->close();
                        $_SESSION ["jsonsalida"] ["codigoerror"] = '0001';
                        $_SESSION ["jsonsalida"] ["mensajeerror"] = 'Activos diferentes a pasivos + patrimonio (para el a&ntilde;o ' . $finAno . ')';
                        $_SESSION ["jsonsalida"] ["focus"] = 'actcte_' . $finAno;
                        $json = $api->json($_SESSION ["jsonsalida"]);
                        $api->response(str_replace("\\/", "/", $json), 200);
                    }
                    if (abs(floatval($fin["pascte"]) + floatval($fin["paslar"]) - floatval($fin["pastot"])) > 0.005) {
                        $mysqli->close();
                        $_SESSION ["jsonsalida"] ["codigoerror"] = '0001';
                        $_SESSION ["jsonsalida"] ["mensajeerror"] = 'Sumatoria de Pasivos diferente a Pasivos totales (para el año ' . $finAno . ')';
                        $_SESSION ["jsonsalida"] ["focus"] = 'actcte_' . $finAno;
                        $json = $api->json($_SESSION ["jsonsalida"]);
                        $api->response(str_replace("\\/", "/", $json), 200);
                    }

                    if (abs(floatval($fin["pastot"]) + floatval($fin["pattot"]) - floatval($fin["paspat"])) > 0.005) {
                        $mysqli->close();
                        $_SESSION ["jsonsalida"] ["codigoerror"] = '0001';
                        $_SESSION ["jsonsalida"] ["mensajeerror"] = 'Sumatoria de Pasivos + Patrimonio diferente al total (para el a&ntilde;o ' . $finAno . ')';
                        $_SESSION ["jsonsalida"] ["focus"] = 'actcte_' . $finAno;
                        $json = $api->json($_SESSION ["jsonsalida"]);
                        $api->response(str_replace("\\/", "/", $json), 200);
                    }
                }
            }
        }


        if (($_SESSION["formulario"]["datos"]["categoria"] == '2') && ($_SESSION["formulario"]["datos"]["actvin"] == 0)) {
            $mysqli->close();
            $_SESSION ["jsonsalida"] ["codigoerror"] = '0001';
            $_SESSION ["jsonsalida"] ["mensajeerror"] = 'Activos vinculados no deben estar en blancos';
            $_SESSION ["jsonsalida"] ["focus"] = 'actvin';
            $json = $api->json($_SESSION ["jsonsalida"]);
            $api->response(str_replace("\\/", "/", $json), 200);
        }

        if (($_SESSION["formulario"]["datos"]["categoria"] == '3') && ($_SESSION["formulario"]["datos"]["actvin"] == 0)) {
            $mysqli->close();
            $_SESSION ["jsonsalida"] ["codigoerror"] = '0001';
            $_SESSION ["jsonsalida"] ["mensajeerror"] = 'Activos vinculados no deben estar en blancos';
            $_SESSION ["jsonsalida"] ["focus"] = 'actvin';
            $json = $api->json($_SESSION ["jsonsalida"]);
            $api->response(str_replace("\\/", "/", $json), 200);
        }

        // 2017-08-09: Validación grupo Niif
        if (($_SESSION["formulario"]["datos"]["organizacion"] != '02') && ($_SESSION["formulario"]["datos"]["categoria"] != '2') && ($_SESSION["formulario"]["datos"]["categoria"] != '3')) {
            if (ltrim($_SESSION["formulario"]["datos"]["gruponiif"], "0") == '') {
                if ($_SESSION["formulario"]["datos"]["ultanoren"] >= '2015') {
                    $mysqli->close();
                    $_SESSION ["jsonsalida"] ["codigoerror"] = '0001';
                    $_SESSION ["jsonsalida"] ["mensajeerror"] = 'Grupo NIIF debe tener un contenido válido';
                    $_SESSION ["jsonsalida"] ["focus"] = 'gruponiif';
                    $json = $api->json($_SESSION ["jsonsalida"]);
                    $api->response(str_replace("\\/", "/", $json), 200);
                }
            }
        }

        // 2018-10-02: Validación controles ley1780
        if ($_SESSION["formulario"]["datos"]["organizacion"] == '01' || ($_SESSION["formulario"]["datos"]["organizacion"] > '02' && $_SESSION["formulario"]["datos"]["categoria"] == '1')) {
            if ($_SESSION["formulario"]["datos"]["organizacion"] != '12' && $_SESSION["formulario"]["datos"]["organizacion"] != '14') {
                if ($_SESSION["tramite"]["tipotramite"] == 'renovacionmatricula' || $_SESSION["tramite"]["tipotramite"] == 'renovacionesadl') {
                    if (trim($_SESSION["formulario"]["datos"]["cumplerequisitos1780"]) == '') {
                        if ($_SESSION["formulario"]["datos"]["ultanoren"] >= '2015') {
                            $mysqli->close();
                            $_SESSION ["jsonsalida"] ["codigoerror"] = '0001';
                            $_SESSION ["jsonsalida"] ["mensajeerror"] = 'De indicar si cumple o no con los requisitos de la Ley 1780';
                            $_SESSION ["jsonsalida"] ["focus"] = 'cumplerequisitos1780';
                            $json = $api->json($_SESSION ["jsonsalida"]);
                            $api->response(str_replace("\\/", "/", $json), 200);
                        }
                    }
                    if (trim($_SESSION["formulario"]["datos"]["cumplerequisitos1780primren"]) == '') {
                        if ($_SESSION["formulario"]["datos"]["ultanoren"] >= '2015') {
                            $mysqli->close();
                            $_SESSION ["jsonsalida"] ["codigoerror"] = '0001';
                            $_SESSION ["jsonsalida"] ["mensajeerror"] = 'De indicar si mantiene o no los requisitos de la Ley 1780';
                            $_SESSION ["jsonsalida"] ["focus"] = 'cumplerequisitos1780primren';
                            $json = $api->json($_SESSION ["jsonsalida"]);
                            $api->response(str_replace("\\/", "/", $json), 200);
                        }
                    }
                } else {
                    if (trim($_SESSION["formulario"]["datos"]["cumplerequisitos1780"]) == '') {
                        if ($_SESSION["formulario"]["datos"]["ultanoren"] >= '2015') {
                            $mysqli->close();
                            $_SESSION ["jsonsalida"] ["codigoerror"] = '0001';
                            $_SESSION ["jsonsalida"] ["mensajeerror"] = 'De indicar si cumple o no con los requisitos de la Ley 1780';
                            $_SESSION ["jsonsalida"] ["focus"] = 'cumplerequisitos1780';
                            $json = $api->json($_SESSION ["jsonsalida"]);
                            $api->response(str_replace("\\/", "/", $json), 200);
                        }
                    }
                }
            }
        }

        // 2017-08-09: Validación información seguridad social
        if ($_SESSION["formulario"]["datos"]["organizacion"] == '01' || ($_SESSION["formulario"]["datos"]["organizacion"] > '02' && $_SESSION["formulario"]["datos"]["categoria"] == '1')) {
            if (trim($_SESSION["formulario"]["datos"]["aportantesegsocial"]) == '') {
                $txtemergente .= 'No seleccion&oacute; el tipo de aportante a seguridad social' . chr(13);
            } else {
                if (ltrim($_SESSION["formulario"]["datos"]["tipoaportantesegsocial"], "0") == '') {
                    if ($_SESSION["formulario"]["datos"]["aportantesegsocial"] == 'S') {
                        if ($_SESSION["formulario"]["datos"]["ultanoren"] >= '2015') {
                            $mysqli->close();
                            $_SESSION ["jsonsalida"] ["codigoerror"] = '0001';
                            $_SESSION ["jsonsalida"] ["mensajeerror"] = 'Seleccione un tipo de aportante válido';
                            $_SESSION ["jsonsalida"] ["focus"] = 'tipoaportantesegsocial';
                            $json = $api->json($_SESSION ["jsonsalida"]);
                            $api->response(str_replace("\\/", "/", $json), 200);
                        }
                    } else {
                        $_SESSION["formulario"]["datos"]["tipoaportantesegsocial"] = '';
                    }
                } else {
                    if ($_SESSION["formulario"]["datos"]["aportantesegsocial"] == 'N') {
                        if ($_SESSION["formulario"]["datos"]["ultanoren"] >= '2015') {
                            $mysqli->close();
                            $_SESSION ["jsonsalida"] ["codigoerror"] = '0001';
                            $_SESSION ["jsonsalida"] ["mensajeerror"] = 'Indicó que no aporta a seguridad social, por lo tanto no debe seleccionar ningú tipo de aportante.';
                            $_SESSION ["jsonsalida"] ["focus"] = 'cumplerequisitos1780';
                            $json = $api->json($_SESSION ["jsonsalida"]);
                            $api->response(str_replace("\\/", "/", $json), 200);
                        }
                    }
                }
            }
        } else {
            $_SESSION["formulario"]["datos"]["aportantesegsocial"] = '';
            $_SESSION["formulario"]["datos"]["tipoaportantesegsocial"] = '';
        }

        // Responsabilidades tributarias
        if (defined('ELIMINADO_PRERUT') && ELIMINADO_PRERUT == 'SI') {
            if ($_SESSION["formulario"]["datos"]["ultanoren"] >= '2015') {
                if ($_SESSION["formulario"]["datos"]["organizacion"] == '01' || ($_SESSION["formulario"]["datos"]["organizacion"] > '02' && $_SESSION["formulario"]["datos"]["categoria"] == '1')) {

                    //
                    if ($_SESSION["formulario"]["datos"]["nit"] == '') {
                        $tiene = 'no';
                        $tienedupli = '';
                        $atem = array();
                        if (!empty($_SESSION["formulario"]["datos"]["codrespotri"])) {
                            foreach ($_SESSION["formulario"]["datos"]["codrespotri"] as $tx2) {
                                if (trim($tx2) != '') {
                                    $tiene = 'si';
                                    if (isset($atem[$tx2])) {
                                        $tienedupli = $tx2 . ' ';
                                    }
                                    $atem[$tx2] = $tx2;
                                }
                            }
                        }
                        if ($tiene == 'no') {
                            $mysqli->close();
                            $_SESSION ["jsonsalida"] ["codigoerror"] = '0001';
                            $_SESSION ["jsonsalida"] ["mensajeerror"] = 'Debe indicar las responsabilidades tributarias en las cuales se clasificará';
                            $_SESSION ["jsonsalida"] ["focus"] = 'codrespotri_1';
                            $json = $api->json($_SESSION ["jsonsalida"]);
                            $api->response(str_replace("\\/", "/", $json), 200);
                        }
                        if ($tienedupli != '') {
                            $mysqli->close();
                            $_SESSION ["jsonsalida"] ["codigoerror"] = '0001';
                            $_SESSION ["jsonsalida"] ["mensajeerror"] = 'Por favor revise, seleccionó responsabilidades repetidas (' . trim($tienedupli) . ')';
                            $_SESSION ["jsonsalida"] ["focus"] = 'codrespotri_1';
                            $json = $api->json($_SESSION ["jsonsalida"]);
                            $api->response(str_replace("\\/", "/", $json), 200);
                        }
                    }

                    //
                    if ($_SESSION["formulario"]["datos"]["nit"] != '') {
                        if (!empty($_SESSION["formulario"]["datos"]["codrespotri"])) {
                            $tiene = 'no';
                            $tienedupli = '';
                            $atem = array();
                            foreach ($_SESSION["formulario"]["datos"]["codrespotri"] as $tx2) {
                                if (trim((string) $tx2) != '') {
                                    $tiene = 'si';
                                    if (isset($atem[$tx2])) {
                                        $tienedupli = $tx2 . ' ';
                                    }
                                }
                            }
                            if ($tiene == 'si') {
                                $mysqli->close();
                                $_SESSION ["jsonsalida"] ["codigoerror"] = '0001';
                                $_SESSION ["jsonsalida"] ["mensajeerror"] = 'Indicó que tiene Nit, por lo tanto no debe reportar responsabilidades tributarias';
                                $_SESSION ["jsonsalida"] ["focus"] = 'codrespotri_1';
                                $json = $api->json($_SESSION ["jsonsalida"]);
                                $api->response(str_replace("\\/", "/", $json), 200);
                            }
                        }
                    }
                }
            }
        }

        // 2021-12-08: Validación datos del propietario
        if ($_SESSION["formulario"]["datos"]["organizacion"] == '02') {
            if ($_SESSION["formulario"]["datos"]["propietarios"][1]["idtipoidentificacionpropietario"] == '') {
                if ($_SESSION["formulario"]["datos"]["propietarios"][1]["matriculapropietario"] != 'NUEVAJUR' && $_SESSION["formulario"]["datos"]["propietarios"][1]["matriculapropietario"] != 'NUEVAESA') {
                    $mysqli->close();
                    $_SESSION ["jsonsalida"] ["codigoerror"] = '0001';
                    $_SESSION ["jsonsalida"] ["mensajeerror"] = 'Debe seleccionar el tipo de identificación del propietario';
                    $_SESSION ["jsonsalida"] ["focus"] = 'prop_1_idtipoidentificacionpropietario';
                    $json = $api->json($_SESSION ["jsonsalida"]);
                    $api->response(str_replace("\\/", "/", $json), 200);
                }
            } else {
                if ($_SESSION["formulario"]["datos"]["propietarios"][1]["matriculapropietario"] == 'NUEVAJUR' || $_SESSION["formulario"]["datos"]["propietarios"][1]["matriculapropietario"] == 'NUEVAESA') {
                    $mysqli->close();
                    $_SESSION ["jsonsalida"] ["codigoerror"] = '0001';
                    $_SESSION ["jsonsalida"] ["mensajeerror"] = 'La persona jurídica se está constituyendo, no se debe asociar tipo de identificación del propietario';
                    $_SESSION ["jsonsalida"] ["focus"] = 'prop_1_idtipoidentificacionpropietario';
                    $json = $api->json($_SESSION ["jsonsalida"]);
                    $api->response(str_replace("\\/", "/", $json), 200);
                }
            }
            if (trim($_SESSION["formulario"]["datos"]["propietarios"][1]["identificacionpropietario"]) == '') {
                if ($_SESSION["formulario"]["datos"]["propietarios"][1]["matriculapropietario"] != 'NUEVAJUR' && $_SESSION["formulario"]["datos"]["propietarios"][1]["matriculapropietario"] != 'NUEVAESA') {
                    $mysqli->close();
                    $_SESSION ["jsonsalida"] ["codigoerror"] = '0001';
                    $_SESSION ["jsonsalida"] ["mensajeerror"] = 'Debe indicar el n&uacute;mero de identificación del propietario';
                    $_SESSION ["jsonsalida"] ["focus"] = 'prop_1_idtipoidentificacionpropietario';
                    $json = $api->json($_SESSION ["jsonsalida"]);
                    $api->response(str_replace("\\/", "/", $json), 200);
                }
            } else {
                if ($_SESSION["formulario"]["datos"]["propietarios"][1]["matriculapropietario"] == 'NUEVAJUR' || $_SESSION["formulario"]["datos"]["propietarios"][1]["matriculapropietario"] == 'NUEVAESA') {
                    $mysqli->close();
                    $_SESSION ["jsonsalida"] ["codigoerror"] = '0001';
                    $_SESSION ["jsonsalida"] ["mensajeerror"] = 'La persona jurídica se está constituyendo, no se debe asociar número de identificación del propietario';
                    $_SESSION ["jsonsalida"] ["focus"] = 'prop_1_idtipoidentificacionpropietario';
                    $json = $api->json($_SESSION ["jsonsalida"]);
                    $api->response(str_replace("\\/", "/", $json), 200);
                }
            }
            if (trim($_SESSION["formulario"]["datos"]["propietarios"][1]["nitpropietario"]) != '') {
                if ($_SESSION["formulario"]["datos"]["propietarios"][1]["matriculapropietario"] == 'NUEVAJUR' || $_SESSION["formulario"]["datos"]["propietarios"][1]["matriculapropietario"] == 'NUEVAESA') {
                    $mysqli->close();
                    $_SESSION ["jsonsalida"] ["codigoerror"] = '0001';
                    $_SESSION ["jsonsalida"] ["mensajeerror"] = 'La persona jurídica se está constituyendo, no se debe asociar número de nit del propietario';
                    $_SESSION ["jsonsalida"] ["focus"] = 'prop_1_idtipoidentificacionpropietario';
                    $json = $api->json($_SESSION ["jsonsalida"]);
                    $api->response(str_replace("\\/", "/", $json), 200);
                }
            }
            if ($_SESSION["formulario"]["datos"]["propietarios"][1]["idtipoidentificacionpropietario"] == '2') {
                $_SESSION["formulario"]["datos"]["propietarios"][1]["nitpropietario"] = $_SESSION["formulario"]["datos"]["propietarios"][1]["identificacionpropietario"];
            }
            if ($_SESSION["formulario"]["datos"]["propietarios"][1]["nombrepropietario"] == '') {
                $mysqli->close();
                $_SESSION ["jsonsalida"] ["codigoerror"] = '0001';
                $_SESSION ["jsonsalida"] ["mensajeerror"] = 'Debe indicar el nombre del propietario';
                $_SESSION ["jsonsalida"] ["focus"] = 'prop_1_idtipoidentificacionpropietario';
                $json = $api->json($_SESSION ["jsonsalida"]);
                $api->response(str_replace("\\/", "/", $json), 200);
            }
            if ($_SESSION["formulario"]["datos"]["propietarios"][1]["municipiopropietario"] == '') {
                $mysqli->close();
                $_SESSION ["jsonsalida"] ["codigoerror"] = '0001';
                $_SESSION ["jsonsalida"] ["mensajeerror"] = 'Debe indicar el municipio comercial del propietario';
                $_SESSION ["jsonsalida"] ["focus"] = 'prop_1_idtipoidentificacionpropietario';
                $json = $api->json($_SESSION ["jsonsalida"]);
                $api->response(str_replace("\\/", "/", $json), 200);
            }
            if ($_SESSION["formulario"]["datos"]["propietarios"][1]["direccionpropietario"] == '') {
                $mysqli->close();
                $_SESSION ["jsonsalida"] ["codigoerror"] = '0001';
                $_SESSION ["jsonsalida"] ["mensajeerror"] = 'Debe indicar la dirección comercial del propietario';
                $_SESSION ["jsonsalida"] ["focus"] = 'prop_1_idtipoidentificacionpropietario';
                $json = $api->json($_SESSION ["jsonsalida"]);
                $api->response(str_replace("\\/", "/", $json), 200);
            }
            if ($_SESSION["formulario"]["datos"]["propietarios"][1]["municipionotpropietario"] == '') {
                $mysqli->close();
                $_SESSION ["jsonsalida"] ["codigoerror"] = '0001';
                $_SESSION ["jsonsalida"] ["mensajeerror"] = 'Debe indicar el municipio de notificación del propietario';
                $_SESSION ["jsonsalida"] ["focus"] = 'prop_1_idtipoidentificacionpropietario';
                $json = $api->json($_SESSION ["jsonsalida"]);
                $api->response(str_replace("\\/", "/", $json), 200);
            }
            if ($_SESSION["formulario"]["datos"]["propietarios"][1]["direccionnotpropietario"] == '') {
                $mysqli->close();
                $_SESSION ["jsonsalida"] ["codigoerror"] = '0001';
                $_SESSION ["jsonsalida"] ["mensajeerror"] = 'Debe indicar la dirección de notificación del propietario';
                $_SESSION ["jsonsalida"] ["focus"] = 'prop_1_idtipoidentificacionpropietario';
                $json = $api->json($_SESSION ["jsonsalida"]);
                $api->response(str_replace("\\/", "/", $json), 200);
            }
            if ($_SESSION["formulario"]["datos"]["propietarios"][1]["telefonopropietario"] == '') {
                $mysqli->close();
                $_SESSION ["jsonsalida"] ["codigoerror"] = '0001';
                $_SESSION ["jsonsalida"] ["mensajeerror"] = 'Debe indicar al menos un número telefónico del propietario';
                $_SESSION ["jsonsalida"] ["focus"] = 'prop_1_idtipoidentificacionpropietario';
                $json = $api->json($_SESSION ["jsonsalida"]);
                $api->response(str_replace("\\/", "/", $json), 200);
            }
        }

        // 2019-11-07: JINT - Validación información de ESADL
        if ($_SESSION["formulario"]["datos"]["organizacion"] == '12' || $_SESSION["formulario"]["datos"]["organizacion"] == '14') {
            if ($_SESSION["formulario"]["datos"]["categoria"] == '1') {

                //
                $tran = false;
                if (base64_decode($_SESSION["entrada"]["idliquidacion"]) != 0) {
                    $trans = retornarRegistrosMysqliApi($mysqli, 'mreg_liquidacion_transacciones', "idliquidacion=" . base64_decode($_SESSION["entrada"]["idliquidacion"]), "id");
                    if ($trans && !empty($trans)) {
                        foreach ($trans as $tx) {
                            if ($tx["organizacion"] == '12' || $tx["organizacion"] == '14') {
                                if ($tx["categoria"] == '1') {
                                    $tran = $tx;
                                }
                            }
                        }
                    }
                }

                //
                if (intval($_SESSION["formulario"]["datos"]["ctresacntasociados"]) == 0) {
                    $mysqli->close();
                    $_SESSION ["jsonsalida"] ["codigoerror"] = '0001';
                    $_SESSION ["jsonsalida"] ["mensajeerror"] = 'No se ha indicado el número de asociados (en el grupo de entidades sin ánimo de lucro)';
                    $_SESSION ["jsonsalida"] ["focus"] = 'ctresacntasociados';
                    $json = $api->json($_SESSION ["jsonsalida"]);
                    $api->response(str_replace("\\/", "/", $json), 200);
                }

                //
                $sumatoria = intval($_SESSION["formulario"]["datos"]["ctresacntmujeres"]) + intval($_SESSION["formulario"]["datos"]["ctresacnthombres"]);
                if (intval($_SESSION["formulario"]["datos"]["ctresacntmujeres"]) != 0 || intval($_SESSION["formulario"]["datos"]["ctresacnthombres"]) != 0) {
                    if (intval($_SESSION["formulario"]["datos"]["ctresacntasociados"]) != $sumatoria) {
                        $mysqli->close();
                        $_SESSION ["jsonsalida"] ["codigoerror"] = '0001';
                        $_SESSION ["jsonsalida"] ["mensajeerror"] = 'La sumatoria de los asociados no es acorde (en el grupo de entidades sin ánimo de lucro)';
                        $_SESSION ["jsonsalida"] ["focus"] = 'ctresacntasociados';
                        $json = $api->json($_SESSION ["jsonsalida"]);
                        $api->response(str_replace("\\/", "/", $json), 200);
                    }
                }

                if (intval($_SESSION["formulario"]["datos"]["ctresacntmujeres"]) < 0) {
                    $mysqli->close();
                    $_SESSION ["jsonsalida"] ["codigoerror"] = '0001';
                    $_SESSION ["jsonsalida"] ["mensajeerror"] = 'No se ha indicado el número de asociados mujeres (en el grupo de entidades sin ánimo de lucro)';
                    $_SESSION ["jsonsalida"] ["focus"] = 'ctresacntasociados';
                    $json = $api->json($_SESSION ["jsonsalida"]);
                    $api->response(str_replace("\\/", "/", $json), 200);
                }
                if (intval($_SESSION["formulario"]["datos"]["ctresacnthombres"]) < 0) {
                    $mysqli->close();
                    $_SESSION ["jsonsalida"] ["codigoerror"] = '0001';
                    $_SESSION ["jsonsalida"] ["mensajeerror"] = 'No se ha indicado el número de asociados hombres (en el grupo de entidades sin ánimo de lucro)';
                    $_SESSION ["jsonsalida"] ["focus"] = 'ctresacntasociados';
                    $json = $api->json($_SESSION ["jsonsalida"]);
                    $api->response(str_replace("\\/", "/", $json), 200);
                }

                if (trim($_SESSION["formulario"]["datos"]["ctresaivc"]) == '') {
                    $mysqli->close();
                    $_SESSION ["jsonsalida"] ["codigoerror"] = '0001';
                    $_SESSION ["jsonsalida"] ["mensajeerror"] = 'No se ha indicado la entidad que ejerce la vigilancia y el control (en el grupo de entidades sin ánimo de lucro)';
                    $_SESSION ["jsonsalida"] ["focus"] = 'ctresaivc';
                    $json = $api->json($_SESSION ["jsonsalida"]);
                    $api->response(str_replace("\\/", "/", $json), 200);
                }

                if ($_SESSION["formulario"]["datos"]["claseespesadl"] == '73' ||
                        $_SESSION["formulario"]["datos"]["claseespesadl"] == '74' ||
                        $_SESSION["formulario"]["datos"]["claseespesadl"] == '75') {
                    if ($_SESSION["formulario"]["datos"]["condiespe2219"] == '') {
                        $mysqli->close();
                        $_SESSION ["jsonsalida"] ["codigoerror"] = '0001';
                        $_SESSION ["jsonsalida"] ["mensajeerror"] = 'Debe indicar si la entidad tiene o no alguna condición especial en relación con lo establecido en la Ley 2219 de 2022';
                        $_SESSION ["jsonsalida"] ["focus"] = 'condiespe2219';
                        $json = $api->json($_SESSION ["jsonsalida"]);
                        $api->response(str_replace("\\/", "/", $json), 200);
                    }
                }

                //
                if ($tran) {
                    if ($tran["codgenesadl"] != '' && $tran["codgenesadl"] != $_SESSION["formulario"]["datos"]["ctresacodnat"]) {
                        $mysqli->close();
                        $_SESSION ["jsonsalida"] ["codigoerror"] = '0001';
                        $_SESSION ["jsonsalida"] ["mensajeerror"] = 'Se seleccionó una naturalza de entidad sin ánimo de lucro (' . $_SESSION["formulario"]["datos"]["ctresacodnat"] . ') diferente a la indicada al inicio del trámite (' . $tran["codgenesadl"] . ')';
                        $_SESSION ["jsonsalida"] ["focus"] = 'ctresacodnat';
                        $json = $api->json($_SESSION ["jsonsalida"]);
                        $api->response(str_replace("\\/", "/", $json), 200);
                    }
                    if ($tran["codespeesadl"] != '' && $tran["codespeesadl"] != $_SESSION["formulario"]["datos"]["claseespesadl"]) {
                        $mysqli->close();
                        $_SESSION ["jsonsalida"] ["codigoerror"] = '0001';
                        $_SESSION ["jsonsalida"] ["mensajeerror"] = 'Se seleccionó una clase de entidad sin ánimo de lucro (' . $_SESSION["formulario"]["datos"]["claseespesadl"] . ') diferente a la indicada al inicio del trámite (' . $tran["codespeesadl"] . ')';
                        $_SESSION ["jsonsalida"] ["focus"] = 'claseespesadl';
                        $json = $api->json($_SESSION ["jsonsalida"]);
                        $api->response(str_replace("\\/", "/", $json), 200);
                    }
                    if ($tran["condiespecialley2219"] != '' && $tran["condiespecialley2219"] != $_SESSION["formulario"]["datos"]["condiespe2219"]) {
                        $mysqli->close();
                        $_SESSION ["jsonsalida"] ["codigoerror"] = '0001';
                        $_SESSION ["jsonsalida"] ["mensajeerror"] = 'Se seleccionó una condición especial según Ley 2219 de 2022 (' . $_SESSION["formulario"]["datos"]["condiespe2219"] . ') diferente a la indicada al inicio del trámite (' . $tran["condiespecialley2219"] . ')';
                        $_SESSION ["jsonsalida"] ["focus"] = 'condiespe2219';
                        $json = $api->json($_SESSION ["jsonsalida"]);
                        $api->response(str_replace("\\/", "/", $json), 200);
                    }
                } else {
                    if ($_SESSION["formulario"]["datos"]["claseespesadl"] == '73' || $_SESSION["formulario"]["datos"]["claseespesadl"] == '74' || $_SESSION["formulario"]["datos"]["claseespesadl"] == '75') {
                        if ($_SESSION["formulario"]["datos"]["condiespe2219"] == '') {
                            $mysqli->close();
                            $_SESSION ["jsonsalida"] ["codigoerror"] = '0001';
                            $_SESSION ["jsonsalida"] ["mensajeerror"] = 'Al seleccionar que la clase de entidad es una Asociación Campesina o Agropecuaria, se debe indicar si esta tiene alguna condición especial de acuerdo con la Ley 2219 de 2022.';
                            $_SESSION ["jsonsalida"] ["focus"] = 'condiespe2219';
                            $json = $api->json($_SESSION ["jsonsalida"]);
                            $api->response(str_replace("\\/", "/", $json), 200);
                        }
                    }
                }
            }
        }

        //
        $controlpot = 'N';

        //
        $codigoError = '0000';
        $msgError = '';
        $focus = '';

        //
        if (!defined('ACTIVAR_CONTROL_POT_PONAL')) {
            define('ACTIVAR_CONTROL_POT_PONAL', 'NO');
        }
        if (ACTIVAR_CONTROL_POT_PONAL == 'SI') {
            if (
                    $_SESSION["formulario"]["datos"]["matricula"] != '' &&
                    substr($_SESSION["formulario"]["datos"]["matricula"], 0, 5) != 'NUEVA'
            ) {
                if (
                        $_SESSION["formulario"]["datos"]["ciius"][1] == 'I5630' ||
                        $_SESSION["formulario"]["datos"]["ciius"][1] == 'S9609' ||
                        $_SESSION["formulario"]["datos"]["ciius"][2] == 'I5630' ||
                        $_SESSION["formulario"]["datos"]["ciius"][2] == 'S9609' ||
                        $_SESSION["formulario"]["datos"]["ciius"][3] == 'I5630' ||
                        $_SESSION["formulario"]["datos"]["ciius"][3] == 'S9609' ||
                        $_SESSION["formulario"]["datos"]["ciius"][4] == 'I5630' ||
                        $_SESSION["formulario"]["datos"]["ciius"][4] == 'S9609'
                ) {
                    $conrestriccioninicial = 'NO';
                    $regsx = retornarRegistrosMysqliApi($mysqli, 'mreg_renovacion_datos_control', "idliquidacion=" . $_SESSION["tramite"]["numeroliquidacion"] . " and matricula='" . $_SESSION["formulario"]["datos"]["matricula"] . "' and momento='I'", "dato");
                    $indat = array();
                    foreach ($regsx as $rg) {
                        $indat[$rg["dato"]] = $rg["contenido"];
                        if ($rg["contenido"] == 'I5630' || $rg["contenido"] == 'S9609') {
                            $conrestriccioninicial = 'SI';
                        }
                    }
                    if ($conrestriccioninicial == 'NO') {
                        $codigoError = '4000';
                        $controlpot = 'S';
                    } else {
                        if (
                                $indat["nombre"] != $_SESSION["formulario"]["datos"]["nombre"] ||
                                $indat["dircom"] != $_SESSION["formulario"]["datos"]["dircom"] ||
                                $indat["muncom"] != $_SESSION["formulario"]["datos"]["muncom"]
                        ) {
                            $codigoError = '4000';
                            $controlpot = 'S';
                        }
                    }
                }
            }
            if ($codigoError == '4000') {
                $msgError = 'Para terminar el proceso de renovación de la matr&iacute;cula ' . $_SESSION["formulario"]["datos"]["matricula"] . ' ';
                $msgError .= 'se requerirá la presentación del certificado de uso de suelos ';
                $msgError .= 'dado que este expediente está clasificado en una actividad de alto impacto (I5630 o S9609) ';
                $msgError .= 'de acuerdo con lo establecido en el artículo 85. del Código de Policía.';
            }
        }

        $arrCampos = array('controlpot');
        $arrValores = array("'" . $controlpot . "'");
        regrabarRegistrosMysqliApi($mysqli, 'mreg_liquidacionexpedientes', $arrCampos, $arrValores, "idliquidacion=" . $_SESSION["tramite"]["numeroliquidacion"] . " and matricula='" . $_SESSION["formulario"]["datos"]["matricula"] . "'");

        //
        $mysqli->close();

        // **************************************************************************** //
        // Resultado
        // **************************************************************************** //
        $_SESSION ["jsonsalida"] ["codigoerror"] = $codigoError;
        $_SESSION ["jsonsalida"] ["mensajeerror"] = $msgError;
        $_SESSION ["jsonsalida"] ["html"] = '';
        $json = $api->json($_SESSION ["jsonsalida"]);
        $api->response(str_replace("\\/", "/", $json), 200);
    }

    public function salvarFormularioMercantil() {
        require_once ('mysqli.php');
        require_once ('sanarEntradasNew.class.php');
        require_once ('funcionesGenerales.php');
        require_once ('funcionesRegistrales.php');
        require_once ('myErrorHandler.php');
        set_error_handler('myErrorHandler');

        //
        $emailusuariocontrol = '';
        $codigousuariocontrol = '';
        $mysqli = conexionMysqliApi();

        //
        $_SESSION["formulario"]["datos"]["codrespotri"] = array();
        foreach ($_SESSION["entrada"] as $key => $valor) {
            if ($key != 'tipohttp' &&
                    $key != 'httphost' &&
                    $key != 'codigoempresa' &&
                    $key != 'session_parameters') {

                $valor = base64_decode($valor);

                //
                switch ($key) {

                    case 'idliquidacion':
                        $_SESSION["formulario"]["idliquidacion"] = trim($valor);
                        $_SESSION["formulario"]["liquidacion"] = trim($valor);
                        break;

                    case 'emailusuariocontrol':
                        $emailusuariocontrol = trim($valor);
                        break;

                    case 'codigousuariocontrol':
                        $codigousuariocontrol = trim($valor);
                        break;

                    case 'tipotramite':
                        $_SESSION["formulario"]["tipotramite"] = trim($valor);
                        break;

                    case 'formulariotipotramite':
                        $_SESSION["formulario"]["formulariotipotramite"] = trim($valor);
                        break;

                    case 'codigobarrasseleccionado':
                        $_SESSION["formulario"]["codigobarras"] = trim($valor);
                        break;

                    case 'reliquidacion':
                        $_SESSION["formulario"]["reliquidacion"] = trim($valor);
                        break;

                    case 'cc':
                        $_SESSION["formulario"]["datos"]["cc"] = $valor;
                        break;

                    // Datos generales
                    case 'matricula':
                        $_SESSION["formulario"]["datos"]["matricula"] = ltrim($valor, '0');
                        $_SESSION["formulario"]["matricula"] = ltrim($valor, '0');
                        break;

                    case 'extinciondominio':
                        $_SESSION["formulario"]["datos"]["extinciondominio"] = $valor;
                        break;

                    case 'extinciondominiofechainicio':
                        $_SESSION["formulario"]["datos"]["extinciondominiofechainicio"] = str_replace(array("/", "-"), "", $valor);
                        break;

                    case 'extinciondominiofechafinal':
                        $_SESSION["formulario"]["datos"]["extinciondominiofechafinal"] = str_replace(array("/", "-"), "", $valor);
                        break;

                    case 'ctrcontrolaccesopublico':
                        $_SESSION["formulario"]["datos"]["ctrcontrolaccesopublico"] = $valor;
                        break;

                    case 'organizacion':
                        $_SESSION["formulario"]["datos"]["organizacion"] = $valor;
                        break;

                    case 'categoria':
                        $_SESSION["formulario"]["datos"]["categoria"] = $valor;
                        break;

                    case 'pendiente_ajuste_nuevo_formato':
                        $_SESSION["formulario"]["datos"]["pendiente_ajuste_nuevo_formato"] = $valor;
                        break;

                    case 'fecha_pendiente_ajuste_nuevo_formato':
                        $_SESSION["formulario"]["datos"]["fecha_pendiente_ajuste_nuevo_formato"] = str_replace(array("-", "/"), "", $valor);
                        break;

                    case 'estadomatricula':
                        $_SESSION["formulario"]["datos"]["estadomatricula"] = $valor;
                        break;

                    case 'estadodatosmatricula':
                        $_SESSION["formulario"]["datos"]["estadodatosmatricula"] = $valor;
                        break;

                    case 'estadocapturado':
                        $_SESSION["formulario"]["datos"]["estadocapturado"] = $valor;
                        break;

                    case 'obligadorenovar':
                        $_SESSION["formulario"]["datos"]["obligadorenovar"] = $valor;
                        break;

                    case 'naturaleza':
                        $_SESSION["formulario"]["datos"]["naturaleza"] = $valor;
                        break;

                    case 'ctrbic':
                        $_SESSION["formulario"]["datos"]["ctrbic"] = strtoupper($valor);
                        break;

                    case 'vigilanciasuperfinanciera':
                        $_SESSION["formulario"]["datos"]["vigilanciasuperfinanciera"] = strtoupper($valor);
                        break;

                    case 'fechamatricula':
                        $_SESSION["formulario"]["datos"]["fechamatricula"] = str_replace(array("-", "/"), "", $valor);
                        break;

                    case 'fecharenovacion':
                        $_SESSION["formulario"]["datos"]["fecharenovacion"] = str_replace(array("-", "/"), "", $valor);
                        break;

                    case 'ultanoren':
                        $_SESSION["formulario"]["datos"]["ultanoren"] = $valor;
                        break;

                    // Datos del proponente
                    case 'proponente':
                        $_SESSION["formulario"]["datos"]["proponente"] = ltrim($valor, "0");
                        break;

                    case 'estadoproponente':
                        $_SESSION["formulario"]["datos"]["estadoproponente"] = $valor;
                        break;

                    // Datos de identificación expediente
                    case 'nombre':
                        $_SESSION["formulario"]["datos"]["nombre"] = strtoupper(trim($valor));
                        $_SESSION["formulario"]["datos"]["nombrebase64"] = base64_encode($_SESSION["formulario"]["datos"]["nombre"]);
                        break;

                    case 'ape1':
                        $_SESSION["formulario"]["datos"]["ape1"] = strtoupper(trim($valor));
                        break;

                    case 'ape2':
                        $_SESSION["formulario"]["datos"]["ape2"] = strtoupper(trim($valor));
                        break;

                    case 'nom1':
                        $_SESSION["formulario"]["datos"]["nom1"] = strtoupper(trim($valor));
                        break;

                    case 'nom2':
                        $_SESSION["formulario"]["datos"]["nom2"] = strtoupper(trim($valor));
                        break;

                    case 'sigla':
                        $_SESSION["formulario"]["datos"]["sigla"] = strtoupper(trim($valor));
                        $_SESSION["formulario"]["datos"]["siglabase64"] = base64_encode($_SESSION["formulario"]["datos"]["sigla"]);
                        break;

                    case 'complementorazonsocial':
                        $_SESSION["formulario"]["datos"]["complementorazonsocial"] = strtoupper(trim($valor));
                        break;

                    case 'lggr':
                        $_SESSION["formulario"]["datos"]["lggr"] = $valor;
                        break;

                    case 'nombrecomercial':
                        $_SESSION["formulario"]["datos"]["nombrecomercial"] = strtoupper(trim($valor));
                        break;

                    case 'tipoidentificacion':
                        $_SESSION["formulario"]["datos"]["tipoidentificacion"] = $valor;
                        break;

                    case 'identificacion':
                        $_SESSION["formulario"]["datos"]["identificacion"] = ltrim(str_replace(array("-", ",", ".", " "), "", $valor), '0');
                        break;

                    case 'sexo':
                        $_SESSION["formulario"]["datos"]["sexo"] = strtoupper($valor);
                        break;

                    case 'emprendimientosocial':
                        $_SESSION["formulario"]["datos"]["emprendimientosocial"] = strtoupper($valor);
                        break;

                    case 'empsoccategorias_otros':
                        $_SESSION["formulario"]["datos"]["empsoccategorias_otros"] = strtoupper($valor);
                        break;

                    case 'empsocbeneficiarios_otros':
                        $_SESSION["formulario"]["datos"]["empsocbeneficiarios_otros"] = strtoupper($valor);
                        break;

                    case 'idmunidoc':
                        $_SESSION["formulario"]["datos"]["idmunidoc"] = $valor;
                        break;

                    case 'fechanacimiento':
                        $_SESSION["formulario"]["datos"]["fechanacimiento"] = str_replace(array("/", "-"), "", $valor);
                        break;

                    case 'fecexpdoc':
                        $_SESSION["formulario"]["datos"]["fecexpdoc"] = str_replace(array("/", "-"), "", $valor);
                        break;

                    case 'paisexpdoc':
                        $_SESSION["formulario"]["datos"]["paisexpdoc"] = $valor;
                        break;

                    case 'nit':
                        $_SESSION["formulario"]["datos"]["nit"] = str_replace(array("-", ".", ",", " "), "", $valor);
                        break;

                    case 'estadonit':
                        $_SESSION["formulario"]["datos"]["estadonit"] = $valor;
                        break;

                    case 'admondian':
                        $_SESSION["formulario"]["datos"]["admondian"] = $valor;
                        break;

                    case 'prerut':
                        $_SESSION["formulario"]["datos"]["prerut"] = $valor;
                        break;

                    case 'nacionalidad':
                        $_SESSION["formulario"]["datos"]["nacionalidad"] = strtoupper(trim($valor));
                        break;

                    case 'idetripaiori':
                        $_SESSION["formulario"]["datos"]["idetripaiori"] = strtoupper(trim($valor));
                        break;

                    case 'paiori':
                        $_SESSION["formulario"]["datos"]["paiori"] = strtoupper(trim($valor));
                        break;

                    case 'idetriextep':
                        $_SESSION["formulario"]["datos"]["idetriextep"] = strtoupper(trim($valor));
                        break;

                    // Constitución y vencimientos
                    case 'fechaconstitucion':
                        $_SESSION["formulario"]["datos"]["fechaconstitucion"] = str_replace(array("/", "-"), "", $valor);
                        break;

                    case 'fechavencimiento':
                        $_SESSION["formulario"]["datos"]["fechavencimiento"] = str_replace(array("/", "-"), "", $valor);
                        break;

                    case 'fechavencimiento1':
                        $_SESSION["formulario"]["datos"]["fechavencimiento1"] = str_replace(array("/", "-"), "", $valor);
                        break;

                    case 'fechavencimiento2':
                        $_SESSION["formulario"]["datos"]["fechavencimiento2"] = str_replace(array("/", "-"), "", $valor);
                        break;

                    case 'fechavencimiento3':
                        $_SESSION["formulario"]["datos"]["fechavencimiento3"] = str_replace(array("/", "-"), "", $valor);
                        break;

                    case 'fechavencimiento4':
                        $_SESSION["formulario"]["datos"]["fechavencimiento4"] = str_replace(array("/", "-"), "", $valor);
                        break;

                    case 'fechavencimiento5':
                        $_SESSION["formulario"]["datos"]["fechavencimiento5"] = str_replace(array("/", "-"), "", $valor);
                        break;

                    case 'vigcontrol':
                        $_SESSION["formulario"]["datos"]["vigcontrol"] = strtoupper(trim($valor));
                        break;

                    case 'vigifecini':
                        $_SESSION["formulario"]["datos"]["vigifecini"] = str_replace(array("/", "-"), "", $valor);
                        break;

                    case 'vigifecfin':
                        $_SESSION["formulario"]["datos"]["vigifecfin"] = str_replace(array("/", "-"), "", $valor);
                        break;

                    // Datos de personeria jurídica
                    case 'fecperj':
                        $_SESSION["formulario"]["datos"]["fecperj"] = str_replace(array("/", "-"), "", $valor);
                        break;

                    case 'idorigenperj':
                        $_SESSION["formulario"]["datos"]["idorigenperj"] = $valor;
                        break;

                    case 'numperj':
                        $_SESSION["formulario"]["datos"]["numperj"] = $valor;
                        break;

                    // Información de cancelacion
                    case 'fechacancelacion':
                        $_SESSION["formulario"]["datos"]["fechacancelacion"] = str_replace(array("/", "-"), "", $valor);
                        break;

                    case 'motivocancelacion':
                        $_SESSION["formulario"]["datos"]["motivocancelacion"] = $valor;
                        break;

                    case 'descripcionmotivocancelacion':
                        $_SESSION["formulario"]["datos"]["descripcionmotivocancelacion"] = $valor;
                        break;

                    case 'fechadisolucion':
                        $_SESSION["formulario"]["datos"]["fechadisolucion"] = str_replace(array("/", "-"), "", $valor);
                        break;

                    case 'fechaliquidacion':
                        $_SESSION["formulario"]["datos"]["fechaliquidacion"] = str_replace(array("/", "-"), "", $valor);
                        break;

                    case 'estadotipoliquidacion':
                        $_SESSION["formulario"]["datos"]["estadotipoliquidacion"] = $valor;
                        break;

                    // Cambio de domicilio
                    case 'camant':
                        $_SESSION["formulario"]["datos"]["camant"] = $valor;
                        break;

                    case 'munant':
                        $_SESSION["formulario"]["datos"]["munant"] = $valor;
                        break;

                    case 'matant':
                        $_SESSION["formulario"]["datos"]["matant"] = $valor;
                        break;

                    case 'fecmatant':
                        $_SESSION["formulario"]["datos"]["fecmatant"] = str_replace(array("/", "-"), "", $valor);
                        break;

                    case 'fecrenant':
                        $_SESSION["formulario"]["datos"]["fecrenant"] = str_replace(array("/", "-"), "", $valor);
                        break;

                    case 'ultanorenant':
                        $_SESSION["formulario"]["datos"]["ultanorenant"] = $valor;
                        break;

                    case 'benart7ant':
                        $_SESSION["formulario"]["datos"]["benart7ant"] = $valor;
                        break;

                    case 'benley1780ant':
                        $_SESSION["formulario"]["datos"]["benley1780ant"] = $valor;
                        break;

                    // Beneficios de Ley
                    case 'benley1780':
                        $_SESSION["formulario"]["datos"]["benley1780"] = strtoupper(trim($valor));
                        break;

                    case 'cumplerequisitos1780':
                        $_SESSION["formulario"]["datos"]["cumplerequisitos1780"] = strtoupper(trim($valor));
                        break;

                    case 'renunciabeneficios1780':
                        $_SESSION["formulario"]["datos"]["renunciabeneficios1780"] = strtoupper(trim($valor));
                        break;

                    case 'cumplerequisitos1780primren':
                        $_SESSION["formulario"]["datos"]["cumplerequisitos1780primren"] = strtoupper(trim($valor));
                        break;

                    case 'art4':
                        $_SESSION["formulario"]["datos"]["art4"] = strtoupper(trim($valor));
                        break;

                    case 'art7':
                        $_SESSION["formulario"]["datos"]["art7"] = strtoupper(trim($valor));
                        break;

                    case 'art50':
                        $_SESSION["formulario"]["datos"]["art50"] = strtoupper(trim($valor));
                        break;

                    case 'ctrcancelacion1429':
                        $_SESSION["formulario"]["datos"]["ctrcancelacion1429"] = strtoupper(trim($valor));
                        break;

                    case 'ctrdepuracion1727':
                        $_SESSION["formulario"]["datos"]["ctrdepuracion1727"] = strtoupper(trim($valor));
                        break;

                    case 'ctrfechadepuracion1727':
                        $_SESSION["formulario"]["datos"]["ctrfechadepuracion1727"] = str_replace(array("/", "-"), "", $valor);
                        break;

                    // grupo empresarial
                    case 'tipogruemp':
                        $_SESSION["formulario"]["datos"]["tipogruemp"] = strtoupper(trim($valor));
                        break;

                    case 'nombregruemp':
                        $_SESSION["formulario"]["datos"]["nombregruemp"] = strtoupper(trim($valor));
                        break;

                    // Información de ESADL
                    case 'clasegenesadl':
                        $_SESSION["formulario"]["datos"]["clasegenesadl"] = (trim($valor));
                        break;

                    case 'claseespesadl':
                        $_SESSION["formulario"]["datos"]["claseespesadl"] = (trim($valor));
                        break;

                    case 'ctresacodnat':
                        $_SESSION["formulario"]["datos"]["ctresacodnat"] = (trim($valor));
                        break;

                    case 'claseeconsoli':
                        $_SESSION["formulario"]["datos"]["claseeconsoli"] = (trim($valor));
                        break;

                    case 'condiespe2219':
                        $_SESSION["formulario"]["datos"]["condiespe2219"] = (trim($valor));
                        break;

                    case 'ctrcodcoop':
                        $_SESSION["formulario"]["datos"]["ctrcodcoop"] = strtoupper(trim($valor));
                        break;

                    case 'ctrcodotras':
                        $_SESSION["formulario"]["datos"]["ctrcodotras"] = strtoupper(trim($valor));
                        break;

                    case 'ctresacntasociados':
                        $_SESSION["formulario"]["datos"]["ctresacntasociados"] = intval(trim($valor));
                        break;

                    case 'ctresacntmujeres':
                        $_SESSION["formulario"]["datos"]["ctresacntmujeres"] = intval(trim($valor));
                        break;

                    case 'ctresacnthombres':
                        $_SESSION["formulario"]["datos"]["ctresacnthombres"] = intval(trim($valor));
                        break;

                    case 'ctresapertgremio':
                        $_SESSION["formulario"]["datos"]["ctresapertgremio"] = strtoupper(trim($valor));
                        break;

                    case 'ctresagremio':
                        $_SESSION["formulario"]["datos"]["ctresagremio"] = strtoupper(trim($valor));
                        break;

                    case 'ctresaacredita':
                        $_SESSION["formulario"]["datos"]["ctresaacredita"] = strtoupper(trim($valor));
                        break;

                    case 'ctresaivc':
                        $_SESSION["formulario"]["datos"]["ctresaivc"] = strtoupper(trim($valor));
                        break;

                    case 'ctresainfoivc':
                        $_SESSION["formulario"]["datos"]["ctresainfoivc"] = strtoupper(trim($valor));
                        break;

                    case 'ctresaautregistro':
                        $_SESSION["formulario"]["datos"]["ctresaautregistro"] = strtoupper(trim($valor));
                        break;

                    case 'ctresaentautoriza':
                        $_SESSION["formulario"]["datos"]["ctresaentautoriza"] = strtoupper(trim($valor));
                        break;

                    case 'ctresadiscap':
                        $_SESSION["formulario"]["datos"]["ctresadiscap"] = strtoupper(trim($valor));
                        break;

                    case 'ctresaetnia':
                        $_SESSION["formulario"]["datos"]["ctresaetnia"] = strtoupper(trim($valor));
                        break;

                    case 'ctresacualetnia':
                        $_SESSION["formulario"]["datos"]["ctresacualetnia"] = strtoupper(trim($valor));
                        break;

                    case 'ctresadespvictreins':
                        $_SESSION["formulario"]["datos"]["ctresadespvictreins"] = strtoupper(trim($valor));
                        break;

                    case 'ctresacualdespvictreins':
                        $_SESSION["formulario"]["datos"]["ctresacualdespvictreins"] = strtoupper(trim($valor));
                        break;

                    case 'ctresaindgest':
                        $_SESSION["formulario"]["datos"]["ctresaindgest"] = strtoupper(trim($valor));
                        break;

                    case 'ctresalgbti':
                        $_SESSION["formulario"]["datos"]["ctresalgbti"] = strtoupper(trim($valor));
                        break;

                    //
                    case 'cantest':
                        $_SESSION["formulario"]["datos"]["cantest"] = intval(trim($valor));
                        break;

                    case 'tamanoempresa':
                        $_SESSION["formulario"]["datos"]["tamanoempresa"] = $valor;
                        break;

                    case 'emprendedor28':
                        $_SESSION["formulario"]["datos"]["emprendedor28"] = strtoupper($valor);
                        break;

                    case 'vigcontrol':
                        $_SESSION["formulario"]["datos"]["vigcontrol"] = $valor;
                        break;

                    case 'pemprendedor28':
                        $_SESSION["formulario"]["datos"]["pemprendedor28"] = doubleval($valor);
                        break;

                    case 'tipopropiedad':
                        $_SESSION["formulario"]["datos"]["tipopropiedad"] = $valor;
                        break;

                    case 'tipolocal':
                        $_SESSION["formulario"]["datos"]["tipolocal"] = $valor;
                        break;

                    // Direccion comercial 
                    case 'dircom':
                        $_SESSION["formulario"]["datos"]["dircom"] = trim($valor);
                        break;

                    case 'dircom_tipovia':
                        $_SESSION["formulario"]["datos"]["dircom_tipovia"] = strtoupper(trim($valor));
                        break;

                    case 'dircom_numvia':
                        $_SESSION["formulario"]["datos"]["dircom_numvia"] = strtoupper(trim($valor));
                        break;

                    case 'dircom_apevia':
                        $_SESSION["formulario"]["datos"]["dircom_apevia"] = strtoupper(trim($valor));
                        break;

                    case 'dircom_orivia':
                        $_SESSION["formulario"]["datos"]["dircom_orivia"] = strtoupper(trim($valor));
                        break;

                    case 'dircom_numcruce':
                        $_SESSION["formulario"]["datos"]["dircom_numcruce"] = strtoupper(trim($valor));
                        break;

                    case 'dircom_apecruce':
                        $_SESSION["formulario"]["datos"]["dircom_apecruce"] = strtoupper(trim($valor));
                        break;

                    case 'dircom_oricruce':
                        $_SESSION["formulario"]["datos"]["dircom_oricruce"] = strtoupper(trim($valor));
                        break;

                    case 'dircom_numplaca':
                        $_SESSION["formulario"]["datos"]["dircom_numplaca"] = strtoupper(trim($valor));
                        break;

                    case 'dircom_complemento':
                        $_SESSION["formulario"]["datos"]["dircom_complemento"] = trim($valor);
                        break;

                    case 'muncom':
                        $_SESSION["formulario"]["datos"]["muncom"] = $valor;
                        break;

                    case 'paicom':
                        $_SESSION["formulario"]["datos"]["paicom"] = $valor;
                        break;

                    case 'telcom1':
                        $_SESSION["formulario"]["datos"]["telcom1"] = $valor;
                        break;

                    case 'telcom2':
                        $_SESSION["formulario"]["datos"]["telcom2"] = $valor;
                        break;

                    case 'celcom':
                        $_SESSION["formulario"]["datos"]["celcom"] = $valor;
                        break;

                    case 'ctrmen':
                        $_SESSION["formulario"]["datos"]["ctrmen"] = $valor;
                        break;

                    case 'faxcom':
                        $_SESSION["formulario"]["datos"]["faxcom"] = $valor;
                        break;

                    case 'aacom':
                        $_SESSION["formulario"]["datos"]["aacom"] = $valor;
                        break;

                    case 'zonapostalcom':
                        $_SESSION["formulario"]["datos"]["zonapostalcom"] = $valor;
                        break;

                    case 'barriocom':
                        $_SESSION["formulario"]["datos"]["barriocom"] = $valor;
                        break;

                    case 'numpredial':
                        $_SESSION["formulario"]["datos"]["numpredial"] = $valor;
                        break;

                    case 'codigopostalcom':
                        $_SESSION["formulario"]["datos"]["codigopostalcom"] = strtoupper(trim($valor));
                        break;

                    case 'codigozonacom':
                        $_SESSION["formulario"]["datos"]["codigozonacom"] = strtoupper(trim($valor));
                        break;

                    case 'emailcom':
                        $_SESSION["formulario"]["datos"]["emailcom"] = strtolower(trim($valor));
                        break;

                    case 'urlcom':
                        $_SESSION["formulario"]["datos"]["urlcom"] = strtolower(trim($valor));
                        break;

                    case 'tiposedeadm':
                        $_SESSION["formulario"]["datos"]["tiposedeadm"] = strtoupper(trim($valor));
                        break;

                    case 'ctrubi':
                        $_SESSION["formulario"]["datos"]["ctrubi"] = $valor;
                        break;

                    case 'ctrfun':
                        $_SESSION["formulario"]["datos"]["ctrfun"] = $valor;
                        break;

                    // Dirección de notificación
                    case 'dirnot':
                        $_SESSION["formulario"]["datos"]["dirnot"] = trim($valor);
                        break;

                    case 'dirnot_tipovia':
                        $_SESSION["formulario"]["datos"]["dirnot_tipovia"] = strtoupper(trim($valor));
                        break;

                    case 'dirnot_numvia':
                        $_SESSION["formulario"]["datos"]["dirnot_numvia"] = strtoupper(trim($valor));
                        break;

                    case 'dirnot_apevia':
                        $_SESSION["formulario"]["datos"]["dirnot_apevia"] = strtoupper(trim($valor));
                        break;

                    case 'dirnot_orivia':
                        $_SESSION["formulario"]["datos"]["dirnot_orivia"] = strtoupper(trim($valor));
                        break;

                    case 'dirnot_numcruce':
                        $_SESSION["formulario"]["datos"]["dirnot_numcruce"] = strtoupper(trim($valor));
                        break;

                    case 'dirnot_apecruce':
                        $_SESSION["formulario"]["datos"]["dirnot_apecruce"] = strtoupper(trim($valor));
                        break;

                    case 'dirnot_oricruce':
                        $_SESSION["formulario"]["datos"]["dirnot_oricruce"] = strtoupper(trim($valor));
                        break;

                    case 'dirnot_numplaca':
                        $_SESSION["formulario"]["datos"]["dirnot_numplaca"] = strtoupper(trim($valor));
                        break;

                    case 'dirnot_complemento':
                        $_SESSION["formulario"]["datos"]["dirnot_complemento"] = trim($valor);
                        break;

                    case 'munnot':
                        $_SESSION["formulario"]["datos"]["munnot"] = $valor;
                        break;

                    case 'painot':
                        $_SESSION["formulario"]["datos"]["painot"] = $valor;
                        break;

                    case 'telnot':
                        $_SESSION["formulario"]["datos"]["telnot"] = $valor;
                        break;

                    case 'telnot2':
                        $_SESSION["formulario"]["datos"]["telnot2"] = $valor;
                        break;

                    case 'celnot':
                        $_SESSION["formulario"]["datos"]["celnot"] = $valor;
                        break;

                    case 'faxnot':
                        $_SESSION["formulario"]["datos"]["faxnot"] = $valor;
                        break;

                    case 'aanot':
                        $_SESSION["formulario"]["datos"]["aanot"] = $valor;
                        break;

                    case 'zonapostalnot':
                        $_SESSION["formulario"]["datos"]["zonapostalnot"] = $valor;
                        break;

                    case 'barrionot':
                        $_SESSION["formulario"]["datos"]["barrionot"] = $valor;
                        break;

                    case 'emailnot':
                        $_SESSION["formulario"]["datos"]["emailnot"] = strtolower(trim($valor));
                        break;

                    case 'urlnot':
                        $_SESSION["formulario"]["datos"]["urlnot"] = strtolower(trim($valor));
                        break;

                    case 'codigopostalnot':
                        $_SESSION["formulario"]["datos"]["codigopostalnot"] = strtoupper(trim($valor));
                        break;

                    case 'codigozonanot':
                        $_SESSION["formulario"]["datos"]["codigozonanot"] = strtoupper(trim($valor));
                        break;

                    // Contension administrativo
                    case 'ctrmennot':
                        $_SESSION["formulario"]["datos"]["ctrmennot"] = $valor;
                        break;

                    // Composicion del capital
                    case 'cap_porcnaltot':
                        $_SESSION["formulario"]["datos"]["cap_porcnaltot"] = doubleval(\funcionesGenerales::retornarNumeroLimpio($valor));
                        break;

                    case 'cap_porcnalpri':
                        $_SESSION["formulario"]["datos"]["cap_porcnalpri"] = doubleval(\funcionesGenerales::retornarNumeroLimpio($valor));
                        break;

                    case 'cap_porcnalpub':
                        $_SESSION["formulario"]["datos"]["cap_porcnalpub"] = doubleval(\funcionesGenerales::retornarNumeroLimpio($valor));
                        break;

                    case 'cap_porcexttot':
                        $_SESSION["formulario"]["datos"]["cap_porcexttot"] = doubleval(\funcionesGenerales::retornarNumeroLimpio($valor));
                        break;

                    case 'cap_porcextpri':
                        $_SESSION["formulario"]["datos"]["cap_porcextpri"] = doubleval(\funcionesGenerales::retornarNumeroLimpio($valor));
                        break;

                    case 'cap_porcextpub':
                        $_SESSION["formulario"]["datos"]["cap_porcextpub"] = doubleval(\funcionesGenerales::retornarNumeroLimpio($valor));
                        break;

                    // Beneficios
                    case 'art4':
                        $_SESSION["formulario"]["datos"]["art4"] = trim($valor);
                        break;

                    case 'art7':
                        $_SESSION["formulario"]["datos"]["art7"] = trim($valor);
                        break;

                    case 'art50':
                        $_SESSION["formulario"]["datos"]["art50"] = trim($valor);
                        break;

                    case 'ctrcancelacion1429':
                        $_SESSION["formulario"]["datos"]["ctrcancelacion1429"] = trim($valor);
                        break;

                    case 'benley1780':
                        $_SESSION["formulario"]["datos"]["benley1780"] = trim($valor);
                        break;

                    case 'cumplerequisitos1780':
                        $_SESSION["formulario"]["datos"]["cumplerequisitos1780"] = trim($valor);
                        break;

                    case 'cumplerequisitos1780primren':
                        $_SESSION["formulario"]["datos"]["cumplerequisitos1780primren"] = trim($valor);
                        break;

                    case 'renunciabeneficios1780':
                        $_SESSION["formulario"]["datos"]["renunciabeneficios1780"] = trim($valor);
                        break;

                    case 'ctrdepuracion1727':
                        $_SESSION["formulario"]["datos"]["ctrdepuracion1727"] = trim($valor);
                        break;

                    case 'ctrfechadepuracion1727':
                        $_SESSION["formulario"]["datos"]["renunciabeneficios1780"] = str_replace(array("/", "-"), "", $valor);
                        break;

                    // Actividad económica
                    case 'desactiv':
                        $_SESSION["formulario"]["datos"]["desactiv"] = trim($valor);
                        break;

                    case 'ciiu1':
                        $_SESSION["formulario"]["datos"]["ciius"][1] = $valor;
                        break;

                    case 'ciiu2':
                        $_SESSION["formulario"]["datos"]["ciius"][2] = $valor;
                        break;

                    case 'ciiu3':
                        $_SESSION["formulario"]["datos"]["ciius"][3] = $valor;
                        break;

                    case 'ciiu4':
                        $_SESSION["formulario"]["datos"]["ciius"][4] = $valor;
                        break;

                    case 'feciniact1':
                        $_SESSION["formulario"]["datos"]["feciniact1"] = str_replace(array("-", "/"), "", $valor);
                        break;

                    case 'feciniact2':
                        $_SESSION["formulario"]["datos"]["feciniact2"] = str_replace(array("-", "/"), "", $valor);
                        break;

                    case 'shd1':
                        $_SESSION["formulario"]["datos"]["shd"][1] = $valor;
                        break;

                    case 'shd2':
                        $_SESSION["formulario"]["datos"]["shd"][2] = $valor;
                        break;

                    case 'shd3':
                        $_SESSION["formulario"]["datos"]["shd"][3] = $valor;
                        break;

                    case 'shd4':
                        $_SESSION["formulario"]["datos"]["shd"][4] = $valor;
                        break;

                    case 'maying1':
                        if ($valor == 'S') {
                            if ($_SESSION["formulario"]["datos"]["ciius"][1] != '') {
                                $_SESSION["formulario"]["datos"]["maying1"] = 'S';
                                $_SESSION["formulario"]["datos"]["ciiutamanoempresarial"] = $_SESSION["formulario"]["datos"]["ciius"][1];
                            }
                        } else {
                            $_SESSION["formulario"]["datos"]["maying1"] = 'N';
                        }
                        break;

                    case 'maying2':
                        if ($valor == 'S') {
                            if ($_SESSION["formulario"]["datos"]["ciius"][2] != '') {
                                $_SESSION["formulario"]["datos"]["maying2"] = 'S';
                                $_SESSION["formulario"]["datos"]["ciiutamanoempresarial"] = $_SESSION["formulario"]["datos"]["ciius"][2];
                            }
                        } else {
                            $_SESSION["formulario"]["datos"]["maying2"] = 'N';
                        }
                        break;

                    case 'maying3':
                        if ($valor == 'S') {
                            if ($_SESSION["formulario"]["datos"]["ciius"][3] != '') {
                                $_SESSION["formulario"]["datos"]["maying3"] = 'S';
                                $_SESSION["formulario"]["datos"]["ciiutamanoempresarial"] = $_SESSION["formulario"]["datos"]["ciius"][3];
                            }
                        } else {
                            $_SESSION["formulario"]["datos"]["maying3"] = 'N';
                        }
                        break;

                    case 'maying4':
                        if ($valor == 'S') {
                            if ($_SESSION["formulario"]["datos"]["ciius"][4] != '') {
                                $_SESSION["formulario"]["datos"]["maying4"] = 'S';
                                $_SESSION["formulario"]["datos"]["ciiutamanoempresarial"] = $_SESSION["formulario"]["datos"]["ciius"][4];
                            }
                        } else {
                            $_SESSION["formulario"]["datos"]["maying4"] = 'N';
                        }
                        break;

                    // Adicionales
                    case 'empresafamiliar':
                        $_SESSION["formulario"]["datos"]["empresafamiliar"] = strtoupper(trim($valor));
                        break;

                    case 'procesosinnovacion':
                        $_SESSION["formulario"]["datos"]["procesosinnovacion"] = strtoupper(trim($valor));
                        break;

                    case 'impexp':
                        $_SESSION["formulario"]["datos"]["impexp"] = $valor;
                        break;

                    case 'codaduaneros':
                        $_SESSION["formulario"]["datos"]["codaduaneros"] = strtoupper(trim($valor));
                        break;

                    // Afiliacion
                    case 'afiliado':
                        $_SESSION["formulario"]["datos"]["afiliado"] = $valor;
                        break;

                    case 'fechaafiliacion':
                        $_SESSION["formulario"]["datos"]["fechaafiliacion"] = str_replace(array("/", "-"), "", $valor);
                        break;

                    case 'fecactaaflia':
                        $_SESSION["formulario"]["datos"]["fecactaaflia"] = str_replace(array("/", "-"), "", $valor);
                        break;

                    case 'numactaaflia':
                        $_SESSION["formulario"]["datos"]["numactaaflia"] = $valor;
                        break;

                    case 'ultanorenafi':
                        $_SESSION["formulario"]["datos"]["ultanorenafi"] = $valor;
                        break;

                    case 'fechaultpagoafi':
                        $_SESSION["formulario"]["datos"]["fechaultpagoafi"] = str_replace(array("/", "-"), "", $valor);
                        break;

                    case 'valorultpagoafi':
                        $_SESSION["formulario"]["datos"]["valorultpagoafi"] = doubleval($valor);
                        break;

                    case 'saldoafiliado':
                        $_SESSION["formulario"]["datos"]["saldoafiliado"] = doubleval($valor);
                        break;

                    case 'fecexafiliacion':
                        $_SESSION["formulario"]["datos"]["fecexafiliacion"] = str_replace(array("/", "-"), "", $valor);
                        break;

                    case 'fecactaafliacan':
                        $_SESSION["formulario"]["datos"]["fecactaafliacan"] = str_replace(array("/", "-"), "", $valor);
                        break;

                    case 'numactaafliacan':
                        $_SESSION["formulario"]["datos"]["numactaafliacan"] = $valor;
                        break;

                    case 'motivodesafiliacion':
                        $_SESSION["formulario"]["datos"]["motivodesafiliacion"] = $valor;
                        break;

                    case 'txtmotivodesafiliacion':
                        $_SESSION["formulario"]["datos"]["txtmotivodesafiliacion"] = trim($valor);
                        break;

                    // Aportes seguridad social
                    case 'aportantesegsocial':
                        $_SESSION["formulario"]["datos"]["aportantesegsocial"] = strtoupper(trim($valor));
                        break;

                    case 'tipoaportantesegsocial':
                        $_SESSION["formulario"]["datos"]["tipoaportantesegsocial"] = strtoupper(trim($valor));
                        break;

                    // Casa principál
                    case 'cpcodcam':
                        $_SESSION["formulario"]["datos"]["cpcodcam"] = sprintf("%02s", strtoupper(trim($valor)));
                        break;

                    case 'cpnummat':
                        $_SESSION["formulario"]["datos"]["cpnummat"] = strtoupper(trim($valor));
                        break;

                    case 'cpnumnit':
                        $_SESSION["formulario"]["datos"]["cpnumnit"] = str_replace(array("-", "/", ",", "."), "", $valor);
                        break;

                    case 'cprazsoc':
                        $_SESSION["formulario"]["datos"]["cprazsoc"] = strtoupper($valor);
                        break;

                    case 'cpdircom':
                        $_SESSION["formulario"]["datos"]["cpdircom"] = strtoupper(trim($valor));
                        break;

                    case 'cpdirnot':
                        $_SESSION["formulario"]["datos"]["cpdirnot"] = strtoupper(trim($valor));
                        break;

                    case 'cpcodmun':
                        $_SESSION["formulario"]["datos"]["cpcodmun"] = strtoupper(trim($valor));
                        break;

                    case 'cpmunnot':
                        $_SESSION["formulario"]["datos"]["cpmunnot"] = strtoupper(trim($valor));
                        break;

                    case 'cpnumtel':
                        $_SESSION["formulario"]["datos"]["cpnumtel"] = strtoupper(trim($valor));
                        break;

                    case 'cpmunnot':
                        $_SESSION["formulario"]["datos"]["cpmunnot"] = strtoupper(trim($valor));
                        break;

                    case 'cptirepleg':
                        $_SESSION["formulario"]["datos"]["cptirepleg"] = strtoupper(trim($valor));
                        break;

                    case 'cpirepleg':
                        $_SESSION["formulario"]["datos"]["cpirepleg"] = strtoupper(trim($valor));
                        break;

                    case 'cpnrepleg':
                        $_SESSION["formulario"]["datos"]["cpnrepleg"] = strtoupper(trim($valor));
                        break;

                    case 'cptelrepleg':
                        $_SESSION["formulario"]["datos"]["cptelrepleg"] = strtoupper(trim($valor));
                        break;

                    case 'cpemailrepleg':
                        $_SESSION["formulario"]["datos"]["cpemailrepleg"] = (trim($valor));
                        break;

                    // Información financiera
                    case 'anodatos':
                        $_SESSION["formulario"]["datos"]["anodatos"] = doubleval($valor);
                        break;

                    case 'fechadatos':
                        $_SESSION["formulario"]["datos"]["fechadatos"] = str_replace(array("/", "-"), "", $valor);
                        break;

                    case 'actvin':
                        $_SESSION["formulario"]["datos"]["actvin"] = doubleval(\funcionesGenerales::retornarNumeroLimpio($valor));
                        break;

                    case 'actcte':
                        $_SESSION["formulario"]["datos"]["actcte"] = doubleval(\funcionesGenerales::retornarNumeroLimpio($valor));
                        break;

                    case 'actnocte':
                        $_SESSION["formulario"]["datos"]["actnocte"] = doubleval(\funcionesGenerales::retornarNumeroLimpio($valor));
                        break;

                    case 'acttot':
                        $_SESSION["formulario"]["datos"]["acttot"] = doubleval(\funcionesGenerales::retornarNumeroLimpio($valor));
                        break;

                    case 'pascte':
                        $_SESSION["formulario"]["datos"]["pascte"] = doubleval(\funcionesGenerales::retornarNumeroLimpio($valor));
                        break;

                    case 'paslar':
                        $_SESSION["formulario"]["datos"]["paslar"] = doubleval(\funcionesGenerales::retornarNumeroLimpio($valor));
                        break;

                    case 'pastot':
                        $_SESSION["formulario"]["datos"]["pastot"] = doubleval(\funcionesGenerales::retornarNumeroLimpio($valor));
                        break;

                    case 'pattot':
                        $_SESSION["formulario"]["datos"]["pattot"] = doubleval(\funcionesGenerales::retornarNumeroLimpio($valor));
                        break;

                    case 'paspat':
                        $_SESSION["formulario"]["datos"]["paspat"] = doubleval(\funcionesGenerales::retornarNumeroLimpio($valor));
                        break;

                    case 'balsoc':
                        $_SESSION["formulario"]["datos"]["balsoc"] = doubleval(\funcionesGenerales::retornarNumeroLimpio($valor));
                        break;

                    case 'ingope':
                        $_SESSION["formulario"]["datos"]["ingope"] = doubleval(\funcionesGenerales::retornarNumeroLimpio($valor));
                        break;

                    case 'ingnoope':
                        $_SESSION["formulario"]["datos"]["ingnoope"] = doubleval(\funcionesGenerales::retornarNumeroLimpio($valor));
                        break;

                    case 'cosven':
                        $_SESSION["formulario"]["datos"]["cosven"] = doubleval(\funcionesGenerales::retornarNumeroLimpio($valor));
                        break;

                    case 'gtoven':
                        $_SESSION["formulario"]["datos"]["gtoven"] = doubleval(\funcionesGenerales::retornarNumeroLimpio($valor));
                        break;

                    case 'gtoadm':
                        $_SESSION["formulario"]["datos"]["gtoadm"] = doubleval(\funcionesGenerales::retornarNumeroLimpio($valor));
                        break;

                    case 'gasimp':
                        $_SESSION["formulario"]["datos"]["gasimp"] = doubleval(\funcionesGenerales::retornarNumeroLimpio($valor));
                        break;

                    case 'utiope':
                        $_SESSION["formulario"]["datos"]["utiope"] = doubleval(\funcionesGenerales::retornarNumeroLimpio($valor));
                        break;

                    case 'utinet':
                        $_SESSION["formulario"]["datos"]["utinet"] = doubleval(\funcionesGenerales::retornarNumeroLimpio($valor));
                        break;

                    case 'personal':
                        $_SESSION["formulario"]["datos"]["personal"] = intval(\funcionesGenerales::retornarNumeroLimpio($valor));
                        break;

                    case 'personaltemp':
                        $_SESSION["formulario"]["datos"]["personaltemp"] = doubleval(\funcionesGenerales::retornarNumeroLimpio($valor));
                        break;

                    case 'gruponiif':
                        $_SESSION["formulario"]["datos"]["gruponiif"] = trim($valor);
                        break;

                    case 'ingesperados':
                        $_SESSION["formulario"]["datos"]["ingesperados"] = doubleval(\funcionesGenerales::retornarNumeroLimpio($valor));
                        break;

                    case 'cantidadmujeres':
                        $_SESSION["formulario"]["datos"]["cantidadmujeres"] = intval(\funcionesGenerales::retornarNumeroLimpio($valor));
                        break;

                    case 'cantidadmujerescargosdirectivos':
                        $_SESSION["formulario"]["datos"]["cantidadmujerescargosdirectivos"] = intval(\funcionesGenerales::retornarNumeroLimpio($valor));
                        break;

                    case 'participacionmujeres':
                        $_SESSION["formulario"]["datos"]["participacionmujeres"] = intval(\funcionesGenerales::retornarNumeroLimpio($valor));
                        break;

                    // referencias comerciales
                    case 'refcrenom1':
                        $_SESSION["formulario"]["datos"]["refcrenom1"] = $valor;
                        break;

                    case 'refcreofi1':
                        $_SESSION["formulario"]["datos"]["refcreofi1"] = $valor;
                        break;

                    case 'refcretel1':
                        $_SESSION["formulario"]["datos"]["refcretel1"] = $valor;
                        break;

                    case 'refcrenom2':
                        $_SESSION["formulario"]["datos"]["refcrenom2"] = $valor;
                        break;

                    case 'refcreofi2':
                        $_SESSION["formulario"]["datos"]["refcreofi2"] = $valor;
                        break;

                    case 'refcretel2':
                        $_SESSION["formulario"]["datos"]["refcretel2"] = $valor;
                        break;

                    case 'refcomnom1':
                        $_SESSION["formulario"]["datos"]["refcomnom1"] = $valor;
                        break;

                    case 'refcomdir1':
                        $_SESSION["formulario"]["datos"]["refcomdir1"] = $valor;
                        break;

                    case 'refcomtel1':
                        $_SESSION["formulario"]["datos"]["refcomtel1"] = $valor;
                        break;

                    case 'refcomnom2':
                        $_SESSION["formulario"]["datos"]["refcomnom2"] = $valor;
                        break;

                    case 'refcomdir2':
                        $_SESSION["formulario"]["datos"]["refcomdir2"] = $valor;
                        break;

                    case 'refcomtel2':
                        $_SESSION["formulario"]["datos"]["refcomtel2"] = $valor;
                        break;

                    // Aportes
                    case 'apolab':
                        $_SESSION["formulario"]["datos"]["apolab"] = doubleval(\funcionesGenerales::retornarNumeroLimpio($valor));
                        break;

                    case 'apolabadi':
                        $_SESSION["formulario"]["datos"]["apolabadi"] = doubleval(\funcionesGenerales::retornarNumeroLimpio($valor));
                        break;

                    case 'apodin':
                        $_SESSION["formulario"]["datos"]["apodin"] = doubleval(\funcionesGenerales::retornarNumeroLimpio($valor));
                        break;

                    case 'apoact':
                        $_SESSION["formulario"]["datos"]["apoact"] = doubleval(\funcionesGenerales::retornarNumeroLimpio($valor));
                        break;

                    case 'apotot':
                        $_SESSION["formulario"]["datos"]["apotot"] = doubleval(\funcionesGenerales::retornarNumeroLimpio($valor));
                        break;

                    // IVC
                    case 'ivcarea':
                        $_SESSION["formulario"]["datos"]["ivcarea"] = doubleval(\funcionesGenerales::retornarNumeroLimpio($valor));
                        break;

                    case 'ivcver':
                        $_SESSION["formulario"]["datos"]["ivcver"] = $valor;
                        break;

                    case 'ivccretip':
                        $_SESSION["formulario"]["datos"]["ivccretip"] = $valor;
                        break;

                    case 'ivcqui':
                        $_SESSION["formulario"]["datos"]["ivcqui"] = $valor;
                        break;

                    case 'ivcali':
                        $_SESSION["formulario"]["datos"]["ivcali"] = $valor;
                        break;

                    case 'tituloorganodirectivo':
                        $_SESSION["formulario"]["datos"]["tituloorganodirectivo"] = $valor;
                        break;

                    case 'siglaenconstitucion':
                        $_SESSION["formulario"]["datos"]["siglaenconstitucion"] = $valor;
                        break;

                    case 'domicilio_ong':
                        $_SESSION["formulario"]["datos"]["domicilio_ong"] = strtoupper($valor);
                        break;

                    // Propietario
                    case 'prop_1_organizacionpropietario':
                        $_SESSION["formulario"]["datos"]["propietarios"][1]["organizacionpropietario"] = $valor;
                        break;

                    case 'prop_1_camarapropietario':
                        $_SESSION["formulario"]["datos"]["propietarios"][1]["camarapropietario"] = sprintf("%02s", $valor);
                        break;

                    case 'prop_1_matriculapropietario':
                        $_SESSION["formulario"]["datos"]["propietarios"][1]["matriculapropietario"] = ltrim($valor, "0");
                        break;

                    case 'prop_1_idtipoidentificacionpropietario':
                        $_SESSION["formulario"]["datos"]["propietarios"][1]["idtipoidentificacionpropietario"] = $valor;
                        break;

                    case 'prop_1_identificacionpropietario':
                        $_SESSION["formulario"]["datos"]["propietarios"][1]["identificacionpropietario"] = ltrim($valor, "0");
                        break;

                    case 'prop_1_nitpropietario':
                        $_SESSION["formulario"]["datos"]["propietarios"][1]["nitpropietario"] = ltrim($valor, "0");
                        break;

                    case 'prop_1_nombrepropietario':
                        $_SESSION["formulario"]["datos"]["propietarios"][1]["nombrepropietario"] = strtoupper($valor);
                        break;

                    case 'prop_1_direccionpropietario':
                        $_SESSION["formulario"]["datos"]["propietarios"][1]["direccionpropietario"] = strtoupper($valor);
                        break;

                    case 'prop_1_municipiopropietario':
                        $_SESSION["formulario"]["datos"]["propietarios"][1]["municipiopropietario"] = strtoupper($valor);
                        break;

                    case 'prop_1_direccionnotpropietario':
                        $_SESSION["formulario"]["datos"]["propietarios"][1]["direccionnotpropietario"] = strtoupper($valor);
                        break;

                    case 'prop_1_municipionotpropietario':
                        $_SESSION["formulario"]["datos"]["propietarios"][1]["municipionotpropietario"] = strtoupper($valor);
                        break;

                    case 'prop_1_telefonopropietario':
                        $_SESSION["formulario"]["datos"]["propietarios"][1]["telefonopropietario"] = strtoupper($valor);
                        break;

                    case 'prop_1_telefono2propietario':
                        $_SESSION["formulario"]["datos"]["propietarios"][1]["telefono2propietario"] = strtoupper($valor);
                        break;

                    case 'prop_1_celularpropietario':
                        $_SESSION["formulario"]["datos"]["propietarios"][1]["celularpropietario"] = strtoupper($valor);
                        break;

                    case 'prop_1_nomreplegpropietario':
                        $_SESSION["formulario"]["datos"]["propietarios"][1]["nomreplegpropietario"] = strtoupper($valor);
                        break;

                    case 'prop_1_tipoidreplegpropietario':
                        $_SESSION["formulario"]["datos"]["propietarios"][1]["tipoidreplegpropietario"] = strtoupper($valor);
                        break;

                    case 'prop_1_numidreplegpropietario':
                        $_SESSION["formulario"]["datos"]["propietarios"][1]["numidreplegpropietario"] = strtoupper($valor);
                        break;

                    case 'prop_1_ultanorenpropietario':
                        $_SESSION["formulario"]["datos"]["propietarios"][1]["ultanorenpropietario"] = strtoupper($valor);
                        break;
                }

                if (substr($key, 0, 9) == 'anodatos_') {
                    $anox = substr($key, 9, 4);
                    $_SESSION["formulario"]["datos"]["f"][$anox]["anodatos"] = $valor;
                    if ($anox == $_SESSION["formulario"]["anorenovar"]) {
                        $_SESSION["formulario"]["datos"]["anodatos"] = $valor;
                    }
                }
                if (substr($key, 0, 11) == 'fechadatos_') {
                    $anox = substr($key, 11, 4);
                    $_SESSION["formulario"]["datos"]["f"][$anox]["fechadatos"] = str_replace(array("-", "/"), "", $valor);
                    if ($anox == $_SESSION["formulario"]["anorenovar"]) {
                        $_SESSION["formulario"]["datos"]["fechadatos"] = str_replace(array(",", "/"), "", $valor);
                    }
                }
                if (substr($key, 0, 9) == 'personal_') {
                    $anox = substr($key, 9, 4);
                    $_SESSION["formulario"]["datos"]["f"][$anox]["personal"] = intval(\funcionesGenerales::retornarNumeroLimpio($valor));
                    if ($anox == $_SESSION["formulario"]["anorenovar"]) {
                        $_SESSION["formulario"]["datos"]["personal"] = intval(\funcionesGenerales::retornarNumeroLimpio($valor));
                    }
                }
                if (substr($key, 0, 13) == 'personaltemp_') {
                    $anox = substr($key, 13, 4);
                    $_SESSION["formulario"]["datos"]["f"][$anox]["personaltemp"] = doubleval(\funcionesGenerales::retornarNumeroLimpio($valor));
                    if ($anox == $_SESSION["formulario"]["anorenovar"]) {
                        $_SESSION["formulario"]["datos"]["personaltemp"] = doubleval(\funcionesGenerales::retornarNumeroLimpio($valor));
                    }
                }
                if (substr($key, 0, 7) == 'actvin_') {
                    $anox = substr($key, 7, 4);
                    $_SESSION["formulario"]["datos"]["f"][$anox]["actvin"] = doubleval(\funcionesGenerales::retornarNumeroLimpio($valor));
                    if ($anox == $_SESSION["formulario"]["anorenovar"]) {
                        $_SESSION["formulario"]["datos"]["actvin"] = doubleval(\funcionesGenerales::retornarNumeroLimpio($valor));
                    }
                }
                if (substr($key, 0, 7) == 'actcte_') {
                    $anox = substr($key, 7, 4);
                    $_SESSION["formulario"]["datos"]["f"][$anox]["actcte"] = doubleval(\funcionesGenerales::retornarNumeroLimpio($valor));
                    if ($anox == $_SESSION["formulario"]["anorenovar"]) {
                        $_SESSION["formulario"]["datos"]["actcte"] = doubleval(\funcionesGenerales::retornarNumeroLimpio($valor));
                    }
                }
                if (substr($key, 0, 9) == 'actnocte_') {
                    $anox = substr($key, 9, 4);
                    $_SESSION["formulario"]["datos"]["f"][$anox]["actnocte"] = doubleval(\funcionesGenerales::retornarNumeroLimpio($valor));
                    if ($anox == $_SESSION["formulario"]["anorenovar"]) {
                        $_SESSION["formulario"]["datos"]["actnocte"] = doubleval(\funcionesGenerales::retornarNumeroLimpio($valor));
                    }
                }

                if (substr($key, 0, 7) == 'actfij_') {
                    $anox = substr($key, 7, 4);
                    $_SESSION["formulario"]["datos"]["f"][$anox]["actfij"] = doubleval(\funcionesGenerales::retornarNumeroLimpio($valor));
                    if ($anox == $_SESSION["formulario"]["anorenovar"]) {
                        $_SESSION["formulario"]["datos"]["actfij"] = doubleval(\funcionesGenerales::retornarNumeroLimpio($valor));
                    }
                }
                if (substr($key, 0, 7) == 'fijnet_') {
                    $anox = substr($key, 7, 4);
                    $_SESSION["formulario"]["datos"]["f"][$anox]["fijnet"] = doubleval(\funcionesGenerales::retornarNumeroLimpio($valor));
                    if ($anox == $_SESSION["formulario"]["anorenovar"]) {
                        $_SESSION["formulario"]["datos"]["fijnet"] = doubleval(\funcionesGenerales::retornarNumeroLimpio($valor));
                    }
                }
                if (substr($key, 0, 7) == 'actotr_') {
                    $anox = substr($key, 7, 4);
                    $_SESSION["formulario"]["datos"]["f"][$anox]["actotr"] = doubleval(\funcionesGenerales::retornarNumeroLimpio($valor));
                    if ($anox == $_SESSION["formulario"]["anorenovar"]) {
                        $_SESSION["formulario"]["datos"]["actotr"] = doubleval(\funcionesGenerales::retornarNumeroLimpio($valor));
                    }
                }
                if (substr($key, 0, 7) == 'actval_') {
                    $anox = substr($key, 7, 4);
                    $_SESSION["formulario"]["datos"]["f"][$anox]["actval"] = doubleval(\funcionesGenerales::retornarNumeroLimpio($valor));
                    if ($anox == $_SESSION["formulario"]["anorenovar"]) {
                        $_SESSION["formulario"]["datos"]["actval"] = doubleval(\funcionesGenerales::retornarNumeroLimpio($valor));
                    }
                }
                if (substr($key, 0, 7) == 'acttot_') {
                    $anox = substr($key, 7, 4);
                    $_SESSION["formulario"]["datos"]["f"][$anox]["acttot"] = doubleval(\funcionesGenerales::retornarNumeroLimpio($valor));
                    $_SESSION["formulario"]["datos"]["f"][$anox]["actsinaju"] = 0;
                    if ($anox == $_SESSION["formulario"]["anorenovar"]) {
                        $_SESSION["formulario"]["datos"]["acttot"] = doubleval(\funcionesGenerales::retornarNumeroLimpio($valor));
                        $_SESSION["formulario"]["datos"]["actsinaju"] = 0;
                    }
                }
                if (substr($key, 0, 7) == 'invent_') {
                    $anox = substr($key, 7, 4);
                    $_SESSION["formulario"]["datos"]["f"][$anox]["invent"] = doubleval(\funcionesGenerales::retornarNumeroLimpio($valor));
                    if ($anox == $_SESSION["formulario"]["anorenovar"]) {
                        $_SESSION["formulario"]["datos"]["invent"] = doubleval(\funcionesGenerales::retornarNumeroLimpio($valor));
                    }
                }
                if (substr($key, 0, 7) == 'pascte_') {
                    $anox = substr($key, 7, 4);
                    $_SESSION["formulario"]["datos"]["f"][$anox]["pascte"] = doubleval(\funcionesGenerales::retornarNumeroLimpio($valor));
                    if ($anox == $_SESSION["formulario"]["anorenovar"]) {
                        $_SESSION["formulario"]["datos"]["pascte"] = doubleval(\funcionesGenerales::retornarNumeroLimpio($valor));
                    }
                }
                if (substr($key, 0, 7) == 'paslar_') {
                    $anox = substr($key, 7, 4);
                    $_SESSION["formulario"]["datos"]["f"][$anox]["paslar"] = doubleval(\funcionesGenerales::retornarNumeroLimpio($valor));
                    if ($anox == $_SESSION["formulario"]["anorenovar"]) {
                        $_SESSION["formulario"]["datos"]["paslar"] = doubleval(\funcionesGenerales::retornarNumeroLimpio($valor));
                    }
                }
                if (substr($key, 0, 7) == 'pastot_') {
                    $anox = substr($key, 7, 4);
                    $_SESSION["formulario"]["datos"]["f"][$anox]["pastot"] = doubleval(\funcionesGenerales::retornarNumeroLimpio($valor));
                    if ($anox == $_SESSION["formulario"]["anorenovar"]) {
                        $_SESSION["formulario"]["datos"]["pastot"] = doubleval(\funcionesGenerales::retornarNumeroLimpio($valor));
                    }
                }
                if (substr($key, 0, 7) == 'pattot_') {
                    $anox = substr($key, 7, 4);
                    $_SESSION["formulario"]["datos"]["f"][$anox]["pattot"] = doubleval(\funcionesGenerales::retornarNumeroLimpio($valor));
                    if ($anox == $_SESSION["formulario"]["anorenovar"]) {
                        $_SESSION["formulario"]["datos"]["pattot"] = doubleval(\funcionesGenerales::retornarNumeroLimpio($valor));
                    }
                }
                if (substr($key, 0, 7) == 'paspat_') {
                    $anox = substr($key, 7, 4);
                    $_SESSION["formulario"]["datos"]["f"][$anox]["paspat"] = doubleval(\funcionesGenerales::retornarNumeroLimpio($valor));
                    if ($anox == $_SESSION["formulario"]["anorenovar"]) {
                        $_SESSION["formulario"]["datos"]["paspat"] = doubleval(\funcionesGenerales::retornarNumeroLimpio($valor));
                    }
                }
                if (substr($key, 0, 7) == 'balsoc_') {
                    $anox = substr($key, 7, 4);
                    $_SESSION["formulario"]["datos"]["f"][$anox]["balsoc"] = doubleval(\funcionesGenerales::retornarNumeroLimpio($valor));
                    if ($anox == $_SESSION["formulario"]["anorenovar"]) {
                        $_SESSION["formulario"]["datos"]["balsoc"] = doubleval(\funcionesGenerales::retornarNumeroLimpio($valor));
                    }
                }

                if (substr($key, 0, 7) == 'ingope_') {
                    $anox = substr($key, 7, 4);
                    $_SESSION["formulario"]["datos"]["f"][$anox]["ingope"] = doubleval(\funcionesGenerales::retornarNumeroLimpio($valor));
                    if ($anox == $_SESSION["formulario"]["anorenovar"]) {
                        $_SESSION["formulario"]["datos"]["ingope"] = doubleval(\funcionesGenerales::retornarNumeroLimpio($valor));
                    }
                }
                if (substr($key, 0, 9) == 'ingnoope_') {
                    $anox = substr($key, 9, 4);
                    $_SESSION["formulario"]["datos"]["f"][$anox]["ingnoope"] = doubleval(\funcionesGenerales::retornarNumeroLimpio($valor));
                    if ($anox == $_SESSION["formulario"]["anorenovar"]) {
                        $_SESSION["formulario"]["datos"]["ingnoope"] = doubleval(\funcionesGenerales::retornarNumeroLimpio($valor));
                    }
                }
                if (substr($key, 0, 7) == 'gtoven_') {
                    $anox = substr($key, 7, 4);
                    $_SESSION["formulario"]["datos"]["f"][$anox]["gtoven"] = doubleval(\funcionesGenerales::retornarNumeroLimpio($valor));
                    if ($anox == $_SESSION["formulario"]["anorenovar"]) {
                        $_SESSION["formulario"]["datos"]["gtoven"] = doubleval(\funcionesGenerales::retornarNumeroLimpio($valor));
                    }
                }
                if (substr($key, 0, 7) == 'gtoadm_') {
                    $anox = substr($key, 7, 4);
                    $_SESSION["formulario"]["datos"]["f"][$anox]["gtoadm"] = doubleval(\funcionesGenerales::retornarNumeroLimpio($valor));
                    if ($anox == $_SESSION["formulario"]["anorenovar"]) {
                        $_SESSION["formulario"]["datos"]["gtoadm"] = doubleval(\funcionesGenerales::retornarNumeroLimpio($valor));
                    }
                }
                if (substr($key, 0, 7) == 'cosven_') {
                    $anox = substr($key, 7, 4);
                    $_SESSION["formulario"]["datos"]["f"][$anox]["cosven"] = doubleval(\funcionesGenerales::retornarNumeroLimpio($valor));
                    if ($anox == $_SESSION["formulario"]["anorenovar"]) {
                        $_SESSION["formulario"]["datos"]["cosven"] = doubleval(\funcionesGenerales::retornarNumeroLimpio($valor));
                    }
                }
                if (substr($key, 0, 7) == 'depamo_') {
                    $anox = substr($key, 7, 4);
                    $_SESSION["formulario"]["datos"]["f"][$anox]["depamo"] = doubleval(\funcionesGenerales::retornarNumeroLimpio($valor));
                    if ($anox == $_SESSION["formulario"]["anorenovar"]) {
                        $_SESSION["formulario"]["datos"]["depamo"] = doubleval(\funcionesGenerales::retornarNumeroLimpio($valor));
                    }
                }
                if (substr($key, 0, 7) == 'gasimp_') {
                    $anox = substr($key, 7, 4);
                    $_SESSION["formulario"]["datos"]["f"][$anox]["gasimp"] = doubleval(\funcionesGenerales::retornarNumeroLimpio($valor));
                    if ($anox == $_SESSION["formulario"]["anorenovar"]) {
                        $_SESSION["formulario"]["datos"]["gasimp"] = doubleval(\funcionesGenerales::retornarNumeroLimpio($valor));
                    }
                }
                if (substr($key, 0, 7) == 'gasint_') {
                    $anox = substr($key, 7, 4);
                    $_SESSION["formulario"]["datos"]["f"][$anox]["gasint"] = doubleval(\funcionesGenerales::retornarNumeroLimpio($valor));
                    if ($anox == $_SESSION["formulario"]["anorenovar"]) {
                        $_SESSION["formulario"]["datos"]["gasint"] = doubleval(\funcionesGenerales::retornarNumeroLimpio($valor));
                    }
                }


                if (substr($key, 0, 7) == 'utiope_') {
                    $anox = substr($key, 7, 4);
                    $_SESSION["formulario"]["datos"]["f"][$anox]["utiope"] = doubleval(\funcionesGenerales::retornarNumeroLimpio($valor));
                    if ($anox == $_SESSION["formulario"]["anorenovar"]) {
                        $_SESSION["formulario"]["datos"]["utiope"] = doubleval(\funcionesGenerales::retornarNumeroLimpio($valor));
                    }
                }
                if (substr($key, 0, 7) == 'utinet_') {
                    $anox = substr($key, 7, 4);
                    $_SESSION["formulario"]["datos"]["f"][$anox]["utinet"] = doubleval(\funcionesGenerales::retornarNumeroLimpio($valor));
                    if ($anox == $_SESSION["formulario"]["anorenovar"]) {
                        $_SESSION["formulario"]["datos"]["utinet"] = doubleval(\funcionesGenerales::retornarNumeroLimpio($valor));
                    }
                }

                if (substr($key, 0, 10) == 'bienes_mi_') {
                    $ind = intval(substr($key, 10));
                    $_SESSION["formulario"]["datos"]["bienes"][$ind]["matinmo"] = trim(strtoupper($valor));
                }
                if (substr($key, 0, 10) == 'bienes_di_') {
                    $ind = intval(substr($key, 10));
                    $_SESSION["formulario"]["datos"]["bienes"][$ind]["dir"] = trim(strtoupper($valor));
                }
                if (substr($key, 0, 10) == 'bienes_ba_') {
                    $ind = intval(substr($key, 10));
                    $_SESSION["formulario"]["datos"]["bienes"][$ind]["barrio"] = trim(strtoupper($valor));
                }
                if (substr($key, 0, 10) == 'bienes_mu_') {
                    $ind = intval(substr($key, 10));
                    $_SESSION["formulario"]["datos"]["bienes"][$ind]["muni"] = trim(strtoupper($valor));
                }
                if (substr($key, 0, 10) == 'bienes_dp_') {
                    $ind = intval(substr($key, 10));
                    $_SESSION["formulario"]["datos"]["bienes"][$ind]["dpto"] = trim(strtoupper($valor));
                }
                if (substr($key, 0, 10) == 'bienes_pa_') {
                    $ind = intval(substr($key, 10));
                    $_SESSION["formulario"]["datos"]["bienes"][$ind]["pais"] = trim(strtoupper($valor));
                }

                // responsabilidades tributarias
                if (substr($key, 0, 12) == 'codrespotri_') {
                    $ind = intval(substr($key, 12));
                    $_SESSION["formulario"]["datos"]["codrespotri"][$key] = trim(strtoupper($valor));
                }

                if (substr($key, 0, 10) == 'empsoccat_') {
                    $_SESSION["formulario"]["datos"][$key] = $valor;
                }

                if (substr($key, 0, 10) == 'empsocben_') {
                    $_SESSION["formulario"]["datos"][$key] = $valor;
                }
            }
        }


        //
        if ($_SESSION["formulario"]["formulariotipotramite"] == 'actualizacionmatriculados' || $_SESSION["formulario"]["formulariotipotramite"] == 'digitacionmatriculados' || $_SESSION["formulario"]["formulariotipotramite"] == 'actualizacionafiliados') {
            $_SESSION["formulario"]["datos"]["ciiutamanoempresarial"] = base64_decode($_SESSION["entrada"]["ciiutamanoempresarial"]);
            $_SESSION["formulario"]["datos"]["ingresostamanoempresarial"] = doubleval(\funcionesGenerales::retornarNumeroLimpio(base64_decode($_SESSION["entrada"]["ingresostamanoempresarial"])));
            $_SESSION["formulario"]["datos"]["anodatostamanoempresarial"] = trim(base64_decode($_SESSION["entrada"]["anodatostamanoempresarial"]));
            $_SESSION["formulario"]["datos"]["fechadatostamanoempresarial"] = str_replace(array("/", "-"), "", base64_decode($_SESSION["entrada"]["fechadatostamanoempresarial"]));
        } else {
            if ($_SESSION["formulario"]["datos"]["ciius"][2] == '' &&
                    $_SESSION["formulario"]["datos"]["ciius"][3] == '' &&
                    $_SESSION["formulario"]["datos"]["ciius"][4] == '') {

                $_SESSION["formulario"]["datos"]["ciiutamanoempresarial"] = $_SESSION["formulario"]["datos"]["ciius"][1];
                $_SESSION["formulario"]["datos"]["ingresostamanoempresarial"] = $_SESSION["formulario"]["datos"]["ingope"];
                $_SESSION["formulario"]["datos"]["feciniact2"] = '';
                $_SESSION["formulario"]["datos"]["maying2"] = '';
                $_SESSION["formulario"]["datos"]["maying3"] = '';
                $_SESSION["formulario"]["datos"]["maying4"] = '';
            }
        }


        //
        if (!isset($_SESSION["formulario"]["datos"]["cc"]) || $_SESSION["formulario"]["datos"]["cc"] == '') {
            $_SESSION["formulario"]["datos"]["cc"] = CODIGO_EMPRESA;
        }

        // En caso de personas juridicas principales con nit, este se toma como numero de identificaci&oacute;n
        if ($_SESSION["formulario"]["datos"]["organizacion"] > '02' && $_SESSION["formulario"]["datos"]["categoria"] == "1") {
            if (trim($_SESSION["formulario"]["datos"]["nit"]) != '') {
                $_SESSION["formulario"]["datos"]["tipoidentificacion"] = '2';
                $_SESSION["formulario"]["datos"]["identificacion"] = trim($_SESSION["formulario"]["datos"]["nit"]);
            }
        }


        // Cuando el tramite sea renovacion actualiza estas variables
        if ($_SESSION["formulario"]["formulariotipotramite"] != 'actualizacionmatriculados' &&
                $_SESSION["formulario"]["formulariotipotramite"] != 'digitacionnmatriculados' &&
                $_SESSION["formulario"]["formulariotipotramite"] != 'actualizacionafiliados') {
            $_SESSION["formulario"]["datos"]["fechadatos"] = date("Ymd");
        }

        $_SESSION["formulario"]["datos"]["cntestab01"] = 0;
        $_SESSION["formulario"]["datos"]["cntestab02"] = 0;
        $_SESSION["formulario"]["datos"]["cntestab03"] = 0;
        $_SESSION["formulario"]["datos"]["cntestab04"] = 0;
        $_SESSION["formulario"]["datos"]["cntestab05"] = 0;
        $_SESSION["formulario"]["datos"]["cntestab06"] = 0;
        $_SESSION["formulario"]["datos"]["cntestab07"] = 0;
        $_SESSION["formulario"]["datos"]["cntestab08"] = 0;
        $_SESSION["formulario"]["datos"]["cntestab09"] = 0;
        $_SESSION["formulario"]["datos"]["cntestab10"] = 0;
        $_SESSION["formulario"]["datos"]["cntestab11"] = 0;

        if ($_SESSION["formulario"]["datos"]["organizacion"] != '01') {
            $_SESSION["formulario"]["datos"]["ape1"] = '';
            $_SESSION["formulario"]["datos"]["ape2"] = '';
            $_SESSION["formulario"]["datos"]["nom1"] = '';
            $_SESSION["formulario"]["datos"]["nom2"] = '';
            $_SESSION["formulario"]["datos"]["sexo"] = '';
            $_SESSION["formulario"]["datos"]["ideext"] = '';

            $_SESSION["formulario"]["datos"]["idmunidoc"] = '';
            $_SESSION["formulario"]["datos"]["fechanacimiento"] = '';
            $_SESSION["formulario"]["datos"]["fecexpdoc"] = '';
            $_SESSION["formulario"]["datos"]["paisexpdoc"] = '';
            $_SESSION["formulario"]["datos"]["nacionalidad"] = '';
            $_SESSION["formulario"]["datos"]["idetripaiori"] = '';
            $_SESSION["formulario"]["datos"]["paiori"] = '';
            $_SESSION["formulario"]["datos"]["idetriextep"] = '';

            $_SESSION["formulario"]["datos"]["c"] = '';
            $_SESSION["formulario"]["datos"]["ideext"] = '';
        }

        if ($_SESSION["formulario"]["datos"]["organizacion"] == '01' || $_SESSION["formulario"]["datos"]["organizacion"] == '02') {
            $_SESSION["formulario"]["datos"]["ctrbic"] = '';
            $_SESSION["formulario"]["datos"]["fechaconstitucion"] = '';
            $_SESSION["formulario"]["datos"]["fechavencimiento"] = '';
        }

        //
        if ($_SESSION["formulario"]["datos"]["organizacion"] == '02' || $_SESSION["formulario"]["datos"]["categoria"] == '2' || $_SESSION["formulario"]["datos"]["categoria"] == '3') {
            $_SESSION["formulario"]["datos"]["actcte"] = 0;
            $_SESSION["formulario"]["datos"]["actnocte"] = 0;
            $_SESSION["formulario"]["datos"]["actfij"] = 0;
            $_SESSION["formulario"]["datos"]["fijnet"] = 0;
            $_SESSION["formulario"]["datos"]["actotr"] = 0;
            $_SESSION["formulario"]["datos"]["actval"] = 0;
            $_SESSION["formulario"]["datos"]["acttot"] = 0;
            $_SESSION["formulario"]["datos"]["invent"] = 0;
            $_SESSION["formulario"]["datos"]["actsinaju"] = 0;
            $_SESSION["formulario"]["datos"]["pascte"] = 0;
            $_SESSION["formulario"]["datos"]["paslar"] = 0;
            $_SESSION["formulario"]["datos"]["pastot"] = 0;
            $_SESSION["formulario"]["datos"]["pattot"] = 0;
            $_SESSION["formulario"]["datos"]["paspat"] = 0;
            $_SESSION["formulario"]["datos"]["balsoc"] = 0;
            $_SESSION["formulario"]["datos"]["ingope"] = 0;
            $_SESSION["formulario"]["datos"]["gtoven"] = 0;
            $_SESSION["formulario"]["datos"]["gtoadm"] = 0;
            $_SESSION["formulario"]["datos"]["cosven"] = 0;
            $_SESSION["formulario"]["datos"]["depamo"] = 0;
            $_SESSION["formulario"]["datos"]["gasimp"] = 0;
            $_SESSION["formulario"]["datos"]["gasint"] = 0;
            $_SESSION["formulario"]["datos"]["utiope"] = 0;
            $_SESSION["formulario"]["datos"]["utinet"] = 0;
            $_SESSION["formulario"]["datos"]["gruponiif"] = '';
        } else {
            $_SESSION["formulario"]["datos"]["actvin"] = 0;
        }

        // JINT: 20230202 - Para cambios en el nombre de las personas naturales
        if ($_SESSION["formulario"]["datos"]["organizacion"] == '01') {
            $_SESSION["formulario"]["datos"]["nombre"] = trim((string) $_SESSION["formulario"]["datos"]["ape1"]);
            if (trim((string) $_SESSION["formulario"]["datos"]["ape2"]) != '') {
                $_SESSION["formulario"]["datos"]["nombre"] .= ' ' . trim((string) $_SESSION["formulario"]["datos"]["ape2"]);
            }
            if (trim((string) $_SESSION["formulario"]["datos"]["nom1"]) != '') {
                $_SESSION["formulario"]["datos"]["nombre"] .= ' ' . trim((string) $_SESSION["formulario"]["datos"]["nom1"]);
            }
            if (trim((string) $_SESSION["formulario"]["datos"]["nom2"]) != '') {
                $_SESSION["formulario"]["datos"]["nombre"] .= ' ' . trim((string) $_SESSION["formulario"]["datos"]["nom2"]);
            }
        }

        // en caso de digitación de formulario en trámites de matrícula y renovación
        // Graba formulario        
        if ($_SESSION["formulario"]["formulariotipotramite"] != 'actualizacionmatriculados' &&
                $_SESSION["formulario"]["formulariotipotramite"] != 'digitacionmatriculados' &&
                $_SESSION["formulario"]["formulariotipotramite"] != 'actualizacionafiliados') {
            $xml = \funcionesRegistrales::serializarExpedienteMatricula($mysqli, '', $_SESSION["formulario"]["datos"]);

            //
            $secx = '000';
            if ($_SESSION["formulario"]["sectransaccion"] != '') {
                $secx = sprintf("%03s", $_SESSION["formulario"]["sectransaccion"]);
            }

            //
            $arrCampos = array(
                'idliquidacion',
                'secuencia',
                'cc',
                'expediente',
                'numrue',
                'grupodatos',
                'xml',
                'idestado'
            );
            $arrValues = array(
                $_SESSION["formulario"]["liquidacion"],
                "'" . $secx . "'",
                "'" . $_SESSION["formulario"]["cc"] . "'",
                "'" . $_SESSION["formulario"]["matricula"] . "'",
                "''",
                "'completo'",
                "'" . addslashes($xml) . "'",
                "'2'"
            );
            if ($_SESSION["formulario"]["sectransaccion"] != '' && $_SESSION["formulario"]["sectransaccion"] != '000') {
                $condicion = "idliquidacion=" . $_SESSION["formulario"]["liquidacion"] . " and secuencia='" . $secx . "' and expediente='" . $_SESSION["formulario"]["matricula"] . "' and grupodatos='completo'";
            } else {
                $condicion = "idliquidacion=" . $_SESSION["formulario"]["liquidacion"] . " and expediente='" . $_SESSION["formulario"]["matricula"] . "' and grupodatos='completo'";
            }
            $result = borrarRegistrosMysqliApi($mysqli, 'mreg_liquidaciondatos', $condicion);

            //    
            $txtemergente = '';
            $result = insertarRegistrosMysqliApi($mysqli, 'mreg_liquidaciondatos', $arrCampos, $arrValues);
            if ($result === false) {
                $txtemergente = 'Error grabando formulario en tabla mreg_liquidaciondatos (' . str_replace("'", "", $_SESSION["generales"]["mensajeerror"]) . ')' . chr(13);
            }

            // Crea el log de mreg_liquidaciondatos
            $arrCampos = array(
                'fecha',
                'hora',
                'idusuario',
                'ip',
                'idliquidacion',
                'expediente',
                'xml'
            );
            $arrValues = array(
                "'" . date("Ymd") . "'",
                "'" . date("His") . "'",
                "'" . $_SESSION["generales"]["codigousuario"] . "'",
                "'" . \funcionesGenerales::localizarIP() . "'",
                $_SESSION["formulario"]["idliquidacion"],
                "'" . $_SESSION["formulario"]["matricula"] . "'",
                "'" . addslashes($xml) . "'"
            );
            insertarRegistrosMysqliApi($mysqli, 'mreg_liquidaciondatos_log_' . date("Y"), $arrCampos, $arrValues);

            // Crea log de operaciones
            $detalle = 'Modificacion o creacion formulario mercantil o esadl.';
            if ($codigousuariocontrol != '' && $codigousuariocontrol != 'USUPUBXX') {
                $detalle .= ' ´Codigo del Usuario: ' . $codigousuariocontrol . "'";
            }
            if ($emailusuariocontrol != '' && $emailusuariocontrol != 'USUPUBXX') {
                $detalle .= ' Email del Usuario: ' . $emailusuariocontrol;
            }
            actualizarLogMysqliApi($mysqli, '003', $_SESSION["generales"]["codigousuario"], 'grabaFormulariosMercantil.php', '', '', '', $detalle, $_SESSION["formulario"]["matricula"], '', $_SESSION["formulario"]["datos"]["nit"], $_SESSION["formulario"]["liquidacion"]);

            // 2016-02-06 : JINT : Graba la tabla mreg_renovacion_datos_control
            if ($_SESSION["formulario"]["tipotramite"] == 'renovacionmatricula' || $_SESSION["formulario"]["tipotramite"] == 'renovacionesadl') {
                \funcionesRegistrales::almacenarDatosImportantesRenovacion($mysqli, $_SESSION["formulario"]["liquidacion"], $_SESSION["formulario"]["datos"], 'F');
            }
        }

        // En caso de digitación formularios y actualización matriculados
        // Graba formulario        
        if ($_SESSION["formulario"]["formulariotipotramite"] == 'actualizacionmatriculados' ||
                $_SESSION["formulario"]["formulariotipotramite"] == 'digitacionmatriculados' ||
                $_SESSION["formulario"]["formulariotipotramite"] == 'actualizacionafiliados') {
            $_SESSION["formulario"]["datos"]["empsoccategorias"] = '';
            $_SESSION["formulario"]["datos"]["empsocbeneficiarios"] = '';
            foreach ($_SESSION["formulario"]["datos"] as $key => $valor) {
                if (substr($key, 0, 10) == 'empsoccat_') {
                    if ($valor == 'S') {
                        if ($_SESSION["formulario"]["datos"]["empsoccategorias"] !== '') {
                            $_SESSION["formulario"]["datos"]["empsoccategorias"] .= ',';
                        }
                        $_SESSION["formulario"]["datos"]["empsoccategorias"] .= $key;
                    }
                }
                if (substr($key, 0, 10) == 'empsocben_') {
                    if ($valor == 'S') {
                        if ($_SESSION["formulario"]["datos"]["empsocbeneficiarios"] !== '') {
                            $_SESSION["formulario"]["datos"]["empsocbeneficiarios"] .= ',';
                        }
                        $_SESSION["formulario"]["datos"]["empsocbeneficiarios"] .= $key;
                    }
                }
            }
            if (!isset($_SESSION["formulario"]["codigobarrasseleccionado"])) {
                $_SESSION["formulario"]["codigobarrasseleccionado"] = '';
            }
            $res = \funcionesRegistrales::actualizarMregEstInscritos($mysqli, $_SESSION["formulario"]["datos"], $_SESSION["formulario"]["codigobarrasseleccionado"], $_SESSION["formulario"]["formulariotipotramite"]);
            if ($res === false) {
                actualizarLogMysqliApi($mysqli, '003', $_SESSION["generales"]["codigousuario"], 'mregGrabaFormulariosMercantil.php', '', '', '', 'Actualización de matriculados erronea : ' . $_SESSION["generales"]["mensajeerror"], $_SESSION["formulario"]["matricula"], '', $_SESSION["formulario"]["datos"]["nit"], $_SESSION["formulario"]["liquidacion"]);
                $txtemergente = $_SESSION["generales"]["mensajeerror"];
            } else {
                actualizarLogMysqliApi($mysqli, '003', $_SESSION["generales"]["codigousuario"], 'mregGrabaFormulariosMercantil.php', '', '', '', 'Actualización de matriculados satisfactoria', $_SESSION["formulario"]["matricula"], '', $_SESSION["formulario"]["datos"]["nit"], $_SESSION["formulario"]["liquidacion"]);
                $txtemergente = '';
            }
        }


        //
        $mysqli->close();

        // Arma el XML de respuesta
        if (trim($txtemergente) == '') {
            $_SESSION["generales"]["mensajeerror"] = '';
            return true;
        } else {
            $_SESSION["generales"]["mensajeerror"] = $txtemergente;
            return false;
        }
    }

    public function grabaFormularioMercantilSelectBarrios(API $api) {
        require_once ('mysqli.php');
        require_once ('funcionesGenerales.php');
        $_SESSION ["jsonsalida"] = array();
        $_SESSION ["jsonsalida"] ["codigoerror"] = '0000';
        $_SESSION ["jsonsalida"] ["mensajeerror"] = '';
        $_SESSION ["jsonsalida"] ["cantidad"] = 0;
        $_SESSION ["jsonsalida"] ["registros"] = array();
        $_SESSION ["jsonsalida"] ["html"] = '';
        if ($api->get_request_method() != "POST") {
            $_SESSION ["jsonsalida"] ["codigoerror"] = "9999";
            $_SESSION ["jsonsalida"] ["mensajeerror"] = 'La petición debe ser POST';
            $api->response($api->json($_SESSION ["jsonsalida"]), 200);
        }
        $api->validarParametro("mun", true);
        $api->validarParametro("session_parameters", false);
        if (isset($_SESSION ["entrada"] ["session_parameters"]) && $_SESSION ["entrada"] ["session_parameters"] != '') {
            \funcionesGenerales::desarmarVariablesPantalla($_SESSION ["entrada"] ["session_parameters"]);
        }
        $mysqli = conexionMysqliApi();
        $bars = retornarRegistrosMysqliApi($mysqli, 'mreg_barriosmuni', "idmunicipio='" . sprintf("%05s", $_SESSION ["entrada"] ["mun"]) . "'", "nombre");
        if ($bars && !empty($bars)) {
            $b = array(
                'id' => '',
                'nom' => 'Seleccione...'
            );
            $_SESSION ["jsonsalida"] ["registros"][] = $b;
            foreach ($bars as $b) {
                $b = array(
                    'id' => $b["idbarrio"],
                    'nom' => $b["nombre"]
                );
                $_SESSION ["jsonsalida"] ["registros"][] = $b;
            }
        }
        $_SESSION ["jsonsalida"] ["cantidad"] = count($bars);
        $mysqli->close();
        $json = $api->json($_SESSION ["jsonsalida"]);
        $api->response(str_replace("\\/", "/", $json), 200);
    }

}

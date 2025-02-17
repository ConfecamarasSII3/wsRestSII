<?php

require '../../librerias/funciones/validarSession.php';

// Valida que el script pueda ser ejecutado
$script=__FILE__;
$ok=validarAcceso($script);
if ($ok!='true') {
    header("Location: ../../disparador.php?accion=negado&mensaje=".$ok);
    exit();
}

//
if (!isset($_SESSION["generales"]["retorno"])) {
    $_SESSION["generales"]["retorno"] = '';
}
if (isset($_SESSION["vars"]["retorno"])) {
    $_SESSION["generales"]["retorno"] = $_SESSION["vars"]["retorno"];
}

// Librer&iacute;as requeridas
require_once $_SESSION["generales"]["pathabsoluto"] . '/librerias/funciones/generales.php';
require_once $_SESSION["generales"]["pathabsoluto"] . '/configuracion/common' . $_SESSION["generales"]["codigoempresa"] . '.php';

//
if (isset($_SESSION["vars"]["tipoopcion"])) {
    $_SESSION["generales"]["tipoopcion"] = $_SESSION["vars"]["tipoopcion"];
}

//
if ($_SESSION["generales"]["codigousuario"] == 'USUPUBXX') {
    $_SESSION["generales"]["sedeusuario"] = '99';
}
if (strlen($_SESSION["generales"]["sedeusuario"]) != 2) {
    $txt = 'La sede a la que está asignado el usuario está incorrectamente definida. Por favor informe este hecho al administrador del sistema';
    armarMensaje(600, 'h2', $txt);
    exit();
}

//
$accion = sanarEntradas::sanarAccion($_SESSION["vars"]["accion"]);
switch ($accion) {
case "seleccion": pantallaInicial();
    break;
case "cargarregistro": pantallaInicial();
    break;
case "cargarexpediente": pantallaSeleccion();
    break;
case "mostrarseleccion": mostrarFormularioSeleccion();
    break;
case "validar": validar();
    break;
case "loadfile": loadFile();
    break;
case "generarradicacion": generarRadicacion();
    break;
}

/**
 * Funci&oacute;n que inicializa un tr&aacute;mite, localiza el n&uacute;mero de la liquidaci&oacute;n y presenta el formulario
 * para que el usuario indique la matr&iacute;cula y el n&uacute;mero de c&eacute;dula relacionado
 */
function pantallaInicial() 
{
    include_once $_SESSION["generales"]["pathabsoluto"] . '/librerias/funciones/generales.php';
    include_once $_SESSION["generales"]["pathabsoluto"] . '/librerias/funciones/persistenciamreg.php';
    include_once $_SESSION["generales"]["pathabsoluto"] . '/librerias/presentacion/template.class.php';
    include_once $_SESSION["generales"]["pathabsoluto"] . '/librerias/presentacion/presentacion.class.php';

    $_SESSION["generales"]["matricula"] = '';
    $_SESSION["generales"]["proponente"] = '';
    if (isset($_SESSION["vars"]["matricula"])) {
        $_SESSION["generales"]["matricula"] = ltrim($_SESSION["vars"]["matricula"], "0");
    }
    if (isset($_SESSION["vars"]["proponente"])) {
        $_SESSION["generales"]["proponente"] = ltrim($_SESSION["vars"]["proponente"], "0");
    }

    include_once '../../librerias/funciones/sanarEntradas.class.php';

    $arrBtnTipo = array();
    $arrBtnEnlace = array();
    $arrBtnImagen = array();
    $arrBtnTooltip = array();

    if ($_SESSION["generales"]["retorno"] == 'consultaexpedientes') {
        $arrBtnTipo = array('href');
        $arrBtnEnlace = array('../../librerias/proceso/mregConsultaExpedientes.php?accion=mostrarexpedientes');
        $arrBtnImagen = array('Browser de consulta');
        $arrBtnTooltip = array('Retornar al browser de consulta de expedientes');
    }

    //
    /*
      if ($_SESSION["generales"]["tipoopcion"] == 'correcciones_mercantil' || $_SESSION["generales"]["tipoopcion"] == 'sincosto_mercantil') {
      if (trim($_SESSION["generales"]["matricula"]) == '') {
      armarMensaje(
      '600', 'h2', 'No es posible continuar con el proceso puesto que no se ha indicado el expediente afectado.', 600, 400, '', '', false, 0, 'si', '', 'si', $arrBtnTipo, $arrBtnEnlace, $arrBtnImagen, $arrBtnTooltip
      );
      }
      }
     */
    if ($_SESSION["generales"]["tipoopcion"] == 'correcciones_mercantil') {
        if (trim($_SESSION["generales"]["matricula"]) == '') {
            armarMensaje(
                '600', 'h2', 'No es posible continuar con el proceso puesto que no se ha indicado el expediente afectado.', 600, 400, '', '', false, 0, 'si', '', 'si', $arrBtnTipo, $arrBtnEnlace, $arrBtnImagen, $arrBtnTooltip
            );
        }
    }

    /*
      if ($_SESSION["generales"]["tipoopcion"] == 'correcciones_proponentes' || $_SESSION["generales"]["tipoopcion"] == 'sincosto_proponentes') {
      if (trim($_SESSION["generales"]["proponente"]) == '') {
      armarMensaje(
      '600', 'h2', 'No es posible continuar con el proceso puesto que no se ha indicado el expediente afectado.', 600, 400, '', '', false, 0, 'si', '', 'si', $arrBtnTipo, $arrBtnEnlace, $arrBtnImagen, $arrBtnTooltip
      );
      }
      }
     */

    if ($_SESSION["generales"]["tipoopcion"] == 'correcciones_proponentes') {
        if (trim($_SESSION["generales"]["proponente"]) == '') {
            armarMensaje(
                '600', 'h2', 'No es posible continuar con el proceso puesto que no se ha indicado el expediente afectado.', 600, 400, '', '', false, 0, 'si', '', 'si', $arrBtnTipo, $arrBtnEnlace, $arrBtnImagen, $arrBtnTooltip
            );
        }
    }


    if ($_SESSION["generales"]["tipousuario"] == '00' || $_SESSION["generales"]["tipousuario"] == '06') {
        $pres = new presentacion();
        $string = '<center>';
        $string .= $pres->armarLineaTexto(600, cambiarSustitutoHtml(retornarPantallaPredisenada('mreg.introduccion.tramites.sin.costo')));

        $arrBtnTipo = array();
        $arrBtnEnlace = array();
        $arrBtnImagen = array();
        $arrBtnTooltip = array();

        $arrBtnTipo[] = 'href';
        $arrBtnEnlace[] = '../../librerias/proceso/mregRadicarDocumentosSinCosto.php?accion=cargarexpediente';
        $arrBtnImagen[] = 'Continuar';
        $arrBtnTooltip[] = 'Continuar con el proceso';

        if ($_SESSION["generales"]["retorno"] == 'consultaexpedientes') {
            $arrBtnTipo[] = 'href';
            $arrBtnEnlace[] = '../../librerias/proceso/mregConsultaExpedientes.php?accion=mostrarexpedientes';
            $arrBtnImagen[] = 'Browser de consulta';
            $arrBtnTooltip[] = 'Retornar al browser de consulta de expedientes';
        }

        $string .= $pres->armarBarraBotonesProcesoDinamico(800, $arrBtnTipo, $arrBtnImagen, $arrBtnEnlace, $arrBtnTooltip);
        $string .= '</center>';
        unset($pres);
        mostrarCuerpoIE26a(array(), '', '', 'Radicaci&oacute;n de documentos sin costo y correcciones', $string, 620, 400, '', 'Espere procesando....');
    } else {
        Header("Location:" . TIPO_HTTP . HTTP_HOST . '/librerias/proceso/mregRadicarDocumentosSinCosto.php?accion=cargarexpediente');
    }
}

/**
 * Funci&oacute;n que inicializa un tr&aacute;mite, localiza el n&uacute;mero de la liquidaci&oacute;n y presenta el formulario
 * para que el usuario indique la matr&iacute;cula y el n&uacute;mero de c&eacute;dula relacionado
 */
function pantallaSeleccion() 
{
    if ($_SESSION["generales"]["tipoopcion"] == 'correcciones_mercantil') {
        if ($_SESSION["generales"]["matricula"] == '') {
            armarMensaje('600', 'h2', 'No es posible continuar con el proceso puesto que no se ha indicado el expediente afectado.');
        }
    }
    if ($_SESSION["generales"]["tipoopcion"] == 'correcciones_proponentes') {
        if ($_SESSION["generales"]["proponente"] == '') {
            armarMensaje('600', 'h2', 'No es posible continuar con el proceso puesto que no se ha indicado el expediente afectado.');
        }
    }


    //
    $_SESSION["tramite"] = retornarMregLiquidacion(0, 'VC');
    $_SESSION["tramite"]["idmatriculabase"] = '';
    $_SESSION["tramite"]["idproponentebase"] = '';

    //
    if ($_SESSION["generales"]["tipoopcion"] == 'correcciones_mercantil' || $_SESSION["generales"]["tipoopcion"] == 'sincosto_mercantil') {
        $_SESSION["tramite"]["idmatriculabase"] = sanarEntradas::sanarString($_SESSION["generales"]["matricula"]);
        if ($_SESSION["tramite"]["idmatriculabase"] != '') {
            $_SESSION["tramite"]["datos"] = retornarExpedienteMercantil($_SESSION["tramite"]["idmatriculabase"]);
            if ($_SESSION["tramite"]["datos"] === false || empty($_SESSION["tramite"]["datos"]) || $_SESSION["tramite"]["datos"] == 0) {
                armarMensaje('600', 'h2', 'La matr&iacute;cula No. ' . $_SESSION["tramite"]["idmatriculabase"] . ' no pudo ser recuperada del sistema de registro.');
            }
        } else {
            $_SESSION["tramite"]["datos"] = desserializarExpedienteMatricula('');
        }
    }

    if ($_SESSION["generales"]["tipoopcion"] == 'correcciones_proponentes' || $_SESSION["generales"]["tipoopcion"] == 'sincosto_proponentes') {
        $_SESSION["tramite"]["idproponentebase"] = sanarEntradas::sanarString($_SESSION["generales"]["proponente"]);
        if ($_SESSION["tramite"]["idproponentebase"] != '') {
            $_SESSION["tramite"]["datos"] = retornarExpedienteProponente($_SESSION["tramite"]["idproponentebase"]);
            if ($_SESSION["tramite"]["datos"] === false || empty($_SESSION["tramite"]["datos"]) || $_SESSION["tramite"]["datos"] == 0) {
                armarMensaje('600', 'h2', 'El proponente No. ' . $_SESSION["tramite"]["idproponentebase"] . ' no pudo ser recuperada del sistema de registro.');
            }
        } else {
            $_SESSION["tramite"]["datos"] = desserializarExpedienteProponente();
        }
    }

    //
    $_SESSION["tramite"]["idliquidacion"] = retornarSecuencia('LIQUIDACION-REGISTROS');
    $_SESSION["tramite"]["numeroliquidacion"] = $_SESSION["tramite"]["idliquidacion"];
    $_SESSION["tramite"]["numerorecuperacion"] = asignarNumeroRecuperacion('mreg');
    $_SESSION["tramite"]["nrocontrolsipref"] = '';

    //
    $_SESSION["tramite"]["tipotramite"] = '';
    $_SESSION["tramite"]["subtipotramite"] = '';
    $_SESSION["tramite"]["fecha"] = date("Ymd");
    $_SESSION["tramite"]["hora"] = date("His");
    $_SESSION["tramite"]["fechaultimamodificacion"] = date("Ymd");
    $_SESSION["tramite"]["idusuario"] = $_SESSION["generales"]["codigousuario"];
    $_SESSION["tramite"]["iptramite"] = localizarIP();

    //
    if ($_SESSION["generales"]["tipoopcion"] == 'correcciones_mercantil' || $_SESSION["generales"]["tipoopcion"] == 'sincosto_mercantil') {
        $_SESSION["tramite"]["idexpedientebase"] = $_SESSION["tramite"]["idmatriculabase"];
        $_SESSION["tramite"]["tipoidentificacionbase"] = $_SESSION["tramite"]["datos"]["tipoidentificacion"];
        $_SESSION["tramite"]["identificacionbase"] = $_SESSION["tramite"]["datos"]["identificacion"];
        $_SESSION["tramite"]["nombrebase"] = $_SESSION["tramite"]["datos"]["nombre"];
        $_SESSION["tramite"]["organizacionbase"] = $_SESSION["tramite"]["datos"]["organizacion"];
        $_SESSION["tramite"]["categoriabase"] = $_SESSION["tramite"]["datos"]["categoria"];
    }

    //
    if ($_SESSION["generales"]["tipoopcion"] == 'correcciones_proponentes' || $_SESSION["generales"]["tipoopcion"] == 'sincosto_proponentes') {
        $_SESSION["tramite"]["idexpedientebase"] = $_SESSION["tramite"]["idproponentebase"];
        $_SESSION["tramite"]["tipoidentificacionbase"] = $_SESSION["tramite"]["datos"]["idtipoidentificacion"];
        $_SESSION["tramite"]["identificacionbase"] = $_SESSION["tramite"]["datos"]["identificacion"];
        $_SESSION["tramite"]["nombrebase"] = $_SESSION["tramite"]["datos"]["nombre"];
        $_SESSION["tramite"]["organizacionbase"] = $_SESSION["tramite"]["datos"]["organizacion"];
        $_SESSION["tramite"]["categoriabase"] = '';
    }

    //
    $_SESSION["tramite"]["idestado"] = '01';
    $_SESSION["tramite"]["incluirafiliacion"] = 'N';
    $_SESSION["tramite"]["incluirformularios"] = 'N';
    $_SESSION["tramite"]["incluirdiploma"] = 'N';
    $_SESSION["tramite"]["incluircartulina"] = 'N';
    $_SESSION["tramite"]["incluircertificados"] = 'N';
    $_SESSION["tramite"]["incluirfletes"] = 'N';
    $_SESSION["tramite"]["proyectocaja"] = '001';
    $_SESSION["tramite"]["idformapago"] = '01';

    //
    $_SESSION["tramite"]["motivocorreccion"] = '';
    $_SESSION["tramite"]["tipoerror1"] = '';
    $_SESSION["tramite"]["tipoerror2"] = '';
    $_SESSION["tramite"]["tipoerror3"] = '';
    $_SESSION["tramite"]["tipoidentificacioncor"] = '';
    $_SESSION["tramite"]["identificacioncor"] = '';
    $_SESSION["tramite"]["nombre1cor"] = '';
    $_SESSION["tramite"]["nombre2cor"] = '';
    $_SESSION["tramite"]["apellido1cor"] = '';
    $_SESSION["tramite"]["apellido2cor"] = '';
    $_SESSION["tramite"]["direccioncor"] = '';
    $_SESSION["tramite"]["municipiocor"] = '';
    $_SESSION["tramite"]["emailcor"] = '';
    $_SESSION["tramite"]["telefonocor"] = '';
    $_SESSION["tramite"]["celularcor"] = '';


    //
    $_SESSION["tramite"]["tipoidentificacionaceptante"] = '';
    $_SESSION["tramite"]["identificacionaceptante"] = '';
    $_SESSION["tramite"]["cargoaceptante"] = '';
    $_SESSION["tramite"]["fechadocideaceptante"] = '';
    $_SESSION["tramite"]["nombre1aceptante"] = '';
    $_SESSION["tramite"]["nombre2aceptante"] = '';
    $_SESSION["tramite"]["apellido1aceptante"] = '';
    $_SESSION["tramite"]["apellido2aceptante"] = '';
    $_SESSION["tramite"]["direccionaceptante"] = '';
    $_SESSION["tramite"]["municipioaceptante"] = '';
    $_SESSION["tramite"]["emailaceptante"] = '';
    $_SESSION["tramite"]["telefonoaceptante"] = '';
    $_SESSION["tramite"]["celularaceptante"] = '';

    //
    $_SESSION["tramite"]["descripcionembargo"] = '';
    $_SESSION["tramite"]["descripciondesembargo"] = '';
    $_SESSION["tramite"]["tipoidentificaciondemandante"] = '';
    $_SESSION["tramite"]["identificaciondemandante"] = '';
    $_SESSION["tramite"]["nombredemandante"] = '';

    //
    $_SESSION["tramite"]["textolibre"] = '';

    //             
    $_SESSION["tramite"]["libro"] = '';
    $_SESSION["tramite"]["numreg"] = '';

    //
    $_SESSION["tramite"]["descripcionpqr"] = '';
    $_SESSION["tramite"]["tipoidentificacionpqr"] = '';
    $_SESSION["tramite"]["identificacionpqr"] = '';
    $_SESSION["tramite"]["nombre1pqr"] = '';
    $_SESSION["tramite"]["nombre2pqr"] = '';
    $_SESSION["tramite"]["apellido1pqr"] = '';
    $_SESSION["tramite"]["apellido2pqr"] = '';
    $_SESSION["tramite"]["direccionpqr"] = '';
    $_SESSION["tramite"]["municipiopqr"] = '';
    $_SESSION["tramite"]["emailpqr"] = '';
    $_SESSION["tramite"]["telefonopqr"] = '';
    $_SESSION["tramite"]["celularpqr"] = '';

    $_SESSION["tramite"]["descripcionrr"] = '';
    $_SESSION["tramite"]["tipoidentificacionrr"] = '';
    $_SESSION["tramite"]["identificacionrr"] = '';
    $_SESSION["tramite"]["nombre1rr"] = '';
    $_SESSION["tramite"]["nombre2rr"] = '';
    $_SESSION["tramite"]["apellido1rr"] = '';
    $_SESSION["tramite"]["apellido2rr"] = '';
    $_SESSION["tramite"]["direccionrr"] = '';
    $_SESSION["tramite"]["municipiorr"] = '';
    $_SESSION["tramite"]["emailrr"] = '';
    $_SESSION["tramite"]["telefonorr"] = '';
    $_SESSION["tramite"]["celularrr"] = '';


    $_SESSION["tramite"]["tipoideradicador"] = '';
    $_SESSION["tramite"]["ideradicador"] = '';
    $_SESSION["tramite"]["fechaexpradicador"] = '';
    $_SESSION["tramite"]["nombreradicador"] = '';
    $_SESSION["tramite"]["emailradicador"] = '';
    $_SESSION["tramite"]["telefonoradicador"] = '';
    $_SESSION["tramite"]["celularradicador"] = '';

    if ($_SESSION["generales"]["tipoopcion"] == 'correcciones_mercantil' 
        || $_SESSION["generales"]["tipoopcion"] == 'correcciones_proponentes'
    ) {
        $_SESSION["tramite"]["tipodoc"] = '90';
        $_SESSION["tramite"]["numdoc"] = 'N/A';
        $_SESSION["tramite"]["fechadoc"] = date("Ymd");
        $_SESSION["tramite"]["origendoc"] = 'CAMARA DE COMERCIO';
        $_SESSION["tramite"]["mundoc"] = MUNICIPIO;
    }

    grabarLiquidacionMreg();

    //
    mostrarFormularioSeleccion();
}

/**
 * Muestra el formulario de selecci&oacute;n de la matr&iacute;cula a renovar y/o permite retomar
 * una liquidaci&oacute;n previamente salvada
 */
function mostrarFormularioSeleccion() 
{
    if ($_SESSION["generales"]["tipoopcion"] == 'correcciones_mercantil') {
        if (substr($_SESSION["tramite"]["idmatriculabase"], 0, 1) != 'S') {
            $arrTem1 = retornarRegistros('mreg_maestro_tramites_sin_costo', "(tiporegistro = 'RegMer') and tipotramite like 'correcciones%'", "descripcion");
        }
        if (substr($_SESSION["tramite"]["idmatriculabase"], 0, 1) == 'S') {
            $arrTem1 = retornarRegistros('mreg_maestro_tramites_sin_costo', "(tiporegistro = 'RegEsadl') and tipotramite like 'correcciones%'", "descripcion");
        }
    }

    //
    if ($_SESSION["generales"]["tipoopcion"] == 'sincosto_mercantil') {
        if (substr($_SESSION["tramite"]["idmatriculabase"], 0, 1) != 'S') {
            $arrTem1 = retornarRegistros('mreg_maestro_tramites_sin_costo', "(tiporegistro = 'RegMer') and tipotramite not like 'correcciones%'", "descripcion");
        }
        if (substr($_SESSION["tramite"]["idmatriculabase"], 0, 1) == 'S') {
            $arrTem1 = retornarRegistros('mreg_maestro_tramites_sin_costo', "(tiporegistro = 'RegEsadl') and tipotramite not like 'correcciones%'", "descripcion");
        }
    }

    //
    if ($_SESSION["generales"]["tipoopcion"] == 'correcciones_proponentes') {
        $arrTem1 = retornarRegistros('mreg_maestro_tramites_sin_costo', "tiporegistro = 'RegPro' and tipotramite like 'correcciones%'", "descripcion");
    }

    //
    if ($_SESSION["generales"]["tipoopcion"] == 'sincosto_proponentes') {
        $arrTem1 = retornarRegistros('mreg_maestro_tramites_sin_costo', "tiporegistro = 'RegPro' and tipotramite not like 'correcciones%'", "descripcion");
    }

    //
    $arrTem = retornarRegistros('mreg_tablassirep', "idtabla='06'", "idcodigo");
    $arr = array();
    foreach ($arrTem as $t) {
        $arr[$t["idcodigo"]] = $t["idcodigo"] . ' - ' . utf8decode_sii($t["descripcion"]);
    }
    $txtSelTD = armarSelectArreglo('Seleccione ...', $arr, $_SESSION["tramite"]["tipodoc"]);

    //
    $arrTem = retornarRegistros('mreg_tipoidentificacion', "1=1", "id");
    $arr = array();
    foreach ($arrTem as $t) {
        $arr[$t["id"]] = $t["id"] . ' - ' . utf8decode_sii($t["descripcion"]);
    }
    $txtSelTDB = armarSelectArreglo('Seleccione ...', $arr, $_SESSION["tramite"]["tipoidentificacionbase"]);

    //
    if (!isset($_SESSION["municipios"])) {
        $arrTem = retornarRegistros('bas_municipios', "1=1", "ciudad");
        $_SESSION["municipios"] = array();
        foreach ($arrTem as $t) {
            $_SESSION["municipios"][$t["codigomunicipio"]] = utf8decode_sii($t["ciudad"]) . ' - (' . substr($t["departamento"], 0, 3) . ')';
        }
    }
    $txtSelMun = armarSelectArreglo('Seleccione ...', $_SESSION["municipios"], $_SESSION["tramite"]["mundoc"]);

    //
    $arrTipoTramite = retornarRegistro('bas_tipotramites', "id='" . $_SESSION["tramite"]["tipotramite"] . "'");

    //
    // Arma pantalla de presentaci&oacute;n
    $string = '';
    $pres = new presentacion();
    $string .= '<center>';

    $txt = 'Indique a continuaci&oacute;n el tipo de tr&aacute;mite que desea radicar';
    $string .= $pres->armarLineaTextoEnriquecido(600, $txt, 'center', '14', '#800000', 'si');
    $string .= $pres->abrirTablaBorde(600);
    foreach ($arrTem1 as $t) {
        $string .= $pres->armarCampoEnlaceImagenUnaColumna(600, utf8decode_sii($t["descripcion"]), '../../html/default/images/pack/edit16.png', 'javascript', 'validarSeleccion(\'' . $t["tiporegistro"] . '\',\'' . $t["tipotramite"] . '\',\'' . $t["subtipotramite"] . '\')');
    }
    $string .= $pres->cerrarTablaBorde();

    $arrBtnTipo = array();
    $arrBtnEnlace = array();
    $arrBtnImagen = array();
    $arrBtnTooltip = array();
    if ($_SESSION["generales"]["retorno"] == 'consultaexpedientes') {
        $arrBtnTipo[] = 'href';
        $arrBtnEnlace[] = '../../librerias/proceso/mregConsultaExpedientes.php?accion=mostrarexpedientes';
        $arrBtnImagen[] = 'Browser de consulta';
        $arrBtnTooltip[] = 'Retornar al browser de consulta de expedientes';
        $string .= $pres->armarBarraBotonesProcesoDinamico(600, $arrBtnTipo, $arrBtnImagen, $arrBtnEnlace, $arrBtnTooltip);
    }
    $string .= '</center>';

    unset($pres);
    $head = '<script type="text/javascript" src="' . TIPO_HTTP . HTTP_HOST . '/librerias/funciones/mregRadicarDocumentosSinCosto.js"></script>';
    mostrarCuerpoIE26a(array(), $head, '', 'Radicaci&oacute;n de documentos sin costo y correcciones', $string, 620, 400, '', 'Espere procesando....');
    exit();
}

/**
 * Funci&oacute;n que realiza las validaciones de las matr&iacute;culas digitadas, de encontrar error no permite continuar
 * en caso contrario muestra la grilla con los datos de las matr&iacute;culas
 */
function validar() 
{
    include_once $_SESSION["generales"]["pathabsoluto"] . '/configuracion/common.php';
    include_once $_SESSION["generales"]["pathabsoluto"] . '/configuracion/common' . $_SESSION["generales"]["codigoempresa"] . '.php';
    include_once $_SESSION["generales"]["pathabsoluto"] . '/librerias/funciones/generales.php';
    include_once $_SESSION["generales"]["pathabsoluto"] . '/librerias/funciones/sanarEntradas.class.php';
    include_once $_SESSION["generales"]["pathabsoluto"] . '/librerias/funciones/persistencia.php';
    include_once $_SESSION["generales"]["pathabsoluto"] . '/librerias/funciones/persistenciamreg.php';
    include_once $_SESSION["generales"]["pathabsoluto"] . '/librerias/funciones/capturaSoportesDocumentales.php';
    include_once $_SESSION["generales"]["pathabsoluto"] . '/librerias/funciones/capturaEvidenciasSipref.php';
    include_once $_SESSION["generales"]["pathabsoluto"] . '/librerias/funciones/Encoding.php';
    include_once $_SESSION["generales"]["pathabsoluto"] . '/librerias/presentacion/template.class.php';
    include_once $_SESSION["generales"]["pathabsoluto"] . '/librerias/presentacion/presentacion.class.php';

    //
    if (isset($_SESSION["vars"]["tiporegistro"])) {
        $_SESSION["tramite"]["tiporegistro"] = sanarEntradas::sanarString(utf8decode_sii($_SESSION["vars"]["tiporegistro"]));
    }
    if (isset($_SESSION["vars"]["tipotramite"])) {
        $_SESSION["tramite"]["tipotramite"] = sanarEntradas::sanarString(utf8decode_sii($_SESSION["vars"]["tipotramite"]));
    }
    if (isset($_SESSION["vars"]["subtipotramite"])) {
        $_SESSION["tramite"]["subtipotramite"] = sanarEntradas::sanarEntero(utf8decode_sii($_SESSION["vars"]["subtipotramite"]));
    }
    if (isset($_SESSION["vars"]["matricula"])) {
        $_SESSION["tramite"]["idmatriculabase"] = strtoupper(sanarEntradas::sanarString(utf8decode_sii($_SESSION["vars"]["matricula"])));
        $_SESSION["tramite"]["matriculabase"] = strtoupper(sanarEntradas::sanarString(utf8decode_sii($_SESSION["vars"]["matricula"])));
    }
    if (isset($_SESSION["vars"]["matricula"])) {
        $_SESSION["tramite"]["idexpedientebase"] = sanarEntradas::sanarString(utf8decode_sii($_SESSION["vars"]["matricula"]));
        $_SESSION["tramite"]["expedientebase"] = sanarEntradas::sanarString(utf8decode_sii($_SESSION["vars"]["matricula"]));
    }
    if (isset($_SESSION["vars"]["tipoidentificacion"])) {
        $_SESSION["tramite"]["tipoidentificacionbase"] = sanarEntradas::sanarEntero(utf8decode_sii($_SESSION["vars"]["tipoidentificacion"]));
    }
    if (isset($_SESSION["vars"]["identificacion"])) {
        $_SESSION["tramite"]["identificacionbase"] = sanarEntradas::sanarEntero(utf8decode_sii($_SESSION["vars"]["identificacion"]));
    }
    if (isset($_SESSION["vars"]["nombre"])) {
        $_SESSION["tramite"]["nombrebase"] = strtoupper(sanarEntradas::sanarString(utf8decode_sii($_SESSION["vars"]["nombre"])));
    }

    //
    if (isset($_SESSION["vars"]["tipodoc"])) {
        $_SESSION["tramite"]["tipodoc"] = sanarEntradas::sanarEntero(utf8decode_sii($_SESSION["vars"]["tipodoc"]));
    }
    if (isset($_SESSION["vars"]["numdoc"])) {
        $_SESSION["tramite"]["numdoc"] = strtoupper(sanarEntradas::sanarString(utf8decode_sii($_SESSION["vars"]["numdoc"])));
    }
    if (isset($_SESSION["vars"]["fechadoc"])) {
        $_SESSION["tramite"]["fechadoc"] = sanarEntradas::sanarEntero(utf8decode_sii($_SESSION["vars"]["fechadoc"]));
    }
    if (isset($_SESSION["vars"]["origendoc"])) {
        $_SESSION["tramite"]["origendoc"] = strtoupper(sanarEntradas::sanarString(utf8decode_sii($_SESSION["vars"]["origendoc"])));
    }
    if (isset($_SESSION["vars"]["mundoc"])) {
        $_SESSION["tramite"]["mundoc"] = sanarEntradas::sanarEntero(utf8decode_sii($_SESSION["vars"]["mundoc"]));
    }


    // Localiza el número de la evidencia sipref que asignará
    $continuar = 'si';
    while ($continuar == 'si') {
        $numrec = generarAleatorioAlfanumerico8();
        if (contarRegistros('mreg_sipref_controlevidencias', "nrocontrolsipref='" . $numrec . "'") == 0) {
            $continuar = 'no';
        }
    }
    $_SESSION["generales"]["nrocontrolsipref"] = $numrec;

    //
    grabarLiquidacionMreg();

    // 2017-06-07 : JINT : AlertaTemprana
    if ($_SESSION["tramite"]["idmatriculabase"] != '') {
        $arrTra = retornarRegistro('bas_tipotramites', "id='" . $_SESSION["tramite"]["tipotramite"] . "'");
        programarAlertaTemprana('RegMer', $_SESSION["tramite"]["idliquidacion"], $_SESSION["tramite"]["idmatriculabase"], '', $arrTra["descripcion"]);
    }

    mostrarRadicacion();
}

function mostrarRadicacion() 
{

    $numrec = $_SESSION["generales"]["nrocontrolsipref"];

    //
    $arrTem = retornarRegistros('mreg_maestro_errores', "1=1", "id");
    $arr = array();
    foreach ($arrTem as $t) {
        $arr[$t["id"]] = utf8decode_sii($t["descripcion"]);
    }
    $txtSelTE1 = armarSelectArreglo('Seleccione ...', $arr, $_SESSION["tramite"]["tipoerror1"]);
    $txtSelTE2 = armarSelectArreglo('Seleccione ...', $arr, $_SESSION["tramite"]["tipoerror2"]);
    $txtSelTE3 = armarSelectArreglo('Seleccione ...', $arr, $_SESSION["tramite"]["tipoerror3"]);


    //
    $arrTem = retornarRegistros('mreg_tablassirep', "idtabla='06'", "idcodigo");
    $arr = array();
    foreach ($arrTem as $t) {
        $arr[$t["idcodigo"]] = $t["idcodigo"] . ' - ' . utf8decode_sii($t["descripcion"]);
    }
    $txtSelTD = armarSelectArreglo('Seleccione ...', $arr, $_SESSION["tramite"]["tipodoc"]);

    //
    $arrTem = retornarRegistros('mreg_tipoidentificacion', "1=1", "id");
    $arrT = array();
    foreach ($arrTem as $t) {
        $arrT[$t["id"]] = $t["id"] . ' - ' . utf8decode_sii($t["descripcion"]);
    }
    $txtSelTDB = armarSelectArreglo('Seleccione ...', $arrT, $_SESSION["tramite"]["tipoidentificacionbase"]);
    $txtSelTDA = armarSelectArreglo('Seleccione ...', $arrT, $_SESSION["tramite"]["tipoidentificacionaceptante"]);
    $txtSelTDD = armarSelectArreglo('Seleccione ...', $arrT, $_SESSION["tramite"]["tipoidentificaciondemandante"]);
    $txtSelMun = armarSelectArreglo('Seleccione ...', $_SESSION["municipios"], $_SESSION["tramite"]["mundoc"]);
    $txtSelMunRr = armarSelectArreglo('Seleccione ...', $_SESSION["municipios"], $_SESSION["tramite"]["municipiorr"]);
    $txtSelMunPqr = armarSelectArreglo('Seleccione ...', $_SESSION["municipios"], $_SESSION["tramite"]["municipiopqr"]);
    $txtSelMunAce = armarSelectArreglo('Seleccione ...', $_SESSION["municipios"], $_SESSION["tramite"]["municipioaceptante"]);

    //
    $arrTemX = retornarRegistro('mreg_maestro_tramites_sin_costo', "tiporegistro='" . $_SESSION["tramite"]["tiporegistro"] . "' and tipotramite='" . $_SESSION["tramite"]["tipotramite"] . "' and subtipotramite='" . $_SESSION["tramite"]["subtipotramite"] . "'");

    // Arma pantalla de presentacion
    $string = '';
    $pres = new presentacion();

    //
    $string .= '<center>';

    $string .= $pres->armarCampoTextoOculto('_tipomenu', $_SESSION["generales"]["tipomenu"]);
    $string .= $pres->armarCampoTextoOculto('_tipohttp', TIPO_HTTP);
    $string .= $pres->armarCampoTextoOculto('_httphost', HTTP_HOST);
    $string .= $pres->armarCampoTextoOculto('_tipotramite', $_SESSION["tramite"]["tipotramite"]);

    $string .= $pres->armarLineaTextoEnriquecido(600, 'A continuaci&oacute;n se desplieguan los datos del tr&aacute;mite a radicar, por favor revise la informaci&oacute;n antes de continuar.', 'center', '14', '#800000', 'si', 'parte1', '1');
    $string .= $pres->abrirTablaBorde(600);
    $string .= $pres->armarCampoTextoProtegido2Lineas(600, 'Tipo de tr&aacute;mite', '_tipotramite', 40, 50, $_SESSION["tramite"]["tipotramite"]);
    $string .= $pres->armarCampoTextoProtegido2Lineas(600, 'Subtipo de tr&aacute;mite', '_subtipotramite', 2, 2, $_SESSION["tramite"]["subtipotramite"]);
    $string .= $pres->armarCampoTextoProtegido2Lineas(600, 'Nombre', '_nombretramite', 40, 128, utf8decode_sii($arrTemX["descripcion"]));
    $string .= $pres->armarCampoTextoProtegido2Lineas(600, 'Id liquidaci&oacute;n', '_idliquidacion', 20, 20, $_SESSION["tramite"]["idliquidacion"]);
    $string .= $pres->armarCampoTextoProtegido2Lineas(600, 'N&uacute;mero recuperaci&oacute;n', '_numerorecuperacion', 20, 20, $_SESSION["tramite"]["numerorecuperacion"]);
    $string .= $pres->cerrarTablaBorde();
    $string .= '<br><br>';

    $string .= $pres->armarLineaTextoEnriquecido(600, 'Datos del expediente afectado.', 'center', '14', '#800000', 'si', 'parte2', '2');
    $string .= $pres->abrirTablaBorde(600);
    $string .= $pres->armarCampoTexto2Lineas(600, 'Matr&iacute;cula', 'no', '_idmatricula', 10, 10, $_SESSION["tramite"]["idmatriculabase"]);
    $string .= $pres->armarCampoTexto2Lineas(600, 'Proponente', 'no', '_idproponente', 10, 10, $_SESSION["tramite"]["idproponentebase"]);
    if ($_SESSION["tramite"]["organizacionbase"] != '02' 
        && $_SESSION["tramite"]["categoriabase"] != '2' && $_SESSION["tramite"]["categoriabase"] != '3'
    ) {
        $string .= $pres->armarCampoSelect2Lineas(600, 'Tipo identificaci&oacute;n', 'no', '_tipoidentificacionbase', 30, 30, $txtSelTDB, $_SESSION["tramite"]["tipoidentificacionbase"], '');
        $string .= $pres->armarCampoTexto2Lineas(600, 'Nro. identificaci&oacute;n', 'no', '_identificacionbase', 20, 20, $_SESSION["tramite"]["identificacionbase"]);
    } else {
        $string .= $pres->armarCampoTextoOculto('_tipoidentificacionbase', $_SESSION["tramite"]["tipoidentificacionbase"]);
        $string .= $pres->armarCampoTextoOculto('_identificacionbase', $_SESSION["tramite"]["identificacionbase"]);
    }
    $string .= $pres->armarCampoTexto2Lineas(600, 'Nombre', 'no', '_nombrebase', 50, 128, $_SESSION["tramite"]["nombrebase"]);
    $string .= $pres->cerrarTablaBorde();
    $string .= '<br><br>';

    $string .= $pres->armarLineaTextoEnriquecido(600, 'Datos del documento radicado.', 'center', '14', '#800000', 'si', 'parte3', '3');
    $string .= $pres->abrirTablaBorde(600);
    $string .= $pres->armarCampoSelect2Lineas(600, 'Tipo documento', 'no', '_tipodoc', 30, 30, $txtSelTD, $_SESSION["tramite"]["tipodoc"], '');
    $string .= $pres->armarCampoTexto2Lineas(600, 'N&uacute;mero documento', 'no', '_numdoc', 10, 10, $_SESSION["tramite"]["numdoc"]);
    $string .= $pres->armarCampoTexto2Lineas(600, 'Fecha documento (AAAAMMDD)', 'no', '_fechadoc', 10, 10, $_SESSION["tramite"]["fechadoc"]);
    $string .= $pres->armarCampoTexto2Lineas(600, 'Origen documento', 'no', '_origendoc', 40, 128, $_SESSION["tramite"]["origendoc"]);
    $string .= $pres->armarCampoSelect2Lineas(600, 'Municipio', 'no', '_mundoc', 30, 30, $txtSelMun, $_SESSION["tramite"]["mundoc"], '');
    $string .= $pres->cerrarTablaBorde();
    $string .= '<br><br>';


    //
    if ($arrTemX["pideaceptacion"] == 'S') {
        $string .= $pres->armarCampoTextoOculto('_datosapedir', 'aceptante');
        $string .= $pres->armarLineaTextoEnriquecido(600, 'Indique los datos de la carta de aceptaci&oacute;n', 'center', '14', '#800000', 'si', 'parte4', '4');
        $string .= $pres->abrirTablaBorde(600);
        $string .= $pres->armarCampoSelect2Lineas(600, 'Tipo identificaci&oacute;n', 'si', '_tipoidentificacionaceptante', 30, 30, $txtSelTDA, $_SESSION["tramite"]["tipoidentificacionaceptante"], '');
        $string .= $pres->armarCampoTexto2Lineas(600, 'N&uacute;mero de identificaci&oacute;n ', 'si', '_identificacionaceptante', 20, 20, $_SESSION["tramite"]["identificacionaceptante"], '');
        $string .= $pres->armarCampoTexto2Lineas(600, 'Fecha dcto. Identidad ', 'si', '_fechadocideaceptante', 10, 10, $_SESSION["tramite"]["fechadocideaceptante"], '');
        $string .= $pres->armarCampoTexto2Lineas(600, 'Nombre (apellidos y nombres)', 'si', '_nombreaceptante', 40, 128, $_SESSION["tramite"]["nombreaceptante"], '');
        $string .= $pres->armarCampoTexto2Lineas(600, 'Primer nombre', 'si', '_nombre1aceptante', 40, 128, $_SESSION["tramite"]["nombre1aceptante"], '');
        $string .= $pres->armarCampoTexto2Lineas(600, 'Segundo nombre', 'si', '_nombre2aceptante', 40, 128, $_SESSION["tramite"]["nombre2aceptante"], '');
        $string .= $pres->armarCampoTexto2Lineas(600, 'Primer apellido', 'si', '_apellido1aceptante', 40, 128, $_SESSION["tramite"]["apellido1aceptante"], '');
        $string .= $pres->armarCampoTexto2Lineas(600, 'Segundo apellido', 'si', '_apellido2aceptante', 40, 128, $_SESSION["tramite"]["apellido2aceptante"], '');
        $string .= $pres->armarCampoTexto2Lineas(600, 'Cargo ', 'si', '_cargoaceptante', 40, 128, $_SESSION["tramite"]["cargoaceptante"], '');
        $string .= $pres->armarCampoTexto2Lineas(600, 'Dirección ', 'si', '_direccionaceptante', 50, 128, $_SESSION["tramite"]["direccionaceptante"], '');
        $string .= $pres->armarCampoSelect2Lineas(600, 'Municipio:', 'no', '_municipioaceptante', 5, 5, $txtSelMunAce, $_SESSION["tramite"]["municipioaceptante"]);
        $string .= $pres->armarCampoTexto2Lineas(600, 'Email ', 'si', '_emailaceptante', 50, 128, $_SESSION["tramite"]["emailaceptante"], '');
        $string .= $pres->armarCampoTexto2Lineas(600, 'Tel&eacute;fono fijo ', 'si', '_telefonoaceptante', 20, 20, $_SESSION["tramite"]["telefonoaceptante"], '');
        $string .= $pres->armarCampoTexto2Lineas(600, 'N&uacute;mero celular ', 'si', '_celularaceptante', 20, 20, $_SESSION["tramite"]["celularaceptante"], '');
        $string .= $pres->cerrarTablaBorde();
        $string .= '<br><br>';
    } else {
        $string .= $pres->armarCampoTextoOculto('_tipoidentificacionaceptante', '');
        $string .= $pres->armarCampoTextoOculto('_identificacionaceptante', '');
        $string .= $pres->armarCampoTextoOculto('_nombre1aceptante', '');
        $string .= $pres->armarCampoTextoOculto('_nombre2aceptante', '');
        $string .= $pres->armarCampoTextoOculto('_apellido1aceptante', '');
        $string .= $pres->armarCampoTextoOculto('_apellido2aceptante', '');
        $string .= $pres->armarCampoTextoOculto('_direccionaceptante', '');
        $string .= $pres->armarCampoTextoOculto('_municipioaceptante', '');
        $string .= $pres->armarCampoTextoOculto('_emailaceptante', '');
        $string .= $pres->armarCampoTextoOculto('_telefonoaceptante', '');
        $string .= $pres->armarCampoTextoOculto('_celularaceptante', '');
        $string .= $pres->armarCampoTextoOculto('_cargoaceptante', '');
        $string .= $pres->armarCampoTextoOculto('_fechadocideaceptante', '');
    }

    //
    if ($arrTemX["pidecorreccion"] == 'S') {
        $_SESSION["tramite"]["tipocorreccion"] = $_SESSION["tramite"]["subtipotramite"];
        $string .= $pres->armarLineaTextoEnriquecido(600, 'Indique el motivo de la correcci&oacute;n', 'center', '14', '#800000', 'si', 'parte4', '4');
        $string .= $pres->abrirTablaBorde(600);
        $string .= $pres->armarCampoMultiTexto2Lineas(600, 'Descripci&oacute;n del motivo de la correcci&oacute;n', 'si', '_motivocorreccion', 5, 40, $_SESSION["tramite"]["motivocorreccion"], '');
        $string .= $pres->armarCampoSelect2Lineas(600, 'Tipo error', 'si', '_tipoerror1', 30, 30, $txtSelTE1, $_SESSION["tramite"]["tipoerror1"], '');
        $string .= $pres->armarCampoSelect2Lineas(600, 'Tipo error', 'no', '_tipoerror2', 30, 30, $txtSelTE2, $_SESSION["tramite"]["tipoerror2"], '');
        $string .= $pres->armarCampoSelect2Lineas(600, 'Tipo error', 'no', '_tipoerror3', 30, 30, $txtSelTE3, $_SESSION["tramite"]["tipoerror3"], '');
        $string .= '<br>';
        $string .= $pres->armarLineaTextoEnriquecido(600, 'Datos de la persona que solicita la corrección', 'center', '14', '#800000', 'si', '', '');
        $string .= '<br>';
        $string .= $pres->armarCampoSelect2Lineas(600, 'Tipo identificaci&oacute;n', 'si', '_tipoidentificacioncor', 30, 30, $txtSelTDD, $_SESSION["tramite"]["tipoidentificacioncor"], '');
        $string .= $pres->armarCampoTexto2LineasOnBlur(600, 'N&uacute;mero de identificaci&oacute;n (En caso de Nit incluya el d&iacute;gito de verificac&oacute;n) ', 'si', '_identificacioncor', 20, 20, $_SESSION["tramite"]["identificacioncor"], 'asignaCor()', '');
        $string .= $pres->armarCampoTexto2Lineas(600, 'Primer nombre', 'si', '_nombre1cor', 40, 128, $_SESSION["tramite"]["nombre1cor"], '');
        $string .= $pres->armarCampoTexto2Lineas(600, 'Segundo nombre', 'si', '_nombre2cor', 40, 128, $_SESSION["tramite"]["nombre2cor"], '');
        $string .= $pres->armarCampoTexto2Lineas(600, 'Primer apellido', 'si', '_apellido1cor', 40, 128, $_SESSION["tramite"]["apellido1cor"], '');
        $string .= $pres->armarCampoTexto2Lineas(600, 'Segundo apellido', 'si', '_apellido2cor', 40, 128, $_SESSION["tramite"]["apellido2cor"], '');
        $string .= $pres->armarCampoTexto2Lineas(600, 'Dirección ', 'si', '_direccioncor', 50, 128, $_SESSION["tramite"]["direccioncor"], '');
        $string .= $pres->armarCampoSelect2Lineas(600, 'Municipio:', 'no', '_municipiocor', 5, 5, $txtSelMunPqr, $_SESSION["tramite"]["municipiocor"]);
        $string .= $pres->armarCampoTexto2Lineas(600, 'Email ', 'si', '_emailcor', 50, 128, $_SESSION["tramite"]["emailcor"], '');
        $string .= $pres->armarCampoTexto2Lineas(600, 'Tel&eacute;fono fijo ', 'si', '_telefonocor', 20, 20, $_SESSION["tramite"]["telefonocor"], '');
        $string .= $pres->armarCampoTexto2Lineas(600, 'N&uacute;mero celular ', 'si', '_celularcor', 20, 20, $_SESSION["tramite"]["celularcor"], '');

        $string .= $pres->cerrarTablaBorde();
        $string .= '<br><br>';
    } else {
        $string .= $pres->armarCampoTextoOculto('_motivocorreccion', '');
        $string .= $pres->armarCampoTextoOculto('_tipoerror1', '');
        $string .= $pres->armarCampoTextoOculto('_tipoerror2', '');
        $string .= $pres->armarCampoTextoOculto('_tipoerror3', '');
        $string .= $pres->armarCampoTextoOculto('_tipoidentificacioncor', '');
        $string .= $pres->armarCampoTextoOculto('_identificacioncor', '');
        $string .= $pres->armarCampoTextoOculto('_nombre1cor', '');
        $string .= $pres->armarCampoTextoOculto('_nombre1cor', '');
        $string .= $pres->armarCampoTextoOculto('_nombre2cor', '');
        $string .= $pres->armarCampoTextoOculto('_apellido1cor', '');
        $string .= $pres->armarCampoTextoOculto('_apellido2cor', '');
        $string .= $pres->armarCampoTextoOculto('_direccioncor', '');
        $string .= $pres->armarCampoTextoOculto('_municipiocor', '');
        $string .= $pres->armarCampoTextoOculto('_emailcor', '');
        $string .= $pres->armarCampoTextoOculto('_telefonocor', '');
        $string .= $pres->armarCampoTextoOculto('_celularcor', '');
    }

    //
    if ($arrTemX["pidepqr"] == 'S') {
        $string .= $pres->armarCampoTextoOculto('_datosapedir', 'pqr');
        $string .= $pres->armarLineaTextoEnriquecido(600, 'Describa en forma detallada la petici&oacute;n, queja, reclamo, sugerencia o felicitaci&oacute;n', 'center', '14', '#800000', 'si', 'parte4', '4');
        $string .= $pres->abrirTablaBorde(600);
        $string .= $pres->armarCampoMultiTexto2Lineas(600, 'Detalle de la petici&oacute;n, queja, reclamo, sugerencia o felicitaci&oacute;n', 'si', '_descripcionpqr', 5, 40, $_SESSION["tramite"]["descripcionpqr"], '');
        $string .= $pres->cerrarTablaBorde();
        $string .= '<br>';
        $string .= $pres->armarLineaTextoEnriquecido(600, 'Datos de la persona que interpone la PQRSF', 'center', '14', '#800000', 'si', 'parte5', '5');
        $string .= $pres->abrirTablaBorde(600);
        $string .= $pres->armarCampoSelect2Lineas(600, 'Tipo identificaci&oacute;n', 'si', '_tipoidentificacionpqr', 30, 30, $txtSelTDD, $_SESSION["tramite"]["tipoidentificacionpqr"], '');
        $string .= $pres->armarCampoTexto2LineasOnBlur(600, 'N&uacute;mero de identificaci&oacute;n (En caso de Nit incluya el d&iacute;gito de verificac&oacute;n) ', 'si', '_identificacionpqr', 20, 20, $_SESSION["tramite"]["identificacionpqr"], 'asignaPqr()', '');
        $string .= $pres->armarCampoTexto2Lineas(600, 'Primer nombre', 'si', '_nombre1pqr', 40, 128, $_SESSION["tramite"]["nombre1pqr"], '');
        $string .= $pres->armarCampoTexto2Lineas(600, 'Segundo nombre', 'si', '_nombre2pqr', 40, 128, $_SESSION["tramite"]["nombre2pqr"], '');
        $string .= $pres->armarCampoTexto2Lineas(600, 'Primer apellido', 'si', '_apellido1pqr', 40, 128, $_SESSION["tramite"]["apellido1pqr"], '');
        $string .= $pres->armarCampoTexto2Lineas(600, 'Segundo apellido', 'si', '_apellido2pqr', 40, 128, $_SESSION["tramite"]["apellido2pqr"], '');
        $string .= $pres->armarCampoTexto2Lineas(600, 'Dirección ', 'si', '_direccionpqr', 50, 128, $_SESSION["tramite"]["direccionpqr"], '');
        $string .= $pres->armarCampoSelect2Lineas(600, 'Municipio:', 'no', '_municipiopqr', 5, 5, $txtSelMunPqr, $_SESSION["tramite"]["municipiopqr"]);
        $string .= $pres->armarCampoTexto2Lineas(600, 'Email ', 'si', '_emailpqr', 50, 128, $_SESSION["tramite"]["emailpqr"], '');
        $string .= $pres->armarCampoTexto2Lineas(600, 'Tel&eacute;fono fijo ', 'si', '_telefonopqr', 20, 20, $_SESSION["tramite"]["telefonopqr"], '');
        $string .= $pres->armarCampoTexto2Lineas(600, 'N&uacute;mero celular ', 'si', '_celularpqr', 20, 20, $_SESSION["tramite"]["celularpqr"], '');
        $string .= $pres->cerrarTablaBorde();
        $string .= '<br><br>';
    } else {
        $string .= $pres->armarCampoTextoOculto('_descripcionpqr', '');
        $string .= $pres->armarCampoTextoOculto('_tipoidentificacionpqr', '');
        $string .= $pres->armarCampoTextoOculto('_identificacionpqr', '');
        $string .= $pres->armarCampoTextoOculto('_nombre1pqr', '');
        $string .= $pres->armarCampoTextoOculto('_nombre1pqr', '');
        $string .= $pres->armarCampoTextoOculto('_nombre2pqr', '');
        $string .= $pres->armarCampoTextoOculto('_apellido1pqr', '');
        $string .= $pres->armarCampoTextoOculto('_apellido2pqr', '');
        $string .= $pres->armarCampoTextoOculto('_direccionpqr', '');
        $string .= $pres->armarCampoTextoOculto('_municipiopqr', '');
        $string .= $pres->armarCampoTextoOculto('_emailpqr', '');
        $string .= $pres->armarCampoTextoOculto('_telefonopqr', '');
        $string .= $pres->armarCampoTextoOculto('_celularpqr', '');
    }

    if ($arrTemX["piderr"] == 'S') {
        $string .= $pres->armarCampoTextoOculto('_datosapedir', 'rr');
        $string .= $pres->armarLineaTextoEnriquecido(600, 'Describa en forma general el contenido del recurso de reposición', 'center', '14', '#800000', 'si', 'parte4', '4');
        $string .= $pres->abrirTablaBorde(600);
        $string .= $pres->armarCampoMultiTexto2Lineas(600, 'Detalle del recurso', 'si', '_descripcionrr', 5, 40, $_SESSION["tramite"]["descripcionrr"], '');
        $string .= '<br>';
        $string .= $pres->armarLineaTextoEnriquecido(600, 'Datos de la persona que interpone el recurso de reposición', 'center', '12', '#800000', 'no');
        $string .= '<br>';
        $string .= $pres->armarCampoSelect2Lineas(600, 'Tipo identificaci&oacute;n', 'si', '_tipoidentificacionrr', 30, 30, $txtSelTDD, $_SESSION["tramite"]["tipoidentificacionrr"], '');
        $string .= $pres->armarCampoTexto2LineasOnBlur(600, 'N&uacute;mero de identificaci&oacute;n (En caso de Nit incluya el d&iacute;gito de verificac&oacute;n) ', 'si', '_identificacionrr', 20, 20, $_SESSION["tramite"]["identificacionrr"], 'asignaRr()', '');
        $string .= $pres->armarCampoTexto2Lineas(600, 'Primer nombre', 'si', '_nombre1rr', 40, 128, $_SESSION["tramite"]["nombre1rr"], '');
        $string .= $pres->armarCampoTexto2Lineas(600, 'Segundo nombre', 'si', '_nombre2rr', 40, 128, $_SESSION["tramite"]["nombre2rr"], '');
        $string .= $pres->armarCampoTexto2Lineas(600, 'Primer apellido', 'si', '_apellido1rr', 40, 128, $_SESSION["tramite"]["apellido1rr"], '');
        $string .= $pres->armarCampoTexto2Lineas(600, 'Segundo apellido', 'si', '_apellido2rr', 40, 128, $_SESSION["tramite"]["apellido2rr"], '');
        $string .= $pres->armarCampoTexto2Lineas(600, 'Dirección ', 'si', '_direccionrr', 50, 128, $_SESSION["tramite"]["direccionrr"], '');
        $string .= $pres->armarCampoSelect2Lineas(600, 'Municipio:', 'no', '_municipiorr', 5, 5, $txtSelMunRr, $_SESSION["tramite"]["municipiorr"]);
        $string .= $pres->armarCampoTexto2Lineas(600, 'Email ', 'si', '_emailrr', 50, 128, $_SESSION["tramite"]["emailrr"], '');
        $string .= $pres->armarCampoTexto2Lineas(600, 'Tel&eacute;fono fijo ', 'si', '_telefonorr', 20, 20, $_SESSION["tramite"]["telefonorr"], '');
        $string .= $pres->armarCampoTexto2Lineas(600, 'N&uacute;mero celular ', 'si', '_celularrr', 20, 20, $_SESSION["tramite"]["celularrr"], '');
        $string .= $pres->cerrarTablaBorde();
        $string .= '<br><br>';
    } else {
        $string .= $pres->armarCampoTextoOculto('_descripcionrr', '');
        $string .= $pres->armarCampoTextoOculto('_tipoidentificacionrr', '');
        $string .= $pres->armarCampoTextoOculto('_identificacionrr', '');
        $string .= $pres->armarCampoTextoOculto('_nombre1rr', '');
        $string .= $pres->armarCampoTextoOculto('_nombre2rr', '');
        $string .= $pres->armarCampoTextoOculto('_apellido1rr', '');
        $string .= $pres->armarCampoTextoOculto('_apellido2rr', '');
        $string .= $pres->armarCampoTextoOculto('_direccionrr', '');
        $string .= $pres->armarCampoTextoOculto('_municipiorr', '');
        $string .= $pres->armarCampoTextoOculto('_emailrr', '');
        $string .= $pres->armarCampoTextoOculto('_telefonorr', '');
        $string .= $pres->armarCampoTextoOculto('_celularrr', '');
    }


    //
    if ($arrTemX["pidetextolibre"] == 'S') {
        $string .= $pres->armarLineaTextoEnriquecido(600, 'Complemente el detalle del requerimiento (si lo estima conveniente)', 'center', '14', '#800000', 'si', '', '#');
        $string .= $pres->abrirTablaBorde(600);
        $string .= $pres->armarCampoMultiTexto2Lineas(600, 'Detalle', 'si', '_textolibre', 5, 40, $_SESSION["tramite"]["textolibre"], '');
        $string .= $pres->cerrarTablaBorde();
        $string .= '<br><br>';
    } else {
        $string .= $pres->armarCampoTextoOculto('_textolibre', '');
    }

    // Revisa si hay soportes
    $arrTem = retornarRegistros('mreg_anexos_liquidaciones', "idliquidacion=" . $_SESSION["tramite"]["idliquidacion"] . " and tipoanexo <> '503' and eliminado <> 'SI'", "idanexo");
    if (!empty($arrTem)) {
        $string .= $pres->armarLineaTextoEnriquecido(600, 'Soportes anexados al tr&aacute;mite', 'center', '14', '#800000', 'si', '', '#');
        $string .= $pres->abrirTablaBorde(600);
        foreach ($arrTem as $a) {
            $string .= '<center><a href="' . TIPO_HTTP . HTTP_HOST . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/' . $a["path"] . '/' . $a["idanexo"] . '.' . $a["tipoarchivo"] . '" target="_blank">' . $a["idanexo"] . '.' . $a["tipoarchivo"] . '</a>&nbsp;&nbsp;';
            $string .= '<a href="javascript:borrarAnexo(\'' . base64_encode($a["idanexo"]) . '\');"><img src="' . TIPO_HTTP . HTTP_HOST . '/html/default/images/pack/delete16.png"></a>';
            $string .= '</center><br>';
        }
        $string .= $pres->cerrarTablaBorde();
        $string .= '<br><br>';
    }

    //
    // Captura Anexos del PQR
    $string .= $pres->armarLineaTextoEnriquecido(600, 'Por favor anexe, en formato PDF los soportes que estime convenientes', 'center', '14', '#800000', 'si', '', '#');
    $string .= $pres->armarLineaTextoEnriquecido(600, '(Arrastre los mismos al siguiente recuadro)', 'center', '12', '#800000', 'no', '', '');
    $string .= $pres->abrirTablaBorde(600);
    $string .= '<form action="../../librerias/proceso/mregRadicarDocumentosSinCosto.php?accion=loadfile" class="dropzone" id="my-awesome-dropzone"></form>';
    $string .= $pres->cerrarTablaBorde();
    $string .= '<br><br>';


    $_SESSION["tramite"]["fotoabsoluta"] = 'images/sii/people.png';
    $_SESSION["tramite"]["foto"] = '../../images/sii/people.png';
    $_SESSION["tramite"]["cedula1absoluta"] = 'images/sii/people.png';
    $_SESSION["tramite"]["cedula1"] = '../../images/sii/people.png';
    $_SESSION["tramite"]["cedula2absoluta"] = 'images/sii/people.png';
    $_SESSION["tramite"]["cedula2"] = '../../images/sii/people.png';

    //
    if (file_exists('../../' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/fotoEvidencias/' . substr($numrec, 0, 3) . '/' . $numrec . '-Foto.jpg')) {
        $_SESSION["tramite"]["foto"] = '../../' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/fotoEvidencias/' . substr($numrec, 0, 3) . '/' . $numrec . '-Foto.jpg';
        $_SESSION["tramite"]["fotoabsoluta"] = PATH_ABSOLUTO_SITIO . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/fotoEvidencias/' . substr($numrec, 0, 3) . '/' . $numrec . '-Foto.jpg';
    }
    if (file_exists('../../' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/fotoEvidencias/' . substr($numrec, 0, 3) . '/' . $numrec . '-Cedula1.jpg')) {
        $_SESSION["tramite"]["cedula1"] = '../../' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/fotoEvidencias/' . substr($numrec, 0, 3) . '/' . $numrec . '-Cedula1.jpg';
        $_SESSION["tramite"]["cedula1absoluta"] = PATH_ABSOLUTO_SITIO . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/fotoEvidencias/' . substr($numrec, 0, 3) . '/' . $numrec . '-Cedula1.jpg';
    }
    if (file_exists('../../' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/fotoEvidencias/' . substr($numrec, 0, 3) . '/' . $numrec . '-Cedula2.jpg')) {
        $_SESSION["tramite"]["cedula2"] = '../../' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/fotoEvidencias/' . substr($numrec, 0, 3) . '/' . $numrec . '-Cedula2.jpg';
        $_SESSION["tramite"]["cedula2absoluta"] = PATH_ABSOLUTO_SITIO . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/fotoEvidencias/' . substr($numrec, 0, 3) . '/' . $numrec . '-Cedula2.jpg';
    }

    $tram = retornarRegistro('bas_tipotramites', "id='" . $_SESSION["tramite"]["tipotramite"] . "'");
    if ($tram["controlevidenciasipref"] != 'si') {
        $string .= $pres->armarCampoTextoOculto('_nrocontrolsipref', '');
        $string .= $pres->armarCampoTextoOculto('_nrocontrolsiprefbio', '');
        $string .= $pres->armarCampoTextoOculto('_seleccionosipref', '');
        $string .= $pres->armarCampoTextoOculto('_seleccionosiprefbio', '');
    } else {
        $string .= $pres->armarCampoTextoOculto('_seleccionosipref', '');
        $string .= $pres->armarLineaTextoEnriquecido(600, 'Captura de evidencias SIPREF', 'center', 16, '#800000', 'si', '', '#');
        $arrBtnTipo = array('javascript', 'javascript');
        $arrBtnImagen = array('Evidencia Fotográfica', 'Evidencia Biométrica');
        $arrBtnEnlace = array('activarSiprefFotografico();', 'activarSiprefBiometrico();');
        $arrBtnToolTip = array('Evidencia Fotográfica', 'Evidencia Biométrica');
        $string .= $pres->armarBarraBotonesProcesoDinamico(600, $arrBtnTipo, $arrBtnImagen, $arrBtnEnlace, $arrBtnToolTip);
        $string .= '<br>';
        $string .= $pres->armarDiv('_div_evidencia_fotografica', 'none');
        $txt1 = 'Proceda a realizar la captura de las im&aacute;genes que quedan como evidencia de la persona que radic&oacute; el tr&aacute;mite..';
        $string .= '<br>';
        $string .= $pres->armarLineaTextoEnriquecido(600, $txt1, 'center', 14, '#800000', 'si');
        $string .= '<br>';
        $string .= $pres->armarCampoTextoProtegido2Lineas(600, 'Nro Control SIPREF', '_nrocontrolsipref', 20, 20, $_SESSION["generales"]["nrocontrolsipref"]);
        $string .= '<br>';
        $string .= $pres->armarTablaAbrir(600);
        $string .= $pres->armarFilaTablaAbrir(200);
        $string .= $pres->abrirTablaBorde(200);
        $string .= $pres->armarCampoMostrarImagenRadicadorFoto(200, $_SESSION["tramite"]["foto"]);
        $path = '../../' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/fotoEvidencias/' . substr($numrec, 0, 3) . '/' . $numrec . '-Foto.jpg';
        $string .= $pres->armarBarraBotonesProcesoDinamico(200, array('javascript', 'javascript'), array('Tomar foto', 'Refrescar'), array('capturarFotoEvidencia(\'' . $numrec . '\',\'' . $_SESSION["tramite"]["fotoabsoluta"] . '\');', 'refrescarFotoEvidencia(\'' . $path . '\');'), array('Foto del usuario', 'Refrescar'), array(), '', 1);
        $string .= $pres->cerrarTablaBorde();
        $string .= $pres->armarFilaTablaCerrar();
        $string .= $pres->armarFilaTablaAbrir(200);
        $string .= $pres->abrirTablaBorde(200);
        $string .= $pres->armarCampoMostrarImagenRadicadorCedula1(200, $_SESSION["tramite"]["cedula1"]);
        $path = '../../' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/fotoEvidencias/' . substr($numrec, 0, 3) . '/' . $numrec . '-Cedula1.jpg';
        $string .= $pres->armarBarraBotonesProcesoDinamico(200, array('javascript', 'javascript'), array('Cara 1 C&eacute;dula', 'Refrescar'), array('capturarFotoEvidenciaCedula1(\'' . $numrec . '\',\'' . $_SESSION["tramite"]["cedula1absoluta"] . '\');', 'refrescarFotoEvidenciaCedula1(\'' . $path . '\');'), array('Cara 1 de la c&eacute;dula', 'Refrescar'), array(), '', 1);
        $string .= $pres->cerrarTablaBorde();
        $string .= $pres->armarFilaTablaCerrar();
        $string .= $pres->armarFilaTablaAbrir(200);
        $string .= $pres->abrirTablaBorde(200);
        $string .= $pres->armarCampoMostrarImagenRadicadorCedula2(200, $_SESSION["tramite"]["cedula2"]);
        $path = '../../' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/fotoEvidencias/' . substr($numrec, 0, 3) . '/' . $numrec . '-Cedula2.jpg';
        $string .= $pres->armarBarraBotonesProcesoDinamico(200, array('javascript', 'javascript'), array('Cara 2 C&eacute;dula', 'Refrescar'), array('capturarFotoEvidenciaCedula2(\'' . $numrec . '\',\'' . $_SESSION["tramite"]["cedula2absoluta"] . '\');', 'refrescarFotoEvidenciaCedula2(\'' . $path . '\');'), array('Cara 2 de la c&eacute;dula', 'Refrescar'), array(), '', 1);
        $string .= $pres->cerrarTablaBorde();
        $string .= $pres->armarFilaTablaCerrar();
        $string .= $pres->armarTablaCerrar();
        $string .= '<br><bR>';
        $txt1 = 'Por favor realice la verificaci&oacute;n del documento de identidad, de la persona que est&aacute; parada frente a usted, ';
        $txt1 .= ' en el sistema de Informaci&oacute;n de la Registradur&iacute;a Nacional ';
        $txt1 .= 'del estado Civil. <a href="http://www3.registraduria.gov.co/certificado/menu.aspx" target="_blank">(Ver este enlace)</a>. Y cargue al sistema de informaci&oacute;n el PDF con la certificaci&oacute;n. ';
        $string .= $pres->armarLineaTextoEnriquecido(600, $txt1, 'center', 14, '#800000', 'si');
        $string .= '<bR>';
        if (!file_exists($_SESSION["generales"]["pathabsoluto"] . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/fotoEvidencias/' . substr($numrec, 0, 3) . '/' . $numrec . '-Rnec.pdf')) {
            $pdf = '../../' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/fotoEvidencias/' . substr($numrec, 0, 3) . '/' . $numrec . '-Rnec.pdf';
            $string .= $pres->armarDiv('div_evidenciarnec', 'block');
            $string .= $pres->armarLineaTextoEnriquecido(600, '!!! No se ha cargado la evidencia de la verificaci&oacute;n ante la registradur&iacute;a !!!', 'center', '14', '#800000', 'si');
            $string .= $pres->cerrarDiv();
            $string .= $pres->armarCampoUploadR(600, 60, 40, 'Evidencia PDF descargada de la R.N.E.C.', 'no', '_evidenciarnec', 'rnec.radicador', $pdf, $numrec);
        } else {
            $pdf = '../../' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/fotoEvidencias/' . substr($numrec, 0, 3) . '/' . $numrec . '-Rnec.pdf';
            $string .= $pres->armarDiv('div_evidenciarnec', 'block');
            $string .= '<center><a href="' . $pdf . '" target="_blank"><img src="../../html/default/images/pdf.png" width=64" height=64" border="3"></a></center><br>';
            $string .= $pres->cerrarDiv();
            $string .= $pres->armarCampoUploadR(600, 60, 40, 'Modificar la evidencia en PDF de la R.N.E.C.', 'no', '_evidenciarnec', 'rnec.evidencia', $pdf, $numrec);
        }
        $string .= '<br><br>';
        $string .= $pres->cerrarDiv();

        $string .= $pres->armarDiv('_div_evidencia_biometrica', 'none');
        $string .= $pres->armarCampoTextoOculto('_seleccionosiprefbio', '');
        $txt1 = 'Por favor indique el n&uacute;mero de control sipref asociado con la huella dactilar digitalizada a trav&eacute;s ';
        $txt1 .= 'del componente biom&eacute;trico. (Puede hacer copy-page desde el componente biom&eacute;trico)';
        $string .= $pres->armarLineaTextoEnriquecido(600, $txt1, 'center', 14, '#800000', 'si');
        $string .= $pres->armarLineaTexto(300, '');
        $string .= $pres->armarLineaTexto(300, '');
        $string .= $pres->armarCampoTexto12R(600, 50, 50, 'Nro. Control Biom&eacute;trico', 'no', '_nrocontrolsiprefbio', 10, 10, '', '');
        $string .= $pres->armarLinea(600);

        $string .= $pres->cerrarDiv();
    }

    $string .= '<br><bR>';

    //       
    $arrBtnTipo = array();
    $arrBtnImagen = array();
    $arrBtnEnlace = array();
    $arrBtnToolTip = array();

    $arrBtnTipo[] = 'javascript';
    $arrBtnImagen[] = 'Radicar Tr&aacute;mite';
    $arrBtnEnlace[] = 'radicarTramite()';
    $arrBtnToolTip[] = 'Radicar el tr&aacute;mite';

    if ($_SESSION["generales"]["retorno"] == 'consultaexpedientes') {
        $arrBtnTipo[] = 'href';
        $arrBtnEnlace[] = '../../librerias/proceso/mregConsultaExpedientes.php?accion=mostrarexpedientes';
        $arrBtnImagen[] = 'Browser de consulta';
        $arrBtnToolTip[] = 'Retornar al browser de consulta de expedientes';
    } else {
        $arrBtnTipo[] = 'href';
        $arrBtnEnlace[] = '../../librerias/proceso/mregRadicarDocumentosSinCosto.php?accion=cargarregistro';
        $arrBtnImagen[] = 'Abandonar';
        $arrBtnToolTip[] = 'Abandonar el tr&aacute;mite';
    }

    $string .= $pres->armarBarraBotonesProcesoDinamico(800, $arrBtnTipo, $arrBtnImagen, $arrBtnEnlace, $arrBtnToolTip);
    $string .= $pres->armarFinFormulario();
    $string .= $pres->cerrarTablaBorde();
    $string .= '</center>';

    unset($pres);
    $head = '<script type="text/javascript" src="../../librerias/funciones/mregRadicarDocumentosSinCosto.js"></script>';
    $head .= '<script type="text/javascript" src="../../librerias/funciones/mreg.js"></script>';
    $head .= '<script type="text/javascript" src="../../includes/js/upclick-min.js"></script>';
    $head .= '<script type="text/javascript" src="../../includes/dropzone/dropzone_pdf.js"></script>';
    $head .= '<link rel="stylesheet" href="../../includes/dropzone/dropzone.css">';


    //
    mostrarCuerpoIE26a(array(), $head, '', 'Radicaci&oacute;n de documentos sin costo y correcciones', $string, 620, 400, '', 'Espere ...');
    exit();
}

function loadFile() 
{
    include_once '../../configuracion/common.php';
    include_once '../../configuracion/common' . $_SESSION["generales"]["codigoempresa"] . '.php';
    include_once '../../librerias/funciones/persistencia.php';
    include_once '../../librerias/presentacion/template.class.php';
    include_once '../../librerias/presentacion/presentacion.class.php';

    $arrTipoTramite = retornarRegistro('bas_tipotramites', "id='" . $_SESSION["tramite"]["tipotramite"] . "'");

    if (!empty($_FILES)) {
        //
        $arrCampos = array(
            'idliquidacion',
            'sectransaccion',
            'identificador',
            'expediente',
            'tipoanexo',
            'idradicacion',
            'numerorecibo',
            'numerooperacion',
            'identificacion',
            'nombre',
            'idtipodoc',
            'numdoc',
            'fechadoc',
            'txtorigendoc',
            'path',
            'tipoarchivo',
            'observaciones',
            'bandeja',
            'eliminado'
        );

        //
        $sectra = null;
        $disco = encontrarPathImagen(tamanoArchivo($_FILES['file']['tmp_name']), str_replace("//", "", 'liquidacionmreg'), '503');
        if ($disco === false) {
            armarMensaje(800, 'h2', 'El anexo no pudo se cargado por favor informe este hecho al administrador del sistema.');
        }

        $arrValores = array(
            $_SESSION["tramite"]["idliquidacion"],
            "'" . $sectra . "'",
            "''",
            "'" . $_SESSION["tramite"]["idexpedientebase"] . "'",
            "'503'",
            0,
            "''",
            "''",
            "'" . $_SESSION["tramite"]["identificacionbase"] . "'",
            "'" . $_SESSION["tramite"]["nombrebase"] . "'",
            "'" . $_SESSION["tramite"]["tipodoc"] . "'",
            "'" . $_SESSION["tramite"]["numdoc"] . "'",
            "'" . $_SESSION["tramite"]["fechadoc"] . "'",
            "'" . $_SESSION["tramite"]["origendoc"] . "'",
            "'liquidacionmreg/" . sprintf("%03s", $disco) . "/'",
            "'pdf'",
            "'" . addslashes($arrTipoTramite["descripcion"]) . "'",
            "'" . $arrTipoTramite["bandeja"] . "'",
            "'NO'"
        );

        //
        insertarRegistros('mreg_anexos_liquidaciones', $arrCampos, $arrValores, 'si');
        $idAnexo = $_SESSION["generales"]["lastId"];
        copy($_FILES['file']['tmp_name'], '../../' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/' . 'liquidacionmreg/' . sprintf("%03s", $disco) . '/' . $idAnexo . '.pdf');
    }
}

function generarRadicacion() 
{
    include_once $_SESSION["generales"]["pathabsoluto"] . '/configuracion/common.php';
    include_once $_SESSION["generales"]["pathabsoluto"] . '/configuracion/common' . $_SESSION["generales"]["codigoempresa"] . '.php';
    include_once $_SESSION["generales"]["pathabsoluto"] . '/librerias/funciones/generales.php';
    include_once $_SESSION["generales"]["pathabsoluto"] . '/librerias/funciones/sanarEntradas.class.php';
    include_once $_SESSION["generales"]["pathabsoluto"] . '/librerias/funciones/persistencia.php';
    include_once $_SESSION["generales"]["pathabsoluto"] . '/librerias/funciones/persistenciamreg.php';
    include_once $_SESSION["generales"]["pathabsoluto"] . '/librerias/funciones/Encoding.php';
    include_once $_SESSION["generales"]["pathabsoluto"] . '/librerias/funciones/capturaSoportesDocumentales.php';
    include_once $_SESSION["generales"]["pathabsoluto"] . '/librerias/funciones/capturaEvidenciasSipref.php';
    include_once $_SESSION["generales"]["pathabsoluto"] . '/librerias/presentacion/template.class.php';
    include_once $_SESSION["generales"]["pathabsoluto"] . '/librerias/presentacion/presentacion.class.php';

    //
    $parametros = base64_decode($_SESSION["vars"]["parametros"]);

    // Asigna la totalidad de las variables pasadas en la linea de parametros
    $lista = explode("|", $parametros);
    foreach ($lista as $l) {
        list ($campo, $valor) = explode(":", $l);
        if ($campo != 'idmunicipio' 
            && $campo != 'tipodoc' 
            && $campo != 'mundoc' 
            && $campo != 'municipioaceptante' 
            && $campo != 'municipiopqr' 
            && $campo != 'municipiorr' 
            && $campo != 'municipiocor' 
            && $campo != 'nrocontrolsipref' 
            && $campo != 'nrocontrolsiprefbio'
        ) {
            $_SESSION["tramite"][$campo] = ltrim($valor, "0");
        } else {
            if ($campo != 'emailaceptante' 
                && $campo != 'emailcor' 
                && $campo != 'emailpqr' 
                && $campo != 'emailrr' 
                && $campo != 'nrocontrolsipref' 
                && $campo != 'nrocontrolsiprefbio'
            ) {
                $_SESSION["tramite"][$campo] = strtoupper(trim($valor));
            } else {
                $_SESSION["tramite"][$campo] = (trim($valor));
            }
        }
    }


    //
    $bio = '';
    $nro = '';
    if ($_SESSION["tramite"]["seleccionosipref"] == 'si') {
        $nro = $_SESSION["tramite"]["nrocontrolsipref"];
    }
    if ($_SESSION["tramite"]["seleccionosiprefbio"] == 'si') {
        $nro = $_SESSION["tramite"]["nrocontrolsiprefbio"];
        $bio = 'bio';
    }
    if ($bio == 'bio') {
        if (contarRegistros('mreg_sipref_controlevidencias', "nrocontrolsipref='" . $nro . "'") > 0) {
            $_SESSION["generales"]["txtemergente"] = 'La evidencia biometrica seleccionada ya esta asociada con otra radicacion';
            mostrarRadicacion();
        }
    }

    //
    if ($_SESSION["tramite"]["nombre1pqr"] != '') {
        $_SESSION["tramite"]["idtipoidentificacioncliente"] = $_SESSION["tramite"]["tipoidentificacionpqr"];
        $_SESSION["tramite"]["identificacioncliente"] = $_SESSION["tramite"]["identificacionpqr"];
        $_SESSION["tramite"]["nombre1cliente"] = $_SESSION["tramite"]["nombre1pqr"];
        $_SESSION["tramite"]["nombre2cliente"] = $_SESSION["tramite"]["nombre2pqr"];
        $_SESSION["tramite"]["apellido1cliente"] = $_SESSION["tramite"]["apellido1pqr"];
        $_SESSION["tramite"]["apellido2cliente"] = $_SESSION["tramite"]["apellido2pqr"];
        $_SESSION["tramite"]["direccion"] = $_SESSION["tramite"]["direccionpqr"];
        $_SESSION["tramite"]["idmunicipio"] = $_SESSION["tramite"]["municipiopqr"];
        $_SESSION["tramite"]["email"] = $_SESSION["tramite"]["emailpqr"];
        $_SESSION["tramite"]["telefono"] = $_SESSION["tramite"]["telefonopqr"];
        $_SESSION["tramite"]["movil"] = $_SESSION["tramite"]["celularpqr"];
    }
    if ($_SESSION["tramite"]["nombre1rr"] != '') {
        $_SESSION["tramite"]["idtipoidentificacioncliente"] = $_SESSION["tramite"]["tipoidentificacionrr"];
        $_SESSION["tramite"]["identificacioncliente"] = $_SESSION["tramite"]["identificacionrr"];
        $_SESSION["tramite"]["nombre1cliente"] = $_SESSION["tramite"]["nombre1rr"];
        $_SESSION["tramite"]["nombre2cliente"] = $_SESSION["tramite"]["nombre2rr"];
        $_SESSION["tramite"]["apellido1cliente"] = $_SESSION["tramite"]["apellido1rr"];
        $_SESSION["tramite"]["apellido2cliente"] = $_SESSION["tramite"]["apellido2rr"];
        $_SESSION["tramite"]["direccion"] = $_SESSION["tramite"]["direccionrr"];
        $_SESSION["tramite"]["idmunicipio"] = $_SESSION["tramite"]["municipiorr"];
        $_SESSION["tramite"]["email"] = $_SESSION["tramite"]["emailrr"];
        $_SESSION["tramite"]["telefono"] = $_SESSION["tramite"]["telefonorr"];
        $_SESSION["tramite"]["movil"] = $_SESSION["tramite"]["celularrr"];
    }
    if ($_SESSION["tramite"]["nombre1aceptante"] != '') {
        $_SESSION["tramite"]["idtipoidentificacioncliente"] = $_SESSION["tramite"]["tipoidentificacionaceptante"];
        $_SESSION["tramite"]["identificacioncliente"] = $_SESSION["tramite"]["identificacionaceptante"];
        $_SESSION["tramite"]["nombre1cliente"] = $_SESSION["tramite"]["nombre1aceptante"];
        $_SESSION["tramite"]["nombre2cliente"] = $_SESSION["tramite"]["nombre2aceptante"];
        $_SESSION["tramite"]["apellido1cliente"] = $_SESSION["tramite"]["apellido1aceptante"];
        $_SESSION["tramite"]["apellido2cliente"] = $_SESSION["tramite"]["apellido2aceptante"];
        $_SESSION["tramite"]["direccion"] = $_SESSION["tramite"]["direccionaceptante"];
        $_SESSION["tramite"]["idmunicipio"] = $_SESSION["tramite"]["municipioaceptante"];
        $_SESSION["tramite"]["email"] = $_SESSION["tramite"]["emailaceptante"];
        $_SESSION["tramite"]["telefono"] = $_SESSION["tramite"]["telefonoaceptante"];
        $_SESSION["tramite"]["movil"] = $_SESSION["tramite"]["celularaceptante"];
    }
    if ($_SESSION["tramite"]["nombre1cor"] != '') {
        $_SESSION["tramite"]["idtipoidentificacioncliente"] = $_SESSION["tramite"]["tipoidentificacioncor"];
        $_SESSION["tramite"]["identificacioncliente"] = $_SESSION["tramite"]["identificacioncor"];
        $_SESSION["tramite"]["nombre1cliente"] = $_SESSION["tramite"]["nombre1cor"];
        $_SESSION["tramite"]["nombre2cliente"] = $_SESSION["tramite"]["nombre2cor"];
        $_SESSION["tramite"]["apellido1cliente"] = $_SESSION["tramite"]["apellido1cor"];
        $_SESSION["tramite"]["apellido2cliente"] = $_SESSION["tramite"]["apellido2cor"];
        $_SESSION["tramite"]["direccion"] = $_SESSION["tramite"]["direccioncor"];
        $_SESSION["tramite"]["idmunicipio"] = $_SESSION["tramite"]["municipiocor"];
        $_SESSION["tramite"]["email"] = $_SESSION["tramite"]["emailcor"];
        $_SESSION["tramite"]["telefono"] = $_SESSION["tramite"]["telefonocor"];
        $_SESSION["tramite"]["movil"] = $_SESSION["tramite"]["celularcor"];
    }

    $_SESSION["tramite"]["nombrecliente"] = trim($_SESSION["tramite"]["nombre1cliente"] . ' ' . $_SESSION["tramite"]["nombre2cliente"]);
    $_SESSION["tramite"]["apellidocliente"] = trim($_SESSION["tramite"]["apellido1cliente"] . ' ' . $_SESSION["tramite"]["apellido2cliente"]);

    //
    $_SESSION["tramite"]["nombre1pagador"] = ($_SESSION["tramite"]["nombre1cliente"]);
    $_SESSION["tramite"]["nombre2pagador"] = ($_SESSION["tramite"]["nombre2cliente"]);
    $_SESSION["tramite"]["apellido1pagador"] = ($_SESSION["tramite"]["apellido1cliente"]);
    $_SESSION["tramite"]["apellido2pagador"] = ($_SESSION["tramite"]["apellido2cliente"]);
    $_SESSION["tramite"]["nombrepagador"] = trim($_SESSION["tramite"]["nombre1pagador"] . ' ' . $_SESSION["tramite"]["nombre2pagador"]);
    $_SESSION["tramite"]["apellidopagador"] = trim($_SESSION["tramite"]["apellido1pagador"] . ' ' . $_SESSION["tramite"]["apellido2pagador"]);
    $_SESSION["tramite"]["tipoidentificacionpagador"] = $_SESSION["tramite"]["idtipoidentificacioncliente"];
    $_SESSION["tramite"]["identificacionpagador"] = $_SESSION["tramite"]["identificacioncliente"];
    $_SESSION["tramite"]["direccionpagador"] = Encoding::fixUTF8($_SESSION["tramite"]["direccion"]);
    $_SESSION["tramite"]["municipiopagador"] = $_SESSION["tramite"]["idmunicipio"];
    $_SESSION["tramite"]["telefonopagador"] = $_SESSION["tramite"]["telefono"];
    $_SESSION["tramite"]["movilpagador"] = $_SESSION["tramite"]["movil"];
    $_SESSION["tramite"]["emailpagador"] = $_SESSION["tramite"]["email"];

    // 2015-2-22: JINT : Se graba la liquidación para asegurarnos que la info digitada quede bien almacenada en caso de errores
    $_SESSION["tramite"]["origen"] = 'presencial';

    //
    $arrTem = retornarRegistro('bas_tipotramites', "id='" . $_SESSION["tramite"]["tipotramite"] . "'");
    $arrTem1 = retornarRegistro('mreg_maestro_tramites_sin_costo', "tipotramite='" . $_SESSION["tramite"]["tipotramite"] . "' and subtipotramite='" . $_SESSION["tramite"]["subtipotramite"] . "'");

    // Arma la tabla detalle liquidaci&oacute;n
    $i = 0;
    $_SESSION["tramite"]["liquidacion"] = array();

    // Servicio primario
    $i++;
    if ($arrTem["tiporegistro"] == 'RegMer' || $arrTem["tiporegistro"] == 'RegEsadl') {
        $_SESSION["tramite"]["liquidacion"][$i]["expediente"] = $_SESSION["tramite"]["idmatriculabase"];
    } else {
        $_SESSION["tramite"]["liquidacion"][$i]["expediente"] = $_SESSION["tramite"]["idproponentebase"];
    }
    $_SESSION["tramite"]["liquidacion"][$i]["nombre"] = $_SESSION["tramite"]["nombrebase"];
    $_SESSION["tramite"]["liquidacion"][$i]["organizacion"] = $_SESSION["tramite"]["organizacionbase"];
    $_SESSION["tramite"]["liquidacion"][$i]["categoria"] = $_SESSION["tramite"]["categoriabase"];
    $_SESSION["tramite"]["liquidacion"][$i]["idservicio"] = $arrTem1["servicio1"];
    $_SESSION["tramite"]["liquidacion"][$i]["ano"] = '';
    $_SESSION["tramite"]["liquidacion"][$i]["cantidad"] = 1;
    $_SESSION["tramite"]["liquidacion"][$i]["valorbase"] = 0;
    $_SESSION["tramite"]["liquidacion"][$i]["porcentaje"] = 0;
    $_SESSION["tramite"]["liquidacion"][$i]["valorservicio"] = 0;
    $_SESSION["tramite"]["liquidacion"][$i]["serviciobase"] = 'S';

    // Servicio secundario
    if ($arrTem1["servicio2"] != '') {
        $i++;
        if ($arrTem["tiporegistro"] == 'RegMer' || $arrTem["tiporegistro"] == 'RegEsadl') {
            $_SESSION["tramite"]["liquidacion"][$i]["expediente"] = $_SESSION["tramite"]["idmatriculabase"];
        } else {
            $_SESSION["tramite"]["liquidacion"][$i]["expediente"] = $_SESSION["tramite"]["idproponentebase"];
        }
        $_SESSION["tramite"]["liquidacion"][$i]["nombre"] = $_SESSION["tramite"]["nombrebase"];
        $_SESSION["tramite"]["liquidacion"][$i]["organizacion"] = $_SESSION["tramite"]["organizacionbase"];
        $_SESSION["tramite"]["liquidacion"][$i]["categoria"] = $_SESSION["tramite"]["categoriabase"];
        $_SESSION["tramite"]["liquidacion"][$i]["idservicio"] = $arrTem1["servicio2"];
        $_SESSION["tramite"]["liquidacion"][$i]["ano"] = '';
        $_SESSION["tramite"]["liquidacion"][$i]["cantidad"] = 1;
        $_SESSION["tramite"]["liquidacion"][$i]["valorbase"] = 0;
        $_SESSION["tramite"]["liquidacion"][$i]["porcentaje"] = 0;
        $_SESSION["tramite"]["liquidacion"][$i]["valorservicio"] = 0;
        $_SESSION["tramite"]["liquidacion"][$i]["serviciobase"] = 'S';
    }

    // Servicio secundario
    if ($arrTem1["servicio3"] != '') {
        $i++;
        if ($arrTem["tiporegistro"] == 'RegMer' || $arrTem["tiporegistro"] == 'RegEsadl') {
            $_SESSION["tramite"]["liquidacion"][$i]["expediente"] = $_SESSION["tramite"]["idmatriculabase"];
        } else {
            $_SESSION["tramite"]["liquidacion"][$i]["expediente"] = $_SESSION["tramite"]["idproponentebase"];
        }
        $_SESSION["tramite"]["liquidacion"][$i]["nombre"] = $_SESSION["tramite"]["nombrebase"];
        $_SESSION["tramite"]["liquidacion"][$i]["organizacion"] = $_SESSION["tramite"]["organizacionbase"];
        $_SESSION["tramite"]["liquidacion"][$i]["categoria"] = $_SESSION["tramite"]["categoriabase"];
        $_SESSION["tramite"]["liquidacion"][$i]["idservicio"] = $arrTem1["servicio3"];
        $_SESSION["tramite"]["liquidacion"][$i]["ano"] = '';
        $_SESSION["tramite"]["liquidacion"][$i]["cantidad"] = 1;
        $_SESSION["tramite"]["liquidacion"][$i]["valorbase"] = 0;
        $_SESSION["tramite"]["liquidacion"][$i]["porcentaje"] = 0;
        $_SESSION["tramite"]["liquidacion"][$i]["valorservicio"] = 0;
        $_SESSION["tramite"]["liquidacion"][$i]["serviciobase"] = 'S';
    }

    //
    $_SESSION["tramite"]["valorbruto"] = 0;
    $_SESSION["tramite"]["valorbaseiva"] = 0;
    $_SESSION["tramite"]["valoriva"] = 0;
    $_SESSION["tramite"]["valorneto"] = 0;
    $_SESSION["tramite"]["valortotal"] = 0;

    // Graba la liquidaci&oacute;n    
    $res = grabarLiquidacionMreg();

    // Llama al servicio web para generar el recibo de caja
    $xmlPago = serializarLiquidacion();
    $res = consumirWsActualizarPago($_SESSION["generales"]["codigoempresa"], $xmlPago, $_SESSION["tramite"]["tipotramite"], $_SESSION["tramite"]["idliquidacion"]);

    //
    if ($res["codigoError"] != '0000') {
        if ($res["codigoError"] == '005X') {
            $_SESSION["generales"]["txtemergente"] = $res["codigoError"] . ' - problema con validaci&oacute;n del esquema';
            mostrarRadicacion();
        } else {
            $_SESSION["generales"]["txtemergente"] = 'Error: ' . $res["codigoError"] . ' - ' . str_replace(array("'", '"', '\''), "", $res["msgError"]);
            mostrarRadicacion();
        }
    }

    //
    if ($nro != '') {

        // Crea registro en SII
        if ($bio != 'bio') {
            $arrCampos = array(
                'tabla',
                'registro',
                'campo',
                'contenido'
            );
            $arrValores = array(
                "'997'",
                "'NROSIPREF'",
                "'" . $nro . "'",
                "'" . $_SESSION["tramite"]["idexpedientebase"] . "'"
            );
            insertarRegistros('mreg_est_campostablas', $arrCampos, $arrValores);
        }


        //
        // Almacena en mreg_sipref_controlevidencias la informaci&oacute;n asociada con el n&uacute;mero de recuperaci&oacute;n siempre y cuando
        // exista alguno de los soportes
        // Usado cuando los soportes se capturan a trav&eacute;s del SII (Foto + Cedula1 + Cedula2 + Certificaci&oacute;n Rnec)
        // En este caso no hay minucia
        $existe = 'no';
        $f1 = '../../' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/fotoEvidencias/' . substr($nrocontrolsipref, 0, 3) . '/' . $nrocontrolsipref . '-Foto.jpg';
        $c1 = '../../' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/fotoEvidencias/' . substr($nrocontrolsipref, 0, 3) . '/' . $nrocontrolsipref . '-Cedula1.jpg';
        $c2 = '../../' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/fotoEvidencias/' . substr($nrocontrolsipref, 0, 3) . '/' . $nrocontrolsipref . '-Cedula2.jpg';
        $r1 = '../../' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/fotoEvidencias/' . substr($nrocontrolsipref, 0, 3) . '/' . $nrocontrolsipref . '-Rnec.pdf';

        //
        if (file_exists($f1)) {
            $existe = 'si';
        }
        if (file_exists($c1)) {
            $existe = 'si';
        }
        if (file_exists($c2)) {
            $existe = 'si';
        }
        if (file_exists($r1)) {
            $existe = 'si';
        }

        //
        $idlogrnec = '';
        if ($_SESSION["tramite"]["seleccionosiprefbio"] == 'si') {
            $idlogrnec = retornarRegistro('mrg_sipref_controlevidencias_biometricas' . "nrocontrolsipref='" . $nro . "'", "idlogrnec");
        }

        //
        if ($existe == 'si' || $bio == 'bio') {
            $arrCampos = array(
                'nrocontrolsipref',
                'tipoevidencia',
                'idlogrnec',
                'fecha',
                'hora',
                'tipotramite',
                'matricula',
                'proponente',
                'nombreradicador',
                'tipoideradicador',
                'ideradicador',
                'fechaexpradicador',
                'recibo',
                'nrorecuperacion',
                'codigobarras',
                'idliquidacion',
                'idusuario',
                'ip',
                'minucia',
                'numminucia'
            );
            $arrValores = array(
                "'" . $nro . "'",
                "'" . $bio . "'",
                "'" . $idlogrnec . "'",
                "'" . date("Ymd") . "'",
                "'" . date("His") . "'",
                "'" . $_SESSION["tramite"]["tipotramite"] . "'",
                "'" . ltrim($_SESSION["tramite"]["idexpedientebase"], "0") . "'",
                "''",
                "'" . trim(strtoupper($_SESSION["tramite"]["nombrecliente"]) . ' ' . strtoupper($_SESSION["tramite"]["apellidocliente"])) . "'",
                "'" . $_SESSION["tramite"]["idtipoidentificacioncliente"] . "'",
                "'" . ltrim($_SESSION["tramite"]["identificacioncliente"], "0") . "'",
                "''",
                "'" . $res["numeroRecibo"] . "'",
                "'" . $_SESSION["tramite"]["numerorecuperacion"] . "'",
                "'" . $res["codigoBarras"] . "'",
                "'" . $_SESSION["tramite"]["numeroliquidacion"] . "'",
                "'" . $_SESSION["generales"]["codigousuario"] . "'",
                "'" . localizarIP() . "'",
                "''", // Minucia
                "''" // Num-Dedo-Minucia
            );
            insertarRegistros('mreg_sipref_controlevidencias', $arrCampos, $arrValores);
        }
    }
    //
    $numliq = $_SESSION["tramite"]["numeroliquidacion"];
    unset($_SESSION["tramite"]);
    header("Location: ../../librerias/proceso/mregSoportesPago.php?accion=mostrarsoportes&liquidacion=" . $numliq . "&claveprepago=" . $res["clavePrepago"] . "&saldoprepago=" . $res["saldoPrepago"] . "&mostrarcerrar=NO");
}

?>
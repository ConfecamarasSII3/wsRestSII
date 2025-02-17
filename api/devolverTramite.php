<?php
namespace api;
use api\API;
trait devolverTramite {

    public function devolverTramiteGrabarDevolucion(API $api) {
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');

        // array de respuesta
        $_SESSION["jsonsalida"] = array();
        $_SESSION["jsonsalida"]["codigoerror"] = '0000';
        $_SESSION["jsonsalida"]["iddevolucion"] = '';
        $_SESSION["jsonsalida"]["mensajeerror"] = '';

        //
        if ($api->get_request_method() != "POST") {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'La petición debe ser POST';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        // ********************************************************************** //
        // Valida que el usuario reportado tenga acceso a la BD y al metodo
        // ********************************************************************** // 
        if (!$api->validarToken('devolverTramiteGrabarDevolucion', $_SESSION["entrada"]["token"], $_SESSION["entrada"]["usuariows"])) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Sin permisos para ejecutar el método ruesGrabarSolicitudBloque ';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        // ********************************************************************** //
        // Recupera las variables del arreglo $_SESSION["entrada1"]
        // ********************************************************************** //

        $_SESSION["vars"]["razonsocial"] = base64_decode($_SESSION["entrada1"]["nombre"]);
        $_SESSION["vars"]["identificacion"] = base64_decode($_SESSION["entrada1"]["identificacion"]);
        $_SESSION["vars"]["matricula"] = base64_decode($_SESSION["entrada1"]["matricula"]);
        $_SESSION["vars"]["proponente"] = base64_decode($_SESSION["entrada1"]["proponente"]);
        $_SESSION["vars"]["organizacion"] = base64_decode($_SESSION["entrada1"]["organizacion"]);

        $_SESSION["vars"]["recibo"] = base64_decode($_SESSION["entrada1"]["recibo"]);
        $_SESSION["vars"]["operacion"] = base64_decode($_SESSION["entrada1"]["operacion"]);
        $_SESSION["vars"]["nuc"] = base64_decode($_SESSION["entrada1"]["nuc"]);
        $_SESSION["vars"]["nin"] = base64_decode($_SESSION["entrada1"]["nin"]);

        $_SESSION["vars"]["codbarras"] = base64_decode($_SESSION["entrada1"]["codbarras"]);
        $_SESSION["vars"]["iddevolucion"] = base64_decode($_SESSION["entrada1"]["iddevolucion"]);
        $_SESSION["vars"]["tipotramite"] = base64_decode($_SESSION["entrada1"]["tipotramite"]);
        $_SESSION["vars"]["tipodevolucion"] = base64_decode($_SESSION["entrada1"]["tipodevolucion"]);
        $_SESSION["vars"]["devolucionparcial"] = base64_decode($_SESSION["entrada1"]["devolucionparcial"]);
        $_SESSION["vars"]["modificarformulario"] = base64_decode($_SESSION["entrada1"]["modificarformulario"]);
        $_SESSION["vars"]["nombredevolucion"] = base64_decode($_SESSION["entrada1"]["nombredevolucion"]);
        $_SESSION["vars"]["email"] = base64_decode($_SESSION["entrada1"]["email"]);
        $_SESSION["vars"]["email2"] = base64_decode($_SESSION["entrada1"]["email2"]);
        $_SESSION["vars"]["email3"] = base64_decode($_SESSION["entrada1"]["email3"]);
        $_SESSION["vars"]["numdoc"] = base64_decode($_SESSION["entrada1"]["numdoc"]);
        $_SESSION["vars"]["idtipodoc"] = base64_decode($_SESSION["entrada1"]["idtipodoc"]);
        $_SESSION["vars"]["iddevolucion"] = base64_decode($_SESSION["entrada1"]["iddevolucion"]);
        $_SESSION["vars"]["observaciones"] = base64_decode($_SESSION["entrada1"]["observaciones"]);
        $_SESSION["vars"]["idusuarioxxx"] = base64_decode($_SESSION["entrada1"]["idusuarioxxx"]);
        $_SESSION["vars"]["motivo1"] = base64_decode($_SESSION["entrada1"]["motivo1"]);
        $_SESSION["vars"]["motivo2"] = base64_decode($_SESSION["entrada1"]["motivo2"]);
        $_SESSION["vars"]["motivo3"] = base64_decode($_SESSION["entrada1"]["motivo3"]);
        $_SESSION["vars"]["motivo4"] = base64_decode($_SESSION["entrada1"]["motivo4"]);
        $_SESSION["vars"]["motivo5"] = base64_decode($_SESSION["entrada1"]["motivo5"]);

        //
        $mysqli = conexionMysqliApi();

        //
        if (trim((string) $_SESSION["vars"]["numdoc"]) == '') {
            $numdoc = retornarConsecutivoTipoDocMysqliApi($mysqli, date("Y"), $_SESSION["vars"]["idtipodoc"], '', '', 'N');
            if ($numdoc === false) {
                $mysqli->close();
                $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                $_SESSION["jsonsalida"]["mensajeerror"] = 'No fue posible retornar el número de la devolución. : ' . $_SESSION["generales"]["mensajeerror"];
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
            }
            $_SESSION["vars"]["numdoc"] = ltrim((string) $numdoc, "0");
            $_SESSION["vars"]["process_id"] = '';
        }

        //
        $arrCampos = array(
            'idradicacion',
            'idtipodoc',
            'numdoc',
            'razonsocial',
            'identificacion',
            'matricula',
            'proponente',
            'organizacion',
            'fechadevolucion',
            'horadevolucion',
            'tipotramite',
            'estado',
            'idusuario',
            'modificarformulario',
            'nombredevolucion',
            'email',
            'email2',
            'email3',
            'fechanotificacion',
            'horanotificacion',
            'observaciones',
            'tipodevolucion',
            'devolucionparcial',
            'observaciones_envio'
        );

        $arrValores = array(
            "'" . ltrim($_SESSION["vars"]["codbarras"], "0") . "'",
            "'" . $_SESSION["vars"]["idtipodoc"] . "'",
            "'" . $_SESSION["vars"]["numdoc"] . "'",
            "'" . addslashes($_SESSION["vars"]["razonsocial"]) . "'",
            "'" . $_SESSION["vars"]["identificacion"] . "'",
            "'" . $_SESSION["vars"]["matricula"] . "'",
            "'" . $_SESSION["vars"]["proponente"] . "'",
            "'" . $_SESSION["vars"]["organizacion"] . "'",
            "'" . date("Ymd") . "'",
            "'" . date("His") . "'",
            "'" . $_SESSION["vars"]["tipotramite"] . "'",
            "'0'",
            "'" . $_SESSION["vars"]["idusuarioxxx"] . "'",
            "'" . $_SESSION["vars"]["modificarformulario"] . "'",
            "'" . addslashes(mb_strtoupper($_SESSION["vars"]["nombredevolucion"], 'utf-8')) . "'",
            "'" . addslashes($_SESSION["vars"]["email"]) . "'",
            "'" . addslashes($_SESSION["vars"]["email2"]) . "'",
            "'" . addslashes($_SESSION["vars"]["email3"]) . "'",
            "''",
            "''",
            "'" . addslashes($_SESSION["vars"]["observaciones"]) . "'",
            "'" . $_SESSION["vars"]["tipodevolucion"] . "'",
            "'" . $_SESSION["vars"]["devolucionparcial"] . "'",
            "''"
        );

        if ($_SESSION["vars"]["iddevolucion"] == 0) {
            insertarRegistrosMysqliApi($mysqli, 'mreg_devoluciones_nueva', $arrCampos, $arrValores, "si");
            $_SESSION["vars"]["iddevolucion"] = $_SESSION["generales"]["lastId"];
        } else {
            $condicion = "iddevolucion=" . $_SESSION["vars"]["iddevolucion"];
            regrabarRegistrosMysqliApi($mysqli, 'mreg_devoluciones_nueva', $arrCampos, $arrValores, $condicion);
        }

        borrarRegistrosMysqliApi($mysqli, 'mreg_devoluciones_motivos', "iddevolucion=" . $_SESSION["vars"]["iddevolucion"]);

        for ($i = 1; $i <= 5; $i++) {
            if (ltrim((string) $_SESSION["vars"]["motivo" . $i], "0") !== '') {
                $arrCampos = array(
                    "iddevolucion",
                    "idmotivo"
                );
                $arrValores = array(
                    $_SESSION["vars"]["iddevolucion"],
                    $_SESSION["vars"]["motivo" . $i]
                );
                insertarRegistrosMysqliApi($mysqli, 'mreg_devoluciones_motivos', $arrCampos, $arrValores);
            }
        }

        //
        $mysqli->close();

        // **************************************************************************** //
        // Resultado
        // **************************************************************************** //
        $_SESSION["jsonsalida"]["iddevolucion"] = $_SESSION["vars"]["iddevolucion"];
        \logApi::peticionRest('api_' . __FUNCTION__);
        $json = $api->json($_SESSION["jsonsalida"]);
        $api->response(str_replace("\\/", "/", $json), 200);
    }

    public function devolverTramiteEliminarAnexo(API $api) {
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');

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

        // ********************************************************************** //
        // Valida que el usuario reportado tenga acceso a la BD y al metodo
        // ********************************************************************** // 
        if (!$api->validarToken('devolverTramiteEliminarAnexo', $_SESSION["entrada"]["token"], $_SESSION["entrada"]["usuariows"])) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Sin permisos para ejecutar el método ruesGrabarSolicitudBloque ';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        // ********************************************************************** //
        // Recupera las variables del arreglo $_SESSION["entrada1"]
        // ********************************************************************** //
        if (file_exists(base64_decode($_SESSION["entrada1"]["anexo"]))) {
            unlink(base64_decode($_SESSION["entrada1"]["anexo"]));
            if (file_exists(base64_decode($_SESSION["entrada1"]["anexo"]))) {
                $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                $_SESSION["jsonsalida"]["mensajeerror"] = 'No fue posible eliminar el anexo.';
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
            } else {
                $_SESSION["jsonsalida"]["codigoerror"] = "0000";
                $_SESSION["jsonsalida"]["mensajeerror"] = 'Anexo eliminado';
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
            }
        } else {
            $_SESSION["jsonsalida"]["codigoerror"] = "0000";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Anexo eliminado';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }
    }

    public function devolverTramiteBuscarMotivos(API $api) {
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');

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

        // ********************************************************************** //
        // Valida que el usuario reportado tenga acceso a la BD y al metodo
        // ********************************************************************** // 
        if (!$api->validarToken('devolverTramiteBuscarMotivos', $_SESSION["entrada"]["token"], $_SESSION["entrada"]["usuariows"])) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Sin permisos para ejecutar el método devolverTramiteBuscarMotivos ';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        // ********************************************************************** //
        // Recupera las variables del arreglo $_SESSION["entrada1"]
        // ********************************************************************** //
        $mysqli = conexionMysqliApi();
        if (base64_decode($_SESSION["entrada"]["textobuscar"]) == '') {
            $regs = retornarRegistrosMysqliApi($mysqli, 'mreg_motivosdevolucion_nuevo', "1=1", "descripcion");
        } else {
            $regs = retornarRegistrosMysqliApi($mysqli, 'mreg_motivosdevolucion_nuevo', "descripcion like '%" . base64_decode($_SESSION["entrada"]["textobuscar"]) . "%'", "descripcion");
        }
        $mysqli->close();
        if ($regs && !empty($regs)) {
            $i = 0;
            $html = '<br>';
            $html .= '<div class="card fat" style="visibility:visible">';
            $html .= '<div class="card-body">';
            foreach ($regs as $r) {
                if (strlen($r["descripcion"]) > 10) {
                    $i++;
                    $html .= $i . '.) ' . $r["descripcion"] . '&nbsp;&nbsp;<a href="javascript:seleccionarMotivo(\'' . $r["idmotivo"] . '\')">Seleccionar</a><br><br>';
                }
            }
            $html .= '</div>';
            $html .= '</div>';
            $_SESSION["jsonsalida"]["codigoerror"] = "0000";
            $_SESSION["jsonsalida"]["mensajeerror"] = '';
            $_SESSION["jsonsalida"]["html"] = $html;
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        } else {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'No se encontraron ocurrencias';
            $_SESSION["jsonsalida"]["html"] = '';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }
    }

    public function devolverTramiteArmarMotivosSeleccionados(API $api) {
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');

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

        // ********************************************************************** //
        // Valida que el usuario reportado tenga acceso a la BD y al metodo
        // ********************************************************************** // 
        if (!$api->validarToken('devolverTramiteArmarMotivosSeleccionados', $_SESSION["entrada"]["token"], $_SESSION["entrada"]["usuariows"])) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Sin permisos para ejecutar el método devolverTramiteArmarMotivosSeleccionados ';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        //
        $list = explode("|", base64_decode($_SESSION["entrada"]["motivosbuscar"]));

        // ********************************************************************** //
        // Recupera las variables del arreglo $_SESSION["entrada1"]
        // ********************************************************************** //
        $html = '<br>';
        $html .= '<div class="card fat" style="visibility:visible">';
        $html .= '<div class="card-body">';
        $mysqli = conexionMysqliApi();
        $i = 0;
        foreach ($list as $l) {
            if ($l !== '' && $l !== 0) {
                $reg = retornarRegistroMysqliApi($mysqli, 'mreg_motivosdevolucion_nuevo', "idmotivo=" . $l);
                $i++;
                $html .= $i . '.) ' . $reg["descripcion"] . '&nbsp;&nbsp;<a href="javascript:borrarMotivo(\'' . $reg["idmotivo"] . '\')">Borrar</a><br><br>';
            }
        }
        $html .= '</div>';
        $html .= '</div>';
        $_SESSION["jsonsalida"]["codigoerror"] = "0000";
        $_SESSION["jsonsalida"]["mensajeerror"] = '';
        $_SESSION["jsonsalida"]["html"] = $html;
        $api->response($api->json($_SESSION["jsonsalida"]), 200);
    }

}

<?php

namespace libreriaswsRestSII;

use libreriaswsRestSII\API;

trait exportarMatriculados
{

    public function exportarMatriculados(API $api)
    {
        ini_set('memory_limit', '4096M');
        ini_set('display_errors', 1);
        ini_set('default_socket_timeout', 14400);
        ini_set('set_time_limit', 14400);
        ini_set('soap.wsdl_cache_enabled', '0');
        ini_set('soap.wsdl_cache_ttl', '0');

        //
        $dateini = date("Ymd") . ' ' . date("His");

        //
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/myErrorHandler.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/arregloMregEstMatriculados.php');
        
        //
        set_error_handler('myErrorHandler');

        //
        $nameLog = 'api-sii1-ExportarMatriculados_' . date("Ymd");

        // array de respuesta
        $_SESSION["jsonsalida"] = array();
        $_SESSION["jsonsalida"]["codigoerror"] = '9999';
        $_SESSION["jsonsalida"]["mensajeerror"] = 'Error en logica';
        $_SESSION["jsonsalida"]["cantidad"] = '';
        $_SESSION["jsonsalida"]["archivo"] = '';

        // Verifica que  método de recepcion de parámetros sea POST
        if ($api->get_request_method() != "POST") {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'La petición debe ser POST';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        $api->validarParametro("usuariows", true);
        $api->validarParametro("token", true);
        $api->validarParametro("idusuario", true);
        $api->validarParametro("fechamatriculainicial", false);
        $api->validarParametro("fechamatriculafinal", false);
        $api->validarParametro("fecharenovacioninicial", false);
        $api->validarParametro("fecharenovacionfinal", false);
        $api->validarParametro("fechacancelacioninicial", false);
        $api->validarParametro("fechacancelacionfinal", false);
        $api->validarParametro("filtroexpedientes", false);
        $api->validarParametro("filtrociius", false); //Puede ser código CIIU separados por comas  Weymer - 2019/08/29
        $api->validarParametro("filtroletraciius", false); //Puede ser letra del CIIU separados por comas Weymer - 2019/08/29
        $api->validarParametro("filtromunicipios", false); //código DANE separados pro comas Weymer - 2019/08/29
        $api->validarParametro("filtroestados", false); //Estados separados por comas Weymer - 2019/08/29
        $api->validarParametro("filtroorganizaciones", false); //Organizaciones separadas por comasWeymer - 2019/08/29
        $api->validarParametro("filtroanoren", false); //años renovación separados por comas Weymer - 2019/08/29
        $api->validarParametro("activosinicial", false); //Weymer - 2019/08/29 Rango de los activos inicial
        $api->validarParametro("activosfinal", false); //Weymer - 2019/08/29 Rango de los activos final
        $api->validarParametro("filtroempleados", false, true); //Weymer - 2019/08/29
        $api->validarParametro("filtroexporta", false); //Weymer - 2019/08/29  (S / N)
        $api->validarParametro("filtroafiliado", false); //Weymer - 2019/08/29  (S / N)

        $code = $_SESSION["entrada"]["idusuario"];
        \logApi::general2($nameLog, $code, 'Inicio extraccion');
        $tlog = '';
        foreach ($_SESSION["entrada"] as $key => $valor) {
            $tlog .= $key . ' => ' . $valor . "\r\n";
        }
        \logApi::general2($nameLog, $code, 'Variables de la petición : ' . $tlog);
        
        // ********************************************************************** //
        // Validar datos de entrada
        // ********************************************************************** // 
        $fechamatriculainicial = trim($_SESSION["entrada"]["fechamatriculainicial"]);
        $fechamatriculafinal = trim($_SESSION["entrada"]["fechamatriculafinal"]);
        $fecharenovacioninicial = trim($_SESSION["entrada"]["fecharenovacioninicial"]);
        $fecharenovacionfinal = trim($_SESSION["entrada"]["fecharenovacionfinal"]);
        $fechacancelacioninicial = trim($_SESSION["entrada"]["fechacancelacioninicial"]);
        $fechacancelacionfinal = trim($_SESSION["entrada"]["fechacancelacionfinal"]);
        $filtroexpedientes = trim($_SESSION["entrada"]["filtroexpedientes"]);
        $filtrociius = trim($_SESSION["entrada"]["filtrociius"]);
        $filtroletraciius = trim($_SESSION["entrada"]["filtroletraciius"]);
        $filtromunicipios = trim($_SESSION["entrada"]["filtromunicipios"]);
        $filtroestados = trim($_SESSION["entrada"]["filtroestados"]);
        $filtroorganizaciones = trim($_SESSION["entrada"]["filtroorganizaciones"]);
        $filtroanoren = trim($_SESSION["entrada"]["filtroanoren"]);
        $filtroempleados = trim($_SESSION["entrada"]["filtroempleados"]);
        $filtroexporta = trim($_SESSION["entrada"]["filtroexporta"]);
        $activosinicial = trim($_SESSION["entrada"]["activosinicial"]);
        $activosfinal = trim($_SESSION["entrada"]["activosfinal"]);
        $filtroafiliado = trim($_SESSION["entrada"]["filtroafiliado"]);


        if ($fechamatriculainicial != '' && !\funcionesGenerales::validarFecha($fechamatriculainicial)) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Fecha de matrícula inicial incorrecta.';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }
        if ($fechamatriculafinal != '' && !\funcionesGenerales::validarFecha($fechamatriculafinal)) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Fecha de matrícula final incorrecta.';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        if ($fecharenovacioninicial != '' && !\funcionesGenerales::validarFecha($fecharenovacioninicial)) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Fecha de renovación inicial incorrecta.';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }
        if ($fecharenovacionfinal != '' && !\funcionesGenerales::validarFecha($fecharenovacionfinal)) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Fecha de renovación final incorrecta.';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }
        if ($fechacancelacioninicial != '' && !\funcionesGenerales::validarFecha($fechacancelacioninicial)) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Fecha de cancelación inicial incorrecta.';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }
        if ($fechacancelacionfinal != '' && !\funcionesGenerales::validarFecha($fechacancelacionfinal)) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Fecha de cancelación final incorrecta.';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }
        if (($filtroexpedientes == '') && ($filtroorganizaciones == '')) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Filtro de expedientes-organizaciones no seleccionado';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        if ($fechamatriculainicial > $fechamatriculafinal) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Rango de fechas de matrícula erróneo';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }
        if ($fecharenovacioninicial > $fecharenovacionfinal) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Rango de fechas de renovación erróneo';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }
        if ($fechacancelacioninicial > $fechacancelacionfinal) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Rango de fechas de cancelación erróneo';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        if ($activosinicial > $activosfinal) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Rango de activos erróneo';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        // ********************************************************************** //
        // Valida que el usuario reportado tenga acceso a la BD y al metodo
        // ********************************************************************** // 
        if (!$api->validarToken('exportarMatriculados', $_SESSION["entrada"]["token"], $_SESSION["entrada"]["usuariows"])) {
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        // ********************************************************************** //
        // Establece conexion con la BD
        // ********************************************************************** // 
        $mysqliu = conexionMysqliApi();               
        $mysqli = conexionMysqliApi('replicabatch');

        if ($mysqli === false) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Error de conexión a BD';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        \logApi::general2($nameLog, $code, 'Abrio conexion por BD');

        // ********************************************************************** //
        // Arma tablas de control
        // ********************************************************************** // 
        // Ciius
        $arrCiius = array();
        $tem = retornarRegistrosMysqliApi($mysqli, 'bas_ciius', "1=1", "idciiunum");
        foreach ($tem as $t) {
            $arrCiius[$t["idciiu"]] = ($t["descripcion"]);
        }
        unset($tem);
        \logApi::general2($nameLog, $code, 'Cargo ciius');


        // Arma tabla de barrios muni
        $arrTem = retornarRegistrosMysqliApi($mysqli, 'mreg_barriosmuni', '1=1', "idmunicipio,idbarrio");
        $arrBar = array();
        foreach ($arrTem as $tem) {
            $arrBar[$tem["idmunicipio"]][$tem["idbarrio"]] = ($tem["nombre"]);
        }
        unset($arrTem);
        \logApi::general2($nameLog, $code, 'Cargo barrios');

        // Arma tabla de municipios
        $arrTem = retornarRegistrosMysqliApi($mysqli, 'bas_municipios', '1=1', "codigomunicipio");
        $arrMun = array();
        foreach ($arrTem as $tem) {
            $arrMun[$tem["codigomunicipio"]] = ($tem["ciudad"]);
        }
        unset($arrTem);
        \logApi::general2($nameLog, $code, 'Cargo municipios');

        // Ubicaciones
        $arrTem = retornarRegistrosMysqliApi($mysqli, 'mreg_ubicacion', '1=1', "id");
        $arrUbi = array();
        foreach ($arrTem as $tem) {
            $arrUbi[$tem["id"]] = ($tem["descripcion"]);
        }
        unset($arrTem);
        \logApi::general2($nameLog, $code, 'Cargo ubicaciones');

        // Tabla 34
        $arrTem = retornarRegistrosMysqliApi($mysqli, 'mreg_tablassirep', "idtabla='34'", "idcodigo");
        $arr34 = array();
        foreach ($arrTem as $tem) {
            $arr34[$tem["idcodigo"]] = ($tem["descripcion"]);
        }
        unset($arrTem);
        \logApi::general2($nameLog, $code, 'Cargo tablas sirep 34');

        // Tabla 35
        $arrTem = retornarRegistrosMysqliApi($mysqli, 'mreg_tablassirep', "idtabla='35'", "idcodigo");
        $arr35 = array();
        foreach ($arrTem as $tem) {
            $arr35[$tem["idcodigo"]] = ($tem["descripcion"]);
        }
        unset($arrTem);
        \logApi::general2($nameLog, $code, 'Cargo tablas sirep 35');

        //
        $arrSerAfil = retornarRegistrosMysqliApi($mysqli, "mreg_servicios", "grupoventas='02'", 'idservicio');
        $Servicios = "";
        foreach ($arrSerAfil as $ServAfil) {
            if ($Servicios != '') {
                $Servicios .= ",";
            }
            $Servicios .= "'" . $ServAfil["idservicio"] . "'";
        }
        \logApi::general2($nameLog, $code, 'Cargo servicios de afiliacion');

        //
        $tx = retornarRegistrosMysqliApi($mysqli, "mreg_codvinculos", "1=1", "id");
        $arrVincs = array();
        foreach ($tx as $t1) {
            if ($t1["tipovinculo"] != '') {
                $arrVincs[$t1["id"]] = $t1;
            }
        }
        unset($tx);
        \logApi::general2($nameLog, $code, 'Cargo codvinculos');

        //
        $formaCalculoAfiliacion = retornarClaveValorMysqliApi($mysqli, '90.01.60');
        \logApi::general2($nameLog, $code, 'Cargo clave valor 90.01.60');


        // **************************************************************************** //
        // Resultado
        // **************************************************************************** //        

        $query = "matricula<>''";
        $query .= " AND (";
        $query .= "(fecmatricula BETWEEN '" . $fechamatriculainicial . "' AND '" . $fechamatriculafinal . "') OR ";
        $query .= "(fecrenovacion BETWEEN '" . $fecharenovacioninicial . "' AND '" . $fecharenovacionfinal . "') OR ";
        $query .= "(feccancelacion<>'' AND (feccancelacion BETWEEN '" . $fechacancelacioninicial . "' AND '" . $fechacancelacionfinal . "'))";
        $query .= ") ";


        if ($filtroexpedientes == 'PN') {
            $query .= "AND (organizacion = '01') ";
        }
        if ($filtroexpedientes == 'PJ') {
            $query .= "AND (organizacion > '02' AND organizacion <> '12' AND organizacion <> '14' AND categoria = '1') ";
        }
        if ($filtroexpedientes == 'PN+PJ') {
            $query .= "AND (organizacion = '01' OR (organizacion <> '02' && organizacion <> '12' AND organizacion <> '14' AND categoria = '1')) ";
        }
        if ($filtroexpedientes == 'ESADL') {
            $query .= "AND (organizacion = '12' OR organizacion = '14' AND categoria = '1') ";
        }
        if ($filtroexpedientes == 'EST') {
            $query .= "AND (organizacion = '02') ";
        }
        if ($filtroexpedientes == 'SUC') {
            $query .= "AND (organizacion > '02' AND categoria = '2') ";
        }
        if ($filtroexpedientes == 'AGE') {
            $query .= "AND (organizacion > '02' AND categoria = '3') ";
        }
        if ($filtroexpedientes == 'SUC+AGE') {
            $query .= "AND (organizacion > '02' AND (categoria = '2' OR categoria = '3')) ";
        }
        if ($filtroexpedientes == 'EST+SUC+AGE') {
            $query .= "AND (organizacion = '02' OR (organizacion > '02' AND (categoria = '2' OR categoria = '3'))) ";
        }

        //
        if ($filtrociius != '') {

            $filtrociius = \funcionesGenerales::encomillar($filtrociius);

            if (trim($query) != '') {
                $query .= ' AND ';
            }

            $query .= "(";
            $query .= "(ciiu1 IN (" . $filtrociius . ")) OR ";
            $query .= "(ciiu2 IN (" . $filtrociius . ")) OR ";
            $query .= "(ciiu3 IN (" . $filtrociius . ")) OR ";
            $query .= "(ciiu4 IN (" . $filtrociius . "))";
            $query .= ")";
        }

        if ($filtroletraciius != '') {

            if (trim($query) != '') {
                $query .= ' AND ';
            }

            $query .= "(";
            $query .= "(ciiu1 LIKE '" . $filtroletraciius . "%') OR ";
            $query .= "(ciiu2 LIKE '" . $filtroletraciius . "%') OR ";
            $query .= "(ciiu3 LIKE '" . $filtroletraciius . "%') OR ";
            $query .= "(ciiu4 LIKE '" . $filtroletraciius . "%')";
            $query .= ")";
        }

        if ($filtromunicipios != '') {

            $filtromunicipios = \funcionesGenerales::encomillar($filtromunicipios);

            if (trim($query) != '') {
                $query .= " AND ";
            }

            $query .= "(";
            $query .= "(muncom IN (" . $filtromunicipios . "))";
            $query .= ")";
        }

        //
        if ($filtroestados != '') {

            $filtroestados = \funcionesGenerales::encomillar($filtroestados);

            if (trim($query) != '') {
                $query .= ' AND ';
            }

            $query .= "(";
            $query .= "(ctrestmatricula IN (" . $filtroestados . "))";
            $query .= ")";
        }


        //
        if ($filtroorganizaciones != '') {

            $filtroorganizaciones = \funcionesGenerales::encomillar($filtroorganizaciones);

            if (trim($query) != '') {
                $query .= ' AND ';
            }

            $query .= "(";
            $query .= "(organizacion IN (" . $filtroorganizaciones . "))";
            $query .= ")";
        }


        //
        if ($filtroanoren != '') {

            $filtroanoren = \funcionesGenerales::encomillar($filtroanoren);

            if (trim($query) != '') {
                $query .= ' AND ';
            }

            $query .= "(";
            $query .= "(ultanoren IN (" . $filtroanoren . "))";
            $query .= ")";
        }



        //
        if ($filtroempleados != '') {


            $filtroempleados = \funcionesGenerales::encomillar($filtroempleados);

            if (trim($query) != '') {
                $query .= ' AND ';
            }

            $query .= "(";
            $query .= "(personal IN (" . $filtroempleados . "))";
            $query .= ")";
        }


        //ctrimpexp
        if ($filtroexporta != '') {

            if (trim($query) != '') {
                $query .= ' AND ';
            }

            switch ($filtroexporta) {
                case "S":
                    $query .= "(ctrimpexp IN ('2','3'))";
                    break;
                case "N":
                    $query .= "(ctrimpexp NOT IN ('2','3'))";
                    break;
            }
        }


        //Rangos de activos
        if (($activosinicial != '') && ($activosfinal != '')) {
            if (trim($query) != '') {
                $query .= ' AND ';
            }
            $query .= "(acttot BETWEEN '" . $activosinicial . "' AND '" . $activosfinal . "')";
        }

        $txtCampos = '';
        foreach ($arrCamposMregEstMatriculadosExportar as $key => $dats) {
            if ($dats[0] == 'S') {
                if ($txtCampos != '') {
                    $txtCampos .= ', ';
                }
                $txtCampos .= $key;
            }
        }

          //Afiliados
          if ($filtroafiliado != '') {

            if (trim($query) != '') {
                $query .= ' AND ';
            }

            switch ($filtroafiliado) {
                case "S":
                    $query .= "(ctrafiliacion=1)";
                    break;
                case "N":
                    $query .= "(ctrafiliacion!=1)";
                    break;
            }
        }

        // Selecciona los años que se van a buscar en el historico de informacion financiera
        $j2 = date("Y");
        $j1 = $j2 - 4;
        $ano1 = '';
        $ano2 = '';
        $ano3 = '';
        $ano4 = '';
        $ano5 = '';
        $ix = 0;
        for ($j = $j1; $j <= $j2; $j++) {
            $ix++;
            switch ($ix) {
                case 1:
                    $ano1 = $j;
                    break;
                case 2:
                    $ano2 = $j;
                    break;
                case 3:
                    $ano3 = $j;
                    break;
                case 4:
                    $ano4 = $j;
                    break;
                case 5:
                    $ano5 = $j;
                    break;
            }
        }

        //Modificado por FK, para incluir el nombre del representante legal suplente o gerente suplente ticket 4DJGhH
        $txtCampos = $txtCampos . ",cpcodcam,cpnummat,cprazsoc,cpnumnit,cpdircom,cpnumtel,cpcodmun";

        // Se adiciona el número que generó la alcaldía cuando se envió la información.
        $txtCampos .= ", (SELECT matriculaic FROM mreg_matriculasalcaldia WHERE mreg_matriculasalcaldia.idmatricula=mreg_est_inscritos.matricula limit 1) AS numalcaldia ";

        // Verifica si tiene libros de comercio registrados.
        $txtCampos .= ", (SELECT libro FROM mreg_est_inscripciones WHERE mreg_est_inscripciones.matricula=mreg_est_inscritos.matricula AND mreg_est_inscripciones.libro IN ('RM07','RM52') AND mreg_est_inscripciones.codigolibro<>'' LIMIT 1) AS tienelibros";

        //
        $query .= " AND matricula NOT IN ('0','') AND trim(fecmatricula) NOT IN ('0','')";


        $queryG = "SELECT " . $txtCampos . " FROM mreg_est_inscritos WHERE " . $query . " ORDER BY matricula DESC";
        \logApi::general2($nameLog, $code, $queryG);

        if (TIPO_AMBIENTE == 'PRUEBAS' || TIPO_AMBIENTE == 'QA') {
            $_SESSION["jsonsalida"]["query"] = $queryG;
           // die($queryG);
        }

        //
        actualizarLogMysqliApi($mysqliu, '026', $_SESSION["entrada"]["idusuario"], 'API-exportarMatriculados', '', '', '', $queryG, '', '');


        // *********************************************************************************************************** //
        // Carga en memoria la información de todos las matrículas para verificaciones posteriores
        // *********************************************************************************************************** //
        $arrMats = array();

        //

        $condicionConsulta = "matricula NOT IN ('0','')";
        $ordenConsulta = "matricula";
        $camposConsulta = "matricula,razonsocial,organizacion,categoria,ctrestmatricula,cpcodcam,cpnummat";

        $tems = retornarRegistrosMysqliApi($mysqli, "mreg_est_inscritos", $condicionConsulta, $ordenConsulta, $camposConsulta);
        foreach ($tems as $tx) {
            $tx["matricula"] = ltrim(trim((string)$tx["matricula"]), "0");
            if ($tx["matricula"] != '') {
                $arrMats[$tx["matricula"]] = $tx;
            }
        }
        unset($tems);
        \logApi::general2($nameLog, $code, 'cargo indice de matriculas');

        // *********************************************************************************************************** //
        // Carga en memoria la información de propietarios
        // *********************************************************************************************************** //    
        $arrProps = array();
        $tems = retornarRegistrosMysqliApi($mysqli, "mreg_est_propietarios", "1=1", "matricula", "matricula,matriculapropietario");
        foreach ($tems as $tx) {
            $tx["matricula"] = ltrim(trim((string)$tx["matricula"]), "0");
            $tx["matriculapropietario"] = ltrim(trim((string)$tx["matriculapropietario"]), "0");
            if (isset($arrMats[$tx["matricula"]])) {
                if (
                    $arrMats[$tx["matricula"]]["ctrestmatricula"] == 'MA' ||
                    $arrMats[$tx["matricula"]]["ctrestmatricula"] == 'MI' ||
                    $arrMats[$tx["matricula"]]["ctrestmatricula"] == 'IA' ||
                    $arrMats[$tx["matricula"]]["ctrestmatricula"] == 'II'
                ) {
                    if (trim((string)$tx["matriculapropietario"]) != '') {
                        if (isset($arrMats[$tx["matriculapropietario"]])) {
                            if (
                                $arrMats[$tx["matriculapropietario"]]["ctrestmatricula"] == 'MA' ||
                                $arrMats[$tx["matriculapropietario"]]["ctrestmatricula"] == 'MI' ||
                                $arrMats[$tx["matriculapropietario"]]["ctrestmatricula"] == 'IA' ||
                                $arrMats[$tx["matriculapropietario"]]["ctrestmatricula"] == 'II'
                            ) {
                                if (!isset($arrProps[$tx["matriculapropietario"]])) {
                                    $arrProps[$tx["matriculapropietario"]] = array();
                                }
                                $arrProps[$tx["matriculapropietario"]][] = $tx["matricula"];
                            }
                        }
                    }
                }
            }
        }
        unset($tems);
        \logApi::general2($nameLog, $code, 'cargo indice de propietarios');

        // *********************************************************************************************************** //
        // Carga en memoria la información de sucursales y agencias
        // *********************************************************************************************************** //        
        $arrSucs = array();
        $tems = retornarRegistrosMysqliApi($mysqli, "mreg_est_inscritos", "categoria IN ('2','3') AND ctrestmatricula IN ('MA','IA','MI','II')", "matricula", "matricula,cpcodcam,cpnummat");
        foreach ($tems as $tx) {
            $tx["cpnummat"] = ltrim(trim((string)$tx["cpnummat"]), "0");
            if (trim($tx["cpcodcam"]) == '' || $tx["cpcodcam"] == CODIGO_EMPRESA) {
                if (isset($arrMats[$tx["cpnummat"]])) {
                    if (
                        $arrMats[$tx["cpnummat"]]["ctrestmatricula"] == 'MA' ||
                        $arrMats[$tx["cpnummat"]]["ctrestmatricula"] == 'MI' ||
                        $arrMats[$tx["cpnummat"]]["ctrestmatricula"] == 'IA' ||
                        $arrMats[$tx["cpnummat"]]["ctrestmatricula"] == 'II'
                    ) {
                        if (!isset($arrSucs[$tx["cpnummat"]])) {
                            $arrSucs[$tx["cpnummat"]] = array();
                        }
                        $arrSucs[$tx["cpnummat"]][] = $tx["matricula"];
                    }
                }
            }
        }
        unset($tems);
        \logApi::general2($nameLog, $code, 'cargo indice de sucursales y agencias');

        \logApi::general2($nameLog, $code, 'Query a ejecutar : ' . $query);
        $mysqli->set_charset("utf8");
        $result = $mysqli->query($queryG);
        \logApi::general2($nameLog, $code, 'Ejecuto query de seleccion');

        if (!$result) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = $mysqli->error;
            $mysqli->close();
            $mysqliu->close();
            \logApi::general2($nameLog, $code, 'Error : ' . $mysqli->error);
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        if ($mysqli->affected_rows == -1 || $mysqli->affected_rows == 0) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'No se encontraron registros para los criterios seleccionados';
            $mysqli->close();
            $mysqliu->close();
            \logApi::general2($nameLog, $code, 'No se encontraron registros para los criterios seleccionados');
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        //
        $cant = 0;
        $name1 = $_SESSION["generales"]["codigoempresa"] . '-exportarMatriculadosApi-' . session_id() . date("Ymd") . '-' . date("His") . '.csv';
        $name = $_SESSION["generales"]["pathabsoluto"] . '/tmp/' . $name1;
        $f = fopen($name, "w");

        /* WSI 19-MAY-2016
         * Iniciar archivo con la secuencia BOM para abrir CSV
         * en codificación utf-8
         */
        fwrite($f, chr(0xEF) . chr(0xBB) . chr(0xBF));

        $lin = '';
        foreach ($arrCamposMregEstMatriculadosExportar as $key => $dats) {
            if ($dats[0] == 'S' || $dats[0] == 'NS') {
                $tit = str_replace(array("{ano1}", "{ano2}", "{ano3}", "{ano4}", "{ano5}"), array($ano1, $ano2, $ano3, $ano4, $ano5), $dats[1]);
                $lin .= $tit . ';';
            }
        }
        $lin .= chr(13) . chr(10);
        \logApi::general2($nameLog, $code, 'Armo linea de titulos');

        //
        fwrite($f, $lin);

        $iMatriculas = 0;
        while ($reg = mysqli_fetch_assoc($result)) {

            if (ltrim(trim($reg["matricula"]), "0") != '') {
                // Log::general2($nameLog, $code, 'Exporto : ' . $reg["matricula"]);
                //
                $reg["razonsocial"] = str_replace(array(",", ";"), " ", $reg["razonsocial"]);
                $reg["matprop"] = '';
                $reg["numidprop"] = '';
                $reg["nitprop"] = '';
                $reg["camaraprop"] = '';
                $reg["nomprop"] = '';
                $reg["dirprop"] = '';
                $reg["telprop"] = '';
                $reg["celprop"] = '';
                $reg["munprop"] = '';
                $reg["emailprop"] = '';

                $reg["anorenaflia"] = '';
                $reg["fecrenaflia"] = '';
                $reg["valpagaflia"] = '';

                $reg["fecano1"] = '';
                $reg["fecano2"] = '';
                $reg["fecano3"] = '';
                $reg["fecano4"] = '';
                $reg["fecano5"] = '';

                $reg["actano1"] = '';
                $reg["actano2"] = '';
                $reg["actano3"] = '';
                $reg["actano4"] = '';
                $reg["actano5"] = '';

                $reg["actvinano1"] = '';
                $reg["actvinano2"] = '';
                $reg["actvinano3"] = '';
                $reg["actvinano4"] = '';
                $reg["actvinano5"] = '';

                $reg["fechadatosult"] = '';
                $reg["actcteult"] = '';
                $reg["actnocteult"] = '';
                $reg["actfijult"] = '';
                $reg["actvalult"] = '';
                $reg["actotrult"] = '';
                $reg["acttotult"] = '';
                $reg["pascteult"] = '';
                $reg["paslarult"] = '';
                $reg["pastotult"] = '';
                $reg["pattotult"] = '';

                $reg["paspatult"] = '';
                $reg["ingopeult"] = '';
                $reg["ingnoopeult"] = '';
                $reg["gasopeult"] = '';
                $reg["gasnoopeult"] = '';

                $reg["utinetult"] = '';
                $reg["utiopeult"] = '';

                $reg["personalult"] = '';

                // 2018-01-11: JINT
                $reg["cantestbd"] = '';
                $reg["cantsucbd"] = '';

                // 2018-05-17: JINT
                $reg["iderep1"] = '';
                $reg["nomrep1"] = '';
                $reg["iderep2"] = '';
                $reg["nomrep2"] = '';
                $reg["iderep3"] = '';
                $reg["nomrep3"] = '';
                $reg["idesup1"] = '';
                $reg["nomsup1"] = '';
                $reg["idesup2"] = '';
                $reg["nomsup2"] = '';
                $reg["idesup3"] = '';
                $reg["nomsup3"] = '';
                $reg["numsocios"] = 0;

                //
                if ($reg["ctrafiliacion"] == '1') {
                
                    $resultadoAfiliado = buscarSaldoAfiliadoMysqliApi($mysqli,ltrim(trim($reg["matricula"]), "0"), '', $formaCalculoAfiliacion);
                    if ($resultadoAfiliado["ultanorenafi"] != '') {
                        $reg["anorenaflia"] = $resultadoAfiliado["ultanorenafi"];
                    }
                    $reg["valpagaflia"] = $resultadoAfiliado["valorultpagoafi"];
                    $reg["fecrenaflia"] = $resultadoAfiliado["fechaultpagoafi"];
                }

                // 
                $txtGrupoNiif = '';
                switch ($reg["gruponiif"]) {
                    case '1':
                        $txtGrupoNiif = '2.- GRUPO I - NIIF PLENAS';
                        break;
                    case '2':
                        $txtGrupoNiif = '3.- GRUPO II';
                        break;
                    case '3':
                        $txtGrupoNiif = '4.- GRUPO III - MICROEMPRESAS';
                        break;
                    case '4':
                        $txtGrupoNiif = '5.- RESOLUCION 414/2014';
                        break;
                    case '5':
                        $txtGrupoNiif = '6.- ENTIDADES DE GOBIERNO - RESOLUCION 533/2015';
                        break;
                    case '6':
                        $txtGrupoNiif = '7.- DECRETO 2649/1993 - SUPERSALUD Y SUPERSUBSIDIO';
                        break;
                    case '7':
                        $txtGrupoNiif = '1.- ENTIDADES PUBLICAS ART. 2 RES. 743 / 2013';
                        break;
                }
                $reg["gruponiif"] = $txtGrupoNiif;

                //
                $anox = "'" . $ano1 . "','" . $ano2 . "','" . $ano3 . "','" . $ano4 . "','" . $ano5 . "'";
                $query = "SELECT * FROM mreg_est_financiera where matricula='" . ltrim(trim((string)$reg["matricula"]), "0") . "' AND anodatos in (" . $anox . ") order by anodatos,fechadatos";
                $mysqli->set_charset("utf8");
                $resultFin = $mysqli->query($query);
                while ($x1 = mysqli_fetch_assoc($resultFin)) {
                    if ($x1["anodatos"] == $ano1) {
                        if ($reg["fecano1"] == '') {
                            $reg["fecano1"] = $x1["fechadatos"];
                            $reg["actano1"] = number_format($x1["acttot"], 0, ',', '.');
                            $reg["actvinano1"] = number_format($x1["actvin"], 0, ',', '.');
                        }
                    }
                    if ($x1["anodatos"] == $ano2) {
                        if ($reg["fecano2"] == '') {
                            $reg["fecano2"] = $x1["fechadatos"];
                            $reg["actano2"] = number_format($x1["acttot"], 0, ',', '.');
                            $reg["actvinano2"] = number_format($x1["actvin"], 0, ',', '.');
                        }
                    }
                    if ($x1["anodatos"] == $ano3) {
                        if ($reg["fecano3"] == '') {
                            $reg["fecano3"] = $x1["fechadatos"];
                            $reg["actano3"] = number_format($x1["acttot"], 0, ',', '.');
                            $reg["actvinano3"] = number_format($x1["actvin"], 0, ',', '.');
                        }
                    }
                    if ($x1["anodatos"] == $ano4) {
                        if ($reg["fecano4"] == '') {
                            $reg["fecano4"] = $x1["fechadatos"];
                            $reg["actano4"] = number_format($x1["acttot"], 0, ',', '.');
                            $reg["actvinano4"] = number_format($x1["actvin"], 0, ',', '.');
                        }
                    }
                    if ($x1["anodatos"] == $ano5) {
                        if ($reg["fecano5"] == '') {
                            $reg["fecano5"] = $x1["fechadatos"];
                            $reg["actano5"] = number_format($x1["acttot"], 0, ',', '.');
                            $reg["actvinano5"] = number_format($x1["actvin"], 0, ',', '.');
                        }
                    }
                    $reg["fechadatosult"] = $x1["fechadatos"];
                    $reg["actcteult"] = number_format($x1["actcte"], 0, ',', '.');
                    $reg["actnocteult"] = number_format($x1["actnocte"], 0, ',', '.');

                    $reg["actnocteult"] = number_format($x1["actnocte"], 0, ',', '.');
                    $reg["actnocteult"] = number_format($x1["actnocte"], 0, ',', '.');
                    $reg["actfijult"] = number_format($x1["actfij"], 0, ',', '.');
                    $reg["actvalult"] = number_format($x1["actval"], 0, ',', '.');
                    $reg["actotrult"] = number_format($x1["actotr"], 0, ',', '.');
                    $reg["acttotult"] = number_format($x1["acttot"], 0, ',', '.');

                    $reg["pascteult"] = number_format($x1["pascte"], 0, ',', '.');
                    $reg["paslarult"] = number_format($x1["paslar"], 0, ',', '.');
                    $reg["pastotult"] = number_format($x1["pastot"], 0, ',', '.');
                    $reg["pattotult"] = number_format($x1["patnet"], 0, ',', '.');
                    $reg["paspatult"] = number_format($x1["paspat"], 0, ',', '.');

                    $reg["ingopeult"] = number_format($x1["ingope"], 0, ',', '.');
                    $reg["ingnoopeult"] = number_format($x1["ingnoope"], 0, ',', '.');
                    $reg["gasopeult"] = number_format($x1["gasope"], 0, ',', '.');
                    $reg["gasnoopeult"] = number_format($x1["gasnoope"], 0, ',', '.');

                    $reg["utiopeult"] = number_format($x1["utiope"], 0, ',', '.');
                    $reg["utinetult"] = number_format($x1["utinet"], 0, ',', '.');

                    //
                    $reg["personalult"] = $x1["personal"];
                }
                mysqli_free_result($resultFin);


                //
                if ($reg["organizacion"] == '02' && ltrim(trim((string)$reg["matricula"]), "0") != '') {

                    $query1 = "SELECT * FROM mreg_est_propietarios where matricula='" . ltrim(trim((string)$reg["matricula"]), "0") . "'";

                    $mysqli->set_charset("utf8");
                    $result1 = $mysqli->query($query1);
                    while ($reg1 = mysqli_fetch_assoc($result1)) {

                        if ($reg1["estado"] == 'V') {
                            if (ltrim(trim((string)$reg1["matriculapropietario"]), "0") != '') {
                                if (trim((string)$reg1["codigocamara"]) == '' || $reg1["codigocamara"] == CODIGO_EMPRESA) {
                                    $query2 = "SELECT * FROM mreg_est_inscritos where matricula='" . ltrim(trim((string)$reg1["matriculapropietario"]), "0") . "'";
                                    $mysqli->set_charset("utf8");
                                    $result2 = $mysqli->query($query2);
                                    while ($reg2 = mysqli_fetch_assoc($result2)) {
                                        $reg["matprop"] = $reg2["matricula"];
                                        $reg["numidprop"] = $reg2["numid"];
                                        $reg["nitprop"] = $reg2["nit"];
                                        $reg["camaraprop"] = CODIGO_EMPRESA;
                                        $reg["nomprop"] = $reg2["razonsocial"];
                                        $reg["dirprop"] = $reg2["dircom"];
                                        $reg["telprop"] = $reg2["telcom"];
                                        $reg["celprop"] = $reg2["telcom2"];
                                        $reg["munprop"] = $reg2["muncom"];
                                        $reg["emailprop"] = $reg2["emailcom"];
                                    }
                                    mysqli_free_result($result2);
                                } else {
                                    $reg["matprop"] = $reg1["matriculapropietario"];
                                    $reg["numidprop"] = $reg1["identificacion"];
                                    $reg["nitprop"] = $reg1["nit"];
                                    $reg["camaraprop"] = $reg1["camarapropietario"];
                                    $reg["nomprop"] = $reg1["razonsocial"];
                                    $reg["dirprop"] = $reg1["dircom"];
                                    $reg["telprop"] = $reg1["telcom1"];
                                    $reg["celprop"] = $reg1["telcom2"];
                                    $reg["munprop"] = $reg1["muncom"];
                                    $reg["emailprop"] = $reg1["emailcom"];
                                }
                            } else {
                                $reg["matprop"] = $reg1["matriculapropietario"];
                                $reg["numidprop"] = $reg1["identificacion"];
                                $reg["nitprop"] = $reg1["nit"];
                                $reg["camaraprop"] = $reg1["camarapropietario"];
                                $reg["nomprop"] = $reg1["razonsocial"];
                                $reg["dirprop"] = $reg1["dircom"];
                                $reg["telprop"] = $reg1["telcom1"];
                                $reg["celprop"] = $reg1["telcom2"];
                                $reg["munprop"] = $reg1["muncom"];
                                $reg["emailprop"] = $reg1["emailcom"];
                            }
                        }
                    }
                    //$reg["matprop"] = $reg["matprop"]."f";
                    mysqli_free_result($result1);
                }

                //
                if ($reg["categoria"] == '2' || $reg["categoria"] == '3') {
                    $reg["matprop"] = $reg1["cpnummat"];
                    $reg["numidprop"] = $reg1["cpnumnit"];
                    $reg["nitprop"] = $reg1["cpnumnit"];
                    $reg["camaraprop"] = $reg1["cpcodcam"];
                    $reg["nomprop"] = $reg1["cprazsoc"];
                    $reg["dirprop"] = $reg1["cpdircom"];
                    $reg["telprop"] = $reg1["cpnumtel"];
                    $reg["celprop"] = '';
                    $reg["munprop"] = $reg1["cpcodmun"];
                    $reg["emailprop"] = '';
                }

                //
                if (trim((string)$reg["tienelibros"]) != '') {
                    $reg["tienelibros"] = 'SI';
                } else {
                    $reg["tienelibros"] = '';
                }

                //
                if (ltrim((string)$reg["barriocom"], "0") == '') {
                    $txtBarrioCom = '';
                } else {
                    if (isset($arrBar[$reg["muncom"]][$reg["barriocom"]])) {
                        $txtBarrioCom = $reg["barriocom"] . ' - ' . $arrBar[$reg["muncom"]][$reg["barriocom"]];
                    } else {
                        $txtBarrioCom = $reg["barriocom"] . ' - NO IDENTIFICADO EN SII';
                    }
                }

                //
                if (ltrim((string)$reg["ctrclasegenesadl"], "0") == '') {
                    $txtClaseGen = '';
                } else {
                    if (isset($arr34[$reg["ctrclasegenesadl"]])) {
                        $txtClaseGen = $reg["ctrclasegenesadl"] . ' - ' . $arr34[$reg["ctrclasegenesadl"]];
                    } else {
                        $txtClaseGen = $reg["ctrclasegenesadl"] . ' - NO LOCALIZADO';
                    }
                }

                //
                if (ltrim((string)$reg["ctrclaseespeesadl"], "0") == '') {
                    $txtClaseEspe = '';
                } else {
                    if (isset($arr35[$reg["ctrclaseespeesadl"]])) {
                        $txtClaseEspe = $reg["ctrclaseespeesadl"] . ' - ' . $arr35[$reg["ctrclaseespeesadl"]];
                    } else {
                        $txtClaseEspe = $reg["ctrclaseespeesadl"] . ' - NO LOCALIZADO';
                    }
                }

                //
                if (ltrim((string)$reg["muncom"], "0") == '') {
                    $txtMunCom = '';
                } else {
                    if (isset($arrMun[$reg["muncom"]])) {
                        $txtMunCom = $reg["muncom"] . ' - ' . $arrMun[$reg["muncom"]];
                    } else {
                        $txtMunCom = $reg["muncom"] . ' - NO LOCALIZADO';
                    }
                }

                //
                if (ltrim((string)$reg["munnot"], "0") == '') {
                    $txtMunNot = '';
                } else {
                    if (isset($arrMun[$reg["munnot"]])) {
                        $txtMunNot = $reg["munnot"] . ' - ' . $arrMun[$reg["munnot"]];
                    } else {
                        $txtMunNot = $reg["munnot"] . ' - NO LOCALIZADO';
                    }
                }

                //
                $txtCiiu1 = '';
                $txtCiiu2 = '';
                $txtCiiu3 = '';
                $txtCiiu4 = '';
                $txtCiiu5 = '';

                if (trim((string)$reg["ciiu1"]) != '') {
                    if (!isset($arrCiius[$reg["ciiu1"]])) {
                        $txtCiiu1 = ' ** NO LOCALIZADO EN TABLA DE CIIUS';
                    } else {
                        $txtCiiu1 = ' ** ' . $arrCiius[$reg["ciiu1"]];
                    }
                }
                if (trim((string)$reg["ciiu2"]) != '') {
                    if (!isset($arrCiius[$reg["ciiu2"]])) {
                        $txtCiiu2 = ' ** NO LOCALIZADO EN TABLA DE CIIUS';
                    } else {
                        $txtCiiu2 = ' ** ' . $arrCiius[$reg["ciiu2"]];
                    }
                }
                if (trim((string)$reg["ciiu3"]) != '') {
                    if (!isset($arrCiius[$reg["ciiu3"]])) {
                        $txtCiiu3 = ' ** NO LOCALIZADO EN TABLA DE CIIUS';
                    } else {
                        $txtCiiu3 = ' ** ' . $arrCiius[$reg["ciiu3"]];
                    }
                }
                if (trim((string)$reg["ciiu4"]) != '') {
                    if (!isset($arrCiius[$reg["ciiu4"]])) {
                        $txtCiiu4 = ' ** NO LOCALIZADO EN TABLA DE CIIUS';
                    } else {
                        $txtCiiu4 = ' ** ' . $arrCiius[$reg["ciiu4"]];
                    }
                }
                if (trim((string)$reg["ciiu5"]) != '') {
                    if (!isset($arrCiius[$reg["ciiu5"]])) {
                        $txtCiiu5 = ' ** NO LOCALIZADO EN TABLA DE CIIUS';
                    } else {
                        $txtCiiu5 = ' ** ' . $arrCiius[$reg["ciiu5"]];
                    }
                }

                //
                $txtCiiu1a = str_replace(array("\r\n", "\r", "\n", ";", ","), " ", $txtCiiu1);
                $txtCiiu2a = str_replace(array("\r\n", "\r", "\n", ";", ","), " ", $txtCiiu2);
                $txtCiiu3a = str_replace(array("\r\n", "\r", "\n", ";", ","), " ", $txtCiiu3);
                $txtCiiu4a = str_replace(array("\r\n", "\r", "\n", ";", ","), " ", $txtCiiu4);
                $txtCiiu5a = str_replace(array("\r\n", "\r", "\n", ";", ","), " ", $txtCiiu5);

                //
                $txtImpExp = '';
                switch ($reg["ctrimpexp"]) {
                    case "1":
                        $txtImpExp = 'IMP';
                        break;
                    case "2":
                        $txtImpExp = 'EXP';
                        break;
                    case "3":
                        $txtImpExp = 'IMP+EXP';
                        break;
                }

                //
                $txtTipoProp = '';
                switch ($reg["ctrtipopropiedad"]) {
                    case "0":
                        $txtTipoProp = 'PROP-UNICO';
                        break;
                    case "1":
                        $txtTipoProp = 'SOC-HECHO';
                        break;
                    case "2":
                        $txtTipoProp = 'COOPROP.';
                        break;
                }

                // 2018-01-11: JINT: cantestbd
                if (isset($arrProps[$reg["matricula"]])) {
                    $reg["cantestbd"] = count($arrProps[$reg["matricula"]]);
                }

                // 2018-01-11: JINT: Sucursales y agencias
                if (isset($arrSucs[$reg["matricula"]])) {
                    $reg["cantsucbd"] = count($arrSucs[$reg["matricula"]]);
                }

                // 2018-05-17: JINT: Tamano de empresa
                $tamano = '';
                if ($reg["organizacion"] != '02' && $reg["categoria"] != '2' && $reg["categoria"] != '3') {
                    $val = str_replace(".", "", $reg["acttotult"]);
                    $val = str_replace(",", ".", $val);
                    $tamano = \funcionesGenerales::calcularTamanoEmpresarial($mysqli, $reg["matricula"], 'actual');
                    $reg["tamanoempresa"] = $tamano["textocompleto"];
                } else {
                    $reg["tamanoempresa"] = '';
                }

                // 2018-05-17: JINT: Vinculos de representacion
                if ($reg["organizacion"] > '02' && $reg["categoria"] == '1') {
                    $iPri = 0;
                    $iSec = 0;
                    $tvx = retornarRegistrosMysqliApi($mysqli, 'mreg_est_vinculos', "matricula='" . $reg["matricula"] . "'", "vinculo");
                    if ($tvx && !empty($tvx)) {
                        foreach ($tvx as $tv) {
                            if ($tv["estado"] != 'H') {
                                if (isset($arrVincs[$tv["vinculo"]])) {
                                    if ($arrVincs[$tv["vinculo"]]["tipovinculo"] == 'RLP') {
                                        $iPri++;
                                        switch ($iPri) {
                                            case 1:
                                                $reg["iderep1"] = $tv["numid"];
                                                $reg["nomrep1"] = $tv["nombre"];
                                                break;
                                            case 2:
                                                $reg["iderep2"] = $tv["numid"];
                                                $reg["nomrep2"] = $tv["nombre"];
                                                break;
                                            case 3:
                                                $reg["iderep3"] = $tv["numid"];
                                                $reg["nomrep3"] = $tv["nombre"];
                                                break;
                                        }
                                    }
                                    if ($arrVincs[$tv["vinculo"]]["tipovinculo"] == 'RLS') {
                                        $iSec++;
                                        switch ($iSec) {
                                            case 1:
                                                $reg["idesup1"] = $tv["numid"];
                                                $reg["nomsup1"] = $tv["nombre"];
                                                break;
                                            case 2:
                                                $reg["idesup2"] = $tv["numid"];
                                                $reg["nomsup2"] = $tv["nombre"];
                                                break;
                                            case 3:
                                                $reg["idesup3"] = $tv["numid"];
                                                $reg["nomsup3"] = $tv["nombre"];
                                                break;
                                        }
                                    }
                                    if ($arrVincs[$tv["vinculo"]]["tipovinculo"] == 'SOC') {
                                        $reg["numsocios"]++;
                                    }
                                }
                            }
                        }
                    }
                }



                //
                $cant++;
                $lin = '';
                foreach ($arrCamposMregEstMatriculadosExportar as $key => $dats) {
                    if ($dats[0] == 'S' || $dats[0] == 'NS') {
                        switch ($key) {
                            case "barriocom":
                                $lin .= $txtBarrioCom . ';';
                                break;
                            case "muncom":
                                $lin .= $txtMunCom . ';';
                                break;
                            case "munnot":
                                $lin .= $txtMunNot . ';';
                                break;
                            case "ctrclasegenesadl":
                                $lin .= $txtClaseGen . ';';
                                break;
                            case "ctrclaseespeesadl":
                                $lin .= $txtClaseEspe . ';';
                                break;
                            case "ctrimpexp":
                                $lin .= $txtImpExp . ';';
                                break;
                            case "ctrtipopropiedad":
                                $lin .= $txtTipoProp . ';';
                                break;
                            case "ciiu1":
                                $lin .= $reg["ciiu1"] . ($txtCiiu1a) . ';';
                                break;
                            case "ciiu2":
                                $lin .= $reg["ciiu2"] . ($txtCiiu2a) . ';';
                                break;
                            case "ciiu3":
                                $lin .= $reg["ciiu3"] . ($txtCiiu3a) . ';';
                                break;
                            case "ciiu4":
                                $lin .= $reg["ciiu4"] . ($txtCiiu4a) . ';';
                                break;
                            case "ciiu5":
                                $lin .= $reg["ciiu5"] . ($txtCiiu5a) . ';';
                                break;
                            case "actano1":
                                if ($reg["organizacion"] == '02' || $reg["categoria"] == '2' || $reg["categoria"] == '3') {
                                    $lin .= $reg["actvinano1"] . ';';
                                } else {
                                    $lin .= $reg["actano1"] . ';';
                                }
                                break;
                            case "actano2":
                                if ($reg["organizacion"] == '02' || $reg["categoria"] == '2' || $reg["categoria"] == '3') {
                                    $lin .= $reg["actvinano2"] . ';';
                                } else {
                                    $lin .= $reg["actano2"] . ';';
                                }
                                break;
                            case "actano3":
                                if ($reg["organizacion"] == '02' || $reg["categoria"] == '2' || $reg["categoria"] == '3') {
                                    $lin .= $reg["actvinano3"] . ';';
                                } else {
                                    $lin .= $reg["actano3"] . ';';
                                }
                                break;
                            case "actano4":
                                if ($reg["organizacion"] == '02' || $reg["categoria"] == '2' || $reg["categoria"] == '3') {
                                    $lin .= $reg["actvinano4"] . ';';
                                } else {
                                    $lin .= $reg["actano4"] . ';';
                                }
                                break;
                            case "actano5":
                                if ($reg["organizacion"] == '02' || $reg["categoria"] == '2' || $reg["categoria"] == '3') {
                                    $lin .= $reg["actvinano5"] . ';';
                                } else {
                                    $lin .= $reg["actano5"] . ';';
                                }
                                break;
                            case "numalcaldia":
                                $lin .= $reg["numalcaldia"] . ';';
                                break;

                            case "tamanoempresa":
                                $lin .= $reg["tamanoempresa"] . ';';
                                break;

                            case "iderep1":
                                $lin .= $reg["iderep1"] . ';';
                                break;

                            case "iderep2":
                                $lin .= $reg["iderep2"] . ';';
                                break;

                            case "iderep3":
                                $lin .= $reg["iderep3"] . ';';
                                break;

                            case "nomrep1":
                                $lin .= $reg["nomrep1"] . ';';
                                break;

                            case "nomrep2":
                                $lin .= $reg["nomrep2"] . ';';
                                break;

                            case "nomrep3":
                                $lin .= $reg["nomrep3"] . ';';
                                break;

                            case "numsocios":
                                $lin .= $reg["numsocios"] . ';';
                                break;


                            default:
                                $lin .= ($reg[$key]) . ';';
                                break;
                        }
                    }
                }

                //
                $lin .= chr(13) . chr(10);
                fwrite($f, $lin);
            }
        }
        fclose($f);

        mysqli_free_result($result);
        
        

        \logApi::general2($nameLog, $code, 'Finalizo extraccion');
        \logApi::general2($nameLog, $code, '');

        $horafin = date("H:i:s");
        $tamano = filesize($name);


        // 2016-03-08 : JINT : Adicionado para que en el log se actualice la extración que se realice.
        $txt = \funcionesGenerales::utf8_encode('Query: ' . $queryG) . ' - Extraidos: ' . $cant . ' - Tamaño: ' . $tamano;
        actualizarLogMysqliApi($mysqliu, '026', $_SESSION["entrada"]["idusuario"], 'API-exportarMatriculados', '', '', '', addslashes($txt), '', '');

        //
        $datefin = date("Ymd") . ' ' . date("His");

        $mysqli->close();
        $mysqliu->close();
        
        //
        $_SESSION["jsonsalida"]["codigoerror"] = '0000';
        $_SESSION["jsonsalida"]["mensajeerror"] = '';
        $_SESSION["jsonsalida"]["cantidad"] = $cant;
        $_SESSION["jsonsalida"]["tamano"] = $tamano;
        $_SESSION["jsonsalida"]["fechaini"] = $dateini;
        $_SESSION["jsonsalida"]["fechafin"] = $datefin;
        $_SESSION["jsonsalida"]["archivo"] = TIPO_HTTP . HTTP_HOST . '/tmp/' . $name1;
        $json = $api->json($_SESSION["jsonsalida"]);
        $api->response(str_replace("\\/", "/", $json), 200);
    }



    public function encontrarFechasCancelacion()
    {
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        
        ini_set("memory_limit", "2048M");
        ini_set("display_errors", "1");
        $mysqli = conexionMysqliApi();
        
        // Carga tabla de actos
        $temx = retornarRegistrosMysqliApi($mysqli, "mreg_actos", "1=1", "idlibro,idacto");
        $tactos = array();
        foreach ($temx as $tx) {
            $ind = $tx["idlibro"] . '-' . $tx["idacto"];
            $tactos[$ind] = $tx;
        }
        unset($temx);

        // Busca matrículas canceladas sin fecha de cancelacion
        $regs = retornarRegistrosMysqliApi($mysqli, 'mreg_est_inscritos', "ctrestmatricula IN ('MC','MF','IC','IF') AND feccancelacion < '19000101' AND matricula > ''", "matricula", "matricula,ctrestmatricula");
        if ($regs && !empty($regs)) {
            foreach ($regs as $rx) {
                $fcan = '';
                $actos = retornarRegistrosMysqliApi($mysqli, 'mreg_est_inscripciones', "matricula='" . $rx["matricula"] . "'", "fecharegistro");
                if ($actos && !empty($actos)) {
                    foreach ($actos as $acx) {
                        $ind = $acx["libro"] . '-' . $acx["acto"];
                        if (
                            $tactos[$ind]["idgrupoacto"] == '002' || // Cancelaciones
                            $tactos[$ind]["idgrupoacto"] == '069' || // Cambios de domicilio salen
                            $tactos[$ind]["idgrupoacto"] == '010'
                        ) { // Liquidaciones 
                            $fcan = $acx["fecharegistro"];
                        }
                    }
                }
                if ($fcan != '') {
                    $arrCampos = array('feccancelacion');
                    $arrValores = array("'" . $fcan . "'");
                    regrabarRegistrosMysqliApi($mysqli, 'mreg_est_inscritos', $arrCampos, $arrValores, "matricula='" . $rx["matricula"] . "'");
                }
            }
        }
        $mysqli->close();
        return true;
    }
}


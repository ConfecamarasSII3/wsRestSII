<?php

class funcionesRegistrales_retornarExpedienteMercantil {

    public static function retornarExpedienteMercantil($dbx = null, $mat = '', $idclase = '', $numid = '', $namex = '', $tipodata = '', $tipoconsulta = 'T', $establecimientosnacionales = 'N', $serviciosMatriculaE = array(), $serviciosRenovacionE = array(), $serviciosAfiliacionE = array()) {
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRues.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/myErrorHandler.php');

        set_error_handler('myErrorHandler');

        $nameLog = 'retornarExpedienteMercantil_' . $mat;
        $genlog = 'no';

        if ($genlog == 'si') {
            \logApi::general2($nameLog, $mat, 'Inicia lectura expediente');
        }

        // ********************************************************************************** //
        // Instancia la BD si no existe
        // ********************************************************************************** //
        if ($dbx === null) {
            $mysqli = new \mysqli(DB_HOST, DB_USUARIO, DB_PASSWORD, DB_NAME, DB_PORT);
            if (mysqli_connect_error()) {
                $_SESSION["generales"]["mensajerror"] = 'Error coenctando con la BD';
                return false;
            }
        } else {
            $mysqli = $dbx;
        }

        // ********************************************************************************** //
        // Define constantes y variables de control
        // ********************************************************************************** //
        $_SESSION["generales"]["fcorte"] = retornarRegistroMysqliApi($mysqli, 'mreg_cortes_renovacion', "ano='" . date("Y") . "'", "corte");
        $_SESSION["generales"]["fcortemesdia"] = substr($_SESSION["generales"]["fcorte"], 4, 4);
        $_SESSION["generales"]["FECHA_CORTE_REVOCACION"] = '20170101';

        // ********************************************************************************** //
        // carga maestro de actos
        // ********************************************************************************** //
        $_SESSION["maestroactos"] = array();
        $temx = retornarRegistrosMysqliApi($mysqli, "mreg_actos", "1=1", "idlibro,idacto");
        foreach ($temx as $x) {
            $ind = $x["idlibro"] . '-' . $x["idacto"];
            $_SESSION["maestroactos"][$ind] = $x;
        }
        unset($temx);

        // ********************************************************************************** //
        // carga maestro de vínculos
        // ********************************************************************************** //
        $_SESSION["maestrovinculos"] = array();
        $temx = retornarRegistrosMysqliApi($mysqli, "mreg_codvinculos", "1=1", "id");
        foreach ($temx as $x) {
            $_SESSION["maestrovinculos"][$x["id"]] = $x;
        }
        unset($temx);

        // ********************************************************************************** //
        // carga maestro de clases de vínculos
        // ********************************************************************************** //
        $_SESSION["clasevinculo"] = array();
        $temx = retornarRegistrosMysqliApi($mysqli, "mreg_clasevinculos", "1=1", "id");
        foreach ($temx as $x) {
            $_SESSION["clasevinculo"][$x["id"]] = $x["descripcion"];
        }
        unset($temx);

        // ********************************************************************************** //
        // carga maestro de certificads
        // ********************************************************************************** //
        $_SESSION["maestrocertificas"] = array();
        $temx = retornarRegistrosMysqliApi($mysqli, "mreg_codigos_certificas", "1=1", "id");
        foreach ($temx as $x) {
            $_SESSION["maestrocertificas"][$x["id"]] = $x;
        }
        unset($temx);

        //
        if (empty($serviciosMatriculaE)) {
            $serviciosRenovacion = array();
            $serviciosMatricula = array();
            $serviciosAfiliacion = array();
            $temx = retornarRegistrosMysqliApi($dbx, "mreg_servicios", "tipoingreso IN ('02','03','12','13')", "idservicio");
            foreach ($temx as $x1) {
                if ($x1["tipoingreso"] == '03' || $x1["tipoingreso"] == '13') {
                    $serviciosRenovacion[$x1["idservicio"]] = $x1["idservicio"];
                }
                if ($x1["tipoingreso"] == '02' || $x1["tipoingreso"] == '12') {
                    $serviciosMatricula[$x1["idservicio"]] = $x1["idservicio"];
                }
            }
            unset($temx);
            $temx = retornarRegistrosMysqliApi($dbx, "mreg_servicios", "1=1", "idservicio");
            foreach ($temx as $x1) {
                if ($x1["grupoventas"] == '02') {
                    $serviciosAfiliacion[$x1["idservicio"]] = $x1["idservicio"];
                }
            }
            unset($temx);
        } else {
            $serviciosMatricula = $serviciosMatriculaE;
            $serviciosRenovacion = $serviciosRenovacionE;
            $serviciosAfiliacion = $serviciosAfiliacionE;
        }

        // ************************************************************************************** //
        // Si el sistema registro es SII
        // ************************************************************************************** //
        // Configuracion del QUERY Inicial
        $siquery = 'no';
        $query = '';
        if ($mat != '') {
            $query = "matricula='" . ltrim($mat, "0") . "'";
            $siquery = 'si';
        }

        if ($siquery == 'no') {
            if ($idclase != '') {
                $query = "idclase='" . $idclase . "' and ";
                $query .= "numid='" . trim($numid) . "'";
                if ($tipoconsulta == 'A') {
                    $query .= " and ctrestmatricula IN ('MA','IA','MI','II')";
                }
                if ($tipoconsulta == 'C') {
                    $query .= " and ctrestmatricula NOT IN ('MA','IA','MI','II')";
                }
                $siquery = 'si';
            }
        }
        if ($siquery == 'no') {
            if ($numid != '') {
                $query = "(numid='" . ltrim($numid, "0") . "' or nit='" . ltrim($numid, "0") . "')";
                if ($tipoconsulta == 'A') {
                    $query .= " and ctrestmatricula IN ('MA','IA','MI','II')";
                }
                if ($tipoconsulta == 'C') {
                    $query .= " and ctrestmatricula NOT IN ('MA','IA','MI','II')";
                }
                $siquery = 'si';
            }
        }

        if ($siquery == 'no') {
            if ($namex != '') {
                $query = "razonsocial='" . $namex . "'";
                $siquery = 'si';
            }
        }


        // Lectura del registro principal
        $reg = retornarRegistroMysqliApi($mysqli, 'mreg_est_inscritos', $query, '*', 'U');
        if ($reg === false) {
            if ($dbx == null) {
                $mysqli->close();
            }
            $_SESSION["generales"]["mensajerror"] = '1.- Error leyendo la tabla mreg_est_inscritos';
            return false;
        }
        if (empty($reg)) {
            if ($dbx == null) {
                $mysqli->close();
            }
            $_SESSION["generales"]["mensajerror"] = '';
            return 0;
        }

        if ($genlog == 'si') {
            \logApi::general2($nameLog, $mat, 'Leyo mreg_est_inscritos');
        }

        // ****************************************************************************** //
        // Validaciones
        // ****************************************************************************** //
        $txtErrores = '';
        if (strlen(trim($reg["fecmatricula"])) != 8 || !\funcionesGenerales::validarFecha($reg["fecmatricula"])) {
            \logApi::general2($nameLog, $mat, 'Fecha de matricula erronea');
            $txtErrores .= 'Se detecta error en fecha de matrícula para el expediente ' . $mat . "\r\n";
            // return false;
        }

        if (strlen(trim($reg["fecrenovacion"])) != 8 || !\funcionesGenerales::validarFecha($reg["fecrenovacion"])) {
            \logApi::general2($nameLog, $mat, 'Fecha de renovacion erronea');
            $txtErrores .= 'Se detecta error en fecha de renovación para el expediente ' . $mat . "\r\n";
            // return false;
        }
        if (trim($reg["fecrenant"]) != '' && !\funcionesGenerales::validarFecha(trim($reg["fecrenant"]))) {
            $txtErrores .= 'Se detecta error en fecha de renovación (camara anterior) para el expediente ' . $mat . "\r\n";
            \logApi::general2($nameLog, $mat, 'Fecha de renovacion en camara anterior erronea');
            // return false;
        }
        // \funcionesgenerales::enviarCorreoError($txtErrores);
        //
        $reg["numid"] = trim(str_replace(array(".", ",", "-", " "), "", $reg["numid"]));
        $reg["nit"] = trim(str_replace(array(".", ",", "-", " "), "", $reg["nit"]));

        // Armado del arreglo de respuesta
        $retorno = array();
        $retorno["matricula"] = $reg["matricula"];
        $retorno["proponente"] = $reg["proponente"];
        $retorno["numerorecibo"] = $reg["numrecibo"];
        $retorno["extinciondominio"] = '';
        $retorno["extinciondominiofechainicio"] = '';
        $retorno["extinciondominiofechafinal"] = '';
        $retorno["ctrcontrolaccesopublico"] = '';

        //
        $retorno["complementorazonsocial"] = '';
        if (isset($reg["complementorazonsocial"])) {
            $retorno["complementorazonsocial"] = stripslashes(\funcionesGenerales::restaurarEspeciales((string) $reg["complementorazonsocial"]));
        }
        // $retorno["nombre"] = stripslashes(\funcionesGenerales::restaurarEspeciales($reg["razonsocial"]));        
        $retorno["nombre"] = str_replace("¥", "Ñ", stripslashes(\funcionesGenerales::restaurarEspeciales((string) $reg["razonsocial"])));
        $retorno["nombre"] = trim($retorno["nombre"]);
        $retorno["nuevonombre"] = '';
        $retorno["nombrebase64"] = '';
        $retorno["nombrebase64decodificado"] = '';
        $retorno["sigla"] = str_replace("¥", "Ñ", stripslashes(\funcionesGenerales::restaurarEspeciales((string) $reg["sigla"])));
        $retorno["siglabase64"] = '';
        $retorno["siglabase64decodificada"] = '';

        $temx = retornarRegistrosMysqliApi($mysqli, 'mreg_est_inscritos_campos', "matricula='" . ltrim($retorno["matricula"], "0") . "'", "campo");
        foreach ($temx as $tx) {
            if (trim($tx["contenido"]) != '') {
                if ($tx["campo"] == 'nombrebase64') {
                    $retorno[$tx["campo"]] = $tx["contenido"];
                }
                if ($tx["campo"] == 'siglabase64') {
                    $retorno[$tx["campo"]] = $tx["contenido"];
                }
            }
        }

        if ($retorno["nombrebase64"] != '') {
            $retorno["nombrebase64decodificado"] = base64_decode($retorno["nombrebase64"]);
        }
        if ($retorno["siglabase64"] != '') {
            $retorno["siglabase64decodificada"] = base64_decode($retorno["siglabase64"]);
        }

        if ($retorno["nombrebase64decodificado"] != '' && $retorno["nombrebase64decodificado"] != $retorno["nombre"]) {
            $retorno["nombre"] = $retorno["nombrebase64decodificado"];
        }

        if ($retorno["siglabase64decodificada"] != '' && $retorno["siglabase64decodificada"] != $retorno["sigla"]) {
            $retorno["sigla"] = $retorno["siglabase64decodificada"];
        }

        $retorno["nombre"] = \funcionesGenerales::borrarpalabrasAutomaticas($retorno["nombre"], $retorno["complementorazonsocial"]);

        $retorno["ape1"] = str_replace("¥", "Ñ", stripslashes(\funcionesGenerales::restaurarEspeciales((string) $reg["apellido1"])));
        $retorno["ape2"] = str_replace("¥", "Ñ", stripslashes(\funcionesGenerales::restaurarEspeciales((string) $reg["apellido2"])));
        $retorno["nom1"] = str_replace("¥", "Ñ", stripslashes(\funcionesGenerales::restaurarEspeciales((string) $reg["nombre1"])));
        $retorno["nom2"] = str_replace("¥", "Ñ", stripslashes(\funcionesGenerales::restaurarEspeciales((string) $reg["nombre2"])));
        $retorno["nombrerues"] = $retorno["nombre"];

        if ($reg["organizacion"] == '01') {
            $nomx = '';
            if (trim($retorno["ape1"]) != '') {
                $nomx .= trim($retorno["ape1"]);
            }
            if (trim($retorno["ape2"]) != '') {
                $nomx .= ' ' . trim($retorno["ape2"]);
            }
            if (trim($retorno["nom1"]) != '') {
                $nomx .= ' ' . trim($retorno["nom1"]);
            }
            if (trim($retorno["nom2"]) != '') {
                $nomx .= ' ' . trim($retorno["nom2"]);
            }
            if (trim($nomx) != '') {
                $retorno["nombre"] = $nomx;
                $retorno["nombrerues"] = $nomx;
            }
        }

        $retorno["tipoidentificacion"] = $reg["idclase"];
        $retorno["identificacion"] = $reg["numid"];
        $retorno["sexo"] = $reg["sexo"];
        $retorno["etnia"] = '';
        $retorno["emprendimientosocial"] = '';
        $retorno["idmunidoc"] = $reg["idmunidoc"];
        $retorno["fechanacimiento"] = $reg["fechanacimiento"];
        $retorno["fecexpdoc"] = $reg["fecexpdoc"];
        $retorno["paisexpdoc"] = $reg["paisexpdoc"];
        $retorno["nit"] = $reg["nit"];
        $retorno["nitsindv"] = '';
        $retorno["dv"] = '';
        if (trim($retorno["nit"]) != '') {
            $nit1 = sprintf("%020s", trim((string) $retorno["nit"]));
            $retorno["nitsindv"] = ltrim(substr($nit1, 0, 19), "0");
            $retorno["dv"] = substr($nit1, 19, 1);
        }
        $retorno["estadonit"] = $reg["estadonit"];
        $retorno["admondian"] = $reg["admondian"];
        $retorno["prerut"] = $reg["prerut"];
        $retorno["nacionalidad"] = $reg["nacionalidad"];

        // 2017-05-31
        $retorno["idetripaiori"] = $reg["idetripaiori"]; // Nuevo campo circular 002
        $retorno["paiori"] = $reg["paiori"]; // Nuevo campo circular 002
        $retorno["idetriextep"] = $reg["idetriextep"]; // Nuevo campo circular 002
        $retorno["ideext"] = $reg["numidextenso"]; // Nuevo campo circular 002


        $retorno["fechamatricula"] = trim((string) $reg["fecmatricula"]);
        $retorno["fecharenovacion"] = trim((string) $reg["fecrenovacion"]);
        $retorno["fecharenovacioninscritos"] = trim((string) $reg["fecrenovacion"]);
        $retorno["fechavencimiento"] = trim((string) $reg["fecvigencia"]);
        $retorno["fechavencimiento1"] = '';
        $retorno["fechavencimiento2"] = '';
        $retorno["fechavencimiento3"] = '';
        $retorno["fechavencimiento4"] = '';
        $retorno["fechavencimiento5"] = '';
        $retorno["fechadisolucioncontrolbeneficios1756"] = '';
        $retorno["fechareactivacioncontrolbeneficios1756"] = '';

        //
        $rvs = retornarRegistrosMysqliApi($mysqli, 'mreg_est_vigencias', "matricula='" . $reg["matricula"] . "'", "fecha");
        $ivs = 0;
        if ($rvs && !empty($rvs)) {
            foreach ($rvs as $rv) {
                $ivs++;
                $retorno["fechavencimiento" . $ivs] = $rv["fecha"];
            }
        }

        //
        $retorno["ultanoren"] = trim($reg["ultanoren"]);
        $retorno["ultanoreninscritos"] = trim($reg["ultanoren"]);

        if (strlen($retorno["fecharenovacion"]) != 8) {
            $retorno["fecharenovacion"] = $retorno["fechamatricula"];
        }
        if ($retorno["ultanoren"] == '') {
            $retorno["ultanoren"] = substr($retorno["fechamatricula"], 0, 4);
            $retorno["ultanoreninscritos"] = trim($reg["ultanoren"]);
        }

        $retorno["obligadorenovar"] = '';
        if (isset($reg["obligadorenovar"])) {
            $retorno["obligadorenovar"] = $reg["obligadorenovar"];
        }

        // Se encuentran al momento de armar la lista de inscripciones        
        $retorno["fechaconstitucion"] = $reg["fecconstitucion"];
        $retorno["fechadisolucion"] = $reg["fecdisolucion"];
        $retorno["fechacancelacion"] = $reg["feccancelacion"];
        $retorno["motivocancelacion"] = $reg["motivocancelacion"];
        $retorno["descripcionmotivocancelacion"] = '';
        $retorno["fechaliquidacion"] = $reg["fecliquidacion"];
        $retorno["disueltaporvencimiento"] = '';
        $retorno["disueltaporacto510"] = '';
        $retorno["fechaacto510"] = '';
        $retorno["fechaacto511"] = '';
        $retorno["perdidacalidadcomerciante"] = '';
        $retorno["fechaperdidacalidadcomerciante"] = '';
        $retorno["fechareactivacioncalidadcomerciante"] = '';
        $retorno["estadotipoliquidacion"] = $reg["estadotipoliquidacion"];
        $retorno["empresafamiliar"] = $reg["empresafamiliar"];
        $retorno["procesosinnovacion"] = $reg["procesosinnovacion"];
        $retorno["estadisuelta"] = '';
        $retorno["norenovado"] = '';
        // 
        if (
                trim($retorno["fechavencimiento"]) != '' &&
                $retorno["fechavencimiento"] != '99999999' && $retorno["fechavencimiento"] != '99999998'
        ) {
            if ($retorno["fechavencimiento"] < date("Ymd")) {
                $retorno["disueltaporvencimiento"] = 'si';
                $retorno["fechadisolucion"] = $retorno["fechavencimiento"];
            }
        }

        //
        $retorno["certificardesde"] = $reg["ctrcertificardesde"];
        $retorno["estadomatricula"] = $reg["ctrestmatricula"];
        $retorno["estadoactiva"] = $reg["ctrestadoactiva"];
        $retorno["estadopreoperativa"] = $reg["ctrestadopreoperativa"];
        $retorno["estadoconcordato"] = $reg["ctrconcordato"];
        $retorno["estadointervenida"] = $reg["ctrestadointervenida"];
        $retorno["estadodisuelta"] = $reg["ctrestadodisuelta"];
        $retorno["estadoreestructuracion"] = $reg["ctrestadoenreestructuracion"];
        $retorno["estadodatosmatricula"] = $reg["ctrestdatos"];
        $retorno["estadoproponente"] = trim((string) $reg["ctrestproponente"]);
        $retorno["estadocapturado"] = trim((string) $reg["ctrestadocapturado"]);
        $retorno["estadocapturadootros"] = trim((string) $reg["ctrestadocapturadootros"]);
        $retorno["cantest"] = intval($reg["cantest"]);

        //
        if (!isset($reg["pendiente_ajuste_nuevo_formato"])) {
            $reg["pendiente_ajuste_nuevo_formato"] = '';
        }
        if (!isset($reg["fecha_pendiente_ajuste_nuevo_formato"])) {
            $reg["fecha_pendiente_ajuste_nuevo_formato"] = '';
        }

        //
        $retorno["codigosbarras"] = 0; // Se arma al encontrar la lista de códigos de barras
        $retorno["pendiente_ajuste_nuevo_formato"] = $reg["pendiente_ajuste_nuevo_formato"];
        $retorno["fecha_pendiente_ajuste_nuevo_formato"] = $reg["fecha_pendiente_ajuste_nuevo_formato"];

        //
        if (defined('FECHA_INICIO_NUEVO_CERTIFICADO_CERMAT') && FECHA_INICIO_NUEVO_CERTIFICADO_CERMAT) {
            $finimat = FECHA_INICIO_NUEVO_CERTIFICADO_CERMAT;
        } else {
            $finimat = '20200101';
        }
        if (defined('FECHA_INICIO_NUEVO_CERTIFICADO_CEREXI') && FECHA_INICIO_NUEVO_CERTIFICADO_CEREXI) {
            $finiexi = FECHA_INICIO_NUEVO_CERTIFICADO_CERMAT;
        } else {
            $finiexi = '20200101';
        }
        if (defined('FECHA_INICIO_NUEVO_CERTIFICADO_CERESADL') && FECHA_INICIO_NUEVO_CERTIFICADO_CERESADL) {
            $finiesadl = FECHA_INICIO_NUEVO_CERTIFICADO_CERESADL;
        } else {
            $finiesadl = '20200101';
        }
        
        if ($reg["organizacion"] == '01' || $reg["organizacion"] == '02' || $reg["categoria"] == '2' || $reg["categoria"] == '3') {
            if ($reg["fecmatricula"] >= $finimat) {
                $reg["pendiente_ajuste_nuevo_formato"] = 'R';
                $reg["fecha_pendiente_ajuste_nuevo_formato"] = $reg["fecmatricula"];
            }
        }

        if ($reg["organizacion"] > '02' && $reg["organizacion"] != '12' && $reg["organizacion"] != '14' && $reg["categoria"] == '1') {
            if ($reg["fecmatricula"] >= $finiexi) {
                $reg["pendiente_ajuste_nuevo_formato"] = 'R';
                $reg["fecha_pendiente_ajuste_nuevo_formato"] = $reg["fecmatricula"];
            }
        }

        if (($reg["organizacion"] == '12' || $reg["organizacion"] == '14') && $reg["categoria"] == '1') {
            if ($reg["fecmatricula"] >= $finiesadl) {
                $reg["pendiente_ajuste_nuevo_formato"] = 'R';
                $reg["fecha_pendiente_ajuste_nuevo_formato"] = $reg["fecmatricula"];
            }
        }

        //
        $retorno["embargos"] = ''; // Se arma al encontrar la lista de embargos
        $retorno["embargostramite"] = ''; // Se arma al encontrar la lista de embargos
        $retorno["recursostramite"] = ''; // Sea arma al encontrar la lista de códigos de barras
        $retorno["tamanoempresa"] = $reg["tamanoempresa"];
        $retorno["emprendedor28"] = $reg["emprendedor28"];
        $retorno["pemprendedor28"] = doubleval($reg["pemprendedor28"]);
        $retorno["organizacion"] = $reg["organizacion"];
        $retorno["organizaciontexto"] = retornarRegistroMysqliApi($mysqli, 'bas_organizacionjuridica', "id='" . $reg["organizacion"] . "'", "descripcion");
        $retorno["categoria"] = $reg["categoria"];
        $retorno["categoriatexto"] = retornarRegistroMysqliApi($mysqli, 'bas_categorias', "id='" . $reg["categoria"] . "'", "descripcion");
        $retorno["naturaleza"] = $reg["naturaleza"];
        if ($reg["naturaleza"] == '') {
            $retorno["naturaleza"] = '0';
        }
        $retorno["imppredil"] = '';
        $retorno["impexp"] = $reg["ctrimpexp"];
        $retorno["tipopropiedad"] = $reg["ctrtipopropiedad"];
        $retorno["tipolocal"] = $reg["ctrtipolocal"];
        $retorno["tipogruemp"] = $reg["tipogruemp"]; // Nuevo campo circular 002  
        $retorno["nombregruemp"] = $reg["nombregruemp"]; // Nuevo campo circular 002 
        //
        $retorno["vigilanciasuperfinanciera"] = $reg["vigilanciasuperfinanciera"];
        $retorno["vigcontrol"] = $reg["vigcontrol"];
        $retorno["fecperj"] = $reg["fecperj"];
        $retorno["idorigenperj"] = $reg["otorgaperj"];
        $retorno["origendocconst"] = $reg["origendocconst"];
        $retorno["numperj"] = $reg["numperj"];
        $retorno["patrimonio"] = $reg["patrimonio"];
        $retorno["vigifecini"] = $reg["vigifecini"];
        $retorno["vigifecfin"] = $reg["vigifecfin"];
        $retorno["clasegenesadl"] = $reg["ctrclasegenesadl"];
        $retorno["claseespesadl"] = $reg["ctrclaseespeesadl"];
        $retorno["claseeconsoli"] = $reg["ctrclaseeconsoli"];
        $retorno["econmixta"] = $reg["ctreconmixta"];
        $retorno["condiespe2219"] = '';

        $retorno["ctrderpub"] = $reg["ctrderpub"]; // Nuevo campo circular 002
        $retorno["ctrcodcoop"] = $reg["ctrcodcoop"]; // Nuevo campo circular 002
        $retorno["ctrcodotras"] = $reg["ctrcodotras"]; // Nuevo campo circular 002

        $retorno["ctresacntasociados"] = $reg["ctresacntasociados"]; // Nuevo campo circular 002  
        $retorno["ctresacntmujeres"] = $reg["ctresacntmujeres"]; // Nuevo campo circular 002  
        $retorno["ctresacnthombres"] = $reg["ctresacnthombres"]; // Nuevo campo circular 002  
        $retorno["ctresapertgremio"] = $reg["ctresapertgremio"]; // Nuevo campo circular 002  
        $retorno["ctresagremio"] = $reg["ctresagremio"]; // Nuevo campo circular 002  
        $retorno["ctresaacredita"] = $reg["ctresaacredita"]; // Nuevo campo circular 002  
        $retorno["ctresaivc"] = $reg["ctresaivc"]; // Nuevo campo circular 002
        if (substr($retorno["matricula"], 0, 1) == 'S') {
            if (trim($retorno["ctresaivc"]) == '') {
                if (trim($reg["vigcontrol"]) != '' && is_numeric($reg["vigcontrol"]) && ltrim($reg["vigcontrol"], "0") != '') {
                    $retorno["ctresaivc"] = retornarRegistroMysqliApi($mysqli, 'mreg_tablassirep', "idtabla='43' and idcodigo = '" . $reg["vigcontrol"] . "'", "descripcion");
                }
                if (trim($reg["vigcontrol"]) != '' && !is_numeric($reg["vigcontrol"])) {
                    $retorno["ctresaivc"] = $reg["vigcontrol"];
                }
            } else {
                $retorno["vigcontrol"] = $retorno["ctresaivc"];
            }
        }
        $retorno["ctresainfoivc"] = $reg["ctresainfoivc"]; // Nuevo campo circular 002
        $retorno["ctresaautregistro"] = $reg["ctresaautregistro"]; // Nuevo campo circular 002  
        $retorno["ctresaentautoriza"] = $reg["ctresaentautoriza"];  // Nuevo campo circular 002  
        $retorno["ctresacodnat"] = $reg["ctresacodnat"]; // Nuevo campo circular 002     1.- Asoc, 2.- Corporacion, 3.- Fundación
        $retorno["ctresadiscap"] = $reg["ctresadiscap"]; // Nuevo campo circular 002     
        $retorno["ctresaetnia"] = $reg["ctresaetnia"]; // Nuevo campo circular 002     
        $retorno["ctresacualetnia"] = $reg["ctresacualetnia"];  // Nuevo campo circular 002     
        $retorno["ctresadespvictreins"] = $reg["ctresadespvictreins"];  // Nuevo campo circular 002     
        $retorno["ctresacualdespvictreins"] = $reg["ctresacualdespvictreins"]; // Nuevo campo circular 002     
        $retorno["ctresaindgest"] = $reg["ctresaindgest"];  // Nuevo campo circular 002            
        $retorno["ctresalgbti"] = $reg["ctresalgbti"]; // Nuevo campo circular 002            
        //
        $retorno["fecexafiliacion"] = '';
        if (isset($reg["fecexafiliacion"])) {
            $retorno["fecexafiliacion"] = $reg["fecexafiliacion"];
        }
        $retorno["motivodesafiliacion"] = $reg["motivodesafiliacion"];
        $retorno["txtmotivodesafiliacion"] = $reg["txtmotivodesafiliacion"];
        $retorno["afiliado"] = $reg["ctrafiliacion"];
        $retorno["fechaafiliacion"] = $reg["fecaflia"];
        $retorno["ultanorenafi"] = $reg["anorenaflia"];
        $retorno["fechaultpagoafi"] = $reg["fecrenaflia"];
        $retorno["valorultpagoafi"] = $reg["valpagaflia"];
        $retorno["saldoafiliado"] = $reg["saldoaflia"];
        $retorno["telaflia"] = $reg["telaflia"];
        $retorno["diraflia"] = $reg["diraflia"];
        $retorno["munaflia"] = $reg["munaflia"];
        $retorno["profaflia"] = $reg["profaflia"];
        $retorno["contaflia"] = $reg["contaflia"];
        $retorno["dircontaflia"] = $reg["dircontaflia"];
        $retorno["muncontaflia"] = $reg["muncontaflia"];
        $retorno["numactaaflia"] = $reg["numactaaflia"];
        $retorno["fecactaaflia"] = $reg["fecactaaflia"];
        $retorno["numactaafliacan"] = $reg["numactacanaflia"];
        $retorno["fecactaafliacan"] = $reg["fecactacanaflia"];
        $afil = encontrarHistoricoPagosAfiliacionMysqliApi($dbx, $retorno["matricula"]);
        if ($afil && !empty($afil)) {
            $retorno["fechaultpagoafi"] = $afil["fecrenaflia"];
            $retorno["ultanorenafi"] = $afil["anorenaflia"];
            $retorno["valorultpagoafi"] = $afil["valorpagadoultimaafiliacion"];
            $retorno["fecrenaflia"] = $afil["fecrenaflia"];
            $retorno["anorenaflia"] = $afil["anorenaflia"];
        }

        $afil1 = buscarSaldoAfiliadoMysqliApi($dbx, $retorno["matricula"]);
        if ($afil1 && !empty($afil1)) {
            $retorno["valorultpagoafi"] = $afil1["pago"];
            $retorno["saldoafiliado"] = $afil1["cupo"];
        }

        // informacion de ubicacion comercial en el registro mercantil
        $retorno["lggr"] = $reg["lggr"];
        $retorno["nombrecomercial"] = $reg["nombrecomercial"];
        $retorno["dircom"] = $reg["dircom"];
        $retorno["dircom_tipovia"] = '';
        $retorno["dircom_numvia"] = '';
        $retorno["dircom_apevia"] = '';
        $retorno["dircom_orivia"] = '';
        $retorno["dircom_numcruce"] = '';
        $retorno["dircom_apecruce"] = '';
        $retorno["dircom_oricruce"] = '';
        $retorno["dircom_numplaca"] = '';
        $retorno["dircom_complemento"] = '';
        $retorno["barriocom"] = $reg["barriocom"];
        $retorno["barriocomnombre"] = '';
        if (trim((string) $reg["barriocom"]) != '') {
            $retorno["barriocomnombre"] = retornarRegistroMysqliApi($mysqli, "mreg_barriosmuni", "idmunicipio='" . $reg["muncom"] . "' and idbarrio='" . $reg["barriocom"] . "'", "nombre");
        }
        $retorno["muncom"] = $reg["muncom"];
        $retorno["paicom"] = $reg["paicom"]; // Nuevo 002
        $retorno["muncomnombre"] = retornarRegistroMysqliApi($mysqli, "bas_municipios", "codigomunicipio='" . $reg["muncom"] . "'", "ciudad");
        $retorno["telcom1"] = $reg["telcom1"];
        $retorno["telcom2"] = $reg["telcom2"];
        $retorno["celcom"] = $reg["telcom3"];
        $retorno["telcomant1"] = '';
        $retorno["telcomant2"] = '';
        $retorno["telcomant3"] = '';
        $retorno["faxcom"] = $reg["faxcom"];
        $retorno["aacom"] = $reg["aacom"];
        $retorno["zonapostalcom"] = '';
        $retorno["emailcom"] = $reg["emailcom"];
        $retorno["emailcom2"] = $reg["emailcom2"];
        $retorno["emailcom3"] = $reg["emailcom3"];
        $retorno["emailcomant"] = '';
        $retorno["nombresegundocontacto"] = $reg["nomsegcon"];
        $retorno["cargosegundocontacto"] = $reg["carsegcon"];
        $retorno["urlcom"] = $reg["urlcom"];
        $retorno["numpredial"] = $reg["numpredial"];
        $retorno["codigopostalcom"] = $reg["codigopostalcom"]; // Nuevo campo circular 002
        $retorno["codigozonacom"] = $reg["codigozonacom"]; // Nuevo campo circular 002 - Urbao o rural
        // informacion de ubicacion de notificacion
        $retorno["dirnot"] = $reg["dirnot"];
        $retorno["dirnot_tipovia"] = '';
        $retorno["dirnot_numvia"] = '';
        $retorno["dirnot_apevia"] = '';
        $retorno["dirnot_orivia"] = '';
        $retorno["dirnot_numcruce"] = '';
        $retorno["dirnot_apecruce"] = '';
        $retorno["dirnot_oricruce"] = '';
        $retorno["dirnot_numplaca"] = '';
        $retorno["dirnot_complemento"] = '';
        $retorno["barrionot"] = $reg["barrionot"];
        $retorno["barrionotnombre"] = '';
        if (trim((string) $reg["barrionot"]) != '') {
            $retorno["barrionotnombre"] = retornarRegistroMysqliApi($mysqli, "mreg_barriosmuni", "idmunicipio='" . $reg["munnot"] . "' and idbarrio='" . $reg["barrionot"] . "'", "nombre");
        }
        $retorno["munnot"] = $reg["munnot"];
        $retorno["painot"] = $reg["painot"]; // Nuevo 002
        $retorno["munnotnombre"] = retornarRegistroMysqliApi($mysqli, "bas_municipios", "codigomunicipio='" . $reg["munnot"] . "'", "ciudad");
        $retorno["telnot"] = $reg["telnot"];
        $retorno["telnot2"] = $reg["telnot2"];
        $retorno["telnotant1"] = '';
        $retorno["telnotant2"] = '';
        $retorno["telnotant3"] = '';
        $retorno["celnot"] = $reg["telnot3"];
        $retorno["faxnot"] = $reg["faxnot"];
        $retorno["aanot"] = $reg["aanot"];
        $retorno["zonapostalnot"] = '';
        $retorno["emailnot"] = $reg["emailnot"];
        $retorno["emailnotant"] = '';
        $retorno["urlnot"] = $reg["urlnot"];
        $retorno["codigopostalnot"] = $reg["codigopostalnot"]; // Nuevo campo circular 002
        $retorno["codigozonanot"] = $reg["codigozonanot"]; // Nuevo campo circular 002 - Urbano o rural
        $retorno["tiposedeadm"] = $reg["tiposedeadm"]; // Nuevo campo circular 002 - 1,2,3,4
        //2017-06-201: JINT: No utilizados
        $retorno["latitudgrados"] = "";
        $retorno["latitudminutos"] = "";
        $retorno["latitudsegundos"] = "";
        $retorno["latitudorientacion"] = "";
        $retorno["longitudgrados"] = "";
        $retorno["longitudminutos"] = "";
        $retorno["longitudsegundos"] = "";
        $retorno["longitudorientacion"] = "";
        $retorno["habilitacionesespeciales"] = array();

        // 2017-06-21: JINT - Se alimenta desde la tabla mreg_geolocalizacion
        $retorno["latitud"] = "";
        $retorno["longitud"] = "";
        $ax1 = retornarRegistrosMysqliApi($mysqli, 'mreg_geolocalizacion', "matricula='" . $retorno["matricula"] . "'", 'fecha asc, hora asc');
        if ($ax1 && !empty($ax1)) {
            foreach ($ax1 as $x1) {
                if ($x1["latitud"] != 'undefined' && $x1["longitud"] != 'undefined') {
                    $retorno["latitud"] = $x1["latitud"];
                    $retorno["longitud"] = $x1["longitud"];
                }
            }
        }
        unset($x1);
        unset($ax1);
        if ($genlog == 'si') {
            \logApi::general2($nameLog, $retorno["matricula"], 'Leyo mreg_geolocalizacion');
        }

        // Localiza emails y celulars anteriores
        // if ($tipodata == 'E') {
        if (trim($retorno["matricula"]) != '') {
            $retorno["telcomant1"] = \funcionesGenerales::localizarCampoAnterior($mysqli, $retorno["matricula"], 'telcom1');
            $retorno["telcomant2"] = \funcionesGenerales::localizarCampoAnterior($mysqli, $retorno["matricula"], 'telcom2');
            $retorno["telcomant3"] = \funcionesGenerales::localizarCampoAnterior($mysqli, $retorno["matricula"], 'celcom');
            $retorno["telnotant1"] = \funcionesGenerales::localizarCampoAnterior($mysqli, $retorno["matricula"], 'telnot');
            $retorno["telnotant2"] = \funcionesGenerales::localizarCampoAnterior($mysqli, $retorno["matricula"], 'telnot2');
            $retorno["telnotant3"] = \funcionesGenerales::localizarCampoAnterior($mysqli, $retorno["matricula"], 'celnot');
            $retorno["emailcomant"] = \funcionesGenerales::localizarCampoAnterior($mysqli, $retorno["matricula"], 'emailcom');
            $retorno["emailnotant"] = \funcionesGenerales::localizarCampoAnterior($mysqli, $retorno["matricula"], 'emailnot');
        }
        if ($genlog == 'si') {
            \logApi::general2($nameLog, $retorno["matricula"], 'Leyo localizarCampoAnterior');
        }

        // }
        // Datos de correspondencia
        $retorno["dircor"] = $reg["dircor"];
        $retorno["telcor"] = $reg["telcor"];
        $retorno["telcor2"] = $reg["telcor2"];
        $retorno["muncor"] = $reg["muncor"];

        // informacion de actividad economica
        $retorno["ciius3"] = array();
        if (isset($reg["ciiu31"])) {
            $retorno["ciius3"][1] = $reg["ciiu31"];
            $retorno["ciius3"][2] = $reg["ciiu32"];
            $retorno["ciius3"][3] = $reg["ciiu33"];
            $retorno["ciius3"][4] = $reg["ciiu34"];
        }

        // informacion ed actividad economica
        $retorno["ciius"] = array();
        $retorno["ciius"][1] = $reg["ciiu1"];
        $retorno["ciius"][2] = $reg["ciiu2"];
        $retorno["ciius"][3] = $reg["ciiu3"];
        $retorno["ciius"][4] = $reg["ciiu4"];
        $retorno["ciius"][5] = $reg["ciiu5"];

        // 2017-10-13: JINT: Para resolver problemas de letras incorrectas en el CIIU
        if (trim($retorno["ciius"][1]) != '') {
            $ciiux = retornarRegistroMysqliApi($mysqli, 'bas_ciius', "idciiunum='" . substr($retorno["ciius"][1], 1) . "'");
            if ($ciiux && !empty($ciiux)) {
                $retorno["ciius"][1] = $ciiux["idciiu"];
            }
        }
        if (trim($retorno["ciius"][2]) != '') {
            $ciiux = retornarRegistroMysqliApi($mysqli, 'bas_ciius', "idciiunum='" . substr($retorno["ciius"][2], 1) . "'");
            if ($ciiux && !empty($ciiux)) {
                $retorno["ciius"][2] = $ciiux["idciiu"];
            }
        }
        if (trim($retorno["ciius"][3]) != '') {
            $ciiux = retornarRegistroMysqliApi($mysqli, 'bas_ciius', "idciiunum='" . substr($retorno["ciius"][3], 1) . "'");
            if ($ciiux && !empty($ciiux)) {
                $retorno["ciius"][3] = $ciiux["idciiu"];
            }
        }
        if (trim($retorno["ciius"][4]) != '') {
            $ciiux = retornarRegistroMysqliApi($mysqli, 'bas_ciius', "idciiunum='" . substr($retorno["ciius"][4], 1) . "'");
            if ($ciiux && !empty($ciiux)) {
                $retorno["ciius"][4] = $ciiux["idciiu"];
            }
        }


        //
        $retorno["versionciiu"] = $reg["versionciiu"];
        $retorno["desactiv"] = stripslashes((string) $reg["actividad"]); // Nuevo campo circular 002
        $retorno["feciniact1"] = $reg["feciniact1"]; // Nuevo campo circular 002
        $retorno["feciniact2"] = $reg["feciniact2"]; // Nuevo campo circular 002
        $retorno["codaduaneros"] = $reg["codaduaneros"]; // Nuevo campo circular 002
        if (!isset($reg["ingesperados"]) || trim((string) $reg["ingesperados"]) == '') {
            $reg["ingesperados"] = 0;
        }
        $retorno["ingesperados"] = $reg["ingesperados"]; // Nuevo campo circular 002
        //
        $retorno["gruponiif"] = $reg["gruponiif"]; // Nuevo campo circular 002
        $retorno["niifconciliacion"] = $reg["niifconciliacion"]; // Nuevo campo circular 002
        $retorno["aportantesegsocial"] = $reg["aportantesegsocial"]; // Nuevo campo circular 002
        $retorno["tipoaportantesegsocial"] = $reg["tipoaportantesegsocial"]; // Nuevo campo circular 002
        // informacion de porcentajes de capital
        $retorno["cap_porcnaltot"] = $reg["pornaltot"];
        $retorno["cap_porcnalpri"] = $reg["pornalpri"];
        $retorno["cap_porcnalpub"] = $reg["pornalpub"];
        $retorno["cap_porcexttot"] = $reg["porexttot"];
        $retorno["cap_porcextpri"] = $reg["porextpri"];
        $retorno["cap_porcextpub"] = $reg["porextpub"];

        $retorno["cap_apolab"] = 0;
        $retorno["cap_apolabadi"] = 0;
        $retorno["cap_apoact"] = 0;
        $retorno["cap_apodin"] = 0;

        $retorno["fecdatoscap"] = '';
        $retorno["capsoc"] = 0;
        $retorno["capaut"] = 0;
        $retorno["capsus"] = 0;
        $retorno["cappag"] = 0;
        $retorno["cuosoc"] = 0;
        $retorno["cuoaut"] = 0;
        $retorno["cuosus"] = 0;
        $retorno["cuopag"] = 0;
        $retorno["capsuc"] = 0;

        // 2019-12-15: JINT: Cantidad de mujeres, participacion y tamaño empresarial
        $retorno["cantidadmujeres"] = $reg["cantidadmujeres"];
        $retorno["cantidadmujerescargosdirectivos"] = $reg["cantidadmujerescargosdirectivos"];
        $retorno["cantidadcargosdirectivos"] = 0;
        $retorno["participacionmujeres"] = $reg["participacionmujeres"];
        $retorno["participacionetnia"] = 0;
        $retorno["ciiutamanoempresarial"] = '';
        $retorno["ingresostamanoempresarial"] = '';
        $retorno["anodatostamanoempresarial"] = '';
        $retorno["fechadatostamanoempresarial"] = '';

        $retorno["tamanoempresarial957"] = '';
        $retorno["tamanoempresarial957uvts"] = 0;
        $retorno["tamanoempresarial957uvbs"] = 0;
        $retorno["tamanoempresarial957codigo"] = '';
        $retorno["tamanoempresarialingresos"] = 0;
        $retorno["tamanoempresarialactivos"] = 0;
        $retorno["tamanoempresarialciiu"] = '';
        $retorno["tamanoempresarialpersonal"] = 0;
        $retorno["tamanoempresarialfechadatos"] = '';
        $retorno["tamanoempresarialanodatos"] = '';
        $retorno["tamanoempresarialformacalculo"] = '';

        $retorno["tamanoempresarial957codigoanterior"] = '';

        // información de Establecimientos de comercio asociados
        $retorno["cntestab01"] = $reg["cntest01"];
        $retorno["cntestab02"] = $reg["cntest02"];
        $retorno["cntestab03"] = $reg["cntest03"];
        $retorno["cntestab04"] = $reg["cntest04"];
        $retorno["cntestab05"] = $reg["cntest05"];
        $retorno["cntestab06"] = $reg["cntest06"];
        $retorno["cntestab07"] = $reg["cntest07"];
        $retorno["cntestab08"] = $reg["cntest08"];
        $retorno["cntestab09"] = $reg["cntest09"];
        $retorno["cntestab10"] = $reg["cntest10"];
        $retorno["cntestab11"] = $reg["cntest11"];

        // información de referencias comerciales y bancarias
        $retorno["refcrenom1"] = $reg["refcrenom1"];
        $retorno["refcreofi1"] = $reg["refcreofi1"];
        $retorno["refcretel1"] = $reg["refcretel1"];
        $retorno["refcrenom2"] = $reg["refcrenom2"];
        $retorno["refcreofi2"] = $reg["refcreofi2"];
        $retorno["refcretel2"] = $reg["refcretel2"];
        $retorno["refcomnom1"] = $reg["refcomnom1"];
        $retorno["refcomdir1"] = $reg["refcomdir1"];
        $retorno["refcomtel1"] = $reg["refcomtel1"];
        $retorno["refcomnom2"] = $reg["refcomnom2"];
        $retorno["refcomdir2"] = $reg["refcomdir2"];
        $retorno["refcomtel2"] = $reg["refcomtel2"];

        $retorno["ultimosactivosreportados"] = 0;
        $retorno["ultimosactivosvinculados"] = 0;
        $retorno["anodatos"] = '';
        $retorno["fechadatos"] = '';
        $retorno["personal"] = 0;
        $retorno["personaltemp"] = 0;
        $retorno["actvin"] = 0;
        $retorno["valest"] = 0;
        $retorno["actcte"] = 0;
        $retorno["actnocte"] = 0;
        $retorno["actfij"] = 0;
        $retorno["fijnet"] = 0;
        $retorno["actotr"] = 0;
        $retorno["actval"] = 0;
        $retorno["acttot"] = 0;
        $retorno["actsinaju"] = 0;
        $retorno["invent"] = 0;
        $retorno["pascte"] = 0;
        $retorno["paslar"] = 0;
        $retorno["pastot"] = 0;
        $retorno["pattot"] = 0;
        $retorno["paspat"] = 0;
        $retorno["balsoc"] = 0;
        $retorno["ingope"] = 0;
        $retorno["ingnoope"] = 0;
        $retorno["gtoven"] = 0;
        $retorno["gtoadm"] = 0;
        $retorno["cosven"] = 0;
        $retorno["depamo"] = 0;
        $retorno["gasint"] = 0;
        $retorno["gasimp"] = 0;
        $retorno["utiope"] = 0;
        $retorno["utinet"] = 0;

        $retorno["apolab"] = $reg["apolab"];
        $retorno["apolabadi"] = $reg["apolabadi"];
        $retorno["apoact"] = $reg["apoact"];
        $retorno["apodin"] = $reg["apodin"];
        $retorno["apotra"] = $reg["apotra"];
        $retorno["apotot"] = $reg["apotot"];

        $retorno["anodatospatrimonio"] = '';
        $retorno["fechadatospatrimonio"] = '';
        $retorno["patrimonioesadl"] = 0;

        // información financiera
        $ax1 = retornarRegistrosMysqliApi($mysqli, 'mreg_est_financiera', "matricula='" . $retorno["matricula"] . "'", 'anodatos asc, fechadatos asc, id asc');
        if ($ax1 && !empty($ax1)) {
            foreach ($ax1 as $x1) {
                $retorno["anodatos"] = $x1["anodatos"];
                $retorno["fechadatos"] = $x1["fechadatos"];
                $retorno["personal"] = $x1["personal"];
                $retorno["personaltemp"] = $x1["pcttemp"];
                $retorno["patrimonio"] = $x1["patrimonio"];
                $retorno["actvin"] = $x1["actvin"];
                $retorno["valest"] = $x1["actvin"];
                $retorno["ultimosactivosvinculados"] = $x1["actvin"];
                $retorno["actcte"] = $x1["actcte"];
                $retorno["actnocte"] = $x1["actnocte"];
                $retorno["actfij"] = $x1["actfij"];
                $retorno["fijnet"] = $x1["fijnet"];
                $retorno["actotr"] = $x1["actotr"];
                $retorno["actval"] = $x1["actval"];
                $retorno["acttot"] = $x1["acttot"];
                $retorno["ultimosactivosreportados"] = $x1["acttot"];
                $retorno["actsinaju"] = 0;
                $retorno["invent"] = 0;
                $retorno["pascte"] = $x1["pascte"];
                $retorno["paslar"] = $x1["paslar"];
                $retorno["pastot"] = $x1["pastot"];
                $retorno["pattot"] = $x1["patnet"];
                $retorno["paspat"] = $x1["paspat"];
                $retorno["balsoc"] = $x1["balsoc"];
                $retorno["ingope"] = $x1["ingope"];
                $retorno["ingnoope"] = $x1["ingnoope"];
                $retorno["gtoven"] = $x1["gtoven"];
                $retorno["gtoadm"] = $x1["gasadm"];
                $retorno["cosven"] = $x1["cosven"];
                $retorno["depamo"] = 0;
                $retorno["gasint"] = $x1["gasint"];
                $retorno["gasimp"] = $x1["gasimp"];
                $retorno["utiope"] = $x1["utiope"];
                $retorno["utinet"] = $x1["utinet"];
            }
            unset($x1);
            unset($ax1);
        }
        if ($genlog == 'si') {
            \logApi::general2($nameLog, $retorno["matricula"], 'Leyo mreg_est_financiera');
        }

        // 2020-01-15: JINT: Calculo de fechas de renovacion y afiliacion
        // No estaba incluido en la nueva rutina
        $histopagos = encontrarHistoricoPagosMysqliApi($mysqli, $retorno["matricula"], $serviciosRenovacion, $serviciosAfiliacion, $serviciosMatricula);
        if ($histopagos["fecultren"] != '') {
            $retorno["fecharenovacion"] = $histopagos["fecultren"];
            $retorno["ultanoren"] = $histopagos["ultanoren"];
            if ($histopagos["actultren"] != 0) {
                if ($retorno["organizacion"] == '02' || $retorno["categoria"] == '2' || $retorno["categoria"] == '3') {
                    $retorno["ultimosactivosvinculados"] = $histopagos["actultren"];
                } else {
                    $retorno["ultimosactivosreportados"] = $histopagos["actultren"];
                }
            }
        }

        //
        $ax1 = retornarRegistrosMysqliApi($mysqli, 'mreg_est_patrimonios', "matricula='" . $retorno["matricula"] . "'", 'anodatos asc, fechadatos asc');
        if ($ax1 && !empty($ax1)) {
            foreach ($ax1 as $x1) {
                $retorno["anodatospatrimonio"] = $x1["anodatos"];
                $retorno["fechadatospatrimonio"] = $x1["fechadatos"];
                $retorno["patrimonioesadl"] = $x1["patrimonio"];
            }
            unset($x1);
            unset($ax1);
        }
        if ($genlog == 'si') {
            \logApi::general2($nameLog, $retorno["matricula"], 'Leyo mreg_est_patrimonios');
        }

        // Campos adicionados en mayo 20 de 2011     
        $retorno["ctrmen"] = $reg["ctrnotemail"];
        $retorno["ctrmennot"] = $reg["ctrnotsms"];
        $retorno["ctrubi"] = $reg["ctrubi"];
        $retorno["ctrfun"] = $reg["ctrfun"];
        $retorno["art4"] = $reg["ctrbenart4"];
        $retorno["art7"] = $reg["ctrbenart7"];
        $retorno["art50"] = $reg["ctrbenart50"];
        $retorno["ctrcancelacion1429"] = $reg["ctrcance1429"];
        $retorno["benley1780"] = $reg["ctrbenley1780"];
        $retorno["cumplerequisitos1780"] = $reg["cumplerequisitos1780"];  // Nuevo campo circular 002
        $retorno["renunciabeneficios1780"] = $reg["renunciabeneficios1780"]; // Nuevo campo circular 002
        $retorno["cumplerequisitos1780primren"] = $reg["cumplerequisitos1780primren"]; // Nuevo campo circular 002
        $retorno["ctrbic"] = $reg["ctrbic"];
        $retorno["ctrdepuracion1727"] = $reg["ctrdepuracion1727"];
        $retorno["ctrfechadepuracion1727"] = $reg["ctrfechadepuracion1727"];
        $retorno["ctrben658"] = $reg["ctrben658"];

        $retorno["fecmatant"] = $reg["fecmatant"];
        $retorno["fecrenant"] = $reg["fecrenant"];
        $retorno["matriculaanterior"] = $reg["matant"];
        $retorno["matant"] = $reg["matant"];
        $retorno["camant"] = $reg["camant"];
        $retorno["munant"] = $reg["munant"];
        $retorno["ultanorenant"] = $reg["ultanorenant"];
        $retorno["benart7ant"] = $reg["benart7ant"];
        $retorno["benley1780ant"] = $reg["benley1780ant"];

        //
        $retorno["ivcenvio"] = $reg["ctrivcenvio"];
        $retorno["ivcsuelos"] = $reg["ctrivcsuelos"];
        $retorno["ivcarea"] = $reg["ctrivcarea"];
        $retorno["ivcver"] = $reg["ctrivcver"];
        $retorno["ivccretip"] = $reg["ctrivccretip"];
        $retorno["ivcali"] = $reg["ctrivcali"];
        $retorno["ivcqui"] = $reg["ctrivcqui"];
        $retorno["ivcriesgo"] = $reg["ctrivcriesgo"];

        // Representacion legal y administracion
        $retorno["idtipoidentificacionreplegal"] = '';
        $retorno["identificacionreplegal"] = '';
        $retorno["nombrereplegal"] = '';
        $retorno["idtipoidentificacionadministrador"] = '';
        $retorno["identificacionadministrador"] = '';
        $retorno["nombreadministrador"] = '';

        // Casa principal
        $retorno["cpcodcam"] = $reg["cpcodcam"];
        $retorno["cpnummat"] = $reg["cpnummat"];
        $retorno["cprazsoc"] = $reg["cprazsoc"];
        $retorno["cpnumnit"] = $reg["cpnumnit"];
        $retorno["cpdircom"] = $reg["cpdircom"];
        $retorno["cpdirnot"] = $reg["cpdirnot"];
        $retorno["cpnumtel"] = $reg["cpnumtel"];
        $retorno["cpnumtel2"] = ''; // Nacen con circular 002
        $retorno["cpnumtel3"] = ''; // Nacen con circular 002
        $retorno["cpnumfax"] = $reg["cpnumfax"];
        $retorno["cpcodmun"] = $reg["cpcodmun"];
        $retorno["cpmunnot"] = $reg["cpmunnot"];
        $retorno["cptirepleg"] = $reg["cptirepleg"];
        $retorno["cpirepleg"] = $reg["cpirepleg"];
        $retorno["cpnrepleg"] = $reg["cpnrepleg"];
        $retorno["cptelrepleg"] = $reg["cptelrepleg"];
        $retorno["cpemailrepleg"] = $reg["cpemailrepleg"];

        // Datos de constitución sacado de libros
        $retorno["datconst_fecdoc"] = '';
        $retorno["datconst_tipdoc"] = '';
        $retorno["datconst_numdoc"] = '';
        $retorno["datconst_oridoc"] = '';
        $retorno["datconst_mundoc"] = '';

        // Bienes
        $retorno["bienes"] = array();

        //
        $retorno["clasevinculo"] = $_SESSION["clasevinculo"];

        // Vinculos 
        // Vinculos históricos
        // Representantes legales
        $retorno["existenvinculos"] = '';
        $retorno["replegal"] = array();
        $retorno["vinculos"] = array();
        $retorno["vinculosh"] = array();
        $vincs = retornarRegistrosMysqliApi($mysqli, 'mreg_est_vinculos', "matricula='" . ltrim(trim($retorno["matricula"]), "0") . "'", "estado,vinculo,idcargo,fecha,idclase,numid");
        $iVincs = 0;
        $iVincsh = 0;
        $iReps = 0;
        $primerRep = 0;
        if ($vincs) {
            if (!empty($vincs)) {
                foreach ($vincs as $vin) {

                    //
                    if ($vin["estado"] == 'V') {
                        $iVincs++;
                        if (!isset($vin["fechaconfiguracion"])) {
                            $vin["fechaconfiguracion"] = '';
                        }
                        if (!isset($vin["fechavencimiento"])) {
                            $vin["fechavencimiento"] = '';
                        }
                        $retorno["existenvinculos"] = 'si';
                        $retorno["vinculos"][$iVincs]["id"] = $vin["id"];
                        $retorno["vinculos"][$iVincs]["idtipoidentificacionotros"] = $vin["idclase"];
                        $retorno["vinculos"][$iVincs]["identificacionotros"] = $vin["numid"];
                        $retorno["vinculos"][$iVincs]["nombreotros"] = stripslashes((string) $vin["nombre"]);
                        $retorno["vinculos"][$iVincs]["apellido1otros"] = stripslashes((string) $vin["ape1"]);
                        $retorno["vinculos"][$iVincs]["apellido2otros"] = stripslashes((string) $vin["ape2"]);
                        $retorno["vinculos"][$iVincs]["nombre1otros"] = stripslashes((string) $vin["nom1"]);
                        $retorno["vinculos"][$iVincs]["nombre2otros"] = stripslashes((string) $vin["nom2"]);
                        $retorno["vinculos"][$iVincs]["direccionotros"] = stripslashes((string) $vin["direccion"]);
                        $retorno["vinculos"][$iVincs]["municipiootros"] = stripslashes((string) $vin["municipio"]);
                        $retorno["vinculos"][$iVincs]["paisotros"] = stripslashes((string) $vin["pais"]);
                        $retorno["vinculos"][$iVincs]["emailotros"] = stripslashes((string) $vin["email"]);
                        $retorno["vinculos"][$iVincs]["celularotros"] = ($vin["celular"]);
                        $retorno["vinculos"][$iVincs]["fechanacimientootros"] = ($vin["fechanacimiento"]);
                        $retorno["vinculos"][$iVincs]["idcargootros"] = ($vin["idcargo"]);
                        $retorno["vinculos"][$iVincs]["cargootros"] = stripslashes((string) $vin["descargo"]);
                        $retorno["vinculos"][$iVincs]["vinculootros"] = ($vin["vinculo"]);
                        $retorno["vinculos"][$iVincs]["numtarprofotros"] = stripslashes((string) $vin["tarjprof"]);
                        $retorno["vinculos"][$iVincs]["idclaseemp"] = ($vin["tirepresenta"]);
                        $retorno["vinculos"][$iVincs]["numidemp"] = ($vin["idrepresenta"]);
                        $retorno["vinculos"][$iVincs]["nombreemp"] = stripslashes((string) $vin["nmrepresenta"]);
                        $retorno["vinculos"][$iVincs]["idvindianpnat"] = ($vin["vinculodianpnat"]);
                        $retorno["vinculos"][$iVincs]["idvindianpjur"] = ($vin["vinculodianpjur"]);
                        $retorno["vinculos"][$iVincs]["cuotasconst"] = ($vin["cuotasconst"]);
                        $retorno["vinculos"][$iVincs]["cuotasref"] = ($vin["cuotasref"]);
                        $retorno["vinculos"][$iVincs]["valorconst"] = ($vin["valorconst"]);
                        $retorno["vinculos"][$iVincs]["valorref"] = ($vin["valorref"]);
                        $retorno["vinculos"][$iVincs]["va1"] = ($vin["valorasociativa1"]);
                        $retorno["vinculos"][$iVincs]["va2"] = ($vin["valorasociativa2"]);
                        $retorno["vinculos"][$iVincs]["va3"] = ($vin["valorasociativa3"]);
                        $retorno["vinculos"][$iVincs]["va4"] = ($vin["valorasociativa4"]);
                        $retorno["vinculos"][$iVincs]["va5"] = ($vin["valorasociativa5"]);
                        $retorno["vinculos"][$iVincs]["va6"] = ($vin["valorasociativa6"]);
                        $retorno["vinculos"][$iVincs]["va7"] = ($vin["valorasociativa7"]);
                        $retorno["vinculos"][$iVincs]["va8"] = ($vin["valorasociativa8"]);
                        $retorno["vinculos"][$iVincs]["librootros"] = ($vin["idlibro"]);
                        $retorno["vinculos"][$iVincs]["inscripcionotros"] = ($vin["numreg"]);
                        $retorno["vinculos"][$iVincs]["dupliotros"] = ($vin["dupli"]);
                        $retorno["vinculos"][$iVincs]["fechaotros"] = ($vin["fecha"]);
                        $retorno["vinculos"][$iVincs]["ciiu1"] = ($vin["ciiu1"]);
                        $retorno["vinculos"][$iVincs]["ciiu2"] = ($vin["ciiu2"]);
                        $retorno["vinculos"][$iVincs]["ciiu3"] = ($vin["ciiu3"]);
                        $retorno["vinculos"][$iVincs]["ciiu4"] = ($vin["ciiu4"]);
                        $retorno["vinculos"][$iVincs]["tipositcontrol"] = ($vin["tipositcontrol"]);
                        $retorno["vinculos"][$iVincs]["tipositcontroltexto"] = '';
                        if (isset($vin["tipositcontroltexto"])) {
                            $retorno["vinculos"][$iVincs]["tipositcontroltexto"] = ($vin["tipositcontroltexto"]);
                        }
                        $retorno["vinculos"][$iVincs]["desactiv"] = ($vin["desactiv"]);
                        $retorno["vinculos"][$iVincs]["fechaconfiguracion"] = ($vin["fechaconfiguracion"]);
                        $retorno["vinculos"][$iVincs]["fechavencimiento"] = ($vin["fechavencimiento"]);
                        $retorno["vinculos"][$iVincs]["codcertifica"] = ($vin["codcertifica"]);
                        if (isset($_SESSION["maestrovinculos"][$vin["vinculo"]])) {
                            $retorno["vinculos"][$iVincs]["tipovinculo"] = $_SESSION["maestrovinculos"][$vin["vinculo"]]["tipovinculo"];
                            $retorno["vinculos"][$iVincs]["tipovinculoceresadl"] = $_SESSION["maestrovinculos"][$vin["vinculo"]]["tipovinculoceresadl"];
                            $retorno["vinculos"][$iVincs]["tipovinculoesadl"] = $_SESSION["maestrovinculos"][$vin["vinculo"]]["tipovinculoceresadl"];
                            $retorno["vinculos"][$iVincs]["puedereactivar"] = strtoupper((string) $_SESSION["maestrovinculos"][$vin["vinculo"]]["puedereactivar"]);
                        } else {
                            $retorno["vinculos"][$iVincs]["tipovinculo"] = '';
                            $retorno["vinculos"][$iVincs]["tipovinculoceresadl"] = '';
                            $retorno["vinculos"][$iVincs]["tipovinculoesadl"] = '';
                            $retorno["vinculos"][$iVincs]["puedereactivar"] = '';
                        }
                        $retorno["vinculos"][$iVincs]["renrem"] = $vin["textorenunciaremocion"];
                        $retorno["vinculos"][$iVincs]["textoembargos"] = '';
                        if (isset($vin["textoembargo"]) && trim((string) $vin["textoembargo"]) != '') {
                            $retorno["vinculos"][$iVincs]["textoembargo"] = $vin["textoembargo"];
                        }
                        $retorno["vinculos"][$iVincs]["nacionalidad"] = '';
                        $retorno["vinculos"][$iVincs]["proindiviso"] = '';
                        if (isset($vin["nacionalidad"])) {
                            $retorno["vinculos"][$iVincs]["nacionalidad"] = stripslashes((string) $vin["nacionalidad"]);
                        }
                        if (isset($vin["proindiviso"])) {
                            $retorno["vinculos"][$iVincs]["proindiviso"] = stripslashes((string) $vin["proindiviso"]);
                        }
                    }

                    //
                    if ($vin["estado"] == 'H') {
                        $iVincsh++;
                        if (!isset($vin["fechaconfiguracion"])) {
                            $vin["fechaconfiguracion"] = '';
                        }
                        if (!isset($vin["fechavencimiento"])) {
                            $vin["fechavencimiento"] = '';
                        }
                        $retorno["vinculosh"][$iVincsh]["idtipoidentificacionotros"] = $vin["idclase"];
                        $retorno["vinculosh"][$iVincsh]["identificacionotros"] = $vin["numid"];
                        $retorno["vinculosh"][$iVincsh]["nombreotros"] = stripslashes((string) $vin["nombre"]);
                        $retorno["vinculosh"][$iVincsh]["apellido1otros"] = stripslashes((string) $vin["ape1"]);
                        $retorno["vinculosh"][$iVincsh]["apellido2otros"] = stripslashes((string) $vin["ape2"]);
                        $retorno["vinculosh"][$iVincsh]["nombre1otros"] = stripslashes((string) $vin["nom1"]);
                        $retorno["vinculosh"][$iVincsh]["nombre2otros"] = stripslashes((string) $vin["nom2"]);
                        $retorno["vinculosh"][$iVincsh]["direccionotros"] = stripslashes((string) $vin["direccion"]);
                        $retorno["vinculosh"][$iVincsh]["municipiootros"] = stripslashes((string) $vin["municipio"]);
                        $retorno["vinculosh"][$iVincsh]["paisotros"] = stripslashes((string) $vin["pais"]);
                        $retorno["vinculosh"][$iVincsh]["emailotros"] = stripslashes((string) $vin["email"]);
                        $retorno["vinculosh"][$iVincsh]["celularotros"] = ($vin["celular"]);
                        $retorno["vinculosh"][$iVincsh]["fechanacimientootros"] = ($vin["fechanacimiento"]);
                        $retorno["vinculosh"][$iVincsh]["idcargootros"] = ($vin["idcargo"]);
                        $retorno["vinculosh"][$iVincsh]["cargootros"] = stripslashes((string) $vin["descargo"]);
                        $retorno["vinculosh"][$iVincsh]["vinculootros"] = ($vin["vinculo"]);
                        $retorno["vinculosh"][$iVincsh]["numtarprofotros"] = stripslashes((string) $vin["tarjprof"]);
                        $retorno["vinculosh"][$iVincsh]["idclaseemp"] = ($vin["tirepresenta"]);
                        $retorno["vinculosh"][$iVincsh]["numidemp"] = ($vin["idrepresenta"]);
                        $retorno["vinculosh"][$iVincsh]["nombreemp"] = stripslashes((string) $vin["nmrepresenta"]);
                        $retorno["vinculosh"][$iVincsh]["idvindianpnat"] = ($vin["vinculodianpnat"]);
                        $retorno["vinculosh"][$iVincsh]["idvindianpjur"] = ($vin["vinculodianpjur"]);
                        $retorno["vinculosh"][$iVincsh]["cuotasconst"] = ($vin["cuotasconst"]);
                        $retorno["vinculosh"][$iVincsh]["cuotasref"] = ($vin["cuotasref"]);
                        $retorno["vinculosh"][$iVincsh]["valorconst"] = ($vin["valorconst"]);
                        $retorno["vinculosh"][$iVincsh]["valorref"] = ($vin["valorref"]);
                        $retorno["vinculosh"][$iVincsh]["va1"] = ($vin["valorasociativa1"]);
                        $retorno["vinculosh"][$iVincsh]["va2"] = ($vin["valorasociativa2"]);
                        $retorno["vinculosh"][$iVincsh]["va3"] = ($vin["valorasociativa3"]);
                        $retorno["vinculosh"][$iVincsh]["va4"] = ($vin["valorasociativa4"]);
                        $retorno["vinculosh"][$iVincsh]["va5"] = ($vin["valorasociativa5"]);
                        $retorno["vinculosh"][$iVincsh]["va6"] = ($vin["valorasociativa6"]);
                        $retorno["vinculosh"][$iVincsh]["va7"] = ($vin["valorasociativa7"]);
                        $retorno["vinculosh"][$iVincsh]["va8"] = ($vin["valorasociativa8"]);
                        $retorno["vinculosh"][$iVincsh]["librootros"] = ($vin["idlibro"]);
                        $retorno["vinculosh"][$iVincsh]["inscripcionotros"] = ($vin["numreg"]);
                        $retorno["vinculosh"][$iVincsh]["dupliotros"] = ($vin["dupli"]);
                        $retorno["vinculosh"][$iVincsh]["fechaotros"] = ($vin["fecha"]);
                        $retorno["vinculosh"][$iVincsh]["ciiu1"] = ($vin["ciiu1"]);
                        $retorno["vinculosh"][$iVincsh]["ciiu2"] = ($vin["ciiu2"]);
                        $retorno["vinculosh"][$iVincsh]["ciiu3"] = ($vin["ciiu3"]);
                        $retorno["vinculosh"][$iVincsh]["ciiu4"] = ($vin["ciiu4"]);
                        $retorno["vinculosh"][$iVincsh]["tipositcontrol"] = ($vin["tipositcontrol"]);
                        $retorno["vinculosh"][$iVincsh]["tipositcontroltexto"] = '';
                        if (isset($vin["tipositcontroltexto"])) {
                            $retorno["vinculosh"][$iVincs]["tipositcontroltexto"] = ($vin["tipositcontroltexto"]);
                        }
                        $retorno["vinculosh"][$iVincsh]["desactiv"] = ($vin["desactiv"]);
                        $retorno["vinculosh"][$iVincsh]["fechaconfiguracion"] = ($vin["fechaconfiguracion"]);
                        $retorno["vinculosh"][$iVincsh]["fechavencimiento"] = ($vin["fechavencimiento"]);
                        $retorno["vinculosh"][$iVincsh]["codcertifica"] = $vin["codcertifica"];
                        if (isset($_SESSION["maestrovinculos"][$vin["vinculo"]])) {
                            $retorno["vinculosh"][$iVincsh]["tipovinculo"] = $_SESSION["maestrovinculos"][$vin["vinculo"]]["tipovinculo"];
                            $retorno["vinculosh"][$iVincsh]["tipovinculoceresadl"] = $_SESSION["maestrovinculos"][$vin["vinculo"]]["tipovinculoceresadl"];
                        } else {
                            $retorno["vinculosh"][$iVincsh]["tipovinculo"] = '';
                            $retorno["vinculosh"][$iVincsh]["tipovinculoceresadl"] = '';
                        }
                        $retorno["vinculosh"][$iVincsh]["renrem"] = $vin["textorenunciaremocion"];
                        if (isset($vin["textoembargo"]) && trim((string) $vin["textoembargo"]) != '') {
                            $retorno["vinculosh"][$iVincsh]["textoembargo"] = $vin["textoembargo"];
                        }
                        $retorno["vinculosh"][$iVincsh]["nacionalidad"] = '';
                        $retorno["vinculosh"][$iVincsh]["proindiviso"] = '';
                        if (isset($vin["nacionalidad"])) {
                            $retorno["vinculosh"][$iVincsh]["nacionalidad"] = stripslashes((string) $vin["nacionalidad"]);
                        }
                        if (isset($vin["proindiviso"])) {
                            $retorno["vinculosh"][$iVincsh]["proindiviso"] = stripslashes((string) $vin["proindiviso"]);
                        }
                    }

                    if (isset($_SESSION["maestrovinculos"][$vin["vinculo"]])) {
                        if (
                                $_SESSION["maestrovinculos"][$vin["vinculo"]]["tipovinculo"] == 'RLP' ||
                                $_SESSION["maestrovinculos"][$vin["vinculo"]]["tipovinculo"] == 'RLS' ||
                                $_SESSION["maestrovinculos"][$vin["vinculo"]]["tipovinculo"] == 'RLS1' ||
                                $_SESSION["maestrovinculos"][$vin["vinculo"]]["tipovinculo"] == 'RLS2' ||
                                $_SESSION["maestrovinculos"][$vin["vinculo"]]["tipovinculo"] == 'RLS3' ||
                                $_SESSION["maestrovinculos"][$vin["vinculo"]]["tipovinculo"] == 'RLS4' ||
                                $_SESSION["maestrovinculos"][$vin["vinculo"]]["tipovinculo"] == 'GER'
                        ) {
                            if ($vin["estado"] == 'V') {
                                $iReps++;
                                $retorno["replegal"][$iReps]["idtipoidentificacionreplegal"] = $vin["idclase"];
                                $retorno["replegal"][$iReps]["identificacionreplegal"] = $vin["numid"];
                                $retorno["replegal"][$iReps]["nombrereplegal"] = stripslashes((string) $vin["nombre"]);
                                $retorno["replegal"][$iReps]["nombre1replegal"] = stripslashes((string) $vin["nom1"]);
                                $retorno["replegal"][$iReps]["nombre2replegal"] = stripslashes((string) $vin["nom2"]);
                                $retorno["replegal"][$iReps]["apellido1replegal"] = stripslashes((string) $vin["ape1"]);
                                $retorno["replegal"][$iReps]["apellido2replegal"] = stripslashes((string) $vin["ape2"]);
                                $retorno["replegal"][$iReps]["emailreplegal"] = stripslashes((string) $vin["email"]);
                                $retorno["replegal"][$iReps]["celularreplegal"] = ($vin["celular"]);
                                $retorno["replegal"][$iReps]["cargoreplegal"] = stripslashes((string) $vin["descargo"]);
                                $retorno["replegal"][$iReps]["vinculoreplegal"] = ($vin["vinculo"]);
                                $retorno["replegal"][$iReps]["libroreplegal"] = ($vin["idlibro"]);
                                $retorno["replegal"][$iReps]["inscripcionreplegal"] = ($vin["numreg"]);
                                $retorno["replegal"][$iReps]["fechareplegal"] = ($vin["fecha"]);
                                $retorno["replegal"][$iReps]["tipovinculo"] = $_SESSION["maestrovinculos"][$vin["vinculo"]]["tipovinculo"];
                            }
                        }
                    }
                    // Primer representante legal y administrador
                    if (isset($_SESSION["maestrovinculos"][$vin["vinculo"]])) {
                        if ($vin["estado"] == 'V') {
                            if (
                                    $_SESSION["maestrovinculos"][$vin["vinculo"]]["tipovinculo"] == 'RLP' ||
                                    $_SESSION["maestrovinculos"][$vin["vinculo"]]["tipovinculo"] == 'GER'
                            ) {
                                $primerRep++;
                                if ($primerRep == 1) {
                                    $retorno["idtipoidentificacionreplegal"] = $vin["idclase"];
                                    $retorno["identificacionreplegal"] = $vin["numid"];
                                    $retorno["nombrereplegal"] = stripslashes((string) $vin["nombre"]);
                                }
                            }
                            if ($_SESSION["maestrovinculos"][$vin["vinculo"]]["tipovinculo"] == 'ADMP') {
                                $retorno["idtipoidentificacionadministrador"] = $vin["idclase"];
                                $retorno["identificacionadministrador"] = $vin["numid"];
                                $retorno["nombreadministrador"] = stripslashes((string) $vin["nombre"]);
                            }
                        }
                    }
                }
            }
        }
        if ($genlog == 'si') {
            \logApi::general2($nameLog, $retorno["matricula"], 'Leyo mreg_est_vinculos');
        }

        $retorno["vincuprop"] = array();
        $iVinProp = 0;

        // Propietarios
        $retorno["propietarios"] = array();
        if ($retorno["organizacion"] == '02') {
            $props = retornarRegistrosMysqliApi($mysqli, 'mreg_est_propietarios', "matricula='" . $retorno["matricula"] . "'", "id");
            $iProp = 0;
            if ($props) {
                if (!empty($props)) {
                    foreach ($props as $prop) {
                        if ($prop["estado"] == 'V') {
                            $iProp++;
                            $prop["fecmatprop"] = '';
                            $prop["fecrenprop"] = '';
                            $prop["ultanorenprop"] = '';
                            $prop["organizacionprop"] = '';
                            $prop["estadodatosprop"] = '';
                            $prop["estadomatriculaprop"] = '';
                            $prop["afiliacionprop"] = '';
                            $prop["ultanorenafliaprop"] = '';
                            $prop["saldoafliaprop"] = 0;
                            $prop["nombrereplegal"] = '';
                            $prop["nit"] = '';
                            $prop["identificacionreplegal"] = '';
                            $prop["tipoidentificacionreplegal"] = '';

                            if ($prop["codigocamara"] == $_SESSION["generales"]["codigoempresa"]) {
                                if (ltrim(trim($prop["matriculapropietario"]), "0") != '') {
                                    $xprop = retornarRegistroMysqliApi($mysqli, 'mreg_est_inscritos', "matricula='" . ltrim(trim($prop["matriculapropietario"]), "0") . "'");
                                    if ($xprop === false || empty($xprop)) {
                                        $prop["tipoidentificacion"] = '';
                                        $prop["identificacion"] = '';
                                        $prop["nit"] = '';
                                        $prop["razonsocial"] = '';
                                        $prop["nombre1"] = '';
                                        $prop["nombre2"] = '';
                                        $prop["apellido1"] = '';
                                        $prop["apellido2"] = '';
                                        $prop["dircom"] = '';
                                        $prop["muncom"] = '';
                                        $prop["dirnot"] = '';
                                        $prop["munnot"] = '';
                                        $prop["telcom1"] = '';
                                        $prop["telcom2"] = '';
                                        $prop["telcom3"] = '';
                                        $prop["fecmatprop"] = '';
                                        $prop["fecrenprop"] = '';
                                        $prop["ultanorenprop"] = '';
                                        $prop["organizacionprop"] = '';
                                        $prop["estadodatosprop"] = '';
                                        $prop["estadomatriculaprop"] = '';
                                        $prop["afiliacionprop"] = '';
                                        $prop["ultanorenafliaprop"] = '';
                                        $prop["saldoafliaprop"] = '';
                                        $prop["nombrereplegal"] = '';
                                        $prop["identificacionreplegal"] = '';
                                        $prop["tipoidentificacionreplegal"] = '';
                                        $prop["tipopropiedad"] = '';
                                    } else {
                                        $prop["tipoidentificacion"] = $xprop["idclase"];
                                        $prop["identificacion"] = $xprop["numid"];
                                        $prop["nit"] = $xprop["nit"];
                                        $prop["razonsocial"] = \funcionesRegistrales::retornarExpedienteMercantilRazonSocial($mysqli, $prop["matriculapropietario"]);
                                        $prop["nombre1"] = $xprop["nombre1"];
                                        $prop["nombre2"] = $xprop["nombre2"];
                                        $prop["apellido1"] = $xprop["apellido1"];
                                        $prop["apellido2"] = $xprop["apellido2"];
                                        $prop["dircom"] = $xprop["dircom"];
                                        $prop["muncom"] = $xprop["muncom"];
                                        $prop["dirnot"] = $xprop["dirnot"];
                                        $prop["munnot"] = $xprop["munnot"];
                                        $prop["telcom1"] = $xprop["telcom1"];
                                        $prop["telcom2"] = $xprop["telcom2"];
                                        $prop["telcom3"] = $xprop["telcom3"];
                                        $prop["fecmatprop"] = $xprop["fecmatricula"];
                                        $prop["fecrenprop"] = $xprop["fecrenovacion"];
                                        $prop["ultanorenprop"] = $xprop["ultanoren"];
                                        $histprop = encontrarHistoricoPagosMysqliApi($mysqli, $xprop["matricula"], $serviciosRenovacion, $serviciosAfiliacion, $serviciosMatricula);
                                        $prop["fecrenprop"] = $histprop["fecultren"];
                                        $prop["ultanorenprop"] = $histprop["ultanoren"];
                                        $prop["acttot"] = $histprop["actultren"];
                                        $prop["ultimosactivosreportados"] = $histprop["actultren"];
                                        $prop["organizacionprop"] = $xprop["organizacion"];
                                        $prop["estadodatosprop"] = $xprop["ctrestdatos"];
                                        $prop["estadomatriculaprop"] = $xprop["ctrestmatricula"];
                                        $prop["afiliacionprop"] = $xprop["ctrafiliacion"];
                                        $prop["ultanorenafliaprop"] = $xprop["anorenaflia"];
                                        $prop["saldoafliaprop"] = $xprop["saldoaflia"];
                                        $prop["nombrereplegal"] = '';
                                        $prop["identificacionreplegal"] = '';
                                        $prop["tipoidentificacionreplegal"] = '';
                                        $prop["razonsocial"] = \funcionesRegistrales::retornarExpedienteMercantilRazonSocial($mysqli, $prop["matriculapropietario"]);
                                        $iXvincs = 0;
                                        $xvincs = retornarRegistrosMysqliApi($mysqli, 'mreg_est_vinculos', "matricula='" . $xprop["matricula"] . "'", "id");
                                        if ($xvincs && !empty($xvincs)) {
                                            foreach ($xvincs as $xvin) {
                                                if ($xvin["estado"] == 'V') {
                                                    if (
                                                            $_SESSION["maestrovinculos"][$xvin["vinculo"]]["tipovinculo"] == 'RLP' ||
                                                            $_SESSION["maestrovinculos"][$xvin["vinculo"]]["tipovinculo"] == 'GER'
                                                    ) {
                                                        $iXvincs++;
                                                        if ($prop["nombrereplegal"] == '') {
                                                            $prop["nombrereplegal"] = stripslashes($xvin["nombre"]);
                                                            $prop["tipoidentificacionreplegal"] = $xvin["idclase"];
                                                            $prop["identificacionreplegal"] = $xvin["numid"];
                                                        }
                                                    }
                                                    $iVinProp++;
                                                    $retorno["vincuprop"][$iVinProp] = array();
                                                    $retorno["vincuprop"][$iVinProp]["idclase"] = $xvin["idclase"];
                                                    $retorno["vincuprop"][$iVinProp]["numid"] = $xvin["numid"];
                                                    $retorno["vincuprop"][$iVinProp]["nombre"] = $xvin["nombre"];
                                                    $retorno["vincuprop"][$iVinProp]["vinculo"] = $xvin["vinculo"];
                                                    $retorno["vincuprop"][$iVinProp]["cargo"] = $xvin["descargo"];
                                                    $retorno["vincuprop"][$iVinProp]["tipovinculo"] = $_SESSION["maestrovinculos"][$xvin["vinculo"]]["tipovinculo"];
                                                    $retorno["vincuprop"][$iVinProp]["tipovinculoesadl"] = $_SESSION["maestrovinculos"][$xvin["vinculo"]]["tipovinculoceresadl"];
                                                    $retorno["vincuprop"][$iVinProp]["puedereactivar"] = $_SESSION["maestrovinculos"][$xvin["vinculo"]]["puedereactivar"];
                                                }
                                            }
                                        }
                                    }
                                }
                            }


                            $retorno["propietarios"][$iProp]["id"] = $prop["id"];
                            $retorno["propietarios"][$iProp]["camarapropietario"] = $prop["codigocamara"];
                            $retorno["propietarios"][$iProp]["matriculapropietario"] = ltrim($prop["matriculapropietario"], '0');
                            $retorno["propietarios"][$iProp]["idtipoidentificacionpropietario"] = trim($prop["tipoidentificacion"]);
                            $retorno["propietarios"][$iProp]["identificacionpropietario"] = ltrim($prop["identificacion"], '0');
                            $retorno["propietarios"][$iProp]["nitpropietario"] = '';
                            if (ltrim(trim($prop["nit"]), '0') == '') {
                                if ($retorno["propietarios"][$iProp]["idtipoidentificacionpropietario"] == '2') {
                                    $retorno["propietarios"][$iProp]["nitpropietario"] = $retorno["propietarios"][$iProp]["identificacionpropietario"];
                                }
                            } else {
                                $retorno["propietarios"][$iProp]["nitpropietario"] = ltrim($prop["nit"], '0');
                            }
                            $retorno["propietarios"][$iProp]["nombrepropietario"] = ($prop["razonsocial"]);
                            $retorno["propietarios"][$iProp]["nom1propietario"] = ($prop["nombre1"]);
                            $retorno["propietarios"][$iProp]["nom2propietario"] = ($prop["nombre2"]);
                            $retorno["propietarios"][$iProp]["ape1propietario"] = ($prop["apellido1"]);
                            $retorno["propietarios"][$iProp]["ape2propietario"] = ($prop["apellido2"]);
                            $retorno["propietarios"][$iProp]["tipopropiedad"] = ($prop["tipopropiedad"]);
                            $retorno["tipopropiedad"] = $prop["tipopropiedad"];
                            $retorno["propietarios"][$iProp]["direccionpropietario"] = ($prop["dircom"]);
                            $retorno["propietarios"][$iProp]["municipiopropietario"] = ($prop["muncom"]);
                            $retorno["propietarios"][$iProp]["direccionnotpropietario"] = ($prop["dirnot"]);
                            $retorno["propietarios"][$iProp]["municipionotpropietario"] = ($prop["munnot"]);
                            $retorno["propietarios"][$iProp]["telefonopropietario"] = ($prop["telcom1"]);
                            $retorno["propietarios"][$iProp]["telefono2propietario"] = ($prop["telcom2"]);
                            $retorno["propietarios"][$iProp]["celularpropietario"] = ($prop["telcom3"]);
                            $retorno["propietarios"][$iProp]["nomreplegpropietario"] = ($prop["nombrereplegal"]);
                            $retorno["propietarios"][$iProp]["numidreplegpropietario"] = ($prop["identificacionreplegal"]);
                            $retorno["propietarios"][$iProp]["tipoidreplegpropietario"] = ($prop["tipoidentificacionreplegal"]);
                            if (isset($prop["participacion"])) {
                                $retorno["propietarios"][$iProp]["participacionpropietario"] = $prop["participacion"];
                            } else {
                                $retorno["propietarios"][$iProp]["participacionpropietario"] = 0;
                            }

                            $retorno["propietarios"][$iProp]["fecmatripropietario"] = '';
                            $retorno["propietarios"][$iProp]["fecrenovpropietario"] = '';
                            $retorno["propietarios"][$iProp]["ultanorenpropietario"] = '';
                            $retorno["propietarios"][$iProp]["organizacionpropietario"] = '';
                            $retorno["propietarios"][$iProp]["estadodatospropietario"] = '';
                            $retorno["propietarios"][$iProp]["estadomatriculapropietario"] = '';
                            $retorno["propietarios"][$iProp]["afiliacionpropietario"] = '';
                            $retorno["propietarios"][$iProp]["ultanorenafipropietario"] = '';
                            $retorno["propietarios"][$iProp]["saldoafiliadopropietario"] = '';

                            if (isset($prop["fecmatprop"])) {
                                $retorno["propietarios"][$iProp]["fecmatripropietario"] = ($prop["fecmatprop"]);
                            }
                            if (isset($prop["fecrenprop"])) {
                                $retorno["propietarios"][$iProp]["fecrenovpropietario"] = ($prop["fecrenprop"]);
                            }
                            if (isset($prop["ultanorenprop"])) {
                                $retorno["propietarios"][$iProp]["ultanorenpropietario"] = ($prop["ultanorenprop"]);
                            }
                            if (isset($prop["organizacionprop"])) {
                                $retorno["propietarios"][$iProp]["organizacionpropietario"] = ($prop["organizacionprop"]);
                            }
                            if (isset($prop["estadodatosprop"])) {
                                $retorno["propietarios"][$iProp]["estadodatospropietario"] = ($prop["estadodatosprop"]);
                            }
                            if (isset($prop["estadomatriculaprop"])) {
                                $retorno["propietarios"][$iProp]["estadomatriculapropietario"] = ($prop["estadomatriculaprop"]);
                            }
                            if (isset($prop["afiliacionprop"])) {
                                $retorno["propietarios"][$iProp]["afiliacionpropietario"] = ($prop["afiliacionprop"]);
                            }
                            if (isset($prop["ultanorenafliaprop"])) {
                                $retorno["propietarios"][$iProp]["ultanorenafipropietario"] = ($prop["ultanorenafliaprop"]);
                            }
                            if (isset($prop["saldoafliaprop"])) {
                                $retorno["propietarios"][$iProp]["saldoafiliadopropietario"] = ($prop["saldoafliaprop"]);
                            }
                        }
                    }
                }
            }
        }

        if ($genlog == 'si') {
            \logApi::general2($nameLog, $retorno["matricula"], 'Leyo mreg_est_propietarios');
        }

        // Propietarios historicos
        $retorno["propietariosh"] = array();
        if ($retorno["organizacion"] == '02') {
            $props = retornarRegistrosMysqliApi($mysqli, 'mreg_est_propietarios', "matricula='" . $retorno["matricula"] . "'", "id");
            $iProp = 0;
            if ($props) {
                if (!empty($props)) {
                    foreach ($props as $prop) {
                        if ($prop["estado"] == 'H') {
                            $iProp++;
                            if (ltrim($prop["matriculapropietario"], "0") != '') {
                                if (ltrim($prop["codigocamara"], "0") == '') {
                                    $prop["codigocamara"] = $_SESSION["generales"]["codigoempresa"];
                                }
                            }
                            $prop["fecmatprop"] = '';
                            $prop["fecrenprop"] = '';
                            $prop["ultanorenprop"] = '';
                            $prop["organizacionprop"] = '';
                            $prop["estadodatosprop"] = '';
                            $prop["estadomatriculaprop"] = '';
                            $prop["afiliacionprop"] = '';
                            $prop["ultanorenafliaprop"] = '';
                            $prop["saldoafliaprop"] = 0;
                            $prop["nombrereplegal"] = '';
                            $prop["identificacionreplegal"] = '';
                            $prop["tipoidentificacionreplegal"] = '';

                            if ($prop["codigocamara"] == $_SESSION["generales"]["codigoempresa"]) {
                                if (ltrim(trim($prop["matriculapropietario"]), "0") != '') {
                                    $xprop = retornarRegistroMysqliApi($mysqli, 'mreg_est_inscritos', "matricula='" . ltrim($prop["matriculapropietario"], "0") . "'");
                                    if ($xprop === false || empty($xprop)) {
                                        $prop["tipoidentificacion"] = '';
                                        $prop["identificacion"] = '';
                                        $prop["nit"] = '';
                                        $prop["razonsocial"] = '';
                                        $prop["nombre1"] = '';
                                        $prop["nombre2"] = '';
                                        $prop["apellido1"] = '';
                                        $prop["apellido2"] = '';
                                        $prop["dircom"] = '';
                                        $prop["muncom"] = '';
                                        $prop["dirnot"] = '';
                                        $prop["munnot"] = '';
                                        $prop["telcom1"] = '';
                                        $prop["telcom2"] = '';
                                        $prop["telcom3"] = '';
                                        $prop["fecmatprop"] = '';
                                        $prop["fecrenprop"] = '';
                                        $prop["ultanorenprop"] = '';
                                        $prop["organizacionprop"] = '';
                                        $prop["estadodatosprop"] = '';
                                        $prop["estadomatriculaprop"] = '';
                                        $prop["afiliacionprop"] = '';
                                        $prop["ultanorenafliaprop"] = '';
                                        $prop["saldoafliaprop"] = '';
                                        $prop["nombrereplegal"] = '';
                                        $prop["identificacionreplegal"] = '';
                                        $prop["tipoidentificacionreplegal"] = '';
                                    } else {
                                        $prop["tipoidentificacion"] = $xprop["idclase"];
                                        $prop["identificacion"] = $xprop["numid"];
                                        $prop["nit"] = $xprop["nit"];
                                        $prop["razonsocial"] = $xprop["razonsocial"];
                                        $prop["nombre1"] = $xprop["nombre1"];
                                        $prop["nombre2"] = $xprop["nombre2"];
                                        $prop["apellido1"] = $xprop["apellido1"];
                                        $prop["apellido2"] = $xprop["apellido2"];
                                        $prop["dircom"] = $xprop["dircom"];
                                        $prop["muncom"] = $xprop["muncom"];
                                        $prop["dirnot"] = $xprop["dirnot"];
                                        $prop["munnot"] = $xprop["munnot"];
                                        $prop["telcom1"] = $xprop["telcom1"];
                                        $prop["telcom2"] = $xprop["telcom2"];
                                        $prop["telcom3"] = $xprop["telcom3"];
                                        $prop["fecmatprop"] = $xprop["fecmatricula"];
                                        $prop["fecrenprop"] = $xprop["fecrenovacion"];
                                        $prop["ultanorenprop"] = $xprop["ultanoren"];
                                        $prop["organizacionprop"] = $xprop["organizacion"];
                                        $prop["estadodatosprop"] = $xprop["ctrestdatos"];
                                        $prop["estadomatriculaprop"] = $xprop["ctrestmatricula"];
                                        $prop["afiliacionprop"] = $xprop["ctrafiliacion"];
                                        $prop["ultanorenafliaprop"] = $xprop["anorenaflia"];
                                        $prop["saldoafliaprop"] = $xprop["saldoaflia"];

                                        $iXvincs = 0;
                                        $xvincs = retornarRegistrosMysqliApi($mysqli, 'mreg_est_vinculos', "matricula='" . $xprop["matricula"] . "' and estado = 'V'", "id");
                                        if ($xvincs) {
                                            if (!empty($xvincs)) {
                                                foreach ($xvincs as $xvin) {
                                                    if (
                                                            $_SESSION["maestrovinculos"][$xvin["vinculo"]]["tipovinculo"] == 'RLP' ||
                                                            $_SESSION["maestrovinculos"][$xvin["vinculo"]]["tipovinculo"] == 'GER'
                                                    ) {
                                                        $iXvincs++;
                                                        if ($iXvincs == 1) {
                                                            $prop["nombrereplegal"] = stripslashes($xvin["nombre"]);
                                                            $prop["identificacionreplegal"] = $xvin["idclase"];
                                                            $prop["tipoidentificacionreplegal"] = $xvin["numid"];
                                                        }
                                                        $iVinProp++;
                                                        $retorno["vincuprop"][$iVinProp] = array();
                                                        $retorno["vincuprop"][$iVinProp]["idclase"] = $xvin["idclase"];
                                                        $retorno["vincuprop"][$iVinProp]["numid"] = $xvin["numid"];
                                                        $retorno["vincuprop"][$iVinProp]["nombre"] = $xvin["nombre"];
                                                        $retorno["vincuprop"][$iVinProp]["vinculo"] = $xvin["vinculo"];
                                                        $retorno["vincuprop"][$iVinProp]["cargo"] = $xvin["descargo"];
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }

                            $retorno["propietariosh"][$iProp]["id"] = $prop["id"];
                            $retorno["propietariosh"][$iProp]["camarapropietario"] = $prop["codigocamara"];
                            $retorno["propietariosh"][$iProp]["matriculapropietario"] = ltrim($prop["matriculapropietario"], '0');
                            $retorno["propietariosh"][$iProp]["idtipoidentificacionpropietario"] = trim($prop["tipoidentificacion"]);
                            $retorno["propietariosh"][$iProp]["identificacionpropietario"] = ltrim($prop["identificacion"], '0');
                            $retorno["propietariosh"][$iProp]["nitpropietario"] = ltrim($prop["nit"], '0');
                            $retorno["propietariosh"][$iProp]["nombrepropietario"] = ($prop["razonsocial"]);
                            $retorno["propietariosh"][$iProp]["nom1propietario"] = ($prop["nombre1"]);
                            $retorno["propietariosh"][$iProp]["nom2propietario"] = ($prop["nombre2"]);
                            $retorno["propietariosh"][$iProp]["ape1propietario"] = ($prop["apellido1"]);
                            $retorno["propietariosh"][$iProp]["ape2propietario"] = ($prop["apellido2"]);
                            $retorno["propietariosh"][$iProp]["tipopropiedad"] = ($prop["tipopropiedad"]);
                            $retorno["tipopropiedad"] = ($prop["tipopropiedad"]);
                            $retorno["propietariosh"][$iProp]["direccionpropietario"] = ($prop["dircom"]);
                            $retorno["propietariosh"][$iProp]["municipiopropietario"] = ($prop["muncom"]);
                            $retorno["propietariosh"][$iProp]["direccionnotpropietario"] = ($prop["dirnot"]);
                            $retorno["propietariosh"][$iProp]["municipionotpropietario"] = ($prop["munnot"]);
                            $retorno["propietariosh"][$iProp]["telefonopropietario"] = ($prop["telcom1"]);
                            $retorno["propietariosh"][$iProp]["telefono2propietario"] = ($prop["telcom2"]);
                            $retorno["propietariosh"][$iProp]["celularpropietario"] = ($prop["telcom3"]);
                            $retorno["propietariosh"][$iProp]["nomreplegpropietario"] = ($prop["nombrereplegal"]);
                            $retorno["propietariosh"][$iProp]["numidreplegpropietario"] = ($prop["identificacionreplegal"]);
                            $retorno["propietariosh"][$iProp]["tipoidreplegpropietario"] = ($prop["tipoidentificacionreplegal"]);

                            if (isset($prop["participacion"])) {
                                $retorno["propietariosh"][$iProp]["participacionpropietario"] = $prop["participacion"];
                            } else {
                                $retorno["propietariosh"][$iProp]["participacionpropietario"] = 0;
                            }

                            $retorno["propietariosh"][$iProp]["fecmatripropietario"] = '';
                            $retorno["propietariosh"][$iProp]["fecrenovpropietario"] = '';
                            $retorno["propietariosh"][$iProp]["ultanorenpropietario"] = '';
                            $retorno["propietariosh"][$iProp]["organizacionpropietario"] = '';
                            $retorno["propietariosh"][$iProp]["estadodatospropietario"] = '';
                            $retorno["propietariosh"][$iProp]["estadomatriculapropietario"] = '';
                            $retorno["propietariosh"][$iProp]["afiliacionpropietario"] = '';
                            $retorno["propietariosh"][$iProp]["ultanorenafipropietario"] = '';
                            $retorno["propietariosh"][$iProp]["saldoafiliadopropietario"] = '';

                            if (isset($prop["fecmatprop"])) {
                                $retorno["propietariosh"][$iProp]["fecmatripropietario"] = ($prop["fecmatprop"]);
                            }
                            if (isset($prop["fecrenprop"])) {
                                $retorno["propietariosh"][$iProp]["fecrenovpropietario"] = ($prop["fecrenprop"]);
                            }
                            if (isset($prop["ultanorenprop"])) {
                                $retorno["propietariosh"][$iProp]["ultanorenpropietario"] = ($prop["ultanorenprop"]);
                            }
                            if (isset($prop["organizacionprop"])) {
                                $retorno["propietariosh"][$iProp]["organizacionpropietario"] = ($prop["organizacionprop"]);
                            }
                            if (isset($prop["estadodatosprop"])) {
                                $retorno["propietariosh"][$iProp]["estadodatospropietario"] = ($prop["estadodatosprop"]);
                            }
                            if (isset($prop["estadomatriculaprop"])) {
                                $retorno["propietariosh"][$iProp]["estadomatriculapropietario"] = ($prop["estadomatriculaprop"]);
                            }
                            if (isset($prop["afiliacionprop"])) {
                                $retorno["propietariosh"][$iProp]["afiliacionpropietario"] = ($prop["afiliacionprop"]);
                            }
                            if (isset($prop["ultanorenafliaprop"])) {
                                $retorno["propietariosh"][$iProp]["ultanorenafipropietario"] = ($prop["ultanorenafliaprop"]);
                            }
                            if (isset($prop["saldoafliaprop"])) {
                                $retorno["propietariosh"][$iProp]["saldoafiliadopropietario"] = ($prop["saldoafliaprop"]);
                            }
                        }
                    }
                }
            }
        }
        if ($genlog == 'si') {
            \logApi::general2($nameLog, $retorno["matricula"], 'Leyo mreg_est_propietarios historicos');
        }

        // Vinculos de la casa principal, en caso de sucursales o agencias
        if ($retorno["organizacion"] > '02' && ($retorno["categoria"] == '2' || $retorno["categoria"] == '3')) {
            if ($retorno["cpcodcam"] == '' || $retorno["cpcodcam"] == $_SESSION["generales"]["codigoempresa"]) {
                if ($retorno["cpnummat"] != '') {
                    $iXvincs = 0;
                    $xvincs = retornarRegistrosMysqliApi($mysqli, 'mreg_est_vinculos', "matricula='" . $retorno["cpnummat"] . "' and estado = 'V'", "id");
                    if ($xvincs) {
                        if (!empty($xvincs)) {
                            foreach ($xvincs as $xvin) {
                                if (
                                        $_SESSION["maestrovinculos"][$xvin["vinculo"]]["tipovinculo"] == 'RLP' ||
                                        $_SESSION["maestrovinculos"][$xvin["vinculo"]]["tipovinculo"] == 'GER'
                                ) {
                                    $iXvincs++;
                                    if ($iXvincs == 1) {
                                        $prop["nombrereplegal"] = stripslashes($xvin["nombre"]);
                                        $prop["identificacionreplegal"] = $xvin["idclase"];
                                        $prop["tipoidentificacionreplegal"] = $xvin["numid"];
                                    }
                                    $iVinProp++;
                                    $retorno["vincuprop"][$iVinProp] = array();
                                    $retorno["vincuprop"][$iVinProp]["idclase"] = $xvin["idclase"];
                                    $retorno["vincuprop"][$iVinProp]["numid"] = $xvin["numid"];
                                    $retorno["vincuprop"][$iVinProp]["nombre"] = $xvin["nombre"];
                                    $retorno["vincuprop"][$iVinProp]["vinculo"] = $xvin["vinculo"];
                                    $retorno["vincuprop"][$iVinProp]["cargo"] = $xvin["descargo"];
                                }
                            }
                        }
                    }
                }
            }
        }
        if ($genlog == 'si') {
            \logApi::general2($nameLog, $retorno["matricula"], 'Leyo mreg_est_vinculos de la casa principal');
        }

        // \logApi::general2($nameLog, $retorno["matricula"], 'Entró a relación establecimientos : ' . date("His"));
        // Relación de establecimientos
        $retorno["establecimientos"] = array();
        if ($retorno["organizacion"] != '02' && (trim($retorno["categoria"]) == '' || $retorno["categoria"] == '0' || $retorno["categoria"] == '1')) {
            $in = "'" . ltrim(trim($retorno["matricula"]), "0") . "'";
            $arrX = retornarRegistrosMysqliApi($mysqli, 'mreg_est_propietarios', "matriculapropietario IN (" . $in . ")", "matricula");
            if ($arrX === false) {
                echo $_SESSION["generales"]["mensajeerror"];
            }
            if ($arrX && !empty($arrX)) {
                $ix = 0;
                foreach ($arrX as $x) {
                    if (ltrim(trim($x["matricula"]), "0") != '') {
                        if ($x["estado"] != 'H') {
                            if ($x["codigocamara"] == '' || $x["codigocamara"] == $_SESSION["generales"]["codigoempresa"]) {
                                $temEst = retornarRegistroMysqliApi($mysqli, 'mreg_est_inscritos', "matricula='" . $x["matricula"] . "'");
                                if (isset($temEst["ctrestmatricula"]) && ($temEst["ctrestmatricula"] == 'MA' || $temEst["ctrestmatricula"] == 'MI')) {
                                    if ($temEst["organizacion"] == '02') {
                                        $ix++;
                                        $retorno["establecimientos"][$ix]["matriculaestablecimiento"] = $x["matricula"];
                                        $retorno["establecimientos"][$ix]["nombreestablecimiento"] = $temEst["razonsocial"];
                                        $retorno["establecimientos"][$ix]["estadodatosestablecimiento"] = $temEst["ctrestmatricula"];
                                        $retorno["establecimientos"][$ix]["estadomatricula"] = $temEst["ctrestmatricula"];
                                        $retorno["establecimientos"][$ix]["dircom"] = $temEst["dircom"];
                                        $retorno["establecimientos"][$ix]["barriocom"] = $temEst["barriocom"];
                                        $retorno["establecimientos"][$ix]["telcom1"] = $temEst["telcom1"];
                                        $retorno["establecimientos"][$ix]["telcom2"] = $temEst["telcom2"];
                                        $retorno["establecimientos"][$ix]["telcom3"] = $temEst["telcom3"];
                                        $retorno["establecimientos"][$ix]["muncom"] = $temEst["muncom"];
                                        $retorno["establecimientos"][$ix]["emailcom"] = $temEst["emailcom"];
                                        $retorno["establecimientos"][$ix]["fechamatricula"] = $temEst["fecmatricula"];
                                        $retorno["establecimientos"][$ix]["fecharenovacion"] = $temEst["fecrenovacion"];
                                        $retorno["establecimientos"][$ix]["ultanoren"] = $temEst["ultanoren"];
                                        $retorno["establecimientos"][$ix]["actvin"] = $temEst["actvin"];
                                        $histoest = encontrarHistoricoPagosMysqliApi($mysqli, $x["matricula"], $serviciosRenovacion, $serviciosAfiliacion, $serviciosMatricula);
                                        $retorno["establecimientos"][$ix]["fecharenovacion"] = $histoest["fecultren"];
                                        $retorno["establecimientos"][$ix]["ultanoren"] = $histoest["ultanoren"];
                                        if (isset($histoest["actultren"]) && is_numeric($histoest["actultren"])) {
                                            $retorno["establecimientos"][$ix]["actvin"] = $histoest["actultren"];
                                        }
                                        $retorno["establecimientos"][$ix]["ciiu1"] = $temEst["ciiu1"];
                                        $retorno["establecimientos"][$ix]["ciiu2"] = $temEst["ciiu2"];
                                        $retorno["establecimientos"][$ix]["ciiu3"] = $temEst["ciiu3"];
                                        $retorno["establecimientos"][$ix]["ciiu4"] = $temEst["ciiu4"];
                                        $retorno["establecimientos"][$ix]["embargado"] = 'NO';
                                        $retorno["establecimientos"][$ix]["embargos"] = array();

                                        $temE = retornarRegistrosMysqliApi($mysqli, 'mreg_est_embargos', "matricula='" . trim($x["matricula"]) . "' and acto IN ('0900','0940','0991','1000','1040') and ctrestadoembargo='1'");
                                        if ($temE && !empty($temE)) {
                                            $iEmb = 0;
                                            foreach ($temE as $e) {
                                                $retorno["establecimientos"][$ix]["embargado"] = 'SI';
                                                $iEmb++;
                                                $retorno["establecimientos"][$ix]["embargos"][$iEmb]["libroembargo"] = $e["libro"];
                                                $retorno["establecimientos"][$ix]["embargos"][$iEmb]["registroembargo"] = $e["numreg"];
                                                $retorno["establecimientos"][$ix]["embargos"][$iEmb]["fechaembargo"] = $e["fecinscripcion"];
                                                $retorno["establecimientos"][$ix]["embargos"][$iEmb]["txtorigenembargo"] = $e["txtorigen"];
                                                $retorno["establecimientos"][$ix]["embargos"][$iEmb]["noticiaembargo"] = stripslashes($e["noticia"]);
                                            }
                                        }

                                        $retorno["establecimientos"][$ix]["valest"] = $temEst["actvin"];
                                        $retorno["establecimientos"][$ix]["tipopropiedad"] = '';
                                        $retorno["establecimientos"][$ix]["ideadministrador"] = '';
                                        $retorno["establecimientos"][$ix]["nombreadministrador"] = '';
                                        $retorno["establecimientos"][$ix]["idearrendatario"] = '';
                                        $retorno["establecimientos"][$ix]["nombrearrendatario"] = '';

                                        $temE = retornarRegistrosMysqliApi($mysqli, 'mreg_est_vinculos', "matricula='" . $x["matricula"] . "' and estado='V'", "vinculo");
                                        if ($temE && !empty($temE)) {
                                            foreach ($temE as $tx) {
                                                if (isset($_SESSION["maestrovinculos"][$tx["vinculo"]]["tipovinculo"]) && $_SESSION["maestrovinculos"][$tx["vinculo"]]["tipovinculo"] == 'ADMP') {
                                                    $retorno["establecimientos"][$ix]["ideadministrador"] = $tx["numid"];
                                                    $retorno["establecimientos"][$ix]["nombreadministrador"] = $tx["nombre"];
                                                }
                                                if (isset($_SESSION["maestrovinculos"][$tx["vinculo"]]["tipovinculo"]) && $_SESSION["maestrovinculos"][$tx["vinculo"]]["tipovinculo"] == 'ARR') {
                                                    $retorno["establecimientos"][$ix]["idearrendatario"] = $tx["numid"];
                                                    $retorno["establecimientos"][$ix]["nombrearrendatario"] = $tx["nombre"];
                                                }
                                            }
                                        }

                                        // 2019-09-10: JINT: Indicar ai existe una solicitud de cancelacion vigente
                                        $retorno["establecimientos"][$ix]["solicitudcancelacion"] = 'no';
                                        $liqs = retornarRegistrosMysqliApi($mysqli, 'mreg_liquidacion', "idexpedientebase='" . $x["matricula"] . "' and tipotramite='solicitudcancelacionest'");
                                        if ($liqs && !empty($liqs)) {
                                            foreach ($liqs as $l) {
                                                if ($l["idestado"] >= '07' && $l["idestado"] != '08' && $l["idestado"] != '10' && $l["idestado"] != '19' && $l["idestado"] != '44' && $l["idestado"] != '99') {
                                                    $retorno["establecimientos"][$ix]["solicitudcancelacion"] = 'si';
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
            unset($arrX);
        }
        if ($genlog == 'si') {
            \logApi::general2($nameLog, $retorno["matricula"], 'Leyo mreg_est_propietarios relacion de establecimientos');
        }

        //2017-11-22 WSIERRA:         
        $retorno["establecimientosh"] = array();

        if ($retorno["organizacion"] != '02' && (trim($retorno["categoria"]) == '' || $retorno["categoria"] == '0' || $retorno["categoria"] == '1')) {
            $in = "'" . ltrim(trim($retorno["matricula"]), "0") . "'";
            $arrX = retornarRegistrosMysqliApi($mysqli, 'mreg_est_propietarios', "matriculapropietario IN (" . $in . ")", "matricula");
            if ($arrX === false) {
                echo $_SESSION["generales"]["mensajeerror"];
            }
            if ($arrX && !empty($arrX)) {
                $ix = 0;
                foreach ($arrX as $x) {
                    if (ltrim(trim($x["matricula"]), "0") != '') {
                        if ($x["estado"] != 'H') {
                            if ($x["codigocamara"] == '' || $x["codigocamara"] == $_SESSION["generales"]["codigoempresa"]) {
                                $temEst = retornarRegistroMysqliApi($mysqli, 'mreg_est_inscritos', "matricula='" . $x["matricula"] . "'");
                                if ($temEst && !empty($temEst)) {
                                    if ($temEst["ctrestmatricula"] == 'MC' || $temEst["ctrestmatricula"] == 'MF') {
                                        if ($temEst["organizacion"] == '02') {
                                            $ix++;
                                            $retorno["establecimientosh"][$ix]["matriculaestablecimiento"] = $x["matricula"];
                                            $retorno["establecimientosh"][$ix]["nombreestablecimiento"] = $temEst["razonsocial"];
                                            $retorno["establecimientosh"][$ix]["estadodatosestablecimiento"] = $temEst["ctrestmatricula"];
                                            $retorno["establecimientosh"][$ix]["estadomatricula"] = $temEst["ctrestmatricula"];
                                            $retorno["establecimientosh"][$ix]["dircom"] = $temEst["dircom"];
                                            $retorno["establecimientosh"][$ix]["barriocom"] = $temEst["barriocom"];
                                            $retorno["establecimientosh"][$ix]["telcom1"] = $temEst["telcom1"];
                                            $retorno["establecimientosh"][$ix]["telcom2"] = $temEst["telcom2"];
                                            $retorno["establecimientosh"][$ix]["telcom3"] = $temEst["telcom3"];
                                            $retorno["establecimientosh"][$ix]["muncom"] = $temEst["muncom"];
                                            $retorno["establecimientosh"][$ix]["emailcom"] = $temEst["emailcom"];
                                            $retorno["establecimientosh"][$ix]["fechamatricula"] = $temEst["fecmatricula"];
                                            $retorno["establecimientosh"][$ix]["fecharenovacion"] = $temEst["fecrenovacion"];
                                            $retorno["establecimientosh"][$ix]["fechacancelacion"] = $temEst["feccancelacion"];
                                            $retorno["establecimientosh"][$ix]["ultanoren"] = $temEst["ultanoren"];
                                            $retorno["establecimientosh"][$ix]["actvin"] = $temEst["actvin"];
                                            $retorno["establecimientosh"][$ix]["ciiu1"] = $temEst["ciiu1"];
                                            $retorno["establecimientosh"][$ix]["ciiu2"] = $temEst["ciiu2"];
                                            $retorno["establecimientosh"][$ix]["ciiu3"] = $temEst["ciiu3"];
                                            $retorno["establecimientosh"][$ix]["ciiu4"] = $temEst["ciiu4"];
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
            unset($arrX);
        }
        if ($genlog == 'si') {
            \logApi::general2($nameLog, $retorno["matricula"], 'Leyo relacion de establecimientos historicos');
        }

        // Relación de sucursales y agencias
        $retorno["sucursalesagencias"] = array();
        if ($retorno["organizacion"] == '01' || ($retorno["organizacion"] > '02' && $retorno["categoria"] == '1')) {
            $camposRequeridos = 'matricula,razonsocial,categoria,ctrestmatricula,fecmatricula,fecrenovacion,ultanoren,dircom,barriocom,muncom,telcom1,telcom2,telcom3,emailcom,ciiu1,ciiu2,ciiu3,ciiu4,actvin,cpcodcam,cpnummat';
            $arrX = retornarRegistrosMysqliApi($mysqli, 'mreg_est_inscritos', "cpcodcam='" . $_SESSION["generales"]["codigoempresa"] . "' and cpnummat='" . $retorno["matricula"] . "'", "matricula", $camposRequeridos);
            if ($arrX && !empty($arrX)) {
                $ix = 0;
                foreach ($arrX as $x) {
                    if (trim($x["matricula"]) != '') {
                        $ix++;
                        $retorno["sucursalesagencias"][$ix]["matriculasucage"] = $x["matricula"];
                        $retorno["sucursalesagencias"][$ix]["nombresucage"] = $x["razonsocial"];
                        $retorno["sucursalesagencias"][$ix]["categoria"] = $x["categoria"];
                        $retorno["sucursalesagencias"][$ix]["estado"] = $x["ctrestmatricula"];
                        $retorno["sucursalesagencias"][$ix]["fechamatricula"] = $x["fecmatricula"];
                        $retorno["sucursalesagencias"][$ix]["fecharenovacion"] = $x["fecrenovacion"];
                        $retorno["sucursalesagencias"][$ix]["ultanoren"] = $x["ultanoren"];
                        $histoest = encontrarHistoricoPagosMysqliApi($mysqli, $x["matricula"], $serviciosRenovacion, $serviciosAfiliacion, $serviciosMatricula);
                        $retorno["sucursalesagencias"][$ix]["fecharenovacion"] = $histoest["fecultren"];
                        $retorno["sucursalesagencias"][$ix]["ultanoren"] = $histoest["ultanoren"];
                        if (isset($histoest["actultren"]) && is_numeric($histoest["actultren"])) {
                            $retorno["sucursalesagencias"][$ix]["actvin"] = $histoest["actultren"];
                        }
                        $retorno["sucursalesagencias"][$ix]["dircom"] = $x["dircom"];
                        $retorno["sucursalesagencias"][$ix]["barriocom"] = $x["barriocom"];
                        $retorno["sucursalesagencias"][$ix]["muncom"] = $x["muncom"];
                        $retorno["sucursalesagencias"][$ix]["telcom1"] = $x["telcom1"];
                        $retorno["sucursalesagencias"][$ix]["telcom2"] = $x["telcom2"];
                        $retorno["sucursalesagencias"][$ix]["telcom3"] = $x["telcom3"];
                        $retorno["sucursalesagencias"][$ix]["emailcom"] = $x["emailcom"];
                        $retorno["sucursalesagencias"][$ix]["ciiu1"] = $x["ciiu1"];
                        $retorno["sucursalesagencias"][$ix]["ciiu2"] = $x["ciiu2"];
                        $retorno["sucursalesagencias"][$ix]["ciiu3"] = $x["ciiu3"];
                        $retorno["sucursalesagencias"][$ix]["ciiu4"] = $x["ciiu4"];
                        $retorno["sucursalesagencias"][$ix]["embargado"] = 'NO';

                        $retorno["sucursalesagencias"][$ix]["libro1embargo"] = '';
                        $retorno["sucursalesagencias"][$ix]["registro1embargo"] = '';
                        $retorno["sucursalesagencias"][$ix]["fecha1embargo"] = '';
                        $retorno["sucursalesagencias"][$ix]["txtorigen1embargo"] = '';
                        $retorno["sucursalesagencias"][$ix]["libro2embargo"] = '';
                        $retorno["sucursalesagencias"][$ix]["registro2embargo"] = '';
                        $retorno["sucursalesagencias"][$ix]["fecha2embargo"] = '';
                        $retorno["sucursalesagencias"][$ix]["txtorigen2embargo"] = '';
                        $retorno["sucursalesagencias"][$ix]["libro3embargo"] = '';
                        $retorno["sucursalesagencias"][$ix]["registro3embargo"] = '';
                        $retorno["sucursalesagencias"][$ix]["fecha3embargo"] = '';
                        $retorno["sucursalesagencias"][$ix]["txtorigen3embargo"] = '';
                        $retorno["sucursalesagencias"][$ix]["libro4embargo"] = '';
                        $retorno["sucursalesagencias"][$ix]["registro4embargo"] = '';
                        $retorno["sucursalesagencias"][$ix]["fecha4embargo"] = '';
                        $retorno["sucursalesagencias"][$ix]["txtorigen4embargo"] = '';
                        $retorno["sucursalesagencias"][$ix]["libro5embargo"] = '';
                        $retorno["sucursalesagencias"][$ix]["registro5embargo"] = '';
                        $retorno["sucursalesagencias"][$ix]["fecha5embargo"] = '';
                        $retorno["sucursalesagencias"][$ix]["txtorigen5embargo"] = '';
                        $retorno["sucursalesagencias"][$ix]["embargado"] = 'NO';
                        $retorno["sucursalesagencias"][$ix]["embargos"] = array();

                        $temE = retornarRegistrosMysqliApi($mysqli, 'mreg_est_embargos', "matricula='" . trim($x["matricula"]) . "' and acto IN ('0900','0940','0991','1000','1040') and ctrestadoembargo='1'");
                        if ($temE && !empty($temE)) {
                            $iEmb = 0;
                            foreach ($temE as $e) {
                                $retorno["sucursalesagencias"][$ix]["embargado"] = 'SI';
                                $iEmb++;
                                $retorno["sucursalesagencias"][$ix]["embargos"][$iEmb]["libroembargo"] = $e["libro"];
                                $retorno["sucursalesagencias"][$ix]["embargos"][$iEmb]["registroembargo"] = $e["numreg"];
                                $retorno["sucursalesagencias"][$ix]["embargos"][$iEmb]["fechaembargo"] = $e["fecinscripcion"];
                                $retorno["sucursalesagencias"][$ix]["embargos"][$iEmb]["txtorigenembargo"] = $e["txtorigen"];
                            }
                        }

                        $retorno["sucursalesagencias"][$ix]["actvin"] = $x["actvin"];
                    }
                }
            }
            unset($arrX);
        }
        if ($genlog == 'si') {
            \logApi::general2($nameLog, $retorno["matricula"], 'Leyo relacion de sucursales y agencias');
        }


        // \logApi::general2($nameLog, $retorno["matricula"], 'Salió de relación suc/age : ' . date("His"));
        // Relación de establecimientos arrendados
        // \logApi::general2($nameLog, $retorno["matricula"], 'Entró a establecimientos arrendados : ' . date("His"));
        $retorno["establecimientosarrendados"] = array();
        if ($retorno["organizacion"] != '02' && (trim($retorno["categoria"]) == '' || $retorno["categoria"] == '0' || $retorno["categoria"] == '1')) {
            if ($retorno["identificacion"] != '') {
                $arrX = retornarRegistrosMysqliApi($mysqli, 'mreg_est_vinculos', "numid='" . $retorno["identificacion"] . "'", "matricula");
                if ($arrX === false) {
                    echo $_SESSION["generales"]["mensajeerror"];
                }
                if ($arrX && !empty($arrX)) {
                    $ix = 0;
                    foreach ($arrX as $x) {
                        if (isset($_SESSION["maestrovinculos"][$x["vinculo"]]) && $_SESSION["maestrovinculos"][$x["vinculo"]]["tipovinculo"] == 'ARR') {
                            if (ltrim(trim($x["matricula"]), "0") != '') {
                                if ($x["estado"] != 'H') {
                                    $temEst = retornarRegistroMysqliApi($mysqli, 'mreg_est_inscritos', "matricula='" . $x["matricula"] . "'");
                                    if ($temEst["ctrestmatricula"] == 'MA' || $temEst["ctrestmatricula"] == 'MI') {
                                        if ($temEst["organizacion"] == '02') {
                                            $ix++;
                                            $retorno["establecimientosarrendados"][$ix]["matricula"] = $x["matricula"];
                                            $retorno["establecimientosarrendados"][$ix]["nombre"] = $temEst["razonsocial"];
                                            $retorno["establecimientosarrendados"][$ix]["estadodatos"] = $temEst["ctrestmatricula"];
                                            $retorno["establecimientosarrendados"][$ix]["estadomatricula"] = $temEst["ctrestmatricula"];
                                            $retorno["establecimientosarrendados"][$ix]["dircom"] = $temEst["dircom"];
                                            $retorno["establecimientosarrendados"][$ix]["barriocom"] = $temEst["barriocom"];
                                            $retorno["establecimientosarrendados"][$ix]["telcom1"] = $temEst["telcom1"];
                                            $retorno["establecimientosarrendados"][$ix]["telcom2"] = $temEst["telcom2"];
                                            $retorno["establecimientosarrendados"][$ix]["telcom3"] = $temEst["telcom3"];
                                            $retorno["establecimientosarrendados"][$ix]["muncom"] = $temEst["muncom"];
                                            $retorno["establecimientosarrendados"][$ix]["emailcom"] = $temEst["emailcom"];
                                            $retorno["establecimientosarrendados"][$ix]["fechamatricula"] = $temEst["fecmatricula"];
                                            $retorno["establecimientosarrendados"][$ix]["fecharenovacion"] = $temEst["fecrenovacion"];
                                            $retorno["establecimientosarrendados"][$ix]["ultanoren"] = $temEst["ultanoren"];
                                            $retorno["establecimientosarrendados"][$ix]["actvin"] = $temEst["actvin"];
                                            $histoest = encontrarHistoricoPagosMysqliApi($mysqli, $x["matricula"], $serviciosRenovacion, $serviciosAfiliacion, $serviciosMatricula);
                                            $retorno["establecimientosarrendados"][$ix]["fecharenovacion"] = $histoest["fecultren"];
                                            $retorno["establecimientosarrendados"][$ix]["ultanoren"] = $histoest["ultanoren"];
                                            if (isset($histoest["actultren"]) && is_numeric($histoest["actultren"])) {
                                                $retorno["establecimientosarrendados"][$ix]["actvin"] = $histoest["actultren"];
                                            }
                                            $retorno["establecimientosarrendados"][$ix]["ciiu1"] = $temEst["ciiu1"];
                                            $retorno["establecimientosarrendados"][$ix]["ciiu2"] = $temEst["ciiu2"];
                                            $retorno["establecimientosarrendados"][$ix]["ciiu3"] = $temEst["ciiu3"];
                                            $retorno["establecimientosarrendados"][$ix]["ciiu4"] = $temEst["ciiu4"];
                                            $retorno["establecimientosarrendados"][$ix]["valest"] = $temEst["actvin"];
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
            unset($arrX);
        }
        if ($genlog == 'si') {
            \logApi::general2($nameLog, $retorno["matricula"], 'Leyo relacion de establecimientos arrendados');
        }


        // \logApi::general2($nameLog, $retorno["matricula"], 'Salió de establecimientos arrendados : ' . date("His"));
        //  if ($tipodata == 'E') {
        // Relación de establecimientos nacionales 
        // \logApi::general2($nameLog, $retorno["matricula"], 'Entró a establecimientos nacionales : ' . date("His"));

        $retorno["establecimientosnacionales"] = array();
        if ($tipodata == 'E' || $establecimientosnacionales == 'S') {
            if ($retorno["organizacion"] == '01' || $retorno["organizacion"] > '02' && $retorno["categoria"] == '1') {
                $ests = \funcionesGenerales::consultarEstablecimientosNacionales($mysqli, $retorno["tipoidentificacion"], $retorno["identificacion"]);
                if (!empty($ests)) {
                    $ix = 0;

                    foreach ($ests as $x) {
                        if ($x["codigo_estado_matricula"] == '01') { // Solo establecimientos activos
                            if ($x["codigo_camara"] != $_SESSION["generales"]["codigoempresa"]) { // Solo si son de una cámara diferente
                                $ix++;

                                //
                                $retorno["establecimientosnacionales"][$ix]["cc"] = trim($x["codigo_camara"]);
                                $retorno["establecimientosnacionales"][$ix]["matriculaestablecimiento"] = ltrim($x["matricula"], "0");
                                $retorno["establecimientosnacionales"][$ix]["nombreestablecimiento"] = trim($x["razon_social"]);
                                $retorno["establecimientosnacionales"][$ix]["estadodatosestablecimiento"] = '6';
                                $retorno["establecimientosnacionales"][$ix]["estadomatricula"] = 'MA';
                                $retorno["establecimientosnacionales"][$ix]["organizacion"] = trim($x["codigo_organizacion_juridica"]);
                                $retorno["establecimientosnacionales"][$ix]["categoria"] = trim($x["codigo_categoria_matricula"]);

                                //
                                $retorno["establecimientosnacionales"][$ix]["dircom"] = trim($x["direccion_comercial"]);
                                $retorno["establecimientosnacionales"][$ix]["barriocom"] = trim($x["codigo_barrio_comercial"]);
                                $retorno["establecimientosnacionales"][$ix]["nbarriocom"] = trim($x["barrio_comercial"]);
                                $retorno["establecimientosnacionales"][$ix]["telcom1"] = trim($x["telefono_comercial_1"]);
                                $retorno["establecimientosnacionales"][$ix]["telcom2"] = trim($x["telefono_comercial_2"]);
                                $retorno["establecimientosnacionales"][$ix]["telcom3"] = trim($x["telefono_comercial_3"]);
                                $retorno["establecimientosnacionales"][$ix]["muncom"] = trim($x["municipio_comercial"]);
                                $retorno["establecimientosnacionales"][$ix]["nmuncom"] = trim($x["nombre_municipio_comercial"]);
                                $retorno["establecimientosnacionales"][$ix]["emailcom"] = trim($x["correo_electronico_comercial"]);
                                $retorno["establecimientosnacionales"][$ix]["codpostalcom"] = trim($x["codigo_postal_comercial"]);

                                //
                                $retorno["establecimientosnacionales"][$ix]["dirnot"] = trim($x["direccion_fiscal"]);
                                $retorno["establecimientosnacionales"][$ix]["barrionot"] = trim($x["codigo_barrio_fiscal"]);
                                $retorno["establecimientosnacionales"][$ix]["nbarrionot"] = trim($x["barrio_fiscal"]);
                                $retorno["establecimientosnacionales"][$ix]["telnot1"] = '';
                                $retorno["establecimientosnacionales"][$ix]["telnot2"] = '';
                                $retorno["establecimientosnacionales"][$ix]["telnot3"] = '';
                                $retorno["establecimientosnacionales"][$ix]["munnot"] = trim($x["municipio_fiscal"]);
                                $retorno["establecimientosnacionales"][$ix]["nmunnot"] = trim($x["nombre_municipio_fiscal"]);
                                $retorno["establecimientosnacionales"][$ix]["emailnor"] = trim($x["correo_electronico_fiscal"]);
                                $retorno["establecimientosnacionales"][$ix]["codpostalnot"] = trim($x["codigo_postal_fiscal"]);

                                //
                                $retorno["establecimientosnacionales"][$ix]["fechamatricula"] = trim($x["fecha_matricula"]);
                                $retorno["establecimientosnacionales"][$ix]["fecharenovacion"] = trim($x["fecha_renovacion"]);
                                $retorno["establecimientosnacionales"][$ix]["fechacancelacion"] = '';
                                $retorno["establecimientosnacionales"][$ix]["ultanoren"] = trim($x["ultimo_ano_renovado"]);
                                $retorno["establecimientosnacionales"][$ix]["actvin"] = doubleval($x["valor_est_ag_suc"]);
                                $retorno["establecimientosnacionales"][$ix]["empleados"] = doubleval($x["empleados"]);

                                //
                                $retorno["establecimientosnacionales"][$ix]["ciiu1"] = '';
                                $retorno["establecimientosnacionales"][$ix]["ciiu2"] = '';
                                $retorno["establecimientosnacionales"][$ix]["ciiu3"] = '';
                                $retorno["establecimientosnacionales"][$ix]["ciiu4"] = '';
                                $retorno["establecimientosnacionales"][$ix]["shd1"] = '';
                                $retorno["establecimientosnacionales"][$ix]["shd2"] = '';
                                $retorno["establecimientosnacionales"][$ix]["shd3"] = '';
                                $retorno["establecimientosnacionales"][$ix]["shd4"] = '';
                                if (trim($x["ciiu1"]) != '') {
                                    $retorno["establecimientosnacionales"][$ix]["ciiu1"] = retornarRegistroMysqliApi($mysqli, "bas_ciius", "idciiunum='" . trim($x["ciiu1"]) . "'", "idciiu");
                                }
                                if (trim($x["ciiu2"]) != '') {
                                    $retorno["establecimientosnacionales"][$ix]["ciiu2"] = retornarRegistroMysqliApi($mysqli, "bas_ciius", "idciiunum='" . trim($x["ciiu2"]) . "'", "idciiu");
                                }
                                if (trim($x["ciiu3"]) != '') {
                                    $retorno["establecimientosnacionales"][$ix]["ciiu3"] = retornarRegistroMysqliApi($mysqli, "bas_ciius", "idciiunum='" . trim($x["ciiu3"]) . "'", "idciiu");
                                }
                                if (trim($x["ciiu4"]) != '') {
                                    $retorno["establecimientosnacionales"][$ix]["ciiu4"] = retornarRegistroMysqliApi($mysqli, "bas_ciius", "idciiunum='" . trim($x["ciiu4"]) . "'", "idciiu");
                                }
                                if (trim($x["shd1"]) != '') {
                                    $retorno["establecimientosnacionales"][$ix]["shd1"] = $x["shd1"];
                                }
                                if (trim($x["shd2"]) != '') {
                                    $retorno["establecimientosnacionales"][$ix]["shd2"] = $x["shd2"];
                                }
                                if (trim($x["shd3"]) != '') {
                                    $retorno["establecimientosnacionales"][$ix]["shd3"] = $x["shd3"];
                                }
                                if (trim($x["shd4"]) != '') {
                                    $retorno["establecimientosnacionales"][$ix]["shd4"] = $x["shd4"];
                                }
                                $retorno["establecimientosnacionales"][$ix]["desactiv"] = trim($x["desc_Act_Econ"]);

                                //
                                $retorno["establecimientosnacionales"][$ix]["afiliado"] = trim($x["afiliado"]);
                                $retorno["establecimientosnacionales"][$ix]["tipolocal"] = trim($x["codigo_tipo_local"]);
                                $retorno["establecimientosnacionales"][$ix]["ctrubi"] = trim($x["codigo_ubicacion_empresa"]);
                            }
                        }
                    }
                }
            }
        }
        if ($genlog == 'si') {
            \logApi::general2($nameLog, $retorno["matricula"], 'Leyo relacion de establecimientos nacionales');
        }


        // historicopagosrenovacion
        $iPagos = 0;
        $retorno["historicopagosrenovacion"] = array();
        if (!empty($histopagos["renovacionanos"])) {
            foreach ($histopagos["renovacionanos"] as $his) {
                $iPagos++;
                $retorno["historicopagosrenovacion"][$iPagos] = array(
                    'tipo' => 'Aplicadas',
                    'recibo' => $his["recibo"],
                    'fecharecibo' => $his["fecharecibo"],
                    'fecharenovacion' => $his["fecrenovacion"],
                    'ano' => $his["ano"],
                    'activos' => $his["activos"],
                    'valor' => $his["valor"]
                );
                if (isset($his["ai"]) && $his["ai"] == 'si') {
                    $retorno["historicopagosrenovacion"][$iPagos]["tipo"] = 'Pend Asent A.I.';
                }
            }
        }
        if (!empty($histopagos["renovacionsinaplicaranos"])) {
            foreach ($histopagos["renovacionsinaplicaranos"] as $his) {
                $retorno["historicopagosrenovacion"][] = array(
                    'tipo' => 'Sin aplicar',
                    'recibo' => $his["recibo"],
                    'fecharecibo' => $his["fecharecibo"],
                    'fecharenovacion' => $his["fecrenovacion"],
                    'ano' => $his["ano"],
                    'activos' => $his["activos"],
                    'valor' => $his["valor"]
                );
            }
        }

        //
        $retorno["rr"] = array();
        $retorno["lcodigosbarras"] = array();
        $arrX = retornarRegistrosMysqliApi($mysqli, 'mreg_est_codigosbarras', "matricula='" . $retorno["matricula"] . "'", "codigobarras");
        $ix = 0;
        $irr = 0;
        if ($arrX && !empty($arrX)) {
            foreach ($arrX as $x) {
                if (
                        $x["estadofinal"] == '01' || // Radicado
                        $x["estadofinal"] == '04' || // En estudio
                        $x["estadofinal"] == '09' || // Reingresado
                        $x["estadofinal"] == '10' || // Devuelto a reparto
                        $x["estadofinal"] == '11' || // Inscrito
                        $x["estadofinal"] == '13' || // Asignado a estudio
                        $x["estadofinal"] == '22' || // Registrado - proponentes
                        $x["estadofinal"] == '23' || // En digitacion
                        $x["estadofinal"] == '24' || // Digitado
                        $x["estadofinal"] == '34' || // Para firma
                        $x["estadofinal"] == '35' || // Firmado
                        $x["estadofinal"] == '38' // Control de calidad
                ) {
                    if ($x["actoreparto"] != '19' && $x["actoreparto"] != '23' && $x["actoreparto"] != '28' && $x["actoreparto"] != '54') {
                        $ix++;
                        $retorno["lcodigosbarras"][$ix]["cbar"] = $x["codigobarras"];
                        $retorno["lcodigosbarras"][$ix]["frad"] = $x["fecharadicacion"];
                        $retorno["lcodigosbarras"][$ix]["ttra"] = $x["actoreparto"];
                        $retorno["lcodigosbarras"][$ix]["esta"] = $x["estadofinal"];
                        $retorno["lcodigosbarras"][$ix]["nesta"] = '';
                        $retorno["lcodigosbarras"][$ix]["ntra"] = retornarRegistroMysqliApi($mysqli, 'mreg_codrutas', "id='" . $retorno["lcodigosbarras"][$ix]["ttra"] . "'", "descripcion");
                        $retorno["lcodigosbarras"][$ix]["sist"] = retornarRegistroMysqliApi($mysqli, 'mreg_codrutas', "id='" . $retorno["lcodigosbarras"][$ix]["ttra"] . "'", "tipo");
                        if (($retorno["lcodigosbarras"][$ix]["sist"] == 'ME') || ($retorno["lcodigosbarras"][$ix]["sist"] == 'ES') ||
                                ($retorno["lcodigosbarras"][$ix]["sist"] == 'RM') || ($retorno["lcodigosbarras"][$ix]["sist"] == 'RE')
                        ) {
                            $retorno["lcodigosbarras"][$ix]["nesta"] = retornarRegistroMysqliApi($mysqli, 'mreg_codestados_rutamercantil', "id='" . $retorno["lcodigosbarras"][$ix]["esta"] . "'", "descripcion");
                        }
                        if ($retorno["lcodigosbarras"][$ix]["sist"] == 'PR') {
                            $retorno["lcodigosbarras"][$ix]["nesta"] = retornarRegistroMysqliApi($mysqli, 'mreg_codestados_rutaproponentes', "id='" . $retorno["lcodigosbarras"][$ix]["esta"] . "'", "descripcion");
                        }
                        $retorno["codigosbarras"]++;

                        // Si la ruta es un embargo
                        if ($x["actoreparto"] == '07') {
                            $retorno["embargostramite"] = 'S';
                        }
                    }

                    // Si la ruta es un recurso en trámite
                    if ($x["actoreparto"] == '19' || $x["actoreparto"] == '23' || $x["actoreparto"] == '28' || $x["actoreparto"] == '54') {
                        $retorno["recursostramite"] = 'S';
                        $irr++;
                        $retorno["rr"][$irr] = array();
                        $retorno["rr"][$irr]["codigobarras"] = $x["codigobarras"];
                        $retorno["rr"][$irr]["fecharadicacion"] = $x["fecharadicacion"];
                        $retorno["rr"][$irr]["detalle"] = $x["detalle"];
                        $retorno["rr"][$irr]["estado"] = $x["estadofinal"];
                        $retorno["rr"][$irr]["tipodocrecurso"] = '';
                        $retorno["rr"][$irr]["numdocrecurso"] = '';
                        $retorno["rr"][$irr]["fecdocrecurso"] = '';
                        $retorno["rr"][$irr]["idclaserecurrente"] = '';
                        $retorno["rr"][$irr]["numidrecurrente"] = '';
                        $retorno["rr"][$irr]["nombrerecurrente"] = '';
                        $retorno["rr"][$irr]["tipodocafectado"] = '';
                        $retorno["rr"][$irr]["numdocafectado"] = '';
                        $retorno["rr"][$irr]["fecdocafectado"] = '';
                        $retorno["rr"][$irr]["libroafectado"] = '';
                        $retorno["rr"][$irr]["registroafectado"] = '';
                        $retorno["rr"][$irr]["dupliafectado"] = '';
                        $retorno["rr"][$irr]["subsidioapelacion"] = '';
                        $retorno["rr"][$irr]["fecharegistroafectado"] = '';
                        $retorno["rr"][$irr]["noticiarecurrida"] = '';
                        $retorno["rr"][$irr]["confirmainscripcion"] = '';
                        $retorno["rr"][$irr]["numeroresolucion"] = '';
                        $retorno["rr"][$irr]["fecharesolucion"] = '';
                        $retorno["rr"][$irr]["tipodocrecurso"] = $x["tipdoc"];
                        $retorno["rr"][$irr]["numdocrecurso"] = $x["numdoc"];
                        $retorno["rr"][$irr]["fecdocrecurso"] = $x["fecdoc"];
                        $retorno["rr"][$irr]["idclaserecurrente"] = $x["idclaserecurrente"];
                        $retorno["rr"][$irr]["numidrecurrente"] = $x["numidrecurrente"];
                        $retorno["rr"][$irr]["nombrerecurrente"] = $x["nombrerecurrente"];
                        $retorno["rr"][$irr]["libroafectado"] = $x["libroafectado"];
                        $retorno["rr"][$irr]["registroafectado"] = $x["registroafectado"];
                        $retorno["rr"][$irr]["dupliafectado"] = $x["dupliafectado"];
                        $retorno["rr"][$irr]["subsidioapelacion"] = $x["subsidioapelacion"];
                        $retorno["rr"][$irr]["confirmainscripcion"] = $x["confirmainscripcion"];
                        $retorno["rr"][$irr]["numeroresolucion"] = $x["numeroresolucion"];
                        $retorno["rr"][$irr]["fecharesolucion"] = $x["fecharesolucion"];
                        $retorno["rr"][$irr]["numidrecurrente2"] = retornarRegistroMysqliApi($mysqli, 'mreg_est_codigosbarras_campos', "codigobarras='" . $x["codigobarras"] . "' and campo='numidrecurrente2'", "contenido");
                        $retorno["rr"][$irr]["nombrerecurrente2"] = retornarRegistroMysqliApi($mysqli, 'mreg_est_codigosbarras_campos', "codigobarras='" . $x["codigobarras"] . "' and campo='nombrerecurrente2'", "contenido");
                        $retorno["rr"][$irr]["numidrecurrente3"] = retornarRegistroMysqliApi($mysqli, 'mreg_est_codigosbarras_campos', "codigobarras='" . $x["codigobarras"] . "' and campo='numidrecurrente3'", "contenido");
                        $retorno["rr"][$irr]["nombrerecurrente3"] = retornarRegistroMysqliApi($mysqli, 'mreg_est_codigosbarras_campos', "codigobarras='" . $x["codigobarras"] . "' and campo='nombrerecurrente3'", "contenido");
                        $retorno["rr"][$irr]["soloapelacion"] = retornarRegistroMysqliApi($mysqli, 'mreg_est_codigosbarras_campos', "codigobarras='" . $x["codigobarras"] . "' and campo='soloapelacion'", "contenido");

                        $retorno["rr"][$irr]["libroafectado2"] = '';
                        $retorno["rr"][$irr]["registroafectado2"] = '';
                        $retorno["rr"][$irr]["dupliafectado2"] = '';

                        $retorno["rr"][$irr]["libroafectado3"] = '';
                        $retorno["rr"][$irr]["registroafectado3"] = '';
                        $retorno["rr"][$irr]["dupliafectado3"] = '';

                        $retorno["rr"][$irr]["libroafectado4"] = '';
                        $retorno["rr"][$irr]["registroafectado42"] = '';
                        $retorno["rr"][$irr]["dupliafectado4"] = '';

                        $retorno["rr"][$irr]["tipodocafectado2"] = '';
                        $retorno["rr"][$irr]["numdocafectado2"] = '';
                        $retorno["rr"][$irr]["fecdocafectado2"] = '';
                        $retorno["rr"][$irr]["fecharegistroafectado2"] = '';
                        $retorno["rr"][$irr]["noticiarecurrida2"] = '';

                        $retorno["rr"][$irr]["tipodocafectado3"] = '';
                        $retorno["rr"][$irr]["numdocafectado3"] = '';
                        $retorno["rr"][$irr]["fecdocafectado3"] = '';
                        $retorno["rr"][$irr]["fecharegistroafectado3"] = '';
                        $retorno["rr"][$irr]["noticiarecurrida3"] = '';

                        $retorno["rr"][$irr]["tipodocafectado4"] = '';
                        $retorno["rr"][$irr]["numdocafectado4"] = '';
                        $retorno["rr"][$irr]["fecdocafectado4"] = '';
                        $retorno["rr"][$irr]["fecharegistroafectado4"] = '';
                        $retorno["rr"][$irr]["noticiarecurrida4"] = '';

                        $inscxs = retornarRegistroMysqliApi($mysqli, 'mreg_est_codigosbarras_campos', "codigobarras='" . $x["codigobarras"] . "' and campo='inscripcionafectada2'", "contenido");
                        if ($inscxs != '') {
                            list ($retorno["rr"][$irr]["libroafectado2"], $retorno["rr"][$irr]["registroafectado2"], $retorno["rr"][$irr]["dupliafectado2"]) = explode("-", $inscxs);
                        }
                        $inscxs = retornarRegistroMysqliApi($mysqli, 'mreg_est_codigosbarras_campos', "codigobarras='" . $x["codigobarras"] . "' and campo='inscripcionafectada3'", "contenido");
                        if ($inscxs != '') {
                            list ($retorno["rr"][$irr]["libroafectado3"], $retorno["rr"][$irr]["registroafectado3"], $retorno["rr"][$irr]["dupliafectado3"]) = explode("-", $inscxs);
                        }
                        $inscxs = retornarRegistroMysqliApi($mysqli, 'mreg_est_codigosbarras_campos', "codigobarras='" . $x["codigobarras"] . "' and campo='inscripcionafectada4'", "contenido");
                        if ($inscxs != '') {
                            list ($retorno["rr"][$irr]["libroafectado4"], $retorno["rr"][$irr]["registroafectado4"], $retorno["rr"][$irr]["dupliafectado4"]) = explode("-", $inscxs);
                        }

                        if ($retorno["rr"][$irr]["libroafectado"] != '' && $retorno["rr"][$irr]["registroafectado"] != '' && $retorno["rr"][$irr]["dupliafectado"] != '') {
                            $condx = "libro='" . $retorno["rr"][$irr]["libroafectado"] . "' and registro='" . $retorno["rr"][$irr]["registroafectado"] . "' and dupli='" . $retorno["rr"][$irr]["dupliafectado"] . "'";
                            $libx = retornarRegistroMysqliApi($mysqli, 'mreg_est_inscripciones', $condx);
                            if ($libx && !empty($libx)) {
                                $retorno["rr"][$irr]["tipodocafectado"] = $libx["tipodocumento"];
                                $retorno["rr"][$irr]["numdocafectado"] = $libx["numerodocumento"];
                                $retorno["rr"][$irr]["fecdocafectado"] = $libx["fechadocumento"];
                                $retorno["rr"][$irr]["fecharegistroafectado"] = $libx["fecharegistro"];
                                $retorno["rr"][$irr]["noticiarecurrida"] = $libx["noticia"];
                            }
                        }

                        if ($retorno["rr"][$irr]["libroafectado2"] != '' && $retorno["rr"][$irr]["registroafectado2"] != '' && $retorno["rr"][$irr]["dupliafectado2"] != '') {
                            $condx = "libro='" . $retorno["rr"][$irr]["libroafectado2"] . "' and registro='" . $retorno["rr"][$irr]["registroafectado2"] . "' and dupli='" . $retorno["rr"][$irr]["dupliafectado2"] . "'";
                            $libx = retornarRegistroMysqliApi($mysqli, 'mreg_est_inscripciones', $condx);
                            if ($libx && !empty($libx)) {
                                $retorno["rr"][$irr]["tipodocafectado2"] = $libx["tipodocumento"];
                                $retorno["rr"][$irr]["numdocafectado2"] = $libx["numerodocumento"];
                                $retorno["rr"][$irr]["fecdocafectado2"] = $libx["fechadocumento"];
                                $retorno["rr"][$irr]["fecharegistroafectado2"] = $libx["fecharegistro"];
                                $retorno["rr"][$irr]["noticiarecurrida2"] = $libx["noticia"];
                            }
                        }

                        if ($retorno["rr"][$irr]["libroafectado3"] != '' && $retorno["rr"][$irr]["registroafectado3"] != '' && $retorno["rr"][$irr]["dupliafectado3"] != '') {
                            $condx = "libro='" . $retorno["rr"][$irr]["libroafectado3"] . "' and registro='" . $retorno["rr"][$irr]["registroafectado3"] . "' and dupli='" . $retorno["rr"][$irr]["dupliafectado3"] . "'";
                            $libx = retornarRegistroMysqliApi($mysqli, 'mreg_est_inscripciones', $condx);
                            if ($libx && !empty($libx)) {
                                $retorno["rr"][$irr]["tipodocafectado3"] = $libx["tipodocumento"];
                                $retorno["rr"][$irr]["numdocafectado3"] = $libx["numerodocumento"];
                                $retorno["rr"][$irr]["fecdocafectado3"] = $libx["fechadocumento"];
                                $retorno["rr"][$irr]["fecharegistroafectado3"] = $libx["fecharegistro"];
                                $retorno["rr"][$irr]["noticiarecurrida3"] = $libx["noticia"];
                            }
                        }

                        if ($retorno["rr"][$irr]["libroafectado4"] != '' && $retorno["rr"][$irr]["registroafectado4"] != '' && $retorno["rr"][$irr]["dupliafectado4"] != '') {
                            $condx = "libro='" . $retorno["rr"][$irr]["libroafectado4"] . "' and registro='" . $retorno["rr"][$irr]["registroafectado4"] . "' and dupli='" . $retorno["rr"][$irr]["dupliafectado4"] . "'";
                            $libx = retornarRegistroMysqliApi($mysqli, 'mreg_est_inscripciones', $condx);
                            if ($libx && !empty($libx)) {
                                $retorno["rr"][$irr]["tipodocafectado4"] = $libx["tipodocumento"];
                                $retorno["rr"][$irr]["numdocafectado4"] = $libx["numerodocumento"];
                                $retorno["rr"][$irr]["fecdocafectado4"] = $libx["fechadocumento"];
                                $retorno["rr"][$irr]["fecharegistroafectado4"] = $libx["fecharegistro"];
                                $retorno["rr"][$irr]["noticiarecurrida4"] = $libx["noticia"];
                            }
                        }
                    }
                }
            }
        }
        if ($genlog == 'si') {
            \logApi::general2($nameLog, $retorno["matricula"], 'Leyo de códigos de barras pendientes');
        }

        // \logApi::general2($nameLog, $retorno["matricula"], 'Salió de codbarras pendientes : ' . date("His"));
        // Relación de inscripciones
        // \logApi::general2($nameLog, $retorno["matricula"], 'Entró a inscripciones : ' . date("His"));
        $retorno["inscripciones"] = array();
        $retorno["inscripcioneslibros"] = array();
        $ix = 0;
        $ixl = 0;

        //
        $enliquidacion = 'no';
        $enreestructuracion = 'no';
        $enreorganizacion = 'no';
        $enliquidacionjudicial = 'no';
        $enliquidacionforsoza = 'no';
        $enrecuperacion = 'no';

        //
        $arrX = retornarRegistrosMysqliApi($mysqli, 'mreg_est_inscripciones', "matricula='" . ltrim(trim($retorno["matricula"]), "0") . "'", "fecharegistro,horaregistro,libro,registro,dupli");
        if ($arrX && !empty($arrX)) {
            foreach ($arrX as $x) {
                $ix++;
                // 2017-11-08: JINT: Se incluye control de revocación solo a partir del 2017
                // dado que en SIREP este no está bien manejado y pone problemas con
                // inscripciones antiguas
                if ($x["fecharegistro"] < '20170101' && $x["ctrrevoca"] == '1') {
                    $x["ctrrevoca"] = '0';
                }

                // if ($x["ctrrevoca"] != '1') {
                $retorno["inscripciones"][$ix] = array();
                $retorno["inscripciones"][$ix]["lib"] = $x["libro"];
                $retorno["inscripciones"][$ix]["nreg"] = $x["registro"];
                $retorno["inscripciones"][$ix]["dupli"] = $x["dupli"];
                $retorno["inscripciones"][$ix]["freg"] = $x["fecharegistro"];
                $retorno["inscripciones"][$ix]["hreg"] = $x["horaregistro"];
                $retorno["inscripciones"][$ix]["frad"] = $x["fecharadicacion"];
                $retorno["inscripciones"][$ix]["cb"] = $x["idradicacion"];
                $retorno["inscripciones"][$ix]["rec"] = $x["recibo"];
                $retorno["inscripciones"][$ix]["nope"] = $x["numerooperacion"];
                $retorno["inscripciones"][$ix]["ope"] = $x["operador"];
                $retorno["inscripciones"][$ix]["acto"] = $x["acto"];
                $retorno["inscripciones"][$ix]["nacto"] = retornarRegistroMysqliAPi($mysqli, 'mreg_actos', "idlibro='" . $x["libro"] . "' and idacto='" . $x["acto"] . "'", "nombre");
                $retorno["inscripciones"][$ix]["idclase"] = $x["tipoidentificacion"];
                $retorno["inscripciones"][$ix]["numid"] = $x["identificacion"];
                $retorno["inscripciones"][$ix]["nombre"] = $x["nombre"];
                $retorno["inscripciones"][$ix]["ndocext"] = $x["numdocextenso"];
                $retorno["inscripciones"][$ix]["ndoc"] = $x["numerodocumento"];
                $retorno["inscripciones"][$ix]["tdoc"] = $x["tipodocumento"];
                $retorno["inscripciones"][$ix]["fdoc"] = $x["fechadocumento"];
                $retorno["inscripciones"][$ix]["idoridoc"] = $x["idorigendoc"];
                $retorno["inscripciones"][$ix]["txoridoc"] = $x["origendocumento"];
                $retorno["inscripciones"][$ix]["idmunidoc"] = $x["municipiodocumento"];
                $retorno["inscripciones"][$ix]["idpaidoc"] = $x["paisdocumento"];
                $retorno["inscripciones"][$ix]["tipolibro"] = $x["tipolibro"];
                $retorno["inscripciones"][$ix]["idlibvii"] = $x["idcodlibro"];
                $retorno["inscripciones"][$ix]["codlibcom"] = $x["codigolibro"];
                $retorno["inscripciones"][$ix]["deslib"] = $x["descripcionlibro"];
                $retorno["inscripciones"][$ix]["paginainicial"] = $x["paginainicial"];
                $retorno["inscripciones"][$ix]["numhojas"] = $x["numeropaginas"];
                $retorno["inscripciones"][$ix]["not"] = $x["noticia"];
                $notx1 = retornarRegistroMysqliApi($mysqli, 'mreg_est_inscripciones_campos', "libro='" . $x["libro"] . "' and registro='" . $x["registro"] . "' and dupli='" . $x["dupli"] . "' and campo='noticiabase64'", "contenido");
                if ($notx1 != '') {
                    $retorno["inscripciones"][$ix]["not"] = base64_decode($notx1);
                }
                $retorno["inscripciones"][$ix]["aclaratoria"] = '';
                $retorno["inscripciones"][$ix]["txtapoderados"] = '';
                $retorno["inscripciones"][$ix]["txtpoder"] = '';
                if (isset($x["aclaratoria"])) {
                    $retorno["inscripciones"][$ix]["aclaratoria"] = $x["aclaratoria"];
                }
                if (isset($x["txtapoderados"])) {
                    $retorno["inscripciones"][$ix]["txtapoderados"] = $x["txtapoderados"];
                }
                if (isset($x["txtpoder"])) {
                    $retorno["inscripciones"][$ix]["txtpoder"] = $x["txtpoder"];
                }

                $retorno["inscripciones"][$ix]["not2"] = '';
                $retorno["inscripciones"][$ix]["not3"] = '';
                $retorno["inscripciones"][$ix]["crecu"] = $x["ctrrecurso"];
                $retorno["inscripciones"][$ix]["cimg"] = '';
                $retorno["inscripciones"][$ix]["cver"] = '';
                $retorno["inscripciones"][$ix]["crot"] = $x["ctrrotulo"];
                $retorno["inscripciones"][$ix]["crev"] = $x["ctrrevoca"];
                $retorno["inscripciones"][$ix]["regrev"] = $x["registrorevocacion"];
                $retorno["inscripciones"][$ix]["cnat"] = '';
                $retorno["inscripciones"][$ix]["fir"] = $x["firma"];
                $retorno["inscripciones"][$ix]["cfir"] = $x["clavefirmado"];
                $retorno["inscripciones"][$ix]["uins"] = $x["usuarioinscribe"];
                $retorno["inscripciones"][$ix]["ufir"] = $x["usuariofirma"];
                $retorno["inscripciones"][$ix]["tnotemail"] = $x["timestampnotificacionemail"];
                $retorno["inscripciones"][$ix]["tnotsms"] = $x["timestampnotificacionsms"];
                $retorno["inscripciones"][$ix]["tnotemail"] = $x["idnotificacionemail"];
                $retorno["inscripciones"][$ix]["tnotsms"] = $x["idnotificacionsms"];
                $retorno["inscripciones"][$ix]["ipubrue"] = $x["idpublicacionrue"];
                $retorno["inscripciones"][$ix]["fpubrue"] = $x["fecpublicacionrue"];
                $retorno["inscripciones"][$ix]["flim"] = $x["fechalimite"];

                $retorno["inscripciones"][$ix]["camant"] = $x["camaraanterior"];
                $retorno["inscripciones"][$ix]["libant"] = $x["libroanterior"];
                $retorno["inscripciones"][$ix]["regant"] = $x["registroanterior"];
                $retorno["inscripciones"][$ix]["fecant"] = $x["fecharegistroanterior"];

                $retorno["inscripciones"][$ix]["camant2"] = $x["camaraanterior2"];
                $retorno["inscripciones"][$ix]["libant2"] = $x["libroanterior2"];
                $retorno["inscripciones"][$ix]["regant2"] = $x["registroanterior2"];
                $retorno["inscripciones"][$ix]["fecant2"] = $x["fecharegistroanterior2"];

                $retorno["inscripciones"][$ix]["camant3"] = $x["camaraanterior3"];
                $retorno["inscripciones"][$ix]["libant3"] = $x["libroanterior3"];
                $retorno["inscripciones"][$ix]["regant3"] = $x["registroanterior3"];
                $retorno["inscripciones"][$ix]["fecant3"] = $x["fecharegistroanterior3"];

                $retorno["inscripciones"][$ix]["camant4"] = $x["camaraanterior4"];
                $retorno["inscripciones"][$ix]["libant4"] = $x["libroanterior4"];
                $retorno["inscripciones"][$ix]["regant4"] = $x["registroanterior4"];
                $retorno["inscripciones"][$ix]["fecant4"] = $x["fecharegistroanterior4"];

                $retorno["inscripciones"][$ix]["camant5"] = $x["camaraanterior5"];
                $retorno["inscripciones"][$ix]["libant5"] = $x["libroanterior5"];
                $retorno["inscripciones"][$ix]["regant5"] = $x["registroanterior5"];
                $retorno["inscripciones"][$ix]["fecant5"] = $x["fecharegistroanterior5"];

                $retorno["inscripciones"][$ix]["tomo72"] = '';
                $retorno["inscripciones"][$ix]["folio72"] = '';
                $retorno["inscripciones"][$ix]["registro72"] = '';

                $retorno["inscripciones"][$ix]["asa"] = $x["actosistemaanterior"];

                if (isset($x["tomo72"])) {
                    $retorno["inscripciones"][$ix]["tomo72"] = $x["tomo72"];
                    $retorno["inscripciones"][$ix]["folio72"] = $x["folio72"];
                    $retorno["inscripciones"][$ix]["registro72"] = $x["registro72"];
                }

                // ************************************************************* //
                // Inscripciones campos
                // ************************************************************* //
                $retorno["inscripciones"][$ix]["anadirrazonsocial"] = retornarRegistroMysqliApi($mysqli, 'mreg_est_inscripciones_campos', "libro='" . $x["libro"] . "' and registro='" . $x["registro"] . "' and dupli='" . $x["dupli"] . "' and campo='anadirrazonsocial'", "contenido");
                $retorno["inscripciones"][$ix]["extinciondominio"] = retornarRegistroMysqliApi($mysqli, 'mreg_est_inscripciones_campos', "libro='" . $x["libro"] . "' and registro='" . $x["registro"] . "' and dupli='" . $x["dupli"] . "' and campo='extinciondominio'", "contenido");
                $retorno["inscripciones"][$ix]["extinciondominiocondicion"] = retornarRegistroMysqliApi($mysqli, 'mreg_est_inscripciones_campos', "libro='" . $x["libro"] . "' and registro='" . $x["registro"] . "' and dupli='" . $x["dupli"] . "' and campo='extinciondominiocondicion'", "contenido");

                //
                $ind = $x["libro"] . '-' . $x["acto"];

                $retorno["inscripciones"][$ix]["est"] = $x["estado"];

                if ($retorno["inscripciones"][$ix]["not"] == '') {
                    if (
                            $_SESSION["generales"]["codigoempresa"] == '06' ||
                            $_SESSION["generales"]["codigoempresa"] == '07' ||
                            $_SESSION["generales"]["codigoempresa"] == '17' ||
                            $_SESSION["generales"]["codigoempresa"] == '24' ||
                            $_SESSION["generales"]["codigoempresa"] == '26' ||
                            $_SESSION["generales"]["codigoempresa"] == '36' ||
                            $_SESSION["generales"]["codigoempresa"] == '37' ||
                            $_SESSION["generales"]["codigoempresa"] == '43' ||
                            $_SESSION["generales"]["codigoempresa"] == '46'
                    ) {
                        $retorno["inscripciones"][$ix]["not"] = \funcionesGenerales::construirNoticiaSurOccidente($mysqli, $x);
                    }
                    if ($retorno["inscripciones"][$ix]["not"] == '') {
                        if ($x["acto"] != '') {
                            if (isset($_SESSION["maestroactos"][$ind])) {
                                $retorno["inscripciones"][$ix]["not"] = $_SESSION["maestroactos"][$ind]["nombre"];
                            } else {
                                $retorno["inscripciones"][$ix]["not"] = '';
                            }
                        }
                    }
                }

                //
                $retorno["inscripciones"][$ix]["nombreacto"] = '';
                $retorno["inscripciones"][$ix]["grupoacto"] = '';
                $retorno["inscripciones"][$ix]["esreforma"] = '';
                $retorno["inscripciones"][$ix]["esreformaespecial"] = '';
                $retorno["inscripciones"][$ix]["anotacionalcapital"] = '';
                $retorno["inscripciones"][$ix]["controldisolucion"] = '';
                $retorno["inscripciones"][$ix]["textoenliquidacion"] = '';
                $retorno["inscripciones"][$ix]["actosistemaanterior"] = $x["actosistemaanterior"];
                $retorno["inscripciones"][$ix]["vinculoafectado"] = $x["vinculoafectado"];
                $retorno["inscripciones"][$ix]["tipoidentificacionafectada"] = $x["tipoidentificacionafectada"];
                $retorno["inscripciones"][$ix]["identificacionafectada"] = $x["identificacionafectada"];
                $retorno["inscripciones"][$ix]["fechalimite"] = $x["fechalimite"];
                if (trim(ltrim((string) $x["idradicacion"], "0")) != '') {
                    $retorno["inscripciones"][$ix]["idradicacion"] = $x["idradicacion"];
                } else {
                    $cbx = retornarRegistrosMysqliApi($mysqli, 'mreg_est_codigosbarras_libros', "libro='" . $x["libro"] . "' and registro='" . $x["registro"] . "'", "id");
                    if ($cbx && !empty($cbx)) {
                        foreach ($cbx as $cbx1) {
                            $retorno["inscripciones"][$ix]["idradicacion"] = $cbx1["codigobarras"];
                        }
                    }
                    unset($cbx);
                }

                //
                if ($x["tipodocumento"] == '03' && $x["acto"] == '0510' && strpos($retorno["inscripciones"][$ix]["not"], "DEPURACION")) {
                    $retorno["inscripciones"][$ix]["tdoc"] = '38';
                }

                //
                if ($x["tipodocumento"] == '03' && ($x["acto"] == '0530' || $x["acto"] == '0540') && strpos($retorno["inscripciones"][$ix]["not"], "DEPURACION")) {
                    $retorno["inscripciones"][$ix]["tdoc"] = '38';
                }


                if (isset($_SESSION["maestroactos"][$ind])) {
                    $retorno["inscripciones"][$ix]["nombreacto"] = $_SESSION["maestroactos"][$ind]["nombre"];
                    $retorno["inscripciones"][$ix]["grupoacto"] = $_SESSION["maestroactos"][$ind]["idgrupoacto"];
                    $retorno["inscripciones"][$ix]["esreforma"] = $_SESSION["maestroactos"][$ind]["controlreforma"];
                    $retorno["inscripciones"][$ix]["esreformaespecial"] = $_SESSION["maestroactos"][$ind]["controlreformaespecial"];
                    $retorno["inscripciones"][$ix]["anotacionalcapital"] = $_SESSION["maestroactos"][$ind]["controlanotacionalcapital"];
                    $retorno["inscripciones"][$ix]["controldisolucion"] = $_SESSION["maestroactos"][$ind]["controldisolucion"];
                    $retorno["inscripciones"][$ix]["textoenliquidacion"] = $_SESSION["maestroactos"][$ind]["textoenliquidacion"];

                    // Perdida de calidad de comerciante
                    if ($_SESSION["maestroactos"][$ind]["idgrupoacto"] == '071') {
                        $retorno["fechaperdidacalidadcomerciante"] = $x["fecharegistro"];
                        $retorno["perdidacalidadcomerciante"] = 'si';
                        if (ltrim(trim($x["fecharadicacion"]), "0") != '') {
                            $retorno["fechaperdidacalidadcomerciante"] = $x["fecharadicacion"];
                        } else {
                            $retorno["fechaperdidacalidadcomerciante"] = $x["fecharegistro"];
                        }
                    }

                    // Reactivación como comerciante 
                    if ($_SESSION["maestroactos"][$ind]["idgrupoacto"] == '073') {
                        // $retorno["fechaperdidacalidadcomerciante"] = '';
                        // $retorno["fechaperdidacalidadcomerciante"] = '';
                        $retorno["perdidacalidadcomerciante"] = 'no';
                        if (ltrim(trim($x["fecharadicacion"]), "0") != '') {
                            $retorno["fechareactivacioncalidadcomerciante"] = $x["fecharadicacion"];
                        } else {
                            $retorno["fechareactivacioncalidadcomerciante"] = $x["fecharegistro"];
                        }
                    }

                    // Disolucion
                    if (
                            ($_SESSION["maestroactos"][$ind]["idgrupoacto"] == '009') ||
                            ($_SESSION["maestroactos"][$ind]["controldisolucion"] == 'S') ||
                            ($_SESSION["maestroactos"][$ind]["textoenliquidacion"] == 'S') ||
                            ($_SESSION["maestroactos"][$ind]["textoenliquidacion"] == 'L')
                    ) {
                        $retorno["reactivadaacto511"] = '';
                        $retorno["fechaacto511"] = '';
                        $retorno["fechadisolucion"] = $x["fecharegistro"];
                        $retorno["disueltaporacto510"] = 'si';
                        if ($x["fecharegistroanterior"] != '') {
                            $retorno["fechaacto510"] = $x["fecharegistroanterior"];
                        } else {
                            if (ltrim(trim($x["fecharadicacion"]), "0") != '') {
                                $retorno["fechaacto510"] = $x["fecharadicacion"];
                            } else {
                                $retorno["fechaacto510"] = $x["fecharegistro"];
                            }
                        }
                        $retorno["fechadisolucioncontrolbeneficios1756"] = $retorno["fechaacto510"];
                    }

                    // Reactivacion 
                    if (
                            ($_SESSION["maestroactos"][$ind]["idgrupoacto"] == '011') ||
                            ($_SESSION["maestroactos"][$ind]["controldisolucion"] == 'R') ||
                            ($_SESSION["maestroactos"][$ind]["textoenliquidacion"] == 'F') ||
                            ($_SESSION["maestroactos"][$ind]["textoenliquidacion"] == 'Q')
                    ) {
                        $retorno["fechadisolucion"] = '';
                        $retorno["disueltaporacto510"] = '';
                        $retorno["fechaacto510"] = '';
                        $retorno["reactivadaacto511"] = 'si';
                        $retorno["fechaacto511"] = $x["fecharegistro"];
                        $retorno["fechareactivacioncontrolbeneficios1756"] = $retorno["fechaacto511"];
                    }

                    // Constitucion      
                    if ($_SESSION["maestroactos"][$ind]["idgrupoacto"] == '005') {
                        $retorno["fechaconstitucion"] = $x["fecharegistro"];
                        if ($x["fecharegistroanterior"] != '') {
                            $retorno["fechaconstitucion"] = $x["fecharegistroanterior"];
                        }
                    }

                    // Cancelacion            
                    if ($retorno["estadomatricula"] == 'MC' || $retorno["estadomatricula"] == 'MF' || $retorno["estadomatricula"] == 'MG') {
                        if ($_SESSION["maestroactos"][$ind]["idgrupoacto"] == '002') {
                            $retorno["fechacancelacion"] = $x["fecharegistro"];
                        }
                    }

                    // liquidacion            
                    if ($_SESSION["maestroactos"][$ind]["idgrupoacto"] == '010') {
                        $retorno["fechaliquidacion"] = $x["fecharegistro"];
                    }

                    //
                    if ($_SESSION["maestroactos"][$ind]["textoenliquidacion"] == 'S') {
                        if (!isset($retorno["inscripciones"][$ix]["anadirrazonsocial"]) || $retorno["inscripciones"][$ix]["anadirrazonsocial"] != 'NO') {
                            $enliquidacion = 'si';
                            // $enreestructuracion = 'no';
                            // $enreorganizacion = 'no';
                            $enliquidacionjudicial = 'no';
                            $enliquidacionforsoza = 'no';
                            // $enrecuperacion = 'no';
                        }
                    }
                    if ($_SESSION["maestroactos"][$ind]["textoenliquidacion"] == 'L') {
                        if (!isset($retorno["inscripciones"][$ix]["anadirrazonsocial"]) || $retorno["inscripciones"][$ix]["anadirrazonsocial"] != 'NO') {
                            $enliquidacionjudicial = 'si';
                            $enliquidacion = 'no';
                            // $enreorganizacion = 'no';
                            // $enreestructuracion = 'no';
                            $enliquidacionforsoza = 'no';
                            // $enrecuperacion = 'no';
                        }
                    }
                    if ($_SESSION["maestroactos"][$ind]["textoenliquidacion"] == 'F') {
                        if (!isset($retorno["inscripciones"][$ix]["anadirrazonsocial"]) || $retorno["inscripciones"][$ix]["anadirrazonsocial"] != 'NO') {
                            $enliquidacionforsoza = 'si';
                            $enliquidacion = 'no';
                            // $enreorganizacion = 'no';
                            $enliquidacionjudicial = 'no';
                            // $enreestructuracion = 'no';
                            // $enrecuperacion = 'no';
                        }
                    }

                    if ($_SESSION["maestroactos"][$ind]["textoenliquidacion"] == 'Q') {
                        if (!isset($retorno["inscripciones"][$ix]["anadirrazonsocial"]) || $retorno["inscripciones"][$ix]["anadirrazonsocial"] != 'NO') {
                            $enliquidacion = 'no';
                            $enliquidacionjudicial = 'no';
                            // $enreestructuracion = 'no';
                            // $enreorganizacion = 'no';
                            $enliquidacionforsoza = 'no';
                            // $enrecuperacion = 'no';
                        }
                    }

                    if ($_SESSION["maestroactos"][$ind]["textoenreestructuracion"] == 'S') {
                        if (!isset($retorno["inscripciones"][$ix]["anadirrazonsocial"]) || $retorno["inscripciones"][$ix]["anadirrazonsocial"] != 'NO') {
                            // $enliquidacion = 'no';
                            // $enliquidacionjudicial = 'no';
                            $enreestructuracion = 'si';
                            $enreorganizacion = 'no';
                            $enrecuperacion = 'no';
                        }
                    }
                    if ($_SESSION["maestroactos"][$ind]["textoenreestructuracion"] == 'L') {
                        if (!isset($retorno["inscripciones"][$ix]["anadirrazonsocial"]) || $retorno["inscripciones"][$ix]["anadirrazonsocial"] != 'NO') {
                            $enliquidacion = 'no';
                            $enliquidacionjudicial = 'si';
                            $enreestructuracion = 'no';
                            $enreorganizacion = 'no';
                            $enrecuperacion = 'no';
                        }
                    }
                    if ($_SESSION["maestroactos"][$ind]["textoenreestructuracion"] == 'R') {
                        if (!isset($retorno["inscripciones"][$ix]["anadirrazonsocial"]) || $retorno["inscripciones"][$ix]["anadirrazonsocial"] != 'NO') {
                            $enliquidacion = 'no';
                            $enliquidacionjudicial = 'no';
                            $enreestructuracion = 'no';
                            $enreorganizacion = 'si';
                            $enrecuperacion = 'no';
                        }
                    }
                    if ($_SESSION["maestroactos"][$ind]["textoenreestructuracion"] == 'E') {
                        if (!isset($retorno["inscripciones"][$ix]["anadirrazonsocial"]) || $retorno["inscripciones"][$ix]["anadirrazonsocial"] != 'NO') {
                            $enliquidacion = 'no';
                            $enliquidacionjudicial = 'no';
                            $enreestructuracion = 'no';
                            $enreorganizacion = 'no';
                            $enrecuperacion = 'si';
                        }
                    }

                    if ($_SESSION["maestroactos"][$ind]["textoenreestructuracion"] == 'Q') {
                        if (!isset($retorno["inscripciones"][$ix]["anadirrazonsocial"]) || $retorno["inscripciones"][$ix]["anadirrazonsocial"] != 'NO') {
                            $enliquidacion = 'no';
                            $enliquidacionjudicial = 'no';
                            $enreestructuracion = 'no';
                            $enreorganizacion = 'no';
                            $enrecuperacion = 'no';
                        }
                    }
                }

                // Libros de comercio
                $eslib = 'no';
                if ($x["libro"] == 'RM07' || $x["libro"] == 'RE52') {
                    if (ltrim(trim($x["acto"]), "0") == '') {
                        $eslib = 'si';
                    } else {
                        if (
                                $_SESSION["maestroactos"][$ind]["idgrupoacto"] == '004' ||
                                $_SESSION["maestroactos"][$ind]["idgrupoacto"] == '085'
                        ) {
                            $eslib = 'si';
                        }
                    }
                }

                //
                if ($x["libro"] == 'RM22' || $x["libro"] == 'RE53') {
                    if (trim($x["acto"]) == '0003' || trim($x["acto"]) == '0004') {
                        if (
                                $_SESSION["maestroactos"][$ind]["idgrupoacto"] == '004' ||
                                $_SESSION["maestroactos"][$ind]["idgrupoacto"] == '085'
                        ) {
                            $eslib = 'si';
                        }
                    }
                }

                //
                if ($eslib == 'si') {
                    $ixl++;
                    $retorno["inscripcioneslibros"][$ixl] = array();
                    $retorno["inscripcioneslibros"][$ixl]["lib"] = $x["libro"];
                    $retorno["inscripcioneslibros"][$ixl]["nreg"] = $x["registro"];
                    $retorno["inscripcioneslibros"][$ixl]["dupli"] = $x["dupli"];
                    $retorno["inscripcioneslibros"][$ixl]["acto"] = $x["acto"];
                    $retorno["inscripcioneslibros"][$ixl]["freg"] = $x["fecharegistro"];
                    $retorno["inscripcioneslibros"][$ixl]["hreg"] = $x["horaregistro"];
                    $retorno["inscripcioneslibros"][$ixl]["tipolibro"] = $x["tipolibro"];
                    $retorno["inscripcioneslibros"][$ixl]["idlibvii"] = $x["idcodlibro"];
                    $retorno["inscripcioneslibros"][$ixl]["codlibcom"] = $x["codigolibro"];
                    $retorno["inscripcioneslibros"][$ixl]["deslib"] = $x["descripcionlibro"];
                    $retorno["inscripcioneslibros"][$ixl]["paginainicial"] = $x["paginainicial"];
                    $retorno["inscripcioneslibros"][$ixl]["numhojas"] = $x["numeropaginas"];
                    $retorno["inscripcioneslibros"][$ixl]["not"] = $x["noticia"];
                    $retorno["inscripcioneslibros"][$ixl]["camant"] = $x["camaraanterior"];
                    $retorno["inscripcioneslibros"][$ixl]["libant"] = $x["libroanterior"];
                    $retorno["inscripcioneslibros"][$ixl]["regant"] = $x["registroanterior"];
                    $retorno["inscripcioneslibros"][$ixl]["fecant"] = $x["fecharegistroanterior"];

                    $retorno["inscripcioneslibros"][$ixl]["camant2"] = $x["camaraanterior2"];
                    $retorno["inscripcioneslibros"][$ixl]["libant2"] = $x["libroanterior2"];
                    $retorno["inscripcioneslibros"][$ixl]["regant2"] = $x["registroanterior2"];
                    $retorno["inscripcioneslibros"][$ixl]["fecant2"] = $x["fecharegistroanterior2"];

                    $retorno["inscripcioneslibros"][$ixl]["camant3"] = $x["camaraanterior3"];
                    $retorno["inscripcioneslibros"][$ixl]["libant3"] = $x["libroanterior3"];
                    $retorno["inscripcioneslibros"][$ixl]["regant3"] = $x["registroanterior3"];
                    $retorno["inscripcioneslibros"][$ixl]["fecant3"] = $x["fecharegistroanterior3"];

                    $retorno["inscripcioneslibros"][$ixl]["camant4"] = $x["camaraanterior4"];
                    $retorno["inscripcioneslibros"][$ixl]["libant4"] = $x["libroanterior4"];
                    $retorno["inscripcioneslibros"][$ixl]["regant4"] = $x["registroanterior4"];
                    $retorno["inscripcioneslibros"][$ixl]["fecant4"] = $x["fecharegistroanterior4"];

                    $retorno["inscripcioneslibros"][$ixl]["camant5"] = $x["camaraanterior5"];
                    $retorno["inscripcioneslibros"][$ixl]["libant5"] = $x["libroanterior5"];
                    $retorno["inscripcioneslibros"][$ixl]["regant5"] = $x["registroanterior5"];
                    $retorno["inscripcioneslibros"][$ixl]["fecant5"] = $x["fecharegistroanterior5"];

                    $retorno["inscripcioneslibros"][$ixl]["est"] = $x["estado"];
                    if ($retorno["inscripcioneslibros"][$ixl]["not"] == '') {
                        $retorno["inscripcioneslibros"][$ixl]["not"] = $_SESSION["maestroactos"][$x["libro"] . '-' . '0003']["nombre"];
                    }
                }

                if ($retorno["inscripciones"][$ix]["grupoacto"] == '005') {
                    $retorno["datconst_fecdoc"] = $x["fechadocumento"];
                    $retorno["datconst_tipdoc"] = $x["tipodocumento"];
                    $retorno["datconst_numdoc"] = $x["numerodocumento"];
                    $retorno["datconst_oridoc"] = $x["origendocumento"];
                    $retorno["datconst_mundoc"] = $x["municipiodocumento"];
                }
            }
        }

        //
        if ($genlog == 'si') {
            \logApi::general2($nameLog, $retorno["matricula"], 'Leyo inscripciones en libros');
        }

        // 2017-08-21: JINT: Pone automáticamente la palabra EN LIQUIDACION y EN RESTRUCTURACION
        // Bien sea porque se vencieron los términos de duracion (Vigencia) o porque
        // encontro algún acto que indique que se debe colocar o no dicha palabra
        // Aplica solo para personas jurídica sprincipales
        // 2017-11-18: si debe adicionar palabras a la razón social, revisa primero
        // si existe contenido en el campo "complentorazonsocial"

        if ($retorno["organizacion"] == '01') {
            if ($retorno["ape1"] != '' && $retorno["nom1"] != '') {
                $retorno["nombrerues"] = $retorno["ape1"];
                if (trim((string) $retorno["ape2"]) != '') {
                    $retorno["nombrerues"] .= ' ' . $retorno["ape2"];
                }
                if (trim((string) $retorno["nom1"]) != '') {
                    $retorno["nombrerues"] .= ' ' . $retorno["nom1"];
                }
                if (trim((string) $retorno["nom2"]) != '') {
                    $retorno["nombrerues"] .= ' ' . $retorno["nom2"];
                }

                $retorno["nombre"] = $retorno["nom1"];
                if (trim((string) $retorno["nom2"]) != '') {
                    $retorno["nombre"] .= ' ' . $retorno["nom2"];
                }
                if (trim((string) $retorno["ape1"]) != '') {
                    $retorno["nombre"] .= ' ' . $retorno["ape1"];
                }
                if (trim((string) $retorno["ape2"]) != '') {
                    $retorno["nombre"] .= ' ' . $retorno["ape2"];
                }
            }
        }

        //
        if ($retorno["estadomatricula"] != 'MC' && $retorno["estadomatricula"] != 'IC') {
            if ($retorno["organizacion"] == '01' || ($retorno["organizacion"] > '02' && $retorno["categoria"] == '1')) {
                $ya = 'no';
                if (
                        $enliquidacion == 'si' ||
                        $enreestructuracion == 'si' ||
                        $enreorganizacion == 'si' ||
                        $enliquidacionjudicial == 'si' ||
                        $enliquidacionforsoza == 'si' ||
                        $enrecuperacion == 'si'
                ) {
                    if ($retorno["complementorazonsocial"] != '') {
                        $retorno["nombre"] .= ' ' . $retorno["complementorazonsocial"];
                        $retorno["nombrerues"] .= ' ' . $retorno["complementorazonsocial"];
                        $ya = 'si';
                    }
                }
                if ($ya == 'no') {
                    if ($enliquidacion == 'si') {
                        $retorno["nombre"] .= ' EN LIQUIDACION';
                        $retorno["nombrerues"] .= ' EN LIQUIDACION';
                        $ya = 'si';
                    }
                    if ($enreestructuracion == 'si') {
                        $retorno["nombre"] .= ' EN REESTRUCTURACION';
                        $retorno["nombrerues"] .= ' EN REESTRUCTURACION';
                        $ya = 'si';
                    }
                    if ($enreorganizacion == 'si') {
                        $retorno["nombre"] .= ' EN REORGANIZACION';
                        $retorno["nombrerues"] .= ' EN REORGANIZACION';
                        $ya = 'si';
                    }
                    if ($enliquidacionjudicial == 'si') {
                        $retorno["nombre"] .= ' EN LIQUIDACION JUDICIAL';
                        $retorno["nombrerues"] .= ' EN LIQUIDACION JUDICIAL';
                        $ya = 'si';
                    }
                    if ($enliquidacionforsoza == 'si') {
                        $retorno["nombre"] .= ' EN LIQUIDACION FORZOSA';
                        $retorno["nombrerues"] .= ' EN LIQUIDACION FORZOSA';
                        $ya = 'si';
                    }
                    if ($enrecuperacion == 'si') {
                        $retorno["nombre"] .= ' EN RECUPERACION EMPRESARIAL';
                        $retorno["nombrerues"] .= ' EN RECUPERACION EMPRESARIAL';
                        $ya = 'si';
                    }
                }
                if ($ya == 'no') {
                    if (trim($retorno["fechavencimiento"]) != '' && $retorno["fechavencimiento"] != '99999999') {
                        if (trim($retorno["fechavencimiento"]) < date("Ymd")) {
                            $retorno["nombre"] .= ' EN LIQUIDACION';
                            $retorno["nombrerues"] .= ' EN LIQUIDACION';
                            $ya = 'si';
                        }
                    }
                }
            }
            if ($retorno["organizacion"] > '02' && ($retorno["categoria"] == '2' || $retorno["categoria"] == '3')) {
                if ($retorno["complementorazonsocial"] != '') {
                    $retorno["nombre"] .= ' ' . $retorno["complementorazonsocial"];
                    $retorno["nombrerues"] .= ' ' . $retorno["complementorazonsocial"];
                    $ya = 'si';
                }
            }
        }

        // Relación de nombres anteriores
        // \logApi::general2($nameLog, $retorno["matricula"], 'Entró a nombres anteriores : ' . date("His"));
        $retorno["nomant"] = array();

        $arrX = retornarRegistrosMysqliApi($mysqli, 'mreg_est_nombresanteriores', "matricula='" . $retorno["matricula"] . "'", "secuencia");
        $ix = 0;
        if ($arrX && !empty($arrX)) {
            foreach ($arrX as $x) {
                if (existeTablaMysqliApi($mysqli, 'mreg_est_nombresanteriores_campos')) {
                    $nomb64 = retornarRegistroMysqliApi($mysqli, 'mreg_est_nombresanteriores_campos', "id_nombresanteriores=" . $x["id"] . " and campo='nombrebase64'", "contenido");
                    if ($nomb64 != '') {
                        $x["nombre"] = base64_decode($nomb64);
                    }
                }
                $ix++;
                $retorno["nomant"][$ix] = array();
                $retorno["nomant"][$ix]["id"] = $x["id"];
                $retorno["nomant"][$ix]["sec"] = $ix;
                $retorno["nomant"][$ix]["lib"] = $x["libro"];
                $retorno["nomant"][$ix]["nreg"] = $x["registro"];
                $retorno["nomant"][$ix]["dup"] = $x["dupli"];
                $retorno["nomant"][$ix]["freg"] = $x["fechareg"];
                $retorno["nomant"][$ix]["nom"] = $x["nombre"];
                $retorno["nomant"][$ix]["ope"] = $x["operador"];
                $retorno["nomant"][$ix]["fcre"] = $x["fechacreacion"];
            }
        }
        if ($genlog == 'si') {
            \logApi::general2($nameLog, $retorno["matricula"], 'Leyo nombres anteriores');
        }

        // Relación de capitales
        $retorno["capitales"] = array();
        $arrX = retornarRegistrosMysqliApi($mysqli, 'mreg_est_capitales', "matricula='" . ltrim(trim($retorno["matricula"]), "0") . "'", "anodatos,fechadatos");
        $icap = 0;
        if ($arrX && !empty($arrX)) {
            foreach ($arrX as $hcap) {
                $icap++;
                $retorno["capitales"][$icap]["anodatos"] = $hcap["anodatos"];
                $retorno["capitales"][$icap]["fechadatos"] = $hcap["fechadatos"];
                $retorno["capitales"][$icap]["libro"] = $hcap["libro"];
                $retorno["capitales"][$icap]["registro"] = $hcap["registro"];
                $retorno["capitales"][$icap]["tipoeconomia"] = $hcap["tipoeconomia"];
                $retorno["capitales"][$icap]["pornaltot"] = $hcap["pornaltot"];
                $retorno["capitales"][$icap]["pornalpri"] = $hcap["pornalpri"];
                $retorno["capitales"][$icap]["pornalpub"] = $hcap["pornalpub"];
                $retorno["capitales"][$icap]["porexttot"] = $hcap["porexttot"];
                $retorno["capitales"][$icap]["porextpri"] = $hcap["porextpri"];
                $retorno["capitales"][$icap]["porextpub"] = $hcap["porextpub"];
                $retorno["capitales"][$icap]["apoact"] = $hcap["aporteactivos"];
                $retorno["capitales"][$icap]["apodin"] = $hcap["aportedinero"];
                $retorno["capitales"][$icap]["apolab"] = $hcap["aportelaboral"];
                $retorno["capitales"][$icap]["apolabadi"] = $hcap["aportelaboraladi"];
                $retorno["capitales"][$icap]["suscrito"] = $hcap["valorsuscrito"];
                $retorno["capitales"][$icap]["autorizado"] = $hcap["valorautorizado"];
                $retorno["capitales"][$icap]["pagado"] = $hcap["valorpagado"];
                $retorno["capitales"][$icap]["social"] = $hcap["valorsocial"];
                $retorno["capitales"][$icap]["asigsuc"] = $hcap["capsucursal"];
                $retorno["capitales"][$icap]["cuosuscrito"] = $hcap["cuotassuscrito"];
                $retorno["capitales"][$icap]["cuoautorizado"] = $hcap["cuotasautorizado"];
                $retorno["capitales"][$icap]["cuopagado"] = $hcap["cuotaspagado"];
                $retorno["capitales"][$icap]["cuosocial"] = $hcap["cuotassocial"];

                $retorno["capitales"][$icap]["nomsuscrito"] = $hcap["nominalsuscrito"];
                $retorno["capitales"][$icap]["nomautorizado"] = $hcap["nominalautorizado"];
                $retorno["capitales"][$icap]["nompagado"] = $hcap["nominalpagado"];
                $retorno["capitales"][$icap]["nomsocial"] = $hcap["nominalsocial"];

                $retorno["capitales"][$icap]["moneda"] = $hcap["moneda"];

                // información de porcentajes de capital
                $retorno["cap_apolab"] = doubleval($hcap["aportelaboral"]);
                $retorno["cap_apolabadi"] = doubleval($hcap["aportelaboraladi"]);
                $retorno["cap_apoact"] = doubleval($hcap["aporteactivos"]);
                $retorno["cap_apodin"] = doubleval($hcap["aportedinero"]);

                $retorno["fecdatoscap"] = $hcap["fechadatos"];
                $retorno["capsoc"] = $hcap["valorsocial"];
                $retorno["capaut"] = $hcap["valorautorizado"];
                $retorno["capsus"] = $hcap["valorsuscrito"];
                $retorno["cappag"] = $hcap["valorpagado"];
                $retorno["cuosoc"] = $hcap["cuotassocial"];
                $retorno["cuoaut"] = $hcap["cuotasautorizado"];
                $retorno["cuosus"] = $hcap["cuotassuscrito"];
                $retorno["cuopag"] = $hcap["cuotaspagado"];

                $retorno["nomsoc"] = $hcap["nominalsocial"];
                $retorno["nomaut"] = $hcap["nominalautorizado"];
                $retorno["nomsus"] = $hcap["nominalsuscrito"];
                $retorno["nompag"] = $hcap["nominalpagado"];

                $retorno["capsuc"] = $hcap["capsucursal"];
                $retorno["monedacap"] = $hcap["moneda"];
            }
        }
        if ($genlog == 'si') {
            \logApi::general2($nameLog, $retorno["matricula"], 'Leyo capitales');
        }

        // Relación de capitales
        $retorno["patrimoniosesadl"] = array();
        $arrX = retornarRegistrosMysqliApi($mysqli, 'mreg_est_patrimonios', "matricula='" . $retorno["matricula"] . "'", "anodatos,fechadatos");
        $icap = 0;
        if ($arrX && !empty($arrX)) {
            foreach ($arrX as $hcap) {
                $icap++;
                $retorno["patrimoniosesadl"][$icap]["anodatos"] = $hcap["anodatos"];
                $retorno["patrimoniosesadl"][$icap]["fechadatos"] = $hcap["fechadatos"];
                $retorno["patrimoniosesadl"][$icap]["patrimonio"] = $hcap["patrimonio"];
            }
        }
        if ($genlog == 'si') {
            \logApi::general2($nameLog, $retorno["matricula"], 'Leyo patrimonios esadl');
        }

        // Información financiera histórica
        $retorno["hf"] = array();
        $ix = 0;
        $arrX = retornarRegistrosMysqliApi($mysqli, 'mreg_est_financiera', "matricula='" . $retorno["matricula"] . "'", "anodatos,fechadatos");
        if ($arrX && !empty($arrX)) {
            foreach ($arrX as $regf) {
                $ix++;
                $retorno["hf"][$ix]["anodatos"] = $regf["anodatos"];
                $retorno["hf"][$ix]["fechadatos"] = $regf["fechadatos"];
                $retorno["hf"][$ix]["personal"] = $regf["personal"];
                $retorno["hf"][$ix]["personaltemp"] = $regf["pcttemp"];
                $retorno["hf"][$ix]["actvin"] = $regf["actvin"];
                $retorno["hf"][$ix]["actcte"] = $regf["actcte"];
                $retorno["hf"][$ix]["actnocte"] = $regf["actnocte"];
                $retorno["hf"][$ix]["actfij"] = $regf["actfij"];
                $retorno["hf"][$ix]["fijnet"] = $regf["fijnet"];
                $retorno["hf"][$ix]["actotr"] = $regf["actotr"];
                $retorno["hf"][$ix]["actval"] = $regf["actval"];
                $retorno["hf"][$ix]["acttot"] = $regf["acttot"];
                $retorno["hf"][$ix]["actsinaju"] = 0;
                $retorno["hf"][$ix]["invent"] = 0;
                $retorno["hf"][$ix]["pascte"] = $regf["pascte"];
                $retorno["hf"][$ix]["paslar"] = $regf["paslar"];
                $retorno["hf"][$ix]["pastot"] = $regf["pastot"];
                $retorno["hf"][$ix]["pattot"] = $regf["patnet"];
                $retorno["hf"][$ix]["paspat"] = $regf["paspat"];
                $retorno["hf"][$ix]["balsoc"] = $regf["balsoc"];
                $retorno["hf"][$ix]["ingope"] = $regf["ingope"];
                $retorno["hf"][$ix]["ingnoope"] = $regf["ingnoope"];
                $retorno["hf"][$ix]["gtoven"] = $regf["gtoven"];
                $retorno["hf"][$ix]["gtoadm"] = $regf["gasadm"];
                $retorno["hf"][$ix]["cosven"] = $regf["cosven"];
                $retorno["hf"][$ix]["depamo"] = 0;
                $retorno["hf"][$ix]["gasint"] = $regf["gasint"];
                $retorno["hf"][$ix]["gasimp"] = $regf["gasimp"];
                $retorno["hf"][$ix]["utiope"] = $regf["utiope"];
                $retorno["hf"][$ix]["utinet"] = $regf["utinet"];
            }
        }
        unset($arrX);
        if ($genlog == 'si') {
            \logApi::general2($nameLog, $retorno["matricula"], 'Leyo histórico financiera');
        }

        // ************************************************************************************* //
        // Calcula tamaño empresarial
        // ************************************************************************************* //
        if (($retorno["organizacion"] == '01' || ($retorno["organizacion"] > '02' && $retorno["categoria"] == '1')) &&
                $retorno["claseespesadl"] != '61' &&
                $retorno["claseespesadl"] != '62'
        ) {
            $tex = retornarRegistrosMysqliApi($mysqli, 'mreg_tamano_empresarial', "matricula='" . $retorno["matricula"] . "'", 'anodatos,fechadatos');
            if ($tex && !empty($tex)) {
                foreach ($tex as $tex1) {
                    $retorno["ciiutamanoempresarial"] = $tex1["ciiu"];
                    $retorno["ingresostamanoempresarial"] = $tex1["ingresos"];
                    $retorno["anodatostamanoempresarial"] = $tex1["anodatos"];
                    $retorno["fechadatostamanoempresarial"] = $tex1["fechadatos"];
                }
            }
            unset($tex);
            unset($tex1);
            if ($genlog == 'si') {
                \logApi::general2($nameLog, $retorno["matricula"], 'Leyo mreg_tamano_empresarial');
            }

            // Determina el tamaño empresarial
            if ($retorno["ciiutamanoempresarial"] == '') {
                $retorno["ciiutamanoempresarial"] = $retorno["ciius"][1];
            }
            if ($retorno["ingresostamanoempresarial"] == '') {
                $retorno["ingresostamanoempresarial"] = $retorno["ingope"];
            }
            if ($retorno["anodatostamanoempresarial"] == '') {
                $retorno["anodatostamanoempresarial"] = $retorno["ultanoren"];
            }
            if ($retorno["fechadatostamanoempresarial"] == '') {
                $retorno["fechadatostamanoempresarial"] = $retorno["anodatos"];
            }
            $anomatricula = 'no';
            if ($retorno["fecharenovacion"] == $retorno["fechamatricula"]) {
                $anomatricula = 'si';
            }

            $tamemp = \funcionesGenerales::calcularTamanoEmpresarial($mysqli, $retorno["matricula"], 'actual', '', 0, 0, '', 0);
            $retorno["tamanoempresarial957"] = $tamemp["textocompleto"];
            $retorno["tamanoempresarial957uvts"] = $tamemp["ingresosuvt"];
            $retorno["tamanoempresarial957uvbs"] = $tamemp["ingresosuvb"];
            $retorno["tamanoempresarial957codigo"] = $tamemp["codigo"];
            $retorno["tamanoempresarialingresos"] = $tamemp["ingresos"];
            $retorno["tamanoempresarialactivos"] = $tamemp["activos"];
            $retorno["tamanoempresarialciiu"] = $tamemp["ciiu"];
            $retorno["tamanoempresarialpersonal"] = $tamemp["personal"];
            $retorno["tamanoempresarialfechadatos"] = $tamemp["fechadatos"];
            $retorno["tamanoempresarialanodatos"] = $tamemp["anodatos"];
            $retorno["tamanoempresarialformacalculo"] = $tamemp["forma"];
            $retorno["tamanoempresarialvaloruvt"] = $tamemp["uvt"];
            $retorno["tamanoempresarialvaloruvb"] = $tamemp["uvb"];
        }


        // Carga códigos CAE
        $retorno["codigoscae"] = array();
        $arrY = retornarRegistrosMysqliApi($mysqli, 'mreg_anexoscae', "1=1", "codigocae");
        if ($arrY && !empty($arrY)) {
            foreach ($arrY as $y) {
                $retorno["codigoscae"][$y["codigocae"]] = retornarRegistroMysqliApi($mysqli, 'mreg_est_campostablas', "tabla='200' and registro='" . $retorno["matricula"] . "' and campo='" . $y["codigocae"] . "'", "contenido");
            }
        }

        // Carga información adicional
        $retorno["informacionadicional"] = array();
        $arrY = retornarRegistrosMysqliApi($mysqli, 'mreg_campos_adicionales_camara', "1=1", "orden");
        if ($arrY && !empty($arrY)) {
            foreach ($arrY as $y) {
                $retorno["informacionadicional"][$y["codigoadicional"]] = retornarRegistroMysqliApi($mysqli, 'mreg_est_campostablas', "tabla='200' and registro='" . $retorno["matricula"] . "' and campo='" . $y["codigoadicional"] . "'", "contenido");
            }
        }
        if ($genlog == 'si') {
            \logApi::general2($nameLog, $retorno["matricula"], 'Leyo información CAE');
        }

        // Certificas que se importaron del SIREP
        $retorno["crt"] = array();
        $arrTemC = retornarRegistrosMysqliApi($mysqli, 'mreg_est_certificas', "matricula='" . ltrim($retorno["matricula"], "0") . "'", "idcertifica,id");
        if ($arrTemC && !empty($arrTemC)) {
            foreach ($arrTemC as $tx) {
                if (trim($tx["texto"]) != '') {
                    if (trim($tx["idcertifica"]) != '') {
                        if (!isset($retorno["crt"][trim($tx["idcertifica"])])) {
                            $retorno["crt"][trim($tx["idcertifica"])] = '';
                        }
                        $txt1 = \funcionesGenerales::reemplazarAcutes(\funcionesGenerales::restaurarEspeciales(trim(str_replace("&rdquo;", '"', $tx["texto"]))));
                        $txt1 = str_replace("||", CHR(13) . CHR(10) . CHR(13) . CHR(10), $txt1);
                        $txt1 = str_replace("|", " ", $txt1);
                        $retorno["crt"][$tx["idcertifica"]] .= $txt1;
                    }
                }
            }
        }
        unset($arrTemC);
        if ($genlog == 'si') {
            \logApi::general2($nameLog, $retorno["matricula"], 'Leyo certificas SIREP');
        }

        // Certificas que se han grabado en el SII
        $retorno["crtsii"] = array();
        $arrTemC = retornarRegistrosMysqliApi($mysqli, 'mreg_certificas_sii', "registro='REGMER' and expediente='" . ltrim($retorno["matricula"], "0") . "'", "idcertifica,id");
        if ($arrTemC && !empty($arrTemC)) {
            foreach ($arrTemC as $tx) {
                if (trim($tx["contenido"]) != '') {
                    if (!isset($retorno["crtsii"][$tx["idcertifica"]])) {
                        $retorno["crtsii"][$tx["idcertifica"]] = '';
                    }
                    $txt1 = \funcionesGenerales::reemplazarAcutes(\funcionesGenerales::restaurarEspeciales(trim(str_replace("&rdquo;", '"', $tx["contenido"]))));
                    $txt1 = str_replace("||", CHR(13) . CHR(10) . CHR(13) . CHR(10), $txt1);
                    $txt1 = str_replace("|", " ", $txt1);
                    $retorno["crtsii"][$tx["idcertifica"]] .= $txt1;
                }
            }
        }
        unset($arrTemC);
        if ($genlog == 'si') {
            \logApi::general2($nameLog, $retorno["matricula"], 'Leyo certificas SII');
        }

        // Carga relación de embargos
        $retorno["embargos"] = 0;
        $retorno["ctrembargos"] = array();
        $i = 0;
        $arrTemE = retornarRegistrosMysqliApi($mysqli, 'mreg_est_embargos', "matricula='" . $retorno["matricula"] . "'", "fecinscripcion,libro,numreg");
        if ($arrTemE === false) {
            echo $_SESSION["generales"]["mensajeerror"];
        }
        if ($arrTemE && !empty($arrTemE)) {
            foreach ($arrTemE as $e) {
                $i++;
                $retorno["ctrembargos"][$i] = array();
                $retorno["ctrembargos"][$i]["id"] = $e["id"];
                $retorno["ctrembargos"][$i]["acto"] = $e["acto"];
                $retorno["ctrembargos"][$i]["idclase"] = $e["idclase"];
                $retorno["ctrembargos"][$i]["numid"] = $e["numid"];
                $retorno["ctrembargos"][$i]["nombre"] = stripslashes((string) $e["nombre"]);
                $retorno["ctrembargos"][$i]["idclasedemandante"] = $e["idclasedemandante"];
                $retorno["ctrembargos"][$i]["numiddemandante"] = $e["numiddemandante"];
                $retorno["ctrembargos"][$i]["nombredemandante"] = stripslashes((string) $e["nombredemandante"]);
                $retorno["ctrembargos"][$i]["tipdoc"] = $e["tipdoc"];
                $retorno["ctrembargos"][$i]["numdoc"] = $e["numdoc"];
                $retorno["ctrembargos"][$i]["fecdoc"] = $e["fecdoc"];
                $retorno["ctrembargos"][$i]["idorigen"] = $e["idorigen"];
                $retorno["ctrembargos"][$i]["txtorigen"] = stripslashes((string) $e["txtorigen"]);
                $retorno["ctrembargos"][$i]["fecrad"] = $e["fecradica"];
                $retorno["ctrembargos"][$i]["estado"] = $e["ctrestadoembargo"];
                if (strlen($e["libro"]) == 2) {
                    if (trim($e["libro"]) > '50' && trim($e["libro"]) < '60') {
                        $retorno["ctrembargos"][$i]["libro"] = 'RE' . $e["libro"];
                    } else {
                        $retorno["ctrembargos"][$i]["libro"] = 'RM' . $e["libro"];
                    }
                } else {
                    $retorno["ctrembargos"][$i]["libro"] = $e["libro"];
                }
                $retorno["ctrembargos"][$i]["numreg"] = $e["numreg"];
                $retorno["ctrembargos"][$i]["codbarras"] = $e["codbarras"];
                $retorno["ctrembargos"][$i]["noticia"] = stripslashes((string) $e["noticia"]);
                $retorno["ctrembargos"][$i]["fecinscripcion"] = $e["fecinscripcion"];
                $retorno["ctrembargos"][$i]["esembargo"] = '';

                $ind = $retorno["ctrembargos"][$i]["libro"] . '-' . $e["acto"];
                if (isset($_SESSION["maestroactos"][$ind])) {
                    if ($_SESSION["maestroactos"][$ind]["idgrupoacto"] == '018') {
                        $retorno["ctrembargos"][$i]["esembargo"] = 'S';
                        if ($e["ctrestadoembargo"] != '2') {
                            $retorno["embargos"]++;
                        }
                    }
                }
            }
        }

        // echo count ($retornos["ctrembargos"]);
        unset($arrTemE);
        if ($genlog == 'si') {
            \logApi::general2($nameLog, $retorno["matricula"], 'Leyo embargos');
        }

        // Relación de pagos de afiliacion
        $retorno["periodicoafiliados"] = array();

        // Relacion de anexos documentales
        $retorno["imagenes"] = array();
        if ($tipodata == 'E') {
            $imgs = array();
            if ($retorno["tipoidentificacion"] == '7') {
                $idex = '';
            } else {
                $idex = ltrim(trim($retorno["identificacion"]), "0");
                if (strlen($idex) < 5) {
                    $idex = '';
                }
            }

            // \logApi::general2($nameLog, $retorno["matricula"], 'Entró buscar imagenes: ' . date("His"));
            if ($idex != '') {
                if (ltrim((string) $retorno["matricula"], "0") != '') {
                    $imgs = retornarRegistrosMysqliApi($mysqli, 'mreg_radicacionesanexos', "matricula='" . ltrim((string) $retorno["matricula"], "0") . "' or identificacion like '" . $idex . "%'", "idanexo");
                } else {
                    $imgs = retornarRegistrosMysqliApi($mysqli, 'mreg_radicacionesanexos', "identificacion like '" . $idex . "%'", "idanexo");
                }
            } else {
                if (ltrim((string) $retorno["matricula"], "0") != '') {
                    $imgs = retornarRegistrosMysqliApi($mysqli, 'mreg_radicacionesanexos', "matricula='" . ltrim((string) $retorno["matricula"], "0") . "'", "idanexo");
                }
            }
            // \logApi::general2($nameLog, $retorno["matricula"], 'Salió de buscar imagenes: ' . date("His"));
            if ($imgs) {
                if (!empty($imgs)) {
                    $i = 0;
                    $indAnexos = array();
                    foreach ($imgs as $img) {
                        if ($img["eliminado"] != 'SI') {
                            if ($img["bandeja"] == '4.-REGMER' || $img["bandeja"] == '5.-REGESADL') {
                                if ($img["tipoanexo"] != '509') {
                                    if (!isset($indAnexos[$img["idanexo"]])) {
                                        $i++;
                                        $retorno["imagenes"][$i] = $img;
                                        $indAnexos[$img["idanexo"]] = $img["idanexo"];
                                        if (ltrim((string) $img["idradicacion"], "0") != '') {
                                            $imgs1 = retornarRegistrosMysqliApi($mysqli, 'mreg_radicacionesanexos', "idradicacion='" . $img["idradicacion"] . "'", "idanexo");
                                            if (!empty($imgs1)) {
                                                foreach ($imgs1 as $img1) {
                                                    if ($img1["eliminado"] != 'SI') {
                                                        if ($img1["tipoanexo"] != '509') {
                                                            if (!isset($indAnexos[$img1["idanexo"]])) {
                                                                $i++;
                                                                $retorno["imagenes"][$i] = $img1;
                                                                $indAnexos[$img1["idanexo"]] = $img1["idanexo"];
                                                            }
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                    $retorno["imagenes1"] = $retorno["imagenes"];
                    $retorno["imagenes"] = array();
                    $i = 0;
                    foreach ($retorno["imagenes1"] as $img) {
                        $i++;
                        $retorno["imagenes"][$i] = $img;
                        if (trim((string) $img["bandeja"]) == '') {
                            $tx = retornarRegistroMysqliApi($mysqli, 'bas_tipodoc', "idtipodoc='" . $img["idtipodoc"] . "'");
                            if ($tx === false || empty($tx)) {
                                $retorno["imagenes"][$i]["bandeja"] = '';
                            } else {
                                $retorno["imagenes"][$i]["bandeja"] = $tx["bandejadigitalizacion"];
                            }
                        }
                        if (trim($img["bandeja"]) == '') {
                            if (trim($img["libro"]) != '') {
                                if (strlen(trim($img["libro"])) < 3) {
                                    if (sprintf("%02s", $img["libro"]) < '50') {
                                        $retorno["imagenes"][$i]["bandeja"] = '4.-REGMER';
                                    } else {
                                        $retorno["imagenes"][$i]["bandeja"] = '5.-REGESADL';
                                    }
                                } else {
                                    if (substr($img["libro"], 2, 2) < '50') {
                                        $retorno["imagenes"][$i]["bandeja"] = '4.-REGMER';
                                    } else {
                                        $retorno["imagenes"][$i]["bandeja"] = '5.-REGESADL';
                                    }
                                }
                            }
                        }
                        $obs = '';
                        $fechamostrar = '';
                        $estadocb = '';
                        if (trim($img["libro"]) != '' && trim($img["registro"]) != '') {
                            $estadocb = 'S';
                            if (strlen(trim($img["libro"])) < 3) {
                                if (sprintf("%02s", $img["libro"]) < '50') {
                                    $txLib = 'RM' . sprintf("%02s", trim($img["libro"]));
                                } else {
                                    $txLib = 'RE' . sprintf("%02s", trim($img["libro"]));
                                }
                            } else {
                                $txLib = trim($img["libro"]);
                            }
                            $condic = "libro='" . $txLib . "' and registro='" . ltrim($img["registro"], "0") . "'";
                            $temIns = retornarRegistroMysqliApi($mysqli, 'mreg_est_inscripciones', $condic);
                            if ($temIns && !empty($temIns)) {
                                $obs = '<strong>Inscripción No. </strong>' . $txLib . '-' . $img["registro"];
                                if ($temIns["noticia"] != '') {
                                    if (trim($obs) != '') {
                                        $obs .= '<br>';
                                    }
                                    $obs .= $temIns["noticia"];
                                }
                                $fechamostrar = $temIns["fecharegistro"];
                            }
                        } else {
                            if (ltrim((string) $img["idradicacion"], "0") != '') {
                                $cb = retornarRegistroMysqliApi($mysqli, 'mreg_est_codigosbarras', "codigobarras='" . ltrim((string) $img["idradicacion"], "0") . "'");
                                $fechamostrar = $cb["fecharadicacion"];
                                $actorep = $cb["actoreparto"];
                                $estadocb = retornarRegistroMysqliApi($mysqli, 'mreg_codestados_rutamercantil', "id='" . $cb["estadofinal"] . "'", "estadoterminal");
                                if ($actorep != '') {
                                    if (trim($obs) != '') {
                                        $obs .= '<br>';
                                    }
                                    $obs .= retornarRegistroMysqliApi($mysqli, 'mreg_codrutas', "id='" . $actorep . "'", "descripcion");
                                }
                            } else {
                                if (trim($img["numerorecibo"]) != '') {
                                    $fechamostrar = retornarRegistroMysqliApi($mysqli, 'mreg_est_recibos', "numerorecibo='" . trim($img["numerorecibo"]) . "'", "fecoperacion");
                                    $actorep = retornarRegistroMysqliApi($mysqli, 'mreg_est_recibos', "numerorecibo='" . trim($img["numerorecibo"]) . "'", "servicio");
                                    $estadocb = 'S';
                                    if ($actorep != '') {
                                        if (trim($obs) != '') {
                                            $obs .= '<br>';
                                        }
                                        $obs .= retornarRegistroMysqliApi($mysqli, 'mreg_servicios', "idservicio='" . $actorep . "'", "nombre");
                                    }
                                }
                            }
                        }

                        if (trim($img["idtipodoc"]) != '') {
                            if ($obs != '') {
                                $obs .= '<br>';
                            }
                            $obs .= '<strong>Tipo documental : </strong>(' . $img["idtipodoc"] . ') ' . str_replace("--- ", "", retornarRegistroMysqliApi($mysqli, 'bas_tipodoc', "idtipodoc='" . $img["idtipodoc"] . "'", "nombre"));
                        }

                        if (trim($img["libro"]) == '') {
                            if (trim($img["observaciones"]) != '') {
                                if ($obs != '') {
                                    $obs .= '<br>';
                                }
                                $obs .= $img["observaciones"];
                            }
                        }
                        if ($fechamostrar == '') {
                            $fechamostrar = $img["fechadoc"];
                        }
                        $retorno["imagenes"][$i]["obs"] = $obs;
                        $retorno["imagenes"][$i]["fmostrar"] = $fechamostrar;
                        $retorno["imagenes"][$i]["estadocb"] = $estadocb;
                    }
                }
            }
            $retorno["imagenes"] = \funcionesGenerales::ordenarMatriz($retorno["imagenes"], "fmostrar", true);
            unset($retorno["imagenes1"]);
        }
        if ($genlog == 'si') {
            \logApi::general2($nameLog, $retorno["matricula"], 'Leyo anexos documentales ' . count($retorno["imagenes"]));
        }

        // Confirma si tiene o no beneficio de la Ley 1780
        if (date("Y") == '2017') {
            if ($retorno["organizacion"] == '01' || ($retorno["organizacion"] > '02' && $retorno["categoria"] == '1')) {
                if ($retorno["fechamatricula"] >= '20160502') {
                    if ($retorno["benley1780"] == '') {
                        if (contarRegistrosMysqliApi($mysqli, 'mreg_est_recibos', "matricula='" . $retorno["matricula"] . "' and servicio='01090110' and (fecoperacion between '20160101' and '20161231')") > 0) {
                            $retorno["benley1780"] = 'S';
                        }
                    }
                }
            }
        }

        // Aplica para el 2018
        if (date("Y") == '2018') {
            if ($retorno["organizacion"] == '01' || ($retorno["organizacion"] > '02' && $retorno["categoria"] == '1')) {
                if ($retorno["fechamatricula"] >= '20170101') {
                    if ($retorno["benley1780"] == '') {
                        if (contarRegistrosMysqliApi($mysqli, 'mreg_est_recibos', "matricula='" . $retorno["matricula"] . "' and (servicio IN ('01090110','01090111')) and (fecoperacion between '20170101' and '20171231')") > 0) {
                            $retorno["benley1780"] = 'S';
                        }
                    }
                }
            }
        }

        // Aplica para el 2019
        if (date("Y") == '2019') {
            if ($retorno["organizacion"] == '01' || ($retorno["organizacion"] > '02' && $retorno["categoria"] == '1')) {
                if ($retorno["fechamatricula"] >= '20180101') {
                    if ($retorno["benley1780"] == '') {
                        if (contarRegistrosMysqliApi($mysqli, 'mreg_est_recibos', "matricula='" . $retorno["matricula"] . "' and (servicio IN ('01090110','01090111')) and (fecoperacion between '20180101' and '20181231')") > 0) {
                            $retorno["benley1780"] = 'S';
                        }
                    }
                }
            }
        }

        // Aplica para el 2020
        if (date("Y") == '2020') {
            if ($retorno["organizacion"] == '01' || ($retorno["organizacion"] > '02' && $retorno["categoria"] == '1')) {
                if ($retorno["fechamatricula"] >= '20190101') {
                    if ($retorno["benley1780"] == '') {
                        if (contarRegistrosMysqliApi($mysqli, 'mreg_est_recibos', "matricula='" . $retorno["matricula"] . "' and (servicio IN ('01090110','01090111')) and (fecoperacion between '20190101' and '20191231')") > 0) {
                            $retorno["benley1780"] = 'S';
                        }
                    }
                }
            }
        }

        // Aplica para el 2021
        if (date("Y") == '2021') {
            if ($retorno["organizacion"] == '01' || ($retorno["organizacion"] > '02' && $retorno["categoria"] == '1')) {
                if ($retorno["fechamatricula"] >= '20200101') {
                    if ($retorno["benley1780"] == '') {
                        if (contarRegistrosMysqliApi($mysqli, 'mreg_est_recibos', "matricula='" . $retorno["matricula"] . "' and (servicio IN ('01090110','01090111')) and (fecoperacion between '20200101' and '20201231')") > 0) {
                            $retorno["benley1780"] = 'S';
                        }
                    }
                }
            }
        }

        // Aplica para el 2022
        if (date("Y") == '2022') {
            if ($retorno["organizacion"] == '01' || ($retorno["organizacion"] > '02' && $retorno["categoria"] == '1')) {
                if ($retorno["fechamatricula"] >= '20210101') {
                    if ($retorno["benley1780"] == '') {
                        if (contarRegistrosMysqliApi($mysqli, 'mreg_est_recibos', "matricula='" . $retorno["matricula"] . "' and (servicio IN ('01090110','01090111')) and (fecoperacion between '20210101' and '20211231')") > 0) {
                            $retorno["benley1780"] = 'S';
                        }
                    }
                }
            }
        }

        // Aplica para el 2023
        if (date("Y") == '2023') {
            if ($retorno["organizacion"] == '01' || ($retorno["organizacion"] > '02' && $retorno["categoria"] == '1')) {
                if ($retorno["fechamatricula"] >= '20220101') {
                    if ($retorno["benley1780"] == '') {
                        if (contarRegistrosMysqliApi($mysqli, 'mreg_est_recibos', "matricula='" . $retorno["matricula"] . "' and (servicio IN ('01090110','01090111')) and (fecoperacion between '20220101' and '20221231')") > 0) {
                            $retorno["benley1780"] = 'S';
                        }
                    }
                }
            }
        }

        // Aplica para el 2024
        if (date("Y") == '2024') {
            if ($retorno["organizacion"] == '01' || ($retorno["organizacion"] > '02' && $retorno["categoria"] == '1')) {
                if ($retorno["fechamatricula"] >= '20230101') {
                    if ($retorno["benley1780"] == '') {
                        if (contarRegistrosMysqliApi($mysqli, 'mreg_est_recibos', "matricula='" . $retorno["matricula"] . "' and (servicio IN ('01090110','01090111')) and (fecoperacion between '20230101' and '20231231')") > 0) {
                            $retorno["benley1780"] = 'S';
                        }
                    }
                }
            }
        }

        // Aplica para el 2025
        if (date("Y") == '2025') {
            if ($retorno["organizacion"] == '01' || ($retorno["organizacion"] > '02' && $retorno["categoria"] == '1')) {
                if ($retorno["fechamatricula"] >= '20240101') {
                    if ($retorno["benley1780"] == '') {
                        if (contarRegistrosMysqliApi($mysqli, 'mreg_est_recibos', "matricula='" . $retorno["matricula"] . "' and (servicio IN ('01090110','01090111')) and (fecoperacion between '20240101' and '20241231')") > 0) {
                            $retorno["benley1780"] = 'S';
                        }
                    }
                }
            }
        }


        // 2017-09-26: jint : DATOS DEL CAE
        $retorno["placaalcaldia"] = '';
        $retorno["placaalcaldiafecha"] = '';
        $retorno["reportealcaldia"] = '';

        $temx = retornarRegistroMysqliApi($mysqli, 'mreg_alcaldias', "idmunicipio='" . trim($retorno["muncom"]) . "'");
        if ($temx && !empty($temx)) {
            if ($temx["eliminado"] != 'SI') {
                if ($temx["fechainicio"] <= $retorno["fechamatricula"]) {
                    $retorno["reportealcaldia"] = 'si';
                }
            }
        }
        unset($temx);

        if ($retorno["reportealcaldia"] == 'si') {
            $temx = retornarRegistroMysqliApi($mysqli, 'mreg_matriculasalcaldia', "idmatricula='" . ltrim($retorno["matricula"], "0") . "'");
            if ($temx && !empty($temx)) {
                $retorno["placaalcaldia"] = trim($temx["matriculaic"]);
                $retorno["placaalcaldiafecha"] = trim($temx["fechamatriculaic"]);
            } else {
                $temx = retornarRegistroMysqliApi($mysqli, 'mreg_envio_matriculas_api', "matricula='" . ltrim($retorno["matricula"], "0") . "'", '*', 'U');
                if ($temx && !empty($temx)) {
                    $retorno["placaalcaldia"] = trim($temx["codigoasignadorespuesta"]);
                    $retorno["placaalcaldiafecha"] = trim($temx["fechahorarespuesta"]);
                }
            }
            unset($temx);
        }

        // *********************************************************************************** 
        // Valida si la matrícula se encuentra inactiva
        // *********************************************************************************** 
        if ($retorno["estadomatricula"] == 'MA' || $retorno["estadomatricula"] == 'IA' || $retorno["estadomatricula"] == 'MR' || $retorno["estadomatricula"] == 'IR') {
            if ($retorno["fecharenovacion"] != '') {
                if ($retorno["ultanoren"] < date("Y")) {
                    if (\funcionesRegistrales::inactivarSiprefMatriculas($mysqli, $retorno["matricula"], $retorno["fechamatricula"], $retorno["fecharenovacion"])) {
                        $retorno["estadomatricula"] = 'MI';
                        if (substr($retorno["matricula"], 0, 1) == 'S') {
                            $retorno["estadomatricula"] = 'II';
                        }
                    }
                }
            }
        }

        // *********************************************************************************** 
        // Campos adicionales del expediente
        // ***********************************************************************************         
        $retorno["domicilio_ong"] = '';
        $retorno["codrespotri"] = array();
        $retorno["tituloorganodirectivo"] = '';
        $retorno["siglaenconstitucion"] = '';
        $retorno["condiespe2219"] = '';
        $retorno["etnia"] = '';
        $retorno["participacionetnia"] = '';
        $retorno["emprendimientosocial"] = '';
        $retorno["empsoccategorias"] = '';
        $retorno["empsocbeneficiarios"] = '';
        $retorno["empsoccategorias_otros"] = '';
        $retorno["empsocbeneficiarios_otros"] = '';
        $retorno["descripcionmotivocancelacion"] = '';

        $iResp = 0;
        $temx = retornarRegistrosMysqliApi($mysqli, 'mreg_est_inscritos_campos', "matricula='" . ltrim($retorno["matricula"], "0") . "'", "campo");
        foreach ($temx as $tx) {
            if (trim($tx["contenido"]) != '') {
                if ($tx["campo"] == 'domicilio_ong') {
                    $retorno[$tx["campo"]] = $tx["contenido"];
                }
                if ($tx["campo"] == 'etnia') {
                    $retorno[$tx["campo"]] = $tx["contenido"];
                }
                if ($tx["campo"] == 'participacionetnia') {
                    $retorno[$tx["campo"]] = doubleval($tx["contenido"]);
                }
                if ($tx["campo"] == 'emprendimientosocial') {
                    $retorno[$tx["campo"]] = $tx["contenido"];
                }
                if ($tx["campo"] == 'tituloorganodirectivo') {
                    $retorno[$tx["campo"]] = $tx["contenido"];
                }
                if ($tx["campo"] == 'siglaenconstitucion') {
                    $retorno[$tx["campo"]] = $tx["contenido"];
                }
                if ($tx["campo"] == 'condiespe2219') {
                    $retorno[$tx["campo"]] = $tx["contenido"];
                }
                if ($tx["campo"] == 'empsoccategorias') {
                    $retorno[$tx["campo"]] = $tx["contenido"];
                    if ($tx["contenido"] != '') {
                        $esx = explode(",", $tx["contenido"]);
                        foreach ($esx as $esx1) {
                            $retorno[$esx1] = 'S';
                        }
                    }
                }
                if ($tx["campo"] == 'empsoccategorias_otros') {
                    $retorno[$tx["campo"]] = $tx["contenido"];
                }
                if ($tx["campo"] == 'empsocbeneficiarios') {
                    $retorno[$tx["campo"]] = $tx["contenido"];
                    if ($tx["contenido"] != '') {
                        $esx = explode(",", $tx["contenido"]);
                        foreach ($esx as $esx1) {
                            $retorno[$esx1] = 'S';
                        }
                    }
                }
                if ($tx["campo"] == 'empsocbeneficiarios_otros') {
                    $retorno[$tx["campo"]] = $tx["contenido"];
                }
                if ($tx["campo"] == 'codrespotri') {
                    $lt = explode(",", $tx["contenido"]);
                    foreach ($lt as $lt1) {
                        if (trim($lt1) != '') {
                            $iResp++;
                            $retorno["codrespotri"][$iResp] = trim($lt1);
                        }
                    }
                }
                if ($tx["campo"] == 'descripcionmotivocancelacion') {
                    $retorno[$tx["campo"]] = $tx["contenido"];
                }
                if ($tx["campo"] == 'extinciondominio') {
                    $retorno[$tx["campo"]] = $tx["contenido"];
                }
                if ($tx["campo"] == 'extinciondominiofechainicio') {
                    $retorno[$tx["campo"]] = $tx["contenido"];
                }
                if ($tx["campo"] == 'extinciondominiofechafinal') {
                    $retorno[$tx["campo"]] = $tx["contenido"];
                }
                if ($tx["campo"] == 'ctrcontrolaccesopublico') {
                    $retorno[$tx["campo"]] = $tx["contenido"];
                }
            }
        }

        //
        //
        if ($retorno["organizacion"] > '02' && $retorno["categoria"] == '1') {
            $retorno["estadisuelta"] = 'no';
        }
        if (
                $retorno["estadomatricula"] != 'MF' &&
                $retorno["estadomatricula"] != 'MC' &&
                $retorno["estadomatricula"] != 'IC' &&
                $retorno["estadomatricula"] != 'IF'
        ) {
            if ($retorno["ctrcancelacion1429"] != '3') {
                if (date("Ymd") > $_SESSION["generales"]["fcorte"]) {
                    if ($retorno["ultanoren"] == (date("Y") - 1)) {
                        $retorno["norenovado"] = 'si';
                        if ($retorno["disueltaporvencimiento"] == 'si') {
                            $retorno["norenovado"] = 'no';
                            $retorno["estadisuelta"] = 'si';
                        } else {
                            if ($retorno["disueltaporacto510"] == 'si') {
                                if ($retorno["fechaacto510"] <= $_SESSION["generales"]["fcorte"]) {
                                    $retorno["norenovado"] = 'no';
                                    $retorno["estadisuelta"] = 'si';
                                }
                            } else {
                                if ($retorno["perdidacalidadcomerciante"] == 'si') {
                                    $ano1 = $retorno["ultanoren"] + 1;
                                    $fcorte1 = retornarRegistroMysqliApi($mysqli, 'mreg_cortes_renovacion', "ano='" . $ano1 . "'", "corte");
                                    if ($retorno["fechaperdidacalidadcomerciante"] <= $fcorte1) {
                                        $retorno["norenovado"] = 'no';
                                    }
                                }
                            }
                        }
                    }
                }
                //

                if ($retorno["ultanoren"] < (date("Y") - 1)) {
                    $retorno["norenovado"] = 'si';
                    if ($retorno["disueltaporvencimiento"] == 'si') {
                        $retorno["estadisuelta"] = 'si';
                        if (($retorno["fechaacto510"] != '') && $retorno["fechaacto510"] < $retorno["fechavencimiento"]) {
                            $retorno["disueltaporvencimiento"] = 'no';
                        } else {
                            if (substr($retorno["fechavencimiento"], 4, 4) <= $_SESSION["generales"]["fcortemesdia"]) {
                                $ultanorendis = substr($retorno["fechavencimiento"], 0, 4) - 1;
                            } else {
                                $ultanorendis = substr($retorno["fechavencimiento"], 0, 4);
                            }
                            if ($retorno["ultanoren"] >= $ultanorendis) {
                                $retorno["norenovado"] = 'no';
                            }
                        }
                    }
                    if ($retorno["disueltaporacto510"] == 'si') {
                        $retorno["estadisuelta"] = 'si';
                        if (substr($retorno["fechaacto510"], 0, 4) == $retorno["ultanoren"]) {
                            $retorno["norenovado"] = 'no';
                        } else {
                            $ano1 = $retorno["ultanoren"] + 1;
                            $fcorte1 = retornarRegistroMysqliApi($mysqli, 'mreg_cortes_renovacion', "ano='" . $ano1 . "'", "corte");
                            if ($retorno["fechaacto510"] <= $fcorte1) {
                                $retorno["norenovado"] = 'no';
                            } else {
                                if ($retorno["perdidacalidadcomerciante"] == 'si') {
                                    if ($retorno["fechaperdidacalidadcomerciante"] <= $fcorte1) {
                                        $retorno["norenovado"] = 'no';
                                    }
                                } else {
                                    if ($retorno["reactivadaacto511"] == 'si') {
                                        if (date("Ymd") <= $_SESSION["generales"]["fcorte"]) {
                                            if ($retorno["fechaacto511"] >= (date("Y") - 1) . '0101' && $retorno["fechaacto511"] <= $_SESSION["generales"]["fcorte"]) {
                                                $retorno["norenovado"] = 'no';
                                            }
                                        } else {
                                            if ($retorno["fechaacto511"] >= date("Y") . '0101') {
                                                $retorno["norenovado"] = 'no';
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        // *************************************************** //
        // Arma arreglo de habilitaciones especiales
        // *************************************************** //
        $estransportecarga = 'no';
        $estransporteespecial = 'no';
        $estransportemixto = 'no';

        $certificartransportecarga = 'no';
        $certificartransporteespecial = 'no';
        $certificartransportemixto = 'no';
        $certificartransportepasajeros = 'no';

        $certificargrupo093 = 'no';

        //
        if (
                $retorno["ciius"][1] == 'H4923' ||
                $retorno["ciius"][2] == 'H4923' ||
                $retorno["ciius"][3] == 'H4923' ||
                $retorno["ciius"][4] == 'H4923'
        ) {
            $estransportecarga = 'si';
        }
        if (
                $retorno["ciius"][1] == 'H4921' ||
                $retorno["ciius"][2] == 'H4921' ||
                $retorno["ciius"][3] == 'H4921' ||
                $retorno["ciius"][4] == 'H4921'
        ) {
            $estransporteespecial = 'si';
        }
        if (
                $retorno["ciius"][1] == 'H4922' ||
                $retorno["ciius"][2] == 'H4922' ||
                $retorno["ciius"][3] == 'H4922' ||
                $retorno["ciius"][4] == 'H4922'
        ) {
            $estransportemixto = 'si';
        }

        foreach ($retorno["inscripciones"] as $ins) {
            if ($ins["grupoacto"] == '066') {
                if ($ins["freg"] < $_SESSION["generales"]["FECHA_CORTE_REVOCACION"] && $ins["crev"] == '1') {
                    $ins["crev"] = '0';
                }
                if ($ins["crev"] != '1' && $ins["crev"] != '9') {
                    $certificartransportecarga = 'encontro';
                }
            }
        }

        foreach ($retorno["inscripciones"] as $ins) {
            if ($ins["grupoacto"] == '067') {
                if ($ins["freg"] < $_SESSION["generales"]["FECHA_CORTE_REVOCACION"] && $ins["crev"] == '1') {
                    $ins["crev"] = '0';
                }
                if ($ins["crev"] != '1' && $ins["crev"] != '9') {
                    $certificartransporteespecial = 'encontro';
                }
            }
        }

        foreach ($retorno["inscripciones"] as $ins) {
            if ($ins["grupoacto"] == '091') {
                if ($ins["freg"] < $_SESSION["generales"]["FECHA_CORTE_REVOCACION"] && $ins["crev"] == '1') {
                    $ins["crev"] = '0';
                }
                if ($ins["crev"] != '1' && $ins["crev"] != '9') {
                    $certificartransportemixto = 'encontro';
                }
            }
        }

        foreach ($retorno["inscripciones"] as $ins) {
            if ($ins["grupoacto"] == '092') {
                if ($ins["freg"] < $_SESSION["generales"]["FECHA_CORTE_REVOCACION"] && $ins["crev"] == '1') {
                    $ins["crev"] = '0';
                }
                if ($ins["crev"] != '1' && $ins["crev"] != '9') {
                    $certificartransportepasajeros = 'encontro';
                }
            }
        }

        foreach ($retorno["inscripciones"] as $ins) {
            if ($ins["grupoacto"] == '093') {
                if ($ins["freg"] < $_SESSION["generales"]["FECHA_CORTE_REVOCACION"] && $ins["crev"] == '1') {
                    $ins["crev"] = '0';
                }
                $imp = 'no';
                if (trim((string) $ins["flim"])) {
                    $imp = 'si';
                } else {
                    if (trim((string) $ins["flim"]) != '' && $ins["flim"] < date("Ymd")) {
                        $imp = 'si';
                    }
                }
                if ($imp == 'si') {
                    if ($ins["crev"] != '1' && $ins["crev"] != '9') {
                        $certificargrupo093 = 'no';
                    }
                }
            }
        }


        if ($estransportecarga == 'si') {
            $estransportecarga = 'falta';
            foreach ($retorno["inscripciones"] as $ins) {
                if ($ins["grupoacto"] == '066') {
                    if ($ins["freg"] < $_SESSION["generales"]["FECHA_CORTE_REVOCACION"] && $ins["crev"] == '1') {
                        $ins["crev"] = '0';
                    }
                    if ($ins["crev"] != '1' && $ins["crev"] != '9') {
                        $estransportecarga = 'encontro';
                    }
                }
            }
        }

        if ($estransporteespecial == 'si') {
            $estransporteespecial = 'falta';
            foreach ($retorno["inscripciones"] as $ins) {
                if ($ins["grupoacto"] == '067') {
                    if ($ins["freg"] < $_SESSION["generales"]["FECHA_CORTE_REVOCACION"] && $ins["crev"] == '1') {
                        $ins["crev"] = '0';
                    }
                    if ($ins["crev"] != '1' && $ins["crev"] != '9') {
                        $estransporteespecial = 'encontro';
                    }
                }
            }
        }

        if ($estransportemixto == 'si') {
            $estransportemixto = 'falta';
            foreach ($retorno["inscripciones"] as $ins) {
                if ($ins["grupoacto"] == '091') {
                    if ($ins["freg"] < $_SESSION["generales"]["FECHA_CORTE_REVOCACION"] && $ins["crev"] == '1') {
                        $ins["crev"] = '0';
                    }
                    if ($ins["crev"] != '1' && $ins["crev"] != '9') {
                        $estransportemixto = 'encontro';
                    }
                }
            }
        }

        //
        /*
          $retorno["inscripciones"][$ix]["camant"] = $x["camaraanterior"];
          $retorno["inscripciones"][$ix]["libant"] = $x["libroanterior"];
          $retorno["inscripciones"][$ix]["regant"] = $x["registroanterior"];
          $retorno["inscripciones"][$ix]["fecant"] = $x["fecharegistroanterior"];

          $retorno["inscripciones"][$ix]["camant2"] = $x["camaraanterior2"];
          $retorno["inscripciones"][$ix]["libant2"] = $x["libroanterior2"];
          $retorno["inscripciones"][$ix]["regant2"] = $x["registroanterior2"];
          $retorno["inscripciones"][$ix]["fecant2"] = $x["fecharegistroanterior2"];

          $retorno["inscripciones"][$ix]["camant3"] = $x["camaraanterior3"];
          $retorno["inscripciones"][$ix]["libant3"] = $x["libroanterior3"];
          $retorno["inscripciones"][$ix]["regant3"] = $x["registroanterior3"];
          $retorno["inscripciones"][$ix]["fecant3"] = $x["fecharegistroanterior3"];

          $retorno["inscripciones"][$ix]["camant4"] = $x["camaraanterior4"];
          $retorno["inscripciones"][$ix]["libant4"] = $x["libroanterior4"];
          $retorno["inscripciones"][$ix]["regant4"] = $x["registroanterior4"];
          $retorno["inscripciones"][$ix]["fecant4"] = $x["fecharegistroanterior4"];

          $retorno["inscripciones"][$ix]["camant5"] = $x["camaraanterior5"];
          $retorno["inscripciones"][$ix]["libant5"] = $x["libroanterior5"];
          $retorno["inscripciones"][$ix]["regant5"] = $x["registroanterior5"];
          $retorno["inscripciones"][$ix]["fecant5"] = $x["fecharegistroanterior5"];
         */

        if ($certificartransportemixto == 'encontro') {
            foreach ($retorno["inscripciones"] as $ins) {
                if ($ins["grupoacto"] == '091') {
                    if ($ins["freg"] < $_SESSION["generales"]["FECHA_CORTE_REVOCACION"] && $ins["crev"] == '1') {
                        $ins["crev"] = '0';
                    }
                    if ($ins["crev"] != '1' && $ins["crev"] != '9') {
                        if (trim($ins["ndocext"]) != '') {
                            $ndocx = $ins["ndocext"];
                        } else {
                            $ndocx = $ins["ndoc"];
                        }
                        $txt = 'Mediante inscripción No. ' . $ins["nreg"] . ' de ' . strtolower(\funcionesGenerales::mostrarFechaLetras1($ins["freg"]));
                        if ($ins["camant"] != '') {
                            $txt .= ', inscrito previamente en la ' . retornarRegistroMysqliApi($mysqli, 'bas_camaras', "id='" . $ins["camant"] . "'", "nombre") . ' el ' . strtolower(\funcionesGenerales::mostrarFechaLetras1($ins["fecant"]));
                            if ($ins["camant2"] != '') {
                                $txt .= ', inscrito previamente en la ' . retornarRegistroMysqliApi($mysqli, 'bas_camaras', "id='" . $ins["camant2"] . "'", "nombre") . ' el ' . strtolower(\funcionesGenerales::mostrarFechaLetras1($ins["fecant2"]));
                                if ($ins["camant3"] != '') {
                                    $txt .= ', inscrito previamente en la ' . retornarRegistroMysqliApi($mysqli, 'bas_camaras', "id='" . $ins["camant3"] . "'", "nombre") . ' el ' . strtolower(\funcionesGenerales::mostrarFechaLetras1($ins["fecant3"]));
                                    if ($ins["camant4"] != '') {
                                        $txt .= ', inscrito previamente en la ' . retornarRegistroMysqliApi($mysqli, 'bas_camaras', "id='" . $ins["camant4"] . "'", "nombre") . ' el ' . strtolower(\funcionesGenerales::mostrarFechaLetras1($ins["fecant4"]));
                                        if ($ins["camant5"] != '') {
                                            $txt .= ', inscrito previamente en la ' . retornarRegistroMysqliApi($mysqli, 'bas_camaras', "id='" . $ins["camant5"] . "'", "nombre") . ' el ' . strtolower(\funcionesGenerales::mostrarFechaLetras1($ins["fecant5"]));
                                        }
                                    }
                                }
                            }
                            $txt .= ', ';
                        } else {
                            $txt .= ' ';
                        }
                        $txt .= 'se registró el acto administrativo No. ' . $ndocx . ' de ' . strtolower(\funcionesGenerales::mostrarFechaLetras1($ins["fdoc"])) . ', expedido por ';
                        $txt .= 'El Ministerio de Transporte que lo habilita para prestar el servicio público de transporte terrestre automotor mixto.';
                        $retorno["habilitacionesespeciales"][] = $txt;
                    }
                }
            }
        }

        //
        if ($certificartransportepasajeros == 'encontro') {
            foreach ($retorno["inscripciones"] as $ins) {
                if ($ins["grupoacto"] == '092') {
                    if ($ins["freg"] < $_SESSION["generales"]["FECHA_CORTE_REVOCACION"] && $ins["crev"] == '1') {
                        $ins["crev"] = '0';
                    }
                    if ($ins["crev"] != '1' && $ins["crev"] != '9') {
                        if (trim($ins["ndocext"]) != '') {
                            $ndocx = $ins["ndocext"];
                        } else {
                            $ndocx = $ins["ndoc"];
                        }
                        $txt = 'Mediante inscripción No. ' . $ins["nreg"] . ' de ' . strtolower(\funcionesGenerales::mostrarFechaLetras1($ins["freg"]));
                        if ($ins["camant"] != '') {
                            $txt .= ', inscrito previamente en la ' . retornarRegistroMysqliApi($mysqli, 'bas_camaras', "id='" . $ins["camant"] . "'", "nombre") . ' el ' . strtolower(\funcionesGenerales::mostrarFechaLetras1($ins["fecant"]));
                            if ($ins["camant2"] != '') {
                                $txt .= ', inscrito previamente en la ' . retornarRegistroMysqliApi($mysqli, 'bas_camaras', "id='" . $ins["camant2"] . "'", "nombre") . ' el ' . strtolower(\funcionesGenerales::mostrarFechaLetras1($ins["fecant2"]));
                                if ($ins["camant3"] != '') {
                                    $txt .= ', inscrito previamente en la ' . retornarRegistroMysqliApi($mysqli, 'bas_camaras', "id='" . $ins["camant3"] . "'", "nombre") . ' el ' . strtolower(\funcionesGenerales::mostrarFechaLetras1($ins["fecant3"]));
                                    if ($ins["camant4"] != '') {
                                        $txt .= ', inscrito previamente en la ' . retornarRegistroMysqliApi($mysqli, 'bas_camaras', "id='" . $ins["camant4"] . "'", "nombre") . ' el ' . strtolower(\funcionesGenerales::mostrarFechaLetras1($ins["fecant4"]));
                                        if ($ins["camant5"] != '') {
                                            $txt .= ', inscrito previamente en la ' . retornarRegistroMysqliApi($mysqli, 'bas_camaras', "id='" . $ins["camant5"] . "'", "nombre") . ' el ' . strtolower(\funcionesGenerales::mostrarFechaLetras1($ins["fecant5"]));
                                        }
                                    }
                                }
                            }
                            $txt .= ', ';
                        } else {
                            $txt .= ' ';
                        }
                        $txt .= 'se registró el acto administrativo No. ' . $ndocx . ' de ' . strtolower(\funcionesGenerales::mostrarFechaLetras1($ins["fdoc"])) . ', expedido por ';
                        $txt .= 'El Ministerio de Transporte que lo habilita para prestar el servicio público de transporte terrestre de pasajeros.';
                        $retorno["habilitacionesespeciales"][] = $txt;
                    }
                }
            }
        }

        //
        if ($estransportecarga == 'encontro' || $certificartransportecarga == 'encontro') {
            foreach ($retorno["inscripciones"] as $ins) {
                if ($ins["grupoacto"] == '066') {
                    if ($ins["freg"] < $_SESSION["generales"]["FECHA_CORTE_REVOCACION"] && $ins["crev"] == '1') {
                        $ins["crev"] = '0';
                    }
                    if ($ins["crev"] != '1' && $ins["crev"] != '9') {
                        if (trim($ins["ndocext"]) != '') {
                            $ndocx = $ins["ndocext"];
                        } else {
                            $ndocx = $ins["ndoc"];
                        }
                        if ($ins["idmunidoc"] != '') {
                            if (trim($ins["txoridoc"]) != '') {
                                $txtmunicipio = 'en ' . ucwords(strtolower(retornarNombreMunicipioMysqliApi($mysqli, $ins["idmunidoc"])));
                                $txt = 'Mediante inscripción No. ' . $ins["nreg"] . ' de ' . strtolower(\funcionesGenerales::mostrarFechaLetras1($ins["freg"]));
                                if ($ins["camant"] != '') {
                                    $txt .= ', inscrito previamente en la ' . retornarRegistroMysqliApi($mysqli, 'bas_camaras', "id='" . $ins["camant"] . "'", "nombre") . ' el ' . strtolower(\funcionesGenerales::mostrarFechaLetras1($ins["fecant"]));
                                    if ($ins["camant2"] != '') {
                                        $txt .= ', inscrito previamente en la ' . retornarRegistroMysqliApi($mysqli, 'bas_camaras', "id='" . $ins["camant2"] . "'", "nombre") . ' el ' . strtolower(\funcionesGenerales::mostrarFechaLetras1($ins["fecant2"]));
                                        if ($ins["camant3"] != '') {
                                            $txt .= ', inscrito previamente en la ' . retornarRegistroMysqliApi($mysqli, 'bas_camaras', "id='" . $ins["camant3"] . "'", "nombre") . ' el ' . strtolower(\funcionesGenerales::mostrarFechaLetras1($ins["fecant3"]));
                                            if ($ins["camant4"] != '') {
                                                $txt .= ', inscrito previamente en la ' . retornarRegistroMysqliApi($mysqli, 'bas_camaras', "id='" . $ins["camant4"] . "'", "nombre") . ' el ' . strtolower(\funcionesGenerales::mostrarFechaLetras1($ins["fecant4"]));
                                                if ($ins["camant5"] != '') {
                                                    $txt .= ', inscrito previamente en la ' . retornarRegistroMysqliApi($mysqli, 'bas_camaras', "id='" . $ins["camant5"] . "'", "nombre") . ' el ' . strtolower(\funcionesGenerales::mostrarFechaLetras1($ins["fecant5"]));
                                                }
                                            }
                                        }
                                    }
                                    $txt .= ', ';
                                } else {
                                    $txt .= ' ';
                                }
                                $txt .= 'se registró el acto administrativo No. ' . $ndocx . ' de ' . strtolower(\funcionesGenerales::mostrarFechaLetras1($ins["fdoc"])) . ', expedido por ';
                                $txt .= ucwords(strtolower($ins["txoridoc"])) . ' ' . $txtmunicipio . ', que lo habilita para prestar el servicio ';
                                $txt .= 'público de transporte terrestre automotor en la modalidad de carga.';
                                $retorno["habilitacionesespeciales"][] = $txt;
                            } else {
                                $txtmunicipio = 'expedido en ' . ucwords(strtolower(retornarNombreMunicipioMysqliApi($mysqli, $ins["idmunidoc"])));
                                $txt = 'Mediante inscripción No. ' . $ins["nreg"] . ' de ' . strtolower(\funcionesGenerales::mostrarFechaLetras1($ins["freg"]));
                                if ($ins["camant"] != '') {
                                    $txt .= ', inscrito previamente en la ' . retornarRegistroMysqliApi($mysqli, 'bas_camaras', "id='" . $ins["camant"] . "'", "nombre") . ' el ' . strtolower(\funcionesGenerales::mostrarFechaLetras1($ins["fecant"]));
                                    if ($ins["camant2"] != '') {
                                        $txt .= ', inscrito previamente en la ' . retornarRegistroMysqliApi($mysqli, 'bas_camaras', "id='" . $ins["camant2"] . "'", "nombre") . ' el ' . strtolower(\funcionesGenerales::mostrarFechaLetras1($ins["fecant2"]));
                                        if ($ins["camant3"] != '') {
                                            $txt .= ', inscrito previamente en la ' . retornarRegistroMysqliApi($mysqli, 'bas_camaras', "id='" . $ins["camant3"] . "'", "nombre") . ' el ' . strtolower(\funcionesGenerales::mostrarFechaLetras1($ins["fecant3"]));
                                            if ($ins["camant4"] != '') {
                                                $txt .= ', inscrito previamente en la ' . retornarRegistroMysqliApi($mysqli, 'bas_camaras', "id='" . $ins["camant4"] . "'", "nombre") . ' el ' . strtolower(\funcionesGenerales::mostrarFechaLetras1($ins["fecant4"]));
                                                if ($ins["camant5"] != '') {
                                                    $txt .= ', inscrito previamente en la ' . retornarRegistroMysqliApi($mysqli, 'bas_camaras', "id='" . $ins["camant5"] . "'", "nombre") . ' el ' . strtolower(\funcionesGenerales::mostrarFechaLetras1($ins["fecant5"]));
                                                }
                                            }
                                        }
                                    }
                                    $txt .= ', ';
                                } else {
                                    $txt .= ' ';
                                }
                                $txt .= 'se registró el acto administrativo No. ' . $ndocx . ' de ' . strtolower(\funcionesGenerales::mostrarFechaLetras1($ins["fdoc"])) . ', ' . $txtmunicipio;
                                $txt .= 'que lo habilita para prestar el servicio ';
                                $txt .= 'público de transporte terrestre automotor en la modalidad de carga.';
                                $retorno["habilitacionesespeciales"][] = $txt;
                            }
                        } else {
                            if (trim($ins["txoridoc"]) != '') {
                                $txt = 'Mediante inscripción No. ' . $ins["nreg"] . ' de ' . strtolower(\funcionesGenerales::mostrarFechaLetras1($ins["freg"]));
                                if ($ins["camant"] != '') {
                                    $txt .= ', inscrito previamente en la ' . retornarRegistroMysqliApi($mysqli, 'bas_camaras', "id='" . $ins["camant"] . "'", "nombre") . ' el ' . strtolower(\funcionesGenerales::mostrarFechaLetras1($ins["fecant"]));
                                    if ($ins["camant2"] != '') {
                                        $txt .= ', inscrito previamente en la ' . retornarRegistroMysqliApi($mysqli, 'bas_camaras', "id='" . $ins["camant2"] . "'", "nombre") . ' el ' . strtolower(\funcionesGenerales::mostrarFechaLetras1($ins["fecant2"]));
                                        if ($ins["camant3"] != '') {
                                            $txt .= ', inscrito previamente en la ' . retornarRegistroMysqliApi($mysqli, 'bas_camaras', "id='" . $ins["camant3"] . "'", "nombre") . ' el ' . strtolower(\funcionesGenerales::mostrarFechaLetras1($ins["fecant3"]));
                                            if ($ins["camant4"] != '') {
                                                $txt .= ', inscrito previamente en la ' . retornarRegistroMysqliApi($mysqli, 'bas_camaras', "id='" . $ins["camant4"] . "'", "nombre") . ' el ' . strtolower(\funcionesGenerales::mostrarFechaLetras1($ins["fecant4"]));
                                                if ($ins["camant5"] != '') {
                                                    $txt .= ', inscrito previamente en la ' . retornarRegistroMysqliApi($mysqli, 'bas_camaras', "id='" . $ins["camant5"] . "'", "nombre") . ' el ' . strtolower(\funcionesGenerales::mostrarFechaLetras1($ins["fecant5"]));
                                                }
                                            }
                                        }
                                    }
                                    $txt .= ', ';
                                } else {
                                    $txt .= ' ';
                                }
                                $txt .= 'se registró el acto administrativo No. ' . $ndocx . ' de ' . strtolower(\funcionesGenerales::mostrarFechaLetras1($ins["fdoc"])) . ', expedido por ';
                                $txt .= ucwords(strtolower($ins["txoridoc"])) . ', que lo habilita para prestar el servicio ';
                                $txt .= 'público de transporte terrestre automotor en la modalidad de carga.';
                                $retorno["habilitacionesespeciales"][] = $txt;
                            } else {
                                $txt = 'Mediante inscripción No. ' . $ins["nreg"] . ' de ' . strtolower(\funcionesGenerales::mostrarFechaLetras1($ins["freg"]));
                                if ($ins["camant"] != '') {
                                    $txt .= ', inscrito previamente en la ' . retornarRegistroMysqliApi($mysqli, 'bas_camaras', "id='" . $ins["camant"] . "'", "nombre") . ' el ' . strtolower(\funcionesGenerales::mostrarFechaLetras1($ins["fecant"]));
                                    if ($ins["camant2"] != '') {
                                        $txt .= ', inscrito previamente en la ' . retornarRegistroMysqliApi($mysqli, 'bas_camaras', "id='" . $ins["camant2"] . "'", "nombre") . ' el ' . strtolower(\funcionesGenerales::mostrarFechaLetras1($ins["fecant2"]));
                                        if ($ins["camant3"] != '') {
                                            $txt .= ', inscrito previamente en la ' . retornarRegistroMysqliApi($mysqli, 'bas_camaras', "id='" . $ins["camant3"] . "'", "nombre") . ' el ' . strtolower(\funcionesGenerales::mostrarFechaLetras1($ins["fecant3"]));
                                            if ($ins["camant4"] != '') {
                                                $txt .= ', inscrito previamente en la ' . retornarRegistroMysqliApi($mysqli, 'bas_camaras', "id='" . $ins["camant4"] . "'", "nombre") . ' el ' . strtolower(\funcionesGenerales::mostrarFechaLetras1($ins["fecant4"]));
                                                if ($ins["camant5"] != '') {
                                                    $txt .= ', inscrito previamente en la ' . retornarRegistroMysqliApi($mysqli, 'bas_camaras', "id='" . $ins["camant5"] . "'", "nombre") . ' el ' . strtolower(\funcionesGenerales::mostrarFechaLetras1($ins["fecant5"]));
                                                }
                                            }
                                        }
                                    }
                                    $txt .= ', ';
                                } else {
                                    $txt .= ' ';
                                }
                                $txt .= 'se registró el acto administrativo No. ' . $ndocx . ' de ' . strtolower(\funcionesGenerales::mostrarFechaLetras1($ins["fdoc"])) . ', ';
                                $txt .= 'que lo habilita para prestar el servicio ';
                                $txt .= 'público de transporte terrestre automotor en la modalidad de carga.';
                                $retorno["habilitacionesespeciales"][] = $txt;
                            }
                        }
                    }
                }
            }
        }

        //
        if ($estransporteespecial == 'encontro' || $certificartransporteespecial == 'encontro') {
            foreach ($retorno["inscripciones"] as $ins) {
                if ($ins["grupoacto"] == '067') {
                    if ($ins["freg"] < $_SESSION["generales"]["FECHA_CORTE_REVOCACION"] && $ins["crev"] == '1') {
                        $ins["crev"] = '0';
                    }
                    if ($ins["crev"] != '1' && $ins["crev"] != '9') {
                        if (trim($ins["ndocext"]) != '') {
                            $ndocx = $ins["ndocext"];
                        } else {
                            $ndocx = $ins["ndoc"];
                        }
                        if (trim($ins["txoridoc"]) != '') {
                            $txt = 'Mediante inscripción No. ' . $ins["nreg"] . ' de ' . strtolower(\funcionesGenerales::mostrarFechaLetras1($ins["freg"]));
                            if ($ins["camant"] != '') {
                                $txt .= ', inscrito previamente en la ' . retornarRegistroMysqliApi($mysqli, 'bas_camaras', "id='" . $ins["camant"] . "'", "nombre") . ' el ' . strtolower(\funcionesGenerales::mostrarFechaLetras1($ins["fecant"]));
                                if ($ins["camant2"] != '') {
                                    $txt .= ', inscrito previamente en la ' . retornarRegistroMysqliApi($mysqli, 'bas_camaras', "id='" . $ins["camant2"] . "'", "nombre") . ' el ' . strtolower(\funcionesGenerales::mostrarFechaLetras1($ins["fecant2"]));
                                    if ($ins["camant3"] != '') {
                                        $txt .= ', inscrito previamente en la ' . retornarRegistroMysqliApi($mysqli, 'bas_camaras', "id='" . $ins["camant3"] . "'", "nombre") . ' el ' . strtolower(\funcionesGenerales::mostrarFechaLetras1($ins["fecant3"]));
                                        if ($ins["camant4"] != '') {
                                            $txt .= ', inscrito previamente en la ' . retornarRegistroMysqliApi($mysqli, 'bas_camaras', "id='" . $ins["camant4"] . "'", "nombre") . ' el ' . strtolower(\funcionesGenerales::mostrarFechaLetras1($ins["fecant4"]));
                                            if ($ins["camant5"] != '') {
                                                $txt .= ', inscrito previamente en la ' . retornarRegistroMysqliApi($mysqli, 'bas_camaras', "id='" . $ins["camant5"] . "'", "nombre") . ' el ' . strtolower(\funcionesGenerales::mostrarFechaLetras1($ins["fecant5"]));
                                            }
                                        }
                                    }
                                }
                                $txt .= ', ';
                            } else {
                                $txt .= ' ';
                            }
                            $txt .= 'se registró el acto administrativo No. ' . $ndocx . ' de ' . strtolower(\funcionesGenerales::mostrarFechaLetras1($ins["fdoc"])) . ', expedido por ';
                            $txt .= ucwords(strtolower($ins["txoridoc"])) . ', que lo habilita para prestar el servicio ';
                            $txt .= 'público de transporte terrestre automotor especial.';
                            $retorno["habilitacionesespeciales"][] = $txt;
                        } else {
                            $txt = 'Mediante inscripción No. ' . $ins["nreg"] . ' DE FECHA ' . strtolower(\funcionesGenerales::mostrarFechaLetras1($ins["freg"]));
                            if ($ins["camant"] != '') {
                                $txt .= ', inscrito previamente en la ' . retornarRegistroMysqliApi($mysqli, 'bas_camaras', "id='" . $ins["camant"] . "'", "nombre") . ' el ' . strtolower(\funcionesGenerales::mostrarFechaLetras1($ins["fecant"]));
                                if ($ins["camant2"] != '') {
                                    $txt .= ', inscrito previamente en la ' . retornarRegistroMysqliApi($mysqli, 'bas_camaras', "id='" . $ins["camant2"] . "'", "nombre") . ' el ' . strtolower(\funcionesGenerales::mostrarFechaLetras1($ins["fecant2"]));
                                    if ($ins["camant3"] != '') {
                                        $txt .= ', inscrito previamente en la ' . retornarRegistroMysqliApi($mysqli, 'bas_camaras', "id='" . $ins["camant3"] . "'", "nombre") . ' el ' . strtolower(\funcionesGenerales::mostrarFechaLetras1($ins["fecant3"]));
                                        if ($ins["camant4"] != '') {
                                            $txt .= ', inscrito previamente en la ' . retornarRegistroMysqliApi($mysqli, 'bas_camaras', "id='" . $ins["camant4"] . "'", "nombre") . ' el ' . strtolower(\funcionesGenerales::mostrarFechaLetras1($ins["fecant4"]));
                                            if ($ins["camant5"] != '') {
                                                $txt .= ', inscrito previamente en la ' . retornarRegistroMysqliApi($mysqli, 'bas_camaras', "id='" . $ins["camant5"] . "'", "nombre") . ' el ' . strtolower(\funcionesGenerales::mostrarFechaLetras1($ins["fecant5"]));
                                            }
                                        }
                                    }
                                }
                                $txt .= ', ';
                            } else {
                                $txt .= ' ';
                            }
                            $txt .= 'se registró el acto administrativo No. ' . $ndocx . ' de ' . strtolower(\funcionesGenerales::mostrarFechaLetras1($ins["fdoc"])) . ' ';
                            $txt .= 'que lo habilita para prestar el servicio ';
                            $txt .= 'público de transporte terrestre automotor especial.';
                            $retorno["habilitacionesespeciales"][] = $txt;
                        }
                    }
                }
            }
        }

        //
        if ($estransportecarga == 'falta') {
            foreach ($_SESSION["maestrocertificas"] as $cx) {
                if ($cx["clase"] == 'CRT-TRACAR') {
                    if (isset($retorno["crtsii"][$cx["id"]]) && $retorno["crtsii"][$cx["id"]] != '') {
                        $retorno["habilitacionesespeciales"][] = $retorno["crtsii"][$cx["id"]];
                        $estransportecarga = 'encontro';
                    }
                }
            }
            if ($estransportecarga == 'falta') {
                if ($retorno["organizacion"] == '01') {
                    $txt = 'La persona natural ';
                } else {
                    $txt = 'La persona jurídica ';
                }
                $txt .= 'no ha inscrito el acto administrativo que lo habilita para prestar el servicio público de transporte automotor en la modalidad de carga.';
                $retorno["habilitacionesespeciales"][] = $txt;
            }
        }

        //
        if ($certificargrupo093 == 'encontro') {
            foreach ($retorno["inscripciones"] as $ins) {
                if ($ins["grupoacto"] == '093') {
                    if ($ins["freg"] < $_SESSION["generales"]["FECHA_CORTE_REVOCACION"] && $ins["crev"] == '1') {
                        $ins["crev"] = '0';
                    }

                    //
                    $imp = 'no';
                    if (trim((string) $ins["flim"])) {
                        $imp = 'si';
                    } else {
                        if (trim((string) $ins["flim"]) != '' && $ins["flim"] < date("Ymd")) {
                            $imp = 'si';
                        }
                    }

                    //
                    if ($imp == 'si') {
                        if ($ins["crev"] != '1' && $ins["crev"] != '9') {
                            if (trim($ins["ndocext"]) != '') {
                                $ndocx = $ins["ndocext"];
                            } else {
                                $ndocx = $ins["ndoc"];
                            }
                            if (trim($ins["txoridoc"]) != '') {
                                $txt = 'Mediante inscripción No. ' . $ins["nreg"] . ' de ' . strtolower(\funcionesGenerales::mostrarFechaLetras1($ins["freg"]));
                                if ($ins["camant"] != '') {
                                    $txt .= ', inscrito previamente en la ' . retornarRegistroMysqliApi($mysqli, 'bas_camaras', "id='" . $ins["camant"] . "'", "nombre") . ' el ' . strtolower(\funcionesGenerales::mostrarFechaLetras1($ins["fecant"]));
                                    if ($ins["camant2"] != '') {
                                        $txt .= ', inscrito previamente en la ' . retornarRegistroMysqliApi($mysqli, 'bas_camaras', "id='" . $ins["camant2"] . "'", "nombre") . ' el ' . strtolower(\funcionesGenerales::mostrarFechaLetras1($ins["fecant2"]));
                                        if ($ins["camant3"] != '') {
                                            $txt .= ', inscrito previamente en la ' . retornarRegistroMysqliApi($mysqli, 'bas_camaras', "id='" . $ins["camant3"] . "'", "nombre") . ' el ' . strtolower(\funcionesGenerales::mostrarFechaLetras1($ins["fecant3"]));
                                            if ($ins["camant4"] != '') {
                                                $txt .= ', inscrito previamente en la ' . retornarRegistroMysqliApi($mysqli, 'bas_camaras', "id='" . $ins["camant4"] . "'", "nombre") . ' el ' . strtolower(\funcionesGenerales::mostrarFechaLetras1($ins["fecant4"]));
                                                if ($ins["camant5"] != '') {
                                                    $txt .= ', inscrito previamente en la ' . retornarRegistroMysqliApi($mysqli, 'bas_camaras', "id='" . $ins["camant5"] . "'", "nombre") . ' el ' . strtolower(\funcionesGenerales::mostrarFechaLetras1($ins["fecant5"]));
                                                }
                                            }
                                        }
                                    }
                                    $txt .= ', ';
                                } else {
                                    $txt .= ' ';
                                }
                                $txt .= 'se registró el acto administrativo No. ' . $ndocx . ' de ' . strtolower(\funcionesGenerales::mostrarFechaLetras1($ins["fdoc"])) . ', expedido por ';
                                $txt .= ucwords(strtolower($ins["txoridoc"])) . ',  a través del cual se ' . $ins["not"];
                                $retorno["habilitacionesespeciales"][] = $txt;
                            } else {
                                $txt = 'Mediante inscripción No. ' . $ins["nreg"] . ' DE FECHA ' . strtolower(\funcionesGenerales::mostrarFechaLetras1($ins["freg"]));
                                if ($ins["camant"] != '') {
                                    $txt .= ', inscrito previamente en la ' . retornarRegistroMysqliApi($mysqli, 'bas_camaras', "id='" . $ins["camant"] . "'", "nombre") . ' el ' . strtolower(\funcionesGenerales::mostrarFechaLetras1($ins["fecant"]));
                                    if ($ins["camant2"] != '') {
                                        $txt .= ', inscrito previamente en la ' . retornarRegistroMysqliApi($mysqli, 'bas_camaras', "id='" . $ins["camant2"] . "'", "nombre") . ' el ' . strtolower(\funcionesGenerales::mostrarFechaLetras1($ins["fecant2"]));
                                        if ($ins["camant3"] != '') {
                                            $txt .= ', inscrito previamente en la ' . retornarRegistroMysqliApi($mysqli, 'bas_camaras', "id='" . $ins["camant3"] . "'", "nombre") . ' el ' . strtolower(\funcionesGenerales::mostrarFechaLetras1($ins["fecant3"]));
                                            if ($ins["camant4"] != '') {
                                                $txt .= ', inscrito previamente en la ' . retornarRegistroMysqliApi($mysqli, 'bas_camaras', "id='" . $ins["camant4"] . "'", "nombre") . ' el ' . strtolower(\funcionesGenerales::mostrarFechaLetras1($ins["fecant4"]));
                                                if ($ins["camant5"] != '') {
                                                    $txt .= ', inscrito previamente en la ' . retornarRegistroMysqliApi($mysqli, 'bas_camaras', "id='" . $ins["camant5"] . "'", "nombre") . ' el ' . strtolower(\funcionesGenerales::mostrarFechaLetras1($ins["fecant5"]));
                                                }
                                            }
                                        }
                                    }
                                    $txt .= ', ';
                                } else {
                                    $txt .= ' ';
                                }
                                $txt .= 'se registró el acto administrativo No. ' . $ndocx . ' de ' . strtolower(\funcionesGenerales::mostrarFechaLetras1($ins["fdoc"])) . ' ';
                                $txt .= 'a través del cual se ' . $ins["not"];
                                $retorno["habilitacionesespeciales"][] = $txt;
                            }
                        }
                    }
                }
            }
        }

        $retorno["nombrebase64decodificado"] = $retorno["nombre"];
        $retorno["nombrebase64"] = base64_encode($retorno["nombre"]);
        $retorno["siglabase64decodificada"] = $retorno["sigla"];
        $retorno["siglabase64"] = base64_encode($retorno["sigla"]);

        // Cierra la conexión con MYSQL
        if ($dbx === null) {
            $mysqli->close();
        }

        //
        if ($genlog == 'si') {
            \logApi::general2($nameLog, $retorno["matricula"], 'termino lectura expediente');
        }

        //
        if (!isset($reg["hashcontrol"])) {
            $reg["hashcontrol"] = '';
        }
        $retorno["hashcontrol"] = $reg["hashcontrol"];

        // ********************************************************************************************************************** //
        // Calculo del hash de control nuevo
        // ********************************************************************************************************************** //
        $retorno["hashcontrolnuevo"] = date("Ymd") . '|' . \funcionesRegistrales::calcularHashMercantil($mysqli, $retorno["matricula"], $retorno);

        //
        return $retorno;
    }
    
    /**
     * 
     * @param type $dbx
     * @param type $mat
     * @return bool|string|int
     */
    public static function retornarExpedienteMercantilCorto($dbx = null, $mat = '') {
        
        /*
        $_SESSION["jsonsalida"]["nombre"] = $arrTem["nombre"];
        $_SESSION["jsonsalida"]["estadomatricula"] = $arrTem["estadomatricula"];
        $_SESSION["jsonsalida"]["fechamatricula"] = $arrTem["fechamatricula"];
        $_SESSION["jsonsalida"]["fecharenovacion"] = $arrTem["fecharenovacion"];
        $_SESSION["jsonsalida"]["ultanoren"] = $arrTem["ultanoren"];
        $_SESSION["jsonsalida"]["fechacancelacionn"] = $arrTem["fechacancelacion"];
        $_SESSION["jsonsalida"]["fechavencimiento"] = $arrTem["fechavencimiento"];
        $_SESSION["jsonsalida"]["estadisuelta"] = $arrTem["estadisuelta"];
        $_SESSION["jsonsalida"]["motivocancelacion"] = $arrTem["motivocancelacion"];
        $_SESSION["jsonsalida"]["descripcionmotivocancelacion"] = $arrTem["descripcionmotivocancelacion"];
        $_SESSION["jsonsalida"]["fechaliquidacion"] = $arrTem["fechaliquidacion"];
        $_SESSION["jsonsalida"]["disueltaporvencimiento"] = $arrTem["disueltaporvencimiento"];
        $_SESSION["jsonsalida"]["disueltaporacto510"] = $arrTem["disueltaporacto510"];
        $_SESSION["jsonsalida"]["fechaacto510"] = $arrTem["fechaacto510"];
        $_SESSION["jsonsalida"]["fechaacto511"] = $arrTem["fechaacto511"];
        $_SESSION["jsonsalida"]["perdidacalidadcomerciante"] = $arrTem["perdidacalidadcomerciante"];
        $_SESSION["jsonsalida"]["fechaperdidacalidadcomerciante"] = $arrTem["fechaperdidacalidadcomerciante"];
        $_SESSION["jsonsalida"]["fechareactivacioncalidadcomerciante"] = $arrTem["fechareactivacioncalidadcomerciante"];
        */
        
        //
        if ($mat == '') {
            return false;
        }
        
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRues.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/myErrorHandler.php');

        set_error_handler('myErrorHandler');

        $nameLog = 'retornarExpedienteMercantilCorto_' . $mat;
        $genlog = 'no';

        if ($genlog == 'si') {
            \logApi::general2($nameLog, $mat, 'Inicia lectura expediente');
        }

        // ********************************************************************************** //
        // Instancia la BD si no existe
        // ********************************************************************************** //
        if ($dbx === null) {
            $mysqli = conexionMysqliApi();
            if ($mysqli === false) {
                $_SESSION["generales"]["mensajerror"] = 'Error coenctando con la BD';
                return false;
            }
        } else {
            $mysqli = $dbx;
        }

        // ********************************************************************************** //
        // Define constantes y variables de control
        // ********************************************************************************** //
        $_SESSION["generales"]["fcorte"] = retornarRegistroMysqliApi($mysqli, 'mreg_cortes_renovacion', "ano='" . date("Y") . "'", "corte");
        $_SESSION["generales"]["fcortemesdia"] = substr($_SESSION["generales"]["fcorte"], 4, 4);
        $_SESSION["generales"]["FECHA_CORTE_REVOCACION"] = '20170101';

        // ********************************************************************************** //
        // carga maestro de actos
        // ********************************************************************************** //
        $_SESSION["maestroactos"] = array();
        $temx = retornarRegistrosMysqliApi($mysqli, "mreg_actos", "1=1", "idlibro,idacto");
        foreach ($temx as $x) {
            $ind = $x["idlibro"] . '-' . $x["idacto"];
            $_SESSION["maestroactos"][$ind] = $x;
        }
        unset($temx);

        // ********************************************************************************** //
        // carga maestro de vínculos
        // ********************************************************************************** //
        $_SESSION["maestrovinculos"] = array();
        $temx = retornarRegistrosMysqliApi($mysqli, "mreg_codvinculos", "1=1", "id");
        foreach ($temx as $x) {
            $_SESSION["maestrovinculos"][$x["id"]] = $x;
        }
        unset($temx);

        // ********************************************************************************** //
        // carga maestro de clases de vínculos
        // ********************************************************************************** //
        $_SESSION["clasevinculo"] = array();
        $temx = retornarRegistrosMysqliApi($mysqli, "mreg_clasevinculos", "1=1", "id");
        foreach ($temx as $x) {
            $_SESSION["clasevinculo"][$x["id"]] = $x["descripcion"];
        }
        unset($temx);

        // ********************************************************************************** //
        // carga maestro de certificads
        // ********************************************************************************** //
        $_SESSION["maestrocertificas"] = array();
        $temx = retornarRegistrosMysqliApi($mysqli, "mreg_codigos_certificas", "1=1", "id");
        foreach ($temx as $x) {
            $_SESSION["maestrocertificas"][$x["id"]] = $x;
        }
        unset($temx);

        //
        if (empty($serviciosMatriculaE)) {
            $serviciosRenovacion = array();
            $serviciosMatricula = array();
            $serviciosAfiliacion = array();
            $temx = retornarRegistrosMysqliApi($dbx, "mreg_servicios", "tipoingreso IN ('02','03','12','13')", "idservicio");
            foreach ($temx as $x1) {
                if ($x1["tipoingreso"] == '03' || $x1["tipoingreso"] == '13') {
                    $serviciosRenovacion[$x1["idservicio"]] = $x1["idservicio"];
                }
                if ($x1["tipoingreso"] == '02' || $x1["tipoingreso"] == '12') {
                    $serviciosMatricula[$x1["idservicio"]] = $x1["idservicio"];
                }
            }
            unset($temx);
            $temx = retornarRegistrosMysqliApi($dbx, "mreg_servicios", "1=1", "idservicio");
            foreach ($temx as $x1) {
                if ($x1["grupoventas"] == '02') {
                    $serviciosAfiliacion[$x1["idservicio"]] = $x1["idservicio"];
                }
            }
            unset($temx);
        } else {
            $serviciosMatricula = $serviciosMatriculaE;
            $serviciosRenovacion = $serviciosRenovacionE;
            $serviciosAfiliacion = $serviciosAfiliacionE;
        }

        // ************************************************************************************** //
        // Si el sistema registro es SII
        // ************************************************************************************** //
        // Configuracion del QUERY Inicial
        $siquery = 'no';
        $query = '';
        if ($mat != '') {
            $query = "matricula='" . ltrim($mat, "0") . "'";
            $siquery = 'si';
        }

        if ($siquery == 'no') {
            if ($idclase != '') {
                $query = "idclase='" . $idclase . "' and ";
                $query .= "numid='" . trim($numid) . "'";
                if ($tipoconsulta == 'A') {
                    $query .= " and ctrestmatricula IN ('MA','IA','MI','II')";
                }
                if ($tipoconsulta == 'C') {
                    $query .= " and ctrestmatricula NOT IN ('MA','IA','MI','II')";
                }
                $siquery = 'si';
            }
        }
        if ($siquery == 'no') {
            if ($numid != '') {
                $query = "(numid='" . ltrim($numid, "0") . "' or nit='" . ltrim($numid, "0") . "')";
                if ($tipoconsulta == 'A') {
                    $query .= " and ctrestmatricula IN ('MA','IA','MI','II')";
                }
                if ($tipoconsulta == 'C') {
                    $query .= " and ctrestmatricula NOT IN ('MA','IA','MI','II')";
                }
                $siquery = 'si';
            }
        }

        if ($siquery == 'no') {
            if ($namex != '') {
                $query = "razonsocial='" . $namex . "'";
                $siquery = 'si';
            }
        }


        // Lectura del registro principal
        $reg = retornarRegistroMysqliApi($mysqli, 'mreg_est_inscritos', $query, '*', 'U');
        if ($reg === false) {
            if ($dbx == null) {
                $mysqli->close();
            }
            $_SESSION["generales"]["mensajerror"] = '1.- Error leyendo la tabla mreg_est_inscritos';
            return false;
        }
        if (empty($reg)) {
            if ($dbx == null) {
                $mysqli->close();
            }
            $_SESSION["generales"]["mensajerror"] = '';
            return 0;
        }

        if ($genlog == 'si') {
            \logApi::general2($nameLog, $mat, 'Leyo mreg_est_inscritos');
        }

        // ****************************************************************************** //
        // Validaciones
        // ****************************************************************************** //
        $txtErrores = '';
        if (strlen(trim($reg["fecmatricula"])) != 8 || !\funcionesGenerales::validarFecha($reg["fecmatricula"])) {
            \logApi::general2($nameLog, $mat, 'Fecha de matricula erronea');
            $txtErrores .= 'Se detecta error en fecha de matrícula para el expediente ' . $mat . "\r\n";
            // return false;
        }

        if (strlen(trim($reg["fecrenovacion"])) != 8 || !\funcionesGenerales::validarFecha($reg["fecrenovacion"])) {
            \logApi::general2($nameLog, $mat, 'Fecha de renovacion erronea');
            $txtErrores .= 'Se detecta error en fecha de renovación para el expediente ' . $mat . "\r\n";
            // return false;
        }
        if (trim($reg["fecrenant"]) != '' && !\funcionesGenerales::validarFecha(trim($reg["fecrenant"]))) {
            $txtErrores .= 'Se detecta error en fecha de renovación (camara anterior) para el expediente ' . $mat . "\r\n";
            \logApi::general2($nameLog, $mat, 'Fecha de renovacion en camara anterior erronea');
            // return false;
        }
        // \funcionesgenerales::enviarCorreoError($txtErrores);
        //
        $reg["numid"] = trim(str_replace(array(".", ",", "-", " "), "", $reg["numid"]));
        $reg["nit"] = trim(str_replace(array(".", ",", "-", " "), "", $reg["nit"]));

        // Armado del arreglo de respuesta
        $retorno = array();
        $retorno["matricula"] = $reg["matricula"];
        $retorno["proponente"] = $reg["proponente"];
        $retorno["numerorecibo"] = $reg["numrecibo"];
        $retorno["extinciondominio"] = '';
        $retorno["extinciondominiofechainicio"] = '';
        $retorno["extinciondominiofechafinal"] = '';
        $retorno["ctrcontrolaccesopublico"] = '';

        //
        $retorno["complementorazonsocial"] = '';
        if (isset($reg["complementorazonsocial"])) {
            $retorno["complementorazonsocial"] = stripslashes(\funcionesGenerales::restaurarEspeciales((string) $reg["complementorazonsocial"]));
        }
        // $retorno["nombre"] = stripslashes(\funcionesGenerales::restaurarEspeciales($reg["razonsocial"]));        
        $retorno["nombre"] = str_replace("¥", "Ñ", stripslashes(\funcionesGenerales::restaurarEspeciales((string) $reg["razonsocial"])));
        $retorno["nombre"] = trim($retorno["nombre"]);
        $retorno["nuevonombre"] = '';
        $retorno["nombrebase64"] = '';
        $retorno["nombrebase64decodificado"] = '';
        $retorno["sigla"] = str_replace("¥", "Ñ", stripslashes(\funcionesGenerales::restaurarEspeciales((string) $reg["sigla"])));
        $retorno["siglabase64"] = '';
        $retorno["siglabase64decodificada"] = '';

        $temx = retornarRegistrosMysqliApi($mysqli, 'mreg_est_inscritos_campos', "matricula='" . ltrim($retorno["matricula"], "0") . "'", "campo");
        foreach ($temx as $tx) {
            if (trim($tx["contenido"]) != '') {
                if ($tx["campo"] == 'nombrebase64') {
                    $retorno[$tx["campo"]] = $tx["contenido"];
                }
                if ($tx["campo"] == 'siglabase64') {
                    $retorno[$tx["campo"]] = $tx["contenido"];
                }
            }
        }

        if ($retorno["nombrebase64"] != '') {
            $retorno["nombrebase64decodificado"] = base64_decode($retorno["nombrebase64"]);
        }
        if ($retorno["siglabase64"] != '') {
            $retorno["siglabase64decodificada"] = base64_decode($retorno["siglabase64"]);
        }

        if ($retorno["nombrebase64decodificado"] != '' && $retorno["nombrebase64decodificado"] != $retorno["nombre"]) {
            $retorno["nombre"] = $retorno["nombrebase64decodificado"];
        }

        if ($retorno["siglabase64decodificada"] != '' && $retorno["siglabase64decodificada"] != $retorno["sigla"]) {
            $retorno["sigla"] = $retorno["siglabase64decodificada"];
        }

        $retorno["nombre"] = \funcionesGenerales::borrarpalabrasAutomaticas($retorno["nombre"], $retorno["complementorazonsocial"]);

        $retorno["ape1"] = str_replace("¥", "Ñ", stripslashes(\funcionesGenerales::restaurarEspeciales((string) $reg["apellido1"])));
        $retorno["ape2"] = str_replace("¥", "Ñ", stripslashes(\funcionesGenerales::restaurarEspeciales((string) $reg["apellido2"])));
        $retorno["nom1"] = str_replace("¥", "Ñ", stripslashes(\funcionesGenerales::restaurarEspeciales((string) $reg["nombre1"])));
        $retorno["nom2"] = str_replace("¥", "Ñ", stripslashes(\funcionesGenerales::restaurarEspeciales((string) $reg["nombre2"])));
        $retorno["nombrerues"] = $retorno["nombre"];

        if ($reg["organizacion"] == '01') {
            $nomx = '';
            if (trim($retorno["ape1"]) != '') {
                $nomx .= trim($retorno["ape1"]);
            }
            if (trim($retorno["ape2"]) != '') {
                $nomx .= ' ' . trim($retorno["ape2"]);
            }
            if (trim($retorno["nom1"]) != '') {
                $nomx .= ' ' . trim($retorno["nom1"]);
            }
            if (trim($retorno["nom2"]) != '') {
                $nomx .= ' ' . trim($retorno["nom2"]);
            }
            if (trim($nomx) != '') {
                $retorno["nombre"] = $nomx;
                $retorno["nombrerues"] = $nomx;
            }
        }

        $retorno["tipoidentificacion"] = $reg["idclase"];
        $retorno["identificacion"] = $reg["numid"];
        $retorno["sexo"] = $reg["sexo"];
        $retorno["etnia"] = '';
        $retorno["emprendimientosocial"] = '';
        $retorno["idmunidoc"] = $reg["idmunidoc"];
        $retorno["fechanacimiento"] = $reg["fechanacimiento"];
        $retorno["fecexpdoc"] = $reg["fecexpdoc"];
        $retorno["paisexpdoc"] = $reg["paisexpdoc"];
        $retorno["nit"] = $reg["nit"];
        $retorno["nitsindv"] = '';
        $retorno["dv"] = '';
        if (trim($retorno["nit"]) != '') {
            $nit1 = sprintf("%020s", trim((string) $retorno["nit"]));
            $retorno["nitsindv"] = ltrim(substr($nit1, 0, 19), "0");
            $retorno["dv"] = substr($nit1, 19, 1);
        }
        $retorno["estadonit"] = $reg["estadonit"];
        $retorno["admondian"] = $reg["admondian"];
        $retorno["prerut"] = $reg["prerut"];
        $retorno["nacionalidad"] = $reg["nacionalidad"];

        // 2017-05-31
        $retorno["idetripaiori"] = $reg["idetripaiori"]; // Nuevo campo circular 002
        $retorno["paiori"] = $reg["paiori"]; // Nuevo campo circular 002
        $retorno["idetriextep"] = $reg["idetriextep"]; // Nuevo campo circular 002
        $retorno["ideext"] = $reg["numidextenso"]; // Nuevo campo circular 002


        $retorno["fechamatricula"] = trim((string) $reg["fecmatricula"]);
        $retorno["fecharenovacion"] = trim((string) $reg["fecrenovacion"]);
        $retorno["fecharenovacioninscritos"] = trim((string) $reg["fecrenovacion"]);
        $retorno["fechavencimiento"] = trim((string) $reg["fecvigencia"]);
        $retorno["fechavencimiento1"] = '';
        $retorno["fechavencimiento2"] = '';
        $retorno["fechavencimiento3"] = '';
        $retorno["fechavencimiento4"] = '';
        $retorno["fechavencimiento5"] = '';
        $retorno["fechadisolucioncontrolbeneficios1756"] = '';
        $retorno["fechareactivacioncontrolbeneficios1756"] = '';

        //
        $rvs = retornarRegistrosMysqliApi($mysqli, 'mreg_est_vigencias', "matricula='" . $reg["matricula"] . "'", "fecha");
        $ivs = 0;
        if ($rvs && !empty($rvs)) {
            foreach ($rvs as $rv) {
                $ivs++;
                $retorno["fechavencimiento" . $ivs] = $rv["fecha"];
            }
        }

        //
        $retorno["ultanoren"] = trim($reg["ultanoren"]);
        $retorno["ultanoreninscritos"] = trim($reg["ultanoren"]);

        if (strlen($retorno["fecharenovacion"]) != 8) {
            $retorno["fecharenovacion"] = $retorno["fechamatricula"];
        }
        if ($retorno["ultanoren"] == '') {
            $retorno["ultanoren"] = substr($retorno["fechamatricula"], 0, 4);
            $retorno["ultanoreninscritos"] = trim($reg["ultanoren"]);
        }

        $retorno["obligadorenovar"] = '';
        if (isset($reg["obligadorenovar"])) {
            $retorno["obligadorenovar"] = $reg["obligadorenovar"];
        }

        // Se encuentran al momento de armar la lista de inscripciones        
        $retorno["fechaconstitucion"] = $reg["fecconstitucion"];
        $retorno["fechadisolucion"] = $reg["fecdisolucion"];
        $retorno["fechacancelacion"] = $reg["feccancelacion"];
        $retorno["motivocancelacion"] = $reg["motivocancelacion"];
        $retorno["descripcionmotivocancelacion"] = '';
        $retorno["fechaliquidacion"] = $reg["fecliquidacion"];
        $retorno["disueltaporvencimiento"] = '';
        $retorno["disueltaporacto510"] = '';
        $retorno["fechaacto510"] = '';
        $retorno["fechaacto511"] = '';
        $retorno["perdidacalidadcomerciante"] = '';
        $retorno["fechaperdidacalidadcomerciante"] = '';
        $retorno["fechareactivacioncalidadcomerciante"] = '';
        $retorno["estadotipoliquidacion"] = $reg["estadotipoliquidacion"];
        $retorno["empresafamiliar"] = $reg["empresafamiliar"];
        $retorno["procesosinnovacion"] = $reg["procesosinnovacion"];
        $retorno["estadisuelta"] = '';
        $retorno["norenovado"] = '';
        // 
        if (
                trim($retorno["fechavencimiento"]) != '' &&
                $retorno["fechavencimiento"] != '99999999' && $retorno["fechavencimiento"] != '99999998'
        ) {
            if ($retorno["fechavencimiento"] < date("Ymd")) {
                $retorno["disueltaporvencimiento"] = 'si';
                $retorno["fechadisolucion"] = $retorno["fechavencimiento"];
            }
        }

        //
        $retorno["certificardesde"] = $reg["ctrcertificardesde"];
        $retorno["estadomatricula"] = $reg["ctrestmatricula"];
        $retorno["estadoactiva"] = $reg["ctrestadoactiva"];
        $retorno["estadopreoperativa"] = $reg["ctrestadopreoperativa"];
        $retorno["estadoconcordato"] = $reg["ctrconcordato"];
        $retorno["estadointervenida"] = $reg["ctrestadointervenida"];
        $retorno["estadodisuelta"] = $reg["ctrestadodisuelta"];
        $retorno["estadoreestructuracion"] = $reg["ctrestadoenreestructuracion"];
        $retorno["estadodatosmatricula"] = $reg["ctrestdatos"];
        $retorno["estadoproponente"] = trim((string) $reg["ctrestproponente"]);
        $retorno["estadocapturado"] = trim((string) $reg["ctrestadocapturado"]);
        $retorno["estadocapturadootros"] = trim((string) $reg["ctrestadocapturadootros"]);
        $retorno["cantest"] = intval($reg["cantest"]);

        //
        if (!isset($reg["pendiente_ajuste_nuevo_formato"])) {
            $reg["pendiente_ajuste_nuevo_formato"] = '';
        }
        if (!isset($reg["fecha_pendiente_ajuste_nuevo_formato"])) {
            $reg["fecha_pendiente_ajuste_nuevo_formato"] = '';
        }

        //
        $retorno["codigosbarras"] = 0; // Se arma al encontrar la lista de códigos de barras
        $retorno["pendiente_ajuste_nuevo_formato"] = $reg["pendiente_ajuste_nuevo_formato"];
        $retorno["fecha_pendiente_ajuste_nuevo_formato"] = $reg["fecha_pendiente_ajuste_nuevo_formato"];

        //
        if (defined('FECHA_INICIO_NUEVO_CERTIFICADO_CERMAT') && FECHA_INICIO_NUEVO_CERTIFICADO_CERMAT) {
            $finimat = FECHA_INICIO_NUEVO_CERTIFICADO_CERMAT;
        } else {
            $finimat = '20200101';
        }
        if (defined('FECHA_INICIO_NUEVO_CERTIFICADO_CEREXI') && FECHA_INICIO_NUEVO_CERTIFICADO_CEREXI) {
            $finiexi = FECHA_INICIO_NUEVO_CERTIFICADO_CERMAT;
        } else {
            $finiexi = '20200101';
        }
        if (defined('FECHA_INICIO_NUEVO_CERTIFICADO_CERESADL') && FECHA_INICIO_NUEVO_CERTIFICADO_CERESADL) {
            $finiesadl = FECHA_INICIO_NUEVO_CERTIFICADO_CERESADL;
        } else {
            $finiesadl = '20200101';
        }
        
        if ($reg["organizacion"] == '01' || $reg["organizacion"] == '02' || $reg["categoria"] == '2' || $reg["categoria"] == '3') {
            if ($reg["fecmatricula"] >= $finimat) {
                $reg["pendiente_ajuste_nuevo_formato"] = 'R';
                $reg["fecha_pendiente_ajuste_nuevo_formato"] = $reg["fecmatricula"];
            }
        }

        if ($reg["organizacion"] > '02' && $reg["organizacion"] != '12' && $reg["organizacion"] != '14' && $reg["categoria"] == '1') {
            if ($reg["fecmatricula"] >= $finiexi) {
                $reg["pendiente_ajuste_nuevo_formato"] = 'R';
                $reg["fecha_pendiente_ajuste_nuevo_formato"] = $reg["fecmatricula"];
            }
        }

        if (($reg["organizacion"] == '12' || $reg["organizacion"] == '14') && $reg["categoria"] == '1') {
            if ($reg["fecmatricula"] >= $finiesadl) {
                $reg["pendiente_ajuste_nuevo_formato"] = 'R';
                $reg["fecha_pendiente_ajuste_nuevo_formato"] = $reg["fecmatricula"];
            }
        }

        //
        $retorno["embargos"] = ''; // Se arma al encontrar la lista de embargos
        $retorno["embargostramite"] = ''; // Se arma al encontrar la lista de embargos
        $retorno["recursostramite"] = ''; // Sea arma al encontrar la lista de códigos de barras
        $retorno["tamanoempresa"] = $reg["tamanoempresa"];
        $retorno["emprendedor28"] = $reg["emprendedor28"];
        $retorno["pemprendedor28"] = doubleval($reg["pemprendedor28"]);
        $retorno["organizacion"] = $reg["organizacion"];
        $retorno["organizaciontexto"] = retornarRegistroMysqliApi($mysqli, 'bas_organizacionjuridica', "id='" . $reg["organizacion"] . "'", "descripcion");
        $retorno["categoria"] = $reg["categoria"];
        $retorno["categoriatexto"] = retornarRegistroMysqliApi($mysqli, 'bas_categorias', "id='" . $reg["categoria"] . "'", "descripcion");
        $retorno["naturaleza"] = $reg["naturaleza"];
        if ($reg["naturaleza"] == '') {
            $retorno["naturaleza"] = '0';
        }
        $retorno["imppredil"] = '';
        $retorno["impexp"] = $reg["ctrimpexp"];
        $retorno["tipopropiedad"] = $reg["ctrtipopropiedad"];
        $retorno["tipolocal"] = $reg["ctrtipolocal"];
        $retorno["tipogruemp"] = $reg["tipogruemp"]; // Nuevo campo circular 002  
        $retorno["nombregruemp"] = $reg["nombregruemp"]; // Nuevo campo circular 002 
        //
        $retorno["vigilanciasuperfinanciera"] = $reg["vigilanciasuperfinanciera"];
        $retorno["vigcontrol"] = $reg["vigcontrol"];
        $retorno["fecperj"] = $reg["fecperj"];
        $retorno["idorigenperj"] = $reg["otorgaperj"];
        $retorno["origendocconst"] = $reg["origendocconst"];
        $retorno["numperj"] = $reg["numperj"];
        $retorno["patrimonio"] = $reg["patrimonio"];
        $retorno["vigifecini"] = $reg["vigifecini"];
        $retorno["vigifecfin"] = $reg["vigifecfin"];
        $retorno["clasegenesadl"] = $reg["ctrclasegenesadl"];
        $retorno["claseespesadl"] = $reg["ctrclaseespeesadl"];
        $retorno["claseeconsoli"] = $reg["ctrclaseeconsoli"];
        $retorno["econmixta"] = $reg["ctreconmixta"];
        $retorno["condiespe2219"] = '';

        $retorno["ctrderpub"] = $reg["ctrderpub"]; // Nuevo campo circular 002
        $retorno["ctrcodcoop"] = $reg["ctrcodcoop"]; // Nuevo campo circular 002
        $retorno["ctrcodotras"] = $reg["ctrcodotras"]; // Nuevo campo circular 002

        $retorno["ctresacntasociados"] = $reg["ctresacntasociados"]; // Nuevo campo circular 002  
        $retorno["ctresacntmujeres"] = $reg["ctresacntmujeres"]; // Nuevo campo circular 002  
        $retorno["ctresacnthombres"] = $reg["ctresacnthombres"]; // Nuevo campo circular 002  
        $retorno["ctresapertgremio"] = $reg["ctresapertgremio"]; // Nuevo campo circular 002  
        $retorno["ctresagremio"] = $reg["ctresagremio"]; // Nuevo campo circular 002  
        $retorno["ctresaacredita"] = $reg["ctresaacredita"]; // Nuevo campo circular 002  
        $retorno["ctresaivc"] = $reg["ctresaivc"]; // Nuevo campo circular 002
        if (substr($retorno["matricula"], 0, 1) == 'S') {
            if (trim($retorno["ctresaivc"]) == '') {
                if (trim($reg["vigcontrol"]) != '' && is_numeric($reg["vigcontrol"]) && ltrim($reg["vigcontrol"], "0") != '') {
                    $retorno["ctresaivc"] = retornarRegistroMysqliApi($mysqli, 'mreg_tablassirep', "idtabla='43' and idcodigo = '" . $reg["vigcontrol"] . "'", "descripcion");
                }
                if (trim($reg["vigcontrol"]) != '' && !is_numeric($reg["vigcontrol"])) {
                    $retorno["ctresaivc"] = $reg["vigcontrol"];
                }
            } else {
                $retorno["vigcontrol"] = $retorno["ctresaivc"];
            }
        }
        $retorno["ctresainfoivc"] = $reg["ctresainfoivc"]; // Nuevo campo circular 002
        $retorno["ctresaautregistro"] = $reg["ctresaautregistro"]; // Nuevo campo circular 002  
        $retorno["ctresaentautoriza"] = $reg["ctresaentautoriza"];  // Nuevo campo circular 002  
        $retorno["ctresacodnat"] = $reg["ctresacodnat"]; // Nuevo campo circular 002     1.- Asoc, 2.- Corporacion, 3.- Fundación
        $retorno["ctresadiscap"] = $reg["ctresadiscap"]; // Nuevo campo circular 002     
        $retorno["ctresaetnia"] = $reg["ctresaetnia"]; // Nuevo campo circular 002     
        $retorno["ctresacualetnia"] = $reg["ctresacualetnia"];  // Nuevo campo circular 002     
        $retorno["ctresadespvictreins"] = $reg["ctresadespvictreins"];  // Nuevo campo circular 002     
        $retorno["ctresacualdespvictreins"] = $reg["ctresacualdespvictreins"]; // Nuevo campo circular 002     
        $retorno["ctresaindgest"] = $reg["ctresaindgest"];  // Nuevo campo circular 002            
        $retorno["ctresalgbti"] = $reg["ctresalgbti"]; // Nuevo campo circular 002            
        //
        $retorno["fecexafiliacion"] = '';
        if (isset($reg["fecexafiliacion"])) {
            $retorno["fecexafiliacion"] = $reg["fecexafiliacion"];
        }
        $retorno["motivodesafiliacion"] = $reg["motivodesafiliacion"];
        $retorno["txtmotivodesafiliacion"] = $reg["txtmotivodesafiliacion"];
        $retorno["afiliado"] = $reg["ctrafiliacion"];
        $retorno["fechaafiliacion"] = $reg["fecaflia"];
        $retorno["ultanorenafi"] = $reg["anorenaflia"];
        $retorno["fechaultpagoafi"] = $reg["fecrenaflia"];
        $retorno["valorultpagoafi"] = $reg["valpagaflia"];
        $retorno["saldoafiliado"] = $reg["saldoaflia"];
        $retorno["telaflia"] = $reg["telaflia"];
        $retorno["diraflia"] = $reg["diraflia"];
        $retorno["munaflia"] = $reg["munaflia"];
        $retorno["profaflia"] = $reg["profaflia"];
        $retorno["contaflia"] = $reg["contaflia"];
        $retorno["dircontaflia"] = $reg["dircontaflia"];
        $retorno["muncontaflia"] = $reg["muncontaflia"];
        $retorno["numactaaflia"] = $reg["numactaaflia"];
        $retorno["fecactaaflia"] = $reg["fecactaaflia"];
        $retorno["numactaafliacan"] = $reg["numactacanaflia"];
        $retorno["fecactaafliacan"] = $reg["fecactacanaflia"];
        $afil = encontrarHistoricoPagosAfiliacionMysqliApi($dbx, $retorno["matricula"]);
        if ($afil && !empty($afil)) {
            $retorno["fechaultpagoafi"] = $afil["fecrenaflia"];
            $retorno["ultanorenafi"] = $afil["anorenaflia"];
            $retorno["valorultpagoafi"] = $afil["valorpagadoultimaafiliacion"];
            $retorno["fecrenaflia"] = $afil["fecrenaflia"];
            $retorno["anorenaflia"] = $afil["anorenaflia"];
        }

        $afil1 = buscarSaldoAfiliadoMysqliApi($dbx, $retorno["matricula"]);
        if ($afil1 && !empty($afil1)) {
            $retorno["valorultpagoafi"] = $afil1["pago"];
            $retorno["saldoafiliado"] = $afil1["cupo"];
        }

        // informacion de ubicacion comercial en el registro mercantil
        $retorno["lggr"] = $reg["lggr"];
        $retorno["nombrecomercial"] = $reg["nombrecomercial"];
        $retorno["dircom"] = $reg["dircom"];
        $retorno["dircom_tipovia"] = '';
        $retorno["dircom_numvia"] = '';
        $retorno["dircom_apevia"] = '';
        $retorno["dircom_orivia"] = '';
        $retorno["dircom_numcruce"] = '';
        $retorno["dircom_apecruce"] = '';
        $retorno["dircom_oricruce"] = '';
        $retorno["dircom_numplaca"] = '';
        $retorno["dircom_complemento"] = '';
        $retorno["barriocom"] = $reg["barriocom"];
        $retorno["barriocomnombre"] = '';
        if (trim((string) $reg["barriocom"]) != '') {
            $retorno["barriocomnombre"] = retornarRegistroMysqliApi($mysqli, "mreg_barriosmuni", "idmunicipio='" . $reg["muncom"] . "' and idbarrio='" . $reg["barriocom"] . "'", "nombre");
        }
        $retorno["muncom"] = $reg["muncom"];
        $retorno["paicom"] = $reg["paicom"]; // Nuevo 002
        $retorno["muncomnombre"] = retornarRegistroMysqliApi($mysqli, "bas_municipios", "codigomunicipio='" . $reg["muncom"] . "'", "ciudad");
        $retorno["telcom1"] = $reg["telcom1"];
        $retorno["telcom2"] = $reg["telcom2"];
        $retorno["celcom"] = $reg["telcom3"];
        $retorno["telcomant1"] = '';
        $retorno["telcomant2"] = '';
        $retorno["telcomant3"] = '';
        $retorno["faxcom"] = $reg["faxcom"];
        $retorno["aacom"] = $reg["aacom"];
        $retorno["zonapostalcom"] = '';
        $retorno["emailcom"] = $reg["emailcom"];
        $retorno["emailcom2"] = $reg["emailcom2"];
        $retorno["emailcom3"] = $reg["emailcom3"];
        $retorno["emailcomant"] = '';
        $retorno["nombresegundocontacto"] = $reg["nomsegcon"];
        $retorno["cargosegundocontacto"] = $reg["carsegcon"];
        $retorno["urlcom"] = $reg["urlcom"];
        $retorno["numpredial"] = $reg["numpredial"];
        $retorno["codigopostalcom"] = $reg["codigopostalcom"]; // Nuevo campo circular 002
        $retorno["codigozonacom"] = $reg["codigozonacom"]; // Nuevo campo circular 002 - Urbao o rural
        // informacion de ubicacion de notificacion
        $retorno["dirnot"] = $reg["dirnot"];
        $retorno["dirnot_tipovia"] = '';
        $retorno["dirnot_numvia"] = '';
        $retorno["dirnot_apevia"] = '';
        $retorno["dirnot_orivia"] = '';
        $retorno["dirnot_numcruce"] = '';
        $retorno["dirnot_apecruce"] = '';
        $retorno["dirnot_oricruce"] = '';
        $retorno["dirnot_numplaca"] = '';
        $retorno["dirnot_complemento"] = '';
        $retorno["barrionot"] = $reg["barrionot"];
        $retorno["barrionotnombre"] = '';
        if (trim((string) $reg["barrionot"]) != '') {
            $retorno["barrionotnombre"] = retornarRegistroMysqliApi($mysqli, "mreg_barriosmuni", "idmunicipio='" . $reg["munnot"] . "' and idbarrio='" . $reg["barrionot"] . "'", "nombre");
        }
        $retorno["munnot"] = $reg["munnot"];
        $retorno["painot"] = $reg["painot"]; // Nuevo 002
        $retorno["munnotnombre"] = retornarRegistroMysqliApi($mysqli, "bas_municipios", "codigomunicipio='" . $reg["munnot"] . "'", "ciudad");
        $retorno["telnot"] = $reg["telnot"];
        $retorno["telnot2"] = $reg["telnot2"];
        $retorno["telnotant1"] = '';
        $retorno["telnotant2"] = '';
        $retorno["telnotant3"] = '';
        $retorno["celnot"] = $reg["telnot3"];
        $retorno["faxnot"] = $reg["faxnot"];
        $retorno["aanot"] = $reg["aanot"];
        $retorno["zonapostalnot"] = '';
        $retorno["emailnot"] = $reg["emailnot"];
        $retorno["emailnotant"] = '';
        $retorno["urlnot"] = $reg["urlnot"];
        $retorno["codigopostalnot"] = $reg["codigopostalnot"]; // Nuevo campo circular 002
        $retorno["codigozonanot"] = $reg["codigozonanot"]; // Nuevo campo circular 002 - Urbano o rural
        $retorno["tiposedeadm"] = $reg["tiposedeadm"]; // Nuevo campo circular 002 - 1,2,3,4
        //2017-06-201: JINT: No utilizados
        $retorno["latitudgrados"] = "";
        $retorno["latitudminutos"] = "";
        $retorno["latitudsegundos"] = "";
        $retorno["latitudorientacion"] = "";
        $retorno["longitudgrados"] = "";
        $retorno["longitudminutos"] = "";
        $retorno["longitudsegundos"] = "";
        $retorno["longitudorientacion"] = "";
        $retorno["habilitacionesespeciales"] = array();

        // 2017-06-21: JINT - Se alimenta desde la tabla mreg_geolocalizacion
        $retorno["latitud"] = "";
        $retorno["longitud"] = "";
        $ax1 = retornarRegistrosMysqliApi($mysqli, 'mreg_geolocalizacion', "matricula='" . $retorno["matricula"] . "'", 'fecha asc, hora asc');
        if ($ax1 && !empty($ax1)) {
            foreach ($ax1 as $x1) {
                if ($x1["latitud"] != 'undefined' && $x1["longitud"] != 'undefined') {
                    $retorno["latitud"] = $x1["latitud"];
                    $retorno["longitud"] = $x1["longitud"];
                }
            }
        }
        unset($x1);
        unset($ax1);
        if ($genlog == 'si') {
            \logApi::general2($nameLog, $retorno["matricula"], 'Leyo mreg_geolocalizacion');
        }

        // Localiza emails y celulars anteriores
        // if ($tipodata == 'E') {
        if (trim($retorno["matricula"]) != '') {
            $retorno["telcomant1"] = \funcionesGenerales::localizarCampoAnterior($mysqli, $retorno["matricula"], 'telcom1');
            $retorno["telcomant2"] = \funcionesGenerales::localizarCampoAnterior($mysqli, $retorno["matricula"], 'telcom2');
            $retorno["telcomant3"] = \funcionesGenerales::localizarCampoAnterior($mysqli, $retorno["matricula"], 'celcom');
            $retorno["telnotant1"] = \funcionesGenerales::localizarCampoAnterior($mysqli, $retorno["matricula"], 'telnot');
            $retorno["telnotant2"] = \funcionesGenerales::localizarCampoAnterior($mysqli, $retorno["matricula"], 'telnot2');
            $retorno["telnotant3"] = \funcionesGenerales::localizarCampoAnterior($mysqli, $retorno["matricula"], 'celnot');
            $retorno["emailcomant"] = \funcionesGenerales::localizarCampoAnterior($mysqli, $retorno["matricula"], 'emailcom');
            $retorno["emailnotant"] = \funcionesGenerales::localizarCampoAnterior($mysqli, $retorno["matricula"], 'emailnot');
        }
        if ($genlog == 'si') {
            \logApi::general2($nameLog, $retorno["matricula"], 'Leyo localizarCampoAnterior');
        }

        // }
        // Datos de correspondencia
        $retorno["dircor"] = $reg["dircor"];
        $retorno["telcor"] = $reg["telcor"];
        $retorno["telcor2"] = $reg["telcor2"];
        $retorno["muncor"] = $reg["muncor"];

        // informacion de actividad economica
        $retorno["ciius3"] = array();
        if (isset($reg["ciiu31"])) {
            $retorno["ciius3"][1] = $reg["ciiu31"];
            $retorno["ciius3"][2] = $reg["ciiu32"];
            $retorno["ciius3"][3] = $reg["ciiu33"];
            $retorno["ciius3"][4] = $reg["ciiu34"];
        }

        // informacion ed actividad economica
        $retorno["ciius"] = array();
        $retorno["ciius"][1] = $reg["ciiu1"];
        $retorno["ciius"][2] = $reg["ciiu2"];
        $retorno["ciius"][3] = $reg["ciiu3"];
        $retorno["ciius"][4] = $reg["ciiu4"];
        $retorno["ciius"][5] = $reg["ciiu5"];

        // 2017-10-13: JINT: Para resolver problemas de letras incorrectas en el CIIU
        if (trim($retorno["ciius"][1]) != '') {
            $ciiux = retornarRegistroMysqliApi($mysqli, 'bas_ciius', "idciiunum='" . substr($retorno["ciius"][1], 1) . "'");
            if ($ciiux && !empty($ciiux)) {
                $retorno["ciius"][1] = $ciiux["idciiu"];
            }
        }
        if (trim($retorno["ciius"][2]) != '') {
            $ciiux = retornarRegistroMysqliApi($mysqli, 'bas_ciius', "idciiunum='" . substr($retorno["ciius"][2], 1) . "'");
            if ($ciiux && !empty($ciiux)) {
                $retorno["ciius"][2] = $ciiux["idciiu"];
            }
        }
        if (trim($retorno["ciius"][3]) != '') {
            $ciiux = retornarRegistroMysqliApi($mysqli, 'bas_ciius', "idciiunum='" . substr($retorno["ciius"][3], 1) . "'");
            if ($ciiux && !empty($ciiux)) {
                $retorno["ciius"][3] = $ciiux["idciiu"];
            }
        }
        if (trim($retorno["ciius"][4]) != '') {
            $ciiux = retornarRegistroMysqliApi($mysqli, 'bas_ciius', "idciiunum='" . substr($retorno["ciius"][4], 1) . "'");
            if ($ciiux && !empty($ciiux)) {
                $retorno["ciius"][4] = $ciiux["idciiu"];
            }
        }


        //
        $retorno["versionciiu"] = $reg["versionciiu"];
        $retorno["desactiv"] = stripslashes((string) $reg["actividad"]); // Nuevo campo circular 002
        $retorno["feciniact1"] = $reg["feciniact1"]; // Nuevo campo circular 002
        $retorno["feciniact2"] = $reg["feciniact2"]; // Nuevo campo circular 002
        $retorno["codaduaneros"] = $reg["codaduaneros"]; // Nuevo campo circular 002
        if (!isset($reg["ingesperados"]) || trim((string) $reg["ingesperados"]) == '') {
            $reg["ingesperados"] = 0;
        }
        $retorno["ingesperados"] = $reg["ingesperados"]; // Nuevo campo circular 002
        //
        $retorno["gruponiif"] = $reg["gruponiif"]; // Nuevo campo circular 002
        $retorno["niifconciliacion"] = $reg["niifconciliacion"]; // Nuevo campo circular 002
        $retorno["aportantesegsocial"] = $reg["aportantesegsocial"]; // Nuevo campo circular 002
        $retorno["tipoaportantesegsocial"] = $reg["tipoaportantesegsocial"]; // Nuevo campo circular 002
        // informacion de porcentajes de capital
        $retorno["cap_porcnaltot"] = $reg["pornaltot"];
        $retorno["cap_porcnalpri"] = $reg["pornalpri"];
        $retorno["cap_porcnalpub"] = $reg["pornalpub"];
        $retorno["cap_porcexttot"] = $reg["porexttot"];
        $retorno["cap_porcextpri"] = $reg["porextpri"];
        $retorno["cap_porcextpub"] = $reg["porextpub"];

        $retorno["cap_apolab"] = 0;
        $retorno["cap_apolabadi"] = 0;
        $retorno["cap_apoact"] = 0;
        $retorno["cap_apodin"] = 0;

        $retorno["fecdatoscap"] = '';
        $retorno["capsoc"] = 0;
        $retorno["capaut"] = 0;
        $retorno["capsus"] = 0;
        $retorno["cappag"] = 0;
        $retorno["cuosoc"] = 0;
        $retorno["cuoaut"] = 0;
        $retorno["cuosus"] = 0;
        $retorno["cuopag"] = 0;
        $retorno["capsuc"] = 0;

        // 2019-12-15: JINT: Cantidad de mujeres, participacion y tamaño empresarial
        $retorno["cantidadmujeres"] = $reg["cantidadmujeres"];
        $retorno["cantidadmujerescargosdirectivos"] = $reg["cantidadmujerescargosdirectivos"];
        $retorno["cantidadcargosdirectivos"] = 0;
        $retorno["participacionmujeres"] = $reg["participacionmujeres"];
        $retorno["participacionetnia"] = 0;
        $retorno["ciiutamanoempresarial"] = '';
        $retorno["ingresostamanoempresarial"] = '';
        $retorno["anodatostamanoempresarial"] = '';
        $retorno["fechadatostamanoempresarial"] = '';

        $retorno["tamanoempresarial957"] = '';
        $retorno["tamanoempresarial957uvts"] = 0;
        $retorno["tamanoempresarial957uvbs"] = 0;
        $retorno["tamanoempresarial957codigo"] = '';
        $retorno["tamanoempresarialingresos"] = 0;
        $retorno["tamanoempresarialactivos"] = 0;
        $retorno["tamanoempresarialciiu"] = '';
        $retorno["tamanoempresarialpersonal"] = 0;
        $retorno["tamanoempresarialfechadatos"] = '';
        $retorno["tamanoempresarialanodatos"] = '';
        $retorno["tamanoempresarialformacalculo"] = '';

        $retorno["tamanoempresarial957codigoanterior"] = '';

        // información de Establecimientos de comercio asociados
        $retorno["cntestab01"] = $reg["cntest01"];
        $retorno["cntestab02"] = $reg["cntest02"];
        $retorno["cntestab03"] = $reg["cntest03"];
        $retorno["cntestab04"] = $reg["cntest04"];
        $retorno["cntestab05"] = $reg["cntest05"];
        $retorno["cntestab06"] = $reg["cntest06"];
        $retorno["cntestab07"] = $reg["cntest07"];
        $retorno["cntestab08"] = $reg["cntest08"];
        $retorno["cntestab09"] = $reg["cntest09"];
        $retorno["cntestab10"] = $reg["cntest10"];
        $retorno["cntestab11"] = $reg["cntest11"];

        // información de referencias comerciales y bancarias
        $retorno["refcrenom1"] = $reg["refcrenom1"];
        $retorno["refcreofi1"] = $reg["refcreofi1"];
        $retorno["refcretel1"] = $reg["refcretel1"];
        $retorno["refcrenom2"] = $reg["refcrenom2"];
        $retorno["refcreofi2"] = $reg["refcreofi2"];
        $retorno["refcretel2"] = $reg["refcretel2"];
        $retorno["refcomnom1"] = $reg["refcomnom1"];
        $retorno["refcomdir1"] = $reg["refcomdir1"];
        $retorno["refcomtel1"] = $reg["refcomtel1"];
        $retorno["refcomnom2"] = $reg["refcomnom2"];
        $retorno["refcomdir2"] = $reg["refcomdir2"];
        $retorno["refcomtel2"] = $reg["refcomtel2"];

        $retorno["ultimosactivosreportados"] = 0;
        $retorno["ultimosactivosvinculados"] = 0;
        $retorno["anodatos"] = '';
        $retorno["fechadatos"] = '';
        $retorno["personal"] = 0;
        $retorno["personaltemp"] = 0;
        $retorno["actvin"] = 0;
        $retorno["valest"] = 0;
        $retorno["actcte"] = 0;
        $retorno["actnocte"] = 0;
        $retorno["actfij"] = 0;
        $retorno["fijnet"] = 0;
        $retorno["actotr"] = 0;
        $retorno["actval"] = 0;
        $retorno["acttot"] = 0;
        $retorno["actsinaju"] = 0;
        $retorno["invent"] = 0;
        $retorno["pascte"] = 0;
        $retorno["paslar"] = 0;
        $retorno["pastot"] = 0;
        $retorno["pattot"] = 0;
        $retorno["paspat"] = 0;
        $retorno["balsoc"] = 0;
        $retorno["ingope"] = 0;
        $retorno["ingnoope"] = 0;
        $retorno["gtoven"] = 0;
        $retorno["gtoadm"] = 0;
        $retorno["cosven"] = 0;
        $retorno["depamo"] = 0;
        $retorno["gasint"] = 0;
        $retorno["gasimp"] = 0;
        $retorno["utiope"] = 0;
        $retorno["utinet"] = 0;

        $retorno["apolab"] = $reg["apolab"];
        $retorno["apolabadi"] = $reg["apolabadi"];
        $retorno["apoact"] = $reg["apoact"];
        $retorno["apodin"] = $reg["apodin"];
        $retorno["apotra"] = $reg["apotra"];
        $retorno["apotot"] = $reg["apotot"];

        $retorno["anodatospatrimonio"] = '';
        $retorno["fechadatospatrimonio"] = '';
        $retorno["patrimonioesadl"] = 0;

        // información financiera
        $ax1 = retornarRegistrosMysqliApi($mysqli, 'mreg_est_financiera', "matricula='" . $retorno["matricula"] . "'", 'anodatos asc, fechadatos asc, id asc');
        if ($ax1 && !empty($ax1)) {
            foreach ($ax1 as $x1) {
                $retorno["anodatos"] = $x1["anodatos"];
                $retorno["fechadatos"] = $x1["fechadatos"];
                $retorno["personal"] = $x1["personal"];
                $retorno["personaltemp"] = $x1["pcttemp"];
                $retorno["patrimonio"] = $x1["patrimonio"];
                $retorno["actvin"] = $x1["actvin"];
                $retorno["valest"] = $x1["actvin"];
                $retorno["ultimosactivosvinculados"] = $x1["actvin"];
                $retorno["actcte"] = $x1["actcte"];
                $retorno["actnocte"] = $x1["actnocte"];
                $retorno["actfij"] = $x1["actfij"];
                $retorno["fijnet"] = $x1["fijnet"];
                $retorno["actotr"] = $x1["actotr"];
                $retorno["actval"] = $x1["actval"];
                $retorno["acttot"] = $x1["acttot"];
                $retorno["ultimosactivosreportados"] = $x1["acttot"];
                $retorno["actsinaju"] = 0;
                $retorno["invent"] = 0;
                $retorno["pascte"] = $x1["pascte"];
                $retorno["paslar"] = $x1["paslar"];
                $retorno["pastot"] = $x1["pastot"];
                $retorno["pattot"] = $x1["patnet"];
                $retorno["paspat"] = $x1["paspat"];
                $retorno["balsoc"] = $x1["balsoc"];
                $retorno["ingope"] = $x1["ingope"];
                $retorno["ingnoope"] = $x1["ingnoope"];
                $retorno["gtoven"] = $x1["gtoven"];
                $retorno["gtoadm"] = $x1["gasadm"];
                $retorno["cosven"] = $x1["cosven"];
                $retorno["depamo"] = 0;
                $retorno["gasint"] = $x1["gasint"];
                $retorno["gasimp"] = $x1["gasimp"];
                $retorno["utiope"] = $x1["utiope"];
                $retorno["utinet"] = $x1["utinet"];
            }
            unset($x1);
            unset($ax1);
        }
        if ($genlog == 'si') {
            \logApi::general2($nameLog, $retorno["matricula"], 'Leyo mreg_est_financiera');
        }

        // 2020-01-15: JINT: Calculo de fechas de renovacion y afiliacion
        // No estaba incluido en la nueva rutina
        $histopagos = encontrarHistoricoPagosMysqliApi($mysqli, $retorno["matricula"], $serviciosRenovacion, $serviciosAfiliacion, $serviciosMatricula);
        if ($histopagos["fecultren"] != '') {
            $retorno["fecharenovacion"] = $histopagos["fecultren"];
            $retorno["ultanoren"] = $histopagos["ultanoren"];
            if ($histopagos["actultren"] != 0) {
                if ($retorno["organizacion"] == '02' || $retorno["categoria"] == '2' || $retorno["categoria"] == '3') {
                    $retorno["ultimosactivosvinculados"] = $histopagos["actultren"];
                } else {
                    $retorno["ultimosactivosreportados"] = $histopagos["actultren"];
                }
            }
        }

        //
        $ax1 = retornarRegistrosMysqliApi($mysqli, 'mreg_est_patrimonios', "matricula='" . $retorno["matricula"] . "'", 'anodatos asc, fechadatos asc');
        if ($ax1 && !empty($ax1)) {
            foreach ($ax1 as $x1) {
                $retorno["anodatospatrimonio"] = $x1["anodatos"];
                $retorno["fechadatospatrimonio"] = $x1["fechadatos"];
                $retorno["patrimonioesadl"] = $x1["patrimonio"];
            }
            unset($x1);
            unset($ax1);
        }
        if ($genlog == 'si') {
            \logApi::general2($nameLog, $retorno["matricula"], 'Leyo mreg_est_patrimonios');
        }

        // Campos adicionados en mayo 20 de 2011     
        $retorno["ctrmen"] = $reg["ctrnotemail"];
        $retorno["ctrmennot"] = $reg["ctrnotsms"];
        $retorno["ctrubi"] = $reg["ctrubi"];
        $retorno["ctrfun"] = $reg["ctrfun"];
        $retorno["art4"] = $reg["ctrbenart4"];
        $retorno["art7"] = $reg["ctrbenart7"];
        $retorno["art50"] = $reg["ctrbenart50"];
        $retorno["ctrcancelacion1429"] = $reg["ctrcance1429"];
        $retorno["benley1780"] = $reg["ctrbenley1780"];
        $retorno["cumplerequisitos1780"] = $reg["cumplerequisitos1780"];  // Nuevo campo circular 002
        $retorno["renunciabeneficios1780"] = $reg["renunciabeneficios1780"]; // Nuevo campo circular 002
        $retorno["cumplerequisitos1780primren"] = $reg["cumplerequisitos1780primren"]; // Nuevo campo circular 002
        $retorno["ctrbic"] = $reg["ctrbic"];
        $retorno["ctrdepuracion1727"] = $reg["ctrdepuracion1727"];
        $retorno["ctrfechadepuracion1727"] = $reg["ctrfechadepuracion1727"];
        $retorno["ctrben658"] = $reg["ctrben658"];

        $retorno["fecmatant"] = $reg["fecmatant"];
        $retorno["fecrenant"] = $reg["fecrenant"];
        $retorno["matriculaanterior"] = $reg["matant"];
        $retorno["matant"] = $reg["matant"];
        $retorno["camant"] = $reg["camant"];
        $retorno["munant"] = $reg["munant"];
        $retorno["ultanorenant"] = $reg["ultanorenant"];
        $retorno["benart7ant"] = $reg["benart7ant"];
        $retorno["benley1780ant"] = $reg["benley1780ant"];

        //
        $retorno["ivcenvio"] = $reg["ctrivcenvio"];
        $retorno["ivcsuelos"] = $reg["ctrivcsuelos"];
        $retorno["ivcarea"] = $reg["ctrivcarea"];
        $retorno["ivcver"] = $reg["ctrivcver"];
        $retorno["ivccretip"] = $reg["ctrivccretip"];
        $retorno["ivcali"] = $reg["ctrivcali"];
        $retorno["ivcqui"] = $reg["ctrivcqui"];
        $retorno["ivcriesgo"] = $reg["ctrivcriesgo"];

        // Representacion legal y administracion
        $retorno["idtipoidentificacionreplegal"] = '';
        $retorno["identificacionreplegal"] = '';
        $retorno["nombrereplegal"] = '';
        $retorno["idtipoidentificacionadministrador"] = '';
        $retorno["identificacionadministrador"] = '';
        $retorno["nombreadministrador"] = '';

        // Casa principal
        $retorno["cpcodcam"] = $reg["cpcodcam"];
        $retorno["cpnummat"] = $reg["cpnummat"];
        $retorno["cprazsoc"] = $reg["cprazsoc"];
        $retorno["cpnumnit"] = $reg["cpnumnit"];
        $retorno["cpdircom"] = $reg["cpdircom"];
        $retorno["cpdirnot"] = $reg["cpdirnot"];
        $retorno["cpnumtel"] = $reg["cpnumtel"];
        $retorno["cpnumtel2"] = ''; // Nacen con circular 002
        $retorno["cpnumtel3"] = ''; // Nacen con circular 002
        $retorno["cpnumfax"] = $reg["cpnumfax"];
        $retorno["cpcodmun"] = $reg["cpcodmun"];
        $retorno["cpmunnot"] = $reg["cpmunnot"];
        $retorno["cptirepleg"] = $reg["cptirepleg"];
        $retorno["cpirepleg"] = $reg["cpirepleg"];
        $retorno["cpnrepleg"] = $reg["cpnrepleg"];
        $retorno["cptelrepleg"] = $reg["cptelrepleg"];
        $retorno["cpemailrepleg"] = $reg["cpemailrepleg"];

        // Datos de constitución sacado de libros
        $retorno["datconst_fecdoc"] = '';
        $retorno["datconst_tipdoc"] = '';
        $retorno["datconst_numdoc"] = '';
        $retorno["datconst_oridoc"] = '';
        $retorno["datconst_mundoc"] = '';

        // Bienes
        $retorno["bienes"] = array();

        //
        $retorno["clasevinculo"] = $_SESSION["clasevinculo"];

        // Vinculos 
        // Vinculos históricos
        // Representantes legales
        $retorno["existenvinculos"] = '';
        $retorno["replegal"] = array();
        $retorno["vinculos"] = array();
        $retorno["vinculosh"] = array();
        $vincs = retornarRegistrosMysqliApi($mysqli, 'mreg_est_vinculos', "matricula='" . ltrim(trim($retorno["matricula"]), "0") . "'", "estado,vinculo,idcargo,fecha,idclase,numid");
        $iVincs = 0;
        $iVincsh = 0;
        $iReps = 0;
        $primerRep = 0;
        if ($vincs) {
            if (!empty($vincs)) {
                foreach ($vincs as $vin) {

                    //
                    if ($vin["estado"] == 'V') {
                        $iVincs++;
                        if (!isset($vin["fechaconfiguracion"])) {
                            $vin["fechaconfiguracion"] = '';
                        }
                        if (!isset($vin["fechavencimiento"])) {
                            $vin["fechavencimiento"] = '';
                        }
                        $retorno["existenvinculos"] = 'si';
                        $retorno["vinculos"][$iVincs]["id"] = $vin["id"];
                        $retorno["vinculos"][$iVincs]["idtipoidentificacionotros"] = $vin["idclase"];
                        $retorno["vinculos"][$iVincs]["identificacionotros"] = $vin["numid"];
                        $retorno["vinculos"][$iVincs]["nombreotros"] = stripslashes((string) $vin["nombre"]);
                        $retorno["vinculos"][$iVincs]["apellido1otros"] = stripslashes((string) $vin["ape1"]);
                        $retorno["vinculos"][$iVincs]["apellido2otros"] = stripslashes((string) $vin["ape2"]);
                        $retorno["vinculos"][$iVincs]["nombre1otros"] = stripslashes((string) $vin["nom1"]);
                        $retorno["vinculos"][$iVincs]["nombre2otros"] = stripslashes((string) $vin["nom2"]);
                        $retorno["vinculos"][$iVincs]["direccionotros"] = stripslashes((string) $vin["direccion"]);
                        $retorno["vinculos"][$iVincs]["municipiootros"] = stripslashes((string) $vin["municipio"]);
                        $retorno["vinculos"][$iVincs]["paisotros"] = stripslashes((string) $vin["pais"]);
                        $retorno["vinculos"][$iVincs]["emailotros"] = stripslashes((string) $vin["email"]);
                        $retorno["vinculos"][$iVincs]["celularotros"] = ($vin["celular"]);
                        $retorno["vinculos"][$iVincs]["fechanacimientootros"] = ($vin["fechanacimiento"]);
                        $retorno["vinculos"][$iVincs]["idcargootros"] = ($vin["idcargo"]);
                        $retorno["vinculos"][$iVincs]["cargootros"] = stripslashes((string) $vin["descargo"]);
                        $retorno["vinculos"][$iVincs]["vinculootros"] = ($vin["vinculo"]);
                        $retorno["vinculos"][$iVincs]["numtarprofotros"] = stripslashes((string) $vin["tarjprof"]);
                        $retorno["vinculos"][$iVincs]["idclaseemp"] = ($vin["tirepresenta"]);
                        $retorno["vinculos"][$iVincs]["numidemp"] = ($vin["idrepresenta"]);
                        $retorno["vinculos"][$iVincs]["nombreemp"] = stripslashes((string) $vin["nmrepresenta"]);
                        $retorno["vinculos"][$iVincs]["idvindianpnat"] = ($vin["vinculodianpnat"]);
                        $retorno["vinculos"][$iVincs]["idvindianpjur"] = ($vin["vinculodianpjur"]);
                        $retorno["vinculos"][$iVincs]["cuotasconst"] = ($vin["cuotasconst"]);
                        $retorno["vinculos"][$iVincs]["cuotasref"] = ($vin["cuotasref"]);
                        $retorno["vinculos"][$iVincs]["valorconst"] = ($vin["valorconst"]);
                        $retorno["vinculos"][$iVincs]["valorref"] = ($vin["valorref"]);
                        $retorno["vinculos"][$iVincs]["va1"] = ($vin["valorasociativa1"]);
                        $retorno["vinculos"][$iVincs]["va2"] = ($vin["valorasociativa2"]);
                        $retorno["vinculos"][$iVincs]["va3"] = ($vin["valorasociativa3"]);
                        $retorno["vinculos"][$iVincs]["va4"] = ($vin["valorasociativa4"]);
                        $retorno["vinculos"][$iVincs]["va5"] = ($vin["valorasociativa5"]);
                        $retorno["vinculos"][$iVincs]["va6"] = ($vin["valorasociativa6"]);
                        $retorno["vinculos"][$iVincs]["va7"] = ($vin["valorasociativa7"]);
                        $retorno["vinculos"][$iVincs]["va8"] = ($vin["valorasociativa8"]);
                        $retorno["vinculos"][$iVincs]["librootros"] = ($vin["idlibro"]);
                        $retorno["vinculos"][$iVincs]["inscripcionotros"] = ($vin["numreg"]);
                        $retorno["vinculos"][$iVincs]["dupliotros"] = ($vin["dupli"]);
                        $retorno["vinculos"][$iVincs]["fechaotros"] = ($vin["fecha"]);
                        $retorno["vinculos"][$iVincs]["ciiu1"] = ($vin["ciiu1"]);
                        $retorno["vinculos"][$iVincs]["ciiu2"] = ($vin["ciiu2"]);
                        $retorno["vinculos"][$iVincs]["ciiu3"] = ($vin["ciiu3"]);
                        $retorno["vinculos"][$iVincs]["ciiu4"] = ($vin["ciiu4"]);
                        $retorno["vinculos"][$iVincs]["tipositcontrol"] = ($vin["tipositcontrol"]);
                        $retorno["vinculos"][$iVincs]["tipositcontroltexto"] = '';
                        if (isset($vin["tipositcontroltexto"])) {
                            $retorno["vinculos"][$iVincs]["tipositcontroltexto"] = ($vin["tipositcontroltexto"]);
                        }
                        $retorno["vinculos"][$iVincs]["desactiv"] = ($vin["desactiv"]);
                        $retorno["vinculos"][$iVincs]["fechaconfiguracion"] = ($vin["fechaconfiguracion"]);
                        $retorno["vinculos"][$iVincs]["fechavencimiento"] = ($vin["fechavencimiento"]);
                        $retorno["vinculos"][$iVincs]["codcertifica"] = ($vin["codcertifica"]);
                        if (isset($_SESSION["maestrovinculos"][$vin["vinculo"]])) {
                            $retorno["vinculos"][$iVincs]["tipovinculo"] = $_SESSION["maestrovinculos"][$vin["vinculo"]]["tipovinculo"];
                            $retorno["vinculos"][$iVincs]["tipovinculoceresadl"] = $_SESSION["maestrovinculos"][$vin["vinculo"]]["tipovinculoceresadl"];
                            $retorno["vinculos"][$iVincs]["tipovinculoesadl"] = $_SESSION["maestrovinculos"][$vin["vinculo"]]["tipovinculoceresadl"];
                            $retorno["vinculos"][$iVincs]["puedereactivar"] = strtoupper((string) $_SESSION["maestrovinculos"][$vin["vinculo"]]["puedereactivar"]);
                        } else {
                            $retorno["vinculos"][$iVincs]["tipovinculo"] = '';
                            $retorno["vinculos"][$iVincs]["tipovinculoceresadl"] = '';
                            $retorno["vinculos"][$iVincs]["tipovinculoesadl"] = '';
                            $retorno["vinculos"][$iVincs]["puedereactivar"] = '';
                        }
                        $retorno["vinculos"][$iVincs]["renrem"] = $vin["textorenunciaremocion"];
                        $retorno["vinculos"][$iVincs]["textoembargos"] = '';
                        if (isset($vin["textoembargo"]) && trim((string) $vin["textoembargo"]) != '') {
                            $retorno["vinculos"][$iVincs]["textoembargo"] = $vin["textoembargo"];
                        }
                        $retorno["vinculos"][$iVincs]["nacionalidad"] = '';
                        $retorno["vinculos"][$iVincs]["proindiviso"] = '';
                        if (isset($vin["nacionalidad"])) {
                            $retorno["vinculos"][$iVincs]["nacionalidad"] = stripslashes((string) $vin["nacionalidad"]);
                        }
                        if (isset($vin["proindiviso"])) {
                            $retorno["vinculos"][$iVincs]["proindiviso"] = stripslashes((string) $vin["proindiviso"]);
                        }
                    }

                    //
                    if ($vin["estado"] == 'H') {
                        $iVincsh++;
                        if (!isset($vin["fechaconfiguracion"])) {
                            $vin["fechaconfiguracion"] = '';
                        }
                        if (!isset($vin["fechavencimiento"])) {
                            $vin["fechavencimiento"] = '';
                        }
                        $retorno["vinculosh"][$iVincsh]["idtipoidentificacionotros"] = $vin["idclase"];
                        $retorno["vinculosh"][$iVincsh]["identificacionotros"] = $vin["numid"];
                        $retorno["vinculosh"][$iVincsh]["nombreotros"] = stripslashes((string) $vin["nombre"]);
                        $retorno["vinculosh"][$iVincsh]["apellido1otros"] = stripslashes((string) $vin["ape1"]);
                        $retorno["vinculosh"][$iVincsh]["apellido2otros"] = stripslashes((string) $vin["ape2"]);
                        $retorno["vinculosh"][$iVincsh]["nombre1otros"] = stripslashes((string) $vin["nom1"]);
                        $retorno["vinculosh"][$iVincsh]["nombre2otros"] = stripslashes((string) $vin["nom2"]);
                        $retorno["vinculosh"][$iVincsh]["direccionotros"] = stripslashes((string) $vin["direccion"]);
                        $retorno["vinculosh"][$iVincsh]["municipiootros"] = stripslashes((string) $vin["municipio"]);
                        $retorno["vinculosh"][$iVincsh]["paisotros"] = stripslashes((string) $vin["pais"]);
                        $retorno["vinculosh"][$iVincsh]["emailotros"] = stripslashes((string) $vin["email"]);
                        $retorno["vinculosh"][$iVincsh]["celularotros"] = ($vin["celular"]);
                        $retorno["vinculosh"][$iVincsh]["fechanacimientootros"] = ($vin["fechanacimiento"]);
                        $retorno["vinculosh"][$iVincsh]["idcargootros"] = ($vin["idcargo"]);
                        $retorno["vinculosh"][$iVincsh]["cargootros"] = stripslashes((string) $vin["descargo"]);
                        $retorno["vinculosh"][$iVincsh]["vinculootros"] = ($vin["vinculo"]);
                        $retorno["vinculosh"][$iVincsh]["numtarprofotros"] = stripslashes((string) $vin["tarjprof"]);
                        $retorno["vinculosh"][$iVincsh]["idclaseemp"] = ($vin["tirepresenta"]);
                        $retorno["vinculosh"][$iVincsh]["numidemp"] = ($vin["idrepresenta"]);
                        $retorno["vinculosh"][$iVincsh]["nombreemp"] = stripslashes((string) $vin["nmrepresenta"]);
                        $retorno["vinculosh"][$iVincsh]["idvindianpnat"] = ($vin["vinculodianpnat"]);
                        $retorno["vinculosh"][$iVincsh]["idvindianpjur"] = ($vin["vinculodianpjur"]);
                        $retorno["vinculosh"][$iVincsh]["cuotasconst"] = ($vin["cuotasconst"]);
                        $retorno["vinculosh"][$iVincsh]["cuotasref"] = ($vin["cuotasref"]);
                        $retorno["vinculosh"][$iVincsh]["valorconst"] = ($vin["valorconst"]);
                        $retorno["vinculosh"][$iVincsh]["valorref"] = ($vin["valorref"]);
                        $retorno["vinculosh"][$iVincsh]["va1"] = ($vin["valorasociativa1"]);
                        $retorno["vinculosh"][$iVincsh]["va2"] = ($vin["valorasociativa2"]);
                        $retorno["vinculosh"][$iVincsh]["va3"] = ($vin["valorasociativa3"]);
                        $retorno["vinculosh"][$iVincsh]["va4"] = ($vin["valorasociativa4"]);
                        $retorno["vinculosh"][$iVincsh]["va5"] = ($vin["valorasociativa5"]);
                        $retorno["vinculosh"][$iVincsh]["va6"] = ($vin["valorasociativa6"]);
                        $retorno["vinculosh"][$iVincsh]["va7"] = ($vin["valorasociativa7"]);
                        $retorno["vinculosh"][$iVincsh]["va8"] = ($vin["valorasociativa8"]);
                        $retorno["vinculosh"][$iVincsh]["librootros"] = ($vin["idlibro"]);
                        $retorno["vinculosh"][$iVincsh]["inscripcionotros"] = ($vin["numreg"]);
                        $retorno["vinculosh"][$iVincsh]["dupliotros"] = ($vin["dupli"]);
                        $retorno["vinculosh"][$iVincsh]["fechaotros"] = ($vin["fecha"]);
                        $retorno["vinculosh"][$iVincsh]["ciiu1"] = ($vin["ciiu1"]);
                        $retorno["vinculosh"][$iVincsh]["ciiu2"] = ($vin["ciiu2"]);
                        $retorno["vinculosh"][$iVincsh]["ciiu3"] = ($vin["ciiu3"]);
                        $retorno["vinculosh"][$iVincsh]["ciiu4"] = ($vin["ciiu4"]);
                        $retorno["vinculosh"][$iVincsh]["tipositcontrol"] = ($vin["tipositcontrol"]);
                        $retorno["vinculosh"][$iVincsh]["tipositcontroltexto"] = '';
                        if (isset($vin["tipositcontroltexto"])) {
                            $retorno["vinculosh"][$iVincs]["tipositcontroltexto"] = ($vin["tipositcontroltexto"]);
                        }
                        $retorno["vinculosh"][$iVincsh]["desactiv"] = ($vin["desactiv"]);
                        $retorno["vinculosh"][$iVincsh]["fechaconfiguracion"] = ($vin["fechaconfiguracion"]);
                        $retorno["vinculosh"][$iVincsh]["fechavencimiento"] = ($vin["fechavencimiento"]);
                        $retorno["vinculosh"][$iVincsh]["codcertifica"] = $vin["codcertifica"];
                        if (isset($_SESSION["maestrovinculos"][$vin["vinculo"]])) {
                            $retorno["vinculosh"][$iVincsh]["tipovinculo"] = $_SESSION["maestrovinculos"][$vin["vinculo"]]["tipovinculo"];
                            $retorno["vinculosh"][$iVincsh]["tipovinculoceresadl"] = $_SESSION["maestrovinculos"][$vin["vinculo"]]["tipovinculoceresadl"];
                        } else {
                            $retorno["vinculosh"][$iVincsh]["tipovinculo"] = '';
                            $retorno["vinculosh"][$iVincsh]["tipovinculoceresadl"] = '';
                        }
                        $retorno["vinculosh"][$iVincsh]["renrem"] = $vin["textorenunciaremocion"];
                        if (isset($vin["textoembargo"]) && trim((string) $vin["textoembargo"]) != '') {
                            $retorno["vinculosh"][$iVincsh]["textoembargo"] = $vin["textoembargo"];
                        }
                        $retorno["vinculosh"][$iVincsh]["nacionalidad"] = '';
                        $retorno["vinculosh"][$iVincsh]["proindiviso"] = '';
                        if (isset($vin["nacionalidad"])) {
                            $retorno["vinculosh"][$iVincsh]["nacionalidad"] = stripslashes((string) $vin["nacionalidad"]);
                        }
                        if (isset($vin["proindiviso"])) {
                            $retorno["vinculosh"][$iVincsh]["proindiviso"] = stripslashes((string) $vin["proindiviso"]);
                        }
                    }

                    if (isset($_SESSION["maestrovinculos"][$vin["vinculo"]])) {
                        if (
                                $_SESSION["maestrovinculos"][$vin["vinculo"]]["tipovinculo"] == 'RLP' ||
                                $_SESSION["maestrovinculos"][$vin["vinculo"]]["tipovinculo"] == 'RLS' ||
                                $_SESSION["maestrovinculos"][$vin["vinculo"]]["tipovinculo"] == 'RLS1' ||
                                $_SESSION["maestrovinculos"][$vin["vinculo"]]["tipovinculo"] == 'RLS2' ||
                                $_SESSION["maestrovinculos"][$vin["vinculo"]]["tipovinculo"] == 'RLS3' ||
                                $_SESSION["maestrovinculos"][$vin["vinculo"]]["tipovinculo"] == 'RLS4' ||
                                $_SESSION["maestrovinculos"][$vin["vinculo"]]["tipovinculo"] == 'GER'
                        ) {
                            if ($vin["estado"] == 'V') {
                                $iReps++;
                                $retorno["replegal"][$iReps]["idtipoidentificacionreplegal"] = $vin["idclase"];
                                $retorno["replegal"][$iReps]["identificacionreplegal"] = $vin["numid"];
                                $retorno["replegal"][$iReps]["nombrereplegal"] = stripslashes((string) $vin["nombre"]);
                                $retorno["replegal"][$iReps]["nombre1replegal"] = stripslashes((string) $vin["nom1"]);
                                $retorno["replegal"][$iReps]["nombre2replegal"] = stripslashes((string) $vin["nom2"]);
                                $retorno["replegal"][$iReps]["apellido1replegal"] = stripslashes((string) $vin["ape1"]);
                                $retorno["replegal"][$iReps]["apellido2replegal"] = stripslashes((string) $vin["ape2"]);
                                $retorno["replegal"][$iReps]["emailreplegal"] = stripslashes((string) $vin["email"]);
                                $retorno["replegal"][$iReps]["celularreplegal"] = ($vin["celular"]);
                                $retorno["replegal"][$iReps]["cargoreplegal"] = stripslashes((string) $vin["descargo"]);
                                $retorno["replegal"][$iReps]["vinculoreplegal"] = ($vin["vinculo"]);
                                $retorno["replegal"][$iReps]["libroreplegal"] = ($vin["idlibro"]);
                                $retorno["replegal"][$iReps]["inscripcionreplegal"] = ($vin["numreg"]);
                                $retorno["replegal"][$iReps]["fechareplegal"] = ($vin["fecha"]);
                                $retorno["replegal"][$iReps]["tipovinculo"] = $_SESSION["maestrovinculos"][$vin["vinculo"]]["tipovinculo"];
                            }
                        }
                    }
                    // Primer representante legal y administrador
                    if (isset($_SESSION["maestrovinculos"][$vin["vinculo"]])) {
                        if ($vin["estado"] == 'V') {
                            if (
                                    $_SESSION["maestrovinculos"][$vin["vinculo"]]["tipovinculo"] == 'RLP' ||
                                    $_SESSION["maestrovinculos"][$vin["vinculo"]]["tipovinculo"] == 'GER'
                            ) {
                                $primerRep++;
                                if ($primerRep == 1) {
                                    $retorno["idtipoidentificacionreplegal"] = $vin["idclase"];
                                    $retorno["identificacionreplegal"] = $vin["numid"];
                                    $retorno["nombrereplegal"] = stripslashes((string) $vin["nombre"]);
                                }
                            }
                            if ($_SESSION["maestrovinculos"][$vin["vinculo"]]["tipovinculo"] == 'ADMP') {
                                $retorno["idtipoidentificacionadministrador"] = $vin["idclase"];
                                $retorno["identificacionadministrador"] = $vin["numid"];
                                $retorno["nombreadministrador"] = stripslashes((string) $vin["nombre"]);
                            }
                        }
                    }
                }
            }
        }
        if ($genlog == 'si') {
            \logApi::general2($nameLog, $retorno["matricula"], 'Leyo mreg_est_vinculos');
        }

        $retorno["vincuprop"] = array();
        $iVinProp = 0;

        // Propietarios
        $retorno["propietarios"] = array();
        if ($retorno["organizacion"] == '02') {
            $props = retornarRegistrosMysqliApi($mysqli, 'mreg_est_propietarios', "matricula='" . $retorno["matricula"] . "'", "id");
            $iProp = 0;
            if ($props) {
                if (!empty($props)) {
                    foreach ($props as $prop) {
                        if ($prop["estado"] == 'V') {
                            $iProp++;
                            $prop["fecmatprop"] = '';
                            $prop["fecrenprop"] = '';
                            $prop["ultanorenprop"] = '';
                            $prop["organizacionprop"] = '';
                            $prop["estadodatosprop"] = '';
                            $prop["estadomatriculaprop"] = '';
                            $prop["afiliacionprop"] = '';
                            $prop["ultanorenafliaprop"] = '';
                            $prop["saldoafliaprop"] = 0;
                            $prop["nombrereplegal"] = '';
                            $prop["nit"] = '';
                            $prop["identificacionreplegal"] = '';
                            $prop["tipoidentificacionreplegal"] = '';

                            if ($prop["codigocamara"] == $_SESSION["generales"]["codigoempresa"]) {
                                if (ltrim(trim($prop["matriculapropietario"]), "0") != '') {
                                    $xprop = retornarRegistroMysqliApi($mysqli, 'mreg_est_inscritos', "matricula='" . ltrim(trim($prop["matriculapropietario"]), "0") . "'");
                                    if ($xprop === false || empty($xprop)) {
                                        $prop["tipoidentificacion"] = '';
                                        $prop["identificacion"] = '';
                                        $prop["nit"] = '';
                                        $prop["razonsocial"] = '';
                                        $prop["nombre1"] = '';
                                        $prop["nombre2"] = '';
                                        $prop["apellido1"] = '';
                                        $prop["apellido2"] = '';
                                        $prop["dircom"] = '';
                                        $prop["muncom"] = '';
                                        $prop["dirnot"] = '';
                                        $prop["munnot"] = '';
                                        $prop["telcom1"] = '';
                                        $prop["telcom2"] = '';
                                        $prop["telcom3"] = '';
                                        $prop["fecmatprop"] = '';
                                        $prop["fecrenprop"] = '';
                                        $prop["ultanorenprop"] = '';
                                        $prop["organizacionprop"] = '';
                                        $prop["estadodatosprop"] = '';
                                        $prop["estadomatriculaprop"] = '';
                                        $prop["afiliacionprop"] = '';
                                        $prop["ultanorenafliaprop"] = '';
                                        $prop["saldoafliaprop"] = '';
                                        $prop["nombrereplegal"] = '';
                                        $prop["identificacionreplegal"] = '';
                                        $prop["tipoidentificacionreplegal"] = '';
                                        $prop["tipopropiedad"] = '';
                                    } else {
                                        $prop["tipoidentificacion"] = $xprop["idclase"];
                                        $prop["identificacion"] = $xprop["numid"];
                                        $prop["nit"] = $xprop["nit"];
                                        $prop["razonsocial"] = \funcionesRegistrales::retornarExpedienteMercantilRazonSocial($mysqli, $prop["matriculapropietario"]);
                                        $prop["nombre1"] = $xprop["nombre1"];
                                        $prop["nombre2"] = $xprop["nombre2"];
                                        $prop["apellido1"] = $xprop["apellido1"];
                                        $prop["apellido2"] = $xprop["apellido2"];
                                        $prop["dircom"] = $xprop["dircom"];
                                        $prop["muncom"] = $xprop["muncom"];
                                        $prop["dirnot"] = $xprop["dirnot"];
                                        $prop["munnot"] = $xprop["munnot"];
                                        $prop["telcom1"] = $xprop["telcom1"];
                                        $prop["telcom2"] = $xprop["telcom2"];
                                        $prop["telcom3"] = $xprop["telcom3"];
                                        $prop["fecmatprop"] = $xprop["fecmatricula"];
                                        $prop["fecrenprop"] = $xprop["fecrenovacion"];
                                        $prop["ultanorenprop"] = $xprop["ultanoren"];
                                        $histprop = encontrarHistoricoPagosMysqliApi($mysqli, $xprop["matricula"], $serviciosRenovacion, $serviciosAfiliacion, $serviciosMatricula);
                                        $prop["fecrenprop"] = $histprop["fecultren"];
                                        $prop["ultanorenprop"] = $histprop["ultanoren"];
                                        $prop["acttot"] = $histprop["actultren"];
                                        $prop["ultimosactivosreportados"] = $histprop["actultren"];
                                        $prop["organizacionprop"] = $xprop["organizacion"];
                                        $prop["estadodatosprop"] = $xprop["ctrestdatos"];
                                        $prop["estadomatriculaprop"] = $xprop["ctrestmatricula"];
                                        $prop["afiliacionprop"] = $xprop["ctrafiliacion"];
                                        $prop["ultanorenafliaprop"] = $xprop["anorenaflia"];
                                        $prop["saldoafliaprop"] = $xprop["saldoaflia"];
                                        $prop["nombrereplegal"] = '';
                                        $prop["identificacionreplegal"] = '';
                                        $prop["tipoidentificacionreplegal"] = '';
                                        $prop["razonsocial"] = \funcionesRegistrales::retornarExpedienteMercantilRazonSocial($mysqli, $prop["matriculapropietario"]);
                                        $iXvincs = 0;
                                        $xvincs = retornarRegistrosMysqliApi($mysqli, 'mreg_est_vinculos', "matricula='" . $xprop["matricula"] . "'", "id");
                                        if ($xvincs && !empty($xvincs)) {
                                            foreach ($xvincs as $xvin) {
                                                if ($xvin["estado"] == 'V') {
                                                    if (
                                                            $_SESSION["maestrovinculos"][$xvin["vinculo"]]["tipovinculo"] == 'RLP' ||
                                                            $_SESSION["maestrovinculos"][$xvin["vinculo"]]["tipovinculo"] == 'GER'
                                                    ) {
                                                        $iXvincs++;
                                                        if ($prop["nombrereplegal"] == '') {
                                                            $prop["nombrereplegal"] = stripslashes($xvin["nombre"]);
                                                            $prop["tipoidentificacionreplegal"] = $xvin["idclase"];
                                                            $prop["identificacionreplegal"] = $xvin["numid"];
                                                        }
                                                    }
                                                    $iVinProp++;
                                                    $retorno["vincuprop"][$iVinProp] = array();
                                                    $retorno["vincuprop"][$iVinProp]["idclase"] = $xvin["idclase"];
                                                    $retorno["vincuprop"][$iVinProp]["numid"] = $xvin["numid"];
                                                    $retorno["vincuprop"][$iVinProp]["nombre"] = $xvin["nombre"];
                                                    $retorno["vincuprop"][$iVinProp]["vinculo"] = $xvin["vinculo"];
                                                    $retorno["vincuprop"][$iVinProp]["cargo"] = $xvin["descargo"];
                                                    $retorno["vincuprop"][$iVinProp]["tipovinculo"] = $_SESSION["maestrovinculos"][$xvin["vinculo"]]["tipovinculo"];
                                                    $retorno["vincuprop"][$iVinProp]["tipovinculoesadl"] = $_SESSION["maestrovinculos"][$xvin["vinculo"]]["tipovinculoceresadl"];
                                                    $retorno["vincuprop"][$iVinProp]["puedereactivar"] = $_SESSION["maestrovinculos"][$xvin["vinculo"]]["puedereactivar"];
                                                }
                                            }
                                        }
                                    }
                                }
                            }


                            $retorno["propietarios"][$iProp]["id"] = $prop["id"];
                            $retorno["propietarios"][$iProp]["camarapropietario"] = $prop["codigocamara"];
                            $retorno["propietarios"][$iProp]["matriculapropietario"] = ltrim($prop["matriculapropietario"], '0');
                            $retorno["propietarios"][$iProp]["idtipoidentificacionpropietario"] = trim($prop["tipoidentificacion"]);
                            $retorno["propietarios"][$iProp]["identificacionpropietario"] = ltrim($prop["identificacion"], '0');
                            $retorno["propietarios"][$iProp]["nitpropietario"] = '';
                            if (ltrim(trim($prop["nit"]), '0') == '') {
                                if ($retorno["propietarios"][$iProp]["idtipoidentificacionpropietario"] == '2') {
                                    $retorno["propietarios"][$iProp]["nitpropietario"] = $retorno["propietarios"][$iProp]["identificacionpropietario"];
                                }
                            } else {
                                $retorno["propietarios"][$iProp]["nitpropietario"] = ltrim($prop["nit"], '0');
                            }
                            $retorno["propietarios"][$iProp]["nombrepropietario"] = ($prop["razonsocial"]);
                            $retorno["propietarios"][$iProp]["nom1propietario"] = ($prop["nombre1"]);
                            $retorno["propietarios"][$iProp]["nom2propietario"] = ($prop["nombre2"]);
                            $retorno["propietarios"][$iProp]["ape1propietario"] = ($prop["apellido1"]);
                            $retorno["propietarios"][$iProp]["ape2propietario"] = ($prop["apellido2"]);
                            $retorno["propietarios"][$iProp]["tipopropiedad"] = ($prop["tipopropiedad"]);
                            $retorno["tipopropiedad"] = $prop["tipopropiedad"];
                            $retorno["propietarios"][$iProp]["direccionpropietario"] = ($prop["dircom"]);
                            $retorno["propietarios"][$iProp]["municipiopropietario"] = ($prop["muncom"]);
                            $retorno["propietarios"][$iProp]["direccionnotpropietario"] = ($prop["dirnot"]);
                            $retorno["propietarios"][$iProp]["municipionotpropietario"] = ($prop["munnot"]);
                            $retorno["propietarios"][$iProp]["telefonopropietario"] = ($prop["telcom1"]);
                            $retorno["propietarios"][$iProp]["telefono2propietario"] = ($prop["telcom2"]);
                            $retorno["propietarios"][$iProp]["celularpropietario"] = ($prop["telcom3"]);
                            $retorno["propietarios"][$iProp]["nomreplegpropietario"] = ($prop["nombrereplegal"]);
                            $retorno["propietarios"][$iProp]["numidreplegpropietario"] = ($prop["identificacionreplegal"]);
                            $retorno["propietarios"][$iProp]["tipoidreplegpropietario"] = ($prop["tipoidentificacionreplegal"]);
                            if (isset($prop["participacion"])) {
                                $retorno["propietarios"][$iProp]["participacionpropietario"] = $prop["participacion"];
                            } else {
                                $retorno["propietarios"][$iProp]["participacionpropietario"] = 0;
                            }

                            $retorno["propietarios"][$iProp]["fecmatripropietario"] = '';
                            $retorno["propietarios"][$iProp]["fecrenovpropietario"] = '';
                            $retorno["propietarios"][$iProp]["ultanorenpropietario"] = '';
                            $retorno["propietarios"][$iProp]["organizacionpropietario"] = '';
                            $retorno["propietarios"][$iProp]["estadodatospropietario"] = '';
                            $retorno["propietarios"][$iProp]["estadomatriculapropietario"] = '';
                            $retorno["propietarios"][$iProp]["afiliacionpropietario"] = '';
                            $retorno["propietarios"][$iProp]["ultanorenafipropietario"] = '';
                            $retorno["propietarios"][$iProp]["saldoafiliadopropietario"] = '';

                            if (isset($prop["fecmatprop"])) {
                                $retorno["propietarios"][$iProp]["fecmatripropietario"] = ($prop["fecmatprop"]);
                            }
                            if (isset($prop["fecrenprop"])) {
                                $retorno["propietarios"][$iProp]["fecrenovpropietario"] = ($prop["fecrenprop"]);
                            }
                            if (isset($prop["ultanorenprop"])) {
                                $retorno["propietarios"][$iProp]["ultanorenpropietario"] = ($prop["ultanorenprop"]);
                            }
                            if (isset($prop["organizacionprop"])) {
                                $retorno["propietarios"][$iProp]["organizacionpropietario"] = ($prop["organizacionprop"]);
                            }
                            if (isset($prop["estadodatosprop"])) {
                                $retorno["propietarios"][$iProp]["estadodatospropietario"] = ($prop["estadodatosprop"]);
                            }
                            if (isset($prop["estadomatriculaprop"])) {
                                $retorno["propietarios"][$iProp]["estadomatriculapropietario"] = ($prop["estadomatriculaprop"]);
                            }
                            if (isset($prop["afiliacionprop"])) {
                                $retorno["propietarios"][$iProp]["afiliacionpropietario"] = ($prop["afiliacionprop"]);
                            }
                            if (isset($prop["ultanorenafliaprop"])) {
                                $retorno["propietarios"][$iProp]["ultanorenafipropietario"] = ($prop["ultanorenafliaprop"]);
                            }
                            if (isset($prop["saldoafliaprop"])) {
                                $retorno["propietarios"][$iProp]["saldoafiliadopropietario"] = ($prop["saldoafliaprop"]);
                            }
                        }
                    }
                }
            }
        }

        if ($genlog == 'si') {
            \logApi::general2($nameLog, $retorno["matricula"], 'Leyo mreg_est_propietarios');
        }

        // Propietarios historicos
        $retorno["propietariosh"] = array();
        if ($retorno["organizacion"] == '02') {
            $props = retornarRegistrosMysqliApi($mysqli, 'mreg_est_propietarios', "matricula='" . $retorno["matricula"] . "'", "id");
            $iProp = 0;
            if ($props) {
                if (!empty($props)) {
                    foreach ($props as $prop) {
                        if ($prop["estado"] == 'H') {
                            $iProp++;
                            if (ltrim($prop["matriculapropietario"], "0") != '') {
                                if (ltrim($prop["codigocamara"], "0") == '') {
                                    $prop["codigocamara"] = $_SESSION["generales"]["codigoempresa"];
                                }
                            }
                            $prop["fecmatprop"] = '';
                            $prop["fecrenprop"] = '';
                            $prop["ultanorenprop"] = '';
                            $prop["organizacionprop"] = '';
                            $prop["estadodatosprop"] = '';
                            $prop["estadomatriculaprop"] = '';
                            $prop["afiliacionprop"] = '';
                            $prop["ultanorenafliaprop"] = '';
                            $prop["saldoafliaprop"] = 0;
                            $prop["nombrereplegal"] = '';
                            $prop["identificacionreplegal"] = '';
                            $prop["tipoidentificacionreplegal"] = '';

                            if ($prop["codigocamara"] == $_SESSION["generales"]["codigoempresa"]) {
                                if (ltrim(trim($prop["matriculapropietario"]), "0") != '') {
                                    $xprop = retornarRegistroMysqliApi($mysqli, 'mreg_est_inscritos', "matricula='" . ltrim($prop["matriculapropietario"], "0") . "'");
                                    if ($xprop === false || empty($xprop)) {
                                        $prop["tipoidentificacion"] = '';
                                        $prop["identificacion"] = '';
                                        $prop["nit"] = '';
                                        $prop["razonsocial"] = '';
                                        $prop["nombre1"] = '';
                                        $prop["nombre2"] = '';
                                        $prop["apellido1"] = '';
                                        $prop["apellido2"] = '';
                                        $prop["dircom"] = '';
                                        $prop["muncom"] = '';
                                        $prop["dirnot"] = '';
                                        $prop["munnot"] = '';
                                        $prop["telcom1"] = '';
                                        $prop["telcom2"] = '';
                                        $prop["telcom3"] = '';
                                        $prop["fecmatprop"] = '';
                                        $prop["fecrenprop"] = '';
                                        $prop["ultanorenprop"] = '';
                                        $prop["organizacionprop"] = '';
                                        $prop["estadodatosprop"] = '';
                                        $prop["estadomatriculaprop"] = '';
                                        $prop["afiliacionprop"] = '';
                                        $prop["ultanorenafliaprop"] = '';
                                        $prop["saldoafliaprop"] = '';
                                        $prop["nombrereplegal"] = '';
                                        $prop["identificacionreplegal"] = '';
                                        $prop["tipoidentificacionreplegal"] = '';
                                    } else {
                                        $prop["tipoidentificacion"] = $xprop["idclase"];
                                        $prop["identificacion"] = $xprop["numid"];
                                        $prop["nit"] = $xprop["nit"];
                                        $prop["razonsocial"] = $xprop["razonsocial"];
                                        $prop["nombre1"] = $xprop["nombre1"];
                                        $prop["nombre2"] = $xprop["nombre2"];
                                        $prop["apellido1"] = $xprop["apellido1"];
                                        $prop["apellido2"] = $xprop["apellido2"];
                                        $prop["dircom"] = $xprop["dircom"];
                                        $prop["muncom"] = $xprop["muncom"];
                                        $prop["dirnot"] = $xprop["dirnot"];
                                        $prop["munnot"] = $xprop["munnot"];
                                        $prop["telcom1"] = $xprop["telcom1"];
                                        $prop["telcom2"] = $xprop["telcom2"];
                                        $prop["telcom3"] = $xprop["telcom3"];
                                        $prop["fecmatprop"] = $xprop["fecmatricula"];
                                        $prop["fecrenprop"] = $xprop["fecrenovacion"];
                                        $prop["ultanorenprop"] = $xprop["ultanoren"];
                                        $prop["organizacionprop"] = $xprop["organizacion"];
                                        $prop["estadodatosprop"] = $xprop["ctrestdatos"];
                                        $prop["estadomatriculaprop"] = $xprop["ctrestmatricula"];
                                        $prop["afiliacionprop"] = $xprop["ctrafiliacion"];
                                        $prop["ultanorenafliaprop"] = $xprop["anorenaflia"];
                                        $prop["saldoafliaprop"] = $xprop["saldoaflia"];

                                        $iXvincs = 0;
                                        $xvincs = retornarRegistrosMysqliApi($mysqli, 'mreg_est_vinculos', "matricula='" . $xprop["matricula"] . "' and estado = 'V'", "id");
                                        if ($xvincs) {
                                            if (!empty($xvincs)) {
                                                foreach ($xvincs as $xvin) {
                                                    if (
                                                            $_SESSION["maestrovinculos"][$xvin["vinculo"]]["tipovinculo"] == 'RLP' ||
                                                            $_SESSION["maestrovinculos"][$xvin["vinculo"]]["tipovinculo"] == 'GER'
                                                    ) {
                                                        $iXvincs++;
                                                        if ($iXvincs == 1) {
                                                            $prop["nombrereplegal"] = stripslashes($xvin["nombre"]);
                                                            $prop["identificacionreplegal"] = $xvin["idclase"];
                                                            $prop["tipoidentificacionreplegal"] = $xvin["numid"];
                                                        }
                                                        $iVinProp++;
                                                        $retorno["vincuprop"][$iVinProp] = array();
                                                        $retorno["vincuprop"][$iVinProp]["idclase"] = $xvin["idclase"];
                                                        $retorno["vincuprop"][$iVinProp]["numid"] = $xvin["numid"];
                                                        $retorno["vincuprop"][$iVinProp]["nombre"] = $xvin["nombre"];
                                                        $retorno["vincuprop"][$iVinProp]["vinculo"] = $xvin["vinculo"];
                                                        $retorno["vincuprop"][$iVinProp]["cargo"] = $xvin["descargo"];
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }

                            $retorno["propietariosh"][$iProp]["id"] = $prop["id"];
                            $retorno["propietariosh"][$iProp]["camarapropietario"] = $prop["codigocamara"];
                            $retorno["propietariosh"][$iProp]["matriculapropietario"] = ltrim($prop["matriculapropietario"], '0');
                            $retorno["propietariosh"][$iProp]["idtipoidentificacionpropietario"] = trim($prop["tipoidentificacion"]);
                            $retorno["propietariosh"][$iProp]["identificacionpropietario"] = ltrim($prop["identificacion"], '0');
                            $retorno["propietariosh"][$iProp]["nitpropietario"] = ltrim($prop["nit"], '0');
                            $retorno["propietariosh"][$iProp]["nombrepropietario"] = ($prop["razonsocial"]);
                            $retorno["propietariosh"][$iProp]["nom1propietario"] = ($prop["nombre1"]);
                            $retorno["propietariosh"][$iProp]["nom2propietario"] = ($prop["nombre2"]);
                            $retorno["propietariosh"][$iProp]["ape1propietario"] = ($prop["apellido1"]);
                            $retorno["propietariosh"][$iProp]["ape2propietario"] = ($prop["apellido2"]);
                            $retorno["propietariosh"][$iProp]["tipopropiedad"] = ($prop["tipopropiedad"]);
                            $retorno["tipopropiedad"] = ($prop["tipopropiedad"]);
                            $retorno["propietariosh"][$iProp]["direccionpropietario"] = ($prop["dircom"]);
                            $retorno["propietariosh"][$iProp]["municipiopropietario"] = ($prop["muncom"]);
                            $retorno["propietariosh"][$iProp]["direccionnotpropietario"] = ($prop["dirnot"]);
                            $retorno["propietariosh"][$iProp]["municipionotpropietario"] = ($prop["munnot"]);
                            $retorno["propietariosh"][$iProp]["telefonopropietario"] = ($prop["telcom1"]);
                            $retorno["propietariosh"][$iProp]["telefono2propietario"] = ($prop["telcom2"]);
                            $retorno["propietariosh"][$iProp]["celularpropietario"] = ($prop["telcom3"]);
                            $retorno["propietariosh"][$iProp]["nomreplegpropietario"] = ($prop["nombrereplegal"]);
                            $retorno["propietariosh"][$iProp]["numidreplegpropietario"] = ($prop["identificacionreplegal"]);
                            $retorno["propietariosh"][$iProp]["tipoidreplegpropietario"] = ($prop["tipoidentificacionreplegal"]);

                            if (isset($prop["participacion"])) {
                                $retorno["propietariosh"][$iProp]["participacionpropietario"] = $prop["participacion"];
                            } else {
                                $retorno["propietariosh"][$iProp]["participacionpropietario"] = 0;
                            }

                            $retorno["propietariosh"][$iProp]["fecmatripropietario"] = '';
                            $retorno["propietariosh"][$iProp]["fecrenovpropietario"] = '';
                            $retorno["propietariosh"][$iProp]["ultanorenpropietario"] = '';
                            $retorno["propietariosh"][$iProp]["organizacionpropietario"] = '';
                            $retorno["propietariosh"][$iProp]["estadodatospropietario"] = '';
                            $retorno["propietariosh"][$iProp]["estadomatriculapropietario"] = '';
                            $retorno["propietariosh"][$iProp]["afiliacionpropietario"] = '';
                            $retorno["propietariosh"][$iProp]["ultanorenafipropietario"] = '';
                            $retorno["propietariosh"][$iProp]["saldoafiliadopropietario"] = '';

                            if (isset($prop["fecmatprop"])) {
                                $retorno["propietariosh"][$iProp]["fecmatripropietario"] = ($prop["fecmatprop"]);
                            }
                            if (isset($prop["fecrenprop"])) {
                                $retorno["propietariosh"][$iProp]["fecrenovpropietario"] = ($prop["fecrenprop"]);
                            }
                            if (isset($prop["ultanorenprop"])) {
                                $retorno["propietariosh"][$iProp]["ultanorenpropietario"] = ($prop["ultanorenprop"]);
                            }
                            if (isset($prop["organizacionprop"])) {
                                $retorno["propietariosh"][$iProp]["organizacionpropietario"] = ($prop["organizacionprop"]);
                            }
                            if (isset($prop["estadodatosprop"])) {
                                $retorno["propietariosh"][$iProp]["estadodatospropietario"] = ($prop["estadodatosprop"]);
                            }
                            if (isset($prop["estadomatriculaprop"])) {
                                $retorno["propietariosh"][$iProp]["estadomatriculapropietario"] = ($prop["estadomatriculaprop"]);
                            }
                            if (isset($prop["afiliacionprop"])) {
                                $retorno["propietariosh"][$iProp]["afiliacionpropietario"] = ($prop["afiliacionprop"]);
                            }
                            if (isset($prop["ultanorenafliaprop"])) {
                                $retorno["propietariosh"][$iProp]["ultanorenafipropietario"] = ($prop["ultanorenafliaprop"]);
                            }
                            if (isset($prop["saldoafliaprop"])) {
                                $retorno["propietariosh"][$iProp]["saldoafiliadopropietario"] = ($prop["saldoafliaprop"]);
                            }
                        }
                    }
                }
            }
        }
        if ($genlog == 'si') {
            \logApi::general2($nameLog, $retorno["matricula"], 'Leyo mreg_est_propietarios historicos');
        }

        // Vinculos de la casa principal, en caso de sucursales o agencias
        if ($retorno["organizacion"] > '02' && ($retorno["categoria"] == '2' || $retorno["categoria"] == '3')) {
            if ($retorno["cpcodcam"] == '' || $retorno["cpcodcam"] == $_SESSION["generales"]["codigoempresa"]) {
                if ($retorno["cpnummat"] != '') {
                    $iXvincs = 0;
                    $xvincs = retornarRegistrosMysqliApi($mysqli, 'mreg_est_vinculos', "matricula='" . $retorno["cpnummat"] . "' and estado = 'V'", "id");
                    if ($xvincs) {
                        if (!empty($xvincs)) {
                            foreach ($xvincs as $xvin) {
                                if (
                                        $_SESSION["maestrovinculos"][$xvin["vinculo"]]["tipovinculo"] == 'RLP' ||
                                        $_SESSION["maestrovinculos"][$xvin["vinculo"]]["tipovinculo"] == 'GER'
                                ) {
                                    $iXvincs++;
                                    if ($iXvincs == 1) {
                                        $prop["nombrereplegal"] = stripslashes($xvin["nombre"]);
                                        $prop["identificacionreplegal"] = $xvin["idclase"];
                                        $prop["tipoidentificacionreplegal"] = $xvin["numid"];
                                    }
                                    $iVinProp++;
                                    $retorno["vincuprop"][$iVinProp] = array();
                                    $retorno["vincuprop"][$iVinProp]["idclase"] = $xvin["idclase"];
                                    $retorno["vincuprop"][$iVinProp]["numid"] = $xvin["numid"];
                                    $retorno["vincuprop"][$iVinProp]["nombre"] = $xvin["nombre"];
                                    $retorno["vincuprop"][$iVinProp]["vinculo"] = $xvin["vinculo"];
                                    $retorno["vincuprop"][$iVinProp]["cargo"] = $xvin["descargo"];
                                }
                            }
                        }
                    }
                }
            }
        }
        if ($genlog == 'si') {
            \logApi::general2($nameLog, $retorno["matricula"], 'Leyo mreg_est_vinculos de la casa principal');
        }

        // \logApi::general2($nameLog, $retorno["matricula"], 'Entró a relación establecimientos : ' . date("His"));
        // Relación de establecimientos
        $retorno["establecimientos"] = array();
        if ($retorno["organizacion"] != '02' && (trim($retorno["categoria"]) == '' || $retorno["categoria"] == '0' || $retorno["categoria"] == '1')) {
            $in = "'" . ltrim(trim($retorno["matricula"]), "0") . "'";
            $arrX = retornarRegistrosMysqliApi($mysqli, 'mreg_est_propietarios', "matriculapropietario IN (" . $in . ")", "matricula");
            if ($arrX === false) {
                echo $_SESSION["generales"]["mensajeerror"];
            }
            if ($arrX && !empty($arrX)) {
                $ix = 0;
                foreach ($arrX as $x) {
                    if (ltrim(trim($x["matricula"]), "0") != '') {
                        if ($x["estado"] != 'H') {
                            if ($x["codigocamara"] == '' || $x["codigocamara"] == $_SESSION["generales"]["codigoempresa"]) {
                                $temEst = retornarRegistroMysqliApi($mysqli, 'mreg_est_inscritos', "matricula='" . $x["matricula"] . "'");
                                if (isset($temEst["ctrestmatricula"]) && ($temEst["ctrestmatricula"] == 'MA' || $temEst["ctrestmatricula"] == 'MI')) {
                                    if ($temEst["organizacion"] == '02') {
                                        $ix++;
                                        $retorno["establecimientos"][$ix]["matriculaestablecimiento"] = $x["matricula"];
                                        $retorno["establecimientos"][$ix]["nombreestablecimiento"] = $temEst["razonsocial"];
                                        $retorno["establecimientos"][$ix]["estadodatosestablecimiento"] = $temEst["ctrestmatricula"];
                                        $retorno["establecimientos"][$ix]["estadomatricula"] = $temEst["ctrestmatricula"];
                                        $retorno["establecimientos"][$ix]["dircom"] = $temEst["dircom"];
                                        $retorno["establecimientos"][$ix]["barriocom"] = $temEst["barriocom"];
                                        $retorno["establecimientos"][$ix]["telcom1"] = $temEst["telcom1"];
                                        $retorno["establecimientos"][$ix]["telcom2"] = $temEst["telcom2"];
                                        $retorno["establecimientos"][$ix]["telcom3"] = $temEst["telcom3"];
                                        $retorno["establecimientos"][$ix]["muncom"] = $temEst["muncom"];
                                        $retorno["establecimientos"][$ix]["emailcom"] = $temEst["emailcom"];
                                        $retorno["establecimientos"][$ix]["fechamatricula"] = $temEst["fecmatricula"];
                                        $retorno["establecimientos"][$ix]["fecharenovacion"] = $temEst["fecrenovacion"];
                                        $retorno["establecimientos"][$ix]["ultanoren"] = $temEst["ultanoren"];
                                        $retorno["establecimientos"][$ix]["actvin"] = $temEst["actvin"];
                                        $histoest = encontrarHistoricoPagosMysqliApi($mysqli, $x["matricula"], $serviciosRenovacion, $serviciosAfiliacion, $serviciosMatricula);
                                        $retorno["establecimientos"][$ix]["fecharenovacion"] = $histoest["fecultren"];
                                        $retorno["establecimientos"][$ix]["ultanoren"] = $histoest["ultanoren"];
                                        if (isset($histoest["actultren"]) && is_numeric($histoest["actultren"])) {
                                            $retorno["establecimientos"][$ix]["actvin"] = $histoest["actultren"];
                                        }
                                        $retorno["establecimientos"][$ix]["ciiu1"] = $temEst["ciiu1"];
                                        $retorno["establecimientos"][$ix]["ciiu2"] = $temEst["ciiu2"];
                                        $retorno["establecimientos"][$ix]["ciiu3"] = $temEst["ciiu3"];
                                        $retorno["establecimientos"][$ix]["ciiu4"] = $temEst["ciiu4"];
                                        $retorno["establecimientos"][$ix]["embargado"] = 'NO';
                                        $retorno["establecimientos"][$ix]["embargos"] = array();

                                        $temE = retornarRegistrosMysqliApi($mysqli, 'mreg_est_embargos', "matricula='" . trim($x["matricula"]) . "' and acto IN ('0900','0940','0991','1000','1040') and ctrestadoembargo='1'");
                                        if ($temE && !empty($temE)) {
                                            $iEmb = 0;
                                            foreach ($temE as $e) {
                                                $retorno["establecimientos"][$ix]["embargado"] = 'SI';
                                                $iEmb++;
                                                $retorno["establecimientos"][$ix]["embargos"][$iEmb]["libroembargo"] = $e["libro"];
                                                $retorno["establecimientos"][$ix]["embargos"][$iEmb]["registroembargo"] = $e["numreg"];
                                                $retorno["establecimientos"][$ix]["embargos"][$iEmb]["fechaembargo"] = $e["fecinscripcion"];
                                                $retorno["establecimientos"][$ix]["embargos"][$iEmb]["txtorigenembargo"] = $e["txtorigen"];
                                                $retorno["establecimientos"][$ix]["embargos"][$iEmb]["noticiaembargo"] = stripslashes($e["noticia"]);
                                            }
                                        }

                                        $retorno["establecimientos"][$ix]["valest"] = $temEst["actvin"];
                                        $retorno["establecimientos"][$ix]["tipopropiedad"] = '';
                                        $retorno["establecimientos"][$ix]["ideadministrador"] = '';
                                        $retorno["establecimientos"][$ix]["nombreadministrador"] = '';
                                        $retorno["establecimientos"][$ix]["idearrendatario"] = '';
                                        $retorno["establecimientos"][$ix]["nombrearrendatario"] = '';

                                        $temE = retornarRegistrosMysqliApi($mysqli, 'mreg_est_vinculos', "matricula='" . $x["matricula"] . "' and estado='V'", "vinculo");
                                        if ($temE && !empty($temE)) {
                                            foreach ($temE as $tx) {
                                                if (isset($_SESSION["maestrovinculos"][$tx["vinculo"]]["tipovinculo"]) && $_SESSION["maestrovinculos"][$tx["vinculo"]]["tipovinculo"] == 'ADMP') {
                                                    $retorno["establecimientos"][$ix]["ideadministrador"] = $tx["numid"];
                                                    $retorno["establecimientos"][$ix]["nombreadministrador"] = $tx["nombre"];
                                                }
                                                if (isset($_SESSION["maestrovinculos"][$tx["vinculo"]]["tipovinculo"]) && $_SESSION["maestrovinculos"][$tx["vinculo"]]["tipovinculo"] == 'ARR') {
                                                    $retorno["establecimientos"][$ix]["idearrendatario"] = $tx["numid"];
                                                    $retorno["establecimientos"][$ix]["nombrearrendatario"] = $tx["nombre"];
                                                }
                                            }
                                        }

                                        // 2019-09-10: JINT: Indicar ai existe una solicitud de cancelacion vigente
                                        $retorno["establecimientos"][$ix]["solicitudcancelacion"] = 'no';
                                        $liqs = retornarRegistrosMysqliApi($mysqli, 'mreg_liquidacion', "idexpedientebase='" . $x["matricula"] . "' and tipotramite='solicitudcancelacionest'");
                                        if ($liqs && !empty($liqs)) {
                                            foreach ($liqs as $l) {
                                                if ($l["idestado"] >= '07' && $l["idestado"] != '08' && $l["idestado"] != '10' && $l["idestado"] != '19' && $l["idestado"] != '44' && $l["idestado"] != '99') {
                                                    $retorno["establecimientos"][$ix]["solicitudcancelacion"] = 'si';
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
            unset($arrX);
        }
        if ($genlog == 'si') {
            \logApi::general2($nameLog, $retorno["matricula"], 'Leyo mreg_est_propietarios relacion de establecimientos');
        }

        //2017-11-22 WSIERRA:         
        $retorno["establecimientosh"] = array();

        if ($retorno["organizacion"] != '02' && (trim($retorno["categoria"]) == '' || $retorno["categoria"] == '0' || $retorno["categoria"] == '1')) {
            $in = "'" . ltrim(trim($retorno["matricula"]), "0") . "'";
            $arrX = retornarRegistrosMysqliApi($mysqli, 'mreg_est_propietarios', "matriculapropietario IN (" . $in . ")", "matricula");
            if ($arrX === false) {
                echo $_SESSION["generales"]["mensajeerror"];
            }
            if ($arrX && !empty($arrX)) {
                $ix = 0;
                foreach ($arrX as $x) {
                    if (ltrim(trim($x["matricula"]), "0") != '') {
                        if ($x["estado"] != 'H') {
                            if ($x["codigocamara"] == '' || $x["codigocamara"] == $_SESSION["generales"]["codigoempresa"]) {
                                $temEst = retornarRegistroMysqliApi($mysqli, 'mreg_est_inscritos', "matricula='" . $x["matricula"] . "'");
                                if ($temEst && !empty($temEst)) {
                                    if ($temEst["ctrestmatricula"] == 'MC' || $temEst["ctrestmatricula"] == 'MF') {
                                        if ($temEst["organizacion"] == '02') {
                                            $ix++;
                                            $retorno["establecimientosh"][$ix]["matriculaestablecimiento"] = $x["matricula"];
                                            $retorno["establecimientosh"][$ix]["nombreestablecimiento"] = $temEst["razonsocial"];
                                            $retorno["establecimientosh"][$ix]["estadodatosestablecimiento"] = $temEst["ctrestmatricula"];
                                            $retorno["establecimientosh"][$ix]["estadomatricula"] = $temEst["ctrestmatricula"];
                                            $retorno["establecimientosh"][$ix]["dircom"] = $temEst["dircom"];
                                            $retorno["establecimientosh"][$ix]["barriocom"] = $temEst["barriocom"];
                                            $retorno["establecimientosh"][$ix]["telcom1"] = $temEst["telcom1"];
                                            $retorno["establecimientosh"][$ix]["telcom2"] = $temEst["telcom2"];
                                            $retorno["establecimientosh"][$ix]["telcom3"] = $temEst["telcom3"];
                                            $retorno["establecimientosh"][$ix]["muncom"] = $temEst["muncom"];
                                            $retorno["establecimientosh"][$ix]["emailcom"] = $temEst["emailcom"];
                                            $retorno["establecimientosh"][$ix]["fechamatricula"] = $temEst["fecmatricula"];
                                            $retorno["establecimientosh"][$ix]["fecharenovacion"] = $temEst["fecrenovacion"];
                                            $retorno["establecimientosh"][$ix]["fechacancelacion"] = $temEst["feccancelacion"];
                                            $retorno["establecimientosh"][$ix]["ultanoren"] = $temEst["ultanoren"];
                                            $retorno["establecimientosh"][$ix]["actvin"] = $temEst["actvin"];
                                            $retorno["establecimientosh"][$ix]["ciiu1"] = $temEst["ciiu1"];
                                            $retorno["establecimientosh"][$ix]["ciiu2"] = $temEst["ciiu2"];
                                            $retorno["establecimientosh"][$ix]["ciiu3"] = $temEst["ciiu3"];
                                            $retorno["establecimientosh"][$ix]["ciiu4"] = $temEst["ciiu4"];
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
            unset($arrX);
        }
        if ($genlog == 'si') {
            \logApi::general2($nameLog, $retorno["matricula"], 'Leyo relacion de establecimientos historicos');
        }

        // Relación de sucursales y agencias
        $retorno["sucursalesagencias"] = array();
        if ($retorno["organizacion"] == '01' || ($retorno["organizacion"] > '02' && $retorno["categoria"] == '1')) {
            $camposRequeridos = 'matricula,razonsocial,categoria,ctrestmatricula,fecmatricula,fecrenovacion,ultanoren,dircom,barriocom,muncom,telcom1,telcom2,telcom3,emailcom,ciiu1,ciiu2,ciiu3,ciiu4,actvin,cpcodcam,cpnummat';
            $arrX = retornarRegistrosMysqliApi($mysqli, 'mreg_est_inscritos', "cpcodcam='" . $_SESSION["generales"]["codigoempresa"] . "' and cpnummat='" . $retorno["matricula"] . "'", "matricula", $camposRequeridos);
            if ($arrX && !empty($arrX)) {
                $ix = 0;
                foreach ($arrX as $x) {
                    if (trim($x["matricula"]) != '') {
                        $ix++;
                        $retorno["sucursalesagencias"][$ix]["matriculasucage"] = $x["matricula"];
                        $retorno["sucursalesagencias"][$ix]["nombresucage"] = $x["razonsocial"];
                        $retorno["sucursalesagencias"][$ix]["categoria"] = $x["categoria"];
                        $retorno["sucursalesagencias"][$ix]["estado"] = $x["ctrestmatricula"];
                        $retorno["sucursalesagencias"][$ix]["fechamatricula"] = $x["fecmatricula"];
                        $retorno["sucursalesagencias"][$ix]["fecharenovacion"] = $x["fecrenovacion"];
                        $retorno["sucursalesagencias"][$ix]["ultanoren"] = $x["ultanoren"];
                        $histoest = encontrarHistoricoPagosMysqliApi($mysqli, $x["matricula"], $serviciosRenovacion, $serviciosAfiliacion, $serviciosMatricula);
                        $retorno["sucursalesagencias"][$ix]["fecharenovacion"] = $histoest["fecultren"];
                        $retorno["sucursalesagencias"][$ix]["ultanoren"] = $histoest["ultanoren"];
                        if (isset($histoest["actultren"]) && is_numeric($histoest["actultren"])) {
                            $retorno["sucursalesagencias"][$ix]["actvin"] = $histoest["actultren"];
                        }
                        $retorno["sucursalesagencias"][$ix]["dircom"] = $x["dircom"];
                        $retorno["sucursalesagencias"][$ix]["barriocom"] = $x["barriocom"];
                        $retorno["sucursalesagencias"][$ix]["muncom"] = $x["muncom"];
                        $retorno["sucursalesagencias"][$ix]["telcom1"] = $x["telcom1"];
                        $retorno["sucursalesagencias"][$ix]["telcom2"] = $x["telcom2"];
                        $retorno["sucursalesagencias"][$ix]["telcom3"] = $x["telcom3"];
                        $retorno["sucursalesagencias"][$ix]["emailcom"] = $x["emailcom"];
                        $retorno["sucursalesagencias"][$ix]["ciiu1"] = $x["ciiu1"];
                        $retorno["sucursalesagencias"][$ix]["ciiu2"] = $x["ciiu2"];
                        $retorno["sucursalesagencias"][$ix]["ciiu3"] = $x["ciiu3"];
                        $retorno["sucursalesagencias"][$ix]["ciiu4"] = $x["ciiu4"];
                        $retorno["sucursalesagencias"][$ix]["embargado"] = 'NO';

                        $retorno["sucursalesagencias"][$ix]["libro1embargo"] = '';
                        $retorno["sucursalesagencias"][$ix]["registro1embargo"] = '';
                        $retorno["sucursalesagencias"][$ix]["fecha1embargo"] = '';
                        $retorno["sucursalesagencias"][$ix]["txtorigen1embargo"] = '';
                        $retorno["sucursalesagencias"][$ix]["libro2embargo"] = '';
                        $retorno["sucursalesagencias"][$ix]["registro2embargo"] = '';
                        $retorno["sucursalesagencias"][$ix]["fecha2embargo"] = '';
                        $retorno["sucursalesagencias"][$ix]["txtorigen2embargo"] = '';
                        $retorno["sucursalesagencias"][$ix]["libro3embargo"] = '';
                        $retorno["sucursalesagencias"][$ix]["registro3embargo"] = '';
                        $retorno["sucursalesagencias"][$ix]["fecha3embargo"] = '';
                        $retorno["sucursalesagencias"][$ix]["txtorigen3embargo"] = '';
                        $retorno["sucursalesagencias"][$ix]["libro4embargo"] = '';
                        $retorno["sucursalesagencias"][$ix]["registro4embargo"] = '';
                        $retorno["sucursalesagencias"][$ix]["fecha4embargo"] = '';
                        $retorno["sucursalesagencias"][$ix]["txtorigen4embargo"] = '';
                        $retorno["sucursalesagencias"][$ix]["libro5embargo"] = '';
                        $retorno["sucursalesagencias"][$ix]["registro5embargo"] = '';
                        $retorno["sucursalesagencias"][$ix]["fecha5embargo"] = '';
                        $retorno["sucursalesagencias"][$ix]["txtorigen5embargo"] = '';
                        $retorno["sucursalesagencias"][$ix]["embargado"] = 'NO';
                        $retorno["sucursalesagencias"][$ix]["embargos"] = array();

                        $temE = retornarRegistrosMysqliApi($mysqli, 'mreg_est_embargos', "matricula='" . trim($x["matricula"]) . "' and acto IN ('0900','0940','0991','1000','1040') and ctrestadoembargo='1'");
                        if ($temE && !empty($temE)) {
                            $iEmb = 0;
                            foreach ($temE as $e) {
                                $retorno["sucursalesagencias"][$ix]["embargado"] = 'SI';
                                $iEmb++;
                                $retorno["sucursalesagencias"][$ix]["embargos"][$iEmb]["libroembargo"] = $e["libro"];
                                $retorno["sucursalesagencias"][$ix]["embargos"][$iEmb]["registroembargo"] = $e["numreg"];
                                $retorno["sucursalesagencias"][$ix]["embargos"][$iEmb]["fechaembargo"] = $e["fecinscripcion"];
                                $retorno["sucursalesagencias"][$ix]["embargos"][$iEmb]["txtorigenembargo"] = $e["txtorigen"];
                            }
                        }

                        $retorno["sucursalesagencias"][$ix]["actvin"] = $x["actvin"];
                    }
                }
            }
            unset($arrX);
        }
        if ($genlog == 'si') {
            \logApi::general2($nameLog, $retorno["matricula"], 'Leyo relacion de sucursales y agencias');
        }


        // \logApi::general2($nameLog, $retorno["matricula"], 'Salió de relación suc/age : ' . date("His"));
        // Relación de establecimientos arrendados
        // \logApi::general2($nameLog, $retorno["matricula"], 'Entró a establecimientos arrendados : ' . date("His"));
        $retorno["establecimientosarrendados"] = array();
        if ($retorno["organizacion"] != '02' && (trim($retorno["categoria"]) == '' || $retorno["categoria"] == '0' || $retorno["categoria"] == '1')) {
            if ($retorno["identificacion"] != '') {
                $arrX = retornarRegistrosMysqliApi($mysqli, 'mreg_est_vinculos', "numid='" . $retorno["identificacion"] . "'", "matricula");
                if ($arrX === false) {
                    echo $_SESSION["generales"]["mensajeerror"];
                }
                if ($arrX && !empty($arrX)) {
                    $ix = 0;
                    foreach ($arrX as $x) {
                        if (isset($_SESSION["maestrovinculos"][$x["vinculo"]]) && $_SESSION["maestrovinculos"][$x["vinculo"]]["tipovinculo"] == 'ARR') {
                            if (ltrim(trim($x["matricula"]), "0") != '') {
                                if ($x["estado"] != 'H') {
                                    $temEst = retornarRegistroMysqliApi($mysqli, 'mreg_est_inscritos', "matricula='" . $x["matricula"] . "'");
                                    if ($temEst["ctrestmatricula"] == 'MA' || $temEst["ctrestmatricula"] == 'MI') {
                                        if ($temEst["organizacion"] == '02') {
                                            $ix++;
                                            $retorno["establecimientosarrendados"][$ix]["matricula"] = $x["matricula"];
                                            $retorno["establecimientosarrendados"][$ix]["nombre"] = $temEst["razonsocial"];
                                            $retorno["establecimientosarrendados"][$ix]["estadodatos"] = $temEst["ctrestmatricula"];
                                            $retorno["establecimientosarrendados"][$ix]["estadomatricula"] = $temEst["ctrestmatricula"];
                                            $retorno["establecimientosarrendados"][$ix]["dircom"] = $temEst["dircom"];
                                            $retorno["establecimientosarrendados"][$ix]["barriocom"] = $temEst["barriocom"];
                                            $retorno["establecimientosarrendados"][$ix]["telcom1"] = $temEst["telcom1"];
                                            $retorno["establecimientosarrendados"][$ix]["telcom2"] = $temEst["telcom2"];
                                            $retorno["establecimientosarrendados"][$ix]["telcom3"] = $temEst["telcom3"];
                                            $retorno["establecimientosarrendados"][$ix]["muncom"] = $temEst["muncom"];
                                            $retorno["establecimientosarrendados"][$ix]["emailcom"] = $temEst["emailcom"];
                                            $retorno["establecimientosarrendados"][$ix]["fechamatricula"] = $temEst["fecmatricula"];
                                            $retorno["establecimientosarrendados"][$ix]["fecharenovacion"] = $temEst["fecrenovacion"];
                                            $retorno["establecimientosarrendados"][$ix]["ultanoren"] = $temEst["ultanoren"];
                                            $retorno["establecimientosarrendados"][$ix]["actvin"] = $temEst["actvin"];
                                            $histoest = encontrarHistoricoPagosMysqliApi($mysqli, $x["matricula"], $serviciosRenovacion, $serviciosAfiliacion, $serviciosMatricula);
                                            $retorno["establecimientosarrendados"][$ix]["fecharenovacion"] = $histoest["fecultren"];
                                            $retorno["establecimientosarrendados"][$ix]["ultanoren"] = $histoest["ultanoren"];
                                            if (isset($histoest["actultren"]) && is_numeric($histoest["actultren"])) {
                                                $retorno["establecimientosarrendados"][$ix]["actvin"] = $histoest["actultren"];
                                            }
                                            $retorno["establecimientosarrendados"][$ix]["ciiu1"] = $temEst["ciiu1"];
                                            $retorno["establecimientosarrendados"][$ix]["ciiu2"] = $temEst["ciiu2"];
                                            $retorno["establecimientosarrendados"][$ix]["ciiu3"] = $temEst["ciiu3"];
                                            $retorno["establecimientosarrendados"][$ix]["ciiu4"] = $temEst["ciiu4"];
                                            $retorno["establecimientosarrendados"][$ix]["valest"] = $temEst["actvin"];
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
            unset($arrX);
        }
        if ($genlog == 'si') {
            \logApi::general2($nameLog, $retorno["matricula"], 'Leyo relacion de establecimientos arrendados');
        }


        // \logApi::general2($nameLog, $retorno["matricula"], 'Salió de establecimientos arrendados : ' . date("His"));
        //  if ($tipodata == 'E') {
        // Relación de establecimientos nacionales 
        // \logApi::general2($nameLog, $retorno["matricula"], 'Entró a establecimientos nacionales : ' . date("His"));

        $retorno["establecimientosnacionales"] = array();
        if ($tipodata == 'E' || $establecimientosnacionales == 'S') {
            if ($retorno["organizacion"] == '01' || $retorno["organizacion"] > '02' && $retorno["categoria"] == '1') {
                $ests = \funcionesGenerales::consultarEstablecimientosNacionales($mysqli, $retorno["tipoidentificacion"], $retorno["identificacion"]);
                if (!empty($ests)) {
                    $ix = 0;

                    foreach ($ests as $x) {
                        if ($x["codigo_estado_matricula"] == '01') { // Solo establecimientos activos
                            if ($x["codigo_camara"] != $_SESSION["generales"]["codigoempresa"]) { // Solo si son de una cámara diferente
                                $ix++;

                                //
                                $retorno["establecimientosnacionales"][$ix]["cc"] = trim($x["codigo_camara"]);
                                $retorno["establecimientosnacionales"][$ix]["matriculaestablecimiento"] = ltrim($x["matricula"], "0");
                                $retorno["establecimientosnacionales"][$ix]["nombreestablecimiento"] = trim($x["razon_social"]);
                                $retorno["establecimientosnacionales"][$ix]["estadodatosestablecimiento"] = '6';
                                $retorno["establecimientosnacionales"][$ix]["estadomatricula"] = 'MA';
                                $retorno["establecimientosnacionales"][$ix]["organizacion"] = trim($x["codigo_organizacion_juridica"]);
                                $retorno["establecimientosnacionales"][$ix]["categoria"] = trim($x["codigo_categoria_matricula"]);

                                //
                                $retorno["establecimientosnacionales"][$ix]["dircom"] = trim($x["direccion_comercial"]);
                                $retorno["establecimientosnacionales"][$ix]["barriocom"] = trim($x["codigo_barrio_comercial"]);
                                $retorno["establecimientosnacionales"][$ix]["nbarriocom"] = trim($x["barrio_comercial"]);
                                $retorno["establecimientosnacionales"][$ix]["telcom1"] = trim($x["telefono_comercial_1"]);
                                $retorno["establecimientosnacionales"][$ix]["telcom2"] = trim($x["telefono_comercial_2"]);
                                $retorno["establecimientosnacionales"][$ix]["telcom3"] = trim($x["telefono_comercial_3"]);
                                $retorno["establecimientosnacionales"][$ix]["muncom"] = trim($x["municipio_comercial"]);
                                $retorno["establecimientosnacionales"][$ix]["nmuncom"] = trim($x["nombre_municipio_comercial"]);
                                $retorno["establecimientosnacionales"][$ix]["emailcom"] = trim($x["correo_electronico_comercial"]);
                                $retorno["establecimientosnacionales"][$ix]["codpostalcom"] = trim($x["codigo_postal_comercial"]);

                                //
                                $retorno["establecimientosnacionales"][$ix]["dirnot"] = trim($x["direccion_fiscal"]);
                                $retorno["establecimientosnacionales"][$ix]["barrionot"] = trim($x["codigo_barrio_fiscal"]);
                                $retorno["establecimientosnacionales"][$ix]["nbarrionot"] = trim($x["barrio_fiscal"]);
                                $retorno["establecimientosnacionales"][$ix]["telnot1"] = '';
                                $retorno["establecimientosnacionales"][$ix]["telnot2"] = '';
                                $retorno["establecimientosnacionales"][$ix]["telnot3"] = '';
                                $retorno["establecimientosnacionales"][$ix]["munnot"] = trim($x["municipio_fiscal"]);
                                $retorno["establecimientosnacionales"][$ix]["nmunnot"] = trim($x["nombre_municipio_fiscal"]);
                                $retorno["establecimientosnacionales"][$ix]["emailnor"] = trim($x["correo_electronico_fiscal"]);
                                $retorno["establecimientosnacionales"][$ix]["codpostalnot"] = trim($x["codigo_postal_fiscal"]);

                                //
                                $retorno["establecimientosnacionales"][$ix]["fechamatricula"] = trim($x["fecha_matricula"]);
                                $retorno["establecimientosnacionales"][$ix]["fecharenovacion"] = trim($x["fecha_renovacion"]);
                                $retorno["establecimientosnacionales"][$ix]["fechacancelacion"] = '';
                                $retorno["establecimientosnacionales"][$ix]["ultanoren"] = trim($x["ultimo_ano_renovado"]);
                                $retorno["establecimientosnacionales"][$ix]["actvin"] = doubleval($x["valor_est_ag_suc"]);
                                $retorno["establecimientosnacionales"][$ix]["empleados"] = doubleval($x["empleados"]);

                                //
                                $retorno["establecimientosnacionales"][$ix]["ciiu1"] = '';
                                $retorno["establecimientosnacionales"][$ix]["ciiu2"] = '';
                                $retorno["establecimientosnacionales"][$ix]["ciiu3"] = '';
                                $retorno["establecimientosnacionales"][$ix]["ciiu4"] = '';
                                $retorno["establecimientosnacionales"][$ix]["shd1"] = '';
                                $retorno["establecimientosnacionales"][$ix]["shd2"] = '';
                                $retorno["establecimientosnacionales"][$ix]["shd3"] = '';
                                $retorno["establecimientosnacionales"][$ix]["shd4"] = '';
                                if (trim($x["ciiu1"]) != '') {
                                    $retorno["establecimientosnacionales"][$ix]["ciiu1"] = retornarRegistroMysqliApi($mysqli, "bas_ciius", "idciiunum='" . trim($x["ciiu1"]) . "'", "idciiu");
                                }
                                if (trim($x["ciiu2"]) != '') {
                                    $retorno["establecimientosnacionales"][$ix]["ciiu2"] = retornarRegistroMysqliApi($mysqli, "bas_ciius", "idciiunum='" . trim($x["ciiu2"]) . "'", "idciiu");
                                }
                                if (trim($x["ciiu3"]) != '') {
                                    $retorno["establecimientosnacionales"][$ix]["ciiu3"] = retornarRegistroMysqliApi($mysqli, "bas_ciius", "idciiunum='" . trim($x["ciiu3"]) . "'", "idciiu");
                                }
                                if (trim($x["ciiu4"]) != '') {
                                    $retorno["establecimientosnacionales"][$ix]["ciiu4"] = retornarRegistroMysqliApi($mysqli, "bas_ciius", "idciiunum='" . trim($x["ciiu4"]) . "'", "idciiu");
                                }
                                if (trim($x["shd1"]) != '') {
                                    $retorno["establecimientosnacionales"][$ix]["shd1"] = $x["shd1"];
                                }
                                if (trim($x["shd2"]) != '') {
                                    $retorno["establecimientosnacionales"][$ix]["shd2"] = $x["shd2"];
                                }
                                if (trim($x["shd3"]) != '') {
                                    $retorno["establecimientosnacionales"][$ix]["shd3"] = $x["shd3"];
                                }
                                if (trim($x["shd4"]) != '') {
                                    $retorno["establecimientosnacionales"][$ix]["shd4"] = $x["shd4"];
                                }
                                $retorno["establecimientosnacionales"][$ix]["desactiv"] = trim($x["desc_Act_Econ"]);

                                //
                                $retorno["establecimientosnacionales"][$ix]["afiliado"] = trim($x["afiliado"]);
                                $retorno["establecimientosnacionales"][$ix]["tipolocal"] = trim($x["codigo_tipo_local"]);
                                $retorno["establecimientosnacionales"][$ix]["ctrubi"] = trim($x["codigo_ubicacion_empresa"]);
                            }
                        }
                    }
                }
            }
        }
        if ($genlog == 'si') {
            \logApi::general2($nameLog, $retorno["matricula"], 'Leyo relacion de establecimientos nacionales');
        }


        // historicopagosrenovacion
        $iPagos = 0;
        $retorno["historicopagosrenovacion"] = array();
        if (!empty($histopagos["renovacionanos"])) {
            foreach ($histopagos["renovacionanos"] as $his) {
                $iPagos++;
                $retorno["historicopagosrenovacion"][$iPagos] = array(
                    'tipo' => 'Aplicadas',
                    'recibo' => $his["recibo"],
                    'fecharecibo' => $his["fecharecibo"],
                    'fecharenovacion' => $his["fecrenovacion"],
                    'ano' => $his["ano"],
                    'activos' => $his["activos"],
                    'valor' => $his["valor"]
                );
                if (isset($his["ai"]) && $his["ai"] == 'si') {
                    $retorno["historicopagosrenovacion"][$iPagos]["tipo"] = 'Pend Asent A.I.';
                }
            }
        }
        if (!empty($histopagos["renovacionsinaplicaranos"])) {
            foreach ($histopagos["renovacionsinaplicaranos"] as $his) {
                $retorno["historicopagosrenovacion"][] = array(
                    'tipo' => 'Sin aplicar',
                    'recibo' => $his["recibo"],
                    'fecharecibo' => $his["fecharecibo"],
                    'fecharenovacion' => $his["fecrenovacion"],
                    'ano' => $his["ano"],
                    'activos' => $his["activos"],
                    'valor' => $his["valor"]
                );
            }
        }

        //
        $retorno["rr"] = array();
        $retorno["lcodigosbarras"] = array();
        $arrX = retornarRegistrosMysqliApi($mysqli, 'mreg_est_codigosbarras', "matricula='" . $retorno["matricula"] . "'", "codigobarras");
        $ix = 0;
        $irr = 0;
        if ($arrX && !empty($arrX)) {
            foreach ($arrX as $x) {
                if (
                        $x["estadofinal"] == '01' || // Radicado
                        $x["estadofinal"] == '04' || // En estudio
                        $x["estadofinal"] == '09' || // Reingresado
                        $x["estadofinal"] == '10' || // Devuelto a reparto
                        $x["estadofinal"] == '11' || // Inscrito
                        $x["estadofinal"] == '13' || // Asignado a estudio
                        $x["estadofinal"] == '22' || // Registrado - proponentes
                        $x["estadofinal"] == '23' || // En digitacion
                        $x["estadofinal"] == '24' || // Digitado
                        $x["estadofinal"] == '34' || // Para firma
                        $x["estadofinal"] == '35' || // Firmado
                        $x["estadofinal"] == '38' // Control de calidad
                ) {
                    if ($x["actoreparto"] != '19' && $x["actoreparto"] != '23' && $x["actoreparto"] != '28' && $x["actoreparto"] != '54') {
                        $ix++;
                        $retorno["lcodigosbarras"][$ix]["cbar"] = $x["codigobarras"];
                        $retorno["lcodigosbarras"][$ix]["frad"] = $x["fecharadicacion"];
                        $retorno["lcodigosbarras"][$ix]["ttra"] = $x["actoreparto"];
                        $retorno["lcodigosbarras"][$ix]["esta"] = $x["estadofinal"];
                        $retorno["lcodigosbarras"][$ix]["nesta"] = '';
                        $retorno["lcodigosbarras"][$ix]["ntra"] = retornarRegistroMysqliApi($mysqli, 'mreg_codrutas', "id='" . $retorno["lcodigosbarras"][$ix]["ttra"] . "'", "descripcion");
                        $retorno["lcodigosbarras"][$ix]["sist"] = retornarRegistroMysqliApi($mysqli, 'mreg_codrutas', "id='" . $retorno["lcodigosbarras"][$ix]["ttra"] . "'", "tipo");
                        if (($retorno["lcodigosbarras"][$ix]["sist"] == 'ME') || ($retorno["lcodigosbarras"][$ix]["sist"] == 'ES') ||
                                ($retorno["lcodigosbarras"][$ix]["sist"] == 'RM') || ($retorno["lcodigosbarras"][$ix]["sist"] == 'RE')
                        ) {
                            $retorno["lcodigosbarras"][$ix]["nesta"] = retornarRegistroMysqliApi($mysqli, 'mreg_codestados_rutamercantil', "id='" . $retorno["lcodigosbarras"][$ix]["esta"] . "'", "descripcion");
                        }
                        if ($retorno["lcodigosbarras"][$ix]["sist"] == 'PR') {
                            $retorno["lcodigosbarras"][$ix]["nesta"] = retornarRegistroMysqliApi($mysqli, 'mreg_codestados_rutaproponentes', "id='" . $retorno["lcodigosbarras"][$ix]["esta"] . "'", "descripcion");
                        }
                        $retorno["codigosbarras"]++;

                        // Si la ruta es un embargo
                        if ($x["actoreparto"] == '07') {
                            $retorno["embargostramite"] = 'S';
                        }
                    }

                    // Si la ruta es un recurso en trámite
                    if ($x["actoreparto"] == '19' || $x["actoreparto"] == '23' || $x["actoreparto"] == '28' || $x["actoreparto"] == '54') {
                        $retorno["recursostramite"] = 'S';
                        $irr++;
                        $retorno["rr"][$irr] = array();
                        $retorno["rr"][$irr]["codigobarras"] = $x["codigobarras"];
                        $retorno["rr"][$irr]["fecharadicacion"] = $x["fecharadicacion"];
                        $retorno["rr"][$irr]["detalle"] = $x["detalle"];
                        $retorno["rr"][$irr]["estado"] = $x["estadofinal"];
                        $retorno["rr"][$irr]["tipodocrecurso"] = '';
                        $retorno["rr"][$irr]["numdocrecurso"] = '';
                        $retorno["rr"][$irr]["fecdocrecurso"] = '';
                        $retorno["rr"][$irr]["idclaserecurrente"] = '';
                        $retorno["rr"][$irr]["numidrecurrente"] = '';
                        $retorno["rr"][$irr]["nombrerecurrente"] = '';
                        $retorno["rr"][$irr]["tipodocafectado"] = '';
                        $retorno["rr"][$irr]["numdocafectado"] = '';
                        $retorno["rr"][$irr]["fecdocafectado"] = '';
                        $retorno["rr"][$irr]["libroafectado"] = '';
                        $retorno["rr"][$irr]["registroafectado"] = '';
                        $retorno["rr"][$irr]["dupliafectado"] = '';
                        $retorno["rr"][$irr]["subsidioapelacion"] = '';
                        $retorno["rr"][$irr]["fecharegistroafectado"] = '';
                        $retorno["rr"][$irr]["noticiarecurrida"] = '';
                        $retorno["rr"][$irr]["confirmainscripcion"] = '';
                        $retorno["rr"][$irr]["numeroresolucion"] = '';
                        $retorno["rr"][$irr]["fecharesolucion"] = '';
                        $retorno["rr"][$irr]["tipodocrecurso"] = $x["tipdoc"];
                        $retorno["rr"][$irr]["numdocrecurso"] = $x["numdoc"];
                        $retorno["rr"][$irr]["fecdocrecurso"] = $x["fecdoc"];
                        $retorno["rr"][$irr]["idclaserecurrente"] = $x["idclaserecurrente"];
                        $retorno["rr"][$irr]["numidrecurrente"] = $x["numidrecurrente"];
                        $retorno["rr"][$irr]["nombrerecurrente"] = $x["nombrerecurrente"];
                        $retorno["rr"][$irr]["libroafectado"] = $x["libroafectado"];
                        $retorno["rr"][$irr]["registroafectado"] = $x["registroafectado"];
                        $retorno["rr"][$irr]["dupliafectado"] = $x["dupliafectado"];
                        $retorno["rr"][$irr]["subsidioapelacion"] = $x["subsidioapelacion"];
                        $retorno["rr"][$irr]["confirmainscripcion"] = $x["confirmainscripcion"];
                        $retorno["rr"][$irr]["numeroresolucion"] = $x["numeroresolucion"];
                        $retorno["rr"][$irr]["fecharesolucion"] = $x["fecharesolucion"];
                        $retorno["rr"][$irr]["numidrecurrente2"] = retornarRegistroMysqliApi($mysqli, 'mreg_est_codigosbarras_campos', "codigobarras='" . $x["codigobarras"] . "' and campo='numidrecurrente2'", "contenido");
                        $retorno["rr"][$irr]["nombrerecurrente2"] = retornarRegistroMysqliApi($mysqli, 'mreg_est_codigosbarras_campos', "codigobarras='" . $x["codigobarras"] . "' and campo='nombrerecurrente2'", "contenido");
                        $retorno["rr"][$irr]["numidrecurrente3"] = retornarRegistroMysqliApi($mysqli, 'mreg_est_codigosbarras_campos', "codigobarras='" . $x["codigobarras"] . "' and campo='numidrecurrente3'", "contenido");
                        $retorno["rr"][$irr]["nombrerecurrente3"] = retornarRegistroMysqliApi($mysqli, 'mreg_est_codigosbarras_campos', "codigobarras='" . $x["codigobarras"] . "' and campo='nombrerecurrente3'", "contenido");
                        $retorno["rr"][$irr]["soloapelacion"] = retornarRegistroMysqliApi($mysqli, 'mreg_est_codigosbarras_campos', "codigobarras='" . $x["codigobarras"] . "' and campo='soloapelacion'", "contenido");

                        $retorno["rr"][$irr]["libroafectado2"] = '';
                        $retorno["rr"][$irr]["registroafectado2"] = '';
                        $retorno["rr"][$irr]["dupliafectado2"] = '';

                        $retorno["rr"][$irr]["libroafectado3"] = '';
                        $retorno["rr"][$irr]["registroafectado3"] = '';
                        $retorno["rr"][$irr]["dupliafectado3"] = '';

                        $retorno["rr"][$irr]["libroafectado4"] = '';
                        $retorno["rr"][$irr]["registroafectado42"] = '';
                        $retorno["rr"][$irr]["dupliafectado4"] = '';

                        $retorno["rr"][$irr]["tipodocafectado2"] = '';
                        $retorno["rr"][$irr]["numdocafectado2"] = '';
                        $retorno["rr"][$irr]["fecdocafectado2"] = '';
                        $retorno["rr"][$irr]["fecharegistroafectado2"] = '';
                        $retorno["rr"][$irr]["noticiarecurrida2"] = '';

                        $retorno["rr"][$irr]["tipodocafectado3"] = '';
                        $retorno["rr"][$irr]["numdocafectado3"] = '';
                        $retorno["rr"][$irr]["fecdocafectado3"] = '';
                        $retorno["rr"][$irr]["fecharegistroafectado3"] = '';
                        $retorno["rr"][$irr]["noticiarecurrida3"] = '';

                        $retorno["rr"][$irr]["tipodocafectado4"] = '';
                        $retorno["rr"][$irr]["numdocafectado4"] = '';
                        $retorno["rr"][$irr]["fecdocafectado4"] = '';
                        $retorno["rr"][$irr]["fecharegistroafectado4"] = '';
                        $retorno["rr"][$irr]["noticiarecurrida4"] = '';

                        $inscxs = retornarRegistroMysqliApi($mysqli, 'mreg_est_codigosbarras_campos', "codigobarras='" . $x["codigobarras"] . "' and campo='inscripcionafectada2'", "contenido");
                        if ($inscxs != '') {
                            list ($retorno["rr"][$irr]["libroafectado2"], $retorno["rr"][$irr]["registroafectado2"], $retorno["rr"][$irr]["dupliafectado2"]) = explode("-", $inscxs);
                        }
                        $inscxs = retornarRegistroMysqliApi($mysqli, 'mreg_est_codigosbarras_campos', "codigobarras='" . $x["codigobarras"] . "' and campo='inscripcionafectada3'", "contenido");
                        if ($inscxs != '') {
                            list ($retorno["rr"][$irr]["libroafectado3"], $retorno["rr"][$irr]["registroafectado3"], $retorno["rr"][$irr]["dupliafectado3"]) = explode("-", $inscxs);
                        }
                        $inscxs = retornarRegistroMysqliApi($mysqli, 'mreg_est_codigosbarras_campos', "codigobarras='" . $x["codigobarras"] . "' and campo='inscripcionafectada4'", "contenido");
                        if ($inscxs != '') {
                            list ($retorno["rr"][$irr]["libroafectado4"], $retorno["rr"][$irr]["registroafectado4"], $retorno["rr"][$irr]["dupliafectado4"]) = explode("-", $inscxs);
                        }

                        if ($retorno["rr"][$irr]["libroafectado"] != '' && $retorno["rr"][$irr]["registroafectado"] != '' && $retorno["rr"][$irr]["dupliafectado"] != '') {
                            $condx = "libro='" . $retorno["rr"][$irr]["libroafectado"] . "' and registro='" . $retorno["rr"][$irr]["registroafectado"] . "' and dupli='" . $retorno["rr"][$irr]["dupliafectado"] . "'";
                            $libx = retornarRegistroMysqliApi($mysqli, 'mreg_est_inscripciones', $condx);
                            if ($libx && !empty($libx)) {
                                $retorno["rr"][$irr]["tipodocafectado"] = $libx["tipodocumento"];
                                $retorno["rr"][$irr]["numdocafectado"] = $libx["numerodocumento"];
                                $retorno["rr"][$irr]["fecdocafectado"] = $libx["fechadocumento"];
                                $retorno["rr"][$irr]["fecharegistroafectado"] = $libx["fecharegistro"];
                                $retorno["rr"][$irr]["noticiarecurrida"] = $libx["noticia"];
                            }
                        }

                        if ($retorno["rr"][$irr]["libroafectado2"] != '' && $retorno["rr"][$irr]["registroafectado2"] != '' && $retorno["rr"][$irr]["dupliafectado2"] != '') {
                            $condx = "libro='" . $retorno["rr"][$irr]["libroafectado2"] . "' and registro='" . $retorno["rr"][$irr]["registroafectado2"] . "' and dupli='" . $retorno["rr"][$irr]["dupliafectado2"] . "'";
                            $libx = retornarRegistroMysqliApi($mysqli, 'mreg_est_inscripciones', $condx);
                            if ($libx && !empty($libx)) {
                                $retorno["rr"][$irr]["tipodocafectado2"] = $libx["tipodocumento"];
                                $retorno["rr"][$irr]["numdocafectado2"] = $libx["numerodocumento"];
                                $retorno["rr"][$irr]["fecdocafectado2"] = $libx["fechadocumento"];
                                $retorno["rr"][$irr]["fecharegistroafectado2"] = $libx["fecharegistro"];
                                $retorno["rr"][$irr]["noticiarecurrida2"] = $libx["noticia"];
                            }
                        }

                        if ($retorno["rr"][$irr]["libroafectado3"] != '' && $retorno["rr"][$irr]["registroafectado3"] != '' && $retorno["rr"][$irr]["dupliafectado3"] != '') {
                            $condx = "libro='" . $retorno["rr"][$irr]["libroafectado3"] . "' and registro='" . $retorno["rr"][$irr]["registroafectado3"] . "' and dupli='" . $retorno["rr"][$irr]["dupliafectado3"] . "'";
                            $libx = retornarRegistroMysqliApi($mysqli, 'mreg_est_inscripciones', $condx);
                            if ($libx && !empty($libx)) {
                                $retorno["rr"][$irr]["tipodocafectado3"] = $libx["tipodocumento"];
                                $retorno["rr"][$irr]["numdocafectado3"] = $libx["numerodocumento"];
                                $retorno["rr"][$irr]["fecdocafectado3"] = $libx["fechadocumento"];
                                $retorno["rr"][$irr]["fecharegistroafectado3"] = $libx["fecharegistro"];
                                $retorno["rr"][$irr]["noticiarecurrida3"] = $libx["noticia"];
                            }
                        }

                        if ($retorno["rr"][$irr]["libroafectado4"] != '' && $retorno["rr"][$irr]["registroafectado4"] != '' && $retorno["rr"][$irr]["dupliafectado4"] != '') {
                            $condx = "libro='" . $retorno["rr"][$irr]["libroafectado4"] . "' and registro='" . $retorno["rr"][$irr]["registroafectado4"] . "' and dupli='" . $retorno["rr"][$irr]["dupliafectado4"] . "'";
                            $libx = retornarRegistroMysqliApi($mysqli, 'mreg_est_inscripciones', $condx);
                            if ($libx && !empty($libx)) {
                                $retorno["rr"][$irr]["tipodocafectado4"] = $libx["tipodocumento"];
                                $retorno["rr"][$irr]["numdocafectado4"] = $libx["numerodocumento"];
                                $retorno["rr"][$irr]["fecdocafectado4"] = $libx["fechadocumento"];
                                $retorno["rr"][$irr]["fecharegistroafectado4"] = $libx["fecharegistro"];
                                $retorno["rr"][$irr]["noticiarecurrida4"] = $libx["noticia"];
                            }
                        }
                    }
                }
            }
        }
        if ($genlog == 'si') {
            \logApi::general2($nameLog, $retorno["matricula"], 'Leyo de códigos de barras pendientes');
        }

        // \logApi::general2($nameLog, $retorno["matricula"], 'Salió de codbarras pendientes : ' . date("His"));
        // Relación de inscripciones
        // \logApi::general2($nameLog, $retorno["matricula"], 'Entró a inscripciones : ' . date("His"));
        $retorno["inscripciones"] = array();
        $retorno["inscripcioneslibros"] = array();
        $ix = 0;
        $ixl = 0;

        //
        $enliquidacion = 'no';
        $enreestructuracion = 'no';
        $enreorganizacion = 'no';
        $enliquidacionjudicial = 'no';
        $enliquidacionforsoza = 'no';
        $enrecuperacion = 'no';

        //
        $arrX = retornarRegistrosMysqliApi($mysqli, 'mreg_est_inscripciones', "matricula='" . ltrim(trim($retorno["matricula"]), "0") . "'", "fecharegistro,horaregistro,libro,registro,dupli");
        if ($arrX && !empty($arrX)) {
            foreach ($arrX as $x) {
                $ix++;
                // 2017-11-08: JINT: Se incluye control de revocación solo a partir del 2017
                // dado que en SIREP este no está bien manejado y pone problemas con
                // inscripciones antiguas
                if ($x["fecharegistro"] < '20170101' && $x["ctrrevoca"] == '1') {
                    $x["ctrrevoca"] = '0';
                }

                // if ($x["ctrrevoca"] != '1') {
                $retorno["inscripciones"][$ix] = array();
                $retorno["inscripciones"][$ix]["lib"] = $x["libro"];
                $retorno["inscripciones"][$ix]["nreg"] = $x["registro"];
                $retorno["inscripciones"][$ix]["dupli"] = $x["dupli"];
                $retorno["inscripciones"][$ix]["freg"] = $x["fecharegistro"];
                $retorno["inscripciones"][$ix]["hreg"] = $x["horaregistro"];
                $retorno["inscripciones"][$ix]["frad"] = $x["fecharadicacion"];
                $retorno["inscripciones"][$ix]["cb"] = $x["idradicacion"];
                $retorno["inscripciones"][$ix]["rec"] = $x["recibo"];
                $retorno["inscripciones"][$ix]["nope"] = $x["numerooperacion"];
                $retorno["inscripciones"][$ix]["ope"] = $x["operador"];
                $retorno["inscripciones"][$ix]["acto"] = $x["acto"];
                $retorno["inscripciones"][$ix]["nacto"] = retornarRegistroMysqliAPi($mysqli, 'mreg_actos', "idlibro='" . $x["libro"] . "' and idacto='" . $x["acto"] . "'", "nombre");
                $retorno["inscripciones"][$ix]["idclase"] = $x["tipoidentificacion"];
                $retorno["inscripciones"][$ix]["numid"] = $x["identificacion"];
                $retorno["inscripciones"][$ix]["nombre"] = $x["nombre"];
                $retorno["inscripciones"][$ix]["ndocext"] = $x["numdocextenso"];
                $retorno["inscripciones"][$ix]["ndoc"] = $x["numerodocumento"];
                $retorno["inscripciones"][$ix]["tdoc"] = $x["tipodocumento"];
                $retorno["inscripciones"][$ix]["fdoc"] = $x["fechadocumento"];
                $retorno["inscripciones"][$ix]["idoridoc"] = $x["idorigendoc"];
                $retorno["inscripciones"][$ix]["txoridoc"] = $x["origendocumento"];
                $retorno["inscripciones"][$ix]["idmunidoc"] = $x["municipiodocumento"];
                $retorno["inscripciones"][$ix]["idpaidoc"] = $x["paisdocumento"];
                $retorno["inscripciones"][$ix]["tipolibro"] = $x["tipolibro"];
                $retorno["inscripciones"][$ix]["idlibvii"] = $x["idcodlibro"];
                $retorno["inscripciones"][$ix]["codlibcom"] = $x["codigolibro"];
                $retorno["inscripciones"][$ix]["deslib"] = $x["descripcionlibro"];
                $retorno["inscripciones"][$ix]["paginainicial"] = $x["paginainicial"];
                $retorno["inscripciones"][$ix]["numhojas"] = $x["numeropaginas"];
                $retorno["inscripciones"][$ix]["not"] = $x["noticia"];
                $notx1 = retornarRegistroMysqliApi($mysqli, 'mreg_est_inscripciones_campos', "libro='" . $x["libro"] . "' and registro='" . $x["registro"] . "' and dupli='" . $x["dupli"] . "' and campo='noticiabase64'", "contenido");
                if ($notx1 != '') {
                    $retorno["inscripciones"][$ix]["not"] = base64_decode($notx1);
                }
                $retorno["inscripciones"][$ix]["aclaratoria"] = '';
                $retorno["inscripciones"][$ix]["txtapoderados"] = '';
                $retorno["inscripciones"][$ix]["txtpoder"] = '';
                if (isset($x["aclaratoria"])) {
                    $retorno["inscripciones"][$ix]["aclaratoria"] = $x["aclaratoria"];
                }
                if (isset($x["txtapoderados"])) {
                    $retorno["inscripciones"][$ix]["txtapoderados"] = $x["txtapoderados"];
                }
                if (isset($x["txtpoder"])) {
                    $retorno["inscripciones"][$ix]["txtpoder"] = $x["txtpoder"];
                }

                $retorno["inscripciones"][$ix]["not2"] = '';
                $retorno["inscripciones"][$ix]["not3"] = '';
                $retorno["inscripciones"][$ix]["crecu"] = $x["ctrrecurso"];
                $retorno["inscripciones"][$ix]["cimg"] = '';
                $retorno["inscripciones"][$ix]["cver"] = '';
                $retorno["inscripciones"][$ix]["crot"] = $x["ctrrotulo"];
                $retorno["inscripciones"][$ix]["crev"] = $x["ctrrevoca"];
                $retorno["inscripciones"][$ix]["regrev"] = $x["registrorevocacion"];
                $retorno["inscripciones"][$ix]["cnat"] = '';
                $retorno["inscripciones"][$ix]["fir"] = $x["firma"];
                $retorno["inscripciones"][$ix]["cfir"] = $x["clavefirmado"];
                $retorno["inscripciones"][$ix]["uins"] = $x["usuarioinscribe"];
                $retorno["inscripciones"][$ix]["ufir"] = $x["usuariofirma"];
                $retorno["inscripciones"][$ix]["tnotemail"] = $x["timestampnotificacionemail"];
                $retorno["inscripciones"][$ix]["tnotsms"] = $x["timestampnotificacionsms"];
                $retorno["inscripciones"][$ix]["tnotemail"] = $x["idnotificacionemail"];
                $retorno["inscripciones"][$ix]["tnotsms"] = $x["idnotificacionsms"];
                $retorno["inscripciones"][$ix]["ipubrue"] = $x["idpublicacionrue"];
                $retorno["inscripciones"][$ix]["fpubrue"] = $x["fecpublicacionrue"];
                $retorno["inscripciones"][$ix]["flim"] = $x["fechalimite"];

                $retorno["inscripciones"][$ix]["camant"] = $x["camaraanterior"];
                $retorno["inscripciones"][$ix]["libant"] = $x["libroanterior"];
                $retorno["inscripciones"][$ix]["regant"] = $x["registroanterior"];
                $retorno["inscripciones"][$ix]["fecant"] = $x["fecharegistroanterior"];

                $retorno["inscripciones"][$ix]["camant2"] = $x["camaraanterior2"];
                $retorno["inscripciones"][$ix]["libant2"] = $x["libroanterior2"];
                $retorno["inscripciones"][$ix]["regant2"] = $x["registroanterior2"];
                $retorno["inscripciones"][$ix]["fecant2"] = $x["fecharegistroanterior2"];

                $retorno["inscripciones"][$ix]["camant3"] = $x["camaraanterior3"];
                $retorno["inscripciones"][$ix]["libant3"] = $x["libroanterior3"];
                $retorno["inscripciones"][$ix]["regant3"] = $x["registroanterior3"];
                $retorno["inscripciones"][$ix]["fecant3"] = $x["fecharegistroanterior3"];

                $retorno["inscripciones"][$ix]["camant4"] = $x["camaraanterior4"];
                $retorno["inscripciones"][$ix]["libant4"] = $x["libroanterior4"];
                $retorno["inscripciones"][$ix]["regant4"] = $x["registroanterior4"];
                $retorno["inscripciones"][$ix]["fecant4"] = $x["fecharegistroanterior4"];

                $retorno["inscripciones"][$ix]["camant5"] = $x["camaraanterior5"];
                $retorno["inscripciones"][$ix]["libant5"] = $x["libroanterior5"];
                $retorno["inscripciones"][$ix]["regant5"] = $x["registroanterior5"];
                $retorno["inscripciones"][$ix]["fecant5"] = $x["fecharegistroanterior5"];

                $retorno["inscripciones"][$ix]["tomo72"] = '';
                $retorno["inscripciones"][$ix]["folio72"] = '';
                $retorno["inscripciones"][$ix]["registro72"] = '';

                $retorno["inscripciones"][$ix]["asa"] = $x["actosistemaanterior"];

                if (isset($x["tomo72"])) {
                    $retorno["inscripciones"][$ix]["tomo72"] = $x["tomo72"];
                    $retorno["inscripciones"][$ix]["folio72"] = $x["folio72"];
                    $retorno["inscripciones"][$ix]["registro72"] = $x["registro72"];
                }

                // ************************************************************* //
                // Inscripciones campos
                // ************************************************************* //
                $retorno["inscripciones"][$ix]["anadirrazonsocial"] = retornarRegistroMysqliApi($mysqli, 'mreg_est_inscripciones_campos', "libro='" . $x["libro"] . "' and registro='" . $x["registro"] . "' and dupli='" . $x["dupli"] . "' and campo='anadirrazonsocial'", "contenido");
                $retorno["inscripciones"][$ix]["extinciondominio"] = retornarRegistroMysqliApi($mysqli, 'mreg_est_inscripciones_campos', "libro='" . $x["libro"] . "' and registro='" . $x["registro"] . "' and dupli='" . $x["dupli"] . "' and campo='extinciondominio'", "contenido");
                $retorno["inscripciones"][$ix]["extinciondominiocondicion"] = retornarRegistroMysqliApi($mysqli, 'mreg_est_inscripciones_campos', "libro='" . $x["libro"] . "' and registro='" . $x["registro"] . "' and dupli='" . $x["dupli"] . "' and campo='extinciondominiocondicion'", "contenido");

                //
                $ind = $x["libro"] . '-' . $x["acto"];

                $retorno["inscripciones"][$ix]["est"] = $x["estado"];

                if ($retorno["inscripciones"][$ix]["not"] == '') {
                    if (
                            $_SESSION["generales"]["codigoempresa"] == '06' ||
                            $_SESSION["generales"]["codigoempresa"] == '07' ||
                            $_SESSION["generales"]["codigoempresa"] == '17' ||
                            $_SESSION["generales"]["codigoempresa"] == '24' ||
                            $_SESSION["generales"]["codigoempresa"] == '26' ||
                            $_SESSION["generales"]["codigoempresa"] == '36' ||
                            $_SESSION["generales"]["codigoempresa"] == '37' ||
                            $_SESSION["generales"]["codigoempresa"] == '43' ||
                            $_SESSION["generales"]["codigoempresa"] == '46'
                    ) {
                        $retorno["inscripciones"][$ix]["not"] = \funcionesGenerales::construirNoticiaSurOccidente($mysqli, $x);
                    }
                    if ($retorno["inscripciones"][$ix]["not"] == '') {
                        if ($x["acto"] != '') {
                            if (isset($_SESSION["maestroactos"][$ind])) {
                                $retorno["inscripciones"][$ix]["not"] = $_SESSION["maestroactos"][$ind]["nombre"];
                            } else {
                                $retorno["inscripciones"][$ix]["not"] = '';
                            }
                        }
                    }
                }

                //
                $retorno["inscripciones"][$ix]["nombreacto"] = '';
                $retorno["inscripciones"][$ix]["grupoacto"] = '';
                $retorno["inscripciones"][$ix]["esreforma"] = '';
                $retorno["inscripciones"][$ix]["esreformaespecial"] = '';
                $retorno["inscripciones"][$ix]["anotacionalcapital"] = '';
                $retorno["inscripciones"][$ix]["controldisolucion"] = '';
                $retorno["inscripciones"][$ix]["textoenliquidacion"] = '';
                $retorno["inscripciones"][$ix]["actosistemaanterior"] = $x["actosistemaanterior"];
                $retorno["inscripciones"][$ix]["vinculoafectado"] = $x["vinculoafectado"];
                $retorno["inscripciones"][$ix]["tipoidentificacionafectada"] = $x["tipoidentificacionafectada"];
                $retorno["inscripciones"][$ix]["identificacionafectada"] = $x["identificacionafectada"];
                $retorno["inscripciones"][$ix]["fechalimite"] = $x["fechalimite"];
                if (trim(ltrim((string) $x["idradicacion"], "0")) != '') {
                    $retorno["inscripciones"][$ix]["idradicacion"] = $x["idradicacion"];
                } else {
                    $cbx = retornarRegistrosMysqliApi($mysqli, 'mreg_est_codigosbarras_libros', "libro='" . $x["libro"] . "' and registro='" . $x["registro"] . "'", "id");
                    if ($cbx && !empty($cbx)) {
                        foreach ($cbx as $cbx1) {
                            $retorno["inscripciones"][$ix]["idradicacion"] = $cbx1["codigobarras"];
                        }
                    }
                    unset($cbx);
                }

                //
                if ($x["tipodocumento"] == '03' && $x["acto"] == '0510' && strpos($retorno["inscripciones"][$ix]["not"], "DEPURACION")) {
                    $retorno["inscripciones"][$ix]["tdoc"] = '38';
                }

                //
                if ($x["tipodocumento"] == '03' && ($x["acto"] == '0530' || $x["acto"] == '0540') && strpos($retorno["inscripciones"][$ix]["not"], "DEPURACION")) {
                    $retorno["inscripciones"][$ix]["tdoc"] = '38';
                }


                if (isset($_SESSION["maestroactos"][$ind])) {
                    $retorno["inscripciones"][$ix]["nombreacto"] = $_SESSION["maestroactos"][$ind]["nombre"];
                    $retorno["inscripciones"][$ix]["grupoacto"] = $_SESSION["maestroactos"][$ind]["idgrupoacto"];
                    $retorno["inscripciones"][$ix]["esreforma"] = $_SESSION["maestroactos"][$ind]["controlreforma"];
                    $retorno["inscripciones"][$ix]["esreformaespecial"] = $_SESSION["maestroactos"][$ind]["controlreformaespecial"];
                    $retorno["inscripciones"][$ix]["anotacionalcapital"] = $_SESSION["maestroactos"][$ind]["controlanotacionalcapital"];
                    $retorno["inscripciones"][$ix]["controldisolucion"] = $_SESSION["maestroactos"][$ind]["controldisolucion"];
                    $retorno["inscripciones"][$ix]["textoenliquidacion"] = $_SESSION["maestroactos"][$ind]["textoenliquidacion"];

                    // Perdida de calidad de comerciante
                    if ($_SESSION["maestroactos"][$ind]["idgrupoacto"] == '071') {
                        $retorno["fechaperdidacalidadcomerciante"] = $x["fecharegistro"];
                        $retorno["perdidacalidadcomerciante"] = 'si';
                        if (ltrim(trim($x["fecharadicacion"]), "0") != '') {
                            $retorno["fechaperdidacalidadcomerciante"] = $x["fecharadicacion"];
                        } else {
                            $retorno["fechaperdidacalidadcomerciante"] = $x["fecharegistro"];
                        }
                    }

                    // Reactivación como comerciante 
                    if ($_SESSION["maestroactos"][$ind]["idgrupoacto"] == '073') {
                        // $retorno["fechaperdidacalidadcomerciante"] = '';
                        // $retorno["fechaperdidacalidadcomerciante"] = '';
                        $retorno["perdidacalidadcomerciante"] = 'no';
                        if (ltrim(trim($x["fecharadicacion"]), "0") != '') {
                            $retorno["fechareactivacioncalidadcomerciante"] = $x["fecharadicacion"];
                        } else {
                            $retorno["fechareactivacioncalidadcomerciante"] = $x["fecharegistro"];
                        }
                    }

                    // Disolucion
                    if (
                            ($_SESSION["maestroactos"][$ind]["idgrupoacto"] == '009') ||
                            ($_SESSION["maestroactos"][$ind]["controldisolucion"] == 'S') ||
                            ($_SESSION["maestroactos"][$ind]["textoenliquidacion"] == 'S') ||
                            ($_SESSION["maestroactos"][$ind]["textoenliquidacion"] == 'L')
                    ) {
                        $retorno["reactivadaacto511"] = '';
                        $retorno["fechaacto511"] = '';
                        $retorno["fechadisolucion"] = $x["fecharegistro"];
                        $retorno["disueltaporacto510"] = 'si';
                        if ($x["fecharegistroanterior"] != '') {
                            $retorno["fechaacto510"] = $x["fecharegistroanterior"];
                        } else {
                            if (ltrim(trim($x["fecharadicacion"]), "0") != '') {
                                $retorno["fechaacto510"] = $x["fecharadicacion"];
                            } else {
                                $retorno["fechaacto510"] = $x["fecharegistro"];
                            }
                        }
                        $retorno["fechadisolucioncontrolbeneficios1756"] = $retorno["fechaacto510"];
                    }

                    // Reactivacion 
                    if (
                            ($_SESSION["maestroactos"][$ind]["idgrupoacto"] == '011') ||
                            ($_SESSION["maestroactos"][$ind]["controldisolucion"] == 'R') ||
                            ($_SESSION["maestroactos"][$ind]["textoenliquidacion"] == 'F') ||
                            ($_SESSION["maestroactos"][$ind]["textoenliquidacion"] == 'Q')
                    ) {
                        $retorno["fechadisolucion"] = '';
                        $retorno["disueltaporacto510"] = '';
                        $retorno["fechaacto510"] = '';
                        $retorno["reactivadaacto511"] = 'si';
                        $retorno["fechaacto511"] = $x["fecharegistro"];
                        $retorno["fechareactivacioncontrolbeneficios1756"] = $retorno["fechaacto511"];
                    }

                    // Constitucion      
                    if ($_SESSION["maestroactos"][$ind]["idgrupoacto"] == '005') {
                        $retorno["fechaconstitucion"] = $x["fecharegistro"];
                        if ($x["fecharegistroanterior"] != '') {
                            $retorno["fechaconstitucion"] = $x["fecharegistroanterior"];
                        }
                    }

                    // Cancelacion            
                    if ($retorno["estadomatricula"] == 'MC' || $retorno["estadomatricula"] == 'MF' || $retorno["estadomatricula"] == 'MG') {
                        if ($_SESSION["maestroactos"][$ind]["idgrupoacto"] == '002') {
                            $retorno["fechacancelacion"] = $x["fecharegistro"];
                        }
                    }

                    // liquidacion            
                    if ($_SESSION["maestroactos"][$ind]["idgrupoacto"] == '010') {
                        $retorno["fechaliquidacion"] = $x["fecharegistro"];
                    }

                    //
                    if ($_SESSION["maestroactos"][$ind]["textoenliquidacion"] == 'S') {
                        if (!isset($retorno["inscripciones"][$ix]["anadirrazonsocial"]) || $retorno["inscripciones"][$ix]["anadirrazonsocial"] != 'NO') {
                            $enliquidacion = 'si';
                            // $enreestructuracion = 'no';
                            // $enreorganizacion = 'no';
                            $enliquidacionjudicial = 'no';
                            $enliquidacionforsoza = 'no';
                            // $enrecuperacion = 'no';
                        }
                    }
                    if ($_SESSION["maestroactos"][$ind]["textoenliquidacion"] == 'L') {
                        if (!isset($retorno["inscripciones"][$ix]["anadirrazonsocial"]) || $retorno["inscripciones"][$ix]["anadirrazonsocial"] != 'NO') {
                            $enliquidacionjudicial = 'si';
                            $enliquidacion = 'no';
                            // $enreorganizacion = 'no';
                            // $enreestructuracion = 'no';
                            $enliquidacionforsoza = 'no';
                            // $enrecuperacion = 'no';
                        }
                    }
                    if ($_SESSION["maestroactos"][$ind]["textoenliquidacion"] == 'F') {
                        if (!isset($retorno["inscripciones"][$ix]["anadirrazonsocial"]) || $retorno["inscripciones"][$ix]["anadirrazonsocial"] != 'NO') {
                            $enliquidacionforsoza = 'si';
                            $enliquidacion = 'no';
                            // $enreorganizacion = 'no';
                            $enliquidacionjudicial = 'no';
                            // $enreestructuracion = 'no';
                            // $enrecuperacion = 'no';
                        }
                    }

                    if ($_SESSION["maestroactos"][$ind]["textoenliquidacion"] == 'Q') {
                        if (!isset($retorno["inscripciones"][$ix]["anadirrazonsocial"]) || $retorno["inscripciones"][$ix]["anadirrazonsocial"] != 'NO') {
                            $enliquidacion = 'no';
                            $enliquidacionjudicial = 'no';
                            // $enreestructuracion = 'no';
                            // $enreorganizacion = 'no';
                            $enliquidacionforsoza = 'no';
                            // $enrecuperacion = 'no';
                        }
                    }

                    if ($_SESSION["maestroactos"][$ind]["textoenreestructuracion"] == 'S') {
                        if (!isset($retorno["inscripciones"][$ix]["anadirrazonsocial"]) || $retorno["inscripciones"][$ix]["anadirrazonsocial"] != 'NO') {
                            // $enliquidacion = 'no';
                            // $enliquidacionjudicial = 'no';
                            $enreestructuracion = 'si';
                            $enreorganizacion = 'no';
                            $enrecuperacion = 'no';
                        }
                    }
                    if ($_SESSION["maestroactos"][$ind]["textoenreestructuracion"] == 'L') {
                        if (!isset($retorno["inscripciones"][$ix]["anadirrazonsocial"]) || $retorno["inscripciones"][$ix]["anadirrazonsocial"] != 'NO') {
                            $enliquidacion = 'no';
                            $enliquidacionjudicial = 'si';
                            $enreestructuracion = 'no';
                            $enreorganizacion = 'no';
                            $enrecuperacion = 'no';
                        }
                    }
                    if ($_SESSION["maestroactos"][$ind]["textoenreestructuracion"] == 'R') {
                        if (!isset($retorno["inscripciones"][$ix]["anadirrazonsocial"]) || $retorno["inscripciones"][$ix]["anadirrazonsocial"] != 'NO') {
                            $enliquidacion = 'no';
                            $enliquidacionjudicial = 'no';
                            $enreestructuracion = 'no';
                            $enreorganizacion = 'si';
                            $enrecuperacion = 'no';
                        }
                    }
                    if ($_SESSION["maestroactos"][$ind]["textoenreestructuracion"] == 'E') {
                        if (!isset($retorno["inscripciones"][$ix]["anadirrazonsocial"]) || $retorno["inscripciones"][$ix]["anadirrazonsocial"] != 'NO') {
                            $enliquidacion = 'no';
                            $enliquidacionjudicial = 'no';
                            $enreestructuracion = 'no';
                            $enreorganizacion = 'no';
                            $enrecuperacion = 'si';
                        }
                    }

                    if ($_SESSION["maestroactos"][$ind]["textoenreestructuracion"] == 'Q') {
                        if (!isset($retorno["inscripciones"][$ix]["anadirrazonsocial"]) || $retorno["inscripciones"][$ix]["anadirrazonsocial"] != 'NO') {
                            $enliquidacion = 'no';
                            $enliquidacionjudicial = 'no';
                            $enreestructuracion = 'no';
                            $enreorganizacion = 'no';
                            $enrecuperacion = 'no';
                        }
                    }
                }

                // Libros de comercio
                $eslib = 'no';
                if ($x["libro"] == 'RM07' || $x["libro"] == 'RE52') {
                    if (ltrim(trim($x["acto"]), "0") == '') {
                        $eslib = 'si';
                    } else {
                        if (
                                $_SESSION["maestroactos"][$ind]["idgrupoacto"] == '004' ||
                                $_SESSION["maestroactos"][$ind]["idgrupoacto"] == '085'
                        ) {
                            $eslib = 'si';
                        }
                    }
                }

                //
                if ($x["libro"] == 'RM22' || $x["libro"] == 'RE53') {
                    if (trim($x["acto"]) == '0003' || trim($x["acto"]) == '0004') {
                        if (
                                $_SESSION["maestroactos"][$ind]["idgrupoacto"] == '004' ||
                                $_SESSION["maestroactos"][$ind]["idgrupoacto"] == '085'
                        ) {
                            $eslib = 'si';
                        }
                    }
                }

                //
                if ($eslib == 'si') {
                    $ixl++;
                    $retorno["inscripcioneslibros"][$ixl] = array();
                    $retorno["inscripcioneslibros"][$ixl]["lib"] = $x["libro"];
                    $retorno["inscripcioneslibros"][$ixl]["nreg"] = $x["registro"];
                    $retorno["inscripcioneslibros"][$ixl]["dupli"] = $x["dupli"];
                    $retorno["inscripcioneslibros"][$ixl]["acto"] = $x["acto"];
                    $retorno["inscripcioneslibros"][$ixl]["freg"] = $x["fecharegistro"];
                    $retorno["inscripcioneslibros"][$ixl]["hreg"] = $x["horaregistro"];
                    $retorno["inscripcioneslibros"][$ixl]["tipolibro"] = $x["tipolibro"];
                    $retorno["inscripcioneslibros"][$ixl]["idlibvii"] = $x["idcodlibro"];
                    $retorno["inscripcioneslibros"][$ixl]["codlibcom"] = $x["codigolibro"];
                    $retorno["inscripcioneslibros"][$ixl]["deslib"] = $x["descripcionlibro"];
                    $retorno["inscripcioneslibros"][$ixl]["paginainicial"] = $x["paginainicial"];
                    $retorno["inscripcioneslibros"][$ixl]["numhojas"] = $x["numeropaginas"];
                    $retorno["inscripcioneslibros"][$ixl]["not"] = $x["noticia"];
                    $retorno["inscripcioneslibros"][$ixl]["camant"] = $x["camaraanterior"];
                    $retorno["inscripcioneslibros"][$ixl]["libant"] = $x["libroanterior"];
                    $retorno["inscripcioneslibros"][$ixl]["regant"] = $x["registroanterior"];
                    $retorno["inscripcioneslibros"][$ixl]["fecant"] = $x["fecharegistroanterior"];

                    $retorno["inscripcioneslibros"][$ixl]["camant2"] = $x["camaraanterior2"];
                    $retorno["inscripcioneslibros"][$ixl]["libant2"] = $x["libroanterior2"];
                    $retorno["inscripcioneslibros"][$ixl]["regant2"] = $x["registroanterior2"];
                    $retorno["inscripcioneslibros"][$ixl]["fecant2"] = $x["fecharegistroanterior2"];

                    $retorno["inscripcioneslibros"][$ixl]["camant3"] = $x["camaraanterior3"];
                    $retorno["inscripcioneslibros"][$ixl]["libant3"] = $x["libroanterior3"];
                    $retorno["inscripcioneslibros"][$ixl]["regant3"] = $x["registroanterior3"];
                    $retorno["inscripcioneslibros"][$ixl]["fecant3"] = $x["fecharegistroanterior3"];

                    $retorno["inscripcioneslibros"][$ixl]["camant4"] = $x["camaraanterior4"];
                    $retorno["inscripcioneslibros"][$ixl]["libant4"] = $x["libroanterior4"];
                    $retorno["inscripcioneslibros"][$ixl]["regant4"] = $x["registroanterior4"];
                    $retorno["inscripcioneslibros"][$ixl]["fecant4"] = $x["fecharegistroanterior4"];

                    $retorno["inscripcioneslibros"][$ixl]["camant5"] = $x["camaraanterior5"];
                    $retorno["inscripcioneslibros"][$ixl]["libant5"] = $x["libroanterior5"];
                    $retorno["inscripcioneslibros"][$ixl]["regant5"] = $x["registroanterior5"];
                    $retorno["inscripcioneslibros"][$ixl]["fecant5"] = $x["fecharegistroanterior5"];

                    $retorno["inscripcioneslibros"][$ixl]["est"] = $x["estado"];
                    if ($retorno["inscripcioneslibros"][$ixl]["not"] == '') {
                        $retorno["inscripcioneslibros"][$ixl]["not"] = $_SESSION["maestroactos"][$x["libro"] . '-' . '0003']["nombre"];
                    }
                }

                if ($retorno["inscripciones"][$ix]["grupoacto"] == '005') {
                    $retorno["datconst_fecdoc"] = $x["fechadocumento"];
                    $retorno["datconst_tipdoc"] = $x["tipodocumento"];
                    $retorno["datconst_numdoc"] = $x["numerodocumento"];
                    $retorno["datconst_oridoc"] = $x["origendocumento"];
                    $retorno["datconst_mundoc"] = $x["municipiodocumento"];
                }
            }
        }

        //
        if ($genlog == 'si') {
            \logApi::general2($nameLog, $retorno["matricula"], 'Leyo inscripciones en libros');
        }

        // 2017-08-21: JINT: Pone automáticamente la palabra EN LIQUIDACION y EN RESTRUCTURACION
        // Bien sea porque se vencieron los términos de duracion (Vigencia) o porque
        // encontro algún acto que indique que se debe colocar o no dicha palabra
        // Aplica solo para personas jurídica sprincipales
        // 2017-11-18: si debe adicionar palabras a la razón social, revisa primero
        // si existe contenido en el campo "complentorazonsocial"

        if ($retorno["organizacion"] == '01') {
            if ($retorno["ape1"] != '' && $retorno["nom1"] != '') {
                $retorno["nombrerues"] = $retorno["ape1"];
                if (trim((string) $retorno["ape2"]) != '') {
                    $retorno["nombrerues"] .= ' ' . $retorno["ape2"];
                }
                if (trim((string) $retorno["nom1"]) != '') {
                    $retorno["nombrerues"] .= ' ' . $retorno["nom1"];
                }
                if (trim((string) $retorno["nom2"]) != '') {
                    $retorno["nombrerues"] .= ' ' . $retorno["nom2"];
                }

                $retorno["nombre"] = $retorno["nom1"];
                if (trim((string) $retorno["nom2"]) != '') {
                    $retorno["nombre"] .= ' ' . $retorno["nom2"];
                }
                if (trim((string) $retorno["ape1"]) != '') {
                    $retorno["nombre"] .= ' ' . $retorno["ape1"];
                }
                if (trim((string) $retorno["ape2"]) != '') {
                    $retorno["nombre"] .= ' ' . $retorno["ape2"];
                }
            }
        }

        //
        if ($retorno["estadomatricula"] != 'MC' && $retorno["estadomatricula"] != 'IC') {
            if ($retorno["organizacion"] == '01' || ($retorno["organizacion"] > '02' && $retorno["categoria"] == '1')) {
                $ya = 'no';
                if (
                        $enliquidacion == 'si' ||
                        $enreestructuracion == 'si' ||
                        $enreorganizacion == 'si' ||
                        $enliquidacionjudicial == 'si' ||
                        $enliquidacionforsoza == 'si' ||
                        $enrecuperacion == 'si'
                ) {
                    if ($retorno["complementorazonsocial"] != '') {
                        $retorno["nombre"] .= ' ' . $retorno["complementorazonsocial"];
                        $retorno["nombrerues"] .= ' ' . $retorno["complementorazonsocial"];
                        $ya = 'si';
                    }
                }
                if ($ya == 'no') {
                    if ($enliquidacion == 'si') {
                        $retorno["nombre"] .= ' EN LIQUIDACION';
                        $retorno["nombrerues"] .= ' EN LIQUIDACION';
                        $ya = 'si';
                    }
                    if ($enreestructuracion == 'si') {
                        $retorno["nombre"] .= ' EN REESTRUCTURACION';
                        $retorno["nombrerues"] .= ' EN REESTRUCTURACION';
                        $ya = 'si';
                    }
                    if ($enreorganizacion == 'si') {
                        $retorno["nombre"] .= ' EN REORGANIZACION';
                        $retorno["nombrerues"] .= ' EN REORGANIZACION';
                        $ya = 'si';
                    }
                    if ($enliquidacionjudicial == 'si') {
                        $retorno["nombre"] .= ' EN LIQUIDACION JUDICIAL';
                        $retorno["nombrerues"] .= ' EN LIQUIDACION JUDICIAL';
                        $ya = 'si';
                    }
                    if ($enliquidacionforsoza == 'si') {
                        $retorno["nombre"] .= ' EN LIQUIDACION FORZOSA';
                        $retorno["nombrerues"] .= ' EN LIQUIDACION FORZOSA';
                        $ya = 'si';
                    }
                    if ($enrecuperacion == 'si') {
                        $retorno["nombre"] .= ' EN RECUPERACION EMPRESARIAL';
                        $retorno["nombrerues"] .= ' EN RECUPERACION EMPRESARIAL';
                        $ya = 'si';
                    }
                }
                if ($ya == 'no') {
                    if (trim($retorno["fechavencimiento"]) != '' && $retorno["fechavencimiento"] != '99999999') {
                        if (trim($retorno["fechavencimiento"]) < date("Ymd")) {
                            $retorno["nombre"] .= ' EN LIQUIDACION';
                            $retorno["nombrerues"] .= ' EN LIQUIDACION';
                            $ya = 'si';
                        }
                    }
                }
            }
            if ($retorno["organizacion"] > '02' && ($retorno["categoria"] == '2' || $retorno["categoria"] == '3')) {
                if ($retorno["complementorazonsocial"] != '') {
                    $retorno["nombre"] .= ' ' . $retorno["complementorazonsocial"];
                    $retorno["nombrerues"] .= ' ' . $retorno["complementorazonsocial"];
                    $ya = 'si';
                }
            }
        }

        // Relación de nombres anteriores
        // \logApi::general2($nameLog, $retorno["matricula"], 'Entró a nombres anteriores : ' . date("His"));
        $retorno["nomant"] = array();

        $arrX = retornarRegistrosMysqliApi($mysqli, 'mreg_est_nombresanteriores', "matricula='" . $retorno["matricula"] . "'", "secuencia");
        $ix = 0;
        if ($arrX && !empty($arrX)) {
            foreach ($arrX as $x) {
                if (existeTablaMysqliApi($mysqli, 'mreg_est_nombresanteriores_campos')) {
                    $nomb64 = retornarRegistroMysqliApi($mysqli, 'mreg_est_nombresanteriores_campos', "id_nombresanteriores=" . $x["id"] . " and campo='nombrebase64'", "contenido");
                    if ($nomb64 != '') {
                        $x["nombre"] = base64_decode($nomb64);
                    }
                }
                $ix++;
                $retorno["nomant"][$ix] = array();
                $retorno["nomant"][$ix]["id"] = $x["id"];
                $retorno["nomant"][$ix]["sec"] = $ix;
                $retorno["nomant"][$ix]["lib"] = $x["libro"];
                $retorno["nomant"][$ix]["nreg"] = $x["registro"];
                $retorno["nomant"][$ix]["dup"] = $x["dupli"];
                $retorno["nomant"][$ix]["freg"] = $x["fechareg"];
                $retorno["nomant"][$ix]["nom"] = $x["nombre"];
                $retorno["nomant"][$ix]["ope"] = $x["operador"];
                $retorno["nomant"][$ix]["fcre"] = $x["fechacreacion"];
            }
        }
        if ($genlog == 'si') {
            \logApi::general2($nameLog, $retorno["matricula"], 'Leyo nombres anteriores');
        }

        // Relación de capitales
        $retorno["capitales"] = array();
        $arrX = retornarRegistrosMysqliApi($mysqli, 'mreg_est_capitales', "matricula='" . ltrim(trim($retorno["matricula"]), "0") . "'", "anodatos,fechadatos");
        $icap = 0;
        if ($arrX && !empty($arrX)) {
            foreach ($arrX as $hcap) {
                $icap++;
                $retorno["capitales"][$icap]["anodatos"] = $hcap["anodatos"];
                $retorno["capitales"][$icap]["fechadatos"] = $hcap["fechadatos"];
                $retorno["capitales"][$icap]["libro"] = $hcap["libro"];
                $retorno["capitales"][$icap]["registro"] = $hcap["registro"];
                $retorno["capitales"][$icap]["tipoeconomia"] = $hcap["tipoeconomia"];
                $retorno["capitales"][$icap]["pornaltot"] = $hcap["pornaltot"];
                $retorno["capitales"][$icap]["pornalpri"] = $hcap["pornalpri"];
                $retorno["capitales"][$icap]["pornalpub"] = $hcap["pornalpub"];
                $retorno["capitales"][$icap]["porexttot"] = $hcap["porexttot"];
                $retorno["capitales"][$icap]["porextpri"] = $hcap["porextpri"];
                $retorno["capitales"][$icap]["porextpub"] = $hcap["porextpub"];
                $retorno["capitales"][$icap]["apoact"] = $hcap["aporteactivos"];
                $retorno["capitales"][$icap]["apodin"] = $hcap["aportedinero"];
                $retorno["capitales"][$icap]["apolab"] = $hcap["aportelaboral"];
                $retorno["capitales"][$icap]["apolabadi"] = $hcap["aportelaboraladi"];
                $retorno["capitales"][$icap]["suscrito"] = $hcap["valorsuscrito"];
                $retorno["capitales"][$icap]["autorizado"] = $hcap["valorautorizado"];
                $retorno["capitales"][$icap]["pagado"] = $hcap["valorpagado"];
                $retorno["capitales"][$icap]["social"] = $hcap["valorsocial"];
                $retorno["capitales"][$icap]["asigsuc"] = $hcap["capsucursal"];
                $retorno["capitales"][$icap]["cuosuscrito"] = $hcap["cuotassuscrito"];
                $retorno["capitales"][$icap]["cuoautorizado"] = $hcap["cuotasautorizado"];
                $retorno["capitales"][$icap]["cuopagado"] = $hcap["cuotaspagado"];
                $retorno["capitales"][$icap]["cuosocial"] = $hcap["cuotassocial"];

                $retorno["capitales"][$icap]["nomsuscrito"] = $hcap["nominalsuscrito"];
                $retorno["capitales"][$icap]["nomautorizado"] = $hcap["nominalautorizado"];
                $retorno["capitales"][$icap]["nompagado"] = $hcap["nominalpagado"];
                $retorno["capitales"][$icap]["nomsocial"] = $hcap["nominalsocial"];

                $retorno["capitales"][$icap]["moneda"] = $hcap["moneda"];

                // información de porcentajes de capital
                $retorno["cap_apolab"] = doubleval($hcap["aportelaboral"]);
                $retorno["cap_apolabadi"] = doubleval($hcap["aportelaboraladi"]);
                $retorno["cap_apoact"] = doubleval($hcap["aporteactivos"]);
                $retorno["cap_apodin"] = doubleval($hcap["aportedinero"]);

                $retorno["fecdatoscap"] = $hcap["fechadatos"];
                $retorno["capsoc"] = $hcap["valorsocial"];
                $retorno["capaut"] = $hcap["valorautorizado"];
                $retorno["capsus"] = $hcap["valorsuscrito"];
                $retorno["cappag"] = $hcap["valorpagado"];
                $retorno["cuosoc"] = $hcap["cuotassocial"];
                $retorno["cuoaut"] = $hcap["cuotasautorizado"];
                $retorno["cuosus"] = $hcap["cuotassuscrito"];
                $retorno["cuopag"] = $hcap["cuotaspagado"];

                $retorno["nomsoc"] = $hcap["nominalsocial"];
                $retorno["nomaut"] = $hcap["nominalautorizado"];
                $retorno["nomsus"] = $hcap["nominalsuscrito"];
                $retorno["nompag"] = $hcap["nominalpagado"];

                $retorno["capsuc"] = $hcap["capsucursal"];
                $retorno["monedacap"] = $hcap["moneda"];
            }
        }
        if ($genlog == 'si') {
            \logApi::general2($nameLog, $retorno["matricula"], 'Leyo capitales');
        }

        // Relación de capitales
        $retorno["patrimoniosesadl"] = array();
        $arrX = retornarRegistrosMysqliApi($mysqli, 'mreg_est_patrimonios', "matricula='" . $retorno["matricula"] . "'", "anodatos,fechadatos");
        $icap = 0;
        if ($arrX && !empty($arrX)) {
            foreach ($arrX as $hcap) {
                $icap++;
                $retorno["patrimoniosesadl"][$icap]["anodatos"] = $hcap["anodatos"];
                $retorno["patrimoniosesadl"][$icap]["fechadatos"] = $hcap["fechadatos"];
                $retorno["patrimoniosesadl"][$icap]["patrimonio"] = $hcap["patrimonio"];
            }
        }
        if ($genlog == 'si') {
            \logApi::general2($nameLog, $retorno["matricula"], 'Leyo patrimonios esadl');
        }

        // Información financiera histórica
        $retorno["hf"] = array();
        $ix = 0;
        $arrX = retornarRegistrosMysqliApi($mysqli, 'mreg_est_financiera', "matricula='" . $retorno["matricula"] . "'", "anodatos,fechadatos");
        if ($arrX && !empty($arrX)) {
            foreach ($arrX as $regf) {
                $ix++;
                $retorno["hf"][$ix]["anodatos"] = $regf["anodatos"];
                $retorno["hf"][$ix]["fechadatos"] = $regf["fechadatos"];
                $retorno["hf"][$ix]["personal"] = $regf["personal"];
                $retorno["hf"][$ix]["personaltemp"] = $regf["pcttemp"];
                $retorno["hf"][$ix]["actvin"] = $regf["actvin"];
                $retorno["hf"][$ix]["actcte"] = $regf["actcte"];
                $retorno["hf"][$ix]["actnocte"] = $regf["actnocte"];
                $retorno["hf"][$ix]["actfij"] = $regf["actfij"];
                $retorno["hf"][$ix]["fijnet"] = $regf["fijnet"];
                $retorno["hf"][$ix]["actotr"] = $regf["actotr"];
                $retorno["hf"][$ix]["actval"] = $regf["actval"];
                $retorno["hf"][$ix]["acttot"] = $regf["acttot"];
                $retorno["hf"][$ix]["actsinaju"] = 0;
                $retorno["hf"][$ix]["invent"] = 0;
                $retorno["hf"][$ix]["pascte"] = $regf["pascte"];
                $retorno["hf"][$ix]["paslar"] = $regf["paslar"];
                $retorno["hf"][$ix]["pastot"] = $regf["pastot"];
                $retorno["hf"][$ix]["pattot"] = $regf["patnet"];
                $retorno["hf"][$ix]["paspat"] = $regf["paspat"];
                $retorno["hf"][$ix]["balsoc"] = $regf["balsoc"];
                $retorno["hf"][$ix]["ingope"] = $regf["ingope"];
                $retorno["hf"][$ix]["ingnoope"] = $regf["ingnoope"];
                $retorno["hf"][$ix]["gtoven"] = $regf["gtoven"];
                $retorno["hf"][$ix]["gtoadm"] = $regf["gasadm"];
                $retorno["hf"][$ix]["cosven"] = $regf["cosven"];
                $retorno["hf"][$ix]["depamo"] = 0;
                $retorno["hf"][$ix]["gasint"] = $regf["gasint"];
                $retorno["hf"][$ix]["gasimp"] = $regf["gasimp"];
                $retorno["hf"][$ix]["utiope"] = $regf["utiope"];
                $retorno["hf"][$ix]["utinet"] = $regf["utinet"];
            }
        }
        unset($arrX);
        if ($genlog == 'si') {
            \logApi::general2($nameLog, $retorno["matricula"], 'Leyo histórico financiera');
        }

        // ************************************************************************************* //
        // Calcula tamaño empresarial
        // ************************************************************************************* //
        if (($retorno["organizacion"] == '01' || ($retorno["organizacion"] > '02' && $retorno["categoria"] == '1')) &&
                $retorno["claseespesadl"] != '61' &&
                $retorno["claseespesadl"] != '62'
        ) {
            $tex = retornarRegistrosMysqliApi($mysqli, 'mreg_tamano_empresarial', "matricula='" . $retorno["matricula"] . "'", 'anodatos,fechadatos');
            if ($tex && !empty($tex)) {
                foreach ($tex as $tex1) {
                    $retorno["ciiutamanoempresarial"] = $tex1["ciiu"];
                    $retorno["ingresostamanoempresarial"] = $tex1["ingresos"];
                    $retorno["anodatostamanoempresarial"] = $tex1["anodatos"];
                    $retorno["fechadatostamanoempresarial"] = $tex1["fechadatos"];
                }
            }
            unset($tex);
            unset($tex1);
            if ($genlog == 'si') {
                \logApi::general2($nameLog, $retorno["matricula"], 'Leyo mreg_tamano_empresarial');
            }

            // Determina el tamaño empresarial
            if ($retorno["ciiutamanoempresarial"] == '') {
                $retorno["ciiutamanoempresarial"] = $retorno["ciius"][1];
            }
            if ($retorno["ingresostamanoempresarial"] == '') {
                $retorno["ingresostamanoempresarial"] = $retorno["ingope"];
            }
            if ($retorno["anodatostamanoempresarial"] == '') {
                $retorno["anodatostamanoempresarial"] = $retorno["ultanoren"];
            }
            if ($retorno["fechadatostamanoempresarial"] == '') {
                $retorno["fechadatostamanoempresarial"] = $retorno["anodatos"];
            }
            $anomatricula = 'no';
            if ($retorno["fecharenovacion"] == $retorno["fechamatricula"]) {
                $anomatricula = 'si';
            }

            $tamemp = \funcionesGenerales::calcularTamanoEmpresarial($mysqli, $retorno["matricula"], 'actual', '', 0, 0, '', 0);
            $retorno["tamanoempresarial957"] = $tamemp["textocompleto"];
            $retorno["tamanoempresarial957uvts"] = $tamemp["ingresosuvt"];
            $retorno["tamanoempresarial957uvbs"] = $tamemp["ingresosuvb"];
            $retorno["tamanoempresarial957codigo"] = $tamemp["codigo"];
            $retorno["tamanoempresarialingresos"] = $tamemp["ingresos"];
            $retorno["tamanoempresarialactivos"] = $tamemp["activos"];
            $retorno["tamanoempresarialciiu"] = $tamemp["ciiu"];
            $retorno["tamanoempresarialpersonal"] = $tamemp["personal"];
            $retorno["tamanoempresarialfechadatos"] = $tamemp["fechadatos"];
            $retorno["tamanoempresarialanodatos"] = $tamemp["anodatos"];
            $retorno["tamanoempresarialformacalculo"] = $tamemp["forma"];
            $retorno["tamanoempresarialvaloruvt"] = $tamemp["uvt"];
            $retorno["tamanoempresarialvaloruvb"] = $tamemp["uvb"];
        }


        // Carga códigos CAE
        $retorno["codigoscae"] = array();
        $arrY = retornarRegistrosMysqliApi($mysqli, 'mreg_anexoscae', "1=1", "codigocae");
        if ($arrY && !empty($arrY)) {
            foreach ($arrY as $y) {
                $retorno["codigoscae"][$y["codigocae"]] = retornarRegistroMysqliApi($mysqli, 'mreg_est_campostablas', "tabla='200' and registro='" . $retorno["matricula"] . "' and campo='" . $y["codigocae"] . "'", "contenido");
            }
        }

        // Carga información adicional
        $retorno["informacionadicional"] = array();
        $arrY = retornarRegistrosMysqliApi($mysqli, 'mreg_campos_adicionales_camara', "1=1", "orden");
        if ($arrY && !empty($arrY)) {
            foreach ($arrY as $y) {
                $retorno["informacionadicional"][$y["codigoadicional"]] = retornarRegistroMysqliApi($mysqli, 'mreg_est_campostablas', "tabla='200' and registro='" . $retorno["matricula"] . "' and campo='" . $y["codigoadicional"] . "'", "contenido");
            }
        }
        if ($genlog == 'si') {
            \logApi::general2($nameLog, $retorno["matricula"], 'Leyo información CAE');
        }

        // Certificas que se importaron del SIREP
        $retorno["crt"] = array();
        $arrTemC = retornarRegistrosMysqliApi($mysqli, 'mreg_est_certificas', "matricula='" . ltrim($retorno["matricula"], "0") . "'", "idcertifica,id");
        if ($arrTemC && !empty($arrTemC)) {
            foreach ($arrTemC as $tx) {
                if (trim($tx["texto"]) != '') {
                    if (trim($tx["idcertifica"]) != '') {
                        if (!isset($retorno["crt"][trim($tx["idcertifica"])])) {
                            $retorno["crt"][trim($tx["idcertifica"])] = '';
                        }
                        $txt1 = \funcionesGenerales::reemplazarAcutes(\funcionesGenerales::restaurarEspeciales(trim(str_replace("&rdquo;", '"', $tx["texto"]))));
                        $txt1 = str_replace("||", CHR(13) . CHR(10) . CHR(13) . CHR(10), $txt1);
                        $txt1 = str_replace("|", " ", $txt1);
                        $retorno["crt"][$tx["idcertifica"]] .= $txt1;
                    }
                }
            }
        }
        unset($arrTemC);
        if ($genlog == 'si') {
            \logApi::general2($nameLog, $retorno["matricula"], 'Leyo certificas SIREP');
        }

        // Certificas que se han grabado en el SII
        $retorno["crtsii"] = array();
        $arrTemC = retornarRegistrosMysqliApi($mysqli, 'mreg_certificas_sii', "registro='REGMER' and expediente='" . ltrim($retorno["matricula"], "0") . "'", "idcertifica,id");
        if ($arrTemC && !empty($arrTemC)) {
            foreach ($arrTemC as $tx) {
                if (trim($tx["contenido"]) != '') {
                    if (!isset($retorno["crtsii"][$tx["idcertifica"]])) {
                        $retorno["crtsii"][$tx["idcertifica"]] = '';
                    }
                    $txt1 = \funcionesGenerales::reemplazarAcutes(\funcionesGenerales::restaurarEspeciales(trim(str_replace("&rdquo;", '"', $tx["contenido"]))));
                    $txt1 = str_replace("||", CHR(13) . CHR(10) . CHR(13) . CHR(10), $txt1);
                    $txt1 = str_replace("|", " ", $txt1);
                    $retorno["crtsii"][$tx["idcertifica"]] .= $txt1;
                }
            }
        }
        unset($arrTemC);
        if ($genlog == 'si') {
            \logApi::general2($nameLog, $retorno["matricula"], 'Leyo certificas SII');
        }

        // Carga relación de embargos
        $retorno["embargos"] = 0;
        $retorno["ctrembargos"] = array();
        $i = 0;
        $arrTemE = retornarRegistrosMysqliApi($mysqli, 'mreg_est_embargos', "matricula='" . $retorno["matricula"] . "'", "fecinscripcion,libro,numreg");
        if ($arrTemE === false) {
            echo $_SESSION["generales"]["mensajeerror"];
        }
        if ($arrTemE && !empty($arrTemE)) {
            foreach ($arrTemE as $e) {
                $i++;
                $retorno["ctrembargos"][$i] = array();
                $retorno["ctrembargos"][$i]["id"] = $e["id"];
                $retorno["ctrembargos"][$i]["acto"] = $e["acto"];
                $retorno["ctrembargos"][$i]["idclase"] = $e["idclase"];
                $retorno["ctrembargos"][$i]["numid"] = $e["numid"];
                $retorno["ctrembargos"][$i]["nombre"] = stripslashes((string) $e["nombre"]);
                $retorno["ctrembargos"][$i]["idclasedemandante"] = $e["idclasedemandante"];
                $retorno["ctrembargos"][$i]["numiddemandante"] = $e["numiddemandante"];
                $retorno["ctrembargos"][$i]["nombredemandante"] = stripslashes((string) $e["nombredemandante"]);
                $retorno["ctrembargos"][$i]["tipdoc"] = $e["tipdoc"];
                $retorno["ctrembargos"][$i]["numdoc"] = $e["numdoc"];
                $retorno["ctrembargos"][$i]["fecdoc"] = $e["fecdoc"];
                $retorno["ctrembargos"][$i]["idorigen"] = $e["idorigen"];
                $retorno["ctrembargos"][$i]["txtorigen"] = stripslashes((string) $e["txtorigen"]);
                $retorno["ctrembargos"][$i]["fecrad"] = $e["fecradica"];
                $retorno["ctrembargos"][$i]["estado"] = $e["ctrestadoembargo"];
                if (strlen($e["libro"]) == 2) {
                    if (trim($e["libro"]) > '50' && trim($e["libro"]) < '60') {
                        $retorno["ctrembargos"][$i]["libro"] = 'RE' . $e["libro"];
                    } else {
                        $retorno["ctrembargos"][$i]["libro"] = 'RM' . $e["libro"];
                    }
                } else {
                    $retorno["ctrembargos"][$i]["libro"] = $e["libro"];
                }
                $retorno["ctrembargos"][$i]["numreg"] = $e["numreg"];
                $retorno["ctrembargos"][$i]["codbarras"] = $e["codbarras"];
                $retorno["ctrembargos"][$i]["noticia"] = stripslashes((string) $e["noticia"]);
                $retorno["ctrembargos"][$i]["fecinscripcion"] = $e["fecinscripcion"];
                $retorno["ctrembargos"][$i]["esembargo"] = '';

                $ind = $retorno["ctrembargos"][$i]["libro"] . '-' . $e["acto"];
                if (isset($_SESSION["maestroactos"][$ind])) {
                    if ($_SESSION["maestroactos"][$ind]["idgrupoacto"] == '018') {
                        $retorno["ctrembargos"][$i]["esembargo"] = 'S';
                        if ($e["ctrestadoembargo"] != '2') {
                            $retorno["embargos"]++;
                        }
                    }
                }
            }
        }

        // echo count ($retornos["ctrembargos"]);
        unset($arrTemE);
        if ($genlog == 'si') {
            \logApi::general2($nameLog, $retorno["matricula"], 'Leyo embargos');
        }

        // Relación de pagos de afiliacion
        $retorno["periodicoafiliados"] = array();

        // Relacion de anexos documentales
        $retorno["imagenes"] = array();
        if ($tipodata == 'E') {
            $imgs = array();
            if ($retorno["tipoidentificacion"] == '7') {
                $idex = '';
            } else {
                $idex = ltrim(trim($retorno["identificacion"]), "0");
                if (strlen($idex) < 5) {
                    $idex = '';
                }
            }

            // \logApi::general2($nameLog, $retorno["matricula"], 'Entró buscar imagenes: ' . date("His"));
            if ($idex != '') {
                if (ltrim((string) $retorno["matricula"], "0") != '') {
                    $imgs = retornarRegistrosMysqliApi($mysqli, 'mreg_radicacionesanexos', "matricula='" . ltrim((string) $retorno["matricula"], "0") . "' or identificacion like '" . $idex . "%'", "idanexo");
                } else {
                    $imgs = retornarRegistrosMysqliApi($mysqli, 'mreg_radicacionesanexos', "identificacion like '" . $idex . "%'", "idanexo");
                }
            } else {
                if (ltrim((string) $retorno["matricula"], "0") != '') {
                    $imgs = retornarRegistrosMysqliApi($mysqli, 'mreg_radicacionesanexos', "matricula='" . ltrim((string) $retorno["matricula"], "0") . "'", "idanexo");
                }
            }
            // \logApi::general2($nameLog, $retorno["matricula"], 'Salió de buscar imagenes: ' . date("His"));
            if ($imgs) {
                if (!empty($imgs)) {
                    $i = 0;
                    $indAnexos = array();
                    foreach ($imgs as $img) {
                        if ($img["eliminado"] != 'SI') {
                            if ($img["bandeja"] == '4.-REGMER' || $img["bandeja"] == '5.-REGESADL') {
                                if ($img["tipoanexo"] != '509') {
                                    if (!isset($indAnexos[$img["idanexo"]])) {
                                        $i++;
                                        $retorno["imagenes"][$i] = $img;
                                        $indAnexos[$img["idanexo"]] = $img["idanexo"];
                                        if (ltrim((string) $img["idradicacion"], "0") != '') {
                                            $imgs1 = retornarRegistrosMysqliApi($mysqli, 'mreg_radicacionesanexos', "idradicacion='" . $img["idradicacion"] . "'", "idanexo");
                                            if (!empty($imgs1)) {
                                                foreach ($imgs1 as $img1) {
                                                    if ($img1["eliminado"] != 'SI') {
                                                        if ($img1["tipoanexo"] != '509') {
                                                            if (!isset($indAnexos[$img1["idanexo"]])) {
                                                                $i++;
                                                                $retorno["imagenes"][$i] = $img1;
                                                                $indAnexos[$img1["idanexo"]] = $img1["idanexo"];
                                                            }
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                    $retorno["imagenes1"] = $retorno["imagenes"];
                    $retorno["imagenes"] = array();
                    $i = 0;
                    foreach ($retorno["imagenes1"] as $img) {
                        $i++;
                        $retorno["imagenes"][$i] = $img;
                        if (trim((string) $img["bandeja"]) == '') {
                            $tx = retornarRegistroMysqliApi($mysqli, 'bas_tipodoc', "idtipodoc='" . $img["idtipodoc"] . "'");
                            if ($tx === false || empty($tx)) {
                                $retorno["imagenes"][$i]["bandeja"] = '';
                            } else {
                                $retorno["imagenes"][$i]["bandeja"] = $tx["bandejadigitalizacion"];
                            }
                        }
                        if (trim($img["bandeja"]) == '') {
                            if (trim($img["libro"]) != '') {
                                if (strlen(trim($img["libro"])) < 3) {
                                    if (sprintf("%02s", $img["libro"]) < '50') {
                                        $retorno["imagenes"][$i]["bandeja"] = '4.-REGMER';
                                    } else {
                                        $retorno["imagenes"][$i]["bandeja"] = '5.-REGESADL';
                                    }
                                } else {
                                    if (substr($img["libro"], 2, 2) < '50') {
                                        $retorno["imagenes"][$i]["bandeja"] = '4.-REGMER';
                                    } else {
                                        $retorno["imagenes"][$i]["bandeja"] = '5.-REGESADL';
                                    }
                                }
                            }
                        }
                        $obs = '';
                        $fechamostrar = '';
                        $estadocb = '';
                        if (trim($img["libro"]) != '' && trim($img["registro"]) != '') {
                            $estadocb = 'S';
                            if (strlen(trim($img["libro"])) < 3) {
                                if (sprintf("%02s", $img["libro"]) < '50') {
                                    $txLib = 'RM' . sprintf("%02s", trim($img["libro"]));
                                } else {
                                    $txLib = 'RE' . sprintf("%02s", trim($img["libro"]));
                                }
                            } else {
                                $txLib = trim($img["libro"]);
                            }
                            $condic = "libro='" . $txLib . "' and registro='" . ltrim($img["registro"], "0") . "'";
                            $temIns = retornarRegistroMysqliApi($mysqli, 'mreg_est_inscripciones', $condic);
                            if ($temIns && !empty($temIns)) {
                                $obs = '<strong>Inscripción No. </strong>' . $txLib . '-' . $img["registro"];
                                if ($temIns["noticia"] != '') {
                                    if (trim($obs) != '') {
                                        $obs .= '<br>';
                                    }
                                    $obs .= $temIns["noticia"];
                                }
                                $fechamostrar = $temIns["fecharegistro"];
                            }
                        } else {
                            if (ltrim((string) $img["idradicacion"], "0") != '') {
                                $cb = retornarRegistroMysqliApi($mysqli, 'mreg_est_codigosbarras', "codigobarras='" . ltrim((string) $img["idradicacion"], "0") . "'");
                                $fechamostrar = $cb["fecharadicacion"];
                                $actorep = $cb["actoreparto"];
                                $estadocb = retornarRegistroMysqliApi($mysqli, 'mreg_codestados_rutamercantil', "id='" . $cb["estadofinal"] . "'", "estadoterminal");
                                if ($actorep != '') {
                                    if (trim($obs) != '') {
                                        $obs .= '<br>';
                                    }
                                    $obs .= retornarRegistroMysqliApi($mysqli, 'mreg_codrutas', "id='" . $actorep . "'", "descripcion");
                                }
                            } else {
                                if (trim($img["numerorecibo"]) != '') {
                                    $fechamostrar = retornarRegistroMysqliApi($mysqli, 'mreg_est_recibos', "numerorecibo='" . trim($img["numerorecibo"]) . "'", "fecoperacion");
                                    $actorep = retornarRegistroMysqliApi($mysqli, 'mreg_est_recibos', "numerorecibo='" . trim($img["numerorecibo"]) . "'", "servicio");
                                    $estadocb = 'S';
                                    if ($actorep != '') {
                                        if (trim($obs) != '') {
                                            $obs .= '<br>';
                                        }
                                        $obs .= retornarRegistroMysqliApi($mysqli, 'mreg_servicios', "idservicio='" . $actorep . "'", "nombre");
                                    }
                                }
                            }
                        }

                        if (trim($img["idtipodoc"]) != '') {
                            if ($obs != '') {
                                $obs .= '<br>';
                            }
                            $obs .= '<strong>Tipo documental : </strong>(' . $img["idtipodoc"] . ') ' . str_replace("--- ", "", retornarRegistroMysqliApi($mysqli, 'bas_tipodoc', "idtipodoc='" . $img["idtipodoc"] . "'", "nombre"));
                        }

                        if (trim($img["libro"]) == '') {
                            if (trim($img["observaciones"]) != '') {
                                if ($obs != '') {
                                    $obs .= '<br>';
                                }
                                $obs .= $img["observaciones"];
                            }
                        }
                        if ($fechamostrar == '') {
                            $fechamostrar = $img["fechadoc"];
                        }
                        $retorno["imagenes"][$i]["obs"] = $obs;
                        $retorno["imagenes"][$i]["fmostrar"] = $fechamostrar;
                        $retorno["imagenes"][$i]["estadocb"] = $estadocb;
                    }
                }
            }
            $retorno["imagenes"] = \funcionesGenerales::ordenarMatriz($retorno["imagenes"], "fmostrar", true);
            unset($retorno["imagenes1"]);
        }
        if ($genlog == 'si') {
            \logApi::general2($nameLog, $retorno["matricula"], 'Leyo anexos documentales ' . count($retorno["imagenes"]));
        }

        // Confirma si tiene o no beneficio de la Ley 1780
        if (date("Y") == '2017') {
            if ($retorno["organizacion"] == '01' || ($retorno["organizacion"] > '02' && $retorno["categoria"] == '1')) {
                if ($retorno["fechamatricula"] >= '20160502') {
                    if ($retorno["benley1780"] == '') {
                        if (contarRegistrosMysqliApi($mysqli, 'mreg_est_recibos', "matricula='" . $retorno["matricula"] . "' and servicio='01090110' and (fecoperacion between '20160101' and '20161231')") > 0) {
                            $retorno["benley1780"] = 'S';
                        }
                    }
                }
            }
        }

        // Aplica para el 2018
        if (date("Y") == '2018') {
            if ($retorno["organizacion"] == '01' || ($retorno["organizacion"] > '02' && $retorno["categoria"] == '1')) {
                if ($retorno["fechamatricula"] >= '20170101') {
                    if ($retorno["benley1780"] == '') {
                        if (contarRegistrosMysqliApi($mysqli, 'mreg_est_recibos', "matricula='" . $retorno["matricula"] . "' and (servicio IN ('01090110','01090111')) and (fecoperacion between '20170101' and '20171231')") > 0) {
                            $retorno["benley1780"] = 'S';
                        }
                    }
                }
            }
        }

        // Aplica para el 2019
        if (date("Y") == '2019') {
            if ($retorno["organizacion"] == '01' || ($retorno["organizacion"] > '02' && $retorno["categoria"] == '1')) {
                if ($retorno["fechamatricula"] >= '20180101') {
                    if ($retorno["benley1780"] == '') {
                        if (contarRegistrosMysqliApi($mysqli, 'mreg_est_recibos', "matricula='" . $retorno["matricula"] . "' and (servicio IN ('01090110','01090111')) and (fecoperacion between '20180101' and '20181231')") > 0) {
                            $retorno["benley1780"] = 'S';
                        }
                    }
                }
            }
        }

        // Aplica para el 2020
        if (date("Y") == '2020') {
            if ($retorno["organizacion"] == '01' || ($retorno["organizacion"] > '02' && $retorno["categoria"] == '1')) {
                if ($retorno["fechamatricula"] >= '20190101') {
                    if ($retorno["benley1780"] == '') {
                        if (contarRegistrosMysqliApi($mysqli, 'mreg_est_recibos', "matricula='" . $retorno["matricula"] . "' and (servicio IN ('01090110','01090111')) and (fecoperacion between '20190101' and '20191231')") > 0) {
                            $retorno["benley1780"] = 'S';
                        }
                    }
                }
            }
        }

        // Aplica para el 2021
        if (date("Y") == '2021') {
            if ($retorno["organizacion"] == '01' || ($retorno["organizacion"] > '02' && $retorno["categoria"] == '1')) {
                if ($retorno["fechamatricula"] >= '20200101') {
                    if ($retorno["benley1780"] == '') {
                        if (contarRegistrosMysqliApi($mysqli, 'mreg_est_recibos', "matricula='" . $retorno["matricula"] . "' and (servicio IN ('01090110','01090111')) and (fecoperacion between '20200101' and '20201231')") > 0) {
                            $retorno["benley1780"] = 'S';
                        }
                    }
                }
            }
        }

        // Aplica para el 2022
        if (date("Y") == '2022') {
            if ($retorno["organizacion"] == '01' || ($retorno["organizacion"] > '02' && $retorno["categoria"] == '1')) {
                if ($retorno["fechamatricula"] >= '20210101') {
                    if ($retorno["benley1780"] == '') {
                        if (contarRegistrosMysqliApi($mysqli, 'mreg_est_recibos', "matricula='" . $retorno["matricula"] . "' and (servicio IN ('01090110','01090111')) and (fecoperacion between '20210101' and '20211231')") > 0) {
                            $retorno["benley1780"] = 'S';
                        }
                    }
                }
            }
        }

        // Aplica para el 2023
        if (date("Y") == '2023') {
            if ($retorno["organizacion"] == '01' || ($retorno["organizacion"] > '02' && $retorno["categoria"] == '1')) {
                if ($retorno["fechamatricula"] >= '20220101') {
                    if ($retorno["benley1780"] == '') {
                        if (contarRegistrosMysqliApi($mysqli, 'mreg_est_recibos', "matricula='" . $retorno["matricula"] . "' and (servicio IN ('01090110','01090111')) and (fecoperacion between '20220101' and '20221231')") > 0) {
                            $retorno["benley1780"] = 'S';
                        }
                    }
                }
            }
        }

        // Aplica para el 2024
        if (date("Y") == '2024') {
            if ($retorno["organizacion"] == '01' || ($retorno["organizacion"] > '02' && $retorno["categoria"] == '1')) {
                if ($retorno["fechamatricula"] >= '20230101') {
                    if ($retorno["benley1780"] == '') {
                        if (contarRegistrosMysqliApi($mysqli, 'mreg_est_recibos', "matricula='" . $retorno["matricula"] . "' and (servicio IN ('01090110','01090111')) and (fecoperacion between '20230101' and '20231231')") > 0) {
                            $retorno["benley1780"] = 'S';
                        }
                    }
                }
            }
        }

        // Aplica para el 2025
        if (date("Y") == '2025') {
            if ($retorno["organizacion"] == '01' || ($retorno["organizacion"] > '02' && $retorno["categoria"] == '1')) {
                if ($retorno["fechamatricula"] >= '20240101') {
                    if ($retorno["benley1780"] == '') {
                        if (contarRegistrosMysqliApi($mysqli, 'mreg_est_recibos', "matricula='" . $retorno["matricula"] . "' and (servicio IN ('01090110','01090111')) and (fecoperacion between '20240101' and '20241231')") > 0) {
                            $retorno["benley1780"] = 'S';
                        }
                    }
                }
            }
        }


        // 2017-09-26: jint : DATOS DEL CAE
        $retorno["placaalcaldia"] = '';
        $retorno["placaalcaldiafecha"] = '';
        $retorno["reportealcaldia"] = '';

        $temx = retornarRegistroMysqliApi($mysqli, 'mreg_alcaldias', "idmunicipio='" . trim($retorno["muncom"]) . "'");
        if ($temx && !empty($temx)) {
            if ($temx["eliminado"] != 'SI') {
                if ($temx["fechainicio"] <= $retorno["fechamatricula"]) {
                    $retorno["reportealcaldia"] = 'si';
                }
            }
        }
        unset($temx);

        if ($retorno["reportealcaldia"] == 'si') {
            $temx = retornarRegistroMysqliApi($mysqli, 'mreg_matriculasalcaldia', "idmatricula='" . ltrim($retorno["matricula"], "0") . "'");
            if ($temx && !empty($temx)) {
                $retorno["placaalcaldia"] = trim($temx["matriculaic"]);
                $retorno["placaalcaldiafecha"] = trim($temx["fechamatriculaic"]);
            } else {
                $temx = retornarRegistroMysqliApi($mysqli, 'mreg_envio_matriculas_api', "matricula='" . ltrim($retorno["matricula"], "0") . "'", '*', 'U');
                if ($temx && !empty($temx)) {
                    $retorno["placaalcaldia"] = trim($temx["codigoasignadorespuesta"]);
                    $retorno["placaalcaldiafecha"] = trim($temx["fechahorarespuesta"]);
                }
            }
            unset($temx);
        }

        // *********************************************************************************** 
        // Valida si la matrícula se encuentra inactiva
        // *********************************************************************************** 
        if ($retorno["estadomatricula"] == 'MA' || $retorno["estadomatricula"] == 'IA' || $retorno["estadomatricula"] == 'MR' || $retorno["estadomatricula"] == 'IR') {
            if ($retorno["fecharenovacion"] != '') {
                if ($retorno["ultanoren"] < date("Y")) {
                    if (\funcionesRegistrales::inactivarSiprefMatriculas($mysqli, $retorno["matricula"], $retorno["fechamatricula"], $retorno["fecharenovacion"])) {
                        $retorno["estadomatricula"] = 'MI';
                        if (substr($retorno["matricula"], 0, 1) == 'S') {
                            $retorno["estadomatricula"] = 'II';
                        }
                    }
                }
            }
        }

        // *********************************************************************************** 
        // Campos adicionales del expediente
        // ***********************************************************************************         
        $retorno["domicilio_ong"] = '';
        $retorno["codrespotri"] = array();
        $retorno["tituloorganodirectivo"] = '';
        $retorno["siglaenconstitucion"] = '';
        $retorno["condiespe2219"] = '';
        $retorno["etnia"] = '';
        $retorno["participacionetnia"] = '';
        $retorno["emprendimientosocial"] = '';
        $retorno["empsoccategorias"] = '';
        $retorno["empsocbeneficiarios"] = '';
        $retorno["empsoccategorias_otros"] = '';
        $retorno["empsocbeneficiarios_otros"] = '';
        $retorno["descripcionmotivocancelacion"] = '';

        $iResp = 0;
        $temx = retornarRegistrosMysqliApi($mysqli, 'mreg_est_inscritos_campos', "matricula='" . ltrim($retorno["matricula"], "0") . "'", "campo");
        foreach ($temx as $tx) {
            if (trim($tx["contenido"]) != '') {
                if ($tx["campo"] == 'domicilio_ong') {
                    $retorno[$tx["campo"]] = $tx["contenido"];
                }
                if ($tx["campo"] == 'etnia') {
                    $retorno[$tx["campo"]] = $tx["contenido"];
                }
                if ($tx["campo"] == 'participacionetnia') {
                    $retorno[$tx["campo"]] = doubleval($tx["contenido"]);
                }
                if ($tx["campo"] == 'emprendimientosocial') {
                    $retorno[$tx["campo"]] = $tx["contenido"];
                }
                if ($tx["campo"] == 'tituloorganodirectivo') {
                    $retorno[$tx["campo"]] = $tx["contenido"];
                }
                if ($tx["campo"] == 'siglaenconstitucion') {
                    $retorno[$tx["campo"]] = $tx["contenido"];
                }
                if ($tx["campo"] == 'condiespe2219') {
                    $retorno[$tx["campo"]] = $tx["contenido"];
                }
                if ($tx["campo"] == 'empsoccategorias') {
                    $retorno[$tx["campo"]] = $tx["contenido"];
                    if ($tx["contenido"] != '') {
                        $esx = explode(",", $tx["contenido"]);
                        foreach ($esx as $esx1) {
                            $retorno[$esx1] = 'S';
                        }
                    }
                }
                if ($tx["campo"] == 'empsoccategorias_otros') {
                    $retorno[$tx["campo"]] = $tx["contenido"];
                }
                if ($tx["campo"] == 'empsocbeneficiarios') {
                    $retorno[$tx["campo"]] = $tx["contenido"];
                    if ($tx["contenido"] != '') {
                        $esx = explode(",", $tx["contenido"]);
                        foreach ($esx as $esx1) {
                            $retorno[$esx1] = 'S';
                        }
                    }
                }
                if ($tx["campo"] == 'empsocbeneficiarios_otros') {
                    $retorno[$tx["campo"]] = $tx["contenido"];
                }
                if ($tx["campo"] == 'codrespotri') {
                    $lt = explode(",", $tx["contenido"]);
                    foreach ($lt as $lt1) {
                        if (trim($lt1) != '') {
                            $iResp++;
                            $retorno["codrespotri"][$iResp] = trim($lt1);
                        }
                    }
                }
                if ($tx["campo"] == 'descripcionmotivocancelacion') {
                    $retorno[$tx["campo"]] = $tx["contenido"];
                }
                if ($tx["campo"] == 'extinciondominio') {
                    $retorno[$tx["campo"]] = $tx["contenido"];
                }
                if ($tx["campo"] == 'extinciondominiofechainicio') {
                    $retorno[$tx["campo"]] = $tx["contenido"];
                }
                if ($tx["campo"] == 'extinciondominiofechafinal') {
                    $retorno[$tx["campo"]] = $tx["contenido"];
                }
                if ($tx["campo"] == 'ctrcontrolaccesopublico') {
                    $retorno[$tx["campo"]] = $tx["contenido"];
                }
            }
        }

        //
        //
        if ($retorno["organizacion"] > '02' && $retorno["categoria"] == '1') {
            $retorno["estadisuelta"] = 'no';
        }
        if (
                $retorno["estadomatricula"] != 'MF' &&
                $retorno["estadomatricula"] != 'MC' &&
                $retorno["estadomatricula"] != 'IC' &&
                $retorno["estadomatricula"] != 'IF'
        ) {
            if ($retorno["ctrcancelacion1429"] != '3') {
                if (date("Ymd") > $_SESSION["generales"]["fcorte"]) {
                    if ($retorno["ultanoren"] == (date("Y") - 1)) {
                        $retorno["norenovado"] = 'si';
                        if ($retorno["disueltaporvencimiento"] == 'si') {
                            $retorno["norenovado"] = 'no';
                            $retorno["estadisuelta"] = 'si';
                        } else {
                            if ($retorno["disueltaporacto510"] == 'si') {
                                if ($retorno["fechaacto510"] <= $_SESSION["generales"]["fcorte"]) {
                                    $retorno["norenovado"] = 'no';
                                    $retorno["estadisuelta"] = 'si';
                                }
                            } else {
                                if ($retorno["perdidacalidadcomerciante"] == 'si') {
                                    $ano1 = $retorno["ultanoren"] + 1;
                                    $fcorte1 = retornarRegistroMysqliApi($mysqli, 'mreg_cortes_renovacion', "ano='" . $ano1 . "'", "corte");
                                    if ($retorno["fechaperdidacalidadcomerciante"] <= $fcorte1) {
                                        $retorno["norenovado"] = 'no';
                                    }
                                }
                            }
                        }
                    }
                }
                //

                if ($retorno["ultanoren"] < (date("Y") - 1)) {
                    $retorno["norenovado"] = 'si';
                    if ($retorno["disueltaporvencimiento"] == 'si') {
                        $retorno["estadisuelta"] = 'si';
                        if (($retorno["fechaacto510"] != '') && $retorno["fechaacto510"] < $retorno["fechavencimiento"]) {
                            $retorno["disueltaporvencimiento"] = 'no';
                        } else {
                            if (substr($retorno["fechavencimiento"], 4, 4) <= $_SESSION["generales"]["fcortemesdia"]) {
                                $ultanorendis = substr($retorno["fechavencimiento"], 0, 4) - 1;
                            } else {
                                $ultanorendis = substr($retorno["fechavencimiento"], 0, 4);
                            }
                            if ($retorno["ultanoren"] >= $ultanorendis) {
                                $retorno["norenovado"] = 'no';
                            }
                        }
                    }
                    if ($retorno["disueltaporacto510"] == 'si') {
                        $retorno["estadisuelta"] = 'si';
                        if (substr($retorno["fechaacto510"], 0, 4) == $retorno["ultanoren"]) {
                            $retorno["norenovado"] = 'no';
                        } else {
                            $ano1 = $retorno["ultanoren"] + 1;
                            $fcorte1 = retornarRegistroMysqliApi($mysqli, 'mreg_cortes_renovacion', "ano='" . $ano1 . "'", "corte");
                            if ($retorno["fechaacto510"] <= $fcorte1) {
                                $retorno["norenovado"] = 'no';
                            } else {
                                if ($retorno["perdidacalidadcomerciante"] == 'si') {
                                    if ($retorno["fechaperdidacalidadcomerciante"] <= $fcorte1) {
                                        $retorno["norenovado"] = 'no';
                                    }
                                } else {
                                    if ($retorno["reactivadaacto511"] == 'si') {
                                        if (date("Ymd") <= $_SESSION["generales"]["fcorte"]) {
                                            if ($retorno["fechaacto511"] >= (date("Y") - 1) . '0101' && $retorno["fechaacto511"] <= $_SESSION["generales"]["fcorte"]) {
                                                $retorno["norenovado"] = 'no';
                                            }
                                        } else {
                                            if ($retorno["fechaacto511"] >= date("Y") . '0101') {
                                                $retorno["norenovado"] = 'no';
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        // *************************************************** //
        // Arma arreglo de habilitaciones especiales
        // *************************************************** //
        $estransportecarga = 'no';
        $estransporteespecial = 'no';
        $estransportemixto = 'no';

        $certificartransportecarga = 'no';
        $certificartransporteespecial = 'no';
        $certificartransportemixto = 'no';
        $certificartransportepasajeros = 'no';

        $certificargrupo093 = 'no';

        //
        if (
                $retorno["ciius"][1] == 'H4923' ||
                $retorno["ciius"][2] == 'H4923' ||
                $retorno["ciius"][3] == 'H4923' ||
                $retorno["ciius"][4] == 'H4923'
        ) {
            $estransportecarga = 'si';
        }
        if (
                $retorno["ciius"][1] == 'H4921' ||
                $retorno["ciius"][2] == 'H4921' ||
                $retorno["ciius"][3] == 'H4921' ||
                $retorno["ciius"][4] == 'H4921'
        ) {
            $estransporteespecial = 'si';
        }
        if (
                $retorno["ciius"][1] == 'H4922' ||
                $retorno["ciius"][2] == 'H4922' ||
                $retorno["ciius"][3] == 'H4922' ||
                $retorno["ciius"][4] == 'H4922'
        ) {
            $estransportemixto = 'si';
        }

        foreach ($retorno["inscripciones"] as $ins) {
            if ($ins["grupoacto"] == '066') {
                if ($ins["freg"] < $_SESSION["generales"]["FECHA_CORTE_REVOCACION"] && $ins["crev"] == '1') {
                    $ins["crev"] = '0';
                }
                if ($ins["crev"] != '1' && $ins["crev"] != '9') {
                    $certificartransportecarga = 'encontro';
                }
            }
        }

        foreach ($retorno["inscripciones"] as $ins) {
            if ($ins["grupoacto"] == '067') {
                if ($ins["freg"] < $_SESSION["generales"]["FECHA_CORTE_REVOCACION"] && $ins["crev"] == '1') {
                    $ins["crev"] = '0';
                }
                if ($ins["crev"] != '1' && $ins["crev"] != '9') {
                    $certificartransporteespecial = 'encontro';
                }
            }
        }

        foreach ($retorno["inscripciones"] as $ins) {
            if ($ins["grupoacto"] == '091') {
                if ($ins["freg"] < $_SESSION["generales"]["FECHA_CORTE_REVOCACION"] && $ins["crev"] == '1') {
                    $ins["crev"] = '0';
                }
                if ($ins["crev"] != '1' && $ins["crev"] != '9') {
                    $certificartransportemixto = 'encontro';
                }
            }
        }

        foreach ($retorno["inscripciones"] as $ins) {
            if ($ins["grupoacto"] == '092') {
                if ($ins["freg"] < $_SESSION["generales"]["FECHA_CORTE_REVOCACION"] && $ins["crev"] == '1') {
                    $ins["crev"] = '0';
                }
                if ($ins["crev"] != '1' && $ins["crev"] != '9') {
                    $certificartransportepasajeros = 'encontro';
                }
            }
        }

        foreach ($retorno["inscripciones"] as $ins) {
            if ($ins["grupoacto"] == '093') {
                if ($ins["freg"] < $_SESSION["generales"]["FECHA_CORTE_REVOCACION"] && $ins["crev"] == '1') {
                    $ins["crev"] = '0';
                }
                $imp = 'no';
                if (trim((string) $ins["flim"])) {
                    $imp = 'si';
                } else {
                    if (trim((string) $ins["flim"]) != '' && $ins["flim"] < date("Ymd")) {
                        $imp = 'si';
                    }
                }
                if ($imp == 'si') {
                    if ($ins["crev"] != '1' && $ins["crev"] != '9') {
                        $certificargrupo093 = 'no';
                    }
                }
            }
        }


        if ($estransportecarga == 'si') {
            $estransportecarga = 'falta';
            foreach ($retorno["inscripciones"] as $ins) {
                if ($ins["grupoacto"] == '066') {
                    if ($ins["freg"] < $_SESSION["generales"]["FECHA_CORTE_REVOCACION"] && $ins["crev"] == '1') {
                        $ins["crev"] = '0';
                    }
                    if ($ins["crev"] != '1' && $ins["crev"] != '9') {
                        $estransportecarga = 'encontro';
                    }
                }
            }
        }

        if ($estransporteespecial == 'si') {
            $estransporteespecial = 'falta';
            foreach ($retorno["inscripciones"] as $ins) {
                if ($ins["grupoacto"] == '067') {
                    if ($ins["freg"] < $_SESSION["generales"]["FECHA_CORTE_REVOCACION"] && $ins["crev"] == '1') {
                        $ins["crev"] = '0';
                    }
                    if ($ins["crev"] != '1' && $ins["crev"] != '9') {
                        $estransporteespecial = 'encontro';
                    }
                }
            }
        }

        if ($estransportemixto == 'si') {
            $estransportemixto = 'falta';
            foreach ($retorno["inscripciones"] as $ins) {
                if ($ins["grupoacto"] == '091') {
                    if ($ins["freg"] < $_SESSION["generales"]["FECHA_CORTE_REVOCACION"] && $ins["crev"] == '1') {
                        $ins["crev"] = '0';
                    }
                    if ($ins["crev"] != '1' && $ins["crev"] != '9') {
                        $estransportemixto = 'encontro';
                    }
                }
            }
        }

        //
        /*
          $retorno["inscripciones"][$ix]["camant"] = $x["camaraanterior"];
          $retorno["inscripciones"][$ix]["libant"] = $x["libroanterior"];
          $retorno["inscripciones"][$ix]["regant"] = $x["registroanterior"];
          $retorno["inscripciones"][$ix]["fecant"] = $x["fecharegistroanterior"];

          $retorno["inscripciones"][$ix]["camant2"] = $x["camaraanterior2"];
          $retorno["inscripciones"][$ix]["libant2"] = $x["libroanterior2"];
          $retorno["inscripciones"][$ix]["regant2"] = $x["registroanterior2"];
          $retorno["inscripciones"][$ix]["fecant2"] = $x["fecharegistroanterior2"];

          $retorno["inscripciones"][$ix]["camant3"] = $x["camaraanterior3"];
          $retorno["inscripciones"][$ix]["libant3"] = $x["libroanterior3"];
          $retorno["inscripciones"][$ix]["regant3"] = $x["registroanterior3"];
          $retorno["inscripciones"][$ix]["fecant3"] = $x["fecharegistroanterior3"];

          $retorno["inscripciones"][$ix]["camant4"] = $x["camaraanterior4"];
          $retorno["inscripciones"][$ix]["libant4"] = $x["libroanterior4"];
          $retorno["inscripciones"][$ix]["regant4"] = $x["registroanterior4"];
          $retorno["inscripciones"][$ix]["fecant4"] = $x["fecharegistroanterior4"];

          $retorno["inscripciones"][$ix]["camant5"] = $x["camaraanterior5"];
          $retorno["inscripciones"][$ix]["libant5"] = $x["libroanterior5"];
          $retorno["inscripciones"][$ix]["regant5"] = $x["registroanterior5"];
          $retorno["inscripciones"][$ix]["fecant5"] = $x["fecharegistroanterior5"];
         */

        if ($certificartransportemixto == 'encontro') {
            foreach ($retorno["inscripciones"] as $ins) {
                if ($ins["grupoacto"] == '091') {
                    if ($ins["freg"] < $_SESSION["generales"]["FECHA_CORTE_REVOCACION"] && $ins["crev"] == '1') {
                        $ins["crev"] = '0';
                    }
                    if ($ins["crev"] != '1' && $ins["crev"] != '9') {
                        if (trim($ins["ndocext"]) != '') {
                            $ndocx = $ins["ndocext"];
                        } else {
                            $ndocx = $ins["ndoc"];
                        }
                        $txt = 'Mediante inscripción No. ' . $ins["nreg"] . ' de ' . strtolower(\funcionesGenerales::mostrarFechaLetras1($ins["freg"]));
                        if ($ins["camant"] != '') {
                            $txt .= ', inscrito previamente en la ' . retornarRegistroMysqliApi($mysqli, 'bas_camaras', "id='" . $ins["camant"] . "'", "nombre") . ' el ' . strtolower(\funcionesGenerales::mostrarFechaLetras1($ins["fecant"]));
                            if ($ins["camant2"] != '') {
                                $txt .= ', inscrito previamente en la ' . retornarRegistroMysqliApi($mysqli, 'bas_camaras', "id='" . $ins["camant2"] . "'", "nombre") . ' el ' . strtolower(\funcionesGenerales::mostrarFechaLetras1($ins["fecant2"]));
                                if ($ins["camant3"] != '') {
                                    $txt .= ', inscrito previamente en la ' . retornarRegistroMysqliApi($mysqli, 'bas_camaras', "id='" . $ins["camant3"] . "'", "nombre") . ' el ' . strtolower(\funcionesGenerales::mostrarFechaLetras1($ins["fecant3"]));
                                    if ($ins["camant4"] != '') {
                                        $txt .= ', inscrito previamente en la ' . retornarRegistroMysqliApi($mysqli, 'bas_camaras', "id='" . $ins["camant4"] . "'", "nombre") . ' el ' . strtolower(\funcionesGenerales::mostrarFechaLetras1($ins["fecant4"]));
                                        if ($ins["camant5"] != '') {
                                            $txt .= ', inscrito previamente en la ' . retornarRegistroMysqliApi($mysqli, 'bas_camaras', "id='" . $ins["camant5"] . "'", "nombre") . ' el ' . strtolower(\funcionesGenerales::mostrarFechaLetras1($ins["fecant5"]));
                                        }
                                    }
                                }
                            }
                            $txt .= ', ';
                        } else {
                            $txt .= ' ';
                        }
                        $txt .= 'se registró el acto administrativo No. ' . $ndocx . ' de ' . strtolower(\funcionesGenerales::mostrarFechaLetras1($ins["fdoc"])) . ', expedido por ';
                        $txt .= 'El Ministerio de Transporte que lo habilita para prestar el servicio público de transporte terrestre automotor mixto.';
                        $retorno["habilitacionesespeciales"][] = $txt;
                    }
                }
            }
        }

        //
        if ($certificartransportepasajeros == 'encontro') {
            foreach ($retorno["inscripciones"] as $ins) {
                if ($ins["grupoacto"] == '092') {
                    if ($ins["freg"] < $_SESSION["generales"]["FECHA_CORTE_REVOCACION"] && $ins["crev"] == '1') {
                        $ins["crev"] = '0';
                    }
                    if ($ins["crev"] != '1' && $ins["crev"] != '9') {
                        if (trim($ins["ndocext"]) != '') {
                            $ndocx = $ins["ndocext"];
                        } else {
                            $ndocx = $ins["ndoc"];
                        }
                        $txt = 'Mediante inscripción No. ' . $ins["nreg"] . ' de ' . strtolower(\funcionesGenerales::mostrarFechaLetras1($ins["freg"]));
                        if ($ins["camant"] != '') {
                            $txt .= ', inscrito previamente en la ' . retornarRegistroMysqliApi($mysqli, 'bas_camaras', "id='" . $ins["camant"] . "'", "nombre") . ' el ' . strtolower(\funcionesGenerales::mostrarFechaLetras1($ins["fecant"]));
                            if ($ins["camant2"] != '') {
                                $txt .= ', inscrito previamente en la ' . retornarRegistroMysqliApi($mysqli, 'bas_camaras', "id='" . $ins["camant2"] . "'", "nombre") . ' el ' . strtolower(\funcionesGenerales::mostrarFechaLetras1($ins["fecant2"]));
                                if ($ins["camant3"] != '') {
                                    $txt .= ', inscrito previamente en la ' . retornarRegistroMysqliApi($mysqli, 'bas_camaras', "id='" . $ins["camant3"] . "'", "nombre") . ' el ' . strtolower(\funcionesGenerales::mostrarFechaLetras1($ins["fecant3"]));
                                    if ($ins["camant4"] != '') {
                                        $txt .= ', inscrito previamente en la ' . retornarRegistroMysqliApi($mysqli, 'bas_camaras', "id='" . $ins["camant4"] . "'", "nombre") . ' el ' . strtolower(\funcionesGenerales::mostrarFechaLetras1($ins["fecant4"]));
                                        if ($ins["camant5"] != '') {
                                            $txt .= ', inscrito previamente en la ' . retornarRegistroMysqliApi($mysqli, 'bas_camaras', "id='" . $ins["camant5"] . "'", "nombre") . ' el ' . strtolower(\funcionesGenerales::mostrarFechaLetras1($ins["fecant5"]));
                                        }
                                    }
                                }
                            }
                            $txt .= ', ';
                        } else {
                            $txt .= ' ';
                        }
                        $txt .= 'se registró el acto administrativo No. ' . $ndocx . ' de ' . strtolower(\funcionesGenerales::mostrarFechaLetras1($ins["fdoc"])) . ', expedido por ';
                        $txt .= 'El Ministerio de Transporte que lo habilita para prestar el servicio público de transporte terrestre de pasajeros.';
                        $retorno["habilitacionesespeciales"][] = $txt;
                    }
                }
            }
        }

        //
        if ($estransportecarga == 'encontro' || $certificartransportecarga == 'encontro') {
            foreach ($retorno["inscripciones"] as $ins) {
                if ($ins["grupoacto"] == '066') {
                    if ($ins["freg"] < $_SESSION["generales"]["FECHA_CORTE_REVOCACION"] && $ins["crev"] == '1') {
                        $ins["crev"] = '0';
                    }
                    if ($ins["crev"] != '1' && $ins["crev"] != '9') {
                        if (trim($ins["ndocext"]) != '') {
                            $ndocx = $ins["ndocext"];
                        } else {
                            $ndocx = $ins["ndoc"];
                        }
                        if ($ins["idmunidoc"] != '') {
                            if (trim($ins["txoridoc"]) != '') {
                                $txtmunicipio = 'en ' . ucwords(strtolower(retornarNombreMunicipioMysqliApi($mysqli, $ins["idmunidoc"])));
                                $txt = 'Mediante inscripción No. ' . $ins["nreg"] . ' de ' . strtolower(\funcionesGenerales::mostrarFechaLetras1($ins["freg"]));
                                if ($ins["camant"] != '') {
                                    $txt .= ', inscrito previamente en la ' . retornarRegistroMysqliApi($mysqli, 'bas_camaras', "id='" . $ins["camant"] . "'", "nombre") . ' el ' . strtolower(\funcionesGenerales::mostrarFechaLetras1($ins["fecant"]));
                                    if ($ins["camant2"] != '') {
                                        $txt .= ', inscrito previamente en la ' . retornarRegistroMysqliApi($mysqli, 'bas_camaras', "id='" . $ins["camant2"] . "'", "nombre") . ' el ' . strtolower(\funcionesGenerales::mostrarFechaLetras1($ins["fecant2"]));
                                        if ($ins["camant3"] != '') {
                                            $txt .= ', inscrito previamente en la ' . retornarRegistroMysqliApi($mysqli, 'bas_camaras', "id='" . $ins["camant3"] . "'", "nombre") . ' el ' . strtolower(\funcionesGenerales::mostrarFechaLetras1($ins["fecant3"]));
                                            if ($ins["camant4"] != '') {
                                                $txt .= ', inscrito previamente en la ' . retornarRegistroMysqliApi($mysqli, 'bas_camaras', "id='" . $ins["camant4"] . "'", "nombre") . ' el ' . strtolower(\funcionesGenerales::mostrarFechaLetras1($ins["fecant4"]));
                                                if ($ins["camant5"] != '') {
                                                    $txt .= ', inscrito previamente en la ' . retornarRegistroMysqliApi($mysqli, 'bas_camaras', "id='" . $ins["camant5"] . "'", "nombre") . ' el ' . strtolower(\funcionesGenerales::mostrarFechaLetras1($ins["fecant5"]));
                                                }
                                            }
                                        }
                                    }
                                    $txt .= ', ';
                                } else {
                                    $txt .= ' ';
                                }
                                $txt .= 'se registró el acto administrativo No. ' . $ndocx . ' de ' . strtolower(\funcionesGenerales::mostrarFechaLetras1($ins["fdoc"])) . ', expedido por ';
                                $txt .= ucwords(strtolower($ins["txoridoc"])) . ' ' . $txtmunicipio . ', que lo habilita para prestar el servicio ';
                                $txt .= 'público de transporte terrestre automotor en la modalidad de carga.';
                                $retorno["habilitacionesespeciales"][] = $txt;
                            } else {
                                $txtmunicipio = 'expedido en ' . ucwords(strtolower(retornarNombreMunicipioMysqliApi($mysqli, $ins["idmunidoc"])));
                                $txt = 'Mediante inscripción No. ' . $ins["nreg"] . ' de ' . strtolower(\funcionesGenerales::mostrarFechaLetras1($ins["freg"]));
                                if ($ins["camant"] != '') {
                                    $txt .= ', inscrito previamente en la ' . retornarRegistroMysqliApi($mysqli, 'bas_camaras', "id='" . $ins["camant"] . "'", "nombre") . ' el ' . strtolower(\funcionesGenerales::mostrarFechaLetras1($ins["fecant"]));
                                    if ($ins["camant2"] != '') {
                                        $txt .= ', inscrito previamente en la ' . retornarRegistroMysqliApi($mysqli, 'bas_camaras', "id='" . $ins["camant2"] . "'", "nombre") . ' el ' . strtolower(\funcionesGenerales::mostrarFechaLetras1($ins["fecant2"]));
                                        if ($ins["camant3"] != '') {
                                            $txt .= ', inscrito previamente en la ' . retornarRegistroMysqliApi($mysqli, 'bas_camaras', "id='" . $ins["camant3"] . "'", "nombre") . ' el ' . strtolower(\funcionesGenerales::mostrarFechaLetras1($ins["fecant3"]));
                                            if ($ins["camant4"] != '') {
                                                $txt .= ', inscrito previamente en la ' . retornarRegistroMysqliApi($mysqli, 'bas_camaras', "id='" . $ins["camant4"] . "'", "nombre") . ' el ' . strtolower(\funcionesGenerales::mostrarFechaLetras1($ins["fecant4"]));
                                                if ($ins["camant5"] != '') {
                                                    $txt .= ', inscrito previamente en la ' . retornarRegistroMysqliApi($mysqli, 'bas_camaras', "id='" . $ins["camant5"] . "'", "nombre") . ' el ' . strtolower(\funcionesGenerales::mostrarFechaLetras1($ins["fecant5"]));
                                                }
                                            }
                                        }
                                    }
                                    $txt .= ', ';
                                } else {
                                    $txt .= ' ';
                                }
                                $txt .= 'se registró el acto administrativo No. ' . $ndocx . ' de ' . strtolower(\funcionesGenerales::mostrarFechaLetras1($ins["fdoc"])) . ', ' . $txtmunicipio;
                                $txt .= 'que lo habilita para prestar el servicio ';
                                $txt .= 'público de transporte terrestre automotor en la modalidad de carga.';
                                $retorno["habilitacionesespeciales"][] = $txt;
                            }
                        } else {
                            if (trim($ins["txoridoc"]) != '') {
                                $txt = 'Mediante inscripción No. ' . $ins["nreg"] . ' de ' . strtolower(\funcionesGenerales::mostrarFechaLetras1($ins["freg"]));
                                if ($ins["camant"] != '') {
                                    $txt .= ', inscrito previamente en la ' . retornarRegistroMysqliApi($mysqli, 'bas_camaras', "id='" . $ins["camant"] . "'", "nombre") . ' el ' . strtolower(\funcionesGenerales::mostrarFechaLetras1($ins["fecant"]));
                                    if ($ins["camant2"] != '') {
                                        $txt .= ', inscrito previamente en la ' . retornarRegistroMysqliApi($mysqli, 'bas_camaras', "id='" . $ins["camant2"] . "'", "nombre") . ' el ' . strtolower(\funcionesGenerales::mostrarFechaLetras1($ins["fecant2"]));
                                        if ($ins["camant3"] != '') {
                                            $txt .= ', inscrito previamente en la ' . retornarRegistroMysqliApi($mysqli, 'bas_camaras', "id='" . $ins["camant3"] . "'", "nombre") . ' el ' . strtolower(\funcionesGenerales::mostrarFechaLetras1($ins["fecant3"]));
                                            if ($ins["camant4"] != '') {
                                                $txt .= ', inscrito previamente en la ' . retornarRegistroMysqliApi($mysqli, 'bas_camaras', "id='" . $ins["camant4"] . "'", "nombre") . ' el ' . strtolower(\funcionesGenerales::mostrarFechaLetras1($ins["fecant4"]));
                                                if ($ins["camant5"] != '') {
                                                    $txt .= ', inscrito previamente en la ' . retornarRegistroMysqliApi($mysqli, 'bas_camaras', "id='" . $ins["camant5"] . "'", "nombre") . ' el ' . strtolower(\funcionesGenerales::mostrarFechaLetras1($ins["fecant5"]));
                                                }
                                            }
                                        }
                                    }
                                    $txt .= ', ';
                                } else {
                                    $txt .= ' ';
                                }
                                $txt .= 'se registró el acto administrativo No. ' . $ndocx . ' de ' . strtolower(\funcionesGenerales::mostrarFechaLetras1($ins["fdoc"])) . ', expedido por ';
                                $txt .= ucwords(strtolower($ins["txoridoc"])) . ', que lo habilita para prestar el servicio ';
                                $txt .= 'público de transporte terrestre automotor en la modalidad de carga.';
                                $retorno["habilitacionesespeciales"][] = $txt;
                            } else {
                                $txt = 'Mediante inscripción No. ' . $ins["nreg"] . ' de ' . strtolower(\funcionesGenerales::mostrarFechaLetras1($ins["freg"]));
                                if ($ins["camant"] != '') {
                                    $txt .= ', inscrito previamente en la ' . retornarRegistroMysqliApi($mysqli, 'bas_camaras', "id='" . $ins["camant"] . "'", "nombre") . ' el ' . strtolower(\funcionesGenerales::mostrarFechaLetras1($ins["fecant"]));
                                    if ($ins["camant2"] != '') {
                                        $txt .= ', inscrito previamente en la ' . retornarRegistroMysqliApi($mysqli, 'bas_camaras', "id='" . $ins["camant2"] . "'", "nombre") . ' el ' . strtolower(\funcionesGenerales::mostrarFechaLetras1($ins["fecant2"]));
                                        if ($ins["camant3"] != '') {
                                            $txt .= ', inscrito previamente en la ' . retornarRegistroMysqliApi($mysqli, 'bas_camaras', "id='" . $ins["camant3"] . "'", "nombre") . ' el ' . strtolower(\funcionesGenerales::mostrarFechaLetras1($ins["fecant3"]));
                                            if ($ins["camant4"] != '') {
                                                $txt .= ', inscrito previamente en la ' . retornarRegistroMysqliApi($mysqli, 'bas_camaras', "id='" . $ins["camant4"] . "'", "nombre") . ' el ' . strtolower(\funcionesGenerales::mostrarFechaLetras1($ins["fecant4"]));
                                                if ($ins["camant5"] != '') {
                                                    $txt .= ', inscrito previamente en la ' . retornarRegistroMysqliApi($mysqli, 'bas_camaras', "id='" . $ins["camant5"] . "'", "nombre") . ' el ' . strtolower(\funcionesGenerales::mostrarFechaLetras1($ins["fecant5"]));
                                                }
                                            }
                                        }
                                    }
                                    $txt .= ', ';
                                } else {
                                    $txt .= ' ';
                                }
                                $txt .= 'se registró el acto administrativo No. ' . $ndocx . ' de ' . strtolower(\funcionesGenerales::mostrarFechaLetras1($ins["fdoc"])) . ', ';
                                $txt .= 'que lo habilita para prestar el servicio ';
                                $txt .= 'público de transporte terrestre automotor en la modalidad de carga.';
                                $retorno["habilitacionesespeciales"][] = $txt;
                            }
                        }
                    }
                }
            }
        }

        //
        if ($estransporteespecial == 'encontro' || $certificartransporteespecial == 'encontro') {
            foreach ($retorno["inscripciones"] as $ins) {
                if ($ins["grupoacto"] == '067') {
                    if ($ins["freg"] < $_SESSION["generales"]["FECHA_CORTE_REVOCACION"] && $ins["crev"] == '1') {
                        $ins["crev"] = '0';
                    }
                    if ($ins["crev"] != '1' && $ins["crev"] != '9') {
                        if (trim($ins["ndocext"]) != '') {
                            $ndocx = $ins["ndocext"];
                        } else {
                            $ndocx = $ins["ndoc"];
                        }
                        if (trim($ins["txoridoc"]) != '') {
                            $txt = 'Mediante inscripción No. ' . $ins["nreg"] . ' de ' . strtolower(\funcionesGenerales::mostrarFechaLetras1($ins["freg"]));
                            if ($ins["camant"] != '') {
                                $txt .= ', inscrito previamente en la ' . retornarRegistroMysqliApi($mysqli, 'bas_camaras', "id='" . $ins["camant"] . "'", "nombre") . ' el ' . strtolower(\funcionesGenerales::mostrarFechaLetras1($ins["fecant"]));
                                if ($ins["camant2"] != '') {
                                    $txt .= ', inscrito previamente en la ' . retornarRegistroMysqliApi($mysqli, 'bas_camaras', "id='" . $ins["camant2"] . "'", "nombre") . ' el ' . strtolower(\funcionesGenerales::mostrarFechaLetras1($ins["fecant2"]));
                                    if ($ins["camant3"] != '') {
                                        $txt .= ', inscrito previamente en la ' . retornarRegistroMysqliApi($mysqli, 'bas_camaras', "id='" . $ins["camant3"] . "'", "nombre") . ' el ' . strtolower(\funcionesGenerales::mostrarFechaLetras1($ins["fecant3"]));
                                        if ($ins["camant4"] != '') {
                                            $txt .= ', inscrito previamente en la ' . retornarRegistroMysqliApi($mysqli, 'bas_camaras', "id='" . $ins["camant4"] . "'", "nombre") . ' el ' . strtolower(\funcionesGenerales::mostrarFechaLetras1($ins["fecant4"]));
                                            if ($ins["camant5"] != '') {
                                                $txt .= ', inscrito previamente en la ' . retornarRegistroMysqliApi($mysqli, 'bas_camaras', "id='" . $ins["camant5"] . "'", "nombre") . ' el ' . strtolower(\funcionesGenerales::mostrarFechaLetras1($ins["fecant5"]));
                                            }
                                        }
                                    }
                                }
                                $txt .= ', ';
                            } else {
                                $txt .= ' ';
                            }
                            $txt .= 'se registró el acto administrativo No. ' . $ndocx . ' de ' . strtolower(\funcionesGenerales::mostrarFechaLetras1($ins["fdoc"])) . ', expedido por ';
                            $txt .= ucwords(strtolower($ins["txoridoc"])) . ', que lo habilita para prestar el servicio ';
                            $txt .= 'público de transporte terrestre automotor especial.';
                            $retorno["habilitacionesespeciales"][] = $txt;
                        } else {
                            $txt = 'Mediante inscripción No. ' . $ins["nreg"] . ' DE FECHA ' . strtolower(\funcionesGenerales::mostrarFechaLetras1($ins["freg"]));
                            if ($ins["camant"] != '') {
                                $txt .= ', inscrito previamente en la ' . retornarRegistroMysqliApi($mysqli, 'bas_camaras', "id='" . $ins["camant"] . "'", "nombre") . ' el ' . strtolower(\funcionesGenerales::mostrarFechaLetras1($ins["fecant"]));
                                if ($ins["camant2"] != '') {
                                    $txt .= ', inscrito previamente en la ' . retornarRegistroMysqliApi($mysqli, 'bas_camaras', "id='" . $ins["camant2"] . "'", "nombre") . ' el ' . strtolower(\funcionesGenerales::mostrarFechaLetras1($ins["fecant2"]));
                                    if ($ins["camant3"] != '') {
                                        $txt .= ', inscrito previamente en la ' . retornarRegistroMysqliApi($mysqli, 'bas_camaras', "id='" . $ins["camant3"] . "'", "nombre") . ' el ' . strtolower(\funcionesGenerales::mostrarFechaLetras1($ins["fecant3"]));
                                        if ($ins["camant4"] != '') {
                                            $txt .= ', inscrito previamente en la ' . retornarRegistroMysqliApi($mysqli, 'bas_camaras', "id='" . $ins["camant4"] . "'", "nombre") . ' el ' . strtolower(\funcionesGenerales::mostrarFechaLetras1($ins["fecant4"]));
                                            if ($ins["camant5"] != '') {
                                                $txt .= ', inscrito previamente en la ' . retornarRegistroMysqliApi($mysqli, 'bas_camaras', "id='" . $ins["camant5"] . "'", "nombre") . ' el ' . strtolower(\funcionesGenerales::mostrarFechaLetras1($ins["fecant5"]));
                                            }
                                        }
                                    }
                                }
                                $txt .= ', ';
                            } else {
                                $txt .= ' ';
                            }
                            $txt .= 'se registró el acto administrativo No. ' . $ndocx . ' de ' . strtolower(\funcionesGenerales::mostrarFechaLetras1($ins["fdoc"])) . ' ';
                            $txt .= 'que lo habilita para prestar el servicio ';
                            $txt .= 'público de transporte terrestre automotor especial.';
                            $retorno["habilitacionesespeciales"][] = $txt;
                        }
                    }
                }
            }
        }

        //
        if ($estransportecarga == 'falta') {
            foreach ($_SESSION["maestrocertificas"] as $cx) {
                if ($cx["clase"] == 'CRT-TRACAR') {
                    if (isset($retorno["crtsii"][$cx["id"]]) && $retorno["crtsii"][$cx["id"]] != '') {
                        $retorno["habilitacionesespeciales"][] = $retorno["crtsii"][$cx["id"]];
                        $estransportecarga = 'encontro';
                    }
                }
            }
            if ($estransportecarga == 'falta') {
                if ($retorno["organizacion"] == '01') {
                    $txt = 'La persona natural ';
                } else {
                    $txt = 'La persona jurídica ';
                }
                $txt .= 'no ha inscrito el acto administrativo que lo habilita para prestar el servicio público de transporte automotor en la modalidad de carga.';
                $retorno["habilitacionesespeciales"][] = $txt;
            }
        }

        //
        if ($certificargrupo093 == 'encontro') {
            foreach ($retorno["inscripciones"] as $ins) {
                if ($ins["grupoacto"] == '093') {
                    if ($ins["freg"] < $_SESSION["generales"]["FECHA_CORTE_REVOCACION"] && $ins["crev"] == '1') {
                        $ins["crev"] = '0';
                    }

                    //
                    $imp = 'no';
                    if (trim((string) $ins["flim"])) {
                        $imp = 'si';
                    } else {
                        if (trim((string) $ins["flim"]) != '' && $ins["flim"] < date("Ymd")) {
                            $imp = 'si';
                        }
                    }

                    //
                    if ($imp == 'si') {
                        if ($ins["crev"] != '1' && $ins["crev"] != '9') {
                            if (trim($ins["ndocext"]) != '') {
                                $ndocx = $ins["ndocext"];
                            } else {
                                $ndocx = $ins["ndoc"];
                            }
                            if (trim($ins["txoridoc"]) != '') {
                                $txt = 'Mediante inscripción No. ' . $ins["nreg"] . ' de ' . strtolower(\funcionesGenerales::mostrarFechaLetras1($ins["freg"]));
                                if ($ins["camant"] != '') {
                                    $txt .= ', inscrito previamente en la ' . retornarRegistroMysqliApi($mysqli, 'bas_camaras', "id='" . $ins["camant"] . "'", "nombre") . ' el ' . strtolower(\funcionesGenerales::mostrarFechaLetras1($ins["fecant"]));
                                    if ($ins["camant2"] != '') {
                                        $txt .= ', inscrito previamente en la ' . retornarRegistroMysqliApi($mysqli, 'bas_camaras', "id='" . $ins["camant2"] . "'", "nombre") . ' el ' . strtolower(\funcionesGenerales::mostrarFechaLetras1($ins["fecant2"]));
                                        if ($ins["camant3"] != '') {
                                            $txt .= ', inscrito previamente en la ' . retornarRegistroMysqliApi($mysqli, 'bas_camaras', "id='" . $ins["camant3"] . "'", "nombre") . ' el ' . strtolower(\funcionesGenerales::mostrarFechaLetras1($ins["fecant3"]));
                                            if ($ins["camant4"] != '') {
                                                $txt .= ', inscrito previamente en la ' . retornarRegistroMysqliApi($mysqli, 'bas_camaras', "id='" . $ins["camant4"] . "'", "nombre") . ' el ' . strtolower(\funcionesGenerales::mostrarFechaLetras1($ins["fecant4"]));
                                                if ($ins["camant5"] != '') {
                                                    $txt .= ', inscrito previamente en la ' . retornarRegistroMysqliApi($mysqli, 'bas_camaras', "id='" . $ins["camant5"] . "'", "nombre") . ' el ' . strtolower(\funcionesGenerales::mostrarFechaLetras1($ins["fecant5"]));
                                                }
                                            }
                                        }
                                    }
                                    $txt .= ', ';
                                } else {
                                    $txt .= ' ';
                                }
                                $txt .= 'se registró el acto administrativo No. ' . $ndocx . ' de ' . strtolower(\funcionesGenerales::mostrarFechaLetras1($ins["fdoc"])) . ', expedido por ';
                                $txt .= ucwords(strtolower($ins["txoridoc"])) . ',  a través del cual se ' . $ins["not"];
                                $retorno["habilitacionesespeciales"][] = $txt;
                            } else {
                                $txt = 'Mediante inscripción No. ' . $ins["nreg"] . ' DE FECHA ' . strtolower(\funcionesGenerales::mostrarFechaLetras1($ins["freg"]));
                                if ($ins["camant"] != '') {
                                    $txt .= ', inscrito previamente en la ' . retornarRegistroMysqliApi($mysqli, 'bas_camaras', "id='" . $ins["camant"] . "'", "nombre") . ' el ' . strtolower(\funcionesGenerales::mostrarFechaLetras1($ins["fecant"]));
                                    if ($ins["camant2"] != '') {
                                        $txt .= ', inscrito previamente en la ' . retornarRegistroMysqliApi($mysqli, 'bas_camaras', "id='" . $ins["camant2"] . "'", "nombre") . ' el ' . strtolower(\funcionesGenerales::mostrarFechaLetras1($ins["fecant2"]));
                                        if ($ins["camant3"] != '') {
                                            $txt .= ', inscrito previamente en la ' . retornarRegistroMysqliApi($mysqli, 'bas_camaras', "id='" . $ins["camant3"] . "'", "nombre") . ' el ' . strtolower(\funcionesGenerales::mostrarFechaLetras1($ins["fecant3"]));
                                            if ($ins["camant4"] != '') {
                                                $txt .= ', inscrito previamente en la ' . retornarRegistroMysqliApi($mysqli, 'bas_camaras', "id='" . $ins["camant4"] . "'", "nombre") . ' el ' . strtolower(\funcionesGenerales::mostrarFechaLetras1($ins["fecant4"]));
                                                if ($ins["camant5"] != '') {
                                                    $txt .= ', inscrito previamente en la ' . retornarRegistroMysqliApi($mysqli, 'bas_camaras', "id='" . $ins["camant5"] . "'", "nombre") . ' el ' . strtolower(\funcionesGenerales::mostrarFechaLetras1($ins["fecant5"]));
                                                }
                                            }
                                        }
                                    }
                                    $txt .= ', ';
                                } else {
                                    $txt .= ' ';
                                }
                                $txt .= 'se registró el acto administrativo No. ' . $ndocx . ' de ' . strtolower(\funcionesGenerales::mostrarFechaLetras1($ins["fdoc"])) . ' ';
                                $txt .= 'a través del cual se ' . $ins["not"];
                                $retorno["habilitacionesespeciales"][] = $txt;
                            }
                        }
                    }
                }
            }
        }

        $retorno["nombrebase64decodificado"] = $retorno["nombre"];
        $retorno["nombrebase64"] = base64_encode($retorno["nombre"]);
        $retorno["siglabase64decodificada"] = $retorno["sigla"];
        $retorno["siglabase64"] = base64_encode($retorno["sigla"]);

        // Cierra la conexión con MYSQL
        if ($dbx === null) {
            $mysqli->close();
        }

        //
        if ($genlog == 'si') {
            \logApi::general2($nameLog, $retorno["matricula"], 'termino lectura expediente');
        }

        //
        if (!isset($reg["hashcontrol"])) {
            $reg["hashcontrol"] = '';
        }
        $retorno["hashcontrol"] = $reg["hashcontrol"];

        // ********************************************************************************************************************** //
        // Calculo del hash de control nuevo
        // ********************************************************************************************************************** //
        $retorno["hashcontrolnuevo"] = date("Ymd") . '|' . \funcionesRegistrales::calcularHashMercantil($mysqli, $retorno["matricula"], $retorno);

        //
        return $retorno;
    }

}

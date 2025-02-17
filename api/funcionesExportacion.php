<?php

class funcionesExportacion {

    public static function exportarJsonMatriculado($dbx, $mat) {
        require_once($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common' . $_SESSION["generales"]["codigoempresa"] . '.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistralesCalculos.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRues.php');

        $aMat = \funcionesRegistrales::retornarExpedienteMercantil($dbx, $mat);
        if ($aMat === false || empty($aMat)) {
            return false;
        }

        $json = array();

        //
        $json["camara"] = CODIGO_EMPRESA;
        $json["matricula"] = $mat;
        $json["proponente"] = $aMat["proponente"];
        $json["nombre"] = $aMat["nombre"];
        if ($aMat["organizacion"] > '01') {
            $json["ape1"] = '';
            $json["ape2"] = '';
            $json["nom1"] = '';
            $json["nom2"] = '';
        } else {
            $json["ape1"] = $aMat["ape1"];
            $json["ape2"] = $aMat["ape2"];
            $json["nom1"] = $aMat["nom1"];
            $json["nom2"] = $aMat["nom2"];
        }
        $json["sigla"] = $aMat["sigla"];
        $json["nombrecomercial"] = '';

        $json["idtipoidentificacion"] = \funcionesRues::homologarTipoIdentificacion($dbx, $aMat["tipoidentificacion"]);
        $json["identificacion"] = '';
        if ($aMat["tipoidentificacion"] == '2') {
            if (trim($aMat["identificacion"]) != '') {
                $sepide = \funcionesGenerales::separarDv($aMat["identificacion"]);
                $json["identificacion"] = $sepide["identificacion"];
            }
        } else {
            $json["identificacion"] = $aMat["identificacion"];
        }
        if ($aMat["organizacion"] == '01') {
            $json["idmunidoc"] = $aMat["idmunidoc"];
            $json["fechaexpdoc"] = $aMat["fechaexpdoc"];
            $json["paisexpdoc"] = $aMat["paisexpdoc"];
        } else {
            $json["idmunidoc"] = '';
            $json["fechaexpdoc"] = '';
            $json["paisexpdoc"] = '';
        }
        if ($aMat["nit"] != '') {
            $sepide = \funcionesGenerales::separarDv($aMat["nit"]);
            $json["nit"] = $sepide["identificacion"];
            $json["dv"] = $sepide["dv"];
        } else {
            $json["nit"] = '';
            $json["dv"] = '';
        }
        if ($aMat["organizacion"] == '01') {
            $json["nacionalidad"] = $aMat["nacionalidad"];
            $json["genero"] = $aMat["sexo"];
        } else {
            $json["nacionalidad"] = '';
            $json["genero"] = '';
        }
        $json["indicadorempresabic"] = '';
        if ($aMat["organizacion"] > '02' && $aMat["categoria"] == '1' && $aMat["organizacion"] != '12' && $aMat["organizacion"] != '14') {
            if ($aMat["ctrbic"] != '') {
                $json["indicadorempresabic"] = $aMat["ctrbic"];
            }
        }

        //
        $json["numidetribpaisorigen"] = $aMat["idetripaiori"];
        $json["numidetribextranjeroep"] = $aMat["idetriextep"];
        $json["fechamatricula"] = $aMat["fechamatricula"];
        $json["fecharenovacion"] = $aMat["fecharenovacion"];
        $json["fechaconstitucion"] = $aMat["fechaconstitucion"];
        $json["fechavigencia"] = $aMat["fechavencimiento"];
        if ($json["fechavigencia"] == '99999999') {
            $json["fechavigencia"] = '99991231';
        }
        $json["fechacancelacion"] = $aMat["fechacancelacion"];
        $json["motivocancelacion"] = '00';
        $json["fechadisolucion"] = $aMat["fechadisolucion"];
        $json["fechaliquidacion"] = $aMat["fechaliquidacion"];
        $json["ultanoren"] = $aMat["ultanoren"];
        $json["estadomatricula"] = \funcionesRues::homologarEstadoMatricula($dbx, $aMat["estadomatricula"]);
        $json["organizacioo"] = \funcionesRues::homologarOrganizacionMatricula($dbx, $aMat["organizacion"], $aMat["categoria"], $aMat["clasegenesadl"], $aMat["claseespesadl"], $aMat["claseeconsoli"]);
        $json["categoria"] = \funcionesRues::homologarCategoriaMatricula($dbx, $aMat["organizacion"], $aMat["categoria"]);
        if ($aMat["organizacion"] > '02' && $aMat["categoria"] == '1') {
            $json["cantidadmujerescargosdirectivos"] = $aMat["cantidadmujerescargosdirectivos"];
        } else {
            $json["cantidadmujerescargosdirectivos"] = 0;
        }
        if (($aMat["organizacion"] == '01' || $aMat["organizacion"] > '02' && $aMat["categoria"] == '1')) {
            $json["cantidadmujeresempleadas"] = $aMat["cantidadmujeres"];
        } else {
            $json["cantidadmujeresempleadas"] = 0;
        }

        $expe["ctrbeneficioarticulo4"] = $aMat["art4"];
        $expe["ctrbeneficioarticulo7"] = $aMat["art7"];
        $expe["ctrbeneficioarticulo50"] = $aMat["art50"];
        $expe["ctrcancelacionley1429depuracion"] = $aMat["ctrcancelacion1429"];
        $expe["ctrbeneficioLey1780"] = $aMat["benley1780"];
        $expe["ctrcumplerequisitosley1780"] = $aMat["cumplerequisitos1780"];
        $expe["ctrrenunciabeneficiosley1780"] = $aMat["renunciabeneficios1780"];
        $expe["ctrcumplerequisitosley1780primrenovacion"] = $aMat["cumplerequisitos1780primren"];
        $expe["ctrdepuracion1727"] = $aMat["ctrdepuracion1727"]; // Campos nuevos sugeridos
        $expe["ctrfechadepuracion1727"] = $aMat["ctrfechadepuracion1727"]; // Campos nuevos sugeridos        
        $json["ctraportante"] = $aMat["aportantesegsocial"];
        $json["ctrtipoaportante"] = $aMat["tipoaportantesegsocial"];
        $json["tamanoempresa"] = $aMat["tamanoempresarial957codigo"];
        $json["emprendedor28"] = '';
        $json["pemprendedor28"] = '';
        $json["impexp"] = $aMat["impexp"];
        $json["tipopropiedad"] = $aMat["tipopropiedad"];
        $json["tipolocal"] = $aMat["tipolocal"];
        $json["empresafamiliar"] = $aMat["empresafamiliar"];
        $json["procesosinnovacion"] = $aMat["procesosinnovacion"];
        $json["ctrubicacion"] = $aMat["ctrubi"];
        $json["ctrfuncionamiento"] = $aMat["ctrfun"];
        $json["tiposedeadm"] = $aMat["tiposedeadm"];
        $json["estadoactualpjur"] = '';
        $json["fechaperj"] = $aMat["fecperj"];
        $json["txtorigenperj"] = $aMat["origendocconst"];
        $json["numperj"] = $aMat["numperj"];
        $json["vigcontrol"] = $aMat["vigcontrol"];
        $json["vigifechaini"] = $aMat["vigifecini"];
        $json["vigifechafin"] = $aMat["vigifecfin"];
        $json["claseeconsoli"] = $aMat["claseeconsoli"];
        $json["cntasociados"] = $aMat["ctresacntasociados"];
        $json["cntmujeres"] = $aMat["ctresacntmujeres"];
        $json["cnthombres"] = $aMat["ctresacnthombres"];
        $json["pertgremio"] = $aMat["ctresapertgremio"];
        $json["nomgremio"] = $aMat["ctresagremio"];
        $json["entidadacreditadacursoeconsol"] = $aMat["ctresaacredita"];
        $json["ivcnombre"] = $aMat["ctresainfoivc"];
        $json["remisioninfoivc"] = $aMat["ctresaivc"];
        $json["ctrautorizaregistro"] = $aMat["ctresaautregistro"];
        $json["entautorizaregistro"] = $aMat["ctresaentautoriza"];
        $json["codnaturaleza"] = $aMat["ctresacodnat"];
        $json["ctrdiscapacitados"] = $aMat["ctresadiscap"];
        $json["ctretnia"] = $aMat["ctresaetnia"];
        $json["cualetnia"] = $aMat["ctresacualetnia"];
        $json["ctrdesvicreins"] = $aMat["ctresadespvictreins"];
        $json["ctrdesvicreinscual"] = $aMat["ctresacualdespvictreins"];
        $json["ctrindgestion"] = $aMat["ctresaindgest"];
        $json["ctrlgbti"] = $aMat["ctresalgbti"];
        $json["ctrafiliacion"] = $aMat["afiliado"];
        $json["dircom"] = $aMat["dircom"];
        $json["muncom"] = $aMat["muncom"];
        $json["telcom1"] = $aMat["telcom1"];
        $json["telcom2"] = $aMat["telcom2"];
        $json["telcom3"] = $aMat["celcom"];
        $json["emailcom"] = $aMat["emailcom"];
        $json["zonacom"] = $aMat["codigozonacom"];
        $json["codposcom"] = $aMat["codigopostalcom"];
        $json["ubicacioncom"] = $aMat["ctrubi"];
        $json["barriocom"] = $aMat["barriocomnombre"];
        $json["dirnot"] = $aMat["dirnot"];
        $json["barrionot"] = '';
        $json["munnot"] = $aMat["munnot"];
        $json["paisnot"] = $aMat["painot"];
        $json["telnot1"] = $aMat["telnot"];
        $json["telnot2"] = $aMat["telnot2"];
        $json["telnot3"] = $aMat["celnot"];
        $json["emailnot"] = $aMat["emailnot"];
        $json["zonanot"] = $aMat["codigozonanot"];
        $json["codposnot"] = $aMat["codigopostalnot"];
        $json["ubicacionnot"] = '';
        $json["autorizonotifemail"] = $aMat["ctrmennot"];

        $json["ciiu1"] = '';
        $json["ciiu2"] = '';
        $json["ciiu3"] = '';
        $json["ciiu4"] = '';
        $json["fechainiciociiu1"] = '';
        $json["fechainiciociiu2"] = '';

        if (trim($aMat["ciius"][1]) != '') {
            $json["ciiu1"] = substr($aMat["ciius"][1], 1);
        }
        if (trim($aMat["ciius"][2]) != '') {
            $json["ciiu2"] = substr($aMat["ciius"][2], 1);
        }
        if (trim($aMat["ciius"][3]) != '') {
            $json["ciiu3"] = substr($aMat["ciius"][3], 1);
        }
        if (trim($aMat["ciius"][4]) != '') {
            $json["ciiu4"] = substr($aMat["ciius"][4], 1);
        }
        $json["fechainiciociiu1"] = $aMat["feciniact1"];
        if (trim($json["ciiu2"]) != '') {
            $json["fechainiciociiu2"] = $aMat["feciniact2"];
        }

        $json["descripcionactividad"] = '';
        if ($aMat["organizacion"] == '01' || $aMat["organizacion"] == '02' || $aMat["categoria"] == '2' || $aMat["categoria"] == '3') {
            $json["descripcionactividad"] = base64_encode(strip_tags($aMat["desactiv"]));
        }

        $json["ciiumayoresingresos"] = '';
        if ($aMat["organizacion"] == '01' || ($aMat["organizacion"] > '02' && $aMat["categoria"] == '1')) {
            if ($aMat["ciiutamanoempresarial"] != '') {
                $json["ciiumayoresingresos"] = substr($aMat["ciiutamanoempresarial"], 1);
            }
        }

        if (isset($aMat["crtsii"]["1300"])) {
            $json["facultades"] = base64_encode(strip_tags(trim($aMat["crtsii"]["1300"])));
        } else {
            $json["facultades"] = '';
        }

        //
        // Ojo solo los últimos 5 años.
        $json["informacionfinanciera"] = array();
        $json["informacionfinanciera"]["infofinanciera"] = array();
        $temfin = array();
        foreach ($aMat["hf"] as $regf) {
            if ($regf["anodatos"] >= date("Y") - 4) {
                $temfin[$regf["anodatos"]] = $regf;
            }
        }

        foreach ($temfin as $regf) {
            $infoFinanciera = array();
            $infoFinanciera["anodatos"] = $regf["anodatos"];
            $infoFinanciera["fechadatos"] = $regf["fechadatos"];
            $infoFinanciera["personal"] = $regf["personal"];
            $infoFinanciera["personaltempporc"] = $regf["pcttemp"];
            $infoFinanciera["actvin"] = $regf["actvin"];
            $infoFinanciera["actcte"] = $regf["actcte"];
            $infoFinanciera["actnocte"] = $regf["actnocte"];
            $infoFinanciera["acttot"] = $regf["acttot"];
            $infoFinanciera["pascte"] = $regf["pascte"];
            $infoFinanciera["pasnocte"] = $regf["paslar"];
            $infoFinanciera["pastot"] = $regf["pastot"];
            $infoFinanciera["patnet"] = $regf["pattot"];
            $infoFinanciera["paspat"] = $regf["paspat"];
            $infoFinanciera["balsoc"] = $regf["balsoc"];
            $infoFinanciera["ingope"] = $regf["ingope"];
            $infoFinanciera["ingnoope"] = $regf["ingnoope"];
            $infoFinanciera["gasope"] = $regf["gtoven"];
            $infoFinanciera["gasnoope"] = $regf["gtoadm"];
            $infoFinanciera["cosven"] = $regf["cosven"];
            $infoFinanciera["gasimp"] = $regf["gasimp"];
            $infoFinanciera["utiope"] = $regf["utiope"];
            $infoFinanciera["utinet"] = $regf["utinet"];
            $infoFinanciera["gruponiif"] = $aMat["gruponiif"];
            $infoFinanciera["pornalpub"] = $aMat["cap_porcnalpub"];
            $infoFinanciera["pornalpri"] = $aMat["cap_porcnalpri"];
            $infoFinanciera["porextpub"] = $aMat["cap_porcextpub"];
            $infoFinanciera["porextpri"] = $aMat["cap_porcextpri"];
            $json["informacionfinanciera"]["infofinanciera"][] = $infoFinanciera;
        }

        //
        $json["vinculos"] = array();
        $json["vinculos"]["vinculo"] = array();
        if (!empty($aMat["vinculos"])) {
            foreach ($aMat["vinculos"] as $r) {
                $rx = array();
                $rx["tipovinculo"] = \funcionesRues::homologarTipoVinculosMatricula($dbx, $r["vinculootros"]);
                $rx["tipovinculosinhomologar"] = $r["vinculootros"];
                $rx["descripciontipovinculosinhomologar"] = retornarRegistroMysqliApi($dbx, 'mreg_codvinculos', "id='" . $r["vinculootros"] . "'", "descripcion");
                $rx["idtipoidentificacion"] = \funcionesRues::homologarTipoIdentificacion($dbx, $r["idtipoidentificacionotros"]);
                $rx["identificacion"] = $r["identificacionotros"];
                $rx["nombre_razon_social"] = $r["nombreotros"];
                $rx["ape1"] = $r["apellido1otros"];
                $rx["ape2"] = $r["apellido2otros"];
                $rx["nom1"] = $r["nombre1otros"];
                $rx["nom2"] = $r["nombre2otros"];
                $rx["fechanacimiento"] = $r["fechanacimientootros"];
                $rx["fechaexpdoc"] = '';
                $rx["cargo"] = '';
                if (trim($r["cargootros"]) != '') {
                    $rx["cargo"] = $r["cargootros"];
                } else {
                    if (ltrim(trim($r["idcargootros"]), "0") != '') {
                        $rx["cargo"] = retornarRegistroMysqliApi($dbx, 'mreg_tablassirep', "idtabla='14' and idcodigo='" . $r["idcargootros"] . "'", "descripcion");
                    } else {
                        $rx["cargo"] = retornarRegistroMysqliApi($dbx, 'mreg_codvinculos', "id='" . $r["vinculootros"] . "'", "descripcion");
                    }
                }
                $rx["renglon"] = '';
                $rx["nitrepresenta"] = '';
                if (trim($r["numidemp"]) != '') {
                    $rx["nitrepresenta"] = trim($r["numidemp"]);
                }
                $rx["libroregistro"] = $r["librootros"];
                $rx["numeroregistro"] = $r["inscripcionotros"];
                $rx["dupliregistro"] = $r["dupliotros"];
                $rx["fecharegistro"] = $r["fechaotros"];
                $rx["numerocuotas"] = $r["cuotasconst"];
                if ($r["cuotasref"] != 0) {
                    $rx["numerocuotas"] = $r["cuotasref"];
                }
                $rx["valorcuotas"] = $r["valorconst"];
                if ($r["valorref"] != 0) {
                    $rx["valorcuotas"] = $r["valorref"];
                }
                $rx["apolab"] = $r["va1"];
                if ($r["va5"] != 0) {
                    $rx["apolab"] = $r["va5"];
                }
                $rx["apolabadi"] = $r["va2"];
                if ($r["va6"] != 0) {
                    $rx["apolabadi"] = $r["va6"];
                }
                $rx["apoact"] = $r["va4"];
                if ($r["va8"] != 0) {
                    $rx["apoact"] = $r["va8"];
                }
                $rx["apodin"] = $r["va3"];
                if ($r["va7"] != 0) {
                    $rx["apodin"] = $r["va7"];
                }
                $json["vinculos"]["vinculo"][] = $rx;
            }
        }

        // Ojo solo el último, no es un arreglo
        $json["capitales"] = array();
        $json["capitales"]["capital"] = array();
        if ($aMat["organizacion"] > '02' && $aMat["categoria"] == '1' && $aMat["organizacion"] != '12' && $aMat["organizacion"] != '14') {
            if (!empty($aMat["capitales"])) {
                foreach ($aMat["capitales"] as $r) {
                    $rx = array();
                    $rx["libroregistro"] = $r["libro"];
                    $rx["numeroregistro"] = $r["registro"];
                    $rx["fechadatos"] = $r["fechadatos"];
                    $rx["anodatos"] = $r["anodatos"];
                    $rx["cuotascapsocial"] = $r["cuosocial"];
                    $rx["valorcapsocial"] = $r["social"];
                    $rx["cuotascapaut"] = $r["cuoautorizado"];
                    $rx["valorcapaut"] = $r["autorizado"];
                    $rx["cuotascapsus"] = $r["cuosuscrito"];
                    $rx["valorcapsus"] = $r["suscrito"];
                    $rx["cuotascappag"] = $r["cuopagado"];
                    $rx["valorcappag"] = $r["pagado"];
                    $rx["apobal"] = $r["apolab"];
                    $rx["apolabadi"] = $r["apolabadi"];
                    $rx["apoact"] = $r["apoact"];
                    $rx["apodin"] = $r["apodin"];
                    $rx["apotot"] = strval($r["apolab"] + $r["apolabadi"] + $r["apoact"] + $r["apodin"]);
                    $rx["patrimonio"] = 0;
                    $rx["porcentajeparticipacionmujeres"] = $aMat["participacionmujeres"];
                    // $rx["valornominal"] = null;
                    $json["capitales"]["capital"] = $rx;
                }
            }
        }
        if ($aMat["categoria"] == '1' && ($aMat["organizacion"] == '12' || $aMat["organizacion"] == '14')) {
            if (!empty($aMat["capitales"])) {
                foreach ($aMat["capitales"] as $r) {
                    $rx = array();
                    $rx["libroregistro"] = null;
                    $rx["numeroregistro"] = null;
                    $rx["fechadatos"] = $r["fechadatos"];
                    $rx["anodatos"] = $r["anodatos"];
                    $rx["cuotascapsocial"] = null;
                    $rx["valorcapsocial"] = null;
                    $rx["cuotascapaut"] = null;
                    $rx["valorcapaut"] = null;
                    $rx["cuotascapsus"] = null;
                    $rx["valorcapsus"] = null;
                    $rx["cuotascappag"] = null;
                    $rx["valorcappag"] = null;
                    $rx["apobal"] = null;
                    $rx["apolabadi"] = null;
                    $rx["apoact"] = null;
                    $rx["apodin"] = null;
                    $rx["apotot"] = null;
                    $rx["patrimonio"] = $r["patrimonio"];
                    $rx["porcentajeparticipacionmujeres"] = null;
                    $json["capitales"]["capital"] = $rx;
                }
            }
        }

        /*
          $json["propietarios"] = array();
          $json["propietarios"]["propietario"] = array();
          foreach ($aMat["propietarios"] as $prop) {
          $propietario = array();
          $propietario["camara"] = $prop["camarapropietario"];
          $propietario["matricula"] = $prop["matriculapropietario"];
          $propietario["idtipoidentificacion"] = \funcionesRues::homologarTipoIdentificacion($mysqli, $prop["idtipoidentificacionpropietario"]);
          $propietario["identificacion"] = $prop["identificacionpropietario"];
          $propietario["nombre"] = $prop["nombrepropietario"];
          $propietario["nom1"] = $prop["nom1propietario"];
          $propietario["nom2"] = $prop["nom2propietario"];
          $propietario["ape1"] = $prop["ape1propietario"];
          $propietario["ape2"] = $prop["ape2propietario"];
          $propietario["dircom"] = $prop["direccionpropietario"];
          $propietario["muncom"] = $prop["municipiopropietario"];
          $propietario["telcom1"] = $prop["telefonopropietario"];
          $propietario["telcom2"] = $prop["telefono2propietario"];
          $propietario["emailcom"] = '';
          $json["propietarios"]["propietario"][] = $propietario;
          }

          //
          $json["casasPrincipales"] = array();
          $json["casasPrincipales"]["casaPrincipal"] = array();
          $casaPrincipal = array();
          $casaPrincipal["cpcamara"] = $aMat["cpcodcam"];
          $casaPrincipal["cpmatricula"] = $aMat["cpnummat"];
          $casaPrincipal["cpnit"] = $aMat["cpnumnit"];
          $casaPrincipal["cprazonsocial"] = $aMat["cprazsoc"];
          $casaPrincipal["cpdircom"] = $aMat["cpdircom"];
          $casaPrincipal["cpmuncom"] = $aMat["cpcodmun"];
          $casaPrincipal["cptelcom1"] = $aMat["cpnumtel"];
          $casaPrincipal["cptelcom2"] = $aMat["cpnumtel2"];
          $casaPrincipal["cptelcom3"] = $aMat["cpnumtel3"];
          $casaPrincipal["cpemailcom"] = '';
          $json["casasPrincipales"] ["casaPrincipal"] = $casaPrincipal;
         */

        //
        $json["certificas"] = array();
        $json["certificas"]["certifica"] = array();
        foreach ($aMat["crtsii"] as $key => $crt) {
            $crtx = array();
            $crtx["codigo"] = \funcionesRues::homologarCertificasMatricula($dbx, $key);
            $crtx["texto"] = base64_encode(strip_tags($crt));
            $json["certificas"]["certifica"][] = $crtx;
        }

        //
        $json["libroscomercio"] = array();
        $json["libroscomercio"]["libro"] = array();
        if (!empty($aMat["inscripcioneslibros"])) {
            foreach ($aMat["inscripcioneslibros"] as $r) {
                $rx = array();
                $rx["libroregistro"] = $r["lib"];
                $rx["numeroregistro"] = $r["nreg"];
                $rx["dupli"] = $r["dupli"];
                $rx["fecharegistro"] = $r["freg"];
                $rx["codigolibro"] = $r["codlibcom"];
                $rx["descripcionlibro"] = $r["deslib"];
                if (trim($rx["descripcionlibro"]) == '') {
                    $rx["descripcionlibro"] = retornarRegistroMysqliApi($dbx, 'mreg_tablassirep', "idtabla='09' and idcodigo='" . $r["idlibvii"] . "'", "descripcion");
                }
                $rx["paginainicial"] = $r["paginainicial"];
                $rx["totalpaginas"] = $r["numhojas"];
                $json["libroscomercio"]["libro"][] = $rx;
            }
        }

        //
        $json["embargos"] = array();
        $json["embargos"]["embargo"] = array();
        if (!empty($aMat["ctrembargos"])) {
            foreach ($aMat["ctrembargos"] as $r) {
                $rx = array();
                $rx["libroregistro"] = $r["libro"];
                $rx["numeroregistro"] = $r["numreg"];
                $rx["fecharegistro"] = $r["fecinscripcion"];
                $rx["tipo"] = '01'; // Homologar
                $rx["matricula"] = $aMat["matricula"];
                $rx["idtipoidentificaciondemandante"] = null;
                $rx["identificaciondemandante"] = null;
                $rx["nombredemandante"] = null;
                $rx["noticia"] = base64_encode($r["noticia"]);
                $json["embargos"]["embargo"][] = $rx;
            }
        }

        //
        $json["infoinscripciones"] = array();
        $json["infoinscripciones"]["kardex"] = array();
        if (!empty($aMat["inscripciones"])) {
            foreach ($aMat["inscripciones"] as $r) {
                $rx = array();
                $rx["libroregistro"] = $r["lib"];
                $rx["numeroregistro"] = $r["nreg"];
                $rx["dupli"] = $r["dupli"];
                $rx["fecharegistro"] = $r["freg"];
                $rx["actogenerico"] = \funcionesRues::homologarActosGenericosMatricula($dbx, $r["grupoacto"]);
                $rx["descripcionacto"] = retornarNombreActosRegistroMysqliApi($dbx, $r["lib"], $r["acto"]);
                $rx["idtipodoc"] = \funcionesRues::homologarTiposDocumentales($dbx, $r["tdoc"]); // Homologar
                $rx["fechadoc"] = $r["fdoc"];
                $rx["txtorigendoc"] = $r["txoridoc"];
                $rx["numdoc"] = $r["ndoc"];
                $rx["noticia"] = base64_encode($r["not"]);
                $rx["ctrrevocacion"] = $r["crev"];
                $rx["numeroregistrorevocacion"] = $r["regrev"];
                $rx["fecharegistrorevocacion"] = null;
                $rx["camaraanterior"] = $r["camant"];
                $rx["librocamaraanterior"] = $r["libant"];
                $rx["numeroregistrocamaraanterior"] = $r["regant"];
                $rx["fecharegistrocamaraanterior"] = $r["fecant"];
                $json["infoinscripciones"]["kardex"][] = $rx;
            }
        }

        //
        return json_encode($json);
    }

    public static function exportarJsonProponente($dbx, $prop) {
        require_once($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common' . $_SESSION["generales"]["codigoempresa"] . '.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistralesCalculos.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRues.php');

        $aProp = \funcionesRegistrales::retornarExpedienteProponente($dbx, $prop);
        if ($aProp === false || empty($aProp)) {
            return false;
        }

        $json = array();

        //
        $json["camara"] = CODIGO_EMPRESA;
        $json["proponente"] = $prop;
        $json["matricula"] = $aProp["matricula"];
        $json["razonsocial"] = $aProp["nombre"];
        $json["ape1"] = $aProp["ape1"];
        $json["ape2"] = $aProp["ape2"];
        $json["nom1"] = $aProp["nom1"];
        $json["nom2"] = $aProp["nom2"];
        $json["sigla"] = $aProp["sigla"];
        $json["tipoidentificacion"] = \funcionesRues::homologarTipoIdentificacion($dbx, $aProp["idtipoidentificacion"]);
        $json["identificacion"] = $aProp["identificacion"];
        $json["idpaisidentificacion"] = '0169';
        $sepide = \funcionesGenerales::separarDv($aProp["nit"]);
        $json["nit"] = $sepide["identificacion"];
        $json["dv"] = $sepide["dv"];
        $json["numidetripaisorigenep"] = '';
        $json["organizacion"] = $aProp["organizacion"];
        $json["tamanoempresa"] = $aProp["tamanoempresa"];
        $json["fechaultimainscripcion"] = $aProp["fechaultimainscripcion"];
        $json["fechaultimarenovacion"] = $aProp["fechaultimarenovacion"];

        //
        $json["dircom"] = $aProp["dircom"];
        $json["ubicom"] = '';
        $json["barriocom"] = '';
        $json["muncom"] = $aProp["muncom"];
        $json["telcom1"] = $aProp["telcom1"];
        $json["telcom2"] = $aProp["telcom2"];
        $json["telcom3"] = $aProp["celcom"];
        $json["emailcom"] = $aProp["emailcom"];
        $json["zonacom"] = $aProp["codigozonacom"];
        $json["codposcom"] = $aProp["codigopostalcom"];

        //
        $json["dirnot"] = $aProp["dirnot"];
        $json["barrionot"] = '';
        $json["munnot"] = $aProp["munnot"];
        $json["paisnot"] = $aProp["painot"];
        $json["telnot1"] = $aProp["telnot"];
        $json["telnot2"] = $aProp["telnot2"];
        $json["telnot3"] = $aProp["celnot"];
        $json["emailnot"] = $aProp["emailnot"];
        $json["zonanot"] = $aProp["codigozonanot"];
        $json["codposnot"] = $aProp["codigopostalnot"];
        $json["tiposede"] = '';

        //
        $json["facultades"] = strip_tags(stripslashes($aProp["facultades"]));

        //
        $json["representacionlegal"] = array();
        $json["representacionlegal"]["representante"] = array();
        if (!empty($aProp["representanteslegales"])) {
            foreach ($aProp["representanteslegales"] as $r) {
                $rx = array();
                $rx["tipoidentificacion"] = \funcionesRues::homologarTipoIdentificacion($dbx, $r["idtipoidentificacionrepleg"]);
                $rx["identificacion"] = $r["identificacionrepleg"];
                $rx["ape1"] = $r["ape1"];
                $rx["ape2"] = $r["ape2"];
                $rx["nom1"] = $r["nom1"];
                $rx["nom2"] = $r["nom2"];
                $rx["cargo"] = $r["cargorepleg"];
                $json["representacionlegal"]["representante"][] = $rx;
            }
        }

        //
        $json["situacionescontrol"] = array();
        $json["situacionescontrol"]["sitcontrol"] = array();
        if (!empty($aProp["sitcontrol"])) {
            foreach ($aProp["sitcontrol"] as $r) {
                $rx = array();
                $rx["nombre"] = $r["nombre"];
                $rx["identificacion"] = $r["identificacion"];
                $rx["domicilio"] = '';
                $rx["domiciliodescriptivo"] = $r["domicilio"];
                $rx["tipo"] = $r["tipo"];
                $rx["grupoempresarial"] = '';
                $json["situacionescontrol"]["sitcontrol"][] = $rx;
            }
        }

        //
        $json["informacionfinanciera"] = array();
        $json["informacionfinanciera"]["inffin_fechacorte"] = strval($aProp["inffin1510_fechacorte"]);
        $json["informacionfinanciera"]["inffin_actcte"] = strval($aProp["inffin1510_actcte"]);
        $json["informacionfinanciera"]["inffin_actnocte"] = strval($aProp["inffin1510_actnocte"]);
        $json["informacionfinanciera"]["inffin_acttot"] = strval($aProp["inffin1510_acttot"]);
        $json["informacionfinanciera"]["inffin_pascte"] = strval($aProp["inffin1510_pascte"]);
        $json["informacionfinanciera"]["inffin_paslar"] = strval($aProp["inffin1510_paslar"]);
        $json["informacionfinanciera"]["inffin_pastot"] = strval($aProp["inffin1510_pastot"]);
        $json["informacionfinanciera"]["inffin_patnet"] = strval($aProp["inffin1510_patnet"]);
        $json["informacionfinanciera"]["inffin_paspat"] = strval($aProp["inffin1510_paspat"]);
        $json["informacionfinanciera"]["inffin_balsoc"] = strval($aProp["inffin1510_balsoc"]);
        $json["informacionfinanciera"]["inffin_ingope"] = strval($aProp["inffin1510_ingope"]);
        $json["informacionfinanciera"]["inffin_ingnoope"] = strval($aProp["inffin1510_ingnoope"]);
        $json["informacionfinanciera"]["inffin_cosven"] = strval($aProp["inffin1510_cosven"]);
        $json["informacionfinanciera"]["inffin_gasope"] = strval($aProp["inffin1510_gasope"]);
        $json["informacionfinanciera"]["inffin_gasnoope"] = strval($aProp["inffin1510_gasnoope"]);
        $json["informacionfinanciera"]["inffin_gasimp"] = strval($aProp["inffin1510_gasimp"]);
        $json["informacionfinanciera"]["inffin_gasfin"] = strval($aProp["inffin1510_gasint"]);
        $json["informacionfinanciera"]["inffin_utiope"] = strval($aProp["inffin1510_utiope"]);
        $json["informacionfinanciera"]["inffin_utinet"] = strval($aProp["inffin1510_utinet"]);
        $json["informacionfinanciera"]["inffin_indliq"] = strval($aProp["inffin1510_indliq"]);
        $json["informacionfinanciera"]["inffin_nivend"] = strval($aProp["inffin1510_nivend"]);
        $json["informacionfinanciera"]["inffin_razcob"] = strval($aProp["inffin1510_razcob"]);
        $json["informacionfinanciera"]["inffin_renpat"] = strval($aProp["inffin1510_renpat"]);
        $json["informacionfinanciera"]["inffin_renact"] = strval($aProp["inffin1510_renact"]);

        //
        $json["clasificaciones"] = array();
        if (!empty($aProp["clasi1510"])) {
            foreach ($aProp["clasi1510"] as $r) {
                $json["clasificaciones"][] = $r;
            }
        }

        //
        $json["experiencia"] = array();
        $json["experiencia"]["contrato"] = array();
        if (!empty($aProp["exp1510"])) {
            foreach ($aProp["exp1510"] as $r) {
                $rx = array();
                $rx["secuencia"] = $r["secuencia"];
                $rx["celebradopor"] = $r["celebradopor"];
                $rx["nombrecontratista"] = $r["nombrecontratista"];
                $rx["nombrecontratante"] = $r["nombrecontratante"];
                $rx["valor"] = $r["valor"];
                $rx["porcentaje"] = $r["porcentaje"];
                $rx["clasificaciones"] = array();
                foreach ($r["clasif"] as $r1) {
                    $rx["clasificaciones"][] = $r1;
                }
                $json["experiencia"]["contrato"][] = $rx;
            }
        }

        //
        $json["contratosee"] = array();
        $json["contratosee"]["contratoee"] = array();
        if (!empty($aProp["contratos"])) {
            foreach ($aProp["contratos"] as $r) {
                $rx = array();
                $rx["nitentidad"] = $r["nitentidad"];
                $rx["nombreentidad"] = $r["nombreentidad"];
                $rx["munientidad"] = $r["idmunientidad"];
                $rx["divarea"] = $r["divarea"];
                $rx["identificacionresponsable"] = $r["idefun"];
                $rx["nombreresponsable"] = $r["nomfun"];
                $rx["cargoresponsable"] = $r["carfun"];
                $rx["fechareporte"] = $r["fecreporte"];
                $rx["fecharadicacion"] = $r["fecradic"];
                $rx["numeroinscripcionlibro"] = $r["numreglib"];
                $rx["fechainscripcionlibro"] = $r["fecreglib"];
                $rx["codigocamara"] = $r["codcamara"];
                $rx["numerocontrato"] = $r["numcontrato"];
                $rx["numerocontratosecop"] = $r["numcontratosecop"];
                $rx["fechaadjudicacion"] = $r["fechaadj"];
                $rx["fechaperfeccionamiento"] = $r["fechaper"];
                $rx["fechainicio"] = $r["fechaini"];
                $rx["fechaterminacion"] = $r["fechater"];
                $rx["fechaejecucion"] = $r["fechaeje"];
                $rx["fechaliquidacion"] = $r["fechaliq"];
                $rx["tipocontratista"] = $r["tipocont"];
                $rx["codigounspsc"] = '';
                if (!empty($r["unspsc"])) {
                    foreach ($r["unspsc"] as $r1) {
                        if ($rx["codigounspsc"] != '') {
                            $rx["codigounspsc"] .= ',';
                        }
                        $rx["codigounspsc"] .= $r1;
                    }
                }
                $rx["objeto"] = $r["objeto"];
                $rx["valorcontrato"] = $r["valorcont"];
                $rx["valorpagado"] = $r["valorcontpag"];
                $rx["relacionadoconstruccion"] = $r["contratorelacionadoconstruccion"];
                $rx["estadocontrato"] = $r["estadocont"];
                $rx["fechaterminacionanticipada"] = $r["fechaterant"];
                $rx["motivoterminacionanticipada"] = $r["motivoter"];
                $rx["fechacesion"] = $r["fechaces"];
                $rx["motivocesion"] = $r["motivoces"];
                $json["contratosee"]["contratoee"][] = $rx;
            }
        }

        //
        $json["multasee"] = array();
        $json["multasee"]["multaee"] = array();
        if (!empty($aProp["multas"])) {
            foreach ($aProp["multas"] as $r) {
                $rx = array();
                $rx["nitentidad"] = $r["nitentidad"];
                $rx["nombreentidad"] = $r["nombreentidad"];
                $rx["munientidad"] = $r["idmunientidad"];
                $rx["divarea"] = $r["divarea"];
                $rx["identificacionresponsable"] = $r["idefun"];
                $rx["nombreresponsable"] = $r["nomfun"];
                $rx["cargoresponsable"] = $r["carfun"];
                $rx["fechareporte"] = $r["fecreporte"];
                $rx["fecharadicacion"] = $r["fecradic"];
                $rx["numeroinscripcionlibro"] = $r["numreglib"];
                $rx["fechainscripcionlibro"] = $r["fecreglib"];
                $rx["codigocamara"] = $r["codcamara"];

                $rx["numeroactoadministrativo"] = $r["numacto"];
                $rx["fechaactoadministrativo"] = $r["fechaacto"];
                $rx["fechaejecutoriaactoadministrativo"] = $r["fechaeje"];

                $rx["numerocontrato"] = $r["numcontrato"];
                $rx["numerocontratosecop"] = $r["numcontratosecop"];
                $rx["valormulta"] = $r["valormult"];
                $rx["valorpagado"] = $r["valormultpag"];
                $rx["numeroactosuspension"] = $r["numsus"];
                $rx["fechaactosuspension"] = $r["fechasus"];
                $rx["numeroactoconfirmacion"] = $r["numconf"];
                $rx["fechaactoconfirmacion"] = $r["fechaconf"];
                $rx["estadomulta"] = $r["estadomult"];
                $rx["numeroactorevocacion"] = $r["numrev"];
                $rx["fechaactorevocacion"] = $r["fechanumrev"];
                $json["multasee"]["multaee"][] = $rx;
            }
        }

        //
        $json["sancionesee"] = array();
        $json["sancionesee"]["sancionee"] = array();
        if (!empty($aProp["sanciones"])) {
            foreach ($aProp["sanciones"] as $r) {
                $rx = array();
                $rx["nitentidad"] = $r["nitentidad"];
                $rx["nombreentidad"] = $r["nombreentidad"];
                $rx["munientidad"] = $r["idmunientidad"];
                $rx["divarea"] = $r["divarea"];
                $rx["identificacionresponsable"] = $r["idefun"];
                $rx["nombreresponsable"] = $r["nomfun"];
                $rx["cargoresponsable"] = $r["carfun"];
                $rx["fechareporte"] = $r["fecreporte"];
                $rx["fecharadicacion"] = $r["fecradic"];
                $rx["numeroinscripcionlibro"] = $r["numreglib"];
                $rx["fechainscripcionlibro"] = $r["fecreglib"];
                $rx["codigocamara"] = $r["codcamara"];

                $rx["numeroactoadministrativo"] = $r["numacto"];
                $rx["fechaactoadministrativo"] = $r["fechaacto"];
                $rx["fechaejecutoriaactoadministrativo"] = $r["fechaeje"];

                $rx["numerocontrato"] = $r["numcontrato"];
                $rx["numerocontratosecop"] = $r["numcontratosecop"];

                $rx["condicionincumplimiento"] = $r["condinc"];
                $rx["relacionadoconstruccion"] = $r["contratorelacionadoconstruccion"];
                $rx["incumplimientovis"] = $r["incumplimientoviviendainteressocial"];

                $rx["numeroactosuspension"] = $r["numsus"];
                $rx["fechaactosuspension"] = $r["fechasus"];
                $rx["numeroactoconfirmacion"] = $r["numconf"];
                $rx["fechaactoconfirmacion"] = $r["fechaconf"];
                $rx["estadosancion"] = $r["estadosanc"];
                $rx["numeroactorevocacion"] = $r["numrev"];
                $rx["fechaactorevocacion"] = $r["fechanumrev"];
                $rx["descripcion"] = $r["descripcion"];
                $rx["fundamentolegal"] = $r["fundamento"];
                $rx["vigencia"] = $r["vigencia"];

                $json["sancionesee"]["sancionee"][] = $rx;
            }
        }

        //
        $json["sancionesdisciplinarias"] = array();
        $json["sancionesdisciplinarias"]["sanciondisciplinariaee"] = array();
        if (!empty($aProp["sandis"])) {
            foreach ($aProp["sandis"] as $r) {
                $rx = array();
                $rx["nitentidad"] = $r["nitentidad"];
                $rx["nombreentidad"] = $r["nombreentidad"];
                $rx["munientidad"] = $r["idmunientidad"];
                $rx["divarea"] = $r["divarea"];
                $rx["identificacionresponsable"] = $r["idefun"];
                $rx["nombreresponsable"] = $r["nomfun"];
                $rx["cargoresponsable"] = $r["carfun"];
                $rx["fechareporte"] = $r["fecreporte"];
                $rx["fecharadicacion"] = $r["fecradic"];
                $rx["numeroinscripcionlibro"] = $r["numreglib"];
                $rx["fechainscripcionlibro"] = $r["fecreglib"];
                $rx["codigocamara"] = $r["codcamara"];

                $rx["numeroactoadministrativo"] = $r["numacto"];
                $rx["fechaactoadministrativo"] = $r["fechaacto"];
                $rx["fechaejecutoriaactoadministrativo"] = $r["fechaeje"];

                $rx["numerocontrato"] = $r["numcontrato"];
                $rx["numerocontratosecop"] = $r["numcontratosecop"];

                $rx["numeroactosuspension"] = $r["numsus"];
                $rx["fechaactosuspension"] = $r["fechasus"];
                $rx["numeroactoconfirmacion"] = $r["numconf"];
                $rx["fechaactoconfirmacion"] = $r["fechaconf"];
                $rx["estadosancion"] = $r["estadosanc"];
                $rx["numeroactorevocacion"] = $r["numrev"];
                $rx["fechaactorevocacion"] = $r["fechanumrev"];
                $rx["descripcion"] = $r["descripcion"];
                $rx["fundamentolegal"] = $r["fundamento"];
                $rx["vigencia"] = $r["vigencia"];

                $json["sancionesdisciplinarias"]["sanciondisciplinariaee"][] = $rx;
            }
        }

        //
        $json["inscripcioneslibros"] = array();
        $json["inscripcioneslibros"]["inscripcion"] = array();
        if (!empty($aProp["inscripciones"])) {
            foreach ($aProp["inscripciones"] as $r) {
                $rx = array();
                $rx["numeroregistro"] = $r["registro"];
                $rx["fecharegistro"] = $r["fecharegistro"];
                $rx["acto"] = \funcionesRues::homologarActosRUP($dbx, $r["acto"]);
                if ($r["texto"] != '') {
                    $rx["noticia"] = $r["texto"];
                } else {
                    $rx["noticia"] = retornarRegistroMysqliApi($dbx, 'mreg_actosproponente', "id='" . $r["acto"] . "'", "descripcion");
                }
                $rx["fechapublicacionrues"] = $r["fecpublicacionrue"];
                $rx["numeropublicacionrues"] = $r["idpublicacionrue"];
                $rx["controlrevocacion"] = $r["ctrrevoca"];
                $rx["numeroregistrorevocacion"] = $r["registrorevocacion"];
                $rx["fecharegistrorevocacion"] = $r["fecharegistrorevocacion"];
                $json["inscripcionesenlibros"]["inscripcion"][] = $rx;
            }
        }

        //
        return json_encode($json);
    }

}

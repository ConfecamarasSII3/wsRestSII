<?php

class funcionesRegistrales_retornarMregLiquidacion {

    public static function retornarMregLiquidacion($dbx, $numliq, $tipo = 'L') {
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');

        // Inicializa las variables del tr&aacute;mite
        $respuesta = array();
        $respuesta["idliquidacion"] = 0;
        $respuesta["numeroliquidacion"] = 0;
        $respuesta["tramiteautomatico"] = '';
        $respuesta["fecha"] = '';
        $respuesta["hora"] = '';
        $respuesta["idusuario"] = '';
        $respuesta["sede"] = '';
        $respuesta["tipotramite"] = '';
        $respuesta["subtipotramite"] = '';
        $respuesta["tipogasto"] = '';
        $respuesta["origen"] = '';
        $respuesta["iptramite"] = '';
        $respuesta["idestado"] = '';
        $respuesta["txtestado"] = '';
        $respuesta["idexpedientebase"] = '';
        $respuesta["idmatriculabase"] = '';
        $respuesta["idproponentebase"] = '';
        $respuesta["tipoproponente"] = '';
        $respuesta["tipoidentificacionbase"] = '';
        $respuesta["identificacionbase"] = '';
        $respuesta["nombrebase"] = '';
        $respuesta["nombrebasebase64"] = '';
        $respuesta["siglabase"] = '';
        $respuesta["siglabasebase64"] = '';
        $respuesta["nom1base"] = '';
        $respuesta["nom2base"] = '';
        $respuesta["ape1base"] = '';
        $respuesta["ape2base"] = '';
        $respuesta["organizacionbase"] = '';
        $respuesta["categoriabase"] = '';
        $respuesta["afiliadobase"] = '';
        $respuesta["matriculabase"] = '';
        $respuesta["proponentebase"] = '';

        $respuesta["numeromatriculapnat"] = '';
        $respuesta["camarapnat"] = '';
        $respuesta["orgpnat"] = '';
        $respuesta["tipoidepnat"] = '';
        $respuesta["idepnat"] = '';
        $respuesta["nombrepnat"] = '';

//
        $respuesta["nombreest"] = '';
        $respuesta["nombrepjur"] = '';
        $respuesta["nombresuc"] = '';
        $respuesta["nombreage"] = '';

        $respuesta["orgpjur"] = '';
        $respuesta["orgsuc"] = '';
        $respuesta["orgage"] = '';

        $respuesta["actpnat"] = '';
        $respuesta["actpjur"] = '';
        $respuesta["actest"] = '';
        $respuesta["actsuc"] = '';
        $respuesta["actage"] = '';

        $respuesta["perpnat"] = '';
        $respuesta["perpjur"] = '';

        $respuesta["munpnat"] = '';
        $respuesta["munest"] = '';
        $respuesta["munpjur"] = '';
        $respuesta["munsuc"] = '';
        $respuesta["munage"] = '';

        $respuesta["ultanoren"] = '';
        $respuesta["domicilioorigen"] = '';
        $respuesta["domiciliodestino"] = '';

        $respuesta["tipocliente"] = '';
        $respuesta["idtipoidentificacioncliente"] = '';
        $respuesta["identificacioncliente"] = '';
        $respuesta["nombrecliente"] = '';
        $respuesta["apellidocliente"] = '';

        $respuesta["razonsocialcliente"] = '';
        $respuesta["nombre1cliente"] = '';
        $respuesta["nombre2cliente"] = '';
        $respuesta["apellido1cliente"] = '';
        $respuesta["apellido2cliente"] = '';
        $respuesta["particulacliente"] = '';

        $respuesta["email"] = '';
        $respuesta["direccion"] = '';
        $respuesta["idmunicipio"] = '';
        $respuesta["telefono"] = '';
        $respuesta["movil"] = '';
        $respuesta["zonapostal"] = '';
        $respuesta["codigoregimen"] = '';
        $respuesta["responsabilidadtributaria"] = '';
        $respuesta["codigoimpuesto"] = '';
        $respuesta["nombreimpuesto"] = '';
        $respuesta["responsabilidadfiscal"] = '';
        $respuesta["pais"] = '';
        $respuesta["lenguaje"] = '';
        $respuesta["direccionnot"] = '';
        $respuesta["idmunicipionot"] = '';
        $respuesta["codposcom"] = '';
        $respuesta["codposnot"] = '';

        $respuesta["tipopagador"] = '';
        $respuesta["nombrepagador"] = '';
        $respuesta["apellidopagador"] = '';

        $respuesta["razonsocialpagador"] = '';
        $respuesta["nombre1pagador"] = '';
        $respuesta["nombre2pagador"] = '';
        $respuesta["apellido1pagador"] = '';
        $respuesta["apellido2pagador"] = '';

        $respuesta["tipoidentificacionpagador"] = '';
        $respuesta["identificacionpagador"] = '';
        $respuesta["direccionpagador"] = '';
        $respuesta["telefonopagador"] = '';
        $respuesta["movilpagador"] = '';
        $respuesta["municipiopagador"] = '';
        $respuesta["emailpagador"] = '';

        $respuesta["valorbruto"] = 0;
        $respuesta["valorbaseiva"] = 0;
        $respuesta["valoriva"] = 0;
        $respuesta["valortotal"] = 0;
        $respuesta["idsolicitudpago"] = 0;
        $respuesta["vueltas"] = 0;
        $respuesta["pagoefectivo"] = 0;
        $respuesta["pagocheque"] = 0;
        $respuesta["pagoconsignacion"] = 0;
        $respuesta["pagovisa"] = 0;
        $respuesta["pagoach"] = 0;
        $respuesta["pagomastercard"] = 0;
        $respuesta["pagoamerican"] = 0;
        $respuesta["pagocredencial"] = 0;
        $respuesta["pagodiners"] = 0;
        $respuesta["pagotdebito"] = 0;
        $respuesta["pagoprepago"] = 0;
        $respuesta["pagoafiliado"] = 0;
        $respuesta["idformapago"] = '';

        $respuesta["numerorecibo"] = '';
        $respuesta["numerooperacion"] = '';
        $respuesta["fecharecibo"] = '';
        $respuesta["horarecibo"] = '';
        $respuesta["totalrecibo"] = 0;

        $respuesta["numerorecibogob"] = '';
        $respuesta["numerooperaciongob"] = '';
        $respuesta["fecharecibogob"] = '';
        $respuesta["horarecibogob"] = '';
        $respuesta["totalrecibogob"] = 0;

        $respuesta["idfranquicia"] = '';
        $respuesta["nombrefranquicia"] = '';
        $respuesta["numeroautorizacion"] = '';
        $respuesta["idcodban"] = '';
        $respuesta["nombrebanco"] = '';
        $respuesta["numerocheque"] = '';
        $respuesta["numerorecuperacion"] = '';
        $respuesta["numeroradicacion"] = '';
        $respuesta["alertaid"] = 0;
        $respuesta["alertaservicio"] = '';
        $respuesta["alertavalor"] = 0;
        $respuesta["ctrcancelacion"] = '';
        $respuesta["idasesor"] = '';
        $respuesta["numeroempleados"] = 0;
        $respuesta["pagoafiliacion"] = '';
        $respuesta["numerofactura"] = '';

        $respuesta["incluirformularios"] = '';
        $respuesta["incluircertificados"] = '';
        $respuesta["incluirdiploma"] = '';
        $respuesta["incluircartulina"] = '';
        $respuesta["incluirafiliacion"] = '';
        $respuesta["matricularpnat"] = '';
        $respuesta["matricularest"] = '';
        $respuesta["regimentributario"] = '';
        $respuesta["tipomatricula"] = '';
        $respuesta["camaracambidom"] = '';
        $respuesta["matriculacambidom"] = '';
        $respuesta["municipiocambidom"] = '';
        $respuesta["fecmatcambidom"] = '';
        $respuesta["fecrencambidom"] = '';
        $respuesta["benart7"] = 'N';
        $respuesta["benley1780"] = 'N';
        $respuesta["controlfirma"] = 'N';
        $respuesta["actualizacionciiuversion4"] = '';
        $respuesta["reliquidacion"] = '';
        $respuesta["cumplorequisitosbenley1780"] = '';
        $respuesta["mantengorequisitosbenley1780"] = '';
        $respuesta["renunciobeneficiosley1780"] = '';
        $respuesta["multadoponal"] = '';
        $respuesta["controlaactividadaltoimpacto"] = '';
        $respuesta["sistemacreacion"] = '';

        $respuesta["nombrebaseliquidacion"] = '';
        $respuesta["tipdocbaseliquidacion"] = '';
        $respuesta["numdocbaseliquidacion"] = '';
        $respuesta["fecdocbaseliquidacion"] = '';
        $respuesta["mundocbaseliquidacion"] = '';
        $respuesta["oridocbaseliquidacion"] = '';
        $respuesta["tiporegistrobaseliquidacion"] = '';

        $respuesta["capital"] = 0;
        $respuesta["tipodoc"] = '';
        $respuesta["numdoc"] = '';
        $respuesta["fechadoc"] = '';
        $respuesta["origendoc"] = '';
        $respuesta["mundoc"] = '';
        $respuesta["organizacion"] = '';
        $respuesta["categoria"] = '';

        $respuesta["tipoiderepleg"] = '';
        $respuesta["iderepleg"] = '';
        $respuesta["nombre1repleg"] = '';
        $respuesta["nombre2repleg"] = '';
        $respuesta["apellido1repleg"] = '';
        $respuesta["apellido2repleg"] = '';
        $respuesta["cargorepleg"] = ''; //
        $respuesta["emailrepleg"] = ''; //
        $respuesta["firmorepleg"] = ''; //
        $respuesta["celularrepleg"] = ''; //

        $respuesta["tipoideradicador"] = '';
        $respuesta["ideradicador"] = '';
        $respuesta["nombreradicador"] = '';
        $respuesta["fechaexpradicador"] = '';
        $respuesta["emailradicador"] = '';
        $respuesta["telefonoradicador"] = '';
        $respuesta["celularradicador"] = '';
        $respuesta["motivoevidenciafotografica"] = '';

        $respuesta["tipolibro"] = ''; //
        $respuesta["codigolibro"] = ''; //
        $respuesta["primeravez"] = ''; //
        $respuesta["confirmadigital"] = ''; //

        $respuesta["iderevfis"] = ''; //
        $respuesta["nombre1revfis"] = ''; //
        $respuesta["nombre2revfis"] = ''; //
        $respuesta["apellido1revfis"] = ''; //
        $respuesta["apellido2revfis"] = ''; //
        $respuesta["cargorevfis"] = ''; //
        $respuesta["emailrevfis"] = ''; //
        $respuesta["firmorevfis"] = ''; //
        $respuesta["celularrevfis"] = ''; //

        $respuesta["idepreasa"] = ''; //
        $respuesta["nombre1preasa"] = ''; //
        $respuesta["nombre2preasa"] = ''; //
        $respuesta["apellido1preasa"] = ''; //
        $respuesta["apellido2preasa"] = ''; //
        $respuesta["cargopreasa"] = ''; //
        $respuesta["emailpreasa"] = ''; //
        $respuesta["firmopreasa"] = ''; //
        $respuesta["celularpreasa"] = ''; //

        $respuesta["idesecasa"] = ''; //
        $respuesta["nombre1secasa"] = ''; //
        $respuesta["nombre2secasa"] = ''; //    
        $respuesta["apellido1secasa"] = ''; //
        $respuesta["apellido2secasa"] = ''; //
        $respuesta["cargosecasa"] = ''; //
        $respuesta["emailsecasa"] = ''; //
        $respuesta["firmosecasa"] = ''; //
        $respuesta["celularsecasa"] = ''; //

        $respuesta["tipoidentificacionaceptante"] = '';
        $respuesta["identificacionaceptante"] = '';
        $respuesta["nombre1aceptante"] = '';
        $respuesta["nombre2aceptante"] = '';
        $respuesta["apellido1aceptante"] = '';
        $respuesta["apellido2aceptante"] = '';
        $respuesta["direccionaceptante"] = '';
        $respuesta["municipioaceptante"] = '';
        $respuesta["emailaceptante"] = '';
        $respuesta["telefonoaceptante"] = '';
        $respuesta["celularaceptante"] = '';
        $respuesta["cargoaceptante"] = '';
        $respuesta["fechadocideaceptante"] = '';

        $respuesta["idmotivocancelacion"] = '';
        $respuesta["motivocancelacion"] = '';
        $respuesta["ctrrenovartodos"] = '';

        $respuesta["motivocorreccion"] = '';
        $respuesta["tipoerror1"] = '';
        $respuesta["tipoerror2"] = '';
        $respuesta["tipoerror3"] = '';
        $respuesta["tipoidentificacioncor"] = '';
        $respuesta["identificacioncor"] = '';
        $respuesta["nombre1cor"] = '';
        $respuesta["nombre2cor"] = '';
        $respuesta["apellido1cor"] = '';
        $respuesta["apellido2cor"] = '';
        $respuesta["direccioncor"] = '';
        $respuesta["municipiocor"] = '';
        $respuesta["emailcor"] = '';
        $respuesta["telefonocor"] = '';
        $respuesta["celularcor"] = '';

        $respuesta["descripcionembargo"] = '';
        $respuesta["descripciondesembargo"] = '';
        $respuesta["tipoidentificaciondemandante"] = '';
        $respuesta["identificaciondemandante"] = '';
        $respuesta["nombredemandante"] = '';
        $respuesta["libro"] = '';
        $respuesta["numreg"] = '';

        $respuesta["descripcionpqr"] = '';
        $respuesta["tipoidentificacionpqr"] = '';
        $respuesta["identificacionpqr"] = '';
        $respuesta["nombre1pqr"] = '';
        $respuesta["nombre2pqr"] = '';
        $respuesta["apellido1pqr"] = '';
        $respuesta["apellido2pqr"] = '';
        $respuesta["direccionpqr"] = '';
        $respuesta["municipiopqr"] = '';
        $respuesta["emailpqr"] = '';
        $respuesta["telefonopqr"] = '';
        $respuesta["celularpqr"] = '';

        $respuesta["descripcionrr"] = '';
        $respuesta["inscripcionrr"] = '';
        $respuesta["tipoidentificacionrr"] = '';
        $respuesta["identificacionrr"] = '';
        $respuesta["nombre1rr"] = '';
        $respuesta["nombre2rr"] = '';
        $respuesta["apellido1rr"] = '';
        $respuesta["apellido2rr"] = '';
        $respuesta["direccionrr"] = '';
        $respuesta["municipiorr"] = '';
        $respuesta["emailrr"] = '';
        $respuesta["telefonorr"] = '';
        $respuesta["celularrr"] = '';
        $respuesta["subsidioapelacionrr"] = '';
        $respuesta["soloapelacionrr"] = '';

        $respuesta["tipocertificado"] = '';
        $respuesta["explicacion"] = '';
        $respuesta["textolibre"] = '';

        $respuesta["proyectocaja"] = '001';
        $respuesta["cargoafiliacion"] = 'NO';
        $respuesta["cargogastoadministrativo"] = 'NO';
        $respuesta["cargoentidadoficial"] = 'NO';
        $respuesta["cargoconsulta"] = 'NO';

        $respuesta["opcionafiliado"] = '';
        $respuesta["saldoafiliado"] = 0;
        $respuesta["matriculaafiliado"] = '';
        $respuesta["ultanorenafi"] = '';

        // Mutaciones
        $respuesta["modcom"] = '';
        $respuesta["modnot"] = '';
        $respuesta["modciiu"] = '';
        $respuesta["modnombre"] = '';

        $respuesta["nombreanterior"] = '';
        $respuesta["nombrenuevo"] = '';

        $respuesta["ant_versionciiu"] = '';
        $respuesta["ant_ciiu11"] = '';
        $respuesta["ant_ciiu12"] = '';
        $respuesta["ant_ciiu13"] = '';
        $respuesta["ant_ciiu14"] = '';
        $respuesta["ant_ciiu21"] = '';
        $respuesta["ant_ciiu22"] = '';
        $respuesta["ant_ciiu23"] = '';
        $respuesta["ant_ciiu24"] = '';
        $respuesta["ant_dircom"] = '';
        $respuesta["ant_telcom1"] = '';
        $respuesta["ant_telcom2"] = '';
        $respuesta["ant_faxcom"] = '';
        $respuesta["ant_celcom"] = '';
        $respuesta["ant_muncom"] = '';
        $respuesta["ant_barriocom"] = '';
        $respuesta["ant_numpredial"] = '';
        $respuesta["ant_emailcom"] = '';
        $respuesta["ant_emailcom2"] = '';
        $respuesta["ant_emailcom3"] = '';
        $respuesta["ant_dirnot"] = '';
        $respuesta["ant_telnot1"] = '';
        $respuesta["ant_telnot2"] = '';
        $respuesta["ant_faxnot"] = '';
        $respuesta["ant_celnot"] = '';
        $respuesta["ant_munnot"] = '';
        $respuesta["ant_barrionot"] = '';
        $respuesta["ant_emailnot"] = '';

        $respuesta["versionciiu"] = '';
        $respuesta["ciiu11"] = '';
        $respuesta["ciiu12"] = '';
        $respuesta["ciiu13"] = '';
        $respuesta["ciiu14"] = '';
        $respuesta["ciiu21"] = '';
        $respuesta["ciiu22"] = '';
        $respuesta["ciiu23"] = '';
        $respuesta["ciiu24"] = '';
        $respuesta["dircom"] = '';
        $respuesta["telcom1"] = '';
        $respuesta["telcom2"] = '';
        $respuesta["faxcom"] = '';
        $respuesta["celcom"] = '';
        $respuesta["muncom"] = '';
        $respuesta["barriocom"] = '';
        $respuesta["numpredial"] = '';
        $respuesta["emailcom"] = '';
        $respuesta["emailcom2"] = '';
        $respuesta["emailcom3"] = '';
        $respuesta["dirnot"] = '';
        $respuesta["telnot1"] = '';
        $respuesta["telnot2"] = '';
        $respuesta["faxnot"] = '';
        $respuesta["celnot"] = '';
        $respuesta["munnot"] = '';
        $respuesta["barrionot"] = '';
        $respuesta["emailnot"] = '';

        // En caso de trámites rues
        $respuesta["rues_numerointerno"] = "";
        $respuesta["rues_numerounico"] = "";
        $respuesta["rues_camarareceptora"] = "";
        $respuesta["rues_camararesponsable"] = "";
        $respuesta["rues_matricula"] = "";
        $respuesta["rues_proponente"] = "";
        $respuesta["rues_nombreregistrado"] = "";
        $respuesta["rues_claseidentificacion"] = "";
        $respuesta["rues_numeroidentificacion"] = "";
        $respuesta["rues_dv"] = "";
        $respuesta["rues_estado_liquidacion"] = "";
        $respuesta["rues_estado_transaccion"] = "";
        $respuesta["rues_nombrepagador"] = "";
        $respuesta["rues_origendocumento"] = "";
        $respuesta["rues_fechadocumento"] = "";
        $respuesta["rues_fechapago"] = "";
        $respuesta["rues_numerofactura"] = "";
        $respuesta["rues_referenciaoperacion"] = "";
        $respuesta["rues_totalpagado"] = 0;
        $respuesta["rues_formapago"] = "";
        $respuesta["rues_indicadororigen"] = "";
        $respuesta["rues_empleados"] = "";
        $respuesta["rues_indicadorbeneficio"] = "";
        $respuesta["rues_fecharespuesta"] = "";
        $respuesta["rues_horarespuesta"] = "";
        $respuesta["rues_codigoerror"] = "";
        $respuesta["rues_mensajeerror"] = "";
        $respuesta["rues_firmadigital"] = "";
        $respuesta["rues_firmadigital"] = "";
        $respuesta["rues_caracteres_por_linea"] = "";

        $respuesta["expedientes"] = array();
        $respuesta["liquidacion"] = array();
        $respuesta["rues_servicios"] = array();
        $respuesta["rues_textos"] = array();
        $respuesta["transacciones"] = array();
        $respuesta["transaccionesgenerales"] = array();
        $respuesta["certificadospendientes"] = array();

        $respuesta["nrocontrolsipref"] = '';
        $respuesta["foto"] = '../../images/sii/people.png';
        $respuesta["fotoabsoluta"] = 'images/sii/people.png';
        $respuesta["cedula1"] = '../../images/sii/people.png';
        $respuesta["cedula1absoluta"] = 'images/sii/people.png';
        $respuesta["cedula2"] = '../../images/sii/people.png';
        $respuesta["cedula2absoluta"] = 'images/sii/people.png';

        $respuesta["firmadoelectronicamente"] = '';
        $respuesta["firmadomanuscrita"] = '';
        $respuesta["tipoidefirmante"] = '';
        $respuesta["identificacionfirmante"] = '';
        $respuesta["fechaexpfirmante"] = '';
        $respuesta["apellido1firmante"] = '';
        $respuesta["apellido2firmante"] = '';
        $respuesta["nombre1firmante"] = '';
        $respuesta["nombre2firmante"] = '';
        $respuesta["emailfirmante"] = '';
        $respuesta["emailfirmanteseguimiento"] = '';
        $respuesta["celularfirmante"] = '';
        $respuesta["direccionfirmante"] = '';
        $respuesta["municipiofirmante"] = '';

        $respuesta["factorfirmado"] = '';
        $respuesta["firmante"] = '';
        $respuesta["exigeverificado"] = '';

        $respuesta["emailcontactoasesoria"] = '';
        $respuesta["comentariosasesoria"] = '';

        $respuesta["pedirbalance"] = '';
        $respuesta["incrementocupocertificados"] = 0;
        $respuesta["cobrarmutacion"] = '';

        $respuesta["propcamaraorigen"] = '';
        $respuesta["propproponenteorigen"] = '';
        $respuesta["propfechaultimainscripcion"] = '';
        $respuesta["propfechaultimarenovacion"] = '';
        $respuesta["propdircom"] = '';
        $respuesta["propmuncom"] = '';
        $respuesta["proptelcom1"] = '';
        $respuesta["proptelcom2"] = '';
        $respuesta["proptelcom3"] = '';
        $respuesta["propemailcom"] = '';
        $respuesta["propdirnot"] = '';
        $respuesta["propmunnot"] = '';
        $respuesta["proptelnot1"] = '';
        $respuesta["proptelnot2"] = '';
        $respuesta["proptelnot3"] = '';
        $respuesta["propemailnot"] = '';

        $respuesta["tramitepresencial"] = '';
        $respuesta["sede"] = '01';
        if (!isset($_SESSION["generales"]["codigousuario"]) || $_SESSION["generales"]["codigousuario"] == 'USUPUBXX') {
            $respuesta["sede"] = '99'; // Sede virtual
            $respuesta["tramitepresencial"] = '1'; // Tramite virtual
        } else {
            $respuesta["tramitepresencial"] = '4'; // Trámite presencial
            if (!isset($_SESSION["generales"]["sedeusuario"])) {
                $_SESSION["generales"]["sedeusuario"] = '01';
            }
            $respuesta["sede"] = $_SESSION["generales"]["sedeusuario"];
        }

//
        $respuesta["anodeposito"] = '';
        $respuesta["cantidadfolios"] = '';
        $respuesta["cantidadhojas"] = '';
        $respuesta["enviara"] = '';
        $respuesta["emailenviocertificados"] = '';
        $respuesta["gateway"] = '';

        $respuesta["idclasefirmamanuscrita"] = '';
        $respuesta["numidfirmamanuscrita"] = '';
        $respuesta["nombrefirmamanuscrita"] = '';
        $respuesta["emailfirmamanuscrita"] = '';

        $respuesta["urlretorno"] = '';
        $respuesta["webhook"] = '';
        $respuesta["tramitedevuelto"] = '';
        $respuesta["botonesauxiliares"] = '';
        $respuesta["iddirectlink"] = 0;
        $respuesta["datofijados"] = '';
        $respuesta["tamanoempresarial957"] = '';
        $respuesta["emailcontrol"] = '';

        $respuesta["forzardescuento1756"] = '';
        $respuesta["asignodatosfacturacion"] = '';
        $respuesta["certificadospendientesbandeja"] = '';

        $respuesta["cf_identificacionfactura"] = '';
        $respuesta["cf_numerofactura"] = '';
        $respuesta["cf_cuentafactura"] = '';
        $respuesta["cf_tipofactura"] = '';
        $respuesta["cf_fuenteoriginalfactura"] = '';
        $respuesta["cf_numerofuenteoriginalfactura"] = '';
        $respuesta["cf_cuentaclientefactura"] = '';
        $respuesta["cf_periodofactura"] = '';
        $respuesta["cf_ccosfactura"] = '';
        $respuesta["cf_fondofactura"] = '';

        $respuesta["facturableelectronicamente"] = '';

        $respuesta["nrounicoreciboexterno"] = '';

        $respuesta["tramiteradicar"] = '';
        $respuesta["domiciliobase"] = '';
        $respuesta["tiposasbase"] = '';
        $respuesta["activosbase"] = 0;
        $respuesta["capitalbase"] = 0;
        $respuesta["ingresosbase"] = 0;
        $respuesta["ciiubase"] = '';
        $respuesta["pagoirbase"] = '';
        $respuesta["boletairbase"] = '';
        $respuesta["fechaboletairbase"] = '';
        $respuesta["gobernacionirbase"] = '';
        $respuesta["beneficio1780base"] = '';
        $respuesta["certificadosbase"] = '';
        $respuesta["libroactasbase"] = '';
        $respuesta["libroactascodigolfbase"] = '';
        $respuesta["libroactasnombrelfbase"] = '';
        $respuesta["libroactashojaslfbase"] = '';
        $respuesta["libroactascodigolebase"] = '';
        $respuesta["libroactasnombrelebase"] = '';
        $respuesta["libroaccionistasbase"] = '';
        $respuesta["libroaccionistascodigolfbase"] = '';
        $respuesta["libroaccionistasnombrelfbase"] = '';
        $respuesta["libroaccionistashojaslfbase"] = '';
        $respuesta["libroaccionistascodigolebase"] = '';
        $respuesta["libroaccionistasnombrelebase"] = '';
        $respuesta["liquidarir"] = '';
        $respuesta["solicitaractivopropietario"] = '';

        //
        if ($tipo == 'VC') {
            return $respuesta;
        }

//
        $arrLiq = array();

//
        if ($tipo == 'L') {
            $arrLiq = retornarRegistroMysqliApi($dbx, 'mreg_liquidacion', "idliquidacion=" . $numliq);
        }

//
        if ($tipo == 'NR') {
            $arrLiq = retornarRegistroMysqliApi($dbx, 'mreg_liquidacion', "numerorecuperacion='" . $numliq . "'", '*', 'U');
            if (($arrLiq) && (!empty($arrLiq))) {
                $numliq = $arrLiq["idliquidacion"];
            }
        }

        if ($tipo == 'CB') {
            $arrLiq = retornarRegistroMysqliApi($dbx, 'mreg_liquidacion', "numeroradicacion='" . $numliq . "'");
            if (($arrLiq) && (!empty($arrLiq))) {
                $numliq = $arrLiq["idliquidacion"];
            }
        }

        if ($tipo == 'R') {
            $arrLiq = retornarRegistroMysqliApi($dbx, 'mreg_liquidacion', "numerorecibo='" . $numliq . "'");
            if (($arrLiq) && (!empty($arrLiq))) {
                $numliq = $arrLiq["idliquidacion"];
            }
        }


        if (empty($arrLiq)) {
            return false;
        }

        if (!isset($arrLiq["sede"]) || $arrLiq["sede"] == '') {
            $arrLiq["sede"] = '99';
            if ($arrLiq["idusuario"] != 'USUPUBXX') {
                if ($arrLiq["idusuario"] == 'RUE') {
                    $arrLiq["sede"] = '90';
                } else {
                    $arrusu = retornarRegistroMysqliApi($dbx, 'usuarios', "idusuario='" . $arrLiq["idusuario"] . "'");
                    if ($arrusu === false || $arrusu["idsede"] == '') {
                        $arrLiq["sede"] = '01';
                    } else {
                        $arrLiq["sede"] = $arrusu["idsede"];
                    }
                }
            }
            if ($arrLiq["sede"] == '') {
                $arrLiq["sede"] = '01';
            }
        }

        if (!isset($arrLiq["pagoconsignacion"])) {
            $arrLiq["pagoconsignacion"] = 0;
        }
        if (!isset($arrLiq["proyectocaja"])) {
            $arrLiq["proyectocaja"] = '001';
        }
        if (!isset($arrLiq["cargoafiliacion"])) {
            $arrLiq["cargoafiliacion"] = 'NO';
        }
        if (!isset($arrLiq["cargogastoadministrativo"])) {
            $arrLiq["cargogastoadministrativo"] = 'NO';
        }
        if (!isset($arrLiq["cargoentidadoficial"])) {
            $arrLiq["cargoentidadoficial"] = 'NO';
        }
        if (!isset($arrLiq["cargoconsulta"])) {
            $arrLiq["cargoconsulta"] = 'NO';
        }
        if (!isset($arrLiq["domicilioorigen"])) {
            $arrLiq["domicilioorigen"] = '';
        }
        if (!isset($arrLiq["domiciliodestino"])) {
            $arrLiq["domiciliodestino"] = '';
        }
        if (!isset($arrLiq["benart7"])) {
            $arrLiq["benart7"] = 'N';
        }
        if (!isset($arrLiq["controlfirma"])) {
            $arrLiq["controlfirma"] = 'N';
        }
        if (!isset($arrLiq["ultanoren"])) {
            $arrLiq["ultanoren"] = '';
        }
        if (!isset($arrLiq["idmatriculabase"])) {
            $arrLiq["idmatriculabase"] = '';
        }
        if (!isset($arrLiq["idproponentebase"])) {
            $arrLiq["idproponentebase"] = '';
        }

        // $respuesta = array();
        $respuesta["idliquidacion"] = $arrLiq["idliquidacion"];
        $respuesta["numeroliquidacion"] = $arrLiq["idliquidacion"];
        $respuesta["sistemacreacion"] = $arrLiq["sistemacreacion"];
        $respuesta["sede"] = $arrLiq["sede"];
        $respuesta["fecha"] = $arrLiq["fecha"];
        $respuesta["hora"] = $arrLiq["hora"];
        $respuesta["fechaultimamodificacion"] = $arrLiq["fechaultimamodificacion"];
        $respuesta["idusuario"] = $arrLiq["idusuario"];
        $respuesta["tipotramite"] = $arrLiq["tipotramite"];
        $respuesta["iptramite"] = $arrLiq["iptramite"];
        $respuesta["idestado"] = $arrLiq["idestado"];
        $respuesta["txtestado"] = retornarRegistroMysqliApi($dbx, "mreg_liquidacionestados", "id='" . $arrLiq["idestado"] . "'", "descripcion");

        $respuesta["idexpedientebase"] = $arrLiq["idexpedientebase"];
        $respuesta["idmatriculabase"] = $arrLiq["idmatriculabase"];
        $respuesta["idproponentebase"] = $arrLiq["idproponentebase"];
        if (trim($respuesta["idexpedientebase"]) == '') {
            if (trim($respuesta["idmatriculabase"]) != '') {
                $respuesta["idexpedientebase"] = $respuesta["idmatriculabase"];
            } else {
                if (trim($respuesta["idproponentebase"]) != '') {
                    $respuesta["idexpedientebase"] = $respuesta["idproponentebase"];
                }
            }
        }

        $respuesta["identificacionbase"] = $arrLiq["identificacionbase"];
        $respuesta["tipoidentificacionbase"] = $arrLiq["tipoidentificacionbase"];
        $respuesta["nombrebase"] = $arrLiq["nombrebase"];
        $respuesta["organizacionbase"] = $arrLiq["organizacionbase"];
        $respuesta["categoriabase"] = $arrLiq["categoriabase"];

        $respuesta["tipoidepnat"] = $arrLiq["tipoidepnat"];
        $respuesta["idepnat"] = $arrLiq["idepnat"];

        $respuesta["nombrepnat"] = $arrLiq["nombrepnat"];
        $respuesta["nombreest"] = $arrLiq["nombreest"];

        $respuesta["actpnat"] = $arrLiq["actpnat"];
        $respuesta["actest"] = $arrLiq["actest"];

        $respuesta["perpnat"] = $arrLiq["perpnat"];

        $respuesta["numeromatriculapnat"] = $arrLiq["numeromatriculapnat"];
        $respuesta["camarapnat"] = $arrLiq["camarapnat"];

        $respuesta["ultanoren"] = $arrLiq["ultanoren"];
        $respuesta["domicilioorigen"] = $arrLiq["domicilioorigen"];
        $respuesta["domiciliodestino"] = $arrLiq["domiciliodestino"];

        $respuesta["idtipoidentificacioncliente"] = $arrLiq["idtipoidentificacioncliente"];
        $respuesta["identificacioncliente"] = $arrLiq["identificacioncliente"];
        $respuesta["nombrecliente"] = $arrLiq["nombrecliente"];
        $respuesta["apellidocliente"] = $arrLiq["apellidocliente"];
        $respuesta["email"] = $arrLiq["email"];
        $respuesta["direccion"] = $arrLiq["direccion"];
        $respuesta["idmunicipio"] = $arrLiq["idmunicipio"];
        $respuesta["telefono"] = $arrLiq["telefono"];
        $respuesta["movil"] = $arrLiq["movil"];

        $respuesta["nombrepagador"] = $arrLiq["nombrepagador"];
        $respuesta["apellidopagador"] = $arrLiq["apellidopagador"];
        $respuesta["tipoidentificacionpagador"] = $arrLiq["tipoidentificacionpagador"];
        $respuesta["identificacionpagador"] = $arrLiq["identificacionpagador"];
        $respuesta["direccionpagador"] = $arrLiq["direccionpagador"];
        $respuesta["telefonopagador"] = $arrLiq["telefonopagador"];
        $respuesta["movilpagador"] = $arrLiq["movilpagador"];
        $respuesta["municipiopagador"] = $arrLiq["municipiopagador"];
        $respuesta["emailpagador"] = $arrLiq["emailpagador"];

        $respuesta["valorbruto"] = $arrLiq["valorbruto"];
        $respuesta["valorbaseiva"] = $arrLiq["valorbaseiva"];
        $respuesta["valoriva"] = $arrLiq["valoriva"];
        $respuesta["valortotal"] = $arrLiq["valortotal"];
        $respuesta["idsolicitudpago"] = $arrLiq["idsolicitudpago"];

        $respuesta["pagoefectivo"] = $arrLiq["pagoefectivo"];
        $respuesta["pagocheque"] = $arrLiq["pagocheque"];
        $respuesta["pagoconsignacion"] = $arrLiq["pagoconsignacion"];
        $respuesta["pagovisa"] = $arrLiq["pagovisa"];
        $respuesta["pagoach"] = $arrLiq["pagoach"];
        $respuesta["pagomastercard"] = $arrLiq["pagomastercard"];
        $respuesta["pagoamerican"] = $arrLiq["pagoamerican"];
        $respuesta["pagocredencial"] = $arrLiq["pagocredencial"];
        $respuesta["pagodiners"] = $arrLiq["pagodiners"];
        $respuesta["pagotdebito"] = $arrLiq["pagotdebito"];
        $respuesta["pagoprepago"] = $arrLiq["pagoprepago"];
        $respuesta["pagoafiliado"] = $arrLiq["pagoafiliado"];

        $respuesta["idformapago"] = $arrLiq["idformapago"];
        $respuesta["numerorecibo"] = $arrLiq["numerorecibo"];
        $respuesta["numerooperacion"] = $arrLiq["numerooperacion"];
        $respuesta["fecharecibo"] = $arrLiq["fecharecibo"];
        $respuesta["horarecibo"] = $arrLiq["horarecibo"];

        if (isset($arrLiq["totalrecibo"])) {
            $respuesta["totalrecibo"] = $arrLiq["totalrecibo"];
        } else {
            $respuesta["totalrecibo"] = $respuesta["valortotal"];
        }

        if (isset($arrLiq["numerorecibogob"])) {
            $respuesta["numerorecibogob"] = $arrLiq["numerorecibogob"];
        }
        if (isset($arrLiq["numerooperaciongob"])) {
            $respuesta["numerooperaciongob"] = $arrLiq["numerooperaciongob"];
        }
        if (isset($arrLiq["fecharecibogob"])) {
            $respuesta["fecharecibogob"] = $arrLiq["fecharecibogob"];
        }
        if (isset($arrLiq["horarecibogob"])) {
            $respuesta["horarecibogob"] = $arrLiq["horarecibogob"];
        }
        if (isset($arrLiq["totalrecibogob"])) {
            $respuesta["totalrecibogob"] = $arrLiq["totalrecibogob"];
        }

        $respuesta["idfranquicia"] = $arrLiq["idfranquicia"];
        $respuesta["nombrefranquicia"] = $arrLiq["nombrefranquicia"];
        $respuesta["numeroautorizacion"] = $arrLiq["numeroautorizacion"];
        $respuesta["idcodban"] = $arrLiq["idcodban"];
        $respuesta["nombrebanco"] = $arrLiq["nombrebanco"];
        $respuesta["numerocheque"] = $arrLiq["numerocheque"];
        $respuesta["numerorecuperacion"] = $arrLiq["numerorecuperacion"];
        $respuesta["numeroradicacion"] = $arrLiq["numeroradicacion"];
        if (trim((string) $respuesta["numeroradicacion"]) != '') {
            $respuesta["estadonumeroradicacion"] = retornarRegistroMysqliApi($dbx, 'mreg_est_codigosbarras', "codigobarras='" . $respuesta["numeroradicacion"] . "'", "estadofinal");
            $respuesta["tipoestadonumeroradicacion"] = retornarRegistroMysqliApi($dbx, 'mreg_codestados_rutamercantil', "id='" . $respuesta["estadonumeroradicacion"] . "'", "tipoestado");
        } else {
            $respuesta["estadonumeroradicacion"] = '';
            $respuesta["tipoestadonumeroradicacion"] = '';
        }
        $respuesta["alertaid"] = $arrLiq["alertaid"];
        $respuesta["alertaservicio"] = $arrLiq["alertaservicio"];
        $respuesta["alertavalor"] = $arrLiq["alertavalor"];
        $respuesta["ctrcancelacion"] = $arrLiq["ctrcancelacion"];
        $respuesta["idasesor"] = $arrLiq["idasesor"];
        $respuesta["numeroempleados"] = $arrLiq["numeroempleados"];
        $respuesta["pagoafiliacion"] = $arrLiq["pagoafiliacion"];
        $respuesta["numerofactura"] = $arrLiq["numerofactura"];
        $respuesta["ticketid"] = $arrLiq["ticketid"];

        $respuesta["incluirformularios"] = $arrLiq["incluirformularios"];
        $respuesta["incluircertificados"] = $arrLiq["incluircertificados"];
        $respuesta["incluirdiploma"] = $arrLiq["incluirdiploma"];
        $respuesta["incluircartulina"] = $arrLiq["incluircartulina"];
        $respuesta["matricularpnat"] = $arrLiq["matricularpnat"];
        $respuesta["matricularest"] = $arrLiq["matricularest"];
        $respuesta["regimentributario"] = $arrLiq["regimentributario"];
        $respuesta["tipomatricula"] = $arrLiq["tipomatricula"];
        $respuesta["camaracambidom"] = $arrLiq["camaracambidom"];
        $respuesta["matriculacambidom"] = $arrLiq["matriculacambidom"];
        $respuesta["municipiocambidom"] = $arrLiq["municipiocambidom"];
        $respuesta["fecmatcambidom"] = $arrLiq["fecmatcambidom"];
        $respuesta["benart7"] = $arrLiq["benart7"];
        $respuesta["controlfirma"] = $arrLiq["controlfirma"];
        $respuesta["actualizacionciiuversion4"] = $arrLiq["actualizacionciiuversion4"];
        $respuesta["reliquidacion"] = $arrLiq["reliquidacion"];

        $respuesta["capital"] = $arrLiq["capital"];
        $respuesta["tipodoc"] = $arrLiq["tipodoc"];
        $respuesta["numdoc"] = $arrLiq["numdoc"];
        $respuesta["fechadoc"] = $arrLiq["fechadoc"];
        $respuesta["origendoc"] = $arrLiq["origendoc"];
        $respuesta["mundoc"] = $arrLiq["mundoc"];
        $respuesta["organizacion"] = $arrLiq["organizacion"];
        $respuesta["categoria"] = $arrLiq["categoria"];

        $respuesta["tipoiderepleg"] = $arrLiq["tipoiderepleg"];
        $respuesta["iderepleg"] = $arrLiq["iderepleg"];
        $respuesta["nombrerepleg"] = $arrLiq["nombrerepleg"];

        $respuesta["tipoideradicador"] = $arrLiq["tipoideradicador"];
        $respuesta["ideradicador"] = $arrLiq["ideradicador"];
        $respuesta["nombreradicador"] = $arrLiq["nombreradicador"];
        $respuesta["fechaexpradicador"] = $arrLiq["fechaexpradicador"];
        $respuesta["emailradicador"] = $arrLiq["emailradicador"];
        $respuesta["telefonoradicador"] = $arrLiq["telefonoradicador"];
        $respuesta["celularradicador"] = $arrLiq["celularradicador"];

        $respuesta["proyectocaja"] = $arrLiq["proyectocaja"];
        $respuesta["cargoafiliacion"] = $arrLiq["cargoafiliacion"];
        $respuesta["cargogastoadministrativo"] = $arrLiq["cargogastoadministrativo"];
        $respuesta["cargoentidadoficial"] = $arrLiq["cargoentidadoficial"];
        $respuesta["cargoconsulta"] = $arrLiq["cargoconsulta"];

        // 2016-04-08: JINT
        $respuesta["tramitepresencial"] = $arrLiq["tramitepresencial"];
        if ($respuesta["tramitepresencial"] == '') {
            if (!isset($_SESSION["generales"]["codigousuario"]) || $_SESSION["generales"]["codigousuario"] == 'USUPUBXX') {
                $respuesta["tramitepresencial"] = '1'; // Tramite virtual
            } else {
                $respuesta["tramitepresencial"] = '4'; // Trámite presencial
            }
        }

        // 2016-09-07: JINT
        $respuesta["firmadoelectronicamente"] = $arrLiq["firmadoelectronicamente"];
        $respuesta["firmadomanuscrita"] = $arrLiq["firmadomanuscrita"];

        $respuesta["cumplorequisitosbenley1780"] = $arrLiq["cumplorequisitosbenley1780"];
        $respuesta["mantengorequisitosbenley1780"] = $arrLiq["mantengorequisitosbenley1780"];
        $respuesta["renunciobeneficiosley1780"] = $arrLiq["renunciobeneficiosley1780"];
        $respuesta["controlactividadaltoimpacto"] = $arrLiq["controlactividadaltoimpacto"];
        $respuesta["multadoponal"] = $arrLiq["multadoponal"];
        $respuesta["gateway"] = $arrLiq["gateway"];
        $respuesta["emailcontrol"] = $arrLiq["emailcontrol"];

        $fqr = retornarRegistroMysqliApi($dbx, 'mreg_liquidacionqr', "idliquidacion=" . $numliq);
        if ($fqr && !empty($fqr)) {
            $respuesta["idclasefirmamanuscrita"] = $fqr["idclase"];
            $respuesta["numidfirmamanuscrita"] = $fqr["numid"];
            $respuesta["nombrefirmamanuscrita"] = $fqr["nombre"];
            $respuesta["emailfirmamanuscrita"] = $fqr["email"];
        }

        $respuesta["rues_claseidentificacion "] = '';

        $temCampos = retornarRegistrosMysqliApi($dbx, 'mreg_liquidacion_campos', "idliquidacion=" . $numliq);

        if (!empty($temCampos)) {
            foreach ($temCampos as $c) {
                if ($c["campo"] == 'incrementocupocertificados') {
                    $respuesta[$c["campo"]] = intval(trim($c["contenido"]));
                } else {
                    if ($c["campo"] == 'cumple1780') {
                        $respuesta["cumplorequisitosbenley1780"] = trim($c["contenido"]);
                    } else {
                        if ($c["campo"] == 'mantiene1780') {
                            $respuesta["mantengorequisitosbenley1780"] = trim($c["contenido"]);
                        } else {
                            if ($c["campo"] == 'renuncia1780') {
                                $respuesta["renunciobeneficiosley1780"] = trim($c["contenido"]);
                            } else {
                                if ($c["campo"] != 'firmadoelectronicamente') {
                                    $respuesta[$c["campo"]] = trim(stripslashes($c["contenido"]));
                                }
                            }
                        }
                    }
                }
            }
        }

        unset($temCampos);

        $temx = retornarRegistrosMysqliApi($dbx, 'mreg_liquidacion_textos_rues', "idliquidacion='" . $numliq . "'", "id");
        $ix = 0;
        foreach ($temx as $x) {
            $ix++;
            $respuesta["rues_textos"][$ix] = stripslashes($x);
        }
        unset($temx);

        $respuesta["matriculabase"] = '';
        $respuesta["proponentebase"] = '';
        $respuesta["nrocontrolsipref"] = $arrLiq["nrocontrolsipref"];

        //
        /*
          $arrTem1 = retornarRegistroMysqliApi($dbx, 'bas_tipotramites', "id='" . $respuesta["tipotramite"] . "'");
          if ($arrTem1 && !empty($arrTem1)) {
          if ($arrTem1["tiporegistro"] == 'RegMer' || $arrTem1["tiporegistro"] == 'RegEsadl') {
          $respuesta["matriculabase"] = $respuesta["idexpedientebase"];
          }
          if ($arrTem1["tiporegistro"] == 'RegPro') {
          $respuesta["proponentebase"] = $respuesta["idexpedientebase"];
          }
          }
         */

        unset($arrLiq);

	$respuesta["expedientes"] = array();
        $respuesta["transacciones"] = array();
        $respuesta["liquidacion"] = array();									
        // Arma arreglo de la liquidación
        $ix = 0;
        $arrDet = retornarRegistrosMysqliApi($dbx, "mreg_liquidaciondetalle", "idliquidacion=" . $numliq, "idsec asc,secuencia asc");        
        if (isset($arrDet) && !empty($arrDet)) {
            foreach ($arrDet as $lin) {                
                if ($lin["idservicio"] != '') {
                    $renglon = array();
                    $renglon["secuencia"] = $lin["secuencia"];
                    $renglon["idsec"] = sprintf("%03s", $lin["idsec"]);
                    $renglon["idservicio"] = $lin["idservicio"];
                    $renglon["txtservicio"] = retornarRegistroMysqliApi($dbx, "mreg_servicios", "idservicio='" . $lin["idservicio"] . "'", "nombre");
                    if (!isset($lin["cc"])) {
                        $lin["cc"] = '';
                    }
                    $renglon["cc"] = $lin["cc"];
                    $renglon["expediente"] = $lin["expediente"];
                    $renglon["nombre"] = $lin["nombre"];
                    $renglon["ano"] = $lin["ano"];
                    $renglon["cantidad"] = $lin["cantidad"];
                    $renglon["valorbase"] = $lin["valorbase"];
                    $renglon["porcentaje"] = $lin["porcentaje"];
                    $renglon["valorservicio"] = $lin["valorservicio"];
                    $renglon["benart7"] = $lin["benart7"];
                    $renglon["benley1780"] = $lin["benley1780"];
                    $renglon["reliquidacion"] = $lin["reliquidacion"];
                    $renglon["serviciobase"] = $lin["serviciobase"];
                    $renglon["pagoafiliacion"] = $lin["pagoafiliacion"];
                    $renglon["ir"] = $lin["ir"];
                    $renglon["iva"] = $lin["iva"];
                    $renglon["idalerta"] = $lin["idalerta"];
                    $renglon["expedienteafiliado"] = $lin["expedienteafiliado"];

                    $renglon["porcentajeiva"] = 0;
                    $renglon["valoriva"] = 0;
                    $renglon["servicioiva"] = '';
                    $renglon["porcentajedescuento"] = 0;
                    $renglon["valordescuento"] = 0;
                    $renglon["serviciodescuento"] = '';
                    $renglon["clavecontrol"] = $lin["clavecontrol"];
                    if (isset($lin["servicioorigen"])) {
                        $renglon["servicioorigen"] = $lin["servicioorigen"];
                        $renglon["diasmora"] = $lin["diasmora"];
                    } else {
                        $renglon["servicioorigen"] = '';
                        $renglon["diasmora"] = 0;                        
                    }
                    
                    if (isset($lin["porcentajeiva"]) && $lin["porcentajeiva"] != 0) {
                        $renglon["porcentajeiva"] = $lin["porcentajeiva"];
                        $renglon["valoriva"] = $lin["valoriva"];
                        $renglon["servicioiva"] = $lin["servicioiva"];
                        $renglon["porcentajedescuento"] = $lin["porcentajedescuento"];
                        $renglon["valordescuento"] = $lin["valordescuento"];
                        $renglon["serviciodescuento"] = $lin["serviciodescuento"];
                    }
                    $ix++;
                    $respuesta["liquidacion"][$ix] = $renglon;
                }
            }
            unset($arrDet);
        }
        
        
        // Arma arreglo de la liquidación RUES
        $ix = 0;
        $arrDet = retornarRegistrosMysqliApi($dbx, "mreg_liquidaciondetalle_rues", "idliquidacion=" . $numliq, "secuencia");
        if (isset($arrDet) && !empty($arrDet)) {
            foreach ($arrDet as $lin) {
                if ($lin["codigo_servicio"] != '') {
                    $renglon = array();
                    $renglon["codigo_servicio"] = $lin["codigo_servicio"];
                    $renglon["descripcion_servicio"] = $lin["descripcion_servicio"];
                    $renglon["orden_servicio"] = $lin["orden_servicio"];
                    $renglon["orden_servicio_asociado"] = $lin["orden_servicio_asociado"];
                    $renglon["nombre_base"] = $lin["nombre_base"];
                    $renglon["valor_base"] = $lin["valor_base"];
                    $renglon["valor_liquidacion"] = $lin["valor_liquidacion"];
                    $renglon["cantidad_servicio"] = $lin["cantidad_servicio"];
                    $renglon["indicador_base"] = $lin["indicador_base"];
                    $renglon["indicador_renovacion"] = $lin["indicador_renovacion"];
                    $renglon["matricula_servicio"] = $lin["matricula_servicio"];
                    $renglon["nombre_matriculado"] = $lin["nombre_matriculado"];
                    $renglon["ano_renovacion"] = $lin["ano_renovacion"];
                    $renglon["valor_activos_sin_ajustes"] = $lin["valor_activos_sin_ajustes"];
                    $ix++;
                    $respuesta["rues_servicios"][$ix] = $renglon;
                }
            }
            unset($arrDet);
        }

        // Arma arreglo de expedientes
        $arrExp = array ();
        $temx = retornarRegistrosMysqliApi($dbx, "mreg_liquidacionexpedientes", "idliquidacion=" . $numliq);
        if ($temx === false) {
            $arrExp = false;
        } else {
            if (!empty($temx)) {
                $arrExp = array();
                $i = -1;
                foreach ($temx as $res) {
                    $i++;
                    $arrExp[$i] = $res;
                    if ($arrExp[$i]["registrobase"] == 'S') {
                        if (trim($arrExp[$i]["primeranorenovado"]) == '') {
                            $arrExp[$i]["primeranorenovado"] = $arrExp[$i]["ultimoanorenovado"];
                        }
                    }
                }
            }
        }
        unset($temx);

        //
        if ($arrExp && is_array($arrExp) && !empty($arrExp)) {
            $i = 0;
            foreach ($arrExp as $lin) {
                $renglon = array();
                $i++;
                if (!isset($lin["cc"])) {
                    $lin["cc"] = '';
                }
                $renglon["cc"] = $lin["cc"];
                $renglon["matricula"] = $lin["matricula"];
                $renglon["proponente"] = $lin["proponente"];
                $renglon["numrue"] = $lin["numrue"];
                $renglon["idtipoidentificacion"] = $lin["idtipoidentificacion"];
                $renglon["identificacion"] = $lin["identificacion"];
                $renglon["razonsocial"] = $lin["razonsocial"];
                $renglon["ape1"] = $lin["ape1"];
                $renglon["ape2"] = $lin["ape2"];
                $renglon["nom1"] = $lin["nom1"];
                $renglon["nom2"] = $lin["nom2"];
                $renglon["organizacion"] = $lin["organizacion"];
                $renglon["txtorganizacion"] = retornarRegistroMysqliApi($dbx, "bas_organizacionjuridica", "id='" . $lin["organizacion"] . "'", "descripcion");
                $renglon["categoria"] = $lin["categoria"];
                $renglon["txtcategoria"] = '';
                if ($lin ["organizacion"] != '01' && $lin ["organizacion"] != '02') {
                    $renglon["txtcategoria"] = retornarRegistroMysqliApi($dbx, "bas_categorias", "id='" . $lin["categoria"] . "'", "descripcion");
                }
                $renglon["afiliado"] = $lin["afiliado"];
                $renglon["propietariojurisdiccion"] = $lin["propietariojurisdiccion"];
                $renglon["ultimoanoafiliado"] = $lin["ultimoanoafiliado"];
                $renglon["primeranorenovado"] = $lin["primeranorenovado"];
                $renglon["ultimoanorenovado"] = $lin["ultimoanorenovado"];
                $renglon["ultimosactivos"] = $lin["ultimosactivos"];
                $renglon["nuevosactivos"] = $lin["nuevosactivos"];
                $renglon["actividad"] = $lin["actividad"];
                $renglon["registrobase"] = $lin["registrobase"];
                $renglon["benart7"] = $lin["benart7"];
                $renglon["benley1780"] = $lin["benley1780"];
                $renglon["fechanacimiento"] = $lin["fechanacimiento"];
                $renglon["renovaresteano"] = $lin["renovaresteano"];
                $renglon["fechamatricula"] = $lin["fechamatricula"];
                $renglon["fecmatant"] = $lin["fecmatant"];
                $renglon["reliquidacion"] = $lin["reliquidacion"];
                $renglon["controlpot"] = $lin["controlpot"];
                $renglon["dircom"] = $lin["dircom"];
                $renglon["muncom"] = $lin["muncom"];
                $respuesta["expedientes"][$i] = $renglon;
            }
        }
        unset($arrExp);

        // Arma arreglo de transacciones generales
        if (existeTablaMysqliApi($dbx, 'mreg_liquidacion_transacciones_generales')) {
            $ixsec = 0;
            $atra = array();
            $arrTra = retornarRegistrosMysqliApi($dbx, 'mreg_liquidacion_transacciones_generales', "idliquidacion=" . $respuesta["numeroliquidacion"], "idsecuencia");
            if ($arrTra && !empty($arrTra)) {
                foreach ($arrTra as $trag) {
                    if ($ixsec == 0) {
                        $ixsec = $trag["idsecuencia"];
                        $atra = array();
                        $atra["idliquidacion"] = $trag["idliquidacion"];
                        $atra["secuencia"] = $trag["idsecuencia"];
                        $atra["transacciongeneral"] = '';
                        $atra["razonsocial"] = '';
                        $atra["sigla"] = '';
                        $atra["organizacion"] = '';
                        $atra["categoria"] = '';
                        $atra["clasegeneral"] = '';
                        $atra["claseespecifica"] = '';
                        $atra["condicionespecialley2219"] = '';
                        $atra["beneficioley1780"] = '';
                        $atra["municipio"] = '';
                        $atra["controlsocios"] = '';
                        $atra["activos"] = 0;
                        $atra["capital"] = 0;
                        $atra["ingresosactividadprincipal"] = 0;
                        $atra["ciiuactividadprincipal"] = '';
                        $atra["observaciones"] = '';
                    }
                    if ($ixsec != $trag["idsecuencia"]) {
                        $respuesta["transaccionesgenerales"][] = $atra;
                        $ixsec = $trag["idsecuencia"];
                        $atra = array();
                        $atra["idliquidacion"] = $trag["idliquidacion"];
                        $atra["secuencia"] = $trag["idsecuencia"];
                        $atra["transacciongeneral"] = '';
                        $atra["razonsocial"] = '';
                        $atra["sigla"] = '';
                        $atra["organizacion"] = '';
                        $atra["categoria"] = '';
                        $atra["clasegeneral"] = '';
                        $atra["claseespecifica"] = '';
                        $atra["condicionespecialley2219"] = '';
                        $atra["beneficioley1780"] = '';
                        $atra["municipio"] = '';
                        $atra["controlsocios"] = '';
                        $atra["activos"] = 0;
                        $atra["capital"] = 0;
                        $atra["ingresosactividadprincipal"] = 0;
                        $atra["ciiuactividadprincipal"] = '';
                        $atra["observaciones"] = '';
                    }
                    if ($trag["campo"] != 'idsecuencia' && $trag["campo"] != 'idliquidacion') {
                        $atra[$trag["campo"]] = stripslashes($trag["contenido"]);
                    }
                }
                $respuesta["transaccionesgenerales"][] = $atra;
                unset($arrTra);
            }
        }

        // Arma arreglo de transacciones
        $ix = 0;
        $arrTra = retornarRegistrosMysqliApi($dbx, 'mreg_liquidacion_transacciones', "idliquidacion=" . $respuesta["numeroliquidacion"], "idsecuencia");
        foreach ($arrTra as $tra) {
            $ix++;
            $respuesta["transacciones"][$ix] = $tra;
            $respuesta["transacciones"][$ix]["idsecuencia"] = sprintf("%03s", $tra["idsecuencia"]);
            $razonsocialbase64 = retornarRegistroMysqliApi($dbx, 'mreg_liquidacion_transacciones_campos', "idliquidacion=" . $tra["idliquidacion"] . " and idsecuencia=" . $tra["idsecuencia"] . " and campo='razonsocialbase64'", "contenido");
            $siglabase64 = retornarRegistroMysqliApi($dbx, 'mreg_liquidacion_transacciones_campos', "idliquidacion=" . $tra["idliquidacion"] . " and idsecuencia=" . $tra["idsecuencia"] . " and campo='siglabase64'", "contenido");
            if ($razonsocialbase64 != ''){
                $respuesta["transacciones"][$ix]["razonsocial"] = base64_decode($razonsocialbase64);
            }
            if ($siglabase64 != ''){
                $respuesta["transacciones"][$ix]["sigla"] = base64_decode($siglabase64);
            }
            
        }
        unset($arrTra);
        $respuesta["iLin"] = $ix;
        if ($respuesta["numerorecuperacion"] != '') {
            if (file_exists('../' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/fotoEvidencias/' . substr($respuesta["numerorecuperacion"], 0, 3) . '/' . $respuesta["numerorecuperacion"] . '-Foto.jpg')) {
                $respuesta["foto"] = '../' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/fotoEvidencias/' . substr($respuesta["numerorecuperacion"], 0, 3) . '/' . $respuesta["numerorecuperacion"] . '-Foto.jpg';
                $respuesta["fotoabsoluta"] = PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/fotoEvidencias/' . substr($respuesta["numerorecuperacion"], 0, 3) . '/' . $respuesta["numerorecuperacion"] . '-Foto.jpg';
            } else {
                $respuesta["fotoabsoluta"] = 'images/sii/people.png';
                $respuesta["foto"] = '../images/sii/people.png';
            }

            if (file_exists('../' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/fotoEvidencias/' . substr($respuesta["numerorecuperacion"], 0, 3) . '/' . $respuesta["numerorecuperacion"] . '-Cedula1.jpg')) {
                $respuesta["cedula1"] = '../' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/fotoEvidencias/' . substr($respuesta["numerorecuperacion"], 0, 3) . '/' . $respuesta["numerorecuperacion"] . '-Cedula1.jpg';
                $respuesta["cedula1absoluta"] = PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/fotoEvidencias/' . substr($respuesta["numerorecuperacion"], 0, 3) . '/' . $respuesta["numerorecuperacion"] . '-Cedula1.jpg';
            } else {
                $respuesta["cedula1absoluta"] = 'images/sii/people.png';
                $respuesta["cedula1"] = '../images/sii/people.png';
            }

            if (file_exists('../' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/fotoEvidencias/' . substr($respuesta["numerorecuperacion"], 0, 3) . '/' . $respuesta["numerorecuperacion"] . '-Cedula2.jpg')) {
                $respuesta["cedula2"] = '../' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/fotoEvidencias/' . substr($respuesta["numerorecuperacion"], 0, 3) . '/' . $respuesta["numerorecuperacion"] . '-Cedula2.jpg';
                $respuesta["cedula2absoluta"] = PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/fotoEvidencias/' . substr($respuesta["numerorecuperacion"], 0, 3) . '/' . $respuesta["numerorecuperacion"] . '-Cedula2.jpg';
            } else {
                $respuesta["cedula2absoluta"] = 'images/sii/people.png';
                $respuesta["cedula2"] = '../images/sii/people.png';
            }
        }
        if ($respuesta["numerorecibo"] != '') {
            $cerx = contarRegistrosMysqliApi($dbx, 'mreg_certificados_pendientes', "recibo='" . $respuesta["numerorecibo"] . "'");
            if ($cerx > 0) {
                $respuesta["certificadospendientesbandeja"] = 'si';
            }
        }

        //
        $respuesta["comentarioasesoria"] = '';
        $respuesta["respuestaasesoria"] = '';
        $respuesta["seguimientoasesoria"] = '';
        $ases = retornarRegistrosMysqliApi($dbx, 'mreg_liquidacion_asesorias', "idliquidacion=" . $respuesta["numeroliquidacion"], "fecha,hora");
        if ($ases && !empty($ases)) {
            foreach ($ases as $s) {
                if ($s["momento"] == 'solicitudasesoria' ||
                        $s["momento"] == 'comentario') {
                    if ($s["comentario"] != '') {
                        $respuesta["seguimientoasesoria"] .= \funcionesGenerales::mostrarFecha($s["fecha"]) . ' ' . \funcionesGenerales::mostrarHora($s["hora"]) . ' - Cliente : ' . $s["comentario"];
                        $respuesta["seguimientoasesoria"] .= '<br><img src="../../images/sii/image/borde1.jpg" width="95%" height="11px"><br>';
                    }
                    if ($s["respuesta"] != '') {
                        $respuesta["seguimientoasesoria"] .= \funcionesGenerales::mostrarFecha($s["fecha"]) . ' ' . \funcionesGenerales::mostrarHora($s["hora"]) . ' - Asesor - <a href="../../librerias/proceso/mregBandejaAsesorias.php?accion=editarrespuesta&id=' . $s["id"] . '">Editar</a> : ' . trim($s["respuesta"]);
                        $respuesta["seguimientoasesoria"] .= '<br><img src="../../images/sii/image/borde1.jpg" width="95%" height="11px"><br>';
                    }
                    $respuesta["emailcontactoasesoria"] = $s["emailcontactoasesoria"];
                }
            }
        }
        unset($ases);
        
        if ($respuesta["nombrebasebase64"] != '') {
            $respuesta["nombrebase"] = base64_decode($respuesta["nombrebasebase64"]);
        }
        if ($respuesta["siglabasebase64"] != '') {
            $respuesta["siglabase"] = base64_decode($respuesta["siglabasebase64"]);
        }

        return $respuesta;
    }

}

?>

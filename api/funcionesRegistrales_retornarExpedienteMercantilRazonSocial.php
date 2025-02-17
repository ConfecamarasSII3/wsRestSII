<?php

class funcionesRegistrales_retornarExpedienteMercantilRazonSocial {

    public static function retornarExpedienteMercantilRazonSocial($dbx = null, $mat = '') {
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRues.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/myErrorHandler.php');
        set_error_handler('myErrorHandler');

        //
        if ($mat == '') {
            return "";
        }
        
        // ********************************************************************************** //
        // Instancia la BD si no existe
        // ********************************************************************************** //
        if ($dbx === null) {
            $mysqli = conexionMysqliApi();
            if (mysqli_connect_error()) {
                $_SESSION["generales"]["mensajerror"] = 'Error conectando con la BD';
                return false;
            }
        } else {
            $mysqli = $dbx;
        }
        
        //
        $nominicial = '';
    
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

        // Lectura del registro principal
        $reg = retornarRegistroMysqliApi($mysqli, 'mreg_est_inscritos', "matricula='" . $mat . "'", '*', 'U');
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
            return false;
        }

        // Armado del arreglo de respuesta
        $retorno = array();
        $retorno["matricula"] = $reg["matricula"];
        $retorno["complementorazonsocial"] = '';
        if (isset($reg["complementorazonsocial"])) {
            $retorno["complementorazonsocial"] = stripslashes(\funcionesGenerales::restaurarEspeciales($reg["complementorazonsocial"]));
        }
        $retorno["nombre"] = str_replace("¥", "Ñ", stripslashes(\funcionesGenerales::restaurarEspeciales($reg["razonsocial"])));
        $retorno["nombre"] = \funcionesGenerales::borrarpalabrasAutomaticas($retorno["nombre"], $retorno["complementorazonsocial"]);
        $retorno["nombre"] = trim($retorno["nombre"]);
        $retorno["ape1"] = str_replace("¥", "Ñ", stripslashes(\funcionesGenerales::restaurarEspeciales($reg["apellido1"])));
        $retorno["ape2"] = str_replace("¥", "Ñ", stripslashes(\funcionesGenerales::restaurarEspeciales($reg["apellido2"])));
        $retorno["nom1"] = str_replace("¥", "Ñ", stripslashes(\funcionesGenerales::restaurarEspeciales($reg["nombre1"])));
        $retorno["nom2"] = str_replace("¥", "Ñ", stripslashes(\funcionesGenerales::restaurarEspeciales($reg["nombre2"])));
        
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
            }
        }
        
        //
        $nominicial = $retorno["nombre"];
        
        //
        $retorno["sigla"] = str_replace("¥", "Ñ", stripslashes(\funcionesGenerales::restaurarEspeciales((string)$reg["sigla"])));

        //
        $retorno["estadomatricula"] = $reg["ctrestmatricula"];
        $retorno["organizacion"] = $reg["organizacion"];
        $retorno["categoria"] = $reg["categoria"];
        $retorno["fechavencimiento"] = trim($reg["fecvigencia"]);
        
        $retorno["fechaperdidacalidadcomerciante"] = '';
        $retorno["perdidacalidadcomerciante"] = '';
        $retorno["reactivadaacto511"] = '';
        $retorno["fechaacto511"] = '';
        $retorno["fechadisolucion"] = '';
        $retorno["disueltaporacto510"] = '';
        $retorno["fechaacto510"] = '';
        $retorno["fechadisolucioncontrolbeneficios1756"] = '';
        $retorno["disueltaporvencimiento"] = '';
        
        if (
                trim($retorno["fechavencimiento"]) != '' &&
                $retorno["fechavencimiento"] != '99999999' && $retorno["fechavencimiento"] != '99999998'
        ) {
            if ($retorno["fechavencimiento"] < date("Ymd")) {
                $retorno["disueltaporvencimiento"] = 'si';
            }
        }
        
        //
        $retorno["inscripciones"] = array();
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
        $arrX = retornarRegistrosMysqliApi($mysqli, 'mreg_est_inscripciones', "matricula='" . ltrim(trim($retorno["matricula"]), "0") . "'", "fecharegistro,libro,registro,dupli");
        if ($arrX && !empty($arrX)) {
            foreach ($arrX as $x) {
                $ix++;
                if ($x["fecharegistro"] < '20170101' && $x["ctrrevoca"] == '1') {
                    $x["ctrrevoca"] = '0';
                }
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

                if (isset($x["tomo72"])) {
                    $retorno["inscripciones"][$ix]["tomo72"] = $x["tomo72"];
                    $retorno["inscripciones"][$ix]["folio72"] = $x["folio72"];
                    $retorno["inscripciones"][$ix]["registro72"] = $x["registro72"];
                }
                $retorno["inscripciones"][$ix]["anadirrazonsocial"] = retornarRegistroMysqliApi($mysqli, 'mreg_est_inscripciones_campos', "libro='" . $x["libro"] . "' and registro='" . $x["registro"] . "' and dupli='" . $x["dupli"] . "' and campo='anadirrazonsocial'", "contenido");
                $ind = $x["libro"] . '-' . $x["acto"];
                $retorno["inscripciones"][$ix]["est"] = $x["estado"];
                $retorno["inscripciones"][$ix]["nombreacto"] = '';
                $retorno["inscripciones"][$ix]["grupoacto"] = '';
                $retorno["inscripciones"][$ix]["esreforma"] = '';
                $retorno["inscripciones"][$ix]["esreformaespecial"] = '';
                $retorno["inscripciones"][$ix]["anotacionalcapital"] = '';
                $retorno["inscripciones"][$ix]["actosistemaanterior"] = $x["actosistemaanterior"];
                $retorno["inscripciones"][$ix]["vinculoafectado"] = $x["vinculoafectado"];
                $retorno["inscripciones"][$ix]["tipoidentificacionafectada"] = $x["tipoidentificacionafectada"];
                $retorno["inscripciones"][$ix]["identificacionafectada"] = $x["identificacionafectada"];
                $retorno["inscripciones"][$ix]["fechalimite"] = $x["fechalimite"];
                if ($x["idradicacion"] != '') {
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
                        $retorno["fechaperdidacalidadcomerciante"] = '';
                        $retorno["perdidacalidadcomerciante"] = '';
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

            }
        }

        //
        if ($retorno["organizacion"] == '01') {
            if ($retorno["nom1"] != '' && $retorno["ape1"] != '') {
                $retorno["nombre"] = $retorno["nom1"];
                if (trim($retorno["nom2"]) != '') {
                    $retorno["nombre"] .= ' ' . $retorno["nom2"];
                }
                if (trim($retorno["ape1"]) != '') {
                    $retorno["nombre"] .= ' ' . $retorno["ape1"];
                }
                if (trim($retorno["ape2"]) != '') {
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
                        $ya = 'si';
                    }
                }
                if ($ya == 'no') {
                    if ($enliquidacion == 'si') {
                        $retorno["nombre"] .= ' EN LIQUIDACION';
                        $ya = 'si';
                    }
                    if ($enreestructuracion == 'si') {
                        $retorno["nombre"] .= ' EN REESTRUCTURACION';
                        $ya = 'si';
                    }
                    if ($enreorganizacion == 'si') {
                        $retorno["nombre"] .= ' EN REORGANIZACION';
                        $ya = 'si';
                    }
                    if ($enliquidacionjudicial == 'si') {
                        $retorno["nombre"] .= ' EN LIQUIDACION JUDICIAL';
                        $ya = 'si';
                    }
                    if ($enliquidacionforsoza == 'si') {
                        $retorno["nombre"] .= ' EN LIQUIDACION FORZOSA';
                        $ya = 'si';
                    }
                    if ($enrecuperacion == 'si') {
                        $retorno["nombre"] .= ' EN RECUPERACION EMPRESARIAL';
                        $ya = 'si';
                    }
                }
                if ($ya == 'no') {
                    if (trim($retorno["fechavencimiento"]) != '' && $retorno["fechavencimiento"] != '99999999') {
                        if (trim($retorno["fechavencimiento"]) < date("Ymd")) {
                            $retorno["nombre"] .= ' EN LIQUIDACION';
                            $ya = 'si';
                        }
                    }
                }
            }
            if ($retorno["organizacion"] > '02' && ($retorno["categoria"] == '2' || $retorno["categoria"] == '3')) {
                if ($retorno["complementorazonsocial"] != '') {
                    $retorno["nombre"] .= ' ' . $retorno["complementorazonsocial"];
                    $ya = 'si';
                }
            }
        }

        if ($nominicial != $retorno["nombre"]) {
            if (isset($actualizar) && $actualizar == 'si') {
                $arrCampos = array (
                    'razonsocial'
                );
                $arrValores = array (
                    "'" . $retorno["nombre"] . "'"
                );
                regrabarRegistrosMysqliApi($mysqli, 'mreg_est_inscritos', $arrCampos, $arrValores, "matricula='" . $mat . "'");        
            }
        }
        // Cierra la conexión con MYSQL
        if ($dbx === null) {
            $mysqli->close();
        }

        //
        return $retorno["nombre"];
    }

}

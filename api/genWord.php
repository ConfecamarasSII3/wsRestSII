<?php
/**
 * Funci&oacute;n que imprime el recibo de caja
 *-
 * @param 	string		$numliq		N&uacute;mero de liquidaci&oacute;n
 */

function armarWord ($dbx,$datos,$formato) {
    if (substr(PHP_VERSION, 0, 1) == '5') {
        return armarWordPhp5($dbx,$datos,$formato);
    }
    if (substr(PHP_VERSION, 0, 1) == '7') {
        return armarWordPhp5($dbx,$datos,$formato);
    }
    if (substr(PHP_VERSION, 0, 1) == '8') {
        return armarWordPhp8($dbx,$datos,$formato);
    }
}

function armarWordPhp5 ($dbx,$datos,$formato) {
	require_once ($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common.php');
	require_once ($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common'.$_SESSION["generales"]["codigoempresa"].'.php');
	include_once ($_SESSION["generales"]["pathabsoluto"] . '/components/tbs_us/tbs_class.php');
	include_once ($_SESSION["generales"]["pathabsoluto"] . '/components/tbs_plugin_opentbs_1.9.6/tbs_plugin_opentbs.php');
	require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');

	// Imagen
        $GLOBALS["img"]= '';
        $GLOBALS["logo"]= '';
        $GLOBALS["img1"]= '';
        $GLOBALS["img2"]= '';
        $GLOBALS["img3"]= '';
        $GLOBALS["img4"]= '';
        
        if (isset($datos["img"])) {
            $GLOBALS["img"]=$datos["img"];
        }  else {
            if (file_exists($_SESSION["generales"]["pathabsoluto"] . '/images/logocamara' . $_SESSION["generales"]["codigoempresa"] . '.gif')) {
                $GLOBALS["img"] = $_SESSION["generales"]["pathabsoluto"] . '/images/logocamara' . $_SESSION["generales"]["codigoempresa"] . '.gif';
            } else {
                if (file_exists($_SESSION["generales"]["pathabsoluto"] . '/images/logocamara' . $_SESSION["generales"]["codigoempresa"] . '.jpg')) {
                    $GLOBALS["img"] = $_SESSION["generales"]["pathabsoluto"] . '/images/logocamara' . $_SESSION["generales"]["codigoempresa"] . '.jpg';                
                }
            }
        }
        
        if (isset($datos["logo"])) {
            $GLOBALS["logo"]=$datos["logo"];
        }  else {
            if (file_exists($_SESSION["generales"]["pathabsoluto"] . '/images/logocamara' . $_SESSION["generales"]["codigoempresa"] . '.gif')) {
                $GLOBALS["logo"] = $_SESSION["generales"]["pathabsoluto"] . '/images/logocamara' . $_SESSION["generales"]["codigoempresa"] . '.gif';
            } else {
                if (file_exists($_SESSION["generales"]["pathabsoluto"] . '/images/logocamara' . $_SESSION["generales"]["codigoempresa"] . '.jpg')) {
                    $GLOBALS["logo"] = $_SESSION["generales"]["pathabsoluto"] . '/images/logocamara' . $_SESSION["generales"]["codigoempresa"] . '.jpg';                
                }
            }
        }
	
	// Firmas
	if (isset($datos["img1"]) && $datos["img1"] != '') {
		$GLOBALS["img1"]=$datos["img1"];
	}
	if (isset($datos["img2"])  && $datos["img2"] != '') {
		$GLOBALS["img2"]=$datos["img2"];
	}
	if (isset($datos["img3"])  && $datos["img3"] != '') {
		$GLOBALS["img3"]=$datos["img3"];
	}
	if (isset($datos["img4"])  && $datos["img4"] != '') {
		$GLOBALS["img4"]=$datos["img4"];
	}
	
	//
	if (!defined("SLOGAN")) {
            define ("SLOGAN","");
        }
	
	// Datos de la organizacion 
 	$GLOBALS["orgnombre"] = RAZONSOCIAL;
 	$GLOBALS["orgnit"] = NIT;
 	$GLOBALS["orgdir1"] = DIRECCION1;
 	$GLOBALS["orgdir2"] = DIRECCION2;
 	$GLOBALS["orgtel1"] = PBX;
 	$GLOBALS["orgmun"] = retornarNombreMunicipioMysqliApi($dbx,MUNICIPIO);
 	$GLOBALS["orgrt"] = retornarNombreTablaBasicaMysqliApi($dbx,'bas_regimentributario', CONDICION_TRIBUTARIA);
 	$GLOBALS["orgemail"] = EMAIL_ATENCION_USUARIOS;
 	$GLOBALS["orgurl"] = WWW_ENTIDAD;
        $GLOBALS["firmamensaje"] = '';
        $GLOBALS["textofirma"] = '';
       
 	if (!defined('WWW_ENTIDAD_CONSULTAS')) {
            define ('WWW_ENTIDAD_CONSULTAS', "'" . WWW_ENTIDAD . "'");
        }
 	$GLOBALS["orgurlconsultaruta"]=WWW_ENTIDAD_CONSULTAS;
         
 	$GLOBALS["slogan"]=SLOGAN;
 	
 	// Firmantes de la organizacion - Rep Legal
 	$GLOBALS["orgreplt"]= retornarNombreTablaBasicaMysqliApi($dbx,'bas_tipoidentificacion', retornarClaveValorMysqliApi($dbx,'01.01.21'));
 	$GLOBALS["orgrepli"]=ltrim((string)retornarClaveValorMysqliApi($dbx,'01.01.22'),"0");
 	$GLOBALS["orgrepln"]= retornarNombreIdentificacionMysqliApi($dbx,$GLOBALS["orgrepli"]);
 	$GLOBALS["orgreplc"]=trim((string)retornarClaveValorMysqliApi($dbx,'01.01.23'));
 	
 	// Firmantes de la organizacion - Director Administrativo
 	$GLOBALS["orgadmi"]=ltrim((string)retornarClaveValorMysqliApi($dbx,'01.01.26'),"0");
 	$GLOBALS["orgadmn"]=retornarNombreIdentificacionMysqliApi($dbx,$GLOBALS["orgadmi"]);
 	$GLOBALS["orgadmc"]=trim((string)retornarClaveValorMysqliApi($dbx,'01.01.27'));
 	
 	// Firmantes de la organizacion - Director Jur&iacute;dico
 	$GLOBALS["orgdirji"]=ltrim((string)retornarClaveValorMysqliApi($dbx,'01.01.30'),"0");
 	$GLOBALS["orgdirjn"]= retornarNombreIdentificacionMysqliApi($dbx,$GLOBALS["orgdirji"]);
 	$GLOBALS["orgdirjc"]=trim((string)retornarClaveValorMysqliApi($dbx,'01.01.31'));

 	// Firmantes de la organizacion - Contador
 	$GLOBALS["orgconi"]=ltrim((string)retornarClaveValorMysqliApi($dbx,'01.01.34'),"0");
 	$GLOBALS["orgconn"]=retornarNombreIdentificacionMysqliApi($dbx,$GLOBALS["orgconi"]);
 	$GLOBALS["orgconc"]=ltrim((string)retornarClaveValorMysqliApi($dbx,'01.01.35'),"0");
 	$GLOBALS["orgcont"]=trim((string)retornarClaveValorMysqliApi($dbx,'01.01.33'));

 	// Firmantes de la organizacion - Revisor Fiscal
 	$GLOBALS["orgrfii"]=ltrim((string)retornarClaveValorMysqliApi($dbx,'01.01.38'),"0");
 	$GLOBALS["orgrfin"]=retornarNombreIdentificacionMysqliApi($dbx,$GLOBALS["orgrfii"]);
 	$GLOBALS["orgrfic"]=ltrim((string)retornarClaveValorMysqliApi($dbx,'01.01.39'),"0");
 	$GLOBALS["orgrfit"]=trim((string)retornarClaveValorMysqliApi($dbx,'01.01.37'));

 	// Firmantes de la organizacion - Recursos humanos
 	$GLOBALS["orgrhi"]=ltrim((string)retornarClaveValorMysqliApi($dbx,'01.01.42'),"0");
 	$GLOBALS["orgrhn"]=retornarNombreIdentificacionMysqliApi($dbx,$GLOBALS["orgrhi"]);
 	$GLOBALS["orgrhc"]=trim((string)retornarClaveValorMysqliApi($dbx,'01.01.43'));
 	
 	// Datos generales del documento
 	(isset ($datos["elaboro"])) ? $GLOBALS["elaboro"]=$datos["elaboro"] : $GLOBALS["elaboro"]='';
 	(isset ($datos["aprobo"])) ? $GLOBALS["aprobo"]=$datos["aprobo"] : $GLOBALS["aprobo"]='';
 	(isset ($datos["doctip"])) ? $GLOBALS["doctip"]=$datos["doctip"] : $GLOBALS["doctip"]='';
 	(isset ($datos["docnro"])) ? $GLOBALS["docnro"]=ltrim($datos["docnro"],"0") : $GLOBALS["docnro"]='';
 	(isset ($datos["docfecn"])) ? $GLOBALS["docfecn"]=$datos["docfecn"] : $GLOBALS["docfecn"]=''; 
 	$GLOBALS["docfecl"] = \funcionesGenerales::mostrarFechaLetras($GLOBALS["docfecn"]);
 	$GLOBALS["docfecl1"] = \funcionesGenerales::mostrarFechaLetras1($GLOBALS["docfecn"]);
 	(isset ($datos["docfecv"])) ? $GLOBALS["docfecv"]=$datos["docfecv"] : $GLOBALS["docfecv"]='';
 	(isset ($datos["docfece"])) ? $GLOBALS["docfece"]=$datos["docfece"] : $GLOBALS["docfece"]='';
 	(isset ($datos["docfeci"])) ? $GLOBALS["docfeci"]=$datos["docfeci"] : $GLOBALS["docfeci"]='';
 	(isset ($datos["docfecf"])) ? $GLOBALS["docfecf"]=$datos["docfecf"] : $GLOBALS["docfecf"]='';
 	(isset ($datos["docest"])) ? $GLOBALS["docest"]= retornarNombreTablaBasicaMysqliApi ($dbx,'bas_estadodocumentos',$datos["docest"]) : $GLOBALS["docest"]='';
 	(isset ($datos["docnprv"])) ? $GLOBALS["docnprv"]=$datos["docnprv"] : $GLOBALS["docnprv"]='';
 	
         
 	// Observaciones y detalles
 	(isset ($datos["docobs"])) ? $GLOBALS["docobs"]=\funcionesGenerales::utf8_decode($datos["docobs"]) : $GLOBALS["docobs"]='';
 	(isset ($datos["docdet"])) ? $GLOBALS["docdet"]=\funcionesGenerales::utf8_decode($datos["docdet"]) : $GLOBALS["docdet"]='';
 	(isset ($datos["doccom"])) ? $GLOBALS["doccom"]=\funcionesGenerales::utf8_decode($datos["doccom"]) : $GLOBALS["doccom"]='';
 	(isset ($datos["docobj"])) ? $GLOBALS["docobj"]=\funcionesGenerales::utf8_decode($datos["docobj"]) : $GLOBALS["docobj"]='';
 	(isset ($datos["docent"])) ? $GLOBALS["docent"]=\funcionesGenerales::utf8_decode($datos["docent"]) : $GLOBALS["docent"]='';
 	
 	// Programas y centros de costos
 	(isset ($datos["docprg"])) ? $GLOBALS["docprg"]=$datos["docprg"] : $GLOBALS["docprg"]='';
 	(isset ($datos["docprgn"])) ? $GLOBALS["docprgn"]=$datos["docprgn"] : $GLOBALS["docprgn"]='';
 	(isset ($datos["doccco"])) ? $GLOBALS["doccco"]=$datos["doccco"] : $GLOBALS["doccco"]='';
 	(isset ($datos["docccon"])) ? $GLOBALS["docccon"]=$datos["docccon"] : $GLOBALS["docccon"]='';
 	(isset ($datos["docfpag"])) ? $GLOBALS["docfpag"]=retornarNombreTablaBasicaMysqliApi($dbx,'bas_formaspago',$datos["docfpag"]) : $GLOBALS["docfpag"]='';
 	if (!isset ($datos["docfon"])) $datos["docfon"]='';
 	$GLOBALS["docfon"]='';
 	switch ($datos["docfon"]) {
 		case "1" : $GLOBALS["docfon"]='P&uacute;blico';break;
 		case "2" : $GLOBALS["docfon"]='Privado';break;
 		case "3" : $GLOBALS["docfon"]='Mixto';break;
 	}
 	
 	// Valores del documento
 	(isset ($datos["docvbr"])) ? $GLOBALS["docvbr"]=number_format($datos["docvbr"]) : $GLOBALS["docvbr"]='';
 	(isset ($datos["docvdes"])) ? $GLOBALS["docvdes"]=number_format($datos["docvdes"]) : $GLOBALS["docvdes"]='';
 	(isset ($datos["docpdes"])) ? $GLOBALS["docpdes"]=number_format($datos["docpdes"]) : $GLOBALS["docpdes"]='';
 	(isset ($datos["docviv"])) ? $GLOBALS["docviv"]=number_format($datos["docviv"]) : $GLOBALS["docviv"]='';
 	(isset ($datos["docvivnd"])) ? $GLOBALS["docvivnd"]=number_format($datos["docvivnd"]) : $GLOBALS["docvivnd"]='';
 	(isset ($datos["docvaiu"])) ? $GLOBALS["docvaiu"]=number_format($datos["docvaiu"]) : $GLOBALS["docvaiu"]='';
 	(isset ($datos["docvrtf"])) ? $GLOBALS["docvrtf"]=number_format($datos["docvrtf"]) : $GLOBALS["docvrtf"]='';
 	(isset ($datos["docvrimc"])) ? $GLOBALS["docvrimc"]=number_format($datos["docvrimc"]) : $GLOBALS["docvrimc"]='';
 	(isset ($datos["docvrcree"])) ? $GLOBALS["docvrcree"]=number_format($datos["docvrcree"]) : $GLOBALS["docvrcree"]='';
 	
 	(isset ($datos["docvrtiv"])) ? $GLOBALS["docvrtiv"] = number_format($datos["docvrtiv"]) : $GLOBALS["docvrtiv"]='';
 	(isset ($datos["docvrtivas"])) ? $GLOBALS["docvrtivas"] = number_format($datos["docvrtivas"]) : $GLOBALS["docvrtivas"]='';
 	(isset ($datos["docvrtic"])) ? $GLOBALS["docvrtic"] = number_format($datos["docvrtic"]) : $GLOBALS["docvrtic"]='';
 	(isset ($datos["docvnet"])) ? $GLOBALS["docvnet"] = number_format($datos["docvnet"]) : $GLOBALS["docvnet"]='';
 	(isset ($datos["docvtot"])) ? $GLOBALS["docvtot"] = number_format($datos["docvtot"]) : $GLOBALS["docvtot"]='';
 	(isset ($datos["docvbrl"])) ? $GLOBALS["docvbrl"] = \funcionesGenerales::montoEscrito($datos["docvbrl"]) : $GLOBALS["docvbrl"]='';
 	(isset ($datos["docvtotl"])) ? $GLOBALS["docvtotl"]= \funcionesGenerales::montoEscrito(round($datos["docvtotl"],0)) : $GLOBALS["docvtotl"]='';
 	
 	// Cuentas
 	(isset ($datos["docctiv"])) ? $GLOBALS["docctiv"]=$datos["docctiv"] : $GLOBALS["docctiv"]='';
 	(isset ($datos["docctivnd"])) ? $GLOBALS["docctivnd"]=$datos["docctivnd"] : $GLOBALS["docctivnd"]='';
 	(isset ($datos["docctimc"])) ? $GLOBALS["docctimc"]=$datos["docctimc"] : $GLOBALS["docctimc"]='';
 	(isset ($datos["docctrtf"])) ? $GLOBALS["docctrtf"]=$datos["docctrtf"] : $GLOBALS["docctrtf"]='';
 	(isset ($datos["docctrtiv"])) ? $GLOBALS["docctrtiv"]=$datos["docctrtiv"] : $GLOBALS["docctrtiv"]='';
 	(isset ($datos["docctcree"])) ? $GLOBALS["docctcree"]=$datos["docctcree"] : $GLOBALS["docctcree"]='';
 	(isset ($datos["doccttivas"])) ? $GLOBALS["doccttivas"]=$datos["doccttivas"] : $GLOBALS["doccttivas"]='';
 	(isset ($datos["doccttic"])) ? $GLOBALS["doccttic"]=$datos["doccttic"] : $GLOBALS["doccttic"]='';
 	(isset ($datos["doccttot"])) ? $GLOBALS["doccttot"]=$datos["doccttot"] : $GLOBALS["doccttot"]='';
 	
 	// Datos de proveedores
 	(isset ($datos["prvide"])) ? $GLOBALS["prvide"]=ltrim((string)$datos["prvide"],"0") : $GLOBALS["prvide"]='';
 	(isset ($datos["prvraz"])) ? $GLOBALS["prvraz"]=trim((string)$datos["prvraz"]) : $GLOBALS["prvraz"]='';
 	(isset ($datos["prvdir1"])) ? $GLOBALS["prvdir1"]=trim((string)$datos["prvdir1"]) : $GLOBALS["prvdir1"]='';
 	(isset ($datos["prvdir2"])) ? $GLOBALS["prvdir2"]=trim((string)$datos["prvdir2"]) : $GLOBALS["prvdir2"]='';
 	(isset ($datos["prvmun"])) ? $GLOBALS["prvmun"]=retornarNombreMunicipioMysqliApi($dbx,$datos["prvmun"]) : $GLOBALS["prvmun"]='';
 	(isset ($datos["prvtel1"])) ? $GLOBALS["prvtel1"]=trim((string)$datos["prvtel1"]) : $GLOBALS["prvtel1"]='';
 	(isset ($datos["prvtel2"])) ? $GLOBALS["prvtel2"]=trim((string)$datos["prvtel2"]) : $GLOBALS["prvtel2"]='';
 	(isset ($datos["prvmov"])) ? $GLOBALS["prvmov"]=trim((string)$datos["prvmov"]) : $GLOBALS["prvmov"]='';
 	(isset ($datos["prvemail"])) ? $GLOBALS["prvemail"]=trim((string)$datos["prvemail"]) : $GLOBALS["prvemail"]='';
 	(isset ($datos["prvrepli"])) ? $GLOBALS["prvrepli"]=ltrim((string)$datos["prvrepli"],"0") : $GLOBALS["prvrepli"]='';
 	(isset ($datos["prvrepln"])) ? $GLOBALS["prvrepln"]=trim((string)$datos["prvrepln"]) : $GLOBALS["prvrepln"]='';

 	// Datos de clientes
 	(isset ($datos["cliide"])) ? $GLOBALS["cliide"]=ltrim((string)$datos["cliide"],"0") : $GLOBALS["cliide"]='';
 	(isset ($datos["cliraz"])) ? $GLOBALS["cliraz"]=\funcionesGenerales::utf8_decode(trim((string)$datos["cliraz"])) : $GLOBALS["cliraz"]='';
 	(isset ($datos["clidir1"])) ? $GLOBALS["clidir1"]=\funcionesGenerales::utf8_decode(trim((string)$datos["clidir1"])) : $GLOBALS["clidir1"]='';
 	(isset ($datos["clidir2"])) ? $GLOBALS["clidir2"]=trim((string)$datos["clidir2"]) : $GLOBALS["clidir2"]='';
 	(isset ($datos["climun"])) ? $GLOBALS["climun"]=retornarNombreMunicipioMysqliApi($dbx,$datos["climun"]) : $GLOBALS["climun"]='';
 	(isset ($datos["clitel1"])) ? $GLOBALS["clitel1"]=trim((string)$datos["clitel1"]) : $GLOBALS["clitel1"]='';
 	(isset ($datos["clitel2"])) ? $GLOBALS["clitel2"]=trim((string)$datos["clitel2"]) : $GLOBALS["clitel2"]='';
 	(isset ($datos["cliemail"])) ? $GLOBALS["cliemail"]=\funcionesGenerales::utf8_decode(trim((string)$datos["cliemail"])) : $GLOBALS["cliemail"]='';
 	
 	(isset ($datos["regven"])) ? $GLOBALS["regven"]=trim((string)$datos["regven"]) : $GLOBALS["regven"]='';
 	(isset ($datos["regret"])) ? $GLOBALS["regret"]=trim((string)$datos["regret"]) : $GLOBALS["regret"]='';
 	(isset ($datos["retfte"])) ? $GLOBALS["retfte"]=trim((string)$datos["retfte"]) : $GLOBALS["retfte"]='';
 	
 	(isset ($datos["ano1"])) ? $GLOBALS["ano1"]=trim((string)$datos["ano1"]) : $GLOBALS["ano1"]='';
 	(isset ($datos["ano2"])) ? $GLOBALS["ano2"]=trim((string)$datos["ano2"]) : $GLOBALS["ano2"]='';
 	
 	if (!isset($datos["dev1"])) $datos["dev1"] = array ();
 	if (!isset($datos["dev2"])) $datos["dev2"] = array ();
 	
 	
 	if (!isset($datos["ceresp1"])) $datos["ceresp1"] = array ();
 	
 	
 	// Datos de items
 	$i=0;
 	$d=array();
 	$dev1=array();
 	$dev2=array();
 	$ceresp1=array();

 	//
 	if (!isset($datos["tb1"])) {
 		$datos["topetb1"]=0;
 		$datos["tb1"]=array();
 	} else {
 		if (!isset($datos["topetb1"])) {
 			$datos["topetb1"]=count($datos["tb1"]);
 		}
 	}
 	
 	//
 	if (isset($datos["tb1"])) {
 		foreach ($datos["tb1"] as $tb) {
 			$i++;
 			if (!isset($tb["codi"])) $tb["codi"] = '';
 			if (!isset($tb["cta"])) $tb["cta"] = '';
 			if (!isset($tb["nom"])) $tb["nom"] = '';
 			if (!isset($tb["ds"])) $tb["ds"] = '';
 			if (!isset($tb["um"])) $tb["um"] = '';
 			if (!isset($tb["cn"])) $tb["cn"] = '';
 			if (!isset($tb["vu"])) $tb["vu"] = '';
 			if (!isset($tb["pi"])) $tb["pi"] = '';
 			if (!isset($tb["paiu"])) $tb["paiu"] = '';
 			if (!isset($tb["vp"])) $tb["vp"] = '';
 			if (!isset($tb["vi"])) $tb["vi"] = '';
 			if (!isset($tb["vaiu"])) $tb["vaiu"] = '';
 			if (!isset($tb["vt"])) $tb["vt"] = '';
 			if (!isset($tb["cpres"])) $tb["cpres"] = '';
 			if (!isset($tb["npres"])) $tb["npres"] = '';
 			if (!isset($tb["cco"])) $tb["cco"] = '';
 			if (!isset($tb["prg"])) $tb["prg"] = '';
 			
 			(($tb["vu"]!='') && ($tb["vu"]!=0)) ? $tb["vu"]=number_format($tb["vu"],2) : $tb["vu"]='';
 			(($tb["pi"]!='') && ($tb["pi"]!=0)) ? $tb["pi"]=number_format($tb["pi"],2) : $tb["pi"]='';
 			(($tb["paiu"]!='') && ($tb["paiu"]!=0)) ? $tb["paiu"]=number_format($tb["paiu"],2) : $tb["paiu"]='';
 			(($tb["vp"]!='') && ($tb["vp"]!=0)) ? $tb["vp"]=number_format($tb["vp"],2) : $tb["vp"]='';
 			(($tb["vi"]!='') && ($tb["vi"]!=0)) ? $tb["vi"]=number_format($tb["vi"],2) : $tb["vi"]='';
 			(($tb["vaiu"]!='') && ($tb["vaiu"]!=0)) ? $tb["vaiu"]=number_format($tb["vaiu"],2) : $tb["vaiu"]='';
 			(($tb["vt"]!='') && ($tb["vt"]!=0)) ? $tb["vt"]=number_format($tb["vt"],2) : $tb["vt"]='';
 			
 			$d[] = array (
 				'codi' => $tb["codi"] , 
 				'cta' => $tb["cta"] , 
 				'nom' => \funcionesGenerales::utf8_decode($tb["nom"]) , 
 				'ds' => \funcionesGenerales::utf8_decode($tb["ds"]) ,
 				'um' => $tb["um"] , 
 				'cn' => $tb["cn"] , 
 				'vu' => $tb["vu"] , 
 				'pi' => $tb["pi"] , 
 				'paiu' => $tb["paiu"] , 
 				'vp' => $tb["vp"] , 
 				'vi' => $tb["vi"] ,
 				'vaiu' => $tb["vaiu"] , 
 				'vt' => $tb["vt"] , 
 				'cpres' => $tb["cpres"] , 
 				'npres' => $tb["npres"] ,
 				'cco' => $tb["cco"] , 
 				'prg' => $tb["prg"]
 			
 			);
 		}
 	}
 	
 	if ($i<$datos["topetb1"]) {
 		for ($j=$i+1;$j<=$datos["topetb1"];$j++) {
 			$d[] = array (
 				'codi' => '' , 
 				'cta' => '' , 
 				'nom' => '' , 
 				'ds' => '' , 
 				'um' => '' ,
 				'cn' => '' , 
 				'vu' => '' , 
 				'pi' => '' ,
 				'paiu' => '' , 
 				'vp' => '' , 
 				'vi' => '' , 
 				'vaiu' => '' , 
 				'vt' => '' , 
 				'cpres' => '' , 
 				'npres' => '',
 				'cco' => '',
 				'prg' => ''
 			); 			
 		}
 	}
 	
	// Datos de novedades
	if (isset($datos["tb2"])) {
		if (!isset($datos["topetb2"])) {
			$datos["topetb2"]=count($datos["tb2"]);
		}
	} else {
		$datos["tb2"]=array();
		$datos["topetb2"]=0;
	}
 	$d2=array ();
 	$j=0;
 	if (isset($datos["tb2"])) {
 		foreach ($datos["tb2"] as $tb) {
 			$j++;
 			(isset($tb["nvd"])) ? $d2[$j]["nvd"]=$tb["nvd"] : $d2[$j]["nvd"]='';
 			(isset($tb["ds"])) ? $d2[$j]["ds"]=\funcionesGenerales::utf8_decode($tb["ds"]) : $d2[$j]["ds"]='';
 			(isset($tb["nom"])) ? $d2[$j]["nom"]=\funcionesGenerales::utf8_decode($tb["nom"]) : $d2[$j]["nom"]='';
 			(isset($tb["cn"])) ? $d2[$j]["cn"]=$tb["cn"] : $d2[$j]["cn"]='';
 			(isset($tb["dev"])) ? $d2[$j]["dev"]=number_format($tb["dev"],2) : $d2[$j]["dev"]='';
 			(isset($tb["ded"])) ? $d2[$j]["ded"]=number_format($tb["ded"],2) : $d2[$j]["ded"]='';
 		}
 	}
 	if ($j<$datos["topetb2"]) {
 		for ($k=$j+1;$k<=$datos["topetb2"];$k++) {
 			$d2[] = array (
 				'nvd' => '' , 
 				'ds' => '' , 
 				'nom' => '' , 
 				'cn' => '' , 
 				'dev' => '' , 
 				'ded' => '' , 
 			); 			
 		}
 	}
 	
 	(isset ($datos["emptdev"])) ? $GLOBALS["emptdev"]=number_format($datos["emptdev"],2) : $GLOBALS["emptdev"]='';
 	(isset ($datos["emptded"])) ? $GLOBALS["emptded"]=number_format($datos["emptded"],2) : $GLOBALS["emptded"]='';
 	
 	// Datos de empleados
 	(isset ($datos["empt"])) ? $GLOBALS["empt"]=retornarNombreTablaBasicaMysqliApi($dbx,'bas_tipoidentificacion',$datos["empt"]) : $GLOBALS["empt"]='';
 	(isset ($datos["empi"])) ? $GLOBALS["empi"]=number_format($datos["empi"],0) : $GLOBALS["empi"]='';
 	(isset ($datos["empn"])) ? $GLOBALS["empn"]=\funcionesGenerales::utf8_decode(trim((string)$datos["empn"])) : $GLOBALS["empn"]='';
 	(isset ($datos["empn1"])) ? $GLOBALS["empn1"]=\funcionesGenerales::utf8_decode(trim((string)$datos["empn1"])) : $GLOBALS["empn1"]='';
 	(isset ($datos["empn2"])) ? $GLOBALS["empn2"]=\funcionesGenerales::utf8_decode(trim((string)$datos["empn2"])) : $GLOBALS["empn2"]='';
 	(isset ($datos["empa1"])) ? $GLOBALS["empa1"]=\funcionesGenerales::utf8_decode(trim((string)$datos["empa1"])) : $GLOBALS["empa1"]='';
 	(isset ($datos["empa2"])) ? $GLOBALS["empa2"]=\funcionesGenerales::utf8_decode(trim((string)$datos["empa2"])) : $GLOBALS["empa2"]='';
 	(isset ($datos["empfecn"])) ? $GLOBALS["empfecn"]=\funcionesGenerales::mostrarfecha($datos["empfecn"]) : $GLOBALS["empfecn"]='';
 	(isset ($datos["empfeci"])) ? $GLOBALS["empfeci"]=\funcionesGenerales::mostrarfecha($datos["empfeci"]) : $GLOBALS["empfeci"]='';
 	(isset ($datos["empfecr"])) ? $GLOBALS["empfecr"]=\funcionesGenerales::mostrarfecha($datos["empfecr"]) : $GLOBALS["empfecr"]='';
 	(isset ($datos["empcarn"])) ? $GLOBALS["empcarn"]=\funcionesGenerales::utf8_decode(trim((string)$datos["empcarn"])) : $GLOBALS["empcarn"]='';
 	(isset ($datos["empper"])) ? $GLOBALS["empper"]=$datos["empper"] : $GLOBALS["empper"]='';
 	(isset ($datos["empfun"])) ? $GLOBALS["empfun"]=$datos["empfun"] : $GLOBALS["empfun"]='';
 	(isset ($datos["emptc"])) ? $GLOBALS["emptc"]=retornarNombreTablaBasicaMysqliApi($dbx,'bas_tipoconnomina',$datos["emptc"]) : $GLOBALS["emptc"]='';
 	(isset ($datos["empms"])) ? $GLOBALS["empms"]=$datos["empms"] : $GLOBALS["empms"]='';
 	(isset ($datos["empprf"])) ? $GLOBALS["empprf"]=$datos["empprf"] : $GLOBALS["empprf"]='';
 	
 	// Afiliaciones del empleado
 	(isset ($datos["empfpen"])) ? $GLOBALS["empfpen"]=$datos["empfpen"] : $GLOBALS["empfpen"]='';
 	(isset ($datos["empeps"])) ? $GLOBALS["empeps"]=$datos["empeps"] : $GLOBALS["empeps"]='';
 	(isset ($datos["emparp"])) ? $GLOBALS["emparp"]=$datos["emparp"] : $GLOBALS["emparp"]='';
 	(isset ($datos["empfces"])) ? $GLOBALS["empfces"]=$datos["empfces"] : $GLOBALS["empfces"]='';
 	(isset ($datos["empprep"])) ? $GLOBALS["empprep"]=$datos["empprep"] : $GLOBALS["empprep"]='';
 	
 	// Salarios, sueldos, cesant&iacute;as, retenciones, devengados, deducidos, etc.
 	(isset ($datos["empsdo"])) ? $GLOBALS["empsdo"]=number_format($datos["empsdo"],0) : $GLOBALS["empsdo"]='';
 	(isset ($datos["empingt"])) ? $GLOBALS["empingt"]=number_format($datos["empingt"],0) : $GLOBALS["empingt"]='';
 	(isset ($datos["empingg"])) ? $GLOBALS["empingg"]=number_format($datos["empingg"],0) : $GLOBALS["empingg"]='';
 	(isset ($datos["empingng"])) ? $GLOBALS["empingng"]=number_format($datos["empingng"],0) : $GLOBALS["empingng"]='';
 	(isset ($datos["empret"])) ? $GLOBALS["empret"]=number_format($datos["empret"],0) : $GLOBALS["empret"]='';
 	(isset ($datos["empsal"])) ? $GLOBALS["empsal"]=number_format($datos["empsal"],0) : $GLOBALS["empsal"]='';
 	(isset ($datos["emppen"])) ? $GLOBALS["emppen"]=number_format($datos["emppen"],0) : $GLOBALS["emppen"]='';
 	(isset ($datos["empces"])) ? $GLOBALS["empces"]=number_format($datos["empces"],0) : $GLOBALS["empces"]='';
 	(isset ($datos["empices"])) ? $GLOBALS["empices"]=number_format($datos["empices"],0) : $GLOBALS["empices"]='';
 	
 	// Devolutivos y Desistimientos
 	(isset ($datos["tipodevolucion"])) ? $GLOBALS["tipodevolucion"]=$datos["tipodevolucion"] : $GLOBALS["tipodevolucion"]='';
 	(isset ($datos["expediente"])) ? $GLOBALS["expediente"]=$datos["expediente"] : $GLOBALS["expediente"]='';
 	(isset ($datos["numrec"])) ? $GLOBALS["numrec"]=$datos["numrec"] : $GLOBALS["numrec"]='';
 	(isset ($datos["razonsocial"])) ? $GLOBALS["razonsocial"]=\funcionesGenerales::utf8_decode($datos["razonsocial"]) : $GLOBALS["razonsocial"]='';
        (isset ($datos["nombreafectado"])) ? $GLOBALS["nombreafectado"]=\funcionesGenerales::utf8_decode($datos["nombreafectado"]) : $GLOBALS["nombreafectado"]='';
 	(isset ($datos["ident"])) ? $GLOBALS["ident"]=$datos["ident"] : $GLOBALS["ident"]='';
 	(isset ($datos["codbarras"])) ? $GLOBALS["codbarras"]=$datos["codbarras"] : $GLOBALS["codbarras"]='';
 	(isset ($datos["fecradica"])) ? $GLOBALS["fecradica"]=$datos["fecradica"] : $GLOBALS["fecradica"]='';
 	(isset ($datos["fecradl1"])) ? $GLOBALS["fecradl1"]=$datos["fecradl1"] : $GLOBALS["fecradl1"]='';
 	(isset ($datos["fecdevolucion"])) ? $GLOBALS["fecdevolucion"]= $datos["fecdevolucion"] : $GLOBALS["fecdevolucion"]='';
 	(isset ($datos["fecdevl1"])) ? $GLOBALS["fecdevl1"]=$datos["fecdevl1"] : $GLOBALS["fecdevl1"]='';
 	(isset ($datos["tipotramite"])) ? $GLOBALS["tipotramite"]=$datos["tipotramite"] : $GLOBALS["tipotramite"]='';
 	(isset ($datos["tipdoc"])) ? $GLOBALS["tipdoc"]=$datos["tipdoc"] : $GLOBALS["tipdoc"]='';
 	(isset ($datos["numdoc"])) ? $GLOBALS["numdoc"]=$datos["numdoc"] : $GLOBALS["numdoc"]='';
 	(isset ($datos["oridoc"])) ? $GLOBALS["oridoc"]=\funcionesGenerales::utf8_decode($datos["oridoc"]) : $GLOBALS["oridoc"]='';
 	(isset ($datos["motivos"])) ? $GLOBALS["motivos"]=\funcionesGenerales::utf8_decode($datos["motivos"]) : $GLOBALS["motivos"]='';
 	(isset ($datos["reingreso"])) ? $GLOBALS["reingreso"]=$datos["reingreso"] : $GLOBALS["reingreso"]='';
 	(isset ($datos["nomabo"])) ? $GLOBALS["nomabo"]=\funcionesGenerales::utf8_decode($datos["nomabo"]) : $GLOBALS["nomabo"]='';
 	(isset ($datos["cargoabogado"])) ? $GLOBALS["cargoabogado"]=\funcionesGenerales::utf8_decode($datos["cargoabogado"]) : $GLOBALS["cargoabogado"]='';
 	(isset ($datos["nuc"])) ? $GLOBALS["nuc"]=$datos["nuc"] : $GLOBALS["nuc"]='';
 	(isset ($datos["nin"])) ? $GLOBALS["nin"]=$datos["nin"] : $GLOBALS["nin"]='';
        (isset ($datos["valor"])) ? $GLOBALS["valor"] = $datos["valor"] : $GLOBALS["valor"]='';
        (isset ($datos["email"])) ? $GLOBALS["email"] = $datos["email"] : $GLOBALS["email"]='';
 	
 	//
 	foreach ($datos["dev1"] as $x1) {
 		$dev1[] = $x1;
 	}
 	
 	//
 	foreach ($datos["dev2"] as $x1) {
 		$dev2[] = $x1;
 	}
 	
 	// Cartulinas
 	(isset ($datos["nombrepropietario"])) ? $GLOBALS["nombrepropietario"]=\funcionesGenerales::utf8_decode($datos["nombrepropietario"]) : $GLOBALS["nombrepropietario"]='';
 	(isset ($datos["nombreestablecimiento"])) ? $GLOBALS["nombreestablecimiento"]=\funcionesGenerales::utf8_decode($datos["nombreestablecimiento"]) : $GLOBALS["nombreestablecimiento"]='';
 	
 	// Declaraciones de proponentes
 	(isset ($datos["ciudad"])) ? $GLOBALS["ciudad"]=\funcionesGenerales::utf8_decode($datos["ciudad"]) : $GLOBALS["ciudad"]='';
	(isset ($datos["docfecl"])) ? $GLOBALS["docfecl"]=$datos["docfecl"] : $GLOBALS["docfecl"]='';
	(isset ($datos["razonsocial"])) ? $GLOBALS["razonsocial"]=\funcionesGenerales::utf8_decode($datos["razonsocial"]) : $GLOBALS["razonsocial"]='';
	(isset ($datos["ident"])) ? $GLOBALS["ident"]=$datos["ident"] : $GLOBALS["ident"]='';
	
	//
	(isset ($datos["tiposc"])) ? $GLOBALS["tiposc"]=$datos["tiposc"] : $GLOBALS["tiposc"]='';
	(isset ($datos["relacionsc"])) ? $GLOBALS["relacionsc"]=\funcionesGenerales::utf8_decode($datos["relacionsc"]) : $GLOBALS["relacionsc"]='';
	
	//
	(isset ($datos["tamanoempresa"])) ? $GLOBALS["tamanoempresa"]=$datos["tamanoempresa"] : $GLOBALS["tamanoempresa"]='';
	
	//
	(isset ($datos["secuenciacontrato"])) ? $GLOBALS["secuenciacontrato"]=$datos["secuenciacontrato"] : $GLOBALS["secuenciacontrato"]='';
	(isset ($datos["nombrecontratante"])) ? $GLOBALS["nombrecontratante"]=\funcionesGenerales::utf8_decode($datos["nombrecontratante"]) : $GLOBALS["nombrecontratante"]='';
	(isset ($datos["nombrecontratista"])) ? $GLOBALS["nombrecontratista"]=\funcionesGenerales::utf8_decode($datos["nombrecontratista"]) : $GLOBALS["nombrecontratista"]='';
	(isset ($datos["modalidadcontratacion"])) ? $GLOBALS["modalidadcontratacion"]=$datos["modalidadcontratacion"] : $GLOBALS["modalidadcontratacion"]='';	
	(isset ($datos["valorsmmlv"])) ? $GLOBALS["valorsmmlv"]=$datos["valorsmmlv"] : $GLOBALS["valorsmmlv"]='';
        (isset ($datos["valorpesos"])) ? $GLOBALS["valorpesos"]=$datos["valorpesos"] : $GLOBALS["valorpesos"]='';
	(isset ($datos["unspsc"])) ? $GLOBALS["unspsc"]=$datos["unspsc"] : $GLOBALS["unspsc"]='';
	
	//
	(isset ($datos["fechacorte"])) ? $GLOBALS["fechacorte"]=$datos["fechacorte"] : $GLOBALS["fechacorte"]='';
	(isset ($datos["actcte"])) ? $GLOBALS["actcte"]=$datos["actcte"] : $GLOBALS["actcte"]='';
        (isset ($datos["actnocte"])) ? $GLOBALS["actnocte"]=$datos["actnocte"] : $GLOBALS["actnocte"]='';
	(isset ($datos["fijnet"])) ? $GLOBALS["fijnet"]=$datos["fijnet"] : $GLOBALS["fijnet"]='';
	(isset ($datos["actotr"])) ? $GLOBALS["actotr"]=$datos["actotr"] : $GLOBALS["actotr"]='';
	(isset ($datos["actval"])) ? $GLOBALS["actval"]=$datos["actval"] : $GLOBALS["actval"]='';
	(isset ($datos["acttot"])) ? $GLOBALS["acttot"]=$datos["acttot"] : $GLOBALS["acttot"]='';
	(isset ($datos["pascte"])) ? $GLOBALS["pascte"]=$datos["pascte"] : $GLOBALS["pascte"]='';
	(isset ($datos["paslar"])) ? $GLOBALS["paslar"]=$datos["paslar"] : $GLOBALS["paslar"]='';
	(isset ($datos["pastot"])) ? $GLOBALS["pastot"]=$datos["pastot"] : $GLOBALS["pastot"]='';
	(isset ($datos["patnet"])) ? $GLOBALS["patnet"]=$datos["patnet"] : $GLOBALS["patnet"]='';
	(isset ($datos["paspat"])) ? $GLOBALS["paspat"]=$datos["paspat"] : $GLOBALS["paspat"]='';
        (isset ($datos["balsoc"])) ? $GLOBALS["balsoc"]=$datos["balsoc"] : $GLOBALS["balsoc"]='';
	(isset ($datos["ingope"])) ? $GLOBALS["ingope"]=$datos["ingope"] : $GLOBALS["ingope"]='';
	(isset ($datos["ingnoope"])) ? $GLOBALS["ingnoope"]=$datos["ingnoope"] : $GLOBALS["ingnoope"]='';
	(isset ($datos["gasope"])) ? $GLOBALS["gasope"]=$datos["gasope"] : $GLOBALS["gasope"]='';
	(isset ($datos["gasnoope"])) ? $GLOBALS["gasnoope"]=$datos["gasnoope"] : $GLOBALS["gasnoope"]='';
	(isset ($datos["cosven"])) ? $GLOBALS["cosven"]=$datos["cosven"] : $GLOBALS["cosven"]='';
	(isset ($datos["utiope"])) ? $GLOBALS["utiope"]=$datos["utiope"] : $GLOBALS["utiope"]='';
	(isset ($datos["utinet"])) ? $GLOBALS["utinet"]=$datos["utinet"] : $GLOBALS["utinet"]='';
	(isset ($datos["gasint"])) ? $GLOBALS["gasint"]=$datos["gasint"] : $GLOBALS["gasint"]='';
        (isset ($datos["gasimp"])) ? $GLOBALS["gasimp"]=$datos["gasimp"] : $GLOBALS["gasimp"]='';
	(isset ($datos["indliq"])) ? $GLOBALS["indliq"]=$datos["indliq"] : $GLOBALS["indliq"]='';
	(isset ($datos["nivend"])) ? $GLOBALS["nivend"]=$datos["nivend"] : $GLOBALS["nivend"]='';
	(isset ($datos["razcob"])) ? $GLOBALS["razcob"]=$datos["razcob"] : $GLOBALS["razcob"]='';
	(isset ($datos["renpat"])) ? $GLOBALS["renpat"]=$datos["renpat"] : $GLOBALS["renpat"]='';
	(isset ($datos["renact"])) ? $GLOBALS["renact"]=$datos["renact"] : $GLOBALS["renact"]='';
	
	// Certificados especiales
	(isset ($datos["ceresp_nombrecamara"])) ? $GLOBALS["ceresp_nombrecamara"]=\funcionesGenerales::utf8_decode($datos["ceresp_nombrecamara"]) : $GLOBALS["ceresp_nombrecamara"]='';
	(isset ($datos["ceresp_direccioncamara"])) ? $GLOBALS["ceresp_direccioncamara"]=\funcionesGenerales::utf8_decode($datos["ceresp_direccioncamara"]) : $GLOBALS["ceresp_direccioncamara"]='';
	(isset ($datos["ceresp_ciudadcamara"])) ? $GLOBALS["ceresp_ciudadcamara"]=\funcionesGenerales::utf8_decode($datos["ceresp_ciudadcamara"]) : $GLOBALS["ceresp_ciudadcamara"]='';
	(isset ($datos["ceresp_municipiocamara"])) ? $GLOBALS["ceresp_municipiocamara"]=$datos["ceresp_municipiocamara"] : $GLOBALS["ceresp_municipiocamara"]='';
	(isset ($datos["ceresp_telefonocamara"])) ? $GLOBALS["ceresp_telefonocamara"]=$datos["ceresp_telefonocamara"] : $GLOBALS["ceresp_telefonocamara"]='';
	(isset ($datos["ceresp_emailcamara"])) ? $GLOBALS["ceresp_emailcamara"]=\funcionesGenerales::utf8_decode($datos["ceresp_emailcamara"]) : $GLOBALS["ceresp_emailcamara"]='';
	(isset ($datos["ceresp_codigoverificacion"])) ? $GLOBALS["ceresp_codigoverificacion"]=$datos["ceresp_codigoverificacion"] : $GLOBALS["ceresp_codigoverificacion"]='';
	(isset ($datos["ceresp_sitioverificacion"])) ? $GLOBALS["ceresp_sitioverificacion"]=$datos["ceresp_sitioverificacion"] : $GLOBALS["ceresp_sitioverificacion"]='';
	(isset ($datos["ceresp_tipocertificacion"])) ? $GLOBALS["ceresp_tipocertificacion"]=\funcionesGenerales::utf8_decode($datos["ceresp_tipocertificacion"]) : $GLOBALS["ceresp_tipocertificacion"]='';
	(isset ($datos["ceresp_explicacion"])) ? $GLOBALS["ceresp_explicacion"]=\funcionesGenerales::utf8_decode($datos["ceresp_explicacion"]) : $GLOBALS["ceresp_explicacion"]='';
	(isset ($datos["ceresp_fechaexpedicion"])) ? $GLOBALS["ceresp_fechaexpedicion"]=$datos["ceresp_fechaexpedicion"] : $GLOBALS["ceresp_fechaexpedicion"]='';
	(isset ($datos["ceresp_fechaletras"])) ? $GLOBALS["ceresp_fechaletras"]=$datos["ceresp_fechaletras"] : $GLOBALS["ceresp_fechaletras"]='';
	(isset ($datos["ceresp_fechasolicitud"])) ? $GLOBALS["ceresp_fechasolicitud"]=$datos["ceresp_fechasolicitud"] : $GLOBALS["ceresp_fechasolicitud"]='';
	(isset ($datos["ceresp_numerorecibo"])) ? $GLOBALS["ceresp_numerorecibo"]=$datos["ceresp_numerorecibo"] : $GLOBALS["ceresp_numerorecibo"]='';
	(isset ($datos["ceresp_numerooperacion"])) ? $GLOBALS["ceresp_numerooperacion"]=$datos["ceresp_numerooperacion"] : $GLOBALS["ceresp_numerooperacion"]='';
	(isset ($datos["ceresp_referencia"])) ? $GLOBALS["ceresp_referencia"]=$datos["ceresp_referencia"] : $GLOBALS["ceresp_referencia"]='';
	(isset ($datos["ceresp_nombrecliente"])) ? $GLOBALS["ceresp_nombrecliente"]=\funcionesGenerales::utf8_decode($datos["ceresp_nombrecliente"]) : $GLOBALS["ceresp_nombrecliente"]='';
	(isset ($datos["ceresp_identificacioncliente"])) ? $GLOBALS["ceresp_identificacioncliente"]=$datos["ceresp_identificacioncliente"] : $GLOBALS["ceresp_identificacioncliente"]='';
	(isset ($datos["ceresp_expediente"])) ? $GLOBALS["ceresp_expediente"]=$datos["ceresp_expediente"] : $GLOBALS["ceresp_expediente"]='';
	(isset ($datos["ceresp_razonsocial"])) ? $GLOBALS["ceresp_razonsocial"]=\funcionesGenerales::utf8_decode($datos["ceresp_razonsocial"]) : $GLOBALS["ceresp_razonsocial"]='';
	(isset ($datos["ceresp_identificacion"])) ? $GLOBALS["ceresp_identificacion"]=$datos["ceresp_identificacion"] : $GLOBALS["ceresp_identificacion"]='';
	(isset ($datos["ceresp_jefejuridico"])) ? $GLOBALS["ceresp_jefejuridico"]=\funcionesGenerales::utf8_decode($datos["ceresp_jefejuridico"]) : $GLOBALS["ceresp_jefejuridico"]='';
	foreach ($datos["ceresp1"] as $x1) {
		$ceresp1[] = $x1;
	}
	
        // Formato responsabilidades tributarias
        $GLOBALS["razonsocial"] = strtoupper($datos["razonsocial"]);
        $GLOBALS["nombre"] = strtoupper($datos["nombre"]);
        $GLOBALS["tipoid"] = $datos["tipoid"];
        $GLOBALS["identificacion"] = $datos["identificacion"];
        $GLOBALS["ciuexp"] = $datos["ciuexp"];
        $GLOBALS["dptoexp"] = $datos["dptoexp"];
        $GLOBALS["paiexp"] = $datos["paiexp"];
        $GLOBALS["responsabilidades"] = $datos["responsabilidades"];
        if (isset($datos["firmamensaje"])) {
            $GLOBALS["firmamensaje"] = $datos["firmamensaje"];
        }  else {
            $GLOBALS["firmamensaje"] = "                        ";
        }
        if (isset($datos["textofirma"])) {
            $GLOBALS["textofirma"] = $datos["textofirma"];
        }
	
	$TBS = new clsTinyButStrong;	
	$TBS->Plugin(TBS_INSTALL, OPENTBS_PLUGIN);	
	$TBS->LoadTemplate($formato);
	$TBS->MergeBlock('a1','array',$d);
	$TBS->MergeBlock('dev1','array',$dev1);
	$TBS->MergeBlock('dev2','array',$dev2);
	$TBS->MergeBlock('ceresp1','array',$ceresp1);
	
	if (isset($datos["logo"]) && trim($datos["logo"])!='') {
		$TBS->PlugIn(OPENTBS_CHANGE_PICTURE,'XXXXXX',$GLOBALS["img"]);
	}
        
	if (isset($datos["img1"]) && trim($datos["img1"])!='') {
		$TBS->PlugIn(OPENTBS_CHANGE_PICTURE,'XXXXXX',$GLOBALS["img1"]);
	}
        $name1 = $_SESSION["generales"]["codigoempresa"] . '-' . session_id() . date("Ymd") . date ("His") . '.docx';
	$name = $_SESSION["generales"]["pathabsoluto"] . '/tmp/' . $name1;
	$TBS->Show(OPENTBS_FILE, $name);
        unset ($TBS);
	return $name1;
}
    
function armarWordPhp8($dbx,$datos,$formato) {
        
	require_once ($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common.php');
	require_once ($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common'.$_SESSION["generales"]["codigoempresa"].'.php');
	include_once ($_SESSION["generales"]["pathabsoluto"] . '/components/tbs_3152/tbs_class.php');
	include_once ($_SESSION["generales"]["pathabsoluto"] . '/components/tbs_plugin_opentbs_1.12.1/tbs_plugin_opentbs.php');
	require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');

	// Imagen
        $GLOBALS["img"]= '';
        $GLOBALS["logo"]= '';
        $GLOBALS["img1"]= '';
        $GLOBALS["img2"]= '';
        $GLOBALS["img3"]= '';
        $GLOBALS["img4"]= '';
        
        if (isset($datos["img"])) {
            $GLOBALS["img"]=$datos["img"];
        }  else {
            if (file_exists($_SESSION["generales"]["pathabsoluto"] . '/images/logocamara' . $_SESSION["generales"]["codigoempresa"] . '.gif')) {
                $GLOBALS["img"] = $_SESSION["generales"]["pathabsoluto"] . '/images/logocamara' . $_SESSION["generales"]["codigoempresa"] . '.gif';
            } else {
                if (file_exists($_SESSION["generales"]["pathabsoluto"] . '/images/logocamara' . $_SESSION["generales"]["codigoempresa"] . '.jpg')) {
                    $GLOBALS["img"] = $_SESSION["generales"]["pathabsoluto"] . '/images/logocamara' . $_SESSION["generales"]["codigoempresa"] . '.jpg';                
                }
            }
        }
        
        if (isset($datos["logo"])) {
            $GLOBALS["logo"]=$datos["logo"];
        }  else {
            if (file_exists($_SESSION["generales"]["pathabsoluto"] . '/images/logocamara' . $_SESSION["generales"]["codigoempresa"] . '.gif')) {
                $GLOBALS["logo"] = $_SESSION["generales"]["pathabsoluto"] . '/images/logocamara' . $_SESSION["generales"]["codigoempresa"] . '.gif';
            } else {
                if (file_exists($_SESSION["generales"]["pathabsoluto"] . '/images/logocamara' . $_SESSION["generales"]["codigoempresa"] . '.jpg')) {
                    $GLOBALS["logo"] = $_SESSION["generales"]["pathabsoluto"] . '/images/logocamara' . $_SESSION["generales"]["codigoempresa"] . '.jpg';                
                }
            }
        }
	
	// Firmas
	if (isset($datos["img1"]) && $datos["img1"] != '') {
		$GLOBALS["img1"]=$datos["img1"];
	}
	if (isset($datos["img2"])  && $datos["img2"] != '') {
		$GLOBALS["img2"]=$datos["img2"];
	}
	if (isset($datos["img3"])  && $datos["img3"] != '') {
		$GLOBALS["img3"]=$datos["img3"];
	}
	if (isset($datos["img4"])  && $datos["img4"] != '') {
		$GLOBALS["img4"]=$datos["img4"];
	}
	
	//
	if (!defined("SLOGAN")) {
            define ("SLOGAN","");
        }
	
	// Datos de la organizacion 
 	$GLOBALS["orgnombre"] = RAZONSOCIAL;
 	$GLOBALS["orgnit"] = NIT;
 	$GLOBALS["orgdir1"] = DIRECCION1;
 	$GLOBALS["orgdir2"] = DIRECCION2;
 	$GLOBALS["orgtel1"] = PBX;
 	$GLOBALS["orgmun"] = retornarNombreMunicipioMysqliApi($dbx,MUNICIPIO);
 	$GLOBALS["orgrt"] = retornarNombreTablaBasicaMysqliApi($dbx,'bas_regimentributario', CONDICION_TRIBUTARIA);
 	$GLOBALS["orgemail"] = EMAIL_ATENCION_USUARIOS;
 	$GLOBALS["orgurl"] = WWW_ENTIDAD;
        $GLOBALS["firmamensaje"] = '';
        $GLOBALS["textofirma"] = '';
       
 	if (!defined('WWW_ENTIDAD_CONSULTAS')) {
            define ('WWW_ENTIDAD_CONSULTAS', "'" . WWW_ENTIDAD . "'");
        }
 	$GLOBALS["orgurlconsultaruta"]=WWW_ENTIDAD_CONSULTAS;
         
 	$GLOBALS["slogan"]=SLOGAN;
 	
 	// Firmantes de la organizacion - Rep Legal
 	$GLOBALS["orgreplt"]= retornarNombreTablaBasicaMysqliApi($dbx,'bas_tipoidentificacion', retornarClaveValorMysqliApi($dbx,'01.01.21'));
 	$GLOBALS["orgrepli"]=ltrim((string)retornarClaveValorMysqliApi($dbx,'01.01.22'),"0");
 	$GLOBALS["orgrepln"]= retornarNombreIdentificacionMysqliApi($dbx,$GLOBALS["orgrepli"]);
 	$GLOBALS["orgreplc"]=trim((string)retornarClaveValorMysqliApi($dbx,'01.01.23'));
 	
 	// Firmantes de la organizacion - Director Administrativo
 	$GLOBALS["orgadmi"]=ltrim((string)retornarClaveValorMysqliApi($dbx,'01.01.26'),"0");
 	$GLOBALS["orgadmn"]=retornarNombreIdentificacionMysqliApi($dbx,$GLOBALS["orgadmi"]);
 	$GLOBALS["orgadmc"]=trim((string)retornarClaveValorMysqliApi($dbx,'01.01.27'));
 	
 	// Firmantes de la organizacion - Director Jur&iacute;dico
 	$GLOBALS["orgdirji"]=ltrim((string)retornarClaveValorMysqliApi($dbx,'01.01.30'),"0");
 	$GLOBALS["orgdirjn"]= retornarNombreIdentificacionMysqliApi($dbx,$GLOBALS["orgdirji"]);
 	$GLOBALS["orgdirjc"]=trim((string)retornarClaveValorMysqliApi($dbx,'01.01.31'));

 	// Firmantes de la organizacion - Contador
 	$GLOBALS["orgconi"]=ltrim((string)retornarClaveValorMysqliApi($dbx,'01.01.34'),"0");
 	$GLOBALS["orgconn"]=retornarNombreIdentificacionMysqliApi($dbx,$GLOBALS["orgconi"]);
 	$GLOBALS["orgconc"]=ltrim((string)retornarClaveValorMysqliApi($dbx,'01.01.35'),"0");
 	$GLOBALS["orgcont"]=trim((string)retornarClaveValorMysqliApi($dbx,'01.01.33'));

 	// Firmantes de la organizacion - Revisor Fiscal
 	$GLOBALS["orgrfii"]=ltrim((string)retornarClaveValorMysqliApi($dbx,'01.01.38'),"0");
 	$GLOBALS["orgrfin"]=retornarNombreIdentificacionMysqliApi($dbx,$GLOBALS["orgrfii"]);
 	$GLOBALS["orgrfic"]=ltrim((string)retornarClaveValorMysqliApi($dbx,'01.01.39'),"0");
 	$GLOBALS["orgrfit"]=trim((string)retornarClaveValorMysqliApi($dbx,'01.01.37'));

 	// Firmantes de la organizacion - Recursos humanos
 	$GLOBALS["orgrhi"]=ltrim((string)retornarClaveValorMysqliApi($dbx,'01.01.42'),"0");
 	$GLOBALS["orgrhn"]=retornarNombreIdentificacionMysqliApi($dbx,$GLOBALS["orgrhi"]);
 	$GLOBALS["orgrhc"]=trim((string)retornarClaveValorMysqliApi($dbx,'01.01.43'));
 	
 	// Datos generales del documento
 	(isset ($datos["elaboro"])) ? $GLOBALS["elaboro"]=$datos["elaboro"] : $GLOBALS["elaboro"]='';
 	(isset ($datos["aprobo"])) ? $GLOBALS["aprobo"]=$datos["aprobo"] : $GLOBALS["aprobo"]='';
 	(isset ($datos["doctip"])) ? $GLOBALS["doctip"]=$datos["doctip"] : $GLOBALS["doctip"]='';
 	(isset ($datos["docnro"])) ? $GLOBALS["docnro"]=ltrim($datos["docnro"],"0") : $GLOBALS["docnro"]='';
 	(isset ($datos["docfecn"])) ? $GLOBALS["docfecn"]=$datos["docfecn"] : $GLOBALS["docfecn"]=''; 
 	$GLOBALS["docfecl"] = \funcionesGenerales::mostrarFechaLetras($GLOBALS["docfecn"]);
 	$GLOBALS["docfecl1"] = \funcionesGenerales::mostrarFechaLetras1($GLOBALS["docfecn"]);
 	(isset ($datos["docfecv"])) ? $GLOBALS["docfecv"]=$datos["docfecv"] : $GLOBALS["docfecv"]='';
 	(isset ($datos["docfece"])) ? $GLOBALS["docfece"]=$datos["docfece"] : $GLOBALS["docfece"]='';
 	(isset ($datos["docfeci"])) ? $GLOBALS["docfeci"]=$datos["docfeci"] : $GLOBALS["docfeci"]='';
 	(isset ($datos["docfecf"])) ? $GLOBALS["docfecf"]=$datos["docfecf"] : $GLOBALS["docfecf"]='';
 	(isset ($datos["docest"])) ? $GLOBALS["docest"]= retornarNombreTablaBasicaMysqliApi ($dbx,'bas_estadodocumentos',$datos["docest"]) : $GLOBALS["docest"]='';
 	(isset ($datos["docnprv"])) ? $GLOBALS["docnprv"]=$datos["docnprv"] : $GLOBALS["docnprv"]='';
 	
         
 	// Observaciones y detalles
 	(isset ($datos["docobs"])) ? $GLOBALS["docobs"]=\funcionesGenerales::utf8_decode($datos["docobs"]) : $GLOBALS["docobs"]='';
 	(isset ($datos["docdet"])) ? $GLOBALS["docdet"]=\funcionesGenerales::utf8_decode($datos["docdet"]) : $GLOBALS["docdet"]='';
 	(isset ($datos["doccom"])) ? $GLOBALS["doccom"]=\funcionesGenerales::utf8_decode($datos["doccom"]) : $GLOBALS["doccom"]='';
 	(isset ($datos["docobj"])) ? $GLOBALS["docobj"]=\funcionesGenerales::utf8_decode($datos["docobj"]) : $GLOBALS["docobj"]='';
 	(isset ($datos["docent"])) ? $GLOBALS["docent"]=\funcionesGenerales::utf8_decode($datos["docent"]) : $GLOBALS["docent"]='';
 	
 	// Programas y centros de costos
 	(isset ($datos["docprg"])) ? $GLOBALS["docprg"]=$datos["docprg"] : $GLOBALS["docprg"]='';
 	(isset ($datos["docprgn"])) ? $GLOBALS["docprgn"]=$datos["docprgn"] : $GLOBALS["docprgn"]='';
 	(isset ($datos["doccco"])) ? $GLOBALS["doccco"]=$datos["doccco"] : $GLOBALS["doccco"]='';
 	(isset ($datos["docccon"])) ? $GLOBALS["docccon"]=$datos["docccon"] : $GLOBALS["docccon"]='';
 	(isset ($datos["docfpag"])) ? $GLOBALS["docfpag"]=retornarNombreTablaBasicaMysqliApi($dbx,'bas_formaspago',$datos["docfpag"]) : $GLOBALS["docfpag"]='';
 	if (!isset ($datos["docfon"])) $datos["docfon"]='';
 	$GLOBALS["docfon"]='';
 	switch ($datos["docfon"]) {
 		case "1" : $GLOBALS["docfon"]='P&uacute;blico';break;
 		case "2" : $GLOBALS["docfon"]='Privado';break;
 		case "3" : $GLOBALS["docfon"]='Mixto';break;
 	}
 	
 	// Valores del documento
 	(isset ($datos["docvbr"])) ? $GLOBALS["docvbr"]=number_format($datos["docvbr"]) : $GLOBALS["docvbr"]='';
 	(isset ($datos["docvdes"])) ? $GLOBALS["docvdes"]=number_format($datos["docvdes"]) : $GLOBALS["docvdes"]='';
 	(isset ($datos["docpdes"])) ? $GLOBALS["docpdes"]=number_format($datos["docpdes"]) : $GLOBALS["docpdes"]='';
 	(isset ($datos["docviv"])) ? $GLOBALS["docviv"]=number_format($datos["docviv"]) : $GLOBALS["docviv"]='';
 	(isset ($datos["docvivnd"])) ? $GLOBALS["docvivnd"]=number_format($datos["docvivnd"]) : $GLOBALS["docvivnd"]='';
 	(isset ($datos["docvaiu"])) ? $GLOBALS["docvaiu"]=number_format($datos["docvaiu"]) : $GLOBALS["docvaiu"]='';
 	(isset ($datos["docvrtf"])) ? $GLOBALS["docvrtf"]=number_format($datos["docvrtf"]) : $GLOBALS["docvrtf"]='';
 	(isset ($datos["docvrimc"])) ? $GLOBALS["docvrimc"]=number_format($datos["docvrimc"]) : $GLOBALS["docvrimc"]='';
 	(isset ($datos["docvrcree"])) ? $GLOBALS["docvrcree"]=number_format($datos["docvrcree"]) : $GLOBALS["docvrcree"]='';
 	
 	(isset ($datos["docvrtiv"])) ? $GLOBALS["docvrtiv"] = number_format($datos["docvrtiv"]) : $GLOBALS["docvrtiv"]='';
 	(isset ($datos["docvrtivas"])) ? $GLOBALS["docvrtivas"] = number_format($datos["docvrtivas"]) : $GLOBALS["docvrtivas"]='';
 	(isset ($datos["docvrtic"])) ? $GLOBALS["docvrtic"] = number_format($datos["docvrtic"]) : $GLOBALS["docvrtic"]='';
 	(isset ($datos["docvnet"])) ? $GLOBALS["docvnet"] = number_format($datos["docvnet"]) : $GLOBALS["docvnet"]='';
 	(isset ($datos["docvtot"])) ? $GLOBALS["docvtot"] = number_format($datos["docvtot"]) : $GLOBALS["docvtot"]='';
 	(isset ($datos["docvbrl"])) ? $GLOBALS["docvbrl"] = \funcionesGenerales::montoEscrito($datos["docvbrl"]) : $GLOBALS["docvbrl"]='';
 	(isset ($datos["docvtotl"])) ? $GLOBALS["docvtotl"]= \funcionesGenerales::montoEscrito(round($datos["docvtotl"],0)) : $GLOBALS["docvtotl"]='';
 	
 	// Cuentas
 	(isset ($datos["docctiv"])) ? $GLOBALS["docctiv"]=$datos["docctiv"] : $GLOBALS["docctiv"]='';
 	(isset ($datos["docctivnd"])) ? $GLOBALS["docctivnd"]=$datos["docctivnd"] : $GLOBALS["docctivnd"]='';
 	(isset ($datos["docctimc"])) ? $GLOBALS["docctimc"]=$datos["docctimc"] : $GLOBALS["docctimc"]='';
 	(isset ($datos["docctrtf"])) ? $GLOBALS["docctrtf"]=$datos["docctrtf"] : $GLOBALS["docctrtf"]='';
 	(isset ($datos["docctrtiv"])) ? $GLOBALS["docctrtiv"]=$datos["docctrtiv"] : $GLOBALS["docctrtiv"]='';
 	(isset ($datos["docctcree"])) ? $GLOBALS["docctcree"]=$datos["docctcree"] : $GLOBALS["docctcree"]='';
 	(isset ($datos["doccttivas"])) ? $GLOBALS["doccttivas"]=$datos["doccttivas"] : $GLOBALS["doccttivas"]='';
 	(isset ($datos["doccttic"])) ? $GLOBALS["doccttic"]=$datos["doccttic"] : $GLOBALS["doccttic"]='';
 	(isset ($datos["doccttot"])) ? $GLOBALS["doccttot"]=$datos["doccttot"] : $GLOBALS["doccttot"]='';
 	
 	// Datos de proveedores
 	(isset ($datos["prvide"])) ? $GLOBALS["prvide"]=ltrim((string)$datos["prvide"],"0") : $GLOBALS["prvide"]='';
 	(isset ($datos["prvraz"])) ? $GLOBALS["prvraz"]=trim((string)$datos["prvraz"]) : $GLOBALS["prvraz"]='';
 	(isset ($datos["prvdir1"])) ? $GLOBALS["prvdir1"]=trim((string)$datos["prvdir1"]) : $GLOBALS["prvdir1"]='';
 	(isset ($datos["prvdir2"])) ? $GLOBALS["prvdir2"]=trim((string)$datos["prvdir2"]) : $GLOBALS["prvdir2"]='';
 	(isset ($datos["prvmun"])) ? $GLOBALS["prvmun"]=retornarNombreMunicipioMysqliApi($dbx,$datos["prvmun"]) : $GLOBALS["prvmun"]='';
 	(isset ($datos["prvtel1"])) ? $GLOBALS["prvtel1"]=trim((string)$datos["prvtel1"]) : $GLOBALS["prvtel1"]='';
 	(isset ($datos["prvtel2"])) ? $GLOBALS["prvtel2"]=trim((string)$datos["prvtel2"]) : $GLOBALS["prvtel2"]='';
 	(isset ($datos["prvmov"])) ? $GLOBALS["prvmov"]=trim((string)$datos["prvmov"]) : $GLOBALS["prvmov"]='';
 	(isset ($datos["prvemail"])) ? $GLOBALS["prvemail"]=trim((string)$datos["prvemail"]) : $GLOBALS["prvemail"]='';
 	(isset ($datos["prvrepli"])) ? $GLOBALS["prvrepli"]=ltrim((string)$datos["prvrepli"],"0") : $GLOBALS["prvrepli"]='';
 	(isset ($datos["prvrepln"])) ? $GLOBALS["prvrepln"]=trim((string)$datos["prvrepln"]) : $GLOBALS["prvrepln"]='';

 	// Datos de clientes
 	(isset ($datos["cliide"])) ? $GLOBALS["cliide"]=ltrim((string)$datos["cliide"],"0") : $GLOBALS["cliide"]='';
 	(isset ($datos["cliraz"])) ? $GLOBALS["cliraz"]=\funcionesGenerales::utf8_decode(trim((string)$datos["cliraz"])) : $GLOBALS["cliraz"]='';
 	(isset ($datos["clidir1"])) ? $GLOBALS["clidir1"]=\funcionesGenerales::utf8_decode(trim((string)$datos["clidir1"])) : $GLOBALS["clidir1"]='';
 	(isset ($datos["clidir2"])) ? $GLOBALS["clidir2"]=trim((string)$datos["clidir2"]) : $GLOBALS["clidir2"]='';
 	(isset ($datos["climun"])) ? $GLOBALS["climun"]=retornarNombreMunicipioMysqliApi($dbx,$datos["climun"]) : $GLOBALS["climun"]='';
 	(isset ($datos["clitel1"])) ? $GLOBALS["clitel1"]=trim((string)$datos["clitel1"]) : $GLOBALS["clitel1"]='';
 	(isset ($datos["clitel2"])) ? $GLOBALS["clitel2"]=trim((string)$datos["clitel2"]) : $GLOBALS["clitel2"]='';
 	(isset ($datos["cliemail"])) ? $GLOBALS["cliemail"]=\funcionesGenerales::utf8_decode(trim((string)$datos["cliemail"])) : $GLOBALS["cliemail"]='';
 	
 	(isset ($datos["regven"])) ? $GLOBALS["regven"]=trim((string)$datos["regven"]) : $GLOBALS["regven"]='';
 	(isset ($datos["regret"])) ? $GLOBALS["regret"]=trim((string)$datos["regret"]) : $GLOBALS["regret"]='';
 	(isset ($datos["retfte"])) ? $GLOBALS["retfte"]=trim((string)$datos["retfte"]) : $GLOBALS["retfte"]='';
 	
 	(isset ($datos["ano1"])) ? $GLOBALS["ano1"]=trim((string)$datos["ano1"]) : $GLOBALS["ano1"]='';
 	(isset ($datos["ano2"])) ? $GLOBALS["ano2"]=trim((string)$datos["ano2"]) : $GLOBALS["ano2"]='';
 	
 	if (!isset($datos["dev1"])) $datos["dev1"] = array ();
 	if (!isset($datos["dev2"])) $datos["dev2"] = array ();
 	
 	
 	if (!isset($datos["ceresp1"])) $datos["ceresp1"] = array ();
 	
 	
 	// Datos de items
 	$i=0;
 	$d=array();
 	$dev1=array();
 	$dev2=array();
 	$ceresp1=array();

 	//
 	if (!isset($datos["tb1"])) {
 		$datos["topetb1"]=0;
 		$datos["tb1"]=array();
 	} else {
 		if (!isset($datos["topetb1"])) {
 			$datos["topetb1"]=count($datos["tb1"]);
 		}
 	}
 	
 	//
 	if (isset($datos["tb1"])) {
 		foreach ($datos["tb1"] as $tb) {
 			$i++;
 			if (!isset($tb["codi"])) $tb["codi"] = '';
 			if (!isset($tb["cta"])) $tb["cta"] = '';
 			if (!isset($tb["nom"])) $tb["nom"] = '';
 			if (!isset($tb["ds"])) $tb["ds"] = '';
 			if (!isset($tb["um"])) $tb["um"] = '';
 			if (!isset($tb["cn"])) $tb["cn"] = '';
 			if (!isset($tb["vu"])) $tb["vu"] = '';
 			if (!isset($tb["pi"])) $tb["pi"] = '';
 			if (!isset($tb["paiu"])) $tb["paiu"] = '';
 			if (!isset($tb["vp"])) $tb["vp"] = '';
 			if (!isset($tb["vi"])) $tb["vi"] = '';
 			if (!isset($tb["vaiu"])) $tb["vaiu"] = '';
 			if (!isset($tb["vt"])) $tb["vt"] = '';
 			if (!isset($tb["cpres"])) $tb["cpres"] = '';
 			if (!isset($tb["npres"])) $tb["npres"] = '';
 			if (!isset($tb["cco"])) $tb["cco"] = '';
 			if (!isset($tb["prg"])) $tb["prg"] = '';
 			
 			(($tb["vu"]!='') && ($tb["vu"]!=0)) ? $tb["vu"]=number_format($tb["vu"],2) : $tb["vu"]='';
 			(($tb["pi"]!='') && ($tb["pi"]!=0)) ? $tb["pi"]=number_format($tb["pi"],2) : $tb["pi"]='';
 			(($tb["paiu"]!='') && ($tb["paiu"]!=0)) ? $tb["paiu"]=number_format($tb["paiu"],2) : $tb["paiu"]='';
 			(($tb["vp"]!='') && ($tb["vp"]!=0)) ? $tb["vp"]=number_format($tb["vp"],2) : $tb["vp"]='';
 			(($tb["vi"]!='') && ($tb["vi"]!=0)) ? $tb["vi"]=number_format($tb["vi"],2) : $tb["vi"]='';
 			(($tb["vaiu"]!='') && ($tb["vaiu"]!=0)) ? $tb["vaiu"]=number_format($tb["vaiu"],2) : $tb["vaiu"]='';
 			(($tb["vt"]!='') && ($tb["vt"]!=0)) ? $tb["vt"]=number_format($tb["vt"],2) : $tb["vt"]='';
 			
 			$d[] = array (
 				'codi' => $tb["codi"] , 
 				'cta' => $tb["cta"] , 
 				'nom' => \funcionesGenerales::utf8_decode($tb["nom"]) , 
 				'ds' => \funcionesGenerales::utf8_decode($tb["ds"]) ,
 				'um' => $tb["um"] , 
 				'cn' => $tb["cn"] , 
 				'vu' => $tb["vu"] , 
 				'pi' => $tb["pi"] , 
 				'paiu' => $tb["paiu"] , 
 				'vp' => $tb["vp"] , 
 				'vi' => $tb["vi"] ,
 				'vaiu' => $tb["vaiu"] , 
 				'vt' => $tb["vt"] , 
 				'cpres' => $tb["cpres"] , 
 				'npres' => $tb["npres"] ,
 				'cco' => $tb["cco"] , 
 				'prg' => $tb["prg"]
 			
 			);
 		}
 	}
 	
 	if ($i<$datos["topetb1"]) {
 		for ($j=$i+1;$j<=$datos["topetb1"];$j++) {
 			$d[] = array (
 				'codi' => '' , 
 				'cta' => '' , 
 				'nom' => '' , 
 				'ds' => '' , 
 				'um' => '' ,
 				'cn' => '' , 
 				'vu' => '' , 
 				'pi' => '' ,
 				'paiu' => '' , 
 				'vp' => '' , 
 				'vi' => '' , 
 				'vaiu' => '' , 
 				'vt' => '' , 
 				'cpres' => '' , 
 				'npres' => '',
 				'cco' => '',
 				'prg' => ''
 			); 			
 		}
 	}
 	
	// Datos de novedades
	if (isset($datos["tb2"])) {
		if (!isset($datos["topetb2"])) {
			$datos["topetb2"]=count($datos["tb2"]);
		}
	} else {
		$datos["tb2"]=array();
		$datos["topetb2"]=0;
	}
 	$d2=array ();
 	$j=0;
 	if (isset($datos["tb2"])) {
 		foreach ($datos["tb2"] as $tb) {
 			$j++;
 			(isset($tb["nvd"])) ? $d2[$j]["nvd"]=$tb["nvd"] : $d2[$j]["nvd"]='';
 			(isset($tb["ds"])) ? $d2[$j]["ds"]=\funcionesGenerales::utf8_decode($tb["ds"]) : $d2[$j]["ds"]='';
 			(isset($tb["nom"])) ? $d2[$j]["nom"]=\funcionesGenerales::utf8_decode($tb["nom"]) : $d2[$j]["nom"]='';
 			(isset($tb["cn"])) ? $d2[$j]["cn"]=$tb["cn"] : $d2[$j]["cn"]='';
 			(isset($tb["dev"])) ? $d2[$j]["dev"]=number_format($tb["dev"],2) : $d2[$j]["dev"]='';
 			(isset($tb["ded"])) ? $d2[$j]["ded"]=number_format($tb["ded"],2) : $d2[$j]["ded"]='';
 		}
 	}
 	if ($j<$datos["topetb2"]) {
 		for ($k=$j+1;$k<=$datos["topetb2"];$k++) {
 			$d2[] = array (
 				'nvd' => '' , 
 				'ds' => '' , 
 				'nom' => '' , 
 				'cn' => '' , 
 				'dev' => '' , 
 				'ded' => '' , 
 			); 			
 		}
 	}
 	
 	(isset ($datos["emptdev"])) ? $GLOBALS["emptdev"]=number_format($datos["emptdev"],2) : $GLOBALS["emptdev"]='';
 	(isset ($datos["emptded"])) ? $GLOBALS["emptded"]=number_format($datos["emptded"],2) : $GLOBALS["emptded"]='';
 	
 	// Datos de empleados
 	(isset ($datos["empt"])) ? $GLOBALS["empt"]=retornarNombreTablaBasicaMysqliApi($dbx,'bas_tipoidentificacion',$datos["empt"]) : $GLOBALS["empt"]='';
 	(isset ($datos["empi"])) ? $GLOBALS["empi"]=number_format($datos["empi"],0) : $GLOBALS["empi"]='';
 	(isset ($datos["empn"])) ? $GLOBALS["empn"]=\funcionesGenerales::utf8_decode(trim((string)$datos["empn"])) : $GLOBALS["empn"]='';
 	(isset ($datos["empn1"])) ? $GLOBALS["empn1"]=\funcionesGenerales::utf8_decode(trim((string)$datos["empn1"])) : $GLOBALS["empn1"]='';
 	(isset ($datos["empn2"])) ? $GLOBALS["empn2"]=\funcionesGenerales::utf8_decode(trim((string)$datos["empn2"])) : $GLOBALS["empn2"]='';
 	(isset ($datos["empa1"])) ? $GLOBALS["empa1"]=\funcionesGenerales::utf8_decode(trim((string)$datos["empa1"])) : $GLOBALS["empa1"]='';
 	(isset ($datos["empa2"])) ? $GLOBALS["empa2"]=\funcionesGenerales::utf8_decode(trim((string)$datos["empa2"])) : $GLOBALS["empa2"]='';
 	(isset ($datos["empfecn"])) ? $GLOBALS["empfecn"]=\funcionesGenerales::mostrarfecha($datos["empfecn"]) : $GLOBALS["empfecn"]='';
 	(isset ($datos["empfeci"])) ? $GLOBALS["empfeci"]=\funcionesGenerales::mostrarfecha($datos["empfeci"]) : $GLOBALS["empfeci"]='';
 	(isset ($datos["empfecr"])) ? $GLOBALS["empfecr"]=\funcionesGenerales::mostrarfecha($datos["empfecr"]) : $GLOBALS["empfecr"]='';
 	(isset ($datos["empcarn"])) ? $GLOBALS["empcarn"]=\funcionesGenerales::utf8_decode(trim((string)$datos["empcarn"])) : $GLOBALS["empcarn"]='';
 	(isset ($datos["empper"])) ? $GLOBALS["empper"]=$datos["empper"] : $GLOBALS["empper"]='';
 	(isset ($datos["empfun"])) ? $GLOBALS["empfun"]=$datos["empfun"] : $GLOBALS["empfun"]='';
 	(isset ($datos["emptc"])) ? $GLOBALS["emptc"]=retornarNombreTablaBasicaMysqliApi($dbx,'bas_tipoconnomina',$datos["emptc"]) : $GLOBALS["emptc"]='';
 	(isset ($datos["empms"])) ? $GLOBALS["empms"]=$datos["empms"] : $GLOBALS["empms"]='';
 	(isset ($datos["empprf"])) ? $GLOBALS["empprf"]=$datos["empprf"] : $GLOBALS["empprf"]='';
 	
 	// Afiliaciones del empleado
 	(isset ($datos["empfpen"])) ? $GLOBALS["empfpen"]=$datos["empfpen"] : $GLOBALS["empfpen"]='';
 	(isset ($datos["empeps"])) ? $GLOBALS["empeps"]=$datos["empeps"] : $GLOBALS["empeps"]='';
 	(isset ($datos["emparp"])) ? $GLOBALS["emparp"]=$datos["emparp"] : $GLOBALS["emparp"]='';
 	(isset ($datos["empfces"])) ? $GLOBALS["empfces"]=$datos["empfces"] : $GLOBALS["empfces"]='';
 	(isset ($datos["empprep"])) ? $GLOBALS["empprep"]=$datos["empprep"] : $GLOBALS["empprep"]='';
 	
 	// Salarios, sueldos, cesant&iacute;as, retenciones, devengados, deducidos, etc.
 	(isset ($datos["empsdo"])) ? $GLOBALS["empsdo"]=number_format($datos["empsdo"],0) : $GLOBALS["empsdo"]='';
 	(isset ($datos["empingt"])) ? $GLOBALS["empingt"]=number_format($datos["empingt"],0) : $GLOBALS["empingt"]='';
 	(isset ($datos["empingg"])) ? $GLOBALS["empingg"]=number_format($datos["empingg"],0) : $GLOBALS["empingg"]='';
 	(isset ($datos["empingng"])) ? $GLOBALS["empingng"]=number_format($datos["empingng"],0) : $GLOBALS["empingng"]='';
 	(isset ($datos["empret"])) ? $GLOBALS["empret"]=number_format($datos["empret"],0) : $GLOBALS["empret"]='';
 	(isset ($datos["empsal"])) ? $GLOBALS["empsal"]=number_format($datos["empsal"],0) : $GLOBALS["empsal"]='';
 	(isset ($datos["emppen"])) ? $GLOBALS["emppen"]=number_format($datos["emppen"],0) : $GLOBALS["emppen"]='';
 	(isset ($datos["empces"])) ? $GLOBALS["empces"]=number_format($datos["empces"],0) : $GLOBALS["empces"]='';
 	(isset ($datos["empices"])) ? $GLOBALS["empices"]=number_format($datos["empices"],0) : $GLOBALS["empices"]='';
 	
 	// Devolutivos y Desistimientos
 	(isset ($datos["tipodevolucion"])) ? $GLOBALS["tipodevolucion"]=$datos["tipodevolucion"] : $GLOBALS["tipodevolucion"]='';
 	(isset ($datos["expediente"])) ? $GLOBALS["expediente"]=$datos["expediente"] : $GLOBALS["expediente"]='';
 	(isset ($datos["numrec"])) ? $GLOBALS["numrec"]=$datos["numrec"] : $GLOBALS["numrec"]='';
 	(isset ($datos["razonsocial"])) ? $GLOBALS["razonsocial"]=\funcionesGenerales::utf8_decode($datos["razonsocial"]) : $GLOBALS["razonsocial"]='';
        (isset ($datos["nombreafectado"])) ? $GLOBALS["nombreafectado"]=\funcionesGenerales::utf8_decode($datos["nombreafectado"]) : $GLOBALS["nombreafectado"]='';
 	(isset ($datos["ident"])) ? $GLOBALS["ident"]=$datos["ident"] : $GLOBALS["ident"]='';
 	(isset ($datos["codbarras"])) ? $GLOBALS["codbarras"]=$datos["codbarras"] : $GLOBALS["codbarras"]='';
 	(isset ($datos["fecradica"])) ? $GLOBALS["fecradica"]=$datos["fecradica"] : $GLOBALS["fecradica"]='';
 	(isset ($datos["fecradl1"])) ? $GLOBALS["fecradl1"]=$datos["fecradl1"] : $GLOBALS["fecradl1"]='';
 	(isset ($datos["fecdevolucion"])) ? $GLOBALS["fecdevolucion"]= $datos["fecdevolucion"] : $GLOBALS["fecdevolucion"]='';
 	(isset ($datos["fecdevl1"])) ? $GLOBALS["fecdevl1"]=$datos["fecdevl1"] : $GLOBALS["fecdevl1"]='';
 	(isset ($datos["tipotramite"])) ? $GLOBALS["tipotramite"]=$datos["tipotramite"] : $GLOBALS["tipotramite"]='';
 	(isset ($datos["tipdoc"])) ? $GLOBALS["tipdoc"]=$datos["tipdoc"] : $GLOBALS["tipdoc"]='';
 	(isset ($datos["numdoc"])) ? $GLOBALS["numdoc"]=$datos["numdoc"] : $GLOBALS["numdoc"]='';
 	(isset ($datos["oridoc"])) ? $GLOBALS["oridoc"]=\funcionesGenerales::utf8_decode($datos["oridoc"]) : $GLOBALS["oridoc"]='';
 	(isset ($datos["motivos"])) ? $GLOBALS["motivos"]=\funcionesGenerales::utf8_decode($datos["motivos"]) : $GLOBALS["motivos"]='';
 	(isset ($datos["reingreso"])) ? $GLOBALS["reingreso"]=$datos["reingreso"] : $GLOBALS["reingreso"]='';
 	(isset ($datos["nomabo"])) ? $GLOBALS["nomabo"]=\funcionesGenerales::utf8_decode($datos["nomabo"]) : $GLOBALS["nomabo"]='';
 	(isset ($datos["cargoabogado"])) ? $GLOBALS["cargoabogado"]=\funcionesGenerales::utf8_decode($datos["cargoabogado"]) : $GLOBALS["cargoabogado"]='';
 	(isset ($datos["nuc"])) ? $GLOBALS["nuc"]=$datos["nuc"] : $GLOBALS["nuc"]='';
 	(isset ($datos["nin"])) ? $GLOBALS["nin"]=$datos["nin"] : $GLOBALS["nin"]='';
        (isset ($datos["valor"])) ? $GLOBALS["valor"] = $datos["valor"] : $GLOBALS["valor"]='';
        (isset ($datos["email"])) ? $GLOBALS["email"] = $datos["email"] : $GLOBALS["email"]='';
 	
 	//
 	foreach ($datos["dev1"] as $x1) {
 		$dev1[] = $x1;
 	}
 	
 	//
 	foreach ($datos["dev2"] as $x1) {
 		$dev2[] = $x1;
 	}
 	
 	// Cartulinas
 	(isset ($datos["nombrepropietario"])) ? $GLOBALS["nombrepropietario"]=\funcionesGenerales::utf8_decode($datos["nombrepropietario"]) : $GLOBALS["nombrepropietario"]='';
 	(isset ($datos["nombreestablecimiento"])) ? $GLOBALS["nombreestablecimiento"]=\funcionesGenerales::utf8_decode($datos["nombreestablecimiento"]) : $GLOBALS["nombreestablecimiento"]='';
 	
 	// Declaraciones de proponentes
 	(isset ($datos["ciudad"])) ? $GLOBALS["ciudad"]=\funcionesGenerales::utf8_decode($datos["ciudad"]) : $GLOBALS["ciudad"]='';
	(isset ($datos["docfecl"])) ? $GLOBALS["docfecl"]=$datos["docfecl"] : $GLOBALS["docfecl"]='';
	(isset ($datos["razonsocial"])) ? $GLOBALS["razonsocial"]=\funcionesGenerales::utf8_decode($datos["razonsocial"]) : $GLOBALS["razonsocial"]='';
	(isset ($datos["ident"])) ? $GLOBALS["ident"]=$datos["ident"] : $GLOBALS["ident"]='';
	
	//
	(isset ($datos["tiposc"])) ? $GLOBALS["tiposc"]=$datos["tiposc"] : $GLOBALS["tiposc"]='';
	(isset ($datos["relacionsc"])) ? $GLOBALS["relacionsc"]=\funcionesGenerales::utf8_decode($datos["relacionsc"]) : $GLOBALS["relacionsc"]='';
	
	//
	(isset ($datos["tamanoempresa"])) ? $GLOBALS["tamanoempresa"]=$datos["tamanoempresa"] : $GLOBALS["tamanoempresa"]='';
	
	//
	(isset ($datos["secuenciacontrato"])) ? $GLOBALS["secuenciacontrato"]=$datos["secuenciacontrato"] : $GLOBALS["secuenciacontrato"]='';
	(isset ($datos["nombrecontratante"])) ? $GLOBALS["nombrecontratante"]=\funcionesGenerales::utf8_decode($datos["nombrecontratante"]) : $GLOBALS["nombrecontratante"]='';
	(isset ($datos["nombrecontratista"])) ? $GLOBALS["nombrecontratista"]=\funcionesGenerales::utf8_decode($datos["nombrecontratista"]) : $GLOBALS["nombrecontratista"]='';
	(isset ($datos["modalidadcontratacion"])) ? $GLOBALS["modalidadcontratacion"]=$datos["modalidadcontratacion"] : $GLOBALS["modalidadcontratacion"]='';	
	(isset ($datos["valorsmmlv"])) ? $GLOBALS["valorsmmlv"]=$datos["valorsmmlv"] : $GLOBALS["valorsmmlv"]='';
        (isset ($datos["valorpesos"])) ? $GLOBALS["valorpesos"]=$datos["valorpesos"] : $GLOBALS["valorpesos"]='';
	(isset ($datos["unspsc"])) ? $GLOBALS["unspsc"]=$datos["unspsc"] : $GLOBALS["unspsc"]='';
	
	//
	(isset ($datos["fechacorte"])) ? $GLOBALS["fechacorte"]=$datos["fechacorte"] : $GLOBALS["fechacorte"]='';
	(isset ($datos["actcte"])) ? $GLOBALS["actcte"]=$datos["actcte"] : $GLOBALS["actcte"]='';
        (isset ($datos["actnocte"])) ? $GLOBALS["actnocte"]=$datos["actnocte"] : $GLOBALS["actnocte"]='';
	(isset ($datos["fijnet"])) ? $GLOBALS["fijnet"]=$datos["fijnet"] : $GLOBALS["fijnet"]='';
	(isset ($datos["actotr"])) ? $GLOBALS["actotr"]=$datos["actotr"] : $GLOBALS["actotr"]='';
	(isset ($datos["actval"])) ? $GLOBALS["actval"]=$datos["actval"] : $GLOBALS["actval"]='';
	(isset ($datos["acttot"])) ? $GLOBALS["acttot"]=$datos["acttot"] : $GLOBALS["acttot"]='';
	(isset ($datos["pascte"])) ? $GLOBALS["pascte"]=$datos["pascte"] : $GLOBALS["pascte"]='';
	(isset ($datos["paslar"])) ? $GLOBALS["paslar"]=$datos["paslar"] : $GLOBALS["paslar"]='';
	(isset ($datos["pastot"])) ? $GLOBALS["pastot"]=$datos["pastot"] : $GLOBALS["pastot"]='';
	(isset ($datos["patnet"])) ? $GLOBALS["patnet"]=$datos["patnet"] : $GLOBALS["patnet"]='';
	(isset ($datos["paspat"])) ? $GLOBALS["paspat"]=$datos["paspat"] : $GLOBALS["paspat"]='';
        (isset ($datos["balsoc"])) ? $GLOBALS["balsoc"]=$datos["balsoc"] : $GLOBALS["balsoc"]='';
	(isset ($datos["ingope"])) ? $GLOBALS["ingope"]=$datos["ingope"] : $GLOBALS["ingope"]='';
	(isset ($datos["ingnoope"])) ? $GLOBALS["ingnoope"]=$datos["ingnoope"] : $GLOBALS["ingnoope"]='';
	(isset ($datos["gasope"])) ? $GLOBALS["gasope"]=$datos["gasope"] : $GLOBALS["gasope"]='';
	(isset ($datos["gasnoope"])) ? $GLOBALS["gasnoope"]=$datos["gasnoope"] : $GLOBALS["gasnoope"]='';
	(isset ($datos["cosven"])) ? $GLOBALS["cosven"]=$datos["cosven"] : $GLOBALS["cosven"]='';
	(isset ($datos["utiope"])) ? $GLOBALS["utiope"]=$datos["utiope"] : $GLOBALS["utiope"]='';
	(isset ($datos["utinet"])) ? $GLOBALS["utinet"]=$datos["utinet"] : $GLOBALS["utinet"]='';
	(isset ($datos["gasint"])) ? $GLOBALS["gasint"]=$datos["gasint"] : $GLOBALS["gasint"]='';
        (isset ($datos["gasimp"])) ? $GLOBALS["gasimp"]=$datos["gasimp"] : $GLOBALS["gasimp"]='';
	(isset ($datos["indliq"])) ? $GLOBALS["indliq"]=$datos["indliq"] : $GLOBALS["indliq"]='';
	(isset ($datos["nivend"])) ? $GLOBALS["nivend"]=$datos["nivend"] : $GLOBALS["nivend"]='';
	(isset ($datos["razcob"])) ? $GLOBALS["razcob"]=$datos["razcob"] : $GLOBALS["razcob"]='';
	(isset ($datos["renpat"])) ? $GLOBALS["renpat"]=$datos["renpat"] : $GLOBALS["renpat"]='';
	(isset ($datos["renact"])) ? $GLOBALS["renact"]=$datos["renact"] : $GLOBALS["renact"]='';
	
	// Certificados especiales
	(isset ($datos["ceresp_nombrecamara"])) ? $GLOBALS["ceresp_nombrecamara"]=\funcionesGenerales::utf8_decode($datos["ceresp_nombrecamara"]) : $GLOBALS["ceresp_nombrecamara"]='';
	(isset ($datos["ceresp_direccioncamara"])) ? $GLOBALS["ceresp_direccioncamara"]=\funcionesGenerales::utf8_decode($datos["ceresp_direccioncamara"]) : $GLOBALS["ceresp_direccioncamara"]='';
	(isset ($datos["ceresp_ciudadcamara"])) ? $GLOBALS["ceresp_ciudadcamara"]=\funcionesGenerales::utf8_decode($datos["ceresp_ciudadcamara"]) : $GLOBALS["ceresp_ciudadcamara"]='';
	(isset ($datos["ceresp_municipiocamara"])) ? $GLOBALS["ceresp_municipiocamara"]=$datos["ceresp_municipiocamara"] : $GLOBALS["ceresp_municipiocamara"]='';
	(isset ($datos["ceresp_telefonocamara"])) ? $GLOBALS["ceresp_telefonocamara"]=$datos["ceresp_telefonocamara"] : $GLOBALS["ceresp_telefonocamara"]='';
	(isset ($datos["ceresp_emailcamara"])) ? $GLOBALS["ceresp_emailcamara"]=\funcionesGenerales::utf8_decode($datos["ceresp_emailcamara"]) : $GLOBALS["ceresp_emailcamara"]='';
	(isset ($datos["ceresp_codigoverificacion"])) ? $GLOBALS["ceresp_codigoverificacion"]=$datos["ceresp_codigoverificacion"] : $GLOBALS["ceresp_codigoverificacion"]='';
	(isset ($datos["ceresp_sitioverificacion"])) ? $GLOBALS["ceresp_sitioverificacion"]=$datos["ceresp_sitioverificacion"] : $GLOBALS["ceresp_sitioverificacion"]='';
	(isset ($datos["ceresp_tipocertificacion"])) ? $GLOBALS["ceresp_tipocertificacion"]=\funcionesGenerales::utf8_decode($datos["ceresp_tipocertificacion"]) : $GLOBALS["ceresp_tipocertificacion"]='';
	(isset ($datos["ceresp_explicacion"])) ? $GLOBALS["ceresp_explicacion"]=\funcionesGenerales::utf8_decode($datos["ceresp_explicacion"]) : $GLOBALS["ceresp_explicacion"]='';
	(isset ($datos["ceresp_fechaexpedicion"])) ? $GLOBALS["ceresp_fechaexpedicion"]=$datos["ceresp_fechaexpedicion"] : $GLOBALS["ceresp_fechaexpedicion"]='';
	(isset ($datos["ceresp_fechaletras"])) ? $GLOBALS["ceresp_fechaletras"]=$datos["ceresp_fechaletras"] : $GLOBALS["ceresp_fechaletras"]='';
	(isset ($datos["ceresp_fechasolicitud"])) ? $GLOBALS["ceresp_fechasolicitud"]=$datos["ceresp_fechasolicitud"] : $GLOBALS["ceresp_fechasolicitud"]='';
	(isset ($datos["ceresp_numerorecibo"])) ? $GLOBALS["ceresp_numerorecibo"]=$datos["ceresp_numerorecibo"] : $GLOBALS["ceresp_numerorecibo"]='';
	(isset ($datos["ceresp_numerooperacion"])) ? $GLOBALS["ceresp_numerooperacion"]=$datos["ceresp_numerooperacion"] : $GLOBALS["ceresp_numerooperacion"]='';
	(isset ($datos["ceresp_referencia"])) ? $GLOBALS["ceresp_referencia"]=$datos["ceresp_referencia"] : $GLOBALS["ceresp_referencia"]='';
	(isset ($datos["ceresp_nombrecliente"])) ? $GLOBALS["ceresp_nombrecliente"]=\funcionesGenerales::utf8_decode($datos["ceresp_nombrecliente"]) : $GLOBALS["ceresp_nombrecliente"]='';
	(isset ($datos["ceresp_identificacioncliente"])) ? $GLOBALS["ceresp_identificacioncliente"]=$datos["ceresp_identificacioncliente"] : $GLOBALS["ceresp_identificacioncliente"]='';
	(isset ($datos["ceresp_expediente"])) ? $GLOBALS["ceresp_expediente"]=$datos["ceresp_expediente"] : $GLOBALS["ceresp_expediente"]='';
	(isset ($datos["ceresp_razonsocial"])) ? $GLOBALS["ceresp_razonsocial"]=\funcionesGenerales::utf8_decode($datos["ceresp_razonsocial"]) : $GLOBALS["ceresp_razonsocial"]='';
	(isset ($datos["ceresp_identificacion"])) ? $GLOBALS["ceresp_identificacion"]=$datos["ceresp_identificacion"] : $GLOBALS["ceresp_identificacion"]='';
	(isset ($datos["ceresp_jefejuridico"])) ? $GLOBALS["ceresp_jefejuridico"]=\funcionesGenerales::utf8_decode($datos["ceresp_jefejuridico"]) : $GLOBALS["ceresp_jefejuridico"]='';
	foreach ($datos["ceresp1"] as $x1) {
		$ceresp1[] = $x1;
	}
	
        // Formato responsabilidades tributarias
        if (isset($datos["razonsocial"])) {
            $GLOBALS["razonsocial"] = strtoupper($datos["razonsocial"]);
        }
        if (isset($datos["nombre"])) {
            $GLOBALS["nombre"] = strtoupper($datos["nombre"]);
        }
        if (isset($datos["tipoid"])) {
            $GLOBALS["tipoid"] = $datos["tipoid"];
        }
        if (isset($datos["identificacion"])) {
            $GLOBALS["identificacion"] = $datos["identificacion"];
        }
        if (isset($datos["ciuexp"])) {
            $GLOBALS["ciuexp"] = $datos["ciuexp"];
        }
        if (isset($datos["dptoexp"])) {
            $GLOBALS["dptoexp"] = $datos["dptoexp"];
        }
        if (isset($datos["paiexp"])) {
            $GLOBALS["paiexp"] = $datos["paiexp"];
        }
        if (isset($datos["responsabilidades"])) {
            $GLOBALS["responsabilidades"] = $datos["responsabilidades"];
        }
        if (isset($datos["firmamensaje"])) {
            $GLOBALS["firmamensaje"] = $datos["firmamensaje"];
        }  else {
            $GLOBALS["firmamensaje"] = "                        ";
        }
        if (isset($datos["textofirma"])) {
            $GLOBALS["textofirma"] = $datos["textofirma"];
        }
	
	$TBS = new clsTinyButStrong;	
	$TBS->Plugin(TBS_INSTALL, OPENTBS_PLUGIN);	
	$TBS->LoadTemplate($formato);
	$TBS->MergeBlock('a1','array',$d);
	$TBS->MergeBlock('dev1','array',$dev1);
	$TBS->MergeBlock('dev2','array',$dev2);
	$TBS->MergeBlock('ceresp1','array',$ceresp1);
	
	if (isset($datos["logo"]) && trim($datos["logo"])!='') {
		$TBS->PlugIn(OPENTBS_CHANGE_PICTURE,'XXXXXX',$GLOBALS["img"]);
	}
        
	if (isset($datos["img1"]) && trim($datos["img1"])!='') {
		$TBS->PlugIn(OPENTBS_CHANGE_PICTURE,'XXXXXX',$GLOBALS["img1"]);
	}
        $name1 = $_SESSION["generales"]["codigoempresa"] . '-' . session_id() . date("Ymd") . date ("His") . '.docx';
	$name = $_SESSION["generales"]["pathabsoluto"] . '/tmp/' . $name1;
	$TBS->Show(OPENTBS_FILE, $name);
        unset ($TBS);
	return $name1;
}
?>
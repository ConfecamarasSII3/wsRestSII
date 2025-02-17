<?php

function armarNombresTablasApi() {
    if (!isset($_SESSION["generales"]["codigoempresa"])) {
        return false;        
    }
    if (!file_exists(PATH_ABSOLUTO_SITIO . '/configuracion/common' . $_SESSION["generales"]["codigoempresa"] . '.php')) {
        return false;
    }
    $_SESSION["generales"]["basopciones"] = 'bas_opciones';
    $_SESSION["generales"]["bastagsbandejaentrada"] = 'bas_tagsbandejaentrada';
    $_SESSION["generales"]["baspermisosespeciales"] = 'bas_permisosespeciales';
    if (defined('TABLA_BAS_OPCIONES') && trim(TABLA_BAS_OPCIONES) != '') {
        $_SESSION["generales"]["basopciones"] = TABLA_BAS_OPCIONES;
    }
    if (defined('TABLA_BAS_TAGSBANDEJAENTRADA') && trim(TABLA_BAS_TAGSBANDEJAENTRADA) != '') {
        $_SESSION["generales"]["bastagsbandejaentrada"] = TABLA_BAS_TAGSBANDEJAENTRADA;
    }
    if (defined('TABLA_BAS_PERMISOSESPECIALES') && trim(TABLA_BAS_PERMISOSESPECIALES) != '') {
        $_SESSION["generales"]["baspermisosespeciales"] = TABLA_BAS_PERMISOSESPECIALES;
    }
    return true;    
}


function separarDvSii2($id) {
    $id = str_replace(",", "", ltrim(trim($id), "0"));
    $id = str_replace(".", "", $id);
    $id = str_replace("-", "", $id);
    $entrada = sprintf("%016s", $id);
    $dv = substr($entrada, 15, 1);
    return array(
        'identificacion' => ltrim(substr($entrada, 0, 15), "0"),
        'dv' => $dv);
}

function cambiarSustitutoHtmlSii2($txt) {
    $txt = str_replace("[0]", " ", $txt);
    $txt = str_replace("[1]", "<", $txt);
    $txt = str_replace("[2]", ">", $txt);
    $txt = str_replace("[3]", "/", $txt);
    $txt = str_replace("[4]", " ", $txt);
    $txt = str_replace("[5]", "\"", $txt);
    $txt = str_replace("[6]", "'", $txt);
    $txt = str_replace("[7]", "&", $txt);
    $txt = str_replace("[8]", "?", $txt);
    $txt = str_replace("[9]", "&aacute;", $txt);
    $txt = str_replace("[10]", "&eacute;", $txt);
    $txt = str_replace("[11]", "&iacute;", $txt);
    $txt = str_replace("[12]", "&oacute;", $txt);
    $txt = str_replace("[13]", "&uacute;", $txt);
    $txt = str_replace("[14]", "&ntilde;", $txt);
    $txt = str_replace("[15]", "&ntilde;", $txt);
    $txt = str_replace("[16]", "+", $txt);
    $txt = str_replace("[17]", "#", $txt);
    $txt = str_replace("[18]", "&aacute;", $txt);
    $txt = str_replace("[19]", "&eacute;", $txt);
    $txt = str_replace("[20]", "&iacute;", $txt);
    $txt = str_replace("[21]", "&oacute;", $txt);
    $txt = str_replace("[22]", "&uacute;", $txt);
    $txt = str_replace("[menorque]", "<", $txt);
    $txt = str_replace("[mayorque]", ">", $txt);
    $txt = str_replace("[slash]", "/", $txt);
    $txt = str_replace("[caracterblanco]", "&nbsp;", $txt);
    $txt = str_replace("[comilladoble]", "\"", $txt);
    $txt = str_replace("[comillasimple]", "'", $txt);
    $txt = str_replace("[ampersand]", "&", $txt);
    $txt = str_replace("[interrogacion]", "?", $txt);
    $txt = str_replace("[atilde]", "&aacute;", $txt);
    $txt = str_replace("[etilde]", "&eacute;", $txt);
    $txt = str_replace("[itilde]", "&iacute;", $txt);
    $txt = str_replace("[otilde]", "&oacute;", $txt);
    $txt = str_replace("[utilde]", "&uacute;", $txt);
    $txt = str_replace("[ene]", "&ntilde;", $txt);
    $txt = str_replace("[ENE]", "&ntilde;", $txt);
    $txt = str_replace("[mas]", "+", $txt);
    return $txt;
}

function xmlEscapeSii2($string) {
    $string = str_replace('&amp;', '&', $string);
    return str_replace('&', '&amp;', $string);
}
function truncateFloatSii2($number, $digitos, $pd = '.', $pm = ',') {
    $raiz = 10;
    $multiplicador = pow($raiz, $digitos);
    $resultado = ((int) ($number * $multiplicador)) / $multiplicador;
    $x = number_format($resultado, $digitos, $pd, $pm);
    $x = str_replace(",", "", $x);
    return $x;
}



function obtenerNavegadorSii2($nav) {
    if (preg_match("/MSIE/i", "$nav")) {
        $resultado = "IE.";
    } else {
        if (preg_match("/Mozilla/i", "$nav")) {
            $resultado = "Mozilla.";
        } else {
            $resultado = "Estas usando $nav";
        }
    }
    return $resultado;
}

function generarAleatorioAlfanumerico10Sii2($validar = '') {
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common.php');
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common' . $_SESSION["generales"]["codigoempresa"] . '.php');
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/librerias/funciones/persistencia.php');

    $numrecvalido = 'NO';
    while ($numrecvalido == 'NO') {
        $ok = 'NO';
        while ($ok == 'NO') {
            // $alfanumerico = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
            $alfanumerico = 'abcdefghjkmnpqrstuvwxyzABCDEFGHJKMNPQRSTUVWXYZ123456789';
            $num = '';
            for ($i = 1; $i <= 10; $i++) {
                $num .= substr(substr($alfanumerico, rand(1, strlen($alfanumerico))), 0, 1);
            }
            if (strlen($num) == 10) {
                $ok = 'SI';
            }
        }
        if ($validar == '') {
            $numrecvalido = 'SI';
        }

        if ($validar == 'desarrollo_actividades') {
            if (contarRegistros('desarrollo_actividades', "numerorecuperacion='" . $num . "'") == 0) {
                $numrecvalido = 'SI';
            }
        }

        if ($validar == 'desarrollo_control_cambios') {
            if (contarRegistros('desarrollo_control_cambios', "numerorecuperacion='" . $num . "'") == 0) {
                $numrecvalido = 'SI';
            }
        }

        if ($validar == 'desarrollo_control_cambios_casosuso') {
            if (contarRegistros('desarrollo_control_cambios_casosuso', "numerorecuperacion='" . $num . "'") == 0) {
                $numrecvalido = 'SI';
            }
        }

        if ($validar == 'infraestructura_contratos') {
            if (contarRegistros('infraestructura_contratos', "numerorecuperacion='" . $num . "'") == 0) {
                $numrecvalido = 'SI';
            }
        }

        if ($validar == 'infraestructura_proveedores') {
            if (contarRegistros('infraestructura_proveedores', "numerorecuperacion='" . $num . "'") == 0) {
                $numrecvalido = 'SI';
            }
        }

        if ($validar == 'infraestructura_clientes') {
            if (contarRegistros('infraestructura_clientes', "numerorecuperacion='" . $num . "'") == 0) {
                $numrecvalido = 'SI';
            }
        }

        if ($validar == 'infraestructura_comentarios') {
            if (contarRegistros('infraestructura_comentarios', "numerorecuperacion='" . $num . "'") == 0) {
                $numrecvalido = 'SI';
            }
        }


        if ($validar == 'mreg_liquidacion_sobre') {
            if (contarRegistros('mreg_liquidacion_sobre', "idsobre='" . $num . "'") == 0) {
                $numrecvalido = 'SI';
            }
        }

        if ($validar == 'mreg_liquidacion') {
            if (contarRegistros('mreg_liquidacion', "numerorecuperacion='" . $num . "'") == 0) {
                $numrecvalido = 'SI';
            }
        }
        if ($validar == 'mreg_liquidacion_baloto') {
            if (contarRegistros('mreg_liquidacion_baloto', "volantenumero='" . $num . "'") == 0) {
                $numrecvalido = 'SI';
            }
        }
        if ($validar == 'mreg_liquidacion_exito') {
            if (contarRegistros('mreg_liquidacion_exito', "volantenumero='" . $num . "'") == 0) {
                $numrecvalido = 'SI';
            }
        }
        if ($validar == 'mreg_reactivaciones_propias') {
            if (contarRegistros('mreg_reactivaciones_propias', "codigoreactivacion='" . $num . "'") == 0) {
                $numrecvalido = 'SI';
            }
        }
        if ($validar == 'mreg_certificados_virtuales') {
            if (contarRegistros('mreg_certificados_virtuales', "id='" . $num . "'") == 0) {
                $numrecvalido = 'SI';
            }
        }
    }
    return $num;
}

function localizarIPSii2() {
    $ip = '';

    //
    if (isset($_SERVER['REMOTE_ADDR'])) {
        $ip = $_SERVER['REMOTE_ADDR'];
    }

    //
    if ($ip == '') {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } else {
            if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
            } else {
                $ip = '127.0.0.1';
            }
        }
    }
    return $ip;
}
function truncateFinancialIndexesSii2($number) {
    $sep = explode(",", $number);
    if (isset($sep[1])) {
        if (strlen($sep[1]) == 1) {
            $number = $sep[0] . ',' . $sep[1] . '0';
        }
        if (strlen($sep[1]) > 2) {
            $number = $sep[0] . ',' . substr($sep[1], 0, 2);
        }
    }
    return $number;
}

function mostrarPesos2Sii2($var) {
    if (trim($var) == '') {
        return "-o-";
    }
    if (!is_numeric($var)) {
        return "-o-";
    }
    return "$" . number_format($var, 2, ",", ".");
}

// retorna la fecha en formato DD/MM/AAAA
function mostrarFechaSii2($fec) {
    if ((trim($fec) == '') || (ltrim($fec, "0") == '')) {
        return '';
    }
    if (strlen($fec) == 10) {
        $fec = str_replace(array("/", "-"), "", $fec);
    }
    return substr($fec, 6, 2) . '/' . substr($fec, 4, 2) . '/' . substr($fec, 0, 4);
}
// retorna la fecha en formato DD/MM/AAAA
function mostrarFecha2Sii2($fec) {
    if ((trim($fec) == '') || (ltrim($fec, "0") == '')) {
        return '';
    }
    if (strlen($fec) == 10) {
        $fec = str_replace(array("/", "-"), "", $fec);
    }
    return substr($fec, 6, 2) . '/' . substr($fec, 4, 2) . '/' . substr($fec, 0, 4);
}
/**
 *
 * @param fecha 	Formato AAAAMMDD
 * @return texto de la forma <nombremes> <numerodia> de <ano>
 */
function mostrarFechaLetrasSii2($fec) {
    if (trim($fec) == '') {
        return '';
    }
    $fec = str_replace(array('-', '/'), "", $fec);
    if (trim($fec) == '') {
        return '';
    }
    $txt = '';
    $mes = substr($fec, 4, 2);
    switch ($mes) {
        case "01": $txt = 'enero';
            break;
        case "02": $txt = 'febrero';
            break;
        case "03": $txt = 'marzo';
            break;
        case "04": $txt = 'abril';
            break;
        case "05": $txt = 'mayo';
            break;
        case "06": $txt = 'junio';
            break;
        case "07": $txt = 'julio';
            break;
        case "08": $txt = 'agosto';
            break;
        case "09": $txt = 'septiembre';
            break;
        case "10": $txt = 'octubre';
            break;
        case "11": $txt = 'noviembre';
            break;
        case "12": $txt = 'diciembre';
            break;
    }
    if (strlen($fec) == 6) {
        return $txt . ' ' . ' de ' . substr($fec, 0, 4);
    } else {
        return $txt . ' ' . substr($fec, 6, 2) . ' de ' . substr($fec, 0, 4);
    }
    exit();
}

/**
 * 
 * @param fecha 	Formato AAAAMMDD
 * @return texto de la forma <numdia> de <nombremes> de <ano>
 */
function mostrarFechaLetras1Sii2($fec) {
    if (trim($fec) == '') {
        return '';
    }
    $fec = str_replace(array('-', '/'), "", $fec);
    if (trim($fec) == '') {
        return '';
    }
    $txt = '';
    $mes = substr($fec, 4, 2);
    switch ($mes) {
        case "01": $txt = 'enero';
            break;
        case "02": $txt = 'febrero';
            break;
        case "03": $txt = 'marzo';
            break;
        case "04": $txt = 'abril';
            break;
        case "05": $txt = 'mayo';
            break;
        case "06": $txt = 'junio';
            break;
        case "07": $txt = 'julio';
            break;
        case "08": $txt = 'agosto';
            break;
        case "09": $txt = 'septiembre';
            break;
        case "10": $txt = 'octubre';
            break;
        case "11": $txt = 'noviembre';
            break;
        case "12": $txt = 'diciembre';
            break;
    }
    if (strlen($fec) == 6) {
        return $txt . ' ' . ' de ' . substr($fec, 0, 4);
    } else {
        return substr($fec, 6, 2) . ' de ' . $txt . ' de ' . substr($fec, 0, 4);
    }
    exit();
}

function isJsonSii2($string) {
    return ((is_string($string) &&
            (is_object(json_decode($string)) ||
            is_array(json_decode($string))))) ? true : false;
}

function unirPdfs($lista = array(), $salida, $orientation = 'P', $unit = 'mm', $format = 'A4') {
    require_once ('fpdf153/fpdf.php');
    require_once ('fpdi/fpdi.php');

    if (!class_exists('concat_pdf')) {

        class concat_pdf extends FPDI {

            var $files = array();

            function setFiles($files) {
                $this->files = $files;
            }

            function concat() {
                foreach ($this->files AS $file) {

                    if (!empty($file)) {
                        $pagecount = $this->setSourceFile($file);
                        for ($i = 1; $i <= $pagecount; $i++) {
                            $tplidx = $this->ImportPage($i);
                            $s = $this->getTemplatesize($tplidx);
                            $this->AddPage($s['h'] > $s['w'] ? 'P' : 'L');
                            $this->useTemplate($tplidx);
                        }
                    } else {
                        $this->AddPage();
                    }
                }
            }

        }

    }
    $pdf = new concat_pdf($orientation, $unit, $format);
    $pdf->setFiles($lista);
    $pdf->concat();
    $pdf->Output($salida, 'F');
}



function borrarPalabrasAutomaticasSii2($txt, $comple = '') {
    $salida = $txt;
    if ($comple != '') {
        $pos = strpos($salida, $comple);
        if ($pos) {
            $pos = $pos - 1;
            $salida = substr($salida, 0, $pos);
        }
    }
    $pos = strpos($salida, '- EN LIQUIDACION');
    if ($pos) {
        $pos = $pos - 1;
        $salida = substr($salida, 0, $pos);
    }

    $pos = strpos($salida, '- EN LIQUIDACION JUDICIAL');
    if ($pos) {
        $pos = $pos - 1;
        $salida = substr($salida, 0, $pos);
    }

    $pos = strpos($salida, '- EN LIQUIDACION FORZOSA');
    if ($pos) {
        $pos = $pos - 1;
        $salida = substr($salida, 0, $pos);
    }

    $pos = strpos($salida, '- EN ACUERDO DE REESTRUCTURACION');
    if ($pos) {
        $pos = $pos - 1;
        $salida = substr($salida, 0, $pos);
    }
    $pos = strpos($salida, '- EN REESTRUCTURACION');
    if ($pos) {
        $pos = $pos - 1;
        $salida = substr($salida, 0, $pos);
    }
    $pos = strpos($salida, '- EN REORGANIZACION');
    if ($pos) {
        $pos = $pos - 1;
        $salida = substr($salida, 0, $pos);
    }

    $pos = strpos($salida, 'EN LIQUIDACION');
    if ($pos) {
        $pos = $pos - 1;
        $salida = substr($salida, 0, $pos);
    }

    $pos = strpos($salida, 'EN LIQUIDACION');
    if ($pos) {
        $pos = $pos - 1;
        $salida = substr($salida, 0, $pos);
    }

    $pos = strpos($salida, 'EN LIQUIDACION JUDICIAL');
    if ($pos) {
        $pos = $pos - 1;
        $salida = substr($salida, 0, $pos);
    }

    $pos = strpos($salida, 'EN LIQUIDACION FORZOSA');
    if ($pos) {
        $pos = $pos - 1;
        $salida = substr($salida, 0, $pos);
    }

    $pos = strpos($salida, 'EN ACUERDO DE REESTRUCTURACION');
    if ($pos) {
        $pos = $pos - 1;
        $salida = substr($salida, 0, $pos);
    }
    $pos = strpos($salida, 'EN REESTRUCTURACION');
    if ($pos) {
        $pos = $pos - 1;
        $salida = substr($salida, 0, $pos);
    }
    $pos = strpos($salida, 'EN REORGANIZACION');
    if ($pos) {
        $pos = $pos - 1;
        $salida = substr($salida, 0, $pos);
    }
    return $salida;
}

function soloNumeros($valor) {

    $valortmp = trim($valor);

    if ($valortmp != '') {
        $valortmp = str_replace(".00", "", $valortmp);
        $valortmp = str_replace(array(",", ".", "-", "/"), "", $valortmp);
        if (!is_numeric($valortmp)) {
            $valortmp = '';
        }
    }
    return $valortmp;
}

function convertirStringNumeroSii2($valx) {
    $valx = trim($valx);
    $val = 0;
    $signo = '+';
    $valx = str_replace(",", "", $valx);
    $valx = ltrim(trim($valx), "0");
    if ($valx == '' || $valx == '0.00' || $valx == '.00') {
        $val = 0;
    } else {
        if (substr($valx, 0, 1) == '-') {
            $signo = '-';
            $valx = str_replace("-", "", $valx);
        }
        $a = explode(".", $valx);
        $val = doubleval($a[0]);
        if (isset($a[1])) {
            $len = strlen($a[1]);
            switch ($len) {
                case 1: $val = $val + intval($a[1]) / 10;
                    break;
                case 2: $val = $val + intval($a[1]) / 100;
                    break;
                case 3: $val = $val + intval($a[1]) / 1000;
                    break;
                case 4: $val = $val + intval($a[1]) / 10000;
                    break;
                case 5: $val = $val + intval($a[1]) / 100000;
                    break;
            }
        }
    }
    if ($signo == '-') {
        $val = $val * -1;
    }
    return $val;
}

/*
 * Crea archivo index.html en la carpeta indicada
 */

function crearIndexSii2($dir) {

    if (!file_exists($dir . '/index.html')) {
        $f = fopen($dir . '/index.html', "w");
        $txt = '	
		<!DOCTYPE HTML>
		<html>
		<head>
		<title>Directorio protegido</title>
		<meta name="keywords" content="" />
		<meta name="description" content="" />
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<meta name="language" content="es" />
		<meta http-equiv="cache-control" content="no-cache">
		</head>
		<body>
			<center>
				<h1>Este directorio no puede ser consultado en forma directa, se encuentra protegido</h1>
			</center>
		</body>
	</html>';
        fwrite($f, $txt);
        fclose($f);
    }
    return true;
}

function desencriptar($cadena, $clave) {
    $cifrado = MCRYPT_RIJNDAEL_256;
    $modo = MCRYPT_MODE_ECB;
    return mcrypt_decrypt($cifrado, $clave, $cadena, $modo, mcrypt_create_iv(mcrypt_get_iv_size($cifrado, $modo), MCRYPT_RAND));
}

function encriptar($cadena, $clave) {
    $cifrado = MCRYPT_RIJNDAEL_256;
    $modo = MCRYPT_MODE_ECB;
    return mcrypt_encrypt($cifrado, $clave, $cadena, $modo, mcrypt_create_iv(mcrypt_get_iv_size($cifrado, $modo), MCRYPT_RAND));
}

function encontrarExtensionSii2($file) {
    $filename = strtolower($file);
    $exts = explode(".", $filename);
    $n = count($exts) - 1;
    $exts1 = $exts[$n];
    $exts2 = '';
    $arr1 = str_split($exts1);
    if ($arr1 && !empty($arr1)) {
        $fin = 'no';
        foreach ($arr1 as $x1) {
            if ($fin == 'no') {
                if ($x1 != '?' && $x1 != '&') {
                    $exts2 .= $x1;
                } else {
                    $fin = 'si';
                }
            }
        }
    }
    unset($arr1);
    unset($exts);
    return $exts2;
}

function generarAleatorioAlfanumericoSii2($tamano = 6) {
    $numrecvalido = 'NO';
    while ($numrecvalido == 'NO') {
        $ok = 'NO';
        while ($ok == 'NO') {
            $alfanumerico = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
            $num = '';
            for ($i = 1; $i <= $tamano; $i++) {
                $num .= substr(substr($alfanumerico, rand(1, strlen($alfanumerico))), 0, 1);
            }
            if (strlen($num) == $tamano) {
                $ok = 'SI';
            }
        }
        $numrecvalido = 'SI';
    }
    return $num;
}

/**
 * Ordena una matriz por un indice (campo) dado 
 */
function ordenarMatrizSii2($arreglo, $campo, $inverse = false) {
    $position = array();
    $newRow = array();
    foreach ($arreglo as $key => $row) {
        $position[$key] = $row[$campo];
        $newRow[$key] = $row;
    }
    if ($inverse) {
        arsort($position);
    } else {
        asort($position);
    }
    $returnArray = array();
    foreach ($position as $key => $pos) {
        $returnArray[] = $newRow[$key];
    }
    return $returnArray;
}

function recuperarImagenRepositorioSii2($img, $sistema = '', $paginas = '', $inicial = '') {
    require_once ('../../../configuracion/common' . $_SESSION["generales"]["codigoempresa"] . '.php');
    if (!defined('TIPO_REPOSITORIO_NUEVAS_IMAGENES')) {
        define('TIPO_REPOSITORIO_NUEVAS_IMAGENES', 'LOCAL');
    }


    //
    $retornar = '';
    $_SESSION["generales"]["mensajeerror"] = 'Imagen no pudo ser recuperada de los repositorios';

    // 2016-03-14 : JINT : Solo en caso que la imagen tenga como sistema origen a DOCUWARE
    // 2018-09-19 : JINT : Se ajusta para que busque en s3
    if ($sistema == 'DOCUWARE') {
        if (TIPO_REPOSITORIO_NUEVAS_IMAGENES == '' || TIPO_REPOSITORIO_NUEVAS_IMAGENES == 'LOCAL') {
            $filex = '../../../tmp/' . $inicial . '-' . date("Ymd") . '-' . date("His") . ".tif";
            $pathx = str_replace(array("//" . sprintf("%08s", $inicial) . '.001', "/" . sprintf("%08s", $inicial) . '.001'), "", $img);
            $arregloPath = explode("/", $pathx);
            $ultimo = count($arregloPath) - 1;
            $pags = 1;
            $intentos = 0;
            $command = 'tiffcp ';
            while ($pags <= $paginas && $intentos < 300) {
                $intentos++;
                $pathx = '';
                foreach ($arregloPath as $p) {
                    if ($pathx != '')
                        $pathx .= '/';
                    $pathx .= $p;
                }
                $img = '../../../' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/' . $pathx . '/' . sprintf("%08s", $inicial) . '.' . sprintf("%03s", $pags);
                if (file_exists($img)) {
                    $command .= $img . ' ';
                    $pags++;
                    $inicial++;
                } else {
                    if (is_numeric($arregloPath[$ultimo])) {
                        $x1 = intval($arregloPath[$ultimo]) + 1;
                        $arregloPath[$ultimo] = sprintf("%03s", $x1);
                    } else {
                        $ultimo--;
                    }
                }
            }
            $command .= $filex;
            shell_exec($command);
            return $filex;
        }
        if (TIPO_REPOSITORIO_NUEVAS_IMAGENES == 'EFS_S3') {
            $filex = '../../../tmp/' . $inicial . '-' . date("Ymd") . '-' . date("His") . ".tif";
            $pathx = str_replace(array("//" . sprintf("%08s", $inicial) . '.001', "/" . sprintf("%08s", $inicial) . '.001'), "", $img);
            $arregloPath = explode("/", $pathx);
            $ultimo = count($arregloPath) - 1;
            $pags = 1;
            $intentos = 0;
            $command = 'tiffcp ';
            while ($pags <= $paginas && $intentos < 300) {
                $intentos++;
                $pathx = '';
                foreach ($arregloPath as $p) {
                    if ($pathx != '')
                        $pathx .= '/';
                    $pathx .= $p;
                }
                $img = apiRecuperarS3Version4($pathx . '/' . sprintf("%08s", $inicial) . '.' . sprintf("%03s", $pags));
                if (file_exists($img)) {
                    $command .= $img . ' ';
                    $pags++;
                    $inicial++;
                } else {
                    if (is_numeric($arregloPath[$ultimo])) {
                        $x1 = intval($arregloPath[$ultimo]) + 1;
                        $arregloPath[$ultimo] = sprintf("%03s", $x1);
                    } else {
                        $ultimo--;
                    }
                }
            }
            $command .= $filex;
            shell_exec($command);
            return $filex;
        }
    }

    // Si el repositorio es Local
    if (TIPO_REPOSITORIO_NUEVAS_IMAGENES == '' || TIPO_REPOSITORIO_NUEVAS_IMAGENES == 'LOCAL' || TIPO_REPOSITORIO_NUEVAS_IMAGENES == 'EFS_S3') {
        if (file_exists('../../../' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/' . $img)) {
            $retornar = '../../../' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/' . $img;
            $_SESSION["generales"]["mensajeerror"] = '';
        } else {
            if (file_exists('../../../' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/' . str_replace("RAD", "rad", $img))) {
                $retornar = '../../../' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/' . $img;
                $_SESSION["generales"]["mensajeerror"] = '';
            } else {
                if (file_exists('../../../' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/' . str_replace("rad", "RAD", $img))) {
                    $retornar = '../../../' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/' . $img;
                    $_SESSION["generales"]["mensajeerror"] = '';
                } else {
                    if (!defined('REPOSITORIO_REMOTO_IMAGENES_WS')) {
                        define('REPOSITORIO_REMOTO_IMAGENES_WS', '');
                    }
                    if (REPOSITORIO_REMOTO_IMAGENES_WS != '') {
                        if (verificarImagenWsRemoto($img)) {
                            $retornar = recuperarImagenWsRemoto($_SESSION["generales"]["codigoempresa"] . '/' . $img);
                        }
                    }
                }
            }
        }
    }

    // Si el repositorio es Remoto
    if (TIPO_REPOSITORIO_NUEVAS_IMAGENES == 'WS') {
        if (verificarImagenWsRemoto($img)) {
            $retornar = recuperarImagenWsRemoto($_SESSION["generales"]["codigoempresa"] . '/' . $img);
        }
    }

    // Si el repositorio es Amazon Aws S3
    if (TIPO_REPOSITORIO_NUEVAS_IMAGENES == 'S3-V4' || TIPO_REPOSITORIO_NUEVAS_IMAGENES == 'EFS_S3') {
        if (existenciaS3Version4_2($img)) {
            $retornar = recuperarS3Version4($img);
        } else {
            if (file_exists('../../../' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/' . $img)) {
                $retornar = '../../../' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/' . $img;
                $_SESSION["generales"]["mensajeerror"] = '';
            } else {
                if (file_exists('../../../' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/' . str_replace("RAD", "rad", $img))) {
                    $retornar = '../../../' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/' . $img;
                    $_SESSION["generales"]["mensajeerror"] = '';
                } else {
                    if (file_exists('../../../' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/' . str_replace("rad", "RAD", $img))) {
                        $retornar = '../../../' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/' . $img;
                        $_SESSION["generales"]["mensajeerror"] = '';
                    } else {
                        if (!defined('REPOSITORIO_REMOTO_IMAGENES_WS')) {
                            define('REPOSITORIO_REMOTO_IMAGENES_WS', '');
                        }
                        if (REPOSITORIO_REMOTO_IMAGENES_WS != '') {
                            if (verificarImagenWsRemoto($img)) {
                                $retornar = recuperarImagenWsRemoto($_SESSION["generales"]["codigoempresa"] . '/' . $img);
                            }
                        }
                    }
                }
            }
        }
    }

    return $retornar;
}

function reemplazarAcutesSii2($txt) {
    $txt = str_replace("&amp;", "&", $txt);
    $txt = str_replace("&AMP;", "&", $txt);
    $txt = str_replace("&aacute;", "á", $txt);
    $txt = str_replace("&eacute;", "é", $txt);
    $txt = str_replace("&iacute;", "í", $txt);
    $txt = str_replace("&oacute;", "ó", $txt);
    $txt = str_replace("&uacute;", "ú", $txt);
    $txt = str_replace("&Aacute;", "Á", $txt);
    $txt = str_replace("&Eacute;", "É", $txt);
    $txt = str_replace("&Iacute;", "Í", $txt);
    $txt = str_replace("&Oacute;", "Ó", $txt);
    $txt = str_replace("&Uacute;", "Ú", $txt);
    $txt = str_replace("&AACUTE;", "Á", $txt);
    $txt = str_replace("&EACUTE;", "É", $txt);
    $txt = str_replace("&IACUTE;", "Í", $txt);
    $txt = str_replace("&OACUTE;", "Ó", $txt);
    $txt = str_replace("&UACUTE;", "Ú", $txt);
    $txt = str_replace("&ntilde;", "ñ", $txt);
    $txt = str_replace("&Ntilde;", "Ñ", $txt);
    $txt = str_replace("&NTILDE;", "Ñ", $txt);
    $txt = str_replace("&NBSP;", " ", $txt);
    return $txt;
}

function reemplazarEspecialesSii2($txt) {

    //
    $txt = str_replace("\"", "[0]", $txt);
    $txt = str_replace("'", "[1]", $txt);
    $txt = str_replace("&", "[2]", $txt);
    // $txt = str_replace("?", "[3]", $txt);
    $txt = str_replace("á", "[4]", $txt);
    $txt = str_replace("é", "[5]", $txt);
    $txt = str_replace("í", "[6]", $txt);
    $txt = str_replace("ó", "[7]", $txt);
    $txt = str_replace("ú", "[8]", $txt);
    $txt = str_replace("ñ", "[9]", $txt);
    $txt = str_replace("Ñ", "[10]", $txt);
    // $txt = str_replace("+", "[11]", $txt);
    // $txt = str_replace("#", "[12]", $txt);
    $txt = str_replace("Á", "[13]", $txt);
    $txt = str_replace("É", "[14]", $txt);
    $txt = str_replace("Í", "[15]", $txt);
    $txt = str_replace("Ó", "[16]", $txt);
    $txt = str_replace("Ú", "[17]", $txt);
    $txt = str_replace("Ü", "[18]", $txt);
    $txt = str_replace("º", "[19]", $txt);
    $txt = str_replace("°", "[20]", $txt);
    //
    $txt = str_replace("ª", "[21]", $txt);
    $txt = str_replace("!", "[22]", $txt);
    $txt = str_replace("¡", "[23]", $txt);
    $txt = str_replace("'", "[24]", $txt);
    $txt = str_replace("´", "[25]", $txt);
    $txt = str_replace("`", "[26]", $txt);
    //
    $txt = str_replace("À", "[28]", $txt);
    $txt = str_replace("È", "[29]", $txt);
    $txt = str_replace("Ì", "[30]", $txt);
    $txt = str_replace("Ò", "[31]", $txt);
    $txt = str_replace("Ù", "[32]", $txt);
    //
    $txt = str_replace("à", "[33]", $txt);
    $txt = str_replace("è", "[34]", $txt);
    $txt = str_replace("ì", "[35]", $txt);
    $txt = str_replace("ò", "[36]", $txt);
    $txt = str_replace("ù", "[37]", $txt);

    //
    $txt = str_replace("©", "@", $txt);

    //             
    $txt = str_replace("[SALTOPARRAFO]", "", $txt);



    return $txt;
}

function restaurarEspecialesMayusculasSii2($txt) {
    $txt = str_replace("[0]", "\"", $txt);
    $txt = str_replace("[1]", "'", $txt);
    $txt = str_replace("[2]", "&", $txt);
    $txt = str_replace("[3]", "?", $txt);
    $txt = str_replace("[4]", "Á", $txt);
    $txt = str_replace("[5]", "É", $txt);
    $txt = str_replace("[6]", "Í", $txt);
    $txt = str_replace("[7]", "Ó", $txt);
    $txt = str_replace("[8]", "Ú", $txt);
    $txt = str_replace("[9]", "Ñ", $txt);
    $txt = str_replace("[10]", "Ñ", $txt);
    $txt = str_replace("[11]", "+", $txt);
    $txt = str_replace("[12]", "#", $txt);
    $txt = str_replace("[13]", "Á", $txt);
    $txt = str_replace("[14]", "É", $txt);
    $txt = str_replace("[15]", "Í", $txt);
    $txt = str_replace("[16]", "Ó", $txt);
    $txt = str_replace("[17]", "Ú", $txt);
    $txt = str_replace("[18]", "Ü", $txt);
    $txt = str_replace("[19]", "º", $txt);
    $txt = str_replace("[20]", "°", $txt);
    $txt = str_replace("[21]", "ª", $txt);
    //
    $txt = str_replace("[22]", "!", $txt);
    $txt = str_replace("[23]", "¡", $txt);
    $txt = str_replace("[24]", "'", $txt);
    $txt = str_replace("[25]", "´", $txt);
    $txt = str_replace("[26]", "`", $txt);
    //
    $txt = str_replace("[28]", "À", $txt);
    $txt = str_replace("[29]", "È", $txt);
    $txt = str_replace("[30]", "Ì", $txt);
    $txt = str_replace("[31]", "Ò", $txt);
    $txt = str_replace("[32]", "Ù", $txt);
    //
    $txt = str_replace("[33]", "à", $txt);
    $txt = str_replace("[34]", "è", $txt);
    $txt = str_replace("[35]", "ì", $txt);
    $txt = str_replace("[36]", "ò", $txt);
    $txt = str_replace("[37]", "ù", $txt);

    $txt = str_replace("[39]", "Ñ", $txt);
    //
    return $txt;
}

function restaurarEspecialesSii2($txt) {
    $txt = str_replace("[0]", "\"", $txt);
    $txt = str_replace("[1]", "'", $txt);
    $txt = str_replace("[2]", "&", $txt);
    $txt = str_replace("[3]", "?", $txt);
    $txt = str_replace("[4]", "á", $txt);
    $txt = str_replace("[5]", "é", $txt);
    $txt = str_replace("[6]", "í", $txt);
    $txt = str_replace("[7]", "ó", $txt);
    $txt = str_replace("[8]", "ú", $txt);
    $txt = str_replace("[9]", "ñ", $txt);
    $txt = str_replace("[10]", "Ñ", $txt);
    $txt = str_replace("[11]", "+", $txt);
    $txt = str_replace("[12]", "#", $txt);
    $txt = str_replace("[13]", "Á", $txt);
    $txt = str_replace("[14]", "É", $txt);
    $txt = str_replace("[15]", "Í", $txt);
    $txt = str_replace("[16]", "Ó", $txt);
    $txt = str_replace("[17]", "Ú", $txt);
    $txt = str_replace("[18]", "Ü", $txt);
    $txt = str_replace("[19]", "º", $txt);
    $txt = str_replace("[20]", "°", $txt);
    $txt = str_replace("[21]", "ª", $txt);
    //
    $txt = str_replace("[22]", "!", $txt);
    $txt = str_replace("[23]", "¡", $txt);
    $txt = str_replace("[24]", "'", $txt);
    $txt = str_replace("[25]", "´", $txt);
    $txt = str_replace("[26]", "`", $txt);
    //
    $txt = str_replace("[28]", "À", $txt);
    $txt = str_replace("[29]", "È", $txt);
    $txt = str_replace("[30]", "Ì", $txt);
    $txt = str_replace("[31]", "Ò", $txt);
    $txt = str_replace("[32]", "Ù", $txt);
    //
    $txt = str_replace("[33]", "à", $txt);
    $txt = str_replace("[34]", "è", $txt);
    $txt = str_replace("[35]", "ì", $txt);
    $txt = str_replace("[36]", "ò", $txt);
    $txt = str_replace("[37]", "ù", $txt);
    //
    return $txt;
}

function restaurarEspecialesRazonSocialSii2($txt) {
    $txt = str_replace("[0]", "", $txt);
    $txt = str_replace("[1]", "", $txt);
    $txt = str_replace("[2]", "&", $txt);
    $txt = str_replace("[3]", "?", $txt);
    $txt = str_replace("[4]", "á", $txt);
    $txt = str_replace("[5]", "é", $txt);
    $txt = str_replace("[6]", "í", $txt);
    $txt = str_replace("[7]", "ó", $txt);
    $txt = str_replace("[8]", "ú", $txt);
    $txt = str_replace("[9]", "ñ", $txt);
    $txt = str_replace("[10]", "Ñ", $txt);
    $txt = str_replace("[11]", "+", $txt);
    $txt = str_replace("[12]", "#", $txt);
    $txt = str_replace("[13]", "Á", $txt);
    $txt = str_replace("[14]", "É", $txt);
    $txt = str_replace("[15]", "Í", $txt);
    $txt = str_replace("[16]", "Ó", $txt);
    $txt = str_replace("[17]", "Ú", $txt);
    $txt = str_replace("[18]", "Ü", $txt);
    $txt = str_replace("[19]", "º", $txt);
    $txt = str_replace("[20]", "°", $txt);
    //
    $txt = str_replace("[21]", "ª", $txt);
    $txt = str_replace("[22]", "!", $txt);
    $txt = str_replace("[23]", "¡", $txt);
    $txt = str_replace("[24]", "'", $txt);
    $txt = str_replace("[25]", "´", $txt);
    $txt = str_replace("[26]", "`", $txt);
    //
    $txt = str_replace("[28]", "À", $txt);
    $txt = str_replace("[29]", "È", $txt);
    $txt = str_replace("[30]", "Ì", $txt);
    $txt = str_replace("[31]", "Ò", $txt);
    $txt = str_replace("[32]", "Ù", $txt);
    //
    $txt = str_replace("[33]", "à", $txt);
    $txt = str_replace("[34]", "è", $txt);
    $txt = str_replace("[35]", "ì", $txt);
    $txt = str_replace("[36]", "ò", $txt);
    $txt = str_replace("[37]", "ù", $txt);
    //
    return $txt;
}

function apiRecuperarS3Version4($file) {
    if (isset($_SESSION["generales"]["pathabsoluto"])) {
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/configuracion/commonPredefiniciones.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common' . $_SESSION["generales"]["codigoempresa"] . '.php');
        include_once ($_SESSION["generales"]["pathabsoluto"] . "/includes/aws/aws-autoloader.php");
    } else {
        require_once ('../../../configuracion/common.php');
        require_once ('../../../configuracion/commonPredefiniciones.php');
        require_once ('../../../configuracion/common' . $_SESSION["generales"]["codigoempresa"] . '.php');
        include_once ('../../../includes/aws/aws-autoloader.php');
    }

    $retornar = '';
    $_SESSION["generales"]["mensajerror"] = 'Imagen no pudo ser recuperada de S3';

    //
    include_once ($_SESSION["generales"]["pathabsoluto"] . "/includes/aws/aws-autoloader.php");

    //
    $sharedConfig = [
        'region' => 'us-east-1',
        'version' => 'latest',
        'credentials' => [
            'key' => S3_awsAccessKey,
            'secret' => S3_awsSecretKey,
        ],
    ];

    $sdk = new Aws\Sdk($sharedConfig);
    $s3Client = $sdk->createS3();

    $name = '../../../tmp/' . $_SESSION["generales"]["codigoempresa"] . '-' . generarAleatorioAlfanumericoSii2(20) . '.' . encontrarExtensionSii2($file);
    $keyf = $_SESSION["generales"]["codigoempresa"] . '/' . $file;
    try {
        // Get the object
        $result = $s3Client->getObject([
            'Bucket' => S3_bucket,
            'Key' => $keyf,
            'SaveAs' => $name
        ]);
        $retornar = $name;
        $_SESSION["generales"]["mensajerror"] = '';
    } catch (S3Exception $e) {
        $retornar = false;
    }

    // 
    unset($s3Client);
    unset($sdk);
    return $retornar;
}

?>

<?php

namespace apisistema;
use JWT;

class funciones {

    public function generarJWT($empresa, $usuariows) {
        require ('JWT.php');
        $key = 'DEVELOPER';
        if (defined('API_SII_DURACION_TOKEN') && trim(API_SII_DURACION_TOKEN) != '') {
            $minutos_token = API_SII_DURACION_TOKEN;
        } else {
            $minutos_token = 60;
        }
        $estampaUnixSolicitud = strtotime(date('Ymd H:i:s'));
        $estampaUnixCaducidad = strtotime('+' . $minutos_token . ' min', $estampaUnixSolicitud);
        $token = array(
            "iat" => $estampaUnixSolicitud,
            "exp" => $estampaUnixCaducidad,
            "data" => array(
                "id" => $empresa,
                "sub" => $usuariows,
            )
        );
        $tokenJWT = JWT::encode($token, $key, 'HS256');
        return $tokenJWT;
    }

    public function interpretarJWT($tokenJWT) {
        require ('JWT.php');
        $key = 'DEVELOPER';
        $respInteprete = JWT::decode(trim($tokenJWT), $key);
        return $respInteprete;
    }

    public function generarAleatorioAlfanumericoToken() {
        $ok = 'NO';
        while ($ok == 'NO') {
            $alfanumerico = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
            $num = '';
            for ($i = 1; $i <= 30; $i++) {
                $num .= substr(substr($alfanumerico, rand(1, strlen($alfanumerico))), 0, 1);
            }
            if (contarRegistrosMysqli(null,'mreg_api_sii_tokens', "token='" . $num . "'") == 0) {
                $ok = 'SI';
            }
        }
        return $num;
    }

    public function franquicia($id) {
        $p_otrosmedios = array("_PSE_");
        $p_banco = array("AV_AV", "AV_BB", "AV_BO", "AV_BP", "T1_BC", "T1_CV");
        $p_visa = array("CR_VE", "CR_VS", "V_VBV");
        $p_mastercard = array("RM_MC");
        $p_americexpress = array("CR_AM");
        $p_credencial = array("CR_CR");
        $p_diners = array("CR_DN");

        $franquicia = "";

        if (in_array($id, $p_banco)) {
            $franquicia = "01";
        }
        if (in_array($id, $p_visa)) {
            $franquicia = "02";
        }
        if (in_array($id, $p_mastercard)) {
            $franquicia = "03";
        }
        if (in_array($id, $p_americexpress)) {
            $franquicia = "04";
        }
        if (in_array($id, $p_credencial)) {
            $franquicia = "05";
        }
        if (in_array($id, $p_diners)) {
            $franquicia = "06";
        }
        if (in_array($id, $p_otrosmedios)) {
            $franquicia = "07";
        }
        return $franquicia;
    }

    public function estadoRespuesta($id) {
        $notpay = array("00", "01", "02", "03", "04", "05", "19", "33");
        $pending = array("06", "66", "11", "77");
        $approved = array("07", "09", "10", "12", "13", "14", "15", "16", "17", "18", "20", "21", "22");
        $failed = array("08");

        if (in_array($id, $notpay)) {
            $estado = "NOT PAY";
        }
        if (in_array($id, $pending)) {
            $estado = "PENDING";
        }
        if (in_array($id, $approved)) {
            $estado = "APPROVED";
        }
        if (in_array($id, $failed)) {
            $estado = "FAILED";
        }
        return $estado;
    }

    public function homologacion_formatos_codificacionSII($mysqli,$servicios) {
        $n = 1;
        $resultado = 0;
        foreach ($servicios as $servicio) {
            $servicio = $servicio->idservicio;
            $codigoHomologacion = retornarRegistroMysqli($mysqli,'mreg_homologaciones_rue', "cod_rue='" . $servicio . "' ");
           //$codigoHomologacion = retornarRegistro('mreg_homologaciones_rue', "cod_rue='" . $servicio . "' ");
            if ($codigoHomologacion == false || count($codigoHomologacion) == 0) {
                $resultado = $servicio;
            }
            $n++;
        }
        return $resultado;
    }

    public function mostrarFecha($fec) {
        if ((trim($fec) == '') || (ltrim($fec, "0") == '')) {
            return '';
        }
        if (strlen($fec) == 10) {
            $fec = str_replace("/", "-", $fec);
            return $fec;
        } else {
            return substr($fec, 0, 4) . '-' . substr($fec, 4, 2) . '-' . substr($fec, 6, 2);
        }
    }

    public function obtenerUsuario($mysqli,$nombreCorto) {

        $filtroQuery = "(idcodigosirepcaja='" . $nombreCorto . "' or idcodigosirepdigitacion='" . $nombreCorto . "' or idcodigosirepregistro='" . $nombreCorto . "') and fechainactivacion='00000000' and eliminado='NO'";

        $arrUsuarioSII=retornarRegistroMysqli($mysqli,'usuarios', $filtroQuery);
        //$arrUsuarioSII = retornarRegistro('usuarios', $filtroQuery);
        if ($arrUsuarioSII !== null && $arrUsuarioSII !== false && $arrUsuarioSII !== 0 && count($arrUsuarioSII) > 0) {
            $idusuario = $arrUsuarioSII['idusuario'];
        } else {
            $idusuario = '';
        }

        return $idusuario;
    }

    public function descargaPdf($url, $directorio) {
        $newfname = $directorio;
        $file = fopen($url, 'rb');
        if ($file) {
            $newf = fopen($newfname, 'wb');
            if ($newf) {
                while (!feof($file)) {
                    fwrite($newf, fread($file, 1024 * 8), 1024 * 8);
                }
            }
        }
        if ($file) {
            fclose($file);
        }
        if ($newf) {
            fclose($newf);
        }
        return "success";
    }

    public function descargaPdfCurl($url, $filePath) {
        //$filePath = dirname(__FILE__) . '/test.pdf';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0);
        curl_setopt($ch, CURLOPT_TIMEOUT, 0);
        curl_setopt($ch, CURLOPT_FILE, $filePath);
        $result = curl_exec($ch);
        curl_close($ch);
        // $result = file_put_contents($filePath, $data);
        if (!$result) {
            echo "error";
        } else {
            echo "success";
        }
    }

    public function ubicarPDF($dbx,$nsobre) {
         $disco = \funcionesGenerales::encontrarPathImagen($dbx, \funcionesGenerales::tamanoArchivo($nsobre), 'sobredigitalmreg', '601');
        if ($disco === false) {
            return FALSE;
        }
        return TRUE;
    }

    public function obtenerNombrePDF($url) {
        $f = explode("/", $url);
        $arch = $f[count($f) - 1];
        return $arch;
    }

    public function obtenerTamanio($peso) {
        return round(($peso / 1048576), 2);
    }

}

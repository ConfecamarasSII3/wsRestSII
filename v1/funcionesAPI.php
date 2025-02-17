<?php

namespace libreriaswsRestSII;

use JWT;

class funcionesAPI
{

    public function generarJWT($empresa, $usuariows)
    {
        require('JWT.php');
        $key = 'WSIDEVELOPER';
        if (defined('API_SII_DURACION_TOKEN') && trim(API_SII_DURACION_TOKEN) != '') {
            $minutos_token = API_SII_DURACION_TOKEN;
        } else {
            $minutos_token = 480;
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

    public function interpretarJWT($tokenJWT)
    {
        require('JWT.php');
        $key = 'WSIDEVELOPER';
        $respInteprete = JWT::decode(trim($tokenJWT), $key);
        return $respInteprete;
    }

    public function generarAleatorioAlfanumericoToken()
    {
        $ok = 'NO';
        while ($ok == 'NO') {
            $alfanumerico = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
            $num = '';
            for ($i = 1; $i <= 30; $i++) {
                $num .= substr(substr($alfanumerico, rand(1, strlen($alfanumerico))), 0, 1);
            }
            if (contarRegistros('mreg_api_sii_tokens', "token='" . $num . "'") == 0) {
                $ok = 'SI';
            }
        }
        return $num;
    }


}

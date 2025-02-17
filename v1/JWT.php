<?php

/**
 * JSON Web Token implementation
 *
 * Minimum implementation used by Realtime auth, based on this spec:
 * http://self-issued.info/docs/draft-jones-json-web-token-01.html.
 *
 * @author Neuman Vong <neuman@twilio.com>
 */
class JWT {

    /**
     * @param string      $jwt    The JWT
     * @param string|null $key    The secret key
     * @param bool        $verify Don't skip verification process 
     *
     * @return object The JWT's payload as a PHP object
     */
    public static function decode($jwt, $key = null, $verify = true) {

        $ret["codigoerror"] = "0000";
        $ret["mensajeerror"] = '';
        $ret["propiedades"] ='';

        $tks = explode('.', $jwt);
        if (count($tks) != 3) {
            $ret["codigoerror"] = "0005";
            $ret["mensajeerror"] = 'Token incorrecto en número de segmentos';
            return $ret;
        }
        list($headb64, $payloadb64, $cryptob64) = $tks;
        if (null === ($header = JWT::jsonDecode(JWT::urlsafeB64Decode($headb64)))
        ) {
            $ret["codigoerror"] = "0005";
            $ret["mensajeerror"] = 'Codificación inválida del segmento header';
            return $ret;
        }
        if (null === $payload = JWT::jsonDecode(JWT::urlsafeB64Decode($payloadb64))
        ) {
            $ret["codigoerror"] = "0005";
            $ret["mensajeerror"] = 'Codificación inválida del segmento payload';
            return $ret;
        }

        $crtUnix = strtotime(date('Ymd H:i:s'));

        //Validar que estampa de creación no sea Superior a la estampa actual
        if (isset($payload->iat) && ($payload->iat > $crtUnix)) {
            $ret["codigoerror"] = "0005";
            $ret["mensajeerror"] = 'No se puede administrar token antes de su solicitud [' . date('Ymd H:i:s', $payload->iat) . ']';
            return $ret;
        }
        // Validar expiración del token
        if (isset($payload->exp) && ($crtUnix >= $payload->exp)) {
            $ret["codigoerror"] = "0005";
            $ret["mensajeerror"] = 'Token Expirado';
            return $ret;
        }

        $sig = JWT::urlsafeB64Decode($cryptob64);
        if ($verify) {
            if (empty($header->alg)) {
                $ret["codigoerror"] = "0005";
                $ret["mensajeerror"] = 'Algoritmo encriptado vacío';
                return $ret;
            }
            if ($sig != JWT::sign("$headb64.$payloadb64", $key, $header->alg)) {
                $ret["codigoerror"] = "0005";
                $ret["mensajeerror"] = 'Firma inválida';
                return $ret;
            }
        }

        $ret["codigoerror"] = "0000";
        $ret["mensajeerror"] = '';
        $ret["propiedades"] = $payload;

        return $ret;
    }

    /**
     * @param object|array $payload PHP object or array
     * @param string       $key     The secret key
     * @param string       $algo    The signing algorithm
     *
     * @return string A JWT
     */
    public static function encode($payload, $key, $algo = 'HS256') {
        $header = array ('typ' => 'JWT', 'alg' => $algo);
        $segments = array ();
        $segments[] = JWT::urlsafeB64Encode(JWT::jsonEncode($header));
        $segments[] = JWT::urlsafeB64Encode(JWT::jsonEncode($payload));
        $signing_input = implode('.', $segments);
        $signature = JWT::sign($signing_input, $key, $algo);
        $segments[] = JWT::urlsafeB64Encode($signature);
        return implode('.', $segments);
    }

    /**
     * @param string $msg    The message to sign
     * @param string $key    The secret key
     * @param string $method The signing algorithm
     *
     * @return string An encrypted message
     */
    public static function sign($msg, $key, $method = 'HS256') {
        $methods = array (
            'HS256' => 'sha256',
            'HS384' => 'sha384',
            'HS512' => 'sha512',
        );
        if (empty($methods[$method])) {
            throw new DomainException('Algorithm not supported');
        }
        return hash_hmac($methods[$method], $msg, $key, true);
    }

    /**
     * @param string $input JSON string
     *
     * @return object Object representation of JSON string
     */
    public static function jsonDecode($input) {
        $obj = json_decode($input);
        if (function_exists('json_last_error') && $errno = json_last_error()) {
            JWT::handleJsonError($errno);
        } else if ($obj === null && $input !== 'null') {
            throw new DomainException('Null result with non-null input');
        }
        return $obj;
    }

    /**
     * @param object|array $input A PHP object or array
     *
     * @return string JSON representation of the PHP object or array
     */
    public static function jsonEncode($input) {
        $json = json_encode($input);
        if (function_exists('json_last_error') && $errno = json_last_error()) {
            JWT::handleJsonError($errno);
        } else if ($json === 'null' && $input !== null) {
            throw new DomainException('Null result with non-null input');
        }
        return $json;
    }

    /**
     * @param string $input A base64 encoded string
     *
     * @return string A decoded string
     */
    public static function urlsafeB64Decode($input) {
        $remainder = strlen($input) % 4;
        if ($remainder) {
            $padlen = 4 - $remainder;
            $input .= str_repeat('=', $padlen);
        }
        return base64_decode(strtr($input, '-_', '+/'));
    }

    /**
     * @param string $input Anything really
     *
     * @return string The base64 encode of what you passed in
     */
    public static function urlsafeB64Encode($input) {
        return str_replace('=', '', strtr(base64_encode($input), '+/', '-_'));
    }

    /**
     * @param int $errno An error number from json_last_error()
     *
     * @return void
     */
    private static function handleJsonError($errno) {
        $messages = array (
            JSON_ERROR_DEPTH => 'Maximum stack depth exceeded',
            JSON_ERROR_CTRL_CHAR => 'Unexpected control character found',
            JSON_ERROR_SYNTAX => 'Syntax error, malformed JSON'
        );
        throw new DomainException(isset($messages[$errno]) ? $messages[$errno] : 'Unknown JSON error: ' . $errno
        );
    }

}

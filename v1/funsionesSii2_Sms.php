<?php

class funcionesSii2_sms {

    /**
     * Retorna el valor presupuestado para una cuenta o rango de cuentas 
     * @param       string   $ano     		A&ntilde;o del proceso
     * @param       string   $cuenta   		Cuenta presupuestal
     * @param       string   $proyecto     	Proyecto a revisar
     * @return      double                  Valor presupuestado
     */
    public static function enviarSms($celular, $mensaje) {
        require_once ('elibom/elibom.php');

        //
        $sms_proveedor = retornarClaveValorSii2('90.32.94');
        $sms_profile = retornarClaveValorSii2('90.32.95');
        $sms_usuario = retornarClaveValorSii2('90.32.97');
        $sms_clave = retornarClaveValorSii2('90.32.98');

        //
        $respuesta = array(
            'codigoError' => '0000',
            'msgError' => '',
            'deliveryCod' => ''
        );

        //
        if (trim($sms_proveedor) == '' || $sms_usuario == '' || $sms_clave == '') {
            $respuesta = array(
                'codigoError' => '9998',
                'msgError' => 'No est&aacute; parametrizado correctamente el proveedor de mensajes SMS',
                'deliveryCod' => ''
            );
            return $respuesta;
        }

        // ****************************************************************************************************************************** //
        // Envio de mensajes a traves de la plataforma SMS LABSMOBILE
        // ****************************************************************************************************************************** //
        if ($sms_proveedor == 'labsmobile') {
            $handler = 'https://api.labsmobile.com/get/send.php?username=' . $sms_usuario . '&password=' . $sms_clave . '&message=' . urlencode($mensaje) . '&msisdn=57' . $celular;
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $handler);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $resultado = curl_exec($ch);
            curl_close($ch);
            $respuesta = new SimpleXMLElement($resultado);

            //
            if ($respuesta->response[0]->code != '0') {
                $codigoError = '9999';
                $txtError = $respuesta->response[0]->code . ' - Error enviando - ' . $respuesta->response[0]->message;
                $respuesta = array(
                    'codigoError' => $codigoError,
                    'msgError' => 'No fue posible enviar el mensaje a trav&eacute;s de la plataforma labsmobile : ' . $txtError,
                    'deliveryCod' => ''
                );
                return $respuesta;
            } else {
                $respuesta = array(
                    'codigoError' => '0000',
                    'msgError' => '',
                    'deliveryCod' => $respuesta->response[0]->subid
                );
                return $respuesta;
            }
        }

        // ********************************************************************************** //
        // SI el proveedor es Elibom
        // ********************************************************************************** //
        if ($sms_proveedor == 'elibom') {

            //Filtro textos en SMS para evitar incidentes en el envio al usuario.
            $mensaje = str_replace("CAMARA DE COMERCIO DE", "C.C.", $mensaje);
            $mensaje = str_replace("-", "", $mensaje);
            $mensaje = str_replace("  ", " ", $mensaje);
            $mensaje = str_replace("MATRICULA", "MATRIC.", $mensaje);
            $mensaje = str_replace("CONSTITUCION", "CONST.", $mensaje);
            $mensaje = str_replace("MODIFICACION", "MODIF.", $mensaje);
            $mensaje = str_replace("CANCELACION", "CANCEL.", $mensaje);
            $mensaje = str_replace("ESCRITURA", "ESCRIT.", $mensaje);
            $mensaje = str_replace("REFORMAS", "REFOR.", $mensaje);
            $mensaje = str_replace("PERSONA", "PERS.", $mensaje);
            $mensaje = str_replace("JURIDICA", "JURID.", $mensaje);


            //Control de longitud.
            if (strlen($mensaje) > 160) {
                $respuesta = array(
                    'codigoError' => '9997',
                    'msgError' => 'el texto del SMS sobrepasa la longitud permitida (160 caracteres)',
                    'deliveryCod' => ''
                );
                return $respuesta;
            }
        }


        // ****************************************************************************************************************************** //
        // Envio de mensajes a traves de la plataforma SMS ELIBOM
        // ****************************************************************************************************************************** //
        if ($sms_proveedor == 'elibom') {
            $elibom = new \ElibomClient($sms_usuario, $sms_clave);
            if ($elibom === false) {
                $respuesta = array(
                    'codigoError' => '9997',
                    'msgError' => 'Error conectando con la plataforma ELIBOM',
                    'deliveryCod' => ''
                );
                return $respuesta;
            }
            try {
                $deliveryId = $elibom->sendMessage('57' . $celular, $mensaje);
            } catch (Exception $e) {
                $respuesta = array(
                    'codigoError' => '9997',
                    'msgError' => 'Error de excepcion al conectar con ELIBOM : ' . $e->getMessage(),
                    'deliveryCod' => ''
                );
                return $respuesta;
            }

            // unset ($elibom);
            if ($deliveryId === false) {
                $respuesta = array(
                    'codigoError' => '9999',
                    'msgError' => 'No fue posible enviar el mensaje a trav&eacute;s de la plataforma ELIBOM',
                    'deliveryCod' => ''
                );
                return $respuesta;
            } else {
                $token = (array) $deliveryId;
                $respuesta = array(
                    'codigoError' => '0000',
                    'msgError' => '',
                    'deliveryCod' => $token[0]
                );
                return $respuesta;
            }
        }

        // ****************************************************************************************************************************** //
        // Envio de mensajes a traves de la plataforma Alo
        // EndPoint: http://portal.colombitrade.com:8080/SendSimpleMessageService/SendSimpleMessage?wsdl
        // ****************************************************************************************************************************** //
        if ($sms_proveedor == 'alo') {

            // $client = new SoapClient("http://portal.colombitrade.com:8080/SendSimpleMessageService/SendSimpleMessage?wsdl");
            $parameters = array(
                "message" => array(
                    "user" => $sms_usuario,
                    "password" => $sms_clave,
                    "profile" => $sms_profile,
                    "address" => $celular,
                    "channel" => 'SMS',
                    "content" => $mensaje
                )
            );

            $message = '<message>';
            $message .= '<user>' . $sms_usuario . '</user>';
            $message .= '<password>' . $sms_clave . '</password>';
            $message .= '<profile>' . $sms_profile . '</profile>';
            $message .= '<address>' . $celular . '</address>';
            $message .= '<channel>SMS</channel>';
            $message .= '<content>' . $mensaje . '</content>';
            $message .= '</message>';

            //
            $client = new SoapClient("http://portal.colombitrade.com:8080/SendSimpleMessageService/SendSimpleMessage?wsdl");
            try {
                $result = $client->__soapCall('send-message', array('parameters' => $parameters));
                if (is_soap_fault($result)) {
                    $respuesta["codigoError"] = '9999';
                    $respuesta["msgError"] = utf8_decode("SOAP Fault: faultcode: " . $result->faultcode . ", faultstring: " . $result->faultstring);
                    return $respuesta;
                }
            } catch (Exception $e) {
                $respuesta["codigoError"] = '9999';
                $respuesta["msgError"] = utf8_decode("Excepci&oacute;n : " . $e->getMessage());
                return $respuesta;
            }

            if ($result === false) {
                $respuesta = array(
                    'codigoError' => '9999',
                    'msgError' => 'No fue posible enviar el mensaje a trav&eacute;s de la plataforma ALO Global Comunicaciones',
                    'deliveryCod' => ''
                );
                return $respuesta;
            } else {
                $t = (array) $result;
                if ($t["return"]->statusCode == '0') {
                    $respuesta = array(
                        'codigoError' => '0000',
                        'msgError' => '',
                        'deliveryCod' => $t["return"]->messageId
                    );
                    return $respuesta;
                }
                if ($t["return"]->statusCode != '0') {
                    $respuesta = array(
                        'codigoError' => '9999',
                        'msgError' => 'Error enviando SMS : StatusCode : ' . $t["return"]->statusCode . ' - StatusText : ' . $t["return"]->statusText . ' - MessageId : ' . $t["return"]->messageId,
                        'deliveryCod' => ''
                    );
                    return $respuesta;
                }
            }
        }

        // ****************************************************************************************************************************** //
        // Envio de mensajes a traves de la plataforma Alo_Global_Ws
        // EndPoint: http://www.portalsms.co/wsSMS/wsEnviosSMS.php?wsdl
        // ****************************************************************************************************************************** //
        if ($sms_proveedor == 'global_alo_ws') {

            // $client = new SoapClient("http://portal.colombitrade.com:8080/SendSimpleMessageService/SendSimpleMessage?wsdl");
            $parameters = array(
                "celular" => $celular,
                "mensaje" => $mensaje,
                "login" => $sms_usuario,
                "clave" => $sms_clave
            );

            $message = '<message>';
            $message .= '<user>' . $sms_usuario . '</user>';
            $message .= '<password>' . $sms_clave . '</password>';
            $message .= '<profile>' . $sms_profile . '</profile>';
            $message .= '<address>' . $celular . '</address>';
            $message .= '<channel>SMS</channel>';
            $message .= '<content>' . $mensaje . '</content>';
            $message .= '</message>';

            //
            $client = new SoapClient("http://www.portalsms.co/wsSMS/wsEnviosSMS.php?wsdl");
            try {
                $result = $client->__soapCall('getEnvioSMS', $parameters);
                if (is_soap_fault($result)) {
                    $respuesta["codigoError"] = '9999';
                    $respuesta["msgError"] = utf8_decode("SOAP Fault: faultcode: " . $result->faultcode . ", faultstring: " . $result->faultstring);
                    return $respuesta;
                }
            } catch (Exception $e) {
                $respuesta["codigoError"] = '9999';
                $respuesta["msgError"] = utf8_decode("Excepci&oacute;n : " . $e->getMessage());
                return $respuesta;
            }

            if ($result === false) {
                $respuesta = array(
                    'codigoError' => '9999',
                    'msgError' => 'No fue posible enviar el mensaje a trav&eacute;s de la plataforma ALO Global Comunicaciones',
                    'deliveryCod' => ''
                );
                return $respuesta;
            } else {
                $codigoError = '0000';
                $msgError = 'Enviado satisfactoriamente';
                $deliveryCod = '';
                switch (trim($result)) {
                    case "400" :
                        $codigoError = '9997';
                        $msgError = '400.- Usuario inactivo o datos de acceso invalidos';
                        break;
                    case "401" :
                        $codigoError = '9999';
                        $msgError = '401.- Linea no autorizada por la plataforma.';
                        break;
                    case "402" :
                        $codigoError = '9999';
                        $msgError = '402.- El contenido del mensaje es vacio.';
                        break;
                    case "404" :
                        $codigoError = '9997';
                        $msgError = '404.- Cupo de mensajes insuficientes.';
                        break;
                    case "407" :
                        $codigoError = '9997';
                        $msgError = '407.- No se realizo ninguna transaccion';
                        break;
                    case "408" :
                        $codigoError = '9999';
                        $msgError = '408.- Numero de celular errado, no es un numero, no tiene el formato de celular';
                        break;
                    case "412" :
                        $codigoError = '9997';
                        $msgError = '412.- Horario de envio no valido para la cuenta de usuario';
                        break;
                    default :
                        $deliveryCod = trim($result);
                        break;
                }
                $respuesta = array(
                    'codigoError' => $codigoError,
                    'msgError' => $msgError,
                    'deliveryCod' => $deliveryCod
                );
                return $respuesta;
            }
        }


        // ****************************************************************************************************************************** //
        // Envio de mensajes a traves de la plataforma CeoMarketing
        // ****************************************************************************************************************************** //
        if ($sms_proveedor == 'ceomarketing') {

            $handler = 'http://api.ceomarketing.co/api/v3/sendsms/plain?user=' . $sms_usuario . '&password=' . $sms_clave . '&sender=CAMARACOMERCIO&SMSText=' . urlencode($mensaje) . '&GSM=57' . $celular;
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $handler);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            // curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $resultado = curl_exec($ch);
            curl_close($ch);

            $respuesta = new SimpleXMLElement($resultado);

            //
            if ($respuesta->result[0]->status != '0') {
                $codigoError = '9999';
                switch ($respuesta->result[0]->status) {
                    case "-1" : $txtError = $respuesta->result[0]->status . ' - Error enviando';
                        $codigoError = '9997';
                        break;
                    case "-2" : $txtError = $respuesta->result[0]->status . ' - No hay cr&eacute;ditos disponibles';
                        $codigoError = '9997';
                        break;
                    case "-3" : $txtError = $respuesta->result[0]->status . ' - Red no descubierta';
                        $codigoError = '9997';
                        break;
                    case "-5" : $txtError = $respuesta->result[0]->status . ' - Usuario o password inv&aacute;lido';
                        $codigoError = '9997';
                        break;
                    case "-6" : $txtError = $respuesta->result[0]->status . ' - Destinatario inv&aacute;lido';
                        $codigoError = '9999';
                        break;
                    case "-10" : $txtError = $respuesta->result[0]->status . ' - Usuario inv&aacute;lido';
                        $codigoError = '9997';
                        break;
                    case "-11" : $txtError = $respuesta->result[0]->status . ' - Password inv&aacute;lido';
                        $codigoError = '9997';
                        break;
                    case "-13" : $txtError = $respuesta->result[0]->status . ' - Destino inv&aacute;lido';
                        $codigoError = '9999';
                        break;
                    case "-22" : $txtError = $respuesta->result[0]->status . ' - Error de sintaxis';
                        $codigoError = '9997';
                        break;
                    case "-23" : $txtError = $respuesta->result[0]->status . ' - Error de proceso';
                        $codigoError = '9997';
                        break;
                    case "-26" : $txtError = $respuesta->result[0]->status . ' - Error de comunicaci&oacute;n';
                        $codigoError = '9997';
                        break;
                    case "-27" : $txtError = $respuesta->result[0]->status . ' - Send Date inv&aacute;lido';
                        $codigoError = '9997';
                        break;
                    case "-28" : $txtError = $respuesta->result[0]->status . ' - Incorrecto PushURL';
                        $codigoError = '9997';
                        break;
                    case "-30" : $txtError = $respuesta->result[0]->status . ' - Incorrecto APPID';
                        $codigoError = '9997';
                        break;
                    case "-33" : $txtError = $respuesta->result[0]->status . ' - Mensaje duplicado';
                        $codigoError = '9999';
                        break;
                    case "-34" : $txtError = $respuesta->result[0]->status . ' - Remitente no habilitado';
                        $codigoError = '9999';
                        break;
                    case "-99" : $txtError = $respuesta->result[0]->status . ' - Error general';
                        $codigoError = '9997';
                        break;
                    default : $txtError = $respuesta->result[0]->status . ' - Error no controlado';
                        $codigoError = '9997';
                        break;
                }
                $respuesta = array(
                    'codigoError' => $codigoError,
                    'msgError' => 'No fue posible enviar el mensaje a trav&eacute;s de la plataforma Ceomarketing : ' . $txtError,
                    'deliveryCod' => ''
                );
                return $respuesta;
            } else {
                $respuesta = array(
                    'codigoError' => '0000',
                    'msgError' => '',
                    'deliveryCod' => $respuesta->result[0]->messageid
                );
                return $respuesta;
            }
        }

        // ****************************************************************************************************************************** //
        // Envio de mensajes a traves de la plataforma CeoMarketing
        // ****************************************************************************************************************************** //
        if ($sms_proveedor == 'masiv') {

            $handler = 'http://api.ceomarketing.co/api/v3/sendsms/plain?user=' . $sms_usuario . '&password=' . $sms_clave . '&sender=CAMARACOMERCIO&SMSText=' . urlencode($mensaje) . '&GSM=57' . $celular;
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $handler);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            // curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $resultado = curl_exec($ch);
            curl_close($ch);

            $respuesta = new SimpleXMLElement($resultado);

            //
            if ($respuesta->result[0]->status != '0') {
                $codigoError = '9999';
                switch ($respuesta->result[0]->status) {
                    case "-1" : $txtError = $respuesta->result[0]->status . ' - Error enviando';
                        $codigoError = '9997';
                        break;
                    case "-2" : $txtError = $respuesta->result[0]->status . ' - No hay cr&eacute;ditos disponibles';
                        $codigoError = '9997';
                        break;
                    case "-3" : $txtError = $respuesta->result[0]->status . ' - Red no descubierta';
                        $codigoError = '9997';
                        break;
                    case "-5" : $txtError = $respuesta->result[0]->status . ' - Usuario o password inv&aacute;lido';
                        $codigoError = '9997';
                        break;
                    case "-6" : $txtError = $respuesta->result[0]->status . ' - Destinatario inv&aacute;lido';
                        $codigoError = '9999';
                        break;
                    case "-10" : $txtError = $respuesta->result[0]->status . ' - Usuario inv&aacute;lido';
                        $codigoError = '9997';
                        break;
                    case "-11" : $txtError = $respuesta->result[0]->status . ' - Password inv&aacute;lido';
                        $codigoError = '9997';
                        break;
                    case "-13" : $txtError = $respuesta->result[0]->status . ' - Destino inv&aacute;lido';
                        $codigoError = '9999';
                        break;
                    case "-22" : $txtError = $respuesta->result[0]->status . ' - Error de sintaxis';
                        $codigoError = '9997';
                        break;
                    case "-23" : $txtError = $respuesta->result[0]->status . ' - Error de proceso';
                        $codigoError = '9997';
                        break;
                    case "-26" : $txtError = $respuesta->result[0]->status . ' - Error de comunicaci&oacute;n';
                        $codigoError = '9997';
                        break;
                    case "-27" : $txtError = $respuesta->result[0]->status . ' - Send Date inv&aacute;lido';
                        $codigoError = '9997';
                        break;
                    case "-28" : $txtError = $respuesta->result[0]->status . ' - Incorrecto PushURL';
                        $codigoError = '9997';
                        break;
                    case "-30" : $txtError = $respuesta->result[0]->status . ' - Incorrecto APPID';
                        $codigoError = '9997';
                        break;
                    case "-33" : $txtError = $respuesta->result[0]->status . ' - Mensaje duplicado';
                        $codigoError = '9999';
                        break;
                    case "-34" : $txtError = $respuesta->result[0]->status . ' - Remitente no habilitado';
                        $codigoError = '9999';
                        break;
                    case "-99" : $txtError = $respuesta->result[0]->status . ' - Error general';
                        $codigoError = '9997';
                        break;
                    default : $txtError = $respuesta->result[0]->status . ' - Error no controlado';
                        $codigoError = '9997';
                        break;
                }
                $respuesta = array(
                    'codigoError' => $codigoError,
                    'msgError' => 'No fue posible enviar el mensaje a trav&eacute;s de la plataforma Ceomarketing : ' . $txtError,
                    'deliveryCod' => ''
                );
                return $respuesta;
            } else {
                $respuesta = array(
                    'codigoError' => '0000',
                    'msgError' => '',
                    'deliveryCod' => $respuesta->result[0]->messageid
                );
                return $respuesta;
            }
        }

        // ****************************************************************************************************************************** //
        // Envio de mensajes a traves de la plataforma CeoMarketing
        // ****************************************************************************************************************************** //
        if ($sms_proveedor == 'stratec') {
            $handler = 'http://api.masiv.co/SmsHandlers/sendhandler.ashx?action=sendmessage&username=' . $sms_usuario . '&password=' . $sms_clave . '&messagedata=' . urlencode($mensaje) . '&recipient=57' . $celular;
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $handler);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $resultado = curl_exec($ch);
            curl_close($ch);

            //
            $respuesta = new SimpleXMLElement($resultado);
            // print_r ($respuesta);
            // echo "respuesta->action : " . $respuesta->action;
            // echo $respuesta->data->errorcode;
            // exit ();
            //
        if ($respuesta->action == 'sendmessage') {
                $codigoError = '0000';
                $msgError = '';
                $messageId = $respuesta->data->acceptreport->messageid;
                // if ($respuesta->data->acceptreport->messageid != '0') {
                //    $codigoError = '9999';
                //    $msgError = $respuesta->data->acceptreport->statusmessage;                
                // }

                $respuesta = array(
                    'codigoError' => $codigoError,
                    'msgError' => $msgError,
                    'deliveryCod' => $messageId
                );
                return $respuesta;
            }

            //
            if ($respuesta->action == 'error') {
                $codigoError = '9999';
                $msgError = $respuesta->data->errorcode;
                $messageId = '';
                switch ($respuesta->data->errorcode) {
                    case "1" : $msgError .= ' - Recipiente invalido';
                        $codigoError = '9999';
                        break;
                    case "1164" : $msgError .= ' - Faltan parametros en la solicitud';
                        $codigoError = '9999';
                        break;
                    case "1158" : $msgError .= ' - Parametro accion desconocido';
                        $codigoError = '9999';
                        break;
                    case "1157" : $msgError .= ' - Usuario o password invalido';
                        $codigoError = '9999';
                        break;
                    case "1156" : $msgError .= ' - Usuario no activado';
                        $codigoError = '9999';
                        break;
                    case "8734" : $msgError .= ' - Error en plataforma de SMS';
                        $codigoError = '9997';
                        break;
                    case "1159" : $msgError .= ' - Creditos insuficientes';
                        $codigoError = '9997';
                        break;
                    case "1160" : $msgError .= ' - Mensaje demasiado largo';
                        $codigoError = '9999';
                        break;
                }

                $respuesta = array(
                    'codigoError' => $codigoError,
                    'msgError' => $msgError,
                    'deliveryCod' => ''
                );
                return $respuesta;
            }
        }

        // ****************************************************************************************************************************** //
        // Envio de mensajes a traves de la plataforma Claro
        // ****************************************************************************************************************************** //
        if ($sms_proveedor == 'claro') {

            //
            $handler = 'http://mic.claro.com.co/servicesPME/app2mobile.jws';

            //
            $data1 = urlencode("method") . "=" . urlencode("sendSMS") . "&";
            $data1 .= urlencode("subscriber") . "=" . urlencode($celular) . "&";
            $data1 .= urlencode("domain") . "=" . urlencode($sms_profile) . "&";
            $data1 .= urlencode("message") . "=" . urlencode($mensaje) . "&";
            $data1 .= urlencode("date") . "=";

            //
            $additionalHeaders = array(
                'Content-Type:application/x-www-form-urlencoded; charset=UTF-8',
                'Content-Length: ' . strlen($data1),
                'Authorization: Basic ' . base64_encode($sms_usuario . ":" . $sms_clave)
            );

            //
            $ch = curl_init($handler);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_HTTPHEADER, $additionalHeaders);
            curl_setopt($ch, CURLOPT_HEADER, 1);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data1);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
            $return = curl_exec($ch);
            curl_close($ch);

            $resultCode = '';
            $operationId = '';
            $posR = '';
            $posO = '';
            $posR = strpos($return, 'resultCode=');
            if ($posR) {
                $resultCode = '9999';
                $message = 'Mensaje de error no codificado';
                $posR = $posR + 11;
                if (substr($return, $posR, 1) == '0') {
                    $resultCode = '0';
                    $message = 'OK';
                }
                if (substr($return, $posR, 3) == '100') {
                    $resultCode = '100';
                    $message = 'Parametros erroneos';
                }
                if (substr($return, $posR, 3) == '200') {
                    $resultCode = '200';
                    $message = 'Usuario deshabilitado';
                }
                if (substr($return, $posR, 3) == '201') {
                    $resultCode = '201';
                    $message = 'Numero de telefono invalido';
                }
                if (substr($return, $posR, 3) == '202') {
                    $resultCode = '202';
                    $message = 'Numero de telefono no aprovisionado';
                }
                if (substr($return, $posR, 3) == '203') {
                    $resultCode = '203';
                    $message = 'Texto nulo';
                }
                if (substr($return, $posR, 3) == '204') {
                    $resultCode = '204';
                    $message = 'Compania inactiva';
                }
                if (substr($return, $posR, 3) == '206') {
                    $resultCode = '206';
                    $message = 'Fecha de despacho expirada';
                }
                if (substr($return, $posR, 3) == '208') {
                    $resultCode = '208';
                    $message = 'Exedio la cuota de mensajes';
                }
                if (substr($return, $posR, 3) == '250') {
                    $resultCode = '250';
                    $message = 'Fecha de despacho tiene limite de 30 dias';
                }
            }

            $posO = strpos($return, 'operationId=');
            if ($posO) {
                $posO = $posO + 12;
                $operationId = trim(substr($return, $posO));
            }

            if ($resultCode == '') {
                $resultCode = '9999';
                $operationId = '';
                $message = 'No fue posible consumir el servicio web de Claro para envio de SMS';
            }
            //
            $respuesta = array(
                'codigoError' => sprintf("%04s", $resultCode),
                'msgError' => $message,
                'deliveryCod' => $operationId
            );
            return $respuesta;
        }

        // ****************************************************************************************************************************** //
        // Env&iacute;o de mensajes a trav&eacute;s de la plataforma Aldeamo
        // ****************************************************************************************************************************** //
        if ($sms_proveedor == 'aldeamo') {

            //
            $handler = str_replace(" ", "%20", 'https://apismsi.aldeamo.com/smsr/r/hcws/smsSendGet/' . $sms_usuario . '/' . $sms_clave . '/' . $celular . '/57/' . trim($mensaje));

            //
            $ch = curl_init($handler);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
            curl_setopt($ch, CURLOPT_HEADER, false);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
            $return = curl_exec($ch);
            curl_close($ch);

            //
            list ($resultCode, $msg) = explode("|", $return);

            //
            if ($resultCode < 0) {
                $respuesta = array(
                    'codigoError' => $resultCode,
                    'msgError' => $msg,
                    'deliveryCod' => ''
                );
            } else {
                $respuesta = array(
                    'codigoError' => '0000',
                    'msgError' => 'Envio satisfactorio',
                    'deliveryCod' => $resultCode
                );
            }
            return $respuesta;
        }
    }

}

?>
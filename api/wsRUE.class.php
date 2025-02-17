<?php
//Prueba SVN
/**
 * @package funciones
 * @author Edwin Avila G&oacute;mez
 * @since 2009/08/27
 *
 */

$empresa = $_SESSION["generales"]["codigoempresa"];

require_once '../../configuracion/common.php';
require_once '../../configuracion/common'.$empresa.'.php';

/**
 * Clase que crea diferentes cliente  webservice del RUE 
 * 
 * @author Administrator
 *
 */
class wsRUE {

	/**
	 * Objeto de contiene el cliente de conexion
	 * @var object SoapClient
	 */
	protected  $cliente;
	/**
	 * Almacena los posibles errores
	 * @var String
	 */
	protected $mensajeError;
	
	/**
	 * Almacena el codigo error
	 * @var String
	 */
	protected $codigoError;
	
	/**
	 * Almacena el nombre del archivo
	 * @var
	 */
	protected $archivoError;
	
	/**
	 * Almacena la linea del error
	 * @var
	 */
	protected $lineaError;
	
	/**
	 * Constructor del clase
	 * @param $wsdl String Direccion url del webserivice a consumir
	 * @return void
	 */
	public function wsRUE ($wsdl){
		
		try{	
			if (!extension_loaded('soap')) {
		        throw new Exception('\'SOAP extension\' no fue cargada, Revise su ZendCore y act&iacute;vela.',60001);
		    }
			
			if ( empty($wsdl) ) {
		        throw new Exception('La Ruta del WSDL no pudo ser le&iacute;da. Revise su parametrizaci&oacute;n.'.$wsdl,60002);
		    }
			
			$this->cliente = new SoapClient($wsdl,array(  'trace'      => true,
										                	'exceptions' => true,
										        			'encoding'=>'ISO-8859-1'
										        		)) ;
			if (is_soap_fault($this->cliente)) {
		        throw new Exception('No se puedo crear el cliente webservice.'.$wsdl,60003);
		    }
			return true; // Valor que describe que se creo el cliente webservice		
		}catch (Exception $e){
			$this->setMensajeError( $e->getMessage());
			$this->setCodigoError($e->getCode());
			$this->setArchivoError($e->getFile());
			$this->setLineaError($e->getLine());
			return false;
		}catch (SoapFault $fault){
			$this->setMensajeError( "No se puedo crear una instancia del cliente");			
			return false;					
		}
	}
	
	/**
	 * Encapsulamiento del mensaje de error
	 * @param $_mensaje
	 * @return void
	 */
	public function setMensajeError($_mensaje){
		$this->mensajeError = $_mensaje;
	}
	/**
	 * Encapsulamiento del mensaje de error
	 * @return String
	 */
	public function getMensajeError(){
		return $this->mensajeError;
	}	
	
	public function setCodigoError($_error){
		$this->codigoError = $_error;
	}
	
	public function getCodigoError(){
		return $this->codigoError;
	}
		
	public function setArchivoError($_archivo){
		$this->archivoError = $_archivo;
	}	
	
	public function getArhivoError(){
		return $this->archivoError;
	}
		
	public function setLineaError($_linea){
		$this->lineaError = $_linea;
	}	
	
	public function getLineaError(){
		return $this->lineaError;
	}

	
}

/**
 * Extencion de la clase wsRue adiciona los metodos del 
 * webservice del RUE RR09N
 * @author Edwin Avila G&ntilde;omez
 *
 */
class wsRR09N extends wsRUE{
	
	/*
	 * Constructor
	 */
	function wsRR09N(){
	}

	function init(){
		if(parent::wsRUE(wsRUE_RR09N)){
			return true;	//crea conexion de cliente con el rue
		}else{
			return false;	// No se puede crear conexion con el ws
		}
	}
	
	
	/**
	 * Consume el metodo ConsultaProponenteNIT
	 * @param $param array asociativo con los tags y los datos a radicar
	 * @return unknown_type
	 */	
	function ConsultaProponenteNIT( $param ){
		try{			
			if ($param === null) {
			        throw new Exception('No hay parametros para ejecutar el metodo crear_RUE_ProponenteNIT_BC.',60901);
			}else{
				$parametros = $this->crear_RUE_ProponenteNIT_BC($param);
							
				if($parametros === false){
					throw new Exception('El tipo de datos RUE_ProponenteNIT_BC no se creo satisfactoriamente',60902);
				}
							
				$result = @$this->cliente->__call('ConsultaProponenteNIT',$parametros);
				//unset($param);
				//unset($parametros);
				
				return $result;
			}
		}catch (SoapFault $fault){
			$mensaje = "SOAP Fault: (faultcode: {$fault->faultcode}, faultstring: {$fault->faultstring})";
			$this->setMensajeError($mensaje);
			return false;
		}
	}

	/**
	 * Consume el metodo RadicarNoticiaProponente
	 * @param $param array asociativo con ls tags y datos a radicar el dato que formaura es RUE_RegistroProponente_BC
	 * @return mixed Retorna false si hay errores o array si se consumio exitosamente
	 */
	function radicarNoticiaProponente( $param ){
		if ($param === null) {
		        throw new Exception('No hay parametros para ejecutar el metodo crear_RUE_ProponenteNIT_BC.');
		}else{
			$parametros = @$this->crear_RUE_RegistroProponente_BC($param);
			unset($param);
			try{
				$result = @$this->cliente->__call('radicarNoticiaProponente',$parametros );
				return $result;
			}catch (SoapFault $fault){
				$this->setMensajeError($fault->faultstring);
				return false;
			}
		}
	}
	
	/**
	 * crea el tipo de datos complejo RUE_RegistroProponente_BC
	 * @param $array Recibe un array con varios parametros y toma los necesario
	 * @return array tipo RUE_RegistroProponente_BC
	 */
	function crear_RUE_RegistroProponente_BC($array){
		
		if( !is_array($array)){
			$this->setMensajeError("Los parametros no tienen el formato requerido, para crear un tipo de dato RUE_RegistroProponente_BC");									
			return false;
		}
		if( count($array) == 20 ){
			$this->setMensajeError("La cantidad de parametros para crear el tipo de dato RUE_RegistroProponente_BC es menor a la requerida");			
			return false;
		}			

		$param['numero_interno'] 			= $array['numero_interno'];
        $param['usuario'] 					= $array['usuario'];
        $param['camara_comercio_proponente']= $array['camara_comercio_proponente'];
        $param['inscripcion_proponente'] 	= $array['inscripcion_proponente'];
        $param['numero_identificacion'] 	= $array['numero_identificacion'];
        $param['digito_verificacion'] 		= $array['digito_verificacion'];
        $param['razon_social'] 				= $array['razon_social'];
        $param['codigo_libro'] 				= $array['codigo_libro'];
        $param['numero_inscripcion_libro'] 	= $array['numero_inscripcion_libro'];
        $param['codigo_acto_rup'] 			= $array['codigo_acto_rup'];
        $param['noticia'] 					= $array['noticia'];
        $param['codigo_estado_noticia'] 	= $array['codigo_estado_noticia'];
        $param['fecha_inscripcion_camara'] 	= $array['fecha_inscripcion_camara'];
        $param['hora_inscripcion_camara'] 	= $array['hora_inscripcion_camara'];
        $param['numero_publicacion_noticia']= $array['numero_publicacion_noticia'];
        $param['fecha_publicacion'] 		= $array['fecha_publicacion'];
        $param['hora_publicacion'] 			= $array['hora_publicacion'];
        $param['codigo_error'] 				= $array['codigo_error'];
        $param['mensaje_error'] 			= $array['mensaje_error'];
        $param['firma_digital'] 			= $array['firma_digital']; 
		
		return array('RUE_RegistroProponente_BC'=>$param);
	}
	
	/**
	 * crea un tipo de da datos complejo RUE_ProponenteNIT_BC
	 * @param $array Recibe un array con varios parametros y toma los necesarios
	 * @return array tipo RUE_ProponenteNIT_BC
	 */
	function crear_RUE_ProponenteNIT_BC($array){
		
		if( !is_array($array)){
			//$this->setMensajeError("Los parametros no tienen el formato requerido, para crear un tipo de dato RUE_ProponenteNIT_BC");									
			throw new Exception("Los parametros no tienen el formato requerido, para crear un tipo de dato RUE_ProponenteNIT_BC",60903);
			return false;
		}
		if( count($array) != 10 ){
			throw new Exception("La cantidad de parametros para crear el tipo de dato RUE_ProponenteNIT_BC es menor a la requerida",60903);
			//$this->setMensajeError("La cantidad de parametros para crear el tipo de dato RUE_ProponenteNIT_BC es menor a la requerida");			
			return false;
		}
				
		$param['numero_interno'] 				= $array['numero_interno'];
        $param['usuario'] 						= $array['usuario'];
        $param['cantidad_registros_out']		= $array['cantidad_registros_out'];
        $param['numero_identificacion_consulta']= $array['numero_identificacion_consulta'];
        $param['digito_verificacion_consulta'] 	= $array['digito_verificacion_consulta'];
        $param['digito_verificacion_consulta'] 	= $array['digito_verificacion_consulta'];
        $param['datos_respuesta'] 				= $array['datos_respuesta'];
        $param['codigo_error'] 					= $array['codigo_error'];
        $param['mensaje_error'] 				= $array['mensaje_error'];
        $param['firma_digital'] 				= $array['firma_digital']; 
		
		return array('RUE_ProponenteNIT_BC'=>$param);
	}	

	/**
	 * Genera un debug
	 * @return void
	 */
	function debug(){
		echo '<h1>Request</h1>';
		echo '<pre>'.htmlspecialchars($this->cliente->__getLastRequestHeaders()).'</pre>';
		echo '<pre>'.htmlspecialchars($this->cliente->__getLastRequest()).'</pre>';
		echo '<h1>Response</h1>';
		echo '<pre>'.htmlspecialchars($this->cliente->__getLastResponseHeaders()).'</pre>';
		echo '<pre>'.htmlspecialchars($this->cliente->__getLastResponse()).'</pre>';
	}
	
	
	
		
	/**
	 * Destructor
	 */
	function __destruct(){
		unset($this);
	}
		
}

/**
 * Clase 
 * @author Edwin
 *
 */
class wsRR02N extends wsRUE{


	function wsRR02N(){
		
	}

	function init(){
		if(parent::wsRUE(wsRUE_RR02N)){
			return true;	//crea conexion de cliente con el rue
		}else{
			return false;	// No se puede crear conexion con el ws
		}
	}	
	/**
	 * Consume el metodo RadicarNoticiaProponente
	 * @param $param array asociativo con ls tags y datos a radicar el dato que formaura es RUE_RegistroProponente_BC
	 * @return mixed Retorna false si hay errores o array si se consumio exitosamente
	 */
	function radicarRegistroProponente( $param ){
		$parametros = $this->crear_RUE_RegistroProponente_BC($param);
		unset($param);
		try{
			$result = @$this->cliente->__call('radicarRegistroProponente',$parametros );
			return $result;
		}catch (SoapFault $fault){
			$this->setMensajeError($fault->faultstring);
			return false;
		}
	}
	
	/**
	 * crea el tipo de datos complejo RUE_RegistroProponente_BC
	 * @param $array Recibe un array con varios parametros y toma los necesario
	 * @return array tipo RUE_RegistroProponente_BC
	 */
	function crear_RUE_RegistroProponente_BC($array){
		
		$param['numero_interno'] 				= $array['numero_interno'];
        $param['usuario'] 						= $array['usuario'];
        $param['camara_comercio_proponente']	= $array['camara_comercio_proponente'];
        $param['inscripcion_proponente'] 		= $array['inscripcion_proponente'];
   		 $param['codigo_camara'] 				= $array['camara_comercio_proponente'];
         $param['matricula'] 					= !empty($array['matricula']) ? $array['matricula'] : '0000000000' ;
        $param['razon_social'] 					= !empty($array['razon_social']) ? $array['razon_social']: null;
        	$param['sigla'] 						= null;
         $param['codigo_clase_identificacion'] 	= $array['codigo_clase_identificacion'];
        $param['numero_identificacion'] 		= $array['numero_identificacion'];
        $param['digito_verificacion'] 			= $array['digito_verificacion'];
         $param['autorizacion_datos'] 			= $array['autorizacion_datos'];
         $param['municipio_comercial'] 			= $array['municipio_comercial'];
         $param['direccion_comercial'] 			= $array['direccion_comercial'];
         $param['telefono_comercial'] 			= $array['telefono_comercial'];
         $param['fax_comercial'] 				= $array['fax_comercial'];        
        	$param['apartado_aereo_comercial'] 		= null;
         $param['municipio_fiscal'] 				= $array['municipio_fiscal'];
         $param['direccion_fiscal'] 				= $array['direccion_fiscal'];
         $param['telefono_fiscal'] 				= $array['telefono_fiscal'];
         $param['fax_fiscal'] 					= $array['fax_fiscal'];
        	$param['apartado_aereo_fiscal'] 		= null;
         $param['correo_electronico'] 			= $array['correo_electronico'];
         $param['codigo_estado_proponente'] 		= $array['codigo_estado_proponente'];
         $param['fecha_inscripcion'] 			= $array['fecha_inscripcion'];
        	$param['multas'] 						= null;
        	$param['sanciones'] 					= null;
        	$param['fecha_renovacion'] 				= null;
        	$param['fecha_cancelacion'] 			= null;
         $param['k_contratacion_constructor'] 	=  $array['k_contratacion_constructor'];
         $param['indicador_constructor'] 		= null;
         $param['k_contratacion_consultor'] 	= $array['k_contratacion_consultor'];
         $param['indicador_consultor'] 			= null;
         $param['k_contratacion_proveedor'] 	= $array['k_contratacion_proveedor'];
         $param['indicador_proveedor'] 			= null;
        	$param['informacion_adicional'] 		= null;
        	$param['codigo_error'] 					= null;
        	$param['mensaje_error'] 				= null;
        	$param['firma_digital'] 				= null; 
		
		return array('RUE_RegistroProponente_BC'=>$param);
	}
	
	function debug(){
		echo '<h1>Request</h1>';
		echo '<pre>'.htmlspecialchars($this->cliente->__getLastRequestHeaders()).'</pre>';
		echo '<pre>'.htmlspecialchars($this->cliente->__getLastRequest()).'</pre>';
		echo '<h1>Response</h1>';
		echo '<pre>'.htmlspecialchars($this->cliente->__getLastResponseHeaders()).'</pre>';
		echo '<pre>'.htmlspecialchars($this->cliente->__getLastResponse()).'</pre>';
	}
		
	function __destruct(){
		unset($this);
	}
}


/**
 * Clase 
 * @author Edwin
 *
 */
class wsMR03N extends wsRUE{


	function wsMR03N(){
		
	}

	function init(){
		if(parent::wsRUE(wsRUE_MR03N)){
			return true;	//crea conexion de cliente con el rue
		}else{
			return false;	// No se puede crear conexion con el ws
		}
	}	
	/**
	 * Consume el metodo RadicarNoticiaProponente
	 * @param $param array asociativo con ls tags y datos a radicar el dato que formaura es RUE_RegistroProponente_BC
	 * @return mixed Retorna false si hay errores o array si se consumio exitosamente
	 */
	function solicitudActualizacionEstado( $param ){
		
		$parametros=array();
		$parametros = self::RUE_ActualizacionEstado_BC($param);
		unset($param);
		try{
			$result = $this->cliente->__call('solicitudActualizacionEstado',$parametros );
			return $result;
			
		}catch(Exception $e ){			
			$this->setMensajeError( $e->getMessage());
			$this->setCodigoError($e->getCode());
			$this->setArchivoError($e->getFile());
			$this->setLineaError($e->getLine());
			return false;
		}	
		/*
		}catch (SoapFault $fault){
			$this->setMensajeError($fault->faultstring);
			return false;
		}
		*/
	}
	
	/**
	 * crea el tipo de datos complejo RUE_RegistroProponente_BC
	 * numero_interno
	 * usuario
	 * estado_transaccion
	 * anexos
	 * estado
	 * fecha_respuesta
	 * hora_respuesta
	 * codigo_error
	 * mensaje_error
	 * firma_digital
	 * 
	 * @param $array Recibe un array con varios parametros y toma los necesario
	 * @return array tipo RUE_RegistroProponente_BC
	 */
	function RUE_ActualizacionEstado_BC($array){
		
		$param = array();
		$param['numero_interno'] 		= $array['numero_interno'];
        $param['usuario'] 				= $array['usuario'];
        $param['estado_transaccion']	= $array['estado_transaccion'];
        $param['anexos'] 				= $array['anexos'];
   		$param['estado'] 				= $array['estado'];
        $param['fecha_respuesta'] 		= $array['fecha_respuesta'];
        $param['hora_respuesta'] 		= $array['hora_respuesta'];
        $param['codigo_error'] 			= null;
        $param['mensaje_error'] 		= null;
        $param['firma_digital'] 		= null; 
		
		return array('RUE_ActualizacionEstado_BC'=>$param);
	}
	
	function debug(){
		echo '<h1>Request</h1>';
		echo '<pre>'.htmlspecialchars($this->cliente->__getLastRequestHeaders()).'</pre>';
		echo '<pre>'.htmlspecialchars($this->cliente->__getLastRequest()).'</pre>';
		echo '<h1>Response</h1>';
		echo '<pre>'.htmlspecialchars($this->cliente->__getLastResponseHeaders()).'</pre>';
		echo '<pre>'.htmlspecialchars($this->cliente->__getLastResponse()).'</pre>';
	}
		
	function __destruct(){
		unset($this);
	}
}


?>
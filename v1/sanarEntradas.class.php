<?php

class sanarEntradas {

	private static $letras = "aábcdeéfghiíjklmnñoópqrstuúvwxyzABCDEFGHIJKLMNÑOPQRSTUVWXYZ";
	private static $letrasUsuario = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789_-";
	private static $string = "aábcdeéfghiíjklmnñoópqrstuúvwxyzAÁBCDEÉFGHIÍJKLMNÑOÓPQRSTUÚVWXYZ0123456789 .-+&%=@$#(){}!?*'_/:";
	private static $enteros = "0123456789";
	private static $flotante = "0123456789.,-+";
	private static $stringRazonSocial = "aábcdeéfghiíjklmnñoópqrstuúvwxyzABCDEFGHIJKLMNÑOPQRSTUVWXYZ0123456789 .-+&=@#()!*'_:";
        private static $stringRazonSocialBasicos = "aábcdeéfghiíjklmnñoópqrstuúvwxyzABCDEFGHIJKLMNÑOPQRSTUVWXYZ0123456789 .-+&@!*_";
        private static $stringDireccion = "aábcdeéfghiíjklmnñoópqrstuúvwxyzABCDEFGHIJKLMNÑOPQRSTUVWXYZ0123456789 .-=#+";
	
	private function __construct(){
	}

	/**
	 * Construye arreglos para validaci&oacute;n a partir de los string de la clase
	 */
	public static function armarArreglo ($txt) {
		$arreglo=array ();
		$j=-1;
		for ($i=0;$i<strlen ($txt);$i++) {
			$char=substr ($txt,$i,1);			
			$arreglo [$char] = $char;
		}	
		return $arreglo;
	}

	private static function quitarCaracteres ($arreglo,$string) {
		$txtsal='';
		for ($i=0;$i<strlen ($string);$i++) {
			$char=substr ($string,$i,1);
			if (isset ($arreglo[$char]))  {
                            $txtsal.=$char;
                        }
		}
		return $txtsal;
	}
	
	/**
	 * 
	 * Sanea el campo accion
	 * Solo se permiten letras y n&uacute;meros
	 */
	public static function sanarAccion ($txt){
                $txtsal = filter_var($txt, FILTER_SANITIZE_STRING);		
		$arrayLetras = self::armarArreglo (self::$letras);
		return self::quitarCaracteres ($arrayLetras,$txtsal);
	}
	
	/**
	 * 
	 * Sanea el campo direccion
	 * Solo se permiten caracteres string validos
	 */
	public static function sanarDireccion ($txt){
		// $txt = filter_var($txt, FILTER_SANITIZE_URL);
		$arrayString = self::armarArreglo (self::$stringDireccion);
		return self::quitarCaracteres ($arrayString,$txt);
	}
	
	/**
	 * 
	 * Sanea el campo Email
	 * Solo se permiten caracteres de n&uacute;meos de punto flotante
	 */
	public static function sanarEmail ($txt){
		$txtsal = filter_var($txt, FILTER_SANITIZE_EMAIL);
		return $txtsal;
	}
		
	
	/**
	 * 
	 * Sanea el campo empresa
	 * Solo se permiten n&uacute;meros
	 */
	public static function sanarEmpresa ($txt){
		$txtsal = filter_var($txt, FILTER_SANITIZE_NUMBER_INT);		
		return $txtsal;
	}

	/**
	 * 
	 * Sanea el campo entero
	 * Solo se permiten n&uacute;meros
	 */
	public static function sanarEntero ($txt){
		$txtsal = filter_var($txt, FILTER_SANITIZE_NUMBER_INT);		
		return $txtsal;
	}

	/**
	 * 
	 * Sanea fecha
	 * Solo se permiten n&uacute;meros
	 */
	public static function sanarFecha ($txt){
		$txt = str_replace (array ("/","-","."),"",$txt);
		$txtsal = filter_var($txt, FILTER_SANITIZE_NUMBER_INT);		
		return $txtsal;
	}
	
	/**
	 * 
	 * Sanea el campo identificacion y nit
	 * Solo se permiten n&uacute;meros
	 */
	public static function sanarIdentificacion ($txt){
		$txtsal = filter_var($txt, FILTER_SANITIZE_NUMBER_INT);		
		return $txtsal;
	}
	
	/**
	 * 
	 * Sanea el campo nombre, apellidos
	 * Solo se permiten letras
	 */
	public static function sanarNombre ($txt){
		// $txtsal = filter_var($txt, FILTER_SANITIZE_STRING);		
		$arrayLetras = self::armarArreglo (self::$letras);		
		return self::quitarCaracteres ($arrayLetras,$txtsal);		
	}
	
	/**
	 * 
	 * Sanea el campo razon social
	 * Solo se permiten caracteres string validos
	 */
	public static function sanarRazonSocial ($txt){
		// $txtsal = filter_var($txt, FILTER_SANITIZE_URL);
		$arrayString = self::armarArreglo (self::$stringRazonSocial);
		return self::quitarCaracteres ($arrayString,$txt);
	}
        
	public static function sanarRazonSocialBasicos ($txt){
		// $txtsal = filter_var($txt, FILTER_SANITIZE_URL);
		$arrayString = self::armarArreglo (self::$stringRazonSocialBasicos);
		return self::quitarCaracteres ($arrayString,$txt);
	}
        
	
	/**
	 * 
	 * Sanea el campo string
	 * Solo se permiten caracteres string validos
	 */
	public static function sanarString ($txt){
		// $txtsal = filter_var($txt, FILTER_SANITIZE_STRING);
		$arrayString = self::armarArreglo (self::$string);
		return self::quitarCaracteres ($arrayString,$txt);
	}

	/**
	 * 
	 * Sanea el campo string
	 * Solo se permiten caracteres string validos
	 */
	public static function sanarStringConEspeciales ($txt){
		// $txtsal = filter_var($txt, FILTER_SANITIZE_URL);
		$arrayString = self::armarArreglo (self::$string);
		return self::quitarCaracteres ($arrayString,$txt);
	}
	
	/**
	 * 
	 * Sanea el campo tel&eacute;fono
	 * Solo se permiten n&uacute;meros
	 */
	public static function sanarTelefono ($txt){	
		$txtsal = filter_var($txt, FILTER_SANITIZE_NUMBER_INT);	
		return $txtsal;
	}

	/**
	 * 
	 * Sanea el campo URL
	 * Solo se permiten caracteres de n&uacute;meos de punto flotante
	 */
	public static function sanarUrl ($txt){
		$txtsal = filter_var($txt, FILTER_SANITIZE_URL);
		return $txtsal;
	}

	/**
	 * 
	 * Sanea el campo usuario
	 * Solo se permiten letras. n&uacute;meros -. _
	 */
	public static function sanarUsuario ($txt){
		$txtsal = filter_var($txt, FILTER_SANITIZE_STRING);		
		$arrayLetras = self::armarArreglo (self::$letrasUsuario);		
		return self::quitarCaracteres ($arrayLetras,$txtsal);	
	}
	
	/**
	 * 
	 * Sanea el campo valor
	 * Solo se permiten caracteres de números de punto flotante
	 */
	public static function sanarValor ($txt){
		$decimal = 0;
		$signo = 0;
		$txtsal = '';
		$txt = trim(ltrim($txt,"0"));
		for ($i=0;$i<strlen($txt);$i++) {
			$c = substr($txt,$i,1);
			if ($c=='-') {
				$signo++;
				if ($signo==1) {
					$txtsal.=$c;					
				}
			}
			if ($c=='.') {
				$decimal++;
				if ($decimal==1) {
					$txtsal.=$c;
				}
			}				
			if (($c>='0') && ($c<='9')) {
				$txtsal.=$c;
			}			
		} 
		if (trim($txtsal)=='') $txtsal = 0;
		return $txtsal;
	}
	
}
?>
<?php

/*
 * carga un objeto a Amazon S3
 */

class funcionesS3V4 {

    public static function tamanoS3Version4($path) {
        if (substr(PHP_VERSION, 0, 1) == '7') {
            require_once ($_SESSION["generales"]["pathabsoluto"] . '/components74/vendor/autoload.php');
        } else {
            if (substr(PHP_VERSION, 0, 1) == '8') {
                require_once ($_SESSION["generales"]["pathabsoluto"] . '/components/vendor/autoload.php');
            } else {
                require_once ($_SESSION["generales"]["pathabsoluto"] . '/components/vendor/autoload.php');
            }
        }

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

        $totalSize = 0;

        $objects = $s3Client->getBucket(S3_bucket . '/' . $path);
        foreach ($objects as $name => $val) {
            if (strpos($name, 'directory/sub-directory') !== false) {
                $totalSize += $val['size'];
            }
        }

        //
        $totalSize = $totalSize / 1024;

        // 
        unset($s3Client);
        unset($sdk);
        return $totalSize;
    }

    /*
     * carga un objeto a Amazon S3
     */

    public static function almacenarS3Version4($path, $imagen, $tipo = 'SII') {
        if (substr(PHP_VERSION, 0, 1) == '7') {
            require_once ($_SESSION["generales"]["pathabsoluto"] . '/components74/vendor/autoload.php');
        } else {
            if (substr(PHP_VERSION, 0, 1) == '8') {
                require_once ($_SESSION["generales"]["pathabsoluto"] . '/components/vendor/autoload.php');
            } else {
                require_once ($_SESSION["generales"]["pathabsoluto"] . '/components/vendor/autoload.php');
            }
        }

        if ($tipo == 'SII') {
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
            $result = $s3Client->putObject(array(
                'Bucket' => S3_bucket,
                'Key' => $imagen,
                'SourceFile' => $path
            ));
        }

        if ($tipo == 'RUES') {
            $sharedConfig = [
                'region' => 'us-east-1',
                'version' => 'latest',
                'credentials' => [
                    'key' => RUES_S3_awsAccessKey,
                    'secret' => RUES_S3_awsSecretKey,
                ],
            ];

            $sdk = new Aws\Sdk($sharedConfig);
            $s3Client = $sdk->createS3();
            $result = $s3Client->putObject(array(
                'Bucket' => RUES_S3_bucket,
                'Key' => $imagen,
                'SourceFile' => $path
            ));
            $_SESSION["generales"]["mensajeerror"] = $result['ObjectURL'];
        }

        // 
        unset($s3Client);
        unset($sdk);
        return true;
    }

    /*
     * Recupera un objeto desde Amazon S3
     */

    public static function recuperarS3Version4($file) {
        if (substr(PHP_VERSION, 0, 1) == '7') {
            require_once ($_SESSION["generales"]["pathabsoluto"] . '/components74/vendor/autoload.php');
        } else {
            if (substr(PHP_VERSION, 0, 1) == '8') {
                require_once ($_SESSION["generales"]["pathabsoluto"] . '/components/vendor/autoload.php');
            } else {
                require_once ($_SESSION["generales"]["pathabsoluto"] . '/components/vendor/autoload.php');
            }
        }

        $retornar = '';
        $_SESSION["generales"]["mensajerror"] = 'Imagen no pudo ser recuperada de S3';

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

        $name = $_SESSION["generales"]["pathabsoluto"] . '/tmp/' . $_SESSION["generales"]["codigoempresa"] . '-' . \funcionesGenerales::generarAleatorioAlfanumerico(20) . '.' . \funcionesGenerales::encontrarExtension($file);
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
        } catch (\Exception $e) {
            $retornar = false;
        }

        // 
        unset($s3Client);
        unset($sdk);
        return $retornar;
    }

    public static function existenciaS3Version4_2($file) {
        if (substr(PHP_VERSION, 0, 1) == '7') {
            require_once ($_SESSION["generales"]["pathabsoluto"] . '/components74/vendor/autoload.php');
        } else {
            if (substr(PHP_VERSION, 0, 1) == '8') {
                require_once ($_SESSION["generales"]["pathabsoluto"] . '/components/vendor/autoload.php');
            } else {
                require_once ($_SESSION["generales"]["pathabsoluto"] . '/components/vendor/autoload.php');
            }
        }

        //
        $retornar = false;
        $_SESSION["generales"]["mensajerror"] = 'Imagen no existe en S3';

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
        $keyf = $_SESSION["generales"]["codigoempresa"] . '/' . $file;

        if ($s3Client->doesObjectExist(S3_bucket, $keyf)) {
            $retornar = true;
        } else {
            $retornar = false;
        }
        // 
        unset($s3Client);
        unset($sdk);
        return $retornar;
    }

    /*
     * Verifica que un objeto exista en Amazon S3
     */

    public static function existenciaS3Version4($file) {
        if (substr(PHP_VERSION, 0, 1) == '7') {
            require_once ($_SESSION["generales"]["pathabsoluto"] . '/components74/vendor/autoload.php');
        } else {
            if (substr(PHP_VERSION, 0, 1) == '8') {
                require_once ($_SESSION["generales"]["pathabsoluto"] . '/components/vendor/autoload.php');
            } else {
                require_once ($_SESSION["generales"]["pathabsoluto"] . '/components/vendor/autoload.php');
            }
        }

        //
        $retornar = false;
        $_SESSION["generales"]["mensajerror"] = 'Imagen no existe en S3';


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
        $keyf = $_SESSION["generales"]["codigoempresa"] . '/' . $file;

        try {
            // Get the object
            $result = $s3Client->getObject([
                'Bucket' => S3_bucket,
                //'Key' => $file,
                'Key' => $keyf,
                'Range' => 'bytes=0-99'
            ]);
            $retornar = true;
            $_SESSION["generales"]["mensajerror"] = '';
        } catch (S3Exception $e) {
            $retornar = false;
        }

        // 
        unset($s3Client);
        unset($sdk);
        return $retornar;
    }

    /*
     * Recupera un objeto desde Amazon S3 RUES
     */

    public static function recuperarS3Version4RUES($file) {
        if (substr(PHP_VERSION, 0, 1) == '7') {
            require_once ($_SESSION["generales"]["pathabsoluto"] . '/components74/vendor/autoload.php');
        } else {
            if (substr(PHP_VERSION, 0, 1) == '8') {
                require_once ($_SESSION["generales"]["pathabsoluto"] . '/components/vendor/autoload.php');
            } else {
                require_once ($_SESSION["generales"]["pathabsoluto"] . '/components/vendor/autoload.php');
            }
        }

        $retornar = '';
        $_SESSION["generales"]["mensajerror"] = 'Imagen no pudo ser recuperada de S3';

        //
        $sharedConfig = [
            'region' => 'us-east-1',
            'version' => 'latest',
            'credentials' => [
                'key' => RUES_S3_awsAccessKey,
                'secret' => RUES_S3_awsSecretKey,
            ],
        ];

        $sdk = new Aws\Sdk($sharedConfig);
        $s3Client = $sdk->createS3();


        $pos = strrpos($file, "/");

        $name = $_SESSION["generales"]["pathabsoluto"] . '/tmp/' . $_SESSION["generales"]["codigoempresa"] . '-' . substr($file, $pos + 1, 34) . '.' . \funcionesGenerales::encontrarExtension($file);

        try {
            // Get the object
            $result = $s3Client->getObject([
                'Bucket' => RUES_S3_bucket,
                'Key' => $file,
                'SaveAs' => $name
            ]);
            $retornar = $name;
            $_SESSION["generales"]["mensajerror"] = '';
        } catch (ErrorException $e) {
            $_SESSION["generales"]["mensajerror"] = $e->getMessage();
            $retornar = false;
        } catch (Exception $e) {
            $_SESSION["generales"]["mensajerror"] = $e->getMessage();
            $retornar = false;
        } catch (S3Exception $e) {
            $retornar = false;
        }

        // 
        unset($s3Client);
        unset($sdk);
        return $retornar;
    }

    /*
     * Verifica que un objeto exista en Amazon S3 RUES
     */

    public static function existenciaS3Version4RUES($file) {
        if (substr(PHP_VERSION, 0, 1) == '7') {
            require_once ($_SESSION["generales"]["pathabsoluto"] . '/components74/vendor/autoload.php');
        } else {
            if (substr(PHP_VERSION, 0, 1) == '8') {
                require_once ($_SESSION["generales"]["pathabsoluto"] . '/components/vendor/autoload.php');
            } else {
                require_once ($_SESSION["generales"]["pathabsoluto"] . '/components/vendor/autoload.php');
            }
        }

        //
        $retornar = false;
        $_SESSION["generales"]["mensajerror"] = 'Imagen no existe en S3';

        //
        $sharedConfig = [
            'region' => 'us-east-1',
            'version' => 'latest',
            'credentials' => [
                'key' => RUES_S3_awsAccessKey,
                'secret' => RUES_S3_awsSecretKey,
            ],
        ];

        $sdk = new Aws\Sdk($sharedConfig);
        $s3Client = $sdk->createS3();

        try {
            // Get the object
            $result = $s3Client->getObject([
                'Bucket' => RUES_S3_bucket,
                'Key' => $file,
                'Range' => 'bytes=0-99'
            ]);
            $retornar = true;
            $_SESSION["generales"]["mensajerror"] = '';
        } catch (ErrorException $e) {
            $_SESSION["generales"]["mensajerror"] = $e->getMessage();
            $retornar = false;
        } catch (Exception $e) {
            $_SESSION["generales"]["mensajerror"] = $e->getMessage();
            $retornar = false;
        } catch (S3Exception $e) {
            $retornar = false;
        }

        // 
        unset($s3Client);
        unset($sdk);
        return $retornar;
    }

    /*
     * Genera URL prefirmada del repositorio S3 
     * 
     * Previamente se valida si el archivo se encuentra localmente de lo contrario realiza la petición al S3 y obtiene URL firmada
     */

    public static function obtenerUrlRepositorioS3($file) {

        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
        $nameLog = 'obtenerUrlRepositorioS3_' . date ("Ymd");
        
        //
        // echo $file;
        // exit ();
        $_SESSION["generales"]["mensajerror"] = 'Imagen no pudo ser recuperada de S3';
        $file = str_replace(array(" ", chr(13) . chr(10), chr(13), chr(10)), "", $file);
        $retornar = '';
        $urlLocal = TIPO_HTTP . HTTP_HOST . "/" . PATH_RELATIVO_IMAGES . "/" . $_SESSION["generales"]["codigoempresa"] . "/" . $file;
        if (file_exists(PATH_ABSOLUTO_IMAGES . "/" . $_SESSION["generales"]["codigoempresa"] . "/" . $file)) {
            if (filesize(PATH_ABSOLUTO_IMAGES . "/" . $_SESSION["generales"]["codigoempresa"] . "/" . $file) == 0) {
                return '';
            } else {
                return $urlLocal;
            }
        }

        //Si el archivo no existe obtiene URL - S3 firmada
        if (!defined('S3_awsAccessKey') || S3_awsAccessKey == '') {
            return '';
        }
        if (!defined('S3_awsSecretKey') || S3_awsSecretKey == '') {
            return '';
        }

        //
        $file = $_SESSION["generales"]["codigoempresa"] . "/" . $file;

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

        try {

            //Creating a presigned URL
            $cmd = $s3Client->getCommand('GetObject', [
                'Bucket' => 'repositoriosii',
                'Key' => $file,
                'Range' => 'bytes=0-99'
            ]);

            $request = $s3Client->createPresignedRequest($cmd, '+180 minutes');
            $retornar = (string) $request->getUri();
            \logApi::general2($nameLog,$file,'Url aws: ' . $retornar);
            $_SESSION["generales"]["mensajerror"] = '';
        } catch (ErrorException $e) {
            $_SESSION["generales"]["mensajerror"] = $e->getMessage();
            $retornar = '';
        } catch (\Exception $e) {
            $_SESSION["generales"]["mensajerror"] = $e->getMessage();
            $retornar = '';
        } catch (S3Exception $e) {
            $retornar = '';
        }

        // 
        unset($s3Client);
        unset($sdk);

        return $retornar;
    }

}

?>
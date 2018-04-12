<?php


define('DEBUG_LOCAL',false);
define('MONITOR_CHECK_JID','0-0-0-0');


mt_srand();

define('clientverification_MODULE_NAME','Client Verification');
define('clientverification_MODULE_VERSION','1.0');
define('clientverification_MODULE_VENDOR', 'Modules4WHMCS [https://github.com/Modules4WHMCS]');
define('clientverification_MODULE_DESCRIPTION','Client Verification for WHMCS');


set_error_handler('clientverification_exception_error_handler');
register_shutdown_function('clientverification_fatal_handler');
spl_autoload_register(array('clientverificationAutoloader', 'autoload'));

Logger::configure(__DIR__.'/log4php_config.xml');




/**
 * exception_error_handler
 */
function clientverification_exception_error_handler($errno, $errstr, $errfile, $errline )
{
    $errMsg = $errno.' '. $errstr.' '. $errfile.' '. $errline;
    Logger::getLogger("")->error($errMsg);
}


class clientverificationAutoloader
{
	/**
	 * Loads a class.
	 * @param string $className The name of the class to load.
	*/
	public static function autoload($className) 
	{
		if(!self::autoSearchClass($className,__DIR__.'/libs')){
		}
	}
	
	private static function autoSearchClass($className,$searchdir)
	{
		$dir=dir($searchdir);
		while($resName=$dir->read()){
			if($resName != '.' && $resName != '..' && is_dir($searchdir.'/'.$resName) === true) {
				if(self::autoSearchClass($className,$searchdir.'/'.$resName)){
					return true;
				}
			}
			else if(strcmp($resName,$className.'.php') === 0 ||
                    strcmp($resName,$className.'.class.php') === 0 ||
					strcmp($resName,$className.'.interface.php')===0){
				include_once $searchdir.'/'.$resName;
				return true;
			}
		}
		return false;
	}
}

function clientverification_fatal_handler()
{
    $errfile = "unknown file";
    $errstr  = "shutdown";
    $errno   = E_CORE_ERROR;
    $errline = 0;

    $error = error_get_last();

    if( $error !== NULL) {
        $errno   = $error["type"];
        $errfile = $error["file"];
        $errline = $error["line"];
        $errstr  = $error["message"];
    }
    Logger::getLogger("")->error(clientverification_format_error( $errno, $errstr, $errfile, $errline ));
}

/**
 * 
 * @param unknown $errno
 * @param unknown $errstr
 * @param unknown $errfile
 * @param unknown $errline
 * @return string
 */
function clientverification_format_error( $errno, $errstr, $errfile, $errline )
{
    $trace = print_r( debug_backtrace( false ), true );
    $content = "Error: $errstr\r\n";
    $content .= "Errno: $errno\r\n";
    $content .= "File: $errfile\r\n";
    $content .= "Line: $errline\r\n";
    $content .= "Trace:\r\n$trace\r\n\r\n";

    return $content;
}






/**
 *
 * @param unknown $str
 * @return string
 */
function clientverification_ajaxError($str)
{
    return json_encode(array('status'=>'error','msg'=>$str));
}

/**
 *
 * @param string $response
 * @param string $msg
 * @param string $msgType
 * @return string
 */
function clientverification_ajaxSuccess($response=null,$msg=null,$msgType=null)
{
    $retArr = array('status'=>'ok');
    if ($response) {
        $retArr['response'] = $response;
    }
    if ($msg) {
        $retArr['msg'] = $msg;
    }
    return json_encode($retArr);
}

/**
 *
 * @param unknown $json
 */
function clientverification_ajaxFinish($json)
{
    if ($json != -1) {
        echo clientverification_ajaxSuccess($json);
    }
    header("Connection: close", true);
    ob_end_flush();
    flush();
    if (function_exists('fastcgi_finish_request')) {
        fastcgi_finish_request();
    }
    die();
}


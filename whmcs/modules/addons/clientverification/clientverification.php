<?php


set_include_path(get_include_path() . PATH_SEPARATOR . __DIR__);
require_once __DIR__.'/common.php';





add_hook("AdminAreaHeadOutput",1,"clientverification_AdminAreaHeadOutput");
/**
 * Хук функция для добавления кастомных html хедеров на страницы админ интерфейса
 * @return String html код для добавления в head страницы
 */
function clientverification_AdminAreaHeadOutput()
{
    $smarty = new ClientVerificationSmarty();
    $smarty->assign('adminhead',true);
    $retData = $smarty->fetch();
    return $retData;
}


/**
 * Функция получения информации о конфигурации модуля для WHMCS
 * @return multitype:string
 */
function clientverification_config()
{
    $configarray = array(
        "name" => clientverification_MODULE_NAME,
        "description" => clientverification_MODULE_DESCRIPTION,
        "version" => clientverification_MODULE_VERSION,
        "author" => clientverification_MODULE_VENDOR,
        "language" => "english"//,

    );
    return $configarray;
}

/**
 * функция активации модуля WHMCS
 * @return multitype:string NULL |multitype:string
 */
function clientverification_activate()
{
    try{
        $ipbl = new ClientVerification();
        $ipbl->activate();
    }
    catch (Exception $e){
        Logger::getLogger('')->error("Exception",$e);
        return array('status'=>'error','description'=> $e->getMessage());
    }

    return array('status'=>'success','description'=>'Installation is successful.');
}

/**
 * Деактивация модуля WHMCS
 * @return multitype:string NULL |multitype:string
 */
function clientverification_deactivate()
{
    try{
        $ipbl = new ClientVerification();
        $ipbl->deactivate();
    }
    catch (Exception $e){
        Logger::getLogger('')->error("Exception",$e);
        return array('status'=>'error','description'=> $e->getMessage());
    }

    return array('status'=>'success','description'=>'Uninstallation is successful.');
}

/**
 * Получение данных от модуля для отображения функционала WHMCS модуля
 * @param Array $vars
 */
function clientverification_output($vars)
{
    global $aInt,$whmcs;

    Logger::getLogger("debug")->debug($_REQUEST);
    $cv = new ClientVerification();


    ///////////////////////////////////////////////////////////////////////
    // AJAX request processing
    if($_REQUEST['f']){
        try{
            $aInt->adminTemplate = '';
            $json = null;

            $callFunc = 'ajax'.ucfirst($_REQUEST['f']);
            $result=$cv->mysqlQuery('SELECT * FROM mod_clientverification_options');
            while($row=mysqli_fetch_assoc($result)) {
                $cv_options[$row['opt_key']] = $row['opt_value'];
            }

            $vars['cv_options'] = $cv_options;

            if(method_exists($cv,$callFunc)) {
                $json = $cv->$callFunc($vars);
            }
        }
        catch(Exception $e){
            Logger::getLogger(__FUNCTION__)->error("Exception",$e);
            echo clientverification_ajaxError($e->getMessage());
            header("Connection: close", true);

            ob_end_flush();
            flush();
            if(function_exists('fastcgi_finish_request')) fastcgi_finish_request();

            exit();
        }

        if($json !== -1){
            Logger::getLogger(__FUNCTION__)->debug($json);
            $response = clientverification_ajaxSuccess($json);
        }

        header("Connection: close");
        ignore_user_abort();
        echo $response;
        $size = ob_get_length();
        header("Content-Length: $size");
        http_response_code(200);
        ob_end_flush();
        flush();

        exit();
    }

    try{
        $smarty = new ClientVerificationSmarty();
        $smarty->assign('adminbody',true);
        $smarty->display();
    }
    catch (Exception $e){
        Logger::getLogger(__FUNCTION__)->error("Exception",$e);
    }

}

function clientverification_mycustomfunction($vars) {

}



function clientverification_clientarea($vars) {

    if($_REQUEST['f']){
        try{
            $json = null;
            $cv = new ClientVerification();

            $result=$cv->mysqlQuery('SELECT * FROM mod_clientverification_options');
            while($row=mysqli_fetch_assoc($result)) {
                $cv_options[$row['opt_key']] = $row['opt_value'];
            }


            $command = 'GetClientsDetails';
            $postData = array(
                'clientid' => $_SESSION['uid'],
                'stats' => false,
            );
            $vars = localAPI($command, $postData,$cv_options['adminusername']);// Optional for WHMCS 7.2 and later
            $vars['cv_options']=$cv_options;


            $callFunc = 'ajax'.ucfirst($_REQUEST['f']);

            if(method_exists($cv,$callFunc)) {
                $json = $cv->$callFunc($vars);
            }
        }
        catch(Exception $e){
            Logger::getLogger(__FUNCTION__)->error("Exception",$e);
            echo clientverification_ajaxError($e->getMessage());
            header("Connection: close", true);

            ob_end_flush();
            flush();
            if(function_exists('fastcgi_finish_request')) fastcgi_finish_request();

            exit();
        }

        if($json !== -1){
            Logger::getLogger(__FUNCTION__)->debug($json);
            $response = clientverification_ajaxSuccess($json);
        }

        header("Connection: close");
        ignore_user_abort();
        echo $response;
        $size = ob_get_length();
        header("Content-Length: $size");
        http_response_code(200);
        ob_end_flush();
        flush();

        exit();
    }


    return array(
        'pagetitle' => 'Account Verification',
        'breadcrumb' => array('index.php?m=demo'=>'Demo Addon'),
        'templatefile' => 'client',
        'requirelogin' => true, # accepts true/false
        'forcessl' => false, # accepts true/false
        'vars' => array(
            'testvar' => 'demo',
            'anothervar' => 'value',
            'sample' => 'test',
        ),
    );

}





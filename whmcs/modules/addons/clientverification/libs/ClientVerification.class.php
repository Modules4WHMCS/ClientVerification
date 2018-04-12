<?php
/**
 * Created by IntelliJ IDEA.
 * User: roman-int3
 * Date: 3/7/18
 * Time: 4:28 PM
 */



class ClientVerification
{

	public $db_host,$db_username,$db_password,$db_name,$db_link;

	public function __construct()
    {

        $this->reconnectToDB();
    }

	public function __destruct()
    {
        if($this->db_link){
            mysqli_close($this->db_link);
        }
    }


	private function reconnectToDB()
    {
        if($this->db_link){
            mysqli_close($this->db_link);
        }
        if(DEBUG_LOCAL){
            include '/home/roman/www/whmcs6/configuration.php';
        }
        else{
            include __DIR__ . '/../../../../configuration.php';
        }

        $this->db_host = $db_host;
        $this->db_username = $db_username;
        $this->db_password = $db_password;
        $this->db_name = $db_name;
        $this->db_link=mysqli_connect($this->db_host,$this->db_username,$this->db_password,$this->db_name);
    }

    public function activate()
    {
        mysqli_multi_query($this->db_link, file_get_contents(__DIR__.'/../install/mod_clientverification_db.sql'));
        $this->reconnectToDB();


        $this->mysqlQuery('INSERT INTO mod_clientverification_options (opt_key, opt_value) VALUES(%s,%s)',
                                       'adminusername','');
        $this->mysqlQuery('INSERT INTO mod_clientverification_options (opt_key, opt_value) VALUES(%s,%s)',
            'docsextensions','jpg,jpeg,png,pdf');
        $this->mysqlQuery('INSERT INTO mod_clientverification_options (opt_key, opt_value) VALUES(%s,%s)',
            'doctypes','Driver License,Passport,Utility Bill');
        $this->mysqlQuery('INSERT INTO mod_clientverification_options (opt_key, opt_value) VALUES(%s,%s)',
            'maxuploadfilesize','50');
        $this->mysqlQuery('INSERT INTO mod_clientverification_options (opt_key, opt_value) VALUES(%s,%s)',
            'usersdocroot','/tmp/clientverification');


    }

    public function deactivate()
    {
        if (!DEBUG_LOCAL) {
            $this->mysqlQuery('DROP TABLE mod_clientverification_user');
            $this->mysqlQuery('DROP TABLE mod_clientverification_options');
            $this->mysqlQuery('DROP TABLE mod_clientverification_user_docs');
            $this->mysqlQuery('DROP TABLE mod_clientverification_engw');

        }
    }



    public function mysqlQuery($query)
    {
        $argcount = func_num_args();
        Logger::getLogger("debug")->debug('$query = '.$query );
        Logger::getLogger("debug")->debug('$argcount = '.$argcount );

        if($argcount > 1){
            $args = func_get_args();
            Logger::getLogger("debug")->debug(print_r($args,true));
            unset($args[0]);
            for ($i = 1; $i <= $argcount - 1; $i++) {
                $args[$i] = $args[$i] === 'NULL'?'NULL':$this->quote_smart($args[$i]);
            }
            $query = vsprintf($query,$args);
        }
        Logger::getLogger("debug")->debug($args);
        Logger::getLogger("debug")->debug($query);
        $result=mysqli_query($this->db_link,$query);
        Logger::getLogger("debug")->debug($result);
        $err=mysqli_errno($this->db_link);
        Logger::getLogger("debug")->debug('mysqli_error='.
            mysqli_error($this->db_link)."\n".'mysqli_errno='.
            mysqli_errno($this->db_link));


        if($err === 2006 || $err === 2013){
            //RECONNECT TO THE MYSQL DB
            $this->db_link=mysqli_connect($this->db_host,$this->db_username,$this->db_password,$this->db_name);
            return $this->mysqlQuery($query);
        }

        return $result;
    }


    private function quote_smart($value)
    {
        // Stripslashes
        if (get_magic_quotes_gpc()){
            $value = stripslashes($value);
        }
        // Quote if not a number or a numeric string
        if (!is_numeric($value)){
            $value = "'" . mysqli_real_escape_string($this->db_link,$value) . "'";
        }
        return $value;
    }





    /**
     *
     * @param unknown $countQuery
     * @param unknown $selectQuery
     * @param string $rowcallback
     * @param string $subtables
     * @return multitype:Ambigous <unknown, number> number unknown
     */
    private function gridSelectQuery($countQuery,$selectQuery,$rowcallback=NULL,$subtables=null)
    {
        $responce = array();
        $page = $_GET['page']; // get the requested page
        $limit = $_GET['rows']; // get how many rows we want to have into the grid
        $sidx = $_GET['sidx']; // get index row - i.e. user click to sort
        $sord = $_GET['sord']; // get the direction
        if(!$sidx) $sidx =1;

        //echo $countQuery;
        Logger::getLogger("debug")->debug($countQuery);
        $result2= $this->mysqlQuery($countQuery);
        $count = mysqli_fetch_row($result2);
        $count = $count[0];
        if( $count >0 ){
            $total_pages = ceil($count/$limit);
        }
        else{
            $total_pages = 0;
        }

        if ($page > $total_pages) {
            $page = $total_pages;
        }
        $start = $limit*$page - $limit;

        if($start < 0){$start = 0;}

        if($_REQUEST['_search']){
            $filters = json_decode(html_entity_decode($_REQUEST['filters']));
            foreach($filters->rules as $rule){
                if($rule->field == 'ip' || $rule->field == 'network' || $rule->field == 'ns1' || $rule->field == 'ns2' || $rule->field == 'gateway'){
                    $ipsOctets = explode('.', $rule->data);
                    foreach($ipsOctets as $octet) {
                        if($octet) {
                            $octetscount++;
                            $searchhex .= sprintf('%02x',$octet);
                        }
                    }
                    $whereQuery .= ' AND ';
                    $whereQuery .= 'left('.$rule->field.','.$octetscount.")=UNHEX('$searchhex')";
                }
                else {
                    $whereQuery .= ' AND '.$rule->field.' LIKE '.quote_smart($rule->data.'%').' ';
                }
            }
        }

        $selectQuery = sprintf($selectQuery,$whereQuery);
        if ($sidx != '' && $sord != '') {
            $selectQuery .= " ORDER BY $sidx $sord";
        }
        if ($limit) {
            $selectQuery .= " LIMIT $start , $limit";
        }

        //echo $selectQuery;
        Logger::getLogger("debug")->debug($selectQuery);
        $result = $this->mysqlQuery($selectQuery);
        while (($row = mysqli_fetch_assoc($result))){
            if ($rowcallback) {
                $row = call_user_func($rowcallback, $row);

            }
            $responce['rows'][]=$row;
        }

        $responce['page'] = $page;
        $responce['total'] = (string)$total_pages;
        $responce['records'] = $count;

        return $responce;
    }


    public function ajaxGetActiveGateways(){
        $result=$this->mysqlQuery('SELECT gw.gateway,IF(engw.gw=gw.gateway, "true", "false") AS verified FROM `tblpaymentgateways` AS gw
                                            LEFT JOIN mod_clientverification_engw AS engw 
                                                      ON engw.gw LIKE gw.gateway
                                          WHERE gw.setting="visible" AND gw.value="on"');
        while($row=mysqli_fetch_assoc($result)){
            $retArr['rows'][] = $row;
        }
        $gridArr['records'] = count($retArr);
        return $retArr;
    }

    public function ajaxSaveGatewayOpt(){
        if($_REQUEST['value'] === 'false' ){
            $this->mysqlQuery('DELETE FROM mod_clientverification_engw 
                                            WHERE gw=%s',$_REQUEST['gateway']);
        }
        else {
            $this->mysqlQuery('INSERT INTO mod_clientverification_engw
                                            (gw) VALUES(%s)',$_REQUEST['gateway']);

        }
    }

    public function ajaxGetUsers(){
        $result=$this->mysqlQuery("SELECT 
          CONCAT ('<a target=\"_blank\" href=\"clientssummary.php?userid=',client.id,'\">',client.firstname,' ',
            client.lastname,'</a>') AS user,user.is_verified AS verified,client.uuid AS uuid
          FROM tblclients AS client
          LEFT JOIN mod_clientverification_user AS user ON user.uuid=client.uuid");

        while($row=mysqli_fetch_assoc($result)){
            $retArr['rows'][] = $row;
        }
        $gridArr['records'] = count($retArr);
        return $retArr;


    }

    public function ajaxSaveUser(){
        $result=$this->mysqlQuery('UPDATE mod_clientverification_user 
                                            SET is_verified=%s WHERE uuid=%s',
            $_REQUEST['value'],$_REQUEST['uuid']);

        if($result) {
            $result = $this->mysqlQuery('INSERT INTO mod_clientverification_user 
                                            (uuid,is_verified) VALUES(%s,%s)',
                     $_REQUEST['uuid'],$_REQUEST['value']);
        }
    }







    /**
     *
     * @param unknown $str
     * @return string
     */
    private function ajaxError($str)
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
    private function ajaxSuccess($response=null,$msg=null,$msgType=null)
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
    private function ajaxFinish($json)
    {
        if ($json != -1) {
            echo ajaxSuccess($json);
        }
        header("Connection: close", true);
        ob_end_flush();
        flush();
        if (function_exists('fastcgi_finish_request')) {
            fastcgi_finish_request();
        }
        die();
    }



    public function ajaxGetSettings()
    {
        $result=$this->mysqlQuery('SELECT * FROM mod_clientverification_options');
        while($row=mysqli_fetch_assoc($result)){
            $retArr[$row['opt_key']]=$row['opt_value'];
        }

        return $retArr;
    }

    public function ajaxSaveSettings()
    {
        $result=$this->mysqlQuery('SELECT opt_key FROM mod_clientverification_options');
        while($row=mysqli_fetch_assoc($result)){
            $this->mysqlQuery('UPDATE mod_clientverification_options SET opt_value=%s WHERE opt_key=%s',
                                        $_REQUEST[$row['opt_key']],$row['opt_key']);
        }
    }


    public function ajaxGetDocTypes(){
        $result=$this->mysqlQuery('SELECT opt_value FROM mod_clientverification_options WHERE opt_key="doctypes"');
        $row=mysqli_fetch_assoc($result);
        return explode(',',$row['opt_value']);
    }

    public function ajaxGetUserDoc($vars){
        $result=$this->mysqlQuery('SELECT * FROM mod_clientverification_user_docs WHERE uuid=%s',$vars['uuid']);
        while($row=mysqli_fetch_assoc($result)){
            $path=$vars['cv_options']['usersdocroot']. DIRECTORY_SEPARATOR .$row['uuid']. DIRECTORY_SEPARATOR .$row['file_name'];
            Logger::getLogger("debug")->debug($path);
            $type = pathinfo($path, PATHINFO_EXTENSION);
            $data = file_get_contents($path);
            $img = 'data:image/' . $type . ';base64,' . base64_encode($data);

            $gridArr['rows'][] = array('fid'=>$row['file_hash'],'filename'=>$row['file_name'],'status'=>$row['status'],
                'doc_type' => $row['doc_type'],'img' => $img
                );
        }

        $gridArr['records'] = count($gridArr);
        return $gridArr;
    }


    public function ajaxGetUserDocs($vars){
        $result=$this->mysqlQuery('SELECT * FROM mod_clientverification_user_docs WHERE uuid=%s',$_REQUEST['uuid']);
        while($row=mysqli_fetch_assoc($result)){
            $path=$vars['cv_options']['usersdocroot']. DIRECTORY_SEPARATOR .$row['uuid']. DIRECTORY_SEPARATOR .$row['file_name'];
            Logger::getLogger("debug")->debug($path);
            $type = pathinfo($path, PATHINFO_EXTENSION);
            $data = file_get_contents($path);
            $img = 'data:image/' . $type . ';base64,' . base64_encode($data);

            $gridArr[] = array('fid'=>$row['file_hash'],'filename'=>$row['file_name'],'status'=>$row['status'],
                'doc_type' => $row['doc_type'],'img' => $img
            );
        }

        return $gridArr;
    }

    public function ajaxSetUserDocStatus($vars){
        if($_REQUEST['status'] === 'Delete'){
            $result=$this->mysqlQuery('SELECT uuid,file_name FROM mod_clientverification_user_docs WHERE file_hash=%s',
                                                        $_REQUEST['fid']);
            $row=mysqli_fetch_assoc($result);
            if(!$row){
                throw new Exception("ERROR");
            }
            $path=$vars['cv_options']['usersdocroot']. DIRECTORY_SEPARATOR .$row['uuid']. DIRECTORY_SEPARATOR .$row['file_name'];
            unlink($path);

            $this->mysqlQuery('DELETE FROM mod_clientverification_user_docs WHERE file_hash=%s',
                $_REQUEST['fid']);
            return array('status'=>'deleted');
        }
        else if($_REQUEST['status'] === 'Accept'){
            $status='accepted';

        }
        else if($_REQUEST['status'] === 'Reject'){
            $status='rejected';
        }

        $this->mysqlQuery('UPDATE mod_clientverification_user_docs SET status=%s WHERE file_hash=%s',
            $status,$_REQUEST['fid']);
        return array('status'=>$status);
    }


    public function ajaxDelUserDoc($vars){

            $result=$this->mysqlQuery('SELECT uuid,file_name FROM mod_clientverification_user_docs WHERE file_hash=%s',
                $_REQUEST['fid']);
            $row=mysqli_fetch_assoc($result);
            if(!$row){
                throw new Exception("ERROR");
            }
            $path=$vars['cv_options']['usersdocroot']. DIRECTORY_SEPARATOR .$row['uuid']. DIRECTORY_SEPARATOR .$row['file_name'];
            unlink($path);

            $this->mysqlQuery('DELETE FROM mod_clientverification_user_docs WHERE file_hash=%s',
                $_REQUEST['fid']);
            return array('status'=>'deleted');

    }

    public function ajaxIsAccountVerified($vars){

        $result=$this->mysqlQuery('SELECT is_verified FROM mod_clientverification_user WHERE uuid=%s',
                                        $vars['uuid']);
        $row=mysqli_fetch_assoc($result);
        return array('is_verified'=>$row['is_verified']);


    }




    /**
     * @param $vars
     * @throws Exception
     */
    public function ajaxUploadUserDocToStorage($vars)
    {
        // HTTP headers for no cache etc
        header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
        header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
        header("Cache-Control: no-store, no-cache, must-revalidate");
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");

        // Get parameters
        $chunk      = isset($_REQUEST["chunk"]) ? (int)$_REQUEST["chunk"] : 0;
        $chunks     = isset($_REQUEST["chunks"]) ? (int)$_REQUEST["chunks"] : 0;
        $fileName   = isset($_REQUEST["name"]) ? $_REQUEST["name"] : '';

        if($chunk === 0){
            $ext = pathinfo($fileName, PATHINFO_EXTENSION);

            //$result=$this->mysqlQuery('SELECT opt_value FROM mod_clientverification_options WHERE opt_key="docsextensions"');
            //$row=mysqli_fetch_assoc($result);
            //$docsextensions=$row['opt_value'];

            if(strpos($vars['cv_options']['docsextensions'],$ext) === false){
                throw new \Exception('Wrong file extension');
            }


            //$result=$this->mysqlQuery('SELECT opt_value FROM mod_clientverification_options WHERE opt_key="usersdocroot"');
            //$row=mysqli_fetch_assoc($result);
            //$usersdocroot=$row['opt_value'];
            //$result=$this->mysqlQuery('SELECT opt_value FROM mod_clientverification_options WHERE opt_key="maxuploadfilesize"');
            //$row=mysqli_fetch_assoc($result);
            //$maxuploadfilesize=$row['opt_value'];
            if($_REQUEST['fileSize'] > $vars['cv_options']['maxuploadfilesize']*1024*1024){
                throw new \Exception('File too big');
            }
        }

        Logger::getLogger("debug")->debug($vars);
        $targetDir = $vars['cv_options']['usersdocroot'] . DIRECTORY_SEPARATOR . $vars['uuid'] ;//WhMcsHvM::getModuleOption('uutmp');

        $cleanupTargetDir = true; // Remove old files
        $maxFileAge = 24 * 60 * 3600; // Temp file age in seconds

        // execution time in seconds
        @set_time_limit(1 * 60 * 60);

        // Clean the fileName for security reasons
        $fileName = preg_replace('/[^\w\._]+/', '_', $fileName); //TODO: check regex
        if(!$fileName){
            $fileName=$_REQUEST['name'];
        }

        Logger::getLogger("debug")->debug('fileName='.$fileName);

        // Make sure the fileName is unique but only if chunking is disabled
        if ($chunks < 2 && file_exists($targetDir . DIRECTORY_SEPARATOR . $fileName)) {
            $ext = strrpos($fileName, '.');
            $fileName_a = substr($fileName, 0, $ext);
            $fileName_b = substr($fileName, $ext);

            $count = 1;
            while (file_exists($targetDir . DIRECTORY_SEPARATOR . $fileName_a . '_' . $count . $fileName_b)) {
                $count++;
            }

            $fileName = $fileName_a . '_' . $count . $fileName_b;
        }

        $filePath = $targetDir . DIRECTORY_SEPARATOR .  $fileName;

        // Create target dir
        if (!file_exists($targetDir) && !mkdir($targetDir,0755,true) && !is_dir($targetDir)) {
            throw new \Exception(sprintf('Directory was not created'));
        }

        Logger::getLogger("debug")->debug('$filePath='.$filePath);

        // Remove old temp files
        if ($cleanupTargetDir) {
            if (!is_dir($targetDir) || !($dir = opendir($targetDir))) {
                throw new \Exception("Failed to open temp directory.");
            } else {
                while (($file = readdir($dir)) !== false) {
                    $tmpfilePath = $targetDir . DIRECTORY_SEPARATOR . $file;

                    // Remove temp file if it is older than the max age and is not the current file
                    if (preg_match('/\.part$/', $file) && (filemtime($tmpfilePath) < time() - $maxFileAge) && ($tmpfilePath != "{$filePath}.part")) {
                        @unlink($tmpfilePath);
                    }
                }
                closedir($dir);
            }
        }

        // Look for the content type header
        if (isset($_SERVER["HTTP_CONTENT_TYPE"])){
            $contentType = $_SERVER["HTTP_CONTENT_TYPE"];
        }

        if (isset($_SERVER["CONTENT_TYPE"])){
            $contentType = $_SERVER["CONTENT_TYPE"];
        }

        // Handle non multipart uploads older WebKit versions didn't support multipart in HTML5
        if (strpos($contentType, "multipart") !== false) {
            if (!isset($_FILES['file']['tmp_name']) || !is_uploaded_file($_FILES['file']['tmp_name'])) {
                throw new \Exception("Failed to move uploaded file.");
            } else {
                // Open temp file
                $out = @fopen("{$filePath}.part", $chunk == 0 ? "wb" : "ab");
                if (!$out) {
                    throw new \Exception("Failed to open output stream.");
                } else {
                    // Read binary input stream and append it to temp file
                    $in = @fopen($_FILES['file']['tmp_name'], "rb");

                    if (!$in) {
                        throw new \Exception("Failed to open input stream.");
                    } else {
                        while ($buff = fread($in, 4096))
                            fwrite($out, $buff);
                    }
                    @fclose($in);
                    @fclose($out);
                    @unlink($_FILES['file']['tmp_name']);
                }
            }
        } else {
            // Open temp file
            $out = @fopen("{$filePath}.part", $chunk === 0 ? 'wb' : 'ab');
            if (!$out) {
                throw new \Exception("Failed to open output stream.");
            } else {
                // Read binary input stream and append it to temp file
                $in = @fopen('php://input', 'rb');

                if (!$in) {
                    throw new \Exception("Failed to open input stream.");
                } else {
                    while ($buff = fread($in, 4096)) {
                        fwrite($out, $buff);
                    }
                }
                @fclose($in);
                @fclose($out);
            }
        }

        // Check if file has been uploaded
        if (!$chunks || $chunk === $chunks - 1) {
            // Strip the temp .part suffix off
            rename("{$filePath}.part", $filePath);

            try{
                $origFileName=preg_replace('/[^\w\._]+/', '_',$_REQUEST['origFileName']); //TODO: need check regex
                if(!$origFileName){
                    $origFileName=$_REQUEST['origFileName'];
                }
                //AjaxApi::uploadFileToHvStore($params,$filePath,$origFileName);

                $md5 = md5($filePath);
                $this->mysqlQuery('INSERT INTO mod_clientverification_user_docs (file_hash,file_name,doc_type,uuid) 
                                                VALUES(%s,%s,%s,%s)',
                                                    $md5,
                                                    $origFileName,
                                                    $_REQUEST['docType'],
                                                    $vars['uuid']
                    );



            }
            catch (Exception $e){
                @unlink($filePath);
                throw new $e;
            }
        }
    }







}
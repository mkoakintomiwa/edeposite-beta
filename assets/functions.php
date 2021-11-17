<?php
@session_start();
$document_root = __DIR__;while(true){if (file_exists($document_root."/settings.json")){break;}else{$document_root=dirname($document_root);}}
include_once $document_root."/settings.php";
include_once "db.php";
include_once "variables.php";
include_once "phpmailer.php";
include_once "gmail/gmail.php";


/**
 * Potential information about user
 *
 * @param Int $uid
 * @return User
 */
function user($public_address=null){
    global $bin;
    if ($public_address===null && !isset($_SESSION["public_address"])){
        return (object)["is_online"=>false];
    }
        
    if (!$public_address) $public_address = $_SESSION["public_address"];
    
    $user = json_decode(http_get_request("https://api.edeposite.info/user",[
        "public_address" => $public_address
    ]),true);
    
    $user["formatted_token"] = $user["token"];
    $user["formatted_bonus"] = $user["bonus"];

    $r = db_fetch("SELECT * FROM crypto_merchants WHERE public_address=?",[$public_address]);

    $user["is_merchant"] = count($r)>0;

    if ($user['is_merchant']){
        $user['merchant'] = $r[0];
        $user['merchant']["formatted_token"] = $user['merchant']["token"];
    }
    
    $user["is_online"] = true;
    
    return (object)$user;
}



function user_by($context,$value){
    $_user_ = db_fetch_one("SELECT * FROM users WHERE `$context`=?",[$value]);
    return user($_user_["uid"]);
}



/**
 * Full associative array describing all members of an office with uids as keys and user information as values
 * 
 * 
 *
 * @param String $office
 * @return array
 */
function office_members($office_id){
    $members = [];

    $_members = db_fetch("SELECT * FROM users WHERE clearance=?",[$office_id]);

    foreach ($_members as $member){
        $members[$member["uid"]] = user($member["uid"]);
    }
    return $members;
}


function office_member_uids($office_id){
    $uids = [];

    $_members = db_fetch("SELECT * FROM users WHERE clearance=?",[$office_id]);

    foreach ($_members as $member){
        $uids[] = $member["uid"];
    }
    return $uids;
}



function minify_html($input) {
    if(trim($input) === "") return $input;
    // Remove extra white-space(s) between HTML attribute(s)
    $input = preg_replace_callback('#<([^\/\s<>!]+)(?:\s+([^<>]*?)\s*|\s*)(\/?)>#s', function($matches) {
        return '<' . $matches[1] . preg_replace('#([^\s=]+)(\=([\'"]?)(.*?)\3)?(\s+|$)#s', ' $1$2', $matches[2]) . $matches[3] . '>';
    }, str_replace("\r", "", $input));
    // Minify inline CSS declaration(s)
    if(strpos($input, ' style=') !== false) {
        $input = preg_replace_callback('#<([^<]+?)\s+style=([\'"])(.*?)\2(?=[\/\s>])#s', function($matches) {
            return '<' . $matches[1] . ' style=' . $matches[2] . minify_css($matches[3]) . $matches[2];
        }, $input);
    }
    if(strpos($input, '</style>') !== false) {
      $input = preg_replace_callback('#<style(.*?)>(.*?)</style>#is', function($matches) {
        return '<style' . $matches[1] .'>'. minify_css($matches[2]) . '</style>';
      }, $input);
    }
    if(strpos($input, '</script>') !== false) {
      $input = preg_replace_callback('#<script(.*?)>(.*?)</script>#is', function($matches) {
        return '<script' . $matches[1] .'>'. minify_js($matches[2]) . '</script>';
      }, $input);
    }
    return preg_replace(
        array(
            // t = text
            // o = tag open
            // c = tag close
            // Keep important white-space(s) after self-closing HTML tag(s)
            '#<(img|input)(>| .*?>)#s',
            // Remove a line break and two or more white-space(s) between tag(s)
            '#(<!--.*?-->)|(>)(?:\n*|\s{2,})(<)|^\s*|\s*$#s',
            '#(<!--.*?-->)|(?<!\>)\s+(<\/.*?>)|(<[^\/]*?>)\s+(?!\<)#s', // t+c || o+t
            '#(<!--.*?-->)|(<[^\/]*?>)\s+(<[^\/]*?>)|(<\/.*?>)\s+(<\/.*?>)#s', // o+o || c+c
            '#(<!--.*?-->)|(<\/.*?>)\s+(\s)(?!\<)|(?<!\>)\s+(\s)(<[^\/]*?\/?>)|(<[^\/]*?\/?>)\s+(\s)(?!\<)#s', // c+t || t+o || o+t -- separated by long white-space(s)
            '#(<!--.*?-->)|(<[^\/]*?>)\s+(<\/.*?>)#s', // empty tag
            '#<(img|input)(>| .*?>)<\/\1>#s', // reset previous fix
            '#(&nbsp;)&nbsp;(?![<\s])#', // clean up ...
            '#(?<=\>)(&nbsp;)(?=\<)#', // --ibid
            // Remove HTML comment(s) except IE comment(s)
            '#\s*<!--(?!\[if\s).*?-->\s*|(?<!\>)\n+(?=\<[^!])#s'
        ),
        array(
            '<$1$2</$1>',
            '$1$2$3',
            '$1$2$3',
            '$1$2$3$4$5',
            '$1$2$3$4$5$6$7',
            '$1$2$3',
            '<$1$2',
            '$1 ',
            '$1',
            ""
        ),
    $input);
}


function minify_css($input) {
    if(trim($input) === "") return $input;
    return preg_replace(
        array(
            // Remove comment(s)
            '#("(?:[^"\\\]++|\\\.)*+"|\'(?:[^\'\\\\]++|\\\.)*+\')|\/\*(?!\!)(?>.*?\*\/)|^\s*|\s*$#s',
            // Remove unused white-space(s)
            '#("(?:[^"\\\]++|\\\.)*+"|\'(?:[^\'\\\\]++|\\\.)*+\'|\/\*(?>.*?\*\/))|\s*+;\s*+(})\s*+|\s*+([*$~^|]?+=|[{};,>~+]|\s*+-(?![0-9\.])|!important\b)\s*+|([[(:])\s++|\s++([])])|\s++(:)\s*+(?!(?>[^{}"\']++|"(?:[^"\\\]++|\\\.)*+"|\'(?:[^\'\\\\]++|\\\.)*+\')*+{)|^\s++|\s++\z|(\s)\s+#si',
            // Replace `0(cm|em|ex|in|mm|pc|pt|px|vh|vw|%)` with `0`
            '#(?<=[\s:])(0)(cm|em|ex|in|mm|pc|pt|px|vh|vw|%)#si',
            // Replace `:0 0 0 0` with `:0`
            '#:(0\s+0|0\s+0\s+0\s+0)(?=[;\}]|\!important)#i',
            // Replace `background-position:0` with `background-position:0 0`
            '#(background-position):0(?=[;\}])#si',
            // Replace `0.6` with `.6`, but only when preceded by `:`, `,`, `-` or a white-space
            '#(?<=[\s:,\-])0+\.(\d+)#s',
            // Minify string value
            '#(\/\*(?>.*?\*\/))|(?<!content\:)([\'"])([a-z_][a-z0-9\-_]*?)\2(?=[\s\{\}\];,])#si',
            '#(\/\*(?>.*?\*\/))|(\burl\()([\'"])([^\s]+?)\3(\))#si',
            // Minify HEX color code
            '#(?<=[\s:,\-]\#)([a-f0-6]+)\1([a-f0-6]+)\2([a-f0-6]+)\3#i',
            // Replace `(border|outline):none` with `(border|outline):0`
            '#(?<=[\{;])(border|outline):none(?=[;\}\!])#',
            // Remove empty selector(s)
            '#(\/\*(?>.*?\*\/))|(^|[\{\}])(?:[^\s\{\}]+)\{\}#s'
        ),
        array(
            '$1',
            '$1$2$3$4$5$6$7',
            '$1',
            ':0',
            '$1:0 0',
            '.$1',
            '$1$3',
            '$1$2$4$5',
            '$1$2$3',
            '$1:0',
            '$1$2'
        ),
    $input);
}
// JavaScript Minifier
function minify_js($input) {
    if(trim($input) === "") return $input;
    return preg_replace(
        array(
            // Remove comment(s)
            '#\s*("(?:[^"\\\]++|\\\.)*+"|\'(?:[^\'\\\\]++|\\\.)*+\')\s*|\s*\/\*(?!\!|@cc_on)(?>[\s\S]*?\*\/)\s*|\s*(?<![\:\=])\/\/.*(?=[\n\r]|$)|^\s*|\s*$#',
            // Remove white-space(s) outside the string and regex
            '#("(?:[^"\\\]++|\\\.)*+"|\'(?:[^\'\\\\]++|\\\.)*+\'|\/\*(?>.*?\*\/)|\/(?!\/)[^\n\r]*?\/(?=[\s.,;]|[gimuy]|$))|\s*([!%&*\(\)\-=+\[\]\{\}|;:,.<>?\/])\s*#s',
            // Remove the last semicolon
            '#;+\}#',
            // Minify object attribute(s) except JSON attribute(s). From `{'foo':'bar'}` to `{foo:'bar'}`
            '#([\{,])([\'])(\d+|[a-z_][a-z0-9_]*)\2(?=\:)#i',
            // --ibid. From `foo['bar']` to `foo.bar`
            '#([a-z0-9_\)\]])\[([\'"])([a-z_][a-z0-9_]*)\2\]#i'
        ),
        array(
            '$1',
            '$1$2',
            '}',
            '$1$3',
            '$1.$3'
        ),
    $input);
}



function html_from_template($html_template,$variables_container=null,$minify=true){
    global $twig;
    $container = $variables_container?$variables_container:$GLOBALS;
    $r = $twig->createTemplate($html_template)->render($container);
    return $minify?minify_html($r):$r;
}


function log_out(){
    global $login_page;
    unset_user();
    header("Location: $login_page");
    die();
}


function unset_user(){
    if (isset($_COOKIE['accounts'])){
        $accounts = json_decode($_COOKIE['accounts'],true);
        
        foreach($accounts as $account_key=>$account_value){
            $accounts[$account_key]['status'] = 'inactive';
        }
        
        set_accounts_cookie($accounts);
    }
    unset($_SESSION['public_address']);
}



function hash_password($password){
    global $password_hash_cost;
    return password_hash($password,PASSWORD_BCRYPT, ["cost" => $password_hash_cost]);
}


function no_user(){
    return !isset($_SESSION["uid"]);
}


function is_user(){
    return isset($_SESSION["uid"]);
}



function files_in_directory($directory){
    $files = [];
    foreach(glob($directory.'/*.*') as $file) {
        $files[] = basename($file);
    }
    return $files;
}


function fs_blob($table_name,$field_name,$insert_id,$file_name=null,$file_extension=null){
    return new fs_blob($table_name,$field_name,$insert_id,$file_name,$file_extension);
}


class fs_blob{
    public $table_name,$field_name,$insert_id,$path,$file_name=null,$file_extension=null;
    
    public function __construct($table_name,$field_name,$insert_id,$file_name=null,$file_extension=null){
        global $blobs_dir;

        $this->table_name = $table_name;
        $this->field_name = $field_name;
        $this->insert_id = $insert_id;
    
        if ($file_extension===null) $file_extension="";
        if ($file_name===null) $file_name="";

        if (strlen(trim($file_extension))>0){
            $file_extension = ".$file_extension";
        }
        if (strlen(trim($file_name))>0){
            $file_name = "-$file_name";
        }
        $this->file_name = $file_name;
        $this->file_extension = $file_extension;
        $this->path = $blobs_dir."/$table_name/$field_name/$insert_id$file_name$file_extension";
    }
    
    
    public static function placeholder(){
        return "fs_blob";
    }

    public function save($content){
        global $blobs_dir;
        $table_name = $this->table_name;
        $field_name = $this->field_name;
        $insert_id = $this->insert_id;
        $file_name = $this->file_name;
        $file_extension = $this->file_extension;

        $dir = $blobs_dir."/$table_name/$field_name";
        if (!file_exists($dir)){
            mkdir($dir,0777,true);
        }
        file_put_contents($dir."/$insert_id$file_name$file_extension",$content);
        return $this;
    }


    public function delete(){
        global $blobs_dir;
        $table_name = $this->table_name;
        $field_name = $this->field_name;
        $insert_id = $this->insert_id;
        $file_name = $this->file_name;
        $file_extension = $this->file_extension;

        $dir = $blobs_dir."/$table_name/$field_name";
        if (!file_exists($dir)){
            mkdir($dir,0777,true);
        }
        try{
            unlink($dir."/$insert_id$file_name$file_extension");
        }catch(Exception $e){}
        return $this;
    }


    public function exists(){
        return file_exists($this->path);
    }


    public function content(){
        return file_exists($this->path)?file_get_contents($this->path):null;
    }

    public function url(){
        global $host,$blobs_rel_dir;
        global $blobs_dir;
        $table_name = $this->table_name;
        $field_name = $this->field_name;
        $insert_id = $this->insert_id;
        $file_name = $this->file_name;
        $file_extension = $this->file_extension;
        
        return "$host$blobs_rel_dir/$table_name/$field_name/$insert_id".urldecode($file_name).$file_extension; 
    }
}



function last_insert_id($table_name,$db_conn=null){
    global $conn;

    $db_conn = $db_conn!=null?$db_conn:$conn;

    $_id = $table_name==="users"?"uid":"id";
    $gs = db_fetch("SELECT $_id FROM `$table_name` ORDER BY $_id DESC",[],$db_conn);
    $id = count($gs)>0?(int)$gs[0][$_id]:0;
    return $id;
}



function file_extension($file_path){
    $ra = explode(".",$file_path);
    $file_extension = trim(array_pop($ra));
    return $file_extension;
}




class Dispute{
    /**
     * Log dispute
     *
     * @param log_dispute_options $log_dispute_options
     * @return String
     */
    public static function log($log_dispute_options){
        global $dispute_ticket_id_length;
        $ticket_id = unique_digits("disputes","ticket_id",$dispute_ticket_id_length);
        row_action([
            "table_name"=>"disputes",
            "columns"=>[
                "ticket_id"=>$ticket_id,
                "subject"=>$log_dispute_options->subject,
                "body"=>$log_dispute_options->body,
                "logged_by"=>$log_dispute_options->logged_by,
                "against"=>$log_dispute_options->against,
                "time"=>time()
            ]
        ])->insert();

        
        self::post($ticket_id,$log_dispute_options->body,$log_dispute_options->logged_by);

        return $ticket_id;
    }


    public static function post($ticket_id,$body,$posted_by){
    
        row_action([
            "table_name"=>"disputes_timeline",
            "columns"=>[
                "ticket_id"=>$ticket_id,
                "body"=>$body,
                "posted_by"=>$posted_by,
                "time"=>time()
            ]
        ])->insert();

        $poster = user($posted_by);
        $poster_office = $poster->office;

        $dispute = self::info($ticket_id);
        
        $office_members = office_members($dispute->against);

        $subject = "$dispute->subject #$ticket_id";
        $fcm_subject = "$poster->name ($poster_office) $subject";
        $sms_subject = "From $poster->name ($poster_office)\n$subject";
        
        $notification = new Notification();

        $notification
        ->set_sender($posted_by)
        ->set_sms_subject($sms_subject)
        ->set_fcm_subject($fcm_subject)
        ->set_email_subject("$dispute->subject #$ticket_id")
        ->set_notification_content($body)
        ->set_notification_context("dispute-$ticket_id");

        $notification_recipients = [];

        foreach ($office_members as $uid=>$_user){
            $notification_recipients[] = $uid;
        }

        $notification_recipients[] = $dispute->logged_by;

        foreach ($notification_recipients as $uid){
            
            $notification->add_recipient($uid);

            //if ($uid!=$posted_by){
            
            //}
        }

        $notification->send();

    }


    public static function info($ticket_id){
        return (object) db_fetch_one("SELECT * FROM disputes WHERE ticket_id=?",[$ticket_id]);
    }


    public static function status($ticket_id){
        return self::info($ticket_id)->status;
    }


    public static function timeline($ticket_id){
        return db_fetch("SELECT * FROM disputes_timeline WHERE ticket_id=? ORDER BY time",[$ticket_id]);
    }

    public static function is_closed($ticket_id){
        return self::status($ticket_id)==="closed";
    }

    public static function is_resolved($ticket_id){
        return self::status($ticket_id)==="resolved";
    }

    public static function is_open($ticket_id){
        return self::status($ticket_id)==="open";
    }


    public static function resolve($ticket_id,$resolved_by){
        global $conn;
        $conn->prepare("UPDATE disputes SET status=?,resolved_by=?,resolved_time=? WHERE ticket_id=?")->execute(["resolved",$resolved_by,time(),$ticket_id]);
    }

    public static function close($ticket_id,$closed_by){
        global $conn;
        $conn->prepare("UPDATE disputes SET status=?,closed_by=?,closed_time=? WHERE ticket_id=?")->execute(["closed",$closed_by,time(),$ticket_id]);
    }

    public static function pending($uid){
        $_user = user($uid);
        return db_fetch("SELECT * FROM disputes WHERE against=? AND status!=?",[$_user->clearance,"closed"]);
    }
}


class ebulksms{

    public $message;

    /**
     * JSON string representation of body of request
     *
     * @var String
     */
    private $data;


    public function __construct(){
        global $organization_name;
        $this->data =  [
            "SMS"=>[
                "auth"=>[
                    "username"=>"reports@groupfarma.com.ng",
                    "apikey"=>"bdd39aeb55b8fe701127588c47adfca17d0b14e8"
                ],
                "message"=>[
                    "sender"=>$organization_name,
                    "flash"=>"0"
                ],
                "recipients"=>[
                    "gsm"=>[]
                ],
                "dndsender"=>1
            ]
        ];
    }


    /**
     * Add recipient of SMS
     *
     * @param String $phone_number Full international phone number with country code - 234 should begin the phone number in the case of Nigeria
     * @param String $message_unique_id Unique ID to each message
     * @return void
     */
    public function add_recipient($phone_number,$message_unique_id){
        $data = $this->data;
        $data["SMS"]["recipients"]["gsm"][] = [
            "msidn"=>$phone_number,
            "msgid"=>$message_unique_id
        ];
        $this->data = $data;
    }
    

    public function send() {

        $url = "http://api.ebulksms.com:8080/sendsms.json";
        $_data = $this->data;
        $_data["SMS"]["message"]["messagetext"] = innertext($this->message);
        $data = json_encode($_data);
        $headers = [
            "Content-Type: application/json"
        ];

        $php_errormsg = '';
        if (is_array($data)) {
            $data = http_build_query($data, '', '&');
        }
        
        $params = array('http' => array(
            'method' => 'POST',
            'content' => $data
            )
        );

        if ($headers !== null) {
            $params['http']['header'] = $headers;
        }


        $ctx = stream_context_create($params);
        
        $fp = fopen($url, 'rb', false, $ctx);
        if (!$fp) {
            return "Error: gateway is inaccessible";
        }
        
        //stream_set_timeout($fp, 0, 250);
        try {
            $response = stream_get_contents($fp);
            if ($response === false) {
                throw new Exception("Problem reading data from $url, $php_errormsg");
            }
        } catch (Exception $e) {
            $response = $e->getMessage();
        }
        return $response;
    }
}


class fcm{

    private $recipients = [];
    public $message = "";
    public $title = "";
    public $image = "";

    public static function update_token($uid,$token){
        global $conn;
        $conn->prepare("UPDATE users SET fcm_token=? WHERE uid=?")->execute([$token,$uid]);
    }

    public static function nullify_token($uid){
        self::update_token($uid,null);
    }

    public function add_recipient($user_token){
        $this->recipients[] = $user_token;
    }

    public function send() {
        global $fcm_server_key;

        $message = $this->message;

        $url = 'https://fcm.googleapis.com/fcm/send';

        $fields = array (
                'registration_ids' => $this->recipients,
                "notification"=>[
                    "title"=>$this->title,
                    "text"=>innertext($message),
                    "click_action"=>"FLUTTER_NOTIFICATION_CLICK",
                    "image"=>$this->image
                ]
        );
        $fields = json_encode ( $fields );
    
        $headers = array (
                'Authorization: key=' . $fcm_server_key,
                'Content-Type: application/json'
        );
    
        $ch = curl_init ();
        curl_setopt ( $ch, CURLOPT_URL, $url );
        curl_setopt ( $ch, CURLOPT_POST, true );
        curl_setopt ( $ch, CURLOPT_HTTPHEADER, $headers );
        curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, true );
        curl_setopt ( $ch, CURLOPT_POSTFIELDS, $fields );
    
        $result = curl_exec ( $ch );
        //echo $result;
        curl_close ( $ch );
    }
}



function processed_link($link){
    global $script_links;
    $processed_link = $link;

    foreach($script_links as $key=>$value){
        $processed_link = str_replace("{".$key."}",$value,$processed_link);
    }
    return $processed_link;
}


function notification_link($n){
    global $rel_dirname;

    $processed_link = processed_link($n['notification_link']);
    
    if ($n['notification_context']==='official'){
        $link = $n['notification_link'];
    }else{
        $link = $rel_dirname.$processed_link;
    }

    return trim($link);
}



function time_difference($t){
    $tt=$t;
    $f=time()-$t;
    $dn = date("w",time());
    $dt=date("w",$tt);
    $yn=date("y",time());
    $yt=date("Y",time());
    
    if($f<=5){
        $s="just now";
        $s = "";
    }elseif(floor($f/60)<1){
        $s=floor($f). " seconds";
    }elseif(floor($f/60)===1){
        $s="1min";
    }elseif(floor($f/60)<60){
        $s=floor($f/60)."mins";
    }elseif(floor($f/3600)===1){
        $s="1hr";
    }elseif(($dn-$dt)==1 && $f<3600*7){
        $s="yesterday at ".date("g:ia",$t);
    }elseif(floor($f/3600)<24){
        $s=floor($f/3600)."hrs";
    }elseif(($dn-$dt)<6 && $f<3600*7){
        $s=date("A",$tt);
    }elseif(($yn-$yt)<1){
        $s=date("B",$tt)." ".date("d",$tt);
    }else{
        $s=date("B",$tt)." ".date("d",$tt).",".date("Y",time());
    }
    return $s;
}

function str_replace_from_array($array,$value){
    return str_replace(array_keys($array),array_values($array),$value);
}

function date_difference($timestamp1 , $timestamp2 , $differenceFormat = '%a' ){
    $datetime1 = (new DateTime())->setTimestamp((int)$timestamp1);
    $datetime2 = (new DateTime())->setTimestamp((int)$timestamp2);
   
    $interval = date_diff($datetime1, $datetime2);
    $value = $interval->format($differenceFormat); 
   
    switch($differenceFormat){
        case "%hhrs %imins":
            $value = str_replace_from_array([
                '1hrs'=>'1hr',
                ' 0mins'=>'',
                '0hrs '=>''
            ],$value);
    }

    return trim($value);
}


function camel($string){
    $ar=preg_split("/\s+/",$string);
    $ty=0;
    foreach ($ar as $ars){
     $ty=$ty+1;
     $out = "";
     if ($ty===1){
         $out=strtolower($ars);
     }else{
         $out.=ucfirst($ars);
     }
    }
    return $out;
}



function message_you_scheme($script=false,$message=false,$tt=false){
    if ($script){
        $m = nl2br($message);
        $tt = $tt!=null?time_difference($tt):"";
        $tt="";
    }else{
        $m="\${nl2br(editor.root.innerHTML)}";
        $tt = "";
    }

    return "
    <div class='message-wrapper you-wrapper'>
        <div class='you message'>
            $m
        </div>
        <div class='message-time'>".$tt."</div>
    </div>
    ";
}



function message_not_you_scheme($script=false,$message=false,$tt=false){
    global $user_to;

    if ($script){
        $m = nl2br($message);
        $tt = $tt!=null?time_difference($tt):"";
        $tt="";
    }else{
        $m="\${nl2br(\$('#message-box').val())}";
        $tt = "";
    }

    return "
    <div class='message-wrapper not-you-wrapper'>
        <div>
            <div class='not-you-with-image'>
                <!--
                <div>
                    <img class='not-you-image' src='$user_to->dp'>
                </div>
                -->

                <div class='not-you message'>
                    $m
                </div>
            </div>
            <div class='message-time'>
                <div>
                    ".$tt."
                </div>
            </div>
        </div>
    </div>
    ";

}



function upload_file_markup($iterator=1,$_options=[]){
    global $image_icon;

    $options = set_defaults([
        'icon'=>$image_icon
    ],$_options);

    $it = $iterator;

    return "
    
        <div class='file-upload centralize'>
            <div class='centralize'>
                <label for='file-$it' id='file-label-$it' class='file-label'>
                    <img src='$options->icon' class='file-label-image' id='file-label-image-$it'>
                </label>
                <input type='file' name='file_$it' id='file-$it' class='file display-none'/>
            </div>
        </div>
    ";
}
$upload_file_markup =  upload_file_markup();




function string_truncate($string, $your_desired_width) {
    $parts = preg_split('/([\s\n\r]+)/', $string, 0, PREG_SPLIT_DELIM_CAPTURE);
    $parts_count = count($parts);
  
    $length = 0;
    $last_part = 0;
    for (; $last_part < $parts_count; ++$last_part) {
      $length += strlen($parts[$last_part]);
      if ($length > $your_desired_width) { break; }
    }
  
    return implode(array_slice($parts, 0, $last_part));
}


/**
 * @example description
```
$notification = new Notification();
$notification
->set_sender($posted_by)
->set_sms_subject($sms_subject)
->set_fcm_subject($fcm_subject)
->set_email_subject("$dispute->subject #$ticket_id")
->set_notification_content($body)
->set_notification_context("dispute-$ticket_id");

foreach ($notification_recipients as $uid){
    $notification->add_recipient($uid);
}

$notification->send();
```
*/
class Notification{
    
    private $recipients = [];
    private $sender_uid;
    private $sms_subject = "";
    private $fcm_subject = "";
    private $email_subject = "";
    private $notification_content = "";
    private $notification_context = "";
    private $notification_link = "";


    public function add_recipient($recipient_uid){
        $this->recipients[] = $recipient_uid;
        return $this;
    }

    public function set_sender($sender_uid=null){
        global $user;
        if (!$sender_uid) $sender_uid = $user->uid;
        $this->sender_uid = $sender_uid;
        return $this;
    }


    public function set_sms_subject($sms_subject){
        $this->sms_subject = $sms_subject;
        return $this;
    }


    public function set_fcm_subject($fcm_subject){
        $this->fcm_subject = $fcm_subject;
        return $this;
    }


    public function set_email_subject($email_subject){
        $this->email_subject = $email_subject;
        return $this;
    }


    public function set_notification_content($notification_content){
        $this->notification_content = $notification_content;
        return $this;
    }


    public function set_notification_context($notification_context){
        $this->notification_context = $notification_context;
        return $this;
    }


    public function set_notification_link($notification_link){
        $this->notification_link = $notification_link;
        return $this;
    }

    
    public function send(){
        $notification_content = $this->notification_content;
        $plaintext_notification_content = innertext($notification_content);

        $sender = user($this->sender_uid);
        foreach ($this->recipients as $uid){
            (new row_action([
                'table_name'=>'notifications',
                'columns'=>[
                    'notification'=>$this->notification_content,
                    'notification_to'=>$uid, 
                    'notification_from'=>$this->sender_uid,
                    'notification_link'=>$this->notification_link,
                    'notification_context'=>$this->notification_context,
                    'read'=>'false'
                ],
                'update'=>[
                    'time'=>time()
                ]
            ]))->insert_once()->update();
        }


        $mail = phpmailer();
        $sms_subject = $this->sms_subject;
        $fcm_subject = $this->fcm_subject;
        $mail->Subject = $this->email_subject;
        $mail->Body    = "
        <div>
            <div style='font-size:20px;'>$notification_content</div>
        </div>
        ";
    
        $mail->AltBody = $plaintext_notification_content;

        $ebulksms = new ebulksms();
        $fcm = new fcm();
        $ebulksms->message = "$sms_subject\n\n$plaintext_notification_content";
        $fcm->title = $fcm_subject;
        $fcm->message = "$sms_subject\n\n$plaintext_notification_content";
        $fcm->image = $sender->dp;

        foreach ($this->recipients as $uid){
            $_user = user($uid);
            $mail->addAddress($_user->email);
            $ebulksms->add_recipient($_user->phone_number,$uid);
            $fcm->add_recipient($_user->fcm_token);
        }
        

        $ebulksms->send();
        $mail->send();
        $fcm->send();
    }


    public static function count_unread($uid=null){
        global $user;
        if (!$uid) $uid = $user->uid;  
        return count(db_fetch("SELECT * FROM notifications WHERE `notification_to`=? AND `read`!=?",[$uid,'true']));
    }


    public static function info($notification_id){
        return (object)db_fetch_one("SELECT * FROM notifications WHERE id=?",[$notification_id]);
    }



    public static function link($notification_id){
        global $rel_dirname;
        $info = self::info($notification_id);
        $track_id = explode("-",$info->notification_context)[1];

        if (str_contains($info->notification_context,"dispute")){
            $link = "$rel_dirname/roles/dispute-timeline.php?ticket-id=$track_id";
        
        }else if (str_contains($info->notification_context,"leave")){
            $link = "$rel_dirname/roles/approve-leave-request.php?leave-id=$track_id";

        }else if (str_contains($info->notification_context,"expense_request")){
            $link = "$rel_dirname/roles/expense-requests.php";

        }else if (str_contains($info->notification_context,"budget_proposals")){
            $link = "$rel_dirname/roles/approve-budget.php?proposed_budget_id=$track_id";

        }else if (str_contains($info->notification_context,"salary_requests-$track_id")){
            $link = "$rel_dirname/roles/approve-salary-payment.php?request_id=$track_id";

        } else if (str_contains($info->notification_context,"approve_expense_request-$track_id")){
            $link = "$rel_dirname/roles/approve-salary-payment.php?request_id=$track_id";

        } else {
            $link = $info->notification_link;
        }
        return $link;
    }


    public static function clear($context,$uid=null){
        global $conn;
        $user = user();
        if (!$uid) $uid = $user->uid;
        $conn->prepare("UPDATE notifications SET `read`=?,read_time=? WHERE notification_context=? AND notification_to=?")->execute(["true",time(),$context,$uid]);
    }

}



function str_contains($haystack,$needle){
    return strpos($haystack,$needle)!==false;
}



class Message{
    
    private $recipients = [];
    private $sender_uid;
    private $fcm_subject = "";
    private $message = "";


    public function add_recipient($recipient_uid){
        $this->recipients[] = $recipient_uid;
    }

    public function set_sender($sender_uid=null){
        global $user;
        if (!$sender_uid) $sender_uid = $user->uid;
        $this->sender_uid = $sender_uid;
        return $this;
    }


    public function set_fcm_subject($fcm_subject){
        $this->fcm_subject = $fcm_subject;
        return $this;
    }


    public function set_message($message){
        $this->message = $message;
        return $this;
    }


    
    public function send(){

        $sender = user($this->sender_uid);
        foreach ($this->recipients as $uid){
            (new row_action([
                'table_name'=>'messages',
                'columns'=>[
                    'to'=>$uid,
                    'from'=>$this->sender_uid,
                    'message'=>$this->message,
                    'time'=>time()
                ]
            ]))->insert();
        }


        $fcm_subject = $this->fcm_subject;

        $fcm = new fcm();
        $fcm->title = $fcm_subject;
        $fcm->image = $sender->dp;

        foreach ($this->recipients as $uid){
            $_user = user($uid);
            $fcm->add_recipient($_user->fcm_token);
        }
        
        $fcm->send();
    }


    public static function count_unread($uid=null){
        global $user;
        if(!$uid) $uid = $user->uid;
        return count(db_fetch("SELECT * FROM messages WHERE `to`=? AND `read`!=? GROUP BY `from`",[$uid,'true']));
    }


    public static function clear($recipient_uid,$sender_uid=null){
        global $conn,$user;
        if (!$sender_uid) $sender_uid = $user->uid;
        $conn->prepare("UPDATE messages SET `read`=? WHERE `from`=? AND `to`=?")->execute(["true",$recipient_uid,$sender_uid]);
    } 
}



function greet_by_day(){
    // 24-hour format of an hour without leading zeros (0 through 23)
    $Hour = date('G');

    if ( $Hour >= 5 && $Hour <= 11 ) {
        $greeting =  "Good morning";
    } else if ( $Hour >= 12 && $Hour <= 18 ) {
        $greeting = "Good afternoon";
    } else if ( $Hour >= 19 || $Hour <= 4 ) {
        $greeting = "Good evening";
    }
    return $greeting;
}



function staffs(){
    global $offices;

    $staffs = [];
    
    foreach ($offices as $office=>$office_properties){
        $staffs[$office] = [];    
    }
    
    foreach (db_fetch("SELECT * FROM users") as $user){
        if (in_array($user["clearance"],array_keys($staffs))){
            $staffs[$user["clearance"]][] = $user["uid"];
        }
    }


    foreach ($staffs as $office=>$staff_list){
        if (count($staff_list)===0){
            unset($staffs[$office]);
        }
    }
    return $staffs;
}


function detailed_staffs(){
    global $offices;
    $staffs = staffs();
    foreach($staffs as $office=>$staff_list){
        unset($staffs[$office]);
        foreach($staff_list as $uid){
            $staffs[$offices[$office]["name"]][$uid] = user($uid);
        }
    }
    return $staffs;
}


function staff_list(){
    $staff_list = [];

    foreach(staffs() as $office=>$staffs){
        foreach($staffs as $staff){
            $staff_list[] = $staff;
        }
    }
    return $staff_list;
}


function detailed_staff_list(){
    $accumulator = [];
    foreach (staff_list() as $uid){
        $accumulator[$uid] = user($uid);
    }
    return $accumulator;
}


function set_default($parameter,$value){
    return isset($parameter)?$parameter:$value;
}



if (!function_exists("set_defaults")){
    function set_defaults($defaults,$options=[]){
        foreach ($defaults as $property=>$value){
            if (!isset($options[$property])) $options[$property] = $value;
        }
        return (object)$options;
    }
}



function fs_link($random_id,$file_extension){
    return fs_blob("cache","upload-links",$random_id,null,$file_extension);
}


function innertext($html_string){
    global $assets;
    include_once $assets."/simple_html_dom.php";

    $html = str_get_html($html_string);

    return $html->plaintext;

}



function detailed_time($time){
    date_default_timezone_set('Africa/Lagos');
    return date("l, jS F,Y \a\\t g:ia",$time);
}

function outlined_time($time){
    date_default_timezone_set('Africa/Lagos');
    return date("l, jS F, Y - g:ia",$time);
}


function std_array($std_class){
    return json_decode(json_encode($std_class),true);
}





function add_dialog_item($it,$item,$options=[
    'go_up'=>false
]){
    return "
    <div class='add-dialog-item'>
        <div class='add-dialog-item-content'>
            <div class='iterator'>
                <div>$it.</div>
                ".($options['go_up']?"<div style='width:100%;text-align:center;'>Click to go up</div>":"")."
            </div>
            <div class='add-dialog-item-name'>$item</div>
        </div>
        <div class='add-dialog-remove-wrapper'>
            <div class='add-dialog-remove' data-toggle='notice'><i class='fa fa-times'></i></div>
        </div>
    </div>
    ";
}



function js_escape($content){
    return minify_html($content);
}



function total_amount_by_items($items_array){
    $total = 0;

    foreach ($items_array as $_amount){    
        $total += (int) preg_replace("/,/","",$_amount);
    }
    return $total;
}




/**
 * @example description
```
$expenditure = new Expenditure();
$expenditure
    ->set_context($context)
    ->set_track_id($track_id)
    ->set_amount($amount)
```
*/
class Expenditure{
    public $fiscal_year,$context,$track_id,$amount;

    public function set_fiscal_year($fiscal_year){
        $this->fiscal_year = $fiscal_year;
        return $this;
    }

    public function set_context($context){
        $this->context = $context;
        return $this;
    }


    public function set_track_id($track_id){
        $this->track_id = $track_id;
        return $this;
    }


    public function set_amount($amount){
        $this->amount = $amount;
        return $this;
    }

    private function add(){
        row_action([
            'table_name'=>'expenditure',
            'columns'=>[
                'fiscal_year'=>$this->fiscal_year,
                'context'=>$this->context,
                'track_id'=>$this->track_id,
                'amount'=> $this->amount,
                'time'=>time()
            ]
        ])->insert();
        return $this;
    }


    public function add_salary(){
        $this->set_context("salary_payments")->add();
    }

    public function add_expense(){
        $this->set_context("expense")->add();
    }


    public static function total_amount($fiscal_year=null){
        if (!$fiscal_year) $fiscal_year = current_values::fiscal_year();
        return db_fetch_one("SELECT SUM(`amount`) AS total FROM expenditure WHERE fiscal_year=?",[$fiscal_year])["total"];
    }

    public static function formatted_total_amount($fiscal_year=null){
        return number_format(self::total_amount($fiscal_year));
    }

    public static function timeline($fiscal_year=null){
        global $rel_dirname,$expenditure_contexts;
        if (!$fiscal_year) $fiscal_year = current_values::fiscal_year();
        $timeline = db_fetch("SELECT * FROM expenditure WHERE fiscal_year=? ORDER BY time DESC",[$fiscal_year]);

        foreach ($timeline as $index => $_expenditure){
            $timeline[$index]['detailed_time'] = date("M j, g:ia",$_expenditure["time"]);
            $timeline[$index]['formatted_amount'] = number_format($_expenditure["amount"]);
            $timeline[$index]["detailed_context"] = $expenditure_contexts[$_expenditure["context"]];

            $track_id = $_expenditure["track_id"];
            $link = "";
            switch($_expenditure["context"]){
                case "salary_payments":
                    $link = "$rel_dirname/roles/approve-salary-payment.php?request_id=$track_id";
                break;

                case "expense":
                    $link = "$rel_dirname/roles/approve-expense-request.php?expense_id=$track_id";
                break;
            }
            $timeline[$index]['link'] = $link;
        }
        return $timeline;
    }
}



class current_values{
    
    public static function set($variable_name,$variable_value){
        row_action([
            'table_name'=>'current_values',
            'columns'=>[
                'name'=>$variable_name
            ],
            'update'=>[
                'value'=>$variable_value,
                'time'=>time()
            ]
        ])->insert_once()->update();
    }
    
    public static function get($variable_name){
        return db_fetch_one("SELECT * FROM current_values WHERE name=?",[$variable_name])["value"];
    }

    public static function set_fiscal_year($fiscal_year){
        self::set("fiscal_year",$fiscal_year);
    }

    public static function fiscal_year(){
        return self::get("fiscal_year");
    }


    public static function set_payroll($payroll){
        self::set("payroll",json_encode($payroll));
    } 

    public static function payroll(){
        return json_decode(self::get("payroll"),true);
    }

    public static function payroll_cost(){
        return total_amount_by_items(self::payroll());
    }
}




function months($month_format="F"){
    $months =  [];
    for ($i = 0; $i < 12; $i++) {
        $timestamp = mktime(0, 0, 0, date('n') - $i, 1);
        $months[date('n', $timestamp)] = date($month_format, $timestamp);
    }
    ksort($months,SORT_NUMERIC);
    return $months;
}



function html_selected($selected_option, $html_select_attributes, $options_and_values_array, $has_placeholder=true){
    $c = count($options_and_values_array);
    if ($has_placeholder){
        $options_and_values_array = [""=>"-Select-"]+$options_and_values_array;
        $c--;
    }
    
    if ($c===0){
        return "";
    }
    
    $select="<select $html_select_attributes>";
    foreach ($options_and_values_array as $k=>$v){
        if ((string)$k===(string)$selected_option){
            $f = "selected";
        }else{
            $f = "";
        }
        if (strpos($k,'"') !==-1){
            $dl = "'";
        }else{
            $dl = '"';
        }
        $select.="<option value=$dl".$k."$dl $f>$v</option>";    
    }
    $select.="</select>";
    
    return $select;
}



/**
 * Apply callable function to all member of an associative array
 *
 * @param array $array
 * @param Callable $callable ($key,$value,$array)
 * @return array The newly formed array
 */
function array_apply($array,$callable){
    foreach ($array as $key=>$value){
        $array = call_user_func_array($callable,[$key,$value,$array]);
    }
    return $array;
}


function array_columns($array){
    $_array = [];

    foreach($array as $k=>$v){
        foreach($v as $a=>$b)
        $_array[$a] = $b;
    }
    
    return $_array;
}


function array_populate($element,$duplicate_count){
    $_ = [];

    for ($i=0;$i<$duplicate_count;$i++){
        $_[$i] = $element;
    }
    return $_;
}


function array_equate($array,$equal_to){
    return array_combine($array,array_populate($equal_to,count($array)));
}


function array_multiply($a){
    return array_combine($a,$a);
}


function base64_arg($argument){
    return base64_encode(json_encode($argument));
}


function recaptcha_verify($recaptcha_token){
    $url = 'https://www.google.com/recaptcha/api/siteverify';
    $data = array('secret' => '6LeLp-cZAAAAAOKizh5YYDspea0sONn3fFekBiSR', 'response' => $recaptcha_token);

    $options = array(
        'http' => array(
            'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
            'method'  => 'POST',
            'content' => http_build_query($data)
        )
    );
    $context  = stream_context_create($options);
    $result = file_get_contents($url, false, $context);
    if ($result === FALSE) { /* Handle error */ }

    return json_decode($result);
}



function verify_email($email_address,$email_code,$email_token){
    return row_action([
        'table_name'=>'email_verification',
        'columns'=>[
            'token'=>$email_token,
            'code'=>$email_code,
            'email'=>$email_address
        ]
    ])->exists();
}


function http_get_request($endpoint,$params=[]){
	$curl = curl_init();

	curl_setopt_array($curl, array(
		CURLOPT_URL => encoded_url($endpoint,$params),
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_ENCODING => "",
		CURLOPT_MAXREDIRS => 10,
		CURLOPT_TIMEOUT => 0,
		CURLOPT_FOLLOWLOCATION => true,
		CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		CURLOPT_CUSTOMREQUEST => "GET",
		CURLOPT_SSL_VERIFYHOST=>0,
		CURLOPT_SSL_VERIFYPEER=>0,
		CURLOPT_HTTPHEADER => array(
			"Cookie: __cfduid=d5c26ee7de5ae66d47b5a468cc1d6d8231614169336"
		),
	));

	$response = curl_exec($curl);
	return $response;
}



/**
 * Encodes string to be used as url using array scheme
 * @param int $location - The location of the file
 * @param array $array
 * @return void
 */
function encoded_url($location,$array=[]){
    $r = $location;

    if (count($array)>0){
        $r .= "?";
        
        $it = 0;
        $count = count($array);
        foreach($array as $key=>$value){
            $it++;

            $r .= "$key=".urlencode($value);
            
            if ($it!=$count){
                $r .= "&";
            }
        }
    }
    return $r;
}


class TransactionEmailAlert{

    private $sender_address;
    private $recipient_address;
    private $sender;
    private $recipient;
    private $token;
    private $date;
    private $time;
    private $transaction_id;

    function __construct($sender_address, $recipient_address){
        $this->sender_address = $sender_address;
        $this->recipient_address = $recipient_address;
        $this->sender = user($sender_address);
        $this->recipient = user($recipient_address);
    }


    public function setToken($token){
        $this->token = $token;
        return $this;
    }


    public function setDate($date){
        $this->date = $date;
        return $this;
    }


    public function setTime($time){
        $this->time = $time;
        return $this;
    }


    public function setTranscationId($transaction_id){
        $this->transaction_id = $transaction_id;
        return $this;
    }


    public function send(){
        foreach ([
            [
                "type"=>"credit",
                "label"=>"Credit"
            ],
            [
                "type"=>"debit",
                "label"=>"Debit"
            ]
        ] as $transaction) {

            $mail = phpmailer();
            $mail->Subject = "eDT Transaction Alert [$transaction[label]: {$this->token}eDT]";
            $message_body = $this->message($transaction["type"]);
            $mail->Body = $message_body;

            $mail->AltBody = innertext($message_body);

            $user = $this->accountUser($transaction["type"]);

            $mail->addAddress($user->email_address);
            $mail->preSend();

            Gmail::send($mail->getSentMIMEMessage());
        }
        
    }


    private function accountUser($transaction_type){
        switch($transaction_type){
            case "credit":
                $user = $this->recipient;
            break;

            case "debit":
                $user = $this->sender;
            break;
        }
        return $user;
    }


    private function message($transaction_type){

        global $organization_logo;

        switch($transaction_type){
            case "credit":
                $transaction_label = "Credit";
                $user_address = $this->recipient_address;
            break;

            case "debit":
                $transaction_label = "Debit";
                $user_address = $this->sender_address;
            break;
        }

        $user = $this->accountUser($transaction_type);

        return "
        <!DOCTYPE html>
            <div>
                <div style='margin-bottom: 30px; text-align: center;'>
                    <img src='$organization_logo' style='width: 100px; height: 100px' >
                </div>
                <div style='margin-bottom: 5px;'>Dear  <b>$user_address</b></div>
                
                <div style='margin-bottom: 5px;'>
                    We wish to inform you that a $transaction_label transaction occurred on your account with us.
                </div>
                
                <div style='margin-bottom: 15px;'>
                    The details of this transaction are shown below:
                </div>
                
                <div style='font-weight: 600; text-decoration: underline; font-size: 15px;'>Transaction Notification</div>
                <div style='margin-top: 7px'>Account Address : $user->public_address</div>
                <div style='margin-top: 7px'>Amount	:	<b>{$this->token}eDT</b></div>
                <div style='margin-top: 7px'>Value Date	:	$this->date</div>
                <div style='margin-top: 7px'>Time of Transaction :	$this->time</div>
                <div style='margin-top: 7px'>Document Number :	$this->transaction_id</div>

                <div style='margin-top: 20px'>The balances on this account as at  $this->time  are as follows:</div>
                
                <div style='margin-top: 5px'>Current Balance : <b>{$user->token}eDT</b></div>
                
                <div style='margin-top: 3px'>Current Bonus : <b>{$user->bonus}eDB</b></div>
                
                <div style='margin-top: 20px'>Please always remember to keep your private key safe to avoid security breaches on your account. Avoid registering with your private with untrusted third party applications. Your security means alot to us.</div>
                
                <div style='margin-top: 20px'>Regards</div>
                <div>eDeposite Cryptocurrency</div>
            </div>
        ";
    }
}

?>
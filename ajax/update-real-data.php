<?php
include_once $_SERVER['DOCUMENT_ROOT']."/settings.php";
include_once $assets."/db.php";
include_once $assets."/variables.php";
include_once $assets."/functions.php";

$response = [];

show_errors();

$context = $_POST["context"];

switch($context){
    case "main":
        $last_id = isset($_POST['last_id'])?$_POST['last_id']:0;
        $no_time_out = isset($_POST['no_time_out'])?$_POST['no_time_out']:[];

        $bu = [];
        foreach ($no_time_out as $nto){
            $v = db_fetch_one("SELECT * FROM attendance WHERE id=?",[$nto]);
            $bu[$nto] = $v['time_out']!=0?date("g:ia",$v['time_out']):'';
        }

        $response['no_time_out']=$bu;

        $f=db_fetch("SELECT * FROM attendance WHERE id>?",[$last_id]);

        for ($i=0;$i<count($f);$i++){
            $c = user($f[$i]["uid"]);
            $f[$i]['name'] = $c->name;
            $f[$i]['office'] = $c->office;
            $f[$i]['detailed_time_in'] = date("g:i a",$f[$i]['time_in']);
            $f[$i]['detailed_time_out'] = $f[$i]['time_out']!=0?date("g:i a",$f[$i]['time_out']):'';
        }

        $response['new_entries'] = $f;
    break;

}


echo json_encode($response);

?>
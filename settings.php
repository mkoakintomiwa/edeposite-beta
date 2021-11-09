<?php

$settings = json_decode(file_get_contents($_SERVER['DOCUMENT_ROOT']."/settings.json"));

$db_name = $settings->db_name;

$db_user = $settings->db_user;

$db_password = $settings->db_password;

$site_port = $settings->site_port;

$rel_dirname;
if (!isset($portal_type)){
	$rel_dirname =  $settings->rel_dirname;     //note that this was initially $abs_dirname but is now deprecated
}else if($portal_type==='admissions'){
	$rel_dirname = $settings->admissions_rel_dirname;
}























































/***************************************************   
****************************************************
****************************************************
****************************************************
*******THESE ARE GENERATED VALUES, DO NOT EDIT******
****************************************************
****************************************************
****************************************************
***************************************************/

date_default_timezone_set('Africa/Lagos');

$site_host = $_SERVER['HTTP_HOST'];

function is_localhost(){
 return $_SERVER['HTTP_HOST']==='localhost';
}

if (is_localhost()){
	$protocol = "http://";
	$specs_path = "/portals/$db_name/specs";	
}else{
	$protocol = "https://";
	$specs_path = "/specs";
}

$host = $protocol.$site_host;

$base_url = $host;

$portal_url = $host.$rel_dirname;

$specs_rel_dir = $rel_dirname.$specs_path;

$specs_dir = $_SERVER['DOCUMENT_ROOT'].$specs_rel_dir;

$specs_url = $host.$specs_rel_dir;

$abs_dirname = $rel_dirname; //abs_dirname is deprecated

$dirname = $_SERVER['DOCUMENT_ROOT'].$abs_dirname;

$assets_abs_url = $abs_dirname . "/assets";

$ajax = $abs_dirname . "/ajax";

$images = $specs_rel_dir . "/images";

$general_images = $portal_url."/images";

$assets = $dirname . "/assets";


?>
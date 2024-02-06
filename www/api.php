<?php 
require_once 'main.php';
use Main\Construction as Construction;
class Api extends Construction
{
}
$api = new Api;
if(!empty($_REQUEST['jason'])){
	$api->dataProcessing($_REQUEST['jason']);
}else{
    $api->errorMessage('bad json');
}

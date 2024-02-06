<?php 
require_once 'main.php';
use Main\Construction as Construction;
class Approve extends Construction
{
	public function approve($mail){
		parent::approveMail($mail);
	}
}
$approve = new Approve;
if(!empty($_REQUEST['mail'])){
	$approve->approve($_REQUEST['mail']);
}else{
    $approve->errorMessage('empty mail');
}

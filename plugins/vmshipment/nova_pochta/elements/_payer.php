<?php defined('_JEXEC') or die();
if (!class_exists('np_param'))
    require(JPATH_ROOT . DS . 'plugins' . DS . 'vmshipment' . DS .'nova_pochta'. DS .'nova_pochta'.DS .'np_param.php');
class JFormFieldPayer extends JFormField {
	var $type = 'payer';
    function getInput() {
		$arrayPayer = array ('0'=>'отримувач', '1'=>'відправник' , '2'=>'третя особа');
		$np_param =new np_param();
		$payerSelct = $np_param->prepareSelect($arrayPayer,$name,$value,$control_name,'payerElement');  	
		return $payerSelct;
    }
}//
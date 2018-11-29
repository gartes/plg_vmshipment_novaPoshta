<?php
defined('_JEXEC') or die();
if (!class_exists('np_param'))
    require(JPATH_ROOT . DS . 'plugins' . DS . 'vmshipment' . DS .'nova_pochta'. DS .'nova_pochta'.DS .'np_param.php');

class JElementFirmDelivery extends JElement {

    var $_name = 'firmdelivery';

    function fetchElement($name, $value, &$node, $control_name) {
	$arrayPayer = array ('np'=>'Новая Почта','dhl'=>'DHL');
    $np_param =new np_param();
	return $np_param->prepareSelect($arrayPayer,$name,$value,$control_name,'firmDelivery');  	 
    }
}
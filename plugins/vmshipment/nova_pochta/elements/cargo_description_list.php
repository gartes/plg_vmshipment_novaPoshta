<?php defined('_JEXEC') or die();
//getCargoDescriptionList() - загрузить справочник описания груза
//
class JFormFieldCargodescriptionlist extends JFormFieldList {
	var $type = 'cargodescriptionlist';
	function getOptions() {
		$array = array ('rud'=>'dРусский');  
		foreach ($array as $k => $v) {
			//$options[] = JHtml::_('select.option', $k, vmText::_(strtoupper($v) ) );
		}
		return $options;	
	}
}	
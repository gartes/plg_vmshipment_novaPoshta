<?php defined('_JEXEC') or die();
//getCargoDescriptionList() - загрузить справочник описания груза
//
if (!class_exists('np_param'))
    require(JPATH_ROOT . DS . 'plugins' . DS . 'vmshipment' . DS .'nova_pochta'. DS .'nova_pochta'.DS .'np_param.php');

JFormHelper::loadFieldClass('list');
jimport('joomla.form.formfield');


class JFormFieldCargodescriptionlist extends JFormFieldList {
	var $type = 'cargodescriptionlist';
	function getOptions() {
		$array = array ('ru'=>'Русский', 'ua'=>'Украинский' , 'gr'=>'Грузинский');
		foreach ($array as $k => $v) {
			$options[] = JHtml::_('select.option', $k, vmText::_(strtoupper($v) ) );
		}
		return $options;	
	}
}	
<?php
defined('_JEXEC') or die();
if (!class_exists('np_param'))
    require(JPATH_ROOT . DS . 'plugins' . DS . 'vmshipment' . DS .'nova_pochta'. DS .'nova_pochta'.DS .'np_param.php');
// елемент выбора складов в городе
class JFormFieldDescriptionOrder extends JFormFieldList {
		var $type = 'descriptionorder';
		function getOptions() {
			$np_param =new np_param();
			$setingNP = $np_param->getSetingShipParams ();
			if(!$setingNP['key_privat_np']){
				$arrayLang[]='Выберите...';
				foreach ($arrayLang as $k => $v) {
					$options[] = JHtml::_('select.option', $k, vmText::_(strtoupper($v) ) );
				}
				return $options;	
			}
			$arr = $np_param->CommonGetCargoDescriptionList();
			foreach ($arr as $k => $v) {
				$options[] = JHtml::_('select.option', $k, vmText::_(strtoupper($v) ) );
			}
			return $options;
		}
}
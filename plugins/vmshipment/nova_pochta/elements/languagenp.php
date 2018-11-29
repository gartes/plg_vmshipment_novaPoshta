<?php
defined('_JEXEC') or die();

JFormHelper::loadFieldClass('list');
jimport('joomla.form.formfield');

class JFormFieldLanguagenp extends JFormFieldList {
    var $type = 'languagenp';
	function getOptions() {
		$arrayLang = array ('ru'=>'Русский!', 'ua'=>'Украинский');
		foreach ($arrayLang as $k => $v) {
			$options[] = JHtml::_('select.option', $k, vmText::_(strtoupper($v) ) );
		}
		return $options;
	}
}
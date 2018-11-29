<?php defined('_JEXEC') or die();
class JFormFieldVmOrderState extends JFormFieldList {
    var $type = 'vmorderstate';
    function getOptions() {
        $db = JFactory::getDBO();
        $query = 'SELECT `order_status_code` AS value, `order_status_name` AS text'
                . ' FROM `#__virtuemart_orderstates` '
                . ' WHERE `virtuemart_vendor_id` = 1'
                . ' ORDER BY `ordering` ASC ';

        $db->setQuery($query);
        $fields = $db->loadObjectList();
		foreach ($fields as $field) {
			$options[] = JHtml::_('select.option', $field->value, vmText::_(strtoupper($field->text) ) );
		}
		return $options;
    }
}
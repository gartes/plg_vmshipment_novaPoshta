<?php

defined('_JEXEC') or die();



class JFormFieldRedeliveryPaymentPayer extends JFormField {


    var $type = 'RedeliveryPaymentPayer';



    function getInput() {

		$class = '';

		$RedeliveryPaymentPayer = array ('1'=>'відправник', '2'=>'отримувач');

        return JHTML::_('select.genericlist', $RedeliveryPaymentPayer, $control_name . '[' . $name . ']', $class, 'value', 'text', $value, $control_name . $name);

    }//



}
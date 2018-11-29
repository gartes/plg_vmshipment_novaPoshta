<?php  defined('_JEXEC') or die();
	
	
	JFormHelper::loadFieldClass('list');
	jimport('joomla.form.formfield');
	
	/**
	 * Class JFormFieldNpVmListTable
	 */
	class JFormFieldNpVmListTable extends JFormFieldList {
		
		/**
		 * Element name
		 * @access    protected
		 * @var        string
		 */
		var $type = 'npvmListTable';
		
		/**
		 * @return string
		 * @author    Gartes
		 * @since     3.8
		 * @copyright 13.11.18
		 */
		protected function getInput() {
			$html = array();
			$attr = '';
			
			// Initialize some field attributes.
			$attr .= !empty($this->class) ? ' class="' . $this->class . '"' : '';
			$attr .= !empty($this->size) ? ' size="' . $this->size . '"' : '';
			$attr .= $this->multiple ? ' multiple' : '';
			$attr .= $this->required ? ' required aria-required="true"' : '';
			$attr .= $this->autofocus ? ' autofocus' : '';
			
			$placeholder = $this->getAttribute('placeholder','Выбрать...');
			$attr .= ' data-placeholder="'.JText::_($placeholder).'"' ;
			
			/*echo'<pre>';print_r( $this  );echo'</pre>'.__FILE__.' '.__LINE__;*/
			
			// To avoid user's confusion, readonly="true" should imply disabled="true".
			if ((string) $this->readonly == '1' || (string) $this->readonly == 'true' || (string) $this->disabled == '1'|| (string) $this->disabled == 'true')
			{
				$attr .= ' disabled="disabled"';
			}
			
			// Initialize JavaScript field attributes.
			$attr .= $this->onchange ? ' onchange="' . $this->onchange . '"' : '';
			
			// Get the field options.
			$options = (array) $this->getOptions();
			
			// Create a read-only list (no name) with hidden input(s) to store the value(s).
			if ((string) $this->readonly == '1' || (string) $this->readonly == 'true')
			{
				$html[] = JHtml::_('select.genericlist', $options, '', trim($attr), 'value', 'text', $this->value, $this->id);
				
				// E.g. form field type tag sends $this->value as array
				if ($this->multiple && is_array($this->value))
				{
					if (!count($this->value))
					{
						$this->value[] = '';
					}
					
					foreach ($this->value as $value)
					{
						$html[] = '<input type="hidden" name="' . $this->name . '" value="' . htmlspecialchars($value, ENT_COMPAT, 'UTF-8') . '"/>';
					}
				}
				else
				{
					$html[] = '<input type="hidden" name="' . $this->name . '" value="' . htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8') . '"/>';
				}
			}
			else
				// Create a regular list.
			{
				$html[] = JHtml::_('select.genericlist', $options, $this->name, trim($attr), 'value', 'text', $this->value, $this->id);
			}
			
			return implode($html);
		}#END FN
		
		/**
		 * @return array
		 * @author    Gartes
		 * @since     3.8
		 * @copyright 13.11.18
		 */
		protected function getOptions() {
			$options = array();
			VmConfig::loadConfig();
			vmLanguage::loadJLang('com_virtuemart');
			
			$this->model = $this->getAttribute('model',false);
			$this->func = $this->getAttribute('func',false);
			if(!$this->model or !$this->func){
				return parent::getOptions();
			}
			$m = VmModel::getModel($this->model);
			if(!$m){
				return parent::getOptions();
			}
			$values = call_user_func(array($m,$this->func));
			
			/*
				echo'<pre>';print_r( $this->multiple );echo'</pre>'.__FILE__.' '.__LINE__;
				echo'<pre>';print_r( JHtml::_('select.option', 0, vmText::_('COM_VIRTUEMART_FORM_TOP_LEVEL')) );echo'</pre>'.__FILE__.' '.__LINE__;*/
			
			if(!$this->multiple)
				$options[] =  JHtml::_('select.option', '', '');
			
			$lvalue = $this->getAttribute('lvalue','value');
			$ltext = $this->getAttribute('ltext','text');
			
			foreach ($values as $v) {
				$options[] = JHtml::_('select.option', $v->$lvalue, vmText::_($v->$ltext));
			}
			
			// Merge any additional options in the XML definition.
			$options = array_merge(parent::getOptions(), $options);
			
			if(!is_array($this->value))$this->value = array($this->value);
			
			if($this->multiple){
				$this->multiple = ' multiple="multiple" ';
			}
			
			return $options;
		}
	}
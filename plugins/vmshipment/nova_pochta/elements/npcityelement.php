<?php defined( '_JEXEC' ) or die();
	if ( !class_exists( 'xzlib\xzlib_app' ) ) require_once JPATH_LIBRARIES . '/xzlib/xzlib_app.php';
	if ( !class_exists( 'helpersShipmentMethodFields' ) )
		require( JPATH_PLUGINS.DS.'vmshipment'.DS.'nova_pochta'.DS.'nova_pochta'.DS.'helpers'.DS.'shipmentMethodFields.php' );
	/**
	 * Город отправителя
	 *
	 * Class JFormFieldNpCityElement
	 */
	class JFormFieldNpCityElement extends JFormFieldList
	{
		
		/**
		 * @var string
		 */
		var $type = 'npcityelement';
		
		/**
		 *
		 * @return array|mixed
		 * @author    Gartes
		 * @since     3.8
		 * @copyright 07.11.18
		 * @throws Exception
		 */
		function getOptions ()
		{
			
			$app = \JFactory::getApplication() ; 
			$api        = xzlib\xzlib_app::_get( 'api', $options = [] );
			$novaPoshta = $api->getApi( 'nova_poshta', $options = [] );
			
			// $novaPoshta->setApiKey ( $method->keyAPI ) ;
			
			$modelAddress = $novaPoshta->getModel( 'Address' );
			
			$prop        = $modelAddress->getCity();
			
			
			
			
			
			$options = [];
			foreach ( $prop->data as $type )
			{
				
				$attrArr = [];
				
				
				foreach ( $type as $key => $val )
				{
					$attrArr[ 'data-' . $key ] = $val;
				}
				
				
				$optKey = [
					'attr'        => $attrArr,
					'disable'     => '',
					'option.attr' => 'OptionCity',
					
					/* 'option.disable' => 'disable',
					 'option.key'     => 'value',
					 'option.label'   => 'option_label',
					 'option.text'    => 'text',*/
				];
				
				$options[] = JHtml::_( 'select.option', $type->Ref, JText::_(  $type->Description   ), $optKey );
			}
			
			
			return $options;
		}#END FN
		
		/**
		 * Method to get the field input markup for a generic list.
		 * Use the multiple attribute to enable multiselect.
		 *
		 * @return  string  The field input markup.
		 *
		 * @since   3.7.0
		 * @throws Exception
		 */
		protected function getInput ()
		{
			
			
			$doc = JFactory::getDocument();
			$app = JFactory::getApplication() ;
			
		
			$Method = helpersShipmentMethodFields::getMethod();
			$options = [];
			
			
			if ( !empty($Method->keyAPI) ){
				$doc->addScriptDeclaration('
					"undefined"===typeof NovaPoshta&&(NovaPoshta={});
					"undefined"===typeof NovaPoshta.Setting&&(NovaPoshta.Setting={});
					NovaPoshta.Setting.keyAPI="'.$Method->keyAPI.'";
					
					
				');
				// Get the field options.
				$options = (array) $this->getOptions();
			}
			
			$doc->addScriptOptions('npKeyAPI' , $Method->keyAPI );
			
			
			
			
			
			
			
			
			##################################################################################
//			
//			echo'<pre>';print_r( $this->value );echo'</pre>'.__FILE__.' '.__LINE__;
//			$session = JFactory::getSession();
//			# Получть данные из сессии
//			$cartData = $session->get( _SHIP_NAME,  helpersShipmentMethodCart::$cartDataNovaPoshta  ) ;
//			echo'<pre>';print_r( $cartData['cartData'] );echo'</pre>'.__FILE__.' '.__LINE__;
			
			##################################################################################
			
			// echo'<pre>';print_r( $options );echo'</pre>'.__FILE__.' '.__LINE__;
			
			
			
			// echo'<pre>';print_r($this->name);echo'</pre>'.__FILE__.' '.__LINE__;
			
			$attr = '';
			
			// Initialize some field attributes.
			$attr .= !empty( $this->class ) ? ' class="' . $this->class . '"' : '';
			$attr .= !empty( $this->size ) ? ' size="' . $this->size . '"' : '';
			$attr .= $this->multiple ? ' multiple' : '';
			$attr .= $this->required ? ' required aria-required="true"' : '';
			$attr .= $this->autofocus ? ' autofocus' : '';
			
			
			// To avoid user's confusion, readonly="true" should imply disabled="true".
			if ( (string) $this->readonly == '1' || (string) $this->readonly == 'true' || (string) $this->disabled == '1' || (string) $this->disabled == 'true' )
			{
				$disabled = ' disabled="disabled"';
			}
			
			
			$attr = [
				'id'             => $this->fieldname, // HTML id for select field
				'list.attr'      => [ // additional HTML attributes for select field
					'onchange' => $this->onchange,
					'class'    => $this->class,
					
					'data-placeholder'=>'Выбирите из списка' ,
					
					// 'disabled' => $disabled ,
				],
				'list.translate' => true, // true to translate
				'option.key'     => 'value', // key name for value in data array
				'option.text'    => 'text', // key name for text in data array
				'option.attr'    => 'OptionCity' // key name for attr in data array
			];
			
			$view   = $app->input->get( 'view', false, 'WORD' );
			$task   = $app->input->get( 'task', false, 'WORD' );
			if (   $view == 'shipmentmethod' && $task == 'edit' ){
				$attr['list.select'] = $Method->CitySender ; // value of the SELECTED field
			}else{
				if ( empty($this->value)){
					// $this->value = ;
					$refCityRecipient = $options[0]->value ;
					$emptyOpt = new stdClass();
					$emptyOpt->value = '';
					$emptyOpt->text = '';
					$emptyOpt->OptionCity = [];
					array_unshift($options,$emptyOpt);
				
				}else {
					$attr['list.select'] = $this->value ; // value of the SELECTED field
					$refCityRecipient = $this->value;
				}#END IF
			}#END IF
			
			
			
			
			$paramNovaPochta = $app->input->getArray(array('_plgNovaPochta'=>'ARRAY')  ) ;
			if ( empty($paramNovaPochta['_plgNovaPochta']['CityRecipient'])){
				if (isset($refCityRecipient)){
					$paramNovaPochta['_plgNovaPochta']['CityRecipient'] = $refCityRecipient ;
				}#END IF
				
			}#END IF
			$app->input->set('_plgNovaPochta' , $paramNovaPochta['_plgNovaPochta'] );
			
			
			
			$result = JHtmlSelect::genericlist( $options, $this->name, $attr  /*, $optKey = 'value', $optText = 'text', $selected = null, $idtag = false, $translate = false*/ );
			
			// echo'<pre>';print_r( $options );echo'</pre>'.__FILE__.' '.__LINE__;
			
			return $result;
			// return implode($html);
		}#END FN
		
		
		
	}#END CLASS

















































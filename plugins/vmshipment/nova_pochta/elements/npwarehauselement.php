<?php defined('_JEXEC') or die();
	if ( !class_exists( 'xzlib\xzlib_app' ) ) require_once JPATH_LIBRARIES . '/xzlib/xzlib_app.php';
	if ( !class_exists( 'helpersShipmentMethodFields' ) )
		require( JPATH_PLUGINS.DS.'vmshipment'.DS.'nova_pochta'.DS.'nova_pochta'.DS.'helpers'.DS.'shipmentMethodFields.php' );
	
	/**
	 * елемент выбора складов в городе
	 *
	 * Class JFormFieldNpWarehausElement
	 */
	class JFormFieldNpWarehausElement extends JFormFieldList
	{
		var $type = 'npwarehauselement';
		
		/**
		 * @return array
		 * @author    Gartes
		 * @since     3.8
		 * @copyright 07.11.18
		 * @throws Exception
		 */
		function getOptions ()
		{
			$app = \JFactory::getApplication() ;
			
			
			$options = [];
			$Method = helpersShipmentMethodFields::getMethod();
			$paramNovaPochta = $app->input->getArray(array('_plgNovaPochta'=>'ARRAY')  ) ;
			
		 
			
			
			if ( empty( $Method->CitySender ) ){
				$app->enqueueMessage('Способ доставки Новая почта - не указан склад отправителя' , 'warning');
				return $options ;
			}
			
			$api        = xzlib\xzlib_app::_get( 'api', $options = [] );
			$novaPoshta = $api->getApi( 'nova_poshta', $options = [] );
			
			$modelAddress = $novaPoshta->getModel( 'Address' );
			
			$_cityRef = false ;
			if ($this->fieldname == 'SenderAddress' ){
				$_cityRef =  $Method->CitySender ;
			}else if ($this->fieldname == 'RecipientAddress'){
				$_cityRef = $paramNovaPochta['_plgNovaPochta']['CityRecipient'];
			}
			
			$prop        = $modelAddress->getWarehouses( $_cityRef );
			
			
			
			//echo'<pre>';print_r( $this->value );echo'</pre>'.__FILE__.' '.__LINE__;
			// echo'<pre>';print_r( $_cityRef );echo'</pre>'.__FILE__.' '.__LINE__;
			
			
			
			
			
			foreach ( $prop->data as $type )
			{
				$options[] = JHtml::_( 'select.option', $type->Ref, vmText::_( $type->Description  ) );
			}
			
			return $options;
		}#END FN
		
		
		
		
	}#END CLASS
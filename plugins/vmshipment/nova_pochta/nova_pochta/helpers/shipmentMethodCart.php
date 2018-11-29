<?php defined( '_JEXEC' ) or die( 'Restricted access' );
	
	/**
	 * Created by PhpStorm.
	 * User: oleg
	 * Date: 09.11.18
	 * Time: 13:36
	 */
	
	class helpersShipmentMethodCart
	{
		
		
		
		
		public  static $cartDataNovaPoshta = [
			'cartData'=>array(
				'nova_pochta'=>array(
					'CityRecipient' => null,
					'ServiceType'=>'WarehouseWarehouse',
					'Address' =>[
						'street'=>null ,
						'house'=>null ,
						'flat'=>null ,
					],
					'RefStreet'=>null,
				)
			)
		];
		
		/**
		 * Получить параметры груза Ширина х Длина х Высота
		 * Вага об’ємна (Ширина х Довжина х Висота/4000)
		 *
		 * @author    Gartes
		 * @since     3.8
		 * @copyright 15.11.18
		 */
		public static function getVolumeGeneralParams ( $order, $method, $subFormShipment )
		{
			
			$SizeUnits = ( !isset( $subFormShipment[ 'nova_pochta' ][ 'VolumeGeneralParams' ][ 'SizeUnits' ] ) ? $method->DEF_SizeUnits : $subFormShipment[ 'nova_pochta' ][ 'SizeUnits' ] );

//			$unq =  1; # метры
			$unq = 0.01; # сантиметры
//			$unq =  0.001; # миллиметры
			
			$_Width  = '';
			$_Length = '';
			$_Height = '';
			
			
			$del = 250;
			
			return [
				'VolumeGeneralParams' => [ '_Width' => $_Width, '_Length' => $_Length, '_Height' => $_Height, 'SizeUnits' => $SizeUnits ],
				
				'VolumeWeight' => ( ( ( $_Width * $SizeUnits ) * ( $_Length * $SizeUnits ) * ( $_Height * $SizeUnits ) ) * $del ),
			];
			
		}#END FN
		
		/**
		 * Подготовка адреса получателя
		 * @param $method
		 * @param $subFormShipment
		 * @param $CounterpartyData
		 *
		 * @return array
		 * @author    Gartes
		 * @since     3.8
		 * @copyright 15.11.18
		 */
		public static function prepareRecipientAddress( $method , $subFormShipment , $CounterpartyData ){
			
			if ($subFormShipment['nova_pochta']['ServiceType'] == 'WarehouseWarehouse'){
				return [
					'Ref'=> $subFormShipment ['nova_pochta']['RecipientAddress'] ,
					'Description'=>null
				];
			}
			
			if ( !class_exists( 'xzlib\xzlib_app' ) ) require_once JPATH_LIBRARIES . '/xzlib/xzlib_app.php';
			
			$api        = xzlib\xzlib_app::_get( 'api',  [] );
			$novaPoshta = $api->getApi( 'nova_poshta', [] );
			$novaPoshta->setApiKey ( $method->keyAPI ) ;
			$modelAddress = $novaPoshta->getModel( 'Address' );
			
			$methodProperties = new stdClass();
			$methodProperties->CounterpartyRef = $CounterpartyData->data[0]->Ref ;
			$methodProperties->StreetRef = $subFormShipment['nova_pochta']['RefStreet'] ;
			$methodProperties->BuildingNumber = $subFormShipment['nova_pochta']['Address']['house'] ;
			$methodProperties->Flat = $subFormShipment['nova_pochta']['Address']['flat'] ;
			$methodProperties->Note = null ;
			
			$res = $modelAddress->save($methodProperties);
			
			return (array)$res->data[0];
		}#END FN
		
		/**
		 * Создать объявленую стоимость
		 *
		 * @param $order
		 * @param $method
		 * @param $subFormShipment
		 *
		 * @return int
		 * @author    Gartes
		 * @since     3.8
		 * @copyright 11.11.18
		 */
		public static function prepareCost( $order, $method, $subFormShipment ){
			
			return 300 ;
		}#END FN
		
		/**
		 * Данные о плательщике за доставку
		 *
		 * @param $order
		 * @param $method
		 * @param $subFormShipment
		 *
		 * @return string
		 * @author    Gartes
		 * @since     3.8
		 * @copyright 11.11.18
		 */
		public static function preparePayerType(  $order , $method  , $subFormShipment ){
			
			$retPayerType = $method->PayerType;
			if ($order[ 'details' ][ 'BT' ]->order_total >= $method->free_shipment && $subFormShipment['nova_pochta']['ServiceType']=='WarehouseWarehouse' ){
				$retPayerType = 'Sender';
			}
		 
			return $retPayerType ;
		}#END FN
		
		/**
		 * Подготовка данных по обратной доставки
		 * @param $order
		 * @param $method
		 *
		 * @return false|string
		 * @author    Gartes
		 * @since     3.8
		 * @copyright 11.11.18
		 */
		public static function prepareBackwardDeliver ( $order, $method )
		{
			$ret                   = new stdClass();
			$ret->PayerType        = $method->BackwardDeliveryPayerType;
			$ret->CargoType        = $method->BackwardDeliveryCargoType;
			$ret->RedeliveryString = $order[ 'details' ][ 'BT' ]->order_total;
			
			return json_encode(  $ret   );
		}#END FN
		
		/**
		 * Подготовить поля для создания контрагента
		 *
		 * @param $cart
		 * @param $order
		 * @param $subFormShipment
		 *
		 * @author    Gartes
		 * @since     3.8
		 * @copyright 11.11.18
		 * @return array
		 */
		public static function prepareCounterpartyFields ( $cart, $order, $subFormShipment, $method )
		{
			
			# Использование единого поля ФИО
			if ( !$method->UseFioField )
			{
				$retArr = [
					'FirstName' => $cart->BT[ $method->Recipient_FirstName_fild ],
					'LastName'  => $cart->BT[ $method->Recipient_LastName_fild ],
					// 'MiddleName' => $cart->BT[$method->Recipient_MiddleName_fild],
				];
			}
			else
			{
				
				$arrData = explode( " ", $cart->BT[ $method->Recipient_LastName_fild ] );
				
				$retArr = [
					'LastName'   => $arrData[ 0 ],
					'FirstName'  => ( isset( $arrData[ 1 ] ) ? $arrData[ 1 ] : '' ),
					'MiddleName' => ( isset( $arrData[ 2 ] ) ? $arrData[ 2 ] : '' ),
				];
				
			}#END IF
			
			$retArr[ 'CounterpartyProperty' ] = 'Recipient';
			$retArr[ 'Phone' ]                = preg_replace( "/[^0-9]/", '', $cart->BT[ $method->Recipient_Phone_fild ] );
			$retArr[ 'Email' ]                = $cart->BT[ $method->Recipient_Email_fild ];
			$retArr[ 'CityRef' ]              = $subFormShipment[ 'nova_pochta' ][ 'CityRecipient' ];
			
			return $retArr;
		}#END FN
		
		/**
		 * Создание SUB формы плагина доставки
		 * @param $plugin
		 * @param $selectedPlugin
		 * @param $openSettingShipmentMethod
		 *
		 * @return string
		 * @throws Exception
		 * @author    Gartes
		 * @since     3.8
		 * @copyright 11.11.18
		 */
		public static function addPluginHtml ( $plugin, $selectedPlugin , $openSettingShipmentMethod  )
		{
			
			
			
			$app = JFactory::getApplication();
			$session = JFactory::getSession();
			$doc = JFactory::getDocument();
			$template = $app->getTemplate();
			
			$doc->addStyleSheet( \JURI::root().'plugins/vmshipment/nova_pochta/nova_pochta/assets/css/'.'novaPoshtaMethodCart.css' /* , array('version' => 'auto')*/ );
			
			
			$doc->addStyleSheet( '//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css');
			
			if ( JVersion::RELEASE <= 3.6 ){
				$doc->addScript( 'https://code.jquery.com/ui/1.12.1/jquery-ui.js', "text/javascript",  1  );
				$doc->addScript( JURI::root() . 'plugins/vmshipment/nova_pochta/nova_pochta/assets/js/' . 'novaPoshtaCore.js', "text/javascript" ,1  );
				$doc->addScript( JURI::root() . 'plugins/vmshipment/nova_pochta/nova_pochta/assets/js/' . 'novaPoshtaMethodCart.js', "text/javascript" ,1  );
			}else
			{
				$doc->addScript( 'https://code.jquery.com/ui/1.12.1/jquery-ui.js', [], [ 'defer' => 1 ] );
				$doc->addScript( \JURI::root() . 'plugins/vmshipment/nova_pochta/nova_pochta/assets/js/' . 'novaPoshtaCore.js', [ 'version' => 'auto' ], [ 'defer' => 1 ] );
				$doc->addScript( \JURI::root() . 'plugins/vmshipment/nova_pochta/nova_pochta/assets/js/' . 'novaPoshtaMethodCart.js', [ 'version' => 'auto' ], [ 'defer' => 1 ] );
			}#END IF
			vmJsApi::addJScript('chosen.jquery.min',false,false);
			vmJsApi::css('chosen');
			
			$doc->addScriptDeclaration('
				"undefined"===typeof NP_MethodCart&&(NP_MethodCart={});
				NP_MethodCart.Setting={
					method_id : '.$plugin->virtuemart_shipmentmethod_id.'
				}
			');
			
			$paramNovaPochta = array(
				'virtuemart_shipmentmethod_id' => $plugin->virtuemart_shipmentmethod_id ,
				'keyAPI' => $plugin->keyAPI ,
			
			);
			$app->input->set('_plgNovaPochta' , $paramNovaPochta  );
			
			$layout      = new JLayoutFile('cart', null ,array('debug' =>  false ) );
			$layout->addIncludePath( JPATH_PLUGINS . '/vmshipment/nova_pochta/nova_pochta/layouts' );
			$layout->addIncludePath( JPATH_THEMES . '/' . $template . '/html/layouts/com_virtuemart/vmshipment/plg_nova_pochta/' );
			$layout->addIncludePath( JPATH_THEMES . '/' . $template . '/html/plg_nova_pochta/' );
			
			
			JForm::addFieldPath(JPATH_PLUGINS.DS.'/plugins/vmshipment/nova_pochta/elements');
			$form = \JForm::getInstance( 'novaPochtaCart', JPATH_PLUGINS.DS.'/vmshipment/nova_pochta/nova_pochta/forms/formCart.xml');
			
			
			
			# Получть данные из сессии
			$cartData = $session->get( _SHIP_NAME,  self::$cartDataNovaPoshta  ) ;
			
			
			/*echo'<pre>';print_r( self::$cartDataNovaPoshta );echo'</pre>'.__FILE__.' '.__LINE__;
			echo'<pre>';print_r( $cartData );echo'</pre>'.__FILE__.' '.__LINE__;*/
			# FIS Для полкй адреса
			$cartData['cartData']['nova_pochta'] = self::_prepareAdressArr($cartData['cartData']['nova_pochta']);
			
			
			
			
			
			
			
			
			
			
			
			
			
			$form->bind( array ( $cartData['cartData'] ) );
			
			
			$data_layout = [
				'form'         => $form->renderFieldset( 'Recipient_fieldset' ),
				'form_Address' => $form->renderFieldset( 'Recipient_Address_fieldset' ),
				'openForm'     => $openSettingShipmentMethod,
				'css_class_blk' => $plugin->css_class_blk ,
				'method_id' => $plugin->virtuemart_shipmentmethod_id ,
			];
			$html   = $layout->render($data_layout); // $displayData
			return $html ;
		}#END FN
		
		/**
		 * FIX - для пердачи данных в форму адрес доставки к двери
		 * @param $arrAdr
		 *
		 * @return mixed
		 * @author    Gartes
		 * @since     3.8
		 * @copyright 11.11.18
		 */
		public static function _prepareAdressArr ($arrAdr){
			
		 
			$res = $arrAdr ;
			foreach ($arrAdr['Address'] as $k => $val){
				$res['Address]['.$k] = $val;
			}#END FOREACH
			return $res ;
		}#END FN
	
	
	}#END CLASS


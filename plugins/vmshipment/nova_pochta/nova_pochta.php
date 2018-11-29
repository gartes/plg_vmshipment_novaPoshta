<?php
	
	defined( '_JEXEC' ) or die( 'Restricted access' );
	/**
	 * Название плагина доставки
	 */
	define('_SHIP_NAME', 'nova_pochta');
	
	if ( !class_exists( 'vmPSPlugin' ) ) require( JPATH_VM_PLUGINS . DS . 'vmpsplugin.php' );
	
	
	/**
	 * Class plgVmShipmentNova_pochta
	 */
	class plgVmShipmentNova_pochta extends vmPSPlugin
	{
		
		/**
		 * plgVmShipmentNova_pochta constructor.
		 *
		 * @param $subject
		 * @param $config
		 *
		 * @throws Exception
		 */
		function __construct ( & $subject, $config )
		{
			//dumpMessage('__construct');
			parent::__construct( $subject, $config );
			$this->_loggable   = true;
			$this->_tablepkey  = 'id';
			$this->_tableId    = 'id';
			$this->tableFields = array_keys( $this->getTableSQLFields() );
			$varsToPush        = $this->getVarsToPush();
			$this->setConfigParameterable( $this->_configTableFieldName, $varsToPush );
			
			$app    = JFactory::getApplication();
			$option = $app->input->get( 'option', false, 'WORD' );// com_virtuemart
			$view   = $app->input->get( 'view', null, 'WORD' ); // shipmentmethod
			$task   = $app->input->get( 'task', null, 'WORD' ); // edit
			$cid    = $app->input->get( 'cid', false, 'ARRAY' ); // 5
			
			$method = $this->getVmPluginMethod( $cid[ 0 ] );
			$app = \JFactory::getApplication() ;
			$app->input->set('_nova_pohsta' , ['keyAPI'=>$method->keyAPI ]) ;
			
			
			if ( $app->isAdmin() && $this->_name == _SHIP_NAME && $option == 'com_virtuemart' && $view == 'shipmentmethod' && $task == 'edit' )
			{
				#Добавить скрипты для страницы VM - Shipment method - Edit
				if ( !class_exists( 'addJsShipmentMethodEdit' ) )
					require( JPATH_PLUGINS.DS.'vmshipment'.DS.$this->_name.DS.$this->_name.DS.'helpers'.DS.'shipmentMethodEdit.php' );
				
				 
				
				helpersShipmentMethodEdit::addJsShipmentMethodEdit( $method );
			}#END IF
			
		}#END FN
		
		/**
		 * Создает в базе данных таблицу если она еще не существует
		 *
		 * @return string
		 * @author    Gartes
		 * @since     3.8
		 * @copyright 03.11.18
		 */
		public function getVmPluginCreateTableSQL ()
		{
			return $this->createTableSQL( 'Shipment Nova Poshta' );
		}#END FN
		
		//
		/**
		 * Создать массив с названиями столбцов и параметрами
		 * @return array
		 * @author    Gartes
		 * @since     3.8
		 * @copyright 03.11.18
		 */
		function getTableSQLFields ()
		{
			
			
			
			$SQLfields = [
				'id'                           => 'int(1) UNSIGNED NOT NULL AUTO_INCREMENT',
				'user_id'                      => 'int(1) UNSIGNED NOT NULL',
				'virtuemart_order_id'          => 'int(11) UNSIGNED',
				'order_number'                 => 'char(32)',
				'virtuemart_shipmentmethod_id' => 'mediumint(1) UNSIGNED',
				
				
				'FirstName'  => 'char(18)',
				'LastName'   => 'varchar(18)',
				'MiddleName' => 'char(18)',
				// 'Phone'      => 'varchar(18)   ',
				
				
				
				
				'PayerType'     => 'varchar(9)', // Тип плательщика
				'PaymentMethod' => 'varchar(7)', // Форма оплаты
				'DateTime'      => 'date NOT NULL DEFAULT \'0000-00-00\'',  // Дата отправки в формате дд.мм.гггг
				'CargoType'     => 'varchar(11) NOT NULL DEFAULT \'Cargo\'',  // Тип груза
				
				
				
				
				// min - 0,1 Вес фактический, кг
				'Weight'         => 'decimal(5,2)',
				# Объем общий, м.куб (min - 0.0004), обязательно для заполнения, если не указаны значения OptionsSeat
				'VolumeWeight' => 'decimal(7,4)',
				# Общий объем м³
				'VolumeGeneral' => 'decimal(12,8)',
				# количество мест отправления
				'SeatsAmount' => 'int(1) NOT NULL' ,
				# Параметры репет табле
				'VolumeGeneralParamsList'=>'text',
				
				
				
				
				// Технология доставки
				'ServiceType'          => 'varchar(36)',
				
				// Текстовое поле, вводиться для доп. описания
				'Description'          => 'varchar(50)',
				// Целое число, объявленная стоимость
				// (если объявленная стоимость не указана,
				// API автоматически подставит минимальную объявленную
				// цену - 300.00
				'Cost'          => 'int(1)',
				// Идентификатор города получателя
				'CityRecipient'        => 'char(36)',
				// Идентификатор получателя
				'Recipient'            => 'char(36)',
				// Идентификатор адреса получателя
				'RecipientAddress'     => 'char(36)',
				// Идентификатор контактного лица получателя
				'ContactRecipient'     => 'char(36)',
				
				// телефон получателя в формате: +380660000000, 80660000000, 0660000000
				'RecipientsPhone'     => 'varchar(13)',
				
				# Ref некладной
				'IntDocNumber'          => 'char(36)',
				#Прогноз даты доставки:
				'EstimatedDeliveryDate'      => 'date NOT NULL DEFAULT \'0000-00-00\'',
				
				
				'InternetDocument'     => 'text',       # JSON - Текстовые поля даннах экспресс-накладной
				'Address'              => 'text',       # JSON - Текстовые поля даннах доставки к двери
				'VolumeGeneralParams'  => 'text',       # JSON - Параметры груза Ширина х Длина х Высота
				'BackwardDeliveryData' => 'text',       # JSON - Обратная доставка денег
				'log'                  => 'text',       # JSON - Логи объекта
				
				// 'RefStreet'          => 'char(36)',
				
				
				
				'shipment_name' => 'varchar(300)',
				
				//'variant_deliv' => 'varchar(20)',
				
//				'ship_syti_nova_poshta'  => 'varchar(36)',
//				'ship_sklad_nova_poshta' => 'varchar(36)',
				
				
				/*'payer'                    => 'varchar(11)',*/
				/*'delivery_in_out'          => 'decimal(6,2)',*/
				/*'redelivery_payment_payer' => 'varchar(11)',*/
				
				
				
				
				
				
				
				
				'estimated_delivery_date' => 'date NOT NULL DEFAULT \'0000-00-00\'', //  орентировачная дата доставки
				// 'state_id'                => 'char(2)', //  id статуса накладной
				// 'state_name'              => 'varchar(50)',// описание статуса
				'deceipt_date_time'       => 'date NOT NULL DEFAULT \'0000-00-00\'',//Дата, когда груз забрал получатель
				/*'reason_description'      => 'varchar(150)',// Причина отказа*/
				
				
				// 'date_otpravki'        => 'date NOT NULL DEFAULT \'0000-00-00\'',
				
				
				'shipment_weight_unit' => 'char(3) DEFAULT \'KG\'',
				/*'col_mest'             => 'varchar(2)',*/
				'shipment_cost'        => 'decimal(10,2)',
				'shipment_package_fee' => 'decimal(10,2)',
				'tax_id'               => 'smallint(1)',
			];
			
			return $SQLfields;
		}#END FN
		/**
		 * Метод сработает после подтверждения заказа покупателем
		 * Показывает данные доставки
		 *
		 * @param integer $order_number The order Number
		 *
		 * @return mixed Null for shipments that aren't active, text (HTML) otherwise
		 */
		public function plgVmOnShowOrderFEShipment ( $virtuemart_order_id, $virtuemart_shipmentmethod_id, &$shipment_name )
		{
			
			
			$app = \JFactory::getApplication() ; 
			
			
			
		 
			
			/*echo'<pre>';print_r(  'plgVmOnShowOrderFEShipment' );echo'</pre>'.__FILE__.' '.__LINE__;
		    echo'<pre>';print_r( $shipment_name );echo'</pre>'.__FILE__.' '.__LINE__;*/
			//dumpMessage(' function plgVmOnShowOrderFEShipment  ');
			$this->onShowOrderFE( $virtuemart_order_id, $virtuemart_shipmentmethod_id, $shipment_name );
			
			// JRequest::setVar('html','HJJJJJJJJJJJJJJJJJJJJJJJJJJJJ');
			
			// $shipment_name = "<div>XZXZZXZXZXZXZXZXZXZX</div>";
			
		}#END FN
		/**
		 * Создает html разметку в названии способа доставки
		 * Добавляет к названию способа доставки
		 * Город доставки - Адрес склада
		 *
		 *
		 * @param $shipment_name
		 * @param $citi_of_sender
		 * @param $warenhouseClient
		 *
		 * @return string
		 *0.00000000
		 * @author    Gartes
		 * @since     3.8
		 * @copyright 03.11.18
		 */
		function getHtmlNameVarianDelivery ( $shipment_name, $citi_of_sender, $warenhouseClient )
		{
			
			
			//dumpMessage('function getHtmlNameVarianDelivery   ');
			if ( !class_exists( 'np_param' ) )
				require( JPATH_ROOT . DS . 'plugins' . DS . 'vmshipment' . DS . 'nova_pochta' . DS . 'nova_pochta' . DS . 'np_param.php' );
			$np_param = new np_param();
			//$warenhouse = $np_param->warenhouse($citi_of_sender,'','ajax');
			$htmlName = $np_param->getFullShipNameHtml( $shipment_name, $citi_of_sender, $warenhouse[ $warenhouseClient ] );
			
			return $htmlName;
		}#END FN
		
		/**
		 * Метод сработает после создания заказа покупателя
		 * отдает данные для заноса в таблицу метода доставки
		 *
		 * @param VirtueMartCart $cart
		 * @param                $order
		 *
		 * @return bool
		 * @author    Gartes
		 * @since     3.8
		 * @copyright 03.11.18
		 * @throws Exception
		 */
		function plgVmConfirmedOrder ( VirtueMartCart $cart, $order )
		{
			
			# Загрузить DB - Обьект выбранного метода доставки
			if ( !( $method = $this->getVmPluginMethod( $order[ 'details' ][ 'BT' ]->virtuemart_shipmentmethod_id ) ) )
			{
				return null; // Another method was selected, do nothing
			}
			
			# Проверка соответствия метода и плагина
			# Отбросить все другие обращения
			if ( !$this->selectedThisElement( $method->shipment_element ) )
			{
				return false;
			}
			
			
			
			$app = \JFactory::getApplication() ;
			$api        = xzlib\xzlib_app::_get( 'api',  [] );
			$novaPoshta = $api->getApi( 'nova_poshta', [] );
			$novaPoshta->setApiKey ( $method->keyAPI ) ;
			
			$modelCounterparty = $novaPoshta->getModel( 'Counterparty' );
			
			
			# Подключить логер
			if ( !class_exists( 'shipmentMethodLog' ) ) require( JPATH_PLUGINS.DS.'vmshipment'.DS.$this->_name.DS.$this->_name.DS.'helpers'.DS.'shipmentMethodLog.php' );
			$MethodLog = shipmentMethodLog::instance();
			
			# Данные SUB Формы доставки
			$subFormShipment = $app->input->getArray(['nova_pochta'=>'ARRAY']) ;
			
			# Создать Контрагента  получателя
			if ( !class_exists( 'helpersShipmentMethodCart' ) )
				require( JPATH_PLUGINS.DS.'vmshipment'.DS.$this->_name.DS.$this->_name.DS.'helpers'.DS.'shipmentMethodCart.php' );
			
			# Подготовить поля конрагента
			$data = helpersShipmentMethodCart::prepareCounterpartyFields( $cart, $order , $subFormShipment , $method  );
			
			
			
			
			$values[ 'FirstName' ]  = $data['FirstName'];
			$values[ 'LastName' ]   = $data['LastName'];
			// $values[ 'MiddleName' ] = null /*$cart->BT[ $method->Recipient_MiddleName_fild ]*/;
			### Телефон получателя в формате: +380660000000, 80660000000, 0660000000
			$values[ 'RecipientsPhone' ] = $data['Phone'] ;
			
			
			# Создать контрагента получателя
			$CounterpartyData = $modelCounterparty->save( $data  );
			if (!$CounterpartyData->success) {
				$MethodLog::addLog($CounterpartyData);
				// $err = $MethodLog::getLogErr();
			}else{
				
				// Идентификатор получателя
				$values[ 'Recipient' ] = $CounterpartyData->data[0]->Ref ;
				// Идентификатор контактного лица получателя
				$values[ 'ContactRecipient' ] = $CounterpartyData->data[0]->ContactPerson->data[0]->Ref ;
				
				# Создать адрес контрагента
				$RecipientAddress = helpersShipmentMethodCart::prepareRecipientAddress( $method , $subFormShipment , $CounterpartyData    );
				$subFormShipment['nova_pochta']['Address']['Description'] = $RecipientAddress['Description'];
				$subFormShipment['nova_pochta']['Address']['RefStreet'] = $subFormShipment['nova_pochta']['RefStreet'];
				
				//  Идентификатор адреса получателя
				// $values[ 'RecipientAddress' ] = $subFormShipment[ 'nova_pochta' ][ 'RecipientAddress' ];
				$values[ 'RecipientAddress' ] = $RecipientAddress['Ref'];
				
				
			}#END IF
			
			
			
			
			
			
			
			$values[ 'user_id' ]                      = $order[ 'details' ][ 'BT' ]->virtuemart_user_id;
			$values[ 'virtuemart_order_id' ]          = $order[ 'details' ][ 'BT' ]->virtuemart_order_id;
			$values[ 'order_number' ]                 = $order[ 'details' ][ 'BT' ]->order_number;
			$values[ 'virtuemart_shipmentmethod_id' ] = $order[ 'details' ][ 'BT' ]->virtuemart_shipmentmethod_id;
			
			
			
			
		     
			
			# Тип плательщика за доставку
			$values[ 'PayerType' ] = helpersShipmentMethodCart::preparePayerType( $order, $method, $subFormShipment );
			# Форма оплаты
			$values[ 'PaymentMethod' ] = $method->PaymentMethod;
			# Дата отправки в формате дд.мм.гггг
			// $values[ 'DateTime' ]  = '00-00-0000';
			# Тип груза
			$values[ 'CargoType' ] = $method->CargoType;
			
			
			
			//$volumeGeneralParams = helpersShipmentMethodCart::getVolumeGeneralParams($order,$method , $subFormShipment );
			
			# Параметры обьема
			// $values[ 'VolumeGeneralParams' ] = json_encode( $volumeGeneralParams['VolumeGeneralParams'] );
			
			
			// min - 0,1 Вес фактический, кго
			$values[ 'Weight' ] = $method->DEF_Weight;
			# Целое число, количество мест отправления
			$values[ 'SeatsAmount' ] = 1  ;
			# Объем общий, м.куб (min - 0.0004), обязательно для заполнения, если не указаны значения OptionsSeat
			$values[ 'VolumeGeneral' ] = ''  ;
			
			
			// способ доставки на склад - к двери
			$values[ 'ServiceType' ] = $subFormShipment[ 'nova_pochta' ][ 'ServiceType' ];
			
			// Текстовое поле, вводиться для доп. описания
			$values[ 'Description' ] = ''  ;
			//Целое число, объявленная стоимость
			// (если объявленная стоимость не указана, API автоматически подставит
			// минимальную объявленную цену - 300.00
			$values[ 'Cost' ] = helpersShipmentMethodCart::prepareCost( $order, $method, $subFormShipment );
			# город получателя
			$values[ 'CityRecipient' ] = $subFormShipment[ 'nova_pochta' ][ 'CityRecipient' ];
			
			
			/*
			# Ref некладной
			$values[ 'IntDocNumber' ] =    $subFormShipment['nova_pochta']['InternetDocument']['IntDocNumber'] ;
			#Прогноз даты доставки:
			$values[ 'EstimatedDeliveryDate' ] = $subFormShipment['nova_pochta']['InternetDocument']['EstimatedDeliveryDate'] ;
			# Информация о Экспресс-накладной
			$values[ 'InternetDocument' ] =  json_encode( $subFormShipment['nova_pochta']['InternetDocument']);
			 */
			
			
			
			# Информация о адресе
			$values[ 'Address' ] =  json_encode( $subFormShipment['nova_pochta']['Address'] );
			
			# Информация об обратная доставка
			$values[ 'BackwardDeliveryData' ] = helpersShipmentMethodCart::prepareBackwardDeliver(  $order , $method  );
			
			// $MethodLog = shipmentMethodLog::instance();
			$values['log'] = json_encode( shipmentMethodLog::getLog()  );
			
			
//			echo'<pre>';print_r( $method );echo'</pre>'.__FILE__.' '.__LINE__;
//			echo'<pre>';print_r( $values );echo'</pre>'.__FILE__.' '.__LINE__;
//			die(__FILE__ .' Lines '. __LINE__ );
			
			
			/*
			echo'<pre>';print_r( $subFormShipment );echo'</pre>'.__FILE__.' '.__LINE__;
			echo'<pre>';print_r( $values );echo'</pre>'.__FILE__.' '.__LINE__;
			die(__FILE__ .' Lines '. __LINE__ );*/
			
			$this->storePSPluginInternalData( $values );
			return true;
			
			
			
			
			
			
			/*
			// название способа доставки -- Новая почта
			$shipment_name = $this->renderPluginName( $method );
			
			
			
			
			// Вариант доставки склад или к двери
			if ( $sesData[ 'variant_deliv' ] == 1 && $sesData[ 'rcpt_street_name' ] )
			{
				$values[ 'ship_sklad_nova_poshta' ] = $sesData[ 'rcpt_street_name' ];
				$values[ 'variant_deliv' ]          = 1;
				$adres                              = $sesData[ 'rcpt_street_name' ];
				$htmlName                           = $np_param->getFullShipNameHtml( $shipment_name, $values[ 'ship_syti_nova_poshta' ], $adres, $values[ 'variant_deliv' ] );
			}
			else
			{
				$values[ 'variant_deliv' ] = $paramShip[ 'variant_delivery' ];
				$htmlName                  = $this->getHtmlNameVarianDelivery( $shipment_name, $values[ 'ship_syti_nova_poshta' ], $values[ 'ship_sklad_nova_poshta' ] );
			}
			//  полное название даоставки
			$values[ 'shipment_name' ] = $htmlName;
			//Вес заказа
			$weight = $this->getOrderWeight( $cart, $method->weight_unit );
			if ( $weight < $paramShip[ 'weight_min_for_np' ] )
			{
				$weight = $paramShip[ 'weight_min_for_np' ];
			}
			$values[ 'order_weight' ] = $weight;
			
			// объем заказа в м3
			$values[ 'volume_general' ] = $tc = $sesData[ 'volume_general' ];
			
			*/
		 }#END FN
		
		
		/**
		 * Создание HTML разметки для названия способа доставки
		 *
		 * @param        $method
		 * @param string $where
		 *
		 * @return mixed
		 * @author    Gartes
		 * @since     3.8
		 * @copyright 19.11.18
		 */
		function renderPluginName ($method , $where = 'checkout') {
			return parent::renderPluginName($method);
		}#END FN
		
		
		
		/**
		 * ADMIN -
		 * view = orders
		 * task = edit
		 * Сохранить данные  Новой почты после редактирования
		 * Событие Триггера
		 * @param $values
		 *
		 * @return bool|int
		 * @throws Exception
		 * @author    Gartes
		 * @since     3.8
		 * @copyright 14.11.18
		 */
		public function plgVmNpOnUpdateOrderBEShipment ( $values )
		{
			$app = \JFactory::getApplication();
			if ( !$app->input->get( 'XzlibRequest', false, 'BOOL' ) ) return 1;
			
			$values['id'] = 4 ;
			
			//$this->storePluginInternalData( $values ,'id' , 15 , true);
			
			 $this->storePSPluginInternalData( $values );
			return true;
		}#END FN
		
		/**
		 * Отображает информацию про перевозчика на странице заказа в админ панели
		 * NOTE, this plugin should NOT be used to display form fields, since it's called outside
		 * a form! Use plgVmOnUpdateOrderBE() instead!
		 *
		 * @param $virtuemart_order_id
		 * @param $virtuemart_shipmentmethod_id
		 *
		 * @return null|string
		 * @author    Gartes
		 * @since     3.8
		 * @copyright 03.11.18
		 * @throws Exception
		 */
		public function plgVmOnShowOrderBEShipment ( $virtuemart_order_id, $virtuemart_shipmentmethod_id )
		{
			//dumpMessage('  public function plgVmOnShowOrderBEShipment  ');
			
			if ( !$this->selectedThisByMethodId( $virtuemart_shipmentmethod_id ) )
			{
				return null;
			}
			$html = $this->getOrderShipmentHtml( $virtuemart_order_id );
			
			return $html;
		}#END FN
		
		
		/**
		 * Получает данные из таблицы модуля доставки
		 * Создает html разметку плагина в админ панели в заказе
		 * возращает <table> DATA </table>
		 *
		 * @param $virtuemart_order_id
		 *
		 * @return string
		 * @author    Gartes
		 * @since     3.8
		 * @copyright 03.11.18
		 * @throws Exception
		 */
		function getOrderShipmentHtml ( $virtuemart_order_id )
		{
			$db = JFactory::getDBO();
			$q  = 'SELECT * , DATE_FORMAT(DateTime,"%d.%m.%Y") AS DateTime FROM `' . $this->_tablename . '` '
				. 'WHERE `virtuemart_order_id` = ' . $virtuemart_order_id;
			$db->setQuery( $q );
			if ( !( $shipinfo = $db->loadObject() ) )
			{
				vmWarn( 500, $q . " " . $db->getErrorMsg() );
				
				return '';
			}
			
			# Создать HTML разметку в бланке редактирования заказ - ADMIN
			if ( !class_exists( 'helpersShipmentMethodOrderEdit' ) )
				require( JPATH_PLUGINS . DS . 'vmshipment' . DS . $this->_name . DS . $this->_name . DS . 'helpers' . DS . 'shipmentMethodOrderEdit.php' );
			
			return helpersShipmentMethodOrderEdit::getOrderEditHtmlForm( $shipinfo );
		}#END FN
		
		/**
		 * Расчет стоимости доставки. 
		 * Стоимость будет включена в сумму заказа
		 * 
		 * @param $method
		 *
		 * @author    Gartes
		 * @since     3.8
		 * @copyright 09.11.18
		 * @return float
		 */
		function getCosts ( VirtueMartCart $cart, $method, $cart_prices )
		{
			return 0.0;
			
			
			/*if ($method->free_shipment && $cart_prices['salesPrice'] >= $method->free_shipment) {
				return 0.0;
			} else {
				return $method->shipment_cost + $method->package_fee;
			}*/
		}#END FN
		
		function convert ( &$method )
		{
			// echo "<br />convert <br />";
			//$method->weight_start = (float) $method->weight_start;
			//$method->weight_stop = (float) $method->weight_stop;
			$method->orderamount_stop = (float) $method->orderamount_stop;
			$method->nbproducts_start = (int) $method->nbproducts_start;
			$method->nbproducts_stop  = (int) $method->nbproducts_stop;
			$method->free_shipment    = (float) $method->free_shipment;
			
			 
			
		}#END FN
		
		// Проверка на минимальный вес
		// Если Минимальный вес установлен и он больше чем вес заказа
		// то возвращаем минимальный вес или вернем вес заказа
		private function _weightCond ( $orderWeight, $method )
		{
			if ( $method->weight_min_for_np and $method->weight_min_for_np > $orderWeight )
			{
				$weight_order = $method->weight_min_for_np;
			}
			else
			{
				$weight_order = $orderWeight;
			}
			
			return $weight_order;
		}#END FN
		
		// подсчет количества товаров для доставке
		private function _nbproductsCond ( $cart, $method )
		{
			//echo "<br />_nbproductsCond <br />";
			$nbproducts = 0;
			foreach ( $cart->products as $product )
			{
				$nbproducts += $product->quantity;
			}
			if ( !isset( $method->nbproducts_start ) AND !isset( $method->nbproducts_stop ) )
			{
				return true;
			}
			if ( $nbproducts )
			{
				$nbproducts_cond = ( $nbproducts >= $method->nbproducts_start AND $nbproducts <= $method->nbproducts_stop
					OR
					( $method->nbproducts_start <= $nbproducts AND ( $method->nbproducts_stop == 0 ) ) );
			}
			else
			{
				$nbproducts_cond = true;
			}
			
			return $nbproducts_cond;
		}#END FN
		
		// подсчет суммы заказа для минимальной максимальной суммы отправки
		private function _orderamountCond ( $cart_prices, $method )
		{
			//echo "<br />_orderamountCond <br />";
			$orderamount = 0;
			if ( !isset( $method->orderamount_start ) AND !isset( $method->orderamount_stop ) )
			{
				return true;
			}
			if ( $cart_prices[ 'salesPrice' ] )
			{
				$orderamount_cond = ( $cart_prices[ 'salesPrice' ] >= $method->orderamount_start AND $cart_prices[ 'salesPrice' ] <= $method->orderamount_stop OR
					( $method->orderamount_start <= $cart_prices[ 'salesPrice' ] AND ( $method->orderamount_stop == 0 ) ) );
			}
			else
			{
				$orderamount_cond = true;
			}
			
			return $orderamount_cond;
		}#END FN
		
		/**
		 * Создание таблицы для этого плагина, если он еще не существует.
		 * Эта функция проверяет, является ли плагин называется активным.
		 * Если да, это вызывает стандартный метод для создания таблиц
		 *
		 * Create the table for this plugin if it does not yet exist.
		 * This functions checks if the called plugin is active one.
		 * When yes it is calling the standard method to create the tables
		 *
		 *
		 * @param $jplugin_id
		 *
		 * @return bool|mixed
		 * @author    Gartes
		 * @since     3.8
		 * @copyright 03.11.18
		 */
		function plgVmOnStoreInstallShipmentPluginTable ( $jplugin_id )
		{
			
			//dumpMessage(' function plgVmOnStoreInstallShipmentPluginTable  ');
			return $this->onStoreInstallPluginTable( $jplugin_id );
		}#END FN
		
		/**
		 * Это событие происходит после метод доставки был выбран и потом изменен.
		 * Он может быть использован для хранения Дополнительная информация платеж в корзину.
		 *
		 * This event is fired after the shipment method has been selected. It can be used to store
		 * additional payment info in the cart.
		 *
		 * @param                $psType
		 * @param VirtueMartCart $cart
		 *
		 * @return null
		 * @author    Gartes
		 * @since     3.8
		 * @copyright 03.11.18
		 */
		public function plgVmOnSelectCheck ( $psType, VirtueMartCart $cart )
		{
			
			
			//dumpMessage(' function plgVmOnSelectCheck  ');
			return $this->OnSelectCheck( $psType, $cart );
		}#END FN
		
		/**
		 * Событие при сохранении способа доставки
		 * Определяет выбранный способ доставки
		 * Загружает выбраннвй способ доставки в корзину. если выбранный способ вернет true
		 *
		 *
		 * @param VirtueMartCart $cart
		 *
		 * @return null
		 * @author    Gartes
		 * @since     3.8
		 * @copyright 03.11.18
		 * @throws Exception
		 */
		public function plgVmOnSelectCheckShipment ( VirtueMartCart &$cart )
		{
			# сохранение данных о доставке в сессию
			$app = \JFactory::getApplication() ;
			$dataShipment = $app->input->getArray(['nova_pochta'=>'ARRAY']);
			$session = JFactory::getSession();
			$session->set( _SHIP_NAME, array ('cartData' => $dataShipment ) );
			
			$res = $this->OnSelectCheck( $cart ); # TRUE
			return $res ;
		}#END FN
		
		
		/**
		 * Проверка на соответствие способа доставки параметрам заказа
		 * return false - Способ доставки не отображается
		 *
		 * @param VirtueMartCart $cart
		 * @param int            $method
		 * @param array          $cart_prices
		 *
		 * @return bool
		 * @author    Gartes
		 * @since     3.8
		 * @copyright 09.11.18
		 */
		protected function checkConditions ($cart, $method, $cart_prices) {
			
			vmAdminInfo ('vmPsPlugin function checkConditions not overriden, gives always back FALSE');
			return true;
		}
		
		
		/**
		 * Срабатывает при Выборе доставки в корзине покупателя
		 * Вызывается в файле view.html.php
		 *
		 * plgVmDisplayListFE
		 * This event is fired to display the pluginmethods in the cart (edit shipment/payment) for exampel
		 *
		 * @param VirtueMartCart $cart
		 * @param int            $selected - ID выбранного способо доставки
		 * @param Array          $htmlIn   - мыссив с html разметкой способов доставки
		 *
		 * @author    Gartes
		 * @since     3.8
		 * @copyright 03.11.18
		 */
		public function plgVmDisplayListFEShipment ( VirtueMartCart $cart, $selected = 0, &$htmlIn )
		{
			$this->displayListFE( $cart, $selected, $htmlIn );
		}#END FN
		
		/**
		 * Создать HTML разметку для способа доставки
		 *
		 * @param $plugin
		 * @param $selectedPlugin
		 * @param $pluginSalesPrice
		 *
		 * @return string
		 * @author    Gartes
		 * @since     3.8
		 * @copyright 09.11.18
		 * @throws Exception
		 */
		protected function getPluginHtml ($plugin, $selectedPlugin, $pluginSalesPrice) {
			
			
			
			if ( !class_exists( 'helpersShipmentMethodCart' ) )
				require( JPATH_PLUGINS.DS.'vmshipment'.DS.$this->_name.DS.$this->_name.DS.'helpers'.DS.'shipmentMethodCart.php' );
			
			$app = \JFactory::getApplication() ;
			$app->input->set('_nova_pohsta' , ['keyAPI'=>$plugin->keyAPI ]) ;
			
			 
			
			$pluginmethod_id = $this->_idName;
			$pluginName = $this->_psType . '_name';
			
			
			$checked = '';
			$openSettingShipmentMethod = false ;
			if ($selectedPlugin == $plugin->$pluginmethod_id) {
				$checked = 'checked="checked"';
				$openSettingShipmentMethod = true ;
			}
			
			
			$_plgNovaPochta = $app->input->getArray(
				array('_plgNovaPochta' =>
					      array(
						      'automaticSelectedShipment' => 'BOOL'
					      )
				)
			);
			
			if ( $_plgNovaPochta['_plgNovaPochta']['automaticSelectedShipment'] ){
				$checked = 'checked="checked"';
				$openSettingShipmentMethod = true ;
			}#END IF
			
			
			
			if (!class_exists ('CurrencyDisplay')) {
				require(VMPATH_ADMIN . DS . 'helpers' . DS . 'currencydisplay.php');
			}
			$currency = CurrencyDisplay::getInstance ();
			$costDisplay = "";
			if ($pluginSalesPrice) {
				$costDisplay = $currency->priceDisplay( $pluginSalesPrice );
				$t = vmText::_( 'COM_VIRTUEMART_PLUGIN_COST_DISPLAY' );
				if(strpos($t,'/')!==FALSE){
					list($discount, $fee) = explode( '/', vmText::_( 'COM_VIRTUEMART_PLUGIN_COST_DISPLAY' ) );
					if($pluginSalesPrice>=0) {
						$costDisplay = '<span class="'.$this->_type.'_cost fee"> ('.$fee.' +'.$costDisplay.")</span>";
					} else if($pluginSalesPrice<0) {
						$costDisplay = '<span class="'.$this->_type.'_cost discount"> ('.$discount.' -'.$costDisplay.")</span>";
					}
				} else {
					$costDisplay = '<span class="'.$this->_type.'_cost fee"> ('.$t.' +'.$costDisplay.")</span>";
				}
			}
			$dynUpdate='';
			
			
			
			
			if( VmConfig::get('oncheckout_ajax',false)) {
				//$url = JRoute::_('index.php?option=com_virtuemart&view=cart&task=updatecart&'. $this->_idName. '='.$plugin->$pluginmethod_id );
				$dynUpdate=' data-dynamic-update="1" ';
			}
			
			
			if ( $_plgNovaPochta['_plgNovaPochta']['automaticSelectedShipment'] ){
				
				$html = '<input type="hidden"'.$dynUpdate.' name="' . $pluginmethod_id . '" id="' . $this->_psType . '_id_' . $plugin->$pluginmethod_id . '"   value="' . $plugin->$pluginmethod_id . '" ' . $checked . ">\n"
					. '<label for="' . $this->_psType . '_id_' . $plugin->$pluginmethod_id . '">'
					. '<span class="' . $this->_type . '">'
					. $plugin->$pluginName . $costDisplay
					. "</span>"
					."</label>\n";
			}else{
				$html = '<input type="radio"'.$dynUpdate.' name="' . $pluginmethod_id . '" id="' . $this->_psType . '_id_' . $plugin->$pluginmethod_id . '"   value="' . $plugin->$pluginmethod_id . '" ' . $checked . ">\n"
					. '<label for="' . $this->_psType . '_id_' . $plugin->$pluginmethod_id . '">'
					. '<span class="' . $this->_type . '">'
					. $plugin->$pluginName . $costDisplay
					. "</span>"
					."</label>\n";
			}#END IF
			
			
			
			
			
			$html .= helpersShipmentMethodCart::addPluginHtml( $plugin, $selectedPlugin , $openSettingShipmentMethod  );
			return $html;
		}#END FN
		
		
		/**
		 * plgVmonSelectedCalculatePrice
		 * Расчет стоимости доствавки для этого способа при оформлении
		 * It is called by the calculator
		 *
		 * @param VirtueMartCart $cart
		 * @param array          $cart_prices
		 * @param                $cart_prices_name
		 *
		 * @return bool|null if the method was not selected, false if the shiiping rate is not valid any more, true otherwise
		 * @author    Gartes
		 * @since     3.8
		 * @copyright 03.11.18
		 */
		public function plgVmonSelectedCalculatePriceShipment ( VirtueMartCart $cart, array &$cart_prices, &$cart_prices_name )
		{
			//dumpMessage(' function plgVmonSelectedCalculatePriceShipment  ');
			return $this->onSelectedCalculatePrice( $cart, $cart_prices, $cart_prices_name );
		}#END FN
		
		/**
		 *
		 *
		 * plgVmOnCheckAutomaticSelected
		 * Checks how many plugins are available. If only one, the user will not have the choice. Enter edit_xxx page
		 * The plugin must check first if it is the correct type
		 *
		 * @param VirtueMartCart $cart
		 * @param array          $cart_prices
		 * @param                $shipCounter - доступно плагинов кроме этого
		 *
		 * @return null
		 * @author    Gartes
		 * @since     3.8
		 * @copyright 10.11.18
		 * @throws Exception
		 */
		function plgVmOnCheckAutomaticSelectedShipment ( VirtueMartCart $cart, array $cart_prices = [], &$shipCounter )
		{
			
			if ( !$shipCounter){
				$app = \JFactory::getApplication() ;
				
				$_plgNovaPochta = $app->input->getArray(array('_plgNovaPochta'=>'ARRAY'));
				$_plgNovaPochta['_plgNovaPochta']['automaticSelectedShipment'] = 1 ;
				$app->input->set('_plgNovaPochta' , $_plgNovaPochta['_plgNovaPochta'] );
				
				
				 
				
				//
				
				
				return 0;
			}
			
			
			//dumpMessage(' function plgVmOnCheckAutomaticSelectedShipment  ');
			// if ( $shipCounter > 1 ) return 0;
			$res = $this->onCheckAutomaticSelected( $cart, $cart_prices, $shipCounter ); // Id shipment method Nova Poshta
			
			
			return $res ;
			
			
			
		}#END FN
		
		/**
		 * This event is fired during the checkout process. It can be used to validate the
		 * method data as entered by the user.
		 * @return boolean True when the data was valid, false otherwise. If the plugin is not activated, it should return null.
		 */
		/*public function plgVmOnCheckoutCheckData($psType, VirtueMartCart $cart) {
			echo "<br />plgVmOnCheckoutCheckData <br />";
			return null;
		}//*/
		
		/**
		 * This method is fired when showing when priting an Order
		 * It displays the the payment method-specific data.
		 * Этот метод срабатывает при показе, когда priting порядке
		 * Это показывает способ оплаты конкретных данных.
		 *
		 * @param integer $_virtuemart_order_id The order ID
		 * @param integer $method_id            method used for this order
		 *
		 * @return mixed Null when for payment methods that were not selected, text (HTML) otherwise
		 */
		function plgVmonShowOrderPrint ( $order_number, $method_id )
		{
			//dumpMessage('  function plgVmonShowOrderPrint  ');
			return $this->onShowOrderPrint( $order_number, $method_id );
		}#END FN
		
		/**
		 * Save updated order data to the method specific table
		 *
		 * @param array $_formData Form data
		 *
		 * @return mixed, True on success, false on failures (the rest of the save-process will be
		 * skipped!), or null when this method is not actived.
		 * @author Oscar van Eijk
		 */
		public function plgVmOnUpdateOrder ( $psType, $_formData )
		{
			//dumpMessage('  function plgVmOnUpdateOrder  ');
			return null;
		}#END FN
		
		/**
		 * Save updated orderline data to the method specific table
		 *
		 * @param array $_formData Form data
		 *
		 * @return mixed, True on success, false on failures (the rest of the save-process will be
		 * skipped!), or null when this method is not actived.
		 */
		public function plgVmOnUpdateOrderLine ( $psType, $_formData )
		{
			//dumpMessage(' function plgVmOnUpdateOrderLine  ');
			return null;
		}#END FN
		
		/**
		 * plgVmOnEditOrderLineBE
		 * This method is fired when editing the order line details in the backend.
		 * It can be used to add line specific package codes
		 *
		 * @param integer $_orderId The order ID
		 * @param integer $_lineId
		 *
		 * @return mixed Null for method that aren't active, text (HTML) otherwise
		 */
		public function plgVmOnEditOrderLineBE ( $psType, $_orderId, $_lineId )
		{
			//dumpMessage(' function plgVmOnEditOrderLineBE  ');
			return null;
		}#END FN
		
		/**
		 * This method is fired when showing the order details in the frontend, for every orderline.
		 * It can be used to display line specific package codes, e.g. with a link to external tracking and
		 * tracing systems
		 * Этот метод срабатывает при показе деталей заказа в интерфейсе для каждого OrderLine.
		 * Это может быть использован для отображения линии специальных кодов пакета, например, со ссылкой на внешних отслеживания и
		 * Системы отслеживания
		 *
		 * @param integer $_orderId The order ID
		 * @param integer $_lineId
		 *
		 * @return mixed Null for method that aren't active, text (HTML) otherwise
		 */
		public function plgVmOnShowOrderLineFE ( $psType, $_orderId, $_lineId )
		{
			//dumpMessage(' function plgVmOnShowOrderLineFE  ');
			return null;
		}#END FN
		
		/**
		 * plgVmOnResponseReceived
		 * This event is fired when the  method returns to the shop after the transaction
		 *  the method itself should send in the URL the parameters needed
		 * NOTE for Plugin developers:
		 *  If the plugin is NOT actually executed (not the selected payment method), this method must return NULL
		 * Это событие происходит, когда метод возвращает в магазин после сделки
		 * Сам метод должен направить в адрес параметры, необходимые
		 * Примечание для разработчиков плагинов:
		 * Если плагин на самом деле не выполняется (не выбранного способа оплаты), этот метод должен вернуть NULL
		 *
		 * @param int  $virtuemart_order_id : should return the virtuemart_order_id
		 * @param text $html                : the html to display
		 *
		 * @return mixed Null when this method was not selected, otherwise the true or false
		 */
		function plgVmOnResponseReceived ( $psType, &$virtuemart_order_id, &$html )
		{
			//dumpMessage(' function plgVmOnResponseReceived  ');
			return null;
		}#END FN
		
		// декларирование параметров способо доставки
		/*function plgVmDeclarePluginParamsShipment($name, $id, &$data) {
			echo "<br />plgVmDeclarePluginParamsShipment <br />";
			$modelOrder = VmModel::getModel ('orders');
			$shipmentModel = VmModel::getModel('Shipmentmethod');
			return  $this->declarePluginParams('shipment', $name, $id,  $data);
		}//*/
		
		// Настроики способа доставки для VM2
		function plgVmDeclarePluginParamsShipment ( $name, $id, &$dataOld )
		{
			//dumpMessage('function plgVmDeclarePluginParamsShipment  ');
			
			return $this->declarePluginParams( 'shipment', $name, $id, $dataOld );
		}#END FN
		
		/**
		 * Событие модель Shipmentmethod
		 * На странице настроек VM - Shipment method - Edit
		 *
		 *
		 *
		 * @param $data - TableShipmentmethods Object 
		 *
		 * @return bool
		 * @author    Gartes
		 * @since     3.8
		 * @copyright 03.11.18
		 */
		function plgVmDeclarePluginParamsShipmentVM3 ( &$data )
		{
			
			
			//dumpMessage(' function plgVmDeclarePluginParamsShipmentVM3  ');
			return  $this->declarePluginParams( 'shipment', $data );
			
		}#END FN
	
	
	
		
		
		function plgVmSetOnTablePluginParamsShipment ( $name, $id, &$table )
		{
			
			//dumpMessage(' function plgVmSetOnTablePluginParamsShipment  ');
			return $this->setOnTablePluginParams( $name, $id, $table );
		}#END FN
		
	} #END CLASS
	// No closing tag
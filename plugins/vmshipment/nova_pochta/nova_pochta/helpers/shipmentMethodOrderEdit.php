<?php
	/**
	 * Created by PhpStorm.
	 * User: oleg
	 * Date: 11.11.18
	 * Time: 17:08
	 */
	
	
	class helpersShipmentMethodOrderEdit
	{
		
		/**
		 * Переопределение скрипта инициализауии для списков <select>
		 * Для исключения тег <select> должен иметь класс NpElement_Chosen
		 *
		 * @author    Gartes
		 * @since     3.8
		 * @copyright 12.11.18
		 */
		private static function revriteUpdateChosenDropdownLayout(){
			
			  vmJsApi::removeJScript('updateChosen');
			
			$script = '
				if (typeof Virtuemart === "undefined") var Virtuemart = {};
					Virtuemart.updateChosenDropdownLayout = function() {
						var vm2string = {
							editImage: "edit image",
							select_all_text: "Выбрать все",
							select_some_options_text: "Доступен для всех"};
						
						jQuery("select:not(.NpElement_Chosen)").each( function () {
							// var swidth = jQuery(this).css("width")+1000;
							jQuery(this).chosen({
								enable_select_all: true,
								select_all_text             :   vm2string.select_all_text ,
								select_some_options_text    :   vm2string.select_some_options_text  ,
								disable_search_threshold: 5,
								//width: swidth
							});
						});
					}
					
					document.addEventListener("DOMContentLoaded", function () {
						Virtuemart.updateChosenDropdownLayout($);
					});
				jQuery(document).ready( function() {});
			';
			
			vmJsApi::addJScript('updateChosen',$script);
			
			// $vmJS = vmJsApi::getJScripts();
			
			
			
			
		}#END FN
		
		public static function getOrderEditHtmlForm( $shipinfo ){
			
			 
			
			
			$app = \JFactory::getApplication() ;
			$doc = JFactory::getDocument();
			$template = $app->getTemplate();
			if ( JVersion::RELEASE <= 3.6 ){
				
				$doc->addScript( JURI::root() . 'plugins/vmshipment/nova_pochta/nova_pochta/assets/js/' . 'novaPoshtaCore.js', "text/javascript" ,1  );
				$doc->addScript( JURI::root() . 'plugins/vmshipment/nova_pochta/nova_pochta/assets/js/' .  'novaPoshtaMethodOrderEdit.js' , "text/javascript" ,1  );
				$doc->addScript( JURI::root() . 'libraries/xzlib/app/document/assets/js/' . 'noty.js' , "text/javascript" ,1 ,1 );
			}else
			{
				$doc->addScript( \JURI::root() . 'plugins/vmshipment/nova_pochta/nova_pochta/assets/js/' . 'novaPoshtaCore.js', [ /*'version' => 'auto'*/ ], [ 'defer' => 1 ] );
				$doc->addScript( \JURI::root() . 'plugins/vmshipment/nova_pochta/nova_pochta/assets/js/' . 'novaPoshtaMethodOrderEdit.js' , [ /*'version' => 'auto'*/ ], [ 'defer' => 1 ] );
				$doc->addScript( \JURI::root() . 'libraries/xzlib/app/document/assets/js/' . 'noty.js', [ /*'version' => 'auto'*/ ], [ 'defer' => 1 ] );
			}
			
			
			$doc->addStyleSheet( \JURI::root() . 'plugins/vmshipment/nova_pochta/nova_pochta/assets/css/'.'novaPoshtaOrderEdit.css'  /*, array('version' => 'auto')*/ );
			$doc->addStyleSheet( \JURI::root() . 'libraries/xzlib/app/document/assets/css/'.'noty/noty.css'  /*, array('version' => 'auto')*/ );
			$doc->addStyleSheet( \JURI::root() . 'libraries/xzlib/app/document/assets/css/'.'noty/themes/mint.css'  /*, array('version' => 'auto')*/ );
			
			
			
			
			
			# Переопределение Chosen
			self::revriteUpdateChosenDropdownLayout();
			
			
			
			
			$method = self::getMethod($shipinfo->virtuemart_shipmentmethod_id) ;
			$app->input->set('virtuemart_shipmentmethod_id' , $shipinfo->virtuemart_shipmentmethod_id );
			
			if ( !class_exists( 'helpersShipmentMethodCart' ) )
				require( JPATH_PLUGINS.DS.'vmshipment'.DS.$method->shipment_element.DS.$method->shipment_element.DS.'helpers'.DS.'shipmentMethodCart.php' );
			
			$novaPoshta = self::getNovaPohstaAPI($method ,$shipinfo  );
			
			$layout      = new JLayoutFile('orderEdit', null ,array('debug' =>  false ) );
			$layout->addIncludePath( JPATH_PLUGINS . '/vmshipment/nova_pochta/nova_pochta/layouts' );
			$layout->addIncludePath( JPATH_THEMES . '/' . $template . '/html/layouts/com_virtuemart/vmshipment/plg_nova_pochta/' );
			$layout->addIncludePath( JPATH_THEMES . '/' . $template . '/html/plg_nova_pochta/' );
			
			JForm::addFieldPath(JPATH_PLUGINS.DS.'/plugins/vmshipment/nova_pochta/elements');
			$form = \JForm::getInstance( 'novaPochtaOrderEdit', JPATH_PLUGINS.DS.'/vmshipment/nova_pochta/nova_pochta/forms/formOrderEdit.xml');
			
			
			
			
			
			
			
			
			
			
			$shipinfo->Address = json_decode( $shipinfo->Address ) ;
			$shipinfo =  helpersShipmentMethodCart::_prepareAdressArr( (array)$shipinfo ) ;
			
			if ( isset( $shipinfo[ 'Address' ]->RefStreet ) )
			{
				$shipinfo[ 'RefStreet' ] = $shipinfo[ 'Address' ]->RefStreet;
			}#END IF
			
			
			
			
			
			
			# Распаковка данных об обратной доставки
			$shipinfo['BackwardDeliveryData'] = (array)json_decode( $shipinfo['BackwardDeliveryData'] ) ;
			# Распаковка данных о  Экспресс-накладной
			$shipinfo['InternetDocument'] = (array)json_decode( $shipinfo['InternetDocument'] ) ;
			
			
			if (!$shipinfo['VolumeGeneralParamsList']){
				 $shipinfo['VolumeGeneralParamsList'] =' {"_Volume":[],"_Width":[],"_Length":[],"_Height":[],"_Weight":[],"_VolumeWeight":[]}';
			}
			//
			# Распаковка данных - параметров груза
			$VolumeGeneralParamsList = (array)json_decode($shipinfo['VolumeGeneralParamsList']) ;
			
			
			if ( count( $VolumeGeneralParamsList['_Volume'] ) > 0  ){
				$form->setFieldAttribute('Weight', 'readonly', 1, 'nova_pochta');
				$form->setFieldAttribute('VolumeGeneral', 'readonly', 1, 'nova_pochta');
				$form->setFieldAttribute('SeatsAmount', 'readonly', 1, 'nova_pochta');
			}
			
			
			
			
			#Объемный вес
			$shipinfo['VolumeWeight']=($shipinfo['VolumeWeight']>0?$shipinfo['VolumeWeight']:'');
			# Общий объем м³
			$shipinfo['VolumeGeneral']=($shipinfo['VolumeGeneral']>0?$shipinfo['VolumeGeneral']:'');
			
			#Дата отправки в формате дд.мм.гггг*
			$shipinfo['DateTime'] = null ;
			
			
			
			#Поле Имя
			$shipinfo['FirstName'] = ( !$shipinfo['FirstName'] ?'':$shipinfo['FirstName']);
			#Поле Фамилия
			$shipinfo['LastName'] = ( !$shipinfo['LastName'] ?'':$shipinfo['LastName']);
			#Поле очество
			$shipinfo['MiddleName'] = ( !$shipinfo['MiddleName'] ?'':$shipinfo['MiddleName']);
			#если поле отчество стало обязательным
			if ( $shipinfo['ServiceType'] == 'WarehouseDoors' && !$shipinfo['BackwardDeliveryData']['BackwardDeliveryData_On'] ){
				$form->setFieldAttribute('MiddleName', 'required', true, 'nova_pochta');
			}
			
			//echo'<pre>';print_r( $shipinfo );echo'</pre>'.__FILE__.' '.__LINE__;
			
			 
			
			
			// echo'<pre>';print_r( $shipinfo['BackwardDeliveryData']['BackwardDeliveryData_On'] );echo'</pre>'.__FILE__.' '.__LINE__;
//			die(__FILE__ .' Lines '. __LINE__ );
			
			
			
		 
			//echo'<pre>';print_r( $shipinfo['Weight'] );echo'</pre>'.__FILE__.' '.__LINE__;
		 
	
			$form->bind(  array('nova_pochta'=>$shipinfo)  );
			
			$data_layout = [
				'Recipient_data' => $form->renderFieldset( 'Recipient_data' ),
				'Recipient_city' => $form->renderFieldset( 'Recipient_city' ),
				'Order_params' =>  $form->renderFieldset( 'Order_params' ),
				'InternetDocument_data' =>  $form->renderFieldset( 'InternetDocument_data' ),
				
			    'Recipient_BackwardDelivery' => $form->renderFieldset( 'Recipient_BackwardDelivery' ),
				'Recipient_ServiceType'         => $form->renderFieldset( 'Recipient_ServiceType' ),
				//'form_Address' => $form->renderFieldset( 'Recipient_Address_fieldset' ),
				'method_id' => $shipinfo['virtuemart_shipmentmethod_id'] ,
				'virtuemart_order_id'=> $shipinfo['virtuemart_order_id'] ,
				'id'=>$shipinfo['id'],
			];
			$html   = $layout->render($data_layout); // $displayData
			self::showJsLog((array)json_decode($shipinfo['log']) ) ;
			
			
			// echo'<pre>';print_r( $shipinfo );echo'</pre>'.__FILE__.' '.__LINE__;
			return $html ;
			
		}#END FN
		
		
		
		
		
		public static function showJsLog($log){
			$doc = JFactory::getDocument();
			$doc->addScriptOptions('plg_novaPoshta' , ['logs' =>  $log ] ) ;
		}#END FN
		
		
		
		
		
		/**
		 * Получить настройки способа доставки
		 * @param $id
		 *
		 * @return mixed
		 * @author    Gartes
		 * @since     3.8
		 * @copyright 11.11.18
		 */
		public static function getMethod($id){
			$shipmentModel = VmModel::getModel('shipmentmethod');
			return  $shipmentModel->getShipment( $id );
		}#END FN
		
		public static function getNovaPohstaAPI($method , $shipinfo  ){
			$app = \JFactory::getApplication() ; 
			$app->input->set('_plgNovaPochta', array(
				'virtuemart_shipmentmethod_id' => $method->virtuemart_shipmentmethod_id,
				'CityRecipient'=> $shipinfo->CityRecipient
			));
			
			// echo'<pre>';print_r( $method->virtuemart_shipmentmethod_id );echo'</pre>'.__FILE__.' '.__LINE__;
			
			if ( !class_exists( 'xzlib\xzlib_app' ) ) require_once JPATH_LIBRARIES . '/xzlib/xzlib_app.php';
			$api        = xzlib\xzlib_app::_get( 'api',  [] );
			$novaPoshta = $api->getApi( 'nova_poshta', [] );
			$novaPoshta->setApiKey ( $method->keyAPI ) ;
			
			return $novaPoshta ;
		}#END FN
		
	} 
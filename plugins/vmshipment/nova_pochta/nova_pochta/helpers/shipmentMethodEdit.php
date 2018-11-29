<?php defined( '_JEXEC' ) or die( 'Restricted access' );
	
	/**
	 * HELPER - настройка способа доставки
	 *
	 * Created by PhpStorm.
	 * User: oleg
	 * Date: 09.11.18
	 * Time: 12:37
	 */
	class helpersShipmentMethodEdit
	{
		/**
		 * Добавить скрипты для страницы VM - Shipment method - Edit
		 *
		 * @param Object $method TableShipmentmethods
		 *
		 * @throws Exception
		 * @author    Gartes
		 * @since     3.8
		 * @copyright 09.11.18
		 */
		public static function addJsShipmentMethodEdit ($method )
		{
			$app = JFactory::getApplication();
			$doc = JFactory::getDocument();
			
			if ( !class_exists( 'xzlib' ) )
			{
				require JPATH_LIBRARIES . '/xzlib/xzlib.php';
			}#END IF
			$xzlibDocument = xzlib::get( 'document', [] );
			
			if ( !$method->keyAPI ) $app->enqueueMessage( JText::_( 'VMSHIPMENT_NP_MSG_KEY' ), 'info' );#END IF
			if ( !empty( $method->keyAPI ) && empty( $method->validity_key ) ) $app->enqueueMessage( JText::_( 'VMSHIPMENT_NP_MSG_VALIDITY_KEY' ), 'warning' );#END IF
			
			
			$doc->addStyleSheet( JURI::root() . 'plugins' . DS . 'vmshipment' . DS . 'nova_pochta' . DS . 'nova_pochta' . DS . 'assets' . DS . 'css' . DS . 'novaPoshtaMethodEdit.css' );
			
			// $doc->addScript( JURI::root() . 'plugins' . DS . 'vmshipment' . DS . 'nova_pochta' . DS . 'js' . DS . 'np_set_admin.js' );
			
			if ( JVersion::RELEASE <= 3.6 ){
				$doc->addScript( JURI::root() . 'plugins/vmshipment/nova_pochta/nova_pochta/assets/js/' . 'novaPoshtaCore.js', "text/javascript" ,1, 1  );
				$doc->addScript( JURI::root() . 'plugins/vmshipment/nova_pochta/nova_pochta/assets/js/' . 'novaPoshtaMethodEdit.js', "text/javascript" ,1, 1  );
			}else{
				$doc->addScript( JURI::root() . 'plugins/vmshipment/nova_pochta/nova_pochta/assets/js/' . 'novaPoshtaCore.js', [ 'version' => 'auto' ], [ 'defer' => 1 ] );
				$doc->addScript( JURI::root() . 'plugins/vmshipment/nova_pochta/nova_pochta/assets/js/' . 'novaPoshtaMethodEdit.js', [ 'version' => 'auto' ], [ 'defer' => 1 ] );
			}#END IF
			
			
			$doc->addScriptDeclaration( '
				"undefined"===typeof NovaPoshta&&(NovaPoshta={});
				"undefined"===typeof NovaPoshta.Setting&&(NovaPoshta.Setting={});
				NovaPoshta.Setting.methodId="' . $method->virtuemart_shipmentmethod_id . '";
			' );
			
			//JVersion::RELEASE;
			//echo'<pre>';print_r( JVersion::RELEASE <= 3.6 );echo'</pre>'.__FILE__.' '.__LINE__;
			
		}#END FN
		
		
	}#END CLASS
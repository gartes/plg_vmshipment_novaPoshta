<?php defined( '_JEXEC' ) or die( 'Restricted access' );
	
	/**
	 * HELPER - настройка способа доставки
	 *
	 * Created by PhpStorm.
	 * User: oleg
	 * Date: 09.11.18
	 * Time: 12:37
	 */
	class helpersShipmentMethodFields
	{
		/**
		 * Получить настройки метода доставки.
		 *
		 * @return mixed
		 * @throws Exception
		 * @author    Gartes
		 * @since     3.8
		 * @copyright 07.11.18
		 */
		public static function getMethod ()
		{
			$app    = \JFactory::getApplication();
			$modelShipmentmethod = VmModel::getModel( 'Shipmentmethod' );
			$paramNovaPochta = $app->input->getArray(array('_plgNovaPochta'=>'ARRAY')) ;
			
			
			$option = $app->input->get( 'option', false, 'WORD' );
			$view   = $app->input->get( 'view', false, 'WORD' );
			$task   = $app->input->get( 'task', false, 'WORD' );
			
			if ( $option == 'com_virtuemart' && $view == 'shipmentmethod' && $task == 'edit' )
			{
				
				$id                  = $app->input->get( 'cid', [], 'ARRAY' );
				
				return $modelShipmentmethod->getShipment( $id[ 0 ] );
			}#END IF
			
			
			
			
			
			if (!empty( $paramNovaPochta['_plgNovaPochta'] )){
				return $modelShipmentmethod->getShipment( $paramNovaPochta['_plgNovaPochta']['virtuemart_shipmentmethod_id']);
			}
			
			
			
			return false ;
		}#END FN
		
		
	}#END CLASS
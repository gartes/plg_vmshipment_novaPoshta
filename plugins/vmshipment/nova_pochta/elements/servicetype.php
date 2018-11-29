<?php defined( '_JEXEC' ) or die();
	
	if ( !class_exists( 'xzlib\xzlib_app' ) ) require_once JPATH_LIBRARIES . '/xzlib/xzlib_app.php';
	
	/**
	 * Технология доставки
	 *
	 * Class JFormFieldServiceType
	 */
	class JFormFieldServiceType extends JFormFieldList
	{
		/**
		 * @var string
		 */
		var $type = 'servicetype';
		
		/**
		 * @return array
		 * @author    Gartes
		 * @since     3.8
		 * @copyright 07.11.18
		 */
		function getOptions ()
		{
			$api        = xzlib\xzlib_app::_get( 'api', $options = [] );
			$novaPoshta = $api->getApi( 'nova_poshta', $options = [] );
			
			$modelCommon  = $novaPoshta->getModel( 'Common' );
			$serviceTypes = $modelCommon->getServiceTypes();
			
			$options = [];
			foreach ( $serviceTypes->data as $type )
			{
				$options[] = JHtml::_( 'select.option', $type->Ref, vmText::_(   $type->Description  ) );
			}
			
			return $options;
			
		}#END FN
	}#END CLASS
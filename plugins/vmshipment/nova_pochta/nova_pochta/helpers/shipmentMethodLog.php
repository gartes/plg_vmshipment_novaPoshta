<?php defined('_JEXEC') or die;
	/**
	 * Created by PhpStorm.
	 * User: oleg
	 * Date: 14.11.18
	 * Time: 13:35
	 */
	 
	class shipmentMethodLog extends JRegistry {
		public $_Log ;
		
		public static $instance;
		
		/**
		 * @param array $options
		 *
		 * @return shipmentMethodLog
		 * @author    Gartes
		 * @since     3.8
		 * @copyright 14.11.18
		 */
		public static function instance ( $options = [] )
		{
			if ( self::$instance === null )
			{
				self::$instance = new self( $options );
			}#END IF
			return self::$instance;
		}#END FN
		
		/**
		 * shipmentMethodLog constructor.
		 *
		 * @param null $options
		 */
		public function __construct ( $options = null )
		{
			$this->_setDefault();
			self::$instance = $this;
		}#END FN
		
		/**
		 * @param $data
		 *
		 * @author    Gartes
		 * @since     3.8
		 * @copyright 14.11.18
		 * @return shipmentMethodLog
		 */
		public static function addLog( $data ){
			
			if (isset($data->errors)){
				$errors = self::$instance->_Log->get('errors') ;
				$errors = array_merge ($errors, $data->errors);
				self::$instance->_Log->set('errors' , $errors) ;
			}#END IF
			
			if (isset($data->warnings)){
				$warnings = self::$instance->_Log->get('warnings') ;
				$warnings = array_merge ($warnings, $data->warnings);
				self::$instance->_Log->set('warnings' , $warnings) ;
			}#END IF
			
			if (isset($data->info)){
				$info = self::$instance->_Log->get('info') ;
				$info = array_merge ($info, $data->info);
				self::$instance->_Log->set('info' , $info) ;
			}#END IF
			if (isset($data->errorCodes))
			{
				$errorCodes = self::$instance->_Log->get( 'errorCodes' );
				$errorCodes = array_merge( $errorCodes, $data->errorCodes );
				self::$instance->_Log->set( 'errorCodes', $errorCodes );
			}#END IF
			
			return 	self::$instance ;
		}#END FN
		
		public static function getLog( $type = false ){
			
			$arrLog = array('errors','warnings' , 'info' , 'errorCodes'  ) ;
			$ret = array();
			if (!$type){
				foreach ( $arrLog as $index ){
					$ret[$index] = self::$instance->_Log->get( $index );
				}
				return $ret ;
			}
			
			return self::$instance->_Log->get($type) ;
		
		}#END FN
		
		public static function getLogErr(){
			return self::getLog('errors');
		}
		
		
		/**
		 * @return stdClass
		 * @author    Gartes
		 * @since     3.8
		 * @copyright 14.11.18
		 */
		protected function _setDefault ()
		{
			
			$Log = new stdClass();
			$Log->errors = [];
			$Log->warnings = [];
			$Log->info = [];
			$Log->errorCodes = [];
			
			$this->_Log =new JRegistry($Log);
			
			
			
			 
			return $this->_Log;
		}#END FN
		
		
		
	}#END CLASS
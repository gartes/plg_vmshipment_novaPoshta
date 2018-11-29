<?php defined('_JEXEC') or die();
class JElementTimeServer extends JElement {
/** Element name
    @access	protected
 @var		string
 */
	var $_name = 'timeserver';
    function fetchElement($name, $value, &$node, $control_name) {
		date_default_timezone_set('Europe/Kiev');
		$hip_set = getSetingShipParams ();	
		$timestamp = time();
		$date_time_array = getdate($timestamp);
		$hours = $date_time_array['hours'];



	//Коректировка времени



	//$hoursPlus = $hours+3;



	



	$minutes = $date_time_array['minutes'];



	$seconds = $date_time_array['seconds'];



	$month = $date_time_array['mon'];



	$day = $date_time_array['mday'];



	$year = $date_time_array['year'];



	$citySelct = '<div style="color:red"><strong>'.$hours.':'.$minutes.'</strong></div>';



		



        return $citySelct;



    }//







}
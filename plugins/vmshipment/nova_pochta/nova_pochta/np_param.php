<?php 

jimport('joomla.environment.request');
class np_param {
	
	// обновление одной ячейки в таблице доставки новой почты
	//  #__virtuemart_shipment_plg_nova_pochta
	//  $order -номер заказа 
	//  $pole - ячейка в столбце
	//  $value - значение 
	function apd_line_order($order,$pole,$value){
		$db = JFactory::getDBO();
		$sql = 'UPDATE #__virtuemart_shipment_plg_nova_pochta
				SET '.$pole.' = "'.$value.'"
				WHERE virtuemart_order_id  ='.$order;
		$db->setQuery($sql);
		if(!$db->query()){
			echo __LINE__.$db->stderr();
		}
	}//

	function cost_calculationDelivery($recipientCity,$mass,$deliveryType,$postpay_sum=0){
		$param = $this->getSetingShipParams();
		$senderCity = $this->jdecoder($param['citi_of_sender']);
		$date = $this->getDeyDateOne($param['time_avto'],'0000-00-00');
		if($param['public_pricet_min_for_np']>$postpay_sum)
			$postpay_sum=$param['public_pricet_min_for_np'];
		if($param['weight_min_for_np']>$mass)
			$mass=$param['weight_min_for_np'];
		$auth= $param['key_privat_np'];
		$xml = '<?xml version="1.0" encoding="utf-8"?>';
		$xml .='<file>';
		$xml .='<auth>'.$auth.'</auth>';
		$xml .='<countPrice>';
		$xml .='<senderCity>'.$senderCity.'</senderCity>';
		$xml .='<recipientCity>'.$senderCity.'</recipientCity>';
		$xml .='<mass>'.$mass.'</mass>';
		/*$xml .='<height>15</height>';
		$xml .='<width>20</width>';
		$xml .='<depth>55</depth>';*/
		$xml .='<loadType_id>1</loadType_id>';
		$xml .='<publicPrice>'.$postpay_sum.'</publicPrice>';
		$xml .='<deliveryType_id>'.$deliveryType.'</deliveryType_id>';
		$xml .='<postpay_sum>'.$postpay_sum.'</postpay_sum>';
		$xml .='<date>'.$date.'</date>';
		$xml .='</countPrice>';
		$xml .='</file>';
		$arrayXml =  $this->starQueryNP ($xml);
		if($arrayXml[1]['tag']=='ERROR'){
			return $r['complete']='ERROR';
		}
		$r['complete']='OK';
		$r['date']=$arrayXml[1]['value'];
		$r['cost']=$arrayXml[2]['value'];
		return $r;
	}//
	
	

	// работа с кешем /////////////////////////////////////////////////////////////////////////////////
	function ceshHendler($ob,$Ref=''){
				
		define(PATH_CESH,   JPATH_SITE.DS.'plugins'.DS.'vmshipment'.DS.'nova_pochta'.DS.'cesh');
				
		if (!is_dir(PATH_CESH))mkdir(PATH_CESH);
		$setingNP = $this->getSetingShipParams ();
		$language = $setingNP['np_language'];
		$timeCesh = $setingNP['time_cech_for_np'];
		$filename = PATH_CESH.DS.$ob.'_'.$language.'.txt';
		
		if($ob=='payer')$timeCesh = $timeCesh*4;
		if($ob=='getWarehouses')$filename= PATH_CESH.DS.$Ref.'_'.$language.'.txt';
		$timeCesh = 1;
		
		
		
		
		//  если файл кеша есть
		if (file_exists($filename)) {
		
			//  получить о нем инфо
			$f_info = stat ($filename);
			
			if ( (time()- $f_info['mtime'])  > $timeCesh or $f_info[7] == 0 ) {
				
				
				unlink($filename);	
				if($ob == 'city' ) {
					$arrCity = $this->getCities();
					$this->ceshWrite('city',$arrCity);
					return $this->restorCity(json_decode(file_get_contents ($filename)),$language);
				}
				if($ob=='getWarehouses'){
					$this->ceshWrite($Ref,$this->getWarehouses($Ref),$ob);
					return $this->restorCity(json_decode(file_get_contents ($filename)),$language);	
				}
				if($ob == 'payer' ) {
					$this->ceshWrite('payer', $this->getTypesOfPayers()  );
					return (array)json_decode(file_get_contents ($filename));
				}
				if($ob=='serviceTypes'){
					$this->ceshWrite('serviceTypes', $this->getServiceTypes()  );	
					return (array)json_decode(file_get_contents ($filename));
				}
				
				
				
				
				
			}else{
				if($ob == 'payer' or $ob=='serviceTypes' ){
					return (array)json_decode(file_get_contents ($filename));
				}
				elseif($ob == 'city' ){
					return $this->restorCity(json_decode(file_get_contents ($filename)),$language);
				}
				elseif($ob=='getWarehouses'){
					return $this->restorCity(json_decode(file_get_contents ($filename)),$language);
					
				}
					
				
			}
					
			
			//  если файл устарел или его размер равен 0
			//  удалить файл и записать заново
			/*if ( (time()- $f_info['mtime'])  > $timeCesh or $f_info[7] == 0 ) {
				unlink($filename);
				$arrayXml = $this->xmlQueryCity();	
				return $this->parsingXmlCity($filename,$language,$arrayXml);
			}else{
				//  если файл не устарел то 
				//  читаем файл
				if($setingNP['api']==0 ){
					$contentCity =   file ($filename);
					foreach ($contentCity  as $v){
						$v = trim ($v);
						$arrCity[$v] = $v;
					}
					asort($arrCity);
					return	$arrCity;
				}
				if($setingNP['api']==1 ){
					$out = json_decode(file_get_contents ($filename));
					if($ob == 'city' ){
						
					}else{
					return $this->restorWarenHouse($out,$language);
					}
				}
			}*/
		}else{
			// если файла кеша нет
			//  отправить запрос получит с новой почты
			//  записать в файл
			if($ob == 'city' ){
				$arrCity = $this->getCities();
				unset($arrCity['action']);
				$this->ceshWrite('city',$arrCity);
				return $this->restorCity(json_decode(file_get_contents ($filename)),$language);
			}
			elseif($ob=='getWarehouses'){
				$arrWarehouses = $this->getWarehouses($Ref);
				unset($arrWarehouses['action']);
				$this->ceshWrite($Ref,$arrWarehouses,$ob);
				return $this->restorCity(json_decode(file_get_contents ($filename)),$language);
			}
			
			elseif($ob=='serviceTypes'){
				$this->ceshWrite('serviceTypes',$this->getServiceTypes() );
				return (array)json_decode(file_get_contents ($filename));				
			}
				
			elseif($ob == 'payer'){
				$this->ceshWrite('payer', $this->getTypesOfPayers()  );
				return (array)json_decode(file_get_contents ($filename));
			}
				
			
			
		}
	}//
	
	function  ceshWrite($type,$data,$ob=''){
		
		define(PATH_CESH,   JPATH_SITE.DS.'plugins'.DS.'vmshipment'.DS.'nova_pochta'.DS.'cesh');
		if (!is_dir(PATH_CESH))mkdir(PATH_CESH);
		$setingNP = $this->getSetingShipParams ();
		$language = $setingNP['np_language'];
		$filename = PATH_CESH.DS.$type.'_'.$language.'.txt';
		$fp = fopen($filename,"w");
		$r = fwrite($fp, json_encode($data) );
		fclose($fp);
		if($r==FALSE){
			return $this->dictionary('errFwrite') ;	
		}	
	}
	
	// подготовка запроса на создание ЭН Новой почты
	function createEnOrder($vm_orderId){
		$shipInfo = $this->getShipInfo($vm_orderId);
		$setingNP = $this->getSetingShipParams();
		if($setingNP['api']==0 ){ 
			$queryXml = '<?xml version="1.0" encoding="UTF-8"?>
							<file>
								<auth>'.$npParam['key_privat_np'].'</auth>
								<order
									order_id="'.$shipInfo->virtuemart_order_id.'"
									date="'.$shipInfo->date_otpravki.'"
									sender_city="'.$this->jdecoder($npParam['citi_of_sender']).'"
									sender_company="'.$this->jdecoder($npParam['name_firm']).'"
									sender_address="'.$npParam['sender_address'].'"
									sender_contact="'.$this->jdecoder($npParam['sender_contact']).'"
									sender_phone="'.$npParam['sender_phone'].'"
									rcpt_city_name="'.$shipInfo->ship_syti_nova_poshta.'"
									rcpt_name="Приватна особа"
									rcpt_warehouse="'.$shipInfo->ship_sklad_nova_poshta.'"
									rcpt_contact="'.$shipInfo->name_recipient.'"
									rcpt_phone_num="'.$shipInfo->tel_recipient.'"'."\n";
			$queryXml.= 'pack_type="Коробка"'."\n";//  тип упаковки
			$queryXml.= 'description="'.$this->jdecoder($npParam['description_order']).'"'."\n";//повний опис відправлення 
			$queryXml .= 'pay_type="1"'."\n";//вид розрахунку:1-готівковий , 2 - безготівковий
			$queryXml .= 'payer="'.$shipInfo->payer.'"'."\n";//вид платника:0-Одержувач,1-Відправник,2-Третя особа
			if ($shipInfo->delivery_in_out>$npParam['public_pricet_min_for_np']  ){
				$npParam['public_pricet_min_for_np'] = $shipInfo->delivery_in_out;	
			}
			$queryXml .= 'cost="'.$npParam['public_pricet_min_for_np'].'"'."\n";//оголошена вартість відправлення
			$queryXml .= 'additional_info="Номер заказа '.$shipInfo->order_number.'"'."\n";// Комментарии к отправлению
			$queryXml .= 'weight="'.$shipInfo->order_weight.'"'."\n";//  вес заказа
			// если есть обратная доставка			
			if ($shipInfo->delivery_in_out>0){
				$queryXml .='redelivery_type="2"'."\n";//тип зворотної доставки 1 - документи 2 - гроші ++Admin
				$queryXml .='delivery_in_out ="'.$shipInfo->delivery_in_out.'"'."\n";
				// платник зворотньої доставки:
				$queryXml .= 'redelivery_payment_payer ="'.$shipInfo->redelivery_payment_payer.'"';		
			}
			$queryXml .='>'."\n";
			// заносим кол-во мест в заказе
			for ($i=0; $i<$shipInfo->col_mest; $i++) {$queryXml.='<order_cont cont_description="" />'."\n";}
			$queryXml .='</order></file>';
			
			$nomerEN = $this ->starQueryNP ($queryXml);
			if($nomerEN[1]['attributes']['NP_ID']){
				$order=$nomerEN[1]['attributes']['ID'];
				$pole = 'ship_nomer_en_nova_poshta';
				$value = $nomerEN[1]['attributes']['NP_ID'];
				$this->apd_line_order($order,$pole,$value);
				$v['nomerEn'] = $value;
				$v['result']=$nomerEN[1]['type'];	
			}
		}
		if($setingNP['api']==1 ){
			$queryXml='{';
			$queryXml.='"apiKey": "'.$setingNP['key_privat_np'].'",';
			$queryXml.='"modelName": "InternetDocument",';
			$queryXml.='"calledMethod": "save",';
			$queryXml.='"methodProperties": {';

//------------- дата отправки------------------------------------------------------------------------------
				$dataArrray = explode("-", $shipInfo->date_otpravki);
				$dataOtpr = sprintf("%02d.%02d.%04d", $dataArrray[2],$dataArrray[1], $dataArrray[0]);
				$queryXml.='"DateTime": "'.$dataOtpr.'",'; 

//------------- технологий доставки ------------------------------------------------------------------------------				
				//: «склад-склад», «двери-двери»,«склад-двери», «двери-склад»
				$queryXml.='"ServiceType": "'.JRequest::getVar('variant_deliv',"WarehouseWarehouse").'",'; 
				
//-------------  Ref отправителя ------------------------------------------------------------------------------					
				$queryXml.='"Sender": "'.$setingNP['senderRef'].'",';

//-------------  город отправителя ------------------------------------------------------------------------------	
				$queryXml.='"CitySender": "'.$setingNP['citi_of_sender'].'",';//  

//-------------  Ref адреса отправителя -------------------------------------------------------------------------	
				$queryXml.='"SenderAddress": "'.$setingNP['sender_address'].'",';//  
				$queryXml.='"ContactSender": "'.$setingNP['senderRefContact'].'",'; //  Ref контакта отправителя				
				$queryXml.='"SendersPhone": "'.$setingNP['sender_phone'].'",'; //  телефон отправителя
				
//-------------  Ref Получателя         -------------------------------------------------------------------------				
				$queryXml.='"Recipient": "'.$shipInfo->ref_сounterparties.'",'; // Ref Получателя
				$queryXml.='"CityRecipient": "'.$shipInfo->ship_syti_nova_poshta.'",'; // Ref город получателя
				 // Ref адреса получателя (Ref склада)
				$queryXml.='"RecipientAddress":"'.$shipInfo->ship_sklad_nova_poshta.'",';
				//  Ref контакта получателя
				$queryXml.='"ContactRecipient":"'.$shipInfo->ref_сounterparty_contact_persons.'",';
				$queryXml.='"RecipientsPhone": "'.$shipInfo->tel_recipient.'",';//  телефон получателя

//-------------  форма оплаты         -------------------------------------------------------------------------
				$queryXml.='"PaymentMethod": "Cash",';// форма оплаты обязательно для заполнения

//-------------  тип плательщика за доставку-------------------------------------------------------------------				
				$queryXml.='"PayerType": "'.$shipInfo->payer.'",';	
				
//-------------  объявленная стоимость      -------------------------------------------------------------------				
				$queryXml.='"Cost": "'.$setingNP['public_pricet_min_for_np'].'",';// заявленая стоимость
				
//-------------  количество мест отправления-------------------------------------------------------------------					
				$queryXml.='"SeatsAmount": "'.$shipInfo->col_mest.'",';//   количество мест
				
//-------------  описания груза  --------------------------------------------------------------------------					
				$Description = $this->CommonGetCargoDescriptionList();
				$queryXml.='"Description": "'.$Description [$setingNP['description_order']].'",'; 
				
//-------------  тип груза  -------------------------------------------------------------------------------			
				$queryXml.='"CargoType": "Cargo",';//тип отправления
				
//-------------  объем общий, м.куб  обязательно, если не указаны значения OptionsSeat---------------------				
				$queryXml.='"VolumeGeneral": "'.$shipInfo->volume_general.'",'; // 
			
//-------------  Вес фактический, кг-----------------------------------------------------------------------
				$queryXml.='"Weight": "'.$shipInfo->order_weight.'",';// общий вес
				
	
				
//-------------  обратная доставка   ----------------------------------------------------------------------						
				if($shipInfo->delivery_in_out > 0 ){ 
					$queryXml.='"BackwardDeliveryData":[{';
					//  Кто платит за обратную доставку
					$queryXml.='"PayerType":"'.$shipInfo->redelivery_payment_payer.'",';
					//  тип обратной доставки
					$queryXml.='"CargoType":"Money",';
					//  сумма обратной доставки
					$queryXml.='"RedeliveryString":"'.$shipInfo->delivery_in_out.'"';
					$queryXml.='}],';
				}
				
//-------------  Комментарий - номер заказа----------------------------------------------------------------
				$queryXml.='"AdditionalInformation": "Номер заказа '.$shipInfo->order_number.'"';							
				
				$queryXml.=' }';
			$queryXml.='}'; 
				 
			  	
	
	$res = json_decode($this->starQueryNP($queryXml) );
	
	
			$db = &JFactory::getDBO();
			$query = $db->getQuery(true);
			$query->update('#__virtuemart_shipment_plg_nova_pochta');
			$query->where('virtuemart_order_id = '.$vm_orderId.'');
				
							
		
			$query->set('ship_nomer_en_nova_poshta = '.$db->Quote($res->data[0]->IntDocNumber) );
			$query->set('ref_int_doc_number = '.$db->Quote($res->data[0]->Ref) );
			$query->set('cost_on_site = '.$db->Quote($res->data[0]->CostOnSite) );
			
			$dataArrray = explode(".", $res->data[0]->EstimatedDeliveryDate);
			$EstimatedDeliveryDate = sprintf("%04d-%02d-%02d", $dataArrray[2],$dataArrray[1],$dataArrray[0] );
			
			$query->set('estimated_delivery_date = '.$db->Quote($EstimatedDeliveryDate) );
		
		
			$db->setQuery($query);
			if (!$db->query()) {
				JError::raiseError(500, $db->getErrorMsg());
			}else{
				$v['result']  = $res-> success;
				$v['nomerEn'] = $res->data[0]->IntDocNumber;
				$v['ref_int_doc_number'] = $res->data[0]->Ref;
				$v['cost_on_site'] = $res->data[0]->CostOnSite;
				$v['estimated_delivery_date'] = $EstimatedDeliveryDate;
				return $v;
			}
		}
		
	}//
	
	// Удаление ен
	function deliteEnOrder($vm_orderId){
		$npParam = $this->getSetingShipParams();
		$shipInfo = $this->getShipInfo($vm_orderId);
		if($npParam['api']==0 ){
			$queryXml = '<?xml version="1.0" encoding="UTF-8"?>
					<file>
    				<auth>'.$npParam['key_privat_np'].'</auth>
     				<close>'.$shipInfo->ship_nomer_en_nova_poshta.'</close>
    				</file>';
			$delitRezult = $this->starQueryNP ($queryXml);
			if($delitRezult[1]['attributes']['ID']){
				$this->apd_line_order($vm_orderId,'ship_nomer_en_nova_poshta',0);		
			}	
			return array('nomerEn'=>$delitRezult[1][attributes][ID],'value'=>$delitRezult[1][value]);
		}
		if($npParam['api']==1 ){
			$json = '{
					 "apiKey":"'.$npParam['key_privat_np'].'",
					 "modelName": "InternetDocument",
					 "calledMethod": "delete",
					 "methodProperties": {
						"DocumentRefs":["'.$shipInfo->ref_int_doc_number.'"]
					 }
					}';
			$res =  json_decode($this->starQueryNP($json));	
			if($res->success == 1 ){ 
				$this->apd_line_order($vm_orderId,'ship_nomer_en_nova_poshta',0);
				$this->apd_line_order($vm_orderId,'ref_int_doc_number',0);
				$this->apd_line_order($vm_orderId,'cost_on_site',0);
				$this->apd_line_order($vm_orderId,'estimated_delivery_date',0);	
			}else{
				foreach ( $res->errors as $k=>$v){
					$v = $this->dictionary ($v);
					$errors['err']=$v;	
				}
				return $errors;
			}
			return $res ;
		}
	}//
	//  обработчик сообщений
	function dictionary ($text){
		switch ($text) {
			case 'Document is printed':return 'Ошибка удаления. Документ распечатан!';break;	
			case 'API auth fail': return 'Ошибка в ключе API'; break;
			case 'errFwrite': return 'Ошибка записи кэш фала' ;	break;
		}
	}
	
	//  разбиение строки с параметрами
	function explodeParamDel($result){
		$vegetables = explode ("|",  $result);
		array_pop($vegetables);
		$arrSeting= array();
		foreach ($vegetables as $k=>$v){
			$i=0;
			$npKeyArray = explode ("=",  $v);
			foreach ($npKeyArray as $kk=>$vv){
				if ($i==0) {
					$arrSeting	[$vv] = ''; $key=$vv;
				}
				if ($i==1) {
					$vv = str_replace("\"","",$vv);	
					$vv = str_replace("[","",$vv);	
					$vv = str_replace("]","",$vv);			
					$arrSeting[$key] = $vv;
				}
				$i++;
			}
		}
		return $arrSeting;
	}//
	
	// получение параметров настройки плагина из таблицы плагинов доставки
	function getVirtuemartShipmentMethods (){
		$db = JFactory::getDBO();
		$q = 'SELECT *  FROM `#__virtuemart_shipmentmethods` WHERE shipment_element  ="nova_pochta"';
		$db->setQuery($q);
		$result = $db->loadObjectList();
		return $result;
	}
	
	//Метод: getTypesOfPayers() — получение списка видов плательщиков услуги доставки: 
	// Отправитель, Получатель, Третье лицо.
	//
	// Вид плательщика "Третье лицо" возможно заказать только после заключения договора 
	//с компанией "Новая Почта"
	//
	//Для обновления данных, справочник необходимо загружать один раз в месяц.
	function getTypesOfPayers(){
		$npParam = $this->getSetingShipParams();
		
		if($npParam['api']==1 ){
			$json = '{
					 "apiKey":"'.$npParam['key_privat_np'].'",
					 "language":"'.$npParam['np_language'].'",
					 "modelName": "Common",
					 "calledMethod": "getTypesOfPayers",
					 "methodProperties": {}
					}';
			$res =  json_decode($this->starQueryNP($json));	
			if($res->success!=1){
				return	$this->dictionary($res->errors); 
			}
			else{
				foreach ($res->data as $v ){
					$r[$v->Ref] = $v->Description;
				}
				return $r;	
			}
		}
		
	}
	
	// Обработтчик даты отправления
	// время отправления машины $time_avto
	// Дата отправки из таблицы заказа 
	function getDeyDateOne($time_avto,$date_otpravki){
		date_default_timezone_set('Europe/Kiev');
		$timestamp = time();
		$date_time_array = getdate($timestamp);
		if ($date_time_array ['hours']>= $time_avto ) {
			$date_time_array[0]+=86400;
			$date_time_array = getdate($date_time_array[0]);
		 }
		 if ($date_otpravki=='0000-00-00'){
			$dataOtpr = sprintf("%04d-%02d-%02d", $date_time_array['year'], $date_time_array['mon'], $date_time_array['mday']);
		 }else{
			$dataArrray = explode("-", $date_otpravki);
			if ($dataArrray[2]<$date_time_array['mday']){
				$dataOtpr = sprintf("%04d-%02d-%02d", $date_time_array['year'], $date_time_array['mon'], $date_time_array['mday']);
			}else{
				$dataOtpr =$date_otpravki;
			}
		 }
		return $dataOtpr;
	 }//

	// получение параметров настройки способа доставки 
	function getSetingShipParams($str="",$q ='SELECT shipment_params  FROM `#__virtuemart_shipmentmethods` WHERE shipment_element ="nova_pochta"')
	{
		$db = JFactory::getDBO();
		$db->setQuery($q);
		$result = $db->loadResult();
		if($str=="str" ){return $result;}
		$setingNP = $this->explodeParamDel($result);
		
		
		if(!$setingNP['key_privat_np']){
			if(JRequest::getVar('key_privat_np')){
				$setingNP['key_privat_np']=JRequest::getVar('key_privat_np');
			}else{
				if(JRequest::getVar('func')){
					$err ='Нет Ключа';
					return $err;	
				}
			}
		}
		if(!$setingNP['api'])
			$setingNP['api']=JRequest::getVar('api' , 1);
		if(!$setingNP['np_language'] )
			$setingNP['np_language']= JRequest::getVar('np_language' , 'ru');	
		if(!$setingNP['time_cech_for_np'])
			$setingNP['time_cech_for_np']=JRequest::getVar('time_cech_for_np' , 604800);
		return $setingNP;
	}//

	
	// Метод: getServiceTypes() — получение списка возможных типов технологий доставки: 
	// «склад-склад», «двери-двери», «склад-двери», «двери-склад»
	// Для обновления данных, справочник необходимо загружать один раз в месяц.
	function  getServiceTypes(){
		$npParam = $this->getSetingShipParams();
		
		if($npParam['api']==1 ){
			$json = '{
					 "apiKey":"'.$npParam['key_privat_np'].'",
					 "language":"'.$npParam['np_language'].'",
					 "modelName": "Common",
					 "calledMethod": "getServiceTypes",
					 "methodProperties": {}
					}';
			$res =  json_decode($this->starQueryNP($json));
			if($res->success!=1){
				return	$this->dictionary($res->errors); 
			}
			else{
				foreach ($res->data as $v ){
					$r[$v->Ref] = $v->Description;
				}
				return $r;	
			}
		}	
	}

	// Метод: getWarehouses — получение справочника отделений компании «Новая Почта»
	function getWarehouses($CityRef){
		$setingNP = $this->getSetingShipParams ();
		$language = $setingNP['np_language'];
		if($setingNP['api']==0 ){
			$filename = JPATH_PLUGINS.'/vmshipment/nova_pochta/cesh/'.md5($language.$city).'.txt';
			$xml = '<?xml version="1.0" encoding="utf-8"?>
					<file>
					<auth>'.$setingNP['key_privat_np'].'</auth>
					<warenhouse/>
					<filter>'.$city.'</filter>
					</file>';
			$arrayXml =  $this->starQueryNP ($xml);
			if ($language=='ru') $teg ='ADDRESSRU';
			if ($language=='ua') $teg ='ADDRESS';
			foreach ($arrayXml as $k=>$v) {
				if (in_array($teg, $v)){
					$adresRu = $v['value'];
					$adresRu = trim ($adresRu);
				}
				if (in_array("NUMBER", $v)){
					$nomerSclad = $v['value'];
					$sladSelect[$v['value']] = $adresRu;
				}
				// ksort($sladSelect);
				if ($sladSelect){
					$fp = fopen($filename,"w");
					foreach ($sladSelect as $k=>$v){
						$test = fwrite($fp, $v."\n");
					}
					fclose($fp);
				}
			}
			return $sladSelect;
		}
		if($setingNP['api']==1 ){
			$json = '{
						"apiKey": "'.$setingNP['key_privat_np'].'",
						"modelName": "Address",
						"calledMethod": "getWarehouses",
						"methodProperties": {
							"CityRef": "'.$CityRef.'"
						}
					}';
			$res =  json_decode($this->starQueryNP($json));
			if($res->success!=1){
				return	$this->dictionary($res->errors); 
			}
			else{
				return $res->data;	
			}
		}
	}//
	
	// распарсить ответ API новой почты
	// создать файл в кеше
	function parsingXmlCity($filename,$language ='ru',$arrayXml){
		$setingNP = $this->getSetingShipParams ();
		if($setingNP['api']==0 ){
			if ($language=='ru') $teg ='NAMERU';
			if ($language=='ua') $teg ='NAMEUKR';
			$fp = fopen($filename,"w");	
			foreach ($arrayXml as $k=>$v){
				if (in_array($teg, $v)){
					$arrCity[$v[value]] = $v[value];
					$test = fwrite($fp, $v[value]."\n");
				}
			}
			asort($arrCity);	
			fclose($fp);
			return $arrCity;
		}
		if($setingNP['api']==1 ){
			$fp = fopen($filename,"w");
			$r = fwrite($fp, $arrayXml);
			fclose($fp);
			return;
		}
	}//
	
	// создать селект из массива
	function prepareSelect($arr,$name,$value,$control_name,$addClass=''){
		dump($arr , '$arr ');
		dump($name , '$name ');
		dump( $value, ' $value');
		dump($control_name , ' $control_name');
		dump($addClass , '$addClass ');
		
		
		$class ='';
		if(!$value ){
			$class.=' data-placeholder="Выберите..." ';
			 $options[] = JHTML::_('select.option',  '', "", 'value', 'text' );
			 $options = array_merge( $options, $arr);
		}else{
			 $options =$arr;
		}
		$class.= ' class ="'.$addClass.'" ';
		$Selct = JHTML::_('select.genericlist',
						  $options, 
						  $control_name.'['.$name.']',      
						  $class, 
						  'value', 
						  'text', 
						  $value, 
						  $control_name .'_'. $name
						  );
		return	$Selct;			  
	}//
	
	
	/*function restorWarenHouse($out,$language){
		foreach ($out  as $v){
			if($language=='ru')$arr[$v->Ref] = trim ($v->DescriptionRu);
			if($language=='ua')$arr[$v->Ref] = trim ($v->Description);	
		}
		return $arrCity;
	}//*/
		
	// преобразовать названия Ref => город API 2/0
	// преобразователь для складов API 2/0
	function restorCity($out,$language){
		foreach ($out  as $v){
			if($language=='ru')$arr[$v->Ref] = trim ($v->DescriptionRu);
			if($language=='ua')$arr[$v->Ref] = trim ($v->Description);	
		}
		return $arr;
		
	}// 
	

	/*function  getCitiAdmin($name,$value,$control_name){
		$arrCity = $this->ceshHendler('city');
		return $this->prepareSelect($arrCity,$name,$value,$control_name);
		
	}*/
	
	//  получить массив с городами
	function getSelectCiti($name,$value,$control_name){
	
		$arrCity = $this->ceshHendler('city');
		
	return $arrCity ; 	
	}//

	// проверка имел ли заказ статус оплаченный чтобы отменить обратную доставку!
	function getOrderHistory($virtuemart_order_id,$order_St){
		
		return ;
		
		$statusZd = 0;
		$db = JFactory::getDBO();
		// Проверка статуса заказа
		$q = "SELECT order_status_code
				FROM #__virtuemart_order_histories
				WHERE virtuemart_order_id=".$virtuemart_order_id."
				ORDER BY virtuemart_order_history_id ASC";
		$db->setQuery($q);
		$order_history = $db->loadAssocList();
		foreach ($order_history as $v){
			if ($v['order_status_code'] == $order_St) {
				$statusZd = 1;
				break;
			}
		}
	return $statusZd ;
	}//

	//  определить order_total
	function getCostOrder ($orderId){

	


	}//

	//  получение названия способа доставки по его id
	function getShipName($id){
		$db = JFactory::getDBO();
		$q = "SELECT shipment_name
				FROM #__virtuemart_shipmentmethods_ru_ru 
				WHERE virtuemart_shipmentmethod_id=". $id."
		";
		$db->setQuery($q);
		return $db->loadResult();
	}//  


	

	// создание полного названия адреса доставки
	//  Новая Почта , Город получателя , Адрес склада
	//  $shipment_name - название способа доставки
	//  $citi_of_sender - город Получателя
	//  $nameWarenhouse = адрес склада в городе
	function getFullShipNameHtml($shipment_name,$citi_of_sender,$nameWarenhouse,$variant_deliv='WarehouseWarehouse'){
		$setingNP = $this->getSetingShipParams ();
		if($setingNP['api']==1 ){
			$arrCity = $this->ceshHendler('city');
			$citi_of_sender = $arrCity[$citi_of_sender];
		}
		$htmlName='<div class="wrNames">';
		$htmlName.='<div class="nameDeliv">'.$shipment_name.'</div>';
		$htmlName.='<div class="nameCity">'.$citi_of_sender.'</div>';
		if($variant_deliv =='WarehouseDoors'){
				$htmlName.='<div class="nameWarenhouse">Доставка на адрес '.$nameWarenhouse.'</div>';
		}else{
			$htmlName.='<div class="nameWarenhouse">'.$nameWarenhouse.'</div>';
		}
		$htmlName.='</div>';
		return $htmlName;
	}
//
	// получение информации о заказе из таблицы плагина с параметрами доставки 
	function getShipInfo($virtuemart_order_id){
		$db = JFactory::getDBO();
		$q = 'SELECT * FROM `#__virtuemart_shipment_plg_nova_pochta` '
			. 'WHERE `virtuemart_order_id` = '.$virtuemart_order_id;
		$db->setQuery($q);
		if (!($shipinfo = $db->loadObject())) {
			vmWarn(500, $q . " " . $db->getErrorMsg());
			return '';
		}
		return $shipinfo;	
	}//
	
///////////////////////////////////////////////////////////////////////////////////////////////////////////////	
	// Counterparty - Модель для работы с данными контрагента
	
	// найти контрагента отправителя в выбраном городе
	function GetCounterpartyProperty(){
		$setingNP = $this->getSetingShipParams ();
		$key_privat_np = $setingNP['key_privat_np'];
		$RefCity = JRequest::getVar('refCity');
		$property = JRequest::getVar('property','Recipient');
		
		$json = '{
					"apiKey": "'.$key_privat_np.'",
					"modelName": "Counterparty",
					"calledMethod": "getCounterparties",
					"methodProperties": 
						{
							"CounterpartyProperty":"'.$property.'",
							"CityRef":"'.$RefCity.'"
							
						}
				}';
		$r = json_decode($this->starQueryNP ($json) );
		if($r->success == 1 ){
			if(count($r->data)!=1 && $property!='Recipient' ){
				$err='Ошибка в данных контрагента отправителя';
				return $err;	
			}
			if($property=='Sender'){
				$contact = $this->CounterpartyGetCounterpartyContactPersons($r->data[0]->Ref); 
				if($contact->success!=1 ){
					$err='Ошибка в информации контактной персоны';
					return $err;	
				}
				if(count($contact->data)==0 ){
					$err = 'Контактная персона не найдена! Проверьте в личном кабинете новой почты';
					return $err;	
				}
				
				
				$d['type'] = 'Sender';
				$d['Ref']=$r->data[0]->Ref;
				$d['FirstName']=$r->data[0]->FirstName;
				$d['LastName']=$r->data[0]->LastName;
				$d['MiddleName']=$r->data[0]->MiddleName;
				$d['contPersRef']=$contact->data[0]->Ref;
				$d['contPersPhones']=$contact->data[0]->Phones;
				$d['contPersEmail']=$contact->data[0]->Email;
				return json_encode($d );
				
			}
		}else{
			$err = 'Контрагенты не найдены';
			return $err;	
		}
				
	}
	//Common - Модель для работы со справочниками ////////////////////////////////////  
	//  получить справочник описания отправления
	function  CommonGetCargoDescriptionList(){
		$setingNP = $this->getSetingShipParams ();
		$key_privat_np = $setingNP['key_privat_np'];
		$lang = $setingNP['np_language'];
		
		$json = '{
					"modelName": "Common",
					"calledMethod": "getCargoDescriptionList",
					"methodProperties": {},
					"apiKey": "'.$key_privat_np.'",
					"language": "'.$lang.'"
				}';	
		$r = json_decode($this->starQueryNP ($json) );
		if($r->success!=1 ){ 
			$err ='Ошибка при получении списка описания отправления';
			return $err;
		}
		foreach ($r->data as $v){
			if($lang=='ru' && $v->DescriptionRu ){
				$description = $v->DescriptionRu;	
			}else{
				$description = $v->Description;	
			}
			$descriptionList[$v->Ref]=trim($description);
		}
		asort( $descriptionList );
		return $descriptionList;
	}

	///////////////////////////////////////////////////////////////////////////////////
	
	
	// Создать контрагента -> получателя
	function CounterpartySave($CityRef,$LastName,$FirstName,$Phone,$Email='',$MiddleName=''){
		$setingNP = $this->getSetingShipParams ();
		$key_privat_np = $setingNP['key_privat_np'];
		
		if(empty($CounterpartyProperty)) $CounterpartyProperty='Recipient';
		$json = '{
					"apiKey": "'.$key_privat_np.'",
					"modelName": "Counterparty",
					"calledMethod": "save",
					"methodProperties": 
						{
							"CounterpartyProperty": "Recipient",
							"CityRef": "'.$CityRef.'",
							"CounterpartyType": "PrivatePerson",
							"FirstName": "'.$FirstName.'",
							"MiddleName": "'.$MiddleName.'",
							"LastName": "'.$LastName.'",
							"Phone": "'.$Phone.'",
							"Email": "'.$Email.'"
							
						}
				}';
		$r = json_decode($this->starQueryNP ($json) );
		return $r ;
	}
	
	// Удалить контрагента
	function CounterpartyDelete($Ref, $RefOldCity){
		$setingNP = $this->getSetingShipParams ();
		$key_privat_np = $setingNP['key_privat_np'];
		$json = '{
					"apiKey": "'.$key_privat_np.'",
					"modelName": "Counterparty",
					"calledMethod": "delete",
					"methodProperties": 
						{
							"Ref": "'.$Ref.'",
							"CityRef":"'.$RefOldCity.'"
						}
				}';
		$r = json_decode($this->starQueryNP ($json) );
		return $r ;	
	}
	
	// Обновление данных контрагента
	function CounterpartyUpdate($Ref,$CityRef,$LastName,$FirstName,$MiddleName,$Phone,$Email){
		$setingNP = $this->getSetingShipParams ();
		$apiKey= $setingNP['key_privat_np'];
			$json = '{
					"apiKey": "'.$apiKey.'",
					"modelName": "Counterparty",
					"calledMethod": "update",
					"methodProperties": 
						{
							"Ref":"'.$Ref.'",
							"CityRef": "'.$CityRef.'",
							"LastName": "'.$LastName.'",
							"FirstName": "'.$FirstName.'",
							"MiddleName": "'.$MiddleName.'",
							"Phone": "'.$Phone.'",
							"Email": "'.$Email.'",
							
							"CounterpartyProperty": "Recipient",
							"CounterpartyType": "PrivatePerson"
						}
					}';
		return json_decode($this->starQueryNP ($json));
	}
	
	//  получить параметры (настройки) контрагента отправителя/получателя
	function CounterpartyGetCounterpartyOptions($Ref){
		$setingNP = $this->getSetingShipParams ();
		$apiKey= $setingNP['key_privat_np'];
			$json = '{
					"apiKey": "'.$apiKey.'",
					"modelName": "Counterparty",
					"calledMethod": "getCounterpartyOptions",
					"methodProperties": 
						{
							"Ref":"'.$Ref.'"
						}
					}';
			return json_decode($this->starQueryNP ($json));
	}
		
	//  получить данные контрагентов в городе
	function CounterpartyGetCounterparties($apiKey,$CityRef,$CounterpartyProperty="Recipient"){
		$json = '{
					"apiKey": "'.$apiKey.'",
					"modelName": "Counterparty",
					"calledMethod": "getCounterparties",
					"methodProperties": {
						"CounterpartyProperty": "'.$CounterpartyProperty.'",
						"CityRef": "'.$CityRef.'"
					}
				}';
		return json_decode($this->starQueryNP ($json) );		
	}//  
	
	//  ContactPerson - Модель для создания контактного лица
	// создать контактное лицо контрагента
	function ContactPersonSave ($Ref){
		$setingNP = $this->getSetingShipParams ();
		$apiKey = $setingNP['key_privat_np'];
		$json = '{
					"apiKey": "'.$apiKey.'",
					"modelName": "ContactPerson",
					"calledMethod": "save",
					"methodProperties": {
						"Ref": "'.$Ref.'"
					}
				}';
		return json_decode($this->starQueryNP($json));		
	}//
	
	//  обновить данные контактного лица
	// $Ref - ref - контактного лица 
	// $CounterpartyRef - ref контрагента 
	function ContactPersonUpdate ($Ref,$CounterpartyRef,$LastName,$FirstName,$MiddleName,$Phone,$Email='' ){
		$setingNP = $this->getSetingShipParams ();
		$apiKey = $setingNP['key_privat_np'];
		$json = '{
					"apiKey": "'.$apiKey.'",
					"modelName": "ContactPerson",
					"calledMethod": "update",
					"methodProperties": { 
						"Ref": "'.$Ref.'",
						"CounterpartyRef": "'.$CounterpartyRef.'",
						"LastName": "'.$LastName.'",
						"FirstName": "'.$FirstName.'",
						"MiddleName": "'.$MiddleName.'",
						"Phone": "'.$Phone.'",
						"Email": "'.$Email.'"
					}
				}';
		return json_decode($this->starQueryNP($json));
	}//  
	
	// получить контактное лицо контрагента
	function CounterpartyGetCounterpartyContactPersons($Ref){
		$setingNP = $this->getSetingShipParams ();
		
		$apiKey= $setingNP['key_privat_np'];
		$json = '{
					"apiKey": "'.$apiKey.'",
					"modelName": "Counterparty",
					"calledMethod": "getCounterpartyContactPersons",
					"methodProperties": {
						"Ref": "'.$Ref.'"
					}
				}';
				
				
		
		return json_decode($this->starQueryNP ($json) );		
	}
	
		
	
	// обновление строки доставки для заказа в таблице способа доставки
	function saveApdLine(){
		$db = &JFactory::getDBO();
		$query = $db->getQuery(true);
		$query->update('#__virtuemart_shipment_plg_nova_pochta');
		$query->where('virtuemart_order_id = '.JRequest::getVar('vm_orderId').'');
		
///--------------------------------  Информация про получателя ----------------------------------------		
		//Фамилия получателя
		$query->set('last_name = '.$db->Quote(JRequest::getVar('last_name_recipient')));// фамилия получателя
		$query->set('first_name = '. $db->Quote(JRequest::getVar('first_name_recipient')));//Имя получателя
		$query->set('middle_name = '. $db->Quote(JRequest::getVar('middle_name_recipient')));//Отчество получателя
		$query->set('tel_recipient = '.$db->Quote(JRequest::getVar('tel_recipient')));//номер телефону Одержувача
///--------------------------------  Информация про отправление ---------------------------------------
		//дата відправи вантажу.
		//По замовченню – «сьогоднішня» дата  
		// $query->set('date_otpravki = '.$db->Quote(JRequest::getVar('date_otpravki')));
		// REF  города получателя
		$query->set('ship_syti_nova_poshta = '.$db->Quote(JRequest::getVar('ship_syti_nova_poshta')));
		
		// Определение способа доставки
		$getWarehouse = JRequest::getVar('variant_deliv','WarehouseWarehouse');
		if($getWarehouse=='WarehouseWarehouse')	$Warehouse = JRequest::getVar('warenhouseClient');
		if($getWarehouse=='WarehouseDoors')	$Warehouse = JRequest::getVar('adres');
		$query->set('variant_deliv = '.$db->Quote($getWarehouse) );		 
		$query->set('ship_sklad_nova_poshta = '.$db->Quote($Warehouse) );
///--------------------------------  Информация об оплате ---------------------------------------------		
		$query->set('payer = '.$db->Quote(JRequest::getVar('payer')));//За доставку платит	
		//  сумма обратной доставки
		$query->set('delivery_in_out = '.$db->Quote((JRequest::getVar('check'))?JRequest::getVar('delivery_in_out'):0.00));
		//  за обратную доставку платит
		$query->set('redelivery_payment_payer = '.$db->Quote(JRequest::getVar('reDelyPayerType')));
///--------------------------------  Информация про заказ	-------------------------------------------			
		$query->set('order_weight = '.$db->Quote(JRequest::getVar('order_weight')));//вага, кг	
		$query->set('volume_general = '.$db->Quote(JRequest::getVar('volume_general')));//Объем общий, м.куб
		$query->set('col_mest = '.$db->Quote(JRequest::getVar('col_mest')));//Количество мест в заказе
///----------------------------------------------------------------------------------------------------		
		
		
		
		
		
///-------------Обновления данных контрагента или перенос конрагента в другой город--------------------			
		$Ref = JRequest::getVar('ref_сounterparties')    ;// REF Контрагента
		$RefCity = JRequest::getVar('ship_syti_nova_poshta'); // REF города получателя
		$LastName = JRequest::getVar('last_name_recipient'); // Фамилия получателя
		$FirstName = JRequest::getVar('first_name_recipient'); //  Имя получателя
		$MiddleName = JRequest::getVar('middle_name_recipient'); //  Отчество получателя
		$Phone = JRequest::getVar('tel_recipient'); //  телефон получателя
		$Email = JRequest::getVar('email'); //  Емайл получателя
		
		// если изменили город получателя
		//  создать нового контрагента в городе
		//  удалить контрагента в старом городе
		if(JRequest::getVar('cityChenge')==1 ){
			$oldCity = JRequest::getVar('oldCity')    ; // REF города в котором удалить контрагента
			$d = $this ->CounterpartyDelete($Ref,$oldCity);// Удалить контрагента в старом городе
			$res['CounterpartyDelete']= $d; // результат удаления
		
			//  Создать контрагента в новом городе
			$r = $this ->CounterpartySave($RefCity,$LastName,$FirstName,$Phone,$Email,$MiddleName);
			$res['CounterpartySave']=$r;
						
			$query->set('ref_сounterparties = '.$db->Quote($r->data[0]->Ref));// обновить Ref контрагента
			$query->set('ref_сounterparty_contact_persons = '.$db->Quote($r->data[0]->ContactPerson->data[0]->Ref));// обновить Ref контакт. лица
			
			$res['cityChenge']= 1;
		}else{
			$res['CounterpartyUpdate'] = $this->CounterpartyUpdate($Ref,$RefCity,$LastName,$FirstName,$MiddleName,$Phone,$Email);
			$RefContPers = JRequest::getVar('ref_сounterparty_contact_persons'); // REF контактного лица контрагента 
			$res['ContactPersonUpdate'] = $this->ContactPersonUpdate ($RefContPers,$Ref, $LastName,$FirstName,$MiddleName,$Phone,$Email );			
		}	
///------------- состовление полного адреса доставки --------------------			
		// получить название способа доставки по id способа доставки
		$shipName = $this->getShipName(	JRequest::getVar('virtuemart_shipmentmethod_id'));
		$cityRPC=JRequest::getVar('ship_syti_nova_poshta');
		$WarehouseRPC = JRequest::getVar('warenhouseName'); 
		$variant_deliv = JRequest::getVar('variant_deliv');
		if($variant_deliv=='WarehouseWarehouse'){
			$htmlName = $this->getFullShipNameHtml($shipName,$cityRPC,$WarehouseRPC);
		}
		$query->set('shipment_name = '.$db->Quote($htmlName) );
		$res['html'] = $htmlName;
		
		
//-----------------------------------------	
		$db->setQuery($query);
		if (!$db->query()){JError::raiseError(500, $db->getErrorMsg());}else{return $res ;}
//-----------------------------------------		
	}//
	
	//  разница между датами в днях
	function daysLeft($date){
		// 88 дней
		$date = date_diff(new DateTime(), new DateTime($date))->days;
		return $date ;	
	}
	
	
	
	//отправка xml запроса к новой почте
	// отправка запроса для API2.0 формат JSON
	function starQueryNP ($query){
		$setingNP = $this->getSetingShipParams ();
		$ch = curl_init();
		if($setingNP['api']==0 ){ 
			curl_setopt($ch, CURLOPT_URL, 'http://orders.novaposhta.ua/xml.php');
			curl_setopt($ch, CURLOPT_HTTPHEADER, Array("Content-Type: text/xml"));
		}
		if($setingNP['api']==1 ){ 
			curl_setopt($ch, CURLOPT_URL, 'https://api.novaposhta.ua/v2.0/json/');
			curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
		}
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $query);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		$response = curl_exec($ch);
		curl_close($ch);
		if($setingNP['api']==1 ){
			$out = $response;
			return $out;
		}else{
			$p = xml_parser_create();
			xml_parse_into_struct($p, $response, $vals, $index);
			xml_parser_free($p);
			return $vals;
		}
	}//

	//   декодер из юникода	
	function jdecoder($json_str) {
     	$cyr_chars = array (
         '\u0430' => 'а', '\u0410' => 'А',
         '\u0431' => 'б', '\u0411' => 'Б',
         '\u0432' => 'в', '\u0412' => 'В',
         '\u0433' => 'г', '\u0413' => 'Г',
         '\u0434' => 'д', '\u0414' => 'Д',
         '\u0435' => 'е', '\u0415' => 'Е',
         '\u0451' => 'ё', '\u0401' => 'Ё',
         '\u0436' => 'ж', '\u0416' => 'Ж',
         '\u0437' => 'з', '\u0417' => 'З',
         '\u0438' => 'и', '\u0418' => 'И',
         '\u0439' => 'й', '\u0419' => 'Й',
         '\u043a' => 'к', '\u041a' => 'К',
         '\u043b' => 'л', '\u041b' => 'Л',
         '\u043c' => 'м', '\u041c' => 'М',
         '\u043d' => 'н', '\u041d' => 'Н',
         '\u043e' => 'о', '\u041e' => 'О',
         '\u043f' => 'п', '\u041f' => 'П',
         '\u0440' => 'р', '\u0420' => 'Р',
         '\u0441' => 'с', '\u0421' => 'С',
         '\u0442' => 'т', '\u0422' => 'Т',
         '\u0443' => 'у', '\u0423' => 'У',
         '\u0444' => 'ф', '\u0424' => 'Ф',
         '\u0445' => 'х', '\u0425' => 'Х',
         '\u0446' => 'ц', '\u0426' => 'Ц',
         '\u0447' => 'ч', '\u0427' => 'Ч',
         '\u0448' => 'ш', '\u0428' => 'Ш',
         '\u0449' => 'щ', '\u0429' => 'Щ',
         '\u044a' => 'ъ', '\u042a' => 'Ъ',
         '\u044b' => 'ы', '\u042b' => 'Ы',
         '\u044c' => 'ь', '\u042c' => 'Ь',
         '\u044d' => 'э', '\u042d' => 'Э',
         '\u044e' => 'ю', '\u042e' => 'Ю',
         '\u044f' => 'я', '\u042f' => 'Я',
		 '\u0456' => 'і', '\u0406' => 'І',
		 '\u0457'=>  'ї', '\u0404'=>  'Є',
         '\r' => '',
         '\n' => '<br />',
         '\t' => ''
     );
		foreach ($cyr_chars as $key => $value) {
       		$json_str = str_replace($key, $value, $json_str);
		}
		return $json_str;
	}//





	// getCities() - загрузить справочник городов компании «Новая Почта»
	//  
	// Для обновления данных, справочник необходимо загружать один раз в сутки
	function  getCities(){
		$npParam = $this->getSetingShipParams();
		$json='{
			"apiKey": "'.$npParam['key_privat_np'].'",
			"modelName": "Address",
			"calledMethod": "getCities",
			"methodProperties":{
				"Page": "1"
			}
		}';
		$res =  json_decode($this->starQueryNP($json));
		if($res->success!=1){
			return	$this->dictionary($res->errors); 
		}
		else{
			return $res->data;
		}
	}	
	
	
	
	
	// подготовить запрос  получения городов в API новой почты
	function xmlQueryCity(){
		
		$setingNP = $this->getSetingShipParams ();
		if($setingNP['api']==0 ){
			$query = '<?xml version="1.0" encoding="utf-8"?>
					 <file>
					 <auth>'.$setingNP['key_privat_np'].'</auth>
					 <citywarehouses/>
					 </file>';
		}
		if($setingNP['api']==1 ){ 
			if($_GET['keyNpSet']){
				$setingNP['key_privat_np']=$_GET['keyNpSet']; 
			}
			$query='{
							"apiKey": "'.$setingNP['key_privat_np'].'",
							"modelName": "Address",
							"calledMethod": "getCities",
							"methodProperties": {
								"Page": "1"
							}
						}';
		}
		return $this->starQueryNP($query);
	}//


}// end class
?>
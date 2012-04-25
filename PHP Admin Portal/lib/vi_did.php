<?php
include_once 'debug.php';

/*
Ok, the Login/Secret that you can use is DTHTest/w$dlt3st

The CustomerID is 211 for now, but this can be changed whenever. It may not even be relevant.
*/

$url = 'https://www.loginto.us/VOIP/Services/APIService.asmx?WSDL';

#$didResponse = $client->auditDIDs(array('login'=>'DTHTest', 'secret' => 'w$dlt3st'));
#print_r($didResponse);
#$auditResponse = $didResponse->auditDIDsResult;
#print_r($auditResponse->DIDs);
#$didList = $didResponse['DIDs'];
class VI_Client{
	var $client;
	var $login;
	var $secret;
	
	function VI_Client($user, $pwd){
		global $url;
		$this->client = new SoapClient($url);
		$this->login = $user;
		$this->secret = $pwd;
	}
	/*
	public function GetDidCount($state, $lata, $ratecenter, $npa, $nxx, $tier, $t38, $cnam){
		print_debug('Getting a count of DIDs : ' . $this->login);
		$result = $this->client->getDIDCount(
			array(	'login'=>$this->login, 
					'secret' => $this->secret,
					'state' => $state,
					'lata' => $lata,
					'rateCenter' => $ratecenter,
					'npa' => $npa,
					'nxx' => $nxx,
					'tier' => $tier,
					't38' => $t38,
					'cnam' => $cnam));
		$DIDCountResult = $result->getDIDCountResult;
		if($DIDCountResult->responseMessage != 'Success'){
			return 0;
		}
		return $DIDCountResult->DIDLocators;
	}*/
	
	public function getDIDs($state, $lata, $ratecenter, $npa, $nxx, $tier, $t38, $cnam){
		print_debug('Getting a DID list');
		set_time_limit(0);
		$result = $this->client->getDIDs(
			array(	'login'=>$this->login, 
					'secret' => $this->secret,
					'state' => $state,
					'lata' => $lata,
					'rateCenter' => $ratecenter,
					'npa' => $npa,
					'nxx' => $nxx,
					'tier' => $tier,
					't38' => $t38,
					'cnam' => $cnam)
			);
		$didResult = $result->getDIDsResult;
		if($didResult->responseMessage != 'Success'){
			throw new Exception('Error ' . $didResult->responseCode . ': ' . $didResult->responseMessage);
			return false;
		}
		return $didResult->DIDLocators->DIDLocator;
	}
	
	public function reserveDIDs($tnList){
		$didList = array();
		foreach($tnList as $tn){
			$didList[] = array('epg' => 30,
								'tn' => $tn);
		}
		$params = array( 	'login'=>$this->login, 
							'secret' => $this->secret,
							'didParams' => $didList);
		$result = $this->client->reserveDID($params);
		return $result;
	}
	
	public function assignDIDs($tnList, $epg){
		$didList = array();
		foreach($tnList as $tn){
			$didList[] = array('epg' => $epg,
								'tn' => $tn);
		}
		$params = array( 	'login'=>$this->login, 
							'secret' => $this->secret,
							'didParams' => $didList);
		$result = $this->client->assignDID($params);
		$didResult = $result->assignDIDResult;
		
		if($didResult->responseMessage != 'Success'){
			throw new Exception('Error ' . $didResult->responseCode . ': ' . $didResult->responseMessage);
			return false;
		}
		$didStatuses = $didResult->DIDs->DID;
		$output = array();
		if(gettype($didStatuses) != "array"){
			$output[] = array('tn' => $didStatuses->tn, 'status' => $didStatuses->status, 'statusCode' => $didStatuses->statusCode);
		}
		else{
			foreach($didStatuses as $status){
				$output[] = array('tn' => $status->tn, 'status' => $status->status, 'statusCode' => $status->statusCode);
			}
		}
		return $output;
	}
}
?>
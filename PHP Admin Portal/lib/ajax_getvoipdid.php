<?php

include_once 'vi_did.php';
include_once '../vars/voip_login_info.php';
$client = new VI_Client($vi_user,$vi_pass);
$state = '';
if(isset($_GET['state'])){
	$state = $_GET['state'];
}
$lata = '';
if(isset($_GET['lata'])){
	$lata = $_GET['lata'];
}
$ratecenter = '';
if(isset($_GET['ratecenter'])){
	$ratecenter = $_GET['ratecenter'];
}
$npa = '';
$nxx = '';
$tier = '';
if(isset($_GET['tier'])){
	$tier = $_GET['tier'];
}
$t38 = '';
$cnam = '';
if(isset($_GET['function'])){
	$function = $_GET['function'];
	if($function == 'count'){
		$out = $client->GetDIDs($state, $lata, $ratecenter, $npa, $nxx, $tier, $t38, $cnam);
		if($out == false){
			return;	
		}
		echo count($out);
	}
	else if($function == 'ratecenter'){
		$options = '<option value=""> All </option>';
		$out = $client->GetDIDs($state, $lata, '', $npa, $nxx, $tier, $t38, $cnam);
		$ratecenters = array();
		foreach($out as $did){
			$ratecenters[] = $did->rateCenter;
		}
		
		$ratecenters = array_unique($ratecenters);
		foreach($ratecenters as $rc){
			if($ratecenter == $rc){
				$options .= '<option selected="selected" value="'.$rc.'"> '.$rc.' </option>';
			}
			else{
				$options .= '<option value="'.$rc.'"> '.$rc.' </option>';
			}
		}
		echo $options;
	}
	else if($function == 'tier'){
	$options = '<option value=""> All </option>';
		$out = $client->GetDIDs($state, $lata, $ratecenter, $npa, $nxx, '', $t38, $cnam);
		$tiers = array();
		foreach($out as $did){
			$tiers[] = $did->tier;
		}
		
		$tiers = array_unique($tiers);
		foreach($tiers as $t){
			if($tier == $t){
				$options .= '<option selected="selected" value="'.$t.'"> '.$t.' </option>';
			}
			else{
				$options .= '<option value="'.$t.'"> '.$t.' </option>';
			}
		}
		echo $options;
	}
	
	else if($function == 'lata'){
	$options = '<option value=""> All </option>';
		$out = $client->GetDIDs($state, '', $ratecenter, $npa, $nxx, $tier, $t38, $cnam);
		
		$latas = array();
		foreach($out as $did){
			$latas[] = $did->lataId;
		}
		
		$latas = array_unique($latas);
		foreach($latas as $l){
			if($lata == $l){
				$options .= '<option selected="selected" value="'.$l.'"> '.$l.' </option>';
			}
			else{
				$options .= '<option value="'.$l.'"> '.$l.' </option>';
			}
		}
		echo $options;
	}
	else if($function == 'showall'){
		$table = <<< HEREDOC
		<table id="listcostumer-table" border="0" cellspacing="0" cellpadding="0">
		<thead><tr>
		<th>Telephone Number</th>
		<th>Rate Center</th>
		<th>State</th>
		<th>Tier</th>
		<th>LATA ID</th>
		<th>Outbound CNAM</th>
		<th>t38</th>
		</tr></thead>
HEREDOC;
		$out = $client->GetDIDs($state, $lata, $ratecenter, $npa, $nxx, $tier, $t38, $cnam);
		if($out == false){
			return;
		}
		foreach($out as $did){
		/*[tn] [rateCenter]  [state]  [tier]  [lataId]  [outboundCNAM]  [t38] */
			$table .= <<< HEREDOC
			<tr>
			<td>{$did->tn}</td>
			<td>{$did->rateCenter}</td>
			<td>{$did->state}</td>
			<td>{$did->tier}</td>
			<td>{$did->lataId}</td>
			<td>{$did->outboundCNAM}</td>
			<td>{$did->t38}</td>
			</tr>
HEREDOC;
		}
		$table .= '</table>';
		echo $table;
	}
	else if($function == 'raw'){
		$out = $client->GetDIDs($state, $lata, $ratecenter, $npa, $nxx, $tier, $t38, $cnam);
		return $out;
	}
	else{
		echo 'error';
	}
}
?>
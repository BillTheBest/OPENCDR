<?php
	include 'config.php';
	include 'lib/SQLQueryFuncs.php';
	include_once 'DAL/table_callrecordmaster_tbr.php';
	include 'lib/Page.php';
	
	$content = '';
	$table = new psql_callrecordmaster_tbr($connectstring);
	$table->Connect();
	$numberOfRows = $table->CountRatingQueue();
	
	$offset = 0;
	if(isset($_POST["offset"])){
		$offset = $_POST["offset"];
	}
	$query = <<< HEREDOC
		SELECT *
			FROM callrecordmaster_tbr WHERE calltype is not NULL
			order by calldatetime
HEREDOC;
	$limit = 1000;
	$endoffset = min($offset + $limit, $numberOfRows);
	$prevoffset = max($offset - $limit, 0);
	$allArrayResults = $table->SelectRatingQueue($offset, $limit);
	
	$titlespiped = "CallID,CustomerID,CallType,CallDateTime,Duration,Direction,SourceIP,OriginationNumber,DestinationNumber,LRN,CNAMDipped,RateCenter,CarrierID";
	$titles = preg_split("/,/",$titlespiped,-1);
	$htmltable = AssocArrayToTable($allArrayResults,$titles); 
	$table->Disconnect();
?> 

<?php

$javaScripts = <<< HEREDOC
<script type="text/javascript">

</script>
HEREDOC;
 ?>
 
		<?php echo GetPageHead("List Results TBR", "main.php", $javaScripts);?>
		
		<div id="body">
			
			<br>
				<input type="submit" class="btn blue add-customer" value="Rate Calls" id="ratebutton"/>
			<form name="export" action="exportpipe.php" method="post">
				<input type="submit" class="btn orange export" value="Export Table">
				<input type="hidden" name="queryString" value="<?php echo htmlspecialchars($query);?>">
				<input type="hidden" name="filename" value="TBRExport.csv">
			</form>
			<br>
			<form action="lib/TBRLibs.php" method="post" id="action">
				<select name="type" id="type">
					<option value="bandwidth">Bandwidth</option>
					<option value="vitelity">Vitelity</option>
					<option value="thinktel">Thinktel</option>
					<option value="telastic">Telastic</option>
					<option value="sansay">Sansay</option>
					<option value="nextone">Nextone</option>
					<option value="netsapiens">NetSapiens</option>
					<option value="telepo">Telepo</option>
					<option value="aretta">Aretta</option>
					<option value="slinger">Slinger</option>
					<option value="cisco">Cisco Call Manager 7.1</option>
					<option value="voip">VOIP Innovations</option>
					<option value="asterisk">Asterisk</option>
					<option value="itel">iTel</option>
				</select>
				<input name="uploadedFile" type="File" id="fileselect" />
			</form>
			<button id="uploadbutton" type="submit">Import File </button>
			<br>
			<div id="progress"></div>
			<div id="messages"></div>
			<?php
			$limitOptions = <<< HEREDOC
		Showing rows : {$offset} to {$endoffset} <br>
		Total number of rows : {$numberOfRows}
		<br>
HEREDOC;
		if($offset > 0){
			$limitOptions .= '
			<form action="listresultstbr.php" method="post" style=\'margin: 0; padding: 0; display:inline;\'>
			<input type="hidden" name="offset" value="'.$prevoffset.'">
			<input type="submit" value="View prev '.$limit.' results"/>
			</form>';
		}
		if($endoffset < $numberOfRows){
		$limitOptions .= '
		<form action="listresultstbr.php" method="post" style=\'margin: 0; padding: 0; display:inline;\'>
		<input type="hidden" name="offset" value="'.$endoffset.'">
		<input type="submit" value="View next '.$limit.' results"/>
		</form>';
		}
		echo $limitOptions;
			?>
			<?php echo $htmltable; ?>
	
		</div>
	
<script>

/*
"messages"
"fileselect"
"progress"
"action"
"uploadbutton"
*/
(function() {

	// getElementById
	function $id(id) {
		return document.getElementById(id);
	}

	// output information
	function Output(msg) {
		var m = $id("messages");
		m.innerHTML = msg;
	}
	
	function EnableProgress(value, max){
		var p = $id("progress");
		p.innerHTML = '<progress value="'+value+'" max="'+max+'"/>';
	}
	function HideProgress(){
		var p = $id("progress");
		p.innerHTML = '';
	}
	
	function UploadHandler(e) {
		var fileselect = $id("fileselect");
		var file = fileselect.files[0];
		//alert(file.name);
		//UploadFileByLine(file);
		UploadFile(file);
	}

	// upload JPEG files
	function UploadFile(file) {
		var total = file.size;
		var xhr = new XMLHttpRequest();
			// create progress bar
			EnableProgress(0,100);

			// progress bar
			xhr.addEventListener("progress", function(e) {
				var pc = 100;
				if (e.lengthComputable) { 
					pc = (e.loaded / e.total * 100);
				}
				else{
					pc = e.loaded / total * 100;
				}
					Output('Progress : ' + Math.round(pc*100)/100 + '%');
				EnableProgress(pc,100);
			}, false);

			// file received/failed
			xhr.onreadystatechange = function(e) {
				if (xhr.readyState == 4) {
					if(xhr.status == 200){
						HideProgress();
						Output("Done : <A HREF=\"javascript:location.reload(true)\">Refresh</a><br>");
					}
					else{
						Output("Error code : " + xhr.status);
					}
				}
				else{
					Output("<blink>Please wait...</blink>");
				}
			};

			// start upload
			xhr.open("POST", $id("action").action, true);
			xhr.setRequestHeader("X_FILESIZE", file.size);
			xhr.setRequestHeader("X_FILENAME", file.name);
			xhr.setRequestHeader("TYPE", $id("type").value);
			xhr.send(file);
	}
	function confirmRateCalls(arg){
		var agree=confirm("This will rate 1 day worth of CDR for each call type. This may take several minutes to run.");
		if (agree){
			// create progress bar
			EnableProgress(0,8);
			CategorizeCDR();
		}
		else{
		}
	}
	function CategorizeCDR(){
		Output('<blink>Categorizing CDR. <br>Please wait...</blink>');
		var xhr = new XMLHttpRequest();
		xhr.onreadystatechange = function(e) {
			if (xhr.readyState == 4){
				if(xhr.status == 200){
					if(!xhr.responseText){
						EnableProgress(1,8);
						Output("CategorizeCDR Done<br>");
						RateIndeterminateJurisdictionCDR();
					}
					else{
						Output(!xhr.responseText);
						HideProgress();
					}
				}
				else{
					Output("Error code : " + xhr.status);
					HideProgress();
				}
			}
		};
		xhr.open("POST", "lib/RunSP.php", true);
		xhr.setRequestHeader("X_SPNAME", 'fnCategorizeCDR');
		xhr.send();
	}
	function RateIndeterminateJurisdictionCDR(){
		Output('<blink>Rating Indeterminate CDR. <br>Please wait...</blink>');
		var xhr = new XMLHttpRequest();
		xhr.onreadystatechange = function(e) {
			if (xhr.readyState == 4){
				if(xhr.status == 200){
					if(!xhr.responseText){
						EnableProgress(2,8);
						Output("RateIndeterminateJurisdictionCDR Done<br>");
						RateInternationalCDR();
					}
					else{
						Output(xhr.responseText);
						HideProgress();
					}
				}
				else{
					Output("Error code : " + xhr.status);
					HideProgress();
				}
			}
		};
		xhr.open("POST", "lib/RunSP.php", true);
		xhr.setRequestHeader("X_SPNAME", 'fnRateIndeterminateJurisdictionCDR');
		xhr.send();
	}
	function RateInternationalCDR(){
		Output('<blink>Rating International CDR. <br>Please wait...</blink>');
		var xhr = new XMLHttpRequest();
		xhr.onreadystatechange = function(e) {
			if (xhr.readyState == 4){
				if(xhr.status == 200){
					if(!xhr.responseText){
						EnableProgress(3,8);
						Output("RateIndeterminateJurisdictionCDR Done<br>");
						RateInterstateCDR();
					}
					else{
						Output(xhr.responseText);
						HideProgress();
					}
				}
				else{
					Output("Error code : " + xhr.status);
					HideProgress();
				}
			}
		};
		xhr.open("POST", "lib/RunSP.php", true);
		xhr.setRequestHeader("X_SPNAME", 'fnRateIndeterminateJurisdictionCDR');
		xhr.send();
	}
	function RateInterstateCDR(){
		Output('<blink>Rating Interstate CDR. <br>Please wait...</blink>');
		var xhr = new XMLHttpRequest();
		xhr.onreadystatechange = function(e) {
			if (xhr.readyState == 4){
				if(xhr.status == 200){
					if(!xhr.responseText){
						EnableProgress(4,8);
						Output("RateInterstateCDR Done<br>");
						RateIntrastateCDR();
					}
					else{
						Output(xhr.responseText);
						HideProgress();
					}
				}
				else{
					Output("Error code : " + xhr.status);
					HideProgress();
				}
			}
		};
		xhr.open("POST", "lib/RunSP.php", true);
		xhr.setRequestHeader("X_SPNAME", 'fnRateInterstateCDR');
		xhr.send();
	}
	function RateIntrastateCDR(){
		Output('<blink>Rating Intrastate CDR. <br>Please wait...</blink>');
		var xhr = new XMLHttpRequest();
		xhr.onreadystatechange = function(e) {
			if (xhr.readyState == 4){
				if(xhr.status == 200){
					if(!xhr.responseText){
						EnableProgress(5,8);
						Output("RateIntrastateCDR Done<br>");
						RateSimpleTerminationCDR();
					}
					else{
						Output(xhr.responseText);
						HideProgress();
					}
				}
				else{
					Output("Error code : " + xhr.status);
					HideProgress();
				}
			}
		};
		xhr.open("POST", "lib/RunSP.php", true);
		xhr.setRequestHeader("X_SPNAME", 'fnRateIntrastateCDR');
		xhr.send();
	}
	function RateSimpleTerminationCDR(){
		Output('<blink>Rating Simple Termination CDR. <br>Please wait...</blink>');
		var xhr = new XMLHttpRequest();
		xhr.onreadystatechange = function(e) {
			if (xhr.readyState == 4){
				if(xhr.status == 200){
					if(!xhr.responseText){
						EnableProgress(6,8);
						Output("RateSimpleTerminationCDR Done<br>");
						RateTieredOriginationCDR();
					}
					else{
						Output(xhr.responseText);
						HideProgress();
					}
				}
				else{
					Output("Error code : " + xhr.status);
					HideProgress();
				}
			}
		};
		xhr.open("POST", "lib/RunSP.php", true);
		xhr.setRequestHeader("X_SPNAME", 'fnRateSimpleTerminationCDR');
		xhr.send();
	}
	function RateTieredOriginationCDR(){
		Output('<blink>Rating Tiered Origination CDR. <br>Please wait...</blink>');
		var xhr = new XMLHttpRequest();
		xhr.onreadystatechange = function(e) {
			if (xhr.readyState == 4){
				if(xhr.status == 200){
					EnableProgress(7,8);
					if(!xhr.responseText){
						Output("RateTieredOriginationCDR Done<br>");
						RateTollFreeOriginationCDR();
					}
					else{
						Output(xhr.responseText);
						HideProgress();
					}
				}
				else{
					Output("Error code : " + xhr.status);
					HideProgress();
				}
			}
		};
		xhr.open("POST", "lib/RunSP.php", true);
		xhr.setRequestHeader("X_SPNAME", 'fnRateTieredOriginationCDR');
		xhr.send();
	}
	function RateTollFreeOriginationCDR(){
		Output('<blink>Rating Toll Free Origination CDR. <br>Please wait...</blink>');
		var xhr = new XMLHttpRequest();
		xhr.onreadystatechange = function(e) {
			if (xhr.readyState == 4){
				if(xhr.status == 200){
					if(!xhr.responseText){
						HideProgress();
						Output(" All Done<br>");
					}
					else{
						Output(xhr.responseText);
						HideProgress();
					}
				}
				else{
					Output("Error code : " + xhr.status);
					HideProgress();
				}
			}
		};
		xhr.open("POST", "lib/RunSP.php", true);
		xhr.setRequestHeader("X_SPNAME", 'fnRateTollFreeOriginationCDR');
		xhr.send();
	}
	
	// initialize
	function Init() {

		var fileselect = $id("fileselect"),
			uploadbutton = $id("uploadbutton"),
			ratebutton = $id("ratebutton");

		// file select
		//fileselect.addEventListener("change", FileSelectHandler, false);
		ratebutton.addEventListener("click",confirmRateCalls, false);
		uploadbutton.addEventListener("click", UploadHandler, false);
	}

	// call initialization file
	if (window.File && window.FileList && window.FileReader) {
		Init();
	}
})();
</script>
	<?php echo GetPageFoot();?>
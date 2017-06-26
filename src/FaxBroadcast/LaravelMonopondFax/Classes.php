<?php
namespace FaxBroadcast\LaravelMonopondFax;
	class MPENV {
		const PRODUCTION = "https://api.monopond.com/fax/soap/v2.1/?wsdl";
		const PRODUCTION_MONOPOND = "https://faxapi.monopond.com/api/fax/v2.1?wsdl";
		const TEST = "http://test.api.monopond.com/fax/soap/v2.1/?wsdl";
		const LOCAL = "http://localhost:8000/fax/soap/v2.1?wsdl";
	}

	class clsWSSEToken {
		private $UsernameToken;
		
		function __construct ($innerVal) {
		  $this->UsernameToken = $innerVal;
		}
	}

	class MonopondMergeField {
	    public $Key;
	    public $Value;
	}

	class MonopondStampMergeField {
		public $StampMergeFieldKey;
		public $TextValue;
		public $ImageValue;
	}

	class MonopondStampMergeFieldKey {
		public $xCoord;
		public $yCoord;
	}

	class MonopondStampMergeFieldTextValue {
		public $fontName;
		public $fontSize;
		public $Value;
	}

	class MonopondStampMergeFieldImageValue {
		public $FileName;
		public $FileData;
		public $width;
		public $height;
	}

	class MonopondDocument {
		public $DocumentRef;
		public $FileName;
		public $FileData;
		public $Order;
		public $DitheringTechnique;
		public $DocMergeData;
		public $StampMergeData;
	}

	class MonopondFaxMessage {
		public $MessageRef;
		public $SendTo;
		public $SendFrom;
		public $Documents;
		public $Resolution;
		public $Blocklists;
		public $ScheduledStartTime;
		public $Retries;
		public $BusyRetries;
		public $HeaderFormat;
		public $MustBeSentBeforeDate;
		public $MaxFaxPages;
		public $CLI;
		public $TimeZone;
	}

	class MonopondBlocklist {
		public $dncr;
		public $fps;
		public $smartblock;
	}

	class MonopondFaxDetailsResponse {
		public $sendFrom;
		public $resolution;
		public $retries;
		public $busyRetries;
		public $headerFormat;

		function __construct($response) {
			$this->sendFrom = (string)$response["sendFrom"][0];
			$this->resolution = (string)$response["resolution"][0];
			$this->retries = (string)$response["retries"][0];
			$this->busyRetries = (string)$response["busyRetries"][0];
			$this->headerFormat = (string)$response["headerFormat"][0];
		}
	}

	class MonopondFaxResultsResponse {
		public $attempt;
		public $result;
		public $error;
		public $cost;
		public $pages;
		public $scheduledStartTime;
		public $dateCallStarted;
		public $dateCallEnded;

		function __construct($response) {
			$this->attempt = (string)$response["attempt"][0];
			$this->result = (string)$response["result"][0];
			$this->error = new MonopondFaxErrorResponse($response->Error);
			$this->cost = (string)$response["cost"][0];
			$this->pages = (string)$response["pages"][0];
			$this->scheduledStartTime = (string)$response["scheduledStartTime"][0];
			$this->dateCallStarted = (string)$response["dateCallEnded"][0];
		}
	}

	class MonopondFaxErrorResponse {
		public $code;
		public $name;


		function __construct($response) {
			$this->code = (string)$response["code"][0];
			$this->name = (string)$response["name"][0];
		}
	}

	class MonopondFaxMessageResponse {    
		public $status;
		public $sendTo;
		public $broadcastRef;
		public $sendRef;
		public $messageRef;
		public $faxDetails; 
		public $faxResults;
	 
		function __construct($faxMessageResponse) {
			$this->status = (string)$faxMessageResponse["status"][0];
			$this->sendTo = (string)$faxMessageResponse["sendTo"][0];
			$this->broadcastRef = (string)$faxMessageResponse["broadcastRef"][0];
			$this->sendRef = (string)$faxMessageResponse["sendRef"][0];
			$this->messageRef = (string)$faxMessageResponse["messageRef"][0];

			if ($faxMessageResponse->FaxDetails != null) {
			   $this->faxDetails = new MonopondFaxDetailsResponse($faxMessageResponse->FaxDetails);
			}

			if (!empty($faxMessageResponse->FaxResults)) {
			   foreach($faxMessageResponse->FaxResults->FaxResult as $faxResult) {
				$this->faxResults[] = new MonopondFaxResultsResponse($faxResult);
			   }
			}
		}
	}

	/* SendFax */
	class MonopondSendFaxRequest{
		public $BroadcastRef;
		public $SendRef;
		public $FaxMessages;
		public $Documents;
		public $Resolution;
		public $Blocklists;
		public $SendFrom;
		public $ScheduledStartTime;
		public $Retries;
		public $BusyRetries;
		public $HeaderFormat;
		public $MustBeSentBeforeDate;
		public $MaxFaxPages;
		public $CLI;
		public $TimeZone;
	}

	class MonopondSendFaxResponse{   
		public $FaxMessages;

		function __construct($responses) {
			foreach($responses->FaxMessage as $response) {
				$this->FaxMessages[] = new MonopondFaxMessageResponse($response);
			}   
		}
	}

	/* FaxStatus */
	class MonopondFaxStatusRequest {
		public $MessageRef;
		public $SendRef;
		public $BroadcastRef;
		public $Verbosity = "brief";
	}

	class MonopondFaxStatusResponse {
		public $FaxStatusTotals;
		public $FaxResultsTotals;
		public $FaxMessages;

		function __construct($response) {
			$this->FaxStatusTotals = new MonopondFaxStatusTotalsResponse($response->FaxStatusTotals);
			$this->FaxResultsTotals = new MonopondFaxResultsTotalsResponse($response->FaxResultsTotals);

			if (!empty($response->FaxMessages)) {
			   foreach ($response->FaxMessages->FaxMessage as $faxMessage) {                
				$this->FaxMessages[] =  new MonopondFaxMessageResponse($faxMessage);
			   } 
			}
		}
	}

	class MonopondFaxStatusTotalsResponse {
		public $pending;
		public $processing;
		public $queued;
		public $starting;
		public $sending;
		public $pausing;
		public $paused;
		public $resuming;
		public $stopping;
		public $finalizing;
		public $done;
		
		function __construct($response) {
			$this->pending = (string)$response["pending"][0];
			$this->processing = (string)$response["processing"][0];
			$this->queued = (string)$response["queued"][0];
			$this->starting = (string)$response["starting"][0];
			$this->sending = (string)$response["sending"][0];
			$this->pausing = (string)$response["pausing"][0];
			$this->paused = (string)$response["paused"][0];
			$this->resuming = (string)$response["resuming"][0];
			$this->stopping = (string)$response["stopping"][0];
			$this->finalizing = (string)$response["finalizing"][0];
			$this->done = (string)$response["done"][0];
		}        
	}

	class MonopondFaxResultsTotalsResponse {
		public $success;
		public $blocked;
		public $failed;
		public $totalAttempts;
		public $totalFaxDuration;
		public $totalPages;
		
		function __construct($response) {
			$this->success = (string)$response["success"][0];
			$this->blocked = (string)$response["blocked"][0];
			$this->failed = (string)$response["failed"][0];
			$this->totalAttempts = (string)$response["totalAttempts"][0];
			$this->totalFaxDuration = (string)$response["totalFaxDuration"][0];
			$this->totalPages = (string)$response["totalPages"][0];
		}
	}

	/* StopFax */
	class MonopondStopFaxRequest {
		public $MessageRef;
		public $SendRef;
		public $BroadcastRef;
	}

	class MonopondStopFaxResponse {
		public $FaxMessages;
		
		function __construct($responses) {
			foreach($responses->FaxMessages->FaxMessage as $response) {
				$this->FaxMessages[] = new MonopondFaxMessageResponse($response);
			}   
		}
	}

	/* PauseFax */
	class MonopondPauseFaxRequest {
		public $MessageRef;
		public $SendRef;
		public $BroadcastRef;
	}

	class MonopondPauseFaxResponse {
		public $FaxMessages;
		
		function __construct($responses) {
			foreach($responses->FaxMessages->FaxMessage as $response) {
				$this->FaxMessages[] = new MonopondFaxMessageResponse($response);
			}   
		}
	}

	/* ResumeFax */
	class MonopondResumeFaxRequest {
		public $MessageRef;
		public $SendRef;
		public $BroadcastRef;
	}

	class MonopondResumeFaxResponse {
		public $FaxMessages;
		
		function __construct($responses) {
			foreach($responses->FaxMessages->FaxMessage as $response) {
				$this->FaxMessages[] = new MonopondFaxMessageResponse($response);
			}   
		}
	}

	class MonopondSaveFaxDocumentRequest {
		public $DocumentRef;
		public $FileName;
		public $FileData;
	}

	class MonopondSaveFaxDocumentResponse {
	}

	class MonopondFaxDocumentPreviewRequest {
		public $DocumentRef;
		public $Resolution;
		public $DitheringTechnique;
		public $DocMergeData;
		public $StampMergeData;
	}

	class MonopondFaxDocumentPreviewResponse {

		public $TiffPreview;
		public $NumberOfPages;

		function __construct($response) {
			$this->TiffPreview = (string)$response[0]->TiffPreview;
			$this->NumberOfPages = (string)$response[0]->NumberOfPages;
		}
	}

	class MonopondDeleteFaxDocumentRequest {

		public $DocumentRef;
		public $MessageRef;
		public $SendRef;
		public $BroadcastRef;
	}

	class MonopondDeleteFaxDocumentResponse {

	}
?>

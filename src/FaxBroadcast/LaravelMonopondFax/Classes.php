<?php
namespace FaxBroadcast\LaravelMonopondFax;

use SimpleXMLElement;

class MPENV {
		const PRODUCTION = "https://api.monopond.com/fax/soap/v2.1/?wsdl";
		const Production = "https://faxapi.monopond.com/api/fax/v2.1?wsdl";
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
            Helper::convertResponse($this, $response);
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
            Helper::convertResponse($this, $response);
			$this->error = new \FaxBroadcast\LaravelMonopondFax\MonopondFaxErrorResponse($response->Error);
		}
	}

	class MonopondFaxErrorResponse {
		public $code;
		public $name;


		function __construct($response) {
            Helper::convertResponse($this, $response);
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
            Helper::convertResponse($this, $faxMessageResponse);

			if ($faxMessageResponse->FaxDetails != null) {
			   $this->faxDetails = new \FaxBroadcast\LaravelMonopondFax\MonopondFaxDetailsResponse($faxMessageResponse->FaxDetails);
			}

			if (!empty($faxMessageResponse->FaxResults)) {
			   foreach($faxMessageResponse->FaxResults->FaxResult as $faxResult) {
				$this->faxResults[] = new \FaxBroadcast\LaravelMonopondFax\MonopondFaxResultsResponse($faxResult);
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
				$this->FaxMessages[] = new \FaxBroadcast\LaravelMonopondFax\MonopondFaxMessageResponse($response);
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
            $this->FaxStatusTotals = new \FaxBroadcast\LaravelMonopondFax\MonopondFaxStatusTotalsResponse($response->FaxStatusTotals);
			$this->FaxResultsTotals = new \FaxBroadcast\LaravelMonopondFax\MonopondFaxResultsTotalsResponse($response->FaxResultsTotals);

			if (!empty($response->FaxMessages)) {
			   foreach ($response->FaxMessages->FaxMessage as $faxMessage) {
				$this->FaxMessages[] =  new \FaxBroadcast\LaravelMonopondFax\MonopondFaxMessageResponse($faxMessage);
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
            Helper::convertResponse($this, $response);
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
            Helper::convertResponse($this, $response);
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
				$this->FaxMessages[] = new \FaxBroadcast\LaravelMonopondFax\MonopondFaxMessageResponse($response);
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
				$this->FaxMessages[] = new \FaxBroadcast\LaravelMonopondFax\MonopondFaxMessageResponse($response);
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
				$this->FaxMessages[] = new \FaxBroadcast\LaravelMonopondFax\MonopondFaxMessageResponse($response);
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

	class Test {
		function __construct() {
			$a = new Test2;
		}
	}

	class Test2 {

	}

    class Helper {
        public static function convertResponse(&$class, SimpleXMLElement $response) {
            $response_obj = json_decode(json_encode($response), 'true');
            foreach ($response_obj['@attributes'] as $key => $value) {
                $class->{$key} = $value;
            }
        }
    }
?>

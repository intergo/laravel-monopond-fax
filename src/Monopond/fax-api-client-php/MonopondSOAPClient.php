
<?php

	class MonopondSOAPClientV2_1 {
		private $_username;
		private $_password;
		private $_wsdl;
		private $_SoapClient;
		private $_strWSSENS = "http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd";
			
		function __construct($username, $password, $wsdl) {
			// Setup monopond API credentials
			$this->_username=$username;
			$this->_password=$password;
			$this->_wsdl = $wsdl;
			
			// Setting up SOAP ready headers with authentication
			$WSSEAuth = new SoapVar(array('ns1:Username' => $this->_username,'ns1:Password' => $this->_password),
									SOAP_ENC_OBJECT,NULL,$this->_strWSSENS,NULL, $this->_strWSSENS);
			$WSSEToken = new \FaxBroadcast\LaravelMonopondFax\clsWSSEToken($WSSEAuth);
			$SoapVarWSSEToken = new SoapVar($WSSEToken,SOAP_ENC_OBJECT,NULL,$this->_strWSSENS,NULL,$this->_strWSSENS);
			$SoapVarHeaderVal=new SoapVar($SoapVarWSSEToken,SOAP_ENC_OBJECT,NULL,$this->_strWSSENS,NULL,$this->_strWSSENS);
			$SoapHeader = new SoapHeader($this->_strWSSENS,'Security',$SoapVarHeaderVal,true);
			
			$context = stream_context_create(array(
		        'ssl' => array(
		        'verify_peer' => false,
		        'verify_peer_name' => false,
		        'allow_self_signed' => true
	   		 	)
			));
			
			// Creating the SOAP client 
			$this->_SoapClient = new SoapClient($this->_wsdl, array(
				"trace" => 1, 
				"stream_context" => $context
				)
			);
			$this->_SoapClient->__setSoapHeaders(array($SoapHeader));
		}
			
		private function convertDocumentArrayToSoapArray($documentArray) {
			// Initialise a blank array
			$soapDocuments = array();
				
			// Setup Documents as SOAP Objects
			foreach($documentArray as $document) {

				// Makes each individual document into a SOAP ready object

				if(!$document->DocumentRef) {
					$document = $this->removeNullValues($document);
				}

				if(@!$document->DitheringTechnique) {
					$document = $this->removeNullValues($document);
				}

				$documentXmlString = '<Document>';
				if(@$document->DocumentRef != null) {
					$documentXmlString .= '<DocumentRef>'.$document->DocumentRef.'</DocumentRef>';
				}

				if(@$document->FileName != null) {
					$documentXmlString .= '<FileName>'.$document->FileName.'</FileName>';
				}

				if(@$document->FileData != null) {
					$documentXmlString .= '<FileData>'.$document->FileData.'</FileData>';
				}

				$documentXmlString .= '<Order>'.@$document->Order.'</Order>';

				if(@$document->DitheringTechnique != null) {
					$documentXmlString .= '<DitheringTechnique>'.$document->DitheringTechnique.'</DitheringTechnique>';
				}

				if(!empty($document->DocMergeData)) {
					$documentXmlString .= $this->convertDocMergeFieldArrayToSoapString($document->DocMergeData);
				}

				if(!empty($document->StampMergeData)) {
					$documentXmlString .= $this->convertStampMergeFieldArrayToSoapString($document->StampMergeData);
				}

				$documentXmlString .= '</Document>';
				$soapDocument = new SoapVar($documentXmlString , XSD_ANYXML);
				$soapDocuments[] = $soapDocument;
			}

			// Make documents array SOAP ready
			$soapDocuments = new SoapVar($soapDocuments,SOAP_ENC_OBJECT);

			return $soapDocuments;
		}

		private function convertDocMergeFieldArrayToSoapString($docMergeFieldArray) {
			$docMergeXmlString = '<DocMergeData>';
			
			foreach ($docMergeFieldArray as $docMergeField) {
				if($docMergeField->Key != null || $docMergeField->Value != null) {
					$docMergeXmlString .= '<MergeField>';
					$docMergeXmlString .= '<Key>'.$docMergeField->Key.'</Key>';
					$docMergeXmlString .= '<Value>'.$docMergeField->Value.'</Value>';
					$docMergeXmlString .= '</MergeField>';
				}
			}
			$docMergeXmlString .= '</DocMergeData>';
			return $docMergeXmlString;
		}

		private function convertStampMergeFieldArrayToSoapString($stampMergeFieldArray) {
			
			$stampMergeXmlString = '<StampMergeData>';

			foreach ($stampMergeFieldArray as $stampMergeField) {

				if($stampMergeField->StampMergeFieldKey != null || $stampMergeField->TextValue != null || $stampMergeField->ImageValue != null) {
					$stampMergeXmlString .= '<MergeField>';

					if($stampMergeField->StampMergeFieldKey != null) {
						$stampMergeXmlString .= $this->createStampMergeFieldKey($stampMergeField->StampMergeFieldKey);
					}
					
					if($stampMergeField->TextValue != null) {
						$stampMergeXmlString .= $this->createTextValueElement($stampMergeField->TextValue);
					}

					if($stampMergeField->ImageValue != null) {
						$stampMergeXmlString .= $this->createImageValueElement($stampMergeField->ImageValue);
					}

					$stampMergeXmlString .= '</MergeField>';
				}
			}
			$stampMergeXmlString .= '</StampMergeData>';

			return $stampMergeXmlString;
		}

		private function createStampMergeFieldKey($mergeFieldKey) {
			
			$stampMergeXmlString = '<Key ';

			if($mergeFieldKey->xCoord != null) {
				$stampMergeXmlString .= 'xCoord="'.$mergeFieldKey->xCoord.'" ';
			}

			if($mergeFieldKey->yCoord != null) {
				$stampMergeXmlString .= 'yCoord="'.$mergeFieldKey->yCoord.'" ';
			}
			$stampMergeXmlString .= '/>';

			return $stampMergeXmlString;
		}

		private function createTextValueElement($stampMergeTextValue) {
			
			$stampMergeXmlString = '<TextValue ';
						
			if($stampMergeTextValue->fontName != null) {
				$stampMergeXmlString .= 'fontName="'.$stampMergeTextValue->fontName.'" ';
			}

			if($stampMergeTextValue->fontSize != null) {
				$stampMergeXmlString .= 'fontSize="'.$stampMergeTextValue->fontSize.'" ';
			}

			$stampMergeXmlString .= '>';

			if($stampMergeTextValue->Value != null) {
				$stampMergeXmlString .= $stampMergeTextValue->Value;
			}

			$stampMergeXmlString .= '</TextValue>';

			return $stampMergeXmlString;
		}

		private function createImageValueElement($stampMergeImageValue) {

			$stampMergeXmlString = '<ImageValue ';

			if($stampMergeImageValue->width) {
				$stampMergeXmlString .= 'width="'.$stampMergeImageValue->width.'" ';
			}

			if($stampMergeImageValue->height) {
				$stampMergeXmlString .= 'height="'.$stampMergeImageValue->height.'" ';
			}

			$stampMergeXmlString .= '>';

			if($stampMergeImageValue->FileName) {
				$stampMergeXmlString .= '<FileName>'.$stampMergeImageValue->FileName.'</FileName>';
			}

			if($stampMergeImageValue->FileData) {
				$stampMergeXmlString .= '<FileData>'.$stampMergeImageValue->FileData.'</FileData>';
			}

			$stampMergeXmlString .= '</ImageValue>';
			return $stampMergeXmlString;
		}

		private function removeNullValues($object) {
			foreach($object as $key => $value) {
					if (!isset($value)) {
							unset($object->$key);
					}
			}
			return $object;
		}

		private function createBlocklistElement($blocklistData) {

			$dncr = $blocklistData->dncr;
			$fps = $blocklistData->fps;
			$smartblock = $blocklistData->smartblock;
			$blocklist = "";

			if($dncr != null || $fps != null || $smartblock != null) {
				$blocklist = '<Blocklists ';

				if($dncr != null) {
					$blocklist .= 'dncr="'.$dncr.'" ';
				}

				if($fps != null) {
					$blocklist .= 'fps="'.$fps.'" ';
				}

				if($smartblock != null) {
					$blocklist .= 'smartblock="'.$smartblock.'"';
				}
				$blocklist .= '/>';
			}

			return $blocklist;
		}
		
		private function createMessageRefsSoapVarObjects($messageRefs) {
			$messageRefSoapObjects = array();
			foreach ($messageRefs as $messageRef) {
				$messageRefSoapObjects[] = new SoapVar($messageRef,SOAP_ENC_OBJECT,null,null,"MessageRef");
			}
			return $messageRefSoapObjects;
		}
			
		public function sendFax($SendFaxRequest) {
			$SendFaxRequest = $this->removeNullValues($SendFaxRequest);        
			
			foreach($SendFaxRequest->FaxMessages as $faxMessage) {
				$faxMessage = $this->removeNullValues($faxMessage);
				// Assign SOAP ready documents array to the fax Message
				if (!empty($faxMessage->Documents)) {
					$faxMessage->Documents = $this->convertDocumentArrayToSoapArray($faxMessage->Documents);    
				}

				if(@$faxMessage->Blocklists != null) {
					$blocklist = $this->createBlocklistElement($faxMessage->Blocklists);
					$faxMessage->Blocklists = new SoapVar($blocklist, XSD_ANYXML);
				}
				
				// Add SOAP ready fax message to an array of fax messages
				$soapFaxMessages[] = new SoapVar($faxMessage,SOAP_ENC_OBJECT,null,null,"FaxMessage");
			}

			if(@$SendFaxRequest->Blocklists != null) {
				$blocklist = $this->createBlocklistElement($SendFaxRequest->Blocklists);
				$SendFaxRequest->Blocklists = new SoapVar($blocklist, XSD_ANYXML);
			}
			
			// Make fax messages array SOAP ready
			$soapFaxMessages = new SoapVar($soapFaxMessages,SOAP_ENC_OBJECT);
			
			// Add soap read fax messages to send reaquest
			$SendFaxRequest->FaxMessages = $soapFaxMessages;
			
			// Assign SOAP ready documents array to the send fax request
			if (!empty($SendFaxRequest->Documents)) {
				$SendFaxRequest->Documents = $this->convertDocumentArrayToSoapArray($SendFaxRequest->Documents);    
			}
			
			// Make fax request SOAP ready
			$SendFaxRequest = new SoapVar($SendFaxRequest,SOAP_ENC_OBJECT,NULL,$this->_strWSSENS,NULL,$this->_strWSSENS);
			try{
					// Try to call send fax
					$this->_SoapClient->SendFax($SendFaxRequest);
			}catch (SoapFault $exception) {
				// Print exception if one occured
				print_r($exception->getMessage());
				// Uncomment the line below to print the XML of the request just made  
				 // print_r($this->_SoapClient->__getLastRequest());
			}

			// Uncomment the line below to print the XML of the request just made  
			// print_r($this->_SoapClient->__getLastResponse());

			$XMLResponseString = $this->_SoapClient->__getLastResponse();
			$XMLResponseString = str_replace("soap:", "", $XMLResponseString);
			$XMLResponseString = str_replace("ns2:", "", $XMLResponseString);

			$element = new SimpleXMLElement($XMLResponseString);
			
			$messagesResponses = $element->Body->SendFaxResponse->FaxMessages;

			return new \FaxBroadcast\LaravelMonopondFax\MonopondSendFaxResponse($messagesResponses);
		}
			
		public function faxStatus($faxStatusRequest) {
			$faxStatusRequest = $this->removeNullValues($faxStatusRequest);
			$faxStatusRequest = new SoapVar($faxStatusRequest,SOAP_ENC_OBJECT,NULL,$this->_strWSSENS,NULL,$this->_strWSSENS);
			try{
					// Try to call fax status
					$this->_SoapClient->FaxStatus($faxStatusRequest);
			}catch (SoapFault $exception) {
					//echo "exception caught";
					print_r($exception->getMessage());
					//print_r($this->_SoapClient->__getLastResponse());
			}
		   
			$XMLResponseString = $this->_SoapClient->__getLastResponse();
			$XMLResponseString = str_replace("soap:", "", $XMLResponseString);
			$XMLResponseString = str_replace("ns2:", "", $XMLResponseString);
			
			$element = new SimpleXMLElement($XMLResponseString);

			$messagesResponses = $element->Body->FaxStatusResponse;
			return new \FaxBroadcast\LaravelMonopondFax\MonopondFaxStatusResponse($messagesResponses);         
		}

		public function stopFax($stopFaxRequest) {
			$stopFaxRequest = $this->removeNullValues($stopFaxRequest);
			$stopFaxRequest = new SoapVar($stopFaxRequest,SOAP_ENC_OBJECT,NULL,$this->_strWSSENS,NULL,$this->_strWSSENS);
			try{
					// Try to call stop fax
					$this->_SoapClient->StopFax($stopFaxRequest);
			}catch (SoapFault $exception) {
					//echo "exception caught";
					print_r($exception->getMessage());
					//print_r($this->_SoapClient->__getLastResponse());
			}
		   
			$XMLResponseString = $this->_SoapClient->__getLastResponse();
			$XMLResponseString = str_replace("soap:", "", $XMLResponseString);
			$XMLResponseString = str_replace("ns2:", "", $XMLResponseString);

			$element = new SimpleXMLElement($XMLResponseString);

			$messagesResponses = $element->Body->StopFaxResponse;

			return new \FaxBroadcast\LaravelMonopondFax\MonopondStopFaxResponse($messagesResponses);         
		}

		public function pauseFax($pauseFaxRequest) {
			$pauseFaxRequest = $this->removeNullValues($pauseFaxRequest);
			$pauseFaxRequest = new SoapVar($pauseFaxRequest,SOAP_ENC_OBJECT,NULL,$this->_strWSSENS,NULL,$this->_strWSSENS);
			try{
					// Try to call pause fax
					$this->_SoapClient->PauseFax($pauseFaxRequest);
			}catch (SoapFault $exception) {
					//echo "exception caught";
					print_r($exception->getMessage());
					//print_r($this->_SoapClient->__getLastResponse());
			}
		   
			$XMLResponseString = $this->_SoapClient->__getLastResponse();
			$XMLResponseString = str_replace("soap:", "", $XMLResponseString);
			$XMLResponseString = str_replace("ns2:", "", $XMLResponseString);
			
			$element = new SimpleXMLElement($XMLResponseString);


			$messagesResponses = $element->Body->PauseFaxResponse;


			return new \FaxBroadcast\LaravelMonopondFax\MonopondPauseFaxResponse($messagesResponses);         
		}

		public function resumeFax($resumeFaxRequest) {
			$resumeFaxRequest = $this->removeNullValues($resumeFaxRequest);
			$resumeFaxRequest = new SoapVar($resumeFaxRequest,SOAP_ENC_OBJECT,NULL,$this->_strWSSENS,NULL,$this->_strWSSENS);
			
			try{
				// Try to call resume fax
				$this->_SoapClient->ResumeFax($resumeFaxRequest);
			}catch (SoapFault $exception) {
				//echo "exception caught";
				print_r($exception->getMessage());
				//print_r($this->_SoapClient->__getLastResponse());
			}

			$XMLResponseString = $this->_SoapClient->__getLastResponse();
			$XMLResponseString = str_replace("soap:", "", $XMLResponseString);
			$XMLResponseString = str_replace("ns2:", "", $XMLResponseString);

			$element = new SimpleXMLElement($XMLResponseString);

			$messagesResponses = $element->Body->ResumeFaxResponse;

			return new \FaxBroadcast\LaravelMonopondFax\MonopondResumeFaxResponse($messagesResponses);         
		}

		public function saveFaxDocument($saveFaxDocumentRequest) {
			$saveFaxDocumentRequest = $this->removeNullValues($saveFaxDocumentRequest);
			$saveFaxDocumentRequest = new SoapVar($saveFaxDocumentRequest,SOAP_ENC_OBJECT,NULL,$this->_strWSSENS,NULL,$this->_strWSSENS);
			try{
					// Try to call fax status
					$this->_SoapClient->SaveFaxDocument($saveFaxDocumentRequest);
			}catch (SoapFault $exception) {
					//echo "exception caught";
					print_r($exception->getMessage());
					//print_r($this->_SoapClient->__getLastResponse());
			}
		   
			$XMLResponseString = $this->_SoapClient->__getLastResponse();
			$XMLResponseString = str_replace("soap:", "", $XMLResponseString);
			$XMLResponseString = str_replace("ns2:", "", $XMLResponseString);
			
			$element = new SimpleXMLElement($XMLResponseString);

			$messagesResponses = $element->Body->SaveFaxDocumentResponse;
			return new \FaxBroadcast\LaravelMonopondFax\MonopondSaveFaxDocumentResponse($messagesResponses); 
		}

		public function faxDocumentPreview($faxDocumentPreviewRequest) {
			$faxDocumentPreviewRequest = $this->removeNullValues($faxDocumentPreviewRequest);
			
			if(!empty($faxDocumentPreviewRequest->DocMergeData)) {
				$docMergeXmlString = $this->convertDocMergeFieldArrayToSoapString($faxDocumentPreviewRequest->DocMergeData);
			 	$faxDocumentPreviewRequest->DocMergeData = new SoapVar($docMergeXmlString, XSD_ANYXML);
			}


			if(!empty($faxDocumentPreviewRequest->StampMergeData)) {
				$stampMergeXmlString = $this->convertStampMergeFieldArrayToSoapString($faxDocumentPreviewRequest->StampMergeData);
				$faxDocumentPreviewRequest->StampMergeData = new SoapVar($stampMergeXmlString, XSD_ANYXML);
			}

			$faxDocumentPreviewRequest = new SoapVar($faxDocumentPreviewRequest,SOAP_ENC_OBJECT,NULL,$this->_strWSSENS,NULL,$this->_strWSSENS);
			
			try{
					// Try to call fax status
					$this->_SoapClient->FaxDocumentPreview($faxDocumentPreviewRequest);
			}catch (SoapFault $exception) {
					//echo "exception caught";
					print_r($exception->getMessage());
					//print_r($this->_SoapClient->__getLastResponse());
			}

			$XMLResponseString = $this->_SoapClient->__getLastResponse();
			$XMLResponseString = str_replace("soap:", "", $XMLResponseString);
			$XMLResponseString = str_replace("ns2:", "", $XMLResponseString);
			
			$element = new SimpleXMLElement($XMLResponseString);

			$messagesResponses = $element->Body->FaxDocumentPreviewResponse;
			return new \FaxBroadcast\LaravelMonopondFax\MonopondFaxDocumentPreviewResponse($messagesResponses); 
		}

		public function deleteFaxDocument($deleteFaxDocumentRequest) {
			$deleteFaxDocumentRequest = $this->removeNullValues($deleteFaxDocumentRequest);

			// TODO: Enable if the multiple deletion of messageRefs will be implemented in whitelabel fax api.
			// if(!empty($deleteFaxDocumentRequest->MessageRefs)) {
			// 	$deleteFaxDocumentRequest->MessageRefs = new SoapVar($this->createMessageRefsSoapVarObjects($deleteFaxDocumentRequest->MessageRefs), SOAP_ENC_OBJECT, NULL, NULL, "MessageRefs");
			// }

			$deleteFaxDocumentRequest = new SoapVar($deleteFaxDocumentRequest,SOAP_ENC_OBJECT,NULL,$this->_strWSSENS,NULL,$this->_strWSSENS);
			
			try{
					// Try to call fax status
					$this->_SoapClient->DeleteFaxDocument($deleteFaxDocumentRequest);
					print_r($this->_SoapClient->__getLastRequest());
			}catch (SoapFault $exception) {
					//echo "exception caught";
					print_r($exception->getMessage());
					//print_r($this->_SoapClient->__getLastResponse());
			}
		   
			$XMLResponseString = $this->_SoapClient->__getLastResponse();
			$XMLResponseString = str_replace("soap:", "", $XMLResponseString);
			$XMLResponseString = str_replace("ns2:", "", $XMLResponseString);
			
			$element = new SimpleXMLElement($XMLResponseString);

			$messagesResponses = $element->Body->DeleteFaxDocumentResponse;
			return new \FaxBroadcast\LaravelMonopondFax\MonopondDeleteFaxDocumentResponse($messagesResponses); 
		}
	}
?>

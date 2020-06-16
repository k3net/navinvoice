<?php

namespace K3Net\NavInvoice\Services;

use Carbon\Carbon, Auth, DB, Config, Mail, SimpleXMLElement, DOMDocument;
use NavOnlineInvoice;
use App\Services\Messages;
use K3Net\NavInvoice\Models\NavTosend, K3Net\NavInvoice\Models\NavConnection;


/***
Azért,mert nem composerrel telepítettük a csomagot, ezt bele kell tenni a vendor/composer/autoload_namespaces.php és vendor/composer/autoload-psr4.php fájlokba: 
    'NavOnlineInvoice\\' => array($vendorDir . '/pzs/nav-online-invoice/src/NavOnlineInvoice'),
***/

class NavInvoiceService {
	var $json_path, $xml, $base64_xml;
  
  public function __construct()
  {
    $this->json_path = storage_path('/app/nav/user.json');
    
		$json = json_encode([
			"login" => env('NAV_LOGIN'),
			"password" => env('NAV_PASSWORD'),
			"taxNumber" => env('NAV_TAXNUMBER'),
			"signKey" => env('NAV_SIGNKEY'),
			"exchangeKey" => env('NAV_EXCHANGEKEY')
		]);
		file_put_contents($this->json_path, $json);
  }
  
	public function nisTestConnection()
	{				
		return $this->test();
	}
	
	public function nisSend($invoiceData)
	{
		$invoiceData = json_decode($invoiceData);
    $this->createXml($invoiceData);
		
		if ($invoiceData->operation == 'XMLTEST'){      
			print $this->xmltest();
		}else{			
			$navTosend = NavTosend::create([
				'invoice_id' => $invoiceData->data->number,
				'customer' => $invoiceData->customer->name,
				'xml' => $this->base64_xml,
				'operation' => $invoiceData->operation,
				'status' => 'tosend'
			]);
			//nem indítjuk el innen, hogy egy időben fusson a cronnal, majd a cron indítja
			//print $nav_interface->send($navTosend);
			//print 'queued: '.$navTosend->id;
		}
	}
	
	public function nisAnnul($id)
	{
		$this->annul($id);
		
		return [
			'alert' => 'érvénytelenítő beküldve',
			'reload' => true
		];
	}
	
	public function nisUpdatexml(Request $request)
	{
		$input = $request->all();
		
		$tosend = NavTosend::find($input['id']);
		$tosend->xml = base64_encode($input['xml']);
		$tosend->status = 'tosend';
		$tosend->save();
		
		return ['alert' => 'mentve', 'refresh' => true];
	}
                                    
  public function createXml($d)
  {
    $methods['Átutalás'] = 'TRANSFER';
    $methods['átutalás'] = 'TRANSFER';
    $methods['Készpénz'] = 'CASH';
    $methods['Bankkártya'] = 'CARD';
    $methods['Utánvét'] = 'OTHER';
    $methods['Előre fizetés'] = 'OTHER';
    
    
    $xml = new SimpleXMLElement('<?xml version="1.0" encoding="utf-8"?><Invoice></Invoice>');
    $xml->addAttribute('xmlns:xmlns:xs', 'http://www.w3.org/2001/XMLSchema-instance');
    $xml->addAttribute('xmlns', 'http://schemas.nav.gov.hu/OSA/1.0/data');
    $xml->addAttribute('xs:xs:schemaLocation', 'http://schemas.nav.gov.hu/OSA/1.0/data invoiceData_20180319.xsd');
    
    $exchange = $xml->addChild('invoiceExchange');
    
    //sztornó számla esetén
    if(isset($d->reference)){
      $reference = $exchange->addChild('invoiceReference');
      $reference->addChild('originalInvoiceNumber',$d->reference->originalInvoiceNumber);
      $reference->addChild('modificationIssueDate',$d->reference->modificationIssueDate);
      $reference->addChild('modificationTimestamp',$d->reference->modificationTimestamp);
      $reference->addChild('modifyWithoutMaster',true);
    }
    
    $head = $exchange->addChild('invoiceHead');
    
    $supplier = $head->addChild('supplierInfo');
    
    $tax = $supplier->addChild('supplierTaxNumber');
    $tax->addChild('taxpayerId',explode('-',$d->supplier->taxnum)[0]);
    $tax->addChild('vatCode',explode('-',$d->supplier->taxnum)[1]);
    $tax->addChild('countyCode',explode('-',$d->supplier->taxnum)[2]);
    
    $supplier->addChild('supplierName',$d->supplier->name);
    
    $address = $supplier->addChild('supplierAddress');
    $detailedAddress = $address->addChild('detailedAddress');
    $detailedAddress->addChild('countryCode','HU');
    $detailedAddress->addChild('postalCode',$d->supplier->postalCode);
    $detailedAddress->addChild('city',$d->supplier->city);
    $detailedAddress->addChild('streetName',$d->supplier->streetName);
    $detailedAddress->addChild('publicPlaceCategory',$d->supplier->publicPlaceCategory);
    $detailedAddress->addChild('number',$d->supplier->number);
    
    $supplier->addChild('supplierBankAccountNumber',$d->supplier->supplierBankAccountNumber);
    
    if(!isset($d->reference)){
      $customer = $head->addChild('customerInfo');
      if(!empty($d->customer->taxnum)){        
        $tax = $customer->addChild('customerTaxNumber');
        $tax->addChild('taxpayerId',explode('-',$d->customer->taxnum)[0]);
        $tax->addChild('vatCode',explode('-',$d->customer->taxnum)[1]);
        $tax->addChild('countyCode',explode('-',$d->customer->taxnum)[2]);
      }
      
      $customer->addChild('customerName',$d->customer->name);
      
      $address = $customer->addChild('customerAddress');
      $simpleAddress = $address->addChild('simpleAddress');
      $simpleAddress->addChild('countryCode','HU');
      $simpleAddress->addChild('postalCode',$d->customer->postalCode);
      $simpleAddress->addChild('city',$d->customer->city);
      $simpleAddress->addChild('additionalAddressDetail',$d->customer->additionalAddressDetail);     
      
    }
    
    $data = $head->addChild('invoiceData');
    
    $data->addChild('invoiceNumber',$d->data->number);
    $data->addChild('invoiceCategory',$d->data->category);
    $data->addChild('invoiceIssueDate',$d->data->issueDate);
    $data->addChild('invoiceDeliveryDate',$d->data->deliveryDate);
    $data->addChild('currencyCode',$d->data->currencyCode);
    $data->addChild('exchangeRate',$d->data->exchangeRate);
    $data->addChild('paymentMethod',$methods[$d->data->paymentMethod]);
    $data->addChild('paymentDate',$d->data->paymentDate);
    $data->addChild('invoiceAppearance','PAPER');
    
    $lines = $exchange->addChild('invoiceLines');
    
    $afa = [];
    $afatemplate = [
                    'netamount' => 0,
                    'vatamount' => 0,
                    'grossamount' => 0
                  ];
    
    $i = 1;
    $count = count($d->lines);
    
    foreach($d->lines as $item){
      $afakulcs = $item->vatPercentage * 100;
      if(!isset($afa[$afakulcs])){
        $afa[$afakulcs] = $afatemplate;
      }
      
      $afa[$afakulcs]['netamount'] += $item->price * $item->quantity;
      $afa[$afakulcs]['vatamount'] += $item->vatAmount * $item->quantity;
      $afa[$afakulcs]['grossamount'] += $item->grossAmountNormal * $item->quantity;
      
      $line = $lines->addChild('line');
      
      $line->addChild('lineNumber',$i);
      $line->addChild('lineExpressionIndicator',true);
      
      if(isset($d->reference))
      {
        $mod = $line->addChild('lineModificationReference');
        $mod->addChild('lineNumberReference', $count + $i -1);
        $mod->addChild('lineOperation', 'MODIFY');
      }
      
      $line->addChild('lineDescription',$item->description);
      $line->addChild('quantity',$item->quantity);
      $line->addChild('unitOfMeasure','PIECE');
      $line->addChild('unitPrice',$item->price);
      $amounts = $line->addChild('lineAmountsNormal');
      $amounts->addChild('lineNetAmount', $item->price * $item->quantity);
      $vat = $amounts->addChild('lineVatRate');
      $vat->addChild('vatPercentage', $item->vatPercentage);
      $amounts->addChild('lineVatAmount', $item->vatAmount * $item->quantity);
      $amounts->addChild('lineGrossAmountNormal', $item->grossAmountNormal * $item->quantity);
      
      $i++;

    }//tétel foreach
    
    $summary = $exchange->addChild('invoiceSummary');
    $sn = $summary->addChild('summaryNormal');
    foreach($afa as $afakulcs => $osszegek)
    {
      $sbvt = $sn->addChild('summaryByVatRate');
      $vr = $sbvt->addChild('vatRate');
      $vr->addChild('vatPercentage',$afakulcs/100);
      $sbvt->addChild('vatRateNetAmount',$osszegek['netamount']);
      $sbvt->addChild('vatRateVatAmount',$osszegek['netamount']);
      $sbvt->addChild('vatRateVatAmountHUF',$osszegek['vatamount']);
      $sbvt->addChild('vatRateGrossAmount',$osszegek['grossamount']);
    }
    
    $sn->addChild('invoiceNetAmount',$d->summary->netamount);
    $sn->addChild('invoiceVatAmount',$d->summary->vatamount);
    $sn->addChild('invoiceVatAmountHUF',$d->summary->vatamount);
    
    $summary->addChild('invoiceGrossAmount',$d->summary->grossamount);
    

    $this->xml = $xml;

    $this->base64_xml = base64_encode($xml->asXML());
    if($d->debug){
      $sxe = simplexml_load_string($xml->asXML());

      if ($sxe === false) {
          echo 'Error while parsing the document';
          exit;
      }

      $dom_sxe = dom_import_simplexml($sxe);
      if (!$dom_sxe) {
          echo 'Error while converting XML';
          exit;
      }

      $dom = new DOMDocument('1.0');
      $dom->preserveWhiteSpace = false;
      $dom->formatOutput = true;
      $dom_sxe = $dom->importNode($dom_sxe, true);
      $dom_sxe = $dom->appendChild($dom_sxe);

      echo nl2br(htmlspecialchars($dom->saveXML()));

      //echo $xml->asXML();
      return false;
    }
    else{
      return true;
    }
  }
	
	public function config($userJson)
	{
		$config = new NavOnlineInvoice\Config(env('NAV_URL'), $userJson);
		$config->useApiSchemaValidation();
		$config->setCurlTimeout(10);
		$config->setSoftware([
			'softwareId' => 'HU1461976620200001',
			'softwareName' => 'K3 invoice2NAV számlázó interface',
			'softwareOperation' => 'ONLINE_SERVICE',
			'softwareMainVersion' => '1',
			'softwareDevName' => 'Kiss Dániel',
			'softwareDevContact' => 'info@k3net.hu',
			'softwareDevCountryCode' => 'HU',
			'softwareDevTaxNumber' => '14619766-2-02'
		]);
		
		return $config;
	}
	
	public function test()
	{
		try {
			$config = $this->config($this->json_path);
			$reporter = new NavOnlineInvoice\Reporter($config);
			$token = $reporter->tokenExchange();
			
			return ['alert' => 'Sikeres kapcsolat! Minen OK!'];
		} catch(NavOnlineInvoice\GeneralErrorResponse $ex) {
			return ['warning' => $ex->getMessage()];
		}
	}
	
	public function xmltest()
	{
    
		try {
			$config = $this->config($this->json_path);
			$reporter = new NavOnlineInvoice\Reporter($config);
		} catch(\Exception $ex) {
			return 'init failed: '.$ex->getMessage();
		}
    
		try {			
			$invoices = new NavOnlineInvoice\InvoiceOperations();
			$invoices->useDataSchemaValidation();
			$invoices->add($this->xml);
			return 'ok';
		} catch(NavOnlineInvoice\XsdValidationError $ex) {
			return 'invalid xml: '.$ex->getMessage();
		} catch(\Exception $ex){
			return 'other error: '.get_class($ex).' '.$ex->getMessage();
		}
	}
	
	protected function send($tosend, $orig_status)
	{
		//config
		try {
			$config = $this->config($this->json_path);
			$reporter = new NavOnlineInvoice\Reporter($config);
			$tosend->msg = 'init ok, pid: '.getmypid();
			$tosend->save();
		} catch(\Exception $ex) {
			$tosend->status = $orig_status;
			$tosend->msg = 'init failed: '.$ex->getMessage();
			$tosend->save();
			return 'init error';
		}
		
		//számla validálás
		try {
			$xml = base64_decode($tosend->xml);
			$invoices = new NavOnlineInvoice\InvoiceOperations();
			$invoices->useDataSchemaValidation();
			$invoices->add(simplexml_load_string($xml));//ezt csak xml validálásra, mert amúgy szar
			$tosend->msg = 'xml is valid';
			$tosend->save();
		} catch(NavOnlineInvoice\XsdValidationError $ex) {
			$tosend->msg = 'invalid xml: '.$ex->getMessage();
			$tosend->status = 'xml error';
			$tosend->save();
			$this->sendalert($tosend);
			return 'sending aborted, invalid xml';
		} catch(\Exception $ex){
			$tosend->msg = get_class($ex).': '.$ex->getMessage();
			$tosend->status = 'exception';
			$tosend->save();
			$this->sendalert($tosend);
			return 'sending aborted, exception';
		}
		
		//számla beküldés
		$connection = NavConnection::create([
			'nav_tosend_id' => $tosend->id
		]);
		
		try {
			$tosend->transaction_id = $reporter->manageInvoice(simplexml_load_string($xml), $tosend->operation);
			$tosend->msg = 'NAV-nak beküldve feldolgozásra: '.$tosend->operation;
			$tosend->status = 'sent';
			$tosend->save();
			
			$connection->msg = 'sent: '.$tosend->transaction_id;
			$connection->save();
			return $tosend->id;
		} catch(\Exception $ex) {
			$tosend->status = $orig_status;
			$tosend->msg = 'sending error: '.get_class($ex).': '.$ex->getErrorCode().' '.$ex->getMessage();
			$tosend->save();
			$connection->msg = 'sending error: '.get_class($ex).': '.$ex->getErrorCode().' '.$ex->getMessage();
			$connection->save();
			return 'sending error';
		}
	}

	//cronból fut 1 percenként, ez indít mindent
	public function getStatus($nav_tosend)
	{
		//visszaellenőrzünk, hogy mi az aktuális statusa a számlának és az alapján értelmezzük
		$orig_status = $nav_tosend->status;
		DB::beginTransaction();
		$start_status = DB::table('nav_tosend')->where('id', $nav_tosend->id)->lockForUpdate()->get();
		DB::table('nav_tosend')->where('id', $nav_tosend->id)->update(['status' => 'inprogress: '.getmypid()]);
		DB::commit();
		
		$tosend = NavTosend::find($nav_tosend->id);
		
		switch ($start_status[0]->status){
			case 'tosend':
				return $this->send($tosend, $orig_status);
			break;
			case 'sent':
			case 'processing':
				$connection = NavConnection::create([
					'nav_tosend_id' => $tosend->id
				]);
				
				try {
          
					$config = $this->config($this->json_path);
					$reporter = new NavOnlineInvoice\Reporter($config);
					
					$re = $reporter->queryInvoiceStatus($tosend->transaction_id);
          
					if (isset($re->processingResults)){
            
						switch ($re->processingResults->processingResult->invoiceStatus){
							case 'DONE':
								$tosend->status = 'done';
								$tosend->msg = 'NAV feldolgozta. Minden rendben.';
								$connection->msg = 'getStatus: done';
								$connection->save();
							break;
							case 'ABORTED':
								$tosend->status = 'aborted';
								$tosend->msg = 'NAV feldolgozta, de hibás: '.print_r($re->processingResults->processingResult->businessValidationMessages, true);
								$connection->msg = 'getStatus: aborted';
								$connection->save();
								$this->sendalert($tosend);
							break;
							case 'PROCESSING':
								$tosend->status = 'processing';
								$tosend->msg = 'Beküldve, de még nem dolgozta fel a NAV, újrapróbálkozás 5 perc múlva.';
								$connection->msg = 'getStatus: processing';
								$connection->save();
							break;
							default:
								//egyéb hiba, újra próbáljuk később, de loggoljuk
                
								$tosend->status = $orig_status;
								$tosend->msg = print_r($re->processingResults->processingResult, true);
								$connection->msg = 'getStatus: '.print_r($re->processingResults->processingResult, true);
								$connection->save();
                
						}
					}else{
						$tosend->status = 'nav_error';
						$tosend->msg = 'nav error, transaction_id not found';
					}
					$tosend->save();
          
				} catch(\Exception $ex) {
					$tosend->status = $orig_status;
					$tosend->msg = get_class($ex).': '.$ex->getMessage();
					$tosend->save();
					$connection->msg = 'getStatus: '.$ex->getMessage();
					$connection->save();
					return $ex->getMessage();
				}
				return $tosend->id;
			break;
		}
		$tosend->status = $orig_status;
		$tosend->save();
    
		return $tosend->id;
	}
	
	public function annul($id)
	{
		$tosend = NavTosend::find($id);
		
		$annul_xml = view('nav.annul_xml', compact('tosend'))->render();

		$annul = $tosend->replicate();
		$annul->operation = 'ANNUL';
		$annul->xml = base64_encode($annul_xml);
		$annul->transaction_id = ''; 
		$annul->msg = '';
		$annul->status = 'tosend';
		$annul->save();
		
		$tosend->status = 'annul';
		$tosend->msg = 'Érvénytelenítve.';
		$tosend->save();
		//cronból indítjuk csak a feldoglozást, hogy ne legyen ütközés, mivel kurva lassú a rendszer
		return;
	}
	
	public function sendalert($tosend)
	{
		$msg = '<h1>Számla beküldés hiba</h1>';
		$msg .= '<b>tosend id:</b> '.$tosend->id.'<br>';
		$msg .= '<b>hibaüzenet:</b><br>';
		$msg .= '<pre>'.$tosend->msg.'</pre>';
		
		Mail::send(
			['html' => 'emails.html', 'text'=>'emails.text'],
			['body' => $msg, 'text' => nl2br(strip_tags($msg))],
			function ($m) {
				$m->from('info@k3net.hu', 'K3 invoice2NAV')
				  ->to('info@k3net.hu')
				  ->subject('onlineszamla error :/');
			}
		);
		
    $user = \App\User::find(2);
		Messages::send($user, 'onlineszamla error', $msg);
	}
}
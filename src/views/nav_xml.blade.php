<InvoiceData xmlns="http://schemas.nav.gov.hu/OSA/2.0/data" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://schemas.nav.gov.hu/OSA/2.0/data invoiceData.xsd">
	<invoiceNumber>{{ $d['data']['number'] }}</invoiceNumber>
	<invoiceIssueDate>{{ $d['data']['issueDate'] }}</invoiceIssueDate>
	<invoiceMain>
		<invoice>
				@if(isset($d['reference']))
					<invoiceReference>
						<originalInvoiceNumber>{{ $d['reference']['originalInvoiceNumber'] }}</originalInvoiceNumber>
						<modifyWithoutMaster>false</modifyWithoutMaster>
						<modificationIndex>1</modificationIndex>
					</invoiceReference>
				@endif
			<invoiceHead>
				<supplierInfo>
					<supplierTaxNumber>
						<taxpayerId>{{ explode('-',$d['supplier']['taxnum'])[0] }}</taxpayerId>
						<vatCode>{{ explode('-',$d['supplier']['taxnum'])[1] }}</vatCode>
						<countyCode>{{ explode('-',$d['supplier']['taxnum'])[2] }}</countyCode>
					</supplierTaxNumber>
					<supplierName>{!! $d['supplier']['name'] !!}</supplierName>
					<supplierAddress>
						<detailedAddress>
							<countryCode>HU</countryCode>
							<postalCode>{!! $d['supplier']['postalCode'] !!}</postalCode>
							<city>{!! $d['supplier']['city'] !!}</city>
							<streetName>{!! $d['supplier']['streetName'] !!}</streetName>
							<publicPlaceCategory>{!! $d['supplier']['publicPlaceCategory'] !!}</publicPlaceCategory>
							<number>{!! $d['supplier']['number'] !!}</number>
							@if (! empty($d['supplier']['level']))<floor>{!! $d['supplier']['level'] !!}</floor>@endif
							@if(! empty($d['supplier']['door']))<door>{!! $d['supplier']['door'] !!}</door>@endif
						</detailedAddress>
					</supplierAddress>
					<supplierBankAccountNumber>{{ $d['supplier']['supplierBankAccountNumber'] }}</supplierBankAccountNumber>
		@if ($d['supplier']['smallTaxpayer'] == 1)
					<individualExemption>true</individualExemption>
		@endif
				</supplierInfo>
				<customerInfo>
					<customerTaxNumber>
						<taxpayerId>{{ explode('-',$d['customer']['taxnum'])[0] }}</taxpayerId>
						<vatCode>{{ explode('-',$d['customer']['taxnum'])[1] }}</vatCode>
						<countyCode>{{ explode('-',$d['customer']['taxnum'])[2] }}</countyCode>
					</customerTaxNumber>
					<customerName>{!! $d['customer']['name'] !!}</customerName>
					<customerAddress>
						<simpleAddress>
							<countryCode>HU</countryCode>
							<postalCode>{!! $d['customer']['postalCode'] !!}</postalCode>
							<city>{!! $d['customer']['city'] !!}</city>
							<additionalAddressDetail>{!! $d['customer']['additionalAddressDetail'] !!}</additionalAddressDetail>
						</simpleAddress>
					</customerAddress>
				</customerInfo>
				<invoiceDetail>
					<invoiceCategory>NORMAL</invoiceCategory>
					<invoiceDeliveryDate>{{ $d['data']['deliveryDate'] }}</invoiceDeliveryDate>
					<currencyCode>HUF</currencyCode>
					<exchangeRate>1</exchangeRate>
					<paymentMethod>{{ $d['data']['paymentMethod'] }}</paymentMethod>
					<paymentDate>{{ $d['data']['paymentDate'] }}</paymentDate>
					<invoiceAppearance>PAPER</invoiceAppearance>
				</invoiceDetail>
			</invoiceHead>
			<invoiceLines>
		@foreach($d['lines'] as $line)
					<line>
						<lineNumber>{{ $i++ }}</lineNumber>
				@if(isset($d['reference']))
						<lineModificationReference>
							<lineNumberReference>{{ count($d['lines']) + $i - 1 }}</lineNumberReference>
							<lineOperation>CREATE</lineOperation>
						</lineModificationReference>
				@endif
						<lineExpressionIndicator>true</lineExpressionIndicator>
						<lineDescription>{!! $line['description'] !!}</lineDescription>
						<quantity>{{ $line['quantity'] }}</quantity>
						<unitOfMeasure>{{ $line['unit'] }}</unitOfMeasure>
						<unitPrice>{{ $line['price'] }}</unitPrice>
						@if(isset($line['discountValue']))
						<lineDiscountData>
							<discountDescription>Kedvezmény</discountDescription>
							<discountValue>{{ $line['discountValue'] }}</discountValue>
							<discountRate>{{ $line['discountRate'] }}</discountRate>
						</lineDiscountData>
						@endif
						<lineAmountsNormal>
							<lineNetAmountData>
								<lineNetAmount>{{ $line['netAmount'] }}</lineNetAmount>
								<lineNetAmountHUF>{{ $line['netAmount'] }}</lineNetAmountHUF>
							</lineNetAmountData>
							<lineVatRate>
								@if ($line['vatPercentage'] == 0 && $d['supplier']['smallTaxpayer'] == 1)
									<vatExemption>AAM</vatExemption>
								@else
									<vatPercentage>{{ $line['vatPercentage'] }}</vatPercentage>
								@endif
							</lineVatRate>
							<lineVatData>
								<lineVatAmount>{{ $line['vatAmount'] }}</lineVatAmount>
								<lineVatAmountHUF>{{ $line['vatAmount'] }}</lineVatAmountHUF>
							</lineVatData>
							<lineGrossAmountData>
								<lineGrossAmountNormal>{{ $line['grossAmountNormal'] }}</lineGrossAmountNormal>
								<lineGrossAmountNormalHUF>{{ $line['grossAmountNormal'] }}</lineGrossAmountNormalHUF>
							</lineGrossAmountData>
						</lineAmountsNormal>
					</line>
		@endforeach
			</invoiceLines>
			<invoiceSummary>
					<summaryNormal>
		@foreach($d['vatrates'] as $rate=>$amounts)
						<summaryByVatRate>
							<vatRate>
								@if ($d['supplier']['smallTaxpayer'] == 1)
									<vatExemption>AAM</vatExemption>
								@else
									<vatPercentage>{{ $rate/100 }}</vatPercentage>
								@endif
							</vatRate>
							<vatRateNetData>
								<vatRateNetAmount>{{ $amounts['netamount'] }}</vatRateNetAmount>
								<vatRateNetAmountHUF>{{ $amounts['netamount'] }}</vatRateNetAmountHUF>
							</vatRateNetData>
							<vatRateVatData>
								<vatRateVatAmount>{{ $amounts['vatamount'] }}</vatRateVatAmount>
								<vatRateVatAmountHUF>{{ $amounts['vatamount'] }}</vatRateVatAmountHUF>
							</vatRateVatData>
							<vatRateGrossData>
								<vatRateGrossAmount>{{ $amounts['grossamount'] }}</vatRateGrossAmount>
								<vatRateGrossAmountHUF>{{ $amounts['grossamount'] }}</vatRateGrossAmountHUF>
							</vatRateGrossData>
						</summaryByVatRate>
		@endforeach
						<invoiceNetAmount>{{ $d['summary']['netamount'] }}</invoiceNetAmount>
						<invoiceNetAmountHUF>{{ $d['summary']['netamount'] }}</invoiceNetAmountHUF>
						<invoiceVatAmount>{{ $d['summary']['vatamount'] }}</invoiceVatAmount>
						<invoiceVatAmountHUF>{{ $d['summary']['vatamount'] }}</invoiceVatAmountHUF>
					</summaryNormal>
					<summaryGrossData>
						<invoiceGrossAmount>{{ $d['summary']['grossamount'] }}</invoiceGrossAmount>
						<invoiceGrossAmountHUF>{{ $d['summary']['grossamount'] }}</invoiceGrossAmountHUF>
					</summaryGrossData>
			</invoiceSummary>
		</invoice>
	</invoiceMain>
</InvoiceData>
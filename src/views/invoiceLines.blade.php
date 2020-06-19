
				
			<invoiceLines>
		@foreach($invoice->items as $item)
					<line>
						<lineNumber>{{ $i++ }}</lineNumber>
				@if($invoice->cancellation != 0)
						<lineModificationReference>
							<lineNumberReference>{{ $invoice->items->count() + $i - 1 }}</lineNumberReference>
							<lineOperation>CREATE</lineOperation>
						</lineModificationReference>
				@endif
						
				@if (in_array($item->unit, ["óra","db","darab"]))
						<lineExpressionIndicator>true</lineExpressionIndicator>
				@else
						<lineExpressionIndicator>false</lineExpressionIndicator>
				@endif
						<lineNatureIndicator>PRODUCT</lineNatureIndicator>
						<lineDescription>{!! $item->item_name !!}</lineDescription>
						<quantity>{{ $item->quantity }}</quantity>
				@if ($item->unit == "óra")
						<unitOfMeasure>HOUR</unitOfMeasure>
				@elseif($item->unit == "db" || $item->unit == "darab")
						<unitOfMeasure>PIECE</unitOfMeasure>
				@else
						<unitOfMeasure>OWN</unitOfMeasure>
						<unitOfMeasureOwn>{!! $item->unit !!}</unitOfMeasureOwn>
				@endif
						<unitPrice>{{ $item->price }}</unitPrice>
						<lineAmountsNormal>
							<lineNetAmountData>
								<lineNetAmount>{{ $item->netto }}</lineNetAmount>
								<lineNetAmountHUF>{{ $item->netto }}</lineNetAmountHUF>
							</lineNetAmountData>
							<lineVatRate>
								@if ($item->vatrate == 0 && $invoice->supplier->small_taxpayer == 1)
									<vatExemption>AAM</vatExemption>
								@else
									<vatPercentage>{{ $item->vatrate / 100 }}</vatPercentage>
								@endif
							</lineVatRate>
							<lineVatData>
								<lineVatAmount>{{ $item->vat }}</lineVatAmount>
								<lineVatAmountHUF>{{ $item->vat }}</lineVatAmountHUF>
							</lineVatData>
							<lineGrossAmountData>
								<lineGrossAmountNormal>{{ $item->amount }}</lineGrossAmountNormal>
								<lineGrossAmountNormalHUF>{{ $item->amount }}</lineGrossAmountNormalHUF>
							</lineGrossAmountData>
						</lineAmountsNormal>
					</line>
		@endforeach
			</invoiceLines>
			<invoiceSummary>
					<summaryNormal>
		@foreach($invoice->vatrates() as $rate=>$amounts)
						<summaryByVatRate>
							<vatRate>
								@if ($item->vatrate == 0 && $invoice->supplier->small_taxpayer == 1)
									<vatExemption>AAM</vatExemption>
								@else
									<vatPercentage>{{ $item->vatrate / 100 }}</vatPercentage>
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
						<invoiceNetAmount>{{ $invoice->sum_net }}</invoiceNetAmount>
						<invoiceNetAmountHUF>{{ $invoice->sum_net }}</invoiceNetAmountHUF>
						<invoiceVatAmount>{{ $invoice->sum_vat }}</invoiceVatAmount>
						<invoiceVatAmountHUF>{{ $invoice->sum_vat }}</invoiceVatAmountHUF>
					</summaryNormal>
					<summaryGrossData>
						<invoiceGrossAmount>{{ $invoice->sum_amount }}</invoiceGrossAmount>
						<invoiceGrossAmountHUF>{{ $invoice->sum_amount }}</invoiceGrossAmountHUF>
					</summaryGrossData>
			</invoiceSummary>
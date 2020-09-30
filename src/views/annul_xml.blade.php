<InvoiceAnnulment xmlns="http://schemas.nav.gov.hu/OSA/2.0/annul"  xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://schemas.nav.gov.hu/OSA/2.0/annul invoiceAnnulment.xsd">
	<annulmentReference>{{ $tosend->invoice->num }}</annulmentReference>
	<annulmentTimestamp>{{ Carbon\Carbon::now('UTC')->format('Y-m-d\TH:i:s').'.000Z' }}</annulmentTimestamp>
	<annulmentCode>ERRATIC_INVOICE_NUMBER</annulmentCode>
	<annulmentReason>technical bug</annulmentReason>
</InvoiceAnnulment>

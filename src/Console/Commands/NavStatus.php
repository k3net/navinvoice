<?php

namespace K3Net\NavInvoice\Console\Commands;
use Illuminate\Console\Command;
use DB, Mail, Carbon\Carbon, Config;
use K3Net\NavInvoice\Models\NavTosend;
use K3Net\NavInvoice\Services\NavInvoiceService;

class NavStatus extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'navstatus';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Lekérdezi a beküldött számlák státuszát és visszamenti db-be.';

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function handle()
	{
		echo Carbon::now()." pid: ".getmypid()." navstatus start ... ";
		$tosends = NavTosend::whereIn('status', ['tosend', 'sent', 'processing'])->get();
		
		$userData = [
			"login" => env('NAV_LOGIN'),
			"password" => env('NAV_PASSWORD'),
			"taxNumber" => env('NAV_TAXNUMBER'),
			"signKey" => env('NAV_SIGNKEY'),
			"exchangeKey" => env('NAV_EXCHANGEKEY')
		];
		
		$nav_interface = new NavInvoiceService($userData);
		
		foreach ($tosends as $tosend){
			echo $nav_interface->getStatus($tosend);
			echo " ... ";
		}
		echo " done: ".Carbon::now()."\n";
	}
}

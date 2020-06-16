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
		$nav_interface = new NavInvoiceService;
		
		foreach ($tosends as $tosend){
			echo $nav_interface->getStatus($tosend);
			echo " ... ";
		}
		echo " done: ".Carbon::now()."\n";
	}
}

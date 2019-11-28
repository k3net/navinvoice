<?php

namespace K3Net\NavInvoice\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use K3Net\NavInvoice\NavConnection, K3Net\NavInvoice\NavTosend;

class NavInvoiceController extends Controller
{
    public function send(Request $request)
    {
      NavConnection::create([
        'tosend_id' => 1,
        'msg' => 'OK'
      ]);
    }
  
  public function sho()
  {
    dd('OK2');
  }
}

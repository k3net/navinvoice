<?php

namespace K3Net\NavInvoice\Models;

use Illuminate\Database\Eloquent\Model;

class NavConnection extends Model
{
	protected $table = 'nav_connections';
	protected $guarded = ['id'];
	
	public function tosend()
	{
		return $this->belongsTo('App\NavConnection');
	}
}

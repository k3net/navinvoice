<?php

namespace K3Net\NavInvoice\Models;

use Illuminate\Database\Eloquent\Model;

class NavTosend extends Model
{
	protected $table = 'nav_tosend';
	protected $guarded = ['id'];
	
	public function user()
	{
		return $this->belongsTo('App\User')->withTrashed();
	}
	
	public function client()
	{
		return $this->belongsTo('App\Client');
	}
	
	public function connections()
	{
		return $this->hasMany('\K3Net\NavInvoice\Models\NavConnection');
	}
}

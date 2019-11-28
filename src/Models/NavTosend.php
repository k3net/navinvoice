<?php

namespace K3Net\NavInvoice;

use Illuminate\Database\Eloquent\Model;

class NavTosend extends Model
{
	protected $table = 'nav_tosend';
	protected $guarded = ['id'];
	
	public function user()
	{
		return $this->belongsTo('App\User')->withTrashed();
	}
	
	public function connections()
	{
		return $this->hasMany('App\NavConnection');
	}
}
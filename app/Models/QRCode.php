<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QRCode extends Model
{
	use HasFactory;

	protected $guarded = ['id'];

	public function depositable()
	{
		return $this->morphOne(Deposit::class, 'depositable');
	}

	public function successDepositable()
	{
		return $this->morphOne(Deposit::class, 'depositable')->where('status', 1);
	}

	public function transactional()
	{
		return $this->morphOne(Transaction::class, 'transactional');
	}

	public function currency()
	{
		return $this->belongsTo(Currency::class, 'currency_id');
	}

	public function user()
	{
		return $this->belongsTo(User::class, 'user_id');
	}

	public function gateway()
	{
		return $this->belongsTo(Gateway::class, 'gateway_id');
	}
}

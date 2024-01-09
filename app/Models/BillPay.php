<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BillPay extends Model
{
	use HasFactory;

	protected $guarded = ['id'];

	public function service()
	{
		return $this->belongsTo(BillService::class, 'service_id');
	}

	public function method()
	{
		return $this->belongsTo(BillMethod::class, 'method_id');
	}

	public function user()
	{
		return $this->belongsTo(User::class, 'user_id');
	}

	public function transactional()
	{
		return $this->morphOne(Transaction::class, 'transactional');
	}

	public function depositable()
	{
		return $this->morphOne(Deposit::class, 'depositable');
	}

	public function baseCurrency()
	{
		return $this->belongsTo(Currency::class, 'base_currency_id', 'id');
	}

	public function walletCurrency()
	{
		return $this->belongsTo(Currency::class, 'from_wallet', 'id');
	}
}

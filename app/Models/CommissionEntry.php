<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommissionEntry extends Model
{
	use HasFactory;

	public function sender()
	{
		return $this->belongsTo(User::class, 'from_user', 'id');
	}

	public function receiver()
	{
		return $this->belongsTo(User::class, 'to_user', 'id');
	}

	public function currency()
	{
		return $this->belongsTo(Currency::class, 'currency_id', 'id');
	}

	public function transactional()
	{
		return $this->morphOne(Transaction::class, 'transactional');
	}
}

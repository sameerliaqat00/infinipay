<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Escrow extends Model
{
	use HasFactory;

	public function transactional()
	{
		return $this->morphOne(Transaction::class, 'transactional');
	}

	public function disputable()
	{
		return $this->morphOne(Dispute::class, 'disputable');
	}

	public function sender()
	{
		return $this->belongsTo(User::class, 'sender_id', 'id');
	}

	public function receiver()
	{
		return $this->belongsTo(User::class, 'receiver_id', 'id');
	}

	public function currency()
	{
		return $this->belongsTo(Currency::class, 'currency_id', 'id');
	}
}

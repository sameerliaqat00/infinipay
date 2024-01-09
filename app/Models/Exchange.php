<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Exchange extends Model
{
	use HasFactory;

	public function fromWallet()
	{
		return $this->belongsTo(Wallet::class, 'from_wallet', 'id');
	}

	public function user()
	{
		return $this->belongsTo(User::class, 'user_id', 'id');
	}

	public function toWallet()
	{
		return $this->belongsTo(Wallet::class, 'to_wallet', 'id');
	}

	public function transactional()
	{
		return $this->morphOne(Transaction::class, 'transactional');
	}
}

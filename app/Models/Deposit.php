<?php

namespace App\Models;

use App\Scopes\ModeScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Deposit extends Model
{
	use HasFactory;

	protected static function booted()
	{
		static::addGlobalScope(new ModeScope);
	}

	public function transactional()
	{
		return $this->morphOne(Transaction::class, 'transactional');
	}

	public function depositable()
	{
		return $this->morphTo();
	}

	public function receiver()
	{
		return $this->belongsTo(User::class, 'user_id', 'id');
	}

	public function currency()
	{
		return $this->belongsTo(Currency::class, 'currency_id', 'id');
	}

	public function gateway()
	{
		return $this->belongsTo(Gateway::class, 'payment_method_id', 'id');
	}
}

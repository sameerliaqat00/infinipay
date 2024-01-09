<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApiOrder extends Model
{
	use HasFactory;

	protected $guarded = ['id'];

	protected $casts = [
		'meta' => 'object',
	];

	public function currency()
	{
		return $this->belongsTo(Currency::class, 'currency_id');
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
}

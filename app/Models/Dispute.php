<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dispute extends Model
{
    use HasFactory;

	public function disputable()
	{
		return $this->morphTo();
	}

	public function disputeDetails()
	{
		return $this->hasMany(DisputeDetails::class,'dispute_id','id');
	}

	public function transactional()
	{
		return $this->morphOne(Transaction::class, 'transactional');
	}
}

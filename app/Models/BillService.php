<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BillService extends Model
{
	use HasFactory;

	protected $guarded = ['id'];
	protected $casts = [
		'info' => 'object'
	];

	public function method()
	{
		return $this->belongsTo(BillMethod::class, 'bill_method_id');
	}
}

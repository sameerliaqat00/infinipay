<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
	use HasFactory;

	protected $guarded = ['id'];

	public function items()
	{
		return $this->morphMany(LineItem::class, 'line_item', 'line_item_type', 'line_item_id');
	}

	public function recuring_invoice()
	{
		return $this->belongsTo(RecuringInvoice::class, 'recuring_invoice_id', 'id');
	}

	public function sendBy()
	{
		return $this->belongsTo(User::class, 'sender_id', 'id');
	}

	public function sender()
	{
		return $this->belongsTo(User::class, 'sender_id', 'id');
	}

	public function currency()
	{
		return $this->belongsTo(Currency::class, 'currency_id', 'id');
	}

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
}

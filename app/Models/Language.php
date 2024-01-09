<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Language extends Model
{
	use HasFactory;

	public function contentDetails()
	{
		return $this->hasOne(ContentDetails::class, 'language_id', 'id');
	}

	public function templates()
	{
		return $this->hasMany(Template::class, 'language_id', 'id');
	}

	public function emailTemplates()
	{
		return $this->hasMany(EmailTemplate::class, 'language_id', 'id');
	}

	public function notifyTemplates()
	{
		return $this->hasMany(NotifyTemplate::class, 'language_id', 'id');
	}
}

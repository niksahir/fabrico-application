<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Quotation extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'product_name', 'price', 'description', 'status'];

    public function attachments() { return $this->hasMany(QuotationAttachment::class); }
}


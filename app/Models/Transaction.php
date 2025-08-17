<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_id',
        'amount',
        'status',
        'description',
    ];

    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }

    protected $appends = ['ticket_number'];

    public function getTicketNumberAttribute()
    {
        return 'TK-' . now()->year . str_pad($this->ticket_id, 4, '0', STR_PAD_LEFT);
    }
}

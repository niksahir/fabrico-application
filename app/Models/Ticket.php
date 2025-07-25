<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'machine', 'issue_description', 'status', 'assigned_to', 'address', 'contact_number', 'machine_fault', 'resolve_description'
    ];

    public function user() { return $this->belongsTo(User::class); }
    public function staff() { return $this->belongsTo(User::class, 'assigned_to'); }
    public function attachments() { return $this->hasMany(TicketAttachment::class); }

    protected $appends = ['ticket_number'];

    public function getTicketNumberAttribute()
    {
        return 'TK-' . now()->year . str_pad($this->id, 4, '0', STR_PAD_LEFT);
    }

}

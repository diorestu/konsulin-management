<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClientCompliance extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'period',
        'pph_21',
        'pph_unifikasi',
        'ppn',
        'pp_55',
        'pph_25',
        'lk',
        'notes',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }
}

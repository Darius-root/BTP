<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BordereauLigne extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'designation',
        'specification_technique',
        'bi',
        'bs',
        'bordereau_id',
    ];

    protected $casts = [
        'bi' => 'decimal:2',
        'bs' => 'decimal:2',
    ];

    /**
     * Relation avec le bordereau parent
     */
    public function bordereau(): BelongsTo
    {
        return $this->belongsTo(Bordereau::class);
    }
}

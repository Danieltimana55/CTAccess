<?php

namespace App\Models;

use App\Traits\HasActivityLog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Portatil extends Model
{
    use HasFactory, HasActivityLog, SoftDeletes;

    protected $table = 'portatiles';
    protected $primaryKey = 'portatil_id';

    protected $fillable = [
        'persona_id',
        'serial',
        'qrCode',
        'marca',
        'modelo',
    ];

    /**
     * Get the persona that owns the portatil.
     */
    public function persona()
    {
        return $this->belongsTo(Persona::class, 'persona_id', 'idPersona');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Grupo extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'grupos';

    protected $fillable = [
        'nome',
        'descricao',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function recursos(): BelongsToMany
    {
        return $this->belongsToMany(Recurso::class, 'grupo_recurso');
    }
}

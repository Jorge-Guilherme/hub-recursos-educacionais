<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Recurso extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'recursos';

    protected $fillable = [
        'titulo',
        'descricao',
        'tipo',
        'url',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'recurso_tag');
    }

    public function grupos(): BelongsToMany
    {
        return $this->belongsToMany(Grupo::class, 'grupo_recurso');
    }
}

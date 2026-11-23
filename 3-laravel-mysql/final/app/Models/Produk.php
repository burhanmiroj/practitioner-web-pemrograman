<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Produk extends Model
{
    protected $table = 'produk';

    protected $fillable = ['kategori_id', 'nama', 'gambar', 'stok', 'harga'];

    public function kategori(): BelongsTo
    {
        return $this->belongsTo(Kategori::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Penjualan extends Model
{
    protected $table = 'penjualan';

    protected $fillable = ['produk_id', 'qty', 'harga_satuan', 'total'];

    protected function casts(): array
    {
        return ['tanggal' => 'datetime'];
    }

    public function produk(): BelongsTo
    {
        return $this->belongsTo(Produk::class);
    }
}

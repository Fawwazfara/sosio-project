<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pembayaran extends Model
{
    use HasFactory;

    protected $fillable = [
        'kode_referensi',
        'siswa_id',
        'kelas_id',
        'wali_user_id',
        'confirmed_by',
        'metode',
        'channel',
        'status',
        'bulan_list',
        'tahun',
        'nominal_per_bulan',
        'total_nominal',
        'qr_payload',
        'catatan',
        'expired_at',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'bulan_list' => 'array',
            'expired_at' => 'datetime',
            'paid_at' => 'datetime',
        ];
    }

    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }

    public function kelas()
    {
        return $this->belongsTo(Kelas::class);
    }

    public function waliUser()
    {
        return $this->belongsTo(User::class, 'wali_user_id');
    }

    public function confirmedBy()
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }

    public function infaks()
    {
        return $this->hasMany(Infak::class);
    }
}


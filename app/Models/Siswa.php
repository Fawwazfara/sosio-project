<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Siswa extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama_lengkap', 'nis', 'kelas_id', 'status', 'tanggal_keluar', 'jenis_kelamin',
        'alamat', 'blok', 'nama_wali_1', 'wa_wali_1', 'nama_wali_2', 'wa_wali_2',
        'wali_user_id',
    ];

    protected $casts = [
        'tanggal_keluar' => 'date:Y-m-d',
    ];

    /**
     * Filter hanya siswa yang masih aktif (bukan status keluar).
     */
    public function scopeAktif($query)
    {
        return $query->where(function ($q) {
            $q->where('status', '!=', 'keluar')
              ->orWhereNull('status');
        });
    }

    public function isKeluar(): bool
    {
        return $this->status === 'keluar';
    }

    public function kelas()
    {
        return $this->belongsTo(Kelas::class);
    }

    public function waliUser()
    {
        return $this->belongsTo(User::class, 'wali_user_id');
    }

    public function infaks()
    {
        return $this->hasMany(Infak::class);
    }

    public function pembayarans()
    {
        return $this->hasMany(Pembayaran::class);
    }
}

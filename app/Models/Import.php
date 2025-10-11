<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Import extends Model
{
    use HasFactory;

    protected $fillable = [
        'imp_date',
        'staff_id',
        'full_name',
        'sup_id',
        'supplier',
        'total',
    ];

    protected $casts = [
        'imp_date' => 'date',
        'total' => 'decimal:2',
    ];

    /**
     * Get the staff that owns the import.
     */
    public function staff()
    {
        return $this->belongsTo(Staff::class, 'staff_id');
    }

    /**
     * Get the supplier that owns the import.
     */
    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'sup_id');
    }

    /**
     * Get the import details for the import.
     */
    public function importDetails()
    {
        return $this->hasMany(ImportDetail::class, 'imp_code');
    }
}

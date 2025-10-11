<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Staff extends Model
{
    use HasFactory;

    protected $table = 'staffs';

    protected $fillable = [
        'full_name',
        'gen',
        'dob',
        'position',
        'salary',
        'stopwork',
        'photo',
        'status',
    ];

    protected $casts = [
        'dob' => 'date',
        'stopwork' => 'boolean',
        'salary' => 'decimal:2',
    ];

    /**
     * Get the imports for the staff.
     */
    public function imports()
    {
        return $this->hasMany(Import::class, 'staff_id');
    }

    /**
     * Get the orders for the staff.
     */
    public function orders()
    {
        return $this->hasMany(Order::class, 'staff_id');
    }

    /**
     * Get the payments for the staff.
     */
    public function payments()
    {
        return $this->hasMany(Payment::class, 'staff_id','id');
    }
}

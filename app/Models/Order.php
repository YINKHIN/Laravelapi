<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'ord_date',
        'staff_id',
        'full_name',
        'cus_id',
        'cus_name',
        'total',
    ];

    protected $casts = [
        'ord_date' => 'datetime',
        'total' => 'decimal:2',
    ];

    /**
     * Get the staff that owns the order.
     */
    public function staff()
    {
        return $this->belongsTo(Staff::class, 'staff_id');
    }

    /**
     * Get the customer that owns the order.
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'cus_id');
    }

    /**
     * Get the order details for the order.
     */
    public function orderDetails()
    {
        return $this->hasMany(OrderDetail::class, 'ord_code');
    }

    /**
     * Get the payments for the order.
     */
    public function payments()
    {
        return $this->hasMany(Payment::class, 'ord_code');
    }
}

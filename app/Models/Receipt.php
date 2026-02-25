<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Receipt extends Model
{
    use HasFactory;

    protected $fillable = [
        'sale_id',
        'receipt_number',
        'receipt_data',
        'format',
        'emailed',
        'sms_sent',
        'printed_at'
    ];

    protected $casts = [
        'emailed' => 'boolean',
        'sms_sent' => 'boolean',
        'receipt_data' => 'array',
        'printed_at' => 'datetime'
    ];

    /**
     * Get the sale that owns the receipt.
     */
    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    /**
     * Generate unique receipt number.
     */
    public static function generateReceiptNumber()
    {
        do {
            $number = 'RCP' . date('Ymd') . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
        } while (self::where('receipt_number', $number)->exists());

        return $number;
    }

    /**
     * Mark as printed.
     */
    public function markAsPrinted()
    {
        $this->update(['printed_at' => now()]);
    }

    /**
     * Mark as emailed.
     */
    public function markAsEmailed()
    {
        $this->update(['emailed' => true]);
    }

    /**
     * Mark SMS as sent.
     */
    public function markSmsSent()
    {
        $this->update(['sms_sent' => true]);
    }
}

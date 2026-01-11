<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    protected $fillable = [
        'user_id',
        'description',
        'amount',
        'currency_id',
        'transaction_type',
        'silo_id',
        'destination_silo_id',
        'category_id',
        'transaction_date',
        'receipt_path',
        'installment_plan_id',
        'recurring_transaction_id',
        'loan_id',
        'status',
        'notes',
        'reference_number',
        'exchange_rate',
        'converted_amount',
        'converted_currency_id'
    ];

    protected $casts = [
        'transaction_date' => 'datetime',
        'amount' => 'decimal:2',
        'converted_amount' => 'decimal:2',
        'exchange_rate' => 'decimal:4'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // public function silo(): BelongsTo
    // {
    //     return $this->belongsTo(Silo::class, 'silo_id');
    // }

    // public function destinationSilo(): BelongsTo
    // {
    //     return $this->belongsTo(Silo::class, 'destination_silo_id');
    // }

    // public function category(): BelongsTo
    // {
    //     return $this->belongsTo(Category::class);
    // }

    // public function currency(): BelongsTo
    // {
    //     return $this->belongsTo(Currency::class);
    // }

    public function getFormattedAmount(): string
    {
        $currency = $this->currency->symbol ?? $this->user->defaultCurrency->symbol ?? 'UGX';
        return $currency . ' ' . number_format(abs($this->amount), 0);
    }

    public function getCategoryColor(): string
    {
        if (!$this->category) {
            return $this->transaction_type === 'income' ? 'bg-green-100' : 'bg-red-100';
        }

        $colors = [
            'food' => 'bg-orange-100',
            'transport' => 'bg-blue-100',
            'utilities' => 'bg-purple-100',
            'entertainment' => 'bg-pink-100',
            'shopping' => 'bg-red-100',
            'income' => 'bg-green-100',
            'transfer' => 'bg-gray-100'
        ];

        return $colors[strtolower($this->category->name)] ?? 'bg-gray-100';
    }

    public function getCategoryIcon(): string
    {
        if (!$this->category) {
            return $this->transaction_type === 'income' ? 'fas fa-money-bill-wave' : 'fas fa-shopping-cart';
        }

        $icons = [
            'food' => 'fas fa-utensils',
            'transport' => 'fas fa-car',
            'utilities' => 'fas fa-bolt',
            'entertainment' => 'fas fa-film',
            'shopping' => 'fas fa-shopping-cart',
            'income' => 'fas fa-money-bill-wave',
            'transfer' => 'fas fa-exchange-alt'
        ];

        return $icons[strtolower($this->category->name)] ?? 'fas fa-receipt';
    }

    public function getCategoryIconColor(): string
    {
        if (!$this->category) {
            return $this->transaction_type === 'income' ? 'text-green-600' : 'text-red-600';
        }

        $colors = [
            'food' => 'text-orange-600',
            'transport' => 'text-blue-600',
            'utilities' => 'text-purple-600',
            'entertainment' => 'text-pink-600',
            'shopping' => 'text-red-600',
            'income' => 'text-green-600',
            'transfer' => 'text-gray-600'
        ];

        return $colors[strtolower($this->category->name)] ?? 'text-gray-600';
    }
}

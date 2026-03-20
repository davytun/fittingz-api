<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;

class PaymentService
{
    public function recordPayment(Order $order, array $data): Payment
    {
        return DB::transaction(function () use ($order, $data) {
            $payment = $order->payments()->create([
                'user_id' => $data['user_id'],
                'amount' => $data['amount'],
                'payment_date' => $data['payment_date'],
                'payment_method' => $data['payment_method'],
                'reference' => $data['reference'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]);

            return $payment;
        });
    }

    public function updatePayment(Payment $payment, array $data): Payment
    {
        return DB::transaction(function () use ($payment, $data) {
            $payment->update($data);
            return $payment->fresh();
        });
    }

    public function deletePayment(Payment $payment): bool
    {
        return DB::transaction(function () use ($payment) {
            return $payment->delete();
        });
    }

    public function getPaymentHistory(Order $order)
    {
        return $order->payments()
            ->orderBy('payment_date', 'desc')
            ->get();
    }

    public function getTotalPaid(Order $order): float
    {
        return (float) $order->payments()->sum('amount');
    }

    public function getBalance(Order $order): float
    {
        return (float) ($order->total_amount - $this->getTotalPaid($order));
    }

    public function getPaymentStatus(Order $order): string
    {
        $balance = $this->getBalance($order);

        if ($balance <= 0) {
            return 'fully_paid';
        }

        if ($this->getTotalPaid($order) > 0) {
            return 'partial';
        }

        return 'unpaid';
    }
}
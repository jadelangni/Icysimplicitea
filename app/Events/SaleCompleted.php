<?php

namespace App\Events;

use App\Models\Sale;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SaleCompleted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $sale;
    public $branchId;
    public $branchName;
    public $amount;
    public $cashierName;
    public $receiptNumber;
    public $itemsCount;

    /**
     * Create a new event instance.
     */
    public function __construct(Sale $sale)
    {
        $this->sale = $sale;
        $this->branchId = $sale->branch_id;
        $this->branchName = $sale->branch->name ?? 'Unknown Branch';
        $this->amount = $sale->total_amount;
        $this->cashierName = $sale->user->name ?? 'Unknown';
        $this->receiptNumber = $sale->receipt_number;
        $this->itemsCount = $sale->items()->count();
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('sales'),
            new Channel('sales.branch.' . $this->branchId),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'sale.completed';
    }

    /**
     * Get the data to broadcast.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'branch_id' => $this->branchId,
            'branch_name' => $this->branchName,
            'amount' => $this->amount,
            'formatted_amount' => '₱' . number_format($this->amount, 2),
            'cashier_name' => $this->cashierName,
            'receipt_number' => $this->receiptNumber,
            'items_count' => $this->itemsCount,
            'timestamp' => now()->toIso8601String(),
        ];
    }
}

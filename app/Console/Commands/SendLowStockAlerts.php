<?php

namespace App\Console\Commands;

use App\Mail\LowStockAlert;
use App\Models\Inventory;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SendLowStockAlerts extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'stock:send-low-alerts';

    /**
     * The console command description.
     */
    protected $description = 'Send email alerts to admins when product stock is low';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Find all inventory items where quantity is at or below minimum stock level
        $lowStockItems = Inventory::with(['product', 'branch'])
            ->whereRaw('quantity <= min_stock_level')
            ->orderBy('quantity', 'asc')
            ->get();

        if ($lowStockItems->isEmpty()) {
            $this->info('No low stock items found. No emails sent.');
            return 0;
        }

        $alerts = $lowStockItems->map(function ($inventory) {
            return [
                'id' => $inventory->id,
                'product_name' => $inventory->product->name ?? 'Unknown',
                'branch_id' => $inventory->branch_id,
                'branch_name' => $inventory->branch->name ?? 'Unknown',
                'quantity' => $inventory->quantity,
                'min_stock_level' => $inventory->min_stock_level,
            ];
        });

        $totalCount = $alerts->count();

        // Get all admin users
        $admins = User::where('role', 'admin')->get();

        if ($admins->isEmpty()) {
            $this->warn('No admin users found to send alerts to.');
            return 0;
        }

        $sentCount = 0;

        foreach ($admins as $admin) {
            // Use alert_email if set, otherwise fall back to login email
            $recipientEmail = $admin->alert_email ?: $admin->email;

            if (empty($recipientEmail)) {
                continue;
            }

            try {
                Mail::to($recipientEmail)->send(new LowStockAlert($alerts, $totalCount));
                $sentCount++;
                $this->info("Low stock alert sent to: {$recipientEmail}");
            } catch (\Exception $e) {
                Log::error("Failed to send low stock alert to {$recipientEmail}: " . $e->getMessage());
                $this->error("Failed to send to {$recipientEmail}: " . $e->getMessage());
            }
        }

        $this->info("Done. {$totalCount} low stock items, {$sentCount} email(s) sent.");

        return 0;
    }
}

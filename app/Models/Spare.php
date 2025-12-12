<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Spare extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'product_code',
        'brand_name',
        'item_name',
        'category',
        'city_id',
        'sector_id',
        'unit_price',
        'total_received_quantity',
        'issued_quantity',
        'stock_quantity',
        'threshold_level',
        'supplier',
        'description',
        'last_stock_in_at',
        'last_updated',
    ];
    protected $casts = [
        'unit_price' => 'decimal:2',
        'total_received_quantity' => 'integer',
        'issued_quantity' => 'integer',
        'stock_quantity' => 'integer',
        'threshold_level' => 'integer',
        'last_stock_in_at' => 'datetime',
        'last_updated' => 'datetime',
    ];

    /**
     * Get the stock logs for the spare.
     */
    public function stockLogs(): HasMany
    {
        return $this->hasMany(SpareStockLog::class, 'spare_id', 'id');
    }

    /**
     * Get the complaint spares for the spare.
     */
    public function complaintSpares(): HasMany
    {
        return $this->hasMany(ComplaintSpare::class, 'spare_id', 'id');
    }

    /**
     * Get the spare approval items for the spare.
     */
    public function approvalItems(): HasMany
    {
        return $this->hasMany(SpareApprovalItem::class, 'spare_id', 'id');
    }

    /**
     * Get the city that owns the spare.
     */
    public function city()
    {
        return $this->belongsTo(City::class, 'city_id', 'id');
    }

    /**
     * Get the sector that owns the spare.
     */
    public function sector()
    {
        return $this->belongsTo(Sector::class, 'sector_id', 'id');
    }

    /**
     * Get available categories
     */
    public static function getCategories(): array
    {
        return [
            'technical' => 'Technical',
            'service' => 'Service',
            'billing' => 'Billing',
            'sanitary' => 'Sanitary',
            'electric' => 'Electric',
            'kitchen' => 'Kitchen',
            'plumbing' => 'Plumbing',
            'other' => 'Other',
        ];
    }

    /**
     * Canonical DB categories (enum values in `spares` table)
     */
    public static function getCanonicalCategories(): array
    {
        return ['electrical', 'plumbing', 'kitchen', 'general', 'tools', 'consumables'];
    }

    /**
     * Normalize any UI category key to canonical DB value
     */
    public static function normalizeCategory(string $category): string
    {
        $map = [
            'electric' => 'electrical',
            'sanitary' => 'plumbing',
            'technical' => 'general',
            'service' => 'consumables',
            'billing' => 'consumables',
            'other' => 'general',
            // direct passthroughs
            'kitchen' => 'kitchen',
            'plumbing' => 'plumbing',
            'electrical' => 'electrical',
            'general' => 'general',
            'tools' => 'tools',
            'consumables' => 'consumables',
        ];
        return $map[$category] ?? $category;
    }

    /**
     * Get category display name
     */
    public function getCategoryDisplayAttribute(): string
    {
        return self::getCategories()[$this->category] ?? $this->category;
    }

    /**
     * Check if stock is low
     */
    public function isLowStock(): bool
    {
        return $this->stock_quantity <= $this->threshold_level;
    }

    /**
     * Check if stock is out
     */
    public function isOutOfStock(): bool
    {
        return $this->stock_quantity <= 0;
    }

    /**
     * Check if stock is sufficient
     */
    public function isStockSufficient(int $requiredQuantity): bool
    {
        return $this->stock_quantity >= $requiredQuantity;
    }

    /**
     * Get stock status
     */
    public function getStockStatusAttribute(): string
    {
        if ($this->isOutOfStock()) {
            return 'out_of_stock';
        } elseif ($this->isLowStock()) {
            return 'low_stock';
        } else {
            return 'in_stock';
        }
    }

    /**
     * Get stock status display
     */
    public function getStockStatusDisplayAttribute(): string
    {
        $statuses = [
            'out_of_stock' => 'Out of Stock',
            'low_stock' => 'Low Stock',
            'in_stock' => 'In Stock',
        ];

        return $statuses[$this->getStockStatusAttribute()] ?? 'Unknown';
    }

    /**
     * Get stock status color
     */
    public function getStockStatusColorAttribute(): string
    {
        $colors = [
            'out_of_stock' => 'danger',
            'low_stock' => 'warning',
            'in_stock' => 'success',
        ];

        return $colors[$this->getStockStatusAttribute()] ?? 'muted';
    }

    /**
     * Get total value of current stock
     */
    public function getTotalValueAttribute(): float
    {
        return $this->stock_quantity * $this->unit_price;
    }

    /**
     * Get formatted unit price
     */
    public function getFormattedUnitPriceAttribute(): string
    {
        return '₹' . number_format($this->unit_price, 2);
    }

    /**
     * Get formatted total value
     */
    public function getFormattedTotalValueAttribute(): string
    {
        return '₹' . number_format($this->getTotalValueAttribute(), 2);
    }

    /**
     * Percentage of utilized stock based on current stock vs total received
     * Shows what percentage has been used (total_received - current_stock) / total_received
     */
    public function getUtilizationPercentAttribute(): float
    {
        $totalReceived = (int)($this->total_received_quantity ?? 0);
        if ($totalReceived <= 0) {
            return 0.0;
        }
        $currentStock = (int)($this->stock_quantity ?? 0);
        $usedStock = $totalReceived - $currentStock;
        // Calculate utilization percentage (what has been used)
        $percent = ($usedStock / $totalReceived) * 100.0;
        // Clamp between 0 and 100 for display sanity
        if ($percent < 0) {
            return 0.0;
        }
        if ($percent > 100) {
            return 100.0;
        }
        return round($percent, 2);
    }

    /**
     * Get last stock out date from stock logs
     */
    public function getLastStockOutAttribute()
    {
        // Use eager loaded logs if available
        if ($this->relationLoaded('stockLogs')) {
            $lastOutLog = $this->stockLogs
                ->where('change_type', 'out')
                ->sortByDesc('created_at')
                ->first();
        } else {
            $lastOutLog = $this->stockLogs()
                ->where('change_type', 'out')
                ->orderBy('created_at', 'desc')
                ->first();
        }
        
        return $lastOutLog ? $lastOutLog->created_at : null;
    }

    /**
     * Add stock
     */
    public function addStock(int $quantity, string $remarks = null, int $referenceId = null): void
    {
        $this->stock_quantity += $quantity;
        // Track cumulative received
        $this->total_received_quantity = (int)($this->total_received_quantity ?? 0) + $quantity;
        $this->last_stock_in_at = now();
        $this->last_updated = now();
        $this->save();

        // Log the stock change with brand name
        $this->stockLogs()->create([
            'change_type' => 'in',
            'quantity' => $quantity,
            'brand_name' => $this->brand_name,
            'reference_id' => $referenceId,
            'remarks' => $remarks,
        ]);
    }

    /**
     * Return stock back to inventory (undo issued) without affecting total received
     */
    public function returnStock(int $quantity, string $remarks = null, int $referenceId = null): void
    {
        if ($quantity <= 0) {
            return;
        }
        $this->stock_quantity += $quantity;
        // Decrease issued to reflect return, never below zero
        $this->issued_quantity = max(0, (int)($this->issued_quantity ?? 0) - $quantity);
        $this->last_updated = now();
        $this->save();

        // Log the stock change with brand name
        $this->stockLogs()->create([
            'change_type' => 'in',
            'quantity' => $quantity,
            'brand_name' => $this->brand_name,
            'reference_id' => $referenceId,
            'remarks' => $remarks ?? 'Returned to stock',
        ]);
    }

    /**
     * Get available stock by brand (FIFO calculation)
     * Returns array of brands with their available stock, sorted by oldest first
     */
    public function getAvailableStockByBrand(): array
    {
        $inLogs = $this->stockLogs()
            ->where('change_type', 'in')
            ->orderBy('created_at', 'asc')
            ->get();
        
        $outLogs = $this->stockLogs()
            ->where('change_type', 'out')
            ->orderBy('created_at', 'asc')
            ->get();
        
        // Calculate net stock by brand (FIFO)
        $stockByBrand = [];
        
        // First, process all 'in' logs to build available stock
        foreach ($inLogs as $inLog) {
            $brandName = $inLog->brand_name ?? $this->brand_name ?? 'N/A';
            if (!isset($stockByBrand[$brandName])) {
                $stockByBrand[$brandName] = [
                    'brand_name' => $brandName,
                    'available' => 0,
                    'first_in_date' => $inLog->created_at,
                ];
            }
            $stockByBrand[$brandName]['available'] += $inLog->quantity;
        }
        
        // Then, process all 'out' logs to deduct stock (FIFO - oldest first)
        foreach ($outLogs as $outLog) {
            $remainingToDeduct = $outLog->quantity;
            
            // Deduct from oldest brand first
            foreach ($stockByBrand as $brandName => &$brandData) {
                if ($remainingToDeduct <= 0) break;
                
                if ($brandData['available'] > 0) {
                    $deducted = min($brandData['available'], $remainingToDeduct);
                    $brandData['available'] -= $deducted;
                    $remainingToDeduct -= $deducted;
                }
            }
        }
        
        // Filter out brands with zero or negative stock and sort by oldest first
        $availableBrands = array_filter($stockByBrand, function($brand) {
            return $brand['available'] > 0;
        });
        
        // Sort by first_in_date (oldest first)
        usort($availableBrands, function($a, $b) {
            return $a['first_in_date'] <=> $b['first_in_date'];
        });
        
        return array_values($availableBrands);
    }

    /**
     * Remove stock using FIFO (First In First Out) - issues from oldest brand first
     */
    public function removeStock(int $quantity, string $remarks = null, int $referenceId = null): bool
    {
        if (!$this->isStockSufficient($quantity)) {
            return false;
        }

        // Get available stock by brand (FIFO)
        $availableStockByBrand = $this->getAvailableStockByBrand();
        
        // If no brand-specific stock available, use current brand
        $brandToIssue = $this->brand_name ?? 'N/A';
        
        // Determine which brand(s) to issue from (FIFO)
        $remainingToIssue = $quantity;
        $brandsToLog = [];
        
        foreach ($availableStockByBrand as $brandData) {
            if ($remainingToIssue <= 0) break;
            
            $availableFromBrand = $brandData['available'];
            if ($availableFromBrand > 0) {
                $toIssueFromBrand = min($availableFromBrand, $remainingToIssue);
                $brandsToLog[] = [
                    'brand_name' => $brandData['brand_name'],
                    'quantity' => $toIssueFromBrand,
                ];
                $remainingToIssue -= $toIssueFromBrand;
                
                // Use the first brand for primary tracking
                if ($brandToIssue === ($this->brand_name ?? 'N/A')) {
                    $brandToIssue = $brandData['brand_name'];
                }
            }
        }
        
        // If no brand-specific stock found, use current brand
        if (empty($brandsToLog)) {
            $brandsToLog[] = [
                'brand_name' => $brandToIssue,
                'quantity' => $quantity,
            ];
        }

        $this->stock_quantity -= $quantity;
        // Track cumulative issued
        $this->issued_quantity = max(0, (int)($this->issued_quantity ?? 0) + $quantity);
        $this->last_updated = now();
        $saved = $this->save();

        if (!$saved) {
            \Log::error('Failed to save stock reduction', [
                'spare_id' => $this->id,
                'quantity' => $quantity,
                'new_stock' => $this->stock_quantity
            ]);
            return false;
        }

        // Log the stock change(s) with brand name(s)
        try {
            // If issuing from multiple brands, create separate logs
            if (count($brandsToLog) > 1) {
                foreach ($brandsToLog as $brandLog) {
                    $this->stockLogs()->create([
                        'change_type' => 'out',
                        'quantity' => $brandLog['quantity'],
                        'brand_name' => $brandLog['brand_name'],
                        'reference_id' => $referenceId,
                        'remarks' => $remarks . (count($brandsToLog) > 1 ? ' (FIFO: ' . $brandLog['brand_name'] . ')' : ''),
                    ]);
                }
            } else {
                // Single brand issue
                $this->stockLogs()->create([
                    'change_type' => 'out',
                    'quantity' => $quantity,
                    'brand_name' => $brandsToLog[0]['brand_name'],
                    'reference_id' => $referenceId,
                    'remarks' => $remarks,
                ]);
            }
        } catch (\Exception $e) {
            \Log::error('Failed to create stock log', [
                'spare_id' => $this->id,
                'error' => $e->getMessage()
            ]);
        }

        return true;
    }

    /**
     * Get stock movement summary
     */
    public function getStockMovementSummary(int $days = 30): array
    {
        $logs = $this->stockLogs()
            ->where('created_at', '>=', now()->subDays($days))
            ->get();

        $inStock = $logs->where('change_type', 'in')->sum('quantity');
        $outStock = $logs->where('change_type', 'out')->sum('quantity');

        return [
            'in_stock' => $inStock,
            'out_stock' => $outStock,
            'net_movement' => $inStock - $outStock,
            'movement_count' => $logs->count(),
        ];
    }

    /**
     * Scope for low stock items (excludes out of stock items)
     */
    public function scopeLowStock($query)
    {
        return $query->whereRaw('stock_quantity <= threshold_level')
                     ->where('stock_quantity', '>', 0);
    }

    /**
     * Scope for out of stock items
     */
    public function scopeOutOfStock($query)
    {
        return $query->where('stock_quantity', '<=', 0);
    }

    /**
     * Scope for in stock items
     */
    public function scopeInStock($query)
    {
        return $query->whereRaw('stock_quantity > threshold_level');
    }

    /**
     * Scope by category
     */
    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope for items with stock above threshold
     */
    public function scopeAboveThreshold($query)
    {
        return $query->whereRaw('stock_quantity > threshold_level');
    }

    /**
     * Scope for recently updated items
     */
    public function scopeRecentlyUpdated($query, $days = 7)
    {
        return $query->where('last_updated', '>=', now()->subDays($days));
    }
}

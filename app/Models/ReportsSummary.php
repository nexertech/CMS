<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReportsSummary extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'reports_summary';

    protected $fillable = [
        'report_type',
        'period_start',
        'period_end',
        'generated_at',
        'data_json',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'generated_at' => 'datetime',
        'data_json' => 'array',
    ];

    /**
     * Get available report types
     */
    public static function getReportTypes(): array
    {
        return [
            'complaints' => 'Complaints Report',
            'spares' => 'Spare Parts Report',
            'employees' => 'Employee Report',
        ];
    }

    /**
     * Get report type display name
     */
    public function getReportTypeDisplayAttribute(): string
    {
        return self::getReportTypes()[$this->report_type] ?? $this->report_type;
    }

    /**
     * Get formatted period
     */
    public function getFormattedPeriodAttribute(): string
    {
        return $this->period_start->format('M d, Y') . ' - ' . $this->period_end->format('M d, Y');
    }

    /**
     * Get formatted generated date
     */
    public function getFormattedGeneratedDateAttribute(): string
    {
        return $this->generated_at->format('M d, Y H:i:s');
    }

    /**
     * Get generated date ago
     */
    public function getGeneratedDateAgoAttribute(): string
    {
        return $this->generated_at->diffForHumans();
    }

    /**
     * Check if report is recent (within specified days)
     */
    public function isRecent(int $days = 7): bool
    {
        return $this->generated_at->isAfter(now()->subDays($days));
    }

    /**
     * Check if report is expired (older than specified days)
     */
    public function isExpired(int $days = 30): bool
    {
        return $this->generated_at->isBefore(now()->subDays($days));
    }

    /**
     * Get report data as array
     */
    public function getDataArrayAttribute(): array
    {
        return $this->data_json ?? [];
    }

    /**
     * Get specific data from report
     */
    public function getData(string $key, $default = null)
    {
        return data_get($this->data_json, $key, $default);
    }

    /**
     * Set specific data in report
     */
    public function setData(string $key, $value): void
    {
        $data = $this->data_json ?? [];
        data_set($data, $key, $value);
        $this->data_json = $data;
    }

    /**
     * Get report summary
     */
    public function getSummaryAttribute(): array
    {
        return [
            'type' => $this->getReportTypeDisplayAttribute(),
            'period' => $this->getFormattedPeriodAttribute(),
            'generated' => $this->getFormattedGeneratedDateAttribute(),
            'is_recent' => $this->isRecent(),
            'is_expired' => $this->isExpired(),
        ];
    }

    /**
     * Scope for specific report type
     */
    public function scopeByType($query, $type)
    {
        return $query->where('report_type', $type);
    }

    /**
     * Scope for recent reports
     */
    public function scopeRecent($query, $days = 7)
    {
        return $query->where('generated_at', '>=', now()->subDays($days));
    }

    /**
     * Scope for expired reports
     */
    public function scopeExpired($query, $days = 30)
    {
        return $query->where('generated_at', '<', now()->subDays($days));
    }

    /**
     * Scope for reports in date range
     */
    public function scopeInDateRange($query, $startDate, $endDate)
    {
        return $query->where('period_start', '>=', $startDate)
            ->where('period_end', '<=', $endDate);
    }

    /**
     * Scope for reports by period
     */
    public function scopeByPeriod($query, $periodStart, $periodEnd)
    {
        return $query->where('period_start', $periodStart)
            ->where('period_end', $periodEnd);
    }

    /**
     * Get or create report for specific type and period
     */
    public static function getOrCreate(string $reportType, $periodStart, $periodEnd)
    {
        return static::firstOrCreate(
            [
                'report_type' => $reportType,
                'period_start' => $periodStart,
                'period_end' => $periodEnd,
            ],
            [
                'generated_at' => now(),
                'data_json' => [],
            ]
        );
    }

    /**
     * Update report data
     */
    public function updateData(array $data): void
    {
        $this->data_json = $data;
        $this->generated_at = now();
        $this->save();
    }

    /**
     * Clear report data
     */
    public function clearData(): void
    {
        $this->data_json = [];
        $this->generated_at = now();
        $this->save();
    }
}

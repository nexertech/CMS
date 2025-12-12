<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class ComplaintAttachment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'complaint_id',
        'file_path',
        'uploaded_at',
    ];

    protected $casts = [
        'uploaded_at' => 'datetime',
    ];

    /**
     * Get the complaint that owns the attachment.
     */
    public function complaint(): BelongsTo
    {
        return $this->belongsTo(Complaint::class, 'complaint_id', 'id');
    }

    /**
     * Get the file name from path
     */
    public function getFileNameAttribute(): string
    {
        return basename($this->file_path);
    }

    /**
     * Get the file extension
     */
    public function getFileExtensionAttribute(): string
    {
        return pathinfo($this->file_path, PATHINFO_EXTENSION);
    }

    /**
     * Get the file size
     */
    public function getFileSizeAttribute(): int
    {
        if (Storage::exists($this->file_path)) {
            return Storage::size($this->file_path);
        }
        return 0;
    }

    /**
     * Get the file size in human readable format
     */
    public function getFileSizeHumanAttribute(): string
    {
        $bytes = $this->getFileSizeAttribute();
        
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        
        $bytes /= (1 << (10 * $pow));
        
        return round($bytes, 2) . ' ' . $units[$pow];
    }

    /**
     * Check if file is an image
     */
    public function isImage(): bool
    {
        $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'];
        return in_array(strtolower($this->getFileExtensionAttribute()), $imageExtensions);
    }

    /**
     * Check if file is a document
     */
    public function isDocument(): bool
    {
        $documentExtensions = ['pdf', 'doc', 'docx', 'txt', 'rtf'];
        return in_array(strtolower($this->getFileExtensionAttribute()), $documentExtensions);
    }

    /**
     * Get the file URL
     */
    public function getFileUrlAttribute(): string
    {
        return Storage::url($this->file_path);
    }

    /**
     * Check if file exists
     */
    public function fileExists(): bool
    {
        return Storage::exists($this->file_path);
    }

    /**
     * Get file type for display
     */
    public function getFileTypeAttribute(): string
    {
        if ($this->isImage()) {
            return 'Image';
        } elseif ($this->isDocument()) {
            return 'Document';
        } else {
            return 'File';
        }
    }

    /**
     * Get file icon class
     */
    public function getFileIconAttribute(): string
    {
        if ($this->isImage()) {
            return 'fas fa-image';
        } elseif ($this->isDocument()) {
            return 'fas fa-file-alt';
        } else {
            return 'fas fa-file';
        }
    }

    /**
     * Scope for images only
     */
    public function scopeImages($query)
    {
        return $query->whereRaw("LOWER(file_path) LIKE '%.jpg' OR LOWER(file_path) LIKE '%.jpeg' OR LOWER(file_path) LIKE '%.png' OR LOWER(file_path) LIKE '%.gif' OR LOWER(file_path) LIKE '%.bmp' OR LOWER(file_path) LIKE '%.webp'");
    }

    /**
     * Scope for documents only
     */
    public function scopeDocuments($query)
    {
        return $query->whereRaw("LOWER(file_path) LIKE '%.pdf' OR LOWER(file_path) LIKE '%.doc' OR LOWER(file_path) LIKE '%.docx' OR LOWER(file_path) LIKE '%.txt' OR LOWER(file_path) LIKE '%.rtf'");
    }

    /**
     * Scope for recent uploads
     */
    public function scopeRecent($query, $days = 7)
    {
        return $query->where('uploaded_at', '>=', now()->subDays($days));
    }
}

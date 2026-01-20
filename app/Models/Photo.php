<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Photo extends Model
{
    protected $fillable = [
        'original_filename',
        'stored_filename',
        'file_path',
        'file_size',
        'mime_type',
        'width',
        'height',
        'related_type',
        'related_id',
        'description',
        'uploaded_by',
    ];

    protected $casts = [
        'width' => 'integer',
        'height' => 'integer',
        'file_size' => 'string',
        'uploaded_by' => 'integer',
        'related_id' => 'integer',
    ];

    /**
     * Get the user who uploaded the photo
     */
    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * Get the full URL to the photo
     */
    public function getUrlAttribute()
    {
        if (Storage::disk('public')->exists($this->file_path)) {
            return Storage::disk('public')->url($this->file_path);
        }
        return null;
    }

    /**
     * Get the full path to the photo
     */
    public function getFullPathAttribute()
    {
        return storage_path('app/public/' . $this->file_path);
    }

    /**
     * Check if photo file exists
     */
    public function exists()
    {
        return Storage::disk('public')->exists($this->file_path);
    }

    /**
     * Get photo content as binary
     */
    public function getContent()
    {
        if ($this->exists()) {
            return Storage::disk('public')->get($this->file_path);
        }
        return null;
    }

    /**
     * Scope to filter by related type and id
     */
    public function scopeRelated($query, $type, $id = null)
    {
        $query->where('related_type', $type);
        if ($id !== null) {
            $query->where('related_id', $id);
        }
        return $query;
    }
}

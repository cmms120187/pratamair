<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Standard extends Model
{
    protected $fillable = [
        'name',
        'reference_type',
        'reference_code',
        'reference_name',
        'class',
        'unit',
        'min_value',
        'max_value',
        'target_value',
        'description',
        'keterangan',
        'photo',
        'photo_id',
        'machine_type_id',
        'status',
    ];

    protected $casts = [
        'min_value' => 'decimal:4',
        'max_value' => 'decimal:4',
        'target_value' => 'decimal:4',
    ];

    // Relationships
    public function machineTypes()
    {
        return $this->belongsToMany(MachineType::class, 'machine_type_standard');
    }
    
    // Keep old relationship for backward compatibility (if needed)
    public function machineType()
    {
        return $this->belongsToMany(MachineType::class, 'machine_type_standard')->first();
    }

    public function predictiveMaintenanceSchedules()
    {
        return $this->hasMany(PredictiveMaintenanceSchedule::class);
    }

    public function variants()
    {
        return $this->hasMany(StandardVariant::class);
    }

    // Photos relationship - many-to-many (legacy StandardPhoto)
    public function photos()
    {
        return $this->belongsToMany(StandardPhoto::class, 'standard_standard_photo', 'standard_id', 'standard_photo_id');
    }

    // Photo relationship - belongs to Photo (new system)
    public function photoModel()
    {
        return $this->belongsTo(Photo::class, 'photo_id');
    }

    // Get photo URL (prioritize photo_id, then photos relationship, fallback to photo path)
    public function getPhotoUrlAttribute()
    {
        // Priority 1: photo_id (new system)
        if ($this->photo_id && $this->photoModel) {
            return route('photos.show', $this->photo_id);
        }
        
        // Priority 2: photos relationship (many-to-many)
        if ($this->relationLoaded('photos') && $this->photos && $this->photos->count() > 0) {
            $firstPhoto = $this->photos->first();
            // Try to find in photos table
            $photo = Photo::where('file_path', $firstPhoto->photo_path)->first();
            if ($photo) {
                return route('photos.show', $photo->id);
            }
            // Fallback to old path - use Storage::url() for proper path
            $photoPath = $firstPhoto->photo_path;
            if (strpos($photoPath, 'images/') === 0) {
                return asset($photoPath);
            }
            return Storage::disk('public')->url($photoPath);
        }
        
        // Priority 3: photo field (legacy)
        if ($this->photo) {
            // Try to find in photos table
            $photo = Photo::where('file_path', $this->photo)
                ->orWhere('file_path', 'like', '%' . basename($this->photo))
                ->first();
            if ($photo) {
                return route('photos.show', $photo->id);
            }
            if (strpos($this->photo, 'images/') === 0) {
                return asset($this->photo);
            }
            return Storage::disk('public')->url($this->photo);
        }
        
        return null;
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    // Helper method to check if a value is within standard
    public function isValueWithinStandard($value)
    {
        if ($this->min_value !== null && $value < $this->min_value) {
            return false;
        }
        if ($this->max_value !== null && $value > $this->max_value) {
            return false;
        }
        return true;
    }

    // Get measurement status based on value
    // Priority: Use StandardVariant (zones) if available, otherwise use min/max values
    public function getMeasurementStatus($value)
    {
        // First, check if standard has variants (zones)
        $variants = $this->variants()->orderBy('order', 'asc')->get();
        
        if ($variants->count() > 0) {
            // Find the variant (zone) that matches the value
            foreach ($variants as $variant) {
                $minVal = $variant->min_value;
                $maxVal = $variant->max_value;
                
                // Check if value falls within this variant's range
                // Use inclusive boundaries: value >= minVal AND value <= maxVal
                $inRange = true;
                if ($minVal !== null && $value < $minVal) {
                    $inRange = false;
                }
                if ($maxVal !== null && $value > $maxVal) {
                    $inRange = false;
                }
                
                if ($inRange) {
                    // Map color to status based on variant color
                    return $this->mapColorToStatus($variant->color);
                }
            }
            
            // If value is outside all variants, check if it's below min or above max
            $firstVariant = $variants->first();
            $lastVariant = $variants->last();
            
            if ($firstVariant && $firstVariant->min_value !== null && $value < $firstVariant->min_value) {
                return 'critical'; // Below minimum zone
            }
            if ($lastVariant && $lastVariant->max_value !== null && $value > $lastVariant->max_value) {
                return 'critical'; // Above maximum zone
            }
            
            // If we get here, value is within overall range but didn't match any variant
            // This shouldn't happen, but return normal as fallback
            return 'normal';
        }
        
        // Fallback: Use old logic with min/max values if no variants
        if (!$this->isValueWithinStandard($value)) {
            // Check if critical (far from range) or warning (close to limit)
            if ($this->min_value !== null && $value < $this->min_value) {
                $diff = abs($value - $this->min_value);
                $range = $this->max_value !== null ? ($this->max_value - $this->min_value) : abs($this->min_value);
                return ($diff > $range * 0.2) ? 'critical' : 'warning';
            }
            if ($this->max_value !== null && $value > $this->max_value) {
                $diff = abs($value - $this->max_value);
                $range = $this->max_value !== null ? ($this->max_value - $this->min_value) : abs($this->max_value);
                return ($diff > $range * 0.2) ? 'critical' : 'warning';
            }
        }
        return 'normal';
    }
    
    // Map color code to status
    // Mapping:
    // - Green = normal (Aman)
    // - Yellow = warning (Perlu Perhatian)
    // - Orange = caution (Perlu Pengawasan)
    // - Red = critical (Perlu Perbaikan)
    private function mapColorToStatus($color)
    {
        if (!$color) {
            return 'normal';
        }
        
        // Normalize color to lowercase for comparison
        $colorLower = strtolower(trim($color));
        
        // Green colors = normal (Aman)
        if (strpos($colorLower, '#22c55e') !== false || 
            strpos($colorLower, '#10b981') !== false || 
            strpos($colorLower, '#16a34a') !== false ||
            strpos($colorLower, 'green') !== false ||
            strpos($colorLower, '#00ff00') !== false) {
            return 'normal';
        }
        
        // Yellow colors = warning (Perlu Perhatian)
        if (strpos($colorLower, '#facc15') !== false || 
            strpos($colorLower, '#eab308') !== false || 
            strpos($colorLower, '#ca8a04') !== false ||
            strpos($colorLower, 'yellow') !== false ||
            strpos($colorLower, '#ffff00') !== false) {
            return 'warning';
        }
        
        // Orange colors = caution (Perlu Pengawasan)
        if (strpos($colorLower, '#fb923c') !== false || 
            strpos($colorLower, '#f97316') !== false || 
            strpos($colorLower, '#ea580c') !== false ||
            strpos($colorLower, 'orange') !== false ||
            strpos($colorLower, '#ffa500') !== false) {
            return 'caution';
        }
        
        // Red colors = critical (Perlu Perbaikan)
        if (strpos($colorLower, '#ef4444') !== false || 
            strpos($colorLower, '#dc2626') !== false || 
            strpos($colorLower, '#b91c1c') !== false ||
            strpos($colorLower, 'red') !== false ||
            strpos($colorLower, '#ff0000') !== false) {
            return 'critical';
        }
        
        // Default to normal if color not recognized
        return 'normal';
    }
}

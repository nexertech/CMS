<?php

namespace App\Traits;

use App\Models\Complaint;
use App\Models\Employee;
use Illuminate\Database\Eloquent\Builder;

trait LocationFilterTrait
{
    /**
     * Apply location-based filtering to complaints query
     */
    public function filterComplaintsByLocation(Builder $query, $user): Builder
    {
        if (!$user) {
            return $query;
        }

        if ($this->hasGlobalAccess($user)) {
            return $query;
        }

        $ids = $this->getNormalizedLocationIds($user);
        $cityIds = $ids['city_ids'];
        $sectorIds = $ids['sector_ids'];

        if (empty($cityIds) && empty($sectorIds)) {
            if ($this->hasGlobalAccess($user)) {
                return $query;
            }
            return $query->whereRaw('1 = 0');
        }

        // 2. Inclusive Filtering: Match via House relationship OR direct Complaint location (for house-less complaints)
        return $query->where(function ($q) use ($cityIds, $sectorIds) {
            // Match via House relationship
            $q->whereHas('house', function ($hq) use ($cityIds, $sectorIds) {
                    if (!empty($sectorIds)) {
                        $hq->whereIn('sector_id', $sectorIds);
                    }
                    elseif (!empty($cityIds)) {
                        $hq->whereIn('city_id', $cityIds);
                    }
                }
                )
                    // OR Match via direct complaint columns (for complaints without a house)
                    ->orWhere(function ($cq) use ($cityIds, $sectorIds) {
                $cq->whereNull('complaints.house_id');
                if (!empty($sectorIds)) {
                    $cq->whereIn('complaints.sector_id', $sectorIds);
                }
                elseif (!empty($cityIds)) {
                    $cq->whereIn('complaints.city_id', $cityIds);
                }
            }
            );
        });
    }

    /**
     * Apply location-based filtering to employees query
     */
    public function filterEmployeesByLocation(Builder $query, $user): Builder
    {
        if (!$user) {
            return $query;
        }

        if ($this->hasGlobalAccess($user)) {
            return $query;
        }

        $ids = $this->getNormalizedLocationIds($user);
        $cityIds = $ids['city_ids'];
        $sectorIds = $ids['sector_ids'];

        if (empty($cityIds) && empty($sectorIds)) {
            if ($this->hasGlobalAccess($user)) {
                return $query;
            }
            return $query->whereRaw('1 = 0');
        }

        if (!empty($sectorIds)) {
            $query->whereIn('sector_id', $sectorIds);
        }
        elseif (!empty($cityIds)) {
            $query->whereIn('city_id', $cityIds);
        }

        return $query;
    }

    /**
     * Check if user can view all data (Global Access)
     * This checks for Admin/Director roles OR if the user has no specific restrictions (assigned all)
     */
    public function hasGlobalAccess($user): bool
    {
        if (!$user) {
            return false;
        }

        $roleName = strtolower($user->role->role_name ?? '');
        
        // 1. Check for traditionally global roles
        if (in_array($roleName, ['admin', 'super admin', 'director'])) {
            return true;
        }

        // 2. Check for "All" access via empty location restrictions for internal users
        $ids = $this->getNormalizedLocationIds($user);
        if (empty($ids['city_ids']) && empty($ids['sector_ids'])) {
            // Internal users (Admin panel) with no restrictions have global access
            if ($user instanceof \App\Models\User) {
                return true;
            }
        }

        // 3. Check if they have ALL cities/sectors assigned (Dynamic All Access)
        static $totalCitiesCount = null;
        if ($totalCitiesCount === null) {
            $totalCitiesCount = \App\Models\City::where('status', 1)->count();
        }

        if ($totalCitiesCount > 0 && count($ids['city_ids']) >= $totalCitiesCount) {
            return true;
        }

        return false;
    }

    /**
     * Alias for backward compatibility or clarity
     */
    public function canViewAllData($user): bool
    {
        return $this->hasGlobalAccess($user);
    }

    public function getUserCityIds($user): ?array
    {
        if (!$user) {
            return null;
        }

        $ids = $this->getNormalizedLocationIds($user);
        $cityIds = $ids['city_ids'];
        $sectorIds = $ids['sector_ids'];

        // If no locations assigned, return null (all cities) for Admins/Directors
        // This is primarily for populating form dropdowns/checkboxes
        if (empty($cityIds) && empty($sectorIds)) {
            $roleName = strtolower($user->role->role_name ?? '');
            if (in_array($roleName, ['admin', 'director'])) {
                return null;
            }
            return [];
        }

        return $cityIds;
    }

    /**
     * Get user's accessible sector IDs (null means all sectors)
     */
    public function getUserSectorIds($user): ?array
    {
        if (!$user || !$user->role) {
            return null;
        }

        $roleName = strtolower($user->role->role_name ?? '');

        $ids = $this->getNormalizedLocationIds($user);
        $cityIds = $ids['city_ids'];
        $sectorIds = $ids['sector_ids'];

        // If no locations assigned, return null (all sectors) for Admins/Directors
        // This is primarily for populating form dropdowns/checkboxes
        if (empty($cityIds) && empty($sectorIds)) {
            if (in_array($roleName, ['admin', 'director'])) {
                return null;
            }
            return [];
        }

        // If user has sector_ids, return them
        if (!empty($sectorIds)) {
            return $sectorIds;
        }

        // If user has city_ids but no sector_ids, they can see all sectors in their cities
        if (!empty($cityIds)) {
            return \App\Models\Sector::whereIn('city_id', $cityIds)
                ->pluck('id')
                ->toArray();
        }

        return [];
    }

    /**
     * Apply location-based filtering to spares query
     */
    public function filterSparesByLocation(Builder $query, $user): Builder
    {
        if (!$user) {
            $query->whereRaw('1 = 0');
            return $query;
        }

        if ($this->hasGlobalAccess($user)) {
            return $query;
        }

        $ids = $this->getNormalizedLocationIds($user);
        $cityIds = $ids['city_ids'];
        $sectorIds = $ids['sector_ids'];

        if (empty($cityIds) && empty($sectorIds)) {
            if ($this->hasGlobalAccess($user)) {
                return $query;
            }
            return $query->whereRaw('1 = 0');
        }

        if (!empty($sectorIds)) {
            $query->whereIn('sector_id', $sectorIds);
        }
        elseif (!empty($cityIds)) {
            $query->whereIn('city_id', $cityIds);
        }

        return $query;
    }

    /**
     * Apply location-based filtering to houses query
     */
    public function filterHousesByLocation(Builder $query, $user): Builder
    {
        if (!$user) {
            return $query;
        }

        if ($this->hasGlobalAccess($user)) {
            return $query;
        }

        $ids = $this->getNormalizedLocationIds($user);
        $cityIds = $ids['city_ids'];
        $sectorIds = $ids['sector_ids'];

        if (empty($cityIds) && empty($sectorIds)) {
            if ($this->hasGlobalAccess($user)) {
                return $query;
            }
            return $query->whereRaw('1 = 0');
        }

        if (!empty($sectorIds)) {
            $query->whereIn('sector_id', $sectorIds);
        }
        elseif (!empty($cityIds)) {
            $query->whereIn('city_id', $cityIds);
        }

        return $query;
    }
    /**
     * Apply location-based filtering to users query
     */
    /**
     * Apply location-based filtering to users query
     */
    public function filterUsersByLocation(Builder $query, $user): Builder
    {
        if (!$user) {
            return $query;
        }

        if ($this->hasGlobalAccess($user)) {
            return $query;
        }

        $ids = $this->getNormalizedLocationIds($user);
        $cityIds = $ids['city_ids'];
        $sectorIds = $ids['sector_ids'];

        if (empty($cityIds) && empty($sectorIds)) {
            if ($this->hasGlobalAccess($user)) {
                return $query;
            }
            $query->whereRaw('1 = 0');
            $query->orWhere('id', $user->id);
            return $query;
        }

        if (!empty($sectorIds)) {
            $query->where(function($q) use ($sectorIds) {
                foreach ($sectorIds as $sectorId) {
                    $q->orWhereJsonContains('sector_ids', (int)$sectorId);
                }
            });
        }
        elseif (!empty($cityIds)) {
            $query->where(function($q) use ($cityIds) {
                foreach ($cityIds as $cityId) {
                    $q->orWhereJsonContains('city_ids', (int)$cityId);
                }
            });
        }

        // Always ensure the user can see themselves
        $query->orWhere('id', $user->id);

        return $query;
    }

    /**
     * Apply location-based filtering to frontend users (privileged users) query
     */
    public function filterFrontendUsersByLocation(Builder $query, $user): Builder
    {
        if (!$user) {
            return $query;
        }

        if ($this->hasGlobalAccess($user)) {
            return $query;
        }

        $ids = $this->getNormalizedLocationIds($user);
        $cityIds = $ids['city_ids'];
        $sectorIds = $ids['sector_ids'];

        // If no locations assigned, allow global access for Admins/Directors
        if (empty($cityIds) && empty($sectorIds)) {
            if ($this->hasGlobalAccess($user)) {
                return $query;
            }
            return $query->whereRaw('1 = 0');
        }

        if (!empty($sectorIds)) {
            $json = json_encode(array_map('intval', (array)$sectorIds));
            $query->whereRaw("JSON_CONTAINS(?, node_ids)", [$json])
                  ->whereRaw("JSON_LENGTH(node_ids) > 0");
        }
        elseif (!empty($cityIds)) {
            $json = json_encode(array_map('intval', (array)$cityIds));
            $query->whereRaw("JSON_CONTAINS(?, group_ids)", [$json])
                  ->whereRaw("JSON_LENGTH(group_ids) > 0");
        }

        return $query;
    }
    public function filterCmesByLocation(Builder $query, $user): Builder
    {
        if (!$user) {
            return $query;
        }

        if ($this->hasGlobalAccess($user)) {
            return $query;
        }

        $ids = $this->getNormalizedLocationIds($user);
        $cityIds = $ids['city_ids'];
        $sectorIds = $ids['sector_ids'];

        if (empty($cityIds) && empty($sectorIds)) {
            if ($this->hasGlobalAccess($user)) {
                return $query;
            }
            return $query->whereRaw('1 = 0');
        }

        return $query->where(function ($q) use ($cityIds, $sectorIds) {
            if (!empty($sectorIds)) {
                $q->whereHas('sectors', function ($sq) use ($sectorIds) {
                            $sq->whereIn('id', $sectorIds);
                        }
                        );
                    }

                    if (!empty($cityIds)) {
                        $method = !empty($sectorIds) ? 'orWhereHas' : 'whereHas';
                        $q->{ $method}('cities', function ($cq) use ($cityIds) {
                            $cq->whereIn('id', $cityIds);
                        }
                        );
                    }
                });
    }

    /**
     * Helper to get normalized location IDs from any user model
     */
    private function getNormalizedLocationIds($user): array
    {
        return [
            'city_ids' => $user->city_ids ?? $user->group_ids ?? [],
            'sector_ids' => $user->sector_ids ?? $user->node_ids ?? []
        ];
    }
}

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

        // 1. If user has no specific location, they see everything (Global Admin/Director)
        if ($user->city_id === null && $user->sector_id === null) {
            return $query;
        }

        // 2. Filter complaints based on the House's location
        // This is more robust than filtering on complaints table directly
        return $query->whereHas('house', function ($houseQuery) use ($user) {
            if ($user->sector_id) {
                // Restricted to a specific Sector
                $houseQuery->where('sector_id', $user->sector_id);
            } elseif ($user->city_id) {
                // Restricted to a specific City (e.g. GE)
                $houseQuery->where('city_id', $user->city_id);
            }
        });
    }

    /**
     * Apply location-based filtering to employees query
     */
    public function filterEmployeesByLocation(Builder $query, $user): Builder
    {
        if (!$user || !$user->role) {
            return $query;
        }

        if ($user->city_id === null && $user->sector_id === null) {
            return $query;
        }

        $roleName = strtolower($user->role->role_name ?? '');

        switch ($roleName) {
            case 'director':
                break;

            case 'admin':
                // Admin sees all ONLY if no specific location is assigned
                if ($user->sector_id) {
                    $query->where('sector_id', $user->sector_id);
                } elseif ($user->city_id) {
                    $query->where('city_id', $user->city_id);
                }
                break;

            case 'garrison_engineer':
                // GE can see only their city's employees
                if ($user->city_id) {
                    $query->where('city_id', $user->city_id);
                } else {
                    // If no city assigned, show nothing
                    $query->whereRaw('1 = 0');
                }
                break;

            case 'complaint_center':
            case 'department_staff':
            case 'complaint officer':
                // Can see only their sector's employees
                if ($user->sector_id) {
                    $query->where('sector_id', $user->sector_id);
                } else {
                    // If no sector assigned, show nothing
                    $query->whereRaw('1 = 0');
                }
                break;
            
            default:
                // For any other role, if they have city_id, filter by city
                if ($user->city_id) {
                    $query->where('city_id', $user->city_id);
                } else {
                    $query->whereRaw('1 = 0');
                }
                break;
        }

        return $query;
    }

    /**
     * Check if user can view all data (Director or Admin)
     */
    public function canViewAllData($user): bool
    {
        if (!$user) {
            return false;
        }

        return $user->city_id === null && $user->sector_id === null;
    }

    /**
     * Get user's accessible city IDs (null means all cities)
     */
    public function getUserCityIds($user): ?array
    {
        if (!$user || !$user->role) {
            return null;
        }

        if ($user->city_id === null && $user->sector_id === null) {
            return null; // All cities
        }

        $roleName = strtolower($user->role->role_name ?? '');

        if ($roleName === 'garrison_engineer' && $user->city_id) {
            return [$user->city_id]; // Only their city
        }

        if (in_array($roleName, ['complaint_center', 'department_staff', 'staff', 'complaint officer']) && $user->city_id) {
            return [$user->city_id]; // Their city
        }

        // Fallback: If user has a city_id, return it
        if ($user->city_id) {
            return [$user->city_id];
        }

        return [];
    }

    /**
     * Get user's accessible sector IDs (null means all sectors)
     */
    public function getUserSectorIds($user): ?array
    {
        if (!$user || !$user->role) {
            return null;
        }

        if ($user->city_id === null && $user->sector_id === null) {
            return null; // All sectors
        }

        $roleName = strtolower($user->role->role_name ?? '');

        if ($roleName === 'garrison_engineer' && $user->city_id) {
            // GE can see all sectors in their city
            return \App\Models\Sector::where('city_id', $user->city_id)
                ->pluck('id')
                ->toArray();
        }

        if (in_array($roleName, ['complaint_center', 'department_staff', 'staff', 'complaint officer']) && $user->sector_id) {
            return [$user->sector_id]; // Only their sector
        }

        // Fallback: If user has a sector_id, return it
        if ($user->sector_id) {
            return [$user->sector_id];
        }

        // If user has city_id but no sector_id, they can see all sectors in their city
        if ($user->city_id) {
            return \App\Models\Sector::where('city_id', $user->city_id)
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
        if (!$user || !$user->role) {
            // If no user or role, show nothing for safety
            $query->whereRaw('1 = 0');
            return $query;
        }

        if ($user->city_id === null && $user->sector_id === null) {
            return $query;
        }

        $roleName = strtolower($user->role->role_name ?? '');

        switch ($roleName) {
            case 'director':
                break;

            case 'admin':
                // Admin sees all ONLY if no specific location is assigned
                if ($user->sector_id) {
                    $query->where('sector_id', $user->sector_id);
                } elseif ($user->city_id) {
                    $query->where('city_id', $user->city_id);
                }
                break;

            case 'garrison_engineer':
                // GE can see only their city's spares
                if ($user->city_id) {
                    $query->where('city_id', $user->city_id);
                } else {
                    // If no city assigned, show nothing
                    $query->whereRaw('1 = 0');
                }
                break;

            case 'complaint_center':
            case 'department_staff':
            case 'complaint officer':
                // Complaint Center and Department Staff can see only their sector's spares
                if ($user->sector_id) {
                    $query->where('sector_id', $user->sector_id);
                } else {
                    // If no sector assigned, show nothing
                    $query->whereRaw('1 = 0');
                }
                break;
            
            default:
                // For any other role, if they have sector_id, filter by sector
                // Otherwise if they have city_id, filter by city
                // Otherwise show nothing
                if ($user->sector_id) {
                    $query->where('sector_id', $user->sector_id);
                } elseif ($user->city_id) {
                    $query->where('city_id', $user->city_id);
                } else {
                    // If no location assigned, show nothing
                    $query->whereRaw('1 = 0');
                }
        }
        return $query;
    }

    /**
     * Apply location-based filtering to houses query
     */
    public function filterHousesByLocation(Builder $query, $user): Builder
    {
        if (!$user || !$user->role) {
            return $query;
        }

        if ($user->city_id === null && $user->sector_id === null) {
            return $query;
        }

        $roleName = strtolower($user->role->role_name ?? '');

        switch ($roleName) {
            case 'director':
                break;

            case 'admin':
                // Admin sees all ONLY if no specific location is assigned
                if ($user->sector_id) {
                    $query->where('sector_id', $user->sector_id);
                } elseif ($user->city_id) {
                    $query->where('city_id', $user->city_id);
                }
                break;

            case 'garrison_engineer':
                // GE can see only their city's houses
                if ($user->city_id) {
                    $query->where('city_id', $user->city_id);
                } else {
                    // If no city assigned, show nothing
                    $query->whereRaw('1 = 0');
                }
                break;

            case 'complaint_center':
            case 'department_staff':
            case 'complaint officer':
                // Can see only their sector's houses
                if ($user->sector_id) {
                    $query->where('sector_id', $user->sector_id);
                } else {
                    // If no sector assigned, show nothing
                    $query->whereRaw('1 = 0');
                }
                break;
            
            default:
                // For any other role, if they have city_id, filter by city
                if ($user->city_id) {
                    $query->where('city_id', $user->city_id);
                } else {
                    $query->whereRaw('1 = 0');
                }
                break;
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
        if (!$user || !$user->role) {
            return $query;
        }

        if ($user->city_id === null && $user->sector_id === null) {
            return $query;
        }

        $roleName = strtolower($user->role->role_name ?? '');

        // Exclude higher authority roles for restricted users
        $startQuery = function($q) {
             // Exclude Director and Global Admins (those with admin role but no location are filtered by location check usually, but specific role exclusion is safer)
             // Also exclude Garrison Engineer if the viewer is just a Complaint Center/Staff
             $q->whereDoesntHave('role', function($rq) {
                 $rq->whereIn('role_name', ['director']);
             });
        };

        // If I am strictly a Sector-level user (Complaint Center/Staff), I shouldn't see GE or Admin
        $excludeGeAndAdmin = function($q) {
             $q->whereDoesntHave('role', function($rq) {
                 $rq->whereIn('role_name', ['director', 'garrison_engineer', 'admin']);
             });
        };

        switch ($roleName) {
            case 'director':
                break;

            case 'admin':
                // Restricted Admin
                if ($user->sector_id || $user->city_id) {
                    $startQuery($query);
                    // Also exclude other 'admin' users? Usually yes, to prevent modifying peers.
                    // Let's stick to the prompt: "cmes director" (likely meaning CMEs/Director/GEs). 
                    // Assuming 'admin' role is high-level too if they are global. 
                    // But if they are local admin, they appear in the list.
                    // The prompt "apny sy higher authority" (Higher authority than themselves).
                    
                    if ($user->sector_id) {
                        $query->where('sector_id', $user->sector_id);
                        // If restricted to sector, assume they are low level admin
                        $excludeGeAndAdmin($query);
                    } elseif ($user->city_id) {
                        $query->where('city_id', $user->city_id);
                        $startQuery($query); // Exclude Director
                    }
                }
                break;

            case 'garrison_engineer':
                // GE can see only their city's users
                // GE shouldn't see Director
                $startQuery($query);
                
                if ($user->city_id) {
                    $query->where('city_id', $user->city_id);
                } else {
                    $query->whereRaw('1 = 0');
                }
                break;

            case 'complaint_center':
            case 'department_staff':
            case 'staff':
                // Can see only their sector's users
                // Shouldn't see GE, Director, Admin
                $excludeGeAndAdmin($query);

                if ($user->sector_id) {
                    $query->where('sector_id', $user->sector_id);
                } else {
                    $query->whereRaw('1 = 0');
                }
                break;
            
            default:
                if ($user->sector_id) {
                    $query->where('sector_id', $user->sector_id);
                } elseif ($user->city_id) {
                    $query->where('city_id', $user->city_id);
                } else {
                    $query->whereRaw('1 = 0');
                }
                break;
        }

        // Always ensure the user can see themselves, regardless of exclusions
        if ($user) {
            $query->orWhere('id', $user->id);
        }

        return $query;
    }

    /**
     * Apply location-based filtering to frontend users (privileged users) query
     */
    public function filterFrontendUsersByLocation(Builder $query, $user): Builder
    {
        if (!$user || !$user->role) {
            return $query;
        }

        if ($user->city_id === null && $user->sector_id === null) {
            return $query;
        }

        $roleName = strtolower($user->role->role_name ?? '');
        $cityId = $user->city_id ?? null;
        $sectorId = $user->sector_id ?? null;

        // Helper closure to apply filter and exclude Super/Higher Privileged Users
        $applyFilter = function($q) use ($cityId, $sectorId) {
            // Exclude users who have CME level access (Higher Authority)
            // Assuming checking if JSON array is not empty/null for cme_ids
            $q->where(function($qq) {
                $qq->whereNull('cme_ids')
                   ->orWhere('cme_ids', '[]')
                   ->orWhere('cme_ids', ''); // Safety check
            });

            if ($sectorId) {
                // If restricted to a sector, show users who have access to this sector
                $q->where(function($subQ) use ($sectorId) {
                   $subQ->whereJsonContains('node_ids', (string)$sectorId)
                        ->orWhereJsonContains('node_ids', (int)$sectorId);
                });
            } elseif ($cityId) {
                // If restricted to a city, show users who have access to this city
                $q->where(function($subQ) use ($cityId) {
                   $subQ->whereJsonContains('group_ids', (string)$cityId)
                        ->orWhereJsonContains('group_ids', (int)$cityId);
                });
            }
        };

        switch ($roleName) {
            case 'director':
                break;

            case 'admin':
                // Admin sees all ONLY if no specific location is assigned
                if ($sectorId || $cityId) {
                    $applyFilter($query);
                }
                break;

            case 'garrison_engineer':
                if ($cityId) {
                   $query->where(function($subQ) use ($cityId) {
                       $subQ->whereJsonContains('group_ids', (string)$cityId)
                            ->orWhereJsonContains('group_ids', (int)$cityId);
                   });
                   // Exclude CME Access users? Maybe GE can see them? 
                   // User said "baqi na hn jin ko sb priviliges hain" (those with all privileges shouldn't show).
                   // Let's exclude CME level for GE too just in case.
                   $query->where(function($qq) {
                        $qq->whereNull('cme_ids')
                           ->orWhere('cme_ids', '[]');
                   });
                } else {
                   $query->whereRaw('1 = 0');
                }
                break;

            case 'complaint_center':
            case 'department_staff':
            case 'staff':
                if ($sectorId) {
                   $applyFilter($query);
                } else {
                   $query->whereRaw('1 = 0');
                }
                break;
            
            default:
                if ($sectorId || $cityId) {
                    $applyFilter($query);
                } else {
                    $query->whereRaw('1 = 0');
                }
                break;
        }

        return $query;
    }
}

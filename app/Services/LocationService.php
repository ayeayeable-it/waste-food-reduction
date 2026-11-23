<?php

namespace App\Services;

class LocationService
{
    private const EARTH_RADIUS_KM = 6371.0;

    /**
     * Calculate the great-circle distance between two coordinates using the Haversine formula.
     */
    public function calculateDistanceKm(
        float $lat1,
        float $lng1,
        float $lat2,
        float $lng2
    ): float {
        $latFrom = deg2rad($lat1);
        $lngFrom = deg2rad($lng1);
        $latTo = deg2rad($lat2);
        $lngTo = deg2rad($lng2);

        $latDelta = $latTo - $latFrom;
        $lngDelta = $lngTo - $lngFrom;

        $angle = 2 * asin(sqrt(
            pow(sin($latDelta / 2), 2) +
            cos($latFrom) * cos($latTo) * pow(sin($lngDelta / 2), 2)
        ));

        return round($angle * self::EARTH_RADIUS_KM, 2);
    }

    /**
     * Return a MySQL-compatible Haversine SQL expression (in kilometres).
     * Uses LEAST/GREATEST clamping to avoid floating-point ACOS domain errors.
     */
    public function haversineSql(string $latColumn = 'stores.latitude', string $lngColumn = 'stores.longitude'): string
    {
        return "(6371 * ACOS(LEAST(1.0, GREATEST(-1.0, "
            . "COS(RADIANS(?)) * COS(RADIANS({$latColumn})) * COS(RADIANS({$lngColumn}) - RADIANS(?)) "
            . "+ SIN(RADIANS(?)) * SIN(RADIANS({$latColumn}))"
            . "))))";
    }
}

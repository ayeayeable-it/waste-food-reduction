<?php

namespace Tests\Unit;

use App\Services\LocationService;
use PHPUnit\Framework\TestCase;

class LocationServiceTest extends TestCase
{
    public function test_haversine_returns_zero_for_identical_coordinates(): void
    {
        $service = new LocationService();

        $distance = $service->calculateDistanceKm(51.5073509, -0.1277583, 51.5073509, -0.1277583);

        $this->assertEquals(0.0, $distance);
    }

    public function test_haversine_calculates_accurate_distance_between_two_seeded_stores(): void
    {
        $service = new LocationService();

        // GreenMart Express (51.5073509, -0.1277583) to CornerFresh Mini-Market (51.5154190, -0.1410990)
        // Great-circle distance is approximately 1.29 km
        $distance = $service->calculateDistanceKm(51.5073509, -0.1277583, 51.5154190, -0.1410990);

        $this->assertGreaterThan(1.20, $distance);
        $this->assertLessThan(1.40, $distance);
    }
}

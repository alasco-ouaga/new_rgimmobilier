<?php

namespace Database\Seeders;

use Botble\Base\Supports\BaseSeeder;
use Botble\RealEstate\Models\Feature;
use Botble\RealEstate\Models\Property;

class FeatureSeeder extends BaseSeeder
{
    public function run(): void
    {
        Feature::query()->truncate();

        $features = [
            [
                'name' => 'Wifi',
            ],
            [
                'name' => 'Parking',
            ],
            [
                'name' => 'Piscine',
            ],
            [
                'name' => 'Balcon',
            ],
            [
                'name' => 'Jardin',
            ],
            [
                'name' => 'Sécurité',
            ],
            [
                'name' => 'Centre de fitness',
            ],
            [
                'name' => 'Climatisation',
            ],
            [
                'name' => 'Chauffage central',
            ],
            [
                'name' => 'Buanderie',
            ],
            [
                'name' => 'Animaux autorisés',
            ],
            [
                'name' => 'Spa & Massage',
            ],
        ];

        foreach ($features as $facility) {
            Feature::query()->create($facility);
        }

        foreach (Property::query()->get() as $property) {
            $property->features()->detach();
            $property->features()->attach([1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12]);
        }
    }
}

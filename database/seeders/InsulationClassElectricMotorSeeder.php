<?php

namespace Database\Seeders;

use App\Models\Standard;
use App\Models\StandardVariant;
use Illuminate\Database\Seeder;

class InsulationClassElectricMotorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Define all Insulation Class Electric Motor standards based on the image
        $standards = [
            // Insulation Class B
            [
                'name' => 'Insulation Class B Electric Motor',
                'class' => 'B',
                'description' => 'Maximum winding temperature for Class B insulation is 130°C, with a permissible temperature rise of 80K and a hotspot temperature margin of 10K above 40°C ambient.',
                'variants' => [
                    ['name' => 'Good', 'min' => 0.00, 'max' => 120.00, 'color' => '#22C55E', 'order' => 1], // Up to end of Permissible Temp Rise (40°C + 80K = 120°C)
                    ['name' => 'Satisfactory', 'min' => 120.01, 'max' => 130.00, 'color' => '#FACC15', 'order' => 2], // Within Hotspot Temp Margin (120°C to 130°C)
                    ['name' => 'Unsatisfactory', 'min' => 130.01, 'max' => 135.00, 'color' => '#FB923C', 'order' => 3], // Slightly above max winding temp
                    ['name' => 'Unacceptable', 'min' => 135.01, 'max' => 9999.99, 'color' => '#EF4444', 'order' => 4], // Significantly above max winding temp
                ]
            ],
            // Insulation Class F
            [
                'name' => 'Insulation Class F Electric Motor',
                'class' => 'F',
                'description' => 'Maximum winding temperature for Class F insulation is 155°C, with a permissible temperature rise of 105K and a hotspot temperature margin of 10K above 40°C ambient.',
                'variants' => [
                    ['name' => 'Good', 'min' => 0.00, 'max' => 145.00, 'color' => '#22C55E', 'order' => 1], // Up to end of Permissible Temp Rise (40°C + 105K = 145°C)
                    ['name' => 'Satisfactory', 'min' => 145.01, 'max' => 155.00, 'color' => '#FACC15', 'order' => 2], // Within Hotspot Temp Margin (145°C to 155°C)
                    ['name' => 'Unsatisfactory', 'min' => 155.01, 'max' => 160.00, 'color' => '#FB923C', 'order' => 3], // Slightly above max winding temp
                    ['name' => 'Unacceptable', 'min' => 160.01, 'max' => 9999.99, 'color' => '#EF4444', 'order' => 4], // Significantly above max winding temp
                ]
            ],
            // Insulation Class H
            [
                'name' => 'Insulation Class H Electric Motor',
                'class' => 'H',
                'description' => 'Maximum winding temperature for Class H insulation is 180°C, with a permissible temperature rise of 125K and a hotspot temperature margin of 15K above 40°C ambient.',
                'variants' => [
                    ['name' => 'Good', 'min' => 0.00, 'max' => 165.00, 'color' => '#22C55E', 'order' => 1], // Up to end of Permissible Temp Rise (40°C + 125K = 165°C)
                    ['name' => 'Satisfactory', 'min' => 165.01, 'max' => 180.00, 'color' => '#FACC15', 'order' => 2], // Within Hotspot Temp Margin (165°C to 180°C)
                    ['name' => 'Unsatisfactory', 'min' => 180.01, 'max' => 185.00, 'color' => '#FB923C', 'order' => 3], // Slightly above max winding temp
                    ['name' => 'Unacceptable', 'min' => 185.01, 'max' => 9999.99, 'color' => '#EF4444', 'order' => 4], // Significantly above max winding temp
                ]
            ],
            // Insulation Class F/B
            [
                'name' => 'Insulation Class F/B Electric Motor',
                'class' => 'F/B',
                'description' => 'Maximum winding temperature for Class F/B insulation is 155°C, with a permissible temperature rise of 80K, a safety margin of 25K, and a hotspot temperature margin of 10K above 40°C ambient. Total permissible temperature rise is 105K.',
                'variants' => [
                    ['name' => 'Good', 'min' => 0.00, 'max' => 120.00, 'color' => '#22C55E', 'order' => 1], // Up to end of 80K Permissible Temp Rise (40°C + 80K = 120°C)
                    ['name' => 'Satisfactory', 'min' => 120.01, 'max' => 145.00, 'color' => '#FACC15', 'order' => 2], // Within Safety Margin (120°C to 145°C)
                    ['name' => 'Unsatisfactory', 'min' => 145.01, 'max' => 155.00, 'color' => '#FB923C', 'order' => 3], // Within Hotspot Temp Margin (145°C to 155°C)
                    ['name' => 'Unacceptable', 'min' => 155.01, 'max' => 9999.99, 'color' => '#EF4444', 'order' => 4], // Above max winding temp
                ]
            ],
        ];

        $createdCount = 0;
        $variantCount = 0;

        foreach ($standards as $standardData) {
            // Create or update standard
            $standard = Standard::firstOrCreate(
                [
                    'reference_code' => 'Insulation Class Electric Motor',
                    'class' => $standardData['class']
                ],
                [
                    'name' => $standardData['name'],
                    'reference_type' => 'IEC/NEMA',
                    'reference_name' => 'Insulation Class Electric Motor - Standard untuk Suhu Operasi Motor Listrik',
                    'unit' => '°C',
                    'description' => $standardData['description'],
                    'keterangan' => 'Standar untuk menentukan kelas isolasi (insulation class) pada motor listrik berdasarkan suhu maksimum yang dapat ditoleransi oleh isolasi. Setiap kelas isolasi memiliki suhu maksimum yang berbeda: Class B (130°C), Class F (155°C), Class F/B (155°C), dan Class H (180°C). Suhu yang diukur harus dibandingkan dengan kelas isolasi motor untuk menentukan apakah motor beroperasi dalam batas aman atau memerlukan perhatian.',
                    'photo' => 'images/Insulation-Class Electric Motor.jpg',
                    'status' => 'active',
                ]
            );

            // Update if exists
            $updated = false;
            if ($standard->name !== $standardData['name']) {
                $standard->name = $standardData['name'];
                $updated = true;
            }
            if ($standard->description !== $standardData['description']) {
                $standard->description = $standardData['description'];
                $updated = true;
            }
            if ($standard->photo !== 'images/Insulation-Class Electric Motor.jpg') {
                $standard->photo = 'images/Insulation-Class Electric Motor.jpg';
                $updated = true;
            }
            if ($standard->status !== 'active') {
                $standard->status = 'active';
                $updated = true;
            }
            if ($updated) {
                $standard->save();
            }

            // Delete existing variants for this standard
            StandardVariant::where('standard_id', $standard->id)->delete();

            // Create variants
            foreach ($standardData['variants'] as $variantData) {
                StandardVariant::create([
                    'standard_id' => $standard->id,
                    'name' => $variantData['name'],
                    'min_value' => $variantData['min'],
                    'max_value' => $variantData['max'],
                    'color' => $variantData['color'],
                    'order' => $variantData['order'],
                ]);
                $variantCount++;
            }

            $createdCount++;
            $this->command->info("Created standard: {$standardData['name']} (ID: {$standard->id}) with " . count($standardData['variants']) . " variants");
        }

        $this->command->info("\nInsulation Class Electric Motor standards seeding completed!");
        $this->command->info("Total standards created/updated: {$createdCount}");
        $this->command->info("Total variants created: {$variantCount}");
    }
}

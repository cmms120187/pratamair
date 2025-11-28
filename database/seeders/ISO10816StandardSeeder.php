<?php

namespace Database\Seeders;

use App\Models\Standard;
use App\Models\StandardVariant;
use Illuminate\Database\Seeder;

class ISO10816StandardSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Define all ISO 10816-3 standards based on the image
        $standards = [
            // Machine Group 4 - Rigid Foundation
            [
                'name' => 'ISO 10816-3 - Machine Group 4 (Rigid)',
                'class' => 'Machine Group 4 - Rigid Foundation',
                'description' => 'Integral driver, Pumps > 15 kW Radial, axial, mixed flow - Rigid Foundation',
                'variants' => [
                    ['name' => 'New machine condition', 'min' => 0.00, 'max' => 1.4, 'color' => '#22C55E', 'order' => 1],
                    ['name' => 'Unlimited long-term operation allowable', 'min' => 1.41, 'max' => 2.8, 'color' => '#FACC15', 'order' => 2],
                    ['name' => 'Short-term operation allowable', 'min' => 2.81, 'max' => 4.5, 'color' => '#FB923C', 'order' => 3],
                    ['name' => 'Vibration causes damage', 'min' => 4.51, 'max' => 15.99, 'color' => '#EF4444', 'order' => 4],
                ]
            ],
            // Machine Group 4 - Flexible Foundation
            [
                'name' => 'ISO 10816-3 - Machine Group 4 (Flexible)',
                'class' => 'Machine Group 4 - Flexible Foundation',
                'description' => 'Integral driver, Pumps > 15 kW Radial, axial, mixed flow - Flexible Foundation',
                'variants' => [
                    ['name' => 'New machine condition', 'min' => 0.00, 'max' => 2.30, 'color' => '#22C55E', 'order' => 1],
                    ['name' => 'Unlimited long-term operation allowable', 'min' => 2.31, 'max' => 4.50, 'color' => '#FACC15', 'order' => 2],
                    ['name' => 'Short-term operation allowable', 'min' => 4.51, 'max' => 7.10, 'color' => '#FB923C', 'order' => 3],
                    ['name' => 'Vibration causes damage', 'min' => 7.11, 'max' => 15.99, 'color' => '#EF4444', 'order' => 4],
                ]
            ],
            // Machine Group 3 - Rigid Foundation
            [
                'name' => 'ISO 10816-3 - Machine Group 3 (Rigid)',
                'class' => 'Machine Group 3 - Rigid Foundation',
                'description' => 'External driver, Pumps > 15 kW Radial, axial, mixed flow - Rigid Foundation',
                'variants' => [
                    ['name' => 'New machine condition', 'min' => 0.00, 'max' => 2.30, 'color' => '#22C55E', 'order' => 1],
                    ['name' => 'Unlimited long-term operation allowable', 'min' => 2.31, 'max' => 4.50, 'color' => '#FACC15', 'order' => 2],
                    ['name' => 'Short-term operation allowable', 'min' => 4.51, 'max' => 7.10, 'color' => '#FB923C', 'order' => 3],
                    ['name' => 'Vibration causes damage', 'min' => 7.11, 'max' => 15.99, 'color' => '#EF4444', 'order' => 4],
                ]
            ],
            // Machine Group 3 - Flexible Foundation
            [
                'name' => 'ISO 10816-3 - Machine Group 3 (Flexible)',
                'class' => 'Machine Group 3 - Flexible Foundation',
                'description' => 'External driver, Pumps > 15 kW Radial, axial, mixed flow - Flexible Foundation',
                'variants' => [
                    ['name' => 'New machine condition', 'min' => 0.00, 'max' => 3.50, 'color' => '#22C55E', 'order' => 1],
                    ['name' => 'Unlimited long-term operation allowable', 'min' => 3.51, 'max' => 7.1, 'color' => '#FACC15', 'order' => 2],
                    ['name' => 'Short-term operation allowable', 'min' => 7.11, 'max' => 11.0, 'color' => '#FB923C', 'order' => 3],
                    ['name' => 'Vibration causes damage', 'min' => 11.1, 'max' => 15.99, 'color' => '#EF4444', 'order' => 4],
                ]
            ],
            // Machine Group 2 - Rigid Foundation
            [
                'name' => 'ISO 10816-3 - Machine Group 2 (Rigid)',
                'class' => 'Machine Group 2 - Rigid Foundation',
                'description' => 'Motors, 160 mm ≤ H ≤ 315 mm, Medium sized machines 15 kW < P ≤ 300 kW - Rigid Foundation',
                'variants' => [
                    ['name' => 'New machine condition', 'min' => 0.00, 'max' => 1.4, 'color' => '#22C55E', 'order' => 1],
                    ['name' => 'Unlimited long-term operation allowable', 'min' => 1.41, 'max' => 2.8, 'color' => '#FACC15', 'order' => 2],
                    ['name' => 'Short-term operation allowable', 'min' => 2.81, 'max' => 4.5, 'color' => '#FB923C', 'order' => 3],
                    ['name' => 'Vibration causes damage', 'min' => 4.51, 'max' => 15.99, 'color' => '#EF4444', 'order' => 4],
                ]
            ],
            // Machine Group 2 - Flexible Foundation
            [
                'name' => 'ISO 10816-3 - Machine Group 2 (Flexible)',
                'class' => 'Machine Group 2 - Flexible Foundation',
                'description' => 'Motors, 160 mm ≤ H ≤ 315 mm, Medium sized machines 15 kW < P ≤ 300 kW - Flexible Foundation',
                'variants' => [
                    ['name' => 'New machine condition', 'min' => 0.00, 'max' => 2.30, 'color' => '#22C55E', 'order' => 1],
                    ['name' => 'Unlimited long-term operation allowable', 'min' => 2.31, 'max' => 4.50, 'color' => '#FACC15', 'order' => 2],
                    ['name' => 'Short-term operation allowable', 'min' => 4.51, 'max' => 7.10, 'color' => '#FB923C', 'order' => 3],
                    ['name' => 'Vibration causes damage', 'min' => 7.11, 'max' => 15.99, 'color' => '#EF4444', 'order' => 4],
                ]
            ],
            // Machine Group 1 - Rigid Foundation
            [
                'name' => 'ISO 10816-3 - Machine Group 1 (Rigid)',
                'class' => 'Machine Group 1 - Rigid Foundation',
                'description' => 'Motors, 315 mm ≤ H, Large machines 300 kW < P < 50 MW - Rigid Foundation',
                'variants' => [
                    ['name' => 'New machine condition', 'min' => 0.00, 'max' => 2.30, 'color' => '#22C55E', 'order' => 1],
                    ['name' => 'Unlimited long-term operation allowable', 'min' => 2.31, 'max' => 4.50, 'color' => '#FACC15', 'order' => 2],
                    ['name' => 'Short-term operation allowable', 'min' => 4.51, 'max' => 7.10, 'color' => '#FB923C', 'order' => 3],
                    ['name' => 'Vibration causes damage', 'min' => 7.11, 'max' => 15.99, 'color' => '#EF4444', 'order' => 4],
                ]
            ],
            // Machine Group 1 - Flexible Foundation
            [
                'name' => 'ISO 10816-3 - Machine Group 1 (Flexible)',
                'class' => 'Machine Group 1 - Flexible Foundation',
                'description' => 'Motors, 315 mm ≤ H, Large machines 300 kW < P < 50 MW - Flexible Foundation',
                'variants' => [
                    ['name' => 'New machine condition', 'min' => 0.00, 'max' => 3.50, 'color' => '#22C55E', 'order' => 1],
                    ['name' => 'Unlimited long-term operation allowable', 'min' => 3.51, 'max' => 7.1, 'color' => '#FACC15', 'order' => 2],
                    ['name' => 'Short-term operation allowable', 'min' => 7.11, 'max' => 11.0, 'color' => '#FB923C', 'order' => 3],
                    ['name' => 'Vibration causes damage', 'min' => 11.1, 'max' => 15.99, 'color' => '#EF4444', 'order' => 4],
                ]
            ],
        ];

        $createdCount = 0;
        $variantCount = 0;

        foreach ($standards as $standardData) {
            // Create or update standard
            $standard = Standard::firstOrCreate(
                [
                    'reference_code' => 'ISO 10816-3',
                    'class' => $standardData['class']
                ],
                [
                    'name' => $standardData['name'],
                    'reference_type' => 'ISO',
                    'reference_name' => 'ISO 10816-3 - Mechanical vibration - Evaluation of machine vibration by measurements on non-rotating parts',
                    'unit' => 'mm/s',
                    'description' => $standardData['description'],
                    'keterangan' => 'Standar ISO 10816-3 untuk evaluasi getaran mesin berdasarkan pengukuran pada bagian non-rotating. Standar ini mengklasifikasikan tingkat getaran menjadi beberapa zone (A, B, C, D) berdasarkan nilai getaran yang diukur dalam mm/s rms.',
                    'photo' => 'images/ISO 10816.jpg',
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
            if ($standard->photo !== 'images/ISO 10816.jpg') {
                $standard->photo = 'images/ISO 10816.jpg';
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

            // Create variants and track min/max values
            $overallMin = null;
            $overallMax = null;
            foreach ($standardData['variants'] as $variantData) {
                StandardVariant::create([
                    'standard_id' => $standard->id,
                    'name' => $variantData['name'],
                    'min_value' => $variantData['min'],
                    'max_value' => $variantData['max'],
                    'color' => $variantData['color'],
                    'order' => $variantData['order'],
                ]);
                
                // Track overall min and max from all variants
                if ($overallMin === null || $variantData['min'] < $overallMin) {
                    $overallMin = $variantData['min'];
                }
                if ($overallMax === null || $variantData['max'] > $overallMax) {
                    $overallMax = $variantData['max'];
                }
                
                $variantCount++;
            }

            // Update standard with overall min and max values
            $standard->min_value = $overallMin;
            $standard->max_value = $overallMax;
            $standard->save();

            $createdCount++;
            $this->command->info("Created standard: {$standardData['name']} (ID: {$standard->id}) with " . count($standardData['variants']) . " variants (Range: {$overallMin} - {$overallMax})");
        }

        $this->command->info("\nISO 10816-3 standards seeding completed!");
        $this->command->info("Total standards created/updated: {$createdCount}");
        $this->command->info("Total variants created: {$variantCount}");
    }
}

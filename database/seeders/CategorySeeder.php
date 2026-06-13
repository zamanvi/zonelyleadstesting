<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'Beauty & Personal Care' => [
                'Aesthetic & Skin Clinics',
                'Barber Shops',
                'Bridal Makeup',
                'Hair Salons',
                'Lashes & Eyebrows',
                'Makeup Artists',
                'Nail Salons',
                'Spas & Massage Centers',
                'Tattoo Studios',
            ],
            'Healthcare & Wellness' => [
                'Chiropractors',
                'Dentists & Dental Clinics',
                'Doctors & Medical Clinics',
                'Gyms & Personal Trainers',
                'Nutritionists',
                'Physiotherapists',
                'Psychologists & Therapists',
                'Veterinary Clinics',
                'Yoga Studios',
            ],
            'Home Services & Repairs' => [
                'Appliance Repair',
                'Carpenters & Painters',
                'Cleaners & Housekeepers',
                'Electricians',
                'HVAC Technicians',
                'Locksmiths',
                'Pest Control',
                'Plumbers',
                'Roofers & Movers',
                'Tow Truck & Roadside Assistance',
            ],
            'Professional Services' => [
                'Business Consultants',
                'CPAs & Accountants',
                'Financial Advisors',
                'Immigration Consultants',
                'Lawyers / Attorneys',
                'Notary Public',
                'Real Estate Agents',
                'Tax Consultants',
                'Tutors & Coaches',
            ],
        ];

        foreach ($categories as $parentTitle => $children) {
            $parentSlug = Str::slug($parentTitle);

            $parentId = DB::table('categories')->where('slug', $parentSlug)->value('id');

            if (!$parentId) {
                $parentId = DB::table('categories')->insertGetId([
                    'title'      => $parentTitle,
                    'slug'       => $parentSlug,
                    'parent_id'  => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            foreach ($children as $childTitle) {
                $childSlug = Str::slug($childTitle);

                if (!DB::table('categories')->where('slug', $childSlug)->exists()) {
                    DB::table('categories')->insert([
                        'title'      => $childTitle,
                        'slug'       => $childSlug,
                        'parent_id'  => $parentId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }
}

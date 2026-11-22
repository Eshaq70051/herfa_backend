<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Service;
use Illuminate\Database\Seeder;

class ServiceCatalogSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'plumbing' => Category::firstOrCreate(
                ['name' => 'السباكة والتسربات'],
                [
                    'description' => 'إصلاح التسربات والانسدادات وتركيب الأدوات الصحية.',
                    'icon' => 'fa-faucet-drip',
                    'sort_order' => 1,
                    'is_active' => true,
                ],
            ),
            'air_conditioning' => Category::firstOrCreate(
                ['name' => 'التكييف والتبريد'],
                [
                    'description' => 'تنظيف وصيانة وتركيب وإصلاح أجهزة التكييف.',
                    'icon' => 'fa-snowflake',
                    'sort_order' => 2,
                    'is_active' => true,
                ],
            ),
            'electrical' => Category::firstOrCreate(
                ['name' => 'الكهرباء والإنارة'],
                [
                    'description' => 'إصلاح الأعطال والتمديدات وتركيب الإنارة.',
                    'icon' => 'fa-bolt',
                    'sort_order' => 3,
                    'is_active' => true,
                ],
            ),
            'appliances' => Category::firstOrCreate(
                ['name' => 'الأجهزة المنزلية'],
                [
                    'description' => 'تشخيص وإصلاح أعطال الأجهزة المنزلية.',
                    'icon' => 'fa-blender-phone',
                    'sort_order' => 4,
                    'is_active' => true,
                ],
            ),
            'cleaning' => Category::firstOrCreate(
                ['name' => 'نظافة وصيانة عامة'],
                [
                    'description' => 'تنظيف المنازل والمفروشات وخزانات المياه.',
                    'icon' => 'fa-broom',
                    'sort_order' => 8,
                    'is_active' => true,
                ],
            ),
            'carpentry' => Category::firstOrCreate(
                ['name' => 'النجارة والأبواب'],
                [
                    'description' => 'إصلاح وتركيب الأبواب والأثاث وأعمال النجارة المنزلية.',
                    'icon' => 'fa-hammer',
                    'sort_order' => 5,
                    'is_active' => true,
                ],
            ),
            'painting' => Category::firstOrCreate(
                ['name' => 'الدهانات والديكور'],
                [
                    'description' => 'أعمال الدهان والتجديد والديكور المنزلي.',
                    'icon' => 'fa-paint-roller',
                    'sort_order' => 6,
                    'is_active' => true,
                ],
            ),
        ];

        foreach ([
            [
                'name' => 'تنظيف المنازل والمفروشات',
                'description' => 'تنظيف المنازل والسجاد والمفروشات وخزانات المياه.',
                'icon' => 'fa-broom',
            ],
            [
                'name' => 'المسابح',
                'description' => 'تنظيف المسابح وفحص أنظمة الضخ والترشيح.',
                'icon' => 'fa-water-ladder',
            ],
            [
                'name' => 'مكافحة الحشرات',
                'description' => 'مكافحة الحشرات المنزلية ومعالجة مصادرها.',
                'icon' => 'fa-shield-virus',
            ],
            [
                'name' => 'نقل الأثاث',
                'description' => 'فك ونقل وإعادة تركيب الأثاث المنزلي.',
                'icon' => 'fa-truck-moving',
            ],
            [
                'name' => 'الترميمات والتجديد',
                'description' => 'أعمال الترميم والتجديد وتحسين المساحات المنزلية.',
                'icon' => 'fa-hammer',
            ],
            [
                'name' => 'الزجاج والألمنيوم',
                'description' => 'تركيب وإصلاح النوافذ والأبواب وأعمال الزجاج والألمنيوم.',
                'icon' => 'fa-window-maximize',
            ],
        ] as $index => $categoryData) {
            $categories[$categoryData['name']] = Category::firstOrCreate(
                ['name' => $categoryData['name']],
                $categoryData + [
                    'sort_order' => 9 + $index,
                    'is_active' => true,
                ],
            );
        }

        $services = [
            [$categories['plumbing'], 'إصلاح تسربات وانسداد الصرف', 'معاينة وإصلاح تسربات المياه وفتح انسداد المصارف.', 0, 30],
            [$categories['plumbing'], 'تركيب الأدوات الصحية', 'تركيب وإصلاح الخلاطات والأحواض والمراحيض.', 0, 30],
            [$categories['air_conditioning'], 'تركيب وصيانة المكيفات', 'معاينة وتركيب أو إصلاح أجهزة التكييف بحسب نوعها.', 0, 30],
            [$categories['air_conditioning'], 'صيانة دورية للمكيفات', 'فحص وتنظيف وحدات التكييف وتجهيزها للاستخدام الموسمي.', 0, 30],
            [$categories['electrical'], 'إصلاح وتمديدات الكهرباء', 'فحص الأعطال وتمديد أو إصلاح التوصيلات الكهربائية.', 0, 30],
            [$categories['electrical'], 'تركيب الإنارة والمفاتيح', 'تركيب أو استبدال وحدات الإنارة والمفاتيح الكهربائية.', 0, 30],
            [$categories['appliances'], 'إصلاح الأجهزة المنزلية', 'تشخيص وإصلاح أعطال الأجهزة المنزلية بحسب نوع الجهاز.', 0, 30],
            [$categories['cleaning'], 'تنظيف المنازل', 'تنظيف المنزل وفق المساحة ونطاق العمل المطلوب.', 0, 0],
            [$categories['cleaning'], 'غسيل السجاد والموكيت', 'تنظيف السجاد والموكيت بحسب العدد والحالة.', 0, 0],
            [$categories['cleaning'], 'تنظيف المفروشات والكنب', 'تنظيف الكنب والمفروشات بحسب نوع القماش والحجم.', 0, 0],
            [$categories['cleaning'], 'تنظيف خزانات المياه', 'تنظيف وتعقيم خزانات المياه المنزلية.', 0, 30],
            [$categories['المسابح'], 'تنظيف وصيانة المسابح', 'تنظيف المسبح وفحص معدات الضخ والترشيح.', 0, 30],
            [$categories['مكافحة الحشرات'], 'مكافحة الحشرات المنزلية', 'معاينة ومعالجة الحشرات المنزلية بحسب نوع الإصابة.', 0, 30],
            [$categories['نقل الأثاث'], 'فك ونقل وتركيب الأثاث', 'تحديد نطاق النقل والفك والتركيب بحسب الأثاث والموقع.', 0, 0],
            [$categories['الترميمات والتجديد'], 'ترميم وتجديد المنزل', 'معاينة الأعمال المطلوبة وتقديم عرض سعر قبل التنفيذ.', 0, 30],
            [$categories['الزجاج والألمنيوم'], 'تركيب وإصلاح الزجاج والألمنيوم', 'معاينة المقاسات ونوع الإصلاح قبل تحديد التكلفة.', 0, 30],
            [$categories['carpentry'], 'إصلاح وتركيب الأبواب والأثاث', 'إصلاح الأبواب والأقفال وأعمال النجارة المنزلية.', 0, 30],
            [$categories['painting'], 'دهان وتجديد الجدران', 'تجهيز ودهان الجدران وفق مساحة العمل وحالتها.', 0, 30],
            [$categories['cleaning'], 'صيانة منزلية دورية', 'معاينة احتياجات الصيانة الدورية للمنزل وتحديد نطاق العمل.', 0, 30],
        ];

        foreach ($services as [$category, $name, $description, $price, $warrantyDays]) {
            Service::updateOrCreate(
                ['category_id' => $category->id, 'name' => $name],
                [
                    'description' => $description,
                    'base_price' => $price,
                    'pricing_type' => 'custom_quote',
                    'warranty_days' => $warrantyDays,
                    'image' => $category->image ?? '/storage/categories/plumbing.svg',
                    'icon' => $category->icon ?? 'fa-wrench',
                    'is_active' => true,
                ],
            );
        }
    }
}

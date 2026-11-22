<?php

namespace Database\Seeders;

use App\Models\StoreService;
use Illuminate\Database\Seeder;

class StoreServiceSeeder extends Seeder
{
    public function run(): void
    {
        $stores = [
            [
                'name' => 'عاملات منزلية بالساعة',
                'company_name' => 'تراك كلين Track Clean',
                'description' => 'عاملات منزلية مدربات على أعلى معايير النظافة والترتيب بنظام الساعة لراحة منزلك، متوفرات للفترات الصباحية والمسائية مع ضمان الأمانة والاحترافية.',
                'logo' => 'https://api.dicebear.com/7.x/shapes/svg?seed=trackclean',
                'phone' => '777123451',
                'rating' => 4.8,
                'reviews_count' => 240,
                'tags' => ['عاملات منزلية بالساعة', 'الفترة الصباحية', 'الفترة المسائية'],
                'badge' => 'شريك بلاتيني',
                'is_active' => true,
                'display_order' => 1,
            ],
            [
                'name' => 'مؤسسة الهنوف للتشغيل والصيانة',
                'company_name' => 'مؤسسة الهنوف للصيانة والتشغيل',
                'description' => 'نقدم حلولاً احترافية بأحدث التقنيات وفريق متخصص يضمن الجودة وسرعة الإنجاز بأسعار تنافسية في كشف التسربات وتنظيف وعزل الخزانات ومكافحة الحشرات.',
                'logo' => 'https://api.dicebear.com/7.x/shapes/svg?seed=alhanouf',
                'phone' => '777123452',
                'rating' => 4.2,
                'reviews_count' => 16,
                'tags' => ['خدمة كشف تسربات المياه', 'تنظيف وعزل الخزانات', 'مكافحة الحشرات'],
                'badge' => 'مؤسسة معتمدة',
                'is_active' => true,
                'display_order' => 2,
            ],
            [
                'name' => 'نكسورا NexAura',
                'company_name' => 'شركة نكسورا للحلول الإنشائية',
                'description' => 'حلول إنشائية وتنفيذية متكاملة، تصميم وديكور، وإدارة وتجهيز الفعاليات والمعارض بأحدث الأساليب الهندسية.',
                'logo' => 'https://api.dicebear.com/7.x/shapes/svg?seed=nexaura',
                'phone' => '777123453',
                'rating' => 4.7,
                'reviews_count' => 2,
                'tags' => ['حلول إنشائية وتنفيذية', 'إدارة وتجهيز الفعاليات', 'ديكور وتشطيب'],
                'badge' => 'معتمد',
                'is_active' => true,
                'display_order' => 3,
            ],
            [
                'name' => 'سكراب والخردة',
                'company_name' => 'شراء السكراب والخردة المعتمد',
                'description' => 'عندك حديد قديم، أجهزة معطلة، أثاث ما تحتاجه، أو مخلفات ترميم؟ لا ترميها - بيعها لنا. نوزن أمام عينك ونشتريها فوراً وندفع كاش.',
                'logo' => 'https://api.dicebear.com/7.x/shapes/svg?seed=scrap',
                'phone' => '777123454',
                'rating' => 5.0,
                'reviews_count' => 1,
                'tags' => ['شراء حديد وألمنيوم', 'أجهزة معطلة قديمة', 'سكراب والخردة'],
                'badge' => 'دفع كاش فوري',
                'is_active' => true,
                'display_order' => 4,
            ],
            [
                'name' => 'مدار الأفق لنقل الأثاث',
                'company_name' => 'مدارات الأفق لنقل العفش',
                'description' => 'نقدم حلولاً احترافية بأحدث التقنيات وفريق متخصص يضمن الجودة وسرعة الإنجاز بأسعار تنافسية لنقل وفك وتغليف وتركيب الأثاث المنزلي والمكتبي.',
                'logo' => 'https://api.dicebear.com/7.x/shapes/svg?seed=madaf',
                'phone' => '777123455',
                'rating' => 4.9,
                'reviews_count' => 85,
                'tags' => ['نقل الأثاث والعفش', 'تغليف احترافي', 'فك وتركيب'],
                'badge' => 'ضمان سلامة الأثاث',
                'is_active' => true,
                'display_order' => 5,
            ],
        ];

        foreach ($stores as $store) {
            StoreService::updateOrCreate(
                ['company_name' => $store['company_name']],
                $store
            );
        }
    }
}

<?php

namespace Database\Seeders;

use App\Models\Advertisement;
use App\Models\Offer;
use App\Models\User;
use Illuminate\Database\Seeder;

class BannerOffersSeeder extends Seeder
{
    public function run(): void
    {
        $craftsman = User::where('role', 'craftsman')->first() ?? User::first();
        $craftsmanId = $craftsman ? $craftsman->id : 1;
        $admin = User::where('role', 'admin')->first() ?? User::first();
        $adminId = $admin ? $admin->id : 1;

        Advertisement::where('title', 'اااااا')->delete();

        $offersData = [
            [
                'title' => 'ودّع المفاتيح التقليدية.. أمان وراحة مع الأقفال الذكية',
                'craftsman_id' => $craftsmanId,
                'service_id' => 5,
                'discount_percentage' => 30.00,
                'description' => 'توريد وتركيب أحدث الأقفال الإلكترونية الذكية مع ضمان سنتين كاملتين وتطبيق هاتف',
                'image' => 'https://images.unsplash.com/photo-1558002038-1055907df827?auto=format&fit=crop&w=800&q=80',
                'start_date' => now()->subDays(5)->toDateString(),
                'end_date' => now()->addMonths(6)->toDateString(),
                'offer_status' => 'active',
            ],
            [
                'title' => 'صيانة وتكييف شاملة بضمان ذهبي 30 يوماً',
                'craftsman_id' => $craftsmanId,
                'service_id' => 3,
                'discount_percentage' => 35.00,
                'description' => 'غسيل وفحص مكيفات الإسبليت وتعبئة غاز الفريون بأحدث الأجهزة ومعالجة التسريب',
                'image' => 'https://images.unsplash.com/photo-1621905251189-08b45d6a269e?auto=format&fit=crop&w=800&q=80',
                'start_date' => now()->subDays(3)->toDateString(),
                'end_date' => now()->addMonths(6)->toDateString(),
                'offer_status' => 'active',
            ],
            [
                'title' => 'كشف تسربات المياه وفحص الشبكات بالأجهزة الحديثة',
                'craftsman_id' => $craftsmanId,
                'service_id' => 1,
                'discount_percentage' => 25.00,
                'description' => 'كشف إلكتروني دقيق بدون تكسير للأنابيب مع تقرير فني معتمد وضمان معتمد',
                'image' => 'https://images.unsplash.com/photo-1585704032915-c3400ca199e7?auto=format&fit=crop&w=800&q=80',
                'start_date' => now()->subDays(2)->toDateString(),
                'end_date' => now()->addMonths(6)->toDateString(),
                'offer_status' => 'active',
            ],
            [
                'title' => 'طوارئ الكهرباء والسباكة 24 ساعة استجابة سريعة',
                'craftsman_id' => $craftsmanId,
                'service_id' => 2,
                'discount_percentage' => 20.00,
                'description' => 'فريق صيانة متنقل يصلك خلال 20 دقيقة لمعالجة أي عطل طارئ',
                'image' => 'https://images.unsplash.com/photo-1621905252507-b35492cc74b4?auto=format&fit=crop&w=800&q=80',
                'start_date' => now()->subDays(1)->toDateString(),
                'end_date' => now()->addMonths(6)->toDateString(),
                'offer_status' => 'active',
            ],
        ];

        foreach ($offersData as $data) {
            Offer::updateOrCreate(
                ['title' => $data['title']],
                $data
            );
        }

        Advertisement::updateOrCreate(
            ['title' => 'انضم إلى شبكة فنيي وشركاء حِرفة وابدأ بزيادة دخلك اليوم!'],
            [
                'advertiser_name' => 'منصة حِرفة',
                'description' => 'سجل معنا كفني أو متجر معتمد واحصل على عملاء فوريين في منطقتك',
                'target_url' => 'https://herfa.app',
                'image' => 'https://images.unsplash.com/photo-1581092160607-ee22621dd758?auto=format&fit=crop&w=800&q=80',
                'start_date' => now()->subDays(10)->toDateString(),
                'end_date' => now()->addMonths(6)->toDateString(),
                'status' => 'active',
                'created_by' => $adminId,
            ]
        );
    }
}

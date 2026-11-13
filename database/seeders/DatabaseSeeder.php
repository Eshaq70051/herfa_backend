<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\CraftsmanProfile;
use App\Models\UserAddress;
use App\Models\Governorate;
use App\Models\District;
use App\Models\Neighborhood;
use App\Models\Category;
use App\Models\Service;
use App\Models\CraftsmanSpecialty;
use App\Models\CraftsmanService;
use App\Models\RequestStatus;
use App\Models\CancellationReason;
use App\Models\ServiceRequest;
use App\Models\RequestDetail;
use App\Models\RequestAssignment;
use App\Models\RequestStatusHistory;
use App\Models\Review;
use App\Models\Offer;
use App\Models\PaymentMethod;
use App\Models\NotificationType;
use App\Models\AdvertisementPosition;
use App\Models\AppSetting;
use App\Models\SupportTicket;
use App\Models\SupportMessage;
use App\Models\AiConversation;
use App\Models\AiMessage;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. المحافظات والمديريات والأحياء
        $gov1 = Governorate::create(['name' => 'الرياض', 'is_active' => true]);
        $gov2 = Governorate::create(['name' => 'مكة المكرمة - جدة', 'is_active' => true]);
        $gov3 = Governorate::create(['name' => 'المنطقة الشرقية', 'is_active' => true]);
        $gov4 = Governorate::create(['name' => 'صنعاء', 'is_active' => true]);

        $dist1 = District::create(['governorate_id' => $gov1->id, 'name' => 'شمال الرياض', 'is_active' => true]);
        $dist2 = District::create(['governorate_id' => $gov1->id, 'name' => 'وسط الرياض', 'is_active' => true]);
        $dist3 = District::create(['governorate_id' => $gov4->id, 'name' => 'السبعين', 'is_active' => true]);

        $neigh1 = Neighborhood::create(['district_id' => $dist1->id, 'name' => 'حي الملقا', 'is_active' => true]);
        $neigh2 = Neighborhood::create(['district_id' => $dist1->id, 'name' => 'حي النرجس', 'is_active' => true]);
        $neigh3 = Neighborhood::create(['district_id' => $dist2->id, 'name' => 'حي العليا', 'is_active' => true]);

        // 2. حالات الطلبات (Statuses)
        $stPending = RequestStatus::create(['name' => 'pending', 'description' => 'بانتظار قبول الحرفي', 'sort_order' => 1]);
        $stAssigned = RequestStatus::create(['name' => 'assigned', 'description' => 'تم إسناد الطلب للحرفي', 'sort_order' => 2]);
        $stInProgress = RequestStatus::create(['name' => 'in_progress', 'description' => 'جاري العمل والصيانة', 'sort_order' => 3]);
        $stCompleted = RequestStatus::create(['name' => 'completed', 'description' => 'مكتمل بنجاح', 'sort_order' => 4]);
        $stCancelled = RequestStatus::create(['name' => 'cancelled', 'description' => 'تم الإلغاء', 'sort_order' => 5]);

        // 3. أسباب الإلغاء
        CancellationReason::create(['reason' => 'تأخر الحرفي عن الموعد المحدد', 'cancelled_by_type' => 'customer']);
        CancellationReason::create(['reason' => 'تم إصلاح المشكلة ذاتياً', 'cancelled_by_type' => 'customer']);
        CancellationReason::create(['reason' => 'عدم الاتفاق على تكلفة قطع الغيار', 'cancelled_by_type' => 'customer']);
        CancellationReason::create(['reason' => 'تعذر التواصل مع العميل أو الوصول للموقع', 'cancelled_by_type' => 'craftsman']);
        CancellationReason::create(['reason' => 'طلب غير مطابق لشروط المنصة', 'cancelled_by_type' => 'admin']);

        // 4. وسائل الدفع
        PaymentMethod::create(['name' => 'الكريمي إكسبرس (Kuraimi)', 'code' => 'kuraimi', 'description' => 'الدفع عبر بنك الكريمي للتمويل الأصغر']);
        PaymentMethod::create(['name' => 'جوالي (Jawwali)', 'code' => 'jawwali', 'description' => 'الدفع المباشر عبر محفظة جوالي']);
        PaymentMethod::create(['name' => 'جيب (Jeeb)', 'code' => 'jeeb', 'description' => 'محفظة جيب الإلكترونية']);
        PaymentMethod::create(['name' => 'ون كاش (OneCash)', 'code' => 'onecash', 'description' => 'سداد ون كاش']);
        PaymentMethod::create(['name' => 'بطاقة مدى / فيزا', 'code' => 'card', 'description' => 'الدفع الإلكتروني عبر البطاقات البنكية']);
        PaymentMethod::create(['name' => 'نقداً عند اكتمال الخدمة (كاش)', 'code' => 'cash', 'description' => 'دفع المبلغ مباشرة للحرفي يداً بيد']);

        // 5. أنواع الإشعارات
        NotificationType::create(['name' => 'طلب جديد', 'code' => 'new_request', 'description' => 'إشعار للحرفي بطلب صيانة قريب منه']);
        NotificationType::create(['name' => 'قبول الطلب', 'code' => 'request_accepted', 'description' => 'إشعار للعميل بقبول الحرفي للطلب']);
        NotificationType::create(['name' => 'في الطريق', 'code' => 'craftsman_on_the_way', 'description' => 'الحرفي متوجه إلى موقع العميل']);
        NotificationType::create(['name' => 'اكتمال الخدمة', 'code' => 'service_completed', 'description' => 'إشعار بتقييم الخدمة ودفع الفاتورة']);

        // 6. مواقع الإعلانات
        AdvertisementPosition::create(['name' => 'شاشة البداية الرئيسية', 'code' => 'home_hero', 'description' => 'سلايدر العروض أعلى الشاشة الرئيسية']);
        AdvertisementPosition::create(['name' => 'بين أقسام الخدمات', 'code' => 'between_categories', 'description' => 'إعلانات التخفيضات المميزة']);

        // 7. إعدادات النظام (App Settings)
        AppSetting::create(['setting_key' => 'app_name', 'setting_value' => 'حِرفة | HERFA', 'setting_group' => 'general', 'description' => 'اسم المنصة']);
        AppSetting::create(['setting_key' => 'support_phone', 'setting_value' => '0500000000', 'setting_group' => 'general', 'description' => 'رقم الدعم الفني المباشر']);
        AppSetting::create(['setting_key' => 'platform_commission_percent', 'setting_value' => '15', 'setting_group' => 'financial', 'description' => 'نسبة عمولة المنصة %']);
        AppSetting::create(['setting_key' => 'emergency_fee', 'setting_value' => '25.00', 'setting_group' => 'financial', 'description' => 'رسوم طلب خدمة الطوارئ']);
        AppSetting::create(['setting_key' => 'currency_symbol', 'setting_value' => 'ر.س', 'setting_group' => 'general', 'description' => 'رمز العملة الافتراضية']);

        // 8. المستخدمون (المشرف، العملاء، الحرفيون)
        $admin = User::create([
            'full_name' => 'مدير منصة حِرفة',
            'phone' => '0500000000',
            'email' => 'admin@herfa.com',
            'password' => 'password123',
            'role' => 'admin',
            'account_status' => 'active',
            'phone_verified_at' => now(),
            'wallet_balance' => 0.00,
        ]);

        $customer = User::create([
            'full_name' => 'عبدالله بن فهد المنصور',
            'phone' => '0555555555',
            'email' => 'client@herfa.com',
            'password' => 'password123',
            'role' => 'customer',
            'account_status' => 'active',
            'current_mode' => 'customer',
            'phone_verified_at' => now(),
            'wallet_balance' => 200.00,
        ]);

        UserAddress::create([
            'user_id' => $customer->id,
            'governorate_id' => $gov1->id,
            'district_id' => $dist1->id,
            'neighborhood' => 'حي الملقا',
            'address_details' => 'شارع أنس بن مالك، فيلا رقم 42',
            'latitude' => 24.8115,
            'longitude' => 46.6189,
            'label' => 'المنزل',
            'is_default' => true,
        ]);

        // الحرفي الأول: سباك وكهربائي متميز
        $craftsman1 = User::create([
            'full_name' => 'أحمد المهندس المحترف',
            'phone' => '0566666666',
            'email' => 'craftsman1@herfa.com',
            'password' => 'password123',
            'role' => 'craftsman',
            'account_status' => 'active',
            'current_mode' => 'craftsman',
            'is_online' => true,
            'rating' => 4.95,
            'total_reviews' => 64,
            'phone_verified_at' => now(),
            'wallet_balance' => 850.00,
        ]);

        CraftsmanProfile::create([
            'user_id' => $craftsman1->id,
            'profession' => 'فني تكييف وسباكة معتمد',
            'experience_years' => 8,
            'bio' => 'فني معتمد ومتخصص في كشف تسربات المياه وصيانة أنظمة التكييف المركزي والإسبليت بجودة عالية وضمان شامل.',
            'city' => 'الرياض',
            'address' => 'حي الصحافة',
            'latitude' => 24.8000,
            'longitude' => 46.6200,
            'identity_image' => 'craftsmen/id1.jpg',
            'identity_status' => 'verified',
            'skill_status' => 'approved',
            'availability_status' => true,
            'approval_status' => 'approved',
            'commission_rate' => 15.00,
        ]);

        // الحرفي الثاني: فني كهرباء
        $craftsman2 = User::create([
            'full_name' => 'سالم باحميد',
            'phone' => '0577777777',
            'email' => 'craftsman2@herfa.com',
            'password' => 'password123',
            'role' => 'craftsman',
            'account_status' => 'active',
            'current_mode' => 'craftsman',
            'is_online' => true,
            'rating' => 4.80,
            'total_reviews' => 38,
            'phone_verified_at' => now(),
            'wallet_balance' => 420.00,
        ]);

        CraftsmanProfile::create([
            'user_id' => $craftsman2->id,
            'profession' => 'فني كهرباء ومنازل ذكية',
            'experience_years' => 5,
            'bio' => 'متخصص في فحص وتمديد شبكات الكهرباء، معالجة التماسات، وتركيب الإنارة الحديثة.',
            'city' => 'الرياض',
            'address' => 'حي الياسمين',
            'latitude' => 24.8200,
            'longitude' => 46.6400,
            'identity_image' => 'craftsmen/id2.jpg',
            'identity_status' => 'verified',
            'skill_status' => 'approved',
            'availability_status' => true,
            'approval_status' => 'approved',
            'commission_rate' => 15.00,
        ]);

        // 9. تخصصات الحرفيين
        $spec1 = CraftsmanSpecialty::create(['name' => 'تمديدات صحية وسباكة']);
        $spec2 = CraftsmanSpecialty::create(['name' => 'صيانة تكييف وتبريد']);
        $spec3 = CraftsmanSpecialty::create(['name' => 'تمديدات كهربائية عامة']);
        $spec4 = CraftsmanSpecialty::create(['name' => 'صيانة أجهزة منزلية']);

        // 10. تصنيفات الخدمات (Categories)
        $catPlumbing = Category::create([
            'name' => 'السباكة والتسربات',
            'description' => 'كشف التسربات، تركيب وإصلاح الأدوات الصحية ومضخات المياه والخزانات.',
            'icon' => 'fa-faucet-drip',
            'image' => 'categories/plumbing.png',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $catAC = Category::create([
            'name' => 'التكييف والتبريد',
            'description' => 'تنظيف دوري، شحن فريون، فحص كفاءة التبريد وإصلاح الأعطال الميكانيكية.',
            'icon' => 'fa-snowflake',
            'image' => 'categories/ac.png',
            'sort_order' => 2,
            'is_active' => true,
        ]);

        $catElectricity = Category::create([
            'name' => 'الكهرباء والإنارة',
            'description' => 'صيانة القواطع الكهربائية، كشف التماس الكهرباء، وتركيب المفاتيح والإنارات.',
            'icon' => 'fa-bolt',
            'image' => 'categories/electricity.png',
            'sort_order' => 3,
            'is_active' => true,
        ]);

        $catAppliances = Category::create([
            'name' => 'الأجهزة المنزلية',
            'description' => 'صيانة الغسالات الأوتوماتيكية، الثلاجات، الأفران والشاشات.',
            'icon' => 'fa-blender-phone',
            'image' => 'categories/appliances.png',
            'sort_order' => 4,
            'is_active' => true,
        ]);

        $catCarpentry = Category::create([
            'name' => 'النجارة والأبواب',
            'description' => 'إصلاح الأقفال والأبواب، صيانة المطابخ، وفك وتركيب غرف النوم.',
            'icon' => 'fa-hammer',
            'image' => 'categories/carpentry.png',
            'sort_order' => 5,
            'is_active' => true,
        ]);

        $catPaint = Category::create([
            'name' => 'الدهانات والديكور',
            'description' => 'معالجة الرطوبة والتشققات، دهان الجدران، وتركيب بديل الخشب والرخام.',
            'icon' => 'fa-paint-roller',
            'image' => 'categories/paint.png',
            'sort_order' => 6,
            'is_active' => true,
        ]);

        $catInsulation = Category::create([
            'name' => 'عزل الأسطح والخزانات',
            'description' => 'عزل مائي وحراري للأسطح والخزانات بأفضل المواد المعتمدة.',
            'icon' => 'fa-shield-halved',
            'image' => 'categories/insulation.png',
            'sort_order' => 7,
            'is_active' => true,
        ]);

        $catCleaning = Category::create([
            'name' => 'نظافة وصيانة عامة',
            'description' => 'تنظيف خزانات المياه، مكافحة الحشرات، وجلي وتلميع الأرضيات.',
            'icon' => 'fa-broom',
            'image' => 'categories/cleaning.png',
            'sort_order' => 8,
            'is_active' => true,
        ]);

        // 11. الخدمات التفصيلية (Services)
        $s1 = Service::create([
            'category_id' => $catPlumbing->id,
            'name' => 'كشف تسربات المياه وفحص الشبكة',
            'description' => 'فحص بأحدث الأجهزة الإلكترونية لتحديد مكان التسريب بدقة مع تقرير فني معتمد.',
            'base_price' => 75.00,
            'warranty_days' => 30,
            'is_active' => true,
        ]);

        $s2 = Service::create([
            'category_id' => $catPlumbing->id,
            'name' => 'تركيب أو صيانة السخانات والخلاطات',
            'description' => 'إصلاح تسريب المياه من السخان، تغيير قلب الخلاط، وتركيب مضخات الضغط.',
            'base_price' => 50.00,
            'warranty_days' => 30,
            'is_active' => true,
        ]);

        $s3 = Service::create([
            'category_id' => $catAC->id,
            'name' => 'غسيل وتنظيف مكيف سبليت عميق',
            'description' => 'تنظيف الوحدة الداخلية والخارجية بغطاء واقي، مع تسليك مجرى الصرف وفحص الفريون.',
            'base_price' => 65.00,
            'warranty_days' => 30,
            'is_active' => true,
        ]);

        $s4 = Service::create([
            'category_id' => $catAC->id,
            'name' => 'شحن فريون ومعالجة تسريب المكيف',
            'description' => 'لحام مواسير النحاس واختبار الضغط وشحن فريون أصلي أمريكي.',
            'base_price' => 120.00,
            'warranty_days' => 45,
            'is_active' => true,
        ]);

        $s5 = Service::create([
            'category_id' => $catElectricity->id,
            'name' => 'إصلاح التماس كهربائي وانقطاع القواطع',
            'description' => 'فحص القواطع الرئيسية والفرعية ومعالجة التماس الأسلاك لضمان السلامة.',
            'base_price' => 60.00,
            'warranty_days' => 30,
            'is_active' => true,
        ]);

        $s6 = Service::create([
            'category_id' => $catAppliances->id,
            'name' => 'صيانة غسالات أوتوماتيك وثلاجات',
            'description' => 'تشخيص الأعطال، استبدال القطع التالفة، فحص طرمبة التصريف والكمبروسر.',
            'base_price' => 70.00,
            'pricing_type' => 'inspection',
            'warranty_days' => 30,
            'is_active' => true,
        ]);

        // ربط الحرفيين بالخدمات (craftsman_services)
        CraftsmanService::create(['craftsman_id' => $craftsman1->id, 'service_id' => $s1->id]);
        CraftsmanService::create(['craftsman_id' => $craftsman1->id, 'service_id' => $s2->id]);
        CraftsmanService::create(['craftsman_id' => $craftsman1->id, 'service_id' => $s3->id]);
        CraftsmanService::create(['craftsman_id' => $craftsman1->id, 'service_id' => $s4->id]);
        CraftsmanService::create(['craftsman_id' => $craftsman2->id, 'service_id' => $s5->id]);

        // 12. طلبات الخدمة التجريبية (service_requests)
        $req1 = ServiceRequest::create([
            'request_number' => 'HRF-' . date('Ymd') . '-001',
            'customer_id' => $customer->id,
            'service_id' => $s3->id,
            'description' => 'المكيف في الصالة الرئيسية يخرج هواء حار مع صوت غير طبيعي، ويحتاج تنظيف وشحن فريون.',
            'address_details' => 'حي الملقا، شارع أنس بن مالك، فيلا 42',
            'latitude' => 24.8115,
            'longitude' => 46.6189,
            'preferred_date' => now()->toDateString(),
            'preferred_time' => '14:30:00',
            'status_id' => $stInProgress->id,
        ]);

        RequestDetail::create([
            'request_id' => $req1->id,
            'detail_name' => 'نوع المكيف',
            'detail_value' => 'مكيف سبليت 24 وحدة - إل جي',
        ]);

        RequestAssignment::create([
            'request_id' => $req1->id,
            'craftsman_id' => $craftsman1->id,
            'assignment_status' => 'accepted',
            'assigned_at' => now()->subMinutes(25),
            'responded_at' => now()->subMinutes(20),
        ]);

        RequestStatusHistory::create([
            'request_id' => $req1->id,
            'old_status_id' => $stPending->id,
            'new_status_id' => $stInProgress->id,
            'changed_by' => $craftsman1->id,
            'notes' => 'تم قبول الطلب وجاري التوجه للموقع',
        ]);

        // طلب مكتمل سابق مع تقييم
        $req2 = ServiceRequest::create([
            'request_number' => 'HRF-' . date('Ymd') . '-002',
            'customer_id' => $customer->id,
            'service_id' => $s1->id,
            'description' => 'تسريب مياه تحت حوض المطبخ يحتاج تغيير صمام وسيليكون.',
            'address_details' => 'حي الملقا، الرياض',
            'latitude' => 24.8115,
            'longitude' => 46.6189,
            'preferred_date' => now()->subDays(2)->toDateString(),
            'status_id' => $stCompleted->id,
        ]);

        RequestAssignment::create([
            'request_id' => $req2->id,
            'craftsman_id' => $craftsman1->id,
            'assignment_status' => 'completed',
            'assigned_at' => now()->subDays(2),
            'responded_at' => now()->subDays(2),
        ]);

        Review::create([
            'request_id' => $req2->id,
            'customer_id' => $customer->id,
            'craftsman_id' => $craftsman1->id,
            'rating' => 5,
            'comment' => 'شغل ممتاز جداً وأخلاق عالية، التزم بالموعد وحل المشكلة في دقائق وضمان 30 يوم.',
            'review_status' => 'visible',
        ]);

        // 13. عروض الحرفيين (Offers)
        Offer::create([
            'craftsman_id' => $craftsman1->id,
            'service_id' => $s3->id,
            'title' => 'خصم 25% على غسيل وصيانة مكيفات الإسبليت',
            'description' => 'عرض خاص لغسيل 3 مكيفات وأكثر يشمل فحص تسريب الفريون مجاناً.',
            'discount_percentage' => 25.00,
            'start_date' => now()->toDateString(),
            'end_date' => now()->addDays(20)->toDateString(),
            'offer_status' => 'active',
        ]);

        // 14. تذاكر الدعم الفني
        $ticket = SupportTicket::create([
            'ticket_number' => 'TCK-2026-001',
            'user_id' => $customer->id,
            'subject' => 'استفسار عن تغطية الضمان لقطع الغيار',
            'description' => 'هل الضمان الممنوح لمدة 30 يوم يشمل القطع المشتراة من خارج المنصة؟',
            'ticket_status' => 'open',
            'priority' => 'medium',
        ]);

        SupportMessage::create([
            'ticket_id' => $ticket->id,
            'sender_id' => $customer->id,
            'message' => 'السلام عليكم، أرجو توضيح آلية الاستفادة من الضمان في حال تعطل القطعة المستبدلة خلال الشهر.',
        ]);

        // 15. محادثة المساعد الذكي
        $aiConvo = AiConversation::create([
            'user_id' => $customer->id,
            'title' => 'تشخيص عطل تسريب ماء المكيف الداخلي',
            'conversation_status' => 'active',
        ]);

        AiMessage::create([
            'conversation_id' => $aiConvo->id,
            'sender_type' => 'user',
            'message_content' => 'المكيف عندي يقطر ماء داخل الغرفة، ما هو السبب وكيف اختار الخدمة الصحيحة؟',
        ]);

        AiMessage::create([
            'conversation_id' => $aiConvo->id,
            'sender_type' => 'assistant',
            'message_content' => 'مرحباً بك! تقطير الماء من الوحدة الداخلية سببه الشائع هو انسداد مجرى خرطوم التصريف بالأتربة، أو اتساخ حوض المكيف. ننصحك بحجز خدمة "غسيل وتنظيف مكيف سبليت عميق" وسيقوم الفني بتسليك المجرى وتنظيف الحوض بالكامل مع ضمان 30 يوماً.',
        ]);

        $this->call(ServiceCatalogSeeder::class);
    }
}

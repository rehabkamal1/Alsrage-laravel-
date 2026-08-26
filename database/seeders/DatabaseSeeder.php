<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Setting;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@alsrage.com'],
            [
                'name' => 'Admin Alsrage',
                'password' => 'admin123',
                'phone' => '0500000000',
                'role' => 'admin',
            ]
        );

        $orderStatuses = [
            ['key' => 'pending', 'label' => 'قيد الانتظار', 'color' => '#ffc107', 'sort_order' => 1],
            ['key' => 'processing', 'label' => 'قيد التنفيذ', 'color' => '#17a2b8', 'sort_order' => 2],
            ['key' => 'completed', 'label' => 'مكتمل', 'color' => '#28a745', 'sort_order' => 3],
            ['key' => 'canceled', 'label' => 'ملغي', 'color' => '#dc3545', 'sort_order' => 4],
        ];

        foreach ($orderStatuses as $status) {
            Setting::updateOrCreate(
                ['group' => 'order_status', 'key' => $status['key']],
                [
                    'label' => $status['label'],
                    'color' => $status['color'],
                    'sort_order' => $status['sort_order'],
                    'is_active' => true,
                ]
            );
        }

        // 1. Seed Employees
        $emp1 = \App\Models\Employee::firstOrCreate(
            ['phone' => '0511111111'],
            ['name' => 'أحمد العتيبي', 'username' => 'ahmed', 'password' => 'password123', 'position' => 'موظف متابعة']
        );
        $emp2 = \App\Models\Employee::firstOrCreate(
            ['phone' => '0522222222'],
            ['name' => 'سارة الشمري', 'username' => 'sara', 'password' => 'password123', 'position' => 'مسؤولة خدمة عملاء']
        );
        $emp3 = \App\Models\Employee::firstOrCreate(
            ['phone' => '0533333331'],
            ['name' => 'محمد الغامدي', 'username' => 'mohamed', 'password' => 'password123', 'position' => 'مسوق وتطوير أعمال']
        );
        $emp4 = \App\Models\Employee::firstOrCreate(
            ['phone' => '0544444441'],
            ['name' => 'نورة المطيري', 'username' => 'noura', 'password' => 'password123', 'position' => 'متابعة وتنسيق']
        );

        // 2. Seed Saudi Offices
        $officeSa1 = \App\Models\SaudiOffice::firstOrCreate(
            ['phone' => '0533333333'],
            ['name' => 'مكتب الرياض الرئيسي', 'city' => 'الرياض', 'address' => 'طريق الملك فهد']
        );
        $officeSa2 = \App\Models\SaudiOffice::firstOrCreate(
            ['phone' => '0544444444'],
            ['name' => 'مكتب جدة الإقليمي', 'city' => 'جدة', 'address' => 'شارع التحلية']
        );
        $officeSa3 = \App\Models\SaudiOffice::firstOrCreate(
            ['phone' => '0555555551'],
            ['name' => 'مكتب الدمام والشرقية', 'city' => 'الدمام', 'address' => 'حي الشاطئ']
        );

        // 3. Seed External Offices
        $officeExt1 = \App\Models\ExternalOffice::firstOrCreate(
            ['name' => 'Manila Manpower Agency'],
            ['country' => 'الفلبين', 'phone' => '+63912345678']
        );
        $officeExt2 = \App\Models\ExternalOffice::firstOrCreate(
            ['name' => 'Jakarta Recruitment Center'],
            ['country' => 'إندونيسيا', 'phone' => '+62812345678']
        );
        $officeExt3 = \App\Models\ExternalOffice::firstOrCreate(
            ['name' => 'Dhaka Manpower Express'],
            ['country' => 'بنغلاديش', 'phone' => '+88017123456']
        );
        $officeExt4 = \App\Models\ExternalOffice::firstOrCreate(
            ['name' => 'Addis Ababa Service Center'],
            ['country' => 'إثيوبيا', 'phone' => '+25191123456']
        );

        // 4. Seed Clients
        $client1 = \App\Models\Client::firstOrCreate(
            ['phone' => '0555555555'],
            ['name' => 'عبدالله بن سلمان', 'client_type' => 'فردي', 'city' => 'الرياض', 'address' => 'حي النخيل', 'employee_id' => $emp1->id]
        );
        $client2 = \App\Models\Client::firstOrCreate(
            ['phone' => '0566666666'],
            ['name' => 'شركة الأفق للاستقدام', 'client_type' => 'مكتب خدمات', 'city' => 'جدة', 'address' => 'حي الزهراء', 'employee_id' => $emp2->id]
        );
        $client3 = \App\Models\Client::firstOrCreate(
            ['phone' => '0577777777'],
            ['name' => 'فهد القحطاني', 'client_type' => 'فردي', 'city' => 'الرياض', 'address' => 'حي الصحافة', 'employee_id' => $emp3->id]
        );
        $client4 = \App\Models\Client::firstOrCreate(
            ['phone' => '0588888888'],
            ['name' => 'مريم العتيبي', 'client_type' => 'فردي', 'city' => 'الدمام', 'address' => 'حي الفيصلية', 'employee_id' => $emp4->id]
        );
        $client5 = \App\Models\Client::firstOrCreate(
            ['phone' => '0599999999'],
            ['name' => 'سلطان السبيعي', 'client_type' => 'فردي', 'city' => 'الرياض', 'address' => 'حي الياسمين', 'employee_id' => $emp1->id]
        );

        // 5. Seed Orders (10 Rich Orders)
        $now = \Carbon\Carbon::now();

        // 1. Completed Order (18,500 SAR - 45 Days - Manila)
        $o1 = \App\Models\Order::firstOrCreate(
            ['visa_number' => 'V-2026-001'],
            [
                'client_id' => $client1->id,
                'employee_id' => $emp1->id,
                'saudi_office_id' => $officeSa1->id,
                'external_office_id' => $officeExt1->id,
                'visa_holder_name' => 'عبدالله بن سلمان',
                'service_type' => 'عاملة منزلية',
                'status' => 'completed',
                'total_price' => 18500,
                'musaned_paid' => 18500,
                'price_difference' => 0,
                'created_at' => $now->copy()->subDays(60),
                'updated_at' => $now->copy()->subDays(15),
            ]
        );

        // 2. Completed Order (14,000 SAR - 30 Days - Jakarta)
        $o2 = \App\Models\Order::firstOrCreate(
            ['visa_number' => 'V-2026-002'],
            [
                'client_id' => $client2->id,
                'employee_id' => $emp2->id,
                'saudi_office_id' => $officeSa2->id,
                'external_office_id' => $officeExt2->id,
                'visa_holder_name' => 'شركة الأفق للاستقدام',
                'service_type' => 'سائق خاص',
                'status' => 'completed',
                'total_price' => 14000,
                'musaned_paid' => 14000,
                'price_difference' => 0,
                'created_at' => $now->copy()->subDays(40),
                'updated_at' => $now->copy()->subDays(10),
            ]
        );

        // 3. Completed Order (21,000 SAR - 50 Days - Manila)
        $o3 = \App\Models\Order::firstOrCreate(
            ['visa_number' => 'V-2026-003'],
            [
                'client_id' => $client3->id,
                'employee_id' => $emp3->id,
                'saudi_office_id' => $officeSa1->id,
                'external_office_id' => $officeExt1->id,
                'visa_holder_name' => 'فهد القحطاني',
                'service_type' => 'ممرضة منزلية',
                'status' => 'completed',
                'total_price' => 21000,
                'musaned_paid' => 21000,
                'price_difference' => 0,
                'created_at' => $now->copy()->subDays(70),
                'updated_at' => $now->copy()->subDays(20),
            ]
        );

        // 4. Delayed Processing Order (Created 80 days ago - SLA Exceeded)
        $o4 = \App\Models\Order::firstOrCreate(
            ['visa_number' => 'V-2026-004'],
            [
                'client_id' => $client4->id,
                'employee_id' => $emp4->id,
                'saudi_office_id' => $officeSa3->id,
                'external_office_id' => $officeExt3->id,
                'visa_holder_name' => 'مريم العتيبي',
                'service_type' => 'عاملة منزلية',
                'status' => 'processing',
                'total_price' => 12000,
                'musaned_paid' => 6000,
                'price_difference' => 6000,
                'created_at' => $now->copy()->subDays(80),
                'updated_at' => $now->copy()->subDays(80),
            ]
        );

        // 5. Delayed Processing Order (Created 90 days ago - SLA Exceeded)
        $o5 = \App\Models\Order::firstOrCreate(
            ['visa_number' => 'V-2026-005'],
            [
                'client_id' => $client5->id,
                'employee_id' => $emp1->id,
                'saudi_office_id' => $officeSa1->id,
                'external_office_id' => $officeExt4->id,
                'visa_holder_name' => 'سلطان السبيعي',
                'service_type' => 'طباخ منزلي',
                'status' => 'processing',
                'total_price' => 15000,
                'musaned_paid' => 7500,
                'price_difference' => 7500,
                'created_at' => $now->copy()->subDays(90),
                'updated_at' => $now->copy()->subDays(90),
            ]
        );

        // 6. Normal Processing Order (Created 20 days ago - Within SLA)
        $o6 = \App\Models\Order::firstOrCreate(
            ['visa_number' => 'V-2026-006'],
            [
                'client_id' => $client1->id,
                'employee_id' => $emp2->id,
                'saudi_office_id' => $officeSa2->id,
                'external_office_id' => $officeExt2->id,
                'visa_holder_name' => 'عبدالله بن سلمان',
                'service_type' => 'سائق خاص',
                'status' => 'processing',
                'total_price' => 14500,
                'musaned_paid' => 14500,
                'price_difference' => 0,
                'created_at' => $now->copy()->subDays(20),
                'updated_at' => $now->copy()->subDays(20),
            ]
        );

        // 7. Pending Order (Created 5 days ago)
        $o7 = \App\Models\Order::firstOrCreate(
            ['visa_number' => 'V-2026-007'],
            [
                'client_id' => $client3->id,
                'employee_id' => $emp3->id,
                'saudi_office_id' => $officeSa1->id,
                'external_office_id' => $officeExt1->id,
                'visa_holder_name' => 'فهد القحطاني',
                'service_type' => 'عاملة منزلية',
                'status' => 'pending',
                'total_price' => 18000,
                'musaned_paid' => 5000,
                'price_difference' => 13000,
                'created_at' => $now->copy()->subDays(5),
                'updated_at' => $now->copy()->subDays(5),
            ]
        );

        // Add Order Tracking entries for active follow-up reports
        \App\Models\OrderTracking::firstOrCreate(
            ['order_id' => $o4->id],
            ['last_action_date' => $now->copy()->subDays(15), 'notes' => 'في انتظار السفارة']
        );
        \App\Models\OrderTracking::firstOrCreate(
            ['order_id' => $o6->id],
            ['last_action_date' => $now->copy()->subDays(2), 'notes' => 'تم الفحص الطبي بنجاح']
        );
    }
}

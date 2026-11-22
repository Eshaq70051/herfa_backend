<?php

namespace Tests\Feature;

use App\Models\CraftsmanProfile;
use App\Models\User;
use Tests\TestCase;

class CraftsmanKycTest extends TestCase
{
    public function test_craftsman_kyc_updates_profile_and_documents(): void
    {
        $user = User::first();
        if (! $user) {
            $this->markTestSkipped('No user found in database.');
        }

        $response = $this->actingAs($user)->postJson('/api/auth/craftsman/kyc', [
            'full_name' => 'فني تكييف معتمد',
            'age' => 31,
            'national_id' => '02029988771',
            'years_of_experience' => 6,
            'alternative_phone' => '773344556',
            'address' => 'صنعاء - شارع الستين',
            'latitude' => 15.3522,
            'longitude' => 44.1834,
            'custom_specialty' => 'تكييف وتبريد مركزي',
            'specialties' => ['التكييف والتبريد'],
            'bio' => 'فني خبير معتمد',
            'id_document' => 'kyc/front_test.jpg',
            'national_id_back' => 'kyc/back_test.jpg',
            'cv_document' => 'cvs/resume_test.pdf',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status' => true,
        ]);

        $profile = CraftsmanProfile::where('user_id', $user->id)->first();
        $this->assertNotNull($profile);
        $this->assertSame(31, (int) $profile->age);
        $this->assertSame('02029988771', $profile->national_id_number);
        $this->assertSame('773344556', $profile->alternative_phone);
        $this->assertSame('صنعاء - شارع الستين', $profile->address);
        $this->assertSame('تكييف وتبريد مركزي', $profile->custom_specialty);
        $this->assertSame('kyc/front_test.jpg', $profile->identity_image);
        $this->assertSame('kyc/back_test.jpg', $profile->national_id_back);
        $this->assertSame('cvs/resume_test.pdf', $profile->cv_document);
        $this->assertSame('client', $user->fresh()->current_mode);

        // التحقق من التحويل بين وضع العميل ووضع الحرفي
        $switchRes = $this->actingAs($user)->postJson('/api/auth/switch-mode', ['mode' => 'craftsman']);
        $switchRes->assertStatus(200);
        $this->assertSame('craftsman', $user->fresh()->current_mode);

        $switchRes2 = $this->actingAs($user)->postJson('/api/auth/switch-mode', ['mode' => 'client']);
        $switchRes2->assertStatus(200);
        $this->assertSame('client', $user->fresh()->current_mode);
    }
}

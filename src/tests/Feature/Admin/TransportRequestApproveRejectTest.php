<?php

namespace Tests\Feature\Admin;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use App\Models\Admin;
use App\Models\Student;
use App\Models\Facility;
use App\Models\TransportRequest;
use App\Enums\TransportRequestStatus;
use App\Notifications\TransportRequestApproved;
use App\Notifications\TransportRequestRejected;

class TransportRequestApproveRejectTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\AdminSeeder::class);
    }

    /** @test */
    public function admin_can_approve_and_notify(): void
    {
        Notification::fake();

        $admin    = Admin::first();
        $student  = Student::factory()->create();
        $facility = Facility::factory()->create();

        $tr = TransportRequest::create([
            'student_id'        => $student->id,
            'facility_id'       => $facility->id,
            'from_station_name' => '大宮(埼玉県)',
            'to_station_name'   => '新宿',
            'travel_date'       => now()->toDateString(),
            'search_url'        => 'https://roote.ekispert.net/result?...',
            'route_memo'        => '快速で行く。乗換1回。',
            'status'            => TransportRequestStatus::Pending->value, // 文字列なら 'pending'
        ]);

        $this->actingAs($admin, 'admin')
             ->patch(route('admin.tr.approve', $tr), ['note' => 'OK'])
             ->assertRedirect();

        $this->assertDatabaseHas('transport_requests', [
            'id'     => $tr->id,
            'status' => TransportRequestStatus::Approved->value, // 文字列なら 'approved'
        ]);

        Notification::assertSentTo([$student], TransportRequestApproved::class);
    }

    /** @test */
    public function admin_can_reject_and_notify(): void
    {
        Notification::fake();

        $admin    = Admin::first();
        $student  = Student::factory()->create();
        $facility = Facility::factory()->create();

        $tr = TransportRequest::create([
            'student_id'        => $student->id,
            'facility_id'       => $facility->id,
            'from_station_name' => '大宮(埼玉県)',
            'to_station_name'   => '新宿',
            'travel_date'       => now()->toDateString(),
            'search_url'        => 'https://roote.ekispert.net/result?...',
            'route_memo'        => '快速で行く。乗換1回。',
            'status'            => TransportRequestStatus::Pending->value,
        ]);

        $this->actingAs($admin, 'admin')
             ->patch(route('admin.tr.reject', $tr), ['admin_note' => 'NG'])
             ->assertRedirect();

        $this->assertDatabaseHas('transport_requests', [
            'id'     => $tr->id,
            'status' => TransportRequestStatus::Rejected->value, // 文字列なら 'rejected'
        ]);

        Notification::assertSentTo([$student], TransportRequestRejected::class);
    }
}


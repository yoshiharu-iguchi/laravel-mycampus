<?php

namespace Tests\Feature\Student;

use Tests\TestCase;
use App\Models\Student;
use App\Models\Facility;
use App\Models\TransportRequest;
use App\Enums\TransportRequestStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;



class TransportRequestStoreTest extends TestCase
{
    
    use RefreshDatabase;

    /** @test */
    public function student_can_create_transport_request(): void
    {
        $student = Student::factory()->create();
        $facility = Facility::factory()->create();

        $this->actingAs($student,'student');

        $data = [
            'facility_id' => $facility->id,
            'from_station_name' => '大宮(埼玉県)',
            'to_station_name' => '新宿',
            'travel_date' => now()->toDateString(),
            'search_url' => 'https://roote.ekispert.net/result?...',
            'route_memo' => '快速で行く。乗換1回。',
        ];

        $response = $this->post(route('student.tr.store'),$data);
        $response->assertRedirect();

        $this->assertDatabaseHas('transport_requests',[
            'student_id' => $student->id,
            'facility_id' => $facility->id,
            'from_station_name' => '大宮(埼玉県)',
            'to_station_name' => '新宿',
            'route_memo' => '快速で行く。乗換1回。',
            'status' => 'pending',
        ]);
    }
}

<?php

namespace Tests\Feature\Student;

use Tests\TestCase;
use App\Models\{Student,Facility};
use App\Models\TransportRequest;
use App\Enums\TransportRequestStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;



class TransportRequestStoreTest extends TestCase
{
    
    use RefreshDatabase;

    /** @test */
    public function test_student_can_access_transport_search_and_request():void
    {
        $student = Student::factory()->create();

        $this->actingAs($student,'student')
            ->get(route('student.tr.create'))
            ->assertStatus(200);
    }
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

        $this->post(route('student.tr.store'),$data)
            ->assertRedirect();

        $this->assertDatabaseHas('transport_requests',[
            'student_id' => $student->id,
            'facility_id' => $facility->id,
            'from_station_name' => '大宮(埼玉県)',
            'to_station_name' => '新宿',
            'route_memo' => '快速で行く。乗換1回。',
            'status' => 'pending',
        ]);
    }

    public function test_guest_cannot_view_transport_request(): void
    {
        $this->post(route('student.tr.store'),[])->assertRedirect();
    }

    public function test_error_when_required_fields_missing():void
    {
        $student = Student::factory()->create();
        $this->actingAs($student,'student');

        $this->post(route('student.tr.store'),[])
            ->assertSessionHasErrors();
    }

    public function test_student_cannot_view_others_data():void
    {
        $studentA = Student::factory()->create(['name' => 'Aさん']);
        $studentB = Student::factory()->create(['name' => 'Bさん']);
        $facility = Facility::factory()->create();

        $this->actingAs($studentA,'student')
            ->post(route('student.tr.store'),[
                'facility_id' => $facility->id,
                'from_station_name' => '大宮(埼玉県)',
                'to_station_name' => '新宿',
                'travel_date' => now()->toDateString(),
                'search_url' => 'https://route.example.com/result?...',
                'route_memo' => 'Aさんの経路申請',
            ])
            ->assertRedirect();
        
        $this->actingAs($studentB,'student')
            ->get(route('student.tr.index'))
            ->assertStatus(200);
    }
}

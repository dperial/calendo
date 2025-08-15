<?php 

final class UpdateTest extends FeatureTestCase {
  public function testUpdateAppointmentSuccess(): void {
    $base = base_url();

    // Seed FK targets
    $userId = $this->ensureTestUserId();
    $catId  = $this->ensureTestCategoryId();

    // Create one via API so the record definitely exists in calendo_test
    $createPayload = [
      'user_id'     => $userId,
      'title'       => 'Seed for update',
      'description' => 'Created by test',
      'category_id' => $catId,
      'status'      => 'scheduled',
      'type'        => 'private',
      'start_date'  => (new DateTime('+1 day'))->format('Y-m-d'),
      'end_date'    => (new DateTime('+1 day'))->format('Y-m-d'),
      'start_time'  => '10:00',
      'end_time'    => '11:00',
    ];

    [$cCode, $cBody, $cErr] = http_post_json("$base/backend/appointments/create_appointment.php", $createPayload, ['X-Test-Env: 1']);
    $this->assertSame(200, $cCode, "Create HTTP $cCode ($cErr): $cBody");
    $cJson = json_decode($cBody, true);
    $this->assertTrue($cJson['success'] ?? false, "Create failed: $cBody");
    $this->assertNotEmpty($cJson['id'] ?? null, "Create didn’t return an id");
    $id = (int)$cJson['id'];

    // Now call update endpoint with that id
    $updatePayload = [
      'id'          => $id,
      'user_id'     => $userId,     // keep user the same
      'title'       => 'Updated title',
      'description' => 'Updated by test',
      'category_id' => $catId,
      'status'      => 'scheduled',
      'type'        => 'private',
      'start_date'  => (new DateTime('+1 day'))->format('Y-m-d'),
      'end_date'    => (new DateTime('+1 day'))->format('Y-m-d'),
      'start_time'  => '12:00',
      'end_time'    => '13:00',
    ];

    [$uCode, $uBody, $uErr] = http_post_json("$base/backend/appointments/update_appointment.php", $updatePayload, ['X-Test-Env: 1']);
    $this->assertSame(200, $uCode, "Update HTTP $uCode ($uErr): $uBody");
    $uJson = json_decode($uBody, true);
    $this->assertTrue(($uJson['success'] ?? false), "Update failed: $uBody");
  }
}

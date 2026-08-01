<?php

namespace Tests\Feature\Audit;

use App\Models\AuditEvent;
use App\Models\User;
use App\Services\AuditTrail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditTrailTest extends TestCase
{
    use RefreshDatabase;

    public function test_audit_events_form_an_append_only_hash_chain(): void
    {
        $user = User::factory()->create();
        $service = app(AuditTrail::class);

        $first = $service->record(
            action: 'patient.search',
            resourceType: 'patient',
            resourceId: null,
            result: 'success',
            user: $user,
            metadata: ['query_length' => 6],
        );
        $second = $service->record(
            action: 'patient.view',
            resourceType: 'patient',
            resourceId: fake()->uuid(),
            result: 'success',
            user: $user,
        );

        $this->assertNull($first->previous_hash);
        $this->assertSame($first->integrity_hash, $second->previous_hash);
        $this->assertSame(64, strlen($first->integrity_hash));
        $this->assertSame(2, AuditEvent::query()->count());
    }

    public function test_audit_event_cannot_be_updated_or_deleted_through_model(): void
    {
        $event = app(AuditTrail::class)->record(
            action: 'session.login',
            resourceType: 'user',
            resourceId: null,
            result: 'success',
        );

        $this->expectException(\LogicException::class);
        $event->update(['reason' => 'diubah']);
    }
}

<?php

namespace App\Queries;

use App\Models\Patient;
use App\Models\Visit;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class PatientVisitHistory
{
    /** @return LengthAwarePaginator<int, Visit> */
    public function paginate(Patient $patient, int $perPage = 10): LengthAwarePaginator
    {
        return Visit::query()
            ->select([
                'id',
                'patient_id',
                'registration_id',
                'visit_date',
                'status',
                'arrived_at',
                'completed_at',
            ])
            ->whereBelongsTo($patient)
            ->with([
                'registration:id,patient_id,provider_schedule_id,requested_service,booking_code',
                'registration.appointment:id,registration_id,appointment_date,slot_start,slot_end,status',
                'registration.schedule:id,provider_user_id,service_type',
                'registration.schedule.provider:id,name',
            ])
            ->latest('visit_date')
            ->paginate($perPage);
    }
}

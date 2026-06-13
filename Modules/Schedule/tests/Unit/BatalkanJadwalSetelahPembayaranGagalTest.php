<?php

use App\Events\Finance\PembayaranDibatalkan;
use App\Events\Jadwal\JadwalBatal;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Modules\Schedule\Enums\ScheduleStatus;
use Modules\Schedule\Enums\ScheduleType;
use Modules\Schedule\Listeners\BatalkanJadwalSetelahPembayaranGagal;
use Modules\Schedule\Models\Schedule;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

function buatEventDibatalkan(int $scheduleId, ?string $paymentStatus): PembayaranDibatalkan
{
    return new PembayaranDibatalkan(
        paymentId: 1,
        invoiceId: 1,
        scheduleId: $scheduleId,
        tenantName: 'Budi',
        tenantPhone: '08123',
        amount: 750000.0,
        paymentStatus: $paymentStatus,
    );
}

test('[BERHASIL] jadwal pending dibatalkan ketika Midtrans expire (status failed)', function () {
    Event::fake([JadwalBatal::class]);

    $schedule = Schedule::create([
        'room_id' => 1,
        'type' => ScheduleType::SEWA->value,
        'status' => ScheduleStatus::PENDING->value,
        'start_date' => '2026-06-01',
        'end_date' => '2026-07-01',
    ]);

    $listener = app(BatalkanJadwalSetelahPembayaranGagal::class);
    $listener->handle(buatEventDibatalkan($schedule->id, 'failed'));

    expect($schedule->fresh()->status)->toBe(ScheduleStatus::CANCELLED);
    Event::assertDispatched(JadwalBatal::class);
});

test('[BERHASIL] jadwal aktif dibatalkan ketika refund Midtrans (status refunded)', function () {
    Event::fake([JadwalBatal::class]);

    $schedule = Schedule::create([
        'room_id' => 1,
        'type' => ScheduleType::SEWA->value,
        'status' => ScheduleStatus::ACTIVE->value,
        'start_date' => '2026-06-01',
        'end_date' => '2026-07-01',
        'activated_at' => now(),
    ]);

    $listener = app(BatalkanJadwalSetelahPembayaranGagal::class);
    $listener->handle(buatEventDibatalkan($schedule->id, 'refunded'));

    expect($schedule->fresh()->status)->toBe(ScheduleStatus::CANCELLED);
    Event::assertDispatched(JadwalBatal::class);
});

test('[BERHASIL] jadwal tidak dibatalkan ketika admin tolak pembayaran manual (status rejected)', function () {
    Event::fake([JadwalBatal::class]);

    $schedule = Schedule::create([
        'room_id' => 1,
        'type' => ScheduleType::SEWA->value,
        'status' => ScheduleStatus::PENDING->value,
        'start_date' => '2026-06-01',
        'end_date' => '2026-07-01',
    ]);

    $listener = app(BatalkanJadwalSetelahPembayaranGagal::class);
    $listener->handle(buatEventDibatalkan($schedule->id, 'rejected'));

    expect($schedule->fresh()->status)->toBe(ScheduleStatus::PENDING);
    Event::assertNotDispatched(JadwalBatal::class);
});

test('[BERHASIL] listener diabaikan jika paymentStatus null', function () {
    Event::fake([JadwalBatal::class]);

    $listener = app(BatalkanJadwalSetelahPembayaranGagal::class);
    $listener->handle(buatEventDibatalkan(1, null));

    Event::assertNotDispatched(JadwalBatal::class);
});

test('[BERHASIL] listener diabaikan jika scheduleId adalah 0', function () {
    Event::fake([JadwalBatal::class]);

    $listener = app(BatalkanJadwalSetelahPembayaranGagal::class);
    $listener->handle(buatEventDibatalkan(0, 'failed'));

    Event::assertNotDispatched(JadwalBatal::class);
});

test('[BERHASIL] listener diabaikan jika jadwal sudah cancelled', function () {
    Event::fake([JadwalBatal::class]);

    $schedule = Schedule::create([
        'room_id' => 1,
        'type' => ScheduleType::SEWA->value,
        'status' => ScheduleStatus::CANCELLED->value,
        'start_date' => '2026-06-01',
        'end_date' => '2026-07-01',
        'finished_at' => now(),
    ]);

    $listener = app(BatalkanJadwalSetelahPembayaranGagal::class);
    $listener->handle(buatEventDibatalkan($schedule->id, 'failed'));

    Event::assertNotDispatched(JadwalBatal::class);
});

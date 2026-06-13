<?php

use App\Events\Jadwal\JadwalBatal;
use App\Events\Jadwal\JadwalSewaAktif;
use App\Events\Jadwal\JadwalSewaSelesai;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Auth\Listeners\TingkatkanKeRoleResident;
use Modules\Auth\Listeners\TurunkanKeRoleMember;
use Modules\Auth\Models\User;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    Role::firstOrCreate(['name' => 'member',   'guard_name' => 'api']);
    Role::firstOrCreate(['name' => 'resident', 'guard_name' => 'api']);
});

// ── TingkatkanKeRoleResident ──────────────────────────────────────────

test('[BERHASIL] JadwalSewaAktif mengubah role member menjadi resident', function () {
    $user = User::factory()->create();
    $user->assignRole('member');

    $event = new JadwalSewaAktif(
        scheduleId: 1,
        roomId: 1,
        roomNumber: '101',
        tenantName: 'User1',
        tenantPhone: '08123456789',
        startDate: '2026-06-01',
        userId: $user->id,
    );

    (new TingkatkanKeRoleResident())->handle($event);

    expect($user->fresh()->hasRole('resident'))->toBeTrue();
    expect($user->fresh()->hasRole('member'))->toBeFalse();
});

test('[BERHASIL] TingkatkanKeRoleResident tidak error jika userId null', function () {
    $event = new JadwalSewaAktif(
        scheduleId: 1,
        roomId: 1,
        roomNumber: '101',
        tenantName: 'Tamu',
        tenantPhone: '',
        startDate: '2026-06-01',
        userId: null,
    );

    expect(fn () => (new TingkatkanKeRoleResident())->handle($event))->not->toThrow(\Throwable::class);
});

test('[BERHASIL] TingkatkanKeRoleResident idempotent jika sudah resident', function () {
    $user = User::factory()->create();
    $user->assignRole('resident');

    $event = new JadwalSewaAktif(
        scheduleId: 1,
        roomId: 1,
        roomNumber: '101',
        tenantName: 'User1',
        tenantPhone: '',
        startDate: '2026-06-01',
        userId: $user->id,
    );

    (new TingkatkanKeRoleResident())->handle($event);

    expect($user->fresh()->hasRole('resident'))->toBeTrue();
});

// ── TurunkanKeRoleMember ──────────────────────────────────────────────

test('[BERHASIL] JadwalSewaSelesai mengubah role resident kembali ke member', function () {
    $user = User::factory()->create();
    $user->assignRole('resident');

    $event = new JadwalSewaSelesai(
        scheduleId: 1,
        roomId: 1,
        roomNumber: '101',
        tenantName: 'User1',
        tenantPhone: '',
        endDate: '2026-07-01',
        userId: $user->id,
    );

    (new TurunkanKeRoleMember())->handle($event);

    expect($user->fresh()->hasRole('member'))->toBeTrue();
    expect($user->fresh()->hasRole('resident'))->toBeFalse();
});

test('[BERHASIL] JadwalBatal tipe sewa mengubah role resident ke member', function () {
    $user = User::factory()->create();
    $user->assignRole('resident');

    $event = new JadwalBatal(
        scheduleId: 1,
        roomId: 1,
        roomNumber: '101',
        tipeJadwal: 'sewa',
        userId: $user->id,
    );

    (new TurunkanKeRoleMember())->handle($event);

    expect($user->fresh()->hasRole('member'))->toBeTrue();
    expect($user->fresh()->hasRole('resident'))->toBeFalse();
});

test('[BERHASIL] JadwalBatal bukan sewa tidak mengubah role', function () {
    $user = User::factory()->create();
    $user->assignRole('resident');

    $event = new JadwalBatal(
        scheduleId: 1,
        roomId: 1,
        roomNumber: '101',
        tipeJadwal: 'kebersihan',
        userId: $user->id,
    );

    (new TurunkanKeRoleMember())->handle($event);

    expect($user->fresh()->hasRole('resident'))->toBeTrue();
});

test('[BERHASIL] TurunkanKeRoleMember tidak error jika userId null', function () {
    $event = new JadwalSewaSelesai(
        scheduleId: 1,
        roomId: 1,
        roomNumber: '101',
        tenantName: '',
        tenantPhone: '',
        endDate: '2026-07-01',
        userId: null,
    );

    expect(fn () => (new TurunkanKeRoleMember())->handle($event))->not->toThrow(\Throwable::class);
});

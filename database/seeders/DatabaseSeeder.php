<?php

namespace Database\Seeders;

use App\Models\AttendanceRecord;
use App\Models\OvertimeEntry;
use App\Models\TimesheetEntry;
use App\Models\CompanySettings;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(RoleTypeUserSeeder::class);

        $adminTimestamp = now()->format('Y-m-d H:i:s');
        $testTimestamp = now()->addSecond()->format('Y-m-d H:i:s');

        $admin = User::query()->updateOrCreate(
            ['email' => 'admin@purplecrayola.com'],
            [
                'name' => 'Purple HR Admin',
                'join_date' => $adminTimestamp,
                'last_login' => $adminTimestamp,
                'phone_number' => '+2348000000000',
                'status' => 'Active',
                'role_name' => 'Super Admin',
                'avatar' => null,
                'position' => 'HR Lead',
                'department' => 'People Operations',
                'password' => Hash::make('Password123!'),
            ]
        );

        $testUser = User::query()->updateOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'join_date' => $testTimestamp,
                'last_login' => $testTimestamp,
                'phone_number' => '+2348111111111',
                'status' => 'Active',
                'role_name' => 'Admin',
                'avatar' => null,
                'position' => 'Manager',
                'department' => 'Operations',
                'password' => Hash::make('password'),
            ]
        );

        $settings = CompanySettings::current();
        $settings->fill(array_merge(CompanySettings::defaults(), [
            'company_name' => 'Purple Crayola',
            'contact_person' => 'Purple HR Admin',
            'address' => 'Lagos, Nigeria',
            'country' => 'Nigeria',
            'city' => 'Lagos',
            'state_province' => 'Lagos',
            'postal_code' => '100001',
            'email' => 'hello@purplecrayola.com',
            'phone_number' => '+2348000000000',
            'mobile_number' => '+2348000000000',
            'fax' => 'N/A',
            'website_url' => 'https://purplecrayola.com',
            'login_image_path' => 'assets/images/purplecrayola heros login.jpg',
        ]));
        $settings->save();

        $this->seedEmployeeDirectoryProfile($admin, '1989-04-18', 'Male', 'People Operations');
        $this->seedEmployeeDirectoryProfile($testUser, '1992-08-09', 'Female', 'Purple HR Admin');
        $this->seedProfileInformation($admin, '1989-04-18', 'Male', '15 Allen Avenue', 'Nigeria', 'Lagos', '100001');
        $this->seedProfileInformation($testUser, '1992-08-09', 'Female', '22 Admiralty Way', 'Nigeria', 'Lagos', '101241');
        $this->seedLeaves($admin, $testUser);
        $this->seedAttendance($admin, $testUser);
        $this->seedTimesheets($admin, $testUser);
        $this->seedOvertime($admin, $testUser);
        $this->seedSalaries($admin, $testUser);
    }

    private function seedEmployeeDirectoryProfile(User $user, string $birthDate, string $gender, string $company): void
    {
        DB::table('employees')->updateOrInsert(
            ['employee_id' => $user->user_id],
            [
                'name' => $user->name,
                'email' => $user->email,
                'birth_date' => $birthDate,
                'gender' => $gender,
                'company' => $company,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        $permissionLists = DB::table('permission_lists')->get();
        DB::table('module_permissions')->where('employee_id', $user->user_id)->delete();

        foreach ($permissionLists as $permission) {
            DB::table('module_permissions')->insert([
                'employee_id' => $user->user_id,
                'module_permission' => $permission->permission_name,
                'id_count' => $permission->id,
                'read' => $permission->read,
                'write' => $permission->write,
                'create' => $permission->create,
                'delete' => $permission->delete,
                'import' => $permission->import,
                'export' => $permission->export,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    private function seedProfileInformation(User $user, string $birthDate, string $gender, string $address, string $country, string $state, string $pinCode): void
    {
        DB::table('profile_information')->updateOrInsert(
            ['user_id' => $user->user_id],
            [
                'birth_date' => $birthDate,
                'gender' => $gender,
                'address' => $address,
                'country' => $country,
                'state' => $state,
                'pin_code' => $pinCode,
                'phone_number' => $user->phone_number,
                'department' => $user->department,
                'designation' => $user->position,
                'reports_to' => $user->name,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }

    private function seedLeaves(User $admin, User $testUser): void
    {
        DB::table('leaves_admins')->delete();

        DB::table('leaves_admins')->insert([
            [
                'user_id' => $admin->user_id,
                'leave_type' => 'Casual Leave 12 Days',
                'from_date' => '2026-03-17',
                'to_date' => '2026-03-18',
                'day' => '2',
                'leave_reason' => 'Quarterly planning retreat',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => $testUser->user_id,
                'leave_type' => 'Medical Leave',
                'from_date' => '2026-03-12',
                'to_date' => '2026-03-12',
                'day' => '1',
                'leave_reason' => 'Clinic appointment',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    private function seedAttendance(User $admin, User $testUser): void
    {
        AttendanceRecord::query()->delete();

        $records = [
            ['user_id' => $admin->user_id, 'attendance_date' => '2026-03-02', 'status' => 'Present', 'check_in' => '08:55:00', 'check_out' => '17:40:00', 'work_minutes' => 510, 'break_minutes' => 45, 'overtime_minutes' => 15, 'notes' => 'Payroll close prep'],
            ['user_id' => $admin->user_id, 'attendance_date' => '2026-03-03', 'status' => 'Late', 'check_in' => '09:25:00', 'check_out' => '18:05:00', 'work_minutes' => 495, 'break_minutes' => 40, 'overtime_minutes' => 25, 'notes' => 'Leadership hiring review'],
            ['user_id' => $admin->user_id, 'attendance_date' => '2026-03-04', 'status' => 'Present', 'check_in' => '08:48:00', 'check_out' => '17:30:00', 'work_minutes' => 507, 'break_minutes' => 45, 'overtime_minutes' => 0, 'notes' => 'Policy updates'],
            ['user_id' => $admin->user_id, 'attendance_date' => '2026-03-05', 'status' => 'Remote', 'check_in' => '08:58:00', 'check_out' => '17:52:00', 'work_minutes' => 514, 'break_minutes' => 35, 'overtime_minutes' => 20, 'notes' => 'Remote admin day'],
            ['user_id' => $admin->user_id, 'attendance_date' => '2026-03-06', 'status' => 'Present', 'check_in' => '08:50:00', 'check_out' => '17:45:00', 'work_minutes' => 520, 'break_minutes' => 40, 'overtime_minutes' => 10, 'notes' => 'Interview loop'],
            ['user_id' => $testUser->user_id, 'attendance_date' => '2026-03-02', 'status' => 'Present', 'check_in' => '09:02:00', 'check_out' => '17:22:00', 'work_minutes' => 485, 'break_minutes' => 35, 'overtime_minutes' => 0, 'notes' => 'Operations planning'],
            ['user_id' => $testUser->user_id, 'attendance_date' => '2026-03-03', 'status' => 'Present', 'check_in' => '08:57:00', 'check_out' => '17:36:00', 'work_minutes' => 506, 'break_minutes' => 40, 'overtime_minutes' => 12, 'notes' => 'Vendor review'],
            ['user_id' => $testUser->user_id, 'attendance_date' => '2026-03-04', 'status' => 'Absent', 'check_in' => null, 'check_out' => null, 'work_minutes' => 0, 'break_minutes' => 0, 'overtime_minutes' => 0, 'notes' => 'Approved personal day'],
            ['user_id' => $testUser->user_id, 'attendance_date' => '2026-03-05', 'status' => 'Remote', 'check_in' => '09:10:00', 'check_out' => '17:18:00', 'work_minutes' => 468, 'break_minutes' => 30, 'overtime_minutes' => 0, 'notes' => 'Remote supplier check-ins'],
            ['user_id' => $testUser->user_id, 'attendance_date' => '2026-03-06', 'status' => 'Late', 'check_in' => '09:31:00', 'check_out' => '18:02:00', 'work_minutes' => 491, 'break_minutes' => 35, 'overtime_minutes' => 18, 'notes' => 'Warehouse handover'],
        ];

        foreach ($records as $record) {
            AttendanceRecord::query()->create($record);
        }
    }


    private function seedTimesheets(User $admin, User $testUser): void
    {
        TimesheetEntry::query()->delete();

        $entries = [
            ['user_id' => $admin->user_id, 'work_date' => '2026-03-03', 'project_name' => 'Payroll Close', 'assigned_hours' => 8, 'worked_hours' => 8, 'description' => 'Closed payroll review and reconciled deductions.'],
            ['user_id' => $admin->user_id, 'work_date' => '2026-03-04', 'project_name' => 'Policy Refresh', 'assigned_hours' => 6, 'worked_hours' => 5, 'description' => 'Updated leave and onboarding policy content.'],
            ['user_id' => $testUser->user_id, 'work_date' => '2026-03-03', 'project_name' => 'Vendor Coordination', 'assigned_hours' => 8, 'worked_hours' => 7, 'description' => 'Coordinated office support vendor requests.'],
            ['user_id' => $testUser->user_id, 'work_date' => '2026-03-05', 'project_name' => 'Operations Review', 'assigned_hours' => 7, 'worked_hours' => 6, 'description' => 'Reviewed weekly operations backlog and handoffs.'],
        ];

        foreach ($entries as $entry) {
            TimesheetEntry::query()->create($entry);
        }
    }

    private function seedOvertime(User $admin, User $testUser): void
    {
        OvertimeEntry::query()->delete();

        $entries = [
            ['user_id' => $admin->user_id, 'ot_date' => '2026-03-03', 'hours' => 1.50, 'ot_type' => 'Payroll Run Support', 'status' => 'Approved', 'approved_by' => 'CEO', 'description' => 'Supported final payroll checks after business hours.'],
            ['user_id' => $admin->user_id, 'ot_date' => '2026-03-06', 'hours' => 1.00, 'ot_type' => 'Interview Debrief', 'status' => 'Pending', 'approved_by' => null, 'description' => 'Stayed back to complete hiring debrief notes.'],
            ['user_id' => $testUser->user_id, 'ot_date' => '2026-03-05', 'hours' => 2.00, 'ot_type' => 'Warehouse Handover', 'status' => 'Approved', 'approved_by' => 'Purple HR Admin', 'description' => 'Extended shift for vendor handover and signoff.'],
        ];

        foreach ($entries as $entry) {
            OvertimeEntry::query()->create($entry);
        }
    }

    private function seedSalaries(User $admin, User $testUser): void
    {
        DB::table('staff_salaries')->delete();

        DB::table('staff_salaries')->insert([
            [
                'name' => $admin->name,
                'user_id' => $admin->user_id,
                'salary' => '9500',
                'basic' => '5000',
                'da' => '1000',
                'hra' => '1200',
                'conveyance' => '450',
                'allowance' => '700',
                'medical_allowance' => '300',
                'tds' => '250',
                'esi' => '100',
                'pf' => '200',
                'leave' => '0',
                'prof_tax' => '150',
                'labour_welfare' => '100',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => $testUser->name,
                'user_id' => $testUser->user_id,
                'salary' => '6200',
                'basic' => '3400',
                'da' => '650',
                'hra' => '750',
                'conveyance' => '250',
                'allowance' => '400',
                'medical_allowance' => '200',
                'tds' => '150',
                'esi' => '75',
                'pf' => '125',
                'leave' => '1',
                'prof_tax' => '100',
                'labour_welfare' => '50',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}

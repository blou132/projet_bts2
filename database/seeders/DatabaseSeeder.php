<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $demoUser = User::query()->updateOrCreate([
            'email' => 'user@gmail.com',
        ], [
            'name' => 'Utilisateur Demo',
            'password' => Hash::make('123456789'),
        ]);

        $adminSystemEmail = (string) env('ADMIN_SYSTEM_EMAIL', 'admin-system@example.test');
        User::query()->updateOrCreate([
            'email' => $adminSystemEmail,
        ], [
            'name' => 'Admin JMI 56',
            'password' => Hash::make(Str::random(32)),
        ]);

        $jmiSystemEmail = (string) env('JMI_SYSTEM_EMAIL', 'support-system@example.test');
        $jmiDisplayName = (string) env('JMI_DISPLAY_NAME', 'Support Demo');
        $jmiSystemUser = User::query()->updateOrCreate([
            'email' => $jmiSystemEmail,
        ], [
            'name' => $jmiDisplayName,
            'password' => Hash::make(Str::random(32)),
        ]);

        $now = now();

        $contactRequestId = DB::table('contact_requests')->insertGetId([
            'name' => 'Utilisateur Demo',
            'phone' => '06 12 34 56 78',
            'message' => 'Bonjour, je souhaite un diagnostic de mon PC portable.',
            'status' => 'pending',
            'user_id' => $demoUser->id,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('contact_requests')->insert([
            'name' => 'Prospect Externe',
            'phone' => '02 97 00 11 22',
            'message' => 'Demande de devis pour installation poste bureautique.',
            'status' => 'in_progress',
            'user_id' => null,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('messages')->insert([
            [
                'sender_id' => $demoUser->id,
                'receiver_id' => $jmiSystemUser->id,
                'contact_request_id' => $contactRequestId,
                'message' => 'Bonjour, je peux passer quand pour le depot ?',
                'status' => 'read',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'sender_id' => $jmiSystemUser->id,
                'receiver_id' => $demoUser->id,
                'contact_request_id' => $contactRequestId,
                'message' => 'Bonjour, vous pouvez passer demain entre 9h30 et 12h.',
                'status' => 'unread',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }
}

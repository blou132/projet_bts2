<?php

namespace Database\Seeders;

use App\Models\ContactRequest;
use App\Models\Message;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $usersByEmail = $this->seedDemoUsers();
        $this->seedSystemUsers();
        $supportUser = $this->resolveSupportUser();
        $this->seedContactRequestsAndThreads($usersByEmail, $supportUser);
    }

    /**
     * @return array<string, User>
     */
    private function seedDemoUsers(): array
    {
        $demoPassword = Hash::make('123456789');

        $demoUsers = [
            ['name' => 'Utilisateur Demo', 'email' => 'user@gmail.com'],
            ['name' => 'Alice Martin', 'email' => 'alice.client@example.test'],
            ['name' => 'Leo Bernard', 'email' => 'leo.client@example.test'],
            ['name' => 'Nina Petit', 'email' => 'nina.client@example.test'],
            ['name' => 'Tom Dubois', 'email' => 'tom.client@example.test'],
        ];

        $usersByEmail = [];
        foreach ($demoUsers as $row) {
            $user = User::query()->updateOrCreate(
                ['email' => $row['email']],
                [
                    'name' => $row['name'],
                    'password' => $demoPassword,
                ]
            );
            $usersByEmail[$row['email']] = $user;
        }

        return $usersByEmail;
    }

    private function seedSystemUsers(): void
    {
        $adminSystemEmail = (string) env('ADMIN_SYSTEM_EMAIL', 'admin-system@example.test');
        User::query()->updateOrCreate(
            ['email' => $adminSystemEmail],
            [
                'name' => 'Admin Interne',
                'password' => Hash::make(Str::random(32)),
            ]
        );

        $jmiSystemEmail = (string) env('JMI_SYSTEM_EMAIL', 'support-system@example.test');
        $jmiDisplayName = (string) env('JMI_DISPLAY_NAME', 'Support Demo');
        User::query()->updateOrCreate(
            ['email' => $jmiSystemEmail],
            [
                'name' => $jmiDisplayName,
                'password' => Hash::make(Str::random(32)),
            ]
        );
    }

    private function resolveSupportUser(): User
    {
        $jmiSystemEmail = (string) env('JMI_SYSTEM_EMAIL', 'support-system@example.test');
        $jmiDisplayName = (string) env('JMI_DISPLAY_NAME', 'Support Demo');

        return User::query()->firstOrCreate(
            ['email' => $jmiSystemEmail],
            [
                'name' => $jmiDisplayName,
                'password' => Hash::make(Str::random(32)),
            ]
        );
    }

    /**
     * @param array<string, User> $usersByEmail
     */
    private function seedContactRequestsAndThreads(array $usersByEmail, User $supportUser): void
    {
        $requests = [
            [
                'name' => 'Utilisateur Demo',
                'phone' => '06 12 34 56 78',
                'message' => 'Bonjour, je souhaite un diagnostic de mon PC portable.',
                'status' => 'pending',
                'email' => 'user@gmail.com',
            ],
            [
                'name' => 'Alice Martin',
                'phone' => '06 21 22 23 24',
                'message' => 'Besoin d aide pour installer internet a domicile.',
                'status' => 'pending',
                'email' => 'alice.client@example.test',
            ],
            [
                'name' => 'Prospect Externe',
                'phone' => '01 45 67 89 10',
                'message' => 'Demande de devis pour remplacement de SSD.',
                'status' => 'pending',
                'email' => null,
            ],
            [
                'name' => 'Leo Bernard',
                'phone' => '07 31 41 51 61',
                'message' => 'Mon ordinateur affiche un ecran noir au demarrage.',
                'status' => 'in_progress',
                'email' => 'leo.client@example.test',
            ],
            [
                'name' => 'Nina Petit',
                'phone' => '06 44 54 64 74',
                'message' => 'Je veux configurer mon imprimante reseau.',
                'status' => 'in_progress',
                'email' => 'nina.client@example.test',
            ],
            [
                'name' => 'Entreprise Demo',
                'phone' => '02 58 68 78 88',
                'message' => 'Maintenance de plusieurs postes bureautiques.',
                'status' => 'in_progress',
                'email' => null,
            ],
            [
                'name' => 'Tom Dubois',
                'phone' => '06 55 65 75 85',
                'message' => 'Le nettoyage et la mise a jour sont termines, merci.',
                'status' => 'done',
                'email' => 'tom.client@example.test',
            ],
            [
                'name' => 'Alice Martin',
                'phone' => '06 77 87 97 07',
                'message' => 'Probleme de clavier resolu, je valide la cloture.',
                'status' => 'done',
                'email' => 'alice.client@example.test',
            ],
            [
                'name' => 'Client Archivage',
                'phone' => '01 11 22 33 44',
                'message' => 'Ancienne demande conservee pour test de purge RGPD.',
                'status' => 'done',
                'email' => null,
            ],
        ];

        foreach ($requests as $index => $row) {
            $createdAt = now()->subDays(20 - ($index * 2));
            if ($row['name'] === 'Client Archivage') {
                $createdAt = now()->subDays(400);
            }

            $linkedUser = $row['email'] ? ($usersByEmail[$row['email']] ?? null) : null;

            $contactRequest = ContactRequest::query()->firstOrNew([
                'name' => $row['name'],
                'phone' => $row['phone'],
            ]);
            $contactRequest->user_id = $linkedUser?->id;
            $contactRequest->message = $row['message'];
            $contactRequest->status = $row['status'];
            $contactRequest->created_at = $createdAt;
            $contactRequest->updated_at = $createdAt->copy()->addHour();
            $contactRequest->save();

            if (!$linkedUser) {
                Message::query()->where('contact_request_id', $contactRequest->id)->delete();
                continue;
            }

            $this->seedThreadForRequest($contactRequest, $linkedUser, $supportUser, $row['status'], $createdAt);
        }
    }

    private function seedThreadForRequest(
        ContactRequest $contactRequest,
        User $clientUser,
        User $supportUser,
        string $requestStatus,
        Carbon $createdAt
    ): void {
        Message::query()->where('contact_request_id', $contactRequest->id)->delete();

        $messages = [
            [
                'sender_id' => $clientUser->id,
                'receiver_id' => $supportUser->id,
                'message' => $contactRequest->message,
                'status' => 'read',
            ],
        ];

        if ($requestStatus === 'pending') {
            $messages[] = [
                'sender_id' => $supportUser->id,
                'receiver_id' => $clientUser->id,
                'message' => 'Bonjour, nous avons bien recu votre demande.',
                'status' => 'read',
            ];
            $messages[] = [
                'sender_id' => $clientUser->id,
                'receiver_id' => $supportUser->id,
                'message' => 'Merci, j attends votre confirmation pour un rendez-vous.',
                'status' => 'unread',
            ];
        } elseif ($requestStatus === 'in_progress') {
            $messages[] = [
                'sender_id' => $supportUser->id,
                'receiver_id' => $clientUser->id,
                'message' => 'Votre demande est en cours de traitement.',
                'status' => 'read',
            ];
            $messages[] = [
                'sender_id' => $clientUser->id,
                'receiver_id' => $supportUser->id,
                'message' => 'Parfait, je reste disponible pour les tests.',
                'status' => 'read',
            ];
            $messages[] = [
                'sender_id' => $supportUser->id,
                'receiver_id' => $clientUser->id,
                'message' => 'Intervention prevue demain entre 10h et 12h.',
                'status' => 'unread',
            ];
        } else {
            $messages[] = [
                'sender_id' => $supportUser->id,
                'receiver_id' => $clientUser->id,
                'message' => 'Votre demande est terminee. Tout fonctionne correctement.',
                'status' => 'read',
            ];
            $messages[] = [
                'sender_id' => $clientUser->id,
                'receiver_id' => $supportUser->id,
                'message' => 'Merci pour le suivi et la rapidite.',
                'status' => 'read',
            ];
        }

        $payload = [];
        foreach ($messages as $index => $row) {
            $messageDate = $createdAt->copy()->addMinutes(($index + 1) * 20);

            $payload[] = [
                'sender_id' => $row['sender_id'],
                'receiver_id' => $row['receiver_id'],
                'contact_request_id' => $contactRequest->id,
                'message' => $row['message'],
                'status' => $row['status'],
                'created_at' => $messageDate,
                'updated_at' => $messageDate,
            ];
        }

        if ($payload !== []) {
            DB::table('messages')->insert($payload);
        }
    }
}

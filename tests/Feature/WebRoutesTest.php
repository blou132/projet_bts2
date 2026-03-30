<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class WebRoutesTest extends TestCase
{
    use RefreshDatabase;

    private function jmiSystemEmail(): string
    {
        return (string) env('JMI_SYSTEM_EMAIL', 'support-system@example.test');
    }

    private function jmiUsername(): string
    {
        return (string) env('JMI_USERNAME', 'client');
    }

    private function jmiPassword(): string
    {
        return (string) env('JMI_PASSWORD', 'client123');
    }

    public function test_home_page_is_accessible(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Demo Site')
            ->assertSee(route('legal.mentions'))
            ->assertSee(route('legal.privacy'))
            ->assertSee("Les informations collectées sont utilisées uniquement pour répondre à votre demande.");
    }

    public function test_legal_pages_are_accessible(): void
    {
        $this->get(route('legal.mentions'))
            ->assertOk()
            ->assertSee('Mentions legales')
            ->assertSee('Responsabilite');

        $this->get(route('legal.privacy'))
            ->assertOk()
            ->assertSee('Politique de confidentialite')
            ->assertSee('Vos droits RGPD');
    }

    public function test_register_page_is_accessible_for_guest(): void
    {
        $this->get(route('register'))
            ->assertOk();
    }

    public function test_register_creates_user_and_redirects_to_login(): void
    {
        $response = $this->post(route('register.submit'), [
            'name' => 'Test User',
            'email' => 'testuser@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('auth_success');
        $this->assertDatabaseHas('users', [
            'name' => 'Test User',
            'email' => 'testuser@example.com',
        ]);
    }

    public function test_register_rejects_profanity_in_name(): void
    {
        $response = $this->from(route('register'))->post(route('register.submit'), [
            'name' => 'Con',
            'email' => 'profanity@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect(route('register'));
        $response->assertSessionHasErrors('name');
        $this->assertDatabaseMissing('users', [
            'email' => 'profanity@example.com',
        ]);
    }

    public function test_user_can_login_with_email_and_password(): void
    {
        $user = User::factory()->create([
            'name' => 'Alice',
            'email' => 'alice@example.com',
            'password' => Hash::make('secret123'),
        ]);

        $response = $this->post(route('login.submit'), [
            'login' => $user->email,
            'password' => 'secret123',
        ]);

        $response->assertRedirect(route('home'));
        $response->assertSessionHas('user_id', $user->id);
        $response->assertSessionHas('user_name', 'Alice');
        $response->assertSessionHas('is_admin', false);
    }

    public function test_admin_can_login_with_unique_credentials(): void
    {
        $response = $this->post(route('login.submit'), [
            'login' => 'admin',
            'password' => 'admin123',
        ]);

        $response->assertRedirect(route('home'));
        $response->assertSessionHas('is_admin', true);
        $response->assertSessionHas('user_id');
    }

    public function test_login_fails_with_invalid_credentials(): void
    {
        $response = $this->from(route('login'))->post(route('login.submit'), [
            'login' => 'unknown@example.com',
            'password' => 'wrong-password',
        ]);

        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors('login');
    }

    public function test_login_page_redirects_when_session_already_exists(): void
    {
        $this->withSession([
            'user_id' => 1,
            'is_admin' => false,
        ])->get(route('login'))
            ->assertRedirect(route('home'));
    }

    public function test_admin_routes_redirect_non_jmi_to_login(): void
    {
        $this->get(route('admin'))->assertRedirect(route('login'));
        $this->get(route('admin.in_progress'))->assertRedirect(route('login'));
        $this->get(route('admin.done'))->assertRedirect(route('login'));
        $this->get(route('admin.search', ['q' => 'abc']))->assertRedirect(route('login'));

        $this->withSession([
            'user_id' => 1,
            'is_jmi' => false,
        ])->post(route('admin.requests.status', ['id' => 1]), [
            'status' => 'done',
        ])->assertRedirect(route('login'));
    }

    public function test_contact_form_creates_pending_request_with_sanitized_fields(): void
    {
        $response = $this->post(route('contact.submit'), [
            'name' => '  <b>Jean</b>  ',
            'phone' => '06 12 34 56 78',
            'message' => '<script>alert(1)</script> Bonjour',
        ]);

        $response->assertRedirect(route('home') . '#contact');
        $response->assertSessionHas('contact_success');
        $this->assertDatabaseHas('contact_requests', [
            'name' => 'Jean',
            'phone' => '06 12 34 56 78',
            'message' => 'alert(1) Bonjour',
            'status' => 'pending',
        ]);
    }

    public function test_contact_form_rejects_invalid_phone_format(): void
    {
        $response = $this->from(route('home'))->post(route('contact.submit'), [
            'name' => 'Jean',
            'phone' => '0612345678',
            'message' => 'Test message',
        ]);

        $response->assertRedirect(route('home'));
        $response->assertSessionHasErrors('phone');
        $this->assertDatabaseCount('contact_requests', 0);
    }

    public function test_contact_form_rejects_profanity_in_message(): void
    {
        $response = $this->from(route('home'))->post(route('contact.submit'), [
            'name' => 'Jean',
            'phone' => '06 12 34 56 78',
            'message' => 'Ceci est de la merde',
        ]);

        $response->assertRedirect(route('home'));
        $response->assertSessionHasErrors('message');
        $this->assertDatabaseCount('contact_requests', 0);
    }

    public function test_admin_index_displays_only_pending_requests(): void
    {
        DB::table('contact_requests')->insert([
            [
                'name' => 'Pending User',
                'phone' => '06 11 11 11 11',
                'message' => 'Pending',
                'status' => 'pending',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Progress User',
                'phone' => '06 22 22 22 22',
                'message' => 'Progress',
                'status' => 'in_progress',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $this->withSession(['is_jmi' => true])
            ->get(route('admin'))
            ->assertOk()
            ->assertSee('Pending User')
            ->assertDontSee('Progress User');
    }

    public function test_admin_tabs_filter_requests_by_status(): void
    {
        DB::table('contact_requests')->insert([
            [
                'name' => 'In Progress User',
                'phone' => '06 33 33 33 33',
                'message' => 'In Progress',
                'status' => 'in_progress',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Done User',
                'phone' => '06 44 44 44 44',
                'message' => 'Done',
                'status' => 'done',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $this->withSession(['is_jmi' => true])
            ->get(route('admin.in_progress'))
            ->assertOk()
            ->assertSee('In Progress User')
            ->assertDontSee('Done User');

        $this->withSession(['is_jmi' => true])
            ->get(route('admin.done'))
            ->assertOk()
            ->assertSee('Done User')
            ->assertDontSee('In Progress User');
    }

    public function test_admin_search_redirects_to_admin_when_query_is_empty(): void
    {
        $this->withSession(['is_jmi' => true])
            ->get(route('admin.search', ['q' => '   ']))
            ->assertRedirect(route('admin'));
    }

    public function test_admin_search_finds_requests_by_name_or_phone(): void
    {
        DB::table('contact_requests')->insert([
            [
                'name' => 'Marie Curie',
                'phone' => '06 55 55 55 55',
                'message' => 'Search me by name',
                'status' => 'pending',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Paul Martin',
                'phone' => '06 66 66 66 66',
                'message' => 'Search me by phone',
                'status' => 'done',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $this->withSession(['is_jmi' => true])
            ->get(route('admin.search', ['q' => 'Marie']))
            ->assertOk()
            ->assertSee('Marie Curie')
            ->assertDontSee('Paul Martin');

        $this->withSession(['is_jmi' => true])
            ->get(route('admin.search', ['q' => '66 66']))
            ->assertOk()
            ->assertSee('Paul Martin');
    }

    public function test_admin_can_update_request_status_and_redirects_to_target_tab(): void
    {
        DB::table('contact_requests')->insert([
            'id' => 123,
            'name' => 'Status User',
            'phone' => '06 77 77 77 77',
            'message' => 'Status',
            'status' => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->withSession(['is_jmi' => true])
            ->post(route('admin.requests.status', ['id' => 123]), [
                'status' => 'in_progress',
            ]);

        $response->assertRedirect(route('admin.in_progress') . '#request-123');
        $this->assertDatabaseHas('contact_requests', [
            'id' => 123,
            'status' => 'in_progress',
        ]);
    }

    public function test_admin_status_update_rejects_invalid_status(): void
    {
        DB::table('contact_requests')->insert([
            'id' => 124,
            'name' => 'Invalid Status User',
            'phone' => '06 88 88 88 88',
            'message' => 'Invalid status',
            'status' => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->from(route('admin'))->withSession(['is_jmi' => true])
            ->post(route('admin.requests.status', ['id' => 124]), [
                'status' => 'blocked',
            ]);

        $response->assertRedirect(route('admin'));
        $response->assertSessionHasErrors('status');
        $this->assertDatabaseHas('contact_requests', [
            'id' => 124,
            'status' => 'pending',
        ]);
    }

    public function test_admin_can_delete_request(): void
    {
        DB::table('contact_requests')->insert([
            'id' => 125,
            'name' => 'Delete User',
            'phone' => '06 99 99 99 99',
            'message' => 'To delete',
            'status' => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->withSession(['is_jmi' => true])
            ->delete(route('admin.requests.delete', ['id' => 125]));

        $response->assertSessionHas('admin_status');
        $this->assertDatabaseMissing('contact_requests', ['id' => 125]);
    }

    public function test_old_requests_are_purged_when_admin_page_is_loaded(): void
    {
        DB::table('contact_requests')->insert([
            [
                'id' => 126,
                'name' => 'Old Request',
                'phone' => '06 10 10 10 10',
                'message' => 'Old message',
                'status' => 'pending',
                'created_at' => now()->subDays(366),
                'updated_at' => now()->subDays(366),
            ],
            [
                'id' => 127,
                'name' => 'Recent Request',
                'phone' => '06 20 20 20 20',
                'message' => 'Recent message',
                'status' => 'pending',
                'created_at' => now()->subDays(10),
                'updated_at' => now()->subDays(10),
            ],
        ]);

        $this->withSession(['is_jmi' => true])
            ->get(route('admin'))
            ->assertOk();

        $this->assertDatabaseMissing('contact_requests', ['id' => 126]);
        $this->assertDatabaseHas('contact_requests', ['id' => 127]);
    }

    public function test_logout_clears_internal_and_user_sessions(): void
    {
        $response = $this->withSession([
            'is_admin' => true,
            'is_jmi' => true,
            'user_id' => 10,
            'user_name' => 'Someone',
        ])->post(route('logout'))
            ->assertRedirect(route('home'));

        $response->assertSessionMissing('is_admin');
        $response->assertSessionMissing('is_jmi');
        $response->assertSessionMissing('user_id');
        $response->assertSessionMissing('user_name');
    }

    public function test_messages_page_requires_logged_session(): void
    {
        $this->get(route('messages.index'))
            ->assertRedirect(route('login'));
    }

    public function test_jmi_can_open_messages_page(): void
    {
        $this->post(route('login.submit'), [
            'login' => $this->jmiUsername(),
            'password' => $this->jmiPassword(),
        ])->assertRedirect(route('home'));

        $this->get(route('messages.index'))
            ->assertOk()
            ->assertSee('Messagerie');
    }

    public function test_admin_cannot_open_messages_or_tickets(): void
    {
        $this->post(route('login.submit'), [
            'login' => 'admin',
            'password' => 'admin123',
        ])->assertRedirect(route('home'));

        $this->get(route('messages.index'))
            ->assertRedirect(route('home'));

        $this->get(route('admin'))
            ->assertRedirect(route('login'));
    }

    public function test_user_can_open_own_conversation_from_contact_request(): void
    {
        $user = User::factory()->create([
            'name' => 'Client User',
            'email' => 'client.user@example.com',
        ]);
        $adminUser = User::factory()->create([
            'name' => 'JMI Support',
            'email' => $this->jmiSystemEmail(),
        ]);

        DB::table('contact_requests')->insert([
            'id' => 700,
            'name' => 'Client User',
            'phone' => '06 40 40 40 40',
            'message' => 'Demande initiale',
            'user_id' => $user->id,
            'status' => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('messages')->insert([
            'sender_id' => $adminUser->id,
            'receiver_id' => $user->id,
            'contact_request_id' => 700,
            'message' => 'Bonjour, nous avons bien recu votre demande.',
            'status' => 'unread',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->withSession([
            'is_admin' => false,
            'user_id' => $user->id,
            'user_name' => $user->name,
        ])->get(route('messages.index', ['request' => 700]))
            ->assertOk()
            ->assertSee('Demande #700')
            ->assertSee('Bonjour, nous avons bien recu votre demande.');
    }

    public function test_logged_user_contact_request_creates_linked_message_thread(): void
    {
        $user = User::factory()->create([
            'name' => 'Linked User',
            'email' => 'linked.user@example.com',
        ]);

        $response = $this->withSession([
            'is_admin' => false,
            'user_id' => $user->id,
            'user_name' => $user->name,
        ])->post(route('contact.submit'), [
            'name' => 'Linked User',
            'phone' => '06 15 15 15 15',
            'message' => 'Bonjour <b>admin</b>',
        ]);

        $response->assertRedirect(route('home') . '#contact');

        $contactRequest = DB::table('contact_requests')->where('phone', '06 15 15 15 15')->first();
        $this->assertNotNull($contactRequest);
        $this->assertSame($user->id, (int) $contactRequest->user_id);

        $adminUser = DB::table('users')->where('email', $this->jmiSystemEmail())->first();
        $this->assertNotNull($adminUser);

        $this->assertDatabaseHas('messages', [
            'sender_id' => $user->id,
            'receiver_id' => (int) $adminUser->id,
            'contact_request_id' => (int) $contactRequest->id,
            'message' => 'Bonjour admin',
            'status' => 'unread',
        ]);
    }

    public function test_user_can_send_message_to_jmi_for_own_contact_request(): void
    {
        $user = User::factory()->create();
        $adminUser = User::factory()->create([
            'name' => 'JMI Support',
            'email' => $this->jmiSystemEmail(),
        ]);

        DB::table('contact_requests')->insert([
            'id' => 701,
            'name' => $user->name,
            'phone' => '06 31 31 31 31',
            'message' => 'Demande 701',
            'user_id' => $user->id,
            'status' => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->withSession([
            'is_admin' => false,
            'user_id' => $user->id,
            'user_name' => $user->name,
        ])->post(route('messages.send'), [
            'contact_request_id' => 701,
            'message' => '<b>Question</b> utilisateur',
        ]);

        $response->assertRedirect(route('messages.index', ['request' => 701]));
        $this->assertDatabaseHas('messages', [
            'sender_id' => $user->id,
            'receiver_id' => $adminUser->id,
            'contact_request_id' => 701,
            'message' => 'Question utilisateur',
            'status' => 'unread',
        ]);
    }

    public function test_user_cannot_send_message_with_profanity(): void
    {
        $user = User::factory()->create();
        User::factory()->create([
            'name' => 'JMI Support',
            'email' => $this->jmiSystemEmail(),
        ]);

        DB::table('contact_requests')->insert([
            'id' => 799,
            'name' => $user->name,
            'phone' => '06 39 39 39 39',
            'message' => 'Demande 799',
            'user_id' => $user->id,
            'status' => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->withSession([
            'is_admin' => false,
            'is_jmi' => false,
            'user_id' => $user->id,
            'user_name' => $user->name,
        ])->from(route('messages.index', ['request' => 799]))->post(route('messages.send'), [
            'contact_request_id' => 799,
            'message' => 'putain test',
        ]);

        $response->assertRedirect(route('messages.index', ['request' => 799]));
        $response->assertSessionHasErrors('message');
        $this->assertDatabaseCount('messages', 0);
    }

    public function test_user_cannot_send_message_on_another_user_contact_request(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();

        DB::table('contact_requests')->insert([
            'id' => 702,
            'name' => 'Owner',
            'phone' => '06 32 32 32 32',
            'message' => 'Owner request',
            'user_id' => $owner->id,
            'status' => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->withSession([
            'is_admin' => false,
            'user_id' => $otherUser->id,
            'user_name' => $otherUser->name,
        ])->post(route('messages.send'), [
            'contact_request_id' => 702,
            'message' => 'Tentative interdite',
        ])->assertRedirect(route('messages.index'));

        $this->assertDatabaseCount('messages', 0);
    }

    public function test_jmi_can_reply_to_user_on_contact_request_thread(): void
    {
        $requester = User::factory()->create();
        $adminUser = User::factory()->create([
            'name' => 'JMI Support',
            'email' => $this->jmiSystemEmail(),
        ]);

        DB::table('contact_requests')->insert([
            'id' => 703,
            'name' => 'Requester',
            'phone' => '06 33 33 33 33',
            'message' => 'Requester request',
            'user_id' => $requester->id,
            'status' => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->withSession([
            'is_jmi' => true,
            'user_id' => $adminUser->id,
            'user_name' => $adminUser->name,
        ])->post(route('messages.send'), [
            'contact_request_id' => 703,
            'message' => 'Reponse admin',
        ]);

        $response->assertRedirect(route('messages.index', ['request' => 703]));
        $this->assertDatabaseHas('messages', [
            'sender_id' => $adminUser->id,
            'receiver_id' => $requester->id,
            'contact_request_id' => 703,
            'message' => 'Reponse admin',
            'status' => 'unread',
        ]);
    }

    public function test_receiver_can_mark_message_as_read_in_thread(): void
    {
        $sender = User::factory()->create();
        $receiver = User::factory()->create();

        DB::table('contact_requests')->insert([
            'id' => 704,
            'name' => 'Receiver',
            'phone' => '06 34 34 34 34',
            'message' => 'Thread read',
            'user_id' => $receiver->id,
            'status' => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('messages')->insert([
            'id' => 801,
            'sender_id' => $sender->id,
            'receiver_id' => $receiver->id,
            'contact_request_id' => 704,
            'message' => 'Read me',
            'status' => 'unread',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->withSession([
            'is_admin' => false,
            'user_id' => $receiver->id,
            'user_name' => $receiver->name,
        ])->post(route('messages.read', ['id' => 801]), [
            'request_id' => 704,
        ]);

        $response->assertRedirect(route('messages.index', ['request' => 704]));
        $this->assertDatabaseHas('messages', [
            'id' => 801,
            'status' => 'read',
        ]);
    }

    public function test_non_receiver_cannot_mark_message_as_read(): void
    {
        $sender = User::factory()->create();
        $receiver = User::factory()->create();
        $otherUser = User::factory()->create();

        DB::table('contact_requests')->insert([
            'id' => 705,
            'name' => 'Receiver',
            'phone' => '06 35 35 35 35',
            'message' => 'Thread secure',
            'user_id' => $receiver->id,
            'status' => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('messages')->insert([
            'id' => 802,
            'sender_id' => $sender->id,
            'receiver_id' => $receiver->id,
            'contact_request_id' => 705,
            'message' => 'Private message',
            'status' => 'unread',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->withSession([
            'is_admin' => false,
            'user_id' => $otherUser->id,
            'user_name' => $otherUser->name,
        ])->post(route('messages.read', ['id' => 802]), [
            'request_id' => 705,
        ])->assertRedirect(route('messages.index', ['request' => 705]));

        $this->assertDatabaseHas('messages', [
            'id' => 802,
            'status' => 'unread',
        ]);
    }
}

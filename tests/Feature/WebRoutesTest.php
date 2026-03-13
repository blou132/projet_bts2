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

    public function test_home_page_is_accessible(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('JMI 56');
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
        $response->assertSessionMissing('user_id');
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

    public function test_admin_routes_redirect_non_admin_to_login(): void
    {
        $this->get(route('admin'))->assertRedirect(route('login'));
        $this->get(route('admin.in_progress'))->assertRedirect(route('login'));
        $this->get(route('admin.done'))->assertRedirect(route('login'));
        $this->get(route('admin.search', ['q' => 'abc']))->assertRedirect(route('login'));

        $this->withSession([
            'user_id' => 1,
            'is_admin' => false,
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

        $this->withSession(['is_admin' => true])
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

        $this->withSession(['is_admin' => true])
            ->get(route('admin.in_progress'))
            ->assertOk()
            ->assertSee('In Progress User')
            ->assertDontSee('Done User');

        $this->withSession(['is_admin' => true])
            ->get(route('admin.done'))
            ->assertOk()
            ->assertSee('Done User')
            ->assertDontSee('In Progress User');
    }

    public function test_admin_search_redirects_to_admin_when_query_is_empty(): void
    {
        $this->withSession(['is_admin' => true])
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

        $this->withSession(['is_admin' => true])
            ->get(route('admin.search', ['q' => 'Marie']))
            ->assertOk()
            ->assertSee('Marie Curie')
            ->assertDontSee('Paul Martin');

        $this->withSession(['is_admin' => true])
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

        $response = $this->withSession(['is_admin' => true])
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

        $response = $this->from(route('admin'))->withSession(['is_admin' => true])
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

        $response = $this->withSession(['is_admin' => true])
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

        $this->withSession(['is_admin' => true])
            ->get(route('admin'))
            ->assertOk();

        $this->assertDatabaseMissing('contact_requests', ['id' => 126]);
        $this->assertDatabaseHas('contact_requests', ['id' => 127]);
    }

    public function test_logout_clears_admin_and_user_sessions(): void
    {
        $response = $this->withSession([
            'is_admin' => true,
            'user_id' => 10,
            'user_name' => 'Someone',
        ])->post(route('logout'))
            ->assertRedirect(route('home'));

        $response->assertSessionMissing('is_admin');
        $response->assertSessionMissing('user_id');
        $response->assertSessionMissing('user_name');
    }
}

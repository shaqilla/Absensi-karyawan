<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase; // Ini biar database ngetesnya bersih tiap jalan

    /** @test */
    public function halaman_login_bisa_diakses()
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertSee('Masuk Sekarang'); // Mastiin teks di kodingan gue muncul
    }

    /** @test */
    public function user_bisa_login_pake_data_bener()
    {
        $user = User::factory()->create([
            'email' => 'samsul@gmail.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->post('/login', [
            'email' => 'samsul@gmail.com',
            'password' => 'password123',
        ]);

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect('/dashboard');
    }
}

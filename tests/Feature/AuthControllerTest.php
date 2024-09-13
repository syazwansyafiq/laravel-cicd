<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_login_with_valid_credentials()
    {
        // Create a user with known credentials
        $user = User::factory()->create([
            'password' => Hash::make('password'),
        ]);

        // Attempt to login with the user's credentials
        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        // Assert the response status is 200 (OK)
        $response->assertStatus(200);

        // Assert the response contains a token
        $response->assertJsonStructure([
            'token',
            'user' => [
                'id',
                'name',
                'email',
                // Include other user fields as needed
            ],
        ]);

        // Optionally assert that the token is valid
        $token = $response->json('token');
        $this->assertNotEmpty($token);

        // Optionally, verify the user is authenticated with the token
        $this->get('/api/user', ['Authorization' => "Bearer $token"])
            ->assertStatus(200)
            ->assertJson([
                'id' => $user->id,
                'email' => $user->email,
            ]);
    }

    /** @test */
    public function it_cannot_login_with_invalid_credentials()
    {
        // Attempt to login with invalid credentials
        $response = $this->postJson('/api/login', [
            'email' => 'invalid@example.com',
            'password' => 'invalidpassword',
        ]);

        // Assert the response status is 401 (Unauthorized)
        $response->assertStatus(401);

        // Assert the response contains an error message
        $response->assertJson([
            'message' => 'Unauthorized',
        ]);
    }
}

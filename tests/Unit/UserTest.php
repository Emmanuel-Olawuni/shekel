<?php

namespace Tests\Unit;

use App\Models\Discount;
use Tests\TestCase; 
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

class UserTest extends TestCase
{
    /**
     * A basic unit test example.
     */
    use RefreshDatabase, WithFaker;

    /**
     * Test user registration endpoint.
     */
    public function test_user_can_register()
    {
        $userData = [
            'name' => 'John Doe',
            'email' => 'john.doe@example.com',
            'password' => 'password123',
        ];
    
        $response = $this->postJson('/api/register', $userData);
    
        // Assert HTTP status code
        $response->assertStatus(201);
    
        // Assert JSON structure
        $response->assertJsonStructure([
            'access_token',
            'token_type',
        ]);
    
        // Optionally, assert specific values in the response
        $response->assertJson([
            'token_type' => 'Bearer',
        ]);
    
        // Assert that the user is saved in the database (if applicable)
        $this->assertDatabaseHas('users', [
            'email' => 'john.doe@example.com',
        ]);
    }
    


    /**
     * Test user login endpoint.
     */
    public function test_user_can_login()
{
    $user = User::factory()->create([
        'password' => bcrypt('password'),
    ]);

    $response = $this->postJson('/api/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    // Assert HTTP status code
    $response->assertStatus(200);

    // Assert JSON structure
    $response->assertJsonStructure([
        'access_token',
        'token_type',
    ]);

    // Optionally, assert specific values in the response
    $response->assertJson([
        'token_type' => 'Bearer',
    ]);
}



    /**
     * Test credit wallet endpoint.
     */
    public function test_user_can_credit_wallet()
    {
        $user = User::factory()->hasWallet()->create();

        $initialBalance = $user->wallet->balance;

        $response = $this->actingAs($user)->postJson('/api/wallet/credit', [
            'amount' => 100,
        ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('wallets', [
            'user_id' => $user->id,
            'balance' => $initialBalance + 100,
        ]);
    }


    /**
     * Test creating a discount with valid data.
     */
    public function test_create_discount_success()
    {
        $user = User::factory()->create(); // Create a user (if not already existing)
    
        $data = [
            'code' => 'SUMMER2024',
            'type' => 'percentage',
            'value' => 20,
            'start_date' => now()->format('Y-m-d'),
            'end_date' => now()->addDays(7)->format('Y-m-d'),
        ];
    
        $response = $this->actingAs($user)->postJson('/api/discounts', $data);
    
        $response->assertStatus(201)
            ->assertJson([
                'message' => 'Discount created successfully',
                'discount' => [
                    'code' => 'SUMMER2024',
                    'type' => 'percentage',
                    'value' => 20,
                ],
            ]);
    
        $this->assertDatabaseHas('discounts', [
            'code' => 'SUMMER2024',
        ]);
    }
    

    /**
     * Test creating a discount with invalid data.
     */

     public function test_create_discount_validation_error()
{
    $data = [
        'code' => 'SUMMER2024',
        'type' => 'invalid_type',
        'value' => 'not_numeric',
        'start_date' => 'invalid_date',
        'end_date' => 'invalid_date',
    ];
    $user = User::factory()->create(); // Create a user (if not already existing)
    $response = $this->actingAs($user)->postJson('/api/discounts', $data);

    $response->assertStatus(400)
        ->assertJson([
            'error' => 'Invalid Input. Unable to create Discount ',
        ]);

    $this->assertDatabaseCount('discounts', 0); // Assert no records were added
}

   

    /**
     * Test applying a valid discount code.
     */
    public function test_apply_discount_success()
    {
        $user = User::factory()->create();
    
        $discount = Discount::factory()->create([
            'code' => 'SUMMER2024',
            'type' => 'percentage',
            'value' => 20,
            'start_date' => now()->subDays(1),
            'end_date' => now()->addDays(7),
        ]);
    
        $data = [
            'code' => 'SUMMER2024',
            'amount' => 100,
        ];
    
        $response = $this->actingAs($user)->postJson('/api/discounts/apply', $data);
    
        $response->assertStatus(201)
            ->assertJson([
                'original_amount' => 100,
                'discounted_amount' => 80, // 20% discount applied
            ]);
    }
    

    /**
     * Test applying an invalid or expired discount code.
     */
    public function test_apply_discount_invalid()
    {
        $discount = Discount::factory()->create([
            'code' => 'EXPIRED2023',
            'type' => 'percentage',
            'value' => 15,
            'start_date' => now()->subMonths(6),
            'end_date' => now()->subMonths(3),
        ]);

        $data = [
            'code' => 'EXPIRED2023',
            'amount' => 100,
        ];
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/discounts/apply', $data);



        
        $response->assertStatus(400)
            ->assertJson([
                'error' => 'Discount code is not valid at this time',
            ]);
    }
}

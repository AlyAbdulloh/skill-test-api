<?php

namespace Tests\Feature;

use App\Models\Expense;
use App\Models\ExpenseDetail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExpenseApiTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test retrieving a list of expenses.
     */
    public function test_can_list_expenses(): void
    {
        ExpenseDetail::factory()->count(3)->create();

        $response = $this->getJson('/api/expenses');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'data' => [
                        '*' => [
                            'id',
                            'expense_date',
                            'notes',
                            'details' => [
                                '*' => [
                                    'id',
                                    'title',
                                    'amount',
                                    'created_at',
                                    'updated_at',
                                ]
                            ],
                            'created_at',
                            'updated_at',
                        ]
                    ]
                ]
            ]);
    }

    /**
     * Test search and filters.
     */
    public function test_can_filter_and_search_expenses(): void
    {
        $expense1 = Expense::create(['expense_date' => '2026-06-01', 'notes' => 'Post renovation']);
        $expense1->details()->create(['title' => 'Gate repair', 'amount' => 50000]);

        $expense2 = Expense::create(['expense_date' => '2026-06-15', 'notes' => 'Weekly cleaning']);
        $expense2->details()->create(['title' => 'Broom purchase', 'amount' => 10000]);

        // Search by detail title
        $response = $this->getJson('/api/expenses?search=Gate');
        $response->assertStatus(200)
            ->assertJsonCount(1, 'data.data')
            ->assertJsonPath('data.data.0.id', $expense1->id);

        // Search by notes
        $response = $this->getJson('/api/expenses?search=cleaning');
        $response->assertStatus(200)
            ->assertJsonCount(1, 'data.data')
            ->assertJsonPath('data.data.0.id', $expense2->id);

        // Filter by date range
        $response = $this->getJson('/api/expenses?start_date=2026-06-10&end_date=2026-06-20');
        $response->assertStatus(200)
            ->assertJsonCount(1, 'data.data')
            ->assertJsonPath('data.data.0.id', $expense2->id);
    }

    /**
     * Test creating an expense with details.
     */
    public function test_can_create_expense_with_details(): void
    {
        $payload = [
            'expense_date' => '2026-06-25',
            'notes' => 'Office operational expenses',
            'details' => [
                ['title' => 'Paper reams', 'amount' => 45000],
                ['title' => 'Ink cartridge', 'amount' => 120000],
            ]
        ];

        $response = $this->postJson('/api/expenses', $payload);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.notes', 'Office operational expenses')
            ->assertJsonCount(2, 'data.details');

        $this->assertDatabaseHas('tr_expenses', [
            'notes' => 'Office operational expenses',
        ]);

        $expense = Expense::first();
        $this->assertEquals('2026-06-25', $expense->expense_date->format('Y-m-d'));
        $this->assertDatabaseHas('tr_expense_details', [
            'expense_id' => $expense->id,
            'title' => 'Paper reams',
            'amount' => 45000.00,
        ]);
        $this->assertDatabaseHas('tr_expense_details', [
            'expense_id' => $expense->id,
            'title' => 'Ink cartridge',
            'amount' => 120000.00,
        ]);
    }

    /**
     * Test creating validation errors.
     */
    public function test_create_expense_validation_fails(): void
    {
        $response = $this->postJson('/api/expenses', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['expense_date', 'notes', 'details']);
    }

    /**
     * Test showing a specific expense.
     */
    public function test_can_show_expense(): void
    {
        $expense = Expense::factory()->create();
        $expense->details()->create(['title' => 'Detail 1', 'amount' => 1000]);

        $response = $this->getJson("/api/expenses/{$expense->id}");

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.notes', $expense->notes)
            ->assertJsonCount(1, 'data.details');
    }

    /**
     * Test updating an expense and rewriting its details.
     */
    public function test_can_update_expense_and_details(): void
    {
        $expense = Expense::create(['expense_date' => '2026-06-01', 'notes' => 'Old notes']);
        $expense->details()->create(['title' => 'Old detail', 'amount' => 10000]);

        $payload = [
            'notes' => 'New notes',
            'details' => [
                ['title' => 'New detail 1', 'amount' => 20000],
                ['title' => 'New detail 2', 'amount' => 30000],
            ]
        ];

        $response = $this->putJson("/api/expenses/{$expense->id}", $payload);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.notes', 'New notes')
            ->assertJsonCount(2, 'data.details');

        $expense->refresh();
        $this->assertEquals('New notes', $expense->notes);

        // Verify old detail was deleted and new ones exist
        $this->assertDatabaseMissing('tr_expense_details', ['title' => 'Old detail']);
        $this->assertDatabaseHas('tr_expense_details', [
            'expense_id' => $expense->id,
            'title' => 'New detail 1',
            'amount' => 20000,
        ]);
        $this->assertDatabaseHas('tr_expense_details', [
            'expense_id' => $expense->id,
            'title' => 'New detail 2',
            'amount' => 30000,
        ]);
    }

    /**
     * Test deleting an expense.
     */
    public function test_can_delete_expense_and_details(): void
    {
        $expense = Expense::create(['expense_date' => '2026-06-01', 'notes' => 'Delete me']);
        $detail = $expense->details()->create(['title' => 'Detail', 'amount' => 5000]);

        $response = $this->deleteJson("/api/expenses/{$expense->id}");

        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        $this->assertDatabaseMissing('tr_expenses', ['id' => $expense->id]);
        $this->assertDatabaseMissing('tr_expense_details', ['id' => $detail->id]);
    }
}

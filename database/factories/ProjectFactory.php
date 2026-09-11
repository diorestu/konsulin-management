<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Project>
 */
class ProjectFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'client_id' => Client::factory(),
            'created_by' => User::factory(),
            'reviewer_id' => fn (array $attributes) => $attributes['created_by'] ?? User::factory(),
            'name' => fake()->randomElement([
                'Monthly Tax Compliance',
                'Annual Financial Statement',
                'VAT Reconciliation',
                'Payroll Tax Review',
            ]),
            'service_type' => fake()->randomElement(['Tax', 'Accounting', 'Audit Support', 'Payroll']),
            'status' => fake()->randomElement(['not_started', 'in_progress', 'waiting_client', 'completed']),
            'priority' => fake()->randomElement(['low', 'medium', 'high', 'urgent']),
            'start_date' => fake()->dateTimeBetween('-1 month', '+1 week')->format('Y-m-d'),
            'due_date' => fake()->dateTimeBetween('+1 week', '+2 months')->format('Y-m-d'),
            'description' => fake()->paragraph(),
        ];
    }
}

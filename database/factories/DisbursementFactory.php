<?php

namespace Database\Factories;

use App\Models\Disbursement;
use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Disbursement>
 */
class DisbursementFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Disbursement::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $semesters = ['1st Semester', '2nd Semester', 'Summer'];
        $fundSources = ['GAA', 'CHED Fund', 'Special Fund', 'RA 10931 Fund'];
        $amount = $this->faker->randomFloat(2, 5000, 50000);

        return [
            'student_seq' => Student::factory(),
            'academic_year' => $this->faker->randomElement(['2023-2024', '2024-2025', '2025-2026']),
            'semester' => $this->faker->randomElement($semesters),
            'curriculum_year_level' => $this->faker->randomElement(['I', 'II', 'III', 'IV', 'V', 'VI']),
            'nta' => 'NTA-' . $this->faker->numerify('####'),
            'fund_source' => $this->faker->randomElement($fundSources),
            'amount' => $amount,
            'voucher_number' => 'VCH-' . $this->faker->unique()->numerify('########'),
            'mode_of_payment' => $this->faker->randomElement(['ATM', 'Cheque', 'Through the HEI']),
            'account_check_no' => $this->faker->optional(0.7)->numerify('##########'),
            'payment_amount' => $amount,
            'lddap_number' => $this->faker->optional(0.8)->numerify('LDDAP-####-##-####'),
            'disbursement_date' => $this->faker->optional(0.9)->dateTimeBetween('-1 year', 'now'),
            'remarks' => $this->faker->optional(0.3)->sentence(),
        ];
    }

    /**
     * Indicate that the disbursement is for ATM payment.
     */
    public function atm(): static
    {
        return $this->state(fn (array $attributes) => [
            'mode_of_payment' => 'ATM',
            'account_check_no' => $this->faker->numerify('##########'),
        ]);
    }

    /**
     * Indicate that the disbursement is by cheque.
     */
    public function cheque(): static
    {
        return $this->state(fn (array $attributes) => [
            'mode_of_payment' => 'Cheque',
            'account_check_no' => $this->faker->numerify('########'),
        ]);
    }

    /**
     * Indicate that the disbursement is through HEI.
     */
    public function throughHei(): static
    {
        return $this->state(fn (array $attributes) => [
            'mode_of_payment' => 'Through the HEI',
            'account_check_no' => null,
        ]);
    }
}
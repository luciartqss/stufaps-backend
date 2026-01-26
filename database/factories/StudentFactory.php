<?php

namespace Database\Factories;

use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Student>
 */
class StudentFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Student::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $regions = ['Region I', 'Region II', 'Region III', 'Region IV-A', 'Region IV-B', 'Region V', 'Region VI', 'Region VII', 'Region VIII', 'Region IX', 'Region X', 'Region XI', 'Region XII', 'NCR', 'CAR', 'ARMM'];
        $provinces = ['Pangasinan', 'Ilocos Norte', 'Ilocos Sur', 'La Union', 'Benguet', 'Cavite', 'Laguna', 'Batangas', 'Rizal', 'Quezon', 'Cebu', 'Davao del Sur', 'Zamboanga del Sur'];
        $institutionalTypes = ['SUC', 'LUC', 'Private HEI'];
        $scholarshipPrograms = [
            'CMSP',
            'Estatistikolar',
            'CoScho',
            'MSRS',
            'SIDA-SGP',
            'ACEF-GIAHEP',
            'MTP-SP',
            'CGMS-SUCs',
            'SNPLP',
        ];
        $disciplines = ['Engineering', 'Education', 'Business Administration', 'Information Technology', 'Nursing', 'Agriculture', 'Sciences', 'Arts and Letters'];
        $majors = ['Computer Science', 'Information Technology', 'Civil Engineering', 'Mechanical Engineering', 'Accountancy', 'Marketing', 'Elementary Education', 'Secondary Education'];
        $degreePrograms = ['BS Computer Science', 'BS Information Technology', 'BS Civil Engineering', 'BS Accountancy', 'BS Nursing', 'Bachelor of Elementary Education', 'Bachelor of Secondary Education'];

        return [
            'in_charge' => $this->faker->name(),
            'award_year' => $this->faker->numberBetween(2020, 2026),
            'scholarship_program' => $this->faker->randomElement($scholarshipPrograms),
            'award_number' => 'AWD-' . $this->faker->unique()->numerify('######'),
            'surname' => $this->faker->lastName(),
            'first_name' => $this->faker->firstName(),
            'middle_name' => $this->faker->lastName(),
            'extension' => $this->faker->optional(0.1)->randomElement(['Jr.', 'Sr.', 'II', 'III']),
            'sex' => $this->faker->randomElement(['Male', 'Female']),
            'date_of_birth' => $this->faker->dateTimeBetween('-25 years', '-18 years'),
            'contact_number' => $this->faker->numerify('09#########'),
            'email_address' => $this->faker->unique()->safeEmail(),
            'street_brgy' => $this->faker->streetAddress() . ', Brgy. ' . $this->faker->word(),
            'municipality_city' => $this->faker->city(),
            'province' => $this->faker->randomElement($provinces),
            'congressional_district' => $this->faker->randomElement(['1st District', '2nd District', '3rd District', '4th District']),
            'zip_code' => $this->faker->numerify('####'),
            'special_group' => $this->faker->optional(0.2)->randomElement(['IP', 'PWD', 'Solo Parent']),
            'certification_number' => $this->faker->optional(0.2)->numerify('CERT-######'),
            'name_of_institution' => $this->faker->company() . ' University',
            'uii' => 'UII-' . $this->faker->unique()->numerify('########'),
            'institutional_type' => $this->faker->randomElement($institutionalTypes),
            'region' => $this->faker->randomElement($regions),
            'degree_program' => $this->faker->randomElement($degreePrograms),
            'program_major' => $this->faker->randomElement($majors),
            'program_discipline' => $this->faker->randomElement($disciplines),
            'program_degree_level' => $this->faker->randomElement(['Pre-baccalaureate', 'Baccalaureate', 'Post Baccalaureate', 'Masters', 'Doctorate']),
            'authority_type' => $this->faker->randomElement(['GP', 'GR', 'RRPA', 'COPC']),
            'authority_number' => 'AUTH-' . $this->faker->numerify('####'),
            'series' => $this->faker->numerify('Series ####'),
            'is_priority' => $this->faker->boolean(20),
            'basis_cmo' => $this->faker->optional(0.3)->numerify('CMO No. ##, s. 20##'),
            'scholarship_status' => $this->faker->randomElement(['On-going', 'Graduated', 'Terminated']),
            'replacement_info' => $this->faker->optional(0.1)->sentence(),
            'termination_reason' => $this->faker->optional(0.1)->sentence(),
        ];
    }

    /**
     * Indicate that the student is ongoing.
     */
    public function ongoing(): static
    {
        return $this->state(fn (array $attributes) => [
            'scholarship_status' => 'On-going',
            'termination_reason' => null,
        ]);
    }

    /**
     * Indicate that the student has graduated.
     */
    public function graduated(): static
    {
        return $this->state(fn (array $attributes) => [
            'scholarship_status' => 'Graduated',
            'termination_reason' => null,
        ]);
    }

    /**
     * Indicate that the student is terminated.
     */
    public function terminated(): static
    {
        return $this->state(fn (array $attributes) => [
            'scholarship_status' => 'Terminated',
            'termination_reason' => $this->faker->randomElement([
                'Academic deficiency',
                'Voluntary withdrawal',
                'Non-compliance with requirements',
                'Transfer to another institution',
            ]),
        ]);
    }
}
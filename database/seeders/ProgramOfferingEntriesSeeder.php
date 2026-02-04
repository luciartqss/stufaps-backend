<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ProgramOfferingEntriesSeeder extends Seeder
{
    public function run(): void
    {
        $path = base_path('to delete later/Program_Offerings.json');
        if (!is_file($path)) {
            throw new RuntimeException('Program_Offerings.json not found at: ' . $path);
        }

        $content = file_get_contents($path);
        $data = json_decode($content, true);
        if (!is_array($data)) {
            throw new RuntimeException('Invalid JSON in Program_Offerings.json');
        }

        $rows = [];
        $now = now();

        foreach ($data as $item) {
            $rows[] = [
                'uii' => $this->normalizeUii($item['UII'] ?? null),
                'is_active' => $this->determineIsActive($item),
                'hei_name' => $this->nullIfEmpty($item['Name of Higher Education Institution'] ?? null),
                'municipality_city' => $this->nullIfEmpty($item["Municipality /\nCity"] ?? null),
                'province' => $this->nullIfEmpty($item['Province'] ?? null),
                'institutional_type' => $this->nullIfEmpty($item['Institutional Type'] ?? null),
                'program' => $this->nullIfEmpty($item['Program'] ?? null),
                'major_specialization' => $this->nullIfEmpty($item['Major / Specialization'] ?? null),
                'discipline_group' => $this->nullIfEmpty($item['Discipline Group'] ?? null),
                'program_level' => $this->nullIfEmpty($item['Program Level'] ?? null),
                'ga_level_i' => $this->nullIfEmpty($item['GA_Level I'] ?? null),
                'ga_level_ii' => $this->nullIfEmpty($item['GA_Level II'] ?? null),
                'ga_level_iii' => $this->nullIfEmpty($item['GA_Level III'] ?? null),
                'ga_level_iv' => $this->nullIfEmpty($item['GA_Level IV'] ?? null),
                'ga_level_v' => $this->nullIfEmpty($item['GA_Level V'] ?? null),
                'ga_level_vi' => $this->nullIfEmpty($item['GA_Level VI'] ?? null),
                'accreditation_level' => $this->nullIfEmpty($item['Accreditation_Accredited Level'] ?? null),
                'accreditation_accreditor' => $this->nullIfEmpty($item['Accreditation_Accreditor'] ?? null),
                'accreditation_validity' => $this->nullIfEmpty($item['Accreditation_Validity'] ?? null),
                'coe_cod' => $this->nullIfEmpty($item['COE / COD'] ?? null),
                'validity' => $this->nullIfEmpty($item['Validity'] ?? null),
                'gpr' => $this->nullIfEmpty($item['GPR'] ?? null),
                'gp_gr_no' => $this->nullIfEmpty($item['GP/GR No.'] ?? null),
                'series' => $this->nullIfEmpty($item['Series'] ?? null),
                'issued_by' => $this->nullIfEmpty($item['Issued by'] ?? null),
                'remarks' => $this->nullIfEmpty($item['REMARKS'] ?? null),
                'remarks2' => $this->nullIfEmpty($item['REMARKS2'] ?? null),
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        foreach (array_chunk($rows, 500) as $chunk) {
            DB::table('program_offering_entries')->insertOrIgnore($chunk);
        }
    }

    private function nullIfEmpty($value): ?string
    {
        if ($value === null) {
            return null;
        }

        $clean = trim((string) $value);
        return $clean === '' || $clean === '-' ? null : $clean;
    }

    private function normalizeUii($value): ?string
    {
        return $this->nullIfEmpty($value);
    }

    private function determineIsActive(array $item): bool
    {
        $flag = $this->nullIfEmpty($item['X'] ?? null);
        if ($flag !== null) {
            return strtolower($flag) === '1' || strtolower($flag) === 'yes';
        }

        $remarks = strtolower($this->nullIfEmpty($item['REMARKS'] ?? '') ?? '');
        return $remarks !== 'closed' && $remarks !== 'inactive';
    }
}

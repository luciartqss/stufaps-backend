<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class DirectoryEntriesSeeder extends Seeder
{
    public function run(): void
    {
        $path = base_path('to delete later/Directory.json');
        if (!is_file($path)) {
            throw new RuntimeException('Directory.json not found at: ' . $path);
        }

        $content = file_get_contents($path);
        $data = json_decode($content, true);
        if (!is_array($data)) {
            throw new RuntimeException('Invalid JSON in Directory.json');
        }

        $rows = [];
        $now = now();

        foreach ($data as $item) {
            $uii = $this->normalizeUii($item['UII'] ?? null);

            $rows[] = [
                'uii' => $uii,
                'name' => $this->nullIfEmpty($item['Name of Institution'] ?? null),
                'name_registered_sec' => $this->nullIfEmpty($item["Name of Institution\n (Registered in SEC)"] ?? null),
                'former_names' => $this->nullIfEmpty($item['Former Name/s'] ?? null),
                'is_active' => $this->determineIsActive($item),
                'remarks_status' => $this->nullIfEmpty($item['Remarks'] ?? null),
                'institutional_type' => $this->nullIfEmpty($item['Institutional Type'] ?? null),
                'sector' => $this->nullIfEmpty($item['Sector'] ?? null),
                'year_established' => $this->toYear($item['Year Established'] ?? null),
                'autonomous_status' => $this->nullIfEmpty($item['Autonomous / Deregulated / IR(LUCs)'] ?? null),
                'autonomous_validity' => $this->nullIfEmpty($item['Validity'] ?? null),
                'complete_address' => $this->nullIfEmpty($item['Complete Address'] ?? null),
                'street_brgy' => $this->nullIfEmpty($item['Street_Brgy'] ?? null),
                'municipality_city' => $this->nullIfEmpty($item['Municipality_City'] ?? null),
                'province' => $this->nullIfEmpty($item['Province'] ?? null),
                'district' => $this->nullIfEmpty($item['District'] ?? null),
                'contact_numbers' => $this->nullIfEmpty($item['Contact Number/s'] ?? null),
                'mobile_numbers' => $this->nullIfEmpty($item['Mobile Number/s'] ?? null),
                'email_address' => $this->nullIfEmpty($item['Email Address'] ?? null),
                'head_name' => $this->nullIfEmpty($item['Head of the Institution'] ?? null),
                'head_designation' => $this->nullIfEmpty($item['Designation'] ?? null),
                'head_telephone' => $this->nullIfEmpty($item['Telephone Number of  Head of the Institution'] ?? null),
                'head_mobile' => $this->nullIfEmpty($item['Mobile Number of Head of the Institution'] ?? null),
                'head_email' => $this->nullIfEmpty($item['Email Address of Head of the Institution'] ?? null),
                'registrar_name' => $this->nullIfEmpty($item['Registrar'] ?? null),
                'registrar_telephone' => $this->nullIfEmpty($item['Telephone Number of the Registrar'] ?? null),
                'registrar_mobile' => $this->nullIfEmpty($item['Mobile Number of the Registrar'] ?? null),
                'registrar_email' => $this->nullIfEmpty($item['Email Address of the Registrar'] ?? null),
                'additional_remarks' => $this->nullIfEmpty($item['Remarks2'] ?? null),
                'head_name_alt' => $this->nullIfEmpty($item['Head of Institution'] ?? null),
                'head_designation_alt' => $this->nullIfEmpty($item['Designation_1'] ?? null),
                'name_alt' => $this->nullIfEmpty($item['Name of Institution_1'] ?? null),
                'complete_address_alt' => $this->nullIfEmpty($item['Complete Address_1'] ?? null),
                'email_address_alt' => $this->nullIfEmpty($item['Email Address_1'] ?? null),
                'head_email_alt' => $this->nullIfEmpty($item['Email Address of the Head of the Institution'] ?? null),
                'registrar_email_alt' => $this->nullIfEmpty($item['Email Address of the Registrar_1'] ?? null),
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        foreach (array_chunk($rows, 500) as $chunk) {
            DB::table('directory_entries')->insertOrIgnore($chunk);
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

    private function toYear($value): ?int
    {
        $clean = $this->nullIfEmpty($value);
        if ($clean === null) {
            return null;
        }

        if (preg_match('/(\d{4})/', $clean, $matches)) {
            return (int) $matches[1];
        }

        return null;
    }

    private function determineIsActive(array $item): bool
    {
        $flag = $this->nullIfEmpty($item['X'] ?? null);
        if ($flag !== null) {
            return strtolower($flag) === '1' || strtolower($flag) === 'yes';
        }

        $remarks = strtolower($this->nullIfEmpty($item['Remarks'] ?? '') ?? '');
        return $remarks !== 'closed' && $remarks !== 'inactive';
    }
}

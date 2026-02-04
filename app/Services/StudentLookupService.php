<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class StudentLookupService
{
    /**
     * Auto-fill missing student fields from directory_entries and program_offering_entries
     * Only fills fields that are null/empty - does not overwrite existing data
     */
    public function fillMissingFields(array $studentData): array
    {
        // Step 1: Get UII from institution name if UII is missing
        if (empty($studentData['uii']) && !empty($studentData['name_of_institution'])) {
            $studentData['uii'] = $this->lookupUiiByInstitutionName($studentData['name_of_institution']);
        }

        // Step 2: Get institution details from directory_entries
        if (!empty($studentData['uii'])) {
            $studentData = $this->fillFromDirectory($studentData);
        }

        // Step 3: Get program details from program_offering_entries
        if (!empty($studentData['uii']) && !empty($studentData['degree_program'])) {
            $studentData = $this->fillFromProgramOfferings($studentData);
        }

        return $studentData;
    }

    /**
     * Lookup UII by institution name (tries exact match first, then partial)
     */
    public function lookupUiiByInstitutionName(string $institutionName): ?string
    {
        $institutionNameLower = strtolower(trim($institutionName));

        // Try exact match first (case-insensitive)
        $entry = DB::table('directory_entries')
            ->whereRaw('LOWER(name) = ?', [$institutionNameLower])
            ->first();

        if ($entry) {
            return $entry->uii;
        }

        // Try alternate name (case-insensitive)
        $entry = DB::table('directory_entries')
            ->whereRaw('LOWER(name_alt) = ?', [$institutionNameLower])
            ->first();

        if ($entry) {
            return $entry->uii;
        }

        // Try partial match (LIKE, case-insensitive)
        $entry = DB::table('directory_entries')
            ->where(function ($q) use ($institutionNameLower) {
                $q->whereRaw('LOWER(name) LIKE ?', ['%' . $institutionNameLower . '%'])
                  ->orWhereRaw('LOWER(name_alt) LIKE ?', ['%' . $institutionNameLower . '%']);
            })
            ->first();

        return $entry?->uii;
    }

    /**
     * Fill fields from directory_entries (institution info)
     */
    protected function fillFromDirectory(array $studentData): array
    {
        // Normalize UII - try with and without leading zeros
        $uii = $studentData['uii'];
        $uiiNormalized = ltrim($uii, '0') ?: '0';
        $uiiPadded = str_pad($uiiNormalized, 5, '0', STR_PAD_LEFT);

        $directory = DB::table('directory_entries')
            ->where(function ($q) use ($uii, $uiiNormalized, $uiiPadded) {
                $q->where('uii', $uii)
                  ->orWhere('uii', $uiiNormalized)
                  ->orWhere('uii', $uiiPadded);
            })
            ->first();

        if (!$directory) {
            return $studentData;
        }

        // Only fill if empty
        if (empty($studentData['name_of_institution'])) {
            $studentData['name_of_institution'] = $directory->name;
        }

        if (empty($studentData['institutional_type'])) {
            $studentData['institutional_type'] = $directory->institutional_type;
        }

        return $studentData;
    }

    /**
     * Fill fields from program_offering_entries (program/authority info)
     */
    protected function fillFromProgramOfferings(array $studentData): array
    {
        // Normalize UII - try with and without leading zeros
        $uii = $studentData['uii'];
        $uiiNormalized = ltrim($uii, '0') ?: '0';
        $uiiPadded = str_pad($uiiNormalized, 5, '0', STR_PAD_LEFT);

        $degreeProgram = trim($studentData['degree_program']);
        $degreeProgramLower = strtolower($degreeProgram);
        $majorLower = !empty($studentData['program_major']) ? strtolower(trim($studentData['program_major'])) : null;

        // Build base query for UII matching
        $baseQuery = function () use ($uii, $uiiNormalized, $uiiPadded) {
            return DB::table('program_offering_entries')
                ->where('is_active', true)
                ->where(function ($q) use ($uii, $uiiNormalized, $uiiPadded) {
                    $q->where('uii', $uii)
                      ->orWhere('uii', $uiiNormalized)
                      ->orWhere('uii', $uiiPadded);
                });
        };

        $program = null;

        // Strategy 1: Exact program match with major handling
        if ($majorLower) {
            // Student has a major - try exact match with major first
            $program = $baseQuery()
                ->whereRaw('LOWER(program) = ?', [$degreeProgramLower])
                ->whereRaw('LOWER(major_specialization) = ?', [$majorLower])
                ->first();

            // Fallback: Try without major filter
            if (!$program) {
                $program = $baseQuery()
                    ->whereRaw('LOWER(program) = ?', [$degreeProgramLower])
                    ->first();
            }
        } else {
            // Student has no major - prefer entries with null major_specialization
            $program = $baseQuery()
                ->whereRaw('LOWER(program) = ?', [$degreeProgramLower])
                ->whereNull('major_specialization')
                ->first();

            // Fallback: Accept any entry for this program
            if (!$program) {
                $program = $baseQuery()
                    ->whereRaw('LOWER(program) = ?', [$degreeProgramLower])
                    ->first();
            }
        }

        // Strategy 2: Partial match (LIKE) if exact match fails
        if (!$program) {
            $program = $baseQuery()
                ->whereRaw('LOWER(program) LIKE ?', ['%' . $degreeProgramLower . '%'])
                ->first();
        }

        // Strategy 3: Try matching without "Bachelor of Science in" prefix etc.
        if (!$program) {
            $shortProgram = $this->extractShortProgramName($degreeProgram);
            if ($shortProgram && $shortProgram !== $degreeProgramLower) {
                $program = $baseQuery()
                    ->whereRaw('LOWER(program) LIKE ?', ['%' . $shortProgram . '%'])
                    ->first();
            }
        }

        if (!$program) {
            return $studentData;
        }

        // Only fill if empty and value exists in program data
        if (empty($studentData['authority_type']) && !empty($program->gpr)) {
            $studentData['authority_type'] = $program->gpr;
        }

        if (empty($studentData['authority_number']) && !empty($program->gp_gr_no)) {
            $studentData['authority_number'] = $program->gp_gr_no;
        }

        if (empty($studentData['series']) && !empty($program->series)) {
            $studentData['series'] = $program->series;
        }

        // Fill other program-related fields if empty
        if (empty($studentData['program_discipline']) && !empty($program->discipline_group)) {
            $studentData['program_discipline'] = $program->discipline_group;
        }

        if (empty($studentData['program_degree_level']) && !empty($program->program_level)) {
            $studentData['program_degree_level'] = $program->program_level;
        }

        return $studentData;
    }

    /**
     * Extract a shorter program name by removing common prefixes
     */
    protected function extractShortProgramName(string $program): ?string
    {
        $prefixes = [
            'bachelor of science in ',
            'bachelor of arts in ',
            'bachelor of ',
            'master of science in ',
            'master of arts in ',
            'master of ',
            'master in ',
            'doctor of philosophy in ',
            'doctor of ',
            'associate in ',
            'diploma in ',
            'certificate in ',
            'bs in ',
            'bs ',
            'ab in ',
            'ab ',
            'ma in ',
            'ma ',
            'ms in ',
            'ms ',
            'phd in ',
            'phd ',
        ];

        $programLower = strtolower(trim($program));
        
        foreach ($prefixes as $prefix) {
            if (str_starts_with($programLower, $prefix)) {
                $short = substr($programLower, strlen($prefix));
                return trim($short) ?: null;
            }
        }

        return null;
    }

    /**
     * Get all institutions for dropdown/autocomplete
     */
    public function getInstitutions(): array
    {
        return DB::table('directory_entries')
            ->select('uii', 'name', 'municipality_city', 'province', 'institutional_type')
            ->where('is_active', true)
            ->orderBy('name')
            ->get()
            ->toArray();
    }

    /**
     * Get programs for a specific institution (by UII)
     */
    public function getProgramsByUii(string $uii): array
    {
        // Normalize UII - try with and without leading zeros
        $uiiNormalized = ltrim($uii, '0') ?: '0';
        $uiiPadded = str_pad($uiiNormalized, 5, '0', STR_PAD_LEFT);

        return DB::table('program_offering_entries')
            ->select('program', 'major_specialization', 'gpr', 'gp_gr_no', 'series', 'discipline_group', 'program_level')
            ->where(function ($q) use ($uii, $uiiNormalized, $uiiPadded) {
                $q->where('uii', $uii)
                  ->orWhere('uii', $uiiNormalized)
                  ->orWhere('uii', $uiiPadded);
            })
            ->where('is_active', true)
            ->orderBy('program')
            ->get()
            ->toArray();
    }

    /**
     * Search institutions by name (for autocomplete)
     */
    public function searchInstitutions(string $search, int $limit = 20): array
    {
        $searchLower = strtolower(trim($search));

        return DB::table('directory_entries')
            ->select('uii', 'name', 'municipality_city', 'province')
            ->where('is_active', true)
            ->where(function ($query) use ($searchLower) {
                $query->whereRaw('LOWER(name) LIKE ?', ['%' . $searchLower . '%'])
                      ->orWhereRaw('LOWER(name_alt) LIKE ?', ['%' . $searchLower . '%']);
            })
            ->limit($limit)
            ->get()
            ->toArray();
    }

    /**
     * Debug lookup - returns diagnostic info about why a lookup might fail
     */
    public function debugLookup(array $studentData): array
    {
        $debug = [
            'input' => [
                'uii' => $studentData['uii'] ?? null,
                'name_of_institution' => $studentData['name_of_institution'] ?? null,
                'degree_program' => $studentData['degree_program'] ?? null,
                'program_major' => $studentData['program_major'] ?? null,
            ],
            'uii_lookup' => null,
            'institution_found' => false,
            'programs_for_uii' => [],
            'program_match' => null,
            'issues' => [],
        ];

        // Check UII
        $uii = $studentData['uii'] ?? null;
        if (empty($uii) && !empty($studentData['name_of_institution'])) {
            $uii = $this->lookupUiiByInstitutionName($studentData['name_of_institution']);
            $debug['uii_lookup'] = $uii;
            if (!$uii) {
                $debug['issues'][] = 'Could not find UII for institution: ' . $studentData['name_of_institution'];
            }
        }

        if (empty($uii)) {
            $debug['issues'][] = 'No UII available';
            return $debug;
        }

        // Check institution
        $uiiNormalized = ltrim($uii, '0') ?: '0';
        $uiiPadded = str_pad($uiiNormalized, 5, '0', STR_PAD_LEFT);
        $debug['uii_variations'] = [$uii, $uiiNormalized, $uiiPadded];

        $institution = DB::table('directory_entries')
            ->where(function ($q) use ($uii, $uiiNormalized, $uiiPadded) {
                $q->where('uii', $uii)
                  ->orWhere('uii', $uiiNormalized)
                  ->orWhere('uii', $uiiPadded);
            })
            ->first();

        $debug['institution_found'] = (bool) $institution;
        if (!$institution) {
            $debug['issues'][] = 'No institution found for UII: ' . $uii;
        }

        // Check programs for this UII
        $programs = DB::table('program_offering_entries')
            ->where(function ($q) use ($uii, $uiiNormalized, $uiiPadded) {
                $q->where('uii', $uii)
                  ->orWhere('uii', $uiiNormalized)
                  ->orWhere('uii', $uiiPadded);
            })
            ->where('is_active', true)
            ->get();

        $debug['programs_count'] = $programs->count();
        $debug['programs_for_uii'] = $programs->map(fn($p) => [
            'program' => $p->program,
            'major' => $p->major_specialization,
            'gpr' => $p->gpr,
            'series' => $p->series,
        ])->toArray();

        if ($programs->isEmpty()) {
            $debug['issues'][] = 'No programs found for UII: ' . $uii;
            return $debug;
        }

        // Try to match program
        if (!empty($studentData['degree_program'])) {
            $degreeProgramLower = strtolower(trim($studentData['degree_program']));
            
            // Try exact match
            $exactMatch = $programs->first(fn($p) => strtolower($p->program) === $degreeProgramLower);
            if ($exactMatch) {
                $debug['program_match'] = 'exact';
                $debug['matched_program'] = [
                    'program' => $exactMatch->program,
                    'major' => $exactMatch->major_specialization,
                    'gpr' => $exactMatch->gpr,
                    'gp_gr_no' => $exactMatch->gp_gr_no,
                    'series' => $exactMatch->series,
                ];
                
                // Check if authority data is empty
                if (empty($exactMatch->gpr) && empty($exactMatch->gp_gr_no) && empty($exactMatch->series)) {
                    $debug['issues'][] = 'Program found but authority data (gpr, gp_gr_no, series) is empty in database';
                }
            } else {
                // Try partial match
                $partialMatch = $programs->first(fn($p) => str_contains(strtolower($p->program), $degreeProgramLower));
                if ($partialMatch) {
                    $debug['program_match'] = 'partial';
                    $debug['matched_program'] = [
                        'program' => $partialMatch->program,
                        'major' => $partialMatch->major_specialization,
                    ];
                } else {
                    $debug['issues'][] = 'No matching program found. Student program: "' . $studentData['degree_program'] . '"';
                    $debug['similar_programs'] = $programs->take(5)->pluck('program')->toArray();
                }
            }
        } else {
            $debug['issues'][] = 'No degree_program provided';
        }

        return $debug;
    }
}

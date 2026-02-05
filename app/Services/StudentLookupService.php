<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class StudentLookupService
{
    /**
     * Normalize a string for comparison:
     * - Lowercase
     * - Trim leading/trailing spaces
     * - Collapse multiple spaces into one
     * - Normalize spaces around hyphens (remove spaces around -)
     */
    protected function normalizeForComparison(string $value): string
    {
        $normalized = strtolower(trim($value));
        // Collapse multiple spaces into one
        $normalized = preg_replace('/\s+/', ' ', $normalized);
        // Remove spaces around hyphens: " - " or " -" or "- " becomes "-"
        $normalized = preg_replace('/\s*-\s*/', '-', $normalized);
        return $normalized;
    }

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
     * Lookup UII by institution name (exact match only, case and space insensitive)
     * Returns null if no exact match is found - spelling must be accurate
     */
    public function lookupUiiByInstitutionName(string $institutionName): ?string
    {
        $normalizedInput = $this->normalizeForComparison($institutionName);

        // Get all directory entries and compare with normalized values
        $entries = DB::table('directory_entries')->get(['uii', 'name', 'name_alt']);

        foreach ($entries as $entry) {
            // Compare normalized names
            if ($this->normalizeForComparison($entry->name) === $normalizedInput) {
                return $entry->uii;
            }
            // Also check alternate name
            if (!empty($entry->name_alt) && $this->normalizeForComparison($entry->name_alt) === $normalizedInput) {
                return $entry->uii;
            }
        }

        // No exact match found
        return null;
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

        $normalizedProgram = $this->normalizeForComparison($studentData['degree_program']);
        $normalizedMajor = !empty($studentData['program_major']) 
            ? $this->normalizeForComparison($studentData['program_major']) 
            : null;

        // Get all programs for this UII and compare with normalized values
        $programs = DB::table('program_offering_entries')
            ->where('is_active', true)
            ->where(function ($q) use ($uii, $uiiNormalized, $uiiPadded) {
                $q->where('uii', $uii)
                  ->orWhere('uii', $uiiNormalized)
                  ->orWhere('uii', $uiiPadded);
            })
            ->get();

        $program = null;

        // Strategy 1: Exact match with both program and major (if major provided)
        if ($normalizedMajor) {
            foreach ($programs as $p) {
                if ($this->normalizeForComparison($p->program) === $normalizedProgram &&
                    !empty($p->major_specialization) &&
                    $this->normalizeForComparison($p->major_specialization) === $normalizedMajor) {
                    $program = $p;
                    break;
                }
            }
        }

        // Strategy 2: Exact program match with null major (if no major provided or no match with major)
        if (!$program && !$normalizedMajor) {
            foreach ($programs as $p) {
                if ($this->normalizeForComparison($p->program) === $normalizedProgram &&
                    empty($p->major_specialization)) {
                    $program = $p;
                    break;
                }
            }
        }

        // Strategy 3: Exact program match (any major) as final fallback
        if (!$program) {
            foreach ($programs as $p) {
                if ($this->normalizeForComparison($p->program) === $normalizedProgram) {
                    $program = $p;
                    break;
                }
            }
        }

        // No match found - spelling must be accurate
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

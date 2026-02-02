<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Masterlist - {{ $program }}</title>
    <style>
        body { font-family: Arial, sans-serif; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid black; padding: 5px; text-align: center; }
        .header { text-align: center; margin-bottom: 20px; }
        .title { font-size: 14px; font-weight: bold; }
    </style>
</head>
<body>
    <div style="display: flex; flex-direction: row; align-items: flex-start; margin-bottom: 5px;">
        <div style="font-family: Calibri, sans-serif; font-size: 10px; font-style: italic; margin-right: 15px;">
            Annex D - Masterlist
        </div>
        <div style="font-family: Calibri, sans-serif; font-size: 10px; font-style: italic; margin-right: 15px;">
            2025 version
        </div>
        <!-- Add more cells as needed -->
    </div>
    <div class="header">
        <div class="title" style="font-family: Calibri, sans-serif; font-size: 10px; font-weight: bold;">COMMISSION ON HIGHER EDUCATION</div>
        <div class="title" style="font-family: Calibri, sans-serif; font-size: 10px; font-weight: bold;">CHED REGIONAL OFFICE IV</div>
        <div class="title" style="font-family: Calibri, sans-serif; font-size: 10px; font-weight: bold;">MASTERLIST OF {{ $program }}</div>
        <div class="title" style="font-family: Calibri, sans-serif; font-size: 10px; font-weight: bold;">PROGRAM BENEFICIARIES</div>
        <div style="font-family: Calibri, sans-serif; font-size: 10px; font-weight: bold;">
            <strong>{{ $semester == 1 ? '1st' : '2nd' }}</strong> Semester, AY <strong>{{ $academicYear }}</strong>
        </div>
    </div>
    
    <table>
        <thead>
            <tr>
                <th rowspan="2" style="font-family: Calibri, sans-serif; font-size: 10px; font-weight: bold;">NOS.</th>
                <th rowspan="2" style="font-family: Calibri, sans-serif; font-size: 8px; font-weight: bold;">Learner Reference Number</th>
                <th rowspan="2" style="font-family: Calibri, sans-serif; font-size: 8px; font-weight: bold;">AWARD. NO.</th>
                <th colspan="4" style="font-family: Calibri, sans-serif; font-size: 8px; font-weight: bold;">NAME</th>
                <th rowspan="2" style="font-family: Calibri, sans-serif; font-size: 8px; font-weight: bold;">HEI</th>
                <th rowspan="2" style="font-family: Calibri, sans-serif; font-size: 8px; font-weight: bold;">PROGRAM NAME</th>
                <th rowspan="2" style="font-family: Calibri, sans-serif; font-size: 8px; font-weight: bold;">CURRENT YEAR LEVEL</th>
                <th rowspan="2" style="font-family: Calibri, sans-serif; font-size: 8px; font-weight: bold;">FINANCIAL BENEFITS</th>
                <th rowspan="2" style="font-family: Calibri, sans-serif; font-size: 8px; font-weight: bold;">REMARKS</th>
            </tr>
            <tr>
                <th style="font-family: Calibri, sans-serif; font-size: 8px; font-weight: bold;">LAST NAME</th>
                <th style="font-family: Calibri, sans-serif; font-size: 8px; font-weight: bold;">FIRST NAME</th>
                <th style="font-family: Calibri, sans-serif; font-size: 8px; font-weight: bold;">M.I.</th>
                <th style="font-family: Calibri, sans-serif; font-size: 8px; font-weight: bold;">Extension</th>
            </tr>
        </thead>
        <tbody>
            @foreach($students as $index => $student)
            <tr>
                <td style="font-family: Calibri, sans-serif; font-size: 8px; text-align: left;">{{ $index + 1 }}</td>
                <td style="font-family: Calibri, sans-serif; font-size: 8px; text-align: left;">{{ data_get($student, 'lrn', data_get($student, 'uii', '')) }}</td>
                <td style="font-family: Calibri, sans-serif; font-size: 8px; text-align: left; font-weight: bold;">{{ data_get($student, 'award_number', '') }}</td>
                <td style="font-family: Calibri, sans-serif; font-size: 8px; text-align: left;">{{ data_get($student, 'surname', '') }}</td>
                <td style="font-family: Calibri, sans-serif; font-size: 8px; text-align: left;">{{ data_get($student, 'first_name', '') }}</td>
                <td style="font-family: Calibri, sans-serif; font-size: 8px; text-align: left;">{{ data_get($student, 'middle_name') ? substr(data_get($student, 'middle_name'), 0, 1) : '' }}</td>
                <td style="font-family: Calibri, sans-serif; font-size: 8px; text-align: left;">{{ data_get($student, 'extension', '') }}</td>
                <td style="font-family: Calibri, sans-serif; font-size: 8px; text-align: left;">{{ data_get($student, 'name_of_institution', '') }}</td>
                <td style="font-family: Calibri, sans-serif; font-size: 8px; text-align: left;">{{ data_get($student, 'degree_program', '') }}</td>
                <td style="font-family: Calibri, sans-serif; font-size: 8px; text-align: center;">{{ data_get($student, 'current_year_level', data_get($student, 'curriculum_year_level', '')) }}</td>
                <td style="font-family: Calibri, sans-serif; font-size: 8px; text-align: right;">{{ number_format((float) data_get($student, 'financial_benefits', 0), 2) }}</td>
                <td style="font-family: Calibri, sans-serif; font-size: 8px; text-align: left;">{{ data_get($student, 'remarks', '') }}</td>
            </tr>
            @endforeach
            <tr>
                <td colspan="10" style="font-family: Calibri, sans-serif; font-size: 12px; font-weight: bold; text-align: right;"><strong>TOTAL</strong></td>
                <td style="font-family: Calibri, sans-serif; font-size: 12px; font-weight: bold; text-align: right;"><strong>{{ number_format((float) $totalBenefits, 2) }}</strong></td>
                <td style="font-family: Calibri, sans-serif; font-size: 12px; text-align: left;"></td>
            </tr>
        </tbody>
        <tfoot>
            <!-- Signature Row - Labels -->
            <tr>
                <td colspan="12" style="border: none; height: 20px;"></td>
            </tr>
            <tr>
                <td style="border: none;"></td><!-- NOS -->
                <td style="border: none; font-family: Calibri, sans-serif; font-size: 10px; text-align: left;">Prepared:</td><!-- LRN -->
                <td style="border: none;"></td><!-- AWARD NO -->
                <td style="border: none;"></td><!-- LAST NAME -->
                <td style="border: none;"></td><!-- FIRST NAME -->
                <td colspan="3" style="border: none; font-family: Calibri, sans-serif; font-size: 10px; text-align: left;">Reviewed and Certified Correct:</td><!-- M.I., Extension, HEI -->
                <td style="border: none;"></td><!-- PROGRAM NAME -->
                <td style="border: none; font-family: Calibri, sans-serif; font-size: 10px; text-align: left;">Approved:</td><!-- CURRENT YEAR LEVEL -->
                <td style="border: none;"></td><!-- FINANCIAL BENEFITS -->
                <td style="border: none;"></td><!-- REMARKS -->
            </tr>
        </tfoot>
    </table>
</body>
</html>
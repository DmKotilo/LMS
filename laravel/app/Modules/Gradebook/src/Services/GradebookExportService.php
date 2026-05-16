<?php

namespace Gradebook\Services;

use Gradebook\Models\Gradebook;
use Symfony\Component\HttpFoundation\StreamedResponse;

class GradebookExportService
{
    public function export(Gradebook $gradebook, string $format): StreamedResponse
    {
        $gradebook->load(['rows' => fn ($q) => $q->orderBy('student_name'), 'teacher']);

        return match ($format) {
            'json' => $this->jsonResponse($gradebook),
            default => $this->csvResponse($gradebook),
        };
    }

    private function csvResponse(Gradebook $gradebook): StreamedResponse
    {
        $filename = 'gradebook-'.$gradebook->id.'.csv';

        return response()->streamDownload(function () use ($gradebook) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($handle, [
                'student_name',
                'group_name',
                'semester',
                'module1_score',
                'module2_score',
                'total_score',
                'final_grade',
            ], ';');

            foreach ($gradebook->rows as $row) {
                fputcsv($handle, [
                    $row->student_name,
                    $row->group_name,
                    $row->semester ?? $gradebook->semester,
                    $row->module1_score,
                    $row->module2_score,
                    $row->total_score,
                    $row->final_grade,
                ], ';');
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=utf-8',
        ]);
    }

    private function jsonResponse(Gradebook $gradebook): StreamedResponse
    {
        $filename = 'gradebook-'.$gradebook->id.'.json';

        $payload = [
            'gradebook' => [
                'id' => $gradebook->id,
                'title' => $gradebook->title,
                'discipline' => $gradebook->discipline,
                'group_name' => $gradebook->group_name,
                'semester' => $gradebook->semester,
                'teacher' => $gradebook->teacher?->fullName(),
                'uploaded_at' => $gradebook->created_at?->toIso8601String(),
            ],
            'rows' => $gradebook->rows->map(fn ($row) => [
                'student_name' => $row->student_name,
                'group_name' => $row->group_name,
                'semester' => $row->semester ?? $gradebook->semester,
                'module1_score' => $row->module1_score,
                'module2_score' => $row->module2_score,
                'total_score' => $row->total_score,
                'final_grade' => $row->final_grade,
            ])->values(),
        ];

        return response()->streamDownload(
            fn () => print(json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)),
            $filename,
            ['Content-Type' => 'application/json; charset=utf-8'],
        );
    }
}

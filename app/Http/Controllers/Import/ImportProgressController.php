<?php

namespace App\Http\Controllers\Import;

use App\Domain\Import\Models\Import;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ImportProgressController extends Controller
{
    public function stream(Request $request, Import $import): StreamedResponse
    {
        // Authorize
        $this->authorize('view', $import);

        return response()->stream(function () use ($import) {
            // Set headers for SSE
            header('Content-Type: text/event-stream');
            header('Cache-Control: no-cache');
            header('Connection: keep-alive');
            header('X-Accel-Buffering: no'); // Disable nginx buffering

            // Send initial state
            $this->sendUpdate($import);

            // Poll for updates while processing
            $lastImportedRows = $import->imported_rows;
            $timeout = 300; // 5 minutes max
            $elapsed = 0;

            while ($elapsed < $timeout) {
                // Refresh import from database
                $import->refresh();

                // Send update if progress changed or status changed
                if ($import->imported_rows !== $lastImportedRows || $import->status->value !== 'processing') {
                    $this->sendUpdate($import);
                    $lastImportedRows = $import->imported_rows;
                }

                // If completed or failed, send final update and close
                if ($import->status->value !== 'processing') {
                    $this->sendUpdate($import);
                    echo "event: close\n";
                    echo "data: {}\n\n";
                    if (ob_get_level() > 0) {
                        ob_end_flush();
                    }
                    flush();
                    break;
                }

                // Sleep for 1 second before next check
                sleep(1);
                $elapsed++;

                // Keep connection alive
                if ($elapsed % 15 === 0) {
                    echo ": keepalive\n\n";
                    if (ob_get_level() > 0) {
                        ob_end_flush();
                    }
                    flush();
                }
            }

            // Send timeout or completion
            if ($elapsed >= $timeout) {
                echo "event: error\n";
                echo "data: " . json_encode(['error' => 'Timeout']) . "\n\n";
            }

            if (ob_get_level() > 0) {
                ob_end_flush();
            }
            flush();
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'X-Accel-Buffering' => 'no',
        ]);
    }

    private function sendUpdate(Import $import): void
    {
        $progress = $import->total_rows > 0
            ? round(($import->imported_rows / $import->total_rows) * 100)
            : 0;

        $data = [
            'status' => $import->status->value,
            'imported_rows' => $import->imported_rows,
            'total_rows' => $import->total_rows,
            'skipped_rows' => $import->skipped_rows,
            'failed_rows' => $import->failed_rows,
            'progress' => $progress,
            'debug_logs' => $import->debug_logs ?? [],
        ];

        echo "event: progress\n";
        echo "data: " . json_encode($data) . "\n\n";

        if (ob_get_level() > 0) {
            ob_end_flush();
        }
        flush();
    }
}

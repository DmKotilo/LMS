<?php

namespace App\Http\Controllers\Moonshine;

use App\Http\Controllers\Controller;
use Illuminate\Http\Response;

class LogsController extends Controller
{
    public function laravel(): Response
    {
        return $this->tailAsText(storage_path('logs/laravel.log'), 500);
    }

    public function bitrix24(): Response
    {
        return $this->tailAsText(storage_path('logs/bitrix24.log'), 500);
    }

    protected function tailAsText(string $path, int $lines): Response
    {
        if (!is_file($path)) {
            return response("Log file not found: {$path}\n", 404)->header('Content-Type', 'text/plain; charset=utf-8');
        }

        $content = $this->tailFile($path, $lines);

        return response($content, 200)->header('Content-Type', 'text/plain; charset=utf-8');
    }

    /**
     * Простой tail последних N строк (без загрузки всего файла).
     */
    protected function tailFile(string $path, int $lines = 500, int $buffer = 8192): string
    {
        $lines = max(1, $lines);

        $fp = fopen($path, 'rb');
        if ($fp === false) {
            return "Failed to open log file: {$path}\n";
        }

        fseek($fp, 0, SEEK_END);
        $pos = ftell($fp);
        $data = '';

        while ($pos > 0 && substr_count($data, "\n") <= $lines) {
            $readSize = min($buffer, $pos);
            $pos -= $readSize;
            fseek($fp, $pos);

            $chunk = fread($fp, $readSize);
            if ($chunk === false || $chunk === '') {
                break;
            }

            $data = $chunk . $data;
        }

        fclose($fp);

        $rows = preg_split("/\r\n|\n|\r/", $data) ?: [];
        $tail = array_slice($rows, -$lines);

        return implode("\n", $tail) . "\n";
    }
}


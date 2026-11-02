<?php
declare(strict_types=1);

namespace App\Integrations;

class CsvParser {
    /**
     * Detecta o delimitador do CSV lendo as primeiras linhas
     */
    public static function detectDelimiter(string $filePath): string {
        $delimiters = [';', ',', "\t", '|'];
        $handle = fopen($filePath, 'r');
        if (!$handle) return ';';

        $firstLine = fgets($handle);
        fclose($handle);

        if (!$firstLine) return ';';

        $bestDelimiter = ';';
        $maxCount = 0;
        foreach ($delimiters as $delim) {
            $count = substr_count($firstLine, $delim);
            if ($count > $maxCount) {
                $maxCount = $count;
                $bestDelimiter = $delim;
            }
        }

        return $bestDelimiter;
    }

    /**
     * Itera sobre as linhas de um CSV grande de forma eficiente (streaming memory-safe)
     */
    public static function parse(string $filePath, callable $rowCallback, ?string $delimiter = null): array {
        if (!file_exists($filePath)) {
            return ['success' => false, 'error' => 'Arquivo CSV não encontrado: ' . $filePath];
        }

        $delimiter = $delimiter ?? self::detectDelimiter($filePath);
        $handle = fopen($filePath, 'r');
        if (!$handle) {
            return ['success' => false, 'error' => 'Não foi possível abrir o arquivo CSV para leitura.'];
        }

        $header = null;
        $processed = 0;
        $errors = 0;

        while (($rawRow = fgets($handle)) !== false) {
            // Conversão de charset ISO-8859-1 para UTF-8 se necessário
            if (!mb_check_encoding($rawRow, 'UTF-8')) {
                $rawRow = mb_convert_encoding($rawRow, 'UTF-8', 'ISO-8859-1');
            }

            $row = str_getcsv($rawRow, $delimiter);
            if (!$row || (count($row) === 1 && $row[0] === null)) {
                continue;
            }

            if ($header === null) {
                // Limpar BOM UTF-8 do cabeçalho
                if (isset($row[0])) {
                    $row[0] = preg_replace('/^\xEF\xBB\xBF/', '', $row[0]);
                }
                $header = array_map(fn($col) => trim(mb_strtolower((string)$col, 'UTF-8')), $row);
                continue;
            }

            if (count($row) !== count($header)) {
                // Tenta alinhar se houver diferença menor
                if (count($row) > count($header)) {
                    $row = array_slice($row, 0, count($header));
                } else {
                    $row = array_pad($row, count($header), '');
                }
            }

            $namedRow = array_combine($header, array_map('trim', $row));
            try {
                $continue = $rowCallback($namedRow, $processed);
                $processed++;
                if ($continue === false) {
                    break;
                }
            } catch (\Throwable $e) {
                $errors++;
            }
        }

        fclose($handle);

        return [
            'success' => true,
            'processed' => $processed,
            'errors' => $errors
        ];
    }

    /**
     * Normaliza valor monetário em texto (ex: "1.250,50" ou "1250.50" ou "R$ 1.250,50")
     */
    public static function parseMoney(mixed $val): float {
        if ($val === null || $val === '') return 0.00;
        $str = preg_replace('/[^\d,\.\-]/', '', (string)$val);
        if (str_contains($str, ',') && str_contains($str, '.')) {
            // Padrão brasileiro 1.250,50 -> 1250.50
            $str = str_replace('.', '', $str);
            $str = str_replace(',', '.', $str);
        } elseif (str_contains($str, ',')) {
            $str = str_replace(',', '.', $str);
        }
        return (float)$str;
    }

    /**
     * Normaliza data em formatos brasileiros comuns para YYYY-MM-DD
     */
    public static function parseDate(?string $val): ?string {
        if (!$val) return null;
        $val = trim($val);
        // DD/MM/AAAA
        if (preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})/', $val, $m)) {
            return sprintf('%04d-%02d-%02d', (int)$m[3], (int)$m[2], (int)$m[1]);
        }
        // AAAA-MM-DD
        if (preg_match('/^(\d{4})-(\d{1,2})-(\d{1,2})/', $val, $m)) {
            return sprintf('%04d-%02d-%02d', (int)$m[1], (int)$m[2], (int)$m[3]);
        }
        return null;
    }
}

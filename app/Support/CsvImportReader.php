<?php

namespace App\Support;

class CsvImportReader
{
    /**
     * Baca file CSV (delimiter ; atau ,) menjadi daftar baris.
     * Header dinormalisasi (huruf kecil, spasi/strip jadi underscore) dan alias dipetakan.
     * Nomor baris mengikuti nomor baris di spreadsheet (header = baris 1).
     *
     * @param  array<string,string>  $alias  header alternatif => header baku
     * @return list<array{baris:int,data:array<string,string>}>
     */
    public static function read(string $path, array $alias = []): array
    {
        $content = (string) file_get_contents($path);
        $content = preg_replace('/^\xEF\xBB\xBF/', '', $content);
        $delimiter = str_contains($content, ';') ? ';' : ',';

        $lines = preg_split('/\r?\n/', trim($content));
        $headers = array_map(function ($header) use ($alias) {
            $header = preg_replace('/[\s\-]+/', '_', strtolower(trim($header)));

            return $alias[$header] ?? $header;
        }, str_getcsv((string) array_shift($lines), $delimiter));

        $rows = [];
        $baris = 1;

        foreach ($lines as $line) {
            if (trim($line) === '') {
                continue;
            }

            $baris++;
            $values = array_pad(array_map('trim', str_getcsv($line, $delimiter)), count($headers), '');

            $rows[] = [
                'baris' => $baris,
                'data' => array_combine($headers, array_slice($values, 0, count($headers))),
            ];
        }

        return $rows;
    }
}
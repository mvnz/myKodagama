<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$outputCss = $root . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'css' . DIRECTORY_SEPARATOR . 'pages' . DIRECTORY_SEPARATOR . 'inline-extracted.css';

$gitFilesRaw = shell_exec('git ls-files "resources/views/**/*.blade.php"');
if (!is_string($gitFilesRaw) || trim($gitFilesRaw) === '') {
    fwrite(STDERR, "No tracked blade files found.\n");
    exit(1);
}

$files = array_values(array_filter(array_map('trim', explode("\n", $gitFilesRaw))));
$styleToClass = [];
$styleBlocks = [];

$normalizeStyle = static function (string $styleRaw): ?string {
    $parts = preg_split('/;/', trim($styleRaw));
    $cleanParts = [];

    foreach ($parts as $part) {
        $decl = trim($part);
        if ($decl === '') {
            continue;
        }

        $decl = preg_replace('/\s*:\s*/', ':', $decl);
        $decl = preg_replace('/\s+/', ' ', $decl);
        $cleanParts[] = $decl;
    }

    if (count($cleanParts) === 0) {
        return null;
    }

    return implode(';', $cleanParts);
};

$collectFromContent = static function (string $content) use (&$styleToClass, &$styleBlocks, $normalizeStyle): void {
    if (preg_match_all('/\sstyle=("|\')(.*?)\1/is', $content, $matches)) {
        foreach ($matches[2] as $styleRaw) {
            $style = $normalizeStyle((string) $styleRaw);
            if ($style === null) {
                continue;
            }

            $className = 'is-' . substr(md5($style), 0, 8);
            $styleToClass[$className] = rtrim($style, ';') . ';';
        }
    }

    if (preg_match_all('/<style\b[^>]*>(.*?)<\/style>/is', $content, $blocks)) {
        foreach ($blocks[1] as $css) {
            $css = trim((string) $css);
            if ($css === '') {
                continue;
            }

            if (preg_match('/\{\{|@php|@if|@foreach|@for|@switch|@unless|@env|@include|@extends|@section|@yield|@stack|@push|<\?php/i', $css)) {
                continue;
            }

            $styleBlocks[$css] = true;
        }
    }
};

foreach ($files as $file) {
    $absolutePath = $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $file);
    if (is_file($absolutePath)) {
        $current = file_get_contents($absolutePath);
        if (is_string($current)) {
            $collectFromContent($current);
        }
    }

    $head = shell_exec('git show HEAD:' . escapeshellarg($file));
    if (is_string($head) && $head !== '') {
        $collectFromContent($head);
    }
}

ksort($styleToClass);
$cssOut = "/* Auto-generated from inline style extraction. */\n";
$cssOut .= "/* Rebuilt from tracked Blade sources and HEAD snapshot. */\n\n";

foreach ($styleToClass as $className => $decl) {
    $cssOut .= '.' . $className . '{' . $decl . "}\n";
}

if (!empty($styleBlocks)) {
    $cssOut .= "\n/* Extracted <style> blocks */\n\n";
    $cssOut .= implode("\n\n", array_keys($styleBlocks)) . "\n";
}

file_put_contents($outputCss, $cssOut);

echo json_encode([
    'classes' => count($styleToClass),
    'style_blocks' => count($styleBlocks),
    'output' => str_replace('\\', '/', $outputCss),
], JSON_PRETTY_PRINT) . PHP_EOL;

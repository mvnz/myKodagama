<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$viewsRoot = $root . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . 'views';
$outputCss = $root . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'css' . DIRECTORY_SEPARATOR . 'pages' . DIRECTORY_SEPARATOR . 'inline-extracted.css';

$styleToClass = [];
$extractedCssBlocks = [];
$changedFiles = [];
$skippedFiles = [];

$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($viewsRoot));

foreach ($iterator as $fileInfo) {
    if (!$fileInfo->isFile()) {
        continue;
    }

    $path = $fileInfo->getPathname();
    if (substr($path, -10) !== '.blade.php') {
        continue;
    }

    $relativePath = str_replace($root . DIRECTORY_SEPARATOR, '', $path);

    $normalizedRelative = str_replace('\\\\', '/', $relativePath);
    if (
        str_ends_with($normalizedRelative, 'resources/views/welcome.blade.php') ||
        str_starts_with($normalizedRelative, 'resources/views/mail/') ||
        str_starts_with($normalizedRelative, 'resources/views/vendor/')
    ) {
        continue;
    }

    $content = file_get_contents($path);
    if ($content === false) {
        continue;
    }

    $original = $content;

    $content = preg_replace_callback(
        '~<style\\b[^>]*>(.*?)</style>~is',
        function (array $m) use (&$extractedCssBlocks, &$skippedFiles, $relativePath): string {
            $css = trim($m[1]);
            if ($css === '') {
                return '';
            }

            if (preg_match('/\\{\\{|@php|@if|@foreach|@for|@switch|@unless|@env|@include|@extends|@section|@yield|@stack|@push|<\\?php/i', $css)) {
                $skippedFiles[$relativePath] = true;
                return $m[0];
            }

            $extractedCssBlocks[] = "/* source: {$relativePath} */\n" . $css;
            return '';
        },
        $content
    );

    $content = preg_replace_callback(
        '~<([a-zA-Z][^>]*?)\\sstyle=("|\')(.*?)\\2([^>]*)>~is',
        function (array $m) use (&$styleToClass): string {
            $left = $m[1];
            $styleRaw = trim($m[3]);
            $right = $m[4];

            $parts = preg_split('/;/', $styleRaw);
            $cleanParts = [];

            foreach ($parts as $part) {
                $decl = trim($part);
                if ($decl === '') {
                    continue;
                }
                $decl = preg_replace('/\\s*:\\s*/', ':', $decl);
                $decl = preg_replace('/\\s+/', ' ', $decl);
                $cleanParts[] = $decl;
            }

            if (count($cleanParts) === 0) {
                return '<' . $left . $right . '>';
            }

            $style = implode(';', $cleanParts);

            if (!isset($styleToClass[$style])) {
                $styleToClass[$style] = 'is-' . substr(md5($style), 0, 8);
            }

            $className = $styleToClass[$style];
            $attrs = $left . $right;

            if (preg_match('/\\bclass=("|\')(.*?)\\1/is', $attrs, $cm)) {
                $quote = $cm[1];
                $existing = trim($cm[2]);
                $newClass = $existing === '' ? $className : ($existing . ' ' . $className);
                $replacement = 'class=' . $quote . $newClass . $quote;
                $attrs = preg_replace('/\\bclass=("|\')(.*?)\\1/is', $replacement, $attrs, 1);
            } else {
                $attrs .= ' class="' . $className . '"';
            }

            return '<' . $attrs . '>';
        },
        $content
    );

    if (
        str_contains($content, '<head') &&
        !str_contains($content, "asset('css/pages/inline-extracted.css')")
    ) {
        if (str_contains($content, "asset('css/koperasi.css')")) {
            $content = str_replace(
                "<link rel=\"stylesheet\" href=\"{{ asset('css/koperasi.css') }}\">",
                "<link rel=\"stylesheet\" href=\"{{ asset('css/koperasi.css') }}\">\n  <link rel=\"stylesheet\" href=\"{{ asset('css/pages/inline-extracted.css') }}\">",
                $content
            );
        } elseif (str_contains($content, '</head>')) {
            $content = str_replace(
                '</head>',
                "  <link rel=\"stylesheet\" href=\"{{ asset('css/pages/inline-extracted.css') }}\">\n</head>",
                $content
            );
        }
    }

    if ($content !== $original) {
        file_put_contents($path, $content);
        $changedFiles[] = str_replace('\\', '/', $relativePath);
    }
}

ksort($styleToClass);

$cssOut = "/* Auto-generated from inline style extraction. */\n";
$cssOut .= "/* Do not edit manually; update source Blade templates instead. */\n\n";

foreach ($styleToClass as $style => $className) {
    $decl = rtrim($style, ';') . ';';
    $cssOut .= '.' . $className . '{' . $decl . "}\n";
}

if (!empty($extractedCssBlocks)) {
    $cssOut .= "\n/* Extracted <style> blocks */\n\n";
    $cssOut .= implode("\n\n", $extractedCssBlocks) . "\n";
}

file_put_contents($outputCss, $cssOut);

$report = [
    'changed_files' => count($changedFiles),
    'unique_inline_styles' => count($styleToClass),
    'extracted_style_blocks' => count($extractedCssBlocks),
    'skipped_dynamic_style_blocks' => array_values(array_keys($skippedFiles)),
];

echo json_encode($report, JSON_PRETTY_PRINT) . PHP_EOL;

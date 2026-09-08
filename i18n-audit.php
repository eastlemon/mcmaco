<?php
// Аудит переводов: ключи __() в views vs словари lang/{ru,en}
$used = [];
$files = [];
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator('resources/views'));
foreach ($iterator as $file) {
    if ($file->getExtension() !== 'php' || ! str_ends_with($file->getFilename(), '.blade.php')) continue;
    $content = file_get_contents($file->getPathname());
    preg_match_all('/__\(\s*[\'"]([a-z0-9_.]+)[\'"]/', $content, $m);
    foreach ($m[1] as $key) {
        $used[$key] = true;
        $files[$key][] = basename($file->getPathname());
    }
}

$dict = [];
foreach (['ru', 'en'] as $loc) {
    foreach (glob("lang/{$loc}/*.php") as $f) {
        $prefix = basename($f, '.php');
        $arr = include $f;
        if (!is_array($arr)) continue;
        foreach ($arr as $k => $v) {
            if (is_array($v)) {
                foreach ($v as $kk => $vv) {
                    $dict[$loc][$prefix . '.' . $k . '.' . $kk] = $vv;
                }
                continue;
            }
            $dict[$loc][$prefix . '.' . $k] = $v;
        }
    }
}

echo 'USED: ' . count($used) . ' keys, dict ru: ' . count($dict['ru']) . ', en: ' . count($dict['en']) . PHP_EOL . PHP_EOL;

foreach (['ru', 'en'] as $loc) {
    $missing = array_diff_key($used, $dict[$loc] ?? []);
    echo "== MISSING in {$loc}: " . count($missing) . PHP_EOL;
    foreach (array_keys($missing) as $key) {
        echo '  ' . $key . '  <- ' . implode(', ', array_slice($files[$key], 0, 3)) . PHP_EOL;
    }
    echo PHP_EOL;
}

// Дубли/пустые значения
foreach (['ru', 'en'] as $loc) {
    $empty = array_filter($dict[$loc] ?? [], fn ($v) => trim((string) $v) === '');
    if ($empty) {
        echo "== EMPTY in {$loc}: " . implode(', ', array_keys($empty)) . PHP_EOL;
    }
}
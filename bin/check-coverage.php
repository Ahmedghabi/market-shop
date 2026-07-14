<?php

declare(strict_types=1);

if ($argc < 3) {
    fwrite(STDERR, "Usage: php bin/check-coverage.php <clover.xml> <minimum-percent>\n");
    exit(2);
}

$coverageFile = $argv[1];
$minimum = (float) $argv[2];

if (!is_file($coverageFile)) {
    fwrite(STDERR, sprintf("Coverage file not found: %s\n", $coverageFile));
    exit(2);
}

$xml = simplexml_load_file($coverageFile);
if (false === $xml || !isset($xml->project->metrics['statements'], $xml->project->metrics['coveredstatements'])) {
    fwrite(STDERR, "Coverage file does not contain project statement metrics.\n");
    exit(2);
}

$statements = (int) $xml->project->metrics['statements'];
$coveredStatements = (int) $xml->project->metrics['coveredstatements'];
$percentage = $statements > 0 ? ($coveredStatements / $statements) * 100 : 0.0;

printf("Line coverage: %.2f%% (%d/%d), minimum: %.2f%%\n", $percentage, $coveredStatements, $statements, $minimum);

if ($percentage < $minimum) {
    fwrite(STDERR, "Coverage gate failed.\n");
    exit(1);
}

fwrite(STDOUT, "Coverage gate passed.\n");

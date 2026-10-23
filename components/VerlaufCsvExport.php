<?php

declare(strict_types=1);

namespace app\components;

use app\models\BestandBewegung;
use yii\db\ActiveQuery;

/**
 * Writes the stock history as an Excel-friendly CSV (UTF-8 BOM, ';' separator).
 *
 * - rows are chronological (oldest first) so the file reads like a ledger
 * - streamed in batches into a temp stream (spills to disk), so large histories
 *   don't exhaust memory
 * - text cells are protected against CSV/formula injection
 */
final class VerlaufCsvExport
{
    public const DELIMITER = ';';

    private const HEADER = [
        'Buchungs-ID',
        'Datum',
        'Uhrzeit',
        'Art',
        'Produkt-ID',
        'Produkt',
        'Barcode',
        'Standort',
        'Menge',
        'Bestand vorher',
        'Bestand nachher',
        'Benutzer-ID',
        'Benutzer',
        'E-Mail',
    ];

    /**
     * @return array{stream: resource, rows: int}
     */
    public static function write(ActiveQuery $query): array
    {
        $query = (clone $query)->orderBy(['bb.gebucht_am' => SORT_ASC, 'bb.id' => SORT_ASC]);

        $fh = fopen('php://temp/maxmemory:' . (2 * 1024 * 1024), 'w+b');
        fwrite($fh, "\xEF\xBB\xBF"); // BOM: Excel detects UTF-8 (umlauts)
        self::put($fh, self::HEADER);

        $rows = 0;
        foreach ($query->each(500) as $m) {
            /** @var BestandBewegung $m */
            $ts = strtotime((string) $m->gebucht_am);
            $p = $m->produkt;
            $b = $m->benutzer;

            self::put($fh, [
                (int) $m->id,
                date('d.m.Y', $ts),
                date('H:i:s', $ts),
                $m->delta > 0 ? 'Zugang' : 'Entnahme',
                (int) $m->produkt_id,
                self::text($p->name ?? ''),
                self::text($p->barcode ?? ''),
                self::text($p->standort ?? ''),
                (int) $m->delta,
                (int) $m->bestand_nach - (int) $m->delta,
                (int) $m->bestand_nach,
                (int) $m->benutzer_id,
                self::text($b ? trim($b->firstname . ' ' . $b->lastname) : ''),
                self::text($b->email ?? ''),
            ]);
            $rows++;
        }

        rewind($fh);

        return ['stream' => $fh, 'rows' => $rows];
    }

    /**
     * Neutralises cells that spreadsheet apps would evaluate as formulas
     * (OWASP "CSV injection"). Numbers are written as ints and never pass here.
     */
    public static function text(string $value): string
    {
        $value = str_replace(["\r\n", "\r", "\n"], ' ', $value);

        if ($value !== '' && in_array($value[0], ['=', '+', '-', '@', "\t", '|', '%'], true)) {
            return "'" . $value;
        }

        return $value;
    }

    /**
     * @param resource $fh
     * @param array<int, string|int> $fields
     */
    private static function put($fh, array $fields): void
    {
        fputcsv($fh, $fields, self::DELIMITER, '"', '');
        // fputcsv ends lines with "\n"; Excel on Windows is happier with CRLF
        fseek($fh, -1, SEEK_CUR);
        fwrite($fh, "\r\n");
    }
}

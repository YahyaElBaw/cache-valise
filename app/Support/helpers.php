<?php

if (! function_exists('money')) {
    /**
     * Format a decimal amount in Tunisian Dinars (3 decimal places).
     */
    function money(string|int|float|null $amount): string
    {
        $normalized = bcadd((string) ($amount ?? '0'), '0', 3);
        $negative = str_starts_with($normalized, '-');
        $unsigned = $negative ? substr($normalized, 1) : $normalized;

        [$intPart, $decPart] = explode('.', $unsigned);
        $formattedInt = number_format((int) $intPart, 0, '', ' ');

        return ($negative ? '-' : '') . $formattedInt . ',' . $decPart . ' DT';
    }
}

<?php

namespace App\Helpers;

class Pagination
{
    /**
     * Calculate metadata for pagination
     *
     * @param integer $totalRecords
     * @param integer $page
     * @param integer $pageSize
     * @return array
     */
    public static function calculateMetadata(int $totalRecords, int $page, int $pageSize): array
    {
        if ($totalRecords === 0) {
            return [];
        }

        return [
            'current_page' => $page,
            'page_size' => $pageSize,
            'first_page' => 1,
            'last_page' => (int) ceil($totalRecords / $pageSize),
            'total_records' => $totalRecords
        ];
    }
}

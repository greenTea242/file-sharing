<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Пагинация
 */
readonly class Pagination
{
    private const int MIN_PAGES = 1;

    public int $currentPage;
    public int $recordsPerPage;
    private int $totalRecords;    
    public ?int $previousPage;
    public ?int $nextPage;

    /**
     * Конструктор
     *
     * @param integer $totalRecords
     * @param integer $currentPage
     * @param integer $recordsPerPage
     */
    public function __construct(int $totalRecords, int $currentPage, int $recordsPerPage = 15)
    {
        $this->totalRecords = $totalRecords;
        $this->recordsPerPage = $recordsPerPage;                
        $this->currentPage = $this->normalizeCurrentPage($currentPage);
        $this->previousPage = $this->getPreviousPage();
        $this->nextPage = $this->getNextPage();
    }

    /**
     * Получить количество страниц
     *
     * @return integer
     */
    public function getTotalPages(): int
    {
        return (int)(ceil($this->totalRecords / $this->recordsPerPage));
    }

    /**
     * Получить смещение
     *
     * @return integer
     */
    public function getOffset(): int
    {
        return ($this->currentPage - 1) * $this->recordsPerPage;
    }

    /**
     * Получить предыдущую странциу
     *
     * @return integer|null
     */
    private function getPreviousPage(): ?int {
        return ($this->currentPage > 1) ? ($this->currentPage - 1)  : null;
    }

    /**
     * Получить следующую страницу
     *
     * @return integer|null
     */
    private function getNextPage(): ?int {
        return ($this->currentPage < $this->getTotalPages()) ? ($this->currentPage + 1)  : null;
    }

    /**
     * Получить диапазон количества страниц
     *
     * @return array<int, int>
     */
    public function getTotalPagesRange(): array {
        $range = [];
        for ($page = $this->currentPage - 2; $page <= $this->currentPage + 2; $page++) {
            if ($this->isValid($page)) {
                $range[] = $page;
            }
        }
        return $range;
    }

    /**
     * Страница валидна?
     *
     * @param integer $page
     * @return boolean
     */    
    private function isValid(int $page): bool {
        return ($page >= self::MIN_PAGES) && ($page <= $this->getTotalPages());
    }
    
    /**
     * Нормализировать текущую страницу
     *
     * @param integer $currentPage
     * @return integer
     */
    private function normalizeCurrentPage(int $currentPage): int {
        $totalPages = $this->getTotalPages();
        if ($currentPage > $totalPages &&
            $totalPages > 0) {
            return $totalPages;
        } elseif ($currentPage < self::MIN_PAGES) {
            return self::MIN_PAGES;
        } else {
            return $currentPage;
        }
    }
}

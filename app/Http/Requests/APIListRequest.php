<?php

namespace App\Http\Requests;

class APIListRequest extends APIRequest
{

    /**
     * Get filters list.
     *
     * @return  array
     */
    public function filters(): array
    {
        return $this->input('filters', []);
    }

    /**
     * Get search terms.
     *
     * @return  array
     */
    public function search(): array
    {
        $search = explode(' ', $this->input('search'));

        return array_map(function ($term) {
            return trim($term);
        }, $search);
    }

    /**
     * Get search terms.
     *
     * @param string $default
     *
     * @return  string
     */
    public function order(string $default = 'asc'): string
    {
        $order = strtolower($this->input('$order'));

        return in_array($order, ['asc', 'desc']) ? $order : $default;
    }

    /**
     * Get order by parameter.
     *
     * @param string|null $default
     *
     * @return  string|null
     */
    public function order_by(?string $default = null): ?string
    {
        return $this->input('order_by', $default);
    }

    /**
     * Get requested page.
     *
     * @return  int
     */
    public function page(): int
    {
        return $this->input('page', 1);
    }

    /**
     * Get requested number of items page.
     *
     * @return  int
     */
    public function perPage(): int
    {
        return $this->input('per_page', 10);
    }
}

<?php

namespace App\Http\Requests;

use Exception;

class APIListRequest extends APIRequest
{
    /** @var array Values must be remembered in cookies */
    protected array $toRemember = [];

    /**
     * Weather initial list request.
     *
     * @return  bool
     */
    public function isInitial(): bool
    {
        return (bool)$this->input('initial', false);
    }

    /**
     * Get filters list.
     *
     * @param array $default
     * @param array $remember
     * @param string|null $key
     *
     * @return  array
     */
    public function filters(array $default = [], array $remember = [], ?string $key = null): array
    {
        if ($this->isInitial()) {
            return $this->getRememberedFilters($key, $remember, $default);
        }

        return $this->withRememberFilters($this->input('filters', []), $remember, $key);
    }

    /**
     * Get search terms.
     *
     * @return  array
     */
    public function search(): array
    {
        $search = $this->input('search');

        if(empty($search)) {
            return [];
        }

        $search = explode(' ', $search);

        return array_map(function ($term) {
            return trim($term);
        }, $search);
    }

    /**
     * Get search fields.
     *
     * @param array $default
     *
     * @return  array
     */
    public function searchBy(array $default = []): array
    {
        return $this->input('search_by', $default);
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
     * @param int $default
     * @param string|null $key
     *
     * @return  int
     */
    public function perPage(int $default = 10, ?string $key = null): int
    {
        if ($this->isInitial()) {
            return $this->getRememberedPerPage($key, $default);
        }

        return $this->withRememberPerPage($this->input('per_page', $default), $key);
    }


    /**
     * Get values to remember in response cookies.
     *
     * @return  string|null
     */
    public function getToRemember(): ?string
    {
        return empty($this->toRemember) ? null : json_encode($this->toRemember);
    }

    /**
     * Get filters from cookies.
     *
     * @param string|null $key
     * @param array|bool $remember
     * @param mixed $default
     *
     * @return  array
     */
    protected function getRememberedFilters(?string $key, array $remember, array $default): array
    {
        if ($key !== null && !empty($remember) && $this->hasCookie($key)) {
            try {
                $filters = json_decode($this->cookie($key), true);
                $filters = array_intersect_key($filters["{$key}_filter"], array_flip($remember));
            } catch (Exception $exception) {
                $filters = $default;
            }

            $this->toRemember["{$key}_filter"] = $filters;

            return $filters;
        }

        return $default;
    }

    /**
     * Get list filters from request and remember it when canned.
     *
     * @param array $filters
     * @param array|bool $remember
     * @param string|null $key
     *
     * @return  void
     */
    protected function withRememberFilters(array $filters, array $remember, ?string $key): array
    {
        if ($key !== null && !empty($remember)) {
            $this->toRemember["{$key}_filter"] = array_intersect_key($filters, array_flip($remember));
        }

        return $filters;
    }

    /**
     * Get per page parameter from cookies.
     *
     * @param string|null $key
     * @param mixed $default
     *
     * @return  int
     */
    protected function getRememberedPerPage(?string $key, int $default): int
    {
        if ($key !== null && $this->hasCookie($key)) {
            try {
                $perPage = json_decode($this->cookie($key), true);
                $perPage = (int)$perPage["{$key}_per_page"];
            } catch (Exception $exception) {
                $perPage = $default;
            }

            $this->toRemember["{$key}_per_page"] = $perPage;

            return $perPage;
        }

        return $default;
    }

    /**
     * Get per page parameter from request and remember it when canned.
     *
     * @param int $perPage
     * @param string|null $key
     *
     * @return  void
     */
    protected function withRememberPerPage(int $perPage, ?string $key): int
    {
        if ($key !== null) {
            $this->toRemember["{$key}_per_page"] = $perPage;
        }

        return $perPage;
    }
}

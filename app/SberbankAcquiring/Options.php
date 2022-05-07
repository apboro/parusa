<?php

namespace App\SberbankAcquiring;

use InvalidArgumentException;

class Options
{
    /** @var int|null Currency code in ISO 4217 format. */
    private ?int $currency = null;

    /** @var string|null A language code in ISO 639-1 format ('en', 'ru' and etc.). */
    private ?string $language = null;

    /** @var string Date format */
    private string $dateFormat = 'YmdHis';

    /**
     * Constructor.
     *
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        $allowed = [
            'currency',
            'language',
        ];

        $unknown = array_diff(array_keys($options), $allowed);

        if (!empty($unknown)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Unknown options [%s]. Allowed options: [%s].',
                    implode(', ', $unknown), implode(', ', $allowed)
                )
            );
        }

        $this->language = $options['language'] ?? null;
        $this->currency = $options['currency'] ?? null;
    }

    /**
     * Get currency.
     *
     * @return int
     */
    public function currency(): ?int
    {
        return $this->currency;
    }

    /**
     * Get language.
     *
     * @return string
     */
    public function language(): ?string
    {
        return $this->language;
    }
}

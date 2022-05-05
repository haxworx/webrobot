<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use App\Utils\FuzzyDateTime;

class AppExtension extends AbstractExtension
{
    public function getFilters()
    {
        return [
            new TwigFilter('fuzzy_date', [$this, 'fuzzyDate']),
        ];
    }

    public function fuzzyDate(\DateTime $dateTime): string
    {
        return FuzzyDateTime::get($dateTime);
    }
}

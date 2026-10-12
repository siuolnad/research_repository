<?php

declare(strict_types=1);

namespace App\Enums;

enum ResearchArea: string
{
    case Health = 'health';
    case Education = 'education';
    case Technology = 'technology';
    case Agriculture = 'agriculture';
    case Environment = 'environment';
    case SocialSciences = 'social_sciences';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Health => 'Health',
            self::Education => 'Education',
            self::Technology => 'Technology',
            self::Agriculture => 'Agriculture',
            self::Environment => 'Environment',
            self::SocialSciences => 'Social sciences',
            self::Other => 'Other',
        };
    }
}

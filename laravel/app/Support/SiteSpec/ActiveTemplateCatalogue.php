<?php

namespace App\Support\SiteSpec;

/**
 * Official template catalogue. Laravel is the only source for active_templates.
 *
 * The unused APPROVED_TEMPLATES list in the Design Agent is not this catalogue.
 */
final class ActiveTemplateCatalogue
{
    /**
     * @var list<string>
     */
    public const IDS = [
        'minimal-clean',
        'premium-luxury',
        'modern-commerce',
        'corporate-pro',
        'food-restaurant',
        'creative-tech',
    ];

    /**
     * @return list<string>
     */
    public static function ids(): array
    {
        return self::IDS;
    }

    public static function contains(string $id): bool
    {
        return in_array($id, self::IDS, true);
    }
}

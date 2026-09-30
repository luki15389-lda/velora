<?php

namespace Database\Seeders\Support;

use App\Models\Color;
use Illuminate\Support\Facades\Storage;

/**
 * Writes a real file into storage/app/public for every seeded product image, so
 * the demo catalogue exercises the same Storage::url() path that uploads use.
 *
 * There's no network access available to fetch real product photography, so
 * this draws a simple flat-lay style silhouette of the actual product type —
 * a t-shirt, a shoe, a bag, and so on — filled in the variant's real colour on
 * a neutral studio-style background. It reads far more like a product photo
 * than a plain colour swatch, even though it's still an illustration rather
 * than a photograph.
 */
class PlaceholderImage
{
    public static function make(string $productSlug, Color $color, string $type, string $label): string
    {
        $path = "products/{$productSlug}/{$color->slug}.svg";

        if (Storage::disk('public')->exists($path)) {
            return $path;
        }

        Storage::disk('public')->put($path, static::svg($color->hex, $type, $label, $color->name));

        return $path;
    }

    private static function svg(string $hex, string $type, string $label, string $colorName): string
    {
        $ink = static::isLight($hex) ? '#1C1C1C' : '#F7F4EF';
        $label = htmlspecialchars($label, ENT_QUOTES);
        $colorName = htmlspecialchars($colorName, ENT_QUOTES);
        $shape = static::shapeFor($type, $hex);

        return <<<SVG
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 800 1000" width="800" height="1000" role="img" aria-label="{$label} in {$colorName}">
          <rect width="800" height="1000" fill="#F3EEE6"/>
          <ellipse cx="400" cy="860" rx="220" ry="24" fill="#000000" opacity="0.05"/>
          <g transform="translate(400,500)">
            {$shape}
          </g>
          <text x="400" y="945" text-anchor="middle" fill="#8A7F72" font-family="Archivo, Helvetica, Arial, sans-serif" font-size="15" letter-spacing="3">{$label} — {$colorName}</text>
        </svg>
        SVG;
    }

    /** Silhouette centred on (0,0); every shape fits roughly a 520×560 box. */
    private static function shapeFor(string $type, string $hex): string
    {
        $stroke = static::isLight($hex) ? '#00000022' : '#00000033';
        $type = mb_strtolower($type);

        return match (true) {
            str_contains($type, 't-shirt') || str_contains($type, 'tshirt') => static::tshirt($hex, $stroke),
            str_contains($type, 'shirt') => static::shirt($hex, $stroke),
            str_contains($type, 'pants') || str_contains($type, 'bottom') => static::pants($hex, $stroke),
            str_contains($type, 'outerwear') || str_contains($type, 'jacket') || str_contains($type, 'hoodie') => static::jacket($hex, $stroke),
            str_contains($type, 'bra') => static::bra($hex, $stroke),
            str_contains($type, 'underwear') => static::underwear($hex, $stroke),
            str_contains($type, 'shoe') => static::shoe($hex, $stroke),
            str_contains($type, 'bag') => static::bag($hex, $stroke),
            default => static::tshirt($hex, $stroke),
        };
    }

    private static function tshirt(string $fill, string $stroke): string
    {
        return <<<SVG
        <path d="M-190,-230 L-90,-270 Q0,-220 90,-270 L190,-230 L230,-120 L150,-80 L150,240 L-150,240 L-150,-80 L-230,-120 Z"
              fill="{$fill}" stroke="{$stroke}" stroke-width="6" stroke-linejoin="round"/>
        <path d="M-90,-270 Q0,-220 90,-270 Q60,-205 0,-200 Q-60,-205 -90,-270 Z" fill="#00000014"/>
        SVG;
    }

    private static function shirt(string $fill, string $stroke): string
    {
        return <<<SVG
        <path d="M-190,-230 L-95,-275 L-50,-235 L0,-200 L50,-235 L95,-275 L190,-230 L225,-115 L150,-75 L150,260 L-150,260 L-150,-75 L-225,-115 Z"
              fill="{$fill}" stroke="{$stroke}" stroke-width="6" stroke-linejoin="round"/>
        <line x1="0" y1="-190" x2="0" y2="255" stroke="{$stroke}" stroke-width="4"/>
        <circle cx="0" cy="-120" r="6" fill="{$stroke}"/>
        <circle cx="0" cy="-40" r="6" fill="{$stroke}"/>
        <circle cx="0" cy="40" r="6" fill="{$stroke}"/>
        <circle cx="0" cy="120" r="6" fill="{$stroke}"/>
        <path d="M-50,-235 L0,-165 L50,-235" fill="none" stroke="{$stroke}" stroke-width="5"/>
        SVG;
    }

    private static function pants(string $fill, string $stroke): string
    {
        return <<<SVG
        <path d="M-140,-270 L140,-270 L155,260 L45,260 L15,-30 L-15,-30 L-45,260 L-155,260 Z"
              fill="{$fill}" stroke="{$stroke}" stroke-width="6" stroke-linejoin="round"/>
        <rect x="-140" y="-270" width="280" height="55" fill="#00000010"/>
        SVG;
    }

    private static function jacket(string $fill, string $stroke): string
    {
        return <<<SVG
        <path d="M-90,-275 L0,-235 L90,-275 L200,-225 L235,-95 L165,-55 L165,265 L-165,265 L-165,-55 L-235,-95 Z"
              fill="{$fill}" stroke="{$stroke}" stroke-width="6" stroke-linejoin="round"/>
        <line x1="0" y1="-215" x2="0" y2="260" stroke="{$stroke}" stroke-width="5"/>
        <path d="M-165,-55 L-90,-20 L-90,265" fill="none" stroke="{$stroke}" stroke-width="4" opacity="0.6"/>
        <path d="M165,-55 L90,-20 L90,265" fill="none" stroke="{$stroke}" stroke-width="4" opacity="0.6"/>
        SVG;
    }

    private static function bra(string $fill, string $stroke): string
    {
        return <<<SVG
        <path d="M-170,-40 Q-190,-140 -110,-170 Q-40,-190 0,-120 Q40,-190 110,-170 Q190,-140 170,-40 Q160,60 60,90 Q20,100 0,60 Q-20,100 -60,90 Q-160,60 -170,-40 Z"
              fill="{$fill}" stroke="{$stroke}" stroke-width="6" stroke-linejoin="round"/>
        <path d="M-100,-165 L-140,-260 M100,-165 L140,-260" stroke="{$stroke}" stroke-width="10" stroke-linecap="round" fill="none"/>
        SVG;
    }

    private static function underwear(string $fill, string $stroke): string
    {
        return <<<SVG
        <path d="M-190,-130 L190,-130 L175,20 Q175,110 90,140 Q40,155 0,90 Q-40,155 -90,140 Q-175,110 -175,20 Z"
              fill="{$fill}" stroke="{$stroke}" stroke-width="6" stroke-linejoin="round"/>
        <rect x="-190" y="-160" width="380" height="35" rx="14" fill="#00000012"/>
        SVG;
    }

    private static function shoe(string $fill, string $stroke): string
    {
        return <<<SVG
        <path d="M-230,60 Q-230,-10 -170,-40 L-70,-90 Q-10,-120 60,-95 L170,-55 Q230,-30 230,30 L230,80 Q230,120 190,120 L-190,120 Q-230,120 -230,80 Z"
              fill="{$fill}" stroke="{$stroke}" stroke-width="6" stroke-linejoin="round"/>
        <path d="M-170,-40 Q-100,-70 -30,-55 Q30,-40 60,-95" fill="none" stroke="{$stroke}" stroke-width="5" opacity="0.6"/>
        <rect x="-230" y="95" width="460" height="25" fill="#00000018"/>
        SVG;
    }

    private static function bag(string $fill, string $stroke): string
    {
        return <<<SVG
        <path d="M-160,-60 Q-160,-190 0,-190 Q160,-190 160,-60 L160,-60"
              fill="none" stroke="{$stroke}" stroke-width="18" stroke-linecap="round"/>
        <path d="M-210,-60 L210,-60 L235,215 Q235,250 195,250 L-195,250 Q-235,250 -235,215 Z"
              fill="{$fill}" stroke="{$stroke}" stroke-width="6" stroke-linejoin="round"/>
        <rect x="-235" y="-70" width="470" height="45" fill="#00000012"/>
        SVG;
    }

    private static function isLight(string $hex): bool
    {
        [$r, $g, $b] = sscanf(ltrim($hex, '#'), '%2x%2x%2x');

        return (($r * 299) + ($g * 587) + ($b * 114)) / 1000 > 150;
    }
}

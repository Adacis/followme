<?php
namespace OCA\Followme\Service;

final class CategoryCatalog {
    public const LABELS = [
        'News' => '📰 News', 'Vulnérabilité' => '🛑 Vulnérabilité', 'Menace' => '⚠️ Menace',
        'Protection des données' => '🔒 Protection des données', 'Outil' => '🔨 Outil',
        'Guide' => '📍 Guide', 'Vidéos' => '🎞 Vidéos',
        'Numérique Responsable' => '⌨️ Numérique Responsable', 'IA' => '🧠 IA',
    ];
    public const DEFAULTS = [
        'News', 'Vulnérabilité', 'Menace', 'Protection des données', 'Outil',
        'Guide', 'Vidéos', 'Numérique Responsable', 'IA',
    ];
}

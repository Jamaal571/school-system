@props(['name'])
@php
    // One place to control every icon. Each name lists Lucide candidates in order,
    // because Lucide renames icons between versions. The first one that exists is used.
    $icons = [
        'squares-2x2'              => ['layout-grid'],
        'academic-cap'             => ['graduation-cap'],
        'users'                    => ['users'],
        'book-open'                => ['book-open'],
        'calendar-days'            => ['calendar-check'],
        'calendar'                 => ['calendar'],
        'wallet'                   => ['wallet'],
        'clipboard-document-check' => ['clipboard-check'],
        'presentation-chart-line'  => ['chart-no-axes-combined', 'bar-chart-3', 'chart-column'],
        'chat-bubble-left-right'   => ['messages-square'],
        'question-mark-circle'     => ['circle-help', 'circle-question-mark', 'help-circle'],
        'cog-6-tooth'              => ['settings'],
        'chevron-up-down'          => ['chevrons-up-down'],
        'chevron-right'            => ['chevron-right'],
        'magnifying-glass'         => ['search'],
        'bell'                     => ['bell'],
        'bars-3'                   => ['menu'],
        'arrow-down-tray'          => ['download'],
        'arrow-right'              => ['arrow-right'],
        'arrow-up'                 => ['arrow-up'],
        'arrow-down'               => ['arrow-down'],
        'megaphone'                => ['megaphone'],
        'user-plus'                => ['user-plus'],
        'document-chart-bar'       => ['file-chart-column', 'file-bar-chart', 'file-text'],
        'shield-exclamation'       => ['shield-alert'],
        'check-circle'             => ['circle-check', 'check-circle-2', 'check-circle'],
    ];
    $icon = null;
    foreach ($icons[$name] ?? [$name] as $candidate) {
        try { $icon = svg('lucide-' . $candidate, $attributes->get('class'), ['stroke-width' => '1.75']); break; }
        catch (\Throwable $e) { /* try the next candidate */ }
    }
@endphp
{{ $icon }}

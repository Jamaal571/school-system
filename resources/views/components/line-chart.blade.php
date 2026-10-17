@props([
    'points', 'labels', 'yMin', 'yMax', 'ticks',
    'forecast' => [], 'suffix' => '', 'smooth' => false,
    'target' => null, 'tooltip' => null, 'forecastLabel' => 'Forecast',
    'width' => 840, 'height' => 250, 'label' => 'Line chart',
])
@php
    $W = $width; $H = $height; $L = 52; $R = 12; $T = 16; $B = 40;
    $plotW = $W - $L - $R; $plotH = $H - $T - $B; $n = count($labels);
    $x = fn ($i) => $L + $i * $plotW / max($n - 1, 1);
    $y = fn ($v) => $T + ($yMax - $v) / ($yMax - $yMin) * $plotH;

    $pts = [];
    foreach ($points as $i => $v) { $pts[] = [$x($i), $y($v)]; }
    $last = end($pts);

    // Smooth curve (Catmull-Rom converted to Bezier) or straight segments.
    $d = 'M' . round($pts[0][0], 1) . ' ' . round($pts[0][1], 1);
    for ($i = 0; $i < count($pts) - 1; $i++) {
        [$p1, $p2] = [$pts[$i], $pts[$i + 1]];
        if ($smooth) {
            $p0 = $pts[max($i - 1, 0)]; $p3 = $pts[min($i + 2, count($pts) - 1)];
            $d .= sprintf(' C%.1f %.1f,%.1f %.1f,%.1f %.1f',
                $p1[0] + ($p2[0] - $p0[0]) / 6, $p1[1] + ($p2[1] - $p0[1]) / 6,
                $p2[0] - ($p3[0] - $p1[0]) / 6, $p2[1] - ($p3[1] - $p1[1]) / 6,
                $p2[0], $p2[1]);
        } else {
            $d .= sprintf(' L%.1f %.1f', $p2[0], $p2[1]);
        }
    }

    $f = 'M' . round($last[0], 1) . ' ' . round($last[1], 1);
    foreach ($forecast as $k => $v) { $f .= sprintf(' L%.1f %.1f', $x(count($points) + $k), $y($v)); }
    $tw = $tooltip ? strlen($tooltip) * 7 + 24 : 0;
@endphp

<svg viewBox="0 0 {{ $W }} {{ $H }}" class="h-auto w-full" role="img" aria-label="{{ $label }}" font-family="inherit">
    @foreach ($ticks as $t)
        <line x1="{{ $L }}" x2="{{ $W - $R }}" y1="{{ $y($t) }}" y2="{{ $y($t) }}" stroke="#E6EBF2"/>
        <text x="{{ $L - 12 }}" y="{{ $y($t) + 4 }}" text-anchor="end" font-size="12" fill="#97A0AF">{{ number_format($t) . $suffix }}</text>
    @endforeach

    @if ($target !== null)
        <line x1="{{ $L }}" x2="{{ $W - $R }}" y1="{{ $y($target) }}" y2="{{ $y($target) }}" stroke="#A9BEF0" stroke-dasharray="5 4"/>
    @endif

    @if ($tooltip)
        <line x1="{{ $last[0] }}" x2="{{ $last[0] }}" y1="{{ $last[1] - 20 }}" y2="{{ $H - $B }}" stroke="#86A3DE" stroke-width="1.5"/>
        @if ($forecast)
            <text x="{{ $last[0] + 54 }}" y="{{ $T + $plotH * 0.74 }}" font-size="12" fill="#97A0AF">{{ $forecastLabel }}</text>
        @endif
    @endif

    @if ($forecast)
        <path d="{{ $f }}" fill="none" stroke="#285BC5" stroke-width="3" stroke-dasharray="6 4"/>
    @endif
    <path d="{{ $d }}" fill="none" stroke="#285BC5" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>

    @if ($tooltip)
        <circle cx="{{ $last[0] }}" cy="{{ $last[1] }}" r="4.5" fill="#285BC5"/>
        <rect x="{{ $last[0] - $tw / 2 }}" y="{{ $last[1] - 48 }}" width="{{ $tw }}" height="28" rx="6" fill="#285BC5"/>
        <text x="{{ $last[0] }}" y="{{ $last[1] - 29 }}" text-anchor="middle" font-size="12" font-weight="600" fill="#fff">{{ $tooltip }}</text>
    @endif

    @foreach ($labels as $i => $text)
        <text x="{{ $x($i) }}" y="{{ $H - 10 }}" font-size="12" fill="#97A0AF"
              text-anchor="{{ $i === 0 ? 'start' : ($i === $n - 1 ? 'end' : 'middle') }}">{{ $text }}</text>
    @endforeach
</svg>

@props(['groups', 'top', 'width' => 640, 'height' => 240, 'label' => 'Bar chart'])
@php
    $W = $width; $H = $height; $L = 50; $R = 10; $T = 24; $B = 32;
    $plotW = $W - $L - $R; $plotH = $H - $T - $B;
    $y = fn ($v) => $T + $plotH - ($top > 0 ? $v / $top * $plotH : 0);
    $k = fn ($v) => '$'.number_format($v / 1000, 1).'k';
    $slot = $plotW / max(count($groups), 1); $bw = min(46, $slot / 4);
@endphp
<svg viewBox="0 0 {{ $W }} {{ $H }}" class="h-auto w-full" role="img" aria-label="{{ $label }}" font-family="inherit">
    @foreach ([0, $top / 2, $top] as $t)
        <line x1="{{ $L }}" x2="{{ $W - $R }}" y1="{{ $y($t) }}" y2="{{ $y($t) }}" stroke="#E6EBF2"/>
        <text x="{{ $L - 8 }}" y="{{ $y($t) + 4 }}" text-anchor="end" font-size="11" fill="#97A0AF">{{ $t == 0 ? '$0' : '$'.number_format($t / 1000).'k' }}</text>
    @endforeach
    @foreach ($groups as $i => $g)
        @php $cx = $L + $slot * ($i + 0.5); @endphp
        <rect x="{{ $cx - $bw - 3 }}" y="{{ $y($g['a']) }}" width="{{ $bw }}" height="{{ max($H - $B - $y($g['a']), 0) }}" rx="3" fill="#285BC5"/>
        <rect x="{{ $cx + 3 }}" y="{{ $y($g['b']) }}" width="{{ $bw }}" height="{{ max($H - $B - $y($g['b']), 0) }}" rx="3" fill="#A9BEF0"/>
        <text x="{{ $cx - $bw / 2 - 3 }}" y="{{ $y($g['a']) - 6 }}" text-anchor="middle" font-size="11" fill="#526279">{{ $k($g['a']) }}</text>
        <text x="{{ $cx + $bw / 2 + 3 }}" y="{{ $y($g['b']) - 6 }}" text-anchor="middle" font-size="11" fill="#526279">{{ $k($g['b']) }}</text>
        <text x="{{ $cx }}" y="{{ $H - 10 }}" text-anchor="middle" font-size="12" fill="#97A0AF">{{ $g['label'] }}</text>
    @endforeach
</svg>

@props([
    'seat',
    'trip',
])

@php
    $num = $seat['number'];
    $code = $seat['code'] ?? sprintf('S-%02d', $num);
    $status = $seat['status'] ?? 'available';
    $isLocked = ($status === 'locked') || in_array($num, [1, 16], true);
    $isBooked = ($status === 'booked');
    $isAvailable = !$isLocked && !$isBooked;

    $displayNum = sprintf('%02d', $num);
    $posLabel = $seat['is_window'] ? __('Fenêtre') : ($seat['is_aisle'] ? __('Couloir') : __('Milieu'));
    $zoneLabel = __('Rangée :r • :z', [
        'r' => sprintf('%02d', $seat['row']),
        'z' => match($seat['zone'] ?? 'mid') {
            'front' => __('Avant'),
            'rear' => __('Arrière'),
            default => __('Milieu')
        }
    ]);

    $title = __('Fauteuil') . " {$displayNum} ({$posLabel})";
    $crewLabel = null;

    if ($num === 1) {
        $title = __('Siège 01 : Chauffeur Principal (Personnel Real Voyage)');
        $crewLabel = __('Chauffeur');
    } elseif ($num === 16) {
        $title = __('Siège 16 : Convoyeur / Sécurité (Personnel Real Voyage)');
        $crewLabel = __('Convoyeur');
    } elseif ($isBooked) {
        $title = __('Fauteuil :num : Déjà Réservé', ['num' => $displayNum]);
    }
@endphp

<div class="seat-armchair {{ $isLocked ? 'seat-state-locked' : ($isBooked ? 'seat-state-booked' : 'seat-state-available') }}"
     data-seat="{{ $code }}"
     data-num="{{ $num }}"
     data-pos="{{ $posLabel }}"
     data-zone="{{ $zoneLabel }}"
     data-locked="{{ $isLocked ? 'true' : 'false' }}"
     title="{{ $title }}"
     role="button"
     tabindex="{{ $isAvailable ? '0' : '-1' }}"
     aria-label="{{ $title }}"
     onmouseenter="showSeatInspector(event, this)"
     onmouseleave="hideSeatInspector()"
     @if($isAvailable)
         onclick="toggleVirtualSeat('{{ $code }}', this)"
         onkeydown="if(event.key==='Enter'||event.key===' '){event.preventDefault();toggleVirtualSeat('{{ $code }}', this);}"
     @endif>

    <!-- Headrest Cushion with Embossed Seat Number or Crew Tag -->
    <div class="armchair-headrest {{ $isLocked ? 'headrest-crew' : '' }}">
        @if($isLocked)
            <i class="fa-solid {{ $num === 1 ? 'fa-id-badge' : 'fa-user-shield' }}"></i>
        @else
            <span>{{ $displayNum }}</span>
        @endif
    </div>

    <!-- Backrest with Ergonomic Contours & Position Label -->
    <div class="armchair-backrest">
        @if($isLocked)
            <span class="crew-designation">{{ $crewLabel }}</span>
        @elseif($isBooked)
            <i class="fa-solid fa-lock" style="font-size: 0.75rem; color: #94A3B8;"></i>
        @else
            <span class="seat-number-tag">{{ $displayNum }}</span>
            <span class="seat-pos-tag">{{ $seat['is_window'] ? 'FEN' : ($seat['is_aisle'] ? 'COUL' : 'MIL') }}</span>
        @endif
    </div>

    <!-- Base Cushion with 3D Depth Shadow -->
    <div class="armchair-cushion"></div>
</div>

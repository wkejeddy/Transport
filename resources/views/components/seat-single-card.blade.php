@props(['seat'])

@php
    $num = $seat['number'];
    $code = $seat['code'] ?? sprintf('S-%02d', $num);
    $status = $seat['status'] ?? 'available';
    $isLocked = ($status === 'locked') || in_array($num, [1, 16]);
    $isBooked = ($status === 'booked');
    $isAvailable = !$isLocked && !$isBooked;

    $displayNum = sprintf('%02d', $num);
    $title = __('Siège') . " {$displayNum}";
    $crewLabel = null;

    if ($num === 1) {
        $title = __('Siège 01 : Chauffeur Principal (Personnel Real Voyage)');
        $crewLabel = __('Chauffeur');
    } elseif ($num === 16) {
        $title = __('Siège 16 : Convoyeur / Personnel de Bord (Personnel Real Voyage)');
        $crewLabel = __('Convoyeur');
    } elseif ($isBooked) {
        $title = __('Siège :num : Déjà Réservé', ['num' => $displayNum]);
    } else {
        $pos = $seat['is_window'] ? __('Fenêtre') : __('Couloir');
        $title = __('Siège :num (:pos) - Disponible', ['num' => $displayNum, 'pos' => $pos]);
    }
@endphp

<div class="seat-item {{ $isLocked ? 'seat-locked' : ($isBooked ? 'seat-booked' : 'seat-available') }}"
     data-seat="{{ $code }}"
     data-num="{{ $num }}"
     title="{{ $title }}"
     role="button"
     tabindex="{{ $isAvailable ? '0' : '-1' }}"
     aria-label="{{ $title }}"
     @if($isAvailable) onclick="toggleSeat('{{ $code }}', this)" onkeydown="if(event.key==='Enter'||event.key===' '){event.preventDefault();toggleSeat('{{ $code }}', this);}" @endif>
    
    <div class="seat-cushion {{ $isLocked ? 'seat-cushion-crew' : '' }}">
        @if($isLocked)
            <i class="fa-solid {{ $num === 1 ? 'fa-id-badge' : 'fa-user-shield' }} crew-icon"></i>
            <span class="seat-code">{{ $displayNum }}</span>
            <span class="crew-tag">{{ $crewLabel }}</span>
        @elseif($isBooked)
            <i class="fa-solid fa-lock booked-icon"></i>
            <span class="seat-code">{{ $displayNum }}</span>
        @else
            <span class="seat-code">{{ $displayNum }}</span>
            <span class="seat-sub-pos">
                {{ $seat['is_window'] ? __('Fen.') : __('Coul.') }}
            </span>
        @endif
    </div>
</div>

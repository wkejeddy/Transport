<template>
  <div class="seatmap-3d-vue-container">
    <!-- Header Controls -->
    <div class="seatmap-header flex justify-between items-center mb-4">
      <div>
        <h3 class="text-lg font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
          <i class="fa-solid fa-bus text-blue-600"></i>
          <span>Plan Cabine Autocar Real Voyage ({{ totalSeats }} places)</span>
        </h3>
        <p class="text-xs text-slate-500 dark:text-slate-400">
          Sièges 01 &amp; 16 strictement réservés à l'équipage Real Voyage (non sélectionnables).
        </p>
      </div>

      <div class="flex gap-2">
        <button
          type="button"
          @click="viewMode = '3d'"
          :class="['px-3 py-1.5 text-xs font-semibold rounded-lg transition-all', viewMode === '3d' ? 'bg-blue-600 text-white shadow-md' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300']"
        >
          <i class="fa-solid fa-cube mr-1"></i> Vue 3D
        </button>
        <button
          type="button"
          @click="viewMode = '2d'"
          :class="['px-3 py-1.5 text-xs font-semibold rounded-lg transition-all', viewMode === '2d' ? 'bg-blue-600 text-white shadow-md' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300']"
        >
          <i class="fa-solid fa-table-cells mr-1"></i> Vue 2D
        </button>
      </div>
    </div>

    <!-- Legend -->
    <div class="flex flex-wrap gap-4 items-center bg-slate-50 dark:bg-slate-800/60 p-3 rounded-lg border border-slate-200 dark:border-slate-700 text-xs mb-4">
      <div class="flex items-center gap-2">
        <span class="w-3 h-3 rounded-sm bg-white dark:bg-slate-700 border border-slate-300 dark:border-slate-600"></span>
        <span class="text-slate-600 dark:text-slate-300">Disponible</span>
      </div>
      <div class="flex items-center gap-2">
        <span class="w-3 h-3 rounded-sm bg-blue-600"></span>
        <span class="text-slate-600 dark:text-slate-300">Sélectionné</span>
      </div>
      <div class="flex items-center gap-2">
        <span class="w-3 h-3 rounded-sm bg-slate-300 dark:bg-slate-600"></span>
        <span class="text-slate-600 dark:text-slate-300">Occupé</span>
      </div>
      <div class="flex items-center gap-2">
        <span class="w-3 h-3 rounded-sm bg-amber-500"></span>
        <span class="font-bold text-amber-600 dark:text-amber-400">Équipage (01 &amp; 16)</span>
      </div>
    </div>

    <!-- Cabin Body -->
    <div :class="['coach-cabin-wrapper relative mx-auto p-6 rounded-2xl border-2 border-slate-300 dark:border-slate-700 bg-slate-100 dark:bg-slate-900', viewMode === '3d' ? 'perspective-3d' : '']">
      <!-- Cockpit Banner -->
      <div class="cockpit-front mb-6 pb-3 border-b-2 border-dashed border-slate-300 dark:border-slate-700 flex justify-between items-center text-xs font-bold text-slate-500 uppercase tracking-wider">
        <div class="flex items-center gap-2 text-amber-600 dark:text-amber-400">
          <i class="fa-solid fa-id-badge"></i>
          <span>Poste Chauffeur (01)</span>
        </div>
        <div class="flex items-center gap-1">
          <i class="fa-solid fa-arrow-up"></i>
          <span>Sens de marche</span>
        </div>
        <div class="text-slate-400">Porte Avant</div>
      </div>

      <!-- Rows Grid (3 left, aisle/center, 2 right + doors) -->
      <div class="space-y-3">
        <div
          v-for="row in rows"
          :key="row.rowNumber"
          class="flex justify-between items-center gap-2"
        >
          <!-- Left side (3 seats) -->
          <div class="flex gap-2">
            <template v-for="(seat, idx) in row.left" :key="seat ? seat.num : 'empty-l-' + idx">
              <button
                v-if="seat"
                type="button"
                :disabled="seat.isLocked || seat.isBooked"
                @click="toggleSeat(seat)"
                :class="getSeatClasses(seat)"
                :title="getSeatTooltip(seat)"
              >
                <i v-if="seat.isLocked" :class="seat.icon" class="text-xs"></i>
                <span v-else>{{ seat.code }}</span>
              </button>
              <div v-else class="w-9 h-10 invisible"></div>
            </template>
          </div>

          <!-- Central Aisle or Center Seat -->
          <div class="aisle w-9 text-center text-[10px] text-slate-400 font-mono select-none flex items-center justify-center">
            <button
              v-if="row.center"
              type="button"
              :disabled="row.center.isLocked || row.center.isBooked"
              @click="toggleSeat(row.center)"
              :class="getSeatClasses(row.center)"
              :title="getSeatTooltip(row.center)"
            >
              <i v-if="row.center.isLocked" :class="row.center.icon" class="text-xs"></i>
              <span v-else>{{ row.center.code }}</span>
            </button>
            <span v-else>{{ row.rowNumber }}</span>
          </div>

          <!-- Right side (2 seats or Door) -->
          <div class="flex gap-2">
            <div
              v-if="row.door"
              class="w-20 h-10 border border-dashed border-emerald-500 bg-emerald-50 dark:bg-emerald-950/30 text-emerald-600 dark:text-emerald-400 text-[10px] font-bold uppercase rounded flex items-center justify-center gap-1"
            >
              <i class="fa-solid fa-door-open"></i>
              <span>{{ row.door }}</span>
            </div>
            <template v-else>
              <template v-for="(seat, idx) in row.right" :key="seat ? seat.num : 'empty-r-' + idx">
                <button
                  v-if="seat"
                  type="button"
                  :disabled="seat.isLocked || seat.isBooked"
                  @click="toggleSeat(seat)"
                  :class="getSeatClasses(seat)"
                  :title="getSeatTooltip(seat)"
                >
                  <i v-if="seat.isLocked" :class="seat.icon" class="text-xs"></i>
                  <span v-else>{{ seat.code }}</span>
                </button>
                <div v-else class="w-9 h-10 invisible"></div>
              </template>
            </template>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';

const props = defineProps({
  totalSeats: {
    type: Number,
    default: 75,
  },
  bookedSeats: {
    type: Array,
    default: () => [],
  },
  selectedSeats: {
    type: Array,
    default: () => [],
  },
  maxSelection: {
    type: Number,
    default: 5,
  },
});

const emit = defineEmits(['update:selectedSeats', 'seat-clicked']);

const viewMode = ref('3d');

// Crew locked seats strictly enforced: 01 (Driver) & 16 (Assistant)
const CREW_LOCKS = {
  1: { role: 'Chauffeur Real Voyage', icon: 'fa-solid fa-id-badge' },
  16: { role: 'Convoyeur / Assistant', icon: 'fa-solid fa-user-shield' },
};

// Generate layout rows matching Carrefour blueprints (3 left, aisle, 2 right + doors)
const rows = computed(() => {
  const cap = props.totalSeats >= 78 ? 80 : 75;
  const specs = cap === 80 ? [
    { row: 1, left: [null, 1, null], right: [2, 3], door: null },
    { row: 2, left: [4, 5, 6], right: [7, 8], door: null },
    { row: 3, left: [9, 10, 11], right: [12, 13], door: null },
    { row: 4, left: [14, 15, 16], right: [], door: 'ENTREE 1' },
    { row: 5, left: [17, 18, 19], right: [20, 21], door: null },
    { row: 6, left: [22, 23, 24], right: [25, 26], door: null },
    { row: 7, left: [27, 28, 29], right: [30, 31], door: null },
    { row: 8, left: [32, 33, 34], right: [35, 36], door: null },
    { row: 9, left: [37, 38, 39], right: [40, 41], door: null },
    { row: 10, left: [42, 43, 44], right: [45, 46], door: null },
    { row: 11, left: [47, 48, 49], right: [50, 51], door: null },
    { row: 12, left: [52, 53, 54], right: [55, 56], door: null },
    { row: 13, left: [57, 58, 59], right: [60, 61], door: null },
    { row: 14, left: [62, 63, 64], right: [], door: 'ENTREE 2' },
    { row: 15, left: [65, 66, 67], right: [68, 69], door: null },
    { row: 16, left: [70, 71, 72], right: [73, 74], door: null },
    { row: 17, left: [75, 76, 77], center: 78, right: [79, 80], door: null },
  ] : [
    { row: 1, left: [null, 1, null], right: [2, 3], door: null },
    { row: 2, left: [4, 5, 6], right: [7, 8], door: null },
    { row: 3, left: [9, 10, 11], right: [12, 13], door: null },
    { row: 4, left: [14, 15, 16], right: [], door: 'ENTREE 1' },
    { row: 5, left: [17, 18, 19], right: [20, 21], door: null },
    { row: 6, left: [22, 23, 24], right: [25, 26], door: null },
    { row: 7, left: [27, 28, 29], right: [30, 31], door: null },
    { row: 8, left: [32, 33, 34], right: [35, 36], door: null },
    { row: 9, left: [37, 38, 39], right: [40, 41], door: null },
    { row: 10, left: [42, 43, 44], right: [45, 46], door: null },
    { row: 11, left: [47, 48, 49], right: [50, 51], door: null },
    { row: 12, left: [52, 53, 54], right: [55, 56], door: null },
    { row: 13, left: [57, 58, 59], right: [], door: 'ENTREE 2' },
    { row: 14, left: [60, 61, 62], right: [63, 64], door: null },
    { row: 15, left: [65, 66, 67], right: [68, 69], door: null },
    { row: 16, left: [70, 71, 72], center: 73, right: [74, 75], door: null },
  ];

  return specs.map(spec => ({
    rowNumber: spec.row,
    left: spec.left.map(n => n ? createSeatObj(n) : null),
    center: spec.center ? createSeatObj(spec.center) : null,
    right: spec.right.map(n => n ? createSeatObj(n) : null),
    door: spec.door,
  }));
});

function createSeatObj(num) {
  const code = 'S-' + String(num).padStart(2, '0');
  const isLocked = Boolean(CREW_LOCKS[num]);
  const isBooked = props.bookedSeats.includes(num) || props.bookedSeats.includes(code);
  const lockMeta = CREW_LOCKS[num] || null;

  return {
    num,
    code,
    isLocked,
    isBooked,
    role: lockMeta ? lockMeta.role : null,
    icon: lockMeta ? lockMeta.icon : null,
  };
}

function isSelected(seat) {
  return props.selectedSeats.includes(seat.code) || props.selectedSeats.includes(seat.num);
}

function getSeatClasses(seat) {
  if (seat.isLocked) {
    return 'w-10 h-10 rounded-md bg-amber-500 text-white font-bold flex items-center justify-center cursor-not-allowed opacity-90 shadow-sm';
  }
  if (seat.isBooked) {
    return 'w-10 h-10 rounded-md bg-slate-300 dark:bg-slate-700 text-slate-500 dark:text-slate-400 font-bold flex items-center justify-center cursor-not-allowed opacity-60';
  }
  if (isSelected(seat)) {
    return 'w-10 h-10 rounded-md bg-blue-600 text-white font-extrabold flex items-center justify-center ring-2 ring-blue-400 shadow-md transform scale-105';
  }
  return 'w-10 h-10 rounded-md bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 font-semibold border border-slate-300 dark:border-slate-600 hover:border-blue-500 hover:text-blue-600 flex items-center justify-center transition-all cursor-pointer shadow-sm';
}

function getSeatTooltip(seat) {
  if (seat.isLocked) return `Siège ${seat.code} : ${seat.role} (Réservé équipage)`;
  if (seat.isBooked) return `Siège ${seat.code} : Déjà réservé`;
  if (isSelected(seat)) return `Siège ${seat.code} : Sélectionné (Cliquez pour désélectionner)`;
  return `Siège ${seat.code} : Disponible`;
}

function toggleSeat(seat) {
  if (seat.isLocked || seat.isBooked) return;

  const current = [...props.selectedSeats];
  const idx = current.indexOf(seat.code);

  if (idx > -1) {
    current.splice(idx, 1);
  } else {
    if (current.length >= props.maxSelection) {
      alert(`Vous ne pouvez sélectionner que ${props.maxSelection} places maximum par réservation.`);
      return;
    }
    current.push(seat.code);
  }

  emit('update:selectedSeats', current);
  emit('seat-clicked', seat);
}
</script>

<style scoped>
.perspective-3d {
  transform: perspective(900px) rotateX(16deg);
  transform-origin: center top;
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
  transition: transform 0.4s ease;
}
</style>

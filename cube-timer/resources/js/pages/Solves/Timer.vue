<script setup lang="ts">
import { ref, onMounted, onUnmounted, computed } from 'vue';
import { Head, router } from '@inertiajs/vue3';

// wCA scramble generator
import { randomScrambleForEvent} from 'cubing/scramble';

defineOptions({
    layout: { breadcrumbs: [{ title: 'Timer', href: '/timer' }] },
});

// states
type TimerState = 'idle' | 'priming' | 'running' | 'stopped';
const state = ref<TimerState>('idle');

// scramble states
const selectedPuzzle = ref('333');
const isManualScramble = ref(true);
const currentScramble = ref('loading scramble');

// penalty state
const currentPenalty = ref<string | null>(null);

const puzzleOptions = [
    { id: '222', name: '2x2x2' },
    { id: '333', name: '3x3x3' },
    { id: '444', name: '4x4x4' },
    { id: 'pyram', name: 'Pyraminx' },
    { id: 'minx', name: 'Megaminx' },
    { id: 'skewb', name: 'Skewb' },
];

const generateNewScramble = async () => {
    if (isManualScramble.value) {
        currentScramble.value = 'User-generated scramble';
        return;
    }
    currentScramble.value = 'Generating...';
    try {
        const scramble = await randomScrambleForEvent(selectedPuzzle.value);
        currentScramble.value = scramble.toString();
    } catch (error) {
        currentScramble.value = 'Could not generate scramble';
        console.error('Scramble generation failed:', error);
    }
}

// timer vars
const timeMs = ref(0);
let startTime = 0;
let animationFrameId = 0;

const formattedTime = computed(() => {
    const totalSeconds = Math.floor(timeMs.value / 1000);
    const minutes = Math.floor(totalSeconds / 60);
    const seconds = totalSeconds % 60;
    const milliseconds = Math.floor((timeMs.value % 1000) / 10);

    const paddedMs = milliseconds.toString().padStart(2, '0');

    if (minutes > 0) {
        const paddedSeconds = seconds.toString().padStart(2, '0');
        return `${minutes}:${paddedSeconds}.${paddedMs}`;
    }

    return `${seconds}.${paddedMs}`;
});

generateNewScramble();

// Timer Actions
const startTimer = () => {
    if (animationFrameId) cancelAnimationFrame(animationFrameId);

    startTime = performance.now();
    const updateTime = () => {
        timeMs.value = performance.now() - startTime;
        animationFrameId = requestAnimationFrame(updateTime);
    };
    animationFrameId = requestAnimationFrame(updateTime);
};

const stopTimer = () => {
    cancelAnimationFrame(animationFrameId);
};

const togglePenalty = (type: '+2' | 'DNF') => {
    currentPenalty.value = currentPenalty.value === type ? null : type;
};

const discardSolve = () => {
    timeMs.value = 0;
    currentPenalty.value = null;
    state.value = 'idle';
    generateNewScramble();
};

const handlePuzzleChange = (e: Event) => {
    (e.target as HTMLElement).blur();
    generateNewScramble();
};

const saveAndReset = () => {
    router.post('/solves', {
        puzzle_type: selectedPuzzle.value,
        solve_time_ms: Math.floor(timeMs.value),
        scramble: currentScramble.value,
        penalty: currentPenalty.value,
    }, {
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => {
            timeMs.value = 0;
            state.value = 'idle';
            currentPenalty.value = null;
            generateNewScramble();
        }
    });
};

// Keyboard Event Listeners
const handleKeydown = (e: KeyboardEvent) => {
    if (e.code !== 'Space') return;
    if (e.repeat) return;
    e.preventDefault();

    if (state.value === 'idle') {
        state.value = 'priming';
        timeMs.value = 0;
    }
    else if (state.value === 'running') {
        stopTimer();
        state.value = 'stopped';
    }
    else if (state.value === 'stopped') {
        saveAndReset();
    }
};

const handleKeyup = (e: KeyboardEvent) => {
    if (e.code !== 'Space') return;

    if (state.value === 'priming') {
        state.value = 'running';
        startTimer();
    }
};

// Lifecycle Hooks
onMounted(() => {
    window.addEventListener('keydown', handleKeydown);
    window.addEventListener('keyup', handleKeyup);
});

onUnmounted(() => {
    window.removeEventListener('keydown', handleKeydown);
    window.removeEventListener('keyup', handleKeyup);
    cancelAnimationFrame(animationFrameId);
});
</script>

<template>
    <Head title="Timer" />

    <div class="w-full h-full min-h-[80vh] flex flex-col relative py-8 px-4 sm:px-8">
        <div class="absolute top-8 left-8 z-10 flex items-center gap-4 transition-opacity duration-200" :class="{ 'opacity-0 pointer-events-none': state === 'running' }">
            <select
                v-model="selectedPuzzle"
                @change="handlePuzzleChange"
                class="bg-transparent border-border text-muted-foreground hover:text-foreground font-mono text-sm focus:ring-1 focus:ring-primary focus:border-primary cursor-pointer transition-colors rounded-md py-1.5 pl-3 pr-8 shadow-sm"
            >
                <option v-for="puzzle in puzzleOptions" :key="puzzle.id" :value="puzzle.id" class="bg-card text-foreground">
                    {{ puzzle.name }}
                </option>
            </select>
        </div>
        <div class="flex flex-col justify-center items-center flex-1 w-full relative">

            <h1 class="text-9xl font-mono font-bold tracking-tighter select-none transition-colors duration-100 mb-8"
                :class="{
                    'text-foreground': state === 'idle' || state === 'running',
                    'text-green-500': state === 'priming',
                    'text-primary': state === 'stopped'
                }">
                {{ formattedTime }}
            </h1>

            <div class="flex gap-4 h-10 transition-opacity duration-200"
                 :class="{ 'opacity-0 pointer-events-none': state !== 'stopped' }">

                <button @mousedown.prevent @click="togglePenalty('+2')"
                        class="px-6 py-1.5 font-mono text-sm font-bold rounded border transition-all"
                        :class="currentPenalty === '+2' ? 'bg-primary text-primary-foreground border-primary shadow-md' : 'text-muted-foreground border-border hover:bg-muted hover:text-foreground'">
                    +2
                </button>

                <button @mousedown.prevent @click="togglePenalty('DNF')"
                        class="px-6 py-1.5 font-mono text-sm font-bold rounded border transition-all"
                        :class="currentPenalty === 'DNF' ? 'bg-destructive text-destructive-foreground border-destructive shadow-md' : 'text-muted-foreground border-border hover:bg-muted hover:text-foreground'">
                    DNF
                </button>

                <button @mousedown.prevent @click="discardSolve"
                        class="px-6 py-1.5 font-mono text-sm font-bold rounded border text-muted-foreground border-border hover:bg-destructive hover:text-destructive-foreground hover:border-destructive transition-all">
                    Discard
                </button>
            </div>

            <div class="mt-12 flex flex-col items-center max-w-4xl transition-opacity duration-200" :class="{ 'opacity-0 pointer-events-none': state === 'running' }">

                <p class="text-muted-foreground font-mono text-xl text-center mb-6 leading-relaxed">
                    {{ currentScramble }}
                </p>

                <label class="flex items-center gap-3 cursor-pointer text-muted-foreground hover:text-foreground font-mono text-sm transition-colors group">
                    <div class="relative flex items-center justify-center">
                        <input
                            type="checkbox"
                            v-model="isManualScramble"
                            @change="handlePuzzleChange"
                            class="peer appearance-none w-4 h-4 border-2 border-muted-foreground rounded-sm checked:bg-primary checked:border-primary focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 focus:ring-offset-background transition-all cursor-pointer"
                        >
                        <svg class="absolute w-3 h-3 text-primary-foreground opacity-0 peer-checked:opacity-100 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                    <span>Hand Scramble</span>
                </label>

            </div>

            <p class="text-muted-foreground/50 text-sm font-mono absolute bottom-0">
                <span v-if="state === 'idle'">Hold Spacebar to prime</span>
                <span v-if="state === 'priming'">Release Spacebar to start</span>
                <span v-if="state === 'running'">&nbsp;</span>
                <span v-if="state === 'stopped'">Press Spacebar to save and reset</span>
            </p>

        </div>
    </div>
</template>

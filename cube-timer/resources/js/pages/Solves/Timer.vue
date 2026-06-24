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
const state = ref<TimerState>('idle'); // ref() makes the variable reactive

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

/**
 * returns the timeMs const but as a string where sub-10 seconds: x.xx
 * sub 60 seconds: xx.xx
 * over 1 minute: x:xx.xx
 */
const formattedTime = computed(() => {
    const totalSeconds = Math.floor(timeMs.value / 1000);
    const minutes = Math.floor(totalSeconds / 60);
    const seconds = totalSeconds % 60;
    const milliseconds = Math.floor((timeMs.value % 1000) / 10); // 2 digits for ms

    const paddedMs = milliseconds.toString().padStart(2, '0');

    if (minutes > 0) {
        const paddedSeconds = seconds.toString().padStart(2, '0');
        return `${minutes}:${paddedSeconds}.${paddedMs}`;
    }

    return `${seconds}.${paddedMs}`;
});

generateNewScramble() // calling once so when the page loads for the first time, it has a scramble preloaded

// Timer Actions
const startTimer = () => {
    // BUG 2 FIX: Kill any ghost timers before starting a new one!
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
    // If it's already selected, clicking it again turns it off
    currentPenalty.value = currentPenalty.value === type ? null : type;
};

const discardSolve = () => {
    timeMs.value = 0;
    currentPenalty.value = null;
    state.value = 'idle';
    generateNewScramble();
};

const handlePuzzleChange = (e: Event) => {
    // Remove focus from the dropdown after selecting, so spacebar doesn't reopen it
    (e.target as HTMLElement).blur();
    generateNewScramble();
};

const saveAndReset = () => {
    // Fire a background POST request to save the solve
    router.post('/solves', {
        puzzle_type: selectedPuzzle.value, // Hardcoded for now
        solve_time_ms: Math.floor(timeMs.value),
        scramble: currentScramble.value, // Dummy scramble for now
        penalty: currentPenalty.value,
    }, {
        preserveState: true, // Don't reset our Vue variables automatically
        preserveScroll: true, // Don't jump the page around
        onSuccess: () => {
            // Once saved, reset everything for the next solve!
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

    // Crucial: OS triggers continuous keydown events if you hold a key. Ignore repeats!
    if (e.repeat) return;

    e.preventDefault(); // Stop spacebar from scrolling the page

    if (state.value === 'idle') {
        state.value = 'priming';
        timeMs.value = 0; // Reset visual timer
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

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-zinc-900 overflow-hidden shadow-sm sm:rounded-lg p-12 min-h-[60vh] flex flex-col justify-center items-center relative">

                <div class="text-center space-y-4">

                    <div class="flex items-center gap-6 transition-opacity duration-200" :class="{ 'opacity-0 pointer-events-none': state === 'running' }">

                        <select
                            v-model="selectedPuzzle"
                            @change="handlePuzzleChange"
                            class="bg-transparent border-none text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 font-mono text-sm focus:ring-0 cursor-pointer transition-colors"
                        >
                            <option v-for="puzzle in puzzleOptions" :key="puzzle.id" :value="puzzle.id" class="text-gray-900">
                                {{ puzzle.name }}
                            </option>
                        </select>

                        <label class="flex items-center gap-2 cursor-pointer text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 font-mono text-sm transition-colors group">
                            <input
                                type="checkbox"
                                v-model="isManualScramble"
                                @change="handlePuzzleChange"
                                class="rounded border-gray-300 text-blue-500 focus:ring-0 bg-transparent cursor-pointer"
                            >
                            <span>Hand Scramble</span>
                        </label>

                    </div>
                    <p class="text-gray-400 font-mono text-xl" :class="{ 'opacity-0': state === 'running' }">
                        {{ currentScramble }}
                    </p>

                    <h1 class="text-8xl font-mono font-bold tracking-tighter select-none transition-colors duration-100"
                        :class="{
                            'text-gray-900 dark:text-gray-100': state === 'idle',
                            'text-green-500': state === 'priming',
                            'text-gray-900 dark:text-gray-100': state === 'running',
                            'text-blue-500': state === 'stopped'
                        }">
                        {{ formattedTime }}
                    </h1>

                    <div class="flex gap-4 h-10 transition-opacity duration-200"
                         :class="{ 'opacity-0 pointer-events-none': state !== 'stopped' }">

                        <button @mousedown.prevent @click="togglePenalty('+2')"
                                class="px-6 py-1.5 font-mono text-sm font-bold rounded border transition-colors"
                                :class="currentPenalty === '+2' ? 'bg-yellow-500 text-white border-yellow-500' : 'text-gray-400 border-gray-300 hover:text-gray-600 dark:border-zinc-700 dark:hover:text-gray-200'">
                            +2
                        </button>

                        <button @mousedown.prevent @click="togglePenalty('DNF')"
                                class="px-6 py-1.5 font-mono text-sm font-bold rounded border transition-colors"
                                :class="currentPenalty === 'DNF' ? 'bg-red-500 text-white border-red-500' : 'text-gray-400 border-gray-300 hover:text-gray-600 dark:border-zinc-700 dark:hover:text-gray-200'">
                            DNF
                        </button>

                        <button @mousedown.prevent @click="discardSolve"
                                class="px-6 py-1.5 font-mono text-sm font-bold rounded border text-gray-400 border-gray-300 hover:bg-red-50 hover:text-red-600 hover:border-red-300 dark:border-zinc-700 dark:hover:bg-red-900/30 transition-colors">
                            Discard
                        </button>
                    </div>

                    <p class="text-gray-500 text-sm h-6">
                        <span v-if="state === 'idle'">Hold Spacebar to prime</span>
                        <span v-if="state === 'priming'">Release Spacebar to start</span>
                        <span v-if="state === 'running'">&nbsp;</span>
                        <span v-if="state === 'stopped'">Press Spacebar to save and reset</span>
                    </p>
                </div>

            </div>
        </div>
    </div>
</template>

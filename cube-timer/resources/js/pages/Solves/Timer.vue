<script setup lang="ts">
import { ref, onMounted, onUnmounted, computed } from 'vue';
import { Head, router } from '@inertiajs/vue3';

defineOptions({
    layout: { breadcrumbs: [{ title: 'Timer', href: '/timer' }] },
});

// states
type TimerState = 'idle' | 'priming' | 'running' | 'stopped';
const state = ref<TimerState>('idle'); // ref() makes the variable reactive

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

// Timer Actions
const startTimer = () => {
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

const saveAndReset = () => {
    // Fire a background POST request to save the solve
    router.post('/solves', {
        puzzle_type: '3x3x3', // Hardcoded for now
        solve_time_ms: Math.floor(timeMs.value),
        scramble: 'R U R\' U\'', // Dummy scramble for now
        penalty: null,
    }, {
        preserveState: true, // Don't reset our Vue variables automatically
        preserveScroll: true, // Don't jump the page around
        onSuccess: () => {
            // Once saved, reset everything for the next solve!
            timeMs.value = 0;
            state.value = 'idle';
        }
    });
};

// --- Keyboard Event Listeners ---
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

// --- Lifecycle Hooks ---
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
                    <p class="text-gray-400 font-mono text-xl" :class="{ 'opacity-0': state === 'running' }">
                        Scramble goes here...
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

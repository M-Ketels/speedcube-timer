<script setup lang="ts">
import { Head } from '@inertiajs/vue3';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Timer', href: '/timer' },
        ],
    },
});
</script>

<template>
    <Head title="Timer" />

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-zinc-900 overflow-hidden shadow-sm sm:rounded-lg p-12 min-h-[60vh] flex flex-col justify-center items-center">

                <div class="text-center space-y-4">
                    <p class="text-gray-400 font-mono text-xl">Scramble goes here...</p>
                    <h1 class="text-8xl font-mono font-bold text-gray-900 dark:text-gray-100 tracking-tighter select-none">
                        00:00.00
                    </h1>
                    <p class="text-gray-500 text-sm">Press Spacebar to start</p>
                </div>

            </div>
        </div>
    </div>
</template>

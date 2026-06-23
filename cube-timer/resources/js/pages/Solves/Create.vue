<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';

const form = useForm({
    puzzle_type: '3x3x3',
    solve_time_ms: '',
    scramble: 'R U R\' U\'', // Dummy scramble
    penalty: '',
});

const submit = () => {
    form.post('/solves');
};

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'My Solves', href: '/solves' },
            { title: 'Add Solve', href: '/solves/create' },
        ],
    },
});
</script>

<template>
    <Head title="Manual Solve Entry" />

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <form @submit.prevent="submit" class="p-6 bg-white rounded-lg shadow-sm space-y-4">

                <div>
                    <label class="block text-sm font-medium text-gray-700">Puzzle Type</label>
                    <input
                        v-model="form.puzzle_type"
                        type="text"
                        class="block w-full mt-1 border-gray-300 rounded-md shadow-sm text-gray-900 bg-white"
                    />
                    <div v-if="form.errors.puzzle_type" class="mt-1 text-sm text-red-600">{{ form.errors.puzzle_type }}</div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Time (in milliseconds)</label>
                    <input
                        v-model="form.solve_time_ms"
                        type="number"
                        required
                        class="block w-full mt-1 border-gray-300 rounded-md shadow-sm text-gray-900 bg-white"
                    />
                    <div v-if="form.errors.solve_time_ms" class="mt-1 text-sm text-red-600">{{ form.errors.solve_time_ms }}</div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Scramble</label>
                    <input
                        v-model="form.scramble"
                        type="text"
                        class="block w-full mt-1 border-gray-300 rounded-md shadow-sm font-mono text-sm text-gray-900 bg-white"
                    />
                    <div v-if="form.errors.scramble" class="mt-1 text-sm text-red-600">{{ form.errors.scramble }}</div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Penalty (+2, DNF, or empty)</label>
                    <input
                        v-model="form.penalty"
                        type="text"
                        class="block w-full mt-1 border-gray-300 rounded-md shadow-sm text-gray-900 bg-white"
                    />
                    <div v-if="form.errors.penalty" class="mt-1 text-sm text-red-600">{{ form.errors.penalty }}</div>
                </div>

                <button
                    type="submit"
                    :disabled="form.processing"
                    class="px-4 py-2 font-bold text-white bg-blue-500 rounded hover:bg-blue-700 disabled:opacity-50"
                >
                    Save Solve
                </button>

            </form>
        </div>
    </div>
</template>

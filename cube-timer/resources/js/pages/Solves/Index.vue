<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';

defineProps<{
    solves: any;
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: '/dashboard' },
            { title: 'My Solves', href: '/solves' },
        ],
    },
});
</script>

<template>
    <Head title="My Solves" />

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-xl font-semibold leading-tight text-gray-600">My Solves</h2>
                <Link href="/solves/create" class="px-4 py-2 font-bold text-white bg-blue-500 rounded hover:bg-blue-700">
                    + Add Manual Solve
                </Link>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Time (ms)</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Scramble</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Penalty</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="solve in solves.data" :key="solve.id">
                            <td class="px-6 py-4 whitespace-nowrap text-gray-700">{{ solve.solve_time_ms }}</td>
                            <td class="px-6 py-4 truncate max-w-xs font-mono text-sm text-gray-700">{{ solve.scramble }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-red-500 text-gray-700">{{ solve.penalty }}</td>
                        </tr>
                    </tbody>
                </table>

                <div v-if="solves.data.length === 0" class="p-6 text-gray-500 text-center">
                    No solves logged yet.
                </div>
            </div>
        </div>
    </div>

    <div v-if="solves.links && solves.links.length > 3" class="px-6 py-4 border-gray-200 flex flex-wrap justify-center gap-2">
        <template v-for="(link, key) in solves.links" :key="key">
            <div v-if="link.url === null"
                 class="px-4 py-2 text-sm text-gray-500 bg-gray-50 border border-gray-300 rounded cursor-not-allowed"
                 v-html="link.label">
            </div>

            <Link v-else
                  :href="link.url"
                  class="px-4 py-2 text-sm border rounded hover:bg-gray-100 transition-colors"
                  :class="{ 'bg-blue-500 text-white border-blue-500 hover:bg-blue-600': link.active, 'text-gray-700 bg-white border-gray-300': !link.active }"
                  v-html="link.label">
            </Link>
        </template>
    </div>
</template>

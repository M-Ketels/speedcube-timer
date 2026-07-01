<script setup lang="ts">
import { reactive, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';

const props = defineProps<{
    solves: any;
    filters: {
        puzzle_type: string;
        sort_by: string;
    };
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: '/dashboard' },
            { title: 'My Solves', href: '/solves' },
        ],
    },
});

// Dropdown state
const filterForm = reactive({
    puzzle_type: props.filters.puzzle_type || 'all',
    sort_by: props.filters.sort_by || 'date_desc',
});

// Automatically fetch new data when a dropdown changes
watch(filterForm, () => {
    router.get('/solves', filterForm, {
        preserveState: true,
        preserveScroll: true
    });
});

const puzzleOptions = [
    { id: 'all', name: 'All Puzzles' },
    { id: '222', name: '2x2x2' },
    { id: '333', name: '3x3x3' },
    { id: '444', name: '4x4x4' },
    { id: 'pyram', name: 'Pyraminx' },
    { id: 'minx', name: 'Megaminx' },
    { id: 'skewb', name: 'Skewb' },
];

const formatTime = (ms: number) => {
    const totalSeconds = Math.floor(ms / 1000);
    const minutes = Math.floor(totalSeconds / 60);
    const seconds = totalSeconds % 60;
    const milliseconds = Math.floor((ms % 1000) / 10);
    const paddedMs = milliseconds.toString().padStart(2, '0');
    if (minutes > 0) {
        const paddedSeconds = seconds.toString().padStart(2, '0');
        return `${minutes}:${paddedSeconds}.${paddedMs}`;
    }
    return `${seconds}.${paddedMs}`;
};

// Formats date into two lines
const formatDate = (dateString: string) => {
    const date = new Date(dateString);
    const formattedDate = new Intl.DateTimeFormat('en-GB', { day: '2-digit', month: 'short', year: 'numeric' }).format(date);
    const formattedTime = new Intl.DateTimeFormat('en-GB', { hour: '2-digit', minute: '2-digit' }).format(date);
    return { date: formattedDate, time: formattedTime };
};
</script>

<template>
    <Head title="My Solves" />

    <div class="py-12 px-4 sm:px-8 font-mono">
        <div class="max-w-7xl mx-auto">

            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-8 gap-4">

                <div class="flex items-center gap-4">
                    <select
                        v-model="filterForm.puzzle_type"
                        class="bg-card border-border text-foreground text-sm rounded-md py-1.5 pl-3 pr-8 focus:ring-1 focus:ring-primary focus:border-primary cursor-pointer shadow-sm"
                    >
                        <option v-for="puzzle in puzzleOptions" :key="puzzle.id" :value="puzzle.id">
                            {{ puzzle.name }}
                        </option>
                    </select>

                    <select
                        v-model="filterForm.sort_by"
                        class="bg-card border-border text-foreground text-sm rounded-md py-1.5 pl-3 pr-8 focus:ring-1 focus:ring-primary focus:border-primary cursor-pointer shadow-sm"
                    >
                        <option value="date_desc">Latest First</option>
                        <option value="date_asc">Oldest First</option>
                        <option value="time_asc">Fastest Time</option>
                        <option value="time_desc">Slowest Time</option>
                    </select>
                </div>

                <Link href="/solves/create" class="px-4 py-2 font-bold text-sm text-primary-foreground bg-primary rounded-md shadow-sm hover:opacity-90 transition-opacity">
                    + Add Manual Solve
                </Link>
            </div>

            <div class="w-full flex flex-col">

                <div class="grid grid-cols-12 gap-4 px-6 py-3 text-primary text-xs tracking-wide">
                    <div class="col-span-1">puzzle</div>
                    <div class="col-span-2">time</div>
                    <div class="col-span-1">penalty</div>
                    <div class="col-span-6">scramble</div>
                    <div class="col-span-2">date</div>
                </div>

                <div v-if="solves.data.length === 0" class="p-12 text-muted-foreground text-center bg-card rounded-xl mt-2">
                    No solves found for this selection.
                </div>

                <div class="space-y-1">
                    <div v-for="(solve, index) in solves.data" :key="solve.id"
                         class="grid grid-cols-12 gap-4 px-6 py-3 items-center rounded-lg transition-colors"
                         :class="index % 2 === 0 ? 'bg-card' : 'bg-transparent hover:bg-card/50'">

                        <div class="col-span-1 text-sm text-muted-foreground">
                            {{ solve.puzzle_type }}
                        </div>

                        <div class="col-span-2 text-base text-foreground font-bold">
                            {{ formatTime(solve.solve_time_ms) }}
                        </div>

                        <div class="col-span-1 text-sm text-destructive font-bold">
                            {{ solve.penalty }}
                        </div>

                        <div class="col-span-6 text-sm text-muted-foreground truncate pr-4" :title="solve.scramble">
                            {{ solve.scramble }}
                        </div>

                        <div class="col-span-2 flex flex-col text-xs text-muted-foreground">
                            <span>{{ formatDate(solve.created_at).date }}</span>
                            <span>{{ formatDate(solve.created_at).time }}</span>
                        </div>
                    </div>
                </div>

            </div>

            <div v-if="solves.links && solves.links.length > 3" class="mt-8 flex flex-wrap justify-center gap-2">
                <template v-for="(link, key) in solves.links" :key="key">
                    <div v-if="link.url === null"
                         class="px-4 py-2 text-sm text-muted-foreground bg-transparent border border-border rounded cursor-not-allowed"
                         v-html="link.label">
                    </div>
                    <Link v-else
                          :href="link.url"
                          class="px-4 py-2 text-sm border rounded transition-colors shadow-sm"
                          :class="link.active
                            ? 'bg-primary text-primary-foreground border-primary'
                            : 'text-foreground bg-card border-border hover:bg-muted'"
                          v-html="link.label">
                    </Link>
                </template>
            </div>

        </div>
    </div>
</template>

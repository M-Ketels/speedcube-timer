<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import Heading from '@/components/Heading.vue';
import { edit } from '@/routes/appearance';

// 1. Accept the settings prop from the Controller
const props = defineProps<{
    settings: {
        id: number;
        theme_name: string;
    }
}>();

// 2. Our theme dictionary
const availableThemes = [
    { id: 'default_dark', name: 'Default Dark' },
    { id: 'matcha_moccha', name: 'Matcha Moccha' },
    { id: 'serika_dark', name: 'Serika Dark' },
    { id: 'nebula', name: 'Nebula' },
    { id: 'matrix', name: 'Matrix' },
];

// 3. Form setup
const form = useForm({
    // Fallback to default_dark just in case the prop hasn't loaded yet
    theme_name: props.settings?.theme_name || 'default_dark',
});

// 4. Background save function
const selectTheme = (themeId: string) => {
    form.theme_name = themeId;
    form.patch('/settings', { // Points to our custom save route!
        preserveScroll: true,
        preserveState: true,
    });
};

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Appearance settings',
                href: edit(),
            },
        ],
    },
});
</script>

<template>
    <Head title="Appearance settings" />

    <h1 class="sr-only">Appearance settings</h1>

    <div class="space-y-6">
        <Heading
            variant="small"
            title="Appearance settings"
            description="Completely change the look and feel of the website by picking a preset."
        />

        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 pt-4">
            <button
                v-for="theme in availableThemes"
                :key="theme.id"
                @click="selectTheme(theme.id)"
                class="px-4 py-3 rounded-md font-mono text-sm font-bold border transition-all text-left flex items-center justify-between"
                :class="form.theme_name === theme.id
                    ? 'bg-blue-50 text-blue-600 border-blue-500 dark:bg-blue-900/20 dark:text-blue-400 dark:border-blue-500 shadow-sm'
                    : 'bg-transparent text-gray-600 border-gray-200 dark:text-gray-400 dark:border-zinc-800 hover:border-gray-300 dark:hover:border-zinc-600'"
            >
                <span>{{ theme.name }}</span>
                <span v-if="form.theme_name === theme.id" class="text-blue-500 dark:text-blue-400">★</span>
            </button>
        </div>

    </div>
</template>

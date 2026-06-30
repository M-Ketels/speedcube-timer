<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import Heading from '@/components/Heading.vue';
import { edit } from '@/routes/appearance';
// Import dictionary
import themesData from '@/themes.json';

const props = defineProps<{
    settings: {
        id: number;
        theme_name: string;
    }
}>();

const form = useForm({
    theme_name: props.settings?.theme_name || 'default_dark',
});

const selectTheme = (themeId: string) => {
    form.theme_name = themeId;

    const themeConfig = themesData[themeId as keyof typeof themesData] || themesData['default_dark'];
    Object.entries(themeConfig.colors).forEach(([cssVariable, value]) => {
        document.documentElement.style.setProperty(cssVariable, value);
    });

    form.patch('/settings/appearance', {
        preserveScroll: true,
        preserveState: true,
    });
};

defineOptions({ layout: { breadcrumbs: [{ title: 'Appearance settings', href: edit() }] } });
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
                v-for="(theme, themeId) in themesData"
                :key="themeId"
                @click="selectTheme(themeId)"
                class="px-4 py-3 rounded-md font-mono text-sm font-bold border transition-all text-left flex items-center justify-between"
                :class="form.theme_name === themeId ? 'ring-2 ring-blue-500 shadow-md' : 'opacity-80 hover:opacity-100'"
                :style="{
                    backgroundColor: theme.colors['--background'],
                    color: theme.colors['--foreground'],
                    borderColor: theme.colors['--border']
                }"
            >
                <span>{{ theme.name }}</span>
                <span v-if="form.theme_name === themeId" :style="{ color: theme.colors['--primary'] }">★</span>
            </button>

        </div>
    </div>
</template>

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

    document.documentElement.style.setProperty('--sidebar-background', themeConfig.colors['--card']);
    document.documentElement.style.setProperty('--sidebar-border', themeConfig.colors['--border']);
    document.documentElement.style.setProperty('--sidebar-foreground', themeConfig.colors['--foreground']);
    document.documentElement.style.setProperty('--sidebar-accent', themeConfig.colors['--muted']);
    document.documentElement.style.setProperty('--sidebar-accent-foreground', themeConfig.colors['--primary']);
    document.documentElement.style.setProperty('--sidebar-ring', themeConfig.colors['--ring']);

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
                class="px-4 py-3 rounded-xl font-mono text-sm font-bold border-2 transition-all text-left flex items-center justify-between"
                :class="form.theme_name === themeId ? 'scale-[1.02] shadow-lg' : 'opacity-80 hover:opacity-100 hover:scale-[1.01]'"
                :style="{
                    backgroundColor: theme.colors['--card'],
                    borderColor: form.theme_name === themeId ? theme.colors['--primary'] : theme.colors['--border']
                }"
            >
                <div class="flex items-center gap-2">
                    <span :style="{ color: theme.colors['--foreground'] }">{{ theme.name }}</span>
                </div>

                <div class="flex gap-1.5 items-center">
                    <div class="w-3 h-3 rounded-full" :style="{ backgroundColor: theme.colors['--primary'] }"></div>
                    <div class="w-3 h-3 rounded-full" :style="{ backgroundColor: theme.colors['--accent'] }"></div>
                    <div class="w-3 h-3 rounded-full" :style="{ backgroundColor: theme.colors['--foreground'] }"></div>
                </div>
            </button>

        </div>
    </div>
</template>

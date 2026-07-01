<script setup lang="ts">
import { Form, Head, usePage, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';
import ProfileController from '@/actions/App/Http/Controllers/Settings/ProfileController';
import DeleteUser from '@/components/DeleteUser.vue';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { edit } from '@/routes/profile';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Profile settings',
                href: edit(),
            },
        ],
    },
});

const page = usePage();
const user = computed(() => page.props.auth.user);

const importForm = useForm({
    csv_file: null as File | null,
});

const handleFileUpload = (event: Event) => {
    const target = event.target as HTMLInputElement;
    if (target.files && target.files.length > 0) {
        importForm.csv_file = target.files[0];
        importForm.post('/solves/import', {
            preserveScroll: true,
            onSuccess: () => {
                alert('Solves successfully imported!');
                importForm.reset();
            },
        });
    }
};

</script>

<template>
    <Head title="Profile settings" />

    <h1 class="sr-only">Profile settings</h1>

    <div class="flex flex-col space-y-6">
        <Heading
            variant="small"
            title="Profile"
            description="Update your name and email address"
        />

        <Form
            v-bind="ProfileController.update.form()"
            class="space-y-6"
            v-slot="{ errors, processing }"
        >
            <div class="grid gap-2">
                <Label for="name">Name</Label>
                <Input
                    id="name"
                    class="mt-1 block w-full"
                    name="name"
                    :default-value="user.name"
                    required
                    autocomplete="name"
                    placeholder="Full name"
                />
                <InputError class="mt-2" :message="errors.name" />
            </div>

            <div class="grid gap-2">
                <Label for="email">Email address</Label>
                <Input
                    id="email"
                    type="email"
                    class="mt-1 block w-full"
                    name="email"
                    :default-value="user.email"
                    required
                    autocomplete="username"
                    placeholder="Email address"
                />
                <InputError class="mt-2" :message="errors.email" />
            </div>

            <div class="flex items-center gap-4">
                <Button :disabled="processing" data-test="update-profile-button"
                    >Save</Button
                >
            </div>
        </Form>
    </div>

    <div class="flex flex-col space-y-6 mt-12 mb-12">
        <Heading
            variant="small"
            title="Data Management"
            description="Import legacy solves from your phone, or export a backup of your current database."
        />

        <div class="flex flex-col sm:flex-row gap-4">
            <a href="/solves/export"
               class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50 border border-input bg-background shadow-sm hover:bg-accent hover:text-accent-foreground h-9 px-4 py-2"
            >
                Export CSV Backup
            </a>

            <div class="relative">
                <input
                    type="file"
                    id="csv_import"
                    accept=".csv"
                    @change="handleFileUpload"
                    class="hidden"
                />
                <Button as="label" for="csv_import" variant="outline" class="cursor-pointer w-full sm:w-auto" :disabled="importForm.processing">
                    <span v-if="importForm.processing">Importing...</span>
                    <span v-else>Import CSV</span>
                </Button>
            </div>
        </div>
        <InputError class="mt-2" :message="importForm.errors.csv_file" />
    </div>

    <DeleteUser />


</template>

<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { BreadcrumbItem } from '@/types';


interface Materials {
    id: number;
    type: string;
    image: string;
    color_code: string;
    code: string;
    name: string;
    measurement: string;
    created_at: string;
    updated_at: string;
}

const { materials, materials_count, filters } = defineProps<{
    materials: Materials[];
    materials_count: string;
    filters: {
        search?: string;
    }
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Materials',
        href: '/materials'
    }
]

const getImageUrl = (path: string) => {
    return `/storage/${path}`;
};

</script>

<template>

    <Head title="Materials" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-flex flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
            <div class="col-span-12">
                <!-- Metric Item Start -->
                <div
                    class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6">
                    <h4 class="text-title-sm font-bold text-gray-800 dark:text-white/90">
                        {{ materials_count }}
                    </h4>

                    <div class="mt-4 flex items-end justify-between sm:mt-5">
                        <div>
                            <p class="text-theme-sm text-gray-700 dark:text-gray-400">
                                Active Deal
                            </p>
                        </div>

                        <div class="flex items-center gap-1">
                            <span
                                class="flex items-center gap-1 rounded-full bg-success-50 px-2 py-0.5 text-theme-xs font-medium text-success-600 dark:bg-success-500/15 dark:text-success-500">
                                +20%
                            </span>

                            <span class="text-theme-xs text-gray-500 dark:text-gray-400">
                                From last month
                            </span>
                        </div>
                    </div>
                </div>
                <!-- Metric Item End -->
            </div>
            <div
                class="relative min-h-[100vh] flex-1 rounded-xl border border-sidebar-border/70 md:min-h-min dark:border-sidebar-border">
                <!-- <PlaceholderPattern /> -->
                <div
                    class="relative overflow-x-auto bg-neutral-primary-soft shadow-xs rounded-base border border-default">

                    <div class="flex items-center px-4">
                        <div class="p-4">
                            <label for="input-group-1" class="sr-only">Search</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none">
                                    <svg class="w-4 h-4 text-body" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                                        width="24" height="24" fill="none" viewBox="0 0 24 24">
                                        <path stroke="currentColor" stroke-linecap="round" stroke-width="2"
                                            d="m21 21-3.5-3.5M17 10a7 7 0 1 1-14 0 7 7 0 0 1 14 0Z" />
                                    </svg>
                                </div>
                                <!-- Search input -->
                                <input type="text" id="search-input"
                                    class="block w-full max-w-96 ps-9 pe-9 py-2 bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand shadow-xs placeholder:text-body"
                                    placeholder="Nom yoki kod bo'yicha qidiring..." />

                                <!-- Clear button -->
                                <button type="button"
                                    class="absolute inset-y-0 end-0 flex items-center pe-3 text-gray-400 hover:text-gray-600 transition-colors"
                                    title="Tozalash">
                                    <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                        <button class="ml-auto bg-green-500 text-white px-3 py-1 rounded"
                            type="button">Qo'shish</button>
                    </div>

                    <table class="w-full text-sm text-left rtl:text-right text-body">
                        <thead
                            class="text-sm text-body bg-neutral-secondary-medium border-b border-t border-default-medium">
                            <tr>
                                <th scope="col" class="p-4">
                                    <div class="flex items-center">
                                        <input type="checkbox" />
                                        <label for="table-checkbox-12" class="sr-only">Table checkbox</label>
                                    </div>
                                </th>
                                <th scope="col" class="px-6 py-3 font-medium">
                                    Materials image
                                </th>
                                <th scope="col" class="px-6 py-3 font-medium">
                                    Materials name
                                </th>
                                <th scope="col" class="px-6 py-3 font-medium">
                                    Materials color
                                </th>
                                <th scope="col" class="px-6 py-3 font-medium">
                                    Materials code
                                </th>
                                <th scope="col" class="px-6 py-3 font-medium">
                                    Materials measurement
                                </th>
                                <th scope="col" class="px-6 py-3 font-medium">
                                    Materials type
                                </th>
                                <th scope="col" class="px-6 py-3 font-medium">
                                    Created at
                                </th>
                                <th scope="col" class="px-6 py-3 font-medium">
                                    Action
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="item in materials" :key="item.id"
                                class="bg-neutral-primary-soft border-b border-default hover:bg-neutral-secondary-medium">

                                <td class="w-4 p-4">
                                    <div class="flex items-center">
                                        <input type="checkbox" :value="item.id" class="w-4 h-4 border rounded">
                                        <label for="table-checkbox-13" class="sr-only">Table checkbox</label>
                                    </div>
                                </td>

                                <th scope="row" class="px-6 py-4 font-medium text-heading whitespace-nowrap">
                                    <img :src="getImageUrl(item.image)" :alt="item.name"
                                        class="h-16 w-16 rounded-lg object-cover" />
                                </th>
                                <td class="px-6 py-4">
                                    {{ item.name }}
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <!-- Rangli kvadrat -->
                                        <div class="w-5 h-5 border border-gray-300 rounded"
                                            :style="{ backgroundColor: item.color_code }"></div>

                                        <!-- Color code matni (ixtiyoriy) -->
                                        <span class="text-sm text-gray-600">{{ item.color_code }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    {{ item.code }}
                                </td>
                                <td class="px-6 py-4">
                                    {{ item.measurement }}
                                </td>
                                <td class="px-6 py-4">
                                    {{ item.type }}
                                </td>
                                <td class="px-6 py-4">
                                    {{ new Date(item.created_at).toLocaleDateString() }}
                                </td>
                            </tr>
                        </tbody>
                    </table>

                </div>
            </div>
        </div>
    </AppLayout>
</template>
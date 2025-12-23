<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import PlaceholderPattern from '../components/PlaceholderPattern.vue';
import { ref, watch, onMounted, onUnmounted } from 'vue'
import { useForm } from '@inertiajs/vue3'
import { route } from 'ziggy-js';
import { useToast } from '@/components/useToast/useToast';
import ToastContainer from '@/components/useToast/ToastContainer.vue';

const { success, error } = useToast();

const notify = () => {
    success("Bu muvaffaqiyatli xabar!");
    error("Bu xatolik xabari!");
}

interface Clothes {
    id: number;
    name: string;
    image: string;
    created_at: string;
    updated_at: string;
}

// Props ni qabul qilish
const { clothes, clothes_count } = defineProps<{
    clothes: Clothes[];
    clothes_count: number;  // shu yerga qo'shasiz
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Clothes',
        href: '/clothes',
    },
];

const getImageUrl = (path: string) => {
    return `/storage/${path}`;
};



const isOpen = ref(false)

const openModal = () => {
    isOpen.value = true
}

const closeModal = () => {
    isOpen.value = false
}

watch(isOpen, (val) => {
    document.body.style.overflow = val ? 'hidden' : ''
})

const onKey = (e: KeyboardEvent) => {
    if (e.key === 'Escape') closeModal()
}

onMounted(() => window.addEventListener('keydown', onKey))
onUnmounted(() => window.removeEventListener('keydown', onKey))

const imageFile = ref<File | null>(null)
const imagePreview = ref<string | null>(null)

const onImageChange = (event: Event) => {
    const target = event.target as HTMLInputElement
    const file = target.files?.[0]

    if (!file) return

    imageFile.value = file
    imagePreview.value = URL.createObjectURL(file)

    form.clothes_image = file // 🔥 MUHIM
}


const form = useForm({
    clothes_name: '',
    code: '',
    clothes_image: null as File | null,
})


const submit = () => {
    form.post(route('clothes.store'), {
        forceFormData: true,
        onSuccess: () => {
            closeModal()
            form.reset()
            imageFile.value = null
            imagePreview.value = null
        }
    });
}


</script>

<template>

    <Head title="Clothes" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
            <div class="grid auto-rows-min gap-4 md:grid-cols-3">
                <div
                    class="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                    <h1 class="flex h-full items-center justify-center text-6xl font-bold">
                        {{ clothes_count }}
                    </h1>
                </div>
                <div
                    class="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                    <PlaceholderPattern />
                </div>
                <div
                    class="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                    <PlaceholderPattern />
                </div>
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
                                <input type="text" id="input-group-1"
                                    class="block w-full max-w-96 ps-9 pe-3 py-2 bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand px-3 py-2.5 shadow-xs placeholder:text-body"
                                    placeholder="Search">
                            </div>
                        </div>
                        <button @click="notify" class="px-4 py-2 bg-blue-600 text-white rounded">Show Toast</button>

                        <button class="ml-auto bg-green-500 text-white px-3 py-1 rounded" type="button"
                            @click="openModal">Qo'shish</button>
                    </div>

                    <table class="w-full text-sm text-left rtl:text-right text-body">
                        <thead
                            class="text-sm text-body bg-neutral-secondary-medium border-b border-t border-default-medium">
                            <tr>
                                <th scope="col" class="p-4">
                                    <div class="flex items-center">
                                        <input id="table-checkbox-12" type="checkbox" value=""
                                            class="w-4 h-4 border border-default-medium rounded-xs bg-neutral-secondary-medium focus:ring-2 focus:ring-brand-soft">
                                        <label for="table-checkbox-12" class="sr-only">Table checkbox</label>
                                    </div>
                                </th>
                                <th scope="col" class="px-6 py-3 font-medium">
                                    Clothes image
                                </th>
                                <th scope="col" class="px-6 py-3 font-medium">
                                    Clothes name
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
                            <tr v-for="item in clothes" :key="item.id"
                                class="bg-neutral-primary-soft border-b border-default hover:bg-neutral-secondary-medium">
                                <td class="w-4 p-4">
                                    <div class="flex items-center">
                                        <input id="table-checkbox-13" type="checkbox" value="{{ item.id }}"
                                            class="w-4 h-4 border border-default-medium rounded-xs bg-neutral-secondary-medium focus:ring-2 focus:ring-brand-soft">
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
                                    {{ new Date(item.created_at).toLocaleDateString() }}
                                </td>
                                <td class="px-6 py-4">
                                    <a href="#" class="font-medium text-fg-brand hover:underline">Edit</a>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <Teleport to="body">
                        <div v-if="isOpen" class="fixed inset-0 z-[999] flex items-center justify-center">
                            <!-- Overlay -->
                            <!-- <div class="absolute inset-0 bg-black/50" @click="closeModal"></div> -->
                            <div class="absolute inset-0 bg-black/50 pointer-events-none"></div>

                            <!-- Modal -->
                            <div class="relative bg-white rounded-lg w-full max-w-md p-6" @click.stop>
                                <h2 class="text-lg font-semibold mb-4">Clothes qo‘shish</h2>

                                <!-- FORM -->
                                <div class="space-y-4">
                                    <input type="text" placeholder="Clothes name" v-model="form.clothes_name"
                                        class="w-full border rounded px-3 py-2" />
                                    <input type="text" placeholder="Clothes Code" v-model="form.code"
                                        class="w-full border rounded px-3 py-2" />

                                    <label class="block">
                                        <span class="text-sm font-medium text-gray-700 mb-1 block">
                                            Clothes image
                                        </span>

                                        <div class="flex items-center justify-between border rounded px-3 py-2 cursor-pointer hover:border-green-500"
                                            @click="$refs.fileInput.click()">
                                            <span class="text-gray-500 text-sm truncate">
                                                {{ imageFile ? imageFile.name : 'Rasm tanlang' }}
                                            </span>

                                            <span class="text-sm text-green-600 font-medium">
                                                Browse
                                            </span>
                                        </div>

                                        <!-- REAL FILE INPUT (hidden) -->
                                        <input type="file" accept="image/*" ref="fileInput" class="hidden"
                                            @change="onImageChange" />
                                    </label>
                                    <div v-if="imagePreview"
                                        class="mt-4 rounded-lg border p-3 flex items-center gap-4 bg-gray-50">
                                        <img :src="imagePreview" alt="Preview" class="h-20 w-20 object-cover rounded" />

                                        <div class="flex-1">
                                            <p class="text-sm font-medium text-gray-800">
                                                {{ imageFile?.name }}
                                            </p>
                                            <p class="text-xs text-gray-500">
                                                {{ (imageFile?.size / 1024).toFixed(1) }} KB
                                            </p>
                                        </div>

                                        <button type="button" class="text-red-500 text-sm hover:underline"
                                            @click="imageFile = null; imagePreview = null">
                                            O‘chirish
                                        </button>
                                    </div>


                                </div>

                                <div class="flex justify-end gap-2 mt-6">
                                    <button @click="closeModal" class="px-4 py-2 bg-gray-200 rounded">
                                        Bekor qilish
                                    </button>
                                    <button class="px-4 py-2 bg-green-500 text-white rounded" @click="submit"
                                        :disabled="form.processing">
                                        {{ form.processing ? 'Saqlanmoqda...' : 'Saqlash' }}
                                    </button>

                                </div>

                                <button @click="closeModal"
                                    class="absolute top-3 right-3 text-gray-500 hover:text-black">
                                    ✕
                                </button>
                            </div>
                        </div>
                    </Teleport>
                </div>
            </div>
        </div>
    </AppLayout>
    <ToastContainer />

</template>

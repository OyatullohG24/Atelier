<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { ref, watch, onMounted, onUnmounted, computed } from 'vue'
import { useForm } from '@inertiajs/vue3'
import { route } from 'ziggy-js';
import { useToast } from '@/components/useToast/useToast';
import ToastContainer from '@/components/useToast/ToastContainer.vue';
import axios from 'axios'

const { success, error } = useToast();

interface Clothes {
    id: number;
    name: string;
    code: string;
    image: string;
    created_at: string;
    updated_at: string;
}

// Props ni qabul qilish
const { clothes, clothes_count, filters } = defineProps<{
    clothes: Clothes[];
    clothes_count: number;
    filters: {
        search?: string
    } // shu yerga qo'shasiz
}>();

// Search state
const searchQuery = ref(filters?.search || '')
let searchTimeout: ReturnType<typeof setTimeout> | null = null

// Search function
const performSearch = () => {
    router.get(
        route('clothes.index'),
        { search: searchQuery.value },
        {
            preserveState: true,
            preserveScroll: true,
            only: ['clothes', 'filters']
        }
    )
}

// Debounced search (500ms kutadi)
const handleSearch = () => {
    if (searchTimeout) {
        clearTimeout(searchTimeout)
    }

    searchTimeout = setTimeout(() => {
        performSearch()
    }, 500)
}

// Watch search input
watch(searchQuery, handleSearch)

// Clear search
const clearSearch = () => {
    searchQuery.value = ''
}

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

    form.clothes_image = file
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
            success("Bu muvaffaqiyatli xabar!");
        },
        onError: (errors) => {
            // Toast'da birinchi xatolikni ko'rsatish
            const firstError = Object.values(errors)[0] as string
            error(firstError)
        }
    });
}


const deleteOne = (id: number) => {
    if (!confirm('Rostdan o‘chirmoqchimisiz?')) return
    form.delete(route('clothes.destroy', id), {
        onSuccess: () => success('O‘chirildi')
    })
}

const selectedIds = ref<number[]>([])

const toggleAll = (e: Event) => {
    const checked = (e.target as HTMLInputElement).checked
    selectedIds.value = checked ? clothes.map(item => item.id) : []
}

const deleteForm = useForm({
    ids: [],
    _method: 'DELETE',
})

const deleteSelected = () => {
    if (!confirm('Rostdan o‘chirmoqchimisiz?')) return
    deleteForm.ids = selectedIds.value

    deleteForm.post(route('clothes.bulk-delete'), {
        onSuccess: () => {
            success('Tanlangan kiyimlar muvaffaqiyatli o‘chirildi')
            selectedIds.value = []
        },
        onError: () => {
            error('Xatolik yuz berdi')
        }
    })
}


const hasErrors = computed(() => Object.keys(form.errors).length > 0)

// Update uchun
const isEditOpen = ref(false)
const editingId = ref<number | null>(null)

const editForm = useForm({
    clothes_name: '',
    code: '',
    clothes_image: null as File | null,
})

const editImageFile = ref<File | null>(null)
const editImagePreview = ref<string | null>(null)

// Edit modal ochish va ma'lumot olish
const openEditModal = async (id: number) => {
    try {
        // Backend'dan ma'lumot olish
        const response = await axios.get(route('clothes.show', id))

        // Response strukturasini tekshirish
        const item = response.data.data || response.data // Ba'zida data ichida data bo'ladi

        console.log('Loaded item:', item) // Debug uchun

        // Form'ni to'ldirish
        editingId.value = item.id
        editForm.clothes_name = item.name
        editForm.code = item.code || ''
        editImagePreview.value = getImageUrl(item.image)

        // Eski rasmni tozalash
        editImageFile.value = null
        editForm.clothes_image = null

        // Modal'ni ochish
        isEditOpen.value = true

    } catch (err: any) {
        error('Ma\'lumotlarni yuklashda xatolik')
        console.error('Edit modal error:', err)
    }
}

// Edit modal yopish
const closeEditModal = () => {
    isEditOpen.value = false
    editingId.value = null
    editForm.reset()
    editImageFile.value = null
    editImagePreview.value = null
}

// Edit image change
const onEditImageChange = (event: Event) => {
    const target = event.target as HTMLInputElement
    const file = target.files?.[0]

    if (!file) return

    editImageFile.value = file
    editImagePreview.value = URL.createObjectURL(file)
    editForm.clothes_image = file
}

// Update submit - to'liq Axios bilan
const submitUpdate = async () => {
    if (!editingId.value) {
        error('ID topilmadi')
        return
    }

    try {
        const formData = new FormData()
        formData.append('clothes_name', editForm.clothes_name)
        formData.append('code', editForm.code || '')

        if (editForm.clothes_image) {
            formData.append('clothes_image', editForm.clothes_image)
        }

        formData.append('_method', 'PUT')
        editForm.processing = true

        await axios.post(
            route('clothes.update', editingId.value),
            formData,
            {
                headers: {
                    'Content-Type': 'multipart/form-data',
                }
            }
        )

        closeEditModal()
        success("Muvaffaqiyatli yangilandi!")

        // Oddiy reload
        window.location.reload()

    } catch (err: any) {
        if (err.response?.data?.errors) {
            const errors = err.response.data.errors
            const firstError = Object.values(errors)[0] as string[]
            error(firstError[0])
        } else {
            error('Xatolik yuz berdi')
        }
    } finally {
        editForm.processing = false
    }
}
// Watch edit modal
watch(isEditOpen, (val) => {
    document.body.style.overflow = val ? 'hidden' : ''
})

</script>

<template>

    <Head title="Clothes" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
            <div class="col-span-12">
                <!-- Metric Group Four -->
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 md:gap-6 xl:grid-cols-3">
                    <!-- Metric Item Start -->
                    <div
                        class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6">
                        <h4 class="text-title-sm font-bold text-gray-800 dark:text-white/90">
                            {{ clothes_count }}
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

                    <!-- Metric Item Start -->
                    <div
                        class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6">
                        <h4 class="text-title-sm font-bold text-gray-800 dark:text-white/90">
                            $234,210
                        </h4>

                        <div class="mt-4 flex items-end justify-between sm:mt-5">
                            <div>
                                <p class="text-theme-sm text-gray-700 dark:text-gray-400">
                                    Revenue Total
                                </p>
                            </div>

                            <div class="flex items-center gap-1">
                                <span
                                    class="flex items-center gap-1 rounded-full bg-success-50 px-2 py-0.5 text-theme-xs font-medium text-success-600 dark:bg-success-500/15 dark:text-success-500">
                                    +9.0%
                                </span>

                                <span class="text-theme-xs text-gray-500 dark:text-gray-400">
                                    From last month
                                </span>
                            </div>
                        </div>
                    </div>
                    <!-- Metric Item End -->

                    <!-- Metric Item Start -->
                    <div
                        class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6">
                        <h4 class="text-title-sm font-bold text-gray-800 dark:text-white/90">
                            874
                        </h4>

                        <div class="mt-4 flex items-end justify-between sm:mt-5">
                            <div>
                                <p class="text-theme-sm text-gray-700 dark:text-gray-400">
                                    Closed Deals
                                </p>
                            </div>

                            <div class="flex items-center gap-1">
                                <span
                                    class="flex items-center gap-1 rounded-full bg-error-50 px-2 py-0.5 text-theme-xs font-medium text-error-600 dark:bg-error-500/15 dark:text-error-500">
                                    -4.5%
                                </span>

                                <span class="text-theme-xs text-gray-500 dark:text-gray-400">
                                    From last month
                                </span>
                            </div>
                        </div>
                    </div>
                    <!-- Metric Item End -->
                </div>
                <!-- Metric Group Four -->
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
                                <input type="text" id="search-input" v-model="searchQuery"
                                    class="block w-full max-w-96 ps-9 pe-9 py-2 bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand shadow-xs placeholder:text-body"
                                    placeholder="Nom yoki kod bo'yicha qidiring..." />

                                <!-- Clear button -->
                                <button v-if="searchQuery" @click="clearSearch" type="button"
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
                        <button v-if="selectedIds.length" class="ml-4 bg-red-500 text-white px-3 py-1 rounded"
                            @click="deleteSelected">
                            Tanlanganlarni o‘chirish ({{ selectedIds.length }})
                        </button>
                        <button class="ml-auto bg-green-500 text-white px-3 py-1 rounded" type="button"
                            @click="openModal">Qo'shish</button>
                    </div>

                    <table class="w-full text-sm text-left rtl:text-right text-body">
                        <thead
                            class="text-sm text-body bg-neutral-secondary-medium border-b border-t border-default-medium">
                            <tr>
                                <th scope="col" class="p-4">
                                    <div class="flex items-center">
                                        <input type="checkbox" @change="toggleAll"
                                            :checked="selectedIds.length === clothes.length && clothes.length > 0" />
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
                                    Clothes code
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
                                        <input type="checkbox" :value="item.id" v-model="selectedIds"
                                            class="w-4 h-4 border rounded" />
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
                                    {{ item.code }}
                                </td>
                                <td class="px-6 py-4">
                                    {{ new Date(item.created_at).toLocaleDateString() }}
                                </td>
                                <td class="px-6 py-4">
                                    <button class="text-red-600 hover:text-red-800 transition-colors"
                                        @click="deleteOne(item.id)" title="O'chirish">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24"
                                            fill="none" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                    <button
                                        class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-md transition-all duration-200"
                                        @click="openEditModal(item)" title="Tahrirlash">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24"
                                            fill="none" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <!-- Bu modal create qilish uchun -->
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
                                        class="w-full border rounded px-3 py-2" :class="{
                                            'border-red-500 focus:ring-red-500': form.errors.code,
                                            'focus:ring-green-500': !form.errors.clothes_name
                                        }" />
                                    <p v-if="form.errors.clothes_name" class="text-red-500 text-sm mt-1">
                                        {{ form.errors.clothes_name }}
                                    </p>
                                    <input type="text" placeholder="Clothes Code" v-model="form.code"
                                        class="w-full border rounded px-3 py-2" :class="{
                                            'border-red-500 focus:ring-red-500': form.errors.code,
                                            'focus:ring-green-500': !form.errors.code
                                        }" />
                                    <p v-if="form.errors.clothes_name" class="text-red-500 text-sm mt-1">
                                        {{ form.errors.code }}
                                    </p>
                                    <label class="block">
                                        <span class="text-sm font-medium text-gray-700 mb-1 block">
                                            Clothes image
                                        </span>

                                        <div class="flex items-center justify-between border rounded px-3 py-2 cursor-pointer hover:border-green-500"
                                            :class="{
                                                'border-red-500 focus:ring-red-500': form.errors.code,
                                                'focus:ring-green-500': !form.errors.clothes_image
                                            }" @click="$refs.fileInput.click()">
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
                                    <p v-if="form.errors.clothes_image" class="text-red-500 text-sm mt-1">
                                        {{ form.errors.clothes_image }}
                                    </p>
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
                    <!-- Bu modal create qilish uchun -->

                    <!-- EDIT MODAL (yangi) -->
                    <Teleport to="body">
                        <div v-if="isEditOpen" class="fixed inset-0 z-[999] flex items-center justify-center">
                            <!-- Overlay -->
                            <div class="absolute inset-0 bg-black/50 pointer-events-none"></div>

                            <!-- Modal -->
                            <div class="relative bg-white rounded-lg w-full max-w-md p-6" @click.stop>
                                <h2 class="text-lg font-semibold mb-4">Clothes tahrirlash</h2>

                                <!-- FORM -->
                                <div class="space-y-4">
                                    <!-- Clothes name -->
                                    <div>
                                        <input type="text" placeholder="Clothes name" v-model="editForm.clothes_name"
                                            class="w-full border rounded px-3 py-2"
                                            :class="{ 'border-red-500': editForm.errors.clothes_name }" />
                                        <p v-if="editForm.errors.clothes_name" class="text-red-500 text-xs mt-1">
                                            {{ editForm.errors.clothes_name }}
                                        </p>
                                    </div>

                                    <!-- Code -->
                                    <div>
                                        <input type="text" placeholder="Clothes Code" v-model="editForm.code"
                                            class="w-full border rounded px-3 py-2"
                                            :class="{ 'border-red-500': editForm.errors.code }" />
                                        <p v-if="editForm.errors.code" class="text-red-500 text-xs mt-1">
                                            {{ editForm.errors.code }}
                                        </p>
                                    </div>

                                    <!-- Image upload -->
                                    <div>
                                        <label class="block">
                                            <span class="text-sm font-medium text-gray-700 mb-1 block">
                                                Clothes image
                                            </span>

                                            <div class="flex items-center justify-between border rounded px-3 py-2 cursor-pointer hover:border-green-500"
                                                :class="{ 'border-red-500': editForm.errors.clothes_image }"
                                                @click="$refs.editFileInput.click()">
                                                <span class="text-gray-500 text-sm truncate">
                                                    {{ editImageFile ? editImageFile.name : 'Rasm tanlang (ixtiyoriy)'
                                                    }}
                                                </span>
                                                <span class="text-sm text-green-600 font-medium">
                                                    Browse
                                                </span>
                                            </div>

                                            <input type="file" accept="image/*" ref="editFileInput" class="hidden"
                                                @change="onEditImageChange" />
                                        </label>

                                        <p v-if="editForm.errors.clothes_image" class="text-red-500 text-xs mt-1">
                                            {{ editForm.errors.clothes_image }}
                                        </p>
                                    </div>

                                    <!-- Image preview -->
                                    <div v-if="editImagePreview"
                                        class="mt-4 rounded-lg border p-3 flex items-center gap-4 bg-gray-50">
                                        <img :src="editImagePreview" alt="Preview"
                                            class="h-20 w-20 object-cover rounded" />

                                        <div class="flex-1">
                                            <p class="text-sm font-medium text-gray-800">
                                                {{ editImageFile?.name || 'Mavjud rasm' }}
                                            </p>
                                            <p v-if="editImageFile" class="text-xs text-gray-500">
                                                {{ (editImageFile.size / 1024).toFixed(1) }} KB
                                            </p>
                                        </div>

                                        <button v-if="editImageFile" type="button"
                                            class="text-red-500 text-sm hover:underline"
                                            @click="editImageFile = null; editImagePreview = null; editForm.clothes_image = null">
                                            O'chirish
                                        </button>
                                    </div>
                                </div>

                                <!-- Buttons -->
                                <div class="flex justify-end gap-2 mt-6">
                                    <button @click="closeEditModal"
                                        class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300">
                                        Bekor qilish
                                    </button>
                                    <button class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600"
                                        @click="submitUpdate" :disabled="editForm.processing">
                                        {{ editForm.processing ? 'Saqlanmoqda...' : 'Yangilash' }}
                                    </button>
                                </div>

                                <!-- Close X -->
                                <button @click="closeEditModal"
                                    class="absolute top-3 right-3 text-gray-500 hover:text-black">
                                    ✕
                                </button>
                            </div>
                        </div>
                    </Teleport>
                    <!-- EDIT MODAL (yangi) -->
                </div>
            </div>
        </div>
    </AppLayout>
    <ToastContainer />
</template>

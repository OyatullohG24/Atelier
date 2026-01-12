<script setup lang="ts">
import { Head, useForm, usePage } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { BreadcrumbItem } from '@/types';
import { ref } from 'vue';
import { route } from 'ziggy-js';
import { useToast } from '@/components/useToast/useToast';
import ToastContainer from '@/components/useToast/ToastContainer.vue';
import axios from 'axios';

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

const { success, error } = useToast();
const page = usePage();

const { materials, materials_count } = defineProps<{
    materials: Materials[];
    materials_count: number;
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

const isOpen = ref(false);

const openModal = () => {
    isOpen.value = true
}

const closeModal = () => {
    isOpen.value = false
}

const form = useForm({
    material_name: '',
    type: '',
    color_code: '',
    material_image: null as File | null,
    code: '',
    measurement: ''
})

const imageFile = ref<File | null>(null)
const imagePreview = ref<string | null>(null)

const onImageChange = (event: Event) => {
    const target = event.target as HTMLInputElement
    const file = target.files?.[0]

    if (!file) return

    imageFile.value = file
    imagePreview.value = URL.createObjectURL(file)

    form.material_image = file
}

const submit = () => {
    form.post(route('material.store'), {
        forceFormData: true,
        onSuccess: () => {
            closeModal();
            form.reset();
            imageFile.value = null;
            imagePreview.value = null;
            const flashMessage = page.props.flash as any
            if (flashMessage?.success) {
                success(flashMessage.success)
            }

        },
        onError: (errors) => {
            const firstError = Object.values(errors)[0] as string
            error(firstError)
        }
    })
}

const deleteOne = (id: number) => {
    if (!confirm('Rostdan ham o\'chirmoqchimisiz')) return
    form.delete(route('material.delete', id), {
        onSuccess: () => {
            const flashMessage = page.props.flash as any
            if (flashMessage?.success) {
                success(flashMessage.success)
            }
        }
    })
}

const isEditOpen = ref(false)
const editingId = ref<number | null>(null)

const editForm = useForm({
    material_name: '',
    type: '',
    color_code: '',
    material_image: null as File | null,
    code: '',
    measurement: ''
});

const editImageFile = ref<File | null>(null)
const editImagePreview = ref<string | null>(null)

// eslint-disable-next-line @typescript-eslint/no-unused-vars
const openEditModal = async (id: number) => {
    // TODO: Implement edit functionality
    try {
        const response = await axios.get(route('material.show', id))
        const item = response.data.data || response.data

        console.log(item);

        editingId.value = item.id
        editForm.material_name = item.name
        editForm.type = item.type
        editForm.color_code = item.color_code
        editForm.code = item.code
        editForm.measurement = item.measurement

        editImagePreview.value = getImageUrl(item.image)

        // Eski rasmni tozalash
        editImageFile.value = null
        editForm.material_image = null

        isEditOpen.value = true
    } catch (err: any) {
        error('Ma\'lumotlarni yuklashda xatolik')
        console.error('Edit modal error:', err)
    }
}


const closeEditModal = () => {
    isEditOpen.value = false
    editingId.value = null
    editForm.reset()
    editImageFile.value = null
    editImagePreview.value = null
}

const onEditImageChange = (event: Event) => {
    const target = event.target as HTMLInputElement
    const file = target.files?.[0]

    if (!file) return

    editImageFile.value = file
    editImagePreview.value = URL.createObjectURL(file)
    editForm.material_image = file
}

const submitUpdate = () => {
    return true
}

</script>

<template>

    <Head title="Materials" />
    <ToastContainer />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-flex flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
            <div class="col-span-12">
                <!-- Metric Group Four -->
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 md:gap-6 xl:grid-cols-3">
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
                        <button class="ml-auto bg-green-500 text-white px-3 py-1 rounded" @click="openModal"
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
                                <td class="px-6 py-4">
                                    <button @click="deleteOne(item.id)" title="O'chirish"
                                        class="p-1.5 text-red-600 hover:bg-red-50 rounded-md transition-all duration-200">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24"
                                            fill="none" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                    <button
                                        class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-md transition-all duration-200"
                                        @click="openEditModal(item.id)" title="Tahrirlash">
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

                    <Teleport to="body">
                        <!-- Teleport komponenti -->
                        <!--
                            Vue 3 Teleport: Bu elementni DOMda boshqa joyga (bu yerda <body>) chiqaradi.
                            Foydasi: modal va overlaylarni parent elementlar layout’idan mustaqil qilish.
                        -->
                        <div v-if="isOpen" class="fixed inset-0 z-[999] flex items-center justify-center">
                            <!-- Modal container -->
                            <!--
                                v-if="isOpen": modal faqat ochilganda DOM’da bo‘ladi
                                fixed inset-0: ekran bo‘ylab cho‘ziladi
                                z-[999]: ustunlik, boshqa elementlardan yuqorida ko‘rinadi
                                flex items-center justify-center: modalni ekran markaziga joylashtirish
                            -->

                            <!-- Overlay / fon qoplama -->
                            <div class="absolute inset-0 bg-black/50 pointer-events-none"></div>
                            <!--
                                absolute inset-0: parent (modal container) ichida ekran bo‘ylab cho‘ziladi
                                bg-black/50: 50% opasity qora fon (semi-transparent)
                                pointer-events-none: overlay ustiga bosilsa click modalga o‘tmaydi
                                Izoh: bu yerda "pointer-events-none" qo‘yilgan, shuning uchun modalni yonini bossa yopilmasligi mumkin
                            -->

                            <!-- Modal o‘zi -->
                            <div class="relative bg-white rounded-lg w-full max-w-md p-6" @click.stop>
                                <!--
                                    relative: ichki elementlarni absolute bilan joylashtirish uchun asos
                                    bg-white: fon rangi oq
                                    rounded-lg: radius bilan yumaloqlash
                                    w-full max-w-md: ekran kattaligiga qarab width, maksimal 28rem
                                    p-6: padding ichki bo‘shliq
                                    @click.stop: modal ustiga click qilinsa, event overlayga o‘tmaydi (modal yopilmasligi uchun)
                                -->
                                <h2 class="text-lg font-semibold mb-4">Material qo‘shish</h2>
                                <!-- Modal sarlavhasi -->
                                <button @click="closeModal"
                                    class="absolute top-2 right-4 text-gray-500 hover:text-black">X</button>
                                <!-- form -->
                                <div class="space-y-4">

                                    <input type="text" placeholder="Material name" v-model="form.material_name"
                                        class="w-full border rounded px-3 py-2" :class="{
                                            'border-red-500 focus:ring-red-500': form.errors.code,
                                            'focus:ring-green-500': !form.errors.material_name
                                        }">

                                    <p v-if="form.errors.material_name" class="text-red-500 text-sm mt-1">
                                        {{ form.errors.material_name }}
                                    </p>

                                    <input type="text" placeholder="Material color" v-model="form.color_code"
                                        class="w-full border rounded px-3 py-2" :class="{
                                            'border-red-500 focus:ring-red-500': form.errors.code,
                                            'focus:ring-green-500': !form.errors.color_code
                                        }">

                                    <p v-if="form.errors.color_code" class="text-red-500 text-sm mt-1">
                                        {{ form.errors.color_code }}
                                    </p>

                                    <input type="text" placeholder="Material code" v-model="form.code"
                                        class="w-full border rounded px-3 py-2" :class="{
                                            'border-red-500 focus:ring-red-500': form.errors.code,
                                            'focus:ring-green-500': !form.errors.code
                                        }">

                                    <p v-if="form.errors.code" class="text-red-500 text-sm mt-1">
                                        {{ form.errors.code }}
                                    </p>

                                    <!-- Material type select -->
                                    <div>
                                        <select v-model="form.type" class="w-full border rounded px-3 py-2">
                                            <option value="" disabled class="text-gray-200">Material type select
                                            </option>
                                            <option value="mato">Mato</option>
                                            <option value="tugma">Tugma</option>
                                            <option value="ip">Ip</option>
                                            <option value="zamok">Zamok</option>
                                        </select>
                                        <p v-if="form.errors.type" class="text-red-500 text-xs mt-1">{{ form.errors.type
                                        }}</p>
                                    </div>

                                    <!-- Material measurment select -->
                                    <div>
                                        <select v-model="form.measurement" class="w-full border rounded px-3 py-2">
                                            <option value="" disabled class="text-gray-200">Material measurement select
                                            </option>
                                            <option value="kg">kg</option>
                                            <option value="m">m</option>
                                            <option value="kv">kv</option>
                                            <option value="dona">dona</option>
                                        </select>
                                        <p v-if="form.errors.type" class="text-red-500 text-xs mt-1">{{
                                            form.errors.measurement
                                        }}</p>
                                    </div>

                                    <!-- Material image -->
                                    <label class="block">
                                        <span class="text-sm font-medium text-gray-700 mb-1 block">
                                            Clothes image
                                        </span>

                                        <div class="flex items-center justify-between border rounded px-3 py-2 cursor-pointer hover:border-green-500"
                                            :class="{
                                                'border-red-500 focus:ring-red-500': form.errors.code,
                                                'focus:ring-green-500': !form.errors.material_image
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
                                    <p v-if="form.errors.material_image" class="text-red-500 text-sm mt-1">
                                        {{ form.errors.material_image }}
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

                                    <div class="flex justify-end gap-2 mt-6">
                                        <button @click="closeModal" class="px-4 py-2 bg-gray-200 rounded">
                                            Bekor qilish
                                        </button>
                                        <button class="px-4 py-4 bg-green-400 text-white rounded" @click="submit"
                                            :disabled="form.processing">
                                            {{ form.processing ? 'Saqlanmoqda..' : 'Saqlash' }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </Teleport>

                    <Teleport to="body">
                        <div v-if="isEditOpen" class="fixed inset-0 z-[999] flex items-center justify-center">
                            <div class="absolute inset-0 bg-black/50 pointer-events-none"></div>

                            <!-- Modal -->
                            <div class="relative bg-white rounded-lg w-full max-w-md p-6" @click.stop>
                                <h2 class="text-lg font-semibold mb-4">Materialni tahrirlash</h2>

                                <div class="space-y-4">
                                    <div>
                                        <input type="text" placeholder="Material name" v-model="editForm.material_name"
                                            class="w-full border rounded px-3 py-2"
                                            :class="{ 'border-red-500': editForm.errors.material_name }" />
                                        <p v-if="editForm.errors.material_name" class="text-red-500 text-xs mt-1">
                                            {{ editForm.errors.material_name }}
                                        </p>
                                    </div>

                                    <div>
                                        <input type="text" placeholder="Material color" v-model="editForm.color_code"
                                            class="w-full border rounded px-3 py-2"
                                            :class="{ 'border-red-500': editForm.errors.color_code }" />
                                        <p v-if="editForm.errors.color_code" class="text-red-500 text-xs mt-1">
                                            {{ editForm.errors.color_code }}
                                        </p>
                                    </div>

                                    <div>
                                        <input type="text" placeholder="Material code" v-model="editForm.code"
                                            class="w-full border rounded px-3 py-2"
                                            :class="{ 'border-red-500': editForm.errors.code }" />
                                        <p v-if="editForm.errors.code" class="text-red-500 text-xs mt-1">
                                            {{ editForm.errors.code }}
                                        </p>
                                    </div>

                                    <div>
                                        <select v-model="editForm.measurement" class="w-full border rounded px-3 py-2">
                                            <option value="" class="text-gray-400">Material measurement select</option>
                                            <option value="kg">kg</option>
                                            <option value="m">m</option>
                                            <option value="kv">kv</option>
                                            <option value="dona">dona</option>
                                        </select>
                                        <p v-if="editForm.errors.measurement" class="texy-red-500 text-xs mt-1">
                                            {{ editForm.errors.measurement }}
                                        </p>
                                    </div>

                                    <div>
                                        <select v-model="editForm.type" class="w-full border rounded px-3 py-2">
                                            <option value="" class="text-gray-400">Material type select</option>
                                            <option value="mato">Mato</option>
                                            <option value="tugma">Tugma</option>
                                            <option value="ip">Ip</option>
                                            <option value="zamok">Zamok</option>
                                        </select>
                                        <p v-if="editForm.errors.measurement" class="texy-red-500 text-xs mt-1">
                                            {{ editForm.errors.measurement }}
                                        </p>
                                    </div>

                                    <div>
                                        <label class="block">
                                            <span class="text-sm font-medium text-gray-700 mb-1 block">
                                                Material image
                                            </span>

                                            <div class="flex items-center justify-between border rounded px-3 py-2 cursor-pointer hover:border-green-700"
                                                :class="{ 'border-red-500': editForm.material_image }"
                                                @click="$refs.editFileInput.click()">
                                                <span class="text-gray-500 text-sm truncate">
                                                    {{
                                                        editImageFile ? editImageFile.name : 'Rasm tanlang (ixtiyoriy)'
                                                    }}
                                                </span>
                                                <span class="text-sm text-green-600 font-medium">
                                                    Browse
                                                </span>

                                                <input type="file" accept="image/*" ref="editFileInput" class="hidden"
                                                    @change="onEditImageChange" />
                                            </div>
                                        </label>
                                        <p v-if="editForm.errors.material_image" class="text-red-500 text-xs mt-1">
                                            {{ editForm.errors.material_image }}
                                        </p>
                                    </div>

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
                                            @click="editImageFile = null; editImagePreview = null; editForm.material_image = null">
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
                                <button @click="closeEditModal"
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
</template>
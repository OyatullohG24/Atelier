<script setup lang="ts">
// ============================================
// 1. IMPORTLAR - Kerakli kutubxonalar va komponentlar
// ============================================

// AppLayout - asosiy sahifa strukturasi (navbar, sidebar, footer)
// Bu layout barcha sahifalar uchun umumiy dizaynni ta'minlaydi
import AppLayout from '@/layouts/AppLayout.vue';

// BreadcrumbItem - sahifa yo'lini ko'rsatish uchun tip (masalan: Home > Clothes)
import { type BreadcrumbItem } from '@/types';

// Inertia.js - Laravel bilan Vue.js ni bog'lash uchun
// Head - sahifa sarlavhasi (browser tab'dagi nom)
// router - sahifalar o'rtasida o'tish va ma'lumot yuklash
import { Head, router } from '@inertiajs/vue3';

// Vue.js reaktiv funksiyalar
// ref - o'zgaruvchilarni reaktiv qilish (o'zgarganda UI avtomatik yangilanadi)
// watch - o'zgaruvchilarni kuzatish (o'zgarganda biror funksiya ishga tushadi)
// onMounted - komponent yuklanganda 1 marta ishga tushadi
// onUnmounted - komponent o'chirilganda ishga tushadi (tozalash uchun)
import { ref, watch, onMounted, onUnmounted } from 'vue'

// useForm - Inertia formalarni boshqarish uchun
// Formani yuborish, xatoliklarni ko'rsatish, loading holatini boshqarish
import { useForm } from '@inertiajs/vue3'

// route - Laravel route nomlari bilan ishlash
// Masalan: route('clothes.index') => '/clothes' URL'ga aylanadi
import { route } from 'ziggy-js';

// Toast xabarlari - foydalanuvchiga success/error xabarlar ko'rsatish
// Masalan: "Muvaffaqiyatli saqlandi!" yoki "Xatolik yuz berdi!"
import { useToast } from '@/components/useToast/useToast';
import ToastContainer from '@/components/useToast/ToastContainer.vue';

// axios - HTTP so'rovlar yuborish (API bilan ishlash)
// GET, POST, PUT, DELETE so'rovlarni yuborish uchun
import axios from 'axios'

// ============================================
// 2. TOAST XABARLARI SOZLASH
// ============================================

// success va error funksiyalarni olish
// success('Xabar') - yashil xabar
// error('Xabar') - qizil xabar
const { success, error } = useToast();

// ============================================
// 3. TYPESCRIPT INTERFEYSI (Ma'lumot strukturasi)
// ============================================

// Har bir kiyim obyektining strukturasini belgilash
// Bu TypeScript'ga qanday ma'lumotlar kelishini aytadi
interface Clothes {
    id: number;              // Kiyim ID raqami (masalan: 1, 2, 3...)
    name: string;            // Kiyim nomi (masalan: "Ko'ylak", "Shim")
    code: string;            // Kiyim kodi (masalan: "KOY-001")
    image: string;           // Rasm yo'li (masalan: "clothes/image.jpg")
    created_at: string;      // Yaratilgan vaqt (masalan: "2024-01-15 10:30:00")
    updated_at: string;      // O'zgartirilgan vaqt
}

// ============================================
// 4. PROPS - Backend'dan kelgan ma'lumotlar
// ============================================

// Laravel Controller'dan kelgan props'larni qabul qilish
// Bu ma'lumotlar sahifa yuklanganda avtomatik keladi
const { clothes, clothes_count, filters } = defineProps<{
    clothes: Clothes[];      // Barcha kiyimlar ro'yxati (array)
    clothes_count: number;   // Jami kiyimlar soni (masalan: 150)
    filters: {               // Filtrlar obyekti
        search?: string      // Qidiruv matni (ixtiyoriy, bo'lmasligi ham mumkin)
    }
}>();

// ============================================
// 5. QIDIRUV (SEARCH) FUNKSIYALARI
// ============================================

// Qidiruv matnini saqlash
// Agar filters'da search bo'lsa, uni olish, aks holda bo'sh string
// ref() - bu o'zgaruvchini reaktiv qiladi (o'zgarganda UI yangilanadi)
const searchQuery = ref(filters?.search || '')

// Timeout o'zgaruvchisi - debounce uchun
// Debounce - har harfda emas, yozib bo'lgandan keyin so'rov yuborish
let searchTimeout: ReturnType<typeof setTimeout> | null = null

// Qidiruv bajarish funksiyasi
// Bu funksiya backend'ga GET so'rov yuboradi va natijani oladi
const performSearch = () => {
    router.get(
        route('clothes.index'),              // Laravel route nomi (/clothes)
        { search: searchQuery.value },       // Qidiruv parametri (?search=ko'ylak)
        {
            preserveState: true,             // Sahifa holatini saqlab qolish (scroll, formalar)
            preserveScroll: true,            // Scroll pozitsiyasini saqlab qolish
            only: ['clothes', 'filters']     // Faqat shu ma'lumotlarni yangilash (tezroq)
        }
    )
}

// Debounced search - har harfda emas, 500ms kutib so'rov yuboradi
// SABAB: Har harfda so'rov yuborish server'ni yuklatadi
// Masalan: foydalanuvchi "kurtka" yozsa:
// - k -> kutadi
// - u -> kutadi
// - r -> kutadi
// - t -> kutadi
// - k -> kutadi
// - a -> 500ms kutadi -> SO'ROV YUBORILADI
const handleSearch = () => {
    // Agar oldingi timeout mavjud bo'lsa, uni bekor qilish
    // SABAB: Yangi harf kiritilganda eski kutishni to'xtatish kerak
    if (searchTimeout) {
        clearTimeout(searchTimeout)
    }

    // 500ms kutib qidiruvni bajarish
    searchTimeout = setTimeout(() => {
        performSearch()
    }, 500)
}

// searchQuery o'zgarganda handleSearch ishga tushadi
// watch() - birinchi argument: kuzatiladigan o'zgaruvchi
//         - ikkinchi argument: o'zgarganda ishlaydigan funksiya
watch(searchQuery, handleSearch)

// Qidiruvni tozalash (X tugmasi bosilganda)
const clearSearch = () => {
    searchQuery.value = ''  // Bo'sh string qo'yilganda watch ishga tushadi va qidiruv tozalanadi
}

// ============================================
// 6. BREADCRUMBS - Sahifa yo'li
// ============================================

// Sahifa tepasidagi yo'l (Home > Clothes)
// Bu foydalanuvchiga qayerda ekanini ko'rsatadi
const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Clothes',     // Ko'rinadigan nom
        href: '/clothes',     // Bosiladigan link
    },
];

// ============================================
// 7. RASM URL YARATISH
// ============================================

// Backend'dan kelgan rasm yo'lini to'liq URL ga aylantirish
// SABAB: Backend faqat "clothes/image.jpg" yuboradi
//        Lekin browser'ga "/storage/clothes/image.jpg" kerak
// Masalan: "images/shirt.jpg" => "/storage/images/shirt.jpg"
const getImageUrl = (path: string) => {
    return `/storage/${path}`;
};

// ============================================
// 8. CREATE MODAL (Yangi kiyim qo'shish)
// ============================================

// Modal ochiq yoki yopiq ekanini boshqarish
// ref(false) - boshlang'ichda yopiq
const isOpen = ref(false)

// Modal ochish funksiyasi
const openModal = () => {
    isOpen.value = true  // true qilinganda modal ko'rinadi
}

// Modal yopish funksiyasi
const closeModal = () => {
    isOpen.value = false  // false qilinganda modal yashirinadi
}

// Modal ochilganda sahifani scroll qilish to'xtatish
// SABAB: Modal ochiq bo'lganda orqa fon scroll bo'lmasligi kerak
watch(isOpen, (val) => {
    // Agar modal ochiq bo'lsa (val=true), body scroll'ini o'chirish
    // Aks holda (val=false), scroll'ni yoqish
    document.body.style.overflow = val ? 'hidden' : ''
})

// Escape tugmasi bosilganda modalni yopish
// SABAB: Foydalanuvchi Esc bosib tezda yopishi mumkin
const onKey = (e: KeyboardEvent) => {
    if (e.key === 'Escape') closeModal()
}

// Komponent yuklanganda event listener qo'shish
// SABAB: Keyboard hodisalarini eshitish uchun
onMounted(() => window.addEventListener('keydown', onKey))

// Komponent o'chirilganda event listener o'chirish
// SABAB: Xotira tozalash (memory leak oldini olish)
onUnmounted(() => window.removeEventListener('keydown', onKey))

// ============================================
// 9. RASM TANLASH (CREATE uchun)
// ============================================

// Tanlangan rasm fayli
// File | null - fayl yoki null bo'lishi mumkin
const imageFile = ref<File | null>(null)

// Rasm ko'rinishi (preview)
// URL string yoki null
const imagePreview = ref<string | null>(null)

// Rasm tanlaganda ishlaydigan funksiya
// Event - input'dan kelgan hodisa
const onImageChange = (event: Event) => {
    // Event'ni HTMLInputElement tipiga o'tkazish
    const target = event.target as HTMLInputElement

    // Birinchi tanlangan faylni olish
    // files?.[0] - agar files mavjud bo'lsa, birinchi elementni ol
    const file = target.files?.[0]

    // Agar fayl yo'q bo'lsa, to'xtatish
    if (!file) return

    // Faylni saqlash
    imageFile.value = file

    // Rasm preview yaratish (blob URL)
    // URL.createObjectURL() - fayldan vaqtinchalik URL yaratadi
    // Masalan: "blob:http://localhost:3000/abc123"
    imagePreview.value = URL.createObjectURL(file)

    // Formga faylni qo'shish
    form.clothes_image = file
}

// ============================================
// 10. CREATE FORM - Yangi kiyim qo'shish formasi
// ============================================

// Inertia useForm - forma ma'lumotlarini boshqarish
// Bu avtomatik ravishda:
// - Ma'lumotlarni saqlaydi
// - Xatoliklarni boshqaradi
// - Loading holatini kuzatadi
const form = useForm({
    clothes_name: '',                    // Kiyim nomi (boshlang'ichda bo'sh)
    code: '',                            // Kiyim kodi (boshlang'ichda bo'sh)
    clothes_image: null as File | null,  // Kiyim rasmi (boshlang'ichda null)
})

// ============================================
// 11. FORMA YUBORISH (CREATE)
// ============================================

// Yangi kiyim qo'shish funksiyasi
const submit = () => {
    // POST so'rov yuborish
    form.post(route('clothes.store'), {  // Laravel route: POST /clothes
        forceFormData: true,              // File yuklash uchun FormData ishlatish

        // Muvaffaqiyatli saqlanganda ishlaydigan funksiya
        onSuccess: () => {
            closeModal()                  // Modalni yopish
            form.reset()                  // Formani tozalash (barcha maydonlar bo'sh)
            imageFile.value = null        // Rasm faylni tozalash
            imagePreview.value = null     // Preview'ni tozalash
            success("Bu muvaffaqiyatli xabar!");  // Yashil toast xabar
        },

        // Xatolik yuz berganda ishlaydigan funksiya
        onError: (errors) => {
            // errors obyekti: { clothes_name: "Nom kiritilmagan", code: "Kod noto'g'ri" }
            // Birinchi xatolikni olish
            const firstError = Object.values(errors)[0] as string
            // Qizil toast xabar ko'rsatish
            error(firstError)
        }
    });
}

// ============================================
// 12. BITTA KIYIMNI O'CHIRISH
// ============================================

// Tanlangan kiyimni o'chirish
// id - o'chiriladigan kiyim ID'si
const deleteOne = (id: number) => {
    // Tasdiqlash dialogini ko'rsatish
    // SABAB: Tasodifan o'chirishning oldini olish
    if (!confirm('Rostdan o\'chirmoqchimisiz?')) return

    // DELETE so'rov yuborish
    // Laravel route: DELETE /clothes/{id}
    form.delete(route('clothes.destroy', id), {
        onSuccess: () => success('O\'chirildi')  // Muvaffaqiyatli toast
    })
}

// ============================================
// 13. KO'P NARSANI O'CHIRISH (BULK DELETE)
// ============================================

// Tanlangan ID'lar ro'yxati
// Masalan: [1, 3, 5, 7] - bu ID'lar tanlangan
const selectedIds = ref<number[]>([])

// "Hammasini tanlash" checkbox'i
// Event - checkbox'dan kelgan hodisa
const toggleAll = (e: Event) => {
    // Checkbox belgilangan yoki yo'qligini tekshirish
    const checked = (e.target as HTMLInputElement).checked

    // Agar belgilangan bo'lsa - barcha ID'larni olish
    // clothes.map(item => item.id) - barcha kiyimlarning ID'larini olish
    // Aks holda - bo'sh array
    selectedIds.value = checked ? clothes.map(item => item.id) : []
}

// Bulk delete uchun alohida forma
// SABAB: Bir nechta ID'ni yuborish kerak
const deleteForm = useForm({
    ids: [],           // O'chiriladigan ID'lar ro'yxati
    _method: 'DELETE', // Laravel'da DELETE method (chunki POST yuboramiz)
})

// Tanlanganlarni o'chirish
const deleteSelected = () => {
    // Tasdiqlash
    if (!confirm('Rostdan o\'chirmoqchimisiz?')) return

    // Tanlangan ID'larni formaga qo'yish
    deleteForm.ids = selectedIds.value

    // POST so'rov yuborish (Laravel bulk-delete route'iga)
    // Laravel'da _method: DELETE ko'rib DELETE'ga o'zgartiradi
    deleteForm.post(route('clothes.bulk-delete'), {
        onSuccess: () => {
            success('Tanlangan kiyimlar muvaffaqiyatli o\'chirildi')
            selectedIds.value = []  // Tanlovni tozalash
        },
        onError: () => {
            error('Xatolik yuz berdi')
        }
    })
}

// ============================================
// 14. EDIT MODAL - Kiyimni tahrirlash
// ============================================

// Edit modal ochiq yoki yopiq
const isEditOpen = ref(false)

// Tahrirlanayotgan kiyim ID'si
// null - hech narsa tahrirlanmayapti
const editingId = ref<number | null>(null)

// Edit uchun forma
// CREATE formasidan alohida, chunki boshqa ma'lumotlar bo'lishi mumkin
const editForm = useForm({
    clothes_name: '',
    code: '',
    clothes_image: null as File | null,
})

// Edit uchun rasm
const editImageFile = ref<File | null>(null)
const editImagePreview = ref<string | null>(null)

// ============================================
// 15. EDIT MODAL OCHISH VA MA'LUMOT YUKLASH
// ============================================

// Edit modalni ochish va ma'lumotlarni backend'dan olish
// id - tahrirlanadigan kiyim ID'si
const openEditModal = async (id: number) => {
    try {
        // Backend'dan kiyim ma'lumotlarini olish (GET so'rov)
        // Laravel route: GET /clothes/{id}
        const response = await axios.get(route('clothes.show', id))

        // Response strukturasini tekshirish
        // Ba'zan: { data: { id: 1, name: "..." } }
        // Ba'zan: { id: 1, name: "..." }
        const item = response.data.data || response.data

        console.log('Loaded item:', item)  // Debug uchun (console'da ko'rish)

        // Form'ni to'ldirish
        editingId.value = item.id
        editForm.clothes_name = item.name
        editForm.code = item.code || ''  // Agar code yo'q bo'lsa, bo'sh string
        editImagePreview.value = getImageUrl(item.image)  // Mavjud rasmni ko'rsatish

        // Yangi rasm tanlanmagan (boshlang'ichda)
        editImageFile.value = null
        editForm.clothes_image = null

        // Modal'ni ochish
        isEditOpen.value = true

    } catch (err: any) {
        // Xatolik yuz bersa
        error('Ma\'lumotlarni yuklashda xatolik')
        console.error('Edit modal error:', err)  // Console'da xatolikni ko'rish
    }
}

// ============================================
// 16. EDIT MODAL YOPISH
// ============================================

const closeEditModal = () => {
    isEditOpen.value = false       // Modal'ni yopish
    editingId.value = null         // ID'ni tozalash
    editForm.reset()               // Formani tozalash
    editImageFile.value = null     // Rasm faylni tozalash
    editImagePreview.value = null  // Preview'ni tozalash
}

// ============================================
// 17. EDIT UCHUN RASM TANLASH
// ============================================

const onEditImageChange = (event: Event) => {
    const target = event.target as HTMLInputElement
    const file = target.files?.[0]

    if (!file) return

    editImageFile.value = file
    editImagePreview.value = URL.createObjectURL(file)
    editForm.clothes_image = file
}

// ============================================
// 18. YANGILASH (UPDATE) FUNKSIYASI
// ============================================

// Kiyimni yangilash - to'liq Axios bilan
// SABAB: Inertia PUT so'rovda file yuklashda muammo bo'lishi mumkin
//        Shuning uchun Axios ishlatamiz
const submitUpdate = async () => {
    // ID tekshirish
    if (!editingId.value) {
        error('ID topilmadi')
        return
    }

    try {
        // FormData yaratish (file yuklash uchun)
        // FormData - file va matnni birgalikda yuborish uchun
        const formData = new FormData()
        formData.append('clothes_name', editForm.clothes_name)
        formData.append('code', editForm.code || '')

        // Agar yangi rasm tanlangan bo'lsa, qo'shish
        if (editForm.clothes_image) {
            formData.append('clothes_image', editForm.clothes_image)
        }

        // Laravel PUT method uchun
        // SABAB: FormData faqat POST yuboradi, lekin Laravel'ga PUT kerak
        formData.append('_method', 'PUT')

        // Processing holatini o'rnatish (loading ko'rsatish uchun)
        editForm.processing = true

        // POST so'rov yuborish (Laravel'da _method: PUT ko'rib PUT'ga o'zgartiradi)
        await axios.post(
            route('clothes.update', editingId.value),  // Laravel route: PUT /clothes/{id}
            formData,
            {
                headers: {
                    'Content-Type': 'multipart/form-data',  // File yuklash uchun
                }
            }
        )

        // Muvaffaqiyatli bo'lsa
        closeEditModal()
        success("Muvaffaqiyatli yangilandi!")

        // Sahifani yangilash
        // SABAB: Inertia avtomatik yangilamaydi, chunki Axios ishlatdik
        window.location.reload()

    } catch (err: any) {
        // Xatoliklarni qayta ishlash
        if (err.response?.data?.errors) {
            // Laravel validation xatoliklari
            // errors: { clothes_name: ["Nom kiritilmagan"], code: ["Kod noto'g'ri"] }
            const errors = err.response.data.errors
            const firstError = Object.values(errors)[0] as string[]
            error(firstError[0])  // Birinchi xatolikni ko'rsatish
        } else {
            // Boshqa xatoliklar
            error('Xatolik yuz berdi')
        }
    } finally {
        // Har qanday holatda processing'ni o'chirish
        // SABAB: Xatolik bo'lsa ham, loading to'xtatilishi kerak
        editForm.processing = false
    }
}

// ============================================
// 19. EDIT MODAL BODY SCROLL
// ============================================

// Edit modal ochilganda scroll'ni to'xtatish
// CREATE modal bilan bir xil
watch(isEditOpen, (val) => {
    document.body.style.overflow = val ? 'hidden' : ''
})

</script>

<template>
    <!-- ============================================ -->
    <!-- 20. SAHIFA HEAD - Brauzer tab sarlavhasi -->
    <!-- ============================================ -->
    <!-- Bu brauzer tab'ida "Clothes" deb ko'rinadi -->

    <Head title="Clothes" />

    <!-- ============================================ -->
    <!-- 21. ASOSIY LAYOUT -->
    <!-- ============================================ -->
    <!-- AppLayout - navbar, sidebar, footer bilan -->
    <!-- :breadcrumbs - sahifa yo'lini uzatish -->
    <AppLayout :breadcrumbs="breadcrumbs">
        <!-- Container - asosiy kontent uchun -->
        <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">

            <!-- ============================================ -->
            <!-- 22. STATISTIKA KARTOCHKASI -->
            <!-- ============================================ -->
            <div class="col-span-12">
                <!-- Grid - 3 ustunli (xl ekranda) -->
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 md:gap-6 xl:grid-cols-3">
                    <!-- Statistika kartasi -->
                    <div
                        class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6">
                        <!-- Jami kiyimlar soni -->
                        <!-- {{ clothes_count }} - Laravel'dan kelgan son -->
                        <h4 class="text-title-sm font-bold text-gray-800 dark:text-white/90">
                            {{ clothes_count }}
                        </h4>

                        <div class="mt-4 flex items-end justify-between sm:mt-5">
                            <div>
                                <p class="text-theme-sm text-gray-700 dark:text-gray-400">
                                    Active Deal
                                </p>
                            </div>

                            <!-- O'sish foizi -->
                            <div class="flex items-center gap-1">
                                <span
                                    class="flex items-center gap-1 rounded-full bg-success-50 px-2 py-0.5 text-theme-xs font-medium text-success-600">
                                    +20%
                                </span>
                                <span class="text-theme-xs text-gray-500 dark:text-gray-400">
                                    From last month
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ============================================ -->
            <!-- 23. ASOSIY JADVAL CONTAINER -->
            <!-- ============================================ -->
            <div
                class="relative min-h-[100vh] flex-1 rounded-xl border border-sidebar-border/70 md:min-h-min dark:border-sidebar-border">
                <div
                    class="relative overflow-x-auto bg-neutral-primary-soft shadow-xs rounded-base border border-default">

                    <!-- ============================================ -->
                    <!-- 24. QIDIRUV VA TUGMALAR QATORI -->
                    <!-- ============================================ -->
                    <div class="flex items-center px-4">
                        <!-- Qidiruv inputi -->
                        <div class="p-4">
                            <label for="input-group-1" class="sr-only">Search</label>
                            <div class="relative">
                                <!-- Qidiruv ikoni (chap tomonda) -->
                                <div class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none">
                                    <svg class="w-4 h-4 text-body" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                                        width="24" height="24" fill="none" viewBox="0 0 24 24">
                                        <path stroke="currentColor" stroke-linecap="round" stroke-width="2"
                                            d="m21 21-3.5-3.5M17 10a7 7 0 1 1-14 0 7 7 0 0 1 14 0Z" />
                                    </svg>
                                </div>

                                <!-- Qidiruv input maydoni -->
                                <!-- v-model="searchQuery" - ikki tomonlama bog'lanish -->
                                <!-- Foydalanuvchi yozganda searchQuery o'zgaradi -->
                                <input type="text" id="search-input" v-model="searchQuery"
                                    class="block w-full max-w-96 ps-9 pe-9 py-2 bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand shadow-xs placeholder:text-body"
                                    placeholder="Nom yoki kod bo'yicha qidiring..." />

                                <!-- Tozalash tugmasi (X) -->
                                <!-- v-if="searchQuery" - faqat matn kiritilganda ko'rinadi -->
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

                        <!-- Tanlanganlarni o'chirish tugmasi -->
                        <!-- v-if="selectedIds.length" - faqat tanlangan bo'lsa ko'rinadi -->
                        <button v-if="selectedIds.length" class="ml-4 bg-red-500 text-white px-3 py-1 rounded"
                            @click="deleteSelected">
                            Tanlanganlarni o'chirish ({{ selectedIds.length }})
                        </button>

                        <!-- Yangi kiyim qo'shish tugmasi -->
                        <!-- ml-auto - o'ng tomonga surish -->
                        <button class="ml-auto bg-green-500 text-white px-3 py-1 rounded" type="button"
                            @click="openModal">
                            Qo'shish
                        </button>
                    </div>

                    <!-- ============================================ -->
                    <!-- 25. JADVAL (TABLE) -->
                    <!-- ============================================ -->
                    <table class="w-full text-sm text-left rtl:text-right text-body">
                        <!-- Jadval sarlavhalari (thead) -->
                        <thead
                            class="text-sm text-body bg-neutral-secondary-medium border-b border-t border-default-medium">
                            <tr>
                                <!-- "Hammasini tanlash" checkbox -->
                                <th scope="col" class="p-4">
                                    <div class="flex items-center">
                                        <!-- @change="toggleAll" - checkbox o'zgarganda toggleAll ishga tushadi -->
                                        <!-- :checked - barcha tanlangan bo'lsa, checkbox belgilangan -->
                                        <input type="checkbox" @change="toggleAll"
                                            :checked="selectedIds.length === clothes.length && clothes.length > 0" />
                                        <label for="table-checkbox-12" class="sr-only">Table checkbox</label>
                                    </div>
                                </th>
                                <!-- Ustun sarlavhalari -->
                                <th scope="col" class="px-6 py-3 font-medium">Clothes image</th>
                                <th scope="col" class="px-6 py-3 font-medium">Clothes name</th>
                                <th scope="col" class="px-6 py-3 font-medium">Clothes code</th>
                                <th scope="col" class="px-6 py-3 font-medium">Created at</th>
                                <th scope="col" class="px-6 py-3 font-medium">Action</th>
                            </tr>
                        </thead>

                        <!-- Jadval qatorlari (tbody) -->
                        <tbody>
                            <!-- v-for="item in clothes" - har bir kiyim uchun qator yaratish -->
                            <!-- :key="item.id" - Vue.js uchun unique kalit (zarur!) -->
                            <tr v-for="item in clothes" :key="item.id"
                                class="bg-neutral-primary-soft border-b border-default hover:bg-neutral-secondary-medium">

                                <!-- Tanlash checkbox -->
                                <td class="w-4 p-4">
                                    <div class="flex items-center">
                                        <!-- :value="item.id" - checkbox qiymati -->
                                        <!-- v-model="selectedIds" - tanlanganda selectedIds'ga qo'shiladi -->
                                        <input type="checkbox" :value="item.id" v-model="selectedIds"
                                            class="w-4 h-4 border rounded" />
                                        <label for="table-checkbox-13" class="sr-only">Table checkbox</label>
                                    </div>
                                </td>

                                <!-- Rasm -->
                                <th scope="row" class="px-6 py-4 font-medium text-heading whitespace-nowrap">
                                    <!-- :src="getImageUrl(item.image)" - rasm URL'i -->
                                    <!-- :alt="item.name" - rasm yuklanmasa, nom ko'rinadi -->
                                    <img :src="getImageUrl(item.image)" :alt="item.name"
                                        class="h-16 w-16 rounded-lg object-cover" />
                                </th>

                                <!-- Nomi -->
                                <!-- {{ item.name }} - kiyim nomi -->
                                <td class="px-6 py-4">{{ item.name }}</td>

                                <!-- Kodi -->
                                <td class="px-6 py-4">{{ item.code }}</td>

                                <!-- Yaratilgan sana -->
                                <!-- new Date().toLocaleDateString() - sanani formatlash -->
                                <td class="px-6 py-4">
                                    {{ new Date(item.created_at).toLocaleDateString() }}
                                </td>

                                <!-- Amallar (o'chirish va tahrirlash) -->
                                <td class="px-6 py-4">
                                    <!-- O'chirish tugmasi -->
                                    <!-- @click="deleteOne(item.id)" - bosilganda o'chirish -->
                                    <button class="text-red-600 hover:text-red-800 transition-colors"
                                        @click="deleteOne(item.id)" title="O'chirish">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24"
                                            fill="none" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>

                                    <!-- Tahrirlash tugmasi -->
                                    <!-- @click="openEditModal(item.id)" - bosilganda edit modal ochiladi -->
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

                    <!-- ============================================ -->
                    <!-- 26. CREATE MODAL - Yangi kiyim qo'shish oynasi -->
                    <!-- ============================================ -->
                    <!-- Teleport to="body" - modal'ni body'ga ko'chirish -->
                    <!-- SABAB: z-index muammolarini oldini olish -->
                    <Teleport to="body">
                        <!-- v-if="isOpen" - faqat isOpen=true bo'lganda ko'rinadi -->
                        <div v-if="isOpen" class="fixed inset-0 z-[999] flex items-center justify-center">
                            <!-- Orqa fon (overlay) -->
                            <!-- pointer-events-none - bosilmaydigan qilish -->
                            <div class="absolute inset-0 bg-black/50 pointer-events-none"></div>

                            <!-- Modal oynasi -->
                            <!-- @click.stop - overlay bosilganda modal yopilmasligi uchun -->
                            <div class="relative bg-white rounded-lg w-full max-w-md p-6" @click.stop>
                                <h2 class="text-lg font-semibold mb-4">Clothes qo'shish</h2>

                                <!-- FORMA -->
                                <div class="space-y-4">
                                    <!-- Kiyim nomi -->
                                    <!-- v-model="form.clothes_name" - ikki tomonlama bog'lanish -->
                                    <input type="text" placeholder="Clothes name" v-model="form.clothes_name"
                                        class="w-full border rounded px-3 py-2" :class="{
                                            'border-red-500 focus:ring-red-500': form.errors.clothes_name,
                                            'focus:ring-green-500': !form.errors.clothes_name
                                        }" />
                                    <!-- Xatolik xabari -->
                                    <!-- v-if="form.errors.clothes_name" - xatolik bo'lsa ko'rinadi -->
                                    <p v-if="form.errors.clothes_name" class="text-red-500 text-sm mt-1">
                                        {{ form.errors.clothes_name }}
                                    </p>

                                    <!-- Kiyim kodi -->
                                    <input type="text" placeholder="Clothes Code" v-model="form.code"
                                        class="w-full border rounded px-3 py-2" :class="{
                                            'border-red-500 focus:ring-red-500': form.errors.code,
                                            'focus:ring-green-500': !form.errors.code
                                        }" />
                                    <p v-if="form.errors.code" class="text-red-500 text-sm mt-1">
                                        {{ form.errors.code }}
                                    </p>

                                    <!-- Rasm yuklash -->
                                    <label class="block">
                                        <span class="text-sm font-medium text-gray-700 mb-1 block">
                                            Clothes image
                                        </span>

                                        <!-- Fake input - ko'rinish uchun -->
                                        <!-- @click="$refs.fileInput.click()" - bosilganda haqiqiy input ochiladi -->
                                        <div class="flex items-center justify-between border rounded px-3 py-2 cursor-pointer hover:border-green-500"
                                            :class="{
                                                'border-red-500 focus:ring-red-500': form.errors.clothes_image,
                                                'focus:ring-green-500': !form.errors.clothes_image
                                            }" @click="$refs.fileInput.click()">
                                            <span class="text-gray-500 text-sm truncate">
                                                <!-- Agar fayl tanlangan bo'lsa, nomini ko'rsatish -->
                                                {{ imageFile ? imageFile.name : 'Rasm tanlang' }}
                                            </span>

                                            <span class="text-sm text-green-600 font-medium">
                                                Browse
                                            </span>
                                        </div>

                                        <!-- REAL FILE INPUT (hidden) -->
                                        <!-- ref="fileInput" - $refs.fileInput orqali kirish -->
                                        <!-- @change="onImageChange" - fayl tanlanganda ishga tushadi -->
                                        <input type="file" accept="image/*" ref="fileInput" class="hidden"
                                            @change="onImageChange" />
                                    </label>
                                    <p v-if="form.errors.clothes_image" class="text-red-500 text-sm mt-1">
                                        {{ form.errors.clothes_image }}
                                    </p>

                                    <!-- Rasm preview -->
                                    <!-- v-if="imagePreview" - fayl tanlanganda ko'rinadi -->
                                    <div v-if="imagePreview"
                                        class="mt-4 rounded-lg border p-3 flex items-center gap-4 bg-gray-50">
                                        <!-- Preview rasm -->
                                        <img :src="imagePreview" alt="Preview" class="h-20 w-20 object-cover rounded" />

                                        <div class="flex-1">
                                            <!-- Fayl nomi -->
                                            <p class="text-sm font-medium text-gray-800">
                                                {{ imageFile?.name }}
                                            </p>
                                            <!-- Fayl hajmi (KB da) -->
                                            <p class="text-xs text-gray-500">
                                                {{ (imageFile?.size / 1024).toFixed(1) }} KB
                                            </p>
                                        </div>

                                        <!-- O'chirish tugmasi -->
                                        <button type="button" class="text-red-500 text-sm hover:underline"
                                            @click="imageFile = null; imagePreview = null">
                                            O'chirish
                                        </button>
                                    </div>
                                </div>

                                <!-- Tugmalar -->
                                <div class="flex justify-end gap-2 mt-6">
                                    <!-- Bekor qilish -->
                                    <button @click="closeModal" class="px-4 py-2 bg-gray-200 rounded">
                                        Bekor qilish
                                    </button>
                                    <!-- Saqlash -->
                                    <!-- :disabled="form.processing" - yuborilayotganda o'chiriladi -->
                                    <button class="px-4 py-2 bg-green-500 text-white rounded" @click="submit"
                                        :disabled="form.processing">
                                        {{ form.processing ? 'Saqlanmoqda...' : 'Saqlash' }}
                                    </button>
                                </div>

                                <!-- Yopish tugmasi (X) -->
                                <button @click="closeModal"
                                    class="absolute top-3 right-3 text-gray-500 hover:text-black">
                                    ✕
                                </button>
                            </div>
                        </div>
                    </Teleport>

                    <!-- ============================================ -->
                    <!-- 27. EDIT MODAL - Kiyimni tahrirlash oynasi -->
                    <!-- ============================================ -->
                    <Teleport to="body">
                        <!-- v-if="isEditOpen" - faqat edit modal ochiq bo'lganda -->
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
                                        <!-- editForm - edit uchun alohida forma -->
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

                                            <!-- Fake input -->
                                            <!-- $refs.editFileInput - edit uchun alohida ref -->
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

                                            <!-- Real input -->
                                            <!-- @change="onEditImageChange" - edit uchun alohida funksiya -->
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
                                                <!-- Yangi fayl yoki mavjud rasm -->
                                                {{ editImageFile?.name || 'Mavjud rasm' }}
                                            </p>
                                            <!-- Fayl hajmi (faqat yangi fayl uchun) -->
                                            <p v-if="editImageFile" class="text-xs text-gray-500">
                                                {{ (editImageFile.size / 1024).toFixed(1) }} KB
                                            </p>
                                        </div>

                                        <!-- O'chirish (faqat yangi fayl uchun) -->
                                        <button v-if="editImageFile" type="button"
                                            class="text-red-500 text-sm hover:underline"
                                            @click="editImageFile = null; editImagePreview = null; editForm.clothes_image = null">
                                            O'chirish
                                        </button>
                                    </div>
                                </div>

                                <!-- Buttons -->
                                <div class="flex justify-end gap-2 mt-6">
                                    <!-- Bekor qilish -->
                                    <button @click="closeEditModal"
                                        class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300">
                                        Bekor qilish
                                    </button>
                                    <!-- Yangilash -->
                                    <!-- @click="submitUpdate" - yangilash funksiyasi -->
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
                </div>
            </div>
        </div>
    </AppLayout>

    <!-- Toast container - xabarlarni ko'rsatish uchun -->
    <ToastContainer />
</template>

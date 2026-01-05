<?php

namespace App\Repositories;

use App\Models\Material;
use App\Repositories\Interfaces\MaterialRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * MaterialRepository - Data Access Layer
 * 
 * Repository Pattern nima?
 * - Database bilan ishlash logikasini ajratish
 * - Model va Service o'rtasidagi qatlam
 * - Database query larni markazlashtirish
 * 
 * Nima uchun Repository kerak?
 * 1. Database logikasini Service dan ajratish
 * 2. Query larni qayta ishlatish (reusability)
 * 3. Test qilishni osonlashtirish (mock qilish oson)
 * 4. Database ni osongina almashtirish mumkin
 * 5. Caching va boshqa optimizatsiyalarni bir joyda qilish
 */
class MaterialRepository implements MaterialRepositoryInterface
{
    /**
     * Cache konfiguratsiyasi
     * 
     * const nima uchun?
     * - O'zgarmas qiymatlar (constant)
     * - Class ichida ishlatiladigan konfiguratsiya
     * 
     * protected nima uchun?
     * - Faqat shu class va child class lar ishlatishi mumkin
     * - Public qilishga hojat yo'q
     * 
     * CACHE_TTL = 3600 (1 soat):
     * - Cache qancha vaqt saqlanadi
     * - 3600 soniya = 1 soat
     * - Keyin avtomatik yangilanadi
     * 
     * CACHE_PREFIX = 'material_':
     * - Cache key prefixi
     * - Boshqa cache lar bilan conflict bo'lmasligi uchun
     * - Masalan: 'material_all_', 'material_find_1'
     */
    protected const CACHE_TTL = 3600; // 1 soat
    protected const CACHE_PREFIX = 'material_';

    /**
     * Constructor - Dependency Injection
     * 
     * Material $material nima uchun?
     * - Eloquent Model ni inject qilish
     * - Query builder sifatida ishlatish
     * - Laravel avtomatik inject qiladi
     * 
     * protected nima uchun?
     * - Class ichida ishlatiladigan property
     * - $this->material orqali access qilish
     */
    public function __construct(protected Material $material) {}

    /**
     * Barcha materiallarni olish (filter bilan)
     * 
     * array $filters = [] nima uchun?
     * - Optional parameter (default bo'sh array)
     * - Filter kerak bo'lmasa, bo'sh array yuboriladi
     * 
     * : Collection nima uchun?
     * - Return type hinting
     * - Eloquent Collection qaytaradi
     * 
     * Cache::remember() nima uchun?
     * - Birinchi marta database dan oladi va cache ga saqlaydi
     * - Keyingi safar cache dan oladi (tezroq)
     * - TTL tugagach, qayta database dan oladi
     * 
     * md5(json_encode($filters)) nima uchun?
     * - Har xil filter uchun alohida cache key
     * - md5 - hash function (qisqa string yaratadi)
     * - json_encode - array ni string ga aylantiradi
     * - Masalan: 'material_all_abc123' va 'material_all_def456'
     */
    public function all(array $filters = []): Collection
    {
        // Unique cache key yaratish (filter ga qarab)
        $cacheKey = self::CACHE_PREFIX . 'all_' . md5(json_encode($filters));

        // Cache::remember() - cache dan olish yoki yangi yaratish
        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($filters) {
            // Query builder yaratish
            $query = $this->material->query();

            // Type filter (agar mavjud bo'lsa)
            // !empty() - null, '', 0, false larni tekshiradi
            if (!empty($filters['type'])) {
                $query->where('type', $filters['type']);
            }

            // Search filter (agar mavjud bo'lsa)
            // where(function) - OR shartlar uchun
            // LIKE - qisman mos kelishni topish
            // % - wildcard (har qanday belgilar)
            if (!empty($filters['search'])) {
                $query->where(function ($q) use ($filters) {
                    $q->where('name', 'like', '%' . $filters['search'] . '%')
                      ->orWhere('code', 'like', '%' . $filters['search'] . '%');
                });
            }

            // Natijalarni olish
            // orderBy - tartiblash (eng yangi birinchi)
            // get() - barcha natijalarni olish
            return $query->orderBy('created_at', 'desc')->get();
        });
    }

    /**
     * Sahifalangan materiallar
     * 
     * LengthAwarePaginator nima?
     * - Laravel pagination obyekti
     * - total, current_page, last_page kabi ma'lumotlar bor
     * 
     * Cache ishlatilmaydi nima uchun?
     * - Pagination har safar o'zgarishi mumkin
     * - Cache qilish murakkab (har bir sahifa uchun alohida cache)
     * - Performance yetarli (index lar bilan)
     */
    public function paginate(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        $query = $this->material->query();

        // Filter lar (all() method bilan bir xil)
        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('code', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('color_code', 'like', '%' . $filters['search'] . '%');
            });
        }

        // paginate() - Laravel pagination
        // Avtomatik LIMIT va OFFSET qo'shadi
        // Query parameter dan current_page oladi
        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    /**
     * Yangi material yaratish
     * 
     * DB::beginTransaction() nima uchun?
     * - Database transaction boshlash
     * - Agar xatolik bo'lsa, rollback qilish mumkin
     * - Data integrity uchun muhim
     * 
     * Transaction nima?
     * - Bir nechta database operatsiyalarni bitta unit sifatida bajarish
     * - Hammasi muvaffaqiyatli bo'lsa commit
     * - Birortasi xato bo'lsa rollback (hech narsa o'zgarmaydi)
     * 
     * try-catch-rollback pattern:
     * - Best practice for database operations
     * - Xatolik bo'lsa, database o'zgarmaydi
     * - Data consistency kafolatlanadi
     */
    public function create(array $data): Material
    {
        // Transaction boshlash
        DB::beginTransaction();
        
        try {
            // Material yaratish
            // create() - Eloquent method
            // Mass assignment ishlatadi ($fillable da bo'lishi kerak)
            $material = $this->material->create($data);

            // Cache ni tozalash
            // Yangi material qo'shildi, eski cache noto'g'ri bo'ldi
            $this->clearCache();

            // Transaction commit qilish
            // Database ga saqlash
            DB::commit();

            return $material;
        } catch (\Throwable $e) {
            // Xatolik bo'lsa, rollback qilish
            // Database o'zgarmaydi
            DB::rollBack();
            
            // Xatoni qayta throw qilish
            throw $e;
        }
    }

    /**
     * ID bo'yicha material topish (cache bilan)
     * 
     * Cache ishlatiladi nima uchun?
     * - find() ko'p chaqiriladi
     * - Har safar database ga murojaat qilish sekin
     * - Cache dan olish tezroq
     */
    public function find(int $id): ?Material
    {
        // Unique cache key (ID bo'yicha)
        $cacheKey = self::CACHE_PREFIX . 'find_' . $id;

        // Cache dan olish yoki database dan olish
        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($id) {
            // find() - Eloquent method
            // ID bo'yicha topish
            // Topilmasa null qaytaradi
            return $this->material->find($id);
        });
    }

    /**
     * Kod bo'yicha material topish
     * 
     * Cache ishlatilmaydi nima uchun?
     * - Kam chaqiriladi (faqat create/update da)
     * - Cache qilishga arzimaydi
     */
    public function findByCode(string $code): ?Material
    {
        // where() - Eloquent query builder
        // first() - birinchi natijani olish (yoki null)
        return $this->material->where('code', $code)->first();
    }

    /**
     * Tur bo'yicha materiallar (cache bilan)
     * 
     * Cache ishlatiladi nima uchun?
     * - Har bir tur uchun alohida cache
     * - Ko'p chaqirilishi mumkin
     */
    public function getByType(string $type): Collection
    {
        $cacheKey = self::CACHE_PREFIX . 'type_' . $type;

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($type) {
            // where() + get() - barcha natijalarni olish
            return $this->material->where('type', $type)->get();
        });
    }

    /**
     * Material yangilash (transaction bilan)
     * 
     * Material $material nima uchun?
     * - Eloquent Model instance
     * - Service da olgan material
     * - ID o'rniga Model yuborish (type safety)
     * 
     * bool qaytaradi nima uchun?
     * - update() method true/false qaytaradi
     * - Muvaffaqiyatli yoki muvaffaqiyatsiz
     */
    public function update(Material $material, array $data): bool
    {
        DB::beginTransaction();
        
        try {
            // update() - Eloquent method
            // Model ni yangilash
            // Mass assignment ishlatadi
            $result = $material->update($data);

            // Cache ni tozalash
            $this->clearCache();

            DB::commit();

            return $result;
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Material o'chirish (transaction bilan)
     */
    public function delete(Material $material): bool
    {
        DB::beginTransaction();
        
        try {
            // delete() - Eloquent method
            // Model ni o'chirish
            // Soft delete bo'lsa, deleted_at ni set qiladi
            // Hard delete bo'lsa, database dan o'chiradi
            $result = $material->delete();

            // Cache ni tozalash
            $this->clearCache();

            DB::commit();

            return $result;
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Kod mavjudligini tekshirish
     * 
     * ?int $excludeId = null nima uchun?
     * - Update da o'z kodini exclude qilish kerak
     * - Create da null bo'ladi (hamma kodlarni tekshirish)
     * 
     * exists() nima uchun?
     * - Faqat mavjudligini tekshirish (true/false)
     * - Ma'lumotlarni olmaslik (tezroq)
     * - COUNT(*) query ishlatadi
     */
    public function codeExists(string $code, ?int $excludeId = null): bool
    {
        // Query builder
        $query = $this->material->where('code', $code);
        
        // Agar excludeId berilgan bo'lsa, uni exclude qilish
        // != - not equal
        // Update da o'z ID sini exclude qilish uchun
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        // exists() - mavjudligini tekshirish
        // true yoki false qaytaradi
        return $query->exists();
    }

    /**
     * Material statistikasi (cache bilan)
     * 
     * Statistika:
     * - Jami materiallar soni
     * - Har bir tur bo'yicha soni
     * 
     * groupBy() nima uchun?
     * - SQL GROUP BY
     * - Har bir tur bo'yicha guruhlashtirish
     * 
     * DB::raw() nima uchun?
     * - Raw SQL expression
     * - count(*) as count - har bir guruhda nechta element
     * 
     * pluck() nima uchun?
     * - Key-value array yaratish
     * - ['mato' => 40, 'tugma' => 30, ...]
     */
    public function getStatistics(): array
    {
        $cacheKey = self::CACHE_PREFIX . 'statistics';

        return Cache::remember($cacheKey, self::CACHE_TTL, function () {
            return [
                // Jami materiallar soni
                // count() - barcha qatorlarni sanash
                'total' => $this->material->count(),
                
                // Har bir tur bo'yicha soni
                // select() - qaysi column larni olish
                // groupBy() - guruhlashtirish
                // pluck() - key-value array
                'by_type' => $this->material->select('type', DB::raw('count(*) as count'))
                    ->groupBy('type')
                    ->pluck('count', 'type')
                    ->toArray(),
            ];
        });
    }

    /**
     * Cache ni tozalash (protected helper method)
     * 
     * protected nima uchun?
     * - Faqat shu class ichida ishlatiladi
     * - Public qilishga hojat yo'q
     * 
     * void nima uchun?
     * - Hech narsa qaytarmaydi
     * - Faqat cache ni tozalaydi
     * 
     * Cache::flush() nima uchun?
     * - Barcha cache ni tozalash
     * - Development uchun yaxshi
     * 
     * Production da nima qilish kerak?
     * - Faqat material cache larni tozalash
     * - Cache::forget() ishlatish
     * - Yoki Cache::tags() ishlatish
     * 
     * Masalan:
     * Cache::forget(self::CACHE_PREFIX . 'all_*');
     * Cache::forget(self::CACHE_PREFIX . 'statistics');
     * 
     * Yoki Cache tags (Redis kerak):
     * Cache::tags(['materials'])->flush();
     */
    protected function clearCache(): void
    {
        // DIQQAT: Production da buni o'zgartiring!
        // Faqat material cache larni tozalash kerak
        // Hozir barcha cache tozalanadi (development uchun yaxshi)
        Cache::flush();
        
        // Production uchun tavsiya:
        // Cache::tags(['materials'])->flush();
        // yoki
        // Cache::forget(self::CACHE_PREFIX . 'all_*');
        // Cache::forget(self::CACHE_PREFIX . 'statistics');
    }
}

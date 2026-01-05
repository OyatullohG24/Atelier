<?php

namespace App\Services;

use App\DTO\MaterialDTO;
use App\Models\Material;
use App\Repositories\Interfaces\MaterialRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

/**
 * MaterialService - Business Logic Layer
 * 
 * Service Layer nima?
 * - Controller va Repository o'rtasidagi qatlam
 * - Business logika shu yerda yoziladi
 * - Ma'lumotlarni qayta ishlash, validatsiya, transformatsiya
 * 
 * Nima uchun Service Layer kerak?
 * 1. Controller ni yengil qilish (thin controller, fat service)
 * 2. Business logikani bir joyga yig'ish
 * 3. Kodni qayta ishlatish (reusability)
 * 4. Test qilishni osonlashtirish
 * 5. Separation of Concerns (har bir qatlam o'z ishini qiladi)
 */
class MaterialService
{
    /**
     * Constructor - Dependency Injection
     * 
     * protected nima uchun?
     * - Faqat shu class ichida ishlatiladi
     * - Child class lar ham foydalanishi mumkin
     * 
     * MaterialRepositoryInterface nima uchun?
     * - Interface ga bog'lanish (Dependency Inversion Principle)
     * - Test qilishda mock qilish oson
     * - Implementation ni osongina almashtirish mumkin
     * 
     * Laravel Dependency Injection:
     * - Laravel avtomatik ravishda MaterialRepository ni inject qiladi
     * - AppServiceProvider da bind qilingan bo'lishi kerak
     */
    public function __construct(
        protected MaterialRepositoryInterface $material_repository
    ) {}

    /**
     * Barcha materiallarni olish (filter bilan)
     * 
     * ?Request $request = null nima uchun?
     * - ? belgisi nullable type (null bo'lishi mumkin)
     * - = null default qiymat (agar berilmasa null bo'ladi)
     * - Filter kerak bo'lmasa, request yubormaslik mumkin
     * 
     * : Collection nima uchun?
     * - Return type hinting
     * - Method Collection qaytarishini kafolatlaydi
     * - IDE autocomplete ishlaydi
     * 
     * try-catch nima uchun?
     * - Xatolarni ushlash va log qilish
     * - User ga tushunarli xato xabari berish
     * - Application crash bo'lmasligi uchun
     */
    public function getAll(?Request $request = null): Collection
    {
        try {
            // Request dan filter larni tayyorlash
            // buildFilters() method pastda yozilgan
            $filters = $this->buildFilters($request);

            // Repository orqali ma'lumotlarni olish
            // Repository cache dan oladi (agar mavjud bo'lsa)
            return $this->material_repository->all($filters);
        } catch (\Throwable $th) {
            // Xatoni log qilish (debugging uchun)
            // Log::error() Laravel ning logging tizimi
            Log::error('MaterialService::getAll error', [
                'message' => $th->getMessage(),           // Xato xabari
                'trace' => $th->getTraceAsString(),       // Stack trace (qayerda xato bo'lgan)
            ]);
            
            // Xatoni qayta throw qilish (Controller da handle qilish uchun)
            throw $th;
        }
    }

    /**
     * Sahifalangan materiallar (Pagination)
     * 
     * Pagination nima uchun kerak?
     * - Katta ma'lumotlarni qismlarga bo'lish
     * - Performance yaxshilash (barcha ma'lumotlarni bir vaqtda yuklamaslik)
     * - User experience yaxshilash
     * 
     * int $perPage = 15 nima uchun?
     * - Har bir sahifada nechta element ko'rsatish
     * - Default 15 ta (Laravel convention)
     * - Controller dan o'zgartirish mumkin
     */
    public function getPaginated(?Request $request = null, int $perPage = 15): LengthAwarePaginator
    {
        try {
            $filters = $this->buildFilters($request);

            // Repository paginate() methodini chaqirish
            // LengthAwarePaginator qaytaradi (total count bilan)
            return $this->material_repository->paginate($perPage, $filters);
        } catch (\Throwable $th) {
            Log::error('MaterialService::getPaginated error', [
                'message' => $th->getMessage(),
            ]);
            throw $th;
        }
    }

    /**
     * Bitta materialni ID bo'yicha olish
     * 
     * ?Material nima uchun?
     * - Nullable return type
     * - Material topilmasa null qaytarishi mumkin
     * - Lekin biz exception throw qilamiz, shuning uchun null qaytarmaydi
     * 
     * Exception throw qilish nima uchun?
     * - Material topilmasa, xato xabari berish
     * - Controller da 404 response qaytarish uchun
     * - Null check qilishga hojat yo'q
     */
    public function getOne(int $id): ?Material
    {
        try {
            // Repository dan material ni olish
            // Repository cache dan oladi (agar mavjud bo'lsa)
            $material = $this->material_repository->find($id);

            // Agar material topilmasa, exception throw qilish
            if (!$material) {
                throw new \Exception("Material topilmadi (ID: {$id})");
            }

            return $material;
        } catch (\Throwable $th) {
            // Xatoni log qilish (ID bilan birga)
            Log::error('MaterialService::getOne error', [
                'id' => $id,
                'message' => $th->getMessage(),
            ]);
            throw $th;
        }
    }

    /**
     * Tur bo'yicha materiallarni olish
     * 
     * string $type nima uchun?
     * - Material turi: mato, tugma, ip, zamok
     * - Controller da validatsiya qilingan
     */
    public function getByType(string $type): Collection
    {
        try {
            return $this->material_repository->getByType($type);
        } catch (\Throwable $th) {
            Log::error('MaterialService::getByType error', [
                'type' => $type,
                'message' => $th->getMessage(),
            ]);
            throw $th;
        }
    }

    /**
     * Yangi material yaratish (DTO bilan)
     * 
     * Bu eng muhim method, ko'p logika bor:
     * 1. Request dan DTO yaratish
     * 2. DTO validatsiya qilish
     * 3. Kod unique ekanligini tekshirish
     * 4. Rasm yuklash
     * 5. Database ga saqlash
     * 6. Xatolik bo'lsa, rasmni o'chirish (rollback)
     */
    public function createMaterial(Request $request): Material
    {
        try {
            // 1. Request dan DTO yaratish (Factory Method)
            // DTO pattern: ma'lumotlarni transfer qilish uchun
            $dto = MaterialDTO::fromRequest($request);

            // 2. DTO validatsiyasi
            // Request validation dan keyin qo'shimcha tekshirish
            if (!$dto->validate()) {
                throw new \Exception('DTO validatsiyasi muvaffaqiyatsiz');
            }

            // 3. Kod unique ekanligini tekshirish
            // Repository da codeExists() method bor
            // Agar kod mavjud bo'lsa, ValidationException throw qilish
            if ($this->material_repository->codeExists($dto->code)) {
                throw ValidationException::withMessages([
                    'code' => ['Bu kod allaqachon mavjud']
                ]);
            }

            // 4. Rasm yuklash
            // handleImageUpload() method pastda yozilgan
            // Unique filename yaratadi va storage ga saqlaydi
            $imagePath = $this->handleImageUpload($dto->image);

            // 5. DTO ni array ga aylantirish
            // toArray() method database uchun tayyor array qaytaradi
            $data = $dto->toArray();
            
            // Rasm yo'lini qo'shish (handleImageUpload dan kelgan)
            $data['image'] = $imagePath;

            // 6. Repository orqali database ga saqlash
            // Repository transaction ishlatadi (rollback uchun)
            $material = $this->material_repository->create($data);

            // 7. Muvaffaqiyatli yaratilganini log qilish
            Log::info('Material created successfully', ['id' => $material->id]);

            return $material;
        } catch (\Throwable $th) {
            // Xatoni log qilish (stack trace bilan)
            Log::error('MaterialService::createMaterial error', [
                'message' => $th->getMessage(),
                'trace' => $th->getTraceAsString(),
            ]);

            // MUHIM: Agar xatolik bo'lsa, yuklangan rasmni o'chirish
            // Bu rollback hisoblanadi (database ga saqlanmagan, rasm ham kerak emas)
            if (isset($imagePath)) {
                $this->deleteImage($imagePath);
            }

            throw $th;
        }
    }

    /**
     * Materialni yangilash (DTO bilan)
     * 
     * Update create dan farqi:
     * 1. Mavjud material ni olish kerak
     * 2. Yangi rasm yuklanmasa, eski rasmni saqlash
     * 3. Yangi rasm yuklansa, eski rasmni o'chirish
     * 4. Kod unique tekshirishda o'zini exclude qilish
     */
    public function updateMaterial(int $id, Request $request): Material
    {
        try {
            // 1. Mavjud materialni olish
            // getOne() method exception throw qiladi agar topilmasa
            $material = $this->getOne($id);
            
            // Eski rasm yo'lini saqlash (kerak bo'lsa qaytarish uchun)
            $oldImagePath = $material->image;

            // 2. Update uchun DTO yaratish
            // fromRequestForUpdate() eski rasm yo'lini ham qabul qiladi
            $dto = MaterialDTO::fromRequestForUpdate($request, $oldImagePath);

            // 3. DTO validatsiyasi
            if (!$dto->validate()) {
                throw new \Exception('DTO validatsiyasi muvaffaqiyatsiz');
            }

            // 4. Kod unique tekshirish (o'zidan tashqari)
            // $id ni exclude qilish kerak (o'z kodini o'zgartirmaslik mumkin)
            if ($this->material_repository->codeExists($dto->code, $id)) {
                throw ValidationException::withMessages([
                    'code' => ['Bu kod allaqachon boshqa materialda mavjud']
                ]);
            }

            // 5. Rasm bilan ishlash
            // Agar yangi rasm yuklangan bo'lsa
            if ($dto->hasNewImage()) {
                // Yangi rasmni yuklash
                $imagePath = $this->handleImageUpload($dto->image);
                
                // Eski rasmni o'chirish (storage dan)
                $this->deleteImage($oldImagePath);
            } else {
                // Yangi rasm yo'q bo'lsa, eski rasmni saqlash
                $imagePath = $oldImagePath;
            }

            // 6. Ma'lumotlarni tayyorlash
            $data = $dto->toArray();
            $data['image'] = $imagePath;

            // 7. Repository orqali yangilash
            // update() method transaction ishlatadi
            $this->material_repository->update($material, $data);

            // 8. Muvaffaqiyatli yangilanganini log qilish
            Log::info('Material updated successfully', ['id' => $id]);

            // 9. Yangilangan materialni qaytarish
            // Fresh instance olish (cache dan emas, database dan)
            return $this->getOne($id);
        } catch (\Throwable $th) {
            Log::error('MaterialService::updateMaterial error', [
                'id' => $id,
                'message' => $th->getMessage(),
                'trace' => $th->getTraceAsString(),
            ]);

            // MUHIM: Agar xatolik bo'lsa va yangi rasm yuklangan bo'lsa
            // Yangi rasmni o'chirish (rollback)
            // Eski rasmni o'chirmaslik (chunki update muvaffaqiyatsiz)
            if (isset($imagePath) && $imagePath !== $oldImagePath) {
                $this->deleteImage($imagePath);
            }

            throw $th;
        }
    }

    /**
     * Materialni o'chirish
     * 
     * Delete qilishda:
     * 1. Material ni olish
     * 2. Database dan o'chirish
     * 3. Rasm faylini o'chirish
     */
    public function deleteMaterial(int $id): bool
    {
        try {
            // 1. Materialni olish
            $material = $this->getOne($id);
            
            // Rasm yo'lini saqlash (o'chirishdan oldin)
            $imagePath = $material->image;

            // 2. Repository orqali database dan o'chirish
            // delete() method transaction ishlatadi
            $result = $this->material_repository->delete($material);

            // 3. Agar muvaffaqiyatli o'chirilgan bo'lsa, rasmni ham o'chirish
            if ($result) {
                $this->deleteImage($imagePath);
                Log::info('Material deleted successfully', ['id' => $id]);
            }

            return $result;
        } catch (\Throwable $th) {
            Log::error('MaterialService::deleteMaterial error', [
                'id' => $id,
                'message' => $th->getMessage(),
            ]);
            throw $th;
        }
    }

    /**
     * Material statistikasi
     * 
     * Statistika:
     * - Jami materiallar soni
     * - Har bir tur bo'yicha soni
     */
    public function getStatistics(): array
    {
        try {
            // Repository da getStatistics() method bor
            // Cache dan oladi (agar mavjud bo'lsa)
            return $this->material_repository->getStatistics();
        } catch (\Throwable $th) {
            Log::error('MaterialService::getStatistics error', [
                'message' => $th->getMessage(),
            ]);
            throw $th;
        }
    }

    /**
     * Rasm yuklash (Protected Helper Method)
     * 
     * protected nima uchun?
     * - Faqat shu class ichida ishlatiladi
     * - Public qilishga hojat yo'q (API method emas)
     * 
     * Helper method nima uchun?
     * - Kod takrorlanishini kamaytirish (DRY principle)
     * - create va update da ishlatiladi
     * - Rasm yuklash logikasi bir joyda
     * 
     * Unique filename nima uchun?
     * - Bir xil nomdagi fayllar conflict qilmasligi uchun
     * - time() + uniqid() kombinatsiyasi unique kafolatlaydi
     */
    protected function handleImageUpload($image): string
    {
        // Rasm mavjudligini tekshirish
        if (!$image) {
            throw new \Exception('Rasm yuklanmadi');
        }

        try {
            // Unique filename yaratish
            // Format: 1234567890_abc123def456.jpg
            // time() - hozirgi vaqt (timestamp)
            // uniqid() - unique string
            // getClientOriginalExtension() - fayl extension (jpg, png, etc)
            $filename = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();

            // Storage ga saqlash
            // storeAs(directory, filename, disk)
            // directory: 'material' (storage/app/public/material)
            // filename: unique filename
            // disk: 'public' (public disk, symlink orqali access qilish mumkin)
            $path = $image->storeAs('material', $filename, 'public');

            return $path;
        } catch (\Throwable $th) {
            // Rasm yuklashda xatolik bo'lsa, log qilish
            Log::error('Image upload failed', [
                'message' => $th->getMessage(),
            ]);
            
            // User-friendly xato xabari
            throw new \Exception('Rasm yuklashda xatolik yuz berdi');
        }
    }

    /**
     * Rasmni o'chirish (Protected Helper Method)
     * 
     * void nima uchun?
     * - Hech narsa qaytarmaydi
     * - Faqat rasm o'chiradi
     * 
     * ?string $path nima uchun?
     * - Nullable parameter
     * - Path null bo'lishi mumkin (eski materiallar uchun)
     * 
     * Safe deletion:
     * - Avval mavjudligini tekshirish
     * - Xatolik bo'lsa, application crash bo'lmasligi
     * - Warning log qilish (error emas, chunki critical emas)
     */
    protected function deleteImage(?string $path): void
    {
        // Path mavjud va fayl mavjudligini tekshirish
        if ($path && Storage::disk('public')->exists($path)) {
            try {
                // Storage dan o'chirish
                Storage::disk('public')->delete($path);
                
                // Muvaffaqiyatli o'chirilganini log qilish
                Log::info('Image deleted', ['path' => $path]);
            } catch (\Throwable $th) {
                // Xatolik bo'lsa, warning log qilish
                // Error emas, chunki rasm o'chirilmasa ham application ishlaydi
                Log::warning('Failed to delete image', [
                    'path' => $path,
                    'message' => $th->getMessage(),
                ]);
            }
        }
    }

    /**
     * Filter larni tayyorlash (Protected Helper Method)
     * 
     * array_filter() nima uchun?
     * - null va empty qiymatlarni olib tashlash
     * - Faqat to'ldirilgan filter larni yuborish
     * - Repository da shart yozishni osonlashtirish
     * 
     * Masalan:
     * Input: ['type' => 'mato', 'search' => null]
     * Output: ['type' => 'mato']
     */
    protected function buildFilters(?Request $request): array
    {
        // Agar request yo'q bo'lsa, bo'sh array qaytarish
        if (!$request) {
            return [];
        }

        // Request dan filter larni olish va tozalash
        return array_filter([
            'type' => $request->input('type'),        // Material turi
            'search' => $request->input('search'),    // Qidiruv so'zi
        ]);
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Material\MaterialStoreRequest;
use App\Http\Requests\Material\MaterialUpdateRequest;
use App\Http\Resources\MaterialResource;
use App\Services\MaterialService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * MaterialController - API Controller
 *
 * Controller nima?
 * - HTTP request larni qabul qiladi
 * - Service layer ga yuboradi
 * - JSON response qaytaradi
 *
 * Thin Controller principle:
 * - Controller da minimal logika
 * - Barcha business logika Service da
 * - Controller faqat request/response bilan ishlaydi
 *
 * RESTful API:
 * - index() - GET /materials (barcha materiallar)
 * - store() - POST /materials (yangi material)
 * - show() - GET /materials/{id} (bitta material)
 * - update() - PUT/PATCH /materials/{id} (yangilash)
 * - destroy() - DELETE /materials/{id} (o'chirish)
 */
class MaterialController extends Controller
{
    /**
     * Constructor - Dependency Injection
     *
     * MaterialService $material_service:
     * - Laravel avtomatik inject qiladi
     * - Service layer ga murojaat qilish uchun
     *
     * protected nima uchun?
     * - Class ichida ishlatiladigan property
     * - $this->material_service orqali access
     */
    public function __construct(
        protected MaterialService $material_service
    ) {}

    /**
     * Barcha materiallarni olish
     *
     * GET /api/materials
     * GET /api/materials?paginate=true&per_page=20
     * GET /api/materials?type=mato&search=ko'k
     *
     * Request $request:
     * - Query parameters olish uchun
     * - Filter va pagination uchun
     *
     * JsonResponse:
     * - JSON format da response
     * - Content-Type: application/json
     *
     * Pagination support:
     * - ?paginate=true - pagination yoqish
     * - ?per_page=20 - sahifada 20 ta element
     *
     * Filter support:
     * - ?type=mato - faqat mato materiallar
     * - ?search=ko'k - nom yoki kodda 'ko'k' so'zi bor materiallar
     */
    public function index(Request $request): JsonResponse
    {
        try {
            // 1. Pagination parametrlarini olish
            // boolean() - true/false ga aylantiradi
            // integer() - int ga aylantiradi
            // Default qiymatlar: paginate=false, per_page=15
            $paginate = $request->boolean('paginate', false);
            $perPage = $request->integer('per_page', 15);

            // 2. Agar pagination kerak bo'lsa
            if ($paginate) {
                // Service dan sahifalangan ma'lumotlarni olish
                $materials = $this->material_service->getPaginated($request, $perPage);

                // Response qaytarish (pagination ma'lumotlari bilan)
                return response()->json([
                    'status' => true,
                    'message' => 'Materiallar muvaffaqiyatli olindi',

                    // MaterialResource::collection() - ma'lumotlarni transform qilish
                    // items() - faqat ma'lumotlar (pagination meta siz)
                    'data' => MaterialResource::collection($materials->items()),

                    // Pagination meta ma'lumotlari
                    'pagination' => [
                        'total' => $materials->total(),              // Jami elementlar soni
                        'per_page' => $materials->perPage(),         // Sahifada nechta
                        'current_page' => $materials->currentPage(), // Hozirgi sahifa
                        'last_page' => $materials->lastPage(),       // Oxirgi sahifa
                        'from' => $materials->firstItem(),           // Birinchi element raqami
                        'to' => $materials->lastItem(),              // Oxirgi element raqami
                    ],
                ]);
            }

            // 3. Agar pagination kerak bo'lmasa, barcha ma'lumotlarni olish
            $materials = $this->material_service->getAll($request);

            // Response qaytarish (pagination siz)
            return response()->json([
                'status' => true,
                'message' => 'Materiallar muvaffaqiyatli olindi',
                'data' => MaterialResource::collection($materials),
                'count' => $materials->count(),  // Jami elementlar soni
            ]);
        } catch (\Throwable $th) {
            // 4. Xatolikni log qilish
            Log::error('MaterialController::index error', [
                'message' => $th->getMessage(),
            ]);

            // 5. Error response qaytarish
            // 500 - Internal Server Error
            // config('app.debug') - debug mode tekshirish
            // Debug mode da xato xabari ko'rsatiladi, production da yo'q
            return response()->json([
                'status' => false,
                'message' => 'Materiallarni olishda xatolik yuz berdi',
                'error' => config('app.debug') ? $th->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Yangi material yaratish
     *
     * POST /api/materials
     *
     * MaterialStoreRequest $request:
     * - Form Request validation
     * - Avtomatik validatsiya qilinadi
     * - Agar validatsiya muvaffaqiyatsiz bo'lsa, 422 error qaytaradi
     *
     * 201 status code:
     * - Created (yangi resurs yaratildi)
     * - RESTful API convention
     *
     * ValidationException:
     * - Service da throw qilingan validatsiya xatolari
     * - Masalan: kod unique emas
     * - 422 status code (Unprocessable Entity)
     */
    public function store(MaterialStoreRequest $request): JsonResponse
    {
        try {
            // 1. Service orqali material yaratish
            // Service DTO ishlatadi
            // Service rasm yuklaydi
            // Service database ga saqlaydi
            $material = $this->material_service->createMaterial($request);

            // 2. Success response (201 Created)
            return response()->json([
                'status' => true,
                'message' => 'Material muvaffaqiyatli yaratildi',
                'data' => MaterialResource::make($material),
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            // 3. Validatsiya xatosi (masalan: kod unique emas)
            // 422 - Unprocessable Entity
            // errors() - barcha validatsiya xatolari
            return response()->json([
                'status' => false,
                'message' => 'Validatsiya xatosi',
                'errors' => $e->errors(),  // ['code' => ['Bu kod allaqachon mavjud']]
            ], 422);
        } catch (\Throwable $th) {
            // 4. Boshqa xatoliklar (masalan: rasm yuklash xatosi)
            Log::error('MaterialController::store error', [
                'message' => $th->getMessage(),
                'trace' => $th->getTraceAsString(),
            ]);

            // 500 - Internal Server Error
            return response()->json([
                'status' => false,
                'message' => 'Material yaratishda xatolik yuz berdi',
                'error' => config('app.debug') ? $th->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Bitta materialni ko'rsatish
     *
     * GET /api/materials/{id}
     *
     * int $id:
     * - Route parameter
     * - Material ID
     * - Type hinting (int bo'lishi kerak)
     *
     * 404 error:
     * - Material topilmasa
     * - Service exception throw qiladi
     */
    public function show(int $id): JsonResponse
    {
        try {
            // 1. Service dan material olish
            // Service exception throw qiladi agar topilmasa
            $material = $this->material_service->getOne($id);

            // 2. Success response
            return response()->json([
                'status' => true,
                'message' => 'Material muvaffaqiyatli olindi',
                'data' => MaterialResource::make($material),
            ]);
        } catch (\Throwable $th) {
            // 3. Xatolik (material topilmadi)
            Log::error('MaterialController::show error', [
                'id' => $id,
                'message' => $th->getMessage(),
            ]);

            // 404 - Not Found
            return response()->json([
                'status' => false,
                'message' => 'Material topilmadi',
                'error' => config('app.debug') ? $th->getMessage() : null,
            ], 404);
        }
    }

    /**
     * Materialni yangilash
     *
     * PUT /api/materials/{id}
     * PATCH /api/materials/{id}
     *
     * MaterialUpdateRequest $request:
     * - Update uchun validatsiya
     * - material_image nullable (majburiy emas)
     *
     * int $id:
     * - Qaysi materialni yangilash
     *
     * Update logikasi:
     * - Yangi rasm yuklansa, eski rasmni o'chirish
     * - Yangi rasm yuklanmasa, eski rasmni saqlash
     * - Kod unique tekshirish (o'zidan tashqari)
     */
    public function update(MaterialUpdateRequest $request, int $id): JsonResponse
    {
        try {
            // 1. Service orqali yangilash
            // Service DTO ishlatadi
            // Service rasm bilan ishlaydi
            // Service database ni yangilaydi
            $material = $this->material_service->updateMaterial($id, $request);

            // 2. Success response
            return response()->json([
                'status' => true,
                'message' => 'Material muvaffaqiyatli yangilandi',
                'data' => MaterialResource::make($material),
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            // 3. Validatsiya xatosi
            return response()->json([
                'status' => false,
                'message' => 'Validatsiya xatosi',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Throwable $th) {
            // 4. Boshqa xatoliklar
            Log::error('MaterialController::update error', [
                'id' => $id,
                'message' => $th->getMessage(),
            ]);

            return response()->json([
                'status' => false,
                'message' => 'Material yangilashda xatolik yuz berdi',
                'error' => config('app.debug') ? $th->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Materialni o'chirish
     *
     * DELETE /api/materials/{id}
     *
     * Delete logikasi:
     * - Database dan o'chirish
     * - Rasm faylini o'chirish
     * - Cache ni tozalash
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            // 1. Service orqali o'chirish
            // Service rasm faylini ham o'chiradi
            $result = $this->material_service->deleteMaterial($id);

            // 2. Success response
            return response()->json([
                'status' => $result,
                'message' => $result ? 'Material muvaffaqiyatli o\'chirildi' : 'Material o\'chirishda xatolik',
                'data' => [],
            ]);
        } catch (\Throwable $th) {
            // 3. Xatolik
            Log::error('MaterialController::destroy error', [
                'id' => $id,
                'message' => $th->getMessage(),
            ]);

            return response()->json([
                'status' => false,
                'message' => 'Material o\'chirishda xatolik yuz berdi',
                'error' => config('app.debug') ? $th->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Tur bo'yicha materiallar
     *
     * GET /api/materials/type/{type}
     *
     * string $type:
     * - Material turi: mato, tugma, ip, zamok
     * - Route parameter
     *
     * Validatsiya:
     * - Faqat to'g'ri turlar qabul qilinadi
     * - Noto'g'ri tur bo'lsa, 400 error
     */
    public function getByType(Request $request, string $type): JsonResponse
    {
        try {
            // 1. Valid turlarni tekshirish
            $validTypes = ['mato', 'tugma', 'ip', 'zamok'];

            // in_array() - array da mavjudligini tekshirish
            if (! in_array($type, $validTypes)) {
                // 400 - Bad Request
                return response()->json([
                    'status' => false,
                    'message' => 'Noto\'g\'ri material turi',
                    'valid_types' => $validTypes,  // To'g'ri turlar ro'yxati
                ], 400);
            }

            // 2. Service dan materiallarni olish
            $materials = $this->material_service->getByType($type);

            // 3. Success response
            return response()->json([
                'status' => true,
                'message' => 'Materiallar muvaffaqiyatli olindi',
                'data' => MaterialResource::collection($materials),
                'count' => $materials->count(),
            ]);
        } catch (\Throwable $th) {
            // 4. Xatolik
            Log::error('MaterialController::getByType error', [
                'type' => $type,
                'message' => $th->getMessage(),
            ]);

            return response()->json([
                'status' => false,
                'message' => 'Materiallarni olishda xatolik yuz berdi',
                'error' => config('app.debug') ? $th->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Material statistikasi
     *
     * GET /api/materials/statistics
     *
     * Response:
     * {
     *   "total": 100,
     *   "by_type": {
     *     "mato": 40,
     *     "tugma": 30,
     *     "ip": 20,
     *     "zamok": 10
     *   }
     * }
     */
    public function statistics(): JsonResponse
    {
        try {
            // 1. Service dan statistikani olish
            // Repository cache dan oladi
            $statistics = $this->material_service->getStatistics();

            // 2. Success response
            return response()->json([
                'status' => true,
                'message' => 'Statistika muvaffaqiyatli olindi',
                'data' => $statistics,
            ]);
        } catch (\Throwable $th) {
            // 3. Xatolik
            Log::error('MaterialController::statistics error', [
                'message' => $th->getMessage(),
            ]);

            return response()->json([
                'status' => false,
                'message' => 'Statistikani olishda xatolik yuz berdi',
                'error' => config('app.debug') ? $th->getMessage() : null,
            ], 500);
        }
    }
}

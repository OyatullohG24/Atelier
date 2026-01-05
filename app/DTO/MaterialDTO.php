<?php

namespace App\DTO;

use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;

/**
 * MaterialDTO - Data Transfer Object
 *
 * DTO nima?
 * - Bu ma'lumotlarni bir joydan ikkinchi joyga ko'chirish uchun ishlatiladi
 * - Request dan kelgan ma'lumotlarni Service va Repository ga yuborish uchun
 * - Type safety (ma'lumot turlarini kafolatlash) uchun
 *
 * Nima uchun DTO kerak?
 * 1. Request obyektini to'g'ridan-to'g'ri Service ga yubormaslik (separation of concerns)
 * 2. Ma'lumotlarni validatsiya qilish va transform qilish
 * 3. Kodni test qilishni osonlashtirish
 * 4. Type hinting orqali xatolarni kamaytirish
 */
class MaterialDTO
{
    /**
     * Constructor - DTO yaratish
     *
     * readonly nima uchun?
     * - readonly property lar faqat constructor da set qilinadi
     * - Keyinchalik o'zgartirib bo'lmaydi (immutability)
     * - Bu xatolarni kamaytiradi, chunki ma'lumotlar o'zgarmaydi
     *
     * public nima uchun?
     * - DTO property lariga to'g'ridan-to'g'ri murojaat qilish uchun
     * - Getter methodlar yozishga hojat yo'q
     *
     * Type hinting nima uchun?
     * - string, ?UploadedFile kabi type lar xatolarni kamaytiradi
     * - IDE autocomplete ishlaydi
     * - Runtime da type error beradi agar noto'g'ri type kelsa
     */
    public function __construct(
        public readonly string $type,              // Material turi: mato, tugma, ip, zamok
        public readonly string $name,              // Material nomi
        public readonly string $code,              // Material kodi (unique bo'lishi kerak)
        public readonly string $colorCode,         // Rang kodi (masalan: #FF0000)
        public readonly string $measurement,       // O'lchov birligi (metr, dona, kg)
        public readonly ?UploadedFile $image = null,     // Yangi yuklangan rasm (nullable)
        public readonly ?string $imagePath = null        // Mavjud rasm yo'li (nullable)
    ) {}

    /**
     * Request dan DTO yaratish (Factory Method Pattern)
     *
     * Static method nima uchun?
     * - DTO ni yaratishning qulay usuli
     * - new MaterialDTO(...) o'rniga MaterialDTO::fromRequest($request)
     *
     * Factory Pattern nima uchun?
     * - Obyekt yaratishni markazlashtirish
     * - Agar yaratish logikasi o'zgarsa, faqat bir joyda o'zgartirish kerak
     *
     * @param  Request  $request  - Laravel Request obyekti
     * @return self - MaterialDTO instance
     */
    public static function fromRequest(Request $request): self
    {
        return new self(
            type: $request->input('type'),                    // Request dan type ni olish
            name: $request->input('material_name'),           // Request dan nom ni olish
            code: $request->input('code'),                    // Request dan kod ni olish
            colorCode: $request->input('color_code'),         // Request dan rang kodini olish
            measurement: $request->input('measurement'),      // Request dan o'lchov birligini olish
            image: $request->file('material_image')           // Request dan rasm faylini olish
            // imagePath null bo'ladi, chunki yangi material yaratilmoqda
        );
    }

    /**
     * Update uchun DTO yaratish
     *
     * Nima uchun alohida method?
     * - Update da mavjud rasm yo'li kerak bo'ladi
     * - Agar yangi rasm yuklanmasa, eski rasm saqlanadi
     *
     * @param  Request  $request  - Laravel Request obyekti
     * @param  string|null  $existingImagePath  - Mavjud rasm yo'li
     * @return self - MaterialDTO instance
     */
    public static function fromRequestForUpdate(Request $request, ?string $existingImagePath = null): self
    {
        return new self(
            type: $request->input('type'),
            name: $request->input('material_name'),
            code: $request->input('code'),
            colorCode: $request->input('color_code'),
            measurement: $request->input('measurement'),
            image: $request->file('material_image'),          // Yangi rasm (bo'lishi shart emas)
            imagePath: $existingImagePath                     // Eski rasm yo'li (fallback uchun)
        );
    }

    /**
     * DTO ni array ga aylantirish
     *
     * Nima uchun kerak?
     * - Database ga saqlash uchun array kerak
     * - Eloquent create() va update() methodlari array qabul qiladi
     *
     * camelCase dan snake_case ga nima uchun?
     * - Database column nomlari snake_case da (color_code)
     * - PHP property lar camelCase da (colorCode)
     * - Bu Laravel convention
     *
     * @return array - Database uchun tayyor array
     */
    public function toArray(): array
    {
        return [
            'type' => $this->type,
            'name' => $this->name,
            'code' => $this->code,
            'color_code' => $this->colorCode,        // camelCase -> snake_case
            'measurement' => $this->measurement,
            'image' => $this->imagePath,              // Rasm yo'li (Service da set qilinadi)
        ];
    }

    /**
     * Yangi rasm mavjudligini tekshirish
     *
     * Nima uchun kerak?
     * - Update da yangi rasm yuklanganmi yoki yo'qmi bilish kerak
     * - Agar yangi rasm bo'lsa, eski rasmni o'chirish kerak
     * - Agar yangi rasm yo'q bo'lsa, eski rasmni saqlash kerak
     *
     * @return bool - true agar yangi rasm yuklangan bo'lsa
     */
    public function hasNewImage(): bool
    {
        return $this->image !== null;
    }

    /**
     * DTO validatsiyasi
     *
     * Nima uchun kerak?
     * - Request validation dan keyin qo'shimcha tekshirish
     * - DTO to'g'ri yaratilganligini kafolatlash
     * - Service layer da ishonch hosil qilish
     *
     * empty() nima uchun?
     * - null, '', 0, false larni tekshiradi
     * - Qisqa va tushunarli
     *
     * @return bool - true agar barcha majburiy field lar to'ldirilgan bo'lsa
     */
    public function validate(): bool
    {
        return ! empty($this->type)
            && ! empty($this->name)
            && ! empty($this->code)
            && ! empty($this->colorCode)
            && ! empty($this->measurement);
    }
}

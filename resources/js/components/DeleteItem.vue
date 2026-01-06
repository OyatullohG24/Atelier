<script setup lang="ts">
import { ref } from 'vue';

interface Props {
    isOpen: boolean;
    message?: string;
    title?: string;
}

const props = withDefaults(defineProps<Props>(), {
    message: 'Rostdan ham o\'chirmoqchimisiz?',
    title: 'O\'chirishni tasdiqlash'
});

const emit = defineEmits<{
    close: [];
    confirm: [];
}>();

const handleConfirm = () => {
    emit('confirm');
};

const handleClose = () => {
    emit('close');
};
</script>

<template>
    <Transition name="modal">
        <div v-if="isOpen" class="fixed inset-0 z-50 flex items-center justify-center">
            <!-- Backdrop -->
            <div class="absolute inset-0 bg-black bg-opacity-50" @click="handleClose"></div>

            <!-- Modal -->
            <div class="relative bg-white rounded-lg shadow-xl max-w-md w-full mx-4 p-6">
                <!-- Header -->
                <div class="mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">
                        {{ title }}
                    </h3>
                </div>

                <!-- Body -->
                <div class="mb-6">
                    <p class="text-gray-600">
                        {{ message }}
                    </p>
                </div>

                <!-- Footer -->
                <div class="flex justify-end gap-3">
                    <button @click="handleClose"
                        class="px-4 py-2 text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">
                        Bekor qilish
                    </button>
                    <button @click="handleConfirm"
                        class="px-4 py-2 text-white bg-red-600 hover:bg-red-700 rounded-lg transition-colors">
                        O'chirish
                    </button>
                </div>
            </div>
        </div>
    </Transition>
</template>

<style scoped>
.modal-enter-active,
.modal-leave-active {
    transition: opacity 0.3s ease;
}

.modal-enter-from,
.modal-leave-to {
    opacity: 0;
}

.modal-enter-active .relative,
.modal-leave-active .relative {
    transition: transform 0.3s ease;
}

.modal-enter-from .relative,
.modal-leave-to .relative {
    transform: scale(0.95);
}
</style>
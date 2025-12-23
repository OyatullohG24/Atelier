<template>
    <div class="fixed top-5 right-5 flex flex-col gap-2 z-50">
        <transition-group name="toast" tag="div">
            <div v-for="toast in toasts" :key="toast.id" :class="[
                'p-3 rounded shadow text-white',
                toastClass(toast.type)
            ]">
                {{ toast.message }}
            </div>
        </transition-group>
    </div>
</template>

<script setup lang="ts">
import { useToast } from './useToast';

const { toasts } = useToast();

const toastClass = (type: string) => {
    switch (type) {
        case 'success': return 'bg-green-500';
        case 'error': return 'bg-red-500';
        default: return 'bg-blue-500';
    }
};
</script>

<style scoped>
.toast-enter-from,
.toast-leave-to {
    opacity: 0;
    transform: translateX(50px);
}

.toast-enter-to,
.toast-leave-from {
    opacity: 1;
    transform: translateX(0);
}

.toast-enter-active,
.toast-leave-active {
    transition: all 0.3s ease;
}
</style>
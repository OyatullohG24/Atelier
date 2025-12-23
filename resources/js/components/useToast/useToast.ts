import { ref } from 'vue';

// ✅ Singleton - fayl darajasida yaratamiz
const toasts = ref<{ id: number; message: string; type: string }[]>([]);

export function useToast() {
    const show = (message: string, type = 'info', duration = 3000) => {
        const id = Date.now();
        toasts.value.push({ id, message, type });

        setTimeout(() => {
            toasts.value = toasts.value.filter(t => t.id !== id);
        }, duration);
    };

    const success = (message: string, duration?: number) => show(message, 'success', duration);
    const error = (message: string, duration?: number) => show(message, 'error', duration);
    const info = (message: string, duration?: number) => show(message, 'info', duration);

    return { toasts, show, success, error, info };
}
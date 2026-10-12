import { ref } from 'vue';

export interface Toast {
    id: number;
    type: 'success' | 'error' | 'info' | 'warning';
    title?: string;
    message: string;
    duration?: number;
}

const toasts = ref<Toast[]>([]);
let toastIdCounter = 0;

export function useToast() {
    const show = (
        message: string,
        type: 'success' | 'error' | 'info' | 'warning' = 'success',
        title?: string,
        duration: number = 3500
    ) => {
        const id = ++toastIdCounter;
        const newToast: Toast = { id, type, title, message, duration };
        toasts.value.push(newToast);

        if (duration > 0) {
            setTimeout(() => {
                remove(id);
            }, duration);
        }
        return id;
    };

    const success = (message: string, title?: string, duration = 3500) =>
        show(message, 'success', title, duration);

    const error = (message: string, title?: string, duration = 4000) =>
        show(message, 'error', title, duration);

    const info = (message: string, title?: string, duration = 3500) =>
        show(message, 'info', title, duration);

    const warning = (message: string, title?: string, duration = 3500) =>
        show(message, 'warning', title, duration);

    const remove = (id: number) => {
        const index = toasts.value.findIndex((t) => t.id === id);
        if (index !== -1) {
            toasts.value.splice(index, 1);
        }
    };

    return {
        toasts,
        show,
        success,
        error,
        info,
        warning,
        remove,
    };
}

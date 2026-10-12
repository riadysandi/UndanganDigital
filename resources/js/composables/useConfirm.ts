import { ref } from 'vue';

export interface ConfirmOptions {
    title?: string;
    message: string;
    confirmText?: string;
    cancelText?: string;
    type?: 'danger' | 'warning' | 'info';
}

const isOpen = ref(false);
const options = ref<ConfirmOptions>({
    title: 'Konfirmasi Tindakan',
    message: '',
    confirmText: 'Lanjutkan',
    cancelText: 'Batal',
    type: 'danger',
});

let resolvePromise: ((value: boolean) => void) | null = null;

export function useConfirm() {
    const confirmAction = (opts: ConfirmOptions): Promise<boolean> => {
        options.value = {
            title: opts.title || 'Konfirmasi Tindakan',
            message: opts.message,
            confirmText: opts.confirmText || 'Ya, Lanjutkan',
            cancelText: opts.cancelText || 'Batal',
            type: opts.type || 'danger',
        };
        isOpen.value = true;

        return new Promise<boolean>((resolve) => {
            resolvePromise = resolve;
        });
    };

    const handleConfirm = () => {
        isOpen.value = false;
        if (resolvePromise) resolvePromise(true);
        resolvePromise = null;
    };

    const handleCancel = () => {
        isOpen.value = false;
        if (resolvePromise) resolvePromise(false);
        resolvePromise = null;
    };

    return {
        isOpen,
        options,
        confirmAction,
        handleConfirm,
        handleCancel,
    };
}

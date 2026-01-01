<script setup lang="ts">
import { onMounted, onUnmounted, watch } from 'vue';

const props = defineProps<{
    show: boolean;
    maxWidth?: string;
}>();

const emit = defineEmits(['close']);

const close = () => {
    emit('close');
};

const closeOnEscape = (e: KeyboardEvent) => {
    if (e.key === 'Escape' && props.show) {
        close();
    }
};

onMounted(() => document.addEventListener('keydown', closeOnEscape));
onUnmounted(() => document.removeEventListener('keydown', closeOnEscape));

watch(
    () => props.show,
    () => {
        if (props.show) {
            document.body.style.overflow = 'hidden';
        } else {
            document.body.style.overflow = '';
        }
    }
);
</script>

<template>
    <div
        v-if="show"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/25 transition-opacity px-4"
        @click.self="close"
    >
        <div
            class="w-full rounded-lg bg-white shadow-lg transition-all max-h-[90vh] overflow-y-auto"
            :class="[maxWidth ?? 'max-w-xl']"
        >
            <slot />
        </div>
    </div>
</template>

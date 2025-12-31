<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { ref } from 'vue';
import Modal from '@/components/Modal.vue';

const props = defineProps<{
	resourceName: string;
	label: string | null;
	deleteUrl: string;
}>();

const emit = defineEmits(['close']);
const processing = ref(false);

const confirmDelete = () => {
	processing.value = true;
	router.delete(props.deleteUrl, {
		onSuccess: () => emit('close'),
		onFinish: () => (processing.value = false)
	});
};
</script>

<template>
	<Modal :show="true" max-width="max-w-sm" @close="$emit('close')">
		<div class="p-6">
			<h2 class="text-lg font-semibold text-gray-800">Confirm Deletion</h2>
			<p class="mt-2 text-gray-600">
				Are you sure you want to delete this {{ resourceName.toLowerCase() }}:
				<span class="font-semibold">{{ label }}</span
				>?
			</p>

			<div class="flex justify-end mt-6 space-x-2">
				<button
					@click="$emit('close')"
					class="px-4 py-2 rounded bg-gray-300 hover:bg-gray-400"
					:disabled="processing"
				>
					Cancel
				</button>
				<button
					@click="confirmDelete"
					class="px-4 py-2 rounded bg-red-500 hover:bg-red-600 text-white"
					:disabled="processing"
				>
					<span v-if="processing">Deleting...</span>
					<span v-else>Delete</span>
				</button>
			</div>
		</div>
	</Modal>
</template>

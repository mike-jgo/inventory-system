<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { watch } from 'vue';

const props = defineProps<{
	resourceName: string; // e.g. "Item" or "Category"
	mode: 'add' | 'edit';
	submitUrl: string; // e.g. "/items" or `/categories/{id}`
	method?: 'post' | 'put'; // optional override
	fields: {
		key: string;
		label: string;
		type: string;
		options?: { value: any; label: string }[];
	}[];
	model?: Record<string, any> | null; // existing values when editing
}>();

const emit = defineEmits(['close']);

// Initialize form with given model or blank
const form = useForm(
	props.fields.reduce(
		(acc, field) => {
			acc[field.key] = props.model ? (props.model[field.key] ?? '') : '';
			return acc;
		},
		{} as Record<string, any>
	)
);

// Reset when model changes
watch(
	() => props.model,
	(newVal) => {
		props.fields.forEach((field) => {
			form[field.key] = newVal ? (newVal[field.key] ?? '') : '';
		});
	},
	{ immediate: true }
);

const submit = () => {
	const method = props.method ?? (props.mode === 'add' ? 'post' : 'put');
	form.transform((data) => {
		if ('password' in data && data.password === '') {
			const { password, ...rest } = data;
			return rest;
		}
		return data;
	})[method](props.submitUrl, {
		onSuccess: () => {
			form.reset();
			emit('close');
		},
		preserveScroll: true
	});
};
</script>

<template>
	<div class="bg-black/25 fixed flex items-center justify-center inset-0 z-50">
		<div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-md">
			<h2 class="text-xl font-semibold mb-4">
				{{ mode === 'add' ? `Add ${resourceName}` : `Edit ${resourceName}` }}
			</h2>

			<form @submit.prevent="submit">
				<div
					v-for="field in fields"
					:key="field.key"
					class="mb-4"
				>
					<label class="block text-sm font-medium text-gray-700">{{ field.label }}</label>

					<template v-if="field.type === 'select'">
						<select
							v-model="form[field.key]"
							class="text-gray-700 mt-1 p-2 block w-full rounded-md border-gray-300 shadow-sm"
							required
						>
							<option
								disabled
								value=""
							>
								-- Select {{ field.label }} --
							</option>
							<option
								v-for="opt in field.options"
								:key="opt.value"
								:value="opt.value"
							>
								{{ opt.label }}
							</option>
						</select>
					</template>

					<template v-else>
						<input
							v-model="form[field.key]"
							:type="field.type"
							:placeholder="`Enter ${field.label}`"
							class="text-gray-700 mt-1 p-2 block w-full rounded-md border-gray-300 shadow-sm"
							:required="!(field.key === 'password' && mode === 'edit')"
						/>
					</template>

					<div
						v-if="form.errors[field.key]"
						class="text-red-500 text-sm"
					>
						{{ form.errors[field.key] }}
					</div>
				</div>

				<div class="flex justify-end space-x-2">
					<button
						type="button"
						@click="$emit('close')"
						class="px-4 py-2 rounded bg-gray-300 hover:bg-gray-400"
					>
						Cancel
					</button>
					<button
						type="submit"
						class="px-4 py-2 rounded bg-blue-500 hover:bg-blue-600 text-white"
						:disabled="form.processing"
					>
						{{
							form.processing
								? mode === 'add'
									? 'Saving...'
									: 'Updating...'
								: mode === 'add'
									? 'Save'
									: 'Update'
						}}
					</button>
				</div>
			</form>
		</div>
	</div>
</template>

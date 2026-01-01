<script setup lang="ts">
import { ref } from 'vue';
import Modal from '@/components/Modal.vue';

const props = defineProps<{
	resourceName: string;
	model: {
		id?: number;
		description?: string;
		subject_type?: string | null;
		causer?: { id: number; name?: string } | null;
		created_at?: string;
		properties?: Record<string, any>;
		remarks?: string | null;
	} | null;
	updateFields?: { field: string; oldValue: any; newValue: any; changed: boolean }[] | null;
}>();

const emit = defineEmits(['close', 'saveRemarks']);

const remarks = ref(props.model?.remarks || '');

// Emit remarks when saving
const saveRemarks = () => {
	if (remarks.value.trim() === '') return;
	emit('saveRemarks', remarks.value.trim());
	remarks.value = '';
};
</script>

<template>
	<Modal :show="true" max-width="max-w-lg" @close="$emit('close')">
		<div class="p-6 max-h-[85vh] overflow-y-auto">
			<!-- Modal Header -->
			<h2 class="text-xl font-semibold mb-4">{{ resourceName }}</h2>

			<!-- Header Info -->
			<div
				v-if="model"
				class="text-sm text-gray-800 space-y-1 mb-4"
			>
				<p><strong>User:</strong> {{ model.causer?.name ?? 'System' }}</p>
				<p><strong>Action:</strong> {{ model.description }}</p>
				<p><strong>Entity:</strong> {{ model.subject_type ?? 'N/A' }}</p>
				<p>
					<strong>Date:</strong>
					{{ model?.created_at ? new Date(model.created_at).toLocaleString() : '—' }}
				</p>
			</div>

			<!-- Changes Table -->
			<div
				v-if="model?.properties && (model.properties.attributes || model.properties.old)"
				class="mt-2"
			>
				<h3 class="text-md font-semibold text-gray-800 mb-2">
                    {{ model.description === 'created' ? 'Created Attributes' : (model.description === 'deleted' ? 'Deleted Attributes' : 'Changes') }}
                </h3>
				<table class="w-full text-sm border border-gray-200 rounded">
					<thead>
						<tr class="bg-gray-100 text-left">
							<th class="p-2">Field</th>
							<th class="p-2" v-if="model.description !== 'created'">Old</th>
							<th class="p-2" v-if="model.description !== 'deleted'">New</th>
						</tr>
					</thead>
					<tbody>
                        <!-- For Created: Show attributes as New -->
                        <template v-if="model.description === 'created' && model.properties.attributes">
                             <tr v-for="(val, key) in model.properties.attributes" :key="key">
                                <td class="p-2 font-medium capitalize">{{ String(key).replace('_', ' ') }}</td>
                                <td class="p-2 text-green-700 font-semibold">{{ val }}</td>
                            </tr>
                        </template>

                        <!-- For Deleted: Show old as Old -->
                        <template v-else-if="model.description === 'deleted' && model.properties.old">
                             <tr v-for="(val, key) in model.properties.old" :key="key">
                                <td class="p-2 font-medium capitalize">{{ String(key).replace('_', ' ') }}</td>
                                <td class="p-2 text-red-700 font-semibold">{{ val }}</td>
                            </tr>
                        </template>

                        <!-- For Updated: Show Diff -->
						<tr
							v-else-if="updateFields"
                            v-for="f in updateFields"
							:key="f.field"
							:class="{ 'bg-green-50': f.changed }"
						>
							<td class="p-2 font-medium capitalize">{{ f.field.replace('_', ' ') }}</td>
							<td class="p-2 text-gray-600">{{ f.oldValue ?? '—' }}</td>
							<td class="p-2">
								<span
									v-if="f.changed"
									class="font-semibold text-green-700"
								>
									{{ f.newValue ?? '—' }}
								</span>
								<span
									v-else
									class="text-gray-600"
									>{{ f.newValue ?? '—' }}</span
								>
							</td>
						</tr>
					</tbody>
				</table>
			</div>

			<!-- Remarks Section -->
			<div class="mt-6">
				<h3 class="text-md font-semibold text-gray-800 mb-2">Remarks</h3>
				<textarea
					v-model="remarks"
					placeholder="Enter remarks here..."
					class="w-full border border-gray-300 rounded p-2 text-sm focus:ring focus:ring-blue-300"
					rows="3"
				></textarea>
			</div>

			<!-- Buttons -->
			<div class="flex justify-end mt-6 space-x-2">
				<button
					type="button"
					@click="$emit('close')"
					class="px-4 py-2 rounded bg-gray-300 hover:bg-gray-400"
				>
					Close
				</button>
				<button
					type="button"
					@click="saveRemarks"
					class="px-4 py-2 rounded bg-blue-400 hover:bg-blue-500 text-white font-semibold"
				>
					Save Remarks
				</button>
			</div>
		</div>
	</Modal>
</template>

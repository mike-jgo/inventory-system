<script setup lang="ts">
import { useSlots, computed, getCurrentInstance } from 'vue';

const props = defineProps<{
	columns: { key: string; label: string }[];
	rows: any[];
	pagination?: {
		current_page: number;
		last_page: number;
		per_page: number;
		total: number;
		links: { url: string | null; label: string; active: boolean }[];
	};
}>();

const emit = defineEmits(['edit', 'delete', 'view', 'paginate']);

// Detect which listeners exist
const hasEdit = computed(() => !!emit && !!getCurrentInstance()?.vnode.props?.onEdit);
const hasDelete = computed(() => !!emit && !!getCurrentInstance()?.vnode.props?.onDelete);
const hasView = computed(() => !!emit && !!getCurrentInstance()?.vnode.props?.onView);

const hasPagination = computed(() => !!props.pagination && props.pagination.last_page > 1);
</script>

<template>
	<div class="overflow-x-auto bg-white shadow-md rounded-lg mt-6">
		<table class="min-w-full border-collapse">
			<thead class="bg-gray-100 text-gray-700">
				<tr>
					<th
						v-for="col in props.columns"
						:key="col.key"
						class="px-6 py-3 text-left text-sm font-medium"
					>
						{{ col.label }}
					</th>
					<th
						v-if="hasEdit || hasDelete || hasView"
						class="px-6 py-3 text-left text-sm font-medium"
					>
						Actions
					</th>
				</tr>
			</thead>

			<tbody>
				<tr
					v-for="row in props.rows"
					:key="row.id"
					class="hover:bg-gray-50 text-gray-900"
				>
					<td
						v-for="col in props.columns"
						:key="col.key"
						class="px-6 py-4 text-sm"
					>
						{{
							col.key.includes('.')
								? col.key.split('.').reduce((obj, key) => obj?.[key], row)
								: row[col.key]
						}}
					</td>

					<td
						v-if="hasEdit || hasDelete || hasView"
						class="px-6 py-4 text-sm space-x-2"
					>
						<button
							v-if="hasEdit"
							class="px-3 py-1 rounded bg-blue-500 hover:bg-blue-600 text-white text-sm"
							@click="emit('edit', row)"
						>
							Edit
						</button>

						<button
							v-if="hasDelete"
							class="px-3 py-1 rounded bg-red-500 hover:bg-red-600 text-white text-sm"
							@click="emit('delete', row)"
						>
							Delete
						</button>

						<button
							v-if="hasView"
							class="px-3 py-1 rounded bg-gray-500 hover:bg-gray-600 text-white text-sm"
							@click="emit('view', row)"
						>
							View
						</button>
					</td>
				</tr>
			</tbody>
		</table>

		<!-- Pagination -->
		<div
			v-if="hasPagination"
			class="flex justify-between items-center border-t px-6 py-4 text-sm text-gray-600"
		>
			<span>
				Page {{ props.pagination?.current_page }} of {{ props.pagination?.last_page }} —
				Total {{ props.pagination?.total }} records
			</span>

			<div class="flex items-center space-x-1">
				<button
					v-for="link in props.pagination?.links"
					:key="link.label"
					:disabled="!link.url"
					@click="emit('paginate', link.url)"
					v-html="link.label"
					class="px-3 py-1 rounded border text-sm"
					:class="[
						link.active
							? 'bg-blue-500 text-white border-blue-500'
							: 'bg-white text-gray-700 hover:bg-gray-100 border-gray-300',
						!link.url && 'opacity-50 cursor-not-allowed'
					]"
				></button>
			</div>
		</div>
	</div>
</template>

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

const emit = defineEmits(['edit', 'delete', 'view', 'paginate', 'row-click']);

// Detect which listeners exist
const hasEdit = computed(() => !!emit && !!getCurrentInstance()?.vnode.props?.onEdit);
const hasDelete = computed(() => !!emit && !!getCurrentInstance()?.vnode.props?.onDelete);
const hasView = computed(() => !!emit && !!getCurrentInstance()?.vnode.props?.onView);
const hasRowClick = computed(() => !!emit && !!getCurrentInstance()?.vnode.props?.onRowClick);

const hasPagination = computed(() => !!props.pagination && props.pagination.last_page > 1);
</script>

<template>
	<div class="bg-white shadow-md rounded-lg mt-6">
		<div class="overflow-x-auto">
			<table class="min-w-full border-collapse">
				<thead class="bg-gray-100 text-gray-700">
					<tr>
						<th
							v-for="col in props.columns"
							:key="col.key"
							class="px-3 sm:px-6 py-3 text-left text-sm font-medium whitespace-nowrap"
						>
							{{ col.label }}
						</th>
						<th
							v-if="hasEdit || hasDelete || hasView || $slots.actions"
							class="px-3 sm:px-6 py-3 text-left text-sm font-medium whitespace-nowrap"
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
						:class="{ 'cursor-pointer': hasRowClick }"
						@click="hasRowClick ? emit('row-click', row) : null"
					>
						<td
							v-for="col in props.columns"
							:key="col.key"
							class="px-3 sm:px-6 py-3 sm:py-4 text-sm"
						>
							<slot :name="`cell-${col.key}`" :row="row" :value="col.key.includes('.') ? col.key.split('.').reduce((obj, key) => obj?.[key], row) : row[col.key]">
								{{
									col.key.includes('.')
										? col.key.split('.').reduce((obj, key) => obj?.[key], row)
										: row[col.key]
								}}
							</slot>
						</td>

						<td
							v-if="hasEdit || hasDelete || hasView || $slots.actions"
							class="px-3 sm:px-6 py-3 sm:py-4 text-sm"
						>
							<div class="flex flex-wrap gap-2">
								<slot name="actions" :row="row"></slot>
								
								<button
									v-if="hasEdit"
									class="px-3 py-1 rounded bg-blue-500 hover:bg-blue-600 text-white text-sm min-w-[60px]"
									@click="emit('edit', row)"
								>
									Edit
								</button>

								<button
									v-if="hasDelete"
									class="px-3 py-1 rounded bg-red-500 hover:bg-red-600 text-white text-sm min-w-[60px]"
									@click="emit('delete', row)"
								>
									Delete
								</button>

								<button
									v-if="hasView"
									class="px-3 py-1 rounded bg-gray-500 hover:bg-gray-600 text-white text-sm min-w-[60px]"
									@click="emit('view', row)"
								>
									View
								</button>
							</div>
						</td>
					</tr>
				</tbody>
			</table>
		</div>

		<!-- Pagination -->
		<div
			v-if="hasPagination"
			class="flex flex-col sm:flex-row justify-between items-center gap-3 border-t px-3 sm:px-6 py-4 text-sm text-gray-600"
		>
			<span class="text-xs sm:text-sm">
				Page {{ props.pagination?.current_page }} of {{ props.pagination?.last_page }} —
				Total {{ props.pagination?.total }} records
			</span>

			<div class="flex items-center gap-1 flex-wrap justify-center">
				<button
					v-for="link in props.pagination?.links"
					:key="link.label"
					:disabled="!link.url"
					@click="emit('paginate', link.url)"
					v-html="link.label"
					class="px-2 sm:px-3 py-1 sm:py-2 rounded border text-xs sm:text-sm min-w-[36px] touch-manipulation"
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

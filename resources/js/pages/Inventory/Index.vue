<script setup lang="ts">
import { router, Head } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import Layout from '@/layouts/Layout.vue';
import DataTable from '@/components/DataTable.vue';
import FormModal from '@/components/FormModal.vue';
import ConfirmDeleteModal from '@/components/DeleteModal.vue';

defineOptions({
	layout: Layout
});

const props = defineProps<{
	inventories: {
		data: {
			id: number;
			name: string;
			supplier: string;
			quantity: number;
			cost_per_unit: number;
			reorder_level: number | null;
			total_value?: number;
		}[];
		current_page: number;
		last_page: number;
		per_page: number;
		total: number;
		links: { url: string | null; label: string; active: boolean }[];
	};
}>();

const page = usePage();
const user = computed(() => page.props.auth?.user);

const showAdd = ref(false);
const showEdit = ref(false);
const showDelete = ref(false);

const inventoryToEdit = ref<any | null>(null);
const inventoryToDelete = ref<any | null>(null);

const inventoryColumns = [
	{ key: 'name', label: 'Name' },
	{ key: 'supplier', label: 'Supplier' },
	{ key: 'quantity', label: 'Quantity (kg)' },
	{ key: 'cost_per_unit', label: 'Cost per Unit' },
	{ key: 'total_value', label: 'Total Value' },
	{ key: 'reorder_level', label: 'Reorder Level' }
];

const openEditModal = (inventory: any) => {
	inventoryToEdit.value = {
		id: inventory.id,
		name: inventory.name,
		supplier: inventory.supplier,
		quantity: inventory.quantity,
		cost_per_unit: inventory.cost_per_unit,
		reorder_level: inventory.reorder_level
	};
	showEdit.value = true;
};

const openDeleteModal = (inventory: any) => {
	inventoryToDelete.value = inventory;
	showDelete.value = true;
};

const handlePaginate = (url: string | null) => {
	if (url) router.visit(url, { preserveScroll: true });
};

// Compute total value for each item
const inventoriesWithTotalValue = computed(() => {
	return {
		...props.inventories,
		data: props.inventories.data.map((item) => ({
			...item,
			total_value: item.quantity * item.cost_per_unit
		}))
	};
});
</script>

<template>
	<Head title="Inventory Management" />

	<div class="p-4 sm:p-6 md:p-8">
		<div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
			<div>
				<h1 class="text-2xl sm:text-3xl font-semibold text-gray-900">Inventory Management</h1>
				<p class="mt-2 text-xs sm:text-sm text-gray-600">Track raw supplies and materials from suppliers (in kilograms)</p>
			</div>
			<button
				@click="showAdd = true"
				class="w-full sm:w-auto px-4 py-2 rounded bg-blue-500 hover:bg-blue-600 text-white touch-manipulation whitespace-nowrap"
			>
				+ Add Inventory Item
			</button>
		</div>

		<DataTable
			:columns="inventoryColumns"
			:rows="inventoriesWithTotalValue.data"
			:pagination="inventoriesWithTotalValue"
			@edit="openEditModal"
			@delete="openDeleteModal"
			@paginate="handlePaginate"
		>
			<template #cell-quantity="{ value, row }">
				<span :class="row.reorder_level && Number(value) <= Number(row.reorder_level) ? 'text-red-600 font-semibold' : ''">
					{{ Number(value).toFixed(2) }} kg
				</span>
			</template>
			<template #cell-cost_per_unit="{ value }">
				{{ new Intl.NumberFormat('en-PH', { style: 'currency', currency: 'PHP' }).format(Number( value)) }}
			</template>
			<template #cell-total_value="{ value }">
				{{ new Intl.NumberFormat('en-PH', { style: 'currency', currency: 'PHP' }).format(Number(value)) }}
			</template>
			<template #cell-reorder_level="{ value }">
				<span v-if="value !== null">{{ Number(value).toFixed(2) }} kg</span>
				<span v-else class="text-gray-400">-</span>
			</template>
		</DataTable>

		<!-- Add Modal -->
		<FormModal
			v-if="showAdd"
			resourceName="Inventory Item"
			mode="add"
			submit-url="/inventory"
			:fields="[
				{ key: 'name', label: 'Item Name', type: 'text' },
				{ key: 'supplier', label: 'Supplier', type: 'text' },
				{ key: 'quantity', label: 'Quantity (kg)', type: 'number', step: '0.01' },
				{ key: 'cost_per_unit', label: 'Cost per Unit (₱)', type: 'number', step: '0.01' },
				{ key: 'reorder_level', label: 'Reorder Level (kg)', type: 'number', step: '0.01' }
			]"
			@close="showAdd = false"
		/>

		<!-- Edit Modal -->
		<FormModal
			v-if="showEdit && inventoryToEdit"
			resourceName="Inventory Item"
			mode="edit"
			:model="inventoryToEdit"
			:submit-url="`/inventory/${inventoryToEdit.id}`"
			:fields="[
				{ key: 'name', label: 'Item Name', type: 'text' },
				{ key: 'supplier', label: 'Supplier', type: 'text' },
				{ key: 'quantity', label: 'Quantity (kg)', type: 'number', step: '0.01' },
				{ key: 'cost_per_unit', label: 'Cost per Unit (₱)', type: 'number', step: '0.01' },
				{ key: 'reorder_level', label: 'Reorder Level (kg)', type: 'number', step: '0.01' }
			]"
			@close="showEdit = false"
		/>

		<!-- Delete Modal -->
		<ConfirmDeleteModal
			v-if="showDelete && inventoryToDelete"
			resourceName="Inventory Item"
			:label="inventoryToDelete.name"
			:delete-url="`/inventory/${inventoryToDelete.id}`"
			@close="showDelete = false"
		/>
	</div>
</template>

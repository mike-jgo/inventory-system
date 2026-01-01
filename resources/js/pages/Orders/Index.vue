<script setup lang="ts">
import { router, Head } from '@inertiajs/vue3';
import { ref } from 'vue';
import Layout from '@/layouts/Layout.vue';
import DataTable from '@/components/DataTable.vue';
import { useForm } from '@inertiajs/vue3';
import Modal from '@/components/Modal.vue';
import SearchFilter from '@/components/SearchFilter.vue'

defineOptions({
	layout: Layout
});

const props = defineProps<{
	orders: {
		data: {
			id: number;
			customer_name: string | null;
			type: string;
			status: string;
			total_amount: number;
			created_at: string;
			user: { name: string };
			wastage: boolean; 
		}[];
		current_page: number;
		last_page: number;
		per_page: number;
		total: number;
		links: { url: string | null; label: string; active: boolean }[];
	};
	filters?: Record<string, any>;
}>();

const orderColumns = [
	{ key: 'id', label: 'ID' },
	{ key: 'customer_name', label: 'Customer' },
	{ key: 'user.name', label: 'Server' },
	{ key: 'type', label: 'Type' },
	{ key: 'status', label: 'Status' },
	{ key: 'wastage', label: 'Wastage' },
	{ key: 'total_amount', label: 'Total' },
	{ key: 'created_at', label: 'Date' }
];

const showCancel = ref(false);
const orderToCancel = ref<any | null>(null);
const cancelForm = useForm({
    wastage: false,
    status: 'cancelled'
});

const openCancelModal = (order: any) => {
    if (order.status === 'cancelled') return;
    orderToCancel.value = order;
    showCancel.value = true;
};

const submitCancel = () => {
    if (!orderToCancel.value) return;
    
    cancelForm.put(`/orders/${orderToCancel.value.id}`, {
        onSuccess: () => {
            showCancel.value = false;
            orderToCancel.value = null;
            cancelForm.reset();
        }
    });
};

const showComplete = ref(false);
const orderToComplete = ref<any | null>(null);
const completeForm = useForm({});

const openCompleteModal = (order: any) => {
    if (order.status !== 'pending') return;
    orderToComplete.value = order;
    showComplete.value = true;
};

const submitComplete = () => {
    if (!orderToComplete.value) return;

    completeForm.put(`/orders/${orderToComplete.value.id}/complete`, {
        onSuccess: () => {
            showComplete.value = false;
            orderToComplete.value = null;
        }
    });
};

const handlePaginate = (url: string | null) => {
	if (url) router.visit(url, { preserveScroll: true });
};

const handleViewOrder = (order: any) => {
	router.visit(`/orders/${order.id}`);
};

const formatDate = (dateString: string) => {
    const date = new Date(dateString);
    return date.getFullYear() + '-' +
        String(date.getMonth() + 1).padStart(2, '0') + '-' +
        String(date.getDate()).padStart(2, '0') + ' ' +
        String(date.getHours()).padStart(2, '0') + ':' +
        String(date.getMinutes()).padStart(2, '0') + ':' +
        String(date.getSeconds()).padStart(2, '0');
};

const filterConfig = [
	{
		key: 'status',
		label: 'All Status',
		type: 'select' as const,
		options: [
			{ value: 'pending', label: 'Pending' },
			{ value: 'completed', label: 'Completed' },
			{ value: 'cancelled', label: 'Cancelled' }
		]
	},
	{
		key: 'type',
		label: 'All Types',
		type: 'select' as const,
		options: [
			{ value: 'dine_in', label: 'Dine In' },
			{ value: 'takeout', label: 'Takeout' },
			{ value: 'online', label: 'Online' }
		]
	}
];

const handleFilterUpdate = (filters: Record<string, any>) => {
	router.visit('/orders', {
		data: filters,
		preserveState: true,
		preserveScroll: true
	});
};
</script>

<template>
	<Head title="Orders" />

	<div class="p-4 sm:p-6 md:p-8">
		<div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4 mb-6">
			<h1 class="text-2xl sm:text-3xl font-semibold text-gray-900">Orders</h1>
			<button
				@click="router.visit('/orders/create')"
				class="w-full sm:w-auto px-4 py-2 rounded bg-indigo-600 hover:bg-indigo-700 text-white font-medium shadow-sm transition-colors touch-manipulation"
			>
				+ New Order (POS)
			</button>
		</div>

		<SearchFilter
			search-placeholder="Search by order ID or customer name..."
			:filters="filterConfig"
			:current-filters="filters"
			@update:filters="handleFilterUpdate"
		/>

		<DataTable
			:columns="orderColumns"
			:rows="orders.data"
			:pagination="orders"
			@paginate="handlePaginate"
			@row-click="handleViewOrder"
		>
            <template #cell-status="{ value }">
                <span v-if="value === 'completed'" class="text-green-600 font-bold">Completed</span>
                <span v-else-if="value === 'cancelled'" class="text-red-600 font-bold">Cancelled</span>
                <span v-else class="capitalize text-blue-600 font-bold">{{ value }}</span>
            </template>
            <template #actions="{ row }">
                 <button 
                    v-if="(row.status === 'pending') || (row.status !== 'pending' && $page.props.auth.user.roles?.includes('Super Admin'))"
                    @click.stop="router.visit(`/orders/${row.id}/edit`)" 
                    class="px-3 py-1 rounded bg-blue-500 hover:bg-blue-600 text-white text-sm min-w-[60px] touch-manipulation"
                >
                    Edit
                </button>
                
                 <button 
                    v-if="row.status === 'pending'"
                    @click.stop="openCompleteModal(row)" 
                    class="px-3 py-1 rounded bg-green-500 hover:bg-green-600 text-white text-sm min-w-[60px] touch-manipulation"
                >
                    Complete
                </button>

                 <button 
                    v-if="row.status !== 'cancelled' && row.status !== 'completed'"
                    @click.stop="openCancelModal(row)" 
                    class="px-3 py-1 rounded bg-red-500 hover:bg-red-600 text-white text-sm min-w-[60px] touch-manipulation"
                >
                    Cancel
                </button>
            </template>
			<template #cell-total_amount="{ value }">
				{{ new Intl.NumberFormat('en-PH', { style: 'currency', currency: 'PHP' }).format(value) }}
			</template>
            <template #cell-created_at="{ value }">
                {{ formatDate(value) }}
            </template>
        </DataTable>

        <!-- Complete Confirmation Modal -->
        <Modal :show="showComplete" @close="showComplete = false">
             <div class="p-4 sm:p-6">
                 <h2 class="text-lg font-medium text-gray-900">Complete Order #{{ orderToComplete?.id }}</h2>
                 <p class="mt-1 text-sm text-gray-600">
                     Are you sure you want to mark this order as completed?
                 </p>
                 <div class="mt-6 flex flex-col-reverse sm:flex-row sm:justify-end gap-3">
                     <button @click="showComplete = false" class="w-full sm:w-auto px-4 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200 touch-manipulation">
                         Cancel
                     </button>
                     <button @click="submitComplete" class="w-full sm:w-auto px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 touch-manipulation" :disabled="completeForm.processing">
                         Confirm Completion
                     </button>
                 </div>
             </div>
        </Modal>

        <!-- Cancel Logic Modal -->
         <Modal :show="showCancel" @close="showCancel = false">
            <div class="p-4 sm:p-6">
                <h2 class="text-lg font-medium text-gray-900">Cancel Order #{{ orderToCancel?.id }}</h2>
                <p class="mt-1 text-sm text-gray-600">
                    Are you sure you want to cancel this order?
                </p>
                
                <div class="mt-4">
                    <label class="flex items-start space-x-3">
                        <input type="checkbox" v-model="cancelForm.wastage" class="form-checkbox h-5 w-5 text-red-600 mt-0.5">
                        <div>
                            <span class="text-gray-900 font-medium">Mark as Wastage?</span>
                            <p class="text-sm text-gray-500 mt-1">
                                If checked, items will <strong>NOT</strong> be returned to inventory.
                                <br>
                                If unchecked, items will be restocked.
                            </p>
                        </div>
                    </label>
                </div>

                <div class="mt-6 flex flex-col-reverse sm:flex-row sm:justify-end gap-3">
                    <button @click="showCancel = false" class="w-full sm:w-auto px-4 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200 touch-manipulation">
                        Dismiss
                    </button>
                    <button @click="submitCancel" class="w-full sm:w-auto px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 touch-manipulation" :disabled="cancelForm.processing">
                        Confirm Cancellation
                    </button>
                </div>
            </div>
         </Modal>
	</div>
</template>

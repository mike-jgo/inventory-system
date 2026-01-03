<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import Layout from '@/layouts/Layout.vue';
import { route } from 'ziggy-js';

defineOptions({
	layout: Layout
});

const props = defineProps<{
	order: {
		id: number;
		customer_name: string | null;
		type: string;
		status: string;
		total_amount: number;
		payment_method: string;
		amount_paid: number;
		change_due: number;
		payment_reference: string | null;
		created_at: string;
		updated_at: string;
		user: {
			id: number;
			name: string;
		};
		order_items: {
			id: number;
			quantity: number;
			price: number;
			item: {
				id: number;
				name: string;
			};
		}[];
	};
}>();

const getStatusColor = (status: string) => {
	switch (status) {
		case 'pending':
			return 'bg-yellow-100 text-yellow-800';
		case 'completed':
			return 'bg-green-100 text-green-800';
		case 'cancelled':
			return 'bg-red-100 text-red-800';
		default:
			return 'bg-gray-100 text-gray-800';
	}
};

const getTypeLabel = (type: string) => {
	switch (type) {
		case 'dine_in':
			return 'Dine In';
		case 'takeout':
			return 'Takeout';
		case 'online':
			return 'Online';
		default:
			return type;
	}
};

const formatDate = (dateString: string) => {
	return new Date(dateString).toLocaleDateString('en-US', {
		month: 'short',
		day: 'numeric',
		year: 'numeric',
		hour: '2-digit',
		minute: '2-digit'
	});
};

const getPaymentMethodLabel = (method: string) => {
	switch (method) {
		case 'cash':
			return 'Cash';
		case 'gcash':
			return 'GCash';
		default:
			return method;
	}
};
</script>

<template>
	<Head :title="`Order #${order.id}`" />

	<div class="p-8">
		<!-- Header -->
		<div class="mb-6">
			<Link
				:href="route('orders.index')"
				class="text-gray-600 hover:text-gray-900 flex items-center text-sm font-medium mb-4"
			>
				<svg
					xmlns="http://www.w3.org/2000/svg"
					class="h-4 w-4 mr-1"
					fill="none"
					viewBox="0 0 24 24"
					stroke="currentColor"
				>
					<path
						stroke-linecap="round"
						stroke-linejoin="round"
						stroke-width="2"
						d="M10 19l-7-7m0 0l7-7m-7 7h18"
					/>
				</svg>
				Back to Orders
			</Link>
			<div class="flex justify-between items-start">
				<div>
					<h1 class="text-3xl font-semibold text-gray-900">Order #{{ order.id }}</h1>
					<p class="text-gray-600 mt-1">{{ formatDate(order.created_at) }}</p>
				</div>
				<span
					:class="getStatusColor(order.status)"
					class="px-4 py-2 rounded-full text-sm font-medium capitalize"
				>
					{{ order.status }}
				</span>
			</div>
		</div>

		<!-- Order Details -->
		<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
			<!-- Order Info Card -->
			<div class="bg-white rounded-xl shadow-md p-6">
				<h2 class="text-lg font-semibold text-gray-900 mb-4">Order Information</h2>
				<div class="space-y-3">
					<div>
						<p class="text-sm text-gray-600">Customer Name</p>
						<p class="font-medium text-gray-900">
							{{ order.customer_name || 'Guest' }}
						</p>
					</div>
					<div>
						<p class="text-sm text-gray-600">Order Type</p>
						<p class="font-medium text-gray-900">{{ getTypeLabel(order.type) }}</p>
					</div>
					<div>
						<p class="text-sm text-gray-600">Created By</p>
						<p class="font-medium text-gray-900">{{ order.user.name }}</p>
					</div>
				</div>
			</div>

			<!-- Dates Card -->
			<div class="bg-white rounded-xl shadow-md p-6">
				<h2 class="text-lg font-semibold text-gray-900 mb-4">Dates</h2>
				<div class="space-y-3">
					<div>
						<p class="text-sm text-gray-600">Created At</p>
						<p class="font-medium text-gray-900">{{ formatDate(order.created_at) }}</p>
					</div>
					<div>
						<p class="text-sm text-gray-600">Last Updated</p>
						<p class="font-medium text-gray-900">{{ formatDate(order.updated_at) }}</p>
					</div>
				</div>
			</div>

			<!-- Total Card -->
			<div class="bg-white rounded-xl shadow-md p-6">
				<h2 class="text-lg font-semibold text-gray-900 mb-4">Total Amount</h2>
				<p class="text-3xl font-bold text-gray-900">
					{{
						new Intl.NumberFormat('en-PH', {
							style: 'currency',
							currency: 'PHP'
						}).format(order.total_amount)
					}}
				</p>
			</div>
		</div>

		<!-- Payment Details -->
		<div class="bg-white rounded-xl shadow-md p-6 mb-6">
			<h2 class="text-xl font-semibold text-gray-900 mb-4">Payment Details</h2>
			<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
				<div>
					<p class="text-sm text-gray-600">Payment Method</p>
					<p class="font-medium text-gray-900 capitalize">
						{{ order.payment_method === 'cash' ? '💵 Cash' : '📱 GCash' }}
					</p>
				</div>
				<div>
					<p class="text-sm text-gray-600">Amount Paid</p>
					<p class="font-medium text-gray-900">
						{{
							new Intl.NumberFormat('en-PH', {
								style: 'currency',
								currency: 'PHP'
							}).format(order.amount_paid)
						}}
					</p>
				</div>
				<div v-if="order.payment_method === 'cash' && order.change_due > 0">
					<p class="text-sm text-gray-600">Change</p>
					<p class="font-medium text-green-700">
						{{
							new Intl.NumberFormat('en-PH', {
								style: 'currency',
								currency: 'PHP'
							}).format(order.change_due)
						}}
					</p>
				</div>
				<div v-if="order.payment_method === 'gcash' && order.payment_reference">
					<p class="text-sm text-gray-600">Transaction Reference</p>
					<p class="font-medium text-gray-900 font-mono text-sm">
						{{ order.payment_reference }}
					</p>
				</div>
			</div>
		</div>

		<!-- Order Items -->
		<div class="bg-white rounded-xl shadow-md p-6">
			<h2 class="text-xl font-semibold text-gray-900 mb-4">Order Items</h2>
			<div class="overflow-x-auto">
				<table class="min-w-full divide-y divide-gray-200">
					<thead class="bg-gray-50">
						<tr>
							<th
								class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
							>
								Item
							</th>
							<th
								class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
							>
								Price
							</th>
							<th
								class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
							>
								Quantity
							</th>
							<th
								class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
							>
								Subtotal
							</th>
						</tr>
					</thead>
					<tbody class="bg-white divide-y divide-gray-200">
						<tr
							v-for="orderItem in order.order_items"
							:key="orderItem.id"
						>
							<td class="px-6 py-4 whitespace-nowrap">
								<div class="text-sm font-medium text-gray-900">
									{{ orderItem.item.name }}
								</div>
							</td>
							<td class="px-6 py-4 whitespace-nowrap">
								<div class="text-sm text-gray-900">
									{{
										new Intl.NumberFormat('en-PH', {
											style: 'currency',
											currency: 'PHP'
										}).format(orderItem.price)
									}}
								</div>
							</td>
							<td class="px-6 py-4 whitespace-nowrap">
								<div class="text-sm text-gray-900">{{ orderItem.quantity }}</div>
							</td>
							<td class="px-6 py-4 whitespace-nowrap">
								<div class="text-sm font-medium text-gray-900">
									{{
										new Intl.NumberFormat('en-PH', {
											style: 'currency',
											currency: 'PHP'
										}).format(orderItem.price * orderItem.quantity)
									}}
								</div>
							</td>
						</tr>
					</tbody>
					<tfoot class="bg-gray-50">
						<tr>
							<td
								colspan="3"
								class="px-2 py-2 text-right text-sm font-semibold text-gray-900"
							>
								Total:
							</td>
							<td class="px-6 py-4 whitespace-nowrap">
								<div class="text-sm font-bold text-gray-900">
									{{
										new Intl.NumberFormat('en-PH', {
											style: 'currency',
											currency: 'PHP'
										}).format(order.total_amount)
									}}
								</div>
							</td>
						</tr>
					</tfoot>
				</table>
			</div>
		</div>
	</div>
</template>

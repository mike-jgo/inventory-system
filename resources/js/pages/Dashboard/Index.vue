<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import Layout from '@/layouts/Layout.vue';
import { route } from 'ziggy-js';

defineOptions({
	layout: Layout
});

const props = defineProps<{
	stats: {
		totalItems: number;
		totalOrders: number;
		pendingOrders: number;
		lowStockItems: number;
	};
	recentOrders: {
		id: number;
		created_at: string;
		status: string;
		items: any[];
	}[];
}>();

const page = usePage();
const auth = computed(() => page.props.auth?.user);

const statCards = [
	{
		title: 'Total Items',
		value: props.stats.totalItems,
		color: 'bg-blue-500',
		link: route('items.index')
	},
	{
		title: 'Total Orders',
		value: props.stats.totalOrders,
		color: 'bg-green-500',
		link: route('orders.index')
	},
	{
		title: 'Pending Orders',
		value: props.stats.pendingOrders,
		color: 'bg-yellow-500',
		link: route('orders.index') + '?status=pending'
	},
	{
		title: 'Low Stock Items',
		value: props.stats.lowStockItems,
		color: 'bg-red-500',
		link: route('items.index')
	}
];

const quickActions = [
	{
		title: 'Create Order',
		description: 'Start a new order',
		color: 'bg-blue-600 hover:bg-blue-700',
		link: route('orders.create')
	},
	{
		title: 'Manage Items',
		description: 'View and edit items',
		color: 'bg-green-600 hover:bg-green-700',
		link: route('items.index')
	},
	{
		title: 'View Activity Log',
		description: 'Check recent activities',
		color: 'bg-purple-600 hover:bg-purple-700',
		link: route('activity-log.index')
	}
];

const formatDate = (dateString: string) => {
	return new Date(dateString).toLocaleDateString('en-US', {
		month: 'short',
		day: 'numeric',
		year: 'numeric',
		hour: '2-digit',
		minute: '2-digit'
	});
};

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
</script>

<template>
	<Head title="Dashboard" />

	<div class="p-8">
		<!-- Welcome Section -->
		<div class="mb-8">
			<h1 class="text-4xl font-bold text-gray-900 mb-2">
				Welcome back, {{ auth?.name }}!
			</h1>
			<p class="text-gray-600 text-lg">Here's what's happening with your inventory today.</p>
		</div>

		<!-- Statistics Cards -->
		<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
			<Link
				v-for="stat in statCards"
				:key="stat.title"
				:href="stat.link"
				class="bg-white rounded-xl shadow-md p-6 hover:shadow-lg transition-shadow duration-200 cursor-pointer"
			>
				<div class="flex items-center justify-between">
					<div>
						<p class="text-gray-600 text-sm font-medium mb-1">{{ stat.title }}</p>
						<p class="text-3xl font-bold text-gray-900">{{ stat.value }}</p>
					</div>
				</div>
			</Link>
		</div>

		<!-- Quick Actions -->
		<div class="mb-8">
			<h2 class="text-2xl font-semibold text-gray-900 mb-4">Quick Actions</h2>
			<div class="grid grid-cols-1 md:grid-cols-3 gap-4">
				<Link
					v-for="action in quickActions"
					:key="action.title"
					:href="action.link"
					:class="action.color"
					class="text-white rounded-lg shadow-md p-6 transition-all duration-200 transform hover:scale-105"
				>
					<h3 class="text-xl font-semibold mb-2">{{ action.title }}</h3>
					<p class="text-white/90 text-sm">{{ action.description }}</p>
				</Link>
			</div>
		</div>

		<!-- Recent Orders -->
		<div class="bg-white rounded-xl shadow-md p-6">
			<div class="flex justify-between items-center mb-4">
				<h2 class="text-2xl font-semibold text-gray-900">Recent Orders</h2>
				<Link
					:href="route('orders.index')"
					class="text-blue-600 hover:text-blue-700 font-medium text-sm"
				>
					View All →
				</Link>
			</div>

			<div v-if="recentOrders.length > 0" class="space-y-3">
				<Link
					v-for="order in recentOrders"
					:key="order.id"
					:href="route('orders.show', order.id)"
					class="block p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors"
				>
					<div class="flex justify-between items-start">
						<div>
							<p class="font-medium text-gray-900">Order #{{ order.id }}</p>
							<p class="text-sm text-gray-600 mt-1">{{ formatDate(order.created_at) }}</p>
						</div>
						<span
							:class="getStatusColor(order.status)"
							class="px-3 py-1 rounded-full text-xs font-medium capitalize"
						>
							{{ order.status }}
						</span>
					</div>
				</Link>
			</div>

			<div v-else class="text-center py-8 text-gray-500">
				<p>No recent orders</p>
			</div>
		</div>
	</div>
</template>

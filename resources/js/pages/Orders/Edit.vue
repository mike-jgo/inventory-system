<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { ref, computed, onMounted } from 'vue';
import Layout from '@/layouts/Layout.vue';
import { usePosCart } from '@/composables/usePosCart';
import { usePosPayment } from '@/composables/usePosPayment';
import PosEditDesktop from '@/components/pos/edit/PosEditDesktop.vue';
import PosEditMobile from '@/components/pos/edit/PosEditMobile.vue';

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
		order_items: {
			id: number;
			item_id: number;
			quantity: number;
			price: number;
			item: {
				id: number;
				name: string;
				price: number;
				quantity: number;
			};
		}[];
		payment_method?: string;
		amount_paid?: number;
		payment_reference?: string;
	};
	categories: {
		id: number;
		name: string;
		items: {
			id: number;
			name: string;
			price: number;
			quantity: number;
		}[];
	}[];
}>();

// State
const activeCategoryId = ref(props.categories[0]?.id || null);
const searchQuery = ref('');

// Cart management using composable
const cartApi = usePosCart({
	mode: 'edit',
	originalOrderItems: props.order.order_items.map((oi) => ({
		item_id: oi.item_id,
		quantity: oi.quantity
	}))
});

// Initialize cart from order
onMounted(() => {
	cartApi.initializeCart(props.order.order_items);
});

// Computed: filtered items
const activeCategoryItems = computed(() => {
	if (!activeCategoryId.value) return [];

	let items = props.categories.find((c) => c.id === activeCategoryId.value)?.items || [];

	if (searchQuery.value) {
		const q = searchQuery.value.toLowerCase();
		items = items.filter((i) => i.name.toLowerCase().includes(q));
	}

	return items;
});

// Form
const form = useForm({
	customer_name: props.order.customer_name || '',
	type: props.order.type,
	status: props.order.status,
	payment_method: (props.order.payment_method || 'cash') as 'cash' | 'gcash',
	amount_paid: props.order.amount_paid || 0,
	payment_reference: props.order.payment_reference || '',
	items: [] as { id: number; quantity: number }[]
});

// Payment management using composable
const paymentApi = usePosPayment(cartApi.cartTotal, form);

// Submit handler
const submitOrder = () => {
	if (cartApi.cart.value.length === 0) return;

	form.items = cartApi.cart.value.map((c) => ({
		id: c.item.id,
		quantity: c.quantity
	}));

	form.put(`/orders/${props.order.id}`, {
		onSuccess: () => {
			// Redirected by backend
		}
	});
};
</script>

<template>
	<Head title="Edit Order" />

	<!-- Desktop Layout -->
	<div class="hidden lg:block">
		<PosEditDesktop
			:order="order"
			:categories="categories"
			:activeCategoryId="activeCategoryId"
			:searchQuery="searchQuery"
			:activeCategoryItems="activeCategoryItems"
			:cart="cartApi.cart.value"
			:cartTotal="cartApi.cartTotal.value"
			:cartItemCount="cartApi.cartItemCount.value"
			:form="form"
			:addToCart="cartApi.addToCart"
			:removeFromCart="cartApi.removeFromCart"
			:updateQuantity="cartApi.updateQuantity"
			:getRemainingStock="cartApi.getRemainingStock"
			:getMaxStock="cartApi.getMaxStock"
			:changeDue="paymentApi.changeDue.value"
			:setExactAmount="paymentApi.setExactAmount"
			:addCash="paymentApi.addCash"
			:quickCashOptions="paymentApi.quickCashOptions.value"
			@update:activeCategoryId="activeCategoryId = $event"
			@update:searchQuery="searchQuery = $event"
			@submit="submitOrder"
		/>
	</div>

	<!-- Mobile Layout -->
	<div class="lg:hidden">
		<PosEditMobile
			:order="order"
			:categories="categories"
			:activeCategoryId="activeCategoryId"
			:searchQuery="searchQuery"
			:activeCategoryItems="activeCategoryItems"
			:cart="cartApi.cart.value"
			:cartTotal="cartApi.cartTotal.value"
			:cartItemCount="cartApi.cartItemCount.value"
			:form="form"
			:addToCart="cartApi.addToCart"
			:removeFromCart="cartApi.removeFromCart"
			:updateQuantity="cartApi.updateQuantity"
			:getRemainingStock="cartApi.getRemainingStock"
			:getMaxStock="cartApi.getMaxStock"
			:changeDue="paymentApi.changeDue.value"
			:setExactAmount="paymentApi.setExactAmount"
			:addCash="paymentApi.addCash"
			:quickCashOptions="paymentApi.quickCashOptions.value"
			@update:activeCategoryId="activeCategoryId = $event"
			@update:searchQuery="searchQuery = $event"
			@submit="submitOrder"
		/>
	</div>
</template>

<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import Layout from '@/layouts/Layout.vue';
import { usePosCart } from '@/composables/usePosCart';
import { usePosPayment } from '@/composables/usePosPayment';
import PosCreateDesktop from '@/components/pos/create/PosCreateDesktop.vue';
import PosCreateMobile from '@/components/pos/create/PosCreateMobile.vue';

defineOptions({
	layout: Layout
});

const props = defineProps<{
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
const cartApi = usePosCart({ mode: 'create' });

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
	customer_name: '',
	type: 'dine_in',
	status: 'pending',
	payment_method: 'cash' as 'cash' | 'gcash',
	amount_paid: 0,
	payment_reference: '',
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

	form.post('/orders', {
		onSuccess: () => {
			// Controller redirects to Index
		}
	});
};
</script>

<template>
	<Head title="New Order (POS)" />

	<!-- Desktop Layout -->
	<div class="hidden lg:block">
		<PosCreateDesktop
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
		<PosCreateMobile
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

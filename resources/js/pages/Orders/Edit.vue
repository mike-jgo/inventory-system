<script setup lang="ts">
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ref, computed, onMounted } from 'vue';
import Layout from '@/layouts/Layout.vue';

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
				quantity: number; // Current stock available
			};
		}[];
	};
	categories: {
		id: number;
		name: string;
		items: {
			id: number;
			name: string;
			price: number;
			quantity: number; // Stock available
		}[];
	}[];
}>();

// State
const activeCategoryId = ref(props.categories[0]?.id || null);
const cart = ref<{ item: any; quantity: number }[]>([]);
const searchQuery = ref('');

// Initialize Cart from Order
onMounted(() => {
	cart.value = props.order.order_items.map((oi) => ({
		item: {
			...oi.item
			// Important: The item.quantity from DB is *current stock*.
			// For editing, "available" stock = current stock + quantity in this order (if we reduce it).
			// But for simple check, we treat it as is.
			// The item object here should match structure of category items.
		},
		quantity: oi.quantity
	}));
});

// Computed
const activeCategoryItems = computed(() => {
	if (!activeCategoryId.value) return [];

	let items = props.categories.find((c) => c.id === activeCategoryId.value)?.items || [];

	if (searchQuery.value) {
		const q = searchQuery.value.toLowerCase();
		items = items.filter((i) => i.name.toLowerCase().includes(q));
	}

	return items;
});

const cartTotal = computed(() => {
	return cart.value.reduce((acc, cartItem) => {
		return acc + Number(cartItem.item.price) * cartItem.quantity;
	}, 0);
});

// Helper Functions
const getOriginalQuantity = (itemId: number) => {
	const originalItem = props.order.order_items.find((oi) => oi.item_id === itemId);
	return originalItem ? originalItem.quantity : 0;
};

const getCartQuantity = (itemId: number) => {
	const cartItem = cart.value.find((c) => c.item.id === itemId);
	return cartItem ? cartItem.quantity : 0;
};

const getMaxStock = (item: any) => {
	// Available to this order = Current Shelf Stock + Originally Held Stock
	return item.quantity + getOriginalQuantity(item.id);
};

const getRemainingStock = (item: any) => {
	return getMaxStock(item) - getCartQuantity(item.id);
};

// Actions
const addToCart = (item: any) => {
	if (getRemainingStock(item) <= 0) return;
	const existing = cart.value.find((c) => c.item.id === item.id);
	if (existing) {
		// Stock Check Logic for Edit:
		// If existing item was in original order, we need to account for that.
		// Simplified: The backend checks strict stock. Frontend provides basic guidance.
		// item.quantity is *current available stock*.
		// If we increase quantity, we consume that stock.

		// Let's rely on backend validation for strictness or complex calculation.
		// But basic check:
		// validation: if (existing.quantity + 1 > item.quantity (stock) + (original_quantity_in_order))
		// This is tricky. For now, simple check against current stock for *new* additions.

		existing.quantity++;
	} else {
		cart.value.push({ item, quantity: 1 });
	}
};

const removeFromCart = (index: number) => {
	cart.value.splice(index, 1);
};

const updateQuantity = (index: number, delta: number) => {
	const cartItem = cart.value[index];
	const newQty = cartItem.quantity + delta;

	if (newQty <= 0) {
		removeFromCart(index);
	} else {
		cartItem.quantity = newQty;
	}
};

// Checkout
const form = useForm({
	customer_name: props.order.customer_name || '',
	type: props.order.type,
	status: props.order.status,
	payment_method: (props.order as any).payment_method || 'cash',
	amount_paid: (props.order as any).amount_paid || 0,
	payment_reference: (props.order as any).payment_reference || '',
	items: [] as { id: number; quantity: number }[]
});

// Payment computed
const changeDue = computed(() => {
	if (form.payment_method === 'cash') {
		return Math.max(0, form.amount_paid - cartTotal.value);
	}
	return 0;
});

// Round up to the next multiple (e.g., next 100, 500, 1000)
const roundUp = (value: number, step: number) => {
  if (step <= 0) return value;
  return Math.ceil(value / step) * step;
};

const setExactAmount = () => {
  form.amount_paid = Number(cartTotal.value.toFixed(2));
};

const setRoundedAmount = (step: number) => {
  const total = cartTotal.value;
  form.amount_paid = roundUp(total, step);
};

const addCash = (delta: number) => {
  const current = Number(form.amount_paid || 0);
  form.amount_paid = current + delta;
};

const quickCashOptions = computed(() => {
  const total = cartTotal.value;

  // Typical PH bill-ish steps
  const steps = [100, 200, 500, 1000];

  // Rounded targets (only show if > total and unique)
  const roundedTargets = steps
    .map((s) => roundUp(total, s))
    .filter((v, i, arr) => v > total && arr.indexOf(v) === i)
    .slice(0, 3); // don't spam too many

  // Also suggest "exact + 100/200/500" for fast change-making
  const plusTargets = [100, 200, 500]
    .map((d) => Math.ceil((total + d) * 100) / 100)
    .filter((v, i, arr) => arr.indexOf(v) === i)
    .slice(0, 2);

  // Merge, unique, and cap
  const all = [...roundedTargets, ...plusTargets]
    .filter((v, i, arr) => arr.indexOf(v) === i)
    .sort((a, b) => a - b)
    .slice(0, 4);

  return all;
});



const submitOrder = () => {
	if (cart.value.length === 0) return;

	form.items = cart.value.map((c) => ({
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

	<div class="h-[calc(100vh-64px)] flex flex-col lg:flex-row bg-gray-100 overflow-hidden">
		<!-- Left Panel: Menu -->
		<div class="flex-1 flex flex-col overflow-hidden">
			<!-- Header/Filters -->
			<div class="bg-white p-3 sm:p-4 shadow-sm z-10 flex flex-col space-y-3">
				<div class="flex items-center justify-between">
					<Link
						href="/orders"
						class="text-gray-600 hover:text-gray-900 flex items-center text-sm font-medium touch-manipulation"
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
					<h1 class="text-base sm:text-lg font-bold text-gray-800">
						Edit Order #{{ order.id }}
					</h1>
				</div>
				<input
					type="text"
					v-model="searchQuery"
					placeholder="Search items..."
					class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 p-2 text-sm sm:text-base"
				/>

				<div class="flex space-x-2 overflow-x-auto pb-2 scrollbar-hide -mx-3 px-3">
					<button
						v-for="cat in categories"
						:key="cat.id"
						@click="activeCategoryId = cat.id"
						:class="[
							'px-3 sm:px-4 py-2 rounded-full text-sm font-medium whitespace-nowrap transition-colors touch-manipulation',
							activeCategoryId === cat.id
								? 'bg-indigo-600 text-white'
								: 'bg-gray-200 text-gray-700 hover:bg-gray-300'
						]"
					>
						{{ cat.name }}
					</button>
				</div>
			</div>

			<!-- Items Grid -->
			<div class="flex-1 overflow-y-auto p-3 sm:p-4">
				<div
					class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-3 xl:grid-cols-4 gap-3 sm:gap-4"
				>
					<button
						v-for="item in activeCategoryItems"
						:key="item.id"
						@click="addToCart(item)"
						:disabled="getRemainingStock(item) <= 0"
						:class="[
							'rounded-lg shadow p-3 sm:p-4 flex flex-col items-center justify-between transition-shadow h-28 sm:h-32 touch-manipulation',
							getRemainingStock(item) <= 0
								? 'bg-gray-100 opacity-75 cursor-not-allowed grayscale'
								: 'bg-white hover:shadow-md active:shadow-sm'
						]"
					>
						<span
							class="font-semibold text-gray-900 text-center line-clamp-2 text-xs sm:text-sm"
							>{{ item.name }}</span
						>
						<div class="mt-2 text-center">
							<span class="block text-indigo-600 font-bold text-sm sm:text-base">{{
								new Intl.NumberFormat('en-PH', {
									style: 'currency',
									currency: 'PHP'
								}).format(item.price)
							}}</span>
							<span
								:class="[
									'text-xs font-medium',
									getRemainingStock(item) <= 0 ? 'text-red-500' : 'text-gray-500'
								]"
							>
								{{
									getRemainingStock(item) <= 0
										? 'Out of Stock'
										: `${getRemainingStock(item)} in stock`
								}}
							</span>
						</div>
					</button>
				</div>
				<div
					v-if="activeCategoryItems.length === 0"
					class="text-center text-gray-500 py-10"
				>
					No items found.
				</div>
			</div>
		</div>

		<!-- Right Panel: Cart -->
		<div
			class="w-full lg:w-96 bg-white shadow-xl flex flex-col border-l border-gray-200 max-h-[50vh] lg:max-h-full"
		>
			<div class="p-3 sm:p-4 border-b border-gray-200 bg-gray-50">
				<h2 class="text-lg sm:text-xl font-bold text-gray-800">Order Items</h2>
			</div>

			<!-- Cart Items -->
			<div class="flex-1 overflow-y-auto p-3 sm:p-4 space-y-3">
				<div
					v-if="cart.length === 0"
					class="text-center text-gray-400 py-10"
				>
					Order is empty.
				</div>
				<div
					v-else
					v-for="(cartItem, index) in cart"
					:key="cartItem.item.id"
					class="flex justify-between items-center bg-gray-50 p-2 sm:p-3 rounded-lg"
				>
					<div class="flex-1 min-w-0 pr-2">
						<h4 class="font-medium text-gray-900 line-clamp-1 text-xs sm:text-sm">
							{{ cartItem.item.name }}
						</h4>
						<p class="text-xs sm:text-sm text-gray-500">
							{{
								new Intl.NumberFormat('en-PH', {
									style: 'currency',
									currency: 'PHP'
								}).format(cartItem.item.price)
							}}
							x {{ cartItem.quantity }}
						</p>
					</div>
					<div class="flex items-center space-x-2">
						<button
							@click="updateQuantity(index, -1)"
							class="w-7 h-7 sm:w-8 sm:h-8 rounded-full bg-gray-200 text-gray-600 hover:bg-gray-300 flex items-center justify-center text-sm touch-manipulation"
						>
							-
						</button>
						<span class="font-bold w-4 text-center text-xs sm:text-sm">{{
							cartItem.quantity
						}}</span>
						<button
							@click="updateQuantity(index, 1)"
							:disabled="cartItem.quantity >= getMaxStock(cartItem.item)"
							:class="[
								'w-7 h-7 sm:w-8 sm:h-8 rounded-full flex items-center justify-center transition-colors text-sm touch-manipulation',
								cartItem.quantity >= getMaxStock(cartItem.item)
									? 'bg-gray-100 text-gray-400 cursor-not-allowed'
									: 'bg-indigo-100 text-indigo-600 hover:bg-indigo-200'
							]"
						>
							+
						</button>
					</div>
				</div>
			</div>

			<!-- Footer: Totals & Checkout -->
			<div class="p-3 sm:p-4 border-t border-gray-200 bg-gray-50 space-y-3 sm:space-y-4">
				<div class="flex justify-between text-base sm:text-lg font-bold">
					<span>Total</span>
					<span>{{
						new Intl.NumberFormat('en-PH', {
							style: 'currency',
							currency: 'PHP'
						}).format(cartTotal)
					}}</span>
				</div>

				<div class="space-y-3">
					<div>
						<label class="block text-xs sm:text-sm font-medium text-gray-700"
							>Order Type</label
						>
						<select
							v-model="form.type"
							class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
						>
							<option value="dine_in">Dine In</option>
							<option value="takeout">Takeout</option>
							<option value="online">Online</option>
						</select>
					</div>
					<div>
						<label class="block text-xs sm:text-sm font-medium text-gray-700"
							>Customer Name</label
						>
						<input
							type="text"
							v-model="form.customer_name"
							class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
							placeholder="Guest"
						/>
					</div>
					<div>
						<label class="block text-xs sm:text-sm font-medium text-gray-700"
							>Status</label
						>
						<select
							v-model="form.status"
							class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
						>
							<option value="pending">Pending</option>
							<option value="completed">Completed</option>
							<option value="cancelled">Cancelled</option>
						</select>
					</div>

					<!-- Payment Section -->
					<div class="border-t border-gray-300 pt-3 space-y-3">
						<h3 class="text-xs sm:text-sm font-semibold text-gray-700">Payment</h3>

						<!-- Payment Method -->
						<div>
							<label class="block text-xs sm:text-sm font-medium text-gray-700 mb-2"
								>Payment Method</label
							>
							<div class="flex space-x-2">
								<button
									type="button"
									@click="form.payment_method = 'cash'"
									:class="[
										'flex-1 py-2 px-3 rounded-md text-xs sm:text-sm font-medium transition-colors touch-manipulation',
										form.payment_method === 'cash'
											? 'bg-indigo-600 text-white'
											: 'bg-gray-200 text-gray-700 hover:bg-gray-300'
									]"
								>
									💵 Cash
								</button>
								<button
									type="button"
									@click="form.payment_method = 'gcash'"
									:class="[
										'flex-1 py-2 px-3 rounded-md text-xs sm:text-sm font-medium transition-colors touch-manipulation',
										form.payment_method === 'gcash'
											? 'bg-indigo-600 text-white'
											: 'bg-gray-200 text-gray-700 hover:bg-gray-300'
									]"
								>
									📱 GCash
								</button>
							</div>
						</div>

						<!-- Cash Payment -->
						<div v-if="form.payment_method === 'cash'">
							<label class="block text-xs sm:text-sm font-medium text-gray-700"
								>Amount Tendered</label
							>
							<input
								type="number"
								v-model.number="form.amount_paid"
								step="0.01"
								:min="cartTotal"
								class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
								placeholder="0.00"
							/>
							<div class="mt-2 flex flex-wrap gap-2">
								<button
									type="button"
									@click="setExactAmount()"
									class="px-3 py-1 text-xs bg-green-100 text-green-700 rounded hover:bg-green-200 touch-manipulation"
								>
									Exact
								</button>

								<button
									v-for="amt in quickCashOptions"
									:key="amt"
									type="button"
									@click="form.amount_paid = amt"
									class="px-3 py-1 text-xs bg-gray-100 text-gray-700 rounded hover:bg-gray-200 touch-manipulation"
								>
									{{ new Intl.NumberFormat('en-PH', { style: 'currency', currency: 'PHP' }).format(amt) }}
								</button>

								<button
									type="button"
									@click="addCash(100)"
									class="px-3 py-1 text-xs bg-gray-100 text-gray-700 rounded hover:bg-gray-200 touch-manipulation"
								>
									+₱100
								</button>

								<button
									type="button"
									@click="addCash(500)"
									class="px-3 py-1 text-xs bg-gray-100 text-gray-700 rounded hover:bg-gray-200 touch-manipulation"
								>
									+₱500
								</button>
							</div>

							<div
								v-if="form.amount_paid > 0"
								class="mt-2 p-2 bg-green-50 rounded-md"
							>
								<div class="flex justify-between text-xs sm:text-sm">
									<span class="font-medium text-gray-700">Change:</span>
									<span class="font-bold text-green-700">{{
										new Intl.NumberFormat('en-PH', {
											style: 'currency',
											currency: 'PHP'
										}).format(changeDue)
									}}</span>
								</div>
							</div>
							<p
								v-if="form.errors.amount_paid"
								class="mt-1 text-xs text-red-600"
							>
								{{ form.errors.amount_paid }}
							</p>
						</div>

						<!-- GCash Payment -->
						<div v-if="form.payment_method === 'gcash'">
							<label class="block text-xs sm:text-sm font-medium text-gray-700"
								>Transaction Reference</label
							>
							<input
								type="text"
								v-model="form.payment_reference"
								class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
								placeholder="Enter GCash reference number"
							/>
							<div class="mt-2 p-2 bg-blue-50 rounded-md">
								<div class="flex justify-between text-xs sm:text-sm">
									<span class="font-medium text-gray-700">Amount to Pay:</span>
									<span class="font-bold text-blue-700">{{
										new Intl.NumberFormat('en-PH', {
											style: 'currency',
											currency: 'PHP'
										}).format(cartTotal)
									}}</span>
								</div>
							</div>
							<p
								v-if="form.errors.payment_reference"
								class="mt-1 text-xs text-red-600"
							>
								{{ form.errors.payment_reference }}
							</p>
						</div>
					</div>
				</div>

				<button
					@click="submitOrder"
					:disabled="cart.length === 0 || form.processing"
					class="w-full py-2.5 sm:py-3 px-4 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-lg shadow transition-colors disabled:opacity-50 touch-manipulation text-sm sm:text-base"
				>
					{{ form.processing ? 'Updating...' : 'Update Order' }}
				</button>
			</div>
		</div>
	</div>
</template>

<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import type { InertiaForm } from '@inertiajs/vue3';

interface CartItem {
	id: number;
	name: string;
	price: number;
	quantity: number;
}

interface CartEntry {
	item: CartItem;
	quantity: number;
}

interface Category {
	id: number;
	name: string;
	items: CartItem[];
}

interface Order {
	id: number;
	customer_name: string | null;
	type: string;
	status: string;
	total_amount: number;
}

interface PaymentFormData {
	customer_name: string;
	type: string;
	status: string;
	payment_method: 'cash' | 'gcash';
	amount_paid: number;
	payment_reference: string;
	items: { id: number; quantity: number }[];
}

interface Props {
	order: Order;
	categories: Category[];
	activeCategoryId: number | null;
	searchQuery: string;
	activeCategoryItems: CartItem[];
	cart: CartEntry[];
	cartTotal: number;
	cartItemCount: number;
	form: InertiaForm<PaymentFormData>;
	// Cart API
	addToCart: (item: CartItem, e?: Event) => void;
	removeFromCart: (index: number) => void;
	updateQuantity: (index: number, delta: number, e?: Event) => void;
	getRemainingStock: (item: CartItem) => number;
	getMaxStock: (item: CartItem) => number;
	// Payment API
	changeDue: number;
	setExactAmount: () => void;
	addCash: (delta: number) => void;
	quickCashOptions: number[];
}

const props = defineProps<Props>();

const emit = defineEmits<{
	'update:activeCategoryId': [value: number];
	'update:searchQuery': [value: string];
	submit: [];
}>();
</script>

<template>
	<div class="h-[calc(100vh-64px)] flex flex-row bg-gray-100 overflow-hidden">
		<!-- Left Panel: Menu -->
		<div class="flex-1 flex flex-col overflow-hidden">
			<!-- Header/Filters -->
			<div class="bg-white p-4 shadow-sm z-10 flex flex-col space-y-3">
				<div class="flex items-center justify-between">
					<Link
						href="/orders"
						class="text-gray-600 hover:text-gray-900 flex items-center text-sm font-medium"
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
					<h1 class="text-lg font-bold text-gray-800">
						Edit Order #{{ order.id }}
					</h1>
				</div>
				<input
					type="text"
					:value="searchQuery"
					@input="emit('update:searchQuery', ($event.target as HTMLInputElement).value)"
					placeholder="Search items..."
					class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 p-2 text-base"
				/>

				<div class="flex space-x-2 overflow-x-auto pb-2 scrollbar-hide">
					<button
						v-for="cat in categories"
						:key="cat.id"
						type="button"
						@click="emit('update:activeCategoryId', cat.id)"
						:class="[
							'px-4 py-2 rounded-full text-sm font-medium whitespace-nowrap transition-colors',
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
			<div class="flex-1 overflow-y-auto p-4">
				<div
					class="grid grid-cols-3 xl:grid-cols-4 gap-4"
				>
					<button
						v-for="item in activeCategoryItems"
						:key="item.id"
						type="button"
						@click="addToCart(item)"
						:disabled="getRemainingStock(item) <= 0"
						:class="[
							'rounded-lg shadow p-4 flex flex-col items-center justify-between transition-shadow h-32',
							getRemainingStock(item) <= 0
								? 'bg-gray-100 opacity-75 cursor-not-allowed grayscale'
								: 'bg-white hover:shadow-md active:shadow-sm'
						]"
					>
						<span
							class="font-semibold text-gray-900 text-center line-clamp-2 text-sm"
							>{{ item.name }}</span
						>
						<div class="mt-2 text-center">
							<span class="block text-indigo-600 font-bold text-base">{{
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
			class="w-96 bg-white shadow-xl flex flex-col border-l border-gray-200"
		>
			<div class="p-4 border-b border-gray-200 bg-gray-50">
				<h2 class="text-xl font-bold text-gray-800">Order Items</h2>
			</div>

			<!-- Cart Items -->
			<div class="flex-1 overflow-y-auto p-4 space-y-3">
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
					class="flex justify-between items-center bg-gray-50 p-3 rounded-lg"
				>
					<div class="flex-1 min-w-0 pr-2">
						<h4 class="font-medium text-gray-900 line-clamp-1 text-sm">
							{{ cartItem.item.name }}
						</h4>
						<p class="text-sm text-gray-500">
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
							type="button"
							@click="updateQuantity(index, -1)"
							class="w-8 h-8 rounded-full bg-gray-200 text-gray-600 hover:bg-gray-300 flex items-center justify-center text-sm"
						>
							-
						</button>
						<span class="font-bold w-4 text-center text-sm">{{
							cartItem.quantity
						}}</span>
						<button
							type="button"
							@click="updateQuantity(index, 1)"
							:disabled="cartItem.quantity >= getMaxStock(cartItem.item)"
							:class="[
								'w-8 h-8 rounded-full flex items-center justify-center transition-colors text-sm',
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
			<div class="p-4 border-t border-gray-200 bg-gray-50 space-y-4">
				<div class="flex justify-between text-lg font-bold">
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
						<label class="block text-sm font-medium text-gray-700"
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
						<label class="block text-sm font-medium text-gray-700"
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
						<label class="block text-sm font-medium text-gray-700"
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
						<h3 class="text-sm font-semibold text-gray-700">Payment</h3>

						<!-- Payment Method -->
						<div>
							<label class="block text-sm font-medium text-gray-700 mb-2"
								>Payment Method</label
							>
							<div class="flex space-x-2">
								<button
									type="button"
									@click="form.payment_method = 'cash'"
									:class="[
										'flex-1 py-2 px-3 rounded-md text-sm font-medium transition-colors',
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
										'flex-1 py-2 px-3 rounded-md text-sm font-medium transition-colors',
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
							<label class="block text-sm font-medium text-gray-700"
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
									class="px-3 py-1 text-xs bg-green-100 text-green-700 rounded hover:bg-green-200"
								>
									Exact
								</button>

								<button
									v-for="amt in quickCashOptions"
									:key="amt"
									type="button"
									@click="form.amount_paid = amt"
									class="px-3 py-1 text-xs bg-gray-100 text-gray-700 rounded hover:bg-gray-200"
								>
									{{ new Intl.NumberFormat('en-PH', { style: 'currency', currency: 'PHP' }).format(amt) }}
								</button>

								<button
									type="button"
									@click="addCash(100)"
									class="px-3 py-1 text-xs bg-gray-100 text-gray-700 rounded hover:bg-gray-200"
								>
									+₱100
								</button>

								<button
									type="button"
									@click="addCash(500)"
									class="px-3 py-1 text-xs bg-gray-100 text-gray-700 rounded hover:bg-gray-200"
								>
									+₱500
								</button>
							</div>
							<div
								v-if="form.amount_paid > 0"
								class="mt-2 p-2 bg-green-50 rounded-md"
							>
								<div class="flex justify-between text-sm">
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
							<label class="block text-sm font-medium text-gray-700"
								>Transaction Reference</label
							>
							<input
								type="text"
								v-model="form.payment_reference"
								class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
								placeholder="Enter GCash reference number"
							/>
							<div class="mt-2 p-2 bg-blue-50 rounded-md">
								<div class="flex justify-between text-sm">
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
					type="button"
					@click="emit('submit')"
					:disabled="cart.length === 0 || form.processing"
					class="w-full py-3 px-4 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-lg shadow transition-colors disabled:opacity-50 text-base"
				>
					{{ form.processing ? 'Updating...' : 'Update Order' }}
				</button>
			</div>
		</div>
	</div>
</template>

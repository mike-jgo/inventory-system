<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ref } from 'vue';
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

// Component-local state for tabs - start on cart for edit mode
const activeTab = ref<'items' | 'cart'>('cart');
const checkoutExpanded = ref(false);
</script>

<template>
	<div class="h-[calc(100vh-64px)] flex flex-col bg-gray-100 overflow-hidden">
		<!-- Header -->
		<div class="bg-white p-3 shadow-sm z-10">
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
				<h1 class="text-base font-bold text-gray-800">
					Edit Order #{{ order.id }}
				</h1>
			</div>
		</div>

		<!-- Tab Buttons -->
		<div class="bg-white border-b border-gray-200 flex">
			<button
				type="button"
				@pointerup.stop.prevent="activeTab = 'items'"
				:class="[
					'flex-1 py-3 text-sm font-medium transition-colors touch-manipulation',
					activeTab === 'items'
						? 'text-indigo-600 border-b-2 border-indigo-600'
						: 'text-gray-500 hover:text-gray-700'
				]"
			>
				Items
			</button>
			<button
				type="button"
				@pointerup.stop.prevent="activeTab = 'cart'"
				:class="[
					'flex-1 py-3 text-sm font-medium transition-colors touch-manipulation relative',
					activeTab === 'cart'
						? 'text-indigo-600 border-b-2 border-indigo-600'
						: 'text-gray-500 hover:text-gray-700'
				]"
			>
				Cart
				<span
					v-if="cartItemCount > 0"
					class="absolute top-1 right-1/4 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center"
				>
					{{ cartItemCount }}
				</span>
			</button>
		</div>

		<!-- Tab Content -->
		<div class="flex-1 overflow-y-auto pb-20">
			<!-- Items Tab -->
			<div v-if="activeTab === 'items'" class="p-3 space-y-3">
				<!-- Search (only on Items tab) -->
				<input
					type="text"
					:value="searchQuery"
					@input="emit('update:searchQuery', ($event.target as HTMLInputElement).value)"
					placeholder="Search items..."
					class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 p-2 text-sm"
				/>

				<!-- Categories (only on Items tab) -->
				<div class="flex space-x-2 overflow-x-auto pb-2 scrollbar-hide">
					<button
						v-for="cat in categories"
						:key="cat.id"
						type="button"
						@pointerup.stop.prevent="emit('update:activeCategoryId', cat.id)"
						:class="[
							'px-3 py-2 rounded-full text-sm font-medium whitespace-nowrap transition-colors touch-manipulation',
							activeCategoryId === cat.id
								? 'bg-indigo-600 text-white'
								: 'bg-gray-200 text-gray-700 hover:bg-gray-300'
						]"
					>
						{{ cat.name }}
					</button>
				</div>
				<!-- Items List (not grid) -->
				<div class="space-y-2">
					<button
						v-for="item in activeCategoryItems"
						:key="item.id"
						type="button"
						@pointerup.stop.prevent="(e) => addToCart(item, e)"
						:disabled="getRemainingStock(item) <= 0"
						:class="[
							'w-full rounded-lg shadow p-3 flex items-center justify-between transition-shadow touch-manipulation',
							getRemainingStock(item) <= 0
								? 'bg-gray-100 opacity-75 cursor-not-allowed'
								: 'bg-white hover:shadow-md active:shadow-sm'
						]"
					>
						<div class="flex-1 text-left min-w-0 pr-3">
							<h4 class="font-semibold text-gray-900 text-sm line-clamp-1">{{ item.name }}</h4>
							<p class="text-xs text-gray-500 mt-0.5">
								{{
									new Intl.NumberFormat('en-PH', {
										style: 'currency',
										currency: 'PHP'
									}).format(item.price)
								}}
								•
								<span
									:class="getRemainingStock(item) <= 0 ? 'text-red-500' : 'text-gray-500'"
								>
									{{
										getRemainingStock(item) <= 0
											? 'Out of Stock'
											: `${getRemainingStock(item)} left`
									}}
								</span>
							</p>
						</div>
						<svg
							v-if="getRemainingStock(item) > 0"
							xmlns="http://www.w3.org/2000/svg"
							class="h-5 w-5 text-indigo-600 flex-shrink-0"
							viewBox="0 0 20 20"
							fill="currentColor"
						>
							<path
								fill-rule="evenodd"
								d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z"
								clip-rule="evenodd"
							/>
						</svg>
					</button>
				</div>
				<div
					v-if="activeCategoryItems.length === 0"
					class="text-center text-gray-500 py-10"
				>
					No items found.
				</div>
			</div>

			<!-- Cart Tab -->
			<div v-if="activeTab === 'cart'" class="flex flex-col h-full">
				<!-- Cart Items -->
				<div class="flex-1 overflow-y-auto p-3 space-y-3">
					<div
						v-if="cart.length === 0"
						class="text-center text-gray-400 py-10"
					>
						Cart is empty.
					</div>
					<div
						v-else
						v-for="(cartItem, index) in cart"
						:key="cartItem.item.id"
						class="flex justify-between items-center bg-white p-4 rounded-lg shadow-sm"
					>
						<div class="flex-1 min-w-0 pr-3">
							<h4 class="font-semibold text-gray-900 text-base line-clamp-1">
								{{ cartItem.item.name }}
							</h4>
							<p class="text-sm text-gray-600 mt-1">
								{{
									new Intl.NumberFormat('en-PH', {
										style: 'currency',
										currency: 'PHP'
									}).format(cartItem.item.price)
								}}
								× {{ cartItem.quantity }}
							</p>
						</div>
						<div class="flex items-center space-x-3">
							<button
								type="button"
								@pointerup.stop.prevent="(e) => updateQuantity(index, -1, e)"
								class="w-9 h-9 rounded-full bg-gray-200 text-gray-700 hover:bg-gray-300 flex items-center justify-center text-base font-medium touch-manipulation"
							>
								−
							</button>
							<span class="font-bold w-6 text-center text-base">{{
								cartItem.quantity
							}}</span>
							<button
								type="button"
								@pointerup.stop.prevent="(e) => updateQuantity(index, 1, e)"
								:disabled="cartItem.quantity >= getMaxStock(cartItem.item)"
								:class="[
									'w-9 h-9 rounded-full flex items-center justify-center transition-colors text-base font-medium touch-manipulation',
									cartItem.quantity >= getMaxStock(cartItem.item)
										? 'bg-gray-100 text-gray-400 cursor-not-allowed'
										: 'bg-indigo-100 text-indigo-600 hover:bg-indigo-200'
								]"
							>
								+
							</button>
						</div>
					</div>

					<!-- Collapsible Checkout Section -->
					<div v-if="cart.length > 0" class="bg-white rounded-lg shadow-sm overflow-hidden">
						<!-- Accordion Header -->
						<button
							type="button"
							@pointerup.stop.prevent="checkoutExpanded = !checkoutExpanded"
							class="w-full flex items-center justify-between p-3 bg-gray-50 hover:bg-gray-100 transition-colors touch-manipulation"
						>
							<span class="text-sm font-semibold text-gray-700">Order Details & Payment</span>
							<svg
								xmlns="http://www.w3.org/2000/svg"
								:class="['h-5 w-5 text-gray-500 transition-transform', checkoutExpanded ? 'rotate-180' : '']"
								fill="none"
								viewBox="0 0 24 24"
								stroke="currentColor"
							>
								<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
							</svg>
						</button>

						<!-- Accordion Content -->
						<div v-show="checkoutExpanded" class="p-3 space-y-3 border-t border-gray-200">
							<!-- Order Type -->
							<div>
								<label class="block text-xs font-medium text-gray-700">Order Type</label>
								<select
									v-model="form.type"
									class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
								>
									<option value="dine_in">Dine In</option>
									<option value="takeout">Takeout</option>
									<option value="online">Online</option>
								</select>
							</div>

							<!-- Customer Name -->
							<div>
								<label class="block text-xs font-medium text-gray-700">Customer Name (Optional)</label>
								<input
									type="text"
									v-model="form.customer_name"
									class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
									placeholder="Guest"
								/>
							</div>

							<!-- Status -->
							<div>
								<label class="block text-xs font-medium text-gray-700">Status</label>
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
							<div class="pt-3 space-y-3 border-t border-gray-200">
								<h3 class="text-sm font-semibold text-gray-900">Payment</h3>

								<!-- Payment Method (Segmented Control) -->
								<div>
									<label class="block text-xs font-medium text-gray-700 mb-2">Method</label>
									<div class="flex rounded-lg overflow-hidden border border-gray-300">
										<button
											type="button"
											@pointerup.stop.prevent="form.payment_method = 'cash'"
											:class="[
												'flex-1 py-2.5 px-3 text-sm font-semibold transition-all touch-manipulation',
												form.payment_method === 'cash'
													? 'bg-indigo-600 text-white'
													: 'bg-white text-gray-700 hover:bg-gray-50'
											]"
										>
											💵 Cash
										</button>
										<button
											type="button"
											@pointerup.stop.prevent="form.payment_method = 'gcash'"
											:class="[
												'flex-1 py-2.5 px-3 text-sm font-semibold transition-all touch-manipulation border-l border-gray-300',
												form.payment_method === 'gcash'
													? 'bg-indigo-600 text-white'
													: 'bg-white text-gray-700 hover:bg-gray-50'
											]"
										>
											📱 GCash
										</button>
									</div>
								</div>

								<!-- Cash Payment -->
								<div v-if="form.payment_method === 'cash'">
									<label class="block text-xs font-medium text-gray-700">Amount Tendered</label>
									<input
										type="number"
										v-model.number="form.amount_paid"
										step="0.01"
										:min="cartTotal"
										class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
										placeholder="0.00"
									/>
									<!-- Horizontal Scrolling Quick Cash -->
									<div class="mt-2 flex space-x-2 overflow-x-auto pb-2 scrollbar-hide">
										<button
											type="button"
											@pointerup.stop.prevent="setExactAmount()"
											class="px-3 py-1.5 text-xs bg-green-100 text-green-700 rounded-md hover:bg-green-200 touch-manipulation whitespace-nowrap flex-shrink-0"
										>
											Exact
										</button>

										<button
											v-for="amt in quickCashOptions"
											:key="amt"
											type="button"
											@pointerup.stop.prevent="form.amount_paid = amt"
											class="px-3 py-1.5 text-xs bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200 touch-manipulation whitespace-nowrap flex-shrink-0"
										>
											{{ new Intl.NumberFormat('en-PH', { style: 'currency', currency: 'PHP' }).format(amt) }}
										</button>

										<button
											type="button"
											@pointerup.stop.prevent="addCash(100)"
											class="px-3 py-1.5 text-xs bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200 touch-manipulation whitespace-nowrap flex-shrink-0"
										>
											+₱100
										</button>

										<button
											type="button"
											@pointerup.stop.prevent="addCash(500)"
											class="px-3 py-1.5 text-xs bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200 touch-manipulation whitespace-nowrap flex-shrink-0"
										>
											+₱500
										</button>
									</div>

									<!-- Change Display -->
									<div v-if="form.amount_paid > 0" class="mt-2 p-2 bg-green-50 rounded-md">
										<div class="flex justify-between text-xs">
											<span class="font-medium text-gray-700">Change:</span>
											<span class="font-bold text-green-700">{{
												new Intl.NumberFormat('en-PH', {
													style: 'currency',
													currency: 'PHP'
												}).format(changeDue)
											}}</span>
										</div>
									</div>
									<p v-if="form.errors.amount_paid" class="mt-1 text-xs text-red-600">
										{{ form.errors.amount_paid }}
									</p>
								</div>

								<!-- GCash Payment -->
								<div v-if="form.payment_method === 'gcash'">
									<label class="block text-xs font-medium text-gray-700">Transaction Reference</label>
									<input
										type="text"
										v-model="form.payment_reference"
										class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
										placeholder="Enter GCash reference number"
									/>
									<div class="mt-2 p-2 bg-blue-50 rounded-md">
										<div class="flex justify-between text-xs">
											<span class="font-medium text-gray-700">Amount to Pay:</span>
											<span class="font-bold text-blue-700">{{
												new Intl.NumberFormat('en-PH', {
													style: 'currency',
													currency: 'PHP'
												}).format(cartTotal)
											}}</span>
										</div>
									</div>
									<p v-if="form.errors.payment_reference" class="mt-1 text-xs text-red-600">
										{{ form.errors.payment_reference }}
									</p>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<!-- Sticky Bottom Bar (Context-Aware) -->
		<div class="fixed bottom-0 inset-x-0 lg:hidden bg-white border-t border-gray-200 p-3 shadow-lg z-20">
			<!-- Items Tab: Show "View Cart" -->
			<div v-if="activeTab === 'items'" class="flex items-center justify-between">
				<div>
					<div class="text-xs text-gray-500">Total</div>
					<div class="text-lg font-bold text-gray-900">
						{{
							new Intl.NumberFormat('en-PH', {
								style: 'currency',
								currency: 'PHP'
							}).format(cartTotal)
						}}
					</div>
				</div>
				<button
					type="button"
					@pointerup.stop.prevent="activeTab = 'cart'"
					:disabled="cart.length === 0"
					class="bg-indigo-600 hover:bg-indigo-700 disabled:bg-gray-300 text-white px-5 py-3 rounded-lg font-semibold text-sm touch-manipulation flex items-center space-x-2 transition-colors"
				>
					<span>View Cart</span>
					<span
						v-if="cartItemCount > 0"
						class="bg-white text-indigo-600 text-xs rounded-full h-5 w-5 flex items-center justify-center font-bold"
					>
						{{ cartItemCount }}
					</span>
				</button>
			</div>

			<!-- Cart Tab: Show "Update Order" -->
			<div v-else-if="activeTab === 'cart'" class="flex flex-col space-y-2">
				<!-- Summary Row -->
				<div class="flex items-center justify-between text-sm">
					<span class="text-gray-600">{{ cartItemCount }} {{ cartItemCount === 1 ? 'item' : 'items' }}</span>
					<div class="text-right">
						<div class="font-bold text-gray-900 text-lg">
							{{
								new Intl.NumberFormat('en-PH', {
									style: 'currency',
									currency: 'PHP'
								}).format(cartTotal)
							}}
						</div>
					</div>
				</div>
				
				<!-- CTA Button -->
				<button
					type="button"
					@pointerup.stop.prevent="emit('submit')"
					:disabled="cart.length === 0 || form.processing"
					class="w-full py-3 px-4 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-lg shadow-md transition-colors disabled:opacity-50 disabled:cursor-not-allowed touch-manipulation text-base"
				>
					{{ form.processing ? 'Processing...' : 'Update Order' }}
				</button>
			</div>
		</div>
	</div>
</template>
<script setup lang="ts">
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import Layout from '@/layouts/Layout.vue';

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
            quantity: number; // Stock available
        }[];
    }[];
}>();

// State
const activeCategoryId = ref(props.categories[0]?.id || null);
const cart = ref<{ item: any; quantity: number }[]>([]);
const searchQuery = ref('');

// Computed
const activeCategoryItems = computed(() => {
    if (!activeCategoryId.value) return [];
    
    let items = props.categories.find(c => c.id === activeCategoryId.value)?.items || [];
    
    if (searchQuery.value) {
        const q = searchQuery.value.toLowerCase();
        items = items.filter(i => i.name.toLowerCase().includes(q));
    }
    
    return items;
});

const cartTotal = computed(() => {
    return cart.value.reduce((acc, cartItem) => {
        return acc + (cartItem.item.price * cartItem.quantity);
    }, 0);
});

// Helper
const getRemainingStock = (item: any) => {
    const cartItem = cart.value.find(c => c.item.id === item.id);
    const inCart = cartItem ? cartItem.quantity : 0;
    return item.quantity - inCart;
};

// Actions
const addToCart = (item: any) => {
    if (getRemainingStock(item) <= 0) return; // Check dynamic stock
    const existing = cart.value.find(c => c.item.id === item.id);
    if (existing) {
        // Check stock limit
        if (existing.quantity < item.quantity) {
             existing.quantity++;
        } else {
            alert('Max stock reached for this item.');
        }
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
    } else if (newQty <= cartItem.item.quantity) {
        cartItem.quantity = newQty;
    } else {
         alert('Max stock reached.');
    }
};

// Checkout
const form = useForm({
    customer_name: '',
    type: 'dine_in',
    status: 'pending',
    items: [] as { id: number; quantity: number }[]
});

const submitOrder = () => {
    if (cart.value.length === 0) return;
    
    form.items = cart.value.map(c => ({
        id: c.item.id,
        quantity: c.quantity
    }));
    
    form.post('/orders', {
        onSuccess: () => {
             // Reset state handled by Inertia reload/redirect usually, 
             // but if we stayed here we would reset.
             // Controller redirects to Index, so we are good.
        }
    });
};
</script>

<template>
    <Head title="New Order (POS)" />

    <div class="h-[calc(100vh-64px)] flex flex-col md:flex-row bg-gray-100 overflow-hidden">
        <!-- Left Panel: Menu -->
        <div class="flex-1 flex flex-col h-full overflow-hidden">
            <!-- Header/Filters -->
            <div class="bg-white p-4 shadow-sm z-10 flex flex-col space-y-4">
                <div class="flex items-center">
                    <Link href="/orders" class="text-gray-600 hover:text-gray-900 flex items-center text-sm font-medium">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        Back to Orders
                    </Link>
                </div>
                <input 
                    type="text" 
                    v-model="searchQuery" 
                    placeholder="Search items..." 
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 p-2"
                />
                
                <div class="flex space-x-2 overflow-x-auto pb-2 scrollbar-hide">
                    <button 
                        v-for="cat in categories" 
                        :key="cat.id"
                        @click="activeCategoryId = cat.id"
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
                <div class="grid grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                    <button 
                        v-for="item in activeCategoryItems" 
                        :key="item.id"
                        @click="addToCart(item)"
                        :disabled="getRemainingStock(item) <= 0"
                        :class="[
                            'rounded-lg shadow p-4 flex flex-col items-center justify-between transition-shadow h-32',
                            getRemainingStock(item) <= 0 
                                ? 'bg-gray-100 opacity-75 cursor-not-allowed grayscale' 
                                : 'bg-white hover:shadow-md'
                        ]"
                    >
                        <span class="font-semibold text-gray-900 text-center line-clamp-2">{{ item.name }}</span>
                        <div class="mt-2 text-center">
                            <span class="block text-indigo-600 font-bold">{{ new Intl.NumberFormat('en-PH', { style: 'currency', currency: 'PHP' }).format(item.price) }}</span>
                            <span 
                                :class="[
                                    'text-xs font-medium',
                                    getRemainingStock(item) <= 0 ? 'text-red-500' : 'text-gray-500'
                                ]"
                            >
                                {{ getRemainingStock(item) <= 0 ? 'Out of Stock' : `${getRemainingStock(item)} in stock` }}
                            </span>
                        </div>
                    </button>
                </div>
                <div v-if="activeCategoryItems.length === 0" class="text-center text-gray-500 py-10">
                    No items found.
                </div>
            </div>
        </div>

        <!-- Right Panel: Cart -->
        <div class="w-full md:w-96 bg-white shadow-xl flex flex-col h-full border-l border-gray-200">
            <div class="p-4 border-b border-gray-200 bg-gray-50">
                <h2 class="text-xl font-bold text-gray-800">Current Order</h2>
            </div>

            <!-- Cart Items -->
            <div class="flex-1 overflow-y-auto p-4 space-y-3">
                <div v-if="cart.length === 0" class="text-center text-gray-400 py-10">
                    Cart is empty.
                </div>
                <div v-else v-for="(cartItem, index) in cart" :key="cartItem.item.id" class="flex justify-between items-center bg-gray-50 p-3 rounded-lg">
                    <div class="flex-1">
                        <h4 class="font-medium text-gray-900 line-clamp-1">{{ cartItem.item.name }}</h4>
                        <p class="text-sm text-gray-500">{{ new Intl.NumberFormat('en-PH', { style: 'currency', currency: 'PHP' }).format(cartItem.item.price) }} x {{ cartItem.quantity }}</p>
                    </div>
                    <div class="flex items-center space-x-2">
                        <button @click="updateQuantity(index, -1)" class="w-8 h-8 rounded-full bg-gray-200 text-gray-600 hover:bg-gray-300 flex items-center justify-center">-</button>
                        <span class="font-bold w-4 text-center">{{ cartItem.quantity }}</span>
                        <button 
                            @click="updateQuantity(index, 1)" 
                            :disabled="cartItem.quantity >= cartItem.item.quantity"
                            :class="[
                                'w-8 h-8 rounded-full flex items-center justify-center transition-colors',
                                cartItem.quantity >= cartItem.item.quantity 
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
                    <span>{{ new Intl.NumberFormat('en-PH', { style: 'currency', currency: 'PHP' }).format(cartTotal) }}</span>
                </div>

                <div class="space-y-3">
                    <div>
                         <label class="block text-sm font-medium text-gray-700">Order Type</label>
                         <select v-model="form.type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                             <option value="dine_in">Dine In</option>
                             <option value="takeout">Takeout</option>
                             <option value="online">Online</option>
                         </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Customer Name (Optional)</label>
                        <input type="text" v-model="form.customer_name" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="Guest">
                    </div>
                    <div>
                         <label class="block text-sm font-medium text-gray-700">Status</label>
                         <select v-model="form.status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                             <option value="pending">Pending</option>
                             <option value="completed">Completed</option>
                         </select>
                    </div>
                </div>

                <button 
                    @click="submitOrder"
                    :disabled="cart.length === 0 || form.processing"
                    class="w-full py-3 px-4 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-lg shadow transition-colors disabled:opacity-50"
                >
                    {{ form.processing ? 'Processing...' : 'Complete Order' }}
                </button>
            </div>
        </div>
    </div>
</template>

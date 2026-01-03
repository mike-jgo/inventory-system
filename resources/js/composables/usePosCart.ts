import { ref, computed, type Ref } from 'vue';

export interface CartItem {
	id: number;
	name: string;
	price: number;
	quantity: number; // Stock available
}

export interface CartEntry {
	item: CartItem;
	quantity: number;
}

export interface OriginalOrderItem {
	item_id: number;
	quantity: number;
}

export interface UsePosCartOptions {
	mode: 'create' | 'edit';
	originalOrderItems?: OriginalOrderItem[];
}

export function usePosCart(options: UsePosCartOptions) {
  const cart: Ref<CartEntry[]> = ref([]);

  const cartTotal = computed(() => cart.value.reduce((acc, c) => acc + Number(c.item.price) * c.quantity, 0));
  const cartItemCount = computed(() => cart.value.reduce((acc, c) => acc + c.quantity, 0));

  // --- Touch duplicate event guard (fast + only affects touch) ---
  let lastTouchActionAt = 0;
  const TOUCH_DUP_MS = 60;

  const shouldIgnore = (e?: Event) => {
    // If no event was passed, don't block anything
    if (!e) return false;

    // PointerEvent is best (covers touch + mouse)
    const pe = e as PointerEvent;

    const isTouch =
      // pointer events
      (typeof pe.pointerType === 'string' && pe.pointerType === 'touch') ||
      // fallback: touch events
      e.type.startsWith('touch');

    if (!isTouch) return false;

    const now = (typeof e.timeStamp === 'number' && e.timeStamp > 0) ? e.timeStamp : Date.now();
    if (now - lastTouchActionAt < TOUCH_DUP_MS) return true;
    lastTouchActionAt = now;
    return false;
  };

  const getOriginalQuantity = (itemId: number) => {
    if (options.mode !== 'edit' || !options.originalOrderItems) return 0;
    return options.originalOrderItems.find((oi) => oi.item_id === itemId)?.quantity ?? 0;
  };

  const getCartQuantity = (itemId: number) => cart.value.find((c) => c.item.id === itemId)?.quantity ?? 0;

  const getMaxStock = (item: CartItem) => {
    if (options.mode === 'create') return item.quantity;
    return item.quantity + getOriginalQuantity(item.id);
  };

  const getRemainingStock = (item: CartItem) => getMaxStock(item) - getCartQuantity(item.id);

  // Accept event as 2nd param
  const addToCart = (item: CartItem, e?: Event) => {
    if (shouldIgnore(e)) return;
    if (getRemainingStock(item) <= 0) return;

    const existing = cart.value.find((c) => c.item.id === item.id);
    if (existing) {
      const maxStock = getMaxStock(item);
      if (existing.quantity < maxStock) existing.quantity++;
    } else {
      cart.value.push({ item, quantity: 1 });
    }
  };

  const removeFromCart = (index: number) => {
    cart.value.splice(index, 1);
  };

  // Accept event as 3rd param
  const updateQuantity = (index: number, delta: number, e?: Event) => {
    if (shouldIgnore(e)) return;

    const cartItem = cart.value[index];
    const newQty = cartItem.quantity + delta;

    if (newQty <= 0) {
      removeFromCart(index);
      return;
    }

    const maxStock = getMaxStock(cartItem.item);
    if (newQty <= maxStock) cartItem.quantity = newQty;
  };

  const initializeCart = (orderItems: any[]) => {
    cart.value = orderItems.map((oi) => ({
      item: { ...oi.item },
      quantity: oi.quantity,
    }));
  };

  return {
    cart,
    cartTotal,
    cartItemCount,
    addToCart,
    removeFromCart,
    updateQuantity,
    getRemainingStock,
    getMaxStock,
    initializeCart,
  };
}

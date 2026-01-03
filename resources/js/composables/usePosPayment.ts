import { computed, watch, type ComputedRef } from 'vue';
import type { InertiaForm } from '@inertiajs/vue3';

export interface PaymentFormData {
	payment_method: 'cash' | 'gcash';
	amount_paid: number;
	payment_reference: string;
	[key: string]: any;
}

export function usePosPayment(
	cartTotal: ComputedRef<number>,
	form: InertiaForm<PaymentFormData>
) {
	// Change due for cash payments
	const changeDue = computed(() => {
		if (form.payment_method === 'cash') {
			return Math.max(0, form.amount_paid - cartTotal.value);
		}
		return 0;
	});

	// Round up to the next multiple
	const roundUp = (value: number, step: number): number => {
		if (step <= 0) return value;
		return Math.ceil(value / step) * step;
	};

	// Set exact amount (cart total)
	const setExactAmount = (): void => {
		form.amount_paid = Number(cartTotal.value.toFixed(2));
	};

	// Add cash increment
	const addCash = (delta: number): void => {
		const current = Number(form.amount_paid || 0);
		form.amount_paid = current + delta;
	};

	// Smart quick cash options based on cart total
	const quickCashOptions = computed(() => {
		const total = cartTotal.value;

		// If total is 0, don't suggest anything
		if (total <= 0) return [];

		// Typical PH cash tender steps
		const steps = [100, 200, 500, 1000];

		// Round-up suggestions (only > total and unique)
		const roundedTargets = steps
			.map((s) => roundUp(total, s))
			.filter((v, i, arr) => v > total && arr.indexOf(v) === i)
			.slice(0, 3);

		// Also suggest "total + 100/200/500" to speed up tendering
		const plusTargets = [100, 200, 500]
			.map((d) => Number((total + d).toFixed(2)))
			.filter((v, i, arr) => arr.indexOf(v) === i)
			.slice(0, 2);

		return [...roundedTargets, ...plusTargets]
			.filter((v, i, arr) => arr.indexOf(v) === i)
			.sort((a, b) => a - b)
			.slice(0, 4);
	});

	// Watch for payment method changes
	watch(
		() => form.payment_method,
		(newMethod, oldMethod) => {
			if (newMethod === 'cash' && oldMethod === 'gcash') {
				// Switching to cash: clear payment reference
				form.payment_reference = '';
			} else if (newMethod === 'gcash' && oldMethod === 'cash') {
				// Switching to GCash: optionally set amount_paid to cart total for UI consistency
				form.amount_paid = Number(cartTotal.value.toFixed(2));
				// Clear any previous reference
				form.payment_reference = '';
			}
		}
	);

	return {
		changeDue,
		setExactAmount,
		addCash,
		quickCashOptions
	};
}

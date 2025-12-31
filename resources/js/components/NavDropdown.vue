<script setup lang="ts">
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';

interface DropdownItem {
	name: string;
	href: string;
}

interface Props {
	name: string;
	items: DropdownItem[];
	isOpen: boolean;
}

const props = defineProps<Props>();

const emit = defineEmits<{
	toggle: [];
	close: [];
}>();

const page = usePage();
const currentUrl = computed(() => page.url);

// Check if current URL matches any item in the dropdown
const isDropdownActive = computed(() => {
	return props.items.some(item => currentUrl.value === item.href);
});
</script>

<template>
	<div class="dropdown-container relative">
		<button
			@click="emit('toggle')"
			class="flex items-center space-x-1 px-3 py-2 rounded font-medium transition-colors"
			:class="[
				isDropdownActive
					? 'text-blue-600 font-semibold bg-blue-50'
					: 'text-gray-700 hover:text-blue-600 hover:bg-gray-50'
			]"
		>
			<span>{{ name }}</span>
			<svg
				class="h-4 w-4 transition-transform"
				:class="{ 'rotate-180': isOpen }"
				xmlns="http://www.w3.org/2000/svg"
				fill="none"
				viewBox="0 0 24 24"
				stroke="currentColor"
			>
				<path
					stroke-linecap="round"
					stroke-linejoin="round"
					stroke-width="2"
					d="M19 9l-7 7-7-7"
				/>
			</svg>
		</button>

		<!-- Dropdown Items -->
		<div
			v-if="isOpen"
			class="absolute left-0 mt-1 min-w-max bg-white rounded-lg shadow-lg z-50"
		>
			<Link
				v-for="item in items"
				:key="item.name"
				:href="item.href"
				@click="emit('close')"
				class="block px-4 py-2 text-sm font-medium transition-colors first:rounded-t-lg last:rounded-b-lg"
				:class="[
					currentUrl === item.href
						? 'text-blue-600 bg-blue-50'
						: 'text-gray-700 hover:bg-gray-50 hover:text-blue-600'
				]"
			>
				{{ item.name }}
			</Link>
		</div>
	</div>
</template>

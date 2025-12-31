<script setup lang="ts">
import { computed } from 'vue';

interface DropdownItem {
	name: string;
	action: () => void;
}

interface Props {
	name: string;
	items: DropdownItem[];
	isOpen: boolean;
	align?: 'left' | 'right';
}

const props = withDefaults(defineProps<Props>(), {
	align: 'left'
});

const emit = defineEmits<{
	toggle: [];
	close: [];
}>();

const alignmentClass = computed(() => {
	return props.align === 'right' ? 'right-0' : 'left-0';
});

const handleItemClick = (item: DropdownItem) => {
	item.action();
	emit('close');
};
</script>

<template>
	<div class="dropdown-container relative">
		<button
			@click="emit('toggle')"
			class="flex items-center space-x-1 px-3 py-2 rounded font-medium transition-colors hover:bg-gray-50 focus:outline-none"
		>
			<span class="text-gray-700 font-medium">{{ name }}</span>
			<svg
				class="h-4 w-4 text-gray-600 transition-transform"
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
			class="absolute mt-1 min-w-max bg-white rounded-lg shadow-lg z-50"
			:class="alignmentClass"
		>
			<button
				v-for="item in items"
				:key="item.name"
				@click="handleItemClick(item)"
				class="w-full text-left block px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 hover:text-blue-600 transition-colors first:rounded-t-lg last:rounded-b-lg"
			>
				{{ item.name }}
			</button>
		</div>
	</div>
</template>

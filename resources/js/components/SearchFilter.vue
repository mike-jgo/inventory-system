<script setup lang="ts">
import { ref, watch } from 'vue';

interface FilterOption {
	value: string;
	label: string;
}

interface FilterConfig {
	key: string;
	label: string;
	type: 'select' | 'date';
	options?: FilterOption[];
}

const props = defineProps<{
	searchPlaceholder?: string;
	filters: FilterConfig[];
	currentFilters?: Record<string, any>;
}>();

const emit = defineEmits<{
	'update:filters': [filters: Record<string, any>];
}>();

// Local state
const searchQuery = ref(props.currentFilters?.search || '');
const selectedFilters = ref<Record<string, any>>({});

// Initialize selected filters from current filters
const initializeFilters = () => {
	props.filters.forEach((filter) => {
		if (props.currentFilters?.[filter.key]) {
			selectedFilters.value[filter.key] = props.currentFilters[filter.key];
		} else {
			selectedFilters.value[filter.key] = '';
		}
	});
};

initializeFilters();

// Watch for changes in currentFilters (e.g., when navigating from dashboard)
watch(() => props.currentFilters, (newFilters) => {
	searchQuery.value = newFilters?.search || '';
	initializeFilters();
}, { deep: true });

// Debounced search
let searchTimeout: ReturnType<typeof setTimeout>;
watch(searchQuery, (newValue) => {
	clearTimeout(searchTimeout);
	searchTimeout = setTimeout(() => {
		applyFilters();
	}, 500);
});

const applyFilters = () => {
	const allFilters: Record<string, any> = {
		search: searchQuery.value || undefined,
		...selectedFilters.value,
	};

	// Remove undefined/empty values
	Object.keys(allFilters).forEach((key) => {
		if (!allFilters[key]) {
			delete allFilters[key];
		}
	});

	emit('update:filters', allFilters);
};

const clearAllFilters = () => {
	searchQuery.value = '';
	props.filters.forEach((filter) => {
		selectedFilters.value[filter.key] = '';
	});
	applyFilters();
};

const hasActiveFilters = () => {
	return searchQuery.value || Object.keys(selectedFilters.value).length > 0;
};
</script>

<template>
	<div class="bg-white p-3 sm:p-4 rounded-lg shadow-sm border border-gray-200 mb-6">
		<div class="flex flex-col sm:flex-row sm:flex-wrap gap-2 items-stretch sm:items-center">
			<!-- Search Input -->
			<div class="w-full sm:w-[300px]">
				<input
					v-model="searchQuery"
					type="text"
					:placeholder="searchPlaceholder || 'Search...'"
					class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none"
				/>
			</div>

			<!-- Dynamic Filters -->
			<template v-for="filter in filters" :key="filter.key">
				<!-- Select Filters -->
				<div v-if="filter.type === 'select'" class="w-full sm:w-[160px]">
					<select
						v-model="selectedFilters[filter.key]"
						@change="applyFilters"
						class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none"
					>
						<option value="">{{ filter.label }}</option>
						<option
							v-for="option in filter.options"
							:key="option.value"
							:value="option.value"
						>
							{{ option.label }}
						</option>
					</select>
				</div>

				<!-- Date Filters -->
				<div v-else-if="filter.type === 'date'" class="w-full sm:w-[160px]">
					<input
						v-model="selectedFilters[filter.key]"
						@change="applyFilters"
						type="date"
						:placeholder="filter.label"
						class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none"
					/>
				</div>
			</template>

			<!-- Clear Filters Button -->
			<button
				v-if="hasActiveFilters()"
				@click="clearAllFilters"
				class="w-full sm:w-auto px-3 py-2 text-sm bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200 transition-colors whitespace-nowrap touch-manipulation"
			>
				Clear All
			</button>
		</div>
	</div>
</template>

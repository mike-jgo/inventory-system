<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { route } from 'ziggy-js';
import Layout from '@/layouts/Layout.vue';
import DataTable from '@/components/DataTable.vue';
import ViewDetailsModal from '@/components/ViewDetailsModal.vue';

defineOptions({ layout: Layout });

const props = defineProps<{
	activities: {
		data: {
			id: number;
			description: string;
			subject_type: string | null;
			causer?: { id: number; name?: string } | null;
			created_at: string;
			properties?: Record<string, any>;
			remarks?: string | null;
		}[];
		current_page: number;
		last_page: number;
		per_page: number;
		total: number;
		links: { url: string | null; label: string; active: boolean }[];
	};
}>();

const columns = [
	{ key: 'id', label: 'ID' },
	{ key: 'causer.name', label: 'User' },
	{ key: 'description', label: 'Action' },
	{ key: 'subject_type', label: 'Entity' },
	{ key: 'created_at', label: 'Timestamp' }
];

// ✅ Clean up the model name before displaying
const formattedActivities = computed(() =>
	props.activities.data.map((a) => ({
		...a,
		subject_type: a.subject_type?.replace('App\\Models\\', '') ?? a.subject_type
	}))
);

const showDetails = ref(false);
const selectedActivity = ref<any | null>(null);

const openDetails = (row: any) => {
	selectedActivity.value = row;
	showDetails.value = true;
};

const hasUpdateDetails = computed(() => {
	const p = selectedActivity.value?.properties;
	return p && p.old && p.new;
});

const getChangedFields = computed(() => {
	if (!hasUpdateDetails.value) return [];
	const oldData = selectedActivity.value.properties.old;
	const newData = selectedActivity.value.properties.new;
	return Object.keys(newData).map((key) => ({
		field: key,
		oldValue: oldData[key],
		newValue: newData[key],
		changed: oldData[key] !== newData[key]
	}));
});

const handlePaginate = (url: string | null) => {
	if (url) router.visit(url, { preserveScroll: true });
};

const saveRemarks = (remarks: string) => {
	if (!selectedActivity.value?.id) return;

	router.post(
		route('activities.remarks', selectedActivity.value.id),
		{ remarks },
		{
			preserveScroll: true,
			onSuccess: () => {
				showDetails.value = false;
				// Optional: toast or alert here
				alert('Remarks saved successfully!');
			},
			onError: (errors) => {
				console.error('Failed to save remarks:', errors);
			}
		}
	);
};
</script>

<template>
	<Head title="Activity Log" />

	<div class="p-8">
		<div class="flex justify-between items-center">
			<h1 class="text-3xl font-semibold text-gray-900">Activity Log</h1>
		</div>

		<p class="mt-4 text-gray-700">A complete record of user activities and system events.</p>

		<DataTable
			:columns="columns"
			:rows="formattedActivities"
			:pagination="activities"
			@paginate="handlePaginate"
			@view="openDetails"
			class="mt-6"
		/>

		<!-- Details Modal -->
		<ViewDetailsModal
			v-if="showDetails && selectedActivity"
			:resource-name="`Log #${selectedActivity.id}`"
			:model="selectedActivity"
			:update-fields="hasUpdateDetails ? getChangedFields : null"
			@close="showDetails = false"
			@saveRemarks="saveRemarks"
		/>
	</div>
</template>

<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { route } from 'ziggy-js';
import Layout from '@/layouts/Layout.vue';
import DataTable from '@/components/DataTable.vue';
import ViewDetailsModal from '@/components/ViewDetailsModal.vue';
import SearchFilter from '@/components/SearchFilter.vue';

defineOptions({ layout: Layout });

const props = defineProps<{
	activities: {
		data: {
			id: number;
			description: string;
			subject_type: string | null;
			subject_id: number | null;
			subject?: { id: number; name?: string } | null;
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
	filters?: Record<string, any>;
	filterOptions?: {
		entities: string[];
		actions: string[];
	};
}>();

const columns = [
	{ key: 'id', label: 'ID' },
	{ key: 'causer.name', label: 'User' },
	{ key: 'display_description', label: 'Action' },
	{ key: 'subject_type', label: 'Entity' },
	{ key: 'created_at', label: 'Timestamp' }
];

const formattedActivities = computed(() =>
	props.activities.data.map((a) => {
		// Extract just the class name from any namespace
		const cleanedSubjectInfo = a.subject_type?.split('\\').pop() ?? a.subject_type;

		let display_description = a.description;

		// Special handling for remarks updates on activity logs
		if (a.description === 'Updated remarks on activity log') {
			const activityLogId = a.properties?.activity_log_id ?? a.subject_id;
			display_description = `Updated remarks on Activity Log #${activityLogId}`;
		}
		else if (['created', 'updated', 'deleted', 'restored'].includes(a.description)) {
			const capitalizedDescription =
				a.description.charAt(0).toUpperCase() + a.description.slice(1);

			let subjectIdentifier = `#${a.subject_id ?? ''}`;

			// Specific logic for Items to show Name
			if (cleanedSubjectInfo === 'Item') {
				if (a.subject && a.subject.name) {
					subjectIdentifier = `"${a.subject.name}"`;
				} else if (a.properties?.attributes?.name) {
					subjectIdentifier = `"${a.properties.attributes.name}"`;
				} else if (a.properties?.old?.name) {
					// For deleted items
					subjectIdentifier = `"${a.properties.old.name}"`;
				}
			}

			display_description =
				`${capitalizedDescription} ${cleanedSubjectInfo ?? ''} ${subjectIdentifier}`.trim();
		}

		return {
			...a,
			subject_type: cleanedSubjectInfo,
			display_description: display_description
		};
	})
);

const showDetails = ref(false);
const selectedActivity = ref<any | null>(null);

const openDetails = (row: any) => {
	selectedActivity.value = row;
	showDetails.value = true;
};

const hasUpdateDetails = computed(() => {
	const p = selectedActivity.value?.properties;
	return p && p.old && p.attributes;
});

const getChangedFields = computed(() => {
	if (!hasUpdateDetails.value) return [];
	const oldData = selectedActivity.value.properties.old;
	const newData = selectedActivity.value.properties.attributes;
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
				// Update the local activity data to reflect the new remarks
				if (selectedActivity.value) {
					selectedActivity.value.remarks = remarks;
				}

				// Find and update the activity in the activities list
				const activityIndex = props.activities.data.findIndex(
					(a) => a.id === selectedActivity.value?.id
				);
				if (activityIndex !== -1) {
					props.activities.data[activityIndex].remarks = remarks;
				}

				showDetails.value = false;
			},
			onError: (errors) => {
				console.error('Failed to save remarks:', errors);
			}
		}
	);
};

const formatDate = (dateString: string) => {
	const date = new Date(dateString);
	return (
		date.getFullYear() +
		'-' +
		String(date.getMonth() + 1).padStart(2, '0') +
		'-' +
		String(date.getDate()).padStart(2, '0') +
		' ' +
		String(date.getHours()).padStart(2, '0') +
		':' +
		String(date.getMinutes()).padStart(2, '0') +
		':' +
		String(date.getSeconds()).padStart(2, '0')
	);
};

// Filter configuration
const filterConfigs = computed(() => [
	{
		key: 'action',
		label: 'Action Type',
		type: 'select' as const,
		options:
			props.filterOptions?.actions.map((a) => ({
				value: a,
				label: a.charAt(0).toUpperCase() + a.slice(1)
			})) || []
	},
	{
		key: 'entity',
		label: 'Entity Type',
		type: 'select' as const,
		options:
			props.filterOptions?.entities.map((e) => ({
				value: e,
				label: e
			})) || []
	},
	{
		key: 'date_from',
		label: 'From Date',
		type: 'date' as const
	},
	{
		key: 'date_to',
		label: 'To Date',
		type: 'date' as const
	}
]);

const handleFilterUpdate = (filters: Record<string, any>) => {
	router.get(route('activity-log.index'), filters, {
		preserveScroll: true,
		preserveState: true
	});
};
</script>

<template>
	<Head title="Activity Log" />

	<div class="p-8">
		<div class="flex justify-between items-center">
			<h1 class="text-3xl font-semibold text-gray-900">Activity Log</h1>
		</div>

		<p class="mt-4 text-gray-700">A complete record of user activities and system events.</p>

		<!-- Search and Filters -->
		<SearchFilter
			:filters="filterConfigs"
			:current-filters="filters"
			search-placeholder="Search by description, user, or ID..."
			@update:filters="handleFilterUpdate"
			class="mt-6"
		/>

		<DataTable
			:columns="columns"
			:rows="formattedActivities"
			:pagination="activities"
			@paginate="handlePaginate"
			@view="openDetails"
		>
			<template #cell-created_at="{ value }">
				{{ formatDate(value) }}
			</template>
		</DataTable>

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

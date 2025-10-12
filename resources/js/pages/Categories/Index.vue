<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';
import Layout from '@/layouts/Layout.vue';
import DataTable from '@/components/DataTable.vue';
import FormModal from '@/components/FormModal.vue';
import ConfirmDeleteModal from '@/components/DeleteModal.vue';

defineOptions({
	layout: Layout
});

defineProps<{
	categories: { id: number; name: string }[];
}>();

const showAdd = ref(false);
const showEdit = ref(false);
const showDelete = ref(false);
const currentCategory = ref<any | null>(null);

const categoryColumns = [{ key: 'name', label: 'Category Name' }];
</script>

<template>
	<Head title="Categories" />

	<div class="p-8">
		<div class="flex justify-between items-center">
			<h1 class="text-3xl font-semibold text-gray-900">Categories</h1>
			<button
				@click="showAdd = true"
				class="px-4 py-2 rounded bg-blue-500 hover:bg-blue-600 text-white"
			>
				+ Add Category
			</button>
		</div>

		<DataTable
			:columns="categoryColumns"
			:rows="categories"
			@edit="
				(cat) => {
					currentCategory = cat;
					showEdit = true;
				}
			"
			@delete="
				(cat) => {
					currentCategory = cat;
					showDelete = true;
				}
			"
		/>

		<!-- Add Modal -->
		<FormModal
			v-if="showAdd"
			resourceName="Category"
			mode="add"
			submit-url="/categories"
			:fields="[{ key: 'name', label: 'Category Name', type: 'text' }]"
			@close="showAdd = false"
		/>

		<!-- Edit Modal -->
		<FormModal
			v-if="showEdit && currentCategory"
			resourceName="Category"
			mode="edit"
			:model="currentCategory"
			:submit-url="`/categories/${currentCategory.id}`"
			:fields="[{ key: 'name', label: 'Category Name', type: 'text' }]"
			@close="showEdit = false"
		/>

		<!-- Delete Modal -->
		<ConfirmDeleteModal
			v-if="showDelete && currentCategory"
			resourceName="Category"
			:label="currentCategory.name"
			:delete-url="`/categories/${currentCategory.id}`"
			@close="showDelete = false"
		/>
	</div>
</template>

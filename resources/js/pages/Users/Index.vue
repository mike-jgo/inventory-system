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
	users: {
		id: number;
		name: string;
		email: string;
		created_at: string;
	}[];
}>();

// Modals
const showAdd = ref(false);
const showEdit = ref(false);
const showDelete = ref(false);

const userToEdit = ref<any | null>(null);
const userToDelete = ref<any | null>(null);

// Table Columns
const userColumns = [
	{ key: 'name', label: 'Name' },
	{ key: 'email', label: 'Email' },
	{ key: 'created_at', label: 'Created' }
];

// Modal Handlers
const openAddModal = () => (showAdd.value = true);
const openEditModal = (user: any) => {
	userToEdit.value = { ...user };
	showEdit.value = true;
};
const openDeleteModal = (user: any) => {
	userToDelete.value = user;
	showDelete.value = true;
};
</script>

<template>
	<Head title="Users" />

	<div class="p-8">
		<div class="flex justify-between items-center">
			<h1 class="text-3xl font-semibold text-gray-900">User Management</h1>
			<button
				@click="openAddModal"
				class="px-4 py-2 rounded bg-blue-500 hover:bg-blue-600 text-white"
			>
				+ Add User
			</button>
		</div>

		<p class="mt-4 text-gray-700">Manage all registered users in the system.</p>

		<DataTable
			:columns="userColumns"
			:rows="users"
			@edit="openEditModal"
			@delete="openDeleteModal"
		/>

		<!-- Add User Modal -->
		<FormModal
			v-if="showAdd"
			resourceName="User"
			mode="add"
			submit-url="/users"
			:fields="[
				{ key: 'name', label: 'Name', type: 'text' },
				{ key: 'email', label: 'Email', type: 'email' },
				{ key: 'password', label: 'Password', type: 'password' }
			]"
			@close="showAdd = false"
		/>

		<!-- Edit User Modal -->
		<FormModal
			v-if="showEdit && userToEdit"
			resourceName="User"
			mode="edit"
			:model="userToEdit"
			:submit-url="`/users/${userToEdit.id}`"
			:fields="[
				{ key: 'name', label: 'Name', type: 'text' },
				{ key: 'email', label: 'Email', type: 'email' },
				{ key: 'password', label: 'New Password (optional)', type: 'password' }
			]"
			@close="showEdit = false"
		/>

		<!-- Delete Confirmation Modal -->
		<ConfirmDeleteModal
			v-if="showDelete && userToDelete"
			resourceName="User"
			:label="userToDelete.name"
			:delete-url="`/users/${userToDelete.id}`"
			@close="showDelete = false"
		/>
	</div>
</template>

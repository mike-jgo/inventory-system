<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';
import Layout from '@/layouts/Layout.vue';
import DataTable from '@/components/DataTable.vue';
import FormModal from '@/components/FormModal.vue';
import ConfirmDeleteModal from '@/components/DeleteModal.vue';

defineOptions({
  layout: Layout,
});

defineProps<{
  items: { id: number; name: string; category_id: number | null; category?: { id: number; name: string } | null; quantity: number }[];
  categories: { id: number; name: string }[];
}>();

const showAdd = ref(false);
const showEdit = ref(false);
const showDelete = ref(false);

const itemToEdit = ref<any | null>(null);
const itemToDelete = ref<any | null>(null);

const itemColumns = [
  { key: 'name', label: 'Name' },
  { key: 'category.name', label: 'Category' },
  { key: 'quantity', label: 'Quantity' },
];

const openEditModal = (item: any) => {
  itemToEdit.value = {
    id: item.id,
    name: item.name,
    category_id: item.category_id,
    quantity: item.quantity,
  };
  showEdit.value = true;
};

const openDeleteModal = (item: any) => {
  itemToDelete.value = item;
  showDelete.value = true;
};
</script>

<template>
  <Head title="Item List" />

  <div class="p-8">
    <div class="flex justify-between items-center">
      <h1 class="text-3xl font-semibold text-gray-900">Item List</h1>
      <button
        @click="showAdd = true"
        class="px-4 py-2 rounded bg-blue-500 hover:bg-blue-600 text-white"
      >
        + Add Item
      </button>
    </div>

    <p class="mt-4 text-gray-700">List of items currently in stock.</p>

    <DataTable
      :columns="itemColumns"
      :rows="items"
      @edit="openEditModal"
      @delete="openDeleteModal"
    />

    <!-- Add Modal -->
    <FormModal
      v-if="showAdd"
      resourceName="Item"
      mode="add"
      submit-url="/items"
      :fields="[
        { key: 'name', label: 'Name', type: 'text' },
        { key: 'category_id', label: 'Category', type: 'select', options: categories.map(c => ({ value: c.id, label: c.name })) },
        { key: 'quantity', label: 'Quantity', type: 'number' }
      ]"
      @close="showAdd = false"
    />

    <!-- Edit Modal -->
    <FormModal
      v-if="showEdit && itemToEdit"
      resourceName="Item"
      mode="edit"
      :model="itemToEdit"
      :submit-url="`/items/${itemToEdit.id}`"
      :fields="[
        { key: 'name', label: 'Name', type: 'text' },
        { key: 'category_id', label: 'Category', type: 'select', options: categories.map(c => ({ value: c.id, label: c.name })) },
        { key: 'quantity', label: 'Quantity', type: 'number' }
      ]"
      @close="showEdit = false"
    />

    <!-- Delete Modal -->
    <ConfirmDeleteModal
      v-if="showDelete && itemToDelete"
      resourceName="Item"
      :label="itemToDelete.name"
      :delete-url="`/items/${itemToDelete.id}`"
      @close="showDelete = false"
    />
  </div>
</template>

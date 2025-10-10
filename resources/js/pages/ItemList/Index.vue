<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';
import AddItemModal from '@/components/AddItemModal.vue';
import DeleteItemModal from '@/components/DeleteItemModal.vue';
import EditItemModal from '@/components/EditItemModal.vue';

defineProps<{
  items: { id: number; name: string; category_id: number | null; category?: { id: number; name: string } | null; quantity: number }[];
  categories: { id: number; name: string }[];
}>();

const showModal = ref(false);
const showDeleteModal = ref(false);
const showEditModal = ref(false);

const itemToDelete = ref<{ id: number; name: string } | null>(null);
const itemToEdit = ref<any | null>(null);

const openDeleteModal = (item: { id: number; name: string }) => {
  itemToDelete.value = item;
  showDeleteModal.value = true;
};

const openEditModal = (item: any) => {
  itemToEdit.value = {
    id: item.id,
    name: item.name,
    category_id: item.category_id,
    quantity: item.quantity,
  };
  showEditModal.value = true;
};
</script>

<template>
  <Head title="Item List" />

  <div class="min-h-screen p-8 bg-gray-200">
    <div class="flex justify-between items-center">
      <h1 class="text-3xl font-semibold text-gray-900">Inventory Items</h1>
      <button
        @click="showModal = true"
        class="px-4 py-2 rounded bg-blue-500 hover:bg-blue-600 text-white"
      >
        + Add Item
      </button>
    </div>

    <p class="mt-4 text-gray-700">List of items currently in stock.</p>

    <div class="mt-6 overflow-x-auto bg-white shadow-md rounded-lg">
      <table class="min-w-full border-collapse">
        <thead class="bg-gray-100 text-gray-700">
          <tr>
            <th class="px-6 py-3 text-left text-sm font-medium">Name</th>
            <th class="px-6 py-3 text-left text-sm font-medium">Category</th>
            <th class="px-6 py-3 text-left text-sm font-medium">Quantity</th>
            <th class="px-6 py-3 text-left text-sm font-medium">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="item in items"
            :key="item.id"
            class="hover:bg-gray-50 text-gray-900"
          >
            <td class="px-6 py-4 text-sm font-medium">{{ item.name }}</td>
            <td class="px-6 py-4 text-sm">{{ item.category ? item.category.name : '—' }}</td>
            <td class="px-6 py-4 text-sm">{{ item.quantity }}</td>
            <td class="px-6 py-4 text-sm space-x-2">
              <button
                class="px-3 py-1 rounded bg-blue-500 hover:bg-blue-600 text-white text-sm"
                @click="openEditModal(item)"
              >
                Edit
              </button>
              <button
                class="px-3 py-1 rounded bg-red-500 hover:bg-red-600 text-white text-sm"
                @click="openDeleteModal(item)"
              >
                Delete
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Add Modal -->
  <AddItemModal v-if="showModal" :categories="categories" @close="showModal = false" />

  <!-- Edit Modal -->
  <EditItemModal
    v-if="showEditModal && itemToEdit"
    :item="itemToEdit"
    :categories="categories"
    @close="showEditModal = false"
  />

  <!-- Delete Modal -->
  <DeleteItemModal
    v-if="showDeleteModal"
    :item-id="itemToDelete?.id || null"
    :item-name="itemToDelete?.name || null"
    @close="showDeleteModal = false"
  />
</template>

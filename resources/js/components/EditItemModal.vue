<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';

const props = defineProps<{
  item: { id: number; name: string; category_id: number | null; quantity: number };
  categories: { id: number; name: string }[];
}>();

const emit = defineEmits(['close']);

const form = useForm({
  name: props.item.name,
  category_id: props.item.category_id,
  quantity: props.item.quantity,
});

const submit = () => {
  form.put(`/items/${props.item.id}`, {
    onSuccess: () => {
      form.reset();
      emit('close');
    },
  });
};
</script>

<template>
  <div class="bg-black/40 fixed inset-0 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-md">
      <h2 class="text-xl font-semibold mb-4">Edit Item</h2>

      <form @submit.prevent="submit">
        <!-- Name -->
        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700">Name</label>
          <input
            v-model="form.name"
            type="text"
            class="text-gray-700 mt-1 p-2 block w-full rounded-md border-gray-300 shadow-sm"
            required
          />
          <div v-if="form.errors.name" class="text-red-500 text-sm">{{ form.errors.name }}</div>
        </div>

        <!-- Category -->
        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700">Category</label>
          <select
            v-model="form.category_id"
            class="text-gray-700 mt-1 p-2 block w-full rounded-md border-gray-300 shadow-sm"
            required
          >
            <option disabled value="">-- Select a category --</option>
            <option v-for="cat in props.categories" :key="cat.id" :value="cat.id">
              {{ cat.name }}
            </option>
          </select>
          <div v-if="form.errors.category_id" class="text-red-500 text-sm">{{ form.errors.category_id }}</div>
        </div>

        <!-- Quantity -->
        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700">Quantity</label>
          <input
            v-model.number="form.quantity"
            type="number"
            min="0"
            class="text-gray-700 mt-1 p-2 block w-full rounded-md border-gray-300 shadow-sm"
            required
          />
          <div v-if="form.errors.quantity" class="text-red-500 text-sm">{{ form.errors.quantity }}</div>
        </div>

        <!-- Buttons -->
        <div class="flex justify-end space-x-2">
          <button
            type="button"
            @click="$emit('close')"
            class="px-4 py-2 rounded bg-gray-300 hover:bg-gray-400"
          >
            Cancel
          </button>
          <button
            type="submit"
            class="px-4 py-2 rounded bg-blue-500 hover:bg-blue-600 text-white"
          >
            Update
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

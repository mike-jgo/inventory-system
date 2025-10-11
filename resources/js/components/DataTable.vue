<script setup lang="ts">
const props = defineProps<{
  columns: { key: string; label: string }[];
  rows: any[];
}>();

const emit = defineEmits(['edit', 'delete']);
</script>

<template>
  <div class="overflow-x-auto bg-white shadow-md rounded-lg mt-6">
    <table class="min-w-full border-collapse">
      <thead class="bg-gray-100 text-gray-700">
        <tr>
          <th
            v-for="col in props.columns"
            :key="col.key"
            class="px-6 py-3 text-left text-sm font-medium"
          >
            {{ col.label }}
          </th>
          <th class="px-6 py-3 text-left text-sm font-medium">Actions</th>
        </tr>
      </thead>
      <tbody>
        <tr
          v-for="row in props.rows"
          :key="row.id"
          class="hover:bg-gray-50 text-gray-900"
        >
          <td
            v-for="col in props.columns"
            :key="col.key"
            class="px-6 py-4 text-sm"
          >
            <!-- Handle nested category.name etc -->
            {{ col.key.includes('.') 
              ? col.key.split('.').reduce((obj, key) => obj?.[key], row) 
              : row[col.key] }}
          </td>
          <td class="px-6 py-4 text-sm space-x-2">
            <button
              class="px-3 py-1 rounded bg-blue-500 hover:bg-blue-600 text-white text-sm"
              @click="emit('edit', row)"
            >
              Edit
            </button>
            <button
              class="px-3 py-1 rounded bg-red-500 hover:bg-red-600 text-white text-sm"
              @click="emit('delete', row)"
            >
              Delete
            </button>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

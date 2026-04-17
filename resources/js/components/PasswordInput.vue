<script setup lang="ts">
import { ref } from 'vue'

defineProps<{
  modelValue: string
  required?: boolean
}>()

defineEmits<{
  'update:modelValue': [value: string]
}>()

const show = ref(false)
</script>

<template>
  <div class="relative">
    <input
      :value="modelValue"
      :type="show ? 'text' : 'password'"
      :required="required"
      class="w-full border border-gray-300 rounded-lg px-3 py-2 pr-10 focus:outline-none focus:ring-2 focus:ring-blue-500"
      @input="$emit('update:modelValue', ($event.target as HTMLInputElement).value)"
    />
    <button
      type="button"
      @click="show = !show"
      class="absolute inset-y-0 right-0 flex items-center px-3 text-gray-500 hover:text-gray-700"
      :aria-label="show ? 'Hide password' : 'Show password'"
    >
      <svg v-if="!show" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
      </svg>
      <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.477 0-8.268-2.943-9.542-7a9.97 9.97 0 012.126-3.364M9.878 9.878a3 3 0 104.243 4.243M9.878 9.878L6.5 6.5m3.378 3.378l4.243 4.243M6.5 6.5L3 3m3.5 3.5A9.953 9.953 0 0112 5c4.477 0 8.268 2.943 9.542 7a9.97 9.97 0 01-1.626 3.174M6.5 6.5l11 11" />
      </svg>
    </button>
  </div>
</template>

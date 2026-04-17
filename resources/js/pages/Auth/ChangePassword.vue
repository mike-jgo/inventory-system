<script setup lang="ts">
import { useForm, usePage } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import PasswordInput from '@/components/PasswordInput.vue'

const page = usePage()
const user = page.props.auth.user as any

const form = useForm({
  current_password: '',
  password: '',
  password_confirmation: '',
})

const submit = () => {
  form.put(route('password.update'), {
    onSuccess: () => form.reset(),
  })
}

const goBack = () => window.history.back()
</script>

<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-100">
    <div class="w-full max-w-md bg-white p-8 rounded-lg shadow-sm">
      <button
        type="button"
        @click="goBack"
        class="flex items-center text-sm text-gray-500 hover:text-gray-700 mb-6 cursor-pointer"
      >
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
        </svg>
        Back
      </button>

      <h1 class="text-2xl font-bold text-center mb-6">Change Password</h1>

      <div v-if="$page.props.flash?.success" class="mb-4 text-green-600 text-sm text-center">
        {{ $page.props.flash.success }}
      </div>

      <form @submit.prevent="submit" class="space-y-4">
        <div>
          <label class="block mb-1 text-gray-700">Current Password</label>
          <PasswordInput v-model="form.current_password" required />
          <div v-if="form.errors.current_password" class="text-red-600 text-sm mt-1">
            {{ form.errors.current_password }}
          </div>
        </div>

        <div>
          <label class="block mb-1 text-gray-700">New Password</label>
          <PasswordInput v-model="form.password" required />
          <p class="text-xs text-gray-500 mt-1">
            Min. 8 characters with uppercase, lowercase, number, and special character.
          </p>
          <div v-if="form.errors.password" class="text-red-600 text-sm mt-1">
            {{ form.errors.password }}
          </div>
        </div>

        <div>
          <label class="block mb-1 text-gray-700">Confirm New Password</label>
          <PasswordInput v-model="form.password_confirmation" required />
        </div>

        <button
          type="submit"
          class="w-full bg-blue-500 text-white py-2 rounded-lg hover:bg-blue-600 transition disabled:opacity-50"
          :disabled="form.processing"
        >
          Update Password
        </button>
      </form>
    </div>
  </div>
</template>

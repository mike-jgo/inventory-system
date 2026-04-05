<script setup lang="ts">
import { Link, useForm } from '@inertiajs/vue3'
import { route } from 'ziggy-js'

const props = defineProps<{
  token: string
  email: string
}>()

const form = useForm({
  token: props.token,
  email: props.email,
  password: '',
  password_confirmation: '',
})

const submit = () => {
  form.post(route('password.store'), {
    onFinish: () => form.reset('password', 'password_confirmation'),
  })
}
</script>

<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-100">
    <div class="w-full max-w-md bg-white p-8 rounded-lg shadow-sm">
      <h1 class="text-2xl font-bold text-center mb-6">Reset Password</h1>

      <form @submit.prevent="submit" class="space-y-4">
        <div>
          <label class="block mb-1 text-gray-700">Email</label>
          <input
            v-model="form.email"
            type="email"
            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
            required
          />
          <div v-if="form.errors.email" class="text-red-600 text-sm mt-1">
            {{ form.errors.email }}
          </div>
        </div>

        <div>
          <label class="block mb-1 text-gray-700">New Password</label>
          <input
            v-model="form.password"
            type="password"
            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
            required
          />
          <p class="text-xs text-gray-500 mt-1">
            Min. 8 characters with uppercase, lowercase, number, and special character.
          </p>
          <div v-if="form.errors.password" class="text-red-600 text-sm mt-1">
            {{ form.errors.password }}
          </div>
        </div>

        <div>
          <label class="block mb-1 text-gray-700">Confirm New Password</label>
          <input
            v-model="form.password_confirmation"
            type="password"
            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
            required
          />
        </div>

        <button
          type="submit"
          class="w-full bg-blue-500 text-white py-2 rounded-lg hover:bg-blue-600 transition disabled:opacity-50"
          :disabled="form.processing"
        >
          Reset Password
        </button>
      </form>

      <p class="mt-4 text-center text-sm">
        <Link :href="route('login')" class="text-blue-500 hover:underline">
          Back to Login
        </Link>
      </p>
    </div>
  </div>
</template>

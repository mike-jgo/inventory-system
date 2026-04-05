<script setup lang="ts">
import { Link, useForm } from '@inertiajs/vue3'
import { route } from 'ziggy-js'

const form = useForm({
  email: '',
})

const submit = () => {
  form.post(route('password.email'))
}
</script>

<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-100">
    <div class="w-full max-w-md bg-white p-8 rounded-lg shadow-sm">
      <h1 class="text-2xl font-bold text-center mb-2">Forgot Password</h1>
      <p class="text-gray-600 text-sm text-center mb-6">
        Enter your email address and we'll send you a reset link.
      </p>

      <div v-if="$page.props.flash?.status" class="mb-4 text-green-600 text-sm text-center">
        {{ $page.props.flash.status }}
      </div>

      <form @submit.prevent="submit">
        <div class="mb-4">
          <label class="block mb-1 text-gray-700">Email</label>
          <input
            v-model="form.email"
            type="email"
            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
            required
            autofocus
          />
          <div v-if="form.errors.email" class="text-red-600 text-sm mt-1">
            {{ form.errors.email }}
          </div>
        </div>

        <button
          type="submit"
          class="w-full bg-blue-500 text-white py-2 rounded-lg hover:bg-blue-600 transition disabled:opacity-50"
          :disabled="form.processing"
        >
          Send Reset Link
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

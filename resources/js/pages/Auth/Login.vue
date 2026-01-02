<script setup lang="ts">
import { Link, useForm, usePage } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import { computed } from 'vue'

const page = usePage()
const logo = computed(() => page.props.logo as string)

const form = useForm({
  email: '',
  password: '',
  remember: false,
})

const submit = () => {
  form.post(route('login.attempt'))
}
</script>

<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-100">
    <div class="w-full max-w-md bg-white p-8 rounded-lg shadow-sm">

      <!-- Logo -->
      <div class="flex justify-center mb-4">
        <div class="w-48 h-48 flex items-center justify-center">
          <img
            :src="`/${logo}`"
            alt="Logo"
            class="max-w-full max-h-full object-contain"
          />
        </div>
      </div>

      <h1 class="text-2xl font-bold text-center mb-6">
        Welcome Back!
      </h1>

      <form @submit.prevent="submit">
        <!-- Email -->
        <div class="mb-4">
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

        <!-- Password -->
        <div class="mb-4">
          <label class="block mb-1 text-gray-700">Password</label>
          <input
            v-model="form.password"
            type="password"
            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
            required
          />
          <div v-if="form.errors.password" class="text-red-600 text-sm mt-1">
            {{ form.errors.password }}
          </div>
        </div>

        <!-- Remember Me -->
        <div class="flex items-center mb-4">
          <input
            id="remember"
            v-model="form.remember"
            type="checkbox"
            class="rounded border-gray-300"
          />
          <label for="remember" class="ml-2 text-sm text-gray-600">
            Remember me
          </label>
        </div>

        <!-- Submit -->
        <button
          type="submit"
          class="w-full bg-blue-500 text-white py-2 rounded-lg hover:bg-blue-600 transition disabled:opacity-50"
          :disabled="form.processing"
        >
          Log in
        </button>
      </form>

      <p class="mt-4 text-center text-sm">
        Don’t have an account?
        <Link
          :href="route('register')"
          class="text-blue-500 hover:underline"
        >
          Register
        </Link>
      </p>
    </div>
  </div>
</template>

<script setup lang="ts">
import { Link, useForm } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import PasswordInput from '@/components/PasswordInput.vue';

const form = useForm({
	name: '',
	email: '',
	password: '',
	password_confirmation: ''
});

const submit = () => {
	form.post(route('register.store'));
};
</script>

<template>
	<div class="min-h-screen flex flex-col items-center justify-center bg-gray-100">
		<div class="bg-white p-8 rounded-lg shadow-xs w-full max-w-md">
			<h1 class="text-2xl font-bold mb-6 text-center">Create an Account</h1>

			<form @submit.prevent="submit">
				<!-- Name -->
				<div class="mb-4">
					<label class="block mb-1 text-gray-700">Name</label>
					<input
						v-model="form.name"
						type="text"
						class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
						required
					/>
					<div v-if="form.errors.name" class="text-red-600 text-sm mt-1">
						{{ form.errors.name }}
					</div>
				</div>

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
					<PasswordInput v-model="form.password" required />
					<ul class="mt-1 text-xs text-gray-500 space-y-0.5 list-disc list-inside">
						<li>At least 8 characters</li>
						<li>At least one uppercase letter</li>
						<li>At least one number</li>
						<li>At least one special character (e.g. !@#$%)</li>
					</ul>
					<div v-if="form.errors.password" class="text-red-600 text-sm mt-1">
						{{ form.errors.password }}
					</div>
				</div>

				<!-- Confirm Password -->
				<div class="mb-4">
					<label class="block mb-1 text-gray-700">Confirm Password</label>
					<PasswordInput v-model="form.password_confirmation" required />
					<div v-if="form.errors.password_confirmation" class="text-red-600 text-sm mt-1">
						{{ form.errors.password_confirmation }}
					</div>
				</div>

				<button
					type="submit"
					class="w-full bg-blue-500 text-white py-2 rounded-lg hover:bg-blue-600 transition disabled:opacity-50"
					:disabled="form.processing"
				>
					Register
				</button>
			</form>

			<p class="mt-4 text-center text-sm">
				Already have an account?
				<Link :href="route('login')" class="text-blue-500 hover:underline">
					Login
				</Link>
			</p>
		</div>
	</div>
</template>

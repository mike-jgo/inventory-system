<script setup lang="ts">
import { Link, useForm } from '@inertiajs/vue3';
import { route } from 'ziggy-js';

const form = useForm({
	email: '',
	password: '',
	remember: false
});

const submit = () => {
	form.post(route('login.attempt'));
};
</script>

<template>
	<div class="min-h-screen flex flex-col items-center justify-center bg-gray-100">
		<div class="bg-white p-8 rounded-lg shadow-xs w-full max-w-md">
			<h1 class="text-2xl font-bold mb-6 text-center">Welcome Back!</h1>

			<form @submit.prevent="submit">
				<div class="mb-4">
					<label class="block mb-1 text-gray-700">Email</label>
					<input
						v-model="form.email"
						type="email"
						class="w-full border-gray-300 rounded-lg border-1 px-2"
						required
					/>
					<div
						v-if="form.errors.email"
						class="text-red-600 text-sm mt-1"
					>
						{{ form.errors.email }}
					</div>
				</div>

				<div class="mb-4">
					<label class="block mb-1 text-gray-700">Password</label>
					<input
						v-model="form.password"
						type="password"
						class="w-full border-gray-300 rounded-lg border-1 px-2"
						required
					/>
					<div
						v-if="form.errors.password"
						class="text-red-600 text-sm mt-1"
					>
						{{ form.errors.password }}
					</div>
				</div>

				<div class="flex items-center mb-4">
					<input
						id="remember"
						v-model="form.remember"
						type="checkbox"
					/>
					<label
						for="remember"
						class="ml-2 text-sm text-gray-600"
					>
						Remember me
					</label>
				</div>

				<button
					type="submit"
					class="w-full bg-blue-500 text-white py-2 rounded-lg hover:bg-blue-600"
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
					>Register</Link
				>
			</p>
		</div>
	</div>
</template>

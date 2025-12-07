<script setup lang="ts">
import { ref, computed } from 'vue';
import { Link, usePage, router } from '@inertiajs/vue3';
import { route } from 'ziggy-js';

const mobileMenuOpen = ref(false);
const dropdownOpen = ref(false);

const page = usePage();
const currentUrl = computed(() => page.url);
const user = computed(() => page.props.auth?.user);

const logout = () => {
	router.post(route('logout'));
};

// Define nav links (conditionally show “Users” if logged in)
const links = computed(() => {
	const base = [
		{ name: 'Items', href: route('items.index') },
		{ name: 'Categories', href: route('categories.index') },
		{ name: 'Activity Log', href: route('activity-log.index') }
	];
	if (user.value && user.value.can?.view_users) {
		base.push({ name: 'Users', href: route('users.index') });
	}
	return base;
});
</script>

<template>
	<nav class="bg-white shadow-md">
		<div class="max-w-auto mx-auto px-4">
			<div class="flex justify-between h-16">
				<!-- Logo -->
				<div class="flex items-center">
					<span class="text-xl font-bold text-blue-600">InventorySys</span>
				</div>

				<!-- Desktop Links -->
				<div class="hidden sm:flex sm:space-x-6 items-center">
					<Link
						v-for="link in links"
						:key="link.name"
						:href="link.href"
						class="font-medium"
						:class="[
							currentUrl === link.href
								? 'text-blue-600 font-semibold border-b-2 border-blue-600'
								: 'text-gray-700 hover:text-blue-600'
						]"
					>
						{{ link.name }}
					</Link>

					<!-- User Dropdown -->
					<div
						v-if="user"
						class="relative ml-4"
					>
						<button
							@click="dropdownOpen = !dropdownOpen"
							class="flex items-center space-x-2 focus:outline-none"
						>
							<span class="text-gray-700 font-medium">{{ user.name }}</span>
							<svg
								class="h-4 w-4 text-gray-600"
								xmlns="http://www.w3.org/2000/svg"
								fill="none"
								viewBox="0 0 24 24"
								stroke="currentColor"
							>
								<path
									stroke-linecap="round"
									stroke-linejoin="round"
									stroke-width="2"
									d="M19 9l-7 7-7-7"
								/>
							</svg>
						</button>

						<!-- Dropdown Menu -->
						<div
							v-if="dropdownOpen"
							class="absolute right-0 mt-2 w-40 bg-white border rounded shadow-lg z-50"
						>
							<button
								@click="logout"
								class="w-full text-left px-4 py-2 text-gray-700 hover:bg-gray-100"
							>
								Logout
							</button>
						</div>
					</div>
				</div>

				<!-- Mobile Menu Button -->
				<div class="flex sm:hidden items-center">
					<button
						@click="mobileMenuOpen = !mobileMenuOpen"
						class="text-gray-700 hover:text-blue-600 focus:outline-none"
					>
						<svg
							class="h-6 w-6"
							xmlns="http://www.w3.org/2000/svg"
							fill="none"
							viewBox="0 0 24 24"
							stroke="currentColor"
						>
							<path
								v-if="!mobileMenuOpen"
								stroke-linecap="round"
								stroke-linejoin="round"
								stroke-width="2"
								d="M4 6h16M4 12h16M4 18h16"
							/>
							<path
								v-else
								stroke-linecap="round"
								stroke-linejoin="round"
								stroke-width="2"
								d="M6 18L18 6M6 6l12 12"
							/>
						</svg>
					</button>
				</div>
			</div>
		</div>

		<!-- Mobile Menu -->
		<div
			v-if="mobileMenuOpen"
			class="sm:hidden px-4 pb-4 space-y-2"
		>
			<Link
				v-for="link in links"
				:key="link.name"
				:href="link.href"
				class="block font-medium"
				:class="[
					currentUrl === link.href
						? 'text-blue-600 font-semibold border-l-4 pl-2 border-blue-600'
						: 'text-gray-700 hover:text-blue-600'
				]"
				@click="mobileMenuOpen = false"
			>
				{{ link.name }}
			</Link>

			<div
				v-if="user"
				class="pt-2 border-t"
			>
				<button
					@click="logout"
					class="w-full text-left px-2 py-2 text-gray-700 hover:bg-gray-100"
				>
					Logout
				</button>
			</div>
		</div>
	</nav>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { Link, usePage, router } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import Dropdown from './Dropdown.vue';

const mobileMenuOpen = ref(false);
const userDropdownOpen = ref(false);
const activeDropdown = ref<string | null>(null);

const page = usePage();
const currentUrl = computed(() => page.url);
const user = computed(() => page.props.auth?.user);

// Helper function to check if a URL matches the current page
const isActive = (href: string) => {
	// Get the current URL path
	const currentPath = currentUrl.value;
	
	// Extract pathname from href if it's a full URL
	let hrefPath = href;
	try {
		const url = new URL(href);
		hrefPath = url.pathname;
	} catch {
		// href is already a path, not a full URL
		hrefPath = href;
	}
	
	// Normalize both paths for comparison (remove trailing slashes)
	const normalizedCurrent = currentPath.replace(/\/$/, '');
	const normalizedHref = hrefPath.replace(/\/$/, '');
	const result = normalizedCurrent === normalizedHref;
	
	return result;
};

const logout = () => {
	router.post(route('logout'));
};

// Toggle dropdown with proper state management
const toggleDropdown = (dropdownName: string) => {
	// Close user dropdown when opening nav dropdown
	userDropdownOpen.value = false;
	
	if (activeDropdown.value === dropdownName) {
		activeDropdown.value = null;
	} else {
		activeDropdown.value = dropdownName;
	}
};

// Toggle user dropdown
const toggleUserDropdown = () => {
	// Close nav dropdowns when opening user dropdown
	activeDropdown.value = null;
	userDropdownOpen.value = !userDropdownOpen.value;
};

// Close all dropdowns
const closeAllDropdowns = () => {
	activeDropdown.value = null;
	userDropdownOpen.value = false;
};

// Handle click outside to close dropdowns
const handleClickOutside = (event: MouseEvent) => {
	const target = event.target as HTMLElement;
	// Check if click is outside all dropdowns
	if (!target.closest('.dropdown-container')) {
		closeAllDropdowns();
	}
};

// Add event listener on mount
onMounted(() => {
	document.addEventListener('click', handleClickOutside);
});

// Remove event listener on unmount
onUnmounted(() => {
	document.removeEventListener('click', handleClickOutside);
});

// Define navigation structure with dropdowns
const navGroups = computed(() => {
	const groups: any[] = [
		{
			name: 'Dashboard',
			href: route('dashboard'),
			isSingle: true
		},
		{
			name: 'Inventory Management',
			items: [
				{ name: 'Items', href: route('items.index') },
				{ name: 'Categories', href: route('categories.index') }
			]
		},
		{
			name: 'Operations',
			items: [
				{ name: 'Orders', href: route('orders.index') },
				{ name: 'Activity Log', href: route('activity-log.index') }
			]
		}
	];

	// Add Inventory to Inventory Management group if superadmin
	if (user.value && user.value.can?.view_inventory) {
		groups[1].items.unshift({ name: 'Inventory', href: route('inventory.index') });
	}

	// Add Users as a separate item if superadmin
	if (user.value && user.value.can?.view_users) {
		groups.push({
			name: 'Users',
			href: route('users.index'),
			isSingle: true
		});
	}

	return groups;
});

// Check if current URL matches any item in the dropdown
const isDropdownActive = (items: any[]) => {
	return items.some(item => isActive(item.href));
};
</script>

<template>
	<nav class="bg-white shadow-md">
		<div class="max-w-auto mx-auto px-4">
			<div class="flex justify-between h-16">
				<!-- Logo -->
				<div class="flex items-center">
					<span class="text-xl font-bold text-blue-600">InventorySys</span>
				</div>

				<!-- Desktop Navigation -->
				<div class="hidden sm:flex sm:space-x-2 items-center">
					<!-- Dropdown Groups -->
					<div
						v-for="group in navGroups"
						:key="group.name"
						class="dropdown-container relative"
					>
						<!-- Single Link (no dropdown) -->
						<Link
							v-if="group.isSingle"
							:href="group.href"
							class="px-3 py-2 rounded font-medium transition-colors"
							:class="[
								isActive(group.href)
									? 'text-blue-700 font-bold'
									: 'text-gray-700 hover:text-blue-600 hover:bg-gray-50'
							]"
						>
							{{ group.name }}
						</Link>

						<!-- Dropdown Menu -->
						<div v-else>
							<button
								@click="toggleDropdown(group.name)"
								class="flex items-center space-x-1 px-3 py-2 rounded font-medium transition-colors"
								:class="[
									isDropdownActive(group.items)
										? 'text-blue-700 font-bold'
										: 'text-gray-700 hover:text-blue-600 hover:bg-gray-50'
								]"
							>
								<span>{{ group.name }}</span>
								<svg
									class="h-4 w-4 transition-transform"
									:class="{ 'rotate-180': activeDropdown === group.name }"
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

							<!-- Dropdown Items -->
							<div
								v-if="activeDropdown === group.name"
								class="absolute left-0 mt-1 min-w-max bg-white rounded-lg shadow-lg z-50"
							>
								<Link
									v-for="item in group.items"
									:key="item.name"
									:href="item.href"
									@click="closeAllDropdowns"
									class="block px-4 py-2 text-sm font-medium transition-colors first:rounded-t-lg last:rounded-b-lg"
									:class="[
										isActive(item.href)
											? 'text-blue-700 font-bold'
											: 'text-gray-700 hover:bg-gray-50 hover:text-blue-600'
									]"
								>
									{{ item.name }}
								</Link>
							</div>
						</div>
					</div>

					<!-- User Dropdown -->
					<Dropdown
						v-if="user"
						:name="user.name"
						:items="[{ name: 'Logout', action: logout }]"
						:is-open="userDropdownOpen"
						align="right"
						class="ml-4"
						@toggle="toggleUserDropdown"
						@close="closeAllDropdowns"
					/>
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
			class="sm:hidden px-4 pb-4 space-y-2 border-t"
		>
			<!-- Mobile Navigation Groups -->
			<div
				v-for="group in navGroups"
				:key="group.name"
				class="py-2"
			>
				<!-- Single Link (no dropdown in mobile) -->
				<Link
					v-if="group.isSingle"
					:href="group.href"
					@click="mobileMenuOpen = false"
					class="block font-medium py-2 px-2 rounded"
					:class="[
						isActive(group.href)
							? 'text-blue-700 font-bold'
							: 'text-gray-700 hover:bg-gray-50'
					]"
				>
					{{ group.name }}
				</Link>

				<!-- Group with Items -->
				<div v-else>
					<div class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1 px-2">
						{{ group.name }}
					</div>
					<Link
						v-for="item in group.items"
						:key="item.name"
						:href="item.href"
						@click="mobileMenuOpen = false"
						class="block font-medium py-2 px-4 rounded"
						:class="[
							isActive(item.href)
								? 'text-blue-700 font-bold'
								: 'text-gray-700 hover:bg-gray-50'
						]"
					>
						{{ item.name }}
					</Link>
				</div>
			</div>

			<!-- Mobile Logout -->
			<div
				v-if="user"
				class="pt-2 border-t"
			>
				<button
					@click="logout"
					class="w-full text-left px-2 py-2 text-gray-700 hover:bg-gray-100 rounded"
				>
					Logout
				</button>
			</div>
		</div>
	</nav>
</template>

<script setup lang="ts">
import { ref } from 'vue';

const mobileMenuOpen = ref(false);

const links = [
  { name: 'Items', href: '/' },
  { name: 'Categories', href: '/categories' },
];
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
          <a
            v-for="link in links"
            :key="link.name"
            :href="link.href"
            class="text-gray-700 hover:text-blue-600 font-medium"
          >
            {{ link.name }}
          </a>
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
    <div v-if="mobileMenuOpen" class="sm:hidden px-4 pb-4 space-y-2">
      <a
        v-for="link in links"
        :key="link.name"
        :href="link.href"
        class="block text-gray-700 hover:text-blue-600 font-medium"
      >
        {{ link.name }}
      </a>
    </div>
  </nav>
</template>


<script setup lang="ts">
import Navbar from '@/components/Navbar.vue';
import FlashMessage from '@/components/FlashMessage.vue';

defineProps<{
	title?: string;
}>();
</script>

<template>
	<div class="min-h-screen flex flex-col bg-gray-100">
		<FlashMessage />
		<!-- Navbar -->
		<Navbar />

		<!-- Page Content -->
		<main class="flex-1">
			<slot />
		</main>
	</div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import { route } from 'ziggy-js'

const props = defineProps<{ status: number }>()

const config = computed(() => {
    const map: Record<number, { color: string; title: string; description: string }> = {
        403: {
            color: '#ef4444',
            title: 'Access Denied',
            description: "You don't have permission to access this page.",
        },
        404: {
            color: '#3b82f6',
            title: 'Page Not Found',
            description: "The page you're looking for doesn't exist or has been moved.",
        },
        500: {
            color: '#f59e0b',
            title: 'Server Error',
            description: 'Something went wrong on our end. Please try again later.',
        },
    }
    return map[props.status] ?? map[500]
})
</script>

<template>
    <Head :title="`${status} – ${config.title}`" />

    <div style="font-family: ui-sans-serif, system-ui, sans-serif; background: #f3f4f6; display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0;">
        <div style="background: white; border-radius: 12px; box-shadow: 0 4px 24px rgba(0,0,0,0.08); padding: 48px; text-align: center; max-width: 420px; width: 100%;">
            <div :style="{ fontSize: '72px', fontWeight: 800, color: config.color, lineHeight: 1 }">
                {{ status }}
            </div>
            <h1 style="font-size: 24px; font-weight: 700; color: #111827; margin: 16px 0 8px;">
                {{ config.title }}
            </h1>
            <p style="color: #6b7280; font-size: 15px; line-height: 1.6;">
                {{ config.description }}
            </p>
            <Link
                :href="route('dashboard')"
                style="display: inline-block; margin-top: 28px; padding: 10px 24px; background: #3b82f6; color: white; border-radius: 8px; text-decoration: none; font-weight: 600; font-size: 14px;"
            >
                Go back home
            </Link>
        </div>
    </div>
</template>

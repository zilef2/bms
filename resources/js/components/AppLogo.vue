<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';

interface Props {
    class?: string;
}

defineProps<Props>();
</script>

<template>
    <div className="bg-sidebar-primary text-sidebar-primary-foreground flex aspect-square size-8 items-center justify-center rounded-md">
        <AppLogoIcon className="size-[1.2rem] fill-current text-white dark:text-black" />
    </div>
    <div className="ml-1 grid flex-1 text-left text-sm">
        <span className="mb-0.5 truncate leading-none font-semibold">Laravel Starter Kit</span>
    </div>
</template>

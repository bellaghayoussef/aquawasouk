<script setup>
defineProps({
    actions: {
        type: Array,
        default: () => [],
    },
})

const emit = defineEmits(['select'])
</script>

<template>
    <div v-if="actions && actions.length" class="flex items-center gap-1.5 overflow-x-auto py-1 scrollbar-none">
        <button
            v-for="(action, index) in actions"
            :key="index"
            type="button"
            @click="emit('select', action)"
            class="whitespace-nowrap shrink-0 px-2.5 py-1 rounded-full text-[11px] font-medium bg-slate-100 hover:bg-brand-50 hover:text-brand-600 text-slate-700 dark:bg-slate-800 dark:hover:bg-slate-700 dark:text-slate-200 border border-slate-200/80 dark:border-slate-700 transition active:scale-95 shadow-2xs"
        >
            {{ action.label }}
        </button>
    </div>
</template>

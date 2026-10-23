<script setup>
import { ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    filters: {
        type: Object,
        default: () => ({})
    },
    showSearch: {
        type: Boolean,
        default: true
    },
    showStatus: {
        type: Boolean,
        default: false
    },
    showRole: {
        type: Boolean,
        default: false
    },
    showCategory: {
        type: Boolean,
        default: false
    },
    showDateRange: {
        type: Boolean,
        default: false
    },
    statusOptions: {
        type: Array,
        default: () => []
    },
    roleOptions: {
        type: Array,
        default: () => []
    },
    categories: {
        type: Array,
        default: () => []
    },
    placeholder: {
        type: String,
        default: 'Search...'
    }
});

const search = ref(props.filters.search || '');
const status = ref(props.filters.status || '');
const role = ref(props.filters.role || '');
const category = ref(props.filters.category || '');
const dateFrom = ref(props.filters.date_from || '');
const dateTo = ref(props.filters.date_to || '');

const applyFilters = () => {
    router.get(window.location.pathname, {
        search: search.value,
        status: status.value,
        role: role.value,
        category: category.value,
        date_from: dateFrom.value,
        date_to: dateTo.value,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const resetFilters = () => {
    search.value = '';
    status.value = '';
    role.value = '';
    category.value = '';
    dateFrom.value = '';
    dateTo.value = '';
    applyFilters();
};

// Auto-apply search after typing stops
let searchTimeout;
watch(search, () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        applyFilters();
    }, 500);
});
</script>

<template>
    <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow-sm mb-6">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Search -->
            <div v-if="showSearch">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Search
                </label>
                <input
                    v-model="search"
                    type="text"
                    :placeholder="placeholder"
                    class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500"
                />
            </div>

            <!-- Status Filter -->
            <div v-if="showStatus">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Status
                </label>
                <select
                    v-model="status"
                    @change="applyFilters"
                    class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500"
                >
                    <option value="">All Status</option>
                    <option v-for="option in statusOptions" :key="option.value" :value="option.value">
                        {{ option.label }}
                    </option>
                </select>
            </div>

            <!-- Role Filter -->
            <div v-if="showRole">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Role
                </label>
                <select
                    v-model="role"
                    @change="applyFilters"
                    class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500"
                >
                    <option value="">All Roles</option>
                    <option v-for="option in roleOptions" :key="option.value" :value="option.value">
                        {{ option.label }}
                    </option>
                </select>
            </div>

            <!-- Category Filter -->
            <div v-if="showCategory">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Category
                </label>
                <select
                    v-model="category"
                    @change="applyFilters"
                    class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500"
                >
                    <option value="">All Categories</option>
                    <option v-for="cat in categories" :key="cat.id" :value="cat.id">
                        {{ cat.name }}
                    </option>
                </select>
            </div>

            <!-- Date From -->
            <div v-if="showDateRange">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Date From
                </label>
                <input
                    v-model="dateFrom"
                    type="date"
                    @change="applyFilters"
                    class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500"
                />
            </div>

            <!-- Date To -->
            <div v-if="showDateRange">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Date To
                </label>
                <input
                    v-model="dateTo"
                    type="date"
                    @change="applyFilters"
                    class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500"
                />
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="mt-4 flex justify-end space-x-2">
            <button
                @click="resetFilters"
                class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md hover:bg-gray-50 dark:hover:bg-gray-600 transition"
            >
                Reset Filters
            </button>
            <button
                @click="applyFilters"
                class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700 transition"
            >
                Apply Filters
            </button>
        </div>
    </div>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const user = computed(() => page.props.auth.user);

// Redirect based on role
const dashboardRoute = computed(() => {
    // if (user.value.role === 'admin') {
    //     return route('admin.dashboard');
    // } else if (user.value.role === 'vendor') {
    //     return route('vendor.dashboard');
    // } else {
    //     return route('customer.dashboard');
    // }
});

// // Auto-redirect to appropriate dashboard
// if (window.location.pathname === '/dashboard') {
//     window.location.href = dashboardRoute.value;
// }
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                Redirecting to your dashboard...
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg dark:bg-gray-800">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <div class="text-center">
                            <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mb-4"></div>
                            <p class="text-lg mb-4">Redirecting you to your dashboard...</p>
                            
                            <div class="mt-6 space-y-2">
                                <p class="text-sm text-gray-600 dark:text-gray-400">Quick links:</p>
                                <div class="flex justify-center space-x-4 mt-4">
                                    <Link v-if="user.role === 'admin'" :href="route('admin.dashboard')" 
                                        class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                                        Admin Dashboard
                                    </Link>
                                    <Link v-if="user.role === 'vendor'" :href="route('vendor.dashboard')" 
                                        class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                                        Vendor Dashboard
                                    </Link>
                                    <Link v-if="user.role === 'customer'" :href="route('customer.dashboard')" 
                                        class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                                        My Dashboard
                                    </Link>
                                    <Link :href="route('home')" 
                                        class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 dark:bg-gray-700 dark:text-gray-200">
                                        Go to Store
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

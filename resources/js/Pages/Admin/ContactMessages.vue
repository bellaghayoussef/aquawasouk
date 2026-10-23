<script setup>
import { Head, router } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { ref } from 'vue';
import TableFilters from '@/Components/TableFilters.vue';

const props = defineProps({
    messages: Object,
    filters: Object,
});

const selectedMessage = ref(null);
const showModal = ref(false);
const newStatus = ref('');
const adminNotes = ref('');

const viewMessage = (message) => {
    selectedMessage.value = message;
    newStatus.value = message.status;
    adminNotes.value = message.admin_notes || '';
    showModal.value = true;
    
    // Mark as read if it's new
    if (message.status === 'new') {
        updateStatus(message.id, 'read', message.admin_notes);
    }
};

const updateStatus = (id, status, notes = null) => {
    router.patch(route('admin.contact-messages.status', id), {
        status: status,
        admin_notes: notes,
    }, {
        preserveScroll: true,
    });
};

const saveNotes = () => {
    if (selectedMessage.value) {
        updateStatus(selectedMessage.value.id, newStatus.value, adminNotes.value);
        showModal.value = false;
    }
};

const deleteMessage = (id) => {
    if (confirm('Are you sure you want to delete this message?')) {
        router.delete(route('admin.contact-messages.destroy', id), {
            preserveScroll: true,
        });
    }
};

const getStatusColor = (status) => {
    const colors = {
        'new': 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
        'read': 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
        'replied': 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
    };
    return colors[status] || colors['new'];
};

const formatDate = (date) => {
    return new Date(date).toLocaleString();
};
</script>

<template>
    <Head title="Contact Messages - Admin" />

    <DashboardLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Contact Messages
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">Contact Messages</h2>

                        <!-- Filters -->
                        <TableFilters
                            :filters="filters"
                            :show-search="true"
                            :show-status="true"
                            :show-date-range="true"
                            :status-options="[
                                { value: 'new', label: 'New' },
                                { value: 'read', label: 'Read' },
                                { value: 'replied', label: 'Replied' }
                            ]"
                            placeholder="Search by name, email, subject..."
                            class="mb-6"
                        />

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Date</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Name</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Email</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Subject</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    <tr v-for="message in messages.data" :key="message.id" :class="message.status === 'new' ? 'bg-blue-50 dark:bg-blue-900/20' : ''">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-300">
                                            {{ formatDate(message.created_at) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                            {{ message.name }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                            {{ message.email }}
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-300">
                                            {{ message.subject }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span :class="getStatusColor(message.status)" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full">
                                                {{ message.status }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                            <button @click="viewMessage(message)" class="text-dolphin-blue hover:text-dolphin-blue/80">
                                                View
                                            </button>
                                            <button @click="deleteMessage(message.id)" class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300">
                                                Delete
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div v-if="messages.links && messages.links.length > 3" class="mt-6 flex justify-center">
                            <nav class="flex items-center space-x-2">
                                <template v-for="(link, index) in messages.links" :key="index">
                                    <component
                                        :is="link.url ? 'a' : 'span'"
                                        :href="link.url || undefined"
                                        :class="[
                                            'px-4 py-2 rounded-md text-sm font-medium transition',
                                            link.active
                                                ? 'bg-dolphin-blue text-white'
                                                : link.url
                                                    ? 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700'
                                                    : 'bg-gray-100 dark:bg-gray-700 text-gray-400 cursor-not-allowed',
                                        ]"
                                        v-html="link.label"
                                    ></component>
                                </template>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Message Modal -->
        <div v-if="showModal && selectedMessage" class="fixed inset-0 z-50 overflow-y-auto" @click.self="showModal = false">
            <div class="flex items-center justify-center min-h-screen px-4">
                <div class="fixed inset-0 bg-black opacity-50"></div>
                <div class="relative bg-white dark:bg-gray-800 rounded-lg max-w-2xl w-full p-6">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Message Details</h3>
                    
                    <div class="space-y-4">
                        <div>
                            <strong class="text-gray-700 dark:text-gray-300">From:</strong>
                            <p class="text-gray-900 dark:text-white">{{ selectedMessage.name }} ({{ selectedMessage.email }})</p>
                        </div>
                        
                        <div>
                            <strong class="text-gray-700 dark:text-gray-300">Subject:</strong>
                            <p class="text-gray-900 dark:text-white">{{ selectedMessage.subject }}</p>
                        </div>
                        
                        <div>
                            <strong class="text-gray-700 dark:text-gray-300">Message:</strong>
                            <p class="text-gray-900 dark:text-white whitespace-pre-wrap">{{ selectedMessage.message }}</p>
                        </div>
                        
                        <div>
                            <strong class="text-gray-700 dark:text-gray-300">Date:</strong>
                            <p class="text-gray-900 dark:text-white">{{ formatDate(selectedMessage.created_at) }}</p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Status:</label>
                            <select v-model="newStatus" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                                <option value="new">New</option>
                                <option value="read">Read</option>
                                <option value="replied">Replied</option>
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Admin Notes:</label>
                            <textarea v-model="adminNotes" rows="3" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white" placeholder="Add notes..."></textarea>
                        </div>
                    </div>
                    
                    <div class="mt-6 flex justify-end space-x-3">
                        <button @click="showModal = false" class="px-4 py-2 bg-gray-300 dark:bg-gray-600 text-gray-700 dark:text-gray-200 rounded-md hover:bg-gray-400 dark:hover:bg-gray-500">
                            Close
                        </button>
                        <button @click="saveNotes" class="px-4 py-2 bg-dolphin-blue text-white rounded-md hover:bg-dolphin-blue/90">
                            Save
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>

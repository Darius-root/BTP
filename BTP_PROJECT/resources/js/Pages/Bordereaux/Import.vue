<script>
import { ref } from 'vue'
import { Head, useForm, Link } from '@inertiajs/vue3'
import AdminLayout from '@/Components/layout/AdminLayout.vue'
import SidebarProvider from '@/Components/layout/SidebarProvider.vue'

export default {
    components: {
        AdminLayout,
        SidebarProvider,
        Head,
        Link,
    },
    setup() {
        const fileInput = ref(null)
        const fileName = ref('')

        const form = useForm({
            file: null,
            annee: new Date().getFullYear(),
            version: 'V1',
        })

        const handleFileChange = (event) => {
            const file = event.target.files[0]
            if (file) {
                form.file = file
                fileName.value = file.name
            }
        }

        const triggerFileInput = () => {
            fileInput.value?.click()
        }

        const removeFile = () => {
            form.file = null
            fileName.value = ''
            if (fileInput.value) {
                fileInput.value.value = ''
            }
        }

        const submit = () => {
            form.post('/bordereaux/import', {
                preserveScroll: true,
                onSuccess: () => {
                    form.reset()
                    fileName.value = ''
                },
            })
        }

        return {
            fileInput,
            fileName,
            form,
            handleFileChange,
            triggerFileInput,
            removeFile,
            submit,
        }
    },
}
</script>

<template>

    <Head title="Importer un bordereau" />

    <SidebarProvider>
        <AdminLayout>
            <div class="grid grid-cols-12 gap-4 md:gap-6">
                <div class="col-span-12">
                    <!-- Header -->
                    <div class="mb-6">
                        <Link href="/bordereaux"
                            class="inline-flex items-center text-sm text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white mb-4">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                        Retour à la liste
                        </Link>
                        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                            Importer un bordereau
                        </h1>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            Importez un fichier Excel contenant les données du bordereau
                        </p>
                    </div>

                    <!-- Form -->
                    <div class="max-w-3xl">
                        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
                            <form @submit.prevent="submit" class="space-y-6">
                                <!-- File Upload -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Fichier Excel <span class="text-red-500">*</span>
                                    </label>
                                    <div class="mt-1">
                                        <input ref="fileInput" type="file" accept=".xlsx,.xls"
                                            @change="handleFileChange" class="hidden" />

                                        <!-- Upload Area -->
                                        <div v-if="!fileName" @click="triggerFileInput"
                                            class="border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg p-8 text-center cursor-pointer hover:border-blue-500 dark:hover:border-blue-400 transition-colors duration-200">
                                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                            </svg>
                                            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                                                Cliquez pour sélectionner un fichier
                                            </p>
                                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-500">
                                                Formats acceptés: XLSX, XLS
                                            </p>
                                        </div>

                                        <!-- File Selected -->
                                        <div v-else
                                            class="border border-gray-300 dark:border-gray-600 rounded-lg p-4 flex items-center justify-between bg-gray-50 dark:bg-gray-700">
                                            <div class="flex items-center">
                                                <svg class="h-8 w-8 text-green-500" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                </svg>
                                                <div class="ml-3">
                                                    <p class="text-sm font-medium text-gray-900 dark:text-white">
                                                        {{ fileName }}
                                                    </p>
                                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                                        Prêt à importer
                                                    </p>
                                                </div>
                                            </div>
                                            <button type="button" @click="removeFile"
                                                class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300">
                                                <svg class="h-5 w-5" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                    <p v-if="form.errors.file" class="mt-1 text-sm text-red-600 dark:text-red-400">
                                        {{ form.errors.file }}
                                    </p>
                                </div>

                                <!-- Année -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Année <span class="text-red-500">*</span>
                                    </label>
                                    <input v-model.number="form.annee" type="number" min="2000"
                                        :max="new Date().getFullYear() + 10" required
                                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                        :class="{ 'border-red-500': form.errors.annee }" />
                                    <p v-if="form.errors.annee" class="mt-1 text-sm text-red-600 dark:text-red-400">
                                        {{ form.errors.annee }}
                                    </p>
                                </div>

                                <!-- Version -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Version <span class="text-red-500">*</span>
                                    </label>
                                    <input v-model="form.version" type="text" maxlength="10" required
                                        placeholder="Ex: V1, V2, etc."
                                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                        :class="{ 'border-red-500': form.errors.version }" />
                                    <p v-if="form.errors.version" class="mt-1 text-sm text-red-600 dark:text-red-400">
                                        {{ form.errors.version }}
                                    </p>
                                </div>

                                <!-- Info Box -->
                                <div
                                    class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                                    <div class="flex">
                                        <svg class="h-5 w-5 text-blue-400 mt-0.5" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        <div class="ml-3">
                                            <h3 class="text-sm font-medium text-blue-800 dark:text-blue-200">
                                                Instructions d'importation
                                            </h3>
                                            <ul
                                                class="mt-2 text-sm text-blue-700 dark:text-blue-300 list-disc list-inside space-y-1">
                                                <li>Le fichier doit être au format Excel (.xlsx ou .xls)</li>
                                                <li>Assurez-vous que les colonnes correspondent au format attendu</li>
                                                <li>L'année doit être comprise entre 2000 et {{ new Date().getFullYear()
                                                    + 10 }}</li>
                                                <li>La version permet de différencier plusieurs imports pour une même
                                                    année</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>

                                <!-- Actions -->
                                <div class="flex items-center justify-end gap-4 pt-4">
                                    <Link href="/bordereaux"
                                        class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 font-medium transition-colors duration-200">
                                    Annuler
                                    </Link>
                                    <button type="submit" :disabled="form.processing || !form.file"
                                        class="px-6 py-2 bg-blue-600 hover:bg-blue-700 disabled:bg-gray-400 text-white font-semibold rounded-lg shadow-sm transition-colors duration-200 flex items-center">
                                        <svg v-if="form.processing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white"
                                            fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                                stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor"
                                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                            </path>
                                        </svg>
                                        {{ form.processing ? 'Importation...' : 'Importer' }}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </AdminLayout>
    </SidebarProvider>
</template>

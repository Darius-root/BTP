<script>
import { ref } from 'vue'
import { Head, useForm, Link } from '@inertiajs/vue3'
import AdminLayout from '@/components/layout/AdminLayout.vue'
import SidebarProvider from '@/components/layout/SidebarProvider.vue'
import PageBreadcrumb from '@/components/common/PageBreadcrumb.vue'
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import { Button } from '@/components/ui/button'
import { Label } from '@/components/ui/label'

export default {
    components: {
        AdminLayout,
        SidebarProvider,
        PageBreadcrumb,
        Head,
        Link,
        Card,
        CardContent,
        CardHeader,
        CardTitle,
        CardDescription,
        Input,
        Button,
        Label,
    },
    props: {
        annees: Array
    },
    setup(props) {
        const currentPageTitle = ref("Importer un bordereau")
        const fileInput = ref(null)
        const isDragging = ref(false)

        const form = useForm({
            file: null,
            nom_bordereau: '',
            annee: new Date().getFullYear().toString(),
            version: 'V1'
        })

        const handleFileChange = (event) => {
            const file = event.target.files[0]
            if (file && isValidFile(file)) {
                form.file = file
                // Remplir automatiquement le nom du bordereau avec le nom du fichier (sans extension)
                if (!form.nom_bordereau) {
                    form.nom_bordereau = file.name.replace(/\.[^/.]+$/, '')
                }
            }
        }

        const isValidFile = (file) => {
            const validExtensions = ['.xlsx', '.xls']
            const fileName = file.name.toLowerCase()
            return validExtensions.some(ext => fileName.endsWith(ext))
        }

        const triggerFileInput = () => {
            fileInput.value?.click()
        }

        const clearFile = () => {
            form.file = null
            form.reset('file')
        }

        // Gestionnaires pour le drag and drop
        const handleDragEnter = (event) => {
            event.preventDefault()
            event.stopPropagation()
            isDragging.value = true
        }

        const handleDragOver = (event) => {
            event.preventDefault()
            event.stopPropagation()
        }

        const handleDragLeave = (event) => {
            event.preventDefault()
            event.stopPropagation()
            isDragging.value = false
        }

        const handleDrop = (event) => {
            event.preventDefault()
            event.stopPropagation()
            isDragging.value = false

            const file = event.dataTransfer.files[0]
            if (file && isValidFile(file)) {
                form.file = file
                if (!form.nom_bordereau) {
                    form.nom_bordereau = file.name.replace(/\.[^/.]+$/, '')
                }
            }
        }

        const submit = () => {
            // Vérifier que tous les champs sont remplis
            if (!form.file || !form.nom_bordereau.trim() || !form.annee || !form.version.trim()) {
                alert('Veuillez remplir tous les champs requis')
                return
            }

            // Créer un FormData
            const formData = new FormData()
            formData.append('file', form.file)
            formData.append('nom_bordereau', form.nom_bordereau.trim())
            formData.append('annee', form.annee)
            formData.append('version', form.version.trim())

            form.post('/bordereaux/import', {
                preserveScroll: true,
                onSuccess: () => {
                    form.reset()
                },
            })
        }

        return {
            currentPageTitle,
            fileInput,
            isDragging,
            form,
            handleFileChange,
            triggerFileInput,
            clearFile,
            handleDragEnter,
            handleDragOver,
            handleDragLeave,
            handleDrop,
            submit
        }
    },
}
</script>

<template>

    <Head title="Importer un bordereau" />
    <SidebarProvider>
        <AdminLayout>
            <!-- Breadcrumb -->
            <PageBreadcrumb :pageTitle="currentPageTitle" />

            <!-- Container principal -->
            <div
                class="rounded-2xl border  p-5  lg:p-6 shadow-sm">
                <div class="max-w-4xl mx-auto">
                    <Card>
                        <CardHeader>
                            <div class="flex items-center justify-between">
                                <div>
                                    <CardTitle class="text-2xl">Importer un bordereau</CardTitle>
                                    <CardDescription class="mt-1">
                                        Importez un fichier Excel contenant les données du bordereau
                                    </CardDescription>
                                </div>
                            </div>
                        </CardHeader>

                        <CardContent>
                            <form @submit.prevent="submit" class="space-y-6">
                                <!-- File Upload avec Drag and Drop -->
                                <div class="space-y-2">
                                    <Label for="file">
                                        Fichier Excel <span class="text-red-500">*</span>
                                    </Label>

                                    <input ref="fileInput" id="file" type="file" accept=".xlsx,.xls"
                                        @change="handleFileChange" class="hidden" />

                                    <!-- Zone de Drag and Drop -->
                                    <div @click="triggerFileInput" @dragenter="handleDragEnter"
                                        @dragover="handleDragOver" @dragleave="handleDragLeave" @drop="handleDrop"
                                        :class="[
                                            'mt-1 border-2 border-dashed rounded-lg p-8 text-center cursor-pointer transition-colors duration-200',
                                            isDragging
                                                ? 'border-blue-500 bg-blue-50 dark:border-blue-400 dark:bg-blue-900/20'
                                                : 'border-gray-300 dark:border-gray-600 hover:border-blue-500 dark:hover:border-blue-400'
                                        ]">
                                        <!-- État: Aucun fichier sélectionné -->
                                        <div v-if="!form.file">
                                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                            </svg>
                                            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                                                <span v-if="!isDragging">
                                                    Cliquez pour sélectionner un fichier ou glissez-déposez ici
                                                </span>
                                                <span v-else class="text-blue-600 dark:text-blue-400">
                                                    Lâchez le fichier pour l'uploader
                                                </span>
                                            </p>
                                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-500">
                                                Formats acceptés: XLSX, XLS (un seul fichier)
                                            </p>
                                        </div>

                                        <!-- État: Fichier sélectionné -->
                                        <div v-else>
                                            <div class="flex items-center justify-center">
                                                <svg class="h-12 w-12 text-green-500" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                </svg>
                                            </div>
                                            <p class="mt-2 text-sm font-medium text-gray-900 dark:text-white">
                                                {{ form.file.name }}
                                            </p>
                                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-500">
                                                Prêt à importer
                                            </p>
                                            <button type="button" @click.stop="clearFile"
                                                class="mt-2 text-sm text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300">
                                                Supprimer ce fichier
                                            </button>
                                        </div>
                                    </div>

                                    <p v-if="form.errors.file" class="mt-1 text-sm text-red-600">
                                        {{ form.errors.file }}
                                    </p>
                                </div>

                                <!-- Configuration du fichier -->
                                <div v-if="form.file" class="space-y-6">
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                        <div class="space-y-2">
                                            <Label for="nom_bordereau">
                                                Nom du bordereau <span class="text-red-500">*</span>
                                            </Label>
                                            <Input id="nom_bordereau" type="text" v-model="form.nom_bordereau" required
                                                maxlength="255" placeholder="Ex: Bordereau 2024" class="w-full" />
                                            <p v-if="form.errors.nom_bordereau" class="mt-1 text-sm text-red-600">
                                                {{ form.errors.nom_bordereau }}
                                            </p>
                                        </div>

                                        <div class="space-y-2">
                                            <Label for="annee">
                                                Année <span class="text-red-500">*</span>
                                            </Label>
                                            <select id="annee" v-model="form.annee" required
                                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                                                <option value="">Sélectionner une année</option>
                                                <option v-for="annee in annees" :key="annee" :value="annee">
                                                    {{ annee }}
                                                </option>
                                            </select>
                                            <p v-if="form.errors.annee" class="mt-1 text-sm text-red-600">
                                                {{ form.errors.annee }}
                                            </p>
                                        </div>

                                        <div class="space-y-2">
                                            <Label for="version">
                                                Version <span class="text-red-500">*</span>
                                            </Label>
                                            <Input id="version" type="text" v-model="form.version" required
                                                maxlength="20" placeholder="Ex: V1, V2, etc." class="w-full" />
                                            <p v-if="form.errors.version" class="mt-1 text-sm text-red-600">
                                                {{ form.errors.version }}
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Info Box -->
                                <div
                                    class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                                    <div class="flex">
                                        <svg class="h-5 w-5 text-blue-400 mt-0.5 flex-shrink-0" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        <div class="ml-3 flex-1">
                                            <h3 class="text-sm font-medium text-blue-800 dark:text-blue-200">
                                                Instructions d'importation
                                            </h3>
                                            <ul
                                                class="mt-2 text-sm text-blue-700 dark:text-blue-300 list-disc list-inside space-y-1">
                                                <li>Le fichier doit être au format Excel (.xlsx ou .xls)</li>
                                                <li>Assurez-vous que les colonnes correspondent au format attendu</li>
                                                <li>Le nom du bordereau doit être unique pour la combinaison
                                                    année/version</li>
                                                <li>Vérifiez que le bordereau n'existe pas déjà avec la même
                                                    combinaison</li>
                                            </ul>

                                            <!-- Lien de téléchargement de l'exemple -->
                                            <div class="mt-3 pt-3 border-t border-blue-200 dark:border-blue-700">
                                                <a href="/exemples/bordereau_exemple.xlsx" download
                                                    class="inline-flex items-center gap-2 text-sm font-medium text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 hover:underline transition-colors">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                    </svg>
                                                    <span>Télécharger un exemple de bordereau</span>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Actions -->
                                <div class="flex items-center gap-4 pt-4">
                                    <Button type="button" variant="outline" as-child class="flex-1 sm:flex-none">
                                        <Link href="/bordereaux">
                                        Annuler
                                        </Link>
                                    </Button>

                                    <Button type="submit" :disabled="form.processing || !form.file"
                                        class="flex-1 sm:flex-none">
                                        <svg v-if="form.processing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white"
                                            fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                                stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor"
                                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                            </path>
                                        </svg>
                                        {{ form.processing ? 'Importation...' : 'Importer' }}
                                    </Button>
                                </div>
                            </form>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </AdminLayout>
    </SidebarProvider>
</template>

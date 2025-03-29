<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';
import axios from 'axios';

const form = useForm({
    archivo: null,
});

const fileInput = ref(null);
const fileName = ref('');
const isValidating = ref(false);
const isUploading = ref(false);
const validationResults = ref(null);
const canProcess = ref(false);
const errors = ref([]);
const showErrorModal = ref(false);
const currentPage = ref(1);
const errorsPerPage = 50; // Mostrar 50 errores por página

const handleFileChange = (e) => {
    const file = e.target.files[0];
    if (file) {
        fileName.value = file.name;
        form.archivo = file;
        // Reiniciar estado
        validationResults.value = null;
        canProcess.value = false;
        showErrorModal.value = false;
    }
};

const validateExcel = () => {
    if (!form.archivo) {
        alert('Por favor seleccione un archivo');
        return;
    }

    isValidating.value = true;
    const formData = new FormData();
    formData.append('archivo', form.archivo);

    axios.post(route('validar.excel'), formData, {
        headers: {
            'Content-Type': 'multipart/form-data'
        }
    }).then(response => {
        validationResults.value = response.data.results;
        canProcess.value = response.data.can_process;
        
        if (!canProcess.value) {
            fetchValidationErrors();
        }
    }).catch(error => {
        console.error('Error validando el archivo:', error);
        alert('Error al validar el archivo: ' + (error.response?.data?.message || error.message));
    }).finally(() => {
        isValidating.value = false;
    });
};

const fetchValidationErrors = () => {
    axios.get(route('validacion.errores'))
        .then(response => {
            errors.value = response.data.errors;
        })
        .catch(error => {
            console.error('Error obteniendo errores:', error);
        });
};

const openErrorModal = () => {
    currentPage.value = 1;
    showErrorModal.value = true;
};

const closeErrorModal = () => {
    showErrorModal.value = false;
};

const processExcel = () => {
    if (!canProcess.value) {
        alert('Debe validar el archivo primero y corregir los errores');
        return;
    }

    isUploading.value = true;
    
    axios.post(route('importarExcel'))
        .then(response => {
            alert('Archivo procesado exitosamente');
            // Reiniciar formulario
            fileName.value = '';
            form.archivo = null;
            validationResults.value = null;
            canProcess.value = false;
        })
        .catch(error => {
            console.error('Error procesando el archivo:', error);
            alert('Error al procesar el archivo: ' + (error.response?.data?.message || error.message));
        })
        .finally(() => {
            isUploading.value = false;
        });
};

const seleccionarArchivo = () => {
    fileInput.value.click();
};

// Lógica de paginación
const totalPages = computed(() => Math.ceil(errors.value.length / errorsPerPage));
const paginatedErrors = computed(() => {
    const startIndex = (currentPage.value - 1) * errorsPerPage;
    return errors.value.slice(startIndex, startIndex + errorsPerPage);
});

const nextPage = () => {
    if (currentPage.value < totalPages.value) {
        currentPage.value++;
    }
};

const prevPage = () => {
    if (currentPage.value > 1) {
        currentPage.value--;
    }
};
</script>

<template>
    <GuestLayout>
        <Head title="Generación Masiva de Certificados" />
        
        <div class="bg-gradient-to-b from-[#363d4d] to-[#2c3340] py-8">
            <!-- Header Section -->
            <div class="container mx-auto px-4 mb-6">
                <div class="text-center text-white space-y-2">
                    <h1 class="text-2xl md:text-3xl font-light tracking-wide">Generación Masiva de Certificados</h1>
                    <p class="text-gray-300 text-sm">Banco Central de Reserva de El Salvador</p>
                </div>
            </div>

            <!-- Upload Card -->
            <div class="container mx-auto px-4 mb-8">
                <div class="max-w-md mx-auto">
                    <div class="bg-white dark:bg-[#303844] rounded-xl shadow-xl overflow-hidden">
                        <div class="px-8 py-6">
                            <div class="text-center space-y-2 mb-8">
                                <h2 class="text-xl font-semibold text-gray-800 dark:text-white">Carga de Archivo</h2>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Selecciona el archivo Excel con los datos para generar certificados</p>
                            </div>

                            <div class="space-y-6">
                                <!-- Input de archivo oculto -->
                                <input
                                    ref="fileInput"
                                    type="file"
                                    accept=".xlsx,.xls,.csv"
                                    class="hidden"
                                    @change="handleFileChange"
                                />
                                
                                <!-- Área de selección de archivo visualmente atractiva -->
                                <div 
                                    @click="seleccionarArchivo"
                                    class="w-full border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg p-8 text-center cursor-pointer hover:border-blue-500 dark:hover:border-blue-400 transition-all"
                                >
                                    <div v-if="!fileName" class="space-y-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                        <div class="text-sm text-gray-600 dark:text-gray-400">
                                            <span class="font-medium">Haz clic para seleccionar</span> o arrastra un archivo
                                        </div>
                                        <p class="text-xs text-gray-500 dark:text-gray-500">
                                            Formatos soportados: Excel (.xlsx, .xls) y CSV
                                        </p>
                                    </div>
                                    
                                    <div v-else class="space-y-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto h-10 w-10 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        <div class="text-sm font-medium text-gray-800 dark:text-gray-300">
                                            {{ fileName }}
                                        </div>
                                        <p class="text-xs text-gray-500 dark:text-gray-500">
                                            Haz clic para cambiar el archivo
                                        </p>
                                    </div>
                                </div>

                                <!-- Sección de validación -->
                                <div class="flex flex-col md:flex-row md:gap-4">
                                    <button
                                        @click="validateExcel"
                                        class="w-full px-4 py-3 mb-4 md:mb-0 text-sm font-medium text-white bg-[#4c566a] hover:bg-[#3b4252] rounded-lg transition focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#4c566a] disabled:opacity-50 dark:focus:ring-offset-gray-800"
                                        :disabled="!form.archivo || isValidating"
                                    >
                                        <span v-if="isValidating">
                                            <svg class="inline w-4 h-4 mr-2 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg>
                                            Validando...
                                        </span>
                                        <span v-else>Paso 1: Validar Excel</span>
                                    </button>

                                    <button
                                        @click="processExcel"
                                        class="w-full px-4 py-3 text-sm font-medium text-white bg-[#363d4d] hover:bg-[#2c3340] rounded-lg transition focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#363d4d] disabled:opacity-50 dark:focus:ring-offset-gray-800"
                                        :disabled="!canProcess || isUploading"
                                    >
                                        <span v-if="isUploading">
                                            <svg class="inline w-4 h-4 mr-2 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg>
                                            Procesando...
                                        </span>
                                        <span v-else>Paso 2: Procesar Certificados</span>
                                    </button>
                                </div>

                                <!-- Resultados de validación -->
                                <div v-if="validationResults" class="mt-6 p-4 bg-gray-50 dark:bg-gray-700/30 rounded-lg">
                                    <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Resultados de la validación</h3>
                                    
                                    <div class="mb-3">
                                        <div class="flex justify-between text-xs text-gray-600 dark:text-gray-400">
                                            <span>Total de registros:</span>
                                            <span class="font-medium">{{ validationResults.total }}</span>
                                        </div>
                                        <div class="flex justify-between text-xs text-gray-600 dark:text-gray-400">
                                            <span>Registros válidos:</span>
                                            <span class="font-medium text-green-600">{{ validationResults.valid_count }}</span>
                                        </div>
                                        <div class="flex justify-between text-xs text-gray-600 dark:text-gray-400">
                                            <span>Registros con errores:</span>
                                            <span class="font-medium" :class="validationResults.invalid_count > 0 ? 'text-red-600' : 'text-gray-600'">
                                                {{ validationResults.invalid_count }}
                                            </span>
                                        </div>
                                    </div>

                                    <!-- Mensaje de éxito o error -->
                                    <div v-if="validationResults.invalid_count === 0" class="p-3 bg-green-100 dark:bg-green-900/20 rounded text-xs text-green-800 dark:text-green-400">
                                        ¡Archivo validado correctamente! Puede proceder con el paso 2.
                                    </div>
                                    
                                    <div v-else class="p-3 bg-red-100 dark:bg-red-900/20 rounded text-xs text-red-800 dark:text-red-400">
                                        Se encontraron errores en el archivo. Por favor corrija los errores y vuelva a validar.
                                        
                                        <div class="mt-2">
                                            <button 
                                                @click="openErrorModal" 
                                                class="text-red-700 dark:text-red-300 hover:underline text-xs font-medium"
                                            >
                                                Ver detalles de errores
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Descripción del proceso -->
                                <div class="mt-6 p-4 bg-gray-50 dark:bg-gray-700/30 rounded-lg">
                                    <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Formato del archivo Excel</h3>
                                    <p class="text-xs text-gray-600 dark:text-gray-400">
                                        El archivo debe contener las siguientes columnas: nombre1, apellido1, dui, correo, teléfono, direccion y distrito.
                                    </p>
                                    <p class="text-xs text-gray-600 dark:text-gray-400 mt-2">
                                        Además, necesita tener las imágenes de los documentos en las siguientes carpetas:
                                    </p>
                                    <ul class="list-disc list-inside mt-1 text-xs text-gray-600 dark:text-gray-400">
                                        <li>document_front/[DUI].jpg|png|jpeg|pdf - Frente del DUI</li>
                                        <li>document_rear/[DUI].jpg|png|jpeg|pdf - Reverso del DUI</li>
                                        <li>document_owner/[DUI].jpg|png|jpeg|pdf - Selfie</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal de errores -->
        <div v-if="showErrorModal" class="fixed inset-0 z-50 overflow-y-auto" aria-modal="true" role="dialog">
            <div class="flex min-h-screen items-end justify-center px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <!-- Fondo oscuro -->
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="closeErrorModal"></div>

                <!-- Centrar modal -->
                <span class="hidden sm:inline-block sm:h-screen sm:align-middle">&#8203;</span>

                <!-- Contenido del modal -->
                <div class="inline-block transform overflow-hidden rounded-lg bg-white text-left align-bottom shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-3xl sm:align-middle">
                    <div class="bg-white dark:bg-[#303844] px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mt-3 sm:mt-0 sm:text-left w-full">
                                <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-white mb-4">
                                    Detalles de errores encontrados
                                </h3>
                                <div class="mt-2 mb-4">
                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                        Se encontraron {{ errors.length }} errores en el archivo. Corrija estos problemas y vuelva a validar.
                                    </p>
                                </div>

                                <!-- Tabla de errores paginada -->
                                <div class="max-h-96 overflow-y-auto mt-4 bg-gray-50 dark:bg-gray-700/30 rounded-lg">
                                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                        <thead class="bg-gray-100 dark:bg-gray-600">
                                            <tr>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-700 dark:text-gray-300">
                                                    Fila
                                                </th>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-700 dark:text-gray-300">
                                                    DUI
                                                </th>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-700 dark:text-gray-300">
                                                    Errores
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-800">
                                            <tr v-for="(error, index) in paginatedErrors" :key="index" class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                                    {{ error.row }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                                    {{ error.dui }}
                                                </td>
                                                <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                                    <ul class="list-disc list-inside">
                                                        <li v-for="(errMsg, errIndex) in error.errors" :key="errIndex">
                                                            {{ errMsg }}
                                                        </li>
                                                    </ul>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>

                                <!-- Paginación -->
                                <div class="flex justify-between items-center mt-4" v-if="totalPages > 1">
                                    <div class="text-sm text-gray-700 dark:text-gray-300">
                                        Página {{ currentPage }} de {{ totalPages }}
                                    </div>
                                    <div class="flex space-x-2">
                                        <button 
                                            @click="prevPage" 
                                            :disabled="currentPage === 1"
                                            class="px-3 py-1 bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 rounded hover:bg-gray-300 dark:hover:bg-gray-600 disabled:opacity-50"
                                        >
                                            Anterior
                                        </button>
                                        <button 
                                            @click="nextPage" 
                                            :disabled="currentPage === totalPages"
                                            class="px-3 py-1 bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 rounded hover:bg-gray-300 dark:hover:bg-gray-600 disabled:opacity-50"
                                        >
                                            Siguiente
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 dark:bg-[#252a35] px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                        <button 
                            type="button" 
                            class="mt-3 inline-flex w-full justify-center rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-4 py-2 text-base font-medium text-gray-700 dark:text-gray-200 shadow-sm hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
                            @click="closeErrorModal"
                        >
                            Cerrar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </GuestLayout>
</template>
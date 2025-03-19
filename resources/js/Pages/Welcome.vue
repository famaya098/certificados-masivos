<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const form = useForm({
    archivo: null,
});

const fileInput = ref(null);
const fileName = ref('');
const isUploading = ref(false);
const progress = ref(0);

const handleFileChange = (e) => {
    const file = e.target.files[0];
    if (file) {
        fileName.value = file.name;
        form.archivo = file;
    }
};

const submit = () => {
    isUploading.value = true;
    progress.value = 0;
    
    // Simulación del progreso 
    /* const interval = setInterval(() => {
        progress.value += 5;
        if (progress.value >= 100) {
            clearInterval(interval);
        }
    }, 200); */

    form.post(route('importarExcel'))
    
    
};

const seleccionarArchivo = () => {
    fileInput.value.click();
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

                            <form @submit.prevent="submit" class="space-y-6">
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

                                <!-- Barra de progreso (visible solo durante la carga) -->
                                <div v-if="isUploading" class="w-full bg-gray-200 rounded-full h-2.5 dark:bg-gray-700">
                                    <div 
                                        class="bg-[#363d4d] h-2.5 rounded-full transition-all duration-300" 
                                        :style="{ width: progress + '%' }"
                                    ></div>
                                </div>

                                <!-- Botón de envío -->
                                <button
                                    type="submit"
                                    class="w-full px-4 py-3 text-sm font-medium text-white bg-[#363d4d] hover:bg-[#2c3340] rounded-lg transition focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#363d4d] disabled:opacity-50 dark:focus:ring-offset-gray-800"
                                    :disabled="!form.archivo || isUploading"
                                >
                                    {{ isUploading ? 'Procesando...' : 'Procesar certificados' }}
                                </button>
                            </form>
                            
                            <!-- Descripción del proceso -->
                            <div class="mt-6 p-4 bg-gray-50 dark:bg-gray-700/30 rounded-lg">
                                <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Formato del archivo Excel</h3>
                                <p class="text-xs text-gray-600 dark:text-gray-400">
                                    El archivo debe contener las siguientes columnas: nombre, apellidos, DUI 
                                    (fotos del reverso y frente), datos de sede, selfie y otros campos requeridos.
                                </p>
                                <p class="text-xs text-gray-600 dark:text-gray-400 mt-2">
                                    Una vez procesado, se generarán los certificados mediante la API de Uanataca.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </GuestLayout>
</template>
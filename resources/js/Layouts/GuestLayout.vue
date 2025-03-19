<script setup>
import BreezeApplicationLogo from "@/Components/ApplicationLogo.vue";
import { Link } from "@inertiajs/vue3";
import { reactive, computed, ref } from "vue";
const isDark = ref();
isDark.value = localStorage.getItem("darkMode") == "true";
function toggleDarkMode() {
    isDark.value = !isDark.value;
    localStorage.setItem("darkMode", isDark.value);
}
</script>

<template>
    <body :class="{ dark: isDark }" class="flex flex-col bg-[#363d4d] h-screen ">
        <div class="grid grid-cols-3 bg-[#303844] justify-items-center">
            <div class="flex flex-col pl-5">
                <div class="flex flex-row">
                    <div>
                        <img class="w-auto h-8 pr-2 m-4 print:hidden " alt="ubicacion" src="/images/instituciones.png" />
                    </div>
                    <div>
                        <div class="my-4 print:hidden">
                            <span class="invisible text-gray-400 uppercase sm:visible hover:text-white">Instituciones</span>
                        </div>
                    </div>
                </div>
                <div class="flex flex-row">
                    <div>
                        <img class="w-auto h-8 pr-2 m-4 print:hidden" alt="ubicacion" src="/images/portal.png" />
                    </div>
                    <div>
                        <div class="my-4 print:hidden">
                            <span class="invisible text-gray-400 sm:visible hover:text-white">Portal de
                                <br />Transparencia</span>
                        </div>
                    </div>
                </div>
            </div>
            <div>
                <a href="/">
                    <img class="items-center w-auto h-32 py-2 align-middle print:hidden" src="/images/logo.png"
                        alt="Banco Central de Reserva de El Salvador" />
                    <img class="w-auto h-32 pr-2 m-4 hidden print:block" alt="ubicacion " src="/images/logodark.png" />
                </a>

            </div>
            <div>
                <template v-if="isDark">
                    <button class="pt-5 text-white print:hidden" @click="toggleDarkMode">
                        <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                            class="w-6 h-6 mr-2 print:hidden">
                            <path d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" class="fill-sky-400/20 stroke-sky-500"></path>
                            <path
                                d="M12 4v1M17.66 6.344l-.828.828M20.005 12.004h-1M17.66 17.664l-.828-.828M12 20.01V19M6.34 17.664l.835-.836M3.995 12.004h1.01M6 6l.835.836"
                                class="stroke-sky-500"></path>
                        </svg>
                    </button>
                </template>
                <template v-else>
                    <button class="pt-5 text-white print:hidden" @click="toggleDarkMode">
                        <svg viewBox="0 0 24 24" fill="none" class="w-6 h-6 mr-2">
                            <path fill-rule="evenodd" clip-rule="evenodd"
                                d="M17.715 15.15A6.5 6.5 0 0 1 9 6.035C6.106 6.922 4 9.645 4 12.867c0 3.94 3.153 7.136 7.042 7.136 3.101 0 5.734-2.032 6.673-4.853Z"
                                class="fill-transparent"></path>
                            <path
                                d="m17.715 15.15.95.316a1 1 0 0 0-1.445-1.185l.495.869ZM9 6.035l.846.534a1 1 0 0 0-1.14-1.49L9 6.035Zm8.221 8.246a5.47 5.47 0 0 1-2.72.718v2a7.47 7.47 0 0 0 3.71-.98l-.99-1.738Zm-2.72.718A5.5 5.5 0 0 1 9 9.5H7a7.5 7.5 0 0 0 7.5 7.5v-2ZM9 9.5c0-1.079.31-2.082.845-2.93L8.153 5.5A7.47 7.47 0 0 0 7 9.5h2Zm-4 3.368C5 10.089 6.815 7.75 9.292 6.99L8.706 5.08C5.397 6.094 3 9.201 3 12.867h2Zm6.042 6.136C7.718 19.003 5 16.268 5 12.867H3c0 4.48 3.588 8.136 8.042 8.136v-2Zm5.725-4.17c-.81 2.433-3.074 4.17-5.725 4.17v2c3.552 0 6.553-2.327 7.622-5.537l-1.897-.632Z"
                                class="fill-slate-400 dark:fill-slate-500"></path>
                            <path fill-rule="evenodd" clip-rule="evenodd"
                                d="M17 3a1 1 0 0 1 1 1 2 2 0 0 0 2 2 1 1 0 1 1 0 2 2 2 0 0 0-2 2 1 1 0 1 1-2 0 2 2 0 0 0-2-2 1 1 0 1 1 0-2 2 2 0 0 0 2-2 1 1 0 0 1 1-1Z"
                                class="fill-slate-400 dark:fill-slate-500"></path>
                        </svg>
                    </button>
                </template>
            </div>
        </div>

        <div class="flex-grow font-sans antialiased text-gray-900" :key="$page.url">
            <slot />
        </div>
        <footer class="w-full print:hidden text-gray-300 bg-[#303844] dark:bg-[#303844] xl:inset-x-0 xl:bottom-0">
            <div class="grid grid-cols-1 py-5 md:grid-cols-3">
                <div class="flex flex-col py-8 md:py-0">
                    <div class="flex flex-row">
                        <div>
                            <img class="w-auto h-8 pr-2 m-4" alt="ubicacion" src="/images/ubicacion.svg" />
                        </div>
                        <div>
                            <div class="my-4">
                                Banco Central De Reserva <br />
                                Alameda Juan Pablo II, entre 15 y 17 Av.
                                Norte.<br />
                                Apartado Postal (106), San Salvador, El
                                Salvador.
                                <br />
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-row">
                        <div>
                            <img class="w-auto h-8 pr-2 m-4" alt="teléfono" src="/images/telefono.svg" />
                        </div>
                        <div>
                            <div class="my-4">(503) 2281-8000</div>
                        </div>
                    </div>

                    <div class="flex flex-row">
                        <div>
                            <img class="w-10 h-8 pr-2 m-4" alt="correo" src="/images/correo.svg" />
                        </div>
                        <div>
                            <div class="my-4">
                                <a href="mailto:info@bcr.gob.sv">
                                    info@bcr.gob.sv
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="order-last md:order-none">
                    <img alt="Banco Central de Reserva de El Salvador" class="w-32 h-32 mx-auto"
                        src="/images/logo_gobierno.svg" />
                </div>
                <div class="order-first py-8 md:py-0 md:order-last">
                    <div class="text-center">Únete a nuestra comunidad</div>
                    <div class="flex flex-row justify-center gap-4 pt-4">
                        <div>
                            <a href="https://www.facebook.com/bcr.sv" target="_blank">
                                <img alt="Redes Sociales" class="w-8 h-8" src="/images/facebook.svg" />
                            </a>
                        </div>
                        <div>
                            <a href="https://twitter.com/bcr_sv" target="_blank">
                                <img alt="Redes Sociales" class="w-8 h-8" src="/images/twitter.svg" />
                            </a>
                        </div>
                        <div>
                            <a href="https://www.youtube.com/user/bancocentralsv" target="_blank">
                                <img alt="Redes Sociales" class="w-8 h-8" src="/images/youtube.svg" />
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="p-5">
                <p class="text-center dark:text-white">
                    © Banco Central de Reserva de El Salvador
                </p>
            </div>
        </footer>
    </body>
</template>
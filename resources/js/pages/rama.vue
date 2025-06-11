<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { onBeforeUnmount, onMounted, reactive, watch } from 'vue';
// import {computed, onMounted, reactive, ref, watch} from 'vue';
import { formatearFecha } from '@/global';
import pkg from 'lodash';
// import PlaceholderPattern from '../components/PlaceholderPattern.vue';
import {Head, router} from '@inertiajs/vue3';

const { debounce, pickBy } = pkg;

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
];

const props = defineProps({
    title: String,
    Numprocesos: Number,
    filters: Object,
    proceso: {
        type: Array,
        // eslint-disable-next-line vue/require-valid-default-prop
        default: [],
    },
});
const data = reactive({
    params: {
        search: props.filters?.search,
    },
    sujetos: Array,
});

// <!--<editor-fold desc="mounted handleclipboard">-->
onMounted(() => {
    handleClipboardRead(); // Al montar
    window.addEventListener('focus', onFocusOrVisible);
    document.addEventListener('visibilitychange', onFocusOrVisible);
});

onBeforeUnmount(() => {
    window.removeEventListener('focus', onFocusOrVisible);
    document.removeEventListener('visibilitychange', onFocusOrVisible);
});

const handleClipboardRead = async () => {
    if (document.visibilityState !== 'visible') return;

    try {
        const clip = await navigator.clipboard.readText();
        if(clip === null || clip === undefined) {
            console.warn('🚫 Portapapeles vacío o no accesible');
            return;
        }
        const isnumericou = parseInt(clip).isInteger()
        if (clip && clip.length > 0 && isnumericou) {
            data.params.search = clip;
        }
    } catch (e) {
        console.warn('🚫 Error leyendo portapapeles:', e);
        // alert('🚫 Error leyendo portapapeles:', e);
        // Limpiar solo si el portapapeles falla y quieres dejarlo vacío
        // data.params.search = '';
    }
};

const onFocusOrVisible = () => handleClipboardRead();

// <!--</editor-fold>-->


watch(() => data.params.search, debounce((newSearch:any, oldSearch:any) => {
    if (newSearch === oldSearch) {
      // No ha cambiado, no hacer nada
      return;
    }
    if (!newSearch || newSearch === '') return;
    
    const numbernewSearch = parseInt(newSearch);
    
    if( isNaN(numbernewSearch) || numbernewSearch < 0) return;
    if(!esRadicacionValida(newSearch)) return
    
    const params = pickBy(data.params);
    router.get(route('rama'), params, {
      replace: true,
      preserveState: true,
      preserveScroll: true,
        onFinish: () => {
            data.sujetos = props.proceso?.sujetosProcesales?.split('|');
        }
    });
  }, 475)
)
function esRadicacionValida(value: any): boolean {
  // Debe tener exactamente 23 caracteres y todos ser dígitos
  return typeof value === 'string'
    && value.length === 23
    && /^[0-9]{23}$/.test(value);
}
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="grid auto-rows-min gap-4 md:grid-cols-2">
                <div
                    class="border-sidebar-border/70 dark:border-sidebar-border relative mx-2 w-full mt-10 overflow-auto rounded-xl border"
                >
                    <input
                        type="text"
                        v-model="data.params.search"
                        class="w-full rounded border px-1 py-2 focus:ring focus:outline-none"
                        placeholder="Número de radicación"
                    />
                </div>
                <!--                <div class="border-sidebar-border/70 dark:border-sidebar-border relative aspect-video overflow-hidden rounded-xl border">-->
                <!--                    <PlaceholderPattern />-->
                <!--                </div>-->
                <!--                <div class="border-sidebar-border/70 dark:border-sidebar-border relative aspect-video overflow-hidden rounded-xl border">-->
                <!--                    <PlaceholderPattern />-->
                <!--                </div>-->
                <!--                <div class="border-sidebar-border/70 dark:border-sidebar-border relative aspect-video overflow-hidden rounded-xl border">-->
                <!--                    <PlaceholderPattern />-->
                <!--                </div>-->
            </div>
            <div class="border-sidebar-border/70 dark:border-sidebar-border relative min-h-[100vh] flex-1 rounded-xl border md:min-h-min">
                <div class="mx-auto w-full max-w-xl rounded-lg bg-white p-8 shadow-lg dark:bg-black">
                    <h1 v-if="proceso.validacioncini" class="mb-6 text-center text-2xl font-bold text-gray-800 dark:text-white">
                        Detalle del Proceso Judicial
                    </h1>
                    <div class="space-y-4">
                        <div class="flex justify-between">
                            <span class="font-semibold text-gray-600 dark:text-white">Radicación:</span>
                            <span class="text-gray-900 dark:text-white">{{ props.proceso?.llaveProceso }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="font-semibold text-gray-600 dark:text-white">Fecha de inicio:</span>
                            <span class="text-gray-900 dark:text-white">{{ formatearFecha(props.proceso.fechaProceso) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="font-semibold text-gray-600 dark:text-white">Última actuación:</span>
                            <span class="text-gray-900 dark:text-white">{{ formatearFecha(props.proceso.fechaUltimaActuacion) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="font-semibold text-gray-600 dark:text-white">Despacho:</span>
                            <span class="text-right text-gray-900 dark:text-white">{{ props.proceso.despacho }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="font-semibold text-gray-600 dark:text-white">Departamento:</span>
                            <span class="text-gray-900 dark:text-white">{{ props.proceso.departamento }}</span>
                        </div>
                        <div class="flex flex-col">
                            <span class="mb-1 font-semibold text-gray-600 dark:text-white">Sujetos Procesales:</span>
                            <div class="rounded p-2 text-sm leading-relaxed text-gray-800 dark:text-white">
                                <div v-for="(sujeto, i) in data.sujetos" :key="i" class="bg-gray-50 text-black dark:bg-black dark:text-white">
                                    • {{ sujeto.trim() }}
                                </div>
                            </div>
                        </div>
                        <div v-if="proceso.validacioncini" class="flex justify-center">
                            <span class="font-semibold text-gray-600 dark:text-white">¿Es privado?</span>
                            <span class="text-gray-900 dark:text-white">
                                {{ props.proceso.esPrivado ? '✅' : '❌' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

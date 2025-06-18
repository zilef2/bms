<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import DataExplicada from '@/pages/DataExplicada.vue';
import DetalleProceso from '@/pages/DetalleProceso.vue';
import DetalleActuacion from '@/pages/DetalleActuacion.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import pkg from 'lodash';
import { PropType, onBeforeUnmount, onMounted, reactive, watch } from 'vue';
import { router } from '@inertiajs/vue3';

const { debounce, pickBy } = pkg;

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
];

// Define la interfaz para la estructura del objeto 'proceso'
interface ProcesoDetalle {
    validacioncini?: boolean; // Ajusta el tipo si no es boolean (ej. string '1'/'0')
    esPrivado?: string; // Asumo que es '1' o '0' como string
    sujetosProcesales?: string;
    llaveProceso?: string;
    fechaProceso?: string;
    fechaUltimaActuacion?: string;
    despacho?: string;
    departamento?: string;
}

interface ProcesoDataType {
    idRegProceso: number;
    llaveProceso: string;
    idConexion: number;
    esPrivado: boolean;
    fechaProceso: string;
    codDespachoCompleto: string;
    despacho: string;
    ponente: string;
    tipoProceso: string;
    claseProceso: string;
    subclaseProceso: string;
    recurso: string;
    ubicacion: string | null;
    contenidoRadicacion: string | null;
    fechaConsulta: string;
    ultimaActualizacion: string;
}

const props = defineProps({
    title: String,
    Numprocesos: Number,
    UltimaActuacion: String,
    filters: Object,
    // ¡Aquí está el cambio clave!
    proceso: {
        type: Object as PropType<ProcesoDetalle | null>, // Indicas que es un Objeto o null
        default: null, // El valor por defecto es null, no un array vacío
    },
    obtenerDetalleProceso: {
        type: Object as PropType<ProcesoDataType | null>, // Indicas que es un Objeto o null
        default: null, // El valor por defecto es null, no un array vacío
    },
    Actuaciones: {
        type: Object as PropType<ProcesoDataType | null>, // Indicas que es un Objeto o null
        default: null, // El valor por defecto es null, no un array vacío
    },
});

const data = reactive({
    params: {
        search: props.filters?.search,
    },
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
        const isnumericou = Number.isInteger(parseInt(clip));
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
                <div class="border-sidebar-border/70 dark:border-sidebar-border relative mx-2 mt-10 w-full overflow-auto rounded-xl border">
                    <input
                        type="text"
                        v-model="data.params.search"
                        class="w-full rounded border px-1 py-2 focus:ring focus:outline-none"
                        placeholder="Número de radicación"
                    />
                </div>
            </div>
            <div class="border-sidebar-border/70 dark:border-sidebar-border relative min-h-[100vh] flex-1 rounded-xl border md:min-h-min">
                <div v-if="props.proceso?.validacioncini"
                    class="xs:max-w-[500px] mx-auto w-full rounded-2xl bg-white p-8 hover:shadow-lg lg:max-w-[700px] 2xl:max-w-[900px] dark:bg-black"
                >
                    <h1 class="relative mb-8 overflow-hidden text-center text-4xl font-extrabold text-blue-600 dark:text-blue-400">
                        Detalles del Proceso Judicial
                        <span
                            class="via-opacity-30 animate-shine pointer-events-none absolute top-0 left-0 h-full w-full bg-gradient-to-r from-transparent via-white to-transparent"
                        ></span>
                    </h1>
                    <div class="space-y-4">
                        <DataExplicada v-if="props.proceso" :proceso="props.proceso" />

                        <div v-if="props.proceso?.validacioncini" class="flex justify-center">
                            <span class="font-semibold text-gray-600 dark:text-white">¿Es privado?</span>
                            <span class="text-gray-900 dark:text-white">
                                {{ props.proceso?.esPrivado == '1' ? '✅' : '❌' }}
                            </span>
                        </div>
                        <div v-if="Array.isArray(props.proceso) && props.proceso.length > 1" class="flex justify-center">
                            Hay mas de un proceso ( {{ props.proceso?.length }} )
                        </div>
<!--asdasdasd {{props.Actuaciones}}-->
                        <DetalleActuacion v-if="props.proceso?.validacioncini && props.Actuaciones" :actuaciones="props.Actuaciones" />
                        
                        <DetalleProceso 
                            v-if="props.proceso?.validacioncini && props.obtenerDetalleProceso" 
                            :proceso="props.obtenerDetalleProceso" 
                            :UltimaActuacion="UltimaActuacion"
                        />
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
/* Define la animación de brillo para Tailwind */
@keyframes shine {
  0% {
    transform: translateX(-100%) skewX(-20deg);
  }
  50% {
    transform: translateX(200%) skewX(-20deg);
  }
  100% {
    transform: translateX(-100%) skewX(-20deg);
  }
}

/* Agrega la animación a Tailwind */
.animate-shine {
  animation: shine 3s infinite ease-in-out;
  animation-delay: 1s;
}
</style>

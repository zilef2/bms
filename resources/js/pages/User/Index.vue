<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import Breadcrumb from '@/Components/Breadcrumb.vue';
import TextInput from '@/Components/TextInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import InfoButton from '@/Components/InfoButton.vue';
import SelectInput from '@/Components/SelectInput.vue';
import { reactive, watch, watchEffect } from 'vue';
import DangerButton from '@/Components/DangerButton.vue';
import pkg from 'lodash';
import Pagination from '@/Components/Pagination.vue';
import { CheckBadgeIcon, ChevronUpDownIcon, PencilIcon, TrashIcon, UserCircleIcon } from '@heroicons/vue/24/solid';
import Create from '@/pages/User/Create.vue';
import Edit from '@/pages/User/Edit.vue';
import Delete from '@/pages/User/Delete.vue';
import DeleteBulk from '@/pages/User/DeleteBulk.vue';
import Checkbox from '@/Components/Checkbox.vue';
import { router, usePage, useForm, Link } from '@inertiajs/vue3';

import { number_format, formatDate, CalcularEdad, CalcularSexo } from '@/global.ts';

const { _, debounce, pickBy } = pkg
const props = defineProps({
    title: String,
    filters: Object,
    users: Object,
    roles: Object,
    breadcrumbs: Object,
    perPage: Number,
    numberPermissions: Number,
})
const data = reactive({
    params: {
        search: props.filters.search,
        field: props.filters.field,
        order: props.filters.order,
        perPage: props.perPage,
    },
    selectedId: [],
    multipleSelect: false,
    createOpen: false,
    editOpen: false,
    deleteOpen: false,
    deleteBulkOpen: false,
    user: null,
    ArchivoNombre: '',
    dataSet: usePage().props.app.perpage
})

const order = (field) => {
    data.params.field = field
    console.log("🧈 debu data.params.field:", data.params.field);
    data.params.order = data.params.order === "asc" ? "desc" : "asc"
    console.log("🧈 debu data.params.order:", data.params.order);
}

watch(() => _.cloneDeep(data.params), debounce(() => {
    let params = pickBy(data.params)
    router.get(route("user.index"), params, {
        replace: true,
        preserveState: true,
        preserveScroll: true,
    })
}, 150))

const selectAll = (event) => {
    if (event.target.checked === false) {
        data.selectedId = []
    } else {
        props.users?.data.forEach((user) => {
            data.selectedId.push(user.id)
        })
    }
}
const select = () => {
    if (props.users?.data.length == data.selectedId.length) {
        data.multipleSelect = true
    } else {
        data.multipleSelect = false
    }
}


const form = useForm({
    archivo1: '',
})

watchEffect(() => {
    data.ArchivoNombre = form.archivo1?.name
})

// text // number // dinero // date // datetime // foreign
const titulos = [
    { order: 'name', label: 'Nombre', type: 'text',required: true },
    { order: 'email', label: 'Email', type: 'text' ,required: true},
    { order: 'identificacion', label: 'Identificacion', type: 'text' ,required: true},
    // { order: 'sexo', label: 'Sexo', type: 'foreign' },
    { order: 'area', label: 'area', type: 'text',required: true},
    { order: 'cargo', label: 'cargo', type: 'text',required: true},
    { order: 'sexo', label: 'sexo', type: 'foreign', nameid: 'sexo_S' ,required: false},
    { order: 'celular', label: 'celular', type: 'text',required: false},
    { order: 'fecha_nacimiento', label: 'Fecha nacimiento', type: 'date' ,required: false},

];
</script>

<template>
    <Head :title="props.title" />

    <AuthenticatedLayout>
        <Breadcrumb :title="title" :breadcrumbs="breadcrumbs" />
        <div class="space-y-4">
            <div class="px-4 sm:px-0">
                <div class="rounded-lg overflow-hidden w-fit">
                    <PrimaryButton v-show="can(['create user'])" class="rounded-none" @click="data.createOpen = true">
                        {{ lang().button.add }}
                    </PrimaryButton>
                    <Create :show="data.createOpen" @close="data.createOpen = false" :roles="props.roles"
                        v-if="can(['create user'])" :title="props.title" :titulos="titulos"/>
                    <Edit :show="data.editOpen" @close="data.editOpen = false" :user="data.user" :roles="props.roles"
                        v-if="can(['update user'])" :title="props.title" :titulos="titulos" />
                    <Delete :show="data.deleteOpen" @close="data.deleteOpen = false" :user="data.user"
                        :title="props.title" />
                    <DeleteBulk :show="data.deleteBulkOpen"
                        @close="data.deleteBulkOpen = false, data.multipleSelect = false, data.selectedId = []"
                        :selectedId="data.selectedId" :title="props.title" />
                </div>
            </div>
            <div class="relative bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <div class="flex justify-between p-2">
                    <div class="flex space-x-2">
                        <SelectInput v-model="data.params.perPage" :dataSet="data.dataSet" />
                        <DangerButton @click="data.deleteBulkOpen = true"
                            v-show="data.selectedId.length != 0 && can(['delete user'])" class="px-3 py-1.5"
                            v-tooltip="lang().tooltip.delete_selected">
                            <TrashIcon class="w-5 h-5" />
                        </DangerButton>
                    </div>
                    <TextInput v-if="props.numberPermissions > 1" v-model="data.params.search" type="text"
                        class="block w-4/6 md:w-3/6 lg:w-2/6 rounded-lg" placeholder="Nombre, correo o identificación" />
                </div>
                <div class="overflow-x-auto scrollbar-table">
                    <table class="w-full">
                        <thead class="uppercase text-sm border-t border-gray-200 dark:border-gray-700">
                            <tr class="dark:bg-gray-900/50 text-left">
                                <th class="px-2 py-4 text-center">
                                    <Checkbox v-model:checked="data.multipleSelect" @change="selectAll" />
                                </th>
                                <th class="px-2 py-4 text-center">#</th>
                                <th class="px-2 py-4 cursor-pointer" v-on:click="order('name')">
                                    <div class="flex justify-between items-center">
                                        <span>{{ lang().label.name }}</span>
                                        <ChevronUpDownIcon class="w-4 h-4" />
                                    </div>
                                </th>
                                <!-- <th class="px-2 py-4 cursor-pointer" v-on:click="order('email')">
                                    <div class="flex justify-between items-center"> <span>{{ lang().label.email }}</span> <ChevronUpDownIcon class="w-4 h-4" /> </div>
                                </th> -->
                                <th class="px-2 py-4">{{ lang().label.role }}</th>

                                <th class="px-2 py-4 cursor-pointer" v-on:click="order('identificacion')">
                                    <div class="flex justify-between items-center"> <span>{{ lang().label.identificacion
                                                                                }}</span>
                                        <ChevronUpDownIcon class="w-4 h-4" />
                                    </div>
                                </th>
                                <th class="px-2 py-4 cursor-pointer" v-on:click="order('sexo')">
                                    <div class="flex justify-between items-center"> <span>{{ lang().label.sexo }}</span>
                                        <ChevronUpDownIcon class="w-4 h-4" />
                                    </div>
                                </th>
                                <th class="px-2 py-4 cursor-pointer" v-on:click="order('fecha_nacimiento')">
                                    <div class="flex justify-between items-center"> <span>{{ lang().label.edad }}</span>
                                        <ChevronUpDownIcon class="w-4 h-4" />
                                    </div>
                                </th>
                                <th class="px-2 py-4 cursor-pointer" v-on:click="order('celular')">
                                    <div class="flex justify-between items-center"> <span>{{ lang().label.celular }}</span>
                                        <ChevronUpDownIcon class="w-4 h-4" />
                                    </div>
                                </th>
                                <th class="px-2 py-4 cursor-pointer" v-on:click="order('area')">
                                    <div class="flex justify-between items-center"> <span>{{ lang().label.area }}</span>
                                        <ChevronUpDownIcon class="w-4 h-4" />
                                    </div>
                                </th>
                                <th class="px-2 py-4 cursor-pointer" v-on:click="order('cargo')">
                                    <div class="flex justify-between items-center"> <span>{{ lang().label.cargo }}</span>
                                        <ChevronUpDownIcon class="w-4 h-4" />
                                    </div>
                                </th>

                                <th class="px-2 py-4">Accion</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(user, index) in users.data" :key="index"
                                class="border-t border-gray-200 dark:border-gray-700 hover:bg-gray-200/30 hover:dark:bg-gray-900/20">
                                <td class="whitespace-nowrap py-4 px-2 sm:py-3 text-center">
                                    <input
                                        class="rounded dark:bg-gray-900 border-gray-300 dark:border-gray-700 text-primary dark:text-primary shadow-sm focus:ring-primary/80 dark:focus:ring-primary dark:focus:ring-offset-gray-800 dark:checked:bg-primary dark:checked:border-primary"
                                        type="checkbox" @change="select" :value="user.id" v-model="data.selectedId" />
                                </td>
                                <td class="whitespace-nowrap py-4 px-2 sm:py-3 text-center">{{ ++index }}</td>
                                <td class="whitespace-nowrap py-4 px-2 sm:py-3">
                                    <span class="flex justify-start items-center">
                                        {{ user.name }} --
                                        <small v-if="props.numberPermissions > 9">
                                            {{ user.id}}
                                        </small>
                                        
                                        <CheckBadgeIcon class="ml-[2px] w-4 h-4 text-primary dark:text-white"
                                            v-show="user.email_verified_at" />
                                    </span>
                                    <span class="flex justify-start items-center text-sm text-gray-600">
                                        {{ user.email }}
                                    </span>

                                </td>
                                <td class="whitespace-nowrap py-4 px-2 sm:py-3">
                                    {{ user.roles.length == 0 ? 'not selected' : user.roles[0].name }}
                                </td>
                                <!-- <td class="whitespace-nowrap py-4 px-2 sm:py-3">{{ user.created_at }}</td>
                                <td class="whitespace-nowrap py-4 px-2 sm:py-3">{{ user.updated_at }}</td> -->
                                <td class="whitespace-nowrap py-4 px-2 sm:py-3">{{ user.identificacion }}</td>
                                <td class="whitespace-nowrap py-4 px-2 sm:py-3">{{ user.sexo }}</td>
                                <td class="whitespace-nowrap text-center py-4 px-2 sm:py-3">{{ CalcularEdad(user.fecha_nacimiento) }}</td>
                                <td class="whitespace-nowrap py-4 px-2 sm:py-3">{{ user.celular }}</td>
                                <td class="whitespace-nowrap py-4 px-2 sm:py-3">{{ user.area }}</td>
                                <td class="whitespace-nowrap py-4 px-2 sm:py-3">{{ user.cargo }}</td>
                                <td class="whitespace-nowrap py-4 px-2 sm:py-3">
                                    <div class="flex justify-center items-center">
                                        <div class="rounded-md overflow-hidden">
                                            <InfoButton v-show="can(['update user'])" type="button"
                                                @click="(data.editOpen = true), (data.user = user)"
                                                class="px-2 py-1.5 rounded-none" v-tooltip="lang().tooltip.edit">
                                                <PencilIcon class="w-4 h-4" />
                                            </InfoButton>
                                            <DangerButton v-show="can(['delete user'])" type="button"
                                                @click="(data.deleteOpen = true), (data.user = user)"
                                                class="px-2 py-1.5 rounded-none" v-tooltip="lang().tooltip.delete">
                                                <TrashIcon class="w-4 h-4" />
                                            </DangerButton>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="flex justify-between items-center p-2 border-t border-gray-200 dark:border-gray-700">
                    <Pagination :links="props.users" :filters="data.params" />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

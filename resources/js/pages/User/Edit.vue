<script setup lang="ts">
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import Modal from '@/Components/Modal.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import SelectInput from '@/Components/SelectInput.vue';
import TextInput from '@/Components/TextInput.vue';
import { useForm } from '@inertiajs/vue3';
import { watchEffect, onMounted, reactive } from 'vue';
import VueDatePicker from '@vuepic/vue-datepicker'; import '@vuepic/vue-datepicker/dist/main.css'
import vSelect from "vue-select"; import "vue-select/dist/vue-select.css";

const props = defineProps({
    show: Boolean,
    title: String,
    user: Object,
    titulos: Object, //parametros de la clase principal
    roles: Object,
})

const emit = defineEmits(["close"]);
const data = reactive({
    params: {
        pregunta: ''
    },
    onceTime:true,
    sexo:[ { title: 'Masculino', value: 'Masculino' }, { title: 'Femenino', value: 'Femenino' } ]
})
//very usefull
const justNames = props.titulos.map(names => names['order'] )
let form = useForm({ ...Object.fromEntries(justNames.map(field => [field, ''])) ,
    role:''
});

const printForm =[];
props.titulos.forEach(names => printForm.push ({
        idd: names['order'], label: names['label'], type: names['type']
    })
);

const update = () => {
    form.put(route('user.update', props.user?.id), {
        preserveScroll: true,
        onSuccess: () => {
            emit("close")
            form.reset()
        },
        onError: () => null,
        onFinish: () => null,
    })
}

watchEffect(() => {
    if (props.show) {
        form.errors = {}
        // if(data.onceTime){

            form.name = props.user?.name
            form.email = props.user?.email
            form.role = props.user?.roles == 0 ? '' : props.user?.roles[0].name

            form.identificacion = props.user?.identificacion
            form.sexo = props.user?.sexo
            form.fecha_nacimiento = props.user?.fecha_nacimiento
            form.celular = props.user?.celular
            form.area = props.user?.area
            form.cargo = props.user?.cargo
            data.onceTime = false
        // }
    }else{
        form.name = null
        form.email = null
        form.role = null

        form.identificacion = null
        form.sexo = null
        form.fecha_nacimiento = null
        form.celular = null
        form.area = null
        form.cargo = null
    }
})

onMounted(() => { });

const roles = props.roles?.map(role => ({
    label: role.name.replace(/_/g," "),
    value: (role.name)
}))

const flow = ['year', 'month', 'calendar'];
</script>

<template>
    <section class="space-y-6">
        <Modal :show="props.show" @close="emit('close')">
            <form class="p-6 mb-12" @submit.prevent="update" >
                <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">
                    {{ lang().label.edit }} {{ props.title }}
                </h2>
                <h3 class="font-medium text-gray-900 dark:text-gray-100 mb-2">
                    {{ lang().label.RequiredFields }}
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div v-for="(atributosform, indice) in printForm" :key="indice">

                        <!-- si es foreign -->
                        <div v-if="atributosform.type === 'foreign'" id="SelectVue" class="mt-2">
                            <div class="inline-flex">
                            <label name="labelSelectVue"> {{atributosform.label}} </label>
                            <small class="text-lg ml-1 font-bold"> {{ titulos[indice].required ? '*':'ㅤ' }} </small>
                          </div>
                            <v-select :options="data[atributosform.idd]" label="title" class="text-lg h-8"
                                v-model="form[atributosform.idd]"></v-select>
                            <InputError class="mt-2" :message="form.errors[atributosform.idd]" />
                        </div>


                        <!-- tiempo -->
                        <div v-else-if="atributosform.type ==='time'" id="SelectVue">
                            <div class="inline-flex">
                              <InputLabel :for="atributosform.label" :value="lang().label[atributosform.label]" />
                              <small class="text-lg ml-1 font-bold"> {{ titulos[indice].required ? '*':'ㅤ' }} </small>
                            </div>
                            <TextInput :id="atributosform.idd" :type="atributosform.type" class="mt-1 block w-full"
                                v-model="form[atributosform.idd]" required :placeholder="atributosform.label"
                                :error="form.errors[atributosform.idd]" step="3600" />
                            <InputError class="mt-2" :message="form.errors[atributosform.idd]" />
                        </div>
                        <div v-else-if="atributosform.type === 'date'" id="SelectVue">
                            <div class="inline-flex">
                              <InputLabel :for="atributosform.label" :value="lang().label[atributosform.label]" />
                              <small class="text-lg ml-1 font-bold"> {{ titulos[indice].required ? '*':'ㅤ' }} </small>
                            </div>
                            <VueDatePicker :is-24="false" :day-names="daynames" :format="formatToVue" :flow="flow" auto-apply
                                :enable-time-picker="false" :id="atributosform.idd"
                                class="mt-1 block w-full" v-model="form[atributosform.idd]" required :placeholder="atributosform.label"
                                :error="form.errors[atributosform.idd]"/>
                            <InputError class="mt-2" :message="form.errors[atributosform.idd]" />
                        </div>


                        <!-- normal -->
                        <div v-else class="">
                          <div class="inline-flex">
                            <InputLabel :for="atributosform.label" :value="lang().label[atributosform.label]" />
                            <small class="text-lg ml-1 font-bold"> {{ titulos[indice].required ? '*':'ㅤ' }} </small>
                          </div>
                            <TextInput :id="atributosform.idd" :type="atributosform.type" class="mt-1 block w-full"
                                v-model="form[atributosform.idd]" required :placeholder="atributosform.label"
                                :error="form.errors[atributosform.idd]" />
                            <InputError class="mt-2" :message="form.errors[atributosform.idd]" />
                        </div>
                    </div>

                    <div>
                      <div class="inline-flex">
                        <InputLabel for="role" :value="lang().label.role" /><small class="text-lg ml-1 font-bold">  * </small>
                      </div>
                        <SelectInput id="role" class="mt-1 block w-full" v-model="form.role" required :dataSet="roles">
                        </SelectInput>
                        <InputError class="mt-2" :message="form.errors.role" />
                    </div>

                </div>



                    <!-- limite_token_general -->


                    <!-- pass -->
                    <!-- <div>
                        <InputLabel for="password" :value="lang().label.password" />
                        <TextInput id="password" type="password" class="mt-1 block w-full" v-model="form.password"
                            :placeholder="lang().placeholder.password" :error="form.errors.password" />
                        <InputError class="mt-2" :message="form.errors.password" />
                    </div>
                    <div>
                        <InputLabel for="password_confirmation" :value="lang().label.password_confirmation" />
                        <TextInput id="password_confirmation" type="password" class="mt-1 block w-full"
                            v-model="form.password_confirmation" :placeholder="lang().placeholder.password_confirmation"
                            :error="form.errors.password_confirmation" />
                        <InputError class="mt-2" :message="form.errors.password_confirmation" />
                    </div> -->
                <div class="flex justify-end my-8">
                    <SecondaryButton :disabled="form.processing" @click="emit('close')"> {{ lang().button.close }}
                    </SecondaryButton>
                    <PrimaryButton class="ml-3" :class="{ 'opacity-25': form.processing }" :disabled="form.processing"
                        @click="update">
                        {{ form.processing ? lang().button.save + '...' : lang().button.save }}
                    </PrimaryButton>
                </div>
            </form>
        </Modal>
    </section>
</template>

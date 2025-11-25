<script setup>
import { FilterMatchMode } from '@primevue/core/api';
import data_nutrition from '~/assets/data/nutrition.json';
import NoData from '~/public/images/no-data.svg';

const { enccode, openPatientDetailsConfirmation } = usePatient();

const diet_list_store = useDietListStore();
const { s_diet_list } = storeToRefs(diet_list_store);
const { error, status } = inject('diet_list');

const patients = ref([]);
const wards = ref([]);

const age_grouped_patients = ref({});
const age_groups = ref(['Adult', 'Pedia']);
const age_group = ref('Adult');

const questions = ref(data_nutrition.questions[age_group.value]);

const visible = ref(false);

const filters = ref();

const initFilters = () => {
    filters.value = {
        wardname: { value: null, matchMode: FilterMatchMode.EQUALS },
        patname: { value: null, matchMode: FilterMatchMode.CONTAINS },
        question1: { value: null, matchMode: FilterMatchMode.EQUALS },
        question2: { value: null, matchMode: FilterMatchMode.EQUALS },
        question3: { value: null, matchMode: FilterMatchMode.EQUALS },
        question4: { value: null, matchMode: FilterMatchMode.EQUALS }
    };
};


//  Fetch patients
async function getPatients(){

    const patients_concatinated = Object.values(s_diet_list.value)?.flat();
    const unique_patients = patients_concatinated.filter((patient, index, self) => { return index === self.findIndex(p => patient.hpercode === p.hpercode) });
    
    age_grouped_patients.value = Object.groupBy(unique_patients, checkAge) ?? {};
    
    changeAgeGroup('Adult');
}

//  Categorizes patients into adult and pediatric groups 
function checkAge({age, agedesc}){
    return age > 18 && agedesc === 'y/o' ? 'ADULT' : 'PEDIA';
}

//  Show patients of selected age group
function changeAgeGroup(_age_group){
    initFilters();
    
    age_group.value = _age_group;
    
    const age_patients = age_grouped_patients.value?.[age_group.value.toUpperCase()] ?? [];

    patients.value = age_patients?.filter(patient => patient.nar === 'YES');
    wards.value = patients.value?.length > 0 ? Object.keys(Object.groupBy(patients.value, ({wardname}) => wardname)).sort() : [];
    questions.value = data_nutrition.questions[age_group.value];
}


//  init
watch(
    () => s_diet_list.value,
    (val) => {
        if(val && Object.keys(val).length > 0) {
            getPatients();
        }
    },
    { immediate: true }
);
</script>

<template>
    <ConfirmPopup group="headless" class="!border-2 !border-primary-300 !left-1/2 !-translate-x-1/2">
        <template #container>
            <div class="rounded-md w-20 p-0">
                <Button text label="Open" class="w-full" pt:label:class="font-bold text-xl text-primary-300" @click="visible = true"></Button>
            </div>
        </template>
    </ConfirmPopup>

    <Dialog 
        modal
        v-model:visible="visible" 
        :dismissableMask=false
        :draggable=false
        header="&nbsp"
        pt:root:class="!w-full md:!w-11/12 !h-max !bg-[--surface-ground] !border-primary !border-2 !rounded-md sm:!text-sm md:!text-base lg:!text-lg" 
        pt:header:class="!py-1"
        pt:mask:class="!backdrop-blur-sm" 
    >
        <Patient :enccode="enccode" />
    </Dialog>
    
    <div class="bg-[--surface-card] p-3 rounded-md">
        <ViewTemplate :error="error" :status="status">
            <div class="container mx-auto sm:text-sm md:text-base lg:text-lg">
                <!-- Diet type buttons -->
                <div class="container mx-auto w-full px-0 font-bold border-b flex">
                    <span v-for="group in age_groups">
                        <Button text :label="group" :class="['!py-0 !px-6 !sm:text-sm !md:text-base !lg:text-lg !text-primary !rounded-none', {'!border-b-primary': age_group === group}]" @click="changeAgeGroup(group)" />
                    </span>
                </div>
        
                <!-- Nutritionally at Risk -->
                <div class="container mx-auto w-full pb-1">
                    <div class="w-full text-center font-bold p-2 border-2 border-primary">
                        <span>Nurtirionally at Risk [ {{ patients?.length ?? 0 }} ]</span>
                    </div>
                </div>
        
                <div class="container mx-auto px-6 pb-1">
                    <ol class="list-decimal">
                        <li v-for="q in questions">{{ q }}</li>
                    </ol>
                </div>
                
                <DataTable
                    :value="patients" 
                    :rows="10" 
                    :pageLinkSize="1" 
                    :rowHover="true" 
                    paginator 
                    scrollable 
                    stripedRows 
                    v-model:filters="filters" 
                    filterDisplay="row" 
                    dataKey="id"
                    class="border border-primary hover:cursor-pointer" 
                    @row-click="openPatientDetailsConfirmation($event)">
        
                    <template #empty>
                        <div class="container mx-auto flex flex-col justify-center items-center py-4 h-[50vh]">
                            <NoData class="text-primary" />
                            <p class="text-lg font-bold mt-4">No data found.</p>
                        </div>
                    </template>
        
                    <Column field="wardname" header="Ward" class="w-[40%]" header-class="text-primary border-b border-primary" :showFilterMenu="false">
                        <template v-if="Object.keys(patients)?.length" #filter="{ filterModel, filterCallback }">
                            <Select :options="wards" v-model="filterModel.value" class="p-column-filter w-full" showClear @change="filterCallback()" />
                        </template>
                    </Column>
        
                    <Column field="patname" header="Patient" class="w-[40%]" header-class="text-primary border-b border-l border-primary" :showFilterMenu="false">
                        <template v-if="Object.keys(patients)?.length" #filter="{ filterModel, filterCallback }">
                            <InputText v-model="filterModel.value" type="text" class="p-column-filter w-full" showClear @input="filterCallback()" />
                        </template>
                    </Column>
        
                    <Column v-for="(value, index) in questions" 
                        :field="`question${index + 1}`" 
                        class="w-[5%]" header-class="text-primary border-b border-l border-primary" 
                        :showFilterMenu="false"
                    >
                        <template #header>
                            <span class="w-full text-center">{{ index + 1 }}</span>
                        </template>
        
                        <template #body="{ data, field }">
                            <div class="w-full text-center">
                                <template v-if="data[field] === 'true'">
                                    <i class="pi pi-check text-emerald-400 !text-3xl"></i>
                                </template>
                                <template v-else>
                                    <i class="pi pi-times text-red-400 !text-3xl"></i>
                                </template>
                            </div>
                        </template>
                        
                        <template v-if="Object.keys(patients)?.length" #filter="{ filterModel, filterCallback }">
                            <div class="w-11 h-11 border border-primary rounded-lg flex items-center justify-center" 
                                @click="
                                    () => {
                                        if (filterModel?.value === null) filterModel.value = true;
                                        else if (filterModel?.value === true) filterModel.value = false;
                                        else filterModel.value = null;
                                        filterCallback();
                                    }"
                            >
                                <template v-if="filterModel?.value === true">
                                    <i class="pi pi-check text-emerald-400 !text-3xl"></i>
                                </template>
                                <template v-else-if="filterModel?.value === false">
                                    <i class="pi pi-times text-red-400 !text-3xl"></i>
                                </template>
                                <template v-else>
                                    <div class="w-5 h-1 bg-primary rounded-sm"></div>
                                </template>
                            </div>
                        </template>
                    </Column>
                </DataTable>
            </div>
        </ViewTemplate>
    </div>
</template>
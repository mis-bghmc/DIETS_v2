<script setup>
import { FilterMatchMode } from '@primevue/core/api';
import { MealsService } from '~/services/MealsService';
import { SNSService } from '~/services/SNSService';
import NoData from '~/public/images/no-data.svg';

const toast = useToast();
const echo = useEcho();
const { sortArray } = useSort();
const { formatDateTime } = useDate();
const { enccode, openPatientDetailsConfirmation } = usePatient();

const user_store = useUserStore();
const { user } = storeToRefs(user_store);

const diet_list_store = useDietListStore();
const { s_diet_list } = storeToRefs(diet_list_store);
const { error, status } = inject('diet_list');

const is_dietitian = ref(user.value?.user_level === '59');

const diet_groups = ref({
    Oral: s_diet_list.value?.O || [],
    Enteral: s_diet_list.value?.E || [],
    SNS: s_diet_list.value?.ONS || []
});
const diet_group = ref('Oral');
const diet_list = ref([]);

const ward_grouped_patients = ref({});
const wards = ref([]);

const ward_code = ref();
const ward_name = ref();

const meal_times = ref([]);
const meal_time = ref(0);

const total = ref(0);
const count = ref(0);

const updated = ref();

const updating_meal_status = ref(false);
const select_all = ref(true);
const visible = ref(false);

const diet_types = ref();
const food_allergies = ref();
const precautions = ref();
const filters = ref();

const initFilters = () => {
    filters.value = {
        global: { value: null, matchMode: FilterMatchMode.CONTAINS },
        dietname: { value: null, matchMode: FilterMatchMode.EQUALS },
        food_allergies: { value: null, matchMode: FilterMatchMode.EQUALS },
        precaution: { value: null, matchMode: FilterMatchMode.EQUALS },
    };
};


//  Show diet list based on the diet group selected
function changeDietGroup(_diet_group){
    initFilters();
    
    ward_code.value = null;
    meal_times.value = [];
    meal_time.value = 0;
    total.value = 0;
    count.value = 0;

    diet_group.value = _diet_group;
    diet_list.value = diet_groups.value[diet_group.value];
    
    setWards(diet_list.value);
}

//  Set wards
function setWards(_diet_list){
    if(_diet_list?.length > 0){
        ward_grouped_patients.value = Object.groupBy(_diet_list, ({wardcode}) => wardcode);
        wards.value = sortArray(Object.entries(ward_grouped_patients.value).map(([wardcode, patients]) => ({wardcode, wardname: patients[0]?.wardname || ''})), 'wardname');

    }else{
        ward_grouped_patients.value = {};
        wards.value = [];
    }
}

//  Set diet list
function setDietList(_meal_time){
    initFilters();

    let ward_patients = ward_grouped_patients.value[ward_code.value];

    if(!ward_patients) return;
    
    if(diet_group.value === 'Enteral'){
        const meal_time_grouped_patients = Object.groupBy(ward_patients, ({meal_time}) => meal_time);
        meal_times.value = Object.keys(meal_time_grouped_patients);
        meal_time.value = _meal_time ?? 0;
        ward_patients = meal_time_grouped_patients[meal_times.value[meal_time.value]];
    }
    
    diet_list.value = is_dietitian.value ? ward_patients : ward_patients.filter(patient => patient.meal_status !== null);
    ward_name.value = ward_patients[0]?.wardname || '';
    diet_types.value = [...new Set(ward_patients.map(patient => patient.dietname))].sort();
    food_allergies.value = [...new Set(ward_patients.map(patient => patient.food_allergies))].filter(food_allergy => food_allergy !== '' && food_allergy !== null).sort();
    precautions.value = [...new Set(ward_patients.map(patient => patient.precaution))].filter(precaution => precaution !== '' && precaution !== null).sort();

    setMealStatusValues(ward_patients);
    checkSelectedAll();
}

//  Set status bar values
function setMealStatusValues(_patients){
    const filter_by = is_dietitian.value ? (patient => patient.meal_status !== null) : (patient => patient.meal_status === 'S');

    count.value = _patients.filter(filter_by).length;
    total.value = diet_list.value?.length || 0;
}

//  Update the meal status for all patients in the ward
async function selectAll(){
    if(!ward_code.value) return;
    if(diet_list.value?.length === 0) return;
    
    select_all.value = !select_all.value;
    const temp_select_all = select_all.value;

    const temp_meal_status = temp_select_all ? (is_dietitian.value ? 'P' : 'S') : (is_dietitian.value ? null : 'P');
    
    for (const patient of diet_list.value) {
        
        const should_update =
            temp_select_all 
                ? patient.meal_status === null || (patient.meal_status === 'P' && !is_dietitian.value)
                : (patient.meal_status === 'S' && patient.verified !== 'Y' && !is_dietitian.value) ||
                (patient.meal_status === 'P' && is_dietitian.value);

        if (should_update) {
            await updateMealStatus(patient, temp_meal_status);
        }
    }
}

//  Update meal status
async function updateMealStatus(_patient, _status){
    try{
        const data = {
            headers: {
                'X-Socket-Id' : echo.socketId()
            },
            body: {
                id: _patient.id,
                meal_status: _status,
                updated_by: user.value.employeeid,
                meal_time: _patient.meal_time,
                role: is_dietitian.value ? 'DIETITIAN' : 'SERVER'
            }
        };

        if(['AM', 'PM', 'MN'].includes(_patient.meal_time)){
            await SNSService.updateMealStatus(data);

        }else{
            await MealsService.updateMealStatus(data)
        }
        
        diet_list_store.updateMealStatus(_patient.hpercode, _patient.meal_time, _status);
        updateMealStatusCount(_status);
        checkSelectedAll();

    }catch(error){
        toast.add({ severity: 'error', summary: 'Error!', detail: 'An error has occured. Please log it into the intranet or call extension 202. [Diet List -> update meal status]' });
        
    }finally{
        updating_meal_status.value = false;
    }
}

//  Update meal status count
function updateMealStatusCount(_new_meal_status){
    if(!ward_code.value) return;
    
    if(_new_meal_status === null || (_new_meal_status === 'P' && !is_dietitian.value)){
        count.value--;
        return;
    }

    count.value++;
}

//  Check if all patients in the diet list are selected
function checkSelectedAll(){
    select_all.value = true;

    if(diet_list.value?.length === 0) return;

    select_all.value = !diet_list.value.some(patient => 
        (is_dietitian.value && patient.meal_status === null) || 
        (!is_dietitian.value && patient.meal_status === 'P')
    );
}

//  Get current meal time
function getMealTime(){
    return diet_groups.value?.[diet_group.value]?.[0]?.meal_time ?? null;
}

//  Set checkbox status
function tickCheckbox(_meal_status){
    return is_dietitian.value ? _meal_status !== null : _meal_status === 'S';
}

//  Disable checkbox if meal is served (DIETITIAN) or verified (SERVER)
function disableCheckbox(_meal_status, _verified){
    return is_dietitian.value ? _meal_status === 'S' : _verified === 'Y';
}

//  Update meal status
async function updateMealStatusCheckbox(patient){
    if(updating_meal_status.value) return;

    updating_meal_status.value = true;

    const meal_status = is_dietitian.value ? 'P' : 'S';
    const current_status = patient?.meal_status;
    const new_meal_status = current_status === null || current_status != meal_status ? meal_status : (meal_status === 'S' ? 'P' : null);
    
    await updateMealStatus(patient, new_meal_status);
}


//  Watcher for diet list updates
watch(updated, (new_value) => {
    if (new_value.message.verified) {
        const dietgroup = diet_groups.value[diet_group.value];
        const patients = dietgroup.filter(p => p.wardname === new_value.message.wardname && p.meal_time === new_value.message.meal_time && p.meal_status === 'S' && p.verified !== 'Y');
        
        if (patients !== -1) {
            patients.forEach((patient) => {
                patient.verified = new_value.message.verified;
            });
        }

        setDietList(meal_time.value);
        return;
    }
    
    const dietgroup = Object.keys(diet_groups.value).find(group => diet_groups.value[group]?.some(p => p.id === new_value.message.id));
    const index = dietgroup ? diet_groups.value[dietgroup].findIndex(p => p.id === new_value.message.id) : -1;
    
    if (index !== -1) {
        const patient = diet_groups.value[dietgroup][index];

        diet_list_store.updateMealStatus(patient.hpercode, patient.meal_time, new_value.message.meal_status);
    }

    updateMealStatusCount(new_value.message.meal_status);
    setDietList(meal_time.value);
});

//  init
watch(
    () => s_diet_list.value,
    (val) => {
        if(val && Object.keys(val).length > 0) {
            changeDietGroup('Oral');
        }
    },
    { immediate: true }
);


//==================== Verification Dialog ======================
//  Hide verification dialog
function closeVerification(){
    diet_list.value.forEach((patient) => {
        if (patient.meal_status === 'S') {
            patient.verified = 'Y';
        }
    });

    setDietList(meal_time.value);
}


//  On mounted
onMounted(()=> {
    echo.channel('status-update-channel')    
        .listen('.status-updated', (e) => {
            updated.value = e;
        })
        .listen('.ons-status-updated', (e) => {
            updated.value = e;
        });
})
</script>

<template>
    <ScrollTop />

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

    <!-- Patients Panel -->
    <div class="bg-[--surface-card] p-3 rounded-md">
        <ViewTemplate :error="error" :status="status">
            <div class="container mx-auto sm:text-sm md:text-base lg:text-lg text-right italic">
                <span class="text-muted-color italic">Updated: </span>
                <span>{{ formatDateTime(diet_groups?.[diet_group]?.[0]?.created_at) }}</span>
            </div>

            <!-- Diet group buttons -->
            <DietGroups :diet-group="diet_group" @change-group="changeDietGroup" />

            <!-- Meal time -->
            <div class="container mx-auto">
                <div class="w-full grid grid-cols-5 text-base md:text-3xl lg:text-5xl px-4 py-1 font-bold border-2 border-primary">
                    <div class="col-span-1"></div>

                    <div class="flex col-span-3 justify-center items-center text-center">
                        <div v-if="diet_group != 'Enteral'">
                            <span>{{ getMealTime() ?? '&nbsp' }}</span>
                        </div>
                        
                        <div v-else class="flex justify-center items-center">
                            <Button v-if="meal_times[meal_time]"
                                text 
                                icon="pi pi-chevron-left" 
                                :disabled="!meal_times[meal_time - 1]" 
                                pt:root:class="bg-transparent print:text-transparent" 
                                @click="setDietList(meal_time - 1)" />

                            <span v-if="meal_times[meal_time]" class="w-32">{{ meal_times[meal_time] }}</span>
                            <span v-else>&nbsp</span>

                            <Button v-if="meal_times[meal_time]" 
                                text 
                                icon="pi pi-chevron-right" 
                                :disabled="!meal_times[meal_time + 1]" 
                                pt:root:class="bg-transparent print:text-transparent" 
                                @click="setDietList(meal_time + 1)" />
                        </div>
                    </div>

                    <div class="flex col-span-1 justify-end items-center">
                        <div :class="{'hidden': !ward_code}">
                            <FoodServiceVerification 
                                :count="count" 
                                :total="total" 
                                :meal-time="getMealTime()" 
                                :meal-time-enteral="meal_times[meal_time]" 
                                :enteral-selected="diet_group === 'Enteral'"
                                :wardname="ward_name"
                                @verified="closeVerification()" 
                            />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Patients Panel Header -->
            <div class="container mx-auto mt-1 bg-primary">
                <div class="p-4">
                    <Select 
                        v-model="ward_code"
                        :options="wards" 
                        optionLabel="wardname" 
                        optionValue="wardcode" 
                        placeholder="Wards" 
                        :fluid="true"
                        size="large"
                        scrollHeight="50vh"
                        pt:panel:class="h-[50vh]"
                        pt:input:class="sm:text-sm md:text-base lg:text-lg font-semibold"
                        @change="setDietList()" />
                </div>
            </div>

            <!-- Patients Panel Body -->
            <div class="container mx-auto border border-primary">
                
                <!-- Meal status progress bar -->
                <div class="sm:text-sm md:text-base lg:text-lg py-2 px-4">
                    <div class="overflow-hidden relative border-0 h-5 rounded-md bg-primary-100">
                        <div class="absolute inset-0 rounded-md bg-primary m-0 border-0 transition-width duration-500 ease-in-out" 
                            :style="{width: total ? `${(count / total) * 100}%` : '0%'}">
                        </div>
                        <div class="absolute inset-0 flex items-center justify-center text-black font-semibold">
                            <span>{{ count }} / {{ total }}</span>
                        </div>
                    </div>
                </div>
                
                <!-- Patients Table -->
                <DataTable 
                    :value="ward_code ? diet_list : null" 
                    datakey="hpercode"
                    stripedRows 
                    :rowHover="true" 
                    removableSort
                    v-model:filters="filters" 
                    filterDisplay="menu"
                    :globalFilterFields="['rmname', 'patname', 'dietname', 'food_allergies', 'precaution', 'ordreas', 'nar']"
                    class="hover:cursor-pointer"
                    @row-click="openPatientDetailsConfirmation($event)">

                    <template #header v-if="ward_code">
                        <div class="flex gap-10">
                            <div class="flex w-full">
                                <IconField iconPosition="left">
                                    <InputIcon>
                                        <i class="pi pi-search" />
                                    </InputIcon>

                                    <InputText v-model="filters['global'].value" placeholder="Search..." class="w-full flex items-center" />
                                </IconField>
                            </div>

                            <div class="flex-1">
                                <Button 
                                    outlined 
                                    icon="pi pi-filter-slash" 
                                    label="Clear"
                                    @click="initFilters()" />
                            </div>
                        </div>
                    </template>

                    <template #empty>
                        <div class="container mx-auto flex flex-col justify-center items-center py-4 h-[50vh]">
                            <NoData class="text-primary" />
                            <p class="text-lg font-bold mt-4">No data found.</p>
                        </div>
                    </template>

                    <Column field="meal_status" style="width: 1%">
                        <template #header>
                            <Checkbox 
                                v-if="is_dietitian"
                                :modelValue="select_all" 
                                :binary="true" 
                                :disabled="diet_list?.[0]?.meal_status === 'S'"
                                pt:root:class="!w-8 !h-8" 
                                pt:box:class="!w-full !h-full !border !border-primary"
                                @change="selectAll()" />
                        </template>

                        <template #body="{data, field}">
                            <Checkbox 
                                :modelValue="tickCheckbox(data[field])" 
                                :binary="true" 
                                :disabled="disableCheckbox(data[field], data.verified)" 
                                pt:root:class="!w-8 !h-8" 
                                pt:box:class="!w-full !h-full !border !border-primary"
                                @change="updateMealStatusCheckbox(data)" />
                        </template>
                    </Column>

                    <Column v-if="!is_dietitian" field="rmname" header="Room" style="width: 20%"></Column>

                    <Column field="patname" header="Name of Patients" :showFilterMenu="false" sortable style="width: 20%">
                        <template #body="{data,field}">
                            <div class="flex flex-col">
                                <span>{{ data[field] }}</span>
                                <span class="text-muted-color italic">{{ data.hpercode }}</span>
                                <span v-if="data?.wardcode === 'ERB'" class="text-blue-400 italic">{{ data?.wardname_o }}</span>
                            </div>
                        </template>
                    </Column>

                    <Column field="dietname" header="Diet Required" filterField="dietname" :showFilterMatchModes="false" showClearButton sortable style="width: 15%">
                        <template #body="{data,field}">
                            <div class="w-full">
                                <Tag :severity="data.food_allergies || data.precaution || data.ordreas?.trim() ? 'danger' : 'success'" class="w-full text-xl font-bold">{{ data[field] }}</Tag>
                            </div>
                        </template>

                        <template #filter="{ filterModel, applyFilter }">
                            <Select v-model="filterModel.value" :options="diet_types" class="p-column-filter" style="min-width: 12rem" @change="applyFilter()" />
                        </template>
                    </Column>

                    <Column header="Food Allergies" filterField="food_allergies" :showFilterMatchModes="false" showClearButton style="width: 15%" class="hidden lg:table-cell">
                        <template #body="{ data }">
                            <div class="flex align-items-center gap-2">
                                <span>{{ data.food_allergies }}</span>
                            </div>
                        </template>

                        <template #filter="{ filterModel, applyFilter }">
                            <Select v-model="filterModel.value" :options="food_allergies" class="p-column-filter" style="min-width: 12rem" @change="applyFilter()" />
                        </template>
                    </Column>

                    <Column header="Precautions" filterField="precaution" :showFilterMatchModes="false" showClearButton style="width: 15%" class="hidden lg:table-cell">
                        <template #body="{ data }">
                            <div class="flex align-items-center gap-2">
                                <span>{{ data.precaution }}</span>
                            </div>
                        </template>

                        <template #filter="{ filterModel, applyFilter }">
                            <Select v-model="filterModel.value" :options="precautions" class="p-column-filter" style="min-width: 12rem" @change="applyFilter()" />
                        </template>
                    </Column>

                    <Column field="ordreas" header="Remarks" style="width: 20%" class="hidden lg:table-cell"></Column>

                    <Column v-if="is_dietitian" field="nar" header="N a R" sortable style="width: 10%" class="hidden lg:table-cell">
                        <template #body="{data, field}">
                            <div v-if="data[field]" class="w-full">
                                <Tag :severity="data[field] === 'YES' ? 'danger' : 'success'" class="w-full text-xl font-bold">{{ data[field] }}</Tag>
                            </div>
                        </template>
                    </Column>
                </DataTable>
            </div>
        </ViewTemplate>
    </div>
</template>
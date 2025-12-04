<script setup>
import { PatientsService } from '~/services/PatientsService';
import { DoctorsOrdersService } from '~/services/DoctorsOrdersService';
import NoData from '~/public/images/no-data.svg';

const confirm = useConfirm();
const toast = useToast();

const { formatDateTime } = useDate();

const user_store = useUserStore();
const { user } = storeToRefs(user_store);

const diets_store = useDietsStore();
const { feeding_modes } = storeToRefs(diets_store);

const props = defineProps({
    data: Object,
    fromHistory: Boolean,
    orderStatus: Object
});

const edit_allergies = ref(false);
const edit_precautions = ref(false);

const a_key = ref();
const p_key = ref();

const precautions = ref();
const food_allergies = ref();

const error_food_allergies = ref(false);
const error_precautions = ref(false);
const mounted_food_allergies = ref(false);
const mounted_precautions = ref(false);

const feeding_mode = ref();

const { error: fm_error, status: fm_status, refresh: fm_refresh } = await useLazyAsyncData(
    'feeding-modes', 
    () => diets_store.getFeedingModes(),
    {
        default: () => [],
        immediate: false
    }
);


//  Edit allergies
function editAllergies(){
    error_food_allergies.value = false;
    mounted_food_allergies.value = false;
    edit_allergies.value = true;
    a_key.value = `A_${new Date()}`;
}

//  Set food allergies
function setFoodAllergies(allergies){
    food_allergies.value = Array.isArray(allergies) ? allergies.join(', ') : allergies;
}

//  Popup to confirm changes to food allergies
const confirmSaveAllergies = () => {
    confirm.require({
        group: 'headless',
        message: 'This will overwrite any existing food allergies.',
        accept: () => {
            saveAllergies();
        },
        reject: () => {
            edit_allergies.value = false
        }
    });
}

//  Save allergies
async function saveAllergies() { 
    if(!food_allergies.value?.trim()) {
        toast.add({ severity: 'error', summary: 'Invalid input!', detail: 'Food allergy cannot be empty.', life: 3000 });
        return;
    }

    if(props.data?.category === food_allergies.value) return;

    try{
        await PatientsService.updateFoodAllergies({
            body: {
                hpercode: props.data?.hpercode,
                allergies: food_allergies.value,
                licno: user.value.employeeid
            }
        });
        
        props.data.category = food_allergies.value;

        toast.add({ severity: 'success', summary: 'Success!', detail: 'Food allergies have been successfully saved.', life: 5000 });

    }catch(error){
        toast.add({ severity: 'error', summary: 'Error!', detail: 'An error has occured. Please log it into the intranet or call extension 202. [Diet Details -> Food Allergies -> save]' });
        
    }finally{
        edit_allergies.value = false;
    }
}

//  Edit precautions
function editPrecautions(){
    error_precautions.value = false;
    mounted_precautions.value = false;
    edit_precautions.value = true;
    p_key.value = `P_${new Date()}`;
}

//  Set precautions
function setPrecautions(precaution) {
    precautions.value = precaution;
}

function formatScheduleString(timeString) {
    if (!timeString || typeof timeString !== "string") {
        return "";
    }

    let times;

    try {
        times = JSON.parse(timeString); // convert string → array
    } catch (error) {
        return ""; // return empty if invalid format
    }

    if (!Array.isArray(times) || times.length === 0) {
        return "";
    }

    return times.join(", ");
}


//  Popup to confirm changes to precaution
const confirmSavePrecautions = () => {
    confirm.require({
        group: 'headless',
        message: 'This will overwrite any existing precaution.',
        accept: () => {
            savePrecaution();
        },
        reject: () => {
            edit_precautions.value = false
        }
    });
}

//  Save precaution
async function savePrecaution() { 
    try{
        const _precautions = precautions.value?.join(', ') || null;

        if(props.data?.precaution === _precautions) return;

        await DoctorsOrdersService.updatePrecautions({
            body: {
                id: props.data?.docointkey,
                precaution: _precautions
            }
        });
    
        props.data.precaution = _precautions;

        toast.add({ severity: 'success', summary: 'Success!', detail: 'Precautions have been successfully saved.', life: 5000 });

    }catch(error){
        toast.add({ severity: 'error', summary: 'Error!', detail: 'An error has occured. Please log it into the intranet or call extension 202. [Diet Details -> Precautions -> save]' });
        
    }finally{
        edit_precautions.value = false;
    }
}


//  On mounted
onMounted(async () => {
    await fm_refresh();

    feeding_mode.value = feeding_modes.value?.find(item => item.id === props.data?.feedingMode?.trim())?.name;
});
</script>

<template>
    <ClientOnly>
        <ConfirmDialog group="headless" class="border border-primary rounded-md" pt:mask:class="backdrop-blur-sm">
            <template #container="{ message, acceptCallback, rejectCallback }">
                <div class="flex flex-col items-center p-4">
                    <div class="flex gap-2 items-end">
                        <i class="pi pi-exclamation-triangle text-red-500 text-2xl"></i>
                        <span>{{ message.message }}</span>
                    </div>
                    
                    <div class="flex items-center gap-2 mt-6">
                        <Button label="Cancel" outlined @click="rejectCallback" class="w-32 p-button-danger"></Button>
                        <Button label="Save" outlined @click="acceptCallback" class="w-32"></Button>
                    </div>
                </div>
            </template>
        </ConfirmDialog>

        <div v-if="!fromHistory">
            <h5 class="flex gap-2 items-center"> 
                <Icon name="fluent:food-16-filled" size="1.5em" class="text-primary"/> 
                <span class="font-bold">Diet Details</span>
            </h5>
        </div>

        <div v-if="!data" class="container mx-auto flex flex-col justify-center items-center py-4 h-[50vh]">
            <NoData class="text-primary" />
            <p class="text-lg font-bold mt-4">No data found.</p>
        </div>

        <div v-else>
            <Tag v-if="fromHistory" :severity="orderStatus?.severity" class="w-full text-xl font-bold mb-2">{{ orderStatus?.label }}</Tag>

            <div class="flex flex-col gap-6">
                <div class="flex flex-col gap-2">
                    <div class="flex gap-2 font-bold text-2xl">
                        <span v-if="data?.diettype">{{ data?.diettype === 'E' ? 'Enteral' : 'Oral' }}</span>
                        <span>-</span>
                        <span>{{ data?.dietname }}</span>
                        <Icon name="fluent:question-circle-16-filled" class="text-2xl bg-primary pb-8 hover:cursor-help" v-tooltip.bottom="data?.dietdesc" />
                    </div>
    
                    <div class="flex flex-col lg:flex-row gap-2">
                        <div class="flex gap-2">
                            <span class="text-muted-color italic">Ordered on</span>
                            <span class="font-semibold">{{ formatDateTime(data?.dodate) }}</span>
                        </div>
                        <div class="flex gap-2">
                            <span class="text-muted-color italic">by</span>
                            <span class="font-semibold">{{ data?.lname ? `${data?.lname}, ${data?.fname}` : '-' }}</span>
                        </div>
                    </div>
                </div>
    
                <Divider type="dashed" class="!my-0" />
    
                <div class="flex flex-col gap-2">
                    <div>
                        <span class="font-bold text-base">Requirements</span>
                    </div>
    
                    <div class="flex flex-col lg:flex-row gap-4 justify-between px-4">
                        <div class="flex flex-col gap-2 w-full">
                            <span class="font-bold">Macronutrients</span>
    
                            <div class="flex flex-col gap-2 pl-4">
                                <div class="flex justify-between">
                                    <span class="text-muted-color italic">Protein</span>
                                    <span class="font-semibold">{{ data?.protein ?? '0' }} g</span>
                                </div>
    
                                <div class="flex justify-between">
                                    <span class="text-muted-color italic">Carbohydrates</span>
                                    <span class="font-semibold">{{ data?.carbohydrates ?? '0' }} g</span>
                                </div>
    
                                <div class="flex justify-between">
                                    <span class="text-muted-color italic">Fats</span>
                                    <span class="font-semibold">{{ data?.fats ?? '0' }} g</span>
                                </div>
    
                                <div class="flex justify-between">
                                    <span class="text-muted-color italic">Fiber</span>
                                    <span class="font-semibold">{{ data?.fiber ?? '0' }} g</span>
                                </div>
                            </div>
                        </div>

                        <Divider type="dashed" layout="vertical" class="!my-0 !hidden lg:!block" />

                        <div class="flex flex-col gap-2 w-full">
                            <div class="flex justify-between">
                                <span class="font-bold">Calories</span>
                                <span class="font-semibold">{{ data?.calories ?? '-' }} kcal</span>
                            </div>

                            <div v-if="data?.diettype === 'EN'" class="flex flex-col gap-2 w-full">
                                <div class="flex justify-between">
                                    <span class="font-bold">Dilution</span>
                                    <span class="font-semibold">{{ data?.dilution ?? '-' }} kcal : 1 ml</span>
                                </div>
    
                                <div class="flex justify-between">
                                    <span class="font-bold">Volume</span>
                                    <span class="font-semibold">{{ data?.volume ?? '-' }} ml</span>
                                </div>

                                <span class="font-bold mt-2">Feeding Procedure</span>

                                <div class="flex flex-col gap-2 pl-4">
                                    <div class="flex justify-between">
                                        <span class="text-muted-color italic">Mode</span>
                                        <ViewTemplate :error="fm_error">
                                            <span class="font-semibold">{{ feeding_mode }}</span>
                                        </ViewTemplate>
                                    </div>

                                    <div class="flex justify-between">
                                        <span class="text-muted-color italic">Duration</span>
                                        <span class="font-semibold">{{ data?.feedingDuration ?? '-' }} hours(s)</span>
                                    </div>

                                    <div class="flex justify-between">
                                        <span class="text-muted-color italic">Frequency</span>
                                        <span class="font-semibold">{{ data?.feedingFrequency ?? '-' }} times / day</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
    
                <Divider type="dashed" class="!my-0" />
    
                <div class="flex flex-col gap-2">
                    <div>
                        <span class="font-bold text-base">Restrictions</span>
                    </div>
    
                    <div class="flex flex-col lg:flex-row gap-6 justify-between px-4">
                        <div class="flex justify-between gap-2 items-start w-full">
                            <div class="flex flex-col gap-2 w-full text-pretty break-words break-all">
                                <div class="flex justify-between items-center">
                                    <span class="text-muted-color italic">Food Allergies</span>
                                    
                                    <div v-if="!fromHistory">
                                        <div v-if="!edit_allergies">
                                            <Button icon="pi pi-pencil" outlined rounded @click="editAllergies()" />
                                        </div>
        
                                        <div v-else>
                                            <div v-if="mounted_food_allergies" class="flex gap-2">
                                                <Button icon="pi pi-times" severity="danger" outlined rounded @click="edit_allergies = false" />
                                                <Button icon="pi pi-check" outlined rounded :disabled="error_food_allergies" @click="confirmSaveAllergies" />
                                            </div>
                                        </div>
                                    </div>
                                </div>
    
                                <span v-if="!edit_allergies" class="font-semibold">{{ data?.category ?? '-' }}</span>
    
                                <div v-else>
                                    <Allergies 
                                        :data="data?.category" 
                                        :key="a_key" 
                                        @updated="setFoodAllergies" 
                                        @error="error_food_allergies = true" 
                                        @mounted="mounted_food_allergies = true"
                                    />
                                </div>
                            </div>
                        </div>
                        
                        <Divider type="dashed" layout="vertical" class="!my-0 !hidden lg:!block" />
    
                        <div class="flex justify-between gap-2 items-start w-full">
                            <div class="flex flex-col gap-2 w-full text-pretty break-words break-all">
                                <div class="flex justify-between items-center">
                                    <span class="text-muted-color italic">Precautions</span>
    
                                    <div v-if="!fromHistory">
                                        <div v-if="!edit_precautions">
                                            <Button icon="pi pi-pencil" outlined rounded @click="editPrecautions()" />
                                        </div>
        
                                        <div v-else>
                                            <div v-if="mounted_precautions" class="flex gap-2">
                                                <Button icon="pi pi-times" severity="danger" outlined rounded @click="edit_precautions = false" />
                                                <Button icon="pi pi-check" outlined rounded :disabled="error_precautions" @click="confirmSavePrecautions" />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <span v-if="!edit_precautions" class="font-semibold">{{ data?.precaution ?? '-' }}</span>
    
                                <div v-else>
                                    <Precautions 
                                        :data="data?.precaution" 
                                        :key="p_key" 
                                        @updated="setPrecautions" 
                                        @error="error_precautions = true" 
                                        @mounted="mounted_precautions = true"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
    
                <Divider type="dashed" class="!my-0" />
    
                <div class="flex flex-col gap-2">
                    <div> <!-- ANCHOR Special Nutrition Supplement (SNS) -->
                        <span class="font-bold text-base">Special Nutrition Supplement (SNS)</span>
                    </div>
    
                    <div class="flex flex-col gap-2 px-4">
                        <div class="flex flex-col lg:flex-row gap-2 justify-between">
                            <span class="text-muted-color italic">Type</span>
                            
                            <div class="flex gap-2 text-pretty break-words break-all">
                                <span v-if="data?.onsName && data?.onsName2" class="font-semibold">{{ `${data?.onsName} + ${data?.onsName2}` }}</span>
                                <span v-else-if="data?.onsName" class="font-semibold">{{ data?.onsName }}</span>
                                <span v-else-if="data?.onsName2" class="font-semibold">{{ data?.onsName2 }}</span>
                                <span v-else class="font-semibold">-</span>
                            </div>
                        </div>
    
                        <div class="flex flex-col lg:flex-row gap-2 justify-between">
                            <span class="text-muted-color italic"> Frequency</span>
    
                            <div class="flex gap-2">
                                <span class="font-semibold">{{ formatScheduleString(data?.onsFrequency) || '-' }}</span>
                            </div>
                        </div>
    
                        <div class="flex flex-col gap-2 justify-between" :class="{'flex-col': data?.onsDescription, 'flex-row': !data?.onsDescription}">
                            <span class="text-muted-color italic">Other Details</span>
                            <span v-if="!data?.onsDescription" class="font-semibold">-</span>
                            
                            <div v-else class="w-full h-max p-2 border border-dashed rounded-md text-pretty break-words break-all">
                                <span class="font-semibold">{{ data?.onsDescription }}</span>
                            </div>
                        </div>
                    </div>
                </div>
    
                <Divider type="dashed" class="!my-0" />
    
                <div class="flex flex-col gap-2">
                    <div>
                        <span class="font-bold text-base">Remarks</span>
                    </div>
    
                    <div class="px-4">
                        <span v-if="!data?.ordreas" class="font-semibold">-</span>
    
                        <div v-else class="w-full h-max p-2 border border-dashed rounded-md text-pretty break-words break-all">
                            <span class="font-semibold">{{ data?.ordreas }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </ClientOnly>
</template>
<script setup>
import { FoodRequestsService } from '~/services/FoodRequestsService';

const emit = defineEmits(['created']);

const props = defineProps({
    date: Date
});

const echo = useEcho();
const toast = useToast();

const user_store = useUserStore();
const { user } = storeToRefs(user_store);

const patients_store = usePatientsStore();
const { admitted_patients } = storeToRefs(patients_store);

const { formatAllNumeric, formatMonthShort } = useDate();

const visible = ref(false);

const requesting = ref();
const qnty = ref();
const meals_selected = ref();
const remarks = ref();

const meals = ref(['BREAKFAST', 'LUNCH', 'DINNER']);
const snacks = ref(['AM SNACK', 'PM SNACK', 'MN SNACK']);

const saving = ref(false);


//  Open food request form
function openForm() {
    requesting.value = null;
    qnty.value = null;
    meals_selected.value = null;
    remarks.value = null;
    visible.value = true;
}

//  Create new food request
async function create() {
    saving.value = true;

    try{
        await FoodRequestsService.create({
            headers: {
                'X-Socket-Id' : echo.socketId()
            },
            body: {
                requesting: requesting.value,
                meals: meals_selected.value,
                qnty: qnty.value,
                remarks: remarks.value,
                request_date: formatAllNumeric(props.date),
                updated_by: user.value.employeeid
            }
        });
        
        emit('created');
        
        toast.add({ severity: 'success', summary: 'Success!', detail: 'Food request have been successfully saved.', life: 5000 });
        
        visible.value = false;

    }catch(error){
        toast.add({ severity: 'error', summary: 'Error!', detail: 'An error has occured. Please log it into the intranet or call extension 202. [Food Requests -> create]' });

    }finally{
        saving.value = false;
    }
}

//  Check if date selected is greater than or equal to current date
function checkDate() {
    return props.date?.setHours(0,0,0,0) >= new Date().setHours(0,0,0,0);
}

//  Validate dialog inputs
function validate() {
    return !requesting.value || !qnty.value || !meals_selected.value?.length;
}

const requestType_selected = ref('option_1');
const frequencyType_selected = ref('option_1');
const selectedPatients = ref([]);

</script>

<template>
    <Button v-if="checkDate()" text icon="pi pi-pen-to-square" @click="openForm()" />

    <Dialog 
        modal 
        v-model:visible="visible" 
        class="!w-full md:!w-1/2 lg:!w-1/3"
        pt:root:class="!rounded-md !border-2 !border-primary sm:!text-sm md:!text-base lg:!text-lg"
        pt:header:class="!pb-2 !pt-3 !rounded-t-lg !border-b !border-primary"
        pt:mask:class="!backdrop-blur-sm" 
    >
        <template #header>
            <span class="font-bold">Food Request</span>
        </template>

        <!-- <div class="flex flex-col gap-4 pt-4">
            <div>
                <span class="font-semibold">Date : </span>
                <span>{{ formatMonthShort(date) }}</span>
            </div>
            <FloatLabel variant="on">
                <InputText id="requesting" v-model="requesting" :invalid="!requesting" class="w-full" />
                <label for="requesting">Requesting</label>
            </FloatLabel>

            <FloatLabel variant="on">
                <InputNumber id="quantity" v-model="qnty" :invalid="!qnty" class="w-full" />
                <label for="quantity">Quantity</label>
            </FloatLabel>

            <div class="flex flex-col gap-2 mt-2 mb-6">
                <div class="w-full text-center border-b border-primary">
                    <span class="font-semibold">Meals & Snacks</span>
                </div>
                <div class="w-full grid grid-cols-2 gap-2 px-4">
                    <div class="col-span-1 flex flex-col gap-2">
                        <div v-for="meal of meals" :key="meal" class="flex align-items-center">
                            <Checkbox 
                                v-model="meals_selected" 
                                :inputId="meal" 
                                :value="meal" 
                                :invalid="!meals_selected?.length"
                                name="meals" 
                                pt:root:class="!w-8 !h-8" 
                                pt:box:class="!w-full !h-full !border !border-primary"
                            />
                            <label :for="meal" class="ml-2 flex items-center">{{ meal }}</label>
                        </div>
                    </div>
    
                    <div class="col-span-1 flex flex-col gap-2">
                        <div v-for="snack of snacks" :key="snack" class="flex align-items-center">
                            <Checkbox 
                                v-model="meals_selected" 
                                :inputId="snack" 
                                :value="snack" 
                                :invalid="!meals_selected?.length"
                                name="snacks" 
                                pt:root:class="!w-8 !h-8" 
                                pt:box:class="!w-full !h-full !border !border-primary"
                            />
                            <label :for="snack" class="ml-2 flex items-center">{{ snack }}</label>
                        </div>
                    </div>
                </div>
            </div>

            <FloatLabel variant="on">
                <Textarea id="remarks" v-model="remarks" rows="5" class="w-full" />
                <label for="remarks">Remarks</label>
            </FloatLabel>

            <Button label="Create" :disabled="validate() || saving" :loading="saving" @click="create()" />
        </div> -->

        <div>
            <p>Select Food Request Type</p>
            <div class="flex gap-4 mt-4">
                <div class="flex items-center">
                    <RadioButton v-model="requestType_selected" inputId="option_1" name="option_1" value="option_1" />
                    <label for="option_1" class="ml-2">Patient</label>
                </div>
                <div class="flex items-center">
                    <RadioButton v-model="requestType_selected" inputId="option_2" name="option_2" value="option_2" />
                    <label for="option_2" class="ml-2">Non-Patient</label>
                </div>
            </div>

            <div>
                <p>Date:</p>
                <div class="flex gap-4 mt-4">
                    <div class="flex items-center">
                        <RadioButton v-model="frequencyType_selected" inputId="option_1" name="option_1" value="option_1" />
                        <label for="option_1" class="ml-2">Specific Date</label>
                    </div>
                    <div class="flex items-center">
                        <RadioButton v-model="frequencyType_selected" inputId="option_2" name="option_2" value="option_2" />
                        <label for="option_2" class="ml-2">Re-Occuring</label>
                    </div>
                </div>
                <DatePicker v-model="icondisplay" showIcon fluid iconDisplay="input" v-if="frequencyType_selected === 'option_1'" />
            </div>

            <div v-if="requestType_selected === 'option_1'" class="mt-6">
                <p>Select Patients</p>
                <MultiSelect 
                    v-model="selectedPatients" 
                    :options="[]" 
                    optionLabel="name" 
                    placeholder="Select Patients" 
                    class="w-full mt-2" 
                    :disabled="false" 
                    filter 
                />
            </div>

            <div>

            </div>
        </div>
    </Dialog>



</template>
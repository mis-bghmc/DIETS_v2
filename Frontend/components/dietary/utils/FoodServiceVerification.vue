<script setup>
import { EmployeesService } from '~/services/EmployeesService';
import { FoodService } from '~/services/FoodService';

const echo = useEcho();
const toast = useToast();
const confirm = useConfirm();

const user_store = useUserStore();
const { user } = storeToRefs(user_store);

const emit = defineEmits(['verified']);

const props = defineProps({
    count: Number,
    total: Number,
    mealTime: String,
    mealTimeEnteral: String,
    enteralSelected: Boolean,
    wardname: String
});

const visible = ref(false);

const total_patients_ward = ref(0);
const unlisted_patients = ref('');
const remarks = ref('');

const emp_id = ref('');
const employee = ref();

const empty = ref(false);
const verification_failed = ref(false);
const processing_prepared = ref(false);

const options = ref({
  penColor: 'rgb(0,0,0)',
  backgroundColor: 'rgb(255, 255, 255, 0)',
  maxWidth: 4,
  minWidth: 2,
});

const signature = ref();


//  Open verification
function openVerification() {
    visible.value = true;
    total_patients_ward.value = 0;
    unlisted_patients.value = '';
    remarks.value = '';
    emp_id.value = '';
    employee.value = null;
    empty.value = false;
    verification_failed.value = false;
    signature.value = null;
}

//  Fetch employee details
async function getEmployee(){
    employee.value = null;
    empty.value = false;

    if(emp_id.value === '') return;
    
    try{
        employee.value = await EmployeesService.getEmployee(emp_id.value);
    
        if(employee.value.length === 0){
            employee.value = null;
            empty.value = true;
        }
        
    }catch(error){
        toast.add({ severity: 'error', summary: 'Error!', detail: 'An error has occured. Please log it into the intranet or call extension 202. [Food Service -> Employee]' });
    }
}

//  Confirm delete draft
const confirmVerify = () => {
    if(!employee.value || employee.value.length === 0 || signature.value?.isCanvasEmpty()){
        verification_failed.value = true;
        return;
    }
    
    confirm.require({
        group: 'headless_food_service',
        accept: () => {
            verify();
        },
        reject: () => {
            
        }
    });
}

//  Verify
async function verify(){
    if(processing_prepared.value) return;
    processing_prepared.value = true;
    
    try{
        await FoodService.verify({
            headers: {
                'X-Socket-Id' : echo.socketId()
            },
            body: {
                wardname: props.wardname,
                meal_time: !props.enteralSelected ? props.mealTime : props.mealTimeEnteral,
                total: total_patients_ward.value,
                served: props.count,
                not_served: props.total - props.count,
                not_given: Math.abs(total_patients_ward.value - props.count),
                unlisted_patients: unlisted_patients.value,
                remarks: remarks.value,
                server_id: user.value.employeeid,
                nurse_id: emp_id.value,
                nurse_signature: signature.value?.saveSignature() || null
            }
        });
        
        emit('verified');
        visible.value = false;
        toast.add({ severity: 'success', summary: 'Success!', detail: 'Food service report have been successfully saved.', life: 5000 });

    }catch(error){
        toast.add({ severity: 'error', summary: 'Error!', detail: 'An error has occured. Please log it into the intranet or call extension 202. [Food Service -> Verification]' });
        
    }finally{
        processing_prepared.value = false;
    }
}
</script>

<template>
    <Button v-if="user?.user_level === '60'" 
        text 
        icon="pi pi-angle-double-right" 
        :disabled="!total"
        class="!px-2 lg:!px-8" 
        pt:icon:class="!text-2xl lg:!text-5xl" 
        @click="openVerification()" 
    />

    <ConfirmDialog group="headless_food_service" class="border border-primary rounded-md" pt:mask:class="backdrop-blur-sm">
        <template #container="{ acceptCallback, rejectCallback }">
            <div class="flex flex-col items-center p-4">
                <div class="flex gap-2 items-center">
                    <i class="pi pi-exclamation-triangle !text-red-500 !text-2xl"></i>
                    <span>Please make sure to fill in all necessary fields before proceeding.</span>
                </div>
                
                <div class="flex items-center gap-2 mt-6">
                    <Button label="Cancel" outlined @click="rejectCallback" class="w-32 p-button-danger"></Button>
                    <Button label="Save" outlined @click="acceptCallback" class="w-32"></Button>
                </div>
            </div>
        </template>
    </ConfirmDialog>

    <Dialog 
        modal 
        v-model:visible="visible" 
        header="Food Service Verification"
        pt:root:class="!w-5/6 lg:!w-1/2 !border-primary !border-2 sm:!text-sm md:!text-base lg:!text-lg" 
        pt:header:class="!pb-2 !pt-3 !rounded-t-lg !border-b !border-primary"
        pt:mask:class="!backdrop-blur-sm" 
    >

        <template #header>
            <span class="font-bold text-primary my-2">Food Service Verification</span>
        </template>

        <div class="grid grid-cols-6 py-2">
            <div class="col-span-4 w-full">
                <p class="font-bold h-10 flex items-end">No. of patients in the ward</p>
                <p class="font-bold h-10 flex items-end">No. of patients served</p>
                <p class="font-bold h-10 flex items-end">No. of trays not served</p>
                <p class="font-bold h-10 flex items-end">No. of patients not given food</p>
            </div>

            <div class="col-span-2 w-full">
                <p class="font-bold h-10 flex items-end">
                    <InputNumber 
                        v-model="total_patients_ward" 
                        inputId="integeronly" 
                        :highlightOnFocus="true" 
                        :min="0"
                        inputClass="w-full h-8 font-bold text-center" />
                </p>
                
                <p class="font-bold h-10 flex items-end">
                    <InputNumber 
                        :modelValue="count" 
                        inputId="integeronly" 
                        :highlightOnFocus="true" 
                        disabled 
                        inputClass="w-full h-8 font-bold text-center" />
                </p>

                <p class="font-bold h-10 flex items-end">
                    <InputNumber 
                        :modelValue="total - count" 
                        inputId="integeronly" 
                        :highlightOnFocus="true" 
                        disabled 
                        inputClass="w-full h-8 font-bold text-center" />
                </p>

                <p class="font-bold h-10 flex items-end">
                    <InputNumber 
                        :modelValue="Math.abs(total_patients_ward-count)" 
                        inputId="integeronly" 
                        :highlightOnFocus="true" 
                        disabled 
                        inputClass="w-full h-8 font-bold text-center" />
                </p>
            </div>
            
            <div class="col-span-6 mt-4">
                <p class="font-bold h-6">Names of unlisted patients</p>

                <div>
                    <Textarea v-model="unlisted_patients" rows="5" cols="30" pt:root:class="w-full h-20" />
                </div>
            </div>

            <div class="col-span-6">
                <p class="font-bold h-6">Remarks</p>

                <div>
                    <Textarea v-model="remarks" rows="5" cols="30" pt:root:class="w-full h-20" />
                </div>
            </div>
        </div>

        <Divider type="dashed" />

        <div class="w-full mb-2 text-center font-bold">
            <p>Nurse on Duty</p>
        </div>
        <div v-if="employee?.length" class="w-full px-2 text-start font-semibold flex items-center">
            <div class="flex-1">{{ employee[0].firstname }} {{ employee[0].middlename }} {{ employee[0].lastname }}</div>

            <div class="flex">
                <Button text icon="pi pi-times" class="!text-red-400 !p-0 !rounded-none" pt:icon:class="!text-lg" @click="employee = null" />
            </div>
        </div>

        <div v-else class="w-full">
            <InputGroup>
                <InputText 
                    type="text" 
                    v-model="emp_id" 
                    @keyup.enter="getEmployee" 
                    @click="empty = false; verification_failed = false" />
    
                <Button icon="pi pi-search" iconClass="!text-2xl" pt:root:class="!w-16" @click="getEmployee" />
            </InputGroup>
        </div>

        <div>
            <p v-if="!employee && empty" class="italic text-red-400 px-2">Employee not found.</p>
        </div>

        <div v-if="employee" class="border border-dashed border-primary rounded-md w-full h-56 mt-2 relative">
            <NuxtSignaturePad
                ref="signature" 
                height='192px'
                width=''
                :max-width="options.maxWidth"
                :min-width="options.minWidth" 
                :options="{penColor: options.penColor, backgroundColor: options.backgroundColor}"
            />

            <div class="absolute top-1 right-1">
                <Button text icon="pi pi-times" class="!text-red-400 !p-0 !rounded-none" pt:icon:class="!text-lg" @click="signature?.clearCanvas(); verification_failed = false" />
            </div>
        </div>

        <div class="mt-2">
            <Button label="Verify" pt:root:class="w-full" @click="confirmVerify()" />
            <p v-if="verification_failed" class="italic text-red-400 px-2">Please provide nurse on duty's ID number and signature to verify.</p>
        </div>
    </Dialog>
</template>
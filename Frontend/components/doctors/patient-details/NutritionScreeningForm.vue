<script setup>
import { NutritionService } from '~/services/NutritionService';

const toast = useToast();

const user_store = useUserStore();
const { user } = storeToRefs(user_store);

const props = defineProps({
    enccode: String,
    height: String,
    weight: String,
    bmi: Number,
    ageBracket: String,
    questions: Array
});

const emit = defineEmits(['updated']);

const answers = ref([]);
const visible = ref(false);
const saving = ref(false);


//  Create new screening
function create() {
    answers.value = [null,null,null,null];
    visible.value = true;
}

//  Save screening
async function save() {
    saving.value = true;

    try{
        await NutritionService.saveScreening({
            body: {
                enccode: props.enccode,
                height: props.height,
                weight: props.weight,
                bmi: props.bmi,
                answers: answers.value,
                riskIndicator: getIndicator(),
                entry_by: user.value.employeeid
            }
        });

        emit('updated');
        visible.value = false;

        toast.add({ severity: 'success', summary: 'Success!', detail: 'Nutrition screening have been successfully saved.', life: 5000 });

    }catch(error){
        toast.add({ severity: 'error', summary: 'Error!', detail: 'An error has occured. Please log it into the intranet or call extension 202. [Nutrition Screening -> save]' });

    }finally{
        saving.value = false;
    }
}

//  Get indicator based on age bracket
function getIndicator() {
    return props.ageBracket === 'Adult' 
    ? answers.value.some(ans => ans === 'Y') ? 'Nutritionally at Risk' : 'Not at Risk'
    : answers.value.filter(ans => ans === 'Y').length >= 2 ? 'Nutritionally at Risk' : 'Not at Risk';
}
</script>

<template>
    <div>
        <div class="flex justify-end w-full">
            <div class="flex flex-col w-full md:w-auto text-center">
                <Button class="w-full md:w-max font-bold" :disabled="!height || !weight" @click="create()">
                    <Icon name="fluent:add-circle-12-filled" size="1.5em"/>
                    <label class="hover:cursor-pointer">New Nutrition Screening</label>  
                </Button>

                <span v-if="!height || !weight" class="italic text-sm text-red-400">**No height and/or weight**</span>
            </div>
        </div>

        <Dialog 
            modal
            v-model:visible="visible" 
            :dismissableMask=false
            :draggable=false
            pt:root:class="!w-full md:!w-2/3 lg:!w-1/2 !h-max !border-primary !border-2 !rounded-md sm:!text-sm md:!text-base lg:!text-lg" 
            pt:header:class="!pb-2 !pt-3 !border-b !border-primary"
            pt:content:class="!p-0"
            pt:mask:class="!backdrop-blur-sm" 
        >
            <template #header>
                <div class="flex my-5 gap-2 items-center">
                    <span class="font-bold">New Nutrition Screening</span>
                </div>
            </template>

            <div class="flex flex-col gap-4 p-4 text-base">
                <div class="grid grid-cols-6 gap-2">
                    <div class="col-span-4">
                        <span></span>
                    </div>
    
                    <div class="col-span-1 text-center">
                        <span>YES</span>
                    </div>

                    <div class="col-span-1 text-center">
                        <span>NO</span>
                    </div>
                </div>

                <Divider type="dashed" class="mt-0" />

                <div v-for="(q, i) in questions">
                    <div class="grid grid-cols-6 gap-2">
                        <div class="col-span-4">
                            <span>{{ q }}</span>
                        </div>
        
                        <div class="col-span-1 text-center">
                            <RadioButton v-model="answers[i]" :invalid="!answers[i]" value="Y" />
                        </div>
    
                        <div class="col-span-1 text-center">
                            <RadioButton v-model="answers[i]" :invalid="!answers[i]" value="N" />
                        </div>
                    </div>

                    <Divider type="dashed" />
                </div>
                <Button raised class="font-bold" :disabled="answers?.some(item => item === null) || saving" :loading="saving" @click="save()">
                    <Icon name="fluent:save-16-filled" size="1.5em"/>
                    <label>Save</label>
                </Button>
            </div>
        </Dialog>
    </div>
</template>
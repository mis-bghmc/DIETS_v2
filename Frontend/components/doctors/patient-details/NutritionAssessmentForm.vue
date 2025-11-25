<script setup>
import { NutritionService } from '~/services/NutritionService';

const toast = useToast();

const user_store = useUserStore();
const { user } = storeToRefs(user_store);

const props = defineProps({
    enccode: String,
    disabled: Boolean
});

const emit = defineEmits(['updated']);

const assessment = ref();
const visible = ref(false);
const saving = ref(false);


//  Create new screening
function create() {
    assessment.value = null;
    visible.value = true;
}

//  Save nutrition assessment
async function save(){
    saving.value = true;

    try{
        await NutritionService.saveAssessment({
            body: {
                enccode: props.enccode,
                assessment: assessment.value,
                assessed_by: user.value.employeeid
            }
        });
    
        emit('updated');
        visible.value = false;

        toast.add({ severity: 'success', summary: 'Success!', detail: 'Nutrition assessment have been successfully saved.', life: 5000 });

    }catch(error){
        toast.add({ severity: 'error', summary: 'Error!', detail: 'An error has occured. Please log it into the intranet or call extension 202. [Nutrition Assessment -> save]' });

    }finally{
        saving.value = false;
    }
}
</script>

<template>
    <div>
        <div class="flex flex-col items-center">
            <Button class="w-full font-bold" :disabled="disabled" @click="create()">
                <Icon name="fluent:add-circle-12-filled" size="1.5em"/>
                <label>New Assessment</label>  
            </Button>

            <span v-if="disabled" class="italic text-sm text-red-400">**No height and/or weight**</span>
        </div>

        <Dialog 
            modal
            v-model:visible="visible" 
            :dismissableMask=false
            :draggable=false
            pt:root:class="!w-full md:!w-5/6 lg:!w-1/2 !h-max !border-primary !border-2 !rounded-md sm:!text-sm md:!text-base lg:!text-lg" 
            pt:header:class="!pb-2 !pt-3 !border-b !border-primary"
            pt:content:class="!p-0"
            pt:mask:class="!backdrop-blur-sm" 
        >
            <template #header>
                <div class="flex my-5 gap-2 items-center">
                    <span class="font-bold">New Assessment</span>
                </div>
            </template>

            <div class="flex flex-col gap-4 p-4">
                <Textarea v-model="assessment" class="w-full !border-dashed !bg-transparent min-h-[65vh] max-h-[65vh]" />

                <Button raised class="font-bold" :disabled="!assessment?.trim() || saving" :loading="saving" @click="save()">
                    <Icon name="fluent:save-16-filled" size="1.5em"/>
                    <label>Save</label>
                </Button>
            </div>
        </Dialog>
    </div>
</template>
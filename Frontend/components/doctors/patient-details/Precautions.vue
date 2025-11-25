<script setup>
const props = defineProps({
    data: String
});

const emit = defineEmits(['updated', 'error', 'mounted']);

const precautions_store = usePrecautionsStore();
const { precautions } = storeToRefs(precautions_store);

const precaution = ref();

const error = ref();
const status = ref();


//  Get precaution
async function getPrecautions() {
    if(Object.keys(precautions.value)?.length) {
        emit('mounted');
        return;
    }

    status.value = 'pending';

    try {
        await precautions_store.getPrecautions();

    }catch(e) {
        error.value = e;
        emit('error');

    }finally{
        status.value = 'success';
        emit('mounted');
    }
}


//  Watcher
watch(
    precaution,
    (new_value) => {
        emit('updated', new_value)
    }
);


//  On mounted
onMounted(async () => {
    await getPrecautions();
    
    const precaution_list = props.data?.split(', ') || [];

    precaution.value = precaution_list?.map(p => p.trim()) || [];
});
</script>

<template>
    <ViewTemplate :error="error" :status="status">
        <MultiSelect 
            v-model="precaution" 
            :options="precautions" 
            optionLabel="name" 
            optionValue="name" 
            placeholder=""  
            id="precaution"  
            display="chip" 
            scrollHeight="50vh" 
            pt:root:class="w-full" 
            :maxSelectedLabels="2" 
        />
    </ViewTemplate>
</template>
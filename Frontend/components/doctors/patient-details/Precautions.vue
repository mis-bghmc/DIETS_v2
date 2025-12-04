<script setup>
const props = defineProps({
    data: String
});

const emit = defineEmits(['updated', 'error', 'mounted']);

const precautions_store = usePrecautionsStore();
const { precautions } = storeToRefs(precautions_store);

const { error, status } = await useAsyncData(
    'precautions', 
    () => precautions_store.getPrecautions(),
    {
        default: () => []
    }
);

const precaution = ref();


//  Watcher for selected precautions
watch(
    precaution,
    (new_value) => {
        emit('updated', new_value)
    }
);

//  Watcher for precautions error and status
watch(
    [error, status],
    (new_value) => {
        if(new_value[0]) emit('error');
        if(new_value[1] === 'success') emit('mounted');
    },
    {immediate: true}
);


//  On mounted
onMounted(async () => {
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
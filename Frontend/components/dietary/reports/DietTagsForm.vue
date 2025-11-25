<script setup>
const { show } = usePrintable();

const wards_store = useWardsStore();
const { wards } = storeToRefs(wards_store);
const ward = ref([]);

const diet_groups = ref(['Oral', 'Enteral', 'SNS']);
const diet_group = ref();

const print_options = ref(['All', 'Adult', 'Pedia']);
const print_option = ref();


//  View diet tags
function view(){
    show(`/dietary/printables/diet-tags?group=${diet_group.value}&option=${print_option.value}&wards=${ward.value}`, 'Diet Tags');
}

//  Validate
function validate() {
    return !diet_group.value || !print_option.value || !ward.value?.length;
}
</script>

<template>
    <div class="flex flex-col gap-4 w-full">
        <FloatLabel variant="on">
            <Select id="diet_groups" :options="diet_groups" v-model="diet_group" :invalid="!diet_group" class="w-full" />
            <label for="diet_groups">Groups</label>
        </FloatLabel>
    
        <FloatLabel variant="on">
            <Select id="print_options" :options="print_options" v-model="print_option" :invalid="!print_option" class="w-full" />
            <label for="print_options">Print</label>
        </FloatLabel>

        <FloatLabel variant="on">
            <MultiSelect id="wards" v-model="ward" :options="wards" option-label="wardname" option-value="wardcode" :maxSelectedLabels="2" display="chip" filter :invalid="!ward?.length" class="w-full" />
            <label for="wards">Wards</label>
        </FloatLabel>

        <div class="w-full flex justify-center">
            <Button label="View" class="w-[33%]" :disabled="validate()" @click="view()"/>
        </div>
    </div>
</template>
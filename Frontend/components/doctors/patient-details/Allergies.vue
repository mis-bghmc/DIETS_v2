<script setup>
const props = defineProps({
    data: String
});

const emit = defineEmits(['updated', 'error', 'mounted']);

const allergies_store = useAllergiesStore();
const { main, sub } = storeToRefs(allergies_store);

const { error, status } = await useAsyncData(
    'allergies', 
    () => allergies_store.getAllergies(),
    {
        default: () => []
    }
);

const food_allergies = ref();
const food_allergies_main = ref();
const food_allergies_sub = ref();


//  Set the allergy option value based on food allergies
function setFoodAllergiesMain() {
    const allergies = props.data || null;
    
    if(['No Known Allergy', 'Undetermined', null].includes(allergies)) {
        food_allergies_main.value = allergies;
    }
    else {
        const allergy_list = allergies?.split(', ') || [];

        food_allergies_main.value = includesFoodAllergies(allergy_list) ? 'With Food Allergy' : 'Others';
    }
}

//  Update the food allergy value based on selected option
function setFoodAllergies() {
    const allergies = props.data || null;
    
    if(food_allergies_main.value === 'With Food Allergy') {
        const allergy_list = allergies?.split(', ') || [];

        food_allergies.value =  includesFoodAllergies(allergy_list) ? allergy_list : null;
    }
    else if(food_allergies_main.value === 'Others') {
        food_allergies.value = allergies;
    }
    else{
        food_allergies.value = food_allergies_main.value;
    }
}

//  Check if all allergies are from the known submenu
function includesFoodAllergies(_allergies) {
    return _allergies?.every(allergy => food_allergies_sub.value?.includes(allergy));
}


//  Watcher
watch(
    food_allergies,
    (new_value) => {
        emit('updated', new_value)
    }
);

//  Watcher for allergies error and status
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
    food_allergies_sub.value = sub.value?.map(item => item.name);
    
    setFoodAllergiesMain();
    setFoodAllergies();
});
</script>

<template>
    <ViewTemplate :error="error" :status="status">
        <div class="flex flex-col gap-2">
            <Select 
                v-model="food_allergies_main" 
                :options="main" 
                optionLabel="name" 
                optionValue="name" 
                placeholder="" 
                scrollHeight="50vh"
                :invalid="!food_allergies?.length" 
                class="w-full"
                @change="setFoodAllergies()" 
            />
        
            <MultiSelect v-if="food_allergies_main === 'With Food Allergy'" 
                v-model="food_allergies" 
                :options="sub" 
                optionLabel="name" 
                optionValue="name" 
                placeholder="Select Allergies"  
                display="chip" 
                scrollHeight="50vh" 
                :maxSelectedLabels="2" 
                :invalid="!food_allergies?.length" 
                pt:labelContainer:class="flex items-end"
            />
        
            <InputText v-if="food_allergies_main === 'Others'" v-model="food_allergies" placeholder="Add Allergy" :invalid="!food_allergies?.length" />
        </div>
    </ViewTemplate>
</template>
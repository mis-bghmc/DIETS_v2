<script setup>
const { show } = usePrintable();
const { formatAllNumeric } = useDate();

const max_date = ref(new Date());
const date = ref(new Date());

const diet_groups = ref({
    Oral: ['BREAKFAST', 'LUNCH', 'DINNER'],
    Enteral: [],
    SNS: ['AM', 'PM', 'MN']
});
const diet_group = ref();
const meal_time = ref(null);


//  View diet tags
function view(){
    show(`/dietary/printables/meal-census?date=${formatAllNumeric(date.value)}&group=${diet_group.value}&mealtime=${meal_time.value}`, 'Meal Census');
}

//  Validate
function validate() {
    return !diet_group.value || !meal_time.value;
}
</script>

<template>
    <div class="flex flex-col gap-4 w-full">
        <FloatLabel variant="on">
            <DatePicker id="date" v-model="date" :maxDate="max_date" :manualInput="false" class="w-full" />
            <label for="date">Date</label>
        </FloatLabel>

        <FloatLabel variant="on">
            <Select id="diet_groups" :options="Object.keys(diet_groups)" v-model="diet_group" :invalid="!diet_group" class="w-full" @change="meal_time = null" />
            <label for="diet_groups">Groups</label>
        </FloatLabel>
    
        <FloatLabel variant="on">
            <InputNumber v-if="diet_group === 'Enteral'" id="meal_times" v-model="meal_time" :invalid="!meal_time" class="w-full" />
            <Select v-else id="meal_times" :options="diet_groups[diet_group]" v-model="meal_time" :invalid="!meal_time" class="w-full" />
            <label for="meal_times">{{ diet_group === 'Enteral' ? 'Feeding' : 'Meal Time' }}</label>
        </FloatLabel>

        <div class="w-full flex justify-center">
            <Button label="View" class="w-[33%]" :disabled="validate()" @click="view()"/>
        </div>
    </div>
</template>
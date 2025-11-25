<script setup>
const router = useRouter();

const diet_list_store = useDietListStore();
const { s_diet_list } = storeToRefs(diet_list_store);
const { error, status } = inject('diet_list');

const nutrition = ref(0);

//  Nutrition
watch(s_diet_list, 
    (new_value) => { 
        const patients_concatinated = Object.values(new_value)?.flat();
        const unique_patients = patients_concatinated?.filter((patient, index, self) => { return index === self.findIndex(p => patient.hpercode === p.hpercode) });

        nutrition.value = unique_patients?.filter(item => item.nar === 'YES')?.length;
    }, 
    { immediate: true }
);
</script>

<template>
    <ViewTemplate :error="error" :status="status">
        <div class="grid grid-rows-6">
            <div class="row-span-1 flex items-center justify-between">
                <span class="text-muted-color">Nutritionally at Risk</span>
                <Button text icon="pi pi-external-link" @click="router.push('/dietary/nutritionally-at-risk')" />
            </div>
            <div class="row-span-5 flex items-center justify-center">
                <span class="font-bold text-6xl text-primary">{{ nutrition }}</span>
            </div>
        </div>
    </ViewTemplate>
</template>
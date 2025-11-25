<script setup>
const toast = useToast();

const wards_store = useWardsStore();
const { wards } = storeToRefs(wards_store);

const reports = ref({
    'Meal Census': false,
    'Diet List': false,
    'Diet Tags': false
});
const report = ref();
const prev_report = ref();


//  Get report accordingly
function getReport() {
    if(reports.value[prev_report.value]) reports.value[prev_report.value] = false;
    reports.value[report.value] = true;
    prev_report.value = report.value;
}

//  On mounted
onMounted(async () => {
    if(Object.keys(wards.value)?.length) return;

    try {
        await wards_store.getWards();

    }catch {
        toast.add({ severity: 'error', summary: 'Error!', detail: 'An error has occured. Please log it into the intranet or call extension 202. [Wards]' });
    }
});
</script>

<template>
    <div class="container mx-auto w-full">
        <div class="grid grid-cols-3 gap-4 lg:gap-2">
            <div class="col-span-3 md:col-span-1 w-full bg-[--surface-card] p-4 rounded-md">
                <FloatLabel variant="on">
                    <Select id="reports" :options="Object.keys(reports)" v-model="report" class="w-full" @change="getReport()" />
                    <label for="reports">Reports</label>
                </FloatLabel>
            </div>
    
            <div class="col-span-3 md:col-span-2 w-full bg-[--surface-card] p-4 rounded-md">
                <MealCensusForm :class="{'hidden': !reports['Meal Census']}" />
                <DietListForm :class="{'hidden': !reports['Diet List']}" />
                <DietTagsForm :class="{'hidden': !reports['Diet Tags']}" />
            </div>
        </div>
    </div>
</template>
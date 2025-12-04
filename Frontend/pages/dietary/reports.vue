<script setup>
const wards_store = useWardsStore();

const { error, status } = await useAsyncData(
    'wards', 
    () => wards_store.getWards(),
    {
        default: () => []
    }
);

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
                <div :class="{'hidden': !reports['Meal Census']}">
                    <MealCensusForm />
                </div>

                <div :class="{'hidden': !reports['Diet List']}">
                    <ViewTemplate :error="error" :status="status">
                        <DietListForm />
                    </ViewTemplate>
                </div>

                <div :class="{'hidden': !reports['Diet Tags']}">
                    <ViewTemplate :error="error" :status="status">
                        <DietTagsForm />
                    </ViewTemplate>
                </div>
            </div>
        </div>
    </div>
</template>
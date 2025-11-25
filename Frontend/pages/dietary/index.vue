<script setup>
const date = ref(new Date());
const month = ref(date.value.getMonth());
const year = ref(date.value.getFullYear());

const requests_dates = ref([]);

const g_key = ref();
const r_key = ref();

function selectDate() {
    g_key.value = `G_${new Date()}`;
    r_key.value = `R_${new Date()}`;
}

function changeMonth(event) {
    month.value = event.month - 1;
    year.value = event.year;
    
    r_key.value = `R_${new Date()}`;
}

function setRequestDates(dates) {
    requests_dates.value = dates;
}
</script>

<template>
    <div class="container mx-auto">
        <ScrollTop />

        <div class="flex flex-col gap-8">
            <div class="grid grid-cols-12 gap-6">
                <div class="bg-[--surface-card] p-3 rounded-md col-span-12 md:col-span-4">
                    <WithSuspense>
                        <AdmittedPatientsCount />
                    </WithSuspense>
                </div>
    
                <div class="bg-[--surface-card] p-3 rounded-md col-span-12 md:col-span-4">
                    <WithSuspense>
                        <DoctorsOrdersCount />
                    </WithSuspense>
                </div>

                <div class="bg-[--surface-card] p-3 rounded-md col-span-12 md:col-span-4">
                    <WithSuspense>
                        <NutritionCount />
                    </WithSuspense>
                </div>
            </div>

            <div class="grid grid-cols-12 gap-6">
                <div class="bg-[--surface-card] p-3 rounded-md h-auto col-span-12 md:col-span-6 xl:col-span-4">
                    <DatePicker 
                        v-model="date" 
                        inline 
                        class="w-full" 
                        pt:panel:class="border-primary rounded-sm" 
                        pt:header:class="border-b-primary" 
                        @month-change="changeMonth"
                        @date-select="selectDate()"
                    >
                        <template #date="slotProps">
                            <span v-if="requests_dates?.includes(slotProps.date.day) && month === slotProps.date.month" class="font-bold border-b-2 border-primary">{{ slotProps.date.day }}</span>
                            <span v-else>{{ slotProps.date.day }}</span>
                        </template>

                        <template #prevbutton>
                            <div></div>
                        </template>

                        <template #nextbutton>
                            <div></div>
                        </template>
                    </DatePicker>
                </div>

                <div class="bg-[--surface-card] p-3 rounded-md col-span-12 md:col-span-6 xl:col-span-8">
                    <WithSuspense :key="r_key">
                        <FoodRequests :date="date" :month="month" :year="year" @update="setRequestDates" />
                    </WithSuspense>
                </div>
            </div>

            <div class="bg-[--surface-card] p-3 rounded-md">
                <WithSuspense :key="g_key">
                    <Graph :date="date" />
                </WithSuspense>
            </div>
        </div>
    </div>
</template>
<script setup>
import { FilterMatchMode } from '@primevue/core/api';
import NoData from '~/public/images/no-data.svg';

const { setMealTimeSuffix } = useEnteralFeeding();

const props = defineProps({
    date: String,
    mealTime: String,
    census: Array,
    type: String
});

const visible = ref(false);

const expandedRows = ref({});

const filters = ref();

const initFilters = () => {
    filters.value = {
        name: { value: null, matchMode: FilterMatchMode.CONTAINS },
    };
};


//  Get width for status bar
function getWidth(_action, _census){
    if(!_census?.length) return 0;

    const prepared = getCountPrepared(_census);
    const served = getCountServed(_census);
    const total = prepared + served;

    if(!total) return 0;

    return _action === 'served' ? (served / total) * 100 : (total / _census.length) * 100;
}

//  Get the number of prepared meals for the specified ward
function getCountPrepared(_census){
    return Object.groupBy(_census, ({meal_status}) => meal_status).P?.length || 0;
}

//  Get the number of served meals for the specified ward
function getCountServed(_census){
    return Object.groupBy(_census, ({meal_status}) => meal_status).S?.length || 0;
}

//  Get the total number of prepared meals for the specified ward
function getTotalPrepared(_census){
    return getCountPrepared(_census) + getCountServed(_census);
}

//  Flatten object
function flattenObject(_data) {
    return Object.values(_data.precautions)?.flat();
}


//
onMounted(() => {
    initFilters();
})
</script>

<template>
    <Button text icon="pi pi-external-link" @click="visible = true" />

    <Dialog 
        modal
        v-model:visible="visible" 
        :dismissableMask=false
        :draggable=false
        pt:root:class="!w-full md:!w-[96vw] !h-full !border-primary !border-2 !rounded-md sm:!text-sm md:!text-base lg:!text-lg" 
        pt:header:class="!pb-2 !pt-3 !border-b !border-primary"
        pt:content:class="!p-0"
        pt:mask:class="!backdrop-blur-sm" 
    >
        <template #header>
            <div class="flex my-5 gap-2 items-center">
                <span class="font-bold">{{ date }} - {{ mealTime }}{{ setMealTimeSuffix(mealTime) }}</span>
            </div>
        </template>

        <DataTable 
            v-model:expandedRows="expandedRows" 
            v-model:filters="filters" 
            :value="census" 
            :rowHover="true" 
            stripedRows 
            dataKey="name"
            filterDisplay="menu" 
            pt:root:class="sm:text-xs md:text-xs lg:text-lg"
        >

            <template #empty>
                <div class="container mx-auto flex flex-col justify-center items-center py-4 h-[62vh]">
                    <NoData class="text-primary" />
                    <p class="text-lg font-bold mt-4">No data found.</p>
                </div>
            </template>

            <Column :expander="true" headerClass="!border-b !border-primary !h-16" class="!w-[1%]" />

            <Column 
                filterField="name" 
                :showFilterMatchModes="false" 
                showClearButton
                filterHeaderClass="py-1"
                headerClass="!border-b !border-primary" 
                class="!w-[30%]">

                <template #header>
                    <div class="w-full flex justify-start items-center font-bold">
                        <span>{{ type }}</span>
                    </div>
                </template>

                <template #body="{data}">
                    <span class="font-semibold">{{ data.name }}</span>
                </template>

                <template #filter="{ filterModel, filterCallback }">
                    <InputText v-model="filterModel.value" type="text" @input="filterCallback()" />
                </template>
            </Column>

            <Column headerClass="!border-b !border-primary" class="!w-[10%]">
                <template #header>
                    <div class="w-full flex justify-center items-center font-bold">
                        <span>Total</span>
                    </div>
                </template>

                <template #body="{data}">
                    <p class="font-semibold text-center">{{ data.total }}</p>
                </template>
            </Column>

            <Column headerClass="!border-b !border-primary" class="!w-[20%]">
                <template #header>
                    <div class="w-full flex justify-center items-center font-bold">
                        <span>Prepared</span>
                    </div>
                </template>

                <template #body="{data}">
                    <div class="mb-2 sm:text-sm md:text-base lg:text-lg pt-2">
                        <div class="overflow-hidden relative border-none rounded-md h-7 bg-primary-100">
                            <div class="absolute inset-0 m-0 rounded-md border-none bg-primary-400 transition-width duration-500 ease-in-out" 
                                :style="{width: getWidth('prepared', flattenObject(data)) + '%'}">
                            </div>
                            <div class="absolute inset-0 flex items-center justify-center text-slate-700 font-semibold">
                                <span>{{ getTotalPrepared(flattenObject(data)) }}</span>
                                <span class="mx-2">/</span>
                                <span>{{ flattenObject(data)?.length }}</span>
                            </div>
                        </div>
                    </div>
                </template>
            </Column>

            <Column headerClass="!border-b !border-primary" class="!w-[20%]">
                <template #header>
                    <div class="w-full flex justify-center items-center font-bold">
                        <span>Served</span>
                    </div>
                </template>

                <template #body="{data}">
                    <div class="mb-2 sm:text-sm md:text-base lg:text-lg pt-2">
                        <div class="overflow-hidden relative border-none rounded-md h-7 bg-primary-100">
                            <div class="absolute inset-0 m-0 rounded-md border-none bg-primary-400 transition-width duration-500 ease-in-out" 
                                :style="{width: getWidth('served', flattenObject(data)) + '%'}">
                            </div>
                            <div class="absolute inset-0 flex items-center justify-center text-slate-700 font-semibold">
                                <span>{{ getCountServed(flattenObject(data)) }}</span>
                                <span class="mx-2">/</span>
                                <span>{{ getTotalPrepared(flattenObject(data)) }}</span>
                            </div>
                        </div>
                    </div>
                </template>
            </Column>

            <template #expansion="_census">
                <div class="border-2 border-red-200">
                    <DataTable 
                        :value="Object.keys(_census.data.precautions).map((key) => ({precautions: key}))" 
                        sortField="precautions" 
                        :sortOrder="1">

                        <Column class="w-[45%]">
                            <template #header>
                                <span class="text-red-400 font-semibold">Precautions</span>
                            </template>
                            <template #body="{data}">
                                <span>{{ data.precautions }}</span>
                            </template>
                        </Column>
                        <Column>
                            <template #body="{data}">
                                <span>{{ _census.data.precautions[data.precautions]?.length }}</span>
                            </template>
                        </Column>
                    </DataTable>
                </div>
            </template>
        </DataTable>
    </Dialog>
</template>

<style scoped lang="scss">
.column-head {
    .column-button {
        color: #f1f5f9;

        &.dark-noir {
            color: #475569;

            &:hover {
                color: #475569;
            }
        }

        &:hover {
            background-color: #f1f5f9;
            color: var(--primary-color);
            cursor: pointer;
        }
    }
}
</style>
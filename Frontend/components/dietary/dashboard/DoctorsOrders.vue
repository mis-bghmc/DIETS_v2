<script setup>
import { DoctorsOrdersService } from '~/services/DoctorsOrdersService';
import { FilterMatchMode } from '@primevue/core/api';
import NoData from '~/public/images/no-data.svg';

const props = defineProps({
    patients: Object
});

const { data: wards, error, status, refresh } = await useLazyAsyncData(
    'doctors-orders-total', 
    () => DoctorsOrdersService.getDoctorsOrdersTotal(),
    {
        default: () => [],
        immediate: false
    }
);

const visible = ref(false);

const expandedRows = ref({});

const status_filter = ref([
    { label: 'Complete', value: 'Y' },
    { label: 'Incomplete', value: 'N'}
]);

const wards_filters = ref({
    wardname: { value: null, matchMode: FilterMatchMode.CONTAINS },
    is_complete: { value: null, matchMode: FilterMatchMode.EQUALS }
});


async function show() {
    visible.value = true;
    await refresh();
}
</script>

<template>
    <Button text icon="pi pi-external-link" @click="show()" />

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
                <span class="font-bold">Doctors' Orders</span>
            </div>
        </template>

        <ViewTemplate :error="error" :status="status" class="!m-0 w-full">
            <DataTable 
                :value="wards" 
                :rowHover="true" 
                :sortOrder="1" 
                sortField="wardname" 
                stripedRows 
                v-model:expandedRows="expandedRows" 
                v-model:filters="wards_filters" 
                filterDisplay="menu" 
                dataKey="wardname"
                pt:root:class="sm:text-xs md:text-xs lg:text-lg"
            >
                <template #empty>
                    <div class="container mx-auto flex flex-col justify-center items-center py-4 h-[75vh]">
                        <NoData class="text-primary" />
                        <p class="text-lg font-bold mt-4">No data found.</p>
                    </div>
                </template>
    
                <Column :expander="true" headerClass="!border-b !border-primary !h-16" class="!w-[1%]" />
    
                <Column 
                    filterField="wardname" 
                    :showFilterMatchModes="false" 
                    showClearButton
                    filterHeaderClass="py-1"
                    headerClass="!border-b !border-primary" 
                    class="!w-[70%]">
    
                    <template #header>
                        <div class="w-full flex justify-start items-center font-bold">
                            <span>Wards</span>
                        </div>
                    </template>
    
                    <template #body="{data}">
                        <span class="font-semibold">{{ data.wardname }}</span>
                    </template>
    
                    <template #filter="{ filterModel, filterCallback }">
                        <InputText v-model="filterModel.value" type="text" @input="filterCallback()" />
                    </template>
                </Column>
    
                <Column 
                    filterField="is_complete" 
                    :showFilterMatchModes="false" 
                    showClearButton
                    filterHeaderClass="py-1"
                    headerClass="!border-b !border-primary" 
                    class="!w-[29%]">
    
                    <template #header>
                        <div class="w-full flex justify-center items-center font-bold">
                            <span>Status</span>
                        </div>
                    </template>
    
                    <template #body="{data}">
                        <div class="mb-2 sm:text-sm md:text-base lg:text-lg pt-2">
                            <div class="overflow-hidden relative border-none rounded-md h-7 bg-primary-100">
                                <div class="absolute inset-0 m-0 rounded-md border-none bg-primary-400 transition-width duration-500 ease-in-out" 
                                    :style="{width: (data.entries / data.total) * 100 + '%'}">
                                </div>
                                <div class="absolute inset-0 flex items-center justify-center text-slate-700 font-semibold">
                                    <span>{{ data.entries ?? 0 }}</span>
                                    <span class="mx-2">/</span>
                                    <span>{{ data.total ?? 0 }}</span>
                                </div>
                            </div>
                        </div>
                    </template>
    
                    <template #filter="{ filterModel, applyFilter }">
                        <Select v-model="filterModel.value" :options="status_filter" optionLabel="label" optionValue="value" style="min-width: 12rem" @change="applyFilter()" />
                    </template>
                </Column>
    
                <template #expansion="_wards">
                    <DataTable 
                        :value="patients.filter(p => p.wardname === _wards.data.wardname)" 
                        :sortOrder="1"
                        sortField="patname" 
                        dataKey="hpercode"
                        class="border border-primary-300"
                    >
                        <Column headerClass="p-0">
                            <template #body="{data}">
                                <div class="flex flex-col">
                                    <span class="font-semibold" :class="{'text-red-400': !data.dodate}">{{ data.patname }}</span>
                                    <span v-if="data?.wardcode === 'ERB'" class="text-blue-400 italic">{{ data.wardname_o }}</span>
                                </div>
                            </template>
                        </Column>
            
                        <Column headerClass="p-0">
                            <template #body="{data}">
                                <div class="flex items-center justify-center">
                                    <span class="font-semibold">{{ data.dodate }}</span>
                                </div>
                            </template>
                        </Column>
                    </DataTable>
                </template>
            </DataTable>
        </ViewTemplate>
    </Dialog>
</template>